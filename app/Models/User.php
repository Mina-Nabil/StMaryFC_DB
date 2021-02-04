<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $table = "app_users";
    public $timestamps = false;

    public function images(){
        return $this->hasMany('App\Models\UserImage', "USIM_USER_ID");
    }
    public function mainImage(){
        return $this->hasOne('App\Models\UserImage', 'id', "USER_MAIN_IMGE");
    }

    public function type(){
        return $this->belongsTo('App\Models\UserType', 'USER_USTP_ID');
    }

    public function group(){
        return $this->belongsTo('App\Models\Group', 'USER_GRUP_ID');
    }

    public function attendance(){
        return $this->hasMany('App\Models\Attendance', "ATND_USER_ID");
    }

    public function payments(){
        return $this->hasMany('App\Models\Payment', "PYMT_USER_ID");
    }

    public function eventPayments(){
        return $this->hasMany('App\Models\EventPayment', "EVPY_USER_ID");
    }

    public function getLatestAttendance(){
        return Attendance::where('ATND_USER_ID', $this->id)->orderByDesc('ATND_DATE')->limit(200)->get();
    }

    public function getAttendedYears(){
        return Attendance::where('ATND_USER_ID', $this->id)->selectRaw("DISTINCT YEAR(ATND_DATE) as year")->get();
    }

    public function monthlyAttendance(){
        return $this->attendance()->whereRaw("MONTH(ATND_DATE) = MONTH(CURDATE())")->count();        
    }

    public function monthlyPayment(){
        return $this->payments()->whereRaw("MONTH(PYMT_DATE) = MONTH(CURDATE())")->sum('PYMT_AMNT');
    }

    public function getLatestPayments($months=1){
        return $this->payments()->whereRaw(" PYMT_DATE > DATE_SUB(NOW(),  Interval {$months} Month) ")
                    ->groupByRaw(" MONTH(PYMT_DATE) ")->groupByRaw("YEAR(PYMT_DATE) ")->selectRaw(" MONTH(PYMT_DATE) as OVRV_MNTH , YEAR(PYMT_DATE) as OVRV_YEAR, SUM(PYMT_AMNT) as OVRV_PAID ")->get();

    }

    public function getOverviewAttendance($months=1){
        return $this->attendance()->whereRaw(" ATND_DATE > DATE_SUB(NOW(),  Interval {$months} Month) ")
                    ->groupByRaw(" MONTH(ATND_DATE) ")->groupByRaw("YEAR(ATND_DATE) ")->selectRaw(" MONTH(ATND_DATE) as OVRV_MNTH , YEAR(ATND_DATE) as OVRV_YEAR, COUNT(attendance.id) as OVRV_ATND ")->get();
    }


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'USER_NAME', 'USER_USTP_ID', 'USER_PASS', 'USER_MAIL', 'USER_MAIN_IMGE', 'USER_FACE_ID', 'USER_CLASS_NAME', 'USER_BDAY'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'USER_PASS'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'USER_BDAY' => 'datetime:Y-m-d',
    ];

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'USER_NAME';
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->USER_PASS;
    }

        // this is a recommended way to declare event handlers
        public static function boot() {
            parent::boot();
    
            static::deleting(function($user) { // before delete() method call this
                 $user->attendance()->delete();
                 $user->payments()->delete();
                 // do the rest of the cleanup...
            });
        }
}
