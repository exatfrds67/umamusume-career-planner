<!-- Breadcrumbs / Search -->
<div class="flex flex-1 gap-x-4 self-stretch items-center lg:gap-x-6">
    <div class="relative flex flex-1">
        <label for="search-field" class="sr-only">Search</label>
        <svg class="pointer-events-none absolute inset-y-0 left-0 h-full w-5 text-neutral-400" viewBox="0 0 20 20"
            fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd"
                d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.452 4.391l3.328 3.329a.75.75 0 1 1-1.06 1.06l-3.329-3.328A7 7 0 0 1 2 9Z"
                clip-rule="evenodd" />
        </svg>
        <input id="search-field"
            class="block h-full w-full border-0 py-0 pl-8 pr-0 text-neutral-900 dark:text-white placeholder:text-neutral-400 focus:ring-0 bg-transparent sm:text-sm"
            placeholder="Search..." type="search" name="search">
    </div>

    <!-- User Menu -->
    <div class="flex items-center gap-x-4 lg:gap-x-6">
        <!-- Theme Toggle -->
        <button type="button" id="theme-toggle"
            x-data="{ isDark: document.documentElement.classList.contains('dark') }" 
            @theme-changed.window="isDark = $event.detail"
            class="-m-2.5 p-2.5 text-neutral-400 hover:text-neutral-500 dark:text-neutral-300 dark:hover:text-neutral-100 transition-colors duration-200"
            aria-label="Toggle theme" :aria-pressed="isDark.toString()" title="Toggle light/dark theme">
            <span class="sr-only">Toggle theme</span>
            <!-- Sun icon (shown in dark mode) -->
            <svg class="h-6 w-6 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
            </svg>
            <!-- Moon icon (shown in light mode) -->
            <svg class="h-6 w-6 block dark:hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
            </svg>
        </button>

        <!-- Critical Alert Badge -->
        <x-ai.critical-alert-badge :alert-count="0" />

        <!-- Notifications -->
        <livewire:notification-dropdown />

        <!-- Separator -->
        <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-neutral-200 dark:lg:bg-neutral-700" aria-hidden="true"></div>

        <!-- Profile dropdown -->
        <div x-data="{ open: false }" class="relative z-20" 
            @keydown.escape.window="open = false"
            @popover-opened.window="if ($event.detail !== 'user-menu') open = false">
            <button type="button" class="-m-1.5 flex items-center p-1.5" id="user-menu-btn" 
                @click="open = !open; if(open) $dispatch('popover-opened', 'user-menu')"
                @click.away="open = false">
                <span class="sr-only">Open user menu</span>
                <img class="h-8 w-8 rounded-full bg-neutral-50"
                    src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'User') }}&background=3b82f6&color=fff"
                    loading="lazy" decoding="async" alt="{{ Auth::user()->name ?? 'User' }}">
                <span class="hidden lg:flex lg:items-center">
                    <span class="ml-4 text-sm font-semibold leading-6 text-neutral-900 dark:text-white" aria-hidden="true">
                        {{ Auth::user()->name ?? 'User' }}
                        @if (Auth::user()?->isAdmin())
                            <span
                                class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                Admin
                            </span>
                        @endif
                    </span>
                    <svg class="ml-2 h-5 w-5 text-neutral-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z"
                            clip-rule="evenodd" />
                    </svg>
                </span>
            </button>

            <div x-show="open" x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute right-0 z-10 mt-2.5 w-40 origin-top-right rounded-md bg-white dark:bg-neutral-800 py-2 shadow-lg ring-1 ring-neutral-900/5 focus:outline-hidden"
                role="menu" aria-orientation="vertical">
                <a href="{{ route('profile.show') }}"
                    class="block px-3 py-1 text-sm leading-6 text-neutral-900 dark:text-neutral-100 hover:bg-neutral-50 dark:hover:bg-neutral-700"
                    role="menuitem">Your profile</a>
                <form method="POST" action="{{ route('logout') }}" id="logout-form">
                    @csrf
                    <button type="submit" id="logout-btn"
                        class="block w-full text-left px-3 py-1 text-sm leading-6 text-neutral-900 dark:text-neutral-100 hover:bg-neutral-50 dark:hover:bg-neutral-700"
                        role="menuitem">Sign out</button>
                </form>
            </div>
        </div>
    </div>
</div>
