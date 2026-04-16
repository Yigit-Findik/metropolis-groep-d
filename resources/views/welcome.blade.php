<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>City Grid</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="city-page">
        <header class="city-topbar">
            @if (Route::has('login'))
                <div class="city-auth-links">
                    @auth
                        <a class="city-auth-link city-auth-link--primary" href="{{ url('/dashboard') }}">Dashboard</a>
                    @else
                        <a class="city-auth-link" href="{{ route('login') }}">Login</a>

                        @if (Route::has('register'))
                            <a class="city-auth-link city-auth-link--primary" href="{{ route('register') }}">Register</a>
                        @endif
                    @endauth
                </div>
            @endif
        </header>

        <main class="city-shell">
            <section class="city-frame">
                <div class="city-body">
                    <section class="city-panel" style="padding:20px;">
                        <div class="city-grid" data-city-grid>
                    @foreach ($gridCells->groupBy('row_index') as $rowNumber => $rowCells)
                        @foreach ($rowCells as $cell)
                            <button
                                type="button"
                                class="city-cell grid-cell {{ filled($cell->function_name) ? 'is-occupied' : 'is-empty' }}"
                                data-grid-cell
                                data-row="{{ $cell->row_index }}"
                                data-column="{{ $cell->column_index }}"
                                data-function="{{ $cell->function_name ?? '' }}"
                                aria-label="Row {{ $cell->row_index }}, column {{ $cell->column_index }}{{ filled($cell->function_name) ? ', occupied' : ', available' }}"
                                aria-pressed="false"
                            >
                            </button>
                        @endforeach
                    @endforeach
                        </div>
                    </section>

                    <aside class="city-side">
                        <div class="city-side-card">
                            <div class="city-side-preview is-empty" data-selected-cell-preview aria-hidden="true"></div>
                        </div>
                    </aside>
                </div>
            </section>
        </main>
    </body>
</html>
