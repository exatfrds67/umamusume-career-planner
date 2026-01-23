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
     *     metadata: array{processing_time: float, agents_used: int, character_id: int}
     * }
     */
    public function executeComprehensiveAnalysis(): array
        if (! $this->enabled) {
            return $this->getDefaultAnalysis($character);
        }

        $startTime = microtime(true);
        $workflow = [];

        try {
            // Step 1: Career Strategy Agent - Create overall plan
            $workflow[] = ['agent' => 'career_strategy', 'status' => 'started'];
            $careerPlan = $this->careerAgent->createCareerPlan($character, $goals);
            $workflow[] = ['agent' => 'career_strategy', 'status' => 'completed'];

            // Step 2: Training Optimization Agent - Optimize training based on career plan
            $workflow[] = ['agent' => 'training_optimization', 'status' => 'started'];
            $trainingContext = [
                'career_plan' => $careerPlan,
                'goals' => $goals,
            ];
            $trainingRecommendations = $this->trainingAgent->optimizeTrainingSequence(
                $character,
                10, // Next 10 turns
                $trainingContext
            );
            $workflow[] = ['agent' => 'training_optimization', 'status' => 'completed'];

            // Step 3: Race Analysis Agent - Analyze upcoming races
            $workflow[] = ['agent' => 'race_analysis', 'status' => 'started'];
            $raceStrategy = $this->raceAgent->recommendRaceStrategy(
                $character,
                ['schedule' => $careerPlan['race_schedule'] ?? []]
            );
            $workflow[] = ['agent' => 'race_analysis', 'status' => 'completed'];

            // Step 4: Skill Management Agent - Optimize skill acquisition
            $workflow[] = ['agent' => 'skill_management', 'status' => 'started'];
            $skillPlan = $this->skillAgent->recommendSkillBuild($character, $goals);
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
    public function executeParallelWorkflow(): array
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
     *     workflow: array<int, array{step: int, agent: string, status: string}>,
     *     confidence: float,
     *     metadata?: array{processing_time: float, steps_executed: int}
     * }
     */
    public function executeSequentialWorkflow(): array
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
                if (isset($result['context_updates'])) {
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
    protected function executeTrainingTask(): array
        $action = $taskConfig['action'] ?? 'analyze';

        return match ($action) {
            'analyze' => $this->trainingAgent->analyzeTrainingOptions(
                $character,
                $taskConfig['training_options'] ?? [],
                $taskConfig['goals'] ?? []
            ),
            'predict' => $this->trainingAgent->predictStatGains(
                $character,
                $taskConfig['training_option'] ?? []
            ),
            'optimize_sequence' => $this->trainingAgent->optimizeTrainingSequence(
                $character,
                $taskConfig['turns'] ?? 10,
                $taskConfig['goals'] ?? []
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
    protected function executeCareerTask(): array
        $action = $taskConfig['action'] ?? 'plan';

        return match ($action) {
            'plan' => $this->careerAgent->createCareerPlan(
                $character,
                $taskConfig['goals'] ?? []
            ),
            'optimize_goals' => $this->careerAgent->optimizeGoalPriorities(
                $character,
                $taskConfig['goals'] ?? []
            ),
            'schedule_races' => $this->careerAgent->generateRaceSchedule(
                $character,
                $taskConfig['constraints'] ?? []
            ),
            'track_milestones' => $this->careerAgent->trackMilestoneProgress(
                $character,
                $taskConfig['milestones'] ?? []
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
    protected function executeRaceTask(): array
        $action = $taskConfig['action'] ?? 'analyze';

        return match ($action) {
            'analyze' => $this->raceAgent->analyzeRacePreparation(
                $character,
                $taskConfig['race_details'] ?? []
            ),
            'predict' => $this->raceAgent->predictRacePerformance(
                $character,
                $taskConfig['race_details'] ?? [],
                $taskConfig['strategy'] ?? []
            ),
            'recommend_strategy' => $this->raceAgent->recommendRaceStrategy(
                $character,
                $taskConfig['race_details'] ?? []
            ),
            'post_race_analysis' => $this->raceAgent->analyzePostRacePerformance(
                $character,
                $taskConfig['race_result'] ?? []
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
    protected function executeSkillTask(): array
        $action = $taskConfig['action'] ?? 'optimize';

        return match ($action) {
            'optimize' => $this->skillAgent->optimizeSPAllocation(
                $character,
                $taskConfig['available_skills'] ?? [],
                $taskConfig['goals'] ?? []
            ),
            'hint_strategy' => $this->skillAgent->generateHintCollectionStrategy(
                $character,
                $taskConfig['target_skills'] ?? []
            ),
            'evolution_plan' => $this->skillAgent->planSkillEvolution(
                $character,
                $taskConfig['current_skills'] ?? []
            ),
            'recommend_build' => $this->skillAgent->recommendSkillBuild(
                $character,
                $taskConfig['goals'] ?? []
            ),
            'analyze_synergies' => $this->skillAgent->analyzeSkillSynergies(
                $character,
                $taskConfig['skills'] ?? []
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
        $confidences = [];

        foreach ($results as $result) {
            if (isset($result['confidence'])) {
                $confidences[] = $result['confidence'];
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
    protected function getDefaultAnalysis(): array
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
