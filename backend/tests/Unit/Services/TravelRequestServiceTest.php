<?php

namespace Tests\Unit\Services;

use App\Contracts\TravelRequestRepositoryInterface;
use App\Jobs\SendTravelRequestNotification;
use App\Models\TravelRequestModel;
use App\Models\TravelRequestStatusModel;
use App\Models\UserModel;
use App\Services\TravelRequestService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class TravelRequestServiceTest extends TestCase
{
    private TravelRequestService $travelRequestService;
    private TravelRequestRepositoryInterface|\Mockery\MockInterface $mockRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRepository = Mockery::mock(TravelRequestRepositoryInterface::class);
        $this->travelRequestService = new TravelRequestService($this->mockRepository);

        Queue::fake();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // =====================
    // GET ALL FOR USER TESTS
    // =====================

    public function test_get_all_for_user_returns_only_user_requests_when_not_admin(): void
    {
        $userId = 1;
        $isAdmin = false;

        $mockCollection = collect([]);
        $mockPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $mockCollection,
            5,
            15,
            1
        );

        $this->mockRepository
            ->shouldReceive('findByUser')
            ->once()
            ->with($userId, [], 15)
            ->andReturn($mockPaginator);

        $result = $this->travelRequestService->getAllForUser($userId, $isAdmin);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('pagination', $result);
    }

    public function test_get_all_for_user_returns_all_requests_when_admin_and_my_requests_false(): void
    {
        $userId = 1;
        $isAdmin = true;
        $myRequestsOnly = false;

        $mockCollection = collect([]);
        $mockPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $mockCollection,
            10,
            15,
            1
        );

        $this->mockRepository
            ->shouldReceive('findAll')
            ->once()
            ->with([], 15)
            ->andReturn($mockPaginator);

        $result = $this->travelRequestService->getAllForUser($userId, $isAdmin, [], 15, $myRequestsOnly);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('pagination', $result);
    }

    public function test_get_all_for_user_returns_only_user_requests_when_admin_but_my_requests_true(): void
    {
        $userId = 1;
        $isAdmin = true;
        $myRequestsOnly = true;

        $mockCollection = collect([]);
        $mockPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $mockCollection,
            5,
            15,
            1
        );

        $this->mockRepository
            ->shouldReceive('findByUser')
            ->once()
            ->with($userId, [], 15)
            ->andReturn($mockPaginator);

        $result = $this->travelRequestService->getAllForUser($userId, $isAdmin, [], 15, $myRequestsOnly);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
    }

    public function test_get_all_for_user_applies_filters_correctly(): void
    {
        $userId = 1;
        $isAdmin = false;
        $filters = ['status' => 'pending', 'destination' => 'Paris'];

        $mockCollection = collect([]);
        $mockPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $mockCollection,
            2,
            15,
            1
        );

        $this->mockRepository
            ->shouldReceive('findByUser')
            ->once()
            ->with($userId, $filters, 15)
            ->andReturn($mockPaginator);

        $result = $this->travelRequestService->getAllForUser($userId, $isAdmin, $filters);

        $this->assertTrue($result['success']);
    }

    public function test_get_all_for_user_respects_per_page_parameter(): void
    {
        $userId = 1;
        $isAdmin = false;
        $perPage = 25;

        $mockCollection = collect([]);
        $mockPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $mockCollection,
            50,
            $perPage,
            1
        );

        $this->mockRepository
            ->shouldReceive('findByUser')
            ->once()
            ->with($userId, [], $perPage)
            ->andReturn($mockPaginator);

        $result = $this->travelRequestService->getAllForUser($userId, $isAdmin, [], $perPage);

        $this->assertTrue($result['success']);
        $this->assertEquals($perPage, $result['pagination']['per_page']);
    }

    // =====================
    // GET BY ID TESTS
    // =====================

    public function test_get_by_id_returns_request_for_owner(): void
    {
        $userId = 1;
        $requestId = 10;
        $isAdmin = false;

        $mockRequest = $this->createMockTravelRequest($requestId, $userId);

        $this->mockRepository
            ->shouldReceive('findById')
            ->once()
            ->with($requestId)
            ->andReturn($mockRequest);

        $result = $this->travelRequestService->getById($requestId, $userId, $isAdmin);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
    }

    public function test_get_by_id_returns_request_for_admin(): void
    {
        $userId = 1;
        $requestId = 10;
        $ownerId = 2;
        $isAdmin = true;

        $mockRequest = $this->createMockTravelRequest($requestId, $ownerId);

        $this->mockRepository
            ->shouldReceive('findById')
            ->once()
            ->with($requestId)
            ->andReturn($mockRequest);

        $result = $this->travelRequestService->getById($requestId, $userId, $isAdmin);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
    }

    public function test_get_by_id_fails_when_not_owner_and_not_admin(): void
    {
        $userId = 1;
        $requestId = 10;
        $ownerId = 2;
        $isAdmin = false;

        $mockRequest = $this->createMockTravelRequest($requestId, $ownerId);

        $this->mockRepository
            ->shouldReceive('findById')
            ->once()
            ->with($requestId)
            ->andReturn($mockRequest);

        $result = $this->travelRequestService->getById($requestId, $userId, $isAdmin);

        $this->assertFalse($result['success']);
        $this->assertEquals('Unauthorized access', $result['message']);
    }

    public function test_get_by_id_fails_when_request_not_found(): void
    {
        $userId = 1;
        $requestId = 999;
        $isAdmin = false;

        $this->mockRepository
            ->shouldReceive('findById')
            ->once()
            ->with($requestId)
            ->andReturn(null);

        $result = $this->travelRequestService->getById($requestId, $userId, $isAdmin);

        $this->assertFalse($result['success']);
        $this->assertEquals('Travel request not found', $result['message']);
    }

    // =====================
    // CREATE TESTS
    // =====================

    public function test_create_succeeds_with_valid_data(): void
    {
        $userId = 1;
        $data = [
            'destination' => 'Paris',
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(10)->format('Y-m-d'),
            'reason' => 'Business meeting',
            'estimated_cost' => 1500.00,
        ];

        $mockRequest = $this->createMockTravelRequest(1, $userId);

        $this->mockRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn($mockRequest);

        $result = $this->travelRequestService->create($data, $userId);

        $this->assertTrue($result['success']);
        $this->assertEquals('Travel request created successfully', $result['message']);
        $this->assertArrayHasKey('data', $result);
    }

    public function test_create_fails_with_missing_destination(): void
    {
        $userId = 1;
        $data = [
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(10)->format('Y-m-d'),
            'reason' => 'Business meeting',
        ];

        $result = $this->travelRequestService->create($data, $userId);

        $this->assertFalse($result['success']);
        $this->assertEquals('Validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('destination', $result['errors']);
    }

    public function test_create_fails_with_missing_start_date(): void
    {
        $userId = 1;
        $data = [
            'destination' => 'Paris',
            'end_date' => now()->addDays(10)->format('Y-m-d'),
            'reason' => 'Business meeting',
        ];

        $result = $this->travelRequestService->create($data, $userId);

        $this->assertFalse($result['success']);
        $this->assertEquals('Validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('start_date', $result['errors']);
    }

    public function test_create_fails_with_missing_end_date(): void
    {
        $userId = 1;
        $data = [
            'destination' => 'Paris',
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'reason' => 'Business meeting',
        ];

        $result = $this->travelRequestService->create($data, $userId);

        $this->assertFalse($result['success']);
        $this->assertEquals('Validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('end_date', $result['errors']);
    }

    public function test_create_fails_with_missing_reason(): void
    {
        $userId = 1;
        $data = [
            'destination' => 'Paris',
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(10)->format('Y-m-d'),
        ];

        $result = $this->travelRequestService->create($data, $userId);

        $this->assertFalse($result['success']);
        $this->assertEquals('Validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('reason', $result['errors']);
    }

    public function test_create_fails_with_start_date_in_past(): void
    {
        $userId = 1;
        $data = [
            'destination' => 'Paris',
            'start_date' => now()->subDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(10)->format('Y-m-d'),
            'reason' => 'Business meeting',
        ];

        $result = $this->travelRequestService->create($data, $userId);

        $this->assertFalse($result['success']);
        $this->assertEquals('Validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
    }

    public function test_create_fails_with_end_date_before_start_date(): void
    {
        $userId = 1;
        $data = [
            'destination' => 'Paris',
            'start_date' => now()->addDays(10)->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'reason' => 'Business meeting',
        ];

        $result = $this->travelRequestService->create($data, $userId);

        $this->assertFalse($result['success']);
        $this->assertEquals('Validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
    }

    public function test_create_fails_with_negative_estimated_cost(): void
    {
        $userId = 1;
        $data = [
            'destination' => 'Paris',
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(10)->format('Y-m-d'),
            'reason' => 'Business meeting',
            'estimated_cost' => -100,
        ];

        $result = $this->travelRequestService->create($data, $userId);

        $this->assertFalse($result['success']);
        $this->assertEquals('Validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
    }

    public function test_create_succeeds_without_estimated_cost(): void
    {
        $userId = 1;
        $data = [
            'destination' => 'Paris',
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(10)->format('Y-m-d'),
            'reason' => 'Business meeting',
        ];

        $mockRequest = $this->createMockTravelRequest(1, $userId);

        $this->mockRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn($mockRequest);

        $result = $this->travelRequestService->create($data, $userId);

        $this->assertTrue($result['success']);
    }

    // =====================
    // UPDATE TESTS
    // =====================

    public function test_update_succeeds_when_owner_and_pending(): void
    {
        $userId = 1;
        $requestId = 10;
        $isAdmin = false;
        $data = [
            'destination' => 'London',
            'estimated_cost' => 2000.00,
        ];

        $mockRequest = $this->createMockTravelRequest($requestId, $userId, 'pending');
        $updatedMockRequest = $this->createMockTravelRequest($requestId, $userId, 'pending');

        $this->mockRepository
            ->shouldReceive('findById')
            ->twice()
            ->with($requestId)
            ->andReturn($mockRequest, $updatedMockRequest);

        $this->mockRepository
            ->shouldReceive('update')
            ->once()
            ->with($requestId, $data);

        $result = $this->travelRequestService->update($requestId, $data, $userId, $isAdmin);

        $this->assertTrue($result['success']);
        $this->assertEquals('Travel request updated successfully', $result['message']);
    }

    public function test_update_succeeds_when_admin(): void
    {
        $userId = 1;
        $requestId = 10;
        $ownerId = 2;
        $isAdmin = true;
        $data = [
            'destination' => 'Berlin',
        ];

        $mockRequest = $this->createMockTravelRequest($requestId, $ownerId, 'pending');
        $updatedMockRequest = $this->createMockTravelRequest($requestId, $ownerId, 'pending');

        $this->mockRepository
            ->shouldReceive('findById')
            ->twice()
            ->with($requestId)
            ->andReturn($mockRequest, $updatedMockRequest);

        $this->mockRepository
            ->shouldReceive('update')
            ->once()
            ->with($requestId, $data);

        $result = $this->travelRequestService->update($requestId, $data, $userId, $isAdmin);

        $this->assertTrue($result['success']);
    }

    public function test_update_fails_when_not_owner_and_not_admin(): void
    {
        $userId = 1;
        $requestId = 10;
        $ownerId = 2;
        $isAdmin = false;
        $data = ['destination' => 'Berlin'];

        $mockRequest = $this->createMockTravelRequest($requestId, $ownerId, 'pending');

        $this->mockRepository
            ->shouldReceive('findById')
            ->once()
            ->with($requestId)
            ->andReturn($mockRequest);

        $result = $this->travelRequestService->update($requestId, $data, $userId, $isAdmin);

        $this->assertFalse($result['success']);
        $this->assertEquals('Unauthorized access', $result['message']);
    }

    public function test_update_fails_when_request_not_found(): void
    {
        $userId = 1;
        $requestId = 999;
        $isAdmin = false;
        $data = ['destination' => 'Berlin'];

        $this->mockRepository
            ->shouldReceive('findById')
            ->once()
            ->with($requestId)
            ->andReturn(null);

        $result = $this->travelRequestService->update($requestId, $data, $userId, $isAdmin);

        $this->assertFalse($result['success']);
        $this->assertEquals('Travel request not found', $result['message']);
    }

    public function test_update_fails_when_status_not_pending(): void
    {
        $userId = 1;
        $requestId = 10;
        $isAdmin = false;
        $data = ['destination' => 'Berlin'];

        $mockRequest = $this->createMockTravelRequest($requestId, $userId, 'approved');

        $this->mockRepository
            ->shouldReceive('findById')
            ->once()
            ->with($requestId)
            ->andReturn($mockRequest);

        $result = $this->travelRequestService->update($requestId, $data, $userId, $isAdmin);

        $this->assertFalse($result['success']);
        $this->assertEquals('Cannot update a travel request that is not pending', $result['message']);
    }

    // =====================
    // UPDATE STATUS TESTS
    // =====================

    public function test_update_status_succeeds_when_admin_approves(): void
    {
        $adminId = 1;
        $requestId = 10;
        $isAdmin = true;
        $status = 'approved';

        $mockRequest = $this->createMockTravelRequest($requestId, 2, 'pending');
        $updatedMockRequest = $this->createMockTravelRequest($requestId, 2, 'approved');

        $this->mockRepository
            ->shouldReceive('findById')
            ->twice()
            ->with($requestId)
            ->andReturn($mockRequest, $updatedMockRequest);

        $this->mockRepository
            ->shouldReceive('update')
            ->once()
            ->with($requestId, Mockery::on(function ($arg) use ($adminId) {
                return isset($arg['status_id']) &&
                       isset($arg['approved_at']) &&
                       $arg['approved_by'] === $adminId;
            }));

        $result = $this->travelRequestService->updateStatus($requestId, $status, $adminId, $isAdmin);

        $this->assertTrue($result['success']);
        $this->assertEquals("Travel request {$status} successfully", $result['message']);
        Queue::assertPushed(SendTravelRequestNotification::class);
    }

    public function test_update_status_succeeds_when_admin_rejects_with_reason(): void
    {
        $adminId = 1;
        $requestId = 10;
        $isAdmin = true;
        $status = 'rejected';
        $rejectionReason = 'Budget constraints';

        $mockRequest = $this->createMockTravelRequest($requestId, 2, 'pending');
        $updatedMockRequest = $this->createMockTravelRequest($requestId, 2, 'rejected');

        $this->mockRepository
            ->shouldReceive('findById')
            ->twice()
            ->with($requestId)
            ->andReturn($mockRequest, $updatedMockRequest);

        $this->mockRepository
            ->shouldReceive('update')
            ->once()
            ->with($requestId, Mockery::on(function ($arg) use ($rejectionReason) {
                return isset($arg['status_id']) &&
                       $arg['rejection_reason'] === $rejectionReason;
            }));

        $result = $this->travelRequestService->updateStatus($requestId, $status, $adminId, $isAdmin, $rejectionReason);

        $this->assertTrue($result['success']);
        Queue::assertPushed(SendTravelRequestNotification::class);
    }

    public function test_update_status_fails_when_not_admin(): void
    {
        $userId = 1;
        $requestId = 10;
        $isAdmin = false;
        $status = 'approved';

        $result = $this->travelRequestService->updateStatus($requestId, $status, $userId, $isAdmin);

        $this->assertFalse($result['success']);
        $this->assertEquals('Only administrators can update travel request status', $result['message']);
    }

    public function test_update_status_fails_when_request_not_found(): void
    {
        $adminId = 1;
        $requestId = 999;
        $isAdmin = true;
        $status = 'approved';

        $this->mockRepository
            ->shouldReceive('findById')
            ->once()
            ->with($requestId)
            ->andReturn(null);

        $result = $this->travelRequestService->updateStatus($requestId, $status, $adminId, $isAdmin);

        $this->assertFalse($result['success']);
        $this->assertEquals('Travel request not found', $result['message']);
    }

    public function test_update_status_fails_with_invalid_status(): void
    {
        $adminId = 1;
        $requestId = 10;
        $isAdmin = true;
        $status = 'invalid_status';

        $mockRequest = $this->createMockTravelRequest($requestId, 2, 'pending');

        $this->mockRepository
            ->shouldReceive('findById')
            ->once()
            ->with($requestId)
            ->andReturn($mockRequest);

        $result = $this->travelRequestService->updateStatus($requestId, $status, $adminId, $isAdmin);

        $this->assertFalse($result['success']);
        $this->assertEquals('Validation failed', $result['message']);
    }

    public function test_update_status_fails_when_cancelling_approved_request(): void
    {
        $adminId = 1;
        $requestId = 10;
        $isAdmin = true;
        $status = 'cancelled';

        $mockRequest = $this->createMockTravelRequest($requestId, 2, 'approved');

        $this->mockRepository
            ->shouldReceive('findById')
            ->once()
            ->with($requestId)
            ->andReturn($mockRequest);

        $result = $this->travelRequestService->updateStatus($requestId, $status, $adminId, $isAdmin);

        $this->assertFalse($result['success']);
        $this->assertEquals('Cannot cancel an approved travel request', $result['message']);
    }

    // =====================
    // CANCEL TESTS
    // =====================

    public function test_cancel_succeeds_when_owner_and_pending(): void
    {
        $userId = 1;
        $requestId = 10;
        $isAdmin = false;

        $mockRequest = $this->createMockTravelRequest($requestId, $userId, 'pending');
        $updatedMockRequest = $this->createMockTravelRequest($requestId, $userId, 'cancelled');

        $this->mockRepository
            ->shouldReceive('findById')
            ->twice()
            ->with($requestId)
            ->andReturn($mockRequest, $updatedMockRequest);

        $this->mockRepository
            ->shouldReceive('update')
            ->once()
            ->with($requestId, ['status_id' => 4]);

        $result = $this->travelRequestService->cancel($requestId, $userId, $isAdmin);

        $this->assertTrue($result['success']);
        $this->assertEquals('Travel request cancelled successfully', $result['message']);
    }

    public function test_cancel_succeeds_when_admin(): void
    {
        $adminId = 1;
        $requestId = 10;
        $ownerId = 2;
        $isAdmin = true;

        $mockRequest = $this->createMockTravelRequest($requestId, $ownerId, 'pending');
        $updatedMockRequest = $this->createMockTravelRequest($requestId, $ownerId, 'cancelled');

        $this->mockRepository
            ->shouldReceive('findById')
            ->twice()
            ->with($requestId)
            ->andReturn($mockRequest, $updatedMockRequest);

        $this->mockRepository
            ->shouldReceive('update')
            ->once();

        $result = $this->travelRequestService->cancel($requestId, $adminId, $isAdmin);

        $this->assertTrue($result['success']);
    }

    public function test_cancel_fails_when_not_owner_and_not_admin(): void
    {
        $userId = 1;
        $requestId = 10;
        $ownerId = 2;
        $isAdmin = false;

        $mockRequest = $this->createMockTravelRequest($requestId, $ownerId, 'pending');

        $this->mockRepository
            ->shouldReceive('findById')
            ->once()
            ->with($requestId)
            ->andReturn($mockRequest);

        $result = $this->travelRequestService->cancel($requestId, $userId, $isAdmin);

        $this->assertFalse($result['success']);
        $this->assertEquals('Unauthorized access', $result['message']);
    }

    public function test_cancel_fails_when_request_not_found(): void
    {
        $userId = 1;
        $requestId = 999;
        $isAdmin = false;

        $this->mockRepository
            ->shouldReceive('findById')
            ->once()
            ->with($requestId)
            ->andReturn(null);

        $result = $this->travelRequestService->cancel($requestId, $userId, $isAdmin);

        $this->assertFalse($result['success']);
        $this->assertEquals('Travel request not found', $result['message']);
    }

    public function test_cancel_fails_when_already_approved(): void
    {
        $userId = 1;
        $requestId = 10;
        $isAdmin = false;

        $mockRequest = $this->createMockTravelRequest($requestId, $userId, 'approved');

        $this->mockRepository
            ->shouldReceive('findById')
            ->once()
            ->with($requestId)
            ->andReturn($mockRequest);

        $result = $this->travelRequestService->cancel($requestId, $userId, $isAdmin);

        $this->assertFalse($result['success']);
        $this->assertEquals('Cannot cancel an approved travel request', $result['message']);
    }

    public function test_cancel_fails_when_already_cancelled(): void
    {
        $userId = 1;
        $requestId = 10;
        $isAdmin = false;

        $mockRequest = $this->createMockTravelRequest($requestId, $userId, 'cancelled');

        $this->mockRepository
            ->shouldReceive('findById')
            ->once()
            ->with($requestId)
            ->andReturn($mockRequest);

        $result = $this->travelRequestService->cancel($requestId, $userId, $isAdmin);

        $this->assertFalse($result['success']);
        $this->assertEquals('Travel request is already cancelled or rejected', $result['message']);
    }

    public function test_cancel_fails_when_already_rejected(): void
    {
        $userId = 1;
        $requestId = 10;
        $isAdmin = false;

        $mockRequest = $this->createMockTravelRequest($requestId, $userId, 'rejected');

        $this->mockRepository
            ->shouldReceive('findById')
            ->once()
            ->with($requestId)
            ->andReturn($mockRequest);

        $result = $this->travelRequestService->cancel($requestId, $userId, $isAdmin);

        $this->assertFalse($result['success']);
        $this->assertEquals('Travel request is already cancelled or rejected', $result['message']);
    }

    // =====================
    // DELETE TESTS
    // =====================

    public function test_delete_succeeds_when_owner_and_pending(): void
    {
        $userId = 1;
        $requestId = 10;
        $isAdmin = false;

        $mockRequest = $this->createMockTravelRequest($requestId, $userId, 'pending');

        $this->mockRepository
            ->shouldReceive('findById')
            ->once()
            ->with($requestId)
            ->andReturn($mockRequest);

        $this->mockRepository
            ->shouldReceive('delete')
            ->once()
            ->with($requestId);

        $result = $this->travelRequestService->delete($requestId, $userId, $isAdmin);

        $this->assertTrue($result['success']);
        $this->assertEquals('Travel request deleted successfully', $result['message']);
    }

    public function test_delete_succeeds_when_admin(): void
    {
        $adminId = 1;
        $requestId = 10;
        $ownerId = 2;
        $isAdmin = true;

        $mockRequest = $this->createMockTravelRequest($requestId, $ownerId, 'pending');

        $this->mockRepository
            ->shouldReceive('findById')
            ->once()
            ->with($requestId)
            ->andReturn($mockRequest);

        $this->mockRepository
            ->shouldReceive('delete')
            ->once()
            ->with($requestId);

        $result = $this->travelRequestService->delete($requestId, $adminId, $isAdmin);

        $this->assertTrue($result['success']);
    }

    public function test_delete_fails_when_not_owner_and_not_admin(): void
    {
        $userId = 1;
        $requestId = 10;
        $ownerId = 2;
        $isAdmin = false;

        $mockRequest = $this->createMockTravelRequest($requestId, $ownerId, 'pending');

        $this->mockRepository
            ->shouldReceive('findById')
            ->once()
            ->with($requestId)
            ->andReturn($mockRequest);

        $result = $this->travelRequestService->delete($requestId, $userId, $isAdmin);

        $this->assertFalse($result['success']);
        $this->assertEquals('Unauthorized access', $result['message']);
    }

    public function test_delete_fails_when_request_not_found(): void
    {
        $userId = 1;
        $requestId = 999;
        $isAdmin = false;

        $this->mockRepository
            ->shouldReceive('findById')
            ->once()
            ->with($requestId)
            ->andReturn(null);

        $result = $this->travelRequestService->delete($requestId, $userId, $isAdmin);

        $this->assertFalse($result['success']);
        $this->assertEquals('Travel request not found', $result['message']);
    }

    public function test_delete_fails_when_status_not_pending(): void
    {
        $userId = 1;
        $requestId = 10;
        $isAdmin = false;

        $mockRequest = $this->createMockTravelRequest($requestId, $userId, 'approved');

        $this->mockRepository
            ->shouldReceive('findById')
            ->once()
            ->with($requestId)
            ->andReturn($mockRequest);

        $result = $this->travelRequestService->delete($requestId, $userId, $isAdmin);

        $this->assertFalse($result['success']);
        $this->assertEquals('Cannot delete a travel request that is not pending', $result['message']);
    }

    // =====================
    // HELPER METHODS
    // =====================

    private function createMockTravelRequest(int $id, int $userId, string $statusName = 'pending'): TravelRequestModel
    {
        /** @var TravelRequestModel|\Mockery\MockInterface $mockRequest */
        $mockRequest = Mockery::mock(TravelRequestModel::class)->makePartial();
        $mockRequest->id = $id;
        $mockRequest->user_id = $userId;
        $mockRequest->destination = 'Paris';
        $mockRequest->start_date = now()->addDays(5);
        $mockRequest->end_date = now()->addDays(10);
        $mockRequest->reason = 'Business meeting';
        $mockRequest->estimated_cost = 1500.00;
        $mockRequest->status_id = 1;
        $mockRequest->created_at = now();
        $mockRequest->updated_at = now();

        $mockStatus = new \stdClass();
        $mockStatus->name = $statusName;
        $mockStatus->id = match($statusName) {
            'pending' => 1,
            'approved' => 2,
            'rejected' => 3,
            'cancelled' => 4,
            default => 1,
        };

        $mockUser = new \stdClass();
        $mockUser->id = $userId;
        $mockUser->name = 'Test User';
        $mockUser->email = 'test@example.com';

        $mockRequest->status = $mockStatus;
        $mockRequest->user = $mockUser;

        $mockRequest->shouldReceive('load')
            ->andReturnSelf();

        return $mockRequest;
    }
}
