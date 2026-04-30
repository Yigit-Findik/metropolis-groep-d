<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Effect Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="px-4 sm:px-6 lg:px-8">

            {{-- Effects Table --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden">
                
                {{-- Table Header --}}
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-blue-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-800 dark:text-gray-100">
                                    Function
                                </th>
                                @foreach($categories as $category)
                                    <th class="px-6 py-3 text-center text-sm font-semibold text-gray-800 dark:text-gray-100">
                                        {{ $category }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        {{-- Table Body --}}
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($functions as $function)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    {{-- Function Name Cell --}}
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                        @if($function->image_path)
                                            <div class="flex items-center gap-3">
                                                <img src="{{ asset($function->image_path) }}" 
                                                     alt="{{ $function->name }}"
                                                     class="w-8 h-8 object-contain">
                                                <span>{{ $function->name }}</span>
                                            </div>
                                        @else
                                            {{ $function->name }}
                                        @endif
                                    </td>

                                    {{-- Effect Value Cells --}}
                                    @foreach($categories as $category)
                                        <td class="px-6 py-4 text-center">
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
                                                        this.originalValue = this.value;
                                                        this.editing = false;
                                                        // Show success notification
                                                        const toast = document.getElementById('effect-toast');
                                                        if (toast) {
                                                            toast.textContent = '{{ $function->name }} effect updated!';
                                                            toast.className = 'fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl shadow-lg text-white text-sm font-semibold bg-green-500 transition-all duration-300';
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
                                                    if (this.value > 0) return 'text-green-600 dark:text-green-400';
                                                    if (this.value < 0) return 'text-red-600 dark:text-red-400';
                                                    return 'text-gray-500 dark:text-gray-400';
                                                }
                                            }" class="flex justify-center">
                                                {{-- Display Mode --}}
                                                <template x-if="!editing">
                                                    <button
                                                        @click="editing = true"
                                                        :class="'text-sm font-semibold px-3 py-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer ' + getColor()"
                                                        title="Click to edit">
                                                        <span x-text="(value > 0 ? '+' : '') + value"></span>
                                                        <svg class="w-3 h-3 inline ml-1 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                    </button>
                                                </template>

                                                {{-- Edit Mode --}}
                                                <template x-if="editing">
                                                    <div class="flex flex-col gap-2">
                                                        <div class="flex gap-2">
                                                            <input
                                                                type="number"
                                                                x-model.number="value"
                                                                @input="validateInput()"
                                                                @keydown.enter="save()"
                                                                @keydown.escape="cancel()"
                                                                min="-10"
                                                                max="10"
                                                                autofocus
                                                                :class="'w-16 px-2 py-1 text-sm rounded border focus:outline-none focus:ring-2 dark:bg-gray-700 dark:text-white ' + (error ? 'border-red-500 dark:border-red-500 focus:ring-red-500' : 'border-gray-300 dark:border-gray-600 focus:ring-blue-500')">
                                                            <button
                                                                @click="save()"
                                                                :disabled="!isValid()"
                                                                :class="'px-2 py-1 text-xs rounded transition ' + (isValid() ? 'bg-green-600 text-white hover:bg-green-700' : 'bg-gray-300 text-gray-500 cursor-not-allowed')">
                                                                Save
                                                            </button>
                                                            <button
                                                                @click="cancel()"
                                                                class="px-2 py-1 text-xs bg-gray-400 text-white rounded hover:bg-gray-500 transition">
                                                                Cancel
                                                            </button>
                                                        </div>
                                                        <template x-if="error">
                                                            <div class="text-xs text-red-600 dark:text-red-400 font-medium bg-red-50 dark:bg-red-900/20 px-2 py-1 rounded">
                                                                <span x-text="error"></span>
                                                            </div>
                                                        </template>
                                                        <div class="text-xs text-gray-600 dark:text-gray-400">
                                                            Range: -10 to +10
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($categories) + 1 }}" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        {{ __('No functions found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>

            {{-- Legend --}}
            <div class="mt-6 bg-blue-50 dark:bg-gray-700/50 rounded-2xl p-6">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-3">Legend</h3>
                <div class="flex flex-wrap gap-6">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-green-600 dark:text-green-400">+ values</span>
                        <span class="text-xs text-gray-600 dark:text-gray-400">Positive effect</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-red-600 dark:text-red-400">- values</span>
                        <span class="text-xs text-gray-600 dark:text-gray-400">Negative effect</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">0</span>
                        <span class="text-xs text-gray-600 dark:text-gray-400">No effect</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Effect Toast Notification --}}
    <div id="effect-toast"
         class="fixed bottom-6 right-6 z-50 hidden px-5 py-3 rounded-xl shadow-lg text-white text-sm font-semibold transition-all duration-300">
    </div>

</x-app-layout>
