# SPEC-002: Training Optimization System - Technical Specification

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Project**: UmamusumeCareerPlanner  
**Author**: AI Development Team  
**Status**: Draft  
**Related Documents**: [PRD-002], [SRS-3.2], [SDS-4.2], [SPEC-001]

---

## Table of Contents

1. [Technical Overview](#1-technical-overview)
2. [Training Prediction Engine](#2-training-prediction-engine)
3. [Support Card Integration](#3-support-card-integration)
4. [Scenario-Specific Mechanics](#4-scenario-specific-mechanics)
5. [API Specification](#5-api-specification)
6. [Database Schema](#6-database-schema)
7. [AI/ML Integration](#7-aiml-integration)
8. [Performance & Caching](#8-performance--caching)
9. [Testing Requirements](#9-testing-requirements)

---

## 1. Technical Overview

### 1.1 Component Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│        TRAINING OPTIMIZATION MODULE (SPEC-002)                  │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  API Layer (Controllers)                                  │   │
│  │  • TrainingPredictionController                          │   │
│  │  • TrainingRecommendationController                      │   │
│  │  • TrainingSessionController                             │   │
│  └──────────────────────────────────────────────────────────┘   │
│                           ↓                                      │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  Service Layer (Business Logic)                           │   │
│  │  • TrainingPredictionService                              │   │
│  │  • TrainingOptimizationService (CQRS)                    │   │
│  │  • SupportCardBonusService                                │   │
│  │  • ScenarioSpecificService (URA vs Unity)                │   │
│  │  • TrainingSessionService                                 │   │
│  └──────────────────────────────────────────────────────────┘   │
│                           ↓                                      │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  Engine Layer (Complex Calculations)                      │   │
│  │  • StatGainCalculationEngine                              │   │
│  │  • BonusMultiplierEngine                                  │   │
│  │  • SkillHintProbabilityEngine                             │   │
│  │  • RankingEngine (effectiveness scoring)                  │   │
│  └──────────────────────────────────────────────────────────┘   │
│                           ↓                                      │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  Repository Layer (Data Access)                           │   │
│  │  • TrainingSessionRepository                              │   │
│  │  • SupportCardRepository                                  │   │
│  │  • SkillHintRepository                                    │   │
│  │  • PredictionHistoryRepository                            │   │
│  └──────────────────────────────────────────────────────────┘   │
│                           ↓                                      │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  Data Layer (Models & Database)                           │   │
│  │  • TrainingSession, SupportCard, SkillHint models        │   │
│  │  • Training cache, session history                        │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 1.2 Core Entities

| Entity | Purpose | Relationships |
| --- | --- | --- |
| Training Session | Single training activity | Has: Stat gains, Mood changes, Skill hints |
| Training Prediction | Forecasted training outcomes | Belongs to: Character, Facility |
| Support Card | Training bonus provider | Has many: Training bonuses, Skill hints |
| Skill Hint | SP cost reduction opportunity | Belongs to: Skill, Support Card |
| Training Recommendation | AI-recommended training choice | Depends on: Goals, Current state |

---

## 2. Training Prediction Engine

### 2.1 Stat Gain Calculation Algorithm

```
FUNCTION calculateStatGains(character, trainingFacility, supportCards)
    
    // Step 1: Base stat gain (facility dependent)
    baseStat[facility] = TRAINING_BASE_STATS[facility]  // 20-60 range
    
    // Step 2: Character modifiers
    characterMod = 1.0
    characterMod *= (1 + character.growthRate[facility] / 100)
    
    // Step 3: Support card bonuses
    supportBonus = 0
    FOR EACH card IN supportCards:
        IF card.providesBonus(facility):
            supportBonus += card.bonusValue
            
            // Check for friendship training (bond >= 80%)
            IF card.bondLevel >= 80:
                supportBonus += card.friendshipBonusValue
                
            // Check for red exclamation (skill hint guaranteed)
            IF card.hasRedExclamation:
                skillHintGuaranteed = TRUE
    
    // Step 4: Facility level multiplier (Unity Cup specific)
    facilityMult = FACILITY_MULTIPLIERS[character.facilityLevel]  // 1.0x to 2.0x
    
    // Step 5: Final calculation
    FOR EACH stat IN [Speed, Stamina, Power, Guts, Wit]:
        statGain[stat] = (
            baseStat[facility] + 
            supportBonus * characterMod * 
            facilityMult
        )
        
        // Round to nearest integer
        statGain[stat] = ROUND(statGain[stat])
    
    RETURN statGain

END FUNCTION
```

### 2.2 Support Card Bonus Matrix

**Support Card Specializations**:

```php
const SUPPORT_CARD_BONUSES = [
    'Speed' => [
        'base_bonus' => 8,
        'friendship_bonus' => 3,
        'level_factors' => [0, 1, 2, 3, 4],  // per limit break
        'red_exclamation_chance' => 0.25,
    ],
    'Stamina' => [
        'base_bonus' => 7,
        'friendship_bonus' => 2,
        'level_factors' => [0, 1, 2, 3, 4],
        'red_exclamation_chance' => 0.20,
    ],
    // ... other stats
];
```

**Meta Tier Support Cards**:

```php
const META_TIER_RANKINGS = [
    'SS' => [
        'Kitasan Black (Power, Speed)' => 'Universal tank, exceptional survival',
        'Narita Brian (Speed, Wit)' => 'Late game specialist, skill focused',
        'Symboli Rudolf (Stamina, Guts)' => 'Endurance powerhouse',
    ],
    'S' => [
        // 15-20 cards with 80%+ effectiveness
    ],
    'A' => [
        // 25-30 cards with 60-80% effectiveness
    ],
];
```

### 2.3 Friendship Training Mechanics

Friendship training is unlocked at 80% bond level:

```php
class FriendshipTrainingCalculation
{
    const BOND_THRESHOLD = 80;  // 80% = bond level max
    
    public function calculateBonus(int $bondLevel, int $participantCount): int
    {
        if ($bondLevel < self::BOND_THRESHOLD) {
            return 0;  // Not eligible
        }
        
        // Participant effects
        // 2 participants: +2 stat bonus
        // 3 participants: +3 stat bonus
        // (Single training facility dedicated to that stat)
        
        $participantBonus = $participantCount - 1;  // -1 for the trainee
        
        return $participantBonus;
    }
    
    /**
     * Example:
     * - Character training Speed with Kitasan (80% bond) and another support card
     * - Speed facility with 3 participants (trainee + 2 support card owners)
     * - Result: Speed gain +2 bonus
     */
}
```

### 2.4 Training Option Ranking Algorithm

```php
class TrainingRankingEngine
{
    public function rankTrainingOptions(
        Character $character,
        array $availableTrainings,
        TrainingGoals $goals
    ): array {
        
        $rankedOptions = [];
        
        foreach ($availableTrainings as $training) {
            $score = 0;
            
            // Component 1: Goal Alignment (0-40 points)
            $goalAlignment = $this->calculateGoalAlignment(
                $training->predictedStatGains,
                $goals
            );
            $score += $goalAlignment * 40;
            
            // Component 2: Stat Efficiency (0-30 points)
            $efficiency = $this->calculateStatEfficiency(
                $training->totalPredictedGain,
                $training->facility->type
            );
            $score += $efficiency * 30;
            
            // Component 3: Scenario-Specific Bonus (0-20 points)
            $scenarioBonus = $this->calculateScenarioBonus(
                $training,
                $character->scenario
            );
            $score += $scenarioBonus * 20;
            
            // Component 4: Skill Hint Probability (0-10 points)
            $skillHintProb = $training->skillHintProbability ?? 0;
            $score += $skillHintProb * 10;
            
            $rankedOptions[] = [
                'training' => $training,
                'score' => $score,
                'components' => [
                    'goal_alignment' => $goalAlignment * 40,
                    'efficiency' => $efficiency * 30,
                    'scenario_bonus' => $scenarioBonus * 20,
                    'skill_hint' => $skillHintProb * 10,
                ]
            ];
        }
        
        // Sort by score (highest first)
        usort($rankedOptions, fn($a, $b) => $b['score'] <=> $a['score']);
        
        return $rankedOptions;
    }
    
    private function calculateGoalAlignment(array $gains, TrainingGoals $goals): float
    {
        $alignment = 0;
        
        foreach ($goals->activeGoals as $goal) {
            if (isset($gains[$goal->stat_type])) {
                $alignment += $gains[$goal->stat_type] / $goal->remainingValue;
            }
        }
        
        return min(1.0, $alignment);  // Normalize to 0-1
    }
}
```

---

## 3. Support Card Integration

### 3.1 Support Card Management

```php
class SupportCard extends Model
{
    protected $fillable = [
        'character_id',
        'card_id',
        'card_name',
        'rarity',        // SSR, SR, R
        'limit_breaks',  // 0-4 (stars)
        'specialization', // Speed, Power, Stamina, Guts, Wit, Pal
        'bond_level',    // 0-100 (0% to 100%)
        'training_bonus',
        'skill_hints_provided',
        'event_skills',
        'career_skills',
    ];
    
    public function getTrainingBonus(string $facility): int
    {
        // Base bonus from specialization + limit break bonuses
        $baseBonus = self::BONUS_VALUES[$this->specialization];
        $limitBreakBonus = $this->limit_breaks * 2;
        
        return $baseBonus + $limitBreakBonus;
    }
    
    public function isFriendshipTrainingAvailable(): bool
    {
        return $this->bond_level >= 80;
    }
    
    public function hasRedExclamation(): bool
    {
        // Determined by support card type and rarity
        return in_array($this->card_id, self::RED_EXCLAMATION_CARDS);
    }
}
```

### 3.2 Support Deck Configuration

```php
class SupportDeck extends Model
{
    protected $fillable = [
        'character_id',
        'support_cards',  // JSON array of 6 card configurations
    ];
    
    const DECK_SIZE = 6;  // Exactly 6 cards: 5 owned + 1 borrowed
    
    public function validateDeckComposition(): bool
    {
        // Verify exactly 6 cards
        if (count($this->support_cards) !== self::DECK_SIZE) {
            return false;
        }
        
        // Verify stat type distribution
        $types = array_map(fn($card) => $card['specialization'], $this->support_cards);
        
        // Ideal distribution: variety across all 5 stats
        // Acceptable: some concentration on target stats
        
        return true;
    }
    
    public function getTotalBonus(string $facility): int
    {
        $total = 0;
        
        foreach ($this->support_cards as $card) {
            if ($card['specialization'] === $facility || $card['specialization'] === 'Pal') {
                $total += $card['bonus'];
            }
        }
        
        return $total;
    }
}
```

### 3.3 Skill Hint Tracking

```php
class SkillHint extends Model
{
    protected $fillable = [
        'character_id',
        'skill_id',
        'support_card_id',
        'hint_count',      // Number of hints acquired
        'total_sp_saved',  // Total SP cost reduction
        'acquired_at',
    ];
    
    const HINT_VALUES = [1, 2, 3, 4];  // Hint counts
    const SP_DISCOUNT_PER_HINT = 20;   // 20% per hint
    const MAX_DISCOUNT = 40;            // 40% maximum (2 hints)
    
    public function calculateFinalSkillCost(int $baseCost): int
    {
        $discountPercentage = min(
            $this->hint_count * self::SP_DISCOUNT_PER_HINT,
            self::MAX_DISCOUNT
        );
        
        $discount = (int)($baseCost * $discountPercentage / 100);
        
        return $baseCost - $discount;
    }
}
```

---

## 4. Scenario-Specific Mechanics

### 4.1 URA Finale Mechanics

**Focus**: Individual character stat optimization

```php
class URAFinaleService
{
    public function predictTrainingOptions(Character $character, SupportDeck $deck): array
    {
        $predictions = [];
        
        // URA Finale has 5 main training facilities
        // Speed, Stamina, Power, Guts, Wit
        
        $facilities = ['Speed', 'Stamina', 'Power', 'Guts', 'Wit'];
        
        foreach ($facilities as $facility) {
            // Calculate stat gains with support card bonuses
            $gains = $this->calculateStatGains(
                $character,
                $facility,
                $deck->getCardsForFacility($facility)
            );
            
            // Check for skill hints
            $skillHints = $this->identifySkillHints(
                $deck->getCardsForFacility($facility)
            );
            
            // Probability of mood/condition changes
            $moodChange = $this->predictMoodChange($character, $facility);
            
            $predictions[] = [
                'facility' => $facility,
                'stat_gains' => $gains,
                'total_gain' => array_sum($gains),
                'skill_hints' => $skillHints,
                'mood_change' => $moodChange,
                'energy_cost' => 20,  // Standard training costs 20% energy
            ];
        }
        
        return $predictions;
    }
    
    public function identifySkillHints(array $facilitySupportCards): array
    {
        $hints = [];
        
        foreach ($facilitySupportCards as $card) {
            // Each card provides specific skills during training
            if ($card->hasRedExclamation()) {
                $hints[] = [
                    'skill_name' => $card->getGuaranteedSkill(),
                    'probability' => 1.0,  // 100% guaranteed
                    'support_card' => $card->name,
                ];
            } else {
                // Normal skill hint chances based on training outcomes
                foreach ($card->providedSkills as $skill) {
                    $hints[] = [
                        'skill_name' => $skill,
                        'probability' => 0.25,  // ~25% chance
                        'support_card' => $card->name,
                    ];
                }
            }
        }
        
        return $hints;
    }
}
```

### 4.2 Unity Cup Mechanics

**Focus**: Team synergy and Spirit Burst optimization

```php
class UnityEupService
{
    public function predictTrainingOptions(
        Character $character,
        array $teamMembers,
        int $spiritBurstGauge
    ): array {
        
        $predictions = [];
        
        // Unity Cup considerations:
        // 1. Team stat distribution (need balanced team)
        // 2. Distance specialization (Sprint/Mile/Medium/Long teams)
        // 3. Spirit Burst filling (4 training = full gauge)
        // 4. Facility level bonuses (1.0x to 2.0x based on team rank)
        
        foreach ($this->getAvailableFacilities() as $facility) {
            $gains = $this->calculateStatGains(
                $character,
                $facility,
                $this->getSupportDeck($character)
            );
            
            // Add Spirit Burst bonus if available
            $spiritBurstBonus = $this->calculateSpiritBurstBonus(
                $facility,
                $spiritBurstGauge
            );
            
            // Team synergy bonus for balanced training
            $teamSynergyBonus = $this->calculateTeamSynergyBonus(
                $character,
                $facility,
                $teamMembers
            );
            
            // Facility level multiplier
            $facilityMultiplier = FACILITY_MULTIPLIERS[$character->facilityLevel];
            
            // Apply all multipliers
            $finalGains = array_map(
                fn($gain) => (int)($gain * (1 + $spiritBurstBonus) * (1 + $teamSynergyBonus) * $facilityMultiplier),
                $gains
            );
            
            $predictions[] = [
                'facility' => $facility,
                'stat_gains' => $finalGains,
                'spirit_burst_potential' => $this->calculateSpiritBurstFilling($facility),
                'team_synergy_bonus' => $teamSynergyBonus,
                'facility_level_boost' => $facilityMultiplier,
            ];
        }
        
        return $predictions;
    }
    
    private function calculateSpiritBurstFilling(string $facility): array
    {
        // 4 training sessions fill the Spirit Burst gauge
        // Full meter grants large stat bonuses + random skill hints
        
        return [
            'training_count_needed' => 4,
            'bonus_stats' => [10, 10, 10, 10, 10],  // +10 to all stats
            'skill_hint_guarantee' => true,
        ];
    }
}
```

---

## 5. API Specification

### 5.1 Training Prediction Endpoints

#### 5.1.1 Get Training Options with Predictions

```http
GET /api/v1/characters/{id}/training-predictions
Authorization: Bearer {token}
```

**Response** (200 OK):
```json
{
    "character_id": 1,
    "current_stats": {
        "speed": 520,
        "stamina": 480,
        "power": 440,
        "guts": 460,
        "wit": 450
    },
    "available_trainings": [
        {
            "facility": "Speed",
            "rank": 1,
            "score": 92.5,
            "predicted_gains": {
                "speed": 45,
                "stamina": 5,
                "power": 3,
                "guts": 2,
                "wit": 1,
                "total": 56
            },
            "support_cards": [
                {
                    "card_id": 1001,
                    "name": "Mejiro Dober",
                    "bonus": 12,
                    "bond_level": 85,
                    "friendship_bonus": 3
                },
                {
                    "card_id": 1002,
                    "name": "Tokai Teio",
                    "bonus": 8,
                    "bond_level": 70
                }
            ],
            "skill_hints": [
                {
                    "skill_name": "Lane Guidance",
                    "probability": 1.0,
                    "source": "Mejiro Dober",
                    "guaranteed": true
                },
                {
                    "skill_name": "Going Strong",
                    "probability": 0.25,
                    "source": "Tokai Teio"
                }
            ],
            "mood_prediction": {
                "current": "Good",
                "after_training": "Normal",
                "change": -1
            },
            "energy_cost": 20,
            "efficiency_rating": "Excellent",
            "goal_alignment": "High"
        },
        {
            "facility": "Stamina",
            "rank": 2,
            "score": 88.3,
            ...
        }
    ],
    "scenario": "URA",
    "current_goals": [
        {
            "goal_type": "stat",
            "target_stat": "speed",
            "target_value": 800,
            "remaining": 280,
            "aligned_trainings": ["Speed"]
        }
    ]
}
```

#### 5.1.2 Get Training Recommendation

```http
GET /api/v1/characters/{id}/training-recommendation
Authorization: Bearer {token}
```

**Response** (200 OK):
```json
{
    "recommended_facility": "Speed",
    "reasoning": "Highest alignment with Speed goal (280 remaining). Red exclamation on Mejiro Dober guarantees Lane Guidance skill hint.",
    "recommendation_details": {
        "primary_reason": "goal_alignment",
        "secondary_reason": "skill_hint_opportunity",
        "confidence": 0.96,
        "expected_benefit": 56,
        "estimated_time_to_goal": 5,
        "turn_number": 150,
        "days_until_race": 15
    },
    "alternative_options": [
        {
            "facility": "Stamina",
            "reasoning": "Secondary goal support",
            "score": 88.3
        },
        {
            "facility": "Power",
            "reasoning": "Balanced training",
            "score": 82.1
        }
    ],
    "warnings": [
        "Energy at 78% - next training may incur fatigue penalty",
        "No mood bonuses active - consider rest for mood recovery"
    ]
}
```

### 5.2 Training Session Endpoints

#### 5.2.1 Create Training Session

```http
POST /api/v1/characters/{id}/training-sessions
Content-Type: application/json
Authorization: Bearer {token}

{
    "facility": "Speed",
    "actual_mood": "Good",
    "actual_energy": 78,
    "skills_acquired": [
        {
            "skill_id": 5,
            "cost": 96,  // 20% discount from hints
            "hints_used": 1
        }
    ]
}
```

**Response** (201 Created):
```json
{
    "id": 1,
    "character_id": 1,
    "facility": "Speed",
    "prediction_accuracy": {
        "speed_gain_predicted": 45,
        "speed_gain_actual": 48,
        "accuracy_percentage": 106.7
    },
    "stat_gains": {
        "speed": 48,
        "stamina": 6,
        "power": 2,
        "guts": 1,
        "wit": 0,
        "total": 57
    },
    "energy_after": 58,
    "mood_after": "Normal",
    "skills_acquired": [
        {
            "skill_id": 5,
            "skill_name": "Going Strong",
            "base_cost": 120,
            "final_cost": 96,
            "sp_saved": 24
        }
    ],
    "conditions_applied": [
        {
            "condition": "Practice Poor",
            "expires_in_turns": 2
        }
    ],
    "character_state_updated": true,
    "next_race_in": 14,
    "created_at": "2026-01-14T10:30:00Z"
}
```

### 5.3 Prediction History Endpoint

#### 5.3.1 Get Prediction Accuracy

```http
GET /api/v1/characters/{id}/prediction-history
?limit=20&accuracy=true
Authorization: Bearer {token}
```

**Response** (200 OK):
```json
{
    "data": [
        {
            "training_session_id": 150,
            "facility": "Speed",
            "predicted_gain": 45,
            "actual_gain": 48,
            "accuracy": 106.7,
            "date": "2026-01-13T10:30:00Z"
        },
        {
            "training_session_id": 149,
            "facility": "Stamina",
            "predicted_gain": 42,
            "actual_gain": 40,
            "accuracy": 95.2,
            "date": "2026-01-12T10:30:00Z"
        }
    ],
    "statistics": {
        "total_sessions": 150,
        "average_accuracy": 97.3,
        "accuracy_trend": "improving",
        "most_accurate_facility": "Speed",
        "least_accurate_facility": "Guts"
    }
}
```

---

## 6. Database Schema

### 6.1 Training Sessions Table

```sql
CREATE TABLE training_sessions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    character_id BIGINT UNSIGNED NOT NULL,
    facility_id INT NOT NULL,
    facility_type VARCHAR(50) NOT NULL,
    turn_number INT NOT NULL,
    predicted_stat_gains JSON,
    actual_stat_gains JSON,
    support_card_ids JSON,
    skills_acquired JSON,
    conditions_applied JSON,
    mood_before VARCHAR(50),
    mood_after VARCHAR(50),
    energy_before INT,
    energy_after INT,
    created_at TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
    INDEX idx_character_id (character_id),
    INDEX idx_facility_type (facility_type),
    INDEX idx_turn_number (turn_number)
) ENGINE=InnoDB;
```

### 6.2 Support Cards Table

```sql
CREATE TABLE support_cards (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    character_id BIGINT UNSIGNED NOT NULL,
    card_id INT NOT NULL,
    card_name VARCHAR(255),
    rarity VARCHAR(10),
    limit_breaks INT DEFAULT 0,
    specialization VARCHAR(50),
    bond_level INT DEFAULT 0,
    training_bonus_speed INT,
    training_bonus_stamina INT,
    training_bonus_power INT,
    training_bonus_guts INT,
    training_bonus_wit INT,
    skills_provided JSON,
    created_at TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
    UNIQUE KEY unique_deck_card (character_id, card_id),
    INDEX idx_bond_level (bond_level)
) ENGINE=InnoDB;
```

### 6.3 Skill Hints Table

```sql
CREATE TABLE skill_hints (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    character_id BIGINT UNSIGNED NOT NULL,
    skill_id INT NOT NULL,
    support_card_id BIGINT UNSIGNED,
    hint_count INT DEFAULT 1,
    total_sp_saved INT DEFAULT 0,
    acquired_at TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
    FOREIGN KEY (support_card_id) REFERENCES support_cards(id) ON DELETE SET NULL,
    UNIQUE KEY unique_skill_hint (character_id, skill_id),
    INDEX idx_support_card_id (support_card_id)
) ENGINE=InnoDB;
```

### 6.4 Training Predictions Table

```sql
CREATE TABLE training_predictions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    character_id BIGINT UNSIGNED NOT NULL,
    facility_type VARCHAR(50),
    rank_score DECIMAL(5, 2),
    predicted_stat_gains JSON,
    support_cards_considered JSON,
    skill_hints_expected JSON,
    goal_alignment_score DECIMAL(5, 2),
    efficiency_rating VARCHAR(50),
    created_at TIMESTAMP,
    expires_at TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
    INDEX idx_character_id (character_id),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB;
```

---

## 7. AI/ML Integration

### 7.1 Prediction Accuracy Learning

The system learns from historical training data to improve future predictions:

```php
class PredictionAccuracyLearningService
{
    public function updateModelFromTrainingSession(TrainingSession $session): void
    {
        // Collect prediction vs actual data
        $accuracy = $this->calculateAccuracy(
            $session->predicted_stat_gains,
            $session->actual_stat_gains
        );
        
        // Store for model retraining
        $this->db->table('prediction_training_data')->insert([
            'character_id' => $session->character_id,
            'facility_type' => $session->facility_type,
            'support_cards' => $session->support_card_ids,
            'mood' => $session->mood_before,
            'energy' => $session->energy_before,
            'prediction_accuracy' => $accuracy,
            'collected_at' => now(),
        ]);
        
        // Trigger model retraining if threshold reached
        if ($this->shouldRetrain()) {
            RetrainPredictionModel::dispatch();
        }
    }
    
    private function calculateAccuracy(array $predicted, array $actual): float
    {
        $totalPredicted = array_sum($predicted);
        $totalActual = array_sum($actual);
        
        if ($totalActual === 0) return 0;
        
        return ($totalActual / $totalPredicted) * 100;
    }
}
```

### 7.2 Recommendation Engine (AI/Ollama Integration)

```php
class TrainingRecommendationEngine
{
    public function __construct(
        private OllamaService $ollama,
        private TrainingPredictionService $predictions,
        private GoalService $goals
    ) {}
    
    public function generateRecommendation(Character $character): TrainingRecommendation
    {
        // Get raw prediction data
        $predictions = $this->predictions->getPredictions($character);
        $activeGoals = $this->goals->getActiveGoals($character);
        
        // Create prompt for Ollama/Claude
        $prompt = $this->buildRecommendationPrompt(
            $character,
            $predictions,
            $activeGoals
        );
        
        // Get AI recommendation
        $aiResponse = $this->ollama->generateCompletion(
            model: 'neural-network-model',
            prompt: $prompt,
            temperature: 0.7,
        );
        
        // Parse and structure response
        return $this->parseAIResponse($aiResponse, $predictions);
    }
    
    private function buildRecommendationPrompt(
        Character $character,
        array $predictions,
        array $goals
    ): string {
        
        return <<<PROMPT
You are an expert Umamusume Career Mode strategist. Recommend the optimal training facility for this character:

Character: {$character->name}
Scenario: {$character->scenario}
Current Stats: Speed {$character->stats['speed']}, Stamina {$character->stats['stamina']}, Power {$character->stats['power']}, Guts {$character->stats['guts']}, Wit {$character->stats['wit']}
Current Mood: {$character->current_mood}
Energy: {$character->current_energy}%
Days Until Race: {$character->days_until_race}

Active Goals:
{json_encode($goals, JSON_PRETTY_PRINT)}

Training Options with Predictions:
{json_encode($predictions, JSON_PRETTY_PRINT)}

Provide a single recommended facility and explain your reasoning considering:
1. Goal alignment and progress
2. Stat efficiency
3. Skill hint opportunities
4. Upcoming race preparation
5. Character condition management

Format your response as JSON.
PROMPT;
    }
}
```

---

## 8. Performance & Caching

### 8.1 Caching Strategy

| Data | TTL | Key Pattern |
| --- | --- | --- |
| Training Predictions | 5 min | `training:predictions:{character_id}` |
| Support Card Bonuses | 1 hour | `support_cards:bonuses:{card_id}` |
| Prediction Accuracy Model | 24 hour | `ml:prediction_model` |
| Skill Hint Probabilities | 2 hour | `skill_hints:prob:{character_id}` |
| Ranking Scores | 5 min | `training:ranking:{character_id}` |

### 8.2 Optimizations

- **Query Optimization**: Eager load support cards, skills, hints
- **Batch Processing**: Calculate predictions for all facilities at once
- **Async Updates**: Store training sessions asynchronously
- **Cache Invalidation**: Clear caches on stat/condition changes

---

## 9. Testing Requirements

### 9.1 Unit Tests

- [ ] Stat gain calculation with all modifiers
- [ ] Support card bonus calculations
- [ ] Friendship training bonus application
- [ ] Spirit Burst filling mechanics
- [ ] Skill hint probability calculations
- [ ] Training ranking algorithm
- [ ] Mood/energy change predictions

### 9.2 Integration Tests

- [ ] End-to-end training prediction workflow
- [ ] Support deck management and validation
- [ ] Skill hint tracking and SP cost reduction
- [ ] Training session creation and character update
- [ ] Prediction accuracy learning

### 9.3 API Tests

- [ ] GET /api/v1/characters/{id}/training-predictions
- [ ] GET /api/v1/characters/{id}/training-recommendation
- [ ] POST /api/v1/characters/{id}/training-sessions
- [ ] GET /api/v1/characters/{id}/prediction-history

### 9.4 Performance Tests

- [ ] Training prediction generation < 200ms
- [ ] Ranking calculation < 100ms
- [ ] Recommendation generation (with AI) < 2s
- [ ] Bulk session creation < 500ms

---

**Next Document**: [SPEC-003_Race_Strategy_Technical.md](SPEC-003_Race_Strategy_Technical.md)

