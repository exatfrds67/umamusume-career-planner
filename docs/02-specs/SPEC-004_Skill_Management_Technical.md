# SPEC-004: Skill Management System - Technical Specification

**Document Version**: 1.0 | **Date**: January 14, 2026 | **Status**: Draft

**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirement 4: Comprehensive Skill Management)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Skill Management Architecture)
- `.kiro/specs/umamusume-career-planner-main/tasks.md` (Task 4.x: Skill System)

**Related Artifacts**:

- PRD: [PRD-004](../prds/PRD-004_Skill_Management.md)
- Flow: [FLOW-004](../flows/FLOW-004_Skill_Management_System.md)
- Wireframes: [WF-008](../wireframes/WF-008_Skill_Shop_Interface.md), [WF-009](../wireframes/WF-009_Skill_Loadout_Manager.md)
- Sequences: [SEQ-003](../sequences/SEQ-003_Skill_Acquisition_and_Upgrade.md)
- User Flows: [UF-005](../user-flows/UF-005_Skill_Management_Flow.md)

## Overview

The Skill Management System handles skill acquisition, cost optimization, hint tracking, and skill evolution mechanics. It integrates with the training optimization system to provide real-time SP cost reduction opportunities.

## Core Entities

- **Skill**: Base skill definition with SP cost, category, effects
- **SkillAcquisition**: Record of skill ownership and acquisition date
- **SkillHint**: SP cost reduction opportunity from support cards
- **SkillEvolution**: Path from Normal to Rare skill variants

## Key Features

### 4.1 Skill Catalog

```php
class Skill extends Model
{
    const CATEGORIES = ['Normal', 'Rare', 'Unique'];
    const STAT_TARGETS = ['Speed', 'Stamina', 'Power', 'Guts', 'Wit'];
    
    protected $fillable = [
        'name',
        'category',      // Normal, Rare, Unique
        'sp_cost',       // Base SP cost
        'target_stat',
        'effect_description',
        'rarity',
        'evolution_from', // ID if evolved from another skill
    ];
    
    const SAMPLE_EVOLUTIONS = [
        'Go with the Flow' => ['base_cost' => 120, 'evolves_to' => 'Lane Legerdemain', 'evolved_cost' => 180],
        'Homestretch Haste' => ['base_cost' => 150, 'evolves_to' => 'In Body and Mind', 'evolved_cost' => 200],
    ];
}
```

### 4.2 Skill Hint Management

```php
class SkillHintService
{
    const HINT_DISCOUNT = 20;  // 20% per hint
    const MAX_DISCOUNT = 40;   // 40% maximum (2 hints)

    public function calculateFinalCost(int $baseSpCost, int $hintCount): int
    {
        $discountPercentage = min(
            $hintCount * self::HINT_DISCOUNT,
            self::MAX_DISCOUNT
        );
        
        return (int)($baseSpCost * (1 - $discountPercentage / 100));
    }
    
    public function trackHintAcquisition(Character $char, Skill $skill, SupportCard $card): SkillHint
    {
        $existingHint = $char->skillHints()
            ->where('skill_id', $skill->id)
            ->first();
        
        if ($existingHint) {
            $existingHint->increment('hint_count');
            return $existingHint;
        }
        
        return $char->skillHints()->create([
            'skill_id' => $skill->id,
            'support_card_id' => $card->id,
            'hint_count' => 1,
        ]);
    }
}
```

### 4.3 Skill Evolution

```php
class SkillEvolutionService
{
    public function evolveSkill(Character $char, Skill $normalSkill): ?Skill
    {
        $evolution = $normalSkill->evolution;
        
        if (!$evolution) {
            return null;  // No evolution available
        }
        
        // Acquire rare version if not owned
        if (!$char->hasSkill($evolution->id)) {
            $char->skills()->attach($evolution->id, [
                'acquired_at' => now(),
                'source' => 'evolution',
            ]);
        }
        
        // Remove normal version (completely replaced)
        $char->skills()->detach($normalSkill->id);
        
        return $evolution;
    }
}
```

## API Endpoints

### GET /api/v1/characters/{id}/skills

Get all skills with hint tracking and acquisition costs

### GET /api/v1/skills

Catalog of all skills with evolution paths

### POST /api/v1/characters/{id}/skills/{skillId}/acquire

Acquire skill with SP cost (considering hints)

### GET /api/v1/characters/{id}/skill-recommendations

AI-powered skill building recommendations

## Database Schema

```sql
CREATE TABLE skills (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    category ENUM('Normal', 'Rare', 'Unique') NOT NULL,
    base_sp_cost INT NOT NULL,
    target_stat VARCHAR(50),
    effect_description TEXT,
    evolution_to_id BIGINT UNSIGNED,
    evolution_cost_increase INT,
    created_at TIMESTAMP,
    UNIQUE KEY unique_skill_name (name)
) ENGINE=InnoDB;

CREATE TABLE skill_acquisitions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    character_id BIGINT UNSIGNED NOT NULL,
    skill_id BIGINT UNSIGNED NOT NULL,
    acquired_at TIMESTAMP,
    source VARCHAR(50),  // 'training', 'evolution', 'inheritance'
    sp_cost_paid INT,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skills(id),
    UNIQUE KEY unique_char_skill (character_id, skill_id),
    INDEX idx_acquired_at (acquired_at)
) ENGINE=InnoDB;
```

## Testing

- [ ] Skill acquisition with various SP costs
- [ ] Hint tracking and discount calculation
- [ ] Skill evolution mechanics (Normal → Rare)
- [ ] Support card skill hint identification
- [ ] SP optimization strategies
- [ ] Skill recommendation engine

---

**Related**: [PRD-004], [SPEC-002], [SPEC-006]
