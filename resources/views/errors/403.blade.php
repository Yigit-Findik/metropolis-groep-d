<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Access Denied — {{ config('app.name') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <style>
            :root {
                color-scheme: light dark;
            }

            body {
                margin: 0;
                font-family: Figtree, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%);
                color: #0f172a;
            }

            .page {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 24px;
            }

            .card {
                width: 100%;
                max-width: 28rem;
                background: rgba(255, 255, 255, 0.94);
                border: 1px solid rgba(148, 163, 184, 0.3);
                border-radius: 24px;
                box-shadow: 0 24px 60px rgba(15, 23, 42, 0.12);
                padding: 32px;
                text-align: center;
                backdrop-filter: blur(10px);
            }

            .code {
                margin: 0 0 16px;
                font-size: 72px;
                line-height: 1;
                font-weight: 700;
                color: #dc2626;
            }

            .title {
                margin: 0 0 8px;
                font-size: 24px;
                line-height: 1.2;
            }

            .text {
                margin: 0 0 28px;
                color: #475569;
            }

            .actions {
                display: flex;
                gap: 12px;
                justify-content: center;
                flex-wrap: wrap;
            }

            .button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 10px 18px;
                border-radius: 12px;
                text-decoration: none;
                font-weight: 600;
                transition: transform 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
            }

            .button:hover {
                transform: translateY(-1px);
            }

            .button-secondary {
                background: #e2e8f0;
                color: #0f172a;
            }

            .button-primary {
                background: #2563eb;
                color: white;
                box-shadow: 0 8px 24px rgba(37, 99, 235, 0.25);
            }
        </style>
    </head>
    <body>
        <div class="page">
            <div class="card">
                <p class="code">403</p>

                <h1 class="title">Access Denied</h1>

                <p class="text">
                    You do not have permission to view this page.
                    Please contact your administrator if you think this is a mistake.
                </p>

                <div class="actions">
                    <a href="{{ url()->previous('/') }}" class="button button-secondary">Go Back</a>
                    <a href="{{ route('dashboard') }}" class="button button-primary">Dashboard</a>
                </div>
            </div>
        </div>
    </body>
</html>
