<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ShowroomController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PartytransactionController;
use App\Http\Middleware\TokenExpiryMiddleware;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes with auth and token expiry middleware
Route::middleware(['auth:sanctum', TokenExpiryMiddleware::class])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('brands', BrandController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('showrooms', ShowroomController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('suppliers', SupplierController::class);
    Route::get('suppliers/showroom/{showroomId}', [SupplierController::class, 'showroomWiseSupplier']);
    Route::apiResource('partytransactions', PartytransactionController::class);
});
