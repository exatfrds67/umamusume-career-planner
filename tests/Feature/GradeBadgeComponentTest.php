<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

describe('GradeBadge UI Component', function () {
    it('renders SS grade with gradient and white text', function () {
        $html = Blade::render('<x-ui.grade-badge grade="SS" />');

        expect($html)
            ->toContain('SS')
            ->toContain('from-yellow-400')
            ->toContain('text-white');
    });

    it('renders S grade with purple gradient and white text', function () {
        $html = Blade::render('<x-ui.grade-badge grade="S" />');

        expect($html)
            ->toContain('S')
            ->toContain('from-purple-500')
            ->toContain('text-white');
    });

    it('renders A grade with red background', function () {
        $html = Blade::render('<x-ui.grade-badge grade="A" />');

        expect($html)
            ->toContain('A')
            ->toContain('bg-red-500')
            ->toContain('text-white');
    });

    it('renders C grade with dark text for contrast on yellow', function () {
        $html = Blade::render('<x-ui.grade-badge grade="C" />');

        expect($html)
            ->toContain('C')
            ->toContain('bg-yellow-500')
            ->toContain('text-neutral-900');
    });

    it('renders G grade with neutral-500 background for WCAG AA contrast', function () {
        $html = Blade::render('<x-ui.grade-badge grade="G" />');

        expect($html)
            ->toContain('G')
            ->toContain('bg-neutral-500')
            ->toContain('text-white')
            ->not->toContain('bg-neutral-400');
    });

    it('renders F grade with neutral-500 background', function () {
        $html = Blade::render('<x-ui.grade-badge grade="F" />');

        expect($html)
            ->toContain('F')
            ->toContain('bg-neutral-500')
            ->toContain('text-white');
    });

    it('renders default grade B when no grade is provided', function () {
        $html = Blade::render('<x-ui.grade-badge />');

        expect($html)
            ->toContain('B')
            ->toContain('bg-orange-500');
    });

    it('renders the grade text inside a span', function () {
        $html = Blade::render('<x-ui.grade-badge grade="A" />');

        expect($html)
            ->toContain('<span')
            ->toContain('inline-flex')
            ->toContain('font-bold')
            ->toContain('rounded');
    });
});
