# USER FLOW DIAGRAMS: Complete Journey Maps

**Document Version**: 2.2.0 | **Date**: January 28, 2026 | **Status**: Current - Aligned with v2.2.0 and game-accurate mechanics

## Overview

User flow diagrams document the complete journeys users take through the system. These maps show decision points, alternate paths, error recovery, and system states at each stage.

**Changes in v2.2.0**:

- Added Performance Monitoring dashboard flow
- Added Support Deck Configuration flow
- Enhanced external sync tracking

**Changes in v2.0.0**:

- Added Storage Mode selection (Local vs Account)
- Separated Career Setup and Training Loop flows
- Added AI Advisory flow with hybrid routing
- Added OCR and Data Import flows
- Integration of Neuron AI agents and MCP tools

---

## UF-001: New Player Onboarding Flow

```text

START: Launch App
    │
    ├─ [First Time?]
    │   └─ NO → [Dashboard]
    │
    └─ YES
        │
        ├─ Welcome Screen
        │   ├─ Tutorial Toggle
        │   └─ [Get Started]
        │
        ├─ Storage Mode Selection
        │   ├─ [Local Mode] (No Login, Offline capable)
        │   │   └─ Initialize Local Storage
        │   └─ [Account Mode] (Cloud Sync, Cross-device)
        │       └─ Login / Register Flow
        │
        ├─ Initial Setup
        │   ├─ [Set User Name]
        │   ├─ [Select Avatar]
        │   └─ [Preferences: Dark/Light, Language]
        │
        └─ Onboarding Complete
            ├─ [Trigger Dashboard Tutorial]
            └─ [→ Dashboard]

```

**Decision Points**: 3 (Tutorial, Storage Mode, Account Creation)  
**Success Metric**: User lands on dashboard with preferred storage mode active

---

## UF-002: Career Setup Flow

```text

START: Create New Run
    │
    ├─ [Select Trainee]
    │   ├─ Filter by Rarity/Distance
    │   └─ [Select Character]
    │
    ├─ [Select Scenario]
    │   └─ URA Finals / Unity Cup / Grand Masters
    │
    ├─ [Inheritance Configuration]
    │   ├─ Select Parent A
    │   ├─ Select Parent B
    │   └─ [Preview Factor Bonuses] (Stats + Skills)
    │
    ├─ [Support Deck Build]
    │   ├─ Select 6 cards
    │   │   ├─ 5 Owned
    │   │   └─ 1 Borrowed (Friend Slot)
    │   └─ [Validate Deck] (Check Type balance)
    │
    └─ [Confirm & Start]
        ├─ Initialize Run State (Day 1)
        └─ [→ Training Screen]

```

**Decision Points**: 4 (Trainee, Scenario, Parents, Deck)  
**Dependencies**: Character roster, Support Card inventory

---

## UF-003: Training Day Flow

```text

START: Turn Start
    │
    ├─ [View Status]
    │   ├─ Current Turn / Total Turns
    │   ├─ Energy / Mood
    │   └─ Active Conditions
    │
    ├─ [Check Training Options]
    │   ├─ Request AI Predictions
    │   │   ├─ Speed | Stamina | Power | Guts | Wisdom
    │   │   └─ [Display Gains + Failure Risk]
    │   │
    │   └─ Check for Support Events / Hints
    │
    ├─ [Select Action]
    │   ├─ Train → [Execute Training]
    │   ├─ Rest → [Restore Energy]
    │   ├─ Race → [Go to Race Prep] (See UF-004)
    │   └─ Skill Shop → [Go to Skills] (See UF-005)
    │
    ├─ [Action Execution]
    │   ├─ Update Stats
    │   ├─ Process Events (Support/Scenario)
    │   └─ Update Mood/Condition
    │
    └─ [Turn End]
        └─ Advance to Next Turn

```

**Loops**: Repeats 78 times per career  
**AI Integration**: Training Prediction Engine (Neuron)

---

## UF-004: Race Day Flow

```text

START: Race Week
    │
    ├─ [Analyze Race]
    │   ├─ Distance / Surface / Grade
    │   ├─ Opponent Strength
    │   └─ Win Probability Calculation
    │
    ├─ [Preparation Phase]
    │   ├─ Check Readiness
    │   │   └─ Stats vs Requirements
    │   ├─ [AI Strategy Advice]
    │   │   └─ Recommended Running Style (Nige/Senkou/Sashi/Oikomi)
    │   └─ Skill Check
    │       └─ Purchase recommended skills?
    │
    ├─ [Race Execution]
    │   ├─ [Start Race]
    │   ├─ Simulation / Animation
    │   └─ [Display Results]
    │
    └─ [Post-Race]
        ├─ Award Stats / SP / Fans
        ├─ Update Career History
        └─ [→ Resume Training Loop]

```

**Decision Points**: Strategy selection, Skill acquisition  
**Key Metric**: Win Probability %

---

## UF-005: Skill Management Flow

```text

START: Skill Shop
    │
    ├─ [View Catalog]
    │   ├─ Filter: Acquired / Available / Hints
    │   └─ Sort: SP Cost / Priority
    │
    ├─ [Select Skill]
    │   ├─ Check Requirements (Pt cost, Prerequisites)
    │   ├─ Check Hint Level (Discount 0-40%, 5 levels)
    │   └─ Check Evolution Status (Normal → Rare)
    │
    ├─ [AI Recommendation]
    │   └─ "High Priority for upcoming Long Distance race"
    │
    ├─ [Acquire Action]
    │   ├─ Deduct SP
    │   ├─ Add to Active Skills
    │   └─ Resolve Evolution (if applicable)
    │
    └─ [Update Loadout]
        └─ Save Configuration

```

**Optimization**: SP Budget Management  
**Integration**: Skill Evolution System

---

## UF-006: Support Deck Building Flow

```text

START: Deck Editor
    │
    ├─ [Select Slot 1-6]
    │
    ├─ [Card Selection]
    │   ├─ Filter by Type (Speed/Stamina/etc.)
    │   ├─ Filter by Meta Tier (SS/S/A/B)
    │   └─ Check Bond/Level status
    │
    ├─ [Deck Analysis]
    │   ├─ Calculate Synergy Score
    │   ├─ Check Type Distribution
    │   └─ [AI Optimization Request]
    │       └─ "Suggest changes for Speed focus"
    │
    └─ [Save Deck]
        └─ Validate Constraints (1 Friend max, 6 Total)

```

**Decision Points**: Card selection, Synergy optimization  
**External Data**: Meta Tier sync from umapyoi.net

---

## UF-007: AI Advisor Journey

```text

START: Ask Question
    │
    ├─ [User Query]
    │   └─ "How do I fix my Stamina?"
    │
    ├─ [Context Assembly]
    │   ├─ Current Run State (Stats, Turn, Deck)
    │   └─ User Preferences
    │
    ├─ [Router]
    │   ├─ Is Ollama available?
    │   │   ├─ YES → [Route Local] (Cost: $0)
    │   │   └─ NO → [Route Cloud] (AWS Bedrock)
    │   └─ Complexity Check
    │
    ├─ [Generation]
    │   ├─ Agent Processing (Neuron)
    │   └─ Response Formatting
    │
    └─ [Response Delivery]
        ├─ Show Advice with Confidence Score
        ├─ [Action Buttons] (Apply/Dismiss)
        └─ Track Token Usage

```

**Integration**: Ollama (Local), AWS Bedrock (Cloud)  
**Metrics**: Latency, Cost, Token Usage

---

## UF-008: OCR & Data Import Flow

```text

START: Import Action
    │
    ├─ [Select Source]
    │   ├─ File (JSON/Excel)
    │   └─ Screenshot (OCR)
    │
    ├─ PATH A: File Import
    │   ├─ Upload File
    │   ├─ Validate Schema
    │   └─ Conflict Resolution (Skip/Overwrite)
    │
    ├─ PATH B: OCR Import
    │   ├─ Upload Image
    │   ├─ Preprocess (Resize/Grayscale)
    │   ├─ Tesseract Extraction
    │   ├─ Data Parsing
    │   └─ [Validation Gate]
    │       ├─ High Confidence → Auto-fill
    │       └─ Low Confidence → Manual Review UI
    │
    └─ [Save Data]
        └─ Update Character/Run Record

```

**Dependencies**: Tesseract Service, Validation Logic  
**Error Handling**: Manual correction UI for low confidence reads

---

## Summary Matrix

| Flow | Length | Decision Points | Loops | AI Involvement |
| --- | --- | --- | --- | --- |
| UF-001 | 5 min | 3 | No | None |
| UF-002 | 5-10 min | 4 | No | Deck recommendation |
| UF-003 | Continuous | 50+ | Yes | Training prediction, Risk assessment |
| UF-004 | 5 min | 3 | No | Win probability, Strategy advice |
| UF-005 | Variable | 10+ | No | Build recommendations |
| UF-006 | 5-10 min | 6 | No | Synergy analysis |
| UF-007 | 1 min | 1 | No | Full conversational AI |
| UF-008 | 2 min | 2 | No | OCR Confidence scoring |

**Total Flows**: 8 major user journeys  
**System Version**: 2.1.0  
**Related**: [TECH-FLOW Index](../tech-flow/000_TECH_FLOW_INDEX.md), [SPEC Index](../specs/000_SPECS_INDEX.md)
