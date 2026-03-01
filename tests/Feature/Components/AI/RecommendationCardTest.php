<?php

use App\View\Components\Ai\RecommendationCard;
use Illuminate\Support\Facades\Blade;

describe('RecommendationCard Component', function () {
    it('renders with minimum required properties', function () {
        $recommendation = (object) [
            'priority' => 'high',
            'action' => 'Speed Training',
            'reasoning' => 'Good support card presence',
            'expected_outcomes' => ['Speed: +45-55'],
            'risks' => ['5% failure rate'],
            'confidence_score' => 0.92,
        ];

        $component = new RecommendationCard($recommendation);
        $view = $component->render();

        expect($view)->toBeInstanceOf(\Illuminate\Contracts\View\View::class);
    });

    it('validates required recommendation properties', function () {
        $recommendation = (object) [
            'action' => 'Speed Training',
            // Missing 'priority'
        ];

        expect(fn () => new RecommendationCard($recommendation))
            ->toThrow(\InvalidArgumentException::class, "must have 'priority' property");
    });

    it('validates priority values', function () {
        $recommendation = (object) [
            'priority' => 'invalid',
            'action' => 'Speed Training',
        ];

        expect(fn () => new RecommendationCard($recommendation))
            ->toThrow(\InvalidArgumentException::class, 'Invalid priority');
    });

    it('accepts all valid priority levels', function () {
        $priorities = ['critical', 'high', 'medium', 'low'];

        foreach ($priorities as $priority) {
            $recommendation = (object) [
                'priority' => $priority,
                'action' => 'Test Action',
            ];

            $component = new RecommendationCard($recommendation);
            expect($component->recommendation->priority)->toBe($priority); // @phpstan-ignore property.notFound
        }
    });

    it('renders collapsed by default', function () {
        $recommendation = (object) [
            'priority' => 'high',
            'action' => 'Speed Training',
        ];

        $component = new RecommendationCard($recommendation);

        expect($component->expanded)->toBeFalse();
    });

    it('can be rendered expanded', function () {
        $recommendation = (object) [
            'priority' => 'high',
            'action' => 'Speed Training',
        ];

        $component = new RecommendationCard($recommendation, expanded: true);

        expect($component->expanded)->toBeTrue();
    });

    it('shows action buttons by default', function () {
        $recommendation = (object) [
            'priority' => 'high',
            'action' => 'Speed Training',
        ];

        $component = new RecommendationCard($recommendation);

        expect($component->showActions)->toBeTrue();
    });

    it('can hide action buttons', function () {
        $recommendation = (object) [
            'priority' => 'high',
            'action' => 'Speed Training',
        ];

        $component = new RecommendationCard($recommendation, showActions: false);

        expect($component->showActions)->toBeFalse();
    });

    it('renders with all sections populated', function () {
        $recommendation = (object) [
            'id' => 'rec-123',
            'priority' => 'critical',
            'action' => 'Stamina Training',
            'reasoning' => "Critical stamina shortage\nRace in 3 turns",
            'expected_outcomes' => [
                'stamina_gain' => '+60-70',
                'bonds' => '+7 each',
            ],
            'risks' => [
                'High energy consumption',
                'May miss speed training opportunity',
            ],
            'confidence_score' => 0.88,
        ];

        $html = Blade::render(
            '<x-ai.recommendation-card :recommendation="$recommendation" />',
            ['recommendation' => $recommendation]
        );

        expect($html)
            ->toContain('Stamina Training')
            ->toContain('Critical')
            ->toContain('88%')
            ->toContain('Critical stamina shortage')
            ->toContain('Race in 3 turns')
            ->toContain('+60-70')
            ->toContain('High energy consumption');
    });

    it('handles missing optional properties gracefully', function () {
        $recommendation = (object) [
            'priority' => 'low',
            'action' => 'Rest',
        ];

        $html = Blade::render(
            '<x-ai.recommendation-card :recommendation="$recommendation" />',
            ['recommendation' => $recommendation]
        );

        expect($html)
            ->toContain('Rest')
            ->toContain('Low');
    });

    it('applies correct priority colors', function () {
        $priorities = [
            'critical' => 'red',
            'high' => 'orange',
            'medium' => 'blue',
            'low' => 'neutral',
        ];

        foreach ($priorities as $priority => $color) {
            $recommendation = (object) [
                'priority' => $priority,
                'action' => 'Test Action',
            ];

            $html = Blade::render(
                '<x-ai.recommendation-card :recommendation="$recommendation" />',
                ['recommendation' => $recommendation]
            );

            expect($html)->toContain("border-{$color}-200");
        }
    });

    it('includes proper ARIA attributes', function () {
        $recommendation = (object) [
            'priority' => 'high',
            'action' => 'Speed Training',
        ];

        $html = Blade::render(
            '<x-ai.recommendation-card :recommendation="$recommendation" />',
            ['recommendation' => $recommendation]
        );

        expect($html)
            ->toContain('role="article"')
            ->toContain('aria-labelledby=')
            ->toContain('aria-expanded=')
            ->toContain('aria-controls=')
            ->toContain('aria-label=');
    });

    it('includes keyboard navigation support', function () {
        $recommendation = (object) [
            'priority' => 'high',
            'action' => 'Speed Training',
        ];

        $html = Blade::render(
            '<x-ai.recommendation-card :recommendation="$recommendation" />',
            ['recommendation' => $recommendation]
        );

        expect($html)
            ->toContain('@click="expanded = !expanded"')
            ->toContain('focus:outline-hidden')
            ->toContain('focus:ring-2');
    });

    it('renders action buttons with proper attributes', function () {
        $recommendation = (object) [
            'id' => 'rec-456',
            'priority' => 'high',
            'action' => 'Speed Training',
        ];

        $html = Blade::render(
            '<x-ai.recommendation-card :recommendation="$recommendation" />',
            ['recommendation' => $recommendation]
        );

        expect($html)
            ->toContain('wire:click="applyRecommendation(\'rec-456\')"')
            ->toContain('wire:click="provideFeedback(\'rec-456\')"')
            ->toContain('Apply Recommendation')
            ->toContain('Dismiss')
            ->toContain('Feedback');
    });

    it('handles array expected outcomes', function () {
        $recommendation = (object) [
            'priority' => 'high',
            'action' => 'Speed Training',
            'expected_outcomes' => [
                'Speed: +45-55',
                'Bonds: +7 each',
                'Skill Hints: Possible',
            ],
        ];

        $html = Blade::render(
            '<x-ai.recommendation-card :recommendation="$recommendation" />',
            ['recommendation' => $recommendation]
        );

        expect($html)
            ->toContain('Speed: +45-55')
            ->toContain('Bonds: +7 each')
            ->toContain('Skill Hints: Possible');
    });

    it('handles associative array expected outcomes', function () {
        $recommendation = (object) [
            'priority' => 'high',
            'action' => 'Speed Training',
            'expected_outcomes' => [
                'speed_gain' => '+45-55',
                'bond_increase' => '+7 each',
            ],
        ];

        $html = Blade::render(
            '<x-ai.recommendation-card :recommendation="$recommendation" />',
            ['recommendation' => $recommendation]
        );

        expect($html)
            ->toContain('Speed gain:')
            ->toContain('+45-55')
            ->toContain('Bond increase:')
            ->toContain('+7 each');
    });

    it('formats multi-line reasoning correctly', function () {
        $recommendation = (object) [
            'priority' => 'high',
            'action' => 'Speed Training',
            'reasoning' => "Line 1: First reason\nLine 2: Second reason\nLine 3: Third reason",
        ];

        $html = Blade::render(
            '<x-ai.recommendation-card :recommendation="$recommendation" />',
            ['recommendation' => $recommendation]
        );

        expect($html)
            ->toContain('First reason')
            ->toContain('Second reason')
            ->toContain('Third reason');
    });

    it('includes dismiss functionality', function () {
        $recommendation = (object) [
            'priority' => 'high',
            'action' => 'Speed Training',
        ];

        $html = Blade::render(
            '<x-ai.recommendation-card :recommendation="$recommendation" />',
            ['recommendation' => $recommendation]
        );

        expect($html)
            ->toContain('x-data')
            ->toContain('dismissed: false')
            ->toContain('x-show="!dismissed"')
            ->toContain('@click="dismissed = true"');
    });

    it('supports custom CSS classes', function () {
        $recommendation = (object) [
            'priority' => 'high',
            'action' => 'Speed Training',
        ];

        $html = Blade::render(
            '<x-ai.recommendation-card :recommendation="$recommendation" class="custom-class" />',
            ['recommendation' => $recommendation]
        );

        expect($html)->toContain('custom-class');
    });
});
