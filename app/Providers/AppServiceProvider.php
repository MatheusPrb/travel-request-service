<?php

namespace App\Providers;

use App\Contracts\TravelOrderRepositoryInterface;
use App\Repositories\TravelOrderRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            TravelOrderRepositoryInterface::class,
            TravelOrderRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
