# Phase 3: Training System Integration - Implementation Plan

**Date**: January 25, 2026  
**Status**: ✅ COMPLETE  
**Priority**: P0 (Critical for complete external API integration)

---

## Overview

Integrate imported support cards with the training system to calculate bonuses, track friendship levels, record skill hints, and provide accurate training predictions.

---

## Current State Analysis

### ✅ Already Implemented

1. **TrainingSession Model** (`app/Models/TrainingSession.php`)
   - Has `participating_support_cards` field (array)
   - Has `skill_hints_obtained` field (array)
   - Has `training_bonuses` field (array)
   - Has `friendship_training` field (boolean)
   - Has `friendship_level_bonus` field
   - Has `support_cards_present` field (array)

2. **Support Card Models**
   - `SupportCardDefinition` - Card catalog
   - `CharacterSupportCard` - Character's owned cards
   - External API integration complete (Phase 2)

3. **Character Model**
   - Has support card relationships
   - Can track deck assignments

### ❌ Missing Components

1. **Support Card Bonus Calculation**
   - No service to calculate stat bonuses from cards
   - No friendship level tracking during training
   - No bond progression system

2. **Training Prediction Integration**
   - Training predictions don't include support card bonuses
   - No skill hint probability calculations
   - No friendship training detection

3. **Deck Management**
   - No support deck assignment to characters
   - No 6-card deck validation
   - No borrowed card support

---

## Implementation Tasks

### Task 3.1: Support Deck Management ⏳

**Goal**: Allow characters to have a 6-card support deck

**Files to Create/Modify**:

- `app/Models/SupportDeck.php` (create)
- `database/migrations/YYYY_MM_DD_create_support_decks_table.php` (create)
- `app/Models/Character.php` (add relationship)

**Database Schema**:

```sql
CREATE TABLE support_decks (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    character_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) DEFAULT 'Main Deck',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE
);

CREATE TABLE support_deck_cards (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    support_deck_id BIGINT UNSIGNED NOT NULL,
    support_card_id BIGINT UNSIGNED NOT NULL,
    position INT NOT NULL CHECK (position BETWEEN 1 AND 6),
    bond_level INT DEFAULT 0 CHECK (bond_level BETWEEN 0 AND 100),
    is_borrowed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (support_deck_id) REFERENCES support_decks(id) ON DELETE CASCADE,
    FOREIGN KEY (support_card_id) REFERENCES support_card_definitions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_deck_position (support_deck_id, position)
);
```

### Task 3.2: Support Bonus Calculator Service ⏳

**Goal**: Calculate stat bonuses from support cards

**Files to Create**:

- `app/Services/Training/SupportBonusCalculator.php`

**Functionality**:

- Calculate base bonus per card (based on rarity and type)
- Apply limit break multipliers
- Calculate friendship training bonus (80+ bond = 1.2x multiplier)
- Aggregate bonuses from multiple cards
- Return bonus breakdown for UI display

**Example Output**:

```php
[
    'speed_bonus' => 15,  // +15% speed from cards
    'stamina_bonus' => 10,
    'power_bonus' => 5,
    'is_friendship' => true,
    'friendship_multiplier' => 1.2,
    'active_cards' => [
        ['id' => 30001, 'name' => 'SSR Special Week', 'bonus' => 10, 'bond' => 85],
        ['id' => 30002, 'name' => 'SSR Silence Suzuka', 'bonus' => 5, 'bond' => 75],
    ]
]
```

### Task 3.3: Training Prediction Enhancement ⏳

**Goal**: Include support card bonuses in training predictions

**Files to Modify**:

- `app/Services/TrainingPredictionService.php` (if exists, or create)
- `app/Http/Controllers/TrainingController.php`

**Functionality**:

- Load character's active support deck
- Calculate bonuses for each training facility
- Apply bonuses to base stat gains
- Display enhanced predictions in UI
- Show which cards are contributing

### Task 3.4: Skill Hint System ⏳

**Goal**: Track and apply skill hints from support cards

**Files to Create/Modify**:

- `app/Services/Training/SkillHintService.php` (create)
- `app/Models/SkillAcquisition.php` (modify)

**Database Schema Update**:

```sql
ALTER TABLE skill_acquisitions 
ADD COLUMN hint_level INT DEFAULT 0 CHECK (hint_level BETWEEN 0 AND 2),
ADD COLUMN hint_sources JSON NULL COMMENT 'Array of support card IDs that provided hints',
ADD COLUMN sp_discount_applied INT DEFAULT 0 COMMENT 'SP discount from hints (0-40%)';
```

**Functionality**:

- Track hint drops during training (5 levels: 10%/20%/30%/35%/40% max discount)
- Link hints to source support cards
- Apply SP discount when acquiring skills
- Display hint status in skill shop

### Task 3.5: Bond Level Progression ⏳

**Goal**: Track and update bond levels during training

**Files to Create**:

- `app/Services/Training/BondProgressionService.php`

**Functionality**:

- Increase bond level when card participates in training
- Base gain: +5 bond per training
- Bonus gain: +7 bond if bond < 50
- Friendship threshold: 80 bond
- Update `support_deck_cards.bond_level` after each training

### Task 3.6: Training Session Recording ⏳

**Goal**: Save complete training data including support card participation

**Files to Modify**:

- `app/Services/TrainingService.php`
- `app/Http/Controllers/TrainingController.php`

**Data to Record**:

```php
TrainingSession::create([
    'career_id' => $career->id,
    'character_id' => $character->id,
    'turn_number' => $turn,
    'training_type' => 'speed',
    'speed_gain' => 25,
    'stamina_gain' => 5,
    // ... other stats
    'participating_support_cards' => [
        ['id' => 30001, 'name' => 'SSR Special Week', 'bond_before' => 80, 'bond_after' => 85, 'bonus_provided' => 10],
        ['id' => 30002, 'name' => 'SSR Silence Suzuka', 'bond_before' => 75, 'bond_after' => 80, 'bonus_provided' => 5],
    ],
    'skill_hints_obtained' => [
        ['skill_id' => 101, 'skill_name' => 'Acceleration', 'source_card_id' => 30001],
    ],
    'training_bonuses' => [
        'support_card_bonus' => 15,
        'friendship_bonus' => 20,
        'total_multiplier' => 1.35,
    ],
    'friendship_training' => true,
    'friendship_level_bonus' => 20,
]);
```

---

## Implementation Order

### Step 1: Database Setup (30 min)

1. Create support_decks migration
2. Create support_deck_cards migration
3. Update skill_acquisitions migration
4. Run migrations

### Step 2: Models & Relationships (30 min)

1. Create SupportDeck model
2. Add Character → SupportDeck relationship
3. Add SupportDeck → SupportCardDefinition relationship
4. Update SkillAcquisition model

### Step 3: Service Layer (2 hours)

1. Create SupportBonusCalculator
2. Create SkillHintService
3. Create BondProgressionService
4. Update/Create TrainingPredictionService
5. Update/Create TrainingService

### Step 4: Controller Integration (1 hour)

1. Update TrainingController
2. Add deck management endpoints
3. Add prediction endpoints with support card data

### Step 5: Frontend Integration (2 hours)

1. Create deck builder UI component
2. Update training prediction display
3. Show support card bonuses
4. Display skill hint notifications
5. Show bond level progress

### Step 6: Testing (1 hour)

1. Unit tests for calculators
2. Feature tests for training with support cards
3. Integration tests for complete flow

---

## Success Criteria

- [ ] Characters can assign a 6-card support deck
- [ ] Training predictions include support card bonuses
- [ ] Stat gains are calculated with card bonuses
- [ ] Bond levels increase after training
- [ ] Friendship training triggers at 80+ bond
- [ ] Skill hints are tracked and applied
- [ ] SP discounts work correctly (5 levels: 10%/20%/30%/35%/40% max)
- [ ] Training sessions record all support card data
- [ ] UI displays support card contributions
- [ ] All tests pass

---

## Estimated Time

- **Total**: ~7 hours
- **Complexity**: Medium-High
- **Dependencies**: Phase 2 (Support Card Management) ✅ Complete

---

## Next Steps

1. Start with Task 3.1 (Support Deck Management)
2. Create database migrations
3. Create models and relationships
4. Implement service layer
5. Integrate with controllers
6. Update frontend
7. Write tests

---

## Notes

- Support card bonuses follow game mechanics (multiplicative stacking)
- Friendship training is a significant bonus (1.2x multiplier)
- Bond progression is linear but accelerated at low levels
- Skill hints are RNG-based but probability increases with bond
- All calculations must match game behavior for accuracy
