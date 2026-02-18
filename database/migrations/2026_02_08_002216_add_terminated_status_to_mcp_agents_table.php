<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add 'terminated' to the status enum on ucp_mcp_agents table.
     * The AgentLifecycleManager and MCPMonitoringService set status to 'terminated'
     * but the original enum only allowed: active, inactive, training, error, maintenance.
     */
    public function up(): void
    {
        if (Schema::hasTable('ucp_mcp_agents')) {
            $driver = Schema::getConnection()->getDriverName();

            if ($driver === 'sqlite') {
                // SQLite doesn't enforce enum constraints the same way,
                // but we need to handle the CHECK constraint if present.
                // Recreate the column without the restrictive CHECK constraint.
                // SQLite enum columns are stored as varchar with CHECK constraints.
                // We'll drop and recreate the column constraint by rebuilding.
                // For SQLite in testing, the simplest approach is to use raw SQL
                // to drop the CHECK constraint by recreating the table.
                // However, since SQLite CHECK constraints from Laravel enum()
                // are enforced, we need to work around this.

                // The safest approach for SQLite: create a new table, copy data, swap.
                DB::statement('PRAGMA foreign_keys=off');

                // Get current table SQL
                $tableInfo = DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name='ucp_mcp_agents'");

                if (! empty($tableInfo)) {
                    $createSql = $tableInfo[0]->sql;

                    // Replace the old enum CHECK constraint with one that includes 'terminated'
                    $oldCheck = "in ('active', 'inactive', 'training', 'error', 'maintenance')";
                    $newCheck = "in ('active', 'inactive', 'training', 'error', 'maintenance', 'terminated')";

                    $newCreateSql = str_replace($oldCheck, $newCheck, $createSql);

                    // Only proceed if the replacement actually changed something
                    if ($newCreateSql !== $createSql) {
                        $newCreateSql = str_replace('CREATE TABLE "ucp_mcp_agents"', 'CREATE TABLE "ucp_mcp_agents_new"', $newCreateSql);

                        DB::statement($newCreateSql);
                        DB::statement('INSERT INTO "ucp_mcp_agents_new" SELECT * FROM "ucp_mcp_agents"');
                        DB::statement('DROP TABLE "ucp_mcp_agents"');
                        DB::statement('ALTER TABLE "ucp_mcp_agents_new" RENAME TO "ucp_mcp_agents"');
                    }
                }

                DB::statement('PRAGMA foreign_keys=on');
            } else {
                // MySQL/MariaDB: ALTER the enum column
                DB::statement("ALTER TABLE `ucp_mcp_agents` MODIFY COLUMN `status` ENUM('active', 'inactive', 'training', 'error', 'maintenance', 'terminated') NOT NULL DEFAULT 'inactive'");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ucp_mcp_agents')) {
            $driver = Schema::getConnection()->getDriverName();

            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys=off');

                $tableInfo = DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name='ucp_mcp_agents'");

                if (! empty($tableInfo)) {
                    $createSql = $tableInfo[0]->sql;

                    $newCheck = "in ('active', 'inactive', 'training', 'error', 'maintenance', 'terminated')";
                    $oldCheck = "in ('active', 'inactive', 'training', 'error', 'maintenance')";

                    $revertSql = str_replace($newCheck, $oldCheck, $createSql);

                    if ($revertSql !== $createSql) {
                        $revertSql = str_replace('CREATE TABLE "ucp_mcp_agents"', 'CREATE TABLE "ucp_mcp_agents_new"', $revertSql);

                        DB::statement($revertSql);
                        DB::statement('INSERT INTO "ucp_mcp_agents_new" SELECT * FROM "ucp_mcp_agents" WHERE "status" != \'terminated\'');
                        DB::statement('DROP TABLE "ucp_mcp_agents"');
                        DB::statement('ALTER TABLE "ucp_mcp_agents_new" RENAME TO "ucp_mcp_agents"');
                    }
                }

                DB::statement('PRAGMA foreign_keys=on');
            } else {
                DB::statement("ALTER TABLE `ucp_mcp_agents` MODIFY COLUMN `status` ENUM('active', 'inactive', 'training', 'error', 'maintenance') NOT NULL DEFAULT 'inactive'");
            }
        }
    }
};
