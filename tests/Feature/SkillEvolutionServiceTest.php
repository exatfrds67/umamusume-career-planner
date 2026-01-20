<?php

/**
 * @property \App\Services\SkillHintService $hintService
 * @property \App\Services\SkillEvolutionService $evolutionService
 * @property \App\Models\Character $character
 * @property \App\Models\Skill $normalSkill
 * @property \App\Models\Skill $rareSkill
 */

use App\Models\Character;
use App\Models\Skill;
use App\Models\SkillAcquisition;
use App\Models\SkillHint;
use App\Services\SkillEvolutionService;
use App\Services\SkillHintService;
use Illuminate\Foundation\Testing\RefreshDatabase;


beforeEach(function () {
    $this->hintService = app(SkillHintService::class);
    $this->evolutionService = new SkillEvolutionService($this->hintService);

    // Create test character
    $this->character = Character::factory()->create([
        'current_stats' => [
            'speed' => 800,
            'stamina' => 700,
            'power' => 600,
            'guts' => 500,
            'wit' => 650,
        ],
    ]);

    // Create Normal skill that can evolve
    $this->normalSkill = Skill::factory()->create([
        'name' => 'Go with the Flow',
        'rarity' => 'normal',
        'base_sp_cost' => 120,
        'can_evolve' => true,
        'is_evolution' => false,
        'meta_tier' => 'A',
    ]);

    // Create Rare evolution target
    $this->rareSkill = Skill::factory()->create([
        'name' => 'Lane Legerdemain',
        'rarity' => 'rare',
        'base_sp_cost' => 180,
        'can_evolve' => false,
        'is_evolution' => true,
        'meta_tier' => 'S',
        'stat_requirements' => [
            'speed' => 600,
            'wit' => 500,
        ],
    ]);

    // Link evolution relationship
    $this->normalSkill->update(['evolution_target_id' => $this->rareSkill->id]);
    $this->rareSkill->update(['evolution_source_id' => $this->normalSkill->id]);
});

describe('Evolution Capability Checking', function () {
    it('detects when a skill can evolve', function () {
        // Acquire the Normal skill
        SkillAcquisition::factory()->create([
            'character_id' => $this->character->id,
            'skill_id' => $this->normalSkill->id,
            'is_active' => true,
        ]);

        $canEvolve = $this->evolutionService->canEvolve($this->character, $this->normalSkill);

        expect($canEvolve)->toBeTrue();
    });

    it('returns false when skill is not acquired', function () {
        $canEvolve = $this->evolutionService->canEvolve($this->character, $this->normalSkill);

        expect($canEvolve)->toBeFalse();
    });

    it('returns false when skill has no evolution path', function () {
        $noEvolutionSkill = Skill::factory()->create([
            'can_evolve' => false,
            'evolution_target_id' => null,
        ]);

        SkillAcquisition::factory()->create([
            'character_id' => $this->character->id,
            'skill_id' => $noEvolutionSkill->id,
            'is_active' => true,
        ]);

        $canEvolve = $this->evolutionService->canEvolve($this->character, $noEvolutionSkill);

        expect($canEvolve)->toBeFalse();
    });

    it('checks stat requirements for evolution', function () {
        // Create character with insufficient stats
        $weakCharacter = Character::factory()->create([
            'current_stats' => [
                'speed' => 400, // Below required 600
                'wit' => 300,   // Below required 500
            ],
        ]);

        SkillAcquisition::factory()->create([
            'character_id' => $weakCharacter->id,
            'skill_id' => $this->normalSkill->id,
            'is_active' => true,
        ]);

        $canEvolve = $this->evolutionService->canEvolve($weakCharacter, $this->normalSkill);

        expect($canEvolve)->toBeFalse();
    });
});

describe('Skill Evolution Process', function () {
    it('successfully evolves a Normal skill to Rare', function () {
        // Acquire the Normal skill
        SkillAcquisition::factory()->create([
            'character_id' => $this->character->id,
            'skill_id' => $this->normalSkill->id,
            'is_active' => true,
        ]);

        $result = $this->evolutionService->evolveSkill($this->character, $this->normalSkill);

        expect($result['success'])->toBeTrue()
            ->and($result['rare_skill']->id)->toBe($this->rareSkill->id)
            ->and($result['normal_skill']->id)->toBe($this->normalSkill->id);

        // Verify Normal skill is deactivated
        $normalAcquisition = SkillAcquisition::where('character_id', $this->character->id)
            ->where('skill_id', $this->normalSkill->id)
            ->first();

        expect($normalAcquisition->is_active)->toBeFalse();

        // Verify Rare skill is acquired
        $rareAcquisition = SkillAcquisition::where('character_id', $this->character->id)
            ->where('skill_id', $this->rareSkill->id)
            ->where('is_active', true)
            ->first();

        expect($rareAcquisition)->not->toBeNull()
            ->and($rareAcquisition->is_evolution)->toBeTrue()
            ->and($rareAcquisition->evolved_from_skill_id)->toBe($this->normalSkill->id)
            ->and($rareAcquisition->replaced_skill)->toBeTrue();
    });

    it('applies hint discounts during evolution', function () {
        // Acquire the Normal skill
        SkillAcquisition::factory()->create([
            'character_id' => $this->character->id,
            'skill_id' => $this->normalSkill->id,
            'is_active' => true,
        ]);

        // Add 2 hints for the Rare skill (40% discount)
        SkillHint::factory()->count(2)->create([
            'character_id' => $this->character->id,
            'skill_id' => $this->rareSkill->id,
            'is_used' => false,
        ]);

        $result = $this->evolutionService->evolveSkill($this->character, $this->normalSkill);

        expect($result['success'])->toBeTrue()
            ->and($result['hints_used'])->toBe(2)
            ->and($result['sp_saved'])->toBe(72) // 40% of 180 SP
            ->and($result['sp_cost'])->toBe(108); // 180 - 72
    });

    it('fails evolution when prerequisites are not met', function () {
        // Don't acquire the Normal skill
        $result = $this->evolutionService->evolveSkill($this->character, $this->normalSkill);

        expect($result['success'])->toBeFalse()
            ->and($result['message'])->toContain('cannot be evolved');
    });

    it('records evolution context in acquisition metadata', function () {
        SkillAcquisition::factory()->create([
            'character_id' => $this->character->id,
            'skill_id' => $this->normalSkill->id,
            'is_active' => true,
        ]);

        $result = $this->evolutionService->evolveSkill($this->character, $this->normalSkill);

        $acquisition = $result['acquisition'];

        expect($acquisition->acquisition_context)->toHaveKey('evolution_type')
            ->and($acquisition->acquisition_context['evolution_type'])->toBe('automatic')
            ->and($acquisition->acquisition_context['normal_skill_name'])->toBe('Go with the Flow')
            ->and($acquisition->acquisition_context['rare_skill_name'])->toBe('Lane Legerdemain');
    });
});

describe('SP Efficiency Calculations', function () {
    it('calculates evolution path vs direct acquisition costs', function () {
        $efficiency = $this->evolutionService->calculateEvolutionEfficiency(
            $this->character,
            $this->normalSkill
        );

        expect($efficiency['has_evolution'])->toBeTrue()
            ->and($efficiency['normal_skill']['base_cost'])->toBe(120)
            ->and($efficiency['rare_skill']['base_cost'])->toBe(180)
            ->and($efficiency)->toHaveKey('evolution_path')
            ->and($efficiency)->toHaveKey('direct_acquisition')
            ->and($efficiency)->toHaveKey('comparison');
    });

    it('recommends evolution when it saves SP', function () {
        // Add hints for Normal skill (cheaper evolution path)
        SkillHint::factory()->count(2)->create([
            'character_id' => $this->character->id,
            'skill_id' => $this->normalSkill->id,
            'is_used' => false,
        ]);

        $efficiency = $this->evolutionService->calculateEvolutionEfficiency(
            $this->character,
            $this->normalSkill
        );

        // Normal: 120 - 40% = 72 SP
        // Rare: 180 SP (no hints)
        // Evolution path: 72 + 180 = 252 SP
        // Direct: 180 SP
        // Direct is better by 72 SP

        expect($efficiency['comparison']['is_evolution_better'])->toBeFalse()
            ->and($efficiency['comparison']['sp_savings'])->toBeLessThan(0);
    });

    it('accounts for hints on both skills in efficiency calculation', function () {
        // Add hints for both skills
        SkillHint::factory()->count(2)->create([
            'character_id' => $this->character->id,
            'skill_id' => $this->normalSkill->id,
            'is_used' => false,
        ]);

        SkillHint::factory()->count(2)->create([
            'character_id' => $this->character->id,
            'skill_id' => $this->rareSkill->id,
            'is_used' => false,
        ]);

        $efficiency = $this->evolutionService->calculateEvolutionEfficiency(
            $this->character,
            $this->normalSkill
        );

        // Normal: 120 - 40% = 72 SP
        // Rare: 180 - 40% = 108 SP
        // Evolution path: 72 + 108 = 180 SP
        // Direct: 108 SP
        // Direct is better by 72 SP

        expect($efficiency['evolution_path']['total_cost'])->toBe(180)
            ->and($efficiency['direct_acquisition']['total_cost'])->toBe(108)
            ->and($efficiency['comparison']['sp_savings'])->toBe(-72);
    });
});

describe('Evolution Opportunities', function () {
    it('identifies all evolution opportunities for a character', function () {
        // Create another evolvable skill
        $anotherNormal = Skill::factory()->create([
            'name' => 'Homestretch Haste',
            'rarity' => 'normal',
            'can_evolve' => true,
            'base_sp_cost' => 140,
        ]);

        $anotherRare = Skill::factory()->create([
            'name' => 'In Body and Mind',
            'rarity' => 'rare',
            'is_evolution' => true,
            'base_sp_cost' => 200,
        ]);

        $anotherNormal->update(['evolution_target_id' => $anotherRare->id]);
        $anotherRare->update(['evolution_source_id' => $anotherNormal->id]);

        // Acquire first Normal skill
        SkillAcquisition::factory()->create([
            'character_id' => $this->character->id,
            'skill_id' => $this->normalSkill->id,
            'is_active' => true,
        ]);

        $opportunities = $this->evolutionService->getEvolutionOpportunities($this->character);

        expect($opportunities)->toHaveCount(2)
            ->and($opportunities[0])->toHaveKey('skill')
            ->and($opportunities[0])->toHaveKey('can_evolve_now')
            ->and($opportunities[0])->toHaveKey('efficiency')
            ->and($opportunities[0])->toHaveKey('priority');
    });

    it('prioritizes ready-to-evolve skills higher', function () {
        // Acquire the Normal skill (ready to evolve)
        SkillAcquisition::factory()->create([
            'character_id' => $this->character->id,
            'skill_id' => $this->normalSkill->id,
            'is_active' => true,
        ]);

        // Create another skill not yet acquired
        $notAcquired = Skill::factory()->create([
            'rarity' => 'normal',
            'can_evolve' => true,
        ]);

        $notAcquiredRare = Skill::factory()->create([
            'rarity' => 'rare',
            'is_evolution' => true,
        ]);

        $notAcquired->update(['evolution_target_id' => $notAcquiredRare->id]);

        $opportunities = $this->evolutionService->getEvolutionOpportunities($this->character);

        // First opportunity should be the one that can evolve now
        expect($opportunities[0]['can_evolve_now'])->toBeTrue()
            ->and($opportunities[0]['priority'])->toBeGreaterThan($opportunities[1]['priority']);
    });
});

describe('Evolution Chain Tracking', function () {
    it('retrieves complete evolution chain for a Normal skill', function () {
        $chain = $this->evolutionService->getEvolutionChain($this->normalSkill);

        expect($chain)->toHaveCount(2)
            ->and($chain[0]['position'])->toBe('source')
            ->and($chain[0]['rarity'])->toBe('normal')
            ->and($chain[1]['position'])->toBe('target')
            ->and($chain[1]['rarity'])->toBe('rare');
    });

    it('retrieves evolution chain for a Rare skill', function () {
        $chain = $this->evolutionService->getEvolutionChain($this->rareSkill);

        expect($chain)->toHaveCount(2)
            ->and($chain[0]['position'])->toBe('source')
            ->and($chain[1]['position'])->toBe('target');
    });
});

describe('Evolution Timing Planning', function () {
    it('plans optimal evolution timing for target skills', function () {
        // Acquire the Normal skill
        SkillAcquisition::factory()->create([
            'character_id' => $this->character->id,
            'skill_id' => $this->normalSkill->id,
            'is_active' => true,
        ]);

        $targetSkills = collect([$this->normalSkill]);
        $plan = $this->evolutionService->planEvolutionTiming($this->character, $targetSkills);

        expect($plan)->toHaveCount(1)
            ->and($plan[0]['can_evolve_now'])->toBeTrue()
            ->and($plan[0]['timing'])->toBe('immediate')
            ->and($plan[0]['recommendation'])->toContain('Ready to evolve now');
    });

    it('identifies delayed timing when prerequisites are not met', function () {
        // Don't acquire the Normal skill
        $targetSkills = collect([$this->normalSkill]);
        $plan = $this->evolutionService->planEvolutionTiming($this->character, $targetSkills);

        expect($plan)->toHaveCount(1)
            ->and($plan[0]['can_evolve_now'])->toBeFalse()
            ->and($plan[0]['timing'])->toBe('delayed')
            ->and($plan[0]['recommendation'])->toContain('Wait until');
    });

    it('sorts immediate opportunities before delayed ones', function () {
        // Create two skills: one ready, one not
        SkillAcquisition::factory()->create([
            'character_id' => $this->character->id,
            'skill_id' => $this->normalSkill->id,
            'is_active' => true,
        ]);

        $notReady = Skill::factory()->create([
            'rarity' => 'normal',
            'can_evolve' => true,
        ]);

        $notReadyRare = Skill::factory()->create([
            'rarity' => 'rare',
            'is_evolution' => true,
        ]);

        $notReady->update(['evolution_target_id' => $notReadyRare->id]);

        $targetSkills = collect([$this->normalSkill, $notReady]);
        $plan = $this->evolutionService->planEvolutionTiming($this->character, $targetSkills);

        expect($plan[0]['timing'])->toBe('immediate')
            ->and($plan[1]['timing'])->toBe('delayed');
    });
});

describe('Evolution Roadmap', function () {
    it('generates comprehensive evolution roadmap', function () {
        // Acquire the Normal skill
        SkillAcquisition::factory()->create([
            'character_id' => $this->character->id,
            'skill_id' => $this->normalSkill->id,
            'is_active' => true,
        ]);

        $roadmap = $this->evolutionService->getEvolutionRoadmap($this->character);

        expect($roadmap)->toHaveKey('character_id')
            ->and($roadmap)->toHaveKey('total_evolution_opportunities')
            ->and($roadmap)->toHaveKey('ready_to_evolve')
            ->and($roadmap)->toHaveKey('pending_prerequisites')
            ->and($roadmap)->toHaveKey('total_potential_sp_savings')
            ->and($roadmap)->toHaveKey('immediate_opportunities')
            ->and($roadmap)->toHaveKey('future_opportunities')
            ->and($roadmap)->toHaveKey('recommendations');
    });

    it('separates ready and pending evolution opportunities', function () {
        // Acquire one skill, leave another unacquired
        SkillAcquisition::factory()->create([
            'character_id' => $this->character->id,
            'skill_id' => $this->normalSkill->id,
            'is_active' => true,
        ]);

        $notAcquired = Skill::factory()->create([
            'rarity' => 'normal',
            'can_evolve' => true,
        ]);

        $notAcquiredRare = Skill::factory()->create([
            'rarity' => 'rare',
            'is_evolution' => true,
        ]);

        $notAcquired->update(['evolution_target_id' => $notAcquiredRare->id]);

        $roadmap = $this->evolutionService->getEvolutionRoadmap($this->character);

        expect($roadmap['ready_to_evolve'])->toBe(1)
            ->and($roadmap['pending_prerequisites'])->toBe(1)
            ->and($roadmap['immediate_opportunities'])->toHaveCount(1)
            ->and($roadmap['future_opportunities'])->toHaveCount(1);
    });

    it('calculates total potential SP savings', function () {
        SkillAcquisition::factory()->create([
            'character_id' => $this->character->id,
            'skill_id' => $this->normalSkill->id,
            'is_active' => true,
        ]);

        $roadmap = $this->evolutionService->getEvolutionRoadmap($this->character);

        expect($roadmap['total_potential_sp_savings'])->toBeGreaterThanOrEqual(0);
    });

    it('provides actionable recommendations', function () {
        SkillAcquisition::factory()->create([
            'character_id' => $this->character->id,
            'skill_id' => $this->normalSkill->id,
            'is_active' => true,
        ]);

        $roadmap = $this->evolutionService->getEvolutionRoadmap($this->character);

        expect($roadmap['recommendations'])->not->toBeEmpty()
            ->and($roadmap['recommendations'][0])->toHaveKey('type')
            ->and($roadmap['recommendations'][0])->toHaveKey('priority')
            ->and($roadmap['recommendations'][0])->toHaveKey('message');
    });
});

describe('Prerequisite Checking', function () {
    it('validates stat requirements for evolution', function () {
        $result = $this->evolutionService->checkEvolutionPrerequisites(
            $this->character,
            $this->normalSkill
        );

        // Character has speed: 800, wit: 650 (both above required 600 and 500)
        expect($result)->toBeTrue();
    });

    it('fails when stat requirements are not met', function () {
        $weakCharacter = Character::factory()->create([
            'current_stats' => [
                'speed' => 400,
                'wit' => 300,
            ],
        ]);

        $result = $this->evolutionService->checkEvolutionPrerequisites(
            $weakCharacter,
            $this->normalSkill
        );

        expect($result)->toBeFalse();
    });

    it('checks prerequisite skills when specified', function () {
        // Create a skill with prerequisite requirements
        $prerequisiteSkill = Skill::factory()->create([
            'rarity' => 'normal',
            'internal_id' => 'prereq_skill_001',
        ]);

        $skillWithPrereq = Skill::factory()->create([
            'rarity' => 'normal',
            'can_evolve' => true,
        ]);

        $rareWithPrereq = Skill::factory()->create([
            'rarity' => 'rare',
            'is_evolution' => true,
            'synergy_skills' => ['prereq_skill_001'],
        ]);

        $skillWithPrereq->update(['evolution_target_id' => $rareWithPrereq->id]);

        // Without prerequisite acquired
        $result = $this->evolutionService->checkEvolutionPrerequisites(
            $this->character,
            $skillWithPrereq
        );

        expect($result)->toBeFalse();

        // With prerequisite acquired
        SkillAcquisition::factory()->create([
            'character_id' => $this->character->id,
            'skill_id' => $prerequisiteSkill->id,
            'is_active' => true,
        ]);

        $result = $this->evolutionService->checkEvolutionPrerequisites(
            $this->character,
            $skillWithPrereq
        );

        expect($result)->toBeTrue();
    });
});
