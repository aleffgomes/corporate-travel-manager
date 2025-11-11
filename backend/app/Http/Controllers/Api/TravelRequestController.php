<?php

namespace App\Http\Controllers\Api;

use App\Models\RoleModel;
use App\Services\TravelRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TravelRequestController extends ApiController
{
    public function __construct(
        private TravelRequestService $travelRequestService
    ) {}

    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\UserModel $user */
        $user = auth('api')->user();
        $user->load('role');
        $isAdmin = $user->role->name === RoleModel::ADMIN;

        $filters = [
            'status' => $request->query('status'),
            'destination' => $request->query('destination'),
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
        ];

        $perPage = $request->query('per_page', 15);
        $page = $request->query('page', 1);
        $myRequestsOnly = $request->query('my_requests', 'false') === 'true';

        $result = $this->travelRequestService->getAllForUser(
            $user->id,
            $isAdmin,
            array_filter($filters),
            $perPage,
            $myRequestsOnly
        );

        if (!$result['success']) {
            return $this->errorResponse($result['message'], 500);
        }

        return $this->successResponse($result['data'], 'Travel requests retrieved successfully', 200, $result['pagination'] ?? null);
    }

    public function show(int $id): JsonResponse
    {
        /** @var \App\Models\UserModel $user */
        $user = auth('api')->user();
        $user->load('role');
        $isAdmin = $user->role->name === RoleModel::ADMIN;

        $result = $this->travelRequestService->getById($id, $user->id, $isAdmin);

        if (!$result['success']) {
            $statusCode = $result['message'] === 'Travel request not found' ? 404 : 403;
            return $this->errorResponse($result['message'], $statusCode);
        }

        return $this->successResponse($result['data'], 'Travel request retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        /** @var \App\Models\UserModel $user */
        $user = auth('api')->user();
        $user->load('role');

        $result = $this->travelRequestService->create(
            $request->all(),
            $user->id
        );

        if (!$result['success']) {
            if (isset($result['errors'])) {
                return $this->errorResponse($result['message'], 422, $result['errors']);
            }
            return $this->errorResponse($result['message'], 500);
        }

        return $this->successResponse($result['data'], $result['message'], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        /** @var \App\Models\UserModel $user */
        $user = auth('api')->user();
        $user->load('role');
        $isAdmin = $user->role->name === RoleModel::ADMIN;

        $result = $this->travelRequestService->update(
            $id,
            $request->all(),
            $user->id,
            $isAdmin
        );

        if (!$result['success']) {
            if (isset($result['errors'])) {
                return $this->errorResponse($result['message'], 422, $result['errors']);
            }
            $statusCode = $result['message'] === 'Travel request not found' ? 404 : 403;
            return $this->errorResponse($result['message'], $statusCode);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    public function destroy(int $id): JsonResponse
    {
        /** @var \App\Models\UserModel $user */
        $user = auth('api')->user();
        $user->load('role');
        $isAdmin = $user->role->name === RoleModel::ADMIN;

        $result = $this->travelRequestService->delete($id, $user->id, $isAdmin);

        if (!$result['success']) {
            $statusCode = $result['message'] === 'Travel request not found' ? 404 : 403;
            return $this->errorResponse($result['message'], $statusCode);
        }

        return $this->successResponse(null, $result['message']);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        /** @var \App\Models\UserModel $user */
        $user = auth('api')->user();
        $user->load('role');
        $isAdmin = $user->role->name === RoleModel::ADMIN;

        $result = $this->travelRequestService->updateStatus(
            $id,
            $request->input('status'),
            $user->id,
            $isAdmin,
            $request->input('rejection_reason')
        );

        if (!$result['success']) {
            if (isset($result['errors'])) {
                return $this->errorResponse($result['message'], 422, $result['errors']);
            }
            $statusCode = match($result['message']) {
                'Travel request not found' => 404,
                'Only administrators can update travel request status' => 403,
                default => 400
            };
            return $this->errorResponse($result['message'], $statusCode);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    public function cancel(int $id): JsonResponse
    {
        /** @var \App\Models\UserModel $user */
        $user = auth('api')->user();
        $user->load('role');
        $isAdmin = $user->role->name === RoleModel::ADMIN;

        $result = $this->travelRequestService->cancel($id, $user->id, $isAdmin);

        if (!$result['success']) {
            $statusCode = match($result['message']) {
                'Travel request not found' => 404,
                'Unauthorized access' => 403,
                default => 400
            };
            return $this->errorResponse($result['message'], $statusCode);
        }

        return $this->successResponse($result['data'], $result['message']);
    }
}
