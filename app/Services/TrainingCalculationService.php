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

    /**
     * Minimum number of rainbow (bond >= 80) cards required for Friendship Training activation
     */
    protected const FRIENDSHIP_CARD_THRESHOLD = 3;

    /**
     * Flat Friendship Training multiplier applied when the threshold is met
     */
    protected const FRIENDSHIP_TRAINING_MULTIPLIER = 1.2;

    /**
     * Rarity-based training stat bonus percentages (applied per matching-facility card)
     *
     * @var array<string, float>
     */
    protected array $rarityBonuses = [
        'SSR' => 0.10,
        'SR' => 0.07,
        'R' => 0.05,
    ];

    /**
     * Limit break multipliers applied to each card\'s base training contribution
     *
     * @var array<int, float>
     */
    protected array $limitBreakMultipliers = [
        0 => 1.0,
        1 => 1.1,
        2 => 1.2,
        3 => 1.3,
        4 => 1.4,
    ];

    public function __construct(MCPClientService $mcpClient, TrainingOptimizationAgent $trainingAgent)
    {
        $this->mcpClient = $mcpClient;
        $this->trainingAgent = $trainingAgent;
    }

    /**
     * Calculate predicted stat gains for a training session
     *
     * VERIFIED FORMULA (Jan 2026):
     * Stat Gain = (Base + StatBonus)
     *           × (1 + GrowthRate)
     *           × (1 + MoodMultiplier × (1 + MoodEffect))
     *           × (1 + TrainingEffect)
     *           × (1 + 0.05 × NumSupportCards)
     *           × FriendshipMultiplier
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
    public function calculateTrainingPrediction(Character $character, string $trainingType, array $trainingData = []): array
    {
        // Get base stat gains
        $baseGains = $this->baseStatGains[$trainingType] ?? [];

        // Extract and validate support cards
        $supportCardsRaw = $trainingData['support_cards'] ?? [];
        /** @var array<int, mixed> $supportCards */
        $supportCards = is_array($supportCardsRaw) ? $supportCardsRaw : [];

        // Calculate stat bonus from support cards
        $statBonus = $this->calculateStatBonus(
            $character,
            $trainingType,
            $supportCards
        );

        // Calculate growth rate multiplier (1 + GrowthRate)
        $growthRateMultiplier = $this->calculateGrowthRateMultiplier(
            $character,
            $trainingType
        );

        // Calculate mood multiplier (1 + MoodMultiplier × (1 + MoodEffect))
        $moodMultiplier = $this->calculateMoodMultiplier(
            $character
        );

        // Calculate training effect from support card traits
        $trainingEffect = $this->calculateTrainingEffect(
            $character,
            $trainingType,
            $supportCards
        );

        // Calculate support card presence bonus (+5% per card)
        $numSupportCards = count($supportCards);
        if (empty($supportCards)) {
            // If no support cards provided, get from character
            $numSupportCards = $character->supportCards()->count();
        }
        $supportCardPresenceMultiplier = 1.0 + (0.05 * min(6, $numSupportCards));

        // Extract and validate participants for friendship multiplier
        $participantsRaw = $trainingData['participants'] ?? 0;
        $participants = is_int($participantsRaw) ? $participantsRaw : (is_numeric($participantsRaw) ? (int) $participantsRaw : 0);

        // Calculate friendship multiplier (product of (1 + FriendshipBonus) for each rainbow card)
        $friendshipMultiplier = $this->calculateFriendshipMultiplierProduct(
            $character,
            $participants,
            $supportCards
        );

        // Calculate facility level bonus (Unity Cup) - applied to base
        $facilityBonus = $this->calculateFacilityBonus(
            $character,
            $trainingType
        );

        // Apply verified multiplicative formula
        $finalGains = [];
        foreach ($baseGains as $stat => $baseGain) {
            // (Base + StatBonus)
            $baseWithBonus = $baseGain + ($statBonus[$stat] ?? 0);

            // Apply facility bonus to base (Unity Cup specific)
            if ($facilityBonus > 0) {
                $baseWithBonus = (int) round($baseWithBonus * (1.0 + $facilityBonus));
            }

            // Apply multiplicative formula
            $gain = $baseWithBonus
                * $growthRateMultiplier
                * $moodMultiplier
                * (1.0 + $trainingEffect)
                * $supportCardPresenceMultiplier
                * $friendshipMultiplier;

            // Apply per-training cap (+100 max, +50 if stat > 1200)
            $currentStatRaw = $character->current_stats[$stat] ?? 0;
            $currentStat = is_numeric($currentStatRaw) ? (int) $currentStatRaw : 0;
            $maxGain = $currentStat > 1200 ? 50 : 100;

            $finalGains[$stat] = (int) min($maxGain, round($gain));
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

        // Calculate total effective multiplier for display
        $totalMultiplier = $growthRateMultiplier
            * $moodMultiplier
            * (1.0 + $trainingEffect)
            * $supportCardPresenceMultiplier
            * $friendshipMultiplier;

        return [
            'stat_gains' => $finalGains,
            'energy_cost' => $energyCost,
            'failure_risk' => $failureRisk,
            'total_bonus' => $totalMultiplier - 1.0,
            'breakdown' => [
                'base_gains' => $baseGains,
                'stat_bonus' => $statBonus,
                'growth_rate_multiplier' => $growthRateMultiplier,
                'mood_multiplier' => $moodMultiplier,
                'training_effect' => $trainingEffect,
                'support_card_presence_multiplier' => $supportCardPresenceMultiplier,
                'friendship_multiplier' => $friendshipMultiplier,
                'facility_bonus' => $facilityBonus,
                'total_multiplier' => $totalMultiplier,
                'per_training_cap' => 'Applied: +100 max (+50 if stat > 1200)',
            ],
            'scenario_specific' => $scenarioSpecific,
        ];
    }

    /**
     * Calculate stat bonus from support cards (added to base)
     *
     * @param  array<int, mixed>  $supportCards
     * @return array<string, int>
     */
    protected function calculateStatBonus(
        Character $character,
        string $trainingType,
        array $supportCards = []
    ): array {
        $statBonus = [];

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
                // Stat bonus from "Stat Bonus" trait on support cards
                // This is a flat bonus added to base before multipliers
                $bonusValue = 2; // Base stat bonus per matching card

                // Additional bonus based on limit break level
                if (is_object($characterCard) && isset($characterCard->limit_break_level)) {
                    $limitBreakLevel = $characterCard->limit_break_level;
                    if (is_numeric($limitBreakLevel)) {
                        $bonusValue += (int) $limitBreakLevel;
                    }
                } elseif (is_array($characterCard) && isset($characterCard['limit_break_level'])) {
                    $limitBreakLevel = $characterCard['limit_break_level'];
                    if (is_numeric($limitBreakLevel)) {
                        $bonusValue += (int) $limitBreakLevel;
                    }
                }

                // Add to primary stat
                $statBonus[$trainingType] = ($statBonus[$trainingType] ?? 0) + $bonusValue;
            }
        }

        return $statBonus;
    }

    /**
     * Calculate growth rate multiplier (1 + GrowthRate)
     * VERIFIED: Growth rates are character-specific innate bonuses
     */
    protected function calculateGrowthRateMultiplier(
        Character $character,
        string $trainingType
    ): float {
        $growthRatesRaw = $character->growth_rates ?? [];
        /** @var array<string, mixed> $growthRates */
        $growthRates = is_array($growthRatesRaw) ? $growthRatesRaw : [];
        $rateRaw = $growthRates[$trainingType] ?? 0;
        $rate = is_numeric($rateRaw) ? (float) $rateRaw : 0.0;

        // Growth rates are stored as percentages (10, 20, 30)
        return 1.0 + ($rate / 100.0);
    }

    /**
     * Calculate mood multiplier (1 + MoodMultiplier × (1 + MoodEffect))
     * VERIFIED: ±2% per mood level from neutral
     */
    protected function calculateMoodMultiplier(Character $character): float
    {
        // Mood effect: ±2% per mood level from neutral
        $moodEffect = match ($character->mood_status) {
            'great' => 0.04,    // +4% (2 levels above neutral)
            'good' => 0.02,     // +2% (1 level above neutral)
            'normal' => 0.0,    // 0% (neutral)
            'bad' => -0.02,     // -2% (1 level below neutral)
            'awful' => -0.04,   // -4% (2 levels below neutral)
            default => 0.0,
        };

        // Base mood multiplier (typically 1.0 unless modified by conditions)
        $baseMoodMultiplier = 1.0;

        // Formula: (1 + MoodMultiplier × (1 + MoodEffect))
        return 1.0 + ($baseMoodMultiplier * $moodEffect);
    }

    /**
     * Calculate training effect from support card traits, incorporating rarity bonuses
     * and limit break multipliers per the docs:
     * - SSR: +10%, SR: +7%, R: +5% per matching-facility card
     * - Limit break multipliers: LB0 = 1.0x, LB1 = 1.1x, LB2 = 1.2x, LB3 = 1.3x, LB4 = 1.4x
     *
     * @param  array<int, mixed>  $supportCards
     */
    protected function calculateTrainingEffect(
        Character $character,
        string $trainingType,
        array $supportCards = []
    ): float {
        $trainingEffect = 0.0;

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
                // Determine rarity bonus for matching card
                $rarity = is_array($card) && isset($card['rarity'])
                    ? $card['rarity']
                    : (is_object($card) && isset($card->rarity) ? $card->rarity : null);

                $rarityBonus = $this->rarityBonuses[strtoupper((string) ($rarity ?? ''))] ?? 0.05;

                // Determine limit break multiplier
                $rawLb = null;
                if (is_object($characterCard) && isset($characterCard->limit_break_level)) {
                    $rawLb = $characterCard->limit_break_level;
                } elseif (is_array($characterCard) && isset($characterCard['limit_break_level'])) {
                    $rawLb = $characterCard['limit_break_level'];
                }

                $limitBreakLevel = is_numeric($rawLb) ? min(4, max(0, (int) $rawLb)) : 0;
                $lbMultiplier = $this->limitBreakMultipliers[$limitBreakLevel] ?? 1.0;

                $trainingEffect += $rarityBonus * $lbMultiplier;
            }
        }

        return $trainingEffect;
    }

    /**
     * Calculate friendship multiplier using a flat 1.2x when 3+ cards have bond >= 80.
     * Per docs: Friendship Training activates when 3 or more support cards reach bond level 80,
     * granting a flat 20% bonus (1.2x) to all training stat gains.
     *
     * @param  array<int, mixed>  $supportCards
     */
    protected function calculateFriendshipMultiplierProduct(
        Character $character,
        int $participants,
        array $supportCards = []
    ): float {
        // If no support cards provided, get from character
        if (empty($supportCards)) {
            $supportCards = $character->supportCards()->with('supportCard')->get();
        }

        $rainbowCardCount = 0;
        foreach ($supportCards as $characterCard) {
            $bondLevel = 0;
            if (is_object($characterCard) && isset($characterCard->friendship_level)) {
                $bondLevel = is_numeric($characterCard->friendship_level) ? (int) $characterCard->friendship_level : 0;
            } elseif (is_object($characterCard) && isset($characterCard->bond_level)) {
                $bondLevel = is_numeric($characterCard->bond_level) ? (int) $characterCard->bond_level : 0;
            } elseif (is_array($characterCard) && isset($characterCard['friendship_level'])) {
                $bondLevel = is_numeric($characterCard['friendship_level']) ? (int) $characterCard['friendship_level'] : 0;
            } elseif (is_array($characterCard) && isset($characterCard['bond_level'])) {
                $bondLevel = is_numeric($characterCard['bond_level']) ? (int) $characterCard['bond_level'] : 0;
            }

            if ($bondLevel >= 80) {
                $rainbowCardCount++;
            }
        }

        // Friendship Training requires 3+ cards simultaneously at bond >= 80 (flat 1.2x multiplier)
        if ($rainbowCardCount >= self::FRIENDSHIP_CARD_THRESHOLD) {
            return self::FRIENDSHIP_TRAINING_MULTIPLIER;
        }

        return 1.0;
    }

    /**
     * Calculate facility level bonus (Unity Cup)
     * Facility levels 1-5 provide 1.0x to 2.0x multipliers
     * VERIFIED: Applied to base value before other multipliers
     */
    protected function calculateFacilityBonus(
        Character $character,
        string $trainingType
    ): float {
        // Only applies to Unity Cup scenario
        if ($character->scenario_type !== 'unity_cup') {
            return 0.0;
        }

        $facilityLevelsRaw = $character->facility_levels ?? [];
        /** @var array<string, mixed> $facilityLevels */
        $facilityLevels = is_array($facilityLevelsRaw) ? $facilityLevelsRaw : [];
        $levelRaw = $facilityLevels[$trainingType] ?? 1;
        $level = is_numeric($levelRaw) ? (int) $levelRaw : 1;

        // Facility level bonus: Level 1 = 0%, Level 5 = 100%
        return ($level - 1) * 0.25; // 0%, 25%, 50%, 75%, 100%
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
    public function calculateBatchPredictions(Character $character, array $trainingTypes, array $trainingData = []): array
    {
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
        $result = Cache::remember($cacheKey, 300, function () use ($character, $context): ?array {
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

        /** @var array<string, mixed>|null $result */
        return is_array($result) ? $result : null;
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
    public function getRecommendedTraining(Character $character, array $trainingData = []): array
    {
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
        if ($recommendedTraining === null) {
            $recommendedTraining = 'speed'; // Default fallback
        }
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
    protected function getPriorityStats(Character $character): array
    {
        /** @var array<string, int> $priorities */
        $priorities = [];
        $goalsRaw = $character->goals ?? [];
        /** @var array<string, mixed> $goals */
        $goals = is_array($goalsRaw) ? $goalsRaw : [];
        $targetStatsRaw = $goals['target_stats'] ?? [];
        /** @var array<string, mixed> $targetStats */
        $targetStats = is_array($targetStatsRaw) ? $targetStatsRaw : [];
        $currentStatsRaw = $character->current_stats ?? [];
        /** @var array<string, mixed> $currentStats */
        $currentStats = is_array($currentStatsRaw) ? $currentStatsRaw : [];

        foreach ($targetStats as $stat => $target) {
            if (! is_string($stat)) {
                continue;
            }
            $targetVal = is_numeric($target) ? (int) $target : 0;
            $currentVal = isset($currentStats[$stat]) && is_numeric($currentStats[$stat]) ? (int) $currentStats[$stat] : 0;
            $gap = max(0, $targetVal - $currentVal);
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
        $statGainsRaw = $prediction['stat_gains'] ?? [];
        /** @var array<string, int> $statGains */
        $statGains = is_array($statGainsRaw) ? $statGainsRaw : [];
        foreach ($statGains as $stat => $gain) {
            if (! is_string($stat)) {
                continue;
            }
            $gainVal = is_numeric($gain) ? (float) $gain : 0.0;
            $priority = $priorityStats[$stat] ?? 0;
            if ($priority > 0) {
                // Higher priority stats get more weight
                $weight = $priority / max(1, array_sum($priorityStats));
                $score += $gainVal * $weight * 10;
            }
        }

        // Penalize high failure risk
        $failureRisk = isset($prediction['failure_risk']) && is_numeric($prediction['failure_risk'])
            ? (float) $prediction['failure_risk']
            : 0.0;
        $score -= $failureRisk * 50;

        // Penalize if energy would drop too low
        $energyLevel = $character->energy_level ?? 100;
        $energyCost = isset($prediction['energy_cost']) && is_numeric($prediction['energy_cost'])
            ? (int) $prediction['energy_cost']
            : 0;
        $energyAfter = (is_numeric($energyLevel) ? (int) $energyLevel : 100) - $energyCost;
        if ($energyAfter < 30) {
            $score -= 20;
        }

        // Bonus for high total bonus multiplier
        $totalBonus = isset($prediction['total_bonus']) && is_numeric($prediction['total_bonus'])
            ? (float) $prediction['total_bonus']
            : 0.0;
        $score += $totalBonus * 20;

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
        $statGainsRaw = $prediction['stat_gains'] ?? [];
        /** @var array<string, mixed> $statGains */
        $statGains = is_array($statGainsRaw) ? $statGainsRaw : [];
        foreach ($statGains as $stat => $gain) {
            if (! is_string($stat)) {
                continue;
            }
            if (isset($priorityStats[$stat]) && $priorityStats[$stat] > 0) {
                $improvedPriorityStats[] = ucfirst($stat);
            }
        }

        if (! empty($improvedPriorityStats)) {
            $reasons[] = 'Improves priority stats: '.implode(', ', $improvedPriorityStats);
        }

        // Mention bonus multiplier if significant
        $totalBonus = isset($prediction['total_bonus']) && is_numeric($prediction['total_bonus'])
            ? (float) $prediction['total_bonus']
            : 0.0;
        if ($totalBonus > 0.3) {
            $bonusPercent = round($totalBonus * 100);
            $reasons[] = "High bonus multiplier (+{$bonusPercent}%)";
        }

        // Mention friendship training if applicable
        $breakdownRaw = $prediction['breakdown'] ?? [];
        /** @var array<string, mixed> $breakdown */
        $breakdown = is_array($breakdownRaw) ? $breakdownRaw : [];
        $friendshipMultiplier = isset($breakdown['friendship_multiplier']) && is_numeric($breakdown['friendship_multiplier'])
            ? (float) $breakdown['friendship_multiplier']
            : 0.0;
        if ($friendshipMultiplier > 0) {
            $reasons[] = 'Friendship training available';
        }

        // Warn about failure risk if high
        $failureRisk = isset($prediction['failure_risk']) && is_numeric($prediction['failure_risk'])
            ? (float) $prediction['failure_risk']
            : 0.0;
        if ($failureRisk > 0.15) {
            $riskPercent = round($failureRisk * 100);
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
    protected function calculateScenarioSpecificMechanics(Character $character, string $trainingType, array $trainingData = []): array
    {
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
    protected function calculateUraFinaleMechanics(Character $character, string $trainingType, array $trainingData = []): array
    {
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
    protected function calculateUnityCupMechanics(Character $character, string $trainingType, array $trainingData = []): array
    {
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
    protected function calculateSpiritBurstMechanics(Character $character, string $trainingType, array $trainingData = []): array
    {
        // Get current Spirit Burst gauge (0-4 sessions)
        $currentGaugeRaw = $trainingData['spirit_burst_gauge'] ?? 0;
        $currentGauge = is_numeric($currentGaugeRaw) ? (int) $currentGaugeRaw : 0;
        $teammatesPresentRaw = $trainingData['teammates_present'] ?? [];
        /** @var array<string, mixed> $teammatesPresent */
        $teammatesPresent = is_array($teammatesPresentRaw) ? $teammatesPresentRaw : [];

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
    protected function calculateTeamMemberInteractions(Character $character, string $trainingType, array $trainingData = []): array
    {
        $teammatesPresentRaw = $trainingData['teammates_present'] ?? [];
        /** @var array<string, mixed> $teammatesPresent */
        $teammatesPresent = is_array($teammatesPresentRaw) ? $teammatesPresentRaw : [];
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
    protected function calculateDistanceTeamPerformance(Character $character, array $trainingData = []): array
    {
        /** @var array<string, array{distance_range: string, members: array<mixed>}> $distanceTeams */
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
        $teamStatRanksRaw = $trainingData['team_stat_ranks'] ?? [];
        /** @var array<string, string> $teamStatRanks */
        $teamStatRanks = is_array($teamStatRanksRaw) ? $teamStatRanksRaw : [];

        // Calculate facility level impacts
        $facilityImpacts = [];
        foreach ($distanceTeams as $teamName => $teamData) {
            $statRankRaw = $teamStatRanks[$teamName] ?? 'D';
            $statRank = is_string($statRankRaw) ? $statRankRaw : 'D';
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
    protected function getUraFinaleRaceFocus(Character $character): array
    {
        // Load aptitudes relationship
        $aptitudesCollection = $character->aptitudes;

        // Convert to associative array for easier access
        $aptitudes = [];
        foreach ($aptitudesCollection as $aptitude) {
            $grade = $aptitude->grade ?? 'C';
            if ($aptitude->distance_type) {
                $aptitudes[$aptitude->distance_type] = $grade;
            }
            if ($aptitude->surface_type) {
                $aptitudes[$aptitude->surface_type] = $grade;
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
    protected function getUraFinaleStatPriority(Character $character): array
    {
        /** @var array<string, int> $priorities */
        $priorities = [];
        $goalsRaw = $character->goals ?? [];
        /** @var array<string, mixed> $goals */
        $goals = is_array($goalsRaw) ? $goalsRaw : [];
        $targetStatsRaw = $goals['target_stats'] ?? [];
        /** @var array<string, mixed> $targetStats */
        $targetStats = is_array($targetStatsRaw) ? $targetStatsRaw : [];
        $currentStatsRaw = $character->current_stats ?? [];
        /** @var array<string, mixed> $currentStats */
        $currentStats = is_array($currentStatsRaw) ? $currentStatsRaw : [];

        foreach ($targetStats as $stat => $target) {
            if (! is_string($stat)) {
                continue;
            }
            $targetVal = is_numeric($target) ? (int) $target : 0;
            $currentVal = isset($currentStats[$stat]) && is_numeric($currentStats[$stat]) ? (int) $currentStats[$stat] : 0;
            $gap = max(0, $targetVal - $currentVal);
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
    protected function calculateTeamSynergy(Character $character, array $teammatesPresent, string $trainingType): array
    {
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
            $grade = $aptitude->grade ?? 'C';
            if ($aptitude->distance_type) {
                $aptitudes[$aptitude->distance_type] = $grade;
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
