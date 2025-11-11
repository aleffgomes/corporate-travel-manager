<?php

namespace App\Repositories;

use App\Contracts\TravelRequestRepositoryInterface;
use App\Models\TravelRequestModel as TravelRequest;
use App\Models\TravelRequestStatusModel;
use Illuminate\Pagination\LengthAwarePaginator;

class TravelRequestRepository implements TravelRequestRepositoryInterface
{
    public function __construct(
        private TravelRequest $model
    ) {}

    public function findByUser(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->where('user_id', $userId)->with(['user.role', 'approver.role', 'status']);

        return $this->applyFilters($query, $filters)->paginate($perPage);
    }

    public function findAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['user.role', 'approver.role', 'status']);

        return $this->applyFilters($query, $filters)->paginate($perPage);
    }    public function create(array $data): TravelRequest
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $travelRequest = $this->model->find($id);
        if (!$travelRequest) {
            return false;
        }
        return $travelRequest->update($data);
    }

    public function updateStatus(int $id, string $status, ?int $approvedBy = null): bool
    {
        $travelRequest = $this->model->find($id);
        if (!$travelRequest) {
            return false;
        }

        $data = ['status' => $status];

        if ($status === TravelRequestStatusModel::APPROVED && $approvedBy) {
            $data['approved_at'] = now();
            $data['approved_by'] = $approvedBy;
        }

        return $travelRequest->update($data);
    }

    public function delete(int $id): bool
    {
        $travelRequest = $this->model->find($id);
        if (!$travelRequest) {
            return false;
        }
        return $travelRequest->delete();
    }

    public function findById(int $id): ?TravelRequest
    {
        return $this->model->with(['user.role', 'approver.role', 'status'])->find($id);
    }

    public function canUserModify(int $travelRequestId, int $userId): bool
    {
        $travelRequest = $this->model->find($travelRequestId);
        if (!$travelRequest) {
            return false;
        }
        return $travelRequest->user_id === $userId;
    }

    public function all()
    {
        return $this->model->with(['user.role', 'approver.role', 'status'])->get();
    }

    public function find(int $id)
    {
        return $this->findById($id);
    }

    private function applyFilters($query, array $filters)
    {
        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (isset($filters['destination'])) {
            $query->byDestination($filters['destination']);
        }

        if (isset($filters['start_date']) || isset($filters['end_date'])) {
            $query->byDateRange(
                $filters['start_date'] ?? null,
                $filters['end_date'] ?? null
            );
        }

        $query->orderBy('created_at', 'desc');

        return $query;
    }
}
