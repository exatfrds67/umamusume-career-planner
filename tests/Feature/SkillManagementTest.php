<?php

use App\Models\Skill;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    // Seed skills before each test
    Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\UcpSkillsSeeder']);
});

describe('Skill Model', function () {
    it('can calculate final cost with hints', function () {
        $skill = Skill::where('internal_id', 'speed_001')->firstOrFail();

        expect($skill->calculateFinalCost(0))->toBe(120)
            ->and($skill->calculateFinalCost(1))->toBe(108)  // 10% discount
            ->and($skill->calculateFinalCost(2))->toBe(96)   // 20% discount
            ->and($skill->calculateFinalCost(3))->toBe(84)   // 30% discount
            ->and($skill->calculateFinalCost(4))->toBe(78)   // 35% discount
            ->and($skill->calculateFinalCost(5))->toBe(72)   // 40% discount (max)
            ->and($skill->calculateFinalCost(6))->toBe(72);  // Still 40% (max)
    });

    it('can get discount percentage', function () {
        $skill = Skill::where('internal_id', 'speed_001')->firstOrFail();

        expect($skill->getDiscountPercentage(0))->toBe(0.0)
            ->and($skill->getDiscountPercentage(1))->toBe(10.0)
            ->and($skill->getDiscountPercentage(2))->toBe(20.0)
            ->and($skill->getDiscountPercentage(3))->toBe(30.0)
            ->and($skill->getDiscountPercentage(4))->toBe(35.0)
            ->and($skill->getDiscountPercentage(5))->toBe(40.0)
            ->and($skill->getDiscountPercentage(6))->toBe(40.0); // Max 40%
    });

    it('can calculate SP saved', function () {
        $skill = Skill::where('internal_id', 'speed_001')->firstOrFail();

        expect($skill->getSpSaved(0))->toBe(0)
            ->and($skill->getSpSaved(1))->toBe(12)  // 10% of 120
            ->and($skill->getSpSaved(2))->toBe(24)  // 20% of 120
            ->and($skill->getSpSaved(3))->toBe(36)  // 30% of 120
            ->and($skill->getSpSaved(4))->toBe(42)  // 35% of 120
            ->and($skill->getSpSaved(5))->toBe(48); // 40% of 120
    });

    it('can check if skill can evolve', function () {
        $normalSkill = Skill::where('internal_id', 'speed_001')->firstOrFail();
        $rareSkill = Skill::where('internal_id', 'speed_001_rare')->firstOrFail();

        expect($normalSkill->canEvolve())->toBeTrue()
            ->and($rareSkill->canEvolve())->toBeFalse();
    });

    it('can check if skill is evolved', function () {
        $normalSkill = Skill::where('internal_id', 'speed_001')->firstOrFail();
        $rareSkill = Skill::where('internal_id', 'speed_001_rare')->firstOrFail();

        expect($normalSkill->isEvolved())->toBeFalse()
            ->and($rareSkill->isEvolved())->toBeTrue();
    });

    it('can get evolution chain', function () {
        $normalSkill = Skill::where('internal_id', 'speed_001')->firstOrFail();
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
    it('normal skills have reasonable SP costs', function () {
        $normalSkills = Skill::ofRarity('normal')->get();

        foreach ($normalSkills as $skill) {
            // Normal skills typically range from 100-180 SP
            expect($skill->base_sp_cost)->toBeGreaterThan(0)
                ->and($skill->base_sp_cost)->toBeLessThanOrEqual(200);
        }
    });

    it('rare skills have higher SP costs than normal skills on average', function () {
        $normalSkills = Skill::ofRarity('normal')->get();
        $rareSkills = Skill::ofRarity('rare')->get();

        $avgNormal = $normalSkills->avg('base_sp_cost');
        $avgRare = $rareSkills->avg('base_sp_cost');

        // Rare skills should cost more on average
        expect($avgRare)->toBeGreaterThan($avgNormal);
    });

    it('rare skills have reasonable SP costs', function () {
        $rareSkills = Skill::ofRarity('rare')->get();

        foreach ($rareSkills as $skill) {
            // Rare skills typically range from 160-320 SP (high-value recovery skills can reach 304 SP)
            expect($skill->base_sp_cost)->toBeGreaterThan(0)
                ->and($skill->base_sp_cost)->toBeLessThanOrEqual(320);
        }
    });
});

describe('Skill Evolution', function () {
    it('evolution relationships are properly set up', function () {
        $normalSkill = Skill::where('internal_id', 'speed_001')->firstOrFail();
        $rareSkill = Skill::where('internal_id', 'speed_001_rare')->firstOrFail();

        expect($normalSkill->evolution_target_id)->toBe($rareSkill->id)
            ->and($rareSkill->evolution_source_id)->toBe($normalSkill->id);
    });

    it('can access evolution target relationship', function () {
        $normalSkill = Skill::where('internal_id', 'speed_001')->firstOrFail();
        $evolutionTarget = $normalSkill->evolutionTarget;

        expect($evolutionTarget)->not->toBeNull()
            ->and($evolutionTarget->internal_id)->toBe('speed_001_rare');
    });

    it('can access evolution source relationship', function () {
        $rareSkill = Skill::where('internal_id', 'speed_001_rare')->firstOrFail();
        $evolutionSource = $rareSkill->evolutionSource;

        expect($evolutionSource)->not->toBeNull()
            ->and($evolutionSource->internal_id)->toBe('speed_001');
    });
});

describe('Character-Exclusive Unique Skills', function () {
    it('has character_exclusive set for unique skills', function () {
        $vodkaSkill = Skill::where('internal_id', 'unique_001')->firstOrFail();

        expect($vodkaSkill)->not->toBeNull()
            ->and($vodkaSkill->character_exclusive)->toBe('Vodka')
            ->and($vodkaSkill->name)->toBe('Cut and Drive!');
    });

    it('has unique_skill_max_level of 4 for character-exclusive skills', function () {
        $uniqueSkills = Skill::whereNotNull('character_exclusive')->get();

        expect($uniqueSkills->count())->toBeGreaterThan(0);

        foreach ($uniqueSkills as $skill) {
            expect($skill->unique_skill_max_level)->toBe(4);
        }
    });

    it('non-unique skills have null character_exclusive', function () {
        $speedSkill = Skill::where('internal_id', 'speed_001')->firstOrFail();

        expect($speedSkill->character_exclusive)->toBeNull()
            ->and($speedSkill->unique_skill_max_level)->toBeNull();
    });

    it('has multiple character-exclusive unique skills', function () {
        $uniqueCharacterSkills = Skill::whereNotNull('character_exclusive')->get();

        expect($uniqueCharacterSkills->count())->toBeGreaterThanOrEqual(67);

        $characters = $uniqueCharacterSkills->pluck('character_exclusive')->unique();
        expect($characters)->toContain('Vodka')
            ->toContain('Oguri Cap')
            ->toContain('Special Week')
            ->toContain('Silence Suzuka')
            ->toContain('Maruzensky')
            ->toContain('Sakura Bakushin O')
            ->toContain('Rice Shower')
            ->toContain('Nice Nature')
            ->toContain('King Halo')
            ->toContain('Haru Urara');
    });

    it('has correct unique skill names from uma.guide', function () {
        $specialWeek = Skill::where('internal_id', 'unique_003')->firstOrFail();
        expect($specialWeek)->not->toBeNull()
            ->and($specialWeek->name)->toBe('Shooting Star')
            ->and($specialWeek->character_exclusive)->toBe('Special Week');

        $daiwaScarlet = Skill::where('internal_id', 'unique_007')->firstOrFail();
        expect($daiwaScarlet)->not->toBeNull()
            ->and($daiwaScarlet->name)->toBe('Resplendent Red Ace')
            ->and($daiwaScarlet->character_exclusive)->toBe('Daiwa Scarlet');

        $bakushinO = Skill::where('internal_id', 'unique_036')->firstOrFail();
        expect($bakushinO)->not->toBeNull()
            ->and($bakushinO->name)->toBe('Genius x Bakushin = Victory')
            ->and($bakushinO->character_exclusive)->toBe('Sakura Bakushin O');

        $niceNature = Skill::where('internal_id', 'unique_045')->firstOrFail();
        expect($niceNature)->not->toBeNull()
            ->and($niceNature->name)->toBe('Just a Little Farther!')
            ->and($niceNature->character_exclusive)->toBe('Nice Nature');

        // Corrected card 01 unique names (verified from uma.guide)
        $silenceSuzuka = Skill::where('internal_id', 'unique_004')->firstOrFail();
        expect($silenceSuzuka)->not->toBeNull()
            ->and($silenceSuzuka->name)->toBe('The View from the Lead Is Mine!')
            ->and($silenceSuzuka->character_exclusive)->toBe('Silence Suzuka');

        $tokaiTeio = Skill::where('internal_id', 'unique_005')->firstOrFail();
        expect($tokaiTeio)->not->toBeNull()
            ->and($tokaiTeio->name)->toBe('Sky-High Teio Step')
            ->and($tokaiTeio->character_exclusive)->toBe('Tokai Teio');

        $naritaBrian = Skill::where('internal_id', 'unique_011')->firstOrFail();
        expect($naritaBrian)->not->toBeNull()
            ->and($naritaBrian->name)->toBe('Shadow Break')
            ->and($naritaBrian->character_exclusive)->toBe('Narita Brian');
    });

    it('has card 02 alternate unique skills', function () {
        // Card 02 alternates — same character, different unique skill
        $specialWeek02 = Skill::where('internal_id', 'unique_049')->firstOrFail();
        expect($specialWeek02)->not->toBeNull()
            ->and($specialWeek02->name)->toBe('Dazzl\'n ♪ Diver')
            ->and($specialWeek02->character_exclusive)->toBe('Special Week');

        $tokaiTeio02 = Skill::where('internal_id', 'unique_050')->firstOrFail();
        expect($tokaiTeio02)->not->toBeNull()
            ->and($tokaiTeio02->name)->toBe('Certain Victory')
            ->and($tokaiTeio02->character_exclusive)->toBe('Tokai Teio');

        $riceShower02 = Skill::where('internal_id', 'unique_062')->firstOrFail();
        expect($riceShower02)->not->toBeNull()
            ->and($riceShower02->name)->toBe('Every Rose Has Its Fangs')
            ->and($riceShower02->character_exclusive)->toBe('Rice Shower');

        // Characters with 2 cards should have 2 unique skills each
        $specialWeekSkills = Skill::where('character_exclusive', 'Special Week')
            ->where('skill_type', 'unique')
            ->whereNotNull('unique_skill_max_level')
            ->get();
        expect($specialWeekSkills->count())->toBe(2);
    });
});

describe('Condition Marker Skills', function () {
    it('has condition_marker for conditional skills', function () {
        $normalConditional = Skill::where('internal_id', 'passive_001')->firstOrFail();
        $goldConditional = Skill::where('internal_id', 'passive_001_rare')->firstOrFail();

        expect($normalConditional->condition_marker)->toBe('○')
            ->and($goldConditional->condition_marker)->toBe('◎');
    });

    it('has null condition_marker for non-conditional skills', function () {
        $normalSkill = Skill::where('internal_id', 'speed_005')->firstOrFail();

        expect($normalSkill->condition_marker)->toBeNull();
    });

    it('gold evolution skills have ◎ marker', function () {
        $goldSkills = Skill::where('condition_marker', '◎')->get();

        expect($goldSkills->count())->toBeGreaterThan(0);

        foreach ($goldSkills as $skill) {
            expect($skill->condition_marker)->toBe('◎');
        }
    });

    it('○ conditional skills can evolve into ◎ skills', function () {
        $rightHandedNormal = Skill::where('internal_id', 'passive_001')->firstOrFail();
        $rightHandedGold = Skill::where('internal_id', 'passive_001_rare')->firstOrFail();

        expect($rightHandedNormal->condition_marker)->toBe('○')
            ->and($rightHandedNormal->can_evolve)->toBeTrue()
            ->and($rightHandedGold->condition_marker)->toBe('◎')
            ->and($rightHandedGold->is_evolution)->toBeTrue();
    });
});

describe('Skill Catalog Completeness', function () {
    it('new generic skills exist with full descriptions', function () {
        $expected = [
            'speed_022' => 'Shifting Gears',
            'speed_023' => 'Lay Low',
            'speed_024' => 'Masterful Gambit',
            'recovery_015' => 'Iron Will',
            'passive_048' => 'Remove Wet Conditions x',
            'unique_068' => 'Xceleration Lvl. 2',
        ];

        foreach ($expected as $internalId => $name) {
            $skill = Skill::where('internal_id', $internalId)->firstOrFail();

            expect($skill->name)->toBe($name)
                ->and($skill->description)->not->toBeNull()
                ->and($skill->description)->not->toBe('')
                ->and($skill->description)->not->toBe('No description available')
                ->and(mb_strlen($skill->description))->toBeGreaterThan(20);
        }
    });

    it('all skills in catalog have a non-empty description', function () {
        $missing = Skill::where(function ($q) {
            $q->whereNull('description')
                ->orWhere('description', '')
                ->orWhere('description', 'No description available');
        })->count();

        expect($missing)->toBe(0);
    });

    it('iron will has high sp cost appropriate for top-tier recovery', function () {
        $skill = Skill::where('internal_id', 'recovery_015')->firstOrFail();

        expect($skill->base_sp_cost)->toBeGreaterThan(200)
            ->and($skill->skill_type)->toBe('recovery')
            ->and($skill->rarity)->toBe('rare');
    });

    it('back-pack speed skills have appropriate sp costs', function () {
        $layLow = Skill::where('internal_id', 'speed_023')->firstOrFail();
        $masterfulGambit = Skill::where('internal_id', 'speed_024')->firstOrFail();

        expect($layLow->base_sp_cost)->toBeGreaterThan(100)
            ->and($masterfulGambit->base_sp_cost)->toBeGreaterThan(100)
            ->and($layLow->skill_type)->toBe('speed')
            ->and($masterfulGambit->skill_type)->toBe('speed');
    });

    it('remove wet conditions is a passive debuff-removal skill', function () {
        $skill = Skill::where('internal_id', 'passive_048')->firstOrFail();

        expect($skill->name)->toBe('Remove Wet Conditions x')
            ->and($skill->skill_type)->toBe('passive')
            ->and($skill->rarity)->toBe('rare')
            ->and($skill->base_sp_cost)->toBeGreaterThan(0);
    });

    it('xceleration lvl 2 is vodkas character-exclusive star upgrade unique', function () {
        $skill = Skill::where('internal_id', 'unique_068')->firstOrFail();

        expect($skill->name)->toBe('Xceleration Lvl. 2')
            ->and($skill->skill_type)->toBe('unique')
            ->and($skill->rarity)->toBe('unique')
            ->and($skill->character_exclusive)->toBe('Vodka')
            ->and($skill->unique_star_upgrade)->toBeTrue();
    });
});
