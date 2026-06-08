<?php

namespace App\Http\Controllers;

use App\Models\ActionHistory;
use App\Models\CityEvent;
use App\Models\CityFunction;
use Illuminate\Http\Request;

class CityEventController extends Controller
{
    public function index()
    {
        $this->processEvents();

        $events = CityEvent::with('cityFunctions')->orderByDesc('created_at')->get();
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
        $original = $this->snapshot($event);

        $event->update($this->payload($validated));
        $this->syncFunctions($event, $request->input('functions', []));

        $this->recordAuditLog('update', $event, $original, $this->snapshot($event));

        return redirect()->route('city_events.index')->with('success', 'City event updated.');
    }

    public function destroy($id)
    {
        $event = CityEvent::findOrFail($id);
        $original = $this->snapshot($event);

        $event->delete();

        $this->recordAuditLog('delete', $event, $original, null);

        return redirect()->route('city_events.index')->with('success', 'City event deleted.');
    }

    public function activate($id)
    {
        $event = CityEvent::findOrFail($id);

        $event->update([
            'is_active'    => true,
            'activated_at' => now(),
            'expires_at'   => null,
        ]);

        $this->recordAuditLog('activate', $event, null, ['is_active' => true, 'expires_at' => null]);

        return redirect()->route('city_events.index')->with('success', "{$event->name} is now active.");
    }

    public function deactivate($id)
    {
        $event = CityEvent::findOrFail($id);

        $event->update(['is_active' => false]);

        $this->recordAuditLog('deactivate', $event, null, ['is_active' => false]);

        return redirect()->route('city_events.index')->with('success', "{$event->name} has been deactivated.");
    }

    public function activeEvents()
    {
        $this->processEvents();

        $events = CityEvent::where(function ($q) {
                $q->where('is_active', true)
                  ->orWhere('event_type', 'recurring');
            })
            ->get([
                'id', 'name', 'event_type', 'is_active', 'expires_at', 'activated_at',
                'recurring_frequency_value', 'recurring_frequency_unit',
                'recurring_active_duration_value', 'recurring_active_duration_unit',
                'one_off_duration_value', 'one_off_duration_unit',
            ])
            ->map(function ($event) {
                return [
                    'id'                      => $event->id,
                    'name'                    => $event->name,
                    'event_type'              => $event->event_type,
                    'is_active'               => $event->is_active,
                    'activated_at_timestamp'  => $event->activated_at ? $event->activated_at->timestamp : null,
                    'active_duration_seconds' => $event->event_type === 'recurring'
                        ? $event->activeDurationSeconds()
                        : $event->oneOffDurationSeconds(),
                    'cycle_duration_seconds'  => $event->event_type === 'recurring'
                        ? $event->cycleDurationSeconds()
                        : null,
                ];
            });

        return response()->json($events);
    }

    private function processEvents(): void
    {
        $now = now();

        CityEvent::where('is_active', true)
            ->where('event_type', 'one-off')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->update(['is_active' => false]);

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

    private function rules(): array
    {
        return [
            'name'                          => ['required', 'string', 'max:255'],
            'description'                   => ['nullable', 'string'],
            'event_type'                    => ['required', 'in:one-off,recurring'],
            'recurring_frequency_value'          => ['nullable', 'integer', 'min:1', 'required_if:event_type,recurring'],
            'recurring_frequency_unit'           => ['nullable', 'in:hour,day,week,month', 'required_if:event_type,recurring'],
            'recurring_active_duration_value'    => ['nullable', 'integer', 'min:1', 'required_if:event_type,recurring'],
            'recurring_active_duration_unit'     => ['nullable', 'in:minute,hour,day,week', 'required_if:event_type,recurring'],
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
            'recurring_active_duration_value'   => $isRecurring ? $validated['recurring_active_duration_value'] : null,
            'recurring_active_duration_unit'    => $isRecurring ? $validated['recurring_active_duration_unit'] : null,
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
