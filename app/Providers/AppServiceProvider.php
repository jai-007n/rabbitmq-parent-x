<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// use App\Queue\Connectors\RabbitConnector;
use Illuminate\Support\Facades\Queue;

class AppServiceProvider extends ServiceProvider
{


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Queue::extend('rabbitmq', function () {
        //     return new RabbitConnector();
        // });
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
}
