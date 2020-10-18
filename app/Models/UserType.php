<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserType extends Model
{
    protected $table = "app_user_types";
    public $timestamps = false;

    public function users(){
        return $this->hasMany('App\Models\User', 'USER_USTP_ID');
    }
}
