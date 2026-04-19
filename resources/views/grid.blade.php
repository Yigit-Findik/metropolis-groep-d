<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Grid') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="px-4 sm:px-6 lg:px-8">

            {{-- Grid and library sit next to each other on desktop, above each other on mobile --}}
            <div class="flex flex-col lg:flex-row gap-6 lg:items-start">

                {{-- MAIN GRID SECTION: Contains the city grid and the removal zone below it --}}
                <div class="flex flex-col gap-4">

                {{-- CITY GRID --------------------------------------------------------------
                     "size" controls how many pixels wide each cell is on desktop.
                     "isDesktop" checks if the screen is wide enough for the zoom slider. --}}
                <div class="shrink-0 bg-blue-50 dark:bg-gray-700 rounded-2xl p-6 shadow-sm"
                     x-data="{
                         size: 96,
                         isDesktop: window.matchMedia('(min-width: 1024px)').matches,
                         init() {
                             const mq = window.matchMedia('(min-width: 1024px)');
                             mq.addEventListener('change', e => this.isDesktop = e.matches);
                         }
                     }">

                    {{-- Sticky so the title and zoom slider stay visible when scrolling down --}}
                    <div class="flex items-center gap-4 mb-4 sticky top-0 z-10 bg-blue-50 dark:bg-gray-700 py-2">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">City Grid</h3>

                        {{-- Zoom slider only shown on desktop --}}
                        <div class="hidden lg:flex items-center gap-3">
                            <label for="grid-size" class="text-sm text-gray-600 dark:text-gray-300">Zoom</label>
                            <input id="grid-size" type="range" min="64" max="128" step="16"
                                   x-model="size"
                                   class="w-28 accent-blue-500"
                                   aria-label="Adjust grid size">
                        </div>
                    </div>

                    {{-- Scrollable on desktop so the grid can be zoomed without breaking the layout --}}
                    <div class="lg:overflow-auto">

                        {{-- Always 4 columns. On mobile the columns shrink to fit the screen.
                             On desktop each column is a fixed number of pixels set by the zoom slider. --}}
                        <div class="grid grid-cols-4 gap-4 w-full"
                             :style="isDesktop ? `grid-template-columns: repeat(4, ${size}px)` : null"
                             data-city-grid>

                            @foreach ($gridCells->groupBy('row_index') as $rowNumber => $rowCells)
                                @foreach ($rowCells as $cell)

                                    {{-- Look up the matching city function by id so we can show its name and image --}}
                                    @php $fn = $cityFunctions->firstWhere('id', $cell->function_id); @endphp

                                    {{-- "is-occupied" or "is-empty" is read by app.js to update the preview panel
                                         draggable="true" allows occupied cells to be dragged off the grid (SIM.3 - Subtask 1) --}}
                                    <button
                                        type="button"
                                        class="grid-cell aspect-square bg-white dark:bg-gray-800 rounded-xl shadow-sm flex flex-col items-center justify-center p-4 cursor-pointer hover:shadow-md transition {{ filled($cell->function_id) ? 'is-occupied' : 'is-empty' }}"
                                        :style="isDesktop ? `width: ${size}px; height: ${size}px` : null"
                                        draggable="true"
                                        data-grid-cell
                                        data-cell-id="{{ $cell->id ?? '' }}"
                                        data-row="{{ $cell->row_index }}"
                                        data-column="{{ $cell->column_index }}"
                                        data-function="{{ $fn?->name ?? '' }}"
                                        data-function-id="{{ $cell->function_id ?? '' }}"
                                        aria-label="Row {{ $cell->row_index }}, column {{ $cell->column_index }}{{ filled($cell->function_id) ? ', occupied' : ', available' }}"
                                    >
                                        @if($fn?->image_path)
                                            {{-- Image scales with the zoom slider, fixed size on mobile --}}
                                            <img src="{{ asset($fn->image_path) }}"
                                                 alt="{{ $fn->name }}"
                                                 class="mb-1">
                                        @endif
                                        <span class="text-xs font-semibold text-center text-black">
                                            {{ $fn?->name ?? '' }}
                                        </span>
                                    </button>

                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- FUNCTION REMOVAL ZONE (SIM.3 - Subtask 2) ------------------------------------------
                     This is the "trash" or "remove" zone where users can drag functions
                     to remove them from the grid. It has a distinctive red/danger color
                     to indicate this is a destructive action. --}}
                <div class="w-full lg:w-auto bg-red-50 dark:bg-red-900/20 border-2 border-dashed border-red-300 dark:border-red-700 rounded-2xl p-6 shadow-sm"
                     id="removal-zone"
                     data-removal-zone>
                    <p class="text-sm text-red-700 dark:text-red-300 text-center font-semibold">
                        Drag here to remove
                    </p>
                </div>

                {{-- Close the main grid section div --}}
                </div>

                {{-- FUNCTION LIBRARY ------------------------------------------------
                     Fills all the space the grid doesn't use.
                     "active" holds the currently selected category filter. --}}
                <div class="flex-1 min-w-0 bg-blue-50 dark:bg-gray-700 rounded-2xl p-6 shadow-sm"
                     x-data="{ active: 'All' }">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4">Function Library</h3>

                    @if($cityFunctions->isEmpty())
                        <p class="text-gray-500 dark:text-gray-400">{{ __('No city functions found.') }}</p>
                    @else
                        {{-- Dropdown to filter which category of functions is shown --}}
                        <div class="mb-6">
                            <select x-model="active"
                                    class="bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="All">All categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}">{{ $category }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Cards fill the available width automatically, fitting as many columns as possible --}}
                        <div class="grid gap-4" style="grid-template-columns: repeat(auto-fill, minmax(96px, 1fr))">
                            @foreach($cityFunctions as $cityFunction)

                                {{-- Hide cards that don't match the selected category --}}
                                <div x-show="active === 'All' || active === '{{ $cityFunction->category }}'"
                                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm flex flex-col items-center justify-center p-4 cursor-pointer hover:shadow-md transition"
                                    draggable="true"
                                    data-function="{{ $cityFunction->name }}"
                                    data-function-id="{{ $cityFunction->id }}"
                                    data-image="{{ $cityFunction->image_path }}">
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
