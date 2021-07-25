<?php

namespace App\Models;

use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class Payment extends Model
{
    protected $casts = ['PYMT_DATE' => 'datetime'];
    protected $table = "payments";
    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo('App\Models\User', "PYMT_USER_ID");
    }

    public function collector()
    {
        return $this->belongsTo('App\Models\User', "PYMT_CLCT_ID");
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
            $payment = new Payment();
            $payment->PYMT_DATE = $startDate;
            $payment->PYMT_AMNT = $amount;
            $payment->PYMT_USER_ID = $userID;
            $payment->PYMT_CLCT_ID = Auth::user()->id;
            $payment->PYMT_NOTE = $note;
            $payment->save();
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
                self::sendPaymentSMS($user->USER_NAME, $user->USER_MOBN, $amount, (new DateTime($date))->format('M-Y'));
            });
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    public function refund()
    {
        $user = User::findOrFail($this->PYMT_USER_ID);
        self::sendPaymentSMS($user->USER_NAME, $user->USER_MOBN, $this->PYMT_AMNT, (new DateTime($this->PYMT_DATE))->format('M-Y'), true);
        return $this->delete();
    }

    public static function sendPaymentSMS($name, $mob, $amount, $month, $refund = false)
    {
        if ($refund) $message = "[REFUND] \n";
        else
            $message = "St. Mary Rehab Football Academy \n";
        $message .= "{$name} \n";
        if ($refund)
            $message .= "Refund {$amount} LE \n";
        else
            $message .= "Payment Received {$amount} LE \n";
        $message .= "{$month}
        
        Thank you";
        return Http::asForm()->post('https://smssmartegypt.com/sms/api/json/', [
            'username' => 'mina9492@hotmail.com',
            'password' => 'mina4ever',
            'sendername' => 'Academy',
            'mobiles' => $mob,
            'message' => $message,
        ]);
    }

    public static function sendPaymentReminderSMS($kidName, $parentMob)
    {
        $message = "Dear Parent,
        We kindly remind you to revise {$kidName} balance with the Finance team.
       
       Please contact Coach Abanob:
       Whatsapp 01211196104
       
       Thank you";

        return Http::asForm()->post('https://smssmartegypt.com/sms/api/json/', [
            'username' => 'mina9492@hotmail.com',
            'password' => 'mina4ever',
            'sendername' => 'Academy',
            'mobiles' => $parentMob,
            'message' => $message,
        ]);
    }
}
