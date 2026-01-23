<?php

declare(strict_types=1);

/**
 * Property-based tests for Tool Output Format.
 *
 * These tests validate universal properties that should hold true
 * across all valid inputs for tool output formatting.
 *
 * Property 11: Tool Output Format
 * For any tool execution, the tool should return data in a format that is
 * easily consumable by an LLM (structured arrays, descriptive strings, or JSON).
 *
 * **Validates: Requirements 10.5**
 *
 * Testing Strategy:
 * - Execute tools with random valid inputs
 * - Verify outputs are non-empty strings or arrays
 * - Verify outputs contain expected structure markers
 * - Test with varying input parameters
 * - Test with minimum 100 iterations
 *
 * Feature: neuron-ai-integration
 * Property: 11 - Tool Output Format
 */

use App\Models\Character;
use App\Models\Skill;
use App\Neuron\Agents\Tools\CharacterStatsTool;
use App\Neuron\Agents\Tools\SkillDataTool;

describe('Property 11: Tool Output Format', function () {
    it('returns LLM-consumable string output for CharacterStatsTool', function () {
        // Create a character with random data
        $character = Character::factory()->create([
            'name' => 'Test Character '.uniqid(),
            'current_stats' => [
                'speed' => rand(400, 1200),
                'stamina' => rand(400, 1200),
                'power' => rand(400, 1200),
                'guts' => rand(400, 1200),
                'wit' => rand(400, 1200),
            ],
        ]);

        // Execute tool
        $tool = new CharacterStatsTool;
        $output = $tool($character->id);

        // Property: Output should be a non-empty string
        expect($output)->toBeString();
        expect($output)->not->toBeEmpty();

        // Property: Output should contain structured sections
        expect($output)->toContain('CHARACTER STATISTICS');
        expect($output)->toContain('CURRENT STATS:');
        expect($output)->toContain('Character:');

        // Property: Output should contain the character's data
        expect($output)->toContain($character->name);
        expect($output)->toContain('Speed:');
        expect($output)->toContain('Stamina:');
        expect($output)->toContain('Power:');
        expect($output)->toContain('Guts:');
        expect($output)->toContain('Wit:');
    })->repeat(100);

    it('returns error message string for invalid CharacterStatsTool input', function () {
        // Use a non-existent character ID
        $invalidId = rand(999999, 9999999);

        // Execute tool
        $tool = new CharacterStatsTool;
        $output = $tool($invalidId);

        // Property: Error output should be a non-empty string
        expect($output)->toBeString();
        expect($output)->not->toBeEmpty();

        // Property: Error output should contain error indicator
        expect($output)->toContain('Error:');
        expect($output)->toContain((string) $invalidId);
    })->repeat(50);

    it('returns LLM-consumable string output for SkillDataTool with skill ID', function () {
        // Create a skill with random data
        $skill = Skill::factory()->create([
            'name' => 'Test Skill '.uniqid(),
            'skill_type' => ['speed', 'passive', 'recovery', 'debuff'][rand(0, 3)],
            'base_sp_cost' => rand(100, 500),
        ]);

        // Execute tool
        $tool = new SkillDataTool;
        $output = $tool(skill_id: $skill->id);

        // Property: Output should be a non-empty string
        expect($output)->toBeString();
        expect($output)->not->toBeEmpty();

        // Property: Output should contain structured sections
        expect($output)->toContain('SKILL DETAILS');
        expect($output)->toContain('Name:');
        expect($output)->toContain('Type:');
        expect($output)->toContain('Base SP Cost:');

        // Property: Output should contain the skill's data
        expect($output)->toContain($skill->name);
        expect($output)->toContain($skill->skill_type);
        expect($output)->toContain((string) $skill->base_sp_cost);
    })->repeat(100);

    it('returns LLM-consumable string output for SkillDataTool with skill type filter', function () {
        // Create multiple skills of the same type
        $skillType = ['speed', 'passive', 'recovery'][rand(0, 2)];
        $skillCount = rand(2, 5);

        for ($i = 0; $i < $skillCount; $i++) {
            Skill::factory()->create([
                'skill_type' => $skillType,
                'name' => "Test {$skillType} Skill {$i}",
            ]);
        }

        // Execute tool
        $tool = new SkillDataTool;
        $output = $tool(skill_type: $skillType);

        // Property: Output should be a non-empty string
        expect($output)->toBeString();
        expect($output)->not->toBeEmpty();

        // Property: Output should contain list structure
        expect($output)->toContain('Found');
        expect($output)->toContain('skills');
        expect($output)->toContain($skillType);
    })->repeat(50);

    it('returns error message string for invalid SkillDataTool input', function () {
        // Use a non-existent skill ID
        $invalidId = rand(999999, 9999999);

        // Execute tool
        $tool = new SkillDataTool;
        $output = $tool(skill_id: $invalidId);

        // Property: Error output should be a non-empty string
        expect($output)->toBeString();
        expect($output)->not->toBeEmpty();

        // Property: Error output should contain error indicator
        expect($output)->toContain('Error:');
        expect($output)->toContain((string) $invalidId);
    })->repeat(50);

    it('returns consistent output format across multiple tool executions', function () {
        // Create test data
        $character = Character::factory()->create();

        // Execute tool multiple times
        $tool = new CharacterStatsTool;
        $output1 = $tool($character->id);
        $output2 = $tool($character->id);
        $output3 = $tool($character->id);

        // Property: Multiple executions should return identical output
        expect($output1)->toBe($output2);
        expect($output2)->toBe($output3);

        // Property: All outputs should be non-empty strings
        expect($output1)->toBeString();
        expect($output1)->not->toBeEmpty();
    })->repeat(50);

    it('returns output with proper line breaks and formatting', function () {
        // Create test data
        $character = Character::factory()->create();

        // Execute tool
        $tool = new CharacterStatsTool;
        $output = $tool($character->id);

        // Property: Output should contain line breaks for readability
        expect($output)->toContain("\n");

        // Property: Output should have section separators
        $lines = explode("\n", $output);
        expect(count($lines))->toBeGreaterThan(5);

        // Property: Output should not be a single long line
        $hasMultipleLines = count($lines) > 1;
        expect($hasMultipleLines)->toBeTrue();
    })->repeat(50);

    it('returns output without sensitive or raw data structures', function () {
        // Create test data
        $character = Character::factory()->create();

        // Execute tool
        $tool = new CharacterStatsTool;
        $output = $tool($character->id);

        // Property: Output should not contain raw PHP array syntax
        expect($output)->not->toContain('Array(');
        expect($output)->not->toContain('stdClass');

        // Property: Output should not contain database column names in raw form
        expect($output)->not->toContain('created_at:');
        expect($output)->not->toContain('updated_at:');

        // Property: Output should be human-readable
        expect($output)->toBeString();
    })->repeat(50);

    it('returns output with clear section headers', function () {
        // Create test data
        $character = Character::factory()->create();

        // Execute tool
        $tool = new CharacterStatsTool;
        $output = $tool($character->id);

        // Property: Output should have clear section headers in uppercase
        expect($output)->toMatch('/[A-Z\s]+:/');

        // Property: Output should have section separators (equals signs)
        expect($output)->toContain('=');
    })->repeat(50);

    it('returns output that is parseable by line', function () {
        // Create test data
        $skill = Skill::factory()->create();

        // Execute tool
        $tool = new SkillDataTool;
        $output = $tool(skill_id: $skill->id);

        // Property: Output should be splittable into lines
        $lines = explode("\n", $output);
        expect($lines)->toBeArray();
        expect(count($lines))->toBeGreaterThan(0);

        // Property: Each line should be a string
        foreach (array_slice($lines, 0, 10) as $line) {
            expect($line)->toBeString();
        }
    })->repeat(50);

    it('returns output with consistent formatting across different tools', function () {
        // Create test data
        $character = Character::factory()->create();
        $skill = Skill::factory()->create();

        // Execute different tools
        $characterTool = new CharacterStatsTool;
        $skillTool = new SkillDataTool;

        $characterOutput = $characterTool($character->id);
        $skillOutput = $skillTool(skill_id: $skill->id);

        // Property: Both outputs should be non-empty strings
        expect($characterOutput)->toBeString();
        expect($skillOutput)->toBeString();
        expect($characterOutput)->not->toBeEmpty();
        expect($skillOutput)->not->toBeEmpty();

        // Property: Both should have section headers
        expect($characterOutput)->toMatch('/[A-Z\s]+:/');
        expect($skillOutput)->toMatch('/[A-Z\s]+:/');

        // Property: Both should have multiple lines
        expect(count(explode("\n", $characterOutput)))->toBeGreaterThan(5);
        expect(count(explode("\n", $skillOutput)))->toBeGreaterThan(5);
    })->repeat(50);
});
