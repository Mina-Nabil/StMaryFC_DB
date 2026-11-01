<?php

namespace App\Console\Commands;

use App\Jobs\DeductMonthlySubscription;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use InvalidArgumentException;

class DeductMonthlyPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:deduct
                            {date? : Month to bill, as YYYY-MM or YYYY-MM-01. Defaults to the previous month}
                            {--force : Bill players who already have an attendance payment for that month}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create monthly attendance payments for a given month';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $month = $this->month();
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return Command::FAILURE;
        }

        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();
        $players = User::with('player_category')->get();
        $billed = 0;
        $skipped = 0;

        $this->info('Billing attendance for '.$start->toDateString().' through '.$end->toDateString().'.');

        foreach ($players as $player) {
            $alreadyBilled = Payment::query()
                ->where('PYMT_USER_ID', $player->id)
                ->whereBetween('PYMT_DATE', [$start->toDateString(), $end->toDateString()])
                ->where('PYMT_NOTE', 'like', 'Atnd %')
                ->exists();

            if ($alreadyBilled && ! $this->option('force')) {
                $this->line('Skipped '.$player->USER_NAME.' (already billed)');
                $skipped++;

                continue;
            }

            DeductMonthlySubscription::dispatchSync($player, $start->copy());
            $billed++;
        }

        $this->info("Finished. Ran {$billed}, skipped {$skipped}.");

        return Command::SUCCESS;
    }

    /**
     * Resolve the month being billed.
     */
    private function month(): Carbon
    {
        $date = $this->argument('date');

        if ($date === null) {
            return Carbon::now()->subMonth()->startOfMonth();
        }

        if (! preg_match('/^\d{4}-\d{2}(-\d{2})?$/', $date)) {
            throw new InvalidArgumentException('Pass a month as YYYY-MM or YYYY-MM-01.');
        }

        $format = strlen($date) === 7 ? '!Y-m' : '!Y-m-d';
        $parsed = Carbon::createFromFormat($format, $date);

        if ($parsed === false || $parsed->format(ltrim($format, '!')) !== $date) {
            throw new InvalidArgumentException('Pass a real month as YYYY-MM or YYYY-MM-01.');
        }

        return $parsed->startOfMonth();
    }
}
