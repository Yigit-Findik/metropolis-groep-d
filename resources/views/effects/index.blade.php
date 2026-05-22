<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Effect Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="px-4 sm:px-6 lg:px-8">

            {{-- Live region for announcing which effect is being edited --}}
            <div id="effect-live" class="sr-only" aria-live="polite"></div>

            @if($selectedFunctionId)
                <div class="mb-6 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900 dark:border-blue-900/40 dark:bg-blue-900/20 dark:text-blue-100">
                    Direct link active for function ID {{ $selectedFunctionId }}.
                </div>
            @endif

            {{-- Effects Table --}}
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-gray-800">
                
                {{-- Table Header --}}
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-gray-200 bg-blue-50 dark:border-gray-600 dark:bg-gray-700">
                                <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-800 dark:text-gray-100">
                                    Function
                                </th>
                                @foreach($categories as $index => $category)
                                    <th id="category-{{ $index }}" class="px-6 py-3 text-left text-sm font-semibold text-gray-800 text-center dark:text-gray-100">
                                        {{ $category }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        {{-- Table Body --}}
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($functions as $function)
                                <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    {{-- Function Name Cell --}}
                                    <td id="function-{{ $function->id }}" tabindex="0" aria-label="Function {{ $function->name }}" class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100 @if($selectedFunctionId === $function->id) ring-2 ring-inset ring-blue-400 @endif @if($function->trashed()) opacity-70 @endif">
                                        <div class="flex items-center gap-3">
                                            @if($function->image_path)
                                                <img src="{{ asset($function->image_path) }}"
                                                     alt="{{ $function->name }}"
                                                     class="h-8 w-8 object-contain">
                                            @endif
                                            <div class="flex items-center gap-2">
                                                <span>{{ $function->name }}</span>
                                                @if($function->trashed())
                                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                                        Archived
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Effect Value Cells --}}
                                    @foreach($categories as $index => $category)
                                        <td class="px-6 py-4 text-center">
                                            @if($function->trashed())
                                                <span class="inline-flex min-w-14 justify-center rounded-lg bg-gray-100 px-3 py-1 text-sm font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                                    {{ $function->{$category} > 0 ? '+' : '' }}{{ $function->{$category} }}
                                                </span>
                                            @else
                                                {{-- Inline editing keeps the table editable without a page reload. --}}
                                                <div x-data="effectEditor(
                                                    {{ $function->{$category} }},
                                                    '{{ route('effects.update', $function->id) }}',
                                                    '{{ csrf_token() }}',
                                                    @js($function->name),
                                                    @js($category)
                                                )" class="flex justify-center">
                                                        <template x-if="!editing">
                                                        <button
                                                            @click="startEditing($event)"
                                                            :class="'cursor-pointer rounded px-3 py-1 text-sm font-semibold transition hover:bg-gray-100 dark:hover:bg-gray-700 ' + getColor()"
                                                            title="Click to edit">
                                                            <span x-text="(value > 0 ? '+' : '') + value"></span>
                                                            <svg aria-hidden="true" class="inline ml-1 h-3 w-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                            </svg>
                                                        </button>
                                                    </template>

                                                    <template x-if="editing">
                                                        <div class="flex flex-col gap-2">
                                                            <div class="flex gap-2">
                                                                <input
                                                                    type="number"
                                                                    x-model.number="value"
                                                                    @input="validateInput()"
                                                                    @keydown.enter="save()"
                                                                    @keydown.escape="cancel()"
                                                                    min="-10"
                                                                    max="10"
                                                                    autofocus
                                                                    aria-label="Edit {{ $function->name }} {{ $category }} value"
                                                                    aria-labelledby="function-{{ $function->id }} category-{{ $index }}"
                                                                    :class="'w-16 rounded border px-2 py-1 text-sm focus:outline-none focus:ring-2 dark:bg-gray-700 dark:text-white ' + (error ? 'border-red-500 focus:ring-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600 focus:ring-blue-500')">
                                                                <button
                                                                    @click="save()"
                                                                    :disabled="!isValid()"
                                                                    :class="'rounded px-2 py-1 text-xs transition ' + (isValid() ? 'bg-green-600 text-white hover:bg-green-700' : 'cursor-not-allowed bg-gray-300 text-gray-500')">
                                                                    Save
                                                                </button>
                                                                <button
                                                                    @click="cancel()"
                                                                    class="rounded bg-gray-400 px-2 py-1 text-xs text-white transition hover:bg-gray-500">
                                                                    Cancel
                                                                </button>
                                                            </div>
                                                            <template x-if="error">
                                                                <div class="rounded bg-red-50 px-2 py-1 text-xs font-medium text-red-600 dark:bg-red-900/20 dark:text-red-400">
                                                                    <span x-text="error"></span>
                                                                </div>
                                                            </template>
                                                            <div class="text-xs text-gray-600 dark:text-gray-400">
                                                                Range: -10 to +10
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($categories) + 1 }}" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        {{ __('No functions found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>

            {{-- Legend --}}
            <div class="mt-6 rounded-2xl bg-blue-50 p-6 dark:bg-gray-700/50">
                <h3 class="mb-3 text-sm font-semibold text-gray-800 dark:text-gray-100">Legend</h3>
                <div class="flex flex-wrap gap-6">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-green-600 dark:text-green-400">+ values</span>
                        <span class="text-xs text-gray-600 dark:text-gray-400">Positive effect</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-red-600 dark:text-red-400">- values</span>
                        <span class="text-xs text-gray-600 dark:text-gray-400">Negative effect</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">0</span>
                        <span class="text-xs text-gray-600 dark:text-gray-400">No effect</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Effect Toast Notification --}}
    <div id="effect-toast"
            class="fixed bottom-6 right-6 z-50 hidden rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-300">
    </div>

</x-app-layout>
