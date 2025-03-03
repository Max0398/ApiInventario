<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadImageController extends Controller
{
    //
    public function store(Request $request){
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        $path = Storage::putFile('images', $request->file('image'), 'public');
        $data = [
            'value' => $path,
            'status' => 200,
        ];
        // Devolver la ruta de la imagen subida
        return response()->json($data, 200);
    }



/****        if($request->hash_file('image')){
            $image= new uploadImage;
            $image->upload($request->$file('image'));
            return response()->json(['url' => Storage::url($image->name)]);
        } else {
            return response()->json(['error' => 'No file provided'], 422);
        }***/


}
