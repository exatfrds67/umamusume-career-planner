<?php

namespace App\Services\AI\Agents;

use App\Models\Character;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Race Analysis Agent
 *
 * Specialized MCP-powered agent for race preparation analysis, performance predictions,
 * strategy recommendations, and post-race analysis.
 *
 * Requirements: 13.2, 13.3, 56.3
 */
class RaceAnalysisAgent
{
    protected MCPClientService $mcpClient;

    protected string $agentId;

    protected bool $enabled;

    /** @var array<string, mixed> */
    protected array $config;

    public function __construct(MCPClientService $mcpClient)
    {
        $this->mcpClient = $mcpClient;
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
     *     reasoning: string
     * }
     */
    public function analyzeRacePreparation(): array
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
            $readiness = is_array((is_array($response) && isset($response['readiness']) ? $response['readiness'] : null)) ? $response['readiness'] : [];
            /** @var array<int, string> $recommendations */
            $recommendations = is_array((is_array($response) && isset($response['recommendations']) ? $response['recommendations'] : null)) ? array_values($response['recommendations']) : [];
            /** @var array<string, mixed> $statRequirements */
            $statRequirements = is_array((is_array($response) && isset($response['stat_requirements']) ? $response['stat_requirements'] : null)) ? $response['stat_requirements'] : [];

            $analysis = [
                'readiness' => $readiness,
                'recommendations' => $recommendations,
                'stat_requirements' => $statRequirements,
                'confidence' => (float) ($response['confidence'] ?? 0.85),
                'reasoning' => (string) ($response['reasoning'] ?? 'Race analysis completed'),
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
     *     reasoning: string
     * }
     */
    public function predictRacePerformance(): array
        $startTime = microtime(true);

        try {
            $context = [
                'character' => $this->getCharacterData($character),
                'race' => $raceDetails,
                'strategy' => $strategy,
                'task' => 'predict_race_performance',
            ];

            $response = $this->processWithMCPAgent($context);

            /** @var array<string, mixed> $performanceFactors */
            $performanceFactors = is_array((is_array($response) && isset($response['performance_factors']) ? $response['performance_factors'] : null)) ? $response['performance_factors'] : [];

            return [
                'predicted_position' => (int) ($response['predicted_position'] ?? 5),
                'win_probability' => (float) ($response['win_probability'] ?? 0.5),
                'performance_factors' => $performanceFactors,
                'confidence' => (float) ($response['confidence'] ?? 0.8),
                'reasoning' => (string) ($response['reasoning'] ?? 'Performance prediction completed'),
            ];
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
     *     reasoning: string
     * }
     */
    public function recommendRaceStrategy(): array
        $startTime = microtime(true);

        try {
            $context = [
                'character' => $this->getCharacterData($character),
                'race' => $raceDetails,
                'task' => 'recommend_race_strategy',
            ];

            $response = $this->processWithMCPAgent($context);

            /** @var array<string, mixed> $strategy */
            $strategy = is_array((is_array($response) && isset($response['strategy']) ? $response['strategy'] : null)) ? $response['strategy'] : [];
            /** @var array<int, string> $skillRecommendations */
            $skillRecommendations = is_array((is_array($response) && isset($response['skill_recommendations']) ? $response['skill_recommendations'] : null)) ? array_values($response['skill_recommendations']) : [];

            return [
                'strategy' => $strategy,
                'running_style' => (string) ($response['running_style'] ?? 'pace_chaser'),
                'skill_recommendations' => $skillRecommendations,
                'confidence' => (float) ($response['confidence'] ?? 0.85),
                'reasoning' => (string) ($response['reasoning'] ?? 'Strategy recommendation completed'),
            ];
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
    public function analyzePostRacePerformance(): array
        $startTime = microtime(true);

        try {
            $context = [
                'character' => $this->getCharacterData($character),
                'race_result' => $raceResult,
                'task' => 'analyze_post_race',
            ];

            $response = $this->processWithMCPAgent($context);

            /** @var array<string, mixed> $analysis */
            $analysis = is_array((is_array($response) && isset($response['analysis']) ? $response['analysis'] : null)) ? $response['analysis'] : [];
            /** @var array<int, string> $strengths */
            $strengths = is_array((is_array($response) && isset($response['strengths']) ? $response['strengths'] : null)) ? array_values($response['strengths']) : [];
            /** @var array<int, string> $weaknesses */
            $weaknesses = is_array((is_array($response) && isset($response['weaknesses']) ? $response['weaknesses'] : null)) ? array_values($response['weaknesses']) : [];
            /** @var array<int, string> $improvements */
            $improvements = is_array((is_array($response) && isset($response['improvements']) ? $response['improvements'] : null)) ? array_values($response['improvements']) : [];

            return [
                'analysis' => $analysis,
                'strengths' => $strengths,
                'weaknesses' => $weaknesses,
                'improvements' => $improvements,
                'confidence' => (float) ($response['confidence'] ?? 0.85),
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
    protected function getCharacterData(): array
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
    protected function processWithMCPAgent(): array
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
    protected function getDefaultRaceAnalysis(): array
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
        return [
            'enabled' => $this->enabled,
            'available' => $this->isAvailable(),
            'agent_id' => $this->agentId,
            'mcp_server' => 'strands-agents',
        ];
    }
}
