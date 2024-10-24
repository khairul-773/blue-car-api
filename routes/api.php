<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ShowroomController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PartytransactionController;

// Registration route
Route::post('/register', [AuthController::class, 'register']);
// Login route
Route::post('/login', [AuthController::class, 'login']);

// Protected route for products
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::apiResource('brands', BrandController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('showrooms', ShowroomController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('suppliers', SupplierController::class);
    Route::get('suppliers/showroom/{showroomId}', [SupplierController::class, 'showroomWiseSupplier']);
    Route::apiResource('partytransactions', PartytransactionController::class);
});