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
        $quality = 20;
        // if (!$this->USIM_CMPS) {
        $ext = last(explode('.', $this->USIM_URL));
        $fileNoExt = str_replace('.' . $ext, '', $this->USIM_URL);
        $imagePath = public_path('storage/' . $this->USIM_URL);
        $newImagePath = $fileNoExt . '_' . $quality . '.' . $ext;
        echo "Extension: " . $ext . "\n";
        echo "FileNoExt: " . $fileNoExt . "\n";
        echo "Path: " . $imagePath . "\n";
        echo "New Path: " . $newImagePath . "\n";
        if ($ext == 'png') {
            $image = imagecreatefrompng($imagePath);
            imagejpeg($image, $newImagePath, $quality);
            $this->USIM_CMPS = 1;
            $this->USIM_URL = $newImagePath;
            $this->save();
            unlink($imagePath);
        } else if ($ext == 'jpg' || $ext == 'jpeg') {
            $image = self::imagecreatefromjpegexif($imagePath);
            echo "Gebt el image? \n";
            $data = getimagesize($imagePath);
            var_dump($data);
            echo "\n";
            echo imagejpeg($image, $newImagePath, $quality);
            echo "tab 3rft tnso5? \n";
            $this->USIM_CMPS = 1;
            $this->USIM_URL = $newImagePath;
            $this->save();
            echo "wa sayevt aho? \n";
            unlink($imagePath);
            echo "m32ola msa7t? \n";
        }


        // }
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


    // public function flip()
    // {

    //     $fileName = $this->USIM_URL;
    //     $ext = last(explode('.', $fileName));
    //     $imagePath = public_path('storage/' . $this->USIM_URL);
    //     $fileNoExt = str_replace('.' . $ext, '', $fileName);
    //     $newImagePath = $fileNoExt . '_rot' . '.' . $ext;
    //     if ($ext == 'png') {
    //         $image = imagecreatefrompng($imagePath);
    //         $image = imagerotate($image, -90, 0);
    //         imagejpeg($image, public_path('storage/' . $newImagePath), 50);
    //     } else if ($ext == 'jpg' || $ext == 'jpeg') {
    //         $image = self::imagecreatefromjpegexif($imagePath);
    //         $image = imageflip($image, IMG_FLIP_VERTICAL);
    //         imagejpeg($image, public_path('storage/' . $newImagePath), 50);
    //     }
    //     $this->USIM_URL = $newImagePath;
    //     $this->save();
    // }


    private static function imagecreatefromjpegexif($filename)
    {
        $img = imagecreatefromjpeg($filename);
        $exif = exif_read_data($filename);
        echo "exif: \n";
        var_dump($exif);
        if ($img && $exif && isset($exif['Orientation'])) {
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
