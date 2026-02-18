<?php

declare(strict_types=1);

use App\Enums\Priority;
use App\Enums\RecommendationType;
use App\Services\AI\RecommendationParser;
use App\ValueObjects\Recommendation;

describe('RecommendationParser', function () {
    beforeEach(function () {
        $this->parser = new RecommendationParser;
    });

    describe('parse', function () {
        it('parses valid JSON recommendation from AI response', function () {
            $aiResponse = [
                'content' => json_encode([
                    'type' => 'training_facility',
                    'priority' => 'high',
                    'action' => 'Train at Speed facility',
                    'reasoning' => 'Three support cards present with high bond levels, enabling Friendship Training bonus',
                    'expected_outcomes' => [
                        'Speed gain: +45-55',
                        'Bond increases: +7 each',
                    ],
                    'risks' => ['5% failure rate due to energy level'],
                    'confidence_score' => 0.92,
                ]),
                'model' => 'claude-3-5-sonnet',
                'provider' => 'bedrock',
            ];

            $recommendation = $this->parser->parse($aiResponse);

            expect($recommendation)->toBeInstanceOf(Recommendation::class);
            expect($recommendation->type)->toBe(RecommendationType::TRAINING_FACILITY);
            expect($recommendation->priority)->toBe(Priority::HIGH);
            expect($recommendation->action)->toBe('Train at Speed facility');
            expect($recommendation->reasoning)->toContain('Friendship Training');
            expect($recommendation->expectedOutcomes)->toHaveCount(2);
            expect($recommendation->risks)->toHaveCount(1);
            expect($recommendation->confidenceScore)->toBe(0.92);
        });

        it('parses JSON from markdown code block', function () {
            $aiResponse = [
                'content' => "Here's my recommendation:\n\n```json\n".json_encode([
                    'priority' => 'critical',
                    'action' => 'Rest immediately',
                    'reasoning' => 'Energy is critically low at 25, high failure rate risk',
                    'expected_outcomes' => ['Energy recovery: +30'],
                    'risks' => [],
                ])."\n```\n\nThis should help.",
                'model' => 'llama3.3',
                'provider' => 'ollama',
            ];

            $recommendation = $this->parser->parse($aiResponse);

            expect($recommendation)->toBeInstanceOf(Recommendation::class);
            expect($recommendation->priority)->toBe(Priority::CRITICAL);
            expect($recommendation->action)->toBe('Rest immediately');
        });

        it('extracts JSON from mixed text content', function () {
            $aiResponse = [
                'content' => 'Based on the analysis, I recommend: '.json_encode([
                    'priority' => 'medium',
                    'action' => 'Train Wisdom',
                    'reasoning' => 'Facility level is low, good opportunity to level up',
                    'expected_outcomes' => ['Wisdom gain: +30-40', 'Energy recovery: +5'],
                ]),
                'model' => 'claude-3-5-haiku',
                'provider' => 'bedrock',
            ];

            $recommendation = $this->parser->parse($aiResponse);

            expect($recommendation)->toBeInstanceOf(Recommendation::class);
            expect($recommendation->priority)->toBe(Priority::MEDIUM);
        });

        it('throws exception for missing content field', function () {
            $aiResponse = [
                'model' => 'claude-3-5-sonnet',
                'provider' => 'bedrock',
            ];

            expect(fn () => $this->parser->parse($aiResponse))
                ->toThrow(InvalidArgumentException::class, 'missing content field');
        });

        it('throws exception for empty content', function () {
            $aiResponse = [
                'content' => '   ',
                'model' => 'claude-3-5-sonnet',
                'provider' => 'bedrock',
            ];

            expect(fn () => $this->parser->parse($aiResponse))
                ->toThrow(InvalidArgumentException::class, 'content is empty');
        });

        it('throws exception for invalid JSON', function () {
            $aiResponse = [
                'content' => 'This is not JSON at all, just plain text without structure',
                'model' => 'claude-3-5-sonnet',
                'provider' => 'bedrock',
            ];

            expect(fn () => $this->parser->parse($aiResponse))
                ->toThrow(InvalidArgumentException::class);
        });

        it('throws exception for missing required fields', function () {
            $aiResponse = [
                'content' => json_encode([
                    'priority' => 'high',
                    // Missing action and reasoning
                ]),
                'model' => 'claude-3-5-sonnet',
                'provider' => 'bedrock',
            ];

            expect(fn () => $this->parser->parse($aiResponse))
                ->toThrow(InvalidArgumentException::class, 'Invalid recommendation structure');
        });

        it('validates priority values', function () {
            $aiResponse = [
                'content' => json_encode([
                    'priority' => 'invalid_priority',
                    'action' => 'Do something',
                    'reasoning' => 'Because reasons',
                ]),
                'model' => 'claude-3-5-sonnet',
                'provider' => 'bedrock',
            ];

            expect(fn () => $this->parser->parse($aiResponse))
                ->toThrow(InvalidArgumentException::class);
        });

        it('handles optional fields gracefully', function () {
            $aiResponse = [
                'content' => json_encode([
                    'priority' => 'low',
                    'action' => 'Consider training Power',
                    'reasoning' => 'Power stat is slightly behind target',
                    // No expected_outcomes, risks, or confidence_score
                ]),
                'model' => 'claude-3-5-sonnet',
                'provider' => 'bedrock',
            ];

            $recommendation = $this->parser->parse($aiResponse);

            expect($recommendation)->toBeInstanceOf(Recommendation::class);
            expect($recommendation->expectedOutcomes)->toBeArray()->toBeEmpty();
            expect($recommendation->risks)->toBeArray()->toBeEmpty();
            expect($recommendation->confidenceScore)->toBeNull();
        });

        it('uses expected type when type field is missing', function () {
            $aiResponse = [
                'content' => json_encode([
                    'priority' => 'high',
                    'action' => 'Purchase Swinging Maestro',
                    'reasoning' => 'Gold skill with Level 3 hint available',
                ]),
                'model' => 'claude-3-5-sonnet',
                'provider' => 'bedrock',
            ];

            $recommendation = $this->parser->parse($aiResponse, RecommendationType::SKILL_PURCHASE->value);

            expect($recommendation->type)->toBe(RecommendationType::SKILL_PURCHASE);
        });

        it('normalizes type values with underscores', function () {
            $aiResponse = [
                'content' => json_encode([
                    'type' => 'trainingfacility', // No underscore
                    'priority' => 'high',
                    'action' => 'Train Speed',
                    'reasoning' => 'Best option available',
                ]),
                'model' => 'claude-3-5-sonnet',
                'provider' => 'bedrock',
            ];

            $recommendation = $this->parser->parse($aiResponse);

            expect($recommendation->type)->toBe(RecommendationType::TRAINING_FACILITY);
        });

        it('handles case-insensitive priority values', function () {
            $aiResponse = [
                'content' => json_encode([
                    'priority' => 'HIGH', // Uppercase
                    'action' => 'Train Speed',
                    'reasoning' => 'Best option',
                ]),
                'model' => 'claude-3-5-sonnet',
                'provider' => 'bedrock',
            ];

            $recommendation = $this->parser->parse($aiResponse);

            expect($recommendation->priority)->toBe(Priority::HIGH);
        });
    });

    describe('parseMultiple', function () {
        it('parses multiple recommendations from array', function () {
            $aiResponse = [
                'content' => json_encode([
                    'recommendations' => [
                        [
                            'priority' => 'high',
                            'action' => 'Train Speed',
                            'reasoning' => 'Three support cards present',
                        ],
                        [
                            'priority' => 'medium',
                            'action' => 'Train Stamina',
                            'reasoning' => 'Two support cards present',
                        ],
                        [
                            'priority' => 'low',
                            'action' => 'Train Power',
                            'reasoning' => 'One support card present',
                        ],
                    ],
                ]),
                'model' => 'claude-3-5-sonnet',
                'provider' => 'bedrock',
            ];

            $recommendations = $this->parser->parseMultiple($aiResponse);

            expect($recommendations)->toBeArray()->toHaveCount(3);
            expect($recommendations[0]->priority)->toBe(Priority::HIGH);
            expect($recommendations[1]->priority)->toBe(Priority::MEDIUM);
            expect($recommendations[2]->priority)->toBe(Priority::LOW);
        });

        it('returns single recommendation as array', function () {
            $aiResponse = [
                'content' => json_encode([
                    'priority' => 'high',
                    'action' => 'Train Speed',
                    'reasoning' => 'Best option',
                ]),
                'model' => 'claude-3-5-sonnet',
                'provider' => 'bedrock',
            ];

            $recommendations = $this->parser->parseMultiple($aiResponse);

            expect($recommendations)->toBeArray()->toHaveCount(1);
            expect($recommendations[0])->toBeInstanceOf(Recommendation::class);
        });

        it('validates each recommendation in array', function () {
            $aiResponse = [
                'content' => json_encode([
                    'recommendations' => [
                        [
                            'priority' => 'high',
                            'action' => 'Train Speed',
                            'reasoning' => 'Good option',
                        ],
                        [
                            'priority' => 'invalid', // Invalid priority
                            'action' => 'Train Stamina',
                            'reasoning' => 'Another option',
                        ],
                    ],
                ]),
                'model' => 'claude-3-5-sonnet',
                'provider' => 'bedrock',
            ];

            expect(fn () => $this->parser->parseMultiple($aiResponse))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('parseWithFallback', function () {
        it('returns parsed recommendation on success', function () {
            $aiResponse = [
                'content' => json_encode([
                    'priority' => 'high',
                    'action' => 'Train Speed',
                    'reasoning' => 'Best option',
                ]),
                'model' => 'claude-3-5-sonnet',
                'provider' => 'bedrock',
            ];

            $recommendation = $this->parser->parseWithFallback($aiResponse);

            expect($recommendation)->toBeInstanceOf(Recommendation::class);
            expect($recommendation->priority)->toBe(Priority::HIGH);
        });

        it('falls back to natural language parsing on JSON failure', function () {
            $aiResponse = [
                'content' => 'I strongly recommend training at the Speed facility immediately. This is critical because your character needs to improve speed stats urgently for the upcoming race.',
                'model' => 'llama3.3',
                'provider' => 'ollama',
            ];

            $recommendation = $this->parser->parseWithFallback($aiResponse);

            expect($recommendation)->toBeInstanceOf(Recommendation::class);
            expect($recommendation->action)->toContain('recommend training');
            expect($recommendation->priority)->toBe(Priority::CRITICAL); // Inferred from "critical"
            expect($recommendation->confidenceScore)->toBe(0.6); // Lower confidence for NL parsing
        });

        it('returns null when all parsing strategies fail', function () {
            $aiResponse = [
                'content' => '', // Empty content
                'model' => 'claude-3-5-sonnet',
                'provider' => 'bedrock',
            ];

            $recommendation = $this->parser->parseWithFallback($aiResponse);

            expect($recommendation)->toBeNull();
        });
    });

    describe('natural language parsing', function () {
        it('infers critical priority from keywords', function () {
            $aiResponse = [
                'content' => 'You must train immediately! This is critical for your character survival.',
                'model' => 'llama3.3',
                'provider' => 'ollama',
            ];

            $recommendation = $this->parser->parseWithFallback($aiResponse);

            expect($recommendation->priority)->toBe(Priority::CRITICAL);
        });

        it('infers high priority from keywords', function () {
            $aiResponse = [
                'content' => 'It is important that you train Speed. I strongly recommend this action.',
                'model' => 'llama3.3',
                'provider' => 'ollama',
            ];

            $recommendation = $this->parser->parseWithFallback($aiResponse);

            expect($recommendation->priority)->toBe(Priority::HIGH);
        });

        it('infers low priority from keywords', function () {
            $aiResponse = [
                'content' => 'You might consider training Power. This is optional and not urgent.',
                'model' => 'llama3.3',
                'provider' => 'ollama',
            ];

            $recommendation = $this->parser->parseWithFallback($aiResponse);

            expect($recommendation->priority)->toBe(Priority::LOW);
        });

        it('defaults to medium priority without keywords', function () {
            $aiResponse = [
                'content' => 'Train at the Wisdom facility for stat gains.',
                'model' => 'llama3.3',
                'provider' => 'ollama',
            ];

            $recommendation = $this->parser->parseWithFallback($aiResponse);

            expect($recommendation->priority)->toBe(Priority::MEDIUM);
        });

        it('extracts outcomes from natural language', function () {
            $aiResponse = [
                'content' => 'Train Speed. You can expect to gain +50 Speed and improve your race performance.',
                'model' => 'llama3.3',
                'provider' => 'ollama',
            ];

            $recommendation = $this->parser->parseWithFallback($aiResponse);

            expect($recommendation->expectedOutcomes)->not->toBeEmpty();
        });

        it('extracts risks from natural language', function () {
            $aiResponse = [
                'content' => 'Train Speed, but be warned: there is a risk of injury due to low energy.',
                'model' => 'llama3.3',
                'provider' => 'ollama',
            ];

            $recommendation = $this->parser->parseWithFallback($aiResponse);

            expect($recommendation->risks)->not->toBeEmpty();
        });

        it('uses first line as action', function () {
            $aiResponse = [
                'content' => "Train at Speed facility\n\nThis is the best option because you have three support cards present.",
                'model' => 'llama3.3',
                'provider' => 'ollama',
            ];

            $recommendation = $this->parser->parseWithFallback($aiResponse);

            expect($recommendation->action)->toBe('Train at Speed facility');
        });

        it('uses full content as reasoning', function () {
            $aiResponse = [
                'content' => 'Train Speed because it is the optimal choice given your current situation.',
                'model' => 'llama3.3',
                'provider' => 'ollama',
            ];

            $recommendation = $this->parser->parseWithFallback($aiResponse);

            expect($recommendation->reasoning)->toBe('Train Speed because it is the optimal choice given your current situation.');
        });
    });

    describe('edge cases', function () {
        it('handles very long content', function () {
            $longReasoning = str_repeat('This is a very detailed explanation. ', 100);
            $aiResponse = [
                'content' => json_encode([
                    'priority' => 'high',
                    'action' => 'Train Speed',
                    'reasoning' => $longReasoning,
                ]),
                'model' => 'claude-3-5-sonnet',
                'provider' => 'bedrock',
            ];

            $recommendation = $this->parser->parse($aiResponse);

            expect($recommendation)->toBeInstanceOf(Recommendation::class);
            expect(strlen($recommendation->reasoning))->toBeGreaterThan(1000);
        });

        it('handles special characters in content', function () {
            $aiResponse = [
                'content' => json_encode([
                    'priority' => 'high',
                    'action' => 'Train "Speed" facility',
                    'reasoning' => "Use the character's special ability & maximize gains!",
                ]),
                'model' => 'claude-3-5-sonnet',
                'provider' => 'bedrock',
            ];

            $recommendation = $this->parser->parse($aiResponse);

            expect($recommendation->action)->toContain('"Speed"');
            expect($recommendation->reasoning)->toContain('&');
        });

        it('handles unicode characters', function () {
            $aiResponse = [
                'content' => json_encode([
                    'priority' => 'high',
                    'action' => 'スピードトレーニング (Speed Training)',
                    'reasoning' => 'Best option for ウマ娘',
                ]),
                'model' => 'claude-3-5-sonnet',
                'provider' => 'bedrock',
            ];

            $recommendation = $this->parser->parse($aiResponse);

            expect($recommendation->action)->toContain('スピード');
        });

        it('handles numeric confidence scores as strings', function () {
            $aiResponse = [
                'content' => json_encode([
                    'priority' => 'high',
                    'action' => 'Train Speed',
                    'reasoning' => 'Best option',
                    'confidence_score' => '0.85', // String instead of float
                ]),
                'model' => 'claude-3-5-sonnet',
                'provider' => 'bedrock',
            ];

            $recommendation = $this->parser->parse($aiResponse);

            expect($recommendation->confidenceScore)->toBe(0.85);
        });

        it('handles nested arrays in expected outcomes', function () {
            $aiResponse = [
                'content' => json_encode([
                    'priority' => 'high',
                    'action' => 'Train Speed',
                    'reasoning' => 'Best option',
                    'expected_outcomes' => [
                        'stats' => ['speed' => '+50', 'stamina' => '+10'],
                        'bonds' => ['+7', '+7', '+7'],
                    ],
                ]),
                'model' => 'claude-3-5-sonnet',
                'provider' => 'bedrock',
            ];

            $recommendation = $this->parser->parse($aiResponse);

            expect($recommendation->expectedOutcomes)->toBeArray();
            expect($recommendation->expectedOutcomes)->toHaveKey('stats');
        });
    });
});
