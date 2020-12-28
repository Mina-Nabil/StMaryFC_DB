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
        return $this->belongsToMany('App\Models\Users', 'events_attendance', 'EVAT_USER_ID', 'EVAT_EVNT_ID')
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
        //0 7agaz bas - 1 7agaz w geh - 2 7agaz w cancel
        $this->users()->attach($user->id, ['EVAT_STTS' => $status]);
    }

    function getEventAttendance()
    {
        return DB::table('app_users')
            ->leftJoin('event_attendance', 'EVAT_USER_ID', '=', 'app_users.id')
            ->leftJoin('events', 'EVAT_EVNT_ID', '=', 'events.id')
            ->leftJoin('event_payments', 'EVPY_EVNT_ID', '=', 'events.id')
            ->selectRaw('EVNT_NAME, USER_NAME, SUM(EVPY_AMNT) as EVPY_PAID, EVAT_STTS')
            ->orderByRaw('ABS(USER_CODE)')
            ->groupBy('app_users.id', 'events.id')
            ->get();
    }

    function deleteAll(){
        DB::table('events_attendance')->where('EVAT_EVNT_ID', $this->id)->delete();
        DB::table('event_payments')->where('ECPY_EVNT_ID', $this->id)->delete();
        $this->delete();
    }
}
