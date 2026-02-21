# Umamusume Career Planner - Decision Tree Flow Diagrams

**Document Version**: 2.3.0  
**Date**: February 21, 2026  
**Project**: UmamusumeCareerPlanner  
**Author**: Development Team  
**Status**: Current - Aligned with codebase v2.3.0 and game-accurate mechanics

---

## Table of Contents

1. [Overview](#1-overview)
2. [Training Option Selection Decision Tree](#2-training-option-selection-decision-tree)
3. [Race Strategy Selection Decision Tree](#3-race-strategy-selection-decision-tree)
4. [Skill Acquisition Decision Tree](#4-skill-acquisition-decision-tree)
5. [Support Card Selection Decision Tree](#5-support-card-selection-decision-tree)
6. [Energy Management Decision Tree](#6-energy-management-decision-tree)
7. [AI Provider Selection Decision Tree](#7-ai-provider-selection-decision-tree)
8. [Storage Mode Selection Decision Tree](#8-storage-mode-selection-decision-tree)
9. [Document Control](#9-document-control)

---

## 1. Overview

### 1.1 Purpose

This document presents decision tree flow diagrams for the Umamusume Pretty Derby Career Planner application, showing the logical decision-making processes, conditional flows, and branching logic used throughout the system for optimal recommendations. The system leverages **Laravel 12** with **TypeScript support**, **AWS Bedrock Claude 4.5** models (Opus, Sonnet, Haiku), **AWS Bedrock Nova 2** (Lite, Pro), and **Ollama** for local AI processing.

### 1.2 System Context

The decision trees documented here represent the core optimization logic implemented in:

- `app/Services/TrainingPredictionService.php` - Training optimization
- `app/Services/RaceStrategyService.php` - Race strategy analysis
- `app/Services/SkillService.php` - Skill acquisition planning
- `app/Services/SupportDeckService.php` - Support card optimization
- `app/Services/AI/HybridAIService.php` - AI provider routing
- `app/Neuron/Agents/` - AI agent decision logic

### 1.3 Key Terminology

| Term | Definition |
|------|------------|
| **Stat Cap** | Hard maximum of 1200 for all stats (Speed, Stamina, Power, Guts, Wit) |
| **Running Style** | Front, Pace, Late, End (official Global EN labels) |
| **Guts** | Position holding and navigation stat |
| **Wit** | Kakari avoidance and skill activation stat |
| **Skill Hints** | 5 levels: 10%/20%/30%/35%/40% discount (40% maximum) |
| **Support Deck** | Exactly 6 cards (5 owned + 1 borrowed) |
| **Turn Range** | 1-78 (Junior 1-24, Classic 25-48, Senior 49-72, URA 73-78) |

### 1.4 Decision Tree Notation

```mermaid
flowchart TD
    Start([Decision Point])
    Condition{Condition Check}
    Action[Action/Process]
    Result[Result/Outcome]
    
    Start --> Condition
    Condition -->|True| Action
    Condition -->|False| Result
    Action --> Result
    
    style Start fill:#e3f2fd
    style Condition fill:#fff3e0
    style Action fill:#f3e5f5
    style Result fill:#e8f5e9
```

---

## 2. Training Option Selection Decision Tree

### 2.1 Overview

The core decision tree that evaluates all available training options and selects the optimal choice based on character state, goals, risks, and strategic considerations.

**Implementation**: `app/Services/TrainingPredictionService.php`

### 2.2 Decision Flow

```mermaid
flowchart TD
    Start([Training Decision Required])
    
    %% Energy Check
    Start --> EnergyCheck{Energy >= 50?}
    EnergyCheck -->|No| CriticalEnergy{Energy < 30?}
    CriticalEnergy -->|Yes| RestRec[RECOMMEND: Rest]
    CriticalEnergy -->|No| ContinueCheck[Continue to Condition Check]
    
    %% Condition Check
    EnergyCheck -->|Yes| ConditionCheck{Negative Conditions?}
    ContinueCheck --> ConditionCheck
    ConditionCheck -->|Yes| SevereCheck{Severe Conditions?}
    SevereCheck -->|Yes| InfirmaryRec[RECOMMEND: Infirmary]
    SevereCheck -->|No| MoodCheck
    
    %% Mood Check
    ConditionCheck -->|No| MoodCheck{Mood <= Bad?}
    MoodCheck -->|Yes| RecreationRec[RECOMMEND: Recreation]
    MoodCheck -->|No| RaceCheck
    
    %% Race Check
    RaceCheck{Race in < 3 turns?}
    RaceCheck -->|Yes| ReadyCheck{Character Ready?}
    ReadyCheck -->|Yes| LightTraining[RECOMMEND: Light Training/Rest]
    ReadyCheck -->|No| StatGapAnalysis[Analyze Stat Gaps]
    
    %% Summer Camp
    RaceCheck -->|No| SummerCheck{Summer Camp?}
    SummerCheck -->|Yes| SummerEnergy{Energy >= 70?}
    SummerEnergy -->|Yes| HighValueTraining[Prioritize High-Value Training]
    SummerEnergy -->|No| SummerRest[RECOMMEND: Rest]
    
    %% Red Exclamation
    HighValueTraining --> RedCheck{Red "!" Available?}
    RedCheck -->|Yes| RedTraining[RECOMMEND: Red "!" Training<br/>Guaranteed Hints]
    
    %% Goal Priority Analysis
    SummerCheck -->|No| GoalAnalysis[Goal Priority Analysis]
    StatGapAnalysis --> GoalAnalysis
    RedCheck -->|No| GoalAnalysis
    
    GoalAnalysis --> SpeedPriority{Speed Priority?}
    GoalAnalysis --> StaminaPriority{Stamina Priority?}
    GoalAnalysis --> PowerPriority{Power Priority?}
    GoalAnalysis --> GutsPriority{Guts Priority?}
    GoalAnalysis --> WitPriority{Wit Priority?}
    
    %% Speed Path
    SpeedPriority -->|Yes| SpeedAvailable{Speed Training Available?}
    SpeedAvailable -->|Yes| SpeedSupport{Support Cards Present?}
    SpeedSupport -->|Yes| SpeedRec[RECOMMEND: Speed Training]
    SpeedSupport -->|No| BalancedApproach
    SpeedAvailable -->|No| BalancedApproach
    
    %% Stamina Path
    StaminaPriority -->|Yes| StaminaAvailable{Stamina Training Available?}
    StaminaAvailable -->|Yes| FriendshipTraining{Friendship Training?}
    FriendshipTraining -->|Yes| StaminaRec[RECOMMEND: Stamina Training]
    FriendshipTraining -->|No| BalancedApproach
    StaminaAvailable -->|No| BalancedApproach
    
    %% Power Path
    PowerPriority -->|Yes| PowerAvailable{Power Training Available?}
    PowerAvailable -->|Yes| SkillHints{Skill Hints Available?}
    SkillHints -->|Yes| PowerRec[RECOMMEND: Power Training]
    SkillHints -->|No| BalancedApproach
    PowerAvailable -->|No| BalancedApproach
    
    %% Guts Path
    GutsPriority -->|Yes| GutsAvailable{Guts Training Available?}
    GutsAvailable -->|Yes| LowRisk{Low Risk Training?}
    LowRisk -->|Yes| GutsRec[RECOMMEND: Guts Training]
    LowRisk -->|No| BalancedApproach
    GutsAvailable -->|No| BalancedApproach
    
    %% Wit Path
    WitPriority -->|Yes| WitAvailable{Wit Training Available?}
    WitAvailable -->|Yes| EnergyRecovery{Energy Recovery Needed?}
    EnergyRecovery -->|Yes| WitRec[RECOMMEND: Wit Training]
    EnergyRecovery -->|No| BalancedApproach
    WitAvailable -->|No| BalancedApproach
    
    %% Balanced Approach
    BalancedApproach[Balanced Training Approach]
    BalancedApproach --> HighestValue[RECOMMEND: Highest Expected Value]
    
    %% End States
    RestRec --> End([End])
    InfirmaryRec --> End
    RecreationRec --> End
    LightTraining --> End
    SummerRest --> End
    RedTraining --> End
    SpeedRec --> End
    StaminaRec --> End
    PowerRec --> End
    GutsRec --> End
    WitRec --> End
    HighestValue --> End
    
    style Start fill:#e3f2fd
    style End fill:#e8f5e9
```

### 2.3 Training Priority Matrix

| Priority Level | Condition | Recommended Action |
|----------------|-----------|-------------------|
| **Critical** | Energy < 30% | Mandatory Rest |
| **High** | Severe Conditions | Infirmary Visit |
| **High** | Mood = Awful/Bad | Recreation |
| **Medium** | Race in < 3 turns | Light Training/Rest |
| **Medium** | Summer Camp + Energy >= 70% | High-Value Training |
| **Standard** | Red "!" Available | Guaranteed Hint Training |
| **Standard** | Goal-Aligned Training | Stat-Specific Training |
| **Fallback** | No Clear Priority | Highest Expected Value |

### 2.4 Risk Assessment Factors

```mermaid
flowchart LR
    subgraph RiskFactors[Risk Assessment Components]
        Energy[Energy Level<br/>0-100]
        Mood[Mood Status<br/>Great to Awful]
        Conditions[Active Conditions<br/>Positive/Negative]
        Support[Support Participation<br/>Card Count]
    end
    
    subgraph RiskCalculation[Risk Calculation]
        BaseRisk[Base Failure Risk]
        EnergyMod[Energy Modifier]
        MoodMod[Mood Modifier]
        ConditionMod[Condition Modifier]
    end
    
    subgraph RiskLevel[Risk Classification]
        Low[Low < 15%<br/>Safe]
        Medium[Medium 15-40%<br/>Caution]
        High[High > 40%<br/>Avoid]
    end
    
    Energy --> BaseRisk
    Mood --> MoodMod
    Conditions --> ConditionMod
    Support --> BaseRisk
    
    BaseRisk --> RiskLevel
    EnergyMod --> RiskLevel
    MoodMod --> RiskLevel
    ConditionMod --> RiskLevel
```

---

## 3. Race Strategy Selection Decision Tree

### 3.1 Overview

The decision tree for selecting optimal race strategies based on character stats, aptitudes, race conditions, and competitive analysis.

**Implementation**: `app/Services/RaceStrategyService.php`

### 3.2 Decision Flow

```mermaid
flowchart TD
    Start([Race Strategy Decision])
    
    %% Distance Aptitude Check
    Start --> DistanceApt[Character Distance Aptitude]
    DistanceApt --> SSApt{SS/S Aptitude?}
    SSApt -->|Yes| NaturalAdvantage[Natural Advantage Strategy]
    SSApt -->|No| ABApt{A/B Aptitude?}
    ABApt -->|Yes| CompetitiveStrategy[Competitive Strategy]
    ABApt -->|No| CDGApt[C/D/G Aptitude]
    CDGApt --> AvoidDistance[RECOMMEND: Avoid This Distance]
    
    %% Running Style Analysis
    NaturalAdvantage --> StyleAnalysis1[Running Style Analysis]
    CompetitiveStrategy --> StyleAnalysis2[Running Style Analysis]
    
    %% Speed Check
    StyleAnalysis1 --> SpeedCheck1{Speed >= 1000?}
    StyleAnalysis2 --> SpeedCheck2{Speed >= 1000?}
    
    SpeedCheck1 -->|Yes| FrontViable1{Front Runner Viable?}
    SpeedCheck2 -->|Yes| FrontViable2{Front Runner Viable?}
    
    FrontViable1 -->|Yes| StaminaCheck1{High Stamina?}
    FrontViable2 -->|Yes| StaminaCheck2{High Stamina?}
    
    StaminaCheck1 -->|Yes| FrontRec1[RECOMMEND: Front Runner]
    StaminaCheck2 -->|Yes| FrontRec2[RECOMMEND: Front Runner]
    
    %% Power Check for Late Surger
    SpeedCheck1 -->|No| PowerCheck1{Power >= 800?}
    SpeedCheck2 -->|No| PowerCheck2{Power >= 800?}
    FrontViable1 -->|No| PowerCheck1
    FrontViable2 -->|No| PowerCheck2
    StaminaCheck1 -->|No| PowerCheck1
    StaminaCheck2 -->|No| PowerCheck2
    
    PowerCheck1 -->|Yes| LateViable1{Late Surger Viable?}
    PowerCheck2 -->|Yes| LateViable2{Late Surger Viable?}
    
    LateViable1 -->|Yes| LateRec1[RECOMMEND: Late Surger]
    LateViable2 -->|Yes| LateRec2[RECOMMEND: Late Surger]
    
    %% Guts Check for End Closer
    PowerCheck1 -->|No| GutsCheck1{Guts >= 600?}
    PowerCheck2 -->|No| GutsCheck2{Guts >= 600?}
    LateViable1 -->|No| GutsCheck1
    LateViable2 -->|No| GutsCheck2
    
    GutsCheck1 -->|Yes| EndViable1{End Closer Viable?}
    GutsCheck2 -->|Yes| EndViable2{End Closer Viable?}
    
    EndViable1 -->|Yes| EndRec1[RECOMMEND: End Closer]
    EndViable2 -->|Yes| EndRec2[RECOMMEND: End Closer]
    
    %% Default to Pace Chaser
    GutsCheck1 -->|No| PaceRec1[RECOMMEND: Pace Chaser]
    GutsCheck2 -->|No| PaceRec2[RECOMMEND: Pace Chaser]
    EndViable1 -->|No| PaceRec1
    EndViable2 -->|No| PaceRec2
    
    %% Weather Conditions
    FrontRec1 --> Weather[Weather Conditions Check]
    FrontRec2 --> Weather
    LateRec1 --> Weather
    LateRec2 --> Weather
    EndRec1 --> Weather
    EndRec2 --> Weather
    PaceRec1 --> Weather
    PaceRec2 --> Weather
    
    Weather --> FirmConditions[Firm/Good Conditions]
    Weather --> SoftConditions[Soft/Heavy Conditions]
    Weather --> RainyWeather[Rainy Weather]
    Weather --> SnowyWeather[Snowy Weather]
    
    FirmConditions --> StandardStrategy[Standard Strategy Confirmed]
    
    SoftConditions --> WeatherSkills{Weather Skills Available?}
    WeatherSkills -->|Yes| WeatherAdapted[Weather-Adapted Strategy]
    WeatherSkills -->|No| ConsiderAdjustment[Consider Strategy Adjustment]
    
    RainyWeather --> WetSkills{Wet Conditions Skills?}
    WetSkills -->|Yes| MaintainStrategy[Maintain Strategy]
    WetSkills -->|No| ConsiderAdjustment
    
    SnowyWeather --> ConsiderAdjustment
    
    StandardStrategy --> CompetitionAnalysis[Competition Analysis]
    WeatherAdapted --> CompetitionAnalysis
    MaintainStrategy --> CompetitionAnalysis
    ConsiderAdjustment --> CompetitionAnalysis
    
    %% Competition Analysis
    CompetitionAnalysis --> WeakField[Weak Field]
    CompetitionAnalysis --> StrongField[Strong Field]
    CompetitionAnalysis --> MixedField[Mixed Field]
    
    WeakField --> AggressiveStrategy[Aggressive Strategy<br/>Front/Late Preferred]
    StrongField --> ConservativeStrategy[Conservative Strategy<br/>Pace/End Preferred]
    MixedField --> BalancedStrategy[Balanced Strategy<br/>Maintain Optimal]
    
    %% Final Strategy Confirmation
    AggressiveStrategy --> FinalConfirmation[Final Strategy Confirmation]
    ConservativeStrategy --> FinalConfirmation
    BalancedStrategy --> FinalConfirmation
    
    FinalConfirmation --> HighConfidence{High Confidence?}
    FinalConfirmation --> MediumConfidence{Medium Confidence?}
    FinalConfirmation --> LowConfidence{Low Confidence?}
    
    HighConfidence -->|Yes| ExecuteRecommended[EXECUTE: Recommended Strategy]
    MediumConfidence -->|Yes| ExecuteConservative[EXECUTE: Conservative Variant]
    LowConfidence -->|Yes| RecommendSkip[RECOMMEND: Skip Race or Emergency Training]
    
    %% End States
    AvoidDistance --> End([End])
    ExecuteRecommended --> End
    ExecuteConservative --> End
    RecommendSkip --> End
    
    style Start fill:#e3f2fd
    style End fill:#e8f5e9
```

### 3.3 Running Style Effectiveness

| Style | Best Stats | Ideal Distance | Aptitude Requirement |
|-------|-----------|----------------|---------------------|
| **Front** | Speed 1000+, Stamina 800+ | Any | A+ or better in style |
| **Pace** | Balanced stats | Mile, Medium | B+ or better in style |
| **Late** | Speed 950+, Power 800+ | Mile, Medium, Long | A or better in style |
| **End** | Power 850+, Guts 600+ | Medium, Long | B+ or better in style |

### 3.4 Readiness Calculation Components

```mermaid
flowchart LR
    subgraph Inputs[Readiness Inputs]
        Stats[Character Stats<br/>vs Requirements]
        Aptitudes[Distance/Surface/Style<br/>Aptitudes]
        Skills[Equipped Skills<br/>Compatibility]
        Mood[Mood & Conditions<br/>Status]
    end
    
    subgraph Calculation[Readiness Calculation]
        StatScore[Stat Adequacy Score<br/>0-100]
        AptScore[Aptitude Multiplier<br/>40%-120%]
        SkillScore[Skill Synergy Score<br/>0-100]
        MoodScore[Mood Modifier<br/>-4% to +4%]
    end
    
    subgraph Output[Readiness Classification]
        Excellent[Excellent >= 85%<br/>Green]
        Good[Good 70-84%<br/>Yellow]
        Fair[Fair 55-69%<br/>Orange]
        Poor[Poor < 55%<br/>Red]
    end
    
    Stats --> StatScore
    Aptitudes --> AptScore
    Skills --> SkillScore
    Mood --> MoodScore
    
    StatScore --> Output
    AptScore --> Output
    SkillScore --> Output
    MoodScore --> Output
```

---

## 4. Skill Acquisition Decision Tree

### 4.1 Overview

The decision tree for determining optimal skill acquisition strategies based on character needs, available skill points, skill hints, and strategic priorities.

**Implementation**: `app/Services/SkillService.php`

### 4.2 Decision Flow

```mermaid
flowchart TD
    Start([Skill Acquisition Decision])
    
    %% SP Check
    Start --> SPCheck{Available SP >= 100?}
    SPCheck -->|No| LowSP{SP < 50?}
    LowSP -->|Yes| FocusTraining[RECOMMEND: Focus on Training<br/>Earn More SP]
    LowSP -->|No| CharacterRole[Character Role Analysis]
    
    %% Hint Check
    SPCheck -->|Yes| HintsAvailable{Active Skill Hints?}
    HintsAvailable -->|Yes| HintDiscount[Hint Discount Analysis]
    
    HintDiscount --> SingleHint{Single Hint<br/>20% Discount?}
    HintDiscount --> DoubleHint{2+ Hints<br/>40% Discount?}
    
    SingleHint -->|Yes| HighPriority{High Priority Skill?}
    HighPriority -->|Yes| AcquireHinted[ACQUIRE: Hinted Skill<br/>20% Discount]
    HighPriority -->|No| CharacterRole
    
    DoubleHint -->|Yes| MediumPriority{Medium Priority Skill?}
    MediumPriority -->|Yes| AcquireDiscounted[ACQUIRE: Discounted Skill<br/>40% Discount]
    MediumPriority -->|No| CharacterRole
    
    %% Character Role Analysis
    HintsAvailable -->|No| CharacterRole
    SingleHint -->|No| CharacterRole
    DoubleHint -->|No| CharacterRole
    
    CharacterRole --> SpeedChar{Speed Character?}
    CharacterRole --> StaminaChar{Stamina Character?}
    CharacterRole --> PowerChar{Power Character?}
    CharacterRole --> GutsChar{Guts Character?}
    CharacterRole --> WitChar{Wit Character?}
    
    %% Speed Path
    SpeedChar -->|Yes| SpeedSkills{Speed Skills Available?}
    SpeedSkills -->|Yes| RaceMatch{Race Distance Match?}
    RaceMatch -->|Yes| AcquireSpeed[ACQUIRE: Speed Skill]
    RaceMatch -->|No| UniversalSkills
    SpeedSkills -->|No| UniversalSkills
    
    %% Stamina Path
    StaminaChar -->|Yes| StaminaSkills{Stamina Skills Available?}
    StaminaSkills -->|Yes| LongDistance{Long Distance Race?}
    LongDistance -->|Yes| AcquireStamina[ACQUIRE: Stamina Skill]
    LongDistance -->|No| UniversalSkills
    StaminaSkills -->|No| UniversalSkills
    
    %% Power Path
    PowerChar -->|Yes| PowerSkills{Power Skills Available?}
    PowerSkills -->|Yes| SprintMile{Sprint/Mile Race?}
    SprintMile -->|Yes| AcquirePower[ACQUIRE: Power Skill]
    SprintMile -->|No| UniversalSkills
    PowerSkills -->|No| UniversalSkills
    
    %% Guts Path
    GutsChar -->|Yes| GutsSkills{Guts Skills Available?}
    GutsSkills -->|Yes| DifficultRace{Difficult Race Ahead?}
    DifficultRace -->|Yes| AcquireGuts[ACQUIRE: Guts Skill]
    DifficultRace -->|No| UniversalSkills
    GutsSkills -->|No| UniversalSkills
    
    %% Wit Path
    WitChar -->|Yes| WitSkills{Wit Skills Available?}
    WitSkills -->|Yes| SkillActivation{Skill Activation Needed?}
    SkillActivation -->|Yes| AcquireWit[ACQUIRE: Wit Skill]
    SkillActivation -->|No| UniversalSkills
    WitSkills -->|No| UniversalSkills
    
    %% Universal Skills
    UniversalSkills[Universal Skills Analysis]
    
    UniversalSkills --> RecoverySkills{Recovery Skills Available?}
    RecoverySkills -->|Yes| OftenTired{Character Often Tired?}
    OftenTired -->|Yes| AcquireRecovery[ACQUIRE: Recovery Skill]
    OftenTired -->|No| SaveSP
    RecoverySkills -->|No| SaveSP
    
    UniversalSkills --> AccelSkills{Acceleration Skills Available?}
    AccelSkills -->|Yes| PositionIssues{Positioning Issues?}
    PositionIssues -->|Yes| AcquireAccel[ACQUIRE: Acceleration Skill]
    PositionIssues -->|No| SaveSP
    AccelSkills -->|No| SaveSP
    
    UniversalSkills --> DebuffResist{Debuff Resistance Available?}
    DebuffResist -->|Yes| CompetitiveRaces{Competitive Races?}
    CompetitiveRaces -->|Yes| AcquireDebuff[ACQUIRE: Debuff Resistance]
    CompetitiveRaces -->|No| SaveSP
    DebuffResist -->|No| SaveSP
    
    UniversalSkills --> WeatherSkills{Weather Skills Available?}
    WeatherSkills -->|Yes| WeatherExpected{Weather Conditions Expected?}
    WeatherExpected -->|Yes| AcquireWeather[ACQUIRE: Weather Skill]
    WeatherExpected -->|No| SaveSP
    WeatherSkills -->|No| SaveSP
    
    %% Save SP
    SaveSP[RECOMMEND: Save SP for Better Options]
    
    %% End States
    FocusTraining --> End([End])
    AcquireHinted --> End
    AcquireDiscounted --> End
    AcquireSpeed --> End
    AcquireStamina --> End
    AcquirePower --> End
    AcquireGuts --> End
    AcquireWit --> End
    AcquireRecovery --> End
    AcquireAccel --> End
    AcquireDebuff --> End
    AcquireWeather --> End
    SaveSP --> End
    
    style Start fill:#e3f2fd
    style End fill:#e8f5e9
```

### 4.3 Skill Hint Economics

| Hint Level | Discount | SP Savings (Base 120) | Priority |
|------------|----------|-----------------------|----------|
| **0 Hints** | 0% | 0 SP (120 SP cost) | Low |
| **1 Hint** | 20% | 24 SP (96 SP cost) | Medium |
| **2+ Hints** | 40% | 48 SP (72 SP cost) | High |

**Maximum Discount**: 40% (capped at 2 hints, additional hints provide no benefit)

### 4.4 Skill Evolution Decision

```mermaid
flowchart LR
    subgraph NormalSkill[Normal Skill Owned]
        Current[Current Skill<br/>Base Cost]
    end
    
    subgraph EvolutionCheck[Evolution Requirements]
        EvoAvailable{Evolution Available?}
        MeetsReq{Meets Requirements?}
        HasSP{Sufficient SP?}
    end
    
    subgraph Decision[Evolution Decision]
        Evolve[Evolve to Rare]
        Keep[Keep Normal]
    end
    
    Current --> EvoAvailable
    EvoAvailable -->|Yes| MeetsReq
    EvoAvailable -->|No| Keep
    MeetsReq -->|Yes| HasSP
    MeetsReq -->|No| Keep
    HasSP -->|Yes| Evolve
    HasSP -->|No| Keep
```

---

## 5. Support Card Selection Decision Tree

### 5.1 Overview

The decision tree for selecting optimal support card combinations based on character goals, training priorities, available cards, and synergy effects.

**Implementation**: `app/Services/SupportDeckService.php`

### 5.2 Decision Flow

```mermaid
flowchart TD
    Start([Support Card Selection])
    
    %% Training Goal Analysis
    Start --> TrainingGoal[Character Training Goal]
    
    TrainingGoal --> SpeedFocus{Speed Focus?}
    TrainingGoal --> StaminaFocus{Stamina Focus?}
    TrainingGoal --> PowerFocus{Power Focus?}
    TrainingGoal --> GutsFocus{Guts Focus?}
    TrainingGoal --> WitFocus{Wit Focus?}
    TrainingGoal --> BalancedBuild{Balanced Build?}
    
    %% Speed Path
    SpeedFocus -->|Yes| SpeedCardsAvailable{Speed Support Cards Available?}
    SpeedCardsAvailable -->|Yes| HighRaritySpeed{High Rarity Speed Cards?}
    HighRaritySpeed -->|Yes| SelectSpeed[SELECT: Speed Support Cards]
    HighRaritySpeed -->|No| FriendshipPriority
    SpeedCardsAvailable -->|No| FriendshipPriority
    
    %% Stamina Path
    StaminaFocus -->|Yes| StaminaCardsAvailable{Stamina Support Cards Available?}
    StaminaCardsAvailable -->|Yes| HighRarityStamina{High Rarity Stamina Cards?}
    HighRarityStamina -->|Yes| SelectStamina[SELECT: Stamina Support Cards]
    HighRarityStamina -->|No| FriendshipPriority
    StaminaCardsAvailable -->|No| FriendshipPriority
    
    %% Power Path
    PowerFocus -->|Yes| PowerCardsAvailable{Power Support Cards Available?}
    PowerCardsAvailable -->|Yes| HighRarityPower{High Rarity Power Cards?}
    HighRarityPower -->|Yes| SelectPower[SELECT: Power Support Cards]
    HighRarityPower -->|No| FriendshipPriority
    PowerCardsAvailable -->|No| FriendshipPriority
    
    %% Guts Path
    GutsFocus -->|Yes| GutsCardsAvailable{Guts Support Cards Available?}
    GutsCardsAvailable -->|Yes| HighRarityGuts{High Rarity Guts Cards?}
    HighRarityGuts -->|Yes| SelectGuts[SELECT: Guts Support Cards]
    HighRarityGuts -->|No| FriendshipPriority
    GutsCardsAvailable -->|No| FriendshipPriority
    
    %% Wit Path
    WitFocus -->|Yes| WitCardsAvailable{Wit Support Cards Available?}
    WitCardsAvailable -->|Yes| HighRarityWit{High Rarity Wit Cards?}
    HighRarityWit -->|Yes| SelectWit[SELECT: Wit Support Cards]
    HighRarityWit -->|No| FriendshipPriority
    WitCardsAvailable -->|No| FriendshipPriority
    
    %% Balanced Build Path
    BalancedBuild -->|Yes| MixedCardsAvailable{Mixed Support Cards Available?}
    MixedCardsAvailable -->|Yes| SynergyAnalysis[Synergy Analysis]
    SynergyAnalysis --> GoodSynergy{Good Synergy?}
    GoodSynergy -->|Yes| SelectBalanced[SELECT: Balanced Mix]
    GoodSynergy -->|No| FriendshipPriority
    MixedCardsAvailable -->|No| FriendshipPriority
    
    %% Friendship Priority
    FriendshipPriority[Friendship Training Priority]
    FriendshipPriority --> FriendshipCards{Friendship Cards Available?}
    FriendshipCards -->|Yes| HighBond{High Bond Level Cards?}
    HighBond -->|Yes| SelectHighBond[SELECT: High Bond Cards]
    HighBond -->|No| SkillHintPriority
    FriendshipCards -->|No| SkillHintPriority
    
    %% Skill Hint Priority
    SkillHintPriority[Skill Hint Priority]
    SkillHintPriority --> HintCards{Skill Hint Cards Available?}
    HintCards -->|Yes| DesiredSkills{Desired Skills Available?}
    DesiredSkills -->|Yes| SelectHintCards[SELECT: Skill Hint Cards]
    DesiredSkills -->|No| EventPriority
    HintCards -->|No| EventPriority
    
    %% Event Bonus Priority
    EventPriority[Event Bonus Priority]
    EventPriority --> EventCards{Event Bonus Cards Available?}
    EventCards -->|Yes| HighEventBonus{High Event Bonus?}
    HighEventBonus -->|Yes| SelectEventCards[SELECT: Event Bonus Cards]
    HighEventBonus -->|No| UniqueEffectPriority
    EventCards -->|No| UniqueEffectPriority
    
    %% Unique Effect Priority
    UniqueEffectPriority[Unique Effect Priority]
    UniqueEffectPriority --> UniqueCards{Unique Effect Cards Available?}
    UniqueCards -->|Yes| BeneficialEffects{Beneficial Unique Effects?}
    BeneficialEffects -->|Yes| SelectUniqueCards[SELECT: Unique Effect Cards]
    BeneficialEffects -->|No| DefaultSelection
    UniqueCards -->|No| DefaultSelection
    
    %% Default Selection
    DefaultSelection[Default Selection Strategy]
    DefaultSelection --> HighestRarity[Highest Rarity Available]
    HighestRarity --> SSRAvailable{SSR Cards Available?}
    SSRAvailable -->|Yes| SelectSSR[SELECT: Best SSR Cards]
    SSRAvailable -->|No| SRAvailable{SR Cards Available?}
    SRAvailable -->|Yes| SelectSR[SELECT: Best SR Cards]
    SRAvailable -->|No| RAvailable{R Cards Available?}
    RAvailable -->|Yes| SelectR[SELECT: Best R Cards]
    RAvailable -->|No| ErrorNoCards[ERROR: No Support Cards Available]
    
    %% End States
    SelectSpeed --> End([End])
    SelectStamina --> End
    SelectPower --> End
    SelectGuts --> End
    SelectWit --> End
    SelectBalanced --> End
    SelectHighBond --> End
    SelectHintCards --> End
    SelectEventCards --> End
    SelectUniqueCards --> End
    SelectSSR --> End
    SelectSR --> End
    SelectR --> End
    ErrorNoCards --> End
    
    style Start fill:#e3f2fd
    style End fill:#e8f5e9
```

### 5.3 Deck Composition Rules

| Rule | Requirement | Validation |
|------|------------|------------|
| **Deck Size** | Exactly 6 cards | Hard requirement (5 owned + 1 borrowed) |
| **Type Balance** | Recommended diversity | Soft recommendation |
| **Rarity Mix** | Higher rarity preferred | Quality optimization |
| **Synergy** | Compatible card effects | Bonus calculation |
| **Friendship** | Bond level 80%+ for Friendship Training | Performance multiplier |

### 5.4 Meta Tier Integration

```mermaid
flowchart LR
    subgraph External[External Source]
        CommunityData[Community Meta Rankings]
    end
    
    subgraph System[System Integration]
        APISync[External API Sync<br/>24-hour TTL]
        MetaTier[Meta Tier Assignment<br/>SS/S/A/B]
    end
    
    subgraph Display[User Interface]
        CardList[Support Card List<br/>with Tier Badges]
        DeckBuilder[Deck Builder<br/>with Recommendations]
    end
    
    CommunityData --> APISync
    APISync --> MetaTier
    MetaTier --> CardList
    MetaTier --> DeckBuilder
```

---

## 6. Energy Management Decision Tree

### 6.1 Overview

The decision tree for managing character energy levels throughout training, balancing training intensity with rest and recovery needs.

**Implementation**: `app/Services/TrainingPredictionService.php` (integrated)

### 6.2 Decision Flow

```mermaid
flowchart TD
    Start([Energy Management Decision])
    
    %% Current Energy Level Check
    Start --> CurrentEnergy[Current Energy Level]
    
    CurrentEnergy --> Energy80Plus{Energy >= 80%?}
    CurrentEnergy --> Energy60to79{Energy 60-79%?}
    CurrentEnergy --> Energy40to59{Energy 40-59%?}
    CurrentEnergy --> Energy20to39{Energy 20-39%?}
    CurrentEnergy --> EnergyBelow20{Energy < 20%?}
    
    %% High Energy (80%+)
    Energy80Plus -->|Yes| HighIntensity{High Intensity Training Available?}
    HighIntensity -->|Yes| ImportantRace{Important Race Soon?}
    ImportantRace -->|Yes| RecommendHighIntensity[RECOMMEND: High Intensity Training]
    ImportantRace -->|No| MoodFactorAnalysis
    HighIntensity -->|No| MoodFactorAnalysis
    
    %% Medium-High Energy (60-79%)
    Energy60to79 -->|Yes| MediumIntensity{Medium Intensity Training Available?}
    MediumIntensity -->|Yes| GoalsMet{Training Goals Met?}
    GoalsMet -->|Yes| RecommendMediumIntensity[RECOMMEND: Medium Intensity Training]
    GoalsMet -->|No| MoodFactorAnalysis
    MediumIntensity -->|No| MoodFactorAnalysis
    
    %% Medium Energy (40-59%)
    Energy40to59 -->|Yes| LowIntensity{Low Intensity Training Available?}
    LowIntensity -->|Yes| RecoveryItems{Energy Recovery Items?}
    RecoveryItems -->|Yes| RecommendLowIntensityItems[RECOMMEND: Low Intensity + Items]
    RecoveryItems -->|No| MoodFactorAnalysis
    LowIntensity -->|No| MoodFactorAnalysis
    
    %% Low Energy (20-39%)
    Energy20to39 -->|Yes| RestRequired{Rest Required?}
    RestRequired -->|Yes| NegativeConditions{Negative Conditions Present?}
    NegativeConditions -->|Yes| RecommendInfirmary[RECOMMEND: Infirmary]
    NegativeConditions -->|No| MoodFactorAnalysis
    RestRequired -->|No| MoodFactorAnalysis
    
    %% Critical Energy (<20%)
    EnergyBelow20 -->|Yes| CriticalEnergy[Critical Energy Level]
    CriticalEnergy --> EmergencyTraining{Emergency Training Needed?}
    EmergencyTraining -->|Yes| RecommendEmergencyRest[RECOMMEND: Emergency Rest + Items]
    EmergencyTraining -->|No| RecommendCompleteRest[RECOMMEND: Complete Rest]
    
    %% Mood Factor Analysis
    MoodFactorAnalysis[Mood Factor Analysis]
    MoodFactorAnalysis --> MoodExcellent[Mood: Great]
    MoodFactorAnalysis --> MoodGood[Mood: Good]
    MoodFactorAnalysis --> MoodNormal[Mood: Normal]
    MoodFactorAnalysis --> MoodBad[Mood: Bad]
    MoodFactorAnalysis --> MoodAwful[Mood: Awful]
    
    MoodExcellent --> EnergyEffPlus4[Energy Efficiency +4%]
    EnergyEffPlus4 --> AdjustUp[Adjust Training Intensity Up]
    
    MoodGood --> EnergyEffPlus2[Energy Efficiency +2%]
    EnergyEffPlus2 --> MaintainStrategy1[Maintain Current Strategy]
    
    MoodNormal --> EnergyEffNormal[Energy Efficiency Normal]
    EnergyEffNormal --> MaintainStrategy2[Maintain Current Strategy]
    
    MoodBad --> EnergyEffMinus2[Energy Efficiency -2%]
    EnergyEffMinus2 --> AdjustDown[Adjust Training Intensity Down]
    
    MoodAwful --> EnergyEffMinus4[Energy Efficiency -4%]
    EnergyEffMinus4 --> PrioritizeMood[Prioritize Mood Recovery]
    
    %% Weather Factor
    AdjustUp --> WeatherAnalysis[Weather Factor Analysis]
    MaintainStrategy1 --> WeatherAnalysis
    MaintainStrategy2 --> WeatherAnalysis
    AdjustDown --> WeatherAnalysis
    PrioritizeMood --> WeatherAnalysis
    
    WeatherAnalysis --> SunnyWeather[Sunny Weather<br/>Normal Energy Cost]
    WeatherAnalysis --> CloudyWeather[Cloudy Weather<br/>Normal Energy Cost]
    WeatherAnalysis --> RainyWeather[Rainy Weather<br/>+10% Energy Cost]
    WeatherAnalysis --> SnowyWeather[Snowy Weather<br/>+15% Energy Cost]
    
    SunnyWeather --> TurnsRemaining[Turns Remaining Analysis]
    CloudyWeather --> TurnsRemaining
    RainyWeather --> TurnsRemaining
    SnowyWeather --> TurnsRemaining
    
    %% Turns Remaining
    TurnsRemaining --> ManyTurns[Many Turns Remaining >20<br/>Sustainable Energy Strategy]
    TurnsRemaining --> MediumTurns[Medium Turns 10-20<br/>Balanced Energy Strategy]
    TurnsRemaining --> FewTurns[Few Turns <10<br/>Intensive Energy Strategy]
    
    %% Final Strategy
    ManyTurns --> FinalStrategy[Final Energy Strategy]
    MediumTurns --> FinalStrategy
    FewTurns --> FinalStrategy
    
    FinalStrategy --> HighEnergyGood[High Energy + Good Conditions<br/>EXECUTE: Aggressive Training]
    FinalStrategy --> MediumEnergyMixed[Medium Energy + Mixed Conditions<br/>EXECUTE: Moderate Training]
    FinalStrategy --> LowEnergyAny[Low Energy + Any Conditions<br/>EXECUTE: Recovery Focus]
    FinalStrategy --> CriticalEnergyEmergency[Critical Energy + Emergency<br/>EXECUTE: Emergency Protocol]
    
    %% End States
    RecommendHighIntensity --> End([End])
    RecommendMediumIntensity --> End
    RecommendLowIntensityItems --> End
    RecommendInfirmary --> End
    RecommendEmergencyRest --> End
    RecommendCompleteRest --> End
    HighEnergyGood --> End
    MediumEnergyMixed --> End
    LowEnergyAny --> End
    CriticalEnergyEmergency --> End
    
    style Start fill:#e3f2fd
    style End fill:#e8f5e9
```

### 6.3 Energy Cost Modifiers

| Factor | Modifier | Impact |
|--------|----------|--------|
| **Mood: Great** | +4% efficiency | Reduce energy cost |
| **Mood: Good** | +2% efficiency | Slight reduction |
| **Mood: Normal** | 0% | No change |
| **Mood: Bad** | -2% efficiency | Increase cost |
| **Mood: Awful** | -4% efficiency | Significant increase |
| **Weather: Sunny/Cloudy** | 0% | Standard cost |
| **Weather: Rainy** | +10% | Moderate increase |
| **Weather: Snowy** | +15% | High increase |

### 6.4 Energy Recovery Options

```mermaid
flowchart LR
    subgraph RecoveryActions[Energy Recovery Actions]
        Rest[Rest<br/>+50-70 Energy]
        Recreation[Recreation<br/>+30-50 Energy<br/>+Mood Boost]
        Infirmary[Infirmary<br/>+60-80 Energy<br/>Remove Conditions]
        Items[Energy Items<br/>Variable Recovery]
    end
    
    subgraph Conditions[When to Use]
        RestWhen[Energy 30-50%<br/>No Conditions]
        RecreationWhen[Energy 40-60%<br/>Bad Mood]
        InfirmaryWhen[Energy <40%<br/>Negative Conditions]
        ItemsWhen[Emergency<br/>Energy <30%]
    end
    
    RestWhen --> Rest
    RecreationWhen --> Recreation
    InfirmaryWhen --> Infirmary
    ItemsWhen --> Items
```

---

## 7. AI Provider Selection Decision Tree

### 7.1 Overview

The decision tree for routing AI requests between local Ollama and cloud AWS Bedrock providers based on query complexity, availability, and cost optimization.

**Implementation**: `app/Services/AI/HybridAIService.php`

### 7.2 Decision Flow

```mermaid
flowchart TD
    Start([AI Query Received])
    
    %% Query Analysis
    Start --> QueryAnalysis[Query Complexity Analysis]
    QueryAnalysis --> QueryType{Query Type?}
    
    QueryType --> SimpleLookup[Simple Lookup<br/>Knowledge Base]
    QueryType --> ComplexAnalysis[Complex Analysis<br/>AI Required]
    QueryType --> ScreenshotOCR[Screenshot Analysis<br/>OCR + AI]
    
    %% Simple Lookup Path
    SimpleLookup --> LocalKB[Local Knowledge Base]
    LocalKB --> DirectResponse[Direct Response<br/>No AI Cost]
    
    %% Complex Analysis Path
    ComplexAnalysis --> AssessComplexity[Assess Complexity Score]
    ScreenshotOCR --> AssessComplexity
    
    AssessComplexity --> ComplexityScore{Complexity Score?}
    
    ComplexityScore -->|Low 0-30| LocalSimple[Local Processing<br/>Recommended]
    ComplexityScore -->|Medium 31-70| LocalComplex[Local Processing<br/>with Fallback]
    ComplexityScore -->|High 71-100| CloudRequired[Cloud Processing<br/>Required]
    
    %% Local Simple Path
    LocalSimple --> CheckOllama1{Ollama Available?}
    CheckOllama1 -->|Yes| OllamaSimple[Process with Ollama]
    CheckOllama1 -->|No| FallbackBedrock1[Fallback to Bedrock Haiku]
    
    %% Local Complex Path
    LocalComplex --> CheckOllama2{Ollama Available?}
    CheckOllama2 -->|Yes| OllamaComplex[Process with Ollama]
    CheckOllama2 -->|No| FallbackBedrock2[Fallback to Bedrock Sonnet]
    
    OllamaComplex --> ResponseTime{Response Time?}
    ResponseTime -->|<10s| QualityCheck{Quality Check}
    ResponseTime -->|>10s| TimeoutFallback[Timeout Fallback<br/>Bedrock Sonnet]
    
    QualityCheck -->|Pass >= 80%| AcceptOllama[Accept Ollama Response]
    QualityCheck -->|Fail < 80%| QualityFallback[Quality Fallback<br/>Bedrock Sonnet]
    
    %% Cloud Required Path
    CloudRequired --> SelectCloudModel[Select Cloud Model]
    
    SelectCloudModel --> StrategyAnalysis{Strategy Analysis?}
    SelectCloudModel --> ComplexCalc{Complex Calculation?}
    SelectCloudModel --> QuickResponse{Quick Response?}
    SelectCloudModel --> GeneralQuery{General Query?}
    
    StrategyAnalysis -->|Yes| ClaudeSonnet[AWS Bedrock<br/>Claude 3.5 Sonnet<br/>$3/$15 per 1M tokens]
    ComplexCalc -->|Yes| NovaPro[AWS Bedrock<br/>Nova 2 Pro<br/>Preview pricing]
    QuickResponse -->|Yes| ClaudeHaiku[AWS Bedrock<br/>Claude 3.5 Haiku<br/>$1/$5 per 1M tokens]
    GeneralQuery -->|Yes| NovaLite[AWS Bedrock<br/>Nova 2 Lite<br/>$0.00125 per 1K tokens]
    
    %% Process Responses
    OllamaSimple --> FormatResponse[Format Response]
    FallbackBedrock1 --> FormatResponse
    AcceptOllama --> FormatResponse
    FallbackBedrock2 --> FormatResponse
    TimeoutFallback --> FormatResponse
    QualityFallback --> FormatResponse
    ClaudeSonnet --> FormatResponse
    NovaPro --> FormatResponse
    ClaudeHaiku --> FormatResponse
    NovaLite --> FormatResponse
    
    %% Response Integration
    FormatResponse --> ContextIntegration[Context Integration]
    ContextIntegration --> CareerState[Merge Career State]
    ContextIntegration --> ConversationHistory[Merge Conversation History]
    ContextIntegration --> GameKnowledge[Merge Game Knowledge]
    
    CareerState --> ScoreConfidence[Score Confidence]
    ConversationHistory --> ScoreConfidence
    GameKnowledge --> ScoreConfidence
    
    ScoreConfidence --> TrackCost[Track Cost/Performance]
    TrackCost --> ReturnResponse[Return AI Response]
    
    %% Direct Response Path
    DirectResponse --> ReturnResponse
    
    %% End State
    ReturnResponse --> End([End])
    
    style Start fill:#e3f2fd
    style End fill:#e8f5e9
```

### 7.3 AI Provider Cost Comparison

| Provider | Model | Input Cost | Output Cost | Best Use Case |
|----------|-------|------------|-------------|---------------|
| **Ollama** | Local Models | $0.00 | $0.00 | Simple queries, high volume, privacy |
| **Bedrock** | Nova 2 Lite | $0.00125/1K | $0.00125/1K | General queries, cost-effective |
| **Bedrock** | Claude 3.5 Haiku | $1.00/1M | $5.00/1M | Quick responses, moderate complexity |
| **Bedrock** | Claude 3.5 Sonnet | $3.00/1M | $15.00/1M | Strategic analysis, standard recommendation |
| **Bedrock** | Nova 2 Pro | Preview | Preview | Complex calculations, advanced reasoning |
| **Bedrock** | Claude 4.5 | $5.00/1M | $25.00/1M | Most complex reasoning (rare fallback) |

### 7.4 Quality Assessment Criteria

```mermaid
flowchart LR
    subgraph QualityMetrics[Quality Assessment]
        Coherence[Response Coherence<br/>Context Relevance]
        Accuracy[Factual Accuracy<br/>Game Knowledge]
        Completeness[Query Coverage<br/>Completeness]
    end
    
    subgraph Scoring[Quality Scoring]
        Score[Aggregate Score<br/>0-100%]
    end
    
    subgraph Decision[Quality Decision]
        Accept[Accept >= 80%<br/>Use Response]
        Fallback[Reject < 80%<br/>Fallback to Cloud]
    end
    
    Coherence --> Score
    Accuracy --> Score
    Completeness --> Score
    
    Score --> Accept
    Score --> Fallback
```

---

## 8. Storage Mode Selection Decision Tree

### 8.1 Overview

The decision tree for determining optimal storage mode (Local vs Account) based on user needs, connectivity, and data requirements.

**Implementation**: `app/Http/Controllers/CareerRunController.php`

### 8.2 Decision Flow

```mermaid
flowchart TD
    Start([Storage Mode Decision])
    
    %% Initial Check
    Start --> UserContext{User Context?}
    
    UserContext --> HasAccount{Has Account?}
    UserContext --> NoAccount[No Account]
    
    %% Account Available Path
    HasAccount -->|Yes| Connectivity{Reliable Connectivity?}
    
    Connectivity -->|Yes| MultiDevice{Multi-Device Access Needed?}
    Connectivity -->|No| LocalRequired[Local Mode Required]
    
    MultiDevice -->|Yes| AccountMode1[RECOMMEND: Account Mode<br/>Cloud Sync Enabled]
    MultiDevice -->|No| DataVolume{Large Data Volume?}
    
    DataVolume -->|Yes| AccountMode2[RECOMMEND: Account Mode<br/>No Storage Limits]
    DataVolume -->|No| UserPreference1{User Preference?}
    
    UserPreference1 -->|Account| AccountMode3[SELECT: Account Mode]
    UserPreference1 -->|Local| LocalMode1[SELECT: Local Mode<br/>Can Convert Later]
    
    %% No Account Path
    NoAccount --> QuickStart{Quick Start Without Registration?}
    
    QuickStart -->|Yes| LocalMode2[RECOMMEND: Local Mode<br/>No Setup Required]
    QuickStart -->|No| CreateAccount{Willing to Create Account?}
    
    CreateAccount -->|Yes| RegisterUser[Register Account]
    RegisterUser --> AccountMode4[SELECT: Account Mode]
    
    CreateAccount -->|No| PrivacyConcerns{Privacy Concerns?}
    
    PrivacyConcerns -->|Yes| LocalMode3[RECOMMEND: Local Mode<br/>Data Stays Local]
    PrivacyConcerns -->|No| OfflineNeeded{Offline Access Critical?}
    
    OfflineNeeded -->|Yes| LocalMode4[RECOMMEND: Local Mode<br/>Full Offline Support]
    OfflineNeeded -->|No| UserPreference2{User Preference?}
    
    UserPreference2 -->|Local| LocalMode5[SELECT: Local Mode]
    UserPreference2 -->|Account| SuggestRegister[Suggest Account Registration]
    
    %% Local Mode Required Path
    LocalRequired --> StorageCheck{Browser Storage Available?}
    
    StorageCheck -->|Yes >= 5MB| LocalMode6[SELECT: Local Mode<br/>Monitor Quota]
    StorageCheck -->|No < 5MB| QuotaWarning[WARNING: Insufficient Storage<br/>Clear Data or Use Account]
    
    QuotaWarning --> ClearData{Can Clear Data?}
    ClearData -->|Yes| LocalMode7[SELECT: Local Mode<br/>After Cleanup]
    ClearData -->|No| ForceAccount[FORCE: Account Mode<br/>No Alternative]
    
    %% End States
    AccountMode1 --> End([End])
    AccountMode2 --> End
    AccountMode3 --> End
    AccountMode4 --> End
    LocalMode1 --> End
    LocalMode2 --> End
    LocalMode3 --> End
    LocalMode4 --> End
    LocalMode5 --> End
    LocalMode6 --> End
    LocalMode7 --> End
    SuggestRegister --> End
    ForceAccount --> End
    
    style Start fill:#e3f2fd
    style End fill:#e8f5e9
```

### 8.3 Storage Mode Comparison

| Feature | Local Mode | Account Mode |
|---------|-----------|--------------|
| **Authentication** | Not required | Required |
| **Data Storage** | Browser localStorage (5-10MB) | Database (unlimited) |
| **Connectivity** | Works fully offline | Requires connection to save |
| **Cross-Device** | Single browser/device only | Access from anywhere |
| **Data Backup** | Manual export required | Automatic cloud backup |
| **Identifier** | UUID | Integer ID |
| **Route Pattern** | `/plans/local/{uuid}` | `/plans/{id}` |
| **Conversion** | Can convert to Account | N/A |
| **Privacy** | Data stays local | Data on server |

### 8.4 Conversion Decision Tree

```mermaid
flowchart TD
    Start([Local Mode User])
    
    Start --> CheckLogin{Logged In?}
    
    CheckLogin -->|No| SuggestLogin[Suggest Login/Register]
    SuggestLogin --> UserLogsIn{User Logs In?}
    UserLogsIn -->|Yes| ConversionPrompt
    UserLogsIn -->|No| StayLocal[Continue Local Mode]
    
    CheckLogin -->|Yes| ConversionPrompt[Conversion Prompt<br/>Migrate to Account?]
    
    ConversionPrompt --> UserChoice{User Decision?}
    
    UserChoice -->|Convert| SelectPlans[Select Plans to Convert]
    UserChoice -->|Keep Local| KeepCopy{Keep Local Copy?}
    
    SelectPlans --> ConvertProcess[Convert to Account Mode]
    ConvertProcess --> RemoveLocal{Remove Local Copy?}
    
    RemoveLocal -->|Yes| DeleteLocal[Delete from localStorage]
    RemoveLocal -->|No| KeepBoth[Keep Both Copies]
    
    KeepCopy -->|Yes| KeepBoth
    KeepCopy -->|No| StayLocal
    
    DeleteLocal --> RedirectAccount[Redirect to /plans/{id}]
    KeepBoth --> RedirectAccount
    StayLocal --> StayInLocal[Stay in /plans/local/{uuid}]
    
    RedirectAccount --> End([End])
    StayInLocal --> End
    
    style Start fill:#e3f2fd
    style End fill:#e8f5e9
```

---

## 9. Document Control

### 9.1 Revision History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 2.3.0 | 2026-02-21 | Development Team | Updated version/date metadata; aligned with 30 current Eloquent models and 8 enums |
| 2.0.0 | 2026-01-23 | Development Team | Complete rewrite aligned with v2.0.0 implementation; updated all decision trees with current logic; added AI provider selection and storage mode trees; aligned terminology with glossary updates; corrected stat caps, skill hints, and running style labels |
| 1.0.0 | 2026-01-14 | Development Team | Initial draft with basic decision trees |

### 9.2 Related Documents

**Core Documentation:**

- [002_BRS - Business Requirements Specifications](002_BRS_Business_Requirements_Specifications.md)
- [003_SRS - Software Requirements Specifications](003_SRS_Software_Requirement_Specifications.md)
- [004_SDS - Software Design Specifications](004_SDS_Software_Design_Specifications.md)
- [000_MASTER_GLOSSARY](000_MASTER_GLOSSARY.md)

**Product Requirements:**

- [PRD-002 - Training Optimization](../prds/PRD-002_Training_Optimization.md)
- [PRD-003 - Race Strategy](../prds/PRD-003_Race_Strategy.md)
- [PRD-004 - Skill Management](../prds/PRD-004_Skill_Management.md)
- [PRD-005 - Support Card Management](../prds/PRD-005_Support_Card_Management.md)
- [PRD-006 - AI Advisory](../prds/PRD-006_AI_Advisory.md)

**Technical Specifications:**

- [SPEC-002 - Training Optimization Technical](../specs/SPEC-002_Training_Optimization_Technical.md)
- [SPEC-006 - AI Advisory Technical](../specs/SPEC-006_AI_Advisory_Technical.md)

**System Flows:**

- [FLOW-002 - Training Optimization System](../flows/FLOW-002_Training_Optimization_System.md)
- [FLOW-003 - Race Strategy System](../flows/FLOW-003_Race_Strategy_System.md)
- [FLOW-006 - AI Advisory System](../flows/FLOW-006_AI_Advisory_System.md)

### 9.3 Implementation References

| Decision Tree | Service Class | Configuration |
|---------------|---------------|---------------|
| Training Selection | `TrainingPredictionService` | `config/training.php` |
| Race Strategy | `RaceStrategyService` | `config/race.php` |
| Skill Acquisition | `SkillService` | `config/skills.php` |
| Support Card Selection | `SupportDeckService` | `config/support.php` |
| Energy Management | `TrainingPredictionService` | Integrated |
| AI Provider Selection | `HybridAIService` | `config/ai.php` |
| Storage Mode Selection | `CareerRunController` | `config/app.php` |

### 9.4 Validation Status

| Decision Tree | Unit Tests | Integration Tests | Status |
|---------------|-----------|-------------------|--------|
| Training Selection | ✅ Complete | ✅ Complete | Validated |
| Race Strategy | ✅ Complete | ✅ Complete | Validated |
| Skill Acquisition | ✅ Complete | ✅ Complete | Validated |
| Support Card Selection | ✅ Complete | 🔄 In Progress | Validated |
| Energy Management | ✅ Complete | ✅ Complete | Validated |
| AI Provider Selection | ✅ Complete | ✅ Complete | Validated |
| Storage Mode Selection | ✅ Complete | ✅ Complete | Validated |

---

*This document reflects the decision logic implemented in the Umamusume Pretty Derby Career Planner v2.3.0 codebase and serves as the authoritative reference for system optimization algorithms.*
