# FLOW-004: Skill Management System Flow

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0
**Date**: January 14, 2026
**Related Documents**: [PRD-004], [SPEC-004]

---

## 1. Skill Acquisition Flow

```mermaid
flowchart TD
    Start([Open Skill Shop]) --> LoadAvailable[Load Available Skills]
    LoadAvailable --> SelectSkill[Select Skill]
    SelectSkill --> CheckSP{Enough SP?}
    CheckSP -->|No| Block[Show SP Needed]
    CheckSP -->|Yes| CheckPrereq{Prerequisites Met?}
    CheckPrereq -->|No| ShowReqs[Show unmet requirements]
    CheckPrereq -->|Yes| ApplyDiscount[Apply Hint Discount]
    ApplyDiscount --> DeductSP[Deduct SP]
    DeductSP --> AddSkill[Add Skill to Owned]
    AddSkill --> UpdateLoadout[Update Loadout (optional)]
```

---

## 2. Skill Evolution Chain Flow

```mermaid
flowchart TD
    Start([Evolve Skill]) --> SelectBase[Select Evolvable Skill]
    SelectBase --> CheckRequirements{Requirements Met?}
    CheckRequirements -->|No| ShowReqs
    CheckRequirements -->|Yes| MapEvolved[Map to Evolved Skill]
    MapEvolved --> DeductSP[Deduct SP if needed]
    DeductSP --> ReplaceSkill[Replace Base with Evolved]
    ReplaceSkill --> SaveHistory[Log before/after]
```

---

## 3. Skill Hint Management Flow

```mermaid
flowchart TD
    Start([Receive Hint]) --> IdentifySource{Source}
    IdentifySource -->|Training| ProcessTraining
    IdentifySource -->|Race| ProcessRace
    IdentifySource -->|Support Event| ProcessEvent
    ProcessTraining --> ApplyHint
    ProcessRace --> ApplyHint
    ProcessEvent --> ApplyHint
    ApplyHint --> MergeHint[Increase Level up to 3]
    MergeHint --> UpdateDiscount[Update Discount 0/20/40%]
```

---

## 4. Skill Loadout Management Flow

```mermaid
flowchart TD
    Start([Open Loadout]) --> LoadOwned[Load Owned Skills]
    LoadOwned --> PlaceSlots[Place into Slots]
    PlaceSlots --> ValidateConflicts{Conflicts/Duplicates?}
    ValidateConflicts -->|Yes| WarnUser
    ValidateConflicts -->|No| SaveLoadout[Save Loadout]
    WarnUser --> Resolve[Resolve conflicts]
    Resolve --> SaveLoadout
```

---

## 5. SP Budget Optimization Flow

```mermaid
flowchart TD
    Start([Optimize SP]) --> GatherSkills[Gather Candidate Skills]
    GatherSkills --> ScoreSkills[Score value per SP]
    ScoreSkills --> Sort[Sort by value]
    Sort --> SelectWithinBudget[Select within SP budget]
    SelectWithinBudget --> BuildPlan[Build purchase plan]
    BuildPlan --> Recommend[Recommend to user]
```

---

## 6. Skill Inheritance from Factors Flow

```mermaid
flowchart TD
    Start([Factor Extraction]) --> LoadFactors[Load Blue/White/Green Factors]
    LoadFactors --> FilterSkills[Filter skills from factors]
    FilterSkills --> AssignHint[Assign hint level 1]
    AssignHint --> TrackParent[Store parent linkage]
```

---

## 7. Skill Effect Activation Flow

```mermaid
flowchart TD
    Start([Race Phase]) --> EvaluateConditions[Check activation conditions]
    EvaluateConditions -->|Met| ApplyEffect[Apply skill effect]
    EvaluateConditions -->|Not Met| Skip[No activation]
    ApplyEffect --> UpdatePerformance[Update race calc]
    Skip --> ContinuePhase
```
