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
                'customer' => function ($query) {
                    $query->select('id', 'name');
                },
                'products' => function ($query) {
                    $query->select('products.id as product_id', 'products.name', 'products.price')
                        ->withPivot('quantity', 'subtotal');
                }
            ]);

            // Filtrar por nombre de cliente si se proporciona
            if ($request->has('customer_name')) {
                $customerName = $request->input('customer_name');
                $ordersQuery->whereHas('customer', function ($query) use ($customerName) {
                    $query->where('name', 'like', '%' . $customerName . '%');
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
                    'customer_name' => $order->customer->name,
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
            $total = 0;
            $orderData = [];

            foreach ($request->products as $product) {
                $productData = Product::find($product['id']);
                if ($productData->stock < $product['quantity']) {
                    DB::rollBack();
                    return response()->json(['message' => 'Not enough stock for product: ' . $productData->name], 400);
                }
                // Calcular el subtotal
                $subTotal = $productData->price * $product['quantity'];

                // Verificar si el producto ya existe en $orderData
                if (isset($orderData[$product['id']])) {
                    // Si existe, sumara la cantidad y el subtotal
                    $orderData[$product['id']]['quantity'] += $product['quantity'];
                    $orderData[$product['id']]['subTotal'] += $subTotal;
                } else {
                    // Si no existe, se agregara
                    $orderData[$product['id']] = [
                        'quantity' => $product['quantity'],
                        'subTotal' => $subTotal,
                    ];
                }

                // Descontar el stock del producto
                $productData->decrement('stock', $product['quantity']);
                // Sumar al total
                $total += $subTotal;
            }

            // Crear la orden
            $order = Order::create([
                'customer_id' => $request->customer_id,
                'status' => $request->status,
                'total' => $total,
            ]);
            // Asociar los productos a la orden
            $order->products()->attach($orderData);

            // Cargar los detalles de la orden
            $orderWithDetails = $order->load([
                'products' => function ($query) {
                    $query->select('products.id as product_id', 'products.name', 'products.price')
                        ->withPivot('quantity', 'subTotal');
                },
                'customer' => function ($query) {
                    $query->select('id', 'name');
                }
            ]);

            // Preparar la respuesta
            $data = [
                'order_id' => $orderWithDetails->id,
                'status' => $orderWithDetails->status,
                'total' => $orderWithDetails->total,
                'customer_name' => $orderWithDetails->customer->name,
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
