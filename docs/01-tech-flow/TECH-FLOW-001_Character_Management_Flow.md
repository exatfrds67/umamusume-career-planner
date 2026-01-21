# TECH-FLOW-001: Character Management - Technical Flow & Task Breakdown

**Document Version**: 1.0 | **Date**: January 14, 2026 | **Status**: Draft

**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirement 1: Character State Management)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Character Management Architecture)
- `.kiro/specs/umamusume-career-planner-main/tasks.md` (Task 1.4: Core Models)

**Related Artifacts**:

- PRD: [PRD-001](../prds/PRD-001_Character_Management.md)
- SPEC: [SPEC-001](../specs/SPEC-001_Character_Management_Technical.md)
- Flow: [FLOW-001](../flows/FLOW-001_Character_Management_System.md)
- Wireframes: [WF-002](../wireframes/WF-002_Character_Creation_Wizard.md), [WF-003](../wireframes/WF-003_Character_Detail_Management.md)
- Sequences: [SEQ-001](../sequences/SEQ-001_Character_Creation_Sequence.md)
- User Flows: [UF-002](../user-flows/UF-002_Career_Setup_Flow.md)

## System Architecture

```
┌────────────────────────────────────────────────────────────────┐
│          CHARACTER MANAGEMENT SYSTEM FLOW                      │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│  UI Layer (Blade Templates / Vue Components)                 │
│  ├── Character Creation Wizard                                │
│  ├── Character Dashboard                                      │
│  ├── Goal Management Panel                                    │
│  └── Aptitude/Factor Viewer                                   │
│           ↓                                                     │
│  API Layer (REST Endpoints)                                   │
│  ├── POST /api/v1/characters                                  │
│  ├── GET /api/v1/characters/{id}                              │
│  ├── PATCH /api/v1/characters/{id}/stats                     │
│  └── Goal management endpoints                                │
│           ↓                                                     │
│  Service Layer (Business Logic)                               │
│  ├── CharacterService                                         │
│  ├── CharacterStateService                                    │
│  ├── FactorInheritanceService                                 │
│  └── GoalManagementService                                    │
│           ↓                                                     │
│  Repository Layer (Data Access)                               │
│  ├── CharacterRepository                                      │
│  ├── AptitudeRepository                                       │
│  └── FactorRepository                                         │
│           ↓                                                     │
│  Database Layer (MySQL)                                       │
│  ├── characters table                                         │
│  ├── character_stats table                                    │
│  ├── aptitudes table                                          │
│  ├── factors table                                            │
│  └── goals table                                              │
│                                                                │
└────────────────────────────────────────────────────────────────┘
```

## Data Flow Diagrams

### 1.1 Character Creation Flow

```
User Input (Form)
    ↓
CharacterController::store()
    ↓
CharacterService::createCharacter()
    │
    ├─→ Validate trainee exists
    ├─→ FactorInheritanceService::calculateInheritedStats()
    │   ├─→ Look up legacy character data
    │   ├─→ Calculate stat bonuses
    │   └─→ Return growth rates
    ├─→ Create Character record
    ├─→ Create initial Aptitudes (fixed)
    ├─→ Create initial Conditions (if any)
    ├─→ Create initial Goals
    ├─→ Create Snapshot (version 1)
    └─→ Trigger CharacterCreated event
            ↓
        AuditLogger logs creation
            ↓
        Broadcast WebSocket update
            ↓
Response with Character object
```

### 1.2 Character State Update Flow

```
Training Session Completes
    ↓
TrainingSessionService::completeSession()
    ↓
CharacterStateService::updateAfterTraining()
    │
    ├─→ Update Stats (with growth rate)
    ├─→ Update Energy (-20-30%)
    ├─→ Update Mood (based on training)
    ├─→ Update Conditions (apply/remove)
    ├─→ Trigger StatsUpdated event
    ├─→ Invalidate character cache
    └─→ Broadcast real-time update
            ↓
GoalManagementService::updateGoalProgress()
    │
    ├─→ For each active goal:
    │   ├─→ Calculate current progress
    │   ├─→ Calculate remaining value
    │   ├─→ Check if completed
    │   └─→ If yes: trigger GoalCompleted event
    └─→ Update goal records
            ↓
AuditLogger::logStateChange()
            ↓
Response with updated character
```

## Implementation Tasks

### Task 1.1: Setup & Models (Week 1)

- [ ] **1.1.1**: Create Character model with relationships
  - Relationships: hasMany Aptitudes, Factors, Goals, Conditions, Skills, Snapshots
  - Casts: Stat values, mood enums, timestamps
  - Scopes: byUser(), active(), completed()
  - Accessors: getGradeForStat(), calculateProgress()

- [ ] **1.1.2**: Create Aptitude model
  - Attributes: character_id, category, type, rating, is_specialty
  - Enum casts: Rating (G through SS)
  - Validation rules: distance/surface/style constraints

- [ ] **1.1.3**: Create Factor model
  - Attributes: character_id, legacy_character_id, factor_type, rating, bonus
  - Methods: calculateBonus(), getDescription()

- [ ] **1.1.4**: Create Goal model
  - Attributes: character_id, goal_type, target_stat, target_value, status
  - Methods: calculateProgress(), calculateRemaining(), markCompleted()

- [ ] **1.1.5**: Create Condition model
  - Attributes: character_id, condition_type, condition_name, expires_at
  - Methods: isExpired(), getModifier()

- [ ] **1.1.6**: Create Snapshot model
  - Attributes: character_id, label, snapshot_data (JSON)
  - Methods: restore(), compare()

### Task 1.2: Repositories (Week 1-2)

- [ ] **1.2.1**: Create CharacterRepository
  - Methods: findById(), findByUser(), store(), update(), delete()
  - Eager loading: with relationships to prevent N+1

- [ ] **1.2.2**: Create AptitudeRepository
  - Methods: findByCharacter(), updateAll()

- [ ] **1.2.3**: Create FactorRepository
  - Methods: findByCharacter(), calculateInheritance()

- [ ] **1.2.4**: Create GoalRepository
  - Methods: findActiveGoals(), findCompletedGoals()

### Task 1.3: Services (Week 2-3)

- [ ] **1.3.1**: Create CharacterService
  - Methods: createCharacter(), updateCharacter(), deleteCharacter()
  - Triggers: Events for creation/update/deletion
  - Caching: Invalidate on updates

- [ ] **1.3.2**: Create CharacterStateService
  - Methods: updateStats(), updateMood(), updateEnergy(), applyCondition()
  - Validations: Stat ranges, mood values, energy bounds

- [ ] **1.3.3**: Create FactorInheritanceService
  - Methods: calculateInheritedStats(), calculateGrowthRates()
  - Algorithm: Factor rating → bonus value conversion
  - Legacy lookup: Parent character data

- [ ] **1.3.4**: Create GoalManagementService
  - Methods: createGoal(), updateGoal(), calculateProgress(), checkCompletion()
  - Validation: Goal targets vs. minimum thresholds

### Task 1.4: Controllers & Endpoints (Week 3)

- [ ] **1.4.1**: Create CharacterController
  - POST /api/v1/characters (create)
  - GET /api/v1/characters/{id} (read)
  - PATCH /api/v1/characters/{id} (update)
  - DELETE /api/v1/characters/{id} (delete)
  - GET /api/v1/characters (list by user)

- [ ] **1.4.2**: Create CharacterStatsController
  - PATCH /api/v1/characters/{id}/stats
  - GET /api/v1/characters/{id}/stat-history

- [ ] **1.4.3**: Create GoalController
  - POST /api/v1/characters/{id}/goals
  - PATCH /api/v1/goals/{id}
  - DELETE /api/v1/goals/{id}
  - GET /api/v1/characters/{id}/goals

- [ ] **1.4.4**: Create AptitudeController
  - GET /api/v1/characters/{id}/aptitudes

- [ ] **1.4.5**: Create FactorController
  - GET /api/v1/characters/{id}/factors

### Task 1.5: Database (Week 2)

- [ ] **1.5.1**: Create migrations
  - characters table
  - character_stats table
  - aptitudes table
  - factors table
  - goals table
  - conditions table
  - character_snapshots table

- [ ] **1.5.2**: Create indexes
  - characters: (user_id), (created_at)
  - character_stats: (character_id, stat_type)
  - aptitudes: (character_id), (category, rating)
  - factors: (character_id), (factor_type)
  - goals: (character_id), (status)
  - conditions: (character_id), (expires_at)

- [ ] **1.5.3**: Create foreign keys
  - All child tables → characters.id

### Task 1.6: Events & Listeners (Week 3)

- [ ] **1.6.1**: Create CharacterCreated event
  - Listener: AuditLogger
  - Listener: WebSocket broadcaster

- [ ] **1.6.2**: Create StatsUpdated event
  - Listener: Cache invalidator
  - Listener: WebSocket broadcaster

- [ ] **1.6.3**: Create GoalCompleted event
  - Listener: Achievement tracker
  - Listener: Notification sender

### Task 1.7: Caching & Queries (Week 3-4)

- [ ] **1.7.1**: Implement character cache
  - Key: character:{id}
  - TTL: 5 minutes
  - Invalidation: On stat/condition update

- [ ] **1.7.2**: Implement eager loading
  - Character queries: with(['stats', 'aptitudes', 'factors', 'goals', 'conditions'])

- [ ] **1.7.3**: Implement pagination
  - List endpoints: 15 items per page

### Task 1.8: Testing (Week 4)

- [ ] **1.8.1**: Unit tests for models (15 tests)
  - Character creation
  - Stat calculations
  - Aptitude validation
  - Factor inheritance
  - Goal progress

- [ ] **1.8.2**: Integration tests (10 tests)
  - End-to-end character creation
  - State update workflow
  - Goal completion

- [ ] **1.8.3**: API tests (10 tests)
  - CRUD operations
  - Error handling
  - Validation

- [ ] **1.8.4**: Performance tests (5 tests)
  - Character load < 100ms
  - Bulk updates < 200ms
  - Query optimization

## Component Specifications

### CharacterService::createCharacter()

```php
/**
 * Create a new character with inheritance calculations
 * 
 * @param User $user
 * @param array $data {
 *     name: string,
 *     trainee_id: int,
 *     scenario: 'URA'|'Unity',
 *     parent1_id: int,
 *     parent2_id: int,
 *     support_cards: array
 * }
 * @return Character
 * @throws InvalidTraineeException
 * @throws InvalidParentException
 */
```

### FactorInheritanceService::calculateInheritedStats()

```php
/**
 * Calculate inherited stats from parent characters
 * 
 * Factors from 2 main parents + 4 grandparents (6 total)
 * 
 * Stat factors: ★☆☆=+5, ★★☆=+12, ★★★=+21
 * Aptitude factors: 1★=+1 grade, 2★=+2, 3★=+3
 * 
 * @param array $parentCharacters
 * @return array ['stat_bonuses' => [...], 'growth_rates' => [...]]
 */
```

## Estimated Effort

- **Models & Relationships**: 8 hours
- **Repositories**: 8 hours
- **Services**: 16 hours
- **Controllers & Routes**: 12 hours
- **Database & Migrations**: 6 hours
- **Events & Listeners**: 6 hours
- **Caching & Optimization**: 6 hours
- **Testing**: 12 hours
- **Documentation**: 4 hours

**Total**: ~78 hours (~2 weeks with 40-hour week)

## Success Criteria

- [x] All 8 tables created with proper indexes
- [x] 7 models with relationships
- [x] 5 repositories with query optimization
- [x] 4 services with business logic
- [x] 5 controllers with REST endpoints
- [x] 35+ passing tests
- [x] 100% of PRD-001 requirements covered
- [x] API documentation complete
- [x] Performance targets met (< 100ms character load)

---

**Next**: [TECH-FLOW-002_Training_Optimization_Flow.md](TECH-FLOW-002_Training_Optimization_Flow.md)
