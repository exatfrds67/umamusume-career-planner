<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\User;
use App\Services\GameMechanicsEngine;
use App\ValueObjects\CharacterStats;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Performance Regression Property Tests
 *
 * Property 30: API Response Time Bounds
 * Property 31: Performance Regression Detection
 *
 * Feature: umamusume-career-planner-main-v2.4.0
 * Validates: Requirements NFR-P-01, NFR-P-09
 *
 * @group performance
 * @group property
 */
beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create(['user_id' => $this->user->id]);
    RateLimiter::clear('api:'.$this->user->id);
    RateLimiter::clear('api');
});

describe('Property 30: API Response Time Bounds', function () {
    it('ensures GET /api/me responds within 500ms for 50 consecutive requests', function () {
        $this->actingAs($this->user, 'sanctum');

        $times = [];
        for ($i = 0; $i < 50; $i++) {
            RateLimiter::clear('api:'.$this->user->id);
            $start = hrtime(true);
            $this->getJson('/api/me')->assertSuccessful();
            $times[] = (hrtime(true) - $start) / 1_000_000;
        }

        sort($times);
        $p95 = $times[(int) ceil(0.95 * count($times)) - 1];

        expect($p95)->toBeLessThan(500, "p95 response time: {$p95}ms");
    })->group('performance', 'property');

    it('ensures character API responds within bounds regardless of character count', function () {
        $this->actingAs($this->user, 'sanctum');

        $counts = [1, 5, 10, 20];

        foreach ($counts as $count) {
            Character::factory()->count($count)->create(['user_id' => $this->user->id]);
            RateLimiter::clear('api:'.$this->user->id);

            $times = [];
            for ($i = 0; $i < 10; $i++) {
                RateLimiter::clear('api:'.$this->user->id);
                $start = hrtime(true);
                $this->getJson('/api/characters')->assertSuccessful();
                $times[] = (hrtime(true) - $start) / 1_000_000;
            }

            sort($times);
            $p95 = $times[(int) ceil(0.95 * count($times)) - 1];

            expect($p95)->toBeLessThan(2000, "p95 with {$count} characters: {$p95}ms");
        }
    })->group('performance', 'property');

    it('ensures authentication endpoints respond within 800ms', function () {
        $times = [];
        for ($i = 0; $i < 20; $i++) {
            $start = hrtime(true);
            $this->postJson('/api/login', ['email' => 'nonexistent@test.com', 'password' => 'password']);
            $times[] = (hrtime(true) - $start) / 1_000_000;
        }

        sort($times);
        $p95 = $times[(int) ceil(0.95 * count($times)) - 1];

        expect($p95)->toBeLessThan(800, "POST /api/login p95: {$p95}ms");
    })->group('performance', 'property');
});

describe('Property 31: Performance Regression Detection', function () {
    it('verifies service method performance is consistent across iterations', function () {
        $engine = new GameMechanicsEngine;

        $batchTimings = [];
        for ($batch = 0; $batch < 5; $batch++) {
            $start = hrtime(true);
            for ($i = 0; $i < 100; $i++) {
                $engine->calculateTrainingGain(
                    baseStat: rand(100, 1200),
                    facilityLevel: rand(1, 5),
                    growthRate: rand(80, 150) / 100,
                    mood: \App\Enums\Mood::GOOD,
                    supportCardBonuses: [rand(0, 10), rand(0, 5)],
                    numCardsPresent: rand(1, 6),
                    isFriendshipTraining: (bool) rand(0, 1),
                );
            }
            $batchTimings[] = (hrtime(true) - $start) / 1_000_000;
        }

        $avg = array_sum($batchTimings) / count($batchTimings);
        $maxDeviation = max($batchTimings) / $avg;

        expect($maxDeviation)->toBeLessThan(3.0, 'No batch should take more than 3x the average');

        foreach ($batchTimings as $index => $time) {
            expect($time)->toBeLessThan(500, "Batch {$index}: {$time}ms should be under 500ms");
        }
    })->group('performance', 'property');

    it('verifies CharacterStats operations scale linearly', function () {
        $sizes = [10, 50, 100, 200];
        $timings = [];

        foreach ($sizes as $size) {
            $start = hrtime(true);
            for ($i = 0; $i < $size; $i++) {
                $stats = new CharacterStats(
                    speed: rand(0, 1500),
                    stamina: rand(0, 1500),
                    power: rand(0, 1500),
                    guts: rand(0, 1500),
                    wisdom: rand(0, 1500),
                );
                $stats->getTotal();
                $stats->getEffectiveTotal();
                $stats->toArray();
            }
            $timings[$size] = (hrtime(true) - $start) / 1_000_000;
        }

        $timePerOp10 = $timings[10] / 10;
        $timePerOp200 = $timings[200] / 200;

        expect($timePerOp200)->toBeLessThan($timePerOp10 * 5, 'Per-operation time should not increase significantly with scale');
    })->group('performance', 'property');

    it('verifies database query performance does not degrade with data volume', function () {
        $this->actingAs($this->user, 'sanctum');

        Character::factory()->count(5)->create(['user_id' => $this->user->id]);
        RateLimiter::clear('api:'.$this->user->id);
        $smallTimes = [];
        for ($i = 0; $i < 10; $i++) {
            RateLimiter::clear('api:'.$this->user->id);
            $start = hrtime(true);
            $this->getJson('/api/characters')->assertSuccessful();
            $smallTimes[] = (hrtime(true) - $start) / 1_000_000;
        }

        Character::factory()->count(15)->create(['user_id' => $this->user->id]);
        RateLimiter::clear('api:'.$this->user->id);
        $largeTimes = [];
        for ($i = 0; $i < 10; $i++) {
            RateLimiter::clear('api:'.$this->user->id);
            $start = hrtime(true);
            $this->getJson('/api/characters')->assertSuccessful();
            $largeTimes[] = (hrtime(true) - $start) / 1_000_000;
        }

        $smallAvg = array_sum($smallTimes) / count($smallTimes);
        $largeAvg = array_sum($largeTimes) / count($largeTimes);

        $ratio = $largeAvg / max($smallAvg, 0.001);

        expect($ratio)->toBeLessThan(5.0, "4x data should not cause 5x slowdown (ratio: {$ratio})");
    })->group('performance', 'property');

    it('verifies page render time stays bounded with multiple characters', function () {
        $this->actingAs($this->user);

        Character::factory()->count(20)->create(['user_id' => $this->user->id]);

        $times = [];
        for ($i = 0; $i < 10; $i++) {
            $start = hrtime(true);
            $this->get('/characters')->assertSuccessful();
            $times[] = (hrtime(true) - $start) / 1_000_000;
        }

        sort($times);
        $p95 = $times[(int) ceil(0.95 * count($times)) - 1];

        expect($p95)->toBeLessThan(3000, "Characters page p95 with 20 chars: {$p95}ms");
    })->group('performance', 'property');

    it('verifies concurrent-style sequential API calls maintain performance', function () {
        $this->actingAs($this->user, 'sanctum');
        Character::factory()->count(5)->create(['user_id' => $this->user->id]);

        $endpoints = ['/api/me', '/api/characters'];

        $times = [];
        for ($i = 0; $i < 20; $i++) {
            RateLimiter::clear('api:'.$this->user->id);
            $endpoint = $endpoints[$i % count($endpoints)];
            $start = hrtime(true);
            $this->getJson($endpoint)->assertSuccessful();
            $times[] = (hrtime(true) - $start) / 1_000_000;
        }

        sort($times);
        $p95 = $times[(int) ceil(0.95 * count($times)) - 1];

        expect($p95)->toBeLessThan(2000, "Mixed API p95: {$p95}ms");

        $avg = array_sum($times) / count($times);
        expect($avg)->toBeLessThan(1000, "Mixed API avg: {$avg}ms");
    })->group('performance', 'property');
});
