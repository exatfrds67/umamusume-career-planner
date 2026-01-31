<x-admin-layout>
    <div class="space-y-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Database Seeders</h1>

        <!-- Run All Seeders -->
        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
            <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">Run All Seeders</h2>
            <form method="POST" action="{{ route('admin.database.seeders.all') }}"
                onsubmit="return confirm('Run all seeders?')">
                @csrf
                <button type="submit" class="rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700">Run All
                    Seeders</button>
            </form>
        </div>

        <!-- Individual Seeders -->
        <div class="rounded-lg bg-white shadow dark:bg-gray-800">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Available Seeders</h2>
            </div>
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($seeders as $seeder)
                    <div class="flex items-center justify-between p-6">
                        <div>
                            <h3 class="font-medium text-gray-900 dark:text-white">{{ $seeder['name'] }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $seeder['class'] }}</p>
                        </div>
                        <form method="POST" action="{{ route('admin.database.seeders.run') }}"
                            onsubmit="return confirm('Run this seeder?')">
                            @csrf
                            <input type="hidden" name="seeder" value="{{ $seeder['class'] }}">
                            <button type="submit"
                                class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Run</button>
                        </form>
                    </div>
                @empty
                    <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                        No seeders found.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-admin-layout>
