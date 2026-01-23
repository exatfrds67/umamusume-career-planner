<?php

declare(strict_types=1);

namespace App\Neuron\Agents\Tools;

use App\Models\Skill;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;

class SkillDataTool extends Tool
{
    public function __construct()
    {
        // Define Tool name and description
        parent::__construct(
            'get_skill_data',
            'Retrieve comprehensive skill information including effects, SP costs, evolution chains, hint information, and synergies. Use this tool to get detailed information about skills for recommendations and strategy planning. Supports querying by skill ID, name, or type.',
        );
    }

    /**
     * Properties are the input arguments of the __invoke method.
     */
    protected function properties(): array
    {
        return [
            new ToolProperty(
                name: 'skill_id',
                type: PropertyType::INTEGER,
                description: 'The unique identifier of the skill to retrieve (optional if name or type is provided)',
                required: false,
            ),
            new ToolProperty(
                name: 'skill_name',
                type: PropertyType::STRING,
                description: 'The name of the skill to search for (optional if skill_id is provided)',
                required: false,
            ),
            new ToolProperty(
                name: 'skill_type',
                type: PropertyType::STRING,
                description: 'Filter skills by type (e.g., "speed", "stamina", "recovery", "acceleration"). Returns multiple skills if specified without skill_id or skill_name.',
                required: false,
            ),
            new ToolProperty(
                name: 'include_evolution_chain',
                type: PropertyType::BOOLEAN,
                description: 'Whether to include the full evolution chain for the skill (default: true)',
                required: false,
            ),
        ];
    }

    /**
     * Implementing the tool logic
     */
    public function __invoke(
        ?int $skill_id = null,
        ?string $skill_name = null,
        ?string $skill_type = null,
        bool $include_evolution_chain = true
    ): string {
        try {
            // Validate that at least one search parameter is provided
            if ($skill_id === null && $skill_name === null && $skill_type === null) {
                return 'Error: Please provide at least one search parameter (skill_id, skill_name, or skill_type).';
            }

            // Query skills based on provided parameters
            $query = Skill::query()->active();

            if ($skill_id !== null) {
                // Direct ID lookup
                try {
                    $skill = $query->with(['evolutionTarget', 'evolutionSource', 'hints'])->findOrFail($skill_id);

                    return $this->formatSingleSkill($skill, $include_evolution_chain);
                } catch (ModelNotFoundException $e) {
                    return "Error: Skill with ID {$skill_id} not found. Please verify the skill ID and try again.";
                }
            }

            if ($skill_name !== null) {
                // Search by name (case-insensitive partial match)
                $skills = $query->where('name', 'LIKE', "%{$skill_name}%")
                    ->with(['evolutionTarget', 'evolutionSource', 'hints'])
                    ->get();

                if ($skills->isEmpty()) {
                    return "Error: No skills found matching name '{$skill_name}'. Please try a different search term.";
                }

                if ($skills->count() === 1) {
                    return $this->formatSingleSkill($skills->first(), $include_evolution_chain);
                }

                // Multiple matches - return list
                return $this->formatMultipleSkills($skills, "Skills matching '{$skill_name}'");
            }

            if ($skill_type !== null) {
                // Filter by type
                $skills = $query->ofType($skill_type)
                    ->with(['evolutionTarget', 'evolutionSource', 'hints'])
                    ->orderBy('meta_tier')
                    ->orderBy('base_sp_cost')
                    ->get();

                if ($skills->isEmpty()) {
                    return "Error: No skills found of type '{$skill_type}'. Please verify the skill type and try again.";
                }

                return $this->formatMultipleSkills($skills, "Skills of type '{$skill_type}'");
            }

            return 'Error: Unable to process skill query. Please check your parameters.';
        } catch (\Exception $e) {
            return "Error retrieving skill data: {$e->getMessage()}";
        }
    }

    /**
     * Format a single skill with full details
     */
    private function formatSingleSkill(Skill $skill, bool $includeEvolutionChain): string
    {
        $output = "SKILL DETAILS\n";
        $output .= "=============\n\n";

        // Basic Information
        $output .= "Name: {$skill->name}\n";
        $output .= "Type: {$skill->skill_type}\n";
        $output .= "Rarity: {$skill->rarity}\n";
        $output .= "Base SP Cost: {$skill->base_sp_cost}\n";

        if ($skill->meta_tier) {
            $output .= "Meta Tier: {$skill->meta_tier}\n";
        }

        $output .= "\n";

        // Description
        if ($skill->description) {
            $output .= "DESCRIPTION:\n";
            $output .= "{$skill->description}\n\n";
        }

        // Effects
        if (! empty($skill->effects)) {
            $output .= "EFFECTS:\n";
            foreach ($skill->effects as $effect) {
                if (is_array($effect)) {
                    $output .= '  - '.json_encode($effect)."\n";
                } else {
                    $output .= "  - {$effect}\n";
                }
            }
            $output .= "\n";
        }

        // Activation Conditions
        if (! empty($skill->activation_conditions)) {
            $output .= "ACTIVATION CONDITIONS:\n";
            foreach ($skill->activation_conditions as $condition) {
                if (is_array($condition)) {
                    $output .= '  - '.json_encode($condition)."\n";
                } else {
                    $output .= "  - {$condition}\n";
                }
            }
            $output .= "\n";
        }

        // Stat Requirements
        if (! empty($skill->stat_requirements)) {
            $output .= "STAT REQUIREMENTS:\n";
            foreach ($skill->stat_requirements as $stat => $value) {
                $output .= "  {$stat}: {$value}\n";
            }
            $output .= "\n";
        }

        // Hint Information
        $output .= "HINT DISCOUNT INFORMATION:\n";
        $output .= "  With 0 hints: {$skill->base_sp_cost} SP (0% discount)\n";
        $output .= '  With 1 hint: '.$skill->calculateFinalCost(1).' SP (20% discount, saves '.$skill->getSpSaved(1)." SP)\n";
        $output .= '  With 2+ hints: '.$skill->calculateFinalCost(2).' SP (40% discount, saves '.$skill->getSpSaved(2)." SP)\n\n";

        // Current Hints
        $hintCount = $skill->hints()->count();
        if ($hintCount > 0) {
            $output .= "CURRENT HINTS: {$hintCount}\n";
            foreach ($skill->hints as $hint) {
                $output .= "  - Source: {$hint->source_type}";
                if ($hint->source_name) {
                    $output .= " ({$hint->source_name})";
                }
                $output .= "\n";
            }
            $output .= "\n";
        }

        // Evolution Information
        if ($includeEvolutionChain && ($skill->canEvolve() || $skill->isEvolved())) {
            $output .= "EVOLUTION CHAIN:\n";
            $chain = $skill->getEvolutionChain();
            foreach ($chain as $index => $chainSkill) {
                $isCurrent = $chainSkill->id === $skill->id;
                $marker = $isCurrent ? ' [CURRENT]' : '';
                $arrow = $index > 0 ? ' → ' : '';
                $output .= "{$arrow}{$chainSkill->name} (SP: {$chainSkill->base_sp_cost}){$marker}\n";
            }
            $output .= "\n";
        }

        // Synergy Skills
        if (! empty($skill->synergy_skills)) {
            $output .= "SYNERGY SKILLS:\n";
            $synergySkills = $skill->getSynergySkills();
            if (! empty($synergySkills)) {
                foreach (array_slice($synergySkills, 0, 5) as $synergySkill) {
                    $output .= "  - {$synergySkill['name']} [{$synergySkill['skill_type']}]\n";
                }
                if (count($synergySkills) > 5) {
                    $output .= '  ... and '.(count($synergySkills) - 5)." more synergy skills\n";
                }
            }
            $output .= "\n";
        }

        // Acquisition Sources
        $sources = [];
        if (! empty($skill->support_card_sources)) {
            $sources[] = 'Support Cards: '.implode(', ', array_slice($skill->support_card_sources, 0, 3));
        }
        if (! empty($skill->event_sources)) {
            $sources[] = 'Events: '.implode(', ', array_slice($skill->event_sources, 0, 3));
        }
        if (! empty($skill->inheritance_sources)) {
            $sources[] = 'Inheritance: '.implode(', ', array_slice($skill->inheritance_sources, 0, 3));
        }

        if (! empty($sources)) {
            $output .= "ACQUISITION SOURCES:\n";
            foreach ($sources as $source) {
                $output .= "  - {$source}\n";
            }
            $output .= "\n";
        }

        // Strategic Notes
        if (! empty($skill->strategic_notes)) {
            $output .= "STRATEGIC NOTES:\n";
            foreach ($skill->strategic_notes as $note) {
                if (is_array($note)) {
                    $output .= '  - '.json_encode($note)."\n";
                } else {
                    $output .= "  - {$note}\n";
                }
            }
        }

        return $output;
    }

    /**
     * Format multiple skills as a list
     */
    private function formatMultipleSkills($skills, string $title): string
    {
        $output = strtoupper($title)."\n";
        $output .= str_repeat('=', strlen($title))."\n\n";

        $output .= 'Found '.count($skills)." skills:\n\n";

        foreach ($skills as $skill) {
            $evolution = '';
            if ($skill->canEvolve()) {
                $evolution = ' → '.$skill->evolutionTarget->name;
            } elseif ($skill->isEvolved()) {
                $evolution = ' (Evolved from '.$skill->evolutionSource->name.')';
            }

            $hints = $skill->hints()->count();
            $hintInfo = $hints > 0 ? " | {$hints} hints" : '';

            $output .= sprintf(
                "- [ID: %d] %s [%s, %s] (SP: %d%s)%s%s\n",
                $skill->id,
                $skill->name,
                $skill->skill_type,
                $skill->rarity,
                $skill->base_sp_cost,
                $hintInfo,
                $evolution,
                $skill->meta_tier ? " | Tier: {$skill->meta_tier}" : ''
            );

            if ($skill->description) {
                $shortDesc = strlen($skill->description) > 80
                    ? substr($skill->description, 0, 77).'...'
                    : $skill->description;
                $output .= "  {$shortDesc}\n";
            }

            $output .= "\n";
        }

        $output .= "Use get_skill_data with a specific skill_id to get detailed information about a skill.\n";

        return $output;
    }
}
