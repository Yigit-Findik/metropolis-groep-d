<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('City Functions') }}
        </h2>
    </x-slot>

    {{-- Alpine.js state for the whole page:
         - open      : controls the create modal
         - editOpen  : controls the edit modal
         - editing   : holds the data of the function currently being edited
         - openEdit  : populates `editing` with the chosen function and opens the edit modal --}}
    <div class="py-12" x-data="{
        open: false,
        editOpen: false,
        editing: {},
        openEdit(fn) {
            this.editing = fn;
            this.editOpen = true;
        }
    }">
        <div class="px-4 sm:px-6 lg:px-8 flex flex-col items-center gap-4">

            {{-- Button that opens the create modal --}}
            <div class="w-fit self-end">
                <button @click="open = true"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition">
                    + Create Function
                </button>
            </div>

            {{-- Functions table — hidden when no functions exist yet --}}
            @if($cityFunctions->isEmpty())
                <p class="text-white">No city functions found.</p>
            @else
                <div class="bg-gray-800 rounded-2xl shadow-sm overflow-hidden w-fit">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead class="bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Image</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            @foreach($cityFunctions as $fn)
                                <tr>
                                    {{-- Image column: shows the function image or a placeholder dash --}}
                                    <td class="px-6 py-4">
                                        @if($fn->image_path)
                                            <img src="{{ asset($fn->image_path) }}"
                                                 alt="{{ $fn->name }}"
                                                 class="w-12 h-12 object-contain rounded">
                                        @else
                                            <div class="w-12 h-12 bg-gray-700 rounded flex items-center justify-center text-white text-xs">—</div>
                                        @endif
                                    </td>

                                    {{-- Name column --}}
                                    <td class="px-6 py-4 font-semibold text-white whitespace-nowrap">
                                        {{ $fn->name }}
                                    </td>

                                    {{-- Category column: displayed as a pill badge --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-block px-2 py-1 text-xs font-medium rounded-full bg-blue-900/40 text-white">
                                            {{ $fn->category ?? '—' }}
                                        </span>
                                    </td>

                                    {{-- Description column --}}
                                    <td class="px-6 py-4 text-sm text-white max-w-md">
                                        {{ $fn->description ?? '—' }}
                                    </td>

                                    {{-- Actions column: Edit opens the edit modal pre-filled with this row's data.
                                         Delete submits a soft-delete request after a browser confirm dialog. --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">

                                            {{-- Edit button: passes all current field values to the Alpine openEdit() method --}}
                                            <button type="button"
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
                                                        image_path: @js($fn->image_path ?? '')
                                                    })"
                                                    class="px-3 py-1 bg-yellow-600 hover:bg-yellow-500 text-white text-xs font-semibold rounded-lg transition">
                                                Edit
                                            </button>

                                            {{-- Delete form: uses method spoofing to send a DELETE request.
                                                 The function is soft-deleted so it can be recovered if needed. --}}
                                            <form action="/city_functions/{{ $fn->id }}" method="POST"
                                                  onsubmit="return confirm('Delete {{ addslashes($fn->name) }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="px-3 py-1 bg-red-700 hover:bg-red-600 text-white text-xs font-semibold rounded-lg transition">
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
             Shown when `open` is true. Clicking the dark backdrop closes the modal.
             enctype="multipart/form-data" is required for image file uploads. --}}
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60"
             @click.self="open = false">

            <div class="bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg mx-4 p-6 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-bold text-white mb-6">Create City Function</h3>

                <form method="POST" action="/city_functions" enctype="multipart/form-data" class="[color-scheme:dark]">
                    @csrf

                    {{-- Optional image upload --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Image</label>
                        <input type="file" name="image" accept="image/*"
                               class="w-full text-sm text-white bg-gray-700 rounded-lg border border-gray-600 px-3 py-2
                                      file:mr-3 file:py-1 file:px-3 file:rounded file:border-0
                                      file:text-sm file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                    </div>

                    {{-- Required: function name --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Name <span class="text-red-400">*</span></label>
                        <input type="text" name="name" required
                               class="!bg-gray-700 !text-white w-full rounded-lg border border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- Required: category dropdown populated from existing categories in the database --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Category <span class="text-red-400">*</span></label>
                        <select name="category" required
                                class="!bg-gray-700 !text-white w-full rounded-lg border border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="" disabled selected>Select a category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Required: short description of the function --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Description <span class="text-red-400">*</span></label>
                        <textarea name="description" required rows="3"
                                  class="!bg-gray-700 !text-white w-full rounded-lg border border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>

                    {{-- Optional QoL section: checkbox reveals the five score fields.
                         When unchecked, the inputs are disabled so the browser omits them from the
                         form submission and the controller defaults each value to 0. --}}
                    <div class="mb-4" x-data="{ qol: false }">
                        <div class="flex items-center gap-3 mb-2">
                            <input type="checkbox" id="create-qol-toggle" x-model="qol"
                                   class="w-4 h-4 rounded border-gray-600 bg-gray-700 text-blue-500 focus:ring-blue-500 cursor-pointer">
                            <label for="create-qol-toggle" class="text-sm text-gray-300 cursor-pointer select-none">
                                Do you want to insert the QoL values?
                            </label>
                        </div>
                        <div x-show="qol" x-transition class="grid grid-cols-2 gap-4">
                            @foreach(['safety' => 'Safety', 'recreation' => 'Recreation', 'environment_quality' => 'Environment Quality', 'facilities' => 'Facilities', 'mobility' => 'Mobility'] as $slug => $label)
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-1">{{ $label }}</label>
                                    <input type="number" name="{{ $slug }}" value="0" min="0"
                                           :disabled="!qol"
                                           class="!bg-gray-700 !text-white w-full rounded-lg border border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Form actions: Cancel closes the modal without saving; Create submits the form --}}
                    <div class="flex justify-between mt-6">
                        <button type="button" @click="open = false"
                                class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm font-semibold rounded-lg transition">
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
             Shown when `editOpen` is true. The form action is bound dynamically
             to the id of the function stored in `editing`.
             x-model binds each input to the matching property in `editing` so the
             fields are pre-filled with the current values when the modal opens. --}}
        <div x-show="editOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60"
             @click.self="editOpen = false">

            <div class="bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg mx-4 p-6 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-bold text-white mb-6">Edit City Function</h3>

                {{-- PUT request via method spoofing — HTML forms only support GET/POST --}}
                <form method="POST" :action="'/city_functions/' + editing.id" enctype="multipart/form-data" class="[color-scheme:dark]">
                    @csrf
                    @method('PUT')

                    {{-- Image: shows the current image if one exists; leave the file input empty to keep it --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Image</label>
                        <template x-if="editing.image_path">
                            <img :src="'/' + editing.image_path" class="w-12 h-12 object-contain rounded mb-2">
                        </template>
                        <input type="file" name="image" accept="image/*"
                               class="w-full text-sm text-white bg-gray-700 rounded-lg border border-gray-600 px-3 py-2
                                      file:mr-3 file:py-1 file:px-3 file:rounded file:border-0
                                      file:text-sm file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                        <p class="text-xs text-gray-400 mt-1">Leave empty to keep the current image.</p>
                    </div>

                    {{-- Name pre-filled via x-model --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Name <span class="text-red-400">*</span></label>
                        <input type="text" name="name" required x-model="editing.name"
                               class="!bg-gray-700 !text-white w-full rounded-lg border border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- Category pre-selected via x-model --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Category <span class="text-red-400">*</span></label>
                        <select name="category" required x-model="editing.category"
                                class="!bg-gray-700 !text-white w-full rounded-lg border border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Description pre-filled via x-model --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Description</label>
                        <textarea name="description" rows="3" x-model="editing.description"
                                  class="!bg-gray-700 !text-white w-full rounded-lg border border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>

                    {{-- QoL values: always visible in the edit form so the admin can update them at any time.
                         Each field is pre-filled with the function's stored score via x-model. --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-300 mb-2">QoL Values</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">Safety</label>
                                <input type="number" name="safety" min="0" x-model="editing.safety"
                                       class="!bg-gray-700 !text-white w-full rounded-lg border border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">Recreation</label>
                                <input type="number" name="recreation" min="0" x-model="editing.recreation"
                                       class="!bg-gray-700 !text-white w-full rounded-lg border border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">Environment Quality</label>
                                <input type="number" name="environment_quality" min="0" x-model="editing.environment_quality"
                                       class="!bg-gray-700 !text-white w-full rounded-lg border border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">Facilities</label>
                                <input type="number" name="facilities" min="0" x-model="editing.facilities"
                                       class="!bg-gray-700 !text-white w-full rounded-lg border border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">Mobility</label>
                                <input type="number" name="mobility" min="0" x-model="editing.mobility"
                                       class="!bg-gray-700 !text-white w-full rounded-lg border border-gray-600 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    {{-- Form actions: Cancel closes the modal without saving; Save Changes submits the PUT request --}}
                    <div class="flex justify-between mt-6">
                        <button type="button" @click="editOpen = false"
                                class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm font-semibold rounded-lg transition">
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
        <div x-data="{ show: true }"
             x-init="setTimeout(() => show = false, 3000)"
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

    </div>
</x-app-layout>
