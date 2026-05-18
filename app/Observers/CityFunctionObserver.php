<?php

namespace App\Observers;

use App\Models\CityFunction;
use App\Models\PendingAction;
use App\Services\CityFunctionEffectValueService;
use App\Services\PendingActionService;
use Illuminate\Support\Facades\Auth;
use App\Models\ActionHistory;

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
        return in_array(Auth::user()?->role?->name, ['Administrator', 'Expert in effects'], true);
    }

    public function created(CityFunction $cityFunction): void
    {
        if (! $this->shouldTrackAdminChange()) {
            return;
        }

        $this->pendingActionService()->registerTrigger(
            $cityFunction,
            PendingAction::TRIGGER_CREATED,
            Auth::user(),
        );

        ActionHistory::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'cell_id' => null,
            'old_city_function_id' => null,
            'new_city_function_id' => $cityFunction->id,
            'details' => [
                'new' => $cityFunction->toArray(),
            ],
        ]);
    }

    public function updated(CityFunction $cityFunction): void
    {
        $isAdminChange = $this->shouldTrackAdminChange();

        // Effect values are handled first: a partial update opens a pending action, and a complete update closes them.
        if ($this->effectValueService()->hasChangedEffectValues($cityFunction)) {
            if ($isAdminChange) {
                $changes = $cityFunction->getChanges();
                $original = $cityFunction->getOriginal();

                ActionHistory::create([
                    'user_id' => Auth::id(),
                    'action' => 'update',
                    'cell_id' => null,
                    'old_city_function_id' => null,
                    'new_city_function_id' => $cityFunction->id,
                    'details' => [
                        'changed' => array_intersect_key($changes, array_flip($this->effectValueService()->effectColumns())),
                        'original' => array_intersect_key($original, $changes),
                    ],
                ]);
            }

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

        // Log all updates performed by administrators and effects experts (including effect changes)
        if ($isAdminChange) {
            $changes = $cityFunction->getChanges();
            $original = $cityFunction->getOriginal();

            ActionHistory::create([
                'user_id' => Auth::id(),
                'action' => 'update',
                'cell_id' => null,
                'old_city_function_id' => null,
                'new_city_function_id' => $cityFunction->id,
                'details' => [
                    'changed' => $changes,
                    'original' => array_intersect_key($original, $changes),
                ],
            ]);
        }
    }

    public function deleted(CityFunction $cityFunction): void
    {
        // Soft deletes no longer create pending actions. Still log the delete action.
        if ($this->shouldTrackAdminChange()) {
            ActionHistory::create([
                'user_id' => Auth::id(),
                'action' => 'delete',
                'cell_id' => null,
                'old_city_function_id' => null,
                'new_city_function_id' => $cityFunction->id,
                'details' => [
                    'original' => $cityFunction->toArray(),
                ],
            ]);
        }
    }
}