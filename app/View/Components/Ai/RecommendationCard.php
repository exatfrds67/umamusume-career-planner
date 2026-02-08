<?php

namespace App\View\Components\Ai;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * AI Recommendation Card Component
 *
 * Displays individual AI-powered recommendations with:
 * - Collapsed/expanded views
 * - Priority-based styling
 * - Reasoning, outcomes, and risks sections
 * - Action buttons (Apply, Dismiss, Feedback)
 * - Full WCAG 2.2 AA accessibility compliance
 */
class RecommendationCard extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public object $recommendation,
        public bool $expanded = false,
        public bool $showActions = true,
    ) {
        // Ensure recommendation has required properties
        $this->validateRecommendation();
    }

    /**
     * Validate that the recommendation object has required properties.
     */
    protected function validateRecommendation(): void
    {
        $required = ['priority', 'action'];

        foreach ($required as $property) {
            if (! property_exists($this->recommendation, $property)) {
                throw new \InvalidArgumentException(
                    "Recommendation object must have '{$property}' property"
                );
            }
        }

        // Validate priority value
        $validPriorities = ['critical', 'high', 'medium', 'low'];
        $priority = property_exists($this->recommendation, 'priority') ? $this->recommendation->priority : null;

        if (! \is_string($priority) || ! \in_array($priority, $validPriorities, true)) {
            throw new \InvalidArgumentException(
                'Invalid priority. Must be one of: '.\implode(', ', $validPriorities)
            );
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.ai.recommendation-card');
    }
}
