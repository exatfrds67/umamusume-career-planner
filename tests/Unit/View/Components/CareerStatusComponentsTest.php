<?php

namespace Tests\Unit\View\Components;

use App\View\Components\ConditionBadge;
use App\View\Components\EnergyGauge;
use App\View\Components\TurnCounter;

describe('TurnCounter Component', function () {
    it('renders with default values', function () {
        $component = new TurnCounter;

        expect($component->current)->toBe(1);
        expect($component->total)->toBe(78);
        expect($component->showStage)->toBeTrue();
        expect($component->showProgress)->toBeTrue();
        expect($component->size)->toBe('md');
    });

    it('accepts custom turn values', function () {
        $component = new TurnCounter(current: 24, total: 78);

        expect($component->current)->toBe(24);
        expect($component->total)->toBe(78);
    });

    it('calculates stage correctly', function () {
        $junior = new TurnCounter(current: 12);
        $classic = new TurnCounter(current: 36);
        $senior = new TurnCounter(current: 60);

        expect($junior->stage())->toBe('Junior');
        expect($classic->stage())->toBe('Classic');
        expect($senior->stage())->toBe('Senior');
    });

    it('calculates progress percentage', function () {
        $component = new TurnCounter(current: 39, total: 78);

        expect($component->progressPercentage())->toBe(50);
    });

    it('returns correct size classes', function () {
        $sizes = [
            'sm' => 'text-xs',
            'md' => 'text-sm',
            'lg' => 'text-base',
        ];

        foreach ($sizes as $size => $expectedClass) {
            $component = new TurnCounter(size: $size);
            expect($component->sizeClasses())->toContain($expectedClass);
        }
    });

    it('renders component view', function () {
        $component = new TurnCounter(current: 24, total: 78);
        $view = $component->render();

        expect($view)->not->toBeNull();
    });
});

describe('ConditionBadge Component', function () {
    it('renders with default condition', function () {
        $component = new ConditionBadge;

        expect($component->condition)->toBe('NORMAL');
        expect($component->size)->toBe('md');
        expect($component->showTrend)->toBeTrue();
    });

    it('accepts all valid conditions', function () {
        $conditions = ['GREAT', 'GOOD', 'NORMAL', 'BAD'];

        foreach ($conditions as $condition) {
            $component = new ConditionBadge(condition: $condition);
            expect($component->condition)->toBe($condition);
        }
    });

    it('normalizes condition to uppercase', function () {
        $component = new ConditionBadge(condition: 'great');

        expect($component->condition)->toBe('GREAT');
    });

    it('returns correct color classes for each condition', function () {
        $colors = [
            'GREAT' => 'bg-pink-500',
            'GOOD' => 'bg-blue-500',
            'NORMAL' => 'bg-orange-500',
            'BAD' => 'bg-red-500',
        ];

        foreach ($colors as $condition => $expectedColor) {
            $component = new ConditionBadge(condition: $condition);
            expect($component->colorClasses())->toContain($expectedColor);
        }
    });

    it('returns correct trend icons', function () {
        $trends = [
            'up' => '↑',
            'down' => '↓',
            'flat' => '→',
        ];

        foreach ($trends as $trend => $expectedIcon) {
            $component = new ConditionBadge(trend: $trend);
            expect($component->trendIcon())->toBe($expectedIcon);
        }
    });

    it('returns correct label for each condition', function () {
        $labels = [
            'GREAT' => 'Great',
            'GOOD' => 'Good',
            'NORMAL' => 'Normal',
            'BAD' => 'Bad',
        ];

        foreach ($labels as $condition => $expectedLabel) {
            $component = new ConditionBadge(condition: $condition);
            expect($component->label())->toBe($expectedLabel);
        }
    });

    it('returns correct size classes', function () {
        $sizes = [
            'sm' => 'text-xs px-2 py-0.5',
            'md' => 'text-sm px-3 py-1',
            'lg' => 'text-base px-4 py-1.5',
        ];

        foreach ($sizes as $size => $expectedClasses) {
            $component = new ConditionBadge(size: $size);
            $classes = $component->sizeClasses();
            foreach (explode(' ', $expectedClasses) as $class) {
                expect($classes)->toContain($class);
            }
        }
    });

    it('renders component view', function () {
        $component = new ConditionBadge(condition: 'GREAT', trend: 'up');
        $view = $component->render();

        expect($view)->not->toBeNull();
    });
});

describe('EnergyGauge Component', function () {
    it('renders with default values', function () {
        $component = new EnergyGauge;

        expect($component->value)->toBe(100);
        expect($component->trend)->toBe('flat');
        expect($component->showIcon)->toBeTrue();
        expect($component->showTrend)->toBeTrue();
        expect($component->size)->toBe('md');
    });

    it('clamps energy value to 0-100 range', function () {
        $low = new EnergyGauge(value: -10);
        $high = new EnergyGauge(value: 150);

        expect($low->value)->toBe(0);
        expect($high->value)->toBe(100);
    });

    it('returns correct energy state based on value', function () {
        $critical = new EnergyGauge(value: 10);
        $low = new EnergyGauge(value: 40);
        $normal = new EnergyGauge(value: 70);

        expect($critical->energyState())->toBe('critical');
        expect($low->energyState())->toBe('low');
        expect($normal->energyState())->toBe('normal');
    });

    it('returns correct color classes for each state', function () {
        $states = [
            0 => 'bg-red-500',    // critical
            40 => 'bg-orange-500', // low
            70 => 'bg-green-500',  // normal
        ];

        foreach ($states as $value => $expectedColor) {
            $component = new EnergyGauge(value: $value);
            expect($component->colorClasses())->toContain($expectedColor);
        }
    });

    it('returns correct trend icon', function () {
        $trends = [
            'up' => '↑',
            'down' => '↓',
            'flat' => '→',
        ];

        foreach ($trends as $trend => $expectedIcon) {
            $component = new EnergyGauge(trend: $trend);
            expect($component->trendIcon())->toBe($expectedIcon);
        }
    });

    it('calculates percentage correctly', function () {
        $component = new EnergyGauge(value: 75);

        expect($component->percentage())->toBe(75);
    });

    it('returns correct size classes', function () {
        $sizes = [
            'sm' => 'h-1',
            'md' => 'h-2',
            'lg' => 'h-3',
        ];

        foreach ($sizes as $size => $expectedClass) {
            $component = new EnergyGauge(size: $size);
            expect($component->sizeClasses())->toContain($expectedClass);
        }
    });

    it('renders component view', function () {
        $component = new EnergyGauge(value: 60, trend: 'down');
        $view = $component->render();

        expect($view)->not->toBeNull();
    });

    it('handles edge case values', function () {
        $zero = new EnergyGauge(value: 0);
        $hundred = new EnergyGauge(value: 100);

        expect($zero->percentage())->toBe(0);
        expect($zero->energyState())->toBe('critical');

        expect($hundred->percentage())->toBe(100);
        expect($hundred->energyState())->toBe('normal');
    });
});
