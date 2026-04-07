<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AffinityGrade;
use App\Enums\SparkType;
use App\Models\Aptitude;
use App\Models\Character;
use App\Models\InheritanceEvent;
use App\Models\ParentCharacter;
use App\Models\Skill;
use App\ValueObjects\SynergyLayerScore;
use App\ValueObjects\SynergyReport;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Multi-layer Synergy Build Analyzer
 *
 * Evaluates how well a character's stats, skills, running style, inheritance,
 * team composition, environmental conditions, and debuff capabilities reinforce
 * each other. Produces a SynergyReport with an overall 0–100 score across 7 layers.
 *
 * Layer weights:
 *   Stat 20%, Skill Phase 25%, Running Style 20%, Inheritance 15%,
 *   Team 10% (Unity Cup only), Environmental 5%, Debuff 5%.
 *
 * When scenario is not Unity Cup, Team weight is redistributed proportionally.
 */
class SynergyBuildAnalyzerService
{
    /**
     * Cache TTL in minutes.
     */
    private const CACHE_TTL_MINUTES = 60;

    /**
     * Base layer weights (before Unity Cup redistribution).
     *
     * @var array<string, float>
     */
    private const LAYER_WEIGHTS = [
        'stat' => 0.20,
        'skill_phase' => 0.25,
        'running_style' => 0.20,
        'inheritance' => 0.15,
        'team' => 0.10,
        'environmental' => 0.05,
        'debuff' => 0.05,
    ];

    /**
     * Ideal stat priority vectors per running style × distance.
     * Values represent relative importance (higher = more important).
     *
     * @var array<string, array<string, array<string, float>>>
     */
    private const STAT_PRIORITY_MAP = [
        'escape' => [
            'sprint' => ['speed' => 1.0, 'stamina' => 0.3, 'power' => 0.8, 'guts' => 0.4, 'wit' => 0.5],
            'mile' => ['speed' => 1.0, 'stamina' => 0.4, 'power' => 0.7, 'guts' => 0.4, 'wit' => 0.5],
            'medium' => ['speed' => 0.9, 'stamina' => 0.6, 'power' => 0.7, 'guts' => 0.5, 'wit' => 0.5],
            'long' => ['speed' => 0.8, 'stamina' => 0.9, 'power' => 0.6, 'guts' => 0.6, 'wit' => 0.5],
        ],
        'lead' => [
            'sprint' => ['speed' => 0.9, 'stamina' => 0.3, 'power' => 0.9, 'guts' => 0.5, 'wit' => 0.5],
            'mile' => ['speed' => 0.9, 'stamina' => 0.5, 'power' => 0.8, 'guts' => 0.5, 'wit' => 0.5],
            'medium' => ['speed' => 0.8, 'stamina' => 0.7, 'power' => 0.8, 'guts' => 0.5, 'wit' => 0.5],
            'long' => ['speed' => 0.7, 'stamina' => 0.9, 'power' => 0.7, 'guts' => 0.6, 'wit' => 0.5],
        ],
        'pace' => [
            'sprint' => ['speed' => 0.9, 'stamina' => 0.3, 'power' => 0.9, 'guts' => 0.6, 'wit' => 0.6],
            'mile' => ['speed' => 0.9, 'stamina' => 0.5, 'power' => 0.8, 'guts' => 0.6, 'wit' => 0.6],
            'medium' => ['speed' => 0.8, 'stamina' => 0.7, 'power' => 0.8, 'guts' => 0.6, 'wit' => 0.6],
            'long' => ['speed' => 0.7, 'stamina' => 0.9, 'power' => 0.7, 'guts' => 0.7, 'wit' => 0.5],
        ],
        'chase' => [
            'sprint' => ['speed' => 1.0, 'stamina' => 0.3, 'power' => 0.9, 'guts' => 0.8, 'wit' => 0.5],
            'mile' => ['speed' => 1.0, 'stamina' => 0.5, 'power' => 0.8, 'guts' => 0.8, 'wit' => 0.5],
            'medium' => ['speed' => 0.9, 'stamina' => 0.7, 'power' => 0.8, 'guts' => 0.8, 'wit' => 0.5],
            'long' => ['speed' => 0.8, 'stamina' => 0.9, 'power' => 0.7, 'guts' => 0.9, 'wit' => 0.5],
        ],
    ];

    /**
     * Maps game activation_conditions phase values to our canonical 4-phase model.
     *
     * @var array<string, string>
     */
    private const PHASE_MAPPING = [
        'early_race' => 'opening',
        'mid_race' => 'mid_race',
        'corner' => 'final_corner',
        'final_corner' => 'final_corner',
        'straight' => 'final_straight',
        'final_straight' => 'final_straight',
        'final_leg' => 'final_straight',
        'progressive' => 'mid_race',
    ];

    /**
     * Ideal skill types per running style.
     *
     * @var array<string, array<int, string>>
     */
    private const STYLE_SKILL_MAP = [
        'escape' => ['speed', 'passive'],
        'lead' => ['speed', 'passive'],
        'pace' => ['speed', 'recovery'],
        'chase' => ['speed', 'recovery'],
    ];

    /**
     * Aptitude grade ranking (highest to lowest).
     *
     * @var array<string, int>
     */
    private const GRADE_RANK = [
        'SS' => 10, 'S' => 9, 'A' => 8, 'B' => 7, 'C' => 6,
        'D' => 5, 'E' => 4, 'F' => 3, 'G' => 2,
    ];

    public function __construct(
        private readonly SkillAnalysisService $skillAnalysisService,
        private readonly SynergyScorer $synergyScorer,
        private readonly SkillStrategyAnalyzer $skillStrategyAnalyzer,
    ) {}

    /**
     * Analyze a character's build synergy across all 7 layers.
     *
     * Returns a cached report if available and fresh (< 1 hour old).
     * Otherwise computes, caches, and returns a new report.
     */
    public function analyzeBuild(Character $character, bool $forceRefresh = false): SynergyReport
    {
        if (! $forceRefresh && $this->hasFreshCache($character)) {
            return SynergyReport::fromArray($character->synergy_snapshot);
        }

        $character->loadMissing(['aptitudes', 'skillAcquisitions.skill', 'careers.parentCharacters.inheritanceEvents', 'supportCards.supportCard']);

        $runningStyle = $this->resolveRunningStyle($character);
        $distance = $this->resolveDistance($character);
        $isUnityCup = $character->scenario_type === 'unity_cup';

        $weights = $this->resolveWeights($isUnityCup);

        $layers = [
            $this->analyzeStatSynergy($character, $runningStyle, $distance, $weights['stat']),
            $this->analyzeSkillPhaseCoverage($character, $weights['skill_phase']),
            $this->analyzeRunningStyleSynergy($character, $runningStyle, $weights['running_style']),
            $this->analyzeInheritanceSynergy($character, $runningStyle, $weights['inheritance']),
            $this->analyzeTeamSynergy($character, $isUnityCup, $weights['team']),
            $this->analyzeEnvironmentalSynergy($character, $weights['environmental']),
            $this->analyzeDebuffSynergy($character, $weights['debuff']),
        ];

        $criticalIssues = $this->collectCriticalIssues($layers);
        $report = SynergyReport::fromLayers($layers, $criticalIssues);

        $character->update([
            'synergy_snapshot' => $report->toArray(),
            'synergy_computed_at' => Carbon::now(),
        ]);

        return $report;
    }

    /**
     * Check if the cached synergy snapshot is still fresh.
     */
    private function hasFreshCache(Character $character): bool
    {
        if ($character->synergy_snapshot === null || $character->synergy_computed_at === null) {
            return false;
        }

        if ($character->synergy_computed_at->diffInMinutes(Carbon::now()) >= self::CACHE_TTL_MINUTES) {
            return false;
        }

        return ! $this->hasUnderlyingDataChangedSinceSnapshot($character, $character->synergy_computed_at);
    }

    private function hasUnderlyingDataChangedSinceSnapshot(Character $character, Carbon $computedAt): bool
    {
        if ($character->updated_at !== null && $character->updated_at->gt($computedAt)) {
            return true;
        }

        if ($character->aptitudes()->where('updated_at', '>', $computedAt)->exists()) {
            return true;
        }

        if ($character->skillAcquisitions()->where('updated_at', '>', $computedAt)->exists()) {
            return true;
        }

        if ($character->skillAcquisitions()->whereHas('skill', function ($query) use ($computedAt): void {
            $query->where('updated_at', '>', $computedAt);
        })->exists()) {
            return true;
        }

        if ($character->supportCards()->where('updated_at', '>', $computedAt)->exists()) {
            return true;
        }

        if ($character->supportCards()->whereHas('supportCard', function ($query) use ($computedAt): void {
            $query->where('updated_at', '>', $computedAt);
        })->exists()) {
            return true;
        }

        if ($character->careers()->where('updated_at', '>', $computedAt)->exists()) {
            return true;
        }

        if ($character->careers()->whereHas('parentCharacters', function ($query) use ($computedAt): void {
            $query->where('updated_at', '>', $computedAt);
        })->exists()) {
            return true;
        }

        return $character->careers()->whereHas('parentCharacters.inheritanceEvents', function ($query) use ($computedAt): void {
            $query->where('updated_at', '>', $computedAt);
        })->exists();
    }

    /**
     * Resolve effective layer weights. For non-Unity-Cup scenarios,
     * the team weight is redistributed proportionally.
     *
     * @return array<string, float>
     */
    private function resolveWeights(bool $isUnityCup): array
    {
        $weights = self::LAYER_WEIGHTS;

        if (! $isUnityCup) {
            $teamWeight = $weights['team'];
            $weights['team'] = 0.0;
            $remaining = array_filter($weights, fn (float $w) => $w > 0);
            $remainingSum = array_sum($remaining);

            foreach ($weights as $key => $w) {
                if ($w > 0 && $key !== 'team') {
                    $weights[$key] = $w + ($teamWeight * ($w / $remainingSum));
                }
            }
        }

        return $weights;
    }

    /**
     * Layer 1: Stat Synergy
     *
     * Compares actual stat ratios against ideal priorities for the character's
     * running style and preferred distance.
     */
    private function analyzeStatSynergy(Character $character, string $runningStyle, string $distance, float $weight): SynergyLayerScore
    {
        $stats = $character->current_stats ?? [];
        $issues = [];
        $recommendations = [];

        if (empty($stats)) {
            return new SynergyLayerScore('stat', 0.0, $weight, ['No stats recorded yet'], ['Start training to build stats']);
        }

        $idealPriorities = self::STAT_PRIORITY_MAP[$runningStyle][$distance]
            ?? self::STAT_PRIORITY_MAP['lead']['medium'];

        $idealSum = array_sum($idealPriorities);
        $idealRatios = array_map(fn (float $v) => $v / $idealSum, $idealPriorities);

        $statValues = [
            'speed' => $stats['speed'] ?? 0,
            'stamina' => $stats['stamina'] ?? 0,
            'power' => $stats['power'] ?? 0,
            'guts' => $stats['guts'] ?? 0,
            'wit' => $stats['wisdom'] ?? $stats['wit'] ?? 0,
        ];

        $totalStats = array_sum($statValues);
        if ($totalStats === 0) {
            return new SynergyLayerScore('stat', 0.0, $weight, ['All stats are zero'], ['Begin training immediately']);
        }

        $actualRatios = array_map(fn (int $v) => $v / $totalStats, $statValues);

        $distance2 = 0.0;
        foreach ($idealRatios as $stat => $idealRatio) {
            $diff = ($actualRatios[$stat] ?? 0) - $idealRatio;
            $distance2 += $diff * $diff;
        }

        $maxDistance = 0.8;
        $score = max(0, 100 * (1 - (sqrt($distance2) / $maxDistance)));

        $sortedIdeal = $idealPriorities;
        arsort($sortedIdeal);
        $topStats = array_slice(array_keys($sortedIdeal), 0, 2);

        foreach ($topStats as $statName) {
            $value = $statValues[$statName] ?? 0;
            if ($value < 300 && $totalStats > 500) {
                $issues[] = ucfirst($statName).' is critically underinvested for this build';
                $recommendations[] = 'Prioritize '.ucfirst($statName).' training';
            }
        }

        $lowestPriorityStat = array_key_last($sortedIdeal);
        if ($lowestPriorityStat !== null && ($statValues[$lowestPriorityStat] ?? 0) > ($totalStats * 0.3)) {
            $issues[] = ucfirst($lowestPriorityStat).' is over-invested relative to build priority';
            $recommendations[] = 'Reduce '.ucfirst($lowestPriorityStat).' training and reallocate to key stats';
        }

        return new SynergyLayerScore('stat', round($score, 1), $weight, $issues, $recommendations);
    }

    /**
     * Layer 2: Skill Strategy & Economy
     *
     * Checks whether acquired skills cover all 4 race phases, support the build's
     * unique skill, and use SP efficiently through hints and cost reductions.
     */
    private function analyzeSkillPhaseCoverage(Character $character, float $weight): SynergyLayerScore
    {
        $acquisitions = $character->skillAcquisitions;
        $issues = [];
        $recommendations = [];

        if ($acquisitions->isEmpty()) {
            return new SynergyLayerScore(
                'skill_phase',
                0.0,
                $weight,
                ['No skills acquired yet'],
                ['Acquire skills covering all race phases'],
                [
                    'display_name' => 'Skill Strategy & Economy',
                    'phase_coverage_score' => 0.0,
                    'phase_depth_score' => 0.0,
                    'intra_skill_synergy_score' => 0.0,
                    'unique_skill_score' => 0.0,
                    'sp_efficiency_score' => 0.0,
                ],
            );
        }

        /** @var Collection<int, Skill> $skills */
        $skills = $acquisitions->map(fn ($a) => $a->skill)->filter();

        $phaseCoverage = ['opening' => 0, 'mid_race' => 0, 'final_corner' => 0, 'final_straight' => 0];

        foreach ($skills as $skill) {
            $conditions = $skill->activation_conditions;
            if (! is_array($conditions) || ! isset($conditions['phase'])) {
                continue;
            }
            $canonicalPhase = self::PHASE_MAPPING[$conditions['phase']] ?? null;
            if ($canonicalPhase !== null && isset($phaseCoverage[$canonicalPhase])) {
                $phaseCoverage[$canonicalPhase]++;
            }
        }

        $phasesCovered = count(array_filter($phaseCoverage, fn (int $c) => $c > 0));
        $baseCoverage = ($phasesCovered / 4) * 40;

        $depthBonus = 0.0;
        foreach ($phaseCoverage as $count) {
            if ($count >= 2) {
                $depthBonus += 2.5;
            }
        }
        $depthBonus = min(10.0, $depthBonus);

        $synergySubScore = 0.0;
        if ($skills->count() >= 2) {
            $synergyAnalysis = $this->skillAnalysisService->analyzeSynergies($skills);
            $synergyStrengths = array_map(fn (array $entry) => $entry['synergy_strength'] ?? 0, $synergyAnalysis);
            $avgStrength = count($synergyStrengths) > 0 ? array_sum($synergyStrengths) / count($synergyStrengths) : 0;
            $synergySubScore = min(15.0, $avgStrength * 1.5);
        }

        $uniqueSkill = $this->skillStrategyAnalyzer->detectUniqueSkill($skills);
        $uniqueSkillScore = 10.0;
        $uniqueSkillName = null;

        if ($uniqueSkill !== null) {
            $uniqueAnalysis = $this->skillStrategyAnalyzer->analyzeUniqueSkillCenterpiece($character, $uniqueSkill, $skills);
            $uniqueSkillScore = round(($uniqueAnalysis['score'] / 100) * 20, 1);
            $uniqueSkillName = $uniqueSkill->name;

            if (! $uniqueAnalysis['is_supported']) {
                $issues[] = 'Unique skill activation conditions are not reliably supported';
                $recommendations = [...$recommendations, ...$uniqueAnalysis['recommendations']];
            }
        }

        $economy = $this->skillStrategyAnalyzer->calculateSPEfficiency($acquisitions);
        $wasteAnalysis = $this->skillStrategyAnalyzer->flagSPWaste($acquisitions, $character->available_sp ?? 0);
        $spEfficiencyScore = round(($economy['efficiency_score'] / 100) * 15, 1);

        if ($economy['avg_hint_level'] < 2.0) {
            $recommendations[] = 'Delay non-essential skill purchases until better hint levels are available';
        }

        if ($wasteAnalysis['sp_waste_total'] >= 60) {
            $issues[] = 'SP spending is inefficient for this build';
            $recommendations[] = 'Prioritize discounted or higher-value skills before low-hint purchases';
            $recommendations = [...$recommendations, ...$wasteAnalysis['wasteful_purchases']];
        }

        $score = min(100.0, $baseCoverage + $depthBonus + $synergySubScore + $uniqueSkillScore + $spEfficiencyScore);

        $uncoveredPhases = array_keys(array_filter($phaseCoverage, fn (int $c) => $c === 0));
        foreach ($uncoveredPhases as $phase) {
            $label = str_replace('_', ' ', $phase);
            $issues[] = 'No skill covers the '.$label.' phase';
            $recommendations[] = 'Acquire a skill that activates during '.$label;
        }

        return new SynergyLayerScore(
            'skill_phase',
            round($score, 1),
            $weight,
            array_values(array_unique($issues)),
            array_values(array_unique($recommendations)),
            [
                'display_name' => 'Skill Strategy & Economy',
                'phase_coverage_score' => round($baseCoverage, 1),
                'phase_depth_score' => round($depthBonus, 1),
                'intra_skill_synergy_score' => round($synergySubScore, 1),
                'unique_skill_score' => round($uniqueSkillScore, 1),
                'sp_efficiency_score' => round($spEfficiencyScore, 1),
                'unique_skill_name' => $uniqueSkillName,
                'avg_hint_level' => $economy['avg_hint_level'],
                'total_sp_saved' => $economy['total_sp_saved'],
                'sp_waste_total' => $wasteAnalysis['sp_waste_total'],
            ],
        );
    }

    /**
     * Layer 3: Running Style Synergy
     *
     * Checks whether skill types align with the character's running style.
     */
    private function analyzeRunningStyleSynergy(Character $character, string $runningStyle, float $weight): SynergyLayerScore
    {
        $acquisitions = $character->skillAcquisitions;
        $issues = [];
        $recommendations = [];

        if ($acquisitions->isEmpty()) {
            return new SynergyLayerScore('running_style', 50.0, $weight, [], ['Acquire skills that match your running style']);
        }

        $skills = $acquisitions->map(fn ($a) => $a->skill)->filter();
        $idealTypes = self::STYLE_SKILL_MAP[$runningStyle] ?? ['speed', 'passive'];

        $matchCount = 0;
        $mismatchCount = 0;

        foreach ($skills as $skill) {
            if (in_array($skill->skill_type, $idealTypes, true) || $skill->skill_type === 'unique') {
                $matchCount++;
            } else {
                if ($skill->skill_type === 'debuff') {
                    continue;
                }
                $mismatchCount++;
            }

            $conditions = $skill->activation_conditions;
            if (is_array($conditions) && isset($conditions['running_style'])) {
                $requiredStyle = $conditions['running_style'];
                if ($requiredStyle !== 'any' && $requiredStyle !== $runningStyle) {
                    $issues[] = $skill->name.' requires '.$requiredStyle.' running style but character uses '.$runningStyle;
                }
            }
        }

        $total = $matchCount + $mismatchCount;
        $score = $total > 0 ? ($matchCount / $total) * 100 : 50;

        if ($mismatchCount > $matchCount && $total > 2) {
            $recommendations[] = 'Most skills do not match '.$runningStyle.' style — consider replacing mismatched skills';
        }

        return new SynergyLayerScore('running_style', round($score, 1), $weight, $issues, $recommendations);
    }

    /**
     * Layer 4: Inheritance Synergy
     *
     * Evaluates how well inherited sparks align with the build strategy.
     */
    private function analyzeInheritanceSynergy(Character $character, string $runningStyle, float $weight): SynergyLayerScore
    {
        $career = $character->careers->first();
        $issues = [];
        $recommendations = [];

        if ($career === null) {
            return new SynergyLayerScore('inheritance', 50.0, $weight, [], ['Start a career to benefit from inheritance']);
        }

        $parents = $career->parentCharacters ?? collect();
        $events = InheritanceEvent::where('career_id', $career->id)->where('is_applied', true)->get();

        if ($parents->isEmpty() && $events->isEmpty()) {
            return new SynergyLayerScore('inheritance', 50.0, $weight, [], ['Select parents with complementary skills and stats']);
        }

        $score = 50.0;

        $affinityMultiplier = 1.0;
        if ($parents->isNotEmpty()) {
            $avgMultiplier = $parents->avg(function (ParentCharacter $p) {
                $grade = $p->affinity_grade instanceof AffinityGrade
                    ? $p->affinity_grade
                    : AffinityGrade::tryFrom((string) $p->affinity_grade);

                return match ($grade) {
                    AffinityGrade::High => 1.0,
                    AffinityGrade::Standard => 0.7,
                    AffinityGrade::Low => 0.4,
                    default => 0.7,
                };
            });
            $affinityMultiplier = $avgMultiplier;
        }

        $eventScore = 0;
        $eventCount = 0;

        foreach ($events as $event) {
            $eventCount++;
            $sparkScore = match ($event->spark_type) {
                SparkType::Blue => $this->scoreBlueSparkAlignment($event, $character, $runningStyle),
                SparkType::Pink => $this->scorePinkSparkAlignment($event, $runningStyle),
                SparkType::Green => 70.0,
                SparkType::White => 60.0,
                default => 50.0,
            };
            $eventScore += $sparkScore * ($event->star_level / 3);
        }

        if ($eventCount > 0) {
            $score = ($eventScore / $eventCount) * $affinityMultiplier;
        } else {
            $score = 50.0 * $affinityMultiplier;
        }

        $score = min(100, max(0, $score));

        if ($affinityMultiplier < 0.6) {
            $issues[] = 'Low parent affinity reduces inheritance effectiveness';
            $recommendations[] = 'Choose parents with higher affinity (◎) for better sparks';
        }

        if ($eventCount === 0) {
            $recommendations[] = 'Apply inheritance events to boost your build';
        }

        return new SynergyLayerScore('inheritance', round($score, 1), $weight, $issues, $recommendations);
    }

    /**
     * Layer 5: Team Synergy (Unity Cup only)
     */
    private function analyzeTeamSynergy(Character $character, bool $isUnityCup, float $weight): SynergyLayerScore
    {
        if (! $isUnityCup || $weight === 0.0) {
            return new SynergyLayerScore('team', 100.0, $weight, [], []);
        }

        $team = $character->team_composition;
        $issues = [];
        $recommendations = [];

        if (! is_array($team) || empty($team)) {
            return new SynergyLayerScore('team', 30.0, $weight, ['No team composition set'], ['Configure team for Unity Cup']);
        }

        $styles = [];
        foreach ($team as $member) {
            if (is_array($member) && isset($member['running_style'])) {
                $styles[] = $member['running_style'];
            } elseif (is_string($member)) {
                $styles[] = $member;
            }
        }

        $uniqueStyles = array_unique($styles);
        $styleCount = count($uniqueStyles);
        $totalMembers = count($styles);

        $idealRoles = ['escape', 'lead', 'chase'];
        $coveredRoles = count(array_intersect($uniqueStyles, $idealRoles));

        $score = match (true) {
            $coveredRoles >= 3 => 90 + min(10, ($totalMembers - 3) * 2),
            $coveredRoles === 2 => 65 + ($styleCount >= 3 ? 10 : 0),
            $coveredRoles === 1 => 40,
            default => 20,
        };

        if ($totalMembers >= 3 && $styleCount === 1) {
            $issues[] = 'All team members share the same running style';
            $recommendations[] = 'Diversify team with different running styles (front runner + mid-pack + closer)';
            $score = max(15, $score - 30);
        }

        return new SynergyLayerScore('team', round(min(100, $score), 1), $weight, $issues, $recommendations);
    }

    /**
     * Layer 6: Environmental Synergy
     *
     * Combines deck score from SynergyScorer with track/weather-conditional skills.
     */
    private function analyzeEnvironmentalSynergy(Character $character, float $weight): SynergyLayerScore
    {
        $issues = [];
        $recommendations = [];

        $deckResult = $this->synergyScorer->calculateDeckScore($character);
        $deckScore = (float) ($deckResult['score'] ?? 0);

        $conditionSkillCount = 0;
        $acquisitions = $character->skillAcquisitions;

        foreach ($acquisitions as $acquisition) {
            $skill = $acquisition->skill;
            if ($skill === null) {
                continue;
            }
            $conditions = $skill->activation_conditions;
            if (! is_array($conditions)) {
                continue;
            }

            $hasEnvironmental = isset($conditions['track'])
                || isset($conditions['weather'])
                || isset($conditions['surface'])
                || isset($conditions['direction']);

            if ($hasEnvironmental) {
                $conditionSkillCount++;
            }
        }

        $conditionScore = min(100.0, $conditionSkillCount * 25.0);
        $hintPoolAnalysis = $this->skillStrategyAnalyzer->analyzeSupportDeckHintPools($character);
        $adjustedDeckScore = ($deckScore * 0.7) + ($hintPoolAnalysis['consistency_score'] * 0.3);
        $score = ($adjustedDeckScore * 0.5) + ($conditionScore * 0.5);

        if ($deckScore < 30) {
            $recommendations[] = 'Improve support deck composition for better environmental synergy';
        }

        if ($hintPoolAnalysis['consistency_score'] < 50) {
            $recommendations[] = 'Use support cards with tighter hint pools for more reliable skill access';
        }

        if ($conditionSkillCount === 0) {
            $recommendations[] = 'Consider adding track or weather-conditional skills for specific race advantages';
        }

        return new SynergyLayerScore(
            'environmental',
            round($score, 1),
            $weight,
            $issues,
            array_values(array_unique($recommendations)),
            [
                'display_name' => 'Environmental Synergy',
                'deck_score' => round($deckScore, 1),
                'hint_pool_consistency_score' => round($hintPoolAnalysis['consistency_score'], 1),
                'condition_skill_count' => $conditionSkillCount,
            ],
        );
    }

    /**
     * Layer 7: Debuff Synergy
     */
    private function analyzeDebuffSynergy(Character $character, float $weight): SynergyLayerScore
    {
        $debuffCount = 0;
        $issues = [];
        $recommendations = [];

        foreach ($character->skillAcquisitions as $acquisition) {
            if ($acquisition->skill?->skill_type === 'debuff') {
                $debuffCount++;
            }
        }

        $score = match (true) {
            $debuffCount >= 3 => 90.0,
            $debuffCount === 2 => 75.0,
            $debuffCount === 1 => 55.0,
            default => 50.0,
        };

        if ($debuffCount === 1) {
            $recommendations[] = 'Add more debuff skills for a full debuff strategy, or drop the single debuff for a pure speed build';
        }

        return new SynergyLayerScore('debuff', $score, $weight, $issues, $recommendations);
    }

    /**
     * Resolve the character's primary running style from their best aptitude grade.
     */
    private function resolveRunningStyle(Character $character): string
    {
        $bestAptitude = $character->aptitudes
            ->where('running_style', '!=', null)
            ->sortByDesc(fn (Aptitude $a) => self::GRADE_RANK[$a->grade] ?? 0)
            ->first();

        return $bestAptitude?->running_style ?? 'lead';
    }

    /**
     * Resolve the character's primary distance from their best aptitude grade.
     */
    private function resolveDistance(Character $character): string
    {
        $bestAptitude = $character->aptitudes
            ->where('distance_type', '!=', null)
            ->sortByDesc(fn (Aptitude $a) => self::GRADE_RANK[$a->grade] ?? 0)
            ->first();

        return $bestAptitude?->distance_type ?? 'medium';
    }

    /**
     * Score a blue spark's alignment with the build's priority stats.
     */
    private function scoreBlueSparkAlignment(InheritanceEvent $event, Character $character, string $runningStyle): float
    {
        $targetStat = $event->target_stat;
        if ($targetStat === null) {
            return 50.0;
        }

        $distance = $this->resolveDistance($character);
        $priorities = self::STAT_PRIORITY_MAP[$runningStyle][$distance]
            ?? self::STAT_PRIORITY_MAP['lead']['medium'];

        $statKey = strtolower($targetStat);
        if ($statKey === 'wisdom') {
            $statKey = 'wit';
        }

        $priority = $priorities[$statKey] ?? 0.5;
        $maxPriority = max($priorities);

        return ($priority / $maxPriority) * 100;
    }

    /**
     * Score a pink spark's alignment with the running style.
     */
    private function scorePinkSparkAlignment(InheritanceEvent $event, string $runningStyle): float
    {
        $skillData = $event->inherited_skill_data;
        if (! is_array($skillData)) {
            return 60.0;
        }

        $skillType = $skillData['skill_type'] ?? null;
        $idealTypes = self::STYLE_SKILL_MAP[$runningStyle] ?? ['speed', 'passive'];

        if ($skillType !== null && in_array($skillType, $idealTypes, true)) {
            return 90.0;
        }

        return 55.0;
    }

    /**
     * Collect issues with high severity from all layers.
     *
     * @param  array<int, SynergyLayerScore>  $layers
     * @return array<int, string>
     */
    private function collectCriticalIssues(array $layers): array
    {
        $critical = [];
        foreach ($layers as $layer) {
            if ($layer->score < 30 && $layer->weight > 0) {
                foreach ($layer->issues as $issue) {
                    $critical[] = $issue;
                }
            }
        }

        return $critical;
    }
}
