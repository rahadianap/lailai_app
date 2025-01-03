<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchasingController;
use App\Http\Controllers\SalesController;
use Illuminate\Support\Facades\Route;

Route::get('/products/categories', [ProductController::class, 'getCategories']);
Route::get('/products/units', [ProductController::class, 'getUnits']);
Route::get('/purchase-order/suppliers', [PurchaseOrderController::class, 'getSuppliers']);
Route::get('/purchase-order/products', [PurchaseOrderController::class, 'getProducts']);
Route::get('/purchase-order/products/{id}', [PurchaseOrderController::class, 'fetchDetails']);
Route::get('/purchasing/suppliers', [PurchasingController::class, 'getSuppliers']);
Route::get('/purchasing/products', [PurchasingController::class, 'getProducts']);
Route::get('/purchasing/po', [PurchasingController::class, 'getPO']);
Route::get('/purchasing/products/{id}', [PurchasingController::class, 'fetchDetails']);
Route::get('/purchasing/po/{id}', [PurchasingController::class, 'fetchPODetails']);
Route::get('/pos/products', [SalesController::class, 'search']);
Route::get('/pos/products/barcode/{barcode}', [SalesController::class, 'getByBarcode']);
Route::get('/pos/vouchers', [SalesController::class, 'getVouchers']);
Route::get('/pos/members', [SalesController::class, 'getMembers']);
