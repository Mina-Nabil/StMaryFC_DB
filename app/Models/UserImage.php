<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserImage extends Model
{
    protected $table = "app_user_images";
    public $timestamps = false;
    

    public function user(){
        return $this->belongsTo('App\Models\User', 'USIM_USER_ID');
    }

    public function deleteImage(){
        if($this->user->USER_MAIN_IMGE == $this->id){
            $this->user->USER_MAIN_IMGE = null;
            $this->user->save();
        }
        unlink(public_path('storage/' . $this->USIM_URL));
        $this->delete();
        return;
    }
}
