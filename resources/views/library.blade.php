<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Library') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-blue-50 dark:bg-gray-700 rounded-2xl p-6 shadow-sm"
                 x-data="{ active: 'All' }">

                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4">Function Library</h3>

                @if($cityFunctions->isEmpty())
                    <p class="text-gray-500 dark:text-gray-400">{{ __('No city functions found.') }}</p>
                @else
                    {{-- Category filter dropdown --}}
                    <div class="mb-6">
                        <select x-model="active"
                                class="bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="All">All categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- City functions grid --}}
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(96px, 1fr)); gap: 1rem;">
                        @foreach($cityFunctions as $cityFunction)
                            <div x-show="active === 'All' || active === '{{ $cityFunction->category }}'"
                                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm flex flex-col items-center justify-center p-4 cursor-pointer hover:shadow-md transition">
                                @if($cityFunction->image_path)
                                    <img src="{{ asset($cityFunction->image_path) }}"
                                         alt="{{ $cityFunction->name }}"
                                         class="w-16 h-16 object-contain mb-2">
                                @endif
                                <span class="text-xs font-semibold text-center text-white">{{ $cityFunction->name }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
