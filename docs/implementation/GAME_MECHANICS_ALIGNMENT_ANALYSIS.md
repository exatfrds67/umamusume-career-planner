# Game Mechanics Alignment Analysis & Recommendations

**Document Type**: Technical Analysis & Implementation Roadmap  
**Date**: January 28, 2026  
**Status**: Active - Requires Immediate Action  
**Priority**: P0 - Critical Business Logic Corrections

---

## Executive Summary

After comprehensive analysis of the codebase against verified game mechanics from authoritative sources, I've identified **4 critical discrepancies** and **12 high-priority alignment tasks** that require immediate attention. The current implementation contains fundamental calculation errors that affect core gameplay simulation accuracy.

**Impact Assessment**:

- **Skill System**: 100% of SP cost calculations are incorrect (using 20%/40% instead of 10%/20%/30%/35%/40%)
- **Aptitude System**: Database schema includes non-existent SS rank
- **Stat System**: Hard cap at 1200 prevents accurate simulation of high-level gameplay
- **Training System**: Missing verified formula components (5+ multipliers)

**Recommended Approach**: Phased implementation using subagents, starting with highest-impact corrections.

---

## Part 1: Critical Discrepancies Found

### 1.1 Skill Hint System (CRITICAL - P0)

**Current Implementation**:

```php
// app/Services/SkillHintService.php
private const MAX_DISCOUNT_HINTS = 2;
private const DISCOUNT_PER_HINT = 20.0;
private const MAX_DISCOUNT_PERCENTAGE = 40.0;

// Result: 1 hint = 20%, 2 hints = 40% (MAX)
```

**Verified Game Mechanics** (from game-mechanics-research-report.md):

- **1 hint**: 10% discount (0.9× cost)
- **2 hints**: 20% discount (0.8× cost)
- **3 hints**: 30% discount (0.7× cost)
- **4 hints**: 35% discount (0.65× cost)
- **5 hints**: 40% discount (0.6× cost) - **MAXIMUM**

**Impact**:

- ❌ All SP cost calculations are incorrect
- ❌ Players receive 2x discount too early (at 2 hints instead of 5)
- ❌ Strategic planning guidance is fundamentally wrong
- ❌ Affects: SkillHintService, SkillAcquisition, SP budget tracking, AI recommendations

**Files Affected**:

- `app/Services/SkillHintService.php` (primary)
- `app/Models/SkillHint.php`
- `app/Models/SkillAcquisition.php`
- `database/migrations/*_skill_hints_table.php`
- `database/migrations/*_skill_acquisitions_table.php`
- All tests referencing hint discounts (50+ files)

**Estimated Effort**: 3-4 days (high test coverage requires extensive updates)

---

### 1.2 Aptitude Grade System (HIGH - P0)

**Current Implementation**:

```php
// database/migrations/2026_01_12_030026_create_aptitudes_table.php
$table->enum('grade', [
    'G', 'G+', 'F', 'F+', 'E', 'E+', 'D', 'D+', 
    'C', 'C+', 'B', 'B+', 'A', 'A+', 'S', 'SS'  // ❌ SS does not exist
])->comment('Aptitude grade');
```

**Verified Game Mechanics**:
> **VERIFIED (Jan 2026)**: S-rank is the maximum aptitude grade. SS does NOT exist in the current game version.

**Impact**:

- ❌ Database schema allows invalid data
- ❌ UI may display non-existent grades
- ❌ Validation logic may accept SS rank
- ⚠️ Low immediate impact (no SS data exists yet)

**Files Affected**:

- `database/migrations/2026_01_12_030026_create_aptitudes_table.php`
- `app/Models/Aptitude.php` (validation)
- `tests/Pest.php` (dataset includes SS)
- UI components displaying aptitude grades

**Estimated Effort**: 1 day (migration + validation updates)

---

### 1.3 Stat Range and Soft Cap (MEDIUM - P1)

**Current Implementation**:

```php
// tests/Pest.php
expect()->extend('toBeValidStat', function (): Pest\Expectation {
    return $expectation->toBeInt()
        ->toBeGreaterThanOrEqual(0)
        ->toBeLessThanOrEqual(1200);  // ❌ Hard cap at 1200
});

// Multiple validation points enforce 1200 max
```

**Verified Game Mechanics**:

- **Standard Range**: 0-1200+ (no hard cap)
- **Soft cap at 1200**: Stats above 1200 count for half value
- **Important breakpoints**: 901, 1200, 1600
- **Special mechanics**: Stamina at 1200+ activates "Stamina Contest" buff

**Impact**:

- ❌ Cannot simulate high-level gameplay (1200+ stats)
- ❌ Missing diminishing returns calculation
- ❌ Missing special mechanic triggers (Stamina Contest)
- ⚠️ Affects advanced players and optimization scenarios

**Files Affected**:

- `tests/Pest.php` (validation helper)
- `app/Models/Character.php` (stat validation)
- `app/Services/TrainingCalculationService.php` (needs diminishing returns)
- UI components (need soft cap indicator)
- All stat validation logic

**Estimated Effort**: 2-3 days (validation + UI + calculation updates)

---

### 1.4 Training Calculation Formula (HIGH - P1)

**Current Implementation**:

```php
// app/Services/TrainingCalculationService.php
$totalMultiplier = 1.0
    + $supportCardBonus
    + $friendshipMultiplier
    + $facilityBonus
    + $growthRateBonus;
```

**Verified Game Mechanics** (from research report):

```math
Stat Gain = (Base + StatBonus)
          × (1 + GrowthRate)
          × (1 + MoodMultiplier × (1 + MoodEffect))
          × (1 + TrainingEffect)
          × (1 + 0.05 × NumSupportCards)
          × FriendshipMultiplier
```

**Missing Components**:

- ❌ MoodEffect multiplier (±2% per mood level from neutral)
- ❌ TrainingEffect from support card traits
- ❌ +5% per support card present (currently using 10% per matching type)
- ❌ FriendshipMultiplier as product, not additive
- ❌ Per-training cap (+100 max, +50 if stat > 1200)

**Impact**:

- ⚠️ Training predictions are approximate, not accurate
- ⚠️ AI recommendations based on incorrect calculations
- ⚠️ Affects optimization and planning accuracy

**Files Affected**:

- `app/Services/TrainingCalculationService.php` (complete rewrite needed)
- `app/Services/Training/*` (related services)
- All training prediction tests

**Estimated Effort**: 5-7 days (complex formula + extensive testing)

---

## Part 2: Implementation Priority Matrix

### Priority Tier 1 (Immediate - Week 1)

| Task | Impact | Effort | Risk | Order |
| ------ | -------- | -------- | ------ | ------- |
| **1.1 Skill Hint System** | Critical | 3-4d | Medium | 1st |
| **1.2 Aptitude SS Rank** | High | 1d | Low | 2nd |

**Rationale**: Skill hint system affects every SP calculation in the application. This is the highest-impact correction. Aptitude fix is quick and prevents future data corruption.

---

### Priority Tier 2 (High Priority - Week 2-3)

| Task | Impact | Effort | Risk | Order |
| ------ | -------- | -------- | ------ | ------- |
| **1.3 Stat Range & Soft Cap** | Medium | 2-3d | Medium | 3rd |
| **1.4 Training Formula** | High | 5-7d | High | 4th |

**Rationale**: Stat range enables advanced gameplay simulation. Training formula is complex but critical for accuracy.

---

### Priority Tier 3 (Enhancement - Week 4+)

| Task | Impact | Effort | Risk | Order |
| ------ | -------- | -------- | ------ | ------- |
| Weather/Track Conditions | Medium | 3-4d | Medium | 5th |
| Advanced Race Physics | Low | 4-5d | High | 6th |
| UI Component Library | Medium | 8-10d | Low | 7th |

---

## Part 3: Recommended Implementation Strategy

### Strategy A: Phased Subagent Delegation (RECOMMENDED)

**Approach**: Create individual specs for each critical fix, delegate to requirements-first-workflow subagent.

**Advantages**:

- ✅ Isolated changes reduce risk
- ✅ Parallel development possible
- ✅ Clear acceptance criteria per spec
- ✅ Incremental testing and validation
- ✅ Easier rollback if issues arise

**Execution Plan**:

#### Phase 1: Skill Hint System Correction (Week 1)

```
Spec: skill-hint-system-correction
├── Requirements
│   ├── Update discount calculation (10%/20%/30%/35%/40%)
│   ├── Support 5 hint levels (not 2)
│   ├── Update all SP cost calculations
│   └── Maintain backward compatibility
├── Design
│   ├── Service layer changes
│   ├── Database schema updates
│   ├── Test suite updates
│   └── Migration strategy
└── Tasks
    ├── Update SkillHintService constants
    ├── Modify calculateDiscountPercentage()
    ├── Update database migrations
    ├── Fix 50+ test files
    └── Update UI components
```

**Subagent Invocation**:

```
invokeSubAgent(
  name: "requirements-first-workflow",
  prompt: "Create spec for correcting skill hint discount system from 20%/40% (max 2 hints) to 10%/20%/30%/35%/40% (max 5 hints) based on verified game mechanics",
  explanation: "Critical business logic correction for SP cost calculations"
)
```

#### Phase 2: Aptitude Grade Correction (Week 1)

```
Spec: aptitude-grade-system-correction
├── Requirements
│   ├── Remove SS rank from enum
│   ├── Update validation logic
│   ├── Verify no SS data exists
│   └── Update tests and datasets
├── Design
│   ├── Migration to remove SS
│   ├── Validation updates
│   └── Test updates
└── Tasks
    ├── Create migration to alter enum
    ├── Update Aptitude model validation
    ├── Fix test datasets
    └── Update UI grade displays
```

#### Phase 3: Stat Range & Soft Cap (Week 2)

```
Spec: stat-range-soft-cap-implementation
├── Requirements
│   ├── Remove 1200 hard cap
│   ├── Implement diminishing returns (50% above 1200)
│   ├── Add breakpoint indicators (901, 1200, 1600)
│   └── Implement Stamina Contest trigger
├── Design
│   ├── Validation logic updates
│   ├── Calculation service updates
│   ├── UI soft cap indicator
│   └── Special mechanic triggers
└── Tasks
    ├── Update validation helpers
    ├── Add diminishing returns calculation
    ├── Create UI soft cap component
    ├── Implement special mechanics
    └── Update all tests
```

#### Phase 4: Training Formula Alignment (Week 3)

```
Spec: training-calculation-formula-alignment
├── Requirements
│   ├── Implement verified multiplicative formula
│   ├── Add missing components (MoodEffect, TrainingEffect, etc.)
│   ├── Implement per-training caps
│   └── Maintain API compatibility
├── Design
│   ├── Complete formula breakdown
│   ├── Component calculation methods
│   ├── Cap enforcement logic
│   └── Test strategy
└── Tasks
    ├── Rewrite calculateTrainingPrediction()
    ├── Add missing multiplier calculations
    ├── Implement training caps
    ├── Update all training tests
    └── Validate against game data
```

---

### Strategy B: Monolithic Update (NOT RECOMMENDED)

**Approach**: Create one large spec covering all changes.

**Disadvantages**:

- ❌ High risk of cascading failures
- ❌ Difficult to test incrementally
- ❌ Hard to rollback partial changes
- ❌ Longer time to first value
- ❌ Complex dependency management

---

## Part 4: Testing Strategy

### 4.1 Test Coverage Requirements

**Per Spec**:

- ✅ Unit tests for all calculation methods
- ✅ Integration tests for service interactions
- ✅ Feature tests for user workflows
- ✅ Property-based tests for formula validation
- ✅ Regression tests for existing functionality

#### Example: Skill Hint System Tests

```php
// Unit Tests
it('calculates 10% discount for 1 hint', function () {
    $service = new SkillHintService();
    expect($service->calculateDiscountPercentage(1))->toBe(10.0);
});

it('calculates 40% max discount for 5 hints', function () {
    $service = new SkillHintService();
    expect($service->calculateDiscountPercentage(5))->toBe(40.0);
});

it('does not exceed 40% for 6+ hints', function () {
    $service = new SkillHintService();
    expect($service->calculateDiscountPercentage(6))->toBe(40.0);
});

// Property-Based Tests
it('discount increases monotonically with hint count', function () {
    $service = new SkillHintService();
    
    for ($i = 1; $i <= 10; $i++) {
        $current = $service->calculateDiscountPercentage($i);
        $previous = $service->calculateDiscountPercentage($i - 1);
        
        expect($current)->toBeGreaterThanOrEqual($previous);
        expect($current)->toBeLessThanOrEqual(40.0);
    }
});
```

### 4.2 Validation Against Game Data

**Approach**: Use verified examples from research report to validate calculations.

**Example Validation Cases**:

```php
// From game-mechanics-research-report.md Section 4.1
it('matches verified hint discount rates', function () {
    $skill = Skill::factory()->create(['base_sp_cost' => 100]);
    $service = new SkillHintService();
    
    // Verified: 1 hint = 10% discount (0.9× cost)
    expect($service->calculateFinalCost($skill, 1))->toBe(90);
    
    // Verified: 2 hints = 20% discount (0.8× cost)
    expect($service->calculateFinalCost($skill, 2))->toBe(80);
    
    // Verified: 3 hints = 30% discount (0.7× cost)
    expect($service->calculateFinalCost($skill, 3))->toBe(70);
    
    // Verified: 4 hints = 35% discount (0.65× cost)
    expect($service->calculateFinalCost($skill, 4))->toBe(65);
    
    // Verified: 5 hints = 40% discount (0.6× cost) - MAXIMUM
    expect($service->calculateFinalCost($skill, 5))->toBe(60);
});
```

---

## Part 5: Migration Strategy

### 5.1 Data Migration for Skill Hints

**Challenge**: Existing skill hint data uses 2-level system, need to migrate to 5-level system.

**Strategy**:

```php
// Migration: Update existing hint levels
public function up(): void
{
    // Existing data: hint_level 1 or 2
    // New system: hint_level 1-5
    
    // Option A: Keep existing data as-is (1 or 2 hints)
    // No migration needed, just update calculation logic
    
    // Option B: Recalculate based on source data
    // Complex, requires re-analyzing hint sources
    
    // RECOMMENDED: Option A (backward compatible)
}
```

**Backward Compatibility**:

- ✅ Existing hint_level values (1-2) remain valid
- ✅ New hints can use levels 3-5
- ✅ Calculation logic handles all levels correctly
- ✅ No data loss or corruption

### 5.2 Database Schema Updates

**Aptitude Grade Migration**:

```php
public function up(): void
{
    // Remove SS from enum
    DB::statement("
        ALTER TABLE ucp_aptitudes 
        MODIFY COLUMN grade ENUM(
            'G', 'G+', 'F', 'F+', 'E', 'E+', 
            'D', 'D+', 'C', 'C+', 'B', 'B+', 
            'A', 'A+', 'S'
        ) COMMENT 'Aptitude grade (S is maximum)'
    ");
    
    // Verify no SS data exists
    $ssCount = DB::table('ucp_aptitudes')
        ->where('grade', 'SS')
        ->count();
    
    if ($ssCount > 0) {
        throw new \Exception("Found {$ssCount} aptitudes with SS grade. Manual review required.");
    }
}
```

---

## Part 6: Risk Assessment & Mitigation

### 6.1 Technical Risks

| Risk | Probability | Impact | Mitigation |
| ------ | ------------- | -------- | ------------ |
| Test suite failures | High | Medium | Update tests incrementally, maintain CI |
| Breaking changes in API | Medium | High | Version API endpoints, deprecation notices |
| Data migration issues | Low | High | Dry-run migrations, backup strategy |
| Performance regression | Low | Medium | Benchmark before/after, optimize queries |
| UI component breakage | Medium | Medium | Component tests, visual regression testing |

### 6.2 Business Risks

| Risk | Probability | Impact | Mitigation |
| ------ | ------------- | -------- | ------------ |
| User confusion (changed calculations) | High | Medium | Release notes, migration guide, FAQ |
| Existing plans become invalid | Medium | High | Provide recalculation tool, explain changes |
| Loss of user trust | Low | High | Transparent communication, accuracy validation |

---

## Part 7: Success Criteria

### 7.1 Technical Acceptance Criteria

**Per Spec**:

- [ ] All unit tests pass (80%+ coverage)
- [ ] All integration tests pass
- [ ] All feature tests pass
- [ ] Property-based tests validate formulas
- [ ] No regression in existing functionality
- [ ] Performance benchmarks met (<2s page load)
- [ ] Accessibility maintained (WCAG 2.2 AA)

**Overall**:

- [ ] All 4 critical discrepancies corrected
- [ ] Calculations match verified game mechanics
- [ ] Documentation updated
- [ ] Migration guides published
- [ ] User communication completed

### 7.2 Validation Criteria

**Formula Validation**:

- [ ] Skill hint discounts match verified rates (10%/20%/30%/35%/40%)
- [ ] Aptitude grades limited to G-S (no SS)
- [ ] Stats support 1200+ with diminishing returns
- [ ] Training formula includes all verified components

**User Validation**:

- [ ] Existing users can recalculate plans
- [ ] New calculations produce realistic results
- [ ] AI recommendations improve in accuracy
- [ ] No data loss during migration

---

## Part 8: Timeline & Resource Allocation

### 8.1 Estimated Timeline

**Total Duration**: 3-4 weeks (15-20 working days)

| Phase | Duration | Parallel? | Dependencies |
| ------- | ---------- | ----------- | -------------- |
| Phase 1: Skill Hints | 3-4 days | No | None |
| Phase 2: Aptitude Grades | 1 day | Yes (with Phase 1) | None |
| Phase 3: Stat Range | 2-3 days | No | Phase 1 complete |
| Phase 4: Training Formula | 5-7 days | No | Phase 3 complete |
| Testing & Validation | 2-3 days | No | All phases complete |
| Documentation & Release | 1-2 days | No | Testing complete |

**Critical Path**: Phase 1 → Phase 3 → Phase 4 (10-14 days)

### 8.2 Resource Requirements

**Development**:

- 1 senior developer (full-time, 3-4 weeks)
- 1 QA engineer (part-time, testing phases)
- 1 technical writer (part-time, documentation)

**Subagent Usage**:

- requirements-first-workflow subagent (4 invocations)
- spec-task-execution subagent (task execution)
- context-gatherer subagent (codebase analysis as needed)

---

## Part 9: Communication Plan

### 9.1 Internal Communication

**Stakeholders**:

- Development team
- QA team
- Product management
- Technical documentation team

**Artifacts**:

- This analysis document
- Individual spec documents (4)
- Migration guides
- Test reports
- Release notes

### 9.2 User Communication

**Channels**:

- In-app notification
- Email to active users
- Documentation updates
- FAQ section
- Video tutorial (optional)

**Key Messages**:

- "We've updated our calculations to match verified game mechanics"
- "Your existing plans may show different SP costs - this is expected"
- "Use the recalculation tool to update your plans"
- "Accuracy improvements benefit strategic planning"

---

## Part 10: Next Steps

### Immediate Actions (Today)

1. **Review & Approve**: Stakeholder review of this analysis
2. **Prioritize**: Confirm priority order (Skill Hints → Aptitudes → Stats → Training)
3. **Prepare**: Set up spec directory structure

### Week 1 Actions

1. **Invoke Subagent**: Create skill-hint-system-correction spec
2. **Invoke Subagent**: Create aptitude-grade-system-correction spec
3. **Begin Implementation**: Start Phase 1 (Skill Hints)
4. **Parallel Work**: Complete Phase 2 (Aptitudes)

### Week 2-3 Actions

1. **Invoke Subagent**: Create stat-range-soft-cap-implementation spec
2. **Invoke Subagent**: Create training-calculation-formula-alignment spec
3. **Continue Implementation**: Phases 3-4
4. **Testing**: Continuous integration testing

### Week 4 Actions

1. **Final Testing**: Comprehensive test suite
2. **Documentation**: Complete all user-facing docs
3. **Migration Tools**: Build recalculation utilities
4. **Release Prep**: Staging deployment, final validation

---

## Appendix A: Code Examples

### A.1 Corrected Skill Hint Service

```php
<?php

namespace App\Services;

class SkillHintService
{
    /**
     * Maximum number of hints that provide discount (40% max at 5 hints)
     * VERIFIED: Levels 1-3 provide 10% each, levels 4-5 provide 5% each
     */
    private const MAX_DISCOUNT_HINTS = 5;

    /**
     * Calculate the discount percentage based on hint count.
     * 
     * Verified discount rates:
     * - 1 hint: 10% (0.9× cost)
     * - 2 hints: 20% (0.8× cost)
     * - 3 hints: 30% (0.7× cost)
     * - 4 hints: 35% (0.65× cost)
     * - 5 hints: 40% (0.6× cost) - MAXIMUM
     */
    public function calculateDiscountPercentage(int $hintCount): float
    {
        $effectiveHints = min($hintCount, self::MAX_DISCOUNT_HINTS);
        
        return match($effectiveHints) {
            1 => 10.0,
            2 => 20.0,
            3 => 30.0,
            4 => 35.0,
            5 => 40.0,
            default => 0.0,
        };
    }
    
    // ... rest of service
}
```

### A.2 Corrected Aptitude Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Verify no SS data exists before migration
        $ssCount = DB::table('ucp_aptitudes')
            ->where('grade', 'SS')
            ->count();
        
        if ($ssCount > 0) {
            throw new \Exception(
                "Found {$ssCount} aptitudes with SS grade. " .
                "SS rank does not exist in game. Manual review required."
            );
        }
        
        // Remove SS from enum (S is maximum)
        DB::statement("
            ALTER TABLE ucp_aptitudes 
            MODIFY COLUMN grade ENUM(
                'G', 'G+', 'F', 'F+', 'E', 'E+', 
                'D', 'D+', 'C', 'C+', 'B', 'B+', 
                'A', 'A+', 'S'
            ) COMMENT 'Aptitude grade (S is maximum, verified Jan 2026)'
        ");
    }
    
    public function down(): void
    {
        // Restore SS for rollback (though it shouldn't be used)
        DB::statement("
            ALTER TABLE ucp_aptitudes 
            MODIFY COLUMN grade ENUM(
                'G', 'G+', 'F', 'F+', 'E', 'E+', 
                'D', 'D+', 'C', 'C+', 'B', 'B+', 
                'A', 'A+', 'S', 'SS'
            ) COMMENT 'Aptitude grade'
        ");
    }
};
```

---

## Appendix B: Reference Documentation

### B.1 Source Documents

1. **game-mechanics-research-report.md** - Verified game mechanics from authoritative sources
2. **GAME_MECHANICS_AUDIT_REPORT.md** - Discrepancy analysis
3. **IMPLEMENTATION_PLAN.md** - 15-week comprehensive implementation plan
4. **game-alignment-plan.md** - UI/UX alignment strategy

### B.2 Key Findings Summary

**Skill Hint System** (Section 4.1):

- Verified: 10%/20%/30%/35%/40% at levels 1-5
- Source: Reddit Community, umamusu.wiki
- Confidence: High (multiple sources)

**Aptitude Grades** (Section 5.1):

- Verified: S is maximum (no SS)
- Source: Game8, Steam Community, umamusume.gg
- Confidence: Very High (official guides)

**Stat Ranges** (Section 1.2):

- Verified: 0-1200+ with diminishing returns
- Source: PCGamesN, Game8
- Confidence: High (game data analysis)

**Training Formula** (Section 2.2):

- Verified: Complex multiplicative formula
- Source: UmamusumeDB, UmaReference.com
- Confidence: Very High (calculator implementations)

---

## Document Control

**Version**: 1.0.0  
**Author**: AI Analysis System  
**Date**: January 28, 2026  
**Status**: Active - Awaiting Approval  
**Next Review**: After Phase 1 completion

**Approval Required From**:

- [ ] Technical Lead
- [ ] Product Manager
- [ ] QA Lead

**Related Documents**:

- `docs/research/game-mechanics-research-report.md`
- `docs/GAME_MECHANICS_AUDIT_REPORT.md`
- `docs/design/IMPLEMENTATION_PLAN.md`
- `docs/design/game-alignment-plan.md`

---

## END OF ANALYSIS
