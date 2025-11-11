<?php

namespace App\Services;

use App\Contracts\TravelRequestRepositoryInterface;
use App\Domain\TravelRequestDomain;
use App\Jobs\SendTravelRequestNotification;
use App\Models\RoleModel;
use App\Models\TravelRequestStatusModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TravelRequestService extends BaseService
{
    public function __construct(
        private TravelRequestRepositoryInterface $travelRequestRepository
    ) {}

    public function getAllForUser(int $userId, bool $isAdmin, array $filters = [], int $perPage = 15, bool $myRequestsOnly = false): array
    {
        try {
            if ($myRequestsOnly || !$isAdmin) {
                $travelRequests = $this->travelRequestRepository->findByUser($userId, $filters, $perPage);
            } else {
                $travelRequests = $this->travelRequestRepository->findAll($filters, $perPage);
            }

            $data = $travelRequests->map(function ($travelRequest) {
                return TravelRequestDomain::fromModel($travelRequest)->toArray();
            });

            return [
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $travelRequests->currentPage(),
                    'per_page' => $travelRequests->perPage(),
                    'total' => $travelRequests->total(),
                    'last_page' => $travelRequests->lastPage(),
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve travel requests',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getById(int $id, int $userId, bool $isAdmin): array
    {
        try {
            $travelRequest = $this->travelRequestRepository->findById($id);

            if (!$travelRequest) {
                return [
                    'success' => false,
                    'message' => 'Travel request not found',
                ];
            }

            if (!$isAdmin && $travelRequest->user_id !== $userId) {
                return [
                    'success' => false,
                    'message' => 'Unauthorized access',
                ];
            }

            return [
                'success' => true,
                'data' => TravelRequestDomain::fromModel($travelRequest)->toArray(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve travel request',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function create(array $data, int $userId): array
    {
        try {
            $validator = Validator::make($data, [
                'destination' => 'required|string|max:255',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'required|string',
                'estimated_cost' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $data['user_id'] = $userId;
            $data['status_id'] = TravelRequestStatusModel::PENDING_ID;

            $travelRequest = $this->travelRequestRepository->create($data);
            $travelRequest->load(['user.role', 'approver.role', 'status']);

            return [
                'success' => true,
                'message' => 'Travel request created successfully',
                'data' => TravelRequestDomain::fromModel($travelRequest)->toArray(),
            ];
        } catch (ValidationException $e) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create travel request',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function update(int $id, array $data, int $userId, bool $isAdmin): array
    {
        try {
            $travelRequest = $this->travelRequestRepository->findById($id);

            if (!$travelRequest) {
                return [
                    'success' => false,
                    'message' => 'Travel request not found',
                ];
            }

            if (!$isAdmin && $travelRequest->user_id !== $userId) {
                return [
                    'success' => false,
                    'message' => 'Unauthorized access',
                ];
            }

            if ($travelRequest->status->name !== TravelRequestStatusModel::PENDING) {
                return [
                    'success' => false,
                    'message' => 'Cannot update a travel request that is not pending',
                ];
            }

            $validator = Validator::make($data, [
                'destination' => 'sometimes|required|string|max:255',
                'start_date' => 'sometimes|required|date|after_or_equal:today',
                'end_date' => 'sometimes|required|date|after_or_equal:start_date',
                'reason' => 'sometimes|required|string',
                'estimated_cost' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $this->travelRequestRepository->update($id, $data);
            $updatedTravelRequest = $this->travelRequestRepository->findById($id);

            return [
                'success' => true,
                'message' => 'Travel request updated successfully',
                'data' => TravelRequestDomain::fromModel($updatedTravelRequest)->toArray(),
            ];
        } catch (ValidationException $e) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update travel request',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function updateStatus(int $id, string $status, int $adminId, bool $isAdmin, ?string $rejectionReason = null): array
    {
        try {
            if (!$isAdmin) {
                return [
                    'success' => false,
                    'message' => 'Only administrators can update travel request status',
                ];
            }

            $travelRequest = $this->travelRequestRepository->findById($id);

            if (!$travelRequest) {
                return [
                    'success' => false,
                    'message' => 'Travel request not found',
                ];
            }

            $validator = Validator::make(['status' => $status], [
                'status' => 'required|in:' . implode(',', [
                    TravelRequestStatusModel::APPROVED,
                    TravelRequestStatusModel::REJECTED,
                    TravelRequestStatusModel::CANCELLED
                ]),
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $statusModel = TravelRequestStatusModel::where('name', $status)->first();
            if (!$statusModel) {
                return [
                    'success' => false,
                    'message' => 'Invalid status',
                ];
            }

            if ($status === TravelRequestStatusModel::CANCELLED && $travelRequest->status->name === TravelRequestStatusModel::APPROVED) {
                return [
                    'success' => false,
                    'message' => 'Cannot cancel an approved travel request',
                ];
            }

            $updateData = ['status_id' => $statusModel->id];
            if ($status === TravelRequestStatusModel::REJECTED && $rejectionReason) {
                $updateData['rejection_reason'] = $rejectionReason;
            }

            if ($status === TravelRequestStatusModel::APPROVED) {
                $updateData['approved_at'] = now();
                $updateData['approved_by'] = $adminId;
            }

            $this->travelRequestRepository->update($id, $updateData);

            $updatedTravelRequest = $this->travelRequestRepository->findById($id);

            if (in_array($status, [TravelRequestStatusModel::APPROVED, TravelRequestStatusModel::REJECTED])) {
                SendTravelRequestNotification::dispatch($updatedTravelRequest, $status);
            }

            return [
                'success' => true,
                'message' => "Travel request {$status} successfully",
                'data' => TravelRequestDomain::fromModel($updatedTravelRequest)->toArray(),
            ];
        } catch (ValidationException $e) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update travel request status',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function cancel(int $id, int $userId, bool $isAdmin): array
    {
        try {
            $travelRequest = $this->travelRequestRepository->findById($id);

            if (!$travelRequest) {
                return [
                    'success' => false,
                    'message' => 'Travel request not found',
                ];
            }

            if (!$isAdmin && $travelRequest->user_id !== $userId) {
                return [
                    'success' => false,
                    'message' => 'Unauthorized access',
                ];
            }

            if ($travelRequest->status->name === TravelRequestStatusModel::APPROVED) {
                return [
                    'success' => false,
                    'message' => 'Cannot cancel an approved travel request',
                ];
            }

            if (in_array($travelRequest->status->name, [TravelRequestStatusModel::CANCELLED, TravelRequestStatusModel::REJECTED])) {
                return [
                    'success' => false,
                    'message' => 'Travel request is already cancelled or rejected',
                ];
            }

            $cancelledStatus = TravelRequestStatusModel::where('name', TravelRequestStatusModel::CANCELLED)->first();
            if (!$cancelledStatus) {
                return [
                    'success' => false,
                    'message' => 'Status not found',
                ];
            }

            $this->travelRequestRepository->update($id, ['status_id' => $cancelledStatus->id]);
            $updatedTravelRequest = $this->travelRequestRepository->findById($id);

            return [
                'success' => true,
                'message' => 'Travel request cancelled successfully',
                'data' => TravelRequestDomain::fromModel($updatedTravelRequest)->toArray(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to cancel travel request',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function delete(int $id, int $userId, bool $isAdmin): array
    {
        try {
            $travelRequest = $this->travelRequestRepository->findById($id);

            if (!$travelRequest) {
                return [
                    'success' => false,
                    'message' => 'Travel request not found',
                ];
            }

            if (!$isAdmin && $travelRequest->user_id !== $userId) {
                return [
                    'success' => false,
                    'message' => 'Unauthorized access',
                ];
            }

            if ($travelRequest->status->name !== TravelRequestStatusModel::PENDING) {
                return [
                    'success' => false,
                    'message' => 'Cannot delete a travel request that is not pending',
                ];
            }

            $this->travelRequestRepository->delete($id);

            return [
                'success' => true,
                'message' => 'Travel request deleted successfully',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete travel request',
                'error' => $e->getMessage(),
            ];
        }
    }
}
