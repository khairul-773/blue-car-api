<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

trait ResponseTrait
{
    /**
     * Success response
     */
    public function successResponse(mixed $data = null, string $message = 'Success', int $statusCode = ResponseAlias::HTTP_OK, $pagination = null): JsonResponse
    {
        $response = [
            'success' => true,
            'statusCode' => $statusCode,
            'message' => $message,
        ];

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        if (!is_null($pagination)) {
            $response['pagination'] = $pagination;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Error response (Multiple errors or single error)
     */
    public function errorResponse(string $message, $errors = '', int $statusCode = ResponseAlias::HTTP_BAD_REQUEST): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'statusCode' => $statusCode,
            'error' => [],
        ];

        // Check if errors is an array
        if (is_array($errors)) {
            $response['error'] = array_map(fn($error) => ['message' => $error], $errors);
        } elseif (is_string($errors)) {
            // If it's a single error string, wrap it in an array for consistency
            $response['error'] = [['message' => $errors]];
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Validation error response (For multiple validation errors)
     */
    public function validationErrorResponse(ValidationException $exception): JsonResponse
    {
        // Map all validation errors to the desired format
        $validationErrors = collect($exception->validator->errors())->map(fn($errors) => array_map(fn($msg) => ['message' => $msg], $errors))->flatten(1);

        return response()->json([
            'success' => false,
            'message' => 'An error occurred',
            'statusCode' => ResponseAlias::HTTP_UNPROCESSABLE_ENTITY,
            'error' => $validationErrors,
        ], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
    }
}