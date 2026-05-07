<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">
            {{ __('Effect Management') }}
        </h2>
    </x-slot>

    <div class="effects-page">
        <div class="effects-container">

            @if($selectedFunctionId)
                <div class="effects-alert">
                    Direct link active for function ID {{ $selectedFunctionId }}.
                </div>
            @endif

            {{-- Effects Table --}}
            <div class="effects-panel">
                
                {{-- Table Header --}}
                <div class="effects-table-shell">
                    <table class="effects-table">
                        <thead class="effects-table-head">
                            <tr>
                                <th class="effects-head-cell">
                                    Function
                                </th>
                                @foreach($categories as $category)
                                    <th class="effects-head-cell effects-head-cell--center">
                                        {{ $category }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        {{-- Table Body --}}
                        <tbody class="effects-table-body">
                            @forelse($functions as $function)
                                <tr class="effects-row">
                                    {{-- Function Name Cell --}}
                                    <td id="function-{{ $function->id }}" class="effects-function-cell @if($selectedFunctionId === $function->id) effects-function-cell--selected @endif @if($function->trashed()) effects-function-cell--archived @endif">
                                        <div class="effects-function-wrap">
                                            @if($function->image_path)
                                                <img src="{{ asset($function->image_path) }}"
                                                     alt="{{ $function->name }}"
                                                     class="effects-function-image">
                                            @endif
                                            <div class="effects-function-meta">
                                                <span>{{ $function->name }}</span>
                                                @if($function->trashed())
                                                    <span class="effects-archive-badge">
                                                        Archived
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Effect Value Cells --}}
                                    @foreach($categories as $category)
                                        <td class="effects-value-cell">
                                            @if($function->trashed())
                                                <span class="effects-value-badge">
                                                    {{ $function->{$category} > 0 ? '+' : '' }}{{ $function->{$category} }}
                                                </span>
                                            @else
                                                {{-- Inline editing keeps the table editable without a page reload. --}}
                                                <div x-data="{
                                                    editing: false,
                                                    originalValue: {{ $function->{$category} }},
                                                    value: {{ $function->{$category} }},
                                                    error: '',
                                                    isValid() {
                                                        return this.value >= -10 && this.value <= 10;
                                                    },
                                                    validateInput() {
                                                        this.error = '';
                                                        if (this.value < -10 || this.value > 10) {
                                                            this.error = 'Value must be between -10 and 10';
                                                        }
                                                    },
                                                    async save() {
                                                        this.error = '';
                                                        if (this.value < -10 || this.value > 10) {
                                                            this.error = 'Value must be between -10 and 10';
                                                            return;
                                                        }
                                                        try {
                                                            const response = await fetch('{{ route('effects.update', $function->id) }}', {
                                                                method: 'POST',
                                                                headers: {
                                                                    'Content-Type': 'application/json',
                                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                                },
                                                                body: JSON.stringify({
                                                                    category: '{{ $category }}',
                                                                    value: this.value,
                                                                })
                                                            });
                                                            if (!response.ok) {
                                                                this.error = 'Failed to update effect';
                                                                this.value = this.originalValue;
                                                                return;
                                                            }
                                                            // Keep the optimistic value and show a brief confirmation toast.
                                                            this.originalValue = this.value;
                                                            this.editing = false;
                                                            const toast = document.getElementById('effect-toast');
                                                            if (toast) {
                                                                toast.textContent = '{{ $function->name }} effect updated!';
                                                                toast.className = 'effects-toast effects-toast--success';
                                                                setTimeout(() => {
                                                                    toast.className = toast.className + ' hidden';
                                                                }, 3000);
                                                            }
                                                        } catch (err) {
                                                            this.error = 'An error occurred';
                                                            this.value = this.originalValue;
                                                        }
                                                    },
                                                    cancel() {
                                                        this.value = this.originalValue;
                                                        this.error = '';
                                                        this.editing = false;
                                                    },
                                                    getColor() {
                                                        if (this.value > 0) return 'effects-editor-view--positive';
                                                        if (this.value < 0) return 'effects-editor-view--negative';
                                                        return 'effects-editor-view--neutral';
                                                    }
                                                }" class="effects-editor">
                                                    <template x-if="!editing">
                                                        <button
                                                            @click="editing = true"
                                                            :class="'effects-editor-view ' + getColor()"
                                                            title="Click to edit">
                                                            <span x-text="(value > 0 ? '+' : '') + value"></span>
                                                            <svg class="inline ml-1 h-3 w-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                            </svg>
                                                        </button>
                                                    </template>

                                                    <template x-if="editing">
                                                        <div class="effects-editor-input-wrap">
                                                            <div class="effects-editor-controls">
                                                                <input
                                                                    type="number"
                                                                    x-model.number="value"
                                                                    @input="validateInput()"
                                                                    @keydown.enter="save()"
                                                                    @keydown.escape="cancel()"
                                                                    min="-10"
                                                                    max="10"
                                                                    autofocus
                                                                    :class="'effects-editor-input ' + (error ? 'effects-editor-input--error' : 'effects-editor-input--neutral')">
                                                                <button
                                                                    @click="save()"
                                                                    :disabled="!isValid()"
                                                                    :class="'effects-editor-save ' + (isValid() ? 'effects-editor-save--enabled' : 'effects-editor-save--disabled')">
                                                                    Save
                                                                </button>
                                                                <button
                                                                    @click="cancel()"
                                                                    class="effects-editor-cancel">
                                                                    Cancel
                                                                </button>
                                                            </div>
                                                            <template x-if="error">
                                                                <div class="effects-editor-error">
                                                                    <span x-text="error"></span>
                                                                </div>
                                                            </template>
                                                            <div class="effects-editor-range">
                                                                Range: -10 to +10
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($categories) + 1 }}" class="effects-empty-cell">
                                        {{ __('No functions found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>

            {{-- Legend --}}
            <div class="effects-legend">
                <h3 class="effects-legend-title">Legend</h3>
                <div class="effects-legend-items">
                    <div class="effects-legend-item">
                        <span class="effects-legend-key effects-legend-key--positive">+ values</span>
                        <span class="effects-legend-note">Positive effect</span>
                    </div>
                    <div class="effects-legend-item">
                        <span class="effects-legend-key effects-legend-key--negative">- values</span>
                        <span class="effects-legend-note">Negative effect</span>
                    </div>
                    <div class="effects-legend-item">
                        <span class="effects-legend-key effects-legend-key--neutral">0</span>
                        <span class="effects-legend-note">No effect</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Effect Toast Notification --}}
    <div id="effect-toast"
            class="effects-toast hidden">
    </div>

</x-app-layout>
