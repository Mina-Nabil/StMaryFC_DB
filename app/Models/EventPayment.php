<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class EventPayment extends Model
{
    protected $table = "event_payments";

    public function getCreatedAtAttribute($date)
    {
        return Carbon::createFromFormat('Y-m-dTH:i:sZ', $date)->format('Y-m-d');
    }

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
        return $payment->save();
    }
}
