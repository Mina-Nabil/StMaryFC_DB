<?php

namespace App\Models;

use Carbon\Carbon;
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

    function attendance()
    {
        return $this->hasOneThrough(EventsAttendance::class, Event::class, "EVPY_EVNT_ID", "EVAT_EVNT_ID");
    }

    public static function addPayment($userID, $eventID, $amount)
    {
        $payment = new EventPayment();
        $payment->EVPY_USER_ID = $userID;
        $payment->EVPY_EVNT_ID = $eventID;
        $payment->EVPY_AMNT = $amount;
        $user = User::findOrFail($userID);
        $event = Event::findOrFail($eventID);
        Payment::sendSMS($user->USER_NAME, $user->USER_MOBN, $amount, $event->EVNT_NAME);
        return $payment->save();
    }

    public static function deletePayments($userID, $eventID)
    {
        return self::where([['EVPY_USER_ID', $userID], ['EVPY_EVNT_ID', $eventID]])->delete();
    }
}
