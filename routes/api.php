<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchasingController;
use Illuminate\Support\Facades\Route;

Route::get('/products/categories', [ProductController::class, 'getCategories']);
Route::get('/products/units', [ProductController::class, 'getUnits']);
Route::get('/purchasing/suppliers', [PurchasingController::class, 'getSuppliers']);
Route::get('/purchasing/products', [PurchasingController::class, 'getProducts']);
Route::get('/purchasing/products/{id}', [PurchasingController::class, 'fetchDetails']);
