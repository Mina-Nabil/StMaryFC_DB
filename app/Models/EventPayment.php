<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class EventPayment extends Model
{
    protected $table = "event_payments";

    function event()
    {
        return $this->belongsTo('App\Models\Event', "EVPY_EVNT_ID");
    }

    function user()
    {
        return $this->belongsTo('App\Models\User', "EVPY_USER_ID");
    }

    public static function addPayment($userID, $eventID, $amount)
    {
        $payment = new EventPayment();
        $payment->EVPY_USER_ID = $userID;
        $payment->EVPY_EVNT_ID = $eventID;
        $payment->EVPY_AMNT = $amount;
        $user = User::findOrFail($userID);
        $event = Event::findOrFail($eventID);
        Payment::sendSMS($user->USER_MOBN, $user->USER_NAME, $amount, $event->EVNT_NAME);
        return $payment->save();
    }

    public static function deletePayments($userID, $eventID)
    {
        return self::where([['EVPY_USER_ID', $userID], ['EVPY_EVNT_ID', $eventID]])->delete();
    }
}
