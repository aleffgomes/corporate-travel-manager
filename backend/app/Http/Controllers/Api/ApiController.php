<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

abstract class ApiController extends Controller
{
    protected function successResponse(mixed $data, string $message = 'Success', int $statusCode = 200, ?array $pagination = null): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if ($pagination) {
            $response['pagination'] = $pagination;
        }

        return response()->json($response, $statusCode);
    }

    protected function errorResponse(string $message, int $statusCode = 400, mixed $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }
}
