# Umamusume Pretty Derby Gameplay Research

**Research Collection Version**: 1.0.0  
**Last Updated**: January 31, 2026  
**Status**: Comprehensive Game Mechanics Documentation

---

## Overview

This directory contains comprehensive research documentation on Umamusume Pretty Derby gameplay mechanics, formulas, and systems. All research is based on official game wikis, community resources, and verified player data to ensure game-accurate implementation in the Career Planner application.

---

## Research Documents

### 1. Unity Cup Scenario Mechanics

**File**: [`unity-cup-mechanics-research.md`](../neuron/unity-cup-mechanics-research.md)  
**Status**: Complete  
**Version**: 1.0.0  
**Date**: January 29, 2026

**Coverage**:

- Team Race System (schedule, structure, victory conditions)
- Spirit Burst Mechanic (gauge filling, stat gains, optimal timing)
- Unity Training (white flame indicators, team progression)
- Red Exclamation Marks (Unity Burst vs Friendship Bonus)
- Facility Level Progression (team rank-based, not usage-based)
- Base Training Values (Global vs JP server)
- Scenario-Specific Skills
- Advanced Mechanics (Zenith Spirit Explosion, Powerhouse Teams)

**Key Findings**:

- Team races every 6 months with +50 all stats for wins vs +10 for losses
- Spirit Burst provides 150+ stats to team members, 15-50 to trainee
- Facility levels tied to team stat rank (F/G=Lv1 → S=Lv5)
- Unity Training should never be skipped (white flames)
- Red exclamation marks indicate multiplicative bonuses

**Sources**: Game8, GameTora, UmaReference, LDPlayer, VortexGaming

---

### 2. URA Finale Scenario & Core Gameplay

**File**: [`umamusume-ura-finale-comprehensive-guide.md`](umamusume-ura-finale-comprehensive-guide.md)  
**Status**: Complete  
**Version**: 1.0.0  
**Date**: January 29, 2026

**Coverage**:

- URA Finale Scenario (structure, facility progression, final races)
- Skill System (categories, SP management, hint system, evolution paths)
- Stamina Management (requirements by distance, gold skills, recovery mechanics)
- Training Mechanics (complete formulas, support card bonuses, friendship training)
- Support Card System (limit breaks, bond progression, deck building)
- Race System (running styles, aptitudes, weather effects)
- Inheritance & Factor System (blue/red/white/green factors)

**Key Findings**:

- Usage-based facility progression (every 4 uses = +1 level)
- Hint system: Lv1=10%, Lv2=20%, Lv3=30%, Lv4=35%, Lv5=40% max discount
- Friendship training activates at Bond ≥80 with +10% to +35% bonus
- Stamina requirements: Sprint 290-400, Mile 390-500, Medium 540-700, Long 790-1000
- Training formula: Base × Level × Growth × Mood × Cards × Multi × Friendship

**Sources**: GameTora, UmamusumeDB, Game8, Community Wikis

---

### 3. Captured Gameplay Conversations

**Files**:

- [`../../deepseek-unity-cup.txt`](../../deepseek-unity-cup.txt) - Unity Cup gameplay walkthrough
- [`../../deepseek-ura-finale.txt`](../../deepseek-ura-finale.txt) - URA Finale gameplay walkthrough

**Status**: Raw gameplay data  
**Date**: January 31, 2026

**Content**:

- Real player scenarios and decision-making
- SP management strategies (save 400-500 for gold skills)
- Stamina crisis management examples
- Skill acquisition priorities
- Race strategy recommendations
- Turn-by-turn gameplay analysis

**Key Insights**:

- SP is extremely scarce - avoid "SP trap" skills
- Stamina is often the limiting factor for long races
- Inheritance in February (Classic Year) is critical
- Red exclamation marks should NEVER be skipped
- Manual team setup before each Team Race is mandatory (Unity Cup)

---

## Quick Reference

### Stat Targets (A+ Grade)

| Stat | Target | Notes |
| ------ | -------- | ------- |
| Speed | 1200+ | Soft cap at 1200 (50% effectiveness above) |
| Stamina | 600-1000 | Distance-dependent |
| Power | 800-1000 | Acceleration and positioning |
| Guts | 600-800 | Last spurt power |
| Wisdom | 800-1000 | Skill activation and energy |

### Training Facility Multipliers

| Level | Multiplier | How to Reach |
| ------- | ------------ | -------------- |
| 1 | 1.00× | Starting level |
| 2 | 1.25× | URA: 4 uses / Unity: Team Rank D-E |
| 3 | 1.50× | URA: 8 uses / Unity: Team Rank B-C |
| 4 | 1.75× | URA: 12 uses / Unity: Team Rank A |
| 5 | 2.00× | URA: 16 uses / Unity: Team Rank S |

### Skill Hint Discounts

| Hint Level | Discount | Example (120 SP base) |
| ------------ | ---------- | ---------------------- |
| 0 | 0% | 120 SP |
| 1 | 10% | 108 SP |
| 2 | 20% | 96 SP |
| 3 | 30% | 84 SP |
| 4 | 35% | 78 SP |
| 5 | 40% | 72 SP (MAX) |

### Stamina Requirements by Distance

| Distance | No Recovery Skills | With 1 Gold Skill | With 2 Gold Skills |
| ---------- | ------------------- | ------------------- | ------------------- |
| Sprint (1000-1400m) | 290-400 | 250-350 | 200-300 |
| Mile (1400-1800m) | 390-500 | 350-450 | 300-400 |
| Medium (1800-2400m) | 540-700 | 450-600 | 400-550 |
| Long (2400-3600m) | 790-1000 | 650-850 | 550-750 |

---

## Implementation Priorities

### Priority 1: Critical Unity Cup Features

1. **Spirit Burst System**
   - Track Spirit Gauge per team member (0-100%)
   - Display Spirit Burst availability
   - Calculate stat gains (150+ to member, 15-50 to trainee)
   - Recommend optimal timing

2. **Team Race System**
   - Schedule team races every 6 months
   - Team member selection interface
   - Team rank tracking (F/G → S)
   - Facility level progression tied to team rank

3. **Unity Training Indicators**
   - White flame visual markers
   - Red exclamation mark emphasis
   - Unity Burst opportunity alerts

### Priority 2: Core Mechanics Enhancement

1. **SP Management System**
   - SP budget tracker (target: 400-500 for gold skills)
   - "SP trap" skill warnings
   - Hint level discount calculator
   - Gold skill prioritization

2. **Stamina Crisis Warnings**
   - Distance-specific stamina requirements
   - Gold skill recommendations
   - Inheritance event tracking
   - Stamina Factor suggestions

3. **Training Formula Accuracy**
   - Implement complete multiplicative formula
   - Friendship training bonuses (Bond ≥80)
   - Multi-training bonuses (+5% per card)
   - Mood multipliers (±20% range)

### Priority 3: Advanced Features

1. **Inheritance System**
   - Blue factors (stat bonuses: ★=+5, ★★=+12, ★★★=+21)
   - Red factors (aptitude bonuses: ★=+1 grade)
   - Affinity calculator
   - Optimal parent recommendations

2. **Race Strategy Enhancements**
   - Running style optimizer
   - Weather effect calculations
   - Aptitude grade impact
   - Win probability predictions

---

## Research Methodology

### Data Sources

**Official Resources**:

- Game8 (game8.co) - Official guides and mechanics
- GameTora (gametora.com) - Comprehensive strategy guides
- UmamusumeDB (umamusumedb.com) - Community database
- UmaReference (umareference.com) - Advanced mechanics

**Community Resources**:

- Reddit r/UmaMusume - Player discussions
- Discord communities - Real-time strategy sharing
- YouTube gameplay videos - Visual mechanics verification
- Twitter/X - JP player insights and data mining

### Verification Process

1. **Cross-Reference**: Compare data across multiple sources
2. **Formula Testing**: Verify calculations match in-game results
3. **Community Validation**: Check against experienced player feedback
4. **Version Tracking**: Note Global vs JP server differences

### Content Compliance

All research content has been:

- Paraphrased and summarized from original sources
- Limited to <30 consecutive words from any single source
- Attributed with source links
- Focused on factual mechanics and formulas

---

## Maintenance Schedule

### Regular Updates

- **Monthly**: Check for new game updates and mechanic changes
- **Quarterly**: Review and update stat caps, base values
- **Major Patches**: Full research review when new scenarios release

### Version Control

- Document version numbers track major changes
- Change logs included in each research document
- Related PRD/SPEC documents updated in parallel

---

## Related Documentation

### Application Documentation

- **PRDs**: [`../02-prds/`](../02-prds/) - Product requirements
- **SPECs**: [`../02-specs/`](../02-specs/) - Technical specifications
- **Flows**: [`../01-flows/`](../01-flows/) - System flow diagrams
- **Wireframes**: [`../01-wireframes/`](../01-wireframes/) - UI specifications

### Core Documentation

- **SRS**: [`../00-core-docs/003_SRS_Software_Requirement_Specifications.md`](../00-core-docs/003_SRS_Software_Requirement_Specifications.md)
- **SDS**: [`../00-core-docs/004_SDS_Software_Design_Specifications.md`](../00-core-docs/004_SDS_Software_Design_Specifications.md)
- **DBD**: [`../00-core-docs/009_DBD_Database_Documentation.md`](../00-core-docs/009_DBD_Database_Documentation.md)

---

## Contributing

### Adding New Research

1. Create new markdown file in `docs/research/` or `docs/neuron/`
2. Follow existing document structure (version, date, sources)
3. Include table of contents for documents >500 lines
4. Add entry to this README with summary
5. Cross-reference with related PRDs/SPECs

### Research Standards

- **Accuracy**: Verify all formulas and values
- **Attribution**: Cite all sources with URLs
- **Clarity**: Use tables, diagrams, and examples
- **Completeness**: Cover edge cases and exceptions
- **Compliance**: Follow content licensing restrictions

---

## Glossary

**Common Terms**:

- **MLB**: Max Limit Break (4LB, requires 5 total card copies)
- **SP**: Skill Points (currency for acquiring skills)
- **HP**: Hit Points / Effective Stamina during races
- **Bond**: Friendship level with support cards (0-100)
- **Friendship Training**: Rainbow-glow training at Bond ≥80
- **Spirit Burst**: Unity Cup mechanic for massive stat gains
- **Unity Training**: Team-based training with white flame indicators
- **Facility Level**: Training effectiveness multiplier (Lv1-5)
- **Team Rank**: Unity Cup team stat ranking (F/G → S)
- **Aptitude**: Character proficiency grades (G → SS)
- **Factor**: Inherited stat/aptitude bonuses (★/★★/★★★)
- **Hint Level**: Skill SP discount level (0-5)
- **Growth Rate**: Character-specific stat affinity multiplier

---

## Document Control

**Version**: 1.0.0  
**Created**: January 31, 2026  
**Last Updated**: January 31, 2026  
**Status**: Active  
**Maintainer**: Development Team

**Change Log**:

- v1.0.0 (2026-01-31): Initial research index creation
  - Added Unity Cup mechanics research
  - Added URA Finale comprehensive guide
  - Added captured gameplay conversations
  - Created quick reference tables
  - Defined implementation priorities

---

End of Document
