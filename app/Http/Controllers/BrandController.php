<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\ValidationException;
use App\Traits\ResponseTrait;
use App\Services\QueryFilterService;
class BrandController extends Controller
{
    use ResponseTrait;

    /**
     * Display a listing of the brands.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $brands = QueryFilterService::applyFilters(Brand::query(), $request, ['name', 'created_at']);

            $pagination = [
                'page' => $brands->currentPage(),
                'pageSize' => $brands->perPage(),
                'totalPages' => $brands->lastPage(),
                'totalCount' => $brands->total(),
            ];

            return $this->successResponse($brands->items(), 'Brands retrieved successfully', Response::HTTP_OK, $pagination);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch brands.', $e->getMessage());
        }
    }

    /**
     * Store a newly created brand.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validate incoming request
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            // Create brand
            $brand = Brand::create($validated);
            return $this->successResponse($brand, 'Brand created successfully', Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            // Return validation error response
            return $this->validationErrorResponse($e);
        } catch (\Exception $e) {
            // Return general error response
            return $this->errorResponse('Failed to create brand.', $e->getMessage());
        }
    }

    /**
     * Display the specified brand.
     */
    public function show(Brand $brand): JsonResponse
    {
        try {
            return $this->successResponse($brand, 'Brand retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve brand.', $e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified brand.
     */
    public function update(Request $request, Brand $brand): JsonResponse
    {
        try {
            // Validate incoming request
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            // Update brand
            $brand->update($validated);
            return $this->successResponse($brand, 'Brand updated successfully');
        } catch (ValidationException $e) {
            // Return validation error response
            return $this->validationErrorResponse($e);
        } catch (\Exception $e) {
            // Return general error response
            return $this->errorResponse('Failed to update brand.', $e->getMessage());
        }
    }

    /**
     * Remove the specified brand.
     */
    public function destroy(Brand $brand): JsonResponse
    {
        try {
            // Delete brand
            $brand->delete();
            return $this->successResponse(null, 'Brand deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete brand.', $e->getMessage());
        }
    }
}