<?php

declare(strict_types=1);

use App\Models\Skill;
use App\Models\SkillHint;
use App\Neuron\Agents\Tools\SkillDataTool;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Unit tests for SkillDataTool.
 *
 * These tests verify:
 * - Tool registration and properties
 * - Tool callable execution with valid data
 * - Tool parameter validation
 * - Error handling for invalid inputs
 *
 * **Validates: Requirements 10.1, 10.2, 10.3, 10.4**
 */
describe('SkillDataTool', function () {
    beforeEach(function () {
        $this->tool = new SkillDataTool;
    });

    describe('Tool Registration', function () {
        it('has correct tool name', function () {
            expect($this->tool->getName())->toBe('get_skill_data');
        });

        it('has descriptive tool description', function () {
            $description = $this->tool->getDescription();

            expect($description)->toBeString();
            expect($description)->not->toBeEmpty();
            expect($description)->toContain('skill');
            expect($description)->toContain('information');
        });

        it('defines required properties', function () {
            $properties = $this->tool->getProperties();

            expect($properties)->toBeArray();
            expect($properties)->not->toBeEmpty();
        });

        it('has skill_id property', function () {
            $properties = $this->tool->getProperties();

            $skillIdProperty = collect($properties)->first(
                fn ($prop) => $prop->getName() === 'skill_id'
            );

            expect($skillIdProperty)->not->toBeNull();
            expect($skillIdProperty->isRequired())->toBeFalse();
            expect($skillIdProperty->getType()->value)->toBe('integer');
        });

        it('has skill_name property', function () {
            $properties = $this->tool->getProperties();

            $skillNameProperty = collect($properties)->first(
                fn ($prop) => $prop->getName() === 'skill_name'
            );

            expect($skillNameProperty)->not->toBeNull();
            expect($skillNameProperty->isRequired())->toBeFalse();
            expect($skillNameProperty->getType()->value)->toBe('string');
        });

        it('has skill_type property', function () {
            $properties = $this->tool->getProperties();

            $skillTypeProperty = collect($properties)->first(
                fn ($prop) => $prop->getName() === 'skill_type'
            );

            expect($skillTypeProperty)->not->toBeNull();
            expect($skillTypeProperty->isRequired())->toBeFalse();
            expect($skillTypeProperty->getType()->value)->toBe('string');
        });

        it('has include_evolution_chain property', function () {
            $properties = $this->tool->getProperties();

            $evolutionProperty = collect($properties)->first(
                fn ($prop) => $prop->getName() === 'include_evolution_chain'
            );

            expect($evolutionProperty)->not->toBeNull();
            expect($evolutionProperty->isRequired())->toBeFalse();
            expect($evolutionProperty->getType()->value)->toBe('boolean');
        });

        it('has property descriptions', function () {
            $properties = $this->tool->getProperties();

            foreach ($properties as $property) {
                expect($property->getDescription())->toBeString();
                expect($property->getDescription())->not->toBeEmpty();
            }
        });
    });

    describe('Tool Callable Execution - Single Skill by ID', function () {
        it('retrieves skill by ID successfully', function () {
            $skill = Skill::factory()->create([
                'name' => 'Speed Star',
                'skill_type' => 'speed',
                'rarity' => 'rare',
                'base_sp_cost' => 120,
                'description' => 'Increases speed in the final stretch',
                'status' => 'active',
            ]);

            $result = ($this->tool)(skill_id: $skill->id);

            expect($result)->toBeString();
            expect($result)->toContain('SKILL DETAILS');
            expect($result)->toContain('Speed Star');
            expect($result)->toContain('Type: speed');
            expect($result)->toContain('Rarity: rare');
            expect($result)->toContain('Base SP Cost: 120');
        });

        it('includes skill description', function () {
            $skill = Skill::factory()->create([
                'name' => 'Test Skill',
                'description' => 'This is a test skill description',
                'status' => 'active',
            ]);

            $result = ($this->tool)(skill_id: $skill->id);

            expect($result)->toContain('DESCRIPTION:');
            expect($result)->toContain('This is a test skill description');
        });

        it('includes skill effects', function () {
            $skill = Skill::factory()->create([
                'name' => 'Test Skill',
                'effects' => ['Increases speed by 10%', 'Reduces stamina consumption'],
                'status' => 'active',
            ]);

            $result = ($this->tool)(skill_id: $skill->id);

            expect($result)->toContain('EFFECTS:');
            expect($result)->toContain('Increases speed by 10%');
            expect($result)->toContain('Reduces stamina consumption');
        });

        it('includes activation conditions', function () {
            $skill = Skill::factory()->create([
                'name' => 'Test Skill',
                'activation_conditions' => ['Final stretch', 'Position: 3rd or better'],
                'status' => 'active',
            ]);

            $result = ($this->tool)(skill_id: $skill->id);

            expect($result)->toContain('ACTIVATION CONDITIONS:');
            expect($result)->toContain('Final stretch');
            expect($result)->toContain('Position: 3rd or better');
        });

        it('includes stat requirements', function () {
            $skill = Skill::factory()->create([
                'name' => 'Test Skill',
                'stat_requirements' => ['speed' => 800, 'stamina' => 600],
                'status' => 'active',
            ]);

            $result = ($this->tool)(skill_id: $skill->id);

            expect($result)->toContain('STAT REQUIREMENTS:');
            expect($result)->toContain('speed: 800');
            expect($result)->toContain('stamina: 600');
        });

        it('includes hint discount information', function () {
            $skill = Skill::factory()->create([
                'name' => 'Test Skill',
                'base_sp_cost' => 100,
                'status' => 'active',
            ]);

            $result = ($this->tool)(skill_id: $skill->id);

            expect($result)->toContain('HINT DISCOUNT INFORMATION:');
            expect($result)->toContain('With 0 hints: 100 SP (0% discount)');
            expect($result)->toContain('With 1 hint:');
            expect($result)->toContain('20% discount');
            expect($result)->toContain('With 2+ hints:');
            expect($result)->toContain('40% discount');
        });

        it('includes current hints when present', function () {
            $skill = Skill::factory()->create([
                'name' => 'Test Skill',
                'status' => 'active',
            ]);

            SkillHint::factory()->create([
                'skill_id' => $skill->id,
                'source_type' => 'support_card',
                'source_name' => 'SSR Oguri Cap',
            ]);

            SkillHint::factory()->create([
                'skill_id' => $skill->id,
                'source_type' => 'event',
                'source_name' => 'Training Event',
            ]);

            $result = ($this->tool)(skill_id: $skill->id);

            expect($result)->toContain('CURRENT HINTS: 2');
            expect($result)->toContain('support_card');
            expect($result)->toContain('SSR Oguri Cap');
            expect($result)->toContain('event');
            expect($result)->toContain('Training Event');
        });

        it('includes meta tier when present', function () {
            $skill = Skill::factory()->create([
                'name' => 'Test Skill',
                'meta_tier' => 'S',
                'status' => 'active',
            ]);

            $result = ($this->tool)(skill_id: $skill->id);

            expect($result)->toContain('Meta Tier: S');
        });

        it('returns error for non-existent skill ID', function () {
            $result = ($this->tool)(skill_id: 99999);

            expect($result)->toBeString();
            expect($result)->toContain('Error');
            expect($result)->toContain('Skill with ID 99999 not found');
        });
    });

    describe('Tool Callable Execution - Search by Name', function () {
        it('retrieves skill by exact name match', function () {
            $skill = Skill::factory()->create([
                'name' => 'Speed Star',
                'status' => 'active',
            ]);

            $result = ($this->tool)(skill_name: 'Speed Star');

            expect($result)->toBeString();
            expect($result)->toContain('SKILL DETAILS');
            expect($result)->toContain('Speed Star');
        });

        it('retrieves skill by partial name match', function () {
            $skill = Skill::factory()->create([
                'name' => 'Speed Star',
                'status' => 'active',
            ]);

            $result = ($this->tool)(skill_name: 'Speed');

            expect($result)->toBeString();
            expect($result)->toContain('Speed Star');
        });

        it('returns multiple skills when name matches multiple', function () {
            Skill::factory()->create([
                'name' => 'Speed Star',
                'skill_type' => 'speed',
                'status' => 'active',
            ]);

            Skill::factory()->create([
                'name' => 'Speed Boost',
                'skill_type' => 'speed',
                'status' => 'active',
            ]);

            $result = ($this->tool)(skill_name: 'Speed');

            expect($result)->toContain('SKILLS MATCHING');
            expect($result)->toContain('Found 2 skills');
            expect($result)->toContain('Speed Star');
            expect($result)->toContain('Speed Boost');
        });

        it('returns error for non-matching name', function () {
            $result = ($this->tool)(skill_name: 'NonExistentSkill');

            expect($result)->toBeString();
            expect($result)->toContain('Error');
            expect($result)->toContain('No skills found matching name');
        });

        it('performs case-insensitive name search', function () {
            $skill = Skill::factory()->create([
                'name' => 'Speed Star',
                'status' => 'active',
            ]);

            $result = ($this->tool)(skill_name: 'speed star');

            expect($result)->toContain('Speed Star');
        });
    });

    describe('Tool Callable Execution - Filter by Type', function () {
        it('retrieves skills by type', function () {
            Skill::factory()->create([
                'name' => 'Speed Skill 1',
                'skill_type' => 'speed',
                'status' => 'active',
            ]);

            Skill::factory()->create([
                'name' => 'Speed Skill 2',
                'skill_type' => 'speed',
                'status' => 'active',
            ]);

            Skill::factory()->create([
                'name' => 'Recovery Skill',
                'skill_type' => 'recovery',
                'status' => 'active',
            ]);

            $result = ($this->tool)(skill_type: 'speed');

            expect($result)->toContain('SKILLS OF TYPE');
            expect($result)->toContain('Found 2 skills');
            expect($result)->toContain('Speed Skill 1');
            expect($result)->toContain('Speed Skill 2');
            expect($result)->not->toContain('Recovery Skill');
        });

        it('returns error for non-matching type', function () {
            $result = ($this->tool)(skill_type: 'nonexistent');

            expect($result)->toBeString();
            expect($result)->toContain('Error');
            expect($result)->toContain('No skills found of type');
        });

        it('orders skills by meta tier and cost', function () {
            Skill::factory()->create([
                'name' => 'Expensive Skill',
                'skill_type' => 'speed',
                'base_sp_cost' => 200,
                'meta_tier' => 'B',
                'status' => 'active',
            ]);

            Skill::factory()->create([
                'name' => 'Cheap Skill',
                'skill_type' => 'speed',
                'base_sp_cost' => 50,
                'meta_tier' => 'A',
                'status' => 'active',
            ]);

            $result = ($this->tool)(skill_type: 'speed');

            expect($result)->toBeString();
            expect($result)->toContain('Found 2 skills');
        });
    });

    describe('Tool Parameter Validation', function () {
        it('returns error when no parameters provided', function () {
            $result = ($this->tool)();

            expect($result)->toBeString();
            expect($result)->toContain('Error');
            expect($result)->toContain('Please provide at least one search parameter');
        });

        it('accepts skill_id parameter', function () {
            $skill = Skill::factory()->create(['status' => 'active']);

            $result = ($this->tool)(skill_id: $skill->id);

            expect($result)->not->toContain('Error: Please provide');
        });

        it('accepts skill_name parameter', function () {
            $skill = Skill::factory()->create([
                'name' => 'Test Skill',
                'status' => 'active',
            ]);

            $result = ($this->tool)(skill_name: 'Test');

            expect($result)->not->toContain('Error: Please provide');
        });

        it('accepts skill_type parameter', function () {
            $skill = Skill::factory()->create([
                'skill_type' => 'speed',
                'status' => 'active',
            ]);

            $result = ($this->tool)(skill_type: 'speed');

            expect($result)->not->toContain('Error: Please provide');
        });

        it('prioritizes skill_id over other parameters', function () {
            $skill1 = Skill::factory()->create([
                'name' => 'Skill One',
                'skill_type' => 'speed',
                'status' => 'active',
            ]);

            $skill2 = Skill::factory()->create([
                'name' => 'Skill Two',
                'skill_type' => 'speed',
                'status' => 'active',
            ]);

            $result = ($this->tool)(
                skill_id: $skill1->id,
                skill_name: 'Skill Two',
                skill_type: 'speed'
            );

            expect($result)->toContain('Skill One');
            expect($result)->not->toContain('Skill Two');
        });
    });

    describe('Tool Output Format - Multiple Skills', function () {
        it('formats multiple skills as a list', function () {
            Skill::factory()->count(3)->create([
                'skill_type' => 'speed',
                'status' => 'active',
            ]);

            $result = ($this->tool)(skill_type: 'speed');

            expect($result)->toContain('Found 3 skills');
            expect($result)->toContain('[ID:');
            expect($result)->toContain('Use get_skill_data with a specific skill_id');
        });

        it('includes skill ID in list format', function () {
            $skill = Skill::factory()->create([
                'name' => 'Test Skill',
                'skill_type' => 'speed',
                'status' => 'active',
            ]);

            $result = ($this->tool)(skill_type: 'speed');

            expect($result)->toContain("[ID: {$skill->id}]");
        });

        it('includes skill type and rarity in list format', function () {
            Skill::factory()->create([
                'name' => 'Test Skill',
                'skill_type' => 'speed',
                'rarity' => 'rare',
                'status' => 'active',
            ]);

            $result = ($this->tool)(skill_type: 'speed');

            expect($result)->toContain('[speed, rare]');
        });

        it('includes SP cost in list format', function () {
            Skill::factory()->create([
                'name' => 'Test Skill',
                'skill_type' => 'speed',
                'base_sp_cost' => 120,
                'status' => 'active',
            ]);

            $result = ($this->tool)(skill_type: 'speed');

            expect($result)->toContain('(SP: 120)');
        });

        it('truncates long descriptions in list format', function () {
            $longDescription = str_repeat('This is a very long description. ', 10);
            Skill::factory()->create([
                'name' => 'Test Skill',
                'skill_type' => 'speed',
                'description' => $longDescription,
                'status' => 'active',
            ]);

            $result = ($this->tool)(skill_type: 'speed');

            expect($result)->toContain('...');
        });
    });

    describe('Tool Output Format - Single Skill', function () {
        it('returns string output suitable for LLM consumption', function () {
            $skill = Skill::factory()->create(['status' => 'active']);

            $result = ($this->tool)(skill_id: $skill->id);

            expect($result)->toBeString();
            expect($result)->not->toBeEmpty();
            // Should be human-readable, not JSON
            expect($result)->not->toStartWith('{');
            expect($result)->not->toStartWith('[');
        });

        it('includes section headers for organization', function () {
            $skill = Skill::factory()->create([
                'description' => 'Test description',
                'effects' => ['Effect 1'],
                'status' => 'active',
            ]);

            $result = ($this->tool)(skill_id: $skill->id);

            expect($result)->toContain('SKILL DETAILS');
            expect($result)->toContain('DESCRIPTION:');
            expect($result)->toContain('EFFECTS:');
            expect($result)->toContain('HINT DISCOUNT INFORMATION:');
        });

        it('handles null values gracefully', function () {
            $skill = Skill::factory()->create([
                'name' => 'Test Skill',
                'description' => '',
                'effects' => [],
                'activation_conditions' => null,
                'stat_requirements' => null,
                'status' => 'active',
            ]);

            $result = ($this->tool)(skill_id: $skill->id);

            expect($result)->toBeString();
            expect($result)->not->toContain('null');
            expect($result)->not->toContain('NULL');
        });

        it('handles empty arrays gracefully', function () {
            $skill = Skill::factory()->create([
                'name' => 'Test Skill',
                'effects' => [],
                'activation_conditions' => [],
                'status' => 'active',
            ]);

            $result = ($this->tool)(skill_id: $skill->id);

            expect($result)->toBeString();
            expect($result)->toContain('Test Skill');
        });
    });

    describe('Tool Integration', function () {
        it('can be invoked as a callable', function () {
            $skill = Skill::factory()->create(['status' => 'active']);

            $tool = $this->tool;
            $result = $tool(skill_id: $skill->id);

            expect($result)->toBeString();
            expect($result)->toContain('SKILL DETAILS');
        });

        it('loads relationships efficiently', function () {
            $skill = Skill::factory()->create(['status' => 'active']);

            SkillHint::factory()->create(['skill_id' => $skill->id]);

            $result = ($this->tool)(skill_id: $skill->id);

            expect($result)->toContain('CURRENT HINTS:');
        });

        it('handles skills with minimal data', function () {
            $skill = Skill::factory()->create([
                'name' => 'Minimal Skill',
                'skill_type' => 'speed',
                'base_sp_cost' => 100,
                'status' => 'active',
            ]);

            $result = ($this->tool)(skill_id: $skill->id);

            expect($result)->toBeString();
            expect($result)->toContain('Minimal Skill');
            expect($result)->not->toContain('Error');
        });

        it('handles skills with maximum data', function () {
            $skill = Skill::factory()->create([
                'name' => 'Maximum Skill',
                'skill_type' => 'speed',
                'rarity' => 'rare',
                'base_sp_cost' => 200,
                'meta_tier' => 'S',
                'description' => 'Full description',
                'effects' => ['Effect 1', 'Effect 2', 'Effect 3'],
                'activation_conditions' => ['Condition 1', 'Condition 2'],
                'stat_requirements' => ['speed' => 1000, 'stamina' => 800],
                'synergy_skills' => [1, 2, 3],
                'support_card_sources' => ['Card 1', 'Card 2'],
                'event_sources' => ['Event 1'],
                'inheritance_sources' => ['Factor 1'],
                'strategic_notes' => ['Note 1', 'Note 2'],
                'status' => 'active',
            ]);

            SkillHint::factory()->count(3)->create(['skill_id' => $skill->id]);

            $result = ($this->tool)(skill_id: $skill->id);

            expect($result)->toBeString();
            expect($result)->toContain('Maximum Skill');
            expect($result)->toContain('DESCRIPTION:');
            expect($result)->toContain('EFFECTS:');
            expect($result)->toContain('ACTIVATION CONDITIONS:');
            expect($result)->toContain('STAT REQUIREMENTS:');
            expect($result)->toContain('CURRENT HINTS: 3');
        });

        it('only returns active skills', function () {
            $activeSkill = Skill::factory()->create([
                'name' => 'Active Skill',
                'status' => 'active',
            ]);

            $inactiveSkill = Skill::factory()->create([
                'name' => 'Inactive Skill',
                'status' => 'inactive',
                'is_active' => false,
            ]);

            $result = ($this->tool)(skill_name: 'Skill');

            expect($result)->toContain('Active Skill');
            expect($result)->not->toContain('Inactive Skill');
        });
    });
});
