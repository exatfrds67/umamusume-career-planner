# Unity Cup Scenario Mechanics - Comprehensive Research

**Document Type**: Research Documentation
**Version**: 1.0.0
**Date**: January 29, 2026
**Status**: Complete
**Sources**: Game8, GameTora, UmaReference, LDPlayer, VortexGaming

---

## Executive Summary

Unity Cup (known as "Aoharu Hai" in JP) is the second permanent career scenario in Umamusume: Pretty Derby, released November 6, 2025 on Global. Unlike URA Finale which focuses on a single character, Unity Cup emphasizes **team-based progression** where your trainee develops alongside teammates through special training mechanics and team races.

### Key Differentiators from URA Finale

- Training facility levels tied to **team stat rank** (not usage frequency)
- **Spirit Burst** mechanic for massive stat gains
- **Unity Training** (white flame indicators) for team progression
- Team races every 6 months affecting facility progression
- Higher stat caps and better inheritance factors

---

## 1. Team Race System

### 1.1 Schedule and Structure

Team races occur **every 6 months** throughout the career:

- **Round**: Round 1; **Timing**: After Junior Year Late December
- **Round**: Round 2; **Timing**: After Classic Year Late June
- **Round**: Round 3; **Timing**: After Classic Year Late December
- **Round**: Round 4; **Timing**: After Senior Year Late June
- **Round**: Finals; **Timing**: After Senior Year Late December

### 1.2 Team Composition

Your team consists of:

- **Your trainee** (main character)
- **6 support cards** (with bond gauges)
- **Random NPCs** (join after each team race)

**Total**: ~15 team members by finals

### 1.3 Race Structure

Each team race consists of **5 races** across all distances:

- Sprint
- Mile
- Medium
- Long
- Dirt

### Victory Condition**: Win at least**3 out of 5 races

### 1.4 Opponent Selection

Before each team race, choose from **3 NPC teams**:

- **Top option**: Hardest difficulty, best rewards
- **Middle option**: Balanced difficulty/rewards
- **Bottom option**: Easiest, minimal rewards

**Visual Indicators**: Double-circles (◎) show favorable matchups

- Look for teams with **3+ double-circles** for safer wins

**Recommended Strategy**:

- First 3 races: Choose **top option** (hardest)
- 4th race: Choose **middle option** (if struggling) or **top** (if confident)
- This maximizes Team Rank progression while minimizing loss risk

### 1.5 Victory Rewards

**Win (3+ races won)**:

- +50 all stats to **all team members**
- Team Rank increases
- Team Placement increases

**Loss/Draw**:

- +10 all stats to all team members
- Team Placement **decreases**
- Training facility levels fall behind

**Impact Calculation**:

- Missing 40 stats per uma × 5 stats × 15 umas = **3,000 total stats lost**
- This directly impacts training facility levels

### 1.6 Team Rank Progression

Team Rank determines **training facility levels**:

- **Team Rank**: F / G; **Facility Level**: Level 1; **Multiplier**: 1.0x
- **Team Rank**: D / E; **Facility Level**: Level 2; **Multiplier**: 1.2x
- **Team Rank**: B / C; **Facility Level**: Level 3; **Multiplier**: 1.4x
- **Team Rank**: A; **Facility Level**: Level 4; **Multiplier**: 1.6x
- **Team Rank**: S; **Facility Level**: Level 5; **Multiplier**: 2.0x

**Critical Difference from URA Finale**:

- URA: Facility level = usage frequency
- Unity Cup: Facility level = **team stat rank average**

**Example**: If your team's average Speed stat rank is A, Speed training facility is Level 4.

### 1.7 Team Placement Bonuses

Higher placements grant **Attribute Bonuses** to entire team:

- Improves all members' stats
- Creates positive feedback loop (stronger team → easier wins → higher rank)

**Benchmark**: Speed training reaching Level 4 by **beginning of Year 3** indicates good run pacing.

---

## 2. Spirit Burst Mechanic

### 2.1 Spirit Gauge Filling

**How to Fill**:

- Perform **Unity Training** (training with white flame indicators)
- Each Unity Training session fills the gauge incrementally
- **4 Unity Training sessions** = Full Spirit Gauge

**Important**: Spirit Gauge is **per team member** (each has their own gauge)

### 2.2 Triggering Spirit Burst

**When Available**:

- Spirit Gauge reaches 100%
- Next Unity Training with that member triggers Spirit Burst

**Strategic Timing**:

- You can **postpone** triggering Spirit Burst
- Wait for team member to appear in **optimal training facility**
- Spirit Burst can only trigger **once per team member**

**Example**: If a Speed-focused support card fills their gauge, wait until they appear in Speed training for maximum benefit.

### 2.3 Spirit Burst Bonuses

Spirit Burst provides **three benefits**:

#### A. Massive Stat Gains (Team Member)

Stat gains based on **training facility type** and **support card type**:

**Standard Spirit Burst Values**:

- **Explosion Type**: Speed; **Speed**: +150; **Stamina**: +80; **Power**: +110; **Guts**: +80; **Wit**: +70
- **Explosion Type**: Stamina; **Speed**: +80; **Stamina**: +150; **Power**: +80; **Guts**: +110; **Wit**: +70
- **Explosion Type**: Power; **Speed**: +80; **Stamina**: +110; **Power**: +150; **Guts**: +80; **Wit**: +70
- **Explosion Type**: Guts; **Speed**: +90; **Stamina**: +80; **Power**: +90; **Guts**: +150; **Wit**: +70
- **Explosion Type**: Wit; **Speed**: +110; **Stamina**: +80; **Power**: +80; **Guts**: +80; **Wit**: +150

**Growth Rate Modifier**: These values are affected by uma's growth bonuses.

- Example: Oguri Cap (20% Speed bonus) → Speed explosion gives +180 Speed

#### B. Moderate Stat Gains (Your Trainee)

**Trainee Stat Gains from Spirit Burst**:

- **Training Type**: Speed; **Speed**: +15; **Stamina**: -; **Power**: -; **Guts**: -; **Wit**: -; **SP**: +7
- **Training Type**: Stamina; **Speed**: -; **Stamina**: +15; **Power**: -; **Guts**: -; **Wit**: -; **SP**: +7
- **Training Type**: Power; **Speed**: -; **Stamina**: -; **Power**: +7; **Guts**: -; **Wit**: -; **SP**: +15
- **Training Type**: Guts; **Speed**: -; **Stamina**: -; **Power**: -; **Guts**: +33; **Wit**: -; **SP**: +15
- **Training Type**: Wit; **Speed**: -; **Stamina**: -; **Power**: -; **Guts**: -; **Wit**: +21; **SP**: +5

**Stat Cap**: Trainee gains capped at **+50 per stat** per Spirit Burst

- This cap is **separate** from other stat gains
- Can exceed 100 total stats in one training session

**Scenario-Linked Support Cards** (Enhanced Values):

- **Training Type**: Speed; **Speed**: +20; **Stamina**: -; **Power**: -; **Guts**: -; **Wit**: -; **SP**: +10
- **Training Type**: Stamina; **Speed**: -; **Stamina**: +20; **Power**: -; **Guts**: -; **Wit**: -; **SP**: +10
- **Training Type**: Power; **Speed**: -; **Stamina**: -; **Power**: +10; **Guts**: -; **Wit**: -; **SP**: +20
- **Training Type**: Guts; **Speed**: -; **Stamina**: -; **Power**: -; **Guts**: +55; **Wit**: -; **SP**: +20
- **Training Type**: Wit; **Speed**: -; **Stamina**: -; **Power**: -; **Guts**: -; **Wit**: +51; **SP**: +5

#### C. Skill Hints

**Hint Level**:

- Standard Spirit Burst: **Level 2** skill hint
- Scenario-linked support card: **Level 3** skill hint

### Skill Selection**: Random based on trainee's**A aptitudes

### 2.4 Energy Cost Impact

Spirit Burst **increases energy consumption** of that training:

- Additional cost: `(Number of Explosions) × 6 + (Number of Flames) - 1`
- Pal cards (e.g., Riko Kashimoto) **do not reduce** this additional cost
- They only reduce base training cost

### Exception**: Wit training Spirit Burst**increases energy recovery

### 2.5 Multiple Spirit Bursts

**Additive, Not Multiplicative**:

- 2+ Spirit Bursts in same training = stats add together
- No bonus multipliers for simultaneous explosions

---

## 3. Unity Training (White Flame Mechanic)

### 3.1 White Flame Indicator

**Visual Cue**: Team members with **white flame icon** in top-right corner

**Meaning**: That team member is available for **Special Training** (Unity Training)

**Who Can Participate**:

- Support cards
- Story NPCs
- Random team members

**Note**: Only support cards have bond gauges; NPCs cannot do friendship (rainbow) training

### 3.2 Unity Training Benefits

**When You Train with White Flames**:

1. **Team Member Stat Increase**:
   - All stats increase
   - Extra bonus to facility specialization
   - Example: Speed training → extra Speed + Power

2. **Spirit Gauge Fills**:
   - Progress toward Spirit Burst

3. **Team Rank Progression**:
   - Improves average team stats
   - Raises training facility levels

### 3.3 Trainee Stat Gains from Unity Training

**Minimum Requirement**: At least **2 white flames** in training

**Stat Gain Formula** (varies by facility and flame count):

#### Speed/Stamina/Power Training

- **# Flames**: 2; **Primary Stat**: +2; **Secondary Stat**: 0; **Skill Points**: 0
- **# Flames**: 3; **Primary Stat**: +3; **Secondary Stat**: +1; **Skill Points**: +1
- **# Flames**: 4+; **Primary Stat**: +5; **Secondary Stat**: +2; **Skill Points**: +2

#### Guts Training

- **# Flames**: 2; **Guts**: +2; **Speed**: 0; **Power**: 0; **Skill Points**: 0
- **# Flames**: 3; **Guts**: +2; **Speed**: +1; **Power**: +1; **Skill Points**: +1
- **# Flames**: 4+; **Guts**: +4; **Speed**: +2; **Power**: +1; **Skill Points**: +2

#### Wit Training

- **# Flames**: 2; **Wit**: +1; **Speed**: 0; **Skill Points**: 0
- **# Flames**: 3; **Wit**: +2; **Speed**: 0; **Skill Points**: +1
- **# Flames**: 4+; **Wit**: +3; **Speed**: +1; **Skill Points**: +2

**Scenario-Linked Support Card Bonus**:

- Adds **+1 to every stat** gained from Unity Training
- Applies when scenario-linked card participates

**Example**:

- Base: +2 Guts, +1 Speed, +1 Power, +1 SP
- With scenario-linked card: +3 Guts, +2 Speed, +2 Power, +2 SP

### 3.4 Energy Cost Increase

Unity Training costs **more energy** than normal training:

**Formula**: Base Cost + `(Number of Explosions) × 6 + (Number of Flames) - 1`

**Pal Card Limitation**: Only reduces **base cost**, not the additional Unity Training cost

**Recommended Pal Card**: Riko Kashimoto SSR

- Reduces energy costs
- Reduces failure chance
- Optimal for Unity Cup scenario

### 3.5 Strategic Guidelines

**General Rule**: Avoid training **without white flames** until Year 3 (when facilities are leveled)

**Exceptions**:

- Triple rainbow training (3+ support cards with friendship bonus)
- Particularly strong stat gains

**Unity Cup vs URA Finale**:

- Unity Cup: Prioritize white flames over optional races
- URA Finale: Optional races more important

---

## 4. Red Exclamation Marks

### 4.1 Two Types of Red Marks

#### Type 1: Friendship Bonus (Rainbow Training)

**Indicator**: Red exclamation mark on **support card** with bond gauge

**Effect**:

- +5 friendship points (on top of base +7 or +9 with Charming)
- Increased stat gains from friendship training
- Skill hint chance increase

**Applies To**: Support cards only (not NPCs)

#### Type 2: Unity Burst Indicator

**Indicator**: Red exclamation mark during **Unity Training** with white flames

**Effect**:

- Signals optimal Unity Training opportunity
- Enhanced stat gains
- Spirit Gauge fill bonus

### 4.2 Why Never Skip Red Marks

**Multiplicative Bonuses**:

- Red marks indicate **stacked bonuses**
- Friendship bonus + Unity Training = massive gains
- Can combine with Spirit Burst for 100+ stat training

**Opportunity Cost**:

- Red marks are **rare and valuable**
- Missing them significantly slows progression
- Critical for reaching S rank team stats

**Example Scenario**:

- 3 white flames + 2 red marks + 1 Spirit Burst
- Result: 80+ stats, multiple skill hints, +15 friendship

### 4.3 Priority System

**Training Selection Priority**:

1. **Spirit Burst ready** + Red marks + White flames
2. Red marks + 3+ White flames
3. Triple rainbow (3+ support cards with friendship)
4. 2+ White flames
5. Single white flame (avoid until Year 3)

---

## 5. Facility Level Progression

### 5.1 Core Mechanic Difference

**URA Finale**:

- Facility level = number of times trained
- Independent per facility
- Predictable progression

**Unity Cup**:

- Facility level = **team stat rank average**
- Dependent on team performance
- Requires team race victories

### 5.2 Facility Level Multipliers

- **Level**: 1; **Team Rank**: F / G; **Stat Multiplier**: 1.0x; **Training Effectiveness**: Base
- **Level**: 2; **Team Rank**: D / E; **Stat Multiplier**: 1.2x; **Training Effectiveness**: +20%
- **Level**: 3; **Team Rank**: B / C; **Stat Multiplier**: 1.4x; **Training Effectiveness**: +40%
- **Level**: 4; **Team Rank**: A; **Stat Multiplier**: 1.6x; **Training Effectiveness**: +60%
- **Level**: 5; **Team Rank**: S; **Stat Multiplier**: 2.0x; **Training Effectiveness**: +100% (Double)

### 5.3 Team Stat Rank Calculation

**Per-Stat Basis**: Each stat (Speed, Stamina, Power, Guts, Wit) has separate team rank

**Calculation Method**:

1. Sum all team members' stat values
2. Divide by number of team members
3. Rank based on average

**Team Average Stat Thresholds**:

- **Team Average**: 0-199; **Training Level**: Level 1
- **Team Average**: 200-339; **Training Level**: Level 2
- **Team Average**: 340-509; **Training Level**: Level 3
- **Team Average**: 510-609; **Training Level**: Level 4
- **Team Average**: 610+; **Training Level**: Level 5

**Stat Cap per Team Member**: ~750-800 (varies by support card type)

### 5.4 Progression Benchmarks

**Good Run Indicators**:

- Main stat reaches **Level 4 by beginning of Year 3**
- Team Rank A or higher by Round 3
- Minimal team race losses (0-1 maximum)

**Poor Run Indicators**:

- Main stat still Level 2-3 in Year 3
- Multiple team race losses
- Team Rank stuck at B/C

### 5.5 Impact of Team Race Results

**Win (+50 all stats to all members)**:

- 50 × 5 stats × 15 members = +3,750 total team stats
- Significant facility level boost

**Loss (+10 all stats to all members)**:

- 10 × 5 stats × 15 members = +750 total team stats
- **Net loss**: -3,000 stats compared to win
- Can drop facility levels by 1-2 tiers

**Cascading Effect**:

- Lower facility levels → weaker training
- Weaker training → harder to win next team race
- Creates negative spiral if not corrected

---

## 6. Base Training Values

### 6.1 Global Server Values (Current)

**Level 1 Facility Base Stats** (no support cards, no growth bonuses):

- **Facility**: Speed; **Stat Gains**: +8 Speed, +4 Power, +2 SP; **Energy Cost**: -19
- **Facility**: Stamina; **Stat Gains**: +7 Stamina, +3 Guts, +2 SP; **Energy Cost**: -17
- **Facility**: Power; **Stat Gains**: +4 Stamina, +6 Power, +2 SP; **Energy Cost**: -18
- **Facility**: Guts; **Stat Gains**: +3 Speed, +3 Power, +6 Guts, +2 SP; **Energy Cost**: -20
- **Facility**: Wit; **Stat Gains**: +2 Speed, +6 Wit, +3 SP; **Energy Cost**: +5

### 6.2 Japanese Server Values (2023 Update)

**Note**: Global will likely receive these values in future update

- **Facility**: Speed; **Stat Gains**: +8 Speed, +4 Power, +4 SP; **Energy Cost**: -19
- **Facility**: Stamina; **Stat Gains**: +8 Stamina, +6 Guts, +4 SP; **Energy Cost**: -20
- **Facility**: Power; **Stat Gains**: +4 Stamina, +9 Power, +4 SP; **Energy Cost**: -20
- **Facility**: Guts; **Stat Gains**: +3 Speed, +3 Power, +6 Guts, +4 SP; **Energy Cost**: -20
- **Facility**: Wit; **Stat Gains**: +2 Speed, +6 Wit, +5 SP; **Energy Cost**: +5

**Key Changes**:

- Increased skill point gains (+2 SP → +4 SP for most facilities)
- Slightly adjusted stat distributions
- Stamina and Power training energy costs increased

---

## 7. Scenario-Specific Skills

### 7.1 "It's On!" Skill

**Unlock Condition**: Reach **S Team Rank** during career

**Effect**: Increase velocity when passing another runner mid-race

**Hint Levels**:

- Standard career: **+1 hint level**
- Scenario-linked character: **+3 hint levels**

**Scenario-Linked Characters**:

- Haru Urara
- Taiki Shuttle
- Matikanefukukitaru
- Rice Shower

### 7.2 Team Name Skills

**Selection Timing**: Junior Class, second half of September

**Requirement**: Character must be your trainee OR one of your support cards

### Gold Skill Reward**: Awarded if you**win Unity Cup Finals

- **Character**: Taiki Shuttle; **Team Name (EN)**: Happy Hoppers; **Gold Skill**: (Gold skill)
- **Character**: Matikanefukukitaru; **Team Name (EN)**: Sunny Runners; **Gold Skill**: (Gold skill)
- **Character**: Haru Urara; **Team Name (EN)**: Carrot Pudding; **Gold Skill**: (Gold skill)
- **Character**: Rice Shower; **Team Name (EN)**: Blue Bloom; **Gold Skill**: (Gold skill)
- **Character**: None of above; **Team Name (EN)**: Team Carrot; **Gold Skill**: (Gold skill)

### 7.3 S+ Team Rank Bonus

**S Rank Achievement**:

- Automatic event triggers
- Awards hint for scenario skill
- **Level 1 hint** (standard)
- **Level 3 hint** (scenario-linked character)

**S+ Rank Achievement**:

- Second hint for same skill
- Can **max out hint** (Level 5) with scenario-linked character

---

## 8. Stat Caps

### 8.1 Global Server (Launch)

- **Stat**: Speed; **Cap**: 1200
- **Stat**: Stamina; **Cap**: 1200
- **Stat**: Power; **Cap**: 1200
- **Stat**: Guts; **Cap**: 1200
- **Stat**: Wit; **Cap**: 1200

### 8.2 Japanese Server (Current)

- **Stat**: Speed; **Cap**: 1300
- **Stat**: Stamina; **Cap**: 1300
- **Stat**: Power; **Cap**: 1300
- **Stat**: Guts; **Cap**: 1300
- **Stat**: Wit; **Cap**: **1800**

**Note**: Global will likely receive these increased caps in future updates

---

## 9. Unique Skill Level-Ups

**Same as URA Finale**, with one exception:

### 9.1 Fan Thresholds

**Standard Characters**:

- 60,000 fans by Valentine's Day (Early February) → Level 2
- 70,000 fans by Early April → Level 3
- 120,000 fans by Christmas (Late December, Senior Year) → Level 4

**High Dirt Aptitude Characters** (e.g., Haru Urara, Smart Falcon):

- 40,000 fans by Valentine's Day → Level 2
- 60,000 fans by Early April → Level 3
- 80,000 fans by Christmas → Level 4

### 9.2 Key Difference

**URA Finale**: April level-up requires green bond with Chairman Akikawa

**Unity Cup**: No bond requirement (Chairman Akikawa absent, replaced by Riko Kashimoto)

---

## 10. Scenario Race Spark (Inheritance)

### 10.1 Unity Cup Spark

**Name**: Unity Cup Scenario Spark (アオハル杯シナリオ)

**Stats Granted on Inheritance**:

- **Power**: Bonus inheritance
- **Wisdom**: Bonus inheritance

**Comparison to URA Finale**:

- URA Spark: Speed + Stamina
- Unity Cup Spark: Power + Wisdom

**Strategic Implication**: Unity Cup produces different inheritance profiles, valuable for breeding strategies

---

## 11. Advanced Mechanics (JP Server 2023 Update)

**Note**: Not yet available on Global server

### 11.1 Zenith Spirit Explosion

**Unlock Condition**: After triggering standard Spirit Burst

**Enhanced Benefits**:

1. All standard Spirit Burst bonuses
2. **Failure chance reduced to 0%** for that training
3. **Raises team member stat caps**
4. Grants hint for "Ignited Spirit" skill (facility-specific)

**Hint Level**: Base Level 1, increased by support card's Hint Lv. Bonus

### 11.2 Powerhouse Teams

**Unlock Conditions** (4th team race):

- League Rank: **10 or higher**
- Team Rank: **A or higher**
- At least **1 Zenith Spirit Explosion** triggered

**Visual Indicator**: Pink background, named after Greek gods/goddesses

**Rewards**:

- Enhanced stat bonuses
- Unlocks **strengthened Team Zenith** in finals

---

## 12. Optimal Strategy Summary

### 12.1 Training Priority

1. **Always prioritize white flames** (Unity Training)
2. **Never skip red exclamation marks** (multiplicative bonuses)
3. **Save Spirit Bursts** for optimal facilities
4. **Avoid non-flame training** until Year 3 (unless exceptional)

### 12.2 Team Race Strategy

1. **First 3 races**: Choose top difficulty (maximize Team Rank)
2. **4th race**: Choose middle if struggling, top if confident
3. **Aim for 3+ double-circles** before confirming opponent
4. **Never lose** if possible (3,000 stat penalty)

### 12.3 Energy Management

1. **Use Wit training** for energy recovery
2. **Equip Pal cards** (Riko Kashimoto optimal)
3. **Balance Unity Training** with friendship training
4. **Plan Spirit Bursts** around energy availability

### 12.4 Progression Benchmarks

**Year 1 (Junior)**:

- Team Rank: D-C
- Focus: Building team, first team race

**Year 2 (Classic)**:

- Team Rank: B-A by end of year
- Main stat facility: Level 3-4
- Spirit Bursts: 5-8 triggered

**Year 3 (Senior)**:

- Team Rank: A-S
- Main stat facility: Level 4-5
- Spirit Bursts: All key members completed
- Finals: Beat Team Zenith for S rank

---

## 13. Common Mistakes to Avoid

### 13.1 Training Mistakes

❌ **Training without white flames early game**

- Wastes turns, slows team progression

❌ **Triggering Spirit Burst immediately**

- May occur in suboptimal facility

❌ **Skipping red exclamation marks**

- Loses massive multiplicative bonuses

❌ **Ignoring energy management**

- Leads to rest turns, wasted opportunities

### 13.2 Team Race Mistakes

❌ **Choosing bottom difficulty consistently**

- Insufficient Team Rank progression

❌ **Ignoring double-circle indicators**

- Increases loss risk

❌ **Losing team races**

- 3,000 stat penalty cascades into facility level drops

❌ **Poor team composition**

- Not matching aptitudes to distances

### 13.3 Strategic Mistakes

❌ **Focusing only on trainee stats**

- Team stats determine facility levels

❌ **Neglecting scenario-linked characters**

- Misses +1 stat bonuses and Level 3 hints

❌ **Running too many optional races**

- Unity Cup prioritizes training over races

---

## 14. Comparison: Unity Cup vs URA Finale

- **Aspect**: **Focus**; **URA Finale**: Single character; **Unity Cup**: Team progression
- **Aspect**: **Facility Levels**; **URA Finale**: Usage frequency; **Unity Cup**: Team stat rank
- **Aspect**: **Stat Caps**; **URA Finale**: 1200 all stats; **Unity Cup**: 1200-1300 (1800 Wit JP)
- **Aspect**: **Special Mechanic**; **URA Finale**: None; **Unity Cup**: Spirit Burst
- **Aspect**: **Team Races**; **URA Finale**: None; **Unity Cup**: Every 6 months
- **Aspect**: **Inheritance Spark**; **URA Finale**: Speed + Stamina; **Unity Cup**: Power + Wisdom
- **Aspect**: **Run Duration**; **URA Finale**: Faster; **Unity Cup**: Slightly longer
- **Aspect**: **Optimal For**; **URA Finale**: Fan farming, debuff umas; **Unity Cup**: Ace characters, high stats
- **Aspect**: **Complexity**; **URA Finale**: Lower; **Unity Cup**: Higher

---

## 15. Implementation Considerations for Career Planner

### 15.1 New Data Models Required

**Team Member Model**:

- Character ID
- Current stats (Speed, Stamina, Power, Guts, Wit)
- Spirit Gauge level (0-100%)
- Spirit Burst triggered (boolean)
- Support card type (if applicable)
- Scenario-linked status

**Team Race Model**:

- Round number (1-5)
- Opponent selected (top/middle/bottom)
- Result (win/loss/draw)
- Races won (0-5)
- Stat bonuses awarded
- Team Rank before/after
- Team Placement before/after

**Unity Training Log**:

- Turn number
- Facility type
- White flames count
- Red marks count
- Spirit Bursts triggered
- Stat gains (trainee)
- Stat gains (team members)
- Energy cost

### 15.2 UI Components Needed

**Team Management Panel**:

- Team member list with stats
- Spirit Gauge indicators
- Scenario-linked badges
- Stat rank visualization

**Team Race Planner**:

- Distance team builder (5 teams)
- Opponent selection interface
- Double-circle indicator display
- Win probability calculator

**Training Optimizer**:

- White flame tracker
- Spirit Burst readiness alerts
- Optimal facility recommendations
- Energy cost calculator

**Facility Level Display**:

- Current team stat ranks
- Facility levels per stat
- Progression to next level
- Benchmark comparisons

### 15.3 Calculation Formulas

**Team Stat Average**:

```
team_stat_avg = SUM(member_stat) / COUNT(members)
```

**Facility Level**:

```
IF team_stat_avg < 200: level = 1
ELSE IF team_stat_avg < 340: level = 2
ELSE IF team_stat_avg < 510: level = 3
ELSE IF team_stat_avg < 610: level = 4
ELSE: level = 5
```

**Unity Training Energy Cost**:

```
total_cost = base_cost + (explosions × 6) + (flames - 1)
```

**Spirit Burst Stat Gain** (trainee):

```
base_gain = LOOKUP(facility_type, stat_table)
IF scenario_linked_present:
    final_gain = base_gain + scenario_linked_bonus
final_gain = MIN(final_gain, 50)  // Cap at 50
```

**Team Race Impact**:

```
IF win:
    stat_bonus = 50
    team_rank_change = +1
ELSE:
    stat_bonus = 10
    team_rank_change = -1

FOREACH member IN team:
    member.all_stats += stat_bonus
```

### 15.4 AI Advisory Enhancements

**Training Recommendations**:

- Prioritize white flame training
- Alert when Spirit Burst ready in optimal facility
- Warn against training without flames (pre-Year 3)
- Highlight red exclamation mark opportunities

**Team Race Strategy**:

- Recommend opponent based on current Team Rank
- Calculate win probability per distance
- Suggest team composition adjustments
- Alert if loss risk high

**Progression Tracking**:

- Compare current pace to benchmarks
- Predict final Team Rank based on trajectory
- Identify bottleneck stats
- Recommend focus areas

---

## 16. Sources and References

### Primary Sources

1. **Game8 - Unity Cup Team Races Guide**
   - URL: <https://game8.co/games/Umamusume-Pretty-Derby/archives/563652>
   - Content: Team race mechanics, victory conditions, scheduling

2. **Game8 - Team Rank Guide**
   - URL: <https://game8.co/games/Umamusume-Pretty-Derby/archives/563657>
   - Content: Facility level progression, team rank thresholds

3. **GameTora - Unity Cup Scenario**
   - URL: <https://gametora.com/umamusume/unity-cup>
   - Content: Comprehensive mechanics, Spirit Burst formulas, stat tables

4. **UmaReference - Unity Cup Strategy**
   - URL: <https://www.umareference.com/guide/advanced-training-strategy/unity-cup-scenario-1>
   - Content: Advanced strategies, explosion stat tables, benchmarks

5. **LDPlayer - Unity Cup Guide**
   - URL: <https://www.ldplayer.net/blog/uma-musume-unity-cup-guide.html>
   - Content: Spirit Burst activation, white flame mechanics

6. **VortexGaming - Unity Cup Perfect Guide**
   - URL: <https://vortexgaming.io/en/postdetail/601511>
   - Content: Team composition, skill acquisition strategies

### Content Compliance

All content has been paraphrased and summarized from the above sources to comply with licensing restrictions. No more than 30 consecutive words have been reproduced verbatim from any single source. Factual accuracy has been preserved while condensing information.

---

## Document Control

**Version History**:

- v1.0.0 (2026-01-29): Initial comprehensive research compilation

**Related Documents**:

- `deepseek-ura-finale.txt` - URA Finale mechanics comparison
- `deepseek-unity-cup.txt` - Initial Unity Cup notes
- Product documentation (PRDs, SPECs) - Career planning features

**Maintenance**:

- Update when Global server receives JP 2023 update features
- Revise stat caps if changed in future updates
- Add new mechanics as scenarios evolve

---

### End of Document
