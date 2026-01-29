<?php

use App\View\Components\TraineeEventBanner;

describe('TraineeEventBanner Component', function () {
    it('renders with default values', function () {
        $component = new TraineeEventBanner;

        expect($component->type)->toBe('event')
            ->and($component->title)->toBe('Event')
            ->and($component->message)->toBe('')
            ->and($component->icon)->toBe('📢')
            ->and($component->dismissible)->toBeTrue();
    });

    it('returns correct background color for event type', function () {
        $component = new TraineeEventBanner(type: 'event');

        expect($component->bgColor())
            ->toContain('bg-orange')
            ->toContain('border-orange');
    });

    it('returns correct background color for warning type', function () {
        $component = new TraineeEventBanner(type: 'warning');

        expect($component->bgColor())
            ->toContain('bg-red')
            ->toContain('border-red');
    });

    it('returns correct background color for achievement type', function () {
        $component = new TraineeEventBanner(type: 'achievement');

        expect($component->bgColor())
            ->toContain('bg-green')
            ->toContain('border-green');
    });

    it('returns correct background color for training type', function () {
        $component = new TraineeEventBanner(type: 'training');

        expect($component->bgColor())
            ->toContain('bg-blue')
            ->toContain('border-blue');
    });

    it('returns correct text color for event type', function () {
        $component = new TraineeEventBanner(type: 'event');

        expect($component->textColor())
            ->toContain('text-orange');
    });

    it('returns correct text color for warning type', function () {
        $component = new TraineeEventBanner(type: 'warning');

        expect($component->textColor())
            ->toContain('text-red');
    });

    it('returns correct text color for achievement type', function () {
        $component = new TraineeEventBanner(type: 'achievement');

        expect($component->textColor())
            ->toContain('text-green');
    });

    it('returns correct text color for training type', function () {
        $component = new TraineeEventBanner(type: 'training');

        expect($component->textColor())
            ->toContain('text-blue');
    });

    it('returns correct accent color for event type', function () {
        $component = new TraineeEventBanner(type: 'event');

        expect($component->accentColor())
            ->toContain('text-orange')
            ->toContain('dark:text-orange');
    });

    it('returns correct accent color for warning type', function () {
        $component = new TraineeEventBanner(type: 'warning');

        expect($component->accentColor())
            ->toContain('text-red')
            ->toContain('dark:text-red');
    });

    it('returns correct accent color for achievement type', function () {
        $component = new TraineeEventBanner(type: 'achievement');

        expect($component->accentColor())
            ->toContain('text-green')
            ->toContain('dark:text-green');
    });

    it('returns correct accent color for training type', function () {
        $component = new TraineeEventBanner(type: 'training');

        expect($component->accentColor())
            ->toContain('text-blue')
            ->toContain('dark:text-blue');
    });

    it('returns event type with emoji fallback for event type', function () {
        $component = new TraineeEventBanner(type: 'event');

        $icon = $component->getIcon();
        expect($icon)->not->toBeEmpty();
    });

    it('returns warning emoji for warning type', function () {
        $component = new TraineeEventBanner(type: 'warning', icon: '');

        expect($component->getIcon())->toBe('⚠️');
    });

    it('returns achievement emoji for achievement type', function () {
        $component = new TraineeEventBanner(type: 'achievement', icon: '');

        expect($component->getIcon())->toBe('🏆');
    });

    it('returns training emoji for training type', function () {
        $component = new TraineeEventBanner(type: 'training', icon: '');

        expect($component->getIcon())->toBe('💪');
    });

    it('returns default event emoji when icon not provided', function () {
        $component = new TraineeEventBanner(type: 'event', icon: '');

        expect($component->getIcon())->toBe('📢');
    });

    it('returns custom icon when provided', function () {
        $component = new TraineeEventBanner(icon: '⭐');

        expect($component->getIcon())->toBe('⭐');
    });

    it('accepts custom title', function () {
        $component = new TraineeEventBanner(title: 'Custom Title');

        expect($component->title)->toBe('Custom Title');
    });

    it('accepts custom message', function () {
        $component = new TraineeEventBanner(message: 'Custom message here');

        expect($component->message)->toBe('Custom message here');
    });

    it('allows dismissible to be set to false', function () {
        $component = new TraineeEventBanner(dismissible: false);

        expect($component->dismissible)->toBeFalse();
    });

    it('correctly renders border color based on type', function () {
        $component = new TraineeEventBanner(type: 'achievement');

        $bgColor = $component->bgColor();
        expect($bgColor)->toContain('border-green');
    });
});
