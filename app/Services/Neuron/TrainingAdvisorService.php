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
        private SupportCard $supportCardModel
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
                TrainingAdviceResponse::class,
                new UserMessage($context),
                maxRetry: 3
            );

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
        if (! empty($character->goals['target_stats'])) {
            $context .= "## Target Goals\n";
            foreach ($character->goals['target_stats'] as $stat => $target) {
                $current = $currentStats[$stat] ?? 0;
                $gap = max(0, $target - $current);
                $context .= '- '.ucfirst($stat).": {$current}/{$target} (Gap: {$gap})\n";
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
        if (! empty($trainingOptions['available_trainings'])) {
            foreach ($trainingOptions['available_trainings'] as $training) {
                $type = $training['type'] ?? 'unknown';
                $context .= '- '.ucfirst($type)." Training\n";

                if (! empty($training['support_cards_present'])) {
                    $context .= '  Support Cards Present: '.count($training['support_cards_present'])."\n";
                }

                if (isset($training['energy_cost'])) {
                    $context .= "  Energy Cost: {$training['energy_cost']}\n";
                }

                if (isset($training['failure_risk'])) {
                    $riskPercent = round($training['failure_risk'] * 100);
                    $context .= "  Failure Risk: {$riskPercent}%\n";
                }

                if (! empty($training['expected_gains'])) {
                    $gains = [];
                    foreach ($training['expected_gains'] as $stat => $gain) {
                        $gains[] = ucfirst($stat).": +{$gain}";
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
            if (! empty($character->facility_levels)) {
                $context .= "Facility Levels:\n";
                foreach ($character->facility_levels as $type => $level) {
                    $context .= '- '.ucfirst($type).": Level {$level}\n";
                }
            }
            if (! empty($trainingOptions['spirit_burst_gauge'])) {
                $gauge = $trainingOptions['spirit_burst_gauge'];
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
        if (! empty($trainingOptions['additional_context'])) {
            $context .= "## Additional Context\n";
            $context .= $trainingOptions['additional_context']."\n\n";
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
    public function parseResponse(): array
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
                yield $chunk;
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
    public function validateTrainingOptions(): array
        $errors = [];

        // Check if available_trainings is present and is an array
        if (isset($trainingOptions['available_trainings'])) {
            if (! is_array($trainingOptions['available_trainings'])) {
                $errors['available_trainings'] = 'Must be an array';
            } else {
                // Validate each training option
                foreach ($trainingOptions['available_trainings'] as $index => $training) {
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
    public function getAdviceHistory(): array
        // Create agent to access chat history
        $agent = new TrainingAdvisorAgent($userId, $characterId);
        $chatHistory = $agent->getChatHistory();

        // Get messages from chat history
        $messages = $chatHistory->getMessages();
        if ($limit > 0) {
            $messages = array_slice($messages, -$limit);
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
