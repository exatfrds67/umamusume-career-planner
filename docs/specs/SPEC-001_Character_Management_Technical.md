# SPEC-001: Character Management System - Technical Specification

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Project**: UmamusumeCareerPlanner  
**Author**: AI Development Team  
**Status**: Draft  
**Related Documents**: [PRD-001], [SRS-3.1], [SDS-4.1], [DBD-009]

---

## Table of Contents

1. [Technical Overview](#1-technical-overview)
2. [Data Model](#2-data-model)
3. [API Specification](#3-api-specification)
4. [Database Schema](#4-database-schema)
5. [System Interactions](#5-system-interactions)
6. [Error Handling](#6-error-handling)
7. [Performance Considerations](#7-performance-considerations)
8. [Testing Requirements](#8-testing-requirements)

---

## 1. Technical Overview

### 1.1 Component Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│          CHARACTER MANAGEMENT MODULE (SPEC-001)                │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  API Layer (Controllers)                                  │   │
│  │  • CharacterController (CRUD operations)                 │   │
│  │  • CharacterStatsController (stat management)            │   │
│  │  • CharacterGoalController (goal management)             │   │
│  └──────────────────────────────────────────────────────────┘   │
│                           ↓                                      │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  Service Layer (Business Logic)                           │   │
│  │  • CharacterService (lifecycle management)               │   │
│  │  • CharacterStateService (state tracking)                │   │
│  │  • GoalManagementService (goal operations)               │   │
│  │  • FactorInheritanceService (inheritance calculations)   │   │
│  └──────────────────────────────────────────────────────────┘   │
│                           ↓                                      │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  Repository Layer (Data Access)                           │   │
│  │  • CharacterRepository                                    │   │
│  │  • AptitudeRepository                                     │   │
│  │  • FactorRepository                                       │   │
│  │  • GoalRepository                                         │   │
│  └──────────────────────────────────────────────────────────┘   │
│                           ↓                                      │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  Data Layer (Models & Database)                           │   │
│  │  • Character (primary entity)                             │   │
│  │  • Aptitude, Factor, Goal, Condition models               │   │
│  │  • MySQL Database (18 tables)                             │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 1.2 Core Entities

| Entity | Purpose | Relationships |
| --- | --- | --- |
| Character | Main trainee entity | Has many: Aptitudes, Factors, Goals, Skills, Conditions |
| Aptitude | Distance/Surface/Style rating | Belongs to: Character |
| Factor | Inherited stat bonus | Belongs to: Character, Legacy Character |
| Goal | Training objective | Belongs to: Character |
| Condition | Status effects | Belongs to: Character |
| Skill | Acquired abilities | Belongs to: Character |
| SkillHint | SP cost reduction | Belongs to: Skill |

---

## 2. Data Model

### 2.1 Character Entity Model

```php
namespace App\Models;

class Character extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'trainee_id',
        'scenario',
        'career_stage',
        'class',
        'current_energy',
        'current_mood',
        'days_until_race',
        'facility_level',
    ];

    protected $casts = [
        'current_energy' => 'integer',
        'facility_level' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function aptitudes(): HasMany { }
    public function factors(): HasMany { }
    public function goals(): HasMany { }
    public function conditions(): HasMany { }
    public function skills(): HasMany { }
    public function snapshots(): HasMany { }
    public function auditLog(): HasMany { }
}
```

### 2.2 Stat System

**Stat Types**: Speed, Stamina, Power, Guts, Wit  
**Value Range**: 0-1200 (training cap)  
**Grade Scale**: G+, G, F+, F, E+, E, D+, D, C+, C, B+, B, A+, A, S+, S, SS

```php
class Stat
{
    public int $speed;      // Priority: ★★★★★
    public int $stamina;    // Priority: ★★★★
    public int $power;      // Priority: ★★★
    public int $guts;       // Priority: ★
    public int $wit;        // Priority: ★★
    
    public function calculateGrade(): string { }
    public function isAtBreakpoint(): bool { }  // 901 or 1600
}
```

### 2.3 Aptitude System

**Aptitude Ratings**: G, G+, F, F+, E, E+, D, D+, C, C+, B, B+, A, A+, S, S+, SS

**Distance Categories**:
- Sprint: 1000-1400m
- Mile: 1401-1800m
- Medium: 1801-2400m
- Long: 2401m+

**Surface Types**: Turf, Dirt

**Running Styles**: Front Runner, Pace Chaser, Late Surger, End Closer

```php
class Aptitude extends Model
{
    protected $fillable = [
        'character_id',
        'category',      // 'distance', 'surface', 'style'
        'type',          // specific value
        'rating',        // G through SS
    ];

    const RATINGS = [
        'G', 'G+', 'F', 'F+', 'E', 'E+', 'D', 'D+', 
        'C', 'C+', 'B', 'B+', 'A', 'A+', 'S', 'S+', 'SS'
    ];

    const DISTANCES = ['Sprint', 'Mile', 'Medium', 'Long'];
    const SURFACES = ['Turf', 'Dirt'];
    const STYLES = ['FrontRunner', 'PaceChaser', 'LateSurger', 'EndCloser'];
}
```

### 2.4 Factor System

**Factor Types**: 
- Blue Stat Factors (Inherited stats)
- Red Aptitude Factors (Inherited aptitudes)
- Green Unique Skill Factors (Inherited unique skills)
- White Normal Skill Factors (Inherited normal skills)

**Stat Factor Ratings**:
- ★☆☆ = +5 bonus
- ★★☆ = +12 bonus
- ★★★ = +21 bonus

**Aptitude Factor Ratings**:
- 1★ = +1 grade
- 2★ = +2 grades
- 3★ = +3 grades

```php
class Factor extends Model
{
    protected $fillable = [
        'character_id',
        'legacy_character_id',
        'factor_type',   // 'stat', 'aptitude', 'unique_skill', 'normal_skill'
        'category',      // stat name or aptitude category
        'rating',        // ★★★ or grade increase
        'inherited_value',
    ];

    const TYPES = ['Stat', 'Aptitude', 'UniqueSkill', 'NormalSkill'];
    const STAT_RATINGS = ['★☆☆' => 5, '★★☆' => 12, '★★★' => 21];

    public function calculateBonus(): int { }
}
```

### 2.5 Growth Rate System

Growth rates are inherited bonuses that multiply training effectiveness.

```php
class GrowthRate extends Model
{
    protected $fillable = [
        'character_id',
        'stat_type',     // speed, stamina, power, guts, wit
        'rate',          // 10, 20, or 30 (percentage)
    ];

    const RATES = [10, 20, 30];  // +10%, +20%, +30%

    public function applyToTraining(int $baseGain): int
    {
        return (int)($baseGain * (1 + $this->rate / 100));
    }
}
```

### 2.6 Goal Entity Model

```php
class Goal extends Model
{
    protected $fillable = [
        'character_id',
        'goal_type',     // 'stat', 'race', 'aptitude'
        'target_stat',   // if stat goal
        'target_value',  // target number
        'distance_type', // if distance-specific
        'status',        // 'active', 'completed', 'abandoned'
    ];

    const MINIMUM_STATS = [
        'Sprint' => ['career' => 350, 'pvp' => 500],
        'Mile' => ['career' => 400, 'pvp' => 600],
        'Medium' => ['career' => 500, 'pvp' => 800],
        'Long' => ['career' => 600, 'pvp' => 900],
    ];

    public function calculateProgress(): float { }
    public function getRemaining(): int { }
}
```

### 2.7 Condition Entity Model

```php
class Condition extends Model
{
    protected $fillable = [
        'character_id',
        'condition_type',  // 'positive' or 'negative'
        'condition_name',  // specific condition
        'effect',          // description
        'modifier',        // percentage modifier
        'expires_at',      // when condition ends
    ];

    const POSITIVE_CONDITIONS = [
        'Charming' => ['effect' => '+2 bond'],
        'Sharp' => ['effect' => '-10% skill costs'],
        'PracticePerfect' => ['effect' => '-2% failure rate'],
    ];

    const NEGATIVE_CONDITIONS = [
        'PracticePoor' => ['effect' => '+2% failure rate'],
        'Migraine' => ['effect' => 'mood resistance'],
        'DrySkin' => ['effect' => 'motivation decrease'],
    ];
}
```

---

## 3. API Specification

### 3.1 Character Management Endpoints

#### 3.1.1 Create Character

```http
POST /api/v1/characters
Content-Type: application/json
Authorization: Bearer {token}

{
    "name": "Mejiro Ardan",
    "trainee_id": 10004,
    "scenario": "URA",
    "parent1_id": 10001,
    "parent2_id": 10002,
    "support_cards": [
        {"card_id": 1001, "rarity": "SSR", "limit_breaks": 4},
        {"card_id": 1002, "rarity": "SR", "limit_breaks": 2},
        ...
    ]
}
```

**Response** (201 Created):
```json
{
    "id": 1,
    "user_id": 1,
    "name": "Mejiro Ardan",
    "trainee_id": 10004,
    "scenario": "URA",
    "current_energy": 100,
    "current_mood": "Normal",
    "days_until_race": 0,
    "facility_level": 1,
    "stats": {
        "speed": 450,
        "stamina": 420,
        "power": 380,
        "guts": 400,
        "wit": 390
    },
    "aptitudes": [...],
    "factors": [...],
    "created_at": "2026-01-14T10:00:00Z",
    "updated_at": "2026-01-14T10:00:00Z"
}
```

#### 3.1.2 Get Character Details

```http
GET /api/v1/characters/{id}
Authorization: Bearer {token}
```

**Response** (200 OK):
```json
{
    "id": 1,
    "name": "Mejiro Ardan",
    "trainee_id": 10004,
    "scenario": "URA",
    "career_stage": "Debut",
    "class": "Middle Distance Specialist",
    "current_energy": 78,
    "current_mood": "Good",
    "days_until_race": 15,
    "facility_level": 2,
    "stats": {
        "speed": 520,
        "stamina": 480,
        "power": 440,
        "guts": 460,
        "wit": 450,
        "grades": {
            "speed": "A",
            "stamina": "A",
            "power": "A",
            "guts": "A",
            "wit": "A"
        }
    },
    "aptitudes": [
        {"category": "distance", "type": "Mile", "rating": "A+"},
        {"category": "surface", "type": "Turf", "rating": "A"},
        {"category": "style", "type": "LateSurger", "rating": "S"}
    ],
    "factors": [
        {
            "id": 1,
            "factor_type": "Stat",
            "category": "Speed",
            "rating": "★★★",
            "bonus": 21
        },
        ...
    ],
    "growth_rates": {
        "speed": 20,
        "stamina": 20,
        "power": 10,
        "guts": 20,
        "wit": 10
    },
    "goals": [
        {
            "id": 1,
            "goal_type": "stat",
            "target_stat": "speed",
            "target_value": 800,
            "current_value": 520,
            "progress": 65,
            "status": "active"
        }
    ],
    "conditions": [
        {
            "id": 1,
            "condition_type": "positive",
            "condition_name": "Sharp",
            "effect": "-10% skill costs",
            "expires_at": "2026-01-20T00:00:00Z"
        }
    ],
    "skills": [
        {
            "id": 1,
            "name": "Nimble",
            "sp_cost": 120,
            "category": "Normal",
            "acquired_at": "2026-01-10T12:00:00Z"
        }
    ],
    "support_deck": [
        {
            "card_id": 1001,
            "name": "Mejiro Dober",
            "rarity": "SSR",
            "limit_breaks": 4,
            "bond_level": 80,
            "skills_provided": ["Lane Guidance", "Cool Breeze"]
        }
    ]
}
```

#### 3.1.3 Update Character Stats

```http
PATCH /api/v1/characters/{id}/stats
Content-Type: application/json
Authorization: Bearer {token}

{
    "stats": {
        "speed": 530,
        "stamina": 490,
        "power": 450,
        "guts": 470,
        "wit": 460
    },
    "source": "training_session",
    "metadata": {
        "training_session_id": 123,
        "facility": "Speed"
    }
}
```

**Response** (200 OK): Updated character object

#### 3.1.4 Update Character Mood/Energy

```http
PATCH /api/v1/characters/{id}/condition
Content-Type: application/json
Authorization: Bearer {token}

{
    "current_energy": 65,
    "current_mood": "Normal",
    "conditions": [
        {
            "condition_type": "positive",
            "condition_name": "Charming",
            "expires_at": "2026-01-20T00:00:00Z"
        }
    ]
}
```

**Response** (200 OK): Updated character object

### 3.2 Goal Management Endpoints

#### 3.2.1 Create Goal

```http
POST /api/v1/characters/{id}/goals
Content-Type: application/json
Authorization: Bearer {token}

{
    "goal_type": "stat",
    "target_stat": "speed",
    "target_value": 800,
    "distance_type": "Mile"
}
```

**Response** (201 Created):
```json
{
    "id": 1,
    "character_id": 1,
    "goal_type": "stat",
    "target_stat": "speed",
    "target_value": 800,
    "current_value": 520,
    "remaining": 280,
    "progress": 65,
    "status": "active",
    "created_at": "2026-01-14T10:00:00Z"
}
```

#### 3.2.2 Update Goal

```http
PATCH /api/v1/goals/{id}
Content-Type: application/json
Authorization: Bearer {token}

{
    "target_value": 850,
    "status": "active"
}
```

**Response** (200 OK): Updated goal object

#### 3.2.3 List Character Goals

```http
GET /api/v1/characters/{id}/goals?status=active
Authorization: Bearer {token}
```

**Response** (200 OK):
```json
{
    "data": [
        {
            "id": 1,
            "character_id": 1,
            "goal_type": "stat",
            "target_stat": "speed",
            "target_value": 800,
            "current_value": 520,
            "progress": 65,
            "status": "active"
        },
        ...
    ],
    "meta": {
        "total": 5,
        "count": 3,
        "per_page": 15,
        "current_page": 1
    }
}
```

### 3.3 Aptitude & Factor Endpoints

#### 3.3.1 Get Character Aptitudes

```http
GET /api/v1/characters/{id}/aptitudes
Authorization: Bearer {token}
```

**Response** (200 OK):
```json
{
    "distances": [
        {"type": "Sprint", "rating": "B+", "is_specialty": false},
        {"type": "Mile", "rating": "A+", "is_specialty": true},
        {"type": "Medium", "rating": "A", "is_specialty": false},
        {"type": "Long", "rating": "B", "is_specialty": false}
    ],
    "surfaces": [
        {"type": "Turf", "rating": "A", "is_specialty": false},
        {"type": "Dirt", "rating": "B", "is_specialty": false}
    ],
    "styles": [
        {"type": "FrontRunner", "rating": "B"},
        {"type": "PaceChaser", "rating": "C+"},
        {"type": "LateSurger", "rating": "S", "is_specialty": true},
        {"type": "EndCloser", "rating": "C"}
    ]
}
```

#### 3.3.2 Get Character Factors

```http
GET /api/v1/characters/{id}/factors
Authorization: Bearer {token}
```

**Response** (200 OK):
```json
{
    "stat_factors": [
        {
            "id": 1,
            "stat_type": "Speed",
            "rating": "★★★",
            "bonus": 21,
            "legacy_character": "Power Lance"
        },
        {
            "id": 2,
            "stat_type": "Stamina",
            "rating": "★★☆",
            "bonus": 12,
            "legacy_character": "Dancer's Image"
        }
    ],
    "aptitude_factors": [
        {
            "id": 3,
            "category": "distance",
            "type": "Mile",
            "rating": "3★",
            "upgrade_grades": 3,
            "legacy_character": "Power Lance"
        }
    ],
    "skill_factors": [
        {
            "id": 5,
            "skill_type": "Unique",
            "skill_name": "Predator's Instinct",
            "guaranteed": true,
            "legacy_character": "Power Lance"
        }
    ]
}
```

### 3.4 Character Snapshot Endpoints

#### 3.4.1 Create Snapshot

```http
POST /api/v1/characters/{id}/snapshots
Content-Type: application/json
Authorization: Bearer {token}

{
    "label": "Day 150 - Pre-Finals Checkpoint",
    "description": "Character state before final training push"
}
```

**Response** (201 Created):
```json
{
    "id": 1,
    "character_id": 1,
    "label": "Day 150 - Pre-Finals Checkpoint",
    "description": "Character state before final training push",
    "snapshot_data": {
        "stats": {...},
        "aptitudes": [...],
        "conditions": [...],
        "skills": [...],
        "mood": "Good",
        "energy": 78
    },
    "created_at": "2026-01-14T10:00:00Z"
}
```

#### 3.4.2 List Snapshots

```http
GET /api/v1/characters/{id}/snapshots
Authorization: Bearer {token}
```

**Response** (200 OK):
```json
{
    "data": [
        {
            "id": 3,
            "label": "Day 200 - Finals Started",
            "created_at": "2026-01-12T10:00:00Z"
        },
        {
            "id": 2,
            "label": "Day 150 - Pre-Finals Checkpoint",
            "created_at": "2026-01-10T10:00:00Z"
        },
        {
            "id": 1,
            "label": "Initial State",
            "created_at": "2026-01-05T10:00:00Z"
        }
    ],
    "meta": {
        "total": 3,
        "count": 3
    }
}
```

---

## 4. Database Schema

### 4.1 Characters Table

```sql
CREATE TABLE characters (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    trainee_id INT NOT NULL,
    scenario ENUM('URA', 'Unity') NOT NULL DEFAULT 'URA',
    career_stage VARCHAR(100),
    class VARCHAR(100),
    current_energy INT NOT NULL DEFAULT 100,
    current_mood ENUM('Awful', 'Bad', 'Normal', 'Good', 'Great') DEFAULT 'Normal',
    days_until_race INT DEFAULT 0,
    facility_level INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;
```

### 4.2 Character Stats Table

```sql
CREATE TABLE character_stats (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    character_id BIGINT UNSIGNED NOT NULL,
    stat_type ENUM('Speed', 'Stamina', 'Power', 'Guts', 'Wit') NOT NULL,
    current_value INT NOT NULL DEFAULT 0,
    base_value INT NOT NULL DEFAULT 0,
    growth_rate INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
    UNIQUE KEY unique_stat (character_id, stat_type),
    INDEX idx_stat_type (stat_type)
) ENGINE=InnoDB;
```

### 4.3 Aptitudes Table

```sql
CREATE TABLE aptitudes (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    character_id BIGINT UNSIGNED NOT NULL,
    category ENUM('Distance', 'Surface', 'Style') NOT NULL,
    type VARCHAR(50) NOT NULL,
    rating VARCHAR(10) NOT NULL,
    is_specialty BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
    UNIQUE KEY unique_aptitude (character_id, category, type),
    INDEX idx_category (category),
    INDEX idx_rating (rating)
) ENGINE=InnoDB;
```

### 4.4 Factors Table

```sql
CREATE TABLE factors (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    character_id BIGINT UNSIGNED NOT NULL,
    legacy_character_id INT,
    factor_type ENUM('Stat', 'Aptitude', 'UniqueSkill', 'NormalSkill') NOT NULL,
    category VARCHAR(100),
    rating VARCHAR(20),
    inherited_value INT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
    INDEX idx_character_id (character_id),
    INDEX idx_factor_type (factor_type)
) ENGINE=InnoDB;
```

### 4.5 Goals Table

```sql
CREATE TABLE goals (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    character_id BIGINT UNSIGNED NOT NULL,
    goal_type ENUM('Stat', 'Race', 'Aptitude') NOT NULL,
    target_stat VARCHAR(50),
    target_value INT,
    distance_type VARCHAR(50),
    status ENUM('Active', 'Completed', 'Abandoned') DEFAULT 'Active',
    created_at TIMESTAMP,
    completed_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
    INDEX idx_character_id (character_id),
    INDEX idx_status (status),
    INDEX idx_goal_type (goal_type)
) ENGINE=InnoDB;
```

### 4.6 Conditions Table

```sql
CREATE TABLE conditions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    character_id BIGINT UNSIGNED NOT NULL,
    condition_type ENUM('Positive', 'Negative') NOT NULL,
    condition_name VARCHAR(100) NOT NULL,
    effect TEXT,
    modifier INT,
    expires_at TIMESTAMP,
    created_at TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
    INDEX idx_character_id (character_id),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB;
```

### 4.7 Character Snapshots Table

```sql
CREATE TABLE character_snapshots (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    character_id BIGINT UNSIGNED NOT NULL,
    label VARCHAR(255),
    description TEXT,
    snapshot_data JSON NOT NULL,
    created_at TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
    INDEX idx_character_id (character_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;
```

---

## 5. System Interactions

### 5.1 Character Creation Workflow

```
User Initiates Character Creation
        ↓
[CharacterController.store()]
        ↓
[CharacterService.createCharacter()]
        ↓
├─→ Create Character record
├─→ [FactorInheritanceService] Calculate inherited stats
├─→ [GrowthRateService] Initialize growth rates
├─→ Create initial Aptitudes (fixed, non-trainable)
├─→ Create initial Conditions (if any)
├─→ Create initial Goals (default or user-provided)
├─→ Create first Snapshot
└─→ [AuditLogger] Log creation event
        ↓
Return Character with full details
```

### 5.2 Character State Update Workflow

```
Training Session Complete
        ↓
[TrainingSessionController.complete()]
        ↓
[CharacterStateService.updateAfterTraining()]
        ↓
├─→ Update Stats (with growth rate multiplier)
├─→ Update Energy (reduce 20-30%)
├─→ Update Mood (based on training results)
├─→ Update Conditions (apply/remove as needed)
├─→ Check Goal Progress
├─→ Trigger "CharacterUpdated" Event
├─→ [AuditLogger] Log stat changes
└─→ [CacheService] Invalidate character cache
        ↓
Return Updated Character
```

### 5.3 Goal Progress Calculation

```
Character Stats Updated
        ↓
[GoalManagementService.updateGoalProgress()]
        ↓
For each Active Goal:
    ├─→ Calculate current progress value
    ├─→ Calculate remaining to target
    ├─→ Calculate percentage progress
    ├─→ If progress >= target:
    │   └─→ Mark goal as "Completed"
    │   └─→ Trigger "GoalCompleted" Event
    └─→ Emit Progress event for UI update
        ↓
Update Goal records in database
```

---

## 6. Error Handling

### 6.1 Validation Errors

| Scenario | Error Code | HTTP Status | Response |
| --- | --- | --- | --- |
| Invalid trainee_id | INVALID_TRAINEE | 422 | {"error": "Trainee not found in database"} |
| Invalid parent selection | INVALID_PARENTS | 422 | {"error": "Parent characters incompatible"} |
| Invalid support card count | INVALID_DECK_SIZE | 422 | {"error": "Support deck must contain exactly 6 cards"} |
| Goal target below minimum | GOAL_BELOW_MINIMUM | 422 | {"error": "Target stat below minimum for distance type"} |
| Energy below 0 | INVALID_ENERGY | 422 | {"error": "Character energy cannot be negative"} |

### 6.2 Business Logic Errors

| Scenario | Error Code | HTTP Status | Response |
| --- | --- | --- | --- |
| Character doesn't exist | CHARACTER_NOT_FOUND | 404 | {"error": "Character not found"} |
| Unauthorized access | UNAUTHORIZED | 403 | {"error": "Not authorized to access this character"} |
| Goal doesn't exist | GOAL_NOT_FOUND | 404 | {"error": "Goal not found"} |
| Snapshot doesn't exist | SNAPSHOT_NOT_FOUND | 404 | {"error": "Snapshot not found"} |
| Database error | DB_ERROR | 500 | {"error": "Database operation failed"} |

---

## 7. Performance Considerations

### 7.1 Caching Strategy

- **Character Cache**: 5-minute TTL for character details with stats/aptitudes
- **Aptitude Cache**: 1-hour TTL for fixed aptitude data
- **Factor Cache**: 1-hour TTL for inheritance calculations
- **Goal Cache**: 10-minute TTL for progress calculations

### 7.2 Query Optimization

```php
// Use eager loading to prevent N+1 queries
Character::with([
    'aptitudes',
    'factors',
    'goals',
    'conditions',
    'skills',
    'stats'
])->find($id);
```

### 7.3 Database Indexes

- `characters` table: user_id, created_at
- `character_stats` table: character_id + stat_type
- `aptitudes` table: character_id, category, rating
- `factors` table: character_id, factor_type
- `goals` table: character_id, status, goal_type
- `conditions` table: character_id, expires_at

---

## 8. Testing Requirements

### 8.1 Unit Tests

- [ ] Character model creation and relationships
- [ ] Stat calculation and grade conversion
- [ ] Aptitude validation
- [ ] Factor inheritance calculations
- [ ] Growth rate application
- [ ] Goal progress tracking
- [ ] Condition application and expiry

### 8.2 Integration Tests

- [ ] Character creation with full workflow
- [ ] Character state updates after training
- [ ] Goal completion and updates
- [ ] Snapshot creation and retrieval
- [ ] Support card deck management
- [ ] Factor inheritance from legacy characters

### 8.3 API Tests

- [ ] POST /api/v1/characters - creation
- [ ] GET /api/v1/characters/{id} - retrieval
- [ ] PATCH /api/v1/characters/{id}/stats - update stats
- [ ] PATCH /api/v1/characters/{id}/condition - update condition
- [ ] Goal CRUD operations
- [ ] Snapshot operations
- [ ] Error handling and validation

### 8.4 Performance Tests

- [ ] Character loading with full relationships < 100ms
- [ ] Bulk stat updates < 200ms
- [ ] Goal progress calculation < 50ms
- [ ] Cache invalidation and refresh < 500ms

---

## 9. Implementation Checklist

### 9.1 Models & Relationships

- [ ] Character model with relationships
- [ ] Stat model and calculations
- [ ] Aptitude model
- [ ] Factor model
- [ ] Goal model
- [ ] Condition model
- [ ] Snapshot model

### 9.2 Controllers & Endpoints

- [ ] CharacterController (CRUD)
- [ ] CharacterStatsController
- [ ] GoalController
- [ ] AptitudeController
- [ ] FactorController
- [ ] ConditionController
- [ ] SnapshotController

### 9.3 Services & Business Logic

- [ ] CharacterService
- [ ] CharacterStateService
- [ ] GoalManagementService
- [ ] FactorInheritanceService
- [ ] GrowthRateService

### 9.4 Repositories

- [ ] CharacterRepository
- [ ] StatRepository
- [ ] AptitudeRepository
- [ ] GoalRepository

### 9.5 Database & Migrations

- [ ] All tables created with indexes
- [ ] Foreign key relationships
- [ ] Seeders for test data

### 9.6 Testing

- [ ] Unit tests (80%+ coverage)
- [ ] Integration tests
- [ ] API tests with assertions

---

**Next Document**: [SPEC-002_Training_Optimization_Technical.md](SPEC-002_Training_Optimization_Technical.md)
