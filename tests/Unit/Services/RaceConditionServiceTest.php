<?php

declare(strict_types=1);

use App\Services\RaceConditionService;

describe('RaceConditionService', function () {
    beforeEach(function () {
        $this->service = new RaceConditionService;
    });

    describe('Power Penalty Calculations', function () {
        it('returns no penalty for firm conditions on turf', function () {
            $penalty = $this->service->calculatePowerPenalty('firm', 'turf');
            expect($penalty)->toBe(0);
        });

        it('returns no penalty for firm conditions on dirt', function () {
            $penalty = $this->service->calculatePowerPenalty('firm', 'dirt');
            expect($penalty)->toBe(0);
        });

        it('returns -50 penalty for good conditions on turf', function () {
            $penalty = $this->service->calculatePowerPenalty('good', 'turf');
            expect($penalty)->toBe(-50);
        });

        it('returns -50 penalty for good conditions on dirt', function () {
            $penalty = $this->service->calculatePowerPenalty('good', 'dirt');
            expect($penalty)->toBe(-50);
        });

        it('returns -50 penalty for soft conditions on turf', function () {
            $penalty = $this->service->calculatePowerPenalty('soft', 'turf');
            expect($penalty)->toBe(-50);
        });

        it('returns -100 penalty for soft conditions on dirt', function () {
            $penalty = $this->service->calculatePowerPenalty('soft', 'dirt');
            expect($penalty)->toBe(-100);
        });

        it('returns -50 penalty for heavy conditions on turf', function () {
            $penalty = $this->service->calculatePowerPenalty('heavy', 'turf');
            expect($penalty)->toBe(-50);
        });

        it('returns -100 penalty for heavy conditions on dirt', function () {
            $penalty = $this->service->calculatePowerPenalty('heavy', 'dirt');
            expect($penalty)->toBe(-100);
        });
    });

    describe('Speed Penalty Calculations', function () {
        it('returns no penalty for firm conditions', function () {
            expect($this->service->calculateSpeedPenalty('firm', 'turf'))->toBe(0);
            expect($this->service->calculateSpeedPenalty('firm', 'dirt'))->toBe(0);
        });

        it('returns no penalty for good conditions', function () {
            expect($this->service->calculateSpeedPenalty('good', 'turf'))->toBe(0);
            expect($this->service->calculateSpeedPenalty('good', 'dirt'))->toBe(0);
        });

        it('returns no penalty for soft conditions', function () {
            expect($this->service->calculateSpeedPenalty('soft', 'turf'))->toBe(0);
            expect($this->service->calculateSpeedPenalty('soft', 'dirt'))->toBe(0);
        });

        it('returns -50 penalty for heavy conditions on turf', function () {
            $penalty = $this->service->calculateSpeedPenalty('heavy', 'turf');
            expect($penalty)->toBe(-50);
        });

        it('returns -50 penalty for heavy conditions on dirt', function () {
            $penalty = $this->service->calculateSpeedPenalty('heavy', 'dirt');
            expect($penalty)->toBe(-50);
        });
    });

    describe('Stamina Drain Calculations', function () {
        it('returns no drain for firm conditions', function () {
            expect($this->service->calculateStaminaDrain('firm', 'turf'))->toBe(0.0);
            expect($this->service->calculateStaminaDrain('firm', 'dirt'))->toBe(0.0);
        });

        it('returns no drain for good conditions', function () {
            expect($this->service->calculateStaminaDrain('good', 'turf'))->toBe(0.0);
            expect($this->service->calculateStaminaDrain('good', 'dirt'))->toBe(0.0);
        });

        it('returns 2% drain for soft conditions on turf', function () {
            $drain = $this->service->calculateStaminaDrain('soft', 'turf');
            expect($drain)->toBe(2.0);
        });

        it('returns 2% drain for soft conditions on dirt', function () {
            $drain = $this->service->calculateStaminaDrain('soft', 'dirt');
            expect($drain)->toBe(2.0);
        });

        it('returns 2% drain for heavy conditions on turf', function () {
            $drain = $this->service->calculateStaminaDrain('heavy', 'turf');
            expect($drain)->toBe(2.0);
        });

        it('returns 2% drain for heavy conditions on dirt', function () {
            $drain = $this->service->calculateStaminaDrain('heavy', 'dirt');
            expect($drain)->toBe(2.0);
        });
    });

    describe('Apply Condition Penalties', function () {
        it('does not modify stats for firm conditions', function () {
            $stats = [
                'speed' => 1000,
                'stamina' => 800,
                'power' => 900,
                'guts' => 700,
                'wit' => 600,
            ];

            $modified = $this->service->applyConditionPenalties($stats, 'firm', 'turf');

            expect($modified)->toBe($stats);
        });

        it('applies power penalty for good conditions on turf', function () {
            $stats = [
                'speed' => 1000,
                'power' => 900,
            ];

            $modified = $this->service->applyConditionPenalties($stats, 'good', 'turf');

            expect($modified['speed'])->toBe(1000); // No speed penalty
            expect($modified['power'])->toBe(850); // -50 power penalty
        });

        it('applies both power and speed penalties for heavy conditions on dirt', function () {
            $stats = [
                'speed' => 1000,
                'power' => 900,
            ];

            $modified = $this->service->applyConditionPenalties($stats, 'heavy', 'dirt');

            expect($modified['speed'])->toBe(950); // -50 speed penalty
            expect($modified['power'])->toBe(800); // -100 power penalty
        });

        it('prevents stats from going below zero', function () {
            $stats = [
                'speed' => 30,
                'power' => 40,
            ];

            $modified = $this->service->applyConditionPenalties($stats, 'heavy', 'dirt');

            expect($modified['speed'])->toBe(0); // Would be -20, clamped to 0
            expect($modified['power'])->toBe(0); // Would be -60, clamped to 0
        });
    });

    describe('Wet Condition Detection', function () {
        it('identifies firm as not wet', function () {
            expect($this->service->isWetCondition('firm'))->toBeFalse();
        });

        it('identifies good as wet', function () {
            expect($this->service->isWetCondition('good'))->toBeTrue();
        });

        it('identifies soft as wet', function () {
            expect($this->service->isWetCondition('soft'))->toBeTrue();
        });

        it('identifies heavy as wet', function () {
            expect($this->service->isWetCondition('heavy'))->toBeTrue();
        });
    });

    describe('Condition Severity', function () {
        it('returns correct severity levels', function () {
            expect($this->service->getConditionSeverity('firm'))->toBe(0);
            expect($this->service->getConditionSeverity('good'))->toBe(1);
            expect($this->service->getConditionSeverity('soft'))->toBe(2);
            expect($this->service->getConditionSeverity('heavy'))->toBe(3);
        });

        it('returns 0 for invalid conditions', function () {
            expect($this->service->getConditionSeverity('invalid'))->toBe(0);
        });
    });

    describe('Condition Impact Description', function () {
        it('describes optimal conditions', function () {
            $description = $this->service->getConditionImpactDescription('firm', 'turf');
            expect($description)->toBe('Optimal conditions - no penalties');
        });

        it('describes good conditions on turf', function () {
            $description = $this->service->getConditionImpactDescription('good', 'turf');
            expect($description)->toBe('Power -50');
        });

        it('describes heavy conditions on dirt', function () {
            $description = $this->service->getConditionImpactDescription('heavy', 'dirt');
            expect($description)->toContain('Power -100');
            expect($description)->toContain('Speed -50');
            expect($description)->toContain('Stamina drain +2%/sec');
        });

        it('describes soft conditions on turf', function () {
            $description = $this->service->getConditionImpactDescription('soft', 'turf');
            expect($description)->toContain('Power -50');
            expect($description)->toContain('Stamina drain +2%/sec');
        });
    });

    describe('Performance Impact Score', function () {
        it('returns 100 for optimal conditions', function () {
            $score = $this->service->calculatePerformanceImpact('firm', 'turf');
            expect($score)->toBe(100.0);
        });

        it('calculates impact for good conditions on turf', function () {
            $score = $this->service->calculatePerformanceImpact('good', 'turf');
            // Power -50 = -2.0 points
            expect($score)->toBe(98.0);
        });

        it('calculates impact for soft conditions on dirt', function () {
            $score = $this->service->calculatePerformanceImpact('soft', 'dirt');
            // Power -100 = -4.0 points
            // Stamina drain 2% = -2.0 points
            expect($score)->toBe(94.0);
        });

        it('calculates impact for heavy conditions on dirt', function () {
            $score = $this->service->calculatePerformanceImpact('heavy', 'dirt');
            // Power -100 = -4.0 points
            // Speed -50 = -2.0 points
            // Stamina drain 2% = -2.0 points
            expect($score)->toBe(92.0);
        });

        it('never returns negative scores', function () {
            $score = $this->service->calculatePerformanceImpact('heavy', 'dirt');
            expect($score)->toBeGreaterThanOrEqual(0.0);
        });

        it('never returns scores above 100', function () {
            $score = $this->service->calculatePerformanceImpact('firm', 'turf');
            expect($score)->toBeLessThanOrEqual(100.0);
        });
    });

    describe('Recommended Skills', function () {
        it('recommends sunny day skill for sunny weather', function () {
            $skills = $this->service->getRecommendedSkills('sunny', 'firm');
            expect($skills)->toContain('Sunny Days ◯');
        });

        it('recommends cloudy day skill for cloudy weather', function () {
            $skills = $this->service->getRecommendedSkills('cloudy', 'firm');
            expect($skills)->toContain('Cloudy Days ◯');
        });

        it('recommends rainy day skill for rainy weather', function () {
            $skills = $this->service->getRecommendedSkills('rainy', 'good');
            expect($skills)->toContain('Rainy Days ◯');
        });

        it('recommends snowy day skill for snowy weather', function () {
            $skills = $this->service->getRecommendedSkills('snowy', 'heavy');
            expect($skills)->toContain('Snowy Days ◯');
        });

        it('recommends firm conditions skill for firm track', function () {
            $skills = $this->service->getRecommendedSkills('sunny', 'firm');
            expect($skills)->toContain('Firm Conditions ◯');
        });

        it('recommends wet conditions skill for good track', function () {
            $skills = $this->service->getRecommendedSkills('rainy', 'good');
            expect($skills)->toContain('Wet Conditions ◯');
        });

        it('recommends wet conditions skill for soft track', function () {
            $skills = $this->service->getRecommendedSkills('rainy', 'soft');
            expect($skills)->toContain('Wet Conditions ◯');
        });

        it('recommends wet conditions skill for heavy track', function () {
            $skills = $this->service->getRecommendedSkills('snowy', 'heavy');
            expect($skills)->toContain('Wet Conditions ◯');
        });

        it('handles null weather gracefully', function () {
            $skills = $this->service->getRecommendedSkills(null, 'firm');
            expect($skills)->toContain('Firm Conditions ◯');
            expect($skills)->not->toContain('Sunny Days ◯');
        });
    });

    describe('Validation Methods', function () {
        it('validates track conditions', function () {
            expect($this->service->isValidTrackCondition('firm'))->toBeTrue();
            expect($this->service->isValidTrackCondition('good'))->toBeTrue();
            expect($this->service->isValidTrackCondition('soft'))->toBeTrue();
            expect($this->service->isValidTrackCondition('heavy'))->toBeTrue();
            expect($this->service->isValidTrackCondition('invalid'))->toBeFalse();
        });

        it('validates surface types', function () {
            expect($this->service->isValidSurface('turf'))->toBeTrue();
            expect($this->service->isValidSurface('dirt'))->toBeTrue();
            expect($this->service->isValidSurface('invalid'))->toBeFalse();
        });

        it('validates weather types', function () {
            expect($this->service->isValidWeather('sunny'))->toBeTrue();
            expect($this->service->isValidWeather('cloudy'))->toBeTrue();
            expect($this->service->isValidWeather('rainy'))->toBeTrue();
            expect($this->service->isValidWeather('snowy'))->toBeTrue();
            expect($this->service->isValidWeather('invalid'))->toBeFalse();
        });
    });
});
