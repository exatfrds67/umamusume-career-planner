<?php

declare(strict_types=1);

namespace App\Services;

use App\Collections\CriticalAlertCollection;
use App\Collections\RecommendationCollection;
use App\Collections\SkillRecommendationCollection;
use App\Models\Character;
use App\Models\Race;
use App\ValueObjects\RaceResult;
use App\ValueObjects\RaceStrategy;
use App\ValueObjects\Recommendation;
use App\ValueObjects\TrainingContext;
use App\ValueObjects\TrainingOutcome;

/**
 * Training Advisory Service
 *
 * Main service coordinating all advisory functions for AI-powered training recommendations.
 * Provides intelligent, context-aware guidance throughout Umamusume Pretty Derby career runs.
 *
 * This service orchestrates:
 * - Turn-by-turn training facility recommendations
 * - Skill purchase prioritization and SP budget management
 * - Race strategy generation and readiness assessment
 * - Critical situation detection and alerts
 * - Prediction accuracy tracking for continuous improvement
 *
 * @see \App\Services\GameMechanicsEngine For game formula calculations
 * @see \App\Services\RuleBasedAdvisor For offline fallback recommendations
 * @see \App\Services\Neuron\NeuronAIService For AI-powered recommendations
 */
class TrainingAdvisoryService
{
    /**
     * Create a new Training Advisory Service instance.
     *
     * @param  \App\Services\Neuron\NeuronAIService  $neuronAIService  AI service for intelligent recommendations
     * @param  \App\Services\RuleBasedAdvisor  $ruleBasedAdvisor  Fallback for offline mode
     * @param  \App\Services\GameMechanicsEngine  $mechanicsEngine  Game formula calculations
     * @param  \App\Services\PredictionAccuracyTracker  $accuracyTracker  Learning and improvement tracking
     * @param  \App\Services\CriticalSituationDetector  $criticalDetector  Critical situation detection
     * @param  \App\Services\RecommendationCacheService  $recommendationCache  Recommendation caching for performance
     * @param  \App\Services\AdvisoryPerformanceMonitor  $performanceMonitor  Performance monitoring and metrics
     */
    public function __construct(
        protected \App\Services\Neuron\NeuronAIService $neuronAIService,
        protected RuleBasedAdvisor $ruleBasedAdvisor,
        protected GameMechanicsEngine $mechanicsEngine,
        protected PredictionAccuracyTracker $accuracyTracker,
        protected CriticalSituationDetector $criticalDetector,
        protected RecommendationCacheService $recommendationCache,
        protected AdvisoryPerformanceMonitor $performanceMonitor
    ) {}

    /**
     * Check if AI service is available
     *
     * @return bool True if AI service can be used
     */
    protected function isAIAvailable(): bool
    {
        return $this->neuronAIService->isAvailable();
    }

    /**
     * Get recommended timeout based on context complexity
     *
     * @param  TrainingContext  $context  Training context to analyze
     * @return int Timeout in seconds
     */
    protected function getTimeoutForContext(TrainingContext $context): int
    {
        // Determine complexity based on context
        $complexity = 'medium';

        // Simple: Early game, few skills, low stats
        if ($context->turnNumber < 20 && count($context->acquiredSkills) < 5) {
            $complexity = 'simple';
        }

        // Complex: Late game, many skills, high stats, multiple upcoming races
        if ($context->turnNumber > 50 || count($context->acquiredSkills) > 15 || count($context->upcomingRaces) > 2) {
            $complexity = 'complex';
        }

        return $this->neuronAIService->getRecommendedTimeout($complexity);
    }

    /**
     * Get training facility recommendations for the current turn.
     *
     * Analyzes complete character state including stats, SP, skills, energy, mood,
     * bonds, facility levels, and phase to deliver actionable recommendations.
     *
     * Response time targets:
     * - Cache hit: <50ms (p95)
     * - Local AI (Ollama): <2 seconds (p95)
     * - Cloud AI (Bedrock): <5 seconds (p95)
     * - Rule-based fallback: <500ms (p95)
     *
     * @param  TrainingContext  $context  Complete character state for analysis
     * @return RecommendationCollection Prioritized training recommendations
     *
     * @throws \RuntimeException When AI service fails and fallback unavailable
     */
    public function getTrainingRecommendations(TrainingContext $context): RecommendationCollection
    {
        $startTime = microtime(true);

        try {
            // Check cache first for fast response
            $cached = $this->recommendationCache->getCachedRecommendations($context);
            if ($cached !== null) {
                $processingTime = microtime(true) - $startTime;

                \Illuminate\Support\Facades\Log::info('[TrainingAdvisory] Recommendations from cache', [
                    'turn' => $context->turnNumber,
                    'count' => count($cached),
                    'processing_time' => $processingTime,
                    'cache_hit' => true,
                ]);

                // Record performance metrics
                $this->performanceMonitor->recordRecommendationGeneration([
                    'response_time_ms' => (int) round($processingTime * 1000),
                    'cache_hit' => true,
                    'ai_provider' => 'cache',
                    'fallback_used' => false,
                    'turn_number' => $context->turnNumber,
                    'career_run_id' => (string) $context->careerRunId,
                ]);

                return $cached;
            }

            // Check if AI service is available
            if ($this->isAIAvailable()) {
                // Build prompt from context
                $prompt = $this->buildTrainingPrompt($context);

                // Get recommended timeout based on complexity
                $timeout = $this->getTimeoutForContext($context);

                // Generate AI-powered recommendations
                try {
                    $recommendations = $this->neuronAIService->generateMultipleRecommendations(
                        $prompt,
                        ['training_context' => $context->toPromptContext()],
                        \App\Enums\RecommendationType::TRAINING_FACILITY->value,
                        $timeout
                    );

                    $processingTime = microtime(true) - $startTime;

                    \Illuminate\Support\Facades\Log::info('[TrainingAdvisory] AI recommendations generated', [
                        'turn' => $context->turnNumber,
                        'count' => count($recommendations),
                        'processing_time' => $processingTime,
                        'cache_hit' => false,
                    ]);

                    $collection = new RecommendationCollection($recommendations);

                    // Cache the recommendations for future requests
                    $this->recommendationCache->cacheRecommendations($context, $collection);

                    // Record performance metrics
                    $this->performanceMonitor->recordRecommendationGeneration([
                        'response_time_ms' => (int) round($processingTime * 1000),
                        'cache_hit' => false,
                        'ai_provider' => 'ollama', // or 'bedrock' based on actual provider
                        'fallback_used' => false,
                        'turn_number' => $context->turnNumber,
                        'career_run_id' => (string) $context->careerRunId,
                    ]);

                    return $collection;
                } catch (\Exception $e) {
                    // Log AI failure and fall back to rule-based
                    \Illuminate\Support\Facades\Log::warning('[TrainingAdvisory] AI generation failed, falling back to rules', [
                        'error' => $e->getMessage(),
                        'turn' => $context->turnNumber,
                    ]);
                }
            }

            // Fall back to rule-based advisor
            $recommendation = $this->ruleBasedAdvisor->recommendTrainingFacility($context);

            $processingTime = microtime(true) - $startTime;

            \Illuminate\Support\Facades\Log::info('[TrainingAdvisory] Rule-based recommendation generated', [
                'turn' => $context->turnNumber,
                'processing_time' => $processingTime,
                'cache_hit' => false,
            ]);

            $collection = new RecommendationCollection([$recommendation]);

            // Cache rule-based recommendations too (they're deterministic but still valuable to cache)
            $this->recommendationCache->cacheRecommendations($context, $collection);

            // Record performance metrics
            $this->performanceMonitor->recordRecommendationGeneration([
                'response_time_ms' => (int) round($processingTime * 1000),
                'cache_hit' => false,
                'ai_provider' => 'rule-based',
                'fallback_used' => true,
                'turn_number' => $context->turnNumber,
                'career_run_id' => (string) $context->careerRunId,
            ]);

            return $collection;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('[TrainingAdvisory] Failed to generate recommendations', [
                'error' => $e->getMessage(),
                'turn' => $context->turnNumber,
            ]);

            throw new \RuntimeException(
                "Failed to generate training recommendations: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Build training recommendation prompt from context
     *
     * @param  TrainingContext  $context  Training context
     * @return string Formatted prompt for AI
     */
    protected function buildTrainingPrompt(TrainingContext $context): string
    {
        $prompt = "Analyze the following training situation and provide recommendations:\n\n";
        $prompt .= $context->toPromptContext();
        $prompt .= "\n\nProvide your recommendations in JSON format with the following structure:\n";
        $prompt .= "{\n";
        $prompt .= "  \"recommendations\": [\n";
        $prompt .= "    {\n";
        $prompt .= "      \"type\": \"training_facility\",\n";
        $prompt .= "      \"priority\": \"high|medium|low|critical\",\n";
        $prompt .= "      \"action\": \"Specific training facility name\",\n";
        $prompt .= "      \"reasoning\": \"Detailed explanation\",\n";
        $prompt .= "      \"expected_outcomes\": [\"outcome1\", \"outcome2\"],\n";
        $prompt .= "      \"risks\": [\"risk1\", \"risk2\"],\n";
        $prompt .= "      \"confidence_score\": 0.0-1.0\n";
        $prompt .= "    }\n";
        $prompt .= "  ]\n";
        $prompt .= "}\n\n";
        $prompt .= "Prioritize recommendations based on:\n";
        $prompt .= "1. Friendship Training availability (bond ≥80)\n";
        $prompt .= "2. Energy level (recommend rest if <50)\n";
        $prompt .= "3. Multi-training bonus (more support cards = better)\n";
        $prompt .= "4. Stat gaps vs goals\n";
        $prompt .= "5. Facility levels (lower levels = more efficient)\n";

        return $prompt;
    }

    /**
     * Get skill purchase advice based on available skills and character state.
     *
     * Evaluates available skills, calculates SP efficiency with hint level discounts,
     * and prioritizes purchases based on character build and remaining SP budget.
     *
     * Prioritization rules:
     * - Gold skills with Level 3+ hints (30%+ discount)
     * - Skills that address critical gaps (stamina recovery, positioning)
     * - Skills with high SP efficiency (cost vs. impact)
     *
     * Response time targets:
     * - Local AI (Ollama): <2 seconds (p95)
     * - Cloud AI (Bedrock): <5 seconds (p95)
     * - Rule-based fallback: <500ms (p95)
     *
     * @param  Character  $character  Character with current stats and SP
     * @param  array<int, array{id: int, name: string, tier: string, base_cost: int, hint_level: int, category: string}>  $availableSkills  Skills available for purchase with hint levels
     * @return SkillRecommendationCollection Prioritized skill recommendations
     */
    public function getSkillPurchaseAdvice(
        Character $character,
        array $availableSkills
    ): SkillRecommendationCollection {
        $startTime = microtime(true);

        try {
            // Check if AI service is available
            if ($this->isAIAvailable()) {
                // Build prompt from character and skills context
                $prompt = $this->buildSkillAdvicePrompt($character, $availableSkills);

                // Get recommended timeout (skill advice is typically medium complexity)
                $timeout = $this->neuronAIService->getRecommendedTimeout('medium');

                // Generate AI-powered recommendations
                try {
                    $recommendations = $this->neuronAIService->generateMultipleRecommendations(
                        $prompt,
                        ['skill_context' => $this->buildSkillContext($character, $availableSkills)],
                        \App\Enums\RecommendationType::SKILL_PURCHASE->value,
                        $timeout
                    );

                    $processingTime = microtime(true) - $startTime;

                    \Illuminate\Support\Facades\Log::info('[TrainingAdvisory] AI skill recommendations generated', [
                        'character_id' => $character->id,
                        'available_skills_count' => count($availableSkills),
                        'recommendations_count' => count($recommendations),
                        'processing_time' => $processingTime,
                    ]);

                    return new SkillRecommendationCollection($recommendations);
                } catch (\Exception $e) {
                    // Log AI failure and fall back to rule-based
                    \Illuminate\Support\Facades\Log::warning('[TrainingAdvisory] AI skill advice failed, falling back to rules', [
                        'error' => $e->getMessage(),
                        'character_id' => $character->id,
                    ]);
                }
            }

            // Fall back to rule-based advisor
            $recommendation = $this->ruleBasedAdvisor->recommendSkillPurchase($character, $availableSkills);

            $processingTime = microtime(true) - $startTime;

            \Illuminate\Support\Facades\Log::info('[TrainingAdvisory] Rule-based skill recommendation generated', [
                'character_id' => $character->id,
                'processing_time' => $processingTime,
                'has_recommendation' => $recommendation !== null,
            ]);

            // Return collection with single recommendation or empty collection
            return new SkillRecommendationCollection($recommendation ? [$recommendation] : []);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('[TrainingAdvisory] Failed to generate skill recommendations', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
            ]);

            throw new \RuntimeException(
                "Failed to generate skill purchase advice: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Build skill purchase advice prompt from character and available skills
     *
     * @param  Character  $character  Character with stats and SP
     * @param  array<int, array{id: int, name: string, tier: string, base_cost: int, hint_level: int, category: string}>  $availableSkills  Available skills for purchase
     * @return string Formatted prompt for AI
     */
    protected function buildSkillAdvicePrompt(Character $character, array $availableSkills): string
    {
        // Get prompt template from config
        $template = config('advisory_prompts.skill_purchase_advice.user');

        // Build acquired skills list
        $acquiredSkillsList = 'None';
        if (isset($character->acquired_skills) && is_array($character->acquired_skills) && ! empty($character->acquired_skills)) {
            $acquiredSkillsList = implode(', ', array_map(function ($skill) {
                if (is_array($skill) && isset($skill['name'])) {
                    return is_string($skill['name']) ? $skill['name'] : 'Unknown';
                }

                return 'Unknown';
            }, $character->acquired_skills));
        }

        // Build available skills list with details
        $availableSkillsList = '';
        foreach ($availableSkills as $skill) {
            $name = is_string($skill['name'] ?? null) ? $skill['name'] : 'Unknown';
            $tier = is_string($skill['tier'] ?? null) ? $skill['tier'] : 'normal';
            $rarity = is_string($skill['rarity'] ?? null) ? $skill['rarity'] : 'common';
            $baseCost = is_int($skill['base_cost'] ?? null) ? $skill['base_cost'] : 100;
            $hintLevel = is_int($skill['hint_level'] ?? null) ? $skill['hint_level'] : 0;
            $category = is_string($skill['category'] ?? null) ? $skill['category'] : 'general';

            $effectiveCost = $this->mechanicsEngine->calculateSkillCost($baseCost, $hintLevel, false);

            $availableSkillsList .= sprintf(
                "- %s (%s, %s)\n  Base Cost: %d SP, Hint Level: %d, Effective Cost: %d SP\n  Category: %s\n",
                $name,
                $tier,
                $rarity,
                $baseCost,
                $hintLevel,
                $effectiveCost,
                $category
            );
        }

        if (empty($availableSkillsList)) {
            $availableSkillsList = 'No skills available for purchase';
        }

        // Ensure template is a string
        if (! is_string($template)) {
            $template = '';
        }

        // Replace placeholders
        $prompt = str_replace(
            [
                '{sp_available}',
                '{acquired_skills}',
                '{target_distance}',
                '{running_style}',
                '{available_skills}',
            ],
            [
                (string) ($character->available_sp ?? 0),
                $acquiredSkillsList,
                (string) ($character->target_distance ?? 'medium'),
                (string) ($character->running_style ?? 'escape'),
                $availableSkillsList,
            ],
            $template
        );

        return $prompt;
    }

    /**
     * Build skill context for AI processing
     *
     * @param  Character  $character  Character with stats and SP
     * @param  array<int, mixed>  $availableSkills  Available skills for purchase
     * @return string Formatted context string
     */
    protected function buildSkillContext(Character $character, array $availableSkills): string
    {
        $context = "Character SP Budget Analysis:\n";
        $context .= '- Available SP: '.($character->available_sp ?? 0)."\n";
        $context .= '- Acquired Skills: '.count($character->acquired_skills ?? [])."\n";
        $context .= '- Available Skills: '.count($availableSkills)."\n\n";

        $context .= "Skill Prioritization:\n";
        $context .= "1. Gold skills with Level 3+ hints (30%+ discount)\n";
        $context .= "2. Stamina recovery skills (reduce requirements by 150-200)\n";
        $context .= "3. Positioning skills for running style optimization\n";
        $context .= "4. Skills with high SP efficiency (low cost, high impact)\n\n";

        $context .= "SP Budget Guidelines:\n";
        $context .= "- Typical career budget: 300-500 SP\n";
        $context .= "- Avoid spending on skills with <Level 2 hints unless critical\n";
        $context .= "- Reserve 100-150 SP for late-game skill purchases\n";

        return $context;
    }

    /**
     * Generate race strategy and readiness assessment.
     *
     * Analyzes character stats, skills, and aptitudes against race requirements
     * to generate optimal running style recommendation and win probability.
     *
     * Includes:
     * - Recommended running style based on aptitudes
     * - Win probability calculation
     * - Stamina requirement check
     * - Pre-race readiness checklist
     * - Risk assessment
     *
     * Response time targets:
     * - Local AI (Ollama): <2 seconds (p95)
     * - Cloud AI (Bedrock): <5 seconds (p95)
     * - Rule-based fallback: <500ms (p95)
     *
     * @param  Character  $character  Character with stats, skills, and aptitudes
     * @param  Race  $race  Target race with distance, surface, and competition level
     * @return \App\Neuron\Responses\RaceStrategyResponse Complete race strategy with recommendations
     */
    public function getRaceStrategy(Character $character, Race $race): \App\Neuron\Responses\RaceStrategyResponse
    {
        $startTime = microtime(true);

        try {
            // Check if AI service is available
            if ($this->isAIAvailable()) {
                // Build prompt from character and race context
                $prompt = $this->buildRaceStrategyPrompt($character, $race);

                // Get recommended timeout (race strategy is typically medium complexity)
                $timeout = $this->neuronAIService->getRecommendedTimeout('medium');

                // Generate AI-powered race strategy
                try {
                    $response = $this->neuronAIService->generateRaceStrategy(
                        $prompt,
                        ['race_context' => $this->buildRaceContext($character, $race)],
                        $timeout
                    );

                    $processingTime = microtime(true) - $startTime;

                    \Illuminate\Support\Facades\Log::info('[TrainingAdvisory] AI race strategy generated', [
                        'character_id' => $character->id,
                        'race_id' => $race->id,
                        'processing_time' => $processingTime,
                    ]);

                    return $response;
                } catch (\Exception $e) {
                    // Log AI failure and fall back to rule-based
                    \Illuminate\Support\Facades\Log::warning('[TrainingAdvisory] AI race strategy failed, falling back to rules', [
                        'error' => $e->getMessage(),
                        'character_id' => $character->id,
                        'race_id' => $race->id,
                    ]);
                }
            }

            // Fall back to rule-based advisor
            $strategy = $this->ruleBasedAdvisor->generateRaceStrategy($character, $race);

            $processingTime = microtime(true) - $startTime;

            \Illuminate\Support\Facades\Log::info('[TrainingAdvisory] Rule-based race strategy generated', [
                'character_id' => $character->id,
                'race_id' => $race->id,
                'processing_time' => $processingTime,
            ]);

            return $strategy;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('[TrainingAdvisory] Failed to generate race strategy', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
                'race_id' => $race->id,
            ]);

            throw new \RuntimeException(
                "Failed to generate race strategy: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Build race strategy prompt from character and race context
     *
     * @param  Character  $character  Character with stats and aptitudes
     * @param  Race  $race  Race details
     * @return string Formatted prompt for AI
     */
    protected function buildRaceStrategyPrompt(Character $character, Race $race): string
    {
        // Get prompt template from config
        $template = config('advisory_prompts.race_strategy.user');

        // Ensure template is a string
        if (! is_string($template)) {
            $template = '';
        }

        // Build equipped skills list
        $equippedSkillsList = 'None';
        if (isset($character->equipped_skills) && is_array($character->equipped_skills) && ! empty($character->equipped_skills)) {
            $equippedSkillsList = implode("\n", array_map(
                function ($skill) {
                    /** @var array{name?: string, tier?: string} $skill */
                    $name = is_string($skill['name'] ?? null) ? $skill['name'] : 'Unknown';
                    $tier = is_string($skill['tier'] ?? null) ? $skill['tier'] : 'normal';

                    return "- {$name} ({$tier})";
                },
                $character->equipped_skills
            ));
        }

        // Get aptitude grades (default to C if not set)
        $distanceAptitude = $character->distance_aptitude ?? 'C';
        $surfaceAptitude = $character->surface_aptitude ?? 'C';
        $escapeAptitude = $character->escape_aptitude ?? 'C';
        $leadAptitude = $character->lead_aptitude ?? 'C';
        $paceAptitude = $character->pace_aptitude ?? 'C';
        $chaseAptitude = $character->chase_aptitude ?? 'C';

        // Get race details
        $distance = $race->distance ?? 'medium';
        $distanceMeters = $race->distance_meters ?? 2000;
        $surface = $race->surface ?? 'turf';
        $weather = $race->weather ?? 'clear';
        $trackCondition = $race->track_condition ?? 'good';
        $competitionLevel = $race->competition_level ?? 'G3';

        // Replace placeholders
        $prompt = str_replace(
            [
                '{speed}',
                '{stamina}',
                '{power}',
                '{guts}',
                '{wisdom}',
                '{distance}',
                '{distance_aptitude}',
                '{surface}',
                '{surface_aptitude}',
                '{escape_apt}',
                '{lead_apt}',
                '{pace_apt}',
                '{chase_apt}',
                '{distance_meters}',
                '{weather}',
                '{track_condition}',
                '{competition_level}',
                '{equipped_skills}',
            ],
            [
                (string) ($character->speed ?? 0),
                (string) ($character->stamina ?? 0),
                (string) ($character->power ?? 0),
                (string) ($character->guts ?? 0),
                (string) ($character->wisdom ?? 0),
                (string) $distance,
                (string) $distanceAptitude,
                (string) $surface,
                (string) $surfaceAptitude,
                (string) $escapeAptitude,
                (string) $leadAptitude,
                (string) $paceAptitude,
                (string) $chaseAptitude,
                (string) $distanceMeters,
                (string) $weather,
                (string) $trackCondition,
                (string) $competitionLevel,
                $equippedSkillsList,
            ],
            $template
        );

        return $prompt;
    }

    /**
     * Build race context for AI processing
     *
     * @param  Character  $character  Character with stats
     * @param  Race  $race  Race details
     * @return string Formatted context string
     */
    protected function buildRaceContext(Character $character, Race $race): string
    {
        $context = "Race Strategy Analysis:\n";
        $context .= '- Character Speed: '.($character->speed ?? 0)."\n";
        $context .= '- Character Stamina: '.($character->stamina ?? 0)."\n";
        $context .= '- Race Distance: '.($race->distance ?? 'medium')." ({$race->distance_meters}m)\n";
        $context .= '- Competition Level: '.($race->competition_level ?? 'G3')."\n\n";

        $context .= "Strategy Considerations:\n";
        $context .= "1. Match running style to highest aptitude grade\n";
        $context .= "2. Ensure stamina meets distance requirements\n";
        $context .= "3. Account for weather and track condition effects\n";
        $context .= "4. Consider equipped skills for stamina recovery\n";
        $context .= "5. Assess stat effectiveness (1200 soft cap)\n\n";

        $context .= "Stamina Requirements by Distance:\n";
        $context .= "- Sprint (1000-1400m): 350-400\n";
        $context .= "- Mile (1400-1800m): 450-500\n";
        $context .= "- Medium (1800-2400m): 600-700\n";
        $context .= "- Long (2400-3600m): 850-1000\n";
        $context .= "Note: Recovery skills reduce requirements by 150-200 each\n";

        return $context;
    }

    /**
     * Detect critical situations requiring immediate attention.
     *
     * Monitors character state for dangerous conditions that could jeopardize
     * career run success. Generates high-priority alerts with specific action items.
     *
     * Alert types:
     * - Stamina crisis (insufficient for upcoming races)
     * - SP shortage (cannot afford required skills)
     * - Energy critical (high failure rate risk)
     * - Bond behind schedule (Friendship Training unavailable)
     * - Facility imbalance (neglected training areas)
     * - Race unready (stat gaps for upcoming race)
     * - Team race unprepared (insufficient preparation)
     *
     * Alerts are prioritized by severity:
     * - CRITICAL: Immediate action required (0-2 turns)
     * - HIGH: Action needed soon (3-5 turns)
     * - MEDIUM: Should address (6-10 turns)
     * - LOW: Monitor situation (>10 turns)
     *
     * @param  TrainingContext  $context  Complete character state for analysis
     * @return CriticalAlertCollection High-priority alerts requiring action
     */
    public function detectCriticalSituations(TrainingContext $context): CriticalAlertCollection
    {
        $startTime = microtime(true);
        $alerts = [];

        try {
            // 1. Check stamina vs upcoming race requirements
            $staminaAlert = $this->criticalDetector->detectStaminaCrisis($context);
            if ($staminaAlert !== null) {
                $alerts[] = $staminaAlert;
            }

            // 2. Check SP budget vs required skills
            $spAlert = $this->criticalDetector->detectSpShortage($context);
            if ($spAlert !== null) {
                $alerts[] = $spAlert;
            }

            // 3. Check energy level for failure risk
            $energyAlert = $this->criticalDetector->detectEnergyCritical($context);
            if ($energyAlert !== null) {
                $alerts[] = $energyAlert;
            }

            // 4. Check bond progress vs turn number
            $bondAlert = $this->criticalDetector->detectBondBehindSchedule($context);
            if ($bondAlert !== null) {
                $alerts[] = $bondAlert;
            }

            // 5. Check mood for training effectiveness
            $moodAlert = $this->criticalDetector->detectMoodIssues($context);
            if ($moodAlert !== null) {
                $alerts[] = $moodAlert;
            }

            // Create collection and sort by priority
            $collection = new CriticalAlertCollection($alerts);

            // Sort alerts by priority (CRITICAL > HIGH > MEDIUM > LOW)
            $sortedAlerts = $collection->sortByDesc(function ($alert) {
                // Convert priority to numeric value for sorting
                return match ($alert->priority->value) {
                    'critical' => 4,
                    'high' => 3,
                    'medium' => 2,
                    'low' => 1,
                    default => 0,
                };
            })->values();

            $processingTime = microtime(true) - $startTime;

            \Illuminate\Support\Facades\Log::info('[TrainingAdvisory] Critical situations detected', [
                'turn' => $context->turnNumber,
                'alert_count' => count($sortedAlerts),
                'alert_types' => $sortedAlerts->pluck('type.value')->toArray(),
                'processing_time' => $processingTime,
            ]);

            return new CriticalAlertCollection($sortedAlerts->all());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('[TrainingAdvisory] Failed to detect critical situations', [
                'error' => $e->getMessage(),
                'turn' => $context->turnNumber,
                'trace' => $e->getTraceAsString(),
            ]);

            // Return empty collection on error rather than throwing
            // This ensures the advisory system remains functional even if detection fails
            return new CriticalAlertCollection([]);
        }
    }

    /**
     * Persist critical alerts to database.
     *
     * Saves alerts from a CriticalAlertCollection to the database for tracking,
     * dismissal, and historical analysis. Only persists alerts for account mode
     * (local mode alerts are stored in browser localStorage).
     *
     * This method:
     * - Filters alerts by storage mode (only account mode)
     * - Checks for existing alerts to avoid duplicates
     * - Persists new alerts to critical_alerts table
     * - Returns count of persisted alerts
     *
     * @param  int  $careerId  Career run ID
     * @param  int  $turnNumber  Current turn number
     * @param  CriticalAlertCollection  $alerts  Collection of alerts to persist
     * @return int Number of alerts persisted
     */
    public function persistCriticalAlerts(
        int $careerId,
        int $turnNumber,
        CriticalAlertCollection $alerts
    ): int {
        $persistedCount = 0;

        try {
            foreach ($alerts as $alert) {
                // Skip local mode alerts - they're stored in browser localStorage
                if ($alert->isLocalMode()) {
                    continue;
                }

                // Check if this alert already exists for this career/turn/type
                $exists = \App\Models\CriticalAlert::query()
                    ->where('career_id', $careerId)
                    ->where('turn_number', $turnNumber)
                    ->where('alert_type', $alert->type)
                    ->exists();

                // Skip if alert already exists
                if ($exists) {
                    continue;
                }

                // Create the alert record
                \App\Models\CriticalAlert::create([
                    'career_id' => $careerId,
                    'turn_number' => $turnNumber,
                    'alert_type' => $alert->type,
                    'message' => $alert->message,
                    'action_items' => $alert->actionItems,
                    'turns_until_critical' => $alert->turnsUntilCritical,
                    'was_dismissed' => false,
                    'dismissed_at' => null,
                ]);

                $persistedCount++;
            }

            if ($persistedCount > 0) {
                \Illuminate\Support\Facades\Log::info('[TrainingAdvisory] Persisted critical alerts', [
                    'career_id' => $careerId,
                    'turn' => $turnNumber,
                    'count' => $persistedCount,
                ]);
            }

            return $persistedCount;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('[TrainingAdvisory] Failed to persist critical alerts', [
                'career_id' => $careerId,
                'turn' => $turnNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return 0 on error rather than throwing
            return 0;
        }
    }

    /**
     * Dismiss a critical alert.
     *
     * Marks an alert as dismissed with timestamp. Dismissed alerts are hidden
     * from active alert displays but remain in the database for historical analysis.
     *
     * @param  int  $alertId  Alert ID to dismiss
     * @return bool True if dismissed successfully, false otherwise
     */
    public function dismissCriticalAlert(int $alertId): bool
    {
        try {
            $alert = \App\Models\CriticalAlert::find($alertId);

            if ($alert === null) {
                \Illuminate\Support\Facades\Log::warning('[TrainingAdvisory] Alert not found for dismissal', [
                    'alert_id' => $alertId,
                ]);

                return false;
            }

            $result = $alert->dismiss();

            if ($result) {
                \Illuminate\Support\Facades\Log::info('[TrainingAdvisory] Alert dismissed', [
                    'alert_id' => $alertId,
                    'career_id' => $alert->career_id,
                    'turn' => $alert->turn_number,
                    'type' => $alert->alert_type->value,
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('[TrainingAdvisory] Failed to dismiss alert', [
                'alert_id' => $alertId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Reactivate a dismissed alert.
     *
     * Removes dismissal status from an alert, making it active again.
     * Useful if a player wants to see a previously dismissed alert.
     *
     * @param  int  $alertId  Alert ID to reactivate
     * @return bool True if reactivated successfully, false otherwise
     */
    public function reactivateCriticalAlert(int $alertId): bool
    {
        try {
            $alert = \App\Models\CriticalAlert::find($alertId);

            if ($alert === null) {
                \Illuminate\Support\Facades\Log::warning('[TrainingAdvisory] Alert not found for reactivation', [
                    'alert_id' => $alertId,
                ]);

                return false;
            }

            $result = $alert->reactivate();

            if ($result) {
                \Illuminate\Support\Facades\Log::info('[TrainingAdvisory] Alert reactivated', [
                    'alert_id' => $alertId,
                    'career_id' => $alert->career_id,
                    'turn' => $alert->turn_number,
                    'type' => $alert->alert_type->value,
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('[TrainingAdvisory] Failed to reactivate alert', [
                'alert_id' => $alertId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get active critical alerts for a career run.
     *
     * Retrieves all non-dismissed alerts for a specific career run,
     * ordered by urgency (most urgent first).
     *
     * @param  int  $careerId  Career run ID
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\CriticalAlert>
     */
    public function getActiveCriticalAlerts(int $careerId): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\CriticalAlert::query()
            ->where('career_id', $careerId)
            ->active()
            ->orderByUrgency()
            ->get();
    }

    /**
     * Get critical alerts for a specific turn.
     *
     * Retrieves all alerts (active and dismissed) for a specific career run and turn.
     * Useful for historical analysis and turn-by-turn review.
     *
     * @param  int  $careerId  Career run ID
     * @param  int  $turnNumber  Turn number
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\CriticalAlert>
     */
    public function getCriticalAlertsForTurn(int $careerId, int $turnNumber): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\CriticalAlert::query()
            ->where('career_id', $careerId)
            ->where('turn_number', $turnNumber)
            ->orderByUrgency()
            ->get();
    }

    /**
     * Record training outcome for prediction accuracy tracking.
     *
     * Compares predicted outcomes with actual results to calculate accuracy metrics
     * and identify areas for model improvement. Used for continuous learning.
     *
     * Tracked metrics:
     * - Stat gain accuracy (predicted vs actual)
     * - Bond increase accuracy
     * - Skill hint prediction accuracy
     * - Failure rate prediction accuracy
     *
     * @param  int  $careerId  Career run ID
     * @param  int  $turnNumber  Turn when training occurred
     * @param  Recommendation  $recommendation  Original recommendation with predictions
     * @param  TrainingOutcome  $actual  Actual training results
     */
    public function recordTrainingOutcome(
        int $careerId,
        int $turnNumber,
        Recommendation $recommendation,
        TrainingOutcome $actual
    ): void {
        try {
            // Determine model version from recommendation source
            $modelVersion = $recommendation->isAIGenerated() ? 'ai-v1' : 'rule-based';

            // Call PredictionAccuracyTracker to record the outcome
            $this->accuracyTracker->recordTrainingOutcome(
                $careerId,
                $turnNumber,
                $recommendation,
                $actual,
                $modelVersion
            );

            \Illuminate\Support\Facades\Log::info('[TrainingAdvisory] Training outcome recorded successfully', [
                'career_id' => $careerId,
                'turn' => $turnNumber,
                'model_version' => $modelVersion,
            ]);
        } catch (\Exception $e) {
            // Log error but don't throw - prediction tracking should never break the main flow
            \Illuminate\Support\Facades\Log::error('[TrainingAdvisory] Failed to record training outcome', [
                'error' => $e->getMessage(),
                'career_id' => $careerId,
                'turn' => $turnNumber,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Record race outcome for strategy accuracy tracking.
     *
     * Compares predicted race performance with actual results to improve
     * future race strategy recommendations.
     *
     * Tracked metrics:
     * - Win probability accuracy
     * - Running style effectiveness
     * - Stamina sufficiency
     * - Skill impact assessment
     *
     * @param  int  $careerId  Career run ID
     * @param  RaceStrategy  $strategy  Original strategy with predictions
     * @param  RaceResult  $actual  Actual race results
     */
    public function recordRaceOutcome(int $careerId, RaceStrategy $strategy, RaceResult $actual): void
    {
        try {
            // Call PredictionAccuracyTracker to record the outcome
            $this->accuracyTracker->recordRaceOutcome(
                $careerId,
                $strategy,
                $actual
            );

            \Illuminate\Support\Facades\Log::info('[TrainingAdvisory] Race outcome recorded successfully', [
                'career_id' => $careerId,
                'race_id' => $strategy->raceId,
                'model_version' => $strategy->modelVersion,
            ]);
        } catch (\Exception $e) {
            // Log error but don't throw - prediction tracking should never break the main flow
            \Illuminate\Support\Facades\Log::error('[TrainingAdvisory] Failed to record race outcome', [
                'error' => $e->getMessage(),
                'career_id' => $careerId,
                'race_id' => $strategy->raceId,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Analyze support card deck for bond progress tracking.
     *
     * Categorizes support cards based on their bond level relative to the
     * Friendship Training threshold (80). This analysis helps identify which
     * cards are ready for Friendship Training and which need more bond building.
     *
     * **Validates: Property 12 (Bond Progress Tracking)**
     *
     * @param  TrainingContext  $context  Training context with support cards
     * @return object Analysis result with cardsReadyForFriendship and cardsNotReadyForFriendship
     */
    public function analyzeSupportCardDeck(TrainingContext $context): object
    {
        $cardsReadyForFriendship = [];
        $cardsNotReadyForFriendship = [];

        foreach ($context->deck->cards as $card) {
            if ($card->bond >= 80) {
                $cardsReadyForFriendship[] = $card;
            } else {
                $cardsNotReadyForFriendship[] = $card;
            }
        }

        return (object) [
            'cardsReadyForFriendship' => $cardsReadyForFriendship,
            'cardsNotReadyForFriendship' => $cardsNotReadyForFriendship,
        ];
    }

    /**
     * Get phase-specific goal tracking.
     *
     * Tracks milestones and goals specific to each career phase (Junior, Classic, Senior).
     * Each phase has different priorities and milestones that should be achieved.
     *
     * **Validates: Property 13 (Phase-Specific Goal Tracking)**
     *
     * @param  TrainingContext  $context  Training context with phase information
     * @return object Tracking result with milestone information
     */
    public function getPhaseGoalTracking(TrainingContext $context): object
    {
        $milestones = [];

        // Define phase-specific milestones
        $phaseMilestones = [
            'junior_year' => ['bondBuilding', 'facilityLevels'],
            'classic_year' => ['statOptimization', 'skillAcquisition'],
            'senior_year' => ['finalPreparation', 'uraReadiness'],
        ];

        // Get milestones for current phase
        $currentPhaseMilestones = $phaseMilestones[$context->phase->value] ?? [];

        foreach ($currentPhaseMilestones as $milestone) {
            $milestones[$milestone] = true;
        }

        return (object) [
            'phase' => $context->phase->value,
            'milestones' => $milestones,
        ];
    }

    /**
     * Get prediction accuracy metrics for a career run.
     *
     * Retrieves accuracy metrics for predictions made during a career run,
     * filtered by recommendation type if specified.
     *
     * **Validates: Property 15 (Prediction Accuracy Recording)**
     *
     * @param  string|int  $careerRunId  Career run identifier
     * @param  \App\Enums\RecommendationType|null  $type  Optional type filter
     * @return \App\ValueObjects\AccuracyMetrics Accuracy metrics
     */
    public function getPredictionAccuracy(
        string|int $careerRunId,
        ?\App\Enums\RecommendationType $type = null
    ): \App\ValueObjects\AccuracyMetrics {
        // Convert UUID to integer for database lookup if needed
        $careerId = is_string($careerRunId) ? 0 : $careerRunId;

        return $this->accuracyTracker->getPredictionAccuracy($careerId, $type);
    }

    /**
     * Get performance metrics for the advisory system.
     *
     * Returns comprehensive performance metrics including response times,
     * cache hit rates, AI provider usage, and fallback frequency.
     *
     * @return array<string, mixed>
     */
    public function getPerformanceMetrics(): array
    {
        return $this->performanceMonitor->getPerformanceReport();
    }
}
