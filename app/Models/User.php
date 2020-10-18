<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

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

    public function getLatestAttendance(){
        return Attendance::where('ATND_USER_ID', $this->id)->orderByDesc('ATND_DATE')->limit(200)->get();
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
        'password'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'USER_BDAY' => 'datetime',
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
}
