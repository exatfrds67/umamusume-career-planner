<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Admin</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 dark:bg-gray-900">
    <div class="min-h-screen">
        <!-- Admin Navigation -->
        <nav class="border-b border-red-700 bg-red-600 dark:bg-red-800">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <div class="flex">
                        <div class="flex shrink-0 items-center">
                            <span class="text-xl font-bold text-white">Admin Panel</span>
                        </div>
                        <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                            <a href="{{ route('admin.users.index') }}"
                                class="inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium text-white hover:text-red-100 {{ request()->routeIs('admin.users.*') ? 'border-white' : 'border-transparent' }}">
                                Users
                            </a>
                            <a href="{{ route('admin.system-settings.index') }}"
                                class="inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium text-white hover:text-red-100 {{ request()->routeIs('admin.system-settings.*') ? 'border-white' : 'border-transparent' }}">
                                System Settings
                            </a>
                            <a href="{{ route('admin.logs.index') }}"
                                class="inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium text-white hover:text-red-100 {{ request()->routeIs('admin.logs.*') ? 'border-white' : 'border-transparent' }}">
                                Logs
                            </a>
                            <a href="{{ route('admin.database.maintenance') }}"
                                class="inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium text-white hover:text-red-100 {{ request()->routeIs('admin.database.*') ? 'border-white' : 'border-transparent' }}">
                                Database
                            </a>
                            <a href="{{ route('admin.queue.index') }}"
                                class="inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium text-white hover:text-red-100 {{ request()->routeIs('admin.queue.*') ? 'border-white' : 'border-transparent' }}">
                                Queue
                            </a>
                            <a href="{{ route('admin.apm') }}"
                                class="inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium text-white hover:text-red-100 {{ request()->routeIs('admin.apm') ? 'border-white' : 'border-transparent' }}">
                                APM
                            </a>
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
        <main class="py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                @if (session('success'))
                    <div class="mb-4 rounded-md bg-green-50 p-4 dark:bg-green-900/20">
                        <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 rounded-md bg-red-50 p-4 dark:bg-red-900/20">
                        <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
                    </div>
                @endif

                {{ $slot }}
            </div>
        </main>
    </div>
</body>

</html>
