# TECH-FLOW-002: Training Optimization - Technical Flow & Task Breakdown

**Document Version**: 1.0 | **Date**: January 14, 2026 | **Status**: Draft

## System Architecture

```
Training Optimization System
├── Prediction Engine
│   ├── StatGainCalculationEngine
│   ├── BonusMultiplierEngine
│   ├── SkillHintProbabilityEngine
│   └── RankingEngine
├── Support Card Service
│   ├── DeckValidation
│   ├── BonusCalculation
│   └── FriendshipTrainingService
├── Scenario Service
│   ├── URAFinaleService
│   └── UnityEupService
└── Repository Layer
    ├── TrainingSessionRepository
    ├── SupportCardRepository
    └── PredictionHistoryRepository
```

## Data Flow

```
CharacterState + SupportDeck
    ↓
TrainingPredictionService::getPredictions()
    ├─→ For each facility (Speed, Stamina, Power, Guts, Wit):
    │   ├─→ StatGainCalculationEngine::calculate()
    │   │   ├─→ Base gain + support bonuses
    │   │   ├─→ Growth rate multiplier
    │   │   ├─→ Friendship training bonus (if bond >= 80%)
    │   │   └─→ Facility level multiplier (Unity only)
    │   ├─→ SkillHintProbabilityEngine::identify()
    │   │   ├─→ Red exclamation cards (guaranteed)
    │   │   └─→ Normal skill hints (25% chance)
    │   ├─→ Mood prediction
    │   └─→ Store in TrainingPrediction
    ↓
TrainingRankingEngine::rankOptions()
    ├─→ Score component 1: Goal alignment (0-40 pts)
    ├─→ Score component 2: Stat efficiency (0-30 pts)
    ├─→ Score component 3: Scenario bonus (0-20 pts)
    └─→ Score component 4: Skill hints (0-10 pts)
    ↓
Return ranked predictions [highest score first]
```

## Implementation Tasks

### Task 2.1: Calculation Engines (Week 1-2)

- [ ] **2.1.1**: Create StatGainCalculationEngine
  - Base stat calculation by facility
  - Character growth rate application
  - Support card bonus aggregation
  - Friendship training multiplier (bond >= 80%)
  - Unit tests: 8 tests

- [ ] **2.1.2**: Create BonusMultiplierEngine
  - Facility level multipliers (1.0x to 2.0x)
  - Support card specialization bonuses
  - Limit break effect calculations
  - Unit tests: 6 tests

- [ ] **2.1.3**: Create SkillHintProbabilityEngine
  - Red exclamation identification (guaranteed)
  - Normal skill hint probability (25%)
  - Hint discount calculation (20% per hint, max 40%)
  - Unit tests: 5 tests

- [ ] **2.1.4**: Create RankingEngine
  - Goal alignment scoring
  - Stat efficiency calculation
  - Scenario-specific bonuses
  - Final score computation
  - Unit tests: 6 tests

### Task 2.2: Services (Week 2-3)

- [ ] **2.2.1**: Create TrainingPredictionService
  - Method: getPredictions(Character, SupportDeck)
  - Caching: 5-minute TTL
  - Integration: all calculation engines

- [ ] **2.2.2**: Create TrainingOptimizationService
  - Method: optimizeTrainingSequence()
  - CQRS pattern: Commands & Queries
  - Event triggering

- [ ] **2.2.3**: Create SupportCardBonusService
  - Method: calculateTotalBonus()
  - Specialization matching
  - Limit break aggregation

- [ ] **2.2.4**: Create URAFinaleService
  - Method: predictTrainingOptions()
  - 5 main facilities
  - Skill hint identification
  - Mood change prediction

- [ ] **2.2.5**: Create UnityEupService
  - Method: predictTrainingOptions()
  - Spirit Burst mechanics
  - Team synergy bonuses
  - Facility level effects

### Task 2.3: Database (Week 2)

- [ ] **2.3.1**: Create training_sessions table
  - Predicted vs. actual stat gains
  - Support card IDs used
  - Skill acquisitions
  - Mood/energy changes

- [ ] **2.3.2**: Create training_predictions table
  - Facility, rank score, predicted gains
  - Support cards considered
  - Expiration timestamps

- [ ] **2.3.3**: Create support_cards table (character-level)
  - Card ID, name, rarity
  - Limit breaks, specialization
  - Bond level, skills provided

- [ ] **2.3.4**: Create skill_hints table
  - Character skill hint counts
  - SP savings per hint
  - Support card source

### Task 2.4: Controllers & Endpoints (Week 3)

- [ ] **2.4.1**: Create TrainingPredictionController
  - GET /api/v1/characters/{id}/training-predictions
  - Returns: Ranked predictions with all details
  - Caching: Automated, 5 min

- [ ] **2.4.2**: Create TrainingRecommendationController
  - GET /api/v1/characters/{id}/training-recommendation
  - Uses: AI/Ollama for complex reasoning
  - Returns: Top choice + alternatives + warnings

- [ ] **2.4.3**: Create TrainingSessionController
  - POST /api/v1/characters/{id}/training-sessions
  - Workflow: Record actual results, update character
  - Calculation: Accuracy metrics

### Task 2.5: AI Integration (Week 3-4)

- [ ] **2.5.1**: Create RecommendationEngine (Ollama)
  - Local model integration
  - Context building
  - Prompt engineering for trainee optimization

- [ ] **2.5.2**: Create Bedrock Fallback
  - Claude integration via AWS SDK
  - Model selection logic
  - Cost tracking

- [ ] **2.5.3**: Implement PredictionAccuracyLearning
  - Collect prediction vs. actual data
  - Trigger model retraining (batch job)
  - Track accuracy improvement

### Task 2.6: Testing (Week 4)

- [ ] **2.6.1**: Unit tests (20 tests)
  - Stat gain calculations with various modifiers
  - Bonus aggregation
  - Ranking algorithm
  - Skill hint probability

- [ ] **2.6.2**: Integration tests (10 tests)
  - End-to-end prediction workflow
  - Training session creation
  - Character state updates

- [ ] **2.6.3**: API tests (8 tests)
  - Prediction retrieval
  - Recommendation generation
  - Session completion

- [ ] **2.6.4**: Performance tests (3 tests)
  - Prediction generation < 200ms
  - Ranking < 100ms
  - Bulk session processing < 500ms

## Data Structures

### TrainingPrediction Response

```php
[
    'facility' => 'Speed',
    'rank' => 1,
    'score' => 92.5,
    'predicted_gains' => [
        'speed' => 45,
        'stamina' => 5,
        ...
    ],
    'support_cards' => [...],
    'skill_hints' => [...],
    'mood_prediction' => [
        'current' => 'Good',
        'after' => 'Normal'
    ],
    'efficiency_rating' => 'Excellent'
]
```

## Estimated Effort

- **Calculation Engines**: 20 hours
- **Services**: 20 hours
- **Database & Migrations**: 6 hours
- **Controllers**: 10 hours
- **AI Integration**: 16 hours
- **Testing**: 16 hours

**Total**: ~88 hours (~2-3 weeks)

## Success Criteria

- [ ] All 4 calculation engines implemented
- [ ] 5 services with business logic
- [ ] 3 controllers with REST endpoints
- [ ] 35+ passing tests
- [ ] Prediction accuracy >= 95%
- [ ] Performance targets met
- [ ] Ollama + Bedrock integration working

---

**Related**: [SPEC-002], [SPEC-005], [SPEC-006]

