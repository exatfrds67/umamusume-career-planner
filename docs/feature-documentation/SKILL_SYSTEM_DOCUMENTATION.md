# Skill System Documentation

## Overview

The Skill System is a comprehensive implementation of the Umamusume skill management system, including skill
categorization, SP cost calculation, hint-based discounts, skill evolution chains, and MCP-powered AI analysis.

## Features

### 1. Comprehensive Skill Database

- **20+ Skills** across all categories (Speed, Passive, Recovery, Debuff, Unique)
- **SP Cost Ranges**:
  - Normal Skills: 120-180 SP
  - Rare Skills: 180-240 SP
  - Unique Skills: Variable SP cost (280-320 SP)
- **Skill Evolution Chains**: Normal → Rare upgrade paths
- **Meta Tier Rankings**: S+, S, A, B, C tiers
- **Strategic Notes**: Usage recommendations and synergy information

### 2. Skill Categorization

#### Speed Skills

- Boost speed during races
- Examples: "Go with the Flow", "Homestretch Haste", "Quick Charge"
- Best for: Front runners and late closers

#### Passive Skills

- Always-active effects
- Examples: "Stamina Keeper", "Corner Master"
- Best for: Long-distance races and technical tracks

#### Recovery Skills

- Restore stamina during races
- Examples: "Recovery", "Second Wind"
- Best for: Marathon races and stamina management

#### Debuff Skills

- Hinder opponents
- Examples: "Blocking", "Intimidation"
- Best for: Tactical racing and front-running strategies

#### Unique Skills

- Character-specific signature skills
- Examples: "Special Week's Determination", "Silence Suzuka's Silent Step"
- Best for: Character specialization and inheritance

### 2. Unique Skill Star Level Upgrade System

All 67 character-exclusive unique skills have a two-tier effect system tied to the character's star level:

| Star Level | Skill Behavior |
| --- | --- |
| Star 1–2 | Weaker base version (`unique_base_effects`) — 200 SP cost |
| Star 3+ | Full-power version (`effects`) — starts at level 1 |
| Star 6 | Full-power version starts at level 3 (`unique_star6_initial_level`) |

#### Key Database Fields

| Field | Type | Description |
| --- | --- | --- |
| `unique_star_upgrade` | boolean \| null | `true` for all 67 upgradeable unique skills |
| `unique_star6_initial_level` | int \| null | Always `3` for upgradeable unique skills |
| `unique_base_effects` | JSON \| null | Effect map for star 1–2 (weaker version) |

#### Base Effect Reduction Pattern

Effect values in `unique_base_effects` follow a consistent rule vs full-power `effects`:

- Target Speed/Acceleration ≥ +0.20: reduced by exactly −0.20
- Small secondary effects: halved
- Stamina Recovery 0.075 → 0.035; 0.055 → 0.015

### 3. SP Cost System

#### Base Costs

```php
// Normal Skills
$normalSkill->base_sp_cost; // 120-180 SP

// Rare Skills
$rareSkill->base_sp_cost; // 180-240 SP

// Unique Skills
$uniqueSkill->base_sp_cost; // 280-320 SP (variable)
```text

#### Hint-Based Discounts

```php
// 5-level progressive discount: 10%/20%/30%/35%/40% max
$skill->calculateFinalCost(0); // Full price
$skill->calculateFinalCost(1); // 10% discount
$skill->calculateFinalCost(2); // 20% discount
$skill->calculateFinalCost(3); // 30% discount
$skill->calculateFinalCost(4); // 35% discount
$skill->calculateFinalCost(5); // 40% discount (max)

// Example: 120 SP skill
// 0 hints: 120 SP (no discount)
// 1 hint:  108 SP (10% off)
// 2 hints: 96 SP (20% off)
// 3 hints: 84 SP (30% off)
// 4 hints: 78 SP (35% off)
// 5 hints: 72 SP (40% off - maximum)
```text

### 4. Skill Evolution System

#### Evolution Chains

```php
// Normal skill can evolve to Rare
$normalSkill->canEvolve(); // true
$normalSkill->evolutionTarget; // Rare version

// Rare skill is evolved from Normal
$rareSkill->isEvolved(); // true
$rareSkill->evolutionSource; // Normal version

// Get full evolution chain
$skill->getEvolutionChain(); // [Normal, Rare]
```text

#### Evolution Examples

- "Go with the Flow" (120 SP) → "Lane Legerdemain" (180 SP)
- "Homestretch Haste" (130 SP) → "In Body and Mind" (190 SP)
- "Stamina Keeper" (140 SP) → "Stamina Master" (200 SP)

### 5. Skill Models

#### Skill Model

```php
use App\Models\Skill;

// Query skills
$speedSkills = Skill::ofType('speed')->get();
$rareSkills = Skill::ofRarity('rare')->get();
$sTierSkills = Skill::ofMetaTier('S')->get();
$evolvableSkills = Skill::canEvolve()->get();

// Calculate costs
$finalCost = $skill->calculateFinalCost($hintCount);
$discount = $skill->getDiscountPercentage($hintCount);
$saved = $skill->getSpSaved($hintCount);

// Check evolution
if ($skill->canEvolve()) {
    $target = $skill->evolutionTarget;
}
```

#### SkillHint Model

```php
use App\Models\SkillHint;

// Track hints
$hint = SkillHint::create([
    'character_id' => $character->id,
    'skill_id' => $skill->id,
    'source_type' => 'support_card',
    'source_name' => 'Kitasan Black SSR',
    'turn_obtained' => 15,
    'career_phase' => 'classic',
    'guaranteed_hint' => true,
    'discount_percentage' => 20.00,
]);

// Query hints
$unusedHints = SkillHint::unused()->get();
$guaranteedHints = SkillHint::guaranteed()->get();
$cardHints = SkillHint::fromSource('support_card')->get();
```text

#### SkillAcquisition Model

```php
use App\Models\SkillAcquisition;

// Track acquisitions
$acquisition = SkillAcquisition::create([
    'character_id' => $character->id,
    'skill_id' => $skill->id,
    'turn_acquired' => 20,
    'career_phase' => 'senior',
    'acquisition_method' => 'purchase',
    'base_sp_cost' => 120,
    'hints_used' => 2,
    'total_discount_percentage' => 40.00,
    'final_sp_cost' => 72,
    'sp_saved' => 48,
]);

// Calculate efficiency
$efficiency = $acquisition->getSpEfficiency(); // 40.0%
```text

### 6. Skill Analysis Service

#### Synergy Analysis

```php
use App\Services\SkillAnalysisService;

$service = new SkillAnalysisService();

// Analyze synergies
$skills = Skill::where('skill_type', 'speed')->get();
$synergyMap = $service->analyzeSynergies($skills);

// Result structure:
// [
//     skill_id => [
//         'skill' => Skill,
//         'synergies' => Collection,
//         'synergy_count' => int,
//         'synergy_strength' => float (0-10),
//     ]
// ]
```text

#### Acquisition Recommendations

```php
// Get optimal acquisition order
$targetSkills = Skill::ofMetaTier('S')->get();
$availableSP = 1000;

$recommendations = $service->recommendAcquisitionOrder($targetSkills, $availableSP);

// Result structure:
// [
//     [
//         'skill' => Skill,
//         'priority' => int,
//         'min_cost' => int,
//         'max_cost' => int,
//         'recommended_hints' => int,
//         'reasoning' => string,
//     ]
// ]
```

#### Build Analysis

```php
// Analyze complete skill build
$character = Character::find(1);
$skills = $character->skills;

$analysis = $service->analyzeSkillBuild($character, $skills);

// Result structure:
// [
//     'character_id' => int,
//     'total_skills' => int,
//     'skill_types' => [
//         'speed' => int,
//         'passive' => int,
//         'recovery' => int,
//         'debuff' => int,
//         'unique' => int,
//     ],
//     'meta_distribution' => [
//         'S+' => int,
//         'S' => int,
//         'A' => int,
//         'B' => int,
//         'C' => int,
//     ],
//     'synergy_analysis' => array,
//     'evolution_potential' => [
//         'evolvable_count' => int,
//         'evolved_count' => int,
//         'evolution_rate' => float,
//         'potential_upgrades' => array,
//     ],
//     'recommendations' => array,
// ]
```text

### 7. MCP Agent Integration

#### Configuration

```php
// config/mcp-agents.php

'skill_analysis' => [
    'enabled' => true,
    'server' => 'strands-agents',
    'model' => 'claude-3-5-sonnet-20241022',
    'capabilities' => [
        'skill_synergy_analysis',
        'sp_optimization',
        'hint_collection_strategy',
        'evolution_planning',
        'meta_tier_recommendations',
    ],
],
```text

#### Available Agents

1. **Skill Analysis Agent**: Analyzes synergies and provides strategic recommendations
2. **Skill Evolution Agent**: Plans long-term evolution paths
3. **SP Budget Management Agent**: Optimizes SP allocation
4. **Hint Farming Strategy Agent**: Maximizes hint collection
5. **Skill Build Planning Agent**: Creates comprehensive skill builds

### 8. Database Schema

#### Skills Table

```sql
CREATE TABLE ucp_skills (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255) UNIQUE,
    internal_id VARCHAR(255) UNIQUE,
    skill_type ENUM('speed', 'passive', 'recovery', 'debuff', 'unique'),
    rarity ENUM('normal', 'rare', 'unique'),
    base_sp_cost INT,
    evolution_target_id BIGINT NULL,
    evolution_source_id BIGINT NULL,
    can_evolve BOOLEAN DEFAULT FALSE,
    is_evolution BOOLEAN DEFAULT FALSE,
    effects JSON,
    description TEXT,
    activation_conditions JSON NULL,
    stat_requirements JSON NULL,
    support_card_sources JSON NULL,
    event_sources JSON NULL,
    inheritance_sources JSON NULL,
    meta_tier ENUM('S+', 'S', 'A', 'B', 'C') NULL,
    strategic_notes JSON NULL,
    synergy_skills JSON NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```text

## Usage Examples

### Example 1: Calculate Skill Cost with Hints

```php
$skill = Skill::where('name', 'Go with the Flow')->first();

// No hints
echo $skill->calculateFinalCost(0); // 120 SP

// With 1 hint (10% discount)
echo $skill->calculateFinalCost(1); // 108 SP

// With 2 hints (20% discount)
echo $skill->calculateFinalCost(2); // 96 SP

// With 3 hints (30% discount)
echo $skill->calculateFinalCost(3); // 84 SP

// With 4 hints (35% discount)
echo $skill->calculateFinalCost(4); // 78 SP

// With 5 hints (40% discount - maximum)
echo $skill->calculateFinalCost(5); // 72 SP
```

// SP saved
echo $skill->getSpSaved(2); // 48 SP

```text

### Example 2: Plan Skill Evolution

```php
$normalSkill = Skill::where('internal_id', 'speed_001')->first();

if ($normalSkill->canEvolve()) {
    $rareSkill = $normalSkill->evolutionTarget;
    
    echo "Evolution Path:\n";
    echo "{$normalSkill->name} ({$normalSkill->base_sp_cost} SP)\n";
    echo "  ↓\n";
    echo "{$rareSkill->name} ({$rareSkill->base_sp_cost} SP)\n";
    
    // Calculate evolution cost
    $normalCost = $normalSkill->calculateFinalCost(2); // With hints
    $rareCost = $rareSkill->calculateFinalCost(2);
    $totalCost = $normalCost + $rareCost;
    
    echo "Total Evolution Cost: {$totalCost} SP\n";
}
```text

### Example 3: Analyze Skill Build

```php
$service = new SkillAnalysisService();
$character = Character::find(1);
$skills = Skill::whereIn('id', [1, 2, 3, 4, 5])->get();

$analysis = $service->analyzeSkillBuild($character, $skills);

echo "Skill Build Analysis:\n";
echo "Total Skills: {$analysis['total_skills']}\n";
echo "Speed Skills: {$analysis['skill_types']['speed']}\n";
echo "S-Tier Skills: {$analysis['meta_distribution']['S']}\n";
echo "Evolvable Skills: {$analysis['evolution_potential']['evolvable_count']}\n";

foreach ($analysis['recommendations'] as $rec) {
    echo "- {$rec['message']} (Priority: {$rec['priority']})\n";
}
```text

## Testing

### Run All Skill Tests

```bash
php artisan test --filter=SkillManagementTest
php artisan test --filter=SkillAnalysisServiceTest
```

### Seed Skills Database

```bash
# Consolidated skill seeder (replaces legacy ComprehensiveSkillSeeder and RealUmaMusumeSkillsSeeder)
php artisan db:seed --class=UcpSkillsSeeder
```text

## Requirements Validation

✅ **Requirement 4.4**: Comprehensive skill management with hint-based cost reduction
✅ **Requirement 31.1**: Skill categorization (Speed, Passive, Recovery, Debuff, Unique)
✅ **Requirement 31.4**: Skill evolution mapping (Normal → Rare upgrade paths)
✅ **Requirement 56.3**: MCP-powered Skill Analysis Agent integration

## Next Steps

1. Implement UI for skill management
2. Add API endpoints for skill operations
3. Integrate with training system for hint acquisition
4. Implement skill acquisition workflow
5. Add skill performance tracking

