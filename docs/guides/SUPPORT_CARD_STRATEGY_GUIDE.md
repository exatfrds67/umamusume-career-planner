# Support Card Strategy Guide

**Document Version**: 1.0.0  
**Date**: 2026-02-23  
**Project**: Umamusume Pretty Derby Career Planner  
**Status**: Complete  
**Audience**: Career Planners (Beginner to Intermediate)

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [Support Card Fundamentals](#2-support-card-fundamentals)
3. [Bond Progression & Friendship Training](#3-bond-progression--friendship-training)
4. [Multi-Card Synergy](#4-multi-card-synergy)
5. [Deck Composition Strategies](#5-deck-composition-strategies)
6. [Scenario-Specific Guidance](#6-scenario-specific-guidance)
7. [Friendship Training Activation](#7-friendship-training-activation)
8. [Quick Reference](#8-quick-reference)

---

## 1. Introduction

Support cards are the **primary force multiplier** in Umamusume Pretty Derby. Each card in your deck amplifies stat gains during training and provides special effects. This guide explains how to maximize their benefits through strategic bond management, deck composition, and scenario-specific selection.

**Key Concepts You'll Learn:**

- How bond progression works and unlocks stronger effects
- When friendship training activates for +20% stat gains
- How multi-card bonuses stack (+5% per support card, max +30%)
- Best deck composition patterns for different character types
- URA Finale vs Unity Cup specific strategies

---

## 2. Support Card Fundamentals

### 2.1 What Are Support Cards?

Support cards are trainer character cards that accompany your Umamusume during her career. They provide:

- **Stat bonuses** during training (5–10% base depending on rarity)
- **Friendship training triggers** at high bond levels
- **Multi-card synergies** when cards share specializations
- **Skill hints** during specific training sessions
- **Event triggers** that unlock special story moments

### 2.2 Support Card Types

**By Specialization:**

| Specialization | Best For |
| --- | --- |
| **Speed** | Speed-focused characters; competitive short races |
| **Stamina** | Stamina-focused characters; distance races 1800m+ |
| **Power** | Power-focused characters; acceleration and grades |
| **Guts** | Guts-focused characters; ability to come from behind |
| **Wit** | Wit-focused characters; race judgment and event handling |
| **Friend (Pal)** | General-purpose; fits any composition, boosts all stats slightly |

**By Rarity (affects base bonus):**

| Rarity | Base Bonus | How to Identify |
| --- | --- | --- |
| **SSR** | 10% bonus | Gold background, most powerful |
| **SR** | 7% bonus | Silver background, strong secondary choice |
| **R** | 5% bonus | Blue background, useful for broad coverage |

### 2.3 Card Composition Rules

Your deck contains **exactly 6 support cards:**

- **5 owned cards**: Cards in your collection
- **1 borrowed card**: A friend's card (once you unlock friend support)

This composition should be treated as your baseline planning assumption. While deck edits may be technically possible in some flows, changing cards mid-run resets bond progress and usually delays friendship timing enough to be a net loss.

---

## 3. Bond Progression & Friendship Training

### 3.1 Understanding Bond Levels

**Bond** is the relationship level between your Umamusume and each support card trainer. It represents how well your character knows and trusts the trainer.

**Bond Range**: 0 → 100 (maximum)

**Bond Progression:**

- Starts at **0** when you first include a card in your deck
- Increases by **5 points** per training session with that card
- Increases by **7 additional points** if the card has Charm status
- **Capped at 100**

**Journey Milestones:**

| Bond Level | Status | Effect |
| --- | --- | --- |
| 0–49 | **Orange** | Standard training bonus applies; no friendship training |
| 50–79 | **Light Orange** | Building familiarity; standard bonus continues; approaching friendship threshold |
| **80–100** | **Rainbow/Gold** | **Friendship Training activated**; +20% stat gain multiplier applies |

### 3.2 Friendship Training: The +20% Multiplier

When a support card reaches **bond level 80 or higher**, it becomes **rainbow-ready**. In this application's planner logic, Friendship Training is treated as active when **3 or more support cards** simultaneously reach bond 80+, unlocking a **1.2x multiplier** (20% bonus) to modeled stat gains.

**Example:**

```text
Basic stat gain: 10 points
Support card bonus: +7% = 10.7 points
Without friendship: 10.7 points final

With 3 friendship-ready cards (bond ≥ 80):
Friendship multiplier applies: 10.7 × 1.2 = 12.84 points final
Bonus: +2.14 additional points
```text

**Key Timeline:**

- **Early turns (bond 0–50)**: Build bond with your preferred cards; don't expect friendship training yet
- **Mid-career (bond 50–79)**: Continue using same cards; bond is approaching threshold
- **Late career (bond 80+)**: Friendship training activates for 3+ cards; expect 15–30% total stat boost

### 3.3 Friendship Training Activation Requirements

Friendship training requires **3 or more support cards** to have bond ≥ 80 simultaneously. When this threshold is met in the planner:

- The training prediction layer marks friendship as **ACTIVE**
- Total stat gain boost uses the planner's modeled **1.2x friendship multiplier**
- AI recommendations begin prioritizing trainings that leverage the active friendship state

**Strategic Implication:**

Focus on consistently using the same 3–5 core support cards throughout your career. By turn 60–80, you'll have 3+ cards in the 80+ bond range, triggering friendship training for a significant late-game power spike.

---

## 4. Multi-Card Synergy

### 4.1 The +5% Per-Card Bonus

Beyond individual card bonuses, **every support card in your deck contributes a flat +5% bonus** to the training type it specializes in, regardless of bond level. This bonus stacks with other effects.

### Example: Speed Training with a Full Deck

You're training Speed. Your deck contains:

- 3 Speed support cards (each +5% Speed training bonus)
- 1 Stamina card (+0% for Speed, but +5% if training Stamina)
- 1 Power card (+0% for Speed)
- 1 Friend (Pal) card (+5% for Speed)

**Speed Training Calculation:**

```text
Base stat gain: 10 points
Personal/aptitude bonus: +5% = 10.5 points
Support card type bonuses: 3 × 5% = +15% = 12.075... ≈ 12
Friend card bonus: +5% = 12.6 points
With friendship training ACTIVE (3 cards at bond ≥ 80): 12.6 × 1.2 = 15.12 points
```

### 4.2 Multi-Card Synergy Patterns

**Specialized Synergy** (recommended for focused builds):

- 4 cards of primary stat (e.g., 4 Speed cards)
- 1 secondary stat for balance (e.g., 1 Stamina card)
- 1 friend/pal card as flexible filler
- Result: Heavy bonus to primary stat, slight boost to secondary, well-balanced

**Balanced Synergy** (recommended for all-rounder builds):

- 1–2 cards per stat type (e.g., 1 Speed, 1 Stamina, 1 Power, 1 Guts, 1 Wit)
- 1 friend/pal card for flexibility
- Result: Moderate bonus to all stats, helps avoid extreme weaknesses

**Flexible Synergy** (recommended for exploration):

- Mix of rarity levels and specializations
- 2–3 cards of primary stat
- Remaining cards chosen for their event triggers or skills
- Result: Unique effects from individual cards, less predictable but fun

---

## 5. Deck Composition Strategies

### 5.1 Before You Start: Deck Selection

**For Speed-Focused Characters:**

```text
Primary (Speed): 3–4 SSR/SR cards with Speed bonus
Secondary (Stamina): 1 card for distance races
Flexible: 1 Friend (Pal) card
Goal: Aggressive Speed growth, support for stamina races
```text

**For Stamina-Focused Characters:**

```text
Primary (Stamina): 3–4 SSR/SR cards with Stamina bonus
Secondary (Power): 1 card for grade races
Flexible: 1 Friend (Pal) card
Goal: Dominant Stamina growth, power support for grades
```

**For All-Rounder Characters:**

```text
Diverse Mix: 1–2 per specialization (Speed, Stamina, Power, Guts, Wit)
Special: Add 1 Friend card if diversity is more important than specialization depth
Goal: No weak stats, flexible for any situation
```text

### 5.2 Limit Break Considerations

Each support card can be limit broken 0–4 times, increasing its bonus multiplier:

- **0 Limit Breaks**: Base bonus (1.0x)
- **1 Limit Break**: +10% to card bonus (1.1x)
- **2 Limit Breaks**: +20% to card bonus (1.2x)
- **3 Limit Breaks**: +30% to card bonus (1.3x)
- **4 Limit Breaks**: +40% to card bonus (1.4x)

**Strategic Tip**: Prioritize limit breaking your 3–5 core support cards. These cards will be in your deck for many runs, so investing in their limit breaks provides the best return on investment.

---

## 6. Scenario-Specific Guidance

### 6.1 URA Finale Strategy

**Context**: URA Finale is a **pure training scenario** focused on maximizing stat growth. Races are secondary; your goal is optimal stat development.

**Recommended Approach:**

- **Deck Focus**: Choose 4–5 cards all of the primary stat you're developing
- **Bond Priority**: Prioritize building bond with these core cards early; aim for 3+ at bond 80+ by turn 70
- **Training Pattern**: Consistently train the same stat to maximize synergy
- **Friendship Training**: Critical for late-game power spike (turns 70–90)
- **Friend Card**: Include 1 pal card to round out weak secondary stats

**Expected Progression:**

```text
Turns 1–30:  Building bond (0–30), standard bonuses active (+7–10%)
Turns 31–60: Bond accelerates (30–70), preparing for friendship threshold
Turns 61–90: Friendship training active (bond 80+), +20% additional multiplier kicks in
Turns 91+:   Peak efficiency; multiple friendship cards provide stacking multipliers
```

### 6.2 Unity Cup Strategy

**Context**: Unity Cup is a **team-based scenario** where individual card bonuses matter less. Focus shifts to:

- Specific support cards with event triggers for your Umamusume
- Cards that provide secondary stat support for team races
- Cards that unlock special interactions in story moments

**Recommended Approach:**

- **Deck Focus**: Choose cards that:
  - Trigger special events for your specific character
  - Provide bonuses to secondary stats needed for team races
  - Have story interactions with your character in Unity Cup
- **Bond Priority**: Build bond strategically, but don't force a single pattern
- **Training Pattern**: Vary training types to prepare for diverse race distances (1200m, 1400m, 1600m, 1800m, 2000m+)
- **Flexibility**: Be prepared to make mid-run adjustments if team composition changes
- **Friend Card**: Choose one with secondary stat bonus for balance

**Expected Progression:**

```text
Turns 1–20:  Focus on team race prep; build bond with "event trigger" cards
Turns 21–50: Balanced stat development; secondary stat emphasis
Turns 51–80: Team races increase; adjust training based on race schedule
Turns 81+:   Optimize remaining weaknesses; prepare for final evaluations
```text

### 6.3 Key Scenario Differences

| Aspect | URA Finale | Unity Cup |
| --- | --- | --- |
| **Primary Goal** | Maximize all stats | Balanced stats for team races |
| **Race Focus** | Single-character races | Team races count heavily |
| **Card Selection** | Primary stat specialization | Balanced coverage + event triggers |
| **Bond Strategy** | Rush 3+ to friendship training | Build organically; event-driven |
| **Friendship Training** | High priority for late-game spike | Lower priority; less impactful |
| **Synergy Emphasis** | Stat bonus stacking | Event trigger matching |

---

## 7. Friendship Training Activation

### 7.1 Step-by-Step: Building to Friendship Training

#### Step 1: Choose Your Core 3 Cards

Select 3 support cards you want to rely on heavily for friendship training. Pick cards with:

- High rarity (SSR preferred)
- Specialization matching your primary stat
- Character synergy (matching story/event triggers)

#### Step 2: Use Them Consistently (Turns 1–60)

Train the types that involve these cards' specializations. Move bond from 0 → 80.

- **Calculation**: 80 points ÷ 5 points per training = 16 training sessions minimum
- **Time Frame**: 16 trainings across ~50 turns + variance = achievable by turn 45–60

#### Step 3: Validate Bond Status

Use the **Training Prediction UI** to check current bond levels for each card:

- Look for cards showing "Rainbow" or "Bond 80+" status
- Once 3+ cards are listed as "Rainbow," friendship training is active

#### Step 4: Activate in Training Recommendation (Turns 61+)

Once friendship training is active:

- The AI Advisor will prioritize trainings using your 3+ friendship cards
- You'll receive recommendations highlighting the +20% bonus
- Stat gains will visibly increase for the same training type

### 7.2 Bonuses Applied to Friendship Training

When training with 3+ cards at bond ≥ 80:

**Formula:**

```text
Final Stat Gain = Base Gain × (1 + Support Type Bonus) × (1 + 0.05 × Card Count) × Friendship Multiplier
Final Stat Gain = Base Gain × (1 + support bonus %) × (1.30 for 6 cards) × 1.2 (friendship)
```

**Example with 3 Friendship Cards:**

```text
Base Speed gain: 10
Support card type bonus (3 Speed cards): 1.15 (15%)
Multi-card bonus (6 total cards): 1.30 (30%)
Friendship multiplier (3+ cards at 80+): 1.2 (20%)

Final = 10 × 1.15 × 1.30 × 1.2 = 18.18 points
(vs. 10 without any bonuses)
```text

This is **an 81% increase** from base, showcasing why friendship training matters.

---

## 8. Quick Reference

### Bond Level Progression

| Career Window | Typical Bond State (core cards) | Friendship Training Active? |
| --- | --- | --- |
| Early career | Bond building underway; few cards near 80 | ❌ Not yet |
| Mid-career | 1-2 cards often become rainbow-ready | ⚠️ Approaching activation |
| Late mid-career | 2-3 core cards can reach 80+ with consistent training | ⚠️ Likely soon |
| Late career | 3+ cards at bond 80+ with stable specialization focus | ✅ Usually active |
| Endgame | Core cards maintained at 80-100 bond | ✅ Peak efficiency |

### Card Selection Decision Tree

```text
START: What's your primary goal?
  │
  ├─ "MAX OUT ONE STAT" (URA Finale)
  │  └─ Choose 4 SSR/SR cards of same specialization
  │     └─ Add 1 secondary stat card + 1 pal card
  │     └─ Rush friendship training by turn 60
  │
  ├─ "BALANCED GROWTH" (All-rounder)
  │  └─ Choose 1–2 cards per specialization (Speed, Stamina, Power, Guts, Wit)
  │     └─ Add 1 pal card for flexibility
  │     └─ Build friendship training organically
  │
  └─ "TEAM RACES" (Unity Cup)
     └─ Choose cards that trigger story events for your character
        └─ Include secondary stat support
        └─ Focus on event triggers over pure stat bonuses
        └─ Friendship training is bonus, not priority
```

### Checklist: Before Starting Your Career

- [ ] I've identified my 3–5 core support cards for the run
- [ ] I know the primary stat specialization of my deck
- [ ] I understand bond starts at 0 and increases by 5 per training
- [ ] I know friendship training activates when 3+ cards reach bond 80
- [ ] I expect friendship training +20% multiplier in turns 60–70+
- [ ] I've identified any special event triggers from my chosen cards
- [ ] My deck composition is 5 owned + 1 borrowed card
- [ ] I'm ready to commit to consistent training patterns for the career

---

## Questions or Need Help?

For more technical details on support card mechanics, see:

- [SPEC-005: Support Card Management Technical Specification](../02-specs/SPEC-005_Support_Card_Management_Technical.md)
- [UF-006: Support Deck Building User Flow](../01-user-flows/UF-006_Support_Deck_Building_Flow.md)
- [SRS Section 3.5: Support Card Management](../00-core-docs/003_SRS_Software_Requirement_Specifications.md#35-support-card-management)

For AI-powered deck recommendations, use the **Deck Advisor** in the character creation wizard.
