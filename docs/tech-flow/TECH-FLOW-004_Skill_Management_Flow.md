# TECH-FLOW-004: Skill Management - Technical Flow & Task Breakdown

**Document Version**: 1.0 | **Date**: January 14, 2026 | **Status**: Draft

**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirement 4: Comprehensive Skill Management)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Skill Management Architecture)
- `.kiro/specs/umamusume-career-planner-main/tasks.md` (Task 4.x: Skill System)

**Related Artifacts**:

- PRD: [PRD-004](../prds/PRD-004_Skill_Management.md)
- SPEC: [SPEC-004](../specs/SPEC-004_Skill_Management_Technical.md)
- Flow: [FLOW-004](../flows/FLOW-004_Skill_Management_System.md)
- Wireframes: [WF-008](../wireframes/WF-008_Skill_Shop_Interface.md), [WF-009](../wireframes/WF-009_Skill_Loadout_Manager.md)
- Sequences: [SEQ-003](../sequences/SEQ-003_Skill_Acquisition_and_Upgrade.md)
- User Flows: [UF-005](../user-flows/UF-005_Skill_Management_Flow.md)

## System Architecture

```
┌────────────────────────────────────────────────────────────────┐
│             SKILL MANAGEMENT SYSTEM FLOW                       │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│  UI Layer (Blade Templates / Vue Components)                 │
│  ├── Skill Shop Interface                                     │
│  ├── Skill Loadout Manager                                    │
│  ├── Skill Hint Tracker                                       │
│  └── Skill Evolution Panel                                    │
│           ↓                                                     │
│  API Layer (REST Endpoints)                                   │
│  ├── GET /api/v1/skills                                       │
│  ├── POST /api/v1/characters/{id}/skills                      │
│  ├── GET /api/v1/characters/{id}/hints                        │
│  └── POST /api/v1/characters/{id}/skills/evolve              │
│           ↓                                                     │
│  Service Layer (Business Logic)                               │
│  ├── SkillCatalogService                                      │
│  ├── SkillHintService                                         │
│  └── SkillEvolutionService                                    │
│           ↓                                                     │
│  Repository Layer (Data Access)                               │
│  └── SkillRepository                                          │
│           ↓                                                     │
│  Database Layer (MySQL)                                       │
│  ├── skills table                                             │
│  ├── skill_acquisitions table                                 │
│  ├── skill_hints table                                        │
│  └── skill_evolutions table                                   │
│                                                                │
└────────────────────────────────────────────────────────────────┘
```

## Data Flow Diagrams

### 4.1 Skill Acquisition Flow

```
User selects skill in shop
    ↓
SkillController::acquireSkill()
    ↓
SkillAcquisitionService::acquire()
    │
    ├─→ Validate character has sufficient SP
    ├─→ SkillHintService::calculateFinalCost()
    │   ├─→ Base SP cost from skill
    │   ├─→ Apply hint discount (20% per hint, max 40%)
    │   └─→ Return discounted cost
    ├─→ Deduct SP from character
    ├─→ Create SkillAcquisition record
    ├─→ Remove used skill hints
    └─→ Trigger SkillAcquired event
            ↓
        Update character skill list
            ↓
Response with updated character
```

### 4.2 Skill Hint Tracking Flow

```
Training session completes with red exclamation
    ↓
TrainingService::processSkillHint()
    ↓
SkillHintService::trackHintAcquisition()
    │
    ├─→ Check if hint already exists for this skill
    ├─→ If exists: increment hint_count (max 2)
    ├─→ If new: create SkillHint record
    └─→ Return SkillHint with current discount %
            ↓
        Update UI skill shop with discounted prices
```

### 4.3 Skill Evolution Flow

```
User triggers evolution for Normal skill
    ↓
SkillEvolutionController::evolveSkill()
    ↓
SkillEvolutionService::evolveSkill()
    │
    ├─→ Validate skill has evolution path
    ├─→ Check character has base skill
    ├─→ Calculate evolution SP cost difference
    ├─→ Deduct additional SP cost
    ├─→ Remove Normal skill
    ├─→ Add Rare skill variant
    └─→ Create SkillEvolution record
            ↓
        Update character skill collection
            ↓
Response with evolved skill
```

## Implementation Tasks

### Task 4.1: Skill Catalog Service (Week 1, ~12 hours)

- [ ] **4.1.1**: Create Skill model and migration
  - Fields: name, category, sp_cost, target_stat, effect_description, rarity, evolution_from
  - Categories: Normal, Rare, Unique
  - Unit tests: 4 tests
  - **Files**: `app/Models/Skill.php`, `database/migrations/*_create_skills_table.php`
  - **Effort**: 4 hours

- [ ] **4.1.2**: Create SkillCatalogService
  - List all available skills
  - Filter by category, target stat, SP cost range
  - Search by name/description
  - Unit tests: 5 tests
  - **Files**: `app/Services/SkillCatalogService.php`
  - **Effort**: 6 hours

- [ ] **4.1.3**: Seed skill database
  - Import skill data from game source
  - Create factory for testing
  - **Files**: `database/seeders/SkillSeeder.php`, `database/factories/SkillFactory.php`
  - **Effort**: 2 hours

### Task 4.2: Skill Hint Management (Week 1-2, ~14 hours)

- [ ] **4.2.1**: Create SkillHint model and migration
  - Fields: character_id, skill_id, support_card_id, hint_count
  - Constraints: max 2 hints per skill
  - Unit tests: 3 tests
  - **Files**: `app/Models/SkillHint.php`, `database/migrations/*_create_skill_hints_table.php`
  - **Effort**: 3 hours

- [ ] **4.2.2**: Create SkillHintService
  - Calculate discounted SP cost (20% per hint, max 40%)
  - Track hint acquisition during training
  - Auto-consume hints on skill purchase
  - Unit tests: 6 tests
  - **Files**: `app/Services/SkillHintService.php`
  - **Effort**: 8 hours

- [ ] **4.2.3**: Integrate with training system
  - Detect red exclamation events
  - Create hint records automatically
  - Update skill shop UI with discounts
  - Integration tests: 3 tests
  - **Files**: Integration in TrainingService
  - **Effort**: 3 hours

### Task 4.3: Skill Evolution (Week 2, ~8 hours)

- [ ] **4.3.1**: Create SkillEvolution model and migration
  - Fields: normal_skill_id, rare_skill_id, sp_cost_difference
  - Evolution paths data
  - **Files**: `app/Models/SkillEvolution.php`, `database/migrations/*_create_skill_evolutions_table.php`
  - **Effort**: 2 hours

- [ ] **4.3.2**: Create SkillEvolutionService
  - Validate evolution eligibility
  - Calculate additional SP cost
  - Swap Normal for Rare skill
  - Unit tests: 5 tests
  - **Files**: `app/Services/SkillEvolutionService.php`
  - **Effort**: 6 hours

### Task 4.4: API Layer (Week 2, ~10 hours)

- [ ] **4.4.1**: Create SkillController
  - GET /api/v1/skills (list all skills)
  - GET /api/v1/skills/{id} (skill details)
  - GET /api/v1/characters/{id}/skills (owned skills)
  - POST /api/v1/characters/{id}/skills (acquire skill)
  - DELETE /api/v1/characters/{id}/skills/{skillId} (remove skill)
  - **Files**: `app/Http/Controllers/API/SkillController.php`
  - **Effort**: 6 hours

- [ ] **4.4.2**: Create SkillEvolutionController
  - POST /api/v1/characters/{id}/skills/{skillId}/evolve
  - GET /api/v1/skills/evolutions (list evolution paths)
  - **Files**: `app/Http/Controllers/API/SkillEvolutionController.php`
  - **Effort**: 4 hours

### Task 4.5: Testing & Integration (Week 3, ~10 hours)

- [ ] **4.5.1**: Feature tests
  - Complete skill acquisition flow
  - Skill hint discount validation
  - Skill evolution workflow
  - SP cost calculations
  - Feature tests: 8 tests
  - **Files**: `tests/Feature/SkillManagementTest.php`
  - **Effort**: 6 hours

- [ ] **4.5.2**: Edge case testing
  - Insufficient SP handling
  - Invalid evolution attempts
  - Duplicate skill prevention
  - Hint overflow handling (> 2 hints)
  - Unit tests: 6 tests
  - **Effort**: 4 hours

## Summary

**Total Effort**: ~54 hours (2-3 weeks)

**Total Tests**: 20+ (15 unit tests, 8 feature tests, 3 integration tests)

**Key Deliverables**:

- 3 services (SkillCatalogService, SkillHintService, SkillEvolutionService)
- 2 controllers with 7 REST endpoints
- 4 database tables with migrations
- Skill database seeder with game data
- Complete hint discount calculation system
- Skill evolution path implementation

**Dependencies**:

- Requires SPEC-001 (Character Management) for character SP tracking
- Integrates with SPEC-002 (Training Optimization) for hint acquisition
- Used by SPEC-003 (Race Strategy) for skill recommendations
