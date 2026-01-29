<?php

use App\View\Components\GoalProgress;

describe('GoalProgress Component', function () {
    it('renders with default values', function () {
        $component = new GoalProgress;

        expect($component->goal)->toBe('G1')
            ->and($component->current)->toBe(0)
            ->and($component->target)->toBe(5)
            ->and($component->size)->toBe('md')
            ->and($component->showLabel)->toBeTrue();
    });

    it('returns correct color classes for G1 goal', function () {
        $component = new GoalProgress(goal: 'G1');

        expect($component->colorClasses())
            ->toContain('text-yellow')
            ->toContain('dark:text-yellow');
    });

    it('returns correct color classes for G2 goal', function () {
        $component = new GoalProgress(goal: 'G2');

        expect($component->colorClasses())
            ->toContain('text-blue')
            ->toContain('dark:text-blue');
    });

    it('returns correct color classes for G3 goal', function () {
        $component = new GoalProgress(goal: 'G3');

        expect($component->colorClasses())
            ->toContain('text-purple')
            ->toContain('dark:text-purple');
    });

    it('returns correct color classes for OP goal', function () {
        $component = new GoalProgress(goal: 'OP');

        expect($component->colorClasses())
            ->toContain('text-green')
            ->toContain('dark:text-green');
    });

    it('returns correct background color for G1', function () {
        $component = new GoalProgress(goal: 'G1');

        expect($component->bgColor())
            ->toContain('bg-yellow');
    });

    it('returns correct background color for G2', function () {
        $component = new GoalProgress(goal: 'G2');

        expect($component->bgColor())
            ->toContain('bg-blue');
    });

    it('returns correct background color for G3', function () {
        $component = new GoalProgress(goal: 'G3');

        expect($component->bgColor())
            ->toContain('bg-purple');
    });

    it('returns correct background color for OP', function () {
        $component = new GoalProgress(goal: 'OP');

        expect($component->bgColor())
            ->toContain('bg-green');
    });

    it('calculates correct percentage for partial progress', function () {
        $component = new GoalProgress(current: 3, target: 5);

        expect($component->percentage())->toBe(60);
    });

    it('calculates 100% when current equals target', function () {
        $component = new GoalProgress(current: 5, target: 5);

        expect($component->percentage())->toBe(100);
    });

    it('calculates 0% when current is 0', function () {
        $component = new GoalProgress(current: 0, target: 5);

        expect($component->percentage())->toBe(0);
    });

    it('returns 0 when target is 0 to avoid division by zero', function () {
        $component = new GoalProgress(current: 5, target: 0);

        expect($component->percentage())->toBe(0);
    });

    it('detects completion when current equals target', function () {
        $component = new GoalProgress(current: 5, target: 5);

        expect($component->isComplete())->toBeTrue();
    });

    it('detects non-completion when current less than target', function () {
        $component = new GoalProgress(current: 3, target: 5);

        expect($component->isComplete())->toBeFalse();
    });

    it('detects completion when current exceeds target', function () {
        $component = new GoalProgress(current: 6, target: 5);

        expect($component->isComplete())->toBeTrue();
    });

    it('returns correct size classes for sm', function () {
        $component = new GoalProgress(size: 'sm');

        expect($component->sizeClasses())
            ->toContain('text-xs');
    });

    it('returns correct size classes for md', function () {
        $component = new GoalProgress(size: 'md');

        expect($component->sizeClasses())
            ->toContain('text-sm');
    });

    it('returns correct size classes for lg', function () {
        $component = new GoalProgress(size: 'lg');

        expect($component->sizeClasses())
            ->toContain('text-base');
    });

    it('hides label when showLabel is false', function () {
        $component = new GoalProgress(showLabel: false);

        expect($component->showLabel)->toBeFalse();
    });
});
