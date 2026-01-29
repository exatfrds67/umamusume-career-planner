<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\Skill;
use App\Models\SkillHint;
use App\Models\User;
use App\Services\SkillHintService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create(['user_id' => $this->user->id]);
    $this->skill = Skill::factory()->create([
        'base_sp_cost' => 120,
        'skill_type' => 'speed',
    ]);
    $this->service = app(SkillHintService::class);
});

describe('Hint Creation', function () {
    it('creates a skill hint with correct discount', function () {
        $hint = $this->service->createHint(
            character: $this->character,
            skill: $this->skill,
            sourceType: 'support_card',
            sourceName: 'Test Support Card',
            sourceId: 1,
            additionalData: ['turn_obtained' => 5, 'career_phase' => 'junior']
        );

        expect($hint)->toBeInstanceOf(SkillHint::class)
            ->and($hint->character_id)->toBe($this->character->id)
            ->and($hint->skill_id)->toBe($this->skill->id)
            ->and($hint->source_type)->toBe('support_card')
            ->and($hint->discount_percentage)->toBe(10.0) // VERIFIED: 10% for first hint
            ->and($hint->is_used)->toBeFalse();
    });

    it('calculates 10% discount for first hint', function () {
        $hint = $this->service->createHint(
            character: $this->character,
            skill: $this->skill,
            sourceType: 'training',
            sourceName: 'Speed Training'
        );

        expect($hint->discount_percentage)->toBe(10.0); // VERIFIED: 10% for level 1
    });

    it('calculates 20% discount for second hint', function () {
        // Create first hint
        $this->service->createHint(
            character: $this->character,
            skill: $this->skill,
            sourceType: 'training',
            sourceName: 'Speed Training'
        );

        // Create second hint
        $hint = $this->service->createHint(
            character: $this->character,
            skill: $this->skill,
            sourceType: 'support_card',
            sourceName: 'Test Card'
        );

        expect($hint->discount_percentage)->toBe(20.0); // VERIFIED: 20% for level 2
    });

    it('calculates 30% discount for third hint', function () {
        // Create first two hints
        $this->service->createHint($this->character, $this->skill, 'training', 'Training 1');
        $this->service->createHint($this->character, $this->skill, 'training', 'Training 2');

        // Create third hint
        $hint = $this->service->createHint($this->character, $this->skill, 'training', 'Training 3');

        expect($hint->discount_percentage)->toBe(30.0); // VERIFIED: 30% for level 3
    });

    it('calculates 35% discount for fourth hint', function () {
        // Create first three hints
        $this->service->createHint($this->character, $this->skill, 'training', 'Training 1');
        $this->service->createHint($this->character, $this->skill, 'training', 'Training 2');
        $this->service->createHint($this->character, $this->skill, 'training', 'Training 3');

        // Create fourth hint
        $hint = $this->service->createHint($this->character, $this->skill, 'training', 'Training 4');

        expect($hint->discount_percentage)->toBe(35.0); // VERIFIED: 35% for level 4
    });

    it('caps discount at 40% for fifth hint (maximum)', function () {
        // Create first four hints
        $this->service->createHint($this->character, $this->skill, 'training', 'Training 1');
        $this->service->createHint($this->character, $this->skill, 'training', 'Training 2');
        $this->service->createHint($this->character, $this->skill, 'training', 'Training 3');
        $this->service->createHint($this->character, $this->skill, 'training', 'Training 4');

        // Create fifth hint (maximum)
        $hint = $this->service->createHint($this->character, $this->skill, 'training', 'Training 5');

        expect($hint->discount_percentage)->toBe(40.0); // VERIFIED: 40% max at level 5
    });

    it('does not exceed 40% discount for sixth hint', function () {
        // Create five hints
        $this->service->createHint($this->character, $this->skill, 'training', 'Training 1');
        $this->service->createHint($this->character, $this->skill, 'training', 'Training 2');
        $this->service->createHint($this->character, $this->skill, 'training', 'Training 3');
        $this->service->createHint($this->character, $this->skill, 'training', 'Training 4');
        $this->service->createHint($this->character, $this->skill, 'training', 'Training 5');

        // Create sixth hint (should still be 40%)
        $hint = $this->service->createHint($this->character, $this->skill, 'training', 'Training 6');

        expect($hint->discount_percentage)->toBe(40.0); // Still capped at 40%
    });
});

describe('Hint Retrieval', function () {
    it('gets all hints for a skill', function () {
        $this->service->createHint($this->character, $this->skill, 'training', 'Training 1');
        $this->service->createHint($this->character, $this->skill, 'support_card', 'Card 1');

        $hints = $this->service->getHintsForSkill($this->character, $this->skill);

        expect($hints)->toHaveCount(2);
    });

    it('gets only unused hints', function () {
        $hint1 = $this->service->createHint($this->character, $this->skill, 'training', 'Training 1');
        $this->service->createHint($this->character, $this->skill, 'support_card', 'Card 1');

        // Mark first hint as used
        $hint1->update(['is_used' => true]);

        $unusedHints = $this->service->getUnusedHintsForSkill($this->character, $this->skill);

        expect($unusedHints)->toHaveCount(1);
    });
});

describe('Cost Calculations', function () {
    it('calculates final cost with no hints', function () {
        $finalCost = $this->service->calculateFinalCost($this->skill, 0);

        expect($finalCost)->toBe(120); // Original cost
    });

    it('calculates final cost with one hint (10% discount)', function () {
        $this->service->createHint($this->character, $this->skill, 'training', 'Training 1');

        $finalCost = $this->service->calculateFinalCost($this->skill, 1);

        expect($finalCost)->toBe(108); // 120 - 10% = 108
    });

    it('calculates final cost with two hints (20% discount)', function () {
        $this->service->createHint($this->character, $this->skill, 'training', 'Training 1');
        $this->service->createHint($this->character, $this->skill, 'support_card', 'Card 1');

        $finalCost = $this->service->calculateFinalCost($this->skill, 2);

        expect($finalCost)->toBe(96); // 120 - 20% = 96
    });

    it('calculates final cost with three hints (30% discount)', function () {
        $this->service->createHint($this->character, $this->skill, 'training', 'Training 1');
        $this->service->createHint($this->character, $this->skill, 'training', 'Training 2');
        $this->service->createHint($this->character, $this->skill, 'training', 'Training 3');

        $finalCost = $this->service->calculateFinalCost($this->skill, 3);

        expect($finalCost)->toBe(84); // 120 - 30% = 84
    });

    it('calculates final cost with four hints (35% discount)', function () {
        $this->service->createHint($this->character, $this->skill, 'training', 'Training 1');
        $this->service->createHint($this->character, $this->skill, 'training', 'Training 2');
        $this->service->createHint($this->character, $this->skill, 'training', 'Training 3');
        $this->service->createHint($this->character, $this->skill, 'training', 'Training 4');

        $finalCost = $this->service->calculateFinalCost($this->skill, 4);

        expect($finalCost)->toBe(78); // 120 - 35% = 78
    });

    it('calculates final cost with five hints (40% discount maximum)', function () {
        $this->service->createHint($this->character, $this->skill, 'training', 'Training 1');
        $this->service->createHint($this->character, $this->skill, 'training', 'Training 2');
        $this->service->createHint($this->character, $this->skill, 'training', 'Training 3');
        $this->service->createHint($this->character, $this->skill, 'training', 'Training 4');
        $this->service->createHint($this->character, $this->skill, 'training', 'Training 5');

        $finalCost = $this->service->calculateFinalCost($this->skill, 5);

        expect($finalCost)->toBe(72); // 120 - 40% = 72
    });

    it('calculates SP saved with hints', function () {
        $this->service->createHint($this->character, $this->skill, 'training', 'Training 1');

        $spSaved = $this->service->calculateSpSaved($this->skill, 1);

        expect($spSaved)->toBe(12); // 10% of 120
    });
});

describe('Hint Statistics', function () {
    it('returns comprehensive hint statistics', function () {
        $this->service->createHint($this->character, $this->skill, 'training', 'Training 1');
        $this->service->createHint($this->character, $this->skill, 'support_card', 'Card 1');

        $stats = $this->service->getHintStatistics($this->character);

        expect($stats)->toHaveKeys(['total_hints', 'unused_hints', 'total_sp_saved', 'source_distribution']);
    });
});

describe('Hint Sources', function () {
    it('tracks hints from training', function () {
        $hint = $this->service->createHint(
            character: $this->character,
            skill: $this->skill,
            sourceType: 'training',
            sourceName: 'Speed Training',
            additionalData: ['training_type' => 'speed']
        );

        expect($hint->source_type)->toBe('training');
    });

    it('tracks hints from support cards', function () {
        $hint = $this->service->createHint(
            character: $this->character,
            skill: $this->skill,
            sourceType: 'support_card',
            sourceName: 'Super Creek',
            sourceId: 123
        );

        expect($hint->source_type)->toBe('support_card')
            ->and($hint->source_id)->toBe(123);
    });

    it('tracks hints from events', function () {
        $hint = $this->service->createHint(
            character: $this->character,
            skill: $this->skill,
            sourceType: 'event',
            sourceName: 'Random Event'
        );

        expect($hint->source_type)->toBe('event');
    });
});
