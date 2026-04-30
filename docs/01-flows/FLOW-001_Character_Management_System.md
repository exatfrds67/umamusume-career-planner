# FLOW-001: Character Management System Flow

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.4.2
**Date**: April 7, 2026
**Project**: UmamusumeCareerPlanner
**Author**: Development Team
**Status**: Current - Updated with verified game mechanics from Global English Server

---

## 1. Character Creation Wizard Flow

This flow details the initialization process for a new character, utilizing the **Laravel 12**
backend services, Blade-driven pages, lightweight Livewire components, and browser-side local
storage helpers. It integrates `CharacterController` and `CharacterStateService` for persistence and
`FactorService` for inheritance-related calculations.

Character setup and subsequent state updates must preserve dual storage behavior. Local mode uses
browser-managed UUID-oriented payloads, while Account mode persists character and career records for
the authenticated owner.

### 1.1 Diagram

```mermaid
flowchart TD
    Start([User Starts New Character Setup]) --> SelectTrainee[Select Trainee]
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

    FinalReview --> CreatePayload[Build Character/Career Setup Payload]
    CreatePayload --> InitState[Initialize State]

    InitState --> DB{Storage Mode?}
    DB -->|Local| LocalStore[Save to localStorage (UUID)]
    DB -->|Account| AuthCheck{Authenticated?}

    AuthCheck -->|No| AuthError[Redirect to login or remain local]
    AuthCheck -->|Yes| CloudStore[Persist Account-Mode Character and Career State]
    CloudStore --> PersistOk{Persisted?}
    PersistOk -->|No| PersistError[Return validation or persistence error]
    PersistOk -->|Yes| Dashboard[Redirect to Dashboard]

    LocalStore --> Dashboard
```

### 1.2 Step Details

| Step | Service/Component | Description |
| --- | --- | --- |
| **Select Trainee** | `CharacterController` | Loads base character data, starting stats, and growth rates from the application dataset. |
| **Calculate Inheritance** | `FactorService` | Applies inheritance and factor-related calculations used during setup. |
| **Validate Deck** | `SupportDeckService` | Ensures exactly 6 cards with type balance checks. |
| **Initialize State** | Character and career initialization services + storage helpers | Initializes Energy (100), Mood (Normal), Turn (1), then persists state according to the active `StorageMode`. For Local mode, a unique UUID is generated before the first browser-side write. |

---

## 2. Turn Progression & State Update Flow

The core loop for updating character state after training or events, triggering **Neuron AI Agents** for analysis.

The turn loop must preserve dual storage behavior. In Local mode, state updates are written to
browser storage using the run UUID. In Account mode, updates are persisted to database-backed career
state for the authenticated owner.

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

    AutoSave --> StorageMode{Storage Mode?}
    StorageMode -->|Local| SaveLocal[Persist State to localStorage by UUID]
    StorageMode -->|Account| SaveAccount[Persist State to Database for Authenticated User]

    SaveLocal --> AICheck{AI Enabled?}
    SaveAccount --> AICheck
    AICheck -->|Yes| TriggerAgent[Trigger Neuron Agent]
    AICheck -->|No| NextTurn

    TriggerAgent --> GenAdvice[Generate Turn Advice]
    GenAdvice --> NextTurn([Advance Turn Counter])
```

---

## 3. Stat Grade Calculation Flow

Logic for determining stat grades based on the v2.0.0 scaling. Stats can exceed 1200 with
diminishing returns; maximum aptitude grade is S.

### 3.1 Diagram

```mermaid
flowchart TD
    Start([Compute Grade]) --> GetRaw[Get Raw Stat Value]
    GetRaw --> CheckCap{Value > 1200?}

    CheckCap -->|Yes| ApplyDiminishing[Apply Diminishing Returns]
    CheckCap -->|No| UseRaw[Use Raw Value]

    ApplyDiminishing --> DetermineGrade{Grade Threshold}
    UseRaw --> DetermineGrade

    DetermineGrade -->|>=1100| S[S Grade - Maximum]
    DetermineGrade -->|950-1099| A[A Grade]
    DetermineGrade -->|850-949| BPlus[B+ Grade]
    DetermineGrade -->|750-849| B[B Grade]
    DetermineGrade -->|650-749| CPlus[C+ Grade]
    DetermineGrade -->|550-649| C[C Grade]
    DetermineGrade -->|450-549| DPlus[D+ Grade]
    DetermineGrade -->|350-449| D[D Grade]
    DetermineGrade -->|250-349| E[E Grade]
    DetermineGrade -->|150-249| F[F Grade]
    DetermineGrade -->|0-149| G[G Grade]

    S --> UI[Update UI Badge]
    G --> UI
```

### 3.2 Stat Cap Notes

- **Base Cap**: 1200 points per stat
- **Overflow**: Stats can exceed 1200 through training, but gains above 1200 have diminishing returns (half value)
- **Per-Training Cap**: +100 per training session (reduced to +50 if stat > 1200)
- **Aptitude Maximum**: S grade is the highest aptitude rating (no SS grade exists in game)

### 3.3 Aptitude Grade Modifiers (Game-Accurate)

Aptitude grades affect performance differently by category. A-rank is the baseline (0% modifier).

| Grade | Surface (Power) | Distance (Speed) | Style (Wit) |
| --- | --- | --- | --- |
| S | +5% | +5% | +10% |
| A | 0% (baseline) | 0% (baseline) | 0% (baseline) |
| B | -10% | -10% | -15% |
| C | -20% | -20% | -25% |
| D | -30% | -40% | -40% |
| E | -50% | -60% | -60% |
| F | -70% | -80% | -80% |
| G | -90% | -90% | -90% |

**Grade Scale**: G → F → E → D → C → B → A → S (maximum)

> **Column Note**: Column headings show the primary stat affected during the race. Surface aptitude affects Power; Distance aptitude affects Speed; Style aptitude affects Wit.

---

## 4. Factor Inheritance Calculation Flow

Detailed logic for `FactorService` processing parent and grandparent factors.

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

### 4.2 Factor Colour Legend

| Colour | Meaning |
| --- | --- |
| **Blue** | Stat bonus (directly adds to the inheriting character's base stats) |
| **Red** | Skill inheritance (passes skills from parent to the inheriting character) |
| **Green** | Growth rate bonus (increases the inheriting character's stat growth rates) |
| **White** | SP bonus (grants additional Skill Points to the inheriting character) |

---

## 5. Goal Progress Tracking Flow

How the system tracks user-defined goals and triggers alerts.

Goal progress is computed the same way in both modes, but persistence differs: Local mode stores
goal state in browser-managed run payloads; Account mode stores goal state in database-backed career
records.

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

The algorithm used by `RaceConditionService` to determine the readiness score (Excellent/Good/Fair/Poor).

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
| --- | --- | --- | --- |
| 2.4.2 | 2026-04-07 | Development Team | Synchronized suite-wide documentation metadata and preserved verified flow mechanics and references. |
| 2.3.0 | 2026-03-10 | Development Team | Added aptitude column-to-stat footnote (Surface → Power, Distance → Speed, Style → Wit); added factor colour legend (Blue/Red/Green/White). |
| 2.2.2 | 2026-03-08 | Development Team | Clarified that Local mode initialization generates a UUID before the first browser-side write. |
| 2.2.1 | 2026-03-08 | Development Team | Added StorageMode-aware setup, auto-save, and goal-persistence notes so Local UUID runs and authenticated Account-mode careers are modeled consistently across the flow. |
| 2.2.0 | 2026-01-28 | Development Team | Updated with verified game mechanics from Global English Server: Removed SS grade (max is S), stats can exceed 1200 with diminishing returns (half value above 1200), per-training cap (+100/+50), added complete aptitude modifier tables by category (Surface/Distance/Style), G grade tier added |
| 2.1.0 | 2026-01-24 | Development Team | Updated to align with v2.0.0 codebase, Laravel 12 architecture, and Neuron AI integration points |
| 1.0.0 | 2026-01-14 | Development Team | Initial flow definitions |

---

## Related Documents

- [PRD-001: Character Management](../02-prds/PRD-001_Character_Management.md)
- [SPEC-001: Character Management Technical](../02-specs/SPEC-001_Character_Management_Technical.md)
- [009_DBD: Database Documentation](../00-core-docs/009_DBD_Database_Documentation.md)
- [017_SUM: Software User Manual](../00-core-docs/017_SUM_Software_User_Manual.md)
