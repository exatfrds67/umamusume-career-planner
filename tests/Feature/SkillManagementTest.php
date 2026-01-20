<?php

use App\Models\Skill;
use Illuminate\Foundation\Testing\RefreshDatabase;


beforeEach(function () {
    // Seed skills before each test
    $this->artisan('db:seed', ['--class' => 'ComprehensiveSkillSeeder']);
});

describe('Skill Model', function () {
    it('can calculate final cost with hints', function () {
        $skill = Skill::where('internal_id', 'speed_001')->first();

        expect($skill->calculateFinalCost(0))->toBe(120)
            ->and($skill->calculateFinalCost(1))->toBe(96)  // 20% discount
            ->and($skill->calculateFinalCost(2))->toBe(72)  // 40% discount (max)
            ->and($skill->calculateFinalCost(3))->toBe(72); // Still 40% (max)
    });

    it('can get discount percentage', function () {
        $skill = Skill::where('internal_id', 'speed_001')->first();

        expect($skill->getDiscountPercentage(0))->toBe(0.0)
            ->and($skill->getDiscountPercentage(1))->toBe(20.0)
            ->and($skill->getDiscountPercentage(2))->toBe(40.0)
            ->and($skill->getDiscountPercentage(3))->toBe(40.0); // Max 40%
    });

    it('can calculate SP saved', function () {
        $skill = Skill::where('internal_id', 'speed_001')->first();

        expect($skill->getSpSaved(0))->toBe(0)
            ->and($skill->getSpSaved(1))->toBe(24)  // 20% of 120
            ->and($skill->getSpSaved(2))->toBe(48); // 40% of 120
    });

    it('can check if skill can evolve', function () {
        $normalSkill = Skill::where('internal_id', 'speed_001')->first();
        $rareSkill = Skill::where('internal_id', 'speed_001_rare')->first();

        expect($normalSkill->canEvolve())->toBeTrue()
            ->and($rareSkill->canEvolve())->toBeFalse();
    });

    it('can check if skill is evolved', function () {
        $normalSkill = Skill::where('internal_id', 'speed_001')->first();
        $rareSkill = Skill::where('internal_id', 'speed_001_rare')->first();

        expect($normalSkill->isEvolved())->toBeFalse()
            ->and($rareSkill->isEvolved())->toBeTrue();
    });

    it('can get evolution chain', function () {
        $normalSkill = Skill::where('internal_id', 'speed_001')->first();
        $chain = $normalSkill->getEvolutionChain();

        expect($chain)->toHaveCount(2)
            ->and($chain[0]->internal_id)->toBe('speed_001')
            ->and($chain[1]->internal_id)->toBe('speed_001_rare');
    });
});

describe('Skill Categorization', function () {
    it('can filter skills by type', function () {
        $speedSkills = Skill::ofType('speed')->get();
        $passiveSkills = Skill::ofType('passive')->get();

        expect($speedSkills->count())->toBeGreaterThan(0)
            ->and($passiveSkills->count())->toBeGreaterThan(0);
    });

    it('can filter skills by rarity', function () {
        $normalSkills = Skill::ofRarity('normal')->get();
        $rareSkills = Skill::ofRarity('rare')->get();
        $uniqueSkills = Skill::ofRarity('unique')->get();

        expect($normalSkills->count())->toBeGreaterThan(0)
            ->and($rareSkills->count())->toBeGreaterThan(0)
            ->and($uniqueSkills->count())->toBeGreaterThan(0);
    });

    it('can filter skills by meta tier', function () {
        $sTierSkills = Skill::ofMetaTier('S')->get();
        $aTierSkills = Skill::ofMetaTier('A')->get();

        expect($sTierSkills->count())->toBeGreaterThan(0)
            ->and($aTierSkills->count())->toBeGreaterThan(0);
    });
});

describe('Skill SP Costs', function () {
    it('normal skills have SP cost between 120-180', function () {
        $normalSkills = Skill::ofRarity('normal')->get();

        foreach ($normalSkills as $skill) {
            expect($skill->base_sp_cost)->toBeGreaterThanOrEqual(120)
                ->and($skill->base_sp_cost)->toBeLessThanOrEqual(180);
        }
    });

    it('rare skills have SP cost between 180-240', function () {
        $rareSkills = Skill::ofRarity('rare')->get();

        foreach ($rareSkills as $skill) {
            expect($skill->base_sp_cost)->toBeGreaterThanOrEqual(180)
                ->and($skill->base_sp_cost)->toBeLessThanOrEqual(240);
        }
    });
});

describe('Skill Evolution', function () {
    it('evolution relationships are properly set up', function () {
        $normalSkill = Skill::where('internal_id', 'speed_001')->first();
        $rareSkill = Skill::where('internal_id', 'speed_001_rare')->first();

        expect($normalSkill->evolution_target_id)->toBe($rareSkill->id)
            ->and($rareSkill->evolution_source_id)->toBe($normalSkill->id);
    });

    it('can access evolution target relationship', function () {
        $normalSkill = Skill::where('internal_id', 'speed_001')->first();
        $evolutionTarget = $normalSkill->evolutionTarget;

        expect($evolutionTarget)->not->toBeNull()
            ->and($evolutionTarget->internal_id)->toBe('speed_001_rare');
    });

    it('can access evolution source relationship', function () {
        $rareSkill = Skill::where('internal_id', 'speed_001_rare')->first();
        $evolutionSource = $rareSkill->evolutionSource;

        expect($evolutionSource)->not->toBeNull()
            ->and($evolutionSource->internal_id)->toBe('speed_001');
    });
});
