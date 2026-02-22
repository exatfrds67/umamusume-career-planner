# Sidebar Minimize/Collapse Implementation Plan

**Document Version**: 1.0.0  
**Date**: February 8, 2026  
**Status**: Planning Phase  
**Related Documents**: [component-inventory.md](../design/component-inventory.md),
[000_WIREFRAMES_INDEX.md](../01-wireframes/000_WIREFRAMES_INDEX.md)

---

## Overview

This document outlines the implementation plan for adding a sidebar minimize/collapse feature to the Umamusume Career
Planner application. The feature will allow users to toggle between a full-width sidebar and a minimized icon-only
sidebar on desktop screens, improving screen real estate management.

---

## Goals

1. **User Control**: Allow users to minimize/expand the sidebar on desktop (≥1024px)
2. **State Persistence**: Remember user preference across sessions using localStorage
3. **Smooth Transitions**: Provide smooth animations for collapse/expand actions
4. **Accessibility**: Maintain WCAG 2.2 AA compliance with keyboard navigation and screen reader support
5. **Responsive**: Maintain existing mobile behavior (bottom nav) and tablet behavior (toggle sidebar)

---

## Current State Analysis

### Existing Sidebar Structure

**Location**: `resources/views/components/app/sidebar.blade.php`

**Current Behavior**:

- **Mobile (<1024px)**: Hidden by default, shown via overlay when `sidebarOpen` is true
- **Desktop (≥1024px)**: Fixed sidebar at 288px width (w-72 = 18rem)
- **State Management**: Alpine.js `sidebarOpen` boolean in `app.blade.php`

**Key Classes**:

```blade
<!-- Desktop Sidebar -->
<div class="hidden lg:fixed lg:inset-y-0 lg:left-0 lg:z-50 lg:flex lg:w-72 lg:flex-col ...">
```text

**Main Content Offset**:

```blade
<!-- Main Column -->
<div class="lg:pl-72 flex flex-col min-h-screen ...">
```

---

## Design Specifications

### Visual States

#### 1. Expanded State (Default)

- **Width**: 288px (w-72)
- **Content**: Full navigation with icons + labels
- **Logo**: Full logo with text
- **Collapsible Groups**: Expandable with chevron icons

#### 2. Minimized State

- **Width**: 80px (w-20)
- **Content**: Icons only, labels hidden
- **Logo**: Icon only (no text)
- **Collapsible Groups**: Hidden, show on hover via tooltip/popover
- **Hover Behavior**: Show tooltip with label on icon hover

### Toggle Button

**Position**: Top-right corner of sidebar (below logo section)
**Icon**:

- Expanded: `ChevronDoubleLeftIcon` (collapse)
- Minimized: `ChevronDoubleRightIcon` (expand)
**Accessibility**:
- `aria-label`: "Minimize sidebar" / "Expand sidebar"
- `aria-expanded`: true / false
- Keyboard shortcut: `Alt + B` (B for "Bar")

---

## Technical Implementation

### Phase 1: State Management (Alpine.js Store)

**File**: `resources/js/stores/sidebar.js` (new file)

```javascript
// Alpine.js store for sidebar state
export default {
    minimized: localStorage.getItem('sidebar-minimized') === 'true',
    
    toggle() {
        this.minimized = !this.minimized;
        localStorage.setItem('sidebar-minimized', this.minimized);
        
        // Dispatch event for other components
        window.dispatchEvent(new CustomEvent('sidebar-toggled', {
            detail: { minimized: this.minimized }
        }));
    },
    
    expand() {
        this.minimized = false;
        localStorage.setItem('sidebar-minimized', false);
    },
    
    minimize() {
        this.minimized = true;
        localStorage.setItem('sidebar-minimized', true);
    }
};
```text

**Registration**: `resources/js/app.js`

```javascript
import sidebarStore from './stores/sidebar';

Alpine.store('sidebar', sidebarStore);
```

### Phase 2: Sidebar Component Updates

**File**: `resources/views/components/app/sidebar.blade.php`

**Changes Required**:

1. **Add dynamic width classes**:

```blade
<div class="flex grow flex-col gap-y-5 overflow-y-auto bg-white dark:bg-gray-800 px-6 pb-4"
     :class="$store.sidebar.minimized ? 'items-center px-2' : 'px-6'"
     x-data="{ ... }">
```text

1. **Add toggle button** (after logo section):

```blade
<!-- Logo Section -->
<div class="flex h-16 shrink-0 items-center gap-3 border-b border-gray-200 dark:border-gray-700"
     :class="$store.sidebar.minimized ? 'justify-center' : 'justify-between'">
    <!-- Logo -->
    <div class="flex items-center gap-3" :class="$store.sidebar.minimized ? 'flex-col' : ''">
        <img src="/images/app_logo/uma_musume_race_planner_logo_128.png"
            alt="{{ config('app.name') }} logo" 
            class="h-10 w-10 shrink-0">
        <span x-show="!$store.sidebar.minimized" 
              x-transition
              class="text-base font-bold text-primary-600 dark:text-primary-400 leading-tight">
            Umamusume<br>Career Planner
        </span>
    </div>
    
    <!-- Toggle Button (Desktop Only) -->
    <button @click="$store.sidebar.toggle()"
            type="button"
            class="hidden lg:flex -mr-2 p-2 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            :aria-label="$store.sidebar.minimized ? 'Expand sidebar' : 'Minimize sidebar'"
            :aria-expanded="!$store.sidebar.minimized">
        <!-- Chevron Double Left (Minimize) -->
        <svg x-show="!$store.sidebar.minimized" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m18.75 4.5-7.5 7.5 7.5 7.5m-6-15L5.25 12l7.5 7.5" />
        </svg>
        <!-- Chevron Double Right (Expand) -->
        <svg x-show="$store.sidebar.minimized" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m5.25 4.5 7.5 7.5-7.5 7.5m6-15 7.5 7.5-7.5 7.5" />
        </svg>
    </button>
</div>
```

1. **Update navigation items** (show/hide labels):

```blade
<!-- Example: Dashboard Link -->
<li>
    <a href="{{ route('dashboard') }}"
       class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 ..."
       :class="$store.sidebar.minimized ? 'justify-center' : ''"
       :title="$store.sidebar.minimized ? 'Dashboard' : ''">
        <svg class="h-6 w-6 shrink-0" ...>...</svg>
        <span x-show="!$store.sidebar.minimized" x-transition>Dashboard</span>
    </a>
</li>
```text

1. **Handle collapsible groups** (hide when minimized):

```blade
<!-- Data Management Group -->
<li x-show="!$store.sidebar.minimized" x-transition>
    <button @click="dataOpen = !dataOpen" ...>
        <!-- Group content -->
    </button>
    <ul x-show="dataOpen" x-collapse>
        <!-- Submenu items -->
    </ul>
</li>

<!-- Minimized: Show icon with tooltip -->
<li x-show="$store.sidebar.minimized" x-transition>
    <div x-data="{ tooltip: false }" class="relative">
        <button @mouseenter="tooltip = true" 
                @mouseleave="tooltip = false"
                class="group flex justify-center rounded-md p-2 ...">
            <svg class="h-6 w-6 shrink-0" ...>...</svg>
        </button>
        <!-- Tooltip -->
        <div x-show="tooltip" 
             x-transition
             class="absolute left-full ml-2 top-0 z-50 bg-gray-900 text-white text-sm px-3 py-2 rounded-md whitespace-nowrap">
            Data Management
        </div>
    </div>
</li>
```

### Phase 3: Layout Component Updates

**File**: `resources/views/layouts/app.blade.php`

**Changes Required**:

1. **Update desktop sidebar width**:

```blade
<!-- Desktop Sidebar -->
<div class="hidden lg:fixed lg:inset-y-0 lg:left-0 lg:z-50 lg:flex lg:flex-col lg:border-r lg:border-gray-200 dark:lg:border-gray-700 lg:bg-white dark:lg:bg-gray-800"
     :class="$store.sidebar.minimized ? 'lg:w-20' : 'lg:w-72'"
     x-transition:all.duration.300ms>
    <x-app.sidebar />
</div>
```text

1. **Update main content offset**:

```blade
<!-- Main Column -->
<div class="flex flex-col min-h-screen transition-all duration-300 relative z-10"
     :class="$store.sidebar.minimized ? 'lg:pl-20' : 'lg:pl-72'">
    <!-- Content -->
</div>
```

---

### Phase 4: Tooltip Component (for minimized state)

**File**: `resources/views/components/sidebar-tooltip.blade.php` (new component)

```blade
@props(['text', 'position' => 'right'])

<div x-data="{ show: false }" class="relative inline-block">
    <div @mouseenter="show = true" 
         @mouseleave="show = false"
         @focus="show = true"
         @blur="show = false">
        {{ $slot }}
    </div>
    
    <div x-show="show"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-x-2"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 translate-x-2"
         @class([
             'absolute z-50 px-3 py-2 text-sm font-medium text-white bg-gray-900 dark:bg-gray-700 rounded-lg shadow-lg whitespace-nowrap pointer-events-none',
             'left-full ml-2 top-1/2 -translate-y-1/2' => $position === 'right',
             'right-full mr-2 top-1/2 -translate-y-1/2' => $position === 'left',
         ])
         role="tooltip">
        {{ $text }}
        <!-- Arrow -->
        <div @class([
            'absolute w-2 h-2 bg-gray-900 dark:bg-gray-700 rotate-45',
            '-left-1 top-1/2 -translate-y-1/2' => $position === 'right',
            '-right-1 top-1/2 -translate-y-1/2' => $position === 'left',
        ])></div>
    </div>
</div>
```text

**Usage Example**:

```blade
<x-sidebar-tooltip text="Dashboard">
    <a href="{{ route('dashboard') }}" class="...">
        <svg class="h-6 w-6">...</svg>
    </a>
</x-sidebar-tooltip>
```

---

### Phase 5: Keyboard Shortcut Integration

**File**: `resources/js/app.js`

```javascript
// Register keyboard shortcut for sidebar toggle
document.addEventListener('keydown', (e) => {
    // Alt + B to toggle sidebar
    if (e.altKey && e.key === 'b') {
        e.preventDefault();
        Alpine.store('sidebar').toggle();
    }
});
```text

**Update Accessibility Settings Panel**:
Add keyboard shortcut documentation to the help modal.

---

## Accessibility Considerations

### WCAG 2.2 AA Compliance

1. **Keyboard Navigation**:
   - Toggle button must be keyboard accessible (Tab, Enter/Space)
   - Keyboard shortcut: `Alt + B`
   - Focus indicators visible (3:1 contrast)

2. **Screen Reader Support**:
   - `aria-label` on toggle button
   - `aria-expanded` state on toggle button
   - Announce state changes via `aria-live` region

3. **Focus Management**:
   - When sidebar minimizes, maintain focus on toggle button
   - When expanding, restore focus to toggle button
   - Tooltips should not trap focus

4. **Color Independence**:
   - Icons must be recognizable without color
   - Hover states must have sufficient contrast

### Implementation

```blade
<!-- Toggle Button with Full Accessibility -->
<button @click="$store.sidebar.toggle()"
        type="button"
        class="..."
        :aria-label="$store.sidebar.minimized ? 'Expand sidebar' : 'Minimize sidebar'"
        :aria-expanded="!$store.sidebar.minimized"
        aria-controls="sidebar-navigation"
        @keydown.enter.prevent="$store.sidebar.toggle()"
        @keydown.space.prevent="$store.sidebar.toggle()">
    <!-- Icons -->
</button>

<!-- Announce state changes -->
<div role="status" 
     aria-live="polite" 
     aria-atomic="true" 
     class="sr-only">
    <span x-text="$store.sidebar.minimized ? 'Sidebar minimized' : 'Sidebar expanded'"></span>
</div>
```

---

## Responsive Behavior

### Breakpoint Strategy

| Breakpoint              | Behavior                                  |
| ----------------------- | ----------------------------------------- |
| **Mobile (<640px)**     | Bottom nav bar (no sidebar)               |
| **Tablet (640-1024px)** | Overlay sidebar (existing behavior)       |
| **Desktop (≥1024px)**   | Fixed sidebar with minimize/expand toggle |

### CSS Transitions

```css
/* Smooth transitions for sidebar and content */
.sidebar-transition {
    transition: width 300ms cubic-bezier(0.4, 0, 0.2, 1);
}

.content-transition {
    transition: padding-left 300ms cubic-bezier(0.4, 0, 0.2, 1);
}
```text

---

## Testing Requirements

### Unit Tests (Pest)

**File**: `tests/Feature/SidebarMinimizeTest.php`

```php
it('persists sidebar state in localStorage', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/dashboard')
                ->assertVisible('.lg\\:w-72') // Expanded by default
                ->click('[aria-label="Minimize sidebar"]')
                ->assertVisible('.lg\\:w-20') // Minimized
                ->refresh()
                ->assertVisible('.lg\\:w-20'); // State persisted
    });
});

it('toggles sidebar with keyboard shortcut', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/dashboard')
                ->keys('body', ['{alt}', 'b'])
                ->assertVisible('.lg\\:w-20')
                ->keys('body', ['{alt}', 'b'])
                ->assertVisible('.lg\\:w-72');
    });
});
```

### Accessibility Tests (Playwright)

**File**: `tests/e2e/accessibility/sidebar-minimize.spec.js`

```javascript
import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

test.describe('Sidebar Minimize Accessibility', () => {
    test('should not have accessibility violations', async ({ page }) => {
        await page.goto('/dashboard');
        
        const accessibilityScanResults = await new AxeBuilder({ page }).analyze();
        expect(accessibilityScanResults.violations).toEqual([]);
        
        // Click minimize button
        await page.click('[aria-label="Minimize sidebar"]');
        
        // Scan again in minimized state
        const minimizedScanResults = await new AxeBuilder({ page }).analyze();
        expect(minimizedScanResults.violations).toEqual([]);
    });
    
    test('toggle button has correct ARIA attributes', async ({ page }) => {
        await page.goto('/dashboard');
        
        const toggleButton = page.locator('[aria-label="Minimize sidebar"]');
        await expect(toggleButton).toHaveAttribute('aria-expanded', 'true');
        
        await toggleButton.click();
        await expect(toggleButton).toHaveAttribute('aria-expanded', 'false');
        await expect(toggleButton).toHaveAttribute('aria-label', 'Expand sidebar');
    });
    
    test('keyboard shortcut works', async ({ page }) => {
        await page.goto('/dashboard');
        
        // Press Alt+B
        await page.keyboard.press('Alt+b');
        
        // Verify sidebar is minimized
        const sidebar = page.locator('.lg\\:w-20');
        await expect(sidebar).toBeVisible();
        
        // Press Alt+B again
        await page.keyboard.press('Alt+b');
        
        // Verify sidebar is expanded
        const expandedSidebar = page.locator('.lg\\:w-72');
        await expect(expandedSidebar).toBeVisible();
    });
    
    test('tooltips appear on hover in minimized state', async ({ page }) => {
        await page.goto('/dashboard');
        
        // Minimize sidebar
        await page.click('[aria-label="Minimize sidebar"]');
        
        // Hover over Dashboard icon
        const dashboardIcon = page.locator('a[href*="dashboard"]').first();
        await dashboardIcon.hover();
        
        // Verify tooltip appears
        const tooltip = page.locator('[role="tooltip"]:has-text("Dashboard")');
        await expect(tooltip).toBeVisible();
    });
});
```text

### Visual Regression Tests

**File**: `tests/e2e/visual/sidebar-states.spec.js`

```javascript
import { test, expect } from '@playwright/test';

test.describe('Sidebar Visual States', () => {
    test('expanded state matches snapshot', async ({ page }) => {
        await page.goto('/dashboard');
        await expect(page).toHaveScreenshot('sidebar-expanded.png');
    });
    
    test('minimized state matches snapshot', async ({ page }) => {
        await page.goto('/dashboard');
        await page.click('[aria-label="Minimize sidebar"]');
        await page.waitForTimeout(300); // Wait for transition
        await expect(page).toHaveScreenshot('sidebar-minimized.png');
    });
    
    test('transition animation is smooth', async ({ page }) => {
        await page.goto('/dashboard');
        
        // Record video of transition
        await page.video();
        
        await page.click('[aria-label="Minimize sidebar"]');
        await page.waitForTimeout(300);
        
        await page.click('[aria-label="Expand sidebar"]');
        await page.waitForTimeout(300);
    });
});
```

---

## Performance Considerations

### Optimization Strategies

1. **CSS Transitions**: Use GPU-accelerated properties (transform, opacity)
2. **Debouncing**: Prevent rapid toggle clicks
3. **Lazy Loading**: Load tooltip content only when needed
4. **LocalStorage**: Minimize writes (only on state change)

### Performance Metrics

| Metric               | Target | Measurement                      |
| -------------------- | ------ | -------------------------------- |
| Toggle Response Time | <100ms | Time from click to visual change |
| Transition Duration  | 300ms  | CSS transition duration          |
| LocalStorage Write   | <10ms  | Time to persist state            |
| Tooltip Render       | <50ms  | Time to show tooltip on hover    |

---

## Implementation Checklist

### Phase 1: State Management

- [ ] Create `resources/js/stores/sidebar.js`
- [ ] Register store in `resources/js/app.js`
- [ ] Test localStorage persistence
- [ ] Test state toggle functionality

### Phase 2: Sidebar Component

- [ ] Add toggle button to sidebar
- [ ] Update logo section for minimized state
- [ ] Update navigation items with conditional labels
- [ ] Handle collapsible groups in minimized state
- [ ] Add tooltips for minimized icons

### Phase 3: Layout Updates

- [ ] Update desktop sidebar width classes
- [ ] Update main content offset classes
- [ ] Add CSS transitions
- [ ] Test responsive behavior

### Phase 4: Tooltip Component

- [ ] Create `resources/views/components/sidebar-tooltip.blade.php`
- [ ] Style tooltip with arrow
- [ ] Test tooltip positioning
- [ ] Test tooltip accessibility

### Phase 5: Keyboard Shortcuts

- [ ] Register `Alt + B` keyboard shortcut
- [ ] Update accessibility settings panel
- [ ] Test keyboard navigation
- [ ] Test screen reader announcements

### Phase 6: Testing

- [ ] Write Pest unit tests
- [ ] Write Playwright accessibility tests
- [ ] Write visual regression tests
- [ ] Manual testing on all breakpoints
- [ ] Cross-browser testing (Chrome, Firefox, Safari, Edge)

### Phase 7: Documentation

- [ ] Update user manual with sidebar minimize feature
- [ ] Update keyboard shortcuts documentation
- [ ] Add inline help tooltips
- [ ] Create demo video/GIF

---

## Rollout Strategy

### Development Environment

1. Implement on feature branch: `feature/sidebar-minimize`
2. Test locally with all breakpoints
3. Run automated test suite
4. Manual QA testing

### Staging Environment

1. Deploy to staging
2. Conduct user acceptance testing (UAT)
3. Gather feedback from team
4. Performance testing

### Production Environment

1. Deploy during low-traffic period
2. Monitor error logs
3. Track user adoption metrics
4. Gather user feedback

---

## Success Metrics

### User Adoption

- **Target**: 40% of desktop users use minimize feature within 30 days
- **Measurement**: Track localStorage `sidebar-minimized` value

### Performance

- **Target**: No performance degradation
- **Measurement**: Lighthouse scores, Core Web Vitals

### Accessibility

- **Target**: 100% WCAG 2.2 AA compliance
- **Measurement**: Automated axe-core scans, manual testing

### User Satisfaction

- **Target**: Positive feedback from users
- **Measurement**: User surveys, support tickets

---

## Future Enhancements

### Phase 2 Features (Post-Launch)

1. **Hover Expand**: Temporarily expand sidebar on hover when minimized
2. **Custom Width**: Allow users to set custom sidebar width
3. **Pinned Items**: Pin frequently used items to top of minimized sidebar
4. **Keyboard Navigation**: Arrow keys to navigate between items
5. **Search**: Quick search in minimized state
6. **Themes**: Different icon styles for minimized state

---

## Related Documentation

- [Component Inventory](../design/component-inventory.md)
- [Wireframes Index](../01-wireframes/000_WIREFRAMES_INDEX.md)
- [Focus Management](../accessibility/focus-management.md)
- [Accessibility System](../accessibility/accessibility-system.md)
- [AGENTS.md](../../AGENTS.md) - AI Agent Development Guidelines

---

## Document Control

**Version History**:

| Version | Date       | Author           | Changes                     |
| ------- | ---------- | ---------------- | --------------------------- |
| 1.0.0   | 2026-02-08 | Development Team | Initial implementation plan |

**Approval**:

- [ ] Product Manager
- [ ] Lead Developer
- [ ] UX Designer
- [ ] Accessibility Specialist

---

*This implementation plan follows the project's dual storage architecture, Laravel 12 conventions, and WCAG 2.2 AA
accessibility standards as outlined in AGENTS.md.*
