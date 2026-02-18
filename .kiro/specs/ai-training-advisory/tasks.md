# Tasks: AI-Powered Training Advisory System

## Overview

This task list breaks down the implementation of the AI-Powered Training Advisory System into actionable items organized by implementation phase. Each task includes acceptance criteria and dependencies.

---

## Phase 1: Core Infrastructure (Weeks 1-2)

### 1.1 Create Database Migrations

- [x] 1.1.1 Create `advisory_recommendations` migration
  - Add all columns per design schema
  - Add foreign key to career_runs
  - Add indexes for career_turn and type
  - Test migration up/down

- [x] 1.1.2 Create `critical_alerts` migration
  - Add all columns per design schema
  - Add foreign key to career_runs
  - Add indexes for career_turn and active alerts
  - Test migration up/down

- [x] 1.1.3 Create `prediction_accuracy` migration
  - Add all columns per design schema
  - Add foreign key to career_runs
  - Add indexes for career, type_model, and created
  - Test migration up/down

### 1.2 Create Domain Enums

- [x] 1.2.1 Create `RecommendationType` enum
  - Define all recommendation types from design
  - Add helper methods if needed
  - Write unit tests

- [x] 1.2.2 Create `Priority` enum
  - Define priority levels (CRITICAL, HIGH, MEDIUM, LOW)
  - Add comparison methods
  - Write unit tests

- [x] 1.2.3 Create `AlertType` enum
  - Define all alert types from design
  - Add helper methods
  - Write unit tests

- [x] 1.2.4 Create `CareerPhase` enum (if not exists)
  - Define phases (JUNIOR, CLASSIC, SENIOR, URA_FINALS)
  - Add helper methods
  - Write unit tests

- [x] 1.2.5 Create `RaceDistance` enum (if not exists)
  - Define distances (SPRINT, MILE, MEDIUM, LONG)
  - Add helper methods
  - Write unit tests

- [x] 1.2.6 Create `RunningStyle` enum (if not exists)
  - Define styles (ESCAPE, LEAD, PACE, CHASE)
  - Add helper methods
  - Write unit tests

- [x] 1.2.7 Create `Mood` enum (if not exists)
  - Define moods (VERY_BAD, BAD, NORMAL, GOOD, GREAT)
  - Add effectiveness multipliers
  - Write unit tests

### 1.3 Create Value Objects

- [x] 1.3.1 Create `TrainingContext` value object
  - Implement all readonly properties
  - Add `toPromptContext()` method
  - Add `isEnergyLow()` helper
  - Add `isMoodPoor()` helper
  - Add `hasFriendshipTrainingAvailable()` helper
  - Write unit tests

- [x] 1.3.2 Create `CharacterStats` value object
  - Implement stats properties (speed, stamina, power, guts, wisdom)
  - Add validation
  - Write unit tests

- [x] 1.3.3 Create `SupportCardDeck` value object
  - Implement cards collection
  - Add bond tracking methods
  - Add facility grouping methods
  - Write unit tests

- [x] 1.3.4 Create `Recommendation` value object
  - Implement all readonly properties
  - Add helper methods
  - Write unit tests

- [x] 1.3.5 Create `CriticalAlert` value object
  - Implement all readonly properties
  - Add helper methods
  - Write unit tests

### 1.4 Create Eloquent Models

- [x] 1.4.1 Create `AdvisoryRecommendation` model
  - Define fillable/guarded properties
  - Add relationship to CareerRun
  - Add casts for JSON fields
  - Create factory
  - Write model tests

- [x] 1.4.2 Create `CriticalAlert` model
  - Define fillable/guarded properties
  - Add relationship to CareerRun
  - Add casts for JSON fields
  - Add scopes for active alerts
  - Create factory
  - Write model tests

- [x] 1.4.3 Create `PredictionAccuracy` model
  - Define fillable/guarded properties
  - Add relationship to CareerRun
  - Add casts for JSON fields
  - Create factory
  - Write model tests

### 1.5 Create Service Skeleton

- [x] 1.5.1 Create `TrainingAdvisoryService` class
  - Define public interface methods (stubs)
  - Add constructor with dependencies
  - Add PHPDoc blocks
  - Register in service provider

- [x] 1.5.2 Create `GameMechanicsEngine` class
  - Define public interface methods (stubs)
  - Add constructor
  - Add PHPDoc blocks
  - Register in service provider

---

## Phase 2: Rule-Based Advisor (Week 3)

### 2.1 Implement GameMechanicsEngine Formulas

- [x] 2.1.1 Implement `calculateTrainingGain()`
  - **Validates: Property 11 (Facility Level Progression)**
  - Implement base stat calculation
  - Apply facility level multiplier
  - Apply growth rate
  - Apply mood modifier
  - Apply support card bonuses
  - Apply multi-training bonus
  - Apply friendship training bonus
  - Write property-based tests
  - Write unit tests with known values

- [x] 2.1.2 Implement `calculateStaminaRequirement()`
  - **Validates: Property 7 (Stamina Requirement Calculation)**
  - Implement distance-based thresholds
  - Apply running style modifiers
  - Apply recovery skill reductions
  - Write property-based tests
  - Write unit tests

- [x] 2.1.3 Implement `calculateSkillCost()`
  - **Validates: Property 5 (Skill Hint Level Discount Accuracy)**
  - Implement base cost calculation
  - Apply hint level discounts (10%/20%/30%/35%/40%)
  - Apply Fast Learner bonus if applicable
  - Write property-based tests
  - Write unit tests

- [x] 2.1.4 Implement `calculateFailureRate()`
  - Implement energy-based failure rate
  - Apply support card presence modifiers
  - Apply condition modifiers
  - Write unit tests

- [x] 2.1.5 Implement `calculateStatEffectiveness()`
  - Implement 1200 soft cap logic
  - Values above 1200 count as half
  - Write unit tests

- [x] 2.1.6 Implement `calculateWinProbability()`
  - Implement stat comparison logic
  - Apply race requirements
  - Apply skill bonuses
  - Write unit tests

- [x] 2.1.7 Implement `calculateMultiTrainingBonus()`
  - **Validates: Property 10 (Multi-Training Bonus Calculation)**
  - Implement +5% per card (max +30%)
  - Write property-based tests
  - Write unit tests

- [x] 2.1.8 Implement `calculateFacilityLevel()`
  - **Validates: Property 11 (Facility Level Progression)**
  - Implement every 4 uses = +1 level
  - Cap at Level 5
  - Write property-based tests
  - Write unit tests

### 2.2 Implement RuleBasedAdvisor

- [x] 2.2.1 Create `RuleBasedAdvisor` class
  - Add constructor with GameMechanicsEngine dependency
  - Define public interface methods
  - Register in service provider

- [x] 2.2.2 Implement `recommendTrainingFacility()`
  - **Validates: Property 3 (Friendship Training Priority)**
  - **Validates: Property 4 (Energy-Based Rest Recommendations)**
  - Prioritize facilities with most support cards
  - Check for Friendship Training availability (bond ≥80)
  - Consider energy level (<50 = rest/wisdom)
  - Consider mood
  - Consider facility levels
  - Return Recommendation object
  - Write unit tests

- [x] 2.2.3 Implement `recommendSkillPurchase()`
  - **Validates: Property 6 (Gold Skill Prioritization)**
  - Prioritize gold skills with Level 3+ hints
  - Check SP budget
  - Avoid SP trap skills
  - Return Recommendation object or null
  - Write unit tests

- [x] 2.2.4 Implement `generateRaceStrategy()`
  - Match running style to aptitude
  - Check stamina requirements
  - Calculate win probability
  - Return RaceStrategy object
  - Write unit tests

### 2.3 Offline Fallback Testing

- [x] 2.3.1 Test rule-based recommendations in isolation
  - **Validates: Property 14 (Offline Fallback Behavior)**
  - Test training recommendations
  - Test skill recommendations
  - Test race strategies
  - Write integration tests

---

## Phase 3: AI Integration (Week 4)

### 3.1 Neuron AI Service Integration

- [x] 3.1.1 Create AI prompt templates
  - Create training recommendation prompt
  - Create skill advice prompt
  - Create race strategy prompt
  - Store in config or database

- [x] 3.1.2 Implement AI recommendation parsing
  - Parse AI response to Recommendation objects
  - Handle malformed responses
  - Validate AI output
  - Write unit tests

- [x] 3.1.3 Integrate with NeuronAIService
  - Call Ollama for local inference
  - Implement fallback to AWS Bedrock
  - Handle timeouts and errors
  - Write integration tests

### 3.2 Implement TrainingAdvisoryService AI Methods

- [x] 3.2.1 Implement `getTrainingRecommendations()` with AI
  - **Validates: Property 1 (Storage Mode Consistency)**
  - **Validates: Property 2 (Response Time Bounds)**
  - Build TrainingContext
  - Call AI service
  - Parse response
  - Fall back to RuleBasedAdvisor on failure
  - Track response time
  - Write integration tests
  - Write property-based tests

- [x] 3.2.2 Implement `getSkillPurchaseAdvice()` with AI
  - Build skill context
  - Call AI service
  - Parse response
  - Fall back to RuleBasedAdvisor on failure
  - Write integration tests

- [x] 3.2.3 Implement `getRaceStrategy()` with AI
  - Build race context
  - Call AI service
  - Parse response
  - Fall back to RuleBasedAdvisor on failure
  - Write integration tests

---

## Phase 4: Critical Detection (Week 5)

### 4.1 Implement CriticalSituationDetector

- [x] 4.1.1 Create `CriticalSituationDetector` class
  - Add constructor with GameMechanicsEngine dependency
  - Define detection methods
  - Register in service provider

- [x] 4.1.2 Implement stamina crisis detection
  - **Validates: Property 9 (Critical Alert Generation)**
  - Check stamina vs upcoming race requirements
  - Calculate turns until critical
  - Generate action items
  - Write unit tests

- [x] 4.1.3 Implement SP shortage detection
  - Check SP budget vs planned purchases
  - Generate budget recommendations
  - Write unit tests

- [x] 4.1.4 Implement energy critical detection
  - Check energy level (<40)
  - Generate recovery recommendations
  - Write unit tests

- [x] 4.1.5 Implement bond behind schedule detection
  - **Validates: Property 12 (Bond Progress Tracking)**
  - Check bonds vs turn number
  - Estimate turns to reach 80
  - Write unit tests

- [x] 4.1.6 Implement facility imbalance detection
  - Check facility level variance
  - Recommend diversification
  - Write unit tests

- [x] 4.1.7 Implement mood detection
  - Check for Bad/Very Bad mood
  - Recommend recreation
  - Write unit tests

### 4.2 Integrate Critical Detection

- [x] 4.2.1 Implement `detectCriticalSituations()` in TrainingAdvisoryService
  - **Validates: Property 9 (Critical Alert Generation)**
  - Call all detection methods
  - Prioritize alerts
  - Return CriticalAlertCollection
  - Write integration tests
  - Write property-based tests

- [x] 4.2.2 Persist critical alerts to database
  - Save alerts to critical_alerts table
  - Handle dismissals
  - Write tests

---

## Phase 5: UI Components (Week 6)

### 5.1 Create Advisory Panel Component

- [x] 5.1.1 Create Livewire `AdvisoryPanel` component
  - Add collapsible panel logic
  - Add priority-based organization
  - Add session-based dismissal tracking
  - Write component tests

- [x] 5.1.2 Create Blade view for advisory panel
  - Implement wireframe design
  - Add critical alerts section
  - Add training recommendations section
  - Add skill recommendations section
  - Add race strategy section
  - Ensure WCAG 2.2 AA compliance

- [x] 5.1.3 Add Alpine.js interactivity
  - Implement expand/collapse
  - Implement dismissal
  - Implement keyboard navigation
  - Test accessibility

### 5.2 Create Critical Alert Badge

- [x] 5.2.1 Create Blade component for alert badge
  - Add pulsing animation
  - Add alert count display
  - Add click handler to open panel

- [x] 5.2.2 Integrate badge into navigation
  - Add to top navigation bar
  - Add to training screen header
  - Test visibility

### 5.3 Create Recommendation Card Component

- [x] 5.3.1 Create Blade component for recommendation card
  - Implement collapsed view
  - Implement expanded view
  - Add reasoning section
  - Add expected outcomes section
  - Add risks section
  - Add action buttons

- [x] 5.3.2 Add Alpine.js interactivity
  - Implement expand/collapse
  - Implement apply action
  - Implement feedback action
  - Test accessibility

### 5.4 Keyboard Shortcuts

- [x] 5.4.1 Implement Alt+A to toggle advisory panel
  - Add keyboard event listener
  - Test across browsers

- [x] 5.4.2 Implement Arrow key navigation
  - Navigate between recommendations
  - Test accessibility

### 5.5 Accessibility Testing

- [x] 5.5.1 Test keyboard navigation
  - Test all interactive elements
  - Test focus management
  - Test screen reader announcements

- [x] 5.5.2 Test color contrast
  - Test in light mode
  - Test in dark mode
  - Ensure WCAG 2.2 AA compliance

---

## Phase 6: Prediction Tracking (Week 7)

### 6.1 Implement PredictionAccuracyTracker

- [x] 6.1.1 Create `PredictionAccuracyTracker` class
  - Define recording methods
  - Define accuracy calculation methods
  - Register in service provider

- [x] 6.1.2 Implement `recordTrainingOutcome()`
  - **Validates: Property 15 (Prediction Accuracy Recording)**
  - Compare predicted vs actual stat gains
  - Calculate accuracy score
  - Persist to prediction_accuracy table
  - Write unit tests

- [x] 6.1.3 Implement `recordRaceOutcome()`
  - Compare predicted vs actual placement
  - Calculate accuracy score
  - Persist to prediction_accuracy table
  - Write unit tests

- [x] 6.1.4 Implement `getPredictionAccuracy()`
  - Calculate accuracy metrics per type
  - Calculate overall accuracy
  - Return AccuracyMetrics object
  - Write unit tests

- [x] 6.1.5 Implement model improvement flagging
  - Check accuracy thresholds
  - Flag models for review
  - Write unit tests

### 6.2 Integrate Prediction Tracking

- [x] 6.2.1 Add outcome recording to TrainingAdvisoryService
  - Call PredictionAccuracyTracker after outcomes
  - Handle errors gracefully
  - Write integration tests

- [x] 6.2.2 Create API endpoints for outcome recording
  - POST /api/advisory/training/outcome
  - POST /api/advisory/race/outcome
  - Write API tests

---

## Phase 7: Testing & Refinement (Week 8)

### 7.1 Property-Based Testing

- [x] 7.1.1 Implement Property 1: Storage Mode Consistency
  - Write property test
  - Run with multiple generators
  - Fix any failures

- [x] 7.1.2 Implement Property 2: Response Time Bounds
  - Write property test
  - Run with multiple generators
  - Fix any failures

- [x] 7.1.3 Implement Property 3: Friendship Training Priority
  - Write property test
  - Run with multiple generators
  - Fix any failures

- [x] 7.1.4 Implement Property 4: Energy-Based Rest Recommendations
  - Write property test
  - Run with multiple generators
  - Fix any failures

- [x] 7.1.5 Implement Property 5: Skill Hint Level Discount Accuracy
  - Write property test
  - Run with multiple generators
  - Fix any failures

- [x] 7.1.6 Implement Property 6: Gold Skill Prioritization
  - Write property test
  - Run with multiple generators
  - Fix any failures

- [x] 7.1.7 Implement Property 7: Stamina Requirement Calculation
  - Write property test
  - Run with multiple generators
  - Fix any failures

- [x] 7.1.8 Implement Property 8: Stamina Recovery Skill Adjustment
  - Write property test
  - Run with multiple generators
  - Fix any failures

- [x] 7.1.9 Implement Property 9: Critical Alert Generation
  - Write property test
  - Run with multiple generators
  - Fix any failures

- [x] 7.1.10 Implement Property 10: Multi-Training Bonus Calculation
  - Write property test
  - Run with multiple generators
  - Fix any failures

- [x] 7.1.11 Implement Property 11: Facility Level Progression
  - Write property test
  - Run with multiple generators
  - Fix any failures

- [x] 7.1.12 Implement Property 12: Bond Progress Tracking
  - Write property test
  - Run with multiple generators
  - Fix any failures

- [x] 7.1.13 Implement Property 13: Phase-Specific Goal Tracking
  - Write property test
  - Run with multiple generators
  - Fix any failures

- [x] 7.1.14 Implement Property 14: Offline Fallback Behavior
  - Write property test
  - Run with multiple generators
  - Fix any failures

- [x] 7.1.15 Implement Property 15: Prediction Accuracy Recording
  - Write property test
  - Run with multiple generators
  - Fix any failures

### 7.2 E2E Testing with Playwright

- [x] 7.2.1 Test complete advisory workflow
  - Navigate to training screen
  - View recommendations
  - Expand recommendation details
  - Apply recommendation
  - Verify outcome

- [x] 7.2.2 Test critical alert handling
  - Trigger critical situation
  - Verify alert appears
  - Dismiss alert
  - Verify dismissal persists

- [x] 7.2.3 Test recommendation application
  - Apply training recommendation
  - Verify training executed
  - Verify outcome recorded

- [x] 7.2.4 Test offline mode behavior
  - Disconnect network
  - Verify rule-based recommendations appear
  - Verify offline indicator shown
  - Reconnect network
  - Verify AI recommendations resume

### 7.3 Performance Optimization

- [x] 7.3.1 Optimize recommendation generation
  - Profile slow queries
  - Add database indexes
  - Implement caching
  - Verify <2s local, <5s cloud

- [x] 7.3.2 Optimize critical detection
  - Profile detection methods
  - Optimize calculations
  - Verify <500ms

- [x] 7.3.3 Optimize UI rendering
  - Profile component rendering
  - Optimize Alpine.js reactivity
  - Verify smooth interactions

### 7.4 Documentation

- [x] 7.4.1 Document API endpoints
  - Add OpenAPI/Swagger specs
  - Add usage examples
  - Add error codes

- [x] 7.4.2 Document service classes
  - Add comprehensive PHPDoc blocks
  - Add usage examples
  - Document dependencies

- [x] 7.4.3 Create user guide
  - Document advisory panel usage
  - Document keyboard shortcuts
  - Add screenshots

- [x] 7.4.4 Create developer guide
  - Document architecture
  - Document extension points
  - Add code examples

---

## Additional Tasks

### API Endpoints

- [x] A.1 Create training recommendations endpoint
  - POST /api/advisory/training/recommendations
  - Implement controller method
  - Add validation
  - Write API tests

- [x] A.2 Create skill advice endpoint
  - POST /api/advisory/skills/advice
  - Implement controller method
  - Add validation
  - Write API tests

- [x] A.3 Create race strategy endpoint
  - POST /api/advisory/race/strategy
  - Implement controller method
  - Add validation
  - Write API tests

- [x] A.4 Create critical detection endpoint
  - POST /api/advisory/critical/detect
  - Implement controller method
  - Add validation
  - Write API tests

### Caching

- [x] C.1 Implement skill catalog caching
  - Cache key: advisory:skill_catalog:{version}
  - TTL: 24 hours
  - Write tests

- [x] C.2 Implement race requirements caching
  - Cache key: advisory:race_requirements:{race_id}
  - TTL: 1 hour
  - Write tests

- [x] C.3 Implement support card meta caching
  - Cache key: advisory:support_card_meta:{version}
  - TTL: 24 hours
  - Write tests

### Local Storage Support

- [x] L.1 Implement local storage schema
  - Define TypeScript interfaces
  - Implement serialization
  - Implement deserialization
  - Write tests

- [x] L.2 Implement local storage persistence
  - Save recommendations to localStorage
  - Save alerts to localStorage
  - Save prediction accuracy to localStorage
  - Write tests

---

## Definition of Done

A task is considered complete when:

- [ ] Code is written and follows Laravel 12 conventions
- [ ] Code passes `vendor/bin/pint` formatting
- [ ] Code passes `vendor/bin/phpstan analyse` static analysis
- [ ] Unit tests are written and passing
- [ ] Integration tests are written and passing (where applicable)
- [ ] Property-based tests are written and passing (where applicable)
- [ ] E2E tests are written and passing (where applicable)
- [ ] Code is reviewed and approved
- [ ] Documentation is updated
- [ ] Works in both Local and Account storage modes
- [ ] Accessibility requirements are met (WCAG 2.2 AA)
