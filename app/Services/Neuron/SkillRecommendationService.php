<?php

declare(strict_types=1);

namespace App\Services\Neuron;

use App\Models\Character;
use App\Models\Skill;
use App\Models\SkillAcquisition;
use App\Models\SkillHint;
use App\Neuron\Agents\SkillRecommendationAgent;
use App\Neuron\Responses\SkillRecommendationResponse;
use Illuminate\Support\Facades\Log;
use NeuronAI\Chat\Messages\UserMessage;

/**
 * Skill Recommendation Service
 *
 * Acts as an intermediary between controllers and the SkillRecommendationAgent.
 * Handles data formatting for agent consumption and response parsing.
 *
 * **Validates: Requirements 7.1, 7.2, 7.3, 7.4**
 */
class SkillRecommendationService
{
    /**
     * Create a new Skill Recommendation Service instance.
     *
     * Injects Eloquent models via constructor for data access.
     */
    public function __construct(
        private Character $characterModel,
        private Skill $skillModel,
        private SkillAcquisition $skillAcquisitionModel,
        private SkillHint $skillHintModel
    ) {}

    /**
     * Get skill recommendations for a character.
     *
     * Formats character data, calls the agent, and returns structured recommendations.
     *
     * @param  int  $characterId  The character to get recommendations for
     * @param  array<string, mixed>  $skillContext  Skill acquisition context and preferences
     * @param  int  $userId  The authenticated user ID
     * @return SkillRecommendationResponse Structured skill recommendations
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If character not found
     */
    public function getRecommendations(int $characterId, array $skillContext, int $userId): SkillRecommendationResponse
    {
        // Load character with relationships
        $character = $this->characterModel
            ->with(['aptitudes', 'skillAcquisitions.skill', 'supportCards.supportCard'])
            ->findOrFail($characterId);

        // Format data for agent consumption
        $context = $this->formatSkillContext($character, $skillContext);

        // Create agent instance
        $agent = new SkillRecommendationAgent($userId, $characterId);

        try {
            // Call agent with structured output
            $response = $agent->structured(
                new UserMessage($context),
                SkillRecommendationResponse::class,
                3
            );

            // Log successful recommendation generation
            Log::info('Skill recommendations generated', [
                'character_id' => $characterId,
                'user_id' => $userId,
                'recommended_count' => \count($response->recommendedSkills),
            ]);

            return $response;
        } catch (\Exception $e) {
            // Log error
            Log::error('Skill recommendation generation failed', [
                'character_id' => $characterId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            // Re-throw for controller to handle
            throw $e;
        }
    }

    /**
     * Format skill context for agent consumption.
     *
     * Transforms database models into LLM-friendly format with all relevant
     * information for making skill recommendations.
     *
     * @param  Character  $character  The character to format
     * @param  array<string, mixed>  $skillContext  Skill acquisition context
     * @return string Formatted context string
     */
    protected function formatSkillContext(Character $character, array $skillContext): string
    {
        $character->loadMissing([
            'aptitudes',
            'skillAcquisitions.skill',
            'supportCards.supportCard',
        ]);

        $context = "# Skill Recommendation Context\n\n";

        // Character basic info
        $context .= "## Character Information\n";
        $context .= "- Name: {$character->name}\n";
        $context .= "- Scenario: {$character->scenario_type}\n";
        $context .= "- Career Stage: {$character->career_stage}\n";
        $context .= "- Current Turn: {$character->current_turn}\n\n";

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

        // Available SP
        if (isset($skillContext['available_sp'])) {
            $context .= "## Available Skill Points\n";
            $context .= "- Current SP: {$skillContext['available_sp']}\n\n";
        }

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

        // Race preferences
        if (! empty($skillContext['race_preferences'])) {
            $context .= "## Race Preferences\n";
            $prefs = $skillContext['race_preferences'];
            if (isset($prefs['preferred_distance'])) {
                $context .= "- Preferred Distance: {$prefs['preferred_distance']}\n";
            }
            if (isset($prefs['preferred_surface'])) {
                $context .= "- Preferred Surface: {$prefs['preferred_surface']}\n";
            }
            if (isset($prefs['preferred_running_style'])) {
                $context .= "- Preferred Running Style: {$prefs['preferred_running_style']}\n";
            }
            $context .= "\n";
        }

        // Currently acquired skills
        $context .= "## Currently Acquired Skills\n";
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

                    if ($acquisition->final_sp_cost) {
                        $context .= " [Cost: {$acquisition->final_sp_cost} SP]";
                    }

                    if ($acquisition->is_evolution) {
                        $context .= ' [EVOLVED]';
                    }

                    $context .= "\n";

                    if ($skill->description) {
                        $context .= "  Description: {$skill->description}\n";
                    }
                }
            }
            $context .= "\n";
        }

        // Available skills with hints
        $availableSkills = $this->getAvailableSkillsWithHints($character->id);
        if (! empty($availableSkills)) {
            $context .= "## Available Skills to Acquire\n";
            foreach ($availableSkills as $skillData) {
                $context .= "- {$skillData['name']}";

                if (isset($skillData['skill_type'])) {
                    $context .= " (Type: {$skillData['skill_type']})";
                }

                if (isset($skillData['rarity'])) {
                    $context .= " [Rarity: {$skillData['rarity']}]";
                }

                $context .= "\n";

                if (isset($skillData['description'])) {
                    $context .= "  Description: {$skillData['description']}\n";
                }

                if (isset($skillData['base_sp_cost'])) {
                    $context .= "  Base Cost: {$skillData['base_sp_cost']} SP";

                    if (isset($skillData['hint_count']) && $skillData['hint_count'] > 0) {
                        $hintCount = $skillData['hint_count'];
                        $finalCost = $skillData['final_sp_cost'] ?? $skillData['base_sp_cost'];
                        $discount = $skillData['discount_percentage'] ?? 0;
                        $context .= " (With {$hintCount} hint(s): {$finalCost} SP, {$discount}% discount)";
                    }

                    $context .= "\n";
                }

                if (isset($skillData['can_evolve']) && $skillData['can_evolve']) {
                    $context .= '  Can evolve to: '.$skillData['evolution_target_name']."\n";
                }

                if (isset($skillData['meta_tier'])) {
                    $context .= "  Meta Tier: {$skillData['meta_tier']}\n";
                }

                if (! empty($skillData['synergy_skills'])) {
                    $context .= '  Synergizes with: '.\implode(', ', $skillData['synergy_skills'])."\n";
                }
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

        // Build strategy
        if (! empty($skillContext['build_strategy'])) {
            $context .= "## Build Strategy\n";
            $context .= $skillContext['build_strategy']."\n\n";
        }

        // Upcoming races
        if (! empty($skillContext['upcoming_races'])) {
            $context .= "## Upcoming Races\n";
            foreach ($skillContext['upcoming_races'] as $race) {
                $raceName = $race['name'] ?? 'Unknown Race';
                $context .= "- {$raceName}";

                if (isset($race['distance_category'])) {
                    $context .= " ({$race['distance_category']})";
                }

                if (isset($race['surface'])) {
                    $context .= " [{$race['surface']}]";
                }

                if (isset($race['turns_until'])) {
                    $context .= " - in {$race['turns_until']} turn(s)";
                }

                $context .= "\n";
            }
            $context .= "\n";
        }

        // Scenario-specific context
        if ($character->scenario_type === 'unity_cup') {
            $context .= "## Unity Cup Specific\n";
            if (! empty($character->facility_levels)) {
                $context .= "Facility Levels:\n";
                foreach ($character->facility_levels as $type => $level) {
                    $context .= '- '.ucfirst($type).": Level {$level}\n";
                }
            }
            $context .= "\n";
        }

        // Goals and priorities
        if (! empty($character->goals)) {
            $context .= "## Character Goals\n";
            if (! empty($character->goals['target_stats'])) {
                $context .= "Target Stats:\n";
                foreach ($character->goals['target_stats'] as $stat => $target) {
                    $current = $currentStats[$stat] ?? 0;
                    $gap = \max(0, $target - $current);
                    $context .= '- '.ucfirst($stat).": {$current}/{$target} (Gap: {$gap})\n";
                }
            }
            if (! empty($character->goals['target_grade'])) {
                $context .= "Target Grade: {$character->goals['target_grade']}\n";
            }
            $context .= "\n";
        }

        // Additional context
        if (! empty($skillContext['additional_context'])) {
            $context .= "## Additional Context\n";
            $context .= $skillContext['additional_context']."\n\n";
        }

        $context .= 'Please analyze this information and provide your skill acquisition recommendations.';

        return $context;
    }

    /**
     * Get available skills with hint information.
     *
     * Retrieves skills that haven't been acquired yet, along with hint counts
     * and cost calculations.
     *
     * @param  int  $characterId  The character ID
     * @return array<int, array<string, mixed>> Array of available skills with hint data
     */
    protected function getAvailableSkillsWithHints(): array
        // Get IDs of already acquired skills
        /** @var array<int> $acquiredSkillIds */
        $acquiredSkillIds = $this->skillAcquisitionModel->query()
            ->where('character_id', $characterId)
            ->where('is_active', true)
            ->pluck('skill_id')
            ->toArray();

        // Get all active skills not yet acquired
        $availableSkills = $this->skillModel->query()
            ->where('is_active', true)
            ->whereNotIn('id', $acquiredSkillIds)
            ->with(['evolutionTarget', 'hints'])
            ->get();

        // Get hint counts for this character
        /** @var array<int, int> $hintCounts */
        $hintCounts = $this->skillHintModel->query()
            ->where('character_id', $characterId)
            ->selectRaw('skill_id, COUNT(*) as hint_count')
            ->groupBy('skill_id')
            ->pluck('hint_count', 'skill_id')
            ->toArray();

        // Format skills with hint information
        $formattedSkills = [];
        foreach ($availableSkills as $skill) {
            $hintCount = $hintCounts[$skill->id] ?? 0;
            $finalCost = $skill->calculateFinalCost($hintCount);
            $discountPercentage = (int) $skill->getDiscountPercentage($hintCount);

            $skillData = [
                'id' => $skill->id,
                'name' => $skill->name,
                'skill_type' => $skill->skill_type,
                'rarity' => $skill->rarity,
                'description' => $skill->description,
                'base_sp_cost' => $skill->base_sp_cost,
                'hint_count' => $hintCount,
                'final_sp_cost' => $finalCost,
                'discount_percentage' => $discountPercentage,
                'can_evolve' => $skill->can_evolve,
                'meta_tier' => $skill->meta_tier,
            ];

            if ($skill->can_evolve && $skill->evolutionTarget) {
                $skillData['evolution_target_name'] = $skill->evolutionTarget->name;
            }

            if (! empty($skill->synergy_skills) && \is_array($skill->synergy_skills)) {
                // Get synergy skill names
                /** @var array<string> $synergySkills */
                $synergySkills = $this->skillModel->query()
                    ->whereIn('internal_id', $skill->synergy_skills)
                    ->pluck('name')
                    ->toArray();
                $skillData['synergy_skills'] = $synergySkills;
            }

            $formattedSkills[] = $skillData;
        }

        // Sort by cost-effectiveness (considering hints)
        \usort($formattedSkills, function ($a, $b) {
            // Prioritize skills with hints
            if ($a['hint_count'] > 0 && $b['hint_count'] === 0) {
                return -1;
            }
            if ($a['hint_count'] === 0 && $b['hint_count'] > 0) {
                return 1;
            }

            // Then by final cost (lower is better)
            return $a['final_sp_cost'] <=> $b['final_sp_cost'];
        });

        return $formattedSkills;
    }

    /**
     * Parse agent response into structured data.
     *
     * The agent already returns a SkillRecommendationResponse object,
     * but this method can be used to extract or transform specific data.
     *
     * @param  SkillRecommendationResponse  $response  The agent response
     * @return array<string, mixed> Parsed response data
     */
    public function parseResponse(): array
        return [
            'recommended_skills' => $response->recommendedSkills,
            'acquisition_strategy' => $response->acquisitionStrategy,
            'sp_budget_considerations' => $response->spBudgetConsiderations,
            'skill_synergies' => $response->skillSynergies,
            'summary' => $response->getSummary(),
            'validation_errors' => $response->validate(),
        ];
    }

    /**
     * Get skill recommendations with streaming response.
     *
     * Streams the agent's response in real-time for better UX.
     *
     * @param  int  $characterId  The character to get recommendations for
     * @param  array<string, mixed>  $skillContext  Skill acquisition context
     * @param  int  $userId  The authenticated user ID
     * @return \Generator<string> Yields response chunks
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If character not found
     */
    public function getRecommendationsStreaming(int $characterId, array $skillContext, int $userId): \Generator
    {
        // Load character with relationships
        $character = $this->characterModel
            ->with(['aptitudes', 'skillAcquisitions.skill', 'supportCards.supportCard'])
            ->findOrFail($characterId);

        // Format data for agent consumption
        $context = $this->formatSkillContext($character, $skillContext);

        // Create agent instance
        $agent = new SkillRecommendationAgent($userId, $characterId);

        try {
            // Stream response from agent
            foreach ($agent->stream(new UserMessage($context)) as $chunk) {
                yield $chunk;
            }

            // Log successful streaming
            Log::info('Skill recommendations streamed', [
                'character_id' => $characterId,
                'user_id' => $userId,
            ]);
        } catch (\Exception $e) {
            // Log error
            Log::error('Skill recommendation streaming failed', [
                'character_id' => $characterId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            // Yield error message
            yield 'Error: Unable to generate skill recommendations. Please try again.';
        }
    }

    /**
     * Validate skill context data structure.
     *
     * Ensures the skill context array has the expected structure.
     *
     * @param  array<string, mixed>  $skillContext  Skill context to validate
     * @return array<string, string> Validation errors (empty if valid)
     */
    public function validateSkillContext(): array
        $errors = [];

        // Validate available SP if present
        if (isset($skillContext['available_sp'])) {
            $sp = $skillContext['available_sp'];
            if (! \is_int($sp) || $sp < 0) {
                $errors['available_sp'] = 'Available SP must be a non-negative integer';
            }
        }

        // Validate race preferences structure if present
        if (isset($skillContext['race_preferences'])) {
            if (! \is_array($skillContext['race_preferences'])) {
                $errors['race_preferences'] = 'Race preferences must be an array';
            } else {
                $prefs = $skillContext['race_preferences'];

                if (isset($prefs['preferred_distance'])) {
                    $validDistances = ['short', 'mile', 'medium', 'long'];
                    if (! \in_array($prefs['preferred_distance'], $validDistances, true)) {
                        $errors['race_preferences.preferred_distance'] = 'Invalid distance. Must be one of: '.\implode(', ', $validDistances);
                    }
                }

                if (isset($prefs['preferred_surface'])) {
                    $validSurfaces = ['turf', 'dirt'];
                    if (! \in_array($prefs['preferred_surface'], $validSurfaces, true)) {
                        $errors['race_preferences.preferred_surface'] = 'Invalid surface. Must be one of: '.\implode(', ', $validSurfaces);
                    }
                }

                if (isset($prefs['preferred_running_style'])) {
                    $validStyles = ['runner', 'leader', 'betweener', 'chaser'];
                    if (! \in_array($prefs['preferred_running_style'], $validStyles, true)) {
                        $errors['race_preferences.preferred_running_style'] = 'Invalid running style. Must be one of: '.\implode(', ', $validStyles);
                    }
                }
            }
        }

        // Validate upcoming races structure if present
        if (isset($skillContext['upcoming_races'])) {
            if (! \is_array($skillContext['upcoming_races'])) {
                $errors['upcoming_races'] = 'Upcoming races must be an array';
            } else {
                foreach ($skillContext['upcoming_races'] as $index => $race) {
                    if (! \is_array($race)) {
                        $errors["upcoming_races.{$index}"] = 'Must be an array';

                        continue;
                    }

                    if (! isset($race['name'])) {
                        $errors["upcoming_races.{$index}.name"] = 'Race name is required';
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Get skill acquisition history for a character.
     *
     * Retrieves past skill acquisitions with details.
     *
     * @param  int  $characterId  The character ID
     * @param  int  $limit  Maximum number of acquisitions to retrieve
     * @return array<int, array<string, mixed>> Array of past skill acquisitions
     */
    public function getAcquisitionHistory(): array
        $acquisitions = $this->skillAcquisitionModel->query()
            ->where('character_id', $characterId)
            ->with(['skill', 'career'])
            ->orderBy('turn_acquired', 'desc')
            ->limit($limit)
            ->get();

        $history = [];
        foreach ($acquisitions as $acquisition) {
            $skill = $acquisition->skill;
            if ($skill) {
                $history[] = [
                    'skill_name' => $skill->name,
                    'skill_type' => $skill->skill_type,
                    'turn_acquired' => $acquisition->turn_acquired,
                    'career_phase' => $acquisition->career_phase,
                    'acquisition_method' => $acquisition->acquisition_method,
                    'base_sp_cost' => $acquisition->base_sp_cost,
                    'final_sp_cost' => $acquisition->final_sp_cost,
                    'sp_saved' => $acquisition->sp_saved,
                    'hints_used' => $acquisition->hints_used,
                    'is_evolution' => $acquisition->is_evolution,
                    'is_active' => $acquisition->is_active,
                ];
            }
        }

        return $history;
    }

    /**
     * Calculate skill synergies for a character.
     *
     * Analyzes acquired skills and identifies synergies.
     *
     * @param  int  $characterId  The character ID
     * @return array<int, array<string, mixed>> Array of skill synergies
     */
    public function calculateSkillSynergies(): array
        // Get acquired skills
        $acquisitions = $this->skillAcquisitionModel->query()
            ->where('character_id', $characterId)
            ->where('is_active', true)
            ->with('skill')
            ->get();

        $synergies = [];
        $processedPairs = [];

        foreach ($acquisitions as $acquisition) {
            $skill = $acquisition->skill;
            if (! $skill || empty($skill->synergy_skills)) {
                continue;
            }

            // Check if any synergy skills are also acquired
            foreach ($acquisitions as $otherAcquisition) {
                $otherSkill = $otherAcquisition->skill;
                if (! $otherSkill || $skill->id === $otherSkill->id) {
                    continue;
                }

                // Check if they synergize
                if ($skill->synergizesWith($otherSkill)) {
                    // Create a unique key to avoid duplicates
                    $pairKey = \min($skill->id, $otherSkill->id).'_'.\max($skill->id, $otherSkill->id);

                    if (! isset($processedPairs[$pairKey])) {
                        $synergies[] = [
                            'skills' => [$skill->name, $otherSkill->name],
                            'skill_types' => [$skill->skill_type, $otherSkill->skill_type],
                            'benefit' => 'These skills work well together and enhance each other\'s effects.',
                        ];
                        $processedPairs[$pairKey] = true;
                    }
                }
            }
        }

        return $synergies;
    }
}
