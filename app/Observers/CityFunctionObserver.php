<?php

namespace App\Observers;

use App\Models\CityFunction;
use App\Models\PendingAction;
use App\Services\CityFunctionEffectValueService;
use App\Services\PendingActionService;
use Illuminate\Support\Facades\Auth;

class CityFunctionObserver
{
    private function pendingActionService(): PendingActionService
    {
        // Resolve the service lazily so the observer stays constructor-free.
        return app(PendingActionService::class);
    }

    private function effectValueService(): CityFunctionEffectValueService
    {
        // Resolve the helper only when an update needs to inspect effect columns.
        return app(CityFunctionEffectValueService::class);
    }

    protected function shouldTrackAdminChange(): bool
    {
        // Check the authenticated user's role name directly, because the observer runs outside a controller context.
        return Auth::user()?->role?->name === 'Administrator';
    }

    public function created(CityFunction $cityFunction): void
    {
        if (! $this->shouldTrackAdminChange()) {
            return;
        }

        // Only queue a creation follow-up when required effect values are still missing or invalid.
        if ($this->effectValueService()->hasCompleteValues($cityFunction)) {
            return;
        }

        $this->pendingActionService()->registerTrigger(
            $cityFunction,
            PendingAction::TRIGGER_CREATED,
            Auth::user(),
        );
    }

    public function updated(CityFunction $cityFunction): void
    {
        // Effect values are handled first: a partial update opens a pending action, and a complete update closes them.
        if ($this->effectValueService()->hasChangedEffectValues($cityFunction)) {
            if (! $this->effectValueService()->hasCompleteValues($cityFunction)) {
                $this->pendingActionService()->registerTrigger(
                    $cityFunction,
                    PendingAction::TRIGGER_EFFECT_VALUES,
                    Auth::user(),
                );

                return;
            }

            $this->pendingActionService()->completePendingActionsForFunction($cityFunction);

            return;
        }

        // Non-effect updates (e.g. name/category) no longer create pending actions.
    }

    public function deleted(CityFunction $cityFunction): void
    {
        // Soft deletes no longer create pending actions.
    }
}