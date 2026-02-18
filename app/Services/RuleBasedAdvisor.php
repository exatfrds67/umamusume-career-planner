<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Priority;
use App\Models\Character;
use App\Models\Race;
use App\Neuron\Responses\RaceStrategyResponse;
use App\ValueObjects\Recommendation;
use App\ValueObjects\TrainingContext;

/**
 * Rule-Based Advisor
 *
 * Provides deterministic, offline-capable recommendations when AI services are unavailable.
 * This service implements game mechanics-based logic for training facility selection,
 * skill purchase prioritization, and race strategy generation.
 *
 * The rule-based advisor serves as a fallback when:
 * - Internet connectivity is unavailable
 * - AI services (Ollama/Bedrock) are down or slow
 * - User explicitly requests offline mode
 *
 * All recommendations are based on established game mechanics and best practices
 * from the Umamusume Pretty Derby community.
 *
 * @see \App\Services\TrainingAdvisoryService
 * @see \App\Services\GameMechanicsEngine
 */
class RuleBasedAdvisor
{
    /**
     * Create a new Rule-Based Advisor instance
     *
     * @param  GameMechanicsEngine  $mechanicsEngine  Game mechanics calculation engine
     */
    public function __construct(
        private readonly GameMechanicsEngine $mechanicsEngine,
    ) {}

    /**
     * Recommend a training facility based on game mechanics
     *
     * Analyzes the training context and recommends the optimal training facility
     * using deterministic rules:
     *
     * 1. If energy < 50: Recommend rest or Wisdom training
     * 2. If Friendship Training available (bond ≥80): Prioritize facility with most friendship-ready cards
     * 3. Otherwise: Recommend facility with most support cards present
     * 4. Consider facility levels (prefer lower levels to unlock multipliers)
     * 5. Consider phase-specific stat priorities
     *
     * **Validates: Property 3 (Friendship Training Priority)**
     * **Validates: Property 4 (Energy-Based Rest Recommendations)**
     *
     * @param  TrainingContext  $context  Complete character and training state
     * @return Recommendation Training facility recommendation
     */
    public function recommendTrainingFacility(
        TrainingContext $context
    ): Recommendation {
        // Rule 1: Low energy requires rest or Wisdom training
        if ($context->isEnergyLow()) {
            return $this->recommendRestOrWisdom($context);
        }

        // Rule 2: Prioritize Friendship Training when available
        if ($context->hasFriendshipTrainingAvailable()) {
            return $this->recommendFriendshipTraining($context);
        }

        // Rule 3: Recommend facility with most support cards
        return $this->recommendMultiTraining($context);
    }

    /**
     * Recommend rest or Wisdom training for low energy
     *
     * When energy is below 50, training failure rates increase significantly.
     * This method recommends either rest (if energy < 40) or Wisdom training
     * (if energy 40-49) to recover energy while maintaining progress.
     *
     * @param  TrainingContext  $context  Training context
     * @return Recommendation Rest or Wisdom recommendation
     */
    private function recommendRestOrWisdom(TrainingContext $context): Recommendation
    {
        $energy = $context->energy;

        // Critical energy (<40): Recommend rest
        if ($energy < 40) {
            return Recommendation::rest(
                priority: Priority::HIGH,
                reasoning: "Energy critically low at {$energy}/100. Rest to avoid high failure rates (15-25% failure risk).",
                expectedOutcomes: [
                    'energy_recovery' => '+50-70 energy',
                    'failure_risk' => 'Eliminated',
                ],
                source: 'rule-based',
                storageMode: $context->storageMode,
            );
        }

        // Low energy (40-49): Recommend Wisdom training
        // Wisdom training provides +5 energy while still gaining stats
        $wisdomLevel = $context->facilityLevels['wisdom'] ?? 1;

        return Recommendation::trainingFacility(
            facility: 'wisdom',
            priority: Priority::HIGH,
            reasoning: "Energy low at {$energy}/100. Wisdom training provides +5 energy recovery while maintaining stat gains. Current failure rate: ~10%.",
            expectedOutcomes: [
                'wisdom_gain' => '+30-40',
                'energy_recovery' => '+5',
                'failure_risk' => '~10%',
            ],
            risks: ['Moderate failure rate until energy recovers'],
            confidenceScore: null,
            source: 'rule-based',
            storageMode: $context->storageMode,
            isFriendshipTraining: false,
        );
    }

    /**
     * Recommend Friendship Training facility
     *
     * When support cards have bond ≥80, Friendship Training provides massive
     * stat multipliers (+10% to +35% based on limit breaks). This method
     * identifies the facility with the most friendship-ready cards.
     *
     * @param  TrainingContext  $context  Training context
     * @return Recommendation Friendship Training recommendation
     */
    private function recommendFriendshipTraining(TrainingContext $context): Recommendation
    {
        // Count friendship-ready cards per facility
        $facilityCardCounts = $this->countFriendshipReadyCardsByFacility($context);

        // Find facility with most friendship-ready cards
        arsort($facilityCardCounts);
        $bestFacility = array_key_first($facilityCardCounts);
        $bestFacility = is_string($bestFacility) ? $bestFacility : 'speed';
        $cardCount = $facilityCardCounts[$bestFacility];

        $facilityLevel = $context->facilityLevels[$bestFacility] ?? 1;
        $multiTrainingBonus = $this->mechanicsEngine->calculateMultiTrainingBonus($cardCount);

        return Recommendation::trainingFacility(
            facility: $bestFacility,
            priority: Priority::HIGH,
            reasoning: "Friendship Training available with {$cardCount} bond ≥80 cards at {$bestFacility}. Massive stat multiplier (+10-35% from friendship bonus). Facility Level {$facilityLevel}.",
            expectedOutcomes: [
                'stat_gain' => '+50-70 (with friendship bonus)',
                'bond_increases' => array_fill(0, $cardCount, '+7-12'),
                'multi_training_bonus' => '+'.($multiTrainingBonus * 100).'%',
            ],
            risks: [],
            confidenceScore: null,
            source: 'rule-based',
            storageMode: $context->storageMode,
            isFriendshipTraining: true,
        );
    }

    /**
     * Recommend multi-training facility
     *
     * When Friendship Training is not available, prioritize facilities with
     * the most support cards present to maximize multi-training bonus
     * (+5% per card, max +30%).
     *
     * @param  TrainingContext  $context  Training context
     * @return Recommendation Multi-training recommendation
     */
    private function recommendMultiTraining(TrainingContext $context): Recommendation
    {
        // Count all cards per facility
        $facilityCardCounts = $this->countCardsByFacility($context);

        // Find facility with most cards
        arsort($facilityCardCounts);
        $bestFacility = array_key_first($facilityCardCounts);
        $bestFacility = is_string($bestFacility) ? $bestFacility : 'speed';
        $cardCount = $facilityCardCounts[$bestFacility];

        // If no cards at any facility, recommend based on phase priorities
        if ($cardCount === 0) {
            return $this->recommendByPhase($context);
        }

        $facilityLevel = $context->facilityLevels[$bestFacility] ?? 1;
        $multiTrainingBonus = $this->mechanicsEngine->calculateMultiTrainingBonus($cardCount);

        return Recommendation::trainingFacility(
            facility: $bestFacility,
            priority: Priority::MEDIUM,
            reasoning: "{$cardCount} support cards present at {$bestFacility}. Multi-training bonus: +".($multiTrainingBonus * 100)."%. Facility Level {$facilityLevel}.",
            expectedOutcomes: [
                'stat_gain' => '+40-55',
                'bond_increases' => array_fill(0, $cardCount, '+7'),
                'multi_training_bonus' => '+'.($multiTrainingBonus * 100).'%',
            ],
            risks: [],
            confidenceScore: null,
            source: 'rule-based',
            storageMode: $context->storageMode,
            isFriendshipTraining: false,
        );
    }

    /**
     * Recommend training based on career phase priorities
     *
     * When no support cards are present, recommend training based on
     * phase-specific stat priorities and facility levels.
     *
     * @param  TrainingContext  $context  Training context
     * @return Recommendation Phase-based recommendation
     */
    private function recommendByPhase(TrainingContext $context): Recommendation
    {
        // Get stats that are behind phase targets
        $statsBehind = $context->getStatsBehindTarget();

        // If stats are behind, prioritize the most deficient stat
        if (! empty($statsBehind)) {
            $targetStat = $statsBehind[0]; // First stat behind target
            $facilityLevel = $context->facilityLevels[$targetStat] ?? 1;

            return Recommendation::trainingFacility(
                facility: $targetStat,
                priority: Priority::MEDIUM,
                reasoning: "{$targetStat} is behind phase target. Focus training to catch up. Facility Level {$facilityLevel}.",
                expectedOutcomes: [
                    'stat_gain' => '+35-45',
                ],
                risks: ['No support cards present - lower gains'],
                confidenceScore: null,
                source: 'rule-based',
                storageMode: $context->storageMode,
                isFriendshipTraining: false,
            );
        }

        // Otherwise, recommend facility with lowest level to unlock multipliers
        $lowestFacility = $context->getLowestFacility();

        return Recommendation::trainingFacility(
            facility: $lowestFacility['facility'],
            priority: Priority::LOW,
            reasoning: "All stats on track. Train {$lowestFacility['facility']} to increase facility level (currently Level {$lowestFacility['level']}).",
            expectedOutcomes: [
                'stat_gain' => '+35-45',
                'facility_progress' => 'Progress toward Level '.($lowestFacility['level'] + 1),
            ],
            risks: ['No support cards present - lower gains'],
            confidenceScore: null,
            source: 'rule-based',
            storageMode: $context->storageMode,
            isFriendshipTraining: false,
        );
    }

    /**
     * Count friendship-ready cards (bond ≥80) by facility
     *
     * @param  TrainingContext  $context  Training context
     * @return array<string, int> Card counts keyed by facility name
     */
    private function countFriendshipReadyCardsByFacility(TrainingContext $context): array
    {
        $counts = [
            'speed' => 0,
            'stamina' => 0,
            'power' => 0,
            'guts' => 0,
            'wisdom' => 0,
        ];

        foreach ($context->deck->cards as $card) {
            if ($card->bond >= 80) {
                $facility = $card->facility;
                if (isset($counts[$facility])) {
                    $counts[$facility]++;
                }
            }
        }

        return $counts;
    }

    /**
     * Count all cards by facility
     *
     * @param  TrainingContext  $context  Training context
     * @return array<string, int> Card counts keyed by facility name
     */
    private function countCardsByFacility(TrainingContext $context): array
    {
        $counts = [
            'speed' => 0,
            'stamina' => 0,
            'power' => 0,
            'guts' => 0,
            'wisdom' => 0,
        ];

        foreach ($context->deck->cards as $card) {
            $facility = $card->facility;
            if (isset($counts[$facility])) {
                $counts[$facility]++;
            }
        }

        return $counts;
    }

    /**
     * Recommend skill purchase based on SP efficiency
     *
     * Analyzes available skills and recommends purchases based on:
     * 1. Gold skills with Level 3+ hints (30%+ discount)
     * 2. SP cost efficiency
     * 3. Remaining SP budget
     * 4. Skill impact (stamina recovery, positioning, etc.)
     *
     * **Validates: Property 6 (Gold Skill Prioritization)**
     *
     * @param  Character  $character  Character with current SP and skills
     * @param  array<array{id: int, name: string, tier: string, base_cost: int, hint_level: int, category: string}>  $availableSkills  Available skills for purchase
     * @return Recommendation|null Skill purchase recommendation, or null if no good options
     */
    public function recommendSkillPurchase(
        Character $character,
        array $availableSkills
    ): ?Recommendation {
        // Filter to skills with good hints (Level 3+)
        $goodHints = array_filter($availableSkills, fn ($skill) => $skill['hint_level'] >= 3);

        // Prioritize gold skills
        $goldSkills = array_filter($goodHints, fn ($skill) => $skill['tier'] === 'gold');

        // If gold skills available, recommend the first one
        if (! empty($goldSkills)) {
            $skill = reset($goldSkills);
            $cost = $this->mechanicsEngine->calculateSkillCost(
                $skill['base_cost'],
                $skill['hint_level'],
                false
            );

            // Check if character has enough SP
            if (! isset($character->available_sp) || $character->available_sp < $cost) {
                return null;
            }

            return Recommendation::skillPurchase(
                skillName: $skill['name'],
                priority: Priority::HIGH,
                reasoning: "Gold {$skill['category']} skill with Level {$skill['hint_level']} hint ({$this->getHintDiscount($skill['hint_level'])}% discount). High impact for races.",
                expectedOutcomes: [
                    'sp_cost' => $cost,
                    'sp_remaining' => $character->available_sp - $cost,
                    'impact' => 'Significant race performance improvement',
                ],
                risks: [],
                confidenceScore: null,
                source: 'rule-based',
                storageMode: 'account', // Character model implies account mode
            );
        }

        // If no gold skills, recommend rare skills with good hints
        if (! empty($goodHints)) {
            $skill = reset($goodHints);
            $cost = $this->mechanicsEngine->calculateSkillCost(
                $skill['base_cost'],
                $skill['hint_level'],
                false
            );

            // Check if character has enough SP
            if (! isset($character->available_sp) || $character->available_sp < $cost) {
                return null;
            }

            return Recommendation::skillPurchase(
                skillName: $skill['name'],
                priority: Priority::MEDIUM,
                reasoning: "{$skill['tier']} {$skill['category']} skill with Level {$skill['hint_level']} hint ({$this->getHintDiscount($skill['hint_level'])}% discount). Good SP efficiency.",
                expectedOutcomes: [
                    'sp_cost' => $cost,
                    'sp_remaining' => $character->available_sp - $cost,
                    'impact' => 'Moderate race performance improvement',
                ],
                risks: [],
                confidenceScore: null,
                source: 'rule-based',
                storageMode: 'account',
            );
        }

        // No good skill options available
        return null;
    }

    /**
     * Get hint discount percentage
     *
     * @param  int  $hintLevel  Hint level (0-5)
     * @return int Discount percentage
     */
    private function getHintDiscount(int $hintLevel): int
    {
        return match ($hintLevel) {
            1 => 10,
            2 => 20,
            3 => 30,
            4 => 35,
            5 => 40,
            default => 0,
        };
    }

    /**
     * Generate race strategy based on character stats and race requirements
     *
     * Analyzes character stats, aptitudes, and race requirements to generate
     * a deterministic race strategy including:
     * - Recommended running style (based on aptitude grades)
     * - Stamina sufficiency check
     * - Skill recommendations
     * - Win probability estimate
     *
     * @param  Character  $character  Character with stats and aptitudes
     * @param  Race  $race  Race details and requirements
     * @return RaceStrategyResponse Race strategy recommendation
     */
    public function generateRaceStrategy(
        Character $character,
        Race $race
    ): RaceStrategyResponse {
        // Determine recommended running style based on aptitudes
        $recommendedStyle = $this->determineRunningStyle($character, $race);

        // Check stamina requirements
        $staminaCheck = $this->checkStaminaRequirement($character, $race, $recommendedStyle);

        // Generate skill recommendations
        $recommendedSkills = $this->recommendRaceSkills($character, $race);

        // Generate preparation advice
        $preparationAdvice = $this->generatePreparationAdvice($character, $race, $staminaCheck);

        // Estimate performance
        $expectedPerformance = $this->estimatePerformance($character, $race, $staminaCheck);

        // Identify risk factors
        $riskFactors = $this->identifyRiskFactors($character, $race, $staminaCheck);

        return new RaceStrategyResponse(
            recommendedRunningStyle: $recommendedStyle,
            recommendedSkills: $recommendedSkills,
            racePreparationAdvice: $preparationAdvice,
            expectedPerformance: $expectedPerformance,
            riskFactors: $riskFactors,
        );
    }

    /**
     * Determine optimal running style based on aptitudes
     *
     * @param  Character  $character  Character with aptitudes
     * @param  Race  $race  Race details
     * @return string Running style (escape, leader, betweener, chaser)
     */
    private function determineRunningStyle(Character $character, Race $race): string
    {
        // For now, return a default based on character's highest aptitude
        // In a full implementation, this would analyze aptitude grades
        return 'escape';
    }

    /**
     * Check if character meets stamina requirements
     *
     * @param  Character  $character  Character with stats
     * @param  Race  $race  Race details
     * @param  string  $runningStyle  Running style
     * @return array{sufficient: bool, current: int, required: int, deficit: int} Stamina check result
     */
    private function checkStaminaRequirement(Character $character, Race $race, string $runningStyle): array
    {
        // Get race distance enum (simplified for now)
        $distance = \App\Enums\RaceDistance::MEDIUM; // Default

        // Get running style enum
        $style = match ($runningStyle) {
            'leader' => \App\Enums\RunningStyle::LEAD,
            'betweener' => \App\Enums\RunningStyle::PACE,
            'chaser' => \App\Enums\RunningStyle::CHASE,
            default => \App\Enums\RunningStyle::ESCAPE,
        };

        // Calculate required stamina
        $required = $this->mechanicsEngine->calculateStaminaRequirement($distance, $style, []);

        $current = $character->stamina ?? 0;
        $deficit = max(0, $required - $current);

        return [
            'sufficient' => $current >= $required,
            'current' => $current,
            'required' => $required,
            'deficit' => (int) $deficit,
        ];
    }

    /**
     * Recommend skills for the race
     *
     * @param  Character  $character  Character with acquired skills
     * @param  Race  $race  Race details
     * @return array<int, string> Recommended skill names
     */
    private function recommendRaceSkills(Character $character, Race $race): array
    {
        // Simplified: recommend generic race skills
        return [
            'Swinging Maestro',
            'Lane Legerdemain',
            'Furious Feat',
        ];
    }

    /**
     * Generate race preparation advice
     *
     * @param  Character  $character  Character stats
     * @param  Race  $race  Race details
     * @param  array{sufficient: bool, current: int, required: int, deficit: int}  $staminaCheck  Stamina check result
     * @return string Preparation advice
     */
    private function generatePreparationAdvice(Character $character, Race $race, array $staminaCheck): string
    {
        if (! $staminaCheck['sufficient']) {
            return "Focus on stamina training immediately. You need {$staminaCheck['deficit']} more stamina to safely complete this race. Prioritize Friendship Training at stamina facility if available.";
        }

        return "Your character is well-prepared for this race. Stamina is sufficient ({$staminaCheck['current']}/{$staminaCheck['required']}). Ensure energy is above 70 before the race for optimal performance.";
    }

    /**
     * Estimate race performance
     *
     * @param  Character  $character  Character stats
     * @param  Race  $race  Race details
     * @param  array{sufficient: bool, current: int, required: int, deficit: int}  $staminaCheck  Stamina check result
     * @return string Performance estimate
     */
    private function estimatePerformance(Character $character, Race $race, array $staminaCheck): string
    {
        if (! $staminaCheck['sufficient']) {
            return 'Low chance of winning due to stamina deficit. High risk of running out of stamina.';
        }

        return 'Good chance of winning. Stats meet race requirements.';
    }

    /**
     * Identify risk factors for the race
     *
     * @param  Character  $character  Character stats
     * @param  Race  $race  Race details
     * @param  array{sufficient: bool, current: int, required: int, deficit: int}  $staminaCheck  Stamina check result
     * @return array<int, string> Risk factors
     */
    private function identifyRiskFactors(Character $character, Race $race, array $staminaCheck): array
    {
        $risks = [];

        if (! $staminaCheck['sufficient']) {
            $risks[] = "Stamina deficit of {$staminaCheck['deficit']} points - high risk of exhaustion";
        }

        // Add more risk checks as needed

        return $risks;
    }
}
