<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 hc:text-white dark:text-gray-200 leading-tight">
            {{ __('Audit Log') }}
        </h1>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white hc:bg-black hc:border hc:border-white dark:bg-gray-800 overflow-hidden shadow-sm hc:shadow-none sm:rounded-lg p-6">
                <form id="filters" method="GET" class="mb-4 flex flex-wrap gap-3 items-end" aria-label="Filter audit log entries">
                    <div class="flex-1 min-w-[140px]">
                        <label for="date_from" class="block text-sm text-gray-700 hc:text-white dark:text-gray-200">From</label>
                        <input id="date_from" type="date" name="date_from" value="{{ request('date_from') }}" class="w-full border rounded px-3 py-2 hc:bg-black hc:text-white hc:border-white dark:bg-gray-700 dark:border-gray-600 dark:text-white" aria-label="Filter from date" />
                    </div>
                    <div class="flex-1 min-w-[140px]">
                        <label for="date_to" class="block text-sm text-gray-700 hc:text-white dark:text-gray-200">To</label>
                        <input id="date_to" type="date" name="date_to" value="{{ request('date_to') }}" class="w-full border rounded px-3 py-2 hc:bg-black hc:text-white hc:border-white dark:bg-gray-700 dark:border-gray-600 dark:text-white" aria-label="Filter to date" />
                    </div>
                    <div class="flex-1 min-w-[120px]">
                        <label for="action_filter" class="block text-sm text-gray-700 hc:text-white dark:text-gray-200">Action</label>
                        <select id="action_filter" name="action" class="w-full border rounded px-3 py-2 hc:bg-black hc:text-white hc:border-white dark:bg-gray-700 dark:border-gray-600 dark:text-white" aria-label="Filter by action type">
                            <option value="">Any</option>
                            <option value="create" @if(request('action')=='create') selected @endif>create</option>
                            <option value="update" @if(request('action')=='update') selected @endif>update</option>
                            <option value="delete" @if(request('action')=='delete') selected @endif>delete</option>
                        </select>
                    </div>
                    <div class="shrink-0">
                        <button type="submit" class="bg-blue-600 hc:bg-yellow-300 hc:text-black text-white px-4 py-2 rounded hover:bg-blue-700 hc:hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-blue-500 hc:focus:ring-yellow-400 focus:ring-offset-2 dark:focus:ring-offset-gray-800" aria-label="Apply audit log filters">Filter</button>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 hc:divide-white dark:divide-gray-700" role="grid" aria-label="System audit log showing user actions on functions and events">
                        <thead>
                            <tr class="text-left text-sm text-gray-500 hc:text-white">
                                <th class="px-3 py-2" scope="col">Timestamp</th>
                                <th class="px-3 py-2" scope="col">User</th>
                                <th class="px-3 py-2" scope="col">Action</th>
                                <th class="px-3 py-2" scope="col">Affected Item</th>
                                <th class="px-3 py-2" scope="col">Details</th>
                            </tr>
                        </thead>
                        <tbody id="audit-rows" class="bg-white hc:bg-black dark:bg-gray-800 divide-y divide-gray-200 hc:divide-white dark:divide-gray-700">
                            @foreach($entries as $entry)
                                @php
                                    $details = is_array($entry->details) ? $entry->details : [];
                                    $isCityEvent = ($details['entity_type'] ?? null) === 'city_event';

                                    $filteredFields = $isCityEvent
                                        ? [
                                            'name' => 'Name',
                                            'description' => 'Description',
                                            'event_type' => 'Event type',
                                            'recurring_frequency_value' => 'Recurring frequency value',
                                            'recurring_frequency_unit' => 'Recurring frequency unit',
                                            'one_off_duration_value' => 'One-off duration value',
                                            'one_off_duration_unit' => 'One-off duration unit',
                                        ]
                                        : [
                                            'name' => 'Name',
                                            'category' => 'Category',
                                            'description' => 'Description',
                                            'Safety' => 'Safety',
                                            'Recreation' => 'Recreation',
                                            'Environment Quality' => 'Environment Quality',
                                            'Facilities' => 'Facilities',
                                            'Mobility' => 'Mobility',
                                        ];

                                    $renderedRows = [];

                                    if (isset($details['changed']) && is_array($details['changed']) && ! empty($details['changed'])) {
                                        foreach ($details['changed'] as $field => $value) {
                                            if (in_array($field, ['created_at', 'updated_at', 'id', 'image_path'], true)) {
                                                continue;
                                            }

                                            $renderedRows[] = [
                                                'label' => $filteredFields[$field] ?? $field,
                                                'value' => $details['original'][$field] ?? null,
                                                'new' => $value,
                                            ];
                                        }
                                    } elseif (isset($details['new']) && is_array($details['new'])) {
                                        foreach ($filteredFields as $field => $label) {
                                            if (array_key_exists($field, $details['new'])) {
                                                $renderedRows[] = [
                                                    'label' => $label,
                                                    'value' => $details['new'][$field],
                                                ];
                                            }
                                        }
                                    } elseif (isset($details['original']) && is_array($details['original'])) {
                                        foreach ($filteredFields as $field => $label) {
                                            if (array_key_exists($field, $details['original'])) {
                                                $renderedRows[] = [
                                                    'label' => $label,
                                                    'value' => $details['original'][$field],
                                                ];
                                            }
                                        }
                                    } elseif (array_key_exists('old', $details) || array_key_exists('new', $details)) {
                                        $renderedRows[] = [
                                            'label' => 'Old value',
                                            'value' => $details['old'] ?? '—',
                                        ];
                                        $renderedRows[] = [
                                            'label' => 'New value',
                                            'value' => $details['new'] ?? '—',
                                        ];
                                    }

                                    if (isset($details['reverted_action_id'])) {
                                        $renderedRows[] = [
                                            'label' => 'Reverted action',
                                            'value' => '#' . $details['reverted_action_id'],
                                        ];
                                    }

                                    $timestampLabel = $entry->created_at->format('j-n-Y H:i:s');
                                    $userLabel = $entry->user?->name ?? 'system';
                                    $actionLabel = $entry->action;
                                    $entityLabel = $isCityEvent ? 'City event' : 'City function';
                                    $functionLabel = $isCityEvent
                                        ? ($details['name'] ?? 'Not available')
                                        : ($entry->newCityFunction?->name ?? $entry->oldCityFunction?->name ?? 'Not available');
                                    $actionBadgeClasses = [
                                        'create' => 'bg-green-100 hc:bg-green-400 text-green-800 hc:text-black dark:bg-green-900/40 dark:text-green-200',
                                        'update' => 'bg-yellow-100 hc:bg-yellow-300 text-yellow-800 hc:text-black dark:bg-yellow-900/40 dark:text-yellow-200',
                                        'delete' => 'bg-red-100 hc:bg-red-400 text-red-800 hc:text-black dark:bg-red-900/40 dark:text-red-200',
                                    ][$actionLabel] ?? 'bg-gray-100 hc:bg-white text-gray-800 hc:text-black dark:bg-gray-700 dark:text-gray-200';
                                    $isSnapshotAction = in_array($actionLabel, ['create', 'delete'], true);
                                    $snapshotTitle = $actionLabel === 'create'
                                        ? ($isCityEvent ? 'Created city event' : 'Created city function')
                                        : ($isCityEvent ? 'Deleted city event' : 'Deleted city function');

                                    $detailsText = [];
                                    $detailsTextForAria = [];
                                    foreach ($renderedRows as $row) {
                                        if (($row['label'] ?? '') === 'name') {
                                            continue;
                                        }

                                        if (array_key_exists('new', $row)) {
                                            $oldVal = is_array($row['value']) ? json_encode($row['value']) : (($row['value'] === null) ? 'empty' : ($row['value'] ?? 'empty'));
                                            $newVal = is_array($row['new']) ? json_encode($row['new']) : (($row['new'] === null) ? 'empty' : ($row['new'] ?? 'empty'));
                                            $detailsText[] = $row['label'] . ': ' . $oldVal . ' -> ' . $newVal;
                                            $detailsTextForAria[] = $row['label'] . ': old ' . $oldVal . ' new ' . $newVal;
                                        } else {
                                            $val = is_array($row['value']) ? json_encode($row['value']) : (($row['value'] === null) ? 'empty' : ($row['value'] ?? 'empty'));
                                            $detailsText[] = $row['label'] . ' ' . $val;
                                            $detailsTextForAria[] = $row['label'] . ' ' . $val;
                                        }
                                    }

                                    $fullDetailsLabel = count($detailsText) > 0 ? implode('. ', $detailsText) : 'No additional details recorded';
                                    $fullDetailsLabelForAria = count($detailsTextForAria) > 0 ? implode('. ', $detailsTextForAria) : 'No additional details recorded';
                                    $actionText = match($actionLabel) {
                                        'create' => 'Created',
                                        'delete' => 'Deleted',
                                        default => ucfirst($actionLabel),
                                    };

                                    $rowLabel = $actionText . ' ' . $entityLabel . ' ' . $functionLabel . '. User ' . $userLabel . '. Timestamp ' . $timestampLabel . '. Details ' . $fullDetailsLabelForAria;
                                @endphp
                                <tr class="text-sm text-gray-700 hc:text-white dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 hc:focus:ring-yellow-400 focus:ring-inset" role="row" tabindex="0" aria-label="{{ $rowLabel }}">
                                    <td class="px-3 py-2" role="gridcell"><time datetime="{{ $entry->created_at->toIso8601String() }}">{{ $timestampLabel }}</time></td>
                                    <td class="px-3 py-2" role="gridcell">{{ $userLabel }}</td>
                                    <td class="px-3 py-2" role="gridcell"><span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold capitalize {{ $actionBadgeClasses }}" aria-label="Action: {{ $actionLabel }}">{{ $actionLabel }}</span></td>
                                    <td class="px-3 py-2" role="gridcell">
                                        <div class="inline-flex items-center gap-2">
                                            <span class="inline-flex items-center rounded-full bg-gray-100 hc:bg-black hc:border hc:border-white px-2.5 py-1 text-xs font-semibold text-gray-700 hc:text-white dark:bg-gray-700 dark:text-gray-200">
                                                {{ $entityLabel }}
                                            </span>
                                            <span>{{ $functionLabel }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 align-top" role="gridcell">
                                        @if(count($renderedRows) > 0)
                                            <div lang="en" class="rounded-lg border border-gray-200 hc:border-white bg-gray-50 hc:bg-neutral-900 p-3 dark:border-gray-700 dark:bg-gray-900/30">
                                                @if($isSnapshotAction)
                                                    <div class="sr-only">{{ $snapshotTitle }}</div>
                                                @endif
                                                <div class="mt-0 flex flex-wrap gap-2">
                                                    @foreach($renderedRows as $row)
                                                        @continue($row['label'] === 'name')
                                                        <div lang="en" class="inline-flex items-center gap-2 rounded-full border border-gray-200 hc:border-white bg-white hc:bg-black px-3 py-1 text-xs text-gray-700 hc:text-white dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                                            <span class="font-semibold text-gray-900 hc:text-white dark:text-gray-100">{{ $row['label'] }}</span>
                                                            @if(array_key_exists('new', $row))
                                                                <span>{{ is_array($row['value']) ? json_encode($row['value']) : (($row['value'] === null) ? 'empty' : ($row['value'] ?? 'empty')) }}</span>
                                                                <span class="text-xs text-gray-400 hc:text-white mx-2">-></span>
                                                                <span>{{ is_array($row['new']) ? json_encode($row['new']) : (($row['new'] === null) ? 'empty' : ($row['new'] ?? 'empty')) }}</span>
                                                            @else
                                                                <span>{{ is_array($row['value']) ? json_encode($row['value']) : (($row['value'] === null) ? 'empty' : ($row['value'] ?? 'empty')) }}</span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-500 hc:text-white dark:text-gray-400 italic" aria-label="No additional details recorded">No additional details recorded</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $entries->links() }}
                </div>
            </div>
        </div>
    </div>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/js/audit_log/audit_log.js')
    @endif
</x-app-layout>

<style>
    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border-width: 0;
    }
</style>
