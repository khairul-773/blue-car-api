<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use App\Traits\ApiResponse;

class BrandController extends Controller
{
    use ApiResponse;
    /**
     * Summary of index
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $brands = Brand::all();
            return $this->successResponse('Brands fetched successfully', $brands, statusCode: Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch brands.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
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
                'description' => 'nullable|string',
            ]);

            $brand = Brand::create($validated);
            return $this->successResponse('Brand created successfully', $brand, Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation Error', $e->validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create brand.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
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
            $brand = Brand::findOrFail($id);
            return $this->successResponse('Brand retrieved successfully', $brand, Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse('Brand not found.', $e->getMessage(), Response::HTTP_NOT_FOUND);
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
                'description' => 'nullable|string',
            ]);

            $brand = Brand::findOrFail($id);
            $brand->update($validated);

            return $this->successResponse('Brand updated successfully', $brand, Response::HTTP_OK);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation Error', $e->validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update brand.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
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
            $brand = Brand::findOrFail($id);
            $brand->delete();

            return $this->successResponse('Brand deleted successfully', null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete brand.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}