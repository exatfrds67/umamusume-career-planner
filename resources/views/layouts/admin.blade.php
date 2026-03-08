<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Admin</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="bg-neutral-50 dark:bg-neutral-900">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-medium focus:text-neutral-900 dark:focus:bg-neutral-800 dark:focus:text-white">Skip to main content</a>
    <div class="min-h-screen">
        <!-- Admin Navigation -->
        <nav aria-label="Admin navigation" class="bg-red-600 dark:bg-red-800 border-b border-red-700 dark:border-red-900">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <div class="flex">
                        <div class="flex shrink-0 items-center">
                            <span class="text-xl font-bold text-white">Admin Panel</span>
                        </div>
                        <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                            <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')" class="text-white hover:text-red-100">
                                Users
                            </x-nav-link>
                            <x-nav-link :href="route('admin.system-settings.index')" :active="request()->routeIs('admin.system-settings.*')" class="text-white hover:text-red-100">
                                System Settings
                            </x-nav-link>
                            <x-nav-link :href="route('admin.logs.index')" :active="request()->routeIs('admin.logs.*')" class="text-white hover:text-red-100">
                                Logs
                            </x-nav-link>
                            <x-nav-link :href="route('admin.database.maintenance')" :active="request()->routeIs('admin.database.*')" class="text-white hover:text-red-100">
                                Database
                            </x-nav-link>
                            <x-nav-link :href="route('admin.queue.index')" :active="request()->routeIs('admin.queue.*')" class="text-white hover:text-red-100">
                                Queue
                            </x-nav-link>
                            <x-nav-link :href="route('admin.apm')" :active="request()->routeIs('admin.apm')" class="text-white hover:text-red-100">
                                APM
                            </x-nav-link>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <a href="{{ route('dashboard') }}" class="text-sm text-white hover:text-red-100">
                            ← Back to App
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm text-white hover:text-red-100">
                                Logout
                            </button>
                        </form>
                    </div>
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
