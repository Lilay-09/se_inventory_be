<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use DB;

class Utility //extends Model
{
    // use HasFactory;
    static function saveImage($base64Image,$user_class,$directory){
        $decodedImage = base64_decode($base64Image);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filePath = $directory . '/' . $user_class;

        file_put_contents($filePath, $decodedImage);

        return $filePath;
    }
}
