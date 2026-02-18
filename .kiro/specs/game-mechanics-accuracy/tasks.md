# Implementation Plan: Game Mechanics Accuracy Corrections

## Overview

This implementation plan breaks down the game mechanics accuracy corrections into discrete, manageable coding tasks. The plan follows a phased approach: core services first, then database updates, migration service, UI updates, and finally testing and deployment. Each task builds on previous work and includes specific requirements references for traceability.

## Tasks

- [ ] 1. Create PHP Enums for Corrected Mechanics
  - Create `HintLevel` enum with cases NONE through LEVEL_5
  - Implement `getReductionPercentage()` method returning correct percentages (0%, 10%, 20%, 30%, 35%, 40%)
  - Implement `getLabel()` method for UI display
  - Update `AptitudeGrade` enum to remove SS case
  - Implement `getDistanceBonus()`, `getSurfaceBonus()`, `getStyleBonus()` methods with correct percentages
  - Create `TrackCondition` enum with cases FIRM, GOOD, SOFT, HEAVY
  - Implement `getPowerPenalty()`, `getSpeedPenalty()`, `getStaminaDrainModifier()` methods
  - _Requirements: 1.1-1.5, 2.1-2.7, 4.2-4.6_

- [ ]* 1.1 Write property tests for HintLevel enum
  - **Property 1: Hint Level Reduction Accuracy**
  - **Validates: Requirements 1.1, 1.2, 1.3, 1.4, 1.5**

- [ ]* 1.2 Write property tests for AptitudeGrade enum
  - **Property 6: S-Rank Aptitude Bonuses**
  - **Property 7: A-Rank Baseline**
  - **Property 8: Below-A-Rank Penalties**
  - **Validates: Requirements 2.3, 2.4, 2.5, 2.6, 2.7**

- [ ]* 1.3 Write property tests for TrackCondition enum
  - **Property 14: Track Condition Penalties**
  - **Validates: Requirements 4.2, 4.3, 4.4, 4.5, 4.6**

- [ ] 2. Implement SkillHintCalculationService
  - [ ] 2.1 Create service class with interface
    - Implement `calculateReduction(int $hintLevel): float` method
    - Implement `calculateFinalCost(int $baseCost, int $hintLevel): int` method
    - Implement `getHintLevelProgression(): array` method
    - Add validation for hint level range (0-5)
    - _Requirements: 1.1-1.6_

  - [ ]* 2.2 Write property tests for hint level calculations
    - **Property 1: Hint Level Reduction Accuracy**
    - **Property 2: Hint Level Range Validation**
    - **Validates: Requirements 1.1-1.6**

  - [ ]* 2.3 Write unit tests for edge cases
    - Test hint level 0 (no reduction)
    - Test hint level 5 (maximum reduction)
    - Test invalid hint levels (-1, 6, 100)
    - Test base cost edge cases (0, 1, 9999)
    - _Requirements: 1.1-1.6_

- [ ] 3. Implement AptitudeGradeBonusService
  - [ ] 3.1 Create service class with interface
    - Implement `isValidGrade(string $grade): bool` method
    - Implement `calculateBonus(string $grade, string $aptitudeType): float` method
    - Implement `getAffectedStat(string $aptitudeType): string` method
    - Add validation rejecting SS grade
    - _Requirements: 2.1-2.10_

  - [ ]* 3.2 Write property tests for aptitude bonuses
    - **Property 5: Aptitude Grade Validation**
    - **Property 6: S-Rank Aptitude Bonuses**
    - **Property 7: A-Rank Baseline**
    - **Property 8: Below-A-Rank Penalties**
    - **Validates: Requirements 2.1-2.8**

  - [ ]* 3.3 Write unit tests for aptitude calculations
    - Test each grade (G through S) for each aptitude type
    - Test SS grade rejection
    - Test affected stat mapping (distance→Speed, surface→Power, style→Wit)
    - _Requirements: 2.1-2.10_

- [ ] 4. Implement StatEffectivenessService
  - [ ] 4.1 Create service class with interface
    - Implement `calculateEffectiveValue(int $rawValue): float` method
    - Implement `isAtSoftCap(int $value): bool` method
    - Implement `getBreakpointInfo(int $value): array` method
    - Add soft cap logic (100% effectiveness 0-1200, 50% above 1200)
    - _Requirements: 3.1-3.10_

  - [ ]* 4.2 Write property tests for stat effectiveness
    - **Property 10: Stat Range Validation**
    - **Property 11: Stat Effectiveness Calculation**
    - **Property 13: Stat Migration Preservation**
    - **Validates: Requirements 3.1, 3.2, 3.3, 3.9, 3.10**

  - [ ]* 4.3 Write unit tests for stat breakpoints
    - Test values at 0, 900, 901, 1199, 1200, 1201, 1600
    - Test soft cap indicator at 1200
    - Test Stamina Contest unlock at 1200+
    - _Requirements: 3.1-3.10_

- [ ] 5. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 6. Implement TrackConditionService
  - [ ] 6.1 Create service class with interface
    - Implement `calculatePenalties(string $condition, string $surface): array` method
    - Implement `getConditionProbabilities(string $weather): array` method
    - Add logic for doubled Power penalties on Dirt surfaces
    - Add track condition validation
    - _Requirements: 4.1-4.10_

  - [ ]* 6.2 Write property tests for track conditions
    - **Property 14: Track Condition Penalties**
    - **Property 16: Track Condition Migration**
    - **Validates: Requirements 4.2-4.6, 4.10**

  - [ ]* 6.3 Write unit tests for track condition scenarios
    - Test each condition (Firm, Good, Soft, Heavy) on Turf
    - Test each wet condition (Good, Soft, Heavy) on Dirt with doubled penalties
    - Test weather to condition probability mapping
    - _Requirements: 4.1-4.10_

- [ ] 7. Update TrainingFormulaService
  - [ ] 7.1 Implement complete 7-component formula
    - Update `calculateStatGain(array $params): int` with full formula
    - Implement `getFormulaBreakdown(array $params): array` for transparency
    - Implement `calculateFriendshipBonus(int $bondLevel): float` method
    - Add multi-training bonus calculation (+5% per card, max +30%)
    - Validate all components are present and in correct order
    - _Requirements: 5.1-5.10_

  - [ ]* 7.2 Write property tests for training formula
    - **Property 17: Training Formula Completeness**
    - **Property 18: Friendship Training Activation**
    - **Property 19: Multi-Training Bonus**
    - **Validates: Requirements 5.1, 5.2, 5.3, 5.4, 5.10**

  - [ ]* 7.3 Write unit tests for formula components
    - Test each multiplier component individually
    - Test friendship training threshold at bond level 80
    - Test support card bonus at 0, 1, 3, 6 cards
    - Test complete formula with all components
    - _Requirements: 5.1-5.10_

- [ ] 8. Implement StaminaRequirementService
  - [ ] 8.1 Create service class
    - Implement method to calculate stamina range for distance and skill count
    - Add distance-specific ranges (Sprint/Mile/Medium/Long)
    - Add gold skill adjustment logic (0 skills vs 2 skills)
    - Implement dynamic recommendation updates
    - _Requirements: 6.1-6.11_

  - [ ]* 8.2 Write property tests for stamina requirements
    - **Property 21: Distance-Specific Stamina Ranges**
    - **Property 22: Stamina Recommendation Updates**
    - **Validates: Requirements 6.1-6.10**

  - [ ]* 8.3 Write unit tests for stamina scenarios
    - Test each distance with 0 gold skills
    - Test each distance with 2 gold skills
    - Test recommendation updates when skills change
    - _Requirements: 6.1-6.11_

- [ ] 9. Checkpoint - Ensure all service tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 10. Create Database Migrations
  - [ ] 10.1 Create migration for skills table updates
    - Add `check_hint_level` constraint (0-5)
    - Add `base_sp_cost` column
    - Add `effective_sp_cost` generated column with hint level formula
    - _Requirements: 1.1-1.9_

  - [ ] 10.2 Create migration for characters table updates
    - Update aptitude grade constraints (remove SS, keep G-S)
    - Add `*_effective` generated columns for all stats
    - Add soft cap calculation logic (1200 threshold, 50% above)
    - _Requirements: 2.1-2.10, 3.1-3.10_

  - [ ] 10.3 Create migration for races table updates
    - Add `track_condition` enum column (Firm/Good/Soft/Heavy)
    - Keep `weather` column for historical data
    - Add data migration logic to convert weather to track conditions
    - _Requirements: 4.1-4.10_

  - [ ] 10.4 Create migration for training_predictions table updates
    - Add columns for all 7 formula components
    - Add `final_multiplier` generated column
    - _Requirements: 5.1-5.10_

  - [ ] 10.5 Create mechanics_migrations tracking table
    - Add columns for migration tracking (name, counts, status, timestamps)
    - Add `rollback_data` JSON column for backup
    - Add indexes for efficient querying
    - _Requirements: 7.1-7.11_

  - [ ]* 10.6 Write migration tests
    - Test schema changes apply correctly
    - Test constraints work as expected
    - Test generated columns calculate correctly
    - Test rollback scripts work
    - _Requirements: 1.1-7.11_

- [ ] 11. Implement MechanicsMigrationService
  - [ ] 11.1 Create migration service class
    - Implement record identification logic
    - Implement backup creation before migration
    - Implement skill cost recalculation using SkillHintCalculationService
    - Implement aptitude grade conversion (SS → S)
    - Implement stat effective value calculation using StatEffectivenessService
    - Implement track condition conversion using TrackConditionService
    - Implement training prediction recalculation using TrainingFormulaService
    - _Requirements: 7.1-7.11_

  - [ ] 11.2 Add migration reporting and error handling
    - Generate detailed migration report (processed, updated, failed counts)
    - Log errors for failed records without stopping migration
    - Create rollback mechanism using backup data
    - Add migration status tracking in mechanics_migrations table
    - _Requirements: 7.1-7.11_

  - [ ]* 11.3 Write property tests for migration service
    - **Property 4: Skill Cost Migration Correctness**
    - **Property 9: Aptitude Grade Migration**
    - **Property 13: Stat Migration Preservation**
    - **Property 16: Track Condition Migration**
    - **Property 20: Training Formula Migration**
    - **Property 23: Migration Completeness**
    - **Property 24: Migration Data Preservation**
    - **Validates: Requirements 1.8, 2.9, 3.9, 4.10, 5.9, 7.1-7.11**

  - [ ]* 11.4 Write integration tests for migration flows
    - Test complete migration of sample dataset
    - Test rollback after migration
    - Test migration with partial failures
    - Test migration report generation
    - _Requirements: 7.1-7.11_

- [ ] 12. Checkpoint - Ensure migration tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 13. Update Skill Views and Components
  - [ ] 13.1 Update skill display components
    - Show both base cost and effective cost with hint level
    - Add hint level indicator with correct percentage
    - Add tooltip explaining hint level system changes
    - Update skill recommendation logic to use SkillHintCalculationService
    - _Requirements: 1.7, 1.9, 8.1, 8.2_

  - [ ]* 13.2 Write browser tests for skill UI
    - **Property 3: SP Cost Display Completeness**
    - Test hint level display shows correct percentages
    - Test base and effective costs are both visible
    - Test tooltip content is accurate
    - **Validates: Requirements 1.7, 8.1, 8.2**

- [ ] 14. Update Character Views and Components
  - [ ] 14.1 Update aptitude grade components
    - Remove SS from grade selection dropdowns
    - Update grade badge component to show only G-S
    - Add bonus percentage display for each aptitude
    - Add tooltip explaining aptitude system changes
    - _Requirements: 2.8, 8.1, 8.2_

  - [ ] 14.2 Update stat display components
    - Show both raw and effective values for stats > 1200
    - Add soft cap indicator at 1200
    - Add breakpoint highlights at 901, 1200, 1600
    - Add Stamina Contest unlock indicator at 1200+
    - Add tooltip explaining stat effectiveness changes
    - _Requirements: 3.4, 3.5, 3.6, 3.8, 8.1, 8.2_

  - [ ]* 14.3 Write browser tests for character UI
    - **Property 12: Stat Display Completeness**
    - Test aptitude grade dropdowns only show G-S
    - Test stat display shows raw and effective values
    - Test soft cap indicator appears at 1200
    - Test breakpoint highlights are visible
    - **Validates: Requirements 2.8, 3.4, 3.5, 3.6, 3.8, 8.1, 8.2**

- [ ] 15. Update Race Views and Components
  - [ ] 15.1 Update race condition components
    - Add track condition selector (Firm/Good/Soft/Heavy)
    - Keep weather field separate from track condition
    - Display condition-specific penalties (Power, Speed, stamina drain)
    - Add Dirt surface indicator with doubled penalty note
    - Add tooltip explaining track condition system changes
    - _Requirements: 4.7, 4.8, 4.9, 8.1, 8.2_

  - [ ]* 15.2 Write browser tests for race UI
    - **Property 15: Track Condition Display Separation**
    - Test track condition and weather are separate fields
    - Test condition penalties display correctly
    - Test Dirt surface shows doubled penalties
    - **Validates: Requirements 4.7, 8.1, 8.2**

- [ ] 16. Update Training Views and Components
  - [ ] 16.1 Update training prediction components
    - Display complete formula breakdown (all 7 components)
    - Show each multiplier value and name
    - Add friendship training indicator when bond ≥ 80
    - Show multi-training bonus based on card count
    - Add tooltip explaining training formula changes
    - _Requirements: 5.5, 5.6, 8.1, 8.2_

  - [ ] 16.2 Update stamina recommendation components
    - Show distance-specific stamina ranges
    - Display both no-skill and with-skill ranges
    - Update recommendations dynamically when gold skills change
    - Add tooltip explaining stamina requirement changes
    - _Requirements: 6.9, 6.10, 8.1, 8.2_

  - [ ]* 16.3 Write browser tests for training UI
    - Test formula breakdown displays all components
    - Test friendship training indicator at bond 80
    - Test stamina ranges update with skill changes
    - **Validates: Requirements 5.5, 5.6, 6.9, 6.10, 8.1, 8.2**

- [ ] 17. Create "What's Changed" Documentation Page
  - Create new route and view for mechanics changes documentation
  - Document all 5 corrected mechanics with before/after comparisons
  - Add example calculations demonstrating corrections
  - Link to research sources (Game8, UmaReference, GameTora)
  - Add FAQ section addressing common questions
  - _Requirements: 10.1-10.10_

- [ ] 18. Add Migration UI and Notifications
  - [ ] 18.1 Create migration status page
    - Display migration progress (processed, updated, failed counts)
    - Show migration report after completion
    - Provide rollback button if needed
    - Display migrated data badge on affected records
    - _Requirements: 7.7, 7.8, 8.8, 8.9_

  - [ ] 18.2 Add first-login notification
    - Create notification component for mechanics corrections
    - Link to "What's Changed" page
    - Allow users to dismiss after reading
    - _Requirements: 10.6_

  - [ ]* 18.3 Write browser tests for migration UI
    - Test migration progress displays correctly
    - Test migration report shows all data
    - Test rollback button works
    - Test notification appears on first login
    - **Validates: Requirements 7.7, 7.8, 8.8, 8.9, 10.6**

- [ ] 19. Checkpoint - Ensure all UI tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 20. Create Artisan Command for Migration
  - Create `php artisan mechanics:migrate` command
  - Add options for dry-run mode
  - Add options for specific migration types (skills, aptitudes, stats, etc.)
  - Add progress bar and status output
  - Add confirmation prompt before running
  - _Requirements: 7.1-7.11_

- [ ]* 20.1 Write tests for Artisan command
  - Test command executes migration
  - Test dry-run mode doesn't modify data
  - Test specific migration type options
  - Test progress output
  - _Requirements: 7.1-7.11_

- [ ] 21. Performance Testing and Optimization
  - [ ] 21.1 Test calculation performance
    - Benchmark hint level calculations (<1ms)
    - Benchmark aptitude bonus calculations (<1ms)
    - Benchmark stat effectiveness calculations (<1ms)
    - Benchmark track condition calculations (<1ms)
    - Benchmark training formula calculations (<5ms)
    - _Requirements: Performance targets from design_

  - [ ] 21.2 Test migration performance
    - Benchmark skill record migration (1000 records <10s)
    - Benchmark character record migration (1000 records <15s)
    - Benchmark race record migration (1000 records <10s)
    - Benchmark training record migration (1000 records <20s)
    - Benchmark complete migration (10,000 records <2min)
    - _Requirements: Performance targets from design_

  - [ ] 21.3 Optimize slow operations
    - Add database indexes if needed
    - Batch process large migrations
    - Cache frequently accessed calculations
    - _Requirements: Performance targets from design_

- [ ] 22. Integration Testing
  - [ ]* 22.1 Test end-to-end skill flow
    - Create skill → Set hint level → Calculate cost → Verify reduction
    - **Validates: Requirements 1.1-1.9**

  - [ ]* 22.2 Test end-to-end character flow
    - Create character → Set aptitudes → Calculate bonuses → Verify stats
    - Create character → Set stats → Calculate effectiveness → Verify soft cap
    - **Validates: Requirements 2.1-2.10, 3.1-3.10**

  - [ ]* 22.3 Test end-to-end race flow
    - Create race → Set track condition → Calculate penalties → Verify modifiers
    - **Validates: Requirements 4.1-4.10**

  - [ ]* 22.4 Test end-to-end training flow
    - Create training → Set all components → Calculate gain → Verify formula
    - **Validates: Requirements 5.1-5.10**

  - [ ]* 22.5 Test end-to-end migration flow
    - Run migration → Verify all records updated → Check rollback capability
    - **Validates: Requirements 7.1-7.11**

- [ ] 23. Final Checkpoint - Run complete test suite
  - Run all unit tests
  - Run all property tests
  - Run all integration tests
  - Run all browser tests
  - Verify >80% code coverage
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 24. Deployment Preparation
  - Create deployment checklist
  - Prepare rollback plan
  - Create database backup scripts
  - Prepare user communication (email, in-app notification)
  - Update changelog with all corrections
  - Review "What's Changed" documentation
  - _Requirements: 10.1-10.10_

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties
- Unit tests validate specific examples and edge cases
- Integration tests validate end-to-end flows
- Browser tests validate UI behavior and user experience
- Migration is designed to be safe with backup and rollback capabilities
- All corrected mechanics are based on verified research from Game8, UmaReference, and GameTora
