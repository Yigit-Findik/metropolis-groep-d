<?php

namespace App\Console\Commands;

use App\Models\CityEvent;
use Illuminate\Console\Command;

class ProcessCityEvents extends Command
{
    protected $signature = 'events:process';
    protected $description = 'Drive the event lifecycle: deactivate expired windows, reactivate recurring events whose next cycle has begun';

    public function handle(): void
    {
        $now = now();

        // --- Deactivate expired one-off events ---
        $deactivatedOneOff = CityEvent::where('is_active', true)
            ->where('event_type', 'one-off')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->update(['is_active' => false]);

        // --- Deactivate recurring events whose active window has closed ---
        // expires_at marks the end of the active window (activated_at + active_duration).
        $deactivatedRecurring = CityEvent::where('is_active', true)
            ->where('event_type', 'recurring')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->update(['is_active' => false]);

        // --- Reactivate recurring events whose next cycle start is due ---
        // Next cycle starts at activated_at + cycle_duration.
        // We find inactive recurring events where that moment has passed.
        $toReactivate = CityEvent::where('is_active', false)
            ->where('event_type', 'recurring')
            ->whereNotNull('activated_at')
            ->get()
            ->filter(fn ($event) => $now->gte(
                $event->activated_at->addSeconds($event->cycleDurationSeconds())
            ));

        foreach ($toReactivate as $event) {
            $event->update([
                'is_active'    => true,
                'activated_at' => $now,
                'expires_at'   => $now->copy()->addSeconds($event->activeDurationSeconds()),
            ]);
        }

        $this->info(
            "One-off deactivated: {$deactivatedOneOff}. " .
            "Recurring deactivated: {$deactivatedRecurring}. " .
            "Recurring reactivated: {$toReactivate->count()}."
        );
    }
}
