<?php

use App\Models\TravelRequestStatusModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_request_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('description')->nullable();
        });

        DB::table('travel_request_statuses')->insert([
            ['id' => TravelRequestStatusModel::PENDING_ID, 'name' => TravelRequestStatusModel::PENDING, 'description' => 'Pending approval'],
            ['id' => TravelRequestStatusModel::APPROVED_ID, 'name' => TravelRequestStatusModel::APPROVED, 'description' => 'Approved'],
            ['id' => TravelRequestStatusModel::REJECTED_ID, 'name' => TravelRequestStatusModel::REJECTED, 'description' => 'Rejected'],
            ['id' => TravelRequestStatusModel::CANCELLED_ID, 'name' => TravelRequestStatusModel::CANCELLED, 'description' => 'Cancelled'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_request_statuses');
    }
};
