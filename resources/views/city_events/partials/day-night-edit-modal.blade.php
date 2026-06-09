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
    <div class="w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl dark:bg-gray-800"
         x-data="{ activeTab: 'settings' }">

        <div class="mb-6 flex items-start justify-between gap-4">
            <div>
                <h2 id="day-night-edit-title" class="text-xl font-bold text-slate-900 dark:text-gray-100">Edit Day/Night Cycle</h2>
                <p id="day-night-edit-description" class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                    Configure cycle durations and set which city functions are affected during each phase.
                </p>
            </div>
            <button type="button" @click="closeDayNightEdit()"
                    class="rounded-full p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"
                    aria-label="Close edit dialog">
                &#x2715;
            </button>
        </div>

        {{-- Tabs --}}
        <div class="mb-6 flex gap-1 border-b border-slate-200 dark:border-gray-700" role="tablist">
            <button type="button" role="tab" :aria-selected="activeTab === 'settings'" @click="activeTab = 'settings'"
                    :class="activeTab === 'settings'
                        ? 'border-b-2 border-cyan-500 text-cyan-600 dark:text-cyan-400'
                        : 'text-slate-500 dark:text-gray-400 hover:text-slate-700 dark:hover:text-gray-200'"
                    class="px-4 pb-2 text-sm font-semibold transition">
                Durations
            </button>
            <button type="button" role="tab" :aria-selected="activeTab === 'day'" @click="activeTab = 'day'"
                    :class="activeTab === 'day'
                        ? 'border-b-2 border-amber-500 text-amber-600 dark:text-amber-400'
                        : 'text-slate-500 dark:text-gray-400 hover:text-slate-700 dark:hover:text-gray-200'"
                    class="px-4 pb-2 text-sm font-semibold transition">
                Day Effects
            </button>
            <button type="button" role="tab" :aria-selected="activeTab === 'night'" @click="activeTab = 'night'"
                    :class="activeTab === 'night'
                        ? 'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400'
                        : 'text-slate-500 dark:text-gray-400 hover:text-slate-700 dark:hover:text-gray-200'"
                    class="px-4 pb-2 text-sm font-semibold transition">
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
                    <div class="rounded-2xl border border-amber-200 bg-amber-50/70 p-4 dark:border-amber-500/20 dark:bg-amber-500/10">
                        <p class="mb-3 text-sm font-semibold text-amber-900 dark:text-amber-100">Day Duration</p>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-gray-300">
                                    Value <span class="text-red-400">*</span>
                                </label>
                                <input type="number" name="day_duration_value" min="1"
                                       x-ref="dnDayValue"
                                       x-model="dayNightEditing.day_duration_value"
                                       class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-gray-300">
                                    Unit <span class="text-red-400">*</span>
                                </label>
                                <select name="day_duration_unit"
                                        x-model="dayNightEditing.day_duration_unit"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                                    <option value="minute">Minute</option>
                                    <option value="hour">Hour</option>
                                    <option value="day">Day</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Night duration --}}
                    <div class="rounded-2xl border border-indigo-200 bg-indigo-50/70 p-4 dark:border-indigo-500/20 dark:bg-indigo-500/10">
                        <p class="mb-3 text-sm font-semibold text-indigo-900 dark:text-indigo-100">Night Duration</p>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-gray-300">
                                    Value <span class="text-red-400">*</span>
                                </label>
                                <input type="number" name="night_duration_value" min="1"
                                       x-model="dayNightEditing.night_duration_value"
                                       class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-gray-300">
                                    Unit <span class="text-red-400">*</span>
                                </label>
                                <select name="night_duration_unit"
                                        x-model="dayNightEditing.night_duration_unit"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
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
                 class="rounded-2xl border border-amber-200 bg-amber-50/70 p-4 dark:border-amber-500/20 dark:bg-amber-500/10">
                <p class="mb-1 text-sm font-semibold text-amber-900 dark:text-amber-100">Day Phase Effects</p>
                <p class="mb-3 text-xs text-slate-500 dark:text-gray-400">
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
                                   class="h-4 w-4 rounded border-slate-300 text-amber-600 focus:ring-amber-500 dark:border-gray-600">
                            <span class="text-sm font-medium text-slate-800 dark:text-gray-200" x-text="fn.name"></span>
                            <span class="text-xs text-slate-400 dark:text-gray-500" x-text="fn.category ? '(' + fn.category + ')' : ''"></span>
                        </label>

                        <div x-show="selected" x-cloak class="mt-2 grid grid-cols-2 gap-2 pl-6 sm:grid-cols-5">
                            @foreach(['safety_modifier' => 'Safety', 'recreation_modifier' => 'Recreation', 'environment_quality_modifier' => 'Env. Quality', 'facilities_modifier' => 'Facilities', 'mobility_modifier' => 'Mobility'] as $key => $label)
                                <div>
                                    <label class="mb-0.5 block text-xs text-slate-500 dark:text-gray-400">{{ $label }}</label>
                                    <input type="number"
                                           :name="`day_functions[${fn.id}][{{ $key }}]`"
                                           :value="modifier('{{ $key }}')"
                                           @input="setModifier('{{ $key }}', $event.target.value)"
                                           :disabled="!selected"
                                           min="-10" max="10"
                                           class="w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm text-slate-900 shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 disabled:opacity-40 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </template>

                <p x-show="allFunctions.length === 0" class="text-sm text-slate-500 dark:text-gray-400">No city functions available yet.</p>
            </div>

            {{-- Night Effects Tab --}}
            <div x-show="activeTab === 'night'"
                 class="rounded-2xl border border-indigo-200 bg-indigo-50/70 p-4 dark:border-indigo-500/20 dark:bg-indigo-500/10">
                <p class="mb-1 text-sm font-semibold text-indigo-900 dark:text-indigo-100">Night Phase Effects</p>
                <p class="mb-3 text-xs text-slate-500 dark:text-gray-400">
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
                                   class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600">
                            <span class="text-sm font-medium text-slate-800 dark:text-gray-200" x-text="fn.name"></span>
                            <span class="text-xs text-slate-400 dark:text-gray-500" x-text="fn.category ? '(' + fn.category + ')' : ''"></span>
                        </label>

                        <div x-show="selected" x-cloak class="mt-2 grid grid-cols-2 gap-2 pl-6 sm:grid-cols-5">
                            @foreach(['safety_modifier' => 'Safety', 'recreation_modifier' => 'Recreation', 'environment_quality_modifier' => 'Env. Quality', 'facilities_modifier' => 'Facilities', 'mobility_modifier' => 'Mobility'] as $key => $label)
                                <div>
                                    <label class="mb-0.5 block text-xs text-slate-500 dark:text-gray-400">{{ $label }}</label>
                                    <input type="number"
                                           :name="`night_functions[${fn.id}][{{ $key }}]`"
                                           :value="modifier('{{ $key }}')"
                                           @input="setModifier('{{ $key }}', $event.target.value)"
                                           :disabled="!selected"
                                           min="-10" max="10"
                                           class="w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm text-slate-900 shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 disabled:opacity-40 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </template>

                <p x-show="allFunctions.length === 0" class="text-sm text-slate-500 dark:text-gray-400">No city functions available yet.</p>
            </div>

            <div class="flex justify-end gap-3">
                <x-secondary-button type="button" @click="closeDayNightEdit()">
                    {{ __('Cancel') }}
                </x-secondary-button>
                <x-primary-button class="bg-cyan-600 hover:bg-cyan-500 focus:ring-cyan-500">
                    {{ __('Save changes') }}
                </x-primary-button>
            </div>
        </form>
    </div>
    </div>
</div>
