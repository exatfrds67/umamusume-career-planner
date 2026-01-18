<?php

/**
 * @property \App\Models\Character $character
 * @property \App\Models\Skill $skill
 */

use App\Models\Character;
use App\Models\Skill;
use App\Models\SkillHint;
use App\Models\SupportCard;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->character = Character::factory()->create();
    $this->skill = Skill::factory()->create([
        'base_sp_cost' => 120,
        'rarity' => 'normal',
    ]);
});

describe('Skill Hint CRUD Operations', function () {
    it('lists all hints for a character', function () {
        SkillHint::factory()->count(3)->create([
            'character_id' => $this->character->id,
        ]);

        $response = $this->getJson("/api/characters/{$this->character->id}/skill-hints");

        $response->assertSuccessful()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'character_id',
                        'skill_id',
                        'source_type',
                        'source_name',
                        'discount_percentage',
                        'is_used',
                    ],
                ],
                'meta' => [
                    'total',
                    'unused',
                    'used',
                ],
            ])
            ->assertJsonPath('meta.total', 3);
    });

    it('filters hints by skill', function () {
        $skill2 = Skill::factory()->create();

        SkillHint::factory()->create([
            'character_id' => $this->character->id,
            'skill_id' => $this->skill->id,
        ]);

        SkillHint::factory()->create([
            'character_id' => $this->character->id,
            'skill_id' => $skill2->id,
        ]);

        $response = $this->getJson("/api/characters/{$this->character->id}/skill-hints?skill_id={$this->skill->id}");

        $response->assertSuccessful()
            ->assertJsonPath('meta.total', 1);
    });

    it('creates a new skill hint', function () {
        $hintData = [
            'character_id' => $this->character->id,
            'skill_id' => $this->skill->id,
            'source_type' => 'support_card',
            'source_name' => 'Test Support Card',
            'turn_obtained' => 15,
            'career_phase' => 'classic',
            'guaranteed_hint' => true,
            'training_type' => 'speed',
        ];

        $response = $this->postJson("/api/characters/{$this->character->id}/skill-hints", $hintData);

        $response->assertCreated()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'character_id',
                    'skill_id',
                    'source_type',
                    'discount_percentage',
                ],
            ])
            ->assertJsonPath('data.source_type', 'support_card')
            ->assertJsonPath('data.guaranteed_hint', true);

        $this->assertDatabaseHas('ucp_skill_hints', [
            'character_id' => $this->character->id,
            'skill_id' => $this->skill->id,
            'source_type' => 'support_card',
        ]);
    });

    it('validates required fields when creating hint', function () {
        $response = $this->postJson("/api/characters/{$this->character->id}/skill-hints", []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['character_id', 'skill_id', 'source_type', 'source_name']);
    });

    it('shows a specific hint', function () {
        $hint = SkillHint::factory()->create([
            'character_id' => $this->character->id,
            'skill_id' => $this->skill->id,
        ]);

        $response = $this->getJson("/api/characters/{$this->character->id}/skill-hints/{$hint->id}");

        $response->assertSuccessful()
            ->assertJsonPath('data.id', $hint->id)
            ->assertJsonPath('data.character_id', $this->character->id);
    });

    it('deletes a hint', function () {
        $hint = SkillHint::factory()->create([
            'character_id' => $this->character->id,
        ]);

        $response = $this->deleteJson("/api/characters/{$this->character->id}/skill-hints/{$hint->id}");

        $response->assertSuccessful()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('ucp_skill_hints', [
            'id' => $hint->id,
        ]);
    });
});

describe('Cost Breakdown and Statistics', function () {
    it('provides cost breakdown for a skill', function () {
        SkillHint::factory()->count(2)->create([
            'character_id' => $this->character->id,
            'skill_id' => $this->skill->id,
            'is_used' => false,
        ]);

        $response = $this->getJson("/api/characters/{$this->character->id}/skill-hints/skills/{$this->skill->id}/cost-breakdown");

        $response->assertSuccessful()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'skill_id',
                    'skill_name',
                    'base_sp_cost',
                    'hint_count',
                    'discount_percentage',
                    'final_sp_cost',
                    'sp_saved',
                    'max_discount_reached',
                    'hints',
                ],
            ])
            ->assertJsonPath('data.hint_count', 2)
            ->assertJsonPath('data.discount_percentage', 40.0)
            ->assertJsonPath('data.max_discount_reached', true);
    });

    it('provides comprehensive statistics', function () {
        SkillHint::factory()->count(5)->create([
            'character_id' => $this->character->id,
        ]);

        $response = $this->getJson("/api/characters/{$this->character->id}/skill-hints/statistics");

        $response->assertSuccessful()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_hints',
                    'unused_hints',
                    'used_hints',
                    'guaranteed_hints',
                    'source_distribution',
                    'total_sp_saved',
                    'skills_with_max_discount',
                    'average_hints_per_skill',
                ],
            ])
            ->assertJsonPath('data.total_hints', 5);
    });
});

describe('Hint Prediction and Opportunities', function () {
    it('predicts hint opportunities for training', function () {
        // Create a support card definition
        $supportCardDef = SupportCard::factory()->create([
            'card_type' => 'speed',
            'skill_hints_provided' => ['Speed Star', 'Acceleration'],
        ]);

        // Create the character-support card relationship
        $characterSupportCard = \App\Models\CharacterSupportCard::factory()->create([
            'character_id' => $this->character->id,
            'support_card_id' => $supportCardDef->id,
            'friendship_level' => 85,
            'limit_break_level' => 4,
        ]);

        $response = $this->postJson("/api/characters/{$this->character->id}/skill-hints/predict-opportunities", [
            'training_type' => 'speed',
            'support_card_ids' => [$characterSupportCard->id],
        ]);

        $response->assertSuccessful()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'training_type',
                    'opportunities' => [
                        '*' => [
                            'skill_id',
                            'skill_name',
                            'support_card_id',
                            'guaranteed',
                            'probability',
                            'current_hints',
                            'potential_discount',
                            'sp_savings',
                        ],
                    ],
                    'guaranteed_count',
                    'total_opportunities',
                ],
            ]);
    });

    it('validates training type for predictions', function () {
        $response = $this->postJson("/api/characters/{$this->character->id}/skill-hints/predict-opportunities", [
            'training_type' => 'invalid',
            'support_card_ids' => [1],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['training_type']);
    });
});

describe('Collection Strategy', function () {
    it('provides hint collection strategy', function () {
        $skill2 = Skill::factory()->create(['base_sp_cost' => 180]);

        // Create one hint for first skill
        SkillHint::factory()->create([
            'character_id' => $this->character->id,
            'skill_id' => $this->skill->id,
        ]);

        $response = $this->postJson("/api/characters/{$this->character->id}/skill-hints/collection-strategy", [
            'skill_ids' => [$this->skill->id, $skill2->id],
        ]);

        $response->assertSuccessful()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'skill_id',
                        'skill_name',
                        'status',
                        'recommendation',
                        'priority',
                        'current_hints',
                    ],
                ],
            ]);
    });
});

describe('Optimal Sequence Calculation', function () {
    it('calculates optimal hint collection sequence', function () {
        $skill2 = Skill::factory()->create(['base_sp_cost' => 180]);

        // Create one hint for first skill (needs one more for max)
        SkillHint::factory()->create([
            'character_id' => $this->character->id,
            'skill_id' => $this->skill->id,
        ]);

        $response = $this->postJson("/api/characters/{$this->character->id}/skill-hints/optimal-sequence", [
            'skill_ids' => [$this->skill->id, $skill2->id],
            'available_turns' => 10,
        ]);

        $response->assertSuccessful()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'sequence' => [
                        '*' => [
                            'turn',
                            'skill_id',
                            'skill_name',
                            'action',
                            'current_hints',
                            'hints_needed',
                            'priority',
                        ],
                    ],
                    'total_turns_needed',
                    'turns_available',
                    'feasible',
                ],
            ]);
    });

    it('validates available turns', function () {
        $response = $this->postJson("/api/characters/{$this->character->id}/skill-hints/optimal-sequence", [
            'skill_ids' => [$this->skill->id],
            'available_turns' => 0,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['available_turns']);
    });
});

describe('Efficiency Evaluation', function () {
    it('evaluates hint collection efficiency', function () {
        // Create hints for skills
        SkillHint::factory()->count(2)->create([
            'character_id' => $this->character->id,
            'skill_id' => $this->skill->id,
        ]);

        $response = $this->postJson("/api/characters/{$this->character->id}/skill-hints/evaluate-efficiency", [
            'skill_ids' => [$this->skill->id],
        ]);

        $response->assertSuccessful()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_skills',
                    'total_base_cost',
                    'total_final_cost',
                    'total_sp_saved',
                    'efficiency_percentage',
                    'skills_with_max_discount',
                    'skills_with_partial_discount',
                    'skills_with_no_discount',
                    'optimization_grade',
                ],
            ]);
    });
});

describe('Mark Hints as Used', function () {
    it('marks hints as used when skill is acquired', function () {
        SkillHint::factory()->count(2)->create([
            'character_id' => $this->character->id,
            'skill_id' => $this->skill->id,
            'is_used' => false,
        ]);

        $response = $this->postJson(
            "/api/characters/{$this->character->id}/skill-hints/skills/{$this->skill->id}/mark-used",
            ['confirm' => true]
        );

        $response->assertSuccessful()
            ->assertJsonPath('data.hints_used', 2);

        $this->assertDatabaseCount('ucp_skill_hints', 2);
        $this->assertDatabaseMissing('ucp_skill_hints', [
            'character_id' => $this->character->id,
            'skill_id' => $this->skill->id,
            'is_used' => false,
        ]);
    });

    it('requires confirmation to mark hints as used', function () {
        $response = $this->postJson(
            "/api/characters/{$this->character->id}/skill-hints/skills/{$this->skill->id}/mark-used",
            ['confirm' => false]
        );

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['confirm']);
    });
});

/**
 * **Validates: Requirements 26.1, 30.1**
 *
 * Property: API returns consistent discount calculations
 */
it('returns consistent discount calculations via API', function (int $hintCount) {
    // Create hints
    SkillHint::factory()->count($hintCount)->create([
        'character_id' => $this->character->id,
        'skill_id' => $this->skill->id,
        'is_used' => false,
    ]);

    $response = $this->getJson("/api/characters/{$this->character->id}/skill-hints/skills/{$this->skill->id}/cost-breakdown");

    $expectedDiscount = min($hintCount * 20, 40);

    $response->assertSuccessful()
        ->assertJsonPath('data.hint_count', $hintCount)
        ->assertJsonPath('data.discount_percentage', (float) $expectedDiscount);
})->with([0, 1, 2, 3]);

/**
 * **Validates: Requirements 26.2, 30.2**
 *
 * Property: SP savings are correctly calculated and returned
 */
it('calculates and returns correct SP savings via API', function (int $baseCost, int $hintCount) {
    $skill = Skill::factory()->create(['base_sp_cost' => $baseCost]);

    SkillHint::factory()->count($hintCount)->create([
        'character_id' => $this->character->id,
        'skill_id' => $skill->id,
        'is_used' => false,
    ]);

    $response = $this->getJson("/api/characters/{$this->character->id}/skill-hints/skills/{$skill->id}/cost-breakdown");

    $expectedDiscount = min($hintCount * 20, 40);
    $expectedFinalCost = $baseCost - (int) (($baseCost * $expectedDiscount) / 100);
    $expectedSaved = $baseCost - $expectedFinalCost;

    $response->assertSuccessful()
        ->assertJsonPath('data.base_sp_cost', $baseCost)
        ->assertJsonPath('data.final_sp_cost', $expectedFinalCost)
        ->assertJsonPath('data.sp_saved', $expectedSaved);
})->with([
    [120, 1],
    [120, 2],
    [180, 1],
    [180, 2],
    [240, 2],
]);
