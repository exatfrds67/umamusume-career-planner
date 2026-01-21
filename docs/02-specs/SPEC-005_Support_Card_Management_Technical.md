# SPEC-005: Support Card Management System - Technical Specification

**Document Version**: 1.0 | **Date**: January 14, 2026 | **Status**: Draft

**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirement 6: Support Card Configuration)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Support Card Architecture)
- `.kiro/specs/umamusume-career-planner-main/tasks.md` (Task 5.x: Support Card System)

**Related Artifacts**:

- PRD: [PRD-005](../prds/PRD-005_Support_Card_Management.md)
- Flow: [FLOW-005](../flows/FLOW-005_Support_Card_Management_System.md)
- Wireframes: [WF-010](../wireframes/WF-010_Support_Card_Collection.md), [WF-011](../wireframes/WF-011_Support_Deck_Builder.md)
- Sequences: [SEQ-005](../sequences/SEQ-005_Support_Card_Upgrade.md)
- User Flows: [UF-006](../user-flows/UF-006_Support_Deck_Building_Flow.md)

## Overview

Support card management handles deck composition, card bonuses, limit break effects, bond level tracking, and skill provision databases for optimal training effectiveness.

## Core Features

### 5.1 Support Card Model

```php
class SupportCard extends Model
{
    protected $fillable = [
        'character_id',
        'card_id',
        'card_name',
        'rarity',        // SSR, SR, R
        'limit_breaks',  // 0-4 stars
        'specialization', // Speed, Power, Stamina, Guts, Wit, Pal
        'bond_level',    // 0-100%
    ];
    
    const SPECIALIZATION_BONUSES = [
        'Speed' => 8,
        'Power' => 8,
        'Stamina' => 7,
        'Guts' => 7,
        'Wit' => 8,
        'Pal' => 5,
    ];
    
    const LIMIT_BREAK_MULTIPLIERS = [0, 0.25, 0.50, 0.75, 1.0];
}
```

### 5.2 Deck Composition

```php
class DeckCompositionValidator
{
    const DECK_SIZE = 6;  // 5 owned + 1 borrowed
    
    public function validateDeck(array $cards): array
    {
        $errors = [];
        
        // Validate count
        if (count($cards) !== self::DECK_SIZE) {
            $errors[] = "Deck must contain exactly 6 cards";
        }
        
        // Validate distribution (ideal: diverse)
        $specs = array_map(fn($c) => $c['specialization'], $cards);
        $uniqueSpecs = count(array_unique($specs));
        
        if ($uniqueSpecs < 3) {
            $errors[] = "Recommend at least 3 different specializations";
        }
        
        // Validate meta recommendations
        $metaCount = $this->countMetaCards($specs);
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => [],
            'meta_card_count' => $metaCount,
            'overall_tier' => $this->calculateDeckTier($specs),
        ];
    }
    
    public function calculateDeckTier(array $specs): string
    {
        // S+ tier: All SS meta cards
        // S tier: Mix of S and SS meta
        // A tier: Mix of S and A tier cards
        // B tier: Mix of A and B tier cards
        
        return 'A';  // Example
    }
}
```

### 5.3 Card Database

```php
class SupportCardDatabase
{
    const META_CARDS = [
        'SS' => [
            'Kitasan Black' => [
                'rarity' => 'SSR',
                'specializations' => ['Power', 'Speed'],
                'skills' => ['Lane Guidance', 'Cool Breeze'],
                'tier' => 'SS',
                'effectiveness' => 0.95,
            ],
            'Narita Brian' => [
                'rarity' => 'SSR',
                'specializations' => ['Speed', 'Wit'],
                'skills' => ['Skill Power Boost', 'Lane Guidance'],
                'tier' => 'SS',
                'effectiveness' => 0.92,
            ],
            'Symboli Rudolf' => [
                'rarity' => 'SSR',
                'specializations' => ['Stamina', 'Guts'],
                'skills' => ['Stamina Boost', 'Guts Boost'],
                'tier' => 'SS',
                'effectiveness' => 0.90,
            ],
        ],
        'S' => [
            // 15-20 high-tier cards
        ],
        'A' => [
            // 25-30 mid-tier cards
        ],
    ];
    
    public function getCardMetadata(string $cardName): array
    {
        foreach (self::META_CARDS as $tier => $cards) {
            if (isset($cards[$cardName])) {
                return $cards[$cardName];
            }
        }
        return [];
    }
}
```

### 5.4 Skill Provision Tracking

```php
class SkillProvisionDatabase
{
    // Maps which skills each support card provides during training
    const CARD_SKILL_PROVISIONS = [
        'Kitasan Black' => [
            'Speed' => ['Lane Guidance', 'Cool Breeze'],
            'Power' => ['Power Acceleration'],
            'event' => ['Friendship Training Bonus'],
        ],
        'Narita Brian' => [
            'Wit' => ['Skill Power Boost', 'Lane Guidance'],
            'Speed' => ['Cool Breeze'],
            'event' => ['Skill Hint Events'],
        ],
        // ... all 200+ cards
    ];
    
    public function getSkillsForCard(string $cardName, string $facility): array
    {
        return self::CARD_SKILL_PROVISIONS[$cardName][$facility] ?? [];
    }
}
```

## API Endpoints

### GET /api/v1/support-cards

Retrieve complete support card database with metadata

### POST /api/v1/characters/{id}/deck

Configure support deck (6 cards total)

### PATCH /api/v1/characters/{id}/deck/{cardId}

Update card bond level or limit breaks

### GET /api/v1/deck-recommendations/{characterId}

AI recommendations for optimal deck composition

## Database Schema

```sql
CREATE TABLE support_card_database (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    card_id INT NOT NULL UNIQUE,
    card_name VARCHAR(255) NOT NULL,
    rarity VARCHAR(10),
    specializations JSON,
    tier VARCHAR(10),
    skills_provided JSON,
    meta_ranking VARCHAR(5),
    created_at TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE character_support_decks (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    character_id BIGINT UNSIGNED NOT NULL,
    card_order INT,
    card_id INT NOT NULL,
    card_name VARCHAR(255),
    limit_breaks INT DEFAULT 0,
    bond_level INT DEFAULT 0,
    is_borrowed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
    UNIQUE KEY unique_deck_slot (character_id, card_order),
    INDEX idx_bond_level (bond_level)
) ENGINE=InnoDB;
```

## Testing

- [ ] Deck composition validation (6 cards)
- [ ] Specialization diversity scoring
- [ ] Meta card ranking
- [ ] Skill provision lookup
- [ ] Bond level effects
- [ ] Limit break calculations
- [ ] Deck recommendation generation

---

**Related**: [PRD-005], [SPEC-002], [SPEC-006]
