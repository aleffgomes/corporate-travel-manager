<?php

namespace Tests\Unit\Services;

use App\Contracts\UserRepositoryInterface;
use App\Models\UserModel as User;
use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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

    // =====================
    // REGISTER TESTS
    // =====================

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

    public function test_register_fails_with_missing_name(): void
    {
        $userData = [
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $result = $this->authService->register($userData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('name', $result['errors']);
    }

    public function test_register_fails_with_missing_email(): void
    {
        $userData = [
            'name' => 'John Doe',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $result = $this->authService->register($userData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('email', $result['errors']);
    }

    public function test_register_fails_with_missing_password(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ];

        $result = $this->authService->register($userData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('password', $result['errors']);
    }

    public function test_register_fails_with_name_too_long(): void
    {
        $userData = [
            'name' => str_repeat('a', 256),
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $result = $this->authService->register($userData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('name', $result['errors']);
    }

    public function test_register_fails_with_email_too_long(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => str_repeat('a', 250) . '@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $result = $this->authService->register($userData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('email', $result['errors']);
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

    public function test_register_handles_repository_exception(): void
    {
        Log::shouldReceive('error')->once();

        Hash::shouldReceive('make')
            ->once()
            ->andReturn('hashed_password');

        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $this->mockRepository
            ->shouldReceive('create')
            ->once()
            ->andThrow(new \Exception('Database error'));

        $result = $this->authService->register($userData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
    }

    // =====================
    // LOGIN TESTS
    // =====================

    public function test_login_successfully_with_valid_credentials(): void
    {
        $credentials = [
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $mockUser = new User([
            'name' => 'John Doe',
            'email' => $credentials['email'],
        ]);
        $mockUser->id = 1;
        $mockUser->created_at = now();
        $mockUser->updated_at = now();

        Auth::shouldReceive('guard')
            ->with('api')
            ->andReturnSelf();

        Auth::shouldReceive('attempt')
            ->with($credentials)
            ->andReturn('fake-jwt-token');

        Auth::shouldReceive('user')
            ->andReturn($mockUser);

        $result = $this->authService->login($credentials);

        $this->assertTrue($result['success']);
        $this->assertEquals('User logged in successfully', $result['message']);
        $this->assertArrayHasKey('token', $result['data']);
        $this->assertArrayHasKey('user', $result['data']);
        $this->assertEquals($credentials['email'], $result['data']['user']['email']);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $credentials = [
            'email' => 'john@example.com',
            'password' => 'wrong_password',
        ];

        Auth::shouldReceive('guard')
            ->with('api')
            ->andReturnSelf();

        Auth::shouldReceive('attempt')
            ->with($credentials)
            ->andReturn(false);

        $result = $this->authService->login($credentials);

        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid credentials', $result['message']);
        $this->assertArrayNotHasKey('data', $result);
    }

    public function test_login_fails_with_missing_email(): void
    {
        $credentials = [
            'password' => 'password123',
        ];

        $result = $this->authService->login($credentials);

        $this->assertFalse($result['success']);
        $this->assertEquals('Validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('email', $result['errors']);
    }

    public function test_login_fails_with_missing_password(): void
    {
        $credentials = [
            'email' => 'john@example.com',
        ];

        $result = $this->authService->login($credentials);

        $this->assertFalse($result['success']);
        $this->assertEquals('Validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('password', $result['errors']);
    }

    public function test_login_fails_with_invalid_email_format(): void
    {
        $credentials = [
            'email' => 'not-an-email',
            'password' => 'password123',
        ];

        $result = $this->authService->login($credentials);

        $this->assertFalse($result['success']);
        $this->assertEquals('Validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('email', $result['errors']);
    }

    public function test_login_fails_with_empty_credentials(): void
    {
        $credentials = [];

        $result = $this->authService->login($credentials);

        $this->assertFalse($result['success']);
        $this->assertEquals('Validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
    }

    public function test_login_handles_exception(): void
    {
        Log::shouldReceive('error')->once();

        $credentials = [
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        Auth::shouldReceive('guard')
            ->with('api')
            ->andThrow(new \Exception('Auth service error'));

        $result = $this->authService->login($credentials);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
    }

    // =====================
    // LOGOUT TESTS
    // =====================

    public function test_logout_successfully(): void
    {
        Auth::shouldReceive('guard')
            ->with('api')
            ->andReturnSelf();

        Auth::shouldReceive('logout')
            ->once()
            ->andReturn(true);

        $result = $this->authService->logout();

        $this->assertTrue($result['success']);
        $this->assertEquals('User logged out successfully', $result['message']);
    }

    public function test_logout_handles_exception(): void
    {
        Log::shouldReceive('error')->once();

        Auth::shouldReceive('guard')
            ->with('api')
            ->andThrow(new \Exception('Logout error'));

        $result = $this->authService->logout();

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
    }

    // =====================
    // REFRESH TESTS
    // =====================

    public function test_refresh_token_successfully(): void
    {
        $mockUser = new User([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        $mockUser->id = 1;
        $mockUser->created_at = now();
        $mockUser->updated_at = now();

        $mockGuard = Mockery::mock('Tymon\JWTAuth\JWTGuard');
        $mockGuard->shouldReceive('refresh')
            ->once()
            ->andReturn('new-jwt-token');
        $mockGuard->shouldReceive('user')
            ->once()
            ->andReturn($mockUser);

        Auth::shouldReceive('guard')
            ->with('api')
            ->twice()
            ->andReturn($mockGuard);

        $result = $this->authService->refresh();

        $this->assertTrue($result['success']);
        $this->assertEquals('Token refreshed successfully', $result['message']);
        $this->assertArrayHasKey('token', $result['data']);
        $this->assertEquals('new-jwt-token', $result['data']['token']);
        $this->assertArrayHasKey('user', $result['data']);
        $this->assertEquals($mockUser->email, $result['data']['user']['email']);
    }

    public function test_refresh_handles_exception_with_invalid_token(): void
    {
        Log::shouldReceive('error')->once();

        $mockGuard = Mockery::mock('Tymon\JWTAuth\JWTGuard');
        $mockGuard->shouldReceive('refresh')
            ->once()
            ->andThrow(new \Exception('Token invalid'));

        Auth::shouldReceive('guard')
            ->with('api')
            ->andReturn($mockGuard);

        $result = $this->authService->refresh();

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
    }

    public function test_refresh_handles_exception_with_expired_token(): void
    {
        Log::shouldReceive('error')->once();

        $mockGuard = Mockery::mock('Tymon\JWTAuth\JWTGuard');
        $mockGuard->shouldReceive('refresh')
            ->once()
            ->andThrow(new \Exception('Token has expired'));

        Auth::shouldReceive('guard')
            ->with('api')
            ->andReturn($mockGuard);

        $result = $this->authService->refresh();

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
    }

    // =====================
    // ME TESTS
    // =====================

    public function test_me_returns_authenticated_user_successfully(): void
    {
        $mockUser = new User([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        $mockUser->id = 1;
        $mockUser->created_at = now();
        $mockUser->updated_at = now();

        Auth::shouldReceive('guard')
            ->with('api')
            ->andReturnSelf();

        Auth::shouldReceive('user')
            ->andReturn($mockUser);

        $result = $this->authService->me();

        $this->assertTrue($result['success']);
        $this->assertEquals('User retrieved successfully', $result['message']);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals($mockUser->email, $result['data']['email']);
        $this->assertEquals($mockUser->name, $result['data']['name']);
    }

    public function test_me_handles_exception_when_user_not_authenticated(): void
    {
        Log::shouldReceive('error')->once();

        Auth::shouldReceive('guard')
            ->with('api')
            ->andReturnSelf();

        Auth::shouldReceive('user')
            ->andThrow(new \Exception('User not authenticated'));

        $result = $this->authService->me();

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
    }

    public function test_me_handles_exception_with_null_user(): void
    {
        Log::shouldReceive('error')->once();

        Auth::shouldReceive('guard')
            ->with('api')
            ->andReturnSelf();

        Auth::shouldReceive('user')
            ->andReturn(null);

        $result = $this->authService->me();

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
    }
}
