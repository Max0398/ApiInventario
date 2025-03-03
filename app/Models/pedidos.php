<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pedidos extends Model
{
    /** @use HasFactory<\Database\Factories\PedidosFactory> */
    use HasFactory;
}
Schema::create('pedidos', function (Blueprint $table) {
    $table->id();
    $table->integer('id_cliente')->unsigned();
    $table->string('nombre_cliente');
    $table->date('fecha');
    $table->decimal('monto', 10, 2);
    $table->decimal('impuesto', 10, 2);
    $table->decimal('total', 10, 2);
    $table->timestamps();
});

