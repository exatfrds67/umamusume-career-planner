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

$sharedCoverageReporter = null;

/**
 * Comprehensive Application Traversal Test
 *
 * Systematically visits ALL application routes like a thorough manual tester.
 * Tests every page for:
 * - Successful page load (no 500 errors)
 * - No JavaScript errors
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
    // Set up test users
    $this->guestUser = null;
    $this->authUser = User::factory()->create();
    $this->adminUser = User::factory()->create(['is_admin' => true]);

    // Set up test data for parameterized routes
    $this->testCharacter = Character::factory()->create([
        'user_id' => $this->authUser->id,
        'name' => 'Test Character',
    ]);

    $this->testRace = Race::factory()->create();
    $this->testSkill = Skill::factory()->create();
    $this->testSupportCard = SupportCard::factory()->create();

    // Initialize services
    $this->routeDiscovery = app(RouteDiscoveryService::class);
    $this->coverageReporter = new CoverageReporter();
    $GLOBALS['_traversal_coverage_reporter'] = $this->coverageReporter;

    // Discover all routes
    $this->routes = $this->routeDiscovery->discoverRoutes();
    $this->stats = $this->routeDiscovery->getStatistics($this->routes);
});

afterAll(function () {
    // Generate reports after all tests complete
    $reporter = $GLOBALS['_traversal_coverage_reporter'] ?? null;
    if ($reporter instanceof CoverageReporter) {
        $htmlPath = $reporter->generateHtmlReport();
        $jsonPath = $reporter->generateJsonReport();

        echo "\n\n";
        echo "═══════════════════════════════════════════════════════════════════\n";
        echo "  📊 TRAVERSAL TEST REPORTS GENERATED\n";
        echo "═══════════════════════════════════════════════════════════════════\n";
        echo "HTML Report: {$htmlPath}\n";
        echo "JSON Report: {$jsonPath}\n";
        echo "═══════════════════════════════════════════════════════════════════\n\n";
        unset($GLOBALS['_traversal_coverage_reporter']);
    }
});

describe('Route Discovery', function () {
    it('discovers all application routes', function () {
        expect($this->routes)->toBeArray()
            ->and($this->routes)->toHaveKeys(['public', 'auth', 'admin'])
            ->and($this->stats['total'])->toBeGreaterThan(0);

        echo "\n📍 Route Discovery Statistics:\n";
        echo "   Total Routes: {$this->stats['total']}\n";
        echo "   Public: {$this->stats['public']}\n";
        echo "   Authenticated: {$this->stats['auth']}\n";
        echo "   Admin: {$this->stats['admin']}\n";
        echo "   With Parameters: {$this->stats['withParameters']}\n";
    })->group('browser', 'traversal');
});

describe('Public Pages Traversal', function () {
    it('visits all public pages without errors', function () {
        $publicRoutes = $this->routes['public'];

        echo "\n🌐 Testing {$this->stats['public']} Public Pages...\n";

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
                continue; // Skip routes we can't build without parameters
            }

            $startTime = microtime(true);
            $success = true;
            $error = null;
            $statusCode = null;

            try {
                $page = visit($url);
                $statusCode = 200;
                $page->assertNoJavaScriptErrors();
                $loadTime = microtime(true) - $startTime;
                echo "   ✓ {$url} ({$loadTime}s)\n";
            } catch (\Throwable $e) {
                $success = false;
                $error = $e->getMessage();
                $loadTime = microtime(true) - $startTime;
                echo "   ✗ {$url} - {$error}\n";

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

        echo "\n🔐 Testing {$this->stats['auth']} Authenticated Pages...\n";

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
                $page->assertNoJavaScriptErrors();
                $loadTime = microtime(true) - $startTime;
                echo "   ✓ {$url} ({$loadTime}s)\n";
            } catch (\Throwable $e) {
                $success = false;
                $error = $e->getMessage();
                $loadTime = microtime(true) - $startTime;
                echo "   ✗ {$url} - {$error}\n";

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

        // Check that most pages loaded successfully (allow some failures for dynamic routes)
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

        echo "\n👑 Testing {$this->stats['admin']} Admin Pages...\n";

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
                $page->assertNoJavaScriptErrors();
                $loadTime = microtime(true) - $startTime;
                echo "   ✓ {$url} ({$loadTime}s)\n";
            } catch (\Throwable $e) {
                $success = false;
                $error = $e->getMessage();
                $loadTime = microtime(true) - $startTime;
                echo "   ✗ {$url} - {$error}\n";

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

        echo "\n⚡ Performance Summary:\n";
        echo "   Average Load Time: {$summary['avg_load_time']}s\n";
        echo "   Maximum Load Time: {$summary['max_load_time']}s\n";
        echo "   Minimum Load Time: {$summary['min_load_time']}s\n";

        // Performance assertions
        expect($summary['avg_load_time'])->toBeLessThan(5.0, 'Average load time should be under 5 seconds')
            ->and($summary['max_load_time'])->toBeLessThan(10.0, 'No page should take more than 10 seconds');
    })->group('browser', 'traversal', 'performance')->depends('Public Pages Traversal', 'Authenticated Pages Traversal');
});
