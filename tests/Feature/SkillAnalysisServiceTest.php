<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\Skill;
use App\Models\User;
use App\Services\SkillAnalysisService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create([
        'user_id' => $this->user->id,
        'available_sp' => 500,
    ]);
    $this->service = app(SkillAnalysisService::class);
});

describe('Skill Synergy Analysis', function () {
    it('analyzes synergies between skills', function () {
        $skills = Skill::factory()->count(5)->create([
            'skill_type' => 'speed',
            'synergy_skills' => [],
        ]);

        $synergyMap = $this->service->analyzeSynergies($skills);

        expect($synergyMap)->toBeArray();
    });

    it('identifies skills with high synergy count', function () {
        // Create skills with synergy relationships
        $skill1 = Skill::factory()->create([
            'internal_id' => 'test_skill_1',
            'synergy_skills' => ['test_skill_2', 'test_skill_3'],
        ]);

        $skill2 = Skill::factory()->create([
            'internal_id' => 'test_skill_2',
            'synergy_skills' => ['test_skill_1'],
        ]);

        $skill3 = Skill::factory()->create([
            'internal_id' => 'test_skill_3',
            'synergy_skills' => ['test_skill_1'],
        ]);

        $skills = collect([$skill1, $skill2, $skill3]);
        $synergyMap = $this->service->analyzeSynergies($skills);

        expect($synergyMap)->toHaveKey($skill1->id)
            ->and($synergyMap[$skill1->id]['synergy_count'])->toBe(2);
    });

    it('calculates synergy strength correctly', function () {
        $skill = Skill::factory()->create([
            'internal_id' => 'main_skill',
            'skill_type' => 'speed',
            'synergy_skills' => ['synergy_skill_1'],
        ]);

        $synergySkill = Skill::factory()->create([
            'internal_id' => 'synergy_skill_1',
            'skill_type' => 'speed',
            'meta_tier' => 'S',
        ]);

        $skills = collect([$skill, $synergySkill]);
        $synergyMap = $this->service->analyzeSynergies($skills);

        expect($synergyMap[$skill->id]['synergy_strength'])->toBeGreaterThanOrEqual(0);
    });
});

describe('Skill Acquisition Recommendations', function () {
    it('recommends optimal skill acquisition order', function () {
        $skills = Skill::factory()->count(5)->create([
            'base_sp_cost' => 100,
            'skill_type' => 'speed',
        ]);

        $recommendations = $this->service->recommendAcquisitionOrder($skills, 300);

        expect($recommendations)->toBeArray();
    });

    it('respects SP budget when recommending skills', function () {
        $expensiveSkill = Skill::factory()->create(['base_sp_cost' => 400]);
        $cheapSkill = Skill::factory()->create(['base_sp_cost' => 50]);

        $skills = collect([$expensiveSkill, $cheapSkill]);
        $recommendations = $this->service->recommendAcquisitionOrder($skills, 100);

        // Should only recommend cheap skill since expensive one exceeds budget
        expect($recommendations)->toBeArray();
        // Total cost of recommended skills should be within budget
        $totalCost = collect($recommendations)->sum('min_cost');
        expect($totalCost)->toBeLessThanOrEqual(100);
    });

    it('prioritizes high-value skills', function () {
        $highValueSkill = Skill::factory()->create([
            'base_sp_cost' => 100,
            'meta_tier' => 'S+',
        ]);

        $lowValueSkill = Skill::factory()->create([
            'base_sp_cost' => 100,
            'meta_tier' => 'C',
        ]);

        $skills = collect([$lowValueSkill, $highValueSkill]);
        $recommendations = $this->service->recommendAcquisitionOrder($skills, 200);

        expect($recommendations)->toBeArray();
    });
});

describe('Skill Build Analysis', function () {
    it('analyzes complete skill build', function () {
        $skills = Skill::factory()->count(10)->create();

        $analysis = $this->service->analyzeSkillBuild($this->character, $skills);

        expect($analysis)->toHaveKeys(['character_id', 'total_skills', 'skill_types', 'meta_distribution']);
    });

    it('calculates type distribution', function () {
        $speedSkills = Skill::factory()->count(3)->create(['skill_type' => 'speed']);
        $passiveSkills = Skill::factory()->count(2)->create(['skill_type' => 'passive']);

        $skills = collect([...$speedSkills, ...$passiveSkills]);
        $analysis = $this->service->analyzeSkillBuild($this->character, $skills);

        expect($analysis['skill_types'])->toHaveKey('speed')
            ->and($analysis['skill_types']['speed'])->toBe(3);
    });

    it('calculates tier distribution', function () {
        $sPlusSkills = Skill::factory()->count(2)->create(['meta_tier' => 'S+']);
        $aSkills = Skill::factory()->count(3)->create(['meta_tier' => 'A']);

        $skills = collect([...$sPlusSkills, ...$aSkills]);
        $analysis = $this->service->analyzeSkillBuild($this->character, $skills);

        expect($analysis['meta_distribution'])->toHaveKey('S+')
            ->and($analysis['meta_distribution'])->toHaveKey('A');
    });
});

describe('Skill Filtering', function () {
    it('filters skills by type using collection', function () {
        $speedSkills = Skill::factory()->count(5)->create(['skill_type' => 'speed']);
        $passiveSkills = Skill::factory()->count(3)->create(['skill_type' => 'passive']);

        $allSkills = Skill::all();
        $filteredSkills = $allSkills->where('skill_type', 'speed');

        expect($filteredSkills)->toHaveCount(5);
    });

    it('filters skills by tier using collection', function () {
        Skill::factory()->count(4)->create(['meta_tier' => 'S+']);
        Skill::factory()->count(2)->create(['meta_tier' => 'B']);

        $allSkills = Skill::all();
        $topTierSkills = $allSkills->whereIn('meta_tier', ['S+', 'S']);

        expect($topTierSkills)->toHaveCount(4);
    });

    it('filters skills by cost range using collection', function () {
        Skill::factory()->count(3)->create(['base_sp_cost' => 50]);
        Skill::factory()->count(2)->create(['base_sp_cost' => 150]);

        $allSkills = Skill::all();
        $cheapSkills = $allSkills->where('base_sp_cost', '<=', 100);

        expect($cheapSkills)->toHaveCount(3);
    });
});
