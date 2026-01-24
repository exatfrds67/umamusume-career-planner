<?php

declare(strict_types=1);

namespace App\Services\MCP;

use App\Models\Career;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Workflow Template Service
 *
 * Provides pre-configured workflow templates for common multi-agent scenarios.
 * Simplifies complex agent orchestration patterns.
 */
class WorkflowTemplateService
{
    /**
     * Template types
     */
    public const TEMPLATE_TRAINING_OPTIMIZATION = 'training_optimization';

    public const TEMPLATE_RACE_PREPARATION = 'race_preparation';

    public const TEMPLATE_SKILL_PLANNING = 'skill_planning';

    public const TEMPLATE_CAREER_STRATEGY = 'career_strategy';

    public const TEMPLATE_COMPREHENSIVE_ANALYSIS = 'comprehensive_analysis';

    public const TEMPLATE_TURN_DECISION = 'turn_decision';

    public const TEMPLATE_GOAL_PLANNING = 'goal_planning';

    public function __construct(
        private readonly AgentOrchestrationService $orchestration,
        private readonly AgentContextService $contextService
    ) {}

    /**
     * Get all available workflow templates
     *
     * @return array<string, array<string, mixed>>
     */
    public function getAvailableTemplates(): array
    {
        return [
            self::TEMPLATE_TRAINING_OPTIMIZATION => [
                'name' => 'Training Optimization',
                'description' => 'Multi-agent workflow for optimizing training decisions',
                'agents' => ['TrainingOptimizationAgent', 'ResourceManagementAgent', 'SkillBuildPlanningAgent'],
                'pattern' => AgentOrchestrationService::PATTERN_SEQUENTIAL,
                'estimated_time' => '5-10 seconds',
            ],
            self::TEMPLATE_RACE_PREPARATION => [
                'name' => 'Race Preparation',
                'description' => 'Comprehensive race analysis and strategy planning',
                'agents' => ['RaceAnalysisAgent', 'StrategyPlanningAgent', 'PerformancePredictionAgent'],
                'pattern' => AgentOrchestrationService::PATTERN_PARALLEL,
                'estimated_time' => '3-7 seconds',
            ],
            self::TEMPLATE_SKILL_PLANNING => [
                'name' => 'Skill Planning',
                'description' => 'SP optimization and skill hint collection strategy',
                'agents' => ['SkillAnalysisAgent', 'HintOptimizationAgent', 'SPBudgetAgent'],
                'pattern' => AgentOrchestrationService::PATTERN_COLLABORATIVE,
                'estimated_time' => '4-8 seconds',
            ],
            self::TEMPLATE_CAREER_STRATEGY => [
                'name' => 'Career Strategy',
                'description' => 'Long-term career planning and goal optimization',
                'agents' => ['CareerStrategyAgent', 'GoalPlanningAgent', 'TimelineOptimizationAgent'],
                'pattern' => AgentOrchestrationService::PATTERN_HIERARCHICAL,
                'estimated_time' => '8-15 seconds',
            ],
            self::TEMPLATE_COMPREHENSIVE_ANALYSIS => [
                'name' => 'Comprehensive Analysis',
                'description' => 'Full character and career analysis with all agents',
                'agents' => ['TrainingOptimizationAgent', 'RaceAnalysisAgent', 'SkillAnalysisAgent', 'CareerStrategyAgent'],
                'pattern' => AgentOrchestrationService::PATTERN_PARALLEL,
                'estimated_time' => '10-20 seconds',
            ],
            self::TEMPLATE_TURN_DECISION => [
                'name' => 'Turn Decision',
                'description' => 'Quick turn-by-turn decision making',
                'agents' => ['TrainingOptimizationAgent', 'ResourceManagementAgent'],
                'pattern' => AgentOrchestrationService::PATTERN_SEQUENTIAL,
                'estimated_time' => '2-5 seconds',
            ],
            self::TEMPLATE_GOAL_PLANNING => [
                'name' => 'Goal Planning',
                'description' => 'Strategic goal setting and achievement planning',
                'agents' => ['GoalPlanningAgent', 'TimelineOptimizationAgent', 'ResourceManagementAgent'],
                'pattern' => AgentOrchestrationService::PATTERN_SEQUENTIAL,
                'estimated_time' => '5-10 seconds',
            ],
        ];
    }

    /**
     * Execute a workflow template
     *
     * @param  array<string, mixed>  $additionalContext
     * @return array<string, mixed>
     */
    public function executeTemplate(
        string $templateType,
        \App\Models\Character $character,
        ?Career $career = null,
        array $additionalContext = []
    ): array {
        try {
            $template = $this->getTemplate($templateType);

            if (! $template) {
                throw new \InvalidArgumentException("Unknown template type: {$templateType}");
            }

            Log::info('[WorkflowTemplate] Executing template', [
                'template' => $templateType,
                'character_id' => $character->id,
                'career_id' => $career?->id,
            ]);

            // Build unified context
            $context = $this->contextService->buildUnifiedContext(
                $character,
                $career,
                null,
                null,
                $additionalContext
            );

            // Create workflow from template
            $templateName = is_string($template['name']) ? $template['name'] : 'Unknown';
            $templatePattern = is_string($template['pattern']) ? $template['pattern'] : AgentOrchestrationService::PATTERN_SEQUENTIAL;
            /** @var array<string> $templateAgents */
            $templateAgents = is_array($template['agents']) ? $template['agents'] : [];
            $workflow = $this->orchestration->createWorkflow(
                $templateName,
                $templatePattern,
                $this->buildAgentConfigs($templateAgents),
                ['template' => $templateType]
            );

            // Execute workflow
            $workflowId = is_string($workflow['id']) ? $workflow['id'] : '';
            $result = $this->orchestration->executeWorkflow($workflowId, $context);

            // Post-process results
            $processedResult = $this->postProcessTemplateResult($templateType, $result);

            Log::info('[WorkflowTemplate] Template execution completed', [
                'template' => $templateType,
                'workflow_id' => $workflow['id'],
                'execution_time' => $result['processing_time_ms'] ?? 0,
            ]);

            return [
                'template' => $templateType,
                'workflow_id' => $workflow['id'],
                'result' => $processedResult,
                'execution_time' => $result['processing_time_ms'] ?? 0,
                'executed_at' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            Log::error('[WorkflowTemplate] Template execution failed', [
                'template' => $templateType,
                'character_id' => $character->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Execute training optimization template
     *
     * @param  array<string, mixed>  $trainingOptions
     * @return array<string, mixed>
     */
    public function executeTrainingOptimization(
        \App\Models\Character $character,
        ?Career $career = null,
        array $trainingOptions = []
    ): array {
        return $this->executeTemplate(
            self::TEMPLATE_TRAINING_OPTIMIZATION,
            $character,
            $career,
            ['training_options' => $trainingOptions]
        );
    }

    /**
     * Execute race preparation template
     *
     * @param  array<string, mixed>  $upcomingRace
     * @return array<string, mixed>
     */
    public function executeRacePreparation(
        \App\Models\Character $character,
        ?Career $career = null,
        array $upcomingRace = []
    ): array {
        return $this->executeTemplate(
            self::TEMPLATE_RACE_PREPARATION,
            $character,
            $career,
            ['upcoming_race' => $upcomingRace]
        );
    }

    /**
     * Execute skill planning template
     *
     * @param  array<string, mixed>  $availableSkills
     * @return array<string, mixed>
     */
    public function executeSkillPlanning(
        \App\Models\Character $character,
        array $availableSkills = []
    ): array {
        return $this->executeTemplate(
            self::TEMPLATE_SKILL_PLANNING,
            $character,
            null,
            ['available_skills' => $availableSkills]
        );
    }

    /**
     * Execute career strategy template
     *
     * @param  array<string, mixed>  $goals
     * @return array<string, mixed>
     */
    public function executeCareerStrategy(
        \App\Models\Character $character,
        array $goals = []
    ): array {
        return $this->executeTemplate(
            self::TEMPLATE_CAREER_STRATEGY,
            $character,
            null,
            ['goals' => $goals]
        );
    }

    /**
     * Execute turn decision template
     *
     * @param  array<string, mixed>  $availableActions
     * @return array<string, mixed>
     */
    public function executeTurnDecision(
        \App\Models\Character $character,
        ?Career $career = null,
        array $availableActions = []
    ): array {
        return $this->executeTemplate(
            self::TEMPLATE_TURN_DECISION,
            $character,
            $career,
            ['available_actions' => $availableActions]
        );
    }

    /**
     * Create custom workflow template
     *
     * @param  array<string>  $agents
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    public function createCustomTemplate(
        string $name,
        string $description,
        array $agents,
        string $pattern,
        array $config = []
    ): array {
        try {
            $templateId = $this->generateTemplateId($name);

            $template = [
                'id' => $templateId,
                'name' => $name,
                'description' => $description,
                'agents' => $agents,
                'pattern' => $pattern,
                'config' => $config,
                'is_custom' => true,
                'created_at' => now()->toIso8601String(),
            ];

            // Store custom template
            Cache::put("custom_template:{$templateId}", $template, 86400);

            Log::info('[WorkflowTemplate] Custom template created', [
                'template_id' => $templateId,
                'name' => $name,
            ]);

            return $template;
        } catch (\Exception $e) {
            Log::error('[WorkflowTemplate] Failed to create custom template', [
                'name' => $name,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get template by type
     *
     * @return array<string, mixed>|null
     */
    protected function getTemplate(string $templateType): ?array
    {
        $templates = $this->getAvailableTemplates();

        return $templates[$templateType] ?? null;
    }

    /**
     * Build agent configurations from agent names
     *
     * @param  array<string>  $agentNames
     * @return array<int, array{id: string, type: string, config: array<string, mixed>}>
     */
    protected function buildAgentConfigs(array $agentNames): array
    {
        $configs = [];

        foreach ($agentNames as $agentName) {
            $configs[] = [
                'id' => uniqid('agent_'),
                'type' => $agentName,
                'config' => [],
            ];
        }

        return $configs;
    }

    /**
     * Post-process template results
     *
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
    protected function postProcessTemplateResult(string $templateType, array $result): array
    {
        // Add template-specific formatting and insights
        $processed = $result;

        $processed['template_type'] = $templateType;
        $processed['summary'] = $this->generateResultSummary($templateType, $result);
        $processed['recommendations'] = $this->extractRecommendations($result);
        $processed['confidence'] = $this->calculateConfidence($result);

        return $processed;
    }

    /**
     * Generate result summary
     *
     * @param  array<string, mixed>  $result
     */
    protected function generateResultSummary(string $templateType, array $result): string
    {
        return match ($templateType) {
            self::TEMPLATE_TRAINING_OPTIMIZATION => 'Training optimization analysis completed with multi-agent recommendations.',
            self::TEMPLATE_RACE_PREPARATION => 'Race preparation analysis completed with strategy recommendations.',
            self::TEMPLATE_SKILL_PLANNING => 'Skill planning analysis completed with SP optimization strategy.',
            self::TEMPLATE_CAREER_STRATEGY => 'Career strategy analysis completed with long-term planning recommendations.',
            self::TEMPLATE_COMPREHENSIVE_ANALYSIS => 'Comprehensive analysis completed across all aspects of character development.',
            self::TEMPLATE_TURN_DECISION => 'Turn decision analysis completed with immediate action recommendations.',
            self::TEMPLATE_GOAL_PLANNING => 'Goal planning analysis completed with achievement timeline.',
            default => 'Workflow template execution completed.',
        };
    }

    /**
     * Extract recommendations from results
     *
     * @param  array<string, mixed>  $result
     * @return array<int, mixed>
     */
    protected function extractRecommendations(array $result): array
    {
        $recommendations = [];

        if (isset($result['results']) && is_array($result['results'])) {
            foreach ($result['results'] as $agentResult) {
                if (is_array($agentResult) && isset($agentResult['output']) && is_array($agentResult['output']) && isset($agentResult['output']['recommendations'])) {
                    $recommendations[] = $agentResult['output']['recommendations'];
                }
            }
        }

        return $recommendations;
    }

    /**
     * Calculate confidence score
     *
     * @param  array<string, mixed>  $result
     */
    protected function calculateConfidence(array $result): float
    {
        // Simple confidence calculation based on agent agreement
        if (! isset($result['results']) || empty($result['results']) || ! is_array($result['results'])) {
            return 0.5;
        }

        $successfulAgents = count(array_filter(
            $result['results'],
            fn ($r) => is_array($r) && ($r['state'] ?? null) === AgentOrchestrationService::STATE_COMPLETED
        ));

        $totalAgents = count($result['results']);

        return $totalAgents > 0 ? $successfulAgents / $totalAgents : 0.5;
    }

    /**
     * Helper methods
     */
    protected function generateTemplateId(string $name): string
    {
        return 'template_'.md5($name.microtime(true));
    }
}
