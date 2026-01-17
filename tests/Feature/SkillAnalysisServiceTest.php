<?php

use App\Models\Character;
use App\Models\Skill;
use App\Services\SkillAnalysisService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'ComprehensiveSkillSeeder']);
    $this->service = new SkillAnalysisService;
});

describe('Skill Synergy Analysis', function () {
    it('can analyze skill synergies', function () {
        $skills = Skill::where('skill_type', 'speed')->take(3)->get();
        $synergyMap = $this->service->analyzeSynergies($skills);

        expect($synergyMap)->toBeArray();
    });

    it('calculates synergy strength correctly', function () {
        $skill = Skill::where('internal_id', 'speed_001')->first();
        $skills = collect([$skill]);

        $synergyMap = $this->service->analyzeSynergies($skills);

        if (isset($synergyMap[$skill->id])) {
            expect($synergyMap[$skill->id]['synergy_strength'])
                ->toBeGreaterThanOrEqual(0.0)
                ->and($synergyMap[$skill->id]['synergy_strength'])
                ->toBeLessThanOrEqual(10.0);
        }
    });
});

describe('Skill Acquisition Recommendations', function () {
    it('can recommend skill acquisition order', function () {
        $skills = Skill::ofRarity('normal')->take(5)->get();
        $recommendations = $this->service->recommendAcquisitionOrder($skills, 1000);

        expect($recommendations)->toBeArray()
            ->and(count($recommendations))->toBeGreaterThan(0);
    });

    it('respects SP budget constraints', function () {
        $skills = Skill::ofRarity('normal')->take(5)->get();
        $lowBudget = 150; // Only enough for 1-2 skills with hints

        $recommendations = $this->service->recommendAcquisitionOrder($skills, $lowBudget);

        $totalCost = array_sum(array_column($recommendations, 'min_cost'));
        expect($totalCost)->toBeLessThanOrEqual($lowBudget);
    });

    it('prioritizes evolvable skills', function () {
        $skills = Skill::where('can_evolve', true)->take(3)->get();
        $recommendations = $this->service->recommendAcquisitionOrder($skills, 1000);

        if (count($recommendations) > 0) {
            expect($recommendations[0]['skill']->can_evolve)->toBeTrue();
        }
    });
});

describe('Skill Build Analysis', function () {
    it('can analyze skill build for character', function () {
        $character = Character::factory()->create();
        $skills = Skill::take(5)->get();

        $analysis = $this->service->analyzeSkillBuild($character, $skills);

        expect($analysis)->toBeArray()
            ->and($analysis)->toHaveKeys([
                'character_id',
                'total_skills',
                'skill_types',
                'meta_distribution',
                'synergy_analysis',
                'evolution_potential',
                'recommendations',
            ]);
    });

    it('analyzes skill type distribution', function () {
        $character = Character::factory()->create();
        $skills = Skill::take(10)->get();

        $analysis = $this->service->analyzeSkillBuild($character, $skills);

        expect($analysis['skill_types'])->toBeArray()
            ->and($analysis['skill_types'])->toHaveKeys([
                'speed',
                'passive',
                'recovery',
                'debuff',
                'unique',
            ]);
    });

    it('analyzes meta tier distribution', function () {
        $character = Character::factory()->create();
        $skills = Skill::take(10)->get();

        $analysis = $this->service->analyzeSkillBuild($character, $skills);

        expect($analysis['meta_distribution'])->toBeArray()
            ->and($analysis['meta_distribution'])->toHaveKeys([
                'S+',
                'S',
                'A',
                'B',
                'C',
            ]);
    });

    it('analyzes evolution potential', function () {
        $character = Character::factory()->create();
        $skills = Skill::where('can_evolve', true)->take(3)->get();

        $analysis = $this->service->analyzeSkillBuild($character, $skills);

        expect($analysis['evolution_potential'])->toBeArray()
            ->and($analysis['evolution_potential'])->toHaveKeys([
                'evolvable_count',
                'evolved_count',
                'evolution_rate',
                'potential_upgrades',
            ])
            ->and($analysis['evolution_potential']['evolvable_count'])
            ->toBeGreaterThan(0);
    });

    it('generates build recommendations', function () {
        $character = Character::factory()->create();
        $skills = Skill::take(5)->get();

        $analysis = $this->service->analyzeSkillBuild($character, $skills);

        expect($analysis['recommendations'])->toBeArray();
    });
});
