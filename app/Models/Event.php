<?php

namespace App\Models;

use Exception;
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
        return $this->belongsToMany('App\Models\User', 'events_attendance', 'EVAT_EVNT_ID', 'EVAT_USER_ID')
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
        return DB::table('app_users', 'u1')->join('groups', 'USER_GRUP_ID', '=', 'groups.id')
            ->leftJoin('events_attendance', function ($join) {
                $join->on('EVAT_USER_ID', '=', 'u1.id');
                $join->where('EVAT_EVNT_ID', '=', $this->id) ;
            })
            ->leftJoin('events', function ($join){
                $join->on('EVAT_EVNT_ID' , '=', 'events.id') ;
                $join->where('events.id', '=', $this->id) ;
            })->selectRaw("EVNT_NAME, USER_NAME, (SELECT SUM(EVPY_AMNT) from event_payments where EVPY_USER_ID = u1.id AND EVPY_EVNT_ID = {$this->id} ) as EVPY_USER_AMNT  , EVAT_STTS, GRUP_NAME, u1.id as USER_ID, events.id as EVNT_ID")
            ->orderByRaw('ABS(USER_CODE)')
            ->groupBy('u1.id', 'events.id', 'EVAT_EVNT_ID')
            ->whereNull('deleted_at')
            ->get();
    }

    static function attachUser($eventID, $userID, $status){
        $event = Event::findOrFail($eventID);
        $exists = $event->users->contains($userID);
    
        if (!$exists)
            try {
                $event->users()->attach($userID, ['EVAT_STTS' => $status]);
                return 1;
            } catch (Exception $e) {
                report($e);
                return 0;
            }
        else
            try {
                return $event->users()->updateExistingPivot($userID, [
                    "EVAT_STTS" => $status
                ]);
            } catch (Exception $e) {
                report($e);
                return 0;
            }
    }

    function deleteAll(){
        DB::table('events_attendance')->where('EVAT_EVNT_ID', $this->id)->delete();
        DB::table('event_payments')->where('EVPY_EVNT_ID', $this->id)->delete();
        $this->delete();
    }
}
