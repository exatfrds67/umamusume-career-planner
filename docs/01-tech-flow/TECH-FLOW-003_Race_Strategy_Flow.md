# TECH-FLOW-003: Race Strategy - Technical Flow & Task Breakdown

**Document Version**: 1.0 | **Date**: January 14, 2026 | **Status**: Draft

**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirement 3: Race Preparation and Strategy)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Race Strategy Architecture)
- `.kiro/specs/umamusume-career-planner-main/tasks.md` (Task 3.x: Race System)

**Related Artifacts**:

- PRD: [PRD-003](../prds/PRD-003_Race_Strategy.md)
- SPEC: [SPEC-003](../specs/SPEC-003_Race_Strategy_Technical.md)
- Flow: [FLOW-003](../flows/FLOW-003_Race_Strategy_System.md)
- Wireframes: [WF-006](../wireframes/WF-006_Race_Calendar_View.md), [WF-007](../wireframes/WF-007_Race_Preparation_Screen.md)
- Sequences: [SEQ-004](../sequences/SEQ-004_Race_Registration_and_Outcome.md)
- User Flows: [UF-004](../user-flows/UF-004_Race_Day_Flow.md)

## System Architecture

```
┌────────────────────────────────────────────────────────────────┐
│             RACE STRATEGY SYSTEM FLOW                          │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│  UI Layer (Blade Templates / Vue Components)                 │
│  ├── Race Calendar View                                       │
│  ├── Race Preparation Screen                                  │
│  ├── Strategy Recommendation Panel                            │
│  └── Race Result Display                                      │
│           ↓                                                     │
│  API Layer (REST Endpoints)                                   │
│  ├── GET /api/v1/races                                        │
│  ├── GET /api/v1/races/{id}/strategy                          │
│  ├── POST /api/v1/races/{id}/register                        │
│  └── POST /api/v1/races/{id}/complete                        │
│           ↓                                                     │
│  Service Layer (Business Logic)                               │
│  ├── RaceRequirementAnalyzer                                  │
│  ├── WeatherImpactCalculator                                  │
│  ├── RunningStyleOptimizer                                    │
│  └── RaceStrategyService                                      │
│           ↓                                                     │
│  Repository Layer (Data Access)                               │
│  ├── RaceRepository                                           │
│  └── RaceHistoryRepository                                    │
│           ↓                                                     │
│  Database Layer (MySQL)                                       │
│  ├── races table                                              │
│  ├── race_requirements table                                  │
│  ├── race_strategies table                                    │
│  └── race_history table                                       │
│                                                                │
└────────────────────────────────────────────────────────────────┘
```

## Data Flow Diagrams

### 3.1 Race Strategy Generation Flow

```
User requests strategy for race
    ↓
RaceStrategyController::getStrategy()
    ↓
RaceStrategyService::generateStrategy()
    │
    ├─→ RaceRequirementAnalyzer::analyzeRequirements()
    │   ├─→ Determine distance category (Sprint/Mile/Medium/Long)
    │   ├─→ Apply competition level multiplier (URAFinale: 1.2x)
    │   ├─→ Compare character stats vs thresholds
    │   └─→ Return stat evaluation (○/⦾/△/×)
    │
    ├─→ WeatherImpactCalculator::calculateImpact()
    │   ├─→ Apply weather modifier (Rainy: 0.95x, Snowy: 0.85x)
    │   ├─→ Apply track condition modifier
    │   ├─→ Check for weather-specific skills
    │   └─→ Recommend weather adaptations
    │
    ├─→ RunningStyleOptimizer::recommendStyle()
    │   ├─→ Score FrontRunner (Speed + Guts focus)
    │   ├─→ Score PaceChaser (Balanced approach)
    │   ├─→ Score LateSurger (Power + Guts focus)
    │   ├─→ Score Stalker (Wit + Power focus)
    │   └─→ Return highest-scoring style
    │
    └─→ Create RaceStrategy record
            ↓
        Return comprehensive strategy
```

### 3.2 Race Completion Flow

```
User completes race
    ↓
RaceStrategyController::completeRace()
    ↓
RaceStrategyService::recordCompletion()
    │
    ├─→ Calculate actual placement
    ├─→ Compare predicted vs actual
    ├─→ Update character stats (Fan count, etc.)
    ├─→ Record RaceHistory entry
    ├─→ Award rewards (skills, items)
    └─→ Trigger RaceCompleted event
            ↓
        AuditLogger logs completion
            ↓
        Update character state
            ↓
Response with race results
```

## Implementation Tasks

### Task 3.1: Race Analysis Engine (Week 1-2, ~24 hours)

- [ ] **3.1.1**: Create RaceRequirementAnalyzer service
  - Distance category thresholds (Sprint/Mile/Medium/Long)
  - Competition level modifiers (URAFinale: 1.2x)
  - Stat evaluation logic (○/⦾/△/× indicators)
  - Unit tests: 6 tests
  - **Files**: `app/Services/RaceRequirementAnalyzer.php`
  - **Effort**: 8 hours

- [ ] **3.1.2**: Create WeatherImpactCalculator service
  - Weather modifiers by surface type
  - Track condition impacts
  - Weather-specific skill detection
  - Unit tests: 5 tests
  - **Files**: `app/Services/WeatherImpactCalculator.php`
  - **Effort**: 6 hours

- [ ] **3.1.3**: Create RunningStyleOptimizer service
  - FrontRunner scoring algorithm
  - PaceChaser scoring algorithm
  - LateSurger scoring algorithm
  - Stalker scoring algorithm
  - Unit tests: 8 tests
  - **Files**: `app/Services/RunningStyleOptimizer.php`
  - **Effort**: 10 hours

### Task 3.2: Race Strategy Service (Week 2, ~16 hours)

- [ ] **3.2.1**: Create RaceStrategyService
  - Integrate all analysis engines
  - Generate comprehensive strategy recommendations
  - Skill selection advisor
  - Pre-race preparation planner
  - Unit tests: 6 tests
  - **Files**: `app/Services/RaceStrategyService.php`
  - **Effort**: 10 hours

- [ ] **3.2.2**: Implement race prediction logic
  - Performance forecasting
  - Placement probability calculation
  - Outcome scenario generation
  - Unit tests: 4 tests
  - **Files**: `app/Services/RacePredictionService.php`
  - **Effort**: 6 hours

### Task 3.3: Repository Layer (Week 3, ~8 hours)

- [ ] **3.3.1**: Create RaceRepository
  - CRUD operations for races
  - Query methods (by date, grade, distance)
  - Filtering and sorting
  - Unit tests: 4 tests
  - **Files**: `app/Repositories/RaceRepository.php`
  - **Effort**: 4 hours

- [ ] **3.3.2**: Create RaceHistoryRepository
  - Race completion records
  - Historical analysis queries
  - Statistics aggregation
  - Unit tests: 3 tests
  - **Files**: `app/Repositories/RaceHistoryRepository.php`
  - **Effort**: 4 hours

### Task 3.4: API Layer (Week 3, ~12 hours)

- [ ] **3.4.1**: Create RaceStrategyController
  - GET /api/v1/races (list available races)
  - GET /api/v1/races/{id} (race details)
  - GET /api/v1/races/{id}/strategy (generate strategy)
  - POST /api/v1/races/{id}/register (register for race)
  - POST /api/v1/races/{id}/complete (record completion)
  - DELETE /api/v1/races/{id}/register (cancel registration)
  - **Files**: `app/Http/Controllers/API/RaceStrategyController.php`
  - **Effort**: 8 hours

- [ ] **3.4.2**: Create Form Requests
  - RaceRegistrationRequest (validation)
  - RaceCompletionRequest (validation)
  - Unit tests: 4 tests
  - **Files**: `app/Http/Requests/RaceRegistrationRequest.php`, `RaceCompletionRequest.php`
  - **Effort**: 4 hours

### Task 3.5: Database Schema (Week 4, ~6 hours)

- [ ] **3.5.1**: Create races migration
  - Table: races (id, name, grade, distance, surface, weather, track_condition, requirements_json)
  - **Files**: `database/migrations/YYYY_MM_DD_create_races_table.php`
  - **Effort**: 2 hours

- [ ] **3.5.2**: Create race_requirements migration
  - Table: race_requirements (id, race_id, stat_name, min_value, recommended_value, optimal_value)
  - **Files**: `database/migrations/YYYY_MM_DD_create_race_requirements_table.php`
  - **Effort**: 2 hours

- [ ] **3.5.3**: Create race_strategies migration
  - Table: race_strategies (id, character_id, race_id, recommended_style, weather_advice_json, skill_recommendations_json)
  - **Files**: `database/migrations/YYYY_MM_DD_create_race_strategies_table.php`
  - **Effort**: 1 hour

- [ ] **3.5.4**: Create race_history migration
  - Table: race_history (id, character_id, race_id, placement, actual_time, predicted_time, strategy_used)
  - **Files**: `database/migrations/YYYY_MM_DD_create_race_history_table.php`
  - **Effort**: 1 hour

### Task 3.6: Testing & Integration (Week 4, ~14 hours)

- [ ] **3.6.1**: Integration tests
  - Complete race strategy flow (request → analysis → recommendation)
  - Race completion workflow
  - Strategy accuracy validation
  - Feature tests: 8 tests
  - **Files**: `tests/Feature/RaceStrategyTest.php`
  - **Effort**: 8 hours

- [ ] **3.6.2**: Performance testing
  - Strategy generation speed (< 200ms)
  - Race list pagination efficiency
  - Historical data queries
  - **Files**: `tests/Performance/RaceStrategyPerformanceTest.php`
  - **Effort**: 4 hours

- [ ] **3.6.3**: Edge case testing
  - Invalid race registrations
  - Missing weather data handling
  - Extreme stat values
  - Unit tests: 6 tests
  - **Effort**: 2 hours

## Summary

**Total Effort**: ~60 hours (2-3 weeks)

**Total Tests**: 25+ (15 unit tests, 8 feature tests, 2 performance tests)

**Key Deliverables**:

- 3 analysis engines (RaceRequirementAnalyzer, WeatherImpactCalculator, RunningStyleOptimizer)
- 2 strategy services (RaceStrategyService, RacePredictionService)
- 2 repositories (RaceRepository, RaceHistoryRepository)
- 1 controller with 6 REST endpoints
- 4 database tables with migrations
- Comprehensive test coverage

**Dependencies**:

- Requires SPEC-001 (Character Management) for character stat access
- Integrates with SPEC-004 (Skill Management) for skill recommendations
- Uses SPEC-002 (Training Optimization) data for stat predictions
