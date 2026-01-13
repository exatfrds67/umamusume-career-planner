# Database Documentation (DBD)

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0
**Date**: January 14, 2026
**Project**: UmamusumeCareerPlanner
**Author**: Development Team
**Updated**: Aligned with Laravel 12, modern database practices, and
performance optimization

---

## Table of Contents

1. [Introduction](#introduction)
2. [Database Architecture](#2-database-architecture)
3. [Schema Design](#3-schema-design)
4. [Data Models](#4-data-models)
5. [Relationships](#5-relationships)
6. [Indexing Strategy](#6-indexing-strategy)
7. [Performance Optimization](#7-performance-optimization)
8. [Caching Strategy](#8-caching-strategy)

---

## Introduction

### Purpose

This Database Documentation (DBD) provides comprehensive documentation
for the Umamusume Career Planner database system, including schema
design, relationships, performance optimization strategies, and
operational procedures.

### Database Technologies

- **Primary Database**: MySQL 8.0+ with InnoDB engine
- **Caching Layer**: Redis 7.0+ for session storage and application
  caching
- **Search Engine**: MySQL Full-Text Search with potential Elasticsearch
  integration
- **Connection Pooling**: Laravel database connection pooling
- **ORM**: Laravel Eloquent ORM with strict mode enabled

### Design Principles

#### Normalization Strategy

- **Third Normal Form (3NF)**: Primary design target for data integrity
- **Selective Denormalization**: Performance-critical queries with
  controlled redundancy
- **JSON Columns**: Flexible schema for game-specific data structures
- **Audit Trails**: Comprehensive change tracking for critical data

#### Performance Principles

- **Index Optimization**: Strategic indexing for query performance
- **Partitioning**: Time-based partitioning for large historical data
- **Caching Layers**: Multi-level caching strategy
- **Query Optimization**: Efficient query patterns and execution plans

---

---

## 2. Database Architecture

### 2.1 Multi-Database Architecture

```text
┌─────────────────────────────────────────────────────────────────┐
│                    APPLICATION LAYER                            │
├─────────────────────────────────────────────────────────────────┤
│  Laravel 12 with Eloquent ORM (Strict Mode)                    │
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐   │
│  │   Models        │ │   Repositories  │ │   Services      │   │
│  │   (Eloquent)    │ │   (Data Access) │ │   (Business)    │   │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                    CACHING LAYER                                │
├─────────────────────────────────────────────────────────────────┤
│  Redis Cluster (Multi-Level Caching)                           │
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐   │
│  │   Query Cache   │ │   Session Store │ │   App Cache     │   │
│  │   (Short TTL)   │ │   (User State)  │ │   (Long TTL)    │   │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                    DATABASE LAYER                               │
├─────────────────────────────────────────────────────────────────┤
│  MySQL 8.0 Cluster (Primary/Replica Configuration)             │
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐   │
│  │   Primary DB    │ │   Read Replica  │ │   Analytics DB  │   │
│  │   (Write/Read)  │ │   (Read Only)   │ │   (Reporting)   │   │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
```

### 2.2 Connection Configuration

```php
<?php

// config/database.php
return [
    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'umamusume_planner'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => 'InnoDB',
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            ]) : [],
        ],

        'mysql_read' => [
            'driver' => 'mysql',
            'read' => [
                'host' => [
                    env('DB_READ_HOST_1', '127.0.0.1'),
                    env('DB_READ_HOST_2', '127.0.0.1'),
                ],
            ],
            'write' => [
                'host' => [env('DB_WRITE_HOST', '127.0.0.1')],
            ],
            'sticky' => true,
            // ... other configuration
        ],

        'redis' => [
            'driver' => 'redis',
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
            'read_write_timeout' => 60,
            'context' => [
                'auth' => [env('REDIS_PASSWORD')],
            ],
        ],
    ],
];
```

---

## 3. Schema Design

### 3.1 Core Entity Tables

#### 3.1.1 User Management Schema

```sql
-- Users table with enhanced security
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    avatar_url VARCHAR(500) NULL,
    timezone VARCHAR(50) DEFAULT 'UTC',
    locale VARCHAR(10) DEFAULT 'en',
    preferences JSON NULL,
    mfa_settings JSON NULL,
    last_login_at TIMESTAMP NULL,
    last_login_ip VARCHAR(45) NULL,
    login_count INT UNSIGNED DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    email_notifications BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_email (email),
    INDEX idx_uuid (uuid),
    INDEX idx_active (is_active),
    INDEX idx_last_login (last_login_at),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User sessions for tracking
CREATE TABLE user_sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,

    INDEX idx_user_id (user_id),
    INDEX idx_last_activity (last_activity),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 3.1.2 Game Data Schema

```sql
-- Characters table (user-owned character instances)
CREATE TABLE characters (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    user_id BIGINT UNSIGNED NOT NULL,
    character_template_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    nickname VARCHAR(100) NULL,
    scenario_id BIGINT UNSIGNED NULL,
    current_turn SMALLINT UNSIGNED DEFAULT 0,
    max_turns SMALLINT UNSIGNED DEFAULT 78,

    -- Current stats (dynamic)
    current_stats JSON NOT NULL DEFAULT '{}',
    -- Base aptitudes (from template)
    aptitudes JSON NOT NULL DEFAULT '{}',
    -- Growth rates and modifiers
    growth_rates JSON NULL,
    -- Training history summary
    training_summary JSON NULL,
    -- Goals and objectives
    goals JSON NULL,
    -- Current status and conditions
    status JSON NULL,

    is_active BOOLEAN DEFAULT TRUE,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_user_characters (user_id, is_active),
    INDEX idx_character_template (character_template_id),
    INDEX idx_scenario (scenario_id),
    INDEX idx_current_turn (current_turn),
    INDEX idx_uuid (uuid),
    INDEX idx_created_at (created_at),

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (character_template_id) REFERENCES character_templates(id),
    FOREIGN KEY (scenario_id) REFERENCES scenarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Character templates (game data from APIs)
CREATE TABLE character_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    external_id VARCHAR(50) NULL,
    name VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NULL,
    name_jp VARCHAR(100) NULL,
    rarity TINYINT UNSIGNED NOT NULL,

    -- Base aptitudes
    aptitudes JSON NOT NULL DEFAULT '{}',
    -- Base stats
    base_stats JSON NOT NULL DEFAULT '{}',
    -- Growth rate modifiers
    growth_modifiers JSON NULL,
    -- Available skills
    available_skills JSON NULL,
    -- Character-specific data
    metadata JSON NULL,

    -- Data source tracking
    data_source VARCHAR(50) NOT NULL DEFAULT 'umapyoi',
    data_version VARCHAR(20) NULL,
    last_synced_at TIMESTAMP NULL,

    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_external_id (external_id),
    INDEX idx_name (name),
    INDEX idx_rarity (rarity),
    INDEX idx_data_source (data_source),
    INDEX idx_last_synced (last_synced_at),
    UNIQUE KEY uk_external_source (external_id, data_source)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 3.1.3 Training and Events Schema

```sql
-- Training sessions (detailed turn-by-turn data)
CREATE TABLE training_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    character_id BIGINT UNSIGNED NOT NULL,
    turn_number SMALLINT UNSIGNED NOT NULL,
    session_type ENUM('training', 'race', 'rest', 'event', 'special') NOT NULL,

    -- Pre-session state
    stats_before JSON NOT NULL,
    conditions_before JSON NULL,

    -- Actions taken
    primary_action VARCHAR(100) NOT NULL,
    secondary_actions JSON NULL,
    support_cards_used JSON NULL,

    -- Results
    stats_gained JSON NOT NULL,
    stats_after JSON NOT NULL,
    conditions_after JSON NULL,
    events_triggered JSON NULL,
    skills_learned JSON NULL,

    -- AI recommendations and analysis
    ai_recommendation JSON NULL,
    ai_confidence DECIMAL(3,2) NULL,
    user_followed_ai BOOLEAN NULL,

    -- Performance metrics
    success_rate DECIMAL(5,2) NULL,
    efficiency_score DECIMAL(5,2) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_character_sessions (character_id, turn_number),
    INDEX idx_session_type (session_type),
    INDEX idx_turn_number (turn_number),
    INDEX idx_uuid (uuid),
    INDEX idx_created_at (created_at),

    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
    UNIQUE KEY uk_character_turn (character_id, turn_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Race results and performance
CREATE TABLE race_results (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    character_id BIGINT UNSIGNED NOT NULL,
    race_id BIGINT UNSIGNED NOT NULL,
    turn_number SMALLINT UNSIGNED NOT NULL,

    -- Race details
    race_name VARCHAR(200) NOT NULL,
    race_grade ENUM('G1', 'G2', 'G3', 'OP', 'Pre-OP', 'Debut') NOT NULL,
    distance SMALLINT UNSIGNED NOT NULL,
    surface ENUM('turf', 'dirt') NOT NULL,
    track_condition ENUM('good', 'slightly_heavy', 'heavy', 'bad') DEFAULT 'good',

    -- Performance
    finish_position TINYINT UNSIGNED NOT NULL,
    total_runners TINYINT UNSIGNED NOT NULL,
    finish_time DECIMAL(6,3) NULL,

    -- Stats at race time
    stats_at_race JSON NOT NULL,
    skills_active JSON NULL,

    -- Rewards and consequences
    fan_gain INT DEFAULT 0,
    skill_points_gain SMALLINT DEFAULT 0,
    prize_money INT DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_character_races (character_id, turn_number),
    INDEX idx_race_performance (race_id, finish_position),
    INDEX idx_race_grade (race_grade),
    INDEX idx_uuid (uuid),

    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
    FOREIGN KEY (race_id) REFERENCES races(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 3.1.4 AI Integration Schema

```sql
-- AI conversations and recommendations
CREATE TABLE ai_conversations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    user_id BIGINT UNSIGNED NOT NULL,
    character_id BIGINT UNSIGNED NULL,
    conversation_type ENUM('recommendation', 'analysis', 'planning', 'general', 'optimization') NOT NULL,

    -- AI model information
    model_provider ENUM('ollama', 'bedrock') NOT NULL,
    model_name VARCHAR(100) NOT NULL,
    model_version VARCHAR(50) NULL,

    -- Conversation data (encrypted)
    prompt_text TEXT NOT NULL,
    response_text TEXT NOT NULL,
    context_data JSON NULL,

    -- Performance metrics
    processing_time_ms INT UNSIGNED NOT NULL,
    token_count_input INT UNSIGNED NULL,
    token_count_output INT UNSIGNED NULL,
    cost_usd DECIMAL(8,6) DEFAULT 0.000000,

    -- Quality metrics
    confidence_score DECIMAL(3,2) NULL,
    user_rating TINYINT UNSIGNED NULL,
    user_feedback TEXT NULL,

    -- Metadata
    metadata JSON NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_user_conversations (user_id, created_at DESC),
    INDEX idx_character_conversations (character_id, created_at DESC),
    INDEX idx_conversation_type (conversation_type),
    INDEX idx_model_provider (model_provider, model_name),
    INDEX idx_uuid (uuid),
    INDEX idx_cost_tracking (created_at, cost_usd),

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- AI usage analytics and cost tracking
CREATE TABLE ai_usage_analytics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    date DATE NOT NULL,

    -- Usage counts by provider
    ollama_requests INT UNSIGNED DEFAULT 0,
    bedrock_requests INT UNSIGNED DEFAULT 0,

    -- Token usage
    total_input_tokens INT UNSIGNED DEFAULT 0,
    total_output_tokens INT UNSIGNED DEFAULT 0,

    -- Cost tracking
    daily_cost_usd DECIMAL(8,6) DEFAULT 0.000000,

    -- Performance metrics
    avg_response_time_ms INT UNSIGNED NULL,
    success_rate DECIMAL(5,2) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_user_date (user_id, date),
    INDEX idx_date_cost (date, daily_cost_usd),
    UNIQUE KEY uk_user_date (user_id, date),

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3.2 Reference Data Tables

#### 3.2.1 Game Reference Data

```sql
-- Scenarios (URA Finals, Aoharu Cup, etc.)
CREATE TABLE scenarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NULL,
    description TEXT NULL,
    max_turns SMALLINT UNSIGNED DEFAULT 78,
    special_rules JSON NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_name (name),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Skills reference data
CREATE TABLE skills (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    external_id VARCHAR(50) NULL,
    name VARCHAR(200) NOT NULL,
    name_en VARCHAR(200) NULL,
    description TEXT NULL,
    skill_type ENUM('normal', 'rare', 'unique', 'evolution') NOT NULL,
    category VARCHAR(50) NULL,
    effects JSON NULL,
    requirements JSON NULL,
    data_source VARCHAR(50) NOT NULL DEFAULT 'umapyoi',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_external_id (external_id),
    INDEX idx_name (name),
    INDEX idx_skill_type (skill_type),
    INDEX idx_category (category),
    UNIQUE KEY uk_external_source (external_id, data_source)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Support cards reference data
CREATE TABLE support_cards (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    external_id VARCHAR(50) NULL,
    name VARCHAR(200) NOT NULL,
    name_en VARCHAR(200) NULL,
    character_name VARCHAR(100) NULL,
    card_type ENUM('speed', 'stamina', 'power', 'guts', 'wit', 'pal') NOT NULL,
    rarity ENUM('R', 'SR', 'SSR') NOT NULL,

    -- Card effects and bonuses
    training_effects JSON NULL,
    race_effects JSON NULL,
    unique_effects JSON NULL,

    -- Limit break effects
    limit_break_effects JSON NULL,

    data_source VARCHAR(50) NOT NULL DEFAULT 'umapyoi',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_external_id (external_id),
    INDEX idx_name (name),
    INDEX idx_card_type (card_type),
    INDEX idx_rarity (rarity),
    INDEX idx_character_name (character_name),
    UNIQUE KEY uk_external_source (external_id, data_source)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 4. Data Models

### 4.1 Eloquent Model Specifications

#### 4.1.1 User Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'avatar_url',
        'timezone',
        'locale',
        'preferences',
        'email_notifications',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'mfa_settings',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'preferences' => 'array',
        'mfa_settings' => 'encrypted:array',
        'is_active' => 'boolean',
        'email_notifications' => 'boolean',
        'login_count' => 'integer',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function characters(): HasMany
    {
        return $this->hasMany(Character::class);
    }

    public function aiConversations(): HasMany
    {
        return $this->hasMany(AIConversation::class);
    }

    public function usageAnalytics(): HasMany
    {
        return $this->hasMany(AIUsageAnalytics::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Accessors & Mutators
    public function getAvatarUrlAttribute($value): string
    {
        return $value ?: $this->generateGravatarUrl();
    }

    private function generateGravatarUrl(): string
    {
        $hash = md5(strtolower(trim($this->email)));
        return "https://www.gravatar.com/avatar/{$hash}?d=identicon&s=200";
    }
}
```

#### 4.1.2 Character Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Character extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'character_template_id',
        'name',
        'nickname',
        'scenario_id',
        'current_turn',
        'max_turns',
        'current_stats',
        'aptitudes',
        'growth_rates',
        'training_summary',
        'goals',
        'status',
        'is_active',
        'completed_at',
    ];

    protected $casts = [
        'current_stats' => 'array',
        'aptitudes' => 'array',
        'growth_rates' => 'array',
        'training_summary' => 'array',
        'goals' => 'array',
        'status' => 'array',
        'is_active' => 'boolean',
        'completed_at' => 'datetime',
        'current_turn' => 'integer',
        'max_turns' => 'integer',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function characterTemplate(): BelongsTo
    {
        return $this->belongsTo(CharacterTemplate::class);
    }

    public function scenario(): BelongsTo
    {
        return $this->belongsTo(Scenario::class);
    }

    public function trainingSessions(): HasMany
    {
        return $this->hasMany(TrainingSession::class);
    }

    public function raceResults(): HasMany
    {
        return $this->hasMany(RaceResult::class);
    }

    public function aiConversations(): HasMany
    {
        return $this->hasMany(AIConversation::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInProgress($query)
    {
        return $query->where('is_active', true)
                    ->whereNull('completed_at');
    }

    // Accessors
    public function getCurrentStatsAttribute($value): array
    {
        $stats = json_decode($value, true) ?: [];

        return array_merge([
            'speed' => 0,
            'stamina' => 0,
            'power' => 0,
            'guts' => 0,
            'wit' => 0,
            'skill_points' => 0,
            'fans' => 0,
        ], $stats);
    }

    // Business Logic Methods
    public function getTotalStats(): int
    {
        $stats = $this->current_stats;
        return $stats['speed'] + $stats['stamina'] + $stats['power'] +
               $stats['guts'] + $stats['wit'];
    }

    public function getProgressPercentage(): float
    {
        return ($this->current_turn / $this->max_turns) * 100;
    }

    public function canTrain(): bool
    {
        return $this->is_active &&
               $this->current_turn < $this->max_turns &&
               is_null($this->completed_at);
    }
}
```

---

## 5. Relationships

### 5.1 Entity Relationship Diagram

```text
Users (1) ──────────── (M) Characters
  │                         │
  │                         │
  │                         ├── (M) TrainingSession
  │                         ├── (M) RaceResults
  │                         └── (M) AIConversations
  │
  └── (M) AIConversations
  └── (M) AIUsageAnalytics

Characters (M) ──── (1) CharacterTemplates
Characters (M) ──── (1) Scenarios

CharacterTemplates (M) ──── (M) Skills
SupportCards (M) ──────── (M) Skills

TrainingSessions (M) ──── (M) SupportCards (used_in_training)
RaceResults (M) ────────── (1) Races
```

### 5.2 Relationship Specifications

#### 5.2.1 Core Relationships

```php
// User -> Characters (One-to-Many)
// A user can have multiple characters
User::class -> hasMany(Character::class)
Character::class -> belongsTo(User::class)

// Character -> TrainingSessions (One-to-Many)
// A character has multiple training sessions
Character::class -> hasMany(TrainingSession::class)
TrainingSession::class -> belongsTo(Character::class)

// Character -> CharacterTemplate (Many-to-One)
// Multiple characters can use the same template
Character::class -> belongsTo(CharacterTemplate::class)
CharacterTemplate::class -> hasMany(Character::class)
```

#### 5.2.2 AI Integration Relationships

```php
// User -> AIConversations (One-to-Many)
User::class -> hasMany(AIConversation::class)
AIConversation::class -> belongsTo(User::class)

// Character -> AIConversations (One-to-Many, Optional)
Character::class -> hasMany(AIConversation::class)
AIConversation::class -> belongsTo(Character::class)->nullable()

// User -> AIUsageAnalytics (One-to-Many)
User::class -> hasMany(AIUsageAnalytics::class)
AIUsageAnalytics::class -> belongsTo(User::class)
```

---

## 6. Indexing Strategy

### 6.1 Primary Indexes

#### 6.1.1 Performance-Critical Indexes

```sql
-- User-related queries
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_active ON users(is_active);
CREATE INDEX idx_users_last_login ON users(last_login_at);

-- Character queries (most frequent)
CREATE INDEX idx_characters_user_active ON characters(user_id, is_active);
CREATE INDEX idx_characters_template ON characters(character_template_id);
CREATE INDEX idx_characters_scenario ON characters(scenario_id);
CREATE INDEX idx_characters_progress ON characters(current_turn, max_turns);

-- Training session queries
CREATE INDEX idx_training_character_turn ON training_sessions(character_id, turn_number);
CREATE INDEX idx_training_type ON training_sessions(session_type);
CREATE INDEX idx_training_created ON training_sessions(created_at);

-- AI conversation queries
CREATE INDEX idx_ai_user_date ON ai_conversations(user_id, created_at DESC);
CREATE INDEX idx_ai_character_date ON ai_conversations(character_id, created_at DESC);
CREATE INDEX idx_ai_type ON ai_conversations(conversation_type);
CREATE INDEX idx_ai_model ON ai_conversations(model_provider, model_name);

-- Cost tracking queries
CREATE INDEX idx_ai_cost_date ON ai_conversations(created_at, cost_usd);
CREATE INDEX idx_usage_user_date ON ai_usage_analytics(user_id, date);
```

#### 6.1.2 Composite Indexes for Complex Queries

```sql
-- Multi-column indexes for common query patterns
CREATE INDEX idx_characters_user_scenario_active ON characters(user_id, scenario_id, is_active);
CREATE INDEX idx_training_character_type_turn ON training_sessions(character_id, session_type, turn_number);
CREATE INDEX idx_ai_user_type_date ON ai_conversations(user_id, conversation_type, created_at DESC);

-- Full-text search indexes
CREATE FULLTEXT INDEX idx_characters_search ON characters(name, nickname);
CREATE FULLTEXT INDEX idx_character_templates_search ON character_templates(name, name_en, name_jp);
CREATE FULLTEXT INDEX idx_skills_search ON skills(name, name_en, description);
```

### 6.2 Index Maintenance

#### 6.2.1 Index Monitoring

```php
<?php

namespace App\Console\Commands;

class MonitorIndexPerformance extends Command
{
    protected $signature = 'db:monitor-indexes';
    protected $description = 'Monitor database index performance and usage';

    public function handle()
    {
        $this->info('Analyzing index performance...');

        // Check index usage statistics
        $indexStats = DB::select("
            SELECT
                TABLE_NAME,
                INDEX_NAME,
                CARDINALITY,
                NULLABLE,
                INDEX_TYPE
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
            ORDER BY TABLE_NAME, SEQ_IN_INDEX
        ");

        // Check for unused indexes
        $unusedIndexes = DB::select("
            SELECT
                object_schema,
                object_name,
                index_name
            FROM performance_schema.table_io_waits_summary_by_index_usage
            WHERE index_name IS NOT NULL
            AND count_star = 0
            AND object_schema = DATABASE()
        ");

        if (!empty($unusedIndexes)) {
            $this->warn('Found potentially unused indexes:');
            foreach ($unusedIndexes as $index) {
                $this->line("- {$index->object_name}.{$index->index_name}");
            }
        }

        // Check for missing indexes on foreign keys
        $this->checkForeignKeyIndexes();

        $this->info('Index analysis complete.');
    }

    private function checkForeignKeyIndexes()
    {
        $missingIndexes = DB::select("
            SELECT
                TABLE_NAME,
                COLUMN_NAME,
                CONSTRAINT_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME NOT IN (
                SELECT DISTINCT TABLE_NAME
                FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE()
                AND COLUMN_NAME = KEY_COLUMN_USAGE.COLUMN_NAME
            )
        ");

        if (!empty($missingIndexes)) {
            $this->warn('Foreign keys without indexes found:');
            foreach ($missingIndexes as $fk) {
                $this->line("- {$fk->TABLE_NAME}.{$fk->COLUMN_NAME}");
            }
        }
    }
}
```

---

## 7. Performance Optimization

### 7.1 Query Optimization Strategies

#### 7.1.1 Eloquent Query Optimization

```php
<?php

namespace App\Repositories;

class CharacterRepository
{
    public function getUserCharactersWithStats(User $user): Collection
    {
        return Character::select([
                'id', 'uuid', 'name', 'nickname', 'current_turn',
                'max_turns', 'current_stats', 'is_active', 'updated_at'
            ])
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->with([
                'characterTemplate:id,name,rarity',
                'scenario:id,name'
            ])
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    public function getCharacterTrainingHistory(Character $character, int $limit = 50): Collection
    {
        return TrainingSession::select([
                'id', 'turn_number', 'session_type', 'primary_action',
                'stats_gained', 'created_at'
            ])
            ->where('character_id', $character->id)
            ->orderBy('turn_number', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getCharacterAnalytics(Character $character): array
    {
        // Use raw queries for complex analytics
        $statsProgression = DB::select("
            SELECT
                turn_number,
                JSON_EXTRACT(stats_after, '$.speed') as speed,
                JSON_EXTRACT(stats_after, '$.stamina') as stamina,
                JSON_EXTRACT(stats_after, '$.power') as power,
                JSON_EXTRACT(stats_after, '$.guts') as guts,
                JSON_EXTRACT(stats_after, '$.wit') as wit
            FROM training_sessions
            WHERE character_id = ?
            ORDER BY turn_number ASC
        ", [$character->id]);

        return [
            'stats_progression' => $statsProgression,
            'total_sessions' => $character->trainingSessions()->count(),
            'avg_stats_per_turn' => $this->calculateAverageStatsGain($character),
        ];
    }
}
```

#### 7.1.2 Database Connection Optimization

```php
<?php

namespace App\Providers;

class DatabaseServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Enable query logging in development
        if (app()->environment('local')) {
            DB::listen(function ($query) {
                if ($query->time > 1000) { // Log slow queries (>1s)
                    Log::warning('Slow query detected', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time' => $query->time
                    ]);
                }
            });
        }

        // Configure connection pooling
        $this->configureConnectionPooling();

        // Set up read/write splitting
        $this->configureReadWriteSplitting();
    }

    private function configureConnectionPooling()
    {
        config([
            'database.connections.mysql.options' => array_merge(
                config('database.connections.mysql.options', []),
                [
                    PDO::ATTR_PERSISTENT => true,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode='STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'"
                ]
            )
        ]);
    }
}
```

### 7.2 Partitioning Strategy

#### 7.2.1 Time-Based Partitioning

```sql
-- Partition training_sessions by month for better performance
ALTER TABLE training_sessions
PARTITION BY RANGE (YEAR(created_at) * 100 + MONTH(created_at)) (
    PARTITION p202601 VALUES LESS THAN (202602),
    PARTITION p202602 VALUES LESS THAN (202603),
    PARTITION p202603 VALUES LESS THAN (202604),
    PARTITION p202604 VALUES LESS THAN (202605),
    PARTITION p202605 VALUES LESS THAN (202606),
    PARTITION p202606 VALUES LESS THAN (202607),
    PARTITION p202607 VALUES LESS THAN (202608),
    PARTITION p202608 VALUES LESS THAN (202609),
    PARTITION p202609 VALUES LESS THAN (202610),
    PARTITION p202610 VALUES LESS THAN (202611),
    PARTITION p202611 VALUES LESS THAN (202612),
    PARTITION p202612 VALUES LESS THAN (202701),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);

-- Partition ai_conversations by month for cost tracking
ALTER TABLE ai_conversations
PARTITION BY RANGE (YEAR(created_at) * 100 + MONTH(created_at)) (
    PARTITION p202601 VALUES LESS THAN (202602),
    PARTITION p202602 VALUES LESS THAN (202603),
    PARTITION p202603 VALUES LESS THAN (202604),
    PARTITION p202604 VALUES LESS THAN (202605),
    PARTITION p202605 VALUES LESS THAN (202606),
    PARTITION p202606 VALUES LESS THAN (202607),
    PARTITION p202607 VALUES LESS THAN (202608),
    PARTITION p202608 VALUES LESS THAN (202609),
    PARTITION p202609 VALUES LESS THAN (202610),
    PARTITION p202610 VALUES LESS THAN (202611),
    PARTITION p202611 VALUES LESS THAN (202612),
    PARTITION p202612 VALUES LESS THAN (202701),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);
```

---

## 8. Caching Strategy

### 8.1 Multi-Level Caching Implementation

#### 8.1.1 Application-Level Caching

```php
<?php

namespace App\Services\Cache;

class GameDataCacheService
{
    private const CACHE_TAGS = [
        'characters' => ['game_data', 'characters'],
        'skills' => ['game_data', 'skills'],
        'support_cards' => ['game_data', 'support_cards'],
        'user_data' => ['user_data'],
        'ai_responses' => ['ai_data', 'temporary']
    ];

    private const CACHE_TTL = [
        'character_templates' => 86400, // 24 hours
        'skills' => 86400, // 24 hours
        'support_cards' => 86400, // 24 hours
        'user_characters' => 3600, // 1 hour
        'training_history' => 1800, // 30 minutes
        'ai_responses' => 300, // 5 minutes
    ];

    public function cacheCharacterTemplates(): void
    {
        $templates = CharacterTemplate::active()
            ->select(['id', 'name', 'name_en', 'rarity', 'aptitudes', 'base_stats'])
            ->get()
            ->keyBy('id');

        Cache::tags(self::CACHE_TAGS['characters'])
            ->put('character_templates:all', $templates, self::CACHE_TTL['character_templates']);
    }

    public function getUserCharacters(User $user): Collection
    {
        $cacheKey = "user_characters:{$user->id}";

        return Cache::tags(self::CACHE_TAGS['user_data'])
            ->remember($cacheKey, self::CACHE_TTL['user_characters'], function () use ($user) {
                return $user->characters()
                    ->active()
                    ->with(['characterTemplate:id,name,rarity', 'scenario:id,name'])
                    ->get();
            });
    }

    public function invalidateUserCache(User $user): void
    {
        Cache::tags(self::CACHE_TAGS['user_data'])->flush();

        // Also clear specific user caches
        $patterns = [
            "user_characters:{$user->id}",
            "user_analytics:{$user->id}:*",
            "ai_conversations:{$user->id}:*"
        ];

        foreach ($patterns as $pattern) {
            $this->clearCachePattern($pattern);
        }
    }

    private function clearCachePattern(string $pattern): void
    {
        $keys = Redis::keys($pattern);
        if (!empty($keys)) {
            Redis::del($keys);
        }
    }
}
```

#### 8.1.2 Query Result Caching

```php
<?php

namespace App\Models\Concerns;

trait CacheableQueries
{
    public function scopeCached($query, string $key = null, int $ttl = 3600)
    {
        $cacheKey = $key ?: $this->generateCacheKey($query);

        return Cache::remember($cacheKey, $ttl, function () use ($query) {
            return $query->get();
        });
    }

    private function generateCacheKey($query): string
    {
        $sql = $query->toSql();
        $bindings = $query->getBindings();

        return 'query:' . md5($sql . serialize($bindings));
    }

    public static function bootCacheableQueries()
    {
        // Clear cache on model changes
        static::saved(function ($model) {
            $model->clearModelCache();
        });

        static::deleted(function ($model) {
            $model->clearModelCache();
        });
    }

    public function clearModelCache(): void
    {
        $tags = $this->getCacheTags();
        Cache::tags($tags)->flush();
    }

    protected function getCacheTags(): array
    {
        return [strtolower(class_basename($this))];
    }
}
```

---

## Conclusion

This Database Documentation provides a comprehensive foundation for the Umamusume Career Planner database system, ensuring optimal performance, scalability, and maintainability while supporting the application's advanced AI integration and real-time features.

---

## Document Control

| Version | Date | Author | Changes |
| ------- | ---- | ------ | ------- |
| 1.0 | 2026-01-11 | Development Team | Initial database documentation |

---

*This document provides comprehensive database documentation for the Umamusume Pretty Derby Career Planner system, including schema design, relationships, indexing strategy, and performance optimization.*
