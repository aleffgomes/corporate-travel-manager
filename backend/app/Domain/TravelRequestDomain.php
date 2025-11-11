<?php

namespace App\Domain;

use App\Models\TravelRequestModel as TravelRequest;
use App\Models\TravelRequestStatusModel;

class TravelRequestDomain
{
    private int $id;
    private int $userId;
    private string $destination;
    private string $startDate;
    private string $endDate;
    private string $reason;
    private ?float $estimatedCost;
    private string $status;
    private ?string $approvedAt;
    private ?int $approvedBy;
    private ?string $rejectionReason;
    private string $createdAt;
    private string $updatedAt;
    private ?array $user;
    private ?array $approver;

    private function __construct(
        int $id,
        int $userId,
        string $destination,
        string $startDate,
        string $endDate,
        string $reason,
        ?float $estimatedCost,
        string $status,
        ?string $approvedAt,
        ?int $approvedBy,
        ?string $rejectionReason,
        string $createdAt,
        string $updatedAt,
        ?array $user = null,
        ?array $approver = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->destination = $destination;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->reason = $reason;
        $this->estimatedCost = $estimatedCost;
        $this->status = $status;
        $this->approvedAt = $approvedAt;
        $this->approvedBy = $approvedBy;
        $this->rejectionReason = $rejectionReason;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->user = $user;
        $this->approver = $approver;
    }

    public static function fromModel(TravelRequest $travelRequest): self
    {
        return new self(
            $travelRequest->id,
            $travelRequest->user_id,
            $travelRequest->destination,
            $travelRequest->start_date->format('Y-m-d'),
            $travelRequest->end_date->format('Y-m-d'),
            $travelRequest->reason,
            $travelRequest->estimated_cost ? (float) $travelRequest->estimated_cost : null,
            $travelRequest->relationLoaded('status') && $travelRequest->status
                ? $travelRequest->status->name
                : TravelRequestStatusModel::PENDING,
            $travelRequest->approved_at?->toIso8601String(),
            $travelRequest->approved_by,
            $travelRequest->rejection_reason,
            $travelRequest->created_at->toIso8601String(),
            $travelRequest->updated_at->toIso8601String(),
            $travelRequest->relationLoaded('user') && $travelRequest->user
                ? [
                    'id' => $travelRequest->user->id,
                    'name' => $travelRequest->user->name,
                    'email' => $travelRequest->user->email,
                ]
                : null,
            $travelRequest->relationLoaded('approver') && $travelRequest->approver
                ? [
                    'id' => $travelRequest->approver->id,
                    'name' => $travelRequest->approver->name,
                    'email' => $travelRequest->approver->email,
                ]
                : null
        );
    }

    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'user_id' => $this->userId,
            'destination' => $this->destination,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'reason' => $this->reason,
            'estimated_cost' => $this->estimatedCost,
            'status' => $this->status,
            'approved_at' => $this->approvedAt,
            'approved_by' => $this->approvedBy,
            'rejection_reason' => $this->rejectionReason,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];

        if ($this->user) {
            $data['user'] = $this->user;
        }

        if ($this->approver) {
            $data['approver'] = $this->approver;
        }

        return $data;
    }
}
