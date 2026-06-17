<div class="grid gap-4">
    <div>
        <label for="{{ $model }}-name" class="mb-1 block text-sm font-medium text-slate-700 hc:text-white dark:text-gray-300">
            Name <span class="text-red-400">*</span>
        </label>
        <input id="{{ $model }}-name" type="text" name="name" required
               aria-label="Name for the event"
               @if($model === 'editing') x-ref="editName" @endif
               x-model="{{ $model }}.name"
               class="w-full rounded-xl border border-slate-300 hc:border-white bg-white hc:bg-black px-4 py-3 text-sm text-slate-900 hc:text-white shadow-sm hc:shadow-none focus:border-cyan-500 hc:focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 hc:focus:ring-yellow-400 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <label for="{{ $model }}-description" class="mb-1 block text-sm font-medium text-slate-700 hc:text-white dark:text-gray-300">
            Description
        </label>
        <textarea id="{{ $model }}-description" name="description" rows="4"
                  aria-label="Description for the event"
                  x-model="{{ $model }}.description"
                  class="w-full rounded-xl border border-slate-300 hc:border-white bg-white hc:bg-black px-4 py-3 text-sm text-slate-900 hc:text-white shadow-sm hc:shadow-none focus:border-cyan-500 hc:focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 hc:focus:ring-yellow-400 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"></textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div>
        <label for="{{ $model }}-event-type" class="mb-1 block text-sm font-medium text-slate-700 hc:text-white dark:text-gray-300">
            Event type <span class="text-red-400">*</span>
        </label>
        <select id="{{ $model }}-event-type" name="event_type"
                aria-label="Put in the event type"
                x-model="{{ $model }}.event_type"
                class="w-full rounded-xl border border-slate-300 hc:border-white bg-white hc:bg-black px-4 py-3 text-sm text-slate-900 hc:text-white shadow-sm hc:shadow-none focus:border-cyan-500 hc:focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 hc:focus:ring-yellow-400 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
            <option value="one-off">One-off</option>
            <option value="recurring">Recurring</option>
        </select>
        <x-input-error :messages="$errors->get('event_type')" class="mt-2" />
    </div>

    {{-- RECURRING SCHEDULE --}}
    <div x-show="{{ $model }}.event_type === 'recurring'" class="recurring-schedule-card rounded-2xl border border-cyan-200 hc:border-white bg-cyan-50/70 hc:bg-neutral-900 p-4 dark:border-cyan-500/20 dark:bg-cyan-500/10">
        <p class="mb-3 text-sm font-semibold text-cyan-900 hc:text-white dark:text-cyan-100">Recurring schedule</p>
        <p class="mb-3 text-xs text-slate-500 hc:text-white dark:text-gray-400">
            Set how long the event is <strong>active per cycle</strong>, and how often each cycle repeats.
            Example: active for 2 hours, 4 times per day &rarr; on for 2h, off for 4h, on for 2h, off for 4h…
        </p>

        {{-- Active duration --}}
        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-cyan-800 hc:text-white dark:text-cyan-200">Active window per cycle</p>
        <div class="mb-4 grid gap-4 sm:grid-cols-2">
            <div>
                <label for="{{ $model }}-recurring-active-duration-value" class="mb-1 block text-sm font-medium text-slate-700 hc:text-white dark:text-gray-300">
                    Active for <span class="text-red-400">*</span>
                </label>
                <input id="{{ $model }}-recurring-active-duration-value" type="number" name="recurring_active_duration_value" min="1"
                       aria-label="How long the event is active per cycle"
                       x-model="{{ $model }}.recurring_active_duration_value"
                       :disabled="{{ $model }}.event_type !== 'recurring'"
                       class="w-full rounded-xl border border-slate-300 hc:border-white bg-white hc:bg-black px-4 py-3 text-sm text-slate-900 hc:text-white shadow-sm hc:shadow-none focus:border-cyan-500 hc:focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 hc:focus:ring-yellow-400 disabled:opacity-50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                <x-input-error :messages="$errors->get('recurring_active_duration_value')" class="mt-2" />
            </div>
            <div>
                <label for="{{ $model }}-recurring-active-duration-unit" class="mb-1 block text-sm font-medium text-slate-700 hc:text-white dark:text-gray-300">
                    Unit <span class="text-red-400">*</span>
                </label>
                <select id="{{ $model }}-recurring-active-duration-unit" name="recurring_active_duration_unit"
                        aria-label="Unit for the active window"
                        x-model="{{ $model }}.recurring_active_duration_unit"
                        :disabled="{{ $model }}.event_type !== 'recurring'"
                        class="w-full rounded-xl border border-slate-300 hc:border-white bg-white hc:bg-black px-4 py-3 text-sm text-slate-900 hc:text-white shadow-sm hc:shadow-none focus:border-cyan-500 hc:focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 hc:focus:ring-yellow-400 disabled:opacity-50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                    <option value="minute">Minute</option>
                    <option value="hour">Hour</option>
                    <option value="day">Day</option>
                    <option value="week">Week</option>
                </select>
                <x-input-error :messages="$errors->get('recurring_active_duration_unit')" class="mt-2" />
            </div>
        </div>

        {{-- Cycle frequency --}}
        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-cyan-800 hc:text-white dark:text-cyan-200">Cycle frequency</p>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="{{ $model }}-recurring-frequency-value" class="mb-1 block text-sm font-medium text-slate-700 hc:text-white dark:text-gray-300">
                    Times per unit <span class="text-red-400">*</span>
                </label>
                <input id="{{ $model }}-recurring-frequency-value" type="number" name="recurring_frequency_value" min="1"
                       aria-label="How many times per unit the event repeats"
                       x-model="{{ $model }}.recurring_frequency_value"
                       :disabled="{{ $model }}.event_type !== 'recurring'"
                       class="w-full rounded-xl border border-slate-300 hc:border-white bg-white hc:bg-black px-4 py-3 text-sm text-slate-900 hc:text-white shadow-sm hc:shadow-none focus:border-cyan-500 hc:focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 hc:focus:ring-yellow-400 disabled:opacity-50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                <p class="mt-2 text-xs text-slate-500 hc:text-white dark:text-gray-400">Example: 4 times per day = one cycle every 6 hours.</p>
                <x-input-error :messages="$errors->get('recurring_frequency_value')" class="mt-2" />
            </div>
            <div>
                <label for="{{ $model }}-recurring-frequency-unit" class="mb-1 block text-sm font-medium text-slate-700 hc:text-white dark:text-gray-300">
                    Frequency unit <span class="text-red-400">*</span>
                </label>
                <select id="{{ $model }}-recurring-frequency-unit" name="recurring_frequency_unit"
                        aria-label="Select the unit the cycle repeats in"
                        x-model="{{ $model }}.recurring_frequency_unit"
                        :disabled="{{ $model }}.event_type !== 'recurring'"
                        class="w-full rounded-xl border border-slate-300 hc:border-white bg-white hc:bg-black px-4 py-3 text-sm text-slate-900 hc:text-white shadow-sm hc:shadow-none focus:border-cyan-500 hc:focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 hc:focus:ring-yellow-400 disabled:opacity-50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                    <option value="hour">Hour</option>
                    <option value="day">Day</option>
                    <option value="week">Week</option>
                    <option value="month">Month</option>
                </select>
                <x-input-error :messages="$errors->get('recurring_frequency_unit')" class="mt-2" />
            </div>
        </div>

        {{-- Step 2: Time slots table — appears once frequency > 0 --}}
        <div class="mt-4 border-t border-cyan-200 pt-4 dark:border-cyan-500/20"
             x-show="{{ $model }}.event_type === 'recurring' && {{ $model }}.recurring_frequency_value > 0">

            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-cyan-800 dark:text-cyan-200">Step 2 — Fill in time slots</p>
            <p class="mb-3 text-xs text-slate-500 dark:text-gray-400">
                Set the start and end time for each occurrence. When frequency is per week or per month, also pick the day or date for each slot.
            </p>

            <div class="overflow-x-auto rounded-xl border border-cyan-200 dark:border-cyan-500/20">
                <table class="w-full text-sm">
                    <thead class="bg-cyan-100 dark:bg-cyan-900/30">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-cyan-800 dark:text-cyan-200 w-16">Slot</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-cyan-800 dark:text-cyan-200"
                                x-show="{{ $model }}.recurring_frequency_unit === 'week'">Day</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-cyan-800 dark:text-cyan-200"
                                x-show="{{ $model }}.recurring_frequency_unit === 'month'">Date</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-cyan-800 dark:text-cyan-200">Start time</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-cyan-800 dark:text-cyan-200">End time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-cyan-100 dark:divide-cyan-500/10 bg-white dark:bg-gray-900">
                        <template x-for="(slot, i) in {{ $model }}.recurring_time_slots" :key="i">
                            <tr>
                                <td class="px-4 py-2 text-xs font-semibold text-slate-500 dark:text-gray-400" x-text="'#' + (i + 1)"></td>
                                <td class="px-4 py-2" x-show="{{ $model }}.recurring_frequency_unit === 'week'">
                                    <select :name="`recurring_time_slots[${i}][week_day]`"
                                            x-model.number="slot.week_day"
                                            :aria-label="`Slot ${i + 1} day of week`"
                                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                        <option value="">— day —</option>
                                        <option value="1">Monday</option>
                                        <option value="2">Tuesday</option>
                                        <option value="3">Wednesday</option>
                                        <option value="4">Thursday</option>
                                        <option value="5">Friday</option>
                                        <option value="6">Saturday</option>
                                        <option value="7">Sunday</option>
                                    </select>
                                </td>
                                <td class="px-4 py-2" x-show="{{ $model }}.recurring_frequency_unit === 'month'">
                                    <input type="number"
                                           :name="`recurring_time_slots[${i}][month_date]`"
                                           x-model.number="slot.month_date"
                                           min="1" max="31" placeholder="1–31"
                                           :aria-label="`Slot ${i + 1} date of month`"
                                           class="w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                </td>
                                <td class="px-4 py-2">
                                    <input type="time"
                                           :name="`recurring_time_slots[${i}][start]`"
                                           x-model="slot.start"
                                           :aria-label="`Slot ${i + 1} start time`"
                                           class="w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                </td>
                                <td class="px-4 py-2">
                                    <input type="time"
                                           :name="`recurring_time_slots[${i}][end]`"
                                           x-model="slot.end"
                                           :aria-label="`Slot ${i + 1} end time`"
                                           class="w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <x-input-error :messages="$errors->get('recurring_time_slots')" class="mt-2" />
            <x-input-error :messages="\Illuminate\Support\Arr::flatten($errors->get('recurring_time_slots.*.start'))" class="mt-1" />
            <x-input-error :messages="\Illuminate\Support\Arr::flatten($errors->get('recurring_time_slots.*.end'))" class="mt-1" />
        </div>
    </div>

    {{-- ONE-OFF DURATION --}}
    <div x-show="{{ $model }}.event_type === 'one-off'" class="rounded-2xl border border-amber-200 hc:border-yellow-300 bg-amber-50/80 hc:bg-neutral-900 p-4 dark:border-amber-500/20 dark:bg-amber-500/10">
        <p class="mb-3 text-sm font-semibold text-amber-900 hc:text-yellow-300 dark:text-amber-100">One-off duration</p>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="{{ $model }}-one-off-duration-value" class="mb-1 block text-sm font-medium text-slate-700 hc:text-white dark:text-gray-300">
                    Duration <span class="text-red-400">*</span>
                </label>
                <input id="{{ $model }}-one-off-duration-value" type="number" name="one_off_duration_value" min="1"
                       aria-label="Duration of the event"
                       x-model="{{ $model }}.one_off_duration_value"
                       :disabled="{{ $model }}.event_type !== 'one-off'"
                       class="w-full rounded-xl border border-slate-300 hc:border-white bg-white hc:bg-black px-4 py-3 text-sm text-slate-900 hc:text-white shadow-sm hc:shadow-none focus:border-cyan-500 hc:focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 hc:focus:ring-yellow-400 disabled:opacity-50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                <x-input-error :messages="$errors->get('one_off_duration_value')" class="mt-2" />
            </div>
            <div>
                <label for="{{ $model }}-one-off-duration-unit" class="mb-1 block text-sm font-medium text-slate-700 hc:text-white dark:text-gray-300">
                    Duration unit <span class="text-red-400">*</span>
                </label>
                <select id="{{ $model }}-one-off-duration-unit" name="one_off_duration_unit"
                        aria-label="Select the unit that the event occurs in"
                        x-model="{{ $model }}.one_off_duration_unit"
                        :disabled="{{ $model }}.event_type !== 'one-off'"
                        class="w-full rounded-xl border border-slate-300 hc:border-white bg-white hc:bg-black px-4 py-3 text-sm text-slate-900 hc:text-white shadow-sm hc:shadow-none focus:border-cyan-500 hc:focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 hc:focus:ring-yellow-400 disabled:opacity-50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                    <option value="hour">Hour</option>
                    <option value="day">Day</option>
                    <option value="week">Week</option>
                </select>
                <x-input-error :messages="$errors->get('one_off_duration_unit')" class="mt-2" />
            </div>
        </div>
    </div>
</div>
