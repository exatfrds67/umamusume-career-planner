<?php

namespace App\Services\AI\Agents;

use App\Models\Character;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Agent Orchestration Service
 *
 * Coordinates multiple MCP-powered agents for collaborative workflows,
 * context sharing, and multi-agent task execution.
 *
 * Requirements: 13.2, 13.3, 56.3
 */
class AgentOrchestrationService
{
    protected MCPClientService $mcpClient;

    protected TrainingOptimizationAgent $trainingAgent;

    protected CareerStrategyAgent $careerAgent;

    protected RaceAnalysisAgent $raceAgent;

    protected SkillManagementAgent $skillAgent;

    protected bool $enabled;

    /** @var array<string, mixed> */
    protected array $config;

    public function __construct(
        MCPClientService $mcpClient,
        TrainingOptimizationAgent $trainingAgent,
        CareerStrategyAgent $careerAgent,
        RaceAnalysisAgent $raceAgent,
        SkillManagementAgent $skillAgent
    ) {
        $this->mcpClient = $mcpClient;
        $this->trainingAgent = $trainingAgent;
        $this->careerAgent = $careerAgent;
        $this->raceAgent = $raceAgent;
        $this->skillAgent = $skillAgent;

        $this->enabled = (bool) Config::get('ai.agents.orchestration.enabled', true);
        $config = Config::get('ai.agents.orchestration', []);
        $this->config = \is_array($config) ? $config : [];
    }

    /**
     * Execute comprehensive career analysis using multiple agents
     *
     * @param  array<string, mixed>  $goals
     * @return array{
     *     career_plan: array<string, mixed>,
     *     training_recommendations: array<string, mixed>,
     *     race_strategy: array<string, mixed>,
     *     skill_plan: array<string, mixed>,
     *     workflow: array<int, array{agent: string, status: string}>,
     *     confidence: float,
     *     metadata: array<string, mixed>
     * }
     */
    public function executeComprehensiveAnalysis(Character $character, array $goals = []): array
    {
        if (! $this->enabled) {
            return $this->getDefaultAnalysis($character);
        }

        $startTime = microtime(true);
        $workflow = [];

        try {
            // Step 1: Career Strategy Agent - Create overall plan
            $workflow[] = ['agent' => 'career_strategy', 'status' => 'started'];
            $careerPlan = \call_user_func([$this->careerAgent, 'createCareerPlan'], $character, $goals);
            $workflow[] = ['agent' => 'career_strategy', 'status' => 'completed'];

            // Step 2: Training Optimization Agent - Optimize training based on career plan
            $workflow[] = ['agent' => 'training_optimization', 'status' => 'started'];
            $trainingContext = [
                'career_plan' => $careerPlan,
                'goals' => $goals,
            ];
            $trainingRecommendations = \call_user_func(
                [$this->trainingAgent, 'optimizeTrainingSequence'],
                $character,
                10, // Next 10 turns
                $trainingContext
            );
            $workflow[] = ['agent' => 'training_optimization', 'status' => 'completed'];

            // Step 3: Race Analysis Agent - Analyze upcoming races
            $workflow[] = ['agent' => 'race_analysis', 'status' => 'started'];
            $raceStrategy = \call_user_func(
                [$this->raceAgent, 'recommendRaceStrategy'],
                $character,
                ['schedule' => $careerPlan['race_schedule'] ?? []]
            );
            $workflow[] = ['agent' => 'race_analysis', 'status' => 'completed'];

            // Step 4: Skill Management Agent - Optimize skill acquisition
            $workflow[] = ['agent' => 'skill_management', 'status' => 'started'];
            $skillPlan = \call_user_func([$this->skillAgent, 'recommendSkillBuild'], $character, $goals);
            $workflow[] = ['agent' => 'skill_management', 'status' => 'completed'];

            // Calculate overall confidence
            $confidence = $this->calculateOverallConfidence([
                (float) ($careerPlan['confidence'] ?? 0.8),
                (float) ($trainingRecommendations['confidence'] ?? 0.8),
                (float) ($raceStrategy['confidence'] ?? 0.8),
                (float) ($skillPlan['confidence'] ?? 0.8),
            ]);

            return [
                'career_plan' => $careerPlan,
                'training_recommendations' => $trainingRecommendations,
                'race_strategy' => $raceStrategy,
                'skill_plan' => $skillPlan,
                'workflow' => $workflow,
                'confidence' => $confidence,
                'metadata' => [
                    'processing_time' => microtime(true) - $startTime,
                    'agents_used' => 4,
                    'character_id' => $character->id,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('[AgentOrchestration] Comprehensive analysis failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
                'workflow' => $workflow,
            ]);

            return $this->getDefaultAnalysis($character);
        }
    }

    /**
     * Execute parallel agent workflow
     *
     * @param  array<string, array<string, mixed>>  $tasks
     * @return array{
     *     results: array<string, mixed>,
     *     workflow: array<int, array{task: string, status: string}>,
     *     confidence: float,
     *     metadata?: array{processing_time: float, tasks_executed: int}
     * }
     */
    public function executeParallelWorkflow(Character $character, array $tasks): array
    {
        $startTime = microtime(true);
        $results = [];
        $workflow = [];

        try {
            foreach ($tasks as $taskName => $taskConfig) {
                $workflow[] = ['task' => $taskName, 'status' => 'started'];

                $result = match ($taskConfig['agent'] ?? '') {
                    'training' => $this->executeTrainingTask($character, $taskConfig),
                    'career' => $this->executeCareerTask($character, $taskConfig),
                    'race' => $this->executeRaceTask($character, $taskConfig),
                    'skill' => $this->executeSkillTask($character, $taskConfig),
                    default => ['error' => 'Unknown agent type'],
                };

                $results[$taskName] = $result;
                $workflow[] = ['task' => $taskName, 'status' => 'completed'];
            }

            return [
                'results' => $results,
                'workflow' => $workflow,
                'confidence' => $this->calculateWorkflowConfidence($results),
                'metadata' => [
                    'processing_time' => microtime(true) - $startTime,
                    'tasks_executed' => count($tasks),
                ],
            ];
        } catch (\Exception $e) {
            Log::error('[AgentOrchestration] Parallel workflow failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
            ]);

            return [
                'results' => $results,
                'workflow' => $workflow,
                'confidence' => 0.5,
            ];
        }
    }

    /**
     * Execute sequential agent workflow with context sharing
     *
     * @param  array<int, array<string, mixed>>  $steps
     * @return array{
     *     results: array<int, mixed>,
     *     shared_context: array<string, mixed>,
     *     workflow: array<int, array<string, mixed>>,
     *     confidence: float,
     *     metadata?: array{processing_time: float, steps_executed: int}
     * }
     */
    public function executeSequentialWorkflow(Character $character, array $steps): array
    {
        $startTime = microtime(true);
        $results = [];
        $sharedContext = [];
        $workflow = [];

        try {
            foreach ($steps as $index => $step) {
                $workflow[] = ['step' => $index, 'agent' => $step['agent'] ?? 'unknown', 'status' => 'started'];

                // Add shared context from previous steps
                $step['shared_context'] = $sharedContext;

                $result = match ($step['agent'] ?? '') {
                    'training' => $this->executeTrainingTask($character, $step),
                    'career' => $this->executeCareerTask($character, $step),
                    'race' => $this->executeRaceTask($character, $step),
                    'skill' => $this->executeSkillTask($character, $step),
                    default => ['error' => 'Unknown agent type'],
                };

                $results[] = $result;
                $workflow[] = ['step' => $index, 'agent' => $step['agent'] ?? 'unknown', 'status' => 'completed'];

                // Update shared context
                if (isset($result['context_updates']) && is_array($result['context_updates'])) {
                    $sharedContext = array_merge($sharedContext, $result['context_updates']);
                }
            }

            return [
                'results' => $results,
                'shared_context' => $sharedContext,
                'workflow' => $workflow,
                'confidence' => $this->calculateWorkflowConfidence($results),
                'metadata' => [
                    'processing_time' => microtime(true) - $startTime,
                    'steps_executed' => count($steps),
                ],
            ];
        } catch (\Exception $e) {
            Log::error('[AgentOrchestration] Sequential workflow failed', [
                'error' => $e->getMessage(),
                'character_id' => $character->id,
            ]);

            return [
                'results' => $results,
                'shared_context' => $sharedContext,
                'workflow' => $workflow,
                'confidence' => 0.5,
            ];
        }
    }

    /**
     * Execute training task
     *
     * @param  array<string, mixed>  $taskConfig
     * @return array<string, mixed>
     */
    protected function executeTrainingTask(Character $character, array $taskConfig): array
    {
        $action = $taskConfig['action'] ?? 'analyze';
        /** @var array<string, mixed> $trainingOptions */
        $trainingOptions = isset($taskConfig['training_options']) && is_array($taskConfig['training_options']) ? $taskConfig['training_options'] : [];
        /** @var array<string, mixed> $goals */
        $goals = isset($taskConfig['goals']) && is_array($taskConfig['goals']) ? $taskConfig['goals'] : [];
        /** @var array<string, mixed> $trainingOption */
        $trainingOption = isset($taskConfig['training_option']) && is_array($taskConfig['training_option']) ? $taskConfig['training_option'] : [];
        /** @var int $turns */
        $turns = isset($taskConfig['turns']) && is_int($taskConfig['turns']) ? $taskConfig['turns'] : 10;

        return match ($action) {
            'analyze' => \call_user_func(
                [$this->trainingAgent, 'analyzeTrainingOptions'],
                $character,
                $trainingOptions,
                $goals
            ),
            'predict' => \call_user_func(
                [$this->trainingAgent, 'predictStatGains'],
                $character,
                $trainingOption
            ),
            'optimize_sequence' => \call_user_func(
                [$this->trainingAgent, 'optimizeTrainingSequence'],
                $character,
                $turns,
                $goals
            ),
            default => ['error' => 'Unknown training action'],
        };
    }

    /**
     * Execute career task
     *
     * @param  array<string, mixed>  $taskConfig
     * @return array<string, mixed>
     */
    protected function executeCareerTask(Character $character, array $taskConfig): array
    {
        $action = $taskConfig['action'] ?? 'plan';
        /** @var array<string, mixed> $goals */
        $goals = isset($taskConfig['goals']) && is_array($taskConfig['goals']) ? $taskConfig['goals'] : [];
        /** @var array<string, mixed> $constraints */
        $constraints = isset($taskConfig['constraints']) && is_array($taskConfig['constraints']) ? $taskConfig['constraints'] : [];
        /** @var array<string, mixed> $milestones */
        $milestones = isset($taskConfig['milestones']) && is_array($taskConfig['milestones']) ? $taskConfig['milestones'] : [];

        return match ($action) {
            'plan' => \call_user_func(
                [$this->careerAgent, 'createCareerPlan'],
                $character,
                $goals
            ),
            'optimize_goals' => \call_user_func(
                [$this->careerAgent, 'optimizeGoalPriorities'],
                $character,
                $goals
            ),
            'schedule_races' => \call_user_func(
                [$this->careerAgent, 'generateRaceSchedule'],
                $character,
                $constraints
            ),
            'track_milestones' => \call_user_func(
                [$this->careerAgent, 'trackMilestoneProgress'],
                $character,
                $milestones
            ),
            default => ['error' => 'Unknown career action'],
        };
    }

    /**
     * Execute race task
     *
     * @param  array<string, mixed>  $taskConfig
     * @return array<string, mixed>
     */
    protected function executeRaceTask(Character $character, array $taskConfig): array
    {
        $action = $taskConfig['action'] ?? 'analyze';
        /** @var array<string, mixed> $raceDetails */
        $raceDetails = isset($taskConfig['race_details']) && is_array($taskConfig['race_details']) ? $taskConfig['race_details'] : [];
        /** @var array<string, mixed> $strategy */
        $strategy = isset($taskConfig['strategy']) && is_array($taskConfig['strategy']) ? $taskConfig['strategy'] : [];
        /** @var array<string, mixed> $raceResult */
        $raceResult = isset($taskConfig['race_result']) && is_array($taskConfig['race_result']) ? $taskConfig['race_result'] : [];

        return match ($action) {
            'analyze' => \call_user_func(
                [$this->raceAgent, 'analyzeRacePreparation'],
                $character,
                $raceDetails
            ),
            'predict' => \call_user_func(
                [$this->raceAgent, 'predictRacePerformance'],
                $character,
                $raceDetails,
                $strategy
            ),
            'recommend_strategy' => \call_user_func(
                [$this->raceAgent, 'recommendRaceStrategy'],
                $character,
                $raceDetails
            ),
            'post_race_analysis' => \call_user_func(
                [$this->raceAgent, 'analyzePostRacePerformance'],
                $character,
                $raceResult
            ),
            default => ['error' => 'Unknown race action'],
        };
    }

    /**
     * Execute skill task
     *
     * @param  array<string, mixed>  $taskConfig
     * @return array<string, mixed>
     */
    protected function executeSkillTask(Character $character, array $taskConfig): array
    {
        $action = $taskConfig['action'] ?? 'optimize';
        /** @var array<string, mixed> $availableSkills */
        $availableSkills = isset($taskConfig['available_skills']) && is_array($taskConfig['available_skills']) ? $taskConfig['available_skills'] : [];
        /** @var array<string, mixed> $goals */
        $goals = isset($taskConfig['goals']) && is_array($taskConfig['goals']) ? $taskConfig['goals'] : [];
        /** @var array<string, mixed> $targetSkills */
        $targetSkills = isset($taskConfig['target_skills']) && is_array($taskConfig['target_skills']) ? $taskConfig['target_skills'] : [];
        /** @var array<string, mixed> $currentSkills */
        $currentSkills = isset($taskConfig['current_skills']) && is_array($taskConfig['current_skills']) ? $taskConfig['current_skills'] : [];
        /** @var array<string, mixed> $skills */
        $skills = isset($taskConfig['skills']) && is_array($taskConfig['skills']) ? $taskConfig['skills'] : [];

        return match ($action) {
            'optimize' => \call_user_func(
                [$this->skillAgent, 'optimizeSPAllocation'],
                $character,
                $availableSkills,
                $goals
            ),
            'hint_strategy' => \call_user_func(
                [$this->skillAgent, 'generateHintCollectionStrategy'],
                $character,
                $targetSkills
            ),
            'evolution_plan' => \call_user_func(
                [$this->skillAgent, 'planSkillEvolution'],
                $character,
                $currentSkills
            ),
            'recommend_build' => \call_user_func(
                [$this->skillAgent, 'recommendSkillBuild'],
                $character,
                $goals
            ),
            'analyze_synergies' => \call_user_func(
                [$this->skillAgent, 'analyzeSkillSynergies'],
                $character,
                $skills
            ),
            default => ['error' => 'Unknown skill action'],
        };
    }

    /**
     * Calculate overall confidence from multiple agent results
     *
     * @param  array<int, float>  $confidences
     */
    protected function calculateOverallConfidence(array $confidences): float
    {
        if (empty($confidences)) {
            return 0.5;
        }

        // Use weighted average (can be adjusted based on agent importance)
        return array_sum($confidences) / count($confidences);
    }

    /**
     * Calculate workflow confidence from results
     *
     * @param  array<string|int, mixed>  $results
     */
    protected function calculateWorkflowConfidence(array $results): float
    {
        /** @var array<int, float> $confidences */
        $confidences = [];

        foreach ($results as $result) {
            if (is_array($result) && isset($result['confidence'])) {
                // @phpstan-ignore-next-line cast.double - Safe cast of confidence values from dynamic API responses
                $confidences[] = (float) $result['confidence'];
            }
        }

        return $this->calculateOverallConfidence($confidences);
    }

    /**
     * Get default analysis when orchestration is unavailable
     *
     * @return array{
     *     career_plan: array<string, mixed>,
     *     training_recommendations: array<string, mixed>,
     *     race_strategy: array<string, mixed>,
     *     skill_plan: array<string, mixed>,
     *     workflow: array<int, array{agent: string, status: string}>,
     *     confidence: float,
     *     metadata: array<string, mixed>
     * }
     */
    protected function getDefaultAnalysis(Character $character): array
    {
        return [
            'career_plan' => [],
            'training_recommendations' => [],
            'race_strategy' => [],
            'skill_plan' => [],
            'workflow' => [],
            'confidence' => 0.5,
            'metadata' => [
                'fallback' => true,
                'reason' => 'Agent orchestration unavailable',
            ],
        ];
    }

    /**
     * Get orchestration status
     *
     * @return array{
     *     enabled: bool,
     *     agents: array<string, array<string, mixed>>,
     *     mcp_status: array<string, mixed>
     * }
     */
    public function getStatus(): array
    {
        return [
            'enabled' => $this->enabled,
            'agents' => [
                'training' => $this->trainingAgent->getStatus(),
                'career' => $this->careerAgent->getStatus(),
                'race' => $this->raceAgent->getStatus(),
                'skill' => $this->skillAgent->getStatus(),
            ],
            'mcp_status' => $this->mcpClient->getAIServicesStatus(),
        ];
    }
}
