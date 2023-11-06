<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\BalancePayment;
use App\Models\PlayersCatogory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MigrateBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'balance:send_sms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send balance sms for the latest month';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $now = Carbon::now();
        $latestBalancePayments = BalancePayment::whereDate('created_at', '>=', $now->format('Y-m-01'))->get();
        Log::info($latestBalancePayments);

        // /** @var BalancePayment */
        // foreach($latestBalancePayments as $pymt){
        //     $pymt->sendSms();
        // }
        // return Command::SUCCESS;
    }
}
