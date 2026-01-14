# SPEC-003: Race Strategy System - Technical Specification

**Document Version**: 1.0 | **Date**: January 14, 2026 | **Status**: Draft

**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirement 3: Race Preparation and Strategy)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Race Strategy Architecture)
- `.kiro/specs/umamusume-career-planner-main/tasks.md` (Task 3.x: Race System)

**Related Artifacts**:

- PRD: [PRD-003](../prds/PRD-003_Race_Strategy.md)
- Flow: [FLOW-003](../flows/FLOW-003_Race_Strategy_System.md)
- Wireframes: [WF-006](../wireframes/WF-006_Race_Calendar_View.md), [WF-007](../wireframes/WF-007_Race_Preparation_Screen.md)
- Sequences: [SEQ-004](../sequences/SEQ-004_Race_Registration_and_Outcome.md)
- User Flows: [UF-004](../user-flows/UF-004_Race_Day_Flow.md)

## Table of Contents

1. [Technical Overview](#1-technical-overview)
2. [Race Analysis Engine](#2-race-analysis-engine)
3. [Strategy Recommendation System](#3-strategy-recommendation-system)
4. [API Specification](#4-api-specification)
5. [Database Schema](#5-database-schema)
6. [Testing Requirements](#6-testing-requirements)

---

## 1. Technical Overview

### 1.1 Core Components

```
Race Strategy Module (SPEC-003)
├── Race Analysis Engine
│   ├── Stat Requirement Analyzer
│   ├── Competition Level Evaluator
│   └── Weather Impact Calculator
├── Strategy Recommendation Service
│   ├── Running Style Optimizer
│   ├── Skill Selection Advisor
│   └── Pre-race Preparation Planner
├── Race Prediction Engine
│   ├── Performance Forecast
│   ├── Placement Probability
│   └── Outcome Scenarios
└── Repository Layer
    ├── RaceRepository
    └── RaceHistoryRepository
```

### 1.2 Core Entities

- **Race**: Track info, grade, distance, surface, weather
- **RaceRequirement**: Stat benchmarks and performance targets
- **RaceStrategy**: Recommended approach for specific race
- **RacePrediction**: AI-generated performance forecast
- **RaceHistory**: Completed race records

---

## 2. Race Analysis Engine

### 2.1 Stat Requirement Analyzer

```php
class RaceRequirementAnalyzer
{
    const REQUIREMENT_THRESHOLDS = [
        'Sprint' => ['min' => 350, 'recommended' => 500, 'optimal' => 700],
        'Mile' => ['min' => 400, 'recommended' => 600, 'optimal' => 800],
        'Medium' => ['min' => 500, 'recommended' => 800, 'optimal' => 1000],
        'Long' => ['min' => 600, 'recommended' => 900, 'optimal' => 1200],
    ];

    public function analyzeRequirements(
        Race $race,
        Character $character
    ): RaceRequirementAnalysis {
        
        $analysis = new RaceRequirementAnalysis();
        
        // Get base requirements for distance
        $baseReqs = self::REQUIREMENT_THRESHOLDS[$race->distance];
        
        // Adjust for competition level
        if ($race->competition_level === 'URAFinale') {
            $baseReqs = array_map(fn($v) => (int)($v * 1.2), $baseReqs);
        }
        
        // Check each stat
        foreach (['Speed', 'Stamina', 'Power', 'Guts', 'Wit'] as $stat) {
            $currentValue = $character->getStatValue($stat);
            $analysis->addStatEvaluation(
                stat: $stat,
                current: $currentValue,
                minimum: $baseReqs['min'],
                recommended: $baseReqs['recommended'],
                optimal: $baseReqs['optimal'],
                status: $this->evaluateStatus($currentValue, $baseReqs)
            );
        }
        
        return $analysis;
    }
    
    private function evaluateStatus(int $current, array $reqs): string
    {
        if ($current >= $reqs['optimal']) return '○';      // Adequate
        if ($current >= $reqs['recommended']) return '⦾';  // Borderline
        if ($current >= $reqs['min']) return '△';          // Insufficient
        return '×';                                         // Inadequate
    }
}
```

### 2.2 Weather Impact Calculator

```php
class WeatherImpactCalculator
{
    const WEATHER_MODIFIERS = [
        'Turf' => [
            'Sunny' => 1.0,
            'Cloudy' => 1.0,
            'Rainy' => 0.95,
            'Snowy' => 0.85,
        ],
        'Dirt' => [
            'Sunny' => 1.0,
            'Cloudy' => 1.0,
            'Rainy' => 0.90,
            'Snowy' => 0.80,
        ],
    ];
    
    const TRACK_CONDITION_MODIFIERS = [
        'Firm' => 1.05,
        'Good' => 1.0,
        'Soft' => 0.95,
        'Heavy' => 0.85,
    ];

    public function calculateWeatherImpact(
        Race $race,
        Character $character
    ): WeatherImpact {
        
        $surfaceModifier = self::WEATHER_MODIFIERS[
            $race->surface
        ][$race->weather] ?? 1.0;
        
        $trackModifier = self::TRACK_CONDITION_MODIFIERS[
            $race->track_condition
        ] ?? 1.0;
        
        $combinedModifier = $surfaceModifier * $trackModifier;
        
        // Check if character has weather-specific skills
        $weatherSkills = $character->getSkillsForWeather($race->weather);
        $weatherAptitudes = $character->getAptitudesForSurface($race->surface);
        
        return new WeatherImpact(
            modifier: $combinedModifier,
            weather_skills: $weatherSkills,
            surface_aptitudes: $weatherAptitudes,
            recommendation: $this->recommendSkillsForWeather(
                $race,
                $character
            )
        );
    }
}
```

---

## 3. Strategy Recommendation System

### 3.1 Running Style Optimizer

```php
class RunningStyleOptimizer
{
    const STYLE_EFFECTIVENESS = [
        'FrontRunner' => [
            'traits' => ['Speed', 'Guts'],
            'ideal_stats' => ['Speed' => 0.5, 'Guts' => 0.3],
            'strengths' => 'Controls race pace, guaranteed first position',
            'weaknesses' => 'Vulnerable to late attacks',
        ],
        'PaceChaser' => [
            'traits' => ['Speed', 'Stamina'],
            'ideal_stats' => ['Speed' => 0.4, 'Stamina' => 0.4],
            'strengths' => 'Balanced tempo control',
            'weaknesses' => 'No major advantages',
        ],
        'LateSurger' => [
            'traits' => ['Power', 'Guts'],
            'ideal_stats' => ['Power' => 0.5, 'Guts' => 0.3],
            'strengths' => 'Strong final push',
            'weaknesses' => 'Must survive early pace',
        ],
        'EndCloser' => [
            'traits' => ['Wit', 'Guts'],
            'ideal_stats' => ['Wit' => 0.4, 'Guts' => 0.3],
            'strengths' => 'Exceptional positioning',
            'weaknesses' => 'Requires high Wit stats',
        ],
    ];

    public function optimizeRunningStyle(
        Race $race,
        Character $character
    ): RunningStyleRecommendation {
        
        $scores = [];
        
        foreach (self::STYLE_EFFECTIVENESS as $style => $config) {
            $aptitude = $character->getAptitudeForStyle($style);
            
            // Score based on aptitude match
            $aptitudeScore = $this->aptitudeToScore($aptitude);
            
            // Score based on stat alignment
            $statScore = 0;
            foreach ($config['ideal_stats'] as $stat => $weight) {
                $currentValue = $character->getStatValue($stat);
                $statScore += ($currentValue / 1200) * $weight * 100;
            }
            
            // Score based on track characteristics
            $trackScore = $this->calculateTrackCompatibility($style, $race);
            
            $totalScore = ($aptitudeScore * 0.5) + ($statScore * 0.3) + ($trackScore * 0.2);
            
            $scores[$style] = $totalScore;
        }
        
        arsort($scores);
        
        return new RunningStyleRecommendation(
            primary: array_key_first($scores),
            alternatives: array_slice(array_keys($scores), 1, 2),
            scores: $scores,
            reasoning: $this->generateReasoning(array_key_first($scores), $character, $race)
        );
    }
    
    private function aptitudeToScore(string $aptitude): int
    {
        $scoreMap = [
            'SS' => 100, 'S+' => 95, 'S' => 90, 'A+' => 80, 'A' => 75,
            'B+' => 60, 'B' => 55, 'C+' => 40, 'C' => 35, 'D+' => 20, 'D' => 15,
        ];
        return $scoreMap[$aptitude] ?? 0;
    }
}
```

### 3.2 Skill Selection Advisor

```php
class SkillSelectionAdvisor
{
    public function recommendSkills(
        Race $race,
        Character $character,
        string $runningStyle
    ): SkillRecommendation {
        
        $recommendations = [];
        
        // Category 1: Weather-specific skills
        $weatherSkills = $this->findWeatherSkills($race->weather);
        if ($weatherSkills) {
            $recommendations['weather'] = [
                'skills' => $weatherSkills,
                'priority' => 'high',
                'reason' => "Essential for {$race->weather} conditions"
            ];
        }
        
        // Category 2: Running style enhancers
        $styleSkills = $this->findStyleSkills($runningStyle);
        $recommendations['running_style'] = [
            'skills' => $styleSkills,
            'priority' => 'high',
            'reason' => "Enhance {$runningStyle} strategy"
        ];
        
        // Category 3: Track characteristics
        $trackSkills = $this->findTrackSkills($race);
        $recommendations['track'] = [
            'skills' => $trackSkills,
            'priority' => 'medium',
            'reason' => "Optimize for {$race->track} characteristics"
        ];
        
        // Category 4: Support card synergy
        $deckSkills = $this->findDeckSynergySkills($character);
        $recommendations['synergy'] = [
            'skills' => $deckSkills,
            'priority' => 'medium',
            'reason' => "Leverage support card bonuses"
        ];
        
        // Rank by priority and character compatibility
        return new SkillRecommendation(
            recommended: $this->rankSkills($recommendations, $character),
            total_skills: count(array_merge(...array_column($recommendations, 'skills'))),
            synergy_score: $this->calculateSynergyScore($character, $recommendations)
        );
    }
}
```

---

## 4. API Specification

### 4.1 Race Analysis Endpoints

#### 4.1.1 Get Upcoming Race Details

```http
GET /api/v1/characters/{id}/upcoming-race
Authorization: Bearer {token}
```

**Response**:

```json
{
    "race": {
        "id": 42,
        "name": "Kanto Okami Cup",
        "grade": "G1",
        "track": "Tokyo",
        "distance": 2400,
        "surface": "Turf",
        "distance_category": "Medium",
        "track_characteristics": {
            "layout": "Right 4 corners",
            "elevation": "Flat",
            "inner_outer": "Inner favorable"
        },
        "scheduled_weather": "Sunny",
        "expected_track_condition": "Good",
        "turn_number": 85,
        "days_until_race": 8
    },
    "stat_requirements": {
        "speed": {
            "current": 520,
            "minimum": 500,
            "recommended": 700,
            "optimal": 900,
            "status": "⦾"
        },
        "stamina": {
            "current": 480,
            "minimum": 600,
            "recommended": 800,
            "optimal": 1000,
            "status": "×"
        },
        "power": {...},
        "guts": {...},
        "wit": {...}
    },
    "requirement_summary": {
        "adequate_stats": 2,
        "borderline_stats": 1,
        "insufficient_stats": 2,
        "overall_readiness": "Borderline"
    },
    "weather_impact": {
        "modifier": 1.0,
        "track_condition": "Good",
        "relevant_skills": [
            "Turf Runner",
            "Cool Breeze"
        ],
        "recommendation": "No weather-specific penalties expected"
    }
}
```

#### 4.1.2 Get Race Strategy Recommendation

```http
GET /api/v1/characters/{id}/race-strategy
Authorization: Bearer {token}
```

**Response**:

```json
{
    "race_id": 42,
    "recommended_strategy": {
        "running_style": "LateSurger",
        "reasoning": "High Power aptitude (A) and Guts provide strong finishing capability. Medium distance favors late acceleration.",
        "confidence": 0.88,
        "score": 85.3
    },
    "alternative_strategies": [
        {
            "running_style": "EndCloser",
            "score": 76.5
        },
        {
            "running_style": "FrontRunner",
            "score": 62.1
        }
    ],
    "skill_recommendations": {
        "essential": [
            {
                "skill_name": "Predator's Instinct",
                "category": "Running Style",
                "reason": "LateSurger enhancement",
                "owned": true,
                "current_sp": 450
            }
        ],
        "recommended": [
            {
                "skill_name": "Turf Runner",
                "category": "Surface Specific",
                "reason": "Turf-track optimization",
                "owned": false,
                "required_sp": 120
            }
        ],
        "optional": [
            {
                "skill_name": "Cool Breeze",
                "category": "Weather Specific",
                "reason": "Potential Sunny weather bonus",
                "owned": true
            }
        ]
    },
    "pre_race_preparation": {
        "stat_gaps": {
            "stamina": {
                "gap": 220,
                "priority": "high",
                "achievable": false,
                "recommendation": "Prepare for mid-race struggles; ensure LateSurger positioning"
            }
        },
        "recommended_training_focus": [
            "Stamina (to mitigate gap)",
            "Power (to enhance finishing)"
        ],
        "estimated_training_sessions": 8,
        "preparation_completion": "80%"
    },
    "performance_forecast": {
        "placement_probability": {
            "1st": 0.35,
            "2nd": 0.40,
            "3rd": 0.20,
            "4th+": 0.05
        },
        "expected_placement": "2nd",
        "win_condition": "Strong Stamina build; avoid early pace attacks",
        "commentary_prediction": "Strong final push! Well done!"
    }
}
```

#### 4.1.3 Complete Race

```http
POST /api/v1/characters/{id}/races/{race_id}/complete
Content-Type: application/json
Authorization: Bearer {token}

{
    "placement": 1,
    "commentary": "Strong final push! Well done!",
    "fan_count_gained": 1850,
    "achieved_goal": true,
    "notes": "Performance exceeded expectations"
}
```

**Response**:

```json
{
    "race_result": {
        "id": 42,
        "character_id": 1,
        "placement": 1,
        "prize_money": 5000000,
        "fans_gained": 1850,
        "grade": "G1",
        "completed_at": "2026-01-14T10:30:00Z"
    },
    "prediction_accuracy": {
        "predicted_placement": "2nd",
        "actual_placement": "1st",
        "accuracy_score": 0.88
    },
    "career_impact": {
        "fan_total": 45000,
        "fan_goal": 50000,
        "goal_progress": 90,
        "next_race_available": true,
        "grade_eligible": "URAFinale"
    },
    "post_race_analysis": {
        "strategy_effectiveness": "Excellent",
        "running_style_worked": true,
        "skill_synergy": "Optimal",
        "lessons_learned": [
            "LateSurger strategy highly effective for this character",
            "Stamina gap was not critical; focus on Power next"
        ]
    }
}
```

---

## 5. Database Schema

### 5.1 Races Table

```sql
CREATE TABLE races (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    character_id BIGINT UNSIGNED NOT NULL,
    race_name VARCHAR(255) NOT NULL,
    grade VARCHAR(10),
    track VARCHAR(50),
    distance INT,
    distance_category VARCHAR(50),
    surface VARCHAR(20),
    track_layout VARCHAR(100),
    weather_scheduled VARCHAR(50),
    track_condition_expected VARCHAR(50),
    turn_number INT,
    scheduled_date DATE,
    status ENUM('Upcoming', 'Completed', 'Cancelled') DEFAULT 'Upcoming',
    placement INT,
    prize_money INT,
    fans_gained INT,
    completed_at TIMESTAMP,
    created_at TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
    INDEX idx_character_id (character_id),
    INDEX idx_status (status),
    INDEX idx_grade (grade)
) ENGINE=InnoDB;
```

### 5.2 Race Strategies Table

```sql
CREATE TABLE race_strategies (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    race_id BIGINT UNSIGNED NOT NULL,
    running_style VARCHAR(50),
    recommended_skills JSON,
    skill_modifications JSON,
    confidence_score DECIMAL(5, 2),
    reasoning TEXT,
    created_at TIMESTAMP,
    FOREIGN KEY (race_id) REFERENCES races(id) ON DELETE CASCADE,
    INDEX idx_race_id (race_id)
) ENGINE=InnoDB;
```

---

## 6. Testing Requirements

- [ ] Stat requirement analysis for all distance types
- [ ] Weather impact calculation with track conditions
- [ ] Running style optimization algorithm
- [ ] Skill recommendation generation
- [ ] Race prediction accuracy
- [ ] Complete race workflow
- [ ] Post-race analysis and lessons
- [ ] Career progression tracking after races

---

**Related Documents**: [PRD-003], [SPEC-001], [SPEC-002]
