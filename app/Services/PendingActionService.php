<?php

namespace App\Services;

use App\Models\CityFunction;
use App\Models\PendingAction;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PendingActionService
{
    public function __construct(
        private readonly CityFunctionEffectValueService $effectValueService,
    ) {
    }

    public function registerTrigger(CityFunction $function, string $triggerType, ?User $user = null): PendingAction
    {
        $pendingAction = PendingAction::query()
            ->where('city_function_id', $function->id)
            ->where('trigger_type', $triggerType)
            ->where('status', PendingAction::STATUS_PENDING)
            ->first();

        if ($pendingAction) {
            return $pendingAction;
        }

        $pendingAction = PendingAction::create([
            'city_function_id' => $function->id,
            'function_name' => $function->name,
            'trigger_type' => $triggerType,
            'status' => PendingAction::STATUS_PENDING,
            'created_by_user_id' => $user?->id,
        ]);

        Log::info('Pending action created', [
            'pending_action_id' => $pendingAction->id,
            'city_function_id' => $function->id,
            'trigger_type' => $triggerType,
        ]);

        return $pendingAction;
    }

    public function completePendingActionsForFunction(CityFunction $function): int
    {
        if (! $this->effectValueService->hasCompleteValues($function)) {
            return 0;
        }

        $pendingActions = PendingAction::query()
            ->where('city_function_id', $function->id)
            ->where('status', PendingAction::STATUS_PENDING)
            ->get();

        $completedCount = 0;

        foreach ($pendingActions as $pendingAction) {
            $pendingAction->forceFill([
                'status' => PendingAction::STATUS_COMPLETED,
                'completed_at' => now(),
            ])->save();

            $completedCount++;
        }

        if ($completedCount > 0) {
            Log::info('Pending actions completed', [
                'city_function_id' => $function->id,
                'completed_count' => $completedCount,
            ]);
        }

        return $completedCount;
    }
}