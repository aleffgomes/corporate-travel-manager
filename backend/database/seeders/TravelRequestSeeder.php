<?php

namespace Database\Seeders;

use App\Models\RoleModel;
use App\Models\TravelRequestModel;
use App\Models\TravelRequestStatusModel;
use App\Models\UserModel;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TravelRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = UserModel::where('role_id', RoleModel::USER_ID)->get();
        $admin = UserModel::where('role_id', RoleModel::ADMIN_ID)->first();

        if ($users->isEmpty() || !$admin) {
            $this->command->warn('Please run UserSeeder first!');
            return;
        }

        // Pending Travel Requests
        foreach ($users as $user) {
            TravelRequestModel::create([
                'user_id' => $user->id,
                'destination' => 'São Paulo - SP',
                'start_date' => Carbon::now()->addDays(15),
                'end_date' => Carbon::now()->addDays(18),
                'reason' => 'Reunião com cliente estratégico',
                'estimated_cost' => 3500.00,
                'status_id' => TravelRequestStatusModel::PENDING_ID,
            ]);
        }

        // Approved Travel Request
        TravelRequestModel::create([
            'user_id' => $users->first()->id,
            'destination' => 'Rio de Janeiro - RJ',
            'start_date' => Carbon::now()->addDays(30),
            'end_date' => Carbon::now()->addDays(33),
            'reason' => 'Conferência anual da empresa',
            'estimated_cost' => 4200.00,
            'status_id' => TravelRequestStatusModel::APPROVED_ID,
            'approved_by' => $admin->id,
            'approved_at' => Carbon::now()->subDays(2),
        ]);

        // Rejected Travel Request
        TravelRequestModel::create([
            'user_id' => $users->last()->id,
            'destination' => 'Brasília - DF',
            'start_date' => Carbon::now()->addDays(10),
            'end_date' => Carbon::now()->addDays(12),
            'reason' => 'Visita técnica',
            'estimated_cost' => 2800.00,
            'status_id' => TravelRequestStatusModel::REJECTED_ID,
            'rejection_reason' => 'Orçamento da área já foi excedido neste mês',
        ]);

        // Cancelled Travel Request
        TravelRequestModel::create([
            'user_id' => $users->first()->id,
            'destination' => 'Belo Horizonte - MG',
            'start_date' => Carbon::now()->addDays(5),
            'end_date' => Carbon::now()->addDays(7),
            'reason' => 'Workshop técnico',
            'estimated_cost' => 1900.00,
            'status_id' => TravelRequestStatusModel::CANCELLED_ID,
        ]);

        // More sample data
        TravelRequestModel::create([
            'user_id' => $users[1]->id ?? $users->first()->id,
            'destination' => 'Porto Alegre - RS',
            'start_date' => Carbon::now()->addDays(45),
            'end_date' => Carbon::now()->addDays(47),
            'reason' => 'Treinamento de equipe',
            'estimated_cost' => 3100.00,
            'status_id' => TravelRequestStatusModel::PENDING_ID,
        ]);

        TravelRequestModel::create([
            'user_id' => $users[1]->id ?? $users->first()->id,
            'destination' => 'Curitiba - PR',
            'start_date' => Carbon::now()->addDays(20),
            'end_date' => Carbon::now()->addDays(22),
            'reason' => 'Auditoria de qualidade',
            'estimated_cost' => 2600.00,
            'status_id' => TravelRequestStatusModel::APPROVED_ID,
            'approved_by' => $admin->id,
            'approved_at' => Carbon::now()->subDays(5),
        ]);
    }
}
