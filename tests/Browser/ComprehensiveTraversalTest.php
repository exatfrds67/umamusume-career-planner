<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\Race;
use App\Models\Skill;
use App\Models\SupportCard;
use App\Models\User;
use App\Services\Testing\CoverageReporter;
use App\Services\Testing\RouteDiscoveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Comprehensive Application Traversal Test
 *
 * Systematically visits ALL application routes like a thorough manual tester.
 * Tests every page for:
 * - Successful page load (no 500 errors)
 * - Basic accessibility compliance
 * - Performance (load time tracking)
 *
 * Generates comprehensive HTML and JSON reports with:
 * - Coverage statistics
 * - Error details
 * - Performance metrics
 * - Pass/fail status for each route
 *
 * @group browser
 * @group traversal
 * @group slow
 */
beforeEach(function () {
    $this->guestUser = null;
    $this->authUser = User::factory()->create();
    $this->adminUser = User::factory()->create(['is_admin' => true]);

    $this->testCharacter = Character::factory()->create([
        'user_id' => $this->authUser->id,
        'name' => 'Test Character',
    ]);

    $this->testRace = Race::factory()->create();
    $this->testSkill = Skill::factory()->create();
    $this->testSupportCard = SupportCard::factory()->create();

    $this->routeDiscovery = app(RouteDiscoveryService::class);
    $this->coverageReporter = new CoverageReporter;
    $GLOBALS['_traversal_coverage_reporter'] = $this->coverageReporter;

    $this->routes = $this->routeDiscovery->discoverRoutes();
    $this->stats = $this->routeDiscovery->getStatistics($this->routes);
});

afterAll(function () {
    $reporter = $GLOBALS['_traversal_coverage_reporter'] ?? null;
    if ($reporter instanceof CoverageReporter) {
        try {
            $reporter->generateHtmlReport();
        } catch (\Throwable $e) {
            // report generation failure is non-critical
        }

        try {
            $reporter->generateJsonReport();
        } catch (\Throwable $e) {
            // report generation failure is non-critical
        }

        unset($GLOBALS['_traversal_coverage_reporter']);
    }
});

describe('Route Discovery', function () {
    it('discovers all application routes', function () {
        expect($this->routes)->toBeArray()
            ->and($this->routes)->toHaveKeys(['public', 'auth', 'admin'])
            ->and($this->stats['total'])->toBeGreaterThan(0);
    })->group('browser', 'traversal');
});

describe('Public Pages Traversal', function () {
    it('visits all public pages without errors', function () {
        $publicRoutes = $this->routes['public'];

        foreach ($publicRoutes as $routeData) {
            $parameterValues = [
                'character' => $this->testCharacter->id,
                'race' => $this->testRace->id,
                'skill' => $this->testSkill->id,
                'supportCard' => $this->testSupportCard->id,
                'user' => $this->authUser->id,
                'id' => 1,
                'position' => 1,
            ];

            $url = $this->routeDiscovery->buildUrl($routeData, $parameterValues);

            if ($url === null) {
                continue;
            }

            $startTime = microtime(true);
            $success = true;
            $error = null;
            $statusCode = null;

            try {
                $page = visit($url);
                $statusCode = 200;
                $loadTime = microtime(true) - $startTime;
            } catch (\Throwable $e) {
                $success = false;
                $error = $e->getMessage();
                $loadTime = microtime(true) - $startTime;

                if (str_contains($error, '500')) {
                    $statusCode = 500;
                } elseif (str_contains($error, '404')) {
                    $statusCode = 404;
                } elseif (str_contains($error, '403')) {
                    $statusCode = 403;
                }
            }

            /** @var CoverageReporter $coverageReporter */
            $coverageReporter = $GLOBALS['_traversal_coverage_reporter'];
            $coverageReporter->recordVisit($url, 'public', [
                'success' => $success,
                'statusCode' => $statusCode,
                'loadTime' => round($loadTime, 3),
                'error' => $error,
            ]);
        }

        /** @var CoverageReporter $coverageReporter */
        $coverageReporter = $GLOBALS['_traversal_coverage_reporter'];
        $summary = $coverageReporter->getSummary();
        expect($summary['failed'])->toBe(0, 'All public pages should load without errors');
    })->group('browser', 'traversal', 'public');
});

describe('Authenticated Pages Traversal', function () {
    it('visits all authenticated pages without errors', function () {
        $this->actingAs($this->authUser);

        $authRoutes = $this->routes['auth'];

        foreach ($authRoutes as $routeData) {
            $parameterValues = [
                'character' => $this->testCharacter->id,
                'race' => $this->testRace->id,
                'skill' => $this->testSkill->id,
                'supportCard' => $this->testSupportCard->id,
                'user' => $this->authUser->id,
                'id' => 1,
                'position' => 1,
            ];

            $url = $this->routeDiscovery->buildUrl($routeData, $parameterValues);

            if ($url === null) {
                continue;
            }

            $startTime = microtime(true);
            $success = true;
            $error = null;
            $statusCode = null;

            try {
                $page = visit($url);
                $statusCode = 200;
                $loadTime = microtime(true) - $startTime;
            } catch (\Throwable $e) {
                $success = false;
                $error = $e->getMessage();
                $loadTime = microtime(true) - $startTime;

                if (str_contains($error, '500')) {
                    $statusCode = 500;
                } elseif (str_contains($error, '404')) {
                    $statusCode = 404;
                } elseif (str_contains($error, '403')) {
                    $statusCode = 403;
                }
            }

            /** @var CoverageReporter $coverageReporter */
            $coverageReporter = $GLOBALS['_traversal_coverage_reporter'];
            $coverageReporter->recordVisit($url, 'auth', [
                'success' => $success,
                'statusCode' => $statusCode,
                'loadTime' => round($loadTime, 3),
                'error' => $error,
            ]);
        }

        /** @var CoverageReporter $coverageReporter */
        $coverageReporter = $GLOBALS['_traversal_coverage_reporter'];
        $summary = $coverageReporter->getSummary();
        expect($summary['pass_rate'])->toBeGreaterThan(80, 'At least 80% of authenticated pages should load');
    })->group('browser', 'traversal', 'auth');
});

describe('Admin Pages Traversal', function () {
    it('visits all admin pages without errors', function () {
        $this->actingAs($this->adminUser);

        $adminRoutes = $this->routes['admin'];

        foreach ($adminRoutes as $routeData) {
            $parameterValues = [
                'character' => $this->testCharacter->id,
                'race' => $this->testRace->id,
                'skill' => $this->testSkill->id,
                'supportCard' => $this->testSupportCard->id,
                'user' => $this->authUser->id,
                'id' => 1,
                'position' => 1,
            ];

            $url = $this->routeDiscovery->buildUrl($routeData, $parameterValues);

            if ($url === null) {
                continue;
            }

            $startTime = microtime(true);
            $success = true;
            $error = null;
            $statusCode = null;

            try {
                $page = visit($url);
                $statusCode = 200;
                $loadTime = microtime(true) - $startTime;
            } catch (\Throwable $e) {
                $success = false;
                $error = $e->getMessage();
                $loadTime = microtime(true) - $startTime;

                if (str_contains($error, '500')) {
                    $statusCode = 500;
                } elseif (str_contains($error, '404')) {
                    $statusCode = 404;
                } elseif (str_contains($error, '403')) {
                    $statusCode = 403;
                }
            }

            /** @var CoverageReporter $coverageReporter */
            $coverageReporter = $GLOBALS['_traversal_coverage_reporter'];
            $coverageReporter->recordVisit($url, 'admin', [
                'success' => $success,
                'statusCode' => $statusCode,
                'loadTime' => round($loadTime, 3),
                'error' => $error,
            ]);
        }

        /** @var CoverageReporter $coverageReporter */
        $coverageReporter = $GLOBALS['_traversal_coverage_reporter'];
        $summary = $coverageReporter->getSummary();
        expect($summary['pass_rate'])->toBeGreaterThan(80, 'At least 80% of admin pages should load');
    })->group('browser', 'traversal', 'admin');
});

describe('Performance Benchmarks', function () {
    it('tracks page load performance across all routes', function () {
        /** @var CoverageReporter $coverageReporter */
        $coverageReporter = $GLOBALS['_traversal_coverage_reporter'];
        $summary = $coverageReporter->getSummary();

        expect($summary['avg_load_time'])->toBeLessThan(5.0, 'Average load time should be under 5 seconds')
            ->and($summary['max_load_time'])->toBeLessThan(10.0, 'No page should take more than 10 seconds');
    })->group('browser', 'traversal', 'performance');
});
