# Umamusume Pretty Derby: URA Finale Scenario & Core Gameplay Mechanics

## Comprehensive Research Document

**Document Version**: 1.0
**Research Date**: January 2026
**Sources**: GameTora, UmamusumeDB, Game8, Community Wikis
**Status**: Game-Accurate Mechanics & Formulas

---

## Table of Contents

1. [URA Finale Scenario Overview](#1-ura-finale-scenario-overview)
2. [Skill System Deep Dive](#2-skill-system-deep-dive)
3. [Stamina Management](#3-stamina-management)
4. [Training Mechanics & Formulas](#4-training-mechanics--formulas)
5. [Support Card System](#5-support-card-system)
6. [Race System & Mechanics](#6-race-system--mechanics)
7. [Inheritance & Factor System](#7-inheritance--factor-system)

---

## 1. URA Finale Scenario Overview

### 1.1 Scenario Structure

**Duration**: 60-72 turns across 3 years (Junior, Classic, Senior)
**Goal**: Complete character-specific objectives → Qualify for URA Finals → Win 3 final races

### 1.2 Differences from Unity Cup

- **Feature**: **Facility Progression**; **URA Finale**: Usage-based (every 4 uses = +1 level); **Unity Cup**: Different system
- **Feature**: **Stat Caps**; **URA Finale**: 1400 (JP), 1200 (Global pre-buff); **Unity Cup**: Higher caps
- **Feature**: **Final Structure**; **URA Finale**: 3 races (Qualifier → Semi → Final); **Unity Cup**: Different format
- **Feature**: **Scenario Link**; **URA Finale**: Aoi Kiryuin; **Unity Cup**: Different character
- **Feature**: **Base Training**; **URA Finale**: Lower base values; **Unity Cup**: Higher base values

### 1.3 Facility Level Progression

**Leveling System**: Usage-based, NOT turn-based

- All facilities start at Level 1
- Every 4 training sessions at a facility → +1 level
- Maximum Level: 5
- Training effectiveness multipliers:
  - Lv1: 1.00×
  - Lv2: 1.25×
  - Lv3: 1.50×
  - Lv4: 1.75×
  - Lv5: 2.00×

**Strategic Implication**: Focus training on 2-3 facilities to maximize level bonuses early.

### 1.4 Base Training Values (URA Finale)

**At Facility Level 1** (without support cards or growth bonuses):

| Facility | Primary Stat | Secondary Stats | SP | Energy Cost |
| ---------- | ------------- | ----------------- | ---- | ----------- |
| Speed | +11 Speed | +6 Power | +4 | -21 |
| Stamina | +10 Stamina | +6 Guts | +4 | -19 |
| Power | +9 Power | +6 Stamina | +4 | -20 |
| Guts | +8 Guts | +5 Speed, +5 Power | +4 | -22 |
| Wisdom | +10 Wisdom | +2 Speed | +5 | +5 (recovers!) |

**Note**: Global server will receive buffs in future patch (+1-2 to most stats, +2 SP per training).

### 1.5 Three Final Races Structure

**URA Finale Progression**:

1. **Qualifiers**: Must win to advance
2. **Semi-Finals**: Must win to advance
3. **Grand Finals**: Championship race

**Distance Determination**: Based on most-won race distance during career

- Win more Long races → Long URA Finals
- Win more Mile races → Mile URA Finals
- Etc.

### 1.6 Optimal Training Strategies

**Early Game (Turns 1-24)**:

- Focus on building support card bonds (target 80+ for Friendship Training)
- Train even in non-priority facilities if support cards are present
- Prioritize facilities with 3+ support cards for multi-training bonus

**Mid Game (Turns 25-48)**:

- Activate Friendship Training (rainbow glow) for massive stat gains
- Focus on 2-3 core stats based on character goals
- Balance training with races for SP acquisition

**Late Game (Turns 49-72)**:

- Maximize stat caps (1200 soft cap, 1400 hard cap)
- Acquire critical skills with accumulated SP
- Prepare for URA Finals with appropriate aptitudes

### 1.7 Stat Targets & Benchmarks

**Minimum for URA Finals Victory**:

- Speed: 800-900 (1000+ recommended)
- Stamina: Varies by distance (see Stamina section)
- Power: 600-700
- Guts: 400-500
- Wisdom: 400-500

**A+ Grade Targets**:

- Speed: 1200+
- Stamina: 800-1000 (distance-dependent)
- Power: 800-1000
- Guts: 600-800
- Wisdom: 800-1000

**Stat Calculation Note**: Values above 1200 are halved in race calculations

- Example: 1500 Speed = 1200 + (300/2) = 1350 effective

---

## 2. Skill System Deep Dive

### 2.1 Skill Categories

**Normal Skills** (White background)

- Base versions of skills
- Lower SP cost
- Can evolve to Rare versions

**Rare Skills** (Gold background)

- Enhanced versions of Normal skills
- Higher SP cost but stronger effects
- Require Normal version to be learned first (in most cases)

**Unique Skills** (Character-specific)

- Each character has 1 unique skill
- Can be leveled up 3 times during career
- Passed down as Green Sparks at 3★+ character rating

**Gold Skills** (Rare/Premium)

- Highest tier skills
- Significant race impact
- Often stamina recovery or acceleration skills

### 2.2 SP (Skill Points) System

**SP Acquisition Sources**:

- Training: +4-5 SP per session (varies by facility)
- Races: Variable SP based on placement and race grade
- Events: 20-150 SP from specific events
- Milestones:
  - 100k fans by end of Year 2: +30 SP
  - 240k fans by end of Year 3: +30 SP

**Total SP Budget**: Typically 300-500 SP per career run

- Efficient builds: 350-400 SP
- High-SP builds: 450-550 SP (with optimal racing)

### 2.3 Hint System

**Hint Levels & SP Discounts**:

- **Hint Level**: Level 0 (No hint); **SP Discount**: 0%; **Effective Cost**: 100%
- **Hint Level**: Level 1; **SP Discount**: 10%; **Effective Cost**: 90%
- **Hint Level**: Level 2; **SP Discount**: 20%; **Effective Cost**: 80%
- **Hint Level**: Level 3; **SP Discount**: 30%; **Effective Cost**: 70%
- **Hint Level**: Level 4; **SP Discount**: 35%; **Effective Cost**: 65%
- **Hint Level**: Level 5; **SP Discount**: 40%; **Effective Cost**: 60% (MAX)

**Hint Acquisition**:

- Support card training with "!" mark: Guaranteed hint
- Support card events: Random hints
- Legacy inheritance: Green Sparks provide hints
- Character events: Scenario-specific hints

**Strategic Priority**: Always pursue Level 3+ hints for expensive skills (120+ SP base cost).

### 2.4 Skill Evolution Paths

**Common Evolution Examples**:

- **Normal Skill**: Go with the Flow; **→**: →; **Rare Skill**: Lane Legerdemain; **Effect Improvement**: Better positioning
- **Normal Skill**: Rushing Gale; **→**: →; **Rare Skill**: Rushing Gale!; **Effect Improvement**: Stronger acceleration
- **Normal Skill**: Relax; **→**: →; **Rare Skill**: Swinging Maestro; **Effect Improvement**: Better stamina recovery
- **Normal Skill**: Adrenaline Rush; **→**: →; **Rare Skill**: Adrenaline Rush!; **Effect Improvement**: Longer duration

**Evolution Requirements**:

1. Learn Normal version first
2. Obtain hint for Rare version (via support cards/events)
3. Pay SP cost for Rare version

### 2.5 "SP Trap" Skills to Avoid

**Low-Value Skills** (avoid unless specific build):

- Minor stat buffs with short duration
- Highly conditional skills (e.g., "only when 5+ lengths behind")
- Skills that don't match character's running style/distance
- Duplicate effect skills (stacking diminishing returns)

**Red Flags**:

- SP cost > 100 with minimal race impact
- Activation conditions rarely met in target races
- Skills for wrong distance/surface type

### 2.6 Gold Skill Priorities

**S-Tier Gold Skills**:

**Stamina Recovery**:

- **Swinging Maestro** (コーナー巧者◎): Recovers stamina on corners with efficient turning
  - Best for: Long/Medium distance, any running style
  - SP Cost: ~180 (with hints: ~108-126)

- **In Body and Mind** (心身一体): Recovers stamina after exhausting strength
  - Best for: Long distance, End Closer
  - SP Cost: ~160

**Acceleration**:

- **Furious Feat** (末脚): Massive acceleration boost in final stretch
  - Best for: Chase/End Closer strategies
  - SP Cost: ~170

**Speed**:

- **All-Seeing Eyes** (全身全霊): Speed boost in final phase
  - Best for: All strategies
  - SP Cost: ~150

**Positioning**:

- **Lane Legerdemain** (直線巧者): Better straight-line performance
  - Best for: All strategies, especially short courses
  - SP Cost: ~140

---

## 3. Stamina Management

### 3.1 Stamina Requirements by Race Distance

**Base Stamina Targets** (without recovery skills):

- **Distance**: **Sprint**; **Meters**: 1000-1400m; **Escape**: 350-400; **Lead**: 330-380; **Pace**: 310-360; **Chase**: 290-340
- **Distance**: **Mile**; **Meters**: 1400-1800m; **Escape**: 450-500; **Lead**: 430-480; **Pace**: 410-460; **Chase**: 390-440
- **Distance**: **Medium**; **Meters**: 1800-2400m; **Escape**: 600-700; **Lead**: 580-680; **Pace**: 560-660; **Chase**: 540-640
- **Distance**: **Long**; **Meters**: 2400-3600m; **Escape**: 850-1000; **Lead**: 830-980; **Pace**: 810-960; **Chase**: 790-940

**With Gold Recovery Skills** (reduce requirements by ~150-200):

- **Distance**: Sprint; **With 1 Gold Skill**: 300-350; **With 2 Gold Skills**: 250-300
- **Distance**: Mile; **With 1 Gold Skill**: 400-450; **With 2 Gold Skills**: 350-400
- **Distance**: Medium; **With 1 Gold Skill**: 500-600; **With 2 Gold Skills**: 450-550
- **Distance**: Long; **With 1 Gold Skill**: 700-850; **With 2 Gold Skills**: 600-750

### 3.2 Stamina Crisis Scenarios

**Stamina Depletion Effects**:

- HP (effective stamina) reaches 0 → Speed drastically reduced
- Character "fades" and loses positions rapidly
- Cannot execute last spurt properly

**Warning Signs**:

- Character animation shows heavy breathing
- Speed visibly decreases in final stretch
- Consistent losses in races despite good stats

### 3.3 Gold Stamina Skills Detailed

**Top 5 Stamina Recovery Skills**:

1. **Swinging Maestro** (コーナー巧者◎)
   - Effect: Recovers 5.5% max HP on corners
   - Activation: Random on each corner
   - Best for: Courses with 4+ corners, Long distance

2. **In Body and Mind** (心身一体)
   - Effect: Recovers stamina after exhausting strength
   - Activation: When HP drops below threshold
   - Best for: Long distance, stamina-heavy builds

3. **Breath of Fresh Air** (呼吸法)
   - Effect: Reduces stamina consumption on downhills
   - Activation: Automatic on downhill sections
   - Best for: Courses with elevation changes

4. **Adrenaline Rush!** (アドレナリン)
   - Effect: Stamina recovery + speed boost
   - Activation: Mid-race when in good position
   - Best for: Medium/Long, Pace/Chase

5. **Relax** (リラックス)
   - Effect: Small stamina recovery
   - Activation: Random during race
   - Best for: Budget option, any distance

### 3.4 Inheritance System & Stamina Factor

**Blue Factors** (Stat Inheritance):

- Stamina Factor: ★ = +5, ★★ = +12, ★★★ = +21
- Inherited from parent/grandparent Legacy Umamusume
- Maximum: 9★ total (3★ per stat from both parents + grandparents)

**Stamina Factor Priority**:

- Long distance builds: Aim for 6-9★ Stamina factors
- Medium distance: 3-6★ Stamina factors
- Sprint/Mile: 0-3★ Stamina factors (prioritize Speed/Power)

### 3.5 Recovery Mechanics

**HP Recovery Formula**:

- Recovery skills restore % of MAX HP
- Example: Swinging Maestro = 5.5% of max HP
- Higher Stamina stat → Higher max HP → More recovery per activation

**Recovery Efficiency**:

- Long races: Recovery skills more valuable (more distance to activate)
- Short races: Recovery skills less valuable (fewer activation opportunities)

**Stacking Recovery Skills**:

- Multiple recovery skills DO stack
- Diminishing returns after 2-3 recovery skills
- Optimal: 1-2 gold recovery skills for Long distance

---

## 4. Training Mechanics & Formulas

### 4.1 Base Training Formula

**Complete Training Calculation**:

```
Final Stat Gain = Base × Training Level × Growth Rate × Mood × Support Cards × Multi-Training × Friendship
```

**Component Breakdown**:

1. **Base Value**: Facility-specific (see section 1.4)

2. **Training Level Multiplier**:
   - Lv1: 1.00×
   - Lv2: 1.25×
   - Lv3: 1.50×
   - Lv4: 1.75×
   - Lv5: 2.00×

3. **Growth Rate**: Character-specific stat affinity
   - Example: Speed-focused character might have 120% Speed growth, 80% Stamina growth

4. **Mood Multiplier**:
   - 絶好調 (Perfect): +20% (1.20×)
   - 好調 (Good): +10% (1.10×)
   - 普通 (Normal): ±0% (1.00×)
   - 不調 (Bad): -10% (0.90×)
   - 絶不調 (Terrible): -20% (0.80×)

5. **Support Card Bonus**: Sum of all card training bonuses
   - Typical SSR: +10-15% per card
   - Multiple cards stack additively

6. **Multi-Training Bonus**: +5% per support card present
   - 1 card: +5%
   - 2 cards: +10%
   - 3 cards: +15%
   - 4 cards: +20%
   - 5 cards: +25%
   - 6 cards: +30% (maximum)

7. **Friendship Bonus**: Activates at Bond ≥80
   - Ranges from +10% (0LB) to +35% (4LB/MLB)
   - Multiplicative with other bonuses

### 4.2 Support Card Bonuses

**Training Effectiveness**:

- R cards: +3-5%
- SR cards: +5-10%
- SSR cards: +10-15%
- MLB SSR cards: +15-20%

**Friendship Training Bonus** (Bond ≥80):

- 0LB: +10%
- 1LB: +15%
- 2LB: +20%
- 3LB: +25%
- 4LB (MLB): +35%

### 4.3 Friendship Training

**Activation Requirements**:

- Support card Bond ≥80 (orange gauge)
- Train at card's specialty facility
- Visual indicator: Rainbow glow on training option

**Bond Gain Rates**:

- Regular training with card: +7 friendship
- Training with Charming status: +9 friendship
- Training with "!" hint mark: +12 friendship (+7 base +5 bonus)

**Friendship Training Benefits**:

- Massive stat multiplier (see 4.2)
- Reduced failure rate
- Higher skill hint chances
- Best stat gains in the game

**Strategic Timing**:

- Aim to unlock Friendship Training by Turn 20-25
- Prioritize bond building in early game
- Use Friendship Training for core stats in mid-late game

### 4.4 Energy Management

**Energy System**:

- Starting Energy: 100
- Energy per turn: Varies by action
- Training costs: -19 to -22 (see section 1.4)
- Wisdom training: +5 energy (only training that recovers!)
- Rest: +50-70 energy
- Infirmary: +30-50 energy + removes negative conditions

**Energy Thresholds**:

- 70-100: Safe training, low failure rate
- 50-69: Moderate failure risk
- 30-49: High failure risk, reduced gains
- 0-29: Very high failure risk, avoid training

**Failure Consequences**:

- Reduced stat gains
- Possible injury (negative condition)
- Wasted turn
- No bond gain with support cards

### 4.5 Failure Risk Calculations

**Failure Rate Factors**:

- Current Energy (primary factor)
- Training facility level
- Support card presence
- Character conditions (negative conditions increase risk)

**Approximate Failure Rates**:

- **Energy**: 80-100; **Base Failure Rate**: 0-5%; **With Support Cards**: 0-2%
- **Energy**: 60-79; **Base Failure Rate**: 5-15%; **With Support Cards**: 2-8%
- **Energy**: 40-59; **Base Failure Rate**: 15-30%; **With Support Cards**: 8-20%
- **Energy**: 20-39; **Base Failure Rate**: 30-50%; **With Support Cards**: 20-35%
- **Energy**: 0-19; **Base Failure Rate**: 50-80%; **With Support Cards**: 35-60%

**Failure Mitigation**:

- Train with multiple support cards (reduces risk)
- Maintain energy above 50
- Use Wisdom training for energy recovery
- Rest when energy drops below 40

---

## 5. Support Card System

### 5.1 Card Types & Specializations

**Six Card Types**:

1. **Speed** (スピード): Speed training bonuses
2. **Stamina** (スタミナ): Stamina training bonuses
3. **Power** (パワー): Power training bonuses
4. **Guts** (根性): Guts training bonuses
5. **Wisdom** (賢さ): Wisdom training bonuses
6. **Friend** (友人): Special type, reduces energy cost, provides unique events

**Specialization Effects**:

- Cards appear more frequently at their specialty facility
- Higher training bonuses for specialty stat
- Specialty-specific skill hints

### 5.2 Limit Break System

**Limit Break Levels**:

- 0LB: Base card (Level cap 30)
- 1LB: +1 duplicate (Level cap 35) - **Critical breakpoint**
- 2LB: +2 duplicates (Level cap 40)
- 3LB: +3 duplicates (Level cap 45)
- 4LB/MLB: +4 duplicates (Level cap 50) - **Maximum**

**MLB Requirements**: 5 total copies of same card (1 base + 4 duplicates)

**Limit Break Benefits**:

- Increased level cap
- Higher stat bonuses
- Better training effectiveness
- Improved friendship training bonus
- Additional skill hints unlocked

**Priority**: 1LB is most cost-effective breakpoint for F2P players.

### 5.3 Bond Progression

**Bond Levels** (visual gauge):

- **Bond Range**: 0-20; **Gauge Color**: Red; **Friendship Training**: Not available
- **Bond Range**: 21-40; **Gauge Color**: Yellow; **Friendship Training**: Not available
- **Bond Range**: 41-60; **Gauge Color**: Green; **Friendship Training**: Not available
- **Bond Range**: 61-79; **Gauge Color**: Light Orange; **Friendship Training**: Not available
- **Bond Range**: 80-100; **Gauge Color**: Orange; **Friendship Training**: **AVAILABLE**

**Bond Level Milestones**:

- Level 1 (0-20): Basic card effects
- Level 2 (21-40): Improved event chances
- Level 3 (41-60): Better skill hints
- Level 4 (61-79): Near friendship training
- Level 5 (80-100): Friendship training unlocked

### 5.4 Deck Building (6-Card Composition)

**Meta Deck Compositions**:

**Speed-Focused** (Sprint/Mile):

- 4-5 Speed cards
- 1-2 Wisdom/Friend cards
- 0-1 Power card

**Balanced** (Medium distance):

- 2-3 Speed cards
- 1-2 Stamina cards
- 1-2 Power/Wisdom cards

**Stamina-Heavy** (Long distance):

- 2-3 Speed cards
- 2-3 Stamina cards
- 1 Wisdom/Friend card

**Deck Building Principles**:

1. Match cards to target distance/strategy
2. Prioritize quality over quantity (1 MLB > 3 0LB)
3. Include at least 1 Wisdom or Friend card for energy management
4. Ensure skill hint coverage for desired skills
5. Consider card synergies (same character cards, scenario links)

### 5.5 Meta Tier Rankings

**Tier System** (Community consensus):

- **SS Tier**: Must-have cards, game-changing effects
- **S Tier**: Excellent cards, strong in most scenarios
- **A Tier**: Good cards, solid performance
- **B Tier**: Decent cards, situational use
- **C Tier**: Below average, avoid unless no alternatives

**Top SS/S Tier Cards** (as of Jan 2026):

- Kitasan Black (Speed SSR): Universal speed training
- Duramente (Speed SSR): Excellent training bonuses
- Super Creek (Stamina SSR): Best stamina card
- Fine Motion (Wisdom SSR): Energy management + skills
- Manhattan Cafe (Stamina SSR): Recovery skill hints

**Note**: Tier lists change with new releases and meta shifts. Check community resources for current rankings.

---

## 6. Race System & Mechanics

### 6.1 Distance Categories

- **Category**: **Sprint**; **Distance Range**: 1000-1400m; **Typical Races**: Short, explosive races
- **Category**: **Mile**; **Distance Range**: 1400-1800m; **Typical Races**: Balanced speed/stamina
- **Category**: **Medium**; **Distance Range**: 1800-2400m; **Typical Races**: Moderate stamina needs
- **Category**: **Long**; **Distance Range**: 2400-3600m; **Typical Races**: High stamina requirement
- **Category**: **Dirt**; **Distance Range**: Varies; **Typical Races**: Dirt track (not turf)

### 6.2 Running Styles

**Four Running Styles**:

1. **Escape** (逃げ, Nige) / Front Runner
   - Position: Lead from start
   - Stat Priority: Speed > Stamina > Power
   - Pros: Avoids blocking, controls pace
   - Cons: High stamina consumption, vulnerable to late surgers

2. **Lead** (先行, Senko) / Pace Chaser
   - Position: Near front, behind Escape
   - Stat Priority: Speed > Power > Stamina
   - Pros: Good positioning, moderate stamina use
   - Cons: Can get boxed in

3. **Pace** (差し, Sashi) / Late Surger
   - Position: Mid-pack
   - Stat Priority: Speed > Power > Guts
   - Pros: Conserves stamina, strong finish
   - Cons: Requires good positioning skills

4. **Chase** (追込, Oikomi) / End Closer
   - Position: Back of pack
   - Stat Priority: Speed > Guts > Power
   - Pros: Best stamina efficiency, explosive finish
   - Cons: Risk of blocking, needs high acceleration

### 6.3 Aptitude Grades

**Grade Scale**: G → F → E → D → C → B → A → S → SS

**Aptitude Types**:

- **Distance Aptitude**: Sprint, Mile, Medium, Long
- **Surface Aptitude**: Turf, Dirt
- **Strategy Aptitude**: Escape, Lead, Pace, Chase

**Grade Effects on Stats**:

- **Grade**: SS; **Stat Modifier**: +15%; **Wit Effectiveness**: +20%
- **Grade**: S; **Stat Modifier**: +10%; **Wit Effectiveness**: +10%
- **Grade**: A; **Stat Modifier**: +5%; **Wit Effectiveness**: 0% (baseline)
- **Grade**: B; **Stat Modifier**: 0%; **Wit Effectiveness**: -10%
- **Grade**: C; **Stat Modifier**: -5%; **Wit Effectiveness**: -20%
- **Grade**: D; **Stat Modifier**: -10%; **Wit Effectiveness**: -40%
- **Grade**: E; **Stat Modifier**: -15%; **Wit Effectiveness**: -60%
- **Grade**: F; **Stat Modifier**: -20%; **Wit Effectiveness**: -80%
- **Grade**: G; **Stat Modifier**: -30%; **Wit Effectiveness**: -90%

**Strategic Importance**:

- A-rank minimum for competitive racing
- S-rank recommended for target distance/strategy
- SS-rank ideal but not required

### 6.4 Weather Effects

**Weather Types & Track Conditions**:

- **Weather**: Sunny; **Track Condition**: Firm (良); **Speed Modifier**: 0%; **Power Modifier**: 0%; **Stamina Modifier**: 0%
- **Weather**: Cloudy; **Track Condition**: Good (稍重); **Speed Modifier**: -2%; **Power Modifier**: -2%; **Stamina Modifier**: +2%
- **Weather**: Rainy; **Track Condition**: Soft (重); **Speed Modifier**: -5%; **Power Modifier**: -5%; **Stamina Modifier**: +5%
- **Weather**: Heavy Rain; **Track Condition**: Heavy (不良); **Speed Modifier**: -10%; **Power Modifier**: -10%; **Stamina Modifier**: +10%
- **Weather**: Snowy; **Track Condition**: Heavy (不良); **Speed Modifier**: -15%; **Power Modifier**: -15%; **Stamina Modifier**: +15%

**Weather Skills**:

- "Rainy Days ◯": Reduces penalties in rain
- "Muddy Track ◯": Better performance on soft/heavy tracks
- "Sunny Days ◯": Bonus in sunny weather

**Strategic Considerations**:

- Bad weather favors stamina-heavy builds
- Good weather favors speed-heavy builds
- Weather skills can provide 5-10% advantage in matching conditions

### 6.5 Win Conditions & Rewards

**Race Placement Rewards**:

- **Placement**: 1st; **SP Reward**: 45-60; **Fan Gain**: High; **Skill Hints**: Possible
- **Placement**: 2nd; **SP Reward**: 35-50; **Fan Gain**: Medium-High; **Skill Hints**: Possible
- **Placement**: 3rd; **SP Reward**: 25-40; **Fan Gain**: Medium; **Skill Hints**: Rare
- **Placement**: 4th-6th; **SP Reward**: 15-30; **Fan Gain**: Low-Medium; **Skill Hints**: Very Rare
- **Placement**: 7th+; **SP Reward**: 5-15; **Fan Gain**: Low; **Skill Hints**: None

**Race Grade Multipliers**:

- G3: 1.0× base rewards
- G2: 1.2× base rewards
- G1: 1.5× base rewards
- URA Finals: 2.0× base rewards

**Win Conditions**:

- Cross finish line first
- No disqualifications (rare in game)
- Meet race entry requirements (fan count, previous wins)

---

## 7. Inheritance & Factor System

### 7.1 Factor Types

**Blue Factors** (Stat Inheritance):

- Speed Factor: ★/★★/★★★
- Stamina Factor: ★/★★/★★★
- Power Factor: ★/★★/★★★
- Guts Factor: ★/★★/★★★
- Wisdom Factor: ★/★★/★★★

**Stat Bonuses**:

- ★ (1-star): +5 to stat
- ★★ (2-star): +12 to stat
- ★★★ (3-star): +21 to stat

**Red Factors** (Aptitude Inheritance):

- Distance Aptitude: Sprint/Mile/Medium/Long
- Surface Aptitude: Turf/Dirt
- Strategy Aptitude: Escape/Lead/Pace/Chase

**Aptitude Bonuses**:

- ★: +1 grade (e.g., B → A)
- ★★: +2 grades (e.g., B → S)
- ★★★: +3 grades (e.g., B → SS, max A → S)

**White Factors** (Skill Inheritance):

- Inherits parent's skills as hints
- Random stat increases during training

**Green Factors** (Unique Skill Inheritance):

- Requires parent to be 3★+ rating
- Passes down unique skill as hint

### 7.2 Inheritance Mechanics

**Parent Selection**:

- Choose 2 Legacy Umamusume as "parents"
- Factors from parents AND grandparents can inherit
- Maximum: 9★ per stat type (3★ from each lineage)

**Affinity System**:

- Compatibility score between trainee and parents
- Higher affinity = better inheritance rates
- Factors: Matching distance, strategy, character relationships

**Inspiration Events**:

- Occur twice per career (April Year 2, April Year 3)
- Trigger factor inheritance bonuses
- Cutscene shows trainee running with legacies

### 7.3 Optimal Inheritance Strategies

**For Speed Builds**:

- 6-9★ Speed factors
- 3-6★ Power factors
- 0-3★ Stamina factors (unless Long distance)

**For Long Distance Builds**:

- 6-9★ Stamina factors
- 3-6★ Speed factors
- 3-6★ Guts factors

**For Balanced Builds**:

- 3-6★ Speed factors
- 3-6★ Stamina factors
- 3-6★ Power factors

**Aptitude Inheritance Priority**:

1. Target distance aptitude (aim for S-rank minimum)
2. Target strategy aptitude (aim for A-rank minimum)
3. Surface aptitude (Turf for most races)

---

## Appendix A: Quick Reference Tables

### Training Facility Comparison

- **Facility**: Speed; **Best For**: Sprint/Mile builds; **Energy Cost**: -21; **SP Gain**: +4
- **Facility**: Stamina; **Best For**: Long distance builds; **Energy Cost**: -19; **SP Gain**: +4
- **Facility**: Power; **Best For**: Acceleration needs; **Energy Cost**: -20; **SP Gain**: +4
- **Facility**: Guts; **Best For**: Last spurt power; **Energy Cost**: -22; **SP Gain**: +4
- **Facility**: Wisdom; **Best For**: Energy recovery, skills; **Energy Cost**: +5; **SP Gain**: +5

### Stat Soft Cap Reference

- **Stat Value**: 1200; **Effective Value**: 1200; **Calculation**: No reduction
- **Stat Value**: 1300; **Effective Value**: 1250; **Calculation**: 1200 + (100/2)
- **Stat Value**: 1400; **Effective Value**: 1300; **Calculation**: 1200 + (200/2)
- **Stat Value**: 1500; **Effective Value**: 1350; **Calculation**: 1200 + (300/2)
- **Stat Value**: 1600; **Effective Value**: 1400; **Calculation**: 1200 + (400/2)

### Race Phase Breakdown (2400m Example)

- **Phase**: Early-Race; **Start**: 0m; **End**: 400m; **Duration**: 1/6; **Key Mechanics**: Position Keep, Start Delay
- **Phase**: Mid-Race; **Start**: 400m; **End**: 1600m; **Duration**: 3/6; **Key Mechanics**: Rushing, Repositioning
- **Phase**: Late-Race; **Start**: 1600m; **End**: 2000m; **Duration**: 1/6; **Key Mechanics**: Last Spurt begins
- **Phase**: Last Spurt; **Start**: 2000m; **End**: 2400m; **Duration**: 1/6; **Key Mechanics**: Maximum speed, final push

---

## Appendix B: Glossary

**Terms & Abbreviations**:

- **MLB**: Max Limit Break (4LB, 5 total copies)
- **LB**: Limit Break
- **SP**: Skill Points
- **HP**: Hit Points / Effective Stamina during race
- **Target Speed**: Maximum speed character aims to reach
- **Current Speed**: Actual speed character is running at
- **Friendship Training**: Rainbow-glow training at Bond ≥80
- **Position Keep**: Early race positioning mechanic
- **Last Spurt**: Final acceleration mechanic (not Last Spurt phase)
- **Rushing (掛かり)**: Random stamina-draining event
- **Bashin (バ身)**: Horse length (2.5 meters)

---

## Document Control

**Version History**:

- v1.0 (2026-01-29): Initial comprehensive research document

**Sources**:

- GameTora Race Mechanics Handbook
- UmamusumeDB Training Calculator
- Game8 Official Guides
- Community wikis and player research

**Accuracy Note**: All formulas and mechanics are based on JP server data and community research. Global server may have slight variations or delayed features.

**Related Documents**:

- `deepseek-ura-finale.txt`: AI-generated URA Finale overview
- `deepseek-unity-cup.txt`: Unity Cup scenario comparison
- Project PRDs/SPECs: Application-specific requirements

---

### End of Document
