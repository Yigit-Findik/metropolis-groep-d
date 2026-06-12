<?php

namespace App\Http\Controllers;

use App\Models\ActionHistory;
use App\Models\CityEvent;
use App\Models\CityFunction;
use App\Models\CityGridCell;
use App\Models\EventRoute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CityEventController extends Controller
{
    public function index()
    {
        $this->processEvents();

        $events = CityEvent::with(['cityFunctions', 'dayFunctions', 'nightFunctions'])
            ->orderByDesc('created_at')
            ->get();

        $cityFunctions = CityFunction::orderBy('name')->get(['id', 'name', 'category']);

        return view('city_events', compact('events', 'cityFunctions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $event = CityEvent::create($this->payload($validated));
        $this->syncFunctions($event, $request->input('functions', []));

        $this->recordAuditLog('create', $event, null, $this->snapshot($event));

        return redirect()->route('city_events.index')->with('success', "{$event->name} was successfully created.");
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate($this->rules());

        $event = CityEvent::findOrFail($id);

        if ($event->is_day_night_cycle) {
            return redirect()->route('city_events.index')->with('error', 'Use the Day/Night Cycle editor to update this event.');
        }

        $original = $this->snapshot($event);

        $event->update($this->payload($validated));
        $this->syncFunctions($event, $request->input('functions', []));

        $this->recordAuditLog('update', $event, $original, $this->snapshot($event));

        return redirect()->route('city_events.index')->with('success', 'City event updated.');
    }

    public function updateDayNight(Request $request, $id)
    {
        $event = CityEvent::findOrFail($id);

        if (! $event->is_day_night_cycle) {
            abort(404);
        }

        $validated = $request->validate([
            'day_duration_value'   => ['required', 'integer', 'min:1'],
            'day_duration_unit'    => ['required', 'in:minute,hour,day'],
            'night_duration_value' => ['required', 'integer', 'min:1'],
            'night_duration_unit'  => ['required', 'in:minute,hour,day'],
            'day_functions'        => ['nullable', 'array'],
            'day_functions.*.safety_modifier'              => ['nullable', 'integer', 'between:-10,10'],
            'day_functions.*.recreation_modifier'          => ['nullable', 'integer', 'between:-10,10'],
            'day_functions.*.environment_quality_modifier' => ['nullable', 'integer', 'between:-10,10'],
            'day_functions.*.facilities_modifier'          => ['nullable', 'integer', 'between:-10,10'],
            'day_functions.*.mobility_modifier'            => ['nullable', 'integer', 'between:-10,10'],
            'night_functions'      => ['nullable', 'array'],
            'night_functions.*.safety_modifier'              => ['nullable', 'integer', 'between:-10,10'],
            'night_functions.*.recreation_modifier'          => ['nullable', 'integer', 'between:-10,10'],
            'night_functions.*.environment_quality_modifier' => ['nullable', 'integer', 'between:-10,10'],
            'night_functions.*.facilities_modifier'          => ['nullable', 'integer', 'between:-10,10'],
            'night_functions.*.mobility_modifier'            => ['nullable', 'integer', 'between:-10,10'],
        ]);

        $event->update([
            'day_duration_value'   => $validated['day_duration_value'],
            'day_duration_unit'    => $validated['day_duration_unit'],
            'night_duration_value' => $validated['night_duration_value'],
            'night_duration_unit'  => $validated['night_duration_unit'],
        ]);

        $this->syncDayNightFunctions(
            $event,
            $request->input('day_functions', []),
            $request->input('night_functions', [])
        );

        $this->recordAuditLog('update', $event, null, [
            'day_duration'   => $validated['day_duration_value'] . ' ' . $validated['day_duration_unit'],
            'night_duration' => $validated['night_duration_value'] . ' ' . $validated['night_duration_unit'],
        ]);

        return redirect()->route('city_events.index')->with('success', 'Day/Night Cycle updated.');
    }

    public function destroy($id)
    {
        $event = CityEvent::findOrFail($id);

        if ($event->is_day_night_cycle) {
            return redirect()->route('city_events.index')->with('error', 'The Day/Night Cycle event cannot be deleted.');
        }

        $original = $this->snapshot($event);

        $event->delete();

        $this->recordAuditLog('delete', $event, $original, null);

        return redirect()->route('city_events.index')->with('success', 'City event deleted.');
    }

    public function activate($id)
    {
        $event = CityEvent::findOrFail($id);

        $update = [
            'is_active'    => true,
            'activated_at' => now(),
            'expires_at'   => null,
        ];

        if ($event->is_day_night_cycle) {
            $update['current_phase']    = 'day';
            $update['phase_started_at'] = now();
        }

        $event->update($update);

        $this->recordAuditLog('activate', $event, null, ['is_active' => true]);

        return redirect()->route('city_events.index')->with('success', "{$event->name} is now active.");
    }

    public function deactivate($id)
    {
        $event = CityEvent::findOrFail($id);

        $update = ['is_active' => false];

        if ($event->is_day_night_cycle) {
            $update['current_phase']    = null;
            $update['phase_started_at'] = null;
        }

        $event->update($update);

        $functionIds = $event->cityFunctions()->pluck('city_functions.id');
        $cellIds = CityGridCell::whereIn('function_id', $functionIds)->pluck('id');
        EventRoute::whereIn('event_cell_id', $cellIds)->delete();

        $this->recordAuditLog('deactivate', $event, null, ['is_active' => false]);

        return redirect()->route('city_events.index')->with('success', "{$event->name} has been deactivated.");
    }

    public function switchPhase(Request $request, $id)
    {
        $event = CityEvent::findOrFail($id);

        if (! $event->is_day_night_cycle || ! $event->is_active) {
            return response()->json(['error' => 'Invalid event'], 400);
        }

        // If the caller specifies which phase they expect to switch FROM, skip if it
        // already changed (prevents double-switches when both pages are open).
        $fromPhase = $request->input('from_phase');
        if ($fromPhase && $event->current_phase !== $fromPhase) {
            return response()->json(['phase' => $event->current_phase, 'skipped' => true]);
        }

        $newPhase = $event->current_phase === 'day' ? 'night' : 'day';

        $event->update([
            'current_phase'    => $newPhase,
            'phase_started_at' => now(),
        ]);

        return response()->json(['phase' => $newPhase]);
    }

    public function activeEvents()
    {
        $this->processEvents();

        $events = CityEvent::where(function ($q) {
                $q->where('is_active', true)
                  ->orWhere('event_type', 'recurring');
            })
            ->with(['cityFunctions', 'dayFunctions', 'nightFunctions'])
            ->get([
                'id', 'name', 'event_type', 'is_active', 'is_day_night_cycle',
                'expires_at', 'activated_at',
                'recurring_frequency_value', 'recurring_frequency_unit',
                'recurring_active_duration_value', 'recurring_active_duration_unit',
                'recurring_time_slots',
                'one_off_duration_value', 'one_off_duration_unit',
                'current_phase', 'phase_started_at',
                'day_duration_value', 'day_duration_unit',
                'night_duration_value', 'night_duration_unit',
            ])
            ->map(function ($event) {
                if ($event->is_day_night_cycle) {
                    $phaseFns = $event->current_phase === 'day'
                        ? $event->dayFunctions
                        : $event->nightFunctions;
                    $linked = $event->is_active
                        ? $phaseFns->map(fn ($fn) => [
                            'function_id'                  => $fn->id,
                            'safety_modifier'              => (int) ($fn->pivot->safety_modifier ?? 0),
                            'recreation_modifier'          => (int) ($fn->pivot->recreation_modifier ?? 0),
                            'environment_quality_modifier' => (int) ($fn->pivot->environment_quality_modifier ?? 0),
                            'facilities_modifier'          => (int) ($fn->pivot->facilities_modifier ?? 0),
                            'mobility_modifier'            => (int) ($fn->pivot->mobility_modifier ?? 0),
                        ])->values()->all()
                        : [];

                    return [
                        'id'                         => $event->id,
                        'name'                       => $event->name,
                        'event_type'                 => $event->event_type,
                        'is_active'                  => $event->is_active,
                        'is_day_night_cycle'         => true,
                        'activated_at_timestamp'     => $event->activated_at ? $event->activated_at->timestamp : null,
                        'current_phase'              => $event->current_phase,
                        'phase_started_at_timestamp' => $event->phase_started_at ? $event->phase_started_at->timestamp : null,
                        'day_duration_seconds'       => $event->dayDurationSeconds(),
                        'night_duration_seconds'     => $event->nightDurationSeconds(),
                        'active_duration_seconds'    => null,
                        'cycle_duration_seconds'     => null,
                        'linked_functions'           => $linked,
                    ];
                }

                $linked = $event->is_active
                    ? $event->cityFunctions->map(fn ($fn) => [
                        'function_id'                  => $fn->id,
                        'safety_modifier'              => (int) ($fn->pivot->safety_modifier ?? 0),
                        'recreation_modifier'          => (int) ($fn->pivot->recreation_modifier ?? 0),
                        'environment_quality_modifier' => (int) ($fn->pivot->environment_quality_modifier ?? 0),
                        'facilities_modifier'          => (int) ($fn->pivot->facilities_modifier ?? 0),
                        'mobility_modifier'            => (int) ($fn->pivot->mobility_modifier ?? 0),
                    ])->values()->all()
                    : [];

                return [
                    'id'                      => $event->id,
                    'name'                    => $event->name,
                    'event_type'              => $event->event_type,
                    'is_active'               => $event->is_active,
                    'is_day_night_cycle'      => false,
                    'activated_at_timestamp'  => $event->activated_at ? $event->activated_at->timestamp : null,
                    'active_duration_seconds' => $event->event_type === 'recurring'
                        ? $event->activeDurationSeconds()
                        : $event->oneOffDurationSeconds(),
                    'cycle_duration_seconds'  => $event->event_type === 'recurring'
                        ? $event->cycleDurationSeconds()
                        : null,
                    'time_slots'              => $event->timeSlotsForJs(),
                    'recurring_frequency_unit'   => $event->recurring_frequency_unit,
                    'current_phase'              => null,
                    'phase_started_at_timestamp' => null,
                    'day_duration_seconds'       => null,
                    'night_duration_seconds'     => null,
                    'linked_functions'           => $linked,
                ];
            });

        return response()->json($events);
    }

    private function processEvents(): void
    {
        $now = now();

        // Deactivate expired one-off events (excludes day/night cycle via event_type check)
        CityEvent::where('is_active', true)
            ->where('event_type', 'one-off')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->update(['is_active' => false]);

        // Deactivate expired recurring events
        CityEvent::where('is_active', true)
            ->where('event_type', 'recurring')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->update(['is_active' => false]);

        // Only auto-reactivate events that have a real expires_at (not simulation-managed ones)
        CityEvent::where('is_active', false)
            ->where('event_type', 'recurring')
            ->whereNotNull('activated_at')
            ->whereNotNull('expires_at')
            ->get()
            ->filter(fn ($event) => $now->gte(
                $event->activated_at->addSeconds($event->cycleDurationSeconds())
            ))
            ->each(function ($event) use ($now) {
                $event->update([
                    'is_active'    => true,
                    'activated_at' => $now,
                    'expires_at'   => null,
                ]);
            });
    }

    private function syncFunctions(CityEvent $event, array $functions): void
    {
        $pivotData = [];

        foreach ($functions as $functionId => $modifiers) {
            $pivotData[(int) $functionId] = [
                'safety_modifier'               => (int) ($modifiers['safety_modifier'] ?? 0),
                'recreation_modifier'           => (int) ($modifiers['recreation_modifier'] ?? 0),
                'environment_quality_modifier'  => (int) ($modifiers['environment_quality_modifier'] ?? 0),
                'facilities_modifier'           => (int) ($modifiers['facilities_modifier'] ?? 0),
                'mobility_modifier'             => (int) ($modifiers['mobility_modifier'] ?? 0),
            ];
        }

        $event->cityFunctions()->sync($pivotData);
    }

    private function syncDayNightFunctions(CityEvent $event, array $dayFunctions, array $nightFunctions): void
    {
        DB::table('city_event_day_night_functions')
            ->where('city_event_id', $event->id)
            ->delete();

        $rows = [];

        foreach ($dayFunctions as $fnId => $modifiers) {
            $rows[] = [
                'city_event_id'                 => $event->id,
                'city_function_id'              => (int) $fnId,
                'phase'                         => 'day',
                'safety_modifier'               => (int) ($modifiers['safety_modifier'] ?? 0),
                'recreation_modifier'           => (int) ($modifiers['recreation_modifier'] ?? 0),
                'environment_quality_modifier'  => (int) ($modifiers['environment_quality_modifier'] ?? 0),
                'facilities_modifier'           => (int) ($modifiers['facilities_modifier'] ?? 0),
                'mobility_modifier'             => (int) ($modifiers['mobility_modifier'] ?? 0),
                'created_at'                    => now(),
                'updated_at'                    => now(),
            ];
        }

        foreach ($nightFunctions as $fnId => $modifiers) {
            $rows[] = [
                'city_event_id'                 => $event->id,
                'city_function_id'              => (int) $fnId,
                'phase'                         => 'night',
                'safety_modifier'               => (int) ($modifiers['safety_modifier'] ?? 0),
                'recreation_modifier'           => (int) ($modifiers['recreation_modifier'] ?? 0),
                'environment_quality_modifier'  => (int) ($modifiers['environment_quality_modifier'] ?? 0),
                'facilities_modifier'           => (int) ($modifiers['facilities_modifier'] ?? 0),
                'mobility_modifier'             => (int) ($modifiers['mobility_modifier'] ?? 0),
                'created_at'                    => now(),
                'updated_at'                    => now(),
            ];
        }

        if (! empty($rows)) {
            DB::table('city_event_day_night_functions')->insert($rows);
        }
    }

    private function rules(): array
    {
        return [
            'name'                          => ['required', 'string', 'max:255'],
            'description'                   => ['nullable', 'string'],
            'event_type'                    => ['required', 'in:one-off,recurring'],
            'recurring_frequency_value'          => ['nullable', 'integer', 'min:1', 'required_if:event_type,recurring'],
            'recurring_frequency_unit'           => ['nullable', 'in:hour,day,week,month', 'required_if:event_type,recurring'],
            'recurring_active_duration_value'    => ['nullable', 'integer', 'min:1'],
            'recurring_active_duration_unit'     => ['nullable', 'in:minute,hour,day,week'],
            'recurring_time_slots'                    => ['nullable', 'array'],
            'recurring_time_slots.*.start'            => ['required_with:recurring_time_slots', 'regex:/^\d{2}:\d{2}$/'],
            'recurring_time_slots.*.end'              => ['required_with:recurring_time_slots', 'regex:/^\d{2}:\d{2}$/'],
            'recurring_time_slots.*.week_day'         => ['nullable', 'integer', 'between:1,7'],
            'recurring_time_slots.*.month_date'       => ['nullable', 'integer', 'between:1,31'],
            'one_off_duration_value'             => ['nullable', 'integer', 'min:1', 'required_if:event_type,one-off'],
            'one_off_duration_unit'         => ['nullable', 'in:hour,day,week', 'required_if:event_type,one-off'],
            'functions'                     => ['nullable', 'array'],
            'functions.*.safety_modifier'              => ['nullable', 'integer', 'between:-10,10'],
            'functions.*.recreation_modifier'          => ['nullable', 'integer', 'between:-10,10'],
            'functions.*.environment_quality_modifier' => ['nullable', 'integer', 'between:-10,10'],
            'functions.*.facilities_modifier'          => ['nullable', 'integer', 'between:-10,10'],
            'functions.*.mobility_modifier'            => ['nullable', 'integer', 'between:-10,10'],
        ];
    }

    private function payload(array $validated): array
    {
        $isRecurring = $validated['event_type'] === 'recurring';

        return [
            'name'                              => $validated['name'],
            'description'                       => $validated['description'] ?? null,
            'event_type'                        => $validated['event_type'],
            'recurring_frequency_value'         => $isRecurring ? $validated['recurring_frequency_value'] : null,
            'recurring_frequency_unit'          => $isRecurring ? $validated['recurring_frequency_unit'] : null,
            'recurring_active_duration_value'   => $isRecurring ? ($validated['recurring_active_duration_value'] ?? null) : null,
            'recurring_active_duration_unit'    => $isRecurring ? ($validated['recurring_active_duration_unit'] ?? null) : null,
            'recurring_time_slots'              => $isRecurring ? ($validated['recurring_time_slots'] ?? null) : null,
            'one_off_duration_value'            => $isRecurring ? null : $validated['one_off_duration_value'],
            'one_off_duration_unit'             => $isRecurring ? null : $validated['one_off_duration_unit'],
        ];
    }

    private function snapshot(CityEvent $event): array
    {
        return [
            'name'                            => $event->name,
            'description'                     => $event->description,
            'event_type'                      => $event->event_type,
            'recurring_frequency_value'       => $event->recurring_frequency_value,
            'recurring_frequency_unit'        => $event->recurring_frequency_unit,
            'recurring_active_duration_value' => $event->recurring_active_duration_value,
            'recurring_active_duration_unit'  => $event->recurring_active_duration_unit,
            'recurring_time_slots'            => $event->recurring_time_slots,
            'one_off_duration_value'          => $event->one_off_duration_value,
            'one_off_duration_unit'           => $event->one_off_duration_unit,
        ];
    }

    private function recordAuditLog(string $action, CityEvent $event, ?array $original, ?array $new): void
    {
        $details = [
            'entity_type' => 'city_event',
            'name'        => $event->name,
        ];

        if ($original !== null) {
            $details['original'] = $original;
        }

        if ($new !== null) {
            $details['new'] = $new;
        }

        if ($action === 'update' && $original !== null && $new !== null) {
            $details['changed'] = $this->diffSnapshot($original, $new);
        }

        ActionHistory::create([
            'user_id'               => auth()->id(),
            'action'                => $action,
            'cell_id'               => null,
            'old_city_function_id'  => null,
            'new_city_function_id'  => null,
            'details'               => $details,
        ]);
    }

    private function diffSnapshot(array $original, array $new): array
    {
        $changed = [];

        foreach ($new as $key => $value) {
            if (($original[$key] ?? null) !== $value) {
                $changed[$key] = $value;
            }
        }

        return $changed;
    }
}
