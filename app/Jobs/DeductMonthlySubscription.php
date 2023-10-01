<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeductMonthlySubscription implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $date;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, Carbon $date)
    {
        $this->user = $user;
        $this->date = $date;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $start = new Carbon($this->date->format('Y-m-01'));
        $end = new Carbon($start->format('Y-m-t'));
        Log::debug('===================DEDUCTIONJOB=======================');
        Log::debug("Dates from $start to $end ");
        Log::debug('User: ' . $this->user->USER_NAME);
        $monthlyAttendance  = Attendance::getAttendanceLite($start, $end, $this->user->id);
        //check attendance count
        $attendanceCount = $monthlyAttendance->count();
        Log::debug('Attendance: ' . $attendanceCount);
        if ($attendanceCount == 0) return;

        //check player category
        $this->user->loadMissing('player_category');
        if ($this->user->player_category == null) return null;
        Log::debug('Category: ' . $this->user->player_category->title);

        //calculate player balance
        $amount = $this->user->player_category->getDue($attendanceCount);
        Log::debug('Amount Due: ' . $amount);

        //paid amount
        $paid = $this->user->payments()->fromTo($start, $end)->get()->sum('PYMT_AMNT');
        Log::debug('Amount Paid: ' . $paid);

        $toPay = $amount - $paid;
        $date = $start;
   
        if ($toPay)
            Payment::addPayment($this->user, $toPay, $date, "$start->monthName Due ($attendanceCount)");
        else
            Log::debug('Already paid - no sending');
    }
}
