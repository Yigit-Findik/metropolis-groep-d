<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div class="flex items-center gap-3">
                <a href="{{ route('grid') }}"
                   class="inline-flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-100 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    Back to Grid
                </a>
                <span class="text-gray-300 dark:text-gray-600" aria-hidden="true">|</span>
                <h1 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">PDF Preview</h1>
            </div>
            <a href="{{ route('grid.download-pdf') }}"
               class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2 rounded-lg shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-400"
               aria-label="Download city grid report as PDF">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                </svg>
                Download PDF
            </a>
        </div>
    </x-slot>

    {{-- Skip link so keyboard/screen-reader users can jump straight to the report --}}
    <a href="#report-content"
       class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:bg-white focus:px-4 focus:py-2 focus:rounded focus:shadow focus:text-blue-700 focus:font-semibold">
        Skip to report content
    </a>

    {{-- Accessibility notice about the PDF itself --}}
    <div role="note" class="bg-amber-50 dark:bg-amber-900/20 border-l-4 border-amber-400 px-6 py-3 text-sm text-amber-800 dark:text-amber-200">
        <strong>Note:</strong> This preview is fully accessible. The downloaded PDF is text-based and can be read by most PDF screen readers, but its structure and reading order may be limited.
    </div>

    {{-- Gray page background to simulate a print preview environment --}}
    <div class="py-10 bg-gray-200 dark:bg-gray-900 min-h-screen">

        {{-- A4 paper container --}}
        <article id="report-content"
                 role="document"
                 aria-label="City Grid Report"
                 tabindex="0"
                 class="mx-auto bg-white shadow-2xl text-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                 style="width: 794px; min-height: 1123px; padding: 48px;">

            {{-- ── Report header ── --}}
            <div class="bg-blue-600 text-white px-6 py-5 rounded-xl mb-6">
                <h2 class="text-2xl font-bold">City Grid Report</h2>
                <p class="text-blue-200 text-xs mt-1">Metropolis &mdash; Quality of Life Simulation Export</p>
            </div>

            {{-- ── Metadata ── --}}
            <section aria-labelledby="meta-heading" class="mb-6">
                <h3 id="meta-heading" class="sr-only">Report metadata</h3>
                <div class="bg-gray-50 border border-gray-200 rounded-xl overflow-hidden">
                    <table class="w-full text-sm">
                        <caption class="sr-only">Report metadata including date, author and score</caption>
                        <tbody>
                            <tr class="border-b border-gray-200">
                                <th scope="row" class="px-4 py-2 font-semibold text-gray-500 w-40 text-left">Report date</th>
                                <td class="px-4 py-2 text-gray-800">{{ $exportedAt }}</td>
                            </tr>
                            <tr class="border-b border-gray-200">
                                <th scope="row" class="px-4 py-2 font-semibold text-gray-500 text-left">Author</th>
                                <td class="px-4 py-2 text-gray-800">{{ $author }}</td>
                            </tr>
                            <tr class="border-b border-gray-200">
                                <th scope="row" class="px-4 py-2 font-semibold text-gray-500 text-left">Placed functions</th>
                                <td class="px-4 py-2 text-gray-800">{{ $placedCount }} of {{ $totalCells }} cells filled</td>
                            </tr>
                            <tr>
                                <th scope="row" class="px-4 py-2 font-semibold text-gray-500 text-left">Total QoL score</th>
                                <td class="px-4 py-2 font-bold text-blue-700">{{ $qol['total_score'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- ── Grid layout ── --}}
            <section aria-labelledby="grid-heading" class="mb-6">
                <h3 id="grid-heading" class="text-base font-bold text-blue-700 border-b-2 border-blue-700 pb-1 mb-4">Grid Layout</h3>
                <table class="w-full border-collapse" style="table-layout: fixed;">
                    <caption class="sr-only">City grid layout showing placed functions per cell</caption>
                    <thead>
                        <tr>
                            <td class="text-xs text-gray-400 font-bold text-center pb-1" style="width:28px;" aria-hidden="true"></td>
                            @for($c = 1; $c <= 4; $c++)
                                <th scope="col" class="text-xs text-gray-400 font-bold text-center pb-1">Column {{ $c }}</th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($gridCells->groupBy('row_index') as $rowIndex => $rowCells)
                            <tr>
                                <th scope="row" class="text-xs text-gray-400 font-bold text-center align-middle" style="width:28px;">Row {{ $rowIndex }}</th>
                                @foreach($rowCells->sortBy('column_index') as $cell)
                                    @php $fn = $cityFunctions->firstWhere('id', $cell->function_id); @endphp
                                    @if($fn)
                                        <td class="border border-gray-200 bg-blue-50 text-center align-middle p-2"
                                            style="height:90px;"
                                            aria-label="Row {{ $rowIndex }}, Column {{ $cell->column_index }}: {{ $fn->name }}, category {{ $fn->category }}">
                                            @if($fn->image_path)
                                                <img src="{{ asset($fn->image_path) }}"
                                                     alt=""
                                                     aria-hidden="true"
                                                     class="w-9 h-9 object-contain mx-auto mb-1">
                                            @endif
                                            <div class="text-xs font-bold text-gray-800 leading-tight">{{ $fn->name }}</div>
                                            <div class="text-gray-500" style="font-size:8px; margin-top:2px;">{{ $fn->category }}</div>
                                        </td>
                                    @else
                                        <td class="border border-gray-200 bg-gray-50 text-center align-middle"
                                            style="height:90px;"
                                            aria-label="Row {{ $rowIndex }}, Column {{ $cell->column_index }}: empty">
                                            <span aria-hidden="true" class="text-gray-300 text-2xl">+</span>
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>

            {{-- ── QoL Score summary ── --}}
            <section aria-labelledby="qol-heading" class="mb-6">
                <h3 id="qol-heading" class="text-base font-bold text-blue-700 border-b-2 border-blue-700 pb-1 mb-4">Quality of Life Score</h3>

                <div class="bg-blue-600 text-white px-5 py-4 rounded-xl mb-4"
                     role="status"
                     aria-label="Total Quality of Life score: {{ $qol['total_score'] }}">
                    <div class="text-4xl font-bold leading-none" aria-hidden="true">{{ $qol['total_score'] }}</div>
                    <div class="text-blue-200 text-xs mt-1" aria-hidden="true">Total QoL Score</div>
                </div>

                <table class="w-full border-collapse text-xs">
                    <caption class="sr-only">Quality of life score breakdown by category showing base score, bonus, penalty and net score</caption>
                    <thead>
                        <tr class="bg-blue-600 text-white">
                            <th scope="col" class="px-3 py-2 text-left">Category</th>
                            <th scope="col" class="px-3 py-2 text-center">Base</th>
                            <th scope="col" class="px-3 py-2 text-center">Bonus</th>
                            <th scope="col" class="px-3 py-2 text-center">Penalty</th>
                            <th scope="col" class="px-3 py-2 text-center font-bold">Net</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $categoryLabels = [
                                'safety'              => 'Safety',
                                'recreation'          => 'Recreation',
                                'environment_quality' => 'Environment Quality',
                                'facilities'          => 'Facilities',
                                'mobility'            => 'Mobility',
                            ];
                        @endphp
                        @foreach($categoryLabels as $slug => $label)
                            @php
                                $bonus   = $qol['bonus_categories'][$slug] ?? 0;
                                $penalty = $qol['penalty_categories'][$slug] ?? 0;
                                $net     = $qol['categories'][$slug] ?? 0;
                                $base    = $net - $bonus - $penalty;
                            @endphp
                            <tr class="{{ $loop->even ? 'bg-blue-50' : 'bg-white' }}">
                                <th scope="row" class="px-3 py-2 border border-gray-200 font-semibold text-gray-700 text-left">{{ $label }}</th>
                                <td class="px-3 py-2 border border-gray-200 text-center text-gray-600">{{ $base }}</td>
                                <td class="px-3 py-2 border border-gray-200 text-center {{ $bonus > 0 ? 'text-green-700 font-bold' : 'text-gray-500' }}"
                                    aria-label="{{ $bonus > 0 ? 'Bonus: plus ' . $bonus : 'Bonus: ' . $bonus }}">
                                    {{ $bonus > 0 ? '+' . $bonus : $bonus }}
                                </td>
                                <td class="px-3 py-2 border border-gray-200 text-center {{ $penalty < 0 ? 'text-red-700 font-bold' : 'text-gray-500' }}">
                                    {{ $penalty }}
                                </td>
                                <td class="px-3 py-2 border border-gray-200 text-center font-bold {{ $net > 0 ? 'text-green-700' : ($net < 0 ? 'text-red-700' : 'text-gray-700') }}">
                                    {{ $net }}
                                </td>
                            </tr>
                        @endforeach
                        <tr class="bg-blue-600 text-white">
                            <th scope="row" class="px-3 py-2 font-bold text-left" colspan="4">Total</th>
                            <td class="px-3 py-2 text-center font-bold text-lg">{{ $qol['total_score'] }}</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            {{-- ── Per-cell breakdown ── --}}
            @if(!empty($qol['breakdown']))
                <section aria-labelledby="breakdown-heading" class="mb-6">
                    <h3 id="breakdown-heading" class="text-base font-bold text-blue-700 border-b-2 border-blue-700 pb-1 mb-4">Per-Cell Breakdown</h3>
                    <table class="w-full border-collapse text-xs">
                        <caption class="sr-only">Individual QoL scores for each placed function across all categories</caption>
                        <thead>
                            <tr class="bg-gray-700 text-white">
                                <th scope="col" class="px-2 py-2 text-center">Row</th>
                                <th scope="col" class="px-2 py-2 text-center">Col</th>
                                <th scope="col" class="px-2 py-2 text-left">Function</th>
                                <th scope="col" class="px-2 py-2 text-center">Safety</th>
                                <th scope="col" class="px-2 py-2 text-center">Recreation</th>
                                <th scope="col" class="px-2 py-2 text-center">Env. Quality</th>
                                <th scope="col" class="px-2 py-2 text-center">Facilities</th>
                                <th scope="col" class="px-2 py-2 text-center">Mobility</th>
                                <th scope="col" class="px-2 py-2 text-center font-bold">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($qol['breakdown'] as $row)
                                @php $rowTotal = $row['safety'] + $row['recreation'] + $row['environment_quality'] + $row['facilities'] + $row['mobility']; @endphp
                                <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                                    <td class="px-2 py-1 border border-gray-200 text-center">{{ $row['row'] }}</td>
                                    <td class="px-2 py-1 border border-gray-200 text-center">{{ $row['column'] }}</td>
                                    <th scope="row" class="px-2 py-1 border border-gray-200 font-semibold text-gray-800 text-left">{{ $row['function'] }}</th>
                                    <td class="px-2 py-1 border border-gray-200 text-center">{{ $row['safety'] }}</td>
                                    <td class="px-2 py-1 border border-gray-200 text-center">{{ $row['recreation'] }}</td>
                                    <td class="px-2 py-1 border border-gray-200 text-center">{{ $row['environment_quality'] }}</td>
                                    <td class="px-2 py-1 border border-gray-200 text-center">{{ $row['facilities'] }}</td>
                                    <td class="px-2 py-1 border border-gray-200 text-center">{{ $row['mobility'] }}</td>
                                    <td class="px-2 py-1 border border-gray-200 text-center font-bold text-gray-800">{{ $rowTotal }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </section>
            @endif

            {{-- ── City events ── --}}
            <section aria-labelledby="events-heading" class="mb-6">
                <h3 id="events-heading" class="text-base font-bold text-blue-700 border-b-2 border-blue-700 pb-1 mb-4">City Events</h3>
                @if($events->isEmpty())
                    <p class="text-xs text-gray-400">No events configured.</p>
                @else
                    <table class="w-full border-collapse text-xs">
                        <caption class="sr-only">List of city events with their type, schedule and description</caption>
                        <thead>
                            <tr class="bg-gray-700 text-white">
                                <th scope="col" class="px-3 py-2 text-left">Name</th>
                                <th scope="col" class="px-3 py-2 text-center">Type</th>
                                <th scope="col" class="px-3 py-2 text-left">Schedule</th>
                                <th scope="col" class="px-3 py-2 text-left">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($events as $event)
                                <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                                    <th scope="row" class="px-3 py-2 border border-gray-200 font-semibold text-gray-800 text-left">{{ $event->name }}</th>
                                    <td class="px-3 py-2 border border-gray-200 text-center">
                                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $event->event_type === 'recurring' ? 'bg-cyan-100 text-cyan-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ $event->type_label }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 border border-gray-200 text-gray-600">{{ $event->schedule_summary }}</td>
                                    <td class="px-3 py-2 border border-gray-200 text-gray-600">{{ $event->description ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </section>

            <footer class="border-t border-gray-200 pt-3 text-center text-gray-400 mt-6 text-xs">
                Generated by Metropolis &bull; {{ $exportedAt }} &bull; {{ $author }}
            </footer>

        </article>{{-- /paper --}}
    </div>

</x-app-layout>
