# FLOW-001: Character Management System Flow

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0
**Date**: January 14, 2026
**Related Documents**: [PRD-001], [SPEC-001]

---

## 1. Character Creation Flow

```mermaid
flowchart TD
    Start([User Starts New Career]) --> SelectTrainee[Select Trainee]
    SelectTrainee --> PickScenario[Pick Scenario]
    PickScenario --> PickParents[Pick Parents A/B]
    PickParents --> PreviewFactors[Preview Factor Bonuses]
    PreviewFactors --> BuildDeck[Build Support Deck (6 slots)]
    BuildDeck --> ValidateDeck{Deck Valid?}
    ValidateDeck -->|No| FixDeck[Fix type/rarity issues]
    FixDeck --> ValidateDeck
    ValidateDeck -->|Yes| InitializeStats[Initialize Stats/Mood/Energy]
    InitializeStats --> SaveRun[Persist Run + Seed]
    SaveRun --> Dashboard[Show Dashboard Day 1]
```

---

## 2. Turn Progression Flow

```mermaid
flowchart TD
    StartTurn([Begin Turn]) --> CheckPhase{Phase?}
    CheckPhase -->|Training| TrainingActions[Training Actions Allowed]
    CheckPhase -->|Race Week| RacePrep[Race Preparation]
    TrainingActions --> ProcessEvents[Process Story/Random Events]
    ProcessEvents --> UpdateState[Update Stats/Mood/Condition]
    UpdateState --> TurnEnd[Advance Turn]
    RacePrep --> RaceDay[Execute Race]
    RaceDay --> UpdateState
    UpdateState --> TurnEnd
    TurnEnd --> NextTurn([Next Turn])
```

---

## 3. Stat Grade Calculation Flow

```mermaid
flowchart TD
    Start([Compute Grade]) --> GetValue[Get Stat Value 0-1200+]
    GetValue --> ApplyBoosts[Apply Scenario/Item Boosts]
    ApplyBoosts --> DetermineGrade{Grade Threshold}
    DetermineGrade -->|>=1100| SS
    DetermineGrade -->|950-1099| S
    DetermineGrade -->|850-949| A
    DetermineGrade -->|750-849| B+
    DetermineGrade -->|650-749| B
    DetermineGrade -->|550-649| C+
    DetermineGrade -->|450-549| C
    DetermineGrade -->|350-449| D+
    DetermineGrade -->|250-349| D
    DetermineGrade -->|150-249| E
    DetermineGrade -->|0-149| F
    SS --> Return[Return Grade Badge]
    F --> Return
```

---

## 4. Factor Inheritance Calculation Flow

```mermaid
flowchart TD
    Start([Calculate Inheritance]) --> LoadParents[Load Parent Factors]
    LoadParents --> BaseStars[Count Base Stars by Type]
    BaseStars --> BonusStars[Apply Scenario/Support Bonuses]
    BonusStars --> BuildPool[Build Blue/Red/White Pools]
    BuildPool --> RollFactors[Roll Factors with Weights]
    RollFactors --> ApplyToChild[Apply Bonuses to Child]
    ApplyToChild --> SaveFactors[Persist Inheritance Result]
```

---

## 5. Goal Progress Tracking Flow

```mermaid
flowchart TD
    Start([Track Goals]) --> LoadGoals[Load Scenario Goals]
    LoadGoals --> Evaluate{Goal Type}
    Evaluate -->|Race Wins| CheckWins[Check Required Placements]
    Evaluate -->|Stat Target| CheckStat[Check Stat >= Target]
    Evaluate -->|Skill Target| CheckSkill[Check Skill Owned]
    CheckWins --> UpdateStatus
    CheckStat --> UpdateStatus
    CheckSkill --> UpdateStatus
    UpdateStatus --> ProgressUI[Update Progress UI]
    ProgressUI --> GoalState{All Goals Met?}
    GoalState -->|Yes| Ready[Ready for Finale]
    GoalState -->|No| Continue[Continue Tracking]
```

---

## 6. Race Readiness Calculation Flow

```mermaid
flowchart TD
    Start([Compute Readiness]) --> GatherInputs[Stats, Aptitudes, Skills, Mood, Condition]
    GatherInputs --> ScoreStats[Score Stat Fit vs Race]
    ScoreStats --> ScoreApt[Score Aptitudes Distance/Surface]
    ScoreApt --> ScoreSkills[Score Active Skills]
    ScoreSkills --> ScoreForm[Score Mood/Condition]
    ScoreForm --> Aggregate[Aggregate Weighted Score]
    Aggregate --> Classify{Classify Tier}
    Classify -->|>=85| Excellent
    Classify -->|70-84| Good
    Classify -->|55-69| Fair
    Classify -->|<55| Poor
    Classify --> ReturnTier[Return Readiness Tier + Percent]
```

---

## 7. Character State Management Flow

```mermaid
flowchart TD
    Start([Monitor State]) --> CheckEnergy[Check Energy Level]
    CheckEnergy --> EnergyLow{Energy < 40?}
    EnergyLow -->|Yes| SuggestRest[Suggest Rest]
    EnergyLow -->|No| ContinueCheck[Check Mood]
    ContinueCheck --> MoodLow{Bad/Normal Mood?}
    MoodLow -->|Yes| SuggestRecovery[Items/Events]
    MoodLow -->|No| CheckCondition{Condition Debuff?}
    CheckCondition -->|Yes| SuggestTreatment[Apply cures]
    CheckCondition -->|No| StateOK[State OK]
    SuggestRest --> UpdateUI
    SuggestRecovery --> UpdateUI
    SuggestTreatment --> UpdateUI
    StateOK --> UpdateUI[Update dashboard indicators]
```
