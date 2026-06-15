<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl leading-tight text-gray-800 hc:text-white dark:text-gray-200">
            {{ __('Effect Management') }}
        </h1>
    </x-slot>

    <div class="py-12">
        <div class="px-4 sm:px-6 lg:px-8">

            {{-- Live region for announcing which effect is being edited --}}
            <div id="effect-live" class="sr-only" aria-live="polite"></div>

            @if($selectedFunctionId)
                <div class="mb-6 rounded-2xl border border-blue-200 hc:border-white bg-blue-50 hc:bg-black px-4 py-3 text-sm text-blue-900 hc:text-white dark:border-blue-900/40 dark:bg-blue-900/20 dark:text-blue-100">
                    Direct link active for function ID {{ $selectedFunctionId }}.
                </div>
            @endif

            {{-- Effects Table --}}
            <div class="overflow-hidden rounded-2xl bg-white hc:bg-black hc:border hc:border-white shadow-sm hc:shadow-none dark:bg-gray-800">

                {{-- Table Header --}}
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-gray-200 hc:border-white bg-blue-50 hc:bg-neutral-900 dark:border-gray-600 dark:bg-gray-700">
                                <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-800 hc:text-white dark:text-gray-100">
                                    Function
                                </th>
                                @foreach($categories as $index => $category)
                                    <th id="category-{{ $index }}" class="px-6 py-3 text-left text-sm font-semibold text-gray-800 hc:text-white text-center dark:text-gray-100">
                                        {{ $category }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        {{-- Table Body --}}
                        <tbody class="divide-y divide-gray-200 hc:divide-white dark:divide-gray-700">
                            @forelse($functions as $function)
                                <tr class="transition hover:bg-gray-50 hc:hover:bg-neutral-900 dark:hover:bg-gray-700/50">
                                    {{-- Function Name Cell --}}
                                    <td id="function-{{ $function->id }}" tabindex="0" aria-label="Function {{ $function->name }}" class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 hc:text-white dark:text-gray-100 @if($selectedFunctionId === $function->id) ring-2 ring-inset ring-blue-400 hc:ring-yellow-300 @endif @if($function->trashed()) opacity-70 @endif">
                                        <div class="flex items-center gap-3">
                                            @if($function->image_path)
                                                <img src="{{ asset($function->image_path) }}"
                                                     alt="{{ $function->name }}"
                                                     class="h-8 w-8 object-contain">
                                            @endif
                                            <div class="flex items-center gap-2">
                                                <span>{{ $function->name }}</span>
                                                @if($function->trashed())
                                                    <span class="inline-flex items-center rounded-full bg-gray-100 hc:bg-neutral-800 hc:border hc:border-white px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-gray-600 hc:text-white dark:bg-gray-700 dark:text-gray-300">
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
                                                <span class="inline-flex min-w-14 justify-center rounded-lg bg-gray-100 hc:bg-neutral-800 hc:text-white px-3 py-1 text-sm font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-300">
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
                                                            :class="'cursor-pointer rounded px-3 py-1 text-sm font-semibold transition hover:bg-gray-100 dark:hover:bg-gray-700 hc:hover:bg-neutral-900 ' + getColor()"
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
                                                                    :class="'w-16 rounded border px-2 py-1 text-sm focus:outline-none focus:ring-2 hc:bg-black hc:text-white hc:placeholder:text-neutral-400 dark:bg-gray-700 dark:text-white ' + (error ? 'border-red-500 focus:ring-red-500 dark:border-red-500 hc:border-red-400' : 'border-gray-300 dark:border-gray-600 focus:ring-blue-500 hc:border-white hc:focus:ring-yellow-400')">
                                                                <button
                                                                    @click="save()"
                                                                    :disabled="!isValid()"
                                                                    aria-label="Save {{ $function->name }} {{ $category }} value"
                                                                    :class="'rounded px-2 py-1 text-xs transition ' + (isValid() ? 'bg-green-600 hc:bg-green-400 hc:text-black text-white hover:bg-green-700' : 'cursor-not-allowed bg-gray-300 hc:bg-neutral-700 hc:text-white text-gray-500')">
                                                                    Save
                                                                </button>
                                                                <button
                                                                    @click="cancel()"
                                                                    aria-label="Cancel editing {{ $function->name }} {{ $category }} value"
                                                                    class="rounded bg-gray-400 hc:bg-neutral-700 hc:text-white hc:border hc:border-white px-2 py-1 text-xs text-white transition hover:bg-gray-500">
                                                                    Cancel
                                                                </button>
                                                            </div>
                                                            <template x-if="error">
                                                                <div class="rounded bg-red-50 hc:bg-black hc:border hc:border-red-400 px-2 py-1 text-xs font-medium text-red-600 hc:text-red-400 dark:bg-red-900/20 dark:text-red-400">
                                                                    <span x-text="error"></span>
                                                                </div>
                                                            </template>
                                                            <div class="text-xs text-gray-600 hc:text-white dark:text-gray-400">
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
                                    <td colspan="{{ count($categories) + 1 }}" class="px-6 py-12 text-center text-gray-500 hc:text-white dark:text-gray-400">
                                        {{ __('No functions found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>

            {{-- Legend --}}
            <div class="mt-6 rounded-2xl bg-blue-50 hc:bg-black hc:border hc:border-white p-6 dark:bg-gray-700/50">
                <h3 class="mb-3 text-sm font-semibold text-gray-800 hc:text-white dark:text-gray-100">Legend</h3>
                <div class="flex flex-wrap gap-6">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-green-600 hc:text-green-400 dark:text-green-400">+ values</span>
                        <span class="text-xs text-gray-600 hc:text-white dark:text-gray-400">Positive effect</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-red-600 hc:text-red-400 dark:text-red-400">- values</span>
                        <span class="text-xs text-gray-600 hc:text-white dark:text-gray-400">Negative effect</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-gray-500 hc:text-white dark:text-gray-400">0</span>
                        <span class="text-xs text-gray-600 hc:text-white dark:text-gray-400">No effect</span>
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
