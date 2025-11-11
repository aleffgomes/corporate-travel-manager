<?php

namespace App\Providers;

use App\Contracts\UserRepositoryInterface;
use App\Contracts\TravelRequestRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\TravelRequestRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(TravelRequestRepositoryInterface::class, TravelRequestRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
