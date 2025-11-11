<?php

namespace App\Contracts;

use App\Models\TravelRequestModel as TravelRequest;
use Illuminate\Pagination\LengthAwarePaginator;

interface TravelRequestRepositoryInterface extends RepositoryInterface
{
    public function findByUser(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findAll(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): TravelRequest;

    public function update(int $id, array $data): bool;

    public function updateStatus(int $id, string $status, ?int $approvedBy = null): bool;

    public function delete(int $id): bool;

    public function findById(int $id): ?TravelRequest;

    public function canUserModify(int $travelRequestId, int $userId): bool;
}
