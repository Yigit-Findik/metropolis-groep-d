<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Access Denied — {{ config('app.name') }}</title>
        @vite('resources/css/app.css')
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    </head>
    <body class="m-0 font-sans text-slate-900 bg-gradient-to-b from-slate-50 to-slate-200">
        <div class="min-h-screen flex items-center justify-center p-6">
            <div class="w-full max-w-md rounded-3xl border border-slate-400/30 bg-white/95 p-8 text-center shadow-[0_24px_60px_rgba(15,23,42,0.12)] backdrop-blur">
                <p class="mb-4 text-7xl font-bold leading-none text-red-600">403</p>

                <h1 class="mb-2 text-2xl leading-tight">Access Denied</h1>

                <p class="mb-7 text-slate-600">
                    You do not have permission to view this page.
                    Please contact your administrator if you think this is a mistake.
                </p>

                <div class="flex flex-wrap justify-center gap-3">
                    <a href="{{ url()->previous('/') }}" class="inline-flex items-center justify-center rounded-xl bg-slate-200 px-5 py-2.5 font-semibold text-slate-900 no-underline transition hover:-translate-y-0.5">Go Back</a>
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-2.5 font-semibold text-white no-underline shadow-[0_8px_24px_rgba(37,99,235,0.25)] transition hover:-translate-y-0.5">Dashboard</a>
                </div>
            </div>
        </div>
    </body>
</html>
