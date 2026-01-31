# Implementation Plan: Skill Seeder Consolidation

## Overview

This implementation plan consolidates three overlapping skill seeders into a single, maintainable architecture. The approach prioritizes data extraction first, then seeder refactoring, followed by cleanup of deprecated files.

## Tasks

- [ ] 1. Create curated skills data file
  - [x] 1.1 Create `database/data/curated_skills.php` with skills array structure
    - Extract all skill definitions from `ComprehensiveSkillSeeder`
    - Extract all skill definitions from `RealUmaMusumeSkillsSeeder`
    - Consolidate into single `skills` array with consistent field structure
    - Add `evolution_pairs` array with all pairs from both seeders
    - _Requirements: 2.1, 2.2, 2.4, 5.4_

  - [ ]* 1.2 Write property test for curated skills validation
    - **Property 1: Curated Skills Have Required Fields**
    - **Validates: Requirements 2.3**

- [ ] 2. Refactor UcpSkillsSeeder as main orchestrator
  - [x] 2.1 Add curated skills loading and seeding methods
    - Implement `loadCuratedSkills()` to load from data file
    - Implement `seedCuratedSkills()` to insert curated skills first
    - Add validation for required fields before insertion
    - _Requirements: 1.1, 1.2, 2.3_

  - [x] 2.2 Implement upsert logic with selective field updates
    - Implement `upsertSkill()` method using `internal_id` as unique key
    - Update only null/empty fields in existing records
    - Track created, updated, skipped counts
    - _Requirements: 4.1, 4.2, 4.4_

  - [ ]* 2.3 Write property test for upsert behavior
    - **Property 4: Selective Field Update During Upsert**
    - **Validates: Requirements 4.2**

  - [x] 2.4 Refactor gametora API integration
    - Keep existing API fetching logic
    - Add merge logic that preserves curated metadata
    - Ensure gametora skills don't overwrite curated fields
    - _Requirements: 3.1, 3.2, 3.4_

  - [ ]* 2.5 Write property test for rarity mapping
    - **Property 2: Gametora Rarity Mapping Consistency**
    - **Validates: Requirements 3.3**

  - [ ]* 2.6 Write property test for curated metadata preservation
    - **Property 3: Curated Metadata Preservation**
    - **Validates: Requirements 3.4**

- [ ] 3. Implement evolution relationship setup
  - [x] 3.1 Consolidate evolution pairs and implement setup method
    - Implement `getEvolutionPairs()` to load from data file
    - Implement `setupEvolutionRelationships()` to update FK fields
    - Add warning logging for missing skills in pairs
    - _Requirements: 5.1, 5.2, 5.3_

  - [ ]* 3.2 Write property test for evolution relationship integrity
    - **Property 5: Evolution Relationship Integrity**
    - **Validates: Requirements 5.1**

- [x] 4. Checkpoint - Verify core seeding functionality
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 5. Implement fresh seeding support
  - [x] 5.1 Add truncate method with database driver handling
    - Implement `truncateTable()` with FK check handling
    - Support both MySQL and SQLite drivers
    - Add `--fresh` option or `fresh()` method
    - _Requirements: 6.1, 6.2, 6.3, 6.4_

  - [x] 5.2 Add progress reporting
    - Display initial skill count before seeding
    - Add progress bar for batch operations
    - Report final counts (created, updated, skipped, errors)
    - _Requirements: 8.1, 8.2, 8.3, 8.4_

- [ ] 6. Update SkillFactory for test compatibility
  - [x] 6.1 Update internal_id generation with test prefix
    - Change internal_id pattern to `test_skill_{unique_number}`
    - Ensure uniqueness across factory-generated skills
    - _Requirements: 7.3, 7.4_

  - [ ]* 6.2 Write property test for factory skill validity
    - **Property 6: Factory Generates Valid Skills**
    - **Validates: Requirements 7.1**

  - [ ]* 6.3 Write property test for factory internal_id uniqueness
    - **Property 7: Factory Internal ID Uniqueness and Prefix**
    - **Validates: Requirements 7.3, 7.4**

- [ ] 7. Update DatabaseSeeder and cleanup
  - [x] 7.1 Update DatabaseSeeder to only call UcpSkillsSeeder
    - Remove any references to ComprehensiveSkillSeeder
    - Remove any references to RealUmaMusumeSkillsSeeder
    - Verify UcpSkillsSeeder is the only skill seeder called
    - _Requirements: 1.4_

  - [x] 7.2 Deprecate or remove legacy seeders
    - Add deprecation notice to ComprehensiveSkillSeeder
    - Add deprecation notice to RealUmaMusumeSkillsSeeder
    - Or delete files if team prefers clean removal
    - _Requirements: 1.3_

- [ ] 8. Integration testing
  - [ ]* 8.1 Write integration test for full seeding workflow
    - Test curated skills are seeded first
    - Test API failure fallback behavior
    - Test fresh seeding mode
    - Test progress reporting output
    - _Requirements: 1.2, 3.2, 4.3, 6.4_

  - [x] 8.2 Verify backward compatibility with existing code
    - Run existing skill-related tests to ensure no regressions
    - Verify SkillController queries work with seeded data
    - Verify evolution relationships work with existing model methods
    - Verify SkillFactory continues to work in all existing tests
    - _Requirements: 9.1, 9.2, 9.4, 9.5_

  - [ ]* 8.3 Write property test for enum value validation
    - **Property 8: Seeded Skills Have Valid Enum Values**
    - **Validates: Requirements 9.3**

- [x] 9. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties
- Unit tests validate specific examples and edge cases
