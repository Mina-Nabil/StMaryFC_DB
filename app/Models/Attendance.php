<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class Attendance extends Model
{
    protected $table = "attendance";
    public $timestamps = false;

    protected $dates = ['ATND_DATE'];

    public static function takeAttendace($userID, $date)
    {
        return self::insert([
            'ATND_DATE' => $date,
            'ATND_USER_ID' => $userID,
        ]);
    }
}
