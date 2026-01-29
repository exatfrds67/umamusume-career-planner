<?php

use App\View\Components\RaceDayBadge;

describe('RaceDayBadge Component', function () {
    it('renders with default values', function () {
        $component = new RaceDayBadge;

        expect($component->daysUntil)->toBe(7)
            ->and($component->isRaceDay)->toBeFalse()
            ->and($component->size)->toBe('md')
            ->and($component->showCountdown)->toBeTrue();
    });

    it('returns correct color classes for blue when days > 7', function () {
        $component = new RaceDayBadge(daysUntil: 8);

        expect($component->colorClasses())
            ->toContain('bg-blue-500');
    });

    it('returns correct color classes for amber when days between 4 and 7', function () {
        $component = new RaceDayBadge(daysUntil: 5);

        expect($component->colorClasses())
            ->toContain('bg-amber-500');
    });

    it('returns correct color classes for orange when days between 2 and 3', function () {
        $component = new RaceDayBadge(daysUntil: 2);

        expect($component->colorClasses())
            ->toContain('bg-orange-500');
    });

    it('returns correct color classes for red when days is 1', function () {
        $component = new RaceDayBadge(daysUntil: 1);

        expect($component->colorClasses())
            ->toContain('bg-red-500');
    });

    it('returns correct color classes for red when isRaceDay is true', function () {
        $component = new RaceDayBadge(isRaceDay: true);

        expect($component->colorClasses())
            ->toContain('bg-red-600');
    });

    it('sets isRaceDay to true when daysUntil is 0', function () {
        $component = new RaceDayBadge(daysUntil: 0);

        expect($component->isRaceDay)->toBeTrue();
    });

    it('clamps negative days to 0', function () {
        $component = new RaceDayBadge(daysUntil: -5);

        expect($component->daysUntil)->toBe(0)
            ->and($component->isRaceDay)->toBeTrue();
    });

    it('returns RACE DAY label when isRaceDay is true', function () {
        $component = new RaceDayBadge(isRaceDay: true);

        expect($component->label())->toBe('RACE DAY');
    });

    it('returns Tomorrow label when days is 1', function () {
        $component = new RaceDayBadge(daysUntil: 1);

        expect($component->label())->toBe('Tomorrow');
    });

    it('returns In X days label for multiple days', function () {
        $component = new RaceDayBadge(daysUntil: 5);

        expect($component->label())->toBe('In 5 days');
    });

    it('returns correct size classes for sm', function () {
        $component = new RaceDayBadge(size: 'sm');

        expect($component->sizeClasses())
            ->toContain('px-2')
            ->toContain('py-0.5')
            ->toContain('text-xs');
    });

    it('returns correct size classes for md', function () {
        $component = new RaceDayBadge(size: 'md');

        expect($component->sizeClasses())
            ->toContain('px-3')
            ->toContain('py-1')
            ->toContain('text-sm');
    });

    it('returns correct size classes for lg', function () {
        $component = new RaceDayBadge(size: 'lg');

        expect($component->sizeClasses())
            ->toContain('px-4')
            ->toContain('py-1.5')
            ->toContain('text-base');
    });

    it('hides countdown text when showCountdown is false', function () {
        $component = new RaceDayBadge(showCountdown: false);

        expect($component->showCountdown)->toBeFalse();
    });
});
