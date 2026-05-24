<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Audit Log') }}
        </h1>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form id="filters" method="GET" class="mb-4 flex gap-2 items-end" aria-label="Filter audit log entries">
                    <div>
                        <label for="date_from" class="block text-sm text-gray-700 dark:text-gray-200">From</label>
                        <input id="date_from" type="date" name="date_from" value="{{ request('date_from') }}" class="border rounded px-2 py-1 dark:bg-gray-700 dark:border-gray-600 dark:text-white" aria-label="Filter from date" />
                    </div>
                    <div>
                        <label for="date_to" class="block text-sm text-gray-700 dark:text-gray-200">To</label>
                        <input id="date_to" type="date" name="date_to" value="{{ request('date_to') }}" class="border rounded px-2 py-1 dark:bg-gray-700 dark:border-gray-600 dark:text-white" aria-label="Filter to date" />
                    </div>
                    <div>
                        <label for="action_filter" class="block text-sm text-gray-700 dark:text-gray-200">Action</label>
                        <select id="action_filter" name="action" class="border rounded px-2 py-1 dark:bg-gray-700 dark:border-gray-600 dark:text-white" aria-label="Filter by action type">
                            <option value="">Any</option>
                            <option value="create" @if(request('action')=='create') selected @endif>create</option>
                            <option value="update" @if(request('action')=='update') selected @endif>update</option>
                            <option value="delete" @if(request('action')=='delete') selected @endif>delete</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800" aria-label="Apply audit log filters">Filter</button>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" role="grid" aria-label="System audit log showing user actions on effects data">
                        <thead>
                            <tr class="text-left text-sm text-gray-500">
                                <th class="px-3 py-2" scope="col">Timestamp</th>
                                <th class="px-3 py-2" scope="col">User</th>
                                <th class="px-3 py-2" scope="col">Action</th>
                                <th class="px-3 py-2" scope="col">Affected Function</th>
                                <th class="px-3 py-2" scope="col">Details</th>
                            </tr>
                        </thead>
                        <tbody id="audit-rows" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($entries as $entry)
                                @php
                                    $details = is_array($entry->details) ? $entry->details : [];

                                    $filteredFields = [
                                        'name',
                                        'category',
                                        'description',
                                        'Safety',
                                        'Recreation',
                                        'Environment Quality',
                                        'Facilities',
                                        'Mobility',
                                    ];

                                    $renderedRows = [];

                                    if (isset($details['new']) && is_array($details['new'])) {
                                        foreach ($filteredFields as $field) {
                                            if (array_key_exists($field, $details['new'])) {
                                                $renderedRows[] = [
                                                    'label' => $field,
                                                    'value' => $details['new'][$field],
                                                ];
                                            }
                                        }
                                    } elseif (isset($details['changed']) && is_array($details['changed'])) {
                                        foreach ($details['changed'] as $field => $value) {
                                            if (in_array($field, ['created_at', 'updated_at', 'id', 'image_path'], true)) {
                                                continue;
                                            }

                                            $renderedRows[] = [
                                                'label' => $field,
                                                'value' => $details['original'][$field] ?? null,
                                                'new' => $value,
                                            ];
                                        }
                                    } elseif (isset($details['original']) && is_array($details['original'])) {
                                        foreach ($filteredFields as $field) {
                                            if (array_key_exists($field, $details['original'])) {
                                                $renderedRows[] = [
                                                    'label' => $field,
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
                                    $functionLabel = $entry->newCityFunction?->name ?? $entry->oldCityFunction?->name ?? 'Not available';
                                    $actionBadgeClasses = [
                                        'create' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200',
                                        'update' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-200',
                                        'delete' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200',
                                    ][$actionLabel] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
                                    $isSnapshotAction = in_array($actionLabel, ['create', 'delete'], true);
                                    $snapshotTitle = $actionLabel === 'create' ? 'Created city function' : 'Deleted city function';

                                    $detailsText = [];
                                    foreach ($renderedRows as $row) {
                                        if (($row['label'] ?? '') === 'name') {
                                            continue;
                                        }

                                        if (array_key_exists('new', $row)) {
                                            $oldVal = is_array($row['value']) ? json_encode($row['value']) : (($row['value'] === null) ? 'empty' : ($row['value'] ?? 'empty'));
                                            $newVal = is_array($row['new']) ? json_encode($row['new']) : (($row['new'] === null) ? 'empty' : ($row['new'] ?? 'empty'));
                                            $detailsText[] = $row['label'] . ' old ' . $oldVal . ' new ' . $newVal;
                                        } else {
                                            $val = is_array($row['value']) ? json_encode($row['value']) : (($row['value'] === null) ? 'empty' : ($row['value'] ?? 'empty'));
                                            $detailsText[] = $row['label'] . ' ' . $val;
                                        }
                                    }

                                    $fullDetailsLabel = count($detailsText) > 0 ? implode('. ', $detailsText) : 'No additional details recorded';
                                    $actionText = match($actionLabel) {
                                        'create' => 'Created',
                                        'delete' => 'Deleted',
                                        default => ucfirst($actionLabel),
                                    };

                                    $rowLabel = $actionText . ' function ' . $functionLabel . '. User ' . $userLabel . '. Timestamp ' . $timestampLabel . '. Details ' . $fullDetailsLabel;
                                @endphp
                                <tr class="text-sm text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-inset" role="row" tabindex="0" aria-label="{{ $rowLabel }}">
                                    <td class="px-3 py-2" role="gridcell"><time datetime="{{ $entry->created_at->toIso8601String() }}">{{ $timestampLabel }}</time></td>
                                    <td class="px-3 py-2" role="gridcell">{{ $userLabel }}</td>
                                    <td class="px-3 py-2" role="gridcell"><span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold capitalize {{ $actionBadgeClasses }}" aria-label="Action: {{ $actionLabel }}">{{ $actionLabel }}</span></td>
                                    <td class="px-3 py-2" role="gridcell">{{ $functionLabel }}</td>
                                    <td class="px-3 py-2 align-top" role="gridcell">
                                        @if(count($renderedRows) > 0)
                                            <div lang="en" class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900/30">
                                                @if($isSnapshotAction)
                                                    <div class="sr-only">{{ $snapshotTitle }}</div>
                                                @endif
                                                <div class="mt-0 flex flex-wrap gap-2">
                                                    @foreach($renderedRows as $row)
                                                        @continue($row['label'] === 'name')
                                                        <div lang="en" class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $row['label'] }}</span>
                                                            @if(array_key_exists('new', $row))
                                                                <span class="text-xs text-gray-500 mr-1">Old:</span>
                                                                <span>{{ is_array($row['value']) ? json_encode($row['value']) : (($row['value'] === null) ? 'empty' : ($row['value'] ?? 'empty')) }}</span>
                                                                <span class="text-xs text-gray-400 mx-2">•</span>
                                                                <span class="text-xs text-gray-500 mr-1">New:</span>
                                                                <span>{{ is_array($row['new']) ? json_encode($row['new']) : (($row['new'] === null) ? 'empty' : ($row['new'] ?? 'empty')) }}</span>
                                                            @else
                                                                <span>{{ is_array($row['value']) ? json_encode($row['value']) : (($row['value'] === null) ? 'empty' : ($row['value'] ?? 'empty')) }}</span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-500 dark:text-gray-400 italic" aria-label="No additional details recorded">No additional details recorded</span>
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
        @vite('resources/js/audit_log.js')
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
