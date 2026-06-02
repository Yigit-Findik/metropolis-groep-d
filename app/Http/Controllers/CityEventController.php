<?php

namespace App\Http\Controllers;

use App\Models\ActionHistory;
use App\Models\CityEvent;
use Illuminate\Http\Request;

class CityEventController extends Controller
{
    public function index()
    {
        $events = CityEvent::orderByDesc('created_at')->get();

        return view('city_events', compact('events'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $event = CityEvent::create($this->payload($validated));

        $this->recordAuditLog('create', $event, null, $this->snapshot($event));

        return redirect()->route('city_events.index')->with('success', "{$event->name} was successfully created.");
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate($this->rules());

        $event = CityEvent::findOrFail($id);
        $original = $this->snapshot($event);

        $event->update($this->payload($validated));

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

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'event_type' => ['required', 'in:one-off,recurring'],
            'recurring_frequency_value' => ['nullable', 'integer', 'min:1', 'required_if:event_type,recurring'],
            'recurring_frequency_unit' => ['nullable', 'in:hour,day,week,month', 'required_if:event_type,recurring'],
            'one_off_duration_value' => ['nullable', 'integer', 'min:1', 'required_if:event_type,one-off'],
            'one_off_duration_unit' => ['nullable', 'in:hour,day,week', 'required_if:event_type,one-off'],
        ];
    }

    private function payload(array $validated): array
    {
        $isRecurring = $validated['event_type'] === 'recurring';

        return [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'event_type' => $validated['event_type'],
            'recurring_frequency_value' => $isRecurring ? $validated['recurring_frequency_value'] : null,
            'recurring_frequency_unit' => $isRecurring ? $validated['recurring_frequency_unit'] : null,
            'one_off_duration_value' => $isRecurring ? null : $validated['one_off_duration_value'],
            'one_off_duration_unit' => $isRecurring ? null : $validated['one_off_duration_unit'],
        ];
    }

    private function snapshot(CityEvent $event): array
    {
        return [
            'name' => $event->name,
            'description' => $event->description,
            'event_type' => $event->event_type,
            'recurring_frequency_value' => $event->recurring_frequency_value,
            'recurring_frequency_unit' => $event->recurring_frequency_unit,
            'one_off_duration_value' => $event->one_off_duration_value,
            'one_off_duration_unit' => $event->one_off_duration_unit,
        ];
    }

    private function recordAuditLog(string $action, CityEvent $event, ?array $original, ?array $new): void
    {
        $details = [
            'entity_type' => 'city_event',
            'name' => $event->name,
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
            'user_id' => auth()->id(),
            'action' => $action,
            'cell_id' => null,
            'old_city_function_id' => null,
            'new_city_function_id' => null,
            'details' => $details,
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