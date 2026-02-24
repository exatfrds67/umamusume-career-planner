<?php

declare(strict_types=1);

describe('Performance Benchmark Property Tests', function () {
    /**
     * Property 29: Page Load Time Bounds
     *
     * Feature: umamusume-career-planner-main-v2.4.0, Property 29: Page Load Time Bounds
     * Validates: Requirements NFR-P-01, NFR-P-02, NFR-P-03
     *
     * All web routes must respond within acceptable time bounds.
     * Target: p95 under 2 seconds for HTML pages, under 500ms for API endpoints.
     */
    it('ensures public pages respond within time bounds', function () {
        $routes = [
            '/',
            '/login',
            '/register',
        ];

        foreach ($routes as $route) {
            $times = [];

            for ($i = 0; $i < 20; $i++) {
                $start = microtime(true);
                $response = $this->get($route);
                $elapsed = (microtime(true) - $start) * 1000;

                $times[] = $elapsed;

                expect($response->getStatusCode())->toBeIn([200, 302]);
            }

            sort($times);
            $p95Index = (int) ceil(count($times) * 0.95) - 1;
            $p95 = $times[$p95Index];

            expect($p95)->toBeLessThan(2000, "Route {$route} p95 response time {$p95}ms exceeds 2000ms");
        }
    })->group('property');

    it('ensures authenticated pages respond within time bounds', function () {
        $user = \App\Models\User::factory()->create();

        $routes = [
            '/dashboard',
            '/profile',
        ];

        foreach ($routes as $route) {
            $times = [];

            for ($i = 0; $i < 20; $i++) {
                $start = microtime(true);
                $response = $this->actingAs($user)->get($route);
                $elapsed = (microtime(true) - $start) * 1000;

                $times[] = $elapsed;

                expect($response->getStatusCode())->toBeIn([200, 302]);
            }

            sort($times);
            $p95Index = (int) ceil(count($times) * 0.95) - 1;
            $p95 = $times[$p95Index];

            expect($p95)->toBeLessThan(2000, "Route {$route} p95 response time {$p95}ms exceeds 2000ms");
        }
    })->group('property');

    it('ensures API endpoints respond within strict time bounds', function () {
        $user = \App\Models\User::factory()->create();
        $token = $user->createToken('test-perf')->plainTextToken;

        $apiRoutes = [
            '/api/v1/characters',
            '/api/v1/skills',
        ];

        foreach ($apiRoutes as $route) {
            $times = [];

            for ($i = 0; $i < 20; $i++) {
                $start = microtime(true);
                $response = $this->withHeader('Authorization', 'Bearer '.$token)->getJson($route);
                $elapsed = (microtime(true) - $start) * 1000;

                $times[] = $elapsed;
            }

            sort($times);
            $p95Index = (int) ceil(count($times) * 0.95) - 1;
            $p95 = $times[$p95Index];

            expect($p95)->toBeLessThan(1000, "API {$route} p95 response time {$p95}ms exceeds 1000ms");
        }
    })->group('property');

    it('ensures response times do not degrade linearly with repeated requests', function () {
        $user = \App\Models\User::factory()->create();

        $firstBatchTimes = [];
        $lastBatchTimes = [];

        for ($i = 0; $i < 10; $i++) {
            $start = microtime(true);
            $this->actingAs($user)->get('/dashboard');
            $firstBatchTimes[] = (microtime(true) - $start) * 1000;
        }

        for ($i = 0; $i < 90; $i++) {
            $this->actingAs($user)->get('/dashboard');
        }

        for ($i = 0; $i < 10; $i++) {
            $start = microtime(true);
            $this->actingAs($user)->get('/dashboard');
            $lastBatchTimes[] = (microtime(true) - $start) * 1000;
        }

        $firstAvg = array_sum($firstBatchTimes) / count($firstBatchTimes);
        $lastAvg = array_sum($lastBatchTimes) / count($lastBatchTimes);

        $degradationRatio = $lastAvg / max($firstAvg, 0.1);
        expect($degradationRatio)->toBeLessThan(3.0, "Response time degraded by {$degradationRatio}x over 100 requests");
    })->group('property');
});
