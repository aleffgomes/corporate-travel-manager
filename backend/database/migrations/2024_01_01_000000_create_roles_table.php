<?php

use App\Models\RoleModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('description')->nullable();
        });

        DB::table('roles')->insert([
            ['id' => RoleModel::USER_ID, 'name' => RoleModel::USER, 'description' => 'Regular user'],
            ['id' => RoleModel::ADMIN_ID, 'name' => RoleModel::ADMIN, 'description' => 'Administrator'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
