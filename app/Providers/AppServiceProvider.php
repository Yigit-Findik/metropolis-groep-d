<?php

namespace App\Providers;

use App\Events\NewFunctionAdded;
use App\Listeners\SendNewFunctionNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // EFF.2 When a new city function is added, notify all effects experts by email.
        Event::listen(NewFunctionAdded::class, SendNewFunctionNotification::class);
    }
}
