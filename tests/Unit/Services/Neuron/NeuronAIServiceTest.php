<?php

declare(strict_types=1);

use App\Enums\Priority;
use App\Enums\RecommendationType;
use App\Services\AI\HybridAIService;
use App\Services\AI\RecommendationParser;
use App\Services\Neuron\NeuronAIService;
use App\ValueObjects\Recommendation;
use Illuminate\Support\Facades\Config;

describe('NeuronAIService', function () {
    beforeEach(function () {
        $this->hybridAI = Mockery::mock(HybridAIService::class);
        $this->parser = Mockery::mock(RecommendationParser::class);

        $this->service = new NeuronAIService(
            $this->hybridAI,
            $this->parser
        );
    });

    afterEach(function () {
        Mockery::close();
    });

    describe('generateRecommendation', function () {
        it('generates recommendation using Ollama when available', function () {
            $prompt = 'What training should I do?';
            $context = ['character_id' => 1];
            $expectedType = RecommendationType::TRAINING_FACILITY->value;

            // Mock HybridAI response
            $aiResponse = [
                'content' => json_encode([
                    'type' => 'training_facility',
                    'priority' => 'high',
                    'action' => 'Speed Training',
                    'reasoning' => 'Best option for current stats',
                    'expected_outcomes' => ['Speed +50'],
                    'risks' => [],
                    'confidence_score' => 0.9,
                ]),
                'provider' => 'ollama',
                'processing_time' => 1.5,
            ];

            $this->hybridAI
                ->shouldReceive('processRequest')
                ->once()
                ->with($prompt, $context)
                ->andReturn($aiResponse);

            // Mock parser
            $recommendation = new Recommendation(
                type: RecommendationType::TRAINING_FACILITY,
                priority: Priority::HIGH,
                action: 'Speed Training',
                reasoning: 'Best option for current stats',
                expectedOutcomes: ['Speed +50'],
                risks: [],
                confidenceScore: 0.9
            );

            $this->parser
                ->shouldReceive('parse')
                ->once()
                ->with($aiResponse, $expectedType)
                ->andReturn($recommendation);

            $result = $this->service->generateRecommendation($prompt, $context, $expectedType);

            expect($result)->toBeInstanceOf(Recommendation::class);
            expect($result->action)->toBe('Speed Training');
            expect($result->priority)->toBe(Priority::HIGH);
        });

        it('falls back to Bedrock when Ollama fails', function () {
            $prompt = 'What training should I do?';
            $context = ['character_id' => 1];

            // Mock HybridAI response (Bedrock fallback)
            $aiResponse = [
                'content' => json_encode([
                    'priority' => 'high',
                    'action' => 'Stamina Training',
                    'reasoning' => 'Stamina is low',
                    'expected_outcomes' => ['Stamina +45'],
                ]),
                'provider' => 'bedrock',
                'processing_time' => 3.2,
            ];

            $this->hybridAI
                ->shouldReceive('processRequest')
                ->once()
                ->andReturn($aiResponse);

            $recommendation = new Recommendation(
                type: RecommendationType::TRAINING_FACILITY,
                priority: Priority::HIGH,
                action: 'Stamina Training',
                reasoning: 'Stamina is low',
                expectedOutcomes: ['Stamina +45'],
                risks: [],
                confidenceScore: null
            );

            $this->parser
                ->shouldReceive('parse')
                ->once()
                ->andReturn($recommendation);

            $result = $this->service->generateRecommendation($prompt, $context);

            expect($result)->toBeInstanceOf(Recommendation::class);
            expect($result->action)->toBe('Stamina Training');
        });

        it('handles timeout parameter', function () {
            $prompt = 'What training should I do?';
            $context = ['character_id' => 1];
            $timeout = 10;

            $expectedContext = array_merge($context, ['timeout' => $timeout]);

            $aiResponse = [
                'content' => json_encode([
                    'priority' => 'medium',
                    'action' => 'Power Training',
                    'reasoning' => 'Balanced approach',
                ]),
                'provider' => 'ollama',
            ];

            $this->hybridAI
                ->shouldReceive('processRequest')
                ->once()
                ->with($prompt, $expectedContext)
                ->andReturn($aiResponse);

            $recommendation = new Recommendation(
                type: RecommendationType::TRAINING_FACILITY,
                priority: Priority::MEDIUM,
                action: 'Power Training',
                reasoning: 'Balanced approach',
                expectedOutcomes: [],
                risks: [],
                confidenceScore: null
            );

            $this->parser
                ->shouldReceive('parse')
                ->once()
                ->andReturn($recommendation);

            $result = $this->service->generateRecommendation($prompt, $context, 'training_facility', $timeout);

            expect($result)->toBeInstanceOf(Recommendation::class);
        });

        it('throws exception when AI service fails', function () {
            $prompt = 'What training should I do?';

            $this->hybridAI
                ->shouldReceive('processRequest')
                ->once()
                ->andThrow(new \RuntimeException('AI service unavailable'));

            expect(fn () => $this->service->generateRecommendation($prompt))
                ->toThrow(\RuntimeException::class, 'Failed to generate AI recommendation');
        });
    });

    describe('generateMultipleRecommendations', function () {
        it('generates multiple recommendations', function () {
            $prompt = 'Give me top 3 training options';
            $context = ['character_id' => 1];

            $aiResponse = [
                'content' => json_encode([
                    'recommendations' => [
                        [
                            'priority' => 'high',
                            'action' => 'Speed Training',
                            'reasoning' => 'Best option',
                        ],
                        [
                            'priority' => 'medium',
                            'action' => 'Stamina Training',
                            'reasoning' => 'Second best',
                        ],
                        [
                            'priority' => 'low',
                            'action' => 'Power Training',
                            'reasoning' => 'Alternative',
                        ],
                    ],
                ]),
                'provider' => 'ollama',
            ];

            $this->hybridAI
                ->shouldReceive('processRequest')
                ->once()
                ->andReturn($aiResponse);

            $recommendations = [
                new Recommendation(
                    type: RecommendationType::TRAINING_FACILITY,
                    priority: Priority::HIGH,
                    action: 'Speed Training',
                    reasoning: 'Best option',
                    expectedOutcomes: [],
                    risks: [],
                    confidenceScore: null
                ),
                new Recommendation(
                    type: RecommendationType::TRAINING_FACILITY,
                    priority: Priority::MEDIUM,
                    action: 'Stamina Training',
                    reasoning: 'Second best',
                    expectedOutcomes: [],
                    risks: [],
                    confidenceScore: null
                ),
                new Recommendation(
                    type: RecommendationType::TRAINING_FACILITY,
                    priority: Priority::LOW,
                    action: 'Power Training',
                    reasoning: 'Alternative',
                    expectedOutcomes: [],
                    risks: [],
                    confidenceScore: null
                ),
            ];

            $this->parser
                ->shouldReceive('parseMultiple')
                ->once()
                ->andReturn($recommendations);

            $result = $this->service->generateMultipleRecommendations($prompt, $context);

            expect($result)->toBeArray();
            expect($result)->toHaveCount(3);
            expect($result[0])->toBeInstanceOf(Recommendation::class);
            expect($result[0]->priority)->toBe(Priority::HIGH);
        });
    });

    describe('generateRecommendationWithFallback', function () {
        it('returns recommendation on success', function () {
            $prompt = 'What training should I do?';

            $aiResponse = [
                'content' => json_encode([
                    'priority' => 'high',
                    'action' => 'Speed Training',
                    'reasoning' => 'Best option',
                ]),
                'provider' => 'ollama',
            ];

            $this->hybridAI
                ->shouldReceive('processRequest')
                ->once()
                ->andReturn($aiResponse);

            $recommendation = new Recommendation(
                type: RecommendationType::TRAINING_FACILITY,
                priority: Priority::HIGH,
                action: 'Speed Training',
                reasoning: 'Best option',
                expectedOutcomes: [],
                risks: [],
                confidenceScore: null
            );

            $this->parser
                ->shouldReceive('parse')
                ->once()
                ->andReturn($recommendation);

            $result = $this->service->generateRecommendationWithFallback($prompt);

            expect($result)->toBeInstanceOf(Recommendation::class);
            expect($result->action)->toBe('Speed Training');
        });

        it('attempts fallback parsing on failure', function () {
            $prompt = 'What training should I do?';

            // First attempt fails
            $this->hybridAI
                ->shouldReceive('processRequest')
                ->once()
                ->andThrow(new \RuntimeException('Parse error'));

            // Second attempt for fallback
            $aiResponse = [
                'content' => 'I recommend Speed Training because it is the best option.',
                'provider' => 'ollama',
            ];

            $this->hybridAI
                ->shouldReceive('processRequest')
                ->once()
                ->andReturn($aiResponse);

            $recommendation = new Recommendation(
                type: RecommendationType::TRAINING_FACILITY,
                priority: Priority::MEDIUM,
                action: 'I recommend Speed Training',
                reasoning: 'I recommend Speed Training because it is the best option.',
                expectedOutcomes: [],
                risks: [],
                confidenceScore: 0.6
            );

            $this->parser
                ->shouldReceive('parseWithFallback')
                ->once()
                ->andReturn($recommendation);

            $result = $this->service->generateRecommendationWithFallback($prompt);

            expect($result)->toBeInstanceOf(Recommendation::class);
        });

        it('returns null when all attempts fail', function () {
            $prompt = 'What training should I do?';

            $this->hybridAI
                ->shouldReceive('processRequest')
                ->twice()
                ->andThrow(new \RuntimeException('Service unavailable'));

            $result = $this->service->generateRecommendationWithFallback($prompt);

            expect($result)->toBeNull();
        });
    });

    describe('isAvailable', function () {
        it('returns true when Ollama is enabled', function () {
            Config::set('ai.ollama.enabled', true);
            Config::set('ai.bedrock.enabled', false);

            expect($this->service->isAvailable())->toBeTrue();
        });

        it('returns true when Bedrock is enabled', function () {
            Config::set('ai.ollama.enabled', false);
            Config::set('ai.bedrock.enabled', true);

            expect($this->service->isAvailable())->toBeTrue();
        });

        it('returns true when both are enabled', function () {
            Config::set('ai.ollama.enabled', true);
            Config::set('ai.bedrock.enabled', true);

            expect($this->service->isAvailable())->toBeTrue();
        });

        it('returns false when both are disabled', function () {
            Config::set('ai.ollama.enabled', false);
            Config::set('ai.bedrock.enabled', false);

            expect($this->service->isAvailable())->toBeFalse();
        });
    });

    describe('getStatus', function () {
        it('returns comprehensive status information', function () {
            Config::set('ai.ollama.enabled', true);
            Config::set('ai.bedrock.enabled', true);
            Config::set('ai.hybrid.enabled', true);
            Config::set('ai.ollama.timeout', 15);

            $status = $this->service->getStatus();

            expect($status)->toHaveKeys(['available', 'providers', 'default_timeout', 'hybrid_enabled']);
            expect($status['available'])->toBeTrue();
            expect($status['providers'])->toHaveKeys(['ollama', 'bedrock']);
            expect($status['default_timeout'])->toBe(15);
            expect($status['hybrid_enabled'])->toBeTrue();
        });
    });

    describe('testService', function () {
        it('returns success when service is working', function () {
            $aiResponse = [
                'content' => 'OK',
                'provider' => 'ollama',
                'processing_time' => 0.5,
            ];

            $this->hybridAI
                ->shouldReceive('processRequest')
                ->once()
                ->with('Respond with "OK" if you can read this.', ['test' => true])
                ->andReturn($aiResponse);

            $result = $this->service->testService();

            expect($result['success'])->toBeTrue();
            expect($result['provider'])->toBe('ollama');
            expect($result['error'])->toBeNull();
        });

        it('returns failure when service is down', function () {
            $this->hybridAI
                ->shouldReceive('processRequest')
                ->once()
                ->andThrow(new \RuntimeException('Service unavailable'));

            $result = $this->service->testService();

            expect($result['success'])->toBeFalse();
            expect($result['provider'])->toBe('none');
            expect($result['error'])->toBeString();
        });
    });

    describe('getRecommendedTimeout', function () {
        it('returns appropriate timeout for simple requests', function () {
            expect($this->service->getRecommendedTimeout('simple'))->toBe(10);
        });

        it('returns appropriate timeout for medium requests', function () {
            expect($this->service->getRecommendedTimeout('medium'))->toBe(15);
        });

        it('returns appropriate timeout for complex requests', function () {
            expect($this->service->getRecommendedTimeout('complex'))->toBe(30);
        });

        it('returns default timeout for unknown complexity', function () {
            expect($this->service->getRecommendedTimeout('unknown'))->toBe(15);
        });
    });
});
