<?php

namespace Tests\Unit\View\Components;

use App\View\Components\StatRadarChart;

describe('StatRadarChart Component', function () {
    it('renders with default values', function () {
        $component = new StatRadarChart;

        expect($component->stats)->toBe([
            'speed' => 0,
            'stamina' => 0,
            'power' => 0,
            'guts' => 0,
            'wit' => 0,
        ]);
        expect($component->max)->toBe(1000);
        expect($component->size)->toBe('md');
        expect($component->showLabels)->toBeTrue();
        expect($component->showValues)->toBeFalse();
        expect($component->animated)->toBeTrue();
    });

    it('accepts custom stats values', function () {
        $stats = [
            'speed' => 500,
            'stamina' => 400,
            'power' => 450,
            'guts' => 300,
            'wit' => 350,
        ];
        $component = new StatRadarChart(stats: $stats, max: 500);

        expect($component->stats)->toBe($stats);
        expect($component->max)->toBe(500);
    });

    it('normalizes stat keys to lowercase', function () {
        $stats = [
            'SPEED' => 500,
            'Stamina' => 400,
            'POWER' => 450,
        ];
        $component = new StatRadarChart(stats: $stats);

        expect($component->stats['speed'])->toBe(500);
        expect($component->stats['stamina'])->toBe(400);
        expect($component->stats['power'])->toBe(450);
    });

    it('returns clamped stat values', function () {
        $stats = ['speed' => 1500, 'stamina' => -100];
        $component = new StatRadarChart(stats: $stats, max: 1000);

        expect($component->getStatValue('speed'))->toBe(1000);
        expect($component->getStatValue('stamina'))->toBe(0);
    });

    it('calculates stat percentages correctly', function () {
        $stats = [
            'speed' => 500,
            'stamina' => 750,
            'power' => 250,
            'guts' => 1000,
            'wit' => 500,
        ];
        $component = new StatRadarChart(stats: $stats, max: 1000);

        $percentages = $component->getStatPercentages();

        expect((int) $percentages['speed'])->toBe(50);
        expect((int) $percentages['stamina'])->toBe(75);
        expect((int) $percentages['power'])->toBe(25);
        expect((int) $percentages['guts'])->toBe(100);
        expect((int) $percentages['wit'])->toBe(50);
    });

    it('returns correct stat colors', function () {
        $component = new StatRadarChart;

        $colors = [
            'speed' => 'text-stat-speed-500 dark:text-stat-speed-400',
            'stamina' => 'text-stat-stamina-500 dark:text-stat-stamina-400',
            'power' => 'text-stat-power-500 dark:text-stat-power-400',
            'guts' => 'text-stat-guts-500 dark:text-stat-guts-400',
            'wit' => 'text-stat-wit-500 dark:text-stat-wit-400',
        ];

        foreach ($colors as $stat => $expectedColor) {
            expect($component->getStatColor($stat))->toBe($expectedColor);
        }
    });

    it('returns correct SVG fill colors', function () {
        $component = new StatRadarChart;

        $colors = [
            'speed' => '#3b82f6',
            'stamina' => '#22c55e',
            'power' => '#f97316',
            'guts' => '#f59e0b',
            'wit' => '#0ea5e9',
        ];

        foreach ($colors as $stat => $expectedColor) {
            expect($component->getSvgFillColor($stat))->toBe($expectedColor);
        }
    });

    it('returns correct size classes', function () {
        $sizes = [
            'sm' => 'w-32 h-32',
            'md' => 'w-64 h-64',
            'lg' => 'w-96 h-96',
        ];

        foreach ($sizes as $size => $expectedClasses) {
            $component = new StatRadarChart(size: $size);
            expect($component->getSizeClasses())->toBe($expectedClasses);
        }
    });

    it('returns stat names in correct order', function () {
        $component = new StatRadarChart;
        $names = $component->getStatNames();

        expect($names)->toBe(['speed', 'stamina', 'power', 'guts', 'wit']);
    });

    it('calculates pentagon points correctly', function () {
        $stats = [
            'speed' => 500,
            'stamina' => 500,
            'power' => 500,
            'guts' => 500,
            'wit' => 500,
        ];
        $component = new StatRadarChart(stats: $stats, max: 1000, size: 'md');

        $points = $component->calculatePoints();

        // Should return 5 points for pentagon
        expect(count($points))->toBe(5);

        // Each point should be a string like "x,y"
        foreach ($points as $point) {
            expect($point)->toMatch('/^\d+(\.\d+)?,\d+(\.\d+)?$/');
        }
    });

    it('generates grid points for reference levels', function () {
        $component = new StatRadarChart(size: 'md');
        $gridPoints = $component->getGridPoints(5);

        // Should have 5 levels
        expect(count($gridPoints))->toBe(5);

        // Each level should have 5 points for pentagon
        foreach ($gridPoints as $level => $points) {
            $pointArray = explode(' ', $points);
            expect(count($pointArray))->toBe(5);
        }
    });

    it('renders component view', function () {
        $component = new StatRadarChart(
            stats: [
                'speed' => 600,
                'stamina' => 500,
                'power' => 700,
                'guts' => 400,
                'wit' => 550,
            ],
            showLabels: true,
            showValues: true
        );
        $view = $component->render();

        expect($view)->not->toBeNull();
    });

    it('handles missing stats gracefully', function () {
        $component = new StatRadarChart(stats: ['speed' => 500]);

        // Missing stats should return 0
        expect($component->getStatValue('stamina'))->toBe(0);
        expect($component->getStatValue('power'))->toBe(0);
    });

    it('supports all size variants with correct proportions', function () {
        $sizes = ['sm', 'md', 'lg'];

        foreach ($sizes as $size) {
            $component = new StatRadarChart(size: $size);
            expect($component->size)->toBe($size);
            expect($component->getSizeClasses())->not->toBeEmpty();
        }
    });
});
