<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Access Denied — {{ config('app.name') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100 dark:bg-gray-900">
        <div class="min-h-screen flex flex-col items-center justify-center px-4">
            <div class="w-full max-w-md bg-white dark:bg-gray-800 shadow-md rounded-lg px-8 py-10 text-center">
                <p class="text-6xl font-bold text-red-500 mb-4">403</p>

                <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100 mb-2">
                    Access Denied
                </h1>

                <p class="text-gray-600 dark:text-gray-400 mb-8">
                    You do not have permission to view this page.
                    Please contact your administrator if you think this is a mistake.
                </p>

                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ url()->previous('/') }}"
                       class="inline-flex items-center justify-center px-5 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-md font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                        Go Back
                    </a>

                    <a href="{{ route('dashboard') }}"
                       class="inline-flex items-center justify-center px-5 py-2 bg-indigo-600 text-white rounded-md font-medium hover:bg-indigo-700 transition">
                        Dashboard
                    </a>
                </div>
            </div>
        </div>
    </body>
</html>
