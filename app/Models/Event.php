<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Event extends Model
{
    protected $table = "events";
    public $timestamps = false;

    function payments()
    {
        return $this->hasMany('App\Models\EventPayment', 'EVPY_EVNT_ID');
    }

    function users()
    {
        return $this->belongsToMany('App\Models\User', 'events_attendance', 'EVAT_USER_ID', 'EVAT_EVNT_ID')
            ->withPivot('EVAT_STTS');
    }

    function addPayment(User $user, $amount)
    {
        $this->payments()->create([
            "EVPY_USER_ID"  => $user->id,
            "EVPY_AMNT"     => $amount
        ]);
    }

    function addUser(User $user, $status = 0)
    {
        //1 7agaz bas - 2 7agaz w geh - 3 7agaz w cancel
        $this->users()->attach($user->id, ['EVAT_STTS' => $status]);
    }

    function getEventAttendance()
    {
        return DB::table('app_users')->join('groups', 'USER_GRUP_ID', '=', 'groups.id')
            ->leftJoin('events_attendance', 'EVAT_USER_ID', '=', 'app_users.id')
            ->leftJoin('events', 'EVAT_EVNT_ID', '=', 'events.id')
            ->leftJoin('event_payments', 'EVPY_EVNT_ID', '=', 'events.id')
            ->selectRaw('EVNT_NAME, USER_NAME, SUM(EVPY_AMNT) as EVPY_PAID, EVAT_STTS, GRUP_NAME, app_users.id as USER_ID, events.id as EVNT_ID')
            ->orderByRaw('ABS(USER_CODE)')
            ->groupBy('app_users.id', 'events.id', 'EVAT_EVNT_ID')
            ->whereNull('deleted_at')
            ->get();
    }

    function deleteAll(){
        DB::table('events_attendance')->where('EVAT_EVNT_ID', $this->id)->delete();
        DB::table('event_payments')->where('ECPY_EVNT_ID', $this->id)->delete();
        $this->delete();
    }
}
