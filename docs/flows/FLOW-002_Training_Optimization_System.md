# FLOW-002: Training Optimization System Flow

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0
**Date**: January 14, 2026
**Related Documents**: [PRD-002], [SPEC-002]

---

## 1. Training Prediction Pipeline Flow

```mermaid
flowchart TD
    Start([User Opens Training]) --> LoadContext[Load Run Context: stats, mood, energy, deck]
    LoadContext --> BaseCalc[Calculate Base Gains by Training Type]
    BaseCalc --> ApplyBonuses[Apply Support Card Bonuses]
    ApplyBonuses --> RiskCalc[Calculate Failure Risk]
    RiskCalc --> HintChance[Determine Hint Chance]
    HintChance --> BuildPrediction[Build Prediction Payload]
    BuildPrediction --> ReturnUI[Return to UI]
```

---

## 2. Training Execution Flow

```mermaid
flowchart TD
    Start([User Confirms Training]) --> LoadPrediction[Load Cached Prediction]
    LoadPrediction --> ValidateEnergy{Energy Sufficient?}
    ValidateEnergy -->|No| Block[Block Action]
    ValidateEnergy -->|Yes| RollOutcome[Roll Success vs Risk]
    RollOutcome -->|Success| ApplyFull[Apply Full Gains + Bond + Hints]
    RollOutcome -->|Failure| ApplyPartial[Apply Partial Gains + Possible Condition Down]
    ApplyFull --> ProcessEvents[Process Events/Story]
    ApplyPartial --> ProcessEvents
    ProcessEvents --> Persist[Persist State + History]
    Persist --> ReturnResult[Return Result to UI]
```

---

## 3. Support Card Bonus Calculation Flow

```mermaid
flowchart TD
    Start([Support Bonus]) --> LoadDeck[Load Active Deck]
    LoadDeck --> IdentifyParticipants[Identify Cards Participating]
    IdentifyParticipants --> SumBonuses[Sum Training Bonuses (type, rarity, level, LB)]
    SumBonuses --> BondFactor[Apply Bond Multipliers]
    BondFactor --> FriendBonus[Apply Friend Slot Bonus if present]
    FriendBonus --> ReturnBonuses[Return Bonus Multipliers]
```

---

## 4. Failure Risk Assessment Flow

```mermaid
flowchart TD
    Start([Risk Calc]) --> BaseRisk[Base Risk = 0]
    BaseRisk --> EnergyPenalty[Add penalty if low energy]
    EnergyPenalty --> MoodPenalty[Add penalty if mood bad]
    MoodPenalty --> ConditionPenalty[Add penalty if debuff]
    ConditionPenalty --> TrainingLevelAdj[Adjust by training difficulty]
    TrainingLevelAdj --> Clamp[Clamp 0-90%]
    Clamp --> ReturnRisk[Return Risk]
```

---

## 5. Skill Hint Acquisition Flow

```mermaid
flowchart TD
    Start([Hint Chance]) --> CheckParticipants[Check participating cards with hints]
    CheckParticipants --> RollHint{Roll per card}
    RollHint -->|Win| AssignHint[Assign Skill Hint Level +1]
    RollHint -->|Lose| NoHint[No hint]
    AssignHint --> UpdateHintState[Update hint levels, cap at 3]
    UpdateHintState --> ReturnHints
```

---

## 6. Friendship Training Activation Flow

```mermaid
flowchart TD
    Start([Friendship Check]) --> BondThreshold{Bond >= 80?}
    BondThreshold -->|No| NoFriend[Normal Training]
    BondThreshold -->|Yes| ApplyFriendBonus[Apply Friendship Bonus +2..+5 per stat]
    ApplyFriendBonus --> UpdateGains
    NoFriend --> UpdateGains[Keep normal gains]
    UpdateGains --> ReturnPrediction
```

---

## 7. Training History & Analytics Flow

```mermaid
flowchart TD
    Start([Record Training]) --> CaptureOutcome[Capture gains, risk, success/failure]
    CaptureOutcome --> SaveHistory[Save to history table]
    SaveHistory --> UpdateStats[Update aggregate: success rate, avg gains]
    UpdateStats --> Analytics[Expose for charts]
    Analytics --> UI[Show trends in dashboard]
```
