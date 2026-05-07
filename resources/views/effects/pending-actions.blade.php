<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">
            {{ __('Pending Actions') }}
        </h2>
    </x-slot>

    <div class="pending-actions-page">
        <div class="pending-actions-container" x-data="{ loading: false }">
            <div class="pending-actions-stats">
                <div class="pending-actions-card">
                    <div class="pending-actions-card-label">Pending</div>
                    <div class="pending-actions-card-value">{{ $pendingCount }}</div>
                </div>
                <div class="pending-actions-card">
                    <div class="pending-actions-card-label">Completed</div>
                    <div class="pending-actions-card-value">{{ $completedCount }}</div>
                </div>
                <div class="pending-actions-card pending-actions-card--wide">
                    <div class="pending-actions-card-label">Total visible</div>
                    <div class="pending-actions-card-value">{{ $pendingActions->count() }}</div>
                </div>
            </div>

            <div class="pending-actions-filter-panel">
                <form method="GET" action="{{ route('effects.pending-actions') }}" class="pending-actions-filter-grid" @submit="loading = true">
                    <div>
                        <label for="status" class="pending-actions-label">Status</label>
                        <select id="status" name="status" class="pending-actions-select">
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected($statusFilter === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="trigger_type" class="pending-actions-label">Trigger type</label>
                        <select id="trigger_type" name="trigger_type" class="pending-actions-select">
                            @foreach($triggerTypeOptions as $value => $label)
                                <option value="{{ $value }}" @selected($triggerTypeFilter === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pending-actions-actions">
                        <button type="submit" class="pending-actions-button-primary">
                            Apply filters
                        </button>
                        <a href="{{ route('effects.pending-actions') }}" class="pending-actions-button-secondary">
                            Reset
                        </a>
                    </div>
                </form>

                <div x-show="loading" x-cloak class="pending-actions-loading">
                    Loading...
                </div>
            </div>

            <div class="pending-actions-table-shell">
                <div class="pending-actions-table-wrap">
                    <table class="pending-actions-table">
                        <thead class="pending-actions-table-head">
                            <tr>
                                <th class="pending-actions-head-cell">Function</th>
                                <th class="pending-actions-head-cell">Trigger type</th>
                                <th class="pending-actions-head-cell">Created at</th>
                                <th class="pending-actions-head-cell">Status</th>
                                <th class="pending-actions-head-cell">User</th>
                                <th class="pending-actions-head-cell pending-actions-head-cell--right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="pending-actions-table-body">
                            @forelse($pendingActions as $pendingAction)
                                <tr class="pending-actions-row">
                                    <td class="pending-actions-function-cell">
                                        <div class="pending-actions-function-label">Related function</div>
                                        <div class="pending-actions-function-value">{{ $pendingAction->resolved_function_name }}</div>
                                        @if($pendingAction->function_name && $pendingAction->function_name !== $pendingAction->resolved_function_name)
                                            <div class="pending-actions-snapshot">Stored snapshot: {{ $pendingAction->function_name }}</div>
                                        @endif
                                    </td>
                                    <td class="pending-actions-trigger-cell">
                                        <div class="pending-actions-trigger-label">Trigger</div>
                                        <span class="pending-actions-trigger-badge pending-actions-trigger-badge--blue">
                                            {{ $pendingAction->trigger_label }}
                                        </span>
                                    </td>
                                    <td class="pending-actions-trigger-cell">
                                        <div class="pending-actions-trigger-label">Created at</div>
                                        <time datetime="{{ $pendingAction->created_at?->toIso8601String() }}">{{ $pendingAction->created_at?->format('d-m-Y H:i') }}</time>
                                        <div class="pending-actions-snapshot">{{ $pendingAction->created_at?->diffForHumans() }}</div>
                                    </td>
                                    <td class="pending-actions-status-cell">
                                        @if($pendingAction->status === \App\Models\PendingAction::STATUS_COMPLETED)
                                            <span class="pending-actions-status-badge pending-actions-status-badge--green">{{ $pendingAction->status_label }}</span>
                                            @if($pendingAction->completed_at)
                                                <div class="pending-actions-snapshot">{{ $pendingAction->completed_at->diffForHumans() }}</div>
                                            @endif
                                        @else
                                            <span class="pending-actions-status-badge pending-actions-status-badge--amber">{{ $pendingAction->status_label }}</span>
                                        @endif
                                    </td>
                                    <td class="pending-actions-user-cell">
                                        {{ $pendingAction->createdBy?->name ?? __('Unknown') }}
                                    </td>
                                    <td class="pending-actions-action-cell">
                                        @if($pendingAction->city_function_id)
                                            <a href="{{ route('effects.index', ['function' => $pendingAction->city_function_id]) }}#function-{{ $pendingAction->city_function_id }}" class="pending-actions-link pending-actions-link--primary">
                                                Open effects screen
                                            </a>
                                        @else
                                            <span class="pending-actions-link pending-actions-link--disabled">
                                                Not available
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="pending-actions-empty-cell">
                                        <div class="pending-actions-empty-wrap">
                                            <div class="pending-actions-empty-title">No actions found</div>
                                            <p class="pending-actions-empty-text">There are no items for the selected filters.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>