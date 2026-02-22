# D09 - Database Documentation

## Uma Musume Career Planner

**Document Version:** 2.0
**Date:** 2026-01-03
**Status:** Draft

---

## Table of Contents

1. [Overview](#1-overview)
2. [Schema Diagram](#2-schema-diagram)
3. [Table Definitions](#3-table-definitions)
4. [Indexes and Performance](#4-indexes-and-performance)
5. [Canonical Naming Conventions](#5-canonical-naming-conventions)
6. [Data Relationships](#6-data-relationships)

---

## 1. Overview

This document details the database schema for the Uma Musume Career Planner. The schema is designed to be canonical, meaning it standardizes naming conventions across all legacy sources. It supports the "Account Mode" of the application.

### 1.1 Database Configuration

- **Property**: **Database Engine**; **Value**: MySQL 8.0 / MariaDB 10.5+ / SQLite (Dev)
- **Property**: **Charset**; **Value**: `utf8mb4`
- **Property**: **Collation**; **Value**: `utf8mb4_unicode_ci`

### 1.2 Schema Overview

```mermaid
mindmap
  root((Database Schema))
    Authentication
      users
      sessions
    Reference Data
      uma_musumes
      skills
    Core Data
      career_runs
      stat_progress
      skill_career_runs
    Supporting
      race_predictions
      goals
      activity_logs
```

---

## 2. Schema Diagram

### 2.1 Entity Relationship Diagram

```mermaid
erDiagram
    users ||--o{ career_runs : owns
    uma_musumes ||--o{ career_runs : features
    career_runs ||--o{ stat_progress : tracks
    career_runs ||--o{ skill_career_runs : has
    career_runs ||--o{ race_predictions : includes
    career_runs ||--o{ goals : defines
    skills ||--o{ skill_career_runs : referenced_by
    users ||--o{ activity_logs : generates

    users {
        bigint id PK
        string name
        string email UK
        string password
        timestamp created_at
    }

    uma_musumes {
        bigint id PK
        string name
        string name_jp
        int growth_speed
        int growth_stamina
        int growth_power
        int growth_guts
        int growth_wit
        enum aptitude_turf
        enum aptitude_dirt
    }

    career_runs {
        bigint id PK
        uuid uuid UK
        bigint user_id FK
        bigint uma_musume_id FK
        string title
        enum status
        enum career_stage
        int current_turn
        int speed
        int stamina
        int power
        int guts
        int wit
        int total_sp_available
        int stamina_percentage
    }

    skills {
        bigint id PK
        string name UK
        string name_jp
        text description
        enum type
        enum tier
        int sp_cost
    }

    stat_progress {
        bigint id PK
        bigint career_run_id FK
        int turn_number
        int speed
        int stamina
        int power
        int guts
        int wit
    }

    skill_career_runs {
        bigint id PK
        bigint career_run_id FK
        bigint skill_id FK
        enum status
        int turn_acquired
    }
```

### 2.2 Table Categories

```mermaid
pie title Tables by Category
    "Authentication" : 2
    "Reference Data" : 2
    "Core Transactional" : 4
    "Supporting" : 2
```

---

## 3. Table Definitions

### 3.1 `users` (Standard Laravel)

Standard authentication table.

```mermaid
classDiagram
    class users {
        +bigint id PK
        +string name
        +string email UK
        +string password
        +timestamp email_verified_at
        +string remember_token
        +timestamp created_at
        +timestamp updated_at
    }
```

- **Column**: `id`; **Type**: BigInt; **Constraints**: PK, Auto; **Description**: Primary key
- **Column**: `name`; **Type**: String(255); **Constraints**: Not Null; **Description**: User display name
- **Column**: `email`; **Type**: String(255); **Constraints**: Unique, Not Null; **Description**: Login email
- **Column**: `password`; **Type**: String(255); **Constraints**: Not Null; **Description**: Hashed password
- **Column**: `email_verified_at`; **Type**: Timestamp; **Constraints**: Nullable; **Description**: Verification timestamp
- **Column**: `remember_token`; **Type**: String(100); **Constraints**: Nullable; **Description**: Session token
- **Column**: `created_at`; **Type**: Timestamp; **Constraints**: -; **Description**: Creation timestamp
- **Column**: `updated_at`; **Type**: Timestamp; **Constraints**: -; **Description**: Last update timestamp

### 3.2 `uma_musumes` (Reference Data)

Stores static character data.

```mermaid
classDiagram
    class uma_musumes {
        +bigint id PK
        +string name
        +string name_jp
        +string image_path
        +string thumbnail_path
        +int growth_speed
        +int growth_stamina
        +int growth_power
        +int growth_guts
        +int growth_wit
        +enum aptitude_turf
        +enum aptitude_dirt
        +enum aptitude_sprint
        +enum aptitude_mile
        +enum aptitude_medium
        +enum aptitude_long
        +enum aptitude_nige
        +enum aptitude_senkou
        +enum aptitude_sashi
        +enum aptitude_oikomi
    }
```

- **Column**: `id`; **Type**: BigInt; **Constraints**: PK; **Description**: Primary key
- **Column**: `name`; **Type**: String(255); **Constraints**: Not Null; **Description**: English name (e.g., "Special Week")
- **Column**: `name_jp`; **Type**: String(255); **Constraints**: Nullable; **Description**: Japanese name
- **Column**: `image_path`; **Type**: String(255); **Constraints**: Nullable; **Description**: Path to full artwork
- **Column**: `thumbnail_path`; **Type**: String(255); **Constraints**: Nullable; **Description**: Path to icon
- **Column**: `growth_speed`; **Type**: Int; **Constraints**: Default 0; **Description**: Speed growth rate (e.g., 20)
- **Column**: `growth_stamina`; **Type**: Int; **Constraints**: Default 0; **Description**: Stamina growth rate
- **Column**: `growth_power`; **Type**: Int; **Constraints**: Default 0; **Description**: Power growth rate
- **Column**: `growth_guts`; **Type**: Int; **Constraints**: Default 0; **Description**: Guts growth rate
- **Column**: `growth_wit`; **Type**: Int; **Constraints**: Default 0; **Description**: Wit growth rate
- **Column**: `aptitude_*`; **Type**: Enum; **Constraints**: SS-G; **Description**: Various aptitude grades

**Aptitude Enum Values:** `SS`, `S`, `A`, `B`, `C`, `D`, `E`, `F`, `G`

### 3.3 `skills` (Reference Data)

Stores static skill data.

```mermaid
classDiagram
    class skills {
        +bigint id PK
        +string name UK
        +string name_jp
        +text description
        +enum type
        +enum tier
        +int sp_cost
        +string icon_path
        +timestamp created_at
        +timestamp updated_at
    }
```

- **Column**: `id`; **Type**: BigInt; **Constraints**: PK; **Description**: Primary key
- **Column**: `name`; **Type**: String(255); **Constraints**: Unique, Not Null; **Description**: English skill name
- **Column**: `name_jp`; **Type**: String(255); **Constraints**: Nullable; **Description**: Japanese skill name
- **Column**: `description`; **Type**: Text; **Constraints**: Nullable; **Description**: Skill effect description
- **Column**: `type`; **Type**: Enum; **Constraints**: Not Null; **Description**: Skill category
- **Column**: `tier`; **Type**: Enum; **Constraints**: Not Null; **Description**: Skill rarity tier
- **Column**: `sp_cost`; **Type**: Int; **Constraints**: Not Null; **Description**: SP cost to acquire
- **Column**: `icon_path`; **Type**: String(255); **Constraints**: Nullable; **Description**: Path to skill icon

**Type Enum:** `speed`, `stamina`, `power`, `guts`, `wit`, `debuff`

**Tier Enum:** `G-`, `G`, `G+`, `F-`, `F`, `F+`, `E-`, `E`, `E+`, `D-`, `D`, `D+`, `C-`, `C`, `C+`, `B-`, `B`, `B+`, `A-`, `A`, `A+`, `S-`, `S`, `S+`, `SS`

### 3.4 `career_runs` (Core Transactional)

Represents a single training run (Plan).

```mermaid
classDiagram
    class career_runs {
        +bigint id PK
        +uuid uuid UK
        +bigint user_id FK
        +bigint uma_musume_id FK
        +enum storage_mode
        +string title
        +enum status
        +enum career_stage
        +int current_turn
        +int speed
        +int stamina
        +int power
        +int guts
        +int wit
        +int energy
        +enum mood
        +int total_sp_available
        +int stamina_percentage
        +text notes
        +timestamp deleted_at
    }
```

- **Column**: `id`; **Type**: BigInt; **Constraints**: PK; **Description**: Primary key
- **Column**: `uuid`; **Type**: UUID; **Constraints**: Unique; **Description**: Global identifier (aligns with localStorage)
- **Column**: `user_id`; **Type**: BigInt; **Constraints**: FK → users, Nullable; **Description**: Owner user
- **Column**: `uma_musume_id`; **Type**: BigInt; **Constraints**: FK → uma_musumes; **Description**: Selected character
- **Column**: `storage_mode`; **Type**: Enum; **Constraints**: Default 'account'; **Description**: Storage type
- **Column**: `title`; **Type**: String(255); **Constraints**: Not Null; **Description**: Plan title
- **Column**: `status`; **Type**: Enum; **Constraints**: Not Null; **Description**: Current status
- **Column**: `career_stage`; **Type**: Enum; **Constraints**: Not Null; **Description**: Current career stage
- **Column**: `current_turn`; **Type**: Int; **Constraints**: 1-78; **Description**: Current turn number
- **Column**: `speed`; **Type**: Int; **Constraints**: Default 0; **Description**: Current speed stat
- **Column**: `stamina`; **Type**: Int; **Constraints**: Default 0; **Description**: Current stamina stat
- **Column**: `power`; **Type**: Int; **Constraints**: Default 0; **Description**: Current power stat
- **Column**: `guts`; **Type**: Int; **Constraints**: Default 0; **Description**: Current guts stat
- **Column**: `wit`; **Type**: Int; **Constraints**: Default 0; **Description**: Current wit stat
- **Column**: `energy`; **Type**: Int; **Constraints**: 0-100; **Description**: Current energy level
- **Column**: `mood`; **Type**: Enum; **Constraints**: Default 'normal'; **Description**: Current mood
- **Column**: `total_sp_available`; **Type**: Int; **Constraints**: Default 0; **Description**: **Canonical:** Available SP
- **Column**: `stamina_percentage`; **Type**: Int; **Constraints**: 0-100; **Description**: **Canonical:** Stamina %
- **Column**: `notes`; **Type**: Text; **Constraints**: Nullable; **Description**: User notes
- **Column**: `deleted_at`; **Type**: Timestamp; **Constraints**: Nullable; **Description**: Soft delete timestamp

**Status Enum:** `in_progress`, `completed`, `archived`

**Career Stage Enum:** `junior`, `classic`, `senior`

**Mood Enum:** `great`, `good`, `normal`, `bad`, `awful`

### 3.5 `stat_progress` (History)

Turn-by-turn log of stats.

```mermaid
classDiagram
    class stat_progress {
        +bigint id PK
        +bigint career_run_id FK
        +int turn_number
        +int speed
        +int stamina
        +int power
        +int guts
        +int wit
        +timestamp created_at
        +timestamp updated_at
    }
```

- **Column**: `id`; **Type**: BigInt; **Constraints**: PK; **Description**: Primary key
- **Column**: `career_run_id`; **Type**: BigInt; **Constraints**: FK → career_runs; **Description**: Parent plan
- **Column**: `turn_number`; **Type**: Int; **Constraints**: **Canonical**; **Description**: Turn number (1-78)
- **Column**: `speed`; **Type**: Int; **Constraints**: Not Null; **Description**: Speed at this turn
- **Column**: `stamina`; **Type**: Int; **Constraints**: Not Null; **Description**: Stamina at this turn
- **Column**: `power`; **Type**: Int; **Constraints**: Not Null; **Description**: Power at this turn
- **Column**: `guts`; **Type**: Int; **Constraints**: Not Null; **Description**: Guts at this turn
- **Column**: `wit`; **Type**: Int; **Constraints**: Not Null; **Description**: Wit at this turn

**Constraint:** `UNIQUE(career_run_id, turn_number)`

### 3.6 `skill_career_runs` (Pivot)

Skills attached to a plan.

```mermaid
classDiagram
    class skill_career_runs {
        +bigint id PK
        +bigint career_run_id FK
        +bigint skill_id FK
        +enum status
        +int turn_acquired
        +string notes
        +timestamp created_at
        +timestamp updated_at
    }
```

- **Column**: `id`; **Type**: BigInt; **Constraints**: PK; **Description**: Primary key
- **Column**: `career_run_id`; **Type**: BigInt; **Constraints**: FK → career_runs; **Description**: Parent plan
- **Column**: `skill_id`; **Type**: BigInt; **Constraints**: FK → skills; **Description**: Referenced skill
- **Column**: `status`; **Type**: Enum; **Constraints**: Not Null; **Description**: Skill status
- **Column**: `turn_acquired`; **Type**: Int; **Constraints**: Nullable; **Description**: Turn when acquired (required if status='acquired')
- **Column**: `notes`; **Type**: String(255); **Constraints**: Nullable; **Description**: User notes

**Status Enum:** `acquired`, `skipped`, `suggested`

### 3.7 `race_predictions`

```mermaid
classDiagram
    class race_predictions {
        +bigint id PK
        +bigint career_run_id FK
        +string race_name
        +string venue
        +enum distance_category
        +enum track_type
        +int predicted_pos
        +int actual_pos
        +int sort_order
    }
```

- **Column**: `id`; **Type**: BigInt; **Constraints**: PK; **Description**: Primary key
- **Column**: `career_run_id`; **Type**: BigInt; **Constraints**: FK → career_runs; **Description**: Parent plan
- **Column**: `race_name`; **Type**: String(255); **Constraints**: Not Null; **Description**: Race name
- **Column**: `venue`; **Type**: String(255); **Constraints**: Nullable; **Description**: Race venue
- **Column**: `distance_category`; **Type**: Enum; **Constraints**: Not Null; **Description**: Distance type
- **Column**: `track_type`; **Type**: Enum; **Constraints**: Not Null; **Description**: Track surface
- **Column**: `predicted_pos`; **Type**: Int; **Constraints**: Nullable; **Description**: Predicted placement
- **Column**: `actual_pos`; **Type**: Int; **Constraints**: Nullable; **Description**: Actual placement
- **Column**: `sort_order`; **Type**: Int; **Constraints**: Default 0; **Description**: Display order

**Distance Category Enum:** `sprint`, `mile`, `medium`, `long`

**Track Type Enum:** `turf`, `dirt`

### 3.8 `goals`

```mermaid
classDiagram
    class goals {
        +bigint id PK
        +bigint career_run_id FK
        +string description
        +boolean completed
        +int sort_order
    }
```

- **Column**: `id`; **Type**: BigInt; **Constraints**: PK; **Description**: Primary key
- **Column**: `career_run_id`; **Type**: BigInt; **Constraints**: FK → career_runs; **Description**: Parent plan
- **Column**: `description`; **Type**: String(255); **Constraints**: Not Null; **Description**: Goal description
- **Column**: `completed`; **Type**: Boolean; **Constraints**: Default false; **Description**: Completion status
- **Column**: `sort_order`; **Type**: Int; **Constraints**: Default 0; **Description**: Display order

### 3.9 `activity_logs`

```mermaid
classDiagram
    class activity_logs {
        +bigint id PK
        +bigint user_id FK
        +string action
        +string model_type
        +bigint model_id
        +json metadata
        +timestamp created_at
    }
```

- **Column**: `id`; **Type**: BigInt; **Constraints**: PK; **Description**: Primary key
- **Column**: `user_id`; **Type**: BigInt; **Constraints**: FK → users, Nullable; **Description**: Acting user
- **Column**: `action`; **Type**: String(50); **Constraints**: Not Null; **Description**: Action type (create, update, delete)
- **Column**: `model_type`; **Type**: String(100); **Constraints**: Not Null; **Description**: Model class name
- **Column**: `model_id`; **Type**: BigInt; **Constraints**: Not Null; **Description**: Affected model ID
- **Column**: `metadata`; **Type**: JSON; **Constraints**: Nullable; **Description**: Change details
- **Column**: `created_at`; **Type**: Timestamp; **Constraints**: Not Null; **Description**: Action timestamp

---

## 4. Indexes and Performance

### 4.1 Index Strategy

```mermaid
flowchart TD
    subgraph Primary["Primary Indexes"]
        PK["Primary Keys<br/>(All tables)"]
    end

    subgraph Foreign["Foreign Key Indexes"]
        FK1["career_runs.user_id"]
        FK2["career_runs.uma_musume_id"]
        FK3["stat_progress.career_run_id"]
        FK4["skill_career_runs.career_run_id"]
        FK5["skill_career_runs.skill_id"]
    end

    subgraph Query["Query Optimization"]
        Q1["career_runs.status"]
        Q2["skills.name"]
        Q3["stat_progress(career_run_id, turn_number)"]
    end

    PK --> FK1
    PK --> FK2
    FK1 --> Q1
    FK3 --> Q3
```

### 4.2 Index Definitions

- **Table**: `career_runs`; **Index**: `idx_user_id`; **Columns**: `user_id`; **Purpose**: Filtering plans by user
- **Table**: `career_runs`; **Index**: `idx_status`; **Columns**: `status`; **Purpose**: Dashboard filtering
- **Table**: `career_runs`; **Index**: `idx_uuid`; **Columns**: `uuid`; **Purpose**: UUID lookups
- **Table**: `stat_progress`; **Index**: `idx_run_turn`; **Columns**: `career_run_id`, `turn_number`; **Purpose**: Chart data retrieval
- **Table**: `skill_career_runs`; **Index**: `idx_career_run`; **Columns**: `career_run_id`; **Purpose**: Skill list retrieval
- **Table**: `skills`; **Index**: `idx_name`; **Columns**: `name`; **Purpose**: Autocomplete performance
- **Table**: `skills`; **Index**: `idx_type`; **Columns**: `type`; **Purpose**: Type filtering
- **Table**: `activity_logs`; **Index**: `idx_model`; **Columns**: `model_type`, `model_id`; **Purpose**: Audit trail lookup

### 4.3 Query Performance Targets

```mermaid
gantt
    title Query Performance Targets
    dateFormat X
    axisFormat %L ms

    section Read Operations
    Plan List (paginated)    :0, 100
    Plan Detail              :0, 50
    Skill Search             :0, 30
    Turn History             :0, 80

    section Write Operations
    Create Plan              :0, 150
    Update Plan              :0, 100
    Add Skill                :0, 50
```

---

## 5. Canonical Naming Conventions

### 5.1 Mandatory Field Names

The following field names are **MANDATORY** and override any legacy naming:

```mermaid
flowchart LR
    subgraph Legacy["Legacy Names (DO NOT USE)"]
        L1["run_id / plan_id"]
        L2["turn / current_turn"]
        L3["sp / total_sp"]
        L4["stamina_pct"]
    end

    subgraph Canonical["Canonical Names (USE THESE)"]
        C1["career_run_id"]
        C2["turn_number"]
        C3["total_sp_available"]
        C4["stamina_percentage"]
    end

    L1 -->|"Replace with"| C1
    L2 -->|"Replace with"| C2
    L3 -->|"Replace with"| C3
    L4 -->|"Replace with"| C4

    style Legacy fill:#ffcdd2
    style Canonical fill:#c8e6c9
```

- **Legacy Name**: `run_id`, `plan_id`; **Canonical Name**: `career_run_id`; **Context**: Foreign key references
- **Legacy Name**: `turn`, `current_turn`; **Canonical Name**: `turn_number`; **Context**: In history/progress tables
- **Legacy Name**: `sp`, `total_sp`; **Canonical Name**: `total_sp_available`; **Context**: SP tracking
- **Legacy Name**: `stamina_pct`; **Canonical Name**: `stamina_percentage`; **Context**: Stamina tracking

### 5.2 Naming Convention Rules

1. **Tables:** Plural, snake_case (e.g., `career_runs`, `skill_career_runs`)
2. **Columns:** Singular, snake_case (e.g., `turn_number`, `sp_cost`)
3. **Foreign Keys:** `{singular_table}_id` (e.g., `career_run_id`, `skill_id`)
4. **Timestamps:** `created_at`, `updated_at`, `deleted_at`
5. **Booleans:** Positive form (e.g., `completed`, not `is_completed`)

---

## 6. Data Relationships

### 6.1 Relationship Diagram

```mermaid
flowchart TD
    subgraph Auth["Authentication"]
        Users["users"]
    end

    subgraph Reference["Reference Data"]
        UmaMusumes["uma_musumes"]
        Skills["skills"]
    end

    subgraph Core["Core Data"]
        CareerRuns["career_runs"]
        StatProgress["stat_progress"]
        SkillCareerRuns["skill_career_runs"]
    end

    subgraph Supporting["Supporting Data"]
        RacePredictions["race_predictions"]
        Goals["goals"]
        ActivityLogs["activity_logs"]
    end

    Users -->|"1:N"| CareerRuns
    UmaMusumes -->|"1:N"| CareerRuns
    CareerRuns -->|"1:N"| StatProgress
    CareerRuns -->|"N:M"| Skills
    Skills -->|"via"| SkillCareerRuns
    CareerRuns -->|"1:N"| RacePredictions
    CareerRuns -->|"1:N"| Goals
    Users -->|"1:N"| ActivityLogs
```

### 6.2 Relationship Summary

- **Relationship**: User → CareerRuns; **Type**: One-to-Many; **Description**: User owns multiple plans
- **Relationship**: UmaMusume → CareerRuns; **Type**: One-to-Many; **Description**: Character featured in plans
- **Relationship**: CareerRun → StatProgress; **Type**: One-to-Many; **Description**: Turn-by-turn history
- **Relationship**: CareerRun ↔ Skills; **Type**: Many-to-Many; **Description**: Skills attached to plans
- **Relationship**: CareerRun → RacePredictions; **Type**: One-to-Many; **Description**: Race predictions per plan
- **Relationship**: CareerRun → Goals; **Type**: One-to-Many; **Description**: Goals per plan
- **Relationship**: User → ActivityLogs; **Type**: One-to-Many; **Description**: User activity audit

### 6.3 Cascade Rules

```mermaid
flowchart TD
    CareerRun["career_runs<br/>(Soft Delete)"]

    CareerRun -->|"CASCADE"| StatProgress["stat_progress"]
    CareerRun -->|"CASCADE"| SkillCareerRuns["skill_career_runs"]
    CareerRun -->|"CASCADE"| RacePredictions["race_predictions"]
    CareerRun -->|"CASCADE"| Goals["goals"]

    User["users<br/>(Soft Delete)"]
    User -->|"SET NULL"| CareerRun
    User -->|"SET NULL"| ActivityLogs["activity_logs"]
```

- **Parent**: `career_runs`; **Child**: `stat_progress`; **On Delete**: CASCADE
- **Parent**: `career_runs`; **Child**: `skill_career_runs`; **On Delete**: CASCADE
- **Parent**: `career_runs`; **Child**: `race_predictions`; **On Delete**: CASCADE
- **Parent**: `career_runs`; **Child**: `goals`; **On Delete**: CASCADE
- **Parent**: `users`; **Child**: `career_runs`; **On Delete**: SET NULL
- **Parent**: `users`; **Child**: `activity_logs`; **On Delete**: SET NULL
- **Parent**: `uma_musumes`; **Child**: `career_runs`; **On Delete**: RESTRICT
- **Parent**: `skills`; **Child**: `skill_career_runs`; **On Delete**: RESTRICT

---

## Document History

- **Version**: 1.0; **Date**: 2026-01-03; **Author**: Development Team; **Changes**: Initial draft
- **Version**: 2.0; **Date**: 2026-01-03; **Author**: Development Team; **Changes**: Added Mermaid diagrams, expanded documentation
