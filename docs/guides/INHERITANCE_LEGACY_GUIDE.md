# Inheritance and Legacy System Guide

**Document Version**: 1.0.0
**Date**: 2026-03-08
**Status**: Current

---

## Table of Contents

1. [Overview](#overview)
2. [Generational Chain Structure](#generational-chain-structure)
3. [Inspiration Events](#inspiration-events)
4. [Spark Types](#spark-types)
5. [Star Level Probabilities](#star-level-probabilities)
6. [Affinity System](#affinity-system)
7. [Parent Selection Strategy](#parent-selection-strategy)
8. [Multi-Generation Optimization Loop](#multi-generation-optimization-loop)
9. [Inheritance Planning Checklist](#inheritance-planning-checklist)

---

## Overview

The Inheritance (Legacy) System allows stats and skills acquired during one career run to carry forward to future runs
through a lineage of parent and grandparent characters. Understanding this system is essential for
long-term account progression,
since each generation builds on the improvements of the previous one.

### Core Concepts

- You can inherit stat bonuses and skills from up to **6 sources** across **3 generations**
- Inheritance is applied at the **Inspiration Events** during a career run
- The quality of inheritance depends on **spark type**, **star level**, and **affinity** between
parent and child characters
- A well-structured inheritance chain can dramatically accelerate stat growth over multiple runs

---

## Generational Chain Structure

Each career run can draw from a 3-generation chain of parent characters:

```text
Generation 3 (Grandparents × 4)
        ↓           ↓
Generation 2 (Parents × 2)
        ↓
Generation 1 (Current run / Child)
```text

| Generation | Role | Count |
| --- | --- | --- |
| Generation 3 | Grandparents | 4 characters |
| Generation 2 | Parents | 2 characters |
| Generation 1 | Current run | 1 character (your active run) |

Each parent contributes inheritance at one of the three Inspiration Events during the career.
Grandparents contribute through the parents at the same events.

---

## Inspiration Events

There are exactly **3 Inspiration Events** per career run, at fixed points in the timeline:

| Event | Timing | Career Stage |
| --- | --- | --- |
| Event 1 | Start of career | Pre-Debut / Junior |
| Event 2 | Year 2 late March | Classic |
| Event 3 | Year 3 late March | Senior |

At each Inspiration Event you:

1. Receive spark(s) from your configured parent lineage
2. Choose which inherited skills or stat bonuses to apply from those sparks
3. Optionally unlock evolutions of existing skills if prerequisites are met

### Event Timing Strategy

- **Event 1** (start): Best for foundational stat bonuses that compound over the full career
- **Event 2** (Year 2 late March): Classic-era skills and mid-career stat fills
- **Event 3** (Year 3 late March): Senior-era skills and final stat boosts before the climax race

Since Event 1 provides the most compounding time, having a strong parent with high-value skills ready for Event 1 is the
highest-ROI inheritance investment.

---

## Spark Types

Inspiration Events produce one of four spark types. Each spark type delivers a different category of benefit:

| Spark | Color | Primary Benefit |
| --- | --- | --- |
| Blue | Blue | Stat bonuses (Speed, Stamina, Power, Guts, or Wit) |
| Pink | Pink | Skill acquisition (inherits skills from parent) |
| Green | Green | Stat growth rate improvement (multiplicative) |
| White | White | SP (Skill Points) bonus |

### Spark Type Details

#### Blue Spark — Stat Bonus

The most common spark type. Provides a direct flat stat bonus applied immediately at the Inspiration Event.

- The number of stat points granted scales with the **star level** of the spark (see [Star Level
Probabilities](#star-level-probabilities))
- A 3★ Blue spark from a parent with 1100+ in a stat provides the largest single-event stat jump
available through inheritance
- Best used when targetting a specific stat to reach a tier threshold

#### Pink Spark — Skill Inheritance

Inherits one or more skills directly from the parent's skill set.

- The parent's skill list is available; you pick which to inherit
- Hint-discounted skills from the parent's run carry over automatically
- Pink sparks are rare; when they fire, prioritise unique skills or expensive hint-required skills

#### Green Spark — Growth Rate Bonus

Increases the character's **growth rate multiplier** for one or more stats.
Growth rate affects every subsequent training action, making this the highest long-term value spark type.

- Even a small growth rate increase (e.g., +2%) compounds across all remaining training turns
- Best applied at **Event 1** to maximise compounding time; late-game Green sparks have diminished value
- Target: traits → aptitude-aligned stats (e.g., Green spark to a Speed-aptitude character's Speed growth rate)

#### White Spark — SP Bonus

Grants a lump sum of SP used to purchase skills at career end.

- Useful if the skill you want is expensive and un-hinted
- Lower priority than Green or Blue sparks from high-stat parents
- Can be strategically valuable for characters who struggle to naturally accumulate SP

---

## Star Level Probabilities

Each spark is generated with a **star level** (1★, 2★, or 3★) that scales its benefit.
The probability of higher star levels increases with the parent's stats.

### Blue Spark Star Probabilities

| Parent's Relevant Stat | 1★ Probability | 2★ Probability | 3★ Probability |
| --- | --- | --- | --- |
| < 600 | ~70% | ~20% | ~10% |
| 600–1100 | ~20% | ~70% | ~10% |
| > 1100 | Lower | Lower | Higher |

> The exact breakpoints shift based on the character's growth rate and aptitude,
> but the general rule holds: **push the parent's target stat above 1100 for the highest 3★ chance**.

### Green Spark Star Probabilities

| Star Level | Approx. Probability |
| --- | --- |
| 1★ | ~5–15% |
| 2★ | ~10–20% |
| 3★ | ~5–15% |

Green sparks are less common overall; each star level represents a larger growth rate increment.

### Pink Spark Star Probabilities

| Star Level | Approx. Probability |
| --- | --- |
| 1★ | ~1–5% |
| 2★ | ~3–10% |
| 3★ | ~1–5% |

Pink sparks are the rarest type. When they occur at 3★, they can inherit an extra skill or a higher-
tier skill evolution.

### White Spark Star Probabilities

| Star Level | Approx. Probability |
| --- | --- |
| 1★ | ~3% |
| 2★ | ~6% |
| 3★ | ~9% |

---

## Affinity System

Each character and parent pairing has an **affinity rating** that modifies spark quality and probability.

| Rating | Symbol | Effect |
| --- | --- | --- |
| High Affinity | ◎ (double circle) | Increased spark star level probability, bonus skill options |
| Standard Affinity | ○ (single circle) | Normal spark probabilities |
| Low Affinity | △ (triangle) | Reduced spark probabilities, fewer skill options |

### Affinity Factors

Affinity is influenced by:

- **Race distance match**: Parent and child trained for the same optimal distance → higher affinity
- **Running style match**: Same running style preference → affinity bonus
- **Character lore relationship**: Certain character pairs have lore-based high affinity (e.g.,
trainer connections, senpai/kouhai relationships)
- **Scenario match**: Both characters completed the same scenario mode (URA Finale, Unity Cup, etc.)

### Affinity Planning

- Always check the affinity rating before finalising your parent selection
- A ◎ affinity parent with slightly lower stats is often better than a ○ parent with marginally higher stats
- Use the affinity filter in the parent selection screen to find hidden high-affinity matches

---

## Parent Selection Strategy

### Criteria in Priority Order

1. **Affinity rating** — ◎ parents should be strongly preferred over ○ or △ parents
2. **Stat level in target stat** — push beyond 1100 for 3★ Blue spark eligibility
3. **Skill library quality** — does the parent have hint-discounted or unique skills worth inheriting via Pink spark?
4. **Growth rate bonus availability** — if the parent had Green sparks in their own career, their
child may pass those growth rates forward
5. **Scenario alignment** — matching career scenario increases spark quality modestly

### Common Mistakes to Avoid

- Selecting parents purely by raw stats while ignoring ◎ affinity options
- Using the same parent lineage repeatedly without farming stat improvements in grandparents
- Ignoring Event 1 — always have your highest-value parent ready for the first Inspiration Event
- Spending Pink spark inherits on cheap/common skills instead of expensive or unique skills

---

## Multi-Generation Optimization Loop

The inheritance system rewards iterative improvement across multiple career runs:

### The Loop

```text
Run N (Grandparent level)
  → Maximise target stat above 1100
  → Collect hint-discounted unique skill

Run N+1 (Parent level)
  → Inherit 3★ Blue spark stat bonus from Grandparent at Event 1
  → Acquire Pink spark skills if available
  → Push own stats higher than Run N

Run N+2 (Child / Current)
  → Inherit 3★ Blue spark from Parent (who now has higher base stats)
  → Compound growth rate bonuses from Green sparks above
  → Reach stat targets faster due to multi-gen bonuses
```

### Acceleration Targets by Generation

| Generation | Realistic Target (Primary Stat) |
| --- | --- |
| Generation 1 (no inheritance) | 800–900 at career end |
| Generation 2 (single parent) | 950–1050 at career end |
| Generation 3 (optimized chain) | 1100–1300+ at career end |

### G1 Race Victories and Inheritance Value

Winning **G1 (Grade 1) races** during a parent's career increases:

- The number of skills available in the parent's Pink spark pool
- The parent's "achievement rating" that subtly boosts affinity with certain child characters
- The parent's own stat peaks (since G1 races require high stats to win)

For inheritance farming runs, prioritise winning at least 2–3 G1 races.

---

## Inheritance Planning Checklist

- [ ] Parent 1 and Parent 2 affinity ratings are ◎ or at minimum ○
- [ ] At least one parent has target stat above 1100 (for 3★ Blue spark eligibility)
- [ ] At least one parent has a desirable skill in their Pink spark pool (unique or expensive skills)
- [ ] Parent careers are assigned to maximise Event 1 overlap (highest-value parent fires at start)
- [ ] Green spark parents are assigned to stat-aligned children (Distance ace parent → Speed/Stamina child)
- [ ] Grandparent tier has been farmed at least 1 generation ahead of the child's stat targets
- [ ] Current run's growth rate aptitude aligns with Green spark availability in parent chain
