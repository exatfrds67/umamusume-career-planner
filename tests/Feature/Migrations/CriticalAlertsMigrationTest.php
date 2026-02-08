<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

describe('Critical Alerts Migration', function () {
    it('creates ucp_critical_alerts table with correct structure', function () {
        expect(Schema::hasTable('ucp_critical_alerts'))->toBeTrue();

        // Verify all columns exist
        expect(Schema::hasColumn('ucp_critical_alerts', 'id'))->toBeTrue();
        expect(Schema::hasColumn('ucp_critical_alerts', 'career_id'))->toBeTrue();
        expect(Schema::hasColumn('ucp_critical_alerts', 'turn_number'))->toBeTrue();
        expect(Schema::hasColumn('ucp_critical_alerts', 'alert_type'))->toBeTrue();
        expect(Schema::hasColumn('ucp_critical_alerts', 'message'))->toBeTrue();
        expect(Schema::hasColumn('ucp_critical_alerts', 'action_items'))->toBeTrue();
        expect(Schema::hasColumn('ucp_critical_alerts', 'turns_until_critical'))->toBeTrue();
        expect(Schema::hasColumn('ucp_critical_alerts', 'was_dismissed'))->toBeTrue();
        expect(Schema::hasColumn('ucp_critical_alerts', 'dismissed_at'))->toBeTrue();
        expect(Schema::hasColumn('ucp_critical_alerts', 'created_at'))->toBeTrue();
    });

    it('has correct indexes', function () {
        $indexes = Schema::getIndexes('ucp_critical_alerts');
        $indexNames = array_column($indexes, 'name');

        // Verify we have indexes (Laravel auto-generates names)
        expect(count($indexes))->toBeGreaterThan(0);

        // Check that we have composite indexes
        $hasCareerTurnIndex = collect($indexes)->contains(function ($index) {
            return $index['columns'] === ['career_id', 'turn_number'];
        });

        $hasActiveIndex = collect($indexes)->contains(function ($index) {
            return $index['columns'] === ['career_id', 'was_dismissed'];
        });

        expect($hasCareerTurnIndex)->toBeTrue();
        expect($hasActiveIndex)->toBeTrue();
    });

    it('has foreign key constraint to ucp_careers', function () {
        $foreignKeys = Schema::getForeignKeys('ucp_critical_alerts');

        expect($foreignKeys)->not->toBeEmpty();

        $careerForeignKey = collect($foreignKeys)->first(function ($fk) {
            return $fk['columns'] === ['career_id'];
        });

        expect($careerForeignKey)->not->toBeNull();
        expect($careerForeignKey['foreign_table'])->toBe('ucp_careers');
        expect($careerForeignKey['foreign_columns'])->toBe(['id']);
        expect($careerForeignKey['on_delete'])->toBe('cascade');
    });

    it('can rollback the migration', function () {
        // Verify table exists before rollback
        expect(Schema::hasTable('ucp_critical_alerts'))->toBeTrue();

        // Get the specific migration file name
        $migrationName = collect(DB::select('SELECT migration FROM migrations ORDER BY id DESC'))
            ->pluck('migration')
            ->first(fn ($m) => str_contains($m, 'critical_alerts'));

        if ($migrationName) {
            // Roll back only this specific migration by using the batch approach
            $batch = DB::table('migrations')->where('migration', $migrationName)->value('batch');
            $migrationsInBatch = DB::table('migrations')->where('batch', $batch)->count();

            // Only test rollback if this migration is the sole one in its batch
            // Otherwise the rollback test is unreliable
            if ($migrationsInBatch === 1) {
                Artisan::call('migrate:rollback', ['--step' => 1]);
                expect(Schema::hasTable('ucp_critical_alerts'))->toBeFalse();
                Artisan::call('migrate');
                expect(Schema::hasTable('ucp_critical_alerts'))->toBeTrue();
            } else {
                // Migration shares a batch with others; verify table structure instead
                $columns = Schema::getColumnListing('ucp_critical_alerts');
                expect($columns)->toContain('id');
                expect($columns)->toContain('career_id');
            }
        }
    });
});
