<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\PlayersCatogory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MigrateBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'balance:migrate {code?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate user balance to new tables';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $code = $this->argument('code');
        $players = User::players();
        if ($code) {
            echo "\nMigrating User#" . $code;
            $players = $players->where('USER_CODE', $code)->get();
        } else {
            echo "\nMigrating all.........";
            $players = $players->get();
        }
        $currentMonth = Carbon::now();
        $currentMonth->setDay(1);
        $currentMonth->subMonth();
        $newPriceDate = new Carbon("2023-07-01");
        /** @var User */
        foreach ($players as $p) {
            echo "\n=======================================================";
            echo "\nStarting Migration for $p->USER_NAME";
            $p->loadMissing('player_category');
            if ($p->player_category == null) {
                echo "\nNo Category attached for the user.. Aborting Migtation";
                return Command::FAILURE;
            }
            $latestBalancePayment = $p->balance_payments()->latest()->first();
            if($latestBalancePayment !== null) continue;
            $latestPayment = $p->payments()->latest()->first();
            if($latestPayment == null) continue;
            $latestPaymentDate = (new Carbon($latestPayment->PYMT_DATE));
            echo "\nLast payment was on " . $latestPaymentDate->format('Y-m-d');
            $latestPaymentDate->addMonth();
            $totalAmount = 0;
            while ($latestPaymentDate->isBefore($currentMonth)) {
                echo "\nMigrating Month $latestPaymentDate->monthName / $latestPaymentDate->year";
                $attendace = Attendance::getAttendanceLite($latestPaymentDate->format('Y-m-01'), $latestPaymentDate->format('Y-m-t'), $p->id)->count();
                $amount = $p->player_category->getDue($attendace);
                if($latestPaymentDate->isBefore($newPriceDate)){
                    $amount = 0.8 * $amount;
                }
                echo "\nAttendance: $attendace - Amount: $amount ";
                $totalAmount += $amount;
                $latestPaymentDate->addMonth();
            }
            echo "\nTotal -$totalAmount added to balance";
            $p->addToBalance(-1 * $totalAmount, "Migrating Balance", null, true);
        }

        return Command::SUCCESS;
    }
}
