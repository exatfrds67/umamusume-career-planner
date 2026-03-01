<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AdvisoryRaceStrategyRequest;
use App\Http\Requests\Api\CriticalDetectionRequest;
use App\Http\Requests\Api\SkillAdviceRequest;
use App\Http\Requests\Api\TrainingRecommendationsRequest;
use App\Http\Requests\RecordRaceOutcomeRequest;
use App\Http\Requests\RecordTrainingOutcomeRequest;
use App\Services\TrainingAdvisoryService;
use App\ValueObjects\RaceResult;
use App\ValueObjects\RaceStrategy;
use App\ValueObjects\Recommendation;
use App\ValueObjects\TrainingContext;
use App\ValueObjects\TrainingOutcome;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * Advisory Controller
 *
 * API endpoints for recording training and race outcomes for prediction accuracy tracking.
 *
 * This controller enables the AI-powered Training Advisory System to learn from
 * actual outcomes by comparing predictions against reality. This data is used to:
 * - Calculate prediction accuracy metrics
 * - Identify models that need improvement
 * - Refine recommendation algorithms over time
 *
 * **Rate Limiting**: 10 requests/minute per user (as per design)
 *
 * **Validates: Requirements 3.8 (Prediction Accuracy Tracking)**
 *
 * @see \App\Services\TrainingAdvisoryService
 * @see \App\Services\PredictionAccuracyTracker
 */
class AdvisoryController extends Controller
{
    /**
     * Create a new Advisory Controller instance.
     */
    public function __construct(
        protected TrainingAdvisoryService $advisoryService
    ) {}

    /**
     * Get training facility recommendations based on current character state.
     *
     * Analyzes complete character state including stats, SP, skills, energy, mood,
     * bonds, facility levels, and phase to deliver actionable recommendations.
     *
     * **Endpoint**: POST /api/advisory/training/recommendations
     *
     * **Validates: Requirements 3.1 (Real-Time Training Recommendations)**
     *
     * **Request Body**:
     * ```json
     * {
     *   "career_run_id": "uuid-or-id",
     *   "storage_mode": "local",
     *   "turn_number": 15,
     *   "phase": "classic_year",
     *   "stats": {
     *     "speed": 450,
     *     "stamina": 380,
     *     "power": 420,
     *     "guts": 350,
     *     "wisdom": 400
     *   },
     *   "sp_available": 180,
     *   "energy": 75,
     *   "mood": "good",
     *   "acquired_skills": [1, 5, 12],
     *   "skill_hints": [
     *     {"skill_id": 23, "level": 3},
     *     {"skill_id": 45, "level": 2}
     *   ],
     *   "support_deck": {
     *     "cards": [
     *       {"id": 1, "bond": 85, "facility": "speed"},
     *       {"id": 2, "bond": 72, "facility": "stamina"}
     *     ]
     *   },
     *   "facility_levels": {
     *     "speed": 3,
     *     "stamina": 2,
     *     "power": 3,
     *     "guts": 2,
     *     "wisdom": 4
     *   },
     *   "upcoming_races": [
     *     {"id": 15, "distance": "medium", "turn": 18}
     *   ]
     * }
     * ```
     *
     * **Response**:
     * ```json
     * {
     *   "recommendations": [
     *     {
     *       "type": "training_facility",
     *       "priority": "high",
     *       "action": "Speed Training",
     *       "reasoning": "3 support cards present...",
     *       "expected_outcomes": {...},
     *       "risks": ["5% failure rate"],
     *       "confidence_score": 0.92
     *     }
     *   ],
     *   "critical_alerts": [],
     *   "response_time_ms": 1850,
     *   "ai_provider": "ollama"
     * }
     * ```
     *
     * @param  TrainingRecommendationsRequest  $request  Validated request with training context
     * @return JsonResponse Training recommendations with critical alerts
     */
    public function getTrainingRecommendations(TrainingRecommendationsRequest $request): JsonResponse
    {
        $startTime = microtime(true);

        try {
            $validated = $request->validated();

            // Build TrainingContext from validated request data
            $context = TrainingContext::fromArray([
                'turn_number' => $validated['turn_number'],
                'phase' => $validated['phase'],
                'stats' => $validated['stats'],
                'sp_available' => $validated['sp_available'],
                'energy' => $validated['energy'],
                'mood' => $validated['mood'],
                'acquired_skills' => $validated['acquired_skills'],
                'skill_hints' => $validated['skill_hints'],
                'support_deck' => $validated['support_deck'],
                'facility_levels' => $validated['facility_levels'],
                'upcoming_races' => $validated['upcoming_races'],
                'scenario' => $validated['scenario'] ?? null,
                'storage_mode' => $validated['storage_mode'],
                'career_run_id' => $validated['career_run_id'],
            ]);

            // Get training recommendations from the advisory service
            $recommendations = $this->advisoryService->getTrainingRecommendations($context);

            // Also detect any critical situations
            $criticalAlerts = $this->advisoryService->detectCriticalSituations($context);

            // Calculate response time
            $responseTimeMs = (int) round((microtime(true) - $startTime) * 1000);

            // Determine AI provider used (from recommendations metadata or default)
            $aiProvider = $this->determineAIProvider($recommendations);

            Log::info('[AdvisoryController] Training recommendations generated', [
                'career_run_id' => $validated['career_run_id'],
                'turn_number' => $validated['turn_number'],
                'storage_mode' => $validated['storage_mode'],
                'recommendations_count' => count($recommendations),
                'critical_alerts_count' => count($criticalAlerts),
                'response_time_ms' => $responseTimeMs,
                'ai_provider' => $aiProvider,
            ]);

            return response()->json([
                'recommendations' => $recommendations->map(fn ($rec) => $this->formatRecommendation($rec))->toArray(),
                'critical_alerts' => $criticalAlerts->map(fn ($alert) => $this->formatCriticalAlert($alert))->toArray(),
                'response_time_ms' => $responseTimeMs,
                'ai_provider' => $aiProvider,
            ]);
        } catch (\Exception $e) {
            report($e);
            Log::error('[AdvisoryController] Failed to generate training recommendations', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate training recommendations',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Determine the AI provider used for recommendations.
     *
     * @param  \App\Collections\RecommendationCollection  $recommendations
     * @return string AI provider name ('ollama', 'bedrock', or 'rule-based')
     */
    protected function determineAIProvider($recommendations): string
    {
        if ($recommendations->isEmpty()) {
            return 'rule-based';
        }

        $first = $recommendations->first();

        // Check if recommendation has source metadata
        if (isset($first->source)) {
            return $first->source;
        }

        // Default to rule-based if no AI metadata
        return 'rule-based';
    }

    /**
     * Format a Recommendation value object for JSON response.
     *
     * @return array<string, mixed>
     */
    protected function formatRecommendation(Recommendation $recommendation): array
    {
        return [
            'type' => $recommendation->type->value,
            'priority' => $recommendation->priority->value,
            'action' => $recommendation->action,
            'reasoning' => $recommendation->reasoning,
            'expected_outcomes' => $recommendation->expectedOutcomes,
            'risks' => $recommendation->risks,
            'confidence_score' => $recommendation->confidenceScore,
        ];
    }

    /**
     * Format a CriticalAlert value object for JSON response.
     *
     * @return array<string, mixed>
     */
    protected function formatCriticalAlert(\App\ValueObjects\CriticalAlert $alert): array
    {
        return [
            'type' => $alert->type->value,
            'priority' => $alert->priority->value,
            'message' => $alert->message,
            'action_items' => $alert->actionItems,
            'turns_until_critical' => $alert->turnsUntilCritical,
            'detailed_analysis' => $alert->detailedAnalysis,
        ];
    }

    /**
     * Get skill purchase advice based on available skills and SP budget.
     *
     * Analyzes available skills, calculates SP efficiency with hint level discounts,
     * and prioritizes purchases based on character build and remaining SP budget.
     *
     * **Endpoint**: POST /api/advisory/skills/advice
     *
     * **Validates: Requirements 3.2 (Skill Purchase Advisory)**
     *
     * **Request Body**:
     * ```json
     * {
     *   "character_id": "uuid-or-id",
     *   "storage_mode": "local",
     *   "sp_available": 220,
     *   "acquired_skills": [1, 5, 12],
     *   "available_skills": [
     *     {
     *       "id": 23,
     *       "name": "Swinging Maestro",
     *       "tier": "gold",
     *       "base_cost": 180,
     *       "hint_level": 3,
     *       "category": "stamina_recovery"
     *     }
     *   ]
     * }
     * ```
     *
     * **Response**:
     * ```json
     * {
     *   "recommendations": [
     *     {
     *       "skill_id": 23,
     *       "priority": "high",
     *       "action": "Purchase Swinging Maestro",
     *       "reasoning": "Gold stamina recovery skill with Level 3 hint...",
     *       "sp_cost": 126,
     *       "sp_remaining": 94,
     *       "expected_impact": "Enables Medium/Long distance races..."
     *     }
     *   ],
     *   "sp_budget_analysis": {
     *     "current": 220,
     *     "recommended_spend": 126,
     *     "remaining": 94,
     *     "projected_total": "300-350 by career end"
     *   },
     *   "response_time_ms": 1200,
     *   "ai_provider": "ollama"
     * }
     * ```
     *
     * @param  SkillAdviceRequest  $request  Validated request with skill context
     * @return JsonResponse Skill purchase recommendations
     */
    public function getSkillPurchaseAdvice(SkillAdviceRequest $request): JsonResponse
    {
        $startTime = microtime(true);

        try {
            $validated = $request->validated();

            // Extract and validate character ID
            $characterIdRaw = $validated['character_id'];
            $characterId = is_int($characterIdRaw) ? $characterIdRaw : (is_string($characterIdRaw) ? (int) $characterIdRaw : 0);

            // Extract and validate SP available
            $spAvailableRaw = $validated['sp_available'];
            $spAvailable = is_int($spAvailableRaw) ? $spAvailableRaw : (is_numeric($spAvailableRaw) ? (int) $spAvailableRaw : 0);

            // Extract acquired skills
            $acquiredSkillsRaw = $validated['acquired_skills'];
            $acquiredSkills = is_array($acquiredSkillsRaw) ? $acquiredSkillsRaw : [];

            // Extract optional fields
            $targetDistanceRaw = $validated['target_distance'] ?? 'medium';
            $targetDistance = is_string($targetDistanceRaw) ? $targetDistanceRaw : 'medium';

            $runningStyleRaw = $validated['running_style'] ?? 'escape';
            $runningStyle = is_string($runningStyleRaw) ? $runningStyleRaw : 'escape';

            // Build a Character-like object with the necessary properties for the service
            // Note: These are dynamic properties for the service, not actual model attributes
            $character = new \App\Models\Character;
            $character->id = $characterId;
            $character->setAttribute('available_sp', $spAvailable);
            $character->setAttribute('acquired_skills', $acquiredSkills);
            $character->setAttribute('target_distance', $targetDistance);
            $character->setAttribute('running_style', $runningStyle);

            /** @var array<int, array{id: int, name: string, tier: string, base_cost: int, hint_level: int, category: string}> $availableSkills */
            $availableSkills = $validated['available_skills'];

            // Get skill purchase advice from the advisory service
            $recommendations = $this->advisoryService->getSkillPurchaseAdvice(
                $character,
                $availableSkills
            );

            // Calculate response time
            $responseTimeMs = (int) round((microtime(true) - $startTime) * 1000);

            // Determine AI provider used
            $aiProvider = $this->determineSkillAIProvider($recommendations);

            // Calculate SP budget analysis
            $spBudgetAnalysis = $this->calculateSPBudgetAnalysis(
                $spAvailable,
                $recommendations
            );

            Log::info('[AdvisoryController] Skill purchase advice generated', [
                'character_id' => $validated['character_id'],
                'storage_mode' => $validated['storage_mode'],
                'sp_available' => $spAvailable,
                'available_skills_count' => \count($availableSkills),
                'recommendations_count' => \count($recommendations),
                'response_time_ms' => $responseTimeMs,
                'ai_provider' => $aiProvider,
            ]);

            return response()->json([
                'recommendations' => $recommendations->map(fn ($rec) => $this->formatSkillRecommendation($rec, $spAvailable))->toArray(),
                'sp_budget_analysis' => $spBudgetAnalysis,
                'response_time_ms' => $responseTimeMs,
                'ai_provider' => $aiProvider,
            ]);
        } catch (\Exception $e) {
            report($e);
            Log::error('[AdvisoryController] Failed to generate skill purchase advice', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate skill purchase advice',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Determine the AI provider used for skill recommendations.
     *
     * @param  \App\Collections\SkillRecommendationCollection  $recommendations
     * @return string AI provider name ('ollama', 'bedrock', or 'rule-based')
     */
    protected function determineSkillAIProvider($recommendations): string
    {
        if ($recommendations->isEmpty()) {
            return 'rule-based';
        }

        $first = $recommendations->first();

        // Check if recommendation has source metadata
        if (isset($first->source)) {
            return $first->source;
        }

        // Default to rule-based if no AI metadata
        return 'rule-based';
    }

    /**
     * Calculate SP budget analysis based on recommendations.
     *
     * @param  int  $currentSP  Current available SP
     * @param  \App\Collections\SkillRecommendationCollection  $recommendations
     * @return array<string, mixed> SP budget analysis
     */
    protected function calculateSPBudgetAnalysis(int $currentSP, $recommendations): array
    {
        $recommendedSpend = 0;

        foreach ($recommendations as $rec) {
            // Get the SP cost from the recommendation
            if (isset($rec->expectedOutcomes['sp_cost'])) {
                $cost = $rec->expectedOutcomes['sp_cost'];
                assert(is_int($cost) || is_numeric($cost));
                $recommendedSpend += (int) $cost;
            }
        }

        $remaining = $currentSP - $recommendedSpend;

        // Estimate projected total SP by career end (typical range 300-500)
        $projectedTotal = $this->estimateProjectedSPTotal($currentSP);

        return [
            'current' => $currentSP,
            'recommended_spend' => $recommendedSpend,
            'remaining' => max(0, $remaining),
            'projected_total' => $projectedTotal,
        ];
    }

    /**
     * Estimate projected total SP by career end.
     *
     * @param  int  $currentSP  Current available SP
     * @return string Projected SP range
     */
    protected function estimateProjectedSPTotal(int $currentSP): string
    {
        // Typical SP gain is 5-15 per turn, with 60-72 turns total
        // This is a rough estimate based on current SP
        if ($currentSP < 100) {
            return '300-400 by career end';
        } elseif ($currentSP < 200) {
            return '350-450 by career end';
        } elseif ($currentSP < 300) {
            return '400-500 by career end';
        } else {
            return '450-550 by career end';
        }
    }

    /**
     * Format a skill Recommendation value object for JSON response.
     *
     * @param  Recommendation  $recommendation  The recommendation to format
     * @param  int  $spAvailable  Current available SP
     * @return array<string, mixed>
     */
    protected function formatSkillRecommendation(Recommendation $recommendation, int $spAvailable): array
    {
        // Extract SP cost from expected outcomes or calculate from action with proper type safety
        $spCostRaw = $recommendation->expectedOutcomes['sp_cost'] ?? 0;
        $spCost = is_int($spCostRaw) ? $spCostRaw : (is_numeric($spCostRaw) ? (int) $spCostRaw : 0);
        $spRemaining = $spAvailable - $spCost;

        // Extract skill ID from expected outcomes or action with proper type safety
        $skillIdRaw = $recommendation->expectedOutcomes['skill_id'] ?? null;
        $skillId = $skillIdRaw !== null ? (is_int($skillIdRaw) ? $skillIdRaw : (is_numeric($skillIdRaw) ? (int) $skillIdRaw : null)) : null;

        // Extract expected impact with proper type safety
        $expectedImpactRaw = $recommendation->expectedOutcomes['expected_impact']
            ?? $recommendation->expectedOutcomes['impact']
            ?? 'Improves character performance';
        $expectedImpact = is_string($expectedImpactRaw) ? $expectedImpactRaw : 'Improves character performance';

        return [
            'skill_id' => $skillId,
            'priority' => $recommendation->priority->value,
            'action' => $recommendation->action,
            'reasoning' => $recommendation->reasoning,
            'sp_cost' => $spCost,
            'sp_remaining' => max(0, $spRemaining),
            'expected_impact' => $expectedImpact,
        ];
    }

    /**
     * Generate race strategy recommendations.
     *
     * Analyzes character stats, skills, and aptitudes against race requirements
     * to generate optimal running style recommendation and win probability.
     *
     * **Endpoint**: POST /api/advisory/race/strategy
     *
     * **Validates: Requirements 3.3 (Race Strategy Generation)**
     *
     * **Request Body**:
     * ```json
     * {
     *   "character_id": "uuid-or-id",
     *   "race_id": 15,
     *   "stats": {
     *     "speed": 850,
     *     "stamina": 650,
     *     "power": 720,
     *     "guts": 580,
     *     "wisdom": 690
     *   },
     *   "skills": [1, 5, 12, 23],
     *   "aptitudes": {
     *     "distance_medium": "A",
     *     "surface_turf": "B",
     *     "style_escape": "A"
     *   }
     * }
     * ```
     *
     * **Response**:
     * ```json
     * {
     *   "strategy": {
     *     "recommended_style": "escape",
     *     "reasoning": "A-grade Escape aptitude...",
     *     "win_probability": 0.78,
     *     "readiness_assessment": {
     *       "stamina": "sufficient",
     *       "speed": "excellent",
     *       "power": "good",
     *       "overall": "ready"
     *     },
     *     "risks": ["B-grade turf aptitude may reduce effectiveness"],
     *     "preparation_checklist": ["✓ Stamina requirement met"]
     *   },
     *   "response_time_ms": 1200,
     *   "ai_provider": "rule-based"
     * }
     * ```
     *
     * @param  AdvisoryRaceStrategyRequest  $request  Validated request with race context
     * @return JsonResponse Race strategy with recommendations
     */
    public function getRaceStrategy(AdvisoryRaceStrategyRequest $request): JsonResponse
    {
        $startTime = microtime(true);

        try {
            $validated = $request->validated();

            // Extract data from request with proper type safety
            $characterId = $validated['character_id'];
            $raceIdRaw = $validated['race_id'];
            $raceId = is_int($raceIdRaw) ? $raceIdRaw : (is_numeric($raceIdRaw) ? (int) $raceIdRaw : 0);

            /** @var array<string, int> $stats */
            $stats = $validated['stats'];

            /** @var array<int> $skills */
            $skills = $validated['skills'];

            /** @var array<string, string> $aptitudes */
            $aptitudes = $validated['aptitudes'];

            /** @var array<string, mixed> $raceDetails */
            $raceDetails = $validated['race_details'];

            $distanceRaw = $raceDetails['distance'];
            $distance = is_string($distanceRaw) ? $distanceRaw : 'medium';

            // Determine the best running style based on aptitudes
            $recommendedStyle = $this->determineRecommendedStyle($aptitudes);

            // Calculate stamina requirement for the race distance
            $staminaRequirement = $this->calculateStaminaRequirement(
                $distance,
                $recommendedStyle,
                $skills
            );

            // Assess readiness based on stats vs requirements
            $readinessAssessment = $this->assessReadiness(
                $stats,
                $staminaRequirement,
                $raceDetails
            );

            // Calculate win probability
            $winProbability = $this->calculateWinProbability(
                $stats,
                $aptitudes,
                $raceDetails,
                $recommendedStyle
            );

            // Generate reasoning
            $reasoning = $this->generateReasoning(
                $recommendedStyle,
                $aptitudes,
                $stats,
                $staminaRequirement,
                $raceDetails
            );

            // Identify risks
            $risks = $this->identifyRisks(
                $stats,
                $aptitudes,
                $staminaRequirement,
                $raceDetails
            );

            // Generate preparation checklist
            $preparationChecklist = $this->generatePreparationChecklist(
                $stats,
                $staminaRequirement,
                $skills,
                $raceDetails
            );

            // Build the strategy response
            $strategy = [
                'recommended_style' => $recommendedStyle,
                'reasoning' => $reasoning,
                'win_probability' => round($winProbability, 2),
                'readiness_assessment' => $readinessAssessment,
                'risks' => $risks,
                'preparation_checklist' => $preparationChecklist,
            ];

            // Calculate response time
            $responseTimeMs = (int) round((microtime(true) - $startTime) * 1000);

            Log::info('[AdvisoryController] Race strategy generated', [
                'character_id' => $characterId,
                'race_id' => $raceId,
                'recommended_style' => $recommendedStyle,
                'win_probability' => $winProbability,
                'response_time_ms' => $responseTimeMs,
            ]);

            return response()->json([
                'strategy' => $strategy,
                'response_time_ms' => $responseTimeMs,
                'ai_provider' => 'rule-based',
            ]);
        } catch (\Exception $e) {
            report($e);
            Log::error('[AdvisoryController] Failed to generate race strategy', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate race strategy',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Determine the recommended running style based on aptitudes.
     *
     * @param  array<string, string>  $aptitudes  Character aptitudes
     * @return string Recommended running style
     */
    protected function determineRecommendedStyle(array $aptitudes): string
    {
        $styleAptitudes = [
            'escape' => $aptitudes['style_escape'] ?? 'C',
            'lead' => $aptitudes['style_lead'] ?? 'C',
            'pace' => $aptitudes['style_pace'] ?? 'C',
            'chase' => $aptitudes['style_chase'] ?? 'C',
        ];

        // Convert grades to numeric values for comparison
        $gradeValues = ['G' => 1, 'F' => 2, 'E' => 3, 'D' => 4, 'C' => 5, 'B' => 6, 'A' => 7, 'S' => 8];

        $bestStyle = 'escape';
        $bestValue = 0;

        foreach ($styleAptitudes as $style => $grade) {
            $value = $gradeValues[$grade] ?? 5;
            if ($value > $bestValue) {
                $bestValue = $value;
                $bestStyle = $style;
            }
        }

        return $bestStyle;
    }

    /**
     * Calculate stamina requirement for a race distance and running style.
     *
     * @param  string  $distance  Race distance category
     * @param  string  $style  Running style
     * @param  array<int>  $skills  Equipped skill IDs
     * @return int Required stamina
     */
    protected function calculateStaminaRequirement(string $distance, string $style, array $skills): int
    {
        // Base stamina requirements by distance (for Escape style)
        $baseRequirements = [
            'sprint' => 375,  // 350-400 range
            'mile' => 475,    // 450-500 range
            'medium' => 650,  // 600-700 range
            'long' => 925,    // 850-1000 range
        ];

        $base = $baseRequirements[$distance] ?? 650;

        // Apply running style modifier
        $styleModifiers = [
            'escape' => 1.0,   // Highest stamina consumption
            'lead' => 0.95,   // Slightly lower
            'pace' => 0.90,   // Lower consumption
            'chase' => 0.85,  // Lowest consumption
        ];

        $modifier = $styleModifiers[$style] ?? 1.0;
        $requirement = (int) round($base * $modifier);

        // Reduce requirement for recovery skills (estimate 150-200 per gold recovery skill)
        // For simplicity, assume each skill ID in the list could be a recovery skill
        // In a real implementation, we'd check the skill database
        $recoverySkillCount = min(\count($skills), 2); // Cap at 2 recovery skills
        $recoveryReduction = $recoverySkillCount * 175; // Average of 150-200

        return max(200, $requirement - $recoveryReduction);
    }

    /**
     * Assess character readiness for the race.
     *
     * @param  array<string, int>  $stats  Character stats
     * @param  int  $staminaRequirement  Required stamina
     * @param  array<string, mixed>  $raceDetails  Race details
     * @return array<string, string> Readiness assessment
     */
    protected function assessReadiness(array $stats, int $staminaRequirement, array $raceDetails): array
    {
        $assessment = [];

        // Assess stamina
        $staminaDiff = $stats['stamina'] - $staminaRequirement;
        if ($staminaDiff >= 100) {
            $assessment['stamina'] = 'excellent';
        } elseif ($staminaDiff >= 0) {
            $assessment['stamina'] = 'sufficient';
        } elseif ($staminaDiff >= -100) {
            $assessment['stamina'] = 'marginal';
        } else {
            $assessment['stamina'] = 'insufficient';
        }

        // Assess speed
        if ($stats['speed'] >= 1000) {
            $assessment['speed'] = 'excellent';
        } elseif ($stats['speed'] >= 800) {
            $assessment['speed'] = 'good';
        } elseif ($stats['speed'] >= 600) {
            $assessment['speed'] = 'adequate';
        } else {
            $assessment['speed'] = 'low';
        }

        // Assess power
        if ($stats['power'] >= 900) {
            $assessment['power'] = 'excellent';
        } elseif ($stats['power'] >= 700) {
            $assessment['power'] = 'good';
        } elseif ($stats['power'] >= 500) {
            $assessment['power'] = 'adequate';
        } else {
            $assessment['power'] = 'low';
        }

        // Overall assessment
        $readyCount = 0;
        if (\in_array($assessment['stamina'], ['excellent', 'sufficient'], true)) {
            $readyCount++;
        }
        if (\in_array($assessment['speed'], ['excellent', 'good'], true)) {
            $readyCount++;
        }
        if (\in_array($assessment['power'], ['excellent', 'good'], true)) {
            $readyCount++;
        }

        if ($readyCount >= 3) {
            $assessment['overall'] = 'ready';
        } elseif ($readyCount >= 2) {
            $assessment['overall'] = 'mostly_ready';
        } elseif ($readyCount >= 1) {
            $assessment['overall'] = 'needs_preparation';
        } else {
            $assessment['overall'] = 'not_ready';
        }

        return $assessment;
    }

    /**
     * Calculate win probability based on stats, aptitudes, and race conditions.
     *
     * @param  array<string, int>  $stats  Character stats
     * @param  array<string, string>  $aptitudes  Character aptitudes
     * @param  array<string, mixed>  $raceDetails  Race details
     * @param  string  $style  Running style
     * @return float Win probability (0.0-1.0)
     */
    protected function calculateWinProbability(
        array $stats,
        array $aptitudes,
        array $raceDetails,
        string $style
    ): float {
        $probability = 0.5; // Base probability

        // Stat contribution (normalized to 0-0.3 range)
        $totalStats = $stats['speed'] + $stats['stamina'] + $stats['power'] + $stats['guts'] + $stats['wisdom'];
        $statBonus = min(0.3, ($totalStats - 2500) / 5000); // 2500 is baseline, 5000 is excellent
        $probability += $statBonus;

        // Aptitude contribution
        $gradeValues = ['G' => 0.5, 'F' => 0.6, 'E' => 0.7, 'D' => 0.8, 'C' => 0.9, 'B' => 1.0, 'A' => 1.1, 'S' => 1.2];

        // Distance aptitude with proper type safety
        $distanceRaw = $raceDetails['distance'] ?? 'medium';
        $distance = is_string($distanceRaw) ? $distanceRaw : 'medium';
        $distanceKey = 'distance_'.$distance;
        $distanceGrade = $aptitudes[$distanceKey] ?? 'C';
        $distanceMultiplier = $gradeValues[$distanceGrade] ?? 0.9;

        // Surface aptitude with proper type safety
        $surfaceRaw = $raceDetails['surface'] ?? 'turf';
        $surface = is_string($surfaceRaw) ? $surfaceRaw : 'turf';
        $surfaceKey = 'surface_'.$surface;
        $surfaceGrade = $aptitudes[$surfaceKey] ?? 'C';
        $surfaceMultiplier = $gradeValues[$surfaceGrade] ?? 0.9;

        // Style aptitude
        $styleKey = 'style_'.$style;
        $styleGrade = $aptitudes[$styleKey] ?? 'C';
        $styleMultiplier = $gradeValues[$styleGrade] ?? 0.9;

        // Apply aptitude multipliers
        $aptitudeBonus = (($distanceMultiplier + $surfaceMultiplier + $styleMultiplier) / 3 - 0.9) * 0.3;
        $probability += $aptitudeBonus;

        // Track condition penalty with proper type safety
        $conditionPenalties = [
            'firm' => 0,
            'good' => 0,
            'soft' => -0.05,
            'heavy' => -0.15,
        ];
        $trackConditionRaw = $raceDetails['track_condition'] ?? 'good';
        $trackCondition = is_string($trackConditionRaw) ? $trackConditionRaw : 'good';
        $probability += $conditionPenalties[$trackCondition] ?? 0;

        // Competition level adjustment with proper type safety
        $competitionAdjustments = [
            'Debut' => 0.1,
            'Pre-OP' => 0.05,
            'OP' => 0,
            'G3' => -0.05,
            'G2' => -0.1,
            'G1' => -0.15,
        ];
        $competitionLevelRaw = $raceDetails['competition_level'] ?? 'G3';
        $competitionLevel = is_string($competitionLevelRaw) ? $competitionLevelRaw : 'G3';
        $probability += $competitionAdjustments[$competitionLevel] ?? 0;

        // Clamp probability to valid range
        return max(0.05, min(0.95, $probability));
    }

    /**
     * Generate reasoning for the recommended strategy.
     *
     * @param  string  $style  Recommended running style
     * @param  array<string, string>  $aptitudes  Character aptitudes
     * @param  array<string, int>  $stats  Character stats
     * @param  int  $staminaRequirement  Required stamina
     * @param  array<string, mixed>  $raceDetails  Race details
     * @return string Reasoning explanation
     */
    protected function generateReasoning(
        string $style,
        array $aptitudes,
        array $stats,
        int $staminaRequirement,
        array $raceDetails
    ): string {
        $styleKey = 'style_'.$style;
        $styleGrade = $aptitudes[$styleKey] ?? 'C';
        $styleName = ucfirst($style);

        $distanceRaw = $raceDetails['distance'] ?? 'medium';
        $distance = is_string($distanceRaw) ? $distanceRaw : 'medium';
        $distanceKey = 'distance_'.$distance;
        $distanceGrade = $aptitudes[$distanceKey] ?? 'C';

        $staminaDiff = $stats['stamina'] - $staminaRequirement;

        $reasoning = "{$styleGrade}-grade {$styleName} aptitude provides optimal race positioning. ";
        $reasoning .= "Character has {$distanceGrade}-grade aptitude for {$distance} distance races. ";

        if ($staminaDiff >= 0) {
            $reasoning .= "Stamina ({$stats['stamina']}) meets the {$staminaRequirement} requirement for this distance. ";
        } else {
            $reasoning .= "Warning: Stamina ({$stats['stamina']}) is {$staminaDiff} below the {$staminaRequirement} requirement. ";
        }

        if ($stats['speed'] >= 800) {
            $reasoning .= "Strong Speed stat ({$stats['speed']}) supports competitive performance.";
        } else {
            $reasoning .= "Speed stat ({$stats['speed']}) may limit competitive edge.";
        }

        return $reasoning;
    }

    /**
     * Identify potential risks for the race.
     *
     * @param  array<string, int>  $stats  Character stats
     * @param  array<string, string>  $aptitudes  Character aptitudes
     * @param  int  $staminaRequirement  Required stamina
     * @param  array<string, mixed>  $raceDetails  Race details
     * @return array<string> List of risks
     */
    protected function identifyRisks(
        array $stats,
        array $aptitudes,
        int $staminaRequirement,
        array $raceDetails
    ): array {
        $risks = [];

        // Stamina risk
        $staminaDiff = $stats['stamina'] - $staminaRequirement;
        if ($staminaDiff < 0) {
            $risks[] = "Stamina is {$staminaDiff} below requirement - risk of running out of energy";
        }

        // Surface aptitude risk with proper type safety
        $surfaceRaw = $raceDetails['surface'] ?? 'turf';
        $surface = is_string($surfaceRaw) ? $surfaceRaw : 'turf';
        $surfaceKey = 'surface_'.$surface;
        $surfaceGrade = $aptitudes[$surfaceKey] ?? 'C';
        if (\in_array($surfaceGrade, ['G', 'F', 'E', 'D'], true)) {
            $risks[] = "{$surfaceGrade}-grade {$surface} aptitude may reduce effectiveness by 5-15%";
        }

        // Distance aptitude risk with proper type safety
        $distanceRaw = $raceDetails['distance'] ?? 'medium';
        $distance = is_string($distanceRaw) ? $distanceRaw : 'medium';
        $distanceKey = 'distance_'.$distance;
        $distanceGrade = $aptitudes[$distanceKey] ?? 'C';
        if (\in_array($distanceGrade, ['G', 'F', 'E', 'D'], true)) {
            $risks[] = "{$distanceGrade}-grade {$distance} distance aptitude may impact performance";
        }

        // Track condition risk with proper type safety
        $trackConditionRaw = $raceDetails['track_condition'] ?? 'good';
        $trackCondition = is_string($trackConditionRaw) ? $trackConditionRaw : 'good';
        if (\in_array($trackCondition, ['soft', 'heavy'], true)) {
            $penalty = $trackCondition === 'heavy' ? '15%' : '5%';
            $risks[] = "{$trackCondition} track condition may reduce performance by {$penalty}";
        }

        // Competition level risk with proper type safety
        $competitionLevelRaw = $raceDetails['competition_level'] ?? 'G3';
        $competitionLevel = is_string($competitionLevelRaw) ? $competitionLevelRaw : 'G3';
        if (\in_array($competitionLevel, ['G1', 'G2'], true)) {
            $risks[] = "High competition level ({$competitionLevel}) - expect strong opponents";
        }

        // Low stats risk
        if ($stats['speed'] < 600) {
            $risks[] = 'Low Speed stat may result in poor positioning';
        }
        if ($stats['power'] < 500) {
            $risks[] = 'Low Power stat may affect acceleration and overtaking';
        }

        return $risks;
    }

    /**
     * Generate preparation checklist for the race.
     *
     * @param  array<string, int>  $stats  Character stats
     * @param  int  $staminaRequirement  Required stamina
     * @param  array<int>  $skills  Equipped skill IDs
     * @param  array<string, mixed>  $raceDetails  Race details
     * @return array<string> Preparation checklist items
     */
    protected function generatePreparationChecklist(
        array $stats,
        int $staminaRequirement,
        array $skills,
        array $raceDetails
    ): array {
        $checklist = [];

        // Stamina check
        if ($stats['stamina'] >= $staminaRequirement) {
            $checklist[] = '✓ Stamina requirement met';
        } else {
            $gap = $staminaRequirement - $stats['stamina'];
            $checklist[] = "✗ Need {$gap} more stamina - focus on Stamina training";
        }

        // Speed check
        if ($stats['speed'] >= 800) {
            $checklist[] = '✓ Speed above 800';
        } else {
            $checklist[] = '⚠ Consider increasing Speed for better positioning';
        }

        // Power check
        if ($stats['power'] >= 700) {
            $checklist[] = '✓ Power above 700';
        } else {
            $checklist[] = '⚠ Consider increasing Power for better acceleration';
        }

        // Skills check
        if (\count($skills) > 0) {
            $checklist[] = '✓ Skills equipped ('.\count($skills).' skills)';
        } else {
            $checklist[] = '⚠ No skills equipped - consider purchasing recovery skills';
        }

        // Track condition advice
        if (\in_array($raceDetails['track_condition'], ['soft', 'heavy'], true)) {
            $checklist[] = '⚠ Consider Power-boosting skills for '.$raceDetails['track_condition'].' track';
        }

        // Distance-specific advice
        if ($raceDetails['distance'] === 'long') {
            $checklist[] = '⚠ Long distance race - ensure stamina recovery skills are equipped';
        }

        return $checklist;
    }

    /**
     * Detect critical situations requiring immediate attention.
     *
     * Analyzes character state to identify critical situations such as:
     * - Stamina crisis (insufficient for upcoming races)
     * - SP shortage (budget below target)
     * - Energy critical (high failure rate risk)
     * - Bond behind schedule (Friendship Training at risk)
     * - Facility imbalance (uneven facility levels)
     * - Race unready (stats below requirements)
     *
     * **Endpoint**: POST /api/advisory/critical/detect
     *
     * **Validates: Requirements 3.4 (Critical Situation Detection)**
     *
     * **Request Body**:
     * ```json
     * {
     *   "career_run_id": "uuid-or-id",
     *   "turn_number": 35,
     *   "context": {
     *     "stats": {"speed": 600, "stamina": 320, "power": 550, "guts": 480, "wisdom": 520},
     *     "energy": 35,
     *     "upcoming_races": [{"distance": "medium", "turn": 38}],
     *     "support_bonds": [65, 70, 58, 75, 68, 72]
     *   }
     * }
     * ```
     *
     * **Response**:
     * ```json
     * {
     *   "alerts": [
     *     {
     *       "type": "stamina_crisis",
     *       "priority": "critical",
     *       "message": "Stamina critically low for upcoming Medium race",
     *       "action_items": ["Focus next 3 turns on Stamina training"],
     *       "turns_until_critical": 3,
     *       "detailed_analysis": "Current stamina of 320 is 280 points below..."
     *     }
     *   ],
     *   "response_time_ms": 150
     * }
     * ```
     *
     * @param  \App\Http\Requests\Api\CriticalDetectionRequest  $request  Validated request with context
     * @return JsonResponse Critical alerts
     */
    public function detectCriticalSituations(CriticalDetectionRequest $request): JsonResponse
    {
        $startTime = microtime(true);

        try {
            $validated = $request->validated();

            // Extract context data with proper type safety
            /** @var array<string, mixed> $context */
            $context = $validated['context'];
            $turnNumberRaw = $validated['turn_number'];
            $turnNumber = is_int($turnNumberRaw) ? $turnNumberRaw : (is_numeric($turnNumberRaw) ? (int) $turnNumberRaw : 1);
            $careerRunId = $validated['career_run_id'];
            $storageModeRaw = $validated['storage_mode'] ?? 'account';
            $storageMode = is_string($storageModeRaw) ? $storageModeRaw : 'account';

            // Extract and cast context fields with proper type safety
            $phaseRaw = $context['phase'] ?? 'classic_year';
            $phase = is_string($phaseRaw) ? $phaseRaw : 'classic_year';
            /** @var array<string, int> $stats */
            $stats = $context['stats'];
            $spAvailableRaw = $context['sp_available'] ?? 0;
            $spAvailable = is_int($spAvailableRaw) ? $spAvailableRaw : (is_numeric($spAvailableRaw) ? (int) $spAvailableRaw : 0);
            $energyRaw = $context['energy'];
            $energy = is_int($energyRaw) ? $energyRaw : (is_numeric($energyRaw) ? (int) $energyRaw : 50);
            $moodRaw = $context['mood'] ?? 'normal';
            $mood = is_string($moodRaw) ? $moodRaw : 'normal';
            /** @var array<int> $acquiredSkills */
            $acquiredSkills = $context['acquired_skills'] ?? [];
            /** @var array<mixed> $skillHints */
            $skillHints = $context['skill_hints'] ?? [];
            /** @var array<int> $supportBonds */
            $supportBonds = $context['support_bonds'] ?? [];
            /** @var array<string, int> $facilityLevels */
            $facilityLevels = $context['facility_levels'] ?? [
                'speed' => 1,
                'stamina' => 1,
                'power' => 1,
                'guts' => 1,
                'wisdom' => 1,
            ];
            /** @var array<mixed> $upcomingRaces */
            $upcomingRaces = $context['upcoming_races'] ?? [];
            $scenario = $context['scenario'] ?? null;

            // Build TrainingContext from validated request data
            $trainingContext = TrainingContext::fromArray([
                'turn_number' => $turnNumber,
                'phase' => $phase,
                'stats' => $stats,
                'sp_available' => $spAvailable,
                'energy' => $energy,
                'mood' => $mood,
                'acquired_skills' => $acquiredSkills,
                'skill_hints' => $skillHints,
                'support_deck' => $this->buildSupportDeckFromBonds($supportBonds),
                'facility_levels' => $facilityLevels,
                'upcoming_races' => $upcomingRaces,
                'scenario' => $scenario,
                'storage_mode' => $storageMode,
                'career_run_id' => $careerRunId,
            ]);

            // Detect critical situations using the advisory service
            $criticalAlerts = $this->advisoryService->detectCriticalSituations($trainingContext);

            // Calculate response time
            $responseTimeMs = (int) round((microtime(true) - $startTime) * 1000);

            Log::info('[AdvisoryController] Critical situations detected', [
                'career_run_id' => $careerRunId,
                'turn_number' => $turnNumber,
                'storage_mode' => $storageMode,
                'alerts_count' => count($criticalAlerts),
                'response_time_ms' => $responseTimeMs,
            ]);

            return response()->json([
                'alerts' => $criticalAlerts->map(fn ($alert) => $this->formatCriticalAlert($alert))->toArray(),
                'response_time_ms' => $responseTimeMs,
            ]);
        } catch (\Exception $e) {
            report($e);
            Log::error('[AdvisoryController] Failed to detect critical situations', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to detect critical situations',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Build a support deck structure from bond levels array.
     *
     * Converts a simple array of bond levels into the support deck format
     * expected by TrainingContext.
     *
     * @param  array<int>  $bonds  Array of bond levels (0-100)
     * @return array<string, mixed> Support deck structure
     */
    protected function buildSupportDeckFromBonds(array $bonds): array
    {
        if (empty($bonds)) {
            return ['cards' => []];
        }

        // Default facilities for cards (distribute evenly)
        $facilities = ['speed', 'stamina', 'power', 'guts', 'wisdom', 'friend'];

        $cards = [];
        foreach ($bonds as $index => $bond) {
            $cards[] = [
                'id' => $index + 1,
                'bond' => (int) $bond,
                'facility' => $facilities[$index % \count($facilities)],
            ];
        }

        return ['cards' => $cards];
    }

    /**
     * Record a training outcome for accuracy tracking.
     *
     * Compares the predicted stat gains from a recommendation against
     * the actual stat gains achieved during training. Calculates an
     * accuracy score and persists the record for analysis.
     *
     * **Endpoint**: POST /api/advisory/training/outcome
     *
     * **Request Body**:
     * ```json
     * {
     *   "career_run_id": 123,
     *   "turn_number": 15,
     *   "recommendation": {
     *     "type": "training_facility",
     *     "priority": "high",
     *     "action": "Speed Training",
     *     "reasoning": "3 support cards present...",
     *     "expected_outcomes": {
     *       "stat_gains": {"speed": 45, "power": 12}
     *     },
     *     "risks": ["5% failure rate"],
     *     "confidence_score": 0.92
     *   },
     *   "actual_outcome": {
     *     "facility": "speed",
     *     "stat_gains": {"speed": 48, "power": 10},
     *     "bond_increases": [7, 7, 7],
     *     "skill_hints": [],
     *     "was_failure": false,
     *     "was_injury": false,
     *     "energy_change": -10
     *   }
     * }
     * ```
     *
     * **Response**:
     * ```json
     * {
     *   "success": true,
     *   "message": "Training outcome recorded successfully",
     *   "accuracy_score": 0.94,
     *   "prediction_id": 456
     * }
     * ```
     *
     * @param  RecordTrainingOutcomeRequest  $request  Validated request
     * @return JsonResponse Success response with accuracy score
     */
    public function recordTrainingOutcome(RecordTrainingOutcomeRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            // Extract data from request with proper type safety
            $careerIdRaw = $validated['career_run_id'];
            $careerId = is_int($careerIdRaw) ? $careerIdRaw : (is_numeric($careerIdRaw) ? (int) $careerIdRaw : 0);
            $turnNumberRaw = $validated['turn_number'];
            $turnNumber = is_int($turnNumberRaw) ? $turnNumberRaw : (is_numeric($turnNumberRaw) ? (int) $turnNumberRaw : 1);

            /** @var array<string, mixed> $recommendationData */
            $recommendationData = $validated['recommendation'];

            /** @var array<string, mixed> $actualOutcomeData */
            $actualOutcomeData = $validated['actual_outcome'];

            // Extract recommendation fields with type safety
            $recTypeRaw = $recommendationData['type'];
            $recType = is_string($recTypeRaw) ? $recTypeRaw : 'training_facility';
            $recPriorityRaw = $recommendationData['priority'];
            $recPriority = is_string($recPriorityRaw) ? $recPriorityRaw : 'medium';
            $recActionRaw = $recommendationData['action'];
            $recAction = is_string($recActionRaw) ? $recActionRaw : '';
            $recReasoningRaw = $recommendationData['reasoning'];
            $recReasoning = is_string($recReasoningRaw) ? $recReasoningRaw : '';
            /** @var array<string, mixed> $recExpectedOutcomes */
            $recExpectedOutcomes = $recommendationData['expected_outcomes'];
            /** @var array<string> $recRisks */
            $recRisks = $recommendationData['risks'] ?? [];
            $recConfidenceScoreRaw = $recommendationData['confidence_score'] ?? null;
            $recConfidenceScore = $recConfidenceScoreRaw !== null && is_numeric($recConfidenceScoreRaw)
                ? (float) $recConfidenceScoreRaw
                : null;

            // Create Recommendation value object
            $recommendation = new Recommendation(
                type: \App\Enums\RecommendationType::from($recType),
                priority: \App\Enums\Priority::from($recPriority),
                action: $recAction,
                reasoning: $recReasoning,
                expectedOutcomes: $recExpectedOutcomes,
                risks: $recRisks,
                confidenceScore: $recConfidenceScore,
            );

            // Extract actual outcome fields with type safety
            $facilityRaw = $actualOutcomeData['facility'];
            $facility = is_string($facilityRaw) ? $facilityRaw : 'speed';
            /** @var array<string, int> $statGains */
            $statGains = $actualOutcomeData['stat_gains'];
            /** @var array<int> $bondIncreases */
            $bondIncreases = $actualOutcomeData['bond_increases'] ?? [];
            /** @var array<mixed> $skillHints */
            $skillHints = $actualOutcomeData['skill_hints'] ?? [];
            $wasFailureRaw = $actualOutcomeData['was_failure'] ?? false;
            $wasFailure = is_bool($wasFailureRaw) ? $wasFailureRaw : (bool) $wasFailureRaw;
            $wasInjuryRaw = $actualOutcomeData['was_injury'] ?? false;
            $wasInjury = is_bool($wasInjuryRaw) ? $wasInjuryRaw : (bool) $wasInjuryRaw;
            $energyChangeRaw = $actualOutcomeData['energy_change'] ?? 0;
            $energyChange = is_int($energyChangeRaw) ? $energyChangeRaw : (is_numeric($energyChangeRaw) ? (int) $energyChangeRaw : 0);
            /** @var array<string, mixed> $additionalData */
            $additionalData = $actualOutcomeData['additional_data'] ?? [];

            // Create TrainingOutcome value object
            $actualOutcome = TrainingOutcome::fromArray([
                'turn_number' => $turnNumber,
                'facility' => $facility,
                'stat_gains' => $statGains,
                'bond_increases' => $bondIncreases,
                'skill_hints' => $skillHints,
                'was_failure' => $wasFailure,
                'was_injury' => $wasInjury,
                'energy_change' => $energyChange,
                'additional_data' => $additionalData,
            ]);

            // Record the outcome
            $this->advisoryService->recordTrainingOutcome(
                $careerId,
                $turnNumber,
                $recommendation,
                $actualOutcome
            );

            Log::info('[AdvisoryController] Training outcome recorded', [
                'career_id' => $careerId,
                'turn_number' => $turnNumber,
                'facility' => $actualOutcome->facility,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Training outcome recorded successfully',
                'data' => [
                    'career_run_id' => $careerId,
                    'turn_number' => $turnNumber,
                    'facility' => $actualOutcome->facility,
                    'was_successful' => $actualOutcome->wasSuccessful(),
                ],
            ], 201);
        } catch (\Exception $e) {
            report($e);
            Log::error('[AdvisoryController] Failed to record training outcome', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to record training outcome',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Record a race outcome for accuracy tracking.
     *
     * Compares the predicted race strategy (running style, win probability)
     * against the actual race result. Calculates an accuracy score based on
     * placement prediction accuracy.
     *
     * **Endpoint**: POST /api/advisory/race/outcome
     *
     * **Request Body**:
     * ```json
     * {
     *   "career_run_id": 123,
     *   "race_id": 15,
     *   "strategy": {
     *     "recommended_style": "escape",
     *     "reasoning": "A-grade Escape aptitude...",
     *     "win_probability": 0.78,
     *     "readiness_assessment": {
     *       "stamina": "sufficient",
     *       "speed": "excellent",
     *       "power": "good",
     *       "overall": "ready"
     *     },
     *     "risks": ["B-grade turf aptitude"],
     *     "preparation_checklist": ["✓ Stamina requirement met"],
     *     "predicted_outcomes": {},
     *     "model_version": "v1.0"
     *   },
     *   "actual_result": {
     *     "placement": 1,
     *     "total_competitors": 18,
     *     "running_style": "escape",
     *     "was_win": true,
     *     "was_placed": true,
     *     "finish_time": 125.5,
     *     "fan_gain": 5000
     *   }
     * }
     * ```
     *
     * **Response**:
     * ```json
     * {
     *   "success": true,
     *   "message": "Race outcome recorded successfully",
     *   "accuracy_score": 1.0,
     *   "prediction_id": 789
     * }
     * ```
     *
     * @param  RecordRaceOutcomeRequest  $request  Validated request
     * @return JsonResponse Success response with accuracy score
     */
    public function recordRaceOutcome(RecordRaceOutcomeRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            // Extract data from request with proper type safety
            $careerIdRaw = $validated['career_run_id'];
            $careerId = is_int($careerIdRaw) ? $careerIdRaw : (is_numeric($careerIdRaw) ? (int) $careerIdRaw : 0);
            $raceIdRaw = $validated['race_id'];
            $raceId = is_int($raceIdRaw) ? $raceIdRaw : (is_numeric($raceIdRaw) ? (int) $raceIdRaw : 0);

            $strategyDataRaw = $validated['strategy'];
            if (! is_array($strategyDataRaw)) {
                throw new \InvalidArgumentException('Strategy data must be an array');
            }
            /** @var array<string, mixed> $strategyData */
            $strategyData = $strategyDataRaw;

            $actualResultDataRaw = $validated['actual_result'];
            if (! is_array($actualResultDataRaw)) {
                throw new \InvalidArgumentException('Actual result data must be an array');
            }
            /** @var array<string, mixed> $actualResultData */
            $actualResultData = $actualResultDataRaw;

            // Extract strategy fields with type safety
            $recommendedStyleRaw = $strategyData['recommended_style'];
            $recommendedStyle = is_string($recommendedStyleRaw) ? $recommendedStyleRaw : 'escape';
            $reasoningRaw = $strategyData['reasoning'];
            $reasoning = is_string($reasoningRaw) ? $reasoningRaw : '';
            $winProbabilityRaw = $strategyData['win_probability'];
            $winProbability = is_numeric($winProbabilityRaw) ? (float) $winProbabilityRaw : 0.5;
            $readinessAssessmentRaw = $strategyData['readiness_assessment'];
            /** @var array<string, string> $readinessAssessment */
            $readinessAssessment = is_array($readinessAssessmentRaw) ? $readinessAssessmentRaw : [];
            /** @var array<string> $risks */
            $risks = $strategyData['risks'] ?? [];
            /** @var array<string> $preparationChecklist */
            $preparationChecklist = $strategyData['preparation_checklist'] ?? [];
            /** @var array<string, mixed> $predictedOutcomes */
            $predictedOutcomes = $strategyData['predicted_outcomes'] ?? [];
            $modelVersionRaw = $strategyData['model_version'] ?? 'unknown';
            $modelVersion = is_string($modelVersionRaw) ? $modelVersionRaw : 'unknown';

            // Create RaceStrategy value object
            $strategy = RaceStrategy::fromArray([
                'race_id' => $raceId,
                'recommended_style' => $recommendedStyle,
                'reasoning' => $reasoning,
                'win_probability' => $winProbability,
                'readiness_assessment' => $readinessAssessment,
                'risks' => $risks,
                'preparation_checklist' => $preparationChecklist,
                'predicted_outcomes' => $predictedOutcomes,
                'model_version' => $modelVersion,
            ]);

            // Extract actual result fields with type safety
            $placementRaw = $actualResultData['placement'];
            $placement = is_int($placementRaw) ? $placementRaw : (is_numeric($placementRaw) ? (int) $placementRaw : 1);
            $totalCompetitorsRaw = $actualResultData['total_competitors'];
            $totalCompetitors = is_int($totalCompetitorsRaw) ? $totalCompetitorsRaw : (is_numeric($totalCompetitorsRaw) ? (int) $totalCompetitorsRaw : 18);
            $runningStyleRaw = $actualResultData['running_style'];
            $runningStyle = is_string($runningStyleRaw) ? $runningStyleRaw : 'escape';
            $wasWinRaw = $actualResultData['was_win'] ?? false;
            $wasWin = is_bool($wasWinRaw) ? $wasWinRaw : (bool) $wasWinRaw;
            $wasPlacedRaw = $actualResultData['was_placed'] ?? false;
            $wasPlaced = is_bool($wasPlacedRaw) ? $wasPlacedRaw : (bool) $wasPlacedRaw;
            $finishTimeRaw = $actualResultData['finish_time'] ?? null;
            $finishTime = $finishTimeRaw !== null && is_numeric($finishTimeRaw)
                ? (float) $finishTimeRaw
                : null;
            $fanGainRaw = $actualResultData['fan_gain'] ?? 0;
            $fanGain = is_int($fanGainRaw) ? $fanGainRaw : (is_numeric($fanGainRaw) ? (int) $fanGainRaw : 0);
            /** @var array<string, mixed> $additionalData */
            $additionalData = $actualResultData['additional_data'] ?? [];

            // Create RaceResult value object
            $actualResult = RaceResult::fromArray([
                'race_id' => $raceId,
                'placement' => $placement,
                'total_competitors' => $totalCompetitors,
                'running_style' => $runningStyle,
                'was_win' => $wasWin,
                'was_placed' => $wasPlaced,
                'finish_time' => $finishTime,
                'fan_gain' => $fanGain,
                'additional_data' => $additionalData,
            ]);

            // Record the outcome
            $this->advisoryService->recordRaceOutcome(
                $careerId,
                $strategy,
                $actualResult
            );

            Log::info('[AdvisoryController] Race outcome recorded', [
                'career_id' => $careerId,
                'race_id' => $raceId,
                'placement' => $actualResult->placement,
                'was_win' => $actualResult->isWin(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Race outcome recorded successfully',
                'data' => [
                    'career_run_id' => $careerId,
                    'race_id' => $raceId,
                    'placement' => $actualResult->placement,
                    'placement_ordinal' => $actualResult->getPlacementOrdinal(),
                    'result_quality' => $actualResult->getResultQuality(),
                ],
            ], 201);
        } catch (\Exception $e) {
            report($e);
            Log::error('[AdvisoryController] Failed to record race outcome', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to record race outcome',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }
}
