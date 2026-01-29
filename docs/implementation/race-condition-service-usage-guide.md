# RaceConditionService Usage Guide

**Service**: `App\Services\RaceConditionService`  
**Phase**: 5 - Weather/Track Conditions  
**Status**: ✅ Complete and Tested

---

## Quick Start

```php
use App\Services\RaceConditionService;

$service = new RaceConditionService();
```

---

## Basic Usage

### Calculate Individual Penalties

```php
// Power penalty
$powerPenalty = $service->calculatePowerPenalty('heavy', 'dirt');
// Returns: -100

// Speed penalty
$speedPenalty = $service->calculateSpeedPenalty('heavy', 'dirt');
// Returns: -50

// Stamina drain
$staminaDrain = $service->calculateStaminaDrain('heavy', 'dirt');
// Returns: 2.0 (2% per second)
```

### Apply Penalties to Character Stats

```php
$characterStats = [
    'speed' => 1000,
    'stamina' => 800,
    'power' => 900,
    'guts' => 700,
    'wit' => 600,
];

$modifiedStats = $service->applyConditionPenalties(
    $characterStats,
    'heavy',  // track condition
    'dirt'    // surface type
);

// Result:
// [
//     'speed' => 950,   // -50 penalty
//     'stamina' => 800, // no change
//     'power' => 800,   // -100 penalty
//     'guts' => 700,    // no change
//     'wit' => 600,     // no change
// ]
```

---

## Condition Analysis

### Check if Condition is Wet

```php
$isWet = $service->isWetCondition('heavy');
// Returns: true (good, soft, heavy are wet)

$isWet = $service->isWetCondition('firm');
// Returns: false
```

### Get Condition Severity

```php
$severity = $service->getConditionSeverity('heavy');
// Returns: 3 (0=firm, 1=good, 2=soft, 3=heavy)
```

### Get Human-Readable Impact Description

```php
$description = $service->getConditionImpactDescription('heavy', 'dirt');
// Returns: "Power -100, Speed -50, Stamina drain +2%/sec"

$description = $service->getConditionImpactDescription('firm', 'turf');
// Returns: "Optimal conditions - no penalties"
```

### Calculate Performance Impact Score

```php
$score = $service->calculatePerformanceImpact('heavy', 'dirt');
// Returns: 92.0 (out of 100, lower = worse conditions)

$score = $service->calculatePerformanceImpact('firm', 'turf');
// Returns: 100.0 (optimal)
```

---

## Skill Recommendations

### Get Recommended Skills for Conditions

```php
$skills = $service->getRecommendedSkills('rainy', 'heavy');
// Returns: ['Rainy Days ◯', 'Wet Conditions ◯']

$skills = $service->getRecommendedSkills('sunny', 'firm');
// Returns: ['Sunny Days ◯', 'Firm Conditions ◯']

$skills = $service->getRecommendedSkills(null, 'soft');
// Returns: ['Wet Conditions ◯'] (no weather-specific skill)
```

---

## Validation

### Validate Input Values

```php
// Track condition
$isValid = $service->isValidTrackCondition('heavy');
// Returns: true

$isValid = $service->isValidTrackCondition('invalid');
// Returns: false

// Surface type
$isValid = $service->isValidSurface('turf');
// Returns: true

$isValid = $service->isValidSurface('grass');
// Returns: false

// Weather type
$isValid = $service->isValidWeather('rainy');
// Returns: true

$isValid = $service->isValidWeather('stormy');
// Returns: false
```

---

## Integration Examples

### Example 1: Race Prediction Service

```php
use App\Services\RaceConditionService;

class RacePredictionService
{
    public function __construct(
        private RaceConditionService $conditionService
    ) {}

    public function predictRaceOutcome(Character $character, Race $race): array
    {
        // Get character stats
        $stats = [
            'speed' => $character->speed,
            'stamina' => $character->stamina,
            'power' => $character->power,
            'guts' => $character->guts,
            'wit' => $character->wit,
        ];

        // Apply weather/track condition penalties
        $modifiedStats = $this->conditionService->applyConditionPenalties(
            $stats,
            $race->track_condition,
            $race->surface
        );

        // Get condition impact
        $impactScore = $this->conditionService->calculatePerformanceImpact(
            $race->track_condition,
            $race->surface
        );

        // Get recommended skills
        $recommendedSkills = $this->conditionService->getRecommendedSkills(
            $race->weather,
            $race->track_condition
        );

        // Calculate race outcome using modified stats
        $prediction = $this->calculateOutcome($modifiedStats, $race);

        return [
            'prediction' => $prediction,
            'condition_impact' => $impactScore,
            'recommended_skills' => $recommendedSkills,
            'modified_stats' => $modifiedStats,
        ];
    }
}
```

### Example 2: Race Strategy Service

```php
use App\Services\RaceConditionService;

class RaceStrategyService
{
    public function __construct(
        private RaceConditionService $conditionService
    ) {}

    public function getStrategy(Character $character, array $raceData): array
    {
        $trackCondition = $raceData['track_condition'] ?? 'firm';
        $surface = $raceData['surface'] ?? 'turf';
        $weather = $raceData['weather'] ?? null;

        // Get condition impact
        $impactDescription = $this->conditionService->getConditionImpactDescription(
            $trackCondition,
            $surface
        );

        $impactScore = $this->conditionService->calculatePerformanceImpact(
            $trackCondition,
            $surface
        );

        // Get recommended skills
        $recommendedSkills = $this->conditionService->getRecommendedSkills(
            $weather,
            $trackCondition
        );

        // Build strategy
        $strategy = [
            'condition_analysis' => [
                'description' => $impactDescription,
                'impact_score' => $impactScore,
                'severity' => $this->conditionService->getConditionSeverity($trackCondition),
                'is_wet' => $this->conditionService->isWetCondition($trackCondition),
            ],
            'recommended_skills' => $recommendedSkills,
            'preparation_advice' => $this->generateAdvice($impactScore, $trackCondition),
        ];

        return $strategy;
    }

    private function generateAdvice(float $impactScore, string $condition): array
    {
        $advice = [];

        if ($impactScore < 95) {
            $advice[] = "Track conditions are challenging. Consider equipping condition-specific skills.";
        }

        if ($this->conditionService->isWetCondition($condition)) {
            $advice[] = "Wet conditions increase stamina drain. Ensure adequate stamina for the distance.";
        }

        if ($this->conditionService->getConditionSeverity($condition) >= 3) {
            $advice[] = "Heavy conditions significantly impact power and speed. Focus on high base stats.";
        }

        return $advice;
    }
}
```

### Example 3: UI Display Component

```php
// In a Livewire component or controller

use App\Services\RaceConditionService;

class RacePreparationComponent
{
    public function getRaceConditionData(Race $race): array
    {
        $service = new RaceConditionService();

        return [
            'impact_description' => $service->getConditionImpactDescription(
                $race->track_condition,
                $race->surface
            ),
            'impact_score' => $service->calculatePerformanceImpact(
                $race->track_condition,
                $race->surface
            ),
            'recommended_skills' => $service->getRecommendedSkills(
                $race->weather,
                $race->track_condition
            ),
            'is_wet' => $service->isWetCondition($race->track_condition),
            'severity' => $service->getConditionSeverity($race->track_condition),
        ];
    }
}
```

---

## Constants Reference

### Weather Types

```php
RaceConditionService::WEATHER_SUNNY   // 'sunny'
RaceConditionService::WEATHER_CLOUDY  // 'cloudy'
RaceConditionService::WEATHER_RAINY   // 'rainy'
RaceConditionService::WEATHER_SNOWY   // 'snowy'
```

### Track Conditions

```php
RaceConditionService::CONDITION_FIRM   // 'firm'
RaceConditionService::CONDITION_GOOD   // 'good'
RaceConditionService::CONDITION_SOFT   // 'soft'
RaceConditionService::CONDITION_HEAVY  // 'heavy'
```

### Surface Types

```php
RaceConditionService::SURFACE_TURF  // 'turf'
RaceConditionService::SURFACE_DIRT  // 'dirt'
```

---

## Penalty Reference Table

| Condition | Surface | Power Penalty | Speed Penalty | Stamina Drain |
|-----------|---------|---------------|---------------|---------------|
| Firm      | Turf    | 0             | 0             | 0%/sec        |
| Firm      | Dirt    | 0             | 0             | 0%/sec        |
| Good      | Turf    | -50           | 0             | 0%/sec        |
| Good      | Dirt    | -50           | 0             | 0%/sec        |
| Soft      | Turf    | -50           | 0             | +2%/sec       |
| Soft      | Dirt    | -100          | 0             | +2%/sec       |
| Heavy     | Turf    | -50           | -50           | +2%/sec       |
| Heavy     | Dirt    | -100          | -50           | +2%/sec       |

**Source**: Research Report Section 6.1 (verified game mechanics)

---

## Performance Impact Scoring

The performance impact score is calculated as:

```
Base Score: 100 (optimal)
- Power penalty × 0.04 (max -4.0 for -100 penalty)
- Speed penalty × 0.04 (max -2.0 for -50 penalty)
- Stamina drain × 1.0 (max -2.0 for 2%/sec drain)

Result: 0-100 (higher = better conditions)
```

### Score Interpretation

- **100**: Optimal conditions (firm)
- **98-99**: Minor impact (good conditions)
- **94-97**: Moderate impact (soft conditions)
- **92-93**: Significant impact (heavy conditions)

---

## Best Practices

### 1. Always Validate Input

```php
if (!$service->isValidTrackCondition($condition)) {
    throw new InvalidArgumentException("Invalid track condition: {$condition}");
}

if (!$service->isValidSurface($surface)) {
    throw new InvalidArgumentException("Invalid surface: {$surface}");
}
```

### 2. Cache Condition Calculations

```php
$cacheKey = "race_condition:{$raceId}:{$trackCondition}:{$surface}";

$conditionData = Cache::remember($cacheKey, 3600, function () use ($service, $trackCondition, $surface) {
    return [
        'impact_score' => $service->calculatePerformanceImpact($trackCondition, $surface),
        'description' => $service->getConditionImpactDescription($trackCondition, $surface),
        'is_wet' => $service->isWetCondition($trackCondition),
    ];
});
```

### 3. Apply Penalties Before Race Calculations

```php
// CORRECT: Apply penalties first
$modifiedStats = $service->applyConditionPenalties($stats, $condition, $surface);
$raceOutcome = $this->calculateRaceOutcome($modifiedStats, $race);

// INCORRECT: Don't calculate with original stats
$raceOutcome = $this->calculateRaceOutcome($stats, $race); // Missing penalties!
```

### 4. Include Condition Info in Race Analysis

```php
$analysis = [
    'predicted_position' => $prediction,
    'win_probability' => $winProb,
    'condition_impact' => [
        'score' => $service->calculatePerformanceImpact($condition, $surface),
        'description' => $service->getConditionImpactDescription($condition, $surface),
        'recommended_skills' => $service->getRecommendedSkills($weather, $condition),
    ],
];
```

---

## Testing

The service has comprehensive test coverage:

```bash
php artisan test --filter=RaceConditionServiceTest

Tests:    51 passed (78 assertions)
Duration: 4.43s
```

Test coverage includes:

- All penalty calculations
- Stat modification logic
- Condition analysis methods
- Skill recommendations
- Validation methods
- Edge cases and boundary conditions

---

## Related Documentation

- **Research Report**: `docs/research/game-mechanics-research-report.md` (Section 6.1)
- **Phase 5 Summary**: `docs/implementation/phase-5-weather-track-conditions-summary.md`
- **Main Summary**: `docs/implementation/game-mechanics-corrections-summary.md`
- **Complete Guide**: `docs/implementation/PHASES_1-5_COMPLETE.md`

---

**Last Updated**: January 28, 2026  
**Service Version**: 1.0  
**Status**: Production-ready ✅
