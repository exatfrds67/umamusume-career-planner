# SEQ-002: Training Block Resolution

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.3.0
**Date**: January 28, 2026
**Related Documents**: [PRD-002], [SPEC-002], [FLOW-002], [TECH-FLOW-002]

---

## Table of Contents

1. [Overview](#1-overview)
2. [Game-Accurate Training Mechanics](#2-game-accurate-training-mechanics)
3. [Participants](#3-participants)
4. [Sequence Flow](#4-sequence-flow)
5. [Detailed Interactions](#5-detailed-interactions)
6. [Data Structures](#6-data-structures)
7. [Error Handling](#7-error-handling)
8. [Performance Considerations](#8-performance-considerations)
9. [Related Documentation](#9-related-documentation)

---

## 1. Overview

### 1.1 Purpose

This sequence diagram documents the training prediction and execution workflow at a technical level.
Where older examples in this document show account-backed controller and database interactions, they
should be interpreted as the current authenticated execution path only. Local-mode training planning
and advisory context should be treated as browser-managed and storage-aware, consistent with
`FLOW-002` and `TECH-FLOW-002`.

### 1.2 Scope

**Covers:**

- Account-mode training prediction and execution flows
- Storage-aware distinction between Local advisory/planning context and Account-mode persistence
- Training prediction generation for all 5 training facilities (Speed, Stamina, Power, Guts, Wit)
- **Complete training formula with all multipliers**
- **Facility level multipliers (1.0× to 2.0×)**
- **Stat soft cap at 1200 with 50% reduction**
- **Bond mechanics (+7 base, +9 with Charming)**
- **Friendship Training threshold (80% bond) and bonuses**
- Support card bonus calculation
- Risk assessment and failure probability
- Skill hint probability calculation
- Training execution and stat updates
- Energy and mood progression
- Event dispatch and polling-visible status updates

**Storage-mode note:** `StorageMode::LOCAL` state may remain browser-managed and UUID-oriented until
converted through the storage transition flow documented in
[SEQ-017](SEQ-017_Storage_Mode_Transition.md).

**Related Artifacts:**

- PRD: [PRD-002](../02-prds/PRD-002_Training_Optimization.md)
- SPEC: [SPEC-002](../02-specs/SPEC-002_Training_Optimization_Technical.md)
- Flow: [FLOW-002](../01-flows/FLOW-002_Training_Optimization_System.md)
- Tech Flow: [TECH-FLOW-002](../01-tech-flow/TECH-FLOW-002_Training_Optimization_Flow.md)
- Wireframe: [WF-004](../01-wireframes/WF-004_Training_Selection_Interface.md),
[WF-005](../01-wireframes/WF-005_Training_Result_Screen.md)
- User Flow: [UF-003](../01-user-flows/UF-003_Training_Day_Flow.md)

### 1.3 Business Context

Training resolution is the core gameplay loop that:

- Drives character stat progression
- Enables skill hint acquisition
- Manages energy and mood dynamics
- Triggers friendship training events
- Records turn-by-turn history

**Success Criteria:**

- Predictions generated within 200ms (with caching)
- Training execution completes within 300ms
- Stat updates reflected immediately in UI
- Status update propagation to active sessions on refresh
- Prediction cache invalidated after execution
- **Formula accuracy matches game within ±1 stat point**

---

## 2. Game-Accurate Training Mechanics

### 2.1 Complete Training Formula (Verified Jan 2026)

The official training stat gain formula from Umamusume Pretty Derby (Global English Server):

```text
Stat Gain = (Base + StatBonus) × (1 + GrowthRate) × (1 + MoodModifier)
            × (1 + FacilityLevelBonus) × (1 + 0.05 × NumSupportCards) × FriendshipMultiplier
```

> **Note**: This formula is an approximation. The game may apply additional hidden mechanics
> not fully observable from external testing.

**Formula Components:**

| Component | Description | Range |
| --- | --- | --- |
| **Base** | Base stat gain from training type | 10-25 per stat |
| **StatBonus** | Support card stat bonuses | 0-50+ |
| **GrowthRate** | Character-specific growth rate | 0-20% per stat |
| **MoodModifier** | Mood effect modifier (Great: +0.04, Good: +0.02, Normal: 0, Bad: −0.02, Awful: −0.04) | −0.04 to +0.04 |
| **FacilityLevelBonus** | Facility level bonus | 0-100% |
| **NumSupportCards** | Cards present at training | 0-6 |
| **FriendshipMultiplier** | Friendship training bonus | 1.0-1.35× |

### 2.2 Facility Level Multipliers

Training facilities have 5 levels with progressive multipliers:

| Level | Multiplier | Training Effect |
| --- | --- | --- |
| 1 | 1.00× | +0% |
| 2 | 1.25× | +25% |
| 3 | 1.50× | +50% |
| 4 | 1.75× | +75% |
| 5 | 2.00× | +100% |

**Special Case - Summer Training Camp:**

- Duration: 4 turns in Early July
- All facilities automatically at Level 5
- Enhanced training opportunities

### 2.3 Stat Soft Cap System

Stats have a soft cap at **1200** with diminishing returns:

| Stat Range | Gain Multiplier | Per-Training Cap |
| --- | --- | --- |
| 0-1200 | 100% | +100 max |
| 1200+ | 50% | +50 max |

**Implementation:**

```text
if (current_stat >= 1200):
    actual_gain = min(calculated_gain × 0.5, 50)
else if (current_stat + calculated_gain > 1200):
    below_cap = 1200 - current_stat
    above_cap = (calculated_gain - below_cap) × 0.5
    actual_gain = min(below_cap + above_cap, 100)
else:
    actual_gain = min(calculated_gain, 100)
```

### 2.4 Bond and Friendship Mechanics

**Bond Gain Per Training:**

| Condition | Bond Gain |
| --- | --- |
| Base (training together) | +7 |
| With Charming condition | +9 |

**Friendship Training:**

| Threshold | Requirement |
| --- | --- |
| Activation | Bond ≥ 80% (80 points) |
| Visual Indicator | Rainbow glow on card |

**Friendship Bonus by Card Rarity:**

| Rarity | Friendship Bonus |
| --- | --- |
| R | +10% |
| SR | +15% |
| SSR | +20-35% |

### 2.5 Support Card Presence Bonus

Each support card present at training adds a stacking bonus:

| Cards Present | Total Bonus |
| --- | --- |
| 0 | +0% |
| 1 | +5% |
| 2 | +10% |
| 3 | +15% |
| 4 | +20% |
| 5 | +25% |
| 6 | +30% |

### 2.6 Training Types and Stat Distribution

Each training type affects multiple stats:

| Training | Primary Stat | Secondary Stats |
| --- | --- | --- |
| Speed | Speed (100%) | Power (partial), Stamina (partial) |
| Stamina | Stamina (100%) | Guts (partial), Power (partial) |
| Power | Power (100%) | Stamina (partial), Guts (partial) |
| Guts | Guts (100%) | Power (partial), Wit (partial) |
| Wit | Wit (100%) | Speed (partial), Stamina (partial) |

---

## 3. Participants

### 3.1 System Components

| Component | Type | Responsibility |
| --- | --- | --- |
| **User** | Actor | Initiates training selection and execution |
| **Livewire Component** | Presentation | `TrainingSelector.php` - Training interface |
| **TrainingController** | Application | Orchestrates training workflow |
| **TrainingPredictionService** | Domain Service | Generates training predictions |
| **TrainingExecutionService** | Domain Service | Executes training and updates state |
| **StatCalculator** | Domain Service | Calculates base stat gains with game formula |
| **BonusCalculator** | Domain Service | Applies support card bonuses |
| **BondCalculator** | Domain Service | Calculates bond gains and friendship status |
| **SoftCapCalculator** | Domain Service | Applies stat soft cap rules |
| **RiskCalculator** | Domain Service | Determines failure probability |
| **Database** | Infrastructure | MySQL/MariaDB persistence layer |
| **Cache** | Infrastructure | Redis prediction cache |
| **EventDispatcher** | Infrastructure | Laravel event broadcasting |
| **Status Refresh Flow** | Infrastructure | Queue/cache-backed refresh updates |

### 3.2 Component Locations

```text
app/
├── Livewire/
│   └── Training/
│       ├── TrainingSelector.php
│       └── PredictionDisplay.php
├── Http/
│   └── Controllers/
│       └── TrainingController.php
├── Services/
│   ├── TrainingPredictionService.php
│   ├── TrainingExecutionService.php
│   ├── StatCalculator.php
│   ├── BonusCalculator.php
│   ├── BondCalculator.php
│   ├── SoftCapCalculator.php
│   └── RiskCalculator.php
├── Models/
│   ├── Career.php
│   ├── TrainingSession.php
│   └── SupportDeck.php
└── Events/
    ├── TrainingCompleted.php
    └── StatsUpdated.php
```

---

## 4. Sequence Flow

### 4.1 High-Level Flow Diagram

```mermaid
sequenceDiagram
    actor User
    participant UI as Training UI
    participant Controller as TrainingController
    participant PredictSvc as TrainingPredictionService
    participant ExecSvc as TrainingExecutionService
    participant DB as Database
    participant Local as Browser localStorage

    User->>UI: Open training options

    alt StorageMode::ACCOUNT
        UI->>Controller: Request account-backed prediction/execution
        Controller->>PredictSvc: Load predictions from persisted character/career context
        PredictSvc-->>Controller: Prediction payload
        User->>Controller: Confirm training action
        Controller->>ExecSvc: Execute account-backed training
        ExecSvc->>DB: Persist updated state and training session
        ExecSvc-->>Controller: Training result
        Controller-->>UI: Updated account-backed result
    else StorageMode::LOCAL
        UI->>Local: Read UUID-backed local run payload
        UI->>UI: Calculate or request storage-aware advisory context
        User->>UI: Confirm local training action
        UI->>Local: Persist updated local run payload
        UI-->>User: Updated local result without DB write
    end
```text

### 4.2 Training Formula Calculation Flow

```mermaid
sequenceDiagram
    participant Caller
    participant StatCalc as StatCalculator
    participant FacilityCalc as FacilityCalculator
    participant GrowthCalc as GrowthRateCalculator
    participant MoodCalc as MoodCalculator
    participant CardCalc as CardPresenceCalculator
    participant FriendCalc as FriendshipCalculator
    participant SoftCapCalc as SoftCapCalculator

    Caller->>StatCalc: calculateStatGain(training, career, deck)

    Note over StatCalc: Step 1: Base + StatBonus
    StatCalc->>StatCalc: getBaseGain(trainingType)
    StatCalc->>StatCalc: getStatBonus(deck, trainingType)
    StatCalc->>StatCalc: baseTotal = base + statBonus

    Note over StatCalc: Step 2: Growth Rate
    StatCalc->>GrowthCalc: getGrowthRate(character, stat)
    GrowthCalc-->>StatCalc: growthRate (0-0.20)
    StatCalc->>StatCalc: afterGrowth = baseTotal × (1 + growthRate)

    Note over StatCalc: Step 3: Mood Effect
    StatCalc->>MoodCalc: getMoodModifier(career.mood)
    MoodCalc-->>StatCalc: moodModifier (-0.04 to +0.04)
    StatCalc->>StatCalc: afterMood = afterGrowth × (1 + moodModifier)

    Note over StatCalc: Step 4: Training Effect (Facility Level)
    StatCalc->>FacilityCalc: getFacilityMultiplier(facilityLevel)
    FacilityCalc-->>StatCalc: facilityMult (1.0-2.0)
    StatCalc->>StatCalc: afterFacility = afterMood × facilityMult

    Note over StatCalc: Step 5: Support Card Presence (+5% per card)
    StatCalc->>CardCalc: countPresentCards(deck, trainingType)
    CardCalc-->>StatCalc: numCards (0-6)
    StatCalc->>StatCalc: afterCards = afterFacility × (1 + 0.05 × numCards)

    Note over StatCalc: Step 6: Friendship Multiplier
    StatCalc->>FriendCalc: getFriendshipMultiplier(deck, trainingType)
    FriendCalc-->>StatCalc: friendMult (1.0-1.35)
    StatCalc->>StatCalc: rawGain = afterCards × friendMult

    Note over StatCalc: Step 7: Apply Soft Cap
    StatCalc->>SoftCapCalc: applySoftCap(rawGain, currentStat)
    SoftCapCalc->>SoftCapCalc: Check if currentStat >= 1200
    alt Above Soft Cap
        SoftCapCalc->>SoftCapCalc: finalGain = min(rawGain × 0.5, 50)
    else Crossing Soft Cap
        SoftCapCalc->>SoftCapCalc: belowCap = 1200 - currentStat
        SoftCapCalc->>SoftCapCalc: aboveCap = (rawGain - belowCap) × 0.5
        SoftCapCalc->>SoftCapCalc: finalGain = min(belowCap + aboveCap, 100)
    else Below Soft Cap
        SoftCapCalc->>SoftCapCalc: finalGain = min(rawGain, 100)
    end
    SoftCapCalc-->>StatCalc: finalGain

    StatCalc-->>Caller: StatGainResult(finalGain, breakdown)
```

### 4.3 Bond and Friendship Calculation Flow

```mermaid
sequenceDiagram
    participant Caller
    participant BondCalc as BondCalculator
    participant ConditionChecker as ConditionChecker
    participant FriendshipCalc as FriendshipCalculator
    participant DB as Database

    Caller->>BondCalc: calculateBondGains(deck, career)

    Note over BondCalc: Check for Charming condition
    BondCalc->>ConditionChecker: hasCondition(career, 'Charming')
    ConditionChecker-->>BondCalc: hasCharming (true/false)

    loop For each card present at training
        BondCalc->>BondCalc: baseBondGain = hasCharming ? 9 : 7
        BondCalc->>BondCalc: newBondLevel = card.bondLevel + baseBondGain
        BondCalc->>BondCalc: cappedBond = min(newBondLevel, 100)

        Note over BondCalc: Check Friendship Threshold
        alt newBondLevel >= 80 AND previousBond < 80
            BondCalc->>BondCalc: Mark card as newly friendship-eligible
        end
    end

    BondCalc-->>Caller: BondGainResult(bondChanges, newFriendshipCards)

    Note over Caller,FriendshipCalc: Apply Friendship Bonus if Eligible
    Caller->>FriendshipCalc: calculateFriendshipBonus(eligibleCards)

    loop For each friendship-eligible card
        FriendshipCalc->>FriendshipCalc: getCardRarity(card)
        alt R Rarity
            FriendshipCalc->>FriendshipCalc: bonus = 0.10 (10%)
        else SR Rarity
            FriendshipCalc->>FriendshipCalc: bonus = 0.15 (15%)
        else SSR Rarity
            FriendshipCalc->>FriendshipCalc: bonus = 0.20 to 0.35 (20-35%)
        end
    end

    FriendshipCalc-->>Caller: totalFriendshipMultiplier
```text

### 4.4 Timeline Breakdown

| Phase | Duration | Description |
| --- | --- | --- |
| **Cache Check** | ~10ms | Redis cache lookup |
| **Prediction Calculation** | ~150ms | Full formula + bonuses + soft cap + risk + hints |
| **Cache Store** | ~5ms | Write to Redis |
| **User Selection** | Variable | User decision time |
| **Authorization** | ~20ms | User permission check |
| **Formula Application** | ~50ms | Complete training formula calculation |
| **Soft Cap Check** | ~5ms | Apply diminishing returns above 1200 |
| **Bond Calculation** | ~10ms | Calculate +7/+9 bond gains |
| **Database Transaction** | ~200ms | Stat updates + bond updates + history inserts |
| **Event Dispatch** | ~30ms | Queue event listeners |
| **Status Refresh** | Request-dependent | Updated state visible on refresh/poll |
| **UI Update** | ~100ms | Animation and state refresh |
| **Total (Prediction)** | ~200ms | Server-side processing |
| **Total (Execution)** | ~300ms | Server-side processing |

---

## 5. Detailed Interactions

### 5.1 Prediction Generation Phase

**Request Flow:**

```
User → Livewire Component → TrainingController → TrainingPredictionService
```text

**Service Implementation (Game-Accurate):**

```php
// app/Services/TrainingPredictionService.php
class TrainingPredictionService
{
    public function __construct(
        private StatCalculator $statCalculator,
        private BonusCalculator $bonusCalculator,
        private BondCalculator $bondCalculator,
        private SoftCapCalculator $softCapCalculator,
        private RiskCalculator $riskCalculator,
        private CacheManager $cache,
    ) {}

    public function getPredictions(Career $career): array
    {
        $cacheKey = "training_predictions:{$career->id}";

        return $this->cache->remember($cacheKey, 300, function () use ($career) {
            $facilities = TrainingType::cases();
            $predictions = [];

            foreach ($facilities as $facility) {
                $predictions[] = $this->calculatePrediction($career, $facility);
            }

            return $this->rankPredictions($predictions, $career);
        });
    }

    private function calculatePrediction(Career $career, TrainingType $facility): array
    {
        $deck = $career->supportDeck;
        $facilityLevel = $career->getFacilityLevel($facility);

        // Check for Summer Training Camp (all facilities Level 5)
        if ($career->isSummerCamp()) {
            $facilityLevel = 5;
        }

        // Step 1: Calculate base stat gains using game formula
        $rawGains = $this->statCalculator->calculateWithGameFormula(
            facility: $facility,
            career: $career,
            deck: $deck,
            facilityLevel: $facilityLevel
        );

        // Step 2: Apply soft cap (1200 threshold, 50% reduction above)
        $cappedGains = $this->softCapCalculator->applySoftCap(
            gains: $rawGains,
            currentStats: $career->getCurrentStats()
        );

        // Step 3: Calculate bond gains (+7 base, +9 with Charming)
        $bondGains = $this->bondCalculator->calculateBondGains(
            deck: $deck,
            facility: $facility,
            hasCharming: $career->hasCondition('Charming')
        );

        // Step 4: Check friendship training eligibility (bond >= 80)
        $friendshipCards = $this->bondCalculator->getFriendshipEligibleCards($deck, $facility);

        // Step 5: Calculate failure risk
        $risk = $this->riskCalculator->calculateFailureRisk($career, $facility);

        // Step 6: Calculate skill hint probability
        $hintChances = $this->calculateHintChances($deck, $facility);

        // Step 7: Score recommendation
        $score = $this->scoreTraining($career, $facility, $cappedGains, $risk);

        return [
            'facility' => $facility->value,
            'facility_level' => $facilityLevel,
            'facility_multiplier' => $this->getFacilityMultiplier($facilityLevel),
            'stat_gains' => [
                'speed' => $cappedGains->speed,
                'stamina' => $cappedGains->stamina,
                'power' => $cappedGains->power,
                'guts' => $cappedGains->guts,
                'wit' => $cappedGains->wit,
            ],
            'raw_gains' => [
                'speed' => $rawGains->speed,
                'stamina' => $rawGains->stamina,
                'power' => $rawGains->power,
                'guts' => $rawGains->guts,
                'wit' => $rawGains->wit,
            ],
            'soft_cap_applied' => $this->softCapCalculator->wasCapApplied($rawGains, $career->getCurrentStats()),
            'energy_cost' => $this->calculateEnergyCost($facility),
            'risk_percentage' => $risk,
            'skill_hints' => $hintChances,
            'bond_gains' => $bondGains,
            'friendship_training' => [
                'eligible_cards' => $friendshipCards,
                'is_active' => count($friendshipCards) > 0,
            ],
            'cards_present' => $this->countCardsPresent($deck, $facility),
            'card_presence_bonus' => $this->countCardsPresent($deck, $facility) * 5, // +5% per card
            'recommendation_score' => $score,
            'efficiency_rating' => $this->rateEfficiency($cappedGains, $risk),
        ];
    }

    private function getFacilityMultiplier(int $level): float
    {
        return match ($level) {
            1 => 1.00,
            2 => 1.25,
            3 => 1.50,
            4 => 1.75,
            5 => 2.00,
            default => 1.00,
        };
    }

    private function rankPredictions(array $predictions, Career $career): array
    {
        usort($predictions, fn($a, $b) => $b['recommendation_score'] <=> $a['recommendation_score']);
        return array_map(fn($p, $idx) => array_merge($p, ['rank' => $idx + 1]), $predictions, array_keys($predictions));
    }
}
```

### 5.2 Stat Calculation Logic (Game-Accurate Formula)

**Complete Formula Implementation:**

```php
// app/Services/StatCalculator.php
class StatCalculator
{
    /**
     * Calculate stat gains using the verified game formula:
     * Stat Gain = (Base + StatBonus) × (1 + GrowthRate) × (1 + MoodModifier)
     *             × (1 + FacilityLevelBonus) × (1 + 0.05 × NumSupportCards) × FriendshipMultiplier
     */
    public function calculateWithGameFormula(
        TrainingType $facility,
        Career $career,
        SupportDeck $deck,
        int $facilityLevel
    ): StatGains {
        $character = $career->character;
        $stats = ['speed', 'stamina', 'power', 'guts', 'wit'];
        $gains = [];

        foreach ($stats as $stat) {
            // Step 1: Base + StatBonus
            $base = $this->getBaseStat($facility, $stat);
            $statBonus = $this->getStatBonus($deck, $facility, $stat);
            $baseTotal = $base + $statBonus;

            // Step 2: Growth Rate (character-specific, 0-20%)
            $growthRate = $character->{"growth_{$stat}"} ?? 0;
            $afterGrowth = $baseTotal * (1 + $growthRate);

            // Step 3: Mood Modifier
            $moodModifier = $this->getMoodModifier($career->mood);
            $afterMood = $afterGrowth * (1 + $moodModifier);

            // Step 4: Training Effect (Facility Level)
            $trainingEffect = $this->getFacilityBonus($facilityLevel);
            $afterFacility = $afterMood * (1 + $trainingEffect);

            // Step 5: Support Card Presence Bonus (+5% per card, max +30%)
            $numCards = $this->countCardsAtFacility($deck, $facility);
            $afterCards = $afterFacility * (1 + 0.05 * $numCards);

            // Step 6: Friendship Multiplier (10-35% based on rarity)
            $friendshipMultiplier = $this->getFriendshipMultiplier($deck, $facility);
            $rawGain = $afterCards * $friendshipMultiplier;

            $gains[$stat] = (int) round($rawGain);
        }

        return new StatGains($gains);
    }

    private function getBaseStat(TrainingType $facility, string $stat): int
    {
        // Base stat gains per training type
        $baseGains = match ($facility) {
            TrainingType::Speed => ['speed' => 22, 'stamina' => 0, 'power' => 9, 'guts' => 0, 'wit' => 0],
            TrainingType::Stamina => ['speed' => 0, 'stamina' => 22, 'power' => 0, 'guts' => 9, 'wit' => 0],
            TrainingType::Power => ['speed' => 0, 'stamina' => 9, 'power' => 22, 'guts' => 0, 'wit' => 0],
            TrainingType::Guts => ['speed' => 0, 'stamina' => 0, 'power' => 9, 'guts' => 22, 'wit' => 0],
            TrainingType::Wit => ['speed' => 9, 'stamina' => 0, 'power' => 0, 'guts' => 0, 'wit' => 22],
        };

        return $baseGains[$stat] ?? 0;
    }

    private function getMoodModifier(Mood $mood): float
    {
        return match ($mood) {
            Mood::Great => 0.04,   // +4%
            Mood::Good => 0.02,    // +2%
            Mood::Normal => 0.00,  // +0%
            Mood::Bad => -0.02,    // -2%
            Mood::Awful => -0.04,  // -4%
        };
    }

    private function getFacilityBonus(int $level): float
    {
        // Facility level multipliers: 1.0×, 1.25×, 1.5×, 1.75×, 2.0×
        return match ($level) {
            1 => 0.00,  // 1.0× total
            2 => 0.25,  // 1.25× total
            3 => 0.50,  // 1.5× total
            4 => 0.75,  // 1.75× total
            5 => 1.00,  // 2.0× total
            default => 0.00,
        };
    }

    private function getFriendshipMultiplier(SupportDeck $deck, TrainingType $facility): float
    {
        $multiplier = 1.0;

        foreach ($deck->cards as $card) {
            if ($this->cardMatchesFacility($card, $facility) && $card->bond_level >= 80) {
                // Friendship bonus by rarity
                $bonus = match ($card->rarity) {
                    'R' => 0.10,      // +10%
                    'SR' => 0.15,     // +15%
                    'SSR' => 0.20 + ($card->limit_break_level * 0.03), // +20-35%
                    default => 0.10,
                };
                $multiplier += $bonus;
            }
        }

        return $multiplier;
    }
}
```text

### 5.3 Soft Cap Calculator

```php
// app/Services/SoftCapCalculator.php
class SoftCapCalculator
{
    private const SOFT_CAP = 1200;
    private const REDUCTION_RATE = 0.5; // 50% gains above soft cap
    private const MAX_GAIN_BELOW_CAP = 100;
    private const MAX_GAIN_ABOVE_CAP = 50;

    /**
     * Apply soft cap rules:
     * - Stats below 1200: 100% gains, max +100 per training
     * - Stats at/above 1200: 50% gains, max +50 per training
     */
    public function applySoftCap(StatGains $rawGains, array $currentStats): StatGains
    {
        $cappedGains = [];

        foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat) {
            $currentStat = $currentStats[$stat] ?? 0;
            $rawGain = $rawGains->{$stat};

            $cappedGains[$stat] = $this->calculateCappedGain($rawGain, $currentStat);
        }

        return new StatGains($cappedGains);
    }

    private function calculateCappedGain(int $rawGain, int $currentStat): int
    {
        // Case 1: Already above soft cap
        if ($currentStat >= self::SOFT_CAP) {
            $reducedGain = (int) ($rawGain * self::REDUCTION_RATE);
            return min($reducedGain, self::MAX_GAIN_ABOVE_CAP);
        }

        // Case 2: Will cross soft cap with this gain
        if ($currentStat + $rawGain > self::SOFT_CAP) {
            $belowCapGain = self::SOFT_CAP - $currentStat;
            $aboveCapRaw = $rawGain - $belowCapGain;
            $aboveCapGain = (int) ($aboveCapRaw * self::REDUCTION_RATE);
            $totalGain = $belowCapGain + $aboveCapGain;
            return min($totalGain, self::MAX_GAIN_BELOW_CAP);
        }

        // Case 3: Entirely below soft cap
        return min($rawGain, self::MAX_GAIN_BELOW_CAP);
    }

    public function wasCapApplied(StatGains $rawGains, array $currentStats): array
    {
        $applied = [];

        foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat) {
            $currentStat = $currentStats[$stat] ?? 0;
            $applied[$stat] = $currentStat >= self::SOFT_CAP ||
                              ($currentStat + $rawGains->{$stat}) > self::SOFT_CAP;
        }

        return $applied;
    }
}
```

### 5.4 Bond Calculator

```php
// app/Services/BondCalculator.php
class BondCalculator
{
    private const BASE_BOND_GAIN = 7;
    private const CHARMING_BOND_GAIN = 9;
    private const FRIENDSHIP_THRESHOLD = 80;
    private const MAX_BOND = 100;

    /**
     * Calculate bond gains for cards present at training.
     * Base: +7 per training together
     * With Charming condition: +9 per training together
     */
    public function calculateBondGains(
        SupportDeck $deck,
        TrainingType $facility,
        bool $hasCharming
    ): array {
        $bondGains = [];
        $bondGain = $hasCharming ? self::CHARMING_BOND_GAIN : self::BASE_BOND_GAIN;

        foreach ($deck->cards as $card) {
            if ($this->cardPresentAtFacility($card, $facility)) {
                $newBond = min($card->bond_level + $bondGain, self::MAX_BOND);
                $bondGains[] = [
                    'card_id' => $card->id,
                    'card_name' => $card->name,
                    'previous_bond' => $card->bond_level,
                    'bond_increase' => $bondGain,
                    'new_bond' => $newBond,
                    'friendship_unlocked' => $card->bond_level < self::FRIENDSHIP_THRESHOLD
                                            && $newBond >= self::FRIENDSHIP_THRESHOLD,
                ];
            }
        }

        return $bondGains;
    }

    /**
     * Get cards eligible for friendship training (bond >= 80).
     */
    public function getFriendshipEligibleCards(SupportDeck $deck, TrainingType $facility): array
    {
        $eligibleCards = [];

        foreach ($deck->cards as $card) {
            if ($this->cardPresentAtFacility($card, $facility)
                && $card->bond_level >= self::FRIENDSHIP_THRESHOLD) {
                $eligibleCards[] = [
                    'card_id' => $card->id,
                    'card_name' => $card->name,
                    'bond_level' => $card->bond_level,
                    'rarity' => $card->rarity,
                    'friendship_bonus' => $this->getFriendshipBonus($card),
                ];
            }
        }

        return $eligibleCards;
    }

    private function getFriendshipBonus(SupportCard $card): float
    {
        return match ($card->rarity) {
            'R' => 0.10,      // +10%
            'SR' => 0.15,     // +15%
            'SSR' => 0.20 + ($card->limit_break_level * 0.03), // +20-35%
            default => 0.10,
        };
    }
}
```text

### 5.5 Risk Assessment

```php
// app/Services/RiskCalculator.php
class RiskCalculator
{
    public function calculateFailureRisk(Career $career, TrainingType $facility): float
    {
        $baseRisk = 10.0; // 10% base failure rate

        // Energy penalty: +1% risk per 10 energy below 50
        if ($career->energy < 50) {
            $energyPenalty = (50 - $career->energy) / 10;
            $baseRisk += $energyPenalty;
        }

        // Mood modifier
        $moodModifier = match ($career->mood) {
            Mood::Great => -5.0,
            Mood::Good => -2.0,
            Mood::Normal => 0.0,
            Mood::Bad => +5.0,
            Mood::Awful => +10.0,
        };
        $baseRisk += $moodModifier;

        // Condition debuffs
        foreach ($career->conditions as $condition) {
            if ($condition->type === 'negative') {
                $baseRisk += $condition->risk_increase;
            }
        }

        // Cap at 90% max
        return min(90.0, max(0.0, $baseRisk));
    }
}
```

### 5.6 Training Execution Phase (Game-Accurate)

**Execution Service:**

```php
// app/Services/TrainingExecutionService.php
class TrainingExecutionService
{
    public function executeTraining(Career $career, TrainingType $facility): TrainingResult
    {
        $prediction = $this->predictionService->getPrediction($career, $facility);

        return DB::transaction(function () use ($career, $facility, $prediction) {
            // Roll for success/failure
            $wasSuccessful = $this->rollForSuccess($prediction['risk_percentage']);

            // Calculate actual gains using game formula
            $rawGains = $wasSuccessful
                ? $prediction['stat_gains']
                : array_map(fn($v) => (int) ($v * 0.3), $prediction['stat_gains']);

            // Apply soft cap (1200 threshold)
            $actualGains = $this->softCapCalculator->applySoftCap(
                new StatGains($rawGains),
                $career->getCurrentStats()
            );

            // Update career stats
            $career->update([
                'speed' => $career->speed + $actualGains->speed,
                'stamina' => $career->stamina + $actualGains->stamina,
                'power' => $career->power + $actualGains->power,
                'guts' => $career->guts + $actualGains->guts,
                'wit' => $career->wit + $actualGains->wit,
                'energy' => max(0, $career->energy - $prediction['energy_cost']),
                'mood' => $this->calculateMoodChange($career, $wasSuccessful),
                'current_turn' => $career->current_turn + 1,
            ]);

            // Update bond levels (+7 base, +9 with Charming)
            $bondUpdates = $this->updateBondLevels($career, $facility, $prediction['bond_gains']);

            // Record training session
            TrainingSession::create([
                'career_id' => $career->id,
                'turn_number' => $career->current_turn,
                'training_type' => $facility->value,
                'facility_level' => $prediction['facility_level'],
                'stat_gains' => $actualGains->toArray(),
                'raw_gains' => $rawGains,
                'soft_cap_applied' => $prediction['soft_cap_applied'],
                'was_successful' => $wasSuccessful,
                'energy_delta' => -$prediction['energy_cost'],
                'bond_updates' => $bondUpdates,
                'friendship_training_active' => $prediction['friendship_training']['is_active'],
                'cards_present' => $prediction['cards_present'],
                'skill_hints_gained' => $wasSuccessful ? $this->rollForHints($prediction['skill_hints']) : [],
            ]);

            // Record stat progression snapshot
            StatProgress::create([
                'career_id' => $career->id,
                'turn_number' => $career->current_turn,
                'speed' => $career->speed,
                'stamina' => $career->stamina,
                'power' => $career->power,
                'guts' => $career->guts,
                'wit' => $career->wit,
            ]);

            // Invalidate prediction cache
            $this->cache->forget("training_predictions:{$career->id}");

            // Dispatch events
            event(new TrainingCompleted($career, $prediction, $wasSuccessful));

            return new TrainingResult($career->fresh(), $actualGains, $wasSuccessful, $bondUpdates);
        });
    }

    private function updateBondLevels(Career $career, TrainingType $facility, array $bondGains): array
    {
        $updates = [];

        foreach ($bondGains as $bondGain) {
            $card = $career->supportDeck->cards->find($bondGain['card_id']);
            if ($card) {
                $card->update(['bond_level' => $bondGain['new_bond']]);
                $updates[] = [
                    'card_id' => $card->id,
                    'previous_bond' => $bondGain['previous_bond'],
                    'new_bond' => $bondGain['new_bond'],
                    'friendship_unlocked' => $bondGain['friendship_unlocked'],
                ];
            }
        }

        return $updates;
    }

    private function rollForSuccess(float $riskPercentage): bool
    {
        return (rand(1, 100) > $riskPercentage);
    }

    private function calculateMoodChange(Career $career, bool $wasSuccessful): Mood
    {
        if ($wasSuccessful && rand(1, 100) <= 30) {
            return $career->mood->improve();
        } elseif (!$wasSuccessful && rand(1, 100) <= 50) {
            return $career->mood->worsen();
        }
        return $career->mood;
    }
}
```text

---

## 6. Data Structures

### 6.1 Training Prediction Response (Game-Accurate)

```json
{
  "predictions": [
    {
      "rank": 1,
      "facility": "speed",
      "facility_level": 3,
      "facility_multiplier": 1.5,
      "stat_gains": {
        "speed": 48,
        "stamina": 5,
        "power": 12,
        "guts": 0,
        "wit": 0
      },
      "raw_gains": {
        "speed": 52,
        "stamina": 5,
        "power": 12,
        "guts": 0,
        "wit": 0
      },
      "soft_cap_applied": {
        "speed": false,
        "stamina": false,
        "power": false,
        "guts": false,
        "wit": false
      },
      "energy_cost": 22,
      "risk_percentage": 12.5,
      "skill_hints": [
        {
          "skill_id": 42,
          "skill_name": "Lane Guidance",
          "probability": 100,
          "source_card": "Mejiro Dober"
        }
      ],
      "bond_gains": [
        {
          "card_id": 5,
          "card_name": "Mejiro Dober",
          "previous_bond": 73,
          "bond_increase": 7,
          "new_bond": 80,
          "friendship_unlocked": true
        },
        {
          "card_id": 12,
          "card_name": "Tokai Teio",
          "previous_bond": 85,
          "bond_increase": 7,
          "new_bond": 92,
          "friendship_unlocked": false
        }
      ],
      "friendship_training": {
        "eligible_cards": [
          {
            "card_id": 12,
            "card_name": "Tokai Teio",
            "bond_level": 85,
            "rarity": "SSR",
            "friendship_bonus": 0.26
          }
        ],
        "is_active": true
      },
      "cards_present": 2,
      "card_presence_bonus": 10,
      "recommendation_score": 92.5,
      "efficiency_rating": "excellent"
    }
  ],
  "career_context": {
    "current_turn": 45,
    "energy": 78,
    "mood": "good",
    "has_charming": false,
    "is_summer_camp": false,
    "current_stats": {
      "speed": 520,
      "stamina": 480,
      "power": 440,
      "guts": 460,
      "wit": 450
    },
    "facility_levels": {
      "speed": 3,
      "stamina": 2,
      "power": 3,
      "guts": 2,
      "wit": 2
    }
  }
}
```

### 6.2 Training Execution Request

```json
{
  "career_id": 157,
  "facility": "speed"
}
```text

### 6.3 Training Execution Response (Game-Accurate)

```json
{
  "success": true,
  "result": {
    "was_successful": true,
    "facility_level": 3,
    "stat_gains": {
      "speed": 48,
      "stamina": 5,
      "power": 12,
      "guts": 0,
      "wit": 0
    },
    "raw_gains_before_cap": {
      "speed": 52,
      "stamina": 5,
      "power": 12,
      "guts": 0,
      "wit": 0
    },
    "soft_cap_applied": {
      "speed": false,
      "stamina": false,
      "power": false,
      "guts": false,
      "wit": false
    },
    "updated_stats": {
      "speed": 568,
      "stamina": 485,
      "power": 452,
      "guts": 460,
      "wit": 450
    },
    "energy_remaining": 56,
    "mood": "good",
    "turn_number": 46,
    "bond_updates": [
      {
        "card_id": 5,
        "card_name": "Mejiro Dober",
        "previous_bond": 73,
        "new_bond": 80,
        "friendship_unlocked": true
      },
      {
        "card_id": 12,
        "card_name": "Tokai Teio",
        "previous_bond": 85,
        "new_bond": 92,
        "friendship_unlocked": false
      }
    ],
    "friendship_training_active": true,
    "cards_present": 2,
    "skill_hints_received": [
      {
        "skill_id": 42,
        "skill_name": "Lane Guidance",
        "source_card": "Mejiro Dober"
      }
    ]
  }
}
```

---

## 7. Error Handling

### 7.1 Validation Errors

| Error Code | Condition | HTTP Status | User Message |
| --- | --- | --- | --- |
| `TRAIN_001` | Career not found | 404 | "Career run not found" |
| `TRAIN_002` | Invalid facility type | 422 | "Invalid training facility selected" |
| `TRAIN_003` | Insufficient energy | 422 | "Not enough energy to train (minimum 10 required)" |
| `TRAIN_004` | Career completed | 422 | "Cannot train on completed career" |
| `TRAIN_005` | Turn limit exceeded | 422 | "Career has reached maximum turn limit (78)" |
| `TRAIN_006` | Invalid facility level | 422 | "Facility level must be between 1 and 5" |

### 7.2 Error Recovery Flow

```mermaid
sequenceDiagram
    participant User
    participant UI as Livewire Component
    participant Controller
    participant Service as TrainingExecutionService
    participant DB as Database

    User->>UI: Execute training
    UI->>Controller: POST /training/execute
    Controller->>Service: executeTraining(career, facility)

    alt Validation Error
        Service-->>Controller: ValidationException
        Controller-->>UI: 422 Validation Error
        UI->>UI: Display error message
        UI-->>User: Show error + retry option
    else Database Error
        Service->>DB: BEGIN TRANSACTION
        DB-->>Service: Deadlock detected
        Service->>DB: ROLLBACK
        Service-->>Controller: 500 Server Error
        Controller-->>UI: 500 Server Error
        UI-->>User: "An error occurred. Please try again."
    else Soft Cap Calculation Error
        Service->>Service: Validate soft cap logic
        Service-->>Controller: Log warning, proceed with capped value
    else Success
        Service->>DB: COMMIT
        Service-->>Controller: TrainingResult
        Controller-->>UI: 200 OK
        UI-->>User: Display success + animation
    end
```text

### 7.3 Transaction Rollback Scenarios

| Scenario | Trigger | Recovery |
| --- | --- | --- |
| Constraint violation | Stat exceeds max (prevented by soft cap) | Rollback, log error |
| Deadlock | Concurrent training execution | Rollback, retry with delay |
| Cache failure | Redis unavailable | Proceed without cache, log warning |
| Event dispatch failure | Status refresh unavailable | Complete transaction, queue event for retry |
| Bond update failure | Invalid card reference | Rollback, log error |

---

## 8. Performance Considerations

### 8.1 Performance Metrics

| Operation | Target | Current | Status |
| --- | --- | --- | --- |
| Prediction generation (cache miss) | <1.2s | ~1.1s | ✅ Met |
| Prediction generation (cache hit) | <200ms | ~150ms | ✅ Met |
| Training formula calculation | <100ms | ~50ms | ✅ Met |
| Soft cap calculation | <10ms | ~5ms | ✅ Met |
| Bond calculation | <20ms | ~10ms | ✅ Met |
| Training execution | <300ms | ~280ms | ✅ Met |
| Status refresh propagation | request-dependent | request-dependent | ✅ Implemented |
| Total user flow (selection + execution) | <2s | ~1.8s | ✅ Met |

### 8.2 Optimization Strategies

**Implemented:**

- Redis caching for predictions (5-minute TTL)
- Parallel calculation of base gains for all facilities
- Eager loading of support deck relationships
- Database indexing on `career_id` and `turn_number`
- Batch insert for stat progress history
- Pre-computed facility multiplier lookup table

**Code Example:**

```php
// Optimized prediction loading with eager loading
$career = Career::with([
    'character.growthRates',
    'supportDeck.cards.bonuses',
    'conditions',
    'facilityLevels',
])->findOrFail($careerId);
```

### 8.3 Database Query Analysis

**Query Count for Full Prediction:**

- Prediction (cache miss): 4 queries (career, deck, conditions, facility levels)
- Prediction (cache hit): 0 queries (pure cache)
- Execution: 5 queries (1 career load + 1 bond update + 3 inserts)

**Total Queries:** 4-9 queries per training turn

**Index Usage:**

```sql
-- Critical indexes for training resolution
CREATE INDEX idx_careers_user_status ON ucp_careers(user_id, status);
CREATE INDEX idx_training_sessions_career_turn ON ucp_training_sessions(career_id, turn_number);
CREATE INDEX idx_stat_progress_career_turn ON ucp_stat_progress(career_id, turn_number);
CREATE INDEX idx_skill_hints_career ON ucp_skill_hints(career_id, is_used);
CREATE INDEX idx_support_cards_bond ON ucp_support_cards(deck_id, bond_level);
```text

### 8.4 Cache Strategy

**Cache Keys:**

- Predictions: `training_predictions:{career_id}`
- TTL: 5 minutes
- Invalidation: After training execution, on career update, on bond level change

**Cache Hit Rate Target:** >80%

```php
// Cache invalidation on execution
$this->cache->forget("training_predictions:{$career->id}");

// Cache warming on career load
$this->cache->remember("training_predictions:{$career->id}", 300, fn() => $this->generatePredictions($career));
```

---

## 9. Related Documentation

### 9.1 System Documentation

| Document | Description |
| --- | --- |
| [PRD-002](../02-prds/PRD-002_Training_Optimization.md) | Product requirements for training system |
| [SPEC-002](../02-specs/SPEC-002_Training_Optimization_Technical.md) | Technical specification for training optimization |
| [FLOW-002](../01-flows/FLOW-002_Training_Optimization_System.md) | System flow for training operations |
| [TECH-FLOW-002](../01-tech-flow/TECH-FLOW-002_Training_Optimization_Flow.md) | Technical flow diagrams |

### 9.2 Related Sequences

| Sequence | Description |
| --- | --- |
| [SEQ-001](SEQ-001_Character_Creation_Sequence.md) | Character creation (sets initial stats) |
| [SEQ-003](SEQ-003_Skill_Acquisition_and_Upgrade.md) | Skill acquisition (uses hints from training) |
| [SEQ-005](SEQ-005_Support_Card_Upgrade.md) | Support card upgrades (affects bonuses) |

### 9.3 UI Documentation

| Document | Description |
| --- | --- |
| [WF-004](../01-wireframes/WF-004_Training_Selection_Interface.md) | Wireframe specification for training selection |
| [WF-005](../01-wireframes/WF-005_Training_Result_Screen.md) | Training result display wireframe |
| [UF-003](../01-user-flows/UF-003_Training_Day_Flow.md) | User flow for training day |

### 9.4 Database Documentation

| Document | Description |
| --- | --- |
| [DBD-009](../00-core-docs/009_DBD_Database_Documentation.md) | Complete database schema documentation |

### 9.5 Game Mechanics Reference

| Source | Description |
| --- | --- |
| Global English Server | Verified mechanics (January 2026) |
| [Game Mechanics Research Report](../research/game-mechanics-research-report.md) | Detailed formula analysis |

---

## Document Control

### Version History

| Version | Date | Author | Changes |
| --- | --- | --- | --- |
| 2.3.0 | 2026-03-10 | Development Team | Corrected mood modifier values to game-accurate Great: +4%, Good: +2%, Normal: 0%, Bad: −2%, Awful: −4%; simplified formula by removing MoodEffect multiplicative nesting; renamed MoodMultiplier → MoodModifier and TrainingEffect → FacilityLevelBonus; added approximation note |
| 2.2.0 | 2026-01-28 | Development Team | Updated with verified game mechanics from Global English Server - added complete training formula, corrected facility multipliers (1.0×-2.0×), updated bond mechanics (+7 base, +9 Charming), soft cap at 1200 with 50% reduction, friendship training threshold at 80% bond, support card presence bonus (+5% per card), Summer Training Camp mechanics |
| 2.0.0 | 2026-01-24 | Development Team | Complete rewrite aligned with v2.0.0 implementation; added detailed sequence flows, caching strategy, WebSocket integration, performance metrics, and aligned with current Laravel 12 architecture |
| 1.0.0 | 2026-01-14 | Development Team | Initial draft |

### Approval

| Role | Name | Signature | Date |
| --- | --- | --- | --- |
| Technical Lead | | | |
| QA Lead | | | |

### Review Schedule

- Next Review: 2026-04-28
- Review Frequency: Quarterly or on major feature changes

---

**Related Standards:**

- Laravel 12 Best Practices
- PSR-12 Coding Standards
- Mermaid Diagram Standards
- IEEE 830 SRS Format
- Umamusume Pretty Derby Global English Server (January 2026)

---

*This sequence diagram reflects the game-accurate training mechanics verified from the Global
English Server as of January 2026. The complete training formula, soft cap system, bond mechanics,
and friendship training thresholds have been validated against in-game behavior. For the most up-to-
date information, refer to the source code in `app/Services/TrainingPredictionService.php`,
`app/Services/StatCalculator.php`, `app/Services/SoftCapCalculator.php`,
`app/Services/BondCalculator.php`, and related files.*
