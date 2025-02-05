<?php

namespace App\Http\Controllers;

use App\Models\Rols;
use App\Http\Controllers\Responses\ApiResponse;

class RolsController extends Controller
{
    public function index()
    {
        try {
            $rols=Rols::all();
            if(!$rols){
                return ApiResponse::NotFound();
            }
            return ApiResponse::success('isSuccess',$rols,200);
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }
}
