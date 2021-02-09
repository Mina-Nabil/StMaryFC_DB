<?php

namespace App\Models;

use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class Payment extends Model
{
    protected $dates = ['PYMT_DATE:d-m-Y'];
    protected $table = "payments";
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo('App\Models\User', "PYMT_USER_ID");
    }

    public static function getPaymentsLite($from, $to, $user = 0)
    {
        $query = DB::table('payments')->whereBetween('PYMT_DATE', [$from, $to]);
        if ($user != 0) {
            $query = $query->where('PYMT_USER_ID', $user);
        }
        return $query->select('id', 'PYMT_AMNT')->get();
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

    public static function addPayment($id, $amount, $date, $note = null)
    {
        try {
            DB::transaction(function () use ($id, $amount, $date, $note) {
                Payment::insertPayment($date, $id, $amount, $note);
                $startDate =  (new DateTime($date))->format('Y-m-01');
                $endDate =  (new DateTime($date))->format('Y-m-t');
                Attendance::setPaid($id, $startDate, $endDate);
                $user = User::findOrFail($id);
                // self::sendSMS($user->USER_NAME, $user->USER_MOBN, $amount, (new DateTime($date))->format('M-Y'));
            });
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    public static function sendSMS($name, $mob, $amount, $month){
        return Http::asForm()->post('https://smssmartegypt.com/sms/api/json/', [
            'username' => 'mina9492@hotmail.com',
            'password' => 'mina4ever',
            'sendername' => 'Academy',
            'mobiles' => $mob,
            'message' => "St. Mary Rehab Football Academy

            {$name}
            Payment Received {$amount} LE
            {$month}
            
            Thank you",
        ]);
    }
}
