<?php

declare(strict_types=1);

namespace App\Neuron\Agents\Tools;

use App\Models\Race;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;

class RaceDataTool extends Tool
{
    public function __construct()
    {
        // Define Tool name and description
        parent::__construct(
            'get_race_data',
            'Retrieve comprehensive race information including conditions, requirements, rewards, and historical performance data. Use this tool to get detailed information about races for strategy planning and race selection. Supports querying by race ID, name, grade, distance, or surface.',
        );
    }

    /**
     * Properties are the input arguments of the __invoke method.
     */
    protected function properties(): array
    {
        return [
            new ToolProperty(
                name: 'race_id',
                type: PropertyType::INTEGER,
                description: 'The unique identifier of the race to retrieve (optional if name or filters are provided)',
                required: false,
            ),
            new ToolProperty(
                name: 'race_name',
                type: PropertyType::STRING,
                description: 'The name of the race to search for (optional if race_id is provided)',
                required: false,
            ),
            new ToolProperty(
                name: 'race_grade',
                type: PropertyType::STRING,
                description: 'Filter races by grade (e.g., "G1", "G2", "G3", "OP", "Pre-OP"). Returns multiple races if specified without race_id or race_name.',
                required: false,
            ),
            new ToolProperty(
                name: 'distance_category',
                type: PropertyType::STRING,
                description: 'Filter races by distance category (e.g., "short", "mile", "intermediate", "long"). Returns multiple races if specified.',
                required: false,
            ),
            new ToolProperty(
                name: 'surface',
                type: PropertyType::STRING,
                description: 'Filter races by surface type (e.g., "turf", "dirt"). Returns multiple races if specified.',
                required: false,
            ),
            new ToolProperty(
                name: 'character_id',
                type: PropertyType::INTEGER,
                description: 'Filter races by character ID to get character-specific race history',
                required: false,
            ),
        ];
    }

    /**
     * Implementing the tool logic
     */
    public function __invoke(
        ?int $race_id = null,
        ?string $race_name = null,
        ?string $race_grade = null,
        ?string $distance_category = null,
        ?string $surface = null,
        ?int $character_id = null
    ): string {
        try {
            // Validate that at least one search parameter is provided
            if ($race_id === null && $race_name === null && $race_grade === null && $distance_category === null && $surface === null && $character_id === null) {
                return 'Error: Please provide at least one search parameter (race_id, race_name, race_grade, distance_category, surface, or character_id).';
            }

            // Query races based on provided parameters
            $query = Race::query()->with(['character', 'career']);

            if ($race_id !== null) {
                // Direct ID lookup
                try {
                    $race = $query->findOrFail($race_id);

                    return $this->formatSingleRace($race);
                } catch (ModelNotFoundException $e) {
                    return "Error: Race with ID {$race_id} not found. Please verify the race ID and try again.";
                }
            }

            // Apply filters
            if ($race_name !== null) {
                $query->where('race_name', 'LIKE', "%{$race_name}%");
            }

            if ($race_grade !== null) {
                $query->where('race_grade', $race_grade);
            }

            if ($distance_category !== null) {
                $query->where('distance_category', $distance_category);
            }

            if ($surface !== null) {
                $query->where('surface', $surface);
            }

            if ($character_id !== null) {
                $query->where('character_id', $character_id);
            }

            // Order by turn number and career phase for chronological listing
            $races = $query->orderBy('turn_number')->orderBy('career_phase')->get();

            if ($races->isEmpty()) {
                $filters = [];
                if ($race_name) {
                    $filters[] = "name '{$race_name}'";
                }
                if ($race_grade) {
                    $filters[] = "grade '{$race_grade}'";
                }
                if ($distance_category) {
                    $filters[] = "distance '{$distance_category}'";
                }
                if ($surface) {
                    $filters[] = "surface '{$surface}'";
                }
                if ($character_id) {
                    $filters[] = "character ID {$character_id}";
                }
                $filterStr = implode(', ', $filters);

                return "Error: No races found matching {$filterStr}. Please try different search criteria.";
            }

            if ($races->count() === 1) {
                return $this->formatSingleRace($races->first());
            }

            // Multiple matches - return list
            return $this->formatMultipleRaces($races);
        } catch (\Exception $e) {
            return "Error retrieving race data: {$e->getMessage()}";
        }
    }

    /**
     * Format a single race with full details
     */
    private function formatSingleRace(Race $race): string
    {
        $output = "RACE DETAILS\n";
        $output .= "============\n\n";

        // Basic Information
        $output .= "Race: {$race->race_name}\n";
        if ($race->race_internal_id) {
            $output .= "Internal ID: {$race->race_internal_id}\n";
        }
        $output .= "Grade: {$race->race_grade}\n";
        $output .= "Distance: {$race->distance_meters}m ({$race->distance_category})\n";
        $output .= "Surface: {$race->surface}\n";
        $output .= "Track Type: {$race->track_type}\n";

        if ($race->turn_number) {
            $output .= "Turn: {$race->turn_number}";
            if ($race->career_phase) {
                $output .= " ({$race->career_phase})";
            }
            $output .= "\n";
        }

        $output .= "\n";

        // Race Conditions
        $output .= "RACE CONDITIONS:\n";
        if ($race->weather) {
            $output .= "  Weather: {$race->weather}\n";
        }
        if ($race->track_condition) {
            $output .= "  Track Condition: {$race->track_condition}\n";
        }
        if ($race->field_size) {
            $output .= "  Field Size: {$race->field_size} horses\n";
        }
        if ($race->running_style) {
            $output .= "  Recommended Running Style: {$race->running_style}\n";
        }

        if (! empty($race->race_conditions)) {
            $output .= "  Additional Conditions:\n";
            foreach ($race->race_conditions as $condition) {
                if (\is_array($condition)) {
                    $output .= '    - '.\json_encode($condition)."\n";
                } else {
                    $output .= "    - {$condition}\n";
                }
            }
        }
        $output .= "\n";

        // URA Finale / Unity Cup Information
        if ($race->is_ura_finale_race) {
            $output .= "URA FINALE RACE:\n";
            if ($race->ura_finale_stage) {
                $output .= "  Stage: {$race->ura_finale_stage}\n";
            }
            if (! empty($race->ura_finale_requirements)) {
                $output .= "  Requirements:\n";
                foreach ($race->ura_finale_requirements as $req => $value) {
                    $output .= "    {$req}: {$value}\n";
                }
            }
            $output .= "\n";
        }

        if ($race->is_unity_cup_match) {
            $output .= "UNITY CUP MATCH:\n";
            if ($race->unity_cup_opponent_rank) {
                $output .= "  Opponent Rank: {$race->unity_cup_opponent_rank}\n";
            }
            if ($race->unity_cup_points_earned) {
                $output .= "  Points Earned: {$race->unity_cup_points_earned}\n";
            }
            $output .= "\n";
        }

        // Character Performance (if race has been run)
        if ($race->finish_position !== null) {
            $output .= "RACE RESULT:\n";
            $output .= "  Finish Position: {$race->finish_position}";
            if ($race->won_race) {
                $output .= ' (WON!)';
            }
            $output .= "\n";

            if ($race->finish_time) {
                $output .= "  Finish Time: {$race->finish_time}\n";
            }
            if ($race->margin_of_victory) {
                $output .= "  Margin: {$race->margin_of_victory}\n";
            }
            if ($race->speed_rating) {
                $output .= "  Speed Rating: {$race->speed_rating}\n";
            }
            $output .= "\n";

            // Character Stats at Race
            if ($race->speed_at_race || $race->stamina_at_race) {
                $output .= "CHARACTER STATS AT RACE:\n";
                if ($race->speed_at_race) {
                    $output .= "  Speed: {$race->speed_at_race}\n";
                }
                if ($race->stamina_at_race) {
                    $output .= "  Stamina: {$race->stamina_at_race}\n";
                }
                if ($race->power_at_race) {
                    $output .= "  Power: {$race->power_at_race}\n";
                }
                if ($race->guts_at_race) {
                    $output .= "  Guts: {$race->guts_at_race}\n";
                }
                if ($race->wit_at_race) {
                    $output .= "  Wit: {$race->wit_at_race}\n";
                }
                $output .= "\n";
            }

            // Character Condition
            if ($race->character_condition || $race->motivation || $race->energy_level) {
                $output .= "CHARACTER CONDITION:\n";
                if ($race->character_condition) {
                    $output .= "  Condition: {$race->character_condition}\n";
                }
                if ($race->motivation) {
                    $output .= "  Motivation: {$race->motivation}\n";
                }
                if ($race->energy_level) {
                    $output .= "  Energy Level: {$race->energy_level}\n";
                }
                $output .= "\n";
            }

            // Skills Activated
            if (! empty($race->skills_activated)) {
                $output .= 'SKILLS ACTIVATED: '.\count($race->skills_activated)."\n";
                foreach (\array_slice($race->skills_activated, 0, 10) as $skill) {
                    if (\is_array($skill)) {
                        $output .= '  - '.\json_encode($skill)."\n";
                    } else {
                        $output .= "  - {$skill}\n";
                    }
                }
                if (\count($race->skills_activated) > 10) {
                    $output .= '  ... and '.(\count($race->skills_activated) - 10)." more skills\n";
                }
                $output .= "\n";
            }

            // Performance Analysis
            if (! empty($race->performance_analysis)) {
                $output .= "PERFORMANCE ANALYSIS:\n";
                foreach ($race->performance_analysis as $key => $value) {
                    if (\is_array($value)) {
                        $output .= "  {$key}: ".\json_encode($value)."\n";
                    } else {
                        $output .= "  {$key}: {$value}\n";
                    }
                }
                $output .= "\n";
            }
        }

        // Rewards
        $hasRewards = $race->fans_gained || $race->sp_reward || ! empty($race->item_rewards) || ! empty($race->stat_bonuses);
        if ($hasRewards) {
            $output .= "REWARDS:\n";
            if ($race->fans_gained) {
                $output .= "  Fans Gained: {$race->fans_gained}\n";
            }
            if ($race->sp_reward) {
                $output .= "  SP Reward: {$race->sp_reward}\n";
            }
            if (! empty($race->item_rewards)) {
                $output .= '  Items: '.\implode(', ', $race->item_rewards)."\n";
            }
            if (! empty($race->stat_bonuses)) {
                $output .= "  Stat Bonuses:\n";
                foreach ($race->stat_bonuses as $stat => $bonus) {
                    $output .= "    {$stat}: +{$bonus}\n";
                }
            }
            $output .= "\n";
        }

        // Strategic Information
        if ($race->strategic_importance) {
            $output .= "STRATEGIC IMPORTANCE:\n";
            $output .= "{$race->strategic_importance}\n\n";
        }

        if (! empty($race->preparation_strategy)) {
            $output .= "PREPARATION STRATEGY:\n";
            foreach ($race->preparation_strategy as $key => $value) {
                if (\is_array($value)) {
                    $output .= "  {$key}: ".\json_encode($value)."\n";
                } else {
                    $output .= "  {$key}: {$value}\n";
                }
            }
            $output .= "\n";
        }

        if (! empty($race->lessons_learned)) {
            $output .= "LESSONS LEARNED:\n";
            foreach ($race->lessons_learned as $lesson) {
                if (\is_array($lesson)) {
                    $output .= '  - '.\json_encode($lesson)."\n";
                } else {
                    $output .= "  - {$lesson}\n";
                }
            }
            $output .= "\n";
        }

        if ($race->race_notes) {
            $output .= "NOTES:\n";
            $output .= "{$race->race_notes}\n\n";
        }

        // Injury Information
        if ($race->injury_occurred) {
            $output .= "⚠️ INJURY OCCURRED DURING THIS RACE\n\n";
        }

        // Character Information
        if ($race->character) {
            $output .= "Character: {$race->character->name}\n";
        }

        return $output;
    }

    /**
     * Format multiple races as a list
     */
    private function formatMultipleRaces($races): string
    {
        $output = "RACE LIST\n";
        $output .= "=========\n\n";

        $output .= 'Found '.\count($races)." races:\n\n";

        foreach ($races as $race) {
            $result = '';
            if ($race->finish_position !== null) {
                $result = $race->won_race ? ' ✓ WON' : " (Finished: {$race->finish_position})";
            }

            $turn = $race->turn_number ? " | Turn {$race->turn_number}" : '';
            $phase = $race->career_phase ? " ({$race->career_phase})" : '';

            $special = '';
            if ($race->is_ura_finale_race) {
                $special = ' [URA FINALE]';
            } elseif ($race->is_unity_cup_match) {
                $special = ' [UNITY CUP]';
            }

            $output .= \sprintf(
                "- [ID: %d] %s [%s, %dm, %s]%s%s%s%s\n",
                $race->id,
                $race->race_name,
                $race->race_grade,
                $race->distance_meters,
                $race->surface,
                $turn,
                $phase,
                $special,
                $result
            );

            if ($race->character) {
                $output .= "  Character: {$race->character->name}\n";
            }

            if ($race->strategic_importance) {
                $shortImportance = \strlen($race->strategic_importance) > 80
                    ? \substr($race->strategic_importance, 0, 77).'...'
                    : $race->strategic_importance;
                $output .= "  {$shortImportance}\n";
            }

            $output .= "\n";
        }

        $output .= "Use get_race_data with a specific race_id to get detailed information about a race.\n";

        return $output;
    }
}
