<div x-cloak x-show="dayNightEditOpen"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto bg-black/60"
     x-effect="if (dayNightEditOpen) $nextTick(() => $refs.dnDayValue && $refs.dnDayValue.focus())"
     @keydown.escape.window="closeDayNightEdit()"
     role="dialog"
     aria-modal="true"
     aria-labelledby="day-night-edit-title"
     aria-describedby="day-night-edit-description">
    <div class="flex min-h-full items-center justify-center px-4 py-6" @click.self="closeDayNightEdit()">
    <div class="w-full max-w-2xl rounded-3xl bg-white hc:bg-black hc:border-2 hc:border-white p-6 shadow-2xl hc:shadow-none dark:bg-gray-800"
         x-data="{ activeTab: 'settings' }">

        <div class="mb-6 flex items-start justify-between gap-4">
            <div>
                <h2 id="day-night-edit-title" class="text-xl font-bold text-slate-900 hc:text-white dark:text-gray-100">Edit Day/Night Cycle</h2>
                <p id="day-night-edit-description" class="mt-1 text-sm text-slate-500 hc:text-white dark:text-gray-400">
                    Configure cycle durations and set which city functions are affected during each phase.
                </p>
            </div>
            <button type="button" @click="closeDayNightEdit()"
                    class="rounded-full p-2 text-slate-400 hc:text-white transition hover:bg-slate-100 hc:hover:bg-neutral-900 hover:text-slate-700 dark:hover:bg-gray-700 dark:hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-yellow-400"
                    aria-label="Close edit dialog">
                &#x2715;
            </button>
        </div>

        {{-- Tabs --}}
        <div class="mb-6 flex gap-1 border-b border-slate-200 hc:border-white dark:border-gray-700" role="tablist">
            <button type="button" role="tab" :aria-selected="activeTab === 'settings'" @click="activeTab = 'settings'"
                    :class="activeTab === 'settings'
                        ? 'border-b-2 border-cyan-500 hc:border-yellow-300 text-cyan-600 hc:text-yellow-300 dark:text-cyan-400'
                        : 'text-slate-500 hc:text-white dark:text-gray-400 hover:text-slate-700 hc:hover:text-yellow-300 dark:hover:text-gray-200'"
                    class="px-4 pb-2 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-yellow-400">
                Durations
            </button>
            <button type="button" role="tab" :aria-selected="activeTab === 'day'" @click="activeTab = 'day'"
                    :class="activeTab === 'day'
                        ? 'border-b-2 border-amber-500 hc:border-yellow-300 text-amber-600 hc:text-yellow-300 dark:text-amber-400'
                        : 'text-slate-500 hc:text-white dark:text-gray-400 hover:text-slate-700 hc:hover:text-yellow-300 dark:hover:text-gray-200'"
                    class="px-4 pb-2 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-yellow-400">
                Day Effects
            </button>
            <button type="button" role="tab" :aria-selected="activeTab === 'night'" @click="activeTab = 'night'"
                    :class="activeTab === 'night'
                        ? 'border-b-2 border-indigo-500 hc:border-yellow-300 text-indigo-600 hc:text-yellow-300 dark:text-indigo-400'
                        : 'text-slate-500 hc:text-white dark:text-gray-400 hover:text-slate-700 hc:hover:text-yellow-300 dark:hover:text-gray-200'"
                    class="px-4 pb-2 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-yellow-400">
                Night Effects
            </button>
        </div>

        <form method="POST" :action="'/events/' + dayNightEditing.id + '/day-night'" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Durations Tab --}}
            <div x-show="activeTab === 'settings'">
                <div class="grid gap-4 sm:grid-cols-2">
                    {{-- Day duration --}}
                    <div class="rounded-2xl border border-amber-200 hc:border-yellow-300 bg-amber-50/70 hc:bg-neutral-900 p-4 dark:border-amber-500/20 dark:bg-amber-500/10">
                        <p class="mb-3 text-sm font-semibold text-amber-900 hc:text-yellow-300 dark:text-amber-100">Day Duration</p>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label for="dn-day-duration-value" class="mb-1 block text-xs font-medium text-slate-700 hc:text-white dark:text-gray-300">
                                    Value <span class="text-red-400">*</span>
                                </label>
                                <input type="number" id="dn-day-duration-value" name="day_duration_value" min="1"
                                       x-ref="dnDayValue"
                                       x-model="dayNightEditing.day_duration_value"
                                       class="w-full rounded-xl border border-slate-300 hc:border-white bg-white hc:bg-black px-3 py-2 text-sm text-slate-900 hc:text-white shadow-sm hc:shadow-none focus:border-cyan-500 hc:focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 hc:focus:ring-yellow-400 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                            </div>
                            <div>
                                <label for="dn-day-duration-unit" class="mb-1 block text-xs font-medium text-slate-700 hc:text-white dark:text-gray-300">
                                    Unit <span class="text-red-400">*</span>
                                </label>
                                <select id="dn-day-duration-unit" name="day_duration_unit"
                                        x-model="dayNightEditing.day_duration_unit"
                                        class="w-full rounded-xl border border-slate-300 hc:border-white bg-white hc:bg-black px-3 py-2 text-sm text-slate-900 hc:text-white shadow-sm hc:shadow-none focus:border-cyan-500 hc:focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 hc:focus:ring-yellow-400 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                                    <option value="minute">Minute</option>
                                    <option value="hour">Hour</option>
                                    <option value="day">Day</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Night duration --}}
                    <div class="rounded-2xl border border-indigo-200 hc:border-white bg-indigo-50/70 hc:bg-neutral-900 p-4 dark:border-indigo-500/20 dark:bg-indigo-500/10">
                        <p class="mb-3 text-sm font-semibold text-indigo-900 hc:text-white dark:text-indigo-100">Night Duration</p>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label for="dn-night-duration-value" class="mb-1 block text-xs font-medium text-slate-700 hc:text-white dark:text-gray-300">
                                    Value <span class="text-red-400">*</span>
                                </label>
                                <input type="number" id="dn-night-duration-value" name="night_duration_value" min="1"
                                       x-model="dayNightEditing.night_duration_value"
                                       class="w-full rounded-xl border border-slate-300 hc:border-white bg-white hc:bg-black px-3 py-2 text-sm text-slate-900 hc:text-white shadow-sm hc:shadow-none focus:border-cyan-500 hc:focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 hc:focus:ring-yellow-400 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                            </div>
                            <div>
                                <label for="dn-night-duration-unit" class="mb-1 block text-xs font-medium text-slate-700 hc:text-white dark:text-gray-300">
                                    Unit <span class="text-red-400">*</span>
                                </label>
                                <select id="dn-night-duration-unit" name="night_duration_unit"
                                        x-model="dayNightEditing.night_duration_unit"
                                        class="w-full rounded-xl border border-slate-300 hc:border-white bg-white hc:bg-black px-3 py-2 text-sm text-slate-900 hc:text-white shadow-sm hc:shadow-none focus:border-cyan-500 hc:focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 hc:focus:ring-yellow-400 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                                    <option value="minute">Minute</option>
                                    <option value="hour">Hour</option>
                                    <option value="day">Day</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Day Effects Tab --}}
            <div x-show="activeTab === 'day'"
                 class="rounded-2xl border border-amber-200 hc:border-yellow-300 bg-amber-50/70 hc:bg-neutral-900 p-4 dark:border-amber-500/20 dark:bg-amber-500/10">
                <p class="mb-1 text-sm font-semibold text-amber-900 hc:text-yellow-300 dark:text-amber-100">Day Phase Effects</p>
                <p class="mb-3 text-xs text-slate-500 hc:text-white dark:text-gray-400">
                    Select which city functions are affected during the day, and set the modifier value for each category (-10 to +10).
                </p>

                <template x-for="fn in allFunctions" :key="fn.id">
                    <div x-data="{
                            get linked() { return dayNightEditing.dayLinkedFunctions.find(l => l.id === fn.id) ?? null; },
                            get selected() { return this.linked !== null; },
                            set selected(val) {
                                if (val) {
                                    dayNightEditing.dayLinkedFunctions.push({ id: fn.id, safety_modifier: 0, recreation_modifier: 0, environment_quality_modifier: 0, facilities_modifier: 0, mobility_modifier: 0 });
                                } else {
                                    dayNightEditing.dayLinkedFunctions = dayNightEditing.dayLinkedFunctions.filter(l => l.id !== fn.id);
                                }
                            },
                            modifier(key) {
                                const l = dayNightEditing.dayLinkedFunctions.find(l => l.id === fn.id);
                                return l ? l[key] : 0;
                            },
                            setModifier(key, val) {
                                const l = dayNightEditing.dayLinkedFunctions.find(l => l.id === fn.id);
                                if (l) l[key] = parseInt(val) || 0;
                            }
                         }"
                         class="mb-3">
                        <label class="flex cursor-pointer items-center gap-2">
                            <input type="checkbox"
                                   :checked="selected"
                                   @change="selected = $event.target.checked"
                                   class="h-4 w-4 rounded border-slate-300 hc:border-white text-amber-600 hc:accent-yellow-300 focus:ring-amber-500 hc:focus:ring-yellow-400 dark:border-gray-600">
                            <span class="text-sm font-medium text-slate-800 hc:text-white dark:text-gray-200" x-text="fn.name"></span>
                            <span class="text-xs text-slate-400 hc:text-white dark:text-gray-500" x-text="fn.category ? '(' + fn.category + ')' : ''"></span>
                        </label>

                        <div x-show="selected" x-cloak class="mt-2 grid grid-cols-2 gap-2 pl-6 sm:grid-cols-5">
                            @foreach(['safety_modifier' => 'Safety', 'recreation_modifier' => 'Recreation', 'environment_quality_modifier' => 'Env. Quality', 'facilities_modifier' => 'Facilities', 'mobility_modifier' => 'Mobility'] as $key => $label)
                                <div>
                                    <label :for="'dn-day-' + fn.id + '-{{ $key }}'" class="mb-0.5 block text-xs text-slate-500 hc:text-white dark:text-gray-400">{{ $label }}</label>
                                    <input type="number"
                                           :id="'dn-day-' + fn.id + '-{{ $key }}'"
                                           :name="`day_functions[${fn.id}][{{ $key }}]`"
                                           :value="modifier('{{ $key }}')"
                                           @input="setModifier('{{ $key }}', $event.target.value)"
                                           :disabled="!selected"
                                           min="-10" max="10"
                                           class="w-full rounded-lg border border-slate-300 hc:border-white bg-white hc:bg-black px-2 py-1.5 text-sm text-slate-900 hc:text-white shadow-sm hc:shadow-none focus:border-cyan-500 hc:focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 hc:focus:ring-yellow-400 disabled:opacity-40 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </template>

                <p x-show="allFunctions.length === 0" class="text-sm text-slate-500 hc:text-white dark:text-gray-400">No city functions available yet.</p>
            </div>

            {{-- Night Effects Tab --}}
            <div x-show="activeTab === 'night'"
                 class="rounded-2xl border border-indigo-200 hc:border-white bg-indigo-50/70 hc:bg-neutral-900 p-4 dark:border-indigo-500/20 dark:bg-indigo-500/10">
                <p class="mb-1 text-sm font-semibold text-indigo-900 hc:text-white dark:text-indigo-100">Night Phase Effects</p>
                <p class="mb-3 text-xs text-slate-500 hc:text-white dark:text-gray-400">
                    Select which city functions are affected during the night, and set the modifier value for each category (-10 to +10).
                </p>

                <template x-for="fn in allFunctions" :key="fn.id">
                    <div x-data="{
                            get linked() { return dayNightEditing.nightLinkedFunctions.find(l => l.id === fn.id) ?? null; },
                            get selected() { return this.linked !== null; },
                            set selected(val) {
                                if (val) {
                                    dayNightEditing.nightLinkedFunctions.push({ id: fn.id, safety_modifier: 0, recreation_modifier: 0, environment_quality_modifier: 0, facilities_modifier: 0, mobility_modifier: 0 });
                                } else {
                                    dayNightEditing.nightLinkedFunctions = dayNightEditing.nightLinkedFunctions.filter(l => l.id !== fn.id);
                                }
                            },
                            modifier(key) {
                                const l = dayNightEditing.nightLinkedFunctions.find(l => l.id === fn.id);
                                return l ? l[key] : 0;
                            },
                            setModifier(key, val) {
                                const l = dayNightEditing.nightLinkedFunctions.find(l => l.id === fn.id);
                                if (l) l[key] = parseInt(val) || 0;
                            }
                         }"
                         class="mb-3">
                        <label class="flex cursor-pointer items-center gap-2">
                            <input type="checkbox"
                                   :checked="selected"
                                   @change="selected = $event.target.checked"
                                   class="h-4 w-4 rounded border-slate-300 hc:border-white text-indigo-600 hc:accent-yellow-300 focus:ring-indigo-500 hc:focus:ring-yellow-400 dark:border-gray-600">
                            <span class="text-sm font-medium text-slate-800 hc:text-white dark:text-gray-200" x-text="fn.name"></span>
                            <span class="text-xs text-slate-400 hc:text-white dark:text-gray-500" x-text="fn.category ? '(' + fn.category + ')' : ''"></span>
                        </label>

                        <div x-show="selected" x-cloak class="mt-2 grid grid-cols-2 gap-2 pl-6 sm:grid-cols-5">
                            @foreach(['safety_modifier' => 'Safety', 'recreation_modifier' => 'Recreation', 'environment_quality_modifier' => 'Env. Quality', 'facilities_modifier' => 'Facilities', 'mobility_modifier' => 'Mobility'] as $key => $label)
                                <div>
                                    <label :for="'dn-night-' + fn.id + '-{{ $key }}'" class="mb-0.5 block text-xs text-slate-500 hc:text-white dark:text-gray-400">{{ $label }}</label>
                                    <input type="number"
                                           :id="'dn-night-' + fn.id + '-{{ $key }}'"
                                           :name="`night_functions[${fn.id}][{{ $key }}]`"
                                           :value="modifier('{{ $key }}')"
                                           @input="setModifier('{{ $key }}', $event.target.value)"
                                           :disabled="!selected"
                                           min="-10" max="10"
                                           class="w-full rounded-lg border border-slate-300 hc:border-white bg-white hc:bg-black px-2 py-1.5 text-sm text-slate-900 hc:text-white shadow-sm hc:shadow-none focus:border-cyan-500 hc:focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 hc:focus:ring-yellow-400 disabled:opacity-40 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </template>

                <p x-show="allFunctions.length === 0" class="text-sm text-slate-500 hc:text-white dark:text-gray-400">No city functions available yet.</p>
            </div>

            <div class="flex justify-end gap-3">
                <x-secondary-button type="button" @click="closeDayNightEdit()">
                    {{ __('Cancel') }}
                </x-secondary-button>
                <x-primary-button class="bg-cyan-600 hc:bg-yellow-300 hc:text-black hover:bg-cyan-500 hc:hover:bg-yellow-200 focus:ring-cyan-500 hc:focus:ring-yellow-400">
                    {{ __('Save changes') }}
                </x-primary-button>
            </div>
        </form>
    </div>
    </div>
</div>
