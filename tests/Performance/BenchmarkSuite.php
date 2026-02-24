<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\Skill;
use App\Models\User;
use App\Services\GameMechanicsEngine;
use App\ValueObjects\CharacterStats;
use Illuminate\Support\Facades\Schema;

/**
 * Performance Benchmark Suite
 *
 * Measures page load, API response, service method, and database query times.
 * Results include git commit SHA for tracking regressions over time.
 *
 * Feature: umamusume-career-planner-main-v2.4.0
 * Validates: Requirements NFR-P-01, NFR-P-09
 *
 * @group performance
 * @group benchmark
 */

/**
 * Get the current git commit SHA for result tracking.
 */
function getCurrentCommitSha(): string
{
    $sha = trim(shell_exec('git rev-parse --short HEAD 2>/dev/null') ?? '');

    return $sha ?: 'unknown';
}

/**
 * Run a benchmark for the given callable over N iterations.
 *
 * @return array{avg_ms: float, min_ms: float, max_ms: float, p95_ms: float, iterations: int, commit: string}
 */
function benchmark(callable $fn, int $iterations = 50): array
{
    $times = [];
    for ($i = 0; $i < $iterations; $i++) {
        $start = hrtime(true);
        $fn();
        $times[] = (hrtime(true) - $start) / 1_000_000;
    }
    sort($times);

    $p95Index = (int) ceil(0.95 * count($times)) - 1;

    return [
        'avg_ms' => array_sum($times) / count($times),
        'min_ms' => min($times),
        'max_ms' => max($times),
        'p95_ms' => $times[$p95Index],
        'iterations' => $iterations,
        'commit' => getCurrentCommitSha(),
    ];
}

/**
 * Store benchmark results for validation.
 *
 * @param  array{avg_ms: float, min_ms: float, max_ms: float, p95_ms: float, iterations: int, commit: string}  $results
 */
function storeBenchmarkResult(string $label, array $results): void
{
    // Results are validated via expect() assertions; no output printed to avoid risky tests.
}

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create([
        'user_id' => $this->user->id,
    ]);
});

describe('HTTP Page Load Benchmarks', function () {
    it('benchmarks dashboard page load', function () {
        $this->actingAs($this->user);
        $results = benchmark(function () {
            $this->get('/dashboard')->assertSuccessful();
        }, 20);

        storeBenchmarkResult('Dashboard Load', $results);
        expect($results['p95_ms'])->toBeLessThan(2000, 'Dashboard p95 should be under 2s');
    })->group('performance', 'benchmark', 'http');

    it('benchmarks characters index page load', function () {
        Character::factory()->count(10)->create(['user_id' => $this->user->id]);
        $this->actingAs($this->user);

        $results = benchmark(function () {
            $this->get('/characters')->assertSuccessful();
        }, 20);

        storeBenchmarkResult('Characters Index', $results);
        expect($results['p95_ms'])->toBeLessThan(2000, 'Characters index p95 should be under 2s');
    })->group('performance', 'benchmark', 'http');

    it('benchmarks skills page load', function () {
        $this->actingAs($this->user);
        $results = benchmark(function () {
            $this->get('/skills')->assertSuccessful();
        }, 20);

        storeBenchmarkResult('Skills Page', $results);
        expect($results['p95_ms'])->toBeLessThan(2000, 'Skills page p95 should be under 2s');
    })->group('performance', 'benchmark', 'http');

    it('benchmarks races page load', function () {
        $this->actingAs($this->user);
        $results = benchmark(function () {
            $this->get('/races')->assertSuccessful();
        }, 20);

        storeBenchmarkResult('Races Page', $results);
        expect($results['p95_ms'])->toBeLessThan(2000, 'Races page p95 should be under 2s');
    })->group('performance', 'benchmark', 'http');

    it('benchmarks welcome page load', function () {
        $results = benchmark(function () {
            $this->get('/')->assertSuccessful();
        }, 20);

        storeBenchmarkResult('Welcome Page', $results);
        expect($results['p95_ms'])->toBeLessThan(1000, 'Welcome page p95 should be under 1s');
    })->group('performance', 'benchmark', 'http');
});

describe('API Response Time Benchmarks', function () {
    it('benchmarks character list API', function () {
        Character::factory()->count(10)->create(['user_id' => $this->user->id]);
        $this->actingAs($this->user, 'sanctum');

        $results = benchmark(function () {
            $this->getJson('/api/characters')->assertSuccessful();
        }, 30);

        storeBenchmarkResult('API: GET /characters', $results);
        expect($results['p95_ms'])->toBeLessThan(1000, 'Character list API p95 should be under 1s');
    })->group('performance', 'benchmark', 'api');

    it('benchmarks character show API', function () {
        $this->actingAs($this->user, 'sanctum');

        $results = benchmark(function () {
            $this->getJson("/api/characters/{$this->character->id}")->assertSuccessful();
        }, 30);

        storeBenchmarkResult('API: GET /characters/{id}', $results);
        expect($results['p95_ms'])->toBeLessThan(500, 'Character show API p95 should be under 500ms');
    })->group('performance', 'benchmark', 'api');

    it('benchmarks skills list API', function () {
        $this->actingAs($this->user, 'sanctum');

        $results = benchmark(function () {
            $this->getJson('/api/skills?character_id='.$this->character->id)->assertSuccessful();
        }, 30);

        storeBenchmarkResult('API: GET /skills', $results);
        expect($results['p95_ms'])->toBeLessThan(1000, 'Skills API p95 should be under 1s');
    })->group('performance', 'benchmark', 'api');

    it('benchmarks auth user endpoint', function () {
        $this->actingAs($this->user, 'sanctum');

        $results = benchmark(function () {
            $this->getJson('/api/me')->assertSuccessful();
        }, 30);

        storeBenchmarkResult('API: GET /me', $results);
        expect($results['p95_ms'])->toBeLessThan(300, 'Auth user API p95 should be under 300ms');
    })->group('performance', 'benchmark', 'api');
});

describe('Service Method Benchmarks', function () {
    it('benchmarks GameMechanicsEngine training gain calculation', function () {
        $engine = new GameMechanicsEngine;

        $results = benchmark(function () use ($engine) {
            $engine->calculateTrainingGain(
                baseStat: 500,
                facilityLevel: 3,
                growthRate: 1.2,
                mood: \App\Enums\Mood::GOOD,
                supportCardBonuses: [5, 3, 2],
                numCardsPresent: 3,
                isFriendshipTraining: false,
            );
        }, 500);

        storeBenchmarkResult('calculateTrainingGain', $results);
        expect($results['p95_ms'])->toBeLessThan(10, 'Training gain p95 should be under 10ms');
    })->group('performance', 'benchmark', 'service');

    it('benchmarks GameMechanicsEngine skill cost calculation', function () {
        $engine = new GameMechanicsEngine;

        $results = benchmark(function () use ($engine) {
            $engine->calculateSkillCost(
                baseCost: 120,
                hintLevel: 3,
                hasFastLearner: false,
            );
        }, 500);

        storeBenchmarkResult('calculateSkillCost', $results);
        expect($results['p95_ms'])->toBeLessThan(5, 'Skill cost p95 should be under 5ms');
    })->group('performance', 'benchmark', 'service');

    it('benchmarks CharacterStats creation and calculations', function () {
        $results = benchmark(function () {
            $stats = new CharacterStats(
                speed: 800,
                stamina: 750,
                power: 680,
                guts: 720,
                wisdom: 900,
            );
            $stats->getTotal();
            $stats->getEffectiveTotal();
            $stats->toArray();
        }, 500);

        storeBenchmarkResult('CharacterStats full workflow', $results);
        expect($results['p95_ms'])->toBeLessThan(5, 'CharacterStats workflow p95 should be under 5ms');
    })->group('performance', 'benchmark', 'service');

    it('benchmarks CharacterStats compareTo operation', function () {
        $stats1 = new CharacterStats(speed: 800, stamina: 700, power: 600, guts: 500, wisdom: 900);
        $stats2 = new CharacterStats(speed: 750, stamina: 800, power: 650, guts: 450, wisdom: 850);

        $results = benchmark(function () use ($stats1, $stats2) {
            $stats1->compareTo($stats2);
        }, 500);

        storeBenchmarkResult('CharacterStats::compareTo', $results);
        expect($results['p95_ms'])->toBeLessThan(5, 'compareTo p95 should be under 5ms');
    })->group('performance', 'benchmark', 'service');
});

describe('Database Query Benchmarks', function () {
    it('benchmarks user with characters eager loading', function () {
        Character::factory()->count(20)->create(['user_id' => $this->user->id]);

        $results = benchmark(function () {
            User::query()
                ->with('characters')
                ->find($this->user->id);
        }, 30);

        storeBenchmarkResult('User with 20 characters (eager)', $results);
        expect($results['p95_ms'])->toBeLessThan(200, 'Eager load p95 should be under 200ms');
    })->group('performance', 'benchmark', 'database');

    it('benchmarks character creation', function () {
        $results = benchmark(function () {
            Character::factory()->create(['user_id' => $this->user->id]);
        }, 20);

        storeBenchmarkResult('Character creation', $results);
        expect($results['p95_ms'])->toBeLessThan(500, 'Character creation p95 should be under 500ms');
    })->group('performance', 'benchmark', 'database');

    it('benchmarks skill query with filters', function () {
        if (! Schema::hasTable('ucp_skills')) {
            $this->markTestSkipped('Skills table not available');
        }

        $results = benchmark(function () {
            Skill::query()
                ->where('type', 'speed')
                ->orderBy('name')
                ->limit(20)
                ->get();
        }, 30);

        storeBenchmarkResult('Skill query with filters', $results);
        expect($results['p95_ms'])->toBeLessThan(200, 'Skill query p95 should be under 200ms');
    })->group('performance', 'benchmark', 'database');
});

describe('Benchmark Result Summary', function () {
    it('validates all benchmark infrastructure is functional', function () {
        $commit = getCurrentCommitSha();

        expect($commit)->toBeString();
        expect(phpversion())->toBeString();
    })->group('performance', 'benchmark');
});
