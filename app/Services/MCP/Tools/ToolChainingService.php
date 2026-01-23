<?php

namespace App\Services\MCP\Tools;

use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Tool Chaining Service
 *
 * Enables complex multi-step operations by chaining multiple MCP tools together,
 * with workflow automation, monitoring, and optimization.
 *
 * Requirements: 13.4, 56.2
 */
class ToolChainingService
{
    protected MCPClientService $mcpClient;

    protected AWSPricingService $awsPricing;

    protected AWSKnowledgeService $awsKnowledge;

    protected AWSAPIService $awsAPI;

    protected Context7Service $context7;

    protected FetchService $fetch;

    protected bool $enabled;

    /** @var array<string, mixed> */
    protected array $workflowTemplates;

    public function __construct(
        MCPClientService $mcpClient,
        AWSPricingService $awsPricing,
        AWSKnowledgeService $awsKnowledge,
        AWSAPIService $awsAPI,
        Context7Service $context7,
        FetchService $fetch
    ) {
        $this->mcpClient = $mcpClient;
        $this->awsPricing = $awsPricing;
        $this->awsKnowledge = $awsKnowledge;
        $this->awsAPI = $awsAPI;
        $this->context7 = $context7;
        $this->fetch = $fetch;

        $this->enabled = (bool) Config::get('mcp.tools.chaining.enabled', true);
        $templates = Config::get('mcp.tools.chaining.templates', []);
        $this->workflowTemplates = \is_array($templates) ? $templates : [];
    }

    /**
     * Execute a tool chain workflow
     *
     * @param  array<int, array{
     *     tool: string,
     *     method: string,
     *     params: array<string, mixed>,
     *     condition?: string,
     *     on_success?: array<string, mixed>,
     *     on_failure?: array<string, mixed>
     * }>  $steps
     * @param  array<string, mixed>  $context
     * @return array{
     *     success: bool,
     *     results: array<int, mixed>,
     *     workflow: array<int, array{
     *         step: int,
     *         tool: string,
     *         method: string,
     *         status: string,
     *         duration: float,
     *         error?: string
     *     }>,
     *     total_duration: float,
     *     context: array<string, mixed>
     * }
     */
    public function executeChain(): array
        if (! $this->enabled) {
            return $this->getDisabledResponse();
        }

        $startTime = microtime(true);
        $results = [];
        $workflow = [];
        $sharedContext = $context;

        foreach ($steps as $index => $step) {
            $stepStartTime = microtime(true);

            // Check condition if specified
            if (isset($step['condition']) && ! $this->evaluateCondition($step['condition'], $sharedContext)) {
                $workflow[] = [
                    'step' => $index,
                    'tool' => $step['tool'],
                    'method' => $step['method'],
                    'status' => 'skipped',
                    'duration' => 0.0,
                ];

                continue;
            }

            try {
                // Execute step
                $result = $this->executeStep($step, $sharedContext);

                $results[] = $result;
                $workflow[] = [
                    'step' => $index,
                    'tool' => $step['tool'],
                    'method' => $step['method'],
                    'status' => 'success',
                    'duration' => microtime(true) - $stepStartTime,
                ];

                // Update shared context
                if (isset($result['context_updates'])) {
                    $sharedContext = [...$sharedContext, ...$result['context_updates']];
                }

                // Handle on_success actions
                if (isset($step['on_success'])) {
                    $this->handleAction($step['on_success'], $result, $sharedContext);
                }
            } catch (\Exception $e) {
                Log::error('[ToolChaining] Step execution failed', [
                    'step' => $index,
                    'tool' => $step['tool'],
                    'method' => $step['method'],
                    'error' => $e->getMessage(),
                ]);

                $workflow[] = [
                    'step' => $index,
                    'tool' => $step['tool'],
                    'method' => $step['method'],
                    'status' => 'failed',
                    'duration' => microtime(true) - $stepStartTime,
                    'error' => $e->getMessage(),
                ];

                // Handle on_failure actions
                if (isset($step['on_failure'])) {
                    $this->handleAction($step['on_failure'], null, $sharedContext);
                }

                // Stop execution on failure unless continue_on_error is set
                if (! ($step['continue_on_error'] ?? false)) {
                    break;
                }
            }
        }

        return [
            'success' => ! empty($results),
            'results' => $results,
            'workflow' => $workflow,
            'total_duration' => microtime(true) - $startTime,
            'context' => $sharedContext,
        ];
    }

    /**
     * Execute a predefined workflow template
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function executeTemplate(): array
        if (! isset($this->workflowTemplates[$templateName])) {
            return [
                'success' => false,
                'error' => "Template '{$templateName}' not found",
            ];
        }

        $template = $this->workflowTemplates[$templateName];
        $steps = $template['steps'] ?? [];

        // Replace parameters in steps
        $steps = $this->replaceParameters($steps, $params);

        return $this->executeChain($steps, $params);
    }

    /**
     * Create AI cost optimization workflow
     *
     * @param  array<string, mixed>  $usageData
     * @return array<string, mixed>
     */
    public function createCostOptimizationWorkflow(): array
        $steps = [
            [
                'tool' => 'aws_pricing',
                'method' => 'getCostOptimizationRecommendations',
                'params' => ['usage_patterns' => $usageData],
            ],
            [
                'tool' => 'aws_knowledge',
                'method' => 'getBedrockOptimizations',
                'params' => [],
            ],
            [
                'tool' => 'aws_api',
                'method' => 'monitorBedrockUsage',
                'params' => ['region' => 'us-east-1'],
            ],
        ];

        return $this->executeChain($steps);
    }

    /**
     * Create external data fetch workflow
     *
     * @param  array<int, string>  $endpoints
     * @return array<string, mixed>
     */
    public function createDataFetchWorkflow(): array
        $steps = [];

        foreach ($endpoints as $endpoint) {
            $steps[] = [
                'tool' => 'fetch',
                'method' => 'fetchUmapyoiData',
                'params' => ['endpoint' => $endpoint],
                'continue_on_error' => true,
            ];
        }

        return $this->executeChain($steps);
    }

    /**
     * Create context-aware AI workflow
     *
     * @param  array<string, mixed>  $aiRequest
     * @return array<string, mixed>
     */
    public function createContextAwareAIWorkflow(): array
        $steps = [
            [
                'tool' => 'context7',
                'method' => 'retrieveContext',
                'params' => ['conversation_id' => $conversationId],
            ],
            [
                'tool' => 'context7',
                'method' => 'analyzeContext',
                'params' => ['conversation_id' => $conversationId],
            ],
            [
                'tool' => 'context7',
                'method' => 'updateContext',
                'params' => [
                    'conversation_id' => $conversationId,
                    'updates' => $aiRequest,
                ],
            ],
        ];

        return $this->executeChain($steps);
    }

    /**
     * Execute a single step
     *
     * @param  array{
     *     tool: string,
     *     method: string,
     *     params: array<string, mixed>
     * }  $step
     * @param  array<string, mixed>  $context
     */
    protected function executeStep(array $step, array $context): mixed
    {
        $tool = $this->getTool($step['tool']);
        $method = $step['method'];
        $params = $step['params'];

        // Replace context variables in params
        $params = $this->replaceContextVariables($params, $context);

        // Execute method
        if (! method_exists($tool, $method)) {
            throw new \BadMethodCallException("Method '{$method}' not found on tool '{$step['tool']}'");
        }

        return $tool->$method(...array_values($params));
    }

    /**
     * Get tool instance
     */
    protected function getTool(string $toolName): object
    {
        return match ($toolName) {
            'aws_pricing' => $this->awsPricing,
            'aws_knowledge' => $this->awsKnowledge,
            'aws_api' => $this->awsAPI,
            'context7' => $this->context7,
            'fetch' => $this->fetch,
            default => throw new \InvalidArgumentException("Unknown tool: {$toolName}"),
        };
    }

    /**
     * Evaluate condition
     *
     * @param  array<string, mixed>  $context
     */
    protected function evaluateCondition(string $condition, array $context): bool
    {
        // Simple condition evaluation (can be extended)
        // Format: "context.key == value" or "context.key != value"

        if (str_contains($condition, '==')) {
            [$key, $value] = explode('==', $condition);
            $key = trim(str_replace('context.', '', $key));
            $value = trim($value, " '\"");

            return ($context[$key] ?? null) == $value;
        }

        if (str_contains($condition, '!=')) {
            [$key, $value] = explode('!=', $condition);
            $key = trim(str_replace('context.', '', $key));
            $value = trim($value, " '\"");

            return ($context[$key] ?? null) != $value;
        }

        return true;
    }

    /**
     * Handle action (on_success or on_failure)
     *
     * @param  array<string, mixed>  $action
     * @param  array<string, mixed>  $context
     */
    protected function handleAction(array $action, mixed $result, array $context): void
    {
        $actionType = $action['type'] ?? 'log';

        match ($actionType) {
            'log' => Log::info('[ToolChaining] Action executed', [
                'action' => $action,
                'result' => $result,
            ]),
            'cache' => Cache::put(
                $action['key'] ?? 'tool_chain_result',
                $result,
                $action['ttl'] ?? 3600
            ),
            'notify' => $this->sendNotification($action, $result),
            default => Log::warning('[ToolChaining] Unknown action type', ['type' => $actionType]),
        };
    }

    /**
     * Send notification for workflow events.
     *
     * Notifications are logged for audit purposes. For production use,
     * this can be extended to integrate with Laravel's notification system
     * (email, Slack, database notifications) based on the action configuration.
     *
     * @param  array<string, mixed>  $action  Notification configuration with optional keys:
     *                                        - channel: notification channel (log, email, slack)
     *                                        - recipients: array of notification recipients
     *                                        - message: custom notification message
     * @param  mixed  $result  The result data to include in the notification
     */
    protected function sendNotification(array $action, mixed $result): void
    {
        $channel = $action['channel'] ?? 'log';
        $message = $action['message'] ?? 'Tool chain workflow notification';

        // Log all notifications for audit trail
        Log::info('[ToolChaining] Notification', [
            'channel' => $channel,
            'message' => $message,
            'action' => $action,
            'result' => $result,
        ]);

        // Future extension point: integrate with Laravel's notification system
        // Example: Notification::send($recipients, new ToolChainNotification($action, $result));
    }

    /**
     * Replace parameters in steps
     *
     * @param  array<int, array<string, mixed>>  $steps
     * @param  array<string, mixed>  $params
     * @return array<int, array<string, mixed>>
     */
    protected function replaceParameters(): array
        $json = json_encode($steps) ?: '[]';

        foreach ($params as $key => $value) {
            $json = str_replace("{{$key}}", (is_string($value) ? (string) $value : ''), $json);
        }

        return json_decode($json, true) ?: [];
    }

    /**
     * Replace context variables in params
     *
     * @param  array<string, mixed>  $params
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function replaceContextVariables(): array
        foreach ($params as $key => $value) {
            if (\is_string($value) && str_starts_with($value, 'context.')) {
                $contextKey = substr($value, 8); // Remove 'context.' prefix
                $params[$key] = $context[$contextKey] ?? $value;
            }
        }

        return $params;
    }

    /**
     * Get disabled response
     *
     * @return array<string, mixed>
     */
    protected function getDisabledResponse(): array
        return [
            'success' => false,
            'results' => [],
            'workflow' => [],
            'total_duration' => 0.0,
            'context' => [],
            'error' => 'Tool chaining is disabled',
        ];
    }

    /**
     * Get available workflow templates
     *
     * @return array<string, array{
     *     name: string,
     *     description: string,
     *     parameters: array<string, string>
     * }>
     */
    public function getAvailableTemplates(): array
        $templates = [];

        foreach ($this->workflowTemplates as $name => $template) {
            $templates[$name] = [
                'name' => $name,
                'description' => $template['description'] ?? '',
                'parameters' => $template['parameters'] ?? [],
            ];
        }

        return $templates;
    }

    /**
     * Get service status
     *
     * @return array{
     *     enabled: bool,
     *     available_tools: array<string, bool>,
     *     template_count: int
     * }
     */
    public function getStatus(): array
        return [
            'enabled' => $this->enabled,
            'available_tools' => [
                'aws_pricing' => $this->awsPricing->isAvailable(),
                'aws_knowledge' => $this->awsKnowledge->isAvailable(),
                'aws_api' => $this->awsAPI->isAvailable(),
                'context7' => $this->context7->isAvailable(),
                'fetch' => $this->fetch->isAvailable(),
            ],
            'template_count' => \count($this->workflowTemplates),
        ];
    }
}
