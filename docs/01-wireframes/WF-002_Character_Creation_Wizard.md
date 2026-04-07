# WF-002: Character Creation Wizard

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.4.1
**Date**: March 10, 2026
**Related Documents**: [PRD-001], [SPEC-001], [FLOW-001], [SEQ-001]

**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirement 1: Character State Management)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Character Creation UI)

**Related Artifacts**:

- PRD: [PRD-001](../02-prds/PRD-001_Character_Management.md)
- SPEC: [SPEC-001](../02-specs/SPEC-001_Character_Management_Technical.md)
- Flow: [FLOW-001](../01-flows/FLOW-001_Character_Management_System.md)
- Tech Flow: [TECH-FLOW-001](../01-tech-flow/TECH-FLOW-001_Character_Management_Flow.md)
- Sequences: [SEQ-001](../01-sequences/SEQ-001_Character_Creation_Sequence.md)
- User Flows: [UF-002](../01-user-flows/UF-002_Career_Setup_Flow.md)
- Related WF: [WF-003](WF-003_Character_Detail_Management.md), [WF-001](WF-001_Dashboard_Overview.md)

> **Alignment Note (March 2026)**: This wireframe has been reviewed for storage-mode consistency, navigation-surface accuracy, and accessibility contract completeness. Component class names, route paths, and service calls shown in code blocks are **illustrative contracts**; verify against the current implementation before use.

---

## 1. Overview

### 1.1 Purpose

The Character Creation Wizard guides users through a multi-step process to create a new career run,
configuring character selection, parent inheritance, support deck composition, and initial settings.
This wizard implements the complete character initialization workflow as defined in SPEC-001.

### 1.2 Key Objectives

| Objective | Description |
| --- | --- |
| **Guided Configuration** | Step-by-step wizard reduces complexity of character setup |
| **Parent Inheritance** | Visual factor preview shows stat bonuses before confirmation |
| **Deck Validation** | Real-time validation ensures valid 6-card support deck |
| **Preview & Review** | Final review step shows all selections before character creation |
| **Error Prevention** | Inline validation prevents invalid configurations |

### 1.3 User Stories

| ID | User Story | Priority |
| --- | --- | --- |
| US-001 | As a player, I want to select my trainee character with clear stat/aptitude information | P0 |
| US-002 | As a player, I want to choose parent characters and see inherited factor bonuses | P0 |
| US-003 | As a player, I want to build my support deck with validation feedback | P0 |
| US-004 | As a player, I want to review all my selections before finalizing the character | P0 |
| US-005 | As a player, I want scenario-specific configuration options | P1 |

---

### 1.4 Storage Mode Support

| Mode | Behavior |
| --- | --- |
| **Local Mode** | Wizard steps held in browser; character persisted as local UUID-keyed record on confirmation |
| **Account Mode** | Wizard state backed by server session; character persisted to database on confirmation |

Both modes redirect to the character detail view on success. Step validation runs identically in both modes.

### 1.5 Navigation Surface

| Surface | Description |
| --- | --- |
| **Entry point** | Conceptual: character creation route (authenticated account mode or local guest flow) |
| **Success redirect** | Character detail view |
| **Cancel / Exit** | Returns to dashboard or character list |

### 1.6 Screen Variants

| Variant | Trigger | Notes |
| --- | --- | --- |
| **Standard (Account)** | Authenticated user | Full persistence on confirmation |
| **Local (Guest)** | Unauthenticated user | Browser-backed; no server call on each step |
| **Mid-Session Recovery** | Page refresh during wizard | Draft state restoration from local store |

---

## 2. Wizard Flow Architecture

### 2.1 Overall Wizard Flow

```mermaid
flowchart TD
    Start([Start Character Creation]) --> Step1[Step 1: Trainee & Scenario]
    Step1 --> Validate1{Valid Selection?}
    Validate1 -->|No| Step1
    Validate1 -->|Yes| Step2[Step 2: Parent Selection]
    Step2 --> Validate2{Valid Parents?}
    Validate2 -->|No| Step2
    Validate2 -->|Yes| Step3[Step 3: Support Deck]
    Step3 --> Validate3{Valid Deck?}
    Validate3 -->|No| Step3
    Validate3 -->|Yes| Step4[Step 4: Review & Confirm]
    Step4 --> Confirm{Confirm Creation?}
    Confirm -->|No| Step1
    Confirm -->|Yes| Create[Create Character]
    Create --> Initialize[Initialize Stats/Mood/Energy]
    Initialize --> Save[Save to Database/localStorage]
    Save --> Redirect[Redirect to Character Dashboard]
    Redirect --> End([Character Created])
```text

### 2.2 Step Sequence

```mermaid
sequenceDiagram
    participant User
    participant Wizard
    participant Validation
    participant CharacterStateService
    participant Database

    User->>Wizard: Start wizard
    Wizard->>User: Show Step 1
    User->>Wizard: Select trainee
    Wizard->>Validation: Validate selection
    Validation-->>Wizard: Valid
    Wizard->>User: Show Step 2
    User->>Wizard: Select parents
    Wizard->>Validation: Calculate factors
    Validation-->>Wizard: Factor preview
    Wizard->>User: Show Step 3
    User->>Wizard: Build support deck
    Wizard->>Validation: Validate deck
    Validation-->>Wizard: Deck valid
    Wizard->>User: Show Step 4 (Review)
    User->>Wizard: Confirm creation
    Wizard->>CharacterStateService: createCharacter()
    CharacterStateService->>Database: Save character
    Database-->>CharacterStateService: Character ID
    CharacterStateService-->>Wizard: Success
    Wizard->>User: Redirect to dashboard
```

### 2.3 State Management

| State Property | Type | Description |
| --- | --- | --- |
| `currentStep` | `int` | Current wizard step (1-4) |
| `traineeId` | `int` | Selected trainee character ID |
| `scenarioType` | `enum` | Scenario type (URA Finals or Unity Cup) |
| `parentA` | `object` | Parent A character data |
| `parentB` | `object` | Parent B character data |
| `supportDeck` | `array` | Array of 6 support card objects |
| `factorPreview` | `object` | Calculated factor bonuses |
| `validationErrors` | `array` | Current validation errors |

---

## 3. Step 1: Trainee & Scenario Selection

### 3.1 Desktop Layout (≥1024px)

```text
┌──────────────────────────────────────────────────────────────────────┐
│ Character Creation Wizard                                     [✕]    │
├──────────────────────────────────────────────────────────────────────┤
│                                                                      │
│ Step 1 of 4: Select Trainee & Scenario                              │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│                                                                      │
│ ┌──────────────────────────────────────────────────────────────┐   │
│ │ Select Your Trainee                                           │   │
│ │ ┌────────────────────────────────────────────────────────┐   │   │
│ │ │ 🔍 Search trainees by name...                          │   │   │
│ │ └────────────────────────────────────────────────────────┘   │   │
│ │                                                               │   │
│ │ Rarity Filter: [☑ SSR] [☑ SR] [☐ R]                          │   │
│ │ Distance Filter: [All ▼]                                      │   │
│ │ Surface Filter: [All ▼]                                       │   │
│ │                                                               │   │
│ │ ┌─────────────────┬─────────────────┬─────────────────────┐ │   │
│ │ │ Mejiro Ardan    │ Kitasan Black   │ Tokai Teio          │ │   │
│ │ │ ┌─────────────┐ │ ┌─────────────┐ │ ┌─────────────────┐ │ │   │
│ │ │ │  [Portrait] │ │ │  [Portrait] │ │ │  [Portrait]     │ │ │   │
│ │ │ └─────────────┘ │ └─────────────┘ │ └─────────────────┘ │ │   │
│ │ │ SSR · Speed     │ SSR · Power     │ SSR · Stamina       │ │   │
│ │ │                 │                 │                     │ │   │
│ │ │ Base Stats:     │ Base Stats:     │ Base Stats:         │ │   │
│ │ │ Speed: 90       │ Speed: 70       │ Speed: 80           │ │   │
│ │ │ Stamina: 70     │ Stamina: 85     │ Stamina: 95         │ │   │
│ │ │ Power: 75       │ Power: 90       │ Power: 70           │ │   │
│ │ │                 │                 │                     │ │   │
│ │ │ Growth Rates:   │ Growth Rates:   │ Growth Rates:       │ │   │
│ │ │ Speed: +20%     │ Speed: +10%     │ Speed: +10%         │ │   │
│ │ │ Stamina: +10%   │ Stamina: +20%   │ Stamina: +20%       │ │   │
│ │ │                 │                 │                     │ │   │
│ │ │ [SELECT]        │ [SELECT]        │ [SELECT]            │ │   │
│ │ └─────────────────┴─────────────────┴─────────────────────┘ │   │
│ │                                                               │   │
│ │ [Load More] (12 of 56 shown)                                 │   │
│ └───────────────────────────────────────────────────────────────┘   │
│                                                                      │
│ ┌──────────────────────────────────────────────────────────────┐   │
│ │ Select Scenario                                               │   │
│ │ ┌────────────────────────────────────────────────────────┐   │   │
│ │ │ [○ URA Finals]                                         │   │   │
│ │ │    Standard training scenario with balanced planning   │   │   │
│ │ │                                                        │   │   │
│ │ │ [○ Unity Cup]                                          │   │   │
│ │ │    Team-based scenario with Unity Cup events          │   │   │
│ │ └────────────────────────────────────────────────────────┘   │   │
│ └───────────────────────────────────────────────────────────────┘   │
│                                                                      │
│ ┌──────────────────────────────────────────────────────────────┐   │
│ │ Selected: Mejiro Ardan (SSR)                                  │   │
│ │ Scenario: URA Finals                                           │   │
│ └───────────────────────────────────────────────────────────────┘   │
│                                                                      │
│                                         [Cancel] [Next: Parents →] │
└──────────────────────────────────────────────────────────────────────┘
```

### 3.2 Tablet Layout (640px-1024px)

```text
┌────────────────────────────────────────────────────┐
│ Character Creation - Step 1 of 4            [✕]   │
├────────────────────────────────────────────────────┤
│ Progress: ▓▓▓▓░░░░░░░░ 25%                        │
├────────────────────────────────────────────────────┤
│                                                    │
│ Select Trainee                                     │
│ ┌��─────────────────────────────────────────────┐  │
│ │ 🔍 Search...                                  │  │
│ └──────────────────────────────────────────────┘  │
│                                                    │
│ Filters: [SSR ✓] [SR ✓] [R ✗]                     │
│                                                    │
│ ┌──────────────────┬──────────────────────────┐   │
│ │ Mejiro Ardan     │ Kitasan Black            │   │
│ │ [Portrait]       │ [Portrait]               │   │
│ │ SSR · Speed      │ SSR · Power              │   │
│ │ [SELECT]         │ [SELECT]                 │   │
│ └──────────────────┴──────────────────────────┘   │
│                                                    │
│ [Show More ▼]                                      │
│                                                    │
│ ┌──────────────────────────────────────────────┐  │
│ │ Scenario Selection                            │  │
│ │ [URA Finals ▼]                                │  │
│ └──────────────────────────────────────────────┘  │
│                                                    │
│ ┌──────────────────────────────────────────────┐  │
│ │ ✓ Mejiro Ardan selected                       │  │
│ │ ✓ URA Championship Finals                     │  │
│ └──────────────────────────────────────────────┘  │
│                                                    │
│                       [Cancel] [Next →]           │
└────────────────────────────────────────────────────┘
```

### 3.3 Mobile Layout (<640px)

```text
┌──────────────────────────────┐
│ Step 1 of 4            [✕]  │
├──────────────────────────────┤
│ ▓▓▓░░░░░░░░░ 25%             │
├──────────────────────────────┤
│                              │
│ Select Trainee               │
│ ┌──────────────────────────┐ │
│ │ 🔍 Search...              │ │
│ └──────────────────────────┘ │
│                              │
│ [SSR ✓] [SR ✓] [R ✗]        │
│                              │
│ ┌──────────────────────────┐ │
│ │ Mejiro Ardan             │ │
│ │ ┌──────────────────────┐ │ │
│ │ │    [Portrait]        �� │ │
│ │ └──────────────────────┘ │ │
│ │ SSR · Speed              │ │
│ │ Speed: 90 | Sta: 70      │ │
│ │                          │ │
│ │ [SELECT]                 │ │
│ └──────────────────────────┘ │
│                              │
│ ┌──────────────────────────┐ │
│ │ Kitasan Black            │ │
│ │ [Portrait]               │ │
│ │ SSR · Power              │ │
│ │ [SELECT]                 │ │
│ └──────────────────────────┘ │
│                              │
│ [Load More ▼]                │
│                              │
│ Scenario:                    │
│ [URA Finals ▼]               │
│                              │
│ ┌──────────────────────────┐ │
│ │ Selected:                 │ │
│ │ Mejiro Ardan              │ │
│ │ URA Finals                │ │
│ └──────────────────────────┘ │
│                              │
│ [Cancel] [Next →]            │
└──────────────────────────────┘
```

### 3.4 Component Specifications

#### 3.4.1 Trainee Card Component

**Component**: `resources/views/components/trainee-card.blade.php`

```blade
<div class="trainee-card" data-testid="trainee-card-{{ $trainee->id }}">
    <div class="trainee-card__portrait">
        <img src="{{ $trainee->image_path }}" alt="{{ $trainee->name }}" />
    </div>

    <div class="trainee-card__header">
        <h3 class="trainee-card__name">{{ $trainee->name }}</h3>
        <span class="trainee-card__rarity badge-{{ strtolower($trainee->rarity) }}">
            {{ $trainee->rarity }}
        </span>
        <span class="trainee-card__specialization">{{ $trainee->specialization }}</span>
    </div>

    <div class="trainee-card__stats">
        <div class="stat-row">
            <span class="stat-label">Speed:</span>
            <span class="stat-value">{{ $trainee->base_speed }}</span>
        </div>
        <div class="stat-row">
            <span class="stat-label">Stamina:</span>
            <span class="stat-value">{{ $trainee->base_stamina }}</span>
        </div>
        <div class="stat-row">
            <span class="stat-label">Power:</span>
            <span class="stat-value">{{ $trainee->base_power }}</span>
        </div>
    </div>

    <div class="trainee-card__growth">
        <h4>Growth Rates</h4>
        @foreach(['speed', 'stamina', 'power', 'guts', 'wit'] as $stat)
            @if($trainee->{"growth_{$stat}"} > 0)
                <div class="growth-row">
                    <span>{{ ucfirst($stat) }}:</span>
                    <span class="growth-value">+{{ $trainee->{"growth_{$stat}"} }}%</span>
                </div>
            @endif
        @endforeach
    </div>

    <button
        class="btn btn-primary trainee-card__select"
        wire:click="selectTrainee({{ $trainee->id }})"
        data-testid="select-trainee-{{ $trainee->id }}"
    >
        Select
    </button>
</div>
```text

#### 3.4.2 Search and Filter Component

**Component**: `app/Livewire/CharacterCreation/TraineeSelector.php`

```php
class TraineeSelector extends Component
{
    public $search = '';
    public $rarityFilters = ['SSR' => true, 'SR' => true, 'R' => false];
    public $distanceFilter = 'all';
    public $surfaceFilter = 'all';

    public $selectedTraineeId = null;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function toggleRarity($rarity)
    {
        $this->rarityFilters[$rarity] = !$this->rarityFilters[$rarity];
        $this->resetPage();
    }

    public function selectTrainee($traineeId)
    {
        $this->selectedTraineeId = $traineeId;
        $this->emit('traineeSelected', $traineeId);
    }

    public function getTraineesProperty()
    {
        return Character::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('name_jp', 'like', "%{$this->search}%");
            })
            ->when(array_filter($this->rarityFilters), function ($query) {
                $query->whereIn('rarity', array_keys(array_filter($this->rarityFilters)));
            })
            ->when($this->distanceFilter !== 'all', function ($query) {
                $query->whereHas('aptitudes', function ($q) {
                    $q->where('distance_type', $this->distanceFilter)
                      ->where('grade', '>=', 'B');
                });
            })
            ->orderBy('rarity', 'desc')
            ->orderBy('name')
            ->paginate(12);
    }

    public function render()
    {
        return view('livewire.character-creation.trainee-selector', [
            'trainees' => $this->trainees,
        ]);
    }
}
```

### 3.5 Validation Rules

| Field | Rule | Error Message |
| --- | --- | --- |
| `trainee_id` | Required, exists | "Please select a trainee character" |
| `scenario_type` | Required, valid enum | "Please select a valid scenario" |

### 3.6 Accessibility

| Element | ARIA Attribute | Purpose |
| --- | --- | --- |
| Search input | `aria-label="Search trainee characters"` | Screen reader description |
| Filter checkboxes | `aria-checked` | Checkbox state |
| Trainee cards | `role="button"` | Interactive card |
| Selected card | `aria-selected="true"` | Selection indicator |

---

## 4. Step 2: Parent Selection & Factor Inheritance

### 4.1 Desktop Layout (≥1024px)

```text
┌─────────────────��────────────────────────────────────────────────────┐
│ Character Creation Wizard                                     [✕]    │
├──────────────────────────────────────────────────────────────────────┤
│                                                                      │
│ Step 2 of 4: Select Parents & Factor Inheritance                    │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│                                                                      │
│ ┌──────────────────────────────────────────────────────────────┐   │
│ │ Main Parents (Choose 2)                                       │   │
│ │                                                               │   │
│ │ ┌─────────────────────────┬─────────────────────────────┐   │   │
│ │ │ Parent A                │ Parent B                    │   │   │
│ │ ├─────────────────────────┼─────────────────────────────┤   │   │
│ │ │ ┌─────────────────────┐ │ ┌─────────────────────────┐ │   │   │
│ │ │ │   [Portrait]        │ │ │   [Portrait]            │ │   │   │
│ │ │ │   Kitasan Black     │ │ │   Mejiro McQueen        │ │   │   │
│ │ │ └─────────────────────┘ │ └─────────────────────────┘ │   │   │
│ │ │                         │                             │   │   │
│ │ │ Name: Kitasan Black     │ Name: Mejiro McQueen        │   │   │
│ │ │ Rarity: SSR             │ Rarity: SSR                 │   │   │
│ │ │                         │                             │   │   │
│ │ │ [CHOOSE PARENT A]       │ [CHOOSE PARENT B]           │   │   │
│ │ │                         │                             │   │   │
│ │ │ Affinity: ◎ (100%)      │ Affinity: ◎ (100%)          │   │   │
│ │ │                         │                             │   │   │
│ │ │ Factor Contributions:   │ Factor Contributions:       │   │   │
│ │ │ • Speed: ★★☆ (+12)      │ • Stamina: ★★★ (+21)        │   │   │
│ │ │ • Power: ★☆☆ (+5)       │ • Wit: ★★☆ (+12)            │   │   │
│ │ └─────────────────────────┴─────────────────────────────┘   │   │
│ └───────────────────────────────────────────────────────────────┘   │
│                                                                      │
│ ┌──────────────────────────────────────────────────────────────┐   │
│ │ Inheritance Preview                                           │   │
│ │                                                               │   │
│ │ Combined Stat Factors:                                        │   │
│ │ ┌────────────────────────────────────────────────────────┐   │   │
│ │ │ Speed:   ★★☆   (+12 from Parent A)                     │   │   │
│ │ │ Stamina: ★★★   (+21 from Parent B)                     │   │   │
│ │ │ Power:   ★☆☆   (+5 from Parent A)                      │   │   │
│ │ │ Guts:    ☆☆☆   (No bonus)                              │   │   │
│ │ │ Wit:     ★★☆   (+12 from Parent B)                     │   │   │
│ │ └──────────────────���─────────────────────────────────────┘   │   │
│ │                                                               │   │
│ │ Growth Rate Bonuses:                                          │   │
│ │ ┌────────────────────────────────────────────────────────┐   │   │
│ │ │ Speed:   +20% (Base) + 10% (Factor) = +30%             │   │   │
│ │ │ Stamina: +10% (Base) + 15% (Factor) = +25%             │   │   │
│ │ │ Power:   +15% (Base) + 5% (Factor) = +20%              │   │   │
│ │ │ Guts:    +10% (Base) + 0% (Factor) = +10%              │   │   │
│ │ │ Wit:     +5% (Base) + 10% (Factor) = +15%              │   │   │
│ │ └────────────────────────────────────────────────────────┘   │   │
│ │                                                               │   │
│ │ Aptitude Bonuses:                                             │   │
│ │ • Mile Distance: No change (already A+)                       │   │
│ │ • Turf Surface: +1 grade (B → A)                              │   │
│ │ • Late Surger Style: No change (already S)                    │   │
│ │                                                               │   │
│ │ 💡 Tip: Choose parents with complementary factor strengths    │   │
│ └───────────────────────────────────────────────────────────────┘   │
│                                                                      │
│                                    [← Back] [Next: Support Deck →] │
└──────────────────────────────────────────────────────────────────────┘
```

### 4.2 Tablet Layout (640px-1024px)

```text
┌────────────────────────────────────────────────────┐
│ Character Creation - Step 2 of 4            [✕]   │
├────────────────────────────────────────────────────┤
│ Progress: ▓▓▓▓▓▓░░░░░░ 50%                        │
├────────────────────────────────────────────────────┤
│                                                    │
│ Select Parents                                     │
│                                                    │
│ ┌──────────────────────────────────────────────┐  │
│ │ Parent A                                      │  │
│ │ ┌──────────────────────────────────────────┐ │  │
│ │ │        [Portrait]                        │ │  │
│ │ │     Kitasan Black (SSR)                  │ │  │
│ │ └──────────────────────────────────────────┘ │  │
│ │                                              │  │
│ │ [CHOOSE PARENT A]                            │  │
│ │                                              │  │
│ │ Factors:                                     │  │
│ │ • Speed: ★★☆ (+12)                           │  │
│ │ • Power: ★☆☆ (+5)                            │  │
│ └──────────────────────────────────────────────┘  │
│                                                    │
│ ┌──────────────────────────────────────────────┐  │
│ │ Parent B                                      │  │
│ │ [Portrait] Mejiro McQueen (SSR)              │  │
│ │ [CHOOSE PARENT B]                            │  │
│ │                                              │  │
│ │ Factors:                                     │  │
│ │ • Stamina: ★★★ (+21)                         │  │
│ │ • Wit: ★★☆ (+12)                             │  │
│ └──────────────────────────────────────────────┘  │
│                                                    │
│ ┌──────────────────────────────────────────────┐  │
│ │ Inheritance Preview                           │  │
│ │ [Expand ▼]                                    │  │
│ └──────────────────────────────────────────────┘  │
│                                                    │
│                       [← Back] [Next →]           │
└────────────────────────────────────────────────────┘
```

### 4.3 Mobile Layout (<640px)

```text
┌──────────────────────────────┐
│ Step 2 of 4            [✕]  │
├──────────────────────────────┤
│ ▓▓▓▓▓▓░░░░░░ 50%             │
├──────────────────────────────┤
│                              │
│ Parent A                     │
│ ┌──────────────────────────┐ │
│ │    [Portrait]            │ │
│ │  Kitasan Black           │ │
│ │  SSR                     │ │
│ └──────────────────────────┘ │
│                              │
│ [CHOOSE PARENT A]            │
│                              │
│ Factors:                     │
│ • Speed: ★★☆ (+12)           │
│ • Power: ★☆☆ (+5)            │
│                              │
│ ─────────────────────────────│
│                              │
│ Parent B                     │
│ ┌──────────────────────────┐ │
│ │    [Portrait]            │ │
│ │  Mejiro McQueen          │ │
│ │  SSR                     │ │
│ └──────────────────────────┘ │
│                              │
│ [CHOOSE PARENT B]            │
│                              │
│ Factors:                     │
│ • Stamina: ★★★ (+21)         │
│ • Wit: ★★☆ (+12)             │
│                              │
│ ┌──────────────────────────┐ │
│ │ Preview [Expand ▼]        │ │
│ └──────────────────────────┘ │
│                              │
│ [← Back] [Next →]            │
└──────────────────────────────┘
```

### 4.4 Factor Calculation Logic

**Service**: `app/Services/FactorService.php`

```php
class FactorService
{
    public function calculateInheritance(Character $trainee, Character $parentA, Character $parentB): array
    {
        return [
            'stat_factors' => $this->calculateStatFactors($trainee, $parentA, $parentB),
            'growth_bonuses' => $this->calculateGrowthBonuses($parentA, $parentB),
            'aptitude_upgrades' => $this->calculateAptitudeUpgrades($trainee, $parentA, $parentB),
        ];
    }

    private function calculateStatFactors(Character $trainee, Character $parentA, Character $parentB): array
    {
        $factors = [];

        foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat) {
            $parentAFactor = $this->getParentFactor($parentA, $stat);
            $parentBFactor = $this->getParentFactor($parentB, $stat);

            // Take the higher factor from both parents
            $bestFactor = max($parentAFactor, $parentBFactor);
            $source = $parentAFactor > $parentBFactor ? 'parentA' : 'parentB';

            $factors[$stat] = [
                'stars' => $bestFactor,
                'bonus' => $this->factorToBonus($bestFactor),
                'source' => $source,
            ];
        }

        return $factors;
    }

    private function factorToBonus(int $stars): int
    {
        return match($stars) {
            1 => 5,
            2 => 12,
            3 => 21,
            default => 0,
        };
    }

    private function calculateGrowthBonuses(Character $parentA, Character $parentB): array
    {
        $bonuses = [];

        foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat) {
            $parentAGrowth = $parentA->{"growth_{$stat}"} ?? 0;
            $parentBGrowth = $parentB->{"growth_{$stat}"} ?? 0;

            // Average growth bonuses from both parents, divided by 2
            $bonuses[$stat] = round(($parentAGrowth + $parentBGrowth) / 2);
        }

        return $bonuses;
    }
}
```text

### 4.5 Factor Rating System

| Stars | Symbol | Stat Bonus | Growth Bonus |
| --- | --- | --- | --- |
| 0 | ☆☆☆ | +0 | +0% |
| 1 | ★☆☆ | +5 | +5% |
| 2 | ★★☆ | +12 | +10% |
| 3 | ★★★ | +21 | +15% |

### 4.6 Aptitude Grade System (Game-Accurate)

**Grade Scale** (S is Maximum - No SS exists):

| Grade | Color | Description |
| --- | --- | --- |
| S | Gold | Maximum aptitude grade |
| A | Purple | Excellent aptitude |
| B | Blue | Good aptitude |
| C | Green | Average aptitude |
| D | Yellow | Below average aptitude |
| E | Orange | Poor aptitude |
| F | Red | Very poor aptitude |
| G | Gray | Lowest aptitude grade |

**Aptitude Categories**:

- **Distance**: Sprint, Mile, Medium, Long
- **Surface**: Turf, Dirt
- **Running Style**: Nige (Leader), Senkou (Betweener), Sashi (Chaser), Oikomi (Last Spurt)

### 4.7 Validation Rules

| Field | Rule | Error Message |
| --- | --- | --- |
| `parent_a_id` | Required, exists, different from parent_b | "Please select Parent A" |
| `parent_b_id` | Required, exists, different from parent_a | "Please select Parent B" |

---

## 5. Step 3: Support Deck Configuration

### 5.1 Desktop Layout (≥1024px)

```
┌──────────────────────────────────────────────────────────────────────┐
│ Character Creation Wizard                                     [✕]    │
├──────────────────────────────────────────────────────────────────────┤
│                                                                      │
│ Step 3 of 4: Configure Support Deck                                 │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│                                                                      │
│ ┌──────────────────────────────────────────────────────────────┐   │
│ │ Support Deck (6 Cards Required)                               │   │
│ │ Current: 5/6 cards selected | Deck Score: 88/100              │   │
│ │                                                               │   │
│ │ ┌─────────────────┬─────────────────┬─────────────────────┐ │   │
│ │ │ Slot 1: Speed   │ Slot 2: Speed   │ Slot 3: Stamina     │ │   │
│ │ ├─────────────────┼─────────────────┼─────────────────────┤ │   │
│ │ │ Mejiro Dober    │ Tokai Teio      │ Kitasan Black       │ │   │
│ │ │ [Portrait]      │ [Portrait]      │ [Portrait]          │ │   │
│ │ │ SSR · Power     │ SSR · Speed     │ SSR · Stamina       │ │   │
│ │ │ Meta: S         │ Meta: SS        │ Meta: S             │ │   │
│ │ │ LB: 4/4 ★★★★   │ LB: 2/4 ★★☆☆   │ LB: 4/4 ★★★★       │ │   │
│ │ │ [CHANGE]        │ [CHANGE]        │ [CHANGE]            │ │   │
│ │ └─────────────────┴─────────────────┴─────────────────────┘ │   │
│ │                                                               │   │
│ │ ┌─────────────────┬─────────────────┬─────────────────────┐ │   │
│ │ │ Slot 4: Wit     │ Slot 5: Guts    │ Slot 6: Friend      │ │   │
│ │ ├─────────────────┼─────────────────┼─────────────────────┤ │   │
│ │ │ Narita Brian    │ Symboli Rudolf  │ [Empty Slot]        │ │   │
│ │ │ [Portrait]      │ [Portrait]      │                     │ │   │
│ │ │ SSR · Wit       │ SSR · Guts      │ Select a friend     │ │   │
│ │ │ Meta: A         │ Meta: B         │ support card        │ │   │
│ │ │ LB: 3/4 ★★★☆   │ LB: 2/4 ★★☆☆   │                     │ │   │
│ │ │ [CHANGE]        │ [CHANGE]        │ [SELECT FRIEND]     │ │   │
│ │ └─────────────────┴─────────────────┴─────────────────────┘ │   │
│ └───────────────────────────────────────────────────────────────┘   │
│                                                                      │
│ ┌──────────────────────────────────────────────────────────────┐   │
│ │ Deck Analysis                                                 │   │
│ │                                                               │   │
│ │ Type Distribution:                                            │   │
│ │ ┌────────────────────────────────────────────────────────┐   │   │
│ │ │ Speed:   2 cards ✓                                     │   │   │
│ │ │ Stamina: 1 card  ✓                                     │   │   │
│ │ │ Power:   1 card  ✓                                     │   │   │
│ │ │ Guts:    1 card  ✓                                     │   │   │
│ │ │ Wit:     1 card  ✓                                     │   │   │
│ │ │ Friend:  0 cards ⚠️ (Required for friendship bonuses)  │   │   │
│ │ └────────────────────────────────────────────────────────┘   │   │
│ │                                                               │   │
│ │ Deck Quality:                                                 │   │
│ │ • Meta cards: 4/5 (80%)                                       │   │
│ │ • Average limit break: 3.0 stars                              │   │
│ │ • Synergy score: 88/100                                       │   │
│ │                                                               │   │
│ │ Recommendations:                                              │   │
│ │ ⚠️ Add a friend support card for friendship training bonuses  │   │
│ │ 💡 Consider swapping Symboli Rudolf for a higher meta card    │   │
│ └───────────────────────────────────────────────────────────────┘   │
│                                                                      │
│                                    [← Back] [Next: Review →]        │
└──────────────────────────────────────────────────────────────────────┘
```text

### 5.2 Card Selection Modal

```
┌──────────────────────────────────────────────────────────────────────┐
│ Select Support Card for Slot 6 (Friend)                      [✕]    │
├──────────────────────────────────────────────────────────────────────┤
│                                                                      │
│ ┌──────────────────────────────────────────────────────────────┐   │
│ │ 🔍 Search cards...                                            │   │
│ └──────────────────────────────────────────────────────────────┘   │
│                                                                      │
│ Filter: [All Types ▼] [All Rarity ▼] | Sort: [Meta Tier ▼]         │
│                                                                      │
│ ┌─────────────────┬──���──────────────┬─────────────────────────┐   │
│ │ Special Week    │ Silence Suzuka  │ Grass Wonder            │   │
│ │ [Portrait]      │ [Portrait]      │ [Portrait]              │   │
│ │ SSR · Friend    │ SR · Friend     │ SR · Friend             │   │
│ │ Meta: S         │ Meta: A         │ Meta: B                 │   │
│ │ LB: 4/4         │ LB: 3/4         │ LB: 2/4                 │   │
│ │ Bond: 0%        │ Bond: 0%        │ Bond: 0%                │   │
│ │ [SELECT]        │ [SELECT]        │ [SELECT]                │   │
│ └─────────────────┴─────────────────┴─────────────────────────┘   │
│                                                                      │
│ [Load More ▼]                                         [Cancel]      │
└──────────────────────────────────────────────────────────────────────┘
```text

### 5.3 Deck Validation Logic

**Component**: `app/Livewire/CharacterCreation/DeckBuilder.php`

```php
class DeckBuilder extends Component
{
    public $deck = [];
    public $deckErrors = [];

    public function validateDeck(): bool
    {
        $this->deckErrors = [];

        // Rule 1: Must have exactly 6 cards
        if (count($this->deck) !== 6) {
            $this->deckErrors[] = 'Deck must contain exactly 6 cards';
            return false;
        }

        // Rule 2: Count card types
        $typeCounts = collect($this->deck)
            ->countBy(fn($card) => $card['type'])
            ->all();

        // Rule 3: No more than 3 of the same type (excluding Friend)
        foreach ($typeCounts as $type => $count) {
            if ($type !== 'friend' && $count > 3) {
                $this->deckErrors[] = "Too many {$type} cards (max 3)";
                return false;
            }
        }

        // Rule 4: Recommend at least 1 Friend card
        if (!isset($typeCounts['friend']) || $typeCounts['friend'] === 0) {
            $this->deckErrors[] = 'Recommended: Add at least 1 Friend card for friendship training bonuses';
        }

        return count($this->deckErrors) === 0;
    }

    public function calculateSynergyScore(): int
    {
        $score = 0;

        // Meta tier scoring
        $metaScores = collect($this->deck)->map(function ($card) {
            return match($card['meta_tier']) {
                'SS' => 25,
                'S' => 20,
                'A' => 15,
                'B' => 10,
                default => 5,
            };
        })->sum();

        $score += $metaScores;

        // Limit break scoring
        $lbScore = collect($this->deck)->sum('limit_break_level') * 2;
        $score += $lbScore;

        // Type diversity scoring
        $uniqueTypes = collect($this->deck)->pluck('type')->unique()->count();
        $score += $uniqueTypes * 5;

        return min(100, $score);
    }
}
```

### 5.4 Deck Rules

| Rule | Description | Validation |
| --- | --- | --- |
| Deck Size | Exactly 6 cards | Hard requirement |
| Type Limit | Max 3 of same type (excluding Friend) | Hard requirement |
| Friend Recommendation | At least 1 Friend card recommended | Soft warning |
| Meta Quality | Higher meta tiers increase synergy score | Scoring factor |

---

## 6. Step 4: Review & Confirm

### 6.1 Desktop Layout (≥1024px)

```text
┌──────────────────────────────────────────────────────────────────────┐
│ Character Creation Wizard                                     [✕]    │
├──────────────────────────────────────────────────────────────────────┤
│                                                                      │
│ Step 4 of 4: Review & Confirm                                       │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│                                                                      │
│ ┌──────────────────────────────────────────────────────────────┐   │
│ │ Character Configuration Summary                               │   │
│ │                                                               │   │
│ │ ┌─────────────────┐                                          │   │
│ │ │   [Portrait]    │  Trainee: Mejiro Ardan (SSR)             │   │
│ │ │  Mejiro Ardan   │  Scenario: URA Championship Finals       │   │
│ │ └─────────────────┘                                          │   │
│ │                                                               │   │
│ │ Base Stats:                                                   │   │
│ │ Speed: 90 | Stamina: 70 | Power: 75 | Guts: 60 | Wit: 65     │   │
│ │                                                               │   │
│ │ [Edit Trainee ✏️]                                             │   │
│ └───────────────────────────────────────────────────────────────┘   │
│                                                                      │
│ ┌──────────────────────────────────────────────────────────────┐   │
│ │ Parent Inheritance                                            │   │
│ │                                                               │   │
│ │ Parent A: Kitasan Black (SSR)                                │   │
│ │ Parent B: Mejiro McQueen (SSR)                               │   │
│ │                                                               │   │
│ │ Factor Bonuses:                                               │   │
│ │ • Speed:   ★★☆ (+12)   Growth: +30%                          │   │
│ │ • Stamina: ★★★ (+21)   Growth: +25%                          │   │
│ │ • Power:   ★☆☆ (+5)    Growth: +20%                          │   │
│ │ • Guts:    ☆☆☆ (+0)    Growth: +10%                          │   │
│ │ • Wit:     ★★☆ (+12)   Growth: +15%                          │   │
│ │                                                               │   │
│ │ Aptitude Upgrades:                                            │   │
│ │ • Turf: B → A                                                 │   │
│ │                                                               │   │
│ │ [Edit Parents ✏️]                                             │   │
│ └───────────────────────────────────────────────────────────────┘   │
│                                                                      │
│ ┌──────────────────────────────────────────────────────────────┐   │
│ │ Support Deck Configuration                                    │   │
│ │                                                               │   │
│ │ Deck Score: 92/100 (Excellent)                                │   │
│ │                                                               │   │
│ │ Cards:                                                        │   │
│ │ 1. Mejiro Dober (SSR · Power) - Meta: S, LB: 4/4             │   │
│ │ 2. Tokai Teio (SSR · Speed) - Meta: SS, LB: 2/4              │   │
│ │ 3. Kitasan Black (SSR · Stamina) - Meta: S, LB: 4/4          │   │
│ │ 4. Narita Brian (SSR · Wit) - Meta: A, LB: 3/4               │   │
│ │ 5. Symboli Rudolf (SSR · Guts) - Meta: B, LB: 2/4            │   │
│ │ 6. Special Week (SSR · Friend) - Meta: S, LB: 4/4            │   │
│ │                                                               │   │
│ │ [Edit Deck ✏️]                                                │   │
│ └───────────────────────────────────────────────────────────────┘   │
│                                                                      │
│ ┌──────────────────────────────────────────────────────────────┐   │
│ │ Initial Career Settings                                       │   │
│ │                                                               │   │
│ │ Starting Turn: 1 (Junior Year)                                │   │
│ │ Starting Mood: Normal                                         │   │
│ │ Starting Energy: 100/100                                      │   │
│ │ Starting SP: 0                                                │   │
│ │                                                               │   │
│ │ Projected Stats (with factors):                               │   │
│ │ Speed:   102 | Stamina: 91 | Power: 80                       │   │
│ │ Guts:    60  | Wit: 77                                        │   │
│ └───────────────────────────────────────────────────────────────┘   │
│                                                                      │
│ ┌──────────────────────────────────────────────────────────────┐   │
│ │ Storage Mode                                                  │   │
│ │                                                               │   │
│ │ ○ Local Mode (Browser storage, works offline)                │   │
│ │ ● Account Mode (Cloud storage, sync across devices)          │   │
│ │                                                               │   │
│ │ ℹ️ Note: You can convert Local runs to Account later         │   │
│ └───────────────────────────────────────────────────────────────┘   │
│                                                                      │
│                                    [← Back] [Create Character]      │
└──────────────────────────────────────────────────────────────────────┘
```

### 6.2 Character Creation Flow

```mermaid
sequenceDiagram
    participant User
    participant Wizard
    participant CharacterStateService
    participant FactorService
    participant Database
    participant Cache

    User->>Wizard: Click "Create Character"
    Wizard->>Wizard: Validate all steps
    Wizard->>CharacterStateService: createCharacter(data)

    CharacterStateService->>FactorService: calculateInheritance()
    FactorService-->>CharacterStateService: Factor bonuses

    CharacterStateService->>Database: Begin transaction
    CharacterStateService->>Database: Create character record
    CharacterStateService->>Database: Create aptitude records
    CharacterStateService->>Database: Create factor records
    CharacterStateService->>Database: Create support_deck record
    CharacterStateService->>Database: Create initial stat_progress
    CharacterStateService->>Database: Commit transaction

    Database-->>CharacterStateService: Character ID

    CharacterStateService->>Cache: Clear user character cache
    CharacterStateService-->>Wizard: Success (character_id)

    Wizard->>User: Redirect to /characters/{id}
```text

### 6.3 Validation Summary

**All Steps Validation**: `app/Http/Requests/CreateCharacterRequest.php`

```php
class CreateCharacterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Step 1
            'trainee_id' => 'required|exists:characters,id',
            'scenario_type' => 'required|in:ura_finale,unity_cup',

            // Step 2
            'parent_a_id' => 'required|exists:characters,id|different:parent_b_id',
            'parent_b_id' => 'required|exists:characters,id|different:parent_a_id',

            // Step 3
            'support_deck' => 'required|array|size:6',
            'support_deck.*.id' => 'required|exists:support_cards,id',
            'support_deck.*.slot' => 'required|integer|between:1,6|distinct',

            // Step 4
            'storage_mode' => 'required|in:local,account',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate deck type distribution
            $deck = $this->input('support_deck', []);
            $typeCounts = collect($deck)->countBy('type');

            foreach ($typeCounts as $type => $count) {
                if ($type !== 'friend' && $count > 3) {
                    $validator->errors()->add(
                        'support_deck',
                        "Too many {$type} cards (max 3 allowed)"
                    );
                }
            }
        });
    }
}
```

---

## 7. Component Library

### 7.1 Reusable Components

| Component | Location | Usage |
| --- | --- | --- |
| Trainee Card | `resources/views/components/trainee-card.blade.php` | Step 1 trainee selection |
| Parent Selector | `resources/views/components/parent-selector.blade.php` | Step 2 parent selection |
| Factor Preview | `resources/views/components/factor-preview.blade.php` | Step 2 inheritance display |
| Support Card Slot | `resources/views/components/support-card-slot.blade.php` | Step 3 deck building |
| Deck Analyzer | `app/Livewire/DeckAnalyzer.php` | Step 3 deck validation |
| Review Summary | `resources/views/components/review-summary.blade.php` | Step 4 review display |

### 7.2 Livewire Components

```mermaid
flowchart TD
    subgraph WizardComponents[Wizard Components]
        Main[CharacterCreationWizard]
        Step1[TraineeSelector]
        Step2[ParentSelector]
        Step3[DeckBuilder]
        Step4[ReviewConfirm]
    end

    subgraph SharedComponents[Shared Components]
        CardGrid[SupportCardGrid]
        StatPreview[StatPreviewPanel]
        FactorCalc[FactorCalculator]
    end

    Main --> Step1
    Main --> Step2
    Main --> Step3
    Main --> Step4

    Step2 --> FactorCalc
    Step3 --> CardGrid
    Step4 --> StatPreview
```text

---

## 8. Performance Specifications

### 8.1 Performance Targets

| Metric | Target | Measurement |
| --- | --- | --- |
| **Initial Load** | < 2.0s | Time to first step render |
| **Step Transition** | < 300ms | Navigation between steps |
| **Factor Calculation** | < 100ms | Parent inheritance preview |
| **Deck Validation** | < 200ms | Support deck validation |
| **Character Creation** | < 1.5s | Database transaction completion |

### 8.2 Optimization Strategies

| Strategy | Implementation | Impact |
| --- | --- | --- |
| **Lazy Loading** | Defer step content until navigated | -40% initial bundle |
| **Debounced Search** | 300ms debounce on search inputs | Reduced API calls |
| **Cached Card Data** | Support card data cached (5 min TTL) | -60% repeated queries |
| **Optimistic UI** | Show loading states immediately | Perceived performance +30% |
| **Database Indexing** | Indexed queries on character/card lookups | -70% query time |

### 8.3 Caching Strategy

```mermaid
flowchart LR
    subgraph CacheLayer[Cache Strategy]
        Characters[Characters List<br/>TTL: 1 hour]
        Cards[Support Cards<br/>TTL: 5 minutes]
        Factors[Factor Calculations<br/>TTL: Session]
    end

    subgraph Database[Database]
        CharTable[(characters)]
        CardTable[(support_cards)]
        FactorTable[(factors)]
    end

    Characters --> CharTable
    Cards --> CardTable
    Factors --> FactorTable
```

---

## 9. Accessibility Specifications

### 9.1 WCAG 2.2 AA Compliance

| Criterion | Implementation | Test Method |
| --- | --- | --- |
| **1.1.1 Non-text Content** | All portraits have descriptive `alt` text | Screen reader testing |
| **1.4.3 Contrast Ratio** | 4.5:1 minimum for all text | Color contrast analyzer |
| **2.1.1 Keyboard** | All wizard steps navigable via keyboard | Keyboard-only testing |
| **2.4.3 Focus Order** | Logical tab order through wizard | Tab key traversal |
| **2.4.7 Focus Visible** | Clear focus indicators on all controls | Visual inspection |
| **3.3.1 Error Identification** | Validation errors clearly identified | Screen reader + visual |
| **3.3.2 Labels or Instructions** | All inputs have associated labels | Automated scan |
| **4.1.2 Name, Role, Value** | Proper ARIA attributes on custom controls | axe-core scan |
| **1.4.1 Use of Color** | Step progress and validation state conveyed by text and icon, not color alone | Visual + screen reader |

### 9.2 Keyboard Navigation

| Action | Shortcut | Context |
| --- | --- | --- |
| Next Step | `Enter` or `Ctrl+→` | When current step valid |
| Previous Step | `Ctrl+←` | Any step except first |
| Cancel Wizard | `Esc` | Any step |
| Search Trainees | `/` | Step 1 |
| Select Card | `Enter` | When card focused |
| Navigate Cards | `Arrow Keys` | Card grid |

### 9.3 Screen Reader Announcements

```html
<!-- Progress announcement -->
<div aria-live="polite" aria-atomic="true" class="sr-only">
    Step 2 of 4: Select Parents. You have selected Mejiro Ardan as your trainee.
</div>

<!-- Validation announcement -->
<div aria-live="assertive" aria-atomic="true" class="sr-only">
    Error: Please select both Parent A and Parent B to continue.
</div>

<!-- Success announcement -->
<div aria-live="polite" aria-atomic="true" class="sr-only">
    Character created successfully. Redirecting to character dashboard.
</div>
```text

### 9.4 Accessibility Interaction Requirements

The following rules MUST be satisfied, independent of visual design or component implementation:

1. **Focus lock in wizard**: When the wizard is open, keyboard focus must be constrained within the
wizard and not reach content behind it.
2. **Focus advance on step change**: On proceeding to the next step, focus moves to the step heading
or the first interactive element in the new step content.
3. **Focus return on cancel**: Closing or cancelling the wizard returns focus to the element that
triggered it (typically the "Create Character" button).
4. **Focus on validation error**: On failed step validation, focus moves to the first field with an
error, and all errors are announced via `aria-live="assertive"`.
5. **Touch targets**: All interactive controls (Select, Next, Back, Cancel, filter checkboxes) must
have a minimum touch area of 44×44 CSS pixels.
6. **Step progress non-color**: The step progress indicator must convey current step by text label
and position, not color alone.

---

## 10. Testing Specifications

### 10.1 Unit Tests

**Test File**: `tests/Unit/Services/FactorServiceTest.php`

```php
test('calculates stat factors correctly', function () {
    $trainee = Character::factory()->create();
    $parentA = Character::factory()->withFactors(['speed' => 2, 'power' => 1])->create();
    $parentB = Character::factory()->withFactors(['stamina' => 3, 'wit' => 2])->create();

    $service = app(FactorService::class);
    $result = $service->calculateInheritance($trainee, $parentA, $parentB);

    expect($result['stat_factors']['speed']['bonus'])->toBe(12)
        ->and($result['stat_factors']['stamina']['bonus'])->toBe(21)
        ->and($result['stat_factors']['power']['bonus'])->toBe(5);
});

test('validates deck type distribution', function () {
    $deck = [
        ['type' => 'speed', 'id' => 1],
        ['type' => 'speed', 'id' => 2],
        ['type' => 'speed', 'id' => 3],
        ['type' => 'speed', 'id' => 4], // Too many speed cards
        ['type' => 'stamina', 'id' => 5],
        ['type' => 'friend', 'id' => 6],
    ];

    $service = app(DeckValidationService::class);
    $result = $service->validateDeck($deck);

    expect($result->isValid())->toBeFalse()
        ->and($result->errors)->toContain('Too many speed cards (max 3 allowed)');
});

test('parent selection prevents same character', function () {
    $trainee = Character::factory()->create();

    $request = CreateCharacterRequest::factory()->create([
        'trainee_id' => $trainee->id,
        'parent_a_id' => 1,
        'parent_b_id' => 1, // Same as parent A
    ]);

    expect($request->validate())->toThrow(ValidationException::class);
});
```

### 10.2 Feature Tests

**Test File**: `tests/Feature/CharacterCreationTest.php`

```php
test('complete character creation wizard flow', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/characters/create')
        ->assertOk()
        ->assertSee('Step 1 of 4');

    // Step 1: Select trainee
    $trainee = Character::factory()->create(['rarity' => 'SSR']);

    Livewire::actingAs($user)
        ->test(TraineeSelector::class)
        ->set('selectedTraineeId', $trainee->id)
        ->set('scenarioType', 'ura_finale')
        ->call('nextStep')
        ->assertEmitted('stepCompleted', 1);

    // Step 2: Select parents
    $parentA = Character::factory()->create();
    $parentB = Character::factory()->create();

    Livewire::actingAs($user)
        ->test(ParentSelector::class)
        ->set('parentAId', $parentA->id)
        ->set('parentBId', $parentB->id)
        ->call('nextStep')
        ->assertEmitted('stepCompleted', 2);

    // Step 3: Build support deck
    $cards = SupportCard::factory()->count(6)->create();

    Livewire::actingAs($user)
        ->test(DeckBuilder::class)
        ->set('supportDeck', $cards->pluck('id')->toArray())
        ->call('validateDeck')
        ->assertHasNoErrors()
        ->call('nextStep')
        ->assertEmitted('stepCompleted', 3);

    // Step 4: Review and confirm
    Livewire::actingAs($user)
        ->test(ReviewConfirm::class)
        ->call('createCharacter')
        ->assertRedirect('/characters');

    $this->assertDatabaseHas('ucp_characters', [
        'user_id' => $user->id,
        'scenario_type' => 'ura_finale',
    ]);
});

test('wizard validates deck composition', function () {
    $user = User::factory()->create();

    // Create deck with too many of same type
    $speedCards = SupportCard::factory()->count(4)->create(['type' => 'speed']);
    $otherCards = SupportCard::factory()->count(2)->create(['type' => 'stamina']);

    Livewire::actingAs($user)
        ->test(DeckBuilder::class)
        ->set('supportDeck', [
            ...$speedCards->pluck('id')->toArray(),
            ...$otherCards->pluck('id')->toArray(),
        ])
        ->call('validateDeck')
        ->assertHasErrors(['supportDeck' => 'Too many speed cards']);
});

test('wizard calculates factor preview correctly', function () {
    $trainee = Character::factory()->create([
        'base_speed' => 90,
        'base_stamina' => 70,
    ]);

    $parentA = Character::factory()->create();
    $parentB = Character::factory()->create();

    // Add factors to parents
    Factor::factory()->create([
        'character_id' => $parentA->id,
        'stat_type' => 'speed',
        'star_level' => 2, // ★★☆ = +12
    ]);

    Factor::factory()->create([
        'character_id' => $parentB->id,
        'stat_type' => 'stamina',
        'star_level' => 3, // ★★★ = +21
    ]);

    $service = app(FactorService::class);
    $preview = $service->calculateInheritance($trainee, $parentA, $parentB);

    expect($preview['stat_factors']['speed']['bonus'])->toBe(12)
        ->and($preview['stat_factors']['stamina']['bonus'])->toBe(21)
        ->and($preview['projected_stats']['speed'])->toBe(102) // 90 + 12
        ->and($preview['projected_stats']['stamina'])->toBe(91); // 70 + 21
});
```text

### 10.3 E2E Tests (Playwright)

**Test File**: `tests/e2e/character-creation-wizard.spec.js`

```javascript
import { test, expect } from '@playwright/test';

test.describe('WF-002: Character Creation Wizard', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/characters/create');
    });

    test('displays step 1 with trainee selection', async ({ page }) => {
        await expect(page.getByTestId('wizard-step-indicator')).toContainText('Step 1 of 4');
        await expect(page.getByTestId('trainee-grid')).toBeVisible();
        await expect(page.getByTestId('scenario-selector')).toBeVisible();
    });

    test('allows searching and filtering trainees', async ({ page }) => {
        // Search by name
        await page.getByTestId('trainee-search').fill('Mejiro Ardan');
        await expect(page.getByTestId('trainee-card-mejiro-ardan')).toBeVisible();

        // Filter by rarity
        await page.getByTestId('filter-rarity-ssr').click();
        const cards = page.getByTestId(/^trainee-card-/);
        const count = await cards.count();

        for (let i = 0; i < count; i++) {
            await expect(cards.nth(i)).toContainText('SSR');
        }
    });

    test('completes full wizard flow', async ({ page }) => {
        // Step 1: Select trainee
        await page.getByTestId('trainee-card-mejiro-ardan').click();
        await page.getByTestId('select-trainee-mejiro-ardan').click();
        await page.getByTestId('scenario-selector').selectOption('ura_finale');
        await page.getByTestId('next-button').click();

        // Step 2: Select parents
        await expect(page.getByTestId('wizard-step-indicator')).toContainText('Step 2 of 4');
        await page.getByTestId('choose-parent-a-button').click();
        await page.getByTestId('parent-option-kitasan-black').click();
        await page.getByTestId('choose-parent-b-button').click();
        await page.getByTestId('parent-option-mejiro-mcqueen').click();

        // Verify factor preview
        await expect(page.getByTestId('factor-preview-speed')).toContainText('★★☆');
        await expect(page.getByTestId('factor-preview-stamina')).toContainText('★★★');

        await page.getByTestId('next-button').click();

        // Step 3: Build support deck
        await expect(page.getByTestId('wizard-step-indicator')).toContainText('Step 3 of 4');

        for (let i = 1; i <= 6; i++) {
            await page.getByTestId(`deck-slot-${i}`).click();
            await page.getByTestId(`card-select-${i}`).first().click();
        }

        // Verify deck validation
        await expect(page.getByTestId('deck-score')).toBeVisible();
        await expect(page.getByTestId('deck-validation-success')).toBeVisible();

        await page.getByTestId('next-button').click();

        // Step 4: Review and confirm
        await expect(page.getByTestId('wizard-step-indicator')).toContainText('Step 4 of 4');
        await expect(page.getByTestId('review-trainee-name')).toContainText('Mejiro Ardan');
        await expect(page.getByTestId('review-scenario')).toContainText('URA Finals');

        await page.getByTestId('create-character-button').click();

        // Verify redirect to character page
        await expect(page).toHaveURL(/\/characters\/\d+/);
        await expect(page.getByRole('heading')).toContainText('Mejiro Ardan');
    });

    test('validates deck type distribution', async ({ page }) => {
        // Navigate to step 3
        await page.getByTestId('trainee-card-mejiro-ardan').click();
        await page.getByTestId('select-trainee-mejiro-ardan').click();
        await page.getByTestId('next-button').click();

        await page.getByTestId('choose-parent-a-button').click();
        await page.getByTestId('parent-option-kitasan-black').click();
        await page.getByTestId('choose-parent-b-button').click();
        await page.getByTestId('parent-option-mejiro-mcqueen').click();
        await page.getByTestId('next-button').click();

        // Try to select 4 speed cards
        const speedCards = await page.getByTestId(/^card-select-speed-/).count();
        for (let i = 0; i < Math.min(4, speedCards); i++) {
            await page.getByTestId(`deck-slot-${i + 1}`).click();
            await page.getByTestId(/^card-select-speed-/).nth(i).click();
        }

        // Verify validation error
        await expect(page.getByTestId('deck-validation-error')).toContainText('Too many speed cards');
        await expect(page.getByTestId('next-button')).toBeDisabled();
    });

    test('supports keyboard navigation', async ({ page }) => {
        await page.keyboard.press('Tab');
        await expect(page.getByTestId('trainee-search')).toBeFocused();

        await page.keyboard.press('Tab');
        await expect(page.getByTestId('trainee-card-mejiro-ardan')).toBeFocused();

        await page.keyboard.press('Enter');
        await expect(page.getByTestId('trainee-card-mejiro-ardan')).toHaveAttribute('aria-selected', 'true');
    });

    test('allows navigation back through wizard steps', async ({ page }) => {
        // Complete step 1
        await page.getByTestId('trainee-card-mejiro-ardan').click();
        await page.getByTestId('select-trainee-mejiro-ardan').click();
        await page.getByTestId('next-button').click();

        // Go to step 2
        await expect(page.getByTestId('wizard-step-indicator')).toContainText('Step 2 of 4');

        // Click back
        await page.getByTestId('back-button').click();

        // Verify back at step 1 with selections preserved
        await expect(page.getByTestId('wizard-step-indicator')).toContainText('Step 1 of 4');
        await expect(page.getByTestId('trainee-card-mejiro-ardan')).toHaveAttribute('aria-selected', 'true');
    });
});
```

### 10.4 Accessibility Tests

**Test File**: `tests/e2e/accessibility/character-creation-wizard.spec.js`

```javascript
import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

test.describe('WF-002: Accessibility', () => {
    test('step 1 has no accessibility violations', async ({ page }) => {
        await page.goto('/characters/create');

        const accessibilityScanResults = await new AxeBuilder({ page })
            .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'])
            .analyze();

        expect(accessibilityScanResults.violations).toEqual([]);
    });

    test('step 2 parent selection is accessible', async ({ page }) => {
        await page.goto('/characters/create');

        // Navigate to step 2
        await page.getByTestId('trainee-card-mejiro-ardan').click();
        await page.getByTestId('select-trainee-mejiro-ardan').click();
        await page.getByTestId('next-button').click();

        const accessibilityScanResults = await new AxeBuilder({ page })
            .withTags(['wcag2a', 'wcag2aa'])
            .analyze();

        expect(accessibilityScanResults.violations).toEqual([]);
    });

    test('supports screen reader announcements', async ({ page }) => {
        await page.goto('/characters/create');

        // Check for live region
        const liveRegion = page.locator('[aria-live="polite"]');
        await expect(liveRegion).toBeAttached();

        // Select trainee
        await page.getByTestId('trainee-card-mejiro-ardan').click();
        await page.getByTestId('select-trainee-mejiro-ardan').click();

        // Verify announcement
        await expect(liveRegion).toContainText('Mejiro Ardan selected');

        // Proceed to next step
        await page.getByTestId('next-button').click();

        // Verify step announcement
        await expect(liveRegion).toContainText('Step 2 of 4');
    });

    test('all interactive elements have accessible names', async ({ page }) => {
        await page.goto('/characters/create');

        const buttons = page.getByRole('button');
        const count = await buttons.count();

        for (let i = 0; i < count; i++) {
            const name = await buttons.nth(i).getAttribute('aria-label');
            const text = await buttons.nth(i).textContent();

            expect(name || text).toBeTruthy();
        }
    });

    test('form inputs have associated labels', async ({ page }) => {
        await page.goto('/characters/create');

        const searchInput = page.getByTestId('trainee-search');
        const labelledBy = await searchInput.getAttribute('aria-label');

        expect(labelledBy).toBe('Search trainee characters');
    });

    test('error messages are announced', async ({ page }) => {
        await page.goto('/characters/create');

        // Navigate to step 3
        await page.getByTestId('trainee-card-mejiro-ardan').click();
        await page.getByTestId('select-trainee-mejiro-ardan').click();
        await page.getByTestId('next-button').click();

        await page.getByTestId('choose-parent-a-button').click();
        await page.getByTestId('parent-option-kitasan-black').click();
        await page.getByTestId('choose-parent-b-button').click();
        await page.getByTestId('parent-option-mejiro-mcqueen').click();
        await page.getByTestId('next-button').click();

        // Try to proceed without full deck
        await page.getByTestId('next-button').click();

        // Verify error announcement
        const errorRegion = page.locator('[aria-live="assertive"]');
        await expect(errorRegion).toContainText('Deck must contain exactly 6 cards');
    });
});
```text

### 10.5 Visual Regression Tests

**Test File**: `tests/e2e/visual/character-creation-wizard.spec.js`

```javascript
import { test, expect } from '@playwright/test';

test.describe('WF-002: Visual Regression', () => {
    test('step 1 matches snapshot', async ({ page }) => {
        await page.goto('/characters/create');
        await expect(page).toHaveScreenshot('step-1-trainee-selection.png');
    });

    test('step 2 factor preview matches snapshot', async ({ page }) => {
        await page.goto('/characters/create');

        // Navigate to step 2
        await page.getByTestId('trainee-card-mejiro-ardan').click();
        await page.getByTestId('select-trainee-mejiro-ardan').click();
        await page.getByTestId('next-button').click();

        await page.getByTestId('choose-parent-a-button').click();
        await page.getByTestId('parent-option-kitasan-black').click();
        await page.getByTestId('choose-parent-b-button').click();
        await page.getByTestId('parent-option-mejiro-mcqueen').click();

        // Take snapshot of factor preview
        const preview = page.getByTestId('factor-preview-panel');
        await expect(preview).toHaveScreenshot('factor-preview.png');
    });

    test('step 3 deck builder matches snapshot', async ({ page }) => {
        await page.goto('/characters/create');

        // Navigate to step 3
        await page.getByTestId('trainee-card-mejiro-ardan').click();
        await page.getByTestId('select-trainee-mejiro-ardan').click();
        await page.getByTestId('next-button').click();

        await page.getByTestId('choose-parent-a-button').click();
        await page.getByTestId('parent-option-kitasan-black').click();
        await page.getByTestId('choose-parent-b-button').click();
        await page.getByTestId('parent-option-mejiro-mcqueen').click();
        await page.getByTestId('next-button').click();

        await expect(page).toHaveScreenshot('step-3-deck-builder.png');
    });

    test('step 4 review matches snapshot', async ({ page }) => {
        await page.goto('/characters/create');

        // Complete all steps
        await page.getByTestId('trainee-card-mejiro-ardan').click();
        await page.getByTestId('select-trainee-mejiro-ardan').click();
        await page.getByTestId('next-button').click();

        await page.getByTestId('choose-parent-a-button').click();
        await page.getByTestId('parent-option-kitasan-black').click();
        await page.getByTestId('choose-parent-b-button').click();
        await page.getByTestId('parent-option-mejiro-mcqueen').click();
        await page.getByTestId('next-button').click();

        for (let i = 1; i <= 6; i++) {
            await page.getByTestId(`deck-slot-${i}`).click();
            await page.getByTestId(`card-select-${i}`).first().click();
        }
        await page.getByTestId('next-button').click();

        await expect(page).toHaveScreenshot('step-4-review.png');
    });

    test('mobile layout matches snapshot', async ({ page }) => {
        await page.setViewportSize({ width: 375, height: 667 });
        await page.goto('/characters/create');

        await expect(page).toHaveScreenshot('step-1-mobile.png');
    });
});
```

---

## 11. Related Documentation

### 11.1 Product Requirements

- [PRD-001: Character Management](../02-prds/PRD-001_Character_Management.md)
- [PRD-005: Support Card Management](../02-prds/PRD-005_Support_Card_Management.md)

### 11.2 Technical Specifications

- [SPEC-001: Character Management Technical](../02-specs/SPEC-001_Character_Management_Technical.md)
- [SPEC-005: Support Card Management Technical](../02-specs/SPEC-005_Support_Card_Management_Technical.md)

### 11.3 Flow Documentation

- [FLOW-001: Character Management System](../01-flows/FLOW-001_Character_Management_System.md)
- [TECH-FLOW-001: Character Management Flow](../01-tech-flow/TECH-FLOW-001_Character_Management_Flow.md)

### 11.4 Sequence Diagrams

- [SEQ-001: Character Creation Sequence](../01-sequences/SEQ-001_Character_Creation_Sequence.md)
- [SEQ-005: Support Card Upgrade](../01-sequences/SEQ-005_Support_Card_Upgrade.md)

### 11.5 User Flow Diagrams

- [UF-002: Career Setup Flow](../01-user-flows/UF-002_Career_Setup_Flow.md)
- [UF-006: Support Deck Building Flow](../01-user-flows/UF-006_Support_Deck_Building_Flow.md)

---

## 12. Version History

| Version | Date | Author | Changes |
| --- | --- | --- | --- |
| 2.4.0 | 2026-03-09 | Development Team | Alignment review: added storage-mode framing (§1.4–1.6), accessibility interaction requirements (§9.4), conceptual component disclaimer, softened implementation status |
| 2.3.0 | 2026-02-22 | Development Team | Updated version/dates, aligned technology references with current stack (Livewire 4, Neuron AI v2.11, GameTora/umapyoi.net) |
| 2.2.0 | 2026-01-28 | Development Team | Updated with verified game mechanics from Global English Server: corrected aptitude grade scale (S is max, no SS), added aptitude categories documentation |
| 2.0.0 | 2026-01-24 | Development Team | Complete wireframe specification with testing requirements, accessibility guidelines, and performance targets aligned with v2.0.0 implementation |
| 1.0.0 | 2026-01-14 | Development Team | Initial wireframe specification |

---

## 13. Notes

**Implementation Status**: Alignment-reviewed concept; specific component classes and route paths in
this document are illustrative and should be verified against the current implementation.

**Known Issues**: None

**Future Enhancements**:

- Drag-and-drop support deck building
- Advanced deck synergy visualization
- Character comparison tool before final selection
- Saved deck templates for quick setup

---

_This wireframe describes the intended experience for the Character Creation Wizard. Details should
be verified against current implementation documentation before treating as authoritative._
