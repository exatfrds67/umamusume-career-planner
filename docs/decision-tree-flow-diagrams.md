# Umamusume Career Planner - Decision Tree Flow Diagrams

## Overview

This document presents decision tree flow diagrams for the Umamusume Pretty Derby Career Planner application, showing the logical decision-making processes, conditional flows, and branching logic used throughout the system for optimal recommendations. The system leverages **Laravel 12** with **TypeScript support**, **AWS Bedrock Claude 4.5** models (Opus, Sonnet, Haiku), **AWS Bedrock Nova 2** (Lite, Pro), and **Ollama** for local AI processing to support all **60 comprehensive requirements**.

## 1. Training Option Selection Decision Tree

### Text Description

The core decision tree that evaluates all available training options and selects the optimal choice based on character state, goals, risks, and strategic considerations.

### ASCII Diagram

```
[Training Decision Required]
        |
        v
[Character Energy >= 50%?] -----> [No] -----> [Energy < 30%?] -----> [Yes] -----> [RECOMMEND: Rest]
        |                                            |
        [Yes]                                        [No]
        |                                            |
        v                                            v
[Negative Conditions Present?] -----> [Yes] -----> [Severe Conditions?] -----> [Yes] -----> [RECOMMEND: Infirmary]
        |                                                    |
        [No]                                                 [No]
        |                                                    |
        v                                                    v
[Mood <= Bad?] -----> [Yes] -----> [RECOMMEND: Recreation] [Continue to Training Analysis]
        |
        [No]
        |
        v
[Race Approaching (< 3 turns)?] -----> [Yes] -----> [Character Ready?] -----> [Yes] -----> [RECOMMEND: Rest/Light Training]
        |                                                    |
        [No]                                                 [No]
        |                                                    |
        v                                                    v
[Summer Camp Period?] -----> [Yes] -----> [Energy >= 70%?] -----> [Yes] -----> [Prioritize High-Value Training]
        |                                        |                                    |
        [No]                                     [No]                                 v
        |                                        |                            [Red "!" Available?] -----> [Yes] -----> [RECOMMEND: Red "!" Training]
        v                                        v                                    |
[Goal Priority Analysis]                 [RECOMMEND: Rest]                          [No]
        |                                                                           |
        +-----> [Speed Priority?] -----> [Yes] -----> [Speed Training Available?] -----> [Yes] -----> [Support Cards Present?] -----> [Yes] -----> [RECOMMEND: Speed Training]
        |                                                    |                                                |
        |                                                    [No]                                             [No]
        |                                                    |                                                |
        +-----> [Stamina Priority?] -----> [Yes] -----> [Stamina Training Available?] -----> [Yes] -----> [Friendship Training?] -----> [Yes] -----> [RECOMMEND: Stamina Training]
        |                                                    |                                                |
        |                                                    [No]                                             [No]
        |                                                    |                                                |
        +-----> [Power Priority?] -----> [Yes] -----> [Power Training Available?] -----> [Yes] -----> [Skill Hints Available?] -----> [Yes] -----> [RECOMMEND: Power Training]
        |                                                    |                                                |
        |                                                    [No]                                             [No]
        |                                                    |                                                |
        +-----> [Guts Priority?] -----> [Yes] -----> [Guts Training Available?] -----> [Yes] -----> [Low Risk Training?] -----> [Yes] -----> [RECOMMEND: Guts Training]
        |                                                    |                                                |
        |                                                    [No]                                             [No]
        |                                                    |                                                |
        +-----> [Wit Priority?] -----> [Yes] -----> [Wit Training Available?] -----> [Yes] -----> [Energy Recovery Needed?] -----> [Yes] -----> [RECOMMEND: Wit Training]
        |                                                    |                                                |
        [No to All]                                          [No]                                             [No]
        |                                                    |                                                |
        v                                                    v                                                v
[Balanced Training Approach] -----> [RECOMMEND: Highest Expected Value Training] <---------------------------+
```

### Mermaid Diagram

```mermaid
flowchart TD
    TrainingDecision([Training Decision Required]) --> EnergyCheck{Character Energy >= 50%?}
    
    EnergyCheck -->|No| LowEnergyCheck{Energy < 30%?}
    LowEnergyCheck -->|Yes| RecommendRest[RECOMMEND: Rest]
    LowEnergyCheck -->|No| ContinueToTraining[Continue to Training Analysis]
    
    EnergyCheck -->|Yes| ConditionsCheck{Negative Conditions Present?}
    ConditionsCheck -->|Yes| SevereConditions{Severe Conditions?}
    SevereConditions -->|Yes| RecommendInfirmary[RECOMMEND: Infirmary]
    SevereConditions -->|No| ContinueToTraining
    
    ConditionsCheck -->|No| MoodCheck{Mood <= Bad?}
    MoodCheck -->|Yes| RecommendRecreation[RECOMMEND: Recreation]
    MoodCheck -->|No| RaceApproaching{Race Approaching < 3 turns?}
    
    RaceApproaching -->|Yes| CharacterReady{Character Ready?}
    CharacterReady -->|Yes| RecommendRestLight[RECOMMEND: Rest/Light Training]
    CharacterReady -->|No| ContinueToTraining
    
    RaceApproaching -->|No| SummerCamp{Summer Camp Period?}
    SummerCamp -->|Yes| SummerEnergyCheck{Energy >= 70%?}
    SummerEnergyCheck -->|Yes| HighValueTraining[Prioritize High-Value Training]
    SummerEnergyCheck -->|No| RecommendRest2[RECOMMEND: Rest]
    
    HighValueTraining --> RedExclamationCheck{Red "!" Available?}
    RedExclamationCheck -->|Yes| RecommendRedTraining[RECOMMEND: Red "!" Training]
    RedExclamationCheck -->|No| GoalPriorityAnalysis[Goal Priority Analysis]
    
    SummerCamp -->|No| GoalPriorityAnalysis
    ContinueToTraining --> GoalPriorityAnalysis
    
    GoalPriorityAnalysis --> SpeedPriority{Speed Priority?}
    GoalPriorityAnalysis --> StaminaPriority{Stamina Priority?}
    GoalPriorityAnalysis --> PowerPriority{Power Priority?}
    GoalPriorityAnalysis --> GutsPriority{Guts Priority?}
    GoalPriorityAnalysis --> WitPriority{Wit Priority?}
    
    SpeedPriority -->|Yes| SpeedAvailable{Speed Training Available?}
    SpeedAvailable -->|Yes| SpeedSupportCards{Support Cards Present?}
    SpeedSupportCards -->|Yes| RecommendSpeed[RECOMMEND: Speed Training]
    SpeedSupportCards -->|No| BalancedApproach[Balanced Training Approach]
    SpeedAvailable -->|No| BalancedApproach
    
    StaminaPriority -->|Yes| StaminaAvailable{Stamina Training Available?}
    StaminaAvailable -->|Yes| FriendshipTraining{Friendship Training?}
    FriendshipTraining -->|Yes| RecommendStamina[RECOMMEND: Stamina Training]
    FriendshipTraining -->|No| BalancedApproach
    StaminaAvailable -->|No| BalancedApproach
    
    PowerPriority -->|Yes| PowerAvailable{Power Training Available?}
    PowerAvailable -->|Yes| SkillHintsAvailable{Skill Hints Available?}
    SkillHintsAvailable -->|Yes| RecommendPower[RECOMMEND: Power Training]
    SkillHintsAvailable -->|No| BalancedApproach
    PowerAvailable -->|No| BalancedApproach
    
    GutsPriority -->|Yes| GutsAvailable{Guts Training Available?}
    GutsAvailable -->|Yes| LowRiskTraining{Low Risk Training?}
    LowRiskTraining -->|Yes| RecommendGuts[RECOMMEND: Guts Training]
    LowRiskTraining -->|No| BalancedApproach
    GutsAvailable -->|No| BalancedApproach
    
    WitPriority -->|Yes| WitAvailable{Wit Training Available?}
    WitAvailable -->|Yes| EnergyRecoveryNeeded{Energy Recovery Needed?}
    EnergyRecoveryNeeded -->|Yes| RecommendWit[RECOMMEND: Wit Training]
    EnergyRecoveryNeeded -->|No| BalancedApproach
    WitAvailable -->|No| BalancedApproach
    
    BalancedApproach --> RecommendHighestValue[RECOMMEND: Highest Expected Value Training]
    
    %% All recommendations flow to end
    RecommendRest --> End([End])
    RecommendInfirmary --> End
    RecommendRecreation --> End
    RecommendRestLight --> End
    RecommendRest2 --> End
    RecommendRedTraining --> End
    RecommendSpeed --> End
    RecommendStamina --> End
    RecommendPower --> End
    RecommendGuts --> End
    RecommendWit --> End
    RecommendHighestValue --> End
```

## 2. Race Strategy Selection Decision Tree

### Text Description

The decision tree for selecting optimal race strategies based on character stats, aptitudes, race conditions, and competitive analysis.

### ASCII Diagram

```
[Race Strategy Decision Required]
        |
        v
[Character Distance Aptitude] -----> [SS/S Aptitude?] -----> [Yes] -----> [Natural Advantage Strategy]
        |                                    |                                    |
        v                                    [No]                                 v
[A/B Aptitude?] -----> [Yes] -----> [Competitive Strategy]          [Running Style Analysis]
        |                                    |                                    |
        [No]                                 v                                    v
        |                            [Running Style Analysis]          [Speed >= 1000?] -----> [Yes] -----> [Front Runner Viable?]
        v                                    |                                    |                                    |
[C/D/G Aptitude] -----> [Avoid This Distance] [Speed >= 1000?] -----> [Yes] -----> [Front Runner Viable?]    [No]                [Yes] -----> [High Stamina?] -----> [Yes] -----> [RECOMMEND: Front Runner]
                                            |                                    |                                    |                                    |
                                            [No]                                 [No]                                 [No]                                 [No]
                                            |                                    |                                    |                                    |
                                            v                                    v                                    v                                    v
                                    [Power >= 800?] -----> [Yes] -----> [Late Surger Viable?] -----> [Yes] -----> [RECOMMEND: Late Surger]    [Pace Chaser Analysis]
                                            |                                    |                                    |
                                            [No]                                 [No]                                 [No]
                                            |                                    |                                    |
                                            v                                    v                                    v
                                    [Guts >= 600?] -----> [Yes] -----> [End Closer Viable?] -----> [Yes] -----> [RECOMMEND: End Closer]
                                            |                                    |                                    |
                                            [No]                                 [No]                                 [No]
                                            |                                    |                                    |
                                            v                                    v                                    v
                                    [RECOMMEND: Pace Chaser] <-------------------+------------------------------------+
                                            |
                                            v
[Weather Conditions Check]
        |
        +-----> [Firm/Good Conditions] -----> [Standard Strategy Confirmed]
        |
        +-----> [Soft/Heavy Conditions] -----> [Weather Skills Available?] -----> [Yes] -----> [Weather-Adapted Strategy]
        |                                                |
        |                                                [No]
        |                                                |
        |                                                v
        +-----> [Rainy Weather] -----> [Wet Conditions Skills?] -----> [Yes] -----> [Maintain Strategy]
        |                                        |                                        |
        |                                        [No]                                     v
        |                                        |                                [Strategy Confidence: High]
        |                                        v
        +-----> [Snowy Weather] -----> [Consider Strategy Adjustment] -----> [Strategy Confidence: Medium]
        |
        v
[Competition Analysis]
        |
        +-----> [Weak Field] -----> [Aggressive Strategy] -----> [Front Runner/Late Surger Preferred]
        |
        +-----> [Strong Field] -----> [Conservative Strategy] -----> [Pace Chaser/End Closer Preferred]
        |
        +-----> [Mixed Field] -----> [Balanced Strategy] -----> [Maintain Optimal Style]
        |
        v
[Final Strategy Confirmation]
        |
        +-----> [High Confidence (Stats + Aptitude + Weather)] -----> [EXECUTE: Recommended Strategy]
        |
        +-----> [Medium Confidence (Some Concerns)] -----> [EXECUTE: Conservative Variant]
        |
        +-----> [Low Confidence (Multiple Issues)] -----> [RECOMMEND: Skip Race or Emergency Training]
```

### Mermaid Diagram

```mermaid
flowchart TD
    RaceStrategyDecision([Race Strategy Decision Required]) --> DistanceAptitude[Character Distance Aptitude]
    
    DistanceAptitude --> SSAptitude{SS/S Aptitude?}
    SSAptitude -->|Yes| NaturalAdvantage[Natural Advantage Strategy]
    SSAptitude -->|No| ABAptitude{A/B Aptitude?}
    
    ABAptitude -->|Yes| CompetitiveStrategy[Competitive Strategy]
    ABAptitude -->|No| CDGAptitude[C/D/G Aptitude]
    CDGAptitude --> AvoidDistance[Avoid This Distance]
    
    NaturalAdvantage --> RunningStyleAnalysis1[Running Style Analysis]
    CompetitiveStrategy --> RunningStyleAnalysis2[Running Style Analysis]
    
    RunningStyleAnalysis1 --> SpeedCheck1{Speed >= 1000?}
    RunningStyleAnalysis2 --> SpeedCheck2{Speed >= 1000?}
    
    SpeedCheck1 -->|Yes| FrontRunnerViable1{Front Runner Viable?}
    SpeedCheck1 -->|No| PowerCheck1{Power >= 800?}
    SpeedCheck2 -->|Yes| FrontRunnerViable2{Front Runner Viable?}
    SpeedCheck2 -->|No| PowerCheck2{Power >= 800?}
    
    FrontRunnerViable1 -->|Yes| HighStamina1{High Stamina?}
    FrontRunnerViable1 -->|No| PaceChaserAnalysis1[Pace Chaser Analysis]
    FrontRunnerViable2 -->|Yes| HighStamina2{High Stamina?}
    FrontRunnerViable2 -->|No| PaceChaserAnalysis2[Pace Chaser Analysis]
    
    HighStamina1 -->|Yes| RecommendFrontRunner1[RECOMMEND: Front Runner]
    HighStamina1 -->|No| PaceChaserAnalysis1
    HighStamina2 -->|Yes| RecommendFrontRunner2[RECOMMEND: Front Runner]
    HighStamina2 -->|No| PaceChaserAnalysis2
    
    PowerCheck1 -->|Yes| LateSurgerViable1{Late Surger Viable?}
    PowerCheck1 -->|No| GutsCheck1{Guts >= 600?}
    PowerCheck2 -->|Yes| LateSurgerViable2{Late Surger Viable?}
    PowerCheck2 -->|No| GutsCheck2{Guts >= 600?}
    
    LateSurgerViable1 -->|Yes| RecommendLateSurger1[RECOMMEND: Late Surger]
    LateSurgerViable1 -->|No| RecommendPaceChaser1[RECOMMEND: Pace Chaser]
    LateSurgerViable2 -->|Yes| RecommendLateSurger2[RECOMMEND: Late Surger]
    LateSurgerViable2 -->|No| RecommendPaceChaser2[RECOMMEND: Pace Chaser]
    
    GutsCheck1 -->|Yes| EndCloserViable1{End Closer Viable?}
    GutsCheck1 -->|No| RecommendPaceChaser1
    GutsCheck2 -->|Yes| EndCloserViable2{End Closer Viable?}
    GutsCheck2 -->|No| RecommendPaceChaser2
    
    EndCloserViable1 -->|Yes| RecommendEndCloser1[RECOMMEND: End Closer]
    EndCloserViable1 -->|No| RecommendPaceChaser1
    EndCloserViable2 -->|Yes| RecommendEndCloser2[RECOMMEND: End Closer]
    EndCloserViable2 -->|No| RecommendPaceChaser2
    
    RecommendFrontRunner1 --> WeatherConditionsCheck[Weather Conditions Check]
    RecommendFrontRunner2 --> WeatherConditionsCheck
    RecommendLateSurger1 --> WeatherConditionsCheck
    RecommendLateSurger2 --> WeatherConditionsCheck
    RecommendEndCloser1 --> WeatherConditionsCheck
    RecommendEndCloser2 --> WeatherConditionsCheck
    RecommendPaceChaser1 --> WeatherConditionsCheck
    RecommendPaceChaser2 --> WeatherConditionsCheck
    PaceChaserAnalysis1 --> WeatherConditionsCheck
    PaceChaserAnalysis2 --> WeatherConditionsCheck
    
    WeatherConditionsCheck --> FirmGoodConditions[Firm/Good Conditions]
    WeatherConditionsCheck --> SoftHeavyConditions[Soft/Heavy Conditions]
    WeatherConditionsCheck --> RainyWeather[Rainy Weather]
    WeatherConditionsCheck --> SnowyWeather[Snowy Weather]
    
    FirmGoodConditions --> StandardStrategy[Standard Strategy Confirmed]
    
    SoftHeavyConditions --> WeatherSkillsAvailable{Weather Skills Available?}
    WeatherSkillsAvailable -->|Yes| WeatherAdaptedStrategy[Weather-Adapted Strategy]
    WeatherSkillsAvailable -->|No| ConsiderAdjustment[Consider Strategy Adjustment]
    
    RainyWeather --> WetConditionsSkills{Wet Conditions Skills?}
    WetConditionsSkills -->|Yes| MaintainStrategy[Maintain Strategy]
    WetConditionsSkills -->|No| ConsiderAdjustment
    
    SnowyWeather --> ConsiderAdjustment
    
    StandardStrategy --> StrategyConfidenceHigh[Strategy Confidence: High]
    WeatherAdaptedStrategy --> StrategyConfidenceHigh
    MaintainStrategy --> StrategyConfidenceHigh
    ConsiderAdjustment --> StrategyConfidenceMedium[Strategy Confidence: Medium]
    
    StrategyConfidenceHigh --> CompetitionAnalysis[Competition Analysis]
    StrategyConfidenceMedium --> CompetitionAnalysis
    
    CompetitionAnalysis --> WeakField[Weak Field]
    CompetitionAnalysis --> StrongField[Strong Field]
    CompetitionAnalysis --> MixedField[Mixed Field]
    
    WeakField --> AggressiveStrategy[Aggressive Strategy]
    AggressiveStrategy --> FrontRunnerLateSurgerPreferred[Front Runner/Late Surger Preferred]
    
    StrongField --> ConservativeStrategy[Conservative Strategy]
    ConservativeStrategy --> PaceChaserEndCloserPreferred[Pace Chaser/End Closer Preferred]
    
    MixedField --> BalancedStrategy[Balanced Strategy]
    BalancedStrategy --> MaintainOptimalStyle[Maintain Optimal Style]
    
    FrontRunnerLateSurgerPreferred --> FinalStrategyConfirmation[Final Strategy Confirmation]
    PaceChaserEndCloserPreferred --> FinalStrategyConfirmation
    MaintainOptimalStyle --> FinalStrategyConfirmation
    
    FinalStrategyConfirmation --> HighConfidence[High Confidence<br/>Stats + Aptitude + Weather]
    FinalStrategyConfirmation --> MediumConfidence[Medium Confidence<br/>Some Concerns]
    FinalStrategyConfirmation --> LowConfidence[Low Confidence<br/>Multiple Issues]
    
    HighConfidence --> ExecuteRecommended[EXECUTE: Recommended Strategy]
    MediumConfidence --> ExecuteConservative[EXECUTE: Conservative Variant]
    LowConfidence --> RecommendSkip[RECOMMEND: Skip Race or Emergency Training]
```

## 3. Skill Acquisition Decision Tree

### Text Description

The decision tree for determining optimal skill acquisition strategies based on character needs, available skill points, skill hints, and strategic priorities.

### ASCII Diagram

```text
[Skill Acquisition Decision Required]
        |
        v
[Available Skill Points >= 100?] -----> [No] -----> [SP < 50?] -----> [Yes] -----> [RECOMMEND: Focus on Training]
        |                                            |
        [Yes]                                        [No]
        |                                            |
        v                                            v
[Active Skill Hints Available?] -----> [Yes] -----> [Hint Discount Analysis]
        |                                            |
        [No]                                         +-----> [Single Hint (20% discount)?] -----> [Yes] -----> [High Priority Skill?] -----> [Yes] -----> [ACQUIRE: Hinted Skill]
        |                                            |                                                                |                                    |
        v                                            |                                                                [No]                                 [No]
[Character Role Analysis]                            +-----> [Duplicate Hints (40% discount)?] -----> [Yes] -----> [Medium Priority Skill?] -----> [Yes] -----> [ACQUIRE: Discounted Skill]
        |                                            |                                                                |                                    |
        +-----> [Speed Character?] -----> [Yes] -----> [Speed Skills Available?] -----> [Yes] -----> [Race Distance Match?] -----> [Yes] -----> [ACQUIRE: Speed Skill]
        |                                            |                                            |                                    |
        |                                            [No]                                         [No]                                 [No]
        |                                            |                                            |                                    |
        +-----> [Stamina Character?] -----> [Yes] -----> [Stamina Skills Available?] -----> [Yes] -----> [Long Distance Race?] -----> [Yes] -----> [ACQUIRE: Stamina Skill]
        |                                            |                                            |                                    |
        |                                            [No]                                         [No]                                 [No]
        |                                            |                                            |                                    |
        +-----> [Power Character?] -----> [Yes] -----> [Power Skills Available?] -----> [Yes] -----> [Sprint/Mile Race?] -----> [Yes] -----> [ACQUIRE: Power Skill]
        |                                            |                                            |                                    |
        |                                            [No]                                         [No]                                 [No]
        |                                            |                                            |                                    |
        +-----> [Guts Character?] -----> [Yes] -----> [Guts Skills Available?] -----> [Yes] -----> [Difficult Race Ahead?] -----> [Yes] -----> [ACQUIRE: Guts Skill]
        |                                            |                                            |                                    |
        |                                            [No]                                         [No]                                 [No]
        |                                            |                                            |                                    |
        +-----> [Wit Character?] -----> [Yes] -----> [Wit Skills Available?] -----> [Yes] -----> [Skill Activation Needed?] -----> [Yes] -----> [ACQUIRE: Wit Skill]
        |                                            |                                            |                                    |
        [No to All]                                  [No]                                         [No]                                 [No]
        |                                            |                                            |                                    |
        v                                            v                                            v                                    v
[Universal Skills Analysis]                          |                                            |                                    |
        |                                            |                                            |                                    |
        +-----> [Recovery Skills Available?] -----> [Yes] -----> [Character Often Tired?] -----> [Yes] -----> [ACQUIRE: Recovery Skill] <---------+
        |                                            |                                            |                                    |
        |                                            [No]                                         [No]                                 |
        |                                            |                                            |                                    |
        +-----> [Acceleration Skills Available?] -----> [Yes] -----> [Positioning Issues?] -----> [Yes] -----> [ACQUIRE: Acceleration Skill] <----+
        |                                            |                                            |                                    |
        |                                            [No]                                         [No]                                 |
        |                                            |                                            |                                    |
        +-----> [Debuff Resistance Available?] -----> [Yes] -----> [Competitive Races?] -----> [Yes] -----> [ACQUIRE: Debuff Resistance] <------+
        |                                            |                                            |                                    |
        |                                            [No]                                         [No]                                 |
        |                                            |                                            |                                    |
        +-----> [Weather Skills Available?] -----> [Yes] -----> [Weather Conditions Expected?] -----> [Yes] -----> [ACQUIRE: Weather Skill] <---+
        |                                            |                                            |
        [No to All]                                  [No]                                         [No]
        |                                            |                                            |
        v                                            v                                            v
[RECOMMEND: Save SP for Better Options] <------------+--------------------------------------------+
```

### Mermaid Diagram

```mermaid
flowchart TD
    SkillDecision([Skill Acquisition Decision Required]) --> SPCheck{Available Skill Points >= 100?}
    
    SPCheck -->|No| LowSPCheck{SP < 50?}
    LowSPCheck -->|Yes| RecommendTraining[RECOMMEND: Focus on Training]
    LowSPCheck -->|No| CharacterRoleAnalysis[Character Role Analysis]
    
    SPCheck -->|Yes| HintsAvailable{Active Skill Hints Available?}
    
    HintsAvailable -->|Yes| HintDiscountAnalysis[Hint Discount Analysis]
    HintsAvailable -->|No| CharacterRoleAnalysis
    
    HintDiscountAnalysis --> SingleHint{Single Hint 20% discount?}
    HintDiscountAnalysis --> DuplicateHints{Duplicate Hints 40% discount?}
    
    SingleHint -->|Yes| HighPrioritySkill{High Priority Skill?}
    HighPrioritySkill -->|Yes| AcquireHintedSkill[ACQUIRE: Hinted Skill]
    HighPrioritySkill -->|No| CharacterRoleAnalysis
    
    DuplicateHints -->|Yes| MediumPrioritySkill{Medium Priority Skill?}
    MediumPrioritySkill -->|Yes| AcquireDiscountedSkill[ACQUIRE: Discounted Skill]
    MediumPrioritySkill -->|No| CharacterRoleAnalysis
    
    SingleHint -->|No| CharacterRoleAnalysis
    DuplicateHints -->|No| CharacterRoleAnalysis
    
    CharacterRoleAnalysis --> SpeedCharacter{Speed Character?}
    CharacterRoleAnalysis --> StaminaCharacter{Stamina Character?}
    CharacterRoleAnalysis --> PowerCharacter{Power Character?}
    CharacterRoleAnalysis --> GutsCharacter{Guts Character?}
    CharacterRoleAnalysis --> WitCharacter{Wit Character?}
    
    SpeedCharacter -->|Yes| SpeedSkillsAvailable{Speed Skills Available?}
    SpeedSkillsAvailable -->|Yes| RaceDistanceMatch{Race Distance Match?}
    RaceDistanceMatch -->|Yes| AcquireSpeedSkill[ACQUIRE: Speed Skill]
    RaceDistanceMatch -->|No| UniversalSkillsAnalysis[Universal Skills Analysis]
    SpeedSkillsAvailable -->|No| UniversalSkillsAnalysis
    
    StaminaCharacter -->|Yes| StaminaSkillsAvailable{Stamina Skills Available?}
    StaminaSkillsAvailable -->|Yes| LongDistanceRace{Long Distance Race?}
    LongDistanceRace -->|Yes| AcquireStaminaSkill[ACQUIRE: Stamina Skill]
    LongDistanceRace -->|No| UniversalSkillsAnalysis
    StaminaSkillsAvailable -->|No| UniversalSkillsAnalysis
    
    PowerCharacter -->|Yes| PowerSkillsAvailable{Power Skills Available?}
    PowerSkillsAvailable -->|Yes| SprintMileRace{Sprint/Mile Race?}
    SprintMileRace -->|Yes| AcquirePowerSkill[ACQUIRE: Power Skill]
    SprintMileRace -->|No| UniversalSkillsAnalysis
    PowerSkillsAvailable -->|No| UniversalSkillsAnalysis
    
    GutsCharacter -->|Yes| GutsSkillsAvailable{Guts Skills Available?}
    GutsSkillsAvailable -->|Yes| DifficultRaceAhead{Difficult Race Ahead?}
    DifficultRaceAhead -->|Yes| AcquireGutsSkill[ACQUIRE: Guts Skill]
    DifficultRaceAhead -->|No| UniversalSkillsAnalysis
    GutsSkillsAvailable -->|No| UniversalSkillsAnalysis
    
    WitCharacter -->|Yes| WitSkillsAvailable{Wit Skills Available?}
    WitSkillsAvailable -->|Yes| SkillActivationNeeded{Skill Activation Needed?}
    SkillActivationNeeded -->|Yes| AcquireWitSkill[ACQUIRE: Wit Skill]
    SkillActivationNeeded -->|No| UniversalSkillsAnalysis
    WitSkillsAvailable -->|No| UniversalSkillsAnalysis
    
    SpeedCharacter -->|No| UniversalSkillsAnalysis
    StaminaCharacter -->|No| UniversalSkillsAnalysis
    PowerCharacter -->|No| UniversalSkillsAnalysis
    GutsCharacter -->|No| UniversalSkillsAnalysis
    WitCharacter -->|No| UniversalSkillsAnalysis
    
    UniversalSkillsAnalysis --> RecoverySkillsAvailable{Recovery Skills Available?}
    UniversalSkillsAnalysis --> AccelerationSkillsAvailable{Acceleration Skills Available?}
    UniversalSkillsAnalysis --> DebuffResistanceAvailable{Debuff Resistance Available?}
    UniversalSkillsAnalysis --> WeatherSkillsAvailable{Weather Skills Available?}
    
    RecoverySkillsAvailable -->|Yes| CharacterOftenTired{Character Often Tired?}
    CharacterOftenTired -->|Yes| AcquireRecoverySkill[ACQUIRE: Recovery Skill]
    CharacterOftenTired -->|No| RecommendSaveSP[RECOMMEND: Save SP for Better Options]
    RecoverySkillsAvailable -->|No| RecommendSaveSP
    
    AccelerationSkillsAvailable -->|Yes| PositioningIssues{Positioning Issues?}
    PositioningIssues -->|Yes| AcquireAccelerationSkill[ACQUIRE: Acceleration Skill]
    PositioningIssues -->|No| RecommendSaveSP
    AccelerationSkillsAvailable -->|No| RecommendSaveSP
    
    DebuffResistanceAvailable -->|Yes| CompetitiveRaces{Competitive Races?}
    CompetitiveRaces -->|Yes| AcquireDebuffResistance[ACQUIRE: Debuff Resistance]
    CompetitiveRaces -->|No| RecommendSaveSP
    DebuffResistanceAvailable -->|No| RecommendSaveSP
    
    WeatherSkillsAvailable -->|Yes| WeatherConditionsExpected{Weather Conditions Expected?}
    WeatherConditionsExpected -->|Yes| AcquireWeatherSkill[ACQUIRE: Weather Skill]
    WeatherConditionsExpected -->|No| RecommendSaveSP
    WeatherSkillsAvailable -->|No| RecommendSaveSP
    
    %% All recommendations flow to end
    RecommendTraining --> End([End])
    AcquireHintedSkill --> End
    AcquireDiscountedSkill --> End
    AcquireSpeedSkill --> End
    AcquireStaminaSkill --> End
    AcquirePowerSkill --> End
    AcquireGutsSkill --> End
    AcquireWitSkill --> End
    AcquireRecoverySkill --> End
    AcquireAccelerationSkill --> End
    AcquireDebuffResistance --> End
    AcquireWeatherSkill --> End
    RecommendSaveSP --> End
```

## 4. Support Card Selection Decision Tree

### Text Description

The decision tree for selecting optimal support card combinations based on character goals, training priorities, available cards, and synergy effects.

### ASCII Diagram

```text
[Support Card Selection Required]
        |
        v
[Character Training Goal] -----> [Speed Focus?] -----> [Yes] -----> [Speed Support Cards Available?] -----> [Yes] -----> [High Rarity Speed Cards?] -----> [Yes] -----> [SELECT: Speed Support Cards]
        |                                |                                    |                                                |                                    |
        |                                [No]                                 [No]                                             [No]                                 [No]
        |                                |                                    |                                                |                                    |
        +-----> [Stamina Focus?] -----> [Yes] -----> [Stamina Support Cards Available?] -----> [Yes] -----> [High Rarity Stamina Cards?] -----> [Yes] -----> [SELECT: Stamina Support Cards]
        |                                |                                    |                                                |                                    |
        |                                [No]                                 [No]                                             [No]                                 [No]
        |                                |                                    |                                                |                                    |
        +-----> [Power Focus?] -----> [Yes] -----> [Power Support Cards Available?] -----> [Yes] -----> [High Rarity Power Cards?] -----> [Yes] -----> [SELECT: Power Support Cards]
        |                                |                                    |                                                |                                    |
        |                                [No]                                 [No]                                             [No]                                 [No]
        |                                |                                    |                                                |                                    |
        +-----> [Guts Focus?] -----> [Yes] -----> [Guts Support Cards Available?] -----> [Yes] -----> [High Rarity Guts Cards?] -----> [Yes] -----> [SELECT: Guts Support Cards]
        |                                |                                    |                                                |                                    |
        |                                [No]                                 [No]                                             [No]                                 [No]
        |                                |                                    |                                                |                                    |
        +-----> [Wit Focus?] -----> [Yes] -----> [Wit Support Cards Available?] -----> [Yes] -----> [High Rarity Wit Cards?] -----> [Yes] -----> [SELECT: Wit Support Cards]
        |                                |                                    |                                                |                                    |
        |                                [No]                                 [No]                                             [No]                                 [No]
        |                                |                                    |                                                |                                    |
        +-----> [Balanced Build?] -----> [Yes] -----> [Mixed Support Cards Available?] -----> [Yes] -----> [Synergy Analysis] -----> [Good Synergy?] -----> [Yes] -----> [SELECT: Balanced Mix]
        |                                |                                    |                                    |                                    |
        [No to All]                      [No]                                 [No]                                 |                                    [No]
        |                                |                                    |                                    |                                    |
        v                                v                                    v                                    v                                    v
[Friendship Training Priority]           |                                    |                            [Friendship Cards Available?] -----> [Yes] -----> [SELECT: Friendship Cards]
        |                                |                                    |                                    |                                    |
        +-----> [Friendship Cards Available?] -----> [Yes] -----> [High Bond Level Cards?] -----> [Yes] -----> [SELECT: High Bond Cards] <---------+    [No]
        |                                |                                    |                                    |                                    |
        |                                [No]                                 [No]                                 [No]                                 |
        |                                |                                    |                                    |                                    |
        +-----> [Skill Hint Priority] -----> [Skill Hint Cards Available?] -----> [Yes] -----> [Desired Skills Available?] -----> [Yes] -----> [SELECT: Skill Hint Cards] <--+
        |                                |                                    |                                    |
        |                                [No]                                 [No]                                 [No]
        |                                |                                    |                                    |
        +-----> [Event Bonus Priority] -----> [Event Bonus Cards Available?] -----> [Yes] -----> [High Event Bonus?] -----> [Yes] -----> [SELECT: Event Bonus Cards]
        |                                |                                    |                                    |
        |                                [No]                                 [No]                                 [No]
        |                                |                                    |                                    |
        +-----> [Unique Effect Priority] -----> [Unique Effect Cards Available?] -----> [Yes] -----> [Beneficial Unique Effects?] -----> [Yes] -----> [SELECT: Unique Effect Cards]
        |                                |                                    |                                    |
        [No to All]                      [No]                                 [No]                                 [No]
        |                                |                                    |                                    |
        v                                v                                    v                                    v
[Default Selection Strategy]             |                                    |                                    |
        |                                |                                    |                                    |
        +-----> [Highest Rarity Available] -----> [SSR Cards Available?] -----> [Yes] -----> [SELECT: Best SSR Cards] <-----------+
        |                                |                                    |
        |                                [No]                                 [No]
        |                                |                                    |
        +-----> [SR Cards Available?] -----> [Yes] -----> [SELECT: Best SR Cards] <---------+
        |                                |                                    |
        |                                [No]                                 [No]
        |                                |                                    |
        +-----> [R Cards Available?] -----> [Yes] -----> [SELECT: Best R Cards] <----------+
        |                                |
        [No Cards Available]             [No]
        |                                |
        v                                v
[ERROR: No Support Cards Available] <----+
```

### Mermaid Diagram

```mermaid
flowchart TD
    SupportCardSelection([Support Card Selection Required]) --> CharacterTrainingGoal[Character Training Goal]
    
    CharacterTrainingGoal --> SpeedFocus{Speed Focus?}
    CharacterTrainingGoal --> StaminaFocus{Stamina Focus?}
    CharacterTrainingGoal --> PowerFocus{Power Focus?}
    CharacterTrainingGoal --> GutsFocus{Guts Focus?}
    CharacterTrainingGoal --> WitFocus{Wit Focus?}
    CharacterTrainingGoal --> BalancedBuild{Balanced Build?}
    
    SpeedFocus -->|Yes| SpeedSupportAvailable{Speed Support Cards Available?}
    SpeedSupportAvailable -->|Yes| HighRaritySpeed{High Rarity Speed Cards?}
    HighRaritySpeed -->|Yes| SelectSpeedSupport[SELECT: Speed Support Cards]
    HighRaritySpeed -->|No| FriendshipTrainingPriority[Friendship Training Priority]
    SpeedSupportAvailable -->|No| FriendshipTrainingPriority
    
    StaminaFocus -->|Yes| StaminaSupportAvailable{Stamina Support Cards Available?}
    StaminaSupportAvailable -->|Yes| HighRarityStamina{High Rarity Stamina Cards?}
    HighRarityStamina -->|Yes| SelectStaminaSupport[SELECT: Stamina Support Cards]
    HighRarityStamina -->|No| FriendshipTrainingPriority
    StaminaSupportAvailable -->|No| FriendshipTrainingPriority
    
    PowerFocus -->|Yes| PowerSupportAvailable{Power Support Cards Available?}
    PowerSupportAvailable -->|Yes| HighRarityPower{High Rarity Power Cards?}
    HighRarityPower -->|Yes| SelectPowerSupport[SELECT: Power Support Cards]
    HighRarityPower -->|No| FriendshipTrainingPriority
    PowerSupportAvailable -->|No| FriendshipTrainingPriority
    
    GutsFocus -->|Yes| GutsSupportAvailable{Guts Support Cards Available?}
    GutsSupportAvailable -->|Yes| HighRarityGuts{High Rarity Guts Cards?}
    HighRarityGuts -->|Yes| SelectGutsSupport[SELECT: Guts Support Cards]
    HighRarityGuts -->|No| FriendshipTrainingPriority
    GutsSupportAvailable -->|No| FriendshipTrainingPriority
    
    WitFocus -->|Yes| WitSupportAvailable{Wit Support Cards Available?}
    WitSupportAvailable -->|Yes| HighRarityWit{High Rarity Wit Cards?}
    HighRarityWit -->|Yes| SelectWitSupport[SELECT: Wit Support Cards]
    HighRarityWit -->|No| FriendshipTrainingPriority
    WitSupportAvailable -->|No| FriendshipTrainingPriority
    
    BalancedBuild -->|Yes| MixedSupportAvailable{Mixed Support Cards Available?}
    MixedSupportAvailable -->|Yes| SynergyAnalysis[Synergy Analysis]
    SynergyAnalysis --> GoodSynergy{Good Synergy?}
    GoodSynergy -->|Yes| SelectBalancedMix[SELECT: Balanced Mix]
    GoodSynergy -->|No| FriendshipCardsAvailable2{Friendship Cards Available?}
    FriendshipCardsAvailable2 -->|Yes| SelectFriendshipCards2[SELECT: Friendship Cards]
    FriendshipCardsAvailable2 -->|No| DefaultSelectionStrategy[Default Selection Strategy]
    MixedSupportAvailable -->|No| FriendshipTrainingPriority
    
    SpeedFocus -->|No| FriendshipTrainingPriority
    StaminaFocus -->|No| FriendshipTrainingPriority
    PowerFocus -->|No| FriendshipTrainingPriority
    GutsFocus -->|No| FriendshipTrainingPriority
    WitFocus -->|No| FriendshipTrainingPriority
    BalancedBuild -->|No| FriendshipTrainingPriority
    
    FriendshipTrainingPriority --> FriendshipCardsAvailable{Friendship Cards Available?}
    FriendshipCardsAvailable -->|Yes| HighBondLevelCards{High Bond Level Cards?}
    HighBondLevelCards -->|Yes| SelectHighBondCards[SELECT: High Bond Cards]
    HighBondLevelCards -->|No| SkillHintPriority[Skill Hint Priority]
    FriendshipCardsAvailable -->|No| SkillHintPriority
    
    SkillHintPriority --> SkillHintCardsAvailable{Skill Hint Cards Available?}
    SkillHintCardsAvailable -->|Yes| DesiredSkillsAvailable{Desired Skills Available?}
    DesiredSkillsAvailable -->|Yes| SelectSkillHintCards[SELECT: Skill Hint Cards]
    DesiredSkillsAvailable -->|No| EventBonusPriority[Event Bonus Priority]
    SkillHintCardsAvailable -->|No| EventBonusPriority
    
    EventBonusPriority --> EventBonusCardsAvailable{Event Bonus Cards Available?}
    EventBonusCardsAvailable -->|Yes| HighEventBonus{High Event Bonus?}
    HighEventBonus -->|Yes| SelectEventBonusCards[SELECT: Event Bonus Cards]
    HighEventBonus -->|No| UniqueEffectPriority[Unique Effect Priority]
    EventBonusCardsAvailable -->|No| UniqueEffectPriority
    
    UniqueEffectPriority --> UniqueEffectCardsAvailable{Unique Effect Cards Available?}
    UniqueEffectCardsAvailable -->|Yes| BeneficialUniqueEffects{Beneficial Unique Effects?}
    BeneficialUniqueEffects -->|Yes| SelectUniqueEffectCards[SELECT: Unique Effect Cards]
    BeneficialUniqueEffects -->|No| DefaultSelectionStrategy
    UniqueEffectCardsAvailable -->|No| DefaultSelectionStrategy
    
    DefaultSelectionStrategy --> HighestRarityAvailable[Highest Rarity Available]
    HighestRarityAvailable --> SSRCardsAvailable{SSR Cards Available?}
    SSRCardsAvailable -->|Yes| SelectBestSSRCards[SELECT: Best SSR Cards]
    SSRCardsAvailable -->|No| SRCardsAvailable{SR Cards Available?}
    
    SRCardsAvailable -->|Yes| SelectBestSRCards[SELECT: Best SR Cards]
    SRCardsAvailable -->|No| RCardsAvailable{R Cards Available?}
    
    RCardsAvailable -->|Yes| SelectBestRCards[SELECT: Best R Cards]
    RCardsAvailable -->|No| ErrorNoCards[ERROR: No Support Cards Available]
    
    %% All selections flow to end
    SelectSpeedSupport --> End([End])
    SelectStaminaSupport --> End
    SelectPowerSupport --> End
    SelectGutsSupport --> End
    SelectWitSupport --> End
    SelectBalancedMix --> End
    SelectFriendshipCards2 --> End
    SelectHighBondCards --> End
    SelectSkillHintCards --> End
    SelectEventBonusCards --> End
    SelectUniqueEffectCards --> End
    SelectBestSSRCards --> End
    SelectBestSRCards --> End
    SelectBestRCards --> End
    ErrorNoCards --> End
```

## 5. Energy Management Decision Tree

### Text Description

The decision tree for managing character energy levels throughout training, balancing training intensity with rest and recovery needs.

### ASCII Diagram

```text
[Energy Management Decision Required]
        |
        v
[Current Energy Level] -----> [Energy >= 80%?] -----> [Yes] -----> [High Intensity Training Available?] -----> [Yes] -----> [Important Race Soon?] -----> [Yes] -----> [RECOMMEND: High Intensity Training]
        |                                |                                    |                                                |                                    |
        |                                [No]                                 [No]                                             [No]                                 [No]
        |                                |                                    |                                                |                                    |
        +-----> [Energy 60-79%?] -----> [Yes] -----> [Medium Intensity Training Available?] -----> [Yes] -----> [Training Goals Met?] -----> [Yes] -----> [RECOMMEND: Medium Intensity Training]
        |                                |                                    |                                                |                                    |
        |                                [No]                                 [No]                                             [No]                                 [No]
        |                                |                                    |                                                |                                    |
        +-----> [Energy 40-59%?] -----> [Yes] -----> [Low Intensity Training Available?] -----> [Yes] -----> [Energy Recovery Items?] -----> [Yes] -----> [RECOMMEND: Low Intensity + Items]
        |                                |                                    |                                                |                                    |
        |                                [No]                                 [No]                                             [No]                                 [No]
        |                                |                                    |                                                |                                    |
        +-----> [Energy 20-39%?] -----> [Yes] -----> [Rest Required?] -----> [Yes] -----> [Negative Conditions Present?] -----> [Yes] -----> [RECOMMEND: Infirmary]
        |                                |                                    |                                                |                                    |
        |                                [No]                                 [No]                                             [No]                                 [No]
        |                                |                                    |                                                |                                    |
        +-----> [Energy < 20%?] -----> [Yes] -----> [Critical Energy Level] -----> [Emergency Training Needed?] -----> [Yes] -----> [RECOMMEND: Emergency Rest + Items]
        |                                |                                    |                                                |
        [No]                             [No]                                 |                                                [No]
        |                                |                                    |                                                |
        v                                v                                    v                                                v
[Energy Status Unknown] -----> [RECOMMEND: Check Character Status] <---------+                                [RECOMMEND: Complete Rest]
        |                                |
        v                                v
[Mood Factor Analysis]           [Turn Economy Analysis]
        |                                |
        +-----> [Mood: Excellent] -----> [Energy Efficiency +20%] -----> [Adjust Training Intensity Up]
        |                                |
        +-----> [Mood: Good] -----> [Energy Efficiency Normal] -----> [Maintain Current Strategy]
        |                                |
        +-----> [Mood: Normal] -----> [Energy Efficiency Normal] -----> [Maintain Current Strategy]
        |                                |
        +-----> [Mood: Bad] -----> [Energy Efficiency -10%] -----> [Adjust Training Intensity Down]
        |                                |
        +-----> [Mood: Very Bad] -----> [Energy Efficiency -20%] -----> [Prioritize Mood Recovery]
        |                                |
        v                                v
[Weather Factor Analysis]        [Turns Remaining Analysis]
        |                                |
        +-----> [Sunny Weather] -----> [Energy Cost Normal] -----> [Standard Energy Management]
        |                                |
        +-----> [Cloudy Weather] -----> [Energy Cost Normal] -----> [Standard Energy Management]
        |                                |
        +-----> [Rainy Weather] -----> [Energy Cost +10%] -----> [Conservative Energy Management]
        |                                |
        +-----> [Snowy Weather] -----> [Energy Cost +15%] -----> [Very Conservative Energy Management]
        |                                |
        v                                +-----> [Many Turns Remaining (>20)] -----> [Sustainable Energy Strategy]
[Final Energy Strategy]                  |
        |                                +-----> [Medium Turns Remaining (10-20)] -----> [Balanced Energy Strategy]
        +-----> [High Energy + Good Conditions] -----> [EXECUTE: Aggressive Training]
        |                                +-----> [Few Turns Remaining (<10)] -----> [Intensive Energy Strategy]
        +-----> [Medium Energy + Mixed Conditions] -----> [EXECUTE: Moderate Training]
        |                                |
        +-----> [Low Energy + Any Conditions] -----> [EXECUTE: Recovery Focus] <----+
        |
        +-----> [Critical Energy + Emergency] -----> [EXECUTE: Emergency Protocol]
```

### Mermaid Diagram

```mermaid
flowchart TD
    EnergyManagement([Energy Management Decision Required]) --> CurrentEnergyLevel[Current Energy Level]
    
    CurrentEnergyLevel --> Energy80Plus{Energy >= 80%?}
    CurrentEnergyLevel --> Energy60to79{Energy 60-79%?}
    CurrentEnergyLevel --> Energy40to59{Energy 40-59%?}
    CurrentEnergyLevel --> Energy20to39{Energy 20-39%?}
    CurrentEnergyLevel --> EnergyBelow20{Energy < 20%?}
    
    Energy80Plus -->|Yes| HighIntensityAvailable{High Intensity Training Available?}
    HighIntensityAvailable -->|Yes| ImportantRaceSoon{Important Race Soon?}
    ImportantRaceSoon -->|Yes| RecommendHighIntensity[RECOMMEND: High Intensity Training]
    ImportantRaceSoon -->|No| MoodFactorAnalysis[Mood Factor Analysis]
    HighIntensityAvailable -->|No| MoodFactorAnalysis
    Energy80Plus -->|No| Energy60to79
    
    Energy60to79 -->|Yes| MediumIntensityAvailable{Medium Intensity Training Available?}
    MediumIntensityAvailable -->|Yes| TrainingGoalsMet{Training Goals Met?}
    TrainingGoalsMet -->|Yes| RecommendMediumIntensity[RECOMMEND: Medium Intensity Training]
    TrainingGoalsMet -->|No| MoodFactorAnalysis
    MediumIntensityAvailable -->|No| MoodFactorAnalysis
    Energy60to79 -->|No| Energy40to59
    
    Energy40to59 -->|Yes| LowIntensityAvailable{Low Intensity Training Available?}
    LowIntensityAvailable -->|Yes| EnergyRecoveryItems{Energy Recovery Items?}
    EnergyRecoveryItems -->|Yes| RecommendLowIntensityItems[RECOMMEND: Low Intensity + Items]
    EnergyRecoveryItems -->|No| MoodFactorAnalysis
    LowIntensityAvailable -->|No| MoodFactorAnalysis
    Energy40to59 -->|No| Energy20to39
    
    Energy20to39 -->|Yes| RestRequired{Rest Required?}
    RestRequired -->|Yes| NegativeConditionsPresent{Negative Conditions Present?}
    NegativeConditionsPresent -->|Yes| RecommendInfirmary[RECOMMEND: Infirmary]
    NegativeConditionsPresent -->|No| MoodFactorAnalysis
    RestRequired -->|No| MoodFactorAnalysis
    Energy20to39 -->|No| EnergyBelow20
    
    EnergyBelow20 -->|Yes| CriticalEnergyLevel[Critical Energy Level]
    CriticalEnergyLevel --> EmergencyTrainingNeeded{Emergency Training Needed?}
    EmergencyTrainingNeeded -->|Yes| RecommendEmergencyRest[RECOMMEND: Emergency Rest + Items]
    EmergencyTrainingNeeded -->|No| RecommendCompleteRest[RECOMMEND: Complete Rest]
    EnergyBelow20 -->|No| EnergyStatusUnknown[Energy Status Unknown]
    
    EnergyStatusUnknown --> RecommendCheckStatus[RECOMMEND: Check Character Status]
    RecommendCheckStatus --> MoodFactorAnalysis
    
    MoodFactorAnalysis --> MoodExcellent[Mood: Excellent]
    MoodFactorAnalysis --> MoodGood[Mood: Good]
    MoodFactorAnalysis --> MoodNormal[Mood: Normal]
    MoodFactorAnalysis --> MoodBad[Mood: Bad]
    MoodFactorAnalysis --> MoodVeryBad[Mood: Very Bad]
    
    MoodExcellent --> EnergyEfficiencyPlus20[Energy Efficiency +20%]
    EnergyEfficiencyPlus20 --> AdjustTrainingUp[Adjust Training Intensity Up]
    
    MoodGood --> EnergyEfficiencyNormal1[Energy Efficiency Normal]
    EnergyEfficiencyNormal1 --> MaintainCurrentStrategy1[Maintain Current Strategy]
    
    MoodNormal --> EnergyEfficiencyNormal2[Energy Efficiency Normal]
    EnergyEfficiencyNormal2 --> MaintainCurrentStrategy2[Maintain Current Strategy]
    
    MoodBad --> EnergyEfficiencyMinus10[Energy Efficiency -10%]
    EnergyEfficiencyMinus10 --> AdjustTrainingDown[Adjust Training Intensity Down]
    
    MoodVeryBad --> EnergyEfficiencyMinus20[Energy Efficiency -20%]
    EnergyEfficiencyMinus20 --> PrioritizeMoodRecovery[Prioritize Mood Recovery]
    
    AdjustTrainingUp --> WeatherFactorAnalysis[Weather Factor Analysis]
    MaintainCurrentStrategy1 --> WeatherFactorAnalysis
    MaintainCurrentStrategy2 --> WeatherFactorAnalysis
    AdjustTrainingDown --> WeatherFactorAnalysis
    PrioritizeMoodRecovery --> WeatherFactorAnalysis
    
    WeatherFactorAnalysis --> SunnyWeather[Sunny Weather]
    WeatherFactorAnalysis --> CloudyWeather[Cloudy Weather]
    WeatherFactorAnalysis --> RainyWeather[Rainy Weather]
    WeatherFactorAnalysis --> SnowyWeather[Snowy Weather]
    
    SunnyWeather --> EnergyCostNormal1[Energy Cost Normal]
    EnergyCostNormal1 --> StandardEnergyManagement1[Standard Energy Management]
    
    CloudyWeather --> EnergyCostNormal2[Energy Cost Normal]
    EnergyCostNormal2 --> StandardEnergyManagement2[Standard Energy Management]
    
    RainyWeather --> EnergyCostPlus10[Energy Cost +10%]
    EnergyCostPlus10 --> ConservativeEnergyManagement[Conservative Energy Management]
    
    SnowyWeather --> EnergyCostPlus15[Energy Cost +15%]
    EnergyCostPlus15 --> VeryConservativeEnergyManagement[Very Conservative Energy Management]
    
    StandardEnergyManagement1 --> TurnsRemainingAnalysis[Turns Remaining Analysis]
    StandardEnergyManagement2 --> TurnsRemainingAnalysis
    ConservativeEnergyManagement --> TurnsRemainingAnalysis
    VeryConservativeEnergyManagement --> TurnsRemainingAnalysis
    
    TurnsRemainingAnalysis --> ManyTurnsRemaining[Many Turns Remaining >20]
    TurnsRemainingAnalysis --> MediumTurnsRemaining[Medium Turns Remaining 10-20]
    TurnsRemainingAnalysis --> FewTurnsRemaining[Few Turns Remaining <10]
    
    ManyTurnsRemaining --> SustainableEnergyStrategy[Sustainable Energy Strategy]
    MediumTurnsRemaining --> BalancedEnergyStrategy[Balanced Energy Strategy]
    FewTurnsRemaining --> IntensiveEnergyStrategy[Intensive Energy Strategy]
    
    SustainableEnergyStrategy --> FinalEnergyStrategy[Final Energy Strategy]
    BalancedEnergyStrategy --> FinalEnergyStrategy
    IntensiveEnergyStrategy --> FinalEnergyStrategy
    
    FinalEnergyStrategy --> HighEnergyGoodConditions[High Energy + Good Conditions]
    FinalEnergyStrategy --> MediumEnergyMixedConditions[Medium Energy + Mixed Conditions]
    FinalEnergyStrategy --> LowEnergyAnyConditions[Low Energy + Any Conditions]
    FinalEnergyStrategy --> CriticalEnergyEmergency[Critical Energy + Emergency]
    
    HighEnergyGoodConditions --> ExecuteAggressive[EXECUTE: Aggressive Training]
    MediumEnergyMixedConditions --> ExecuteModerate[EXECUTE: Moderate Training]
    LowEnergyAnyConditions --> ExecuteRecoveryFocus[EXECUTE: Recovery Focus]
    CriticalEnergyEmergency --> ExecuteEmergencyProtocol[EXECUTE: Emergency Protocol]
    
    %% All recommendations flow to end
    RecommendHighIntensity --> End([End])
    RecommendMediumIntensity --> End
    RecommendLowIntensityItems --> End
    RecommendInfirmary --> End
    RecommendEmergencyRest --> End
    RecommendCompleteRest --> End
    RecommendCheckStatus --> End
    ExecuteAggressive --> End
    ExecuteModerate --> End
    ExecuteRecoveryFocus --> End
    ExecuteEmergencyProtocol --> End
```

## Summary

This document provides comprehensive decision tree flow diagrams for the Umamusume Pretty Derby Career Planner application, covering the five critical decision-making processes:

1. **Training Option Selection** - The core optimization engine that evaluates all available training options based on character state, energy levels, conditions, goals, and strategic priorities.

2. **Race Strategy Selection** - Determines optimal racing strategies by analyzing character aptitudes, stats, weather conditions, and competitive field strength.

3. **Skill Acquisition** - Guides skill point spending decisions based on available hints, character role, strategic priorities, and cost-benefit analysis.

4. **Support Card Selection** - Optimizes support card combinations considering character goals, card synergies, friendship levels, and available bonuses.

5. **Energy Management** - Balances training intensity with energy conservation, factoring in mood, weather, turn economy, and strategic timing.

Each decision tree includes both ASCII diagrams for technical documentation and Mermaid diagrams for visual presentation, providing comprehensive logical flows that can be implemented in the application's recommendation engine.
