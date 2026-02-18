# Requirements Document: Unity Cup Scenario Support

## Introduction

The Unity Cup Scenario is a distinct training mode in Umamusume: Pretty Derby that introduces team-based mechanics, strategic resource management, and cooperative gameplay elements. This feature extends the existing career planning system to support Unity Cup's unique mechanics including team races, Spirit Burst system, Unity Training, facility progression tied to Team Rank, and team member management.

Unlike the URA Finale scenario where facility levels increase through usage, Unity Cup ties facility progression to overall Team Rank, requiring players to strategically develop their entire team to maximize training effectiveness. The Spirit Burst mechanic introduces a resource management layer where players must decide when to activate powerful training boosts that benefit both team members and the trainee.

## Glossary

- **System**: The Umamusume Career Planner application
- **User**: A player using the career planner to optimize Unity Cup runs
- **Trainee**: The primary character being trained in a career run
- **Team_Member**: One of the five supporting characters in Unity Cup mode
- **Team_Race**: A competitive event occurring every 6 months with 5 individual races
- **Spirit_Gauge**: A 0-100% resource meter per team member that enables Spirit Burst
- **Spirit_Burst**: A powerful training boost activated when Spirit Gauge is full
- **Unity_Training**: Special training opportunities marked with white flames
- **Unity_Burst**: Critical training opportunities marked with red exclamation marks
- **Team_Rank**: Overall team performance grade (F/G through S) affecting facility levels
- **Stat_Rank**: Individual performance grade per team member per stat
- **Facility_Level**: Training facility effectiveness tier (1-5) determined by Team Rank
- **Team_Zenith**: The final opponent team that must be defeated to complete Unity Cup
- **Career_Run**: A complete playthrough from start to graduation
- **Turn**: A single time unit in the career progression (78 total turns)

## Requirements

### Requirement 1: Unity Cup Scenario Selection

**User Story:** As a user, I want to select Unity Cup as my scenario type when creating a career run, so that I can access Unity Cup-specific mechanics and tracking.

#### Acceptance Criteria

1. WHEN creating a new career run, THE System SHALL provide Unity Cup as a selectable scenario option alongside URA Finale
2. WHEN Unity Cup is selected, THE System SHALL initialize Unity Cup-specific data structures including team composition, Spirit Gauges, and Team Rank
3. WHEN Unity Cup is selected, THE System SHALL display Unity Cup-specific UI elements and hide URA Finale-specific elements
4. WHEN viewing an existing Unity Cup career run, THE System SHALL display the scenario type badge indicating "Unity Cup"
5. THE System SHALL persist the scenario type selection with the career run data

### Requirement 2: Team Member Management

**User Story:** As a user, I want to select and manage five team members for my Unity Cup run, so that I can build an optimal team composition for team races and training synergies.

#### Acceptance Criteria

1. WHEN creating a Unity Cup career run, THE System SHALL require selection of exactly 5 team members from available characters
2. WHEN selecting team members, THE System SHALL display character stats, aptitudes, and specializations to inform selection
3. WHEN team members are selected, THE System SHALL initialize individual Spirit Gauges (0-100%) for each team member
4. WHEN team members are selected, THE System SHALL initialize Stat Ranks (F/G through S) for each team member for each stat type
5. THE System SHALL validate that the trainee character is not selected as a team member
6. THE System SHALL allow users to view and edit team member selection before finalizing the career run
7. WHEN displaying team members, THE System SHALL show their current Spirit Gauge levels and Stat Ranks

### Requirement 3: Team Race System

**User Story:** As a user, I want to track and plan team races occurring every 6 months, so that I can prepare my team and maximize stat rewards.

#### Acceptance Criteria

1. WHEN a career run reaches Late December or Late June, THE System SHALL trigger a team race event
2. WHEN a team race occurs, THE System SHALL present 5 race slots (Sprint, Mile, Medium, Long, Dirt) requiring team member assignment
3. WHEN assigning team members to races, THE System SHALL validate that each team member is assigned to exactly one race
4. WHEN assigning team members to races, THE System SHALL display aptitude grades and recommended assignments based on distance and surface preferences
5. WHEN team race results are recorded, THE System SHALL apply +50 to all stats for each race won
6. WHEN team race results are recorded, THE System SHALL apply +10 to all stats for each race lost
7. THE System SHALL track team race win/loss records throughout the career run
8. WHEN the final Unity Cup occurs, THE System SHALL require victory against Team Zenith (winning 3 of 5 races) to complete the scenario successfully
9. THE System SHALL validate that team race assignments are complete before allowing turn progression

### Requirement 4: Spirit Gauge Tracking

**User Story:** As a user, I want to track Spirit Gauge levels for each team member, so that I can identify when Spirit Burst is available and plan optimal activation timing.

#### Acceptance Criteria

1. THE System SHALL maintain a Spirit Gauge value (0-100%) for each team member
2. WHEN Spirit Gauge reaches 100%, THE System SHALL visually indicate that Spirit Burst is available for that team member
3. WHEN recording training activities, THE System SHALL allow users to update Spirit Gauge values based on in-game observations
4. WHEN displaying team members, THE System SHALL show Spirit Gauge levels with visual indicators (progress bars, percentage values)
5. THE System SHALL persist Spirit Gauge values with turn-by-turn progression data
6. WHEN Spirit Gauge is at 100%, THE System SHALL highlight the team member as ready for Spirit Burst activation
7. THE System SHALL allow manual Spirit Gauge adjustments to correct tracking errors

### Requirement 5: Spirit Burst Activation and Stat Gains

**User Story:** As a user, I want to activate Spirit Burst on training facilities when team member gauges are full, so that I can maximize stat gains for both team members and my trainee.

#### Acceptance Criteria

1. WHEN a team member's Spirit Gauge is at 100%, THE System SHALL enable Spirit Burst activation option for training
2. WHEN Spirit Burst is activated on a training facility, THE System SHALL apply stat gains of 150+ to participating team members
3. WHEN Spirit Burst is activated on a training facility, THE System SHALL apply stat gains of 15-50 to the trainee
4. WHEN Spirit Burst is activated, THE System SHALL reset the team member's Spirit Gauge to 0%
5. THE System SHALL record Spirit Burst activations in turn-by-turn progression history
6. WHEN displaying training predictions, THE System SHALL indicate potential Spirit Burst opportunities based on team member presence and gauge levels
7. THE System SHALL allow users to mark training turns as Spirit Burst activations and record the resulting stat gains

### Requirement 6: Unity Training System

**User Story:** As a user, I want to identify and prioritize Unity Training opportunities marked with white flames, so that I can boost team stats and Team Rank effectively.

#### Acceptance Criteria

1. WHEN recording training activities, THE System SHALL allow users to mark training as Unity Training (white flame indicator)
2. WHEN Unity Training is performed, THE System SHALL apply boosts to team member stats and Team Rank progression
3. WHEN Unity Training opportunities with red exclamation marks (Unity Burst/Friendship Bonus) are available, THE System SHALL flag them as mandatory high-priority actions
4. THE System SHALL track Unity Training frequency and effectiveness throughout the career run
5. WHEN displaying training recommendations, THE System SHALL prioritize Unity Training opportunities
6. THE System SHALL record Unity Training activations in turn-by-turn progression history
7. WHEN Unity Burst opportunities are identified, THE System SHALL provide visual warnings if the user attempts to skip them

### Requirement 7: Team Rank Progression System

**User Story:** As a user, I want to track overall Team Rank progression from F/G to S, so that I can understand how my team's performance affects facility levels and plan accordingly.

#### Acceptance Criteria

1. THE System SHALL maintain an overall Team Rank value (F, G, E, D, C, B, A, S) for the career run
2. WHEN Team Rank changes, THE System SHALL update facility levels according to the mapping: F/G=Level 1, D/E=Level 2, B/C=Level 3, A=Level 4, S=Level 5
3. WHEN recording Unity Training activities, THE System SHALL allow users to update Team Rank based on in-game progression
4. WHEN displaying facility information, THE System SHALL show current facility levels determined by Team Rank
5. THE System SHALL track Team Rank progression history throughout the career run
6. WHEN Team Rank reaches A (Rank 5) before the final Unity Cup, THE System SHALL highlight the achievement of the +50 stat bonus eligibility
7. THE System SHALL provide visual indicators showing the relationship between Team Rank and facility levels

### Requirement 8: Individual Stat Rank Tracking

**User Story:** As a user, I want to track individual Stat Ranks (Speed, Stamina, Power, Guts, Wit) for each team member, so that I can monitor team development and identify training priorities.

#### Acceptance Criteria

1. THE System SHALL maintain Stat Rank values (F, G, E, D, C, B, A, S) for each team member for each of the five stats
2. WHEN recording training activities, THE System SHALL allow users to update individual Stat Ranks for team members
3. WHEN displaying team member information, THE System SHALL show all five Stat Ranks with visual grade indicators
4. THE System SHALL track Stat Rank progression history for each team member throughout the career run
5. WHEN Stat Ranks improve, THE System SHALL record the turn number and training activity that caused the improvement
6. THE System SHALL provide summary views showing Stat Rank distribution across the team
7. WHEN analyzing team composition, THE System SHALL highlight Stat Rank imbalances or weaknesses

### Requirement 9: Facility Level Display and Progression

**User Story:** As a user, I want to see facility levels determined by Team Rank rather than usage count, so that I understand Unity Cup's unique progression system and plan training accordingly.

#### Acceptance Criteria

1. THE System SHALL display facility levels (1-5) based on current Team Rank, not usage count
2. WHEN Team Rank changes, THE System SHALL immediately update all facility level displays
3. THE System SHALL clearly indicate that facility levels are Team Rank-dependent in Unity Cup mode
4. WHEN comparing scenarios, THE System SHALL distinguish between URA Finale's usage-based and Unity Cup's rank-based facility progression
5. THE System SHALL provide tooltips or help text explaining the Team Rank to facility level mapping
6. WHEN displaying training predictions, THE System SHALL use Team Rank-determined facility levels for stat gain calculations
7. THE System SHALL prevent manual facility level adjustments in Unity Cup mode (as they are derived from Team Rank)

### Requirement 10: Unity Cup Victory Condition Tracking

**User Story:** As a user, I want to track progress toward defeating Team Zenith in the final Unity Cup, so that I can ensure my team is prepared for the victory condition.

#### Acceptance Criteria

1. THE System SHALL identify the final team race as the Unity Cup against Team Zenith
2. WHEN recording final Unity Cup results, THE System SHALL require winning at least 3 of 5 races for scenario completion
3. WHEN the final Unity Cup is won, THE System SHALL mark the career run as successfully completed
4. WHEN the final Unity Cup is lost, THE System SHALL mark the career run as failed and record the outcome
5. THE System SHALL display victory condition status (races won/needed) during the final Unity Cup
6. THE System SHALL track which specific races were won or lost in the final Unity Cup
7. WHEN viewing completed Unity Cup runs, THE System SHALL display the final Unity Cup outcome prominently

### Requirement 11: Turn-by-Turn Unity Cup Data Recording

**User Story:** As a user, I want to record Unity Cup-specific data (Spirit Gauges, Team Rank, Stat Ranks, Unity Training) for each turn, so that I can track progression and analyze training effectiveness.

#### Acceptance Criteria

1. WHEN recording turn data, THE System SHALL capture Spirit Gauge levels for all five team members
2. WHEN recording turn data, THE System SHALL capture current Team Rank
3. WHEN recording turn data, THE System SHALL capture Stat Ranks for all team members
4. WHEN recording turn data, THE System SHALL capture whether Unity Training or Spirit Burst was activated
5. THE System SHALL persist Unity Cup-specific turn data alongside standard stat progression data
6. WHEN viewing turn history, THE System SHALL display Unity Cup-specific data in an organized format
7. THE System SHALL allow editing of Unity Cup-specific turn data to correct tracking errors

### Requirement 12: Unity Cup Analytics and Reporting

**User Story:** As a user, I want to view analytics specific to Unity Cup runs, so that I can identify patterns, optimize strategies, and compare different approaches.

#### Acceptance Criteria

1. THE System SHALL provide summary statistics for Unity Cup runs including team race win rates, Spirit Burst frequency, and Team Rank progression timeline
2. WHEN viewing analytics, THE System SHALL display Team Rank progression graphs showing the path from F/G to S
3. WHEN viewing analytics, THE System SHALL display Spirit Burst activation patterns and their impact on stat gains
4. WHEN viewing analytics, THE System SHALL display Unity Training frequency and correlation with Team Rank improvements
5. THE System SHALL allow comparison of multiple Unity Cup runs to identify successful strategies
6. THE System SHALL highlight key milestones such as reaching Team Rank A before the final Unity Cup
7. WHEN exporting career run data, THE System SHALL include all Unity Cup-specific metrics and progression data

### Requirement 13: Unity Cup Import/Export Support

**User Story:** As a user, I want to import and export Unity Cup career runs with full fidelity, so that I can backup data, share strategies, and migrate between devices.

#### Acceptance Criteria

1. WHEN exporting a Unity Cup career run, THE System SHALL include all Unity Cup-specific data (team composition, Spirit Gauges, Team Rank, Stat Ranks, team race results)
2. WHEN importing a Unity Cup career run, THE System SHALL validate Unity Cup-specific data structures and business rules
3. WHEN importing a Unity Cup career run, THE System SHALL correctly restore team member associations and progression data
4. THE System SHALL support both JSON and CSV export formats for Unity Cup data
5. WHEN detecting import format, THE System SHALL recognize Unity Cup scenario indicators and route to appropriate import handlers
6. THE System SHALL provide clear error messages if Unity Cup-specific data is missing or invalid during import
7. WHEN importing legacy data without Unity Cup support, THE System SHALL gracefully handle missing fields and provide migration guidance

### Requirement 14: Unity Cup UI Components and Visualization

**User Story:** As a user, I want intuitive UI components for Unity Cup-specific features, so that I can efficiently manage team members, track gauges, and record progression.

#### Acceptance Criteria

1. THE System SHALL provide a team member selector component allowing selection of 5 characters with stat and aptitude display
2. THE System SHALL provide Spirit Gauge visualization components showing 0-100% progress bars with visual indicators for full gauges
3. THE System SHALL provide Team Rank display components showing current rank and facility level implications
4. THE System SHALL provide Stat Rank display components showing grade badges for each team member's five stats
5. THE System SHALL provide team race assignment interfaces with drag-and-drop or selection controls for assigning members to race slots
6. THE System SHALL provide Unity Training indicators (white flames, red exclamation marks) in training facility displays
7. THE System SHALL ensure all Unity Cup UI components are accessible (WCAG 2.2 AA compliant) with keyboard navigation and screen reader support
8. THE System SHALL support both light and dark modes for all Unity Cup UI components

### Requirement 15: Unity Cup AI Advisory Integration

**User Story:** As a user, I want AI-powered recommendations for Unity Cup strategy, so that I can optimize team composition, Spirit Burst timing, and training priorities.

#### Acceptance Criteria

1. WHEN requesting AI recommendations, THE System SHALL analyze team composition and suggest optimal team member selections based on aptitudes and synergies
2. WHEN requesting AI recommendations, THE System SHALL identify optimal Spirit Burst activation timing based on training facility availability and team member presence
3. WHEN requesting AI recommendations, THE System SHALL prioritize Unity Training opportunities and explain their impact on Team Rank progression
4. WHEN requesting AI recommendations, THE System SHALL suggest team race assignments based on team member aptitudes and distance preferences
5. WHEN requesting AI recommendations, THE System SHALL predict Team Rank progression timeline and identify if reaching Rank A before final Unity Cup is achievable
6. THE System SHALL provide AI recommendations that account for Unity Cup-specific mechanics and differ from URA Finale strategies
7. WHEN AI recommendations are generated, THE System SHALL explain the reasoning behind Unity Cup-specific suggestions

### Requirement 16: Unity Cup Data Migration from URA Finale

**User Story:** As a user, I want clear separation between URA Finale and Unity Cup data structures, so that existing URA Finale runs are not affected and I can maintain both scenario types.

#### Acceptance Criteria

1. THE System SHALL maintain separate data structures for URA Finale and Unity Cup scenarios
2. WHEN viewing career run lists, THE System SHALL clearly distinguish between URA Finale and Unity Cup runs with scenario badges
3. THE System SHALL prevent accidental conversion of URA Finale runs to Unity Cup format
4. WHEN filtering career runs, THE System SHALL allow filtering by scenario type
5. THE System SHALL ensure URA Finale-specific features (usage-based facility levels) remain functional and unchanged
6. THE System SHALL ensure Unity Cup-specific features (Team Rank-based facility levels) do not affect URA Finale runs
7. WHEN creating new career runs, THE System SHALL default to the user's most recently used scenario type

### Requirement 17: Unity Cup Validation and Business Rules

**User Story:** As a user, I want the system to validate Unity Cup-specific business rules, so that I can ensure data integrity and catch tracking errors early.

#### Acceptance Criteria

1. THE System SHALL validate that exactly 5 team members are selected for Unity Cup runs
2. THE System SHALL validate that Spirit Gauge values are within 0-100% range
3. THE System SHALL validate that Team Rank values are valid grades (F, G, E, D, C, B, A, S)
4. THE System SHALL validate that Stat Rank values are valid grades for each team member
5. THE System SHALL validate that team race assignments include exactly 5 team members with no duplicates
6. THE System SHALL validate that facility levels match Team Rank according to the defined mapping
7. THE System SHALL validate that final Unity Cup results include win/loss status for all 5 races
8. WHEN validation fails, THE System SHALL provide clear error messages indicating the specific rule violation and how to correct it

### Requirement 18: Unity Cup Performance Optimization

**User Story:** As a user, I want Unity Cup features to perform efficiently, so that I can manage complex team data without experiencing slowdowns or delays.

#### Acceptance Criteria

1. WHEN loading a Unity Cup career run, THE System SHALL load team member data and Spirit Gauges within 2 seconds
2. WHEN updating Spirit Gauge values, THE System SHALL reflect changes in the UI within 100ms
3. WHEN calculating Team Rank-based facility levels, THE System SHALL update all facility displays within 200ms
4. THE System SHALL efficiently store Unity Cup-specific data to minimize database size and query complexity
5. WHEN displaying team race assignment interfaces, THE System SHALL render all team members and race slots within 1 second
6. THE System SHALL cache Team Rank to facility level mappings to avoid repeated calculations
7. WHEN exporting Unity Cup data, THE System SHALL complete export operations within 5 seconds for runs up to 78 turns

### Requirement 19: Unity Cup Help and Documentation

**User Story:** As a user, I want comprehensive help documentation for Unity Cup mechanics, so that I can understand the scenario's unique features and optimize my strategies.

#### Acceptance Criteria

1. THE System SHALL provide help documentation explaining Unity Cup scenario mechanics and differences from URA Finale
2. THE System SHALL provide tooltips explaining Spirit Gauge, Spirit Burst, Unity Training, and Team Rank concepts
3. THE System SHALL provide visual guides showing the Team Rank to facility level mapping
4. THE System SHALL provide examples of optimal Spirit Burst timing and Unity Training prioritization
5. THE System SHALL provide guidance on team composition strategies and team race assignment optimization
6. THE System SHALL provide help content accessible via help icons, tooltips, and a dedicated help section
7. WHEN users encounter Unity Cup-specific features for the first time, THE System SHALL offer contextual onboarding tips

### Requirement 20: Unity Cup Accessibility and Internationalization

**User Story:** As a user, I want Unity Cup features to be fully accessible and support internationalization, so that all users can effectively use the scenario regardless of ability or language preference.

#### Acceptance Criteria

1. THE System SHALL ensure all Unity Cup UI components meet WCAG 2.2 AA accessibility standards
2. THE System SHALL provide keyboard navigation for team member selection, Spirit Gauge management, and team race assignment
3. THE System SHALL provide screen reader announcements for Spirit Gauge level changes and Team Rank progression
4. THE System SHALL ensure sufficient color contrast for Spirit Gauge indicators, Team Rank badges, and Stat Rank displays in both light and dark modes
5. THE System SHALL support internationalization for Unity Cup-specific terminology (Spirit Burst, Unity Training, Team Zenith)
6. THE System SHALL provide alt text for all Unity Cup-specific icons and visual indicators
7. THE System SHALL ensure touch targets for mobile interactions meet minimum size requirements (44x44px) for all Unity Cup controls
