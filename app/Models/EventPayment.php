<?php

namespace App\Models;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Model;

class EventPayment extends Model
{
    protected $table = "event_payments";

    public $state;

    function event()
    {
        return $this->belongsTo('App\Models\Event', "EVPY_EVNT_ID");
    }

    function user()
    {
        return $this->belongsTo('App\Models\User', "EVPY_USER_ID");
    }

    public static function report(DateTime $from, DateTime $to, $user = 0)
    {
        $to = $to->format('Y-m-d 23:59:59');
        $from = $from->format('Y-m-d 00:00:00');

        $query = self::join("app_users", "app_users.id", "=", "EVPY_USER_ID")->join("events", 'events.id', '=', 'EVPY_EVNT_ID')
            ->leftJoin('events_attendance', function ($join) {
                $join->on('EVAT_USER_ID', '=', 'app_users.id')->on('EVAT_EVNT_ID', '=', 'events.id');
            })
            ->whereBetween('created_at', [$from, $to])
            ->select("EVNT_NAME", "EVAT_STTS", "USER_NAME", "event_payments.*");

        if ($user > 0) {

            $query = $query->where("EVPY_USER_ID", $user);
        }

        return $query->get();
    }

    public static function getUserEventPayments($userID)
    {
        return self::join("app_users", "app_users.id", "=", "EVPY_USER_ID")->join("events", 'events.id', '=', 'EVPY_EVNT_ID')
            ->leftJoin('events_attendance', function ($join) {
                $join->on('EVAT_USER_ID', '=', 'app_users.id')->on('EVAT_EVNT_ID', '=', 'events.id');
            })->select("EVNT_NAME", "EVAT_STTS", "USER_NAME", "event_payments.*")
            ->where("EVPY_USER_ID", $userID)->get();
    }

    public static function addPayment($userID, $eventID, $amount)
    {
        $payment = new EventPayment();
        $payment->EVPY_USER_ID = $userID;
        $payment->EVPY_EVNT_ID = $eventID;
        $payment->EVPY_AMNT = $amount;
        $user = User::findOrFail($userID);
        $event = Event::findOrFail($eventID);
        Payment::sendPaymentSMS($user->USER_NAME, $user->USER_MOBN, $amount, $event->EVNT_NAME);
        return $payment->save();
    }

    public static function deletePayments($userID, $eventID)
    {
        $eventPayments = self::where([['EVPY_USER_ID', $userID], ['EVPY_EVNT_ID', $eventID]])->get();
        foreach ($eventPayments as $payment)
            $payment->refund();
    }

    public function refund()
    {

        $user = User::findOrFail($this->EVPY_USER_ID);
        $event = Event::findOrFail($this->EVPY_EVNT_ID);
        Payment::sendPaymentSMS($user->USER_NAME, $user->USER_MOBN, $this->EVPY_AMNT, $event->EVNT_NAME, true);
        return $this->delete();
    }
}
