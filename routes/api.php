<?php

use App\Http\Controllers\TicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RolsControler;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::prefix('user')->group(function () {

    Route::post('/register', [UserController::class, 'register'])->name('register');
    Route::post('/login', [UserController::class, 'login'])->name('login');
    Route::get('/listUsers', [UserController::class, 'listUsers'])->name('listUsers');

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/profile', [UserController::class, 'profile'])->name('profile');
        Route::post('/logout', [UserController::class, 'logout'])->name('logout');
    });
});
Route::prefix('rols')->group(function () {
    Route::get('/', [RolsController::class, 'index']);
});

//rutas de categoria
Route::prefix('category')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::post('/', [CategoryController::class, 'store']);
});

//ruta para cargar los menus del usuario
Route::prefix('menu')->group(function () {
    Route::get('/{user}', [MenuController::class, 'menuUser']);
});


// Rutas para clientes (customers)
Route::prefix('customers')->group(function () {
    Route::get('/', [CustomerController::class, 'index']);
    Route::post('/', [CustomerController::class, 'store']);
    Route::get('/{customer}', [CustomerController::class, 'show']);
    Route::put('/{customer}', [CustomerController::class, 'update']);
    Route::delete('/{customer}', [CustomerController::class, 'destroy']);
});

// Rutas para productos (products)
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::post('/store', [ProductController::class, 'store']);
    Route::get('/{product}', [ProductController::class, 'show']);
    Route::put('/{product}', [ProductController::class, 'update']);
    Route::delete('/{product}', [ProductController::class, 'destroy']);
});

// Rutas para ordenes (order)
Route::prefix('orders')->group(function () {
    Route::get('/',[OrderController::class, 'index']);
    Route::post('/', [OrderController::class, 'createOrder']);
    Route::get('/{order}', [OrderController::class, 'show']);
    Route::put('/{order}', [OrderController::class, 'update']);
});

Route::prefix('ticket')->group(function () {
    Route::get('generate/{order}', [TicketController::class, 'TicketGenerate']);
});

//Generar Ticket


