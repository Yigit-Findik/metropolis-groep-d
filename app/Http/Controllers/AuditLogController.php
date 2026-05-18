<?php

namespace App\Http\Controllers;

use App\Models\ActionHistory;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        // Only administrators may view the audit log
        if (auth()->user()?->role?->name !== 'Administrator') {
            abort(403);
        }

        $query = ActionHistory::query()
            ->with(['user', 'oldCityFunction', 'newCityFunction'])
            ->whereNotIn('action', ['assign', 'remove', 'undo'])
            ->orderByDesc('created_at');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $entries = $query->paginate(50)->withQueryString();

        return view('audit_log', [
            'entries' => $entries,
        ]);
    }
}
