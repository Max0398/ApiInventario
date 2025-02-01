<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Responses\ApiResponse;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{

    public function index()
    {
        try{
            $categories = Category::all();
            if(!$categories){
                return ApiResponse::NotFound('Category Not Found');
            }
            return ApiResponse::Success('Category List',$categories);
        }
        catch(\Exception $e){
            return ApiResponse::Error($e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {

            $valid = $request->validated();
            $category= Category::create($valid);

            if(!$category){
                return ApiResponse::Error('Category Not Registered');
            }
            return ApiResponse::Success('Category Registered' ,$category);

        }catch (\Exception $e){
            return ApiResponse::Error($e->getMessage());
        }
    }

}
