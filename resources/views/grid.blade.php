<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h1 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Grid') }}
            </h1>
            <a href="{{ route('grid.export-pdf') }}"
               class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg shadow-sm transition"
               aria-label="Export grid report as PDF">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v2a2 2 0 002 2h14a2 2 0 002-2v-2M7 7l5-5 5 5" />
                </svg>
                Export PDF
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="px-4 sm:px-6 lg:px-8">

            {{-- Top bar: QoL Score Banner + Active Events panel --}}
            <div class="flex flex-col xl:flex-row gap-4 mb-6">

                {{-- QoL Score Banner --}}
                <div class="flex-1 min-w-0 bg-gray-200 dark:bg-gray-800 rounded-2xl shadow-sm px-4 sm:px-8 py-6" role="group" aria-label="Quality of life summary">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-[150px_repeat(5,minmax(120px,1fr))] gap-x-4 gap-y-3 items-start">

                        {{-- Total score: spans both columns on mobile so it stands alone --}}
                        <div class="col-span-2 sm:col-span-1 min-w-0">
                            <p class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase tracking-wide" aria-hidden="true">Total QoL</p>
                            <p tabindex="0" class="text-gray-800 dark:text-gray-100 text-4xl font-bold mt-1 focus:outline-none focus:ring-2 focus:ring-blue-400 rounded" id="qol-score-value" aria-live="polite" aria-atomic="true">—</p>
                            <p tabindex="0" class="text-gray-600 dark:text-gray-300 text-sm font-semibold mt-1 focus:outline-none focus:ring-2 focus:ring-blue-400 rounded" id="qol-score-label" aria-live="polite" aria-atomic="true">—</p>
                        </div>

                        @foreach(['safety' => 'Safety', 'recreation' => 'Recreation', 'environment_quality' => 'Environment Quality', 'facilities' => 'Facilities', 'mobility' => 'Mobility'] as $slug => $label)
                            <div>
                                <p class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase tracking-wide">{{ $label }}</p>
                                <p tabindex="0" class="text-gray-800 dark:text-gray-100 text-xl font-semibold mt-0.5 focus:outline-none focus:ring-2 focus:ring-blue-400 rounded" id="qol-{{ $slug }}" aria-live="polite" aria-atomic="true">—</p>
                            </div>
                        @endforeach

                        {{-- Bonus/Penalty/Events detail rows: only visible on large screens --}}
                        <div class="hidden lg:block lg:col-span-6"></div>
                        <div class="hidden lg:block text-gray-700 dark:text-gray-200 font-medium">Bonus:</div>
                        @foreach(['safety', 'recreation', 'environment_quality', 'facilities', 'mobility'] as $slug)
                            <div tabindex="0" class="hidden lg:block text-green-600 dark:text-green-400 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-400 rounded" id="qol-bonus-{{ $slug }}" aria-live="polite" aria-atomic="true">+0</div>
                        @endforeach

                        <div class="hidden lg:block text-gray-700 dark:text-gray-200 font-medium">Penalty:</div>
                        @foreach(['safety', 'recreation', 'environment_quality', 'facilities', 'mobility'] as $slug)
                            <div tabindex="0" class="hidden lg:block text-red-600 dark:text-red-400 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-400 rounded" id="qol-penalty-{{ $slug }}" aria-live="polite" aria-atomic="true">-0</div>
                        @endforeach

                        <div class="hidden lg:block text-gray-700 dark:text-gray-200 font-medium">Events:</div>
                        @foreach(['safety', 'recreation', 'environment_quality', 'facilities', 'mobility'] as $slug)
                            <div tabindex="0" class="hidden lg:block text-gray-500 dark:text-gray-400 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-400 rounded" id="qol-event-{{ $slug }}" aria-live="polite" aria-atomic="true">0</div>
                        @endforeach
                    </div>
                </div>

                {{-- Active Events Panel --}}
                <div class="w-full xl:w-80 shrink-0 self-start bg-gray-200 dark:bg-gray-800 rounded-2xl shadow-sm px-6 py-6"
                     x-data="activeEvents"
                     role="region"
                     aria-label="Currently active events">
                    <p class="text-gray-500 dark:text-gray-400 text-xs font-medium uppercase tracking-wide mb-3">Active Events</p>

                    <template x-if="events.length === 0">
                        <p class="text-gray-500 dark:text-gray-400 text-sm">No active events.</p>
                    </template>

                    <ul class="space-y-2 overflow-y-auto h-48 pr-1" aria-live="polite" aria-atomic="true">
                        <template x-for="event in events" :key="event.id">
                            <li class="rounded-xl px-3 py-2 border"
                                :class="event.is_active
                                    ? 'bg-green-100 dark:bg-green-900/40 border-green-200 dark:border-green-700'
                                    : 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-700'">
                                <p class="text-sm font-semibold break-words"
                                   :class="event.is_active ? 'text-green-800 dark:text-green-200' : 'text-yellow-800 dark:text-yellow-200'"
                                   x-text="event.name"></p>
                                <p class="text-xs mt-0.5"
                                   :class="event.is_active ? 'text-green-600 dark:text-green-400' : 'text-yellow-600 dark:text-yellow-400'"
                                   x-text="formatStatus(event)"></p>
                            </li>
                        </template>
                    </ul>
                </div>

            </div>{{-- end top bar --}}

            {{-- Grid and library sit next to each other on desktop, above each other on mobile --}}
            <div class="flex flex-col lg:flex-row gap-6 lg:items-start" x-data="gridZoom" :style="`--grid-size: ${effectiveGridSize}px`">

                {{-- MAIN GRID SECTION: Contains the city grid and the removal zone below it --}}
                 <section class="flex flex-col gap-4" aria-labelledby="city-grid-heading">

                 {{-- CITY GRID --------------------------------------------------------------
                     "size" controls how many pixels wide each cell is on desktop.
                     "isDesktop" checks if the screen is wide enough for the zoom slider. --}}
                 <div class="shrink-0 bg-blue-50 dark:bg-gray-700 rounded-2xl p-6 shadow-sm">

                    {{-- Sticky so the title and zoom slider stay visible when scrolling down --}}
                    <div class="flex justify-between items-center w-full">
                        <div class="flex items-center gap-4 mb-4 sticky top-0 z-10 bg-blue-50 dark:bg-gray-700 py-2">
                            <h2 id="city-grid-heading" class="text-lg font-bold text-gray-800 dark:text-gray-100">City Grid</h2>

                            {{-- Zoom slider only shown on desktop --}}
                            <div class="hidden lg:flex items-center gap-3">
                                <label for="grid-size" class="text-sm text-gray-600 dark:text-gray-300">Zoom</label>
                                <input id="grid-size" type="range" min="128" max="224" step="16"
                                    x-model="size"
                                    class="w-28 accent-blue-500"
                                    aria-label="Adjust grid size">
                            </div>
                        </div>
                        <div class="mb-4">
                            <button id="undo-button" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold shadow-sm">Undo Last Action</button>
                        </div>
                    </div>

                    {{-- Scrollable on desktop so the grid can be zoomed without breaking the layout --}}
                    <div class="lg:overflow-auto lg:p-1">

                        {{-- Always 4 columns. On mobile the columns shrink to fit the screen.
                             On desktop each column is a fixed number of pixels set by the zoom slider. --}}
                                <div class="grid grid-cols-4 gap-4 w-full"
                                    :style="isDesktop ? `grid-template-columns: repeat(4, ${size}px)` : null"
                                    data-city-grid tabindex="0">

                            @foreach ($gridCells->groupBy('row_index') as $rowNumber => $rowCells)
                                @foreach ($rowCells as $cell)

                                    {{-- Look up the matching city function by id so we can show its name and image --}}
                                    @php $fn = $cityFunctions->firstWhere('id', $cell->function_id); @endphp

                                    {{-- "is-occupied" or "is-empty" is read by app.js to update the preview panel
                                         draggable="true" allows occupied cells to be dragged off the grid (SIM.3 - Subtask 1) 
                                         Empty cells have a dashed border for accessibility (visual distinction without color alone) --}}
                                    <button
                                        type="button"
                                        tabindex="0"
                                        class="grid-cell border border-gray-200 dark:border-gray-700 aspect-square bg-white dark:bg-gray-800 rounded-xl shadow-sm flex flex-col items-center justify-center p-2 lg:p-4 cursor-pointer hover:shadow-md transition focus:outline-none focus:ring-2 focus:ring-blue-500 {{ filled($cell->function_id) ? 'is-occupied' : 'is-empty' }}"
                                        :style="isDesktop ? `width: ${size}px; height: ${size}px; border-width: calc(var(--grid-size) / 48);` : 'border-width: calc(var(--grid-size) / 48);'"
                                        draggable="true"
                                        data-grid-cell
                                        data-cell-id="{{ $cell->id ?? '' }}"
                                        data-row="{{ $cell->row_index }}"
                                        data-column="{{ $cell->column_index }}"
                                        data-function="{{ $fn?->name ?? '' }}"
                                        data-function-id="{{ $cell->function_id ?? '' }}"
                                        data-category="{{ $fn?->category ?? '' }}"
                                        data-safety="{{ $fn?->Safety ?? 0 }}"
                                        data-recreation="{{ $fn?->Recreation ?? 0 }}"
                                        data-environment-quality="{{ $fn?->{'Environment Quality'} ?? 0 }}"
                                        data-facilities="{{ $fn?->Facilities ?? 0 }}"
                                        data-mobility="{{ $fn?->Mobility ?? 0 }}"
                                        aria-label="Row {{ $cell->row_index }}, column {{ $cell->column_index }}{{ filled($cell->function_id) ? ', occupied by ' . ($fn?->name ?? 'a function') . ($fn?->category ? ', category ' . $fn->category : '') : ', available' }}"
                                    >
                                        @if($fn?->image_path)
                                            {{-- Image scales with the zoom slider, fixed size on mobile --}}
                                            <img src="{{ asset($fn->image_path) }}"
                                                 alt="{{ $fn->image_alt ?? $fn->name }}"
                                                 class="object-contain mb-1 flex-shrink-0"
                                                 style="width: calc(var(--grid-size) * 0.42); height: calc(var(--grid-size) * 0.42);">
                                        @endif
                                        <span class="font-semibold text-center text-black w-full leading-tight"
                                              style="font-size: max(6px, calc(var(--grid-size) * 0.07))">
                                            {{ $fn?->name ?? '' }}
                                        </span>
                                        {{-- Show "+" indicator for empty cells (accessibility: visual marker that doesn't rely on color) --}}
                                        @if(!filled($cell->function_id))
                                            <span class="text-gray-400 dark:text-gray-600 text-2xl font-light" aria-hidden="true">+</span>
                                        @endif
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
                <div class="w-full lg:w-auto bg-red-50 dark:bg-red-900/20 border-2 border-dashed border-red-300 dark:border-red-700 rounded-2xl p-6 shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500"
                     id="removal-zone"
                     role="button"
                     tabindex="0"
                     aria-label="Remove function — drop here or press Enter to remove the focused cell"
                     data-removal-zone>
                    <p class="text-sm text-red-700 dark:text-red-300 text-center font-semibold">
                        Drag here to remove
                    </p>
                </div>

                {{-- SIM.12 - Access Road Panel --}}
                <div class="w-full lg:w-auto bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-amber-800 dark:text-amber-200 uppercase tracking-wide">Access Roads</h3>
                        <div class="flex gap-2">
                            <button id="access-road-toggle"
                                    type="button"
                                    class="bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                                Place Road
                            </button>
                            <button id="access-road-cancel"
                                    type="button"
                                    class="hidden bg-gray-500 hover:bg-gray-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                                Cancel
                            </button>
                        </div>
                    </div>
                    <p id="access-road-status" class="text-xs text-amber-700 dark:text-amber-300 mb-3 min-h-[1rem]" aria-live="polite" aria-atomic="true"></p>
                    <div id="access-road-list" class="space-y-1"></div>
                </div>

                {{-- SIM.12.2 - Event Route Panel --}}
                <section class="w-full lg:w-auto bg-violet-50 dark:bg-violet-900/20 border border-violet-200 dark:border-violet-700 rounded-2xl p-6 shadow-sm"
                         aria-labelledby="event-route-heading">
                    <div class="flex items-center justify-between mb-3">
                        <h3 id="event-route-heading" class="text-sm font-semibold text-violet-800 dark:text-violet-200 uppercase tracking-wide">Event Routes</h3>
                        <div class="flex gap-2">
                            <button id="event-route-create"
                                    type="button"
                                    disabled
                                    aria-disabled="true"
                                    class="bg-violet-600 hover:bg-violet-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
                                Create Route
                            </button>
                            <button id="event-route-cancel"
                                    type="button"
                                    class="hidden bg-gray-500 hover:bg-gray-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                                Cancel
                            </button>
                        </div>
                    </div>

                    {{-- Road selector --}}
                    <div class="mb-3">
                        <label for="event-route-road-select" class="block text-xs font-medium text-violet-700 dark:text-violet-300 mb-1">
                            Access road to route from
                        </label>
                        <select id="event-route-road-select"
                                class="w-full text-xs rounded-lg border border-violet-300 dark:border-violet-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-violet-500">
                            <option value="" disabled selected>Select an access road…</option>
                        </select>
                    </div>

                    <p id="event-route-status"
                       class="text-xs text-violet-700 dark:text-violet-300 mb-3 min-h-[2rem]"
                       aria-live="polite"
                       aria-atomic="true"></p>

                    <div id="event-route-list" class="space-y-1"></div>

                    {{-- Legend --}}
                    <div class="mt-4 pt-3 border-t border-violet-200 dark:border-violet-700 space-y-1">
                        <p class="text-xs text-violet-600 dark:text-violet-400 font-medium">Legend</p>
                        <div class="flex items-center gap-2">
                            <span class="inline-block w-4 h-4 rounded border-2 border-violet-500 bg-violet-100 dark:bg-violet-800/40" aria-hidden="true"></span>
                            <span class="text-xs text-violet-700 dark:text-violet-300">Event location</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-block w-4 h-4 rounded border-2 border-violet-700 bg-violet-200 dark:bg-violet-700/60" aria-hidden="true"></span>
                            <span class="text-xs text-violet-700 dark:text-violet-300">Event route path</span>
                        </div>
                    </div>
                </section>

                {{-- Close the main grid section --}}
                </section>

                {{-- FUNCTION LIBRARY ------------------------------------------------
                     Fills all the space the grid doesn't use.
                     "active" holds the currently selected category filter. --}}
                <section class="flex-1 min-w-0 bg-blue-50 dark:bg-gray-700 rounded-2xl p-6 shadow-sm"
                        x-data="functionLibrary(@js($cityFunctions->map(fn ($cityFunction) => [
                            'name' => $cityFunction->name,
                            'category' => $cityFunction->category ?? '',
                        ])->values()))"
                         x-init="syncNoResultsAnnouncement(); $watch('searchTerm', () => syncNoResultsAnnouncement()); $watch('active', () => syncNoResultsAnnouncement())"
                         aria-labelledby="function-library-heading">
                    <h2 id="function-library-heading" class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4">Function Library</h2>

                    @if($cityFunctions->isEmpty())
                        <p class="text-gray-500 dark:text-gray-400">{{ __('No city functions found.') }}</p>
                    @else
                        <div class="mb-6 space-y-3">
                            <div>
                                <label for="function-search" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Search by name or category</label>
                                <input id="function-search" type="text" x-model.debounce.150ms="searchTerm" placeholder="Type a function name or category"
                                       class="w-full bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>

                            {{-- Dropdown to filter which category of functions is shown --}}
                            <div>
                                <label for="category-filter" class="sr-only">Filter by category</label>
                                <select id="category-filter" x-model="active"
                                        class="bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="All">All categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}">{{ $category }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <p x-cloak x-show="!hasVisibleFunctions()" class="text-gray-500 dark:text-gray-400 mb-4" aria-hidden="true">
                            No results found.
                        </p>

                        <div class="sr-only" role="alert" aria-live="assertive" aria-atomic="true" x-text="noResultsAnnouncement"></div>

                        {{-- Cards fill the available width automatically, fitting as many columns as possible --}}
                        <div class="grid gap-4" style="grid-template-columns: repeat(auto-fill, minmax(96px, 1fr))">
                            @foreach($cityFunctions as $cityFunction)

                                {{-- Hide cards that don't match the selected category --}}
                                <button
                                    type="button"
                                    data-library-card
                                    x-show="isVisible(@js($cityFunction->name), @js($cityFunction->category ?? ''))"
                                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm flex flex-col items-center justify-center p-4 cursor-pointer hover:shadow-md transition focus:outline-none focus:ring-2 focus:ring-blue-500 border border-gray-200 dark:border-gray-700"
                                    :style="`border-width: calc(var(--grid-size) / 48);`"
                                    draggable="true"
                                    data-function="{{ $cityFunction->name }}"
                                    data-function-id="{{ $cityFunction->id }}"
                                    data-category="{{ $cityFunction->category ?? '' }}"
                                    data-image="{{ $cityFunction->image_path }}"
                                    data-image-alt="{{ $cityFunction->image_alt ?? $cityFunction->name }}"
                                    data-qol-score="{{ ($cityFunction->Safety ?? 0) + ($cityFunction->Recreation ?? 0) + ($cityFunction->{'Environment Quality'} ?? 0) + ($cityFunction->Facilities ?? 0) + ($cityFunction->Mobility ?? 0) }}"
                                    data-safety="{{ $cityFunction->Safety ?? 0 }}"
                                    data-recreation="{{ $cityFunction->Recreation ?? 0 }}"
                                    data-environment-quality="{{ $cityFunction->{'Environment Quality'} ?? 0 }}"
                                    data-facilities="{{ $cityFunction->Facilities ?? 0 }}"
                                    data-mobility="{{ $cityFunction->Mobility ?? 0 }}"
                                    aria-label="Drag {{ $cityFunction->name }} onto the grid"
                                    data-conditions='@json($cityFunction->functionConditions ?? [])'
                                    @mouseenter="highlightCells({{ $cityFunction->id }}, $event.target); $dispatch('show-library-preview', { card: $event.target })"
                                    @mouseleave="clearHighlights(); $dispatch('hide-library-preview')"
                                    @focus="$dispatch('show-library-preview', { card: $event.target })"
                                    @blur="$dispatch('hide-library-preview')"
                                    @keydown.escape="$dispatch('hide-library-preview')">
                                    @if($cityFunction->image_path)
                                        <img src="{{ asset($cityFunction->image_path) }}"
                                             alt="{{ $cityFunction->name }}"
                                             class="w-16 h-16 object-contain mb-2">
                                    @endif
                                    <span class="text-xs font-semibold text-center text-gray-700 dark:text-white break-words w-full leading-tight line-clamp-2">{{ $cityFunction->name }}</span>
                                </button>

                            @endforeach
                        </div>
                    @endif
                </section>

            </div>
        </div>
    </div>

    {{-- SIM.12 - Access road visual styles --}}
    {{-- SIM.12.2 - Event route visual styles --}}
    <style>
        /* Access road cells — amber */
        .road-cell {
            background-color: rgba(245, 158, 11, 0.18) !important;
            border-color: rgb(245, 158, 11) !important;
        }
        .road-start-selected {
            outline: 3px solid rgb(34, 197, 94) !important;
            outline-offset: -3px;
        }
        .road-selection-mode [data-grid-cell] {
            cursor: crosshair;
        }
        .road-selection-mode [data-grid-cell]:hover {
            outline: 3px solid rgb(245, 158, 11);
            outline-offset: -3px;
        }

        /* Event location cells — violet outline + light background */
        .event-location-cell {
            background-color: rgba(139, 92, 246, 0.12) !important;
            border-color: rgb(139, 92, 246) !important;
            border-style: dashed !important;
        }

        /* Event route path cells — solid violet, stronger fill */
        .event-route-cell {
            background-color: rgba(109, 40, 217, 0.22) !important;
            border-color: rgb(109, 40, 217) !important;
        }

        /* When a cell is both a road and a route, route takes visual precedence */
        .event-route-cell.road-cell {
            background-color: rgba(109, 40, 217, 0.30) !important;
            border-color: rgb(109, 40, 217) !important;
        }

        /* Event route selection mode cursor and hover */
        .event-route-selection-mode [data-grid-cell] {
            cursor: default;
        }
        .event-route-selection-mode [data-grid-cell].event-location-cell {
            cursor: pointer;
        }
        .event-route-selection-mode [data-grid-cell].event-location-cell:hover,
        .event-route-selection-mode [data-grid-cell].event-location-cell:focus {
            outline: 3px solid rgb(139, 92, 246);
            outline-offset: -3px;
        }
    </style>

    {{-- QoL Toast Notification --}}
        <div id="grid-a11y-announcer" aria-live="polite" aria-atomic="true" role="status" class="sr-only"></div>

        <div id="qol-toast"
         class="fixed bottom-6 right-6 z-50 hidden px-5 py-3 rounded-xl shadow-lg text-white text-sm font-semibold transition-all duration-300">
    </div>

</x-app-layout>
