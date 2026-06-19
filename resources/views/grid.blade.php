<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center w-full gap-2">
            <h1 class="font-semibold text-xl text-gray-800 hc:text-white dark:text-gray-200 leading-tight">
                {{ __('Grid') }}
            </h1>
            <a href="{{ route('grid.export-pdf') }}"
               class="inline-flex items-center gap-2 bg-blue-600 hc:bg-yellow-300 hc:text-black hover:bg-blue-700 hc:hover:bg-yellow-200 text-white text-sm font-semibold px-4 py-2 rounded-lg shadow-sm hc:shadow-none transition"
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
            <div class="flex flex-col lg:flex-row gap-4 mb-6">

                {{-- QoL Score Banner --}}
                <div class="flex-1 min-w-0 overflow-x-auto bg-gray-200 hc:bg-black hc:border hc:border-white dark:bg-gray-800 rounded-2xl shadow-sm hc:shadow-none px-4 sm:px-8 py-6" role="group" aria-label="Quality of life summary">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-[150px_repeat(5,minmax(120px,1fr))] gap-x-4 gap-y-3 items-start">

                        <div class="col-span-2 sm:col-span-1 min-w-0">
                            <p class="text-gray-500 hc:text-white dark:text-gray-400 text-xs font-medium uppercase tracking-wide" aria-hidden="true">Total QoL</p>
                            <p tabindex="0" class="text-gray-800 hc:text-white dark:text-gray-100 text-4xl font-bold mt-1 focus:outline-none focus:ring-2 focus:ring-blue-400 hc:focus:ring-yellow-400 rounded" id="qol-score-value" aria-live="polite" aria-atomic="true" aria-label="Total quality of life score">—</p>
                            <p tabindex="0" class="text-gray-600 hc:text-white dark:text-gray-300 text-sm font-semibold mt-1 focus:outline-none focus:ring-2 focus:ring-blue-400 hc:focus:ring-yellow-400 rounded" id="qol-score-label">—</p>
                        </div>

                        @foreach(['safety' => 'Safety', 'recreation' => 'Recreation', 'environment_quality' => 'Environment Quality', 'facilities' => 'Facilities', 'mobility' => 'Mobility'] as $slug => $label)
                            <div>
                                <p class="text-gray-500 hc:text-white dark:text-gray-400 text-xs font-medium uppercase tracking-wide">{{ $label }}</p>
                                <p tabindex="0" class="text-gray-800 hc:text-white dark:text-gray-100 text-xl font-semibold mt-0.5 focus:outline-none focus:ring-2 focus:ring-blue-400 hc:focus:ring-yellow-400 rounded" id="qol-{{ $slug }}">—</p>
                            </div>
                        @endforeach

                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-300 hc:border-white dark:border-gray-600 space-y-3 text-sm">
                        <div>
                            <p class="font-medium text-gray-700 hc:text-white dark:text-gray-200 mb-1">Bonus</p>
                            <div class="flex flex-wrap gap-x-4 gap-y-1">
                                @foreach(['safety' => 'Safety', 'recreation' => 'Recreation', 'environment_quality' => 'Env. Quality', 'facilities' => 'Facilities', 'mobility' => 'Mobility'] as $slug => $dimLabel)
                                    <span class="text-xs text-gray-500 hc:text-white dark:text-gray-400">{{ $dimLabel }}: <span tabindex="0" class="text-green-600 hc:text-green-400 dark:text-green-400 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-400 hc:focus:ring-yellow-400 rounded" id="qol-bonus-{{ $slug }}">+0</span></span>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <p class="font-medium text-gray-700 hc:text-white dark:text-gray-200 mb-1">Penalty</p>
                            <div class="flex flex-wrap gap-x-4 gap-y-1">
                                @foreach(['safety' => 'Safety', 'recreation' => 'Recreation', 'environment_quality' => 'Env. Quality', 'facilities' => 'Facilities', 'mobility' => 'Mobility'] as $slug => $dimLabel)
                                    <span class="text-xs text-gray-500 hc:text-white dark:text-gray-400">{{ $dimLabel }}: <span tabindex="0" class="text-red-600 hc:text-red-400 dark:text-red-400 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-400 hc:focus:ring-yellow-400 rounded" id="qol-penalty-{{ $slug }}">-0</span></span>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <p class="font-medium text-gray-700 hc:text-white dark:text-gray-200 mb-1">Events</p>
                            <div class="flex flex-wrap gap-x-4 gap-y-1">
                                @foreach(['safety' => 'Safety', 'recreation' => 'Recreation', 'environment_quality' => 'Env. Quality', 'facilities' => 'Facilities', 'mobility' => 'Mobility'] as $slug => $dimLabel)
                                    <span class="text-xs text-gray-500 hc:text-white dark:text-gray-400">{{ $dimLabel }}: <span tabindex="0" class="text-gray-500 hc:text-white dark:text-gray-400 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-400 hc:focus:ring-yellow-400 rounded" id="qol-event-{{ $slug }}">0</span></span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Active Events Panel --}}
                <div class="w-full lg:w-80 shrink-0 self-start bg-gray-200 hc:bg-black hc:border hc:border-white dark:bg-gray-800 rounded-2xl shadow-sm hc:shadow-none px-6 py-6"
                     x-data="activeEvents"
                     role="region"
                     aria-label="Currently active events">
                    <p class="text-gray-500 hc:text-white dark:text-gray-400 text-xs font-medium uppercase tracking-wide mb-3">Active Events</p>

                    <template x-if="events.length === 0">
                        <p class="text-gray-500 hc:text-white dark:text-gray-400 text-sm">No active events.</p>
                    </template>

                    <ul class="space-y-2 overflow-y-auto h-48 pr-1" aria-label="Active events">
                        <template x-for="event in events" :key="event.id">
                            <li class="rounded-xl px-3 py-2 border focus:outline-none focus:ring-2 focus:ring-blue-400 hc:focus:ring-yellow-400"
                                tabindex="0"
                                :class="event.is_active
                                    ? 'bg-green-100 hc:bg-black hc:border-green-400 dark:bg-green-900/40 border-green-200 dark:border-green-700'
                                    : 'bg-yellow-50 hc:bg-black hc:border-yellow-300 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-700'"
                                :aria-label="event.name + ', ' + formatStatus(event)">
                                <p class="text-sm font-semibold break-words"
                                   :class="event.is_active ? 'text-green-800 hc:text-green-400 dark:text-green-200' : 'text-yellow-800 hc:text-yellow-300 dark:text-yellow-200'"
                                   x-text="event.name"></p>
                                <template x-if="progressPercent(event) !== null">
                                    <div class="mt-1.5 h-1.5 rounded-full overflow-hidden"
                                         :class="event.is_active ? 'bg-green-200 dark:bg-green-800' : 'bg-yellow-200 dark:bg-yellow-800'">
                                        <div class="h-full rounded-full transition-all duration-1000"
                                             :class="event.is_active ? 'bg-green-500 dark:bg-green-400' : 'bg-yellow-500 dark:bg-yellow-400'"
                                             :style="'width: ' + progressPercent(event) + '%'"></div>
                                    </div>
                                </template>
                                <p class="text-xs mt-0.5"
                                   :class="event.is_active ? 'text-green-600 hc:text-green-400 dark:text-green-400' : 'text-yellow-600 hc:text-yellow-300 dark:text-yellow-400'"
                                   x-text="formatStatus(event)"></p>
                            </li>
                        </template>
                    </ul>
                </div>

            </div>{{-- end top bar --}}

            {{-- Simulation speed controls --}}
            <div class="w-full bg-gray-800 hc:bg-black hc:border hc:border-white rounded-2xl shadow-sm hc:shadow-none px-3 sm:px-6 py-4 mb-6"
                 x-data="simulationControls">

                <div class="flex flex-wrap items-center gap-x-4 gap-y-2">

                    <span class="text-gray-400 hc:text-white text-xs font-semibold uppercase tracking-wide">Simulation</span>

                    <button id="simulation-play-pause"
                            @click="paused ? play() : pause()"
                            :aria-label="paused ? 'Play simulation' : 'Pause simulation'"
                            class="px-4 py-2 rounded-lg text-sm font-semibold transition text-white hc:text-black"
                            :class="paused ? 'bg-green-600 hc:bg-green-400 hover:bg-green-500 hc:hover:bg-green-300' : 'bg-yellow-500 hc:bg-yellow-300 hover:bg-yellow-400 hc:hover:bg-yellow-200'">
                        <span x-text="paused ? 'Play' : 'Pause'">Play</span>
                    </button>

                    <div class="flex items-center gap-2" role="radiogroup" aria-label="Simulation speed">
                        @foreach([1, 2, 5] as $spd)
                            <button @click="setMultiplier({{ $spd }})"
                                    role="radio"
                                    :aria-checked="multiplier === {{ $spd }}"
                                    :aria-label="multiplier === {{ $spd }} ? 'Change simulation speed to {{ $spd }}x, selected' : 'Change simulation speed to {{ $spd }}x'"
                                    class="px-3 py-1.5 rounded-lg text-sm font-semibold transition"
                                    :class="multiplier === {{ $spd }} ? 'bg-blue-600 hc:bg-yellow-300 hc:text-black text-white' : 'bg-gray-700 hc:bg-neutral-900 hc:border hc:border-white text-gray-300 hc:text-white hover:bg-gray-600 hc:hover:bg-neutral-800'">
                                <span aria-hidden="true">{{ $spd }}x</span>
                            </button>
                        @endforeach
                    </div>

                    <div class="border-l border-gray-600 h-6" aria-hidden="true"></div>

                    {{-- Time unit: 1 real second = 1 sim Sec / Min / Hr / Day --}}
                    <div class="flex items-center gap-2" role="radiogroup" aria-label="Simulation time unit">
                        @foreach(['Sec' => 1, 'Min' => 60, 'Hr' => 3600, 'Day' => 86400] as $label => $secs)
                            <button @click="setUnit({{ $secs }})"
                                    role="radio"
                                    :aria-checked="unitSeconds === {{ $secs }}"
                                    :aria-label="unitSeconds === {{ $secs }} ? '1 second equals 1 simulation {{ $label === 'Sec' ? 'second' : ($label === 'Min' ? 'minute' : ($label === 'Hr' ? 'hour' : 'day')) }}, selected' : '1 second equals 1 simulation {{ $label === 'Sec' ? 'second' : ($label === 'Min' ? 'minute' : ($label === 'Hr' ? 'hour' : 'day')) }}'"
                                    class="px-3 py-1.5 rounded-lg text-sm font-semibold transition"
                                    :class="unitSeconds === {{ $secs }} ? 'bg-violet-600 hc:bg-yellow-300 hc:text-black text-white' : 'bg-gray-700 hc:bg-neutral-900 hc:border hc:border-white text-gray-300 hc:text-white hover:bg-gray-600 hc:hover:bg-neutral-800'">
                                <span aria-hidden="true">{{ $label }}</span>
                            </button>
                        @endforeach
                    </div>

                    <div class="border-l border-gray-600 h-6" aria-hidden="true"></div>

                    {{-- Skip forward --}}
                    <div class="flex items-center gap-2" role="group" aria-label="Skip simulation time forward">
                        <span class="text-gray-400 hc:text-white text-xs font-semibold uppercase tracking-wide" aria-hidden="true">Skip</span>
                        <input type="number"
                               x-model.number="skipAmount"
                               min="1"
                               class="w-14 px-3 py-1.5 text-sm font-semibold rounded-lg bg-gray-700 hc:bg-neutral-900 hc:border hc:border-white text-gray-100 hc:text-white border-0 ring-1 ring-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-400 hc:focus:ring-yellow-400"
                               aria-label="Skip amount">
                        <select x-model.number="skipUnit"
                                class="px-3 py-1.5 text-sm font-semibold rounded-lg bg-gray-700 hc:bg-neutral-900 hc:border hc:border-white text-gray-300 hc:text-white border-0 ring-1 ring-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-400 hc:focus:ring-yellow-400 cursor-pointer"
                                aria-label="Skip unit">
                            <option value="1" aria-label="Seconds">Sec</option>
                            <option value="60" aria-label="Minutes">Min</option>
                            <option value="3600" aria-label="Hours">Hr</option>
                            <option value="86400" aria-label="Days">Day</option>
                        </select>
                        <button @click="skip()"
                                class="px-3 py-1.5 rounded-lg text-sm font-semibold transition bg-blue-600 hc:bg-yellow-300 hc:text-black hover:bg-blue-500 hc:hover:bg-yellow-200 text-white">
                            Skip
                        </button>
                    </div>

                    {{-- Current speed display --}}
                    <div class="flex items-center gap-4 text-sm sm:ml-auto">
                        <div class="flex items-center gap-2">
                            <span class="text-gray-400 hc:text-white">Speed:</span>
                            <span id="simulation-current-speed"
                                  class="text-white hc:text-yellow-300 font-bold"
                                  aria-live="polite"
                                  x-text="speed + 'x'">1x</span>
                            <span id="simulation-status"
                                  class="text-gray-400 hc:text-white"
                                  aria-live="polite"
                                  x-text="paused ? '(paused)' : '(running)'">(paused)</span>
                        </div>
                        <div class="border-l border-gray-600 pl-4"
                             tabindex="0"
                             :aria-label="'Simulation time: ' + simTime">
                            <span class="text-gray-300 text-xs font-mono font-semibold tabular-nums"
                                  aria-hidden="true"
                                  x-text="simTime">00:00</span>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Grid and library --}}
            <div class="flex flex-col lg:flex-row gap-6 lg:items-start" x-data="gridZoom" :style="`--grid-size: ${effectiveGridSize}px`">

                <section class="flex flex-col gap-4 w-full min-w-0 lg:w-auto" aria-labelledby="city-grid-heading">

                 <div class="shrink-0 bg-blue-50 hc:bg-black hc:border hc:border-white dark:bg-gray-700 rounded-2xl p-3 sm:p-6 shadow-sm hc:shadow-none">

                    <div class="flex flex-wrap justify-between items-center w-full gap-y-2">
                        <div class="flex items-center gap-4 mb-4 sticky top-0 z-10 bg-blue-50 hc:bg-black dark:bg-gray-700 py-2">
                            <h2 id="city-grid-heading" class="text-lg font-bold text-gray-800 hc:text-white dark:text-gray-100">City Grid</h2>

                            <div class="hidden lg:flex items-center gap-3">
                                <label for="grid-size" class="text-sm text-gray-600 hc:text-white dark:text-gray-300">Zoom</label>
                                <input id="grid-size" type="range" min="128" max="224" step="16"
                                    x-model="size"
                                    class="w-28 accent-blue-500 hc:accent-yellow-300"
                                    aria-label="Adjust grid size">
                            </div>
                        </div>
                        <div class="mb-4 flex items-center gap-2">
                            @if($userRole === 'Policy maker' || $userRole === 'Administrator')
                                <button id="approve-all-button"
                                        aria-label="Approve the entire grid"
                                        class="flex items-center gap-1 bg-green-600 hc:bg-green-400 hc:text-black hover:bg-green-700 hc:hover:bg-green-300 text-white px-4 py-2 rounded-lg font-semibold shadow-sm hc:shadow-none">
                                    <span class="material-symbols-outlined" style="font-size:1.1rem" aria-hidden="true">lock</span>
                                    <span>Approve All</span>
                                </button>
                                <button id="revoke-all-button"
                                        aria-label="Disapprove the entire grid"
                                        class="flex items-center gap-1 bg-red-600 hc:bg-red-400 hc:text-black hover:bg-red-700 hc:hover:bg-red-300 text-white px-4 py-2 rounded-lg font-semibold shadow-sm hc:shadow-none">
                                    <span class="material-symbols-outlined" style="font-size:1.1rem" aria-hidden="true">lock_open</span>
                                    <span>Disapprove All</span>
                                </button>
                            @endif
                            @if($userRole !== 'Policy maker')
                                <button id="undo-button" class="bg-blue-600 hc:bg-yellow-300 hc:text-black hover:bg-blue-700 hc:hover:bg-yellow-200 text-white px-4 py-2 rounded-lg font-semibold shadow-sm hc:shadow-none">Undo Last Action</button>
                            @endif
                        </div>
                    </div>

                    <div class="lg:overflow-auto lg:p-1">
                        <div class="grid grid-cols-4 gap-1 sm:gap-4 w-full"
                            :style="isDesktop ? `grid-template-columns: repeat(4, ${size}px)` : null"
                            data-city-grid tabindex="0">

                            @foreach ($gridCells->groupBy('row_index') as $rowNumber => $rowCells)
                                @foreach ($rowCells as $cell)

                                    @php $fn = $cityFunctions->firstWhere('id', $cell->function_id); @endphp

                                    <div class="relative">
                                    <button
                                        type="button"
                                        tabindex="0"
                                        class="grid-cell w-full relative border aspect-square bg-white hc:bg-black dark:bg-gray-800 rounded-xl shadow-sm hc:shadow-none flex flex-col items-center justify-center p-2 lg:p-4 cursor-pointer hover:shadow-md hc:hover:shadow-none transition focus:outline-none focus:ring-2 focus:ring-blue-500 hc:focus:ring-yellow-400 {{ filled($cell->function_id) ? 'is-occupied' : 'is-empty' }} {{ ($cell->is_approved ?? false) ? 'is-approved border-green-600 hc:border-green-400 dark:border-green-500' : 'border-gray-200 hc:border-white dark:border-gray-700' }}"
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
                                        data-approved="{{ ($cell->is_approved ?? false) ? 'true' : 'false' }}"
                                        aria-label="Row {{ $cell->row_index }}, column {{ $cell->column_index }}{{ filled($cell->function_id) ? ', occupied by ' . ($fn?->name ?? 'a function') . ($fn?->category ? ', category ' . $fn->category : '') : ', available' }}{{ ($cell->is_approved ?? false) ? ', approved' : '' }}"
                                    >
                                        @if($fn?->image_path)
                                            <img src="{{ asset($fn->image_path) }}"
                                                 alt="{{ $fn->image_alt ?? $fn->name }}"
                                                 class="object-contain mb-1 flex-shrink-0"
                                                 style="width: calc(var(--grid-size) * 0.42); height: calc(var(--grid-size) * 0.42);">
                                        @endif
                                        <span class="font-semibold text-center text-black hc:text-white w-full leading-tight"
                                              style="font-size: max(6px, calc(var(--grid-size) * 0.07))">
                                            {{ $fn?->name ?? '' }}
                                        </span>
                                        @if(!filled($cell->function_id))
                                            <span class="text-gray-400 hc:text-white dark:text-gray-600 text-2xl font-light" aria-hidden="true">+</span>
                                        @endif
                                    </button>

                                    @if($userRole === 'Policy maker' || $userRole === 'Administrator')
                                        <span
                                            class="approve-toggle material-symbols-outlined absolute top-1 right-1 z-10 cursor-pointer select-none transition-colors rounded border {{ ($cell->is_approved ?? false) ? 'text-green-600 hc:text-green-400 hover:text-red-500 hc:hover:text-red-400 border-green-600 hc:border-green-400' : 'text-gray-300 hc:text-white hover:text-green-600 hc:hover:text-green-400 border-gray-300 hc:border-white' }}"
                                            style="font-size: max(10px, calc(var(--grid-size) * 0.14))"
                                            data-cell-id="{{ $cell->id ?? '' }}"
                                            data-approved="{{ ($cell->is_approved ?? false) ? 'true' : 'false' }}"
                                            title="{{ ($cell->is_approved ?? false) ? 'Revoke approval' : 'Approve this cell' }}"
                                            role="button"
                                            tabindex="0"
                                            aria-label="{{ ($cell->is_approved ?? false) ? 'Revoke approval for row ' . $cell->row_index . ' column ' . $cell->column_index : 'Approve row ' . $cell->row_index . ' column ' . $cell->column_index }}"
                                        >{{ ($cell->is_approved ?? false) ? 'lock' : 'lock_open' }}</span>
                                    @endif
                                    </div>

                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>

                @if($userRole !== 'Policy maker')
                <div class="w-full lg:w-auto bg-red-50 hc:bg-black hc:border-2 hc:border-red-400 dark:bg-red-900/20 border-2 border-dashed border-red-300 hc:border-solid dark:border-red-700 rounded-2xl p-6 shadow-sm hc:shadow-none focus:outline-none focus:ring-2 focus:ring-red-500 hc:focus:ring-yellow-400"
                     id="removal-zone"
                     role="button"
                     tabindex="0"
                     aria-label="Remove function — drop here or press Enter to remove the focused cell"
                     data-removal-zone>
                    <p class="text-sm text-red-700 hc:text-red-400 dark:text-red-300 text-center font-semibold">
                        Drag here to remove
                    </p>
                </div>
                @endif

                {{-- Access Road Panel --}}
                <div class="w-full lg:w-auto bg-amber-50 hc:bg-black hc:border hc:border-yellow-300 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-2xl p-6 shadow-sm hc:shadow-none">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-amber-800 hc:text-yellow-300 dark:text-amber-200 uppercase tracking-wide">Access Roads</h3>
                        <div class="flex gap-2">
                            <button id="access-road-toggle"
                                    type="button"
                                    class="bg-amber-500 hc:bg-yellow-300 hc:text-black hover:bg-amber-600 hc:hover:bg-yellow-200 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm hc:shadow-none focus:outline-none focus:ring-2 focus:ring-amber-400 hc:focus:ring-yellow-400">
                                Place Road
                            </button>
                            <button id="access-road-cancel"
                                    type="button"
                                    class="hidden bg-gray-500 hc:bg-neutral-800 hc:border hc:border-white hc:text-white hover:bg-gray-600 hc:hover:bg-neutral-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm hc:shadow-none focus:outline-none focus:ring-2 focus:ring-gray-400 hc:focus:ring-yellow-400">
                                Cancel
                            </button>
                        </div>
                    </div>
                    <p id="access-road-status" class="text-xs text-amber-700 hc:text-yellow-300 dark:text-amber-300 mb-3 min-h-[1rem]" aria-live="polite" aria-atomic="true"></p>
                    <div id="access-road-list" class="space-y-1"></div>
                </div>

                {{-- Event Route Panel --}}
                <div class="w-full lg:w-auto bg-violet-50 hc:bg-black hc:border hc:border-white dark:bg-violet-900/20 border border-violet-200 dark:border-violet-700 rounded-2xl p-6 shadow-sm hc:shadow-none"
                     aria-labelledby="event-route-heading">
                    <div class="flex items-center justify-between mb-3">
                        <h3 id="event-route-heading" class="text-sm font-semibold text-violet-800 hc:text-white dark:text-violet-200 uppercase tracking-wide">Event Routes</h3>
                        <div class="flex gap-2">
                            <button id="event-route-create"
                                    type="button"
                                    disabled
                                    aria-disabled="true"
                                    class="bg-violet-600 hc:bg-yellow-300 hc:text-black hover:bg-violet-700 hc:hover:bg-yellow-200 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm hc:shadow-none focus:outline-none focus:ring-2 focus:ring-violet-400 hc:focus:ring-yellow-400">
                                Create Route
                            </button>
                            <button id="event-route-cancel"
                                    type="button"
                                    class="hidden bg-gray-500 hc:bg-neutral-800 hc:border hc:border-white hc:text-white hover:bg-gray-600 hc:hover:bg-neutral-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm hc:shadow-none focus:outline-none focus:ring-2 focus:ring-gray-400 hc:focus:ring-yellow-400">
                                Cancel
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="event-route-road-select" class="block text-xs font-medium text-violet-700 hc:text-white dark:text-violet-300 mb-1">Access road to route from</label>
                        <select id="event-route-road-select"
                                class="w-full text-xs rounded-lg border border-violet-300 hc:border-white hc:bg-black hc:text-white dark:border-violet-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-violet-500 hc:focus:ring-yellow-400">
                            <option value="" disabled selected>Select an access road…</option>
                        </select>
                    </div>

                    <p id="event-route-status"
                       class="text-xs text-violet-700 hc:text-white dark:text-violet-300 mb-3 min-h-[1rem]"
                       aria-live="polite"
                       aria-atomic="true"></p>

                    <div id="event-route-list" class="space-y-1"></div>
                </div>

                </section>

                {{-- FUNCTION LIBRARY --}}
                @if($userRole !== 'Policy maker')
                <section class="flex-1 min-w-0 bg-blue-50 hc:bg-black hc:border hc:border-white dark:bg-gray-700 rounded-2xl p-3 sm:p-6 shadow-sm hc:shadow-none"
                        x-data="functionLibrary(@js($cityFunctions->map(fn ($cityFunction) => [
                            'name' => $cityFunction->name,
                            'category' => $cityFunction->category ?? '',
                        ])->values()))"
                         x-init="syncNoResultsAnnouncement(); $watch('searchTerm', () => syncNoResultsAnnouncement()); $watch('active', () => syncNoResultsAnnouncement())"
                         aria-labelledby="function-library-heading">
                    <h2 id="function-library-heading" class="text-lg font-bold text-gray-800 hc:text-white dark:text-gray-100 mb-4">Function Library</h2>

                    @if($cityFunctions->isEmpty())
                        <p class="text-gray-500 hc:text-white dark:text-gray-400">{{ __('No city functions found.') }}</p>
                    @else
                        <div class="mb-6 space-y-3">
                            <div>
                                <label for="function-search" class="block text-sm font-medium text-gray-700 hc:text-white dark:text-gray-200 mb-1">Search by name or category</label>
                                <input id="function-search" type="text" x-model.debounce.150ms="searchTerm" placeholder="Type a function name or category"
                                       class="w-full bg-white hc:bg-black hc:text-white hc:border-white hc:placeholder:text-neutral-400 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 hc:focus:ring-yellow-400">
                            </div>

                            <div>
                                <label for="category-filter" class="sr-only">Filter by category</label>
                                <select id="category-filter" x-model="active"
                                        class="w-full bg-white hc:bg-black hc:text-white hc:border-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 hc:focus:ring-yellow-400">
                                    <option value="All">All categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}">{{ $category }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <p x-cloak x-show="!hasVisibleFunctions()" class="text-gray-500 hc:text-white dark:text-gray-400 mb-4" aria-hidden="true">
                            No results found.
                        </p>

                        <div class="sr-only" role="alert" aria-live="assertive" aria-atomic="true" x-text="noResultsAnnouncement"></div>

                        <div class="grid gap-4" style="grid-template-columns: repeat(auto-fill, minmax(96px, 1fr))">
                            @foreach($cityFunctions as $cityFunction)
                                <button
                                    type="button"
                                    data-library-card
                                    x-show="isVisible(@js($cityFunction->name), @js($cityFunction->category ?? ''))"
                                    class="bg-white hc:bg-black hc:border-2 hc:border-white dark:bg-gray-800 rounded-xl shadow-sm hc:shadow-none flex flex-col items-center justify-center p-4 cursor-pointer hover:shadow-md hc:hover:shadow-none hc:hover:border-yellow-300 transition focus:outline-none focus:ring-2 focus:ring-blue-500 hc:focus:ring-yellow-400 border border-gray-200 dark:border-gray-700"
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
                                             class="w-16 h-16 object-contain mb-2"
                                             draggable="false">
                                    @endif
                                    <span class="text-xs font-semibold text-center text-gray-700 hc:text-white dark:text-white break-words w-full leading-tight line-clamp-2">{{ $cityFunction->name }}</span>
                                </button>

                            @endforeach
                        </div>
                    @endif
                </section>
                @endif

            </div>

            {{-- REV.2.1, REV.2.2 --}}
            <div class="flex flex-col lg:flex-row gap-6 mt-6">
                {{-- REV.2.2 - Improvement Suggestions --}}
                <section class="flex-1 min-w-0" x-data="gridCellSuggestions(@js($userRole))" aria-labelledby="suggestions-heading">
                    <div class="bg-white hc:bg-black hc:border hc:border-white dark:bg-gray-800 rounded-2xl shadow-sm hc:shadow-none px-4 sm:px-6 py-6">

                        <h2 id="suggestions-heading" class="text-lg font-bold text-gray-800 hc:text-white dark:text-gray-100 mb-5">Improvement Suggestions</h2>

                        {{-- Add suggestion form — only for policy makers and administrators --}}
                        @if($userRole === 'Policy maker' || $userRole === 'Administrator')
                        <form @submit.prevent="submit" class="mb-6 space-y-3" novalidate>
                            <div class="flex flex-col sm:flex-row gap-3">
                                <div class="flex-1">
                                    <label for="suggestion-cell" class="block text-sm font-medium text-gray-700 hc:text-white dark:text-gray-300 mb-1">Grid cell</label>
                                    <select id="suggestion-cell"
                                            x-model="selectedCellId"
                                            class="w-full rounded-xl border border-gray-300 hc:border-white dark:border-gray-600 bg-white hc:bg-black dark:bg-gray-700 text-gray-800 hc:text-white dark:text-gray-100 text-sm px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 hc:focus:ring-yellow-400">
                                        <option value="" disabled>Select a cell…</option>
                                        <template x-for="cell in cells" :key="cell.id">
                                            <option :value="cell.id" x-text="cellLabel(cell)"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label for="suggestion-description" class="block text-sm font-medium text-gray-700 hc:text-white dark:text-gray-300 mb-1">Recommended change</label>
                                <textarea id="suggestion-description"
                                        x-model="description"
                                        rows="3"
                                        maxlength="1000"
                                        placeholder="Describe what you would like to see changed…"
                                        class="w-full rounded-xl border border-gray-300 hc:border-white dark:border-gray-600 bg-white hc:bg-black dark:bg-gray-700 text-gray-800 hc:text-white dark:text-gray-100 text-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 hc:focus:ring-yellow-400 resize-none"
                                        aria-describedby="suggestion-error"></textarea>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <p id="suggestion-error"
                                    x-show="error"
                                    x-text="error"
                                    class="text-sm text-red-600 hc:text-red-400 dark:text-red-400"
                                    aria-live="polite"></p>
                                <button type="submit"
                                        :disabled="submitting || !selectedCellId || !description.trim()"
                                        class="ml-auto bg-orange-500 hc:bg-yellow-300 hc:text-black hover:bg-orange-600 hc:hover:bg-yellow-200 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold px-4 py-2 rounded-lg transition focus:outline-none focus:ring-2 focus:ring-orange-400 hc:focus:ring-yellow-400">
                                    <span x-text="submitting ? 'Submitting…' : 'Submit Suggestion'">Submit Suggestion</span>
                                </button>
                            </div>
                        </form>
                        @endif

                        {{-- Loading state --}}
                        <template x-if="loading">
                            <p class="text-gray-400 hc:text-white dark:text-gray-500 text-sm" aria-live="polite">Loading suggestions…</p>
                        </template>

                        {{-- Empty state --}}
                        <template x-if="!loading && suggestions.length === 0">
                            <p class="text-gray-500 hc:text-white dark:text-gray-400 text-sm">No suggestions yet.</p>
                        </template>

                        {{-- Suggestion list --}}
                        <ul class="space-y-4" aria-label="Improvement suggestions">
                            <template x-for="s in suggestions" :key="s.id">
                                <li class="border border-gray-200 hc:border-white dark:border-gray-700 rounded-xl px-4 py-3">
                                    <div class="flex items-start justify-between gap-3 flex-wrap">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                                                    :class="statusClass(s.status)"
                                                    x-text="statusLabel(s.status)"></span>
                                                <span class="text-sm font-semibold text-gray-800 hc:text-white dark:text-gray-100"
                                                    x-text="'Row ' + s.row + ', Column ' + s.column"></span>
                                                <span class="text-xs text-gray-400 hc:text-white dark:text-gray-500"
                                                    x-text="'— ' + s.author + ', ' + s.created_at"></span>
                                            </div>
                                            <p class="text-sm text-gray-700 hc:text-white dark:text-gray-300 whitespace-pre-wrap break-words"
                                            x-text="s.description"></p>
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0">
                                            {{-- Accept / Reject — city planner and administrator --}}
                                            @if($userRole === 'City planner' || $userRole === 'Administrator')
                                            <template x-if="s.status === 'pending'">
                                                <div class="flex gap-2">
                                                    <button @click="setStatus(s.id, 'accepted')"
                                                            class="text-xs font-semibold text-green-600 hc:text-green-400 hover:text-green-800 hc:hover:text-green-300 border border-green-400 hc:border-green-400 hover:border-green-600 hc:hover:border-green-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-green-400 hc:focus:ring-yellow-400 transition">
                                                        Accept
                                                    </button>
                                                    <button @click="setStatus(s.id, 'rejected')"
                                                            class="text-xs font-semibold text-red-500 hc:text-red-400 hover:text-red-700 hc:hover:text-red-300 border border-red-400 hc:border-red-400 hover:border-red-600 hc:hover:border-red-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-red-400 hc:focus:ring-yellow-400 transition">
                                                        Reject
                                                    </button>
                                                </div>
                                            </template>
                                            @endif

                                            {{-- Delete own suggestion --}}
                                            <template x-if="s.is_mine">
                                                <button @click="remove(s.id)"
                                                        class="text-xs font-semibold text-red-500 hc:text-red-400 hover:text-red-700 hc:hover:text-red-300 focus:outline-none focus:ring-2 focus:ring-red-400 hc:focus:ring-yellow-400 rounded px-2 py-1 transition"
                                                        :aria-label="'Delete suggestion for row ' + s.row + ' column ' + s.column">
                                                    Delete
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </li>
                            </template>
                        </ul>

                    </div>
                </section>

                {{-- REV.2.1 - Simulation Comments --}}
                <section class="flex-1 min-w-0" x-data="simulationComments" aria-labelledby="comments-heading">
                    <div class="bg-white hc:bg-black hc:border hc:border-white dark:bg-gray-800 rounded-2xl shadow-sm hc:shadow-none px-4 sm:px-6 py-6">

                        <h2 id="comments-heading" class="text-lg font-bold text-gray-800 hc:text-white dark:text-gray-100 mb-5">Comments</h2>

                        {{-- Add comment form — only visible to policy makers and administrators --}}
                        @if($userRole === 'Policy maker' || $userRole === 'Administrator')
                        <form @submit.prevent="submit" class="mb-6" novalidate>
                            <label for="comment-body" class="block text-sm font-medium text-gray-700 hc:text-white dark:text-gray-300 mb-1">
                                Add a comment
                            </label>
                            <textarea
                                id="comment-body"
                                x-model="newBody"
                                rows="3"
                                maxlength="1000"
                                placeholder="Leave feedback for the city planner…"
                                class="w-full rounded-xl border border-gray-300 hc:border-white dark:border-gray-600 bg-white hc:bg-black dark:bg-gray-700 text-gray-800 hc:text-white dark:text-gray-100 text-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 hc:focus:ring-yellow-400 resize-none"
                                aria-describedby="comment-error"
                            ></textarea>

                            <div class="flex items-center justify-between mt-2 gap-4">
                                <p id="comment-error"
                                x-show="error"
                                x-text="error"
                                class="text-sm text-red-600 hc:text-red-400 dark:text-red-400"
                                aria-live="polite"></p>
                                <button
                                    type="submit"
                                    :disabled="submitting || !newBody.trim()"
                                    class="ml-auto bg-blue-600 hc:bg-yellow-300 hc:text-black hover:bg-blue-700 hc:hover:bg-yellow-200 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold px-4 py-2 rounded-lg transition focus:outline-none focus:ring-2 focus:ring-blue-400 hc:focus:ring-yellow-400"
                                >
                                    <span x-text="submitting ? 'Posting…' : 'Post Comment'">Post Comment</span>
                                </button>
                            </div>
                        </form>
                        @endif

                        {{-- Loading state --}}
                        <template x-if="loading">
                            <p class="text-gray-400 hc:text-white dark:text-gray-500 text-sm" aria-live="polite">Loading comments…</p>
                        </template>

                        {{-- Empty state --}}
                        <template x-if="!loading && comments.length === 0">
                            <p class="text-gray-500 hc:text-white dark:text-gray-400 text-sm">No comments yet.</p>
                        </template>

                        {{-- Comment list --}}
                        <ul class="space-y-4" aria-label="Simulation comments">
                            <template x-for="comment in comments" :key="comment.id">
                                <li class="border border-gray-200 hc:border-white dark:border-gray-700 rounded-xl px-4 py-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-baseline gap-2 flex-wrap">
                                                <span class="text-sm font-semibold text-gray-800 hc:text-white dark:text-gray-100"
                                                    x-text="comment.author"></span>
                                                <span class="text-xs text-gray-400 hc:text-white dark:text-gray-500"
                                                    x-text="comment.created_at"></span>
                                            </div>
                                            <p class="text-sm text-gray-700 hc:text-white dark:text-gray-300 mt-1.5 whitespace-pre-wrap break-words"
                                            x-text="comment.body"></p>
                                        </div>
                                        <template x-if="comment.is_mine">
                                            <button
                                                @click="remove(comment.id)"
                                                class="shrink-0 text-xs font-semibold text-red-500 hc:text-red-400 hover:text-red-700 hc:hover:text-red-300 focus:outline-none focus:ring-2 focus:ring-red-400 hc:focus:ring-yellow-400 rounded px-2 py-1 transition"
                                                :aria-label="'Delete comment by ' + comment.author"
                                            >Delete</button>
                                        </template>
                                    </div>
                                </li>
                            </template>
                        </ul>

                    </div>
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
            border-width: 3px !important;
        }
        .road-cell span {
            color: white !important;
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

        /* Event location cells — violet outline*/
        .event-location-cell {
            border-color: rgb(139, 92, 246) !important;
            border-style: dashed !important;
            border-width: 4px !important;
        }

        /* Event route path cells — solid violet, stronger fill */
        .event-route-cell {
            background-color: rgba(109, 40, 217, 0.22) !important;
            border-color: rgb(109, 40, 217) !important;
        }
        .event-route-cell span {
            color: white !important;
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

    <div id="grid-a11y-announcer" aria-live="polite" aria-atomic="true" role="status" class="sr-only"></div>
    <div id="grid-a11y-error" role="alert" aria-live="assertive" aria-atomic="true" class="sr-only"></div>

    <div id="qol-toast"
         role="status"
         aria-live="assertive"
         aria-atomic="true"
         class="fixed bottom-6 right-6 z-50 hidden px-5 py-3 rounded-xl shadow-lg text-white text-sm font-semibold transition-all duration-300">
    </div>

</x-app-layout>
