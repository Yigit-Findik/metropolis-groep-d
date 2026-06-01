<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Events') }}
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Create temporary city events, then review or adjust them from the overview panel.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="cityEvents">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div x-data="autoHideToast"
                     x-show="show"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 translate-y-2"
                     class="fixed bottom-6 right-6 z-50 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-lg"
                     role="status"
                     aria-live="polite">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid gap-6 xl:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
                <section class="overflow-hidden rounded-3xl bg-slate-950 text-white shadow-2xl ring-1 ring-white/10">
                    <div class="border-b border-white/10 bg-gradient-to-br from-cyan-500/25 via-slate-950 to-slate-900 px-6 py-8">
                        <span class="inline-flex rounded-full bg-cyan-400/15 px-3 py-1 text-xs font-semibold tracking-wide text-cyan-200 uppercase">
                            New Event
                        </span>
                        <h2 class="mt-4 text-2xl font-bold">Create an event</h2>
                        <p class="mt-2 max-w-xl text-sm text-slate-300">
                            Choose a one-off event for temporary disruption or a recurring event for repeating city conditions.
                        </p>
                    </div>

                    <div class="p-6">
                        <form method="POST" action="{{ route('city_events.store') }}" class="space-y-6">
                            @csrf
                            @include('city_events.partials.form-fields', ['model' => 'creating'])

                            <div class="flex justify-end">
                                <x-primary-button class="bg-cyan-600 hover:bg-cyan-500 focus:ring-cyan-500">
                                    {{ __('Create Event') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </section>

                <section class="rounded-3xl bg-white/95 shadow-xl ring-1 ring-slate-200/80 dark:bg-gray-800 dark:ring-gray-700">
                    <div class="border-b border-slate-200 px-6 py-6 dark:border-gray-700">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900 dark:text-gray-100">Event overview</h2>
                                <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                                    {{ $events->count() }} {{ \Illuminate\Support\Str::plural('event', $events->count()) }} configured.
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-2 text-xs font-semibold uppercase tracking-wide">
                                <span class="rounded-full bg-cyan-100 px-3 py-1 text-cyan-800 dark:bg-cyan-500/10 dark:text-cyan-200">
                                    {{ $events->where('event_type', 'recurring')->count() }} recurring
                                </span>
                                <span class="rounded-full bg-amber-100 px-3 py-1 text-amber-800 dark:bg-amber-500/10 dark:text-amber-200">
                                    {{ $events->where('event_type', 'one-off')->count() }} one-off
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        @if($events->isEmpty())
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center dark:border-gray-600 dark:bg-gray-900/30">
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-gray-100">No events yet</h3>
                                <p class="mt-2 text-sm text-slate-500 dark:text-gray-400">
                                    Use the form on the left to add your first event.
                                </p>
                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach($events as $event)
                                    @include('city_events.partials.card', ['event' => $event])
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>
            </div>
        </div>

        @include('city_events.partials.edit-modal')

        {{-- CONFIRMATION MODAL --------------------------------------------------
             Shared delete confirmation dialog for event deletes. Keeps keyboard
             focus inside the modal while it is open and announces the action. --}}
        <div x-cloak x-data="confirmModal" @keydown.escape.window="cancel()"
             x-show="show"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4"
             role="dialog"
             aria-modal="true"
             aria-labelledby="delete-event-title"
             aria-describedby="delete-event-message"
             @click.self="cancel()">

            <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-800"
                 x-effect="if (show) $nextTick(() => $refs.confirmDeleteButton && $refs.confirmDeleteButton.focus())">
                <h2 id="delete-event-title" class="text-lg font-bold text-gray-900 dark:text-gray-100">Confirm delete</h2>
                <p id="delete-event-message" class="mt-3 text-sm text-gray-700 dark:text-gray-300" x-text="message"></p>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button"
                            @click="cancel()"
                            x-ref="cancelDeleteButton"
                            class="px-4 py-2 bg-gray-300 dark:bg-gray-700 hover:bg-gray-400 dark:hover:bg-gray-600 text-gray-900 dark:text-gray-100 font-semibold rounded-lg transition">
                        Cancel
                    </button>
                    <button type="button"
                            @click="confirm()"
                            x-ref="confirmDeleteButton"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300 text-white font-semibold rounded-lg transition">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>