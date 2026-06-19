<div x-cloak x-show="editOpen"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto bg-black/60"
    x-effect="if (editOpen) $nextTick(() => $refs.editName && $refs.editName.focus())"
     @keydown.escape.window="closeEdit()"
     role="dialog"
     aria-modal="true"
     aria-labelledby="edit-event-title"
     aria-describedby="edit-event-description">
    <div class="flex min-h-full items-center justify-center px-4 py-6" @click.self="closeEdit()">
    <div class="w-full max-w-2xl rounded-3xl bg-white hc:bg-black hc:border-2 hc:border-white p-6 shadow-2xl hc:shadow-none dark:bg-gray-800">
        <div class="mb-6 flex items-start justify-between gap-4">
            <div>
                <h2 id="edit-event-title" class="text-xl font-bold text-slate-900 hc:text-white dark:text-gray-100">Edit event</h2>
                <p id="edit-event-description" class="mt-1 text-sm text-slate-500 hc:text-white dark:text-gray-400">Update the name, description, and schedule settings.</p>
            </div>
            <button type="button" @click="closeEdit()" class="rounded-full p-2 text-slate-400 hc:text-white transition hover:bg-slate-100 hc:hover:bg-neutral-900 hover:text-slate-700 dark:hover:bg-gray-700 dark:hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-yellow-400" aria-label="Close edit dialog">
                ✕
            </button>
        </div>

        <form method="POST" :action="'/events/' + editing.id" class="space-y-6">
            @csrf
            @method('PUT')
            @include('city_events.partials.form-fields', ['model' => 'editing'])
            @include('city_events.partials.function-links', ['model' => 'editing', 'cityFunctions' => []])

            <div class="flex justify-end gap-3">
                <x-secondary-button type="button" @click="closeEdit()">
                    {{ __('Cancel') }}
                </x-secondary-button>
                <x-primary-button class="bg-cyan-600 hc:bg-yellow-300 hc:text-black hover:bg-cyan-500 hc:hover:bg-yellow-200 focus:ring-cyan-500 hc:focus:ring-yellow-400">
                    {{ __('Save changes') }}
                </x-primary-button>
            </div>
        </form>
    </div>
    </div>
</div>