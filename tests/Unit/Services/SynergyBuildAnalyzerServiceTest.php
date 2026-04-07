<?php

declare(strict_types=1);

use App\Models\Aptitude;
use App\Models\Career;
use App\Models\Character;
use App\Models\CharacterSupportCard;
use App\Models\InheritanceEvent;
use App\Models\ParentCharacter;
use App\Models\Skill;
use App\Models\SkillAcquisition;
use App\Models\SupportCardDefinition;
use App\Services\SkillAnalysisService;
use App\Services\SkillStrategyAnalyzer;
use App\Services\SynergyBuildAnalyzerService;
use App\Services\SynergyScorer;
use App\ValueObjects\SynergyReport;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->service = new SynergyBuildAnalyzerService(
        new SkillAnalysisService,
        new SynergyScorer,
        new SkillStrategyAnalyzer,
    );
});

describe('SynergyBuildAnalyzerService', function () {

    describe('analyzeBuild', function () {
        it('returns a SynergyReport with all 7 layers', function () {
            $character = Character::factory()->uraFinale()->create();

            $report = $this->service->analyzeBuild($character);

            expect($report)->toBeInstanceOf(SynergyReport::class);
            expect($report->layers)->toHaveCount(7);
            expect($report->overallScore)->toBeGreaterThanOrEqual(0)
                ->toBeLessThanOrEqual(100);
            expect($report->tier)->toBeIn(['S+', 'S', 'A', 'B', 'C']);
        });

        it('persists snapshot to the database after analysis', function () {
            $character = Character::factory()->uraFinale()->create();

            $report = $this->service->analyzeBuild($character);

            $character->refresh();
            expect($character->synergy_snapshot)->not->toBeNull();
            expect($character->synergy_computed_at)->not->toBeNull();
            expect((float) $character->synergy_snapshot['overall_score'])->toEqual($report->overallScore);
        });

        it('returns cached report when cache is fresh', function () {
            $character = Character::factory()->uraFinale()->create();

            $report1 = $this->service->analyzeBuild($character);

            $character->refresh();
            $originalComputedAt = $character->synergy_computed_at;

            $report2 = $this->service->analyzeBuild($character);

            expect($report2->overallScore)->toBe($report1->overallScore);
            $character->refresh();
            expect($character->synergy_computed_at->toDateTimeString())
                ->toBe($originalComputedAt->toDateTimeString());
        });

        it('recomputes when cache is stale', function () {
            $character = Character::factory()->uraFinale()->create();

            $this->service->analyzeBuild($character);
            $character->refresh();

            $character->updateQuietly([
                'synergy_computed_at' => Carbon::now()->subMinutes(61),
            ]);

            $character->refresh();
            $oldComputedAt = $character->synergy_computed_at;

            $this->service->analyzeBuild($character);
            $character->refresh();

            expect($character->synergy_computed_at->gt($oldComputedAt))->toBeTrue();
        });

        it('recomputes when forceRefresh is true', function () {
            $character = Character::factory()->uraFinale()->create();

            $this->service->analyzeBuild($character);
            $character->refresh();
            $oldComputedAt = $character->synergy_computed_at;

            Carbon::setTestNow(Carbon::now()->addSeconds(2));
            $this->service->analyzeBuild($character, forceRefresh: true);
            $character->refresh();

            expect($character->synergy_computed_at->gt($oldComputedAt))->toBeTrue();

            Carbon::setTestNow();
        });

        it('recomputes when related build data changes within cache ttl', function () {
            $character = Character::factory()->uraFinale()->create();

            $skill = Skill::factory()->create([
                'skill_type' => 'speed',
                'activation_conditions' => ['phase' => 'early_race'],
            ]);

            $acquisition = SkillAcquisition::factory()->create([
                'character_id' => $character->id,
                'skill_id' => $skill->id,
            ]);

            $this->service->analyzeBuild($character);
            $character->refresh();
            $cachedAt = $character->synergy_computed_at;

            Carbon::setTestNow($cachedAt->copy()->addSeconds(2));

            $acquisition->touch();
            $this->service->analyzeBuild($character);
            $character->refresh();

            expect($character->synergy_computed_at->gt($cachedAt))->toBeTrue();

            Carbon::setTestNow();
        });
    });

    describe('analyzeStatSynergy', function () {
        it('scores high when stats match ideal priorities for chase/medium', function () {
            $character = Character::factory()->uraFinale()->withStats([
                'speed' => 1000,
                'stamina' => 700,
                'power' => 800,
                'guts' => 800,
                'wit' => 500,
            ])->create();

            Aptitude::factory()->for($character)->runningStyle('chase')->state(['grade' => 'S'])->create();
            Aptitude::factory()->for($character)->distance('medium')->state(['grade' => 'A'])->create();

            $report = $this->service->analyzeBuild($character);

            $statLayer = collect($report->layers)->firstWhere('layer', 'stat');
            expect($statLayer)->not->toBeNull();
            expect($statLayer->score)->toBeGreaterThan(50);
        });

        it('scores low when stats are misaligned', function () {
            $character = Character::factory()->uraFinale()->withStats([
                'speed' => 100,
                'stamina' => 100,
                'power' => 100,
                'guts' => 100,
                'wit' => 1200,
            ])->create();

            Aptitude::factory()->for($character)->runningStyle('chase')->state(['grade' => 'S'])->create();
            Aptitude::factory()->for($character)->distance('sprint')->state(['grade' => 'A'])->create();

            $report = $this->service->analyzeBuild($character);

            $statLayer = collect($report->layers)->firstWhere('layer', 'stat');
            expect($statLayer->score)->toBeLessThan(60);
        });

        it('returns zero score when no stats recorded', function () {
            $character = Character::factory()->uraFinale()->create([
                'current_stats' => [],
            ]);

            $report = $this->service->analyzeBuild($character);

            $statLayer = collect($report->layers)->firstWhere('layer', 'stat');
            expect($statLayer->score)->toBe(0.0);
            expect($statLayer->issues)->not->toBeEmpty();
        });
    });

    describe('analyzeSkillPhaseCoverage', function () {
        it('scores high when all 4 phases are covered', function () {
            $character = Character::factory()->uraFinale()->create();

            $phases = ['early_race', 'mid_race', 'final_corner', 'final_straight'];
            foreach ($phases as $phase) {
                $skill = Skill::factory()->create([
                    'activation_conditions' => ['phase' => $phase],
                    'skill_type' => 'speed',
                ]);
                SkillAcquisition::factory()->create([
                    'character_id' => $character->id,
                    'skill_id' => $skill->id,
                    'hints_used' => 5,
                    'total_discount_percentage' => 40.0,
                    'base_sp_cost' => 180,
                    'final_sp_cost' => 108,
                    'sp_saved' => 72,
                ]);
            }

            $report = $this->service->analyzeBuild($character);

            $phaseLayer = collect($report->layers)->firstWhere('layer', 'skill_phase');
            expect($phaseLayer->score)->toBeGreaterThanOrEqual(60);
            expect($phaseLayer->issues)->toBeEmpty();
            expect($phaseLayer->breakdown['display_name'])->toBe('Skill Strategy & Economy');
        });

        it('flags uncovered phases', function () {
            $character = Character::factory()->uraFinale()->create();

            $skill = Skill::factory()->create([
                'activation_conditions' => ['phase' => 'early_race'],
                'skill_type' => 'speed',
            ]);
            SkillAcquisition::factory()->create([
                'character_id' => $character->id,
                'skill_id' => $skill->id,
            ]);

            $report = $this->service->analyzeBuild($character);

            $phaseLayer = collect($report->layers)->firstWhere('layer', 'skill_phase');
            expect($phaseLayer->issues)->not->toBeEmpty();
            expect(implode(' ', $phaseLayer->issues))->toContain('mid race');
        });

        it('returns zero when no skills acquired', function () {
            $character = Character::factory()->uraFinale()->create();

            $report = $this->service->analyzeBuild($character);

            $phaseLayer = collect($report->layers)->firstWhere('layer', 'skill_phase');
            expect($phaseLayer->score)->toBe(0.0);
        });

        it('awards depth bonus for phases with 2+ skills', function () {
            $character = Character::factory()->uraFinale()->create();

            foreach (range(1, 3) as $_) {
                $skill = Skill::factory()->create([
                    'activation_conditions' => ['phase' => 'final_straight'],
                    'skill_type' => 'speed',
                ]);
                SkillAcquisition::factory()->create([
                    'character_id' => $character->id,
                    'skill_id' => $skill->id,
                ]);
            }

            $report = $this->service->analyzeBuild($character);

            $phaseLayer = collect($report->layers)->firstWhere('layer', 'skill_phase');
            expect($phaseLayer->score)->toBeGreaterThan(17);
        });

        it('rewards builds that support a unique skill centerpiece', function () {
            $character = Character::factory()->uraFinale()->create();

            Aptitude::factory()->for($character)->runningStyle('chase')->state(['grade' => 'S'])->create();
            Aptitude::factory()->for($character)->distance('medium')->state(['grade' => 'A'])->create();

            $uniqueSkill = Skill::factory()->create([
                'name' => 'Final Burst',
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

            foreach ([$uniqueSkill, $supportSkill] as $skill) {
                SkillAcquisition::factory()->create([
                    'character_id' => $character->id,
                    'skill_id' => $skill->id,
                    'hints_used' => 4,
                    'total_discount_percentage' => 35.0,
                ]);
            }

            $report = $this->service->analyzeBuild($character);

            $phaseLayer = collect($report->layers)->firstWhere('layer', 'skill_phase');
            expect($phaseLayer->breakdown['unique_skill_name'])->toBe('Final Burst');
            expect($phaseLayer->breakdown['unique_skill_score'])->toBeGreaterThan(10);
        });

        it('scores SP efficiency higher when acquisitions use strong hint discounts', function () {
            $character = Character::factory()->uraFinale()->create();

            foreach (range(1, 3) as $index) {
                $skill = Skill::factory()->create([
                    'name' => 'Efficient Skill '.$index,
                    'activation_conditions' => ['phase' => 'mid_race'],
                ]);

                SkillAcquisition::factory()->create([
                    'character_id' => $character->id,
                    'skill_id' => $skill->id,
                    'hints_used' => 5,
                    'total_discount_percentage' => 40.0,
                    'base_sp_cost' => 200,
                    'final_sp_cost' => 120,
                    'sp_saved' => 80,
                ]);
            }

            $report = $this->service->analyzeBuild($character);

            $phaseLayer = collect($report->layers)->firstWhere('layer', 'skill_phase');
            expect($phaseLayer->breakdown['avg_hint_level'])->toBe(5.0);
            expect($phaseLayer->breakdown['sp_efficiency_score'])->toBeGreaterThan(13);
        });

        it('flags inefficient SP spending on low-hint low-value skills', function () {
            $character = Character::factory()->uraFinale()->create(['available_sp' => 40]);

            $skill = Skill::factory()->create([
                'name' => 'Inefficient Buy',
                'base_sp_cost' => 200,
                'meta_tier' => 'C',
                'skill_type' => 'speed',
                'activation_conditions' => ['phase' => 'early_race'],
            ]);

            SkillAcquisition::factory()->create([
                'character_id' => $character->id,
                'skill_id' => $skill->id,
                'priority_level' => 'low',
                'hints_used' => 0,
                'total_discount_percentage' => 0.0,
                'base_sp_cost' => 200,
                'final_sp_cost' => 200,
                'sp_saved' => 0,
            ]);

            $report = $this->service->analyzeBuild($character);

            $phaseLayer = collect($report->layers)->firstWhere('layer', 'skill_phase');
            expect($phaseLayer->issues)->toContain('SP spending is inefficient for this build');
            expect($phaseLayer->breakdown['sp_waste_total'])->toBeGreaterThan(0);
        });
    });

    describe('analyzeRunningStyleSynergy', function () {
        it('scores high when skill types match running style', function () {
            $character = Character::factory()->uraFinale()->create();

            Aptitude::factory()->for($character)->runningStyle('chase')->state(['grade' => 'S'])->create();

            // chase ideal types: speed, recovery
            foreach (['speed', 'speed', 'recovery'] as $type) {
                $skill = Skill::factory()->create(['skill_type' => $type]);
                SkillAcquisition::factory()->create([
                    'character_id' => $character->id,
                    'skill_id' => $skill->id,
                ]);
            }

            $report = $this->service->analyzeBuild($character);

            $styleLayer = collect($report->layers)->firstWhere('layer', 'running_style');
            expect($styleLayer->score)->toBe(100.0);
        });

        it('scores lower when skills mismatch running style', function () {
            $character = Character::factory()->uraFinale()->create();

            Aptitude::factory()->for($character)->runningStyle('escape')->state(['grade' => 'S'])->create();

            // escape ideal types: speed, passive
            // giving recovery skills (mismatched)
            foreach (['recovery', 'recovery', 'recovery'] as $type) {
                $skill = Skill::factory()->create(['skill_type' => $type]);
                SkillAcquisition::factory()->create([
                    'character_id' => $character->id,
                    'skill_id' => $skill->id,
                ]);
            }

            $report = $this->service->analyzeBuild($character);

            $styleLayer = collect($report->layers)->firstWhere('layer', 'running_style');
            expect($styleLayer->score)->toBe(0.0);
        });

        it('flags activation condition mismatches', function () {
            $character = Character::factory()->uraFinale()->create();

            Aptitude::factory()->for($character)->runningStyle('chase')->state(['grade' => 'S'])->create();

            $skill = Skill::factory()->create([
                'skill_type' => 'speed',
                'activation_conditions' => [
                    'phase' => 'final_straight',
                    'running_style' => 'escape',
                ],
            ]);
            SkillAcquisition::factory()->create([
                'character_id' => $character->id,
                'skill_id' => $skill->id,
            ]);

            $report = $this->service->analyzeBuild($character);

            $styleLayer = collect($report->layers)->firstWhere('layer', 'running_style');
            expect($styleLayer->issues)->not->toBeEmpty();
            expect(implode(' ', $styleLayer->issues))->toContain('escape');
        });
    });

    describe('analyzeInheritanceSynergy', function () {
        it('scores well with high-affinity parents and aligned sparks', function () {
            $character = Character::factory()->uraFinale()->create();

            Aptitude::factory()->for($character)->runningStyle('chase')->state(['grade' => 'S'])->create();
            Aptitude::factory()->for($character)->distance('medium')->state(['grade' => 'A'])->create();

            $career = Career::factory()->create([
                'character_id' => $character->id,
                'user_id' => $character->user_id,
            ]);

            $parent = ParentCharacter::factory()->highAffinity()->create([
                'career_id' => $career->id,
            ]);

            InheritanceEvent::factory()->blueSpark('speed', 3)->applied()->create([
                'career_id' => $career->id,
                'parent_character_id' => $parent->id,
            ]);

            $report = $this->service->analyzeBuild($character);

            $inheritLayer = collect($report->layers)->firstWhere('layer', 'inheritance');
            expect($inheritLayer->score)->toBeGreaterThan(60);
        });

        it('returns baseline when no career exists', function () {
            $character = Character::factory()->uraFinale()->create();

            $report = $this->service->analyzeBuild($character);

            $inheritLayer = collect($report->layers)->firstWhere('layer', 'inheritance');
            expect($inheritLayer->score)->toBe(50.0);
        });

        it('flags low affinity parents', function () {
            $character = Character::factory()->uraFinale()->create();

            Aptitude::factory()->for($character)->runningStyle('lead')->state(['grade' => 'S'])->create();

            $career = Career::factory()->create([
                'character_id' => $character->id,
                'user_id' => $character->user_id,
            ]);

            ParentCharacter::factory()->create([
                'career_id' => $career->id,
                'affinity_grade' => 'low',
            ]);

            InheritanceEvent::factory()->greenSpark('speed', 1)->applied()->create([
                'career_id' => $career->id,
                'parent_character_id' => ParentCharacter::factory()->create([
                    'career_id' => $career->id,
                    'affinity_grade' => 'low',
                ])->id,
            ]);

            $report = $this->service->analyzeBuild($character);

            $inheritLayer = collect($report->layers)->firstWhere('layer', 'inheritance');
            expect($inheritLayer->issues)->toContain('Low parent affinity reduces inheritance effectiveness');
        });
    });

    describe('analyzeTeamSynergy', function () {
        it('returns 100 for non-Unity-Cup scenarios', function () {
            $character = Character::factory()->uraFinale()->create();

            $report = $this->service->analyzeBuild($character);

            $teamLayer = collect($report->layers)->firstWhere('layer', 'team');
            expect($teamLayer->score)->toBe(100.0);
        });

        it('scores high with diverse team in Unity Cup', function () {
            $character = Character::factory()->unityCup()->create([
                'team_composition' => [
                    ['running_style' => 'escape'],
                    ['running_style' => 'lead'],
                    ['running_style' => 'chase'],
                ],
            ]);

            $report = $this->service->analyzeBuild($character);

            $teamLayer = collect($report->layers)->firstWhere('layer', 'team');
            expect($teamLayer->score)->toBeGreaterThanOrEqual(90);
        });

        it('scores low with empty team in Unity Cup', function () {
            $character = Character::factory()->unityCup()->create([
                'team_composition' => [],
            ]);

            $report = $this->service->analyzeBuild($character);

            $teamLayer = collect($report->layers)->firstWhere('layer', 'team');
            expect($teamLayer->score)->toBe(30.0);
            expect($teamLayer->issues)->toContain('No team composition set');
        });

        it('flags same-style teams in Unity Cup', function () {
            $character = Character::factory()->unityCup()->create([
                'team_composition' => [
                    ['running_style' => 'lead'],
                    ['running_style' => 'lead'],
                    ['running_style' => 'lead'],
                ],
            ]);

            $report = $this->service->analyzeBuild($character);

            $teamLayer = collect($report->layers)->firstWhere('layer', 'team');
            expect($teamLayer->issues)->toContain('All team members share the same running style');
        });
    });

    describe('analyzeEnvironmentalSynergy', function () {
        it('awards score for environmental condition skills', function () {
            $character = Character::factory()->uraFinale()->create();

            $skill = Skill::factory()->create([
                'activation_conditions' => ['track' => 'left', 'phase' => 'mid_race'],
                'skill_type' => 'speed',
            ]);
            SkillAcquisition::factory()->create([
                'character_id' => $character->id,
                'skill_id' => $skill->id,
            ]);

            $report = $this->service->analyzeBuild($character);

            $envLayer = collect($report->layers)->firstWhere('layer', 'environmental');
            expect($envLayer->score)->toBeGreaterThan(0);
        });

        it('recommends environmental skills when none exist', function () {
            $character = Character::factory()->uraFinale()->create();

            $report = $this->service->analyzeBuild($character);

            $envLayer = collect($report->layers)->firstWhere('layer', 'environmental');
            expect($envLayer->recommendations)->not->toBeEmpty();
        });

        it('includes support card hint pool consistency in the breakdown', function () {
            $character = Character::factory()->uraFinale()->create();

            $supportCard = SupportCardDefinition::factory()->create([
                'skill_hints_provided' => ['Corner Adept ◯', 'Focus', 'Professor of Curvature'],
            ]);
            CharacterSupportCard::factory()->create([
                'character_id' => $character->id,
                'support_card_id' => $supportCard->id,
            ]);

            $report = $this->service->analyzeBuild($character->fresh()->load('supportCards.supportCard'));

            $envLayer = collect($report->layers)->firstWhere('layer', 'environmental');
            expect($envLayer->breakdown['hint_pool_consistency_score'])->toBeGreaterThanOrEqual(80);
        });
    });

    describe('analyzeDebuffSynergy', function () {
        it('scores 50 with no debuff skills', function () {
            $character = Character::factory()->uraFinale()->create();

            $report = $this->service->analyzeBuild($character);

            $debuffLayer = collect($report->layers)->firstWhere('layer', 'debuff');
            expect($debuffLayer->score)->toBe(50.0);
        });

        it('scores 55 with one debuff skill', function () {
            $character = Character::factory()->uraFinale()->create();

            $skill = Skill::factory()->create(['skill_type' => 'debuff']);
            SkillAcquisition::factory()->create([
                'character_id' => $character->id,
                'skill_id' => $skill->id,
            ]);

            $report = $this->service->analyzeBuild($character);

            $debuffLayer = collect($report->layers)->firstWhere('layer', 'debuff');
            expect($debuffLayer->score)->toBe(55.0);
        });

        it('scores 90 with 3+ debuff skills', function () {
            $character = Character::factory()->uraFinale()->create();

            foreach (range(1, 3) as $_) {
                $skill = Skill::factory()->create(['skill_type' => 'debuff']);
                SkillAcquisition::factory()->create([
                    'character_id' => $character->id,
                    'skill_id' => $skill->id,
                ]);
            }

            $report = $this->service->analyzeBuild($character);

            $debuffLayer = collect($report->layers)->firstWhere('layer', 'debuff');
            expect($debuffLayer->score)->toBe(90.0);
        });
    });

    describe('weight redistribution', function () {
        it('redistributes team weight for non-Unity-Cup scenarios', function () {
            $character = Character::factory()->uraFinale()->create();

            $report = $this->service->analyzeBuild($character);

            $teamLayer = collect($report->layers)->firstWhere('layer', 'team');
            expect($teamLayer->weight)->toBe(0.0);

            $totalWeight = collect($report->layers)->sum('weight');
            expect(round($totalWeight, 2))->toBe(1.0);
        });

        it('keeps team weight for Unity Cup scenarios', function () {
            $character = Character::factory()->unityCup()->create([
                'team_composition' => [
                    ['running_style' => 'escape'],
                    ['running_style' => 'lead'],
                    ['running_style' => 'chase'],
                ],
            ]);

            $report = $this->service->analyzeBuild($character);

            $teamLayer = collect($report->layers)->firstWhere('layer', 'team');
            expect($teamLayer->weight)->toBe(0.10);

            $totalWeight = collect($report->layers)->sum('weight');
            expect(round($totalWeight, 2))->toBe(1.0);
        });
    });

    describe('tier assignment', function () {
        it('assigns correct tiers based on score thresholds', function () {
            expect(SynergyReport::tierFromScore(95))->toBe('S+');
            expect(SynergyReport::tierFromScore(90))->toBe('S+');
            expect(SynergyReport::tierFromScore(85))->toBe('S');
            expect(SynergyReport::tierFromScore(80))->toBe('S');
            expect(SynergyReport::tierFromScore(70))->toBe('A');
            expect(SynergyReport::tierFromScore(65))->toBe('A');
            expect(SynergyReport::tierFromScore(55))->toBe('B');
            expect(SynergyReport::tierFromScore(50))->toBe('B');
            expect(SynergyReport::tierFromScore(40))->toBe('C');
            expect(SynergyReport::tierFromScore(0))->toBe('C');
        });
    });

    describe('critical issues', function () {
        it('collects issues from layers scoring below 30', function () {
            $character = Character::factory()->uraFinale()->create([
                'current_stats' => [],
            ]);

            $report = $this->service->analyzeBuild($character);

            expect($report->criticalIssues)->not->toBeEmpty();
        });
    });
});
