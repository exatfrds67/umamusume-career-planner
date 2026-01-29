---
name: uma-musume-domain-knowledge
description: Domain knowledge for Uma Musume Pretty Derby game mechanics, character stats, training system, and career planning. Use when working with game-specific features, calculations, or business logic.
---

# Uma Musume Pretty Derby Domain Knowledge

## Overview

This skill provides comprehensive domain knowledge for the Uma Musume Pretty Derby Career Planner application, including game mechanics, stat systems, training calculations, and career planning strategies.

## Core Game Concepts

### Character Stats (5 Primary Stats)

1. **Speed (スピード)**: Affects running speed and acceleration
   - Range: 0-1200
   - Critical for all race types
   - Primary stat for most characters

2. **Stamina (スタミナ)**: Affects endurance and energy consumption
   - Range: 0-1200
   - Essential for long-distance races
   - Prevents slowdown in final stretch

3. **Power (パワー)**: Affects acceleration and ability to maintain speed
   - Range: 0-1200
   - Important for uphill sections
   - Helps maintain position in crowded fields

4. **Guts (根性)**: Affects recovery and performance when tired
   - Range: 0-1200
   - Helps maintain speed when stamina is low
   - Important for comeback mechanics

5. **Wit (賢さ)**: Affects skill activation rate and learning
   - Range: 0-1200
   - Increases skill trigger frequency
   - Improves training efficiency

### Aptitude Grades

Characters have aptitudes for different race conditions:

**Grade Scale**: G → F → E → D → C → B → A → S → SS

**Aptitude Types**:

1. **Distance Aptitudes**:
   - Short (短距離): <1400m
   - Mile (マイル): 1400-1800m
   - Medium (中距離): 1800-2400m
   - Long (長距離): >2400m

2. **Surface Aptitudes**:
   - Turf (芝): Grass tracks
   - Dirt (ダート): Dirt tracks

3. **Running Style Aptitudes**:
   - Escape (逃げ): Front runner
   - Leading (先行): Stalker
   - Insert (差し): Closer
   - Pursuit (追込): Deep closer

**Aptitude Effects**:

- SS/S: Optimal performance
- A/B: Good performance
- C/D: Reduced performance
- E/F/G: Significantly reduced performance

### Growth Rates

Each character has growth rate modifiers for each stat:

- Range: 0% to 200%
- Affects training efficiency
- Higher growth rate = more stat gains per training

## Training System

### Training Facilities (5 Types)

1. **Speed Training (スピード練習)**
   - Primary: Speed gains
   - Secondary: Power gains
   - Recommended for: Speed-focused builds

2. **Stamina Training (スタミナ練習)**
   - Primary: Stamina gains
   - Secondary: Guts gains
   - Recommended for: Long-distance runners

3. **Power Training (パワー練習)**
   - Primary: Power gains
   - Secondary: Speed gains
   - Recommended for: Balanced builds

4. **Guts Training (根性練習)**
   - Primary: Guts gains
   - Secondary: Stamina gains
   - Recommended for: Comeback strategies

5. **Wit Training (賢さ練習)**
   - Primary: Wit gains
   - Secondary: All stats (small)
   - Recommended for: Skill-focused builds

### Training Mechanics

**Stat Gain Calculation**:

```
Base Gain × Growth Rate × Support Card Bonus × Facility Level × Random Factor
```

**Factors Affecting Training**:

- Character mood (絶好調/好調/普通/不調/絶不調)
- Energy level (体力)
- Support card bonds
- Facility level
- Training partners present
- Random variation (±10%)

### Support Cards

**Card Types** (6 slots in deck):

1. Speed (スピード)
2. Stamina (スタミナ)
3. Power (パワー)
4. Guts (根性)
5. Wit (賢さ)
6. Friend (友人)

**Card Attributes**:

- Rarity: R, SR, SSR
- Limit Break: ★0 to ★4
- Bond Level: 1-5
- Unique skills and events

**Support Card Effects**:

- Stat training bonuses
- Skill hints
- Event bonuses
- Friendship/bond bonuses

## Skill System

### Skill Types

1. **Normal Skills (通常スキル)**
   - Base skills available to all
   - Lower SP cost
   - Can evolve to Rare skills

2. **Rare Skills (レアスキル)**
   - Enhanced versions of Normal skills
   - Higher SP cost
   - Better effects

3. **Unique Skills (固有スキル)**
   - Character-specific skills
   - Cannot be learned by others
   - Powerful effects

### Skill Acquisition

**SP (Skill Points)**:

- Earned through training
- Used to purchase skills
- Limited resource requiring planning

**Hint System**:

- Skills can have hint levels: 1-5
- Each level reduces SP cost:
  - Level 1: 10% discount
  - Level 2: 20% discount
  - Level 3: 30% discount
  - Level 4: 35% discount
  - Level 5: 40% discount (max)

**Skill Evolution**:

- Normal skills can evolve to Rare
- Requires specific conditions
- Example: "Go with the Flow" → "Lane Legerdemain"

### Skill Categories

1. **Speed Skills**: Increase running speed
2. **Acceleration Skills**: Improve acceleration
3. **Recovery Skills**: Restore stamina
4. **Position Skills**: Affect race positioning
5. **Condition Skills**: Improve performance in specific conditions
6. **Debuff Skills**: Hinder opponents

## Career Run Structure

### Turn System

- Total Turns: 60-70 (varies by scenario)
- Each turn represents a training period
- Actions per turn:
  - Training (5 facilities)
  - Rest (recover energy)
  - Race (participate in race)
  - Outing (improve mood/bonds)

### Career Stages

1. **Junior (ジュニア級)**: Turns 1-24
   - Focus: Building base stats
   - Races: Debut and early competitions

2. **Classic (クラシック級)**: Turns 25-48
   - Focus: Balanced development
   - Races: Major competitions (Derby, Oaks)

3. **Senior (シニア級)**: Turns 49-72
   - Focus: Final optimization
   - Races: Championship races

### Goal System

**Goal Types**:

1. **Mandatory Goals**: Must complete to progress
2. **Optional Goals**: Bonus rewards
3. **URA Finals**: Final championship series

**Goal Requirements**:

- Stat thresholds
- Skill requirements
- Aptitude requirements
- Race placement requirements

## Race System

### Race Mechanics

**Race Factors**:

1. Distance compatibility
2. Surface compatibility
3. Running style compatibility
4. Current stats
5. Equipped skills
6. Weather conditions
7. Track condition

**Weather Effects**:

- Sunny (晴れ): Normal conditions
- Cloudy (曇り): Slight stat reduction
- Rainy (雨): -5% performance on turf
- Snowy (雪): -15% performance

**Track Conditions**:

- Good (良): Normal
- Slightly Heavy (稍重): -3% performance
- Heavy (重): -7% performance
- Bad (不良): -12% performance

### Race Strategy

**Running Styles**:

1. **Escape (逃げ)**: Lead from start
   - Pros: Control pace, avoid traffic
   - Cons: High stamina consumption

2. **Leading (先行)**: Stay near front
   - Pros: Good position, flexible
   - Cons: Moderate stamina use

3. **Insert (差し)**: Mid-pack, close late
   - Pros: Energy conservation
   - Cons: Risk of traffic

4. **Pursuit (追込)**: Back of pack, late charge
   - Pros: Lowest stamina use early
   - Cons: Requires perfect timing

## Calculation Formulas

### Training Gain Estimation

```
Estimated Gain = Base × Growth Rate × (1 + Support Bonus) × Mood Modifier
```

**Mood Modifiers**:

- 絶好調 (Perfect): 1.20
- 好調 (Good): 1.10
- 普通 (Normal): 1.00
- 不調 (Bad): 0.90
- 絶不調 (Terrible): 0.80

### SP Calculation

```
Total SP = Base SP + Training Bonus + Race Bonus + Event Bonus
```

**SP Sources**:

- Training: 1-3 SP per session
- Races: 5-15 SP based on placement
- Events: Variable (0-10 SP)

### Skill Cost with Hints

```
Final Cost = Base Cost × (1 - Hint Discount)
```

Example:

- Base Cost: 100 SP
- Hint Level 3: 30% discount
- Final Cost: 100 × 0.70 = 70 SP

## Grade System

### Final Grade Calculation

Based on multiple factors:

1. **Stat Total**: Sum of all 5 stats
2. **Skill Quality**: Number and rarity of skills
3. **Race Results**: Performance in key races
4. **Aptitudes**: Compatibility ratings
5. **Fan Count**: Total fans earned

**Grade Tiers**:

- SS+: Elite (>15,000 total stats)
- SS: Excellent (14,000-15,000)
- S+: Very Good (13,000-14,000)
- S: Good (12,000-13,000)
- A+: Above Average (11,000-12,000)
- A: Average (10,000-11,000)
- B+: Below Average (<10,000)

## Optimization Strategies

### Stat Distribution

**Balanced Build**:

- All stats: 800-1000
- Good for versatility
- Suitable for most races

**Specialized Build**:

- Primary stats: 1000-1200
- Secondary stats: 600-800
- Tertiary stats: 400-600
- Optimized for specific race types

### SP Management

**Priority System**:

1. Essential skills for running style
2. Condition-specific skills
3. Recovery skills
4. Bonus skills

**Hint Optimization**:

- Wait for higher hint levels when possible
- Balance between cost and necessity
- Consider skill evolution paths

### Support Deck Building

**Deck Composition**:

- 2-3 cards matching primary stat
- 1-2 cards for secondary stats
- 1 Friend card for events
- Consider card synergies

**Card Selection Criteria**:

- Training bonuses
- Skill hints offered
- Event quality
- Bond bonuses

## Common Patterns

### Training Patterns

**Early Game (Turns 1-24)**:

- Focus on base stat building
- Establish support card bonds
- Collect skill hints

**Mid Game (Turns 25-48)**:

- Balanced stat development
- Acquire core skills
- Prepare for major races

**Late Game (Turns 49-72)**:

- Final stat optimization
- Complete skill set
- Race-focused training

### Race Scheduling

**Optimal Race Frequency**:

- 1-2 races per stage
- Balance between SP gain and training time
- Prioritize mandatory goals

## Database Schema Reference

### Key Models

1. **Character**: Base character data
2. **Career**: Individual career run
3. **StatProgress**: Turn-by-turn stat tracking
4. **SkillAcquisition**: Skills learned and equipped
5. **TrainingSession**: Training history
6. **Race**: Race participation and results
7. **SupportCard**: Support card inventory
8. **SupportDeck**: Deck configuration

### Canonical Field Names

- `speed`, `stamina`, `power`, `guts`, `wit`
- `total_sp_available`, `total_sp_spent`
- `turn_number`, `career_stage`
- `aptitude_grade` (G/F/E/D/C/B/A/S/SS)
- `growth_rate_percentage`

## External Data Sources

### APIs

1. **umapyoi.net**: Character data, skill database
2. **UmamusumeDB.com**: Community tools and calculators

### Data Sync

- Character stats and aptitudes
- Skill database and evolution paths
- Support card meta rankings
- Race schedules and requirements

## Related Documentation

- **PRD-001**: Character Management (character stats, aptitudes)
- **PRD-002**: Training Optimization (training system mechanics)
- **PRD-003**: Race Strategy (race mechanics and strategy)
- **PRD-004**: Skill Management (skill system and SP)
- **PRD-005**: Support Card Management (support card system)
- **SPEC-002**: Training calculation specifications
- **SPEC-003**: Race strategy specifications
- **SPEC-004**: Skill system specifications
- **SPEC-005**: Support card specifications
- `docs/00-core-docs/009_DBD_Database_Documentation.md`: Database schema
- `docs/00-core-docs/000_MASTER_GLOSSARY.md`: Canonical terminology
- `docs/research/game-mechanics-research-report.md`: Game mechanics research
- `docs/feature-documentation/SKILL_SYSTEM_DOCUMENTATION.md`: Skill system details
- `docs/external-api-integration/SKILLS_DATA_IMPLEMENTATION.md`: Skills API integration
- `docs/external-api-integration/SUPPORT_CARD_IMPLEMENTATION_ROADMAP.md`: Support cards

## Version Information

- Game Version: Current (JP/Global)
- Application Version: v2.0.0
- Last Updated: 2026-01-29
