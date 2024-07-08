<?php

namespace App\Http\Controllers;

use App\Models\Showroom;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class ShowroomController extends Controller
{
    public function index()
    {
        try {
            $showrooms = Showroom::all();
            return response()->json($showrooms, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch showrooms.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:showrooms,name',
                'location' => 'required|string|max:255',
                'manager' => 'nullable|string|max:255',
                'mobile' => 'nullable|string|max:15',
                'mobile_two' => 'nullable|string|max:15',
                'address' => 'nullable|string',
            ]);

            $showroom = new Showroom();
            $showroom->name = $request->name;
            $showroom->name_code = $this->generateUniqueNameCode($request->name);
            $showroom->location = $request->location;
            $showroom->manager = $request->manager;
            $showroom->mobile = $request->mobile;
            $showroom->mobile_two = $request->mobile_two;
            $showroom->address = $request->address;
            $showroom->save();

            return response()->json($showroom, Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create showroom.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        try {
            $showroom = Showroom::findOrFail($id);
            return response()->json($showroom, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Showroom not found.',
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:255|unique:showrooms,name,' . $id,
                'location' => 'sometimes|required|string|max:255',
                'manager' => 'nullable|string|max:255',
                'mobile' => 'nullable|string|max:15',
                'mobile_two' => 'nullable|string|max:15',
                'address' => 'nullable|string',
            ]);

            $showroom = Showroom::findOrFail($id);
            $showroom->name = $request->name;
            $showroom->name_code = $this->generateUniqueNameCode($request->name);
            $showroom->location = $request->location;
            $showroom->manager = $request->manager;
            $showroom->mobile = $request->mobile;
            $showroom->mobile_two = $request->mobile_two;
            $showroom->address = $request->address;
            $showroom->save();

            return response()->json($showroom, Response::HTTP_OK);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update showroom.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            $showroom = Showroom::findOrFail($id);
            $showroom->delete();

            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete showroom.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function generateUniqueNameCode($name)
    {
        $baseNameCode = Str::slug($name);
        $suffix = 1;

        while (Showroom::where('name_code', $baseNameCode . '-' . $suffix)->exists()) {
            $suffix++;
        }

        return $baseNameCode . '-' . $suffix;
    }
}
