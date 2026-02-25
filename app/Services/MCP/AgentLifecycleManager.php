<?php

declare(strict_types=1);

namespace App\Services\MCP;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Agent Lifecycle Manager
 *
 * Manages the complete lifecycle of agents including creation, initialization,
 * monitoring, maintenance, and termination.
 */
class AgentLifecycleManager
{
    /**
     * Lifecycle stages
     */
    public const STAGE_CREATED = 'created';

    public const STAGE_INITIALIZING = 'initializing';

    public const STAGE_ACTIVE = 'active';

    public const STAGE_PAUSED = 'paused';

    public const STAGE_TERMINATING = 'terminating';

    public const STAGE_TERMINATED = 'terminated';

    public function __construct(
        private readonly MCPClientService $mcpClient
    ) {}

    /**
     * Create and initialize a new agent
     *
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    public function createAgent(string $type, string $name, array $config = []): array
    {
        try {
            $agentId = $this->generateAgentId($type);

            // Create agent record in database
            $agentData = [
                'id' => $agentId,
                'type' => $type,
                'name' => $name,
                'config' => json_encode($config),
                'status' => self::STAGE_CREATED,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            DB::table('ucp_mcp_agents')->insert($agentData);

            Log::info('[AgentLifecycle] Agent created', [
                'agent_id' => $agentId,
                'type' => $type,
                'name' => $name,
            ]);

            // Initialize agent
            $this->initializeAgent($agentId);

            $agent = $this->getAgent($agentId);
            if ($agent === null) {
                throw new \RuntimeException("Failed to retrieve created agent: {$agentId}");
            }

            return $agent;
        } catch (\Exception $e) {
            Log::error('[AgentLifecycle] Failed to create agent', [
                'type' => $type,
                'name' => $name,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Initialize an agent
     */
    public function initializeAgent(string $agentId): bool
    {
        try {
            $this->updateAgentStage($agentId, self::STAGE_INITIALIZING);

            $agent = $this->getAgent($agentId);
            if ($agent === null) {
                throw new \RuntimeException("Agent not found: {$agentId}");
            }

            // Perform initialization tasks
            $this->setupAgentResources($agentId);
            $this->registerAgentWithMCP($agentId, $agent);
            $this->startAgentMonitoring($agentId);

            $this->updateAgentStage($agentId, self::STAGE_ACTIVE);

            Log::info('[AgentLifecycle] Agent initialized', [
                'agent_id' => $agentId,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('[AgentLifecycle] Failed to initialize agent', [
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
            ]);

            $this->updateAgentStage($agentId, self::STAGE_TERMINATED);

            return false;
        }
    }

    /**
     * Monitor agent health and performance
     *
     * @return array<string, mixed>
     */
    public function monitorAgent(string $agentId): array
    {
        $agent = $this->getAgent($agentId);

        if (! $agent) {
            throw new \RuntimeException("Agent not found: {$agentId}");
        }

        $metrics = $this->collectAgentMetrics($agentId);
        $health = $this->assessAgentHealth($agentId, $metrics);

        return [
            'agent_id' => $agentId,
            'stage' => $agent['status'],
            'metrics' => $metrics,
            'health' => $health,
            'recommendations' => $this->generateMaintenanceRecommendations($metrics, $health),
        ];
    }

    /**
     * Pause an agent
     */
    public function pauseAgent(string $agentId): bool
    {
        try {
            $this->updateAgentStage($agentId, self::STAGE_PAUSED);

            Log::info('[AgentLifecycle] Agent paused', [
                'agent_id' => $agentId,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('[AgentLifecycle] Failed to pause agent', [
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Resume a paused agent
     */
    public function resumeAgent(string $agentId): bool
    {
        try {
            $agent = $this->getAgent($agentId);

            if ($agent === null) {
                throw new \RuntimeException("Agent not found: {$agentId}");
            }

            $stage = $agent['status'] ?? null;
            if ($stage !== self::STAGE_PAUSED) {
                throw new \RuntimeException("Agent is not paused: {$agentId}");
            }

            $this->updateAgentStage($agentId, self::STAGE_ACTIVE);

            Log::info('[AgentLifecycle] Agent resumed', [
                'agent_id' => $agentId,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('[AgentLifecycle] Failed to resume agent', [
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Terminate an agent
     */
    public function terminateAgent(string $agentId): bool
    {
        try {
            $this->updateAgentStage($agentId, self::STAGE_TERMINATING);

            // Cleanup agent resources
            $this->cleanupAgentResources($agentId);
            $this->unregisterAgentFromMCP($agentId);
            $this->stopAgentMonitoring($agentId);

            $this->updateAgentStage($agentId, self::STAGE_TERMINATED);

            Log::info('[AgentLifecycle] Agent terminated', [
                'agent_id' => $agentId,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('[AgentLifecycle] Failed to terminate agent', [
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get all active agents
     *
     * @return array<int, array<string, mixed>>
     */
    public function getActiveAgents(): array
    {
        /** @var array<int, array<string, mixed>> $agents */
        $agents = DB::table('ucp_mcp_agents')
            ->where('status', self::STAGE_ACTIVE)
            ->get()
            ->map(fn ($agent): array => (array) $agent)
            ->all();

        return $agents;
    }

    /**
     * Get agent lifecycle history
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAgentHistory(string $agentId): array
    {
        $result = Cache::get("agent_history:{$agentId}");

        if (is_array($result)) {
            /** @var array<int, array<string, mixed>> $result */
            return $result;
        }

        return [];
    }

    /**
     * Protected helper methods
     */
    protected function generateAgentId(string $type): string
    {
        return 'agent_'.$type.'_'.uniqid().'_'.bin2hex(random_bytes(4));
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function getAgent(string $agentId): ?array
    {
        $agent = DB::table('ucp_mcp_agents')
            ->where('id', $agentId)
            ->first();

        if (! $agent) {
            return null;
        }

        $agentArray = (array) $agent;
        $agentArray['config'] = json_decode($agentArray['config'], true);

        return $agentArray;
    }

    protected function updateAgentStage(string $agentId, string $stage): void
    {
        DB::table('ucp_mcp_agents')
            ->where('id', $agentId)
            ->update([
                'status' => $stage,
                'updated_at' => now(),
            ]);

        // Record in history
        $this->recordLifecycleEvent($agentId, $stage);
    }

    protected function recordLifecycleEvent(string $agentId, string $event): void
    {
        $history = $this->getAgentHistory($agentId);
        $history[] = [
            'event' => $event,
            'timestamp' => now()->toIso8601String(),
        ];

        Cache::put("agent_history:{$agentId}", $history, 86400); // 24 hours
    }

    protected function setupAgentResources(string $agentId): void
    {
        // Initialize agent cache
        Cache::put("agent_resources:{$agentId}", [
            'initialized_at' => now()->toIso8601String(),
        ], 3600);
    }

    /**
     * @param  array<string, mixed>  $agent
     */
    protected function registerAgentWithMCP(string $agentId, array $agent): void
    {
        // Register agent with MCP server
        try {
            $type = is_string($agent['type'] ?? null) ? $agent['type'] : '';
            $config = is_array($agent['config'] ?? null) ? $agent['config'] : [];
            $this->mcpClient->registerAgent($agentId, $type, $config);
        } catch (\Exception $e) {
            Log::warning('[AgentLifecycle] Failed to register agent with MCP', [
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function startAgentMonitoring(string $agentId): void
    {
        Cache::put("agent_monitoring:{$agentId}", [
            'started_at' => now()->toIso8601String(),
            'status' => 'active',
        ], 3600);
    }

    /**
     * @return array<string, mixed>
     */
    protected function collectAgentMetrics(string $agentId): array
    {
        $result = Cache::get("agent_metrics:{$agentId}");

        return is_array($result) ? $result : [
            'execution_count' => 0,
            'average_execution_time' => 0,
            'success_rate' => 100,
            'last_execution' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $metrics
     */
    protected function assessAgentHealth(string $agentId, array $metrics): string
    {
        $successRate = $metrics['success_rate'] ?? 100;

        if ($successRate >= 90) {
            return 'healthy';
        } elseif ($successRate >= 70) {
            return 'degraded';
        } else {
            return 'unhealthy';
        }
    }

    /**
     * @param  array<string, mixed>  $metrics
     * @return array<int, array<string, string>>
     */
    protected function generateMaintenanceRecommendations(array $metrics, string $health): array
    {
        $recommendations = [];

        if ($health === 'unhealthy') {
            $recommendations[] = [
                'type' => 'critical',
                'message' => 'Agent health is critical. Consider restarting or reconfiguring the agent.',
            ];
        } elseif ($health === 'degraded') {
            $recommendations[] = [
                'type' => 'warning',
                'message' => 'Agent performance is degraded. Review recent errors and optimize configuration.',
            ];
        }

        if (($metrics['average_execution_time'] ?? 0) > 10) {
            $recommendations[] = [
                'type' => 'performance',
                'message' => 'Average execution time is high. Consider optimizing agent logic or resources.',
            ];
        }

        return $recommendations;
    }

    protected function cleanupAgentResources(string $agentId): void
    {
        Cache::forget("agent_resources:{$agentId}");
        Cache::forget("agent_metrics:{$agentId}");
        Cache::forget("agent_monitoring:{$agentId}");
        Cache::forget("agent_history:{$agentId}");
    }

    protected function unregisterAgentFromMCP(string $agentId): void
    {
        try {
            $this->mcpClient->unregisterAgent($agentId);
        } catch (\Exception $e) {
            Log::warning('[AgentLifecycle] Failed to unregister agent from MCP', [
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function stopAgentMonitoring(string $agentId): void
    {
        Cache::forget("agent_monitoring:{$agentId}");
    }
}
