<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TravelRequestStatusModel extends Model
{
    // Status constants
    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';
    public const CANCELLED = 'cancelled';

    // Status IDs
    public const PENDING_ID = 1;
    public const APPROVED_ID = 2;
    public const REJECTED_ID = 3;
    public const CANCELLED_ID = 4;

    protected $table = 'travel_request_statuses';

    protected $fillable = [
        'name',
        'description',
    ];

    public $timestamps = false;
}
