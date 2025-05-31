<?php

namespace App\Application\Infrastructure\Providers;

use App\Domain\Ports\WindPacketRepository;
use App\Domain\Ports\WindStorageService;
use App\Domain\Services\WindStorageManager;
use App\Application\Infrastructure\Persistence\Eloquent\EloquentWindPacketRepository;
use Illuminate\Support\ServiceProvider;

class WindBoxServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind the domain ports to their infrastructure adapters
        $this->app->bind(WindPacketRepository::class, EloquentWindPacketRepository::class);
        $this->app->bind(WindStorageService::class, WindStorageManager::class);

        // You can bind use cases directly if they don't have complex dependencies
        // (Laravel's auto-wiring handles simple constructor injections)
        // If they had multiple implementations, you'd bind interfaces here.
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

