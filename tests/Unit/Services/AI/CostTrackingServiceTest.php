<?php

declare(strict_types=1);

use App\Services\AI\CostTrackingService;
use App\Services\MCP\Tools\AWSPricingService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(DatabaseMigrations::class);

beforeEach(function () {
    /** @var AWSPricingService&Mockery\MockInterface $awsPricing */
    $awsPricing = Mockery::mock(AWSPricingService::class);
    $this->awsPricing = $awsPricing;
    $this->costService = new CostTrackingService($awsPricing);

    // Create the ai_costs table if it doesn't exist
    if (! Schema::hasTable('ucp_ai_costs')) {
        Schema::create('ucp_ai_costs', function ($table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('character_id')->nullable();
            $table->string('provider');
            $table->string('model');
            $table->string('request_type')->nullable();
            $table->integer('input_tokens');
            $table->integer('output_tokens');
            $table->integer('total_tokens');
            $table->decimal('input_cost', 10, 6);
            $table->decimal('output_cost', 10, 6);
            $table->decimal('total_cost', 10, 6);
            $table->decimal('response_time', 8, 3)->nullable();
            $table->boolean('cached')->default(false);
            $table->text('request_summary')->nullable();
            $table->timestamps();
        });
    }
});

afterEach(fn () => Mockery::close());

describe('CostTrackingService', function () {
    describe('trackCost', function () {
        it('tracks AI request cost in database', function () {
            $this->awsPricing->shouldReceive('getBedrockPricing')
                ->andReturn([
                    'models' => [
                        'claude-3-5-sonnet' => [
                            'input_price' => 3.0,
                            'output_price' => 15.0,
                            'unit' => 'per 1M tokens',
                        ],
                    ],
                ]);

            $this->costService->trackCost(
                provider: 'bedrock',
                model: 'claude-3-5-sonnet',
                inputTokens: 1000,
                outputTokens: 500,
                cost: 0.0105,
                userId: null,
                characterId: null,
                requestType: 'training_advice'
            );

            $this->assertDatabaseHas('ucp_ai_costs', [
                'provider' => 'bedrock',
                'model' => 'claude-3-5-sonnet',
                'input_tokens' => 1000,
                'output_tokens' => 500,
            ]);
        });
    });

    describe('getTotalCost', function () {
        it('returns total cost for period', function () {
            // Insert test data
            DB::table('ucp_ai_costs')->insert([
                [
                    'provider' => 'bedrock',
                    'model' => 'claude-3-5-sonnet',
                    'input_tokens' => 1000,
                    'output_tokens' => 500,
                    'total_tokens' => 1500,
                    'input_cost' => 0.003,
                    'output_cost' => 0.0075,
                    'total_cost' => 0.0105,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'provider' => 'bedrock',
                    'model' => 'claude-3-5-haiku',
                    'input_tokens' => 500,
                    'output_tokens' => 250,
                    'total_tokens' => 750,
                    'input_cost' => 0.0005,
                    'output_cost' => 0.00125,
                    'total_cost' => 0.00175,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            $total = $this->costService->getTotalCost('30d');

            expect($total)->toBe(0.01225);
        });

        it('filters by user when provided', function () {
            // Create users first
            $user1 = \App\Models\User::factory()->create();
            $user2 = \App\Models\User::factory()->create();

            DB::table('ucp_ai_costs')->insert([
                [
                    'user_id' => $user1->id,
                    'provider' => 'bedrock',
                    'model' => 'claude-3-5-sonnet',
                    'input_tokens' => 1000,
                    'output_tokens' => 500,
                    'total_tokens' => 1500,
                    'input_cost' => 0.003,
                    'output_cost' => 0.0075,
                    'total_cost' => 0.01,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_id' => $user2->id,
                    'provider' => 'bedrock',
                    'model' => 'claude-3-5-sonnet',
                    'input_tokens' => 1000,
                    'output_tokens' => 500,
                    'total_tokens' => 1500,
                    'input_cost' => 0.003,
                    'output_cost' => 0.0075,
                    'total_cost' => 0.02,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            $totalUser1 = $this->costService->getTotalCost('30d', $user1->id);
            $totalUser2 = $this->costService->getTotalCost('30d', $user2->id);

            expect($totalUser1)->toBe(0.01);
            expect($totalUser2)->toBe(0.02);
        });
    });

    describe('getCostByProvider', function () {
        it('returns cost breakdown by provider', function () {
            DB::table('ucp_ai_costs')->insert([
                [
                    'provider' => 'bedrock',
                    'model' => 'claude-3-5-sonnet',
                    'input_tokens' => 1000,
                    'output_tokens' => 500,
                    'total_tokens' => 1500,
                    'input_cost' => 0.003,
                    'output_cost' => 0.0075,
                    'total_cost' => 0.01,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'provider' => 'ollama',
                    'model' => 'llama3.3',
                    'input_tokens' => 1000,
                    'output_tokens' => 500,
                    'total_tokens' => 1500,
                    'input_cost' => 0,
                    'output_cost' => 0,
                    'total_cost' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            $breakdown = $this->costService->getCostByProvider('30d');

            expect($breakdown)->toHaveKey('bedrock');
            expect($breakdown)->toHaveKey('ollama');
            expect($breakdown['bedrock'])->toBe(0.01);
            expect($breakdown['ollama'])->toBe(0.0);
        });
    });

    describe('getCostByModel', function () {
        it('returns cost breakdown by model', function () {
            DB::table('ucp_ai_costs')->insert([
                [
                    'provider' => 'bedrock',
                    'model' => 'claude-3-5-sonnet',
                    'input_tokens' => 1000,
                    'output_tokens' => 500,
                    'total_tokens' => 1500,
                    'input_cost' => 0.003,
                    'output_cost' => 0.0075,
                    'total_cost' => 0.015,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'provider' => 'bedrock',
                    'model' => 'claude-3-5-haiku',
                    'input_tokens' => 2000,
                    'output_tokens' => 1000,
                    'total_tokens' => 3000,
                    'input_cost' => 0.002,
                    'output_cost' => 0.005,
                    'total_cost' => 0.007,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            $breakdown = $this->costService->getCostByModel('30d');

            expect($breakdown)->toHaveKey('claude-3-5-sonnet');
            expect($breakdown)->toHaveKey('claude-3-5-haiku');
        });
    });

    describe('getBudgetStatus', function () {
        it('returns healthy status when under budget', function () {
            DB::table('ucp_ai_costs')->insert([
                'provider' => 'bedrock',
                'model' => 'claude-3-5-sonnet',
                'input_tokens' => 1000,
                'output_tokens' => 500,
                'total_tokens' => 1500,
                'input_cost' => 0.003,
                'output_cost' => 0.0075,
                'total_cost' => 5.0, // $5 spent
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $status = $this->costService->getBudgetStatus(null, 100.0); // $100 budget

            expect($status['status'])->toBe('healthy');
            expect($status['alert_level'])->toBe('success');
            expect($status['budget_utilization'])->toBeLessThan(75);
        });

        it('returns warning status when approaching budget', function () {
            DB::table('ucp_ai_costs')->insert([
                'provider' => 'bedrock',
                'model' => 'claude-3-5-sonnet',
                'input_tokens' => 1000,
                'output_tokens' => 500,
                'total_tokens' => 1500,
                'input_cost' => 0.003,
                'output_cost' => 0.0075,
                'total_cost' => 80.0, // $80 spent
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $status = $this->costService->getBudgetStatus(null, 100.0); // $100 budget

            expect($status['status'])->toBe('warning');
            expect($status['alert_level'])->toBe('warning');
        });

        it('returns critical status when near budget limit', function () {
            DB::table('ucp_ai_costs')->insert([
                'provider' => 'bedrock',
                'model' => 'claude-3-5-sonnet',
                'input_tokens' => 1000,
                'output_tokens' => 500,
                'total_tokens' => 1500,
                'input_cost' => 0.003,
                'output_cost' => 0.0075,
                'total_cost' => 95.0, // $95 spent
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $status = $this->costService->getBudgetStatus(null, 100.0); // $100 budget

            expect($status['status'])->toBe('critical');
            expect($status['alert_level'])->toBe('danger');
        });

        it('returns exceeded status when over budget', function () {
            DB::table('ucp_ai_costs')->insert([
                'provider' => 'bedrock',
                'model' => 'claude-3-5-sonnet',
                'input_tokens' => 1000,
                'output_tokens' => 500,
                'total_tokens' => 1500,
                'input_cost' => 0.003,
                'output_cost' => 0.0075,
                'total_cost' => 110.0, // $110 spent
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $status = $this->costService->getBudgetStatus(null, 100.0); // $100 budget

            expect($status['status'])->toBe('exceeded');
            expect($status['remaining_budget'])->toBeLessThan(0);
        });
    });

    describe('getCostOptimizationRecommendations', function () {
        it('returns optimization recommendations', function () {
            DB::table('ucp_ai_costs')->insert([
                'provider' => 'bedrock',
                'model' => 'claude-3-5-sonnet',
                'input_tokens' => 5000,
                'output_tokens' => 2500,
                'total_tokens' => 7500,
                'input_cost' => 0.015,
                'output_cost' => 0.0375,
                'total_cost' => 0.0525,
                'cached' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $recommendations = $this->costService->getCostOptimizationRecommendations();

            expect($recommendations)->toHaveKeys([
                'current_cost',
                'optimized_cost',
                'potential_savings',
                'recommendations',
            ]);
            expect($recommendations['recommendations'])->toBeArray();
        });

        it('suggests local processing for Bedrock costs', function () {
            DB::table('ucp_ai_costs')->insert([
                'provider' => 'bedrock',
                'model' => 'claude-3-5-sonnet',
                'input_tokens' => 10000,
                'output_tokens' => 5000,
                'total_tokens' => 15000,
                'input_cost' => 0.03,
                'output_cost' => 0.075,
                'total_cost' => 0.105,
                'cached' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $recommendations = $this->costService->getCostOptimizationRecommendations();

            $hasLocalProcessingRec = collect($recommendations['recommendations'])
                ->contains(fn ($rec) => $rec['type'] === 'local_processing');

            expect($hasLocalProcessingRec)->toBeTrue();
        });
    });

    describe('getDailyCostTrend', function () {
        it('returns daily cost trend data', function () {
            // Insert data for multiple days
            for ($i = 0; $i < 5; $i++) {
                DB::table('ucp_ai_costs')->insert([
                    'provider' => 'bedrock',
                    'model' => 'claude-3-5-sonnet',
                    'input_tokens' => 1000,
                    'output_tokens' => 500,
                    'total_tokens' => 1500,
                    'input_cost' => 0.003,
                    'output_cost' => 0.0075,
                    'total_cost' => 0.01 * ($i + 1),
                    'created_at' => now()->subDays($i),
                    'updated_at' => now()->subDays($i),
                ]);
            }

            $trend = $this->costService->getDailyCostTrend(7);

            expect($trend)->toBeArray();
            expect(count($trend))->toBeGreaterThan(0);

            foreach ($trend as $day) {
                expect($day)->toHaveKeys(['date', 'cost']);
            }
        });
    });
});
