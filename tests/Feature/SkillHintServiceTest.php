<?php

/**
 * @property \App\Services\SkillHintService $hintService
 * @property \App\Models\Character $character
 * @property \App\Models\Skill $normalSkill
 * @property \App\Models\Skill $rareSkill
 */

use App\Models\Character;
use App\Models\Skill;
use App\Models\SkillHint;
use App\Services\SkillHintService;
use Illuminate\Foundation\Testing\RefreshDatabase;


beforeEach(function () {
    $this->hintService = app(SkillHintService::class);

    // Create test character
    $this->character = Character::factory()->create();

    // Create test skills
    $this->normalSkill = Skill::factory()->create([
        'name' => 'Test Normal Skill',
        'rarity' => 'normal',
        'base_sp_cost' => 120,
    ]);

    $this->rareSkill = Skill::factory()->create([
        'name' => 'Test Rare Skill',
        'rarity' => 'rare',
        'base_sp_cost' => 180,
    ]);
});

describe('Skill Hint Creation', function () {
    it('creates a skill hint with correct discount percentage', function () {
        $hint = $this->hintService->createHint(
            character: $this->character,
            skill: $this->normalSkill,
            sourceType: 'support_card',
            sourceName: 'Test Support Card'
        );

        expect($hint)->toBeInstanceOf(SkillHint::class)
            ->and($hint->character_id)->toBe($this->character->id)
            ->and($hint->skill_id)->toBe($this->normalSkill->id)
            ->and($hint->source_type)->toBe('support_card')
            ->and($hint->discount_percentage)->toBe(20.0)
            ->and($hint->is_used)->toBeFalse();
    });

    it('calculates 40% discount for second hint', function () {
        // Create first hint
        $this->hintService->createHint(
            character: $this->character,
            skill: $this->normalSkill,
            sourceType: 'support_card',
            sourceName: 'Card 1'
        );

        // Create second hint
        $hint2 = $this->hintService->createHint(
            character: $this->character,
            skill: $this->normalSkill,
            sourceType: 'support_card',
            sourceName: 'Card 2'
        );

        expect($hint2->discount_percentage)->toBe(40.0);
    });

    it('caps discount at 40% for third hint', function () {
        // Create three hints
        $this->hintService->createHint($this->character, $this->normalSkill, 'support_card', 'Card 1');
        $this->hintService->createHint($this->character, $this->normalSkill, 'support_card', 'Card 2');
        $hint3 = $this->hintService->createHint($this->character, $this->normalSkill, 'support_card', 'Card 3');

        expect($hint3->discount_percentage)->toBe(40.0);
    });

    it('stores additional hint data correctly', function () {
        $hint = $this->hintService->createHint(
            character: $this->character,
            skill: $this->normalSkill,
            sourceType: 'training',
            sourceName: 'Speed Training',
            sourceId: 1,
            additionalData: [
                'turn_obtained' => 15,
                'career_phase' => 'classic',
                'guaranteed_hint' => true,
                'training_type' => 'speed',
            ]
        );

        expect($hint->turn_obtained)->toBe(15)
            ->and($hint->career_phase)->toBe('classic')
            ->and($hint->guaranteed_hint)->toBeTrue()
            ->and($hint->training_type)->toBe('speed');
    });
});

describe('Hint Retrieval', function () {
    it('retrieves all hints for a skill', function () {
        $this->hintService->createHint($this->character, $this->normalSkill, 'support_card', 'Card 1');
        $this->hintService->createHint($this->character, $this->normalSkill, 'event', 'Event 1');

        $hints = $this->hintService->getHintsForSkill($this->character, $this->normalSkill);

        expect($hints)->toHaveCount(2);
    });

    it('retrieves only unused hints', function () {
        $hint1 = $this->hintService->createHint($this->character, $this->normalSkill, 'support_card', 'Card 1');
        $hint2 = $this->hintService->createHint($this->character, $this->normalSkill, 'event', 'Event 1');

        $hint1->markAsUsed();

        $unusedHints = $this->hintService->getUnusedHintsForSkill($this->character, $this->normalSkill);

        expect($unusedHints)->toHaveCount(1)
            ->and($unusedHints->first()->id)->toBe($hint2->id);
    });

    it('separates hints by character', function () {
        $character2 = Character::factory()->create();

        $this->hintService->createHint($this->character, $this->normalSkill, 'support_card', 'Card 1');
        $this->hintService->createHint($character2, $this->normalSkill, 'support_card', 'Card 1');

        $hints1 = $this->hintService->getHintsForSkill($this->character, $this->normalSkill);
        $hints2 = $this->hintService->getHintsForSkill($character2, $this->normalSkill);

        expect($hints1)->toHaveCount(1)
            ->and($hints2)->toHaveCount(1);
    });
});

describe('Cost Calculations', function () {
    it('calculates correct discount percentage', function () {
        expect($this->hintService->calculateDiscountPercentage(0))->toBe(0.0)
            ->and($this->hintService->calculateDiscountPercentage(1))->toBe(20.0)
            ->and($this->hintService->calculateDiscountPercentage(2))->toBe(40.0)
            ->and($this->hintService->calculateDiscountPercentage(3))->toBe(40.0);
    });

    it('calculates final cost with hints', function () {
        // 120 SP skill with 1 hint = 96 SP (20% off)
        expect($this->hintService->calculateFinalCost($this->normalSkill, 1))->toBe(96)
            // 120 SP skill with 2 hints = 72 SP (40% off)
            ->and($this->hintService->calculateFinalCost($this->normalSkill, 2))->toBe(72)
            // 180 SP skill with 1 hint = 144 SP (20% off)
            ->and($this->hintService->calculateFinalCost($this->rareSkill, 1))->toBe(144)
            // 180 SP skill with 2 hints = 108 SP (40% off)
            ->and($this->hintService->calculateFinalCost($this->rareSkill, 2))->toBe(108);
    });

    it('calculates SP saved correctly', function () {
        // 120 SP skill with 1 hint saves 24 SP
        expect($this->hintService->calculateSpSaved($this->normalSkill, 1))->toBe(24)
            // 120 SP skill with 2 hints saves 48 SP
            ->and($this->hintService->calculateSpSaved($this->normalSkill, 2))->toBe(48)
            // 180 SP skill with 2 hints saves 72 SP
            ->and($this->hintService->calculateSpSaved($this->rareSkill, 2))->toBe(72);
    });

    it('provides comprehensive cost breakdown', function () {
        $this->hintService->createHint($this->character, $this->normalSkill, 'support_card', 'Card 1');
        $this->hintService->createHint($this->character, $this->normalSkill, 'event', 'Event 1');

        $breakdown = $this->hintService->getCostBreakdown($this->character, $this->normalSkill);

        expect($breakdown)->toHaveKeys([
            'skill_id',
            'skill_name',
            'base_sp_cost',
            'hint_count',
            'discount_percentage',
            'final_sp_cost',
            'sp_saved',
            'max_discount_reached',
            'hints',
        ])
            ->and($breakdown['hint_count'])->toBe(2)
            ->and($breakdown['discount_percentage'])->toBe(40.0)
            ->and($breakdown['final_sp_cost'])->toBe(72)
            ->and($breakdown['sp_saved'])->toBe(48)
            ->and($breakdown['max_discount_reached'])->toBeTrue();
    });
});

describe('Hint Usage', function () {
    it('marks hints as used', function () {
        $this->hintService->createHint($this->character, $this->normalSkill, 'support_card', 'Card 1');
        $this->hintService->createHint($this->character, $this->normalSkill, 'event', 'Event 1');

        $hintsUsed = $this->hintService->markHintsAsUsed($this->character, $this->normalSkill);

        expect($hintsUsed)->toBe(2);

        $unusedHints = $this->hintService->getUnusedHintsForSkill($this->character, $this->normalSkill);
        expect($unusedHints)->toHaveCount(0);
    });

    it('only marks unused hints', function () {
        $hint1 = $this->hintService->createHint($this->character, $this->normalSkill, 'support_card', 'Card 1');
        $this->hintService->createHint($this->character, $this->normalSkill, 'event', 'Event 1');

        $hint1->markAsUsed();

        $hintsUsed = $this->hintService->markHintsAsUsed($this->character, $this->normalSkill);

        expect($hintsUsed)->toBe(1);
    });
});

describe('Hint Collection Strategy', function () {
    it('recommends collecting hints for skills with no hints', function () {
        $skills = collect([$this->normalSkill, $this->rareSkill]);

        $strategies = $this->hintService->getHintCollectionStrategy($this->character, $skills);

        expect($strategies)->toHaveCount(2)
            ->and($strategies[0]['status'])->toBe('collect_hints')
            ->and($strategies[0]['priority'])->toBe('low');
    });

    it('prioritizes skills with one hint', function () {
        $this->hintService->createHint($this->character, $this->normalSkill, 'support_card', 'Card 1');

        $skills = collect([$this->normalSkill, $this->rareSkill]);
        $strategies = $this->hintService->getHintCollectionStrategy($this->character, $skills);

        $normalSkillStrategy = collect($strategies)->firstWhere('skill_id', $this->normalSkill->id);

        expect($normalSkillStrategy['status'])->toBe('one_more_hint')
            ->and($normalSkillStrategy['priority'])->toBe('medium');
    });

    it('marks skills with max discount as ready', function () {
        $this->hintService->createHint($this->character, $this->normalSkill, 'support_card', 'Card 1');
        $this->hintService->createHint($this->character, $this->normalSkill, 'event', 'Event 1');

        $skills = collect([$this->normalSkill]);
        $strategies = $this->hintService->getHintCollectionStrategy($this->character, $skills);

        expect($strategies[0]['status'])->toBe('max_discount')
            ->and($strategies[0]['priority'])->toBe('high');
    });
});

describe('Hint Statistics', function () {
    it('calculates comprehensive statistics', function () {
        // Create hints for different skills
        $this->hintService->createHint($this->character, $this->normalSkill, 'support_card', 'Card 1');
        $this->hintService->createHint($this->character, $this->normalSkill, 'event', 'Event 1');
        $hint3 = $this->hintService->createHint($this->character, $this->rareSkill, 'support_card', 'Card 2');

        $hint3->markAsUsed();

        $stats = $this->hintService->getHintStatistics($this->character);

        expect($stats)->toHaveKeys([
            'total_hints',
            'unused_hints',
            'used_hints',
            'guaranteed_hints',
            'source_distribution',
            'total_sp_saved',
            'skills_with_max_discount',
            'average_hints_per_skill',
        ])
            ->and($stats['total_hints'])->toBe(3)
            ->and($stats['unused_hints'])->toBe(2)
            ->and($stats['used_hints'])->toBe(1)
            ->and($stats['skills_with_max_discount'])->toBe(1);
    });

    it('tracks source distribution', function () {
        $this->hintService->createHint($this->character, $this->normalSkill, 'support_card', 'Card 1');
        $this->hintService->createHint($this->character, $this->normalSkill, 'support_card', 'Card 2');
        $this->hintService->createHint($this->character, $this->rareSkill, 'event', 'Event 1');

        $stats = $this->hintService->getHintStatistics($this->character);

        expect($stats['source_distribution'])->toHaveKey('support_card')
            ->and($stats['source_distribution']['support_card'])->toBe(2)
            ->and($stats['source_distribution']['event'])->toBe(1);
    });
});

/**
 * **Validates: Requirements 26.1, 26.2**
 *
 * Property: Hint discount calculation is consistent and capped at 40%
 */
it('maintains consistent discount calculation across all hint counts', function (int $hintCount) {
    $discount = $this->hintService->calculateDiscountPercentage($hintCount);

    // Discount should be 20% per hint, capped at 40%
    $expectedDiscount = min($hintCount * 20, 40);

    expect($discount)->toBe((float) $expectedDiscount)
        ->and($discount)->toBeLessThanOrEqual(40.0)
        ->and($discount)->toBeGreaterThanOrEqual(0.0);
})->with([0, 1, 2, 3, 4, 5, 10, 100]);

/**
 * **Validates: Requirements 26.1, 30.1**
 *
 * Property: Final cost is always less than or equal to base cost
 */
it('ensures final cost never exceeds base cost', function (int $baseCost, int $hintCount) {
    $skill = Skill::factory()->create(['base_sp_cost' => $baseCost]);

    $finalCost = $this->hintService->calculateFinalCost($skill, $hintCount);

    expect($finalCost)->toBeLessThanOrEqual($baseCost)
        ->and($finalCost)->toBeGreaterThan(0);
})->with([
    [120, 0],
    [120, 1],
    [120, 2],
    [180, 0],
    [180, 1],
    [180, 2],
    [240, 0],
    [240, 1],
    [240, 2],
]);

/**
 * **Validates: Requirements 26.2, 30.2**
 *
 * Property: SP saved equals base cost minus final cost
 */
it('calculates SP savings correctly for all scenarios', function (int $baseCost, int $hintCount) {
    $skill = Skill::factory()->create(['base_sp_cost' => $baseCost]);

    $finalCost = $this->hintService->calculateFinalCost($skill, $hintCount);
    $spSaved = $this->hintService->calculateSpSaved($skill, $hintCount);

    expect($spSaved)->toBe($baseCost - $finalCost)
        ->and($spSaved)->toBeGreaterThanOrEqual(0);
})->with([
    [120, 0],
    [120, 1],
    [120, 2],
    [150, 1],
    [180, 2],
    [200, 1],
    [240, 2],
]);
