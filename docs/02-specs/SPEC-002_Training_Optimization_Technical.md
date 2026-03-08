# SPEC-002: Training Optimization System - Technical Specification

**Document Version**: 2.2.0  
**Date**: 2026-01-28  
**Project**: Umamusume Pretty Derby Career Planner  
**Status**: Active - Updated with game-accurate training formula  
**Classification**: Internal - Development Team

---

## Document Information

| Attribute | Value |
| --- | --- |
| **Document ID** | SPEC-002 |
| **Related PRD** | [PRD-002: Training Optimization](../prds/PRD-002_Training_Optimization.md) |
| **Architecture Version** | v2.2.0 |
| **Approval Status** | Approved |
| **Last Reviewed** | 2026-01-28 |

### Related Documents

**Requirements & Design**:

- [SRS Section 3.2: Training System](../003_SRS_Software_Requirement_Specifications.md#32-training-system)
- [SDS Section 4.2: Training Architecture](../004_SDS_Software_Design_Specifications.md#42-training-module)

**Data & Integration**:

- [DBD Section 5.2: Training Tables](../009_DBD_Database_Documentation.md#52-training-tables)
- [API Section 4.2: Training Endpoints](../010_API_API_Documentation.md#42-training-endpoints)

**Visual Documentation**:

- [FLOW-002: Training Optimization System](../flows/FLOW-002_Training_Optimization_System.md)
- [SEQ-002: Training Session Execution](../sequences/SEQ-002_Training_Session_Execution.md)
- [WF-004: Training Optimizer Interface](../wireframes/WF-004_Training_Optimizer_Interface.md)
- [UF-003: Training Optimization Flow](../user-flows/UF-003_Training_Optimization_Flow.md)

---

## Table of Contents

1. [Technical Overview](#1-technical-overview)
2. [Architecture Design](#2-architecture-design)
3. [Calculation Engines](#3-calculation-engines)
4. [Service Layer](#4-service-layer)
5. [API Specification](#5-api-specification)
6. [Database Schema](#6-database-schema)
7. [AI Integration](#7-ai-integration)
8. [Business Logic](#8-business-logic)
9. [Integration Points](#9-integration-points)
10. [Error Handling](#10-error-handling)
11. [Performance Optimization](#11-performance-optimization)
12. [Security Considerations](#12-security-considerations)
13. [Testing Strategy](#13-testing-strategy)
14. [Appendices](#14-appendices)

---

## 1. Technical Overview

### 1.1 Module Purpose

The Training Optimization System is the core gameplay simulation engine responsible for
predicting training outcomes, calculating stat gains, assessing failure risks, and
generating AI-driven recommendations. It orchestrates the interaction between character
state, support cards, and training facilities to maximize career run efficiency.

**Core Responsibilities**:

- Stat gain prediction with support card bonuses
- Failure risk calculation based on energy and mood
- Training option ranking and recommendations
- Training session execution and validation
- Bond level tracking with support cards
- Skill hint probability calculations
- Integration with Neuron AI for intelligent advisories

### 1.2 Business Context

In Umamusume Pretty Derby, each turn during a career run presents 5 training facility
options plus a Rest action. Players must optimize their choices based on:

- **Current Stats**: Character's progression toward goals
- **Energy Level**: Affects failure risk and stat gains
- **Mood Status**: Modifies training effectiveness
- **Support Cards**: Provide bonuses when present at facilities
- **Friendship Training**: Enhanced gains at 80+ bond level
- **Upcoming Races**: Strategic timing for race preparation

This module provides deterministic predictions to enable informed decision-making.

### 1.3 Technical Scope

**In Scope**:

- Training prediction calculations for all 6 action types
- Support card bonus aggregation
- Risk assessment algorithms
- Training session execution with RNG resolution
- Bond level progression tracking
- Skill hint generation
- AI recommendation integration
- Performance caching for predictions

**Out of Scope**:

- Character creation (SPEC-001)
- Race execution (SPEC-003)
- Skill acquisition (SPEC-004)
- Support deck configuration (SPEC-005)

### 1.4 Technology Stack

| Component | Technology | Version | Purpose |
| --- | --- | --- | --- |
| **Framework** | Laravel | 12.x | Application foundation |
| **Language** | PHP | 8.3+ | Server-side logic |
| **Database** | MySQL | 8.0+ | Data persistence |
| **Cache** | Redis | 7.x | Prediction caching |
| **AI** | Neuron Framework | 1.x | Advisory agents |
| **AI Provider (Local)** | Ollama | Latest | Local recommendations |
| **AI Provider (Cloud)** | AWS Bedrock Claude | 4.5 | Complex analysis |

---

## 2. Architecture Design

### 2.1 Component Architecture

```mermaid
graph TB
    subgraph "Presentation Layer"
        API[TrainingController]
        Livewire[TrainingOptimizer Component]
        FormRequest[TrainingRequest]
    end

    subgraph "Application Layer"
        TrainingSvc[TrainingService]
        PredictionSvc[TrainingPredictionService]
        AdvisorySvc[AIAdvisoryService]
    end

    subgraph "Domain Layer"
        Session[TrainingSession Model]
        Prediction[Prediction DTO]
        Calculators[Calculator Services]
    end

    subgraph "Infrastructure Layer"
        DB[(MySQL)]
        Cache[(Redis)]
        NeuronAI[Neuron AI Agent]
    end

    API --> FormRequest
    FormRequest --> TrainingSvc
    Livewire --> PredictionSvc
    
    TrainingSvc --> Session
    TrainingSvc --> DB
    
    PredictionSvc --> Calculators
    PredictionSvc --> Cache
    
    AdvisorySvc --> NeuronAI
    AdvisorySvc --> PredictionSvc
    
    Calculators --> StatGainCalc[StatGainCalculator]
    Calculators --> BonusCalc[SupportBonusCalculator]
    Calculators --> RiskCalc[RiskCalculator]
    Calculators --> HintCalc[SkillHintCalculator]
```text

### 2.2 Layer Responsibilities

**Presentation Layer**:

- HTTP request/response handling
- Training action validation
- Real-time prediction updates via Livewire

**Application Layer**:

- Training workflow orchestration
- Prediction generation and caching
- AI advisory coordination

**Domain Layer**:

- Training business rules
- Calculation algorithms
- Data transfer objects

**Infrastructure Layer**:

- Database persistence
- Cache management
- AI service integration

### 2.3 Design Patterns

| Pattern | Implementation | Purpose |
| --- | --- | --- |
| **Strategy** | Calculator interfaces | Pluggable calculation algorithms |
| **DTO** | `TrainingPrediction` | Immutable prediction data |
| **Repository** | `TrainingSessionRepository` | Data access abstraction |
| **Service Layer** | `TrainingService` | Business logic encapsulation |
| **Cache-Aside** | Redis predictions | Performance optimization |
| **Chain of Responsibility** | Risk calculators | Modular risk assessment |

---

## 3. Calculation Engines

### 3.1 Stat Gain Calculator

The `StatGainCalculator` computes raw stat increases based on facility type, character
growth rates, facility level, and current training conditions.

```php
<?php

namespace App\Services\Training\Calculators;

use App\Models\Character;
use App\Enums\TrainingType;
use App\ValueObjects\StatCollection;

/**
 * Stat Gain Calculator
 * 
 * Calculates base stat gains for training facilities using game-accurate formula.
 * 
 * Game-Accurate Training Formula:
 * Stat Gain = (Base + StatBonus) × (1 + GrowthRate) × (1 + MoodMultiplier × (1 + MoodEffect)) 
 *             × (1 + TrainingEffect) × (1 + 0.05 × NumSupportCards) × FriendshipMultiplier
 * 
 * Per-training cap: +100 (reduced to +50 if stat > 1200)
 */
class StatGainCalculator
{
    /**
     * Base stat gains per training type
     */
    private const FACILITY_BASE_GAINS = [
        'speed' => ['speed' => 10, 'power' => 5],
        'stamina' => ['stamina' => 10, 'guts' => 5],
        'power' => ['power' => 10, 'stamina' => 5],
        'guts' => ['guts' => 10, 'speed' => 5],
        'wisdom' => ['wit' => 10, 'speed' => 2],
    ];

    /**
     * Facility level multipliers (Level 1-5)
     */
    private const LEVEL_MULTIPLIERS = [
        1 => 1.0,
        2 => 1.1,
        3 => 1.2,
        4 => 1.3,
        5 => 1.5,
    ];
    
    /**
     * Per-training stat gain caps
     */
    private const PER_TRAINING_CAP = 100;
    private const PER_TRAINING_CAP_ABOVE_SOFT = 50;
    private const SOFT_CAP = 1200;

    /**
     * Calculate base stat gains using game-accurate formula
     * 
     * @param TrainingType $type Training facility type
     * @param Character $character Character instance
     * @param int $facilityLevel Facility level (1-5)
     * @return StatCollection
     */
    public function calculateBase(
        TrainingType $type,
        Character $character,
        int $facilityLevel = 1
    ): StatCollection {
        $baseGains = self::FACILITY_BASE_GAINS[$type->value] ?? [];
        $growthRates = $character->growth_rates;
        
        // Apply facility level multiplier
        $levelMultiplier = self::LEVEL_MULTIPLIERS[$facilityLevel] ?? 1.0;

        $gains = [];
        
        foreach ($baseGains as $stat => $baseValue) {
            // Apply growth rate bonus (e.g., 20% = 1.20x)
            $growthBonus = 1 + (($growthRates[$stat] ?? 0) / 100);
            
            // Calculate final gain
            $finalGain = $baseValue * $levelMultiplier * $growthBonus;
            
            // Apply per-training cap based on current stat
            $currentStat = $character->current_stats[$stat] ?? 0;
            $cap = $currentStat > self::SOFT_CAP 
                ? self::PER_TRAINING_CAP_ABOVE_SOFT 
                : self::PER_TRAINING_CAP;
            
            $gains[$stat] = (int) min($cap, round($finalGain));
        }

        return new StatCollection($gains);
    }

    /**
     * Calculate gains with all modifiers applied (game-accurate formula)
     * 
     * Formula: (Base + StatBonus) × (1 + GrowthRate) × (1 + MoodMultiplier × (1 + MoodEffect)) 
     *          × (1 + TrainingEffect) × (1 + 0.05 × NumSupportCards) × FriendshipMultiplier
     * 
     * @param TrainingType $type
     * @param Character $character
     * @param int $facilityLevel
     * @param float $moodMultiplier
     * @param float $supportMultiplier
     * @param int $numSupportCards Number of support cards at facility
     * @param bool $isFriendshipTraining Whether friendship training is active
     * @return StatCollection
     */
    public function calculateWithModifiers(
        TrainingType $type,
        Character $character,
        int $facilityLevel,
        float $moodMultiplier,
        float $supportMultiplier,
        int $numSupportCards = 0,
        bool $isFriendshipTraining = false
    ): StatCollection {
        $baseGains = $this->calculateBase($type, $character, $facilityLevel);
        
        // Support card presence bonus: +5% per card
        $cardPresenceBonus = 1 + (0.05 * $numSupportCards);
        
        // Friendship training multiplier (1.2x when bond >= 80)
        $friendshipMultiplier = $isFriendshipTraining ? 1.2 : 1.0;
        
        $modifiedGains = [];
        
        foreach ($baseGains->toArray() as $stat => $value) {
            $modified = $value * $moodMultiplier * $supportMultiplier * $cardPresenceBonus * $friendshipMultiplier;
            
            // Apply per-training cap
            $currentStat = $character->current_stats[$stat] ?? 0;
            $cap = $currentStat > self::SOFT_CAP 
                ? self::PER_TRAINING_CAP_ABOVE_SOFT 
                : self::PER_TRAINING_CAP;
            
            $modifiedGains[$stat] = (int) min($cap, round($modified));
        }
        
        return new StatCollection($modifiedGains);
    }
}
```

### 3.2 Support Bonus Calculator

Aggregates bonuses from support cards present at a training facility.

```php
<?php

namespace App\Services\Training\Calculators;

use App\Models\CareerRun;
use App\Models\SupportDeck;
use App\Enums\TrainingType;

/**
 * Support Bonus Calculator
 * 
 * Calculates stat bonuses from support cards at training facilities.
 */
class SupportBonusCalculator
{
    /**
     * Friendship training bond threshold
     */
    private const FRIENDSHIP_THRESHOLD = 80;

    /**
     * Friendship training multiplier
     */
    private const FRIENDSHIP_MULTIPLIER = 1.2;

    /**
     * Calculate total support bonuses for a facility
     * 
     * @param CareerRun $career
     * @param TrainingType $type
     * @param array $cardsAtFacility Card IDs present at facility
     * @return array{multiplier: float, is_friendship: bool, cards: array}
     */
    public function calculate(
        CareerRun $career,
        TrainingType $type,
        array $cardsAtFacility
    ): array {
        $deck = $career->supportDeck;
        
        if (!$deck) {
            return [
                'multiplier' => 1.0,
                'is_friendship' => false,
                'cards' => [],
            ];
        }

        $totalBonus = 0;
        $isFriendship = false;
        $activeCards = [];

        foreach ($cardsAtFacility as $cardId) {
            $card = $deck->cards()->find($cardId);
            
            if (!$card) {
                continue;
            }

            $bondLevel = $card->pivot->bond_level ?? 0;
            
            // Get base bonus for this card's specialization
            $baseBonus = $this->getCardBonus($card, $type);
            $totalBonus += $baseBonus;

            // Check for friendship training
            if ($bondLevel >= self::FRIENDSHIP_THRESHOLD) {
                $isFriendship = true;
            }

            $activeCards[] = [
                'id' => $card->id,
                'name' => $card->name,
                'bond_level' => $bondLevel,
                'bonus' => $baseBonus,
            ];
        }

        // Convert bonus percentage to multiplier
        $multiplier = 1.0 + ($totalBonus / 100);

        // Apply friendship multiplier if triggered
        if ($isFriendship) {
            $multiplier *= self::FRIENDSHIP_MULTIPLIER;
        }

        return [
            'multiplier' => $multiplier,
            'is_friendship' => $isFriendship,
            'cards' => $activeCards,
        ];
    }

    /**
     * Get bonus value for a specific card and training type
     * 
     * @param \App\Models\SupportCard $card
     * @param TrainingType $type
     * @return int
     */
    private function getCardBonus($card, TrainingType $type): int
    {
        // Match card specialization to training type
        if (strtolower($card->specialization) === $type->value) {
            // Base bonus for matching specialization
            $baseBonus = match ($card->rarity) {
                'SSR' => 10,
                'SR' => 7,
                'R' => 5,
                default => 3,
            };

            // Apply limit break multiplier
            $limitBreakBonus = $card->limit_breaks * 2;

            return $baseBonus + $limitBreakBonus;
        }

        return 0;
    }
}
```text

### 3.3 Risk Calculator

Determines failure probability based on energy, mood, and training type.

```php
<?php

namespace App\Services\Training\Calculators;

use App\Models\Character;
use App\Enums\TrainingType;
use App\Enums\MoodStatus;

/**
 * Training Risk Calculator
 * 
 * Calculates failure probability for training actions.
 */
class RiskCalculator
{
    /**
     * Calculate training failure risk
     * 
     * @param Character $character
     * @param TrainingType $type
     * @return array{risk: float, factors: array}
     */
    public function calculate(Character $character, TrainingType $type): array
    {
        $baseRisk = $this->calculateBaseRisk($character->energy_level);
        $moodModifier = $this->getMoodModifier($character->mood_status);
        $typeModifier = $this->getTrainingTypeModifier($type);
        
        // Combine risk factors
        $totalRisk = ($baseRisk + $moodModifier + $typeModifier);
        
        // Clamp to valid range (0-100%)
        $totalRisk = max(0, min(100, $totalRisk));

        return [
            'risk' => $totalRisk,
            'factors' => [
                'energy_risk' => $baseRisk,
                'mood_modifier' => $moodModifier,
                'type_modifier' => $typeModifier,
            ],
        ];
    }

    /**
     * Calculate base risk from energy level
     * 
     * Energy > 50: 0% risk
     * Energy 30-50: Linear 0% -> 15%
     * Energy < 30: Exponential 15% -> 70%
     * 
     * @param int $energy Energy level (0-100)
     * @return float
     */
    private function calculateBaseRisk(int $energy): float
    {
        if ($energy > 50) {
            return 0.0;
        }

        if ($energy >= 30) {
            // Linear interpolation from 0% at 50 to 15% at 30
            return 15 * (50 - $energy) / 20;
        }

        // Exponential growth below 30
        // Formula: 15 + 55 * ((30 - energy) / 30)^2
        $factor = (30 - $energy) / 30;
        return 15 + (55 * pow($factor, 2));
    }

    /**
     * Get mood modifier for risk
     * 
     * @param MoodStatus $mood
     * @return float
     */
    private function getMoodModifier(MoodStatus $mood): float
    {
        return match ($mood) {
            MoodStatus::Awful => 10.0,
            MoodStatus::Bad => 5.0,
            MoodStatus::Normal => 0.0,
            MoodStatus::Good => -2.0,
            MoodStatus::Great => -5.0,
        };
    }

    /**
     * Get training type modifier
     * 
     * @param TrainingType $type
     * @return float
     */
    private function getTrainingTypeModifier(TrainingType $type): float
    {
        return match ($type) {
            TrainingType::Wisdom => -5.0, // Wisdom has lower risk
            TrainingType::Rest => -100.0, // Rest has no risk
            default => 0.0,
        };
    }

    /**
     * Resolve training outcome based on risk
     * 
     * @param float $riskPercentage
     * @return bool True if successful, false if failed
     */
    public function resolveOutcome(float $riskPercentage): bool
    {
        $roll = mt_rand(1, 10000) / 100; // Random 0.00-100.00
        
        return $roll > $riskPercentage;
    }
}
```

### 3.4 Skill Hint Calculator

Calculates probability of receiving skill hints from support cards.

```php
<?php

namespace App\Services\Training\Calculators;

use App\Models\CareerRun;
use App\Models\SupportCard;

/**
 * Skill Hint Probability Calculator
 * 
 * Calculates chances of receiving skill hints during training.
 */
class SkillHintCalculator
{
    /**
     * Calculate skill hint probability
     * 
     * @param CareerRun $career
     * @param array $cardsAtFacility
     * @return array{hints: array, probability: float}
     */
    public function calculate(CareerRun $career, array $cardsAtFacility): array
    {
        $deck = $career->supportDeck;
        
        if (!$deck) {
            return ['hints' => [], 'probability' => 0.0];
        }

        $possibleHints = [];
        $totalProbability = 0;

        foreach ($cardsAtFacility as $cardId) {
            $card = $deck->cards()->find($cardId);
            
            if (!$card) {
                continue;
            }

            $hintRate = $card->hint_rate ?? 10; // Base 10%
            $bondBonus = ($card->pivot->bond_level ?? 0) / 10; // +1% per 10 bond
            
            $finalRate = min(50, $hintRate + $bondBonus); // Cap at 50%

            // Get skills this card can hint
            $skills = $this->getCardSkills($card, $career);

            foreach ($skills as $skill) {
                $possibleHints[] = [
                    'skill_id' => $skill['id'],
                    'skill_name' => $skill['name'],
                    'probability' => $finalRate,
                    'source_card' => $card->name,
                ];

                $totalProbability = max($totalProbability, $finalRate);
            }
        }

        return [
            'hints' => $possibleHints,
            'probability' => $totalProbability,
        ];
    }

    /**
     * Get skills a card can provide hints for
     * 
     * @param SupportCard $card
     * @param CareerRun $career
     * @return array
     */
    private function getCardSkills(SupportCard $card, CareerRun $career): array
    {
        // Get skills from card metadata
        $cardSkills = $card->skills_provided ?? [];
        
        // Filter out skills already owned by character
        $ownedSkills = $career->character->skills->pluck('id')->toArray();
        
        return collect($cardSkills)
            ->reject(fn($skill) => in_array($skill['id'], $ownedSkills))
            ->values()
            ->toArray();
    }
}
```text

---

## 4. Service Layer

### 4.1 TrainingPredictionService

Generates forecast data for all training options for the current turn.

```php
<?php

namespace App\Services\Training;

use App\Models\CareerRun;
use App\DTOs\TrainingPrediction;
use App\Enums\TrainingType;
use App\Services\Training\Calculators\{
    StatGainCalculator,
    SupportBonusCalculator,
    RiskCalculator,
    SkillHintCalculator
};
use Illuminate\Support\Facades\Cache;

/**
 * Training Prediction Service
 * 
 * Generates predictions for all available training options.
 */
class TrainingPredictionService
{
    public function __construct(
        private StatGainCalculator $statCalc,
        private SupportBonusCalculator $bonusCalc,
        private RiskCalculator $riskCalc,
        private SkillHintCalculator $hintCalc
    ) {}

    /**
     * Get predictions for all training options
     * 
     * @param CareerRun $career
     * @return array<TrainingPrediction>
     */
    public function getPredictions(CareerRun $career): array
    {
        $cacheKey = "training:predictions:{$career->id}:{$career->current_turn}";

        return Cache::tags(['training', "career:{$career->id}"])->remember(
            $cacheKey,
            now()->addMinutes(5),
            fn() => $this->generatePredictions($career)
        );
    }

    /**
     * Generate fresh predictions
     * 
     * @param CareerRun $career
     * @return array<TrainingPrediction>
     */
    private function generatePredictions(CareerRun $career): array
    {
        $character = $career->character;
        $predictions = [];

        // Generate predictions for each training type
        foreach (TrainingType::cases() as $type) {
            if ($type === TrainingType::Rest) {
                $predictions[] = $this->predictRest($character);
                continue;
            }

            $predictions[] = $this->predictTraining($career, $type);
        }

        // Sort by recommendation score
        usort($predictions, fn($a, $b) => $b->score <=> $a->score);

        return $predictions;
    }

    /**
     * Predict training outcome
     * 
     * @param CareerRun $career
     * @param TrainingType $type
     * @return TrainingPrediction
     */
    private function predictTraining(CareerRun $career, TrainingType $type): TrainingPrediction
    {
        $character = $career->character;
        
        // Simulate card distribution (in real scenario, this comes from game state)
        $cardsAtFacility = $this->getCardsAtFacility($career, $type);

        // Calculate support bonuses
        $supportData = $this->bonusCalc->calculate($career, $type, $cardsAtFacility);

        // Calculate stat gains
        $gains = $this->statCalc->calculateWithModifiers(
            $type,
            $character,
            $career->facility_levels[$type->value] ?? 1,
            $character->mood_status->getMultiplier(),
            $supportData['multiplier']
        );

        // Calculate risk
        $riskData = $this->riskCalc->calculate($character, $type);

        // Calculate skill hints
        $hintData = $this->hintCalc->calculate($career, $cardsAtFacility);

        // Calculate energy cost
        $energyCost = $this->calculateEnergyCost($type, $supportData['is_friendship']);

        // Calculate recommendation score
        $score = $this->calculateScore($gains, $riskData['risk'], $energyCost, $career);

        return new TrainingPrediction(
            type: $type->value,
            gains: $gains->toArray(),
            energyCost: $energyCost,
            risk: $riskData['risk'],
            riskFactors: $riskData['factors'],
            hints: $hintData['hints'],
            hintProbability: $hintData['probability'],
            supportCards: $supportData['cards'],
            isFriendship: $supportData['is_friendship'],
            bondGains: $this->calculateBondGains($supportData['cards']),
            score: $score
        );
    }

    /**
     * Predict rest outcome
     * 
     * @param \App\Models\Character $character
     * @return TrainingPrediction
     */
    private function predictRest($character): TrainingPrediction
    {
        $energyGain = 50;
        
        // Bonus energy in good mood
        if (in_array($character->mood_status, [MoodStatus::Good, MoodStatus::Great])) {
            $energyGain += 10;
        }

        return new TrainingPrediction(
            type: 'rest',
            gains: [],
            energyCost: -$energyGain,
            risk: 0,
            riskFactors: [],
            hints: [],
            hintProbability: 0,
            supportCards: [],
            isFriendship: false,
            bondGains: [],
            score: $this->calculateRestScore($character, $energyGain)
        );
    }

    /**
     * Calculate energy cost for training
     * 
     * @param TrainingType $type
     * @param bool $isFriendship
     * @return int
     */
    private function calculateEnergyCost(TrainingType $type, bool $isFriendship): int
    {
        $baseCost = match ($type) {
            TrainingType::Speed => 20,
            TrainingType::Stamina => 22,
            TrainingType::Power => 24,
            TrainingType::Guts => 18,
            TrainingType::Wisdom => 15,
            default => 0,
        };

        // Friendship training reduces cost
        if ($isFriendship) {
            $baseCost = (int) ($baseCost * 0.8);
        }

        return $baseCost;
    }

    /**
     * Calculate bond gains for active cards
     * 
     * @param array $cards
     * @return array
     */
    private function calculateBondGains(array $cards): array
    {
        return array_map(function ($card) {
            $baseGain = 5;
            
            // Higher gains for lower bond levels
            if ($card['bond_level'] < 50) {
                $baseGain = 7;
            }

            return [
                'card_id' => $card['id'],
                'gain' => $baseGain,
            ];
        }, $cards);
    }

    /**
     * Calculate recommendation score
     * 
     * @param StatCollection $gains
     * @param float $risk
     * @param int $energyCost
     * @param CareerRun $career
     * @return int
     */
    private function calculateScore(
        $gains,
        float $risk,
        int $energyCost,
        CareerRun $career
    ): int {
        $score = 0;

        // Stat gain value
        foreach ($gains->toArray() as $stat => $value) {
            $score += $value * $this->getStatPriority($stat, $career);
        }

        // Risk penalty
        $score -= (int) ($risk * 2);

        // Energy efficiency
        if ($energyCost > 0) {
            $score -= (int) ($energyCost * 0.5);
        }

        return max(0, $score);
    }

    /**
     * Get stat priority based on career goals
     * 
     * @param string $stat
     * @param CareerRun $career
     * @return float
     */
    private function getStatPriority(string $stat, CareerRun $career): float
    {
        $goals = $career->character->goals ?? [];
        
        foreach ($goals as $goal) {
            if ($goal['type'] === 'stat' && $goal['target'] === $stat) {
                return 1.5; // Prioritize goal stats
            }
        }

        return 1.0;
    }

    /**
     * Calculate rest recommendation score
     * 
     * @param \App\Models\Character $character
     * @param int $energyGain
     * @return int
     */
    private function calculateRestScore($character, int $energyGain): int
    {
        // Rest is highly recommended when energy is low
        if ($character->energy_level < 30) {
            return 90;
        }

        if ($character->energy_level < 50) {
            return 60;
        }

        return 30;
    }

    /**
     * Get cards present at facility (simulation)
     * 
     * @param CareerRun $career
     * @param TrainingType $type
     * @return array
     */
    private function getCardsAtFacility(CareerRun $career, TrainingType $type): array
    {
        $deck = $career->supportDeck;
        
        if (!$deck) {
            return [];
        }

        // Filter cards by specialization matching training type
        return $deck->cards()
            ->where('specialization', ucfirst($type->value))
            ->pluck('id')
            ->toArray();
    }
}
```

### 4.2 TrainingService

Handles execution of training actions with validation and persistence.

```php
<?php

namespace App\Services\Training;

use App\Models\{CareerRun, TrainingSession};
use App\DTOs\TrainingResult;
use App\Enums\TrainingType;
use App\Services\Training\Calculators\RiskCalculator;
use App\Events\TrainingCompleted;
use Illuminate\Support\Facades\{DB, Cache};

/**
 * Training Execution Service
 * 
 * Handles training action execution and state updates.
 */
class TrainingService
{
    public function __construct(
        private TrainingPredictionService $predictionService,
        private RiskCalculator $riskCalc
    ) {}

    /**
     * Execute training action
     * 
     * @param CareerRun $career
     * @param string $facilityType
     * @return TrainingResult
     * @throws \App\Exceptions\InvalidTrainingException
     */
    public function executeTraining(CareerRun $career, string $facilityType): TrainingResult
    {
        return DB::transaction(function () use ($career, $facilityType) {
            // Validate turn limit
            $this->validateTurnLimit($career);

            // Get prediction
            $predictions = $this->predictionService->getPredictions($career);
            $prediction = collect($predictions)->firstWhere('type', $facilityType);

            if (!$prediction) {
                throw new \App\Exceptions\InvalidTrainingException(
                    "Invalid training type: {$facilityType}"
                );
            }

            // Resolve RNG for success/failure
            $isSuccess = $this->riskCalc->resolveOutcome($prediction->risk);

            // Apply results
            if ($isSuccess) {
                $result = $this->applySuccess($career, $prediction);
            } else {
                $result = $this->applyFailure($career, $prediction);
            }

            // Create training session record
            $session = $this->createSession($career, $prediction, $result);

            // Update career state
            $this->updateCareerState($career, $result);

            // Invalidate cache
            Cache::tags(["career:{$career->id}"])->flush();

            // Fire event
            event(new TrainingCompleted($career, $session));

            return $result;
        });
    }

    /**
     * Validate turn limit
     * 
     * @param CareerRun $career
     * @return void
     * @throws \App\Exceptions\InvalidTrainingException
     */
    private function validateTurnLimit(CareerRun $career): void
    {
        if ($career->current_turn >= $career->max_turns) {
            throw new \App\Exceptions\InvalidTrainingException(
                'Career run has reached maximum turns'
            );
        }
    }

    /**
     * Apply successful training results
     * 
     * @param CareerRun $career
     * @param TrainingPrediction $prediction
     * @return TrainingResult
     */
    private function applySuccess(CareerRun $career, $prediction): TrainingResult
    {
        $character = $career->character;

        // Apply stat gains
        foreach ($prediction->gains as $stat => $value) {
            $character->updateStats([$stat => $value]);
        }

        // Reduce energy
        $character->energy_level = $character->clampEnergy(
            $character->energy_level - $prediction->energyCost
        );

        // Apply bond gains
        if ($prediction->bondGains) {
            $this->applyBondGains($career, $prediction->bondGains);
        }

        // Roll for skill hints
        $hintsGained = $this->rollSkillHints($prediction);

        $character->save();

        return new TrainingResult(
            outcome: 'success',
            statDeltas: $prediction->gains,
            energyDelta: -$prediction->energyCost,
            bondGains: $prediction->bondGains,
            hintsGained: $hintsGained,
            turn: $career->current_turn + 1
        );
    }

    /**
     * Apply failure penalties
     * 
     * @param CareerRun $career
     * @param TrainingPrediction $prediction
     * @return TrainingResult
     */
    private function applyFailure(CareerRun $career, $prediction): TrainingResult
    {
        $character = $career->character;

        // Reduce stats slightly
        $penalties = [];
        foreach ($prediction->gains as $stat => $value) {
            $penalty = (int) ($value * 0.3); // 30% of expected gain
            $penalties[$stat] = -$penalty;
            $character->updateStats([$stat => -$penalty]);
        }

        // Reduce energy more
        $energyPenalty = (int) ($prediction->energyCost * 1.5);
        $character->energy_level = $character->clampEnergy(
            $character->energy_level - $energyPenalty
        );

        // Mood may drop
        if ($character->mood_status !== MoodStatus::Awful) {
            // 30% chance to drop mood
            if (mt_rand(1, 100) <= 30) {
                $character->mood_status = match ($character->mood_status) {
                    MoodStatus::Great => MoodStatus::Good,
                    MoodStatus::Good => MoodStatus::Normal,
                    MoodStatus::Normal => MoodStatus::Bad,
                    MoodStatus::Bad => MoodStatus::Awful,
                    default => $character->mood_status,
                };
            }
        }

        $character->save();

        return new TrainingResult(
            outcome: 'failure',
            statDeltas: $penalties,
            energyDelta: -$energyPenalty,
            bondGains: [],
            hintsGained: [],
            turn: $career->current_turn + 1
        );
    }

    /**
     * Create training session record
     * 
     * @param CareerRun $career
     * @param TrainingPrediction $prediction
     * @param TrainingResult $result
     * @return TrainingSession
     */
    private function createSession(
        CareerRun $career,
        $prediction,
        TrainingResult $result
    ): TrainingSession {
        return TrainingSession::create([
            'career_id' => $career->id,
            'turn_number' => $career->current_turn + 1,
            'training_type' => $prediction->type,
            'stat_gains' => $result->statDeltas,
            'support_bonuses' => $prediction->supportCards,
            'skill_hints_gained' => $result->hintsGained,
            'success_rate' => 100 - $prediction->risk,
            'was_successful' => $result->outcome === 'success',
            'energy_delta' => $result->energyDelta,
        ]);
    }

    /**
     * Update career run state
     * 
     * @param CareerRun $career
     * @param TrainingResult $result
     * @return void
     */
    private function updateCareerState(CareerRun $career, TrainingResult $result): void
    {
        $career->increment('current_turn');
        $career->save();
    }

    /**
     * Apply bond gains to support deck
     * 
     * @param CareerRun $career
     * @param array $bondGains
     * @return void
     */
    private function applyBondGains(CareerRun $career, array $bondGains): void
    {
        foreach ($bondGains as $bondGain) {
            $career->supportDeck->cards()
                ->updateExistingPivot($bondGain['card_id'], [
                    'bond_level' => DB::raw("LEAST(100, bond_level + {$bondGain['gain']})")
                ]);
        }
    }

    /**
     * Roll for skill hints based on probability
     * 
     * @param TrainingPrediction $prediction
     * @return array
     */
    private function rollSkillHints($prediction): array
    {
        if (empty($prediction->hints)) {
            return [];
        }

        $gained = [];

        foreach ($prediction->hints as $hint) {
            $roll = mt_rand(1, 10000) / 100;
            
            if ($roll <= $hint['probability']) {
                $gained[] = $hint;
            }
        }

        return $gained;
    }
}
```text

---

## 5. API Specification

### 5.1 Endpoint Overview

| Method | Endpoint | Description | Auth Required |
| --- | --- | --- | --- |
| POST | `/api/v1/training/predict` | Get predictions for current turn | Yes |
| POST | `/api/v1/training/execute` | Execute training action | Yes |
| GET | `/api/v1/training/history/{career_id}` | Get training history | Yes |

### 5.2 Get Training Predictions

**Endpoint**: `POST /api/v1/training/predict`

**Request Headers**:

```http
Content-Type: application/json
Authorization: Bearer {token}
Accept: application/json
```

**Request Body**:

```json
{
    "career_run_id": "uuid-here",
    "current_turn": 45
}
```text

**Success Response** (200 OK):

```json
{
    "data": [
        {
            "type": "speed",
            "gains": {
                "speed": 42,
                "power": 12
            },
            "energy_cost": 22,
            "risk": 0,
            "risk_factors": {
                "energy_risk": 0,
                "mood_modifier": 0,
                "type_modifier": 0
            },
            "hints": [
                {
                    "skill_id": 101,
                    "skill_name": "Lane Guidance",
                    "probability": 15,
                    "source_card": "Kitasan Black"
                }
            ],
            "hint_probability": 15,
            "support_cards": [
                {
                    "id": 1,
                    "name": "Kitasan Black",
                    "bond_level": 85,
                    "bonus": 12
                }
            ],
            "is_friendship": true,
            "bond_gains": [
                {
                    "card_id": 1,
                    "gain": 5
                }
            ],
            "score": 95
        },
        {
            "type": "rest",
            "gains": {},
            "energy_cost": -50,
            "risk": 0,
            "risk_factors": {},
            "hints": [],
            "hint_probability": 0,
            "support_cards": [],
            "is_friendship": false,
            "bond_gains": [],
            "score": 40
        }
    ],
    "meta": {
        "cached": true,
        "generated_at": "2026-01-24T10:30:00Z"
    }
}
```

**Error Responses**:

```json
// 404 Not Found
{
    "message": "Career run not found",
    "error_code": "CAREER_NOT_FOUND"
}

// 422 Validation Error
{
    "message": "The given data was invalid.",
    "errors": {
        "career_run_id": ["The career_run_id field is required."]
    }
}
```text

### 5.3 Execute Training

**Endpoint**: `POST /api/v1/training/execute`

**Request Body**:

```json
{
    "career_run_id": "uuid-here",
    "facility": "speed"
}
```

**Success Response** (200 OK):

```json
{
    "success": true,
    "result": {
        "outcome": "success",
        "stat_deltas": {
            "speed": 42,
            "power": 12
        },
        "energy_delta": -22,
        "bond_gains": [
            {
                "card_id": 1,
                "gain": 5
            }
        ],
        "hints_gained": [
            {
                "skill_id": 101,
                "skill_name": "Lane Guidance",
                "probability": 15,
                "source_card": "Kitasan Black"
            }
        ],
        "turn": 46
    },
    "character_state": {
        "current_stats": {
            "speed": 542,
            "stamina": 380,
            "power": 432,
            "guts": 350,
            "wit": 420
        },
        "energy_level": 78,
        "mood_status": "great"
    }
}
```text

**Failure Response** (200 OK):

```json
{
    "success": true,
    "result": {
        "outcome": "failure",
        "stat_deltas": {
            "speed": -13,
            "power": -4
        },
        "energy_delta": -33,
        "bond_gains": [],
        "hints_gained": [],
        "turn": 46
    },
    "character_state": {
        "current_stats": {
            "speed": 487,
            "stamina": 380,
            "power": 416,
            "guts": 350,
            "wit": 420
        },
        "energy_level": 67,
        "mood_status": "normal"
    }
}
```

---

## 6. Database Schema

### 6.1 Table: `ucp_training_sessions`

Stores historical record of all training actions.

```sql
CREATE TABLE ucp_training_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    career_id BIGINT UNSIGNED NOT NULL,
    turn_number TINYINT UNSIGNED NOT NULL COMMENT 'Turn when training occurred (1-78)',
    training_type VARCHAR(20) NOT NULL COMMENT 'speed, stamina, power, guts, wisdom, rest',
    stat_gains JSON NOT NULL COMMENT 'Actual stat changes {"speed": 42, "power": 12}',
    support_bonuses JSON NULL COMMENT 'Breakdown of support card contributions',
    skill_hints_gained JSON NULL COMMENT 'Array of skill hint objects',
    success_rate DECIMAL(5,2) NOT NULL COMMENT 'Calculated success probability (0-100)',
    was_successful BOOLEAN NOT NULL DEFAULT TRUE,
    energy_delta TINYINT NOT NULL COMMENT 'Energy change (negative for consumption)',
    mood_change VARCHAR(20) NULL COMMENT 'Mood status after training if changed',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (career_id) REFERENCES ucp_careers(id) ON DELETE CASCADE,
    INDEX idx_career_turn (career_id, turn_number),
    INDEX idx_training_type (training_type),
    INDEX idx_success (was_successful)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```text

### 6.2 Table: `ucp_careers` (Updates)

Training execution updates these fields:

```sql
-- Fields updated by training service
current_turn TINYINT UNSIGNED NOT NULL DEFAULT 1,
current_stats JSON NOT NULL,
energy_level TINYINT UNSIGNED NOT NULL DEFAULT 100,
mood_status ENUM('awful','bad','normal','good','great') NOT NULL DEFAULT 'normal',
conditions JSON NULL
```

### 6.3 Table: `ucp_support_deck_cards` (Updates)

Bond level updates during training:

```sql
-- Pivot table fields
bond_level TINYINT UNSIGNED NOT NULL DEFAULT 0 CHECK (bond_level BETWEEN 0 AND 100)
```text

---

## 7. AI Integration

### 7.1 Training Advisor Agent

**Agent Class**: `App\Neuron\Agents\TrainingAdvisorAgent`

**Purpose**: Provides intelligent training recommendations based on character state,
goals, and long-term strategy.

**System Prompt Template**:

```text
You are an expert Umamusume training strategist.

Analyze the following training context and provide recommendations:

**Character State:**
- Name: {{ character_name }}
- Current Stats: Speed {{ speed }}, Stamina {{ stamina }}, Power {{ power }}, Guts {{ guts }}, Wit {{ wit }}
- Energy: {{ energy }}%
- Mood: {{ mood }}

**Training Options:**
{{ predictions_json }}

**Goals:**
{{ goals_json }}

**Upcoming Events:**
- Next Race: {{ next_race }} (Turn {{ race_turn }})
- Turns Remaining: {{ turns_remaining }}

Provide a recommendation in JSON format:
{
    "recommended_facility": "speed|stamina|power|guts|wisdom|rest",
    "confidence": 0.0-1.0,
    "reasoning": "Brief explanation",
    "alternative": "Alternative option if primary is unavailable",
    "long_term_impact": "How this affects overall strategy"
}
```

**Tool Integration**:

```php
// Available MCP tools for the agent
[
    'TrainingPredictionTool' => 'Query current predictions',
    'StatAnalysisTool' => 'Analyze stat gaps vs goals',
    'RaceRequirementTool' => 'Check race readiness',
]
```text

### 7.2 Hybrid AI Routing

**Local (Ollama)**:

- Quick turn-by-turn recommendations
- Pattern recognition for common scenarios
- Offline availability
- Response time: < 1s

**Cloud (AWS Bedrock Claude)**:

- Complex multi-turn strategic planning
- Goal conflict resolution
- Scenario-specific optimizations
- Response time: 2-5s

**Fallback Logic**:

```php
try {
    // Attempt local AI first
    $advice = $this->ollamaService->getAdvice($context);
} catch (OllamaUnavailableException $e) {
    // Fallback to cloud AI
    $advice = $this->bedrockService->getAdvice($context);
} catch (Exception $e) {
    // Fallback to rule-based recommendation
    $advice = $this->ruleBasedRecommendation($predictions);
}
```

---

## 8. Business Logic

### 8.1 Training Type Effects

| Type | Primary Stat | Secondary Stat | Energy Cost | Base Risk |
| --- | --- | --- | --- | --- |
| Speed | Speed +10 | Power +5 | 20 | Normal |
| Stamina | Stamina +10 | Guts +5 | 22 | Normal |
| Power | Power +10 | Stamina +5 | 24 | Normal |
| Guts | Guts +10 | Speed +5 | 18 | Normal |
| Wisdom | Wit +10 | Speed +2 | 15 | Low |
| Rest | - | - | -50 | None |

### 8.2 Friendship Training

**Activation Requirements**:

- At least 1 support card at facility
- Card bond level ≥ 80

**Effects**:

- 1.2x stat gain multiplier
- 20% energy cost reduction
- Enhanced skill hint probability (+5%)

### 8.3 Energy Management Thresholds

| Energy Range | Training Impact | Recommended Action |
| --- | --- | --- |
| 80-100 | Optimal performance | Train freely |
| 50-79 | Normal performance | Monitor carefully |
| 30-49 | Reduced gains, risk starts | Consider rest soon |
| 0-29 | High risk, poor gains | Rest immediately |

### 8.4 Mood Effects on Training

| Mood | Stat Multiplier | Risk Modifier | Duration |
| --- | --- | --- | --- |
| Awful | 0.90x | +10% | 3-5 turns |
| Bad | 0.95x | +5% | 2-3 turns |
| Normal | 1.00x | 0% | Baseline |
| Good | 1.05x | -2% | 2-4 turns |
| Great | 1.10x | -5% | 3-6 turns |

---

## 9. Integration Points

### 9.1 Character Service Integration

Training system reads and updates character state:

```php
// Read character state
$character = $career->character;
$currentStats = $character->current_stats;
$energy = $character->energy_level;
$mood = $character->mood_status;

// Update after training
$character->updateStats(['speed' => 42, 'power' => 12]);
$character->energy_level = $character->clampEnergy($energy - 22);
$character->save();
```text

### 9.2 Support Card Service Integration

Retrieves support deck configuration and updates bond levels:

```php
// Get active deck
$deck = $career->supportDeck;
$cards = $deck->cards()->with('pivot')->get();

// Update bond levels
$deck->cards()->updateExistingPivot($cardId, [
    'bond_level' => DB::raw('LEAST(100, bond_level + 5)')
]);
```

### 9.3 Skill Service Integration

Registers skill hints gained during training:

```php
foreach ($hintsGained as $hint) {
    app(SkillService::class)->addHint(
        characterId: $character->id,
        skillId: $hint['skill_id'],
        level: 1,
        source: 'training'
    );
}
```text

### 9.4 Event Broadcasting

**Events Fired**:

- `TrainingCompleted`: After successful execution
- `BondLevelIncreased`: When bond threshold crossed
- `SkillHintReceived`: When hint gained
- `EnergyLow`: When energy drops below 30

**Listeners**:

- `UpdateTrainingAnalytics`: Track training patterns
- `InvalidateTrainingCache`: Clear prediction cache
- `NotifyLowEnergy`: Alert user via WebSocket

---

## 10. Error Handling

### 10.1 Exception Hierarchy

```php
App\Exceptions\TrainingException (Base)
├── InvalidTrainingException
├── InsufficientEnergyException
├── TurnLimitExceededException
└── PredictionCacheException
```

### 10.2 Error Codes

| Code | HTTP Status | Description | Resolution |
| --- | --- | --- | --- |
| `TRAIN_INVALID_TYPE` | 422 | Invalid training facility type | Use valid type |
| `TRAIN_TURN_LIMIT` | 422 | Career reached max turns | Start new career |
| `TRAIN_INSUFFICIENT_ENERGY` | 422 | Energy below minimum threshold | Rest first |
| `TRAIN_CAREER_NOT_FOUND` | 404 | Career run not found | Verify ID |
| `TRAIN_PREDICTION_FAILED` | 500 | Prediction generation failed | Retry request |

### 10.3 Validation Rules

```php
// Training execution validation
[
    'career_run_id' => 'required|uuid|exists:ucp_careers,id',
    'facility' => 'required|in:speed,stamina,power,guts,wisdom,rest',
]

// Prediction request validation
[
    'career_run_id' => 'required|uuid|exists:ucp_careers,id',
    'current_turn' => 'required|integer|min:1|max:78',
]
```text

---

## 11. Performance Optimization

### 11.1 Caching Strategy

**Prediction Cache**:

```php
Cache::tags(['training', "career:{$career_id}"])->remember(
    "training:predictions:{$career_id}:{$turn}",
    now()->addMinutes(5),
    fn() => $this->generatePredictions($career)
);
```

**Cache Invalidation**:

- On training execution: Flush career tag
- On stat update: Flush character tag
- On support deck change: Flush career tag

**Cache Keys**:

- `training:predictions:{career_id}:{turn}`: Prediction data
- `training:history:{career_id}`: Session history

**TTL Strategy**:

- Predictions: 5 minutes (state-dependent)
- History: 15 minutes (static data)

### 11.2 Query Optimization

**Eager Loading**:

```php
$career = CareerRun::with([
    'character.aptitudes',
    'supportDeck.cards',
])->findOrFail($id);
```text

**Selective Loading**:

```php
TrainingSession::select([
    'id',
    'turn_number',
    'training_type',
    'stat_gains',
    'was_successful'
])->where('career_id', $careerId)->get();
```

### 11.3 Performance Targets

| Operation | Target | Measurement |
| --- | --- | --- |
| Prediction generation | < 100ms | p95 |
| Training execution | < 200ms | p95 |
| History retrieval (50 turns) | < 50ms | p95 |
| AI recommendation | < 2s (local) / < 5s (cloud) | p95 |

---

## 12. Security Considerations

### 12.1 Authorization

```php
// Training execution policy
public function execute(User $user, CareerRun $career): bool
{
    return $user->id === $career->character->user_id;
}
```text

### 12.2 Input Validation

- Facility type must be enum value
- Career must belong to authenticated user
- Turn number must not exceed maximum
- Energy level must be sufficient for non-rest actions

### 12.3 Rate Limiting

```php
// Apply rate limiting to training endpoints
RateLimiter::for('training', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()->id);
});
```

---

## 13. Testing Strategy

### 13.1 Unit Tests

```php
// tests/Unit/Services/StatGainCalculatorTest.php

test('calculates base gains correctly', function () {
    $character = Character::factory()->make([
        'growth_rates' => ['speed' => 20, 'power' => 10],
    ]);
    
    $calculator = app(StatGainCalculator::class);
    $gains = $calculator->calculateBase(TrainingType::Speed, $character, 1);
    
    expect($gains->get('speed'))->toBe(12) // 10 * 1.2 (20% growth)
        ->and($gains->get('power'))->toBe(6); // 5 * 1.1 (10% growth) rounded
});

test('risk increases exponentially below 30 energy', function () {
    $character = Character::factory()->make(['energy_level' => 20]);
    
    $calculator = app(RiskCalculator::class);
    $result = $calculator->calculate($character, TrainingType::Speed);
    
    expect($result['risk'])->toBeGreaterThan(30);
});
```text

### 13.2 Feature Tests

```php
// tests/Feature/TrainingExecutionTest.php

test('successful training updates character stats', function () {
    $user = User::factory()->create();
    $career = CareerRun::factory()->for($user)->create();
    
    $response = $this->actingAs($user)
        ->postJson('/api/v1/training/execute', [
            'career_run_id' => $career->id,
            'facility' => 'speed',
        ]);
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'result' => ['outcome', 'stat_deltas', 'turn']
        ]);
    
    $career->refresh();
    expect($career->current_turn)->toBe(2);
});

test('training creates session record', function () {
    $career = CareerRun::factory()->create();
    
    app(TrainingService::class)->executeTraining($career, 'speed');
    
    $this->assertDatabaseHas('ucp_training_sessions', [
        'career_id' => $career->id,
        'training_type' => 'speed',
        'turn_number' => 1,
    ]);
});
```

### 13.3 Integration Tests

```php
// tests/Integration/TrainingPredictionTest.php

test('predictions include all training types', function () {
    $career = CareerRun::factory()->create();
    
    $service = app(TrainingPredictionService::class);
    $predictions = $service->getPredictions($career);
    
    expect($predictions)->toHaveCount(6) // 5 facilities + rest
        ->and($predictions[0])->toHaveKeys(['type', 'gains', 'risk', 'score']);
});
```text

---

## 14. Appendices

### Appendix A: Calculation Formulas

**Stat Gain Formula**:

```text
Final Gain = Base Gain × Level Multiplier × Growth Rate × Mood × Support Multiplier

Where:
- Base Gain: Facility constant (10 for primary, 5 for secondary)
- Level Multiplier: 1.0 + (Level - 1) × 0.1
- Growth Rate: 1.0 + (Rate / 100)
- Mood: 0.90 - 1.10
- Support Multiplier: 1.0 + (Bonus / 100) × 1.2 (if friendship)
```

**Risk Formula**:

```text
Total Risk = Base Risk + Mood Modifier + Type Modifier

Base Risk (Energy):
- Energy > 50: 0%
- Energy 30-50: 15% × (50 - Energy) / 20
- Energy < 30: 15% + 55% × ((30 - Energy) / 30)²
```text

### Appendix B: Support Card Bonuses

| Rarity | Base Bonus | LB0 | LB1 | LB2 | LB3 | LB4 |
| --- | --- | --- | --- | --- | --- | --- |
| SSR | 10% | 10% | 12% | 14% | 16% | 18% |
| SR | 7% | 7% | 9% | 11% | 13% | 15% |
| R | 5% | 5% | 7% | 9% | 11% | 13% |

### Appendix C: Energy Recovery

| Action | Base Recovery | Mood Bonus | Final Recovery |
| --- | --- | --- | --- |
| Rest (Normal) | 50 | 0 | 50 |
| Rest (Good) | 50 | +10 | 60 |
| Rest (Great) | 50 | +20 | 70 |

### Appendix D: Change Log

| Version | Date | Author | Changes |
| --- | --- | --- | --- |
| 2.2.0 | 2026-01-28 | Development Team | Game-accurate training formula with support card presence bonus (+5% per card), per-training caps (100/50 based on soft cap), friendship multiplier integration |
| 2.0.0 | 2026-01-24 | Development Team | Full v2.0.0 alignment, added AI integration, complete calculation engines |
| 1.0.0 | 2026-01-23 | Development Team | Initial technical specification |

---

### Document Approval

| Role | Name | Signature | Date |
| --- | --- | --- | --- |
| Tech Lead | [Name] | _________ | 2026-01-24 |
| Product Owner | [Name] | _________ | 2026-01-24 |
| QA Lead | [Name] | _________ | 2026-01-24 |

---

**Document Control**  
**Maintained By**: Backend Development Team  
**Review Frequency**: Bi-weekly during active development  
**Next Review Date**: 2026-02-07  
**Distribution**: Development Team, QA Team, Product Management

---

### End of Document
