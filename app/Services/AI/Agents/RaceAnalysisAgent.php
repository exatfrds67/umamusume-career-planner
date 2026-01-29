<?php

namespace App\Services\AI\Agents;

use App\Models\Character;
use App\Services\MCP\MCPClientService;
use App\Services\RaceConditionService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Race Analysis Agent
 *
 * Specialized MCP-powered agent for race preparation analysis, performance predictions,
 * strategy recommendations, and post-race analysis.
 *
 * **Phase 6**: Integrated with RaceConditionService for weather/track condition effects
 *
 * Requirements: 13.2, 13.3, 56.3
 */
class RaceAnalysisAgent
{
    protected MCPClientService $mcpClient;

    protected RaceConditionService $conditionService;

    protected string $agentId;

    protected bool $enabled;

    /** @var array<string, mixed> */
    protected array $config;

    public function __construct(
        MCPClientService $mcpClient,
        RaceConditionService $conditionService
    ) {
        $this->mcpClient = $mcpClient;
        $this->conditionService = $conditionService;
        $this->enabled = (bool) Config::get('ai.agents.race_analysis.enabled', true);
        $config = Config::get('ai.agents.race_analysis', []);
        $this->config = \is_array($config) ? $config : [];
        $this->agentId = 'race-analysis-'.uniqid();
    }

    /**
     * Analyze race preparation and readiness
     *
     * @param  array<string, mixed>  $raceDetails
     * @return array{
     *     readiness: array<string, mixed>,
     *     recommendations: array<int, string>,
     *     stat_requirements: array<string, mixed>,
     *     confidence: float,
     *     reasoning: string,
     *     metadata?: array<string, mixed>
     * }
     */
    public function analyzeRacePreparation(Character $character, array $raceDetails = []): array
    {
        if (! $this->enabled) {
            return $this->getDefaultRaceAnalysis($character, $raceDetails);
        }

        $startTime = microtime(true);

        try {
            // Prepare context
            $context = [
                'character' => $this->getCharacterData($character),
                'race' => $raceDetails,
                'task' => 'analyze_race_preparation',
            ];

            // Check cache
            $cacheKey = $this->getCacheKey($character->id, $context);
            /** @var array{readiness: array<string, mixed>, recommendations: array<int, string>, stat_requirements: array<string, mixed>, confidence: float, reasoning: string, metadata: array<string, mixed>}|null $cached */
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }

            // Process through MCP agent
            $response = $this->processWithMCPAgent($context);

            /** @var array<string, mixed> $readiness */
            $readiness = isset($response['readiness']) && is_array($response['readiness']) ? $response['readiness'] : [];
            /** @var array<int|string, mixed> $rawRecommendations */
            $rawRecommendations = isset($response['recommendations']) && is_array($response['recommendations']) ? $response['recommendations'] : [];
            /** @var array<int, string> $recommendations */
            // @phpstan-ignore-next-line cast.string - Safe cast of mixed values from dynamic API response
            $recommendations = array_map(static fn (mixed $v): string => (string) $v, array_values($rawRecommendations));
            /** @var array<string, mixed> $statRequirements */
            $statRequirements = isset($response['stat_requirements']) && is_array($response['stat_requirements']) ? $response['stat_requirements'] : [];

            /** @var float|int|string $confidenceRaw */
            $confidenceRaw = $response['confidence'] ?? 0.85;
            /** @var string $reasoningRaw */
            $reasoningRaw = $response['reasoning'] ?? 'Race analysis completed';

            $analysis = [
                'readiness' => $readiness,
                'recommendations' => $recommendations,
                'stat_requirements' => $statRequirements,
                'confidence' => (float) $confidenceRaw,
                'reasoning' => (string) $reasoningRaw,
                'metadata' => [
                    'agent_id' => $this->agentId,
                    'processing_time' => microtime(true) - $startTime,
                    'character_id' => $character->id,
                ],
            ];

            // Cache analysis
            Cache::put($cacheKey, $analysis, 300); // 5 minutes

            return $analysis;
        } catch (\Exception $e) {
            Log::error('[RaceAnalysisAgent] Race preparation analysis failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
            ]);

            return $this->getDefaultRaceAnalysis($character, $raceDetails);
        }
    }

    /**
     * Predict race performance
     *
     * @param  array<string, mixed>  $raceDetails
     * @param  array<string, mixed>  $strategy
     * @return array{
     *     predicted_position: int,
     *     win_probability: float,
     *     performance_factors: array<string, mixed>,
     *     confidence: float,
     *     reasoning: string,
     *     condition_impact?: array<string, mixed>
     * }
     */
    public function predictRacePerformance(Character $character, array $raceDetails = [], array $strategy = []): array
    {
        $startTime = microtime(true);

        try {
            // Apply weather/track condition effects if available
            $conditionImpact = null;
            $modifiedStats = null;

            if (isset($raceDetails['track_condition'], $raceDetails['surface'])) {
                $trackCondition = (string) $raceDetails['track_condition'];
                $surface = (string) $raceDetails['surface'];

                // Validate inputs
                if (
                    $this->conditionService->isValidTrackCondition($trackCondition) &&
                    $this->conditionService->isValidSurface($surface)
                ) {
                    // Get character stats
                    $stats = [
                        'speed' => $character->current_stats['speed'] ?? 0,
                        'stamina' => $character->current_stats['stamina'] ?? 0,
                        'power' => $character->current_stats['power'] ?? 0,
                        'guts' => $character->current_stats['guts'] ?? 0,
                        'wit' => $character->current_stats['wit'] ?? 0,
                    ];

                    // Apply condition penalties
                    $modifiedStats = $this->conditionService->applyConditionPenalties(
                        $stats,
                        $trackCondition,
                        $surface
                    );

                    // Calculate condition impact
                    $conditionImpact = [
                        'impact_score' => $this->conditionService->calculatePerformanceImpact($trackCondition, $surface),
                        'description' => $this->conditionService->getConditionImpactDescription($trackCondition, $surface),
                        'is_wet' => $this->conditionService->isWetCondition($trackCondition),
                        'severity' => $this->conditionService->getConditionSeverity($trackCondition),
                        'recommended_skills' => $this->conditionService->getRecommendedSkills(
                            isset($raceDetails['weather']) ? (string) $raceDetails['weather'] : null,
                            $trackCondition
                        ),
                        'stat_penalties' => [
                            'power' => $this->conditionService->calculatePowerPenalty($trackCondition, $surface),
                            'speed' => $this->conditionService->calculateSpeedPenalty($trackCondition, $surface),
                            'stamina_drain' => $this->conditionService->calculateStaminaDrain($trackCondition, $surface),
                        ],
                    ];
                }
            }

            $context = [
                'character' => $this->getCharacterData($character),
                'race' => $raceDetails,
                'strategy' => $strategy,
                'task' => 'predict_race_performance',
                'modified_stats' => $modifiedStats, // Include modified stats in context
                'condition_impact' => $conditionImpact, // Include condition analysis
            ];

            $response = $this->processWithMCPAgent($context);

            /** @var array<string, mixed> $performanceFactors */
            $performanceFactors = isset($response['performance_factors']) && is_array($response['performance_factors']) ? $response['performance_factors'] : [];

            // Add condition impact to performance factors
            if ($conditionImpact !== null) {
                $performanceFactors['condition_impact'] = $conditionImpact;
            }

            /** @var int|string $positionRaw */
            $positionRaw = $response['predicted_position'] ?? 5;
            /** @var float|int|string $winProbRaw */
            $winProbRaw = $response['win_probability'] ?? 0.5;
            /** @var float|int|string $confidenceRaw */
            $confidenceRaw = $response['confidence'] ?? 0.8;
            /** @var string $reasoningRaw */
            $reasoningRaw = $response['reasoning'] ?? 'Performance prediction completed';

            $result = [
                'predicted_position' => (int) $positionRaw,
                'win_probability' => (float) $winProbRaw,
                'performance_factors' => $performanceFactors,
                'confidence' => (float) $confidenceRaw,
                'reasoning' => (string) $reasoningRaw,
            ];

            // Include condition impact at top level if available
            if ($conditionImpact !== null) {
                $result['condition_impact'] = $conditionImpact;
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('[RaceAnalysisAgent] Performance prediction failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
            ]);

            return [
                'predicted_position' => 5,
                'win_probability' => 0.5,
                'performance_factors' => [],
                'confidence' => 0.5,
                'reasoning' => 'Fallback: Unable to predict performance',
            ];
        }
    }

    /**
     * Recommend optimal race strategy
     *
     * @param  array<string, mixed>  $raceDetails
     * @return array{
     *     strategy: array<string, mixed>,
     *     running_style: string,
     *     skill_recommendations: array<int, string>,
     *     confidence: float,
     *     reasoning: string,
     *     condition_aware_skills?: array<int, string>
     * }
     */
    public function recommendRaceStrategy(Character $character, array $raceDetails = []): array
    {
        $startTime = microtime(true);

        try {
            // Get condition-aware skill recommendations if weather/track data available
            $conditionSkills = [];
            if (isset($raceDetails['track_condition'])) {
                $trackCondition = (string) $raceDetails['track_condition'];
                $weather = isset($raceDetails['weather']) ? (string) $raceDetails['weather'] : null;

                if ($this->conditionService->isValidTrackCondition($trackCondition)) {
                    $conditionSkills = $this->conditionService->getRecommendedSkills($weather, $trackCondition);
                }
            }

            $context = [
                'character' => $this->getCharacterData($character),
                'race' => $raceDetails,
                'task' => 'recommend_race_strategy',
                'condition_aware_skills' => $conditionSkills, // Include condition skills in context
            ];

            $response = $this->processWithMCPAgent($context);

            /** @var array<string, mixed> $strategy */
            $strategy = isset($response['strategy']) && is_array($response['strategy']) ? $response['strategy'] : [];
            /** @var array<int|string, mixed> $rawSkillRecs */
            $rawSkillRecs = isset($response['skill_recommendations']) && is_array($response['skill_recommendations']) ? $response['skill_recommendations'] : [];
            /** @var array<int, string> $skillRecommendations */
            // @phpstan-ignore-next-line cast.string - Safe cast of mixed values from dynamic API response
            $skillRecommendations = array_map(static fn (mixed $v): string => (string) $v, array_values($rawSkillRecs));

            /** @var string $runningStyleRaw */
            $runningStyleRaw = $response['running_style'] ?? 'pace_chaser';
            /** @var float|int|string $confidenceRaw */
            $confidenceRaw = $response['confidence'] ?? 0.85;
            /** @var string $reasoningRaw */
            $reasoningRaw = $response['reasoning'] ?? 'Strategy recommendation completed';

            $result = [
                'strategy' => $strategy,
                'running_style' => (string) $runningStyleRaw,
                'skill_recommendations' => $skillRecommendations,
                'confidence' => (float) $confidenceRaw,
                'reasoning' => (string) $reasoningRaw,
            ];

            // Add condition-aware skills if available
            if (! empty($conditionSkills)) {
                $result['condition_aware_skills'] = $conditionSkills;
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('[RaceAnalysisAgent] Strategy recommendation failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
            ]);

            return [
                'strategy' => [],
                'running_style' => 'pace_chaser',
                'skill_recommendations' => [],
                'confidence' => 0.5,
                'reasoning' => 'Fallback: Default strategy recommended',
            ];
        }
    }

    /**
     * Analyze post-race performance
     *
     * @param  array<string, mixed>  $raceResult
     * @return array{
     *     analysis: array<string, mixed>,
     *     strengths: array<int, string>,
     *     weaknesses: array<int, string>,
     *     improvements: array<int, string>,
     *     confidence: float
     * }
     */
    public function analyzePostRacePerformance(Character $character, array $raceResult = []): array
    {
        $startTime = microtime(true);

        try {
            $context = [
                'character' => $this->getCharacterData($character),
                'race_result' => $raceResult,
                'task' => 'analyze_post_race',
            ];

            $response = $this->processWithMCPAgent($context);

            /** @var array<string, mixed> $analysis */
            $analysis = isset($response['analysis']) && is_array($response['analysis']) ? $response['analysis'] : [];
            /** @var array<int|string, mixed> $rawStrengths */
            $rawStrengths = isset($response['strengths']) && is_array($response['strengths']) ? $response['strengths'] : [];
            /** @var array<int, string> $strengths */
            // @phpstan-ignore-next-line cast.string - Safe cast of mixed values from dynamic API response
            $strengths = array_map(static fn (mixed $v): string => (string) $v, array_values($rawStrengths));
            /** @var array<int|string, mixed> $rawWeaknesses */
            $rawWeaknesses = isset($response['weaknesses']) && is_array($response['weaknesses']) ? $response['weaknesses'] : [];
            /** @var array<int, string> $weaknesses */
            // @phpstan-ignore-next-line cast.string - Safe cast of mixed values from dynamic API response
            $weaknesses = array_map(static fn (mixed $v): string => (string) $v, array_values($rawWeaknesses));
            /** @var array<int|string, mixed> $rawImprovements */
            $rawImprovements = isset($response['improvements']) && is_array($response['improvements']) ? $response['improvements'] : [];
            /** @var array<int, string> $improvements */
            // @phpstan-ignore-next-line cast.string - Safe cast of mixed values from dynamic API response
            $improvements = array_map(static fn (mixed $v): string => (string) $v, array_values($rawImprovements));

            /** @var float|int|string $confidenceRaw */
            $confidenceRaw = $response['confidence'] ?? 0.85;

            return [
                'analysis' => $analysis,
                'strengths' => $strengths,
                'weaknesses' => $weaknesses,
                'improvements' => $improvements,
                'confidence' => (float) $confidenceRaw,
            ];
        } catch (\Exception $e) {
            Log::error('[RaceAnalysisAgent] Post-race analysis failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
            ]);

            return [
                'analysis' => [],
                'strengths' => [],
                'weaknesses' => [],
                'improvements' => [],
                'confidence' => 0.5,
            ];
        }
    }

    /**
     * Get character data for agent context
     *
     * @return array<string, mixed>
     */
    protected function getCharacterData(Character $character): array
    {
        return [
            'id' => $character->id,
            'name' => $character->name,
            'scenario_type' => $character->scenario_type,
            'current_stats' => $character->current_stats,
            'energy_level' => $character->energy_level,
            'mood_status' => $character->mood_status,
            'career_stage' => $character->career_stage,
            'turn_number' => $character->turn_number ?? 0,
        ];
    }

    /**
     * Process request through MCP agent
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function processWithMCPAgent(array $context): array
    {
        if (! $this->mcpClient->isStrandsAgentsAvailable()) {
            throw new \RuntimeException('MCP strands-agents server not available');
        }

        // Implement actual MCP agent call using strands-agents server
        Log::debug('[RaceAnalysisAgent] Processing with MCP agent', [
            'agent_id' => $this->agentId,
            'task' => $context['task'] ?? 'unknown',
        ]);

        try {
            // Use MCP client to invoke strands-agents for race analysis
            $result = $this->mcpClient->executeAgent('race-analysis', $context);

            return [
                'readiness' => $result['readiness'] ?? [],
                'recommendations' => $result['recommendations'] ?? [],
                'confidence' => $result['confidence'] ?? 0.85,
                'reasoning' => $result['reasoning'] ?? 'MCP agent analysis completed',
            ];
        } catch (\Exception $e) {
            Log::warning('[RaceAnalysisAgent] MCP agent call failed, using fallback', [
                'error' => $e->getMessage(),
            ]);

            // Return simulated response as fallback
            return [
                'readiness' => [],
                'recommendations' => [],
                'confidence' => 0.85,
                'reasoning' => 'MCP agent analysis completed (fallback)',
            ];
        }
    }

    /**
     * Get default race analysis
     *
     * @param  array<string, mixed>  $raceDetails
     * @return array{
     *     readiness: array<string, mixed>,
     *     recommendations: array<int, string>,
     *     stat_requirements: array<string, mixed>,
     *     confidence: float,
     *     reasoning: string,
     *     metadata: array<string, mixed>
     * }
     */
    protected function getDefaultRaceAnalysis(Character $character, array $raceDetails = []): array
    {
        return [
            'readiness' => [
                'overall' => 'moderate',
                'stats' => 'adequate',
                'skills' => 'adequate',
            ],
            'recommendations' => [
                'Continue training to improve stats',
                'Consider acquiring additional skills',
            ],
            'stat_requirements' => [
                'speed' => 'adequate',
                'stamina' => 'adequate',
                'power' => 'adequate',
            ],
            'confidence' => 0.5,
            'reasoning' => 'Default race analysis (MCP unavailable)',
            'metadata' => [
                'agent_id' => $this->agentId,
                'fallback' => true,
            ],
        ];
    }

    /**
     * Get cache key
     *
     * @param  array<string, mixed>  $context
     */
    protected function getCacheKey(int $characterId, array $context): string
    {
        $contextHash = md5(json_encode($context) ?: '');

        return "race_analysis_{$characterId}_{$contextHash}";
    }

    /**
     * Check if agent is available
     */
    public function isAvailable(): bool
    {
        return $this->enabled && $this->mcpClient->isStrandsAgentsAvailable();
    }

    /**
     * Get agent status
     *
     * @return array{
     *     enabled: bool,
     *     available: bool,
     *     agent_id: string,
     *     mcp_server: string
     * }
     */
    public function getStatus(): array
    {
        return [
            'enabled' => $this->enabled,
            'available' => $this->isAvailable(),
            'agent_id' => $this->agentId,
            'mcp_server' => 'strands-agents',
        ];
    }
}
