<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                :root {
                    color-scheme: light dark;
                }

                body {
                    margin: 0;
                    font-family: Figtree, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                    color: #0f172a;
                    background:
                        radial-gradient(circle at top, rgba(59, 130, 246, 0.12), transparent 35%),
                        linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%);
                }

                a {
                    color: #1d4ed8;
                }

                input[type="text"],
                input[type="email"],
                input[type="password"] {
                    width: 100%;
                    box-sizing: border-box;
                    margin-top: 0.25rem;
                    border: 1px solid #cbd5e1;
                    border-radius: 0.75rem;
                    padding: 0.75rem 0.875rem;
                    background: rgba(255, 255, 255, 0.96);
                    color: #0f172a;
                    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
                }

                input[type="text"]:focus,
                input[type="email"]:focus,
                input[type="password"]:focus {
                    outline: none;
                    border-color: #2563eb;
                    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.18);
                }

                label {
                    display: inline-flex;
                    align-items: center;
                    gap: 0.5rem;
                    font-size: 0.875rem;
                    font-weight: 600;
                    color: #334155;
                }

                .min-h-screen {
                    min-height: 100vh;
                }

                .flex {
                    display: flex;
                }

                .flex-col {
                    flex-direction: column;
                }

                .sm\:justify-center {
                    justify-content: center;
                }

                .items-center {
                    align-items: center;
                }

                .pt-6 {
                    padding-top: 1.5rem;
                }

                .sm\:pt-0 {
                    padding-top: 0;
                }

                .bg-gray-100 {
                    background-color: transparent;
                }

                .dark\:bg-gray-900 {
                    background-color: transparent;
                }

                .w-full {
                    width: 100%;
                }

                .sm\:max-w-md {
                    max-width: 28rem;
                }

                .mt-6 {
                    margin-top: 1.5rem;
                }

                .px-6 {
                    padding-left: 1.5rem;
                    padding-right: 1.5rem;
                }

                .py-4 {
                    padding-top: 1rem;
                    padding-bottom: 1rem;
                }

                .bg-white {
                    background: rgba(255, 255, 255, 0.96);
                }

                .shadow-md {
                    box-shadow: 0 18px 50px rgba(15, 23, 42, 0.12);
                }

                .overflow-hidden {
                    overflow: hidden;
                }

                .sm\:rounded-lg {
                    border-radius: 1rem;
                }

                .rounded-md {
                    border-radius: 0.75rem;
                }

                .rounded {
                    border-radius: 0.5rem;
                }

                .mt-1 {
                    margin-top: 0.25rem;
                }

                .mt-2 {
                    margin-top: 0.5rem;
                }

                .mt-4 {
                    margin-top: 1rem;
                }

                .ms-2 {
                    margin-left: 0.5rem;
                }

                .ms-3 {
                    margin-left: 0.75rem;
                }

                .me-2 {
                    margin-right: 0.5rem;
                }

                .justify-end {
                    justify-content: flex-end;
                }

                .text-sm {
                    font-size: 0.875rem;
                }

                .text-gray-600 {
                    color: #475569;
                }

                .text-gray-400 {
                    color: #94a3b8;
                }

                .text-gray-900 {
                    color: #0f172a;
                }

                .bg-gray-800 {
                    background: rgba(15, 23, 42, 0.92);
                }

                .dark\:bg-gray-800 {
                    background: rgba(15, 23, 42, 0.92);
                }

                .dark\:text-gray-300 {
                    color: #cbd5e1;
                }

                .dark\:text-gray-400 {
                    color: #94a3b8;
                }

                .dark\:text-gray-900 {
                    color: #0f172a;
                }

                .dark\:border-gray-700 {
                    border-color: #334155;
                }

                .dark\:bg-gray-900 {
                    background-color: #0f172a;
                }

                .dark\:bg-gray-800 input {
                    background: #0f172a;
                    color: #e2e8f0;
                    border-color: #334155;
                }

                .dark\:bg-gray-800 label,
                .dark\:bg-gray-800 .text-gray-600,
                .dark\:bg-gray-800 .text-gray-400 {
                    color: #cbd5e1;
                }

                .underline {
                    text-decoration: underline;
                    text-underline-offset: 0.2em;
                }

                .inline-flex {
                    display: inline-flex;
                }

                .items-center {
                    align-items: center;
                }

                .px-4 {
                    padding-left: 1rem;
                    padding-right: 1rem;
                }

                .py-2 {
                    padding-top: 0.5rem;
                    padding-bottom: 0.5rem;
                }

                .bg-gray-800 button,
                .bg-gray-800 .inline-flex.items-center.px-4.py-2,
                button.inline-flex.items-center.px-4.py-2 {
                    border: none;
                    background: #1f2937;
                    color: white;
                    font-size: 0.75rem;
                    font-weight: 700;
                    letter-spacing: 0.08em;
                    text-transform: uppercase;
                    padding: 0.75rem 1rem;
                    border-radius: 0.75rem;
                    transition: transform 0.15s ease, background-color 0.15s ease;
                    cursor: pointer;
                }

                button:hover,
                .inline-flex.items-center.px-4.py-2:hover {
                    transform: translateY(-1px);
                    background: #111827;
                }

                .w-20 {
                    width: 5rem;
                }

                .h-20 {
                    height: 5rem;
                }

                .fill-current {
                    fill: currentColor;
                }

                .text-gray-500 {
                    color: #64748b;
                }
            </style>
        @endif
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
