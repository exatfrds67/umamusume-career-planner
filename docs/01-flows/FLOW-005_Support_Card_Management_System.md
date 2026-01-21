# FLOW-005: Support Card Management System Flow

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0
**Date**: January 14, 2026
**Related Documents**: [PRD-005], [SPEC-005]

---

## 1. Support Card Collection Management Flow

```mermaid
flowchart TD
    Start([Open Collection]) --> LoadCollection[Load Owned Cards]
    LoadCollection --> FilterSort[Apply Filters/Sorting]
    FilterSort --> ViewCard[View Card Detail]
    ViewCard --> Actions{Action}
    Actions -->|Level Up| LevelCard
    Actions -->|Limit Break| LBC
    Actions -->|Add to Deck| AddDeck
    Actions -->|Compare| CompareCards
```

---

## 2. Deck Building & Optimization Flow

```mermaid
flowchart TD
    Start([Build Deck]) --> CreateOrEdit{Create New or Edit Existing}
    CreateOrEdit --> SelectCards[Select 6 Cards]
    SelectCards --> ValidateTypes{Type limits OK?}
    ValidateTypes -->|No| Warn
    Warn --> Adjust[Adjust selection]
    Adjust --> ValidateTypes
    ValidateTypes -->|Yes| ScoreDeck[Score deck]
    ScoreDeck --> Optimize{Auto-optimize?}
    Optimize -->|Yes| ApplyOptimize
    ApplyOptimize --> SaveDeck[Save Deck]
    Optimize -->|No| SaveDeck
```

---

## 3. Bond Progression & Training Flow

```mermaid
flowchart TD
    Start([Training]) --> LoadDeck[Load Active Deck]
    LoadDeck --> IdentifyParticipants[Identify participating cards]
    IdentifyParticipants --> AddBond[Add bond +3 base]
    AddBond --> CheckMilestone{Reached milestone?}
    CheckMilestone -->|Yes| GrantReward[Reward: hints/stats/event]
    CheckMilestone -->|No| Continue
    GrantReward --> UpdateState
    Continue --> UpdateState[Persist bond levels]
```

---

## 4. Meta Tier Synchronization Flow

```mermaid
flowchart TD
    Start([Sync Tiers]) --> Trigger{Daily/Manual/Event?}
    Trigger --> FetchMeta[Fetch tiers from umapyoi.net]
    FetchMeta --> Compare[Compare with current]
    Compare --> UpdateCards[Update tier labels]
    UpdateCards --> NotifyChanges[Notify user changes]
```

---

## 5. Support Card Recommendation Flow

```mermaid
flowchart TD
    Start([Need deck advice]) --> AnalyzeProfile[Analyze run profile]
    AnalyzeProfile --> IdentifyGaps[Identify type/stat gaps]
    IdentifyGaps --> RankCandidates[Rank candidate cards]
    RankCandidates --> SuggestTop[Suggest top list + acquisition tips]
```

---

## 6. Card Comparison & Analysis Flow

```mermaid
flowchart TD
    Start([Compare cards]) --> Select2to4[Select 2-4 cards]
    Select2to4 --> ShowTable[Show stats, bonuses, events, skills]
    ShowTable --> ScoreContext[Score per context]
    ScoreContext --> HighlightBest[Highlight best options]
```

---

## 7. Support Card Event Management Flow

```mermaid
flowchart TD
    Start([Event Triggered]) --> IdentifyEvent{Bond/Training/Random}
    IdentifyEvent --> ShowChoices[Show choices/outcomes]
    ShowChoices --> ApplyOutcome[Apply stats/bond/hints/items]
    ApplyOutcome --> PersistEvent[Persist history]
```
