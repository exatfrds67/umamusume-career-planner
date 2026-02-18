# Implementation Plan: Unity Cup Scenario Support

## Overview

This implementation plan breaks down the Unity Cup scenario feature into discrete, incremental tasks. The approach follows a layered implementation strategy: database schema → domain models → service layer → UI components → integration → testing. Each task builds on previous work, ensuring continuous validation and no orphaned code.

## Tasks

- [ ] 1. Database Schema and Migrations
  - [ ] 1.1 Create migration for career_runs table extensions
    - Add `scenario_type` VARCHAR(20) column with default 'ura_finale'
    - Add `team_rank` VARCHAR(1) column (nullable)
    - Add index on `scenario_type`
    - _Requirements: 1.2, 1.5_
  
  - [ ] 1.2 Create team_members table migration
    - Create table with career_run_id, character_id, position columns
    - Add foreign keys and indexes
    - Add unique constraint on (career_run_id, position)
    - _Requirements: 2.1, 2.3_
  
  - [ ] 1.3 Create spirit_gauges table migration
    - Create table with team_member_id, turn_number, gauge_value, spirit_burst_activated, stat_gains columns
    - Add foreign keys and indexes
    - Add CHECK constraint for gauge_value range (0-100)
    - _Requirements: 4.1, 4.5_
  
  - [ ] 1.4 Create team_races table migration
    - Create table with career_run_id, turn_number, is_final_cup, is_completed, races_won, races_lost, defeated_team_zenith columns
    - Add foreign keys and indexes
    - Add CHECK constraint for races_won + races_lost <= 5
    - _Requirements: 3.1, 3.7_
  
  - [ ] 1.5 Create team_race_assignments table migration
    - Create table with team_race_id, team_member_id, race_distance_type, result, stat_bonus_applied columns
    - Add foreign keys and indexes
    - Add unique constraint on (team_race_id, race_distance_type)
    - _Requirements: 3.2, 3.3_
  
  - [ ] 1.6 Create stat_ranks table migration
    - Create table with team_member_id, turn_number, speed_rank, stamina_rank, power_rank, guts_rank, wit_rank columns
    - Add foreign keys and indexes
    - _Requirements: 8.1, 8.4_
  
  - [ ] 1.7 Create migration for stat_progress table extensions
    - Add unity_training_activated, unity_burst_activated, spirit_burst_team_member_id columns
    - Add foreign key for spirit_burst_team_member_id
    - _Requirements: 6.2, 5.5_

- [ ] 2. Domain Models and Enums
  - [ ] 2.1 Create ScenarioType enum
    - Define URA_FINALE and UNITY_CUP cases
    - Implement getDisplayName() method
    - Implement getStrategy() method (stub for now)
    - _Requirements: 1.1, 1.4_
  
  - [ ] 2.2 Create TeamRank enum
    - Define F, G, E, D, C, B, A, S cases
    - Implement getFacilityLevel() method with mapping logic
    - Implement getNumericValue() method for comparisons
    - _Requirements: 7.1, 7.2_
  
  - [ ] 2.3 Create RaceDistanceType enum
    - Define SPRINT, MILE, MEDIUM, LONG, DIRT cases
    - Implement getDisplayName() method
    - _Requirements: 3.2_
  
  - [ ] 2.4 Extend CareerRun model
    - Add scenario_type and team_rank to fillable and casts
    - Add teamMembers(), teamRaces() relationships
    - Implement isUnityCup() helper method
    - Implement getFacilityLevel() method using strategy pattern
    - _Requirements: 1.2, 1.5, 7.2_
  
  - [ ] 2.5 Create TeamMember model
    - Define fillable fields and relationships (careerRun, character, spiritGauges, statRanks, raceAssignments)
    - Implement getCurrentSpiritGauge() helper method
    - Implement getCurrentStatRanks() helper method
    - _Requirements: 2.3, 2.4, 4.1_
  
  - [ ] 2.6 Create SpiritGauge model
    - Define fillable fields and casts
    - Add teamMember relationship
    - Implement isFull() and canActivateSpiritBurst() helper methods
    - _Requirements: 4.1, 4.2, 5.1_
  
  - [ ] 2.7 Create TeamRace model
    - Define fillable fields and casts
    - Add careerRun and assignments relationships
    - Implement isVictorious() and getTotalStatBonus() helper methods
    - _Requirements: 3.1, 3.5, 3.6_
  
  - [ ] 2.8 Create TeamRaceAssignment model
    - Define fillable fields and casts
    - Add teamRace and teamMember relationships
    - _Requirements: 3.2, 3.3_
  
  - [ ] 2.9 Create StatRank model
    - Define fillable fields and casts for all five stat ranks
    - Add teamMember relationship
    - Implement getRankForStat() and getAverageRankValue() helper methods
    - _Requirements: 8.1, 8.3_
  
  - [ ] 2.10 Extend StatProgress model
    - Add unity_training_activated, unity_burst_activated, spirit_burst_team_member_id to fillable and casts
    - Add spiritBurstTeamMember relationship
    - _Requirements: 6.2, 5.5_

- [ ] 3. Validation and Error Handling
  - [ ] 3.1 Create UnityCupValidationException class
    - Define constructor with field, rule, guidance parameters
    - Implement toArray() method for error response formatting
    - _Requirements: 17.8_
  
  - [ ] 3.2 Create UnityCupValidator class
    - Implement validateTeamSelection() method
    - Implement validateSpiritGauge() method
    - Implement validateTeamRank() method
    - Implement validateRaceAssignments() method
    - _Requirements: 2.1, 2.5, 4.1, 17.1, 17.2, 17.3, 17.5_
  
  - [ ]* 3.3 Write property test for team selection validation
    - **Property 3: Team Member Count Validation**
    - **Validates: Requirements 2.1, 2.5**
  
  - [ ]* 3.4 Write property test for Spirit Gauge range validation
    - **Property 4: Spirit Gauge Range Invariant**
    - **Validates: Requirements 4.1, 17.2**
  
  - [ ]* 3.5 Write property test for Team Rank validation
    - **Property 12: Team Rank Validity**
    - **Validates: Requirements 7.1, 17.3**
  
  - [ ]* 3.6 Write property test for Stat Rank validation
    - **Property 13: Stat Rank Validity**
    - **Validates: Requirements 8.1, 17.4**

- [ ] 4. Scenario Strategy Pattern
  - [ ] 4.1 Create ScenarioStrategy interface
    - Define getFacilityLevel() method signature
    - Define calculateStatGains() method signature
    - Define getRequiredData() method signature
    - Define validateProgression() method signature
    - _Requirements: 7.2, 9.1_
  
  - [ ] 4.2 Create URAFinaleStrategy class
    - Implement getFacilityLevel() using usage-based logic
    - Implement other interface methods for URA Finale
    - _Requirements: 16.5_
  
  - [ ] 4.3 Create UnityCupStrategy class
    - Implement getFacilityLevel() using Team Rank mapping
    - Implement other interface methods for Unity Cup
    - _Requirements: 7.2, 9.1_
  
  - [ ] 4.4 Update ScenarioType enum to return strategy instances
    - Implement getStrategy() method to return appropriate strategy
    - _Requirements: 7.2, 9.1_
  
  - [ ]* 4.5 Write property test for Team Rank to facility level mapping
    - **Property 11: Team Rank to Facility Level Mapping**
    - **Validates: Requirements 7.2, 7.4, 9.1, 9.2, 17.6**
  
  - [ ]* 4.6 Write property test for scenario data isolation
    - **Property 20: Scenario Data Isolation**
    - **Validates: Requirements 16.1, 16.5, 16.6**

- [ ] 5. Service Layer - Unity Cup Core
  - [ ] 5.1 Create UnityCupService class
    - Implement initializeCareerRun() method
    - Implement getFacilityLevels() method
    - Implement updateTeamRank() method
    - Implement isReadyForFinalCup() method
    - Implement processFinalCup() method
    - Implement getProgressionSummary() method
    - _Requirements: 1.2, 7.2, 7.3, 10.2_
  
  - [ ]* 5.2 Write property test for Unity Cup initialization
    - **Property 1: Unity Cup Initialization Completeness**
    - **Validates: Requirements 1.2, 2.3, 2.4**
  
  - [ ]* 5.3 Write property test for scenario type persistence
    - **Property 2: Scenario Type Persistence**
    - **Validates: Requirements 1.5**

- [ ] 6. Service Layer - Team Management
  - [ ] 6.1 Create TeamManagementService class
    - Implement validateTeamSelection() method
    - Implement createTeamMembers() method
    - Implement getTeamMemberDetails() method
    - Implement updateStatRanks() method
    - Implement getTeamSummary() method
    - Implement recommendRaceAssignments() method
    - _Requirements: 2.1, 2.3, 2.4, 8.2, 3.4_
  
  - [ ]* 6.2 Write unit tests for team management service
    - Test team member creation
    - Test stat rank updates
    - Test race assignment recommendations
    - _Requirements: 2.1, 2.3, 8.2_

- [ ] 7. Service Layer - Spirit Gauge Management
  - [ ] 7.1 Create SpiritGaugeService class
    - Implement initializeGauges() method
    - Implement updateGauge() method
    - Implement getCurrentGauges() method
    - Implement isSpiritBurstAvailable() method
    - Implement activateSpiritBurst() method
    - Implement getSpiritBurstHistory() method
    - _Requirements: 2.3, 4.3, 4.4, 5.1, 5.4_
  
  - [ ]* 7.2 Write property test for Spirit Burst state transition
    - **Property 6: Spirit Burst State Transition**
    - **Validates: Requirements 5.4, 5.5**
  
  - [ ]* 7.3 Write property test for Spirit Burst stat gain ranges
    - **Property 7: Spirit Burst Stat Gain Ranges**
    - **Validates: Requirements 5.2, 5.3**
  
  - [ ]* 7.4 Write property test for Spirit Gauge visualization
    - **Property 5: Spirit Gauge Visualization Completeness**
    - **Validates: Requirements 4.2, 4.4, 4.6**

- [ ] 8. Service Layer - Team Race Management
  - [ ] 8.1 Create TeamRaceService class
    - Implement getScheduledRaces() method
    - Implement createTeamRace() method
    - Implement assignTeamMembers() method
    - Implement validateAssignments() method
    - Implement recordResults() method
    - Implement calculateStatBonuses() method
    - Implement isTeamZenithDefeated() method
    - _Requirements: 3.1, 3.2, 3.3, 3.5, 3.6, 10.2_
  
  - [ ]* 8.2 Write property test for team race scheduling
    - **Property 8: Team Race Scheduling**
    - **Validates: Requirements 3.1, 3.2**
  
  - [ ]* 8.3 Write property test for team race assignment uniqueness
    - **Property 9: Team Race Assignment Uniqueness**
    - **Validates: Requirements 3.3, 17.5**
  
  - [ ]* 8.4 Write property test for team race stat bonus calculation
    - **Property 10: Team Race Stat Bonus Calculation**
    - **Validates: Requirements 3.5, 3.6**
  
  - [ ]* 8.5 Write property test for final Unity Cup victory condition
    - **Property 15: Final Unity Cup Victory Condition**
    - **Validates: Requirements 3.8, 10.2, 10.3, 10.4**

- [ ] 9. Checkpoint - Core Services Complete
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 10. UI Components - Team Member Selection
  - [ ] 10.1 Create TeamMemberSelector Livewire component
    - Implement team member selection logic (exactly 5 required)
    - Implement validation preventing trainee selection
    - Display character stats and aptitudes
    - Wire up to TeamManagementService
    - _Requirements: 2.1, 2.2, 2.5_
  
  - [ ] 10.2 Create team-member-selector.blade.php view
    - Render character cards with stats and aptitudes
    - Implement selection UI (checkboxes or cards)
    - Display validation errors
    - Ensure WCAG 2.2 AA compliance
    - _Requirements: 2.2, 14.1, 20.1_
  
  - [ ] 10.3 Create team-member-selector.js Alpine component
    - Handle client-side selection state
    - Implement visual feedback for selection
    - Handle keyboard navigation
    - _Requirements: 2.2, 20.2_
  
  - [ ]* 10.4 Write browser test for team member selection
    - Test selecting 5 team members
    - Test validation for incorrect counts
    - Test preventing trainee selection
    - _Requirements: 2.1, 2.5_

- [ ] 11. UI Components - Spirit Gauge Display
  - [ ] 11.1 Create SpiritBurstGauge Blade component
    - Render progress bar with 0-100% value
    - Display visual indicator when gauge is full
    - Include ARIA attributes for accessibility
    - Support light and dark modes
    - _Requirements: 4.2, 4.4, 4.6, 14.2_
  
  - [ ] 11.2 Create spirit-burst-gauge.js Alpine component
    - Handle gauge value updates
    - Animate gauge fill transitions
    - Trigger visual effects when reaching 100%
    - _Requirements: 4.4_
  
  - [ ] 11.3 Create spirit-burst-gauge.css styles
    - Style progress bar with gradient
    - Style full gauge indicator
    - Ensure sufficient contrast in both themes
    - _Requirements: 14.2, 20.4_
  
  - [ ]* 11.4 Write accessibility test for Spirit Gauge component
    - Test ARIA attributes
    - Test keyboard navigation
    - Test screen reader announcements
    - Test color contrast
    - _Requirements: 20.1, 20.2, 20.3, 20.4_

- [ ] 12. UI Components - Team Rank Display
  - [ ] 12.1 Create TeamRankBadge Blade component
    - Render Team Rank grade badge
    - Display facility level implications
    - Include tooltip with mapping explanation
    - _Requirements: 7.4, 9.3, 14.3_
  
  - [ ] 12.2 Update facility display components
    - Show facility levels based on Team Rank for Unity Cup
    - Prevent manual level adjustments in Unity Cup mode
    - Display clear indicators of rank-based progression
    - _Requirements: 9.1, 9.2, 9.7_
  
  - [ ]* 12.3 Write property test for facility level manual adjustment prevention
    - **Property 22: Facility Level Manual Adjustment Prevention**
    - **Validates: Requirements 9.7**

- [ ] 13. UI Components - Team Race Assignment
  - [ ] 13.1 Create TeamRaceAssignment Livewire component
    - Implement race slot assignment logic
    - Display 5 race slots (Sprint, Mile, Medium, Long, Dirt)
    - Show team member aptitudes for each distance
    - Validate assignments (no duplicates, all slots filled)
    - Wire up to TeamRaceService
    - _Requirements: 3.2, 3.3, 3.4, 14.5_
  
  - [ ] 13.2 Create team-race-assignment.blade.php view
    - Render race slots with distance types
    - Render team member cards with aptitudes
    - Implement drag-and-drop or selection UI
    - Display validation errors
    - Ensure WCAG 2.2 AA compliance
    - _Requirements: 3.4, 14.5, 20.1_
  
  - [ ] 13.3 Create team-race-assignment.js Alpine component
    - Handle assignment state
    - Implement drag-and-drop with keyboard alternative
    - Provide visual feedback for assignments
    - _Requirements: 14.5, 20.2_
  
  - [ ]* 13.4 Write browser test for team race assignment
    - Test assigning all 5 team members
    - Test validation for duplicates
    - Test validation for incomplete assignments
    - Test keyboard navigation
    - _Requirements: 3.3, 3.9, 20.2_

- [ ] 14. UI Components - Stat Rank Display
  - [ ] 14.1 Create StatRankDisplay Blade component
    - Render all 5 stat ranks with grade badges
    - Display stat rank progression history
    - Include visual indicators for rank improvements
    - _Requirements: 8.3, 14.4_
  
  - [ ]* 14.2 Write property test for stat rank display completeness
    - **Property 14: Stat Rank Display Completeness**
    - **Validates: Requirements 8.3**

- [ ] 15. Career Run Creation Flow
  - [ ] 15.1 Update CareerRunCreate Livewire component
    - Add scenario type selection (URA Finale / Unity Cup)
    - Conditionally show team member selector for Unity Cup
    - Initialize Unity Cup data structures on creation
    - _Requirements: 1.1, 1.2, 2.1_
  
  - [ ] 15.2 Update career run creation views
    - Add scenario type selector UI
    - Integrate TeamMemberSelector component
    - Display scenario-specific help text
    - _Requirements: 1.1, 2.1_
  
  - [ ]* 15.3 Write browser test for Unity Cup career run creation
    - Test scenario selection
    - Test team member selection
    - Test initialization of Unity Cup data
    - Verify scenario badge display
    - _Requirements: 1.1, 1.2, 1.4_

- [ ] 16. Turn Recording Flow
  - [ ] 16.1 Update turn recording components for Unity Cup
    - Add Spirit Gauge update inputs
    - Add Team Rank update input
    - Add Stat Rank update inputs
    - Add Unity Training activation checkbox
    - Add Spirit Burst activation controls
    - _Requirements: 4.3, 7.3, 8.2, 6.1, 5.7_
  
  - [ ] 16.2 Update turn recording service
    - Persist Spirit Gauge values
    - Persist Team Rank changes
    - Persist Stat Rank changes
    - Persist Unity Training and Spirit Burst flags
    - _Requirements: 11.1, 11.2, 11.3, 11.4, 11.5_
  
  - [ ]* 16.3 Write property test for turn data completeness
    - **Property 16: Turn Data Completeness**
    - **Validates: Requirements 11.1, 11.2, 11.3, 11.4**

- [ ] 17. Team Race Flow
  - [ ] 17.1 Create TeamRaceEvent Livewire component
    - Detect team race turns (Late December, Late June)
    - Display team race assignment interface
    - Record race results (wins/losses)
    - Apply stat bonuses
    - _Requirements: 3.1, 3.2, 3.5, 3.6_
  
  - [ ] 17.2 Create final Unity Cup flow
    - Mark final team race as Unity Cup vs Team Zenith
    - Validate victory condition (3+ wins)
    - Mark career run as complete or failed
    - Display final results prominently
    - _Requirements: 10.1, 10.2, 10.3, 10.4_
  
  - [ ]* 17.3 Write browser test for team race flow
    - Test team race creation at correct turns
    - Test race assignment
    - Test result recording
    - Test stat bonus application
    - _Requirements: 3.1, 3.2, 3.5, 3.6_
  
  - [ ]* 17.4 Write browser test for final Unity Cup
    - Test victory condition (3+ wins)
    - Test defeat condition (<3 wins)
    - Test career run completion status
    - _Requirements: 10.2, 10.3, 10.4_

- [ ] 18. Checkpoint - UI and Flows Complete
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 19. Import/Export Extensions
  - [ ] 19.1 Extend export service for Unity Cup
    - Include team composition in export
    - Include Spirit Gauge history in export
    - Include Team Rank progression in export
    - Include Stat Rank progression in export
    - Include team race results in export
    - Support JSON and CSV formats
    - _Requirements: 13.1, 13.4_
  
  - [ ] 19.2 Extend import service for Unity Cup
    - Detect Unity Cup scenario indicators
    - Validate Unity Cup-specific data structures
    - Restore team member associations
    - Restore Spirit Gauge history
    - Restore Team Rank and Stat Rank progression
    - Restore team race results
    - _Requirements: 13.2, 13.3, 13.5_
  
  - [ ] 19.3 Implement import error handling
    - Provide clear error messages for validation failures
    - Handle missing Unity Cup fields gracefully
    - Provide migration guidance for legacy data
    - _Requirements: 13.6, 13.7_
  
  - [ ]* 19.4 Write property test for Unity Cup data round-trip integrity
    - **Property 17: Unity Cup Data Round-Trip Integrity**
    - **Validates: Requirements 13.1, 13.3, 4.5, 5.5, 6.6, 7.5, 8.4, 11.5**
  
  - [ ]* 19.5 Write property test for import format detection
    - **Property 18: Import Format Detection**
    - **Validates: Requirements 13.5**
  
  - [ ]* 19.6 Write property test for import validation error clarity
    - **Property 19: Import Validation Error Clarity**
    - **Validates: Requirements 13.6, 17.8**

- [ ] 20. Analytics and Reporting
  - [ ] 20.1 Create Unity Cup analytics components
    - Display team race win rate statistics
    - Display Spirit Burst activation frequency
    - Display Team Rank progression timeline graph
    - Display Unity Training frequency and correlation
    - _Requirements: 12.1, 12.2, 12.3, 12.4_
  
  - [ ] 20.2 Create Unity Cup comparison views
    - Allow comparison of multiple Unity Cup runs
    - Highlight successful strategies
    - Display key milestones (Team Rank A achievement)
    - _Requirements: 12.5, 12.6_
  
  - [ ]* 20.3 Write unit tests for analytics calculations
    - Test win rate calculations
    - Test Spirit Burst frequency calculations
    - Test Team Rank progression timeline
    - _Requirements: 12.1, 12.2, 12.3_

- [ ] 21. AI Advisory Integration
  - [ ] 21.1 Extend AI advisory service for Unity Cup
    - Implement team composition recommendations
    - Implement Spirit Burst timing recommendations
    - Implement Unity Training prioritization
    - Implement team race assignment recommendations
    - Implement Team Rank progression predictions
    - _Requirements: 15.1, 15.2, 15.3, 15.4, 15.5_
  
  - [ ] 21.2 Update AI advisory prompts
    - Add Unity Cup-specific context to prompts
    - Differentiate Unity Cup strategies from URA Finale
    - Include reasoning explanations in responses
    - _Requirements: 15.6, 15.7_
  
  - [ ]* 21.3 Write unit tests for AI advisory recommendations
    - Test team composition recommendations
    - Test Spirit Burst timing recommendations
    - Test Unity Training prioritization
    - _Requirements: 15.1, 15.2, 15.3_

- [ ] 22. Training Predictions Integration
  - [ ] 22.1 Update training prediction service
    - Use Team Rank-based facility levels for Unity Cup
    - Include Spirit Burst opportunity indicators
    - Prioritize Unity Training opportunities
    - _Requirements: 9.6, 5.6, 6.5_
  
  - [ ]* 22.2 Write property test for training prediction facility level consistency
    - **Property 23: Training Prediction Facility Level Consistency**
    - **Validates: Requirements 9.6**
  
  - [ ]* 22.3 Write property test for Spirit Burst opportunity indication
    - **Property 30: Spirit Burst Opportunity Indication**
    - **Validates: Requirements 5.6**
  
  - [ ]* 22.4 Write property test for Unity Training prioritization
    - **Property 31: Unity Training Prioritization**
    - **Validates: Requirements 6.5**

- [ ] 23. Career Run List and Filtering
  - [ ] 23.1 Update career run list components
    - Display scenario type badges
    - Add scenario type filter
    - Distinguish Unity Cup and URA Finale runs visually
    - _Requirements: 16.2, 16.4_
  
  - [ ]* 23.2 Write property test for scenario type filtering
    - **Property 21: Scenario Type Filtering**
    - **Validates: Requirements 16.2**
  
  - [ ]* 23.3 Write property test for default scenario selection
    - **Property 29: Default Scenario Selection**
    - **Validates: Requirements 16.7**

- [ ] 24. Scenario Conversion Prevention
  - [ ] 24.1 Implement scenario conversion validation
    - Prevent URA Finale to Unity Cup conversion
    - Provide clear error messages
    - _Requirements: 16.3_
  
  - [ ]* 24.2 Write property test for scenario conversion prevention
    - **Property 27: Scenario Conversion Prevention**
    - **Validates: Requirements 16.3**

- [ ] 25. Help and Documentation
  - [ ] 25.1 Create Unity Cup help documentation
    - Explain Unity Cup mechanics and differences from URA Finale
    - Document Spirit Gauge, Spirit Burst, Unity Training concepts
    - Provide visual guides for Team Rank to facility level mapping
    - Include examples of optimal strategies
    - _Requirements: 19.1, 19.2, 19.3, 19.4, 19.5_
  
  - [ ] 25.2 Add contextual help and tooltips
    - Add tooltips to Spirit Gauge components
    - Add tooltips to Team Rank displays
    - Add tooltips to Unity Training indicators
    - Add help icons throughout Unity Cup UI
    - _Requirements: 19.2, 19.6_
  
  - [ ] 25.3 Implement onboarding tips
    - Show contextual tips for first-time Unity Cup users
    - Provide dismissible tips for key features
    - _Requirements: 19.7_

- [ ] 26. Accessibility Validation
  - [ ] 26.1 Run automated accessibility tests
    - Test all Unity Cup components with axe-core or similar
    - Verify WCAG 2.2 AA compliance
    - Fix any identified issues
    - _Requirements: 20.1_
  
  - [ ] 26.2 Test keyboard navigation
    - Verify all Unity Cup interactions are keyboard accessible
    - Test tab order and focus management
    - Test keyboard alternatives for drag-and-drop
    - _Requirements: 20.2_
  
  - [ ] 26.3 Test screen reader support
    - Verify screen reader announcements for state changes
    - Test with NVDA or JAWS
    - Fix any identified issues
    - _Requirements: 20.3_
  
  - [ ] 26.4 Verify color contrast
    - Test all Unity Cup components in light and dark modes
    - Ensure sufficient contrast ratios (4.5:1 for text, 3:1 for UI components)
    - _Requirements: 20.4_
  
  - [ ] 26.5 Verify touch targets
    - Measure all interactive elements
    - Ensure minimum 44x44px touch targets
    - _Requirements: 20.7_
  
  - [ ]* 26.6 Write property test for alt text completeness
    - **Property 26: Alt Text Completeness**
    - **Validates: Requirements 20.6**

- [ ] 27. Internationalization
  - [ ] 27.1 Externalize Unity Cup terminology
    - Add translation keys for Spirit Burst, Unity Training, Team Zenith, etc.
    - Support Japanese character and skill names
    - _Requirements: 20.5_
  
  - [ ] 27.2 Test locale-aware formatting
    - Test percentage and number formatting
    - Test date formatting for team race schedules
    - _Requirements: 20.5_

- [ ] 28. Final Integration Testing
  - [ ]* 28.1 Run complete Unity Cup workflow browser test
    - Create Unity Cup career run
    - Select team members
    - Record multiple turns with Spirit Gauge updates
    - Trigger and complete team races
    - Activate Spirit Burst
    - Complete final Unity Cup
    - Export and import career run
    - _Requirements: All_
  
  - [ ]* 28.2 Run regression tests for URA Finale
    - Verify URA Finale functionality unchanged
    - Verify usage-based facility progression still works
    - Verify no Unity Cup data leaks into URA Finale runs
    - _Requirements: 16.5, 16.6_

- [ ] 29. Final Checkpoint - Complete Feature
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties
- Unit tests validate specific examples and edge cases
- Browser tests validate end-to-end user workflows
- Accessibility tests ensure WCAG 2.2 AA compliance
