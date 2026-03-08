<?php

declare(strict_types=1);

// Ensure sufficient memory for large test suites and disable Xdebug overhead
@ini_set('memory_limit', '2048M');
@putenv('XDEBUG_MODE=off');
// Provide dummy API keys for providers used in tests to avoid constructor errors
@putenv('ANTHROPIC_KEY=test');

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
| Unit tests extend the base TestCase. Model tests require database refresh
| to test relationships and attributes. Service tests may not need database.
|
*/
pest()->extend(Tests\TestCase::class)
    ->use(RefreshDatabase::class)
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
| Property Tests Configuration
|--------------------------------------------------------------------------
|
| Property-based tests verify universal properties that should hold true
| across all valid inputs. These tests use repetition to validate
| correctness properties with randomly generated data.
|
*/
pest()->extend(Tests\TestCase::class)
    ->use(RefreshDatabase::class)
    ->use(WithFaker::class)
    ->in('Property');

/*
|--------------------------------------------------------------------------
| Performance Tests Configuration
|--------------------------------------------------------------------------
|
| Performance tests benchmark critical operations and detect regressions.
| These tests measure response times, throughput, and resource usage
| against configurable thresholds.
|
*/
pest()->extend(Tests\TestCase::class)
    ->use(RefreshDatabase::class)
    ->use(WithFaker::class)
    ->in('Performance');

/*
|--------------------------------------------------------------------------
| Security Tests Configuration
|--------------------------------------------------------------------------
|
| Security tests verify protection against common vulnerabilities
| including SQL injection, XSS, CSRF, and authorization bypass.
| These tests ensure the application meets NFR-S requirements.
|
*/
pest()->extend(Tests\TestCase::class)
    ->use(RefreshDatabase::class)
    ->use(WithFaker::class)
    ->in('Security');

/*
|--------------------------------------------------------------------------
| Browser Tests Configuration
|--------------------------------------------------------------------------
|
| Browser tests use Pest 4's built-in browser testing capabilities to test
| the full application workflow in a real browser environment. These tests
| verify end-to-end user journeys and UI interactions.
|
*/
pest()->browser()->timeout(30000);

pest()->extend(Tests\TestCase::class)
    ->use(RefreshDatabase::class)
    ->use(WithFaker::class)
    ->beforeEach(function () {
        // Ensure browser tests use production Vite build instead of dev server.
        // When `npm run dev` is running, a `public/hot` file exists which makes
        // @vite generate dev server URLs (http://[::1]:5173/...). The Pest browser
        // test server cannot reach the Vite dev server, so JS assets fail to load.
        // Temporarily move the hot file so @vite uses the production manifest.
        $hotFile = public_path('hot');
        $activeBackupFile = public_path('hot.browser-test-active-backup');

        // If a stale backup already exists, still move the active hot file out of the way.
        if (file_exists($hotFile)) {
            if (file_exists($activeBackupFile)) {
                unlink($activeBackupFile);
            }

            rename($hotFile, $activeBackupFile);
        }
    })
    ->afterEach(function () {
        $hotFile = public_path('hot');
        $activeBackupFile = public_path('hot.browser-test-active-backup');

        if (file_exists($activeBackupFile) && ! file_exists($hotFile)) {
            rename($activeBackupFile, $hotFile);
        }
    })
    ->in('Browser');

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

// Verify a value is a valid stat value (0-2000, with soft cap at 1200)
// VERIFIED (Jan 2026): Stats can exceed 1200 with diminishing returns (50% value above 1200)
// Important breakpoints: 901, 1200 (soft cap), 1600
expect()->extend('toBeValidStat', function () {
    /** @var Pest\Expectation<mixed> $expectation */
    $expectation = $this;

    return $expectation->toBeInt()
        ->toBeGreaterThanOrEqual(0)
        ->toBeLessThanOrEqual(2000); // Practical maximum (1200 + 800 effective)
});

// Verify a value is a valid aptitude grade (G through S - S is maximum, SS does NOT exist)
// VERIFIED (Jan 2026): S-rank is the maximum aptitude grade in current game version
expect()->extend('toBeValidAptitudeGrade', function () {
    /** @var Pest\Expectation<mixed> $expectation */
    $expectation = $this;
    $validGrades = ['G', 'G+', 'F', 'F+', 'E', 'E+', 'D', 'D+', 'C', 'C+', 'B', 'B+', 'A', 'A+', 'S'];

    return $expectation->toBeIn($validGrades);
});

// Verify a value is a valid scenario type
expect()->extend('toBeValidScenarioType', function () {
    /** @var Pest\Expectation<mixed> $expectation */
    $expectation = $this;

    return $expectation->toBeIn(['ura_finale', 'unity_cup']);
});

// Verify a value is a valid SP cost (positive integer)
expect()->extend('toBeValidSpCost', function () {
    /** @var Pest\Expectation<mixed> $expectation */
    $expectation = $this;

    return $expectation->toBeInt()->toBeGreaterThan(0);
});

// Verify a value is a valid energy level (0-100)
expect()->extend('toBeValidEnergyLevel', function () {
    /** @var Pest\Expectation<mixed> $expectation */
    $expectation = $this;

    return $expectation->toBeInt()
        ->toBeGreaterThanOrEqual(0)
        ->toBeLessThanOrEqual(100);
});

// Verify a value is a valid mood status
expect()->extend('toBeValidMoodStatus', function () {
    /** @var Pest\Expectation<mixed> $expectation */
    $expectation = $this;

    return $expectation->toBeIn(['awful', 'bad', 'normal', 'good', 'great']);
});

// Verify a value is a valid skill type
expect()->extend('toBeValidSkillType', function () {
    /** @var Pest\Expectation<mixed> $expectation */
    $expectation = $this;

    return $expectation->toBeIn(['normal', 'rare', 'unique', 'inherited']);
});

// Verify a value is a valid support card rarity
expect()->extend('toBeValidCardRarity', function () {
    /** @var Pest\Expectation<mixed> $expectation */
    $expectation = $this;

    return $expectation->toBeIn(['R', 'SR', 'SSR']);
});

// Verify a value is a valid factor type
expect()->extend('toBeValidFactorType', function () {
    /** @var Pest\Expectation<mixed> $expectation */
    $expectation = $this;

    return $expectation->toBeIn(['blue_stat', 'red_aptitude', 'green_unique', 'white_normal']);
});

// Verify a value is a valid factor level (1-3 stars)
expect()->extend('toBeValidFactorLevel', function () {
    /** @var Pest\Expectation<mixed> $expectation */
    $expectation = $this;

    return $expectation->toBeInt()
        ->toBeGreaterThanOrEqual(1)
        ->toBeLessThanOrEqual(3);
});

// Verify a JSON response has the expected structure
expect()->extend('toHaveJsonStructure', function (array $structure) {
    /** @var Pest\Expectation<mixed> $expectation */
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
 * Generate a random valid stat value (0-2000, with soft cap at 1200).
 * VERIFIED (Jan 2026): Stats above 1200 have diminishing returns (50% value).
 */
function randomStat(): int
{
    return random_int(0, 2000);
}

/**
 * Generate a random valid aptitude grade.
 * VERIFIED (Jan 2026): S is maximum, SS does not exist in game
 */
function randomAptitudeGrade(): string
{
    $grades = ['G', 'G+', 'F', 'F+', 'E', 'E+', 'D', 'D+', 'C', 'C+', 'B', 'B+', 'A', 'A+', 'S'];

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
 * Calculate effective stat value with diminishing returns above 1200.
 * VERIFIED (Jan 2026): Stats above 1200 count for half value.
 *
 * Example: 1500 actual = 1200 + (300 × 0.5) = 1350 effective
 *
 * @param  int  $actualStat  The actual stat value
 * @return float The effective stat value after diminishing returns
 */
function calculateEffectiveStat(int $actualStat): float
{
    if ($actualStat <= 1200) {
        return (float) $actualStat;
    }

    // Above 1200: base 1200 + (excess × 0.5)
    $excess = $actualStat - 1200;

    return 1200.0 + ($excess * 0.5);
}

/**
 * Generate a random valid mood status.
 */
function randomMoodStatus(): string
{
    $moods = ['awful', 'bad', 'normal', 'good', 'great'];

    return $moods[array_rand($moods)];
}

/**
 * Create a test TrainingContext with default valid values.
 *
 * @param  array<string, mixed>  $overrides
 */
function createTestTrainingContext(array $overrides = []): \App\ValueObjects\TrainingContext
{
    $defaults = [
        'turn_number' => 15,
        'phase' => 'classic_year',
        'stats' => [
            'speed' => 500,
            'stamina' => 450,
            'power' => 480,
            'guts' => 400,
            'wisdom' => 450,
        ],
        'sp_available' => 180,
        'energy' => 75,
        'mood' => 'normal',
        'acquired_skills' => [],
        'skill_hints' => [],
        'support_deck' => [
            'cards' => [],
        ],
        'facility_levels' => [
            'speed' => 3,
            'stamina' => 2,
            'power' => 3,
            'guts' => 2,
            'wisdom' => 4,
        ],
        'upcoming_races' => [],
        'scenario' => null,
        'storage_mode' => 'account',
        'career_run_id' => 1,
    ];

    $data = array_merge($defaults, $overrides);

    return \App\ValueObjects\TrainingContext::fromArray($data);
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

/**
 * Valid aptitude grades for characters.
 * VERIFIED (Jan 2026): S is the maximum aptitude grade. SS does NOT exist.
 */
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
    'S' => ['S'],  // Maximum aptitude grade
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
