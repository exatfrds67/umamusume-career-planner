<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * VERIFIED (Jan 2026): S-rank is the maximum aptitude grade.
     * SS does NOT exist in the current game version.
     */
    public function up(): void
    {
        // Verify no SS data exists before migration
        $ssCount = DB::table('ucp_aptitudes')
            ->where('grade', 'SS')
            ->count();

        if ($ssCount > 0) {
            throw new \Exception(
                "Found {$ssCount} aptitudes with SS grade. ".
                    'SS rank does not exist in game. Manual review required before migration.'
            );
        }

        // For MySQL/MariaDB: Use ALTER TABLE MODIFY
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE ucp_aptitudes 
                MODIFY COLUMN grade ENUM(
                    'G', 'G+', 'F', 'F+', 'E', 'E+', 
                    'D', 'D+', 'C', 'C+', 'B', 'B+', 
                    'A', 'A+', 'S'
                ) COMMENT 'Aptitude grade (S is maximum, verified Jan 2026)'
            ");
        }

        // For SQLite: Recreate table (SQLite doesn't support MODIFY COLUMN)
        if (DB::connection()->getDriverName() === 'sqlite') {
            // SQLite doesn't enforce ENUM, so we just add a comment in the schema
            // The validation will be handled at the application level
            // No migration needed for SQLite as it stores as TEXT anyway
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // For MySQL/MariaDB: Restore SS and S+ for rollback
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE ucp_aptitudes 
                MODIFY COLUMN grade ENUM(
                    'G', 'G+', 'F', 'F+', 'E', 'E+', 
                    'D', 'D+', 'C', 'C+', 'B', 'B+', 
                    'A', 'A+', 'S', 'S+', 'SS'
                ) COMMENT 'Aptitude grade'
            ");
        }

        // For SQLite: No action needed
    }
};
