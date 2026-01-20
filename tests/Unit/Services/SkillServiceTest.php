<?php

declare(strict_types=1);

use App\Services\SkillService;
use Illuminate\Foundation\Testing\DatabaseMigrations;

uses(DatabaseMigrations::class);

beforeEach(function () {
    $this->skillService = new SkillService;
});

describe('SkillService', function () {
    describe('calculateFinalCost', function () {
        it('returns base cost when no hints', function () {
            $baseCost = 100;
            $hintCount = 0;

            $result = $this->skillService->calculateFinalCost($baseCost, $hintCount);

            expect($result)->toBe(100);
        });

        it('applies 20% discount with 1 hint', function () {
            $baseCost = 100;
            $hintCount = 1;

            $result = $this->skillService->calculateFinalCost($baseCost, $hintCount);

            expect($result)->toBe(80); // 100 - 20%
        });

        it('applies 40% max discount with 2 hints', function () {
            $baseCost = 100;
            $hintCount = 2;

            $result = $this->skillService->calculateFinalCost($baseCost, $hintCount);

            expect($result)->toBe(60); // 100 - 40%
        });

        it('caps discount at 40% with 3+ hints', function () {
            $baseCost = 100;
            $hintCount = 5;

            $result = $this->skillService->calculateFinalCost($baseCost, $hintCount);

            expect($result)->toBe(60); // Still 40% max
        });

        it('handles odd base costs correctly', function () {
            $baseCost = 150;
            $hintCount = 1;

            $result = $this->skillService->calculateFinalCost($baseCost, $hintCount);

            expect($result)->toBe(120); // 150 - 30 (20%)
        });

        it('never returns negative cost', function () {
            $baseCost = 10;
            $hintCount = 2;

            $result = $this->skillService->calculateFinalCost($baseCost, $hintCount);

            expect($result)->toBeGreaterThanOrEqual(0);
        });
    });

    describe('getDiscountPercentage', function () {
        it('returns 0% for no hints', function () {
            $result = $this->skillService->getDiscountPercentage(0);

            expect($result)->toBe(0.0);
        });

        it('returns 20% for 1 hint', function () {
            $result = $this->skillService->getDiscountPercentage(1);

            expect($result)->toBe(20.0);
        });

        it('returns 40% for 2 hints', function () {
            $result = $this->skillService->getDiscountPercentage(2);

            expect($result)->toBe(40.0);
        });

        it('caps at 40% for 3+ hints', function () {
            $result = $this->skillService->getDiscountPercentage(10);

            expect($result)->toBe(40.0);
        });
    });
});
