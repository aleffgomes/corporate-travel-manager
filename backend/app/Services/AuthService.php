<?php

namespace App\Services;

use App\Contracts\UserRepositoryInterface;
use App\Domain\UserDomain;
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
}
