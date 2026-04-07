# Inheritance System in Uma Musume: Pretty Derby (Legacies, Sparks, Inspiration)

**Document Version**: 1.0.0
**Date**: 2026-03-08
**Status**: Current

---

## Table of Contents

1. [Overview](#overview)
2. [Overview of the Inheritance System](#1-overview-of-the-inheritance-system)
3. [The Legacy Structure](#2-the-legacy-structure)
4. [Sparks (Inheritance Traits)](#3-sparks-inheritance-traits)
5. [When Inheritance Occurs](#4-when-inheritance-occurs)
6. [Blue Sparks (Stat Inheritance)](#5-blue-sparks-stat-inheritance)
7. [Pink Sparks (Aptitude Inheritance)](#6-pink-sparks-aptitude-inheritance)
8. [Green Sparks (Unique Skill Inheritance)](#7-green-sparks-unique-skill-inheritance)
9. [White Sparks (Skill and Race Factors)](#8-white-sparks-skill-and-race-factors)
10. [How Sparks Are Generated at the End of a Run](#9-how-sparks-are-generated-at-the-end-of-a-run)
11. [Affinity (Compatibility System)](#10-affinity-compatibility-system)
12. [Spark Inheritance Probability](#11-spark-inheritance-probability)
13. [Inspiration Event Outcomes](#12-inspiration-event-outcomes)
14. [Multi-Generation Optimization (Breeding Loop)](#13-multi-generation-optimization-breeding-loop)
15. [Differences Between JP and Global Servers](#14-differences-between-jp-and-global-servers)
16. [Strategic Importance of Inheritance](#15-strategic-importance-of-inheritance)
17. [Summary](#summary)

---

## Overview

Below is a detailed explanation of the inheritance system (also called Legacies, Sparks, or
Inspiration) in *Uma Musume: Pretty Derby*.
This mechanic exists in both the JP server and Global EN server and forms the long-term progression loop of the game.

## 1. Overview of the Inheritance System

Inheritance is the system where previously trained characters (Veterans) pass traits to future trainees.

When starting a new Career run:

1. You choose two Veteran Umamusume as Parents.
2. Each parent also has their own parents (Grandparents).
3. These six characters form the inheritance lineage.

During the career, traits from this lineage can pass down through Inspiration events, providing bonuses such as:

- Stat increases
- Aptitude upgrades
- Skill hints

This system is called Inspiration in-game.

Source: [Umamusume Wiki - Game:Inheritance](https://umamusu.wiki/Game%3AInheritance)

## 2. The Legacy Structure

Each career run uses a three-generation inheritance chain.

Structure:

```text
Grandparent A -\
               +-- Parent A -\
Grandparent B -/               \
                                +-- Trainee (current run)
Grandparent C -\               /
               +-- Parent B -/
Grandparent D -/
```

The trainee can inherit traits from:

- Both parents
- Their four grandparents

This creates six possible inheritance sources during a run.

Source: [Umamusume Wiki - Game:Inheritance](https://umamusu.wiki/Game%3AInheritance)

## 3. Sparks (Inheritance Traits)

The traits passed through inheritance are called Sparks or Factors.

They are generated after completing a career run and determine what a character can pass down to future generations.

Source: [uma.guide - Sparks and Inheritance](https://uma.guide/guides/sparks)

There are four main spark types:

| Spark Type | Function |
| --- | --- |
| Blue | Stat bonuses |
| Pink (Red) | Aptitude upgrades |
| Green | Unique skill inheritance |
| White | Skill hints or race bonuses |

These sparks activate during Inspiration events and modify the trainee's growth.

## 4. When Inheritance Occurs

Inheritance is triggered three times during a career run.

1. Initial Inspiration: At the start of the career
2. Classic Year Inspiration: Late March of Year 2
3. Senior Year Inspiration: Late March of Year 3

During these moments, the trainee receives bonuses depending on the sparks possessed by parents and grandparents.

Source: [Game8 - How to Get 3 Star Sparks](https://game8.co/games/Umamusume-Pretty-Derby/archives/541826)

## 5. Blue Sparks (Stat Inheritance)

Blue sparks provide stat increases.

Possible stats:

- Speed
- Stamina
- Power
- Guts
- Wisdom

At each inspiration event, the game calculates stat bonuses based on the total stars in blue sparks among the lineage.

Source: [uma.guide - Sparks and Inheritance](https://uma.guide/guides/sparks)

### Star Levels

Blue sparks come in 1-star, 2-star, or 3-star levels.

Star level determines the strength of inheritance.

Example probability of star level depending on final stat value:

| Final Stat | 1-star | 2-star | 3-star |
| --- | --- | --- | --- |
| Less than 600 | 90% | 10% | 0% |
| 600 to 1100 | 45% | 50% | 5% |
| Greater than 1100 | 20% | 70% | 10% |

Higher stats increase the chance of higher star sparks.

Source: [uma.guide - Sparks and Inheritance](https://uma.guide/guides/sparks)

## 6. Pink Sparks (Aptitude Inheritance)

Pink sparks increase aptitudes, which affect race suitability.

Aptitudes include:

### Distance

- Sprint
- Mile
- Medium
- Long

### Surface

- Turf
- Dirt

### Running Style

- Front
- Pace
- Late
- End closer

Pink sparks can raise aptitude ranks.

Example progression:

```text
E -> D -> C -> B -> A
```

The number of stars determines how many rank increases occur.

Source: [UmaGuide - Legacies](https://umagui.de/guide-content/Career/Legacies.html)

Example requirement:

| Target Rank | Stars Required |
| --- | --- |
| D | 1 |
| C | +3 |
| B | +3 |
| A | +3 |

However, the initial inheritance can only raise aptitude up to A rank.

Source: [UmaGuide - Legacies](https://umagui.de/guide-content/Career/Legacies.html)

Higher ranks like S aptitude can only occur through inspiration events later in the career.

## 7. Green Sparks (Unique Skill Inheritance)

Green sparks pass down hints for a character's unique skill.

Characteristics:

- Usually tied to the parent's unique ability
- Provides a weaker version or hint of that ability
- Only available if the parent is 3-star rarity or higher

These sparks can significantly influence the skill builds available to the trainee.

Source: [Game8 - How to Get 3 Star Sparks](https://game8.co/games/Umamusume-Pretty-Derby/archives/541826)

## 8. White Sparks (Skill and Race Factors)

White sparks represent skill or race-based bonuses.

They can originate from:

- Skills learned during the career
- G1 race victories
- Scenario completion

Examples include:

- Skill hints
- Small stat bonuses
- Scenario bonuses

Winning G1 races can produce race-specific white sparks.

Source: [Prydwen Institute - Legacies Guide](https://d2ankz0m1a0dsp.cloudfront.net/umamusume/guides/legacies-guide/)

## 9. How Sparks Are Generated at the End of a Run

When a career ends, the trained Uma generates new sparks.

Spark generation depends on:

- Final stats
- Learned skills
- Races won
- Scenario completion

Example probabilities:

| Trait Source | Base Chance |
| --- | --- |
| Normal skill to white spark | 20% |
| Circle-double skill to white spark | 25% |
| Gold skill to white spark | 40% |

Additional bonuses occur if parents already possess the same spark.

Source: [Prydwen Institute - Legacies Guide](https://d2ankz0m1a0dsp.cloudfront.net/umamusume/guides/legacies-guide/)

## 10. Affinity (Compatibility System)

Inheritance effectiveness depends heavily on affinity between characters.

Affinity is represented by symbols:

| Symbol | Meaning |
| --- | --- |
| Double-circle | High compatibility |
| Circle | Moderate compatibility |
| Triangle | Low compatibility |

Higher affinity increases the probability that sparks activate during inspiration events.

Source: [GameTora - Legacies Guide](https://gametora.com/umamusume/inheritance-guide)

Affinity is calculated based on:

- Character relationships
- Shared race victories
- Similar aptitudes

## 11. Spark Inheritance Probability

The probability of inheriting sparks depends on star level and affinity.

Example base inheritance rates:

| Spark Type | 1-star | 2-star | 3-star |
| --- | --- | --- | --- |
| Blue | 70% | 80% | 90% |
| Pink | 1% | 3% | 5% |
| Green | 5% | 10% | 15% |
| White | 3% | 6% | 9% |

These values are then multiplied by affinity bonuses.

Source: [Uma Reference - Chance of Inheriting
Sparks](https://www.umareference.com/guide/legacies/chance-of-inheriting-sparks)

Grandparent inheritance probabilities are usually half of parent values.

## 12. Inspiration Event Outcomes

When an inspiration event occurs, the trainee may receive:

- Stat boosts from blue sparks
- Aptitude upgrades from pink sparks
- Skill hints from green or white sparks

Stat bonuses are guaranteed, but other spark effects occur randomly depending on inheritance probabilities.

## 13. Multi-Generation Optimization (Breeding Loop)

High-level players often create inheritance loops.

Example workflow:

1. Train a character focused on Speed factors
2. Train another focused on Stamina factors
3. Use them as parents
4. Produce a new generation with both traits

Over multiple generations, this produces characters with:

- Strong base stats
- Improved aptitudes
- Powerful inherited skills

This loop is one of the core long-term progression mechanics of the game.

## 14. Differences Between JP and Global Servers

The core inheritance system is identical in both versions.

However, the JP server includes later features, such as:

- Additional scenario sparks
- More inheritance events
- Spark reroll mechanics

These features are gradually released on the Global server over time.

## 15. Strategic Importance of Inheritance

Inheritance dramatically affects training efficiency.

Benefits include:

- Higher starting stats
- Improved race aptitude
- Access to rare skills
- Better compatibility with support decks

Because of this, high-level players spend significant time farming strong parents with optimal sparks.

## Summary

The inheritance system is essentially a genetic progression system that connects all career runs.

Key mechanics include:

| Mechanic | Function |
| --- | --- |
| Legacy selection | Choose parents for the run |
| Sparks | Traits generated after training |
| Inspiration events | Moments when inheritance activates |
| Affinity | Compatibility affecting inheritance probability |
| Star levels | Determine spark strength |
| Multi-generation loops | Optimize future trainees |

This system ensures that each career run strengthens future runs, creating the long-term gameplay
loop that defines *Uma Musume* training meta.

---

If you want, I can also explain advanced inheritance mechanics used by high-level players, such as:

- Inheritance loops and factor farming strategies
- How players create 9-star parents
- Why S-rank distance aptitude is extremely valuable in PvP
