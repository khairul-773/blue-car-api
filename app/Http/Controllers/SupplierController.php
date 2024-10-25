<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Traits\ApiResponse;

class SupplierController extends Controller
{
    use ApiResponse;
    /**
     * Summary of index
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $suppliers = Supplier::with(["showroom:id,name"])->get();
            return $this->successResponse('Suppliers fetched successfully.', $suppliers, Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch suppliers.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
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
     * Summary of show
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
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
     * Summary of showroomWiseSupplier
     * @param mixed $showroomId
     * @return \Illuminate\Http\JsonResponse
     */
    public function showroomWiseSupplier($showroomId)
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
     * Summary of update
     * @param \Illuminate\Http\Request $request
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
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
     * Summary of destroy
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroy($id)
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
