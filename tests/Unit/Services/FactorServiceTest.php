<?php

declare(strict_types=1);

use App\Models\Aptitude;
use App\Models\Character;
use App\Models\Factor;
use App\Services\FactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->factorService = app(FactorService::class);
    $this->character = Character::factory()->create([
        'name' => 'Test Character',
        'current_stats' => [
            'speed' => 100,
            'stamina' => 100,
            'power' => 100,
            'guts' => 100,
            'wit' => 100,
        ],
    ]);
});

describe('Blue Factors (Stat Bonuses)', function () {
    it('calculates stat bonuses correctly for 1-star factors', function () {
        Factor::factory()
            ->blueStat('speed', '1_star')
            ->create(['character_id' => $this->character->id]);

        $bonuses = $this->factorService->calculateStatBonuses($this->character);

        expect($bonuses['speed'])->toBe(5)
            ->and($bonuses['stamina'])->toBe(0)
            ->and($bonuses['power'])->toBe(0)
            ->and($bonuses['guts'])->toBe(0)
            ->and($bonuses['wit'])->toBe(0);
    });

    it('calculates stat bonuses correctly for 2-star factors', function () {
        Factor::factory()
            ->blueStat('stamina', '2_star')
            ->create(['character_id' => $this->character->id]);

        $bonuses = $this->factorService->calculateStatBonuses($this->character);

        expect($bonuses['stamina'])->toBe(12);
    });

    it('calculates stat bonuses correctly for 3-star factors', function () {
        Factor::factory()
            ->blueStat('power', '3_star')
            ->create(['character_id' => $this->character->id]);

        $bonuses = $this->factorService->calculateStatBonuses($this->character);

        expect($bonuses['power'])->toBe(21);
    });

    it('accumulates multiple stat bonuses of the same type', function () {
        Factor::factory()
            ->blueStat('speed', '3_star')
            ->create(['character_id' => $this->character->id]);

        Factor::factory()
            ->blueStat('speed', '2_star')
            ->create(['character_id' => $this->character->id]);

        Factor::factory()
            ->blueStat('speed', '1_star')
            ->create(['character_id' => $this->character->id]);

        $bonuses = $this->factorService->calculateStatBonuses($this->character);

        expect($bonuses['speed'])->toBe(38); // 21 + 12 + 5
    });

    it('calculates bonuses for multiple different stats', function () {
        Factor::factory()
            ->blueStat('speed', '3_star')
            ->create(['character_id' => $this->character->id]);

        Factor::factory()
            ->blueStat('stamina', '2_star')
            ->create(['character_id' => $this->character->id]);

        Factor::factory()
            ->blueStat('power', '1_star')
            ->create(['character_id' => $this->character->id]);

        $bonuses = $this->factorService->calculateStatBonuses($this->character);

        expect($bonuses['speed'])->toBe(21)
            ->and($bonuses['stamina'])->toBe(12)
            ->and($bonuses['power'])->toBe(5)
            ->and($bonuses['guts'])->toBe(0)
            ->and($bonuses['wit'])->toBe(0);
    });

    it('ignores inactive factors', function () {
        Factor::factory()
            ->blueStat('speed', '3_star')
            ->inactive()
            ->create(['character_id' => $this->character->id]);

        $bonuses = $this->factorService->calculateStatBonuses($this->character);

        expect($bonuses['speed'])->toBe(0);
    });

    it('applies stat bonuses to character stats', function () {
        Factor::factory()
            ->blueStat('speed', '3_star')
            ->create(['character_id' => $this->character->id]);

        Factor::factory()
            ->blueStat('stamina', '2_star')
            ->create(['character_id' => $this->character->id]);

        $statsWithBonuses = $this->factorService->applyStatBonuses($this->character);

        expect($statsWithBonuses['speed'])->toBe(121) // 100 + 21
            ->and($statsWithBonuses['stamina'])->toBe(112) // 100 + 12
            ->and($statsWithBonuses['power'])->toBe(100)
            ->and($statsWithBonuses['guts'])->toBe(100)
            ->and($statsWithBonuses['wit'])->toBe(100);
    });
});

describe('Red Factors (Aptitude Upgrades)', function () {
    beforeEach(function () {
        // Create aptitudes for the character
        Aptitude::factory()->create([
            'character_id' => $this->character->id,
            'distance_type' => 'mile',
            'surface_type' => 'turf',
            'running_style' => null,
            'grade' => 'B',
        ]);

        Aptitude::factory()->create([
            'character_id' => $this->character->id,
            'distance_type' => null,
            'surface_type' => 'turf',
            'running_style' => 'runner',
            'grade' => 'C',
        ]);
    });

    it('calculates aptitude improvements', function () {
        Factor::factory()
            ->redAptitude('mile', 1)
            ->create(['character_id' => $this->character->id]);

        $improvements = $this->factorService->calculateAptitudeImprovements($this->character);

        expect($improvements)->toHaveKey('mile')
            ->and($improvements['mile'])->toBe(1);
    });

    it('accumulates multiple aptitude improvements', function () {
        Factor::factory()
            ->redAptitude('mile', 1)
            ->create(['character_id' => $this->character->id]);

        Factor::factory()
            ->redAptitude('mile', 2)
            ->create(['character_id' => $this->character->id]);

        $improvements = $this->factorService->calculateAptitudeImprovements($this->character);

        expect($improvements['mile'])->toBe(3);
    });

    it('ignores inactive red factors', function () {
        Factor::factory()
            ->redAptitude('mile', 1)
            ->inactive()
            ->create(['character_id' => $this->character->id]);

        $improvements = $this->factorService->calculateAptitudeImprovements($this->character);

        expect($improvements)->toBeEmpty();
    });
});

describe('Green Factors (Unique Skills)', function () {
    it('retrieves unique skills', function () {
        Factor::factory()
            ->greenUniqueSkill('Absolute Silence')
            ->create(['character_id' => $this->character->id]);

        Factor::factory()
            ->greenUniqueSkill('Winning Ticket')
            ->create(['character_id' => $this->character->id]);

        $uniqueSkills = $this->factorService->getUniqueSkills($this->character);

        expect($uniqueSkills)->toHaveCount(2)
            ->and($uniqueSkills->first()->unique_skill_name)->toBe('Absolute Silence')
            ->and($uniqueSkills->last()->unique_skill_name)->toBe('Winning Ticket');
    });

    it('ignores inactive unique skills', function () {
        Factor::factory()
            ->greenUniqueSkill('Absolute Silence')
            ->inactive()
            ->create(['character_id' => $this->character->id]);

        $uniqueSkills = $this->factorService->getUniqueSkills($this->character);

        expect($uniqueSkills)->toHaveCount(0);
    });
});

describe('White Factors (Normal Skills)', function () {
    it('retrieves normal skills', function () {
        Factor::factory()
            ->whiteNormalSkill('Acceleration')
            ->create(['character_id' => $this->character->id]);

        Factor::factory()
            ->whiteNormalSkill('Endurance')
            ->create(['character_id' => $this->character->id]);

        $normalSkills = $this->factorService->getNormalSkills($this->character);

        expect($normalSkills)->toHaveCount(2)
            ->and($normalSkills->first()->normal_skill_name)->toBe('Acceleration')
            ->and($normalSkills->last()->normal_skill_name)->toBe('Endurance');
    });

    it('ignores inactive normal skills', function () {
        Factor::factory()
            ->whiteNormalSkill('Acceleration')
            ->inactive()
            ->create(['character_id' => $this->character->id]);

        $normalSkills = $this->factorService->getNormalSkills($this->character);

        expect($normalSkills)->toHaveCount(0);
    });
});

describe('Factor Creation Methods', function () {
    it('creates blue factors correctly', function () {
        $factor = $this->factorService->createBlueFactor(
            $this->character,
            'speed',
            '3_star',
            'main_parent_1',
            'Silence Suzuka'
        );

        expect($factor)->toBeInstanceOf(Factor::class)
            ->and($factor->factor_type)->toBe('blue_stats')
            ->and($factor->stat_type)->toBe('speed')
            ->and($factor->stat_bonus)->toBe(21)
            ->and($factor->star_level)->toBe('3_star')
            ->and($factor->source_parent)->toBe('main_parent_1')
            ->and($factor->source_character_name)->toBe('Silence Suzuka')
            ->and($factor->is_active)->toBeTrue();
    });

    it('creates red factors correctly', function () {
        $factor = $this->factorService->createRedFactor(
            $this->character,
            'mile',
            1,
            '1_star',
            'main_parent_2',
            'Tokai Teio'
        );

        expect($factor)->toBeInstanceOf(Factor::class)
            ->and($factor->factor_type)->toBe('red_aptitudes')
            ->and($factor->aptitude_type)->toBe('mile')
            ->and($factor->grade_improvement)->toBe(1)
            ->and($factor->star_level)->toBe('1_star')
            ->and($factor->source_parent)->toBe('main_parent_2')
            ->and($factor->source_character_name)->toBe('Tokai Teio')
            ->and($factor->is_active)->toBeTrue();
    });

    it('creates green factors correctly', function () {
        $factor = $this->factorService->createGreenFactor(
            $this->character,
            'Absolute Silence',
            ['effect_type' => 'speed_boost', 'effect_value' => 15],
            'main_parent_1',
            'Silence Suzuka'
        );

        expect($factor)->toBeInstanceOf(Factor::class)
            ->and($factor->factor_type)->toBe('green_unique_skills')
            ->and($factor->unique_skill_name)->toBe('Absolute Silence')
            ->and($factor->star_level)->toBe('3_star')
            ->and($factor->skill_effects)->toBeArray()
            ->and($factor->skill_effects['effect_type'])->toBe('speed_boost')
            ->and($factor->is_active)->toBeTrue();
    });

    it('creates white factors correctly', function () {
        $factor = $this->factorService->createWhiteFactor(
            $this->character,
            'Acceleration',
            ['distance_type' => 'mile', 'bonus_value' => 8],
            '2_star',
            'grandparent_1',
            'Daiwa Scarlet'
        );

        expect($factor)->toBeInstanceOf(Factor::class)
            ->and($factor->factor_type)->toBe('white_normal_skills')
            ->and($factor->normal_skill_name)->toBe('Acceleration')
            ->and($factor->star_level)->toBe('2_star')
            ->and($factor->race_bonuses)->toBeArray()
            ->and($factor->race_bonuses['distance_type'])->toBe('mile')
            ->and($factor->is_active)->toBeTrue();
    });
});

describe('Factor Grouping and Counting', function () {
    beforeEach(function () {
        Factor::factory()->blueStat('speed', '3_star')->create(['character_id' => $this->character->id]);
        Factor::factory()->blueStat('stamina', '2_star')->create(['character_id' => $this->character->id]);
        Factor::factory()->redAptitude('mile', 1)->create(['character_id' => $this->character->id]);
        Factor::factory()->greenUniqueSkill('Absolute Silence')->create(['character_id' => $this->character->id]);
        Factor::factory()->whiteNormalSkill('Acceleration')->twoStar()->create(['character_id' => $this->character->id]);
    });

    it('groups factors by type', function () {
        $factorsByType = $this->factorService->getFactorsByType($this->character);

        expect($factorsByType)->toHaveKeys(['blue_stats', 'red_aptitudes', 'green_unique_skills', 'white_normal_skills'])
            ->and($factorsByType['blue_stats'])->toHaveCount(2)
            ->and($factorsByType['red_aptitudes'])->toHaveCount(1)
            ->and($factorsByType['green_unique_skills'])->toHaveCount(1)
            ->and($factorsByType['white_normal_skills'])->toHaveCount(1);
    });

    it('counts factors by star level', function () {
        $counts = $this->factorService->getFactorCountByStarLevel($this->character);

        expect($counts)->toHaveKeys(['1_star', '2_star', '3_star'])
            ->and($counts['1_star'])->toBe(1) // red aptitude
            ->and($counts['2_star'])->toBe(2) // stamina blue + white skill
            ->and($counts['3_star'])->toBe(2); // speed blue + green skill
    });
});
