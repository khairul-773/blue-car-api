<?php

namespace App\Http\Controllers;

use App\Models\Showroom;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use App\Traits\ApiResponse;

class ShowroomController extends Controller
{
    use ApiResponse;
    /**
     * Summary of index
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $showrooms = Showroom::all();
            return $this->successResponse('Showrooms fetched successfully.', $showrooms, Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch showrooms.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
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

            return $this->successResponse('Showroom created successfully.', $showroom, Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation Error', $e->validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create showroom.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
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
            $showroom = Showroom::findOrFail($id);
            return $this->successResponse('Showroom details retrieved successfully.', $showroom, Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse('Showroom not found.', $e->getMessage(), Response::HTTP_NOT_FOUND);
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

            return $this->successResponse('Showroom updated successfully.', $showroom, Response::HTTP_OK);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation Error', $e->validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update showroom.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
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
            $showroom = Showroom::findOrFail($id);
            $showroom->delete();

            return $this->successResponse('Showroom deleted successfully.', null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete showroom.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Summary of generateUniqueNameCode
     * @param mixed $name
     * @return string
     */
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