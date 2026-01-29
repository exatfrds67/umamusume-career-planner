<?php

declare(strict_types=1);

namespace App\Services\Neuron;

use App\Models\Character;
use App\Models\Race;
use App\Models\Skill;
use App\Neuron\Agents\RaceStrategyAgent;
use App\Neuron\Responses\RaceStrategyResponse;
use App\Services\RaceConditionService;
use Illuminate\Support\Facades\Log;
use NeuronAI\Chat\Messages\UserMessage;

/**
 * Race Strategy Service
 *
 * Acts as an intermediary between controllers and the RaceStrategyAgent.
 * Handles data formatting for agent consumption and response parsing.
 *
 * **Phase 7**: Integrated with RaceConditionService for condition-aware strategies
 *
 * **Validates: Requirements 7.1, 7.2, 7.3, 7.4**
 */
class RaceStrategyService
{
    /**
     * Create a new Race Strategy Service instance.
     *
     * Injects Eloquent models via constructor for data access.
     */
    public function __construct(
        private Character $characterModel,
        private Race $raceModel,
        private Skill $skillModel,
        private RaceConditionService $conditionService
    ) {}

    /**
     * Get race strategy advice for a character and race.
     *
     * Formats race and character data, calls the agent, and returns structured strategy.
     *
     * @param  int  $characterId  The character to get strategy for
     * @param  array<string, mixed>  $raceData  Race information and context
     * @param  int  $userId  The authenticated user ID
     * @return RaceStrategyResponse Structured race strategy
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If character not found
     */
    public function getStrategy(int $characterId, array $raceData, int $userId): RaceStrategyResponse
    {
        // Load character with relationships
        $character = $this->characterModel
            ->with(['aptitudes', 'skillAcquisitions.skill', 'supportCards.supportCard'])
            ->findOrFail($characterId);

        // Format data for agent consumption
        $context = $this->formatRaceContext($character, $raceData);

        // Determine race ID for session scoping
        $raceId = isset($raceData['race_id']) && is_int($raceData['race_id']) ? $raceData['race_id'] : null;

        // Create agent instance
        $agent = new RaceStrategyAgent($userId, $raceId);

        try {
            // Call agent with structured output
            $response = $agent->structured(
                new UserMessage($context),
                RaceStrategyResponse::class,
                3
            );

            // Ensure response is of correct type
            if (! $response instanceof RaceStrategyResponse) {
                throw new \RuntimeException('Unexpected response type from agent');
            }

            // Log successful strategy generation
            Log::info('Race strategy generated', [
                'character_id' => $characterId,
                'user_id' => $userId,
                'race_name' => $raceData['race_name'] ?? 'unknown',
                'recommended_running_style' => $response->recommendedRunningStyle,
            ]);

            return $response;
        } catch (\Exception $e) {
            // Log error
            Log::error('Race strategy generation failed', [
                'character_id' => $characterId,
                'user_id' => $userId,
                'race_name' => $raceData['race_name'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            // Re-throw for controller to handle
            throw $e;
        }
    }

    /**
     * Format race context for agent consumption.
     *
     * Transforms database models into LLM-friendly format with all relevant
     * information for making race strategy recommendations.
     *
     * @param  Character  $character  The character to format
     * @param  array<string, mixed>  $raceData  Race information
     * @return string Formatted context string
     */
    protected function formatRaceContext(Character $character, array $raceData): string
    {
        // Ensure required relationships are loaded to avoid lazy loading violations in tests
        $character->loadMissing(['aptitudes', 'skillAcquisitions.skill', 'supportCards.supportCard']);

        $context = "# Race Strategy Context\n\n";

        // Race information
        $context .= "## Race Information\n";
        $raceName = isset($raceData['race_name']) && is_scalar($raceData['race_name']) ? (string) $raceData['race_name'] : 'Unknown Race';
        $raceGrade = isset($raceData['race_grade']) && is_scalar($raceData['race_grade']) ? (string) $raceData['race_grade'] : 'Unknown';
        $distanceMeters = isset($raceData['distance_meters']) && is_scalar($raceData['distance_meters']) ? (string) $raceData['distance_meters'] : 'Unknown';
        $distanceCategory = isset($raceData['distance_category']) && is_scalar($raceData['distance_category']) ? (string) $raceData['distance_category'] : 'Unknown';
        $surfaceStr = isset($raceData['surface']) && is_scalar($raceData['surface']) ? (string) $raceData['surface'] : 'Unknown';
        $trackType = isset($raceData['track_type']) && is_scalar($raceData['track_type']) ? (string) $raceData['track_type'] : 'Unknown';
        $context .= "- Race Name: {$raceName}\n";
        $context .= "- Grade: {$raceGrade}\n";
        $context .= "- Distance: {$distanceMeters} meters\n";
        $context .= "- Distance Category: {$distanceCategory}\n";
        $context .= "- Surface: {$surfaceStr}\n";
        $context .= "- Track Type: {$trackType}\n";

        if (isset($raceData['weather']) && is_scalar($raceData['weather'])) {
            $weather = (string) $raceData['weather'];
            $context .= "- Weather: {$weather}\n";
        }

        if (isset($raceData['track_condition']) && is_scalar($raceData['track_condition'])) {
            $trackCondition = (string) $raceData['track_condition'];
            $context .= "- Track Condition: {$trackCondition}\n";
        }

        if (isset($raceData['field_size'])) {
            $fieldSize = is_scalar($raceData['field_size']) ? (string) $raceData['field_size'] : 'Unknown';
            $context .= "- Field Size: {$fieldSize} horses\n";
        }

        $context .= "\n";

        // Add condition analysis if weather/track data available
        if (isset($raceData['track_condition'], $raceData['surface'])) {
            $trackCondition = (string) $raceData['track_condition'];
            $surface = (string) $raceData['surface'];

            if (
                $this->conditionService->isValidTrackCondition($trackCondition) &&
                $this->conditionService->isValidSurface($surface)
            ) {
                $context .= "## Track Condition Analysis\n";

                // Get condition impact
                $impactDescription = $this->conditionService->getConditionImpactDescription($trackCondition, $surface);
                $impactScore = $this->conditionService->calculatePerformanceImpact($trackCondition, $surface);
                $isWet = $this->conditionService->isWetCondition($trackCondition);
                $severity = $this->conditionService->getConditionSeverity($trackCondition);

                $context .= "- Impact: {$impactDescription}\n";
                $context .= "- Performance Score: {$impactScore}/100\n";
                $context .= '- Condition Type: '.($isWet ? 'Wet' : 'Dry')."\n";
                $context .= "- Severity Level: {$severity}/3\n";

                // Get recommended skills
                $weather = isset($raceData['weather']) ? (string) $raceData['weather'] : null;
                $recommendedSkills = $this->conditionService->getRecommendedSkills($weather, $trackCondition);

                if (! empty($recommendedSkills)) {
                    $context .= "- Recommended Skills for Conditions:\n";
                    foreach ($recommendedSkills as $skill) {
                        $context .= "  * {$skill}\n";
                    }
                }

                // Calculate modified stats
                $stats = [
                    'speed' => $currentStats['speed'] ?? 0,
                    'stamina' => $currentStats['stamina'] ?? 0,
                    'power' => $currentStats['power'] ?? 0,
                    'guts' => $currentStats['guts'] ?? 0,
                    'wit' => $currentStats['wit'] ?? 0,
                ];

                $modifiedStats = $this->conditionService->applyConditionPenalties($stats, $trackCondition, $surface);

                $context .= "- Effective Stats (with condition penalties):\n";
                foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat) {
                    $original = $stats[$stat];
                    $modified = $modifiedStats[$stat];
                    $diff = $modified - $original;
                    $diffStr = $diff >= 0 ? "+{$diff}" : (string) $diff;
                    $context .= '  * '.ucfirst($stat).": {$modified} (original: {$original}, {$diffStr})\n";
                }

                $context .= "\n";
            }
        }

        // Character basic info
        $context .= "## Character Information\n";
        $context .= "- Name: {$character->name}\n";
        $context .= "- Scenario: {$character->scenario_type}\n";

        if (isset($character->current_turn)) {
            $context .= "- Current Turn: {$character->current_turn}\n";
        }

        if (isset($character->energy_level)) {
            $context .= "- Energy Level: {$character->energy_level}/100\n";
        }

        if (isset($character->motivation)) {
            $context .= "- Motivation: {$character->motivation}\n";
        }

        if (isset($character->character_condition)) {
            $context .= "- Condition: {$character->character_condition}\n";
        }

        $context .= "\n";

        // Current stats
        $context .= "## Current Statistics\n";
        $currentStats = $character->current_stats ?? [];
        foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat) {
            $value = $currentStats[$stat] ?? 0;
            $grade = $character->getStatGrade($value);
            $context .= '- '.ucfirst($stat).": {$value} (Grade: {$grade})\n";
        }
        $context .= "\n";

        // Aptitudes
        $context .= "## Character Aptitudes\n";
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

        // Acquired skills
        $context .= "## Acquired Skills\n";
        $acquiredSkills = $character->skillAcquisitions;
        if ($acquiredSkills->isEmpty()) {
            $context .= "No skills acquired yet.\n\n";
        } else {
            foreach ($acquiredSkills as $acquisition) {
                $skill = $acquisition->skill;
                if ($skill) {
                    $context .= "- {$skill->name}";

                    if ($skill->skill_type) {
                        $context .= " (Type: {$skill->skill_type})";
                    }

                    if ($acquisition->is_equipped) {
                        $context .= ' [EQUIPPED]';
                    }

                    $context .= "\n";

                    if ($skill->description) {
                        $context .= "  Description: {$skill->description}\n";
                    }
                }
            }
            $context .= "\n";
        }

        // Available skills (if provided)
        if (! empty($raceData['available_skills']) && is_array($raceData['available_skills'])) {
            $context .= "## Available Skills to Equip\n";
            foreach ($raceData['available_skills'] as $skillData) {
                if (! is_array($skillData)) {
                    continue;
                }
                $skillName = isset($skillData['name']) && is_string($skillData['name']) ? $skillData['name'] : 'Unknown Skill';
                $context .= "- {$skillName}";

                if (isset($skillData['skill_type']) && is_string($skillData['skill_type'])) {
                    $skillType = $skillData['skill_type'];
                    $context .= " (Type: {$skillType})";
                }

                if (isset($skillData['description']) && is_string($skillData['description'])) {
                    $skillDescription = $skillData['description'];
                    $context .= "\n  Description: {$skillDescription}";
                }

                $context .= "\n";
            }
            $context .= "\n";
        }

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
                }
            }
            $context .= "\n";
        }

        // Race conditions and requirements
        if (! empty($raceData['race_conditions'])) {
            $context .= "## Race Conditions\n";
            if (\is_array($raceData['race_conditions'])) {
                foreach ($raceData['race_conditions'] as $key => $value) {
                    $valueStr = is_scalar($value) ? (string) $value : 'N/A';
                    $context .= '- '.ucfirst((string) $key).": {$valueStr}\n";
                }
            } elseif (is_scalar($raceData['race_conditions'])) {
                $conditionStr = (string) $raceData['race_conditions'];
                $context .= $conditionStr."\n";
            }
            $context .= "\n";
        }

        // URA Finale specific
        if (! empty($raceData['is_ura_finale_race'])) {
            $context .= "## URA Finale Race\n";
            $context .= "This is a URA Finale race.\n";

            if (! empty($raceData['ura_finale_stage'])) {
                $uraStage = is_scalar($raceData['ura_finale_stage']) ? (string) $raceData['ura_finale_stage'] : 'Unknown';
                $context .= "Stage: {$uraStage}\n";
            }

            if (! empty($raceData['ura_finale_requirements']) && is_array($raceData['ura_finale_requirements'])) {
                $context .= "Requirements:\n";
                foreach ($raceData['ura_finale_requirements'] as $req) {
                    $reqStr = is_scalar($req) ? (string) $req : 'N/A';
                    $context .= "  - {$reqStr}\n";
                }
            }
            $context .= "\n";
        }

        // Unity Cup specific
        if (! empty($raceData['is_unity_cup_match'])) {
            $context .= "## Unity Cup Match\n";
            $context .= "This is a Unity Cup match.\n";

            if (isset($raceData['unity_cup_opponent_rank'])) {
                $opponentRank = is_scalar($raceData['unity_cup_opponent_rank']) ? (string) $raceData['unity_cup_opponent_rank'] : 'Unknown';
                $context .= "Opponent Rank: {$opponentRank}\n";
            }

            $context .= "\n";
        }

        // Past race performance
        if (! empty($raceData['past_race_performance'])) {
            $context .= "## Past Race Performance\n";
            $recentRaces = $this->raceModel->query()
                ->where('character_id', '=', $character->id)
                ->orderBy('turn_number', 'desc')
                ->limit(3)
                ->get();

            if ($recentRaces->isNotEmpty()) {
                foreach ($recentRaces as $race) {
                    $context .= "- {$race->race_name}";

                    if ($race->finish_position) {
                        $context .= " (Finished: {$race->finish_position})";
                    }

                    if ($race->won_race) {
                        $context .= ' [WON]';
                    }

                    $context .= "\n";

                    if ($race->performance_analysis) {
                        $context .= '  Analysis: '.\json_encode($race->performance_analysis)."\n";
                    }
                }
            } else {
                $context .= "No past race data available.\n";
            }
            $context .= "\n";
        }

        // Strategic importance
        if (! empty($raceData['strategic_importance']) && is_scalar($raceData['strategic_importance'])) {
            $context .= "## Strategic Importance\n";
            $importance = (string) $raceData['strategic_importance'];
            $context .= $importance."\n\n";
        }

        // Additional context
        if (! empty($raceData['additional_context']) && is_scalar($raceData['additional_context'])) {
            $context .= "## Additional Context\n";
            $additionalCtx = (string) $raceData['additional_context'];
            $context .= $additionalCtx."\n\n";
        }

        $context .= 'Please analyze this information and provide your race strategy recommendation.';

        return $context;
    }

    /**
     * Parse agent response into structured data.
     *
     * The agent already returns a RaceStrategyResponse object,
     * but this method can be used to extract or transform specific data.
     *
     * @param  RaceStrategyResponse  $response  The agent response
     * @return array<string, mixed> Parsed response data
     */
    public function parseResponse(RaceStrategyResponse $response): array
    {
        return [
            'recommended_running_style' => $response->recommendedRunningStyle,
            'recommended_skills' => $response->recommendedSkills,
            'race_preparation_advice' => $response->racePreparationAdvice,
            'expected_performance' => $response->expectedPerformance,
            'risk_factors' => $response->riskFactors,
            'summary' => $response->getSummary(),
            'validation_errors' => $response->validate(),
        ];
    }

    /**
     * Get race strategy with streaming response.
     *
     * Streams the agent's response in real-time for better UX.
     *
     * @param  int  $characterId  The character to get strategy for
     * @param  array<string, mixed>  $raceData  Race information
     * @param  int  $userId  The authenticated user ID
     * @return \Generator<string> Yields response chunks
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If character not found
     */
    public function getStrategyStreaming(int $characterId, array $raceData, int $userId): \Generator
    {
        // Load character with relationships
        $character = $this->characterModel
            ->with(['aptitudes', 'skillAcquisitions.skill', 'supportCards.supportCard'])
            ->findOrFail($characterId);

        // Format data for agent consumption
        $context = $this->formatRaceContext($character, $raceData);

        // Determine race ID for session scoping
        $raceId = isset($raceData['race_id']) && is_int($raceData['race_id']) ? $raceData['race_id'] : null;

        // Create agent instance
        $agent = new RaceStrategyAgent($userId, $raceId);

        try {
            // Stream response from agent
            foreach ($agent->stream(new UserMessage($context)) as $chunk) {
                yield is_scalar($chunk) ? (string) $chunk : '';
            }

            // Log successful streaming
            Log::info('Race strategy streamed', [
                'character_id' => $characterId,
                'user_id' => $userId,
                'race_name' => $raceData['race_name'] ?? 'unknown',
            ]);
        } catch (\Exception $e) {
            // Log error
            Log::error('Race strategy streaming failed', [
                'character_id' => $characterId,
                'user_id' => $userId,
                'race_name' => $raceData['race_name'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            // Yield error message
            yield 'Error: Unable to generate race strategy. Please try again.';
        }
    }

    /**
     * Validate race data structure.
     *
     * Ensures the race data array has the expected structure.
     *
     * @param  array<string, mixed>  $raceData  Race data to validate
     * @return array<string, string> Validation errors (empty if valid)
     */
    public function validateRaceData(array $raceData): array
    {
        $errors = [];

        // Check required fields
        if (! isset($raceData['race_name']) || ! is_scalar($raceData['race_name']) || trim((string) $raceData['race_name']) === '') {
            $errors['race_name'] = 'Race name is required';
        }

        // Validate distance if present
        if (isset($raceData['distance_meters'])) {
            $distance = $raceData['distance_meters'];
            if (! \is_int($distance) || $distance < 1000 || $distance > 4000) {
                $errors['distance_meters'] = 'Distance must be an integer between 1000 and 4000 meters';
            }
        }

        // Validate race grade if present
        if (isset($raceData['race_grade'])) {
            $validGrades = ['G1', 'G2', 'G3', 'OP', 'Pre-OP', 'Debut', 'Make Debut'];
            if (! \in_array($raceData['race_grade'], $validGrades, true)) {
                $errors['race_grade'] = 'Invalid race grade. Must be one of: '.\implode(', ', $validGrades);
            }
        }

        // Validate surface if present
        if (isset($raceData['surface'])) {
            $validSurfaces = ['turf', 'dirt'];
            if (! \in_array($raceData['surface'], $validSurfaces, true)) {
                $errors['surface'] = 'Invalid surface. Must be one of: '.\implode(', ', $validSurfaces);
            }
        }

        // Validate available skills structure if present
        if (isset($raceData['available_skills'])) {
            if (! \is_array($raceData['available_skills'])) {
                $errors['available_skills'] = 'Must be an array';
            } else {
                foreach ($raceData['available_skills'] as $index => $skill) {
                    if (! \is_array($skill)) {
                        $errors["available_skills.{$index}"] = 'Must be an array';

                        continue;
                    }

                    if (! isset($skill['name'])) {
                        $errors["available_skills.{$index}.name"] = 'Skill name is required';
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Get race strategy history for a character.
     *
     * Retrieves past strategy advice from chat history for review.
     *
     * @param  int  $characterId  The character ID
     * @param  int  $userId  The user ID
     * @param  int|null  $raceId  Optional race ID for specific race history
     * @param  int  $limit  Maximum number of messages to retrieve
     * @return array<int, array<string, mixed>> Array of past strategy messages
     */
    public function getStrategyHistory(int $characterId, int $userId, ?int $raceId = null, int $limit = 10): array
    {
        // Chat history is managed internally by Neuron AI agents
        // When Neuron AI provides a public API for chat history retrieval,
        // this method can be implemented to fetch past strategy conversations
        // For now, return empty array as history is not externally accessible
        return [];
    }

    /**
     * Get recommended skills for a race based on character and race data.
     *
     * Queries available skills and filters based on race conditions.
     *
     * @param  int  $characterId  The character ID
     * @param  array<string, mixed>  $raceData  Race information
     * @return array<int, array<string, mixed>> Array of recommended skills
     */
    public function getRecommendedSkills(int $characterId, array $raceData = []): array
    {
        // Load character with acquired skills
        /** @var Character $character */
        $character = $this->characterModel
            ->with(['skillAcquisitions.skill'])
            ->findOrFail($characterId);

        // Get all acquired skills
        $acquiredSkillIds = $character->skillAcquisitions->pluck('skill_id')->toArray();

        // Query skills that are acquired and active
        $skills = $this->skillModel->query()
            ->whereIn('id', $acquiredSkillIds)
            ->where('is_active', '=', true)
            ->get();

        // Format skills for response
        $recommendedSkills = [];
        foreach ($skills as $skill) {
            $recommendedSkills[] = [
                'id' => $skill->id,
                'name' => $skill->name,
                'skill_type' => $skill->skill_type,
                'description' => $skill->description,
                'effects' => $skill->effects,
                'activation_conditions' => $skill->activation_conditions,
            ];
        }

        return $recommendedSkills;
    }
}
