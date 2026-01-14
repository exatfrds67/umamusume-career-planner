# FLOW-003: Race Strategy System Flow

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0
**Date**: January 14, 2026
**Related Documents**: [PRD-003], [SPEC-003]

---

## 1. Race Preparation Flow

```mermaid
flowchart TD
    Start([View Races]) --> LoadSchedule[Load Race Schedule]
    LoadSchedule --> SelectRace[Select Target Race]
    SelectRace --> GenerateCompetitors[Generate Competitor Set]
    GenerateCompetitors --> CalcReadiness[Calculate Readiness Score]
    CalcReadiness --> CalcWinProb[Calculate Win Probability]
    CalcWinProb --> Recommendations[Generate Prep Recommendations]
    Recommendations --> PreviewUI[Return Preview]
    PreviewUI --> ConfirmEntry{Enter Race?}
    ConfirmEntry -->|No| Exit
    ConfirmEntry -->|Yes| SaveEntry[Save Entry + Schedule]
```

---

## 2. Race Execution Simulation Flow

```mermaid
flowchart TD
    Start([Execute Race]) --> LoadContext[Load Run + Competitors]
    LoadContext --> SimStart[Simulate Start Phase]
    SimStart --> SimMid[Simulate Mid Phase]
    SimMid --> SimFinal[Simulate Final Phase]
    SimFinal --> ApplySkills[Apply Skill Triggers]
    ApplySkills --> RankCompetitors[Rank Competitors]
    RankCompetitors --> DetermineRewards[Determine Rewards]
    DetermineRewards --> Persist[Persist History + Rewards]
    Persist --> ReturnResult[Return Result]
```

---

## 3. Win Probability Calculation Flow

```mermaid
flowchart TD
    Start([Win Prob]) --> Base50[Base 50%]
    Base50 --> GradeAdj[Adjust by Race Grade]
    GradeAdj --> AptitudeAdj[Adjust by Aptitude Match]
    AptitudeAdj --> StatCompare[Adjust by Stat Comparison]
    StatCompare --> SkillAdj[Adjust by Skill Advantage]
    SkillAdj --> CompetitorAdj[Adjust by Competitor Strength]
    CompetitorAdj --> Clamp[Clamp 5-95%]
    Clamp --> Confidence[Classify Confidence]
    Confidence --> ReturnProb[Return Probability]
```

---

## 4. Race Reward Distribution Flow

```mermaid
flowchart TD
    Start([Distribute Rewards]) --> GetPlacement[Get Placement]
    GetPlacement --> PlacementMult[Placement Multiplier]
    PlacementMult --> GradeMult[Grade Multiplier]
    GradeMult --> CalculateRewards[Calculate Money/Fans/Items]
    CalculateRewards --> ApplyBonuses[Apply Event/Factor Bonuses]
    ApplyBonuses --> PersistRewards[Persist Rewards]
    PersistRewards --> ReturnRewards[Return to UI]
```

---

## 5. Competitor Generation Flow

```mermaid
flowchart TD
    Start([Generate Competitors]) --> LoadTemplate[Load Baseline by Grade]
    LoadTemplate --> RandomizeStats[Randomize Stats within band]
    RandomizeStats --> AssignStyle[Assign Race Style]
    AssignStyle --> AssignSkills[Assign Skills by Style]
    AssignSkills --> MarkStrong[Mark Strong Competitor if needed]
    MarkStrong --> ReturnSet[Return Competitor Set]
```

---

## 6. Race History & Analytics Flow

```mermaid
flowchart TD
    Start([Record Race]) --> SaveResult[Save placement, rewards, logs]
    SaveResult --> UpdateTrends[Update stats: win rate, placements]
    UpdateTrends --> Charts[Expose to charts/tables]
    Charts --> UI[Show analytics]
```

---

## 7. Race Schedule Planning Flow

```mermaid
flowchart TD
    Start([Plan Schedule]) --> LoadGoals[Load Scenario Goals]
    LoadGoals --> RecommendRaces[Recommend Races per goal]
    RecommendRaces --> EvaluatePrep[Evaluate readiness gap]
    EvaluatePrep --> MarkDeadlines[Mark deadlines with alerts]
    MarkDeadlines --> SavePlan[Save race plan]
```
