<?php

use App\View\Components\AptitudeDisplay;
use App\View\Components\CharacterPortrait;

describe('CharacterPortrait Component', function () {
    it('returns correct size classes', function () {
        expect((new CharacterPortrait('/image.jpg', size: 'xs'))->sizeClasses())->toBe('w-12 h-12')
            ->and((new CharacterPortrait('/image.jpg', size: 'sm'))->sizeClasses())->toBe('w-16 h-16')
            ->and((new CharacterPortrait('/image.jpg', size: 'md'))->sizeClasses())->toBe('w-24 h-24')
            ->and((new CharacterPortrait('/image.jpg', size: 'lg'))->sizeClasses())->toBe('w-32 h-32')
            ->and((new CharacterPortrait('/image.jpg', size: 'xl'))->sizeClasses())->toBe('w-40 h-40')
            ->and((new CharacterPortrait('/image.jpg', size: '2xl'))->sizeClasses())->toBe('w-48 h-48');
    });

    it('returns correct badge position classes', function () {
        expect((new CharacterPortrait('/image.jpg', badgePosition: 'top-left'))->badgePositionClasses())->toBe('top-0 left-0')
            ->and((new CharacterPortrait('/image.jpg', badgePosition: 'top-right'))->badgePositionClasses())->toBe('top-0 right-0')
            ->and((new CharacterPortrait('/image.jpg', badgePosition: 'bottom-left'))->badgePositionClasses())->toBe('bottom-0 left-0')
            ->and((new CharacterPortrait('/image.jpg', badgePosition: 'bottom-right'))->badgePositionClasses())->toBe('bottom-0 right-0');
    });

    it('includes border classes when showBorder is true', function () {
        $component = new CharacterPortrait('/image.jpg', showBorder: true);
        expect($component->borderClasses())->toContain('border-2');
    });

    it('excludes border classes when showBorder is false', function () {
        $component = new CharacterPortrait('/image.jpg', showBorder: false);
        expect($component->borderClasses())->toBe('');
    });

    it('includes rounded classes when rounded is true', function () {
        $component = new CharacterPortrait('/image.jpg', rounded: true);
        expect($component->roundedClasses())->toContain('rounded-lg');
    });

    it('renders image with alt text', function () {
        $view = $this->blade(
            '<x-character-portrait image="/test.jpg" alt="Special Week" />',
        );

        $view->assertSee('/test.jpg', false)
            ->assertSee('Special Week');
    });

    it('renders badge when provided', function () {
        $view = $this->blade(
            '<x-character-portrait image="/test.jpg" badge="<span>⭐</span>" />',
        );

        $view->assertSee('⭐', false);
    });
});

describe('AptitudeDisplay Component', function () {
    it('normalizes type and grade to correct case', function () {
        $component = new AptitudeDisplay(type: 'TURF', grade: 's');
        expect($component->type)->toBe('turf')
            ->and($component->grade)->toBe('S');
    });

    it('returns correct grade colors', function () {
        expect((new AptitudeDisplay('turf', 'S'))->gradeColor())->toContain('yellow')
            ->and((new AptitudeDisplay('turf', 'A'))->gradeColor())->toContain('orange')
            ->and((new AptitudeDisplay('turf', 'B'))->gradeColor())->toContain('blue')
            ->and((new AptitudeDisplay('turf', 'C'))->gradeColor())->toContain('green')
            ->and((new AptitudeDisplay('turf', 'D'))->gradeColor())->toContain('neutral')
            ->and((new AptitudeDisplay('turf', 'G'))->gradeColor())->toContain('red');
    });

    it('returns correct type labels', function () {
        expect((new AptitudeDisplay('turf'))->typeLabel())->toBe('Turf')
            ->and((new AptitudeDisplay('dirt'))->typeLabel())->toBe('Dirt')
            ->and((new AptitudeDisplay('short'))->typeLabel())->toBe('Short')
            ->and((new AptitudeDisplay('escape'))->typeLabel())->toBe('Escape');
    });

    it('returns correct layout classes', function () {
        expect((new AptitudeDisplay('turf', layout: 'horizontal'))->layoutClasses())
            ->toContain('flex-row')
            ->and((new AptitudeDisplay('turf', layout: 'vertical'))->layoutClasses())
            ->toContain('flex-col');
    });

    it('renders grade and type label', function () {
        $view = $this->blade(
            '<x-aptitude-display type="turf" grade="A" />',
        );

        $view->assertSee('Turf')
            ->assertSee('A');
    });

    it('shows bonus percentage when provided', function () {
        $view = $this->blade(
            '<x-aptitude-display type="turf" grade="S" :bonus="10" />',
        );

        $view->assertSee('+10%');
    });

    it('hides label when showLabel is false', function () {
        $view = $this->blade(
            '<x-aptitude-display type="turf" grade="A" :show-label="false" />',
        );

        $view->assertDontSee('Turf')
            ->assertSee('A');
    });
});
