<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Grid') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-6 lg:items-start">

                {{-- City Grid --}}
                <div class="shrink-0 bg-blue-50 dark:bg-gray-700 rounded-2xl p-6 shadow-sm"
                     x-data="{
                         size: 96,
                         isDesktop: window.matchMedia('(min-width: 1024px)').matches,
                         init() {
                             const mq = window.matchMedia('(min-width: 1024px)');
                             mq.addEventListener('change', e => this.isDesktop = e.matches);
                         }
                     }">
                    <div class="flex items-center gap-4 mb-4 sticky top-0 z-10 bg-blue-50 dark:bg-gray-700 py-2">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">City Grid</h3>
                        <div class="hidden lg:flex items-center gap-3">
                            <label for="grid-size" class="text-sm text-gray-600 dark:text-gray-300">Zoom</label>
                            <input id="grid-size" type="range" min="64" max="128" step="16"
                                   x-model="size"
                                   class="w-28 accent-blue-500"
                                   aria-label="Adjust grid size">
                        </div>
                    </div>
                    <div class="lg:overflow-auto">
                        <div class="grid grid-cols-4 gap-4 w-full"
                             :style="isDesktop ? `grid-template-columns: repeat(4, ${size}px)` : null"
                             data-city-grid>
                            @foreach ($gridCells->groupBy('row_index') as $rowNumber => $rowCells)
                                @foreach ($rowCells as $cell)
                                    <button
                                        type="button"
                                        class="grid-cell aspect-square bg-white dark:bg-gray-800 rounded-xl shadow-sm flex flex-col items-center justify-center p-4 cursor-pointer hover:shadow-md transition {{ filled($cell->function_name) ? 'is-occupied' : 'is-empty' }}"
                                        :style="isDesktop ? `width: ${size}px; height: ${size}px` : null"
                                        data-grid-cell
                                        data-row="{{ $cell->row_index }}"
                                        data-column="{{ $cell->column_index }}"
                                        data-function="{{ $cell->function_name ?? '' }}"
                                        aria-label="Row {{ $cell->row_index }}, column {{ $cell->column_index }}{{ filled($cell->function_name) ? ', occupied' : ', available' }}"
                                        aria-pressed="false"
                                    >
                                        <span class="text-xs font-semibold text-center text-gray-500 dark:text-gray-400">
                                            {{ $cell->function_name ?? '' }}
                                        </span>
                                    </button>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Function Library --}}
                <div class="flex-1 min-w-0 bg-blue-50 dark:bg-gray-700 rounded-2xl p-6 shadow-sm"
                     x-data="{ active: 'All' }">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4">Function Library</h3>

                    @if($cityFunctions->isEmpty())
                        <p class="text-gray-500 dark:text-gray-400">{{ __('No city functions found.') }}</p>
                    @else
                        {{-- Category filter dropdown --}}
                        <div class="mb-6">
                            <select x-model="active"
                                    class="bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="All">All categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}">{{ $category }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- City functions grid --}}
                        <div class="grid gap-4" style="grid-template-columns: repeat(auto-fill, minmax(96px, 1fr))">
                            @foreach($cityFunctions as $cityFunction)
                                <div x-show="active === 'All' || active === '{{ $cityFunction->category }}'"
                                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm flex flex-col items-center justify-center p-4 cursor-pointer hover:shadow-md transition">
                                    @if($cityFunction->image_path)
                                        <img src="{{ asset($cityFunction->image_path) }}"
                                             alt="{{ $cityFunction->name }}"
                                             class="w-16 h-16 object-contain mb-2">
                                    @endif
                                    <span class="text-xs font-semibold text-center text-gray-700 dark:text-white">{{ $cityFunction->name }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
