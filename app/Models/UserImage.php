<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserImage extends Model
{
    protected $table = "app_user_images";
    public $timestamps = false;
    

    public function user(){
        return $this->belongsTo('App\Models\User', 'USER_USTP_ID');
    }
}
