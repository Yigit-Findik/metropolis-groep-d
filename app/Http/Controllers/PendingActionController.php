<?php

namespace App\Http\Controllers;

use App\Models\PendingAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PendingActionController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', PendingAction::class);

        $statusFilter = $request->string('status')->toString();
        $triggerTypeFilter = $request->string('trigger_type')->toString();

        $statusOptions = [
            'all' => 'Alle statussen',
            PendingAction::STATUS_PENDING => 'Open',
            PendingAction::STATUS_COMPLETED => 'Afgerond',
        ];

        $triggerTypeOptions = [
            'all' => 'Alle triggers',
            PendingAction::TRIGGER_CREATED => 'Functie toegevoegd',
            PendingAction::TRIGGER_UPDATED => 'Functie gewijzigd',
            PendingAction::TRIGGER_SOFT_DELETED => 'Functie gearchiveerd',
            PendingAction::TRIGGER_EFFECT_VALUES => 'Effectwaarden invullen',
        ];

        $query = PendingAction::query()
            ->with(['cityFunction', 'createdBy'])
            ->latest('created_at');

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

        return view('effects.pending-actions', [
            'pendingActions' => $pendingActions,
            'statusFilter' => $statusFilter,
            'triggerTypeFilter' => $triggerTypeFilter,
            'statusOptions' => $statusOptions,
            'triggerTypeOptions' => $triggerTypeOptions,
            'pendingCount' => PendingAction::query()->where('status', PendingAction::STATUS_PENDING)->count(),
            'completedCount' => PendingAction::query()->where('status', PendingAction::STATUS_COMPLETED)->count(),
        ]);
    }
}