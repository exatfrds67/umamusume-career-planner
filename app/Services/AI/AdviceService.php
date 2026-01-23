<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Models\Character;
use App\Services\TrainingCalculationService;
use Illuminate\Support\Facades\Log;

/**
 * AI Advice Service
 *
 * Provides AI-powered training advice using local Ollama models.
 * Injects character stats, turn info, and goals into prompts.
 *
 * Requirements: Task 2.5
 */
class AdviceService
{
    /**
     * Training advice prompt template
     */
    protected const TRAINING_ADVICE_PROMPT = <<<'PROMPT'
You are an expert Uma Musume career advisor. Based on the following character state, provide strategic training advice.

## Character: {name}
- **Scenario**: {scenario_type}
- **Turn**: {current_turn}/72
- **Stage**: {career_stage}

## Current Stats:
- Speed: {speed}
- Stamina: {stamina}
- Power: {power}
- Guts: {guts}
- Wit: {wit}

## Energy: {energy_level}%
## Mood: {mood_status}

## Goals:
{goals}

## Priority Stats:
{stat_priorities}

## Available Training Options:
{training_options}

Please provide:
1. **Recommended Training**: Which training type to choose this turn
2. **Reasoning**: Why this is the optimal choice
3. **Alternative**: A backup option if the main choice fails
4. **Energy Management**: Should the character rest instead?
5. **Long-term Strategy**: How this decision fits the overall career plan

Be concise but thorough. Consider failure risks when energy is low.
PROMPT;

    public function __construct(
        protected OllamaService $ollamaService,
        protected HybridAIService $hybridAIService,
        protected TrainingCalculationService $trainingService
    ) {}

    /**
     * Get training advice for a character
     *
     * @return array{
     *     recommended: string,
     *     reasoning: string,
     *     alternative: string,
     *     should_rest: bool,
     *     strategy: string,
     *     confidence: float,
     *     provider: string
     * }
     */
    public function getTrainingAdvice(): array
        try {
            // Build context from character data
            $context = $this->buildCharacterContext($character);

            // Get training predictions for all types
            $predictions = $this->trainingService->calculateBatchPredictions(
                $character,
                ['speed', 'stamina', 'power', 'guts', 'wit']
            );

            // Build the prompt
            $prompt = $this->buildAdvicePrompt($character, $predictions);

            // Try local Ollama first for privacy
            if ($this->ollamaService->isAvailable()) {
                $response = $this->ollamaService->generate($prompt, $context);

                return $this->parseAdviceResponse($response['content'], 'ollama', $response['confidence']);
            }

            // Fallback to hybrid service (may use Bedrock)
            $response = $this->hybridAIService->processRequest($prompt, $context, $character->id);

            return $this->parseAdviceResponse($response['content'], $response['provider'], $response['confidence']);

        } catch (\Exception $e) {
            Log::error('[AdviceService] Failed to get training advice', [
                'character_id' => $character->id,
                'error' => $e->getMessage(),
            ]);

            // Return rule-based fallback advice
            return $this->getRuleBasedAdvice($character);
        }
    }

    /**
     * Build character context for AI prompt
     *
     * @return array<string, mixed>
     */
    protected function buildCharacterContext(): array
        $stats = $character->current_stats ?? [];

        return [
            'character' => [
                'name' => $character->name,
                'scenario_type' => $character->scenario_type,
                'speed' => $stats['speed'] ?? 0,
                'stamina' => $stats['stamina'] ?? 0,
                'power' => $stats['power'] ?? 0,
                'guts' => $stats['guts'] ?? 0,
                'wit' => $stats['wit'] ?? 0,
            ],
            'career' => [
                'stage' => $character->career_stage ?? 'junior',
                'turn' => $character->current_turn ?? 1,
                'total_turns' => 72,
            ],
            'goals' => array_keys($character->goals['target_stats'] ?? []),
        ];
    }

    /**
     * Build the advice prompt with character data
     *
     * @param  array<string, array<string, mixed>>  $predictions
     */
    protected function buildAdvicePrompt(Character $character, array $predictions): string
    {
        $stats = $character->current_stats ?? [];
        $goals = $character->goals['target_stats'] ?? [];
        $priorities = $character->stat_priorities ?? [];

        // Format goals
        $goalsStr = '';
        foreach ($goals as $stat => $target) {
            $current = $stats[$stat] ?? 0;
            $goalsStr .= "- {$stat}: {$current}/{$target}\n";
        }
        if (empty($goalsStr)) {
            $goalsStr = "No specific goals set.\n";
        }

        // Format priorities
        $prioritiesStr = '';
        arsort($priorities);
        foreach ($priorities as $stat => $priority) {
            $prioritiesStr .= "- {$stat}: Priority {$priority}\n";
        }
        if (empty($prioritiesStr)) {
            $prioritiesStr = "No priorities set.\n";
        }

        // Format training options
        $optionsStr = '';
        foreach ($predictions as $type => $prediction) {
            $gains = $prediction['stat_gains'] ?? [];
            $primaryGain = $gains[$type] ?? 0;
            $energy = $prediction['energy_cost'] ?? 0;
            $risk = ($prediction['failure_risk'] ?? 0) * 100;
            $optionsStr .= "- {$type}: +{$primaryGain} {$type}, Energy: -{$energy}, Risk: {$risk}%\n";
        }

        // Build prompt from template
        $prompt = str_replace(
            [
                '{name}', '{scenario_type}', '{current_turn}', '{career_stage}',
                '{speed}', '{stamina}', '{power}', '{guts}', '{wit}',
                '{energy_level}', '{mood_status}',
                '{goals}', '{stat_priorities}', '{training_options}',
            ],
            [
                $character->name,
                $character->scenario_type ?? 'ura_finale',
                (string) ($character->current_turn ?? 1),
                $character->career_stage ?? 'junior',
                (string) ($stats['speed'] ?? 0),
                (string) ($stats['stamina'] ?? 0),
                (string) ($stats['power'] ?? 0),
                (string) ($stats['guts'] ?? 0),
                (string) ($stats['wit'] ?? 0),
                (string) ($character->energy_level ?? 100),
                $character->mood_status ?? 'normal',
                $goalsStr,
                $prioritiesStr,
                $optionsStr,
            ],
            self::TRAINING_ADVICE_PROMPT
        );

        return $prompt;
    }

    /**
     * Parse AI response into structured advice
     *
     * @return array{recommended: string, reasoning: string, alternative: string, should_rest: bool, strategy: string, confidence: float, provider: string}
     */
    protected function parseAdviceResponse(): array
        // Simple parsing - extract key sections
        $recommended = 'speed';
        $alternative = 'stamina';
        $shouldRest = false;

        // Look for training type mentions
        $trainingTypes = ['speed', 'stamina', 'power', 'guts', 'wit'];
        foreach ($trainingTypes as $type) {
            if (stripos($content, "recommend {$type}") !== false ||
                stripos($content, "choose {$type}") !== false) {
                $recommended = $type;
                break;
            }
        }

        // Check for rest recommendation
        if (stripos($content, 'rest') !== false && stripos($content, 'should rest') !== false) {
            $shouldRest = true;
        }

        return [
            'recommended' => $recommended,
            'reasoning' => $content,
            'alternative' => $alternative,
            'should_rest' => $shouldRest,
            'strategy' => 'Focus on priority stats while managing energy.',
            'confidence' => $confidence,
            'provider' => $provider,
        ];
    }

    /**
     * Get rule-based fallback advice when AI is unavailable
     *
     * @return array{recommended: string, reasoning: string, alternative: string, should_rest: bool, strategy: string, confidence: float, provider: string}
     */
    protected function getRuleBasedAdvice(): array
        $energy = $character->energy_level ?? 100;
        $priorities = $character->stat_priorities ?? [];

        // If energy is too low, recommend rest
        if ($energy < 30) {
            return [
                'recommended' => 'rest',
                'reasoning' => 'Energy is critically low. Rest to avoid training failure.',
                'alternative' => 'speed',
                'should_rest' => true,
                'strategy' => 'Recover energy before continuing training.',
                'confidence' => 0.9,
                'provider' => 'rule_based',
            ];
        }

        // Find highest priority stat
        arsort($priorities);
        $recommended = array_key_first($priorities) ?? 'speed';

        return [
            'recommended' => $recommended,
            'reasoning' => "Train {$recommended} based on current stat priorities.",
            'alternative' => 'stamina',
            'should_rest' => false,
            'strategy' => 'Focus on priority stats to reach target goals.',
            'confidence' => 0.7,
            'provider' => 'rule_based',
        ];
    }
}
