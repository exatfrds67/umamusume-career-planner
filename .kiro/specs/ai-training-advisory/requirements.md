# Requirements Document: AI-Powered Training Advisory System

## 1. Introduction

The AI-Powered Training Advisory System provides intelligent, context-aware guidance for players during Umamusume Pretty Derby career runs. The system analyzes character state, training scenarios, and game mechanics to deliver actionable recommendations for training choices, skill purchases, race strategies, and critical decision points.

This system leverages the existing Neuron AI infrastructure (Ollama + AWS Bedrock) to provide real-time advisory services that help players achieve consistent A+ grade character ratings through data-driven decision-making.

## 2. Glossary

- **Advisory_System**: The AI-powered recommendation engine that analyzes character state and provides guidance
- **Training_Context**: Current character state including stats, SP, skills, turn number, phase, conditions, energy, and mood
- **Recommendation**: A specific actionable suggestion with reasoning and priority level
- **Critical_Alert**: High-priority warning about situations requiring immediate attention
- **Phase**: Career stage (Junior Year, Classic Year, Senior Year, URA Finals)
- **Training_Facility**: One of five training locations (Speed, Stamina, Power, Guts, Wisdom)
- **Support_Card_Deck**: Collection of 6 support cards providing bonuses and skills
- **Skill_Hint**: Progressive discount on skill SP cost (Level 1: 10%, Level 2: 20%, Level 3: 30%, Level 4: 35%, Level 5: 40% max)
- **Friendship_Training**: High-value training with rainbow glow when support card bond ≥80
- **Bond_Level**: Support card friendship gauge (0-100, orange at 80+ enables Friendship Training)
- **Facility_Level**: Training facility progression (Level 1-5, upgrades every 4 uses, affects stat multiplier)
- **URA_Finale**: Standard scenario with 60-72 turns and three final races
- **Unity_Cup**: Alternative scenario with team race mechanics and different progression
- **Neuron_Agent**: AI agent from the existing Neuron infrastructure
- **SP_Budget**: Available Skill Points for purchasing skills (typically 300-500 per career)
- **Running_Style**: Character's race approach (Escape/Front Runner, Lead/Pace Chaser, Pace/Late Surger, Chase/End Closer)
- **Aptitude_Grade**: Character's proficiency rating (G to S, no SS) for distance/surface/style
- **Energy**: Character's training capacity (0-100, affects failure rate and stat gains)
- **Mood**: Character's mental state (Very Bad, Bad, Normal, Good, Great) affecting training effectiveness
- **Gold_Skill**: High-tier rare skill with significant race impact (e.g., Swinging Maestro, Furious Feat)
- **Stamina_Crisis**: Situation where character's stamina is insufficient for target race distance
- **Multi_Training_Bonus**: +5% stat gain per support card present at training (max +30% with 6 cards)

## 3. Functional Requirements

### 3.1 Real-Time Training Recommendations

**User Story:** As a player, I want turn-by-turn training recommendations based on my character's current state, so that I can make optimal training choices aligned with my goals.

**Acceptance Criteria:**

1. WHEN a player views the training screen, THEN the Advisory_System SHALL analyze the Training_Context and generate facility recommendations
2. WHEN generating recommendations, THEN the Advisory_System SHALL consider current stats, phase-specific goals, support card positions, bond levels, facility levels (1-5), energy (0-100), and mood
3. WHEN multiple facilities are viable, THEN the Advisory_System SHALL rank them by expected value considering multi-training bonus (+5% per support card, max +30%)
4. WHEN Friendship Training is available (bond ≥80, rainbow glow), THEN the Advisory_System SHALL prioritize it for maximum stat gains
5. WHEN energy is below 50, THEN the Advisory_System SHALL recommend Wisdom training (+5 energy) or rest to avoid high failure rates
6. WHEN a training choice has risks (injury, failure), THEN the Advisory_System SHALL include risk assessment based on current energy level
7. WHEN facility levels are uneven, THEN the Advisory_System SHALL recommend training at lower-level facilities to unlock higher multipliers (every 4 uses = +1 level)
8. The Advisory_System SHALL provide recommendations within 2 seconds for local AI and 5 seconds for cloud AI

### 3.2 Skill Purchase Advisory

**User Story:** As a player, I want intelligent skill purchase recommendations that consider my SP budget and character build, so that I can acquire the most impactful skills efficiently.

**Acceptance Criteria:**

1. WHEN a player opens the skill shop, THEN the Advisory_System SHALL analyze available skills and recommend purchases prioritized by impact
2. WHEN analyzing skills, THEN the Advisory_System SHALL consider hint levels (1-5 with 10%/20%/30%/35%/40% discounts), evolution paths, SP cost, and remaining budget
3. WHEN gold skills are available with Level 3+ hints, THEN the Advisory_System SHALL prioritize them in recommendations
4. WHEN SP budget is limited (typical budget 300-500 SP), THEN the Advisory_System SHALL recommend waiting for better hint levels or prioritizing essential skills
5. The Advisory_System SHALL track skill evolution chains (Normal → Rare) and recommend acquiring normal skills that evolve to rare variants
6. WHEN stamina recovery skills are needed, THEN the Advisory_System SHALL prioritize gold skills like Swinging Maestro, In Body and Mind, and Adrenaline Rush
7. The Advisory_System SHALL warn against "SP trap" skills with low value or highly conditional activation requirements

### 3.3 Race Strategy Generation

**User Story:** As a player, I want pre-race strategy recommendations based on my character's stats and the race requirements, so that I can maximize my chances of winning.

**Acceptance Criteria:**

1. WHEN a player views an upcoming race, THEN the Advisory_System SHALL generate a strategy recommendation
2. WHEN generating strategy, THEN the Advisory_System SHALL analyze distance requirements, competition level, terrain, weather conditions, and track condition effects
3. WHEN character stamina is insufficient for race distance, THEN the Advisory_System SHALL provide stamina gap analysis using distance-specific thresholds (Sprint: 350-400, Mile: 450-500, Medium: 600-700, Long: 850-1000 for Escape style)
4. WHEN multiple running styles are viable, THEN the Advisory_System SHALL recommend the optimal style based on aptitude grades (G to S scale) and current stats
5. WHEN weather affects track conditions (Firm/Good/Soft/Heavy), THEN the Advisory_System SHALL adjust recommendations for Power penalties and stamina drain
6. The Advisory_System SHALL calculate win probability considering stat effectiveness above 1200 soft cap (values above 1200 count as half)
7. WHEN stamina recovery skills are equipped, THEN the Advisory_System SHALL reduce stamina requirements by 150-200 per gold recovery skill

### 3.4 Critical Situation Detection

**User Story:** As a player, I want immediate alerts when my character faces critical situations, so that I can take corrective action before it's too late.

**Acceptance Criteria:**

1. WHEN stamina falls below distance-specific safe thresholds for upcoming races, THEN the Advisory_System SHALL generate a Critical_Alert with specific stamina targets
2. WHEN SP budget is insufficient for planned skill purchases (typical budget 300-500 SP per career), THEN the Advisory_System SHALL alert the player with budget recommendations
3. WHEN team race requirements are not met (Unity Cup scenario), THEN the Advisory_System SHALL alert with preparation checklist
4. WHEN character energy drops below 40 with important training ahead, THEN the Advisory_System SHALL recommend rest or Wisdom training for recovery
5. WHEN support card bonds are below 80 by Turn 25, THEN the Advisory_System SHALL alert that Friendship Training opportunities are at risk
6. WHEN facility levels are significantly unbalanced (e.g., Level 5 Speed but Level 1 Stamina), THEN the Advisory_System SHALL recommend diversifying training
7. WHEN mood is Bad or Very Bad, THEN the Advisory_System SHALL recommend recreation to restore training effectiveness
8. The Advisory_System SHALL display Critical_Alerts prominently with clear action items and priority levels

### 3.5 Support Card Deck Analysis

**User Story:** As a player, I want analysis of my support card deck's effectiveness, so that I can optimize card selection and bond management.

**Acceptance Criteria:**

1. WHEN a player views their support card deck, THEN the Advisory_System SHALL calculate synergy scores between cards based on specialization and limit breaks
2. WHEN analyzing deck composition, THEN the Advisory_System SHALL identify missing specializations or redundant cards for target distance/strategy
3. WHEN bond levels are below 80, THEN the Advisory_System SHALL recommend training facilities to increase bonds efficiently (base +7, +9 with Charming, +12 with hint mark)
4. WHEN deck changes are suggested, THEN the Advisory_System SHALL provide reasoning based on character goals, scenario mechanics, and card tier rankings
5. WHEN Friendship Training is unlocked (bond ≥80), THEN the Advisory_System SHALL highlight the massive stat multiplier benefit (+10% to +35% based on limit breaks)
6. The Advisory_System SHALL track bond progress toward 80 threshold and estimate turns needed to unlock Friendship Training
7. WHEN multiple support cards are at the same facility, THEN the Advisory_System SHALL calculate multi-training bonus (+5% per card, max +30%)

### 3.6 Phase-Specific Goal Tracking

**User Story:** As a player, I want phase-specific milestone tracking and reminders, so that I stay on track to achieve A+ grade ratings.

**Acceptance Criteria:**

1. WHEN entering a new phase (Junior/Classic/Senior Year), THEN the Advisory_System SHALL establish phase-specific stat targets and milestones
2. WHEN approaching phase transitions, THEN the Advisory_System SHALL provide readiness assessment for upcoming challenges (e.g., Summer Training Camp in Early July)
3. WHEN stat growth is behind target, THEN the Advisory_System SHALL recommend corrective training focus considering facility levels and support card positions
4. WHEN phase-specific events approach (Summer Training Camp, URA Finals), THEN the Advisory_System SHALL provide preparation checklists (e.g., maximize energy and mood for camp)
5. WHEN in Junior Year (Turns 1-24), THEN the Advisory_System SHALL prioritize bond building to unlock Friendship Training by Turn 25
6. WHEN in Classic/Senior Year, THEN the Advisory_System SHALL focus on core stat optimization and skill acquisition
7. The Advisory_System SHALL track progress toward A+ grade requirements (Speed 1200+, appropriate stamina for distance, Power 800+, Guts 600+, Wisdom 800+)
8. WHEN approaching Turn 70-72 (career end), THEN the Advisory_System SHALL verify URA Finals readiness with final stat check

### 3.7 Contextual Advisory Panel Integration

**User Story:** As a player, I want advisory recommendations integrated into my training workflow, so that I can access guidance without disrupting my gameplay flow.

**Acceptance Criteria:**

1. WHEN viewing training screens, THEN the Advisory_System SHALL display recommendations in a non-intrusive panel
2. WHEN recommendations are available, THEN the Advisory_System SHALL provide expandable details with reasoning
3. WHEN multiple recommendation types exist, THEN the Advisory_System SHALL organize them by priority and category
4. WHEN the player dismisses a recommendation, THEN the Advisory_System SHALL remember the dismissal for the current session
5. The Advisory_System SHALL support keyboard shortcuts for quick access to advisory features

### 3.8 Prediction Accuracy Tracking

**User Story:** As a system administrator, I want to track the accuracy of AI predictions versus actual outcomes, so that the system can improve over time.

**Acceptance Criteria:**

1. WHEN a player completes a training turn, THEN the Advisory_System SHALL record predicted versus actual stat gains
2. WHEN a race completes, THEN the Advisory_System SHALL compare predicted placement with actual results
3. WHEN sufficient data is collected, THEN the Advisory_System SHALL calculate accuracy metrics per recommendation type
4. WHEN accuracy falls below thresholds, THEN the Advisory_System SHALL flag the model for review or retraining
5. The Advisory_System SHALL store accuracy data for both local and account storage modes

### 3.9 Offline-Capable Basic Recommendations

**User Story:** As a player in local mode, I want basic recommendations available offline, so that I can receive guidance without internet connectivity.

**Acceptance Criteria:**

1. WHEN internet connectivity is unavailable, THEN the Advisory_System SHALL provide rule-based recommendations
2. WHEN using offline mode, THEN the Advisory_System SHALL clearly indicate that advanced AI features are unavailable
3. WHEN connectivity is restored, THEN the Advisory_System SHALL seamlessly transition to AI-powered recommendations
4. The Advisory_System SHALL cache common recommendation patterns for offline use
5. The Advisory_System SHALL function identically in local and account storage modes when offline

### 3.10 Neuron AI Infrastructure Integration

**User Story:** As a developer, I want the advisory system to leverage existing Neuron AI infrastructure, so that we maintain consistency and avoid duplicate implementations.

**Acceptance Criteria:**

1. The Advisory_System SHALL use Neuron_Agent instances for AI-powered recommendations
2. WHEN local Ollama is available, THEN the Advisory_System SHALL use it as the primary inference provider
3. WHEN local AI is unavailable or slow, THEN the Advisory_System SHALL fall back to AWS Bedrock
4. The Advisory_System SHALL reuse existing prompt templates and agent configurations from the Neuron system
5. The Advisory_System SHALL respect AI provider selection and configuration from user preferences

### 3.11 Historical Performance Analysis

**User Story:** As a player, I want to review past recommendations and their outcomes, so that I can learn from previous decisions and improve my strategy.

**Acceptance Criteria:**

1. WHEN viewing a completed career run, THEN the Advisory_System SHALL display recommendation history with outcomes
2. WHEN analyzing historical data, THEN the Advisory_System SHALL highlight successful recommendations and missed opportunities
3. WHEN comparing multiple runs, THEN the Advisory_System SHALL identify patterns in successful strategies
4. The Advisory_System SHALL provide exportable reports of recommendation accuracy and decision outcomes
5. The Advisory_System SHALL visualize recommendation trends across career phases

### 3.12 Scenario-Specific Mechanics Support

**User Story:** As a player using Unity Cup or other scenarios, I want recommendations tailored to scenario-specific mechanics, so that I can optimize for unique requirements.

**Acceptance Criteria:**

1. WHEN playing Unity Cup scenario, THEN the Advisory_System SHALL incorporate team race mechanics into recommendations
2. WHEN scenario-specific events are active, THEN the Advisory_System SHALL adjust recommendations for event bonuses
3. WHEN unique scenario mechanics affect training, THEN the Advisory_System SHALL explain mechanic interactions in reasoning
4. The Advisory_System SHALL maintain scenario profiles with mechanic definitions and optimal strategies
5. The Advisory_System SHALL detect active scenario from character data and apply appropriate recommendation logic

## 4. Non-Functional Requirements

### 4.1 Performance

- Response time for local AI recommendations: ≤2 seconds
- Response time for cloud AI recommendations: ≤5 seconds
- System shall handle concurrent recommendations for multiple career runs

### 4.2 Reliability

- System shall gracefully degrade to rule-based recommendations when AI services are unavailable
- Prediction accuracy tracking shall not impact recommendation generation performance
- System shall maintain state consistency across local and account storage modes

### 4.3 Usability

- Advisory panel shall be non-intrusive and dismissible
- Recommendations shall include clear reasoning and expected outcomes
- Critical alerts shall be visually distinct and prioritized
- System shall support keyboard shortcuts for accessibility

### 4.4 Compatibility

- System shall work in both Local Mode (browser storage) and Account Mode (database)
- System shall function offline with rule-based recommendations
- System shall integrate with existing Neuron AI infrastructure without modifications

## 5. Constraints

- Must leverage existing Neuron AI infrastructure (Ollama + AWS Bedrock)
- Must maintain compatibility with dual storage architecture (Local/Account modes)
- Must not require internet connectivity for basic functionality
- Must respect user privacy and data storage preferences
- Must follow Laravel 12 conventions and project coding standards

## 6. Assumptions

- Neuron AI infrastructure is properly configured and operational
- Game mechanics formulas are accurately documented and stable
- Support card meta rankings are regularly updated from community sources
- Users have basic understanding of Umamusume Pretty Derby game mechanics
- Browser localStorage has sufficient capacity for local mode recommendations

## 7. Dependencies

- Neuron AI Service (existing)
- Game Mechanics Engine (to be implemented)
- Character state management system (existing)
- Support card database (existing)
- Skill catalog system (existing)
- Race requirements database (existing)
