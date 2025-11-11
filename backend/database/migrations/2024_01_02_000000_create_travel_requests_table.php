<?php

use App\Models\TravelRequestStatusModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('destination');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason');
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->foreignId('status_id')->default(TravelRequestStatusModel::PENDING_ID)->constrained('travel_request_statuses')->onDelete('restrict');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status_id']);
            $table->index(['start_date', 'end_date']);
            $table->index('destination');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_requests');
    }
};
