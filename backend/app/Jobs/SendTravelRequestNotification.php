<?php

namespace App\Jobs;

use App\Models\TravelRequestModel;
use App\Models\TravelRequestStatusModel;
use App\Models\UserModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTravelRequestNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public TravelRequestModel $travelRequest,
        public string $action
    ) {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        try {
            $user = $this->travelRequest->user;

            if (!$user) {
                Log::warning("User not found for travel request {$this->travelRequest->id}");
                return;
            }

            $message = $this->buildNotificationMessage();

            Log::info("Travel Request Notification", [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'travel_request_id' => $this->travelRequest->id,
                'action' => $this->action,
                'status' => $this->travelRequest->status ? $this->travelRequest->status->name : TravelRequestStatusModel::PENDING,
                'message' => $message,
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to send travel request notification", [
                'travel_request_id' => $this->travelRequest->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function buildNotificationMessage(): string
    {
        $destination = $this->travelRequest->destination;
        $startDate = $this->travelRequest->start_date->format('d/m/Y');

        return match($this->action) {
            'approved' => "Sua solicitação de viagem para {$destination} em {$startDate} foi APROVADA!",
            'rejected' => "Sua solicitação de viagem para {$destination} em {$startDate} foi REJEITADA. Motivo: {$this->travelRequest->rejection_reason}",
            'cancelled' => "Sua solicitação de viagem para {$destination} em {$startDate} foi CANCELADA.",
            default => "Atualização na sua solicitação de viagem para {$destination}.",
        };
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Travel request notification job failed", [
            'travel_request_id' => $this->travelRequest->id,
            'action' => $this->action,
            'error' => $exception->getMessage(),
        ]);
    }
}
