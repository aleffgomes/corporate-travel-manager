<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends ApiController
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function register(Request $request)
    {
        $result = $this->authService->register($request->all());

        if (!$result['success']) {
            return $this->errorResponse(
                $result['message'],
                400,
                $result['errors'] ?? null
            );
        }

        return $this->successResponse(
            $result['data'],
            $result['message'],
            201
        );
    }

    public function login()
    {
        return $this->successResponse(null, 'User logged in successfully', 200);
    }

    public function logout()
    {
        return $this->successResponse(null, 'User logged out successfully', 200);
    }
}
