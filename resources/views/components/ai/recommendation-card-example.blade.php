{{--
    AI Recommendation Card Component - Usage Examples
    
    This file demonstrates various ways to use the recommendation card component.
    It is not meant to be included in production - it's for documentation purposes.
--}}

{{-- Example 1: Minimum Required Properties --}}
<x-ai.recommendation-card :recommendation="(object) [
    'priority' => 'high',
    'action' => 'Speed Training',
]" />

{{-- Example 2: Full Recommendation with All Properties --}}
<x-ai.recommendation-card :recommendation="(object) [
    'id' => 'rec-123',
    'priority' => 'critical',
    'action' => 'Stamina Training',
    'reasoning' => 'Critical stamina shortage for upcoming race
Race distance requires 600 stamina
Current stamina is only 320',
    'expected_outcomes' => [
        'stamina_gain' => '+60-70',
        'bond_increases' => '+7 each (3 cards)',
        'skill_hints' => 'Possible Level 2 hint',
    ],
    'risks' => ['High energy consumption (15 energy)', 'May miss speed training opportunity'],
    'confidence_score' => 0.88,
]" />

{{-- Example 3: Expanded by Default --}}
<x-ai.recommendation-card :recommendation="$recommendation" :expanded="true" />

{{-- Example 4: Without Action Buttons --}}
<x-ai.recommendation-card :recommendation="$recommendation" :show-actions="false" />

{{-- Example 5: With Custom CSS Classes --}}
<x-ai.recommendation-card :recommendation="$recommendation" class="mb-4 shadow-xl" />

{{-- Example 6: Different Priority Levels --}}
<div class="space-y-4">
    {{-- Critical Priority (Red) --}}
    <x-ai.recommendation-card :recommendation="(object) [
        'priority' => 'critical',
        'action' => 'Stamina Crisis Alert',
        'reasoning' => 'Immediate action required',
        'confidence_score' => 0.95,
    ]" />

    {{-- High Priority (Orange) --}}
    <x-ai.recommendation-card :recommendation="(object) [
        'priority' => 'high',
        'action' => 'Speed Training Recommended',
        'reasoning' => 'Optimal conditions present',
        'confidence_score' => 0.92,
    ]" />

    {{-- Medium Priority (Blue) --}}
    <x-ai.recommendation-card :recommendation="(object) [
        'priority' => 'medium',
        'action' => 'Consider Wisdom Training',
        'reasoning' => 'Energy recovery needed',
        'confidence_score' => 0.78,
    ]" />

    {{-- Low Priority (Neutral) --}}
    <x-ai.recommendation-card :recommendation="(object) [
        'priority' => 'low',
        'action' => 'Optional Rest Period',
        'reasoning' => 'No urgent needs',
        'confidence_score' => 0.65,
    ]" />
</div>

{{-- Example 7: Array-based Expected Outcomes --}}
<x-ai.recommendation-card :recommendation="(object) [
    'priority' => 'high',
    'action' => 'Speed Training',
    'expected_outcomes' => ['Speed: +45-55', 'Bonds: +7 each', 'Skill Hints: Possible'],
]" />

{{-- Example 8: Associative Array Expected Outcomes --}}
<x-ai.recommendation-card :recommendation="(object) [
    'priority' => 'high',
    'action' => 'Speed Training',
    'expected_outcomes' => [
        'speed_gain' => '+45-55',
        'bond_increase' => '+7 each',
        'skill_hints' => 'Possible Level 2',
    ],
]" />

{{-- Example 9: In a Livewire Component Context --}}
<div wire:poll.5s>
    @foreach ($recommendations as $recommendation)
        <x-ai.recommendation-card :recommendation="$recommendation" class="mb-3" />
    @endforeach
</div>

{{-- Example 10: With Alpine.js Integration --}}
<div x-data="{ showRecommendations: true }">
    <button @click="showRecommendations = !showRecommendations">
        Toggle Recommendations
    </button>

    <div x-show="showRecommendations" x-transition>
        <x-ai.recommendation-card :recommendation="$recommendation" />
    </div>
</div>

{{--
    Accessibility Features:
    
    - Full keyboard navigation support (Tab, Enter, Escape)
    - ARIA attributes for screen readers
    - Focus management with visible focus indicators
    - Semantic HTML structure
    - High contrast mode support
    - Reduced motion support
    
    Keyboard Shortcuts:
    
    - Tab: Navigate between elements
    - Enter/Space: Toggle expand/collapse
    - Escape: Dismiss recommendation (when focused on dismiss button)
    
    WCAG 2.2 AA Compliance:
    
    - Color contrast ratios meet AA standards
    - Interactive elements have minimum 44x44px touch targets
    - All functionality available via keyboard
    - Screen reader announcements for state changes
    - Focus indicators clearly visible
--}}
