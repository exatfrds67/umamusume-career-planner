# SPEC-003: Race Strategy System - Technical Specification

**Document Version**: 2.4.1
**Date**: 2026-03-11
**Project**: Umamusume Pretty Derby Career Planner
**Status**: Current - Calendar/Targets view data contracts updated
**Classification**: Internal - Development Team

---

## Document Information

| Attribute | Value |
| --- | --- |
| **Document ID** | SPEC-003 |
| **Related PRD** | [PRD-003: Race Strategy](../02-prds/PRD-003_Race_Strategy.md) |
| **Architecture Version** | v2.4.1 |
| **Approval Status** | Approved |
| **Last Reviewed** | 2026-03-11 |

### Related Documents

**Requirements & Design**:

- [SRS Section 3.3: Race Management](../00-core-docs/003_SRS_Software_Requirement_Specifications.md#33-race-management)
- [SDS Section 4.3: Race Strategy Architecture](../00-core-
docs/004_SDS_Software_Design_Specifications.md#43-race-strategy-module)

**Data & Integration**:

- [DBD Section 5.3: Race Tables](../00-core-docs/009_DBD_Database_Documentation.md#53-race-tables)
- [API Section 4.3: Race Endpoints](../00-core-docs/010_API_API_Documentation.md#43-race-endpoints)

**Visual Documentation**:

- [FLOW-003: Race Strategy System](../01-flows/FLOW-003_Race_Strategy_System.md)
- [SEQ-004: Race Registration and Outcome](../01-sequences/SEQ-004_Race_Registration_and_Outcome.md)
- [WF-006: Race Calendar View](../01-wireframes/WF-006_Race_Calendar_View.md)
- [WF-007: Race Preparation Screen](../01-wireframes/WF-007_Race_Preparation_Screen.md)

---

## Table of Contents

1. [Technical Overview](#1-technical-overview)
2. [Architecture Design](#2-architecture-design)
3. [Analysis Engines](#3-analysis-engines)
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
14. [Frontend View Data Contracts](#14-frontend-view-data-contracts)
15. [Appendices](#15-appendices)

---

## 1. Technical Overview

### 1.1 Module Purpose

The Race Strategy System provides intelligent planning, analysis, and execution support
for race events throughout a character's career. It integrates stat analysis, aptitude
matching, weather conditions, and AI-driven strategy recommendations to optimize race
performance and maximize fan gain.

**Core Responsibilities**:

- Race calendar management and scheduling
- Stat requirement analysis for race readiness
- Running style optimization based on aptitudes
- Weather and track condition impact calculations
- Win probability prediction using weighted scoring
- Strategy recommendations via Neuron AI agents
- Race result recording and performance tracking

### 1.2 Business Context

Racing is the primary progression mechanism in Umamusume Pretty Derby:

- **Fan Acquisition**: Winning races increases fan count (required for scenario completion)
- **Stat Validation**: Races validate training effectiveness
- **Skill Points**: Race rewards provide SP for skill acquisition
- **Story Progression**: Required races unlock scenario milestones
- **Grade Classification**: G3 → G2 → G1 progression determines career success

Strategic race selection and preparation directly impact career outcomes.

### 1.3 Technical Scope

**In Scope**:

- Race definition database synchronized from external APIs
- Race requirement analysis (stats, aptitudes, skills)
- Running style recommendation engine
- Weather impact modeling
- Win probability calculations
- AI-powered strategy recommendations
- Race result recording with rewards processing
- Calendar view with conflict detection

**Out of Scope**:

- Race simulation/animation (game client responsibility)
- Real-time multiplayer races
- Character stat modification (SPEC-001)
- Skill acquisition logic (SPEC-004)

### 1.4 Technology Stack

| Component | Technology | Version | Purpose |
| --- | --- | --- | --- |
| **Framework** | Laravel | 12.x | Application foundation |
| **Language** | PHP | 8.3+ | Server-side logic |
| **Database** | MySQL | 8.0+ | Data persistence |
| **Cache** | Redis | 7.x | Race data caching |
| **AI** | Neuron AI | v2.11 | Strategy agents |
| **AI Provider (Local)** | Ollama | Latest | Quick analysis |
| **AI Provider (Cloud)** | AWS Bedrock Claude | 4.5 | Complex strategy |
| **External API** | umapyoi.net | - | Race definitions |

---

## 2. Architecture Design

### 2.1 Component Architecture

```mermaid
graph TB
    subgraph "Presentation Layer"
        API[RaceController]
        Livewire[RaceCalendar Component]
        FormRequest[RaceRequest]
    end

    subgraph "Application Layer"
        RaceSvc[RaceConditionService]
        StrategySvc[Neuron\RaceStrategyService]
        AnalysisSvc[RaceConditionService]
    end

    subgraph "Domain Layer"
        RaceDef[RaceDefinition Model]
        RaceResult[RaceResult Model]
        Analyzers[Analysis Engines]
    end

    subgraph "Infrastructure Layer"
        DB[(MySQL)]
        Cache[(Redis)]
        External[ExternalDataService]
        NeuronAI[RaceStrategyAgent]
    end

    API --> FormRequest
    FormRequest --> RaceSvc
    Livewire --> RaceSvc

    RaceSvc --> StrategySvc
    RaceSvc --> AnalysisSvc

    StrategySvc --> NeuronAI
    AnalysisSvc --> Analyzers

    RaceSvc --> RaceDef
    RaceSvc --> RaceResult
    RaceSvc --> DB

    RaceSvc --> External
    RaceSvc --> Cache

    Analyzers --> ReqAnalyzer[RequirementAnalyzer]
    Analyzers --> WinCalc[RaceConditionService]
    Analyzers --> StyleOpt[StyleOptimizer]
```text

### 2.2 Layer Responsibilities

**Presentation Layer**:

- HTTP request/response handling
- Race calendar rendering
- Input validation for race actions

**Application Layer**:

- Race workflow orchestration
- Strategy generation coordination
- External data synchronization

**Domain Layer**:

- Race business rules
- Analysis algorithms
- Win probability models

**Infrastructure Layer**:

- Database persistence
- External API integration
- AI service communication
- Cache management

### 2.3 Design Patterns

| Pattern | Implementation | Purpose |
| --- | --- | --- |
| **Repository** | `RaceDefinitionRepository` | Abstract data access |
| **Strategy** | Analysis calculators | Pluggable analysis algorithms |
| **Factory** | `RaceResultFactory` | Result object creation |
| **Observer** | Event listeners | React to race completion |
| **Cache-Aside** | Race definitions | Performance optimization |
| **Adapter** | External API clients | Abstract external services |

---

## 3. Analysis Engines

### 3.1 Requirement Analyzer

Evaluates character readiness against race stat requirements.

```php
<?php

namespace App\Services\Race\Analyzers;

use App\Models\{Character, RaceDefinition};
use App\DTOs\RaceAnalysis;

/**
 * Race Requirement Analyzer
 *
 * Evaluates character readiness for specific races based on stat requirements.
 */
class RaceRequirementAnalyzer
{
    /**
     * Stat requirement thresholds by distance category
     */
    private const THRESHOLDS = [
        'sprint' => ['speed' => 1.2, 'power' => 1.0, 'stamina' => 0.6, 'wit' => 0.8],
        'mile' => ['speed' => 1.1, 'power' => 0.9, 'stamina' => 0.8, 'wit' => 0.9],
        'medium' => ['speed' => 1.0, 'stamina' => 1.0, 'power' => 0.9, 'guts' => 0.8],
        'long' => ['stamina' => 1.2, 'guts' => 1.0, 'speed' => 0.8, 'power' => 0.7],
    ];

    /**
     * Grade difficulty multipliers
     */
    private const GRADE_MULTIPLIERS = [
        'G1' => 1.3,
        'G2' => 1.15,
        'G3' => 1.0,
        'OP' => 0.85,
        'Pre-OP' => 0.7,
    ];

    /**
     * Analyze character readiness for a race
     *
     * @param Character $character
     * @param RaceDefinition $race
     * @return RaceAnalysis
     */
    public function analyze(Character $character, RaceDefinition $race): RaceAnalysis
    {
        $requirements = $this->calculateRequirements($race);
        $currentStats = $character->current_stats;

        $statAnalysis = [];
        $totalGap = 0;

        foreach ($requirements as $stat => $target) {
            $current = $currentStats[$stat] ?? 0;
            $gap = $target - $current;
            $status = $this->evaluateStatus($current, $target);

            $statAnalysis[$stat] = [
                'current' => $current,
                'target' => $target,
                'gap' => $gap,
                'status' => $status,
                'percentage' => $target > 0 ? min(100, ($current / $target) * 100) : 100,
            ];

            if ($gap > 0) {
                $totalGap += $gap;
            }
        }

        $readinessScore = $this->calculateReadinessScore($statAnalysis);

        return new RaceAnalysis(
            raceId: $race->id,
            raceName: $race->name,
            statAnalysis: $statAnalysis,
            readinessScore: $readinessScore,
            recommendation: $this->getRecommendation($readinessScore),
            totalGap: $totalGap
        );
    }

    /**
     * Calculate stat requirements for a race
     *
     * @param RaceDefinition $race
     * @return array<string, int>
     */
    private function calculateRequirements(RaceDefinition $race): array
    {
        $baseRequirements = $this->getBaseRequirements($race->distance_type);
        $gradeMultiplier = self::GRADE_MULTIPLIERS[$race->grade] ?? 1.0;

        $requirements = [];

        foreach ($baseRequirements as $stat => $multiplier) {
            // Base calculation: distance-dependent threshold
            $baseValue = match ($race->distance_type) {
                'sprint' => 800,
                'mile' => 850,
                'medium' => 900,
                'long' => 950,
                default => 850,
            };

            $requirements[$stat] = (int) ($baseValue * $multiplier * $gradeMultiplier);
        }

        return $requirements;
    }

    /**
     * Get base requirement multipliers for distance type
     *
     * @param string $distanceType
     * @return array<string, float>
     */
    private function getBaseRequirements(string $distanceType): array
    {
        return self::THRESHOLDS[$distanceType] ?? self::THRESHOLDS['medium'];
    }

    /**
     * Evaluate stat status
     *
     * @param int $current
     * @param int $target
     * @return string
     */
    private function evaluateStatus(int $current, int $target): string
    {
        $percentage = $target > 0 ? ($current / $target) * 100 : 100;

        return match (true) {
            $percentage >= 110 => 'excellent',
            $percentage >= 100 => 'optimal',
            $percentage >= 90 => 'adequate',
            $percentage >= 75 => 'borderline',
            default => 'inadequate',
        };
    }

    /**
     * Calculate overall readiness score
     *
     * @param array $statAnalysis
     * @return int
     */
    private function calculateReadinessScore(array $statAnalysis): int
    {
        $totalPercentage = 0;
        $statCount = count($statAnalysis);

        foreach ($statAnalysis as $analysis) {
            $totalPercentage += $analysis['percentage'];
        }

        return $statCount > 0 ? (int) ($totalPercentage / $statCount) : 0;
    }

    /**
     * Get recommendation based on readiness score
     *
     * @param int $readinessScore
     * @return string
     */
    private function getRecommendation(int $readinessScore): string
    {
        return match (true) {
            $readinessScore >= 100 => 'highly_recommended',
            $readinessScore >= 90 => 'recommended',
            $readinessScore >= 75 => 'possible',
            $readinessScore >= 60 => 'risky',
            default => 'not_recommended',
        };
    }
}
```

### 3.2 Win Probability Calculator

Calculates race win probability using weighted scoring model.

```php
<?php

namespace App\Services\Race\Analyzers;

use App\Models\{Character, RaceDefinition};
use App\Enums\{AptitudeGrade, MoodStatus};

/**
 * Win Probability Calculator
 *
 * Calculates predicted win probability using weighted stat/aptitude scoring.
 * Uses game-accurate aptitude modifiers (G-S grades, S is maximum).
 */
class RaceConditionService
{
    /**
     * Component weights in probability calculation
     */
    private const WEIGHTS = [
        'stats' => 0.40,
        'aptitudes' => 0.30,
        'skills' => 0.20,
        'mood' => 0.10,
    ];

    /**
     * Game-accurate aptitude grade multipliers
     * S is maximum grade. A is baseline (1.0).
     *
     * Note: Actual modifiers vary by category (Surface/Distance/Style)
     * These are simplified averages for win probability calculation.
     */
    private const APTITUDE_MULTIPLIERS = [
        'S' => 1.05,   // +5% (only grade with positive bonus)
        'A' => 1.00,   // Baseline
        'B' => 0.90,   // -10%
        'C' => 0.80,   // -20%
        'D' => 0.65,   // -35% (average of category penalties)
        'E' => 0.45,   // -55%
        'F' => 0.25,   // -75%
        'G' => 0.10,   // -90%
    ];

    /**
     * Calculate win probability
     *
     * @param Character $character
     * @param RaceDefinition $race
     * @return array{probability: float, breakdown: array}
     */
    public function calculate(Character $character, RaceDefinition $race): array
    {
        $statScore = $this->calculateStatScore($character, $race);
        $aptitudeScore = $this->calculateAptitudeScore($character, $race);
        $skillScore = $this->calculateSkillScore($character, $race);
        $moodScore = $this->calculateMoodScore($character);

        // Weighted average
        $totalScore = (
            $statScore * self::WEIGHTS['stats'] +
            $aptitudeScore * self::WEIGHTS['aptitudes'] +
            $skillScore * self::WEIGHTS['skills'] +
            $moodScore * self::WEIGHTS['mood']
        );

        // Convert to probability (0-1)
        $probability = $this->scoreToProbability($totalScore);

        return [
            'probability' => round($probability, 4),
            'breakdown' => [
                'stat_score' => round($statScore, 2),
                'aptitude_score' => round($aptitudeScore, 2),
                'skill_score' => round($skillScore, 2),
                'mood_score' => round($moodScore, 2),
                'total_score' => round($totalScore, 2),
            ],
        ];
    }

    /**
     * Calculate stat component score
     *
     * @param Character $character
     * @param RaceDefinition $race
     * @return float
     */
    private function calculateStatScore(Character $character, RaceDefinition $race): float
    {
        $analyzer = app(RaceRequirementAnalyzer::class);
        $analysis = $analyzer->analyze($character, $race);

        // Score is the readiness percentage
        return min(100, $analysis->readinessScore);
    }

    /**
     * Calculate aptitude component score
     *
     * @param Character $character
     * @param RaceDefinition $race
     * @return float
     */
    private function calculateAptitudeScore(Character $character, RaceDefinition $race): float
    {
        $aptitudes = $character->aptitudes;

        // Get relevant aptitudes
        $distanceApt = $aptitudes->where('category', 'distance')
            ->where('type', $race->distance_type)
            ->first();

        $surfaceApt = $aptitudes->where('category', 'surface')
            ->where('type', strtolower($race->surface))
            ->first();

        // Calculate average multiplier
        $distanceMultiplier = $distanceApt
            ? self::APTITUDE_MULTIPLIERS[$distanceApt->grade->value] ?? 1.0
            : 1.0;

        $surfaceMultiplier = $surfaceApt
            ? self::APTITUDE_MULTIPLIERS[$surfaceApt->grade->value] ?? 1.0
            : 1.0;

        $avgMultiplier = ($distanceMultiplier + $surfaceMultiplier) / 2;

        // Convert to 0-100 score (1.0 = 80, 1.25 = 100)
        return min(100, ($avgMultiplier - 0.75) * 200);
    }

    /**
     * Calculate skill component score
     *
     * @param Character $character
     * @param RaceDefinition $race
     * @return float
     */
    private function calculateSkillScore(Character $character, RaceDefinition $race): float
    {
        $skills = $character->skills;

        if ($skills->isEmpty()) {
            return 50; // Neutral score
        }

        $relevantSkills = $skills->filter(function ($skill) use ($race) {
            // Check if skill conditions match race
            $conditions = $skill->conditions_json ?? [];

            if (empty($conditions)) {
                return true; // Universal skill
            }

            // Match distance
            if (isset($conditions['distance'])) {
                if (!in_array($race->distance_type, $conditions['distance'])) {
                    return false;
                }
            }

            // Match surface
            if (isset($conditions['surface'])) {
                if (!in_array(strtolower($race->surface), $conditions['surface'])) {
                    return false;
                }
            }

            return true;
        });

        // Score based on relevant skill count and rarity
        $score = 50; // Base score

        foreach ($relevantSkills as $skill) {
            $bonus = match ($skill->rarity->value) {
                'unique' => 15,
                'rare' => 10,
                'normal' => 5,
                default => 3,
            };

            $score += $bonus;
        }

        return min(100, $score);
    }

    /**
     * Calculate mood component score
     *
     * @param Character $character
     * @return float
     */
    private function calculateMoodScore(Character $character): float
    {
        return match ($character->mood_status) {
            MoodStatus::Great => 100,
            MoodStatus::Good => 90,
            MoodStatus::Normal => 75,
            MoodStatus::Bad => 50,
            MoodStatus::Awful => 25,
        };
    }

    /**
     * Convert total score to win probability
     *
     * Uses sigmoid function for realistic probability curve
     *
     * @param float $score
     * @return float
     */
    private function scoreToProbability(float $score): float
    {
        // Sigmoid: P(x) = 1 / (1 + e^(-k(x - x0)))
        // k = steepness, x0 = midpoint
        $k = 0.08;
        $x0 = 75; // 75% score = 50% win probability

        $probability = 1 / (1 + exp(-$k * ($score - $x0)));

        return $probability;
    }
}
```text

### 3.3 Running Style Optimizer

Recommends optimal running style based on aptitudes and race characteristics.

```php
<?php

namespace App\Services\Race\Analyzers;

use App\Models\{Character, RaceDefinition};
use App\Enums\RunningStyle;

/**
 * Running Style Optimizer
 *
 * Determines optimal running style for a character in a specific race.
 */
class RunningStyleOptimizer
{
    /**
     * Stat importance by running style
     */
    private const STYLE_STAT_WEIGHTS = [
        'front_runner' => ['speed' => 1.5, 'power' => 1.2, 'stamina' => 0.8],
        'pace_chaser' => ['speed' => 1.3, 'power' => 1.0, 'stamina' => 1.0],
        'late_surger' => ['speed' => 1.0, 'power' => 1.3, 'stamina' => 1.1],
        'end_closer' => ['speed' => 0.8, 'power' => 1.5, 'stamina' => 1.2],
    ];

    /**
     * Optimize running style selection
     *
     * @param Character $character
     * @param RaceDefinition $race
     * @return array{recommended_style: string, confidence: float, alternatives: array}
     */
    public function optimize(Character $character, RaceDefinition $race): array
    {
        $styleScores = [];

        foreach (RunningStyle::cases() as $style) {
            $score = $this->calculateStyleScore($character, $race, $style);

            $styleScores[$style->value] = [
                'score' => $score,
                'style' => $style->value,
            ];
        }

        // Sort by score descending
        uasort($styleScores, fn($a, $b) => $b['score'] <=> $a['score']);

        $topStyle = array_values($styleScores)[0];
        $alternatives = array_slice(array_values($styleScores), 1, 2);

        return [
            'recommended_style' => $topStyle['style'],
            'confidence' => $this->calculateConfidence($topStyle['score'], $alternatives),
            'alternatives' => $alternatives,
            'all_scores' => $styleScores,
        ];
    }

    /**
     * Calculate score for a specific running style
     *
     * @param Character $character
     * @param RaceDefinition $race
     * @param RunningStyle $style
     * @return float
     */
    private function calculateStyleScore(
        Character $character,
        RaceDefinition $race,
        RunningStyle $style
    ): float {
        $score = 0;

        // Component 1: Aptitude match (40%)
        $aptitudeScore = $this->getAptitudeScore($character, $style);
        $score += $aptitudeScore * 0.4;

        // Component 2: Stat match (40%)
        $statScore = $this->getStatScore($character, $style);
        $score += $statScore * 0.4;

        // Component 3: Distance suitability (20%)
        $distanceScore = $this->getDistanceScore($race, $style);
        $score += $distanceScore * 0.2;

        return $score;
    }

    /**
     * Get aptitude score for running style
     *
     * @param Character $character
     * @param RunningStyle $style
     * @return float
     */
    private function getAptitudeScore(Character $character, RunningStyle $style): float
    {
        $aptitude = $character->aptitudes
            ->where('category', 'style')
            ->where('type', $style->value)
            ->first();

        if (!$aptitude) {
            return 50; // Neutral
        }

        return match ($aptitude->grade->value) {
            'SS' => 100,
            'S' => 90,
            'A' => 80,
            'B' => 70,
            'C' => 60,
            'D' => 50,
            'E' => 40,
            'F' => 30,
            'G' => 20,
            default => 50,
        };
    }

    /**
     * Get stat alignment score for running style
     *
     * @param Character $character
     * @param RunningStyle $style
     * @return float
     */
    private function getStatScore(Character $character, RunningStyle $style): float
    {
        $weights = self::STYLE_STAT_WEIGHTS[$style->value] ?? [];
        $stats = $character->current_stats;

        $totalScore = 0;
        $totalWeight = 0;

        foreach ($weights as $stat => $weight) {
            $statValue = $stats[$stat] ?? 0;

            // Normalize to 0-100 (assuming 1200 max)
            $normalized = min(100, ($statValue / 1200) * 100);

            $totalScore += $normalized * $weight;
            $totalWeight += $weight;
        }

        return $totalWeight > 0 ? $totalScore / $totalWeight : 50;
    }

    /**
     * Get distance suitability score
     *
     * @param RaceDefinition $race
     * @param RunningStyle $style
     * @return float
     */
    private function getDistanceScore(RaceDefinition $race, RunningStyle $style): float
    {
        // Different styles suit different distances
        $suitability = match ($race->distance_type) {
            'sprint' => [
                'front_runner' => 90,
                'pace_chaser' => 80,
                'late_surger' => 70,
                'end_closer' => 50,
            ],
            'mile' => [
                'front_runner' => 80,
                'pace_chaser' => 90,
                'late_surger' => 85,
                'end_closer' => 70,
            ],
            'medium' => [
                'front_runner' => 70,
                'pace_chaser' => 85,
                'late_surger' => 90,
                'end_closer' => 85,
            ],
            'long' => [
                'front_runner' => 60,
                'pace_chaser' => 75,
                'late_surger' => 85,
                'end_closer' => 90,
            ],
            default => [
                'front_runner' => 75,
                'pace_chaser' => 75,
                'late_surger' => 75,
                'end_closer' => 75,
            ],
        };

        return $suitability[$style->value] ?? 75;
    }

    /**
     * Calculate confidence in recommendation
     *
     * @param float $topScore
     * @param array $alternatives
     * @return float
     */
    private function calculateConfidence(float $topScore, array $alternatives): float
    {
        if (empty($alternatives)) {
            return 1.0;
        }

        $secondScore = $alternatives[0]['score'] ?? 0;
        $gap = $topScore - $secondScore;

        // Larger gap = higher confidence
        // Gap of 20+ = 100% confidence
        return min(1.0, $gap / 20);
    }
}
```

### 3.4 Weather Impact Calculator

Calculates track condition effects on performance.

```php
<?php

namespace App\Services\Race\Analyzers;

use App\Models\Character;
use App\Enums\{TrackCondition, WeatherType};

/**
 * Weather Impact Calculator
 *
 * Calculates performance modifiers based on track conditions and weather.
 * Uses planner-readable track condition approximations derived from verified
 * Global EN mechanics.
 *
 * Track Condition Penalties:
 * - Firm: No penalties
 * - Good: Power -50
 * - Soft: Power -50/-100 (Turf/Dirt), Stamina +2%/sec drain
 * - Heavy: Power -50/-100, Speed -50, Stamina +2%/sec drain
 */
class WeatherImpactCalculator
{
    /**
        * Planner-readable track condition modifiers
     *
        * Penalties are applied as flat reductions for readability inside the planner.
        * The live game resolves these effects through more complex race-performance
        * calculations rather than literal permanent stat subtraction.
        *
     * - Power penalties vary by surface (Turf: -50, Dirt: -100)
     * - Speed penalties apply to Heavy conditions
     * - Stamina drain increases on Soft/Heavy
     */
    private const CONDITION_PENALTIES = [
        'firm' => [
            'power_penalty' => 0,
            'speed_penalty' => 0,
            'stamina_drain_modifier' => 1.0,
        ],
        'good' => [
            'power_penalty' => 50,
            'speed_penalty' => 0,
            'stamina_drain_modifier' => 1.0,
        ],
        'soft' => [
            'power_penalty_turf' => 50,
            'power_penalty_dirt' => 100,
            'speed_penalty' => 0,
            'stamina_drain_modifier' => 1.02, // +2% per second
        ],
        'heavy' => [
            'power_penalty_turf' => 50,
            'power_penalty_dirt' => 100,
            'speed_penalty' => 50,
            'stamina_drain_modifier' => 1.02, // +2% per second
        ],
    ];

    /**
     * Calculate weather impact with game-accurate penalties
     *
     * @param Character $character
     * @param TrackCondition $condition
     * @param string $surface 'turf' or 'dirt'
     * @return array{penalties: array, effective_stats: array, skill_recommendations: array}
     */
    public function calculate(
        Character $character,
        TrackCondition $condition,
        string $surface = 'turf'
    ): array {
        $penalties = self::CONDITION_PENALTIES[$condition->value] ?? self::CONDITION_PENALTIES['firm'];

        // Calculate power penalty based on surface
        $powerPenalty = $surface === 'dirt'
            ? ($penalties['power_penalty_dirt'] ?? $penalties['power_penalty'] ?? 0)
            : ($penalties['power_penalty_turf'] ?? $penalties['power_penalty'] ?? 0);

        $speedPenalty = $penalties['speed_penalty'] ?? 0;
        $staminaDrainMod = $penalties['stamina_drain_modifier'] ?? 1.0;

        // Calculate effective stats after penalties
        $currentStats = $character->current_stats;
        $effectiveStats = [
            'speed' => max(0, ($currentStats['speed'] ?? 0) - $speedPenalty),
            'stamina' => $currentStats['stamina'] ?? 0, // Stamina drain is runtime, not flat
            'power' => max(0, ($currentStats['power'] ?? 0) - $powerPenalty),
            'guts' => $currentStats['guts'] ?? 0,
            'wit' => $currentStats['wit'] ?? 0,
        ];

        $skillRecommendations = $this->getSkillRecommendations($condition);

        return [
            'penalties' => [
                'power' => $powerPenalty,
                'speed' => $speedPenalty,
                'stamina_drain_modifier' => $staminaDrainMod,
            ],
            'effective_stats' => $effectiveStats,
            'skill_recommendations' => $skillRecommendations,
            'impact_description' => $this->getImpactDescription($condition),
        ];
    }

    /**
     * Get recommended skills for track condition
     *
     * @param TrackCondition $condition
     * @return array
     */
    private function getSkillRecommendations(TrackCondition $condition): array
    {
        return match ($condition) {
            TrackCondition::Heavy => [
                'Muddy Track',
                'Dirt Master',
                'Stamina Boost',
                'Heavy Ground Specialist',
            ],
            TrackCondition::Soft => [
                'Muddy Track',
                'Track Adaptation',
                'Wet Surface',
                'Stamina Recovery',
            ],
            TrackCondition::Good => [
                'Track Adaptation',
            ],
            default => [],
        };
    }

    /**
     * Get human-readable impact description
     *
     * @param TrackCondition $condition
     * @return string
     */
    private function getImpactDescription(TrackCondition $condition): string
    {
        return match ($condition) {
            TrackCondition::Firm => 'Optimal conditions - no penalties',
            TrackCondition::Good => 'Minor Power penalty (-50)',
            TrackCondition::Soft => 'Power penalty (-50/-100 Turf/Dirt), increased stamina drain (+2%/sec)',
            TrackCondition::Heavy => 'Speed penalty (-50), Power penalty (-50/-100), increased stamina drain (+2%/sec)',
        };
    }
}
```text

---

## 4. Service Layer

### 4.1 RaceConditionService

Main orchestration service for race operations.

```php
<?php

namespace App\Services\Race;

use App\Models\{RaceDefinition, RaceResult, CareerRun};
use App\Repositories\RaceDefinitionRepository;
use App\Services\External\ExternalDataService;
use Illuminate\Support\Facades\{DB, Cache};

/**
 * Race Management Service
 *
 * Handles race-related business operations.
 */
class RaceConditionService
{
    public function __construct(
        private RaceDefinitionRepository $repository,
        private RaceConditionService $conditionService,
        private ExternalDataService $externalApi
    ) {}

    /**
     * Get race calendar for a specific month
     *
     * @param int $month
     * @param array $filters
     * @return \Illuminate\Support\Collection
     */
    public function getCalendar(int $month, array $filters = [])
    {
        return Cache::tags(['races'])->remember(
            "race:calendar:{$month}:" . md5(json_encode($filters)),
            now()->addDay(),
            fn() => $this->repository->getByMonth($month, $filters)
        );
    }

    /**
     * Get race analysis for character
     *
     * @param int $raceId
     * @param int $characterId
     * @return array
     */
    public function analyzeRace(int $raceId, int $characterId): array
    {
        $race = $this->repository->findOrFail($raceId);
        $character = Character::with(['aptitudes', 'skills'])->findOrFail($characterId);

        return $this->analysisService->analyze($character, $race);
    }

    /**
     * Record race result
     *
     * @param CareerRun $career
     * @param int $raceDefinitionId
     * @param array $resultData
     * @return RaceResult
     */
    public function recordResult(
        CareerRun $career,
        int $raceDefinitionId,
        array $resultData
    ): RaceResult {
        return DB::transaction(function () use ($career, $raceDefinitionId, $resultData) {
            // Create race result record
            $result = RaceResult::create([
                'career_id' => $career->id,
                'race_definition_id' => $raceDefinitionId,
                'turn_number' => $career->current_turn,
                'placement' => $resultData['placement'],
                'fans_gained' => $resultData['fans_gained'] ?? 0,
                'rewards' => $resultData['rewards'] ?? null,
                'mood_after' => $resultData['mood_after'] ?? null,
                'strategy_used' => $resultData['strategy_used'] ?? null,
            ]);

            // Apply rewards
            if (isset($resultData['rewards'])) {
                $this->applyRewards($career, $resultData['rewards']);
            }

            // Update fan count
            $career->increment('total_fans', $resultData['fans_gained'] ?? 0);

            // Fire event
            event(new \App\Events\RaceCompleted($career, $result));

            return $result;
        });
    }

    /**
     * Sync race definitions from external API
     *
     * @return int Number of races updated
     */
    public function syncRaceDefinitions(): int
    {
        $externalRaces = $this->externalApi->getRaceDefinitions();
        $syncedCount = 0;

        foreach ($externalRaces as $externalRace) {
            $this->repository->updateOrCreate(
                ['external_id' => $externalRace['id']],
                [
                    'name' => $externalRace['name'],
                    'name_jp' => $externalRace['name_jp'] ?? null,
                    'grade' => $externalRace['grade'],
                    'surface' => $externalRace['surface'],
                    'distance' => $externalRace['distance'],
                    'distance_type' => $this->determineDistanceType($externalRace['distance']),
                    'track_location' => $externalRace['location'] ?? null,
                    'month' => $externalRace['month'],
                    'half' => $externalRace['half'],
                    'requirements' => $externalRace['requirements'] ?? null,
                ]
            );

            $syncedCount++;
        }

        Cache::tags(['races'])->flush();

        return $syncedCount;
    }

    /**
     * Apply race rewards to character
     *
     * @param CareerRun $career
     * @param array $rewards
     * @return void
     */
    private function applyRewards(CareerRun $career, array $rewards): void
    {
        $character = $career->character;

        // Apply stat gains
        if (isset($rewards['stats'])) {
            $character->updateStats($rewards['stats']);
        }

        // Add skill points
        if (isset($rewards['skill_points'])) {
            $career->increment('skill_points', $rewards['skill_points']);
        }

        $character->save();
    }

    /**
     * Determine distance type from meters
     *
     * @param int $distance
     * @return string
     */
    private function determineDistanceType(int $distance): string
    {
        return match (true) {
            $distance < 1400 => 'sprint',
            $distance < 1800 => 'mile',
            $distance < 2400 => 'medium',
            default => 'long',
        };
    }
}
```

### 4.2 RaceConditionService (Analysis Aggregation)

Aggregates analysis from multiple analyzers within RaceConditionService.

```php
<?php

namespace App\Services\Race;

use App\Models\{Character, RaceDefinition};
use App\Services\Race\Analyzers\{
    RaceRequirementAnalyzer,
    RaceConditionService,
    RunningStyleOptimizer,
    WeatherImpactCalculator
};
use App\Enums\TrackCondition;

/**
 * Race Analysis Aggregation Service
 *
 * Combines multiple analysis engines into comprehensive race analysis.
 */
class RaceConditionService
{
    public function __construct(
        private RaceRequirementAnalyzer $requirementAnalyzer,
        private RaceConditionService $winCalc,
        private RunningStyleOptimizer $styleOptimizer,
        private WeatherImpactCalculator $weatherCalc
    ) {}

    /**
     * Perform comprehensive race analysis
     *
     * @param Character $character
     * @param RaceDefinition $race
     * @param TrackCondition|null $trackCondition
     * @return array
     */
    public function analyze(
        Character $character,
        RaceDefinition $race,
        ?TrackCondition $trackCondition = null
    ): array {
        // Default to good conditions
        $trackCondition = $trackCondition ?? TrackCondition::Good;

        // Component analyses
        $requirementAnalysis = $this->requirementAnalyzer->analyze($character, $race);
        $winProbability = $this->winCalc->calculate($character, $race);
        $styleOptimization = $this->styleOptimizer->optimize($character, $race);
        $weatherImpact = $this->weatherCalc->calculate($character, $trackCondition);

        // Aptitude matching
        $aptitudeMatch = $this->analyzeAptitudeMatch($character, $race);

        return [
            'race' => [
                'id' => $race->id,
                'name' => $race->name,
                'grade' => $race->grade,
                'distance' => $race->distance,
                'distance_type' => $race->distance_type,
                'surface' => $race->surface,
            ],
            'readiness' => [
                'score' => $requirementAnalysis->readinessScore,
                'recommendation' => $requirementAnalysis->recommendation,
                'stat_analysis' => $requirementAnalysis->statAnalysis,
                'total_gap' => $requirementAnalysis->totalGap,
            ],
            'win_probability' => $winProbability['probability'],
            'win_breakdown' => $winProbability['breakdown'],
            'running_style' => [
                'recommended' => $styleOptimization['recommended_style'],
                'confidence' => $styleOptimization['confidence'],
                'alternatives' => $styleOptimization['alternatives'],
            ],
            'aptitude_match' => $aptitudeMatch,
            'weather' => [
                'condition' => $trackCondition->value,
                'modifiers' => $weatherImpact['modifiers'],
                'skill_recommendations' => $weatherImpact['skill_recommendations'],
                'description' => $weatherImpact['impact_description'],
            ],
        ];
    }

    /**
     * Analyze aptitude matching for race
     *
     * @param Character $character
     * @param RaceDefinition $race
     * @return array
     */
    private function analyzeAptitudeMatch(Character $character, RaceDefinition $race): array
    {
        $aptitudes = $character->aptitudes;

        $distanceApt = $aptitudes->where('category', 'distance')
            ->where('type', $race->distance_type)
            ->first();

        $surfaceApt = $aptitudes->where('category', 'surface')
            ->where('type', strtolower($race->surface))
            ->first();

        return [
            'distance' => $distanceApt?->grade->value ?? 'C',
            'surface' => $surfaceApt?->grade->value ?? 'C',
            'overall' => $this->calculateOverallMatch($distanceApt, $surfaceApt),
        ];
    }

    /**
     * Calculate overall aptitude match grade
     *
     * @param \App\Models\Aptitude|null $distanceApt
     * @param \App\Models\Aptitude|null $surfaceApt
     * @return string
     */
    private function calculateOverallMatch($distanceApt, $surfaceApt): string
    {
        $gradeValues = [
            'SS' => 8, 'S' => 7, 'A' => 6, 'B' => 5,
            'C' => 4, 'D' => 3, 'E' => 2, 'F' => 1, 'G' => 0,
        ];

        $distanceValue = $gradeValues[$distanceApt?->grade->value ?? 'C'] ?? 4;
        $surfaceValue = $gradeValues[$surfaceApt?->grade->value ?? 'C'] ?? 4;

        $average = ($distanceValue + $surfaceValue) / 2;

        return match (true) {
            $average >= 7.5 => 'SS',
            $average >= 6.5 => 'S',
            $average >= 5.5 => 'A',
            $average >= 4.5 => 'B',
            $average >= 3.5 => 'C',
            $average >= 2.5 => 'D',
            $average >= 1.5 => 'E',
            $average >= 0.5 => 'F',
            default => 'G',
        };
    }
}
```text

### 4.3 Neuron\RaceStrategyService

AI-powered strategy recommendation service.

```php
<?php

namespace App\Services\Race;

use App\Models\{Character, RaceDefinition};
use App\Services\AI\AdviceService;
use App\Neuron\Agents\RaceStrategyAgent;

/**
 * Race Strategy Recommendation Service
 *
 * Provides AI-powered strategic recommendations for races.
 */
class RaceStrategyService
{
    public function __construct(
        private RaceStrategyAgent $agent,
        private AdviceService $aiService,
        private RaceConditionService $conditionService
    ) {}

    /**
     * Get AI strategy recommendation
     *
     * @param int $characterId
     * @param int $raceId
     * @return array
     */
    public function getStrategy(int $characterId, int $raceId): array
    {
        $character = Character::with(['aptitudes', 'skills'])->findOrFail($characterId);
        $race = RaceDefinition::findOrFail($raceId);

        // Get analytical data
        $analysis = $this->analysisService->analyze($character, $race);

        // Build AI context
        $context = $this->buildContext($character, $race, $analysis);

        // Get AI advice
        $advice = $this->aiService->getAdvice(
            context: $context,
            topic: 'race_strategy',
            agent: $this->agent
        );

        return [
            'analysis' => $analysis,
            'ai_recommendation' => $advice,
        ];
    }

    /**
     * Build AI context for strategy generation
     *
     * @param Character $character
     * @param RaceDefinition $race
     * @param array $analysis
     * @return string
     */
    private function buildContext(
        Character $character,
        RaceDefinition $race,
        array $analysis
    ): string {
        return <<<CONTEXT
        Race: {$race->name} ({$race->grade}, {$race->distance}m, {$race->surface})

        Character: {$character->name}
        Stats: Speed {$character->current_stats['speed']}, Stamina {$character->current_stats['stamina']},
        Power {$character->current_stats['power']}, Guts {$character->current_stats['guts']}, Wit
        {$character->current_stats['wit']}

        Aptitudes:
        - Distance ({$race->distance_type}): {$analysis['aptitude_match']['distance']}
        - Surface ({$race->surface}): {$analysis['aptitude_match']['surface']}

        Analysis:
        - Readiness Score: {$analysis['readiness']['score']}%
        - Win Probability: {$analysis['win_probability']}
        - Recommended Style: {$analysis['running_style']['recommended']}

        Skills: {$this->formatSkills($character->skills)}
        CONTEXT;
    }

    /**
     * Format skills for context
     *
     * @param \Illuminate\Support\Collection $skills
     * @return string
     */
    private function formatSkills($skills): string
    {
        return $skills->pluck('name')->join(', ') ?: 'None';
    }
}
```

---

## 5. API Specification

### 5.1 Endpoint Overview

| Method | Endpoint | Description | Auth Required |
| --- | --- | --- | --- |
| GET | `/api/v1/races` | List/search race calendar | Yes |
| GET | `/api/v1/races/{id}` | Get race details | Yes |
| GET | `/api/v1/races/{id}/analysis` | Get race analysis for character | Yes |
| POST | `/api/v1/races/strategy` | Get AI strategy recommendation | Yes |
| POST | `/api/v1/careers/{id}/race-result` | Record race result | Yes |
| POST | `/api/v1/races/sync` | Sync race definitions | Admin |

### 5.2 Get Race Analysis

**Endpoint**: `GET /api/v1/races/{id}/analysis`

**Query Parameters**:

- `character_id` (required): Character ID for analysis
- `track_condition` (optional): good, yielding, soft, heavy

**Request Example**:

```http
GET /api/v1/races/101/analysis?character_id=123&track_condition=good
Authorization: Bearer {token}
Accept: application/json
```text

**Success Response** (200 OK):

```json
{
    "data": {
        "race": {
            "id": 101,
            "name": "Japan Cup",
            "grade": "G1",
            "distance": 2400,
            "distance_type": "medium",
            "surface": "Turf"
        },
        "readiness": {
            "score": 85,
            "recommendation": "recommended",
            "stat_analysis": {
                "speed": {
                    "current": 950,
                    "target": 900,
                    "gap": 0,
                    "status": "optimal",
                    "percentage": 105.6
                },
                "stamina": {
                    "current": 820,
                    "target": 900,
                    "gap": 80,
                    "status": "borderline",
                    "percentage": 91.1
                }
            },
            "total_gap": 80
        },
        "win_probability": 0.4235,
        "win_breakdown": {
            "stat_score": 85.3,
            "aptitude_score": 92.5,
            "skill_score": 78.0,
            "mood_score": 90.0,
            "total_score": 86.7
        },
        "running_style": {
            "recommended": "late_surger",
            "confidence": 0.85,
            "alternatives": [
                {"style": "pace_chaser", "score": 82.3},
                {"style": "end_closer", "score": 78.1}
            ]
        },
        "aptitude_match": {
            "distance": "A",
            "surface": "S",
            "overall": "A"
        },
        "weather": {
            "condition": "good",
            "modifiers": {
                "speed": 1.0,
                "stamina": 1.0
            },
            "skill_recommendations": [],
            "description": "Optimal conditions for racing"
        }
    }
}
```

### 5.3 Get AI Strategy

**Endpoint**: `POST /api/v1/races/strategy`

**Request Body**:

```json
{
    "character_id": 123,
    "race_definition_id": 101
}
```text

**Success Response** (200 OK):

```json
{
    "data": {
        "analysis": {
            "readiness": {"score": 85},
            "win_probability": 0.4235
        },
        "ai_recommendation": {
            "recommended_style": "Late Surger",
            "confidence": 0.88,
            "reasoning": "Character has high Power stats (950) which favor late acceleration on Medium distance
            races. Stamina is slightly below optimal, but adequate for this strategy.",
            "skill_priority": [
                "Arc Maestro",
                "Stamina Recovery",
                "Corner Acceleration"
            ],
            "weather_note": "If track becomes Heavy, consider 'Muddy Track' skill.",
            "preparation_advice": "Focus on 1-2 more Stamina training sessions before the race to reach the 900
            threshold for optimal performance."
        }
    }
}
```

### 5.4 Record Race Result

**Endpoint**: `POST /api/v1/careers/{id}/race-result`

**Request Body**:

```json
{
    "race_definition_id": 101,
    "placement": 1,
    "fans_gained": 15000,
    "strategy_used": "late_surger",
    "mood_after": "great",
    "rewards": {
        "skill_points": 45,
        "stats": {
            "speed": 10,
            "stamina": 5
        }
    }
}
```text

**Success Response** (201 Created):

```json
{
    "data": {
        "id": 501,
        "race_name": "Japan Cup",
        "placement": 1,
        "fans_gained": 15000,
        "total_fans": 125000,
        "rewards_applied": {
            "skill_points": 45,
            "stat_gains": {"speed": 10, "stamina": 5}
        },
        "created_at": "2026-01-24T15:30:00Z"
    }
}
```

---

## 6. Database Schema

### 6.1 Table: `ucp_race_definitions`

Reference data for all races synchronized from external APIs.

```sql
CREATE TABLE ucp_race_definitions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    external_id VARCHAR(100) NULL COMMENT 'External API identifier',
    name VARCHAR(255) NOT NULL,
    name_jp VARCHAR(255) NULL,
    grade ENUM('G1', 'G2', 'G3', 'OP', 'Pre-OP') NOT NULL,
    surface ENUM('Turf', 'Dirt') NOT NULL,
    distance SMALLINT UNSIGNED NOT NULL COMMENT 'Distance in meters',
    distance_type ENUM('sprint', 'mile', 'medium', 'long') NOT NULL,
    track_location VARCHAR(100) NULL COMMENT 'e.g., Tokyo, Kyoto',
    track_direction ENUM('clockwise', 'counter_clockwise') NULL,
    month TINYINT UNSIGNED NOT NULL CHECK (month BETWEEN 1 AND 12),
    half TINYINT UNSIGNED NOT NULL COMMENT '1=first half, 2=second half',
    requirements JSON NULL COMMENT 'Base stat requirements',
    metadata JSON NULL COMMENT 'Additional race data',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_external (external_id),
    INDEX idx_grade (grade),
    INDEX idx_surface (surface),
    INDEX idx_distance_type (distance_type),
    INDEX idx_month_half (month, half)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```text

### 6.2 Table: `ucp_race_results`

Historical record of races participated in by characters.

```sql
CREATE TABLE ucp_race_results (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    career_id BIGINT UNSIGNED NOT NULL,
    race_definition_id BIGINT UNSIGNED NOT NULL,
    turn_number TINYINT UNSIGNED NOT NULL,
    placement TINYINT UNSIGNED NOT NULL COMMENT 'Finish position (1-18)',
    fans_gained INT UNSIGNED NOT NULL DEFAULT 0,
    rewards JSON NULL COMMENT '{"skill_points": 45, "stats": {...}}',
    mood_after ENUM('awful', 'bad', 'normal', 'good', 'great') NULL,
    strategy_used VARCHAR(50) NULL COMMENT 'Running style used',
    track_condition VARCHAR(20) NULL COMMENT 'Weather at race time',
    prediction_accuracy DECIMAL(5,2) NULL COMMENT 'Comparison to predicted outcome',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (career_id) REFERENCES ucp_careers(id) ON DELETE CASCADE,
    FOREIGN KEY (race_definition_id) REFERENCES ucp_race_definitions(id),
    INDEX idx_career_turn (career_id, turn_number),
    INDEX idx_placement (placement),
    INDEX idx_race_definition (race_definition_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 7. AI Integration

### 7.1 Race Strategy Agent

**Agent Class**: `App\Neuron\Agents\RaceStrategyAgent`

**System Prompt**:

```text
You are an expert Umamusume race strategist with deep knowledge of optimal racing strategies.

Analyze the following race context and provide strategic recommendations:

**Race Details:**
- Race: {{ race_name }} ({{ grade }}, {{ distance }}m, {{ surface }})
- Track: {{ location }}, {{ direction }}

**Character State:**
- Name: {{ character_name }}
- Stats: Speed {{ speed }}, Stamina {{ stamina }}, Power {{ power }}, Guts {{ guts }}, Wit {{ wit }}
- Aptitudes: Distance {{ distance_apt }}, Surface {{ surface_apt }}
- Skills: {{ skills_list }}

**Analysis Data:**
- Readiness Score: {{ readiness_score }}%
- Win Probability: {{ win_probability }}
- Stat Gaps: {{ stat_gaps }}

**Task:**
Determine the optimal running style and provide actionable preparation advice.

**Output Format (JSON):**
{
    "recommended_style": "front_runner|pace_chaser|late_surger|end_closer",
    "confidence": 0.0-1.0,
    "reasoning": "Brief explanation of style choice",
    "skill_priority": ["skill1", "skill2", "skill3"],
    "weather_note": "Advice for non-optimal conditions",
    "preparation_advice": "Training recommendations before race"
}
```text

**Available Tools**:

```php
[
    'RaceAnalysisTool' => 'Query detailed race requirements',
    'StatGapTool' => 'Calculate stat deficiencies',
    'AptitudeMatchTool' => 'Verify aptitude compatibility',
]
```

---

## 8. Business Logic

### 8.1 Race Grade System

| Grade | Difficulty | Fan Reward Range | Stat Requirements |
| --- | --- | --- | --- |
| G1 | Highest | 10,000-25,000 | 900-1100+ |
| G2 | High | 5,000-15,000 | 800-950 |
| G3 | Medium | 2,000-8,000 | 700-850 |
| OP | Low-Medium | 1,000-4,000 | 600-750 |
| Pre-OP | Low | 500-2,000 | 500-650 |

### 8.2 Distance Categories

| Type | Range (meters) | Primary Stats | Suitable Styles |
| --- | --- | --- | --- |
| Sprint | < 1400 | Speed, Power | Front Runner, Pace Chaser |
| Mile | 1400-1799 | Speed, Stamina | Pace Chaser, Late Surger |
| Medium | 1800-2399 | Stamina, Speed | Late Surger, End Closer |
| Long | ≥ 2400 | Stamina, Guts | End Closer, Late Surger |

### 8.3 Fan Gain Formula

```text
Base Fans = Race Grade Base × Placement Multiplier

Placement Multipliers:
1st: 1.0
2nd: 0.6
3rd: 0.4
4th-6th: 0.2
7th+: 0.1

Bonuses:
- First G1 Win: +50%
- Scenario Key Race: +30%
- Perfect Aptitude Match (SS/SS): +20%
```text

### 8.4 Running Style Mechanics

**Front Runner**:

- Pros: Early position control, lower stamina drain in short races
- Cons: High risk of being caught, requires high Speed
- Best For: Sprint, Mile distances with A+ Speed aptitude

**Pace Chaser**:

- Pros: Balanced position, moderate energy use
- Cons: Competitive positioning required
- Best For: Mile, Medium distances with balanced stats

**Late Surger**:

- Pros: Strong final stretch acceleration
- Cons: Requires precise timing
- Best For: Medium distances with high Power

**End Closer**:

- Pros: Saves stamina, explosive finish
- Cons: Risk of running out of track
- Best For: Long distances with high Power/Guts

---

## 9. Integration Points

### 9.1 External API Integration

**Primary Source**: `umapyoi.net/api/races`
**Fallback**: `gametora.com/api/races`

**Sync Schedule**: Daily at 00:00 UTC
**Data Cached**: 24 hours

```php
// Sync job
Schedule::call(function () {
    app(RaceConditionService::class)->syncRaceDefinitions();
})->daily();
```

### 9.2 OCR Integration

Extract race results from screenshots:

```php
$ocrResult = app(TesseractService::class)->extractRaceResult($imagePath);

// Expected output
[
    'race_name' => 'Japan Cup',
    'placement' => 1,
    'fans_gained' => 15000,
]
```text

### 9.3 Character Service Integration

```php
// Read character for analysis
$character = Character::with(['aptitudes', 'skills'])->find($id);

// Update after race
$character->increment('total_fans', $fansGained);
```

---

## 10. Error Handling

### 10.1 Exception Hierarchy

```php
App\Exceptions\RaceException (Base)
├── RaceNotFoundException
├── InvalidPlacementException
├── RaceDefinitionSyncException
└── AnalysisFailedException
```text

### 10.2 Error Codes

| Code | HTTP Status | Description | Resolution |
| --- | --- | --- | --- |
| `RACE_NOT_FOUND` | 404 | Race definition not found | Verify race ID |
| `RACE_INVALID_PLACEMENT` | 422 | Invalid placement value | Use 1-18 |
| `RACE_SYNC_FAILED` | 503 | External API unavailable | Retry later |
| `RACE_ANALYSIS_FAILED` | 500 | Analysis calculation error | Check character data |

---

## 11. Performance Optimization

### 11.1 Caching Strategy

```php
// Race definitions (static data)
Cache::tags(['races'])->remember('race:calendar:' . $month, now()->addDay(), ...);

// Analysis results
Cache::tags(['race-analysis'])->remember('race:analysis:' . $key, now()->addHours(3), ...);
```

### 11.2 Performance Targets

| Operation | Target | Measurement |
| --- | --- | --- |
| Calendar retrieval | < 50ms | p95 |
| Race analysis | < 200ms | p95 |
| AI strategy generation | < 3s | p95 |
| Result recording | < 150ms | p95 |

---

## 12. Security Considerations

### 12.1 Authorization

```php
public function analyze(User $user, RaceDefinition $race, Character $character): bool
{
    return $user->id === $character->user_id;
}
```text

### 12.2 Input Validation

```php
[
    'race_definition_id' => 'required|exists:ucp_race_definitions,id',
    'placement' => 'required|integer|min:1|max:18',
    'fans_gained' => 'required|integer|min:0',
]
```

---

## 13. Testing Strategy

### 13.1 Unit Tests

```php
test('requirement analyzer calculates correct thresholds', function () {
    $race = RaceDefinition::factory()->make(['grade' => 'G1', 'distance_type' => 'medium']);
    $character = Character::factory()->make(['current_stats' => ['speed' => 900]]);

    $analyzer = app(RaceRequirementAnalyzer::class);
    $analysis = $analyzer->analyze($character, $race);

    expect($analysis->readinessScore)->toBeGreaterThan(70);
});

test('win probability increases with better stats', function () {
    $race = RaceDefinition::factory()->make();
    $weakChar = Character::factory()->make(['current_stats' => ['speed' => 500]]);
    $strongChar = Character::factory()->make(['current_stats' => ['speed' => 1000]]);

    $calc = app(RaceConditionService::class);

    $weakProb = $calc->calculate($weakChar, $race);
    $strongProb = $calc->calculate($strongChar, $race);

    expect($strongProb['probability'])->toBeGreaterThan($weakProb['probability']);
});

test('running style optimizer recommends correct style for sprint', function () {
    $race = RaceDefinition::factory()->make(['distance_type' => 'sprint']);
    $character = Character::factory()->create();

    // Create high Speed aptitude
    $character->aptitudes()->create([
        'category' => 'style',
        'type' => 'front_runner',
        'grade' => 'S',
        'bonus_value' => 15,
    ]);

    $optimizer = app(RunningStyleOptimizer::class);
    $result = $optimizer->optimize($character, $race);

    expect($result['recommended_style'])->toBe('front_runner')
        ->and($result['confidence'])->toBeGreaterThan(0.5);
});

test('weather impact calculator applies correct modifiers', function () {
    $character = Character::factory()->make();
    $calculator = app(WeatherImpactCalculator::class);

    $result = $calculator->calculate($character, TrackCondition::Heavy);

    expect($result['modifiers']['speed'])->toBe(0.85)
        ->and($result['modifiers']['stamina'])->toBe(1.15)
        ->and($result['skill_recommendations'])->toContain('Muddy Track');
});

test('distance type determination is accurate', function () {
    $service = app(RaceConditionService::class);

    expect($service->determineDistanceType(1200))->toBe('sprint')
        ->and($service->determineDistanceType(1600))->toBe('mile')
        ->and($service->determineDistanceType(2000))->toBe('medium')
        ->and($service->determineDistanceType(2800))->toBe('long');
});
```text

### 13.2 Feature Tests

```php
// tests/Feature/RaceAnalysisTest.php

test('user can get race analysis for their character', function () {
    $user = User::factory()->create();
    $character = Character::factory()->for($user)->create([
        'current_stats' => ['speed' => 900, 'stamina' => 850, 'power' => 800],
    ]);
    $race = RaceDefinition::factory()->create();

    $response = $this->actingAs($user)
        ->getJson("/api/v1/races/{$race->id}/analysis?character_id={$character->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'race',
                'readiness',
                'win_probability',
                'running_style',
                'aptitude_match',
            ]
        ]);
});

test('user cannot analyze race with another users character', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $character = Character::factory()->for($user2)->create();
    $race = RaceDefinition::factory()->create();

    $response = $this->actingAs($user1)
        ->getJson("/api/v1/races/{$race->id}/analysis?character_id={$character->id}");

    $response->assertStatus(403);
});

test('race result is recorded correctly', function () {
    $user = User::factory()->create();
    $career = CareerRun::factory()->for($user)->create();
    $race = RaceDefinition::factory()->create();

    $response = $this->actingAs($user)
        ->postJson("/api/v1/careers/{$career->id}/race-result", [
            'race_definition_id' => $race->id,
            'placement' => 1,
            'fans_gained' => 15000,
            'strategy_used' => 'late_surger',
        ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('ucp_race_results', [
        'career_id' => $career->id,
        'race_definition_id' => $race->id,
        'placement' => 1,
        'fans_gained' => 15000,
    ]);

    $career->refresh();
    expect($career->total_fans)->toBe(15000);
});

test('race calendar returns races for specified month', function () {
    RaceDefinition::factory()->create(['month' => 3, 'half' => 1]);
    RaceDefinition::factory()->create(['month' => 3, 'half' => 2]);
    RaceDefinition::factory()->create(['month' => 5, 'half' => 1]);

    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->getJson('/api/v1/races?month=3');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('race calendar filters by grade', function () {
    RaceDefinition::factory()->create(['grade' => 'G1', 'month' => 6]);
    RaceDefinition::factory()->create(['grade' => 'G2', 'month' => 6]);
    RaceDefinition::factory()->create(['grade' => 'G3', 'month' => 6]);

    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->getJson('/api/v1/races?month=6&grade=G1');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
});
```

### 13.3 Integration Tests

```php
// tests/Integration/RaceStrategyAITest.php

test('AI strategy agent provides valid recommendations', function () {
    $character = Character::factory()->create([
        'current_stats' => ['speed' => 900, 'stamina' => 750, 'power' => 950],
    ]);
    $race = RaceDefinition::factory()->create(['distance_type' => 'medium']);

    $service = app(RaceStrategyService::class);
    $strategy = $service->getStrategy($character->id, $race->id);

    expect($strategy)->toHaveKeys(['analysis', 'ai_recommendation'])
        ->and($strategy['ai_recommendation'])->toHaveKeys([
            'recommended_style',
            'confidence',
            'reasoning',
        ])
        ->and($strategy['ai_recommendation']['confidence'])->toBeBetween(0, 1);
});

test('external race sync creates or updates definitions', function () {
    // Mock external API response
    Http::fake([
        'umapyoi.net/api/races' => Http::response([
            [
                'id' => 'ext_001',
                'name' => 'Tokyo Yushun (Japanese Derby)',
                'name_jp' => '東京優駿（日本ダービー）',
                'grade' => 'G1',
                'surface' => 'Turf',
                'distance' => 2400,
                'location' => 'Tokyo',
                'month' => 5,
                'half' => 2,
            ]
        ], 200),
    ]);

    $service = app(RaceConditionService::class);
    $syncedCount = $service->syncRaceDefinitions();

    expect($syncedCount)->toBe(1);

    $this->assertDatabaseHas('ucp_race_definitions', [
        'external_id' => 'ext_001',
        'name' => 'Tokyo Yushun (Japanese Derby)',
        'grade' => 'G1',
    ]);
});

test('race analysis aggregates all components correctly', function () {
    $character = Character::factory()->create();
    $character->aptitudes()->createMany([
        ['category' => 'distance', 'type' => 'medium', 'grade' => 'A'],
        ['category' => 'surface', 'type' => 'turf', 'grade' => 'S'],
        ['category' => 'style', 'type' => 'late_surger', 'grade' => 'A'],
    ]);

    $race = RaceDefinition::factory()->create([
        'distance_type' => 'medium',
        'surface' => 'Turf',
    ]);

    $service = app(RaceConditionService::class);
    $analysis = $service->analyze($character, $race);

    expect($analysis)->toHaveKeys([
        'race',
        'readiness',
        'win_probability',
        'running_style',
        'aptitude_match',
        'weather',
    ])
    ->and($analysis['aptitude_match']['distance'])->toBe('A')
    ->and($analysis['aptitude_match']['surface'])->toBe('S');
});
```text

### 13.4 Performance Tests

```php
// tests/Performance/RaceAnalysisPerformanceTest.php

test('race analysis completes within performance target', function () {
    $character = Character::factory()->create();
    $race = RaceDefinition::factory()->create();

    $service = app(RaceConditionService::class);

    $startTime = microtime(true);
    $service->analyze($character, $race);
    $endTime = microtime(true);

    $executionTime = ($endTime - $startTime) * 1000; // Convert to ms

    expect($executionTime)->toBeLessThan(200); // 200ms target
});

test('race calendar query is performant with 100 races', function () {
    RaceDefinition::factory()->count(100)->create(['month' => 6]);

    $service = app(RaceConditionService::class);

    $startTime = microtime(true);
    $service->getCalendar(6);
    $endTime = microtime(true);

    $executionTime = ($endTime - $startTime) * 1000;

    expect($executionTime)->toBeLessThan(50); // 50ms target
});
```

### 13.5 Test Data Factories

```php
// database/factories/RaceDefinitionFactory.php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RaceDefinitionFactory extends Factory
{
    public function definition(): array
    {
        $distances = [
            'sprint' => [1000, 1200, 1400],
            'mile' => [1600, 1800],
            'medium' => [2000, 2200, 2400],
            'long' => [2500, 3000, 3200],
        ];

        $distanceType = $this->faker->randomElement(['sprint', 'mile', 'medium', 'long']);
        $distance = $this->faker->randomElement($distances[$distanceType]);

        return [
            'external_id' => 'test_' . $this->faker->unique()->numerify('####'),
            'name' => $this->faker->words(3, true) . ' Stakes',
            'name_jp' => null,
            'grade' => $this->faker->randomElement(['G1', 'G2', 'G3', 'OP', 'Pre-OP']),
            'surface' => $this->faker->randomElement(['Turf', 'Dirt']),
            'distance' => $distance,
            'distance_type' => $distanceType,
            'track_location' => $this->faker->randomElement(['Tokyo', 'Kyoto', 'Nakayama', 'Hanshin']),
            'track_direction' => $this->faker->randomElement(['clockwise', 'counter_clockwise']),
            'month' => $this->faker->numberBetween(1, 12),
            'half' => $this->faker->numberBetween(1, 2),
        ];
    }

    public function g1(): self
    {
        return $this->state(fn (array $attributes) => [
            'grade' => 'G1',
        ]);
    }

    public function sprint(): self
    {
        return $this->state(fn (array $attributes) => [
            'distance_type' => 'sprint',
            'distance' => 1200,
        ]);
    }

    public function turf(): self
    {
        return $this->state(fn (array $attributes) => [
            'surface' => 'Turf',
        ]);
    }
}
```text

```php
// database/factories/RaceResultFactory.php

namespace Database\Factories;

use App\Models\{CareerRun, RaceDefinition};
use Illuminate\Database\Eloquent\Factories\Factory;

class RaceResultFactory extends Factory
{
    public function definition(): array
    {
        $placement = $this->faker->numberBetween(1, 18);

        // Calculate fans based on placement
        $fansGained = match (true) {
            $placement === 1 => $this->faker->numberBetween(10000, 25000),
            $placement === 2 => $this->faker->numberBetween(5000, 12000),
            $placement === 3 => $this->faker->numberBetween(2000, 6000),
            default => $this->faker->numberBetween(500, 2000),
        };

        return [
            'career_id' => CareerRun::factory(),
            'race_definition_id' => RaceDefinition::factory(),
            'turn_number' => $this->faker->numberBetween(1, 78),
            'placement' => $placement,
            'fans_gained' => $fansGained,
            'rewards' => [
                'skill_points' => $placement <= 3 ? $this->faker->numberBetween(30, 50) : 0,
                'stats' => $placement === 1 ? ['speed' => 10, 'stamina' => 5] : null,
            ],
            'mood_after' => $placement <= 3 ? 'great' : ($placement <= 6 ? 'good' : 'normal'),
            'strategy_used' => $this->faker->randomElement([
                'front_runner',
                'pace_chaser',
                'late_surger',
                'end_closer',
            ]),
            'track_condition' => $this->faker->randomElement(['good', 'yielding', 'soft', 'heavy']),
        ];
    }

    public function winner(): self
    {
        return $this->state(fn (array $attributes) => [
            'placement' => 1,
            'fans_gained' => 15000,
            'mood_after' => 'great',
        ]);
    }
}
```

---

## 14. Frontend View Data Contracts

This section defines the exact JSON shape passed by `RaceController` to the Alpine.js components for
the Calendar and Targets views.

### 14.1 Race Calendar View (`/races/calendar`)

Data is embedded via `<script id="race-calendar-data" type="application/json">` and consumed by the
`raceCarouselView()` Alpine component.

**Controller method**: `RaceController::calendar()`

Each race object in the array:

| Key | Type | Source field | Description |
| --- | --- | --- | --- |
| `id` | int | `game_races.id` | Primary key |
| `name` | string | `name_en` | English race name |
| `grade` | string | `grade` | G1 / G2 / G3 / OP / Pre-OP / Debut |
| `surface` | string | `surface` | `turf` or `dirt` |
| `distance` | int | `distance_meters` | Distance in metres |
| `distanceCategory` | string | `distance_category` | sprint / mile / medium / long / super\_long |
| `phase` | string | `phase` | junior / classic / senior / all |
| `month` | string | `month_label` | In-game month string (e.g. "April") |
| `year` | int | `year_in_scenario` | 1 = Junior, 2 = Classic, 3 = Senior |
| `venue` | string | `venue` | Track venue name |
| `fanRequirement` | int | `fan_requirement` | Minimum fans required to enter |
| `fansReward` | int | `fans_reward` | Fans awarded on win |
| `spReward` | int | `sp_reward` | SP awarded on win |
| `statRequirements` | object\|null | `stat_requirements` | Stat threshold map (e.g. `{"speed": 700}`) |
| `isUraFinale` | bool | `is_ura_finale` | True for URA Finale races |
| `status` | string | computed | "upcoming" (static placeholder; will reflect run data in a future version) |

**Important**: The Alpine component must reference `race.fansReward` (not `race.fanCount`) for calendar data.

### 14.2 Race Targets View (`/races/targets`)

Data is embedded via `window.pageData` before Vite and consumed by the `raceTargets()` Alpine component.

**Controller method**: `RaceController::targets()`

Each race object in `window.pageData.races`:

| Key | Type | Source field | Description |
| --- | --- | --- | --- |
| `id` | int | `game_races.id` | Primary key |
| `name` | string | `name_en` | English race name |
| `grade` | string | `grade` | G1 / G2 / G3 / OP / Pre-OP / Debut |
| `distance` | int | `distance_meters` | Distance in metres |
| `distanceCategory` | string | `distance_category` | sprint / mile / medium / long / super\_long |
| `type` | string | `surface` | `turf` or `dirt` |
| `phase` | string | `phase` | junior / classic / senior / all |
| `fanCount` | int | `fans_reward` | Fans awarded on win |
| `fanRequirement` | int | `fan_requirement` | Minimum fans required to enter |
| `spReward` | int | `sp_reward` | SP awarded on win |
| `statRequirements` | object\|null | `stat_requirements` | Stat threshold map |
| `year` | int | `year_in_scenario` | 1 = Junior, 2 = Classic, 3 = Senior |
| `month` | string | `month_label` | In-game month string |
| `isUraFinale` | bool | `is_ura_finale` | True for URA Finale races |

### 14.3 Calendar Alpine Component: Filter State

The `raceCarouselView()` component maintains the following filter state:

| Property | Type | Values | Description |
| --- | --- | --- | --- |
| `activeSurfaceFilter` | string\|null | `turf`, `dirt`, or `null` | Surface filter |
| `activeDistanceFilter` | string\|null | `sprint`, `mile`, `medium`, `long`, `super_long`, or `null` | Distance category filter |
| `activePhaseFilter` | string\|null | `junior`, `classic`, `senior`, `all`, or `null` | Career phase filter |
| `activeMonthFilter` | string\|null | In-game month label string or `null` | Month filter |

Filters use `null` = show all. Surface and distance are independent filters, never combined in a single filter property.

### 14.4 Targets Alpine Component: Filter State

The `raceTargets()` component maintains the following filter state:

| Property | Type | Values | Description |
| --- | --- | --- | --- |
| `filterGrade` | string\|null | `G1`, `G2`, `G3`, `OP`, `Pre-OP`, `Debut`, or `null` | Grade filter |
| `filterPhase` | string\|null | `junior`, `classic`, `senior`, `all`, or `null` | Phase filter |

---

## 15. Appendices

### Appendix A: Race Grade Fan Rewards

| Grade | Position | Min Fans | Max Fans | SP Reward |
| --- | --- | --- | --- | --- |
| **G1** | 1st | 15,000 | 25,000 | 50 |
| | 2nd | 8,000 | 15,000 | 30 |
| | 3rd | 4,000 | 8,000 | 20 |
| **G2** | 1st | 8,000 | 15,000 | 40 |
| | 2nd | 4,000 | 8,000 | 25 |
| | 3rd | 2,000 | 4,000 | 15 |
| **G3** | 1st | 4,000 | 8,000 | 30 |
| | 2nd | 2,000 | 4,000 | 20 |
| | 3rd | 1,000 | 2,000 | 10 |
| **OP** | 1st | 2,000 | 4,000 | 20 |
| | 2nd | 1,000 | 2,000 | 10 |
| | 3rd | 500 | 1,000 | 5 |

### Appendix B: Famous Race Examples

**Sprint Races**:

- Takamatsunomiya Kinen (G1, 1200m, Turf)
- Sprinters Stakes (G1, 1200m, Turf)

**Mile Races**:

- Mile Championship (G1, 1600m, Turf)
- Yasuda Kinen (G1, 1600m, Turf)

**Medium Races**:

- Tokyo Yushun (Japanese Derby) (G1, 2400m, Turf)
- Kikuka Sho (Japanese St. Leger) (G1, 3000m, Turf)

**Long Races**:

- Tenno Sho (Spring) (G1, 3200m, Turf)
- Arima Kinen (G1, 2500m, Turf)

### Appendix C: Win Probability Interpretation

| Probability Range | Interpretation | Recommendation |
| --- | --- | --- |
| 0.70 - 1.00 | Very High | Highly recommended |
| 0.50 - 0.69 | High | Recommended |
| 0.35 - 0.49 | Moderate | Possible, prepare well |
| 0.20 - 0.34 | Low | Risky, consider skip |
| 0.00 - 0.19 | Very Low | Not recommended |

### Appendix D: Running Style Stat Priorities

**Front Runner**:

1. Speed (Primary)
2. Power (Secondary)
3. Stamina (Tertiary)
4. Guts (Minor)
5. Wit (Minor)

**Pace Chaser**:

1. Speed (Primary)
2. Stamina (Secondary)
3. Power (Secondary)
4. Wit (Minor)
5. Guts (Minor)

**Late Surger**:

1. Power (Primary)
2. Stamina (Secondary)
3. Speed (Secondary)
4. Guts (Tertiary)
5. Wit (Minor)

**End Closer**:

1. Power (Primary)
2. Stamina (Primary)
3. Guts (Secondary)
4. Speed (Tertiary)
5. Wit (Minor)

### Appendix E: Weather Condition Effects

| Condition | Speed Modifier | Stamina Modifier | Recommended Skills |
| --- | --- | --- | --- |
| Good | 1.00x | 1.00x | Standard loadout |
| Yielding | 0.95x | 1.05x | Track Adaptation |
| Soft | 0.90x | 1.10x | Wet Surface, Muddy Track |
| Heavy | 0.85x | 1.15x | Muddy Track, Dirt Master, Stamina Boost |

### Appendix F: Change Log

| Version | Date | Author | Changes |
| --- | --- | --- | --- |
| 2.4.1 | 2026-03-11 | Development Team | Clarified that `WeatherImpactCalculator` uses planner-side track-condition approximations for readability rather than literal internal game stat subtraction. |
| 2.4.0 | 2026-03-04 | Development Team | Added Section 14 (Frontend View Data Contracts) defining exact JSON shapes for calendar and targets views. Documented split surface/distance filters, phase filter, corrected grade values, fansReward vs fanCount distinction. |
| 2.3.0 | 2026-02-22 | Development Team | Updated to v2.3.0: RaceConditionService, Neuron\RaceStrategyService, ExternalDataService, AdviceService, GameTora fallback, Neuron AI v2.11, status complete |
| 2.2.0 | 2026-01-28 | Development Team | Game-accurate track conditions (Firm/Good/Soft/Heavy with flat stat penalties), corrected aptitude modifiers (S max, A baseline), surface-specific power penalties |
| 2.0.0 | 2026-01-24 | Development Team | Full v2.0.0 alignment, added AI integration, complete analysis engines, comprehensive testing strategy |
| 1.0.0 | 2026-01-23 | Development Team | Initial technical specification |

---

### Document Approval

| Role | Name | Signature | Date |
| --- | --- | --- | --- |
| Tech Lead | [Name] | _________ | 2026-01-24 |
| Product Owner | [Name] | _________ | 2026-01-24 |
| QA Lead | [Name] | _________ | 2026-01-24 |
| AI/ML Lead | [Name] | _________ | 2026-01-24 |

---

### Document Control

**Maintained By**: Backend Development Team
**Review Frequency**: Bi-weekly during active development
**Next Review Date**: 2026-03-07
**Distribution**: Development Team, QA Team, Product Management, Data Science Team

---

End of Document
