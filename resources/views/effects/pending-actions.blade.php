<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl leading-tight text-gray-800 hc:text-white dark:text-gray-200">
            {{ __('Pending Actions') }}
        </h1>
    </x-slot>

    <div class="py-12">
        <div class="px-4 sm:px-6 lg:px-8" x-data="{ loading: false }">
            <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <div tabindex="0" role="group" aria-label="Pending: {{ $pendingCount }}" class="rounded-2xl bg-white hc:bg-black hc:border hc:border-white p-5 shadow-sm hc:shadow-none ring-1 ring-gray-200 hc:ring-0 dark:bg-gray-800 dark:ring-gray-700">
                    <div class="text-sm text-gray-500 hc:text-white dark:text-gray-400">Pending</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900 hc:text-white dark:text-gray-100">{{ $pendingCount }}</div>
                </div>
                <div tabindex="0" role="group" aria-label="Completed: {{ $completedCount }}" class="rounded-2xl bg-white hc:bg-black hc:border hc:border-white p-5 shadow-sm hc:shadow-none ring-1 ring-gray-200 hc:ring-0 dark:bg-gray-800 dark:ring-gray-700">
                    <div class="text-sm text-gray-500 hc:text-white dark:text-gray-400">Completed</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900 hc:text-white dark:text-gray-100">{{ $completedCount }}</div>
                </div>
                <div tabindex="0" role="group" aria-label="Total visible: {{ $pendingActions->count() }}" class="rounded-2xl bg-white hc:bg-black hc:border hc:border-white p-5 shadow-sm hc:shadow-none ring-1 ring-gray-200 hc:ring-0 sm:col-span-2 dark:bg-gray-800 dark:ring-gray-700 xl:col-span-1">
                    <div class="text-sm text-gray-500 hc:text-white dark:text-gray-400">Total visible</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900 hc:text-white dark:text-gray-100">{{ $pendingActions->count() }}</div>
                </div>
            </div>

            <div class="mb-6 rounded-2xl bg-white hc:bg-black hc:border hc:border-white p-4 shadow-sm hc:shadow-none ring-1 ring-gray-200 hc:ring-0 dark:bg-gray-800 dark:ring-gray-700">
                <form method="GET" action="{{ route('effects.pending-actions') }}" class="grid gap-4 md:grid-cols-3" @submit="loading = true">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 hc:text-white dark:text-gray-200">Status</label>
                        <select id="status" name="status" class="mt-1 block w-full rounded-lg border-gray-300 hc:border-white hc:bg-black hc:text-white bg-white text-gray-900 shadow-sm focus:border-blue-500 hc:focus:border-yellow-400 focus:ring-blue-500 hc:focus:ring-yellow-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected($statusFilter === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="trigger_type" class="block text-sm font-medium text-gray-700 hc:text-white dark:text-gray-200">Trigger type</label>
                        <select id="trigger_type" name="trigger_type" class="mt-1 block w-full rounded-lg border-gray-300 hc:border-white hc:bg-black hc:text-white bg-white text-gray-900 shadow-sm focus:border-blue-500 hc:focus:border-yellow-400 focus:ring-blue-500 hc:focus:ring-yellow-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                            @foreach($triggerTypeOptions as $value => $label)
                                <option value="{{ $value }}" @selected($triggerTypeFilter === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end gap-3">
                        <button type="submit" class="inline-flex items-center rounded-lg bg-blue-600 hc:bg-yellow-300 hc:text-black px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 hc:hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-blue-500 hc:focus:ring-yellow-400 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                            Apply filters
                        </button>
                        <a href="{{ route('effects.pending-actions') }}" class="inline-flex items-center rounded-lg bg-gray-100 hc:bg-black hc:border hc:border-white hc:text-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-200 hc:hover:bg-neutral-900 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                            Reset
                        </a>
                    </div>
                </form>

                <div x-show="loading" x-cloak class="mt-4 rounded-xl border border-dashed border-gray-300 hc:border-white p-4 text-sm text-gray-500 hc:text-white dark:border-gray-600 dark:text-gray-400">
                    Loading...
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl bg-white hc:bg-black hc:border hc:border-white shadow-sm hc:shadow-none ring-1 ring-gray-200 hc:ring-0 dark:bg-gray-800 dark:ring-gray-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 hc:divide-white dark:divide-gray-700">
                        <thead class="hidden md:table-header-group bg-gray-50 hc:bg-neutral-900 dark:bg-gray-700/60">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 hc:text-white dark:text-gray-300">Function</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 hc:text-white dark:text-gray-300">Trigger type</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 hc:text-white dark:text-gray-300">Created at</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 hc:text-white dark:text-gray-300">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 hc:text-white dark:text-gray-300">Still required</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 hc:text-white dark:text-gray-300">User</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 hc:text-white text-right dark:text-gray-300">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 hc:divide-white dark:divide-gray-700">
                            @forelse($pendingActions as $pendingAction)
                                @php
                                    $categoryText = $pendingAction->cityFunction?->category ? 'Category: ' . $pendingAction->cityFunction?->category . '. ' : '';
                                    $missing = count($pendingAction->missing_effect_columns ?? []) > 0 ? implode(', ', $pendingAction->missing_effect_columns) : 'All values filled';
                                    $createdText = $pendingAction->created_at?->format('d-m-Y H:i') ?: '';
                                    $statusText = $pendingAction->status_label;
                                @endphp
                                {{-- Mobile card --}}
                                <tr class="md:hidden border-b border-gray-200 hc:border-white dark:border-gray-700">
                                    <td colspan="7" class="px-4 py-4">
                                        <div class="space-y-3 text-sm">
                                            <div class="font-semibold text-gray-900 hc:text-white dark:text-gray-100">{{ $pendingAction->resolved_function_name }}</div>
                                            @if($pendingAction->cityFunction?->category)
                                                <div class="text-xs text-gray-500 hc:text-white dark:text-gray-400">{{ $pendingAction->cityFunction?->category }}</div>
                                            @endif
                                            @if($pendingAction->function_name && $pendingAction->function_name !== $pendingAction->resolved_function_name)
                                                <div class="text-xs text-gray-500 hc:text-white dark:text-gray-400">Snapshot: {{ $pendingAction->function_name }}</div>
                                            @endif
                                            <div class="flex flex-wrap gap-2 items-center">
                                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold bg-blue-50 hc:bg-black hc:border hc:border-white text-blue-700 hc:text-white dark:bg-blue-900/30 dark:text-blue-200">{{ $pendingAction->trigger_label }}</span>
                                                <span class="text-xs text-gray-500 hc:text-white dark:text-gray-400">({{ $pendingAction->trigger_type }})</span>
                                            </div>
                                            <div class="text-xs text-gray-500 hc:text-white dark:text-gray-400">
                                                <time datetime="{{ $pendingAction->created_at?->toIso8601String() }}">{{ $pendingAction->created_at?->format('d-m-Y H:i') }}</time>
                                                · {{ $pendingAction->created_at?->diffForHumans() }}
                                            </div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                @if($pendingAction->status === \App\Models\PendingAction::STATUS_COMPLETED)
                                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold bg-green-100 hc:bg-black hc:border hc:border-green-400 text-green-700 hc:text-green-400 dark:bg-green-900/30 dark:text-green-200">{{ $pendingAction->status_label }}</span>
                                                @else
                                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold bg-amber-100 hc:bg-black hc:border hc:border-yellow-300 text-amber-700 hc:text-yellow-300 dark:bg-amber-900/30 dark:text-amber-200">{{ $pendingAction->status_label }}</span>
                                                @endif
                                                <span class="text-xs text-gray-500 hc:text-white dark:text-gray-400">by {{ $pendingAction->createdBy?->name ?? __('Unknown') }}</span>
                                            </div>
                                            @if(count($pendingAction->missing_effect_columns ?? []) > 0)
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($pendingAction->missing_effect_columns as $column)
                                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold bg-red-50 hc:bg-black hc:border hc:border-red-400 text-red-700 hc:text-red-400 dark:bg-red-900/30 dark:text-red-200">{{ $column }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-xs text-gray-500 hc:text-white dark:text-gray-400">All values filled</span>
                                            @endif
                                            @if($pendingAction->city_function_id)
                                                <a href="{{ route('effects.index', ['function' => $pendingAction->city_function_id]) }}#function-{{ $pendingAction->city_function_id }}"
                                                   class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold transition bg-gray-900 hc:bg-yellow-300 hc:text-black text-white hover:bg-gray-700 hc:hover:bg-yellow-200 dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-white">
                                                    Open effects screen
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                {{-- Desktop row --}}
                                <tr tabindex="0" role="button" class="hidden md:table-row transition hover:bg-gray-50 hc:hover:bg-neutral-900 dark:hover:bg-gray-700/40"
                                    aria-label="Function: {{ $pendingAction->resolved_function_name }}. {{ $categoryText }} Trigger: {{ $pendingAction->trigger_label }} ({{ $pendingAction->trigger_type }}). Created: {{ $createdText }}. Status: {{ $statusText }}. Still required: {{ $missing }}. User: {{ $pendingAction->createdBy?->name ?? 'Unknown' }}"
                                    onclick="(function(el, evt){ const target = evt.target; if(target.closest('a, button, input, select, textarea')) return; const a=el.querySelector('a'); if(a) a.click(); })(this, event)"
                                    onkeydown="(function(evt){ if(evt.key==='Enter' || evt.key===' '){ evt.preventDefault(); const a=this.querySelector('a'); if(a) a.click(); } }).call(this, event)">
                                    <td class="px-6 py-4">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 hc:text-white dark:text-gray-400">Related function</div>
                                        <div class="mt-1 font-medium text-gray-900 hc:text-white dark:text-gray-100">{{ $pendingAction->resolved_function_name }}</div>
                                        @if($pendingAction->cityFunction?->category)
                                            <div class="mt-1 text-xs text-gray-600 hc:text-white dark:text-gray-300">Category: {{ $pendingAction->cityFunction?->category }}</div>
                                        @endif
                                        @if($pendingAction->function_name && $pendingAction->function_name !== $pendingAction->resolved_function_name)
                                            <div class="mt-1 text-xs text-gray-500 hc:text-white dark:text-gray-400">Stored snapshot: {{ $pendingAction->function_name }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 hc:text-white dark:text-gray-200">
                                        <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500 hc:text-white dark:text-gray-400">Trigger</div>
                                            <div class="flex items-center gap-3">
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold bg-blue-50 hc:bg-black hc:border hc:border-white text-blue-700 hc:text-white dark:bg-blue-900/30 dark:text-blue-200">{{ $pendingAction->trigger_label }}</span>
                                            <span class="sr-only">{{ $pendingAction->trigger_type }}</span>
                                            <span class="text-xs text-gray-500 hc:text-white dark:text-gray-400">({{ $pendingAction->trigger_type }})</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 hc:text-white dark:text-gray-200">
                                        <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500 hc:text-white dark:text-gray-400">Created</div>
                                        <time datetime="{{ $pendingAction->created_at?->toIso8601String() }}">{{ $pendingAction->created_at?->format('d-m-Y H:i') }}</time>
                                        <div class="mt-1 text-xs text-gray-500 hc:text-white dark:text-gray-400">{{ $pendingAction->created_at?->diffForHumans() }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 hc:text-white dark:text-gray-200">
                                        @if($pendingAction->status === \App\Models\PendingAction::STATUS_COMPLETED)
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold bg-green-100 hc:bg-black hc:border hc:border-green-400 text-green-700 hc:text-green-400 dark:bg-green-900/30 dark:text-green-200">{{ $pendingAction->status_label }}</span>
                                            @if($pendingAction->completed_at)
                                                <div class="mt-1 text-xs text-gray-500 hc:text-white dark:text-gray-400">{{ $pendingAction->completed_at->diffForHumans() }}</div>
                                            @endif
                                        @else
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold bg-amber-100 hc:bg-black hc:border hc:border-yellow-300 text-amber-700 hc:text-yellow-300 dark:bg-amber-900/30 dark:text-amber-200">{{ $pendingAction->status_label }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 hc:text-white dark:text-gray-200">
                                        @if(count($pendingAction->missing_effect_columns ?? []) > 0)
                                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 hc:text-white dark:text-gray-400 mb-2">Missing values</div>
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($pendingAction->missing_effect_columns as $column)
                                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold bg-red-50 hc:bg-black hc:border hc:border-red-400 text-red-700 hc:text-red-400 dark:bg-red-900/30 dark:text-red-200">{{ $column }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-500 hc:text-white dark:text-gray-400">All values filled</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 hc:text-white dark:text-gray-200">
                                        {{ $pendingAction->createdBy?->name ?? __('Unknown') }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        @if($pendingAction->city_function_id)
                                            <a href="{{ route('effects.index', ['function' => $pendingAction->city_function_id]) }}#function-{{ $pendingAction->city_function_id }}" class="inline-flex items-center rounded-lg px-4 py-2 font-semibold transition bg-gray-900 hc:bg-yellow-300 hc:text-black text-white hover:bg-gray-700 hc:hover:bg-yellow-200 dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-white">
                                                Open effects screen
                                            </a>
                                        @else
                                            <span class="inline-flex items-center rounded-lg px-4 py-2 font-semibold transition bg-gray-100 hc:bg-neutral-800 hc:text-white text-gray-400 dark:bg-gray-700 dark:text-gray-500">
                                                Not available
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-16 text-center">
                                        <div class="mx-auto max-w-md">
                                            <div class="text-lg font-semibold text-gray-900 hc:text-white dark:text-gray-100">No actions found</div>
                                            <p class="mt-2 text-sm text-gray-500 hc:text-white dark:text-gray-400">There are no items for the selected filters.</p>
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
