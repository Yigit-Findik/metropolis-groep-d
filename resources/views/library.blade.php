<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Library') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-blue-50 dark:bg-gray-700 rounded-2xl p-6 shadow-sm">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-6">Function Library</h3>

                @if($cityFunctions->isEmpty())
                    <p class="text-gray-500 dark:text-gray-400">{{ __('No city functions found.') }}</p>
                @else
                    <div class="flex flex-wrap gap-4">
                        @foreach($cityFunctions as $cityFunction)
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm flex flex-col items-center justify-center p-4 cursor-pointer hover:shadow-md transition w-24">
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
