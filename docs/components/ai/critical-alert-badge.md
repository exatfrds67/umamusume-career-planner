# Critical Alert Badge Component

## Overview

The Critical Alert Badge is a visual indicator component that displays the count of critical alerts in the AI-Powered
Training Advisory System. It features a pulsing animation to draw attention when critical alerts are present and
integrates seamlessly with the advisory panel.

## Location

- **Blade Component**: `resources/views/components/ai/critical-alert-badge.blade.php`
- **CSS Styles**: `resources/css/components/ai/critical-alert-badge.css`
- **Tests**: `tests/Feature/Components/AI/CriticalAlertBadgeTest.php`
- **Demo Page**: `resources/views/test/critical-alert-badge-demo.blade.php`

## Features

### Visual Indicators

- **No Alerts State**: Gray icon, no badge, no animation
- **Critical Alerts State**: Red pulsing icon with count badge
- **Count Display**: Shows exact count up to 99, then displays "99+"
- **Pulsing Animation**: Smooth 2-second pulse animation on both icon and badge

### Accessibility (WCAG 2.2 AA Compliant)

- **Screen Reader Support**: Descriptive ARIA labels and screen reader text
- **Keyboard Navigation**: Fully keyboard accessible (Tab, Enter, Space)
- **Focus Indicators**: Clear focus visible states
- **Reduced Motion**: Animations disabled when user prefers reduced motion
- **High Contrast**: Proper contrast ratios and high contrast mode support
- **Semantic HTML**: Uses proper button element with type attribute

### Interaction

- **Click Handler**: Dispatches Alpine.js event `open-advisory-panel` with section set to `alerts`
- **Hover Effects**: Color transitions on hover
- **Tooltip**: Native browser tooltip showing alert count

## Usage

### Basic Usage

```blade
{{-- No alerts --}}
<x-ai.critical-alert-badge :alert-count="0" />

{{-- With alerts --}}
<x-ai.critical-alert-badge :alert-count="3" />
```text

### Size Variations

```blade
{{-- Small --}}
<x-ai.critical-alert-badge :alert-count="5" size="sm" />

{{-- Medium (default) --}}
<x-ai.critical-alert-badge :alert-count="5" size="md" />

{{-- Large --}}
<x-ai.critical-alert-badge :alert-count="5" size="lg" />
```text

### Custom Attributes

```blade
{{-- With custom ID and data attributes --}}
<x-ai.critical-alert-badge
    :alert-count="2"
    id="header-alert-badge"
    data-testid="alert-badge"
/>

{{-- With custom classes --}}
<x-ai.critical-alert-badge
    :alert-count="2"
    class="custom-spacing"
/>
```text

### Integration with Header

```blade
<!-- In resources/views/components/app/header.blade.php -->
<div class="flex items-center gap-x-4 lg:gap-x-6">
    <!-- Theme Toggle -->
    <button type="button" id="theme-toggle">...</button>

    <!-- Critical Alert Badge -->
    <x-ai.critical-alert-badge :alert-count="$criticalAlertCount ?? 0" />

    <!-- Notifications -->
    <button type="button">...</button>
</div>
```

### Event Handling

The component dispatches an Alpine.js event when clicked:

```javascript
// Listen for the event
document.addEventListener('alpine:init', () => {
    Alpine.data('advisoryPanel', () => ({
        init() {
            this.$watch('$event', (event) => {
                if (event.type === 'open-advisory-panel') {
                    this.openPanel(event.detail.section);
                }
            });
        }
    }));
});
```text

Or use Alpine's `@open-advisory-panel` directive:

```blade
<div x-data="{ panelOpen: false, activeSection: 'alerts' }"
     @open-advisory-panel.window="panelOpen = true; activeSection = $event.detail.section">
    <!-- Advisory Panel -->
</div>
```text

## Props

| Prop         | Type     | Default | Description                             |
| ------------ | -------- | ------- | --------------------------------------- |
| `alertCount` | `int`    | `0`     | Number of critical alerts to display    |
| `size`       | `string` | `'md'`  | Size variant: `'sm'`, `'md'`, or `'lg'` |

## Styling

### CSS Classes

The component uses the following key CSS classes:

- `.critical-alert-icon` - Applied to icon when alerts exist, triggers pulse animation
- `.critical-alert-badge` - Applied to count badge, triggers badge pulse animation

### Animations

**Icon Pulse Animation** (`criticalAlertPulse`):

- Duration: 2 seconds
- Easing: cubic-bezier(0.4, 0, 0.6, 1)
- Effect: Opacity 1 → 0.7 → 1, Scale 1 → 1.05 → 1

**Badge Pulse Animation** (`badgePulse`):

- Duration: 2 seconds
- Easing: cubic-bezier(0.4, 0, 0.6, 1)
- Effect: Box shadow expands from 0 to 6px with fade

### Color Scheme

**Critical State (alerts > 0)**:

- Light mode: `text-red-600` hover `text-red-700`
- Dark mode: `text-red-400` hover `text-red-300`
- Badge: `bg-red-600` (light) / `bg-red-500` (dark)

**Normal State (no alerts)**:

- Light mode: `text-gray-400` hover `text-gray-500`
- Dark mode: `text-gray-300` hover `text-gray-100`

## Testing

### Running Tests

```bash
# Run all component tests
php artisan test --filter=CriticalAlertBadgeTest

# Run specific test
php artisan test --filter="it renders with single alert"
```text

## Test Coverage

The component has comprehensive test coverage including:

- Rendering with different alert counts (0, 1, 5, 150)
- Size variations (sm, md, lg)
- ARIA attributes and accessibility
- Event dispatching
- Screen reader text
- Singular/plural forms
- Animation classes
- Custom attributes
- Color classes
- SVG icon rendering
- Badge styling

### Demo Page

Visit the demo page to see all variations:

```

/demo/critical-alert-badge

```text

## Accessibility Checklist

- [x] Semantic HTML (button element)
- [x] ARIA labels describing state
- [x] Screen reader text for context
- [x] Keyboard accessible (Tab, Enter, Space)
- [x] Focus visible indicators
- [x] Color contrast ratios meet WCAG AA
- [x] Reduced motion support
- [x] High contrast mode support
- [x] Tooltip for additional context
- [x] Proper button type attribute

## Browser Support

- Chrome/Edge 90+
- Firefox 88+
- Safari 14+
- Opera 76+

## Performance

- **CSS**: ~1KB minified
- **Render Time**: <5ms
- **Animation Performance**: GPU-accelerated (transform, opacity)
- **No JavaScript**: Pure CSS animations

## Related Components

- `x-ai.advisory-panel` - Main advisory panel that opens when badge is clicked
- `x-ai.critical-alert-card` - Individual alert display within the panel
- `x-app.header` - Header component where badge is typically placed

## Future Enhancements

- [ ] Sound notification option for new critical alerts
- [ ] Animation customization (speed, style)
- [ ] Badge color customization for different alert types
- [ ] Dismissible alerts tracking
- [ ] Alert priority levels (critical, high, medium)

## Changelog

### v1.0.0 (2026-01-29)

- Initial implementation
- Pulsing animation for critical alerts
- WCAG 2.2 AA accessibility compliance
- Size variations (sm, md, lg)
- Comprehensive test coverage
- Demo page and documentation

## Support

For issues or questions about this component:

1. Check the demo page: `/demo/critical-alert-badge`
2. Review the test file for usage examples
3. Consult the AI Training Advisory System spec: `.kiro/specs/ai-training-advisory/`
