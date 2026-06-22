<nav x-data="navigation" class="sticky top-0 z-50 bg-white hc:bg-black border-b border-gray-100 hc:border-white dark:bg-gray-800 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center justify-center bg-white rounded-lg px-2 py-1">
                        <x-application-logo class="block h-9 w-auto" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 lg:-my-px lg:ms-10 lg:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    @if(in_array(Auth::user()->role?->name, ['Administrator', 'City planner', 'Policy maker'], true))
                        <x-nav-link :href="route('grid')" :active="request()->routeIs('grid')">
                            {{ __('Grid') }}
                        </x-nav-link>
                    @endif
                    @if(in_array(Auth::user()->role?->name, ['Administrator', 'City planner'], true))
                        <x-nav-link :href="route('city_events.index')" :active="request()->routeIs('city_events.*')">
                            {{ __('Events') }}
                        </x-nav-link>
                    @endif
                    @if(Auth::user()->role?->name === 'Administrator')
                        <x-nav-link :href="route('city_functions')" :active="request()->routeIs('city_functions')">
                            {{ __('City Functions') }}
                        </x-nav-link>
                    @endif
                    @if(in_array(Auth::user()->role?->name, ['Administrator', 'Expert in effects'], true))
                        <x-nav-link :href="route('effects.index')" :active="request()->routeIs('effects.index')">
                            {{ __('Effects') }}
                        </x-nav-link>
                        <x-nav-link :href="route('effects.pending-actions')" :active="request()->routeIs('effects.pending-actions')">
                            {{ __('Pending actions') }}
                        </x-nav-link>
                    @endif
                    @if(Auth::user()->role?->name === 'Administrator')
                        <x-nav-link :href="route('audit_log')" :active="request()->routeIs('audit_log')">
                            {{ __('Audit Log') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Right side: Contrast Toggle + Settings Dropdown -->
            <div class="hidden lg:flex lg:items-center lg:gap-3">

                <!-- High Contrast Toggle -->
                <div x-data="contrastToggle">
                    <button
                        @click="toggle()"
                        :aria-pressed="active.toString()"
                        aria-label="Toggle high contrast mode"
                        class="inline-flex items-center gap-1.5 rounded-lg border-2 px-3 py-1.5 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-yellow-400 focus-visible:ring-offset-2"
                        :class="active
                            ? 'border-black bg-yellow-300 text-black'
                            : 'border-gray-300 bg-white text-gray-600 hover:border-gray-400 hover:text-gray-800 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300'"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="12" cy="12" r="9" />
                            <path stroke-linecap="round" d="M12 3v18M12 3a9 9 0 010 18" />
                        </svg>
                        <span x-text="active ? 'Contrast: On' : 'Contrast: Off'"></span>
                    </button>
                </div>

                <!-- Settings Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button aria-label="Profile" aria-haspopup="true" :aria-expanded="open.toString()" title="Profile" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 hc:text-white dark:text-gray-400 bg-white hc:bg-black dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150 focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-indigo-400">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg aria-hidden="true" class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    @click="submitLogout($event)">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center gap-1 lg:hidden">
                <!-- High contrast toggle (mobile top bar, icon only) -->
                <div x-data="contrastToggle">
                    <button
                        @click="toggle()"
                        :aria-pressed="active.toString()"
                        aria-label="Toggle high contrast mode"
                        class="inline-flex items-center justify-center p-2 rounded-md transition focus:outline-none focus-visible:ring-2 focus-visible:ring-yellow-400"
                        :class="active
                            ? 'bg-yellow-300 text-black border-2 border-black'
                            : 'text-gray-400 hc:text-white dark:text-gray-500 hover:bg-gray-100 hc:hover:bg-neutral-900 dark:hover:bg-gray-900'"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="12" cy="12" r="9" />
                            <path stroke-linecap="round" d="M12 3v18M12 3a9 9 0 010 18" />
                        </svg>
                    </button>
                </div>
                <button
                    @click="open = ! open"
                    :aria-label="open ? 'Close navigation' : 'Navigation button'"
                    :title="open ? 'Close navigation' : 'Navigation button'"
                    :aria-expanded="open.toString()"
                    aria-controls="mobile-navigation-menu"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hc:text-white dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 hc:hover:bg-neutral-900 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg aria-hidden="true" class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div id="mobile-navigation-menu" :class="{'block': open, 'hidden': ! open}" class="hidden lg:hidden bg-white hc:bg-black dark:bg-gray-800">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            @if(in_array(Auth::user()->role?->name, ['Administrator', 'City planner', 'Policy maker'], true))
                <x-responsive-nav-link :href="route('grid')" :active="request()->routeIs('grid')">
                    {{ __('Grid') }}
                </x-responsive-nav-link>
            @endif
            @if(in_array(Auth::user()->role?->name, ['Administrator', 'City planner'], true))
                <x-responsive-nav-link :href="route('city_events.index')" :active="request()->routeIs('city_events.*')">
                    {{ __('Events') }}
                </x-responsive-nav-link>
            @endif
            @if(Auth::user()->role?->name === 'Administrator')
                <x-responsive-nav-link :href="route('city_functions')" :active="request()->routeIs('city_functions')">
                    {{ __('City Functions') }}
                </x-responsive-nav-link>
            @endif
            @if(in_array(Auth::user()->role?->name, ['Administrator', 'Expert in effects'], true))
                <x-responsive-nav-link :href="route('effects.index')" :active="request()->routeIs('effects.index')">
                    {{ __('Effects') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('effects.pending-actions')" :active="request()->routeIs('effects.pending-actions')">
                    {{ __('Pending actions') }}
                </x-responsive-nav-link>
            @endif
            @if(Auth::user()->role?->name === 'Administrator')
                <x-responsive-nav-link :href="route('audit_log')" :active="request()->routeIs('audit_log')">
                    {{ __('Audit Log') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 hc:border-white dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 hc:text-white dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500 hc:text-white">{{ Auth::user()->email }}</div>
            </div>

            <!-- Mobile contrast toggle -->
            <div class="mt-3 px-4" x-data="contrastToggle">
                <button
                    @click="toggle()"
                    :aria-pressed="active.toString()"
                    aria-label="Toggle high contrast mode"
                    class="inline-flex items-center gap-1.5 rounded-lg border-2 px-3 py-1.5 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-yellow-400"
                    :class="active
                        ? 'border-black bg-yellow-300 text-black'
                        : 'border-gray-300 bg-white text-gray-600 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300'"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" />
                        <path stroke-linecap="round" d="M12 3v18M12 3a9 9 0 010 18" />
                    </svg>
                    <span x-text="active ? 'Contrast: On' : 'Contrast: Off'"></span>
                </button>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
