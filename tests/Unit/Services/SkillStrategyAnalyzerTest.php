<?php

declare(strict_types=1);

use App\Models\Aptitude;
use App\Models\Character;
use App\Models\CharacterSupportCard;
use App\Models\Skill;
use App\Models\SkillAcquisition;
use App\Models\SupportCardDefinition;
use App\Services\SkillStrategyAnalyzer;
use Illuminate\Support\Collection;

beforeEach(function () {
    $this->service = new SkillStrategyAnalyzer;
});

describe('SkillStrategyAnalyzer', function () {
    it('detects a unique skill from the equipped skills', function () {
        $uniqueSkill = Skill::factory()->create([
            'character_exclusive' => 'Special Week',
            'skill_type' => 'unique',
        ]);
        $normalSkill = Skill::factory()->create(['skill_type' => 'speed']);

        $result = $this->service->detectUniqueSkill(new Collection([$normalSkill, $uniqueSkill]));

        expect($result?->id)->toBe($uniqueSkill->id);
    });

    it('recognizes when a unique skill is supported by the rest of the build', function () {
        $character = Character::factory()->uraFinale()->create();
        Aptitude::factory()->for($character)->runningStyle('chase')->state(['grade' => 'S'])->create();
        Aptitude::factory()->for($character)->distance('medium')->state(['grade' => 'A'])->create();

        $uniqueSkill = Skill::factory()->create([
            'character_exclusive' => 'Special Week',
            'skill_type' => 'unique',
            'activation_conditions' => [
                'phase' => 'final_straight',
                'running_style' => 'chase',
                'distance' => 'medium',
            ],
        ]);
        $supportSkill = Skill::factory()->create([
            'skill_type' => 'speed',
            'activation_conditions' => ['phase' => 'final_straight'],
        ]);

        $analysis = $this->service->analyzeUniqueSkillCenterpiece($character, $uniqueSkill, new Collection([$uniqueSkill, $supportSkill]));

        expect($analysis['is_supported'])->toBeTrue();
        expect($analysis['score'])->toBeGreaterThan(75);
    });

    it('flags missing setup when a unique skill has unsupported conditions', function () {
        $character = Character::factory()->uraFinale()->create();

        $uniqueSkill = Skill::factory()->create([
            'character_exclusive' => 'Tokai Teio',
            'skill_type' => 'unique',
            'activation_conditions' => [
                'phase' => 'final_corner',
                'running_style' => 'escape',
                'position' => 'top 3',
            ],
        ]);

        $analysis = $this->service->analyzeUniqueSkillCenterpiece($character, $uniqueSkill, new Collection([$uniqueSkill]));

        expect($analysis['is_supported'])->toBeFalse();
        expect($analysis['missing_conditions'])->not->toBeEmpty();
    });

    it('scores SP efficiency higher when acquisitions use strong hint discounts', function () {
        $character = Character::factory()->create();

        $acquisitions = collect(range(1, 3))->map(function () use ($character) {
            $skill = Skill::factory()->create(['base_sp_cost' => 200]);

            return SkillAcquisition::factory()->create([
                'character_id' => $character->id,
                'skill_id' => $skill->id,
                'base_sp_cost' => 200,
                'hints_used' => 5,
                'total_discount_percentage' => 40.0,
                'final_sp_cost' => 120,
                'sp_saved' => 80,
            ]);
        });

        $analysis = $this->service->calculateSPEfficiency($acquisitions);

        expect($analysis['avg_hint_level'])->toBe(5.0);
        expect($analysis['efficiency_score'])->toBeGreaterThan(95);
    });

    it('analyzes support card hint pools for consistency', function () {
        $character = Character::factory()->create();
        $skill = Skill::factory()->create(['name' => 'Corner Adept ◯']);
        SkillAcquisition::factory()->create([
            'character_id' => $character->id,
            'skill_id' => $skill->id,
        ]);

        $supportCard = SupportCardDefinition::factory()->create([
            'skill_hints_provided' => ['Corner Adept ◯', 'Focus', 'Professor of Curvature'],
        ]);
        CharacterSupportCard::factory()->create([
            'character_id' => $character->id,
            'support_card_id' => $supportCard->id,
        ]);

        $analysis = $this->service->analyzeSupportDeckHintPools($character->fresh()->load('supportCards.supportCard', 'skillAcquisitions.skill'));

        expect($analysis['skills_with_hints'])->toBeGreaterThanOrEqual(1);
        expect($analysis['consistency_score'])->toBeGreaterThanOrEqual(80);
    });

    it('flags wasteful low-hint low-value purchases', function () {
        $character = Character::factory()->create(['available_sp' => 50]);
        $skill = Skill::factory()->create([
            'name' => 'Late Purchase Tax',
            'base_sp_cost' => 200,
            'meta_tier' => 'C',
            'skill_type' => 'speed',
        ]);

        $acquisition = SkillAcquisition::factory()->create([
            'character_id' => $character->id,
            'skill_id' => $skill->id,
            'priority_level' => 'low',
            'hints_used' => 0,
            'base_sp_cost' => 200,
            'final_sp_cost' => 200,
            'sp_saved' => 0,
        ]);

        $analysis = $this->service->flagSPWaste(collect([$acquisition->load('skill')]), $character->available_sp);

        expect($analysis['sp_waste_total'])->toBeGreaterThan(0);
        expect($analysis['wasteful_purchases'])->not->toBeEmpty();
    });
});
