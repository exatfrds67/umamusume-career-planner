<?php

namespace App\Services\MCP\Agents;

use App\Models\Character;
use App\Services\MCP\MCPClientService;
use App\Services\SkillEvolutionService;
use Illuminate\Support\Collection;

/**
 * Skill Build Planning Agent
 *
 * Provides character synergy analysis and meta optimization for skill builds.
 * Analyzes character aptitudes, racing goals, and meta tier rankings to
 * recommend optimal skill combinations that maximize performance.
 */
class SkillBuildPlanningAgent
{
    protected MCPClientService $mcpClient;

    protected SkillEvolutionService $evolutionService;

    /**
     * Skill categories and their strategic importance
     *
     * @var array<string, int>
     */
    protected array $skillCategoryWeights = [
        'speed' => 5,      // Acceleration and top speed
        'recovery' => 4,   // Stamina recovery
        'positioning' => 3, // Race positioning
        'debuff' => 3,     // Opponent debuffs
        'passive' => 2,    // Passive bonuses
    ];

    /**
     * Meta tier rankings
     *
     * @var array<string, int>
     */
    protected array $metaTierScores = [
        'SS' => 100,
        'S' => 85,
        'A' => 70,
        'B' => 55,
        'C' => 40,
    ];

    public function __construct(MCPClientService $mcpClient, SkillEvolutionService $evolutionService)
    {
        $this->mcpClient = $mcpClient;
        $this->evolutionService = $evolutionService;
    }

    /**
     * Analyze skill build and provide comprehensive planning recommendations
     *
     * @param  array<string, mixed>  $context
     * @return array{
     *     character_analysis: array<string, mixed>,
     *     skill_synergies: array<string, mixed>,
     *     meta_optimization: array<string, mixed>,
     *     build_recommendations: array<string, mixed>,
     *     recommendations: array<string, string>,
     *     confidence: float
     * }
     */
    public function analyzeSkillBuild(
        Character $character,
        Collection $availableSkills,
        Collection $currentSkills,
        array $context = []
    ): array {
        // Analyze character strengths and weaknesses
        $characterAnalysis = $this->analyzeCharacter($character);

        // Identify skill synergies
        $skillSynergies = $this->identifySkillSynergies($character, $availableSkills, $currentSkills);

        // Optimize for meta performance
        $metaOptimization = $this->optimizeForMeta($character, $availableSkills, $characterAnalysis);

        // Generate build recommendations
        $buildRecommendations = $this->generateBuildRecommendations(
            $character,
            $characterAnalysis,
            $skillSynergies,
            $metaOptimization
        );

        // Generate strategic recommendations
        $recommendations = $this->generateRecommendations(
            $characterAnalysis,
            $skillSynergies,
            $metaOptimization
        );

        // Calculate confidence score
        $confidence = $this->calculateConfidence($characterAnalysis, $skillSynergies);

        return [
            'character_analysis' => $characterAnalysis,
            'skill_synergies' => $skillSynergies,
            'meta_optimization' => $metaOptimization,
            'build_recommendations' => $buildRecommendations,
            'recommendations' => $recommendations,
            'confidence' => $confidence,
        ];
    }

    /**
     * Analyze character strengths and weaknesses
     *
     * @return array<string, mixed>
     */
    protected function analyzeCharacter(Character $character): array
    {
        // Analyze aptitudes
        $aptitudes = $character->aptitudes;
        $aptitudeAnalysis = $this->analyzeAptitudes($aptitudes);

        // Analyze stats
        $stats = $character->current_stats ?? [];
        $statAnalysis = $this->analyzeStats($stats);

        // Determine racing style
        $racingStyle = $this->determineRacingStyle($aptitudeAnalysis, $statAnalysis);

        // Identify strengths and weaknesses
        $strengths = $this->identifyStrengths($aptitudeAnalysis, $statAnalysis);
        $weaknesses = $this->identifyWeaknesses($aptitudeAnalysis, $statAnalysis);

        return [
            'aptitude_analysis' => $aptitudeAnalysis,
            'stat_analysis' => $statAnalysis,
            'racing_style' => $racingStyle,
            'strengths' => $strengths,
            'weaknesses' => $weaknesses,
            'specialization' => $this->determineSpecialization($aptitudeAnalysis),
        ];
    }

    /**
     * Analyze character aptitudes
     *
     * @return array<string, mixed>
     */
    protected function analyzeAptitudes($aptitudes): array
    {
        $distanceAptitudes = [];
        $surfaceAptitudes = [];
        $styleAptitudes = [];

        foreach ($aptitudes as $aptitude) {
            $grade = $aptitude->grade;
            $numericGrade = $this->gradeToNumeric($grade);

            if (in_array($aptitude->distance_type, ['sprint', 'mile', 'medium', 'long'])) {
                $distanceAptitudes[$aptitude->distance_type] = [
                    'grade' => $grade,
                    'numeric' => $numericGrade,
                ];
            }

            if (in_array($aptitude->surface_type, ['turf', 'dirt'])) {
                $surfaceAptitudes[$aptitude->surface_type] = [
                    'grade' => $grade,
                    'numeric' => $numericGrade,
                ];
            }

            if (in_array($aptitude->running_style, ['front_runner', 'pace_chaser', 'late_surger', 'end_closer'])) {
                $styleAptitudes[$aptitude->running_style] = [
                    'grade' => $grade,
                    'numeric' => $numericGrade,
                ];
            }
        }

        // Find best aptitudes
        $bestDistance = $this->findBestAptitude($distanceAptitudes);
        $bestSurface = $this->findBestAptitude($surfaceAptitudes);
        $bestStyle = $this->findBestAptitude($styleAptitudes);

        return [
            'distance_aptitudes' => $distanceAptitudes,
            'surface_aptitudes' => $surfaceAptitudes,
            'style_aptitudes' => $styleAptitudes,
            'best_distance' => $bestDistance,
            'best_surface' => $bestSurface,
            'best_style' => $bestStyle,
        ];
    }

    /**
     * Find best aptitude from a set
     *
     * @param  array<string, array>  $aptitudes
     */
    protected function findBestAptitude(array $aptitudes): ?string
    {
        if (empty($aptitudes)) {
            return null;
        }

        $best = null;
        $bestScore = 0;

        foreach ($aptitudes as $type => $data) {
            if ($data['numeric'] > $bestScore) {
                $bestScore = $data['numeric'];
                $best = $type;
            }
        }

        return $best;
    }

    /**
     * Convert grade letter to numeric value
     */
    protected function gradeToNumeric(string $grade): int
    {
        return match ($grade) {
            'SS' => 9,
            'S' => 8,
            'A' => 7,
            'B' => 6,
            'C' => 5,
            'D' => 4,
            'E' => 3,
            'F' => 2,
            'G' => 1,
            default => 0,
        };
    }

    /**
     * Analyze character stats
     *
     * @param  array<string, int>  $stats
     * @return array<string, mixed>
     */
    protected function analyzeStats(array $stats): array
    {
        $statRatings = [];

        foreach ($stats as $stat => $value) {
            $statRatings[$stat] = [
                'value' => $value,
                'rating' => $this->rateStatValue($value),
                'breakpoint_status' => $this->getBreakpointStatus($value),
            ];
        }

        return [
            'stat_ratings' => $statRatings,
            'highest_stat' => $this->findHighestStat($stats),
            'lowest_stat' => $this->findLowestStat($stats),
            'total_stats' => array_sum($stats),
        ];
    }

    /**
     * Rate stat value
     */
    protected function rateStatValue(int $value): string
    {
        return match (true) {
            $value >= 1200 => 'excellent',
            $value >= 900 => 'good',
            $value >= 600 => 'adequate',
            $value >= 400 => 'low',
            default => 'very_low',
        };
    }

    /**
     * Get breakpoint status for stat value
     */
    protected function getBreakpointStatus(int $value): string
    {
        if ($value >= 1200) {
            return 'above_second_breakpoint';
        }

        if ($value >= 900) {
            return 'above_first_breakpoint';
        }

        return 'below_breakpoints';
    }

    /**
     * Find highest stat
     *
     * @param  array<string, int>  $stats
     */
    protected function findHighestStat(array $stats): ?string
    {
        if (empty($stats)) {
            return null;
        }

        return array_key_first(array_slice($stats, 0, 1, true));
    }

    /**
     * Find lowest stat
     *
     * @param  array<string, int>  $stats
     */
    protected function findLowestStat(array $stats): ?string
    {
        if (empty($stats)) {
            return null;
        }

        asort($stats);

        return array_key_first($stats);
    }

    /**
     * Determine racing style based on analysis
     *
     * @param  array<string, mixed>  $aptitudeAnalysis
     * @param  array<string, mixed>  $statAnalysis
     */
    protected function determineRacingStyle(array $aptitudeAnalysis, array $statAnalysis): string
    {
        $bestStyle = $aptitudeAnalysis['best_style'];
        $bestDistance = $aptitudeAnalysis['best_distance'];

        // Combine style and distance for racing style
        return match (true) {
            $bestStyle === 'front_runner' && in_array($bestDistance, ['sprint', 'mile']) => 'speed_specialist',
            $bestStyle === 'end_closer' && in_array($bestDistance, ['medium', 'long']) => 'stamina_specialist',
            $bestDistance === 'sprint' => 'sprinter',
            $bestDistance === 'long' => 'stayer',
            default => 'all_rounder',
        };
    }

    /**
     * Identify character strengths
     *
     * @param  array<string, mixed>  $aptitudeAnalysis
     * @param  array<string, mixed>  $statAnalysis
     * @return array<string, string>
     */
    protected function identifyStrengths(array $aptitudeAnalysis, array $statAnalysis): array
    {
        $strengths = [];

        // Aptitude strengths
        $bestDistance = $aptitudeAnalysis['best_distance'];
        $bestStyle = $aptitudeAnalysis['best_style'];

        if ($bestDistance) {
            $strengths['distance'] = "Excellent {$bestDistance} aptitude";
        }

        if ($bestStyle) {
            $strengths['style'] = "Strong {$bestStyle} aptitude";
        }

        // Stat strengths
        $highestStat = $statAnalysis['highest_stat'];
        if ($highestStat) {
            $value = $statAnalysis['stat_ratings'][$highestStat]['value'];
            if ($value >= 900) {
                $strengths['stat'] = "High {$highestStat} ({$value})";
            }
        }

        return $strengths;
    }

    /**
     * Identify character weaknesses
     *
     * @param  array<string, mixed>  $aptitudeAnalysis
     * @param  array<string, mixed>  $statAnalysis
     * @return array<string, string>
     */
    protected function identifyWeaknesses(array $aptitudeAnalysis, array $statAnalysis): array
    {
        $weaknesses = [];

        // Stat weaknesses
        $lowestStat = $statAnalysis['lowest_stat'];
        if ($lowestStat) {
            $value = $statAnalysis['stat_ratings'][$lowestStat]['value'];
            if ($value < 600) {
                $weaknesses['stat'] = "Low {$lowestStat} ({$value})";
            }
        }

        return $weaknesses;
    }

    /**
     * Determine character specialization
     *
     * @param  array<string, mixed>  $aptitudeAnalysis
     */
    protected function determineSpecialization(array $aptitudeAnalysis): string
    {
        $bestDistance = $aptitudeAnalysis['best_distance'];
        $bestSurface = $aptitudeAnalysis['best_surface'];

        return "{$bestDistance}_{$bestSurface}";
    }

    /**
     * Identify skill synergies
     *
     * @return array<string, mixed>
     */
    protected function identifySkillSynergies(
        Character $character,
        Collection $availableSkills,
        Collection $currentSkills
    ): array {
        // Analyze current skill synergies
        $currentSynergies = $this->analyzeCurrentSynergies($currentSkills);

        // Find complementary skills
        $complementarySkills = $this->findComplementarySkills($character, $availableSkills, $currentSkills);

        // Identify skill gaps
        $skillGaps = $this->identifySkillGaps($character, $currentSkills);

        // Calculate synergy score
        $synergyScore = $this->calculateSynergyScore($currentSynergies, $skillGaps);

        return [
            'current_synergies' => $currentSynergies,
            'complementary_skills' => $complementarySkills,
            'skill_gaps' => $skillGaps,
            'synergy_score' => $synergyScore,
        ];
    }

    /**
     * Analyze current skill synergies
     *
     * @return array<string, mixed>
     */
    protected function analyzeCurrentSynergies(Collection $currentSkills): array
    {
        $categoryCounts = [];

        foreach ($currentSkills as $skill) {
            $category = $skill->category ?? 'unknown';
            $categoryCounts[$category] = ($categoryCounts[$category] ?? 0) + 1;
        }

        return [
            'category_distribution' => $categoryCounts,
            'total_skills' => $currentSkills->count(),
            'balance_score' => $this->calculateBalanceScore($categoryCounts),
        ];
    }

    /**
     * Calculate balance score for skill distribution
     *
     * @param  array<string, int>  $categoryCounts
     */
    protected function calculateBalanceScore(array $categoryCounts): int
    {
        if (empty($categoryCounts)) {
            return 0;
        }

        // Ideal distribution: 2-3 skills per category
        $score = 100;

        foreach ($categoryCounts as $count) {
            if ($count > 4) {
                $score -= 10; // Too many in one category
            } elseif ($count < 2) {
                $score -= 5; // Too few in one category
            }
        }

        return max(0, $score);
    }

    /**
     * Find complementary skills
     *
     * @return array<string, mixed>
     */
    protected function findComplementarySkills(
        Character $character,
        Collection $availableSkills,
        Collection $currentSkills
    ): array {
        $currentCategories = $currentSkills->pluck('category')->unique()->toArray();
        $complementary = [];

        foreach ($availableSkills as $skill) {
            $category = $skill->category ?? 'unknown';

            // Skip if already have many skills in this category
            $currentCount = $currentSkills->where('category', $category)->count();
            if ($currentCount >= 3) {
                continue;
            }

            // Check if skill complements character
            if ($this->isComplementaryToCharacter($skill, $character)) {
                $complementary[] = [
                    'skill_id' => $skill->id,
                    'skill_name' => $skill->name,
                    'category' => $category,
                    'synergy_reason' => $this->getSynergyReason($skill, $character),
                ];
            }
        }

        return $complementary;
    }

    /**
     * Check if skill is complementary to character
     */
    protected function isComplementaryToCharacter(object $skill, Character $character): bool
    {
        // Check if skill matches character's best distance
        $aptitudes = $character->aptitudes;
        $bestDistance = $this->getBestDistanceAptitude($aptitudes);

        // Simple check: skill name contains distance type
        $skillName = strtolower($skill->name);

        return str_contains($skillName, $bestDistance);
    }

    /**
     * Get synergy reason for skill
     */
    protected function getSynergyReason(object $skill, Character $character): string
    {
        $aptitudes = $character->aptitudes;
        $bestDistance = $this->getBestDistanceAptitude($aptitudes);

        return "Complements {$bestDistance} specialization";
    }

    /**
     * Get best distance aptitude
     */
    protected function getBestDistanceAptitude($aptitudes): string
    {
        $distanceGrades = [];

        foreach ($aptitudes as $aptitude) {
            if (in_array($aptitude->distance_type, ['sprint', 'mile', 'medium', 'long'])) {
                $distanceGrades[$aptitude->distance_type] = $this->gradeToNumeric($aptitude->grade);
            }
        }

        if (empty($distanceGrades)) {
            return 'mile';
        }

        arsort($distanceGrades);

        return array_key_first($distanceGrades);
    }

    /**
     * Identify skill gaps
     *
     * @return array<string, mixed>
     */
    protected function identifySkillGaps(Character $character, Collection $currentSkills): array
    {
        $gaps = [];

        // Check for missing essential categories
        $currentCategories = $currentSkills->pluck('category')->unique()->toArray();

        $essentialCategories = ['speed', 'recovery', 'positioning'];

        foreach ($essentialCategories as $category) {
            if (! in_array($category, $currentCategories)) {
                $gaps[] = [
                    'category' => $category,
                    'severity' => 'high',
                    'recommendation' => "Add {$category} skills for balanced build",
                ];
            }
        }

        return $gaps;
    }

    /**
     * Calculate synergy score
     *
     * @param  array<string, mixed>  $currentSynergies
     * @param  array<string, mixed>  $skillGaps
     */
    protected function calculateSynergyScore(array $currentSynergies, array $skillGaps): int
    {
        $score = $currentSynergies['balance_score'];

        // Deduct for gaps
        foreach ($skillGaps as $gap) {
            if ($gap['severity'] === 'high') {
                $score -= 15;
            }
        }

        return max(0, $score);
    }

    /**
     * Optimize for meta performance
     *
     * @param  array<string, mixed>  $characterAnalysis
     * @return array<string, mixed>
     */
    protected function optimizeForMeta(
        Character $character,
        Collection $availableSkills,
        array $characterAnalysis
    ): array {
        // Identify meta skills
        $metaSkills = $this->identifyMetaSkills($availableSkills);

        // Match meta skills to character
        $matchedMetaSkills = $this->matchMetaSkillsToCharacter($metaSkills, $characterAnalysis);

        // Calculate meta optimization score
        $metaScore = $this->calculateMetaScore($matchedMetaSkills);

        return [
            'meta_skills' => $metaSkills,
            'matched_meta_skills' => $matchedMetaSkills,
            'meta_score' => $metaScore,
        ];
    }

    /**
     * Identify meta skills
     *
     * @return array<string, mixed>
     */
    protected function identifyMetaSkills(Collection $availableSkills): array
    {
        $metaSkills = [];

        foreach ($availableSkills as $skill) {
            $metaTier = $skill->meta_tier ?? 'C';

            if (in_array($metaTier, ['SS', 'S', 'A'])) {
                $metaSkills[] = [
                    'skill_id' => $skill->id,
                    'skill_name' => $skill->name,
                    'meta_tier' => $metaTier,
                    'tier_score' => $this->metaTierScores[$metaTier],
                ];
            }
        }

        // Sort by tier score
        usort($metaSkills, fn ($a, $b) => $b['tier_score'] <=> $a['tier_score']);

        return $metaSkills;
    }

    /**
     * Match meta skills to character
     *
     * @param  array<string, mixed>  $metaSkills
     * @param  array<string, mixed>  $characterAnalysis
     * @return array<string, mixed>
     */
    protected function matchMetaSkillsToCharacter(array $metaSkills, array $characterAnalysis): array
    {
        $matched = [];

        $specialization = $characterAnalysis['specialization'];

        foreach ($metaSkills as $skill) {
            // Simple matching: all meta skills are good
            $matched[] = [
                ...$skill,
                'match_reason' => 'High meta tier skill',
                'priority' => $skill['tier_score'] >= 85 ? 'high' : 'medium',
            ];
        }

        return $matched;
    }

    /**
     * Calculate meta optimization score
     *
     * @param  array<string, mixed>  $matchedMetaSkills
     */
    protected function calculateMetaScore(array $matchedMetaSkills): int
    {
        if (empty($matchedMetaSkills)) {
            return 0;
        }

        $totalScore = 0;
        foreach ($matchedMetaSkills as $skill) {
            $totalScore += $skill['tier_score'];
        }

        return (int) ($totalScore / count($matchedMetaSkills));
    }

    /**
     * Generate build recommendations
     *
     * @param  array<string, mixed>  $characterAnalysis
     * @param  array<string, mixed>  $skillSynergies
     * @param  array<string, mixed>  $metaOptimization
     * @return array<string, mixed>
     */
    protected function generateBuildRecommendations(
        Character $character,
        array $characterAnalysis,
        array $skillSynergies,
        array $metaOptimization
    ): array {
        $recommendations = [];

        // Priority 1: Meta skills that match character
        $matchedMeta = array_slice($metaOptimization['matched_meta_skills'], 0, 3);
        if (! empty($matchedMeta)) {
            $recommendations['meta_priority'] = [
                'title' => 'High Priority Meta Skills',
                'skills' => $matchedMeta,
                'reason' => 'Top tier skills that enhance overall performance',
            ];
        }

        // Priority 2: Complementary skills
        $complementary = array_slice($skillSynergies['complementary_skills'], 0, 3);
        if (! empty($complementary)) {
            $recommendations['synergy_priority'] = [
                'title' => 'Synergy-Enhancing Skills',
                'skills' => $complementary,
                'reason' => 'Skills that complement character strengths',
            ];
        }

        // Priority 3: Gap-filling skills
        $gaps = $skillSynergies['skill_gaps'];
        if (! empty($gaps)) {
            $recommendations['gap_filling'] = [
                'title' => 'Essential Gap-Filling Skills',
                'gaps' => $gaps,
                'reason' => 'Address missing skill categories for balanced build',
            ];
        }

        return $recommendations;
    }

    /**
     * Generate strategic recommendations
     *
     * @param  array<string, mixed>  $characterAnalysis
     * @param  array<string, mixed>  $skillSynergies
     * @param  array<string, mixed>  $metaOptimization
     * @return array<string, string>
     */
    protected function generateRecommendations(
        array $characterAnalysis,
        array $skillSynergies,
        array $metaOptimization
    ): array {
        $recommendations = [];

        // Character-specific recommendations
        $racingStyle = $characterAnalysis['racing_style'];
        $recommendations['racing_style'] = "Build optimized for {$racingStyle} racing style";

        // Synergy recommendations
        $synergyScore = $skillSynergies['synergy_score'];
        if ($synergyScore < 70) {
            $recommendations['synergy'] = 'Improve skill synergy by balancing skill categories';
        }

        // Meta recommendations
        $metaScore = $metaOptimization['meta_score'];
        if ($metaScore >= 80) {
            $recommendations['meta'] = 'Excellent meta optimization - continue current strategy';
        } elseif ($metaScore < 60) {
            $recommendations['meta'] = 'Consider adding more meta-tier skills for competitive performance';
        }

        return $recommendations;
    }

    /**
     * Calculate confidence score for recommendations
     *
     * @param  array<string, mixed>  $characterAnalysis
     * @param  array<string, mixed>  $skillSynergies
     */
    protected function calculateConfidence(array $characterAnalysis, array $skillSynergies): float
    {
        $confidence = 1.0;

        // Reduce confidence if character analysis is incomplete
        if (empty($characterAnalysis['strengths'])) {
            $confidence *= 0.8;
        }

        // Reduce confidence if synergy score is low
        $synergyScore = $skillSynergies['synergy_score'];
        if ($synergyScore < 50) {
            $confidence *= 0.9;
        }

        return round($confidence, 2);
    }
}
