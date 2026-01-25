<?php

declare(strict_types=1);

namespace App\MCP;

use App\Services\AI\HybridAIService;
use Illuminate\Support\Facades\Log;

/**
 * Coordinates multiple AI agents for complex multi-step tasks
 * Handles task routing, agent execution chains, and result aggregation
 */
class SubagentCoordinationService
{
    public function __construct(
        protected HybridAIService $aiService
    ) {}

    /**
     * Coordinate a task across multiple agents
     *
     * @param  string  $taskType  Task type (training, race, skill, full_analysis)
     * @param  array<string, mixed>  $context  Task context and parameters
     * @return array<string, mixed> Aggregated results from all agents
     */
    public function coordinateTask(string $taskType, array $context): array
    {
        $agents = $this->getAgentsForTask($taskType);

        if (empty($agents)) {
            Log::warning("No agents found for task type: {$taskType}");

            return ['error' => 'No agents available for this task type'];
        }

        return $this->executeAgentChain($agents, $context);
    }

    /**
     * Execute a chain of agents sequentially
     *
     * @param  array<int, string>  $agents  Agent identifiers
     * @param  array<string, mixed>  $context  Execution context
     * @return array<string, mixed> Combined results
     */
    protected function executeAgentChain(array $agents, array $context): array
    {
        $results = [];
        $enrichedContext = $context;

        foreach ($agents as $agent) {
            try {
                $result = $this->executeAgent($agent, $enrichedContext);
                $results[$agent] = $result;

                // Enrich context with previous results for next agent
                $enrichedContext['previous_results'] = $results;
            } catch (\Exception $e) {
                Log::error("Agent execution failed: {$agent}", [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                $results[$agent] = [
                    'error' => $e->getMessage(),
                    'status' => 'failed',
                ];
            }
        }

        return $this->aggregateResults($results);
    }

    /**
     * Execute a single agent
     *
     * @param  string  $agent  Agent identifier
     * @param  array<string, mixed>  $context  Execution context
     * @return array<string, mixed> Agent result
     */
    protected function executeAgent(string $agent, array $context): array
    {
        $prompt = $this->buildAgentPrompt($agent, $context);

        $response = $this->aiService->generateResponse($prompt, [
            'agent' => $agent,
            'context' => $context,
        ]);

        return [
            'agent' => $agent,
            'response' => $response,
            'status' => 'success',
        ];
    }

    /**
     * Aggregate results from multiple agents
     *
     * @param  array<string, mixed>  $results  Individual agent results
     * @return array<string, mixed> Aggregated results
     */
    protected function aggregateResults(array $results): array
    {
        $successful = array_filter($results, fn ($r) => ($r['status'] ?? '') === 'success');
        $failed = array_filter($results, fn ($r) => ($r['status'] ?? '') === 'failed');

        return [
            'results' => $results,
            'summary' => [
                'total_agents' => count($results),
                'successful' => count($successful),
                'failed' => count($failed),
            ],
            'status' => empty($failed) ? 'success' : 'partial',
        ];
    }

    /**
     * Get agents required for a task type
     *
     * @param  string  $taskType  Task type identifier
     * @return array<int, string> Agent identifiers
     */
    protected function getAgentsForTask(string $taskType): array
    {
        return match ($taskType) {
            'training' => ['training_advisor'],
            'race' => ['race_strategy'],
            'skill' => ['skill_recommendation'],
            'full_analysis' => ['training_advisor', 'race_strategy', 'skill_recommendation'],
            default => [],
        };
    }

    /**
     * Build prompt for specific agent
     *
     * @param  string  $agent  Agent identifier
     * @param  array<string, mixed>  $context  Execution context
     * @return string Formatted prompt
     */
    protected function buildAgentPrompt(string $agent, array $context): string
    {
        $basePrompt = "Analyze the following context and provide recommendations:\n\n";
        $basePrompt .= json_encode($context, JSON_PRETTY_PRINT);

        return match ($agent) {
            'training_advisor' => $basePrompt."\n\nProvide training recommendations.",
            'race_strategy' => $basePrompt."\n\nProvide race strategy recommendations.",
            'skill_recommendation' => $basePrompt."\n\nProvide skill recommendations.",
            default => $basePrompt,
        };
    }
}
