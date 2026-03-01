<?php

declare(strict_types=1);

use App\Services\AI\AIPerformanceMonitor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    // Clear metrics before each test
    Cache::forget('ai_performance_global');

    $this->monitor = new AIPerformanceMonitor;
});

describe('AIPerformanceMonitor', function () {
    describe('trackRequest', function () {
        it('tracks successful AI request', function () {
            $response = [
                'processing_time' => 1.5,
                'token_count' => 100,
                'cost' => 0.005,
            ];

            Log::shouldReceive('debug')->once();

            $this->monitor->trackRequest('ollama', $response);

            $metrics = $this->monitor->getMetrics();

            expect($metrics['total_requests'])->toBe(1);
            expect($metrics['total_tokens'])->toBe(100);
            expect($metrics['total_cost'])->toBe(0.005);
            expect($metrics['providers']['ollama']['total_requests'])->toBe(1);
            expect($metrics['providers']['ollama']['successful_requests'])->toBe(1);
            expect($metrics['providers']['ollama']['success_rate'])->toBe(1);
        });

        it('calculates average metrics correctly', function () {
            Log::shouldReceive('debug')->twice();

            $this->monitor->trackRequest('bedrock', [
                'processing_time' => 2.0,
                'token_count' => 200,
                'cost' => 0.01,
            ]);

            $this->monitor->trackRequest('bedrock', [
                'processing_time' => 4.0,
                'token_count' => 400,
                'cost' => 0.02,
            ]);

            $metrics = $this->monitor->getProviderMetrics('bedrock');

            expect($metrics['total_requests'])->toBe(2);
            expect($metrics['average_processing_time'])->toBe(3.0);
            expect($metrics['average_tokens'])->toBe(300);
            expect($metrics['average_cost'])->toBe(0.015);
        });

        it('initializes provider metrics if not exists', function () {
            Log::shouldReceive('debug')->once();

            $this->monitor->trackRequest('mcp-strands', [
                'processing_time' => 0.5,
                'token_count' => 50,
                'cost' => 0.0,
            ]);

            $metrics = $this->monitor->getProviderMetrics('mcp-strands');

            expect($metrics)->toBeArray();
            expect($metrics['total_requests'])->toBe(1);
            expect($metrics['failed_requests'])->toBe(0);
        });

        it('handles missing response fields gracefully', function () {
            Log::shouldReceive('debug')->once();

            $this->monitor->trackRequest('ollama', []); // Empty response

            $metrics = $this->monitor->getProviderMetrics('ollama');

            expect($metrics['total_processing_time'])->toBe(0.0);
            expect($metrics['total_tokens'])->toBe(0);
            expect($metrics['total_cost'])->toBe(0.0);
        });

        it('logs error on tracking failure', function () {
            // Force an exception by mocking Cache to throw
            Cache::shouldReceive('get')->andThrow(new \Exception('Cache error'));
            Log::shouldReceive('error')->once();

            $this->monitor->trackRequest('ollama', ['processing_time' => 1.0]);

            // Should not throw exception - errors are caught and logged
            expect(true)->toBeTrue();
        });
    });

    describe('trackFailure', function () {
        it('tracks failed AI request', function () {
            Log::shouldReceive('warning')->once();

            $this->monitor->trackFailure('bedrock', 'Connection timeout');

            $metrics = $this->monitor->getMetrics();

            expect($metrics['total_failures'])->toBe(1);
            expect($metrics['providers']['bedrock']['failed_requests'])->toBe(1);
            expect($metrics['providers']['bedrock']['success_rate'])->toBe(0);
        });

        it('calculates success rate after mixed results', function () {
            Log::shouldReceive('debug')->twice();
            Log::shouldReceive('warning')->once();

            $this->monitor->trackRequest('ollama', ['processing_time' => 1.0, 'token_count' => 100, 'cost' => 0.0]);
            $this->monitor->trackRequest('ollama', ['processing_time' => 1.0, 'token_count' => 100, 'cost' => 0.0]);
            $this->monitor->trackFailure('ollama', 'Model unavailable');

            $metrics = $this->monitor->getProviderMetrics('ollama');

            expect($metrics['total_requests'])->toBe(3);
            expect($metrics['successful_requests'])->toBe(2);
            expect($metrics['failed_requests'])->toBe(1);
            expect($metrics['success_rate'])->toBeGreaterThan(0.6)->toBeLessThan(0.7);
        });
    });

    describe('getProviderComparison', function () {
        it('compares multiple providers', function () {
            Log::shouldReceive('debug')->times(3);

            $this->monitor->trackRequest('ollama', [
                'processing_time' => 0.5,
                'token_count' => 100,
                'cost' => 0.0,
            ]);

            $this->monitor->trackRequest('bedrock', [
                'processing_time' => 2.0,
                'token_count' => 200,
                'cost' => 0.01,
            ]);

            $this->monitor->trackRequest('mcp-strands', [
                'processing_time' => 1.0,
                'token_count' => 150,
                'cost' => 0.005,
            ]);

            $comparison = $this->monitor->getProviderComparison();

            expect($comparison)->toHaveKeys(['ollama', 'bedrock', 'mcp-strands']);
            expect($comparison['ollama']['success_rate'])->toBe(100.0);
            expect($comparison['bedrock']['average_cost'])->toBeGreaterThan(0.0);
        });
    });

    describe('getCostSummary', function () {
        it('generates cost summary by provider', function () {
            Log::shouldReceive('debug')->twice();

            $this->monitor->trackRequest('ollama', ['cost' => 0.0, 'token_count' => 100, 'processing_time' => 1.0]);
            $this->monitor->trackRequest('bedrock', ['cost' => 0.05, 'token_count' => 500, 'processing_time' => 3.0]);

            $costSummary = $this->monitor->getCostSummary();

            expect($costSummary)->toHaveKeys(['total_cost', 'by_provider', 'by_model']);
            expect($costSummary['total_cost'])->toBe(0.05);
            expect($costSummary['by_provider']['ollama'])->toBe(0.0);
            expect($costSummary['by_provider']['bedrock'])->toBe(0.05);
            expect($costSummary['by_model'])->toHaveKey('llama3.3');
            expect($costSummary['by_model'])->toHaveKey('claude-3-5-sonnet');
        });
    });

    describe('getPerformanceReport', function () {
        it('generates comprehensive performance report', function () {
            Log::shouldReceive('debug')->twice();

            $this->monitor->trackRequest('ollama', [
                'processing_time' => 1.0,
                'token_count' => 100,
                'cost' => 0.0,
            ]);

            $this->monitor->trackRequest('bedrock', [
                'processing_time' => 2.5,
                'token_count' => 250,
                'cost' => 0.015,
            ]);

            $report = $this->monitor->getPerformanceReport();

            expect($report)->toHaveKeys(['summary', 'providers', 'cost_summary', 'recommendations']);
            expect($report['summary'])->toHaveKeys(['total_requests', 'total_cost', 'total_failures', 'average_processing_time', 'last_updated']);
            expect($report['summary']['total_requests'])->toBe(2);
            expect($report['providers'])->toHaveKeys(['ollama', 'bedrock']);
        });

        it('generates recommendations for high costs', function () {
            Log::shouldReceive('debug')->once();

            $this->monitor->trackRequest('bedrock', [
                'processing_time' => 2.0,
                'token_count' => 1000,
                'cost' => 1.5, // High cost
            ]);

            $report = $this->monitor->getPerformanceReport();

            expect($report['recommendations'])->toContain('Total AI costs are high. Review request complexity and model selection');
        });

        it('recommends Ollama optimization when underutilized', function () {
            Log::shouldReceive('debug')->times(5);

            // Track 1 Ollama request
            $this->monitor->trackRequest('ollama', ['processing_time' => 0.5, 'token_count' => 50, 'cost' => 0.0]);

            // Track 4 Bedrock requests (more than 2x Ollama)
            foreach (range(1, 4) as $i) {
                $this->monitor->trackRequest('bedrock', ['processing_time' => 2.0, 'token_count' => 200, 'cost' => 0.01]);
            }

            $report = $this->monitor->getPerformanceReport();

            expect($report['recommendations'])->toContain('Consider routing more simple requests to Ollama to reduce costs');
        });
    });

    describe('resetMetrics', function () {
        it('clears all metrics', function () {
            Log::shouldReceive('debug')->once();
            Log::shouldReceive('info')->once();

            $this->monitor->trackRequest('ollama', ['processing_time' => 1.0, 'token_count' => 100, 'cost' => 0.0]);

            $this->monitor->resetMetrics();

            $metrics = $this->monitor->getMetrics();

            expect($metrics['total_requests'])->toBe(0);
            expect($metrics['providers'])->toBeEmpty();
        });
    });
});
