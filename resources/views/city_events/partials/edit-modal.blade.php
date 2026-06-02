<div x-cloak x-show="editOpen"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4"
    x-effect="if (editOpen) $nextTick(() => $refs.editName && $refs.editName.focus())"
     @keydown.escape.window="closeEdit()"
     @click.self="closeEdit()"
     role="dialog"
     aria-modal="true"
     aria-labelledby="edit-event-title"
     aria-describedby="edit-event-description">
    <div class="w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl dark:bg-gray-800">
        <div class="mb-6 flex items-start justify-between gap-4">
            <div>
                <h2 id="edit-event-title" class="text-xl font-bold text-slate-900 dark:text-gray-100">Edit event</h2>
                <p id="edit-event-description" class="mt-1 text-sm text-slate-500 dark:text-gray-400">Update the name, description, and schedule settings.</p>
            </div>
            <button type="button" @click="closeEdit()" class="rounded-full p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-gray-700 dark:hover:text-gray-200" aria-label="Close edit dialog">
                ✕
            </button>
        </div>

        <form method="POST" :action="'/events/' + editing.id" class="space-y-6">
            @csrf
            @method('PUT')
            @include('city_events.partials.form-fields', ['model' => 'editing'])

            <div class="flex justify-end gap-3">
                <x-secondary-button type="button" @click="closeEdit()">
                    {{ __('Cancel') }}
                </x-secondary-button>
                <x-primary-button class="bg-cyan-600 hover:bg-cyan-500 focus:ring-cyan-500">
                    {{ __('Save changes') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</div>