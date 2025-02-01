<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Responses\ApiResponse;
use App\Http\Requests\StoreProductsRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;

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
            $valid = $request->validated();
            //guardar la imagen en el storage
            if($request->hasFile('image_path')){
                //guardara en storage/app/public/products
                $imagePath = $request->file('image_path')->store('images', 'public');
                $valid['image_path'] = $imagePath; //agregara la ruta validada
            }

            $product= Product::create($valid);
            if(!$product){
                return ApiResponse::error('Products not create',[],404);
            }
            $data=[
                'product'=>$product,
            ];
            return ApiResponse::success('isSuccess',$data);
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
            ];

            // Devolver respuesta exitosa
            return ApiResponse::success('Product updated successfully', $data, 200);
        } catch (\Exception $exception) {
            // Devolver respuesta de error
            return ApiResponse::error('An error occurred while updating the product', [], 500);
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
