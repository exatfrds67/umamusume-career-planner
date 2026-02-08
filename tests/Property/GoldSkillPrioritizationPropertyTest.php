<?php

declare(strict_types=1);

use App\Models\Character;
use App\Services\GameMechanicsEngine;
use App\Services\RuleBasedAdvisor;

describe('RuleBasedAdvisor::recommendSkillPurchase - Property 6: Gold Skill Prioritization', function () {
    beforeEach(function () {
        $this->mechanicsEngine = new GameMechanicsEngine;
        $this->advisor = new RuleBasedAdvisor($this->mechanicsEngine);
    });

    /**
     * Property 6: Gold Skill Prioritization
     *
     * **Validates: Requirements 3.2**
     *
     * Gold skills with Level 3+ hints must be prioritized in skill recommendations.
     * When multiple skills are available, gold skills with high hints (≥3) should
     * be recommended before normal or rare skills, regardless of other factors.
     */
    it('prioritizes gold skills with level 3+ hints over normal skills with level 5 hints', function () {
        $character = generateCharacterWithSP(300);

        $skills = [
            generateSkill('Normal Skill', 'normal', 80, 5, 'positioning'),
            generateSkill('Gold Recovery', 'gold', 180, 3, 'stamina_recovery'),
            generateSkill('Normal Skill 2', 'normal', 100, 4, 'acceleration'),
        ];

        $recommendation = $this->advisor->recommendSkillPurchase($character, $skills);

        expect($recommendation)->not->toBeNull()
            ->and($recommendation->action)->toContain('Gold Recovery');
    })->group('property');

    it('prioritizes gold skills with level 3 hints over rare skills with level 5 hints', function () {
        $character = generateCharacterWithSP(300);

        $skills = [
            generateSkill('Rare Skill', 'rare', 120, 5, 'positioning'),
            generateSkill('Gold Skill', 'gold', 180, 3, 'stamina_recovery'),
            generateSkill('Rare Skill 2', 'rare', 110, 4, 'acceleration'),
        ];

        $recommendation = $this->advisor->recommendSkillPurchase($character, $skills);

        expect($recommendation)->not->toBeNull()
            ->and($recommendation->action)->toContain('Gold Skill');
    })->group('property');

    it('prioritizes gold skills with level 4 hints', function () {
        $character = generateCharacterWithSP(300);

        $skills = [
            generateSkill('Normal Skill', 'normal', 80, 5, 'positioning'),
            generateSkill('Gold Skill L4', 'gold', 180, 4, 'stamina_recovery'),
            generateSkill('Rare Skill', 'rare', 120, 5, 'acceleration'),
        ];

        $recommendation = $this->advisor->recommendSkillPurchase($character, $skills);

        expect($recommendation)->not->toBeNull()
            ->and($recommendation->action)->toContain('Gold Skill L4');
    })->group('property');

    it('prioritizes gold skills with level 5 hints', function () {
        $character = generateCharacterWithSP(300);

        $skills = [
            generateSkill('Normal Skill', 'normal', 80, 5, 'positioning'),
            generateSkill('Gold Skill L5', 'gold', 180, 5, 'stamina_recovery'),
            generateSkill('Rare Skill', 'rare', 120, 5, 'acceleration'),
        ];

        $recommendation = $this->advisor->recommendSkillPurchase($character, $skills);

        expect($recommendation)->not->toBeNull()
            ->and($recommendation->action)->toContain('Gold Skill L5');
    })->group('property');

    it('does not recommend gold skills with hints below level 3', function () {
        $character = generateCharacterWithSP(300);

        $skills = [
            generateSkill('Gold Skill L1', 'gold', 180, 1, 'stamina_recovery'),
            generateSkill('Gold Skill L2', 'gold', 180, 2, 'positioning'),
            generateSkill('Normal Skill L5', 'normal', 80, 5, 'acceleration'),
        ];

        $recommendation = $this->advisor->recommendSkillPurchase($character, $skills);

        // Should recommend the normal skill with L5 hint instead
        expect($recommendation)->not->toBeNull()
            ->and($recommendation->action)->toContain('Normal Skill L5');
    })->group('property');

    it('prioritizes first gold skill when multiple gold skills with level 3+ hints available', function () {
        $character = generateCharacterWithSP(300);

        $skills = [
            generateSkill('Gold Skill A', 'gold', 180, 3, 'stamina_recovery'),
            generateSkill('Gold Skill B', 'gold', 180, 4, 'positioning'),
            generateSkill('Gold Skill C', 'gold', 180, 5, 'acceleration'),
        ];

        $recommendation = $this->advisor->recommendSkillPurchase($character, $skills);

        expect($recommendation)->not->toBeNull()
            ->and($recommendation->action)->toContain('Gold Skill A');
    })->group('property');

    it('prioritizes gold skills across different skill categories', function () {
        $categories = ['stamina_recovery', 'positioning', 'acceleration', 'speed_boost', 'endurance'];

        foreach ($categories as $category) {
            $character = generateCharacterWithSP(300);

            $skills = [
                generateSkill('Normal Skill', 'normal', 80, 5, 'positioning'),
                generateSkill("Gold {$category}", 'gold', 180, 3, $category),
                generateSkill('Rare Skill', 'rare', 120, 5, 'acceleration'),
            ];

            $recommendation = $this->advisor->recommendSkillPurchase($character, $skills);

            expect($recommendation)->not->toBeNull()
                ->and($recommendation->action)->toContain("Gold {$category}");
        }
    })->group('property');

    it('prioritizes gold skills with varying base costs', function () {
        $baseCosts = [150, 180, 200, 220, 250];

        foreach ($baseCosts as $baseCost) {
            $character = generateCharacterWithSP(300);

            $skills = [
                generateSkill('Normal Skill', 'normal', 80, 5, 'positioning'),
                generateSkill("Gold Skill {$baseCost}", 'gold', $baseCost, 3, 'stamina_recovery'),
                generateSkill('Rare Skill', 'rare', 120, 5, 'acceleration'),
            ];

            $recommendation = $this->advisor->recommendSkillPurchase($character, $skills);

            expect($recommendation)->not->toBeNull()
                ->and($recommendation->action)->toContain("Gold Skill {$baseCost}");
        }
    })->group('property');

    it('returns null when character has insufficient SP for gold skill', function () {
        $character = generateCharacterWithSP(100); // Not enough for gold skill

        $skills = [
            generateSkill('Gold Skill', 'gold', 180, 3, 'stamina_recovery'), // Costs 126 SP after discount
        ];

        $recommendation = $this->advisor->recommendSkillPurchase($character, $skills);

        expect($recommendation)->toBeNull();
    })->group('property');

    it('returns null when no skills have level 3+ hints', function () {
        $character = generateCharacterWithSP(300);

        $skills = [
            generateSkill('Gold Skill L1', 'gold', 180, 1, 'stamina_recovery'),
            generateSkill('Gold Skill L2', 'gold', 180, 2, 'positioning'),
            generateSkill('Normal Skill L2', 'normal', 80, 2, 'acceleration'),
        ];

        $recommendation = $this->advisor->recommendSkillPurchase($character, $skills);

        expect($recommendation)->toBeNull();
    })->group('property');

    it('calculates correct SP cost for gold skills with level 3 hints', function () {
        $character = generateCharacterWithSP(300);

        $skills = [
            generateSkill('Gold Skill', 'gold', 180, 3, 'stamina_recovery'),
        ];

        $recommendation = $this->advisor->recommendSkillPurchase($character, $skills);

        expect($recommendation)->not->toBeNull();

        // Level 3 hint = 30% discount: 180 * 0.7 = 126
        expect($recommendation->expectedOutcomes['sp_cost'])->toBe(126)
            ->and($recommendation->expectedOutcomes['sp_remaining'])->toBe(174);
    })->group('property');

    it('calculates correct SP cost for gold skills with level 4 hints', function () {
        $character = generateCharacterWithSP(300);

        $skills = [
            generateSkill('Gold Skill', 'gold', 180, 4, 'stamina_recovery'),
        ];

        $recommendation = $this->advisor->recommendSkillPurchase($character, $skills);

        expect($recommendation)->not->toBeNull();

        // Level 4 hint = 35% discount: 180 * 0.65 = 117
        expect($recommendation->expectedOutcomes['sp_cost'])->toBe(117)
            ->and($recommendation->expectedOutcomes['sp_remaining'])->toBe(183);
    })->group('property');

    it('calculates correct SP cost for gold skills with level 5 hints', function () {
        $character = generateCharacterWithSP(300);

        $skills = [
            generateSkill('Gold Skill', 'gold', 180, 5, 'stamina_recovery'),
        ];

        $recommendation = $this->advisor->recommendSkillPurchase($character, $skills);

        expect($recommendation)->not->toBeNull();

        // Level 5 hint = 40% discount: 180 * 0.6 = 108
        expect($recommendation->expectedOutcomes['sp_cost'])->toBe(108)
            ->and($recommendation->expectedOutcomes['sp_remaining'])->toBe(192);
    })->group('property');

    it('includes high priority for gold skill recommendations', function () {
        $character = generateCharacterWithSP(300);

        $skills = [
            generateSkill('Gold Skill', 'gold', 180, 3, 'stamina_recovery'),
        ];

        $recommendation = $this->advisor->recommendSkillPurchase($character, $skills);

        expect($recommendation)->not->toBeNull()
            ->and($recommendation->priority)->toBe(\App\Enums\Priority::HIGH);
    })->group('property');

    it('includes correct reasoning for gold skill recommendations', function () {
        $character = generateCharacterWithSP(300);

        $skills = [
            generateSkill('Swinging Maestro', 'gold', 180, 3, 'stamina_recovery'),
        ];

        $recommendation = $this->advisor->recommendSkillPurchase($character, $skills);

        expect($recommendation)->not->toBeNull()
            ->and($recommendation->reasoning)->toContain('Gold')
            ->and($recommendation->reasoning)->toContain('stamina_recovery')
            ->and($recommendation->reasoning)->toContain('Level 3')
            ->and($recommendation->reasoning)->toContain('30%');
    })->group('property');

    it('prioritizes gold skills in mixed skill lists with various hint levels', function () {
        $character = generateCharacterWithSP(300);

        $skills = [
            generateSkill('Normal L5', 'normal', 80, 5, 'positioning'),
            generateSkill('Rare L4', 'rare', 120, 4, 'acceleration'),
            generateSkill('Normal L4', 'normal', 90, 4, 'speed_boost'),
            generateSkill('Gold L3', 'gold', 180, 3, 'stamina_recovery'),
            generateSkill('Rare L5', 'rare', 130, 5, 'endurance'),
            generateSkill('Normal L3', 'normal', 85, 3, 'positioning'),
        ];

        $recommendation = $this->advisor->recommendSkillPurchase($character, $skills);

        expect($recommendation)->not->toBeNull()
            ->and($recommendation->action)->toContain('Gold L3');
    })->group('property');

    it('handles real-world gold skill scenarios', function () {
        $realWorldScenarios = [
            [
                'name' => 'Swinging Maestro',
                'baseCost' => 180,
                'hintLevel' => 3,
                'expectedCost' => 126,
            ],
            [
                'name' => 'In Body and Mind',
                'baseCost' => 180,
                'hintLevel' => 4,
                'expectedCost' => 117,
            ],
            [
                'name' => 'Adrenaline Rush',
                'baseCost' => 180,
                'hintLevel' => 5,
                'expectedCost' => 108,
            ],
            [
                'name' => 'Furious Feat',
                'baseCost' => 200,
                'hintLevel' => 3,
                'expectedCost' => 140,
            ],
        ];

        foreach ($realWorldScenarios as $scenario) {
            $character = generateCharacterWithSP(300);

            $skills = [
                generateSkill('Normal Skill', 'normal', 80, 5, 'positioning'),
                generateSkill($scenario['name'], 'gold', $scenario['baseCost'], $scenario['hintLevel'], 'stamina_recovery'),
            ];

            $recommendation = $this->advisor->recommendSkillPurchase($character, $skills);

            expect($recommendation)->not->toBeNull()
                ->and($recommendation->action)->toContain($scenario['name'])
                ->and($recommendation->expectedOutcomes['sp_cost'])->toBe($scenario['expectedCost']);
        }
    })->group('property');

    it('prioritizes gold skills when character has exact SP needed', function () {
        // Character has exactly 126 SP (cost of gold skill with L3 hint)
        $character = generateCharacterWithSP(126);

        $skills = [
            generateSkill('Gold Skill', 'gold', 180, 3, 'stamina_recovery'),
            generateSkill('Normal Skill', 'normal', 80, 5, 'positioning'),
        ];

        $recommendation = $this->advisor->recommendSkillPurchase($character, $skills);

        expect($recommendation)->not->toBeNull()
            ->and($recommendation->action)->toContain('Gold Skill')
            ->and($recommendation->expectedOutcomes['sp_remaining'])->toBe(0);
    })->group('property');

    it('falls back to rare skills when no gold skills with level 3+ hints available', function () {
        $character = generateCharacterWithSP(300);

        $skills = [
            generateSkill('Gold Skill L2', 'gold', 180, 2, 'stamina_recovery'),
            generateSkill('Rare Skill L3', 'rare', 120, 3, 'positioning'),
            generateSkill('Normal Skill L5', 'normal', 80, 5, 'acceleration'),
        ];

        $recommendation = $this->advisor->recommendSkillPurchase($character, $skills);

        expect($recommendation)->not->toBeNull()
            ->and($recommendation->action)->toContain('Rare Skill L3')
            ->and($recommendation->priority)->toBe(\App\Enums\Priority::MEDIUM);
    })->group('property');

    it('maintains gold skill priority across multiple test runs', function () {
        // Property: Deterministic behavior - same inputs should always produce same outputs
        $character = generateCharacterWithSP(300);

        $skills = [
            generateSkill('Normal Skill', 'normal', 80, 5, 'positioning'),
            generateSkill('Gold Skill', 'gold', 180, 3, 'stamina_recovery'),
            generateSkill('Rare Skill', 'rare', 120, 5, 'acceleration'),
        ];

        $recommendations = [];
        for ($i = 0; $i < 5; $i++) {
            $recommendation = $this->advisor->recommendSkillPurchase($character, $skills);
            $recommendations[] = $recommendation->action;
        }

        // All recommendations should be identical
        expect(array_unique($recommendations))->toHaveCount(1)
            ->and($recommendations[0])->toContain('Gold Skill');
    })->group('property');

    it('prioritizes gold skills regardless of skill order in array', function () {
        $character = generateCharacterWithSP(300);

        $skillOrders = [
            // Gold skill first
            [
                generateSkill('Gold Skill', 'gold', 180, 3, 'stamina_recovery'),
                generateSkill('Normal Skill', 'normal', 80, 5, 'positioning'),
                generateSkill('Rare Skill', 'rare', 120, 5, 'acceleration'),
            ],
            // Gold skill in middle
            [
                generateSkill('Normal Skill', 'normal', 80, 5, 'positioning'),
                generateSkill('Gold Skill', 'gold', 180, 3, 'stamina_recovery'),
                generateSkill('Rare Skill', 'rare', 120, 5, 'acceleration'),
            ],
            // Gold skill last
            [
                generateSkill('Normal Skill', 'normal', 80, 5, 'positioning'),
                generateSkill('Rare Skill', 'rare', 120, 5, 'acceleration'),
                generateSkill('Gold Skill', 'gold', 180, 3, 'stamina_recovery'),
            ],
        ];

        foreach ($skillOrders as $skills) {
            $recommendation = $this->advisor->recommendSkillPurchase($character, $skills);

            expect($recommendation)->not->toBeNull()
                ->and($recommendation->action)->toContain('Gold Skill');
        }
    })->group('property');
});

/**
 * Helper function to generate a character with specific SP
 *
 * @param  int  $availableSP  Available SP amount
 */
function generateCharacterWithSP(int $availableSP): Character
{
    $character = new Character;
    $character->available_sp = $availableSP;

    return $character;
}

/**
 * Helper function to generate a skill array
 *
 * @param  string  $name  Skill name
 * @param  string  $tier  Skill tier (normal/rare/gold)
 * @param  int  $baseCost  Base SP cost
 * @param  int  $hintLevel  Hint level (0-5)
 * @param  string  $category  Skill category
 * @return array{id: int, name: string, tier: string, base_cost: int, hint_level: int, category: string}
 */
function generateSkill(
    string $name,
    string $tier,
    int $baseCost,
    int $hintLevel,
    string $category
): array {
    static $idCounter = 1;

    return [
        'id' => $idCounter++,
        'name' => $name,
        'tier' => $tier,
        'base_cost' => $baseCost,
        'hint_level' => $hintLevel,
        'category' => $category,
    ];
}
