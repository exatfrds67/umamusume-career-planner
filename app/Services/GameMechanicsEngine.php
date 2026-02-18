<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Mood;
use App\Enums\RaceDistance;
use App\Enums\RunningStyle;
use App\Models\Race;
use App\ValueObjects\CharacterStats;

/**
 * Game Mechanics Engine
 *
 * Encapsulates all game formula calculations for Umamusume Pretty Derby.
 * This service provides accurate implementations of training gains, stamina requirements,
 * skill costs, failure rates, stat effectiveness, win probabilities, and other core
 * game mechanics.
 *
 * @see \App\Services\TrainingAdvisoryService
 * @see \App\Services\RuleBasedAdvisor
 */
class GameMechanicsEngine
{
    /**
     * Calculate training stat gain
     *
     * Calculates the expected stat gain from a training session based on:
     * - Base stat value
     * - Facility level (1-5, provides multiplier)
     * - Character growth rate (stat-specific multiplier)
     * - Current mood (affects training effectiveness)
     * - Support card bonuses (stat-specific bonuses from cards)
     * - Number of support cards present (affects multi-training bonus)
     * - Whether this is friendship training (bond ≥80)
     *
     * Formula incorporates:
     * - Facility level multiplier (1.0 + (level - 1) * 0.05)
     * - Growth rate multiplier
     * - Mood effectiveness modifier
     * - Support card stat bonuses
     * - Multi-training bonus (+5% per card, max +30%)
     * - Friendship training bonus (additional multiplier)
     *
     * @param  int  $baseStat  Current stat value (0-1200+)
     * @param  int  $facilityLevel  Training facility level (1-5)
     * @param  float  $growthRate  Character's growth rate for this stat (0.8-1.2 typical)
     * @param  Mood  $mood  Character's current mood state
     * @param  array<string, int>  $supportCardBonuses  Stat bonuses from support cards
     * @param  int  $numCardsPresent  Number of support cards at this facility
     * @param  bool  $isFriendshipTraining  Whether friendship training is active (bond ≥80)
     * @return int Calculated stat gain
     */
    public function calculateTrainingGain(
        int $baseStat,
        int $facilityLevel,
        float $growthRate,
        Mood $mood,
        array $supportCardBonuses,
        int $numCardsPresent,
        bool $isFriendshipTraining
    ): int {
        // Base training gain (typically 30-50 depending on facility)
        // Using 40 as a reasonable base value
        $baseGain = 40;

        // Apply facility level multiplier (1.0 + (level - 1) * 0.05)
        // Level 1: 1.00x, Level 2: 1.05x, Level 3: 1.10x, Level 4: 1.15x, Level 5: 1.20x
        $facilityMultiplier = 1.0 + (($facilityLevel - 1) * 0.05);

        // Apply growth rate (character-specific stat affinity)
        $growthMultiplier = $growthRate;

        // Apply mood effectiveness modifier
        $moodMultiplier = $mood->effectivenessMultiplier();

        // Calculate support card bonus (sum of all stat bonuses)
        $supportBonus = array_sum($supportCardBonuses);

        // Apply multi-training bonus (+5% per card, max +30%)
        $multiTrainingBonus = $this->calculateMultiTrainingBonus($numCardsPresent);

        // Apply friendship training bonus (typically +10-15% when bond ≥80)
        $friendshipMultiplier = $isFriendshipTraining ? 1.15 : 1.0;

        // Calculate final gain
        // Formula: (baseGain * facilityMultiplier * growthMultiplier * moodMultiplier * (1 + multiTrainingBonus) * friendshipMultiplier) + supportBonus
        $calculatedGain = (
            $baseGain
            * $facilityMultiplier
            * $growthMultiplier
            * $moodMultiplier
            * (1.0 + $multiTrainingBonus)
            * $friendshipMultiplier
        ) + $supportBonus;

        // Round to nearest integer
        return (int) round($calculatedGain);
    }

    /**
     * Calculate stamina requirement for a race
     *
     * Calculates the minimum stamina required to complete a race based on:
     * - Race distance (Sprint/Mile/Medium/Long)
     * - Running style (Escape/Lead/Pace/Chase)
     * - Recovery skills equipped (reduce requirements)
     *
     * Base requirements by distance:
     * - Sprint: 350-400
     * - Mile: 450-500
     * - Medium: 600-700
     * - Long: 850-1000
     *
     * Running style modifiers:
     * - Escape: Highest stamina requirement (1.0x)
     * - Lead: High stamina requirement (0.95x)
     * - Pace: Moderate stamina requirement (0.85x)
     * - Chase: Lower stamina requirement (0.75x)
     *
     * Recovery skills reduce requirements by 150-200 per gold skill.
     *
     * @param  RaceDistance  $distance  Race distance category
     * @param  RunningStyle  $style  Character's running style
     * @param  array<int>  $recoverySkills  Array of recovery skill IDs equipped
     * @return int Minimum stamina required
     */
    public function calculateStaminaRequirement(
        RaceDistance $distance,
        RunningStyle $style,
        array $recoverySkills
    ): int {
        // Get base stamina requirement for the distance (using recommended midpoint)
        // This gives us the baseline for Escape style
        $baseRequirement = $distance->recommendedStamina();

        // Apply running style multiplier
        // Escape: 1.0x (baseline), Lead: 0.95x, Pace: 0.85x, Chase: 0.75x
        $styleMultiplier = $style->staminaMultiplier();
        $adjustedRequirement = (int) round($baseRequirement * $styleMultiplier);

        // Apply recovery skill reductions
        // Each gold recovery skill reduces requirement by 150-200
        // Using 175 as the midpoint value
        $recoverySkillCount = count($recoverySkills);
        $recoveryReduction = $recoverySkillCount * 175;

        // Calculate final requirement (cannot go below 0)
        $finalRequirement = max(0, $adjustedRequirement - $recoveryReduction);

        return $finalRequirement;
    }

    /**
     * Calculate skill purchase cost with discounts
     *
     * Calculates the actual SP cost to purchase a skill based on:
     * - Base skill cost
     * - Hint level (0-5, provides progressive discounts)
     * - Fast Learner trait (additional discount)
     *
     * Hint level discounts:
     * - Level 0: 0% discount
     * - Level 1: 10% discount
     * - Level 2: 20% discount
     * - Level 3: 30% discount
     * - Level 4: 35% discount
     * - Level 5: 40% discount (max)
     *
     * Fast Learner provides an additional discount on top of hint discounts.
     *
     * @param  int  $baseCost  Base SP cost of the skill
     * @param  int  $hintLevel  Hint level (0-5)
     * @param  bool  $hasFastLearner  Whether character has Fast Learner trait
     * @return int Final SP cost after discounts
     */
    public function calculateSkillCost(
        int $baseCost,
        int $hintLevel,
        bool $hasFastLearner
    ): int {
        // Define hint level discount percentages
        $hintDiscounts = [
            0 => 0.00,  // No hint: 0% discount
            1 => 0.10,  // Level 1: 10% discount
            2 => 0.20,  // Level 2: 20% discount
            3 => 0.30,  // Level 3: 30% discount
            4 => 0.35,  // Level 4: 35% discount
            5 => 0.40,  // Level 5: 40% discount (max)
        ];

        // Clamp hint level to valid range (0-5)
        $hintLevel = max(0, min(5, $hintLevel));

        // Get the discount for this hint level
        $hintDiscount = $hintDiscounts[$hintLevel];

        // Apply hint level discount
        $costAfterHint = $baseCost * (1.0 - $hintDiscount);

        // Apply Fast Learner discount if applicable
        // Fast Learner provides an additional 10% discount
        if ($hasFastLearner) {
            $costAfterHint *= 0.90;
        }

        // Round to nearest integer
        return (int) round($costAfterHint);
    }

    /**
     * Calculate training failure rate
     *
     * Calculates the probability of training failure based on:
     * - Current energy level (lower energy = higher failure rate)
     * - Number of support cards present (more cards = lower failure rate)
     * - Active conditions (injuries, poor mood, etc.)
     *
     * Energy thresholds:
     * - 70+: Very low failure rate (~1-2%)
     * - 50-69: Low failure rate (~3-5%)
     * - 30-49: Moderate failure rate (~8-12%)
     * - <30: High failure rate (~15-25%)
     *
     * Support cards reduce failure rate by ~1% per card.
     * Negative conditions increase failure rate.
     *
     * @param  int  $energy  Current energy level (0-100)
     * @param  int  $numSupportCards  Number of support cards at facility
     * @param  array<string>  $conditions  Active negative conditions
     * @return float Failure rate as decimal (0.0-1.0)
     */
    public function calculateFailureRate(
        int $energy,
        int $numSupportCards,
        array $conditions
    ): float {
        // Calculate base failure rate from energy level
        $baseRate = $this->calculateBaseFailureRateFromEnergy($energy);

        // Reduce failure rate by support card presence (~1% per card)
        $supportCardReduction = $numSupportCards * 0.01;

        // Increase failure rate by negative conditions
        $conditionModifier = $this->calculateConditionModifier($conditions);

        // Calculate final failure rate
        $failureRate = $baseRate - $supportCardReduction + $conditionModifier;

        // Clamp between 0.0 and 1.0
        return max(0.0, min(1.0, $failureRate));
    }

    /**
     * Calculate base failure rate from energy level
     *
     * Energy thresholds:
     * - 70+: Very low failure rate (1.5% midpoint)
     * - 50-69: Low failure rate (4% midpoint)
     * - 30-49: Moderate failure rate (10% midpoint)
     * - <30: High failure rate (20% midpoint)
     *
     * @param  int  $energy  Current energy level (0-100)
     * @return float Base failure rate as decimal
     */
    private function calculateBaseFailureRateFromEnergy(int $energy): float
    {
        if ($energy >= 70) {
            // Very low failure rate: 1-2% (using 1.5% midpoint)
            return 0.015;
        }

        if ($energy >= 50) {
            // Low failure rate: 3-5% (using 4% midpoint)
            return 0.04;
        }

        if ($energy >= 30) {
            // Moderate failure rate: 8-12% (using 10% midpoint)
            return 0.10;
        }

        // High failure rate: 15-25% (using 20% midpoint)
        return 0.20;
    }

    /**
     * Calculate failure rate modifier from negative conditions
     *
     * Each negative condition increases failure rate by a specific amount:
     * - 'injury': +5% failure rate
     * - 'poor_health': +3% failure rate
     * - 'overworked': +2% failure rate
     *
     * @param  array<string>  $conditions  Active negative conditions
     * @return float Total condition modifier
     */
    private function calculateConditionModifier(array $conditions): float
    {
        $modifier = 0.0;

        foreach ($conditions as $condition) {
            $modifier += match ($condition) {
                'injury' => 0.05,
                'poor_health' => 0.03,
                'overworked' => 0.02,
                default => 0.0,
            };
        }

        return $modifier;
    }

    /**
     * Calculate effective stat value with soft cap
     *
     * Calculates the effective value of a stat considering the 1200 soft cap.
     * Stats above 1200 count as half their value for effectiveness calculations.
     *
     * Examples:
     * - 800 → 800 (no cap)
     * - 1200 → 1200 (at cap)
     * - 1300 → 1250 (1200 + (100 / 2))
     * - 1400 → 1300 (1200 + (200 / 2))
     *
     * @param  int  $statValue  Raw stat value
     * @return int Effective stat value after soft cap
     */
    public function calculateStatEffectiveness(
        int $statValue
    ): int {
        // Soft cap threshold
        $softCap = 1200;

        // If stat is at or below the soft cap, return as-is
        if ($statValue <= $softCap) {
            return $statValue;
        }

        // Calculate the amount above the soft cap
        $amountAboveCap = $statValue - $softCap;

        // Values above 1200 count as half their value
        $effectiveAboveCap = (int) round($amountAboveCap / 2);

        // Return soft cap + half of the excess
        return $softCap + $effectiveAboveCap;
    }

    /**
     * Calculate race win probability
     *
     * Calculates the probability of winning a race based on:
     * - Character stats (speed, stamina, power, guts, wisdom)
     * - Race requirements (distance, surface, competition level)
     * - Equipped skills (race-specific bonuses)
     *
     * Considers:
     * - Stat effectiveness (with 1200 soft cap)
     * - Aptitude grades for distance/surface/style
     * - Stamina sufficiency
     * - Skill synergies
     * - Competition level
     *
     * @param  CharacterStats  $stats  Character's current stats
     * @param  Race  $race  Race details and requirements
     * @param  array<int>  $skills  Array of equipped skill IDs
     * @return float Win probability as decimal (0.0-1.0)
     */
    public function calculateWinProbability(
        CharacterStats $stats,
        Race $race,
        array $skills
    ): float {
        // Calculate effective stats (applying 1200 soft cap)
        $effectiveSpeed = $this->calculateStatEffectiveness($stats->speed);
        $effectiveStamina = $this->calculateStatEffectiveness($stats->stamina);
        $effectivePower = $this->calculateStatEffectiveness($stats->power);
        $effectiveGuts = $this->calculateStatEffectiveness($stats->guts);
        $effectiveWisdom = $this->calculateStatEffectiveness($stats->wisdom);

        // Get race requirements
        // For now, we'll use basic thresholds based on race distance
        // In a full implementation, these would come from the Race model
        $requiredSpeed = $this->getRequiredSpeed($race);
        $requiredStamina = $this->getRequiredStamina($race);
        $requiredPower = $this->getRequiredPower($race);
        $requiredGuts = $this->getRequiredGuts($race);

        // Calculate stat ratios (how well character meets requirements)
        // Ratio > 1.0 means character exceeds requirements
        $speedRatio = $requiredSpeed > 0 ? $effectiveSpeed / $requiredSpeed : 1.0;
        $staminaRatio = $requiredStamina > 0 ? $effectiveStamina / $requiredStamina : 1.0;
        $powerRatio = $requiredPower > 0 ? $effectivePower / $requiredPower : 1.0;
        $gutsRatio = $requiredGuts > 0 ? $effectiveGuts / $requiredGuts : 1.0;

        // Weight the stats based on their importance for racing
        // Speed: 40%, Stamina: 30%, Power: 15%, Guts: 15%
        $weightedRatio = ($speedRatio * 0.40)
            + ($staminaRatio * 0.30)
            + ($powerRatio * 0.15)
            + ($gutsRatio * 0.15);

        // Apply skill bonuses
        // Each skill provides approximately 2-5% win probability boost
        $skillBonus = \count($skills) * 0.03; // 3% per skill on average

        // Calculate base probability from weighted ratio
        // Using a sigmoid-like curve to map ratio to probability
        // Ratio of 1.0 (meeting requirements) = ~50% win probability
        // Ratio of 1.2 (20% above requirements) = ~70% win probability
        // Ratio of 0.8 (20% below requirements) = ~30% win probability
        $baseProbability = $this->calculateProbabilityFromRatio($weightedRatio);

        // Add skill bonus
        $finalProbability = $baseProbability + $skillBonus;

        // Clamp between 0.0 and 1.0
        return max(0.0, min(1.0, $finalProbability));
    }

    /**
     * Get required speed for a race
     *
     * @param  Race  $race  Race details
     * @return int Required speed stat
     */
    private function getRequiredSpeed(Race $race): int
    {
        // Base speed requirements by distance
        // Short: 800, Mile: 850, Intermediate: 900, Long: 950
        $distanceCategory = $race->distance_category ?? 'intermediate';

        return match ($distanceCategory) {
            'short' => 800,
            'mile' => 850,
            'intermediate' => 900,
            'long' => 950,
            default => 900,
        };
    }

    /**
     * Get required stamina for a race
     *
     * @param  Race  $race  Race details
     * @return int Required stamina stat
     */
    private function getRequiredStamina(Race $race): int
    {
        // Base stamina requirements by distance
        // Short: 350, Mile: 450, Intermediate: 650, Long: 900
        $distanceCategory = $race->distance_category ?? 'intermediate';

        return match ($distanceCategory) {
            'short' => 350,
            'mile' => 450,
            'intermediate' => 650,
            'long' => 900,
            default => 650,
        };
    }

    /**
     * Get required power for a race
     *
     * @param  Race  $race  Race details
     * @return int Required power stat
     */
    private function getRequiredPower(Race $race): int
    {
        // Base power requirements
        // Power is important for acceleration and uphill sections
        return 700;
    }

    /**
     * Get required guts for a race
     *
     * @param  Race  $race  Race details
     * @return int Required guts stat
     */
    private function getRequiredGuts(Race $race): int
    {
        // Base guts requirements
        // Guts is important for late-race performance
        return 600;
    }

    /**
     * Calculate probability from stat ratio using sigmoid-like curve
     *
     * Maps stat ratio to win probability:
     * - Ratio 0.5 (50% of requirements) → ~10% probability
     * - Ratio 0.8 (80% of requirements) → ~30% probability
     * - Ratio 1.0 (100% of requirements) → ~50% probability
     * - Ratio 1.2 (120% of requirements) → ~70% probability
     * - Ratio 1.5 (150% of requirements) → ~85% probability
     *
     * @param  float  $ratio  Weighted stat ratio
     * @return float Probability as decimal (0.0-1.0)
     */
    private function calculateProbabilityFromRatio(float $ratio): float
    {
        // Using a modified sigmoid function
        // P(x) = 1 / (1 + e^(-k * (x - x0)))
        // where k controls steepness and x0 is the midpoint
        $k = 5.0; // Steepness factor
        $x0 = 1.0; // Midpoint (ratio of 1.0 = 50% probability)

        $probability = 1.0 / (1.0 + exp(-$k * ($ratio - $x0)));

        return $probability;
    }

    /**
     * Calculate multi-training bonus
     *
     * Calculates the stat gain bonus from multiple support cards being present
     * at the same training facility.
     *
     * Bonus: +5% per support card, maximum +30% (6 cards)
     *
     * Examples:
     * - 0 cards: 0% bonus
     * - 1 card: 5% bonus
     * - 2 cards: 10% bonus
     * - 3 cards: 15% bonus
     * - 4 cards: 20% bonus
     * - 5 cards: 25% bonus
     * - 6+ cards: 30% bonus (capped)
     *
     * @param  int  $numCards  Number of support cards present
     * @return float Bonus multiplier as decimal (0.0-0.30)
     */
    public function calculateMultiTrainingBonus(
        int $numCards
    ): float {
        // Calculate 5% per card, capped at 30% (6 cards)
        $bonus = $numCards * 0.05;

        // Cap at 30% maximum
        return min($bonus, 0.30);
    }

    /**
     * Calculate facility level from use count
     *
     * Calculates the current level of a training facility based on how many
     * times it has been used.
     *
     * Progression: Every 4 uses = +1 level, maximum Level 5
     *
     * Examples:
     * - 0-3 uses: Level 1
     * - 4-7 uses: Level 2
     * - 8-11 uses: Level 3
     * - 12-15 uses: Level 4
     * - 16+ uses: Level 5 (capped)
     *
     * @param  int  $useCount  Number of times facility has been used
     * @return int Facility level (1-5)
     */
    public function calculateFacilityLevel(
        int $useCount
    ): int {
        // Every 4 uses = +1 level, starting at Level 1
        // 0-3 uses: Level 1
        // 4-7 uses: Level 2
        // 8-11 uses: Level 3
        // 12-15 uses: Level 4
        // 16+ uses: Level 5 (capped)
        $level = (int) floor($useCount / 4) + 1;

        // Cap at Level 5
        return min($level, 5);
    }
}
