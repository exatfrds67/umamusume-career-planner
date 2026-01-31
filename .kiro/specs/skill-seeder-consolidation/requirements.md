# Requirements Document

## Introduction

This document defines the requirements for consolidating the skill seeders in the Uma Musume Career Planner application. The project currently has three overlapping skill seeders (`ComprehensiveSkillSeeder`, `RealUmaMusumeSkillsSeeder`, `UcpSkillsSeeder`) with conflicting behaviors, unclear dependencies, and duplicate skill definitions. The consolidation will create a single, maintainable seeder architecture with clear data sources and proper upsert logic.

## Glossary

- **Skill_Seeder**: A Laravel database seeder class responsible for populating the `ucp_skills` table with skill data
- **Curated_Skills**: Hand-crafted skill definitions with complete metadata including evolution pairs, strategic notes, and synergy information
- **Gametora_Skills**: Skills fetched from the gametora.com external API containing 500+ skills from the game
- **Evolution_Pair**: A relationship between a normal skill and its rare evolved version (e.g., "Go with the Flow" → "Lane Legerdemain")
- **Internal_ID**: A unique string identifier for each skill used to match and deduplicate skills across data sources
- **Upsert**: A database operation that inserts a new record or updates an existing one based on a unique key
- **SP_Cost**: Skill Points cost required to acquire a skill in the game

## Requirements

### Requirement 1: Single Orchestrator Seeder

**User Story:** As a developer, I want a single main seeder that orchestrates all skill seeding, so that I have a clear entry point and predictable seeding behavior.

#### Acceptance Criteria

1. THE Skill_Seeder SHALL provide a single `UcpSkillsSeeder` class as the main entry point for all skill seeding operations
2. WHEN `UcpSkillsSeeder` is executed, THE Skill_Seeder SHALL seed curated local skills before fetching external API skills
3. THE Skill_Seeder SHALL remove or deprecate `ComprehensiveSkillSeeder` and `RealUmaMusumeSkillsSeeder` as standalone seeders
4. WHEN `DatabaseSeeder` calls skill seeding, THE Skill_Seeder SHALL only call `UcpSkillsSeeder`

### Requirement 2: Curated Skills Data Source

**User Story:** As a developer, I want curated skills stored in a structured data file, so that skill definitions are maintainable and separate from seeder logic.

#### Acceptance Criteria

1. THE Skill_Seeder SHALL store curated skill definitions in a dedicated data file (`database/data/curated_skills.php` or similar)
2. THE Curated_Skills data file SHALL contain all skills currently defined in `ComprehensiveSkillSeeder` and `RealUmaMusumeSkillsSeeder`
3. WHEN loading curated skills, THE Skill_Seeder SHALL validate that each skill has required fields: `name`, `internal_id`, `skill_type`, `rarity`, `base_sp_cost`
4. THE Curated_Skills data file SHALL define evolution pairs as metadata that can be processed after skill insertion

### Requirement 3: Gametora API Integration

**User Story:** As a developer, I want the seeder to fetch skills from gametora.com API, so that the database contains comprehensive game data beyond curated skills.

#### Acceptance Criteria

1. WHEN fetching from gametora.com, THE Skill_Seeder SHALL use a configurable timeout (default 30 seconds)
2. IF the gametora.com API request fails, THEN THE Skill_Seeder SHALL log the error and continue with curated skills only
3. WHEN processing gametora skills, THE Skill_Seeder SHALL map gametora rarity values to database rarity values (1-2 → normal, 3-4 → rare, 5 → unique)
4. THE Skill_Seeder SHALL NOT overwrite curated skill metadata when merging gametora skills with matching `internal_id`

### Requirement 4: Upsert and Deduplication Logic

**User Story:** As a developer, I want proper upsert logic to prevent duplicate skills, so that the database maintains data integrity regardless of seeder run order.

#### Acceptance Criteria

1. THE Skill_Seeder SHALL use `internal_id` as the unique key for upsert operations
2. WHEN a skill with matching `internal_id` exists, THE Skill_Seeder SHALL update only fields that are null or empty in the existing record
3. THE Skill_Seeder SHALL NOT truncate the `ucp_skills` table during normal seeding operations
4. WHEN seeding is complete, THE Skill_Seeder SHALL report counts of created, updated, and skipped skills

### Requirement 5: Evolution Relationship Setup

**User Story:** As a developer, I want evolution relationships established after all skills are seeded, so that foreign key references are valid.

#### Acceptance Criteria

1. WHEN all skills are inserted, THE Skill_Seeder SHALL setup evolution relationships by updating `evolution_target_id` and `evolution_source_id` fields
2. THE Skill_Seeder SHALL define evolution pairs using `internal_id` references (e.g., `['speed_001', 'speed_001_rare']`)
3. IF an evolution pair references a non-existent skill, THEN THE Skill_Seeder SHALL log a warning and skip that pair
4. THE Skill_Seeder SHALL consolidate all evolution pairs from both `ComprehensiveSkillSeeder` and `RealUmaMusumeSkillsSeeder`

### Requirement 6: Fresh Seeding Support

**User Story:** As a developer, I want the ability to perform a fresh seed that clears existing data, so that I can reset the database to a known state.

#### Acceptance Criteria

1. THE Skill_Seeder SHALL provide a `--fresh` option or separate method for truncating the table before seeding
2. WHEN performing fresh seeding, THE Skill_Seeder SHALL disable foreign key checks before truncation and re-enable after
3. WHEN performing fresh seeding, THE Skill_Seeder SHALL handle both MySQL and SQLite database drivers
4. THE Skill_Seeder SHALL default to upsert behavior (non-destructive) when no fresh option is specified

### Requirement 7: Factory Compatibility

**User Story:** As a developer, I want the SkillFactory to work with the consolidated seeder structure, so that tests can create valid skill instances.

#### Acceptance Criteria

1. THE SkillFactory SHALL generate skills with all required fields matching the database schema
2. THE SkillFactory SHALL provide state methods for creating evolution pairs (`canEvolve()`, `evolved()`)
3. THE SkillFactory SHALL generate unique `internal_id` values to prevent conflicts with seeded data
4. WHEN creating skills in tests, THE SkillFactory SHALL use a distinct `internal_id` prefix (e.g., `test_skill_*`) to avoid collisions

### Requirement 8: Progress Reporting

**User Story:** As a developer, I want clear progress reporting during seeding, so that I can monitor long-running seeding operations.

#### Acceptance Criteria

1. WHEN seeding skills, THE Skill_Seeder SHALL display a progress bar for batch operations
2. THE Skill_Seeder SHALL report the initial skill count before seeding begins
3. THE Skill_Seeder SHALL report final counts: total skills, created, updated, skipped, and errors
4. IF errors occur during seeding, THEN THE Skill_Seeder SHALL log detailed error information without stopping the entire process

### Requirement 9: Backward Compatibility

**User Story:** As a developer, I want the consolidated seeder to maintain backward compatibility with existing application code, so that no breaking changes occur in skill queries or relationships.

#### Acceptance Criteria

1. THE Skill_Seeder SHALL preserve all existing skill fields used by the application: `is_active`, `skill_type`, `rarity`, `meta_tier`, `base_sp_cost`, `name`, `description`
2. THE Skill_Seeder SHALL maintain evolution relationships (`evolution_target_id`, `evolution_source_id`) compatible with existing `evolutionTarget()` and `evolutionSource()` model methods
3. WHEN skills are seeded, THE Skill_Seeder SHALL ensure all skills have valid enum values for `skill_type` (speed, passive, recovery, debuff, unique) and `rarity` (normal, rare, unique)
4. THE Skill_Seeder SHALL NOT change the `ucp_skills` table schema or column names
5. WHEN existing tests use `SkillFactory`, THE Factory SHALL continue to work without modification to test code
