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

    public function login(Request $request)
    {
    $result = $this->authService->login($request->all());

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

    public function logout()
    {
        $result = $this->authService->logout();

        if (!$result['success']) {
            return $this->errorResponse(
                $result['message'],
                400,
                $result['errors'] ?? null
            );
        }

        return $this->successResponse(
            null,
            $result['message'],
            200
        );
    }

    public function refresh()
    {
        $result = $this->authService->refresh();

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
            200
        );
    }

    public function me()
    {
        $result = $this->authService->me();

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
            200
        );
    }
}
