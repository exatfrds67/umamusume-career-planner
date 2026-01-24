# FLOW-001: Character Management System Flow

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.1.0
**Date**: January 24, 2026
**Project**: UmamusumeCareerPlanner
**Author**: Development Team
**Status**: Current - Aligned with codebase v2.0.0

---

## 1. Character Creation Wizard Flow

This flow details the initialization process for a new character, utilizing the **Laravel 12** backend services and **Livewire 3** wizard components. It integrates `CharacterService` for persistence and `FactorInheritanceService` for stat calculations.

### 1.1 Diagram

```mermaid
flowchart TD
    Start([User Starts New Career]) --> SelectTrainee[Select Trainee]
    SelectTrainee --> LoadBaseStats[Load Base Stats & Growth Rates]
    LoadBaseStats --> SelectScenario[Select Scenario]
    
    SelectScenario --> SelectParents[Select Parents (Inheritance)]
    SelectParents --> CalcInheritance[Calculate Factor Bonuses]
    CalcInheritance --> PreviewInheritance[Display Projected Gains]
    
    PreviewInheritance --> BuildDeck[Build Support Deck]
    BuildDeck --> ValidateDeck{Deck Valid?}
    
    ValidateDeck -->|No| FixDeck[Fix: 5 Owned + 1 Borrowed]
    FixDeck --> ValidateDeck
    
    ValidateDeck -->|Yes| SetGoals[AI-Assisted Goal Setting]
    SetGoals --> FinalReview[Review Configuration]
    
    FinalReview --> CreateRecord[Create Character Record]
    CreateRecord --> InitState[Initialize State]
    
    InitState --> DB{Storage Mode?}
    DB -->|Local| LocalStore[Save to localStorage (UUID)]
    DB -->|Account| CloudStore[Save to MySQL (ucp_characters)]
    
    LocalStore --> Dashboard[Redirect to Dashboard]
    CloudStore --> Dashboard
```

### 1.2 Step Details

| Step | Service/Component | Description |
|------|-------------------|-------------|
| **Select Trainee** | `CharacterService` | Fetches base stats (0-1200) and growth rates from `ucp_game_data`. |
| **Calculate Inheritance** | `FactorInheritanceService` | Applies factor bonuses: ★ (+5), ★★ (+12), ★★★ (+21). |
| **Validate Deck** | `SupportDeckService` | Ensures exactly 6 cards with type balance checks. |
| **Initialize State** | `CareerRunService` | Sets Energy (100), Mood (Normal), Turn (1). |

---

## 2. Turn Progression & State Update Flow

The core loop for updating character state after training or events, triggering **Neuron AI Agents** for analysis.

### 2.1 Diagram

```mermaid
flowchart TD
    StartTurn([Begin Turn]) --> LoadContext[Load Character Context]
    LoadContext --> CheckPhase{Phase?}
    
    CheckPhase -->|Training| TrainingPhase[Training Phase]
    CheckPhase -->|Race Week| RacePhase[Race Preparation]
    
    TrainingPhase --> UserAction[User Selects Training/Rest]
    UserAction --> CalcOutcome[Calculate Outcome]
    
    CalcOutcome --> UpdateStats[Update Stats (Speed/Stamina/etc)]
    CalcOutcome --> UpdateEnergy[Update Energy (-Cost / +Rest)]
    
    RacePhase --> ExecuteRace[Simulate/Record Race]
    ExecuteRace --> UpdateResults[Update Fan Count & Skill Pts]
    
    UpdateStats --> CheckConditions[Check Conditions/Events]
    UpdateResults --> CheckConditions
    
    CheckConditions --> AutoSave[Auto-Save State]
    
    AutoSave --> AICheck{AI Enabled?}
    AICheck -->|Yes| TriggerAgent[Trigger Neuron Agent]
    AICheck -->|No| NextTurn
    
    TriggerAgent --> GenAdvice[Generate Turn Advice]
    GenAdvice --> NextTurn([Advance Turn Counter])
```

---

## 3. Stat Grade Calculation Flow

Logic for determining stat grades based on the v2.0.0 scaling (0-1200 hard cap).

### 3.1 Diagram

```mermaid
flowchart TD
    Start([Compute Grade]) --> GetRaw[Get Raw Stat Value]
    GetRaw --> ClampValue[Clamp 0-1200]
    
    ClampValue --> DetermineGrade{Grade Threshold}
    
    DetermineGrade -->|>=1100| SS[SS Grade]
    DetermineGrade -->|950-1099| S[S Grade]
    DetermineGrade -->|850-949| A[A Grade]
    DetermineGrade -->|750-849| BPlus[B+ Grade]
    DetermineGrade -->|650-749| B[B Grade]
    DetermineGrade -->|550-649| CPlus[C+ Grade]
    DetermineGrade -->|450-549| C[C Grade]
    DetermineGrade -->|350-449| DPlus[D+ Grade]
    DetermineGrade -->|250-349| D[D Grade]
    DetermineGrade -->|150-249| E[E Grade]
    DetermineGrade -->|0-149| F[F Grade]
    
    SS --> UI[Update UI Badge]
    F --> UI
```

---

## 4. Factor Inheritance Calculation Flow

Detailed logic for `FactorInheritanceService` processing parent and grandparent factors.

### 4.1 Diagram

```mermaid
flowchart TD
    Start([Calculate Inheritance]) --> LoadParents[Load 2 Parents + 4 Grandparents]
    
    LoadParents --> IterateFactors[Iterate Blue/Red/Green/White Factors]
    
    IterateFactors --> CheckCompatibility[Check Trainee Compatibility]
    CheckCompatibility --> SumStars[Sum Star Ratings]
    
    SumStars --> ApplyBase[Apply Base Stat Bonus]
    ApplyBase --> ScenarioCheck{Scenario Bonus?}
    
    ScenarioCheck -->|URA| BonusURA[Apply URA Bonus]
    ScenarioCheck -->|Unity| BonusUnity[Apply Unity Bonus]
    
    BonusURA --> Finalize[Finalize Initial Stats]
    BonusUnity --> Finalize
    
    Finalize --> SaveFactors[Persist to ucp_factors]
```

---

## 5. Goal Progress Tracking Flow

How the system tracks user-defined goals and triggers alerts.

### 5.1 Diagram

```mermaid
flowchart TD
    Start([Track Goals]) --> LoadGoals[Load Active Goals (JSON)]
    LoadGoals --> GetCurrentState[Get Current Stats/Results]
    
    GetCurrentState --> IterateGoals{Iterate Goals}
    
    IterateGoals -->|Stat Target| CheckStat[Compare Stat vs Target]
    IterateGoals -->|Race Result| CheckPlacement[Check Race Placement]
    IterateGoals -->|Skill| CheckSkill[Check Skill Inventory]
    
    CheckStat --> CalcProgress[% Progress]
    CheckPlacement --> CalcProgress
    CheckSkill --> CalcProgress
    
    CalcProgress --> StatusCheck{Status?}
    
    StatusCheck -->|Completed| MarkDone[Mark Complete ✅]
    StatusCheck -->|On Track| MarkGood[Mark On Track 🟡]
    StatusCheck -->|Behind| MarkRisk[Mark At Risk 🔴]
    
    MarkRisk --> AIAlert[Trigger AI Warning]
    
    MarkDone --> UpdateUI[Update Goal Component]
    MarkGood --> UpdateUI
    AIAlert --> UpdateUI
```

---

## 6. Race Readiness Calculation Flow

The algorithm used by `RaceService` to determine the Readiness Score (Excellent/Good/Fair/Poor).

### 6.1 Diagram

```mermaid
flowchart TD
    Start([Compute Readiness]) --> Inputs[Gather Inputs]
    
    Inputs --> Step1[Stat Requirements Check]
    Inputs --> Step2[Aptitude Modifiers]
    Inputs --> Step3[Skill Synergy]
    Inputs --> Step4[Condition/Mood]
    
    Step1 --> Score[Base Score]
    
    Step2 -->|Distance/Surface| ModifyScore[Apply Multiplier]
    ModifyScore --> Score
    
    Step3 -->|Active Skills| AddBonus[Add Skill Bonus]
    AddBonus --> Score
    
    Step4 -->|Mood Effect| ApplyMood[Apply Mood Modifier]
    ApplyMood --> Score
    
    Score --> Classify{Classify Total}
    
    Classify -->|>=85%| Excellent[Excellent 🟢]
    Classify -->|70-84%| Good[Good 🟡]
    Classify -->|55-69%| Fair[Fair 🟠]
    Classify -->|<55%| Poor[Poor 🔴]
    
    Poor --> Return[Return Readiness Object]
```

---

## 7. Character State & Condition Management Flow

Logic for managing energy, mood, and status conditions (e.g., "Overweight", "Lazy").

### 7.1 Diagram

```mermaid
flowchart TD
    Start([Monitor State]) --> CheckEnergy[Check Energy Level]
    
    CheckEnergy --> EnergyLow{Energy < 30?}
    EnergyLow -->|Yes| FlagRest[Flag: High Failure Risk]
    
    EnergyLow -->|No| CheckMood[Check Mood Status]
    CheckMood --> MoodLow{Mood < Normal?}
    MoodLow -->|Yes| FlagMood[Flag: Reduced Training Gains]
    
    CheckMood --> CheckConditions[Check Conditions (JSON)]
    
    CheckConditions --> HasBad{Negative Condition?}
    HasBad -->|Yes| Identify[Identify: Skinny/Overweight/Lazy]
    Identify --> FlagCondition[Flag: Specific Debuff]
    
    FlagRest --> AggregateRisks
    FlagMood --> AggregateRisks
    FlagCondition --> AggregateRisks
    
    AggregateRisks --> AIInput[Send to Training Advisor Agent]
    AIInput --> SuggestAction[Suggest Infirmary/Rest/Date]
    SuggestAction --> UpdateDashboard[Update Dashboard Indicators]
```

---

## Document Control

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 2.1.0 | 2026-01-24 | Development Team | Updated to align with v2.0.0 codebase, Laravel 12 architecture, and Neuron AI integration points |
| 1.0.0 | 2026-01-14 | Development Team | Initial flow definitions |

---

## Related Documents

- [PRD-001: Character Management](../prds/PRD-001_Character_Management.md)
- [SPEC-001: Character Management Technical](../specs/SPEC-001_Character_Management_Technical.md)
- [009_DBD: Database Documentation](../009_DBD_Database_Documentation.md)
- [017_SUM: Software User Manual](../017_SUM_Software_User_Manual.md)
