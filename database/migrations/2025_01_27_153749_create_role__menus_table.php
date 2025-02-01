<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Crear la tabla menu_rol
        Schema::create('menu_rol', function (Blueprint $table) {
            $table->id(); // Clave primaria
            $table->unsignedBigInteger('rol_id'); // Clave foránea hacia rols
            $table->unsignedBigInteger('menu_id'); // Clave foránea hacia menus
            $table->foreign('rol_id')->references('id')->on('rols')->onDelete('cascade');
            $table->foreign('menu_id')->references('id')->on('menus')->onDelete('cascade');

            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar la tabla menu_rol
        Schema::dropIfExists('menu_rol');
    }
};
