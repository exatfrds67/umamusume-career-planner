<x-admin-layout title="Database Seeders">
    <div class="space-y-6">
        <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">Database Seeders</h1>

        <!-- Run All Seeders -->
        <div class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800">
            <h2 class="mb-4 text-xl font-semibold text-neutral-900 dark:text-white">Run All Seeders</h2>
            <x-admin-confirm-action
                :action="route('admin.database.seeders.all')"
                title="Run All Seeders"
                message="This will execute all database seeders, which may insert or replace data. Continue?"
                variant="warning"
                confirmText="Run All"
                buttonLabel="Run All Seeders"
                buttonClass="rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-green-500 focus-visible:ring-offset-2" />
        </div>

        <!-- Individual Seeders -->
        <div class="rounded-lg bg-white shadow dark:bg-neutral-800">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-neutral-900 dark:text-white">Available Seeders</h2>
            </div>
            <div class="divide-y divide-neutral-200 dark:divide-neutral-700">
                @forelse ($seeders as $seeder)
                    <div class="flex items-center justify-between p-6">
                        <div>
                            <h3 class="font-medium text-neutral-900 dark:text-white">{{ $seeder['name'] }}</h3>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ $seeder['class'] }}</p>
                        </div>
                        <form method="POST" action="{{ route('admin.database.seeders.run') }}"
                            onsubmit="return confirm('Run {{ $seeder['name'] }} seeder?')">
                            @csrf
                            <input type="hidden" name="seeder" value="{{ $seeder['class'] }}">
                            <button type="submit"
                                aria-label="Run {{ $seeder['name'] }} seeder"
                                class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">Run</button>
                        </form>
                    </div>
                @empty
                    <div class="p-6 text-center text-neutral-500 dark:text-neutral-400">
                        No seeders found.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-admin-layout>
