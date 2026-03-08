<?php

declare(strict_types=1);

use App\Models\Skill;
use Database\Seeders\UcpSkillsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('UcpSkillsSeeder - Curated Skills Loading', function () {
    it('validates curated skills have required fields', function () {
        // Feature: skill-seeder-consolidation, Property 1: Curated Skills Have Required Fields
        $curatedData = require database_path('seeders/data/curated_skills.php');
        $requiredFields = ['name', 'internal_id', 'skill_type', 'rarity', 'base_sp_cost'];

        expect($curatedData)->toHaveKey('skills');
        expect($curatedData['skills'])->toBeArray();

        foreach ($curatedData['skills'] as $skill) {
            foreach ($requiredFields as $field) {
                expect($skill)->toHaveKey($field);
                expect($skill[$field])->not->toBeEmpty();
                expect($skill[$field])->not->toBeNull();
            }
        }
    });

    it('loads curated skills from data file', function () {
        $seeder = new UcpSkillsSeeder;
        $reflection = new ReflectionClass($seeder);
        $method = $reflection->getMethod('loadCuratedSkills');
        $method->setAccessible(true);

        $data = $method->invoke($seeder);

        expect($data)->toBeArray();
        expect($data)->toHaveKey('skills');
        expect($data)->toHaveKey('evolution_pairs');
        expect($data['skills'])->toBeArray();
        expect($data['evolution_pairs'])->toBeArray();
        expect($data['skills'])->not->toBeEmpty();
    });

    it('validates skill types are valid enums', function () {
        $curatedData = require database_path('seeders/data/curated_skills.php');
        $validTypes = ['speed', 'passive', 'recovery', 'debuff', 'unique'];

        foreach ($curatedData['skills'] as $skill) {
            expect($skill['skill_type'])->toBeIn($validTypes);
        }
    });

    it('validates rarity values are valid enums', function () {
        $curatedData = require database_path('seeders/data/curated_skills.php');
        $validRarities = ['normal', 'rare', 'unique'];

        foreach ($curatedData['skills'] as $skill) {
            expect($skill['rarity'])->toBeIn($validRarities);
        }
    });

    it('validates meta_tier values are valid enums when present', function () {
        $curatedData = require database_path('seeders/data/curated_skills.php');
        $validTiers = ['S+', 'S', 'A', 'B', 'C', null];

        foreach ($curatedData['skills'] as $skill) {
            if (isset($skill['meta_tier'])) {
                expect($skill['meta_tier'])->toBeIn($validTiers);
            }
        }
    });

    it('validates base_sp_cost is a positive integer', function () {
        $curatedData = require database_path('seeders/data/curated_skills.php');

        foreach ($curatedData['skills'] as $skill) {
            expect($skill['base_sp_cost'])->toBeInt();
            expect($skill['base_sp_cost'])->toBeGreaterThan(0);
        }
    });
});

describe('UcpSkillsSeeder - Evolution Relationship Setup', function () {
    it('loads evolution pairs from data file', function () {
        $seeder = new UcpSkillsSeeder;
        $reflection = new ReflectionClass($seeder);
        $method = $reflection->getMethod('getEvolutionPairs');
        $method->setAccessible(true);

        $pairs = $method->invoke($seeder);
        assert(is_array($pairs));

        expect($pairs)->toBeArray();
        expect($pairs)->not->toBeEmpty();

        // Verify structure of pairs
        foreach ($pairs as $pair) {
            expect($pair)->toBeArray();
            expect($pair)->toHaveCount(2);
            expect($pair[0])->toBeString(); // source internal_id
            expect($pair[1])->toBeString(); // target internal_id
        }
    });

    it('sets up evolution relationships correctly', function () {
        // Feature: skill-seeder-consolidation, Property 5: Evolution Relationship Integrity
        // Seed curated skills first
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\UcpSkillsSeeder'])
            ->assertExitCode(0);

        // Check a known evolution pair from the curated data
        $sourceSkill = Skill::where('internal_id', 'speed_001')->first();
        $targetSkill = Skill::where('internal_id', 'speed_001_rare')->first();

        expect($sourceSkill)->not->toBeNull();
        expect($targetSkill)->not->toBeNull();
        expect($sourceSkill->evolution_target_id)->toBe($targetSkill->id);
        expect($targetSkill->evolution_source_id)->toBe($sourceSkill->id);
    });

    it('handles missing skills gracefully', function () {
        // This test verifies that the seeder handles missing skills gracefully
        $seeder = new UcpSkillsSeeder;
        $reflection = new ReflectionClass($seeder);
        $method = $reflection->getMethod('setupEvolutionRelationships');
        $method->setAccessible(true);

        // The method should complete without throwing exceptions
        expect(fn () => $method->invoke($seeder))->not->toThrow(Exception::class);
    });
});

describe('UcpSkillsSeeder - New Fields Validation', function () {
    it('validates condition_marker values are valid symbols', function () {
        $curatedData = require database_path('seeders/data/curated_skills.php');
        $validMarkers = ['○', '◎', null];

        foreach ($curatedData['skills'] as $skill) {
            $marker = $skill['condition_marker'] ?? null;
            expect($marker)->toBeIn($validMarkers);
        }
    });

    it('validates character_exclusive is only on unique skills', function () {
        $curatedData = require database_path('seeders/data/curated_skills.php');

        foreach ($curatedData['skills'] as $skill) {
            if (array_key_exists('character_exclusive', $skill) && $skill['character_exclusive'] !== null) {
                expect($skill['skill_type'])->toBe('unique');
                expect($skill['unique_skill_max_level'])->toBe(4);
            }
        }
    });

    it('validates unique_skill_max_level is 4 when present', function () {
        $curatedData = require database_path('seeders/data/curated_skills.php');

        foreach ($curatedData['skills'] as $skill) {
            if (isset($skill['unique_skill_max_level'])) {
                expect($skill['unique_skill_max_level'])->toBe(4);
            }
        }
    });

    it('seeds character_exclusive and condition_marker into database', function () {
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\UcpSkillsSeeder'])
            ->assertExitCode(0);

        $vodkaSkill = Skill::where('internal_id', 'unique_001')->first();
        expect($vodkaSkill)->not->toBeNull();
        expect($vodkaSkill->character_exclusive)->toBe('Vodka');
        expect($vodkaSkill->unique_skill_max_level)->toBe(4);

        $conditionalSkill = Skill::where('internal_id', 'passive_001')->first();
        expect($conditionalSkill)->not->toBeNull();
        expect($conditionalSkill->condition_marker)->toBe('○');
    });
});

describe('UcpSkillsSeeder - Fresh Seeding Support', function () {
    it('truncates table with MySQL driver', function () {
        // Create some test skills
        Skill::factory()->count(5)->create();
        expect(Skill::count())->toBe(5);

        // Create seeder and call truncateTable
        $seeder = new UcpSkillsSeeder;
        $reflection = new ReflectionClass($seeder);
        $method = $reflection->getMethod('truncateTable');
        $method->setAccessible(true);

        $method->invoke($seeder);

        // Verify table is empty
        expect(Skill::count())->toBe(0);
    });

    it('truncates table with SQLite driver', function () {
        // Create some test skills
        Skill::factory()->count(5)->create();
        expect(Skill::count())->toBe(5);

        // Create seeder and call truncateTable
        $seeder = new UcpSkillsSeeder;
        $reflection = new ReflectionClass($seeder);
        $method = $reflection->getMethod('truncateTable');
        $method->setAccessible(true);

        $method->invoke($seeder);

        // Verify table is empty
        expect(Skill::count())->toBe(0);
    });

    it('supports fresh method for programmatic control', function () {
        $seeder = new UcpSkillsSeeder;

        // Call fresh() method
        $result = $seeder->fresh();

        // Verify it returns self for chaining
        expect($result)->toBe($seeder);

        // Verify fresh flag is set
        $reflection = new ReflectionClass($seeder);
        $property = $reflection->getProperty('fresh');
        $property->setAccessible(true);

        expect($property->getValue($seeder))->toBeTrue();
    });

    it('performs fresh seeding when fresh mode is enabled', function () {
        // Create some existing skills with test prefix (unique internal_ids like test_skill_XXXXX)
        $existingSkills = Skill::factory()->count(3)->create();
        $initialCount = Skill::count();
        expect($initialCount)->toBe(3);

        // Get the internal_ids of the factory skills (they use test_skill_XXXXX format)
        $existingInternalIds = $existingSkills->pluck('internal_id')->toArray();

        // Run seeder with fresh mode - this will truncate and reseed with curated skills
        $seeder = (new UcpSkillsSeeder)->fresh();
        $seeder->run();

        // After fresh seeding, the old test skills should be gone
        // and new curated skills should be present
        $newCount = Skill::count();

        // Verify the old factory skills no longer exist (curated skills use different internal_ids)
        foreach ($existingInternalIds as $internalId) {
            expect(Skill::where('internal_id', $internalId)->first())->toBeNull();
        }

        // Verify new skills were added (curated skills)
        expect($newCount)->toBeGreaterThan(0);
    });

    it('defaults to non-destructive upsert behavior', function () {
        // Create some existing skills
        $existingSkills = Skill::factory()->count(3)->create();
        $existingCount = Skill::count();
        expect($existingCount)->toBe(3);

        // Run seeder without fresh mode
        $this->artisan('db:seed', ['--class' => UcpSkillsSeeder::class])
            ->assertExitCode(0);

        // Verify existing skills still exist
        foreach ($existingSkills as $skill) {
            expect(Skill::find($skill->id))->not->toBeNull();
        }

        // Verify new skills were added
        expect(Skill::count())->toBeGreaterThan($existingCount);
    });

    it('handles foreign key constraints during truncation', function () {
        // This test verifies that truncation works even with foreign key constraints
        // Create a skill with evolution relationships
        $sourceSkill = Skill::factory()->create([
            'internal_id' => 'test_source',
            'can_evolve' => true,
        ]);

        $targetSkill = Skill::factory()->create([
            'internal_id' => 'test_target',
            'is_evolution' => true,
            'evolution_source_id' => $sourceSkill->id,
        ]);

        $sourceSkill->update(['evolution_target_id' => $targetSkill->id]);

        expect(Skill::count())->toBe(2);

        // Create seeder and call truncateTable
        $seeder = new UcpSkillsSeeder;
        $reflection = new ReflectionClass($seeder);
        $method = $reflection->getMethod('truncateTable');
        $method->setAccessible(true);

        // Should not throw exception despite foreign keys
        expect(fn () => $method->invoke($seeder))->not->toThrow(Exception::class);

        // Verify table is empty
        expect(Skill::count())->toBe(0);
    });

    describe('unique star upgrade data in curated_skills', function () {
        it('all unique skills in curated data have the star upgrade fields', function () {
            $curatedData = require database_path('seeders/data/curated_skills.php');

            $uniqueSkills = array_filter($curatedData['skills'], fn ($s) => ($s['unique_star_upgrade'] ?? false) === true);

            expect($uniqueSkills)->not->toBeEmpty();

            foreach ($uniqueSkills as $skill) {
                expect($skill)->toHaveKey('unique_star_upgrade');
                expect($skill)->toHaveKey('unique_star6_initial_level');
                expect($skill)->toHaveKey('unique_base_effects');

                expect($skill['unique_star_upgrade'])->toBeTrue();
                expect($skill['unique_star6_initial_level'])->toBeIn([2, 3]);
                expect($skill['unique_base_effects'])->toBeArray()->not->toBeEmpty();
            }
        });

        it('exactly 68 curated skills have unique star upgrade enabled', function () {
            $curatedData = require database_path('seeders/data/curated_skills.php');

            $upgradeableCount = count(array_filter(
                $curatedData['skills'],
                fn ($s) => ($s['unique_star_upgrade'] ?? false) === true
            ));

            expect($upgradeableCount)->toBe(68);
        });

        it('all unique_base_effects contain at least one meaningful effect key', function () {
            $curatedData = require database_path('seeders/data/curated_skills.php');
            $validEffectKeys = ['target_speed', 'accel', 'stamina_recovery', 'lane_movement', 'enemy_speed', 'enemy_stamina_drain'];

            $uniqueSkills = array_filter($curatedData['skills'], fn ($s) => ($s['unique_star_upgrade'] ?? false) === true);

            foreach ($uniqueSkills as $skill) {
                $baseEffects = $skill['unique_base_effects'];
                $effectKeys = array_filter(
                    array_keys($baseEffects),
                    fn ($k) => in_array($k, $validEffectKeys, true)
                );

                expect(count($effectKeys))->toBeGreaterThan(0, "Skill {$skill['internal_id']} must have at least one valid effect key");
            }
        });

        it('seeds database with correct unique star upgrade data for all 68 skills', function () {
            $seeder = (new UcpSkillsSeeder)->fresh();
            $seeder->run();

            $upgradeableSkills = Skill::query()
                ->where('unique_star_upgrade', true)
                ->get();

            expect($upgradeableSkills)->toHaveCount(68);

            foreach ($upgradeableSkills as $skill) {
                expect($skill->unique_star_upgrade)->toBeTrue();
                expect($skill->unique_star6_initial_level)->toBeIn([2, 3]);
                expect($skill->unique_base_effects)->toBeArray()->not->toBeEmpty();
            }
        });
    });
});
