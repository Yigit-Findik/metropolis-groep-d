<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>City Grid Report</title>
    <style>
        {!! file_get_contents(public_path('build/assets/' . $cssFile)) !!}

        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; }
        .grid-cell { height: 90px; }
    </style>
</head>
<body class="bg-white text-gray-800 text-sm p-6">

    {{-- ── Report header ── --}}
    <div class="bg-blue-600 text-white px-6 py-5 rounded-xl mb-6">
        <h1 class="text-2xl font-bold">City Grid Report</h1>
        <p class="text-blue-200 text-xs mt-1">Metropolis Quality of Life Simulation Export</p>
    </div>

    {{-- ── Metadata ── --}}
    <div class="bg-gray-50 border border-gray-200 rounded-xl overflow-hidden mb-6">
        <table class="w-full text-sm">
            <tbody>
                <tr class="border-b border-gray-200">
                    <td class="px-4 py-2 font-semibold text-gray-500 w-40">Report date</td>
                    <td class="px-4 py-2 text-gray-800">{{ $exportedAt }}</td>
                </tr>
                <tr class="border-b border-gray-200">
                    <td class="px-4 py-2 font-semibold text-gray-500">Author</td>
                    <td class="px-4 py-2 text-gray-800">{{ $author }}</td>
                </tr>
                <tr class="border-b border-gray-200">
                    <td class="px-4 py-2 font-semibold text-gray-500">Placed functions</td>
                    <td class="px-4 py-2 text-gray-800">{{ $placedCount }} / {{ $totalCells }}</td>
                </tr>
                <tr>
                    <td class="px-4 py-2 font-semibold text-gray-500">Total QoL score</td>
                    <td class="px-4 py-2 font-bold text-blue-700">{{ $qol['total_score'] }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- ── Grid layout ── --}}
    <h2 class="text-base font-bold text-blue-700 border-b-2 border-blue-700 pb-1 mb-4">Grid Layout</h2>
    <table class="w-full border-collapse mb-6" style="table-layout: fixed;">
        <thead>
            <tr>
                <td class="text-xs text-gray-400 font-bold text-center pb-1" style="width:28px;"></td>
                @for($c = 1; $c <= 4; $c++)
                    <td class="text-xs text-gray-400 font-bold text-center pb-1">Col {{ $c }}</td>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach($gridCells->groupBy('row_index') as $rowIndex => $rowCells)
                <tr>
                    <td class="text-xs text-gray-400 font-bold text-center" style="width:28px; vertical-align:middle;">R{{ $rowIndex }}</td>
                    @foreach($rowCells->sortBy('column_index') as $cell)
                        @php $fn = $cityFunctions->firstWhere('id', $cell->function_id); @endphp
                        @if($fn)
                            <td class="grid-cell border border-gray-200 bg-blue-50 text-center align-middle p-2">
                                @if($fn->image_path && file_exists(public_path($fn->image_path)))
                                    <img src="{{ public_path($fn->image_path) }}"
                                         alt="{{ $fn->image_alt ?? $fn->name }}"
                                         style="width:36px;height:36px;object-fit:contain;display:block;margin:0 auto 4px;">
                                @endif
                                <div class="text-xs font-bold text-gray-800 leading-tight">{{ $fn->name }}</div>
                                <div class="text-gray-500" style="font-size:8px; margin-top:2px;">{{ $fn->category }}</div>
                            </td>
                        @else
                            <td class="grid-cell border border-gray-200 bg-gray-50 text-center align-middle">
                                <span class="text-gray-300 text-2xl">+</span>
                            </td>
                        @endif
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── QoL Score summary ── --}}
    <h2 class="text-base font-bold text-blue-700 border-b-2 border-blue-700 pb-1 mb-4">Quality of Life Score</h2>

    <div class="bg-blue-600 text-white px-5 py-4 rounded-xl mb-4">
        <div class="text-4xl font-bold leading-none">{{ $qol['total_score'] }}</div>
        <div class="text-blue-200 text-xs mt-1">Total QoL Score</div>
    </div>

    <table class="w-full border-collapse mb-6 text-xs">
        <thead>
            <tr class="bg-blue-600 text-white">
                <th class="px-3 py-2 text-left">Category</th>
                <th class="px-3 py-2 text-center">Base</th>
                <th class="px-3 py-2 text-center">Bonus</th>
                <th class="px-3 py-2 text-center">Penalty</th>
                <th class="px-3 py-2 text-center font-bold">Net</th>
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
                    $rowBg   = $loop->even ? 'bg-blue-50' : 'bg-white';
                @endphp
                <tr class="{{ $rowBg }}">
                    <td class="px-3 py-2 border border-gray-200 font-semibold text-gray-700">{{ $label }}</td>
                    <td class="px-3 py-2 border border-gray-200 text-center text-gray-600">{{ $base }}</td>
                    <td class="px-3 py-2 border border-gray-200 text-center {{ $bonus > 0 ? 'text-green-700 font-bold' : 'text-gray-500' }}">
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
                <td class="px-3 py-2 font-bold" colspan="4">Total</td>
                <td class="px-3 py-2 text-center font-bold text-lg">{{ $qol['total_score'] }}</td>
            </tr>
        </tbody>
    </table>

    {{-- ── Per-cell breakdown ── --}}
    @if(!empty($qol['breakdown']))
        <h2 class="text-base font-bold text-blue-700 border-b-2 border-blue-700 pb-1 mb-4">Per-Cell Breakdown</h2>
        <table class="w-full border-collapse mb-6 text-xs">
            <thead>
                <tr class="bg-gray-700 text-white">
                    <th class="px-2 py-2 text-center">Row</th>
                    <th class="px-2 py-2 text-center">Col</th>
                    <th class="px-2 py-2 text-left">Function</th>
                    <th class="px-2 py-2 text-center">Safety</th>
                    <th class="px-2 py-2 text-center">Recreation</th>
                    <th class="px-2 py-2 text-center">Env. Quality</th>
                    <th class="px-2 py-2 text-center">Facilities</th>
                    <th class="px-2 py-2 text-center">Mobility</th>
                    <th class="px-2 py-2 text-center font-bold">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($qol['breakdown'] as $row)
                    @php $rowTotal = $row['safety'] + $row['recreation'] + $row['environment_quality'] + $row['facilities'] + $row['mobility']; @endphp
                    <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                        <td class="px-2 py-1 border border-gray-200 text-center">{{ $row['row'] }}</td>
                        <td class="px-2 py-1 border border-gray-200 text-center">{{ $row['column'] }}</td>
                        <td class="px-2 py-1 border border-gray-200 font-semibold text-gray-800">{{ $row['function'] }}</td>
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
    @endif

    {{-- ── City events ── --}}
    <h2 class="text-base font-bold text-blue-700 border-b-2 border-blue-700 pb-1 mb-4">City Events</h2>
    @if($events->isEmpty())
        <p class="text-xs text-gray-400 mb-6">No events configured.</p>
    @else
        <table class="w-full border-collapse mb-6 text-xs">
            <thead>
                <tr class="bg-gray-700 text-white">
                    <th class="px-3 py-2 text-left">Name</th>
                    <th class="px-3 py-2 text-center">Type</th>
                    <th class="px-3 py-2 text-left">Schedule</th>
                    <th class="px-3 py-2 text-left">Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach($events as $event)
                    <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                        <td class="px-3 py-2 border border-gray-200 font-semibold text-gray-800">{{ $event->name }}</td>
                        <td class="px-3 py-2 border border-gray-200 text-center">{{ $event->type_label }}</td>
                        <td class="px-3 py-2 border border-gray-200 text-gray-600">{{ $event->schedule_summary }}</td>
                        <td class="px-3 py-2 border border-gray-200 text-gray-600">{{ $event->description ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="border-t border-gray-200 pt-3 text-center text-gray-400 mt-6" style="font-size:9px;">
        Generated by Metropolis &bull; {{ $exportedAt }} &bull; {{ $author }}
    </div>

</body>
</html>
