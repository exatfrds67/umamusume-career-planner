# Blade Asset Refactoring Plan

**Date**: 2026-01-29
**Status**: In Progress
**Goal**: Separate inline CSS and JavaScript from Blade templates into dedicated external resource files

## Overview

This document tracks the refactoring of inline `<style>` and `<script>` blocks from Blade templates to improve:

- Code maintainability
- Build optimization
- Browser caching
- Development workflow

## Refactoring Strategy

### 1. Analysis Strategy

**Global Assets**: Extract to `resources/css/app.css` or `resources/js/app.js`

- Common utilities, resets, shared components
- Global libraries (Bootstrap overrides, Alpine.js stores)

**View-Specific Assets**: Create dedicated files

- CSS/JS targeting specific IDs or classes unique to that view
- Logic only relevant to that page

### 2. File Structure

```text
resources/
├── css/
│   ├── app.css (global styles)
│   ├── components/ (component-specific styles)
│   │   ├── turn-counter.css ✅
│   │   ├── stat-bar.css ✅
│   │   ├── gauges.css ✅ (energy-gauge, bond-meter)
│   │   ├── condition-badge.css ✅
│   │   ├── grade-badge.css
│   │   ├── support-card.css
│   │   ├── character-card.css
│   │   ├── skill-card.css
│   │   ├── skill-icon.css
│   │   ├── keyboard-shortcuts.css
│   │   ├── quick-actions.css
│   │   ├── slide-panel.css
│   │   └── stat-radar-chart.css
│   └── pages/ (page-specific styles)
│       ├── support-cards/
│       ├── skills/
│       ├── races/
│       ├── profile/
│       ├── ocr/
│       └── ...
└── js/
    ├── app.js (global scripts)
    ├── components/ (component-specific scripts)
    │   ├── quick-actions.js
    │   ├── slide-panel.js
    │   ├── line-chart.js
    │   ├── class-pyramid.js
    │   ├── password-input.js
    │   ├── team-member-selector.js
    │   ├── spirit-burst-gauge.js
    │   ├── facility-management.js
    │   └── ai/ (AI-related components)
    │       ├── workflow-visualization.js
    │       ├── tool-usage-indicator.js
    │       ├── tool-execution-monitor.js
    │       ├── server-status-indicator.js
    │       ├── provider-selector.js
    │       ├── performance-metrics.js
    │       ├── agent-selector.js
    │       └── agent-progress-tracker.js
    └── pages/ (page-specific scripts)
        ├── support-cards/
        │   └── index.js
        ├── skills/
        │   ├── index.js
        │   └── planner.js
        ├── races/
        │   ├── targets.js
        │   └── calendar.js
        ├── profile/
        │   ├── show.js
        │   └── security-tab.js
        ├── ocr/
        │   ├── results.js
        │   └── skill-list-form.js
        ├── migration/
        │   └── index.js
        ├── mcp/
        │   └── dashboard.js
        ├── import/
        │   └── index.js
        ├── export/
        │   └── index.js
        ├── historical/
        │   └── index.js
        ├── external-data/
        │   └── browse.js
        ├── data-management/
        │   └── index.js
        ├── performance/
        │   └── dashboard.js
        └── training/
            └── predictions.js
```

### 3. PHP Variable Injection Patterns

#### Method A: Data Attributes (Preferred)

```blade
{{-- In Blade file --}}
<div id="dashboard"
     data-user-id="{{ $user->id }}"
     data-config="{{ json_encode($config) }}"
     x-data="dashboardManager()">
</div>

@push('scripts')
    @vite(['resources/js/pages/dashboard.js'])
@endpush
```text

```javascript
// In dashboard.js
export function dashboardManager() {
    return {
        userId: null,
        config: null,

        init() {
            const container = document.getElementById('dashboard');
            this.userId = container.dataset.userId;
            this.config = JSON.parse(container.dataset.config);
        }
    };
}

// Register with Alpine
if (window.Alpine) {
    Alpine.data('dashboardManager', dashboardManager);
}
```

#### Method B: Window Object (Alternative)

```blade
{{-- In Blade file --}}
<script>
    window.viewData = {
        userId: {{ $user->id }},
        config: @json($config)
    };
</script>

<div x-data="dashboardManager()"></div>

@push('scripts')
    @vite(['resources/js/pages/dashboard.js'])
@endpush
```text

```javascript
// In dashboard.js
export function dashboardManager() {
    return {
        userId: window.viewData.userId,
        config: window.viewData.config,

        init() {
            // Use the data
        }
    };
}
```

## Completed Refactorings

### Components (CSS)

| Component | Status | CSS File | Notes |
| ----------- | -------- | ---------- | ------- |
| turn-counter | ✅ Complete | `components/turn-counter.css` | Shimmer animation |
| stat-bar | ✅ Complete | `components/stat-bar.css` | Shimmer + stat icons |
| energy-gauge | ✅ Complete | `components/gauges.css` | Shared with bond-meter |
| bond-meter | ✅ Complete | `components/gauges.css` | Shared with energy-gauge |
| condition-badge | ✅ Complete | `components/condition-badge.css` | Pulse animation |
| grade-badge | ⏳ Pending | `components/grade-badge.css` | - |
| support-card | ⏳ Pending | `components/support-card.css` | - |
| support-card-mini | ⏳ Pending | `components/support-card.css` | Shared |
| character-card | ⏳ Pending | `components/character-card.css` | - |
| skill-card | ⏳ Pending | `components/skill-card.css` | - |
| skill-icon | ⏳ Pending | `components/skill-icon.css` | - |
| keyboard-shortcuts-help | ⏳ Pending | `components/keyboard-shortcuts.css` | - |
| quick-actions | ⏳ Pending | `components/quick-actions.css` | Animation |
| slide-panel | ⏳ Pending | `components/slide-panel.css` | Animation |
| stat-radar-chart | ⏳ Pending | `components/stat-radar-chart.css` | SVG styles |

### Components (JavaScript)

| Component | Status | JS File | Data Injection Method |
| ----------- | -------- | --------- | ---------------------- |
| quick-actions | ⏳ Pending | `components/quick-actions.js` | Alpine data |
| slide-panel | ⏳ Pending | `components/slide-panel.js` | Alpine data |
| line-chart | ⏳ Pending | `components/line-chart.js` | Props via Alpine |
| class-pyramid | ⏳ Pending | `components/class-pyramid.js` | Props via Alpine |
| password-input | ⏳ Pending | `components/password-input.js` | DOM manipulation |
| team-member-selector | ⏳ Pending | `components/team-member-selector.js` | Props via Alpine |
| spirit-burst-gauge | ⏳ Pending | `components/spirit-burst-gauge.js` | Props via Alpine |
| facility-management | ⏳ Pending | `components/facility-management.js` | Props via Alpine |
| ai/workflow-visualization | ⏳ Pending | `components/ai/workflow-visualization.js` | Alpine data |
| ai/tool-usage-indicator | ⏳ Pending | `components/ai/tool-usage-indicator.js` | Alpine data |
| ai/tool-execution-monitor | ⏳ Pending | `components/ai/tool-execution-monitor.js` | Props via Alpine |
| ai/server-status-indicator | ⏳ Pending | `components/ai/server-status-indicator.js` | Alpine data |
| ai/provider-selector | ⏳ Pending | `components/ai/provider-selector.js` | Alpine data |
| ai/performance-metrics | ⏳ Pending | `components/ai/performance-metrics.js` | Props via Alpine |
| ai/agent-selector | ⏳ Pending | `components/ai/agent-selector.js` | Alpine data |
| ai/agent-progress-tracker | ⏳ Pending | `components/ai/agent-progress-tracker.js` | Props via Alpine |

### Pages (JavaScript)

| Page | Status | JS File | Data Injection Method | Notes |
| ------ | -------- | --------- | ---------------------- | ------- |
| support-cards/index | ⏳ Pending | `pages/support-cards/index.js` | Alpine data | Complex API calls |
| skills/index | ⏳ Pending | `pages/skills/index.js` | Data attributes | Admin flag |
| skills/planner | ⏳ Pending | `pages/skills/planner.js` | Alpine data | - |
| races/targets | ⏳ Pending | `pages/races/targets.js` | Alpine data | - |
| races/calendar | ⏳ Pending | `pages/races/calendar.js` | Alpine data | - |
| profile/show | ⏳ Pending | `pages/profile/show.js` | Alpine data | - |
| profile/security-tab | ⏳ Pending | `pages/profile/security-tab.js` | Alpine data | - |
| performance/dashboard | ⏳ Pending | `pages/performance/dashboard.js` | Alpine data | - |
| ocr/results | ⏳ Pending | `pages/ocr/results.js` | DOM manipulation | - |
| ocr/skill-list-form | ⏳ Pending | `pages/ocr/skill-list-form.js` | Data attributes | Dynamic index |
| migration/index | ⏳ Pending | `pages/migration/index.js` | Data attributes | CSRF token |
| mcp/dashboard | ⏳ Pending | `pages/mcp/dashboard.js` | Alpine data | - |
| import/index | ⏳ Pending | `pages/import/index.js` | DOM manipulation | - |
| export/index | ⏳ Pending | `pages/export/index.js` | DOM manipulation | - |
| historical/index | ⏳ Pending | `pages/historical/index.js` | Async function | - |
| external-data/browse | ⏳ Pending | `pages/external-data/browse.js` | Alpine data | - |
| data-management/index | ⏳ Pending | `pages/data-management/index.js` | Alpine data | - |
| training/predictions | ⏳ Pending | `pages/training/predictions.js` | Module import | Already modular |

### Special Cases

| File | Status | Notes |
| ------ | -------- | ------- |
| layouts/guest.blade.php | ⏳ Pending | Theme initialization - keep inline (critical path) |
| layouts/app.blade.php | ⏳ Pending | Theme initialization - keep inline (critical path) |
| test-api.blade.php | ⏳ Pending | Standalone test file - low priority |
| support-cards/deck-builder.blade.php | ⏳ Pending | Uses window.deckBuilderData pattern |
| breadcrumb.blade.php | ⏳ Pending | JSON-LD structured data - keep inline (SEO) |

## Vite Configuration Updates

### Current Configuration

```javascript
input: ["resources/css/app.css", "resources/js/app.js"]
```text

### Required Updates

```javascript
input: [
    // Global
    "resources/css/app.css",
    "resources/js/app.js",

    // Components CSS
    "resources/css/components/turn-counter.css",
    "resources/css/components/stat-bar.css",
    "resources/css/components/gauges.css",
    "resources/css/components/condition-badge.css",
    "resources/css/components/grade-badge.css",
    "resources/css/components/support-card.css",
    "resources/css/components/character-card.css",
    "resources/css/components/skill-card.css",
    "resources/css/components/skill-icon.css",
    "resources/css/components/keyboard-shortcuts.css",
    "resources/css/components/quick-actions.css",
    "resources/css/components/slide-panel.css",
    "resources/css/components/stat-radar-chart.css",

    // Components JS
    "resources/js/components/quick-actions.js",
    "resources/js/components/slide-panel.js",
    "resources/js/components/line-chart.js",
    "resources/js/components/class-pyramid.js",
    "resources/js/components/password-input.js",
    "resources/js/components/team-member-selector.js",
    "resources/js/components/spirit-burst-gauge.js",
    "resources/js/components/facility-management.js",

    // AI Components JS
    "resources/js/components/ai/workflow-visualization.js",
    "resources/js/components/ai/tool-usage-indicator.js",
    "resources/js/components/ai/tool-execution-monitor.js",
    "resources/js/components/ai/server-status-indicator.js",
    "resources/js/components/ai/provider-selector.js",
    "resources/js/components/ai/performance-metrics.js",
    "resources/js/components/ai/agent-selector.js",
    "resources/js/components/ai/agent-progress-tracker.js",

    // Pages JS
    "resources/js/pages/support-cards/index.js",
    "resources/js/pages/skills/index.js",
    "resources/js/pages/skills/planner.js",
    "resources/js/pages/races/targets.js",
    "resources/js/pages/races/calendar.js",
    "resources/js/pages/profile/show.js",
    "resources/js/pages/profile/security-tab.js",
    "resources/js/pages/performance/dashboard.js",
    "resources/js/pages/ocr/results.js",
    "resources/js/pages/ocr/skill-list-form.js",
    "resources/js/pages/migration/index.js",
    "resources/js/pages/mcp/dashboard.js",
    "resources/js/pages/import/index.js",
    "resources/js/pages/export/index.js",
    "resources/js/pages/historical/index.js",
    "resources/js/pages/external-data/browse.js",
    "resources/js/pages/data-management/index.js",
]
```

## Testing Checklist

After refactoring each file:

- [ ] Verify styles render correctly in light mode
- [ ] Verify styles render correctly in dark mode
- [ ] Test JavaScript functionality
- [ ] Check browser console for errors
- [ ] Verify Alpine.js data binding works
- [ ] Test responsive behavior
- [ ] Verify animations work
- [ ] Check accessibility (keyboard navigation, screen readers)

## Build Process

1. Run `npm run build` to compile assets
2. Check `public/build/` for generated files
3. Verify manifest.json includes all new files
4. Test in production mode

## Rollback Plan

If issues occur:

1. Git revert specific commits
2. Restore inline styles/scripts temporarily
3. Debug and fix issues
4. Re-apply refactoring

## Performance Benefits

Expected improvements:

- **Caching**: External files cached by browser
- **Parallel Loading**: Multiple files loaded simultaneously
- **Code Splitting**: Vite automatically splits code
- **Minification**: Better compression of external files
- **Development**: Hot module replacement for faster dev

## Next Steps

1. ✅ Create directory structure
2. ✅ Refactor component CSS (5/17 complete)
3. ⏳ Complete remaining component CSS
4. ⏳ Refactor component JavaScript
5. ⏳ Refactor page JavaScript
6. ⏳ Update Vite configuration
7. ⏳ Test all pages
8. ⏳ Run build and verify production

## Notes

- Keep theme initialization inline in layouts (critical rendering path)
- Keep JSON-LD structured data inline (SEO requirements)
- Use `@once` directive to prevent duplicate style/script loading
- Use `@push('styles')` and `@push('scripts')` for proper stack management
- Prefer data attributes over window object for better encapsulation
