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

    public function user()
    {
        return $this->belongsTo("App\Models\User", "ATND_USER_ID");
    }

    public static function getAttendance($from, $to, $user=0)
    {
   
        $query = DB::table('attendance')->join('app_users', 'app_users.id', '=', 'ATND_USER_ID')->join('groups', 'groups.id', '=', 'USER_GRUP_ID')
        ->whereBetween('ATND_DATE', [$from, $to]);
        if($user!=0){
            $query = $query->where('ATND_USER_ID', $user);
        }
        return $query->select('attendance.id', 'ATND_DATE', 'USER_NAME', 'GRUP_NAME')
            ->orderByDesc('ATND_DATE')
            ->get();
    }

    public static function takeAttendace($userID, $date)
    {
        return self::insert([
            'ATND_DATE' => $date,
            'ATND_USER_ID' => $userID,
        ]);
    }
}
