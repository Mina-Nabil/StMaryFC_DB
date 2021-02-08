<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventsAttendance extends Model
{
    protected $table = "events_attendance";
    public $timestamps = false;

    public static function getStatusName($id){
        switch ($id) {
            case 0:
                return "None";
            case 1:
                return "Paid";
            case 2:
                return "Received";
            case 3:
                return "Ok";
            
            default:
            return "None";
        }
    }
}
