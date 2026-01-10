# Umamusume Career Planner - System Process Flow Diagrams

## Overview

This document presents the system-level process flow diagrams for the Umamusume Pretty Derby Career Planner application, showing how internal processes interact, data flows between components, and system-level decision making. The system is built with **Laravel 12** (released February 24, 2025) with **TypeScript support**, **Tailwind CSS v4** (released January 22, 2025), and integrates with **AWS Bedrock Claude 4.5** models and **AWS Bedrock Nova 2** for AI capabilities, supporting all **60 comprehensive requirements**.

## 1. External Data Integration and Synchronization Flow

### Text Description

The external data integration process manages connections to multiple community APIs, handles data synchronization, caching strategies, and fallback mechanisms when external sources are unavailable. The system primarily integrates with **umapyoi.net** (active public API) and **UmamusumeDB.com** after the deprecation of SimpleSandman/UmaMusumeAPI (EOL October 29, 2024).

### ASCII Diagram

```
[System Startup] -----> [Initialize API Connections]
        |
        v
[API Health Check]
        |
        +-----> [UmaMusumeAPI] -----> [Character/Race Data]
        |       (GitHub)                    |
        |                                   v
        +-----> [umapyoi.net] -----> [Japanese Game Data] -----> [Data Validation]
        |                                   |                         |
        +-----> [UmamusumeDB.com] -----> [Calculator Tools] ----------+
        |                                   |                         |
        +-----> [Community Sources] -----> [Meta/Tier Lists] ---------+
        |                                                             |
        v                                                             v
[Connection Status Assessment] <--------------------------------------+
        |
        +-----> [All APIs Available] -----> [Full Sync Mode]
        |                                        |
        +-----> [Partial APIs Available] -----> [Selective Sync]
        |                                        |
        +-----> [No APIs Available] -----> [Offline Mode] -----> [Use Cached Data]
        |                                        |
        v                                        v
[Data Processing Pipeline] <---------------------+
        |
        +-----> [Data Normalization] -----> [Schema Mapping]
        |
        +-----> [Conflict Resolution] -----> [Priority Rules]
        |
        +-----> [Cache Update] -----> [Timestamp Tracking]
        |
        v
[Local Database Update] -----> [Change Detection]
        |                            |
        v                            v
[Notification System] <--------------+
        |
        v
[User Interface Refresh]
```

### Mermaid Diagram

```mermaid
flowchart TD
    SystemStartup([System Startup]) --> InitAPI[Initialize API Connections]
    InitAPI --> APIHealthCheck[API Health Check]
    
    APIHealthCheck --> UmaMusumeAPI[UmaMusumeAPI<br/>GitHub]
    APIHealthCheck --> Umapyoi[umapyoi.net]
    APIHealthCheck --> UmamusumeDB[UmamusumeDB.com]
    APIHealthCheck --> CommunityAPI[Community Sources]
    
    UmaMusumeAPI --> CharacterRaceData[Character/Race Data]
    Umapyoi --> JapaneseGameData[Japanese Game Data]
    UmamusumeDB --> CalculatorTools[Calculator Tools]
    CommunityAPI --> MetaTierLists[Meta/Tier Lists]
    
    CharacterRaceData --> DataValidation[Data Validation]
    JapaneseGameData --> DataValidation
    CalculatorTools --> DataValidation
    MetaTierLists --> DataValidation
    
    DataValidation --> ConnectionStatus[Connection Status Assessment]
    
    ConnectionStatus --> AllAPIs[All APIs Available<br/>Full Sync Mode]
    ConnectionStatus --> PartialAPIs[Partial APIs Available<br/>Selective Sync]
    ConnectionStatus --> NoAPIs[No APIs Available<br/>Offline Mode]
    
    NoAPIs --> UseCachedData[Use Cached Data]
    
    AllAPIs --> DataProcessing[Data Processing Pipeline]
    PartialAPIs --> DataProcessing
    UseCachedData --> DataProcessing
    
    DataProcessing --> DataNormalization[Data Normalization<br/>Schema Mapping]
    DataProcessing --> ConflictResolution[Conflict Resolution<br/>Priority Rules]
    DataProcessing --> CacheUpdate[Cache Update<br/>Timestamp Tracking]
    
    DataNormalization --> LocalDBUpdate[Local Database Update]
    ConflictResolution --> LocalDBUpdate
    CacheUpdate --> LocalDBUpdate
    
    LocalDBUpdate --> ChangeDetection[Change Detection]
    ChangeDetection --> NotificationSystem[Notification System]
    NotificationSystem --> UIRefresh[User Interface Refresh]
```

## 2. Training Optimization Engine Process Flow

### Text Description

The core optimization engine that processes character state, analyzes training options, calculates predictions, and generates recommendations using multiple algorithms and data sources. Built with **Laravel 12** backend and enhanced by **AWS Bedrock** AI models for intelligent decision making.

### ASCII Diagram

```
[Training Request] -----> [Character State Analysis]
        |
        v
[Data Collection]
        |
        +-----> [Current Stats] -----> [Stat Progression Analysis]
        |
        +-----> [Support Cards] -----> [Friendship Levels Check]
        |
        +-----> [Energy/Mood] -----> [Risk Assessment]
        |
        +-----> [Conditions] -----> [Impact Calculation]
        |
        +-----> [Turn Context] -----> [Career Phase Analysis]
        |
        v
[Training Options Evaluation]
        |
        +-----> [Speed Training] -----> [Stat Gain Calculation]
        |                                    |
        +-----> [Stamina Training] -----> [Support Participation] -----> [Friendship Bonus]
        |                                    |                              |
        +-----> [Power Training] -----> [Skill Hint Probability] -----> [SP Value Analysis]
        |                                    |                              |
        +-----> [Guts Training] -----> [Red "!" Detection] -----> [Guaranteed Hints]
        |                                    |                              |
        +-----> [Wit Training] -----> [Energy Recovery Bonus] -------------+
        |                                    |                              |
        +-----> [Rest] -----> [Energy Restoration] -------------------------+
        |                                    |                              |
        +-----> [Recreation] -----> [Mood Improvement] ---------------------+
        |                                                                   |
        v                                                                   v
[Optimization Algorithms] <-------------------------------------------------+
        |
        +-----> [Goal Priority Weighting] -----> [Distance-Specific Requirements]
        |
        +-----> [Turn Economy Analysis] -----> [Remaining Turns vs Goals]
        |
        +-----> [Risk-Reward Calculation] -----> [Failure Probability vs Benefit]
        |
        +-----> [Long-term Impact Assessment] -----> [Career Trajectory Modeling]
        |
        v
[Recommendation Scoring]
        |
        +-----> [Primary Option] -----> [Highest Expected Value]
        |
        +-----> [Alternative Options] -----> [Risk-Adjusted Alternatives]
        |
        +-----> [Risk Warnings] -----> [Failure Probability Alerts]
        |
        v
[Output Generation] -----> [Formatted Recommendations]
        |
        v
[Prediction Logging] -----> [Accuracy Tracking Database]
```

### Mermaid Diagram

```mermaid
flowchart TD
    TrainingRequest([Training Request]) --> CharacterStateAnalysis[Character State Analysis]
    CharacterStateAnalysis --> DataCollection[Data Collection]
    
    DataCollection --> CurrentStats[Current Stats]
    DataCollection --> SupportCards[Support Cards]
    DataCollection --> EnergyMood[Energy/Mood]
    DataCollection --> Conditions[Conditions]
    DataCollection --> TurnContext[Turn Context]
    
    CurrentStats --> StatProgression[Stat Progression Analysis]
    SupportCards --> FriendshipCheck[Friendship Levels Check]
    EnergyMood --> RiskAssessment[Risk Assessment]
    Conditions --> ImpactCalculation[Impact Calculation]
    TurnContext --> CareerPhaseAnalysis[Career Phase Analysis]
    
    StatProgression --> TrainingOptionsEval[Training Options Evaluation]
    FriendshipCheck --> TrainingOptionsEval
    RiskAssessment --> TrainingOptionsEval
    ImpactCalculation --> TrainingOptionsEval
    CareerPhaseAnalysis --> TrainingOptionsEval
    
    TrainingOptionsEval --> SpeedTraining[Speed Training]
    TrainingOptionsEval --> StaminaTraining[Stamina Training]
    TrainingOptionsEval --> PowerTraining[Power Training]
    TrainingOptionsEval --> GutsTraining[Guts Training]
    TrainingOptionsEval --> WitTraining[Wit Training]
    TrainingOptionsEval --> Rest[Rest]
    TrainingOptionsEval --> Recreation[Recreation]
    
    SpeedTraining --> StatGainCalc[Stat Gain Calculation]
    StaminaTraining --> SupportParticipation[Support Participation]
    PowerTraining --> SkillHintProb[Skill Hint Probability]
    GutsTraining --> RedExclamation[Red "!" Detection]
    WitTraining --> EnergyRecoveryBonus[Energy Recovery Bonus]
    Rest --> EnergyRestoration[Energy Restoration]
    Recreation --> MoodImprovement[Mood Improvement]
    
    SupportParticipation --> FriendshipBonus[Friendship Bonus]
    SkillHintProb --> SPValueAnalysis[SP Value Analysis]
    RedExclamation --> GuaranteedHints[Guaranteed Hints]
    
    StatGainCalc --> OptimizationAlgorithms[Optimization Algorithms]
    FriendshipBonus --> OptimizationAlgorithms
    SPValueAnalysis --> OptimizationAlgorithms
    GuaranteedHints --> OptimizationAlgorithms
    EnergyRecoveryBonus --> OptimizationAlgorithms
    EnergyRestoration --> OptimizationAlgorithms
    MoodImprovement --> OptimizationAlgorithms
    
    OptimizationAlgorithms --> GoalPriorityWeighting[Goal Priority Weighting]
    OptimizationAlgorithms --> TurnEconomyAnalysis[Turn Economy Analysis]
    OptimizationAlgorithms --> RiskRewardCalc[Risk-Reward Calculation]
    OptimizationAlgorithms --> LongTermImpact[Long-term Impact Assessment]
    
    GoalPriorityWeighting --> DistanceRequirements[Distance-Specific Requirements]
    TurnEconomyAnalysis --> RemainingTurnsGoals[Remaining Turns vs Goals]
    RiskRewardCalc --> FailureProbBenefit[Failure Probability vs Benefit]
    LongTermImpact --> CareerTrajectory[Career Trajectory Modeling]
    
    DistanceRequirements --> RecommendationScoring[Recommendation Scoring]
    RemainingTurnsGoals --> RecommendationScoring
    FailureProbBenefit --> RecommendationScoring
    CareerTrajectory --> RecommendationScoring
    
    RecommendationScoring --> PrimaryOption[Primary Option<br/>Highest Expected Value]
    RecommendationScoring --> AlternativeOptions[Alternative Options<br/>Risk-Adjusted Alternatives]
    RecommendationScoring --> RiskWarnings[Risk Warnings<br/>Failure Probability Alerts]
    
    PrimaryOption --> OutputGeneration[Output Generation]
    AlternativeOptions --> OutputGeneration
    RiskWarnings --> OutputGeneration
    
    OutputGeneration --> FormattedRecommendations[Formatted Recommendations]
    FormattedRecommendations --> PredictionLogging[Prediction Logging]
    PredictionLogging --> AccuracyTracking[Accuracy Tracking Database]
```

## 3. AI Model Selection and Fallback Process Flow

### Text Description

The intelligent AI system that manages model selection between local **Ollama** and cloud **AWS Bedrock** services, handles fallback scenarios, and optimizes response quality and performance. The system uses **Claude 4.5** models (Opus: $5/$25 per 1M tokens, Sonnet: $3/$15, Haiku: $1/$5) and **Nova 2** models (Lite: $0.00125 per 1K tokens, Pro: Preview) for different complexity levels.

### ASCII Diagram

```
[AI Query Received] -----> [Query Complexity Analysis]
        |
        v
[Query Classification]
        |
        +-----> [Simple Lookup] -----> [Local Knowledge Base] -----> [Direct Response]
        |
        +-----> [Complex Analysis] -----> [AI Processing Required]
        |
        +-----> [Screenshot Analysis] -----> [OCR + AI Required]
        |
        v
[Local Ollama Attempt]
        |
        +-----> [Model Loading Check] -----> [Ollama Available?]
        |                                        |
        |                                        +-----> [Yes] -----> [Start Processing]
        |                                        |                         |
        |                                        +-----> [No] -----> [Skip to AWS Fallback]
        |                                                                   |
        v                                                                   |
[Response Time Monitoring] <------------------------------------------------+
        |
        +-----> [< 5 seconds] -----> [Continue Processing]
        |                                   |
        +-----> [5-10 seconds] -----> [Quality Check Preparation]
        |                                   |
        +-----> [> 10 seconds] -----> [Trigger AWS Fallback] -----> [Terminate Ollama]
        |                                                                   |
        v                                                                   |
[Ollama Response Received] <------------------------------------------------+
        |
        v
[Quality Assessment]
        |
        +-----> [Response Coherence] -----> [Context Relevance Check]
        |
        +-----> [Factual Accuracy] -----> [Game Knowledge Validation]
        |
        +-----> [Completeness] -----> [Query Coverage Analysis]
        |
        v
[Quality Score Calculation]
        |
        +-----> [Score >= 80%] -----> [Accept Ollama Response] -----> [Response Delivery]
        |
        +-----> [Score < 80%] -----> [Trigger AWS Fallback]
        |
        v
[AWS Bedrock Fallback]
        |
        +-----> [Query Type Analysis]
        |       |
        |       +-----> [Strategic Analysis] -----> [Claude 4.5 Sonnet]
        |       |
        |       +-----> [Complex Calculation] -----> [Nova Pro]
        |       |
        |       +-----> [Quick Response] -----> [Claude 4.5 Haiku]
        |       |
        |       +-----> [General Query] -----> [Nova Lite]
        |
        v
[AWS Processing]
        |
        +-----> [Request Formatting] -----> [Context Injection]
        |
        +-----> [Model Invocation] -----> [Response Monitoring]
        |
        +-----> [Response Validation] -----> [Format Checking]
        |
        v
[Response Integration]
        |
        +-----> [Context Merging] -----> [Career State Integration]
        |
        +-----> [Confidence Scoring] -----> [Model Source Disclosure]
        |
        +-----> [Follow-up Generation] -----> [Related Suggestions]
        |
        v
[Final Response Assembly] -----> [Response Delivery]
        |
        v
[Performance Logging]
        |
        +-----> [Response Time Tracking] -----> [Model Performance Database]
        |
        +-----> [Quality Metrics] -----> [Accuracy Improvement Data]
        |
        +-----> [Cost Tracking] -----> [AWS Usage Optimization]
        |
        v
[Learning Integration] -----> [Model Selection Optimization]
```

### Mermaid Diagram

```mermaid
flowchart TD
    AIQueryReceived([AI Query Received]) --> QueryComplexityAnalysis[Query Complexity Analysis]
    QueryComplexityAnalysis --> QueryClassification[Query Classification]
    
    QueryClassification --> SimpleLookup[Simple Lookup]
    QueryClassification --> ComplexAnalysis[Complex Analysis]
    QueryClassification --> ScreenshotAnalysis[Screenshot Analysis]
    
    SimpleLookup --> LocalKB[Local Knowledge Base]
    LocalKB --> DirectResponse[Direct Response]
    
    ComplexAnalysis --> AIProcessingRequired[AI Processing Required]
    ScreenshotAnalysis --> OCRAIRequired[OCR + AI Required]
    
    AIProcessingRequired --> LocalOllamaAttempt[Local Ollama Attempt]
    OCRAIRequired --> LocalOllamaAttempt
    
    LocalOllamaAttempt --> ModelLoadingCheck[Model Loading Check]
    ModelLoadingCheck --> OllamaAvailable{Ollama Available?}
    
    OllamaAvailable -->|Yes| StartProcessing[Start Processing]
    OllamaAvailable -->|No| SkipToAWS[Skip to AWS Fallback]
    
    StartProcessing --> ResponseTimeMonitoring[Response Time Monitoring]
    
    ResponseTimeMonitoring --> Under5Sec[< 5 seconds<br/>Continue Processing]
    ResponseTimeMonitoring --> Between5And10[5-10 seconds<br/>Quality Check Preparation]
    ResponseTimeMonitoring --> Over10Sec[> 10 seconds<br/>Trigger AWS Fallback]
    
    Over10Sec --> TerminateOllama[Terminate Ollama]
    TerminateOllama --> AWSFallback[AWS Bedrock Fallback]
    
    Under5Sec --> OllamaResponseReceived[Ollama Response Received]
    Between5And10 --> OllamaResponseReceived
    
    OllamaResponseReceived --> QualityAssessment[Quality Assessment]
    
    QualityAssessment --> ResponseCoherence[Response Coherence<br/>Context Relevance Check]
    QualityAssessment --> FactualAccuracy[Factual Accuracy<br/>Game Knowledge Validation]
    QualityAssessment --> Completeness[Completeness<br/>Query Coverage Analysis]
    
    ResponseCoherence --> QualityScoreCalc[Quality Score Calculation]
    FactualAccuracy --> QualityScoreCalc
    Completeness --> QualityScoreCalc
    
    QualityScoreCalc --> HighScore[Score >= 80%<br/>Accept Ollama Response]
    QualityScoreCalc --> LowScore[Score < 80%<br/>Trigger AWS Fallback]
    
    HighScore --> ResponseDelivery[Response Delivery]
    LowScore --> AWSFallback
    SkipToAWS --> AWSFallback
    
    AWSFallback --> QueryTypeAnalysis[Query Type Analysis]
    
    QueryTypeAnalysis --> StrategicAnalysis[Strategic Analysis<br/>Claude 4.5 Sonnet]
    QueryTypeAnalysis --> ComplexCalculation[Complex Calculation<br/>Nova Pro]
    QueryTypeAnalysis --> QuickResponse[Quick Response<br/>Claude 4.5 Haiku]
    QueryTypeAnalysis --> GeneralQuery[General Query<br/>Nova Lite]
    
    StrategicAnalysis --> AWSProcessing[AWS Processing]
    ComplexCalculation --> AWSProcessing
    QuickResponse --> AWSProcessing
    GeneralQuery --> AWSProcessing
    
    AWSProcessing --> RequestFormatting[Request Formatting<br/>Context Injection]
    AWSProcessing --> ModelInvocation[Model Invocation<br/>Response Monitoring]
    AWSProcessing --> ResponseValidation[Response Validation<br/>Format Checking]
    
    RequestFormatting --> ResponseIntegration[Response Integration]
    ModelInvocation --> ResponseIntegration
    ResponseValidation --> ResponseIntegration
    
    ResponseIntegration --> ContextMerging[Context Merging<br/>Career State Integration]
    ResponseIntegration --> ConfidenceScoring[Confidence Scoring<br/>Model Source Disclosure]
    ResponseIntegration --> FollowupGeneration[Follow-up Generation<br/>Related Suggestions]
    
    ContextMerging --> FinalResponseAssembly[Final Response Assembly]
    ConfidenceScoring --> FinalResponseAssembly
    FollowupGeneration --> FinalResponseAssembly
    
    FinalResponseAssembly --> ResponseDelivery
    
    ResponseDelivery --> PerformanceLogging[Performance Logging]
    
    PerformanceLogging --> ResponseTimeTracking[Response Time Tracking<br/>Model Performance Database]
    PerformanceLogging --> QualityMetrics[Quality Metrics<br/>Accuracy Improvement Data]
    PerformanceLogging --> CostTracking[Cost Tracking<br/>AWS Usage Optimization]
    
    ResponseTimeTracking --> LearningIntegration[Learning Integration<br/>Model Selection Optimization]
    QualityMetrics --> LearningIntegration
    CostTracking --> LearningIntegration
    
    DirectResponse --> ResponseDelivery
```

## 4. Career Data Management and Analytics Process Flow

### Text Description

The comprehensive data management system that handles career progression tracking, historical analysis, performance metrics calculation, and pattern recognition for optimization improvements.

### ASCII Diagram

```
[Career Event Trigger] -----> [Event Classification]
        |
        v
[Event Type Routing]
        |
        +-----> [Training Session] -----> [Training Data Capture]
        |                                      |
        |                                      +-----> [Predicted vs Actual Results]
        |                                      +-----> [Support Card Participation]
        |                                      +-----> [Skill Hints Acquired]
        |                                      +-----> [Energy/Mood Changes]
        |
        +-----> [Race Completion] -----> [Race Data Capture]
        |                                     |
        |                                     +-----> [Final Position/Time]
        |                                     +-----> [Strategy Effectiveness]
        |                                     +-----> [Stat Adequacy Analysis]
        |                                     +-----> [Fan/SP Gains]
        |
        +-----> [Skill Acquisition] -----> [Skill Data Capture]
        |                                       |
        |                                       +-----> [SP Cost Paid]
        |                                       +-----> [Hints Used]
        |                                       +-----> [Evolution Tracking]
        |
        +-----> [Turn Progression] -----> [Turn Data Capture]
        |                                      |
        |                                      +-----> [Character State Snapshot]
        |                                      +-----> [Goal Progress Update]
        |                                      +-----> [Phase Transition Tracking]
        |
        v
[Data Validation and Storage]
        |
        +-----> [Data Integrity Check] -----> [Constraint Validation]
        |
        +-----> [Duplicate Detection] -----> [Merge Resolution]
        |
        +-----> [Database Transaction] -----> [ACID Compliance]
        |
        v
[Real-time Analytics Processing]
        |
        +-----> [Performance Metrics Calculation]
        |       |
        |       +-----> [Training Efficiency] -----> [Stat Gains per Turn]
        |       +-----> [Prediction Accuracy] -----> [Expected vs Actual]
        |       +-----> [Goal Progress Rate] -----> [Completion Trajectory]
        |       +-----> [Resource Utilization] -----> [SP/Energy Efficiency]
        |
        +-----> [Pattern Recognition Analysis]
        |       |
        |       +-----> [Successful Decision Sequences] -----> [Strategy Patterns]
        |       +-----> [Failure Point Identification] -----> [Risk Patterns]
        |       +-----> [Optimal Timing Detection] -----> [Phase Strategies]
        |       +-----> [Support Card Synergies] -----> [Deck Effectiveness]
        |
        +-----> [Comparative Analysis]
        |       |
        |       +-----> [Multi-Career Comparison] -----> [Character Performance]
        |       +-----> [Meta Strategy Tracking] -----> [Community Benchmarks]
        |       +-----> [Improvement Identification] -----> [Optimization Opportunities]
        |
        v
[Historical Data Aggregation]
        |
        +-----> [Career Completion Analysis] -----> [Final Grade Correlation]
        |
        +-----> [Long-term Trend Analysis] -----> [Strategy Evolution]
        |
        +-----> [Success Factor Identification] -----> [Key Performance Indicators]
        |
        v
[Insight Generation]
        |
        +-----> [Recommendation Updates] -----> [Algorithm Refinement]
        |
        +-----> [User Feedback Integration] -----> [Preference Learning]
        |
        +-----> [Predictive Model Training] -----> [Machine Learning Updates]
        |
        v
[Report Generation] -----> [Dashboard Updates]
        |
        v
[Notification System] -----> [User Alerts]
```

### Mermaid Diagram

```mermaid
flowchart TD
    CareerEventTrigger([Career Event Trigger]) --> EventClassification[Event Classification]
    EventClassification --> EventTypeRouting[Event Type Routing]
    
    EventTypeRouting --> TrainingSession[Training Session]
    EventTypeRouting --> RaceCompletion[Race Completion]
    EventTypeRouting --> SkillAcquisition[Skill Acquisition]
    EventTypeRouting --> TurnProgression[Turn Progression]
    
    TrainingSession --> TrainingDataCapture[Training Data Capture]
    TrainingDataCapture --> PredictedVsActual[Predicted vs Actual Results]
    TrainingDataCapture --> SupportCardParticipation[Support Card Participation]
    TrainingDataCapture --> SkillHintsAcquired[Skill Hints Acquired]
    TrainingDataCapture --> EnergyMoodChanges[Energy/Mood Changes]
    
    RaceCompletion --> RaceDataCapture[Race Data Capture]
    RaceDataCapture --> FinalPositionTime[Final Position/Time]
    RaceDataCapture --> StrategyEffectiveness[Strategy Effectiveness]
    RaceDataCapture --> StatAdequacyAnalysis[Stat Adequacy Analysis]
    RaceDataCapture --> FanSPGains[Fan/SP Gains]
    
    SkillAcquisition --> SkillDataCapture[Skill Data Capture]
    SkillDataCapture --> SPCostPaid[SP Cost Paid]
    SkillDataCapture --> HintsUsed[Hints Used]
    SkillDataCapture --> EvolutionTracking[Evolution Tracking]
    
    TurnProgression --> TurnDataCapture[Turn Data Capture]
    TurnDataCapture --> CharacterStateSnapshot[Character State Snapshot]
    TurnDataCapture --> GoalProgressUpdate[Goal Progress Update]
    TurnDataCapture --> PhaseTransitionTracking[Phase Transition Tracking]
    
    PredictedVsActual --> DataValidationStorage[Data Validation and Storage]
    SupportCardParticipation --> DataValidationStorage
    SkillHintsAcquired --> DataValidationStorage
    EnergyMoodChanges --> DataValidationStorage
    FinalPositionTime --> DataValidationStorage
    StrategyEffectiveness --> DataValidationStorage
    StatAdequacyAnalysis --> DataValidationStorage
    FanSPGains --> DataValidationStorage
    SPCostPaid --> DataValidationStorage
    HintsUsed --> DataValidationStorage
    EvolutionTracking --> DataValidationStorage
    CharacterStateSnapshot --> DataValidationStorage
    GoalProgressUpdate --> DataValidationStorage
    PhaseTransitionTracking --> DataValidationStorage
    
    DataValidationStorage --> DataIntegrityCheck[Data Integrity Check<br/>Constraint Validation]
    DataValidationStorage --> DuplicateDetection[Duplicate Detection<br/>Merge Resolution]
    DataValidationStorage --> DatabaseTransaction[Database Transaction<br/>ACID Compliance]
    
    DataIntegrityCheck --> RealtimeAnalytics[Real-time Analytics Processing]
    DuplicateDetection --> RealtimeAnalytics
    DatabaseTransaction --> RealtimeAnalytics
    
    RealtimeAnalytics --> PerformanceMetrics[Performance Metrics Calculation]
    RealtimeAnalytics --> PatternRecognition[Pattern Recognition Analysis]
    RealtimeAnalytics --> ComparativeAnalysis[Comparative Analysis]
    
    PerformanceMetrics --> TrainingEfficiency[Training Efficiency<br/>Stat Gains per Turn]
    PerformanceMetrics --> PredictionAccuracy[Prediction Accuracy<br/>Expected vs Actual]
    PerformanceMetrics --> GoalProgressRate[Goal Progress Rate<br/>Completion Trajectory]
    PerformanceMetrics --> ResourceUtilization[Resource Utilization<br/>SP/Energy Efficiency]
    
    PatternRecognition --> SuccessfulDecisions[Successful Decision Sequences<br/>Strategy Patterns]
    PatternRecognition --> FailurePointID[Failure Point Identification<br/>Risk Patterns]
    PatternRecognition --> OptimalTiming[Optimal Timing Detection<br/>Phase Strategies]
    PatternRecognition --> SupportCardSynergies[Support Card Synergies<br/>Deck Effectiveness]
    
    ComparativeAnalysis --> MultiCareerComparison[Multi-Career Comparison<br/>Character Performance]
    ComparativeAnalysis --> MetaStrategyTracking[Meta Strategy Tracking<br/>Community Benchmarks]
    ComparativeAnalysis --> ImprovementID[Improvement Identification<br/>Optimization Opportunities]
    
    TrainingEfficiency --> HistoricalDataAgg[Historical Data Aggregation]
    PredictionAccuracy --> HistoricalDataAgg
    GoalProgressRate --> HistoricalDataAgg
    ResourceUtilization --> HistoricalDataAgg
    SuccessfulDecisions --> HistoricalDataAgg
    FailurePointID --> HistoricalDataAgg
    OptimalTiming --> HistoricalDataAgg
    SupportCardSynergies --> HistoricalDataAgg
    MultiCareerComparison --> HistoricalDataAgg
    MetaStrategyTracking --> HistoricalDataAgg
    ImprovementID --> HistoricalDataAgg
    
    HistoricalDataAgg --> CareerCompletionAnalysis[Career Completion Analysis<br/>Final Grade Correlation]
    HistoricalDataAgg --> LongTermTrends[Long-term Trend Analysis<br/>Strategy Evolution]
    HistoricalDataAgg --> SuccessFactorID[Success Factor Identification<br/>Key Performance Indicators]
    
    CareerCompletionAnalysis --> InsightGeneration[Insight Generation]
    LongTermTrends --> InsightGeneration
    SuccessFactorID --> InsightGeneration
    
    InsightGeneration --> RecommendationUpdates[Recommendation Updates<br/>Algorithm Refinement]
    InsightGeneration --> UserFeedbackIntegration[User Feedback Integration<br/>Preference Learning]
    InsightGeneration --> PredictiveModelTraining[Predictive Model Training<br/>Machine Learning Updates]
    
    RecommendationUpdates --> ReportGeneration[Report Generation]
    UserFeedbackIntegration --> ReportGeneration
    PredictiveModelTraining --> ReportGeneration
    
    ReportGeneration --> DashboardUpdates[Dashboard Updates]
    DashboardUpdates --> NotificationSystem[Notification System]
    NotificationSystem --> UserAlerts[User Alerts]
```

## 5. Error Handling and Recovery Process Flow

### Text Description

The comprehensive error handling system that manages failures, implements recovery strategies, maintains system stability, and ensures data integrity across all system components.

### ASCII Diagram

```
[System Operation] -----> [Error Detection]
        |
        v
[Error Classification]
        |
        +-----> [API Connection Error] -----> [Network Failure Handling]
        |                                          |
        |                                          +-----> [Retry Logic] -----> [Exponential Backoff]
        |                                          +-----> [Fallback to Cache] -----> [Offline Mode]
        |                                          +-----> [User Notification] -----> [Status Update]
        |
        +-----> [Database Error] -----> [Data Integrity Protection]
        |                                    |
        |                                    +-----> [Transaction Rollback] -----> [Consistency Check]
        |                                    +-----> [Backup Restoration] -----> [Data Recovery]
        |                                    +-----> [Connection Pool Reset] -----> [Reconnection]
        |
        +-----> [AI Model Error] -----> [Model Fallback Handling]
        |                                    |
        |                                    +-----> [Ollama Failure] -----> [AWS Fallback]
        |                                    +-----> [AWS Rate Limit] -----> [Request Queuing]
        |                                    +-----> [Model Timeout] -----> [Response Caching]
        |
        +-----> [OCR Processing Error] -----> [Image Analysis Recovery]
        |                                          |
        |                                          +-----> [Image Quality Check] -----> [Enhancement]
        |                                          +-----> [Alternative OCR Engine] -----> [Backup Processing]
        |                                          +-----> [Manual Input Fallback] -----> [User Assistance]
        |
        +-----> [Calculation Error] -----> [Computation Recovery]
        |                                       |
        |                                       +-----> [Input Validation] -----> [Sanitization]
        |                                       +-----> [Algorithm Fallback] -----> [Simplified Calculation]
        |                                       +-----> [Default Values] -----> [Safe Defaults]
        |
        v
[Recovery Strategy Selection]
        |
        +-----> [Immediate Recovery] -----> [Automatic Retry]
        |                                        |
        |                                        +-----> [Success] -----> [Resume Operation]
        |                                        +-----> [Failure] -----> [Escalate to Manual]
        |
        +-----> [Graceful Degradation] -----> [Reduced Functionality]
        |                                           |
        |                                           +-----> [Core Features Only] -----> [Essential Operations]
        |                                           +-----> [Cached Data Usage] -----> [Limited Updates]
        |                                           +-----> [User Notification] -----> [Status Explanation]
        |
        +-----> [System Restart] -----> [Component Isolation]
        |                                     |
        |                                     +-----> [Service Restart] -----> [Health Check]
        |                                     +-----> [Cache Clearing] -----> [Fresh Start]
        |                                     +-----> [Configuration Reset] -----> [Default Settings]
        |
        v
[Error Logging and Analysis]
        |
        +-----> [Error Details Capture] -----> [Stack Trace/Context]
        |
        +-----> [Frequency Analysis] -----> [Pattern Detection]
        |
        +-----> [Impact Assessment] -----> [Severity Classification]
        |
        +-----> [Root Cause Analysis] -----> [Prevention Strategies]
        |
        v
[System Health Monitoring]
        |
        +-----> [Performance Metrics] -----> [Response Time Tracking]
        |
        +-----> [Resource Usage] -----> [Memory/CPU Monitoring]
        |
        +-----> [Error Rate Tracking] -----> [Trend Analysis]
        |
        +-----> [User Impact Assessment] -----> [Experience Metrics]
        |
        v
[Preventive Measures Implementation]
        |
        +-----> [Code Improvements] -----> [Bug Fixes]
        |
        +-----> [Infrastructure Upgrades] -----> [Capacity Planning]
        |
        +-----> [Monitoring Enhancements] -----> [Early Warning Systems]
        |
        +-----> [Documentation Updates] -----> [Troubleshooting Guides]
        |
        v
[Recovery Verification] -----> [System Stability Check]
        |
        v
[User Communication] -----> [Status Updates]
```

### Mermaid Diagram

```mermaid
flowchart TD
    SystemOperation([System Operation]) --> ErrorDetection[Error Detection]
    ErrorDetection --> ErrorClassification[Error Classification]
    
    ErrorClassification --> APIConnectionError[API Connection Error]
    ErrorClassification --> DatabaseError[Database Error]
    ErrorClassification --> AIModelError[AI Model Error]
    ErrorClassification --> OCRProcessingError[OCR Processing Error]
    ErrorClassification --> CalculationError[Calculation Error]
    
    APIConnectionError --> NetworkFailureHandling[Network Failure Handling]
    NetworkFailureHandling --> RetryLogic[Retry Logic<br/>Exponential Backoff]
    NetworkFailureHandling --> FallbackToCache[Fallback to Cache<br/>Offline Mode]
    NetworkFailureHandling --> UserNotification1[User Notification<br/>Status Update]
    
    DatabaseError --> DataIntegrityProtection[Data Integrity Protection]
    DataIntegrityProtection --> TransactionRollback[Transaction Rollback<br/>Consistency Check]
    DataIntegrityProtection --> BackupRestoration[Backup Restoration<br/>Data Recovery]
    DataIntegrityProtection --> ConnectionPoolReset[Connection Pool Reset<br/>Reconnection]
    
    AIModelError --> ModelFallbackHandling[Model Fallback Handling]
    ModelFallbackHandling --> OllamaFailure[Ollama Failure<br/>AWS Fallback]
    ModelFallbackHandling --> AWSRateLimit[AWS Rate Limit<br/>Request Queuing]
    ModelFallbackHandling --> ModelTimeout[Model Timeout<br/>Response Caching]
    
    OCRProcessingError --> ImageAnalysisRecovery[Image Analysis Recovery]
    ImageAnalysisRecovery --> ImageQualityCheck[Image Quality Check<br/>Enhancement]
    ImageAnalysisRecovery --> AlternativeOCR[Alternative OCR Engine<br/>Backup Processing]
    ImageAnalysisRecovery --> ManualInputFallback[Manual Input Fallback<br/>User Assistance]
    
    CalculationError --> ComputationRecovery[Computation Recovery]
    ComputationRecovery --> InputValidation[Input Validation<br/>Sanitization]
    ComputationRecovery --> AlgorithmFallback[Algorithm Fallback<br/>Simplified Calculation]
    ComputationRecovery --> DefaultValues[Default Values<br/>Safe Defaults]
    
    RetryLogic --> RecoveryStrategySelection[Recovery Strategy Selection]
    FallbackToCache --> RecoveryStrategySelection
    UserNotification1 --> RecoveryStrategySelection
    TransactionRollback --> RecoveryStrategySelection
    BackupRestoration --> RecoveryStrategySelection
    ConnectionPoolReset --> RecoveryStrategySelection
    OllamaFailure --> RecoveryStrategySelection
    AWSRateLimit --> RecoveryStrategySelection
    ModelTimeout --> RecoveryStrategySelection
    ImageQualityCheck --> RecoveryStrategySelection
    AlternativeOCR --> RecoveryStrategySelection
    ManualInputFallback --> RecoveryStrategySelection
    InputValidation --> RecoveryStrategySelection
    AlgorithmFallback --> RecoveryStrategySelection
    DefaultValues --> RecoveryStrategySelection
    
    RecoveryStrategySelection --> ImmediateRecovery[Immediate Recovery]
    RecoveryStrategySelection --> GracefulDegradation[Graceful Degradation]
    RecoveryStrategySelection --> SystemRestart[System Restart]
    
    ImmediateRecovery --> AutomaticRetry[Automatic Retry]
    AutomaticRetry --> RetrySuccess[Success<br/>Resume Operation]
    AutomaticRetry --> RetryFailure[Failure<br/>Escalate to Manual]
    
    GracefulDegradation --> ReducedFunctionality[Reduced Functionality]
    ReducedFunctionality --> CoreFeaturesOnly[Core Features Only<br/>Essential Operations]
    ReducedFunctionality --> CachedDataUsage[Cached Data Usage<br/>Limited Updates]
    ReducedFunctionality --> UserNotification2[User Notification<br/>Status Explanation]
    
    SystemRestart --> ComponentIsolation[Component Isolation]
    ComponentIsolation --> ServiceRestart[Service Restart<br/>Health Check]
    ComponentIsolation --> CacheClearing[Cache Clearing<br/>Fresh Start]
    ComponentIsolation --> ConfigurationReset[Configuration Reset<br/>Default Settings]
    
    RetrySuccess --> ErrorLoggingAnalysis[Error Logging and Analysis]
    RetryFailure --> ErrorLoggingAnalysis
    CoreFeaturesOnly --> ErrorLoggingAnalysis
    CachedDataUsage --> ErrorLoggingAnalysis
    UserNotification2 --> ErrorLoggingAnalysis
    ServiceRestart --> ErrorLoggingAnalysis
    CacheClearing --> ErrorLoggingAnalysis
    ConfigurationReset --> ErrorLoggingAnalysis
    
    ErrorLoggingAnalysis --> ErrorDetailsCapture[Error Details Capture<br/>Stack Trace/Context]
    ErrorLoggingAnalysis --> FrequencyAnalysis[Frequency Analysis<br/>Pattern Detection]
    ErrorLoggingAnalysis --> ImpactAssessment[Impact Assessment<br/>Severity Classification]
    ErrorLoggingAnalysis --> RootCauseAnalysis[Root Cause Analysis<br/>Prevention Strategies]
    
    ErrorDetailsCapture --> SystemHealthMonitoring[System Health Monitoring]
    FrequencyAnalysis --> SystemHealthMonitoring
    ImpactAssessment --> SystemHealthMonitoring
    RootCauseAnalysis --> SystemHealthMonitoring
    
    SystemHealthMonitoring --> PerformanceMetrics[Performance Metrics<br/>Response Time Tracking]
    SystemHealthMonitoring --> ResourceUsage[Resource Usage<br/>Memory/CPU Monitoring]
    SystemHealthMonitoring --> ErrorRateTracking[Error Rate Tracking<br/>Trend Analysis]
    SystemHealthMonitoring --> UserImpactAssessment[User Impact Assessment<br/>Experience Metrics]
    
    PerformanceMetrics --> PreventiveMeasures[Preventive Measures Implementation]
    ResourceUsage --> PreventiveMeasures
    ErrorRateTracking --> PreventiveMeasures
    UserImpactAssessment --> PreventiveMeasures
    
    PreventiveMeasures --> CodeImprovements[Code Improvements<br/>Bug Fixes]
    PreventiveMeasures --> InfrastructureUpgrades[Infrastructure Upgrades<br/>Capacity Planning]
    PreventiveMeasures --> MonitoringEnhancements[Monitoring Enhancements<br/>Early Warning Systems]
    PreventiveMeasures --> DocumentationUpdates[Documentation Updates<br/>Troubleshooting Guides]
    
    CodeImprovements --> RecoveryVerification[Recovery Verification]
    InfrastructureUpgrades --> RecoveryVerification
    MonitoringEnhancements --> RecoveryVerification
    DocumentationUpdates --> RecoveryVerification
    
    RecoveryVerification --> SystemStabilityCheck[System Stability Check]
    SystemStabilityCheck --> UserCommunication[User Communication]
    UserCommunication --> StatusUpdates[Status Updates]
```

## System Process Flow Summary

### Key System Processes

1. **External Data Integration**: Manages API connections, data synchronization, and fallback mechanisms
2. **Training Optimization Engine**: Core algorithm processing with multi-factor analysis and recommendation generation
3. **AI Model Selection**: Intelligent routing between local Ollama and cloud AWS models with quality assessment
4. **Career Data Management**: Comprehensive tracking, analytics, and pattern recognition for continuous improvement
5. **Error Handling and Recovery**: Robust failure management with graceful degradation and automatic recovery

### Critical System Features

- **Fault Tolerance**: Multiple fallback mechanisms ensure system availability
- **Performance Optimization**: Intelligent caching and resource management
- **Data Integrity**: ACID compliance and transaction management
- **Scalability**: Modular architecture supporting growth and enhancement
- **Learning Capability**: Continuous improvement through analytics and pattern recognition

### Integration Architecture

- **API Management**: Centralized external data integration with health monitoring
- **AI Orchestration**: Seamless model switching with performance optimization
- **Data Pipeline**: Real-time processing with historical analysis
- **Error Recovery**: Comprehensive failure handling with user communication
- **Performance Monitoring**: Continuous system health assessment and optimization

These system process flow diagrams provide detailed insight into the internal workings of the Umamusume career planner, ensuring robust, scalable, and maintainable system architecture.
