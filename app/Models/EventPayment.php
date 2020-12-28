<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventPayment extends Model
{
    protected $table = "event_payments";

    function event(){
        return $this->belongsTo('App/Models/Event');
    }
}
