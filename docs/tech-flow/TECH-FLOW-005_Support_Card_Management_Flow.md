# TECH-FLOW-005: Support Card Management - Technical Flow & Task Breakdown

**Document Version**: 1.0 | **Date**: January 14, 2026 | **Status**: Draft

**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirement 6: Support Card Configuration)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Support Card Architecture)
- `.kiro/specs/umamusume-career-planner-main/tasks.md` (Task 5.x: Support Card System)

**Related Artifacts**:

- PRD: [PRD-005](../prds/PRD-005_Support_Card_Management.md)
- SPEC: [SPEC-005](../specs/SPEC-005_Support_Card_Management_Technical.md)
- Flow: [FLOW-005](../flows/FLOW-005_Support_Card_Management_System.md)
- Wireframes: [WF-010](../wireframes/WF-010_Support_Card_Collection.md), [WF-011](../wireframes/WF-011_Support_Deck_Builder.md)
- Sequences: [SEQ-005](../sequences/SEQ-005_Support_Card_Upgrade.md)
- User Flows: [UF-006](../user-flows/UF-006_Support_Deck_Building_Flow.md)

## System Architecture

```
┌────────────────────────────────────────────────────────────────┐
│          SUPPORT CARD MANAGEMENT SYSTEM FLOW                   │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│  UI Layer (Blade Templates / Vue Components)                 │
│  ├── Support Card Collection Grid                             │
│  ├── Support Deck Builder                                     │
│  ├── Card Limit Break Interface                               │
│  └── Bond Level Tracker                                       │
│           ↓                                                     │
│  API Layer (REST Endpoints)                                   │
│  ├── GET /api/v1/support-cards                                │
│  ├── POST /api/v1/characters/{id}/deck                        │
│  ├── PATCH /api/v1/support-cards/{id}/limit-break            │
│  └── GET /api/v1/characters/{id}/deck/bonuses                │
│           ↓                                                     │
│  Service Layer (Business Logic)                               │
│  ├── DeckCompositionService                                   │
│  ├── SupportCardBonusCalculator                               │
│  └── BondLevelService                                         │
│           ↓                                                     │
│  Repository Layer (Data Access)                               │
│  └── SupportCardRepository                                    │
│           ↓                                                     │
│  Database Layer (MySQL)                                       │
│  ├── support_cards table                                      │
│  ├── support_decks table                                      │
│  └── card_bonds table                                         │
│                                                                │
└────────────────────────────────────────────────────────────────┘
```

## Data Flow Diagrams

### 5.1 Deck Composition Flow

```
User selects 6 support cards
    ↓
DeckController::saveDeck()
    ↓
DeckCompositionService::validateAndSave()
    │
    ├─→ Validate exactly 6 cards selected
    ├─→ Check for duplicate cards (not allowed)
    ├─→ Validate card ownership
    ├─→ Calculate total deck bonus
    │   ├─→ Speed bonus aggregation
    │   ├─→ Stamina bonus aggregation
    │   ├─→ Power/Guts/Wit bonuses
    │   └─→ Special effect stacking
    ├─→ Save SupportDeck configuration
    └─→ Return deck summary with total bonuses
            ↓
Response with deck composition
```

### 5.2 Limit Break Flow

```
User upgrades support card
    ↓
SupportCardController::limitBreak()
    ↓
SupportCardService::applyLimitBreak()
    │
    ├─→ Validate card ownership
    ├─→ Check current limit break level (0-4)
    ├─→ Deduct required resources (duplicates/items)
    ├─→ Increment limit_break_level
    ├─→ Recalculate card bonuses
    │   ├─→ LB1: +5% stat bonuses
    │   ├─→ LB2: +10% stat bonuses
    │   ├─→ LB3: +15% stat bonuses
    │   ├─→ LB4: +20% stat bonuses
    └─→ Update SupportCard record
            ↓
        Return updated card with new bonuses
```

### 5.3 Bond Level Tracking Flow

```
Training session completes with support card
    ↓
TrainingService::updateBondLevels()
    ↓
BondLevelService::incrementBond()
    │
    ├─→ For each support card in training:
    │   ├─→ Award bond points (+5 per training)
    │   ├─→ Check if level threshold reached
    │   ├─→ If 80%+ → Unlock friendship training
    │   └─→ Update CardBond record
    └─→ Return bond status for all cards
            ↓
        Update UI with bond progress
```

## Implementation Tasks

### Task 5.1: Support Card Models (Week 1, ~10 hours)

- [ ] **5.1.1**: Create SupportCard model and migration
  - Fields: card_id, card_name, rarity, specialization, limit_break_level, stat_bonuses_json
  - Rarity: SSR, SR, R
  - Specialization: Speed, Stamina, Power, Guts, Wit, Friend
  - Unit tests: 4 tests
  - **Files**: `app/Models/SupportCard.php`, `database/migrations/*_create_support_cards_table.php`
  - **Effort**: 4 hours

- [ ] **5.1.2**: Create SupportDeck model and migration
  - Fields: character_id, card_1_id, card_2_id, ..., card_6_id, total_bonuses_json
  - Constraint: exactly 6 cards, no duplicates
  - Unit tests: 3 tests
  - **Files**: `app/Models/SupportDeck.php`, `database/migrations/*_create_support_decks_table.php`
  - **Effort**: 3 hours

- [ ] **5.1.3**: Create CardBond model and migration
  - Fields: character_id, support_card_id, bond_level, bond_points, friendship_unlocked
  - Bond calculation: 0-100%
  - Unit tests: 3 tests
  - **Files**: `app/Models/CardBond.php`, `database/migrations/*_create_card_bonds_table.php`
  - **Effort**: 3 hours

### Task 5.2: Deck Composition Service (Week 1-2, ~14 hours)

- [ ] **5.2.1**: Create DeckCompositionService
  - Validate deck composition (6 cards, no duplicates)
  - Save deck configuration
  - Retrieve active deck for character
  - Unit tests: 6 tests
  - **Files**: `app/Services/DeckCompositionService.php`
  - **Effort**: 6 hours

- [ ] **5.2.2**: Create SupportCardBonusCalculator
  - Aggregate stat bonuses from 6 cards
  - Apply limit break multipliers
  - Calculate special effect stacking
  - Unit tests: 7 tests
  - **Files**: `app/Services/SupportCardBonusCalculator.php`
  - **Effort**: 8 hours

### Task 5.3: Limit Break System (Week 2, ~10 hours)

- [ ] **5.3.1**: Create LimitBreakService
  - Validate limit break prerequisites
  - Calculate required resources
  - Apply level bonuses (LB0-LB4)
  - Recalculate card effectiveness
  - Unit tests: 6 tests
  - **Files**: `app/Services/LimitBreakService.php`
  - **Effort**: 7 hours

- [ ] **5.3.2**: Create limit break resource tracking
  - Track duplicate cards for breaking
  - Alternative: Use special items
  - **Files**: Integration with inventory system
  - **Effort**: 3 hours

### Task 5.4: Bond Level Service (Week 2, ~8 hours)

- [ ] **5.4.1**: Create BondLevelService
  - Increment bond points after training (+5 per session)
  - Level up calculation (thresholds: 20, 40, 60, 80, 100)
  - Unlock friendship training at 80%+
  - Unit tests: 5 tests
  - **Files**: `app/Services/BondLevelService.php`
  - **Effort**: 6 hours

- [ ] **5.4.2**: Integrate with training system
  - Auto-update bonds after each training
  - Track friendship training eligibility
  - Integration tests: 2 tests
  - **Effort**: 2 hours

### Task 5.5: API Layer (Week 3, ~12 hours)

- [ ] **5.5.1**: Create SupportCardController
  - GET /api/v1/support-cards (list all cards)
  - GET /api/v1/support-cards/{id} (card details)
  - PATCH /api/v1/support-cards/{id}/limit-break (upgrade)
  - GET /api/v1/characters/{id}/collection (owned cards)
  - **Files**: `app/Http/Controllers/API/SupportCardController.php`
  - **Effort**: 6 hours

- [ ] **5.5.2**: Create DeckController
  - GET /api/v1/characters/{id}/deck (active deck)
  - POST /api/v1/characters/{id}/deck (save deck)
  - DELETE /api/v1/characters/{id}/deck (clear deck)
  - GET /api/v1/characters/{id}/deck/bonuses (calculated bonuses)
  - **Files**: `app/Http/Controllers/API/DeckController.php`
  - **Effort**: 6 hours

### Task 5.6: Testing & Integration (Week 3, ~10 hours)

- [ ] **5.6.1**: Feature tests
  - Complete deck building flow
  - Limit break progression
  - Bond level advancement
  - Bonus calculation accuracy
  - Feature tests: 8 tests
  - **Files**: `tests/Feature/SupportCardManagementTest.php`
  - **Effort**: 6 hours

- [ ] **5.6.2**: Edge case testing
  - Duplicate card prevention
  - Invalid deck configurations (< 6 or > 6 cards)
  - Limit break at max level
  - Bond overflow handling
  - Unit tests: 6 tests
  - **Effort**: 4 hours

## Summary

**Total Effort**: ~64 hours (3 weeks)

**Total Tests**: 23+ (16 unit tests, 8 feature tests, 2 integration tests)

**Key Deliverables**:

- 3 services (DeckCompositionService, SupportCardBonusCalculator, BondLevelService, LimitBreakService)
- 2 controllers with 8 REST endpoints
- 3 database tables with migrations
- Complete limit break system (LB0-LB4)
- Bond level tracking with friendship training unlock
- Deck composition validation and bonus calculation

**Dependencies**:

- Requires SPEC-001 (Character Management) for character association
- Integrates with SPEC-002 (Training Optimization) for bonus application and bond updates
- Provides data to SPEC-004 (Skill Management) for skill hint provisioning
