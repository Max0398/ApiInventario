<?php

namespace App\Http\Controllers;

use Fpdf\Fpdf;
use App\Models\Order;

class TicketController extends Controller{
    public function TicketGenerate($order_id)
    {
        try {
            // Validar que el order_id sea un número entero válido y que exista en la base de datos
            if (!is_numeric($order_id)) {
                return response()->json(['message' => 'ID de orden inválido'], 400);
            }

            if (!$order = Order::find($order_id)) {
                return response()->json(['message' => 'Orden no encontrada'], 404);
            }

            // Cargar la orden con sus detalles
            $orderWithDetails = $order->load([
                'products' => function ($query) {
                    $query->select('products.id as product_id', 'products.name', 'products.price')
                        ->withPivot('quantity', 'subTotal');
                },
                'customer' => function ($query) {
                    $query->select('id', 'name', 'email', 'address', 'phone');
                }
            ]);

            // Preparar los datos para el ticket
            $venta = [
                'order_id' => $orderWithDetails->id,
                'status' => $orderWithDetails->status,
                'subTotal' => $orderWithDetails->subtotal,
                'total' => $orderWithDetails->total,
                'order_date' => $orderWithDetails->created_at->format('d/m/Y'),
                'order_time' => $orderWithDetails->created_at->format('H:i'),
                'customer_name' => $orderWithDetails->customer->name,
                'customer_email' => $orderWithDetails->customer->email,
                'customer_address' => $orderWithDetails->customer->address ?? 'No proporcionada',
                'customer_phone' => $orderWithDetails->customer->phone ?? 'No proporcionado',
                'products' => $orderWithDetails->products->map(function ($product) {
                    return [
                        'name' => $product->name,
                        'quantity' => $product->pivot->quantity,
                        'subTotal' => $product->pivot->subTotal,
                        'price' => $product->price,
                    ];
                }),
            ];

            // Instanciar FPDF con un ancho de 80 mm (sin altura fija)
            $pdf = new FPDF('P', 'mm', array(80,160));
            $pdf->AddPage();
            $pdf->SetMargins(5, 5, 5); // Márgenes pequeños
            $pdf->SetAutoPageBreak(true, 10); // AutoPageBreak activado con un margen inferior de 10 mm
            $pdf->SetFont('Arial', 'B', 9);


            if (file_exists('resources/busines.png')) {
                $pdf->Image( 'resources/busines.png', 15, 2, 45);
            }

            // Encabezado: Información de la empresa
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(70, 5, 'MI EMPRESA S.A. DE C.V.', 0, 1, 'C');
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(70, 5, 'Direccion: Calle Falsa 123, Col. Centro', 0, 1, 'C');
            $pdf->Cell(70, 5, 'Telefono: 555-123-4567', 0, 1, 'C');
            $pdf->Cell(70, 5, 'Email: contacto@miempresa.com', 0, 1, 'C');
            $pdf->Ln(2);

            // Línea separadora
            $pdf->Cell(70, 2, '-----------------------------------------------------', 0, 1, 'C');

            // Información del cliente
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(70, 5, 'Datos del Cliente', 0, 1, 'L');
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(70, 5, 'Nombre: ' . $venta['customer_name'], 0, 1, 'L');
            $pdf->Cell(70, 5, 'Email: ' . $venta['customer_email'], 0, 1, 'L');
            $pdf->Cell(70, 5, 'Direccion: ' . $venta['customer_address'], 0, 1, 'L');
            $pdf->Cell(70, 5, 'Telefono: ' . $venta['customer_phone'], 0, 1, 'L');
            $pdf->Ln(2);

            // Línea separadora
            $pdf->Cell(70, 2, '-----------------------------------------------------', 0, 1, 'C');

            // Detalles de la orden
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(70, 5, 'Detalles de la Orden', 0, 1, 'L');
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(35, 5, 'Orden Numero: ' . $venta['order_id'], 0, 0, 'L');
            $pdf->Cell(35, 5, 'Fecha: ' . $venta['order_date'], 0, 1, 'L');
            $pdf->Cell(35, 5, 'Hora: ' . $venta['order_time'], 0, 1, 'L');
            $pdf->Ln(2);

            // Línea separadora
            $pdf->Cell(70, 2, '-----------------------------------------------------', 0, 1, 'C');

            // Encabezado de tabla de productos
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(10, 4, 'Cant.', 0, 0, 'L');
            $pdf->Cell(30, 4, 'Descripcion', 0, 0, 'L');
            $pdf->Cell(15, 4, 'Precio', 2, 0, 'C');
            $pdf->Cell(15, 4, 'Sub Total', 0, 1, 'C');

            // Línea separadora de tabla
            $pdf->Cell(70, 2, '-----------------------------------------------------', 0, 1, 'C');

            $totalProductos = 0;
            $pdf->SetFont('Arial', '', 7);

            // Detalle de productos
            foreach ($venta['products'] as $producto) {
                $subTotal = number_format($producto['subTotal'], 2, '.', ',');
                $totalProductos += $producto['quantity'];

                $pdf->Cell(10, 4, $producto['quantity'], 0, 0, 'L');
                $yInicio = $pdf->GetY();
                $pdf->MultiCell(30, 4, $producto['name'], 0, 'L');
                $yFin = $pdf->GetY();

                $pdf->SetXY(45, $yInicio);
                $pdf->Cell(15, 4, 'C$ ' . number_format($producto['price'], 2, '.', ','), 0, 0, 'C');

                $pdf->SetXY(60, $yInicio);
                $pdf->Cell(15, 4, 'C$ ' . $subTotal, 0, 1, 'R');
                $pdf->SetY($yFin);
            }

            // Línea de total de productos
            $pdf->Ln();
            $pdf->Cell(70, 4, 'Total Articulos: ' . $totalProductos, 0, 1, 'L');

            // Total
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(70, 5, 'Total: C$ ' . number_format($venta['total'], 2, '.', ','), 0, 1, 'R');

            // Agradecimiento
            $pdf->Ln();
            $pdf->MultiCell(70, 5, 'AGRADECEMOS SU PREFERENCIA.¡VUELVA PRONTO!', 0, 'C');

            // Descargar el PDF generado
            return response($pdf->Output('S'), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="ticket_' . $order_id . '.pdf"');

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al generar el ticket', 'error' => $e->getMessage()], 500);
        }
    }
}

