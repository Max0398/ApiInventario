<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Controllers\Responses\ApiResponse;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{

    public function index()
    {
        try {

            $customer = Customer::where('active', 1)->get();
            if (!$customer) {
                return ApiResponse::NotFound('Not Found', [], 404);
            }
            $data = $customer->map(function ($customer) {
                return [
                    'id'=>$customer->id,
                    'name' => $customer->name,
                    'last_name' => $customer->last_name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'address' => $customer->address,
                    'active' => $customer->active,
                ];
            });
            return ApiResponse::Success('isSuccess', $data, 200);
        } catch (\Exception $e) {
            return response()->json([$e->getMessage()]);
        }
    }

    public function store(StoreCustomerRequest $request)
    {
        try {
            $valid = $request->validated();
            $customer = Customer::create($valid);

            if (!$customer) {
                return ApiResponse::NotFound('Error en el Registro', [], 500);
            }

            return ApiResponse::Success('isSuccess', $customer, 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function show($Customerid)
    {
        try {

            $customer = Customer::find($Customerid);
            if (!$customer) {
                return ApiResponse::NotFound('Not Found', [], 404);
            }
            return ApiResponse::Success('isSuccess', $customer, 200);

        } catch (\Exception $e) {
            return response()->json([$e->getMessage()]);
        }
    }

    public function update(UpdateCustomerRequest $request, $Customerid)
    {
        try {
            $customer = Customer::find($Customerid);
            if (!$customer) {
                return response()->json(['error' => 'Customer not found'], 404);
            }
            $valid = $request->validated();
            $customer->update($valid);
            $data = ['customer' => $customer];
            return ApiResponse::Success('Customer updated successfully', $data, 200);
        } catch (\Exception $e) {
            return response()->json([$e->getMessage()]);
        }
    }


    public function destroy($Customerid)
    {
        try {
            $delete = Customer::find($Customerid);
            if (!$delete) {
                return ApiResponse::NotFound('Not Found', [], 404);
            }
            $delete->active=false;
            $delete->save();
            return ApiResponse::Success('isSuccess', $delete, 200);
        } catch (\Exception $e) {
            return response()->json([$e->getMessage()]);
        }
    }
}
