<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use App\Traits\ApiResponse;

class ProductController extends Controller
{
    use ApiResponse;
    /**
     * Summary of index
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $products = Product::with([
                'brand:id,name',
                'category:id,name'
            ])->get();

            return $this->successResponse('Products fetched successfully', $products, Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch products.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Summary of store
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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
                'status' => 0,
            ]);

            return $this->successResponse('Product created successfully', $product, Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation error', $e->validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create product.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Summary of show
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $product = Product::findOrFail($id);
            return $this->successResponse('Product retrieved successfully', $product, Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse('Product not found.', $e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }
    /**
     * Summary of update
     * @param \Illuminate\Http\Request $request
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
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

            return $this->successResponse('Product updated successfully', $product, Response::HTTP_OK);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation error', $e->validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update product.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Summary of destroy
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();

            return $this->successResponse('Product deleted successfully', null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete product.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Summary of restore
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {
        try {
            $product = Product::onlyTrashed()->findOrFail($id);
            $product->restore();

            return $this->successResponse('Product restored successfully', $product, Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to restore product.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
