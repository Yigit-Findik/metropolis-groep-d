<?php

namespace App\Observers;

use App\Models\CityFunction;
use App\Models\PendingAction;
use App\Services\CityFunctionEffectValueService;
use App\Services\PendingActionService;
use Illuminate\Support\Facades\Auth;

class CityFunctionObserver
{
    public function __construct(
        private readonly PendingActionService $pendingActionService,
        private readonly CityFunctionEffectValueService $effectValueService,
    ) {
    }

    protected function shouldTrackAdminChange(): bool
    {
        return Auth::user()?->role?->name === 'Administrator';
    }

    public function created(CityFunction $cityFunction): void
    {
        if (! $this->shouldTrackAdminChange()) {
            return;
        }

        $this->pendingActionService->registerTrigger(
            $cityFunction,
            PendingAction::TRIGGER_CREATED,
            Auth::user(),
        );
    }

    public function updated(CityFunction $cityFunction): void
    {
        if ($this->effectValueService->hasChangedEffectValues($cityFunction)) {
            if (! $this->effectValueService->hasCompleteValues($cityFunction)) {
                $this->pendingActionService->registerTrigger(
                    $cityFunction,
                    PendingAction::TRIGGER_EFFECT_VALUES,
                    Auth::user(),
                );

                return;
            }

            $this->pendingActionService->completePendingActionsForFunction($cityFunction);

            return;
        }

        if (! $this->shouldTrackAdminChange()) {
            return;
        }

        $this->pendingActionService->registerTrigger(
            $cityFunction,
            PendingAction::TRIGGER_UPDATED,
            Auth::user(),
        );
    }

    public function deleted(CityFunction $cityFunction): void
    {
        if (! $this->shouldTrackAdminChange()) {
            return;
        }

        $this->pendingActionService->registerTrigger(
            $cityFunction,
            PendingAction::TRIGGER_SOFT_DELETED,
            Auth::user(),
        );
    }
}