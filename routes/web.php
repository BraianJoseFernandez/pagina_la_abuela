<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PublicMenuController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Rotisería La Abuela
|--------------------------------------------------------------------------
*/

// Menú Público
Route::get('/', [PublicMenuController::class, 'index'])->name('home');
Route::get('/categoria/{slug}', [PublicMenuController::class, 'getCategory'])->name('menu.category');
Route::post('/pedido/guardar', [PublicMenuController::class, 'saveOrder'])->name('order.save');

// Autenticación
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Panel Administrativo y Personal
Route::middleware(['auth', 'role:admin,personal'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard general
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Gestión de Categorías
    Route::post('categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');
    Route::post('categories/images/reorder', [CategoryController::class, 'reorderImages'])->name('categories.images.reorder');
    Route::resource('categories', CategoryController::class);
    Route::delete('categories/image/{image}', [CategoryController::class, 'deleteImage'])->name('categories.delete-image');

    // Gestión de Productos / Carta
    Route::post('products/reorder', [ProductController::class, 'reorder'])->name('products.reorder');
    Route::resource('products', ProductController::class);
    Route::post('products/{product}/toggle-availability', [ProductController::class, 'toggleAvailability'])->name('products.toggle-availability');
    Route::post('products/{product}/quick-price', [ProductController::class, 'quickPriceUpdate'])->name('products.quick-price');

    // Gestión de Sección Eventos y Promociones
    Route::get('events', [EventController::class, 'index'])->name('events.index');
    Route::post('events', [EventController::class, 'update'])->name('events.update');

    // Historial de Pedidos WhatsApp
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
    Route::delete('orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');

    // Configuración del Negocio
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');

    // Gestión de Usuarios (Exclusivo Administrador)
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class);
    });
});
