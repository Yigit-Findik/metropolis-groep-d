{{--
    Renders the "Affected Functions" section for both the create form and the edit modal.

    For the CREATE form: PHP renders each function row; Alpine manages per-row toggle state.
    For the EDIT modal:  Alpine renders the list dynamically from `editing.linkedFunctions`
                         and the `allFunctions` array initialised in cityEvents.js.

    $model  — 'creating' | 'editing'  (matches the Alpine x-data keys)
--}}

<div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-gray-600 dark:bg-gray-900/30">
    <p class="mb-1 text-sm font-semibold text-slate-800 dark:text-gray-100">Affected Functions</p>
    <p class="mb-3 text-xs text-slate-500 dark:text-gray-400">
        Select which city functions this event temporarily adjusts, then set the modifier value
        for each effect category (-10 to +10).
    </p>

    @if($model === 'creating')
        {{-- Static PHP rendering for the create form — Alpine manages toggle per row. --}}
        @forelse($cityFunctions as $fn)
            <div x-data="{
                    selected: false,
                    mods: { safety: 0, recreation: 0, environment_quality: 0, facilities: 0, mobility: 0 }
                 }"
                 class="mb-3">
                <label class="flex cursor-pointer items-center gap-2">
                    <input type="checkbox" x-model="selected"
                           class="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500 dark:border-gray-600">
                    <span class="text-sm font-medium text-slate-800 dark:text-gray-200">{{ $fn->name }}</span>
                    @if($fn->category)
                        <span class="text-xs text-slate-400 dark:text-gray-500">({{ $fn->category }})</span>
                    @endif
                </label>

                <div x-show="selected" x-cloak class="mt-2 grid grid-cols-2 gap-2 pl-6 sm:grid-cols-5">
                    @foreach(['safety' => 'Safety', 'recreation' => 'Recreation', 'environment_quality' => 'Env. Quality', 'facilities' => 'Facilities', 'mobility' => 'Mobility'] as $key => $label)
                        <div>
                            <label class="mb-0.5 block text-xs text-slate-500 dark:text-gray-400">{{ $label }}</label>
                            <input type="number"
                                   name="functions[{{ $fn->id }}][{{ $key }}_modifier]"
                                   x-model="mods.{{ $key }}"
                                   :disabled="!selected"
                                   min="-10" max="10"
                                   class="w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm text-slate-900 shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 disabled:opacity-40 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500 dark:text-gray-400">No city functions available yet.</p>
        @endforelse

    @else
        {{-- Alpine rendering for the edit modal — populated from editing.linkedFunctions. --}}
        <template x-for="fn in allFunctions" :key="fn.id">
            <div x-data="{
                    get linked() { return editing.linkedFunctions.find(l => l.id === fn.id) ?? null; },
                    get selected() { return this.linked !== null; },
                    set selected(val) {
                        if (val) {
                            editing.linkedFunctions.push({ id: fn.id, safety_modifier: 0, recreation_modifier: 0, environment_quality_modifier: 0, facilities_modifier: 0, mobility_modifier: 0 });
                        } else {
                            editing.linkedFunctions = editing.linkedFunctions.filter(l => l.id !== fn.id);
                        }
                    },
                    modifier(key) {
                        const l = editing.linkedFunctions.find(l => l.id === fn.id);
                        return l ? l[key] : 0;
                    },
                    setModifier(key, val) {
                        const l = editing.linkedFunctions.find(l => l.id === fn.id);
                        if (l) l[key] = parseInt(val) || 0;
                    }
                 }"
                 class="mb-3">
                <label class="flex cursor-pointer items-center gap-2">
                    <input type="checkbox"
                           :checked="selected"
                           @change="selected = $event.target.checked"
                           class="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500 dark:border-gray-600">
                    <span class="text-sm font-medium text-slate-800 dark:text-gray-200" x-text="fn.name"></span>
                    <span class="text-xs text-slate-400 dark:text-gray-500" x-text="fn.category ? '(' + fn.category + ')' : ''"></span>
                </label>

                <div x-show="selected" x-cloak class="mt-2 grid grid-cols-2 gap-2 pl-6 sm:grid-cols-5">
                    @foreach(['safety_modifier' => 'Safety', 'recreation_modifier' => 'Recreation', 'environment_quality_modifier' => 'Env. Quality', 'facilities_modifier' => 'Facilities', 'mobility_modifier' => 'Mobility'] as $key => $label)
                        <div>
                            <label class="mb-0.5 block text-xs text-slate-500 dark:text-gray-400">{{ $label }}</label>
                            <input type="number"
                                   :name="`functions[${fn.id}][{{ $key }}]`"
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
    @endif
</div>
