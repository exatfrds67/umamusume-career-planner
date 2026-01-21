<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Test Case Configuration
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
| This configuration is optimized for Laravel 12 with comprehensive database testing,
| parallel execution support, and proper test organization.
|
*/

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

/*
|--------------------------------------------------------------------------
| Feature Tests Configuration
|--------------------------------------------------------------------------
|
| Feature tests use RefreshDatabase to ensure a clean database state for each test.
| This is essential for testing API endpoints, controllers, and user workflows.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(RefreshDatabase::class)
    ->use(WithFaker::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Unit Tests Configuration
|--------------------------------------------------------------------------
|
| Unit tests extend the base TestCase but don't require database refresh
| for most cases. They focus on testing isolated business logic.
|
*/
pest()->extend(Tests\TestCase::class)
    ->use(WithFaker::class)
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Integration Tests Configuration
|--------------------------------------------------------------------------
|
| Integration tests use LazilyRefreshDatabase for better performance
| when testing complex workflows that span multiple components.
|
*/
pest()->extend(Tests\TestCase::class)
    ->use(LazilyRefreshDatabase::class)
    ->use(WithFaker::class)
    ->in('Integration');

/*
|--------------------------------------------------------------------------
| Architecture Tests Configuration
|--------------------------------------------------------------------------
|
| Architecture tests verify code structure and dependencies without
| requiring database access. They ensure code quality standards.
|
*/
pest()->extend(Tests\TestCase::class)
    ->in('Architecture');

/*
|--------------------------------------------------------------------------
| Custom Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions.
| The "expect()" function gives you access to a set of "expectations" methods that you
| can use to assert different things. Here we extend the Expectation API with custom
| assertions specific to the Umamusume Career Planner application.
|
*/

// Verify a value is a valid stat value (0-1200)
expect()->extend('toBeValidStat', function (): Pest\Expectation {
    /** @var Pest\Expectation $expectation */
    $expectation = $this;

    return $expectation->toBeInt()
        ->toBeGreaterThanOrEqual(0)
        ->toBeLessThanOrEqual(1200);
});

// Verify a value is a valid aptitude grade (G through SS)
expect()->extend('toBeValidAptitudeGrade', function (): Pest\Expectation {
    /** @var Pest\Expectation $expectation */
    $expectation = $this;
    $validGrades = ['G', 'G+', 'F', 'F+', 'E', 'E+', 'D', 'D+', 'C', 'C+', 'B', 'B+', 'A', 'A+', 'S', 'S+', 'SS'];

    return $expectation->toBeIn($validGrades);
});

// Verify a value is a valid scenario type
expect()->extend('toBeValidScenarioType', function (): Pest\Expectation {
    /** @var Pest\Expectation $expectation */
    $expectation = $this;

    return $expectation->toBeIn(['ura_finale', 'unity_cup']);
});

// Verify a value is a valid SP cost (positive integer)
expect()->extend('toBeValidSpCost', function (): Pest\Expectation {
    /** @var Pest\Expectation $expectation */
    $expectation = $this;

    return $expectation->toBeInt()->toBeGreaterThan(0);
});

// Verify a value is a valid energy level (0-100)
expect()->extend('toBeValidEnergyLevel', function (): Pest\Expectation {
    /** @var Pest\Expectation $expectation */
    $expectation = $this;

    return $expectation->toBeInt()
        ->toBeGreaterThanOrEqual(0)
        ->toBeLessThanOrEqual(100);
});

// Verify a value is a valid mood status
expect()->extend('toBeValidMoodStatus', function (): Pest\Expectation {
    /** @var Pest\Expectation $expectation */
    $expectation = $this;

    return $expectation->toBeIn(['awful', 'bad', 'normal', 'good', 'great']);
});

// Verify a value is a valid skill type
expect()->extend('toBeValidSkillType', function (): Pest\Expectation {
    /** @var Pest\Expectation $expectation */
    $expectation = $this;

    return $expectation->toBeIn(['normal', 'rare', 'unique', 'inherited']);
});

// Verify a value is a valid support card rarity
expect()->extend('toBeValidCardRarity', function (): Pest\Expectation {
    /** @var Pest\Expectation $expectation */
    $expectation = $this;

    return $expectation->toBeIn(['R', 'SR', 'SSR']);
});

// Verify a value is a valid factor type
expect()->extend('toBeValidFactorType', function (): Pest\Expectation {
    /** @var Pest\Expectation $expectation */
    $expectation = $this;

    return $expectation->toBeIn(['blue_stat', 'red_aptitude', 'green_unique', 'white_normal']);
});

// Verify a value is a valid factor level (1-3 stars)
expect()->extend('toBeValidFactorLevel', function (): Pest\Expectation {
    /** @var Pest\Expectation $expectation */
    $expectation = $this;

    return $expectation->toBeInt()
        ->toBeGreaterThanOrEqual(1)
        ->toBeLessThanOrEqual(3);
});

// Verify a JSON response has the expected structure
expect()->extend('toHaveJsonStructure', function (array $structure): Pest\Expectation {
    /** @var Pest\Expectation $expectation */
    $expectation = $this;
    /** @var array<array-key, mixed> $data */
    $data = (array) $expectation->value;

    foreach ($structure as $key => $value) {
        $expectedKey = is_int($key) ? $value : $key;

        if (is_array($expectedKey)) {
            continue;
        }

        expect($data)->toHaveKey($expectedKey);
    }

    return $expectation;
});

/*
|--------------------------------------------------------------------------
| Global Helper Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Create a test character with default valid stats.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function createTestCharacterData(array $overrides = []): array
{
    return array_merge([
        'name' => 'Test Character',
        'scenario_type' => 'ura_finale',
        'speed_stat' => 300,
        'stamina_stat' => 250,
        'power_stat' => 200,
        'guts_stat' => 150,
        'wit_stat' => 180,
        'energy_level' => 100,
        'mood_status' => 'good',
        'career_stage' => 'junior',
    ], $overrides);
}

/**
 * Create test aptitude data with default valid grades.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function createTestAptitudeData(array $overrides = []): array
{
    return array_merge([
        'sprint_aptitude' => 'A',
        'mile_aptitude' => 'B+',
        'medium_aptitude' => 'C',
        'long_aptitude' => 'D',
        'turf_aptitude' => 'A+',
        'dirt_aptitude' => 'C+',
        'front_runner_aptitude' => 'B',
        'pace_chaser_aptitude' => 'A',
        'late_surger_aptitude' => 'C+',
        'end_closer_aptitude' => 'D+',
    ], $overrides);
}

/**
 * Create test support card data with default valid values.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function createTestSupportCardData(array $overrides = []): array
{
    return array_merge([
        'card_name' => 'Test Support Card',
        'card_rarity' => 'SSR',
        'limit_break_level' => 4,
        'specialization_type' => 'speed',
        'friendship_level' => 80,
        'position_slot' => 1,
        'is_borrowed' => false,
    ], $overrides);
}

/**
 * Create test skill data with default valid values.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function createTestSkillData(array $overrides = []): array
{
    return array_merge([
        'skill_name' => 'Test Skill',
        'skill_type' => 'normal',
        'base_sp_cost' => 120,
        'hint_discount' => 0,
        'is_acquired' => false,
    ], $overrides);
}

/**
 * Generate a random valid stat value (0-1200).
 */
function randomStat(): int
{
    return random_int(0, 1200);
}

/**
 * Generate a random valid aptitude grade.
 */
function randomAptitudeGrade(): string
{
    $grades = ['G', 'G+', 'F', 'F+', 'E', 'E+', 'D', 'D+', 'C', 'C+', 'B', 'B+', 'A', 'A+', 'S', 'S+', 'SS'];

    return $grades[array_rand($grades)];
}

/**
 * Generate a random valid energy level (0-100).
 */
function randomEnergyLevel(): int
{
    return random_int(0, 100);
}

/**
 * Generate a random valid mood status.
 */
function randomMoodStatus(): string
{
    $moods = ['awful', 'bad', 'normal', 'good', 'great'];

    return $moods[array_rand($moods)];
}

/*
|--------------------------------------------------------------------------
| Test Groups and Tags
|--------------------------------------------------------------------------
|
| Define test groups for organizing and filtering tests during execution.
| Use these with: php artisan test --group=api
|
*/

// Groups are defined using the ->group() method in individual test files
// Example: it('does something')->group('api', 'character');

/*
|--------------------------------------------------------------------------
| Datasets
|--------------------------------------------------------------------------
|
| Define reusable datasets for property-based testing and data-driven tests.
| These can be used with the ->with() method in test files.
|
*/

dataset('valid_stats', fn () => [
    'minimum' => [0],
    'low' => [100],
    'medium' => [500],
    'high' => [900],
    'maximum' => [1200],
]);

dataset('invalid_stats', fn () => [
    'negative' => [-1],
    'too_high' => [1201],
    'way_too_high' => [9999],
]);

dataset('valid_aptitude_grades', fn () => [
    'G' => ['G'],
    'G+' => ['G+'],
    'F' => ['F'],
    'F+' => ['F+'],
    'E' => ['E'],
    'E+' => ['E+'],
    'D' => ['D'],
    'D+' => ['D+'],
    'C' => ['C'],
    'C+' => ['C+'],
    'B' => ['B'],
    'B+' => ['B+'],
    'A' => ['A'],
    'A+' => ['A+'],
    'S' => ['S'],
    'S+' => ['S+'],
    'SS' => ['SS'],
]);

dataset('scenario_types', fn () => [
    'ura_finale' => ['ura_finale'],
    'unity_cup' => ['unity_cup'],
]);

dataset('mood_statuses', fn () => [
    'awful' => ['awful'],
    'bad' => ['bad'],
    'normal' => ['normal'],
    'good' => ['good'],
    'great' => ['great'],
]);

dataset('skill_types', fn () => [
    'normal' => ['normal'],
    'rare' => ['rare'],
    'unique' => ['unique'],
    'inherited' => ['inherited'],
]);

dataset('card_rarities', fn () => [
    'R' => ['R'],
    'SR' => ['SR'],
    'SSR' => ['SSR'],
]);

dataset('factor_types', fn () => [
    'blue_stat' => ['blue_stat'],
    'red_aptitude' => ['red_aptitude'],
    'green_unique' => ['green_unique'],
    'white_normal' => ['white_normal'],
]);

dataset('http_methods', fn () => [
    'GET' => ['GET'],
    'POST' => ['POST'],
    'PUT' => ['PUT'],
    'PATCH' => ['PATCH'],
    'DELETE' => ['DELETE'],
]);
