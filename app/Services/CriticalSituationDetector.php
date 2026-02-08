<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Priority;
use App\Enums\RaceDistance;
use App\Enums\RunningStyle;
use App\ValueObjects\CriticalAlert;
use App\ValueObjects\TrainingContext;

/**
 * Critical Situation Detector
 *
 * Monitors character state for critical situations requiring immediate attention.
 * Generates high-priority alerts for conditions such as stamina crisis, SP shortage,
 * energy critical, bond behind schedule, facility imbalance, and mood issues.
 *
 * This service analyzes the training context and detects situations that could
 * jeopardize the character's ability to achieve A+ grade ratings or complete
 * career objectives successfully.
 *
 * @see \App\Services\TrainingAdvisoryService
 * @see \App\Services\GameMechanicsEngine
 * @see \App\ValueObjects\CriticalAlert
 * @see \App\Enums\AlertType
 */
class CriticalSituationDetector
{
    /**
     * Create a new critical situation detector instance
     *
     * @param  GameMechanicsEngine  $mechanicsEngine  Game mechanics calculation engine
     */
    public function __construct(
        private readonly GameMechanicsEngine $mechanicsEngine
    ) {}

    /**
     * Detect stamina crisis situations
     *
     * Checks if character's stamina is insufficient for upcoming race distance
     * requirements. Generates critical alert with specific stamina targets and
     * action items for recovery.
     *
     * Stamina thresholds by distance (for Escape style):
     * - Sprint: 350-400
     * - Mile: 450-500
     * - Medium: 600-700
     * - Long: 850-1000
     *
     * Alert is triggered when stamina falls below the minimum threshold for
     * the next scheduled race, with severity increasing as the race approaches.
     *
     * @param  TrainingContext  $context  Current training context
     * @return CriticalAlert|null Alert if stamina crisis detected, null otherwise
     */
    public function detectStaminaCrisis(TrainingContext $context): ?CriticalAlert
    {
        // Get the next upcoming race
        $nextRace = $context->getNextRace();

        // No crisis if no upcoming races
        if ($nextRace === null) {
            return null;
        }

        // Parse the race distance
        $distanceStr = $nextRace['distance'] ?? 'medium';
        $distance = RaceDistance::from($distanceStr);

        // Calculate stamina requirement for Escape style (highest requirement)
        // We use Escape as the baseline since it requires the most stamina
        $requiredStamina = $this->mechanicsEngine->calculateStaminaRequirement(
            $distance,
            RunningStyle::ESCAPE,
            [] // No recovery skills assumed for crisis detection
        );

        // Get current stamina
        $currentStamina = $context->stats->stamina;

        // Calculate stamina gap
        $staminaGap = $requiredStamina - $currentStamina;

        // No crisis if stamina is sufficient
        if ($staminaGap <= 0) {
            return null;
        }

        // Calculate turns until race
        $turnsUntilRace = $nextRace['turn'] - $context->turnNumber;

        // Calculate required stamina gain per turn
        $requiredGainPerTurn = $turnsUntilRace > 0
            ? (int) ceil($staminaGap / $turnsUntilRace)
            : $staminaGap;

        // Generate action items based on the situation
        $actionItems = [];

        if ($turnsUntilRace <= 3) {
            $actionItems[] = 'URGENT: Focus ALL remaining turns on Stamina training';
            $actionItems[] = 'Prioritize Friendship Training at Stamina facility if available (bond ≥80)';
        } else {
            $actionItems[] = "Focus next {$turnsUntilRace} turns on Stamina training";
            $actionItems[] = 'Prioritize Friendship Training at Stamina facility when available';
        }

        // Add recovery skill recommendation if gap is large
        if ($staminaGap > 200) {
            $actionItems[] = 'Consider purchasing stamina recovery skills (reduces requirement by 150-200)';
            $actionItems[] = 'Target gold recovery skills: Swinging Maestro, In Body and Mind, Adrenaline Rush';
        }

        // Add facility level recommendation
        $staminaFacilityLevel = $context->facilityLevels['stamina'] ?? 1;
        if ($staminaFacilityLevel < 3) {
            $actionItems[] = "Train at Stamina facility to increase facility level (currently Level {$staminaFacilityLevel})";
        }

        // Generate detailed analysis
        $distanceLabel = $distance->label();
        $detailedAnalysis = "Current stamina of {$currentStamina} is {$staminaGap} points below the {$requiredStamina} minimum for {$distanceLabel} distance Escape style. ";

        if ($turnsUntilRace > 0) {
            $detailedAnalysis .= "With {$turnsUntilRace} turns remaining, you need approximately +{$requiredGainPerTurn} stamina per turn. ";
        } else {
            $detailedAnalysis .= 'The race is THIS TURN - stamina is critically insufficient. ';
        }

        // Add context about running style alternatives
        $detailedAnalysis .= "\n\nRunning style alternatives:\n";
        $detailedAnalysis .= "- Escape (1.0x): {$requiredStamina} stamina required\n";

        $leadRequirement = $this->mechanicsEngine->calculateStaminaRequirement($distance, RunningStyle::LEAD, []);
        $detailedAnalysis .= "- Lead (0.95x): {$leadRequirement} stamina required\n";

        $paceRequirement = $this->mechanicsEngine->calculateStaminaRequirement($distance, RunningStyle::PACE, []);
        $detailedAnalysis .= "- Pace (0.85x): {$paceRequirement} stamina required\n";

        $chaseRequirement = $this->mechanicsEngine->calculateStaminaRequirement($distance, RunningStyle::CHASE, []);
        $detailedAnalysis .= "- Chase (0.75x): {$chaseRequirement} stamina required\n";

        // Check if any alternative style would work
        if ($currentStamina >= $chaseRequirement) {
            $detailedAnalysis .= "\nNote: Chase style may be viable with current stamina, but will require different aptitude grades.";
        } elseif ($currentStamina >= $paceRequirement) {
            $detailedAnalysis .= "\nNote: Pace style may be viable with current stamina, but will require different aptitude grades.";
        }

        // Generate the alert message
        $message = "Stamina critically low for upcoming {$distanceLabel} race ({$currentStamina} vs {$requiredStamina} required)";

        // Create and return the critical alert
        return CriticalAlert::staminaCrisis(
            message: $message,
            actionItems: $actionItems,
            turnsUntilCritical: max(0, $turnsUntilRace),
            detailedAnalysis: $detailedAnalysis,
            storageMode: $context->storageMode,
        );
    }

    /**
     * Detect SP shortage situations
     *
     * Checks if SP budget is insufficient for planned skill purchases.
     * Typical SP budget is 300-500 per career run.
     *
     * Alert is triggered when:
     * - Current SP is low relative to turn number
     * - Planned skill purchases exceed available SP
     * - Critical skills are unavailable due to SP constraints
     *
     * @param  TrainingContext  $context  Current training context
     * @return CriticalAlert|null Alert if SP shortage detected, null otherwise
     */
    public function detectSpShortage(TrainingContext $context): ?CriticalAlert
    {
        // Estimate total SP available by career end
        // Typical career runs are 60-72 turns
        // SP is earned through training, races, and events
        // Average SP gain: 5-8 SP per turn
        $turnsRemaining = 72 - $context->turnNumber;
        $averageSpPerTurn = 6; // Conservative estimate
        $estimatedFutureSp = $turnsRemaining * $averageSpPerTurn;
        $projectedTotalSp = $context->spAvailable + $estimatedFutureSp;

        // Calculate SP already spent
        // Typical budget is 300-500 SP per career
        $typicalMinBudget = 300;
        $typicalMaxBudget = 500;

        // Estimate SP spent based on acquired skills
        // Average skill cost is ~100-150 SP (before discounts)
        $skillsAcquired = count($context->acquiredSkills);
        $estimatedSpSpent = $skillsAcquired * 120; // Conservative average

        // Calculate total SP earned so far
        $totalSpEarned = $context->spAvailable + $estimatedSpSpent;

        // Check if we're on track for minimum budget
        // Use a more lenient threshold for early game
        $progressRatio = $context->turnNumber / 72;
        $expectedSpByTurn = $progressRatio * $typicalMinBudget;
        $spDeficit = $expectedSpByTurn - $totalSpEarned;

        // Calculate severity based on deficit and remaining turns
        $isLowSp = $context->spAvailable < 50;
        $isProjectedLow = $projectedTotalSp < $typicalMinBudget;
        $isCriticallyLow = $projectedTotalSp < 250;

        // Early game exception: Don't alert if we're in early game (turn < 20) and projected SP is adequate
        if ($context->turnNumber < 20 && $projectedTotalSp >= $typicalMinBudget) {
            return null;
        }

        // Late game exception: Don't alert if we have adequate SP remaining for the few turns left
        // In late game (turn > 60), focus on current SP rather than projected total
        if ($context->turnNumber > 60 && $context->spAvailable >= 100) {
            return null;
        }

        // No alert if we're on track or ahead and not in a critical situation
        if ($spDeficit <= 0 && $projectedTotalSp >= $typicalMinBudget && ! $isLowSp) {
            return null;
        }

        // Only alert if SP situation is concerning
        if (! $isLowSp && ! $isProjectedLow) {
            return null;
        }

        // Generate action items based on situation
        $actionItems = [];

        if ($isLowSp) {
            $actionItems[] = 'Current SP critically low - avoid purchasing skills until SP increases';
            $actionItems[] = 'Focus on training and races to earn more SP';
        }

        if ($isCriticallyLow) {
            $actionItems[] = 'Projected total SP below minimum budget (250 SP)';
            $actionItems[] = 'Prioritize ONLY essential gold skills with Level 3+ hints';
            $actionItems[] = 'Avoid purchasing normal skills - wait for rare skill evolution opportunities';
        } elseif ($isProjectedLow) {
            $actionItems[] = "Projected total SP ({$projectedTotalSp}) below typical minimum ({$typicalMinBudget})";
            $actionItems[] = 'Be selective with skill purchases - prioritize high-impact skills';
            $actionItems[] = 'Wait for higher hint levels (Level 3+) before purchasing to maximize discounts';
        }

        // Add hint level recommendations
        $actionItems[] = 'Target skills with Level 3+ hints for 30-40% discount';

        // Check if we have high-level hints available
        $highLevelHints = array_filter($context->skillHints, fn ($hint) => $hint['level'] >= 3);
        if (! empty($highLevelHints)) {
            $hintCount = count($highLevelHints);
            $actionItems[] = "You have {$hintCount} skill(s) with Level 3+ hints - prioritize these purchases";
        }

        // Add race participation recommendation
        if ($turnsRemaining > 10) {
            $actionItems[] = 'Participate in optional races to earn additional SP';
        }

        // Generate detailed analysis
        $detailedAnalysis = "SP Budget Analysis:\n\n";
        $detailedAnalysis .= "Current Status:\n";
        $detailedAnalysis .= "- SP Available: {$context->spAvailable}\n";
        $detailedAnalysis .= "- Skills Acquired: {$skillsAcquired}\n";
        $detailedAnalysis .= "- Estimated SP Spent: {$estimatedSpSpent}\n";
        $detailedAnalysis .= "- Total SP Earned: {$totalSpEarned}\n\n";

        $detailedAnalysis .= "Projections:\n";
        $detailedAnalysis .= "- Turns Remaining: {$turnsRemaining}\n";
        $detailedAnalysis .= "- Estimated Future SP: {$estimatedFutureSp} (at {$averageSpPerTurn} SP/turn)\n";
        $detailedAnalysis .= "- Projected Total SP: {$projectedTotalSp}\n";
        $detailedAnalysis .= "- Typical Budget Range: {$typicalMinBudget}-{$typicalMaxBudget} SP\n\n";

        if ($spDeficit > 0) {
            $detailedAnalysis .= "Budget Gap:\n";
            $detailedAnalysis .= "- Expected SP by Turn {$context->turnNumber}: ".round($expectedSpByTurn)."\n";
            $detailedAnalysis .= '- Current Deficit: '.round($spDeficit)." SP\n\n";
        }

        $detailedAnalysis .= "Recommendations:\n";
        $detailedAnalysis .= "- Prioritize gold skills (stamina recovery, positioning, acceleration)\n";
        $detailedAnalysis .= "- Wait for Level 3+ hints before purchasing (30-40% discount)\n";
        $detailedAnalysis .= "- Avoid 'SP trap' skills with low value or highly conditional activation\n";
        $detailedAnalysis .= "- Consider skill evolution paths (Normal → Rare) for better value\n";

        // Add hint level discount information
        $detailedAnalysis .= "\nHint Level Discounts:\n";
        $detailedAnalysis .= "- Level 1: 10% discount\n";
        $detailedAnalysis .= "- Level 2: 20% discount\n";
        $detailedAnalysis .= "- Level 3: 30% discount\n";
        $detailedAnalysis .= "- Level 4: 35% discount\n";
        $detailedAnalysis .= "- Level 5: 40% discount (maximum)\n";

        // Generate alert message
        if ($isCriticallyLow) {
            $message = "SP budget critically low - projected total {$projectedTotalSp} SP (minimum 300 recommended)";
        } elseif ($isLowSp) {
            $message = "Current SP very low ({$context->spAvailable}) - be selective with purchases";
        } else {
            $message = "SP budget below target - projected total {$projectedTotalSp} SP (typical range 300-500)";
        }

        // Calculate turns until critical (when SP runs out completely)
        // If current SP is very low, it's already critical
        $turnsUntilCritical = $isLowSp ? 0 : max(0, (int) floor($turnsRemaining / 2));

        // Create and return the SP shortage alert
        return CriticalAlert::spShortage(
            message: $message,
            actionItems: $actionItems,
            turnsUntilCritical: $turnsUntilCritical,
            detailedAnalysis: $detailedAnalysis,
            storageMode: $context->storageMode,
        );
    }

    /**
     * Detect energy critical situations
     *
     * Checks if energy level is critically low (below 40), causing high
     * failure rates and reduced training effectiveness.
     *
     * Energy thresholds:
     * - 70+: Very low failure rate (~1-2%)
     * - 50-69: Low failure rate (~3-5%)
     * - 30-49: Moderate failure rate (~8-12%)
     * - <30: High failure rate (~15-25%)
     *
     * Alert is triggered when energy drops below 40 with important training
     * or races ahead.
     *
     * @param  TrainingContext  $context  Current training context
     * @return CriticalAlert|null Alert if energy critical detected, null otherwise
     */
    public function detectEnergyCritical(TrainingContext $context): ?CriticalAlert
    {
        // Get current energy level
        $currentEnergy = $context->energy;

        // Define critical threshold
        $criticalThreshold = 40;

        // No alert if energy is above critical threshold
        if ($currentEnergy >= $criticalThreshold) {
            return null;
        }

        // Calculate failure rate based on current energy
        // Using GameMechanicsEngine to get accurate failure rate
        $failureRate = $this->mechanicsEngine->calculateFailureRate(
            $currentEnergy,
            \count($context->deck->cards ?? []),
            [] // No special conditions for now
        );

        // Determine severity based on energy level
        $isCritical = $currentEnergy < 30; // Very high failure rate
        $isUrgent = $currentEnergy <= 20; // Extremely high failure rate

        // Generate action items based on severity
        $actionItems = [];

        if ($isUrgent) {
            $actionItems[] = 'URGENT: Rest immediately - energy critically low';
            $actionItems[] = 'Do NOT attempt any training until energy recovers to at least 50';
        } elseif ($isCritical) {
            $actionItems[] = 'Rest immediately or train Wisdom (+5 energy)';
            $actionItems[] = 'Avoid high-risk training until energy recovers to 50+';
        } else {
            $actionItems[] = 'Rest or train Wisdom (+5 energy) to recover';
            $actionItems[] = 'Avoid training at facilities with low support card presence';
        }

        // Add context-specific recommendations
        $nextRace = $context->getNextRace();
        if ($nextRace !== null) {
            $turnsUntilRace = $nextRace['turn'] - $context->turnNumber;
            if ($turnsUntilRace <= 5) {
                $actionItems[] = "Important race in {$turnsUntilRace} turn(s) - prioritize energy recovery";
            }
        }

        // Add Wisdom training recommendation if facility level is decent
        $wisdomFacilityLevel = $context->facilityLevels['wisdom'] ?? 1;
        if ($wisdomFacilityLevel >= 3 && $currentEnergy >= 25) {
            $actionItems[] = "Wisdom training recommended (Level {$wisdomFacilityLevel} facility, +5 energy bonus)";
        }

        // Add rest recommendation for very low energy
        if ($currentEnergy < 25) {
            $actionItems[] = 'Rest is strongly recommended over Wisdom training at this energy level';
        }

        // Generate detailed analysis
        $detailedAnalysis = "Energy Critical Analysis:\n\n";
        $detailedAnalysis .= "Current Status:\n";
        $detailedAnalysis .= "- Energy Level: {$currentEnergy}/100\n";
        $detailedAnalysis .= '- Failure Rate: '.round($failureRate * 100, 1)."%\n";
        $detailedAnalysis .= '- Status: ';

        if ($isUrgent) {
            $detailedAnalysis .= "EXTREMELY CRITICAL (< 20)\n\n";
        } elseif ($isCritical) {
            $detailedAnalysis .= "CRITICAL (< 30)\n\n";
        } else {
            $detailedAnalysis .= "LOW (< 40)\n\n";
        }

        $detailedAnalysis .= "Energy Impact on Training:\n";
        $detailedAnalysis .= "- Training failure risk significantly increased\n";
        $detailedAnalysis .= "- Stat gains reduced when training fails\n";
        $detailedAnalysis .= "- Risk of injury conditions\n";
        $detailedAnalysis .= "- Bond gains may be reduced\n\n";

        $detailedAnalysis .= "Energy Thresholds:\n";
        $detailedAnalysis .= "- 70+: Very low failure rate (~1-2%)\n";
        $detailedAnalysis .= "- 50-69: Low failure rate (~3-5%)\n";
        $detailedAnalysis .= "- 30-49: Moderate failure rate (~8-12%)\n";
        $detailedAnalysis .= "- <30: High failure rate (~15-25%)\n\n";

        $detailedAnalysis .= "Recovery Options:\n";
        $detailedAnalysis .= "- Rest: Recovers 50-70 energy (guaranteed)\n";
        $detailedAnalysis .= "- Wisdom Training: +5 energy bonus (plus stat gains)\n";
        $detailedAnalysis .= "- Recreation: Improves mood and recovers some energy\n\n";

        // Add race context if applicable
        if ($nextRace !== null) {
            $turnsUntilRace = $nextRace['turn'] - $context->turnNumber;
            $distanceLabel = ucfirst($nextRace['distance'] ?? 'unknown');
            $detailedAnalysis .= "Upcoming Race:\n";
            $detailedAnalysis .= "- Distance: {$distanceLabel}\n";
            $detailedAnalysis .= "- Turns Until Race: {$turnsUntilRace}\n";
            $detailedAnalysis .= "- Recommendation: Recover energy before race to maximize performance\n\n";
        }

        $detailedAnalysis .= "Strategic Considerations:\n";
        $detailedAnalysis .= "- Low energy increases risk of wasted turns (failed training)\n";
        $detailedAnalysis .= "- Recovering energy now prevents multiple failed training attempts\n";
        $detailedAnalysis .= "- Wisdom training provides both energy recovery and stat gains\n";
        $detailedAnalysis .= "- Rest is safer when energy is extremely low (<25)\n";

        // Generate alert message based on severity
        if ($isUrgent) {
            $message = "Energy extremely critical at {$currentEnergy} - immediate rest required";
        } elseif ($isCritical) {
            $message = "Energy critical at {$currentEnergy} - high failure rate risk (".round($failureRate * 100, 1).'%)';
        } else {
            $message = "Energy low at {$currentEnergy} - increased failure rate (".round($failureRate * 100, 1).'%)';
        }

        // Create and return the energy critical alert
        return new CriticalAlert(
            type: \App\Enums\AlertType::ENERGY_CRITICAL,
            message: $message,
            actionItems: $actionItems,
            turnsUntilCritical: 0, // Always 0 for energy critical (already critical)
            detailedAnalysis: $detailedAnalysis,
            priority: $isUrgent ? Priority::CRITICAL : Priority::HIGH,
            storageMode: $context->storageMode,
        );
    }

    /**
     * Detect bond behind schedule situations
     *
     * Checks if support card bonds are below 80 by Turn 25, risking loss of
     * Friendship Training opportunities.
     *
     * Friendship Training (bond ≥80) provides:
     * - Massive stat multiplier bonus (+10% to +35% based on limit breaks)
     * - Rainbow glow visual indicator
     * - Significantly higher stat gains
     *
     * Alert is triggered when:
     * - Turn 25 approaching and bonds below 80
     * - Bond progress rate insufficient to reach 80 in time
     * - Multiple cards behind schedule
     *
     * @param  TrainingContext  $context  Current training context
     * @return CriticalAlert|null Alert if bonds behind schedule, null otherwise
     */
    public function detectBondBehindSchedule(TrainingContext $context): ?CriticalAlert
    {
        // Target turn for Friendship Training readiness
        $targetTurn = 25;

        // Get support card deck
        $deck = $context->deck;

        // No alert if deck is empty
        if ($deck->isEmpty()) {
            return null;
        }

        // Get cards not ready for Friendship Training
        $cardsNotReady = $deck->getCardsNotReady();

        // No alert if all cards are already ready
        if (empty($cardsNotReady)) {
            return null;
        }

        // Calculate turns remaining until target
        $turnsRemaining = $targetTurn - $context->turnNumber;

        // If we're past the target turn, check if we have any cards ready
        // If not, it's still a problem but less urgent
        if ($turnsRemaining <= 0) {
            $readyCount = $deck->getFriendshipReadyCount();
            $totalCards = $deck->getCardCount();

            // If we have at least half the cards ready, don't alert
            if ($readyCount >= ($totalCards / 2)) {
                return null;
            }

            // Past target turn with insufficient bonds - still alert but lower urgency
            $turnsRemaining = 0;
        }

        // Bond gain rates:
        // - Base: +7 per training
        // - With Charming trait: +9 per training
        // - With hint mark: +12 per training
        // We'll use conservative estimate of +7 for calculations
        $baseBondGain = 7;

        // Estimate turns needed for each card to reach 80
        $turnsEstimates = $deck->estimateTurnsToFriendship($baseBondGain);

        // Find cards that won't make it in time
        $cardsAtRisk = [];
        $maxTurnsNeeded = 0;

        foreach ($cardsNotReady as $card) {
            $turnsNeeded = $turnsEstimates[$card->id] ?? 0;

            // Card is at risk if it needs more turns than we have remaining
            if ($turnsNeeded > $turnsRemaining && $turnsRemaining > 0) {
                $cardsAtRisk[] = [
                    'card' => $card,
                    'turns_needed' => $turnsNeeded,
                    'bond_gap' => 80 - $card->bond,
                ];

                $maxTurnsNeeded = max($maxTurnsNeeded, $turnsNeeded);
            }
        }

        // Calculate severity based on number of cards at risk and time remaining
        $totalCards = $deck->getCardCount();
        $cardsAtRiskCount = \count($cardsAtRisk);
        $cardsNotReadyCount = \count($cardsNotReady);

        // No alert if:
        // - We're early in the game (turn < 15) and have time
        // - Less than 2 cards are at risk
        // - We have plenty of time remaining (> 10 turns)
        if ($context->turnNumber < 15 && $turnsRemaining > 10) {
            return null;
        }

        if ($cardsAtRiskCount < 2 && $turnsRemaining > 5) {
            return null;
        }

        // If we're past target turn, only alert if majority of cards aren't ready
        if ($turnsRemaining <= 0 && $cardsNotReadyCount < ($totalCards / 2)) {
            return null;
        }

        // Generate action items based on situation
        $actionItems = [];

        if ($turnsRemaining <= 0) {
            $actionItems[] = "Target turn ({$targetTurn}) passed - {$cardsNotReadyCount} of {$totalCards} cards still below bond 80";
            $actionItems[] = 'Focus on training at facilities with low-bond support cards';
        } elseif ($turnsRemaining <= 3) {
            $actionItems[] = "URGENT: Only {$turnsRemaining} turn(s) remaining to reach bond 80 target";
            $actionItems[] = 'Prioritize training at facilities with multiple low-bond cards';
        } else {
            $actionItems[] = "{$cardsAtRiskCount} card(s) at risk of missing bond 80 by Turn {$targetTurn}";
            $actionItems[] = 'Increase training frequency at facilities with low-bond cards';
        }

        // Add specific facility recommendations
        $facilityRecommendations = $this->generateBondBuildingRecommendations($cardsNotReady);
        foreach ($facilityRecommendations as $recommendation) {
            $actionItems[] = $recommendation;
        }

        // Add bond gain optimization tips
        $actionItems[] = 'Bond gains: Base +7, +9 with Charming trait, +12 with hint mark';

        if ($context->turnNumber < 20) {
            $actionItems[] = 'Consider prioritizing bond building over stat optimization in early game';
        }

        // Generate detailed analysis
        $detailedAnalysis = "Bond Progress Analysis:\n\n";
        $detailedAnalysis .= "Current Status:\n";
        $detailedAnalysis .= "- Turn: {$context->turnNumber}/{$targetTurn} (target)\n";
        $detailedAnalysis .= '- Turns Remaining: '.($turnsRemaining > 0 ? $turnsRemaining : 'Past target')."\n";
        $detailedAnalysis .= "- Cards Ready (≥80): {$deck->getFriendshipReadyCount()}/{$totalCards}\n";
        $detailedAnalysis .= "- Cards Not Ready (<80): {$cardsNotReadyCount}/{$totalCards}\n";

        if ($cardsAtRiskCount > 0) {
            $detailedAnalysis .= "- Cards At Risk: {$cardsAtRiskCount}/{$totalCards}\n";
        }

        $detailedAnalysis .= "\n";

        // Add individual card analysis
        $detailedAnalysis .= "Card Bond Levels:\n";
        foreach ($deck->cards as $card) {
            $bondStatus = $card->bond >= 80 ? '✓ Ready' : '✗ Not Ready';
            $turnsNeeded = $turnsEstimates[$card->id] ?? 0;
            $cardName = $card->name ?? "Card #{$card->id}";
            $facilityLabel = ucfirst($card->facility);

            $detailedAnalysis .= "- {$cardName} ({$facilityLabel}): {$card->bond}/80 {$bondStatus}";

            if ($card->bond < 80) {
                $detailedAnalysis .= " - ~{$turnsNeeded} turn(s) needed";

                if ($turnsRemaining > 0 && $turnsNeeded > $turnsRemaining) {
                    $detailedAnalysis .= ' ⚠️ AT RISK';
                }
            }

            $detailedAnalysis .= "\n";
        }

        $detailedAnalysis .= "\n";

        // Add Friendship Training benefits explanation
        $detailedAnalysis .= "Friendship Training Benefits (bond ≥80):\n";
        $detailedAnalysis .= "- Massive stat multiplier bonus (+10% to +35% based on limit breaks)\n";
        $detailedAnalysis .= "- Rainbow glow visual indicator\n";
        $detailedAnalysis .= "- Significantly higher stat gains per training\n";
        $detailedAnalysis .= "- Essential for achieving A+ grade ratings\n\n";

        // Add bond gain mechanics
        $detailedAnalysis .= "Bond Gain Mechanics:\n";
        $detailedAnalysis .= "- Base gain: +7 per training at card's facility\n";
        $detailedAnalysis .= "- With Charming trait: +9 per training\n";
        $detailedAnalysis .= "- With hint mark present: +12 per training\n";
        $detailedAnalysis .= "- Bonds increase when training at the card's assigned facility\n";
        $detailedAnalysis .= "- Multiple cards at same facility all gain bonds simultaneously\n\n";

        // Add strategic recommendations
        $detailedAnalysis .= "Strategic Recommendations:\n";
        $detailedAnalysis .= "- Target Turn 25 for Friendship Training readiness\n";
        $detailedAnalysis .= "- Prioritize facilities with multiple low-bond cards\n";
        $detailedAnalysis .= "- Balance bond building with stat progression\n";
        $detailedAnalysis .= "- Friendship Training provides 2-3x stat gains compared to normal training\n";

        // Add facility distribution analysis
        $distribution = $deck->getFacilityDistribution();
        $detailedAnalysis .= "\nFacility Distribution:\n";
        foreach ($distribution as $facility => $count) {
            if ($count > 0) {
                $facilityLabel = ucfirst($facility);
                $cardsAtFacility = $deck->getCardsAtFacility($facility);
                $notReadyAtFacility = array_filter($cardsAtFacility, fn ($c) => $c->bond < 80);
                $notReadyCount = \count($notReadyAtFacility);

                $detailedAnalysis .= "- {$facilityLabel}: {$count} card(s)";

                if ($notReadyCount > 0) {
                    $detailedAnalysis .= " ({$notReadyCount} not ready)";
                }

                $detailedAnalysis .= "\n";
            }
        }

        // Generate alert message based on situation
        if ($turnsRemaining <= 0) {
            $message = "Bond progress behind schedule - {$cardsNotReadyCount} of {$totalCards} cards still below bond 80 (target: Turn {$targetTurn})";
        } elseif ($turnsRemaining <= 3) {
            $message = "URGENT: {$cardsAtRiskCount} card(s) at risk of missing bond 80 target - only {$turnsRemaining} turn(s) remaining";
        } else {
            $message = "Bond progress behind schedule - {$cardsAtRiskCount} card(s) at risk of missing bond 80 by Turn {$targetTurn}";
        }

        // Determine priority based on urgency
        $priority = match (true) {
            $turnsRemaining <= 0 => Priority::HIGH,
            $turnsRemaining <= 3 => Priority::CRITICAL,
            $cardsAtRiskCount >= ($totalCards / 2) => Priority::HIGH,
            default => Priority::MEDIUM,
        };

        // Create and return the bond behind schedule alert
        return new CriticalAlert(
            type: \App\Enums\AlertType::BOND_BEHIND_SCHEDULE,
            message: $message,
            actionItems: $actionItems,
            turnsUntilCritical: max(0, $turnsRemaining),
            detailedAnalysis: $detailedAnalysis,
            priority: $priority,
            storageMode: $context->storageMode,
        );
    }

    /**
     * Generate bond building recommendations based on card bonds
     *
     * @param  array<\App\ValueObjects\SupportCard>  $cardsNotReady  Cards below bond 80
     * @return array<string> Facility-specific recommendations
     */
    private function generateBondBuildingRecommendations(array $cardsNotReady): array
    {
        $recommendations = [];

        // Group cards by facility
        $cardsByFacility = [];
        foreach ($cardsNotReady as $card) {
            $facility = $card->facility;
            if (! isset($cardsByFacility[$facility])) {
                $cardsByFacility[$facility] = [];
            }
            $cardsByFacility[$facility][] = $card;
        }

        // Sort facilities by number of cards needing bonds (descending)
        uasort($cardsByFacility, fn ($a, $b) => count($b) <=> count($a));

        // Generate recommendations for top facilities
        $count = 0;
        foreach ($cardsByFacility as $facility => $cards) {
            if ($count >= 3) {
                break; // Limit to top 3 facilities
            }

            $facilityLabel = ucfirst($facility);
            $cardCount = count($cards);

            if ($cardCount > 1) {
                $recommendations[] = "Train at {$facilityLabel} facility ({$cardCount} cards need bonds)";
            } else {
                $recommendations[] = "Train at {$facilityLabel} facility (1 card needs bonds)";
            }

            $count++;
        }

        return $recommendations;
    }

    /**
     * Detect facility imbalance situations
     *
     * Checks if facility levels are significantly unbalanced (e.g., Level 5
     * Speed but Level 1 Stamina).
     *
     * Facility levels affect training effectiveness:
     * - Level 1: 1.00x multiplier
     * - Level 2: 1.05x multiplier
     * - Level 3: 1.10x multiplier
     * - Level 4: 1.15x multiplier
     * - Level 5: 1.20x multiplier
     *
     * Progression: Every 4 uses = +1 level, maximum Level 5
     *
     * Alert is triggered when:
     * - Variance between facility levels exceeds threshold (e.g., 3+ levels)
     * - Critical facilities (Speed, Stamina) are significantly behind
     * - Imbalance affects character build goals
     *
     * @param  TrainingContext  $context  Current training context
     * @return CriticalAlert|null Alert if facility imbalance detected, null otherwise
     */
    public function detectFacilityImbalance(TrainingContext $context): ?CriticalAlert
    {
        // Check if facilities are imbalanced using TrainingContext helper
        if (! $context->hasFacilityImbalance()) {
            return null;
        }

        // Get facility level statistics
        $facilityLevels = $context->facilityLevels;
        $minLevel = empty($facilityLevels) ? 1 : min($facilityLevels);
        $maxLevel = empty($facilityLevels) ? 1 : max($facilityLevels);
        $variance = $maxLevel - $minLevel;

        // Get lowest and highest facilities
        $lowestFacility = $context->getLowestFacility();
        $highestFacility = $context->getHighestFacility();

        // Calculate average facility level
        $avgLevel = array_sum($facilityLevels) / count($facilityLevels);

        // Determine severity based on variance
        // Variance > 2 is imbalanced (already checked by hasFacilityImbalance)
        // Variance >= 4 is critically imbalanced
        $isCritical = $variance >= 4;

        // Generate action items based on imbalance
        $actionItems = [];

        if ($isCritical) {
            $actionItems[] = "URGENT: Facility levels severely imbalanced (Level {$minLevel} to {$maxLevel})";
            $actionItems[] = "Focus next 5-8 turns on {$lowestFacility['facility']} training to balance facilities";
        } else {
            $actionItems[] = "Facility levels imbalanced (Level {$minLevel} to {$maxLevel})";
            $actionItems[] = "Diversify training to level up {$lowestFacility['facility']} facility";
        }

        // Add specific facility recommendations
        $actionItems[] = "Train at {$lowestFacility['facility']} facility (currently Level {$lowestFacility['level']})";
        $actionItems[] = 'Every 4 training sessions at a facility increases its level by 1';

        // Check if critical facilities (Speed, Stamina) are behind
        $criticalFacilities = ['speed', 'stamina'];
        $criticalBehind = [];

        foreach ($criticalFacilities as $facility) {
            $level = $facilityLevels[$facility] ?? 1;
            if ($level < $avgLevel - 1) {
                $criticalBehind[] = $facility;
            }
        }

        if (! empty($criticalBehind)) {
            $facilitiesList = implode(' and ', array_map('ucfirst', $criticalBehind));
            $actionItems[] = "PRIORITY: {$facilitiesList} facilities are behind average - these are critical for race performance";
        }

        // Add facility level multiplier explanation
        $actionItems[] = 'Higher facility levels provide better stat multipliers (Level 1: 1.0x → Level 5: 1.20x)';

        // Generate detailed analysis
        $detailedAnalysis = "Facility Level Imbalance Analysis:\n\n";
        $detailedAnalysis .= "Current Facility Levels:\n";

        foreach ($facilityLevels as $facility => $level) {
            $facilityLabel = ucfirst($facility);
            $status = '';

            if ($facility === $lowestFacility['facility']) {
                $status = ' ⚠️ LOWEST';
            } elseif ($facility === $highestFacility['facility']) {
                $status = ' ✓ HIGHEST';
            } elseif (in_array($facility, $criticalBehind, true)) {
                $status = ' ⚠️ CRITICAL BEHIND';
            }

            $detailedAnalysis .= "- {$facilityLabel}: Level {$level}{$status}\n";
        }

        $detailedAnalysis .= "\n";
        $detailedAnalysis .= "Statistics:\n";
        $detailedAnalysis .= "- Minimum Level: {$minLevel} ({$lowestFacility['facility']})\n";
        $detailedAnalysis .= "- Maximum Level: {$maxLevel} ({$highestFacility['facility']})\n";
        $detailedAnalysis .= '- Variance: '.$variance." levels\n";
        $detailedAnalysis .= '- Average Level: '.round($avgLevel, 1)."\n\n";

        $detailedAnalysis .= "Impact of Imbalance:\n";
        $detailedAnalysis .= "- Lower-level facilities provide reduced stat gains\n";
        $detailedAnalysis .= "- Unbalanced training limits character development\n";
        $detailedAnalysis .= "- Missing out on multiplier bonuses from higher facility levels\n";
        $detailedAnalysis .= "- May create stat gaps that affect race performance\n\n";

        $detailedAnalysis .= "Facility Level Multipliers:\n";
        $detailedAnalysis .= "- Level 1: 1.00x (base)\n";
        $detailedAnalysis .= "- Level 2: 1.05x (+5%)\n";
        $detailedAnalysis .= "- Level 3: 1.10x (+10%)\n";
        $detailedAnalysis .= "- Level 4: 1.15x (+15%)\n";
        $detailedAnalysis .= "- Level 5: 1.20x (+20%)\n\n";

        $detailedAnalysis .= "Facility Level Progression:\n";
        $detailedAnalysis .= "- Every 4 training sessions at a facility = +1 level\n";
        $detailedAnalysis .= "- Maximum facility level: 5\n";
        $detailedAnalysis .= "- Training at lower-level facilities is more efficient for leveling\n\n";

        // Calculate training sessions needed to balance
        $sessionsNeeded = ($maxLevel - $minLevel) * 4;
        $detailedAnalysis .= "Balancing Plan:\n";
        $detailedAnalysis .= "- Sessions needed to bring {$lowestFacility['facility']} to Level {$maxLevel}: {$sessionsNeeded}\n";
        $detailedAnalysis .= "- Recommended: Focus on {$lowestFacility['facility']} for next ".ceil($sessionsNeeded / 2)." turns\n";
        $detailedAnalysis .= "- Balance with other training needs (bonds, stats, energy)\n\n";

        $detailedAnalysis .= "Strategic Recommendations:\n";
        $detailedAnalysis .= "- Prioritize training at lower-level facilities when support cards are present\n";
        $detailedAnalysis .= "- Balance facility leveling with stat optimization goals\n";
        $detailedAnalysis .= "- Speed and Stamina facilities are most critical for race performance\n";
        $detailedAnalysis .= "- Aim for all facilities at Level 3+ by mid-Classic Year (Turn 35-40)\n";
        $detailedAnalysis .= "- Balanced facilities provide more flexible training options\n";

        // Generate alert message
        if ($isCritical) {
            $message = "Facility levels severely imbalanced - {$lowestFacility['facility']} at Level {$lowestFacility['level']} vs {$highestFacility['facility']} at Level {$highestFacility['level']}";
        } else {
            $message = "Facility levels imbalanced - recommend diversifying training ({$lowestFacility['facility']} Level {$lowestFacility['level']} vs {$highestFacility['facility']} Level {$highestFacility['level']})";
        }

        // Calculate turns until critical
        // If already critically imbalanced, it's urgent (0 turns)
        // Otherwise, estimate based on variance
        $turnsUntilCritical = $isCritical ? 0 : max(0, (int) floor((4 - $variance) * 3));

        // Create and return the facility imbalance alert
        return new CriticalAlert(
            type: \App\Enums\AlertType::FACILITY_IMBALANCE,
            message: $message,
            actionItems: $actionItems,
            turnsUntilCritical: $turnsUntilCritical,
            detailedAnalysis: $detailedAnalysis,
            priority: $isCritical ? Priority::HIGH : Priority::MEDIUM,
            storageMode: $context->storageMode,
        );
    }

    /**
     * Detect mood issues
     *
     * Checks if character mood is Bad or Very Bad, significantly reducing
     * training effectiveness.
     *
     * Mood effectiveness multipliers:
     * - Very Bad: 0.70x (30% penalty)
     * - Bad: 0.85x (15% penalty)
     * - Normal: 1.00x (baseline)
     * - Good: 1.10x (10% bonus)
     * - Great: 1.20x (20% bonus)
     *
     * Alert is triggered when:
     * - Mood is Bad or Very Bad
     * - Important training or races are upcoming
     * - Mood has been poor for multiple turns
     *
     * @param  TrainingContext  $context  Current training context
     * @return CriticalAlert|null Alert if mood issues detected, null otherwise
     */
    public function detectMoodIssues(TrainingContext $context): ?CriticalAlert
    {
        // TODO: Implement mood detection logic
        // - Check current mood state
        // - Assess mood impact on training effectiveness
        // - Check upcoming important events
        // - Generate recreation recommendations
        // - Return CriticalAlert with mood recovery plan
        return null;
    }

    /**
     * Detect race unready situations
     *
     * Checks if character stats are insufficient for upcoming race requirements
     * (distance, competition level, terrain).
     *
     * Alert is triggered when:
     * - Stats below race requirements
     * - Win probability below acceptable threshold
     * - Critical skills missing
     * - Aptitude grades insufficient
     *
     * @param  TrainingContext  $context  Current training context
     * @return CriticalAlert|null Alert if race unready detected, null otherwise
     */
    public function detectRaceUnready(TrainingContext $context): ?CriticalAlert
    {
        // TODO: Implement race unready detection logic
        // - Check upcoming races
        // - Calculate win probability using GameMechanicsEngine
        // - Assess stat gaps
        // - Check skill requirements
        // - Generate preparation recommendations
        // - Return CriticalAlert with race readiness plan
        return null;
    }

    /**
     * Detect team race unprepared situations (Unity Cup scenario)
     *
     * Checks if Unity Cup scenario team race requirements are not met.
     *
     * Unity Cup specific mechanics:
     * - Team race coordination
     * - Unique scenario bonuses
     * - Different progression requirements
     *
     * Alert is triggered when:
     * - Team race requirements not met
     * - Scenario-specific mechanics not optimized
     * - Team composition issues
     *
     * @param  TrainingContext  $context  Current training context
     * @return CriticalAlert|null Alert if team race unprepared, null otherwise
     */
    public function detectTeamRaceUnprepared(TrainingContext $context): ?CriticalAlert
    {
        // TODO: Implement team race unprepared detection logic
        // - Check if Unity Cup scenario is active
        // - Assess team race requirements
        // - Check scenario-specific mechanics
        // - Generate preparation checklist
        // - Return CriticalAlert with team race preparation plan
        return null;
    }

    /**
     * Detect all critical situations
     *
     * Runs all detection methods and returns a collection of critical alerts
     * prioritized by severity and urgency.
     *
     * Detection methods called:
     * - detectStaminaCrisis()
     * - detectSpShortage()
     * - detectEnergyCritical()
     * - detectBondBehindSchedule()
     * - detectFacilityImbalance()
     * - detectMoodIssues()
     * - detectRaceUnready()
     * - detectTeamRaceUnprepared()
     *
     * @param  TrainingContext  $context  Current training context
     * @return array<CriticalAlert> Array of critical alerts, prioritized by severity
     */
    public function detectAll(TrainingContext $context): array
    {
        $alerts = [];

        // Run all detection methods
        if ($alert = $this->detectStaminaCrisis($context)) {
            $alerts[] = $alert;
        }

        if ($alert = $this->detectSpShortage($context)) {
            $alerts[] = $alert;
        }

        if ($alert = $this->detectEnergyCritical($context)) {
            $alerts[] = $alert;
        }

        if ($alert = $this->detectBondBehindSchedule($context)) {
            $alerts[] = $alert;
        }

        if ($alert = $this->detectFacilityImbalance($context)) {
            $alerts[] = $alert;
        }

        if ($alert = $this->detectMoodIssues($context)) {
            $alerts[] = $alert;
        }

        if ($alert = $this->detectRaceUnready($context)) {
            $alerts[] = $alert;
        }

        if ($alert = $this->detectTeamRaceUnprepared($context)) {
            $alerts[] = $alert;
        }

        // Sort alerts by priority (CRITICAL first, then HIGH, MEDIUM, LOW)
        usort($alerts, function (CriticalAlert $a, CriticalAlert $b) {
            return $this->comparePriority($a->priority, $b->priority);
        });

        return $alerts;
    }

    /**
     * Compare two priority levels for sorting
     *
     * Returns negative if $a has higher priority than $b,
     * positive if $b has higher priority than $a,
     * zero if equal priority.
     *
     * Priority order: CRITICAL > HIGH > MEDIUM > LOW
     *
     * @param  Priority  $a  First priority
     * @param  Priority  $b  Second priority
     * @return int Comparison result
     */
    private function comparePriority(Priority $a, Priority $b): int
    {
        $priorityOrder = [
            Priority::CRITICAL->value => 0,
            Priority::HIGH->value => 1,
            Priority::MEDIUM->value => 2,
            Priority::LOW->value => 3,
        ];

        return $priorityOrder[$a->value] <=> $priorityOrder[$b->value];
    }
}
