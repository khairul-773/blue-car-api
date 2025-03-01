<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Traits\ResponseTrait;

class SupplierController extends Controller
{
    use ResponseTrait;

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $suppliers = Supplier::with(["showroom:id,name"])->get();
            return $this->successResponse('Suppliers fetched successfully.', $suppliers, Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch suppliers.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'showroom_id' => 'required|integer|exists:showrooms,id',
                'date' => 'required|date',
                'name' => 'required|string|max:255',
                'contact_person' => 'required|string|max:255',
                'mobile' => 'required|string|max:20',
                'address' => 'required|string',
                'initial_balance' => 'required|numeric',
                'status' => 'required|in:Receivable,Payable',
            ]);

            $supplier = Supplier::create($validated);

            return $this->successResponse('Supplier created successfully.', $supplier, Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation Error', $e->validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create supplier.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $supplier = Supplier::findOrFail($id);
            return $this->successResponse('Supplier fetched successfully.', $supplier, Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Supplier not found.', $e->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch supplier.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param $showroomId
     * @return JsonResponse
     */
    public function showroomWiseSupplier($showroomId): JsonResponse
    {
        try {
            $suppliers = Supplier::where('showroom_id', $showroomId)->get();

            if ($suppliers->isEmpty()) {
                return $this->errorResponse('No suppliers found for the specified showroom.', null, Response::HTTP_NOT_FOUND);
            }

            return $this->successResponse('Suppliers fetched successfully.', $suppliers, Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch suppliers for the showroom.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'showroom_id' => 'required|integer|exists:showrooms,id',
                'date' => 'required|date',
                'name' => 'required|string|max:255',
                'contact_person' => 'required|string|max:255',
                'mobile' => 'required|string|max:20',
                'address' => 'required|string',
                'initial_balance' => 'required|numeric',
                'status' => 'required|in:Receivable,Payable',
            ]);

            $supplier = Supplier::findOrFail($id);
            $supplier->update($validated);

            return $this->successResponse('Supplier updated successfully.', $supplier, Response::HTTP_OK);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation Error', $e->validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update supplier.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $supplier = Supplier::findOrFail($id);
            $supplier->delete();

            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete supplier.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
