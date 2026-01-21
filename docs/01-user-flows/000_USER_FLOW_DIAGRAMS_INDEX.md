# USER FLOW DIAGRAMS: Complete Journey Maps

**Document Version**: 1.0 | **Date**: January 14, 2026 | **Status**: Comprehensive

## Overview

User flow diagrams document the complete journeys users take through the system. These maps show decision points, alternate paths, error recovery, and system states at each stage.

---

## UF-001: New Player Onboarding Flow

```
START: Launch App
    │
    ├─ [First Time?]
    │   └─ NO → [Dashboard]
    │
    └─ YES
        │
        ├─ Welcome Screen
        │   ├─ Tutorial Toggle
        │   └─ [Continue]
        │
        ├─ Account Creation
        │   ├─ [Username]
        │   ├─ [Email]
        │   ├─ [Password]
        │   ├─ [Verify Email]
        │   └─ {Validation Error} ──> [Re-enter]
        │
        ├─ Career Configuration
        │   ├─ [Select Trainee]
        │   │   └─ 50+ options with filters
        │   ├─ [Select Scenario]
        │   │   └─ Chapter 1, Grand Masters, etc.
        │   ├─ [Set Goal]
        │   │   └─ URA Championship, Climax Race, etc.
        │   └─ [Set Target Grade]
        │       └─ C, B, A, A+, Sss
        │
        ├─ Goal Breakdown
        │   ├─ [System shows recommended training facilities]
        │   ├─ [Shows stat allocation targets]
        │   ├─ [Shows timeline: {weeks} weeks]
        │   └─ [Confirm Goal]
        │
        ├─ Dashboard Orientation
        │   ├─ [Character Stats Card]
        │   ├─ [Training Prediction]
        │   ├─ [Upcoming Race Schedule]
        │   ├─ [Goals Progress]
        │   ├─ [Support Cards Team]
        │   └─ [Interactive Tutorial Overlays]
        │
        ├─ First Training Session
        │   ├─ [System recommends facility]
        │   ├─ [Show stat gains preview]
        │   ├─ [User confirms training]
        │   └─ [Training completes with animation]
        │
        └─ Onboarding Complete
            ├─ [Give rewards (rare skill book)]
            ├─ [Unlock full features]
            └─ [→ Dashboard]
```

**Decision Points**: 5 (Tutorial, Trainee, Scenario, Goal, Facility)  
**Error Paths**: Email validation, stat conflicts  
**Estimated Duration**: 10-15 minutes  
**Success Metric**: Career created, first training completed

---

## UF-002: Career Progression & Training Loop

```
START: Dashboard
    │
    ├─ [View Career Progress]
    │   ├─ Weeks Remaining: {X}
    │   ├─ Goal Status: {On Track/At Risk}
    │   └─ Current Grade: {Grade}
    │
    ├─ TRAINING LOOP (repeats X times)
    │   │
    │   ├─ [Select Training Facility]
    │   │   ├─ Speed Facility → +Speed +Stamina
    │   │   ├─ Power Facility → +Power
    │   │   ├─ Intelligence Facility → +Intelligence -Stamina
    │   │   ├─ Wisdom Facility → +Wisdom
    │   │   ├─ Vitality Facility → +Vitality +Stamina
    │   │   └─ [View Predictions for each]
    │   │
    │   ├─ {AI Recommendation Available?}
    │   │   ├─ YES → [Show AI suggestion with reasoning]
    │   │   └─ NO → [Continue without AI]
    │   │
    │   ├─ [Confirm Training]
    │   │   ├─ Execute training session
    │   │   ├─ Update character stats
    │   │   ├─ Reduce energy/motivation
    │   │   └─ Log training history
    │   │
    │   ├─ {Check for Special Event}
    │   │   ├─ YES → [Trigger Event with decision]
    │   │   │   ├─ Option A (stat bonus)
    │   │   │   ├─ Option B (relationship bonus)
    │   │   │   └─ Option C (skill unlock)
    │   │   └─ NO → [Continue]
    │   │
    │   ├─ [View Updated Stats]
    │   │   ├─ Current: {Speed: 42%}
    │   │   ├─ Goal: {Speed: 60%}
    │   │   ├─ Gap: {18%}
    │   │   └─ Progress: {On Track}
    │   │
    │   └─ {More weeks remaining?}
    │       ├─ YES → [Loop back to training selection]
    │       └─ NO → [Schedule races]
    │
    ├─ RACE PHASE
    │   │
    │   ├─ [View Upcoming Race Schedule]
    │   │   └─ 4 races shown for selected weeks
    │   │
    │   ├─ [Analyze Race]
    │   │   ├─ Race Requirements
    │   │   ├─ Predicted Performance
    │   │   ├─ Grade Outcome
    │   │   └─ Strategies
    │   │
    │   ├─ [Execute Race]
    │   │   ├─ Load race scene
    │   │   ├─ Play race animation
    │   │   ├─ Show results
    │   │   └─ Log to database
    │   │
    │   ├─ {Race Result Analysis}
    │   │   ├─ Win → [Grant rewards + fans]
    │   │   ├─ 2nd → [Grant partial rewards]
    │   │   └─ Loss → [Log result, motivation impact]
    │   │
    │   └─ {All races completed?}
    │       ├─ NO → [Continue to next race]
    │       └─ YES → [Summary]
    │
    ├─ CAREER SUMMARY
    │   ├─ [Final Grade Achieved]
    │   ├─ [Total Fans Earned]
    │   ├─ [Training History Chart]
    │   ├─ [Race Performance Summary]
    │   ├─ [Skill Acquisition List]
    │   └─ [Comparison to Goal]
    │
    └─ [Continue / Start New Career]
        ├─ New Career → [Back to Trainee Selection]
        └─ Retire → [Dashboard / Archive]
```

**Duration**: 24 weeks (with accelerated training option)  
**Decision Points**: 50+ (facility choice, event decisions, race strategies)  
**Failure Path**: {At Risk} → [Notify user, suggest AI help]

---

## UF-003: Goal Management & Tracking

```
START: Dashboard / Goals Panel
    │
    ├─ [View Active Goals]
    │   ├─ Primary Goal: {URA Championship Grade A}
    │   │   ├─ Status: ON TRACK (70% progress)
    │   │   ├─ Requirements Met: 4/5
    │   │   ├─ Next Milestone: 2 races left
    │   │   └─ [Details]
    │   │
    │   ├─ Secondary Goal: {Climax Race Top 5}
    │   │   ├─ Status: IN PROGRESS
    │   │   ├─ Requirements Met: 3/4
    │   │   └─ [Details]
    │   │
    │   └─ Milestone Goals: {9 skill acquisitions}
    │       ├─ Status: AT RISK (3/9 completed)
    │       ├─ Weeks Remaining: 8
    │       └─ [Alert: Increase training frequency]
    │
    ├─ [Goal Details Panel]
    │   ├─ Goal Name: {URA Championship Grade A}
    │   ├─ Scenario: {URA Championship}
    │   ├─ Target Grade: {A}
    │   ├─ Current Grade: {B+}
    │   ├─ Grade Gap: 300 fans needed
    │   │
    │   ├─ Stat Requirements
    │   │   ├─ Speed: 70% ✓
    │   │   ├─ Stamina: 65% (need 70%) ✗
    │   │   ├─ Power: 60% ✓
    │   │   ├─ Intelligence: 50% ✓
    │   │   └─ Wisdom: 55% ✓
    │   │
    │   ├─ Race Requirements
    │   │   ├─ Final Race Grade: A ✓
    │   │   ├─ Total Fans: 2,500 (have 2,200) ✗
    │   │   └─ Never Last Place: ✓
    │   │
    │   └─ {Adjust Goal?}
    │       ├─ [Modify Target Grade]
    │       ├─ [Extend Timeline]
    │       ├─ [Change Scenario]
    │       └─ [Cancel Goal]
    │
    ├─ {Goal Analysis}
    │   │
    │   ├─ {On Track Path}
    │   │   ├─ Continue current training regimen
    │   │   ├─ Focus on: Stamina +5%
    │   │   ├─ Races: Perform in top 3
    │   │   └─ Estimated completion: 6 weeks
    │   │
    │   ├─ {At Risk Path}
    │   │   ├─ [Request AI Analysis]
    │   │   │   └─ [Receive remedial plan]
    │   │   ├─ {System suggests}
    │   │   │   ├─ Increase training frequency: 5→7 per week
    │   │   │   ├─ Focus on Stamina exclusively for 2 weeks
    │   │   │   └─ Use support card: {Durability +}
    │   │   ├─ {Estimated recovery: 8 weeks}
    │   │   └─ [Implement Plan / Dismiss]
    │   │
    │   └─ {Off Track Path}
    │       ├─ [Goal Failed: Reset?]
    │       ├─ Option A: Continue modified goal (lower grade target)
    │       ├─ Option B: Extend timeline (new deadline)
    │       └─ Option C: Archive career
    │
    ├─ [Goal Progress Visualization]
    │   ├─ Timeline Chart (weeks vs progress)
    │   ├─ Stat radar (current vs target)
    │   ├─ Race performance history
    │   └─ Grade trajectory
    │
    └─ END: Goal tracking complete
```

**Update Frequency**: After each training session, after each race  
**Alert Triggers**: At Risk (60% expected), Off Track (30%), Milestone completed  
**AI Integration**: Remedial plan generation when At Risk

---

## UF-004: Skill Planning & Acquisition

```
START: Skill Management Screen
    │
    ├─ [View Skill Inventory]
    │   ├─ Owned Skills: {32}
    │   │   ├─ Rare Skills: {8}
    │   │   ├─ Normal Skills: {18}
    │   │   ├─ Common Skills: {6}
    │   │   └─ By Category: Speed, Stamina, etc.
    │   │
    │   ├─ [Sort & Filter]
    │   │   ├─ By rarity
    │   │   ├─ By category
    │   │   ├─ By acquisition cost
    │   │   └─ By evolution potential
    │   │
    │   └─ [Select Skill for Details]
    │       ├─ Name: {Go with the Flow}
    │       ├─ Rarity: Normal
    │       ├─ Cost: 120 SP (base)
    │       ├─ Effect: +5 Stamina in race
    │       ├─ Evolution: → Lane Legerdemain (Rare)
    │       ├─ Hints Collected: 2
    │       ├─ Final Cost: 72 SP (40% reduction)
    │       └─ [Acquire] or [View Alternative Paths]
    │
    ├─ [Skill Acquisition Planning]
    │   │
    │   ├─ Current SP: {850}
    │   │
    │   ├─ {Planned Acquisitions}
    │   │   ├─ Week 8: Skill A (80 SP)
    │   │   ├─ Week 12: Skill B (120 SP)
    │   │   ├─ Week 18: Skill C (150 SP)
    │   │   └─ Total Cost: 350 SP [Projected Available: 750 SP] ✓
    │   │
    │   └─ [Adjust Plan]
    │       ├─ Add skill
    │       ├─ Remove skill
    │       ├─ Prioritize by goal
    │       └─ Auto-plan (AI recommendation)
    │
    ├─ [Acquire Skill Workflow]
    │   │
    │   ├─ [Select Skill to Acquire]
    │   │   └─ {Search & filter from available list}
    │   │
    │   ├─ [Review Cost Calculation]
    │   │   ├─ Base Cost: 120 SP
    │   │   ├─ Hint Reduction: -48 SP (2 hints × 24 SP each)
    │   │   ├─ Final Cost: 72 SP
    │   │   ├─ Current SP: 850
    │   │   └─ After Acquisition: 778 SP ✓
    │   │
    │   ├─ [Confirm Acquisition]
    │   │   ├─ Deduct 72 SP
    │   │   ├─ Add skill to inventory
    │   │   ├─ Log to database
    │   │   └─ {Check evolution eligibility}
    │   │
    │   └─ {Evolution Opportunity}
    │       ├─ YES → [Offer evolution]
    │       │   ├─ Evolve to: Lane Legerdemain (Rare)
    │       │   ├─ Cost: +60 SP
    │       │   ├─ Benefits: +7 Stamina (vs +5)
    │       │   └─ [Evolve Now] / [Skip]
    │       └─ NO → [Complete acquisition]
    │
    ├─ [Hint Collection Dashboard]
    │   │
    │   ├─ Hints Available: {12 total}
    │   │   ├─ Used: 2
    │   │   ├─ Ready to use: 10
    │   │   └─ Pending: 0
    │   │
    │   ├─ [Hint Strategy]
    │   │   ├─ Option A: Reserve high-value hints (Rare skills)
    │   │   ├─ Option B: Use hints for common skills now
    │   │   ├─ Option C: Save for evolution skills
    │   │   └─ [AI Recommendation: Option C]
    │   │
    │   └─ [Auto-apply hints?]
    │       ├─ Enable: Hints auto-apply to next acquisition
    │       └─ Disable: Manual hint application
    │
    └─ END: Skill planning complete, portfolio optimized
```

**Total Skills Available**: 150+  
**Planning Horizon**: 24 weeks  
**Optimization**: Hints reduce costs 20-40%, evolution adds 5% effect bonus  
**AI Role**: Recommend skill priority based on goal requirements

---

## UF-005: Support Card Configuration

```
START: Support Card Team Screen
    │
    ├─ [View Current 6-Card Deck]
    │   ├─ Card 1: Mejiro Mcqueen (SSR)
    │   │   ├─ Skill Provision: Wisdom +
    │   │   ├─ Bond: Level 5 (100%)
    │   │   ├─ Specialization: Wisdom Focused
    │   │   └─ [Details]
    │   │
    │   ├─ Card 2: Mejiro Dober (SR)
    │   │   ├─ Skill Provision: Intelligence +
    │   │   ├─ Bond: Level 3 (60%)
    │   │   ├─ Specialization: General
    │   │   └─ [Details]
    │   │
    │   ├─ Card 3: Wonderful Luna (SR)
    │   │   ├─ Skill Provision: Stamina ++
    │   │   ├─ Bond: Level 4 (80%)
    │   │   ├─ Specialization: Stamina Synergy
    │   │   └─ [Details]
    │   │
    │   ├─ Card 4: Maruzen Skipper (R)
    │   │   ├─ Skill Provision: Speed
    │   │   ├─ Bond: Level 2 (40%)
    │   │   ├─ Specialization: None
    │   │   └─ [Details / Bench]
    │   │
    │   ├─ Card 5: Ikuno Dictate (R)
    │   ├─ Card 6: Admire Vega (SR)
    │   │
    │   └─ [Edit Team]
    │
    ├─ [Bench & Collection]
    │   ├─ Owned Cards: 45 total
    │   ├─ SSR: 3
    │   ├─ SR: 12
    │   ├─ R: 30
    │   │
    │   ├─ [Search & Filter]
    │   │   ├─ By rarity
    │   │   ├─ By skill type
    │   │   ├─ By bond level
    │   │   └─ By synergy value
    │   │
    │   └─ [View Card Details]
    │       ├─ Name: Mejiro Mcqueen
    │       ├─ Rarity: SSR
    │       ├─ Skill Type: Wisdom
    │       ├─ Base Effect: Wisdom +1%
    │       ├─ Bond Bonus: +0.5% per level
    │       ├─ Specialization: Wisdom Focused
    │       └─ In Team: YES (Slot 1)
    │
    ├─ [Reconfigure Team]
    │   │
    │   ├─ Goal: {Improve Wisdom for URA Championship}
    │   │
    │   ├─ Current Synergy Score: 78/100
    │   │   ├─ Wisdom synergy: 85%
    │   │   ├─ Stamina synergy: 70%
    │   │   └─ Overall harmony: 78%
    │   │
    │   ├─ [Suggested Optimizations]
    │   │   ├─ Option A: Swap Maruzen Skipper → Mejiro Dober (boost Intelligence)
    │   │   ├─ Option B: Swap Ikuno Dictate → Wonderful Luna (boost Stamina)
    │   │   ├─ Option C: Keep current (already optimal for Wisdom)
    │   │   └─ {System recommends: Option B}
    │   │
    │   ├─ [Make Changes]
    │   │   ├─ Drag Card to Slot
    │   │   ├─ Remove from Team
    │   │   ├─ Rotate Available Cards
    │   │   └─ [Confirm Changes]
    │   │
    │   └─ [Updated Synergy]
    │       └─ New Score: 82/100 ✓
    │
    ├─ [Bond Level Management]
    │   │
    │   ├─ [Improve Bonds]
    │   │   ├─ Card 1: Mcqueen [████████░░] Level 5 (Maxed)
    │   │   ├─ Card 2: Dober [██████░░░░] Level 3 → Level 4 (20 interactions)
    │   │   ├─ Card 3: Luna [███████░░░] Level 4 → Level 5 (15 interactions)
    │   │   └─ Card 4: Skipper [██░░░░░░░░] Level 2 → Level 3 (25 interactions)
    │   │
    │   ├─ [Bond Growth Strategy]
    │   │   ├─ Focus on: High-impact cards (SR+ only)
    │   │   ├─ Target: Level 4+ for all active cards
    │   │   ├─ Timeline: 6 weeks
    │   │   └─ [Enable Bond Farm Reminder]
    │   │
    │   └─ [Bond Interactions]
    │       ├─ Talk: +1 bond, 30 min cooldown
    │       ├─ Gift: +3 bond, rare resource cost
    │       ├─ Training: +2 bond, during training session
    │       └─ Outing: +2 bond, costs AP
    │
    └─ END: Support card team optimized for goal achievement
```

**Constraint**: Exactly 6 cards active  
**Bond System**: Level 1-5, unlocks skill hints  
**Synergy Calculation**: Weighted rating based on stat alignment  
**Meta Tracking**: Tier rankings for specific scenarios

---

## UF-006: Race Preparation & Execution

```
START: Race Screen / Calendar
    │
    ├─ [View Race Schedule]
    │   ├─ Next Race: {URA Championship Final - 2 weeks away}
    │   ├─ Previous Results: {Won 3, 2nd 1, Lost 0}
    │   ├─ Grade History: [C, B, A, A+, A+]
    │   └─ [Select Race to Analyze]
    │
    ├─ [Analyze Race]
    │   │
    │   ├─ Race Info
    │   │   ├─ Name: URA Championship Final
    │   │   ├─ Scenario: URA Championship
    │   │   ├─ Distance: 2400m
    │   │   ├─ Surface: Turf
    │   │   ├─ Running Style: Frontrunner Preferred
    │   │   └─ Track: Hard (Fast)
    │   │
    │   ├─ Requirements for Grade A
    │   │   ├─ Top 3 Finish: Required
    │   │   ├─ Min Speed: 70%
    │   │   ├─ Min Stamina: 68%
    │   │   ├─ Fans Needed: 200+
    │   │   └─ Current Stats: [Speed 72% ✓, Stamina 66% ⚠]
    │   │
    │   ├─ Performance Prediction
    │   │   ├─ Grade Prediction: A (85% confidence)
    │   │   ├─ Expected Placing: 2nd
    │   │   ├─ Confidence: 85%
    │   │   ├─ Variance: ±1 place
    │   │   └─ [View Prediction Details]
    │   │
    │   ├─ Strategies
    │   │   │
    │   │   ├─ Strategy A: Conservative (Stamina-safe)
    │   │   │   ├─ Maintain steady pace
    │   │   │   ├─ Predicted Placing: 2-3
    │   │   │   ├─ Success Rate: 90%
    │   │   │   └─ [Select]
    │   │   │
    │   │   ├─ Strategy B: Aggressive (Speed-focused)
    │   │   │   ├─ Chase early leader
    │   │   │   ├─ Predicted Placing: 1-2
    │   │   │   ├─ Success Rate: 75%
    │   │   │   ├─ Risk: Stamina depletion
    │   │   │   └─ [Select]
    │   │   │
    │   │   └─ Strategy C: AI Recommended
    │   │       ├─ Balanced approach with position flexibility
    │   │       ├─ Predicted Placing: 1-2
    │   │       ├─ Success Rate: 82%
    │   │       ├─ Reasoning: {Stamina slightly below target, use speed advantage}
    │   │       └─ [Select]
    │   │
    │   └─ [Confirm & Execute Race]
    │
    ├─ [Pre-Race Preparation - 1 Week Before]
    │   │
    │   ├─ Stamina Gap Analysis
    │   │   ├─ Current: 66%
    │   │   ├─ Target: 70%
    │   │   ├─ Gap: 4%
    │   │   ├─ Time to close: 7 days
    │   │   │
    │   │   └─ {System suggests}
    │   │       ├─ 2 Vitality Facility sessions
    │   │       ├─ 1 Speed Facility session
    │   │       └─ Predicted result: 71% Stamina ✓
    │   │
    │   ├─ Support Card Optimization
    │   │   ├─ Current Team: [Mcqueen, Dober, Luna, Skipper, Dictate, Vega]
    │   │   ├─ Synergy: 82%
    │   │   └─ Optimization for race type: Turf + Frontrunner
    │   │       ├─ Consider swapping: Skipper → [Mejiro Dober for Intelligence]
    │   │       ├─ Updated Synergy: 85%
    │   │       └─ [Apply Change]
    │   │
    │   └─ [Race Ready: Execute in 7 days]
    │
    ├─ [Race Execution]
    │   │
    │   ├─ Load Race Scene
    │   │   ├─ Track rendering: 100%
    │   │   ├─ Horse model: Ready
    │   │   ├─ AI opponents: 10 loaded
    │   │   └─ [Start Race]
    │   │
    │   ├─ Race Animation (2-3 minutes)
    │   │   ├─ Early Game: Maintain position
    │   │   ├─ Middle: Chase leader (Aggressive strategy)
    │   │   ├─ Final Sprint: Full power
    │   │   └─ [Race complete: Placing 2nd]
    │   │
    │   └─ [Show Results]
    │       ├─ Placing: 2nd
    │       ├─ Grade Achieved: A ✓
    │       ├─ Fans Earned: 250
    │       ├─ Grade Points: +150
    │       ├─ Rewards: Rare Skill Book (Random)
    │       ├─ Comparison to Prediction: ✓ As predicted
    │       └─ [Log Race & Update Career]
    │
    └─ END: Race complete, results recorded, next steps shown
```

**Race Duration**: 2-3 minute animation (skippable)  
**Strategy Impact**: ±5-10% placing variation  
**Preparation Window**: 1-2 weeks ideal  
**Failure Path**: {Stamina too low} → [Warn user before race]

---

## Summary Matrix

| Flow | Length | Decision Points | Loops | AI Involvement |
|------|--------|-----------------|-------|-----------------|
| UF-001 | 10-15 min | 5 | 0 | Optional tutorial |
| UF-002 | 24 weeks | 50+ | Yes (training) | Training prediction, AI recommendation |
| UF-003 | Continuous | 3-5 | Yes (weekly) | Goal analysis, remedial planning |
| UF-004 | Variable | 10+ | No | Skill priority recommendation |
| UF-005 | 5-10 min | 2 | No | Card optimization |
| UF-006 | 2-3 weeks | 3 | No | Race prediction, strategy recommendation |

**Total Flows**: 6 major user journeys  
**Integration Points**: 15+ AI decision support moments  
**Error Recovery**: Guided paths for all failure scenarios

---

**Related**: [TECH-FLOW Index](../tech-flow/000_TECH_FLOW_INDEX.md), [Sequence Diagrams](./000_SEQUENCE_DIAGRAMS_INDEX.md), [SPEC Index](../specs/000_SPECS_INDEX.md)
