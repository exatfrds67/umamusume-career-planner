<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DatabaseMaintenanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Response;

class DatabaseController extends Controller
{
    public function __construct(
        protected DatabaseMaintenanceService $dbService
    ) {}

    /**
     * Display database maintenance page.
     */
    public function maintenance()
    {
        $tables = $this->dbService->getTableInfo();
        $migrationStatus = $this->dbService->getMigrationStatus();

        return view('admin.database.maintenance', compact('tables', 'migrationStatus'));
    }

    /**
     * Optimize database tables.
     */
    public function optimize()
    {
        $optimized = $this->dbService->optimizeTables();

        return back()->with('success', 'Optimized '.count($optimized).' table(s).');
    }

    /**
     * Create database backup.
     */
    public function backup()
    {
        try {
            $filename = $this->dbService->createBackup();

            return back()->with('success', "Backup created: {$filename}");
        } catch (\Exception $e) {
            return back()->with('error', 'Backup failed: '.$e->getMessage());
        }
    }

    /**
     * Download database backup.
     */
    public function downloadBackup(string $filename)
    {
        $path = storage_path('app/backups/'.$filename);

        if (! file_exists($path)) {
            return back()->with('error', 'Backup file not found.');
        }

        return Response::download($path);
    }

    /**
     * Run migrations.
     */
    public function migrate()
    {
        Artisan::call('migrate', ['--force' => true]);

        return back()->with('success', 'Migrations executed successfully.');
    }

    /**
     * Display seeders page.
     */
    public function seeders()
    {
        $seeders = $this->dbService->getAvailableSeeders();

        return view('admin.database.seeders', compact('seeders'));
    }

    /**
     * Run specific seeder.
     */
    public function runSeeder(Request $request)
    {
        $request->validate([
            'seeder' => ['required', 'string'],
        ]);

        $success = $this->dbService->runSeeder($request->input('seeder'));

        if ($success) {
            return back()->with('success', 'Seeder executed successfully.');
        }

        return back()->with('error', 'Seeder execution failed.');
    }

    /**
     * Run all seeders.
     */
    public function seedAll()
    {
        Artisan::call('db:seed', ['--force' => true]);

        return back()->with('success', 'All seeders executed successfully.');
    }

    /**
     * Fresh migration with seeding.
     */
    public function fresh()
    {
        Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);

        return back()->with('success', 'Database refreshed and seeded successfully.');
    }
}
