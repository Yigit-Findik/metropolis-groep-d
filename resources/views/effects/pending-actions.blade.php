<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Pending Actions') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="px-4 sm:px-6 lg:px-8" x-data="{ loading: false }">
            <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <div tabindex="0" role="group" aria-label="Pending: {{ $pendingCount }}" class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Pending</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $pendingCount }}</div>
                </div>
                <div tabindex="0" role="group" aria-label="Completed: {{ $completedCount }}" class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Completed</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $completedCount }}</div>
                </div>
                <div tabindex="0" role="group" aria-label="Total visible: {{ $pendingActions->count() }}" class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 sm:col-span-2 dark:bg-gray-800 dark:ring-gray-700 xl:col-span-1">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total visible</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $pendingActions->count() }}</div>
                </div>
            </div>

            <div class="mb-6 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                <form method="GET" action="{{ route('effects.pending-actions') }}" class="grid gap-4 md:grid-cols-3" @submit="loading = true">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Status</label>
                        <select id="status" name="status" class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected($statusFilter === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="trigger_type" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Trigger type</label>
                        <select id="trigger_type" name="trigger_type" class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                            @foreach($triggerTypeOptions as $value => $label)
                                <option value="{{ $value }}" @selected($triggerTypeFilter === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end gap-3">
                        <button type="submit" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                            Apply filters
                        </button>
                        <a href="{{ route('effects.pending-actions') }}" class="inline-flex items-center rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                            Reset
                        </a>
                    </div>
                </form>

                <div x-show="loading" x-cloak class="mt-4 rounded-xl border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">
                    Loading...
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/60">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">Function</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">Trigger type</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">Created at</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">Still required</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">User</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 text-right dark:text-gray-300">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($pendingActions as $pendingAction)
                                @php
                                    $categoryText = $pendingAction->cityFunction?->category ? 'Category: ' . $pendingAction->cityFunction?->category . '. ' : '';
                                    $missing = count($pendingAction->missing_effect_columns ?? []) > 0 ? implode(', ', $pendingAction->missing_effect_columns) : 'All values filled';
                                    $createdText = $pendingAction->created_at?->format('d-m-Y H:i') ?: '';
                                    $statusText = $pendingAction->status_label;
                                @endphp
                                <tr tabindex="0" role="button" class="transition hover:bg-gray-50 dark:hover:bg-gray-700/40"
                                    aria-label="Function: {{ $pendingAction->resolved_function_name }}. {{ $categoryText }} Trigger: {{ $pendingAction->trigger_label }} ({{ $pendingAction->trigger_type }}). Created: {{ $createdText }}. Status: {{ $statusText }}. Still required: {{ $missing }}. User: {{ $pendingAction->createdBy?->name ?? 'Unknown' }}"
                                    onclick="(function(el, evt){ const target = evt.target; if(target.closest('a, button, input, select, textarea')) return; const a=el.querySelector('a'); if(a) a.click(); })(this, event)"
                                    onkeydown="(function(evt){ if(evt.key==='Enter' || evt.key===' '){ evt.preventDefault(); const a=this.querySelector('a'); if(a) a.click(); } }).call(this, event)">
                                    <td class="px-6 py-4">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Related function</div>
                                        <div class="mt-1 font-medium text-gray-900 dark:text-gray-100">{{ $pendingAction->resolved_function_name }}</div>
                                        @if($pendingAction->cityFunction?->category)
                                            <div class="mt-1 text-xs text-gray-600 dark:text-gray-300">Category: {{ $pendingAction->cityFunction?->category }}</div>
                                        @endif
                                        @if($pendingAction->function_name && $pendingAction->function_name !== $pendingAction->resolved_function_name)
                                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Stored snapshot: {{ $pendingAction->function_name }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">
                                        <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Trigger</div>
                                            <div class="flex items-center gap-3">
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-200">{{ $pendingAction->trigger_label }}</span>
                                            <span class="sr-only">{{ $pendingAction->trigger_type }}</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">({{ $pendingAction->trigger_type }})</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">
                                        <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Created</div>
                                        <time datetime="{{ $pendingAction->created_at?->toIso8601String() }}">{{ $pendingAction->created_at?->format('d-m-Y H:i') }}</time>
                                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $pendingAction->created_at?->diffForHumans() }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">
                                        @if($pendingAction->status === \App\Models\PendingAction::STATUS_COMPLETED)
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-200">{{ $pendingAction->status_label }}</span>
                                            @if($pendingAction->completed_at)
                                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $pendingAction->completed_at->diffForHumans() }}</div>
                                            @endif
                                        @else
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-200">{{ $pendingAction->status_label }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">
                                        @if(count($pendingAction->missing_effect_columns ?? []) > 0)
                                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Missing values</div>
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($pendingAction->missing_effect_columns as $column)
                                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-200">{{ $column }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-500 dark:text-gray-400">All values filled</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">
                                        {{ $pendingAction->createdBy?->name ?? __('Unknown') }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        @if($pendingAction->city_function_id)
                                            <a href="{{ route('effects.index', ['function' => $pendingAction->city_function_id]) }}#function-{{ $pendingAction->city_function_id }}" class="inline-flex items-center rounded-lg px-4 py-2 font-semibold transition bg-gray-900 text-white hover:bg-gray-700 dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-white">
                                                Open effects screen
                                            </a>
                                        @else
                                            <span class="inline-flex items-center rounded-lg px-4 py-2 font-semibold transition bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500">
                                                Not available
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-16 text-center">
                                        <div class="mx-auto max-w-md">
                                            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">No actions found</div>
                                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">There are no items for the selected filters.</p>
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