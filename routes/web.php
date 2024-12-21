<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', [\App\Http\Controllers\AuthController::class, 'login'])->name('login');
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'postLogin'])->name('post-login');

Route::get('/register', [\App\Http\Controllers\AuthController::class, 'register'])->name('register');
Route::post('/register', [\App\Http\Controllers\AuthController::class, 'postRegister'])->name('post-register');

Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index']);
    Route::prefix('/products')->group(function () {
        Route::get('/', [\App\Http\Controllers\ProductController::class, 'index'])->name('products.index');
        Route::post('/', [\App\Http\Controllers\ProductController::class, 'store'])->name('products.store');
        Route::get('/{products}', [\App\Http\Controllers\ProductController::class, 'edit'])->name('products.edit');  //with GET
        Route::put('/{products}', [\App\Http\Controllers\ProductController::class, 'update'])->name('products.update');  //with GET
    });
});
