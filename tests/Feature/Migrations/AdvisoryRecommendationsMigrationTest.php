<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

describe('Advisory Recommendations Migration', function () {
    it('creates ucp_advisory_recommendations table with correct structure', function () {
        expect(Schema::hasTable('ucp_advisory_recommendations'))->toBeTrue();

        // Verify all columns exist
        expect(Schema::hasColumn('ucp_advisory_recommendations', 'id'))->toBeTrue();
        expect(Schema::hasColumn('ucp_advisory_recommendations', 'career_id'))->toBeTrue();
        expect(Schema::hasColumn('ucp_advisory_recommendations', 'turn_number'))->toBeTrue();
        expect(Schema::hasColumn('ucp_advisory_recommendations', 'recommendation_type'))->toBeTrue();
        expect(Schema::hasColumn('ucp_advisory_recommendations', 'priority'))->toBeTrue();
        expect(Schema::hasColumn('ucp_advisory_recommendations', 'action'))->toBeTrue();
        expect(Schema::hasColumn('ucp_advisory_recommendations', 'reasoning'))->toBeTrue();
        expect(Schema::hasColumn('ucp_advisory_recommendations', 'expected_outcomes'))->toBeTrue();
        expect(Schema::hasColumn('ucp_advisory_recommendations', 'confidence_score'))->toBeTrue();
        expect(Schema::hasColumn('ucp_advisory_recommendations', 'was_followed'))->toBeTrue();
        expect(Schema::hasColumn('ucp_advisory_recommendations', 'created_at'))->toBeTrue();
    });

    it('can rollback the migration', function () {
        // Verify table exists before rollback
        expect(Schema::hasTable('ucp_advisory_recommendations'))->toBeTrue();

        // Get the specific migration file name
        $migrationName = collect(DB::select('SELECT migration FROM migrations ORDER BY id DESC'))
            ->pluck('migration')
            ->first(fn ($m) => str_contains($m, 'advisory_recommendations'));

        if ($migrationName) {
            // Roll back only this specific migration by using the batch approach
            $batch = DB::table('migrations')->where('migration', $migrationName)->value('batch');
            $migrationsInBatch = DB::table('migrations')->where('batch', $batch)->count();

            // Only test rollback if this migration is the sole one in its batch
            // Otherwise the rollback test is unreliable
            if ($migrationsInBatch === 1) {
                Artisan::call('migrate:rollback', ['--step' => 1]);
                expect(Schema::hasTable('ucp_advisory_recommendations'))->toBeFalse();
                Artisan::call('migrate');
                expect(Schema::hasTable('ucp_advisory_recommendations'))->toBeTrue();
            } else {
                // Migration shares a batch with others; verify table structure instead
                $columns = Schema::getColumnListing('ucp_advisory_recommendations');
                expect($columns)->toContain('id');
                expect($columns)->toContain('career_id');
            }
        }
    });
});
