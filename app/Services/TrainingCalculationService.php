<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Character;
use App\Services\MCP\MCPClientService;
use App\Services\MCP\TrainingOptimizationAgent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Training Calculation Service
 *
 * Handles comprehensive training calculations including:
 * - Base stat gain calculations with support card bonuses
 * - Friendship training multipliers
 * - Facility level bonuses for Unity Cup
 * - Energy cost calculations
 * - Training failure risk assessment
 * - MCP integration for complex calculations
 */
class TrainingCalculationService
{
    protected MCPClientService $mcpClient;

    protected TrainingOptimizationAgent $trainingAgent;

    /**
     * Base stat gains for each training type
     *
     * @var array<string, array<string, int>>
     */
    protected array $baseStatGains = [
        'speed' => ['speed' => 10, 'power' => 2],
        'stamina' => ['stamina' => 10, 'power' => 2],
        'power' => ['power' => 10, 'guts' => 2],
        'guts' => ['guts' => 10, 'wit' => 2],
        'wit' => ['wit' => 10, 'speed' => 2],
    ];

    /**
     * Base energy costs for each training type
     *
     * @var array<string, int>
     */
    protected array $baseEnergyCosts = [
        'speed' => 20,
        'stamina' => 20,
        'power' => 20,
        'guts' => 20,
        'wit' => 20,
        'rest' => -50, // Rest recovers energy
    ];

    public function __construct(MCPClientService $mcpClient, TrainingOptimizationAgent $trainingAgent)
    {
        $this->mcpClient = $mcpClient;
        $this->trainingAgent = $trainingAgent;
    }

    /**
     * Calculate predicted stat gains for a training session
     *
     * @param  array<string, mixed>  $trainingData
     * @return array{
     *     stat_gains: array<string, int>,
     *     energy_cost: int,
     *     failure_risk: float,
     *     total_bonus: float,
     *     breakdown: array<string, mixed>,
     *     scenario_specific: array<string, mixed>
     * }
     */
    public function calculateTrainingPrediction(): array
    {
        // Get base stat gains
        $baseGains = $this->baseStatGains[$trainingType] ?? [];

        // Calculate support card bonuses
        $supportCardBonus = $this->calculateSupportCardBonus(
            $character,
            $trainingType,
            $trainingData['support_cards'] ?? []
        );

        // Calculate friendship training multiplier
        $friendshipMultiplier = $this->calculateFriendshipMultiplier(
            $trainingData['participants'] ?? 0
        );

        // Calculate facility level bonus (Unity Cup)
        $facilityBonus = $this->calculateFacilityBonus(
            $character,
            $trainingType
        );

        // Calculate growth rate bonus
        $growthRateBonus = $this->calculateGrowthRateBonus(
            $character,
            $trainingType
        );

        // Calculate total multiplier
        $totalMultiplier = 1.0
            + $supportCardBonus
            + $friendshipMultiplier
            + $facilityBonus
            + $growthRateBonus;

        // Apply multiplier to base gains
        $finalGains = [];
        foreach ($baseGains as $stat => $gain) {
            $finalGains[$stat] = (int) round($gain * $totalMultiplier);
        }

        // Calculate energy cost
        $energyCost = $this->calculateEnergyCost(
            $character,
            $trainingType,
            $trainingData
        );

        // Calculate failure risk
        $failureRisk = $this->calculateFailureRisk(
            $character,
            $energyCost
        );

        // Calculate scenario-specific mechanics
        $scenarioSpecific = $this->calculateScenarioSpecificMechanics(
            $character,
            $trainingType,
            $trainingData
        );

        return [
            'stat_gains' => $finalGains,
            'energy_cost' => $energyCost,
            'failure_risk' => $failureRisk,
            'total_bonus' => $totalMultiplier - 1.0,
            'breakdown' => [
                'base_gains' => $baseGains,
                'support_card_bonus' => $supportCardBonus,
                'friendship_multiplier' => $friendshipMultiplier,
                'facility_bonus' => $facilityBonus,
                'growth_rate_bonus' => $growthRateBonus,
                'total_multiplier' => $totalMultiplier,
            ],
            'scenario_specific' => $scenarioSpecific,
        ];
    }

    /**
     * Calculate support card bonus
     *
     * @param  array<int, mixed>  $supportCards
     */
    protected function calculateSupportCardBonus(
        Character $character,
        string $trainingType,
        array $supportCards = []
    ): float {
        $bonus = 0.0;

        // If no support cards provided, get from character
        if (empty($supportCards)) {
            $supportCards = $character->supportCards()->with('supportCard')->get();
        }

        foreach ($supportCards as $characterCard) {
            // Get the support card definition
            $card = is_object($characterCard) && isset($characterCard->supportCard)
                ? $characterCard->supportCard
                : $characterCard;

            // Check if card type matches training type
            $cardType = is_array($card) && isset($card['card_type'])
                ? $card['card_type']
                : (is_object($card) && isset($card->card_type) ? $card->card_type : null);

            if ($cardType && strtolower($cardType) === strtolower($trainingType)) {
                // Base bonus: 10% per matching card
                $bonus = ($bonus ?? 0) + 0.10;

                // Additional bonus based on limit break level (if available)
                if (is_object($characterCard) && isset($characterCard->limit_break_level)) {
                    $bonus = ($bonus ?? 0) + $characterCard->limit_break_level * 0.02;
                } elseif (is_array($characterCard) && isset($characterCard['limit_break_level'])) {
                    $bonus = ($bonus ?? 0) + $characterCard['limit_break_level'] * 0.02;
                }
            }
        }

        return $bonus;
    }

    /**
     * Calculate friendship training multiplier
     * 2 participants = +2 bonus, 3 participants = +3 bonus
     */
    protected function calculateFriendshipMultiplier(int $participants): float
    {
        return match ($participants) {
            2 => 0.02, // +2% bonus
            3 => 0.03, // +3% bonus
            default => 0.0,
        };
    }

    /**
     * Calculate facility level bonus (Unity Cup)
     * Facility levels 1-5 provide 1.0x to 2.0x multipliers
     */
    protected function calculateFacilityBonus(
        Character $character,
        string $trainingType
    ): float {
        // Only applies to Unity Cup scenario
        if ($character->scenario_type !== 'unity_cup') {
            return 0.0;
        }

        $facilityLevels = $character->facility_levels ?? [];
        $level = $facilityLevels[$trainingType] ?? 1;

        // Facility level bonus: Level 1 = 0%, Level 5 = 100%
        return ($level - 1) * 0.25; // 0%, 25%, 50%, 75%, 100%
    }

    /**
     * Calculate growth rate bonus from inherited factors
     */
    protected function calculateGrowthRateBonus(
        Character $character,
        string $trainingType
    ): float {
        $growthRates = $character->growth_rates ?? [];
        $rate = $growthRates[$trainingType] ?? 0;

        // Growth rates are stored as percentages (10, 20, 30)
        return $rate / 100.0;
    }

    /**
     * Calculate energy cost for training
     *
     * @param  array<string, mixed>  $trainingData
     */
    protected function calculateEnergyCost(
        Character $character,
        string $trainingType,
        array $trainingData = []
    ): int {
        $baseCost = $this->baseEnergyCosts[$trainingType] ?? 20;

        // Mood affects energy cost
        $moodModifier = match ($character->mood_status) {
            'great' => 0.9, // 10% reduction
            'good' => 0.95, // 5% reduction
            'normal' => 1.0,
            'bad' => 1.05, // 5% increase
            'awful' => 1.10, // 10% increase
            default => 1.0,
        };

        return (int) round($baseCost * $moodModifier);
    }

    /**
     * Calculate training failure risk based on energy level
     */
    protected function calculateFailureRisk(
        Character $character,
        int $energyCost
    ): float {
        $currentEnergy = $character->energy_level ?? 100;
        $energyAfter = $currentEnergy - $energyCost;

        // Failure risk increases as energy decreases
        return match (true) {
            $energyAfter >= 70 => 0.0,
            $energyAfter >= 50 => 0.05,
            $energyAfter >= 30 => 0.15,
            $energyAfter >= 10 => 0.30,
            default => 0.50,
        };
    }

    /**
     * Calculate batch predictions for multiple training options
     *
     * @param  array<string>  $trainingTypes
     * @param  array<string, mixed>  $trainingData
     * @return array<string, array{
     *     stat_gains: array<string, int>,
     *     energy_cost: int,
     *     failure_risk: float,
     *     total_bonus: float,
     *     breakdown: array<string, mixed>
     * }>
     */
    public function calculateBatchPredictions(): array
        $predictions = [];

        foreach ($trainingTypes as $trainingType) {
            $predictions[$trainingType] = $this->calculateTrainingPrediction(
                $character,
                $trainingType,
                $trainingData
            );
        }

        return $predictions;
    }

    /**
     * Integrate with MCP Training Optimization Agent for complex calculations
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>|null
     */
    public function getMCPOptimization(
        Character $character,
        array $context = []
    ): ?array {
        // Check if MCP is enabled and strands-agents server is available
        if (! $this->mcpClient->isEnabled()) {
            Log::info('MCP is disabled, skipping optimization');

            return null;
        }

        if (! $this->mcpClient->isServerEnabled('strands-agents')) {
            Log::info('strands-agents MCP server is not enabled');

            return null;
        }

        // Cache key for MCP optimization
        $cacheKey = "mcp_training_optimization_{$character->id}_".md5(json_encode($context) ?: '');

        // Try to get from cache first (5 minutes TTL)
        return Cache::remember($cacheKey, 300, function () use ($character, $context) {
            try {
                // Use the TrainingOptimizationAgent for multi-agent workflow
                $optimization = $this->trainingAgent->getOptimization($character, $context);

                if ($optimization) {
                    Log::info('MCP training optimization completed', [
                        'character_id' => $character->id,
                        'workflow' => $optimization['agent_workflow'],
                        'confidence_score' => $optimization['confidence_score'],
                        'processing_time_ms' => $optimization['processing_time_ms'],
                    ]);

                    return $optimization;
                }

                // Fallback if agent returns null
                Log::info('MCP agent returned null, using fallback');

                return [
                    'status' => 'unavailable',
                    'message' => 'MCP agents not available',
                    'recommendations' => [],
                ];
            } catch (\Exception $e) {
                Log::error('MCP training optimization failed', [
                    'error' => $e->getMessage(),
                    'character_id' => $character->id,
                ]);

                return null;
            }
        });
    }

    /**
     * Get recommended training option based on character goals
     *
     * @param  array<string, mixed>  $trainingData
     * @return array{
     *     recommended_training: string,
     *     reason: string,
     *     prediction: array<string, mixed>,
     *     alternatives: array<string, array<string, mixed>>
     * }
     */
    public function getRecommendedTraining(): array
        // Get all available training types
        $trainingTypes = ['speed', 'stamina', 'power', 'guts', 'wit'];

        // Calculate predictions for all training types
        $predictions = $this->calculateBatchPredictions(
            $character,
            $trainingTypes,
            $trainingData
        );

        // Determine priority stats from character goals
        $priorityStats = $this->getPriorityStats($character);

        // Score each training option
        $scores = [];
        foreach ($predictions as $trainingType => $prediction) {
            $score = $this->scoreTrainingOption(
                $prediction,
                $priorityStats,
                $character
            );
            $scores[$trainingType] = $score;
        }

        // Sort by score (highest first)
        arsort($scores);

        // Get recommended training (highest score)
        $recommendedTraining = array_key_first($scores);
        $recommendedPrediction = $predictions[$recommendedTraining];

        // Get alternatives (next 2 best options)
        $alternatives = [];
        $alternativeTypes = array_slice(array_keys($scores), 1, 2);
        foreach ($alternativeTypes as $type) {
            $alternatives[$type] = $predictions[$type];
        }

        // Generate reason
        $reason = $this->generateRecommendationReason(
            $recommendedTraining,
            $recommendedPrediction,
            $priorityStats,
            $character
        );

        return [
            'recommended_training' => $recommendedTraining,
            'reason' => $reason,
            'prediction' => $recommendedPrediction,
            'alternatives' => $alternatives,
        ];
    }

    /**
     * Get priority stats from character goals
     *
     * @return array<string, int>
     */
    protected function getPriorityStats(): array
        $priorities = [];
        $goals = $character->goals ?? [];
        $targetStats = $goals['target_stats'] ?? [];
        $currentStats = $character->current_stats ?? [];

        foreach ($targetStats as $stat => $target) {
            $current = $currentStats[$stat] ?? 0;
            $gap = max(0, $target - $current);
            $priorities[$stat] = $gap;
        }

        // Sort by gap (highest first)
        arsort($priorities);

        return $priorities;
    }

    /**
     * Score a training option based on priority stats and character state
     *
     * @param  array<string, mixed>  $prediction
     * @param  array<string, int>  $priorityStats
     */
    protected function scoreTrainingOption(
        array $prediction,
        array $priorityStats,
        Character $character
    ): float {
        $score = 0.0;

        // Score based on stat gains for priority stats
        foreach ($prediction['stat_gains'] as $stat => $gain) {
            $priority = $priorityStats[$stat] ?? 0;
            if ($priority > 0) {
                // Higher priority stats get more weight
                $weight = $priority / max(1, array_sum($priorityStats));
                $score = ($score ?? 0) + $gain * $weight * 10;
            }
        }

        // Penalize high failure risk
        $score -= $prediction['failure_risk'] * 50;

        // Penalize if energy would drop too low
        $energyAfter = ($character->energy_level ?? 100) - $prediction['energy_cost'];
        if ($energyAfter < 30) {
            $score -= 20;
        }

        // Bonus for high total bonus multiplier
        $score = ($score ?? 0) + $prediction['total_bonus'] * 20;

        return $score;
    }

    /**
     * Generate human-readable recommendation reason
     *
     * @param  array<string, mixed>  $prediction
     * @param  array<string, int>  $priorityStats
     */
    protected function generateRecommendationReason(
        string $trainingType,
        array $prediction,
        array $priorityStats,
        Character $character
    ): string {
        $reasons = [];

        // Check which priority stats this training improves
        $improvedPriorityStats = [];
        foreach ($prediction['stat_gains'] as $stat => $gain) {
            if (isset($priorityStats[$stat]) && $priorityStats[$stat] > 0) {
                $improvedPriorityStats[] = ucfirst($stat);
            }
        }

        if (! empty($improvedPriorityStats)) {
            $reasons[] = 'Improves priority stats: '.implode(', ', $improvedPriorityStats);
        }

        // Mention bonus multiplier if significant
        if ($prediction['total_bonus'] > 0.3) {
            $bonusPercent = round($prediction['total_bonus'] * 100);
            $reasons[] = "High bonus multiplier (+{$bonusPercent}%)";
        }

        // Mention friendship training if applicable
        if (
            isset($prediction['breakdown']['friendship_multiplier']) &&
            $prediction['breakdown']['friendship_multiplier'] > 0
        ) {
            $reasons[] = 'Friendship training available';
        }

        // Warn about failure risk if high
        if ($prediction['failure_risk'] > 0.15) {
            $riskPercent = round($prediction['failure_risk'] * 100);
            $reasons[] = "Warning: {$riskPercent}% failure risk";
        }

        return ! empty($reasons)
            ? implode('. ', $reasons).'.'
            : 'Best option for current character state.';
    }

    /**
     * Calculate scenario-specific mechanics (URA Finale vs Unity Cup)
     *
     * @param  array<string, mixed>  $trainingData
     * @return array<string, mixed>
     */
    protected function calculateScenarioSpecificMechanics(): array
        return match ($character->scenario_type) {
            'unity_cup' => $this->calculateUnityCupMechanics(
                $character,
                $trainingType,
                $trainingData
            ),
            'ura_finale' => $this->calculateUraFinaleMechanics(
                $character,
                $trainingType,
                $trainingData
            ),
            default => [
                'scenario' => 'unknown',
                'mechanics' => [],
            ],
        };
    }

    /**
     * Calculate URA Finale specific mechanics
     * Focus: Individual character optimization with traditional training
     *
     * @param  array<string, mixed>  $trainingData
     * @return array<string, mixed>
     */
    protected function calculateUraFinaleMechanics(): array
        return [
            'scenario' => 'ura_finale',
            'optimization_focus' => 'individual',
            'mechanics' => [
                'traditional_training' => true,
                'individual_optimization' => true,
                'race_focus' => $this->getUraFinaleRaceFocus($character),
                'stat_priority' => $this->getUraFinaleStatPriority($character),
            ],
            'recommendations' => [
                'focus' => 'Maximize individual stats for race performance',
                'strategy' => 'Prioritize stat gaps and race requirements',
            ],
        ];
    }

    /**
     * Calculate Unity Cup specific mechanics
     * Focus: Team coordination, Spirit Burst, facility levels
     *
     * @param  array<string, mixed>  $trainingData
     * @return array<string, mixed>
     */
    protected function calculateUnityCupMechanics(): array
        // Calculate Spirit Burst mechanics
        $spiritBurst = $this->calculateSpiritBurstMechanics(
            $character,
            $trainingType,
            $trainingData
        );

        // Calculate team member interactions
        $teamInteractions = $this->calculateTeamMemberInteractions(
            $character,
            $trainingType,
            $trainingData
        );

        // Calculate distance team performance
        $distanceTeamPerformance = $this->calculateDistanceTeamPerformance(
            $character,
            $trainingData
        );

        return [
            'scenario' => 'unity_cup',
            'optimization_focus' => 'team',
            'mechanics' => [
                'spirit_burst' => $spiritBurst,
                'team_interactions' => $teamInteractions,
                'distance_team_performance' => $distanceTeamPerformance,
                'facility_levels' => $character->facility_levels ?? [],
            ],
            'recommendations' => [
                'focus' => 'Coordinate with team members for Spirit Burst',
                'strategy' => 'Balance individual stats with team synergy',
            ],
        ];
    }

    /**
     * Calculate Spirit Burst mechanics
     * 4 training sessions with teammates fill gauge for large stat bonuses + random skill hints
     *
     * @param  array<string, mixed>  $trainingData
     * @return array<string, mixed>
     */
    protected function calculateSpiritBurstMechanics(): array
        // Get current Spirit Burst gauge (0-4 sessions)
        $currentGauge = $trainingData['spirit_burst_gauge'] ?? 0;
        $teammatesPresent = $trainingData['teammates_present'] ?? [];

        // Check if this training will contribute to Spirit Burst gauge
        $willContribute = count($teammatesPresent) > 0;
        $gaugeAfterTraining = $willContribute ? min(4, $currentGauge + 1) : $currentGauge;

        // Check if Spirit Burst will trigger
        $willTrigger = $gaugeAfterTraining >= 4;

        // Calculate Spirit Burst bonuses if triggered
        $spiritBurstBonuses = [];
        if ($willTrigger) {
            $spiritBurstBonuses = [
                'stat_bonus' => [
                    'speed' => 50,
                    'stamina' => 50,
                    'power' => 50,
                    'guts' => 30,
                    'wit' => 30,
                ],
                'skill_hint_chance' => 0.8, // 80% chance for random skill hint
                'energy_recovery' => 20,
            ];
        }

        return [
            'enabled' => true,
            'current_gauge' => $currentGauge,
            'gauge_after_training' => $gaugeAfterTraining,
            'will_contribute' => $willContribute,
            'will_trigger' => $willTrigger,
            'sessions_until_trigger' => max(0, 4 - $gaugeAfterTraining),
            'bonuses' => $spiritBurstBonuses,
            'flame_icon_visible' => $willTrigger,
            'teammates_present' => $teammatesPresent,
        ];
    }

    /**
     * Calculate team member interaction effects
     * Unity training bonuses: 2 participants +2 bonus, 3 participants +3 bonus
     *
     * @param  array<string, mixed>  $trainingData
     * @return array<string, mixed>
     */
    protected function calculateTeamMemberInteractions(): array
        $teammatesPresent = $trainingData['teammates_present'] ?? [];
        $teammateCount = count($teammatesPresent);

        // Calculate Unity training bonus
        $unityBonus = match ($teammateCount) {
            2 => 2,
            3 => 3,
            default => 0,
        };

        // Calculate team synergy effects
        $teamSynergy = $this->calculateTeamSynergy(
            $character,
            $teammatesPresent,
            $trainingType
        );

        return [
            'teammates_present' => $teammatesPresent,
            'teammate_count' => $teammateCount,
            'unity_bonus' => $unityBonus,
            'team_synergy' => $teamSynergy,
            'coordination_level' => $this->getTeamCoordinationLevel($teammateCount),
        ];
    }

    /**
     * Calculate distance team performance tracking
     * 5 teams: Sprint (1000-1400m), Mile (1401-1800m), Medium (1801-2400m), Long (2401m+), Dirt
     *
     * @param  array<string, mixed>  $trainingData
     * @return array<string, mixed>
     */
    protected function calculateDistanceTeamPerformance(): array
        $distanceTeams = [
            'sprint' => ['distance_range' => '1000-1400m', 'members' => []],
            'mile' => ['distance_range' => '1401-1800m', 'members' => []],
            'medium' => ['distance_range' => '1801-2400m', 'members' => []],
            'long' => ['distance_range' => '2401m+', 'members' => []],
            'dirt' => ['distance_range' => 'All distances', 'members' => []],
        ];

        // Get character's primary distance specialization
        $characterDistance = $this->getCharacterDistanceSpecialization($character);

        // Calculate team stat ranks (D-S determining facility levels 1-5)
        $teamStatRanks = $trainingData['team_stat_ranks'] ?? [];

        // Calculate facility level impacts
        $facilityImpacts = [];
        foreach ($distanceTeams as $teamName => $teamData) {
            $statRank = $teamStatRanks[$teamName] ?? 'D';
            $facilityLevel = $this->convertStatRankToFacilityLevel($statRank);

            $facilityImpacts[$teamName] = [
                'stat_rank' => $statRank,
                'facility_level' => $facilityLevel,
                'bonus_multiplier' => ($facilityLevel - 1) * 0.25, // 0% to 100%
            ];
        }

        return [
            'distance_teams' => $distanceTeams,
            'character_specialization' => $characterDistance,
            'team_stat_ranks' => $teamStatRanks,
            'facility_impacts' => $facilityImpacts,
            'team_race_schedule' => 'Every 6 months',
        ];
    }

    /**
     * Get URA Finale race focus based on character aptitudes
     *
     * @return array<string, mixed>
     */
    protected function getUraFinaleRaceFocus(): array
        // Load aptitudes relationship
        $aptitudesCollection = $character->aptitudes;

        // Convert to associative array for easier access
        $aptitudes = [];
        foreach ($aptitudesCollection as $aptitude) {
            if ($aptitude->distance_type) {
                $aptitudes[$aptitude->distance_type] = (is_string($aptitude) ? (string) $aptitude : '')->grade;
            }
            if ($aptitude->surface_type) {
                $aptitudes[$aptitude->surface_type] = (is_string($aptitude) ? (string) $aptitude : '')->grade;
            }
        }

        // Analyze aptitudes to determine optimal race focus
        $bestDistance = 'mile'; // Default
        $bestSurface = 'turf'; // Default

        // Find best distance aptitude
        $distanceAptitudes = [
            'sprint' => $aptitudes['sprint'] ?? 'C',
            'mile' => $aptitudes['mile'] ?? 'C',
            'medium' => $aptitudes['medium'] ?? 'C',
            'long' => $aptitudes['long'] ?? 'C',
        ];

        arsort($distanceAptitudes);
        $bestDistance = (string) (array_key_first($distanceAptitudes) ?? 'mile');

        // Find best surface aptitude
        $surfaceAptitudes = [
            'turf' => $aptitudes['turf'] ?? 'C',
            'dirt' => $aptitudes['dirt'] ?? 'C',
        ];

        arsort($surfaceAptitudes);
        $bestSurface = (string) (array_key_first($surfaceAptitudes) ?? 'turf');

        return [
            'best_distance' => $bestDistance,
            'best_surface' => $bestSurface,
            'distance_aptitudes' => $distanceAptitudes,
            'surface_aptitudes' => $surfaceAptitudes,
        ];
    }

    /**
     * Get URA Finale stat priority based on character goals and aptitudes
     *
     * @return array<string, int>
     */
    protected function getUraFinaleStatPriority(): array
        $goals = $character->goals ?? [];
        $targetStats = $goals['target_stats'] ?? [];
        $currentStats = $character->current_stats ?? [];

        $priorities = [];
        foreach ($targetStats as $stat => $target) {
            $current = $currentStats[$stat] ?? 0;
            $gap = max(0, $target - $current);
            $priorities[$stat] = $gap;
        }

        // Sort by gap (highest first)
        arsort($priorities);

        return $priorities;
    }

    /**
     * Calculate team synergy effects
     *
     * @param  array<string, mixed>  $teammatesPresent
     * @return array<string, mixed>
     */
    protected function calculateTeamSynergy(): array
        if (empty($teammatesPresent)) {
            return [
                'level' => 'none',
                'bonus' => 0.0,
            ];
        }

        // Calculate synergy based on teammate count and training type match
        $synergyLevel = count($teammatesPresent) >= 2 ? 'high' : 'medium';
        $synergyBonus = count($teammatesPresent) * 0.05; // 5% per teammate

        return [
            'level' => $synergyLevel,
            'bonus' => $synergyBonus,
            'teammates' => $teammatesPresent,
        ];
    }

    /**
     * Get team coordination level based on teammate count
     */
    protected function getTeamCoordinationLevel(int $teammateCount): string
    {
        return match ($teammateCount) {
            3 => 'excellent',
            2 => 'good',
            1 => 'fair',
            default => 'none',
        };
    }

    /**
     * Get character's distance specialization based on aptitudes
     */
    protected function getCharacterDistanceSpecialization(Character $character): string
    {
        // Load aptitudes relationship
        $aptitudesCollection = $character->aptitudes;

        // Convert to associative array for easier access
        $aptitudes = [];
        foreach ($aptitudesCollection as $aptitude) {
            if ($aptitude->distance_type) {
                $aptitudes[$aptitude->distance_type] = (is_string($aptitude) ? (string) $aptitude : '')->grade;
            }
        }

        $distanceAptitudes = [
            'sprint' => $aptitudes['sprint'] ?? 'C',
            'mile' => $aptitudes['mile'] ?? 'C',
            'medium' => $aptitudes['medium'] ?? 'C',
            'long' => $aptitudes['long'] ?? 'C',
        ];

        arsort($distanceAptitudes);

        return (string) (array_key_first($distanceAptitudes) ?? 'mile');
    }

    /**
     * Convert stat rank (D-S) to facility level (1-5)
     */
    protected function convertStatRankToFacilityLevel(string $statRank): int
    {
        return match (strtoupper($statRank)) {
            'S' => 5,
            'A' => 4,
            'B' => 3,
            'C' => 2,
            'D' => 1,
            default => 1,
        };
    }
}
