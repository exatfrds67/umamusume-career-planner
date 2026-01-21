<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\Skill;
use App\Models\SkillAcquisition;
use App\Services\SkillEvolutionService;
use App\Services\SkillHintService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    /** @var SkillHintService&Mockery\MockInterface $hintService */
    $hintService = Mockery::mock(SkillHintService::class);
    $this->hintService = $hintService;
    $this->evolutionService = new SkillEvolutionService($hintService);
    $this->character = Character::factory()->create([
        'current_stats' => [
            'speed' => 800,
            'stamina' => 700,
            'power' => 600,
            'guts' => 500,
            'wit' => 500,
        ],
    ]);
});

afterEach(function () {
    Mockery::close();
});

describe('SkillEvolutionService', function () {
    describe('canEvolve', function () {
        it('returns false for skill without evolution capability', function () {
            $skill = Skill::factory()->create([
                'can_evolve' => false,
                'evolution_target_id' => null,
            ]);

            $result = $this->evolutionService->canEvolve($this->character, $skill);

            expect($result)->toBeFalse();
        });

        it('returns false when character has not acquired the skill', function () {
            $rareSkill = Skill::factory()->create([
                'rarity' => 'rare',
                'is_evolution' => true,
            ]);

            $normalSkill = Skill::factory()->create([
                'can_evolve' => true,
                'evolution_target_id' => $rareSkill->id,
                'rarity' => 'normal',
            ]);

            $result = $this->evolutionService->canEvolve($this->character, $normalSkill);

            expect($result)->toBeFalse();
        });

        it('returns true when all prerequisites are met', function () {
            $rareSkill = Skill::factory()->create([
                'rarity' => 'rare',
                'is_evolution' => true,
                'stat_requirements' => null,
                'synergy_skills' => null,
            ]);

            $normalSkill = Skill::factory()->create([
                'can_evolve' => true,
                'evolution_target_id' => $rareSkill->id,
                'rarity' => 'normal',
            ]);

            // Character has acquired the skill
            SkillAcquisition::factory()->create([
                'character_id' => $this->character->id,
                'skill_id' => $normalSkill->id,
                'is_active' => true,
            ]);

            $result = $this->evolutionService->canEvolve($this->character, $normalSkill);

            expect($result)->toBeTrue();
        });
    });

    describe('checkEvolutionPrerequisites', function () {
        it('returns false when stat requirements not met', function () {
            $rareSkill = Skill::factory()->create([
                'rarity' => 'rare',
                'is_evolution' => true,
                'stat_requirements' => ['speed' => 1000], // Character has 800
            ]);

            $normalSkill = Skill::factory()->create([
                'can_evolve' => true,
                'evolution_target_id' => $rareSkill->id,
                'rarity' => 'normal',
            ]);

            $result = $this->evolutionService->checkEvolutionPrerequisites($this->character, $normalSkill);

            expect($result)->toBeFalse();
        });

        it('returns true when stat requirements are met', function () {
            $rareSkill = Skill::factory()->create([
                'rarity' => 'rare',
                'is_evolution' => true,
                'stat_requirements' => ['speed' => 700], // Character has 800
            ]);

            $normalSkill = Skill::factory()->create([
                'can_evolve' => true,
                'evolution_target_id' => $rareSkill->id,
                'rarity' => 'normal',
            ]);

            $result = $this->evolutionService->checkEvolutionPrerequisites($this->character, $normalSkill);

            expect($result)->toBeTrue();
        });
    });

    describe('evolveSkill', function () {
        it('returns failure when skill cannot evolve', function () {
            $skill = Skill::factory()->create([
                'can_evolve' => false,
            ]);

            $result = $this->evolutionService->evolveSkill($this->character, $skill);

            expect($result['success'])->toBeFalse();
            expect($result)->toHaveKey('reason');
        });

        it('successfully evolves a skill when prerequisites are met', function () {
            $rareSkill = Skill::factory()->create([
                'name' => 'Lane Legerdemain',
                'rarity' => 'rare',
                'is_evolution' => true,
                'base_sp_cost' => 180,
                'stat_requirements' => null,
                'synergy_skills' => null,
            ]);

            $normalSkill = Skill::factory()->create([
                'name' => 'Go with the Flow',
                'can_evolve' => true,
                'evolution_target_id' => $rareSkill->id,
                'rarity' => 'normal',
                'base_sp_cost' => 120,
            ]);

            // Character has acquired the normal skill
            SkillAcquisition::factory()->create([
                'character_id' => $this->character->id,
                'skill_id' => $normalSkill->id,
                'is_active' => true,
            ]);

            // Mock hint service
            $this->hintService->shouldReceive('getUnusedHintsForSkill')
                ->andReturn(collect([]));
            $this->hintService->shouldReceive('calculateDiscountPercentage')
                ->andReturn(0.0);
            $this->hintService->shouldReceive('calculateFinalCost')
                ->andReturn(180);
            $this->hintService->shouldReceive('calculateSpSaved')
                ->andReturn(0);
            $this->hintService->shouldReceive('markHintsAsUsed')
                ->andReturn(true);

            $result = $this->evolutionService->evolveSkill($this->character, $normalSkill);

            expect($result['success'])->toBeTrue();
            expect($result['rare_skill']->id)->toBe($rareSkill->id);
            expect($result)->toHaveKeys(['sp_cost', 'sp_saved', 'hints_used']);
        });
    });

    describe('calculateEvolutionEfficiency', function () {
        it('returns no evolution message for non-evolvable skill', function () {
            $skill = Skill::factory()->create([
                'can_evolve' => false,
                'evolution_target_id' => null,
            ]);

            $result = $this->evolutionService->calculateEvolutionEfficiency($this->character, $skill);

            expect($result['has_evolution'])->toBeFalse();
            expect($result['message'])->toBe('Skill does not have an evolution path');
        });

        it('calculates efficiency comparison for evolvable skill', function () {
            $rareSkill = Skill::factory()->create([
                'name' => 'Rare Skill',
                'rarity' => 'rare',
                'is_evolution' => true,
                'base_sp_cost' => 200,
            ]);

            $normalSkill = Skill::factory()->create([
                'name' => 'Normal Skill',
                'can_evolve' => true,
                'evolution_target_id' => $rareSkill->id,
                'rarity' => 'normal',
                'base_sp_cost' => 120,
            ]);

            // Mock hint service
            $this->hintService->shouldReceive('getUnusedHintsForSkill')
                ->andReturn(collect([]));
            $this->hintService->shouldReceive('calculateFinalCost')
                ->andReturn(120, 200);

            $result = $this->evolutionService->calculateEvolutionEfficiency($this->character, $normalSkill);

            expect($result['has_evolution'])->toBeTrue();
            expect($result)->toHaveKeys([
                'normal_skill',
                'rare_skill',
                'evolution_path',
                'direct_acquisition',
                'comparison',
            ]);
            expect($result['comparison'])->toHaveKeys([
                'sp_savings',
                'is_evolution_better',
                'recommendation',
            ]);
        });
    });

    describe('getEvolutionChain', function () {
        it('returns evolution chain for normal skill', function () {
            $rareSkill = Skill::factory()->create([
                'rarity' => 'rare',
                'is_evolution' => true,
            ]);

            $normalSkill = Skill::factory()->create([
                'can_evolve' => true,
                'evolution_target_id' => $rareSkill->id,
                'rarity' => 'normal',
            ]);

            $chain = $this->evolutionService->getEvolutionChain($normalSkill);

            expect($chain)->toBeArray();
            expect(count($chain))->toBeGreaterThanOrEqual(1);
        });
    });
});
