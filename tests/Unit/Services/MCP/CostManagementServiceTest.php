<?php

declare(strict_types=1);

/**
 * @property App\Services\MCP\MCPClientService&Mockery\MockInterface $mcpClient
 * @property App\Services\MCP\CostManagementService $service
 */

use App\Services\MCP\CostManagementService;
use App\Services\MCP\MCPClientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    /** @var MCPClientService&Mockery\MockInterface $mcpClient */
    $mcpClient = Mockery::mock(MCPClientService::class);
    $this->mcpClient = $mcpClient;
    $this->service = new CostManagementService($this->mcpClient);

    // Clear cache before each test
    Cache::flush();

    // Set default budget
    Config::set('ai.budget.monthly_limit', 100.0);
});

afterEach(function () {
    Mockery::close();
});

describe('Cost Calculation', function () {
    it('calculates Ollama cost as zero', function () {
        $cost = $this->service->calculateCost('ollama', 'llama3.3', 1000, 500);

        expect($cost)->toBe(0.0);
    });

    it('calculates Bedrock Nova Lite cost correctly', function () {
        $cost = $this->service->calculateCost('bedrock', 'amazon.nova-lite-v1:0', 1000, 1000);

        // (1000/1000 * 0.00125) + (1000/1000 * 0.00125) = 0.0025
        expect($cost)->toBe(0.0025);
    });

    it('calculates Bedrock Sonnet cost correctly', function () {
        $cost = $this->service->calculateCost('bedrock', 'anthropic.claude-3-5-sonnet-20241022-v2:0', 1000, 1000);

        // (1000/1000 * 0.003) + (1000/1000 * 0.015) = 0.018
        expect($cost)->toBe(0.018);
    });

    it('calculates Bedrock Opus cost correctly', function () {
        $cost = $this->service->calculateCost('bedrock', 'anthropic.claude-opus-4.5', 1000, 1000);

        // (1000/1000 * 0.005) + (1000/1000 * 0.025) = 0.03
        expect($cost)->toBe(0.03);
    });

    it('defaults to Sonnet pricing for unknown models', function () {
        $cost = $this->service->calculateCost('bedrock', 'unknown-model', 1000, 1000);

        expect($cost)->toBe(0.018); // Sonnet pricing
    });
});

describe('Cost Tracking', function () {
    it('tracks AI operation cost successfully', function () {
        $operation = [
            'provider' => 'bedrock',
            'model' => 'anthropic.claude-3-5-sonnet-20241022-v2:0',
            'input_tokens' => 1000,
            'output_tokens' => 500,
            'execution_time' => 2.5,
            'type' => 'chat',
            'user_id' => 1,
            'metadata' => ['test' => 'data'],
        ];

        $this->service->trackCost($operation);

        $this->assertDatabaseHas('ucp_ai_costs', [
            'provider' => 'bedrock',
            'model' => 'anthropic.claude-3-5-sonnet-20241022-v2:0',
            'input_tokens' => 1000,
            'output_tokens' => 500,
            'total_tokens' => 1500,
            'request_type' => 'chat',
            'user_id' => 1,
        ]);
    });

    it('updates cached totals when tracking cost', function () {
        $operation = [
            'provider' => 'bedrock',
            'model' => 'anthropic.claude-3-5-sonnet-20241022-v2:0',
            'input_tokens' => 1000,
            'output_tokens' => 500,
            'execution_time' => 2.5,
        ];

        $this->service->trackCost($operation);

        $cachedTotal = Cache::get('cost_total_bedrock');
        expect($cachedTotal)->toBeGreaterThan(0.0);
    });
});

describe('Budget Status', function () {
    it('returns correct budget status when under limit', function () {
        // Track some costs
        DB::table('ucp_ai_costs')->insert([
            'provider' => 'bedrock',
            'model' => 'test',
            'input_tokens' => 1000,
            'output_tokens' => 1000,
            'total_tokens' => 2000,
            'total_cost' => 10.0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Cache::put('cost_total_all', 10.0, 3600);

        $status = $this->service->checkBudgetStatus();

        expect($status['budget_limit'])->toBe(100.0)
            ->and($status['current_spending'])->toBe(10.0)
            ->and($status['remaining_budget'])->toBe(90.0)
            ->and($status['usage_percentage'])->toBe(10.0)
            ->and($status['is_exceeded'])->toBeFalse()
            ->and($status['alert_level'])->toBe('normal');
    });

    it('returns warning alert when approaching limit', function () {
        Cache::put('cost_total_all', 80.0, 3600);

        $status = $this->service->checkBudgetStatus();

        expect($status['alert_level'])->toBe('warning')
            ->and($status['is_exceeded'])->toBeFalse();
    });

    it('returns critical alert when near limit', function () {
        Cache::put('cost_total_all', 95.0, 3600);

        $status = $this->service->checkBudgetStatus();

        expect($status['alert_level'])->toBe('critical')
            ->and($status['is_exceeded'])->toBeFalse();
    });

    it('returns exceeded status when over limit', function () {
        Cache::put('cost_total_all', 105.0, 3600);

        $status = $this->service->checkBudgetStatus();

        expect($status['alert_level'])->toBe('exceeded')
            ->and($status['is_exceeded'])->toBeTrue();
    });
});

describe('Cost Breakdown', function () {
    it('returns cost breakdown by provider', function () {
        // Insert test data
        DB::table('ucp_ai_costs')->insert([
            [
                'provider' => 'ollama',
                'model' => 'llama3.3',
                'input_tokens' => 1000,
                'output_tokens' => 500,
                'total_tokens' => 1500,
                'total_cost' => 0.0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'provider' => 'bedrock',
                'model' => 'sonnet',
                'input_tokens' => 2000,
                'output_tokens' => 1000,
                'total_tokens' => 3000,
                'total_cost' => 0.05,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $breakdown = $this->service->getCostBreakdownByProvider(30);

        expect($breakdown)->toHaveKey('ollama')
            ->and($breakdown)->toHaveKey('bedrock')
            ->and($breakdown['ollama']['total_cost'])->toBe(0.0)
            ->and($breakdown['bedrock']['total_cost'])->toBe(0.05);
    });

    it('returns cost breakdown by model', function () {
        DB::table('ucp_ai_costs')->insert([
            [
                'provider' => 'bedrock',
                'model' => 'sonnet',
                'input_tokens' => 1000,
                'output_tokens' => 500,
                'total_tokens' => 1500,
                'total_cost' => 0.02,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'provider' => 'bedrock',
                'model' => 'opus',
                'input_tokens' => 1000,
                'output_tokens' => 500,
                'total_tokens' => 1500,
                'total_cost' => 0.03,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $breakdown = $this->service->getCostBreakdownByModel(30);

        expect($breakdown)->toHaveKey('sonnet')
            ->and($breakdown)->toHaveKey('opus')
            ->and($breakdown['sonnet']['total_cost'])->toBe(0.02)
            ->and($breakdown['opus']['total_cost'])->toBe(0.03);
    });
});

describe('Daily Cost Trend', function () {
    it('returns daily cost trend', function () {
        $today = now()->format('Y-m-d');

        DB::table('ucp_ai_costs')->insert([
            'provider' => 'bedrock',
            'model' => 'sonnet',
            'input_tokens' => 1000,
            'output_tokens' => 500,
            'total_tokens' => 1500,
            'total_cost' => 0.05,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $trend = $this->service->getDailyCostTrend(30);

        expect($trend)->toHaveKey($today)
            ->and($trend[$today])->toBe(0.05);
    });
});

describe('Optimization Recommendations', function () {
    it('recommends using Ollama when only using Bedrock', function () {
        DB::table('ucp_ai_costs')->insert([
            'provider' => 'bedrock',
            'model' => 'sonnet',
            'input_tokens' => 10000,
            'output_tokens' => 5000,
            'total_tokens' => 15000,
            'total_cost' => 50.0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $recommendations = $this->service->getOptimizationRecommendations();

        expect($recommendations)->not->toBeEmpty()
            ->and($recommendations[0]['type'])->toBe('provider_optimization')
            ->and($recommendations[0]['action'])->toContain('Ollama');
    });

    it('recommends reviewing expensive model usage', function () {
        DB::table('ucp_ai_costs')->insert([
            'provider' => 'bedrock',
            'model' => 'opus',
            'input_tokens' => 100000,
            'output_tokens' => 50000,
            'total_tokens' => 150000,
            'total_cost' => 15.0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $recommendations = $this->service->getOptimizationRecommendations();

        $hasModelOptimization = collect($recommendations)
            ->contains(fn ($r) => $r['type'] === 'model_optimization');

        expect($hasModelOptimization)->toBeTrue();
    });

    it('alerts when budget is exceeded', function () {
        Cache::put('cost_total_all', 105.0, 3600);

        $recommendations = $this->service->getOptimizationRecommendations();

        $hasBudgetAlert = collect($recommendations)
            ->contains(fn ($r) => $r['type'] === 'budget_alert');

        expect($hasBudgetAlert)->toBeTrue();
    });
});

describe('Usage Analytics', function () {
    it('returns comprehensive usage analytics', function () {
        DB::table('ucp_ai_costs')->insert([
            [
                'provider' => 'ollama',
                'model' => 'llama3.3',
                'input_tokens' => 1000,
                'output_tokens' => 500,
                'total_tokens' => 1500,
                'total_cost' => 0.0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'provider' => 'bedrock',
                'model' => 'sonnet',
                'input_tokens' => 2000,
                'output_tokens' => 1000,
                'total_tokens' => 3000,
                'total_cost' => 0.05,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        Cache::put('cost_total_all', 0.05, 3600);

        $analytics = $this->service->getUsageAnalytics(30);

        expect($analytics['total_requests'])->toBe(2)
            ->and($analytics['total_cost'])->toBe(0.05)
            ->and($analytics['total_tokens'])->toBe(4500)
            ->and($analytics)->toHaveKey('by_provider')
            ->and($analytics)->toHaveKey('by_model')
            ->and($analytics)->toHaveKey('daily_trend')
            ->and($analytics)->toHaveKey('budget_status')
            ->and($analytics)->toHaveKey('recommendations');
    });
});

describe('Budget Alerts', function () {
    it('should send alert when threshold is reached', function () {
        Cache::put('cost_total_all', 80.0, 3600);

        $shouldSend = $this->service->shouldSendBudgetAlert();

        expect($shouldSend)->toBeTrue();
    });

    it('should not send duplicate alerts', function () {
        Cache::put('cost_total_all', 80.0, 3600);

        // First call should return true
        $shouldSend1 = $this->service->shouldSendBudgetAlert();
        expect($shouldSend1)->toBeTrue();

        // Second call should return false (already sent)
        $shouldSend2 = $this->service->shouldSendBudgetAlert();
        expect($shouldSend2)->toBeFalse();
    });

    it('generates correct alert message', function () {
        Cache::put('cost_total_all', 80.0, 3600);

        $message = $this->service->getBudgetAlertMessage();

        expect($message)->toContain('warning')
            ->and($message)->toContain('80.0%');
    });
});

describe('Monthly Reset', function () {
    it('resets monthly costs and alerts', function () {
        Cache::put('cost_total_all', 50.0, 3600);
        Cache::put('budget_alert_sent_warning', true, 3600);

        $this->service->resetMonthlyCosts();

        expect(Cache::has('cost_total_all'))->toBeFalse()
            ->and(Cache::has('budget_alert_sent_warning'))->toBeFalse();
    });
});
