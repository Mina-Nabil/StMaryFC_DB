<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Attendance extends Model
{
    protected $table = "attendance";
    public $timestamps = false;

    protected $dates = ['ATND_DATE'];

    public static function getAttendance($from, $to, $user = 0, $group = 0)
    {
        $query = DB::table('attendance')
        ->join('app_users', 'app_users.id', '=', 'ATND_USER_ID')
        ->leftjoin('app_users as taken_users', 'taken_users.id', '=', 'ATND_TAKN_ID')
        ->join('groups', 'groups.id', '=', 'app_users.USER_GRUP_ID')
            ->whereBetween('ATND_DATE', [$from, $to]);
        if ($user != 0) {
            $query = $query->where('ATND_USER_ID', $user);
        } else if ($group != 0) {
            $query = $query->where('USER_GRUP_ID', $group);
        }
        return $query->select('attendance.id', 'ATND_DATE', 'ATND_PAID', 'GRUP_NAME', 'ATND_USER_ID', 'ATND_TAKN_ID')
        ->selectRaw('app_users.USER_NAME as ATND_NAME')
        ->selectRaw('taken_users.USER_NAME as TAKN_NAME')
            ->orderByDesc('ATND_DATE')
            ->get();
    }

    public static function getAttendanceLite($from, $to, $user = 0)
    {
        $query = DB::table('attendance')->whereBetween('ATND_DATE', [$from, $to]);
        if ($user != 0) {
            $query = $query->where('ATND_USER_ID', $user);
        }
        return $query->select('attendance.id')->get();
    }

    public static function getDuePayments()
    {
        return DB::table('attendance')->join('app_users', "app_users.id", '=', "ATND_USER_ID")
            ->where('ATND_PAID', 0)
            ->selectRaw("DISTINCT USER_NAME, ATND_USER_ID , DATE_FORMAT(ATND_DATE, '%Y-%M') as paymentDue")
            ->get();
    }

    public static function takeAttendace($userID, $date)
    {
        if (!self::hasAttendance($userID, $date)) {

            $isPaid = Payment::didPayMonth($userID, $date);

            return self::insert([
                'ATND_DATE' => $date,
                'ATND_PAID' => ($isPaid) ? 1 : 0,
                'ATND_USER_ID' => $userID,
                'ATND_TAKN_ID' => Auth::id(),
            ]);
        } else return 0;
    }

    public static function setPaid($userID, $startDate, $endDate)
    {
        return DB::table('attendance')->where('ATND_USER_ID', $userID)->whereBetween('ATND_DATE', [$startDate, $endDate])->update([
            "ATND_PAID" => 1
        ]);
    }

    public static function getUnpaidDays($userID)
    {
        return DB::table('attendance')->where([['ATND_USER_ID', '=', $userID], ['ATND_PAID', '=',  0]])->selectRaw('id, DATE_FORMAT(ATND_DATE, "%d-%M") as date')->get();
    }

    public static function hasAttendance($userID, $date)
    {
        return  DB::table('attendance')->whereDate('ATND_DATE', $date)->where('ATND_USER_ID', $userID)->get()->count();
    }

    ///relations
    public function user(): BelongsTo
    {
        return $this->belongsTo("App\Models\User", "ATND_USER_ID");
    }
    public function taken_by(): BelongsTo
    {
        return $this->belongsTo("App\Models\User", "ATND_TAKN_ID");
    }
}
