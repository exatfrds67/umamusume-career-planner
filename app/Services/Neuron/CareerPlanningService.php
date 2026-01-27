<?php

declare(strict_types=1);

namespace App\Services\Neuron;

use App\Models\Career;
use App\Models\Character;
use App\Neuron\Agents\CareerPlanningAgent;
use App\Neuron\Responses\CareerPlanningResponse;
use Illuminate\Support\Facades\Log;
use NeuronAI\Chat\Messages\UserMessage;

/**
 * Career Planning Service
 *
 * Acts as an intermediary between controllers and the CareerPlanningAgent.
 * Handles context formatting, validation, and response parsing.
 *
 * **Validates: Requirements 7.1, 7.2, 7.3, 7.4**
 */
class CareerPlanningService
{
    /**
     * Create a new Career Planning Service instance.
     */
    public function __construct(
        private Character $characterModel,
        private Career $careerModel
    ) {}

    /**
     * Get career planning guidance for a character.
     *
     * @param  int  $characterId  The character to plan for
     * @param  array<string, mixed>  $planningContext  Planning context and preferences
     * @param  int  $userId  The authenticated user ID
     * @return CareerPlanningResponse Structured planning response
     */
    public function getPlan(int $characterId, array $planningContext, int $userId): CareerPlanningResponse
    {
        $character = $this->characterModel
            ->with(['aptitudes', 'supportCards.supportCard', 'skillAcquisitions.skill'])
            ->findOrFail($characterId);

        $career = $this->resolveCareer($characterId, $planningContext);

        $context = $this->formatCareerContext($character, $career, $planningContext);

        $agent = new CareerPlanningAgent($userId, $characterId, $career?->id);

        try {
            $response = $agent->structured(
                new UserMessage($context),
                CareerPlanningResponse::class,
                3
            );

            if (! $response instanceof CareerPlanningResponse) {
                throw new \RuntimeException('Unexpected response type from agent');
            }

            Log::info('Career planning generated', [
                'character_id' => $characterId,
                'career_id' => $career?->id,
                'user_id' => $userId,
                'milestones' => count($response->milestones),
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('Career planning generation failed', [
                'character_id' => $characterId,
                'career_id' => $career?->id,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get career planning guidance as a streaming response.
     *
     * @param  int  $characterId  The character to plan for
     * @param  array<string, mixed>  $planningContext  Planning context and preferences
     * @param  int  $userId  The authenticated user ID
     * @return \Generator<string>
     */
    public function getPlanStreaming(int $characterId, array $planningContext, int $userId): \Generator
    {
        $character = $this->characterModel
            ->with(['aptitudes', 'supportCards.supportCard', 'skillAcquisitions.skill'])
            ->findOrFail($characterId);

        $career = $this->resolveCareer($characterId, $planningContext);

        $context = $this->formatCareerContext($character, $career, $planningContext);

        $agent = new CareerPlanningAgent($userId, $characterId, $career?->id);

        try {
            foreach ($agent->stream(new UserMessage($context)) as $chunk) {
                yield is_scalar($chunk) ? (string) $chunk : '';
            }

            Log::info('Career planning streamed', [
                'character_id' => $characterId,
                'career_id' => $career?->id,
                'user_id' => $userId,
            ]);
        } catch (\Exception $e) {
            Log::error('Career planning streaming failed', [
                'character_id' => $characterId,
                'career_id' => $career?->id,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            yield 'Error: Unable to generate career plan. Please try again.';
        }
    }

    /**
     * Validate planning context structure.
     *
     * @param  array<string, mixed>  $planningContext
     * @return array<string, string>
     */
    public function validatePlanningContext(array $planningContext): array
    {
        $errors = [];

        if (isset($planningContext['focus_stats'])) {
            if (! is_array($planningContext['focus_stats'])) {
                $errors['focus_stats'] = 'Focus stats must be an array.';
            } else {
                $validStats = ['speed', 'stamina', 'power', 'guts', 'wit'];
                foreach ($planningContext['focus_stats'] as $index => $stat) {
                    if (! in_array($stat, $validStats, true)) {
                        $errors['focus_stats'] = "Invalid focus stat at index {$index}.";

                        break;
                    }
                }
            }
        }

        if (isset($planningContext['goal_horizon_turns']) && (! is_int($planningContext['goal_horizon_turns']) || $planningContext['goal_horizon_turns'] < 1)) {
            $errors['goal_horizon_turns'] = 'Goal horizon turns must be a positive integer.';
        }

        if (isset($planningContext['preferred_races']) && ! is_array($planningContext['preferred_races'])) {
            $errors['preferred_races'] = 'Preferred races must be an array.';
        }

        return $errors;
    }

    /**
     * Parse response for API output.
     *
     * @return array<string, mixed>
     */
    public function parseResponse(CareerPlanningResponse $response): array
    {
        return [
            'summary' => $response->summary,
            'focus_areas' => $response->focusAreas,
            'milestones' => $response->milestones,
            'race_plan' => $response->racePlan,
            'risk_notes' => $response->riskNotes,
            'validation_errors' => $response->validate(),
        ];
    }

    /**
     * Get career planning history for a character.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getPlanHistory(int $characterId, int $userId, int $limit = 10): array
    {
        $agent = new CareerPlanningAgent($userId, $characterId);
        $chatHistory = $agent->getChatHistory();

        $messages = $chatHistory->getMessages();
        if ($limit > 0) {
            $messages = array_slice($messages, -$limit, null, true);
        }

        $history = [];
        foreach ($messages as $message) {
            $history[] = [
                'role' => $message->getRole(),
                'content' => $message->getContent(),
                'timestamp' => $message->getMetadata('timestamp') ?? null,
            ];
        }

        return $history;
    }

    /**
     * Build formatted career planning context.
     *
     * @param  array<string, mixed>  $planningContext
     */
    protected function formatCareerContext(Character $character, ?Career $career, array $planningContext): string
    {
        $context = "# Career Planning Context\n\n";

        $context .= "## Character Information\n";
        $context .= "- Name: {$character->name}\n";
        $context .= "- Scenario: {$character->scenario_type}\n";
        $context .= "- Career Stage: {$character->career_stage}\n";
        $context .= "- Current Turn: {$character->current_turn}\n";
        $context .= "- Energy Level: {$character->energy_level}/100\n";
        $context .= "- Mood Status: {$character->mood_status}\n\n";

        $context .= "## Current Statistics\n";
        /** @var array<string, int> $currentStats */
        $currentStats = $character->current_stats ?? [];
        foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat) {
            $value = $currentStats[$stat] ?? 0;
            $grade = $character->getStatGrade($value);
            $context .= '- ' . ucfirst($stat) . ": {$value} (Grade: {$grade})\n";
        }
        $context .= "\n";

        if ($career !== null) {
            $context .= "## Career Progress\n";
            $context .= '- Career Name: ' . ($career->career_name ?? 'Unnamed') . "\n";
            $context .= '- Status: ' . ($career->status ?? 'unknown') . "\n";
            $context .= '- Current Turn: ' . ($career->current_turn ?? $character->current_turn) . "\n";
            $context .= '- Current Phase: ' . ($career->current_phase ?? $character->career_stage) . "\n\n";
        }

        if (! empty($character->goals) && is_array($character->goals)) {
            $context .= "## Character Goals\n";
            /** @var array<string, mixed> $goals */
            $goals = $character->goals;
            foreach ($goals as $key => $value) {
                if (is_scalar($value)) {
                    $context .= "- {$key}: {$value}\n";
                }
            }
            $context .= "\n";
        }

        if ($career !== null && ! empty($career->strategic_goals)) {
            $context .= "## Career Strategic Goals\n";
            $strategicGoals = $career->strategic_goals;
            if (is_array($strategicGoals)) {
                foreach ($strategicGoals as $goalKey => $goalValue) {
                    if (is_scalar($goalValue)) {
                        $context .= "- {$goalKey}: {$goalValue}\n";
                    }
                }
            } elseif (is_string($strategicGoals)) {
                $context .= $strategicGoals . "\n";
            }
            $context .= "\n";
        }

        $context .= "## Aptitudes\n";
        if ($character->aptitudes->isEmpty()) {
            $context .= "No aptitude data available.\n\n";
        } else {
            foreach ($character->aptitudes as $aptitude) {
                if ($aptitude->distance_type) {
                    $context .= "- Distance ({$aptitude->distance_type}): {$aptitude->grade}\n";
                }
                if ($aptitude->surface_type) {
                    $context .= "- Surface ({$aptitude->surface_type}): {$aptitude->grade}\n";
                }
                if ($aptitude->running_style) {
                    $context .= "- Running Style ({$aptitude->running_style}): {$aptitude->grade}\n";
                }
            }
            $context .= "\n";
        }

        $context .= "## Support Deck\n";
        if ($character->supportCards->isEmpty()) {
            $context .= "No support cards equipped.\n\n";
        } else {
            foreach ($character->supportCards as $characterCard) {
                $card = $characterCard->supportCard;
                if ($card) {
                    $context .= "- {$card->name} (Type: {$card->card_type}, Rarity: {$card->rarity})\n";
                    $context .= "  Friendship: {$characterCard->friendship_level}/100\n";
                }
            }
            $context .= "\n";
        }

        if ($career !== null) {
            $context .= "## Recent Training Sessions\n";
            $recentTrainings = $career->trainingSessions()
                ->orderByDesc('turn_number')
                ->limit(5)
                ->get();

            if ($recentTrainings->isEmpty()) {
                $context .= "No recent training sessions recorded.\n\n";
            } else {
                foreach ($recentTrainings as $session) {
                    $totalGain = $session->total_stat_points_gained;
                    if ($totalGain === null) {
                        $totalGain = ($session->speed_gain ?? 0)
                            + ($session->stamina_gain ?? 0)
                            + ($session->power_gain ?? 0)
                            + ($session->guts_gain ?? 0)
                            + ($session->wit_gain ?? 0);
                    }
                    $context .= "- Turn {$session->turn_number}: {$session->training_type} (+{$totalGain} total stats)\n";
                }
                $context .= "\n";
            }

            $context .= "## Upcoming Races\n";
            $currentTurn = $career->current_turn ?? $character->current_turn ?? 0;
            $upcomingRaces = $career->races()
                ->where('turn_number', '>=', $currentTurn)
                ->orderBy('turn_number')
                ->limit(5)
                ->get();

            if ($upcomingRaces->isEmpty()) {
                $context .= "No upcoming races scheduled.\n\n";
            } else {
                foreach ($upcomingRaces as $race) {
                    $turnInfo = $race->turn_number ? "Turn {$race->turn_number}" : 'Upcoming';
                    $distance = $race->distance_category ?? 'unknown distance';
                    $surface = $race->surface ?? 'unknown surface';
                    $context .= "- {$race->race_name} ({$turnInfo}, {$distance}, {$surface})\n";
                }
                $context .= "\n";
            }
        }

        if (! empty($planningContext)) {
            $context .= "## Planning Preferences\n";
            if (isset($planningContext['goal_horizon_turns'])) {
                $horizonTurns = is_scalar($planningContext['goal_horizon_turns']) ? (string) $planningContext['goal_horizon_turns'] : 'N/A';
                $context .= "- Goal Horizon: {$horizonTurns} turns\n";
            }
            if (! empty($planningContext['focus_stats']) && is_array($planningContext['focus_stats'])) {
                $stats = implode(', ', $planningContext['focus_stats']);
                $context .= "- Focus Stats: {$stats}\n";
            }
            if (! empty($planningContext['target_grade'])) {
                $targetGrade = is_scalar($planningContext['target_grade']) ? (string) $planningContext['target_grade'] : 'N/A';
                $context .= "- Target Grade: {$targetGrade}\n";
            }
            if (! empty($planningContext['additional_context'])) {
                $additionalContext = is_scalar($planningContext['additional_context']) ? (string) $planningContext['additional_context'] : 'N/A';
                $context .= "- Additional Context: {$additionalContext}\n";
            }
            $context .= "\n";
        }

        return $context;
    }

    /**
     * Resolve the career for planning context.
     *
     * @param  array<string, mixed>  $planningContext
     */
    protected function resolveCareer(int $characterId, array $planningContext): ?Career
    {
        if (isset($planningContext['career_id']) && is_int($planningContext['career_id'])) {
            return $this->careerModel->newQuery()
                ->where('character_id', $characterId)
                ->find($planningContext['career_id']);
        }

        return $this->careerModel->newQuery()
            ->where('character_id', $characterId)
            ->orderByDesc('created_at')
            ->first();
    }
}
