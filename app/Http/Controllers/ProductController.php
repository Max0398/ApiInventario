<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Responses\ApiResponse;
use App\Http\Requests\StoreProductsRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        try {
            $products = Product::where('active', 1)->get();
            if (!$products) {
                return ApiResponse::NotFound('Products not found', [], 404);
            }

            //aqui devuelve el producto con mas detalles como la categoria y todo ese rollo
            $data = $products->map(function ($product) {
                $descripcion = $product->categories ? $product->categories->name : "none";
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image_path' => $product->image_path ? asset('storage/' . $product->image_path) : null,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'category_id' => $product->category_id,
                    'description' => $descripcion,
                    'active' => $product->active,
                ];
            });
            return ApiResponse::Success('isSuccess', $data,200);

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public function store(StoreProductsRequest $request)
    {
        try{
            $valid = Validator::make($request->all(),[
                'name'=>['required','string','max:50'],
                'description' =>['string','max:150'],
                'price'=>['required','numeric'],
                'stock'=>['required','integer','min:0'],
                'category_id'=>['required | exists:category,id'],
                'active'=>['required','boolean'],
            ]);
            if($valid-> fails()){
                $data=[
                    'message'=>'Error en la validacion de los datos',
                    'errors' => $valid->errors(),
                    'status' => 400,
                ];
                return response()->json($data, 400);
            }
            /* Estructurar los datos */
                $newProduct = Product::create($request->all());
        /****        'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price, // encriptar el password
                'stock' => $request->stock,
                'image_path' => $request->image_path,
                'active' => $request->active,
                'category_id' => $request->category_id,
           ***/


            if(!$newProduct){
               $data=[
                     'message'=>'Error en la creacion del producto',
                     'status'=>500,
                ];
                return response()->json($data,500);
            }
            $data=[
                'producto'=>$newProduct,
                'status'=>201,
            ];
            return response()->json($data,201);
        }
        catch (\Exception $exception){
            return ApiResponse::error('Error',$exception->getMessage(),[] ,500);
        }

    }

    public function show(string $id)
    {
        //aunque en angular se puede filtrar
       try{
           $products = Product::find($id);

           if (!$products) {
               return ApiResponse::NotFound('Product not found', [], 404);
           }
           $descripcion = $products->categories ? $products->categories->name : "none";

           $data = [
               'id' => $products->id,
               'name' => $products->name,
               'price' => $products->price,
               'stock' => $products->stock,
               'category_id' => $products->category_id,
               'description' => $descripcion,
               'active' => $products->active,
           ];

           return ApiResponse::Success('isSuccess', $data, 200);

       }catch (\Exception $exception){
           return $exception->getMessage();
       }
    }

    public function update(UpdateProductRequest $request, $category_id)
    {
        try {
            $product = Product::find($category_id);
            if (!$product) {
                return ApiResponse::NotFound('Product not found', [], 404);
            }

            $valid = $request->validated();

            $product->update($valid);
            $product->save();

            $descripcion = $product->categories ? $product->categories->name : "none";

            // Preparar los datos de respuesta
            $data = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'stock' => $product->stock,
                'category_id' => $product->category_id,
                'description' => $descripcion,
                'active' => $product->active,
                'image_path' => $product->image_path ? asset('storage/' . $product->image_path) : null,
            ];

            // Devolver respuesta exitosa
            return ApiResponse::success('Product updated successfully', $data, 200);
        } catch (\Exception $exception) {
            // Devolver respuesta de error
            return ApiResponse::Error('An error occurred while updating the product', [], 500);
        }
    }

    public function destroy(string $id)
    {
        try {

            $product = Product::find($id);
            if(!$product){
                return ApiResponse::NotFound('Product not found', [], 404);
            }
            $product->active = false;
            $product->save();
            $data = [
                'product' => $product,
            ];
            return ApiResponse::success('isSuccess', $data);

        }catch (\Exception $exception){
            return $exception->getMessage();
        }
    }


}
