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
                $image = self::imagecreatefromjpegexif(public_path('storage/' . $this->USIM_URL));
                imagejpeg($image, $imagePath, 60);
            }

            $this->USIM_CMPS = 1;
            $this->save();
        }
    }

    public function rotate()
    {
        if ($this->id != 3 && $this->id != 1 && $this->id != 6 ) {
            $ext = last(explode('.', $this->USIM_URL));
            $imagePath = public_path('storage/' . $this->USIM_URL);
            if ($ext == 'png') {
                $image = imagecreatefrompng($imagePath);
                $image = imagerotate($image, -90, 0);
                imagejpeg($image, $imagePath, 100);
            } else if ($ext == 'jpg' || $ext == 'jpeg') {
                $image = self::imagecreatefromjpegexif(public_path('storage/' . $this->USIM_URL));
                $image = imagerotate($image, -90, 0);
                imagejpeg($image, $imagePath, 100);
            }
        }
    }


    private static function imagecreatefromjpegexif($filename)
    {
        $img = imagecreatefromjpeg($filename);
        $exif = exif_read_data($filename);
        if ($img && $exif && isset($exif['Orientation']))
        {
            $ort = $exif['Orientation'];

            if ($ort == 6 || $ort == 5)
                $img = imagerotate($img, 270, null);
            if ($ort == 3 || $ort == 4)
                $img = imagerotate($img, 180, null);
            if ($ort == 8 || $ort == 7)
                $img = imagerotate($img, 90, null);

            if ($ort == 5 || $ort == 4 || $ort == 7)
                imageflip($img, IMG_FLIP_HORIZONTAL);
        }
        return $img;
    }
}
