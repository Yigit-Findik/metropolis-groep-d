<article tabindex="0" aria-label="Event {{ $event->name }}, {{ $event->type_label }}{{ $event->description ? ', description: ' . $event->description : '' }}, {{ $event->schedule_summary }}"
         class="rounded-2xl border border-slate-200 bg-slate-50 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-cyan-500 dark:border-gray-700 dark:bg-gray-900/50">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <h3 class="truncate text-lg font-semibold text-slate-900 dark:text-gray-100">{{ $event->name }}</h3>
                <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide {{ $event->event_type === 'recurring' ? 'bg-cyan-100 text-cyan-800 dark:bg-cyan-500/10 dark:text-cyan-200' : 'bg-amber-100 text-amber-800 dark:bg-amber-500/10 dark:text-amber-200' }}">
                    {{ $event->type_label }}
                </span>
            </div>
            @if($event->description)
                <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-gray-300">{{ $event->description }}</p>
            @endif
            <p class="mt-3 text-sm font-medium text-slate-700 dark:text-gray-200">{{ $event->schedule_summary }}</p>
        </div>

        <div class="flex shrink-0 items-center gap-2">
            <button type="button"
                    aria-label="Edit event {{ $event->name }}"
                    @click="openEdit({
                        id: {{ $event->id }},
                        name: @js($event->name),
                        description: @js($event->description ?? ''),
                        event_type: @js($event->event_type),
                        recurring_frequency_value: @js($event->recurring_frequency_value),
                        recurring_frequency_unit: @js($event->recurring_frequency_unit),
                        one_off_duration_value: @js($event->one_off_duration_value),
                        one_off_duration_unit: @js($event->one_off_duration_unit),
                    })"
                    class="rounded-xl bg-cyan-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-cyan-500">
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