<div class="grid gap-4">
    <div>
        <label for="{{ $model }}-name" class="mb-1 block text-sm font-medium text-slate-700 dark:text-gray-300">
            Name <span class="text-red-400">*</span>
        </label>
        <input id="{{ $model }}-name" type="text" name="name" required
               aria-label="Name for the event"
               @if($model === 'editing') x-ref="editName" @endif
               @if($model === 'creating') x-model="creating.name" @else x-model="editing.name" @endif
               class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <label for="{{ $model }}-description" class="mb-1 block text-sm font-medium text-slate-700 dark:text-gray-300">
            Description
        </label>
        <textarea id="{{ $model }}-description" name="description" rows="4"
                  aria-label="Description for the event"
                  @if($model === 'creating') x-model="creating.description" @else x-model="editing.description" @endif
                  class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"></textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div>
        <label for="{{ $model }}-event-type" class="mb-1 block text-sm font-medium text-slate-700 dark:text-gray-300">
            Event type <span class="text-red-400">*</span>
        </label>
        <select id="{{ $model }}-event-type" name="event_type"
                aria-label="Put in the event type"
                @if($model === 'creating') x-model="creating.event_type" @else x-model="editing.event_type" @endif
                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
            <option value="one-off">One-off</option>
            <option value="recurring">Recurring</option>
        </select>
        <x-input-error :messages="$errors->get('event_type')" class="mt-2" />
    </div>

    <div @if($model === 'creating') x-show="creating.event_type === 'recurring'" @else x-show="editing.event_type === 'recurring'" @endif class="rounded-2xl border border-cyan-200 bg-cyan-50/70 p-4 dark:border-cyan-500/20 dark:bg-cyan-500/10">
        <p class="mb-3 text-sm font-semibold text-cyan-900 dark:text-cyan-100">Recurring schedule</p>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
            <label for="{{ $model }}-recurring-frequency-value" class="mb-1 block text-sm font-medium text-slate-700 dark:text-gray-300">
                Frequency <span class="text-red-400">*</span>
            </label>
            <input id="{{ $model }}-recurring-frequency-value" type="number" name="recurring_frequency_value" min="1"
                   aria-label="Frequency for the event"
                   @if($model === 'creating') x-model="creating.recurring_frequency_value" :disabled="creating.event_type !== 'recurring'" @else x-model="editing.recurring_frequency_value" :disabled="editing.event_type !== 'recurring'" @endif
                   class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 disabled:opacity-50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
            <p class="mt-2 text-xs text-slate-500 dark:text-gray-400">Example: 3 means the event happens three times per selected unit.</p>
            <x-input-error :messages="$errors->get('recurring_frequency_value')" class="mt-2" />
            </div>
            <div>
                <label for="{{ $model }}-recurring-frequency-unit" class="mb-1 block text-sm font-medium text-slate-700 dark:text-gray-300">
                    Frequency unit <span class="text-red-400">*</span>
                </label>
                <select id="{{ $model }}-recurring-frequency-unit" name="recurring_frequency_unit"
                        aria-label="Select the unit that the event occurs in"
                        @if($model === 'creating') x-model="creating.recurring_frequency_unit" :disabled="creating.event_type !== 'recurring'" @else x-model="editing.recurring_frequency_unit" :disabled="editing.event_type !== 'recurring'" @endif
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 disabled:opacity-50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                    <option value="hour">Hour</option>
                    <option value="day">Day</option>
                    <option value="week">Week</option>
                    <option value="month">Month</option>
                </select>
                <x-input-error :messages="$errors->get('recurring_frequency_unit')" class="mt-2" />
            </div>
        </div>
    </div>

    <div @if($model === 'creating') x-show="creating.event_type === 'one-off'" @else x-show="editing.event_type === 'one-off'" @endif class="rounded-2xl border border-amber-200 bg-amber-50/80 p-4 dark:border-amber-500/20 dark:bg-amber-500/10">
        <p class="mb-3 text-sm font-semibold text-amber-900 dark:text-amber-100">One-off duration</p>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="{{ $model }}-one-off-duration-value" class="mb-1 block text-sm font-medium text-slate-700 dark:text-gray-300">
                    Duration <span class="text-red-400">*</span>
                </label>
                <input id="{{ $model }}-one-off-duration-value" type="number" name="one_off_duration_value" min="1"
                       aria-label="Duration of the event"
                       @if($model === 'creating') x-model="creating.one_off_duration_value" :disabled="creating.event_type !== 'one-off'" @else x-model="editing.one_off_duration_value" :disabled="editing.event_type !== 'one-off'" @endif
                       class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 disabled:opacity-50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                <x-input-error :messages="$errors->get('one_off_duration_value')" class="mt-2" />
            </div>
            <div>
                <label for="{{ $model }}-one-off-duration-unit" class="mb-1 block text-sm font-medium text-slate-700 dark:text-gray-300">
                    Duration unit <span class="text-red-400">*</span>
                </label>
                <select id="{{ $model }}-one-off-duration-unit" name="one_off_duration_unit"
                        aria-label="Select the unit that the event occurs in"
                        @if($model === 'creating') x-model="creating.one_off_duration_unit" :disabled="creating.event_type !== 'one-off'" @else x-model="editing.one_off_duration_unit" :disabled="editing.event_type !== 'one-off'" @endif
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 disabled:opacity-50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                    <option value="hour">Hour</option>
                    <option value="day">Day</option>
                    <option value="week">Week</option>
                </select>
                <x-input-error :messages="$errors->get('one_off_duration_unit')" class="mt-2" />
            </div>
        </div>
    </div>
</div>