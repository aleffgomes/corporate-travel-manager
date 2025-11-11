<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TravelRequestModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'travel_requests';

    protected $fillable = [
        'user_id',
        'destination',
        'start_date',
        'end_date',
        'reason',
        'estimated_cost',
        'status_id',
        'approved_at',
        'approved_by',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'approved_at' => 'datetime',
            'estimated_cost' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'approved_by');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(TravelRequestStatusModel::class, 'status_id');
    }

    public function scopePending($query)
    {
        return $query->whereHas('status', function($q) {
            $q->where('name', TravelRequestStatusModel::PENDING);
        });
    }

    public function scopeApproved($query)
    {
        return $query->whereHas('status', function($q) {
            $q->where('name', TravelRequestStatusModel::APPROVED);
        });
    }

    public function scopeByStatus($query, string $statusName)
    {
        return $query->whereHas('status', function($q) use ($statusName) {
            $q->where('name', $statusName);
        });
    }

    public function scopeByDestination($query, string $destination)
    {
        return $query->where('destination', 'like', "%{$destination}%");
    }

    public function scopeByDateRange($query, ?string $startDate, ?string $endDate)
    {
        if ($startDate) {
            $query->where('start_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('end_date', '<=', $endDate);
        }
        return $query;
    }
}
