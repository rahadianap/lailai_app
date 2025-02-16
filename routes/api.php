<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\MutasiKeluarController;
use App\Http\Controllers\MutasiMasukController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchasingController;
use App\Http\Controllers\ReturBeliController;
use App\Http\Controllers\ReturJualController;
use App\Http\Controllers\SalesController;
use Illuminate\Support\Facades\Route;

Route::get('/products/categories', [ProductController::class, 'getCategories']);
Route::get('/products/units', [ProductController::class, 'getUnits']);
Route::get('/purchase-order/suppliers', [PurchaseOrderController::class, 'getSuppliers']);
Route::get('/purchase-order/products', [PurchaseOrderController::class, 'getProducts']);
Route::get('/purchase-order/products/{id}', [PurchaseOrderController::class, 'fetchDetails']);
Route::get('/purchase-order/print/{id}', [PurchaseOrderController::class, 'print']);
Route::get('/purchasing/suppliers', [PurchasingController::class, 'getSuppliers']);
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/purchasing/products', [PurchasingController::class, 'getProducts'])->name('purchasing.products');
    Route::get('/mutasi-keluar/tujuan', [MutasiKeluarController::class, 'getTujuan'])->name('mutasi-keluar.tujuans');
    Route::get('/mutasi-keluar/products', [MutasiKeluarController::class, 'getProducts'])->name('mutasi-keluar.products');
    Route::get('/purchasing/products/{id}', [PurchasingController::class, 'fetchDetails']);
    Route::get('/mutasi-keluar/products/{id}', [MutasiKeluarController::class, 'fetchDetails']);
    Route::get('/mutasi-masuk/asal', [MutasiMasukController::class, 'getMutasiKeluar'])->name('mutasi-masuk.asals');
    Route::get('/mutasi-masuk/asal/{id}', [MutasiMasukController::class, 'fetchMutasiKeluarDetails']);
});
Route::get('/purchasing/po', [PurchasingController::class, 'getPO']);
Route::get('/purchasing/po/{id}', [PurchasingController::class, 'fetchPODetails']);
Route::get('/purchasing/print/{id}', [PurchasingController::class, 'print']);
Route::get('/pos/products', [SalesController::class, 'search']);
Route::get('/pos/products/barcode/{barcode}', [SalesController::class, 'getByBarcode']);
Route::get('/pos/vouchers', [SalesController::class, 'getVouchers']);
Route::get('/pos/members', [SalesController::class, 'getMembers']);
Route::get('/coa/groups', [AccountController::class, 'getGroups']);
Route::get('/retur-beli/suppliers', [ReturBeliController::class, 'getSuppliers']);
Route::get('/retur-beli/purchasing', [ReturBeliController::class, 'getPurchasing']);
Route::get('/retur-beli/products', [ReturBeliController::class, 'getProducts']);
Route::get('/retur-beli/products/{id}', [ReturBeliController::class, 'fetchDetails']);
Route::get('/retur-beli/purchasing/{id}', [ReturBeliController::class, 'fetchPurchasingDetails']);
Route::get('/retur-beli/print/{id}', [ReturBeliController::class, 'print']);
Route::get('/retur-jual/sales', [ReturJualController::class, 'getSales']);
Route::get('/retur-jual/sales/{id}', [ReturJualController::class, 'fetchSalesDetails']);
Route::get('/retur-jual/print/{id}', [ReturJualController::class, 'print']);
