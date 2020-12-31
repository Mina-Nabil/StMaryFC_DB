<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventPayment extends Model
{
    protected $table = "event_payments";

    function event()
    {
        return $this->belongsTo('App/Models/Event');
    }

    public static function addPayment($userID, $eventID, $amount)
    {
        $payment = new EventPayment();
        $payment->EVPY_USER_ID = $userID;
        $payment->EVPY_EVNT_ID = $eventID;
        $payment->EVPY_AMNT = $amount;
        return $payment->save();
    }
}
