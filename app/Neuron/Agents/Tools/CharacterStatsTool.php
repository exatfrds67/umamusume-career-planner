<?php

declare(strict_types=1);

namespace App\Neuron\Agents\Tools;

use App\Models\Character;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;

class CharacterStatsTool extends Tool
{
    public function __construct()
    {
        // Define Tool name and description
        parent::__construct(
            'get_character_stats',
            'Retrieve comprehensive character statistics including current stats, aptitudes, skills, and career progress. Use this tool to get detailed information about a character for training recommendations and strategy planning.',
        );
    }

    /**
     * Properties are the input arguments of the __invoke method.
     */
    protected function properties(): array
    {
        return [
            new ToolProperty(
                name: 'character_id',
                type: PropertyType::INTEGER,
                description: 'The unique identifier of the character to retrieve statistics for',
                required: true,
            ),
        ];
    }

    /**
     * Implementing the tool logic
     */
    public function __invoke(int $character_id): string
    {
        try {
            $character = Character::with([
                'aptitudes',
                'skillAcquisitions.skill',
                'supportCards.supportCard',
            ])->findOrFail($character_id);

            // Format character data for LLM consumption
            $output = [
                'character_info' => [
                    'id' => $character->id,
                    'name' => $character->name,
                    'scenario_type' => $character->scenario_type,
                    'career_stage' => $character->career_stage,
                    'current_turn' => $character->current_turn,
                    'status' => $character->status,
                ],
                'current_stats' => $this->formatStats($character),
                'aptitudes' => $this->formatAptitudes($character),
                'skills' => $this->formatSkills($character),
                'career_progress' => [
                    'energy_level' => $character->energy_level,
                    'mood_status' => $character->mood_status,
                    'conditions' => $character->conditions ?? [],
                    'days_until_race' => $character->days_until_race,
                    'progress_percentage' => $character->getProgressPercentage(),
                ],
                'goals' => $character->goals ?? [],
                'support_cards' => $this->formatSupportCards($character),
            ];

            return $this->formatForLLM($output);
        } catch (ModelNotFoundException $e) {
            return "Error: Character with ID {$character_id} not found. Please verify the character ID and try again.";
        } catch (\Exception $e) {
            return "Error retrieving character statistics: {$e->getMessage()}";
        }
    }

    /**
     * Format character stats with grades
     */
    private function formatStats(Character $character): array
    {
        $stats = $character->current_stats ?? [];
        $formatted = [];

        foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat) {
            $value = $stats[$stat] ?? 0;
            $formatted[$stat] = [
                'value' => $value,
                'grade' => $character->getStatGrade($value),
            ];
        }

        return $formatted;
    }

    /**
     * Format aptitudes for LLM consumption
     */
    private function formatAptitudes(Character $character): array
    {
        $aptitudes = [];

        foreach ($character->aptitudes as $aptitude) {
            $key = $aptitude->distance_type ?? $aptitude->surface_type ?? $aptitude->running_style;
            $aptitudes[$key] = $aptitude->grade;
        }

        return $aptitudes;
    }

    /**
     * Format acquired skills
     */
    private function formatSkills(Character $character): array
    {
        $skills = [];

        foreach ($character->skillAcquisitions()->active()->with('skill')->get() as $acquisition) {
            if ($acquisition->skill) {
                $skills[] = [
                    'name' => $acquisition->skill->name,
                    'type' => $acquisition->skill->skill_type ?? 'unknown',
                    'sp_cost' => $acquisition->final_sp_cost,
                    'is_evolution' => $acquisition->is_evolution,
                    'turn_acquired' => $acquisition->turn_acquired,
                ];
            }
        }

        return $skills;
    }

    /**
     * Format support cards
     */
    private function formatSupportCards(Character $character): array
    {
        $cards = [];

        foreach ($character->supportCards()->with('supportCard')->get() as $characterCard) {
            if ($characterCard->supportCard) {
                $cards[] = [
                    'name' => $characterCard->supportCard->name,
                    'type' => $characterCard->supportCard->card_type ?? 'unknown',
                    'rarity' => $characterCard->supportCard->rarity ?? 'unknown',
                    'friendship_level' => $characterCard->friendship_level ?? 0,
                    'limit_break_level' => $characterCard->limit_break_level ?? 0,
                ];
            }
        }

        return $cards;
    }

    /**
     * Format the output array as a readable string for LLM
     */
    private function formatForLLM(array $data): string
    {
        $output = "CHARACTER STATISTICS\n";
        $output .= "===================\n\n";

        // Character Info
        $info = $data['character_info'];
        $output .= "Character: {$info['name']}\n";
        $output .= "Scenario: {$info['scenario_type']}\n";
        $output .= "Career Stage: {$info['career_stage']}\n";
        $output .= "Current Turn: {$info['current_turn']}\n";
        $output .= "Status: {$info['status']}\n\n";

        // Current Stats
        $output .= "CURRENT STATS:\n";
        foreach ($data['current_stats'] as $stat => $values) {
            $output .= sprintf(
                "  %s: %d (Grade: %s)\n",
                ucfirst($stat),
                $values['value'],
                $values['grade']
            );
        }
        $output .= "\n";

        // Aptitudes
        if (! empty($data['aptitudes'])) {
            $output .= "APTITUDES:\n";
            foreach ($data['aptitudes'] as $type => $grade) {
                $output .= "  {$type}: {$grade}\n";
            }
            $output .= "\n";
        }

        // Skills
        if (! empty($data['skills'])) {
            $skillCount = count($data['skills']);
            $output .= "ACQUIRED SKILLS ({$skillCount} total):\n";
            foreach (array_slice($data['skills'], 0, 10) as $skill) {
                $evolution = $skill['is_evolution'] ? ' (Evolved)' : '';
                $output .= sprintf(
                    "  - %s [%s] (SP: %d, Turn: %d)%s\n",
                    $skill['name'],
                    $skill['type'],
                    $skill['sp_cost'],
                    $skill['turn_acquired'],
                    $evolution
                );
            }
            if ($skillCount > 10) {
                $output .= '  ... and '.($skillCount - 10)." more skills\n";
            }
            $output .= "\n";
        }

        // Career Progress
        $progress = $data['career_progress'];
        $output .= "CAREER PROGRESS:\n";
        $output .= "  Energy Level: {$progress['energy_level']}\n";
        $output .= "  Mood: {$progress['mood_status']}\n";
        if (! empty($progress['conditions'])) {
            $output .= '  Conditions: '.implode(', ', $progress['conditions'])."\n";
        }
        if ($progress['days_until_race'] !== null) {
            $output .= "  Days Until Race: {$progress['days_until_race']}\n";
        }
        $output .= "  Overall Progress: {$progress['progress_percentage']}%\n\n";

        // Goals
        if (! empty($data['goals'])) {
            $output .= "GOALS:\n";
            if (isset($data['goals']['target_stats'])) {
                $output .= "  Target Stats:\n";
                foreach ($data['goals']['target_stats'] as $stat => $target) {
                    $current = $data['current_stats'][$stat]['value'] ?? 0;
                    $remaining = max(0, $target - $current);
                    $output .= "    {$stat}: {$target} (remaining: {$remaining})\n";
                }
            }
            $output .= "\n";
        }

        // Support Cards
        if (! empty($data['support_cards'])) {
            $output .= "SUPPORT CARDS:\n";
            foreach ($data['support_cards'] as $card) {
                $output .= sprintf(
                    "  - %s [%s, %s] (Friendship: %d, LB: %d)\n",
                    $card['name'],
                    $card['type'],
                    $card['rarity'],
                    $card['friendship_level'],
                    $card['limit_break_level']
                );
            }
        }

        return $output;
    }
}
