<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [\App\Http\Controllers\AuthController::class, 'login'])->name('login');
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'postLogin'])->name('post-login');

Route::get('/register', [\App\Http\Controllers\AuthController::class, 'register'])->name('register');
Route::post('/register', [\App\Http\Controllers\AuthController::class, 'postRegister'])->name('post-register');

Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index']);
    Route::prefix('/users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('users.index');
        Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
        Route::put('/{user}/password', [UserController::class, 'updatePassword'])->name('users.update-password');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
    Route::prefix('/products')->group(function () {
        Route::get('/', [\App\Http\Controllers\ProductController::class, 'index'])->name('products.index');
        Route::post('/', [\App\Http\Controllers\ProductController::class, 'store'])->name('products.store');
        Route::get('/{products}', [\App\Http\Controllers\ProductController::class, 'edit'])->name('products.edit');  //with GET
        Route::put('/{products}', [\App\Http\Controllers\ProductController::class, 'update'])->name('products.update');  //with GET
        Route::delete('/{products}', [\App\Http\Controllers\ProductController::class, 'destroy'])->name('products.destroy');
    });
    Route::prefix('/purchase-order')->group(function () {
        Route::get('/', [\App\Http\Controllers\PurchaseOrderController::class, 'index'])->name('purchase-order.index');
        Route::post('/', [\App\Http\Controllers\PurchaseOrderController::class, 'store'])->name('purchase-order.store');
        // Route::get('/{purchase-order}', [\App\Http\Controllers\PurchaseOrderController::class, 'edit'])->name('purchase-order.edit');  //with GET
        // Route::put('/{purchase-order}', [\App\Http\Controllers\PurchaseOrderController::class, 'update'])->name('purchase-order.update');  //with GET
    });
    Route::prefix('/purchasing')->group(function () {
        Route::get('/', [\App\Http\Controllers\PurchasingController::class, 'index'])->name('purchasing.index');
        Route::post('/', [\App\Http\Controllers\PurchasingController::class, 'store'])->name('purchasing.store');
        // Route::post('/', [\App\Http\Controllers\PurchasingController::class, 'store'])->name('purchasing.store');
        // Route::get('/{purchasing}', [\App\Http\Controllers\PurchasingController::class, 'edit'])->name('purchasing.edit');  //with GET
        // Route::put('/{purchasing}', [\App\Http\Controllers\PurchasingController::class, 'update'])->name('purchasing.update');  //with GET
    });
    Route::prefix('/pos')->group(function () {
        Route::get('/', [\App\Http\Controllers\SalesController::class, 'index'])->name('pos.index');
        // Route::post('/', [\App\Http\Controllers\Sales::class, 'store'])->name('pos.store');
        // Route::post('/', [\App\Http\Controllers\PurchasingController::class, 'store'])->name('purchasing.store');
        // Route::get('/{purchasing}', [\App\Http\Controllers\PurchasingController::class, 'edit'])->name('purchasing.edit');  //with GET
        // Route::put('/{purchasing}', [\App\Http\Controllers\PurchasingController::class, 'update'])->name('purchasing.update');  //with GET
    });
    Route::prefix('/vouchers')->group(function () {
        Route::get('/', [\App\Http\Controllers\VoucherController::class, 'index'])->name('vouchers.index');
        Route::post('/', [\App\Http\Controllers\VoucherController::class, 'store'])->name('vouchers.store');
        Route::get('/{vouchers}', [\App\Http\Controllers\VoucherController::class, 'edit'])->name('vouchers.edit');  //with GET
        Route::put('/{vouchers}', [\App\Http\Controllers\VoucherController::class, 'update'])->name('vouchers.update');  //with GET
    });
    Route::prefix('/members')->group(function () {
        Route::get('/', [\App\Http\Controllers\MemberController::class, 'index'])->name('members.index');
        Route::post('/', [\App\Http\Controllers\MemberController::class, 'store'])->name('members.store');
        Route::get('/{members}', [\App\Http\Controllers\MemberController::class, 'edit'])->name('members.edit');  //with GET
        Route::put('/{members}', [\App\Http\Controllers\MemberController::class, 'update'])->name('members.update');  //with GET
    });
    Route::prefix('/categories')->group(function () {
        Route::get('/', [\App\Http\Controllers\CategoryController::class, 'index'])->name('categories.index');
        Route::post('/', [\App\Http\Controllers\CategoryController::class, 'store'])->name('categories.store');
        Route::get('/{categories}', [\App\Http\Controllers\CategoryController::class, 'edit'])->name('categories.edit');  //with GET
        Route::put('/{categories}', [\App\Http\Controllers\CategoryController::class, 'update'])->name('categories.update');  //with GET
        Route::delete('/{categories}', [\App\Http\Controllers\CategoryController::class, 'destroy'])->name('categories.destroy');
    });
    Route::prefix('/units')->group(function () {
        Route::get('/', [\App\Http\Controllers\UnitController::class, 'index'])->name('units.index');
        Route::post('/', [\App\Http\Controllers\UnitController::class, 'store'])->name('units.store');
        Route::get('/{units}', [\App\Http\Controllers\UnitController::class, 'edit'])->name('units.edit');  //with GET
        Route::put('/{units}', [\App\Http\Controllers\UnitController::class, 'update'])->name('units.update');  //with GET
        Route::delete('/{units}', [\App\Http\Controllers\UnitController::class, 'destroy'])->name('units.destroy');
    });
    Route::prefix('/suppliers')->group(function () {
        Route::get('/', [\App\Http\Controllers\SupplierController::class, 'index'])->name('suppliers.index');
        Route::post('/', [\App\Http\Controllers\SupplierController::class, 'store'])->name('suppliers.store');
        Route::get('/{suppliers}', [\App\Http\Controllers\SupplierController::class, 'edit'])->name('suppliers.edit');  //with GET
        Route::put('/{suppliers}', [\App\Http\Controllers\SupplierController::class, 'update'])->name('suppliers.update');  //with GET
        Route::delete('/{suppliers}', [\App\Http\Controllers\SupplierController::class, 'destroy'])->name('suppliers.destroy');
    });
});
