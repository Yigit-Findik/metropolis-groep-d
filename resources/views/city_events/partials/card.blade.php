<article tabindex="0"
         x-data="eventCard({{ $event->id }}, {{ $event->is_active ? 'true' : 'false' }}, {{ $event->event_type === 'recurring' ? 'true' : 'false' }}, {{ $event->is_in_simulation ? 'true' : 'false' }})"
         aria-label="Event {{ $event->name }}, {{ $event->type_label }}{{ $event->description ? ', description: ' . $event->description : '' }}, {{ $event->schedule_summary }}"
         class="rounded-2xl border p-5 shadow-sm focus:outline-none focus:ring-2 focus:ring-cyan-500 transition"
         :class="showDeactivate
             ? 'border-emerald-300 bg-emerald-50 dark:border-emerald-700 dark:bg-emerald-900/20'
             : 'border-amber-200 bg-amber-50 dark:border-amber-700/50 dark:bg-amber-900/10'">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <h3 class="truncate text-lg font-semibold text-slate-900 dark:text-gray-100">{{ $event->name }}</h3>

                {{-- Event type badge --}}
                @if($event->is_day_night_cycle)
                    <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide bg-violet-100 text-violet-800 dark:bg-violet-500/10 dark:text-violet-200">
                        Day/Night
                    </span>
                    {{-- Lock badge --}}
                    <span class="rounded-full px-2 py-1 text-xs font-semibold bg-slate-200 text-slate-600 dark:bg-gray-700 dark:text-gray-400"
                          title="This event is permanent and cannot be deleted">
                        Locked
                    </span>
                @else
                    <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide
                                 {{ $event->event_type === 'recurring'
                                     ? 'bg-cyan-100 text-cyan-800 dark:bg-cyan-500/10 dark:text-cyan-200'
                                     : 'bg-amber-100 text-amber-800 dark:bg-amber-500/10 dark:text-amber-200' }}">
                        {{ $event->type_label }}
                    </span>
                @endif

                {{-- Active status badge --}}
                @if($event->is_day_night_cycle)
                    <span x-show="isActive"
                          class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide
                                 {{ $event->current_phase === 'day'
                                     ? 'bg-amber-100 text-amber-800 dark:bg-amber-500/10 dark:text-amber-300'
                                     : 'bg-indigo-100 text-indigo-800 dark:bg-indigo-500/10 dark:text-indigo-300' }}">
                        {{ $event->current_phase ? ucfirst($event->current_phase) : 'Active' }}
                    </span>
                @elseif($event->event_type === 'recurring')
                    <span x-show="isActive"
                          class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide bg-emerald-100 text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-300">
                        Active
                    </span>
                    <span x-show="isInSimulation && !isActive"
                          class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide bg-cyan-100 text-cyan-800 dark:bg-cyan-500/10 dark:text-cyan-300">
                        In Simulation
                    </span>
                @else
                    <span x-show="isActive"
                          class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide bg-emerald-100 text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-300">
                        Active
                    </span>
                @endif
            </div>

            @if($event->description)
                <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-gray-300">{{ $event->description }}</p>
            @endif

            <p class="mt-3 text-sm font-medium text-slate-700 dark:text-gray-200">{{ $event->schedule_summary }}</p>

            {{-- Live countdown timers --}}
            @if($event->is_day_night_cycle && $event->is_active && $event->current_phase && $event->phase_started_at)
                <p class="mt-1 text-xs"
                   x-data="dayNightCycleTimer({{ $event->dayDurationSeconds() }}, {{ $event->nightDurationSeconds() }}, {{ $event->id }}, @js($event->current_phase), {{ $event->phase_started_at->timestamp }})"
                   :class="colorClass"
                   x-text="label"></p>
            @elseif($event->event_type === 'recurring' && $event->hasTimeSlot())
                <p class="mt-1 text-xs"
                   x-data="timeSlotEventTimer(@js($event->timeSlotsForJs()), {{ $event->id }}, {{ $event->is_active ? 'true' : 'false' }}, @js($event->recurring_frequency_unit))"
                   :class="colorClass"
                   x-text="label"></p>
            @elseif($event->event_type === 'recurring' && $event->activated_at)
                <p class="mt-1 text-xs"
                   x-data="recurringEventTimer({{ $event->activeDurationSeconds() }}, {{ $event->cycleDurationSeconds() }}, {{ $event->id }}, {{ $event->activated_at->timestamp }})"
                   :class="colorClass"
                   x-text="label"></p>
            @elseif($event->event_type === 'one-off' && $event->is_active && $event->activated_at)
                <p class="mt-1 text-xs text-emerald-600 dark:text-emerald-400"
                   x-data="expiryCountdown({{ $event->oneOffDurationSeconds() }}, 'expires', {{ $event->id }}, {{ $event->activated_at->timestamp }})"
                   x-text="label"></p>
            @endif

            {{-- Linked functions --}}
            @if($event->is_day_night_cycle)
                @if($event->dayFunctions->isNotEmpty() || $event->nightFunctions->isNotEmpty())
                    @if($event->dayFunctions->isNotEmpty())
                        <p class="mt-2 text-xs text-slate-500 dark:text-gray-400">
                            Day affects: {{ $event->dayFunctions->pluck('name')->join(', ') }}
                        </p>
                    @endif
                    @if($event->nightFunctions->isNotEmpty())
                        <p class="mt-1 text-xs text-slate-500 dark:text-gray-400">
                            Night affects: {{ $event->nightFunctions->pluck('name')->join(', ') }}
                        </p>
                    @endif
                @endif
            @elseif($event->cityFunctions->isNotEmpty())
                <p class="mt-2 text-xs text-slate-500 dark:text-gray-400">
                    Affects: {{ $event->cityFunctions->pluck('name')->join(', ') }}
                </p>
            @endif
        </div>

        <div class="flex shrink-0 flex-wrap items-center gap-2">

            {{-- Activate / Deactivate --}}
            <form x-show="showDeactivate" method="POST" action="{{ route('city_events.deactivate', $event->id) }}">
                @csrf
                <button type="submit"
                        aria-label="Deactivate event {{ $event->name }}"
                        class="rounded-xl bg-emerald-700 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-600">
                    Deactivate
                </button>
            </form>
            <form x-show="!showDeactivate" method="POST" action="{{ route('city_events.activate', $event->id) }}">
                @csrf
                <button type="submit"
                        aria-label="Activate event {{ $event->name }}"
                        class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-500">
                    Activate
                </button>
            </form>

            @if($event->is_day_night_cycle)
                {{-- Day/Night specific edit button --}}
                <button type="button"
                        aria-label="Edit Day/Night Cycle settings"
                        @click="openDayNightEdit({
                            id: {{ $event->id }},
                            day_duration_value: @js($event->day_duration_value ?? 8),
                            day_duration_unit: @js($event->day_duration_unit ?? 'hour'),
                            night_duration_value: @js($event->night_duration_value ?? 8),
                            night_duration_unit: @js($event->night_duration_unit ?? 'hour'),
                            dayLinkedFunctions: @js($event->dayFunctions->map(fn($f) => [
                                'id'                             => $f->id,
                                'safety_modifier'                => $f->pivot->safety_modifier,
                                'recreation_modifier'            => $f->pivot->recreation_modifier,
                                'environment_quality_modifier'   => $f->pivot->environment_quality_modifier,
                                'facilities_modifier'            => $f->pivot->facilities_modifier,
                                'mobility_modifier'              => $f->pivot->mobility_modifier,
                            ])->values()),
                            nightLinkedFunctions: @js($event->nightFunctions->map(fn($f) => [
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
            @else
                {{-- Regular edit button --}}
                <button type="button"
                        aria-label="Edit event {{ $event->name }}"
                        @click="openEdit({
                            id: {{ $event->id }},
                            name: @js($event->name),
                            description: @js($event->description ?? ''),
                            event_type: @js($event->event_type),
                            recurring_frequency_value: @js($event->recurring_frequency_value),
                            recurring_frequency_unit: @js($event->recurring_frequency_unit),
                            recurring_time_slots: @js($event->recurring_time_slots ?? []),
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

                {{-- Delete (not shown for day/night cycle) --}}
                <form method="POST" action="{{ route('city_events.destroy', $event->id) }}" x-data="deleteForm(@js($event->name))" @submit="confirmAndSubmit($event)">
                    @csrf
                    @method('DELETE')
                    <button type="submit" aria-label="Delete event {{ $event->name }}" class="rounded-xl bg-rose-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-rose-500">
                        Delete
                    </button>
                </form>
            @endif
        </div>
    </div>
</article>
