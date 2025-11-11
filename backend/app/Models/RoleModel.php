<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleModel extends Model
{
    // Role constants
    public const USER = 'user';
    public const ADMIN = 'admin';

    // Role IDs
    public const USER_ID = 1;
    public const ADMIN_ID = 2;

    protected $table = 'roles';

    protected $fillable = [
        'name',
        'description',
    ];

    public $timestamps = false;
}
