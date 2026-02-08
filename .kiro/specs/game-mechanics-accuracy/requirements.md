# Requirements Document: Game Mechanics Accuracy Corrections

## Introduction

This specification addresses critical inaccuracies in the Umamusume Career Planner's game mechanics implementation. Through comprehensive research from Game8, UmaReference, GameTora, and community sources, several discrepancies between the application's current behavior and actual game mechanics have been identified. These corrections are essential for providing accurate training predictions, skill planning, race strategy, and character optimization.

The corrections span five major systems: skill hint mechanics, aptitude grade bonuses, stat caps and effectiveness, weather/track conditions, and training formulas. Each correction has significant impact on player decision-making and must be implemented with backward compatibility considerations for existing user data.

## Glossary

- **System**: The Umamusume Career Planner application
- **Skill_Hint_System**: The mechanism for reducing SP costs through hint level progression
- **Aptitude_System**: The character rating system for distance, surface, and running style
- **Stat_System**: The character attribute tracking and calculation system (Speed/Stamina/Power/Guts/Wit)
- **Weather_System**: The race condition modeling system for track and weather effects
- **Training_System**: The turn-by-turn stat gain calculation and prediction system
- **Migration_Service**: The data conversion service for updating existing records
- **SP**: Skill Points, the currency for acquiring skills
- **Bond_Level**: The friendship level between character and support card (0-100)
- **Growth_Rate**: The per-stat training effectiveness multiplier
- **Track_Condition**: The race surface state (Firm, Good, Soft, Heavy)
- **Soft_Cap**: The stat threshold where effectiveness begins to diminish (1200)
- **Hard_Cap**: The practical maximum stat value (~1600)

## Requirements

### Requirement 1: Skill Hint System Correction

**User Story:** As a player planning skill acquisitions, I want accurate SP cost calculations based on the correct hint level system, so that I can budget my SP effectively and make informed decisions about which skills to acquire.

#### Acceptance Criteria

1. WHEN a skill has hint level 1, THE Skill_Hint_System SHALL apply a 10% SP cost reduction
2. WHEN a skill has hint level 2, THE Skill_Hint_System SHALL apply a 20% SP cost reduction (10% × 2)
3. WHEN a skill has hint level 3, THE Skill_Hint_System SHALL apply a 30% SP cost reduction (10% × 3)
4. WHEN a skill has hint level 4, THE Skill_Hint_System SHALL apply a 35% SP cost reduction (30% + 5%)
5. WHEN a skill has hint level 5, THE Skill_Hint_System SHALL apply a 40% SP cost reduction (35% + 5%)
6. WHEN calculating SP costs, THE Skill_Hint_System SHALL support hint levels from 0 to 5 inclusive
7. WHEN displaying skill costs in the UI, THE System SHALL show both base cost and reduced cost with hint level
8. WHEN migrating existing data, THE Migration_Service SHALL recalculate all skill acquisition costs using the corrected formula
9. WHEN a user views skill recommendations, THE System SHALL use the corrected hint level formula for SP budget planning

### Requirement 2: Aptitude Grade System Correction

**User Story:** As a player evaluating character builds, I want accurate aptitude grade bonuses that match the actual game mechanics, so that I can correctly assess character performance potential and make informed training decisions.

#### Acceptance Criteria

1. THE Aptitude_System SHALL support grades G, F, E, D, C, B, A, and S only
2. THE Aptitude_System SHALL NOT support SS grade in any aptitude category
3. WHEN a character has S-rank distance aptitude, THE Aptitude_System SHALL apply a +5% Speed bonus
4. WHEN a character has S-rank surface aptitude, THE Aptitude_System SHALL apply a +5% Power bonus
5. WHEN a character has S-rank running style aptitude, THE Aptitude_System SHALL apply a +10% Wit bonus
6. WHEN a character has A-rank aptitude, THE Aptitude_System SHALL apply 0% bonus (baseline)
7. WHEN a character has B-rank or lower aptitude, THE Aptitude_System SHALL apply negative percentage penalties
8. WHEN displaying aptitude grades in the UI, THE System SHALL show only valid grades (G through S)
9. WHEN migrating existing data, THE Migration_Service SHALL convert any SS grades to S grades
10. WHEN calculating race performance, THE System SHALL apply aptitude bonuses to the appropriate stats

### Requirement 3: Stat Range and Effectiveness Correction

**User Story:** As a player optimizing character stats, I want accurate information about stat caps and effectiveness thresholds, so that I can make informed decisions about stat distribution and understand diminishing returns.

#### Acceptance Criteria

1. THE Stat_System SHALL support stat values from 0 to 1600
2. WHEN a stat value is between 0 and 1200, THE Stat_System SHALL apply 100% effectiveness
3. WHEN a stat value exceeds 1200, THE Stat_System SHALL apply 50% effectiveness to the amount above 1200
4. WHEN a stat reaches 1200, THE System SHALL display a visual indicator that the soft cap has been reached
5. WHEN a stat exceeds 1200, THE System SHALL show both the raw value and the effective value
6. WHEN Stamina reaches 1200 or higher, THE System SHALL indicate that Stamina Contest mechanics are unlocked
7. WHEN providing stat recommendations, THE System SHALL consider the 1200 soft cap in optimization calculations
8. WHEN displaying stat breakpoints, THE System SHALL highlight values at 901, 1200, and 1600
9. WHEN migrating existing data, THE Migration_Service SHALL preserve all stat values including those above 1200
10. WHEN calculating training predictions, THE Training_System SHALL account for diminished effectiveness above 1200

### Requirement 4: Weather and Track Condition System Correction

**User Story:** As a player preparing for races, I want accurate weather and track condition effects that match actual game mechanics, so that I can properly assess race readiness and select appropriate skills.

#### Acceptance Criteria

1. THE Weather_System SHALL model weather as affecting track condition probability, not direct performance
2. WHEN track condition is Firm, THE Weather_System SHALL apply 0 Power penalty and 0% stamina drain modifier (baseline)
3. WHEN track condition is Good, THE Weather_System SHALL apply -50 Power penalty and 0% stamina drain modifier
4. WHEN track condition is Soft, THE Weather_System SHALL apply -50 Power penalty and +2% stamina drain modifier
5. WHEN track condition is Heavy, THE Weather_System SHALL apply -50 Power penalty, -50 Speed penalty, and +2% stamina drain modifier
6. WHEN the race surface is Dirt AND track condition is Good, Soft, or Heavy, THE Weather_System SHALL double the Power penalty
7. WHEN displaying race conditions, THE System SHALL show track condition separately from weather
8. WHEN calculating race performance predictions, THE System SHALL apply track condition modifiers to the appropriate stats
9. WHEN recommending skills for races, THE System SHALL consider track condition effects
10. WHEN migrating existing data, THE Migration_Service SHALL convert weather-based penalties to track condition-based penalties

### Requirement 5: Training Formula Accuracy Correction

**User Story:** As a player making training decisions, I want accurate stat gain predictions based on the complete training formula, so that I can optimize my training choices and achieve my target stats efficiently.

#### Acceptance Criteria

1. THE Training_System SHALL calculate stat gains using the formula: Base × (1 + GrowthRate) × (1 + MoodMultiplier) × (1 + TrainingEffect) × (1 + 0.05 × NumSupportCards) × FriendshipMultiplier
2. WHEN Bond_Level is 80 or higher, THE Training_System SHALL activate Friendship Training with a bonus between +10% and +35%
3. WHEN multiple support cards are present in a training facility, THE Training_System SHALL apply +5% bonus per card (maximum +30% at 6 cards)
4. WHEN calculating training predictions, THE Training_System SHALL use all seven formula components
5. WHEN displaying training predictions, THE System SHALL show the breakdown of each multiplier component
6. WHEN a support card reaches Bond_Level 80, THE System SHALL indicate that Friendship Training is now active
7. WHEN comparing predicted vs actual gains, THE System SHALL use the corrected formula for accuracy tracking
8. WHEN providing training recommendations, THE System SHALL rank facilities using the complete formula
9. WHEN migrating existing data, THE Migration_Service SHALL recalculate historical predictions using the corrected formula
10. THE Training_System SHALL validate that all multipliers are applied in the correct multiplicative order

### Requirement 6: Distance-Specific Stamina Requirements Correction

**User Story:** As a player building characters for specific race distances, I want accurate stamina requirements that account for gold skill adjustments, so that I can optimize my stat distribution and skill selection.

#### Acceptance Criteria

1. WHEN planning for Sprint distance races with 0 gold stamina skills, THE System SHALL recommend 290-400 Stamina
2. WHEN planning for Sprint distance races with 2 gold stamina skills, THE System SHALL recommend 200-300 Stamina
3. WHEN planning for Mile distance races with 0 gold stamina skills, THE System SHALL recommend 390-500 Stamina
4. WHEN planning for Mile distance races with 2 gold stamina skills, THE System SHALL recommend 300-400 Stamina
5. WHEN planning for Medium distance races with 0 gold stamina skills, THE System SHALL recommend 540-700 Stamina
6. WHEN planning for Medium distance races with 2 gold stamina skills, THE System SHALL recommend 400-550 Stamina
7. WHEN planning for Long distance races with 0 gold stamina skills, THE System SHALL recommend 790-1000 Stamina
8. WHEN planning for Long distance races with 2 gold stamina skills, THE System SHALL recommend 550-750 Stamina
9. WHEN displaying stamina recommendations, THE System SHALL show both the no-skill and with-skill ranges
10. WHEN a user adds or removes gold stamina skills, THE System SHALL update stamina recommendations dynamically
11. WHEN calculating race readiness, THE System SHALL validate stamina against the appropriate distance-specific range

### Requirement 7: Data Migration and Backward Compatibility

**User Story:** As an existing user with historical data, I want my data to be automatically migrated to use the corrected mechanics, so that my past records remain accurate and I can continue using the application without data loss.

#### Acceptance Criteria

1. WHEN the migration runs, THE Migration_Service SHALL identify all records requiring updates
2. WHEN migrating skill data, THE Migration_Service SHALL recalculate SP costs using the corrected hint level formula
3. WHEN migrating aptitude data, THE Migration_Service SHALL convert SS grades to S grades and recalculate bonuses
4. WHEN migrating stat data, THE Migration_Service SHALL preserve all values and recalculate effective values above 1200
5. WHEN migrating race data, THE Migration_Service SHALL convert weather penalties to track condition penalties
6. WHEN migrating training data, THE Migration_Service SHALL recalculate predictions using the complete formula
7. WHEN migration completes, THE Migration_Service SHALL generate a detailed report of all changes
8. WHEN migration encounters errors, THE Migration_Service SHALL log the error and continue processing remaining records
9. WHEN a user views migrated data, THE System SHALL display a badge indicating the data has been updated
10. THE Migration_Service SHALL create a backup of all data before applying any changes
11. THE Migration_Service SHALL provide a rollback mechanism in case of migration failures

### Requirement 8: UI Updates for Corrected Mechanics

**User Story:** As a player using the application, I want the UI to clearly communicate the corrected mechanics and their effects, so that I can understand how the changes impact my planning and decision-making.

#### Acceptance Criteria

1. WHEN displaying skill hint levels, THE System SHALL show the correct reduction percentage for each level (10%, 20%, 30%, 35%, 40%)
2. WHEN displaying aptitude grades, THE System SHALL show only valid grades (G-S) with correct bonus percentages
3. WHEN displaying stats above 1200, THE System SHALL show both raw and effective values
4. WHEN displaying track conditions, THE System SHALL show condition-specific penalties separately from weather
5. WHEN displaying training predictions, THE System SHALL show the complete formula breakdown
6. WHEN displaying stamina recommendations, THE System SHALL show distance-specific ranges with and without gold skills
7. WHEN a mechanic has been corrected, THE System SHALL display an informational tooltip explaining the change
8. WHEN viewing historical data, THE System SHALL indicate which records have been migrated
9. THE System SHALL provide a "What's Changed" page documenting all mechanics corrections
10. THE System SHALL provide comparison views showing old vs new calculations for educational purposes

### Requirement 9: Testing and Validation

**User Story:** As a developer maintaining the application, I want comprehensive tests validating the corrected mechanics, so that I can ensure accuracy and prevent regressions.

#### Acceptance Criteria

1. THE System SHALL include unit tests for each corrected formula component
2. THE System SHALL include integration tests for end-to-end calculation flows
3. THE System SHALL include property-based tests for stat calculations across the full range (0-1600)
4. THE System SHALL include property-based tests for hint level reductions across all levels (0-5)
5. THE System SHALL include property-based tests for aptitude grade bonuses across all grades (G-S)
6. THE System SHALL include property-based tests for track condition effects across all conditions
7. THE System SHALL include property-based tests for training formula calculations with all multiplier combinations
8. THE System SHALL include migration tests validating data conversion accuracy
9. THE System SHALL include UI tests validating correct display of corrected mechanics
10. THE System SHALL maintain test coverage above 80% for all corrected mechanics code

### Requirement 10: Documentation and User Communication

**User Story:** As a user of the application, I want clear documentation explaining the mechanics corrections and how they affect my existing data, so that I can understand the changes and adjust my planning strategies accordingly.

#### Acceptance Criteria

1. THE System SHALL provide a changelog documenting all mechanics corrections with before/after comparisons
2. THE System SHALL provide a migration guide explaining what data will be updated and how
3. THE System SHALL provide updated help documentation for each corrected mechanic
4. THE System SHALL provide example calculations demonstrating the corrected formulas
5. THE System SHALL provide a FAQ addressing common questions about the corrections
6. WHEN a user first logs in after the update, THE System SHALL display a notification about the mechanics corrections
7. WHEN a user views a corrected mechanic, THE System SHALL provide contextual help explaining the change
8. THE System SHALL provide a comparison tool allowing users to see old vs new calculations
9. THE System SHALL provide links to the research sources (Game8, UmaReference, GameTora) for verification
10. THE System SHALL provide a feedback mechanism for users to report any remaining inaccuracies

---

## Document Information

**Document Type**: Requirements Document  
**Version**: 1.0.0  
**Date**: January 29, 2026  
**Status**: Draft  
**Feature**: game-mechanics-accuracy  
**Related**: SRS, SDS, DBD, Training System, Skill System, Race System
