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
        Route::get('/{id}', [\App\Http\Controllers\PurchaseOrderController::class, 'edit'])->name('purchase-order.edit');  //with GET
        Route::put('/{id}', [\App\Http\Controllers\PurchaseOrderController::class, 'update'])->name('purchase-order.update');  //with GET
        Route::delete('/{id}', [\App\Http\Controllers\PurchaseOrderController::class, 'destroy'])->name('purchase-order.destroy');
        Route::put('/approve/{id}', [\App\Http\Controllers\PurchaseOrderController::class, 'approve'])->name('purchase-order.approve');
    });
    Route::prefix('/purchasing')->group(function () {
        Route::get('/', [\App\Http\Controllers\PurchasingController::class, 'index'])->name('purchasing.index');
        Route::post('/', [\App\Http\Controllers\PurchasingController::class, 'store'])->name('purchasing.store');
        Route::get('/{id}', [\App\Http\Controllers\PurchasingController::class, 'edit'])->name('purchasing.edit');  //with GET
        Route::put('/{id}', [\App\Http\Controllers\PurchasingController::class, 'update'])->name('purchasing.update');  //with GET
        Route::delete('/{id}', [\App\Http\Controllers\PurchasingController::class, 'destroy'])->name('purchasing.destroy');
        Route::put('/approve/{id}', [\App\Http\Controllers\PurchasingController::class, 'approve'])->name('purchasing.approve');
    });
    Route::prefix('/penjualan')->group(function () {
        Route::get('/', [\App\Http\Controllers\PenjualanController::class, 'index'])->name('penjualan.index');
        // Route::post('/', [\App\Http\Controllers\PurchasingController::class, 'store'])->name('penjualan.store');
        // Route::get('/{id}', [\App\Http\Controllers\PurchasingController::class, 'edit'])->name('penjualan.edit');  //with GET
        // Route::put('/{id}', [\App\Http\Controllers\PurchasingController::class, 'update'])->name('penjualan.update');  //with GET
        // Route::delete('/{id}', [\App\Http\Controllers\PurchasingController::class, 'destroy'])->name('penjualan.destroy');
    });
    Route::prefix('/retur-beli')->group(function () {
        Route::get('/', [\App\Http\Controllers\ReturBeliController::class, 'index'])->name('retur-beli.index');
        Route::post('/', [\App\Http\Controllers\ReturBeliController::class, 'store'])->name('retur-beli.store');
        Route::get('/{id}', [\App\Http\Controllers\ReturBeliController::class, 'edit'])->name('retur-beli.edit');  //with GET
        Route::put('/{id}', [\App\Http\Controllers\ReturBeliController::class, 'update'])->name('retur-beli.update');  //with GET
        Route::delete('/{id}', [\App\Http\Controllers\ReturBeliController::class, 'destroy'])->name('retur-beli.destroy');
        Route::put('/approve/{id}', [\App\Http\Controllers\ReturBeliController::class, 'approve'])->name('retur-beli.approve');
    });
    Route::prefix('/retur-jual')->group(function () {
        Route::get('/', [\App\Http\Controllers\ReturJualController::class, 'index'])->name('retur-jual.index');
        Route::post('/', [\App\Http\Controllers\ReturJualController::class, 'store'])->name('retur-jual.store');
        Route::get('/{id}', [\App\Http\Controllers\ReturJualController::class, 'edit'])->name('retur-jual.edit');  //with GET
        Route::put('/{id}', [\App\Http\Controllers\ReturJualController::class, 'update'])->name('retur-jual.update');  //with GET
        Route::delete('/{id}', [\App\Http\Controllers\ReturJualController::class, 'destroy'])->name('retur-jual.destroy');
        Route::put('/approve/{id}', [\App\Http\Controllers\ReturJualController::class, 'approve'])->name('retur-jual.approve');
    });
    Route::prefix('/pos')->group(function () {
        Route::get('/', [\App\Http\Controllers\SalesController::class, 'index'])->name('pos.index');
        Route::post('/', [\App\Http\Controllers\SalesController::class, 'store'])->name('pos.store');
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
    Route::prefix('/stores')->group(function () {
        Route::get('/', [\App\Http\Controllers\StoreController::class, 'index'])->name('stores.index');
        Route::post('/', [\App\Http\Controllers\StoreController::class, 'store'])->name('stores.store');
        Route::get('/{stores}', [\App\Http\Controllers\StoreController::class, 'edit'])->name('stores.edit');  //with GET
        Route::put('/{stores}', [\App\Http\Controllers\StoreController::class, 'update'])->name('stores.update');  //with GET
        Route::delete('/{stores}', [\App\Http\Controllers\StoreController::class, 'destroy'])->name('stores.destroy');
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
    Route::prefix('/account-groups')->group(function () {
        Route::get('/', [\App\Http\Controllers\KelompokAccountController::class, 'index'])->name('account-groups.index');
        Route::post('/', [\App\Http\Controllers\KelompokAccountController::class, 'store'])->name('account-groups.store');
        Route::get('/{id}', [\App\Http\Controllers\KelompokAccountController::class, 'edit'])->name('account-groups.edit');  //with GET
        Route::put('/{id}', [\App\Http\Controllers\KelompokAccountController::class, 'update'])->name('account-groups.update');  //with GET
        Route::delete('/{id}', [\App\Http\Controllers\KelompokAccountController::class, 'destroy'])->name('account-groups.destroy');
    });
    Route::prefix('/accounts')->group(function () {
        Route::get('/', [\App\Http\Controllers\AccountController::class, 'index'])->name('accounts.index');
        Route::post('/', [\App\Http\Controllers\AccountController::class, 'store'])->name('accounts.store');
        Route::get('/{id}', [\App\Http\Controllers\AccountController::class, 'edit'])->name('accounts.edit');  //with GET
        Route::put('/{id}', [\App\Http\Controllers\AccountController::class, 'update'])->name('accounts.update');  //with GET
        Route::delete('/{id}', [\App\Http\Controllers\AccountController::class, 'destroy'])->name('accounts.destroy');
    });
});
