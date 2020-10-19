<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Payment extends Model
{
    protected $dates = ['PYMT_DATE'];
    protected $table = "payments";
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo('App\Models\User', "PYMT_USER_ID");
    }

    public static function getPaymentsAttendanceArrayPerMonth($userID, $date)
    {
    }

    public static function insertPayment($date, $userID, $amount, $note = '')
    {
        DB::transaction(function () use ($date, $userID, $amount, $note) {

            $startDate = (new DateTime($date))->format('Y-m-01');
            DB::table('payments')->insert([
                ["PYMT_DATE" => $startDate, "PYMT_AMNT" => $amount, "PYMT_USER_ID" => $userID, "PYMT_NOTE" => $note]
            ]);
        });
    }

    public static function didPayMonth($userID, $date)
    {
        $date = (new DateTime($date));
        $startDate = $date->format('Y-m-01');
        $endDate = $date->format('Y-m-t');
        $noOfPayments = DB::table("payments")->where('PYMT_USER_ID', $userID)->whereBetween('PYMT_DATE', [$startDate, $endDate])
            ->get()->count();
        return ($noOfPayments > 0) ? true : false;
    }
}
