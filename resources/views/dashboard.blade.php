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
                            <div id="kb-general" tabindex="0" class="text-sm font-semibold uppercase tracking-wide text-gray-500 hc:text-white dark:text-gray-400 mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400 hc:focus:ring-yellow-400 rounded">
                                General
                            </div>
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

                        {{-- Site Navigation --}}
                        <section aria-labelledby="kb-sitenav">
                            <div id="kb-sitenav" tabindex="0" class="text-sm font-semibold uppercase tracking-wide text-gray-500 hc:text-white dark:text-gray-400 mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400 hc:focus:ring-yellow-400 rounded">
                                Site Navigation (all pages)
                            </div>
                            <table class="w-full text-sm border-collapse" role="table">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-left font-semibold text-gray-700 hc:text-white dark:text-gray-200 py-2 pr-6 w-48 border-b border-gray-200 hc:border-white dark:border-gray-600">Key</th>
                                        <th scope="col" class="text-left font-semibold text-gray-700 hc:text-white dark:text-gray-200 py-2 border-b border-gray-200 hc:border-white dark:border-gray-600">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 hc:divide-white dark:divide-gray-700">
                                    <tr tabindex="0" aria-label="Tab or Shift+Tab — Move between links in the top navigation bar" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Tab</kbd>
                                            <span class="text-gray-400 hc:text-white dark:text-gray-400 mx-1">/</span>
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Shift+Tab</kbd>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Move between links in the top navigation bar</td>
                                    </tr>
                                    <tr tabindex="0" aria-label="Enter — Open the focused page" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Enter</kbd>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Open the focused page</td>
                                    </tr>
                                    <tr tabindex="0" aria-label="Tab to your username at the top right, then Enter — Open the account menu to reach your Profile or Log Out" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Tab</kbd>
                                            <span class="text-gray-400 hc:text-white dark:text-gray-400 mx-1">to username, then</span>
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Enter</kbd>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Open the account menu (Profile &amp; Log Out)</td>
                                    </tr>
                                </tbody>
                            </table>
                        </section>

                        {{-- Modals --}}
                        <section aria-labelledby="kb-modals">
                            <div id="kb-modals" tabindex="0" class="text-sm font-semibold uppercase tracking-wide text-gray-500 hc:text-white dark:text-gray-400 mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400 hc:focus:ring-yellow-400 rounded">
                                Dialogs &amp; Modals (all pages)
                            </div>
                            <p tabindex="0" class="text-sm text-gray-700 hc:text-white dark:text-gray-300 mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400 hc:focus:ring-yellow-400 rounded">
                                Every create, edit, and delete dialog on this site traps focus — you cannot accidentally tab outside it. The same shortcuts work in all dialogs.
                            </p>
                            <table class="w-full text-sm border-collapse" role="table">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-left font-semibold text-gray-700 hc:text-white dark:text-gray-200 py-2 pr-6 w-48 border-b border-gray-200 hc:border-white dark:border-gray-600">Key</th>
                                        <th scope="col" class="text-left font-semibold text-gray-700 hc:text-white dark:text-gray-200 py-2 border-b border-gray-200 hc:border-white dark:border-gray-600">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 hc:divide-white dark:divide-gray-700">
                                    <tr tabindex="0" aria-label="Tab or Shift+Tab — Move between form fields inside the open dialog" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Tab</kbd>
                                            <span class="text-gray-400 hc:text-white dark:text-gray-400 mx-1">/</span>
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Shift+Tab</kbd>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Move between form fields inside the open dialog</td>
                                    </tr>
                                    <tr tabindex="0" aria-label="Enter or Space when a button is focused — Submit the form or confirm the action" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Enter</kbd>
                                            <span class="text-gray-400 hc:text-white dark:text-gray-400 mx-1">or</span>
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Space</kbd>
                                            <span class="text-gray-400 hc:text-white dark:text-gray-400 mx-1">(on a button)</span>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Submit the form or confirm the action</td>
                                    </tr>
                                    <tr tabindex="0" aria-label="Escape — Close the dialog without saving" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Escape</kbd>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Close the dialog without saving</td>
                                    </tr>
                                </tbody>
                            </table>
                        </section>

                        {{-- Function Library --}}
                        <section aria-labelledby="kb-library">
                            <div id="kb-library" tabindex="0" class="text-sm font-semibold uppercase tracking-wide text-gray-500 hc:text-white dark:text-gray-400 mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400 hc:focus:ring-yellow-400 rounded">
                                Function Library (Grid page)
                            </div>
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
                            <div id="kb-grid" tabindex="0" class="text-sm font-semibold uppercase tracking-wide text-gray-500 hc:text-white dark:text-gray-400 mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400 hc:focus:ring-yellow-400 rounded">
                                City Grid (Grid page)
                            </div>
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
                            <div id="kb-save" tabindex="0" class="text-sm font-semibold uppercase tracking-wide text-gray-500 hc:text-white dark:text-gray-400 mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400 hc:focus:ring-yellow-400 rounded">
                                Saving &amp; Loading
                            </div>
                            <p tabindex="0" class="text-sm text-gray-700 hc:text-white dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400 hc:focus:ring-yellow-400 rounded">
                                Changes to the grid are saved automatically after each action — no manual save is needed. To reload the current state, navigate back to the Grid page using the navigation bar at the top.
                            </p>
                        </section>

                        {{-- Simulation --}}
                        <section aria-labelledby="kb-simulation">
                            <div id="kb-simulation" tabindex="0" class="text-sm font-semibold uppercase tracking-wide text-gray-500 hc:text-white dark:text-gray-400 mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400 hc:focus:ring-yellow-400 rounded">
                                Simulation Controls (Grid page)
                            </div>
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

                        {{-- City Events page --}}
                        <section aria-labelledby="kb-cityevents">
                            <div id="kb-cityevents" tabindex="0" class="text-sm font-semibold uppercase tracking-wide text-gray-500 hc:text-white dark:text-gray-400 mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400 hc:focus:ring-yellow-400 rounded">
                                Events page
                            </div>
                            <p tabindex="0" class="text-sm text-gray-700 hc:text-white dark:text-gray-300 mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400 hc:focus:ring-yellow-400 rounded">
                                The Events page has a create form on the left and your existing events on the right. Use it to add one-off or recurring city events.
                            </p>
                            <table class="w-full text-sm border-collapse" role="table">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-left font-semibold text-gray-700 hc:text-white dark:text-gray-200 py-2 pr-6 w-48 border-b border-gray-200 hc:border-white dark:border-gray-600">Key</th>
                                        <th scope="col" class="text-left font-semibold text-gray-700 hc:text-white dark:text-gray-200 py-2 border-b border-gray-200 hc:border-white dark:border-gray-600">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 hc:divide-white dark:divide-gray-700">
                                    <tr tabindex="0" aria-label="Tab through the create form — Move between the Name, Type, Start Date, End Date, and Function fields" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Tab</kbd>
                                            <span class="text-gray-400 hc:text-white dark:text-gray-400 mx-1">through create form</span>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Move between the Name, Type, Date, and Function fields</td>
                                    </tr>
                                    <tr tabindex="0" aria-label="Tab to the Create Event button, then Enter — Submit the new event" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Tab</kbd>
                                            <span class="text-gray-400 hc:text-white dark:text-gray-400 mx-1">to "Create Event", then</span>
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Enter</kbd>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Submit and create the new event</td>
                                    </tr>
                                    <tr tabindex="0" aria-label="Tab to an Edit button in the event list, then Enter — Open the Edit Event dialog" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Tab</kbd>
                                            <span class="text-gray-400 hc:text-white dark:text-gray-400 mx-1">to "Edit" in event list, then</span>
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Enter</kbd>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Open the Edit Event dialog — Tab through fields, Enter to save, Escape to cancel</td>
                                    </tr>
                                    <tr tabindex="0" aria-label="Tab to a Delete button in the event list, then Enter — Open the Delete confirmation dialog for that event" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Tab</kbd>
                                            <span class="text-gray-400 hc:text-white dark:text-gray-400 mx-1">to "Delete" in event list, then</span>
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Enter</kbd>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Open the Delete confirmation — Tab to "Delete" then Enter to confirm, or Escape to cancel</td>
                                    </tr>
                                </tbody>
                            </table>
                        </section>

                        {{-- City Functions page --}}
                        <section aria-labelledby="kb-cityfunctions">
                            <div id="kb-cityfunctions" tabindex="0" class="text-sm font-semibold uppercase tracking-wide text-gray-500 hc:text-white dark:text-gray-400 mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400 hc:focus:ring-yellow-400 rounded">
                                City Functions page
                            </div>
                            <p tabindex="0" class="text-sm text-gray-700 hc:text-white dark:text-gray-300 mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400 hc:focus:ring-yellow-400 rounded">
                                Use this page to create, edit, and delete city functions. Functions can then be placed on the Grid.
                            </p>
                            <table class="w-full text-sm border-collapse" role="table">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-left font-semibold text-gray-700 hc:text-white dark:text-gray-200 py-2 pr-6 w-48 border-b border-gray-200 hc:border-white dark:border-gray-600">Key</th>
                                        <th scope="col" class="text-left font-semibold text-gray-700 hc:text-white dark:text-gray-200 py-2 border-b border-gray-200 hc:border-white dark:border-gray-600">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 hc:divide-white dark:divide-gray-700">
                                    <tr tabindex="0" aria-label="Tab to the Create Function button, then Enter or Space — Open the Create Function dialog" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Tab</kbd>
                                            <span class="text-gray-400 hc:text-white dark:text-gray-400 mx-1">to "+ Create Function", then</span>
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Enter</kbd>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Open the Create Function dialog</td>
                                    </tr>
                                    <tr tabindex="0" aria-label="Inside the Create dialog, Tab — Move through the fields: Name, Category, Description, then the Create button" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Tab</kbd>
                                            <span class="text-gray-400 hc:text-white dark:text-gray-400 mx-1">(inside dialog)</span>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Move through the fields: Name → Category → Description → Create button</td>
                                    </tr>
                                    <tr tabindex="0" aria-label="Tab to a function row — Read the function name, category, and description with a screen reader" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Tab</kbd>
                                            <span class="text-gray-400 hc:text-white dark:text-gray-400 mx-1">to a function row</span>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Read the function's name, category, and description</td>
                                    </tr>
                                    <tr tabindex="0" aria-label="Tab to the Edit button in a row, then Enter or Space — Open the Edit Function dialog for that function" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Tab</kbd>
                                            <span class="text-gray-400 hc:text-white dark:text-gray-400 mx-1">to "Edit" button, then</span>
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Enter</kbd>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Open the Edit Function dialog — change name, category, description, QoL values, and adjacency rules</td>
                                    </tr>
                                    <tr tabindex="0" aria-label="Tab to the Delete button in a row, then Enter or Space — Open the Delete confirmation dialog for that function" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Tab</kbd>
                                            <span class="text-gray-400 hc:text-white dark:text-gray-400 mx-1">to "Delete" button, then</span>
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Enter</kbd>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Open the Delete confirmation dialog — Tab to "Delete" then Enter to confirm, or Escape to cancel</td>
                                    </tr>
                                </tbody>
                            </table>
                        </section>

                        {{-- Effects page --}}
                        <section aria-labelledby="kb-effects">
                            <div id="kb-effects" tabindex="0" class="text-sm font-semibold uppercase tracking-wide text-gray-500 hc:text-white dark:text-gray-400 mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400 hc:focus:ring-yellow-400 rounded">
                                Effects page
                            </div>
                            <p tabindex="0" class="text-sm text-gray-700 hc:text-white dark:text-gray-300 mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400 hc:focus:ring-yellow-400 rounded">
                                Use the Effects page to set quality-of-life values (−10 to 10) for each city function per category. Values affect the QoL score shown on the Grid page.
                            </p>
                            <table class="w-full text-sm border-collapse" role="table">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-left font-semibold text-gray-700 hc:text-white dark:text-gray-200 py-2 pr-6 w-48 border-b border-gray-200 hc:border-white dark:border-gray-600">Key</th>
                                        <th scope="col" class="text-left font-semibold text-gray-700 hc:text-white dark:text-gray-200 py-2 border-b border-gray-200 hc:border-white dark:border-gray-600">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 hc:divide-white dark:divide-gray-700">
                                    <tr tabindex="0" aria-label="Tab — Move between function names and effect value fields in the table" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Tab</kbd>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Move between function names and effect value fields in the table</td>
                                    </tr>
                                    <tr tabindex="0" aria-label="Type a number when an effect field is focused — Enter a value between negative 10 and 10" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <span class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">0–9</span>
                                            <span class="text-gray-400 hc:text-white dark:text-gray-400 mx-1">/ −</span>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Type a value between −10 and 10 when an effect field is focused</td>
                                    </tr>
                                    <tr tabindex="0" aria-label="Tab to the next field or press Enter — Save the entered value and move on" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Tab</kbd>
                                            <span class="text-gray-400 hc:text-white dark:text-gray-400 mx-1">or</span>
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Enter</kbd>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Save the entered value and move to the next field</td>
                                    </tr>
                                </tbody>
                            </table>
                        </section>

                        {{-- Profile & Account --}}
                        <section aria-labelledby="kb-profile">
                            <div id="kb-profile" tabindex="0" class="text-sm font-semibold uppercase tracking-wide text-gray-500 hc:text-white dark:text-gray-400 mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400 hc:focus:ring-yellow-400 rounded">
                                Profile &amp; Account
                            </div>
                            <table class="w-full text-sm border-collapse" role="table">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-left font-semibold text-gray-700 hc:text-white dark:text-gray-200 py-2 pr-6 w-48 border-b border-gray-200 hc:border-white dark:border-gray-600">Key</th>
                                        <th scope="col" class="text-left font-semibold text-gray-700 hc:text-white dark:text-gray-200 py-2 border-b border-gray-200 hc:border-white dark:border-gray-600">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 hc:divide-white dark:divide-gray-700">
                                    <tr tabindex="0" aria-label="Tab to your username at the top right, then Enter — Open the account dropdown menu" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Tab</kbd>
                                            <span class="text-gray-400 hc:text-white dark:text-gray-400 mx-1">to username, then</span>
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Enter</kbd>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Open the account dropdown menu</td>
                                    </tr>
                                    <tr tabindex="0" aria-label="Tab to Profile in the dropdown, then Enter — Go to the Profile settings page" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Tab</kbd>
                                            <span class="text-gray-400 hc:text-white dark:text-gray-400 mx-1">to "Profile", then</span>
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Enter</kbd>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Go to the Profile settings page</td>
                                    </tr>
                                    <tr tabindex="0" aria-label="Tab through profile fields — Move between Name, Email, and Password fields" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Tab</kbd>
                                            <span class="text-gray-400 hc:text-white dark:text-gray-400 mx-1">through fields</span>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Move between Name, Email, and Password fields</td>
                                    </tr>
                                    <tr tabindex="0" aria-label="Tab to Save, then Enter — Save your profile changes" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Tab</kbd>
                                            <span class="text-gray-400 hc:text-white dark:text-gray-400 mx-1">to "Save", then</span>
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Enter</kbd>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Save your profile changes</td>
                                    </tr>
                                    <tr tabindex="0" aria-label="Tab to Log Out in the dropdown, then Enter — Sign out of the application" class="focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400 hc:focus:ring-yellow-400">
                                        <td class="py-2.5 pr-6 align-top" aria-hidden="true">
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Tab</kbd>
                                            <span class="text-gray-400 hc:text-white dark:text-gray-400 mx-1">to "Log Out", then</span>
                                            <kbd class="inline-block px-2 py-0.5 rounded border border-gray-300 hc:border-white bg-gray-100 hc:bg-black dark:bg-gray-700 dark:border-gray-500 text-gray-800 hc:text-white dark:text-gray-100 font-mono text-xs">Enter</kbd>
                                        </td>
                                        <td class="py-2.5 text-gray-700 hc:text-white dark:text-gray-300" aria-hidden="true">Sign out of the application</td>
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
