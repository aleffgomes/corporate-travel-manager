<?php

namespace Tests\Unit\Services;

use App\Contracts\UserRepositoryInterface;
use App\Models\UserModel as User;
use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    private AuthService $authService;
    private UserRepositoryInterface|\Mockery\MockInterface $mockRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->authService = new AuthService($this->mockRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_register_creates_user_successfully(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $mockUser = new User([
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);
        $mockUser->id = 1;
        $mockUser->created_at = now();
        $mockUser->updated_at = now();

        $this->mockRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn($mockUser);

        Auth::shouldReceive('guard')
            ->with('api')
            ->andReturnSelf();

        Auth::shouldReceive('login')
            ->with($mockUser)
            ->andReturn('fake-jwt-token');

        $result = $this->authService->register($userData);

        $this->assertTrue($result['success']);
        $this->assertEquals('User registered successfully', $result['message']);
        $this->assertArrayHasKey('token', $result['data']);
        $this->assertArrayHasKey('user', $result['data']);
        $this->assertEquals($userData['email'], $result['data']['user']['email']);
    }

    public function test_register_fails_with_invalid_email(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $result = $this->authService->register($userData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('email', $result['errors']);
    }

    public function test_register_fails_with_short_password(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => '123',
            'password_confirmation' => '123',
        ];

        $result = $this->authService->register($userData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('password', $result['errors']);
    }

    public function test_register_fails_with_password_mismatch(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ];

        $result = $this->authService->register($userData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('password', $result['errors']);
    }

    public function test_register_fails_with_missing_required_fields(): void
    {
        $userData = [
            'email' => 'john@example.com',
        ];

        $result = $this->authService->register($userData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
    }

    public function test_register_hashes_password_before_saving(): void
    {
        Hash::shouldReceive('make')
            ->once()
            ->with('password123')
            ->andReturn('hashed_password');

        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $mockUser = new User([
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);
        $mockUser->id = 1;
        $mockUser->created_at = now();
        $mockUser->updated_at = now();

        $this->mockRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return $arg['password'] === 'hashed_password';
            }))
            ->andReturn($mockUser);

        Auth::shouldReceive('guard')
            ->with('api')
            ->andReturnSelf();

        Auth::shouldReceive('login')
            ->with($mockUser)
            ->andReturn('fake-jwt-token');

        $result = $this->authService->register($userData);

        $this->assertTrue($result['success']);
    }
}
