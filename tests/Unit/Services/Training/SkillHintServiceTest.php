<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\Skill;
use App\Models\SkillAcquisition;
use App\Models\SupportCard;
use App\Services\Training\SkillHintService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new SkillHintService;
});

describe('SkillHintService', function () {
    it('records hint for skill acquisition', function () {
        $character = Character::factory()->create();
        $skill = Skill::factory()->create(['base_sp_cost' => 100]);
        $card = SupportCard::factory()->create();

        $acquisition = $this->service->recordHint($character, $skill, $card);

        expect($acquisition)->not->toBeNull()
            ->and($acquisition->hint_level)->toBe(1)
            ->and($acquisition->sp_discount_applied)->toBe(10) // VERIFIED: 10% for level 1
            ->and($acquisition->final_sp_cost)->toBe(90); // 100 - 10% = 90
    });

    it('increments hint level on second hint', function () {
        $character = Character::factory()->create();
        $skill = Skill::factory()->create(['base_sp_cost' => 100]);
        $card1 = SupportCard::factory()->create();
        $card2 = SupportCard::factory()->create();

        // First hint
        $this->service->recordHint($character, $skill, $card1);

        // Second hint
        $acquisition = $this->service->recordHint($character, $skill, $card2);

        expect($acquisition->hint_level)->toBe(2)
            ->and($acquisition->sp_discount_applied)->toBe(20) // VERIFIED: 20% for level 2
            ->and($acquisition->final_sp_cost)->toBe(80); // 100 - 20% = 80
    });

    it('continues to level 5 for maximum discount', function () {
        $character = Character::factory()->create();
        $skill = Skill::factory()->create(['base_sp_cost' => 100]);
        $card1 = SupportCard::factory()->create();
        $card2 = SupportCard::factory()->create();
        $card3 = SupportCard::factory()->create();
        $card4 = SupportCard::factory()->create();
        $card5 = SupportCard::factory()->create();

        // First four hints
        $this->service->recordHint($character, $skill, $card1);
        $this->service->recordHint($character, $skill, $card2);
        $this->service->recordHint($character, $skill, $card3);
        $this->service->recordHint($character, $skill, $card4);

        // Fifth hint should reach maximum
        $acquisition = $this->service->recordHint($character, $skill, $card5);

        expect($acquisition->hint_level)->toBe(5) // VERIFIED: Max level is 5
            ->and($acquisition->sp_discount_applied)->toBe(40) // VERIFIED: 40% max
            ->and($acquisition->final_sp_cost)->toBe(60); // 100 - 40% = 60
    });

    it('does not exceed maximum hint level', function () {
        $character = Character::factory()->create();
        $skill = Skill::factory()->create(['base_sp_cost' => 100]);
        $card1 = SupportCard::factory()->create();
        $card2 = SupportCard::factory()->create();
        $card3 = SupportCard::factory()->create();
        $card4 = SupportCard::factory()->create();
        $card5 = SupportCard::factory()->create();
        $card6 = SupportCard::factory()->create();

        // First five hints
        $this->service->recordHint($character, $skill, $card1);
        $this->service->recordHint($character, $skill, $card2);
        $this->service->recordHint($character, $skill, $card3);
        $this->service->recordHint($character, $skill, $card4);
        $this->service->recordHint($character, $skill, $card5);

        // Sixth hint should not increase level
        $acquisition = $this->service->recordHint($character, $skill, $card6);

        expect($acquisition->hint_level)->toBe(5) // Still 5
            ->and($acquisition->sp_discount_applied)->toBe(40) // Still 40%
            ->and($acquisition->final_sp_cost)->toBe(60); // No change
    });

    it('gets hint probability based on bond level', function () {
        $card = SupportCard::factory()->create();
        $skill = Skill::factory()->create();

        $lowBondProbability = $this->service->getHintProbability($card, $skill, 20);
        $highBondProbability = $this->service->getHintProbability($card, $skill, 100);

        expect($lowBondProbability)->toBeGreaterThan(0)
            ->and($highBondProbability)->toBeGreaterThan($lowBondProbability)
            ->and($highBondProbability)->toBeLessThanOrEqual(1.0);
    });

    it('gets skills with hints for character', function () {
        $character = Character::factory()->create();
        $skill1 = Skill::factory()->create(['base_sp_cost' => 100]);
        $skill2 = Skill::factory()->create(['base_sp_cost' => 120]);
        $skill3 = Skill::factory()->create(['base_sp_cost' => 80]);
        $card = SupportCard::factory()->create();

        // Add hints to skill1 and skill2
        $this->service->recordHint($character, $skill1, $card);
        $this->service->recordHint($character, $skill2, $card);

        // skill3 has no hints
        SkillAcquisition::create([
            'character_id' => $character->id,
            'skill_id' => $skill3->id,
            'base_sp_cost' => 80,
            'hint_level' => 0,
            'sp_discount_applied' => 0,
            'final_sp_cost' => 80,
            'is_active' => false,
            'turn_acquired' => 0,
            'career_phase' => 'junior',
            'acquisition_method' => 'purchase',
            'sp_saved' => 0,
        ]);

        $skillsWithHints = $this->service->getSkillsWithHints($character);

        expect($skillsWithHints)->toHaveCount(2);
    });

    it('gets hint summary for character', function () {
        $character = Character::factory()->create();
        $skill1 = Skill::factory()->create(['name_en' => 'Speed Boost', 'base_sp_cost' => 100]);
        $skill2 = Skill::factory()->create(['name_en' => 'Stamina Up', 'base_sp_cost' => 120]);
        $card = SupportCard::factory()->create();

        // Add 1 hint to skill1 (10% discount)
        $this->service->recordHint($character, $skill1, $card);

        // Add 5 hints to skill2 (40% max discount)
        $this->service->recordHint($character, $skill2, $card);
        $this->service->recordHint($character, $skill2, $card);
        $this->service->recordHint($character, $skill2, $card);
        $this->service->recordHint($character, $skill2, $card);
        $this->service->recordHint($character, $skill2, $card);

        $summary = $this->service->getHintSummary($character);

        expect($summary['total_skills_with_hints'])->toBe(2)
            ->and($summary['total_hints'])->toBe(6) // 1 + 5
            ->and($summary['total_sp_saved'])->toBe(58) // 10 (skill1: 10%) + 48 (skill2: 40%)
            ->and($summary['skills_with_max_hints'])->toBe(1); // skill2 has 5 hints (max)
    });
});
