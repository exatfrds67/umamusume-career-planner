@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - {{ $title ?? 'Admin' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="bg-gray-50 dark:bg-gray-900">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-medium focus:text-gray-900 dark:focus:bg-gray-800 dark:focus:text-white">Skip to main content</a>
    <div class="min-h-screen">
        <!-- Admin Navigation -->
        <nav aria-label="Admin navigation" class="border-b border-red-700 bg-red-600 dark:border-red-900 dark:bg-red-800" x-data="{ mobileOpen: false }">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <div class="flex">
                        <div class="flex shrink-0 items-center">
                            <span class="text-xl font-bold text-white">Admin Panel</span>
                        </div>
                        <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                            <a href="{{ route('admin.users.index') }}"
                                aria-current="{{ request()->routeIs('admin.users.*') ? 'page' : 'false' }}"
                                class="inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium text-white hover:text-red-100 {{ request()->routeIs('admin.users.*') ? 'border-white' : 'border-transparent' }}">
                                Users
                            </a>
                            <a href="{{ route('admin.system-settings.index') }}"
                                aria-current="{{ request()->routeIs('admin.system-settings.*') ? 'page' : 'false' }}"
                                class="inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium text-white hover:text-red-100 {{ request()->routeIs('admin.system-settings.*') ? 'border-white' : 'border-transparent' }}">
                                System Settings
                            </a>
                            <a href="{{ route('admin.logs.index') }}"
                                aria-current="{{ request()->routeIs('admin.logs.*') ? 'page' : 'false' }}"
                                class="inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium text-white hover:text-red-100 {{ request()->routeIs('admin.logs.*') ? 'border-white' : 'border-transparent' }}">
                                Logs
                            </a>
                            <a href="{{ route('admin.database.maintenance') }}"
                                aria-current="{{ request()->routeIs('admin.database.*') ? 'page' : 'false' }}"
                                class="inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium text-white hover:text-red-100 {{ request()->routeIs('admin.database.*') ? 'border-white' : 'border-transparent' }}">
                                Database
                            </a>
                            <a href="{{ route('admin.queue.index') }}"
                                aria-current="{{ request()->routeIs('admin.queue.*') ? 'page' : 'false' }}"
                                class="inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium text-white hover:text-red-100 {{ request()->routeIs('admin.queue.*') ? 'border-white' : 'border-transparent' }}">
                                Queue
                            </a>
                            <a href="{{ route('admin.apm') }}"
                                aria-current="{{ request()->routeIs('admin.apm') ? 'page' : 'false' }}"
                                class="inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium text-white hover:text-red-100 {{ request()->routeIs('admin.apm') ? 'border-white' : 'border-transparent' }}">
                                APM
                            </a>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <!-- Mobile hamburger -->
                        <button @click="mobileOpen = !mobileOpen" class="inline-flex items-center justify-center rounded p-2 text-white hover:bg-red-700 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-white sm:hidden" aria-label="Toggle navigation menu" :aria-expanded="mobileOpen">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                <path x-show="mobileOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        <a href="{{ route('dashboard') }}" class="text-sm text-white hover:text-red-100 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-red-600 rounded">
                            <span aria-hidden="true">&larr;</span> Back to App
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm text-white hover:text-red-100 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-red-600 rounded">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Mobile navigation menu -->
            <div x-show="mobileOpen" x-cloak x-transition class="border-t border-red-700 sm:hidden">
                <div class="space-y-1 px-4 pb-3 pt-2">
                    <a href="{{ route('admin.users.index') }}" class="block rounded px-3 py-2 text-base font-medium text-white hover:bg-red-700 {{ request()->routeIs('admin.users.*') ? 'bg-red-700' : '' }}">Users</a>
                    <a href="{{ route('admin.system-settings.index') }}" class="block rounded px-3 py-2 text-base font-medium text-white hover:bg-red-700 {{ request()->routeIs('admin.system-settings.*') ? 'bg-red-700' : '' }}">System Settings</a>
                    <a href="{{ route('admin.logs.index') }}" class="block rounded px-3 py-2 text-base font-medium text-white hover:bg-red-700 {{ request()->routeIs('admin.logs.*') ? 'bg-red-700' : '' }}">Logs</a>
                    <a href="{{ route('admin.database.maintenance') }}" class="block rounded px-3 py-2 text-base font-medium text-white hover:bg-red-700 {{ request()->routeIs('admin.database.*') ? 'bg-red-700' : '' }}">Database</a>
                    <a href="{{ route('admin.queue.index') }}" class="block rounded px-3 py-2 text-base font-medium text-white hover:bg-red-700 {{ request()->routeIs('admin.queue.*') ? 'bg-red-700' : '' }}">Queue</a>
                    <a href="{{ route('admin.apm') }}" class="block rounded px-3 py-2 text-base font-medium text-white hover:bg-red-700 {{ request()->routeIs('admin.apm') ? 'bg-red-700' : '' }}">APM</a>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main id="main-content" class="py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                @if (session('success'))
                    <div role="alert" class="mb-4 rounded-md bg-green-50 p-4 dark:bg-green-900/20">
                        <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
                    </div>
                @endif

                @if (session('error'))
                    <div role="alert" class="mb-4 rounded-md bg-red-50 p-4 dark:bg-red-900/20">
                        <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
                    </div>
                @endif

                {{ $slot }}
            </div>
        </main>
    </div>
    @livewireScriptConfig
</body>

</html>
