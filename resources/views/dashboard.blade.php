<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 hc:text-white dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h1>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white hc:bg-black hc:border hc:border-white dark:bg-gray-800 overflow-hidden shadow-sm hc:shadow-none sm:rounded-lg">
                <div class="p-6 text-gray-900 hc:text-white dark:text-gray-100">
                    {{ __("You're logged in!") }}
                </div>
            </div>

            {{-- Keyboard Navigation Reference --}}
            <div class="bg-white hc:bg-black hc:border hc:border-white dark:bg-gray-800 overflow-hidden shadow-sm hc:shadow-none sm:rounded-lg">
                <div class="p-6">

                    <p tabindex="0" class="text-lg font-bold text-gray-800 hc:text-white dark:text-gray-100 mb-1 focus:outline-none focus:ring-2 focus:ring-blue-400 hc:focus:ring-yellow-400 rounded">
                        Keyboard Navigation
                    </p>
                    <p tabindex="0" class="text-sm text-gray-500 hc:text-white dark:text-gray-400 mb-6 focus:outline-none focus:ring-2 focus:ring-blue-400 hc:focus:ring-yellow-400 rounded">
                        Every feature on this site is accessible without a mouse. Use the shortcuts below to navigate the full application.
                    </p>

                    <div class="space-y-8">

                        {{-- General --}}
                        <section aria-labelledby="kb-general">
                            <h3 id="kb-general" class="text-sm font-semibold uppercase tracking-wide text-gray-500 hc:text-white dark:text-gray-400 mb-3">
                                General
                            </h3>
                            <table class="w-full text-sm border-collapse" role="table">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-left font-semibold text-gray-700 hc:text-white dark:text-gray-200 py-2 pr-6 w-48 border-b border-gray-200 hc:border-white dark:border-gray-600">Key</th>
                                        <th scope="col" class="text-left font-semibold text-gray-700 hc:text-white dark:text-gray-200 py-2 border-b border-gray-200 hc:border-white dark:border-gray-600">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 hc:divide-white dark:divide-gray-700">
                                    <tr tabindex="0" aria-label="Tab — Move focus to the next interactive element (link, button, input)" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Tab</kbd>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Move focus to the next interactive element (link, button, input)</td>
                                    </tr>
                                    <tr tabindex="0" aria-label="Shift + Tab — Move focus to the previous interactive element" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Shift</kbd>
                                            <span class="text-gray-400 hc:text-white dark:text-gray-400 mx-1">+</span>
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Tab</kbd>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Move focus to the previous interactive element</td>
                                    </tr>
                                    <tr tabindex="0" aria-label="Enter or Space — Activate the focused link or button" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Enter</kbd>
                                            <span class="text-gray-400 hc:text-white dark:text-gray-400 mx-1">or</span>
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Space</kbd>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Activate the focused link or button</td>
                                    </tr>
                                </tbody>
                            </table>
                        </section>

                        {{-- Function Library --}}
                        <section aria-labelledby="kb-library">
                            <h3 id="kb-library" class="text-sm font-semibold uppercase tracking-wide text-gray-500 hc:text-white dark:text-gray-400 mb-3">
                                Function Library (Grid page)
                            </h3>
                            <table class="w-full text-sm border-collapse" role="table">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-left font-semibold text-gray-700 hc:text-white dark:text-gray-200 py-2 pr-6 w-48 border-b border-gray-200 hc:border-white dark:border-gray-600">Key</th>
                                        <th scope="col" class="text-left font-semibold text-gray-700 hc:text-white dark:text-gray-200 py-2 border-b border-gray-200 hc:border-white dark:border-gray-600">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 hc:divide-white dark:divide-gray-700">
                                    <tr tabindex="0" aria-label="Tab or Shift+Tab — Move between function cards in the library" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Tab</kbd>
                                            <span class="text-gray-400 hc:text-white dark:text-gray-400 mx-1">/</span>
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Shift+Tab</kbd>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Move between function cards in the library</td>
                                    </tr>
                                    <tr tabindex="0" aria-label="Enter or Space — Select or deselect a function card for placement" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Enter</kbd>
                                            <span class="text-gray-400 hc:text-white dark:text-gray-400 mx-1">or</span>
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Space</kbd>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Select or deselect a function card for placement</td>
                                    </tr>
                                </tbody>
                            </table>
                        </section>

                        {{-- Grid --}}
                        <section aria-labelledby="kb-grid">
                            <h3 id="kb-grid" class="text-sm font-semibold uppercase tracking-wide text-gray-500 hc:text-white dark:text-gray-400 mb-3">
                                City Grid (Grid page)
                            </h3>
                            <table class="w-full text-sm border-collapse" role="table">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-left font-semibold text-gray-700 hc:text-white dark:text-gray-200 py-2 pr-6 w-48 border-b border-gray-200 hc:border-white dark:border-gray-600">Key</th>
                                        <th scope="col" class="text-left font-semibold text-gray-700 hc:text-white dark:text-gray-200 py-2 border-b border-gray-200 hc:border-white dark:border-gray-600">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 hc:divide-white dark:divide-gray-700">
                                    <tr tabindex="0" aria-label="Arrow keys — Move focus between grid cells" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">↑</kbd>
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">↓</kbd>
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">←</kbd>
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">→</kbd>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Move focus between grid cells</td>
                                    </tr>
                                    <tr tabindex="0" aria-label="Enter or Space — Place the selected library function on an empty cell, or pick up a function already on the grid to move or remove it" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Enter</kbd>
                                            <span class="text-gray-400 hc:text-white dark:text-gray-400 mx-1">or</span>
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Space</kbd>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">
                                            Place the selected library function on an empty cell — or pick up a function already on the grid to move or remove it
                                        </td>
                                    </tr>
                                    <tr tabindex="0" aria-label="Delete or Backspace — Remove the function from the focused cell" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Delete</kbd>
                                            <span class="text-gray-400 hc:text-white dark:text-gray-400 mx-1">or</span>
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Backspace</kbd>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Remove the function from the focused cell</td>
                                    </tr>
                                    <tr tabindex="0" aria-label="Escape — Cancel picking up a cell and put it back" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Escape</kbd>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Cancel picking up a cell (put it back)</td>
                                    </tr>
                                </tbody>
                            </table>
                        </section>

                        {{-- Saving and loading --}}
                        <section aria-labelledby="kb-save">
                            <h3 id="kb-save" class="text-sm font-semibold uppercase tracking-wide text-gray-500 hc:text-white dark:text-gray-400 mb-3">
                                Saving &amp; Loading
                            </h3>
                            <p class="text-sm text-gray-700 hc:text-white dark:text-gray-300">
                                Changes to the grid are saved automatically after each action — no manual save is needed. To reload the current state, navigate back to the Grid page using the navigation bar at the top.
                            </p>
                        </section>

                        {{-- Simulation --}}
                        <section aria-labelledby="kb-simulation">
                            <h3 id="kb-simulation" class="text-sm font-semibold uppercase tracking-wide text-gray-500 hc:text-white dark:text-gray-400 mb-3">
                                Simulation Controls (Grid page)
                            </h3>
                            <table class="w-full text-sm border-collapse" role="table">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-left font-semibold text-gray-700 hc:text-white dark:text-gray-200 py-2 pr-6 w-48 border-b border-gray-200 hc:border-white dark:border-gray-600">Key</th>
                                        <th scope="col" class="text-left font-semibold text-gray-700 hc:text-white dark:text-gray-200 py-2 border-b border-gray-200 hc:border-white dark:border-gray-600">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 hc:divide-white dark:divide-gray-700">
                                    <tr tabindex="0" aria-label="Tab to the Play or Pause button, then Enter — Play or pause the simulation" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Tab</kbd>
                                            <span class="text-gray-400 hc:text-white dark:text-gray-400 mx-1">to button, then</span>
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Enter</kbd>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Play or pause the simulation</td>
                                    </tr>
                                    <tr tabindex="0" aria-label="Tab to a speed button, then Enter — Set simulation speed to 1x, 2x, or 5x" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Tab</kbd>
                                            <span class="text-gray-400 hc:text-white dark:text-gray-400 mx-1">to speed button, then</span>
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Enter</kbd>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Set simulation speed (1×, 2×, or 5×)</td>
                                    </tr>
                                    <tr tabindex="0" aria-label="Tab to the Skip input, type an amount, Tab to the Skip button, then Enter — Skip the simulation forward by a set amount of time" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Tab</kbd>
                                            <span class="text-gray-400 hc:text-white dark:text-gray-400 mx-1">to Skip input, type amount, then Tab to</span>
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Skip</kbd>
                                            <span class="text-gray-400 hc:text-white dark:text-gray-400 mx-1">button and press</span>
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Enter</kbd>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Skip the simulation forward by a set amount of time</td>
                                    </tr>
                                </tbody>
                            </table>
                        </section>

                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
