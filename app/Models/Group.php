<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    public $timestamps = false;

    public function users()
    {
        return $this->hasMany('App\Models\User', "USER_GRUP_ID");
    }


    function toggle(){
        if($this->GRUP_ACTV) {
            $this->GRUP_ACTV = 0;
        } else {
            $this->GRUP_ACTV = 1;
        }
        return $this->save();
    }
}
