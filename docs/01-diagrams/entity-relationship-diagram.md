# Umamusume Career Planner - Entity Relationship Diagram (ERD)

## Overview

This document presents the Entity Relationship Diagrams for the Umamusume Pretty Derby Career Planner application, showing the data model structure, relationships between entities, and database schema design that supports all **59 requirements**. The system is built with **Laravel 12** (released February 24, 2025) with **TypeScript support**, **Tailwind CSS v4**, and integrates with **AWS Bedrock** and **Ollama** for AI capabilities.

## High-Level Entity Overview

### Text Description

The database consists of 15 major entity groups:

1. **User Management**: User profiles and preferences
2. **Character System**: Umamusume characters, stats, and aptitudes
3. **Career Management**: Career runs, progression, and historical data
4. **Support Card System**: Support cards, decks, and friendship data
5. **Skill System**: Skills, hints, evolution chains, and acquisition tracking
6. **Training System**: Training sessions, predictions, and outcomes
7. **Race System**: Races, results, and performance analytics
8. **Inheritance System**: Legacy characters, factors, and breeding data
9. **Item System**: Items, inventory, and usage tracking
10. **PvP System**: Champions Meeting teams and tournament data
11. **Event System**: Events, choices, and seasonal content
12. **Meta System**: Community data, tier lists, and competitive analysis
13. **AI System**: Conversation history and AI interaction data
14. **Analytics System**: Performance metrics and statistical analysis
15. **External Integration**: API data synchronization and community connections

### ASCII Entity Overview

```text
[User] ----< [Career] >---- [Character]
  |            |               |
  |            |               |
  v            v               v
[Preferences] [Training]    [Stats]
              [Sessions]    [Aptitudes]
                 |            |
                 |            |
                 v            v
              [Support]    [Skills]
              [Cards]      [Hints]
                 |            |
                 |            |
                 v            v
              [Friendship] [Evolution]
              [Training]   [Chains]
                 |            |
                 |            |
                 v            v
              [Race]       [Items]
              [Results]    [Inventory]
                 |            |
                 |            |
                 v            v
              [PvP]        [Events]
              [Teams]      [Choices]
                 |            |
                 |            |
                 v            v
              [Meta]       [AI]
              [Data]       [Conversations]
```

## Core Entity Relationship Diagram

### Text Description

The core entities and their relationships:

**User (1) ----< Career (M)**

- One user can have many career runs
- Each career belongs to one user

**Career (1) ----< Character (1)**

- Each career focuses on one character
- Character can be used in multiple careers

**Career (1) ----< Support_Deck (1) ----< Support_Card_Assignment (M) >---- Support_Card (1)**

- Each career has one support deck
- Support deck contains 6 card assignments (5 owned + 1 friend)
- Each assignment references one support card

**Character (1) ----< Character_Stats (M)**

- Character has multiple stat records (historical progression)
- Each stat record belongs to one character

**Career (1) ----< Training_Session (M) >---- Training_Option (1)**

- Career contains many training sessions
- Each session uses one training option

**Character (1) ----< Skill_Acquisition (M) >---- Skill (1)**

- Character can acquire many skills
- Each acquisition references one skill

### Mermaid ERD - Core Entities

```mermaid
erDiagram
    User {
        int user_id PK
        string username
        string email
        json preferences
        timestamp created_at
        timestamp updated_at
    }

    Career {
        int career_id PK
        int user_id FK
        int character_id FK
        string scenario_type
        int current_turn
        string career_stage
        string status
        json goals
        timestamp started_at
        timestamp completed_at
    }

    Character {
        int character_id PK
        string character_name
        string character_type
        json base_stats
        json aptitudes
        json growth_rates
        timestamp created_at
    }

    Character_Stats {
        int stats_id PK
        int career_id FK
        int character_id FK
        int turn_number
        int speed
        int stamina
        int power
        int guts
        int wit
        int energy
        string mood
        json conditions
        timestamp recorded_at
    }

    Support_Deck {
        int deck_id PK
        int career_id FK
        string deck_name
        timestamp created_at
    }

    Support_Card {
        int card_id PK
        string card_name
        string rarity
        string specialization
        json effects
        json skill_provisions
        string tier_ranking
    }

    Support_Card_Assignment {
        int assignment_id PK
        int deck_id FK
        int card_id FK
        int position
        int limit_break_level
        int friendship_level
        boolean is_friend_card
    }

    Training_Session {
        int session_id PK
        int career_id FK
        int turn_number
        int training_option_id FK
        json predicted_gains
        json actual_gains
        json participants
        boolean success
        timestamp session_date
    }

    Training_Option {
        int option_id PK
        string option_name
        string stat_focus
        json base_gains
        json requirements
        json modifiers
    }

    Skill {
        int skill_id PK
        string skill_name
        string skill_type
        string rarity
        int base_sp_cost
        json effects
        int evolution_target_id FK
        json prerequisites
    }

    Skill_Acquisition {
        int acquisition_id PK
        int career_id FK
        int character_id FK
        int skill_id FK
        int turn_acquired
        int sp_cost_paid
        int hints_used
        json hint_sources
        timestamp acquired_at
    }

    %% Relationships
    User ||--o{ Career : "has many"
    Career ||--|| Character : "trains"
    Career ||--|| Support_Deck : "uses"
    Career ||--o{ Character_Stats : "tracks"
    Career ||--o{ Training_Session : "contains"
    Career ||--o{ Skill_Acquisition : "includes"

    Support_Deck ||--o{ Support_Card_Assignment : "contains"
    Support_Card ||--o{ Support_Card_Assignment : "assigned to"

    Training_Session }o--|| Training_Option : "uses"

    Character ||--o{ Character_Stats : "has stats"
    Character ||--o{ Skill_Acquisition : "acquires skills"

    Skill ||--o{ Skill_Acquisition : "acquired as"
    Skill ||--o{ Skill : "evolves to"
```

## Extended Entity Relationships

### Race and Performance System

```mermaid
erDiagram
    Race {
        int race_id PK
        string race_name
        string grade
        string distance
        string surface
        string track
        json weather_conditions
        json stat_requirements
        timestamp race_date
    }

    Race_Entry {
        int entry_id PK
        int career_id FK
        int race_id FK
        string strategy
        string running_style
        json pre_race_stats
        json skills_equipped
        timestamp entered_at
    }

    Race_Result {
        int result_id PK
        int entry_id FK
        int final_position
        int margin
        int fans_gained
        int sp_gained
        json performance_metrics
        timestamp completed_at
    }

    Race_Calendar {
        int calendar_id PK
        int race_id FK
        string year_type
        string month
        string period
        boolean is_goal_race
        json eligibility_requirements
    }

    Career ||--o{ Race_Entry : "enters"
    Race ||--o{ Race_Entry : "has entries"
    Race ||--|| Race_Calendar : "scheduled in"
    Race_Entry ||--|| Race_Result : "produces"
```

### Inheritance and Legacy System

```mermaid
erDiagram
    Legacy_Character {
        int legacy_id PK
        int source_career_id FK
        int character_id FK
        string final_grade
        json final_stats
        json available_factors
        json available_skills
        timestamp created_at
    }

    Factor {
        int factor_id PK
        string factor_type
        string factor_name
        int star_level
        json bonus_values
        string source_type
    }

    Legacy_Factor {
        int legacy_factor_id PK
        int legacy_id FK
        int factor_id FK
        int quantity
        timestamp acquired_at
    }

    Inheritance_Plan {
        int plan_id PK
        int career_id FK
        int parent1_legacy_id FK
        int parent2_legacy_id FK
        json grandparent_legacies
        json target_factors
        json affinity_scores
    }

    Career ||--|| Legacy_Character : "creates"
    Legacy_Character ||--o{ Legacy_Factor : "provides"
    Factor ||--o{ Legacy_Factor : "included in"
    Career ||--|| Inheritance_Plan : "uses"
    Legacy_Character ||--o{ Inheritance_Plan : "parent in"
```

### PvP and Champions Meeting System

```mermaid
erDiagram
    Champions_Team {
        int team_id PK
        int user_id FK
        string team_name
        string cup_type
        json team_composition
        timestamp created_at
    }

    Team_Member {
        int member_id PK
        int team_id FK
        int character_id FK
        string role
        int position
        json member_stats
        json equipped_skills
    }

    Tournament_Entry {
        int entry_id PK
        int team_id FK
        string tournament_name
        string league_type
        int entry_cost
        timestamp entered_at
    }

    Tournament_Result {
        int result_id PK
        int entry_id FK
        int final_ranking
        json match_results
        json rewards_earned
        timestamp completed_at
    }

    Meta_Analysis {
        int analysis_id PK
        string cup_type
        json stat_requirements
        json tier_rankings
        json successful_strategies
        timestamp analysis_date
    }

    User ||--o{ Champions_Team : "creates"
    Champions_Team ||--o{ Team_Member : "contains"
    Character ||--o{ Team_Member : "participates as"
    Champions_Team ||--o{ Tournament_Entry : "enters"
    Tournament_Entry ||--|| Tournament_Result : "produces"
```

### Item and Resource Management System

```mermaid
erDiagram
    Item {
        int item_id PK
        string item_name
        string item_type
        string category
        json effects
        int base_cost
        string rarity
    }

    User_Inventory {
        int inventory_id PK
        int user_id FK
        int item_id FK
        int quantity
        timestamp acquired_at
        timestamp expires_at
    }

    Item_Usage {
        int usage_id PK
        int career_id FK
        int item_id FK
        int quantity_used
        int turn_used
        json usage_context
        timestamp used_at
    }

    Gacha_Banner {
        int banner_id PK
        string banner_name
        string banner_type
        json rate_up_items
        json pity_system
        timestamp start_date
        timestamp end_date
    }

    Gacha_Pull {
        int pull_id PK
        int user_id FK
        int banner_id FK
        json items_received
        int cost_paid
        int pity_counter
        timestamp pulled_at
    }

    Resource_Budget {
        int budget_id PK
        int user_id FK
        int carats_current
        int carats_monthly_income
        json spending_plan
        json pull_targets
        timestamp updated_at
    }

    User ||--o{ User_Inventory : "owns"
    Item ||--o{ User_Inventory : "stored as"
    Career ||--o{ Item_Usage : "uses items"
    Item ||--o{ Item_Usage : "used in"
    User ||--o{ Gacha_Pull : "performs"
    Gacha_Banner ||--o{ Gacha_Pull : "pulled from"
    User ||--|| Resource_Budget : "manages"
```

### Event and Seasonal Content System

```mermaid
erDiagram
    Event {
        int event_id PK
        string event_name
        string event_type
        json event_data
        timestamp start_date
        timestamp end_date
        json rewards
    }

    Event_Choice {
        int choice_id PK
        int event_id FK
        string choice_text
        json outcomes
        json requirements
        float success_rate
    }

    Career_Event {
        int career_event_id PK
        int career_id FK
        int event_id FK
        int choice_id FK
        int turn_occurred
        json outcome_received
        timestamp occurred_at
    }

    Seasonal_Campaign {
        int campaign_id PK
        string campaign_name
        string campaign_type
        json rewards_structure
        json participation_requirements
        timestamp start_date
        timestamp end_date
    }

    User_Campaign_Progress {
        int progress_id PK
        int user_id FK
        int campaign_id FK
        json progress_data
        json rewards_claimed
        timestamp last_updated
    }

    Event ||--o{ Event_Choice : "has choices"
    Career ||--o{ Career_Event : "experiences"
    Event ||--o{ Career_Event : "occurs in"
    Event_Choice ||--o{ Career_Event : "selected in"
    User ||--o{ User_Campaign_Progress : "participates in"
    Seasonal_Campaign ||--o{ User_Campaign_Progress : "tracked by"
```

### AI and Analytics System

```mermaid
erDiagram
    AI_Conversation {
        int conversation_id PK
        int user_id FK
        int career_id FK
        json conversation_history
        string ai_model_used
        timestamp started_at
        timestamp last_message_at
    }

    AI_Message {
        int message_id PK
        int conversation_id FK
        string message_type
        text message_content
        json context_data
        string ai_model
        float confidence_score
        timestamp sent_at
    }

    Screenshot_Analysis {
        int analysis_id PK
        int user_id FK
        string image_path
        json extracted_data
        float confidence_score
        json validation_results
        timestamp analyzed_at
    }

    Performance_Metric {
        int metric_id PK
        int career_id FK
        string metric_type
        string metric_name
        float metric_value
        json metric_context
        timestamp recorded_at
    }

    Statistical_Analysis {
        int analysis_id PK
        int user_id FK
        string analysis_type
        json input_parameters
        json results
        json confidence_intervals
        timestamp generated_at
    }

    Community_Data {
        int data_id PK
        string data_type
        string source
        json data_content
        timestamp last_updated
        float reliability_score
    }

    User ||--o{ AI_Conversation : "has conversations"
    Career ||--o{ AI_Conversation : "discussed in"
    AI_Conversation ||--o{ AI_Message : "contains"
    User ||--o{ Screenshot_Analysis : "uploads"
    Career ||--o{ Performance_Metric : "generates"
    User ||--o{ Statistical_Analysis : "requests"
```

## Database Schema Considerations

### Indexing Strategy

```sql
-- Primary performance indexes
CREATE INDEX idx_career_user_id ON Career(user_id);
CREATE INDEX idx_career_character_id ON Career(character_id);
CREATE INDEX idx_training_session_career_turn ON Training_Session(career_id, turn_number);
CREATE INDEX idx_character_stats_career_turn ON Character_Stats(career_id, turn_number);
CREATE INDEX idx_skill_acquisition_career ON Skill_Acquisition(career_id);
CREATE INDEX idx_race_entry_career ON Race_Entry(career_id);

-- Search and filtering indexes
CREATE INDEX idx_support_card_tier ON Support_Card(tier_ranking);
CREATE INDEX idx_skill_type_rarity ON Skill(skill_type, rarity);
CREATE INDEX idx_race_grade_distance ON Race(grade, distance);
CREATE INDEX idx_event_type_date ON Event(event_type, start_date);

-- Analytics indexes
CREATE INDEX idx_performance_metric_type ON Performance_Metric(metric_type, recorded_at);
CREATE INDEX idx_tournament_result_ranking ON Tournament_Result(final_ranking);
CREATE INDEX idx_gacha_pull_banner_date ON Gacha_Pull(banner_id, pulled_at);
```

### Data Integrity Constraints

```sql
-- Referential integrity
ALTER TABLE Career ADD CONSTRAINT fk_career_user
    FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE;

ALTER TABLE Character_Stats ADD CONSTRAINT fk_stats_career
    FOREIGN KEY (career_id) REFERENCES Career(career_id) ON DELETE CASCADE;

-- Business logic constraints
ALTER TABLE Support_Card_Assignment ADD CONSTRAINT chk_position_range
    CHECK (position BETWEEN 1 AND 6);

ALTER TABLE Character_Stats ADD CONSTRAINT chk_stat_ranges
    CHECK (speed BETWEEN 0 AND 1200 AND stamina BETWEEN 0 AND 1200
           AND power BETWEEN 0 AND 1200 AND guts BETWEEN 0 AND 1200
           AND wit BETWEEN 0 AND 1200);

ALTER TABLE Training_Session ADD CONSTRAINT chk_turn_positive
    CHECK (turn_number > 0);

-- Unique constraints
ALTER TABLE Support_Card_Assignment ADD CONSTRAINT uk_deck_position
    UNIQUE (deck_id, position);

ALTER TABLE Legacy_Factor ADD CONSTRAINT uk_legacy_factor
    UNIQUE (legacy_id, factor_id);
```

### Partitioning Strategy

```sql
-- Partition large tables by date for performance
CREATE TABLE Training_Session (
    -- columns...
) PARTITION BY RANGE (session_date) (
    PARTITION p_2024 VALUES LESS THAN ('2025-01-01'),
    PARTITION p_2025 VALUES LESS THAN ('2026-01-01'),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);

-- Partition analytics tables by metric type
CREATE TABLE Performance_Metric (
    -- columns...
) PARTITION BY LIST (metric_type) (
    PARTITION p_training VALUES IN ('training_efficiency', 'stat_gains'),
    PARTITION p_racing VALUES IN ('race_performance', 'win_rate'),
    PARTITION p_general VALUES IN ('career_progress', 'goal_completion')
);
```

## Entity Relationship Summary

### Key Relationships

1. **User-Centric Design**: All data flows from User entity, enabling multi-user support
2. **Career-Focused Structure**: Career is the central entity connecting all gameplay data
3. **Hierarchical Character Data**: Character → Stats → Skills → Training progression
4. **Flexible Support System**: Support cards with dynamic deck composition
5. **Comprehensive Analytics**: Performance tracking across all game aspects
6. **Community Integration**: External data synchronization and sharing capabilities

### Scalability Considerations

- **Horizontal Partitioning**: Large tables partitioned by date or type
- **Indexing Strategy**: Optimized for common query patterns
- **Data Archival**: Historical data management for long-term analytics
- **Caching Layer**: Frequently accessed data cached for performance
- **API Integration**: External data synchronized with conflict resolution

This comprehensive ERD structure supports all 59 requirements while maintaining data integrity, performance, and scalability for the complete Umamusume optimization system.
