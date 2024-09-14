<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ShowroomController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PartytransactionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('brands', BrandController::class);
Route::apiResource('categories', CategoryController::class);
Route::apiResource('showrooms', ShowroomController::class);
Route::apiResource('products', ProductController::class);
Route::apiResource('suppliers', SupplierController::class);
Route::get('suppliers/showroom/{showroomId}', [SupplierController::class, 'showroomWiseSupplier']);
Route::apiResource('partytransactions', PartytransactionController::class);