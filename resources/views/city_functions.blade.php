<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('City Functions') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="px-4 sm:px-6 lg:px-8 flex justify-center">

            @if($cityFunctions->isEmpty())
                <p class="text-white">{{ __('No city functions found.') }}</p>
            @else
                <div class="bg-gray-800 rounded-2xl shadow-sm overflow-hidden w-fit">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead class="bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Image</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            @foreach($cityFunctions as $fn)
                                <tr>
                                    <td class="px-6 py-4">
                                        @if($fn->image_path)
                                            <img src="{{ asset($fn->image_path) }}"
                                                 alt="{{ $fn->name }}"
                                                 class="w-12 h-12 object-contain rounded">
                                        @else
                                            <div class="w-12 h-12 bg-gray-700 rounded flex items-center justify-center text-white text-xs">—</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-white whitespace-nowrap">
                                        {{ $fn->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-block px-2 py-1 text-xs font-medium rounded-full bg-blue-900/40 text-white">
                                            {{ $fn->category ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-white max-w-md">
                                        {{ $fn->description ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
