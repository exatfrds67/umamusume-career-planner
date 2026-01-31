<?php

declare(strict_types=1);

use App\Models\Skill;
use Database\Seeders\UcpSkillsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Fresh Seeding Integration', function () {
    it('can perform fresh seeding programmatically', function () {
        // Create some existing skills
        Skill::factory()->count(5)->create();
        $initialCount = Skill::count();
        expect($initialCount)->toBe(5);

        // Create a seeder instance and enable fresh mode
        $seeder = new UcpSkillsSeeder;
        $seeder->fresh();

        // Run the seeder
        $seeder->run();

        // Verify the table was truncated and reseeded
        $finalCount = Skill::count();

        // Should have curated skills now (not the factory-created ones)
        expect($finalCount)->toBeGreaterThan(0);

        // Verify none of the original test skills exist
        expect(Skill::where('internal_id', 'LIKE', 'test_skill_%')->count())->toBe(0);

        // Verify we have curated skills
        $curatedSkill = Skill::where('internal_id', 'speed_001')->first();
        expect($curatedSkill)->not->toBeNull();
    });

    it('preserves existing skills without fresh mode', function () {
        // Create some existing skills
        $existingSkills = Skill::factory()->count(3)->create();
        $existingIds = $existingSkills->pluck('id')->toArray();

        // Run seeder without fresh mode
        $seeder = new UcpSkillsSeeder;
        $seeder->run();

        // Verify existing skills still exist
        foreach ($existingIds as $id) {
            expect(Skill::find($id))->not->toBeNull();
        }

        // Verify new skills were added
        expect(Skill::count())->toBeGreaterThan(3);
    });

    it('handles evolution relationships after fresh seeding', function () {
        // Perform fresh seeding
        $seeder = new UcpSkillsSeeder;
        $seeder->fresh();
        $seeder->run();

        // Check that evolution relationships are properly set up
        $sourceSkill = Skill::where('internal_id', 'speed_001')->first();
        $targetSkill = Skill::where('internal_id', 'speed_001_rare')->first();

        expect($sourceSkill)->not->toBeNull();
        expect($targetSkill)->not->toBeNull();
        expect($sourceSkill->evolution_target_id)->toBe($targetSkill->id);
        expect($targetSkill->evolution_source_id)->toBe($sourceSkill->id);
    });
});
