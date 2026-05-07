<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Pending Actions') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="px-4 sm:px-6 lg:px-8" x-data="{ loading: false }">
            <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Openstaand</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $pendingCount }}</div>
                </div>
                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Afgerond</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $completedCount }}</div>
                </div>
                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700 sm:col-span-2 xl:col-span-1">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Totaal zichtbaar</div>
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
                            Filter toepassen
                        </button>
                        <a href="{{ route('effects.pending-actions') }}" class="inline-flex items-center rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                            Reset
                        </a>
                    </div>
                </form>

                <div x-show="loading" x-cloak class="mt-4 rounded-xl border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">
                    Laden...
                </div>
            </div>

            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/60">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">Functie</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">Trigger type</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">Aangemaakt</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">Gebruiker</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">Actie</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($pendingActions as $pendingAction)
                                <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                    <td class="px-6 py-4">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Related function</div>
                                        <div class="mt-1 font-medium text-gray-900 dark:text-gray-100">{{ $pendingAction->resolved_function_name }}</div>
                                        @if($pendingAction->function_name && $pendingAction->function_name !== $pendingAction->resolved_function_name)
                                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Stored snapshot: {{ $pendingAction->function_name }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Trigger</div>
                                        <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-200">
                                            {{ $pendingAction->trigger_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Created at</div>
                                        <time datetime="{{ $pendingAction->created_at?->toIso8601String() }}">{{ $pendingAction->created_at?->format('d-m-Y H:i') }}</time>
                                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $pendingAction->created_at?->diffForHumans() }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">
                                        @if($pendingAction->status === \App\Models\PendingAction::STATUS_COMPLETED)
                                            <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-200">{{ $pendingAction->status_label }}</span>
                                            @if($pendingAction->completed_at)
                                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $pendingAction->completed_at->diffForHumans() }}</div>
                                            @endif
                                        @else
                                            <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-900/30 dark:text-amber-200">{{ $pendingAction->status_label }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">
                                        {{ $pendingAction->createdBy?->name ?? __('Onbekend') }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        @if($pendingAction->city_function_id)
                                            <a href="{{ route('effects.index', ['function' => $pendingAction->city_function_id]) }}#function-{{ $pendingAction->city_function_id }}" class="inline-flex items-center rounded-lg bg-gray-900 px-4 py-2 font-semibold text-white transition hover:bg-gray-700 dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-white">
                                                Open effect scherm
                                            </a>
                                        @else
                                            <span class="inline-flex items-center rounded-lg bg-gray-100 px-4 py-2 font-semibold text-gray-400 dark:bg-gray-700 dark:text-gray-500">
                                                Niet beschikbaar
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-16 text-center">
                                        <div class="mx-auto max-w-md">
                                            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">Geen acties gevonden</div>
                                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Er zijn geen items voor de gekozen filters.</p>
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