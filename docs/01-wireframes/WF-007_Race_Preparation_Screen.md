# WF-007: Race Preparation Screen

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.3.0  
**Date**: February 22, 2026  
**Related Documents**: [PRD-003], [SPEC-003], [FLOW-003], [SEQ-004]

**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirement 3: Race Preparation and Strategy)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Race Preparation UI)

**Related Artifacts**:

- PRD: [PRD-003](../prds/PRD-003_Race_Strategy.md)
- SPEC: [SPEC-003](../specs/SPEC-003_Race_Strategy_Technical.md)
- Flow: [FLOW-003](../flows/FLOW-003_Race_Strategy_System.md)
- Tech Flow: [TECH-FLOW-003](../tech-flow/TECH-FLOW-003_Race_Strategy_Flow.md)
- Sequences: [SEQ-004](../sequences/SEQ-004_Race_Registration_and_Outcome.md)
- User Flows: [UF-004](../user-flows/UF-004_Race_Day_Flow.md)
- Related WF: [WF-006](WF-006_Race_Calendar_View.md), [WF-001](WF-001_Dashboard_Overview.md)

---

## 1. Overview

### 1.1 Purpose

The Race Preparation Screen provides comprehensive analysis and recommendations for upcoming races, enabling players to assess readiness, optimize running style strategy, and receive AI-powered tactical advice for competitive performance.

### 1.2 Key Objectives

| Objective | Description |
| --- | --- |
| **Readiness Assessment** | Calculate and display multi-factor readiness score |
| **Win Probability** | Predict placement probability based on stats and competition |
| **Strategy Optimization** | Recommend optimal running style and tactical approach |
| **AI Integration** | Provide intelligent race strategy recommendations |
| **Preparation Tracking** | Track recommended actions before race day |

### 1.3 User Stories

| ID | User Story | Priority |
| --- | --- | --- |
| US-001 | As a player, I want to see my race readiness score with factor breakdown | P0 |
| US-002 | As a player, I want to know my win probability and placement forecast | P0 |
| US-003 | As a player, I want running style recommendations with reasoning | P0 |
| US-004 | As a player, I want AI-powered race strategy advice | P1 |
| US-005 | As a player, I want a preparation checklist before race day | P1 |

---

## 2. Layout Specifications

### 2.1 Desktop Layout (≥1024px)

```text

┌──────────────────────────────────────────────────────────────────────┐
│ Race Preparation: Kanto Okami Cup                            [≡]    │
├──────────────────────────────────────────────────────────────────────┤
│                                                                      │
│ ┌────────────┬───────────────────────────────────────────────────┐  │
│ │ Sidebar    │ Main Content Area                                 │  │
│ │            │                                                   │  │
│ │ Dashboard  │ ┌──────────────────────────────────────────────┐ │  │
│ │ Character  │ │ Race Information Header                       │ │  │
│ │ Training   │ │ ┌────────────────────────────────────────────┐│ │  │
│ │ Races    ●│ │ │ Kanto Okami Cup (G1)                       ││ │  │
│ │ Skills     │ │ │ Track: Tokyo Racecourse                    ││ │  │
│ │ Support    │ │ │ Distance: Medium (2400m) | Surface: Turf   ││ │  │
│ │ AI Advisor │ │ │ Weather: Sunny | Track: Good               ││ │  │
│ │ Settings   │ │ │ Turn: 52 | Days Until Race: 8              ││ │  │
│ │            │ │ │ Prize: ¥15,000,000 (1st)                   ││ │  │
│ │            │ │ └────────────────────────────────────────────┘│ │  │
│ │            │ └──────────────────────────────────────────────┘ │  │
│ │            │                                                   │  │
│ │            │ ┌──────────────────────┬────────────────────────┐│  │
│ │            │ │ Readiness Assessment │ Win Probability        ││  │
│ │            │ ├──────────────────────┼────────────────────────┤│  │
│ │            │ │ Overall Score: 85%   │ 1st Place: 35%         ││  │
│ │            │ │ Status: 🟢 Excellent │ 2nd Place: 40%         ││  │
│ │            │ │                      │ 3rd Place: 20%         ││  │
│ │            │ │ Factor Breakdown:    │ 4th+ Place: 5%         ││  │
│ │            │ │ • Stats Match: 90%   │                        ││  │
│ │            │ │ • Aptitude Fit: 95%  │ Placement Forecast:    ││  │
│ │            │ │ • Skills: 80%        │ ┌──────────────────┐  ││  │
│ │            │ │ • Mood/Energy: 75%   │ │ ████████░░░░░░░░ │  ││  │
│ │            │ │ • Condition: 85%     │ │ 1st 2nd 3rd 4th+ │  ││  │
│ │            │ │                      │ └──────────────────┘  ││  │
│ │            │ └──────────────────────┴────────────────────────┘│  │
│ │            │                                                   │  │
│ │            │ ┌──────────────────────┬────────────────────────┐│  │
│ │            │ │ Stat Requirements    │ Running Style          ││  │
│ │            │ ├──────────────────────┼────────────────────────┤│  │
│ │            │ │ Speed:   520 req.    │ RECOMMENDED:           ││  │
│ │            │ │ Current: 568 ✅      │ Late Surger (差し)     ││  │
│ │            │ │ Status:  Excellent   │                        ││  │
│ │            │ │                      │ Match Score: 92/100    ││  │
│ │            │ │ Stamina: 480 req.    │                        ││  │
│ │            │ │ Current: 442 ⚠️     │ Reasoning:             ││  │
│ │            │ │ Status:  Borderline  │ • High Power aptitude  ││  │
│ │            │ │                      │ • Strong Guts stat     ││  │
│ │            │ │ Power:   440 req.    │ • Medium distance fit  ││  │
│ │            │ │ Current: 443 ✅      │ • Turf surface bonus   ││  │
│ │            │ │ Status:  Adequate    │                        ││  │
│ │            │ │                      │ Alternative Styles:    ││  │
│ │            │ │ Guts:    400 req.    │ • Pace Chaser (85/100) ││  │
│ │            │ │ Current: 460 ✅      │ • Front Runner (72/100)││  │
│ │            │ │ Status:  Good        │                        ││  │
│ │            │ │                      │ [VIEW SIMULATION]      ││  │
│ │            │ │ Wit:     380 req.    │                        ││  │
│ │            │ │ Current: 450 ✅      │                        ││  │
│ │            │ │ Status:  Good        │                        ││  │
│ │            │ └──────────────────────┴────────────────────────┘│  │
│ │            │                                                   │  │
│ │            │ ┌───────────────────────────────────────────────┐│  │
│ │            │ │ Preparation Checklist                         ││  │
│ │            │ ├───────────────────────────────────────────────┤│  │
│ │            │ │ ☑ Stats meet minimum requirements             ││  │
│ │            │ │ ⚠️ Stamina 38 pts below recommended (+80 rec.)││  │
│ │            │ │ ☑ Running style aptitude S grade              ││  │
│ │            │ │ ☑ Distance aptitude A+ grade                  ││  │
│ │            │ │ ☑ Surface aptitude A grade                    ││  │
│ │            │ │ ☐ Acquire recommended skills (2 pending)      ││  │
│ │            │ │ ☑ Energy above 60% (current: 78%)             ││  │
│ │            │ │ ☑ Mood Good or better (current: Good)         ││  │
│ │            │ │                                               ││  │
│ │            │ │ Recommended Actions:                          ││  │
│ │            │ │ 1. Train Stamina 2x (estimated +84 gain)      ││  │
│ │            │ │    [GO TO TRAINING]                           ││  │
│ │            │ │ 2. Acquire "Endurance Master" skill (180 SP)  ││  │
│ │            │ │    [GO TO SKILL SHOP]                         ││  │
│ │            │ │ 3. Rest before race day (energy recovery)     ││  │
│ │            │ │                                               ││  │
│ │            │ └───────────────────────────────────────────────┘│  │
│ │            │                                                   │  │
│ │            │ ┌───────────────────────────────────────────────┐│  │
│ │            │ │ AI Race Strategy Analysis                     ││  │
│ │            │ ├───────────────────────────────────────────────┤│  │
│ │            │ │ 🤖 AI Recommendation                          ││  │
│ │            │ │                                               ││  │
│ │            │ │ Your current build is well-suited for this    ││  │
│ │            │ │ race, but I recommend the following strategy: ││  │
│ │            │ │                                               ││  │
│ │            │ │ 1. Use Late Surger style to conserve stamina  ││  │
│ │            │ │    early and burst in final 400m              ││  │
│ │            │ │                                               ││  │
│ │            │ │ 2. Your stamina is borderline - avoid early   ││  │
│ │            │ │    position contests to preserve energy       ││  │
│ │            │ │                                               ││  │
│ │            │ │ 3. Activate acceleration skills at 400m mark  ││  │
│ │            │ │    when competitors begin to tire             ││  │
│ │            │ │                                               ││  │
│ │            │ │ Win probability with this strategy: 38%       ││  │
│ │            │ │ Confidence: 87%                               ││  │
│ │            │ │ Provider: Ollama (Local)                      ││  │
│ │            │ │                                               ││  │
│ │            │ │ [ASK FOLLOW-UP] [VIEW DETAILED ANALYSIS]      ││  │
│ │            │ └───────────────────────────────────────────────┘│  │
│ │            │                                                   │  │
│ │            │ ┌───────────────────────────────────────────────┐│  │
│ │            │ │ Quick Actions                                 ││  │
│ │            │ │ [ENTER RACE] [RUN SIMULATION] [ASK AI]        ││  │
│ │            │ │ [SAVE STRATEGY] [BACK TO CALENDAR]            ││  │
│ │            │ └───────────────────────────────────────────────┘│  │
│ └────────────┴───────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────────────┘

```

### 2.2 Tablet Layout (640px-1024px)

```text

┌────────────────────────────────────────────────────┐
│ Race Preparation                               [≡]  │
├────────────────────────────────────────────────────┤
│ ☰ Menu Toggle                                      │
├────────────────────────────────────────────────────┤
│                                                    │
│ ┌──────────────────────────────────────────────┐  │
│ │ Kanto Okami Cup (G1)                         │  │
│ │ Tokyo | 2400m Turf | Turn 52 | 8 days        │  │
│ └──────────────────────────────────────────────┘  │
│                                                    │
│ ┌──────────────────────────────────────────────┐  │
│ │ Readiness: 85% 🟢                            │  │
│ │ Win Prob: 35% (1st) | 40% (2nd)              │  │
│ │ [Expand Details ▼]                           │  │
│ └──────────────────────────────────────────────┘  │
│                                                    │
│ ┌──────────────────────────────────────────────┐  │
│ │ Stat Requirements                             │  │
│ │ Speed:   568/520 ✅                          │  │
│ │ Stamina: 442/480 ⚠️                          │  │
│ │ Power:   443/440 ✅                          │  │
│ │ [View All Stats ▼]                           │  │
│ └──────────────────────────────────────────────┘  │
│                                                    │
│ ┌──────────────────────────────────────────────┐  │
│ │ Recommended Style                             │  │
│ │ Late Surger (差し) - 92/100                   │  │
│ │ [View Reasoning ▼]                           │  │
│ └──────────────────────────────────────────────┘  │
│                                                    │
│ ┌──────────────────────────────────────────────┐  │
│ │ Preparation Checklist (6/8)                   │  │
│ │ [Expand ▼]                                    │  │
│ └──────────────────────────────────────────────┘  │
│                                                    │
│ ┌──────────────────────────────────────────────┐  │
│ │ AI Strategy                                   │  │
│ │ [View Recommendation ▼]                       │  │
│ └──────────────────────────────────────────────┘  │
│                                                    │
│ [ENTER RACE] [SIMULATE] [ASK AI]                  │
└────────────────────────────────────────────────────┘

```

### 2.3 Mobile Layout (<640px)

```text

┌──────────────────────────────┐
│ Race Prep              [≡]  │
├──────────────────────────────┤
│                              │
│ Kanto Okami Cup (G1)         │
│ Tokyo | 2400m | Turn 52      │
│                              │
│ ─────────────────────────────│
│                              │
│ Readiness                    │
│ ┌──────────────────────────┐ │
│ │ 85% 🟢 Excellent         │ │
│ │ [Details ▼]              │ │
│ └──────────────────────────┘ │
│                              │
│ ─────────────────────────────│
│                              │
│ Win Probability              │
│ ┌──────────────────────────┐ │
│ │ 1st: 35% | 2nd: 40%      │ │
│ │ [Forecast ▼]             │ │
│ └──────────────────────────┘ │
│                              │
│ ─────────────────────────────│
│                              │
│ Stats vs Requirements        │
│ ┌──────────────────────────┐ │
│ │ Speed:   568/520 ✅      │ │
│ │ Stamina: 442/480 ⚠️     │ │
│ │ [View All ▼]             │ │
│ └──────────────────────────┘ │
│                              │
│ ─────────────────────────────│
│                              │
│ Running Style                │
│ ┌──────────────────────────┐ │
│ │ Late Surger (92/100)     │ │
│ │ [Reasoning ▼]            │ │
│ └──────────────────────────┘ │
│                              │
│ ─────────────────────────────│
│                              │
│ Checklist (6/8)              │
│ [Expand ▼]                   │
│                              │
│ ─────────────────────────────│
│                              │
│ AI Strategy                  │
│ [View ▼]                     │
│                              │
│ ─────────────────────────────│
│                              │
│ [ENTER RACE]                 │
│ [SIMULATE]                   │
│ [ASK AI]                     │
└──────────────────────────────┘
│  Bottom Navigation Bar       │
│ [🏠][👤][⚡][🏆][🤖]\[⚙️]   │
└──────────────────────────────┘

```

---

## 3. Component Specifications

### 3.1 Race Information Header

**Component**: `resources/views/components/race/race-header.blade.php`

```blade
<div class="race-header" data-testid="race-header">
    <div class="race-header__title">
        <h1 class="text-2xl font-bold">{{ $race->name }}</h1>
        <span class="badge badge-{{ strtolower($race->grade) }}">{{ $race->grade }}</span>
    </div>
    
    <div class="race-header__details">
        <div class="detail-item">
            <span class="detail-label">Track:</span>
            <span class="detail-value">{{ $race->track_name }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Distance:</span>
            <span class="detail-value">{{ $race->distance_category }} ({{ $race->distance }}m)</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Surface:</span>
            <span class="detail-value">{{ $race->surface }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Weather:</span>
            <span class="detail-value">{{ $race->weather }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Track Condition:</span>
            <span class="detail-value">{{ $race->track_condition }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Turn:</span>
            <span class="detail-value">{{ $careerRun->current_turn }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Days Until Race:</span>
            <span class="detail-value">{{ $daysUntilRace }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Prize Money:</span>
            <span class="detail-value">¥{{ number_format($race->prize_money) }} (1st)</span>
        </div>
    </div>
</div>
```text

### 3.2 Readiness Assessment Component

**Component**: `app/Livewire/Race/ReadinessAssessment.php`

```php
class ReadinessAssessment extends Component
{
    public Race $race;
    public Character $character;
    
    public function mount(Race $race, Character $character)
    {
        $this->race = $race;
        $this->character = $character;
    }
    
    public function getReadinessProperty()
    {
        return app(RaceReadinessService::class)
            ->calculateReadiness($this->character, $this->race);
    }
    
    public function render()
    {
        return view('livewire.race.readiness-assessment', [
            'readiness' => $this->readiness,
        ]);
    }
}
```

**Visual Format**:

```text
┌────────────────────────────────────────┐
│ Readiness Assessment                   │
├────────────────────────────────────────┤
│ Overall Score: 85%                     │
│ Status: 🟢 Excellent                   │
│                                        │
│ Factor Breakdown:                      │
│ • Stats Match:    90% ████████████░    │
│ • Aptitude Fit:   95% █████████████    │
│ • Skills:         80% ████████░░░░     │
│ • Mood/Energy:    75% ███████░░░░░     │
│ • Condition:      85% ████████░░░      │
└────────────────────────────────────────┘
```

**Readiness Tier Classification**:

| Score | Tier | Color | Icon |
| --- | --- | --- | --- |
| ≥85% | Excellent | Green | 🟢 |
| 70-84% | Good | Yellow | 🟡 |
| 55-69% | Fair | Orange | 🟠 |
| <55% | Poor | Red | 🔴 |

### 3.3 Win Probability Component

**Component**: `app/Livewire/Race/WinProbability.php`

```php
class WinProbability extends Component
{
    public Race $race;
    public Character $character;
    
    public function mount(Race $race, Character $character)
    {
        $this->race = $race;
        $this->character = $character;
    }
    
    public function getProbabilityProperty()
    {
        return app(RaceStrategyService::class)
            ->calculateWinProbability($this->character, $this->race);
    }
    
    public function render()
    {
        return view('livewire.race.win-probability', [
            'probability' => $this->probability,
        ]);
    }
}
```text

**Visual Format**:

```
┌────────────────────────────────────────┐
│ Win Probability                        │
├────────────────────────────────────────┤
│ 1st Place: 35%                         │
│ 2nd Place: 40%                         │
│ 3rd Place: 20%                         │
│ 4th+ Place: 5%                         │
│                                        │
│ Placement Forecast:                    │
│ ┌──────────────────────────────────┐  │
│ │ ████████░░░░░░░░░░░░░░░░░░░░░░░░ │  │
│ │ 35%  40%  20%  5%                │  │
│ │ 1st  2nd  3rd  4th+              │  │
│ └──────────────────────────────────┘  │
└────────────────────────────────────────┘
```text

### 3.4 Stat Requirements Component

**Component**: `resources/views/components/race/stat-requirements.blade.php`

```blade
<div class="stat-requirements" data-testid="stat-requirements">
    <h3 class="text-lg font-semibold mb-4">Stat Requirements</h3>
    
    @foreach(['speed', 'stamina', 'power', 'guts', 'wit'] as $stat)
        <div class="stat-requirement-row" data-testid="stat-req-{{ $stat }}">
            <div class="stat-info">
                <span class="stat-label">{{ ucfirst($stat) }}:</span>
                <span class="stat-required">{{ $race->{"required_{$stat}"} }} req.</span>
            </div>
            <div class="stat-current">
                <span class="stat-value">Current: {{ $character->{$stat} }}</span>
                @if($character->{$stat} >= $race->{"required_{$stat}"} * 1.1)
                    <span class="status-icon excellent">✅</span>
                    <span class="status-text">Excellent</span>
                @elseif($character->{$stat} >= $race->{"required_{$stat}"})
                    <span class="status-icon adequate">✅</span>
                    <span class="status-text">Adequate</span>
                @elseif($character->{$stat} >= $race->{"required_{$stat}"} * 0.9)
                    <span class="status-icon borderline">⚠️</span>
                    <span class="status-text">Borderline</span>
                @else
                    <span class="status-icon inadequate">❌</span>
                    <span class="status-text">Inadequate</span>
                @endif
            </div>
        </div>
    @endforeach
</div>
```

**Status Classification**:

| Status | Threshold | Icon | Action |
| --- | --- | --- | --- |
| Excellent | ≥110% of required | ✅ | None needed |
| Adequate | 100-109% of required | ✅ | Optional improvement |
| Borderline | 90-99% of required | ⚠️ | Training recommended |
| Inadequate | <90% of required | ❌ | Training required |

### 3.5 Running Style Recommendation

**Component**: `app/Livewire/Race/RunningStyleRecommendation.php`

```php
class RunningStyleRecommendation extends Component
{
    public Race $race;
    public Character $character;
    
    public function mount(Race $race, Character $character)
    {
        $this->race = $race;
        $this->character = $character;
    }
    
    public function getRecommendationProperty()
    {
        return app(RunningStyleOptimizer::class)
            ->recommend($this->character, $this->race);
    }
    
    public function render()
    {
        return view('livewire.race.running-style-recommendation', [
            'recommendation' => $this->recommendation,
        ]);
    }
}
```text

**Visual Format**:

```
┌────────────────────────────────────────┐
│ Running Style Recommendation           │
├────────────────────────────────────────┤
│ RECOMMENDED:                           │
│ Late Surger (差し)                     │
│                                        │
│ Match Score: 92/100                    │
│                                        │
│ Reasoning:                             │
│ • High Power aptitude (A)              │
│ • Strong Guts stat (460)               │
│ • Medium distance fit (A+)             │
│ • Turf surface bonus (+10%)            │
│                                        │
│ Alternative Styles:                    │
│ • Pace Chaser (先行): 85/100           │
│ • Front Runner (逃げ): 72/100          │
│ • End Closer (追込): 68/100            │
│                                        │
│ [VIEW SIMULATION]                      │
└────────────────────────────────────────┘
```text

**Running Style Match Scoring**:

| Factor | Weight | Calculation |
| --- | --- | --- |
| Style Aptitude | 40% | Aptitude grade × effectiveness |
| Stat Distribution | 30% | Power/Guts ratio for style |
| Distance Match | 20% | Distance aptitude × style fit |
| Surface Match | 10% | Surface aptitude × style bonus |

**Aptitude Grade Scale (Global English Server - Feb 2026)**:

| Grade | Rank | Effectiveness |
| --- | --- | --- |
| S | Maximum | 100% |
| A | Excellent | 90% |
| B | Good | 80% |
| C | Average | 70% |
| D | Below Average | 60% |
| E | Poor | 50% |
| F | Very Poor | 40% |
| G | Minimum | 30% |

**Note**: S is the maximum grade. There is no SS grade in the game.

**Track Condition Effects on Race Performance**:

| Condition | Power Penalty | Speed Penalty | Stamina Drain | Strategy Impact |
| --- | --- | --- | --- | --- |
| Firm | None | None | None | All styles viable |
| Good | -50 Power | None | None | Slight disadvantage for power-dependent styles |
| Soft | -50 to -100 Power | None | +2%/sec | Stamina management critical |
| Heavy | -50 to -100 Power | -50 Speed | +2%/sec | Conservative strategies recommended |

### 3.6 Preparation Checklist

**Component**: `app/Livewire/Race/PreparationChecklist.php`

```php
class PreparationChecklist extends Component
{
    public Race $race;
    public Character $character;
    
    public function mount(Race $race, Character $character)
    {
        $this->race = $race;
        $this->character = $character;
    }
    
    public function getChecklistProperty()
    {
        return app(RacePreparationService::class)
            ->generateChecklist($this->character, $this->race);
    }
    
    public function render()
    {
        return view('livewire.race.preparation-checklist', [
            'checklist' => $this->checklist,
        ]);
    }
}
```

**Visual Format**:

```text
┌────────────────────────────────────────────────────┐
│ Preparation Checklist (6/8 Complete)               │
├────────────────────────────────────────────────────┤
│ ☑ Stats meet minimum requirements                 │
│ ⚠️ Stamina 38 pts below recommended (+80 rec.)    │
│ ☑ Running style aptitude S grade                  │
│ ☑ Distance aptitude A+ grade                      │
│ ☑ Surface aptitude A grade                        │
│ ☐ Acquire recommended skills (2 pending)          │
│ ☑ Energy above 60% (current: 78%)                 │
│ ☑ Mood Good or better (current: Good)             │
│                                                    │
│ Recommended Actions:                               │
│ 1. Train Stamina 2x (estimated +84 gain)          │
│    [GO TO TRAINING]                                │
│ 2. Acquire "Endurance Master" skill (180 SP)      │
│    [GO TO SKILL SHOP]                              │
│ 3. Rest before race day (energy recovery)         │
└────────────────────────────────────────────────────┘
```

**Checklist Item Types**:

| Type | Icon | Description |
| --- | --- | --- |
| Complete | ☑ | Requirement met |
| Warning | ⚠️ | Close to threshold, improvement recommended |
| Incomplete | ☐ | Requirement not met, action required |

### 3.7 AI Strategy Analysis

**Component**: `app/Livewire/Race/AIStrategyAnalysis.php`

```php
class AIStrategyAnalysis extends Component
{
    public Race $race;
    public Character $character;
    
    public function mount(Race $race, Character $character)
    {
        $this->race = $race;
        $this->character = $character;
    }
    
    public function getStrategyProperty()
    {
        return app(AIAdvisoryService::class)
            ->getRaceStrategy($this->character, $this->race);
    }
    
    public function askFollowUp()
    {
        return redirect()->route('ai-advisor', [
            'context' => 'race_strategy',
            'race_id' => $this->race->id,
        ]);
    }
    
    public function render()
    {
        return view('livewire.race.ai-strategy-analysis', [
            'strategy' => $this->strategy,
        ]);
    }
}
```text

**Visual Format**:

```
┌────────────────────────────────────────────────────┐
│ AI Race Strategy Analysis                          │
├────────────────────────────────────────────────────┤
│ 🤖 AI Recommendation                               │
│                                                    │
│ Your current build is well-suited for this race,  │
│ but I recommend the following strategy:            │
│                                                    │
│ 1. Use Late Surger style to conserve stamina      │
│    early and burst in final 400m                   │
│                                                    │
│ 2. Your stamina is borderline - avoid early       │
│    position contests to preserve energy            │
│                                                    │
│ 3. Activate acceleration skills at 400m mark      │
│    when competitors begin to tire                  │
│                                                    │
│ Win probability with this strategy: 38%            │
│ Confidence: 87%                                    │
│ Provider: Ollama (Local)                           │
│                                                    │
│ [ASK FOLLOW-UP] [VIEW DETAILED ANALYSIS]           │
└────────────────────────────────────────────────────┘
```text

---

## 4. State Management

### 4.1 Livewire Component State

**Main Component**: `app/Livewire/Race/RacePreparation.php`

```php
class RacePreparation extends Component
{
    public Race $race;
    public Character $character;
    public CareerRun $careerRun;
    
    public $expandedSections = [
        'readiness' => true,
        'stats' => true,
        'style' => true,
        'checklist' => false,
        'ai_strategy' => false,
    ];
    
    protected $listeners = [
        'stats-updated' => '$refresh',
        'energy-changed' => '$refresh',
    ];
    
    public function toggleSection(string $section)
    {
        $this->expandedSections[$section] = !$this->expandedSections[$section];
    }
    
    public function enterRace()
    {
        // Confirmation modal, then register
        $this->dispatch('open-modal', 'confirm-race-entry');
    }
    
    public function runSimulation()
    {
        return redirect()->route('race.simulation', ['race' => $this->race]);
    }
    
    public function askAI()
    {
        return redirect()->route('ai-advisor', [
            'context' => 'race_strategy',
            'race_id' => $this->race->id,
        ]);
    }
    
    public function render()
    {
        return view('livewire.race.race-preparation');
    }
}
```

### 4.2 Data Flow

```mermaid
sequenceDiagram
    participant User
    participant PrepScreen as Preparation Screen
    participant ReadinessService as Readiness Service
    participant StrategyService as Strategy Service
    participant AIService as AI Advisory Service
    participant Database
    
    User->>PrepScreen: View race preparation
    PrepScreen->>ReadinessService: Calculate readiness
    ReadinessService->>Database: Load character stats/aptitudes
    Database-->>ReadinessService: Character data
    ReadinessService->>ReadinessService: Calculate factors
    ReadinessService-->>PrepScreen: Readiness score
    
    PrepScreen->>StrategyService: Get running style rec
    StrategyService->>StrategyService: Analyze aptitudes
    StrategyService-->>PrepScreen: Style recommendation
    
    PrepScreen->>StrategyService: Calculate win probability
    StrategyService->>StrategyService: Compare to competitors
    StrategyService-->>PrepScreen: Probability forecast
    
    PrepScreen->>AIService: Get race strategy
    AIService->>AIService: Build context
    AIService->>AIService: Generate strategy
    AIService-->>PrepScreen: AI recommendation
    
    PrepScreen->>User: Display all analysis
```text

### 4.3 Cache Strategy

| Data Type | Cache Key | TTL | Invalidation |
| --- | --- | --- | --- |
| Readiness score | `readiness:{character_id}:{race_id}` | 5 minutes | On stat update |
| Win probability | `win_prob:{character_id}:{race_id}` | 5 minutes | On stat/skill update |
| Running style rec | `style_rec:{character_id}:{race_id}` | 10 minutes | On aptitude update |
| AI strategy | `ai_strategy:{character_id}:{race_id}` | 15 minutes | On manual refresh |

---

## 5. Interaction Patterns

### 5.1 Race Entry Flow

```mermaid
flowchart TD
    Start([User Clicks Enter Race]) --> CheckReq{Minimum Reqs Met?}
    CheckReq -->|No| ShowWarning[Show Warning Modal]
    ShowWarning --> UserDecision{Proceed Anyway?}
    UserDecision -->|No| Cancel([Cancel])
    UserDecision -->|Yes| Confirm[Confirmation Modal]
    CheckReq -->|Yes| Confirm
    Confirm --> UserConfirm{Confirm Entry?}
    UserConfirm -->|No| Cancel
    UserConfirm -->|Yes| Register[Register for Race]
    Register --> Update[Update Database]
    Update --> Notify[Send Notification]
    Notify --> Success([Entry Confirmed])
```

### 5.2 Simulation Flow

```mermaid
sequenceDiagram
    participant User
    participant PrepScreen as Preparation Screen
    participant SimService as Simulation Service
    participant ResultModal as Result Modal
    
    User->>PrepScreen: Click "Run Simulation"
    PrepScreen->>SimService: simulateRace(character, race)
    SimService->>SimService: Load competitors
    SimService->>SimService: Run race simulation
    SimService->>SimService: Calculate placement
    SimService-->>PrepScreen: Simulation result
    PrepScreen->>ResultModal: Display result
    ResultModal->>User: Show placement + stats
```text

### 5.3 AI Advisory Integration Flow

```mermaid
sequenceDiagram
    participant User
    participant PrepScreen as Preparation Screen
    participant AIService as AI Advisory Service
    participant Ollama as Ollama (Local)
    participant Bedrock as AWS Bedrock
    
    User->>PrepScreen: Click "Ask AI"
    PrepScreen->>AIService: getRaceStrategy(character, race)
    AIService->>AIService: Build context
    AIService->>Ollama: Try local model
    
    alt Ollama Available
        Ollama-->>AIService: Strategy response
    else Ollama Unavailable
        AIService->>Bedrock: Fallback to cloud
        Bedrock-->>AIService: Strategy response
    end
    
    AIService->>AIService: Score confidence
    AIService-->>PrepScreen: AI recommendation
    PrepScreen->>User: Display strategy
```

---

## 6. Accessibility Specifications

### 6.1 WCAG 2.2 AA Compliance

| Criterion | Implementation | Test Method |
| --- | --- | --- |
| **1.1.1 Non-text Content** | All icons have `aria-label` | Screen reader testing |
| **1.4.3 Contrast Ratio** | 4.5:1 minimum for text | Color contrast analyzer |
| **2.1.1 Keyboard** | All interactive elements keyboard accessible | Keyboard-only testing |
| **2.4.3 Focus Order** | Logical tab order through sections | Tab key traversal |
| **2.4.7 Focus Visible** | Clear focus indicators on controls | Visual inspection |
| **3.2.4 Consistent Identification** | Consistent status icons and badges | Manual review |
| **4.1.2 Name, Role, Value** | Proper ARIA attributes on controls | axe-core scan |

### 6.2 Keyboard Navigation

| Action | Shortcut | Context |
| --- | --- | --- |
| Enter Race | `Enter` or `E` | When preparation screen loaded |
| Run Simulation | `S` | Preparation screen |
| Ask AI | `A` | Preparation screen |
| Expand/Collapse Section | `Space` | When section focused |
| Navigate Sections | `Tab` / `Shift+Tab` | Preparation screen |
| Return to Calendar | `Esc` or `C` | Preparation screen |

### 6.3 Screen Reader Announcements

```html
<!-- Readiness announcement -->
<div aria-live="polite" aria-atomic="true" class="sr-only">
    Race readiness calculated. Overall score: 85 percent, Excellent status.
</div>

<!-- Win probability announcement -->
<div aria-live="polite" aria-atomic="true" class="sr-only">
    Win probability: 35 percent for first place, 40 percent for second place, 20 percent for third place.
</div>

<!-- Stat requirement warning -->
<div aria-live="assertive" aria-atomic="true" class="sr-only">
    Warning: Stamina stat is 38 points below recommended. Current: 442, Recommended: 480.
</div>

<!-- AI strategy announcement -->
<div aria-live="polite" aria-atomic="true" class="sr-only">
    AI race strategy generated. Recommended running style: Late Surger. Confidence: 87 percent.
</div>

<!-- Race entry confirmation -->
<div aria-live="assertive" aria-atomic="true" class="sr-only">
    Race entry confirmed. You are now registered for Kanto Okami Cup on turn 52.
</div>
```text

---

## 7. Performance Specifications

### 7.1 Performance Targets

| Metric | Target | Measurement |
| --- | --- | --- |
| **Page Load** | < 1.5 seconds | Time to first render |
| **Readiness Calculation** | < 300ms | Service execution time |
| **Win Probability** | < 400ms | Service execution time |
| **AI Strategy Generation** | < 3 seconds | With AI provider |
| **Section Expand/Collapse** | < 100ms | UI interaction |

### 7.2 Optimization Strategies

| Strategy | Implementation | Impact |
| --- | --- | --- |
| **Eager Loading** | Preload race + character data with relationships | -50% query count |
| **Calculation Caching** | Cache readiness/probability scores (5min TTL) | -70% repeated calculations |
| **Lazy Loading** | Defer AI strategy until section expanded | -30% initial load time |
| **Response Caching** | Cache AI responses (15min TTL) | -90% AI provider calls |

### 7.3 Bundle Size Budget

| Asset Type | Budget | Current | Status |
| --- | --- | --- | --- |
| JavaScript | 50 KB | 45 KB | ✅ Within budget |
| CSS | 22 KB | 20 KB | ✅ Within budget |
| Images | 40 KB | 35 KB | ✅ Within budget |
| Total | 112 KB | 100 KB | ✅ Within budget |

---

## 8. Testing Specifications

### 8.1 Unit Tests

**Test File**: `tests/Unit/Services/RaceReadinessServiceTest.php`

```php
test('calculates readiness score correctly', function () {
    $character = Character::factory()->create([
        'speed' => 568,
        'stamina' => 442,
        'power' => 443,
        'guts' => 460,
        'wit' => 450,
    ]);
    
    $race = Race::factory()->create([
        'required_speed' => 520,
        'required_stamina' => 480,
        'required_power' => 440,
        'required_guts' => 400,
        'required_wit' => 380,
    ]);
    
    $service = app(RaceReadinessService::class);
    $readiness = $service->calculateReadiness($character, $race);
    
    expect($readiness->overall_percentage)->toBeGreaterThanOrEqual(80)
        ->and($readiness->tier)->toBe('good')
        ->and($readiness->factors)->toHaveKeys([
            'stats_match',
            'aptitude_fit',
            'skills',
            'mood_energy',
            'condition',
        ]);
});

test('classifies readiness tiers correctly', function () {
    $service = app(RaceReadinessService::class);
    
    expect($service->getTier(90))->toBe('excellent')
        ->and($service->getTier(75))->toBe('good')
        ->and($service->getTier(60))->toBe('fair')
        ->and($service->getTier(45))->toBe('poor');
});
```

### 8.2 Feature Tests

**Test File**: `tests/Feature/Race/RacePreparationTest.php`

```php
test('user can view race preparation screen', function () {
    $user = User::factory()->create();
    $character = Character::factory()->for($user)->create();
    $race = Race::factory()->create();
    
    $this->actingAs($user)
        ->get(route('races.prepare', ['race' => $race, 'character' => $character]))
        ->assertOk()
        ->assertSee('Race Preparation')
        ->assertSee($race->name);
});

test('user can enter race from preparation screen', function () {
    $user = User::factory()->create();
    $character = Character::factory()->for($user)->create();
    $race = Race::factory()->create();
    
    Livewire::actingAs($user)
        ->test(RacePreparation::class, ['race' => $race, 'character' => $character])
        ->call('enterRace')
        ->assertDispatched('open-modal', 'confirm-race-entry');
});

test('user can run race simulation', function () {
    $user = User::factory()->create();
    $character = Character::factory()->for($user)->create();
    $race = Race::factory()->create();
    
    Livewire::actingAs($user)
        ->test(RacePreparation::class, ['race' => $race, 'character' => $character])
        ->call('runSimulation')
        ->assertRedirect(route('race.simulation', ['race' => $race]));
});
```text

### 8.3 E2E Tests (Playwright)

**Test File**: `tests/e2e/race-preparation.spec.js`

```javascript
test.describe('WF-007: Race Preparation Screen', () => {
    test('displays race preparation correctly', async ({ page }) => {
        await page.goto('/races/1/prepare');
        
        // Check race header
        await expect(page.getByTestId('race-header')).toBeVisible();
        await expect(page.getByRole('heading', { name: /Kanto Okami Cup/ })).toBeVisible();
        
        // Check readiness assessment
        const readiness = page.getByTestId('readiness-assessment');
        await expect(readiness).toBeVisible();
        await expect(readiness).toContainText(/Overall Score:/);
        
        // Check win probability
        const winProb = page.getByTestId('win-probability');
        await expect(winProb).toBeVisible();
        await expect(winProb).toContainText(/1st Place:/);
    });
    
    test('displays stat requirements with status', async ({ page }) => {
        await page.goto('/races/1/prepare');
        
        const statReqs = page.getByTestId('stat-requirements');
        await expect(statReqs).toBeVisible();
        
        // Check for status icons
        await expect(statReqs.getByTestId('stat-req-speed')).toContainText('✅');
        await expect(statReqs.getByTestId('stat-req-stamina')).toContainText('⚠️');
    });
    
    test('displays running style recommendation', async ({ page }) => {
        await page.goto('/races/1/prepare');
        
        const styleRec = page.getByTestId('running-style-recommendation');
        await expect(styleRec).toBeVisible();
        await expect(styleRec).toContainText(/Late Surger/);
        await expect(styleRec).toContainText(/Match Score:/);
    });
    
    test('displays preparation checklist', async ({ page }) => {
        await page.goto('/races/1/prepare');
        
        const checklist = page.getByTestId('preparation-checklist');
        await expect(checklist).toBeVisible();
        
        // Check for checklist items
        await expect(checklist).toContainText(/Stats meet minimum requirements/);
        await expect(checklist).toContainText(/Stamina.*below recommended/);
    });
    
    test('displays AI strategy analysis', async ({ page }) => {
        await page.goto('/races/1/prepare');
        
        const aiStrategy = page.getByTestId('ai-strategy-analysis');
        await expect(aiStrategy).toBeVisible();
        await expect(aiStrategy).toContainText(/AI Recommendation/);
        await expect(aiStrategy).toContainText(/Confidence:/);
    });
    
    test('allows entering race', async ({ page }) => {
        await page.goto('/races/1/prepare');
        
        await page.getByTestId('enter-race-button').click();
        
        // Verify confirmation modal
        await expect(page.getByRole('dialog')).toBeVisible();
        await expect(page.getByText(/Confirm race entry/)).toBeVisible();
    });
    
    test('supports keyboard navigation', async ({ page }) => {
        await page.goto('/races/1/prepare');
        
        // Tab through sections
        await page.keyboard.press('Tab');
        await page.keyboard.press('Tab');
        
        // Expand section with Space
        await page.keyboard.press('Space');
        
        // Navigate to enter race button
        for (let i = 0; i < 5; i++) {
            await page.keyboard.press('Tab');
        }
        
        // Enter race with Enter key
        await page.keyboard.press('Enter');
        
        await expect(page.getByRole('dialog')).toBeVisible();
    });
});
```

### 8.4 Accessibility Tests

**Test File**: `tests/e2e/accessibility/race-preparation.spec.js`

```javascript
import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

test.describe('WF-007: Accessibility', () => {
    test('has no automatically detectable accessibility issues', async ({ page }) => {
        await page.goto('/races/1/prepare');
        
        const accessibilityScanResults = await new AxeBuilder({ page })
            .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'])
            .analyze();
        
        expect(accessibilityScanResults.violations).toEqual([]);
    });
    
    test('announces readiness score to screen readers', async ({ page }) => {
        await page.goto('/races/1/prepare');
        
        const liveRegion = page.locator('[aria-live="polite"]');
        
        await expect(liveRegion).toContainText(/Race readiness calculated/);
    });
    
    test('announces stat warnings to screen readers', async ({ page }) => {
        await page.goto('/races/1/prepare');
        
        const liveRegion = page.locator('[aria-live="assertive"]');
        
        await expect(liveRegion).toContainText(/Warning.*Stamina/);
    });
    
    test('stat requirements have proper ARIA attributes', async ({ page }) => {
        await page.goto('/races/1/prepare');
        
        const statReqs = page.getByTestId('stat-requirements');
        
        const speedReq = statReqs.getByTestId('stat-req-speed');
        await expect(speedReq).toHaveAttribute('aria-label');
    });
    
    test('supports keyboard-only workflow', async ({ page }) => {
        await page.goto('/races/1/prepare');
        
        // Navigate using keyboard only
        await page.keyboard.press('Tab'); // Race header
        await page.keyboard.press('Tab'); // Readiness section
        await page.keyboard.press('Tab'); // Win probability
        await page.keyboard.press('Tab'); // Stat requirements
        await page.keyboard.press('Tab'); // Running style
        await page.keyboard.press('Tab'); // Checklist
        await page.keyboard.press('Tab'); // AI strategy
        await page.keyboard.press('Tab'); // Enter race button
        
        // Verify focus on enter race button
        await expect(page.getByTestId('enter-race-button')).toBeFocused();
        
        // Execute with Enter
        await page.keyboard.press('Enter');
        
        await expect(page.getByRole('dialog')).toBeVisible();
    });
});
```text

---

## 9. Related Documentation

### 9.1 Product Requirements

- [PRD-003: Race Strategy](../prds/PRD-003_Race_Strategy.md)

### 9.2 Technical Specifications

- [SPEC-003: Race Strategy Technical](../specs/SPEC-003_Race_Strategy_Technical.md)

### 9.3 Flow Documentation

- [FLOW-003: Race Strategy System](../flows/FLOW-003_Race_Strategy_System.md)
- [TECH-FLOW-003: Race Strategy Flow](../tech-flow/TECH-FLOW-003_Race_Strategy_Flow.md)

### 9.4 Sequence Diagrams

- [SEQ-004: Race Registration and Outcome](../sequences/SEQ-004_Race_Registration_and_Outcome.md)

### 9.5 User Flows

- [UF-004: Race Day Flow](../user-flows/UF-004_Race_Day_Flow.md)

### 9.6 Related Wireframes

- [WF-006: Race Calendar View](WF-006_Race_Calendar_View.md)
- [WF-001: Dashboard Overview](WF-001_Dashboard_Overview.md)

---

## 10. Version History

| Version | Date | Author | Changes |
| --- | --- | --- | --- |
| 2.3.0 | 2026-02-22 | Development Team | Updated version/dates, aligned technology references with current stack (Livewire 4, Neuron AI v2.11, GameTora/umapyoi.net) |
| 2.2.0 | 2026-01-28 | Development Team | Updated with verified game mechanics from Global English Server: track condition effects (Firm/Good/Soft/Heavy with Power/Speed/Stamina penalties), S-max aptitude grades (G→F→E→D→C→B→A→S), class pyramid |
| 2.0.0 | 2026-01-24 | Development Team | Comprehensive update aligned with v2.0.0 implementation; added readiness assessment, win probability, AI strategy integration, preparation checklist, accessibility specifications, and testing requirements |
| 1.0.0 | 2026-01-14 | Development Team | Initial wireframe specification |

---

## 11. Notes

**Implementation Status**: ✅ Complete

**Known Issues**: None

**Future Enhancements**:

- Race simulation replay with step-by-step breakdown
- Historical race performance comparison
- Multi-character race comparison
- Community race statistics integration
- Advanced AI strategy with multiple scenarios
- Weather/track condition impact analysis

---

*This wireframe specification reflects the current implementation of the Race Preparation Screen and serves as the authoritative reference for UI/UX development and testing.*

```
