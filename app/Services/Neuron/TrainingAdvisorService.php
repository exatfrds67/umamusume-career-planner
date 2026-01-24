<?php

declare(strict_types=1);

namespace App\Services\Neuron;

use App\Models\Character;
use App\Models\SupportCard;
use App\Models\TrainingSession;
use App\Neuron\Agents\TrainingAdvisorAgent;
use App\Neuron\Responses\TrainingAdviceResponse;
use Illuminate\Support\Facades\Log;
use NeuronAI\Chat\Messages\UserMessage;

/**
 * Training Advisor Service
 *
 * Acts as an intermediary between controllers and the TrainingAdvisorAgent.
 * Handles data formatting for agent consumption and response parsing.
 *
 * **Validates: Requirements 7.1, 7.2, 7.3, 7.4**
 */
class TrainingAdvisorService
{
    /**
     * Create a new Training Advisor Service instance.
     *
     * Injects Eloquent models via constructor for data access.
     */
    public function __construct(
        private Character $characterModel,
        private TrainingSession $trainingSessionModel,
        /** @phpstan-ignore-next-line property.onlyWritten */
        private SupportCard $supportCardModel // Used for potential card-based recommendations
    ) {}

    /**
     * Get training advice for a character.
     *
     * Formats character data, calls the agent, and returns structured advice.
     *
     * @param  int  $characterId  The character to get advice for
     * @param  array<string, mixed>  $trainingOptions  Available training options and context
     * @param  int  $userId  The authenticated user ID
     * @return TrainingAdviceResponse Structured training advice
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If character not found
     */
    public function getAdvice(int $characterId, array $trainingOptions, int $userId): TrainingAdviceResponse
    {
        // Load character with relationships
        $character = $this->characterModel
            ->with(['aptitudes', 'supportCards.supportCard', 'skillAcquisitions'])
            ->findOrFail($characterId);

        // Format data for agent consumption
        $context = $this->formatTrainingContext($character, $trainingOptions);

        // Create agent instance
        $agent = new TrainingAdvisorAgent($userId, $characterId);

        try {
            // Call agent with structured output
            $response = $agent->structured(
                new UserMessage($context),
                TrainingAdviceResponse::class,
                3
            );

            // Ensure response is of correct type
            if (! $response instanceof TrainingAdviceResponse) {
                throw new \RuntimeException('Unexpected response type from agent');
            }

            // Log successful advice generation
            Log::info('Training advice generated', [
                'character_id' => $characterId,
                'user_id' => $userId,
                'recommended_training' => $response->recommendedTraining,
            ]);

            return $response;
        } catch (\Exception $e) {
            // Log error
            Log::error('Training advice generation failed', [
                'character_id' => $characterId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            // Re-throw for controller to handle
            throw $e;
        }
    }

    /**
     * Format training context for agent consumption.
     *
     * Transforms database models into LLM-friendly format with all relevant
     * information for making training recommendations.
     *
     * @param  Character  $character  The character to format
     * @param  array<string, mixed>  $trainingOptions  Available training options
     * @return string Formatted context string
     */
    protected function formatTrainingContext(Character $character, array $trainingOptions): string
    {
        $character->loadMissing([
            'aptitudes',
            'supportCards.supportCard',
            'skillAcquisitions',
        ]);

        $context = "# Training Decision Context\n\n";

        // Character basic info
        $context .= "## Character Information\n";
        $context .= "- Name: {$character->name}\n";
        $context .= "- Scenario: {$character->scenario_type}\n";
        $context .= "- Career Stage: {$character->career_stage}\n";
        $context .= "- Current Turn: {$character->current_turn}\n";
        $context .= "- Energy Level: {$character->energy_level}/100\n";
        $context .= "- Mood Status: {$character->mood_status}\n\n";

        // Current stats
        $context .= "## Current Statistics\n";
        /** @var array<string, int> $currentStats */
        $currentStats = $character->current_stats ?? [];
        foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat) {
            $value = $currentStats[$stat] ?? 0;
            $grade = $character->getStatGrade($value);
            $context .= '- '.ucfirst($stat).": {$value} (Grade: {$grade})\n";
        }
        $context .= "\n";

        // Target goals
        if (! empty($character->goals) && is_array($character->goals) && ! empty($character->goals['target_stats']) && is_array($character->goals['target_stats'])) {
            $context .= "## Target Goals\n";
            /** @var array<string, int|float> $targetStats */
            $targetStats = $character->goals['target_stats'];
            foreach ($targetStats as $stat => $target) {
                if (! is_string($stat)) {
                    continue;
                }
                $targetInt = is_numeric($target) ? (int) $target : 0;
                $current = $currentStats[$stat] ?? 0;
                $gap = max(0, $targetInt - $current);
                $context .= '- '.ucfirst($stat).": {$current}/{$targetInt} (Gap: {$gap})\n";
            }
            $context .= "\n";
        }

        // Aptitudes
        $context .= "## Character Aptitudes\n";
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

        // Support cards
        $context .= "## Support Card Deck\n";
        $supportCards = $character->supportCards;
        if ($supportCards->isEmpty()) {
            $context .= "No support cards equipped.\n\n";
        } else {
            foreach ($supportCards as $characterCard) {
                $card = $characterCard->supportCard;
                if ($card) {
                    $context .= "- {$card->name} (Type: {$card->card_type}, Rarity: {$card->rarity})\n";
                    $context .= "  Friendship: {$characterCard->friendship_level}/100\n";
                    if ($characterCard->limit_break_level > 0) {
                        $context .= "  Limit Break: {$characterCard->limit_break_level}\n";
                    }
                }
            }
            $context .= "\n";
        }

        // Training options
        $context .= "## Available Training Options\n";
        if (! empty($trainingOptions['available_trainings']) && is_array($trainingOptions['available_trainings'])) {
            foreach ($trainingOptions['available_trainings'] as $training) {
                if (! is_array($training)) {
                    continue;
                }
                $type = isset($training['type']) && is_string($training['type']) ? $training['type'] : 'unknown';
                $context .= '- '.ucfirst($type)." Training\n";

                if (! empty($training['support_cards_present']) && is_array($training['support_cards_present'])) {
                    $cardCount = count($training['support_cards_present']);
                    $context .= "  Support Cards Present: {$cardCount}\n";
                }

                if (isset($training['energy_cost']) && is_numeric($training['energy_cost'])) {
                    $energyCost = (string) $training['energy_cost'];
                    $context .= "  Energy Cost: {$energyCost}\n";
                }

                if (isset($training['failure_risk']) && is_numeric($training['failure_risk'])) {
                    $riskPercent = (string) round((float) $training['failure_risk'] * 100);
                    $context .= "  Failure Risk: {$riskPercent}%\n";
                }

                if (! empty($training['expected_gains']) && is_array($training['expected_gains'])) {
                    $gains = [];
                    foreach ($training['expected_gains'] as $stat => $gain) {
                        $statStr = is_string($stat) ? ucfirst($stat) : 'Unknown';
                        $gainStr = is_numeric($gain) ? (string) $gain : '0';
                        $gains[] = "{$statStr}: +{$gainStr}";
                    }
                    $context .= '  Expected Gains: '.implode(', ', $gains)."\n";
                }
            }
        } else {
            $context .= "Standard training options available: Speed, Stamina, Power, Guts, Wit, Rest\n";
        }
        $context .= "\n";

        // Scenario-specific context
        if ($character->scenario_type === 'unity_cup') {
            $context .= "## Unity Cup Specific\n";
            if (! empty($character->facility_levels) && is_array($character->facility_levels)) {
                $context .= "Facility Levels:\n";
                /** @var array<string, int|string> $facilityLevels */
                $facilityLevels = $character->facility_levels;
                foreach ($facilityLevels as $type => $level) {
                    $typeStr = is_string($type) ? $type : (string) $type;
                    $levelStr = is_scalar($level) ? (string) $level : '0';
                    $context .= '- '.ucfirst($typeStr).": Level {$levelStr}\n";
                }
            }
            if (! empty($trainingOptions['spirit_burst_gauge'])) {
                $gauge = is_scalar($trainingOptions['spirit_burst_gauge']) ? (string) $trainingOptions['spirit_burst_gauge'] : '0';
                $context .= "Spirit Burst Gauge: {$gauge}/4\n";
            }
            $context .= "\n";
        }

        // Recent training history
        $recentSessions = $this->trainingSessionModel
            ->where('character_id', $character->id)
            ->orderBy('turn_number', 'desc')
            ->limit(3)
            ->get();

        if ($recentSessions->isNotEmpty()) {
            $context .= "## Recent Training History (Last 3 Turns)\n";
            foreach ($recentSessions as $session) {
                $context .= "- Turn {$session->turn_number}: {$session->training_type} training\n";
                $gains = [];
                foreach ($session->stat_gains as $stat => $gain) {
                    if ($gain > 0) {
                        $gains[] = ucfirst($stat).": +{$gain}";
                    }
                }
                if (! empty($gains)) {
                    $context .= '  Gains: '.implode(', ', $gains)."\n";
                }
            }
            $context .= "\n";
        }

        // Additional context
        if (! empty($trainingOptions['additional_context']) && is_scalar($trainingOptions['additional_context'])) {
            $context .= "## Additional Context\n";
            $additionalCtx = (string) $trainingOptions['additional_context'];
            $context .= $additionalCtx."\n\n";
        }

        $context .= 'Please analyze this information and provide your training recommendation.';

        return $context;
    }

    /**
     * Parse agent response into structured data.
     *
     * The agent already returns a TrainingAdviceResponse object,
     * but this method can be used to extract or transform specific data.
     *
     * @param  TrainingAdviceResponse  $response  The agent response
     * @return array<string, mixed> Parsed response data
     */
    public function parseResponse(TrainingAdviceResponse $response): array
    {
        return [
            'recommended_training' => $response->recommendedTraining,
            'reasoning' => $response->reasoning,
            'expected_gains' => $response->expectedGains,
            'alternatives' => $response->alternatives,
            'summary' => $response->getSummary(),
            'validation_errors' => $response->validate(),
        ];
    }

    /**
     * Get training advice with streaming response.
     *
     * Streams the agent's response in real-time for better UX.
     *
     * @param  int  $characterId  The character to get advice for
     * @param  array<string, mixed>  $trainingOptions  Available training options
     * @param  int  $userId  The authenticated user ID
     * @return \Generator<string> Yields response chunks
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If character not found
     */
    public function getAdviceStreaming(int $characterId, array $trainingOptions, int $userId): \Generator
    {
        // Load character with relationships
        $character = $this->characterModel
            ->with(['aptitudes', 'supportCards.supportCard', 'skillAcquisitions'])
            ->findOrFail($characterId);

        // Format data for agent consumption
        $context = $this->formatTrainingContext($character, $trainingOptions);

        // Create agent instance
        $agent = new TrainingAdvisorAgent($userId, $characterId);

        try {
            // Stream response from agent
            foreach ($agent->stream(new UserMessage($context)) as $chunk) {
                yield is_scalar($chunk) ? (string) $chunk : '';
            }

            // Log successful streaming
            Log::info('Training advice streamed', [
                'character_id' => $characterId,
                'user_id' => $userId,
            ]);
        } catch (\Exception $e) {
            // Log error
            Log::error('Training advice streaming failed', [
                'character_id' => $characterId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            // Yield error message
            yield 'Error: Unable to generate training advice. Please try again.';
        }
    }

    /**
     * Validate training options data structure.
     *
     * Ensures the training options array has the expected structure.
     *
     * @param  array<string, mixed>  $trainingOptions  Training options to validate
     * @return array<string, string> Validation errors (empty if valid)
     */
    public function validateTrainingOptions(array $trainingOptions): array
    {
        $errors = [];

        // Check if available_trainings is present and is an array
        if (isset($trainingOptions['available_trainings'])) {
            if (! is_array($trainingOptions['available_trainings'])) {
                $errors['available_trainings'] = 'Must be an array';
            } else {
                // Validate each training option
                /** @var array<int|string, mixed> $trainings */
                $trainings = $trainingOptions['available_trainings'];
                foreach ($trainings as $index => $training) {
                    if (! is_array($training)) {
                        $errors["available_trainings.{$index}"] = 'Must be an array';

                        continue;
                    }

                    if (! isset($training['type'])) {
                        $errors["available_trainings.{$index}.type"] = 'Training type is required';
                    }
                }
            }
        }

        // Validate spirit_burst_gauge if present
        if (isset($trainingOptions['spirit_burst_gauge'])) {
            $gauge = $trainingOptions['spirit_burst_gauge'];
            if (! is_int($gauge) || $gauge < 0 || $gauge > 4) {
                $errors['spirit_burst_gauge'] = 'Must be an integer between 0 and 4';
            }
        }

        return $errors;
    }

    /**
     * Get recent training advice history for a character.
     *
     * Retrieves past advice from chat history for review.
     *
     * @param  int  $characterId  The character ID
     * @param  int  $userId  The user ID
     * @param  int  $limit  Maximum number of messages to retrieve
     * @return array<int, array<string, mixed>> Array of past advice messages
     */
    public function getAdviceHistory(int $characterId, int $userId, int $limit = 10): array
    {
        // Create agent to access chat history
        $agent = new TrainingAdvisorAgent($userId, $characterId);
        $chatHistory = $agent->getChatHistory();

        // Get messages from chat history
        $messages = $chatHistory->getMessages();
        if ($limit > 0) {
            $messages = array_slice($messages, -$limit, null, true);
        }

        // Format messages for display
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
}
