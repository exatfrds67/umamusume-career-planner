# Task 1.4 Implementation Prompts

## Core Models and Eloquent Relationships

**Project**: UmamusumeCareerPlanner  
**Task**: 1.4 - Core Models and Eloquent Relationships  
**Date**: January 12, 2026  
**Estimated Time**: 8-10 hours  

---

## Implementation Context

You are implementing Task 1.4 for the UmamusumeCareerPlanner Laravel 12 application. The database schema has been implemented with 18 tables, and you need to create corresponding Eloquent models with proper relationships, business logic, and Laravel 12 features.

### Database Schema Reference

The following tables are implemented and ready for model creation:

- **Core Entities**: `ucp_users`, `ucp_characters`, `ucp_aptitudes`, `ucp_factors`
- **Skill Management**: `ucp_skills`, `ucp_skill_hints`, `ucp_skill_acquisitions`
- **Career Tracking**: `ucp_careers`, `ucp_training_sessions`, `ucp_races`
- **Support System**: `ucp_support_cards`, `ucp_events`, `ucp_external_data`
- **AI & MCP**: `ucp_ai_conversations`, `ucp_mcp_servers`, `ucp_mcp_agents`
- **Utilities**: `ucp_user_preferences`, `ucp_system_logs`

---

## Task 1.4.1: Core Entity Models Implementation

### Prompt for User Model Creation

**Context**: Create the User model with Laravel Sanctum authentication and comprehensive relationship definitions.

**Implementation Requirements**:

```php
// Create User model with these specifications:
// 1. Use Laravel Sanctum's HasApiTokens trait
// 2. Implement proper fillable fields and hidden attributes
// 3. Add relationships to characters, preferences, and AI conversations
// 4. Use Laravel 12's new Attribute syntax for accessors/mutators
// 5. Implement proper password hashing and validation

// Database table: ucp_users
// Key fields: id, name, email, password, email_verified_at, remember_token, created_at, updated_at

// Relationships to implement:
// - hasMany: characters, user_preferences, ai_conversations
// - morphMany: system_logs (as loggable)
```

**Laravel 12 Features to Use**:

- PHP 8 constructor property promotion where applicable
- Explicit return type declarations for all methods
- Laravel Sanctum integration for API authentication
- Proper JSON casting for any complex fields

**Code Quality Requirements**:

- Use descriptive method names following Laravel conventions
- Implement proper PHPDoc blocks with parameter and return types
- Follow existing code conventions by examining sibling files
- Use curly braces for all control structures

### Prompt for Character Model Creation

**Context**: Create the Character model with comprehensive stat management, JSON casting, and scenario-specific methods.

**Implementation Requirements**:

```php
// Create Character model with these specifications:
// 1. Implement stat management for Speed, Stamina, Power, Guts, Wit (0-1200 range)
// 2. Use JSON casting for complex data fields (stats, bonuses, configurations)
// 3. Add scenario-specific methods for URA Finale and Unity Cup
// 4. Implement stat validation and grade calculation methods
// 5. Add relationships to users, aptitudes, factors, careers, skills

// Database table: ucp_characters
// Key fields: id, user_id, name, scenario_type, current_stats (JSON), target_stats (JSON), 
//            energy_level, mood_status, created_at, updated_at

// Relationships to implement:
// - belongsTo: user
// - hasOne: aptitudes
// - hasMany: careers, factors
// - belongsToMany: skills (with pivot data for acquisition details)
```

**Business Logic to Implement**:

- Stat validation methods (0-1200 range)
- Grade calculation from numerical stats (G+ through SS)
- Scenario-specific optimization methods
- Progress tracking toward target stats
- Energy and mood management methods

### Prompt for Aptitude Model Creation

**Context**: Create the Aptitude model with grade validation and aptitude-specific query scopes.

**Implementation Requirements**:

```php
// Create Aptitude model with these specifications:
// 1. Implement grade validation for G through SS ratings
// 2. Add query scopes for filtering by distance, surface, running style
// 3. Create methods for aptitude-based race suitability analysis
// 4. Implement enum casting for aptitude grades
// 5. Add relationship to character (belongsTo)

// Database table: ucp_aptitudes
// Key fields: id, character_id, sprint_aptitude, mile_aptitude, medium_aptitude, 
//            long_aptitude, turf_aptitude, dirt_aptitude, front_runner_aptitude,
//            pace_chaser_aptitude, late_surger_aptitude, end_closer_aptitude

// Aptitude grades: G, G+, F, F+, E, E+, D, D+, C, C+, B, B+, A, A+, S, SS
```

**Query Scopes to Implement**:

- Distance-based scopes (sprint, mile, medium, long)
- Surface-based scopes (turf, dirt)
- Running style scopes (front runner, pace chaser, late surger, end closer)
- Grade-based filtering scopes

### Prompt for Factor Model Creation

**Context**: Create the Factor model with inheritance calculation methods and affinity tracking.

**Implementation Requirements**:

```php
// Create Factor model with these specifications:
// 1. Implement inheritance calculation methods for different factor types
// 2. Add affinity compatibility checking (◎ symbol logic)
// 3. Create factor stacking calculation methods
// 4. Implement enum casting for factor types and rarity levels
// 5. Add relationships to characters and parent characters

// Database table: ucp_factors
// Key fields: id, character_id, parent_character_id, factor_type, factor_rarity,
//            stat_bonuses (JSON), aptitude_bonuses (JSON), skill_bonuses (JSON),
//            affinity_compatibility, created_at, updated_at

// Factor types: Blue (stat), Red (aptitude), Green (unique skill), White (normal skill/race)
// Factor rarity: ★☆☆, ★★☆, ★★★
```

**Business Logic to Implement**:

- Factor bonus calculation methods
- Inheritance success rate calculation
- Affinity compatibility checking
- Factor stacking rules implementation
- Parent-child relationship validation

---

## Task 1.4.2: Skill Management Models Implementation

### Prompt for Skill Model Creation

**Context**: Create the Skill model with SP cost calculation, hint tracking, and evolution relationships.

**Implementation Requirements**:

```php
// Create Skill model with these specifications:
// 1. Implement SP cost calculation with hint-based discounts
// 2. Add skill evolution methods for Normal → Rare upgrades
// 3. Create skill type enums and validation
// 4. Implement skill effect and prerequisite tracking
// 5. Add relationships to characters, skill_hints, skill_acquisitions

// Database table: ucp_skills
// Key fields: id, name, skill_type, skill_category, base_sp_cost, evolution_skill_id,
//            prerequisites (JSON), effects (JSON), meta_tier, created_at, updated_at

// Skill types: Normal (120-180 SP), Rare (180-240 SP), Unique (variable)
// Skill categories: Speed, Passive, Recovery, Debuff
```

**Business Logic to Implement**:

- SP cost calculation with hint discounts (20% per duplicate, 40% max)
- Skill evolution replacement logic
- Prerequisite validation methods
- Meta tier ranking methods
- Skill synergy analysis

### Prompt for SkillHint Model Creation

**Context**: Create the SkillHint model with discount calculation and source tracking.

**Implementation Requirements**:

```php
// Create SkillHint model with these specifications:
// 1. Track hint sources (support cards, events, inheritance)
// 2. Calculate discount percentages (20% per duplicate, 40% max)
// 3. Implement hint acquisition probability methods
// 4. Add relationships to characters, skills, support_cards
// 5. Track red "!" indicator logic for guaranteed hints

// Database table: ucp_skill_hints
// Key fields: id, character_id, skill_id, source_type, source_id, hint_count,
//            discount_percentage, is_guaranteed, acquired_at, created_at, updated_at
```

**Business Logic to Implement**:

- Discount calculation methods
- Hint probability calculation
- Guaranteed hint detection (red "!" logic)
- Source tracking and validation
- Duplicate hint management

### Prompt for SkillAcquisition Model Creation

**Context**: Create the SkillAcquisition model with cost tracking and evolution management.

**Implementation Requirements**:

```php
// Create SkillAcquisition model with these specifications:
// 1. Track skill acquisition costs and discounts applied
// 2. Manage skill evolution replacements
// 3. Record acquisition timing and career context
// 4. Add relationships to characters, skills, careers
// 5. Implement cost efficiency analysis methods

// Database table: ucp_skill_acquisitions
// Key fields: id, character_id, career_id, skill_id, base_cost, discount_applied,
//            final_cost, acquisition_turn, evolution_from_skill_id, created_at, updated_at
```

**Business Logic to Implement**:

- Cost tracking and analysis
- Evolution replacement logic
- Acquisition timing optimization
- Career context integration
- Efficiency metrics calculation

---

## Task 1.4.3: Career Tracking Models Implementation

### Prompt for Career Model Creation

**Context**: Create the Career model with comprehensive analytics methods and scenario support.

**Implementation Requirements**:

```php
// Create Career model with these specifications:
// 1. Support both URA Finale and Unity Cup scenarios
// 2. Implement comprehensive analytics and progress tracking
// 3. Add scenario-specific methods for different mechanics
// 4. Track career outcomes and performance metrics
// 5. Add relationships to characters, training_sessions, races, skill_acquisitions

// Database table: ucp_careers
// Key fields: id, character_id, scenario_type, career_status, start_stats (JSON),
//            final_stats (JSON), final_grade, total_turns, completion_date,
//            unity_cup_data (JSON), created_at, updated_at

// Scenario types: URA_Finale, Unity_Cup
// Career status: Active, Completed, Failed, Abandoned
```

**Business Logic to Implement**:

- Scenario-specific optimization methods
- Progress tracking and analytics
- Performance metrics calculation
- Goal completion tracking
- Career outcome analysis

### Prompt for TrainingSession Model Creation

**Context**: Create the TrainingSession model with stat gain tracking and prediction accuracy.

**Implementation Requirements**:

```php
// Create TrainingSession model with these specifications:
// 1. Track turn-by-turn training data with stat gains
// 2. Record prediction accuracy for machine learning
// 3. Implement Spirit Burst mechanics for Unity Cup
// 4. Track energy consumption and mood changes
// 5. Add relationships to careers, support_cards

// Database table: ucp_training_sessions
// Key fields: id, career_id, turn_number, training_type, predicted_gains (JSON),
//            actual_gains (JSON), energy_before, energy_after, mood_before, mood_after,
//            spirit_burst_triggered, participants (JSON), created_at, updated_at
```

**Business Logic to Implement**:

- Stat gain calculation and tracking
- Prediction accuracy measurement
- Spirit Burst mechanics implementation
- Energy and mood management
- Training effectiveness analysis

### Prompt for Race Model Creation

**Context**: Create the Race model with performance analysis and strategy effectiveness tracking.

**Implementation Requirements**:

```php
// Create Race model with these specifications:
// 1. Track race performance and strategy effectiveness
// 2. Record race conditions and requirements
// 3. Implement performance analysis methods
// 4. Track goal completion and fan acquisition
// 5. Add relationships to careers, characters

// Database table: ucp_races
// Key fields: id, career_id, race_name, race_grade, distance, surface, track_conditions,
//            final_position, strategy_used, stats_at_race (JSON), performance_analysis (JSON),
//            fans_gained, goal_completed, race_date, created_at, updated_at
```

**Business Logic to Implement**:

- Performance analysis methods
- Strategy effectiveness tracking
- Goal completion validation
- Race readiness assessment
- Historical performance comparison

---

## Task 1.4.4: Support System and MCP Integration Models

### Prompt for SupportCard Model Creation

**Context**: Create the SupportCard model with 6-card deck management and friendship tracking.

**Implementation Requirements**:

```php
// Create SupportCard model with these specifications:
// 1. Manage 6-card deck composition (5 owned + 1 friend)
// 2. Track friendship levels and bond progression
// 3. Implement skill provision mapping
// 4. Add meta tier ranking and effectiveness analysis
// 5. Add relationships to characters, skills, training_sessions

// Database table: ucp_support_cards
// Key fields: id, name, rarity, card_type, limit_break_level, friendship_level,
//            stat_bonuses (JSON), skill_provisions (JSON), meta_tier, effects (JSON),
//            is_friend_card, created_at, updated_at
```

**Business Logic to Implement**:

- Deck composition validation (6-card limit)
- Friendship level progression
- Skill provision tracking
- Meta tier analysis
- Training bonus calculations

### Prompt for MCP Integration Models Creation

**Context**: Create MCP integration models for AI conversations, server management, and agent orchestration.

**Implementation Requirements**:

```php
// Create AIConversation model with these specifications:
// 1. Track hybrid AI system conversations (Ollama + Bedrock)
// 2. Manage conversation context and continuity
// 3. Record AI model usage and costs
// 4. Implement conversation analytics
// 5. Add relationships to users, characters

// Database table: ucp_ai_conversations
// Key fields: id, user_id, character_id, conversation_context (JSON), model_used,
//            token_usage, cost_estimate, conversation_quality, created_at, updated_at

// Create MCPServer model for server health monitoring
// Create MCPAgent model for subagent lifecycle management
```

**Business Logic to Implement**:

- Conversation context management
- AI model routing logic
- Cost tracking and optimization
- Performance analytics
- Health monitoring methods

---

## Implementation Guidelines

### Laravel 12 Best Practices

1. **Use PHP 8 constructor property promotion** where applicable
2. **Implement explicit return type declarations** for all methods
3. **Use Laravel 12's new Attribute syntax** for accessors/mutators
4. **Implement JSON casting** for complex data fields
5. **Use enum casting** for status fields and categorical data

### Code Quality Standards

1. **Follow existing conventions** by examining sibling files
2. **Use descriptive names** for variables and methods
3. **Implement proper PHPDoc blocks** with array shape definitions
4. **Use curly braces** for all control structures
5. **Maintain consistent naming** across all models

### Relationship Implementation

1. **Define all relationships** with proper return type hints
2. **Use eager loading** to prevent N+1 queries
3. **Implement proper foreign key constraints** in relationships
4. **Add relationship validation** where appropriate
5. **Use polymorphic relationships** for flexible data modeling

### Testing Requirements

1. **Create comprehensive Pest tests** for all models
2. **Test all relationships** and business logic methods
3. **Validate data integrity** and constraint enforcement
4. **Test edge cases** and error conditions
5. **Ensure 80% test coverage** minimum

---

## Success Criteria

### Task 1.4.1 Completion

- [x] User model with Sanctum authentication
- [x] Character model with stat management and JSON casting
- [x] Aptitude model with grade validation and query scopes
- [x] Factor model with inheritance calculations

### Task 1.4.2 Completion

- [x] Skill model with evolution support and SP calculations
- [x] SkillHint model with discount tracking
- [x] SkillAcquisition model with cost analysis

### Task 1.4.3 Completion

- [x] Career model with scenario support and analytics
- [x] TrainingSession model with stat tracking
- [x] Race model with performance analysis

### Task 1.4.4 Completion

- [x] SupportCard model with deck management
- [x] Event model with decision tracking
- [x] MCP integration models (AIConversation, MCPServer, MCPAgent)

### Overall Task 1.4 Completion

- [x] All 18 database tables have corresponding Eloquent models
- [x] Comprehensive relationships defined with eager loading optimization
- [x] JSON fields properly cast and accessible with type safety
- [x] Model scopes enable efficient querying for common use cases
- [x] Laravel 12 strict mode compliance prevents performance issues
- [x] All models tested with comprehensive Pest test suite

---

## Next Steps After Task 1.4

Upon completion of Task 1.4, the project will be ready to proceed to **Phase 2: Authentication & API Foundation** with:

1. **Task 2.1**: Laravel Sanctum Authentication System
2. **Task 2.2**: Frontend Foundation with Tailwind CSS v4
3. **Task 2.3**: Character Management Interface

All implementation prompts and readiness checklists are prepared for seamless continuation into Phase 2.

---

**Implementation Prompts Status**: Complete and Ready  
**Next Review**: Upon Task 1.4 completion  
**Prepared By**: Development Team  
**Date**: January 12, 2026
