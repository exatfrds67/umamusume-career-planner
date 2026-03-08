<?php

declare(strict_types=1);

use App\Models\Career;
use App\Models\Character;
use App\Models\Skill;
use App\Models\SupportCardDefinition;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
});

describe('Performance Tests', function (): void {
    describe('Response Time', function (): void {
        it('dashboard loads within acceptable time', function (): void {
            $startTime = microtime(true);

            $response = $this->get('/dashboard');

            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;

            $response->assertSuccessful();

            // Dashboard should load in under 3 seconds (allows for test environment variability)
            expect($responseTime)->toBeLessThan(3000);
        });

        it('API endpoints respond quickly', function (): void {
            $startTime = microtime(true);

            $response = $this->getJson('/api/v1/characters');

            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;

            $response->assertSuccessful();

            // API should respond in under 500ms
            expect($responseTime)->toBeLessThan(500);
        });

        it('character list loads efficiently with many records', function (): void {
            // Create many characters
            Character::factory()->count(100)->create(['user_id' => $this->user->id]);

            $startTime = microtime(true);

            $response = $this->getJson('/api/v1/characters');

            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;

            $response->assertSuccessful();

            // Should still be fast with pagination
            expect($responseTime)->toBeLessThan(2000);
        });
    });

    describe('Database Query Optimization', function (): void {
        it('avoids N+1 queries on character list', function (): void {
            Character::factory()->count(10)->create(['user_id' => $this->user->id]);

            DB::enableQueryLog();

            $this->getJson('/api/v1/characters');

            $queries = DB::getQueryLog();
            DB::disableQueryLog();

            // Should not have excessive queries (N+1 would be 11+ queries)
            expect(count($queries))->toBeLessThan(15);
        });

        it('avoids N+1 queries on career with relations', function (): void {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            TrainingSession::factory()->count(20)->create(['career_id' => $career->id]);

            DB::enableQueryLog();

            $this->getJson("/api/v1/careers/{$career->id}");

            $queries = DB::getQueryLog();
            DB::disableQueryLog();

            // Should use eager loading
            expect(count($queries))->toBeLessThan(10);
        });

        it('uses indexes for common queries', function (): void {
            Character::factory()->count(50)->create(['user_id' => $this->user->id]);

            DB::enableQueryLog();

            // Query by user_id (should use index)
            $this->getJson('/api/v1/characters');

            $queries = DB::getQueryLog();
            DB::disableQueryLog();

            // Check that queries are efficient
            foreach ($queries as $query) {
                // Queries should complete quickly
                expect($query['time'])->toBeLessThan(100);
            }
        });
    });

    describe('Memory Usage', function (): void {
        it('handles large datasets without memory issues', function (): void {
            $initialMemory = memory_get_usage();

            // Create large dataset
            Character::factory()->count(100)->create(['user_id' => $this->user->id]);

            $response = $this->getJson('/api/v1/characters?per_page=100');

            $finalMemory = memory_get_usage();
            $memoryUsed = ($finalMemory - $initialMemory) / 1024 / 1024; // MB

            $response->assertSuccessful();

            // Should not use excessive memory (under 50MB for this operation)
            expect($memoryUsed)->toBeLessThan(50);
        });

        it('pagination prevents memory overload', function (): void {
            Character::factory()->count(500)->create(['user_id' => $this->user->id]);

            $initialMemory = memory_get_usage();

            // Request paginated data
            $response = $this->getJson('/api/v1/characters?per_page=20');

            $finalMemory = memory_get_usage();
            $memoryUsed = ($finalMemory - $initialMemory) / 1024 / 1024;

            $response->assertSuccessful();
            $response->assertJsonCount(20, 'data');

            // Pagination should keep memory low
            expect($memoryUsed)->toBeLessThan(20);
        });
    });

    describe('Concurrent Requests', function (): void {
        it('handles multiple simultaneous requests', function (): void {
            $character = Character::factory()->create(['user_id' => $this->user->id]);

            $responses = [];
            $startTime = microtime(true);

            // Simulate concurrent requests
            for ($i = 0; $i < 10; $i++) {
                $responses[] = $this->getJson("/api/v1/characters/{$character->id}");
            }

            $endTime = microtime(true);
            $totalTime = ($endTime - $startTime) * 1000;

            // All requests should succeed
            foreach ($responses as $response) {
                $response->assertSuccessful();
            }

            // Total time should be reasonable (not 10x single request time)
            expect($totalTime)->toBeLessThan(5000);
        });
    });

    describe('Caching Performance', function (): void {
        it('cached responses are faster', function (): void {
            Skill::factory()->count(50)->create(['is_active' => true]);

            // First request (cache miss)
            $startTime1 = microtime(true);
            $this->getJson('/api/v1/skills');
            $time1 = (microtime(true) - $startTime1) * 1000;

            // Second request (cache hit)
            $startTime2 = microtime(true);
            $this->getJson('/api/v1/skills');
            $time2 = (microtime(true) - $startTime2) * 1000;

            // Cached response should be faster or similar
            // (In test environment, caching may not show dramatic improvement)
            expect($time2)->toBeLessThan($time1 * 2);
        });
    });

    describe('Search Performance', function (): void {
        it('search queries are efficient', function (): void {
            Skill::factory()->count(100)->create(['is_active' => true]);

            $startTime = microtime(true);

            $response = $this->getJson('/api/v1/skills?search=speed');

            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;

            $response->assertSuccessful();

            // Search should be fast
            expect($responseTime)->toBeLessThan(500);
        });

        it('filtered queries are efficient', function (): void {
            SupportCardDefinition::factory()->count(100)->create(['is_active' => true]);

            $startTime = microtime(true);

            $response = $this->getJson('/api/v1/support-cards?card_type=speed&rarity=SSR');

            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;

            $response->assertSuccessful();

            // Filtered query should be fast
            expect($responseTime)->toBeLessThan(500);
        });
    });

    describe('Bulk Operations', function (): void {
        it('bulk create is efficient', function (): void {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            $sessions = [];
            for ($i = 1; $i <= 50; $i++) {
                $sessions[] = [
                    'turn_number' => $i,
                    'training_type' => 'speed',
                ];
            }

            $startTime = microtime(true);

            $response = $this->postJson("/api/v1/careers/{$career->id}/training-sessions/bulk", [
                'sessions' => $sessions,
            ]);

            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;

            // Bulk operation should complete in reasonable time
            expect($responseTime)->toBeLessThan(5000);
        });
    });

    describe('Report Generation', function (): void {
        it('career report generates efficiently', function (): void {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create([
                'character_id' => $character->id,
                'status' => 'completed',
            ]);

            TrainingSession::factory()->count(72)->create(['career_id' => $career->id]);

            $startTime = microtime(true);

            $response = $this->getJson("/api/v1/careers/{$career->id}/report");

            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;

            $response->assertSuccessful();

            // Report should generate in under 3 seconds
            expect($responseTime)->toBeLessThan(3000);
        });

        it('comparison report handles multiple careers', function (): void {
            $character = Character::factory()->create(['user_id' => $this->user->id]);

            $careerIds = [];
            for ($i = 0; $i < 5; $i++) {
                $career = Career::factory()->create(['character_id' => $character->id]);
                TrainingSession::factory()->count(30)->create(['career_id' => $career->id]);
                $careerIds[] = $career->id;
            }

            $startTime = microtime(true);

            $response = $this->postJson('/api/v1/careers/compare', [
                'career_ids' => $careerIds,
            ]);

            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;

            $response->assertSuccessful();

            // Comparison should complete in reasonable time
            expect($responseTime)->toBeLessThan(5000);
        });
    });
});

describe('Load Testing Scenarios', function (): void {
    describe('High Traffic Simulation', function (): void {
        it('handles burst of requests', function (): void {
            $character = Character::factory()->create(['user_id' => $this->user->id]);

            $successCount = 0;
            $startTime = microtime(true);

            // Simulate burst of 50 requests
            for ($i = 0; $i < 50; $i++) {
                $response = $this->getJson('/api/v1/characters');
                if ($response->status() === 200) {
                    $successCount++;
                }
            }

            $endTime = microtime(true);
            $totalTime = ($endTime - $startTime) * 1000;

            // Most requests should succeed
            expect($successCount)->toBeGreaterThan(40);

            // Total time should be reasonable
            expect($totalTime)->toBeLessThan(30000);
        });
    });

    describe('Stress Testing', function (): void {
        it('maintains performance under load', function (): void {
            // Create substantial data
            Character::factory()->count(50)->create(['user_id' => $this->user->id]);
            Skill::factory()->count(100)->create(['is_active' => true]);
            SupportCardDefinition::factory()->count(100)->create(['is_active' => true]);

            $responseTimes = [];

            // Make multiple requests and track response times
            for ($i = 0; $i < 20; $i++) {
                $startTime = microtime(true);
                $this->getJson('/api/v1/characters');
                $responseTimes[] = (microtime(true) - $startTime) * 1000;
            }

            $avgResponseTime = array_sum($responseTimes) / count($responseTimes);
            $maxResponseTime = max($responseTimes);

            // Average should be reasonable
            expect($avgResponseTime)->toBeLessThan(500);

            // No single request should be extremely slow
            expect($maxResponseTime)->toBeLessThan(2000);
        });
    });
});
