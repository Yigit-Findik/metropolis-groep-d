<?php

namespace App\Providers;

use App\Models\PendingAction;
use App\Models\CityFunction;
use App\Observers\CityFunctionObserver;
use App\Policies\PendingActionPolicy;
use Illuminate\Support\Facades\Gate;
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
        CityFunction::observe(CityFunctionObserver::class);
        Gate::policy(PendingAction::class, PendingActionPolicy::class);

        // From `development`: when a new city function is added, notify effects experts.
        // Ensure `App\Events\NewFunctionAdded` and `App\Listeners\SendNewFunctionNotification`
        // exist before relying on this behaviour.
        Event::listen(\App\Events\NewFunctionAdded::class, \App\Listeners\SendNewFunctionNotification::class);
    }
}
