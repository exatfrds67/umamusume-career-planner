<?php

use App\View\Components\ProgressBar;

describe('ProgressBar Component', function () {
    it('clamps current value to max', function () {
        $component = new ProgressBar(current: 150, max: 100);
        expect($component->current)->toBe(100);

        $component = new ProgressBar(current: -10, max: 100);
        expect($component->current)->toBe(0);
    });

    it('calculates percentage correctly', function () {
        expect((new ProgressBar(current: 50, max: 100))->percentage())->toBe(50.0)
            ->and((new ProgressBar(current: 33, max: 100))->percentage())->toBe(33.0)
            ->and((new ProgressBar(current: 0, max: 100))->percentage())->toBe(0.0)
            ->and((new ProgressBar(current: 100, max: 100))->percentage())->toBe(100.0);
    });

    it('handles division by zero gracefully', function () {
        $component = new ProgressBar(current: 50, max: 0);
        expect($component->percentage())->toBe(0.0);
    });

    it('returns correct stat color classes', function () {
        expect((new ProgressBar(color: 'speed'))->colorClasses())->toContain('blue')
            ->and((new ProgressBar(color: 'stamina'))->colorClasses())->toContain('green')
            ->and((new ProgressBar(color: 'power'))->colorClasses())->toContain('orange')
            ->and((new ProgressBar(color: 'guts'))->colorClasses())->toContain('amber')
            ->and((new ProgressBar(color: 'wit'))->colorClasses())->toContain('sky');
    });

    it('returns correct semantic color classes', function () {
        expect((new ProgressBar(color: 'success'))->colorClasses())->toContain('green-500')
            ->and((new ProgressBar(color: 'warning'))->colorClasses())->toContain('yellow-500')
            ->and((new ProgressBar(color: 'danger'))->colorClasses())->toContain('red-500');
    });

    it('returns correct size classes', function () {
        expect((new ProgressBar(size: 'sm'))->sizeClasses())->toBe('h-2')
            ->and((new ProgressBar(size: 'md'))->sizeClasses())->toBe('h-4')
            ->and((new ProgressBar(size: 'lg'))->sizeClasses())->toBe('h-6');
    });

    it('renders progress bar with correct width', function () {
        $view = $this->blade(
            '<x-progress-bar :current="75" :max="100" />',
        );

        $view->assertSee('75%', false);
    });

    it('shows label when enabled', function () {
        $view = $this->blade(
            '<x-progress-bar :current="50" :max="100" :show-label="true" />',
        );

        $view->assertSee('50%');
    });

    it('shows current and max values when enabled', function () {
        $view = $this->blade(
            '<x-progress-bar :current="25" :max="100" :show-values="true" />',
        );

        $view->assertSee('25')
            ->assertSee('100');
    });

    it('applies animation class when enabled', function () {
        $component = new ProgressBar(animated: true);
        $view = $this->blade(
            '<x-progress-bar :current="50" :max="100" :animated="true" />',
        );

        $view->assertSee('transition-all', false);
    });
});
