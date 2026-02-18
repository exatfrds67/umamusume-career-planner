# Design Document: Game Mechanics Accuracy Corrections

## Overview

This design implements comprehensive corrections to five critical game mechanics systems in the Umamusume Career Planner based on verified research from Game8, UmaReference, GameTora, and community sources. The corrections address fundamental calculation errors that impact training predictions, skill planning, race strategy, and character optimization.

### Scope

The design covers:

1. **Skill Hint System**: Correcting SP cost reduction formula (5 levels, 10%/10%/10%/5%/5% progression)
2. **Aptitude Grade System**: Removing SS grade, correcting bonus percentages for S/A ranks
3. **Stat System**: Implementing soft cap at 1200 with 50% effectiveness above, hard cap at ~1600
4. **Weather/Track System**: Separating weather from track conditions, implementing condition-specific penalties
5. **Training Formula**: Implementing complete 7-component multiplicative formula with friendship training
6. **Stamina Requirements**: Distance-specific ranges with gold skill adjustments
7. **Data Migration**: Safe conversion of existing data to corrected mechanics
8. **UI Updates**: Clear communication of corrected mechanics and their effects

### Design Principles

- **Accuracy First**: All formulas must match verified game mechanics exactly
- **Backward Compatibility**: Existing data must be preserved and migrated safely
- **Transparency**: Users must understand what changed and why
- **Testability**: All calculations must be unit and property testable
- **Performance**: Corrections must not degrade application performance

## Architecture

### System Components

```
┌─────────────────────────────────────────────────────────────┐
│                     Presentation Layer                       │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │ Skill Views  │  │ Training UI  │  │  Race Views  │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
└─────────────────────────────────────────────────────────────┘
                            │
┌─────────────────────────────────────────────────────────────┐
│                    Application Layer                         │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │ Livewire     │  │ Controllers  │  │ Form         │      │
│  │ Components   │  │              │  │ Requests     │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
└─────────────────────────────────────────────────────────────┘
                            │
┌─────────────────────────────────────────────────────────────┐
│                      Service Layer                           │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ SkillHintCalculationService (NEW)                    │   │
│  │ AptitudeGradeBonusService (NEW)                      │   │
│  │ StatEffectivenessService (NEW)                       │   │
│  │ TrackConditionService (NEW)                          │   │
│  │ TrainingFormulaService (UPDATED)                     │   │
│  │ StaminaRequirementService (NEW)                      │   │
│  │ MechanicsMigrationService (NEW)                      │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                            │
┌─────────────────────────────────────────────────────────────┐
│                       Domain Layer                           │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │ Models       │  │ Enums        │  │ Value        │      │
│  │              │  │              │  │ Objects      │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
└─────────────────────────────────────────────────────────────┘
```

### Component Responsibilities

**SkillHintCalculationService**

- Calculate SP cost reduction based on hint level (0-5)
- Apply correct percentage per level (10%, 20%, 30%, 35%, 40%)
- Provide hint level progression information

**AptitudeGradeBonusService**

- Validate aptitude grades (G-S only, no SS)
- Calculate stat bonuses for S-rank aptitudes (+5% Speed/Power, +10% Wit)
- Apply baseline (0%) for A-rank, penalties for B and below

**StatEffectivenessService**

- Calculate effective stat values considering soft cap at 1200
- Apply 100% effectiveness for 0-1200, 50% effectiveness above 1200
- Identify stat breakpoints (901, 1200, 1600)
- Determine special mechanic unlocks (e.g., Stamina Contest at 1200+)

**TrackConditionService**

- Model track conditions (Firm, Good, Soft, Heavy) separately from weather
- Calculate condition-specific penalties (Power, Speed, stamina drain)
- Apply double penalties for Dirt surface in wet conditions
- Determine track condition probability based on weather

**TrainingFormulaService** (Updated)

- Implement complete 7-component multiplicative formula
- Calculate friendship training bonus (Bond ≥80: +10% to +35%)
- Calculate multi-training bonus (+5% per support card, max +30%)
- Provide formula component breakdown for transparency

**StaminaRequirementService**

- Calculate distance-specific stamina requirements (Sprint/Mile/Medium/Long)
- Adjust requirements based on gold stamina skill count
- Provide range recommendations (min-max)

**MechanicsMigrationService**

- Identify records requiring migration
- Recalculate values using corrected formulas
- Generate migration reports
- Provide rollback capability

## Components and Interfaces

### Service Interfaces

```php
interface SkillHintCalculationService
{
    /**
     * Calculate SP cost reduction percentage for a given hint level.
     *
     * @param int $hintLevel Hint level (0-5)
     * @return float Reduction percentage (0.0-0.40)
     */
    public function calculateReduction(int $hintLevel): float;

    /**
     * Calculate final SP cost after hint reduction.
     *
     * @param int $baseCost Base SP cost
     * @param int $hintLevel Hint level (0-5)
     * @return int Final SP cost
     */
    public function calculateFinalCost(int $baseCost, int $hintLevel): int;

    /**
     * Get hint level progression information.
     *
     * @return array<int, float> Map of hint level to reduction percentage
     */
    public function getHintLevelProgression(): array;
}

interface AptitudeGradeBonusService
{
    /**
     * Validate aptitude grade.
     *
     * @param string $grade Grade to validate
     * @return bool True if valid (G-S)
     */
    public function isValidGrade(string $grade): bool;

    /**
     * Calculate stat bonus for aptitude grade and type.
     *
     * @param string $grade Aptitude grade (G-S)
     * @param string $aptitudeType Type (distance/surface/style)
     * @return float Bonus percentage (-1.0 to 0.10)
     */
    public function calculateBonus(string $grade, string $aptitudeType): float;

    /**
     * Get affected stat for aptitude type.
     *
     * @param string $aptitudeType Type (distance/surface/style)
     * @return string Affected stat name
     */
    public function getAffectedStat(string $aptitudeType): string;
}

interface StatEffectivenessService
{
    /**
     * Calculate effective stat value considering soft cap.
     *
     * @param int $rawValue Raw stat value (0-1600)
     * @return float Effective stat value
     */
    public function calculateEffectiveValue(int $rawValue): float;

    /**
     * Check if stat value is at or above soft cap.
     *
     * @param int $value Stat value
     * @return bool True if at/above 1200
     */
    public function isAtSoftCap(int $value): bool;

    /**
     * Get stat breakpoint information.
     *
     * @param int $value Stat value
     * @return array Breakpoint data (next threshold, distance to threshold)
     */
    public function getBreakpointInfo(int $value): array;
}

interface TrackConditionService
{
    /**
     * Calculate track condition penalties.
     *
     * @param string $condition Track condition (Firm/Good/Soft/Heavy)
     * @param string $surface Race surface (Turf/Dirt)
     * @return array Penalties (power, speed, stamina_drain)
     */
    public function calculatePenalties(string $condition, string $surface): array;

    /**
     * Determine track condition probability based on weather.
     *
     * @param string $weather Weather condition
     * @return array<string, float> Map of condition to probability
     */
    public function getConditionProbabilities(string $weather): array;
}

interface TrainingFormulaService
{
    /**
     * Calculate stat gain using complete formula.
     *
     * @param array $params Formula parameters
     * @return int Calculated stat gain
     */
    public function calculateStatGain(array $params): int;

    /**
     * Get formula component breakdown.
     *
     * @param array $params Formula parameters
     * @return array Component values and multipliers
     */
    public function getFormulaBreakdown(array $params): array;

    /**
     * Calculate friendship training bonus.
     *
     * @param int $bondLevel Bond level (0-100)
     * @return float Bonus multiplier (1.0-1.35)
     */
    public function calculateFriendshipBonus(int $bondLevel): float;
}
```

## Data Models

### Database Schema Updates

**skills table** (Updated)

```sql
-- Add hint level validation
ALTER TABLE skills 
  ADD CONSTRAINT check_hint_level 
  CHECK (hint_level >= 0 AND hint_level <= 5);

-- Add columns for tracking corrected costs
ALTER TABLE skills
  ADD COLUMN base_sp_cost INT NOT NULL DEFAULT 0,
  ADD COLUMN effective_sp_cost INT GENERATED ALWAYS AS (
    CASE 
      WHEN hint_level = 0 THEN base_sp_cost
      WHEN hint_level = 1 THEN FLOOR(base_sp_cost * 0.90)
      WHEN hint_level = 2 THEN FLOOR(base_sp_cost * 0.80)
      WHEN hint_level = 3 THEN FLOOR(base_sp_cost * 0.70)
      WHEN hint_level = 4 THEN FLOOR(base_sp_cost * 0.65)
      WHEN hint_level = 5 THEN FLOOR(base_sp_cost * 0.60)
    END
  ) STORED;
```

**characters table** (Updated)

```sql
-- Update aptitude grade constraints
ALTER TABLE characters
  DROP CONSTRAINT IF EXISTS check_distance_aptitude,
  DROP CONSTRAINT IF EXISTS check_surface_aptitude,
  DROP CONSTRAINT IF EXISTS check_style_aptitude;

ALTER TABLE characters
  ADD CONSTRAINT check_distance_aptitude 
    CHECK (distance_aptitude IN ('G','F','E','D','C','B','A','S')),
  ADD CONSTRAINT check_surface_aptitude 
    CHECK (surface_aptitude IN ('G','F','E','D','C','B','A','S')),
  ADD CONSTRAINT check_style_aptitude 
    CHECK (running_style_aptitude IN ('G','F','E','D','C','B','A','S'));

-- Add columns for effective stat values
ALTER TABLE characters
  ADD COLUMN speed_effective DECIMAL(10,2) GENERATED ALWAYS AS (
    CASE 
      WHEN speed <= 1200 THEN speed
      ELSE 1200 + (speed - 1200) * 0.5
    END
  ) STORED,
  ADD COLUMN stamina_effective DECIMAL(10,2) GENERATED ALWAYS AS (
    CASE 
      WHEN stamina <= 1200 THEN stamina
      ELSE 1200 + (stamina - 1200) * 0.5
    END
  ) STORED,
  ADD COLUMN power_effective DECIMAL(10,2) GENERATED ALWAYS AS (
    CASE 
      WHEN power <= 1200 THEN power
      ELSE 1200 + (power - 1200) * 0.5
    END
  ) STORED,
  ADD COLUMN guts_effective DECIMAL(10,2) GENERATED ALWAYS AS (
    CASE 
      WHEN guts <= 1200 THEN guts
      ELSE 1200 + (guts - 1200) * 0.5
    END
  ) STORED,
  ADD COLUMN wit_effective DECIMAL(10,2) GENERATED ALWAYS AS (
    CASE 
      WHEN wit <= 1200 THEN wit
      ELSE 1200 + (wit - 1200) * 0.5
    END
  ) STORED;
```

**races table** (Updated)

```sql
-- Add track condition column
ALTER TABLE races
  ADD COLUMN track_condition ENUM('Firm', 'Good', 'Soft', 'Heavy') 
    NOT NULL DEFAULT 'Firm',
  ADD COLUMN weather VARCHAR(50) NULL;

-- Migrate existing weather data to track conditions
UPDATE races 
SET track_condition = CASE
  WHEN weather = 'Rainy' THEN 'Soft'
  WHEN weather = 'Snowy' THEN 'Heavy'
  ELSE 'Firm'
END;
```

**training_predictions table** (Updated)

```sql
-- Add columns for formula components
ALTER TABLE training_predictions
  ADD COLUMN base_gain INT NOT NULL DEFAULT 0,
  ADD COLUMN growth_rate_multiplier DECIMAL(5,3) NOT NULL DEFAULT 1.000,
  ADD COLUMN mood_multiplier DECIMAL(5,3) NOT NULL DEFAULT 1.000,
  ADD COLUMN training_effect_multiplier DECIMAL(5,3) NOT NULL DEFAULT 1.000,
  ADD COLUMN support_card_multiplier DECIMAL(5,3) NOT NULL DEFAULT 1.000,
  ADD COLUMN friendship_multiplier DECIMAL(5,3) NOT NULL DEFAULT 1.000,
  ADD COLUMN final_multiplier DECIMAL(5,3) GENERATED ALWAYS AS (
    growth_rate_multiplier * 
    mood_multiplier * 
    training_effect_multiplier * 
    support_card_multiplier * 
    friendship_multiplier
  ) STORED;
```

**mechanics_migrations table** (New)

```sql
CREATE TABLE mechanics_migrations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  migration_name VARCHAR(255) NOT NULL,
  records_processed INT NOT NULL DEFAULT 0,
  records_updated INT NOT NULL DEFAULT 0,
  records_failed INT NOT NULL DEFAULT 0,
  started_at TIMESTAMP NOT NULL,
  completed_at TIMESTAMP NULL,
  status ENUM('pending', 'running', 'completed', 'failed') NOT NULL DEFAULT 'pending',
  error_log TEXT NULL,
  rollback_data JSON NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_migration_name (migration_name),
  INDEX idx_status (status)
);
```

### PHP Enums

**HintLevel** (New)

```php
enum HintLevel: int
{
    case NONE = 0;
    case LEVEL_1 = 1;
    case LEVEL_2 = 2;
    case LEVEL_3 = 3;
    case LEVEL_4 = 4;
    case LEVEL_5 = 5;

    public function getReductionPercentage(): float
    {
        return match($this) {
            self::NONE => 0.0,
            self::LEVEL_1 => 0.10,
            self::LEVEL_2 => 0.20,
            self::LEVEL_3 => 0.30,
            self::LEVEL_4 => 0.35,
            self::LEVEL_5 => 0.40,
        };
    }

    public function getLabel(): string
    {
        return match($this) {
            self::NONE => 'No Hints',
            self::LEVEL_1 => 'Hint Lv1 (-10%)',
            self::LEVEL_2 => 'Hint Lv2 (-20%)',
            self::LEVEL_3 => 'Hint Lv3 (-30%)',
            self::LEVEL_4 => 'Hint Lv4 (-35%)',
            self::LEVEL_5 => 'Hint Lv5 (-40%)',
        };
    }
}
```

**AptitudeGrade** (Updated)

```php
enum AptitudeGrade: string
{
    case G = 'G';
    case F = 'F';
    case E = 'E';
    case D = 'D';
    case C = 'C';
    case B = 'B';
    case A = 'A';
    case S = 'S';
    // SS removed

    public function getDistanceBonus(): float
    {
        return match($this) {
            self::S => 0.05,  // +5% Speed
            self::A => 0.0,   // Baseline
            self::B => -0.05,
            self::C => -0.10,
            self::D => -0.15,
            self::E => -0.20,
            self::F => -0.25,
            self::G => -0.30,
        };
    }

    public function getSurfaceBonus(): float
    {
        return match($this) {
            self::S => 0.05,  // +5% Power
            self::A => 0.0,   // Baseline
            self::B => -0.05,
            self::C => -0.10,
            self::D => -0.15,
            self::E => -0.20,
            self::F => -0.25,
            self::G => -0.30,
        };
    }

    public function getStyleBonus(): float
    {
        return match($this) {
            self::S => 0.10,  // +10% Wit
            self::A => 0.0,   // Baseline
            self::B => -0.05,
            self::C => -0.10,
            self::D => -0.15,
            self::E => -0.20,
            self::F => -0.25,
            self::G => -0.30,
        };
    }
}
```

**TrackCondition** (New)

```php
enum TrackCondition: string
{
    case FIRM = 'Firm';
    case GOOD = 'Good';
    case SOFT = 'Soft';
    case HEAVY = 'Heavy';

    public function getPowerPenalty(bool $isDirt = false): int
    {
        $basePenalty = match($this) {
            self::FIRM => 0,
            self::GOOD => -50,
            self::SOFT => -50,
            self::HEAVY => -50,
        };

        return $isDirt ? $basePenalty * 2 : $basePenalty;
    }

    public function getSpeedPenalty(): int
    {
        return match($this) {
            self::FIRM => 0,
            self::GOOD => 0,
            self::SOFT => 0,
            self::HEAVY => -50,
        };
    }

    public function getStaminaDrainModifier(): float
    {
        return match($this) {
            self::FIRM => 0.0,
            self::GOOD => 0.0,
            self::SOFT => 0.02,
            self::HEAVY => 0.02,
        };
    }
}
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property Reflection

After analyzing all acceptance criteria, I identified several opportunities to consolidate related properties:

- **Hint level properties 1.1-1.5** can be combined into a single comprehensive property testing all levels
- **Aptitude bonus properties 2.3-2.5** can be combined into a single property testing all S-rank bonuses
- **Track condition properties 4.2-4.5** can be combined into a single property testing all conditions
- **Stat effectiveness properties 3.2-3.3** can be combined into a single property covering the full range

### Skill Hint System Properties

**Property 1: Hint Level Reduction Accuracy**

*For any* skill with a base SP cost and hint level (0-5), the calculated SP cost reduction should match the exact percentage for that level: 0% for level 0, 10% for level 1, 20% for level 2, 30% for level 3, 35% for level 4, and 40% for level 5.

**Validates: Requirements 1.1, 1.2, 1.3, 1.4, 1.5**

**Property 2: Hint Level Range Validation**

*For any* hint level value, the system should accept values 0-5 inclusive and reject all other values.

**Validates: Requirements 1.6**

**Property 3: SP Cost Display Completeness**

*For any* skill with a hint level > 0, the rendered UI output should contain both the base SP cost and the reduced SP cost.

**Validates: Requirements 1.7**

**Property 4: Skill Cost Migration Correctness**

*For any* skill record with an old SP cost calculation, after migration the effective SP cost should equal the base cost multiplied by (1 - hint level reduction percentage).

**Validates: Requirements 1.8, 1.9**

### Aptitude Grade System Properties

**Property 5: Aptitude Grade Validation**

*For any* aptitude grade value, the system should accept only grades G, F, E, D, C, B, A, and S, and reject all other values including SS.

**Validates: Requirements 2.1, 2.2**

**Property 6: S-Rank Aptitude Bonuses**

*For any* character with S-rank aptitude, the system should apply +5% Speed bonus for distance aptitude, +5% Power bonus for surface aptitude, and +10% Wit bonus for running style aptitude.

**Validates: Requirements 2.3, 2.4, 2.5**

**Property 7: A-Rank Baseline**

*For any* character with A-rank aptitude in any category, the system should apply exactly 0% bonus (baseline).

**Validates: Requirements 2.6**

**Property 8: Below-A-Rank Penalties**

*For any* character with aptitude grade B or lower (B, C, D, E, F, G), the system should apply a negative percentage penalty that increases as the grade decreases.

**Validates: Requirements 2.7**

**Property 9: Aptitude Grade Migration**

*For any* character record with SS grade in any aptitude category, after migration the grade should be converted to S and bonuses recalculated accordingly.

**Validates: Requirements 2.9, 2.10**

### Stat System Properties

**Property 10: Stat Range Validation**

*For any* stat value, the system should accept values from 0 to 1600 inclusive.

**Validates: Requirements 3.1**

**Property 11: Stat Effectiveness Calculation**

*For any* stat value, the effective value should equal the raw value when raw ≤ 1200, and should equal 1200 + (raw - 1200) × 0.5 when raw > 1200.

**Validates: Requirements 3.2, 3.3, 3.10**

**Property 12: Stat Display Completeness**

*For any* stat value exceeding 1200, the rendered UI output should contain both the raw value and the calculated effective value.

**Validates: Requirements 3.5**

**Property 13: Stat Migration Preservation**

*For any* character record with stat values above 1200, after migration the raw stat values should remain unchanged and effective values should be correctly calculated.

**Validates: Requirements 3.9**

### Track Condition System Properties

**Property 14: Track Condition Penalties**

*For any* race with a track condition (Firm/Good/Soft/Heavy) and surface type (Turf/Dirt), the system should apply the correct Power penalty, Speed penalty, and stamina drain modifier according to the condition, with doubled Power penalties for Dirt surfaces in wet conditions.

**Validates: Requirements 4.2, 4.3, 4.4, 4.5, 4.6**

**Property 15: Track Condition Display Separation**

*For any* race, the rendered UI output should display track condition and weather as separate, distinct fields.

**Validates: Requirements 4.7**

**Property 16: Track Condition Migration**

*For any* race record with weather-based penalties, after migration the penalties should be converted to track condition-based penalties with equivalent effects.

**Validates: Requirements 4.10**

### Training Formula Properties

**Property 17: Training Formula Completeness**

*For any* training calculation with all seven components (Base, GrowthRate, MoodMultiplier, TrainingEffect, NumSupportCards, FriendshipMultiplier), the final stat gain should equal Base × (1 + GrowthRate) × (1 + MoodMultiplier) × (1 + TrainingEffect) × (1 + 0.05 × NumSupportCards) × FriendshipMultiplier, with all multipliers applied in the correct order.

**Validates: Requirements 5.1, 5.4, 5.10**

**Property 18: Friendship Training Activation**

*For any* training calculation where Bond_Level ≥ 80, the FriendshipMultiplier should be between 1.10 and 1.35 (representing +10% to +35% bonus).

**Validates: Requirements 5.2**

**Property 19: Multi-Training Bonus**

*For any* training calculation with N support cards present (0 ≤ N ≤ 6), the support card multiplier should equal (1 + 0.05 × N), with a maximum of 1.30 at 6 cards.

**Validates: Requirements 5.3**

**Property 20: Training Formula Migration**

*For any* training prediction record, after migration the recalculated stat gain using the complete formula should replace the old prediction value.

**Validates: Requirements 5.9**

### Stamina Requirement Properties

**Property 21: Distance-Specific Stamina Ranges**

*For any* race distance (Sprint/Mile/Medium/Long) and gold stamina skill count (0-2), the recommended stamina range should match the specified values: Sprint 290-400 (0 skills) or 200-300 (2 skills), Mile 390-500 or 300-400, Medium 540-700 or 400-550, Long 790-1000 or 550-750.

**Validates: Requirements 6.1, 6.2, 6.3, 6.4, 6.5, 6.6, 6.7, 6.8**

**Property 22: Stamina Recommendation Updates**

*For any* character build, when gold stamina skills are added or removed, the stamina recommendations should update to reflect the new skill count's appropriate range.

**Validates: Requirements 6.10**

### Migration Properties

**Property 23: Migration Completeness**

*For any* migration run, all records requiring updates should be identified, processed, and either successfully updated or logged as failed, with a complete report generated.

**Validates: Requirements 7.1, 7.7, 7.8**

**Property 24: Migration Data Preservation**

*For any* record processed during migration, the original data should be backed up before changes are applied, and a rollback mechanism should be available.

**Validates: Requirements 7.10, 7.11**

## Error Handling

### Validation Errors

**Invalid Hint Level**

- **Trigger**: Hint level outside 0-5 range
- **Response**: Throw `InvalidHintLevelException` with message "Hint level must be between 0 and 5, got: {value}"
- **Recovery**: Reject the operation, log error, return validation error to user

**Invalid Aptitude Grade**

- **Trigger**: Aptitude grade not in G-S range or equals SS
- **Response**: Throw `InvalidAptitudeGradeException` with message "Aptitude grade must be G, F, E, D, C, B, A, or S, got: {value}"
- **Recovery**: Reject the operation, log error, return validation error to user

**Invalid Stat Value**

- **Trigger**: Stat value outside 0-1600 range
- **Response**: Throw `InvalidStatValueException` with message "Stat value must be between 0 and 1600, got: {value}"
- **Recovery**: Reject the operation, log error, return validation error to user

**Invalid Track Condition**

- **Trigger**: Track condition not in Firm/Good/Soft/Heavy
- **Response**: Throw `InvalidTrackConditionException` with message "Track condition must be Firm, Good, Soft, or Heavy, got: {value}"
- **Recovery**: Reject the operation, log error, return validation error to user

### Calculation Errors

**Training Formula Component Missing**

- **Trigger**: Required formula component is null or missing
- **Response**: Throw `MissingFormulaComponentException` with message "Required component {name} is missing from training calculation"
- **Recovery**: Log error, use default value (1.0 for multipliers), continue calculation with warning

**Bond Level Out of Range**

- **Trigger**: Bond level outside 0-100 range
- **Response**: Throw `InvalidBondLevelException` with message "Bond level must be between 0 and 100, got: {value}"
- **Recovery**: Clamp to valid range (0 or 100), log warning, continue calculation

**Negative Stat Gain**

- **Trigger**: Training formula produces negative result
- **Response**: Log warning "Training calculation produced negative gain: {value}"
- **Recovery**: Return 0 as minimum gain, log for investigation

### Migration Errors

**Migration Already Running**

- **Trigger**: Attempt to start migration while one is in progress
- **Response**: Throw `MigrationInProgressException` with message "Migration {name} is already running"
- **Recovery**: Reject new migration request, return status of running migration

**Migration Rollback Failed**

- **Trigger**: Rollback operation encounters error
- **Response**: Log critical error "Migration rollback failed: {error}", notify administrators
- **Recovery**: Preserve rollback data, provide manual rollback instructions

**Record Migration Failed**

- **Trigger**: Individual record fails to migrate
- **Response**: Log error with record ID and reason, increment failed count
- **Recovery**: Continue processing remaining records, include failed records in migration report

### Data Integrity Errors

**Orphaned Skill Acquisition**

- **Trigger**: Skill acquisition references non-existent skill
- **Response**: Log error "Orphaned skill acquisition found: {id}, skill_id: {skill_id}"
- **Recovery**: Skip migration for this record, include in error report

**Inconsistent Stat Values**

- **Trigger**: Effective stat value doesn't match calculated value
- **Response**: Log warning "Stat value inconsistency detected for character {id}"
- **Recovery**: Recalculate effective value, update record, log correction

**Missing Required Field**

- **Trigger**: Required field is null during migration
- **Response**: Log error "Required field {field} is null for record {id}"
- **Recovery**: Skip record, include in failed records report

## Testing Strategy

### Dual Testing Approach

This feature requires both unit testing and property-based testing for comprehensive coverage:

**Unit Tests**: Verify specific examples, edge cases, and error conditions

- Specific hint level calculations (0, 1, 5)
- Specific aptitude grades (G, A, S)
- Specific stat values (0, 1200, 1201, 1600)
- Specific track conditions (Firm, Heavy)
- Error handling scenarios
- Migration edge cases

**Property Tests**: Verify universal properties across all inputs

- All hint levels (0-5) with random base costs
- All aptitude grades (G-S) with random characters
- All stat values (0-1600) with effectiveness calculations
- All track conditions with random surfaces
- All training formula components with random parameters
- All distance/skill combinations for stamina requirements

### Property-Based Testing Configuration

**Framework**: Use Pest with property-based testing support (via pest-plugin-property or manual generators)

**Minimum Iterations**: 100 per property test

**Test Tagging**: Each property test must reference its design document property

- Format: `// Feature: game-mechanics-accuracy, Property {number}: {property_text}`

**Example Property Test Structure**:

```php
it('calculates hint level reductions correctly for all levels', function () {
    // Feature: game-mechanics-accuracy, Property 1: Hint Level Reduction Accuracy
    
    $hintLevels = [0, 1, 2, 3, 4, 5];
    $expectedReductions = [0.0, 0.10, 0.20, 0.30, 0.35, 0.40];
    
    foreach ($hintLevels as $index => $level) {
        $baseCost = fake()->numberBetween(100, 1000);
        $service = app(SkillHintCalculationService::class);
        
        $reduction = $service->calculateReduction($level);
        $finalCost = $service->calculateFinalCost($baseCost, $level);
        
        expect($reduction)->toBe($expectedReductions[$index]);
        expect($finalCost)->toBe((int)($baseCost * (1 - $expectedReductions[$index])));
    }
})->repeat(100);
```

### Test Coverage Requirements

**Service Layer**: >90% coverage

- All calculation methods
- All validation methods
- All error handling paths

**Migration Service**: >95% coverage

- All migration paths
- All rollback scenarios
- All error conditions

**Enum Methods**: 100% coverage

- All enum cases
- All calculation methods
- All label methods

### Integration Testing

**End-to-End Flows**:

1. Create skill → Set hint level → Calculate cost → Verify reduction
2. Create character → Set aptitudes → Calculate bonuses → Verify stats
3. Create character → Set stats → Calculate effectiveness → Verify soft cap
4. Create race → Set track condition → Calculate penalties → Verify modifiers
5. Create training → Set all components → Calculate gain → Verify formula
6. Run migration → Verify all records updated → Check rollback capability

**Browser Testing** (Playwright):

- Skill cost display with hint levels
- Aptitude grade selection and bonus display
- Stat display with soft cap indicators
- Track condition selection and penalty display
- Training prediction breakdown display
- Migration progress and report display

### Performance Testing

**Calculation Performance**:

- Hint level calculation: <1ms per operation
- Aptitude bonus calculation: <1ms per operation
- Stat effectiveness calculation: <1ms per operation
- Track condition penalty calculation: <1ms per operation
- Training formula calculation: <5ms per operation

**Migration Performance**:

- Process 1000 skill records: <10 seconds
- Process 1000 character records: <15 seconds
- Process 1000 race records: <10 seconds
- Process 1000 training records: <20 seconds
- Complete migration with rollback data: <2 minutes for 10,000 total records

### Regression Testing

**Prevent Regressions**:

- Lock in corrected formulas with property tests
- Test against known game data examples
- Compare with community calculator results
- Validate against Game8/UmaReference data

**Continuous Validation**:

- Run property tests on every commit
- Run integration tests on every PR
- Run performance tests weekly
- Run migration tests on staging before production

## Implementation Notes

### Phase 1: Core Services (Week 1)

1. Implement new service classes with corrected formulas
2. Implement new enums with correct values
3. Add comprehensive unit tests for all services
4. Add property-based tests for all calculations

### Phase 2: Database Updates (Week 1-2)

1. Create migration for schema updates
2. Add new columns and constraints
3. Test schema changes on development database
4. Prepare rollback scripts

### Phase 3: Migration Service (Week 2)

1. Implement MechanicsMigrationService
2. Add backup and rollback capabilities
3. Test migration on sample data
4. Add comprehensive migration tests

### Phase 4: UI Updates (Week 2-3)

1. Update all views to use corrected mechanics
2. Add visual indicators for soft caps, hint levels, etc.
3. Add tooltips explaining changes
4. Create "What's Changed" documentation page

### Phase 5: Testing & Validation (Week 3)

1. Run full test suite
2. Perform manual testing of all affected features
3. Validate against community data
4. Performance testing and optimization

### Phase 6: Deployment (Week 4)

1. Deploy to staging environment
2. Run migration on staging data
3. User acceptance testing
4. Deploy to production with monitoring

### Backward Compatibility Strategy

**Data Preservation**:

- All existing data is backed up before migration
- Raw stat values are preserved (only effective values added)
- Historical records maintain original timestamps
- Migration creates audit trail

**Rollback Plan**:

- Rollback scripts prepared for each schema change
- Migration service stores pre-migration state
- Manual rollback instructions documented
- Rollback tested on staging before production

**User Communication**:

- In-app notification about mechanics corrections
- Email to all users explaining changes
- "What's Changed" page with detailed explanations
- FAQ addressing common concerns
- Comparison tool showing old vs new calculations

---

## Document Information

**Document Type**: Design Document  
**Version**: 1.0.0  
**Date**: January 29, 2026  
**Status**: Draft  
**Feature**: game-mechanics-accuracy  
**Related**: requirements.md, SRS, SDS, DBD, Training System, Skill System, Race System
