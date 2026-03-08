# SPEC-005: Support Card Management System - Technical Specification

**Document Version**: 2.3.0  
**Date**: 2026-02-22  
**Project**: Umamusume Pretty Derby Career Planner  
**Status**: Complete - Implementation verified  
**Classification**: Internal - Development Team

---

## Document Information

| Attribute | Value |
| --- | --- |
| **Document ID** | SPEC-005 |
| **Related PRD** | [PRD-005: Support Card Management](../prds/PRD-005_Support_Card_Management.md) |
| **Architecture Version** | v2.3.0 |
| **Approval Status** | Approved |
| **Last Reviewed** | 2026-02-22 |

### Related Documents

**Requirements & Design**:

- [SRS Section 3.5: Support Card Management](../003_SRS_Software_Requirement_Specifications.md#35-support-card-management)
- [SDS Section 4.5: Support Card Architecture](../004_SDS_Software_Design_Specifications.md#45-support-card-module)

**Data & Integration**:

- [DBD Section 5.5: Support Card Tables](../009_DBD_Database_Documentation.md#55-support-card-tables)
- [API Section 4.5: Support Card Endpoints](../010_API_API_Documentation.md#45-support-card-endpoints)

**Visual Documentation**:

- [FLOW-005: Support Card Management System](../flows/FLOW-005_Support_Card_Management_System.md)
- [SEQ-005: Support Card Upgrade](../sequences/SEQ-005_Support_Card_Upgrade.md)
- [WF-010: Support Card Collection](../wireframes/WF-010_Support_Card_Collection.md)
- [WF-011: Support Deck Builder](../wireframes/WF-011_Support_Deck_Builder.md)
- [UF-006: Support Deck Building Flow](../user-flows/UF-006_Support_Deck_Building_Flow.md)

---

## Table of Contents

1. [Technical Overview](#1-technical-overview)
2. [Architecture Design](#2-architecture-design)
3. [Support Card System](#3-support-card-system)
4. [Deck Management](#4-deck-management)
5. [Bond & Limit Break Systems](#5-bond--limit-break-systems)
6. [Service Layer](#6-service-layer)
7. [API Specification](#7-api-specification)
8. [Database Schema](#8-database-schema)
9. [AI Integration](#9-ai-integration)
10. [Business Logic](#10-business-logic)
11. [Integration Points](#11-integration-points)
12. [Error Handling](#12-error-handling)
13. [Performance Optimization](#13-performance-optimization)
14. [Security Considerations](#14-security-considerations)
15. [Testing Strategy](#15-testing-strategy)
16. [Appendices](#16-appendices)

---

## 1. Technical Overview

### 1.1 Module Purpose

The Support Card Management System handles the complete lifecycle of support cards and deck composition. Support cards are essential training multipliers that provide stat bonuses, skill hints, and event triggers during career runs. This module manages card inventory, deck configuration, bond level progression, limit break mechanics, and meta tier list integration.

**Core Responsibilities**:

- Support card catalog with metadata synchronization
- User card inventory management with ownership tracking
- Deck composition (6 cards: 5 owned + 1 borrowed)
- Bond level tracking and progression
- Limit break system (0-4 stars)
- Training bonus calculation
- Friendship training activation
- Skill hint provision tracking
- Meta tier list integration
- Deck synergy analysis
- AI-powered deck recommendations

### 1.2 Business Context

In Umamusume Pretty Derby, support cards are the primary force multipliers for training:

- **Specializations**: Speed, Stamina, Power, Guts, Wit, Friend (Pal)
- **Bonuses**: Stat gain multipliers, skill hint rates, event frequency
- **Bond System**: Relationship level (0-100) affecting training efficiency
- **Friendship Training**: 1.2x multiplier when bond ≥ 80
- **Limit Breaks**: 0-4 stars enhancing card effects

Strategic deck composition and bond management are critical for achieving optimal training outcomes and successful career runs.

### 1.3 Technical Scope

**In Scope**:

- Support card database with external sync
- User inventory management
- Deck builder with validation (6-card composition)
- Bond level tracking and progression
- Limit break mechanics (0-4 stars)
- Training bonus aggregation
- Skill hint provision logic
- Meta tier list integration
- Synergy analysis engine
- AI-powered deck recommendations
- Deck template system

**Out of Scope**:

- Gacha/acquisition mechanics (game client)
- Card art/asset management (CDN)
- Training execution logic (SPEC-002)
- Skill acquisition (SPEC-004)

### 1.4 Technology Stack

| Component | Technology | Version | Purpose |
| --- | --- | --- | --- |
| **Framework** | Laravel | 12.x | Application foundation |
| **Language** | PHP | 8.2+ | Server-side logic |
| **Database** | MySQL | 8.0+ | Data persistence |
| **Cache** | Redis | 7.x | Card metadata caching |
| **AI** | Neuron AI | v2.11 | Deck recommendation agents |
| **AI Provider (Local)** | Ollama | Latest | Quick recommendations |
| **AI Provider (Cloud)** | AWS Bedrock Claude | 4.5 | Complex optimization |

---

## 2. Architecture Design

### 2.1 Component Architecture

```mermaid
graph TB
    subgraph "Presentation Layer"
        API[SupportCardController]
        Livewire[DeckBuilder Component]
        FormRequest[DeckRequest]
    end

    subgraph "Application Layer"
        CardSvc[SupportCardDeckService]
        DeckSvc[DeckManagementService]
        BondSvc[FriendshipBondService]
        SynergySvc[DeckOptimizationService]
        RecommendSvc[DeckRecommendationService]
    end

    subgraph "Domain Layer"
        CardModel[SupportCard Model]
        InventoryModel[UserCardInventory Model]
        DeckModel[SupportDeck Model]
        BonusCalc[BonusCalculator]
    end

    subgraph "Infrastructure Layer"
        DB[(MySQL)]
        Cache[(Redis)]
        External[ExternalDataService]
        NeuronAI[DeckOptimizationAgent]
    end

    API --> FormRequest
    FormRequest --> DeckSvc
    Livewire --> DeckSvc
    
    DeckSvc --> CardSvc
    DeckSvc --> BondSvc
    DeckSvc --> SynergySvc
    DeckSvc --> RecommendSvc
    
    RecommendSvc --> NeuronAI
    SynergySvc --> BonusCalc
    
    CardSvc --> CardModel
    CardSvc --> InventoryModel
    DeckSvc --> DeckModel
    
    CardModel --> DB
    CardModel --> Cache
    
    CardSvc --> External
```text

### 2.2 Layer Responsibilities

**Presentation Layer**:

- HTTP request/response handling
- Deck builder UI rendering
- Real-time deck validation

**Application Layer**:

- Deck composition workflows
- Bond progression management
- Synergy calculation orchestration
- AI recommendation coordination

**Domain Layer**:

- Support card business rules
- Deck composition validation
- Bonus calculation algorithms
- Bond level logic

**Infrastructure Layer**:

- Database persistence
- External API synchronization
- Cache management
- AI service integration

### 2.3 Design Patterns

| Pattern | Implementation | Purpose |
| --- | --- | --- |
| **Repository** | `SupportCardRepository` | Abstract data access |
| **Factory** | `DeckFactory` | Deck object creation |
| **Strategy** | Bonus calculators | Pluggable bonus algorithms |
| **Observer** | Event listeners | React to bond/deck changes |
| **Composite** | Deck synergy analyzer | Aggregate card effects |
| **Cache-Aside** | Card catalog | Performance optimization |

---

## 3. Support Card System

### 3.1 Support Card Model

The core support card entity representing card definitions.

```php
<?php

namespace App\Models;

use App\Enums\{SupportCardRarity, SupportCardType};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Support Card Entity
 * 
 * Represents a support card definition in the game database.
 * 
 * @property int $id
 * @property string $name
 * @property string|null $name_jp
 * @property string $character_name Associated character
 * @property SupportCardRarity $rarity
 * @property SupportCardType $type
 * @property string $specialization Speed, Stamina, Power, Guts, Wit, Friend
 * @property array $base_bonuses Base stat bonuses
 * @property array $max_bonuses Max bonuses at MLB (4 stars)
 * @property int $unique_effect_id
 * @property array $skills_provided Skills this card can hint
 * @property string|null $meta_tier SS, S, A, B, C
 * @property float|null $meta_score 0.0-10.0
 * @property string|null $icon_path
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class SupportCard extends Model
{
    use HasFactory;

    protected $table = 'ucp_support_cards';

    protected $fillable = [
        'name',
        'name_jp',
        'character_name',
        'rarity',
        'type',
        'specialization',
        'base_bonuses',
        'max_bonuses',
        'unique_effect_id',
        'skills_provided',
        'meta_tier',
        'meta_score',
        'icon_path',
    ];

    protected $casts = [
        'rarity' => SupportCardRarity::class,
        'type' => SupportCardType::class,
        'base_bonuses' => 'array',
        'max_bonuses' => 'array',
        'unique_effect_id' => 'integer',
        'skills_provided' => 'array',
        'meta_score' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships

    public function userInventories()
    {
        return $this->hasMany(UserCardInventory::class);
    }

    public function decks()
    {
        return $this->belongsToMany(SupportDeck::class, 'ucp_support_deck_cards')
            ->withPivot(['position', 'bond_level', 'is_borrowed'])
            ->withTimestamps();
    }

    // Business Methods

    /**
     * Calculate bonuses at specific limit break level
     * 
     * @param int $limitBreaks 0-4
     * @return array
     */
    public function getBonusesAtLevel(int $limitBreaks): array
    {
        $limitBreaks = max(0, min(4, $limitBreaks));
        
        $bonuses = [];
        
        foreach ($this->base_bonuses as $key => $baseValue) {
            $maxValue = $this->max_bonuses[$key] ?? $baseValue;
            $increment = ($maxValue - $baseValue) / 4; // 4 limit breaks
            
            $bonuses[$key] = $baseValue + ($increment * $limitBreaks);
        }

        return $bonuses;
    }

    /**
     * Check if card provides specific skill
     * 
     * @param int $skillId
     * @return bool
     */
    public function providesSkill(int $skillId): bool
    {
        return in_array($skillId, $this->skills_provided ?? []);
    }

    /**
     * Get training bonus for specific stat
     * 
     * @param string $stat
     * @param int $limitBreaks
     * @return int
     */
    public function getStatBonus(string $stat, int $limitBreaks = 0): int
    {
        $bonuses = $this->getBonusesAtLevel($limitBreaks);
        
        $bonusKey = match ($stat) {
            'speed' => 'speed_bonus',
            'stamina' => 'stamina_bonus',
            'power' => 'power_bonus',
            'guts' => 'guts_bonus',
            'wit' => 'wit_bonus',
            default => null,
        };

        return $bonusKey ? ($bonuses[$bonusKey] ?? 0) : 0;
    }

    /**
     * Check if card is meta relevant
     * 
     * @return bool
     */
    public function isMetaTier(): bool
    {
        return in_array($this->meta_tier, ['SS', 'S', 'A']);
    }
}
```

### 3.2 Enumerations

**SupportCardRarity**:

```php
<?php

namespace App\Enums;

enum SupportCardRarity: string
{
    case SSR = 'SSR';
    case SR = 'SR';
    case R = 'R';
    
    public function getBaseBonusMultiplier(): float
    {
        return match($this) {
            self::SSR => 1.0,
            self::SR => 0.8,
            self::R => 0.6,
        };
    }
}
```text

**SupportCardType**:

```php
<?php

namespace App\Enums;

enum SupportCardType: string
{
    case Speed = 'speed';
    case Stamina = 'stamina';
    case Power = 'power';
    case Guts = 'guts';
    case Wit = 'wit';
    case Friend = 'friend';
}
```

### 3.3 User Card Inventory

Tracks user-owned cards with limit breaks and metadata.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * User Card Inventory Entity
 * 
 * Represents a user's ownership of a specific support card.
 * 
 * @property int $id
 * @property string $user_id
 * @property int $support_card_id
 * @property int $limit_breaks 0-4 stars
 * @property int $level Current level (1-50)
 * @property bool $is_favorite
 * @property \Carbon\Carbon $acquired_at
 * @property \Carbon\Carbon $updated_at
 */
class UserCardInventory extends Model
{
    protected $table = 'ucp_user_card_inventory';

    const CREATED_AT = 'acquired_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'user_id',
        'support_card_id',
        'limit_breaks',
        'level',
        'is_favorite',
    ];

    protected $casts = [
        'user_id' => 'string',
        'support_card_id' => 'integer',
        'limit_breaks' => 'integer',
        'level' => 'integer',
        'is_favorite' => 'boolean',
        'acquired_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'limit_breaks' => 0,
        'level' => 1,
        'is_favorite' => false,
    ];

    // Relationships

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function supportCard()
    {
        return $this->belongsTo(SupportCard::class);
    }

    // Business Methods

    /**
     * Add limit break
     * 
     * @return void
     */
    public function addLimitBreak(): void
    {
        if ($this->limit_breaks < 4) {
            $this->limit_breaks++;
        }
    }

    /**
     * Check if max limit break
     * 
     * @return bool
     */
    public function isMaxLimitBreak(): bool
    {
        return $this->limit_breaks === 4;
    }

    /**
     * Get current bonuses
     * 
     * @return array
     */
    public function getCurrentBonuses(): array
    {
        return $this->supportCard->getBonusesAtLevel($this->limit_breaks);
    }
}
```text

---

## 4. Deck Management

### 4.1 Support Deck Model

Represents a configured deck of 6 support cards.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Support Deck Entity
 * 
 * Represents a 6-card support deck configuration.
 * 
 * @property int $id
 * @property string $user_id
 * @property string $name Deck name
 * @property string|null $description
 * @property bool $is_active Currently selected deck
 * @property string|null $meta_rating Overall deck rating
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class SupportDeck extends Model
{
    use HasFactory;

    protected $table = 'ucp_support_decks';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'is_active',
        'meta_rating',
    ];

    protected $casts = [
        'user_id' => 'string',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'is_active' => false,
    ];

    // Relationships

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cards()
    {
        return $this->belongsToMany(SupportCard::class, 'ucp_support_deck_cards')
            ->withPivot(['position', 'bond_level', 'is_borrowed'])
            ->withTimestamps()
            ->orderBy('pivot_position');
    }

    public function careerRuns()
    {
        return $this->hasMany(CareerRun::class);
    }

    // Business Methods

    /**
     * Get card at specific position
     * 
     * @param int $position 1-6
     * @return SupportCard|null
     */
    public function getCardAtPosition(int $position): ?SupportCard
    {
        return $this->cards()->wherePivot('position', $position)->first();
    }

    /**
     * Get borrowed card
     * 
     * @return SupportCard|null
     */
    public function getBorrowedCard(): ?SupportCard
    {
        return $this->cards()->wherePivot('is_borrowed', true)->first();
    }

    /**
     * Get owned cards count
     * 
     * @return int
     */
    public function getOwnedCardsCount(): int
    {
        return $this->cards()->wherePivot('is_borrowed', false)->count();
    }

    /**
     * Validate deck composition
     * 
     * @return array{valid: bool, errors: array}
     */
    public function validate(): array
    {
        $errors = [];

        // Must have exactly 6 cards
        if ($this->cards->count() !== 6) {
            $errors[] = 'Deck must contain exactly 6 cards';
        }

        // Must have exactly 1 borrowed card
        $borrowedCount = $this->cards->where('pivot.is_borrowed', true)->count();
        if ($borrowedCount !== 1) {
            $errors[] = 'Deck must have exactly 1 borrowed card';
        }

        // Must have 5 owned cards
        $ownedCount = $this->cards->where('pivot.is_borrowed', false)->count();
        if ($ownedCount !== 5) {
            $errors[] = 'Deck must have exactly 5 owned cards';
        }

        // No duplicate cards
        $cardIds = $this->cards->pluck('id')->toArray();
        if (count($cardIds) !== count(array_unique($cardIds))) {
            $errors[] = 'Deck cannot contain duplicate cards';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Get type distribution
     * 
     * @return array
     */
    public function getTypeDistribution(): array
    {
        $distribution = [];
        
        foreach ($this->cards as $card) {
            $type = $card->specialization;
            $distribution[$type] = ($distribution[$type] ?? 0) + 1;
        }

        return $distribution;
    }

    /**
     * Calculate average bond level
     * 
     * @return float
     */
    public function getAverageBondLevel(): float
    {
        $totalBond = $this->cards->sum('pivot.bond_level');
        $cardCount = $this->cards->count();

        return $cardCount > 0 ? $totalBond / $cardCount : 0;
    }
}
```

### 4.2 Deck Card Pivot

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Support Deck Card Pivot
 * 
 * Pivot table with additional deck-specific data.
 * 
 * @property int $support_deck_id
 * @property int $support_card_id
 * @property int $position 1-6
 * @property int $bond_level 0-100
 * @property bool $is_borrowed
 */
class SupportDeckCard extends Pivot
{
    protected $table = 'ucp_support_deck_cards';

    protected $fillable = [
        'support_deck_id',
        'support_card_id',
        'position',
        'bond_level',
        'is_borrowed',
    ];

    protected $casts = [
        'support_deck_id' => 'integer',
        'support_card_id' => 'integer',
        'position' => 'integer',
        'bond_level' => 'integer',
        'is_borrowed' => 'boolean',
    ];

    protected $attributes = [
        'bond_level' => 0,
        'is_borrowed' => false,
    ];

    /**
     * Check if friendship training active
     * 
     * @return bool
     */
    public function isFriendshipActive(): bool
    {
        return $this->bond_level >= 80;
    }

    /**
     * Get bond tier
     * 
     * @return string
     */
    public function getBondTier(): string
    {
        return match (true) {
            $this->bond_level >= 80 => 'max',
            $this->bond_level >= 60 => 'high',
            $this->bond_level >= 40 => 'medium',
            $this->bond_level >= 20 => 'low',
            default => 'minimal',
        };
    }
}
```text

### 4.3 Deck Composition Validator

```php
<?php

namespace App\Services\SupportCard;

use App\Models\{SupportDeck, SupportCard, UserCardInventory};

/**
 * Deck Composition Validator
 * 
 * Validates deck composition rules and constraints.
 */
class DeckManagementValidator
{
    private const DECK_SIZE = 6;
    private const OWNED_CARDS = 5;
    private const BORROWED_CARDS = 1;

    /**
     * Validate deck configuration
     * 
     * @param array $cardConfigs Array of [card_id, is_borrowed, position]
     * @param string $userId
     * @return array{valid: bool, errors: array, warnings: array}
     */
    public function validate(array $cardConfigs, string $userId): array
    {
        $errors = [];
        $warnings = [];

        // Validate count
        if (count($cardConfigs) !== self::DECK_SIZE) {
            $errors[] = "Deck must contain exactly " . self::DECK_SIZE . " cards";
            return ['valid' => false, 'errors' => $errors, 'warnings' => $warnings];
        }

        // Validate ownership
        $ownedCards = collect($cardConfigs)->where('is_borrowed', false);
        $borrowedCards = collect($cardConfigs)->where('is_borrowed', true);

        if ($ownedCards->count() !== self::OWNED_CARDS) {
            $errors[] = "Must have exactly " . self::OWNED_CARDS . " owned cards";
        }

        if ($borrowedCards->count() !== self::BORROWED_CARDS) {
            $errors[] = "Must have exactly " . self::BORROWED_CARDS . " borrowed card";
        }

        // Validate user owns the cards marked as owned
        $ownedCardIds = $ownedCards->pluck('card_id')->toArray();
        $userOwnedCount = UserCardInventory::where('user_id', $userId)
            ->whereIn('support_card_id', $ownedCardIds)
            ->count();

        if ($userOwnedCount !== count($ownedCardIds)) {
            $errors[] = "User does not own all marked cards";
        }

        // Validate no duplicates
        $allCardIds = collect($cardConfigs)->pluck('card_id')->toArray();
        if (count($allCardIds) !== count(array_unique($allCardIds))) {
            $errors[] = "Deck cannot contain duplicate cards";
        }

        // Validate positions
        $positions = collect($cardConfigs)->pluck('position')->toArray();
        $expectedPositions = range(1, self::DECK_SIZE);
        if (array_diff($expectedPositions, $positions) || array_diff($positions, $expectedPositions)) {
            $errors[] = "Invalid position configuration (must be 1-6)";
        }

        // Warnings for suboptimal composition
        $warnings = array_merge($warnings, $this->analyzeComposition($cardConfigs));

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Analyze deck composition for warnings
     * 
     * @param array $cardConfigs
     * @return array
     */
    private function analyzeComposition(array $cardConfigs): array
    {
        $warnings = [];
        $cardIds = collect($cardConfigs)->pluck('card_id');
        $cards = SupportCard::whereIn('id', $cardIds)->get();

        // Check type diversity
        $types = $cards->pluck('specialization')->unique();
        if ($types->count() < 3) {
            $warnings[] = "Low type diversity. Recommend at least 3 different specializations.";
        }

        // Check meta tier
        $metaCards = $cards->whereIn('meta_tier', ['SS', 'S'])->count();
        if ($metaCards < 3) {
            $warnings[] = "Consider using more meta-tier cards for optimal performance.";
        }

        // Check for all same type
        if ($types->count() === 1) {
            $warnings[] = "Deck has only one card type. This limits training flexibility.";
        }

        return $warnings;
    }
}
```

---

## 5. Bond & Limit Break Systems

### 5.1 Bond Progression Service

Manages bond level increases during training.

```php
<?php

namespace App\Services\SupportCard;

use App\Models\{SupportDeck, SupportCard};
use Illuminate\Support\Facades\DB;

/**
 * Bond Progression Service
 * 
 * Handles bond level increases and friendship training activation.
 */
class BondProgressionService
{
    private const MAX_BOND = 100;
    private const FRIENDSHIP_THRESHOLD = 80;

    /**
     * Increase bond level for cards
     * 
     * @param SupportDeck $deck
     * @param array $bondGains Array of [card_id => gain_amount]
     * @return array Updated bond levels
     */
    public function increaseBond(SupportDeck $deck, array $bondGains): array
    {
        $updated = [];

        DB::transaction(function () use ($deck, $bondGains, &$updated) {
            foreach ($bondGains as $cardId => $gain) {
                $currentBond = $deck->cards()
                    ->where('support_card_id', $cardId)
                    ->first()
                    ?->pivot
                    ?->bond_level ?? 0;

                $newBond = min(self::MAX_BOND, $currentBond + $gain);

                $deck->cards()->updateExistingPivot($cardId, [
                    'bond_level' => $newBond,
                ]);

                $updated[$cardId] = [
                    'previous' => $currentBond,
                    'current' => $newBond,
                    'gained' => $newBond - $currentBond,
                    'threshold_crossed' => $this->checkThresholdCrossed($currentBond, $newBond),
                ];
            }
        });

        return $updated;
    }

    /**
     * Calculate bond gain for training session
     * 
     * @param SupportCard $card
     * @param int $currentBond
     * @param bool $wasAtFacility
     * @return int
     */
    public function calculateBondGain(
        SupportCard $card,
        int $currentBond,
        bool $wasAtFacility
    ): int {
        if (!$wasAtFacility) {
            return 0;
        }

        // Base gain
        $baseGain = 5;

        // Higher gains at lower bond levels
        if ($currentBond < 40) {
            $baseGain = 7;
        } elseif ($currentBond < 70) {
            $baseGain = 6;
        }

        // Rarity bonus
        $rarityBonus = match ($card->rarity) {
            SupportCardRarity::SSR => 1,
            SupportCardRarity::SR => 0,
            SupportCardRarity::R => -1,
        };

        return max(1, $baseGain + $rarityBonus);
    }

    /**
     * Check if bond threshold was crossed
     * 
     * @param int $previous
     * @param int $current
     * @return array
     */
    private function checkThresholdCrossed(int $previous, int $current): array
    {
        $thresholds = [20, 40, 60, 80, 100];
        $crossed = [];

        foreach ($thresholds as $threshold) {
            if ($previous < $threshold && $current >= $threshold) {
                $crossed[] = $threshold;
            }
        }

        return $crossed;
    }

    /**
     * Get friendship status for deck
     * 
     * @param SupportDeck $deck
     * @return array
     */
    public function getFriendshipStatus(SupportDeck $deck): array
    {
        $status = [];

        foreach ($deck->cards as $card) {
            $bondLevel = $card->pivot->bond_level;
            
            $status[] = [
                'card_id' => $card->id,
                'card_name' => $card->name,
                'bond_level' => $bondLevel,
                'is_friendship_active' => $bondLevel >= self::FRIENDSHIP_THRESHOLD,
                'progress_to_friendship' => max(0, self::FRIENDSHIP_THRESHOLD - $bondLevel),
            ];
        }

        return $status;
    }
}
```text

### 5.2 Limit Break Service

```php
<?php

namespace App\Services\SupportCard;

use App\Models\UserCardInventory;
use App\Exceptions\{MaxLimitBreakException, InsufficientResourcesException};

/**
 * Limit Break Service
 * 
 * Handles limit break (uncap) operations.
 */
class LimitBreakService
{
    private const MAX_LIMIT_BREAKS = 4;

    /**
     * Add limit break to card
     * 
     * @param UserCardInventory $inventory
     * @return UserCardInventory
     * @throws MaxLimitBreakException
     */
    public function addLimitBreak(UserCardInventory $inventory): UserCardInventory
    {
        if ($inventory->isMaxLimitBreak()) {
            throw new MaxLimitBreakException(
                "Card already at maximum limit break (4 stars)"
            );
        }

        $inventory->addLimitBreak();
        $inventory->save();

        // Fire event
        event(new \App\Events\SupportCardLimitBroken($inventory));

        return $inventory;
    }

    /**
     * Get limit break benefits preview
     * 
     * @param UserCardInventory $inventory
     * @return array
     */
    public function getNextLimitBreakBenefits(UserCardInventory $inventory): array
    {
        if ($inventory->isMaxLimitBreak()) {
            return [
                'possible' => false,
                'message' => 'Already at maximum limit break',
            ];
        }

        $currentBonuses = $inventory->getCurrentBonuses();
        $nextBonuses = $inventory->supportCard->getBonusesAtLevel(
            $inventory->limit_breaks + 1
        );

        $improvements = [];
        foreach ($currentBonuses as $key => $current) {
            $next = $nextBonuses[$key] ?? $current;
            if ($next > $current) {
                $improvements[$key] = [
                    'current' => $current,
                    'next' => $next,
                    'increase' => $next - $current,
                ];
            }
        }

        return [
            'possible' => true,
            'current_stars' => $inventory->limit_breaks,
            'next_stars' => $inventory->limit_breaks + 1,
            'improvements' => $improvements,
        ];
    }

    /**
     * Get limit break cost (if applicable)
     * 
     * @param UserCardInventory $inventory
     * @return array
     */
    public function getLimitBreakCost(UserCardInventory $inventory): array
    {
        // In the actual game, this would require duplicate cards or special items
        // For planning purposes, we can return metadata
        
        return [
            'current_stars' => $inventory->limit_breaks,
            'max_stars' => self::MAX_LIMIT_BREAKS,
            'remaining' => self::MAX_LIMIT_BREAKS - $inventory->limit_breaks,
        ];
    }
}
```

---

## 6. Service Layer

### 6.1 Support Card Service

Main orchestration service for support card operations.

```php
<?php

namespace App\Services\SupportCard;

use App\Models\{SupportCard, UserCardInventory};
use App\Repositories\SupportCardRepository;
use App\Services\External\ExternalDataService;
use Illuminate\Support\Facades\{DB, Cache};

/**
 * Support Card Management Service
 * 
 * Handles support card-related business operations.
 */
class SupportCardDeckService
{
    public function __construct(
        private SupportCardRepository $repository,
        private ExternalDataService $externalApi
    ) {}

    /**
     * Get user's card inventory
     * 
     * @param string $userId
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUserInventory(string $userId, array $filters = [])
    {
        $query = UserCardInventory::where('user_id', $userId)
            ->with('supportCard');

        // Filter by type
        if (!empty($filters['type'])) {
            $query->whereHas('supportCard', function ($q) use ($filters) {
                $q->where('specialization', $filters['type']);
            });
        }

        // Filter by rarity
        if (!empty($filters['rarity'])) {
            $query->whereHas('supportCard', function ($q) use ($filters) {
                $q->where('rarity', $filters['rarity']);
            });
        }

        // Filter by limit breaks
        if (isset($filters['min_limit_breaks'])) {
            $query->where('limit_breaks', '>=', $filters['min_limit_breaks']);
        }

        // Filter by favorites
        if (!empty($filters['favorites_only'])) {
            $query->where('is_favorite', true);
        }

        // Sort
        $sortBy = $filters['sort_by'] ?? 'acquired_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Add card to user inventory
     * 
     * @param string $userId
     * @param int $supportCardId
     * @param int $limitBreaks
     * @return UserCardInventory
     */
    public function addToInventory(
        string $userId,
        int $supportCardId,
        int $limitBreaks = 0
    ): UserCardInventory {
        // Check if already owned
        $existing = UserCardInventory::where('user_id', $userId)
            ->where('support_card_id', $supportCardId)
            ->first();

        if ($existing) {
            // Add limit break instead
            if (!$existing->isMaxLimitBreak()) {
                $existing->addLimitBreak();
                $existing->save();
            }
            return $existing;
        }

        // Create new inventory entry
        return UserCardInventory::create([
            'user_id' => $userId,
            'support_card_id' => $supportCardId,
            'limit_breaks' => min(4, max(0, $limitBreaks)),
            'level' => 1,
        ]);
    }

    /**
     * Search support card catalog
     * 
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function searchCatalog(array $filters = [])
    {
        $query = SupportCard::query();

        // Text search
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('character_name', 'like', "%{$filters['search']}%");
            });
        }

        // Type filter
        if (!empty($filters['type'])) {
            $query->where('specialization', $filters['type']);
        }

        // Rarity filter
        if (!empty($filters['rarity'])) {
            $query->where('rarity', $filters['rarity']);
        }

        // Meta tier filter
        if (!empty($filters['meta_tier'])) {
            $query->where('meta_tier', $filters['meta_tier']);
        }

        // Sort
        $sortBy = $filters['sort_by'] ?? 'meta_score';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Sync support card definitions from external API
     * 
     * @return int
     */
    public function syncCardDefinitions(): int
    {
        $externalCards = $this->externalApi->getSupportCards();
        $syncedCount = 0;

        foreach ($externalCards as $externalCard) {
            $this->repository->updateOrCreate(
                ['id' => $externalCard['id']],
                [
                    'name' => $externalCard['name'],
                    'name_jp' => $externalCard['name_jp'] ?? null,
                    'character_name' => $externalCard['character_name'],
                    'rarity' => $externalCard['rarity'],
                    'type' => $externalCard['type'],
                    'specialization' => $externalCard['specialization'],
                    'base_bonuses' => $externalCard['base_bonuses'],
                    'max_bonuses' => $externalCard['max_bonuses'],
                    'unique_effect_id' => $externalCard['unique_effect_id'] ?? null,
                    'skills_provided' => $externalCard['skills_provided'] ?? [],
                    'meta_tier' => $externalCard['meta_tier'] ?? null,
                    'meta_score' => $externalCard['meta_score'] ?? null,
                    'icon_path' => $externalCard['icon_url'] ?? null,
                ]
            );

            $syncedCount++;
        }

        Cache::tags(['support-cards'])->flush();

        return $syncedCount;
    }

    /**
     * Get meta tier rankings
     * 
     * @param string|null $type Filter by card type
     * @return \Illuminate\Support\Collection
     */
    public function getMetaTierRankings(?string $type = null)
    {
        return Cache::tags(['support-cards'])->remember(
            "meta:rankings:" . ($type ?? 'all'),
            now()->addDay(),
            function () use ($type) {
                $query = SupportCard::whereNotNull('meta_tier')
                    ->whereNotNull('meta_score')
                    ->orderByRaw("FIELD(meta_tier, 'SS', 'S', 'A', 'B', 'C')")
                    ->orderBy('meta_score', 'desc');

                if ($type) {
                    $query->where('specialization', $type);
                }

                return $query->get();
            }
        );
    }
}
```text

### 6.2 Deck Management Service

```php
<?php

namespace App\Services\SupportCard;

use App\Models\{SupportDeck, SupportCard, UserCardInventory};
use App\Exceptions\{InvalidDeckCompositionException};
use Illuminate\Support\Facades\DB;

/**
 * Deck Management Service
 * 
 * Handles support deck creation, updates, and validation.
 */
class DeckManagementService
{
    public function __construct(
        private DeckManagementValidator $validator
    ) {}

    /**
     * Create new support deck
     * 
     * @param string $userId
     * @param string $name
     * @param array $cardConfigs
     * @return SupportDeck
     * @throws InvalidDeckCompositionException
     */
    public function createDeck(
        string $userId,
        string $name,
        array $cardConfigs
    ): SupportDeck {
        // Validate composition
        $validation = $this->validator->validate($cardConfigs, $userId);
        
        if (!$validation['valid']) {
            throw new InvalidDeckCompositionException(
                "Invalid deck composition: " . implode(', ', $validation['errors'])
            );
        }

        return DB::transaction(function () use ($userId, $name, $cardConfigs) {
            // Create deck
            $deck = SupportDeck::create([
                'user_id' => $userId,
                'name' => $name,
            ]);

            // Attach cards
            foreach ($cardConfigs as $config) {
                $deck->cards()->attach($config['card_id'], [
                    'position' => $config['position'],
                    'bond_level' => $config['bond_level'] ?? 0,
                    'is_borrowed' => $config['is_borrowed'],
                ]);
            }

            return $deck->fresh(['cards']);
        });
    }

    /**
     * Update deck configuration
     * 
     * @param SupportDeck $deck
     * @param array $cardConfigs
     * @return SupportDeck
     * @throws InvalidDeckCompositionException
     */
    public function updateDeck(SupportDeck $deck, array $cardConfigs): SupportDeck
    {
        // Validate composition
        $validation = $this->validator->validate($cardConfigs, $deck->user_id);
        
        if (!$validation['valid']) {
            throw new InvalidDeckCompositionException(
                "Invalid deck composition: " . implode(', ', $validation['errors'])
            );
        }

        return DB::transaction(function () use ($deck, $cardConfigs) {
            // Detach all current cards
            $deck->cards()->detach();

            // Attach new configuration
            foreach ($cardConfigs as $config) {
                $deck->cards()->attach($config['card_id'], [
                    'position' => $config['position'],
                    'bond_level' => $config['bond_level'] ?? 0,
                    'is_borrowed' => $config['is_borrowed'],
                ]);
            }

            return $deck->fresh(['cards']);
        });
    }

    /**
     * Set active deck for user
     * 
     * @param string $userId
     * @param int $deckId
     * @return SupportDeck
     */
    public function setActiveDeck(string $userId, int $deckId): SupportDeck
    {
        return DB::transaction(function () use ($userId, $deckId) {
            // Deactivate all user decks
            SupportDeck::where('user_id', $userId)
                ->update(['is_active' => false]);

            // Activate selected deck
            $deck = SupportDeck::where('user_id', $userId)
                ->where('id', $deckId)
                ->firstOrFail();

            $deck->is_active = true;
            $deck->save();

            return $deck;
        });
    }

    /**
     * Clone existing deck
     * 
     * @param SupportDeck $sourceDeck
     * @param string $newName
     * @return SupportDeck
     */
    public function cloneDeck(SupportDeck $sourceDeck, string $newName): SupportDeck
    {
        return DB::transaction(function () use ($sourceDeck, $newName) {
            // Create new deck
            $newDeck = SupportDeck::create([
                'user_id' => $sourceDeck->user_id,
                'name' => $newName,
                'description' => $sourceDeck->description,
            ]);

            // Copy card configuration
            foreach ($sourceDeck->cards as $card) {
                $newDeck->cards()->attach($card->id, [
                    'position' => $card->pivot->position,
                    'bond_level' => 0, // Reset bond for new deck
                    'is_borrowed' => $card->pivot->is_borrowed,
                ]);
            }

            return $newDeck->fresh(['cards']);
        });
    }

    /**
     * Get user's decks
     * 
     * @param string $userId
     * @return \Illuminate\Support\Collection
     */
    public function getUserDecks(string $userId)
    {
        return SupportDeck::where('user_id', $userId)
            ->with(['cards'])
            ->orderBy('is_active', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get();
    }
}
```

### 6.3 Deck Synergy Service

```php
<?php

namespace App\Services\SupportCard;

use App\Models\SupportDeck;

/**
 * Deck Synergy Analysis Service
 * 
 * Analyzes deck composition for synergies and optimization.
 */
class DeckSynergyService
{
    /**
     * Analyze deck synergy
     * 
     * @param SupportDeck $deck
     * @return array
     */
    public function analyzeSynergy(SupportDeck $deck): array
    {
        return [
            'type_distribution' => $this->analyzeTypeDistribution($deck),
            'stat_coverage' => $this->analyzeStatCoverage($deck),
            'skill_coverage' => $this->analyzeSkillCoverage($deck),
            'meta_rating' => $this->calculateMetaRating($deck),
            'friendship_potential' => $this->analyzeFriendshipPotential($deck),
            'overall_score' => $this->calculateOverallScore($deck),
        ];
    }

    /**
     * Analyze type distribution
     * 
     * @param SupportDeck $deck
     * @return array
     */
    private function analyzeTypeDistribution(SupportDeck $deck): array
    {
        $distribution = $deck->getTypeDistribution();
        
        $diversity = count($distribution);
        $balance = $this->calculateBalance($distribution);

        return [
            'distribution' => $distribution,
            'diversity_score' => $diversity / 6 * 100, // 0-100
            'balance_score' => $balance,
            'recommendation' => $this->getDistributionRecommendation($diversity, $balance),
        ];
    }

    /**
     * Calculate distribution balance
     * 
     * @param array $distribution
     * @return float
     */
    private function calculateBalance(array $distribution): float
    {
        $total = array_sum($distribution);
        $ideal = $total / count($distribution);
        
        $variance = 0;
        foreach ($distribution as $count) {
            $variance += pow($count - $ideal, 2);
        }
        
        $stdDev = sqrt($variance / count($distribution));
        
        // Lower std dev = better balance (max score 100)
        return max(0, 100 - ($stdDev * 30));
    }

    /**
     * Analyze stat coverage
     * 
     * @param SupportDeck $deck
     * @return array
     */
    private function analyzeStatCoverage(SupportDeck $deck): array
    {
        $stats = ['speed' => 0, 'stamina' => 0, 'power' => 0, 'guts' => 0, 'wit' => 0];
        
        foreach ($deck->cards as $card) {
            $bonuses = $card->getBonusesAtLevel($card->pivot->limit_breaks ?? 0);
            
            foreach ($stats as $stat => $value) {
                $bonusKey = "{$stat}_bonus";
                $stats[$stat] += $bonuses[$bonusKey] ?? 0;
            }
        }

        return [
            'total_bonuses' => $stats,
            'coverage_score' => $this->calculateCoverageScore($stats),
        ];
    }

    /**
     * Calculate stat coverage score
     * 
     * @param array $stats
     * @return float
     */
    private function calculateCoverageScore(array $stats): float
    {
        $nonZero = count(array_filter($stats, fn($v) => $v > 0));
        return ($nonZero / count($stats)) * 100;
    }

    /**
     * Analyze skill coverage
     * 
     * @param SupportDeck $deck
     * @return array
     */
    private function analyzeSkillCoverage(SupportDeck $deck): array
    {
        $allSkills = [];
        
        foreach ($deck->cards as $card) {
            $allSkills = array_merge($allSkills, $card->skills_provided ?? []);
        }

        $uniqueSkills = array_unique($allSkills);

        return [
            'total_skills' => count($uniqueSkills),
            'skills' => $uniqueSkills,
            'coverage_score' => min(100, count($uniqueSkills) * 5), // 5 points per skill
        ];
    }

    /**
     * Calculate meta rating
     * 
     * @param SupportDeck $deck
     * @return array
     */
    private function calculateMetaRating(SupportDeck $deck): array
    {
        $metaCards = $deck->cards->whereIn('meta_tier', ['SS', 'S', 'A']);
        $avgScore = $deck->cards->avg('meta_score') ?? 0;

        $tierCounts = [
            'SS' => $deck->cards->where('meta_tier', 'SS')->count(),
            'S' => $deck->cards->where('meta_tier', 'S')->count(),
            'A' => $deck->cards->where('meta_tier', 'A')->count(),
        ];

        return [
            'meta_card_count' => $metaCards->count(),
            'average_meta_score' => round($avgScore, 2),
            'tier_distribution' => $tierCounts,
            'meta_rating' => $this->getOverallMetaTier($tierCounts, $avgScore),
        ];
    }

    /**
     * Get overall meta tier
     * 
     * @param array $tierCounts
     * @param float $avgScore
     * @return string
     */
    private function getOverallMetaTier(array $tierCounts, float $avgScore): string
    {
        if ($tierCounts['SS'] >= 4) return 'SS';
        if ($tierCounts['SS'] >= 2 && $tierCounts['S'] >= 2) return 'S+';
        if ($tierCounts['S'] >= 3) return 'S';
        if ($avgScore >= 7.0) return 'A+';
        if ($avgScore >= 6.0) return 'A';
        if ($avgScore >= 5.0) return 'B';
        return 'C';
    }

    /**
     * Analyze friendship potential
     * 
     * @param SupportDeck $deck
     * @return array
     */
    private function analyzeFriendshipPotential(SupportDeck $deck): array
    {
        $bondService = app(BondProgressionService::class);
        $friendshipStatus = $bondService->getFriendshipStatus($deck);

        $activeCount = collect($friendshipStatus)
            ->where('is_friendship_active', true)
            ->count();

        return [
            'active_friendship_count' => $activeCount,
            'potential_score' => ($activeCount / 6) * 100,
            'cards_status' => $friendshipStatus,
        ];
    }

    /**
     * Calculate overall deck score
     * 
     * @param SupportDeck $deck
     * @return float
     */
    private function calculateOverallScore(SupportDeck $deck): float
    {
        $synergy = $this->analyzeSynergy($deck);

        $weights = [
            'type_diversity' => 0.20,
            'stat_coverage' => 0.25,
            'skill_coverage' => 0.20,
            'meta_rating' => 0.25,
            'friendship_potential' => 0.10,
        ];

        $score = 0;
        $score += ($synergy['type_distribution']['diversity_score'] ?? 0) * $weights['type_diversity'];
        $score += ($synergy['stat_coverage']['coverage_score'] ?? 0) * $weights['stat_coverage'];
        $score += ($synergy['skill_coverage']['coverage_score'] ?? 0) * $weights['skill_coverage'];
        $score += ($synergy['meta_rating']['average_meta_score'] ?? 0) * 10 * $weights['meta_rating'];
        $score += ($synergy['friendship_potential']['potential_score'] ?? 0) * $weights['friendship_potential'];

        return round($score, 2);
    }

    /**
     * Get distribution recommendation
     * 
     * @param int $diversity
     * @param float $balance
     * @return string
     */
    private function getDistributionRecommendation(int $diversity, float $balance): string
    {
        if ($diversity < 3) {
            return "Low diversity. Add more card types for flexibility.";
        }

        if ($balance < 60) {
            return "Unbalanced distribution. Consider evening out card types.";
        }

        if ($diversity >= 5 && $balance >= 80) {
            return "Excellent diversity and balance!";
        }

        return "Good distribution. Minor optimizations possible.";
    }
}
```text

---

## 7. API Specification

### 7.1 Endpoint Overview

| Method | Endpoint | Description | Auth Required |
| --- | --- | --- | --- |
| GET | `/api/v1/support-cards` | List card catalog | Yes |
| GET | `/api/v1/support-cards/{id}` | Get card details | Yes |
| GET | `/api/v1/users/inventory` | Get user's card inventory | Yes |
| POST | `/api/v1/users/inventory` | Add card to inventory | Yes |
| PATCH | `/api/v1/users/inventory/{id}/limit-break` | Add limit break | Yes |
| GET | `/api/v1/support-decks` | List user's decks | Yes |
| POST | `/api/v1/support-decks` | Create new deck | Yes |
| PUT | `/api/v1/support-decks/{id}` | Update deck | Yes |
| DELETE | `/api/v1/support-decks/{id}` | Delete deck | Yes |
| POST | `/api/v1/support-decks/{id}/activate` | Set as active deck | Yes |
| GET | `/api/v1/support-decks/{id}/synergy` | Get synergy analysis | Yes |

### 7.2 List Support Cards

**Endpoint**: `GET /api/v1/support-cards`

**Query Parameters**:

- `search` (optional): Text search
- `type` (optional): speed, stamina, power, guts, wit, friend
- `rarity` (optional): SSR, SR, R
- `meta_tier` (optional): SS, S, A, B, C
- `sort_by` (optional): meta_score, name
- `sort_order` (optional): asc, desc
- `per_page` (optional): Results per page

**Success Response** (200 OK):

```json
{
    "data": [
        {
            "id": 1,
            "name": "Kitasan Black",
            "name_jp": "キタサンブラック",
            "character_name": "Kitasan Black",
            "rarity": "SSR",
            "type": "speed",
            "specialization": "Speed",
            "base_bonuses": {
                "speed_bonus": 10,
                "power_bonus": 5,
                "hint_rate": 3
            },
            "max_bonuses": {
                "speed_bonus": 18,
                "power_bonus": 10,
                "hint_rate": 6
            },
            "skills_provided": [101, 102, 103],
            "meta_tier": "SS",
            "meta_score": 9.5,
            "icon_path": "/icons/cards/kitasan_black.png"
        }
    ],
    "meta": {
        "current_page": 1,
        "total": 247
    }
}
```

### 7.3 Create Support Deck

**Endpoint**: `POST /api/v1/support-decks`

**Request Body**:

```json
{
    "name": "Speed Focus Deck",
    "description": "Optimized for speed training",
    "cards": [
        {
            "card_id": 1,
            "position": 1,
            "bond_level": 0,
            "is_borrowed": false
        },
        {
            "card_id": 2,
            "position": 2,
            "bond_level": 0,
            "is_borrowed": false
        },
        {
            "card_id": 3,
            "position": 3,
            "bond_level": 0,
            "is_borrowed": false
        },
        {
            "card_id": 4,
            "position": 4,
            "bond_level": 0,
            "is_borrowed": false
        },
        {
            "card_id": 5,
            "position": 5,
            "bond_level": 0,
            "is_borrowed": false
        },
        {
            "card_id": 6,
            "position": 6,
            "bond_level": 0,
            "is_borrowed": true
        }
    ]
}
```text

**Success Response** (201 Created):

```json
{
    "data": {
        "id": 10,
        "name": "Speed Focus Deck",
        "description": "Optimized for speed training",
        "is_active": false,
        "cards": [
            {
                "id": 1,
                "name": "Kitasan Black",
                "pivot": {
                    "position": 1,
                    "bond_level": 0,
                    "is_borrowed": false
                }
            }
        ],
        "validation": {
            "valid": true,
            "errors": [],
            "warnings": []
        },
        "created_at": "2026-01-24T10:00:00Z"
    }
}
```

### 7.4 Get Deck Synergy Analysis

**Endpoint**: `GET /api/v1/support-decks/{id}/synergy`

**Success Response** (200 OK):

```json
{
    "data": {
        "type_distribution": {
            "distribution": {
                "Speed": 3,
                "Stamina": 2,
                "Power": 1
            },
            "diversity_score": 50.0,
            "balance_score": 72.5,
            "recommendation": "Good distribution. Minor optimizations possible."
        },
        "stat_coverage": {
            "total_bonuses": {
                "speed": 54,
                "stamina": 32,
                "power": 28,
                "guts": 12,
                "wit": 18
            },
            "coverage_score": 100.0
        },
        "skill_coverage": {
            "total_skills": 18,
            "coverage_score": 90.0
        },
        "meta_rating": {
            "meta_card_count": 5,
            "average_meta_score": 8.2,
            "tier_distribution": {
                "SS": 2,
                "S": 3,
                "A": 1
            },
            "meta_rating": "S+"
        },
        "friendship_potential": {
            "active_friendship_count": 3,
            "potential_score": 50.0,
            "cards_status": [
                {
                    "card_id": 1,
                    "card_name": "Kitasan Black",
                    "bond_level": 85,
                    "is_friendship_active": true,
                    "progress_to_friendship": 0
                }
            ]
        },
        "overall_score": 82.5
    }
}
```text

### 7.5 Add Limit Break

**Endpoint**: `PATCH /api/v1/users/inventory/{id}/limit-break`

**Success Response** (200 OK):

```json
{
    "data": {
        "inventory_id": 123,
        "support_card_id": 1,
        "card_name": "Kitasan Black",
        "previous_stars": 2,
        "current_stars": 3,
        "improvements": {
            "speed_bonus": {
                "current": 14,
                "next": 16,
                "increase": 2
            },
            "power_bonus": {
                "current": 7,
                "next": 8,
                "increase": 1
            }
        }
    }
}
```

---

## 8. Database Schema

### 8.1 Table: `ucp_support_cards`

Master support card catalog.

```sql
CREATE TABLE ucp_support_cards (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    name_jp VARCHAR(255) NULL,
    character_name VARCHAR(255) NOT NULL,
    rarity ENUM('SSR', 'SR', 'R') NOT NULL,
    type VARCHAR(50) NOT NULL COMMENT 'Card category',
    specialization VARCHAR(50) NOT NULL COMMENT 'Speed, Stamina, Power, Guts, Wit, Friend',
    base_bonuses JSON NOT NULL COMMENT 'Bonuses at 0 stars',
    max_bonuses JSON NOT NULL COMMENT 'Bonuses at 4 stars',
    unique_effect_id INT UNSIGNED NULL,
    skills_provided JSON NULL COMMENT 'Array of skill IDs this card can hint',
    meta_tier VARCHAR(10) NULL COMMENT 'SS, S, A, B, C',
    meta_score DECIMAL(3,1) NULL COMMENT '0.0-10.0',
    icon_path VARCHAR(500) NULL,
    external_source VARCHAR(50) NULL COMMENT 'NEW: Data source identifier (e.g., gamewith, gamerch)',
    external_id VARCHAR(100) NULL COMMENT 'NEW: ID in external source system',
    last_synced_at TIMESTAMP NULL COMMENT 'NEW: Last sync timestamp from external source',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_rarity (rarity),
    INDEX idx_specialization (specialization),
    INDEX idx_meta_tier (meta_tier),
    INDEX idx_meta_score (meta_score),
    INDEX idx_external_source (external_source, external_id),
    FULLTEXT idx_search (name, character_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```text

### 8.2 Table: `ucp_user_card_inventory`

User-owned support cards.

```sql
CREATE TABLE ucp_user_card_inventory (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    support_card_id BIGINT UNSIGNED NOT NULL,
    limit_breaks TINYINT UNSIGNED NOT NULL DEFAULT 0 CHECK (limit_breaks BETWEEN 0 AND 4),
    level TINYINT UNSIGNED NOT NULL DEFAULT 1 CHECK (level BETWEEN 1 AND 50),
    is_favorite BOOLEAN NOT NULL DEFAULT FALSE,
    acquired_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES ucp_users(id) ON DELETE CASCADE,
    FOREIGN KEY (support_card_id) REFERENCES ucp_support_cards(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_card (user_id, support_card_id),
    INDEX idx_user_id (user_id),
    INDEX idx_limit_breaks (limit_breaks),
    INDEX idx_is_favorite (is_favorite)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 8.3 Table: `ucp_support_decks`

Support deck configurations.

```sql
CREATE TABLE ucp_support_decks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_active BOOLEAN NOT NULL DEFAULT FALSE,
    meta_rating VARCHAR(10) NULL COMMENT 'Overall deck tier',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES ucp_users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```text

### 8.4 Table: `ucp_support_deck_cards`

Pivot table linking decks to cards.

```sql
CREATE TABLE ucp_support_deck_cards (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    support_deck_id BIGINT UNSIGNED NOT NULL,
    support_card_id BIGINT UNSIGNED NOT NULL,
    position TINYINT UNSIGNED NOT NULL CHECK (position BETWEEN 1 AND 6),
    bond_level TINYINT UNSIGNED NOT NULL DEFAULT 0 CHECK (bond_level BETWEEN 0 AND 100),
    is_borrowed BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (support_deck_id) REFERENCES ucp_support_decks(id) ON DELETE CASCADE,
    FOREIGN KEY (support_card_id) REFERENCES ucp_support_cards(id) ON DELETE CASCADE,
    UNIQUE KEY unique_deck_position (support_deck_id, position),
    INDEX idx_deck_id (support_deck_id),
    INDEX idx_card_id (support_card_id),
    INDEX idx_bond_level (bond_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 9. AI Integration

### 9.1 Deck Optimization Agent

**Agent Class**: `App\Neuron\Agents\DeckOptimizationAgent`

**System Prompt**:

```text
You are an expert Umamusume support deck strategist.

Analyze the user's card inventory and training goals to recommend optimal deck composition.

**User Inventory:**
{{ user_cards_json }}

**Training Goals:**
- Focus: {{ focus_stats }}
- Scenario: {{ scenario_type }}
- Target Character: {{ character_name }}

**Current Deck (if exists):**
{{ current_deck_json }}

**Task:**
Recommend optimal 6-card deck composition (5 owned + 1 borrowed).

**Output Format (JSON):**
{
    "recommended_deck": [
        {
            "card_id": 123,
            "position": 1,
            "reasoning": "Why this card at this position"
        }
    ],
    "borrowed_card_suggestion": {
        "card_id": 456,
        "reasoning": "Why borrow this specific card"
    },
    "synergy_notes": "Overall deck strategy explanation",
    "alternative_options": ["Alternative card suggestions"]
}
```text

**Available Tools**:

```php
[
    'CardInventoryTool' => 'Query user owned cards',
    'MetaTierTool' => 'Get current meta rankings',
    'SynergyCalculatorTool' => 'Calculate deck synergy scores',
]
```

### 9.2 Deck Recommendation Service

```php
<?php

namespace App\Services\SupportCard;

use App\Models\{User, SupportDeck, Character};
use App\Services\AI\AdviceService;
use App\Neuron\Agents\DeckOptimizationAgent;

/**
 * AI-Powered Deck Recommendation Service
 */
class DeckRecommendationService
{
    public function __construct(
        private DeckOptimizationAgent $agent,
        private AdviceService $aiService,
        private SupportCardDeckService $cardService
    ) {}

    /**
     * Get AI deck recommendations
     * 
     * @param User $user
     * @param Character|null $targetCharacter
     * @param array $goals
     * @return array
     */
    public function getRecommendations(
        User $user,
        ?Character $targetCharacter = null,
        array $goals = []
    ): array {
        // Get user inventory
        $inventory = $this->cardService->getUserInventory($user->id);

        // Get current active deck if exists
        $currentDeck = SupportDeck::where('user_id', $user->id)
            ->where('is_active', true)
            ->with('cards')
            ->first();

        // Build AI context
        $context = $this->buildContext($inventory, $currentDeck, $targetCharacter, $goals);

        // Get AI recommendations
        $advice = $this->aiService->getAdvice(
            context: $context,
            topic: 'deck_optimization',
            agent: $this->agent
        );

        return $advice;
    }

    /**
     * Build AI context
     * 
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator $inventory
     * @param SupportDeck|null $currentDeck
     * @param Character|null $character
     * @param array $goals
     * @return string
     */
    private function buildContext($inventory, $currentDeck, $character, array $goals): string
    {
        $inventoryJson = json_encode($inventory->map(function ($item) {
            return [
                'card_id' => $item->support_card_id,
                'name' => $item->supportCard->name,
                'type' => $item->supportCard->specialization,
                'rarity' => $item->supportCard->rarity->value,
                'limit_breaks' => $item->limit_breaks,
                'meta_tier' => $item->supportCard->meta_tier,
            ];
        })->values());

        $currentDeckJson = $currentDeck ? json_encode($currentDeck->cards->map(function ($card) {
            return [
                'card_id' => $card->id,
                'name' => $card->name,
                'position' => $card->pivot->position,
                'bond_level' => $card->pivot->bond_level,
            ];
        })->values()) : 'null';

        $characterInfo = $character ? "{$character->name} (Scenario: {$character->scenario_type->value})" : "General purpose";
        
        $focusStats = !empty($goals) ? implode(', ', $goals) : "Balanced training";

        return <<<CONTEXT
        User Inventory: {$inventoryJson}
        
        Current Deck: {$currentDeckJson}
        
        Target Character: {$characterInfo}
        Focus Stats: {$focusStats}
        CONTEXT;
    }
}
```text

---

## 10. Business Logic

### 10.1 Bonus Calculation

**Base Bonus Formula**:

```text
FinalBonus = BaseBonus + (LimitBreakIncrement × LimitBreaks)

Where:
- BaseBonus: Value at 0 stars
- LimitBreakIncrement: (MaxBonus - BaseBonus) / 4
- LimitBreaks: 0-4
```

**Example** (SSR Speed Card):

```text
Base (0★): Speed +10
Max (4★): Speed +18

Increment = (18 - 10) / 4 = 2

0★: 10
1★: 12
2★: 14
3★: 16
4★: 18
```text

### 10.2 Friendship Training

**Activation Requirements**:

- Bond level ≥ 80
- Card present at training facility
- Training type matches card specialization (higher chance)

**Effects**:

- 1.2x stat gain multiplier
- Enhanced skill hint probability (+5%)
- Energy cost reduction (-20%)

### 10.3 Bond Progression

| Bond Level | Status | Training Gain | Notes |
| --- | --- | --- | --- |
| 0-19 | Low | 7/session | Initial phase |
| 20-39 | Building | 6/session | Regular interaction |
| 40-59 | Familiar | 6/session | Event unlock threshold |
| 60-79 | Close | 5/session | Near friendship |
| 80-100 | Friendship | 5/session | Max benefits active |

### 10.4 Deck Meta Tiers

| Tier | Description | Characteristics |
| --- | --- | --- |
| SS | Optimal | 4+ SS tier cards, avg score 8.5+ |
| S+ | Excellent | 2+ SS cards, 3+ S cards |
| S | Strong | 3+ S tier cards, avg score 7.5+ |
| A | Good | Mix of S/A tier cards |
| B | Acceptable | Functional but suboptimal |
| C | Needs Improvement | Low tier cards, poor synergy |

---

## 11. Integration Points

### 11.1 Training System Integration

Support cards provide bonuses during training:

```php
// From training prediction
$cardsAtFacility = $this->getCardsAtFacility($deck, $trainingType);

foreach ($cardsAtFacility as $card) {
    $bonuses = $card->getBonusesAtLevel($card->pivot->limit_breaks);
    $totalBonus += $bonuses['speed_bonus'] ?? 0;
    
    // Check friendship
    if ($card->pivot->bond_level >= 80) {
        $isFriendship = true;
    }
}
```

### 11.2 Skill System Integration

Support cards provide skill hints:

```php
// Check if card can hint skill
if ($card->providesSkill($skillId)) {
    $hintProbability = $card->getBonusesAtLevel($limitBreaks)['hint_rate'] ?? 0;
    
    // Roll for hint
    if (mt_rand(1, 100) <= $hintProbability) {
        app(SkillService::class)->addHint(
            characterId: $character->id,
            skillId: $skillId,
            level: 1,
            sourceType: 'support_card',
            sourceId: $card->id
        );
    }
}
```text

### 11.3 Character System Integration

Decks are attached to career runs:

```php
$career = CareerRun::create([
    'character_id' => $character->id,
    'support_deck_id' => $deck->id,
    // ... other fields
]);
```

---

## 12. Error Handling

### 12.1 Exception Hierarchy

```php
App\Exceptions\SupportCardException (Base)
├── InvalidDeckCompositionException
├── MaxLimitBreakException
├── CardNotOwnedException
├── DeckValidationException
└── InsufficientCardsException
```text

### 12.2 Error Codes

| Code | HTTP Status | Description | Resolution |
| --- | --- | --- | --- |
| `DECK_INVALID_SIZE` | 422 | Deck must have 6 cards | Add/remove cards |
| `DECK_INVALID_BORROWED` | 422 | Must have exactly 1 borrowed | Adjust borrowed flag |
| `CARD_NOT_OWNED` | 422 | User doesn't own card | Choose owned card |
| `DECK_DUPLICATE_CARD` | 422 | Duplicate card in deck | Remove duplicate |
| `LIMIT_BREAK_MAX` | 422 | Already at 4 stars | Cannot limit break further |

### 12.3 Validation Rules

```php
// Create deck
[
    'name' => 'required|string|max:255',
    'cards' => 'required|array|size:6',
    'cards.*.card_id' => 'required|exists:ucp_support_cards,id',
    'cards.*.position' => 'required|integer|min:1|max:6',
    'cards.*.is_borrowed' => 'required|boolean',
]

// Add to inventory
[
    'support_card_id' => 'required|exists:ucp_support_cards,id',
    'limit_breaks' => 'integer|min:0|max:4',
]
```

---

## 13. Performance Optimization

### 13.1 Caching Strategy

```php
// Card catalog (static)
Cache::tags(['support-cards'])->remember('cards:catalog', now()->addDay(), ...);

// User inventory
Cache::tags(['user-inventory', "user:{$userId}"])->remember(
    "inventory:{$userId}",
    now()->addMinutes(15),
    ...
);

// Meta rankings
Cache::tags(['support-cards'])->remember('meta:rankings', now()->addDay(), ...);
```text

### 13.2 Query Optimization

**Eager Loading**:

```php
$deck = SupportDeck::with([
    'cards.userInventory' => fn($q) => $q->where('user_id', $userId),
])->findOrFail($id);
```

**Selective Loading**:

```php
SupportCard::select(['id', 'name', 'specialization', 'meta_tier'])
    ->where('meta_tier', 'SS')
    ->get();
```text

### 13.3 Performance Targets

| Operation | Target | Measurement |
| --- | --- | --- |
| Card catalog search | < 50ms | p95 |
| Deck validation | < 30ms | p95 |
| Synergy analysis | < 100ms | p95 |
| Deck creation | < 200ms | p95 |
| AI recommendations | < 5s | p95 |

---

## 14. Security Considerations

### 14.1 Authorization

```php
public function viewDeck(User $user, SupportDeck $deck): bool
{
    return $user->id === $deck->user_id;
}

public function updateDeck(User $user, SupportDeck $deck): bool
{
    return $user->id === $deck->user_id;
}

public function addToInventory(User $user, UserCardInventory $inventory): bool
{
    return $user->id === $inventory->user_id;
}
```

### 14.2 Input Validation

- Verify card ownership before deck creation
- Validate deck composition rules (6 cards, 1 borrowed)
- Prevent duplicate cards in deck
- Validate position uniqueness (1-6)
- Limit break range validation (0-4)

### 14.3 Rate Limiting

```php
RateLimiter::for('deck-operations', function (Request $request) {
    return Limit::perMinute(30)->by($request->user()->id);
});
```text

---

## 15. Testing Strategy

### 15.1 Unit Tests

```php
// tests/Unit/Models/SupportCardTest.php

test('calculates bonuses at different limit break levels', function () {
    $card = SupportCard::factory()->make([
        'base_bonuses' => ['speed_bonus' => 10],
        'max_bonuses' => ['speed_bonus' => 18],
    ]);
    
    expect($card->getBonusesAtLevel(0)['speed_bonus'])->toBe(10.0)
        ->and($card->getBonusesAtLevel(2)['speed_bonus'])->toBe(14.0)
        ->and($card->getBonusesAtLevel(4)['speed_bonus'])->toBe(18.0);
});

test('deck validates composition correctly', function () {
    $deck = SupportDeck::factory()->create();
    
    // Add 5 owned + 1 borrowed
    for ($i = 1; $i <= 5; $i++) {
        $deck->cards()->attach(SupportCard::factory()->create()->id, [
            'position' => $i,
            'is_borrowed' => false,
        ]);
    }
    
    $deck->cards()->attach(SupportCard::factory()->create()->id, [
        'position' => 6,
        'is_borrowed' => true,
    ]);
    
    $validation = $deck->validate();
    
    expect($validation['valid'])->toBeTrue()
        ->and($validation['errors'])->toBeEmpty();
});

test('bond level clamps to max 100', function () {
    $pivot = new SupportDeckCard([
        'bond_level' => 95,
    ]);
    
    $pivot->bond_level = 150; // Try to exceed
    
    expect($pivot->bond_level)->toBeLessThanOrEqual(100);
});

test('friendship activates at bond 80', function () {
    $pivot = new SupportDeckCard(['bond_level' => 85]);
    
    expect($pivot->isFriendshipActive())->toBeTrue();
    
    $pivot->bond_level = 75;
    expect($pivot->isFriendshipActive())->toBeFalse();
});
```

### 15.2 Feature Tests

```php
// tests/Feature/SupportDeckManagementTest.php

test('user can create valid support deck', function () {
    $user = User::factory()->create();
    
    // Add cards to inventory
    $cards = SupportCard::factory()->count(6)->create();
    foreach ($cards->take(5) as $card) {
        UserCardInventory::create([
            'user_id' => $user->id,
            'support_card_id' => $card->id,
        ]);
    }
    
    $cardConfigs = $cards->map(fn($card, $index) => [
        'card_id' => $card->id,
        'position' => $index + 1,
        'is_borrowed' => $index === 5, // Last one borrowed
    ])->toArray();
    
    $response = $this->actingAs($user)
        ->postJson('/api/v1/support-decks', [
            'name' => 'Test Deck',
            'cards' => $cardConfigs,
        ]);
    
    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'Test Deck')
        ->assertJsonPath('data.validation.valid', true);
    
    $this->assertDatabaseHas('ucp_support_decks', [
        'user_id' => $user->id,
        'name' => 'Test Deck',
    ]);
});

test('deck creation fails with invalid composition', function () {
    $user = User::factory()->create();
    $cards = SupportCard::factory()->count(6)->create();
    
    // All marked as borrowed (invalid)
    $cardConfigs = $cards->map(fn($card, $index) => [
        'card_id' => $card->id,
        'position' => $index + 1,
        'is_borrowed' => true,
    ])->toArray();
    
    $response = $this->actingAs($user)
        ->postJson('/api/v1/support-decks', [
            'name' => 'Invalid Deck',
            'cards' => $cardConfigs,
        ]);
    
    $response->assertStatus(422);
});

test('user can add limit break to owned card', function () {
    $user = User::factory()->create();
    $card = SupportCard::factory()->create();
    $inventory = UserCardInventory::create([
        'user_id' => $user->id,
        'support_card_id' => $card->id,
        'limit_breaks' => 2,
    ]);
    
    $response = $this->actingAs($user)
        ->patchJson("/api/v1/users/inventory/{$inventory->id}/limit-break");
    
    $response->assertStatus(200)
        ->assertJsonPath('data.current_stars', 3);
    
    $inventory->refresh();
    expect($inventory->limit_breaks)->toBe(3);
});

test('user cannot exceed max limit breaks', function () {
    $user = User::factory()->create();
    $inventory = UserCardInventory::factory()->create([
        'user_id' => $user->id,
        'limit_breaks' => 4,
    ]);
    
    $response = $this->actingAs($user)
        ->patchJson("/api/v1/users/inventory/{$inventory->id}/limit-break");
    
    $response->assertStatus(422)
        ->assertJsonPath('error_code', 'LIMIT_BREAK_MAX');
});

test('deck synergy analysis calculates correctly', function () {
    $user = User::factory()->create();
    $deck = SupportDeck::factory()->for($user)->create();
    
    // Add diverse cards
    $speedCards = SupportCard::factory()->count(3)->create(['specialization' => 'Speed']);
    $staminaCards = SupportCard::factory()->count(2)->create(['specialization' => 'Stamina']);
    $powerCard = SupportCard::factory()->create(['specialization' => 'Power']);
    
    $allCards = $speedCards->concat($staminaCards)->concat([$powerCard]);
    
    foreach ($allCards as $index => $card) {
        $deck->cards()->attach($card->id, [
            'position' => $index + 1,
            'bond_level' => 50,
            'is_borrowed' => $index === 5,
        ]);
    }
    
    $response = $this->actingAs($user)
        ->getJson("/api/v1/support-decks/{$deck->id}/synergy");
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'type_distribution',
                'stat_coverage',
                'skill_coverage',
                'meta_rating',
                'friendship_potential',
                'overall_score',
            ]
        ]);
    
    expect($response->json('data.type_distribution.distribution'))
        ->toHaveKey('Speed', 3)
        ->toHaveKey('Stamina', 2)
        ->toHaveKey('Power', 1);
});

test('user can set active deck', function () {
    $user = User::factory()->create();
    $deck1 = SupportDeck::factory()->for($user)->create(['is_active' => true]);
    $deck2 = SupportDeck::factory()->for($user)->create(['is_active' => false]);
    
    $response = $this->actingAs($user)
        ->postJson("/api/v1/support-decks/{$deck2->id}/activate");
    
    $response->assertStatus(200);
    
    $deck1->refresh();
    $deck2->refresh();
    
    expect($deck1->is_active)->toBeFalse()
        ->and($deck2->is_active)->toBeTrue();
});
```text

### 15.3 Integration Tests

```php
// tests/Integration/DeckBondProgressionTest.php

test('bond increases during training session', function () {
    $deck = SupportDeck::factory()->create();
    $cards = SupportCard::factory()->count(3)->create();
    
    foreach ($cards as $index => $card) {
        $deck->cards()->attach($card->id, [
            'position' => $index + 1,
            'bond_level' => 0,
            'is_borrowed' => false,
        ]);
    }
    
    $bondService = app(BondProgressionService::class);
    
    $bondGains = [
        $cards[0]->id => 7,
        $cards[1]->id => 5,
    ];
    
    $updated = $bondService->increaseBond($deck, $bondGains);
    
    expect($updated[$cards[0]->id]['current'])->toBe(7)
        ->and($updated[$cards[1]->id]['current'])->toBe(5);
    
    $deck->refresh();
    expect($deck->getCardAtPosition(1)->pivot->bond_level)->toBe(7);
});

test('bond progression crosses friendship threshold', function () {
    $deck = SupportDeck::factory()->create();
    $card = SupportCard::factory()->create();
    
    $deck->cards()->attach($card->id, [
        'position' => 1,
        'bond_level' => 75,
        'is_borrowed' => false,
    ]);
    
    $bondService = app(BondProgressionService::class);
    $updated = $bondService->increaseBond($deck, [$card->id => 10]);
    
    expect($updated[$card->id]['threshold_crossed'])->toContain(80);
});

test('external card sync updates database', function () {
    Http::fake([
        'umapyoi.net/api/support-cards' => Http::response([
            [
                'id' => 1,
                'name' => 'Kitasan Black',
                'character_name' => 'Kitasan Black',
                'rarity' => 'SSR',
                'type' => 'speed',
                'specialization' => 'Speed',
                'base_bonuses' => ['speed_bonus' => 10],
                'max_bonuses' => ['speed_bonus' => 18],
                'skills_provided' => [101, 102],
                'meta_tier' => 'SS',
                'meta_score' => 9.5,
            ]
        ], 200),
    ]);
    
    $service = app(SupportCardDeckService::class);
    $syncedCount = $service->syncCardDefinitions();
    
    expect($syncedCount)->toBe(1);
    
    $this->assertDatabaseHas('ucp_support_cards', [
        'id' => 1,
        'name' => 'Kitasan Black',
        'meta_tier' => 'SS',
    ]);
});
```

### 15.4 AI Integration Tests

```php
// tests/Integration/DeckRecommendationAITest.php

test('AI provides valid deck recommendations', function () {
    $user = User::factory()->create();
    
    // Create inventory
    $cards = SupportCard::factory()->count(20)->create();
    foreach ($cards->take(15) as $card) {
        UserCardInventory::create([
            'user_id' => $user->id,
            'support_card_id' => $card->id,
            'limit_breaks' => rand(0, 4),
        ]);
    }
    
    $service = app(DeckRecommendationService::class);
    $recommendations = $service->getRecommendations($user);
    
    expect($recommendations)->toHaveKeys([
        'recommended_deck',
        'borrowed_card_suggestion',
        'synergy_notes',
    ])
    ->and($recommendations['recommended_deck'])->toHaveCount(5); // 5 owned cards
});
```text

### 15.5 Test Data Factories

```php
// database/factories/SupportCardFactory.php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SupportCardFactory extends Factory
{
    public function definition(): array
    {
        $specialization = $this->faker->randomElement([
            'Speed', 'Stamina', 'Power', 'Guts', 'Wit', 'Friend'
        ]);
        
        return [
            'name' => $this->faker->name(),
            'character_name' => $this->faker->firstName(),
            'rarity' => $this->faker->randomElement(['SSR', 'SR', 'R']),
            'type' => strtolower($specialization),
            'specialization' => $specialization,
            'base_bonuses' => [
                'speed_bonus' => $this->faker->numberBetween(5, 10),
                'stamina_bonus' => $this->faker->numberBetween(3, 8),
                'hint_rate' => $this->faker->numberBetween(2, 5),
            ],
            'max_bonuses' => [
                'speed_bonus' => $this->faker->numberBetween(15, 20),
                'stamina_bonus' => $this->faker->numberBetween(10, 15),
                'hint_rate' => $this->faker->numberBetween(5, 8),
            ],
            'skills_provided' => $this->faker->randomElements(range(101, 150), 3),
            'meta_tier' => $this->faker->randomElement(['SS', 'S', 'A', 'B', 'C']),
            'meta_score' => $this->faker->randomFloat(1, 5.0, 10.0),
        ];
    }
    
    public function ssr(): self
    {
        return $this->state(fn (array $attributes) => [
            'rarity' => 'SSR',
            'meta_tier' => $this->faker->randomElement(['SS', 'S', 'A']),
            'meta_score' => $this->faker->randomFloat(1, 7.0, 10.0),
        ]);
    }
    
    public function metaTier(): self
    {
        return $this->state(fn (array $attributes) => [
            'rarity' => 'SSR',
            'meta_tier' => $this->faker->randomElement(['SS', 'S']),
            'meta_score' => $this->faker->randomFloat(1, 8.0, 10.0),
        ]);
    }
}
```

```php
// database/factories/SupportDeckFactory.php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupportDeckFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->optional()->sentence(),
            'is_active' => false,
        ];
    }
    
    public function active(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }
}
```text

---

## 16. Appendices

### Appendix A: Popular Support Cards

**SS Tier Speed Cards**:

- **Kitasan Black (SSR)**: Speed +18, Power +10, Hint Rate +6 (MLB)
- **Narita Brian (SSR)**: Speed +16, Wit +12, Skill Power +5 (MLB)
- **Mejiro McQueen (SSR)**: Speed +15, Stamina +14, Positioning +4 (MLB)

**SS Tier Stamina Cards**:

- **Super Creek (SSR)**: Stamina +20, Guts +12, Recovery +6 (MLB)
- **Gold Ship (SSR)**: Stamina +18, Guts +14, Unique Effect (MLB)
- **Symboli Rudolf (SSR)**: Stamina +17, Power +10, Leadership +5 (MLB)

**SS Tier Friend Cards**:

- **Special Week (Friend SSR)**: All Stats +3, Motivation +8, Bond Speed +6 (MLB)
- **Vodka (Friend SSR)**: All Stats +2, Event Rate +10, Hint Rate +5 (MLB)

### Appendix B: Limit Break Progression

| Stars | Speed Bonus Example | Stamina Bonus Example | Hint Rate Example |
| --- | --- | --- | --- |
| 0★ | 10 | 8 | 3 |
| 1★ | 12 | 10 | 4 |
| 2★ | 14 | 12 | 5 |
| 3★ | 16 | 14 | 6 |
| 4★ (MLB) | 18 | 16 | 7 |

### Appendix C: Optimal Deck Templates

**Speed Training Deck**:

1. Kitasan Black (SSR, Speed, 4★)
2. Narita Brian (SSR, Speed, 4★)
3. Tokai Teio (SSR, Speed, 3★)
4. Agnes Tachyon (SSR, Wit, 4★)
5. Mejiro McQueen (SSR, Speed, 2★)
6. **Borrowed**: Symboli Rudolf (SSR, Stamina, 4★)

**Balanced Training Deck**:

1. Kitasan Black (SSR, Speed, 4★)
2. Super Creek (SSR, Stamina, 4★)
3. Vodka (SSR, Power, 3★)
4. Nice Nature (SSR, Guts, 3★)
5. Agnes Tachyon (SSR, Wit, 4★)
6. **Borrowed**: Special Week Friend (SSR, Friend, 4★)

**Long Distance Deck**:

1. Super Creek (SSR, Stamina, 4★)
2. Symboli Rudolf (SSR, Stamina, 4★)
3. Gold Ship (SSR, Stamina, 3★)
4. Nice Nature (SSR, Guts, 4★)
5. Mejiro McQueen (SSR, Speed, 2★)
6. **Borrowed**: Special Week Friend (SSR, Friend, 4★)

### Appendix D: Bond Milestone Rewards

| Bond Level | Reward | Effect |
| --- | --- | --- |
| 20 | Hint Event Unlock | First skill hint available |
| 40 | Bonus Event | Special character event |
| 60 | Enhanced Training | +10% bonus effectiveness |
| 80 | Friendship Training | 1.2x multiplier activation |
| 100 | Max Bond Bonus | Guaranteed unique event |

### Appendix E: Meta Tier Criteria

**SS Tier Requirements**:

- Rarity: SSR
- Base bonuses ≥ 10 for primary stat
- MLB bonuses ≥ 18 for primary stat
- Unique effect or exceptional versatility
- Community usage rate > 80%

**S Tier Requirements**:

- Rarity: SSR
- Strong stat bonuses (8-10 base)
- Good skill hint pool
- Scenario compatibility
- Community usage rate > 60%

**A Tier Requirements**:

- Rarity: SSR/SR
- Decent bonuses (6-8 base)
- Functional in multiple scenarios
- Community usage rate > 40%

### Appendix F: Change Log

| Version | Date | Author | Changes |
| --- | --- | --- | --- |
| 2.3.0 | 2026-02-22 | Development Team | Updated service names (SupportCardDeckService, DeckManagementService, FriendshipBondService, DeckOptimizationService, ExternalDataService), Neuron AI v2.11, PHP 8.2+, marked implementation complete |
| 2.2.0 | 2026-01-28 | Development Team | Updated to align with game-accurate mechanics (v2.2.0 architecture) |
| 2.0.0 | 2026-01-24 | Development Team | Full v2.0.0 alignment, complete testing strategy, AI integration, bond system, synergy analysis |
| 1.0.0 | 2026-01-14 | Development Team | Initial technical specification |

---

### Document Approval

| Role | Name | Signature | Date |
| --- | --- | --- | --- |
| Tech Lead | [Name] | _________ | 2026-01-24 |
| Product Owner | [Name] | _________ | 2026-01-24 |
| QA Lead | [Name] | _________ | 2026-01-24 |
| Game Designer | [Name] | _________ | 2026-01-24 |

---

**Document Control**  
**Maintained By**: Backend Development Team  
**Review Frequency**: Bi-weekly during active development  
**Next Review Date**: 2026-03-07  
**Distribution**: Development Team, QA Team, Product Management, Game Design Team

---

### End of Document
