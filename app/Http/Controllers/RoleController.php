<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Http\Controllers\Responses\ApiResponse;

class RoleController extends Controller
{
    public function index()
    {
        try {
            $rols=Role::all();
            if(!$rols){
                return ApiResponse::NotFound();
            }
            return ApiResponse::success('isSuccess',$rols,200);
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }
}
