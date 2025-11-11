<?php

namespace App\Services;

use App\Contracts\UserRepositoryInterface;
use App\Domain\UserDomain;
use App\Models\RoleModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthService extends BaseService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function register(array $data): array
    {
        try {
            $validator = Validator::make($data, [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $user = $this->userRepository->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role_id' => RoleModel::USER_ID,
            ]);

            $token = auth('api')->login($user);
            $userDomain = UserDomain::fromModel($user);

            return [
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'token' => $token,
                    'user' => $userDomain->toArray(),
                ],
            ];
        } catch (ValidationException $e) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ];
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function login(array $data): array
    {
        try {
            $validator = Validator::make($data, [
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $credentials = [
                'email' => $data['email'],
                'password' => $data['password'],
            ];

            if (!$token = auth('api')->attempt($credentials)) {
                return [
                    'success' => false,
                    'message' => 'Invalid credentials',
                ];
            }

            /** @var \App\Models\UserModel $user */
            $user = auth('api')->user();
            $user->load('role');
            $userDomain = UserDomain::fromModel($user);

            return [
                'success' => true,
                'message' => 'User logged in successfully',
                'data' => [
                    'token' => $token,
                    'user' => $userDomain->toArray(),
                ],
            ];
        } catch (ValidationException $e) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ];
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function logout(): array
    {
        try {
            auth('api')->logout();

            return [
                'success' => true,
                'message' => 'User logged out successfully',
            ];
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function refresh(): array
    {
        try {
            /** @var \Tymon\JWTAuth\JWTGuard $guard */
            $guard = auth('api');

            if (!$guard->check()) {
                return [
                    'success' => false,
                    'message' => 'Token is invalid or expired',
                ];
            }

            try {
                $newToken = $guard->refresh();
            } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
                return [
                    'success' => false,
                    'message' => 'Token has expired and cannot be refreshed',
                ];
            } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
                return [
                    'success' => false,
                    'message' => 'Token is invalid',
                ];
            }

            $user = $guard->user();

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found',
                ];
            }

            /** @var \App\Models\UserModel $user */
            $user->load('role');
            $userDomain = UserDomain::fromModel($user);

            return [
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'token' => $newToken,
                    'user' => $userDomain->toArray(),
                ],
            ];
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function me(): array
    {
        try {
            /** @var \App\Models\UserModel $user */
            $user = auth('api')->user();
            $user->load('role');
            $userDomain = UserDomain::fromModel($user);

            return [
                'success' => true,
                'message' => 'User retrieved successfully',
                'data' => $userDomain->toArray(),
            ];
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }
}
