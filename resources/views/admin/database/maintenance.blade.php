<x-admin-layout title="Database Maintenance">
    <div class="space-y-6">
        <div class="admin-page-hero">
            <div class="admin-page-hero__content">
                <div>
                    <div class="admin-page-hero__eyebrow">
                        <span>Persistence</span>
                    </div>
                    <h1 class="admin-page-hero__title">Database Maintenance</h1>
                    <p class="admin-page-hero__body text-sm sm:text-base">Optimize tables, create backups, and manage schema operations from a single workspace.</p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <x-admin-confirm-action
                :action="route('admin.database.optimize')"
                title="Optimize Database Tables"
                message="This will run optimization on all database tables. The operation may take a moment."
                variant="info"
                confirmText="Optimize"
                buttonLabel="Optimize Tables"
                buttonClass="w-full rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2" />

            <x-admin-confirm-action
                :action="route('admin.database.backup')"
                title="Create Database Backup"
                message="This will create a full backup of the current database. Continue?"
                variant="info"
                confirmText="Create Backup"
                buttonLabel="Create Backup"
                buttonClass="w-full rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-green-500 focus-visible:ring-offset-2" />

            <x-admin-confirm-action
                :action="route('admin.database.migrate')"
                title="Run Database Migrations"
                message="This will execute all pending database migrations. This action may not be easily reversible."
                variant="warning"
                confirmText="Run Migrations"
                buttonLabel="Run Migrations"
                buttonClass="w-full rounded-md bg-amber-600 px-4 py-2 text-white hover:bg-amber-700 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2" />

            <x-admin-confirm-action
                :action="route('admin.database.fresh')"
                title="Fresh + Seed Database"
                message="This will DELETE ALL DATA in the database and re-seed with fresh data. This action is irreversible."
                variant="danger"
                confirmText="Delete All Data"
                :requireTypedConfirmation="true"
                typedConfirmationWord="CONFIRM"
                buttonLabel="Fresh + Seed"
                buttonClass="w-full rounded-md bg-red-600 px-4 py-2 text-white hover:bg-red-700 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2" />
        </div>

        <!-- Migration Status -->
        <div class="admin-surface p-6">
            <h2 class="mb-4 text-xl font-semibold text-neutral-900 dark:text-white">Migration Status</h2>
            <pre aria-label="Migration status output" class="overflow-x-auto rounded bg-neutral-100 p-4 text-xs text-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">{{ $migrationStatus['output'] }}</pre>
        </div>

        <!-- Tables -->
        <div class="admin-surface overflow-x-auto">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-neutral-900 dark:text-white">Database Tables</h2>
            </div>
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <caption class="sr-only">Database tables</caption>
                <thead class="bg-neutral-50 dark:bg-neutral-900">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                            Table</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                            Rows</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                            Size</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                            Engine</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-800">
                    @foreach ($tables as $table)
                        <tr>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-neutral-900 dark:text-white">
                                {{ $table['name'] }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-500 dark:text-neutral-400">
                                {{ number_format($table['rows']) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-500 dark:text-neutral-400">
                                {{ $table['size'] }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-500 dark:text-neutral-400">
                                {{ $table['engine'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
