<?php

namespace App\Console\Commands;

use App\Models\BalancePayment;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RevertPaymentBatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:revert {start : First balance_payments id in the batch} {end : Last balance_payments id in the batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove a duplicated attendance billing batch and restore player balances';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $start = (int) $this->argument('start');
        $end = (int) $this->argument('end');

        if ($start < 1 || $end < $start) {
            $this->error('Pass a balance payment id range, with the start id less than or equal to the end id.');

            return Command::FAILURE;
        }

        $balances = BalancePayment::query()
            ->whereBetween('id', [$start, $end])
            ->orderBy('id')
            ->get();

        if ($balances->isEmpty()) {
            $this->error("No balance payments found from {$start} to {$end}.");

            return Command::FAILURE;
        }

        $notAttendance = $balances->filter(fn (BalancePayment $row) => ! str_starts_with((string) $row->title, 'Atnd '));
        if ($notAttendance->isNotEmpty()) {
            $this->error('This range includes balance payments that are not attendance deductions: '.$notAttendance->pluck('id')->implode(', '));

            return Command::FAILURE;
        }

        $claimedPaymentIds = [];
        $pairs = [];

        foreach ($balances as $balance) {
            $payment = $this->matchingPayment($balance, $claimedPaymentIds);
            if ($payment === null) {
                $this->error("No matching payments row for balance payment {$balance->id} ({$balance->title}, user {$balance->app_users_id}). Nothing was deleted.");

                return Command::FAILURE;
            }

            $claimedPaymentIds[] = $payment->id;
            $pairs[] = [$balance, $payment];
        }

        $userIds = $balances->pluck('app_users_id')->unique()->values();
        foreach ($userIds as $userId) {
            $oldestDeletedId = $balances->where('app_users_id', $userId)->min('id');
            $deletedIds = $balances->where('app_users_id', $userId)->pluck('id');
            $later = BalancePayment::query()
                ->where('app_users_id', $userId)
                ->where('id', '>', $oldestDeletedId)
                ->whereNotIn('id', $deletedIds)
                ->pluck('id');

            if ($later->isNotEmpty()) {
                $this->error("User {$userId} has balance payments after this batch ({$later->implode(', ')}). Their later totals include this charge, so the batch was left in place.");

                return Command::FAILURE;
            }
        }

        $this->table(
            ['Balance id', 'Payment id', 'User', 'Title', 'Amount'],
            collect($pairs)->map(function (array $pair) {
                [$balance, $payment] = $pair;

                return [
                    $balance->id,
                    $payment->id,
                    $balance->app_users_id,
                    $balance->title,
                    $balance->value,
                ];
            })->all()
        );

        if (! $this->confirm('Delete these '.$balances->count().' balance payments and their matching payments, then restore each player balance?', false)) {
            $this->info('Cancelled.');

            return Command::SUCCESS;
        }

        DB::transaction(function () use ($pairs, $userIds) {
            foreach ($pairs as [$balance, $payment]) {
                $payment->delete();
                $balance->delete();
            }

            foreach ($userIds as $userId) {
                $latest = BalancePayment::query()
                    ->where('app_users_id', $userId)
                    ->orderByDesc('id')
                    ->first();

                User::query()->whereKey($userId)->update([
                    'balance' => $latest ? $latest->new_balance : 0,
                ]);
            }
        });

        $this->info('Reverted '.$balances->count().' attendance charges. Player balances now match their latest remaining balance payment.');

        return Command::SUCCESS;
    }

    /**
     * Find the payments row created with this balance deduction.
     *
     * @param  array<int, int>  $claimedPaymentIds
     */
    private function matchingPayment(BalancePayment $balance, array $claimedPaymentIds): ?Payment
    {
        return Payment::query()
            ->where('PYMT_USER_ID', $balance->app_users_id)
            ->where('PYMT_NOTE', $balance->title)
            ->where('PYMT_AMNT', abs($balance->value))
            ->when($claimedPaymentIds !== [], fn ($query) => $query->whereNotIn('id', $claimedPaymentIds))
            ->get()
            ->sortBy([
                fn (Payment $payment) => abs($payment->created_at?->diffInSeconds($balance->created_at, false) ?? PHP_INT_MAX),
                fn (Payment $payment) => -$payment->id,
            ])
            ->first();
    }
}
