<?php

use Illuminate\Http\Request;
use App\Http\Controllers\PaymentGateway;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AuthenticationController;
use App\Http\Controllers\api\RoleController;
use App\Http\Controllers\api\CategoryController;
use App\Http\Controllers\api\ProductController;
use App\Models\Product;

Route::post('/register', [AuthenticationController::class, 'register']);
Route::post('/login', [AuthenticationController::class, 'login']);

Route::get('/saya', [AuthenticationController::class, 'me'])->middleware('auth:sanctum');
Route::get('/user', [RoleController::class, 'store_permission']);


// Route::post('/super-admin', action: [RoleController::class, 'store'])->middleware('auth:sanctum', 'role:super-admin');
Route::post('/super-admin/permission', action: [RoleController::class, 'store_permission'])->middleware('auth:sanctum', 'role:super-admin');
Route::get('/super-admin', action: [RoleController::class, 'store'])->middleware('auth:sanctum', 'role:super-admin');


Route::prefix('categories')->middleware('auth:sanctum')->group(function () {
    // Menampilkan semua kategori
    Route::get('/showdata', [CategoryController::class, 'index'])->middleware('role:admin|super-admin|customer'); 
    Route::post('/tambah', [CategoryController::class, 'setCategory'])->middleware('role:admin');
    Route::put('/update', [CategoryController::class, 'updateCategory'])->middleware('role:admin');;
    Route::delete('/delete', [CategoryController::class, 'deleteCategory'])->middleware('role:admin');; 
});

Route::prefix('product')->middleware('auth:sanctum')->group(function(){

    Route::get('/showdata',[ProductController::class, 'index'])->middleware('role:admin|super-admin|customer');
    Route::post('/tambah',[ProductController::class, 'setProduct'])->middleware('role:admin|super-admin');
    Route::post('/update',[ProductController::class, 'update'])->middleware('role:admin|super-admin');
    Route::delete('/delete',[Productcontroller::class, 'delete'])->middleware(('role:admin|super-admin'));
});


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/payment-gateway/store', [\App\Http\Controllers\Api\PaymentGateway::class, 'store'])->middleware('auth:sanctum', 'role:admin|super-admin|customer');
Route::post('/payment-gateway/cek-detail-transaksi', [\App\Http\Controllers\Api\PaymentGateway::class, 'CekDetailTransaksi'])->middleware('auth:sanctum', 'role:admin|super-admin|customer');
Route::post('/payment-gateway/cek-status-transaksi', [\App\Http\Controllers\Api\PaymentGateway::class, 'CekStatusPembayaran'])->middleware('auth:sanctum', 'role:admin|super-admin|customer');
Route::post('/destination', [\App\Http\Controllers\Api\ShippingController::class, 'get_destinations'])->middleware('auth:sanctum', 'role:admin|super-admin|customer');