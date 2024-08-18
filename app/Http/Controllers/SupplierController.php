<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SupplierController extends Controller
{
    public function index()
    {
        try {
            $suppliers = Supplier::all();
            return response()->json($suppliers, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch suppliers.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

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

            $supplier = Supplier::create([
                'showroom_id' => $validated['showroom_id'],
                'date' => $validated['date'],
                'name' => $validated['name'],
                'contact_person' => $validated['contact_person'],
                'mobile' => $validated['mobile'],
                'address' => $validated['address'],
                'initial_balance' => $validated['initial_balance'],
                'status' => $validated['status'],
            ]);

            return response()->json($supplier, Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create supplier.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        try {
            $supplier = Supplier::findOrFail($id);
            return response()->json($supplier, Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Supplier not found.',
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch supplier.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

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

            return response()->json($supplier, Response::HTTP_OK);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Supplier not found.',
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update supplier.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            $supplier = Supplier::findOrFail($id);
            $supplier->delete();

            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Supplier not found.',
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete supplier.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function restore($id)
    {
        try {
            $supplier = Supplier::onlyTrashed()->findOrFail($id);
            $supplier->restore();

            return response()->json($supplier, Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Supplier not found.',
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to restore supplier.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
