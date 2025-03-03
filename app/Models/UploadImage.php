<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Svg\Tag\Image;

class UploadImage extends Model
{
    //
    protected $fillable =['name','path'];

    public function uploadImage($file, $name){

    }
}
