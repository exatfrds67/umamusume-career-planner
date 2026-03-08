# Skill System Guide

**Document Version**: 1.0.0
**Date**: 2026-03-08
**Status**: Current

---

## Table of Contents

1. [Overview](#overview)
2. [Skill Categories](#skill-categories)
3. [Wit Stat and Activation Probability](#wit-stat-and-activation-probability)
4. [Race Phase Triggers](#race-phase-triggers)
5. [Skill Duration and Distance Scaling](#skill-duration-and-distance-scaling)
6. [Running Style Synergies](#running-style-synergies)
7. [Skill Costs and Hint Discounts](#skill-costs-and-hint-discounts)
8. [Unique Skills](#unique-skills)
9. [Build Strategies by Running Style](#build-strategies-by-running-style)
10. [Skill Planning Checklist](#skill-planning-checklist)

---

## Overview

Skills in Uma Musume: Pretty Derby activate during races to provide temporary stat boosts or positional advantages.
Planning an effective skill set requires matching skills to your character's running style, race distance, and Wit stat.

A career run typically unlocks skills through:

- **Training events** — random hints from support cards' special events
- **Inspiration events** — 3 fixed events per career where you select skill upgrades
- **SP spending** — skills cost Skill Points (SP) during the Skill Selection screen

---

## Skill Categories

| Category | Effect | Example Skills |
| --- | --- | --- |
| Acceleration | Increases speed during race section | Sprint, Burst |
| Pace Control | Maintains running pace; prevents deceleration | Even Pace, Stamina Saver |
| Position | Adjusts track position / overtaking priority | Corner Rush, Lane Change |
| Condition | Boosts via race conditions (weather, track, distance) | Wet Track Expert, Long Distance Ace |
| Recovery | Restores stamina mid-race | Second Wind, Guts Recovery |

Stacking multiple skills from the same category typically has diminishing returns; aim for a balanced set that covers acceleration,
position, and at least one condition or recovery skill.

---

## Wit Stat and Activation Probability

The Wit stat governs the **probability** that a skill fires during a race. Without sufficient Wit, skills are unreliable regardless of build quality.

### Activation Formula

```text
Activation Chance = max(100 - 9000 / BaseWit, 20%)
```text

### Reference Table

| Wit Value | Activation Chance |
| --- | --- |
| 100 | 20% (floor) |
| 200 | 20% (floor) |
| 300 | ~70% |
| 400 | ~77.5% |
| 500 | ~82% |
| 600 | ~85% |
| 750 | ~88% |
| 900 | ~90% |
| 1200 | ~92.5% |

### Recommended Wit Targets

- **Minimum viable**: 300 Wit (~70% activation) — skills will still misfire frequently
- **Standard threshold**: 400+ Wit (~77.5%) — reliable enough for most shorter races
- **Competitive target**: 600+ Wit (~85%) — consistent activation in G1 and longer races
- **Distance specialist**: 750+ Wit (~88%) — recommended for 2400 m+ events

> **Key Insight**: Below 300 Wit, skills frequently fail to activate (floor is 20%).
> Prioritise Wit training in early-to-mid career even if it is not your primary stat.

---

## Race Phase Triggers

Skills are assigned a **trigger location** within a race, divided into four phases:

| Phase | Race Section | Typical Duration |
| --- | --- | --- |
| Start | Gates open → first 200-400 m | ~5-10 s |
| Middle | After start phase → final corner | ~30-60 s |
| Final Corner | Last major bend before the straight | ~8-15 s |
| Final Straight | Last 300-400 m to finish line | ~10-20 s |

### Phase Strategy Notes

- **Front runners** benefit most from Start and Middle phase acceleration — they need to reach the front early and hold pace
- **Pace setters** want Middle phase pace control to prevent over-exerting stamina
- **Position changers** (stalkers) need Final Corner position skills to move around opponents at the turn
- **End closers** need Final Straight acceleration so the burst fires at the right moment

Skills with a "random trigger" roll their activation RNG at a set point in the race; Wit affects the result of that roll.

---

## Skill Duration and Distance Scaling

Skill duration scales directly with race distance, so the same skill lasts longer in a 3200 m race than in a 1600 m sprint.

### Duration Formula

```text
Effective Duration = BaseDuration × (RaceDistance / 1000)
```

### Examples

| Skill Base Duration | Race Distance | Effective Duration |
| --- | --- | --- |
| 3.0 s | 1600 m | 4.8 s |
| 3.0 s | 2000 m | 6.0 s |
| 3.0 s | 2400 m | 7.2 s |
| 3.0 s | 3200 m | 9.6 s |

This means **distance specialists benefit disproportionately from duration-heavy skills**.
A skill that provides marginal value in a sprint can be decisive in a long-distance race.

---

## Running Style Synergies

Match skills to your character's running style to maximise race impact.

### Front Runner

- **Goal**: Reach the front before the middle phase; hold pace through the final corner
- **Priority phases**: Start, Middle
- **Recommended skill types**: Early acceleration, pace maintenance, position lock (prevent being overtaken in middle)
- **Avoid**: End-closer skills (Final Straight burst) — these fire too late for a front runner

### Pace Setter (Stalker)

- **Goal**: Sit just behind the leader in middle phase; surge at the final corner
- **Priority phases**: Middle, Final Corner
- **Recommended skill types**: Middle acceleration, corner position, stamina recovery mid-race
- **Avoid**: Pure start skills — pace setters intentionally hold back from the gates

### Position Changer (Difference Runner)

- **Goal**: Mid-pack in middle phase; use Final Corner to overtake multiple opponents
- **Priority phases**: Final Corner, Final Straight
- **Recommended skill types**: Corner speed, overtaking priority, final straight acceleration
- **Highlight**: Position skills that reduce lane change hesitation are highly effective here

### End Closer (Oikomi)

- **Goal**: Last position until final straight; one decisive burst to pass everyone
- **Priority phases**: Final Straight (exclusively)
- **Recommended skill types**: Final straight acceleration, overtaking power, stamina recovery (to survive the middle phase drain)
- **Risk**: If the field separates too far, even a perfect burst cannot close the gap; pair with a stamina recovery skill

---

## Skill Costs and Hint Discounts

Skills are purchased with **SP (Skill Points)** at the Skill Selection screen (end of career).

### SP Sources

- Each training action generates a small amount of SP
- Wit training generates slightly more SP than other stats
- Some support card events grant bonus SP
- Goals and race victories can grant SP rewards

### Hint Discount System

Support card events sometimes trigger **hints** — these reduce the SP cost of specific skills.

- A skill with no hints costs its full listed SP value
- Each hint level typically reduces cost by 20-25%
- A fully-hinted skill (3 hints) can cost roughly 50% of its base price
- **Strategy**: If a key skill has gone hint-discounted via training events, prioritise buying it over a cheaper un-hinted alternative

### Priority Buying Order

1. Unique skill (if available and cost is reasonable)
2. Fully-hinted expensive skills (maximise hint discount value)
3. Phase-appropriate skills matched to running style
4. Condition skills matching target race environment (turf/dirt, weather, distance)

---

## Unique Skills

Each character in Uma Musume has an exclusive **Unique Skill** that cannot be acquired by any other character.

- Unique skills are consistently stronger than standard skills of the same type
- They are unlocked through a specific **scenario event** tied to career progression
- Acquiring the unique skill is often the highest-priority SP investment in a career run
- The unique skill's activation phase aligns with the character's canonical running style

> If a character's unique skill is a Final Straight acceleration and your character is being trained as a Front Runner,
> consider re-evaluating the running style — characters are designed around their unique skill's optimal timing.

---

## Build Strategies by Running Style

### Recommended Skill Sets

#### Front Runner Build

| Slot | Skill Type | Phase | Priority |
| --- | --- | --- | --- |
| 1 | Unique skill | Varies | Must-have |
| 2 | Early acceleration | Start | High |
| 3 | Pace maintenance | Middle | High |
| 4 | Position lock | Middle | Medium |
| 5 | Condition match | Any | Medium |
| 6 | Recovery | Any | Low / flex |

#### End Closer Build

| Slot | Skill Type | Phase | Priority |
| --- | --- | --- | --- |
| 1 | Unique skill | Final Straight | Must-have |
| 2 | Final straight burst | Final Straight | High |
| 3 | Stamina recovery | Middle | High |
| 4 | Overtaking priority | Final Straight | Medium |
| 5 | Condition match | Any | Medium |
| 6 | Corner position | Final Corner | Low / flex |

---

## Skill Planning Checklist

- [ ] Wit stat is 400+ before final skill selection (aim for 600+ in G1 campaigns)
- [ ] Unique skill acquired
- [ ] At least 2 skills matching primary running style phase
- [ ] At least 1 condition skill matching main race target (distance, turf/dirt)
- [ ] At least 1 recovery or stamina skill in the set
- [ ] Hint-discounted skills prioritised in SP budget
- [ ] Skill phases do not conflict (not mixing front runner + end closer timing)
