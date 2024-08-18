<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index()
    {
        try {
            $products = Product::with([
                'brand:id,name',
                'category:id,name'
            ])->get();
            return response()->json($products, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch products.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'model' => 'nullable|string|max:255',
                'category_id' => 'required|integer',
                'brand_id' => 'required|integer',
                'purchase_price' => 'required|numeric',
                'sale_price' => 'required|numeric',
                'low_level' => 'nullable|integer',
            ]);

            $product = Product::create([
                'name' => $validated['name'],
                'model' => $validated['model'],
                'category_id' => $validated['category_id'],
                'brand_id' => $validated['brand_id'],
                'purchase_price' => $validated['purchase_price'],
                'sale_price' => $validated['sale_price'],
                'low_level' => $validated['low_level'] ?? 0,
                'status' => 0, // Default status
            ]);

            return response()->json($product, Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create product.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        try {
            $product = Product::findOrFail($id);
            return response()->json($product, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Product not found.',
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'model' => 'nullable|string|max:255',
                'category_id' => 'required|integer',
                'brand_id' => 'required|integer',
                'purchase_price' => 'required|numeric',
                'sale_price' => 'required|numeric',
                'low_level' => 'nullable|integer'
            ]);

            $product = Product::findOrFail($id);
            $product->update($validated);

            return response()->json($product, Response::HTTP_OK);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update product.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();

            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete product.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function restore($id)
    {
        try {
            $product = Product::onlyTrashed()->findOrFail($id);
            $product->restore();

            return response()->json($product, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to restore product.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}