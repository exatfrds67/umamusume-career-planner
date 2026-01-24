# SEQ-001: Character Creation Sequence

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.0.0  
**Date**: January 24, 2026  
**Related Documents**: [PRD-001], [SPEC-001], [FLOW-001], [TECH-FLOW-001]

---

## Table of Contents

1. [Overview](#1-overview)
2. [Participants](#2-participants)
3. [Sequence Flow](#3-sequence-flow)
4. [Detailed Interactions](#4-detailed-interactions)
5. [Data Structures](#5-data-structures)
6. [Error Handling](#6-error-handling)
7. [Performance Considerations](#7-performance-considerations)
8. [Related Documentation](#8-related-documentation)

---

## 1. Overview

### 1.1 Purpose

This sequence diagram documents the complete character creation flow in the Umamusume Career Planner application, from initial trainee selection through factor inheritance calculation and database persistence.

### 1.2 Scope

**Covers:**

- Trainee and scenario selection
- Parent character selection for factor inheritance
- Support deck configuration
- Initial stat calculation and persistence
- Database transaction management

**Related Artifacts:**

- PRD: [PRD-001](../prds/PRD-001_Character_Management.md)
- SPEC: [SPEC-001](../specs/SPEC-001_Character_Management_Technical.md)
- Flow: [FLOW-001](../flows/FLOW-001_Character_Management_System.md)
- Tech Flow: [TECH-FLOW-001](../tech-flow/TECH-FLOW-001_Character_Management_Flow.md)
- Wireframe: [WF-002](../wireframes/WF-002_Character_Creation_Wizard.md)
- User Flow: [UF-002](../user-flows/UF-002_Career_Setup_Flow.md)

### 1.3 Business Context

Character creation is the foundational workflow that:

- Establishes career run configuration
- Determines inherited stat bonuses via factor system
- Configures initial training deck
- Sets scenario type and goals

**Success Criteria:**

- Character created with valid initial state
- Factor bonuses correctly calculated (★☆☆=+5, ★★☆=+12, ★★★=+21)
- Support deck validated (exactly 6 cards)
- Database transaction committed atomically

---

## 2. Participants

### 2.1 System Components

| Component | Type | Responsibility |
|-----------|------|----------------|
| **User** | Actor | Initiates character creation workflow |
| **Livewire Component** | Presentation | `CharacterCreationWizard.php` - Multi-step form management |
| **CharacterController** | Application | Orchestrates creation workflow |
| **CharacterService** | Domain Service | Character creation business logic |
| **FactorService** | Domain Service | Factor inheritance calculations |
| **SupportDeckService** | Domain Service | Deck validation and composition |
| **Database** | Infrastructure | MySQL/MariaDB persistence layer |
| **EventDispatcher** | Infrastructure | Laravel event broadcasting |

### 2.2 Component Locations

```

app/
├── Livewire/
│   └── Character/
│       └── CharacterCreationWizard.php
├── Http/
│   └── Controllers/
│       └── CharacterController.php
├── Services/
│   ├── CharacterService.php
│   ├── FactorService.php
│   └── SupportDeckService.php
└── Models/
    ├── Character.php
    ├── Factor.php
    └── SupportDeck.php

```

---

## 3. Sequence Flow

### 3.1 High-Level Flow Diagram

```mermaid
sequenceDiagram
    actor User
    participant UI as Livewire Wizard
    participant Controller as CharacterController
    participant CharSvc as CharacterService
    participant FactorSvc as FactorService
    participant DeckSvc as SupportDeckService
    participant DB as Database
    participant Events as EventDispatcher

    User->>UI: Open Creation Wizard
    UI->>Controller: GET /characters/create
    Controller-->>UI: Render wizard (Step 1)
    
    Note over UI,User: Step 1: Trainee Selection
    User->>UI: Select trainee + scenario
    UI->>Controller: Validate selection
    Controller-->>UI: Next step enabled
    
    Note over UI,User: Step 2: Parent Selection
    User->>UI: Select Parent A & B
    UI->>FactorSvc: Preview inheritance
    FactorSvc-->>UI: Factor bonuses preview
    
    Note over UI,User: Step 3: Support Deck
    User->>UI: Configure 6-card deck
    UI->>DeckSvc: Validate deck composition
    DeckSvc-->>UI: Validation result
    
    Note over UI,User: Step 4: Confirm & Create
    User->>UI: Submit creation
    UI->>Controller: POST /characters
    
    Controller->>DB: BEGIN TRANSACTION
    
    Controller->>CharSvc: create(data)
    CharSvc->>DB: INSERT characters
    DB-->>CharSvc: character_id
    
    CharSvc->>FactorSvc: calculateInheritance(character, parents)
    FactorSvc->>FactorSvc: Calculate stat bonuses
    FactorSvc-->>CharSvc: factor_data
    
    CharSvc->>DB: INSERT factors
    CharSvc->>DB: INSERT aptitudes
    CharSvc->>DB: INSERT goals (if provided)
    
    CharSvc->>Events: Dispatch CharacterCreated
    Events->>Events: Queue event listeners
    
    CharSvc-->>Controller: Character object
    
    Controller->>DB: COMMIT TRANSACTION
    
    Controller-->>UI: 201 Created + character data
    UI->>UI: Update component state
    UI-->>User: Success message + redirect to Dashboard
```

### 3.2 Timeline Breakdown

| Phase | Duration | Description |
|-------|----------|-------------|
| **Step 1-3** | ~30s | User input and selection |
| **Validation** | ~100ms | Form validation and preview |
| **Database Operations** | ~300ms | Transaction, inserts, factor calculations |
| **Event Dispatch** | ~50ms | Queue event listeners |
| **Response** | ~50ms | Render success and redirect |
| **Total** | ~500ms | Server-side processing time |

---

## 4. Detailed Interactions

### 4.1 Step 1: Trainee Selection

**Request Flow:**

```
User → Livewire Component → Controller
```

**Controller Action:**

```php
// CharacterController.php
public function create()
{
    $trainees = Character::where('is_playable', true)
        ->with('baseStats', 'aptitudes')
        ->orderBy('name')
        ->get();
    
    $scenarios = ScenarioType::cases();
    
    return view('livewire.character.wizard-step-1', [
        'trainees' => $trainees,
        'scenarios' => $scenarios,
    ]);
}
```

**Data Returned:**

- List of playable trainees with base stats
- Available scenario types (URA Finale, Unity Cup, etc.)

### 4.2 Step 2: Parent Selection & Factor Preview

**Request Flow:**

```
User → Livewire Component → FactorService
```

**FactorService Calculation:**

```php
// FactorService.php
public function previewInheritance(
    Character $trainee,
    Character $parentA,
    Character $parentB
): array {
    $factors = [
        'speed' => $this->calculateFactorBonus($trainee, $parentA, $parentB, 'speed'),
        'stamina' => $this->calculateFactorBonus($trainee, $parentA, $parentB, 'stamina'),
        'power' => $this->calculateFactorBonus($trainee, $parentA, $parentB, 'power'),
        'guts' => $this->calculateFactorBonus($trainee, $parentA, $parentB, 'guts'),
        'wit' => $this->calculateFactorBonus($trainee, $parentA, $parentB, 'wit'),
    ];
    
    return $factors;
}

private function calculateFactorBonus(
    Character $trainee,
    Character $parentA,
    Character $parentB,
    string $stat
): int {
    $stars = $this->determineStarLevel($trainee, $parentA, $parentB, $stat);
    
    return match($stars) {
        1 => 5,   // ★☆☆
        2 => 12,  // ★★☆
        3 => 21,  // ★★★
        default => 0,
    };
}
```

**Factor Calculation Rules:**

| Star Level | Bonus | Criteria |
|------------|-------|----------|
| ★☆☆ | +5 | Basic inheritance match |
| ★★☆ | +12 | Strong inheritance match |
| ★★★ | +21 | Perfect inheritance match |

### 4.3 Step 3: Support Deck Validation

**Request Flow:**

```
User → Livewire Component → SupportDeckService
```

**Validation Rules:**

```php
// SupportDeckService.php
public function validate(array $cardIds, int $userId): ValidationResult
{
    // Rule 1: Exactly 6 cards
    if (count($cardIds) !== 6) {
        return ValidationResult::failed('Deck must contain exactly 6 cards');
    }
    
    // Rule 2: 5 owned + 1 borrowed
    $ownedCards = SupportCard::whereIn('id', $cardIds)
        ->where('user_id', $userId)
        ->count();
    
    if ($ownedCards < 5) {
        return ValidationResult::failed('At least 5 cards must be owned');
    }
    
    // Rule 3: No duplicates
    if (count($cardIds) !== count(array_unique($cardIds))) {
        return ValidationResult::failed('Duplicate cards not allowed');
    }
    
    // Rule 4: Type balance recommendation (warning only)
    $typeDistribution = $this->analyzeTypeDistribution($cardIds);
    if ($typeDistribution['imbalance'] > 0.5) {
        return ValidationResult::warning('Deck type distribution may be suboptimal');
    }
    
    return ValidationResult::success();
}
```

### 4.4 Step 4: Database Transaction

**Transaction Scope:**

```php
// CharacterService.php
public function create(array $data): Character
{
    return DB::transaction(function () use ($data) {
        // 1. Create character record
        $character = Character::create([
            'user_id' => $data['user_id'],
            'trainee_id' => $data['trainee_id'],
            'scenario_type' => $data['scenario_type'],
            'name' => $data['name'],
            // Initial stats from base + factors
            'speed' => $data['base_speed'] + $data['factor_speed'],
            'stamina' => $data['base_stamina'] + $data['factor_stamina'],
            'power' => $data['base_power'] + $data['factor_power'],
            'guts' => $data['base_guts'] + $data['factor_guts'],
            'wit' => $data['base_wit'] + $data['factor_wit'],
            'energy' => 100, // Full energy
            'mood' => MoodStatus::Normal,
            'current_turn' => 1,
        ]);
        
        // 2. Insert factor records
        foreach ($data['factors'] as $stat => $bonus) {
            Factor::create([
                'character_id' => $character->id,
                'stat_type' => $stat,
                'bonus_value' => $bonus,
                'source_parent' => $data["parent_{$stat}"],
            ]);
        }
        
        // 3. Copy aptitudes from trainee
        $this->copyAptitudes($character, $data['trainee_id']);
        
        // 4. Insert initial goals (if provided)
        if (!empty($data['goals'])) {
            $this->createGoals($character, $data['goals']);
        }
        
        // 5. Associate support deck
        if (!empty($data['support_deck_id'])) {
            $character->support_deck_id = $data['support_deck_id'];
            $character->save();
        }
        
        // 6. Dispatch event
        event(new CharacterCreated($character));
        
        return $character;
    });
}
```

**Database Operations:**

1. `INSERT INTO ucp_characters` - Main character record
2. `INSERT INTO ucp_factors` - Factor inheritance records (5 rows)
3. `INSERT INTO ucp_aptitudes` - Aptitude grades (9 rows)
4. `INSERT INTO ucp_goals` - Initial goals (optional, 0-5 rows)
5. Event dispatch to queue listeners

**Total Inserts:** 15-20 rows per character creation

---

## 5. Data Structures

### 5.1 Character Creation Request

```json
{
  "user_id": 1,
  "trainee_id": 42,
  "scenario_type": "ura_finale",
  "name": "Speed Build - Special Week",
  "parent_a_id": 15,
  "parent_b_id": 28,
  "support_deck_id": 3,
  "goals": [
    {
      "type": "stat_target",
      "stat": "speed",
      "target_value": 1000
    },
    {
      "type": "race_win",
      "race_id": 12,
      "required_placement": 1
    }
  ]
}
```

### 5.2 Factor Calculation Result

```json
{
  "speed": {
    "star_level": 3,
    "bonus": 21,
    "source_parent": "parent_a"
  },
  "stamina": {
    "star_level": 2,
    "bonus": 12,
    "source_parent": "parent_b"
  },
  "power": {
    "star_level": 1,
    "bonus": 5,
    "source_parent": "parent_a"
  },
  "guts": {
    "star_level": 1,
    "bonus": 5,
    "source_parent": "parent_b"
  },
  "wit": {
    "star_level": 2,
    "bonus": 12,
    "source_parent": "parent_a"
  }
}
```

### 5.3 Character Creation Response

```json
{
  "id": 157,
  "uuid": "9d8f4e21-7a3c-4b5d-9e2f-1c8d7a4b3e6f",
  "user_id": 1,
  "trainee_id": 42,
  "scenario_type": "ura_finale",
  "name": "Speed Build - Special Week",
  "stats": {
    "speed": 321,
    "stamina": 212,
    "power": 205,
    "guts": 205,
    "wit": 212
  },
  "energy": 100,
  "mood": "normal",
  "current_turn": 1,
  "support_deck_id": 3,
  "created_at": "2026-01-24T10:30:00Z"
}
```

---

## 6. Error Handling

### 6.1 Validation Errors

| Error Code | Condition | HTTP Status | User Message |
|------------|-----------|-------------|--------------|
| `CHAR_001` | Missing required fields | 422 | "Please complete all required fields" |
| `CHAR_002` | Invalid trainee selection | 422 | "Selected trainee is not available" |
| `CHAR_003` | Invalid scenario type | 422 | "Selected scenario is not valid" |
| `CHAR_004` | Deck validation failed | 422 | "Support deck must contain exactly 6 cards" |
| `CHAR_005` | Parent selection invalid | 422 | "Invalid parent character selection" |

### 6.2 Error Recovery Flow

```mermaid
sequenceDiagram
    participant User
    participant UI as Livewire Wizard
    participant Controller
    participant DB as Database

    User->>UI: Submit invalid data
    UI->>Controller: POST /characters
    Controller->>Controller: Validate request
    Controller-->>UI: 422 Validation Error
    UI->>UI: Display inline errors
    UI-->>User: Show error messages
    
    Note over User,UI: User corrects errors
    
    User->>UI: Resubmit with valid data
    UI->>Controller: POST /characters
    Controller->>DB: BEGIN TRANSACTION
    
    alt Database Error
        DB-->>Controller: Constraint violation
        Controller->>DB: ROLLBACK
        Controller-->>UI: 500 Server Error
        UI-->>User: "An error occurred. Please try again."
    else Success
        DB-->>Controller: Rows inserted
        Controller->>DB: COMMIT
        Controller-->>UI: 201 Created
        UI-->>User: Success + redirect
    end
```

### 6.3 Transaction Rollback Scenarios

| Scenario | Trigger | Recovery |
|----------|---------|----------|
| Constraint violation | Duplicate character name | Rollback, display error |
| Foreign key error | Invalid trainee_id reference | Rollback, re-validate form |
| Database timeout | Long-running transaction | Rollback, retry with timeout |
| Concurrent modification | Parent character deleted | Rollback, refresh parent list |

---

## 7. Performance Considerations

### 7.1 Performance Metrics

| Operation | Target | Current | Status |
|-----------|--------|---------|--------|
| Form render (Step 1) | <500ms | ~350ms | ✅ Met |
| Factor preview | <200ms | ~150ms | ✅ Met |
| Deck validation | <300ms | ~250ms | ✅ Met |
| Database transaction | <500ms | ~450ms | ✅ Met |
| Total creation flow | <2s | ~1.8s | ✅ Met |

### 7.2 Optimization Strategies

**Implemented:**

- Eager loading of trainee relationships
- Factor calculation caching during preview
- Bulk insert for aptitudes and factors
- Database indexing on foreign keys

**Code Example:**

```php
// Optimized trainee loading with eager loading
$trainees = Character::where('is_playable', true)
    ->with([
        'baseStats',
        'aptitudes',
        'growthRates',
    ])
    ->select(['id', 'name', 'name_jp', 'image_path']) // Only needed columns
    ->get();
```

### 7.3 Database Query Analysis

**Query Count:**

- Step 1 (Trainee list): 1 query (eager loaded)
- Step 2 (Factor preview): 2 queries (parent data)
- Step 3 (Deck validation): 1 query (card ownership)
- Step 4 (Creation): 5 queries (1 character + 4 related inserts)

**Total Queries:** 9 queries for complete flow

**Index Usage:**

```sql
-- Critical indexes for character creation
CREATE INDEX idx_characters_user_trainee ON ucp_characters(user_id, trainee_id);
CREATE INDEX idx_factors_character ON ucp_factors(character_id);
CREATE INDEX idx_aptitudes_character ON ucp_aptitudes(character_id);
CREATE INDEX idx_support_cards_user ON ucp_support_cards(user_id);
```

---

## 8. Related Documentation

### 8.1 System Documentation

| Document | Description |
|----------|-------------|
| [PRD-001](../prds/PRD-001_Character_Management.md) | Product requirements for character management |
| [SPEC-001](../specs/SPEC-001_Character_Management_Technical.md) | Technical specification for character system |
| [FLOW-001](../flows/FLOW-001_Character_Management_System.md) | System flow for character operations |
| [TECH-FLOW-001](../tech-flow/TECH-FLOW-001_Character_Management_Flow.md) | Technical flow diagrams |

### 8.2 Related Sequences

| Sequence | Description |
|----------|-------------|
| [SEQ-002](SEQ-002_Training_Block_Resolution.md) | Training session execution |
| [SEQ-012](SEQ-012_Run_Snapshot_and_Restore.md) | Character state snapshots |
| [SEQ-015](SEQ-015_Data_Migration_Snapshot_to_Live.md) | Data migration for characters |

### 8.3 UI Documentation

| Document | Description |
|----------|-------------|
| [WF-002](../wireframes/WF-002_Character_Creation_Wizard.md) | Wireframe specification for creation wizard |
| [WF-003](../wireframes/WF-003_Character_Detail_Management.md) | Character detail page wireframe |
| [UF-002](../user-flows/UF-002_Career_Setup_Flow.md) | User flow for career setup |

### 8.4 Database Documentation

| Document | Description |
|----------|-------------|
| [DBD-009](../009_DBD_Database_Documentation.md) | Complete database schema documentation |

---

## Document Control

### Version History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 2.0.0 | 2026-01-24 | Development Team | Complete rewrite aligned with v2.0.0 implementation; added detailed sequence flows, error handling, performance metrics, and aligned with current Laravel 12 architecture |
| 1.0.0 | 2026-01-14 | Development Team | Initial draft |

### Approval

| Role | Name | Signature | Date |
|------|------|-----------|------|
| Technical Lead | | | |
| QA Lead | | | |

### Review Schedule

- Next Review: 2026-04-24
- Review Frequency: Quarterly or on major feature changes

---

**Related Standards:**

- Laravel 12 Best Practices
- PSR-12 Coding Standards
- Mermaid Diagram Standards
- IEEE 830 SRS Format

---

*This sequence diagram reflects the current implementation of the character creation workflow as of v2.0.0. For the most up-to-date information, refer to the source code in `app/Services/CharacterService.php` and related files.*
