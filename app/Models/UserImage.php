<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;

class UserImage extends Model
{
    protected $table = "app_user_images";
    public $timestamps = false;


    public function user()
    {
        return $this->belongsTo('App\Models\User', 'USIM_USER_ID');
    }

    public function deleteImage()
    {
        if ($this->user->USER_MAIN_IMGE == $this->id) {
            $this->user->USER_MAIN_IMGE = null;
            $this->user->save();
        }
        try {
            unlink(public_path('storage/' . $this->USIM_URL));
        } catch (Exception $e) {
        }
        $this->delete();
        return;
    }

    public function compress()
    {
        if (!$this->USIM_CMPS) {
            $ext = last(explode('.', $this->USIM_URL));
            $imagePath = public_path('storage/' . $this->USIM_URL);
            if ($ext == 'png') {
                $image = imagecreatefrompng($imagePath);
                imagejpeg($image, $imagePath, 60);
            } else if ($ext == 'jpg' || $ext == 'jpeg') {
                $image = imagecreatefromjpeg(public_path('storage/' . $this->USIM_URL));
                imagejpeg($image, $imagePath, 60);
            }

            $this->USIM_CMPS = 1;
            $this->save();
        }
    }
}
