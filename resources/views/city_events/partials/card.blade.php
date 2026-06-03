<article tabindex="0"
         aria-label="Event {{ $event->name }}, {{ $event->type_label }}{{ $event->description ? ', description: ' . $event->description : '' }}, {{ $event->schedule_summary }}{{ $event->isCurrentlyActive() ? ', currently active' : '' }}"
         class="rounded-2xl border p-5 shadow-sm focus:outline-none focus:ring-2 focus:ring-cyan-500 transition
                {{ $event->isCurrentlyActive()
                    ? 'border-emerald-300 bg-emerald-50 dark:border-emerald-700 dark:bg-emerald-900/20'
                    : 'border-slate-200 bg-slate-50 dark:border-gray-700 dark:bg-gray-900/50' }}">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <h3 class="truncate text-lg font-semibold text-slate-900 dark:text-gray-100">{{ $event->name }}</h3>

                {{-- Event type badge --}}
                <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide
                             {{ $event->event_type === 'recurring'
                                 ? 'bg-cyan-100 text-cyan-800 dark:bg-cyan-500/10 dark:text-cyan-200'
                                 : 'bg-amber-100 text-amber-800 dark:bg-amber-500/10 dark:text-amber-200' }}">
                    {{ $event->type_label }}
                </span>

                {{-- Active status badge --}}
                @if($event->isCurrentlyActive())
                    <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide bg-emerald-100 text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-300">
                        Active
                    </span>
                @endif
            </div>

            @if($event->description)
                <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-gray-300">{{ $event->description }}</p>
            @endif

            <p class="mt-3 text-sm font-medium text-slate-700 dark:text-gray-200">{{ $event->schedule_summary }}</p>

            {{-- Live countdown timers — tick every second client-side. --}}
            @if($event->event_type === 'recurring' && $event->activated_at && $event->expires_at)
                {{-- One component handles both active ("Cycle ends in X") and inactive ("Reactivates in X")
                     so the transition happens in the browser without needing a page reload. --}}
                @php $nextActivationAt = $event->activated_at->copy()->addSeconds($event->cycleDurationSeconds()); @endphp
                <p class="mt-1 text-xs"
                   x-data="recurringEventTimer(@js($event->expires_at->toIso8601String()), @js($nextActivationAt->toIso8601String()))"
                   :class="colorClass"
                   x-text="label"></p>
            @elseif($event->isCurrentlyActive() && $event->expires_at)
                {{-- One-off active: simple expiry countdown. --}}
                <p class="mt-1 text-xs text-emerald-600 dark:text-emerald-400"
                   x-data="expiryCountdown(@js($event->expires_at->toIso8601String()), 'expires')"
                   x-text="label"></p>
            @endif

            {{-- Linked functions --}}
            @if($event->cityFunctions->isNotEmpty())
                <p class="mt-2 text-xs text-slate-500 dark:text-gray-400">
                    Affects: {{ $event->cityFunctions->pluck('name')->join(', ') }}
                </p>
            @endif
        </div>

        <div class="flex shrink-0 flex-wrap items-center gap-2">

            {{-- Activate / Deactivate --}}
            @if($event->isCurrentlyActive())
                <form method="POST" action="{{ route('city_events.deactivate', $event->id) }}">
                    @csrf
                    <button type="submit"
                            aria-label="Deactivate event {{ $event->name }}"
                            class="rounded-xl bg-emerald-700 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-600">
                        Deactivate
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('city_events.activate', $event->id) }}">
                    @csrf
                    <button type="submit"
                            aria-label="Activate event {{ $event->name }}"
                            class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-500">
                        Activate
                    </button>
                </form>
            @endif

            <button type="button"
                    aria-label="Edit event {{ $event->name }}"
                    @click="openEdit({
                        id: {{ $event->id }},
                        name: @js($event->name),
                        description: @js($event->description ?? ''),
                        event_type: @js($event->event_type),
                        recurring_frequency_value: @js($event->recurring_frequency_value),
                        recurring_frequency_unit: @js($event->recurring_frequency_unit),
                        recurring_active_duration_value: @js($event->recurring_active_duration_value),
                        recurring_active_duration_unit: @js($event->recurring_active_duration_unit),
                        one_off_duration_value: @js($event->one_off_duration_value),
                        one_off_duration_unit: @js($event->one_off_duration_unit),
                        linkedFunctions: @js($event->cityFunctions->map(fn($f) => [
                            'id'                             => $f->id,
                            'safety_modifier'                => $f->pivot->safety_modifier,
                            'recreation_modifier'            => $f->pivot->recreation_modifier,
                            'environment_quality_modifier'   => $f->pivot->environment_quality_modifier,
                            'facilities_modifier'            => $f->pivot->facilities_modifier,
                            'mobility_modifier'              => $f->pivot->mobility_modifier,
                        ])->values()),
                    })"
                    class="rounded-xl bg-yellow-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-yellow-500">
                Edit
            </button>

            <form method="POST" action="{{ route('city_events.destroy', $event->id) }}" x-data="deleteForm(@js($event->name))" @submit="confirmAndSubmit($event)">
                @csrf
                @method('DELETE')
                <button type="submit" aria-label="Delete event {{ $event->name }}" class="rounded-xl bg-rose-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-rose-500">
                    Delete
                </button>
            </form>
        </div>
    </div>
</article>
