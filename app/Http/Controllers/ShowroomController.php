<?php

namespace App\Http\Controllers;

use App\Models\Showroom;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Traits\ResponseTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ShowroomController extends Controller
{
    use ResponseTrait;

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $showrooms = Showroom::all();
            return $this->successResponse($showrooms, 'Showrooms fetched successfully.', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch showrooms.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request): JsonResponse
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
            $this->extracted($request, $showroom);

            return $this->successResponse($showroom, 'Showroom created successfully.', Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create showroom.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $showroom = Showroom::findOrFail($id);
            return $this->successResponse($showroom, 'Showroom details retrieved successfully.', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse('Showroom not found.', $e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    public function update(Request $request, $id): JsonResponse
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
            $this->extracted($request, $showroom);

            return $this->successResponse($showroom, 'Showroom updated successfully.', Response::HTTP_OK);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update showroom.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $showroom = Showroom::findOrFail($id);
            $showroom->delete();
            return $this->successResponse(null, 'Showroom deleted successfully.', Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete showroom.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function generateUniqueNameCode($name): string
    {
        $baseNameCode = Str::slug($name);
        $suffix = 1;
        while (Showroom::where('name_code', $baseNameCode . '-' . $suffix)->exists()) {
            $suffix++;
        }
        return $baseNameCode . '-' . $suffix;
    }

    /**
     * @param Request $request
     * @param $showroom
     * @return void
     */
    public function extracted(Request $request, $showroom): void
    {
        $showroom->name = $request->name;
        $showroom->name_code = $this->generateUniqueNameCode($request->name);
        $showroom->location = $request->location;
        $showroom->manager = $request->manager;
        $showroom->mobile = $request->mobile;
        $showroom->mobile_two = $request->mobile_two;
        $showroom->address = $request->address;
        $showroom->save();
    }
}
