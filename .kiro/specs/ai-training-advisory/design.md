# Design Document: AI-Powered Training Advisory System

## Overview

The AI-Powered Training Advisory System is a comprehensive recommendation engine that provides intelligent, context-aware guidance throughout Umamusume Pretty Derby career runs. The system analyzes character state, training scenarios, support card configurations, and game mechanics to deliver actionable recommendations for training choices, skill purchases, race strategies, and critical decision points.

### Key Design Principles

1. **Context-Aware Intelligence**: Analyze complete character state including stats, SP, skills, energy, mood, bonds, facility levels, and phase
2. **Real-Time Responsiveness**: Provide recommendations within 2-5 seconds using hybrid AI infrastructure
3. **Actionable Guidance**: Deliver specific, prioritized recommendations with clear reasoning
4. **Progressive Disclosure**: Show high-priority alerts prominently while keeping detailed analysis accessible
5. **Offline Capability**: Provide rule-based recommendations when AI services are unavailable
6. **Learning System**: Track prediction accuracy and improve recommendations over time

### System Scope

**In Scope**:

- Turn-by-turn training facility recommendations
- Skill purchase prioritization and SP budget management
- Race strategy generation and readiness assessment
- Critical situation detection and alerts
- Support card deck analysis and bond management
- Phase-specific goal tracking and milestone reminders
- Prediction accuracy tracking for continuous improvement

**Out of Scope**:

- Automated gameplay or decision execution
- Real-time race control or intervention
- Support card gacha recommendations
- Character breeding/inheritance planning (separate feature)
- Multiplayer or competitive analysis

---

## Architecture

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Presentation Layer                        │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │ Advisory     │  │ Critical     │  │ Skill Shop   │      │
│  │ Panel        │  │ Alerts       │  │ Advisor      │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
└─────────────────────────────────────────────────────────────┘
                            │
┌─────────────────────────────────────────────────────────────┐
│                   Application Layer                          │
│  ┌──────────────────────────────────────────────────────┐   │
│  │         TrainingAdvisoryService                      │   │
│  │  ┌────────────┐  ┌────────────┐  ┌────────────┐    │   │
│  │  │ Training   │  │ Skill      │  │ Race       │    │   │
│  │  │ Recommender│  │ Advisor    │  │ Strategist │    │   │
│  │  └────────────┘  └────────────┘  └────────────┘    │   │
│  └──────────────────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────────────────┐   │
│  │         CriticalSituationDetector                    │   │
│  └──────────────────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────────────────┐   │
│  │         PredictionAccuracyTracker                    │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                            │
┌─────────────────────────────────────────────────────────────┐
│                    Domain Layer                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │ Training     │  │ Skill        │  │ Race         │      │
│  │ Context      │  │ Catalog      │  │ Requirements │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │ Support Card │  │ Recommendation│  │ Critical     │      │
│  │ Deck         │  │              │  │ Alert        │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
└─────────────────────────────────────────────────────────────┘
                            │
┌─────────────────────────────────────────────────────────────┐
│                 Infrastructure Layer                         │
│  ┌──────────────────────────────────────────────────────┐   │
│  │         NeuronAIService (Existing)                   │   │
│  │  ┌────────────┐              ┌────────────┐         │   │
│  │  │ Ollama     │  Fallback    │ AWS        │         │   │
│  │  │ (Local)    │  ────────>   │ Bedrock    │         │   │
│  │  └────────────┘              └────────────┘         │   │
│  └──────────────────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────────────────┐   │
│  │         RuleBasedAdvisor (Offline Fallback)          │   │
│  └──────────────────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────────────────┐   │
│  │         GameMechanicsEngine                          │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

### Component Responsibilities

**TrainingAdvisoryService**: Orchestrates all advisory functions, coordinates between recommenders, manages AI agent lifecycle

**TrainingRecommender**: Analyzes training context and generates facility recommendations with expected value calculations

**SkillAdvisor**: Evaluates available skills, calculates SP efficiency, prioritizes purchases based on character build

**RaceStrategist**: Generates race strategies, calculates win probabilities, assesses character readiness

**CriticalSituationDetector**: Monitors character state for critical conditions, generates high-priority alerts

**PredictionAccuracyTracker**: Records predicted vs actual outcomes, calculates accuracy metrics, flags models for improvement

**GameMechanicsEngine**: Encapsulates game formulas and calculations (training gains, stamina requirements, stat effectiveness)

**NeuronAIService**: Existing AI infrastructure providing Ollama + AWS Bedrock integration

**RuleBasedAdvisor**: Offline fallback providing deterministic recommendations based on game mechanics

---

## Components and Interfaces

### TrainingAdvisoryService

**Purpose**: Main service coordinating all advisory functions

**Public Interface**:

```php
class TrainingAdvisoryService
{
    public function getTrainingRecommendations(
        TrainingContext $context
    ): RecommendationCollection;
    
    public function getSkillPurchaseAdvice(
        Character $character,
        array $availableSkills
    ): SkillRecommendationCollection;
    
    public function getRaceStrategy(
        Character $character,
        Race $race
    ): RaceStrategy;
    
    public function detectCriticalSituations(
        TrainingContext $context
    ): CriticalAlertCollection;
    
    public function recordTrainingOutcome(
        int $turnNumber,
        Recommendation $recommendation,
        TrainingOutcome $actual
    ): void;
    
    public function recordRaceOutcome(
        RaceStrategy $strategy,
        RaceResult $actual
    ): void;
}
```

**Dependencies**:

- NeuronAIService (for AI-powered recommendations)
- RuleBasedAdvisor (for offline fallback)
- GameMechanicsEngine (for calculations)
- PredictionAccuracyTracker (for learning)

### TrainingContext

**Purpose**: Value object encapsulating complete character state for analysis

**Structure**:

```php
class TrainingContext
{
    public function __construct(
        public readonly int $turnNumber,
        public readonly CareerPhase $phase,
        public readonly CharacterStats $stats,
        public readonly int $spAvailable,
        public readonly int $energy,
        public readonly Mood $mood,
        public readonly array $acquiredSkills,
        public readonly array $skillHints,
        public readonly SupportCardDeck $deck,
        public readonly array $facilityLevels,
        public readonly array $upcomingRaces,
        public readonly ?Scenario $scenario,
    ) {}
    
    public function toPromptContext(): string;
    public function isEnergyLow(): bool;
    public function isMoodPoor(): bool;
    public function hasFriendshipTrainingAvailable(): bool;
}
```

### Recommendation

**Purpose**: Represents a single actionable recommendation

**Structure**:

```php
class Recommendation
{
    public function __construct(
        public readonly RecommendationType $type,
        public readonly Priority $priority,
        public readonly string $action,
        public readonly string $reasoning,
        public readonly array $expectedOutcomes,
        public readonly array $risks,
        public readonly ?float $confidenceScore,
    ) {}
}

enum RecommendationType: string
{
    case TRAINING_FACILITY = 'training_facility';
    case SKILL_PURCHASE = 'skill_purchase';
    case RACE_STRATEGY = 'race_strategy';
    case REST_RECOVERY = 'rest_recovery';
    case BOND_BUILDING = 'bond_building';
}

enum Priority: string
{
    case CRITICAL = 'critical';
    case HIGH = 'high';
    case MEDIUM = 'medium';
    case LOW = 'low';
}
```

### CriticalAlert

**Purpose**: High-priority warning requiring immediate attention

**Structure**:

```php
class CriticalAlert
{
    public function __construct(
        public readonly AlertType $type,
        public readonly string $message,
        public readonly array $actionItems,
        public readonly int $turnsUntilCritical,
        public readonly ?string $detailedAnalysis,
    ) {}
}

enum AlertType: string
{
    case STAMINA_CRISIS = 'stamina_crisis';
    case SP_SHORTAGE = 'sp_shortage';
    case ENERGY_CRITICAL = 'energy_critical';
    case BOND_BEHIND_SCHEDULE = 'bond_behind_schedule';
    case FACILITY_IMBALANCE = 'facility_imbalance';
    case RACE_UNREADY = 'race_unready';
    case TEAM_RACE_UNPREPARED = 'team_race_unprepared';
}
```

### GameMechanicsEngine

**Purpose**: Encapsulates all game formula calculations

**Public Interface**:

```php
class GameMechanicsEngine
{
    public function calculateTrainingGain(
        int $baseStat,
        int $facilityLevel,
        float $growthRate,
        Mood $mood,
        array $supportCardBonuses,
        int $numCardsPresent,
        bool $isFriendshipTraining
    ): int;
    
    public function calculateStaminaRequirement(
        RaceDistance $distance,
        RunningStyle $style,
        array $recoverySkills
    ): int;
    
    public function calculateSkillCost(
        int $baseCost,
        int $hintLevel,
        bool $hasFastLearner
    ): int;
    
    public function calculateFailureRate(
        int $energy,
        int $numSupportCards,
        array $conditions
    ): float;
    
    public function calculateStatEffectiveness(
        int $statValue
    ): int; // Handles 1200 soft cap
    
    public function calculateWinProbability(
        CharacterStats $stats,
        Race $race,
        array $skills
    ): float;
}
```

### RuleBasedAdvisor

**Purpose**: Provides deterministic recommendations when AI is unavailable

**Public Interface**:

```php
class RuleBasedAdvisor
{
    public function recommendTrainingFacility(
        TrainingContext $context
    ): Recommendation;
    
    public function recommendSkillPurchase(
        Character $character,
        array $availableSkills
    ): ?Recommendation;
    
    public function generateRaceStrategy(
        Character $character,
        Race $race
    ): RaceStrategy;
}
```

**Rule-Based Logic**:

- Training: Prioritize facilities with most support cards, consider energy/mood
- Skills: Buy gold skills with Level 3+ hints, avoid SP traps
- Races: Match running style to aptitude, check stamina requirements

---

## Data Models

### Database Schema

**advisory_recommendations** table:

```sql
CREATE TABLE advisory_recommendations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    career_run_id BIGINT UNSIGNED NOT NULL,
    turn_number INT NOT NULL,
    recommendation_type VARCHAR(50) NOT NULL,
    priority VARCHAR(20) NOT NULL,
    action TEXT NOT NULL,
    reasoning TEXT NOT NULL,
    expected_outcomes JSON,
    confidence_score DECIMAL(5,4) NULL,
    was_followed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (career_run_id) REFERENCES career_runs(id) ON DELETE CASCADE,
    INDEX idx_career_turn (career_run_id, turn_number),
    INDEX idx_type (recommendation_type)
);
```

**critical_alerts** table:

```sql
CREATE TABLE critical_alerts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    career_run_id BIGINT UNSIGNED NOT NULL,
    turn_number INT NOT NULL,
    alert_type VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    action_items JSON NOT NULL,
    turns_until_critical INT NULL,
    was_dismissed BOOLEAN DEFAULT FALSE,
    dismissed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (career_run_id) REFERENCES career_runs(id) ON DELETE CASCADE,
    INDEX idx_career_turn (career_run_id, turn_number),
    INDEX idx_active (career_run_id, was_dismissed)
);
```

**prediction_accuracy** table:

```sql
CREATE TABLE prediction_accuracy (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    career_run_id BIGINT UNSIGNED NOT NULL,
    turn_number INT NOT NULL,
    prediction_type VARCHAR(50) NOT NULL,
    predicted_value JSON NOT NULL,
    actual_value JSON NOT NULL,
    accuracy_score DECIMAL(5,4) NOT NULL,
    model_version VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (career_run_id) REFERENCES career_runs(id) ON DELETE CASCADE,
    INDEX idx_career (career_run_id),
    INDEX idx_type_model (prediction_type, model_version),
    INDEX idx_created (created_at)
);
```

### Local Storage Schema

For local mode, recommendations and alerts are stored in browser localStorage:

```typescript
interface LocalAdvisoryData {
    recommendations: {
        [turnNumber: number]: Recommendation[];
    };
    criticalAlerts: {
        [turnNumber: number]: CriticalAlert[];
    };
    predictionAccuracy: PredictionRecord[];
    lastUpdated: string; // ISO 8601
}
```

### Caching Strategy

**Cache Keys**:

- `advisory:skill_catalog:{version}` - Skill database (TTL: 24 hours)
- `advisory:race_requirements:{race_id}` - Race requirements (TTL: 1 hour)
- `advisory:support_card_meta:{version}` - Card tier rankings (TTL: 24 hours)

**Cache Invalidation**:

- Character state changes → Clear turn-specific recommendations
- Skill purchases → Clear skill advice cache
- Race completion → Clear race strategy cache

---

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Storage Mode Consistency

**Validates: Requirements 3.1, 3.7**

For any recommendation generated, the storage mode (Local/Account) must be correctly identified and propagated throughout the recommendation lifecycle.

```php
property('recommendations preserve storage mode', function () {
    $context = generateTrainingContext();
    $storageMode = $context->storageMode;
    
    $recommendations = $advisoryService->getTrainingRecommendations($context);
    
    foreach ($recommendations as $rec) {
        expect($rec->storageMode)->toBe($storageMode);
    }
});
```

### Property 2: Response Time Bounds

**Validates: Requirements 3.1, 4.1**

All recommendations must be generated within specified time bounds based on AI provider.

```php
property('recommendations meet response time requirements', function () {
    $context = generateTrainingContext();
    $provider = $context->aiProvider; // 'local' or 'cloud'
    
    $start = microtime(true);
    $recommendations = $advisoryService->getTrainingRecommendations($context);
    $duration = microtime(true) - $start;
    
    $maxTime = $provider === 'local' ? 2.0 : 5.0;
    expect($duration)->toBeLessThanOrEqual($maxTime);
});
```

### Property 3: Friendship Training Priority

**Validates: Requirements 3.1**

When Friendship Training is available (bond ≥80), it must be prioritized in recommendations.

```php
property('friendship training is prioritized when available', function () {
    $context = generateTrainingContextWithFriendshipAvailable();
    
    $recommendations = $advisoryService->getTrainingRecommendations($context);
    $topRecommendation = $recommendations->first();
    
    expect($topRecommendation->isFriendshipTraining())->toBeTrue();
    expect($topRecommendation->priority)->toBe(Priority::HIGH);
});
```

### Property 4: Energy-Based Rest Recommendations

**Validates: Requirements 3.1**

When energy falls below 50, the system must recommend rest or Wisdom training.

```php
property('low energy triggers rest recommendations', function () {
    $context = generateTrainingContextWithLowEnergy(); // energy < 50
    
    $recommendations = $advisoryService->getTrainingRecommendations($context);
    
    $hasRestOrWisdom = $recommendations->contains(function ($rec) {
        return $rec->type === RecommendationType::REST_RECOVERY 
            || ($rec->type === RecommendationType::TRAINING_FACILITY 
                && $rec->action === 'Wisdom');
    });
    
    expect($hasRestOrWisdom)->toBeTrue();
});
```

### Property 5: Skill Hint Level Discount Accuracy

**Validates: Requirements 3.2**

Skill cost calculations must correctly apply hint level discounts (10%/20%/30%/35%/40%).

```php
property('skill costs apply correct hint discounts', function () {
    $baseCost = 100;
    $hintLevels = [1 => 0.10, 2 => 0.20, 3 => 0.30, 4 => 0.35, 5 => 0.40];
    
    foreach ($hintLevels as $level => $discount) {
        $expectedCost = (int)($baseCost * (1 - $discount));
        $actualCost = $mechanicsEngine->calculateSkillCost($baseCost, $level, false);
        
        expect($actualCost)->toBe($expectedCost);
    }
});
```

### Property 6: Gold Skill Prioritization

**Validates: Requirements 3.2**

Gold skills with Level 3+ hints must be prioritized in skill recommendations.

```php
property('gold skills with high hints are prioritized', function () {
    $character = generateCharacterWithSPBudget();
    $skills = [
        generateGoldSkill(hintLevel: 3),
        generateNormalSkill(hintLevel: 5),
        generateGoldSkill(hintLevel: 1),
    ];
    
    $recommendations = $advisoryService->getSkillPurchaseAdvice($character, $skills);
    $topRecommendation = $recommendations->first();
    
    expect($topRecommendation->skill->isGold())->toBeTrue();
    expect($topRecommendation->skill->hintLevel)->toBeGreaterThanOrEqual(3);
});
```

### Property 7: Stamina Requirement Calculation

**Validates: Requirements 3.3**

Stamina requirements must be calculated correctly based on distance and running style.

```php
property('stamina requirements match distance thresholds', function () {
    $distances = [
        RaceDistance::SPRINT => [350, 400],
        RaceDistance::MILE => [450, 500],
        RaceDistance::MEDIUM => [600, 700],
        RaceDistance::LONG => [850, 1000],
    ];
    
    foreach ($distances as $distance => [$min, $max]) {
        $requirement = $mechanicsEngine->calculateStaminaRequirement(
            $distance,
            RunningStyle::ESCAPE,
            []
        );
        
        expect($requirement)->toBeGreaterThanOrEqual($min);
        expect($requirement)->toBeLessThanOrEqual($max);
    }
});
```

### Property 8: Stamina Recovery Skill Adjustment

**Validates: Requirements 3.3**

Stamina requirements must be reduced by 150-200 per gold recovery skill equipped.

```php
property('recovery skills reduce stamina requirements', function () {
    $baseRequirement = $mechanicsEngine->calculateStaminaRequirement(
        RaceDistance::LONG,
        RunningStyle::ESCAPE,
        []
    );
    
    $withRecovery = $mechanicsEngine->calculateStaminaRequirement(
        RaceDistance::LONG,
        RunningStyle::ESCAPE,
        [generateGoldRecoverySkill()]
    );
    
    $reduction = $baseRequirement - $withRecovery;
    expect($reduction)->toBeGreaterThanOrEqual(150);
    expect($reduction)->toBeLessThanOrEqual(200);
});
```

### Property 9: Critical Alert Generation

**Validates: Requirements 3.4**

Critical alerts must be generated when character state meets alert conditions.

```php
property('critical alerts are generated for dangerous states', function () {
    $criticalConditions = [
        'lowStamina' => fn() => generateContextWithLowStamina(),
        'lowEnergy' => fn() => generateContextWithEnergy(30),
        'lowBonds' => fn() => generateContextWithLowBonds(turn: 25),
    ];
    
    foreach ($criticalConditions as $condition => $generator) {
        $context = $generator();
        $alerts = $advisoryService->detectCriticalSituations($context);
        
        expect($alerts)->not->toBeEmpty();
        expect($alerts->first()->priority)->toBe(Priority::CRITICAL);
    }
});
```

### Property 10: Multi-Training Bonus Calculation

**Validates: Requirements 3.5**

Multi-training bonus must be calculated as +5% per support card (max +30%).

```php
property('multi training bonus scales correctly', function () {
    $numCards = [0, 1, 2, 3, 4, 5, 6, 7];
    
    foreach ($numCards as $count) {
        $bonus = $mechanicsEngine->calculateMultiTrainingBonus($count);
        $expected = min($count * 0.05, 0.30);
        
        expect($bonus)->toBe($expected);
    }
});
```

### Property 11: Facility Level Progression

**Validates: Requirements 3.1**

Facility levels must progress correctly (every 4 uses = +1 level, max Level 5).

```php
property('facility levels progress correctly', function () {
    $uses = [0, 1, 3, 4, 7, 8, 11, 12, 15, 16, 20];
    
    foreach ($uses as $useCount) {
        $level = $mechanicsEngine->calculateFacilityLevel($useCount);
        $expectedLevel = min(floor($useCount / 4) + 1, 5);
        
        expect($level)->toBe($expectedLevel);
    }
});
```

### Property 12: Bond Progress Tracking

**Validates: Requirements 3.5**

Bond progress must track correctly toward 80 threshold for Friendship Training.

```php
property('bond progress tracks toward friendship threshold', function () {
    $context = generateTrainingContextWithBonds([60, 70, 75, 79, 80, 85]);
    
    $analysis = $advisoryService->analyzeSupportCardDeck($context);
    
    $readyCards = $analysis->cardsReadyForFriendship;
    $notReadyCards = $analysis->cardsNotReadyForFriendship;
    
    expect($readyCards->every(fn($card) => $card->bond >= 80))->toBeTrue();
    expect($notReadyCards->every(fn($card) => $card->bond < 80))->toBeTrue();
});
```

### Property 13: Phase-Specific Goal Tracking

**Validates: Requirements 3.6**

Phase-specific milestones must be tracked and reported correctly.

```php
property('phase milestones are tracked correctly', function () {
    $phases = [
        CareerPhase::JUNIOR => ['bondBuilding', 'facilityLevels'],
        CareerPhase::CLASSIC => ['statOptimization', 'skillAcquisition'],
        CareerPhase::SENIOR => ['finalPreparation', 'uraReadiness'],
    ];
    
    foreach ($phases as $phase => $expectedMilestones) {
        $context = generateTrainingContextForPhase($phase);
        $tracking = $advisoryService->getPhaseGoalTracking($context);
        
        foreach ($expectedMilestones as $milestone) {
            expect($tracking->hasMilestone($milestone))->toBeTrue();
        }
    }
});
```

### Property 14: Offline Fallback Behavior

**Validates: Requirements 3.9, 4.2**

When AI services are unavailable, the system must fall back to rule-based recommendations.

```php
property('system falls back to rules when AI unavailable', function () {
    $context = generateTrainingContext();
    
    // Simulate AI service failure
    $this->mock(NeuronAIService::class)
        ->shouldReceive('generateRecommendation')
        ->andThrow(new AIServiceException());
    
    $recommendations = $advisoryService->getTrainingRecommendations($context);
    
    expect($recommendations)->not->toBeEmpty();
    expect($recommendations->first()->source)->toBe('rule-based');
});
```

### Property 15: Prediction Accuracy Recording

**Validates: Requirements 3.8**

Predicted outcomes must be recorded and compared with actual outcomes.

```php
property('predictions are recorded for accuracy tracking', function () {
    $context = generateTrainingContext();
    $recommendation = $advisoryService->getTrainingRecommendations($context)->first();
    
    $actualOutcome = generateTrainingOutcome();
    
    $advisoryService->recordTrainingOutcome(
        $context->turnNumber,
        $recommendation,
        $actualOutcome
    );
    
    $accuracy = $advisoryService->getPredictionAccuracy(
        $context->careerRunId,
        RecommendationType::TRAINING_FACILITY
    );
    
    expect($accuracy)->toBeInstanceOf(AccuracyMetrics::class);
    expect($accuracy->totalPredictions)->toBeGreaterThan(0);
});
```

---

## API Endpoints

### Training Recommendations

**POST /api/advisory/training/recommendations**

Request:

```json
{
  "career_run_id": "uuid-or-id",
  "storage_mode": "local",
  "turn_number": 15,
  "phase": "classic_year",
  "stats": {
    "speed": 450,
    "stamina": 380,
    "power": 420,
    "guts": 350,
    "wisdom": 400
  },
  "sp_available": 180,
  "energy": 75,
  "mood": "good",
  "acquired_skills": [1, 5, 12],
  "skill_hints": [
    {"skill_id": 23, "level": 3},
    {"skill_id": 45, "level": 2}
  ],
  "support_deck": {
    "cards": [
      {"id": 1, "bond": 85, "facility": "speed"},
      {"id": 2, "bond": 72, "facility": "stamina"}
    ]
  },
  "facility_levels": {
    "speed": 3,
    "stamina": 2,
    "power": 3,
    "guts": 2,
    "wisdom": 4
  },
  "upcoming_races": [
    {"id": 15, "distance": "medium", "turn": 18}
  ]
}
```

Response:

```json
{
  "recommendations": [
    {
      "type": "training_facility",
      "priority": "high",
      "action": "Speed Training",
      "reasoning": "3 support cards present (Friendship Training available), facility at Level 3, aligns with upcoming Medium race requirements",
      "expected_outcomes": {
        "speed_gain": "+45-55",
        "bond_increases": ["+7", "+7", "+7"],
        "skill_hints": ["Possible Level 2 hint for Swinging Maestro"]
      },
      "risks": ["5% failure rate due to energy level"],
      "confidence_score": 0.92
    }
  ],
  "critical_alerts": [],
  "response_time_ms": 1850,
  "ai_provider": "ollama"
}
```

### Skill Purchase Advice

**POST /api/advisory/skills/advice**

Request:

```json
{
  "character_id": "uuid-or-id",
  "storage_mode": "local",
  "sp_available": 220,
  "acquired_skills": [1, 5, 12],
  "available_skills": [
    {
      "id": 23,
      "name": "Swinging Maestro",
      "tier": "gold",
      "base_cost": 180,
      "hint_level": 3,
      "category": "stamina_recovery"
    },
    {
      "id": 45,
      "name": "Lane Legerdemain",
      "tier": "rare",
      "base_cost": 120,
      "hint_level": 2,
      "category": "positioning"
    }
  ]
}
```

Response:

```json
{
  "recommendations": [
    {
      "skill_id": 23,
      "priority": "high",
      "action": "Purchase Swinging Maestro",
      "reasoning": "Gold stamina recovery skill with Level 3 hint (30% discount). Reduces stamina requirements by 150-200. Cost: 126 SP (discounted from 180).",
      "sp_cost": 126,
      "sp_remaining": 94,
      "expected_impact": "Enables Medium/Long distance races with lower stamina investment"
    }
  ],
  "sp_budget_analysis": {
    "current": 220,
    "recommended_spend": 126,
    "remaining": 94,
    "projected_total": "300-350 by career end"
  }
}
```

### Race Strategy

**POST /api/advisory/race/strategy**

Request:

```json
{
  "character_id": "uuid-or-id",
  "race_id": 15,
  "stats": {
    "speed": 850,
    "stamina": 650,
    "power": 720,
    "guts": 580,
    "wisdom": 690
  },
  "skills": [1, 5, 12, 23],
  "aptitudes": {
    "distance_medium": "A",
    "surface_turf": "B",
    "style_escape": "A"
  }
}
```

Response:

```json
{
  "strategy": {
    "recommended_style": "escape",
    "reasoning": "A-grade Escape aptitude, sufficient stamina (650 vs 600 requirement), strong Speed stat",
    "win_probability": 0.78,
    "readiness_assessment": {
      "stamina": "sufficient",
      "speed": "excellent",
      "power": "good",
      "overall": "ready"
    },
    "risks": [
      "B-grade turf aptitude may reduce effectiveness by 5-10%"
    ],
    "preparation_checklist": [
      "✓ Stamina requirement met",
      "✓ Speed above 800",
      "✓ Recovery skills equipped",
      "⚠ Consider turf-specific skills if available"
    ]
  }
}
```

### Critical Situation Detection

**POST /api/advisory/critical/detect**

Request:

```json
{
  "career_run_id": "uuid-or-id",
  "turn_number": 35,
  "context": {
    "stats": {"speed": 600, "stamina": 320, "power": 550, "guts": 480, "wisdom": 520},
    "energy": 35,
    "upcoming_races": [{"distance": "medium", "turn": 38}],
    "support_bonds": [65, 70, 58, 75, 68, 72]
  }
}
```

Response:

```json
{
  "alerts": [
    {
      "type": "stamina_crisis",
      "priority": "critical",
      "message": "Stamina critically low for upcoming Medium race (320 vs 600 required)",
      "action_items": [
        "Focus next 3 turns on Stamina training",
        "Prioritize Friendship Training at Stamina facility",
        "Consider purchasing stamina recovery skills"
      ],
      "turns_until_critical": 3,
      "detailed_analysis": "Current stamina of 320 is 280 points below the 600 minimum for Medium distance Escape style. With 3 turns remaining, you need approximately +90 stamina per turn."
    },
    {
      "type": "energy_critical",
      "priority": "high",
      "message": "Energy at 35 - high failure rate risk",
      "action_items": [
        "Rest immediately or train Wisdom",
        "Avoid high-risk training until energy recovers to 50+"
      ],
      "turns_until_critical": 0
    }
  ]
}
```

---

## UI Components

### Advisory Panel Component

**Location**: Integrated into training screen, skill shop, race planning

**Features**:

- Collapsible panel with priority-based organization
- Expandable recommendation cards with detailed reasoning
- Dismissible alerts with session persistence
- Keyboard shortcuts (Alt+A to toggle, Arrow keys to navigate)

**Wireframe**:

```
┌─────────────────────────────────────────────────┐
│ 🤖 AI Advisory (Alt+A)                    [−]  │
├─────────────────────────────────────────────────┤
│ ⚠️ CRITICAL ALERTS (2)                          │
│ ┌───────────────────────────────────────────┐   │
│ │ 🚨 Stamina Crisis                         │   │
│ │ 320/600 for Medium race in 3 turns        │   │
│ │ [View Details] [Dismiss]                  │   │
│ └───────────────────────────────────────────┘   │
│                                                 │
│ 📊 TRAINING RECOMMENDATIONS (3)                 │
│ ┌───────────────────────────────────────────┐   │
│ │ ⭐ Speed Training (High Priority)          │   │
│ │ 3 cards present • Friendship available    │   │
│ │ Expected: +45-55 Speed, +21 bonds         │   │
│ │ [Show Details] [Apply]                    │   │
│ └───────────────────────────────────────────┘   │
│                                                 │
│ 💎 SKILL RECOMMENDATIONS (2)                    │
│ 🏁 RACE STRATEGY (1)                            │
└─────────────────────────────────────────────────┘
```

### Critical Alert Badge

**Location**: Top navigation bar, training screen header

**Behavior**:

- Pulsing red badge when critical alerts exist
- Shows alert count
- Clicking opens advisory panel to alerts section

### Recommendation Card

**Expanded View**:

```
┌─────────────────────────────────────────────────┐
│ ⭐ Speed Training                                │
│ Priority: High • Confidence: 92%                │
├─────────────────────────────────────────────────┤
│ REASONING                                       │
│ • 3 support cards present (Friendship Training) │
│ • Facility at Level 3 (1.15x multiplier)       │
│ • Aligns with Medium race in 3 turns           │
│                                                 │
│ EXPECTED OUTCOMES                               │
│ • Speed: +45-55 (base 40 × 1.15 × 1.15)        │
│ • Bonds: +7 each (3 cards)                     │
│ • Skill Hints: Possible Lv2 Swinging Maestro   │
│                                                 │
│ RISKS                                           │
│ • 5% failure rate (energy at 75)               │
│                                                 │
│ [Apply Recommendation] [Dismiss] [Feedback]    │
└─────────────────────────────────────────────────┘
```

---

## Implementation Phases

### Phase 1: Core Infrastructure (Weeks 1-2)

- TrainingAdvisoryService skeleton
- GameMechanicsEngine with core formulas
- TrainingContext value object
- Recommendation and CriticalAlert models
- Database migrations

### Phase 2: Rule-Based Advisor (Week 3)

- RuleBasedAdvisor implementation
- Training facility selection logic
- Skill purchase prioritization
- Race strategy generation
- Offline fallback testing

### Phase 3: AI Integration (Week 4)

- NeuronAIService integration
- Prompt engineering for recommendations
- Hybrid fallback logic (Ollama → Bedrock → Rules)
- Response parsing and validation

### Phase 4: Critical Detection (Week 5)

- CriticalSituationDetector implementation
- Alert generation logic
- Priority calculation
- Action item generation

### Phase 5: UI Components (Week 6)

- Advisory panel component
- Critical alert badges
- Recommendation cards
- Keyboard shortcuts
- Accessibility testing

### Phase 6: Prediction Tracking (Week 7)

- PredictionAccuracyTracker implementation
- Outcome recording
- Accuracy metrics calculation
- Model improvement flagging

### Phase 7: Testing & Refinement (Week 8)

- Property-based testing
- E2E testing with Playwright
- Performance optimization
- Documentation completion

---

## Testing Strategy

### Unit Tests

- GameMechanicsEngine formula accuracy
- RuleBasedAdvisor logic correctness
- TrainingContext serialization
- Recommendation prioritization

### Integration Tests

- NeuronAIService integration
- Database persistence
- Cache invalidation
- Fallback behavior

### Property-Based Tests

- All 15 correctness properties
- Edge case discovery
- Invariant validation

### E2E Tests (Playwright)

- Complete advisory workflow
- Critical alert handling
- Recommendation application
- Offline mode behavior

---

## Performance Considerations

### Caching Strategy

- Skill catalog: 24-hour TTL
- Race requirements: 1-hour TTL
- Support card meta: 24-hour TTL
- Recommendation cache: Turn-specific, invalidate on state change

### Optimization Targets

- Local AI: <2 seconds (p95)
- Cloud AI: <5 seconds (p95)
- Rule-based: <500ms (p95)
- Database queries: <100ms (p95)

### Monitoring

- Response time tracking per provider
- Fallback frequency
- Prediction accuracy trends
- Cache hit rates

---

## Security Considerations

- Validate all user-provided context data
- Sanitize AI-generated text before display
- Rate limit API endpoints (10 requests/minute per user)
- Encrypt sensitive data in prediction_accuracy table
- Audit log for recommendation applications

---

## Accessibility

- WCAG 2.2 AA compliance
- Keyboard navigation (Tab, Arrow keys, Enter, Escape)
- Screen reader announcements for alerts
- High contrast mode support
- Focus management in advisory panel

---

## Future Enhancements

### v2.1

- Custom recommendation preferences
- Historical recommendation analysis
- Comparative career run insights
- Export recommendation reports

### v3.0

- Machine learning model fine-tuning
- Community-sourced recommendation patterns
- Real-time multiplayer advisory
- Advanced scenario-specific strategies
