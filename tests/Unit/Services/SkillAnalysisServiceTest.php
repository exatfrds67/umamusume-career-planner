<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\Skill;
use App\Services\SkillAnalysisService;

beforeEach(function () {
    $this->analysisService = new SkillAnalysisService;
    $this->character = Character::factory()->create();
});

describe('SkillAnalysisService', function () {
    describe('analyzeSynergies', function () {
        it('returns empty array for skills without synergies', function () {
            $skills = collect([
                Skill::factory()->create(['synergy_skills' => null]),
                Skill::factory()->create(['synergy_skills' => []]),
            ]);

            $result = $this->analysisService->analyzeSynergies($skills);

            expect($result)->toBeArray();
        });

        it('identifies synergy pairs between skills', function () {
            $skill1 = Skill::factory()->create([
                'internal_id' => 'skill_001',
                'synergy_skills' => ['skill_002'],
            ]);

            $skill2 = Skill::factory()->create([
                'internal_id' => 'skill_002',
                'synergy_skills' => ['skill_001'],
            ]);

            $skills = collect([$skill1, $skill2]);

            $result = $this->analysisService->analyzeSynergies($skills);

            expect($result)->toHaveKey($skill1->id);
            expect($result[$skill1->id])->toHaveKeys(['skill', 'synergies', 'synergy_count', 'synergy_strength']);
        });

        it('calculates synergy strength correctly', function () {
            $skill1 = Skill::factory()->create([
                'internal_id' => 'skill_001',
                'skill_type' => 'speed',
                'meta_tier' => 'S',
                'synergy_skills' => ['skill_002', 'skill_003'],
            ]);

            $skill2 = Skill::factory()->create([
                'internal_id' => 'skill_002',
                'skill_type' => 'speed',
                'meta_tier' => 'S',
            ]);

            $skill3 = Skill::factory()->create([
                'internal_id' => 'skill_003',
                'skill_type' => 'passive',
                'meta_tier' => 'A',
            ]);

            $skills = collect([$skill1, $skill2, $skill3]);

            $result = $this->analysisService->analyzeSynergies($skills);

            expect($result[$skill1->id]['synergy_strength'])->toBeGreaterThan(0);
        });
    });

    describe('recommendAcquisitionOrder', function () {
        it('returns empty array when no skills can be afforded', function () {
            $skills = collect([
                Skill::factory()->create(['base_sp_cost' => 500]),
            ]);

            $result = $this->analysisService->recommendAcquisitionOrder($skills, 100);

            expect($result)->toBeEmpty();
        });

        it('prioritizes evolvable skills', function () {
            $evolvableSkill = Skill::factory()->create([
                'can_evolve' => true,
                'meta_tier' => 'B',
                'base_sp_cost' => 100,
            ]);

            $nonEvolvableSkill = Skill::factory()->create([
                'can_evolve' => false,
                'meta_tier' => 'A',
                'base_sp_cost' => 100,
            ]);

            $skills = collect([$nonEvolvableSkill, $evolvableSkill]);

            $result = $this->analysisService->recommendAcquisitionOrder($skills, 500);

            expect($result)->not->toBeEmpty();
            // Evolvable skill should be prioritized
            expect($result[0]['skill']->id)->toBe($evolvableSkill->id);
        });

        it('includes reasoning for each recommendation', function () {
            $skill = Skill::factory()->create([
                'meta_tier' => 'S',
                'can_evolve' => true,
                'base_sp_cost' => 100,
            ]);

            $skills = collect([$skill]);

            $result = $this->analysisService->recommendAcquisitionOrder($skills, 500);

            expect($result[0])->toHaveKey('reasoning');
            expect($result[0]['reasoning'])->toBeString();
            expect(strlen($result[0]['reasoning']))->toBeGreaterThan(0);
        });

        it('calculates min and max costs correctly', function () {
            $skill = Skill::factory()->create([
                'base_sp_cost' => 200,
                'meta_tier' => 'A',
            ]);

            $skills = collect([$skill]);

            $result = $this->analysisService->recommendAcquisitionOrder($skills, 500);

            expect($result[0]['min_cost'])->toBeLessThanOrEqual($result[0]['max_cost']);
            expect($result[0]['max_cost'])->toBe(200);
        });
    });

    describe('analyzeSkillBuild', function () {
        it('returns comprehensive build analysis', function () {
            $skills = collect([
                Skill::factory()->create(['skill_type' => 'speed', 'meta_tier' => 'S']),
                Skill::factory()->create(['skill_type' => 'passive', 'meta_tier' => 'A']),
                Skill::factory()->create(['skill_type' => 'recovery', 'meta_tier' => 'B']),
            ]);

            $result = $this->analysisService->analyzeSkillBuild($this->character, $skills);

            expect($result)->toHaveKeys([
                'character_id',
                'total_skills',
                'skill_types',
                'meta_distribution',
                'synergy_analysis',
                'evolution_potential',
                'recommendations',
            ]);
            expect($result['total_skills'])->toBe(3);
        });

        it('analyzes skill type distribution', function () {
            $skills = collect([
                Skill::factory()->create(['skill_type' => 'speed']),
                Skill::factory()->create(['skill_type' => 'speed']),
                Skill::factory()->create(['skill_type' => 'passive']),
            ]);

            $result = $this->analysisService->analyzeSkillBuild($this->character, $skills);

            expect($result['skill_types']['speed'])->toBe(2);
            expect($result['skill_types']['passive'])->toBe(1);
        });

        it('analyzes meta tier distribution', function () {
            $skills = collect([
                Skill::factory()->create(['meta_tier' => 'S+']),
                Skill::factory()->create(['meta_tier' => 'S']),
                Skill::factory()->create(['meta_tier' => 'A']),
            ]);

            $result = $this->analysisService->analyzeSkillBuild($this->character, $skills);

            expect($result['meta_distribution']['S+'])->toBe(1);
            expect($result['meta_distribution']['S'])->toBe(1);
            expect($result['meta_distribution']['A'])->toBe(1);
        });

        it('analyzes evolution potential', function () {
            $skills = collect([
                Skill::factory()->create(['can_evolve' => true, 'is_evolution' => false]),
                Skill::factory()->create(['can_evolve' => false, 'is_evolution' => true]),
                Skill::factory()->create(['can_evolve' => false, 'is_evolution' => false]),
            ]);

            $result = $this->analysisService->analyzeSkillBuild($this->character, $skills);

            expect($result['evolution_potential']['evolvable_count'])->toBe(1);
            expect($result['evolution_potential']['evolved_count'])->toBe(1);
        });

        it('generates build recommendations', function () {
            // Create a build with no speed skills
            $skills = collect([
                Skill::factory()->create(['skill_type' => 'passive']),
                Skill::factory()->create(['skill_type' => 'recovery']),
            ]);

            $result = $this->analysisService->analyzeSkillBuild($this->character, $skills);

            expect($result['recommendations'])->toBeArray();
            // Should recommend adding speed skills
            $hasSpeedRecommendation = collect($result['recommendations'])
                ->contains(fn ($rec) => str_contains(strtolower($rec['message']), 'speed'));
            expect($hasSpeedRecommendation)->toBeTrue();
        });
    });
});
