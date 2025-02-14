<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Responses\ApiResponse;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;


class OrderController extends Controller
{
    public function index(Request $request)
    {
        //cargar las relaciones entre , customer,order,producto solo los campos necesarios
        try {
            // Iniciar la consulta con las relaciones cargadas
            $ordersQuery = Order::with([
                'user' => function ($query) {
                    $query->select('id', 'name');
                },
                'products' => function ($query) {
                    $query->select('products.id as product_id', 'products.name', 'products.price')
                        ->withPivot('quantity', 'subtotal');
                }
            ]);

            // Filtrar por nombre de cliente si se proporciona
            if ($request->has('user_name')) {
                $userName = $request->input('user_name');
                $ordersQuery->whereHas('user', function ($query) use ($userName) {
                    $query->where('name', 'like', '%' . $userName . '%');
                });
            }

            // Filtrar por rango de fechas si se proporcionan ambas fechas
            if ($request->has('start_date') && $request->has('end_date')) {
                $startDate = Carbon::parse($request->input('start_date'))->startOfDay(); // Convertir a inicio del día
                $endDate = Carbon::parse($request->input('end_date'))->endOfDay(); // Convertir a fin del día
                $ordersQuery->whereBetween('created_at', [$startDate, $endDate]);
            }

            $orders = $ordersQuery->get();

            // Mapear las columnas con la tabla intermedia y retornar una respuesta mas clara con el json
            $data = $orders->map(function ($order) {
                return [
                    'order_id' => $order->id,
                    'status' => $order->status,
                    'total' => $order->total,
                    'order_date' => $order->created_at->format('Y-m-d H:i:s'), // Formato corto
                    'user_name' => $order->user->name,
                    'products' => $order->products->map(function ($product) {
                        return [
                            'name' => $product->name,
                            'quantity' => $product->pivot->quantity,
                            'subtotal' => $product->pivot->subtotal,
                            'price' => $product->price,
                        ];
                    }),
                ];
            });

            return ApiResponse::Success('isSuccess', $data, 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving orders', 'error' => $e->getMessage()], 500);
        }
    }

    public function createOrder(StoreOrderRequest $request)
    {
        DB::beginTransaction();
        try {
            $orderData = [];

            foreach ($request->products as $product) {
                $productData = Product::find($product['id']);
                if (!$productData || $productData->stock < $product['quantity']) {
                    DB::rollBack();
                    return response()->json(['message' => 'Not enough stock for product: ' . ($productData->name ?? 'Unknown')], 400);
                }

                // Guardar los datos enviados ya calculados
                $orderData[$product['id']] = [
                    'quantity' => $product['quantity'],
                    'subtotal' => $product['subtotal'], // Guardar directamente desde el subTotal del frontend
                ];

                // Descontar stock con verificación de concurrencia
                $updatedRows = Product::where('id', $product['id'])
                    ->where('stock', '>=', $product['quantity'])
                    ->decrement('stock', $product['quantity']);

                if ($updatedRows === 0) {
                    DB::rollBack();
                    return response()->json(['message' => 'Stock inconsistency detected for product: ' . $productData->name], 400);
                }
            }

            // Crear la orden con el total enviado desde el frontend
            $order = Order::create([
                'user_id' => $request->user_id,
                'status' => $request->status,
                'total' => $request->total, // Se usa el total enviado por el frontend
            ]);

            // Asociar los productos a la orden con los datos enviados
            $order->products()->attach($orderData);

            // Cargar los detalles de la orden
            $orderWithDetails = $order->load([
                'products' => function ($query) {
                    $query->select('products.id as product_id', 'products.name', 'products.price')
                        ->withPivot('quantity', 'subTotal');
                },
                'user' => function ($query) {
                    $query->select('id', 'name');
                }
            ]);

            // Preparar la respuesta
            $data = [
                'order_id' => $orderWithDetails->id,
                'status' => $orderWithDetails->status,
                'total' => $orderWithDetails->total,
                'username' => $orderWithDetails->user->name,
                'products' => $orderWithDetails->products->map(function ($product) {
                    return [
                        'name' => $product->name,
                        'quantity' => $product->pivot->quantity,
                        'subTotal' => $product->pivot->subTotal,
                        'price' => $product->price,
                    ];
                }),
            ];

            DB::commit();
            return ApiResponse::Success('Order created successfully', $data, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating order', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateOrderRequest $request, $orderId)
    {
        try {
            $order = Order::find($orderId);
            if (!$order) {
                return ApiResponse::Success('Order not found', [], 404);
            }
            //Aqui solo actualiza el status ya sea cancelada , completada o pendiente
            $order->update([
                'status' => $request->status,
            ]);

            $data = [
                'order_id' => $order->id,
                'status' => $order->status,
                'message' => 'isSuccess',
            ];

            return ApiResponse::Success('Order updated successfully', $data, 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating order', 'error' => $e->getMessage()], 500);
        }
    }

}
