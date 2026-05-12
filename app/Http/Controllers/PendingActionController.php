<?php

namespace App\Http\Controllers;

use App\Models\PendingAction;
use App\Services\CityFunctionEffectValueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PendingActionController extends Controller
{
    public function __construct(
        private readonly CityFunctionEffectValueService $effectValueService,
    ) {
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', PendingAction::class);

        $statusFilter = $request->string('status')->toString();
        $triggerTypeFilter = $request->string('trigger_type')->toString();

        // Keep the filter labels in one place so the dropdown values stay readable.
        $statusOptions = [
            'all' => 'All statuses',
            PendingAction::STATUS_PENDING => 'Pending',
            PendingAction::STATUS_COMPLETED => 'Completed',
        ];

        $triggerTypeOptions = [
            'all' => 'All triggers',
            PendingAction::TRIGGER_CREATED => 'Function created',
            PendingAction::TRIGGER_UPDATED => 'Function updated',
            PendingAction::TRIGGER_SOFT_DELETED => 'Function archived',
            PendingAction::TRIGGER_EFFECT_VALUES => 'Fill in effect values',
        ];

        $query = PendingAction::query()
            ->with(['cityFunction', 'createdBy'])
            ->latest('created_at');

        // Fall back to the default state when the query string contains an invalid value.
        if (! array_key_exists($statusFilter, $statusOptions)) {
            $statusFilter = PendingAction::STATUS_PENDING;
        }

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        if (! array_key_exists($triggerTypeFilter, $triggerTypeOptions)) {
            $triggerTypeFilter = 'all';
        }

        if ($triggerTypeFilter !== 'all') {
            $query->where('trigger_type', $triggerTypeFilter);
        }

        $pendingActions = $query->get();

        // Enrich each pending action with missing effect columns so the view can display what still needs to be done.
        $pendingActionsWithMissing = $pendingActions->map(function ($pendingAction) {
            $pendingAction->missing_effect_columns = $pendingAction->cityFunction
                ? $this->effectValueService->missingEffectColumns($pendingAction->cityFunction)
                : [];
            return $pendingAction;
        });

        return view('effects.pending-actions', [
            'pendingActions' => $pendingActionsWithMissing,
            'statusFilter' => $statusFilter,
            'triggerTypeFilter' => $triggerTypeFilter,
            'statusOptions' => $statusOptions,
            'triggerTypeOptions' => $triggerTypeOptions,
            'pendingCount' => PendingAction::query()->where('status', PendingAction::STATUS_PENDING)->count(),
            'completedCount' => PendingAction::query()->where('status', PendingAction::STATUS_COMPLETED)->count(),
        ]);
    }
}