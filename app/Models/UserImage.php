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
                imagejpeg($image, $imagePath, 40);
            } else if ($ext == 'jpg' || $ext == 'jpeg') {
                $image = self::imagecreatefromjpegexif($imagePath);
                imagejpeg($image, $imagePath, 40);
            }

            $this->USIM_CMPS = 1;
            $this->save();
        }
    }

    // public function rotateImage()
    // {
    //     if ($this->USIM_USER_ID != 3 && $this->USIM_USER_ID != 1 && $this->USIM_USER_ID != 6 ) {
    //         $fileName = $this->USIM_URL;
    //         $ext = last(explode('.', $fileName));
    //         $fileNoExt = str_replace('.' . $ext, '', $fileName);
    //         $imagePath = public_path('storage/' . $this->USIM_URL);
    //         $newImagePath = $fileNoExt . '_rot' . '.' . $ext;
    //         if ($ext == 'png') {
    //             $image = imagecreatefrompng($imagePath);
    //             $image = imagerotate($image, -90, 0);
    //             imagejpeg($image, public_path('storage/' . $newImagePath), 50);
    //         } else if ($ext == 'jpg' || $ext == 'jpeg') {
    //             $image = self::imagecreatefromjpegexif($imagePath);
    //             $image = imagerotate($image, -90, 0);
    //             imagejpeg($image, public_path('storage/' . $newImagePath), 50);
    //         }
    //         $this->USIM_URL = $newImagePath;
    //         $this->save();
    //     }
    // }


    public function flip()
    {
        if ($this->USIM_USER_ID == 3 ) {
            $fileName = $this->USIM_URL;
            $ext = last(explode('.', $fileName));
            $fileNoExt = str_replace('.' . $ext, '', $fileName);
            $imagePath = public_path('storage/' . $this->USIM_URL);
            $newImagePath = $fileNoExt . '_rot' . '.' . $ext;
            if ($ext == 'png') {
                $image = imagecreatefrompng($imagePath);
                $image = imagerotate($image, -90, 0);
                imagejpeg($image, public_path('storage/' . $newImagePath), 50);
            } else if ($ext == 'jpg' || $ext == 'jpeg') {
                $image = self::imagecreatefromjpegexif($imagePath);
                $image = imagerotate($image, -180, 0);
                imagejpeg($image, public_path('storage/' . $newImagePath), 50);
            }
            $this->USIM_URL = $newImagePath;
            $this->save();
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
