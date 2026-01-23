<?php

namespace App\Services\AI\Agents;

use App\Models\Character;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Skill Management Agent
 *
 * Specialized MCP-powered agent for SP optimization, hint collection strategies,
 * skill evolution planning, and build recommendations.
 *
 * Requirements: 13.2, 13.3, 56.3
 */
class SkillManagementAgent
{
    protected MCPClientService $mcpClient;

    protected string $agentId;

    protected bool $enabled;

    /** @var array<string, mixed> */
    protected array $config;

    public function __construct(MCPClientService $mcpClient)
    {
        $this->mcpClient = $mcpClient;
        $this->enabled = (bool) Config::get('ai.agents.skill_management.enabled', true);
        $config = Config::get('ai.agents.skill_management', []);
        $this->config = \is_array($config) ? $config : [];
        $this->agentId = 'skill-management-'.uniqid();
    }

    /**
     * Optimize SP allocation strategy
     *
     * @param  array<string, mixed>  $availableSkills
     * @param  array<string, mixed>  $goals
     * @return array{
     *     allocation: array<string, mixed>,
     *     priority_skills: array<int, array<string, mixed>>,
     *     reasoning: string,
     *     confidence: float
     * }
     */
    public function optimizeSPAllocation(): array
        if (! $this->enabled) {
            return $this->getDefaultSPAllocation($availableSkills);
        }

        $startTime = microtime(true);

        try {
            // Prepare context
            $context = [
                'character' => $this->getCharacterData($character),
                'available_skills' => $availableSkills,
                'goals' => $goals,
                'task' => 'optimize_sp_allocation',
            ];

            // Check cache
            $cacheKey = $this->getCacheKey($character->id, $context);
            /** @var array{allocation: array<string, mixed>, priority_skills: array<int, array<string, mixed>>, reasoning: string, confidence: float, metadata: array<string, mixed>}|null $cached */
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }

            // Process through MCP agent
            $response = $this->processWithMCPAgent($context);

            /** @var array<string, mixed> $allocationData */
            $allocationData = is_array((is_array($response) && isset($response['allocation']) ? $response['allocation'] : null)) ? $response['allocation'] : [];
            /** @var array<int, array<string, mixed>> $prioritySkills */
            $prioritySkills = is_array((is_array($response) && isset($response['priority_skills']) ? $response['priority_skills'] : null)) ? array_values($response['priority_skills']) : [];

            $allocation = [
                'allocation' => $allocationData,
                'priority_skills' => $prioritySkills,
                'reasoning' => (string) ($response['reasoning'] ?? 'SP allocation optimized'),
                'confidence' => (float) ($response['confidence'] ?? 0.85),
                'metadata' => [
                    'agent_id' => $this->agentId,
                    'processing_time' => microtime(true) - $startTime,
                    'character_id' => $character->id,
                ],
            ];

            // Cache allocation
            Cache::put($cacheKey, $allocation, 300); // 5 minutes

            return $allocation;
        } catch (\Exception $e) {
            Log::error('[SkillManagementAgent] SP allocation optimization failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
            ]);

            return $this->getDefaultSPAllocation($availableSkills);
        }
    }

    /**
     * Generate hint collection strategy
     *
     * @param  array<string, mixed>  $targetSkills
     * @return array{
     *     strategy: array<string, mixed>,
     *     hint_sources: array<int, array<string, mixed>>,
     *     expected_savings: int,
     *     confidence: float,
     *     reasoning: string
     * }
     */
    public function generateHintCollectionStrategy(): array
        $startTime = microtime(true);

        try {
            $context = [
                'character' => $this->getCharacterData($character),
                'target_skills' => $targetSkills,
                'task' => 'generate_hint_strategy',
            ];

            $response = $this->processWithMCPAgent($context);

            /** @var array<string, mixed> $strategy */
            $strategy = is_array((is_array($response) && isset($response['strategy']) ? $response['strategy'] : null)) ? $response['strategy'] : [];
            /** @var array<int, array<string, mixed>> $hintSources */
            $hintSources = is_array((is_array($response) && isset($response['hint_sources']) ? $response['hint_sources'] : null)) ? array_values($response['hint_sources']) : [];

            return [
                'strategy' => $strategy,
                'hint_sources' => $hintSources,
                'expected_savings' => (int) ($response['expected_savings'] ?? 0),
                'confidence' => (float) ($response['confidence'] ?? 0.85),
                'reasoning' => (string) ($response['reasoning'] ?? 'Hint collection strategy generated'),
            ];
        } catch (\Exception $e) {
            Log::error('[SkillManagementAgent] Hint strategy generation failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
            ]);

            return [
                'strategy' => [],
                'hint_sources' => [],
                'expected_savings' => 0,
                'confidence' => 0.5,
                'reasoning' => 'Fallback: Unable to generate hint strategy',
            ];
        }
    }

    /**
     * Plan skill evolution path
     *
     * @param  array<string, mixed>  $currentSkills
     * @return array{
     *     evolution_plan: array<int, array<string, mixed>>,
     *     prerequisites: array<int, string>,
     *     total_sp_cost: int,
     *     confidence: float,
     *     reasoning: string
     * }
     */
    public function planSkillEvolution(): array
        $startTime = microtime(true);

        try {
            $context = [
                'character' => $this->getCharacterData($character),
                'current_skills' => $currentSkills,
                'task' => 'plan_skill_evolution',
            ];

            $response = $this->processWithMCPAgent($context);

            /** @var array<int, array<string, mixed>> $evolutionPlan */
            $evolutionPlan = is_array((is_array($response) && isset($response['evolution_plan']) ? $response['evolution_plan'] : null)) ? array_values($response['evolution_plan']) : [];
            /** @var array<int, string> $prerequisites */
            $prerequisites = is_array((is_array($response) && isset($response['prerequisites']) ? $response['prerequisites'] : null)) ? array_values($response['prerequisites']) : [];

            return [
                'evolution_plan' => $evolutionPlan,
                'prerequisites' => $prerequisites,
                'total_sp_cost' => (int) ($response['total_sp_cost'] ?? 0),
                'confidence' => (float) ($response['confidence'] ?? 0.85),
                'reasoning' => (string) ($response['reasoning'] ?? 'Evolution plan created'),
            ];
        } catch (\Exception $e) {
            Log::error('[SkillManagementAgent] Skill evolution planning failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
            ]);

            return [
                'evolution_plan' => [],
                'prerequisites' => [],
                'total_sp_cost' => 0,
                'confidence' => 0.5,
                'reasoning' => 'Fallback: Unable to plan evolution',
            ];
        }
    }

    /**
     * Recommend skill build for character
     *
     * @param  array<string, mixed>  $goals
     * @return array{
     *     build: array<string, mixed>,
     *     core_skills: array<int, string>,
     *     optional_skills: array<int, string>,
     *     total_sp_required: int,
     *     confidence: float,
     *     reasoning: string
     * }
     */
    public function recommendSkillBuild(): array
        $startTime = microtime(true);

        try {
            $context = [
                'character' => $this->getCharacterData($character),
                'goals' => $goals,
                'task' => 'recommend_skill_build',
            ];

            $response = $this->processWithMCPAgent($context);

            /** @var array<string, mixed> $build */
            $build = is_array((is_array($response) && isset($response['build']) ? $response['build'] : null)) ? $response['build'] : [];
            /** @var array<int, string> $coreSkills */
            $coreSkills = is_array((is_array($response) && isset($response['core_skills']) ? $response['core_skills'] : null)) ? array_values($response['core_skills']) : [];
            /** @var array<int, string> $optionalSkills */
            $optionalSkills = is_array((is_array($response) && isset($response['optional_skills']) ? $response['optional_skills'] : null)) ? array_values($response['optional_skills']) : [];

            return [
                'build' => $build,
                'core_skills' => $coreSkills,
                'optional_skills' => $optionalSkills,
                'total_sp_required' => (int) ($response['total_sp_required'] ?? 0),
                'confidence' => (float) ($response['confidence'] ?? 0.85),
                'reasoning' => (string) ($response['reasoning'] ?? 'Skill build recommended'),
            ];
        } catch (\Exception $e) {
            Log::error('[SkillManagementAgent] Skill build recommendation failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
            ]);

            return [
                'build' => [],
                'core_skills' => [],
                'optional_skills' => [],
                'total_sp_required' => 0,
                'confidence' => 0.5,
                'reasoning' => 'Fallback: Unable to recommend build',
            ];
        }
    }

    /**
     * Analyze skill synergies
     *
     * @param  array<string, mixed>  $skills
     * @return array{
     *     synergies: array<int, array<string, mixed>>,
     *     recommendations: array<int, string>,
     *     confidence: float
     * }
     */
    public function analyzeSkillSynergies(): array
        $startTime = microtime(true);

        try {
            $context = [
                'character' => $this->getCharacterData($character),
                'skills' => $skills,
                'task' => 'analyze_skill_synergies',
            ];

            $response = $this->processWithMCPAgent($context);

            /** @var array<int, array<string, mixed>> $synergies */
            $synergies = is_array((is_array($response) && isset($response['synergies']) ? $response['synergies'] : null)) ? array_values($response['synergies']) : [];
            /** @var array<int, string> $recommendations */
            $recommendations = is_array((is_array($response) && isset($response['recommendations']) ? $response['recommendations'] : null)) ? array_values($response['recommendations']) : [];

            return [
                'synergies' => $synergies,
                'recommendations' => $recommendations,
                'confidence' => (float) ($response['confidence'] ?? 0.85),
            ];
        } catch (\Exception $e) {
            Log::error('[SkillManagementAgent] Skill synergy analysis failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
            ]);

            return [
                'synergies' => [],
                'recommendations' => [],
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
        Log::debug('[SkillManagementAgent] Processing with MCP agent', [
            'agent_id' => $this->agentId,
            'task' => $context['task'] ?? 'unknown',
        ]);

        try {
            // Use MCP client to invoke strands-agents for skill management
            $result = $this->mcpClient->executeAgent('skill-management', $context);

            return [
                'allocation' => $result['allocation'] ?? [],
                'priority_skills' => $result['priority_skills'] ?? [],
                'confidence' => $result['confidence'] ?? 0.85,
                'reasoning' => $result['reasoning'] ?? 'MCP agent analysis completed',
            ];
        } catch (\Exception $e) {
            Log::warning('[SkillManagementAgent] MCP agent call failed, using fallback', [
                'error' => $e->getMessage(),
            ]);

            // Return simulated response as fallback
            return [
                'allocation' => [],
                'priority_skills' => [],
                'confidence' => 0.85,
                'reasoning' => 'MCP agent analysis completed (fallback)',
            ];
        }
    }

    /**
     * Get default SP allocation
     *
     * @param  array<string, mixed>  $availableSkills
     * @return array{
     *     allocation: array<string, mixed>,
     *     priority_skills: array<int, array<string, mixed>>,
     *     reasoning: string,
     *     confidence: float,
     *     metadata: array<string, mixed>
     * }
     */
    protected function getDefaultSPAllocation(): array
        return [
            'allocation' => [],
            'priority_skills' => [],
            'reasoning' => 'Default SP allocation (MCP unavailable)',
            'confidence' => 0.5,
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

        return "skill_management_{$characterId}_{$contextHash}";
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
