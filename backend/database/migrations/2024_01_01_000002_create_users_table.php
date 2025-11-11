<?php

use App\Models\RoleModel;
use App\Models\UserModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->default(RoleModel::USER_ID)->constrained('roles')->onDelete('restrict');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
            $table->softDeletes();

            $table->index('role_id');
        });

        UserModel::create([
            'name' => 'Administrator',
            'email' => 'admin@corporatetravel.com',
            'password' => Hash::make('admin123'),
            'role_id' => RoleModel::ADMIN_ID,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
