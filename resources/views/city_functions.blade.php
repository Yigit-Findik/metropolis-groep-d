<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 hc:text-white dark:text-gray-200 leading-tight">
            {{ __('City Functions') }}
        </h1>
    </x-slot>

    <div class="py-12" x-data="cityFunctions">
        <div class="px-4 sm:px-6 lg:px-8 flex flex-col items-center gap-4">

            <div class="w-fit self-end">
                <button @click="openCreate()"
                        class="px-4 py-2 bg-blue-600 hc:bg-yellow-300 hc:text-black hover:bg-blue-700 hc:hover:bg-yellow-200 text-white text-sm font-semibold rounded-lg transition">
                    + Create Function
                </button>
            </div>

            @if($cityFunctions->isEmpty())
                <p class="text-gray-700 hc:text-white dark:text-white">No city functions found.</p>
            @else
                <div class="bg-white hc:bg-black hc:border hc:border-white dark:bg-gray-800 rounded-2xl shadow-sm hc:shadow-none overflow-x-auto w-full">
                    <table class="min-w-full divide-y divide-gray-200 hc:divide-white dark:divide-gray-700" role="grid" aria-label="City functions list">
                        <thead class="bg-gray-50 hc:bg-neutral-900 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 hc:text-white dark:text-white uppercase tracking-wider">Image</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 hc:text-white dark:text-white uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 hc:text-white dark:text-white uppercase tracking-wider">Category</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 hc:text-white dark:text-white uppercase tracking-wider">Description</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 hc:text-white dark:text-white uppercase tracking-wider" aria-hidden="true">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 hc:divide-white dark:divide-gray-700">
                            @foreach($cityFunctions as $fn)
                                <tr role="row" tabindex="0"
                                    class="hc:text-white"
                                    aria-label="Row {{ $loop->iteration }}. Name: {{ $fn->name }}. Category: {{ $fn->category ?? 'Put in a category here' }}. Description: {{ $fn->description ?? 'Put in a description here' }}">
                                    <td class="px-6 py-4">
                                        @if($fn->image_path)
                                            <img src="{{ asset($fn->image_path) }}"
                                                 alt="{{ $fn->image_alt ?? $fn->name }}"
                                                 class="w-12 h-12 object-contain rounded">
                                        @else
                                            <div class="w-12 h-12 bg-gray-200 hc:bg-neutral-800 hc:border hc:border-white dark:bg-gray-700 rounded flex items-center justify-center text-gray-500 hc:text-white dark:text-white text-xs" aria-hidden="true">—</div>
                                        @endif
                                    </td>
                                    <td id="fn-name-{{ $fn->id }}" class="px-6 py-4 font-semibold text-gray-900 hc:text-white dark:text-white whitespace-nowrap">
                                        {{ $fn->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span id="fn-cat-{{ $fn->id }}" class="inline-block px-2 py-1 text-xs font-medium rounded-full bg-blue-100 hc:bg-black hc:border hc:border-white text-blue-800 hc:text-white dark:bg-blue-900/40 dark:text-white">
                                            {{ $fn->category ?? '—' }}
                                        </span>
                                    </td>
                                    <td id="fn-desc-{{ $fn->id }}" class="px-6 py-4 text-sm text-gray-700 hc:text-white dark:text-white max-w-md">
                                        @if($fn->description)
                                            {{ $fn->description }}
                                        @else
                                            <span aria-hidden="true" class="text-gray-400 hc:text-white dark:text-white">—</span>
                                            <span class="sr-only">Put in a description here</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <button type="button"
                                                aria-label="Edit {{ $fn->name }}"
                                                @click="openEdit({
                                                    id: {{ $fn->id }},
                                                    name: @js($fn->name),
                                                    category: @js($fn->category),
                                                    description: @js($fn->description ?? ''),
                                                    safety: {{ $fn->Safety ?? 0 }},
                                                    recreation: {{ $fn->Recreation ?? 0 }},
                                                    environment_quality: {{ $fn->{'Environment Quality'} ?? 0 }},
                                                    facilities: {{ $fn->Facilities ?? 0 }},
                                                    mobility: {{ $fn->Mobility ?? 0 }},
                                                    image_path: @js($fn->image_path ?? ''),
                                                    image_alt: @js($fn->image_alt ?? ''),
                                                    functionConditions: @js($fn->functionConditions)
                                                }, @js($cityFunctions->map(fn($f) => ['id' => $f->id, 'name' => $f->name, 'functionConditions' => $f->functionConditions])))"
                                                class="px-3 py-2 bg-yellow-600 hc:bg-yellow-300 hc:text-black hover:bg-yellow-500 hc:hover:bg-yellow-200 text-white text-xs font-semibold rounded-lg transition">
                                            Edit
                                            </button>

                                            <form action="/city_functions/{{ $fn->id }}" method="POST"
                                                  x-data="deleteForm(@js($fn->name))"
                                                  @submit="confirmAndSubmit($event)">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        aria-label="Delete {{ $fn->name }}"
                                                        class="px-3 py-2 bg-red-700 hc:bg-red-400 hc:text-black hover:bg-red-600 hc:hover:bg-red-300 text-white text-xs font-semibold rounded-lg transition">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

        </div>

        {{-- CREATE MODAL --}}
        <div x-cloak x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60"
             @keydown.escape.window="closeCreate()"
             x-effect="if (open) $nextTick(() => $refs.createName && $refs.createName.focus())"
             @click.self="closeCreate()"
             @focusin.window="enforceCreateFocus($event, $refs.createDialog)">

            <div x-ref="createDialog" class="bg-white hc:bg-black hc:border-2 hc:border-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg mx-4 p-6 max-h-[90vh] overflow-y-auto">
                <h2 class="text-lg font-bold text-gray-900 hc:text-white dark:text-white mb-6">Create City Function</h2>

                <form method="POST" action="/city_functions" enctype="multipart/form-data" class="dark:[color-scheme:dark]" @keydown.tab.prevent="trapCreateFocus($event)">
                    @csrf

                    <div class="mb-4">
                        <label for="create-image" class="block text-sm font-medium text-gray-600 hc:text-white dark:text-gray-300 mb-1">Image</label>
                        <input type="file" id="create-image" name="image" accept="image/*"
                               class="w-full text-sm text-gray-700 hc:text-white dark:text-white bg-gray-50 hc:bg-neutral-900 dark:bg-gray-700 rounded-lg border border-gray-300 hc:border-white dark:border-gray-600 px-3 py-2
                                      file:mr-3 file:py-1 file:px-3 file:rounded file:border-0
                                      file:text-sm file:bg-blue-600 hc:file:bg-yellow-300 hc:file:text-black file:text-white hover:file:bg-blue-700 hc:hover:file:bg-yellow-200 cursor-pointer">
                    </div>

                    <div class="mb-4">
                        <label for="create-name" class="block text-sm font-medium text-gray-600 hc:text-white dark:text-gray-300 mb-1">Name <span class="text-red-400 hc:text-red-400">*</span></label>
                        <input type="text" id="create-name" name="name" required x-ref="createName"
                               class="!bg-gray-50 hc:!bg-black hc:!text-white hc:!border-white dark:!bg-gray-700 !text-gray-900 dark:!text-white w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 hc:focus:ring-yellow-400">
                    </div>

                    <div class="mb-4">
                        <label for="create-category" class="block text-sm font-medium text-gray-600 hc:text-white dark:text-gray-300 mb-1">Category <span class="text-red-400 hc:text-red-400">*</span></label>
                        <select id="create-category" name="category" required
                                class="!bg-gray-50 hc:!bg-black hc:!text-white hc:!border-white dark:!bg-gray-700 !text-gray-900 dark:!text-white w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 hc:focus:ring-yellow-400">
                            <option value="" disabled selected>Put in a category here</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="create-description" class="block text-sm font-medium text-gray-600 hc:text-white dark:text-gray-300 mb-1">Description <span class="text-red-400 hc:text-red-400">*</span></label>
                        <textarea id="create-description" name="description" required rows="3"
                                  class="!bg-gray-50 hc:!bg-black hc:!text-white hc:!border-white dark:!bg-gray-700 !text-gray-900 dark:!text-white w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 hc:focus:ring-yellow-400 resize-none"></textarea>
                    </div>

                    <div class="mb-4" x-data="qolToggle">
                        <div class="flex items-center gap-3 mb-2">
                            <input type="checkbox" id="create-qol-toggle" x-model="qol"
                                   :aria-expanded="qol.toString()"
                                   aria-controls="create-qol-fields"
                                   class="w-4 h-4 rounded border-gray-300 hc:border-white dark:border-gray-600 bg-white hc:bg-black dark:bg-gray-700 text-blue-500 hc:accent-yellow-300 focus:ring-blue-500 hc:focus:ring-yellow-400 cursor-pointer">
                            <label for="create-qol-toggle" class="text-sm text-gray-600 hc:text-white dark:text-gray-300 cursor-pointer select-none">
                                Do you want to insert the QoL values?
                            </label>
                        </div>
                        <div id="create-qol-fields" x-show="qol" x-transition class="grid grid-cols-2 gap-4">
                            @foreach(['safety' => 'Safety', 'recreation' => 'Recreation', 'environment_quality' => 'Environment Quality', 'facilities' => 'Facilities', 'mobility' => 'Mobility'] as $slug => $label)
                                <div>
                                    <label for="create-{{ $slug }}" class="block text-sm font-medium text-gray-600 hc:text-white dark:text-gray-300 mb-1">{{ $label }}</label>
                                    <input type="number" id="create-{{ $slug }}" name="{{ $slug }}" value="0" min="-10" max="10"
                                           :disabled="!qol"
                                           class="!bg-gray-50 hc:!bg-black hc:!text-white hc:!border-white dark:!bg-gray-700 !text-gray-900 dark:!text-white w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 hc:focus:ring-yellow-400">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-between mt-6">
                        <button type="button" @click="closeCreate()"
                                class="px-4 py-2 bg-gray-200 hc:bg-neutral-800 hc:border hc:border-white hc:text-white dark:bg-gray-700 hover:bg-gray-300 hc:hover:bg-neutral-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white text-sm font-semibold rounded-lg transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 hc:bg-yellow-300 hc:text-black hover:bg-blue-700 hc:hover:bg-yellow-200 text-white text-sm font-semibold rounded-lg transition">
                            Create
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- EDIT MODAL --}}
        <div x-cloak x-show="editOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60"
             @keydown.escape.window="closeEdit()"
             x-effect="if (editOpen) $nextTick(() => $refs.editName && $refs.editName.focus())"
             @click.self="closeEdit()"
             @focusin.window="enforceEditFocus($event, $refs.editDialog)">

            <div x-ref="editDialog" class="bg-white hc:bg-black hc:border-2 hc:border-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg mx-4 p-6 max-h-[90vh] overflow-y-auto">
                <h2 class="text-lg font-bold text-gray-900 hc:text-white dark:text-white mb-6">Edit City Function</h2>

                <form method="POST" :action="'/city_functions/' + editing.id" enctype="multipart/form-data" class="dark:[color-scheme:dark]" @submit="submitEdit" @keydown.tab.prevent="trapEditFocus($event)">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="edit-image" class="block text-sm font-medium text-gray-600 hc:text-white dark:text-gray-300 mb-1">Image</label>
                        <template x-if="editing.image_path">
                            <img :src="'/' + editing.image_path" :alt="editing.image_alt ?? editing.name" class="w-12 h-12 object-contain rounded mb-2">
                        </template>
                        <input type="file" id="edit-image" name="image" accept="image/*"
                               class="w-full text-sm text-gray-700 hc:text-white dark:text-white bg-gray-50 hc:bg-neutral-900 dark:bg-gray-700 rounded-lg border border-gray-300 hc:border-white dark:border-gray-600 px-3 py-2
                                      file:mr-3 file:py-1 file:px-3 file:rounded file:border-0
                                      file:text-sm file:bg-blue-600 hc:file:bg-yellow-300 hc:file:text-black file:text-white hover:file:bg-blue-700 hc:hover:file:bg-yellow-200 cursor-pointer">
                        <p class="text-xs text-gray-500 hc:text-white dark:text-gray-400 mt-1">Leave empty to keep the current image.</p>
                    </div>

                    <div class="mb-4">
                        <label for="edit-image-alt" class="block text-sm font-medium text-gray-600 hc:text-white dark:text-gray-300 mb-1">Image alt text (optional)</label>
                        <input type="text" id="edit-image-alt" name="image_alt" x-model="editing.image_alt"
                               class="!bg-gray-50 hc:!bg-black hc:!text-white hc:!border-white dark:!bg-gray-700 !text-gray-900 dark:!text-white w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 hc:focus:ring-yellow-400"
                               placeholder="Short description for screen readers">
                    </div>

                    <div class="mb-4">
                        <label for="edit-name" class="block text-sm font-medium text-gray-600 hc:text-white dark:text-gray-300 mb-1">Name <span class="text-red-400 hc:text-red-400">*</span></label>
                        <input type="text" id="edit-name" name="name" required x-model="editing.name" x-ref="editName"
                               class="!bg-gray-50 hc:!bg-black hc:!text-white hc:!border-white dark:!bg-gray-700 !text-gray-900 dark:!text-white w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 hc:focus:ring-yellow-400">
                    </div>

                    <div class="mb-4">
                        <label for="edit-category" class="block text-sm font-medium text-gray-600 hc:text-white dark:text-gray-300 mb-1">Category <span class="text-red-400 hc:text-red-400">*</span></label>
                        <select id="edit-category" name="category" required x-model="editing.category"
                                class="!bg-gray-50 hc:!bg-black hc:!text-white hc:!border-white dark:!bg-gray-700 !text-gray-900 dark:!text-white w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 hc:focus:ring-yellow-400">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="edit-description" class="block text-sm font-medium text-gray-600 hc:text-white dark:text-gray-300 mb-1">Description</label>
                        <textarea id="edit-description" name="description" rows="3" x-model="editing.description"
                                  class="!bg-gray-50 hc:!bg-black hc:!text-white hc:!border-white dark:!bg-gray-700 !text-gray-900 dark:!text-white w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 hc:focus:ring-yellow-400 resize-none"></textarea>
                    </div>

                    <div class="mb-4" role="group" aria-labelledby="edit-qol-heading">
                        <p id="edit-qol-heading" class="block text-sm font-medium text-gray-600 hc:text-white dark:text-gray-300 mb-2">QoL Values</p>
                        <div class="grid grid-cols-2 gap-4">
                            @foreach([
                                'edit-safety' => ['name' => 'safety', 'model' => 'editing.safety', 'label' => 'Safety'],
                                'edit-recreation' => ['name' => 'recreation', 'model' => 'editing.recreation', 'label' => 'Recreation'],
                                'edit-environment-quality' => ['name' => 'environment_quality', 'model' => 'editing.environment_quality', 'label' => 'Environment Quality'],
                                'edit-facilities' => ['name' => 'facilities', 'model' => 'editing.facilities', 'label' => 'Facilities'],
                                'edit-mobility' => ['name' => 'mobility', 'model' => 'editing.mobility', 'label' => 'Mobility'],
                            ] as $id => $field)
                                <div>
                                    <label for="{{ $id }}" class="block text-sm font-medium text-gray-600 hc:text-white dark:text-gray-300 mb-1">{{ $field['label'] }}</label>
                                    <input type="number" id="{{ $id }}" name="{{ $field['name'] }}" min="-10" max="10" x-model="editing.{{ $field['name'] }}"
                                           class="!bg-gray-50 hc:!bg-black hc:!text-white hc:!border-white dark:!bg-gray-700 !text-gray-900 dark:!text-white w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 hc:focus:ring-yellow-400">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-4" role="group" aria-labelledby="edit-adjacency-heading">
                        <p id="edit-adjacency-heading" class="block text-sm font-medium text-gray-600 hc:text-white dark:text-gray-300 mb-2">Adjacency Rules</p>
                        <p class="text-xs text-gray-500 hc:text-white dark:text-gray-400 mb-3">Define which functions must be neighbors or must not be neighbors.</p>

                        <div class="space-y-2 mb-4">
                            <template x-for="(condition, index) in editing.conditions" :key="index">
                                <div class="flex items-center justify-between bg-gray-100 hc:bg-neutral-900 hc:border hc:border-white dark:bg-gray-700 rounded-lg p-3">
                                    <div class="flex-1">
                                        <span x-text="getTargetName(condition.target_function_id)" class="font-medium text-gray-900 hc:text-white dark:text-white"></span>
                                        <span x-text="' (' + condition.type + ')'" :class="condition.type === 'forbidden' ? 'text-red-400 hc:text-red-400' : 'text-green-400 hc:text-green-400'" class="text-sm ml-2"></span>
                                    </div>
                                    <button type="button" @click="removeCondition(index)"
                                            class="px-3 py-2 bg-red-700 hc:bg-red-400 hc:text-black hover:bg-red-600 hc:hover:bg-red-300 text-white text-xs font-semibold rounded transition">
                                        Remove
                                    </button>
                                </div>
                            </template>
                            <template x-if="editing.conditions.length === 0">
                                <p class="text-sm text-gray-500 hc:text-white">No adjacency rules yet.</p>
                            </template>
                        </div>

                        <div class="bg-gray-100 hc:bg-neutral-900 hc:border hc:border-white dark:bg-gray-700 rounded-lg p-3 space-y-3">
                            <div>
                                <label for="edit-condition-target" class="block text-xs font-medium text-gray-600 hc:text-white dark:text-gray-300 mb-1">Target Function</label>
                                <select id="edit-condition-target" x-model.number="newConditionTarget"
                                        class="!bg-white hc:!bg-black hc:!text-white hc:!border-white dark:!bg-gray-800 !text-gray-900 dark:!text-white w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 hc:focus:ring-yellow-400">
                                    <option value="">Select a function</option>
                                    <template x-for="fn in allFunctions.filter(f => f.id !== editing.id)" :key="fn.id">
                                        <option :value="fn.id" x-text="fn.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="flex gap-3">
                                <div class="flex-1">
                                    <label for="edit-condition-type" class="block text-xs font-medium text-gray-600 hc:text-white dark:text-gray-300 mb-1">Type</label>
                                    <select id="edit-condition-type" x-model="newConditionType"
                                            class="!bg-white hc:!bg-black hc:!text-white hc:!border-white dark:!bg-gray-800 !text-gray-900 dark:!text-white w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 hc:focus:ring-yellow-400">
                                        <option value="required">Required</option>
                                        <option value="forbidden">Forbidden</option>
                                    </select>
                                </div>
                                <div class="flex items-end">
                                    <button type="button" @click="addCondition()"
                                            class="px-4 py-2 bg-blue-600 hc:bg-yellow-300 hc:text-black hover:bg-blue-700 hc:hover:bg-yellow-200 text-white text-xs font-semibold rounded-lg transition">
                                        Add Rule
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between mt-6">
                        <button type="button" @click="closeEdit()"
                                class="px-4 py-2 bg-gray-200 hc:bg-neutral-800 hc:border hc:border-white hc:text-white dark:bg-gray-700 hover:bg-gray-300 hc:hover:bg-neutral-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white text-sm font-semibold rounded-lg transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-yellow-600 hc:bg-yellow-300 hc:text-black hover:bg-yellow-500 hc:hover:bg-yellow-200 text-white text-sm font-semibold rounded-lg transition">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if(session('success'))
        <div x-data="autoHideToast"
             x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl shadow-lg text-white hc:text-black text-sm font-semibold bg-green-700 hc:bg-green-400">
            {{ session('success') }}
        </div>
        @endif

        {{-- CONFIRMATION MODAL --}}
        <div x-cloak x-data="confirmModal" @keydown.escape="cancel()"
             x-show="show"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60"
             @click.self="cancel()">

            <div class="bg-white hc:bg-black hc:border-2 hc:border-white dark:bg-gray-800 rounded-xl shadow-xl max-w-sm mx-4 p-6"
                 @click.stop>
                <p class="text-gray-700 hc:text-white dark:text-gray-300 mb-6" x-text="message"></p>
                <div class="flex justify-end gap-3">
                    <button type="button"
                            @click="cancel()"
                            class="px-4 py-2 bg-gray-300 hc:bg-neutral-800 hc:border hc:border-white hc:text-white dark:bg-gray-700 hover:bg-gray-400 hc:hover:bg-neutral-700 dark:hover:bg-gray-600 text-gray-900 dark:text-gray-100 font-semibold rounded-lg transition">
                        Cancel
                    </button>
                    <button type="button"
                            @click="confirm()"
                            class="px-4 py-2 bg-red-600 hc:bg-red-400 hc:text-black hover:bg-red-700 hc:hover:bg-red-300 text-white font-semibold rounded-lg transition">
                        Delete
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
