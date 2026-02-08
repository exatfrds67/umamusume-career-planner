<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseMaintenanceService
{
    /**
     * Get database table information.
     *
     * @return array<int, array{name: string, rows: int, size: string, engine: string}>
     */
    public function getTableInfo(): array
    {
        $tables = [];
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if ($driver === 'mysql') {
            $results = DB::select('SHOW TABLE STATUS');

            foreach ($results as $table) {
                $tables[] = [
                    'name' => $table->Name,
                    'rows' => $table->Rows,
                    'size' => $this->formatBytes($table->Data_length + $table->Index_length),
                    'engine' => $table->Engine,
                ];
            }
        } elseif ($driver === 'sqlite') {
            $results = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");

            foreach ($results as $table) {
                $name = $table->name;
                $count = DB::table($name)->count();

                $tables[] = [
                    'name' => $name,
                    'rows' => $count,
                    'size' => 'N/A',
                    'engine' => 'SQLite',
                ];
            }
        }

        return $tables;
    }

    /**
     * Optimize database tables.
     *
     * @return array<int, string>
     */
    public function optimizeTables(): array
    {
        $results = [];
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            $tables = DB::select('SHOW TABLES');
            $database = DB::getDatabaseName();

            foreach ($tables as $table) {
                $tableName = $table->{"Tables_in_{$database}"};
                DB::statement("OPTIMIZE TABLE `{$tableName}`");
                $results[] = $tableName;
            }
        }

        return $results;
    }

    /**
     * Create database backup.
     */
    public function createBackup(): string
    {
        $filename = 'backup_'.date('Y-m-d_His').'.sql';
        $path = storage_path('app/backups/'.$filename);

        File::ensureDirectoryExists(storage_path('app/backups'));

        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');

            $database = is_string($database) ? $database : '';
            $username = is_string($username) ? $username : '';
            $password = is_string($password) ? $password : '';
            $host = is_string($host) ? $host : '';

            $command = sprintf(
                'mysqldump -h %s -u %s -p%s %s > %s',
                escapeshellarg($host),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($path)
            );

            exec($command);
        } elseif ($driver === 'sqlite') {
            $dbPath = database_path('database.sqlite');
            File::copy($dbPath, $path);
        }

        return $filename;
    }

    /**
     * Get list of available seeders.
     *
     * @return array<int, array{name: string, class: string}>
     */
    public function getAvailableSeeders(): array
    {
        $seederPath = database_path('seeders');
        $files = File::files($seederPath);
        $seeders = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'php' && $file->getFilename() !== 'DatabaseSeeder.php') {
                $seeders[] = [
                    'name' => $file->getFilenameWithoutExtension(),
                    'class' => 'Database\\Seeders\\'.$file->getFilenameWithoutExtension(),
                ];
            }
        }

        return $seeders;
    }

    /**
     * Run specific seeder.
     */
    public function runSeeder(string $class): bool
    {
        try {
            Artisan::call('db:seed', ['--class' => $class, '--force' => true]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get migration status.
     *
     * @return array{output: string}
     */
    public function getMigrationStatus(): array
    {
        Artisan::call('migrate:status');
        $output = Artisan::output();

        return ['output' => $output];
    }

    /**
     * Format bytes to human-readable size.
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
