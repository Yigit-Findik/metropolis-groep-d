<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('City Functions') }}
        </h1>
    </x-slot>

    {{-- Alpine.js for page:
        open      : controls the create modal
        editOpen  : controls the edit modal
        editing   : holds the data of the function currently being edited
        openEdit  : populates editing with the chosen function and opens the edit modal --}}
    <div class="py-12" x-data="cityFunctions">
        <div class="px-4 sm:px-6 lg:px-8 flex flex-col items-center gap-4">

            {{-- button that opens the create modal --}}
            <div class="w-fit self-end">
                <button @click="openCreate()"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition">
                    + Create Function
                </button>
            </div>

            {{-- Functions table — hidden when no functions exist yet --}}
            @if($cityFunctions->isEmpty())
                <p class="text-gray-700 dark:text-white">No city functions found.</p>
            @else
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-x-auto w-full">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" role="grid" aria-label="City functions list">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-white uppercase tracking-wider">Image</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-white uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-white uppercase tracking-wider">Category</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-white uppercase tracking-wider">Description</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-white uppercase tracking-wider" aria-hidden="true">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($cityFunctions as $fn)
                                <tr role="row" tabindex="0"
                                    aria-label="Row {{ $loop->iteration }}. Name: {{ $fn->name }}. Category: {{ $fn->category ?? 'Put in a category here' }}. Description: {{ $fn->description ?? 'Put in a description here' }}">
                                    {{-- Image column: shows the function image or a placeholder dash --}}
                                    <td class="px-6 py-4">
                                        @if($fn->image_path)
                                            <img src="{{ asset($fn->image_path) }}"
                                                 alt="{{ $fn->image_alt ?? $fn->name }}"
                                                 class="w-12 h-12 object-contain rounded">
                                        @else
                                            <div class="w-12 h-12 bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center text-gray-500 dark:text-white text-xs" aria-hidden="true">—</div>
                                        @endif
                                    </td>

                                    {{-- Name column --}}
                                    <td id="fn-name-{{ $fn->id }}" class="px-6 py-4 font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                                        {{ $fn->name }}
                                    </td>

                                    {{-- Category column: displayed as a pill badge --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span id="fn-cat-{{ $fn->id }}" class="inline-block px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-white">
                                            {{ $fn->category ?? '—' }}
                                        </span>
                                    </td>

                                    {{-- Description column --}}
                                    <td id="fn-desc-{{ $fn->id }}" class="px-6 py-4 text-sm text-gray-700 dark:text-white max-w-md">
                                        @if($fn->description)
                                            {{ $fn->description }}
                                        @else
                                            <span aria-hidden="true" class="text-gray-400 dark:text-white">—</span>
                                            <span class="sr-only">Put in a description here</span>
                                        @endif
                                    </td>

                                    {{-- Actions column: Edit opens the edit modal pre-filled with this row's data.
                                         Delete submits a soft-delete request after a browser confirm dialog. --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">

                                            {{-- Edit button: passes all current field values to the Alpine openEdit() method --}}
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
                                                    class="px-3 py-2 bg-yellow-600 hover:bg-yellow-500 text-white text-xs font-semibold rounded-lg transition">
                                                Edit
                                            </button>

                                            {{-- Delete form: uses method spoofing to send a DELETE request.
                                                 The function is soft-deleted so it can be recovered if needed. --}}
                                            <form action="/city_functions/{{ $fn->id }}" method="POST"
                                                  x-data="deleteForm(@js($fn->name))"
                                                  @submit="confirmAndSubmit($event)">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        aria-label="Delete {{ $fn->name }}"
                                                        class="px-3 py-2 bg-red-700 hover:bg-red-600 text-white text-xs font-semibold rounded-lg transition">
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

        {{-- CREATE MODAL -------------------------------------------------------
             Shown when "open" is true. Clicking the dark backdrop closes the modal.
             enctype="multipart/form-data" is required for image file uploads. --}}
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

                        <div x-ref="createDialog" class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg mx-4 p-6 max-h-[90vh] overflow-y-auto">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Create City Function</h2>

                <form method="POST" action="/city_functions" enctype="multipart/form-data" class="dark:[color-scheme:dark]" @keydown.tab.prevent="trapCreateFocus($event)">
                    @csrf

                    {{-- Optional image upload --}}
                    <div class="mb-4">
                        <label for="create-image" class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Image</label>
                        <input type="file" id="create-image" name="image" accept="image/*"
                               class="w-full text-sm text-gray-700 dark:text-white bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2
                                      file:mr-3 file:py-1 file:px-3 file:rounded file:border-0
                                      file:text-sm file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                    </div>

                    {{-- Required: function name --}}
                    <div class="mb-4">
                        <label for="create-name" class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Name <span class="text-red-400">*</span></label>
                        <input type="text" id="create-name" name="name" required
                               class="!bg-gray-50 dark:!bg-gray-700 !text-gray-900 dark:!text-white w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- Required: category dropdown populated from existing categories in the database --}}
                    <div class="mb-4">
                        <label for="create-category" class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Category <span class="text-red-400">*</span></label>
                        <select id="create-category" name="category" required
                                class="!bg-gray-50 dark:!bg-gray-700 !text-gray-900 dark:!text-white w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="" disabled selected>Put in a category here</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Required: short description of the function --}}
                    <div class="mb-4">
                        <label for="create-description" class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Description <span class="text-red-400">*</span></label>
                        <textarea id="create-description" name="description" required rows="3"
                                  class="!bg-gray-50 dark:!bg-gray-700 !text-gray-900 dark:!text-white w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>

                    {{-- Optional QoL section: checkbox reveals the five score fields.
                         When unchecked, the inputs are disabled so the browser omits them from the
                         form submission and the controller defaults each value to 0. --}}
                    <div class="mb-4" x-data="qolToggle">
                        <div class="flex items-center gap-3 mb-2">
                            <input type="checkbox" id="create-qol-toggle" x-model="qol"
                                   class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-blue-500 focus:ring-blue-500 cursor-pointer">
                            <label for="create-qol-toggle" class="text-sm text-gray-600 dark:text-gray-300 cursor-pointer select-none">
                                Do you want to insert the QoL values?
                            </label>
                        </div>
                        <div x-show="qol" x-transition class="grid grid-cols-2 gap-4">
                            @foreach(['safety' => 'Safety', 'recreation' => 'Recreation', 'environment_quality' => 'Environment Quality', 'facilities' => 'Facilities', 'mobility' => 'Mobility'] as $slug => $label)
                                <div>
                                    <label for="create-{{ $slug }}" class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">{{ $label }}</label>
                                    <input type="number" id="create-{{ $slug }}" name="{{ $slug }}" value="0" min="-10" max="10"
                                           :disabled="!qol"
                                           class="!bg-gray-50 dark:!bg-gray-700 !text-gray-900 dark:!text-white w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Form actions: Cancel closes the modal without saving; Create submits the form --}}
                    <div class="flex justify-between mt-6">
                        <button type="button" @click="closeCreate()"
                                class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-900 dark:text-white text-sm font-semibold rounded-lg transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition">
                            Create
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- EDIT MODAL ---------------------------------------------------------
             Shown when "editOpen" is true. The form action is bound dynamically
             to the id of the function stored in "editing".
             x-model binds each input to the matching property in "editing" so the
             fields are pre-filled with the current values when the modal opens. --}}
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

                        <div x-ref="editDialog" class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg mx-4 p-6 max-h-[90vh] overflow-y-auto">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Edit City Function</h2>

                {{-- PUT request via method spoofing — HTML forms only support GET/POST --}}
                <form method="POST" :action="'/city_functions/' + editing.id" enctype="multipart/form-data" class="dark:[color-scheme:dark]" @submit="submitEdit" @keydown.tab.prevent="trapEditFocus($event)">
                    @csrf
                    @method('PUT')

                    {{-- Image: shows the current image if one exists; leave the file input empty to keep it --}}
                    <div class="mb-4">
                        <label for="edit-image" class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Image</label>
                        <template x-if="editing.image_path">
                            <img :src="'/' + editing.image_path" :alt="editing.image_alt ?? editing.name" class="w-12 h-12 object-contain rounded mb-2">
                        </template>
                        <input type="file" id="edit-image" name="image" accept="image/*"
                               class="w-full text-sm text-gray-700 dark:text-white bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2
                                      file:mr-3 file:py-1 file:px-3 file:rounded file:border-0
                                      file:text-sm file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Leave empty to keep the current image.</p>
                    </div>

                    {{-- Optional: image alt text for screen readers (edit form) --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Image alt text (optional)</label>
                        <input type="text" name="image_alt" x-model="editing.image_alt"
                               class="!bg-gray-50 dark:!bg-gray-700 !text-gray-900 dark:!text-white w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Short description for screen readers">
                    </div>
                    {{-- Name pre-filled via x-model --}}
                    <div class="mb-4">
                        <label for="edit-name" class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Name <span class="text-red-400">*</span></label>
                        <input type="text" id="edit-name" name="name" required x-model="editing.name"
                               class="!bg-gray-50 dark:!bg-gray-700 !text-gray-900 dark:!text-white w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- Category pre-selected via x-model --}}
                    <div class="mb-4">
                        <label for="edit-category" class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Category <span class="text-red-400">*</span></label>
                        <select id="edit-category" name="category" required x-model="editing.category"
                                class="!bg-gray-50 dark:!bg-gray-700 !text-gray-900 dark:!text-white w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Description pre-filled via x-model --}}
                    <div class="mb-4">
                        <label for="edit-description" class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Description</label>
                        <textarea id="edit-description" name="description" rows="3" x-model="editing.description"
                                  class="!bg-gray-50 dark:!bg-gray-700 !text-gray-900 dark:!text-white w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>

                    {{-- QoL values: always visible in the edit form so the admin can update them at any time.
                         Each field is pre-filled with the function's stored score via x-model. --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-2">QoL Values</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="edit-safety" class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Safety</label>
                                <input type="number" id="edit-safety" name="safety" min="-10" max="10" x-model="editing.safety"
                                       class="!bg-gray-50 dark:!bg-gray-700 !text-gray-900 dark:!text-white w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="edit-recreation" class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Recreation</label>
                                <input type="number" id="edit-recreation" name="recreation" min="-10" max="10" x-model="editing.recreation"
                                       class="!bg-gray-50 dark:!bg-gray-700 !text-gray-900 dark:!text-white w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="edit-environment-quality" class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Environment Quality</label>
                                <input type="number" id="edit-environment-quality" name="environment_quality" min="-10" max="10" x-model="editing.environment_quality"
                                       class="!bg-gray-50 dark:!bg-gray-700 !text-gray-900 dark:!text-white w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="edit-facilities" class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Facilities</label>
                                <input type="number" id="edit-facilities" name="facilities" min="-10" max="10" x-model="editing.facilities"
                                       class="!bg-gray-50 dark:!bg-gray-700 !text-gray-900 dark:!text-white w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="edit-mobility" class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Mobility</label>
                                <input type="number" id="edit-mobility" name="mobility" min="-10" max="10" x-model="editing.mobility"
                                       class="!bg-gray-50 dark:!bg-gray-700 !text-gray-900 dark:!text-white w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    {{-- Adjacency Rules section --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-2">Adjacency Rules</label>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Define which functions must be neighbors or must not be neighbors.</p>

                        {{-- List existing conditions --}}
                        <div class="space-y-2 mb-4">
                            <template x-for="(condition, index) in editing.conditions" :key="index">
                                <div class="flex items-center justify-between bg-gray-100 dark:bg-gray-700 rounded-lg p-3">
                                    <div class="flex-1">
                                        <span x-text="getTargetName(condition.target_function_id)" class="font-medium text-gray-900 dark:text-white"></span>
                                        <span x-text="' (' + condition.type + ')'" :class="condition.type === 'forbidden' ? 'text-red-400' : 'text-green-400'" class="text-sm ml-2"></span>
                                    </div>
                                    <button type="button" @click="removeCondition(index)"
                                            class="px-3 py-2 bg-red-700 hover:bg-red-600 text-white text-xs font-semibold rounded transition">
                                        Remove
                                    </button>
                                </div>
                            </template>
                            <template x-if="editing.conditions.length === 0">
                                <p class="text-sm text-gray-500">No adjacency rules yet.</p>
                            </template>
                        </div>

                        {{-- Add new condition --}}
                        <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-3 space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Target Function</label>
                                <select x-model.number="newConditionTarget"
                                        class="!bg-white dark:!bg-gray-800 !text-gray-900 dark:!text-white w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select a function</option>
                                    <template x-for="fn in allFunctions.filter(f => f.id !== editing.id)" :key="fn.id">
                                        <option :value="fn.id" x-text="fn.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="flex gap-3">
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Adjacency Type</label>
                                    <select x-model="newConditionType"
                                            class="!bg-white dark:!bg-gray-800 !text-gray-900 dark:!text-white w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="required">Required</option>
                                        <option value="forbidden">Forbidden</option>
                                    </select>
                                </div>
                                <div class="flex items-end">
                                    <button type="button" @click="addCondition()"
                                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition">
                                        Add Rule
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Form actions: Cancel closes the modal without saving; Save Changes submits the PUT request --}}
                    <div class="flex justify-between mt-6">
                        <button type="button" @click="closeEdit()"
                                class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-900 dark:text-white text-sm font-semibold rounded-lg transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-yellow-600 hover:bg-yellow-500 text-white text-sm font-semibold rounded-lg transition">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- SUCCESS TOAST ------------------------------------------------------
             Only rendered when a flash message exists (after create, edit or delete).
             Auto-hides after 3 seconds via Alpine's x-init timeout. --}}
        @if(session('success'))
        <div x-data="autoHideToast"
             x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl shadow-lg text-white text-sm font-semibold bg-green-700">
            {{ session('success') }}
        </div>
        @endif

        {{-- CONFIRMATION MODAL --------------------------------------------------
             Non-blocking modal for delete confirmations. Doesn't interrupt workflow. --}}
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

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-sm mx-4 p-6"
                 @click.stop>
                <p class="text-gray-700 dark:text-gray-600 dark:text-gray-300 mb-6" x-text="message"></p>
                <div class="flex justify-end gap-3">
                    <button type="button"
                            @click="cancel()"
                            class="px-4 py-2 bg-gray-300 dark:bg-gray-700 hover:bg-gray-400 dark:hover:bg-gray-600 text-gray-900 dark:text-gray-100 font-semibold rounded-lg transition">
                        Cancel
                    </button>
                    <button type="button"
                            @click="confirm()"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition">
                        Delete
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
