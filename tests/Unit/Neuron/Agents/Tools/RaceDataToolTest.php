<?php

declare(strict_types=1);

use App\Models\Career;
use App\Models\Character;
use App\Models\Race;
use App\Models\User;
use App\Neuron\Agents\Tools\RaceDataTool;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Unit tests for RaceDataTool.
 *
 * These tests verify:
 * - Tool registration and properties
 * - Tool callable execution with valid data
 * - Tool parameter validation
 * - Error handling for invalid inputs
 *
 * **Validates: Requirements 10.1, 10.2, 10.3, 10.4**
 */
describe('RaceDataTool', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->tool = new RaceDataTool;
    });

    describe('Tool Registration', function () {
        it('has correct tool name', function () {
            expect($this->tool->getName())->toBe('get_race_data');
        });

        it('has descriptive tool description', function () {
            $description = $this->tool->getDescription();

            expect($description)->toBeString();
            expect($description)->not->toBeEmpty();
            expect($description)->toContain('race');
            expect($description)->toContain('information');
        });

        it('defines required properties', function () {
            $properties = $this->tool->getProperties();

            expect($properties)->toBeArray();
            expect($properties)->not->toBeEmpty();
        });

        it('has race_id property', function () {
            $properties = $this->tool->getProperties();

            $raceIdProperty = collect($properties)->first(
                fn ($prop) => $prop->getName() === 'race_id'
            );

            expect($raceIdProperty)->not->toBeNull();
            expect($raceIdProperty->isRequired())->toBeFalse();
            expect($raceIdProperty->getType()->value)->toBe('integer');
        });

        it('has race_name property', function () {
            $properties = $this->tool->getProperties();

            $raceNameProperty = collect($properties)->first(
                fn ($prop) => $prop->getName() === 'race_name'
            );

            expect($raceNameProperty)->not->toBeNull();
            expect($raceNameProperty->isRequired())->toBeFalse();
            expect($raceNameProperty->getType()->value)->toBe('string');
        });

        it('has race_grade property', function () {
            $properties = $this->tool->getProperties();

            $raceGradeProperty = collect($properties)->first(
                fn ($prop) => $prop->getName() === 'race_grade'
            );

            expect($raceGradeProperty)->not->toBeNull();
            expect($raceGradeProperty->isRequired())->toBeFalse();
            expect($raceGradeProperty->getType()->value)->toBe('string');
        });

        it('has distance_category property', function () {
            $properties = $this->tool->getProperties();

            $distanceProperty = collect($properties)->first(
                fn ($prop) => $prop->getName() === 'distance_category'
            );

            expect($distanceProperty)->not->toBeNull();
            expect($distanceProperty->isRequired())->toBeFalse();
            expect($distanceProperty->getType()->value)->toBe('string');
        });

        it('has surface property', function () {
            $properties = $this->tool->getProperties();

            $surfaceProperty = collect($properties)->first(
                fn ($prop) => $prop->getName() === 'surface'
            );

            expect($surfaceProperty)->not->toBeNull();
            expect($surfaceProperty->isRequired())->toBeFalse();
            expect($surfaceProperty->getType()->value)->toBe('string');
        });

        it('has character_id property', function () {
            $properties = $this->tool->getProperties();

            $characterIdProperty = collect($properties)->first(
                fn ($prop) => $prop->getName() === 'character_id'
            );

            expect($characterIdProperty)->not->toBeNull();
            expect($characterIdProperty->isRequired())->toBeFalse();
            expect($characterIdProperty->getType()->value)->toBe('integer');
        });

        it('has property descriptions', function () {
            $properties = $this->tool->getProperties();

            foreach ($properties as $property) {
                expect($property->getDescription())->toBeString();
                expect($property->getDescription())->not->toBeEmpty();
            }
        });
    });

    describe('Tool Callable Execution - Single Race by ID', function () {
        it('retrieves race by ID successfully', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            $race = Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'Japan Cup',
                'race_grade' => 'G1',
                'distance_meters' => 2400,
                'distance_category' => 'intermediate',
                'surface' => 'turf',
                'track_type' => 'right',
            ]);

            $result = ($this->tool)(race_id: $race->id);

            expect($result)->toBeString();
            expect($result)->toContain('RACE DETAILS');
            expect($result)->toContain('Japan Cup');
            expect($result)->toContain('Grade: G1');
            expect($result)->toContain('Distance: 2400m (intermediate)');
            expect($result)->toContain('Surface: turf');
            expect($result)->toContain('Track Type: right');
        });

        it('includes race conditions', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            $race = Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'weather' => 'sunny',
                'track_condition' => 'good',
                'field_size' => 18,
                'running_style' => 'leading',
            ]);

            $result = ($this->tool)(race_id: $race->id);

            expect($result)->toContain('RACE CONDITIONS:');
            expect($result)->toContain('Weather: sunny');
            expect($result)->toContain('Track Condition: good');
            expect($result)->toContain('Field Size: 18 horses');
            expect($result)->toContain('Recommended Running Style: leading');
        });

        it('includes race result when race has been run', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            $race = Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'finish_position' => 1,
                'won_race' => true,
                'finish_time' => '2:24.5',
                'speed_rating' => 95,
            ]);

            $result = ($this->tool)(race_id: $race->id);

            expect($result)->toContain('RACE RESULT:');
            expect($result)->toContain('Finish Position: 1');
            expect($result)->toContain('(WON!)');
            expect($result)->toContain('Finish Time: 2:24.5');
            expect($result)->toContain('Speed Rating: 95');
        });

        it('includes character stats at race', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            $race = Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'finish_position' => 1,
                'speed_at_race' => 1000,
                'stamina_at_race' => 900,
                'power_at_race' => 800,
                'guts_at_race' => 700,
                'wit_at_race' => 850,
            ]);

            $result = ($this->tool)(race_id: $race->id);

            expect($result)->toContain('CHARACTER STATS AT RACE:');
            expect($result)->toContain('Speed: 1000');
            expect($result)->toContain('Stamina: 900');
            expect($result)->toContain('Power: 800');
            expect($result)->toContain('Guts: 700');
            expect($result)->toContain('Wit: 850');
        });

        it('includes URA Finale information', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            $race = Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'is_ura_finale_race' => true,
                'ura_finale_stage' => 'URA2',
                'ura_finale_requirements' => ['speed' => 800, 'stamina' => 700],
            ]);

            $result = ($this->tool)(race_id: $race->id);

            expect($result)->toContain('URA FINALE RACE:');
            expect($result)->toContain('Stage: URA2');
            expect($result)->toContain('Requirements:');
            expect($result)->toContain('speed: 800');
            expect($result)->toContain('stamina: 700');
        });

        it('includes Unity Cup information', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            $race = Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'is_unity_cup_match' => true,
                'unity_cup_opponent_rank' => 'A',
                'unity_cup_points_earned' => 150,
            ]);

            $result = ($this->tool)(race_id: $race->id);

            expect($result)->toContain('UNITY CUP MATCH:');
            expect($result)->toContain('Opponent Rank: A');
            expect($result)->toContain('Points Earned: 150');
        });

        it('returns error for non-existent race ID', function () {
            $result = ($this->tool)(race_id: 99999);

            expect($result)->toBeString();
            expect($result)->toContain('Error');
            expect($result)->toContain('Race with ID 99999 not found');
        });
    });

    describe('Tool Callable Execution - Search by Name', function () {
        it('retrieves race by exact name match', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            $race = Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'Japan Cup',
            ]);

            $result = ($this->tool)(race_name: 'Japan Cup');

            expect($result)->toBeString();
            expect($result)->toContain('Japan Cup');
        });

        it('retrieves race by partial name match', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            $race = Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'Japan Cup',
            ]);

            $result = ($this->tool)(race_name: 'Japan');

            expect($result)->toContain('Japan Cup');
        });

        it('returns multiple races when name matches multiple', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'Tokyo Derby',
            ]);

            Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'Tokyo Stakes',
            ]);

            $result = ($this->tool)(race_name: 'Tokyo');

            expect($result)->toContain('RACE LIST');
            expect($result)->toContain('Found 2 races');
            expect($result)->toContain('Tokyo Derby');
            expect($result)->toContain('Tokyo Stakes');
        });

        it('returns error for non-matching name', function () {
            $result = ($this->tool)(race_name: 'NonExistentRace');

            expect($result)->toBeString();
            expect($result)->toContain('Error');
            expect($result)->toContain('No races found matching');
        });
    });

    describe('Tool Callable Execution - Filter by Grade', function () {
        it('retrieves races by grade', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'Race 1',
                'race_grade' => 'G1',
            ]);

            Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'Race 2',
                'race_grade' => 'G1',
            ]);

            Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'Race 3',
                'race_grade' => 'G2',
            ]);

            $result = ($this->tool)(race_grade: 'G1');

            expect($result)->toContain('Found 2 races');
            expect($result)->toContain('Race 1');
            expect($result)->toContain('Race 2');
            expect($result)->not->toContain('Race 3');
        });

        it('returns error for non-matching grade', function () {
            $result = ($this->tool)(race_grade: 'G5');

            expect($result)->toBeString();
            expect($result)->toContain('Error');
            expect($result)->toContain('No races found matching');
        });
    });

    describe('Tool Callable Execution - Filter by Distance', function () {
        it('retrieves races by distance category', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'Short Race',
                'distance_category' => 'short',
            ]);

            Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'Long Race',
                'distance_category' => 'long',
            ]);

            $result = ($this->tool)(distance_category: 'short');

            expect($result)->toContain('Short Race');
            expect($result)->not->toContain('Long Race');
        });
    });

    describe('Tool Callable Execution - Filter by Surface', function () {
        it('retrieves races by surface type', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'Turf Race',
                'surface' => 'turf',
            ]);

            Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'Dirt Race',
                'surface' => 'dirt',
            ]);

            $result = ($this->tool)(surface: 'turf');

            expect($result)->toContain('Turf Race');
            expect($result)->not->toContain('Dirt Race');
        });
    });

    describe('Tool Callable Execution - Filter by Character', function () {
        it('retrieves races for specific character', function () {
            $character1 = Character::factory()->create(['user_id' => $this->user->id]);
            $character2 = Character::factory()->create(['user_id' => $this->user->id]);
            $career1 = Career::factory()->create(['character_id' => $character1->id]);
            $career2 = Career::factory()->create(['character_id' => $character2->id]);

            Race::factory()->create([
                'character_id' => $character1->id,
                'career_id' => $career1->id,
                'race_name' => 'Character 1 Race',
            ]);

            Race::factory()->create([
                'character_id' => $character2->id,
                'career_id' => $career2->id,
                'race_name' => 'Character 2 Race',
            ]);

            $result = ($this->tool)(character_id: $character1->id);

            expect($result)->toContain('Character 1 Race');
            expect($result)->not->toContain('Character 2 Race');
        });
    });

    describe('Tool Parameter Validation', function () {
        it('returns error when no parameters provided', function () {
            $result = ($this->tool)();

            expect($result)->toBeString();
            expect($result)->toContain('Error');
            expect($result)->toContain('Please provide at least one search parameter');
        });

        it('accepts race_id parameter', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);
            $race = Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
            ]);

            $result = ($this->tool)(race_id: $race->id);

            expect($result)->not->toContain('Error: Please provide');
        });

        it('accepts race_name parameter', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);
            $race = Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'Test Race',
            ]);

            $result = ($this->tool)(race_name: 'Test');

            expect($result)->not->toContain('Error: Please provide');
        });

        it('accepts race_grade parameter', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);
            $race = Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_grade' => 'G1',
            ]);

            $result = ($this->tool)(race_grade: 'G1');

            expect($result)->not->toContain('Error: Please provide');
        });

        it('accepts distance_category parameter', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);
            $race = Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'distance_category' => 'short',
            ]);

            $result = ($this->tool)(distance_category: 'short');

            expect($result)->not->toContain('Error: Please provide');
        });

        it('accepts surface parameter', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);
            $race = Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'surface' => 'turf',
            ]);

            $result = ($this->tool)(surface: 'turf');

            expect($result)->not->toContain('Error: Please provide');
        });

        it('accepts character_id parameter', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);
            $race = Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
            ]);

            $result = ($this->tool)(character_id: $character->id);

            expect($result)->not->toContain('Error: Please provide');
        });

        it('prioritizes race_id over other parameters', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            $race1 = Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'Race One',
            ]);

            $race2 = Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'Race Two',
            ]);

            $result = ($this->tool)(
                race_id: $race1->id,
                race_name: 'Race Two'
            );

            expect($result)->toContain('Race One');
            expect($result)->not->toContain('Race Two');
        });

        it('combines multiple filter parameters', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'Tokyo Derby',
                'race_grade' => 'G1',
                'surface' => 'turf',
            ]);

            Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'Tokyo Stakes',
                'race_grade' => 'G2',
                'surface' => 'turf',
            ]);

            $result = ($this->tool)(
                race_grade: 'G1',
                surface: 'turf'
            );

            expect($result)->toContain('Tokyo Derby');
            expect($result)->not->toContain('Tokyo Stakes');
        });
    });

    describe('Tool Output Format - Multiple Races', function () {
        it('formats multiple races as a list', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            Race::factory()->count(3)->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_grade' => 'G1',
            ]);

            $result = ($this->tool)(race_grade: 'G1');

            expect($result)->toContain('RACE LIST');
            expect($result)->toContain('Found 3 races');
            expect($result)->toContain('[ID:');
            expect($result)->toContain('Use get_race_data with a specific race_id');
        });

        it('includes race ID in list format', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            $race = Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'Test Race',
            ]);

            Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'Test Cup',
            ]);

            $result = ($this->tool)(race_name: 'Test');

            expect($result)->toContain("[ID: {$race->id}]");
        });

        it('includes race grade, distance, and surface in list format', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'Test Race',
                'race_grade' => 'G1',
                'distance_meters' => 2400,
                'surface' => 'turf',
            ]);

            Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'Test Stakes',
                'race_grade' => 'G2',
            ]);

            $result = ($this->tool)(race_name: 'Test');

            expect($result)->toContain('[G1, 2400m, turf]');
        });

        it('indicates URA Finale races in list', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'URA Race',
                'is_ura_finale_race' => true,
            ]);

            Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'URA Trial',
            ]);

            $result = ($this->tool)(race_name: 'URA');

            expect($result)->toContain('[URA FINALE]');
        });

        it('indicates Unity Cup races in list', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'Unity Race',
                'is_unity_cup_match' => true,
            ]);

            Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'Unity Trial',
            ]);

            $result = ($this->tool)(race_name: 'Unity');

            expect($result)->toContain('[UNITY CUP]');
        });

        it('shows win indicator for won races', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'Won Race',
                'finish_position' => 1,
                'won_race' => true,
            ]);

            Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'Won Trial',
                'finish_position' => 5,
                'won_race' => false,
            ]);

            $result = ($this->tool)(race_name: 'Won');

            expect($result)->toContain('✓ WON');
        });

        it('shows finish position for completed races', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'Completed Race',
                'finish_position' => 3,
                'won_race' => false,
            ]);

            Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'Completed Trial',
                'finish_position' => 6,
                'won_race' => false,
            ]);

            $result = ($this->tool)(race_name: 'Completed');

            expect($result)->toContain('(Finished: 3)');
        });
    });

    describe('Tool Output Format - Single Race', function () {
        it('returns string output suitable for LLM consumption', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);
            $race = Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
            ]);

            $result = ($this->tool)(race_id: $race->id);

            expect($result)->toBeString();
            expect($result)->not->toBeEmpty();
            // Should be human-readable, not JSON
            expect($result)->not->toStartWith('{');
            expect($result)->not->toStartWith('[');
        });

        it('includes section headers for organization', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);
            $race = Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'weather' => 'sunny',
            ]);

            $result = ($this->tool)(race_id: $race->id);

            expect($result)->toContain('RACE DETAILS');
            expect($result)->toContain('RACE CONDITIONS:');
        });

        it('handles null values gracefully', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);
            $race = Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'weather' => 'sunny',
                'track_condition' => 'good',
                'finish_position' => 2,
                'finish_time' => null,
                'margin_of_victory' => null,
            ]);

            $result = ($this->tool)(race_id: $race->id);

            expect($result)->toBeString();
            expect($result)->not->toContain('null');
            expect($result)->not->toContain('NULL');
        });

        it('handles empty arrays gracefully', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);
            $race = Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_conditions' => [],
                'skills_activated' => [],
            ]);

            $result = ($this->tool)(race_id: $race->id);

            expect($result)->toBeString();
            expect($result)->toContain('RACE DETAILS');
        });
    });

    describe('Tool Integration', function () {
        it('can be invoked as a callable', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);
            $race = Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
            ]);

            $tool = $this->tool;
            $result = $tool(race_id: $race->id);

            expect($result)->toBeString();
            expect($result)->toContain('RACE DETAILS');
        });

        it('loads relationships efficiently', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);
            $race = Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
            ]);

            $result = ($this->tool)(race_id: $race->id);

            // Verify character relationship was loaded
            expect($result)->toContain("Character: {$character->name}");
        });

        it('handles races with minimal data', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);
            $race = Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'Minimal Race',
            ]);

            $result = ($this->tool)(race_id: $race->id);

            expect($result)->toBeString();
            expect($result)->toContain('Minimal Race');
            expect($result)->not->toContain('Error');
        });

        it('handles races with maximum data', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);
            $race = Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'Maximum Race',
                'race_grade' => 'G1',
                'distance_meters' => 2400,
                'distance_category' => 'intermediate',
                'surface' => 'turf',
                'track_type' => 'right',
                'weather' => 'sunny',
                'track_condition' => 'good',
                'field_size' => 18,
                'running_style' => 'leading',
                'turn_number' => 30,
                'career_phase' => 'classic',
                'finish_position' => 1,
                'won_race' => true,
                'finish_time' => '2:24.5',
                'speed_rating' => 95,
                'speed_at_race' => 1000,
                'stamina_at_race' => 900,
                'power_at_race' => 800,
                'guts_at_race' => 700,
                'wit_at_race' => 850,
                'character_condition' => 'perfect',
                'motivation' => 'high',
                'energy_level' => 80,
                'fans_gained' => 5000,
                'sp_reward' => 100,
                'is_ura_finale_race' => true,
                'ura_finale_stage' => 'URA_FINAL',
                'strategic_importance' => 'high',
                'race_notes' => 'Perfect conditions',
            ]);

            $result = ($this->tool)(race_id: $race->id);

            expect($result)->toBeString();
            expect($result)->toContain('Maximum Race');
            expect($result)->toContain('RACE CONDITIONS:');
            expect($result)->toContain('RACE RESULT:');
            expect($result)->toContain('CHARACTER STATS AT RACE:');
            expect($result)->toContain('URA FINALE RACE:');
            expect($result)->toContain('REWARDS:');
            expect($result)->toContain('STRATEGIC IMPORTANCE:');
        });

        it('orders races chronologically in list view', function () {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'Race 3',
                'turn_number' => 30,
            ]);

            Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'Race 1',
                'turn_number' => 10,
            ]);

            Race::factory()->create([
                'character_id' => $character->id,
                'career_id' => $career->id,
                'race_name' => 'Race 2',
                'turn_number' => 20,
            ]);

            $result = ($this->tool)(character_id: $character->id);

            // Verify races are ordered by turn number
            $race1Pos = strpos($result, 'Race 1');
            $race2Pos = strpos($result, 'Race 2');
            $race3Pos = strpos($result, 'Race 3');

            expect($race1Pos)->toBeLessThan($race2Pos);
            expect($race2Pos)->toBeLessThan($race3Pos);
        });
    });
});
