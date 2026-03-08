# Blade Asset Extraction Refactoring

**Document Type**: Implementation Summary  
**Version**: 1.0.0  
**Date**: January 29, 2026  
**Status**: In Progress  
**Related**: AGENTS.md, tech.md, structure.md

## Overview

This document tracks the systematic refactoring of inline CSS and JavaScript from Blade templates into dedicated
external resource files, following Laravel and Vite best practices.

## Objectives

1. **Separation of Concerns**: Extract inline `<style>` and `<script>` blocks into dedicated files
2. **Maintainability**: Centralize styles and scripts for easier updates
3. **Performance**: Enable better caching and code splitting via Vite
4. **Code Quality**: Follow Laravel 12 and modern frontend best practices

## Refactoring Strategy

### Analysis Approach

**Global vs. Specific Assets**:

- **Global Assets**: Common utilities, resets, shared components → `resources/css/app.css` or `resources/js/app.js`
- **View-Specific Assets**: Page-specific logic/styles → `resources/{css|js}/pages/{path}/{file}.{css|js}`
- **Component Assets**: Component-specific styles → `resources/css/components/{component}.css`

### File Structure Convention

```text
Source View: resources/views/pages/dashboard.blade.php
Target CSS:  resources/css/pages/dashboard.css
Target JS:   resources/js/pages/dashboard.js

Source Component: resources/views/components/skill-card.blade.php
Target CSS:       resources/css/components/skill-card.css
```text

### Handling PHP Variables in JavaScript

**Problem**: Blade syntax (e.g., `{{ $user->id }}`) cannot be moved to `.js` files.

**Solutions**:

#### Method A: Data Attributes

```html
<!-- Blade file -->
<div id="dashboard" data-user-id="{{ $user->id }}"></div>

<!-- External JS -->
const userId = document.getElementById('dashboard').dataset.userId;
```text

#### Method B: Window Object

```html
<!-- Blade file -->
<script>
    window.viewData = {
        userId: {{ $user->id }},
        characterId: {{ $character->id }}
    };
</script>

<!-- External JS -->
const userId = window.viewData.userId;
```

## Completed Refactoring

### 1. Support Cards Index Page

**File**: `resources/views/support-cards/index.blade.php`

**Actions**:

- ✅ Extracted `supportCardManager()` Alpine component to `resources/js/pages/support-cards/index.js`
- ✅ Registered component in `resources/js/app.js`
- ✅ Removed inline `<script>` block from Blade file
- ✅ Component now loaded via main app.js bundle (no separate @vite directive needed)

**Files Created**:

- `resources/js/pages/support-cards/index.js`

**Files Modified**:

- `resources/views/support-cards/index.blade.php`
- `resources/js/app.js`

### 2. Component Styles Extraction

#### Grade Badge Component

**File**: `resources/views/components/grade-badge.blade.php`

**Actions**:

- ✅ Extracted grade-specific gradient styles to `resources/css/components/grade-badge.css`
- ✅ Added `@once` + `@push('styles')` + `@vite()` directive
- ✅ Registered in `vite.config.js` input array

**Files Created**:

- `resources/css/components/grade-badge.css`

**Files Modified**:

- `resources/views/components/grade-badge.blade.php`
- `vite.config.js`

#### Skill Card Component

**File**: `resources/views/components/skill-card.blade.php`

**Actions**:

- ✅ Extracted hover transitions and line-clamp utility to `resources/css/components/skill-card.css`
- ✅ Added `@once` + `@push('styles')` + `@vite()` directive
- ✅ Registered in `vite.config.js` input array

**Files Created**:

- `resources/css/components/skill-card.css`

**Files Modified**:

- `resources/views/components/skill-card.blade.php`
- `vite.config.js`

#### Support Card Component

**File**: `resources/views/components/support-card.blade.php`

**Actions**:

- ✅ Extracted gradient backgrounds and rainbow-pulse animation to `resources/css/components/support-card.css`
- ✅ Added `@once` + `@push('styles')` + `@vite()` directive
- ✅ Registered in `vite.config.js` input array

**Files Created**:

- `resources/css/components/support-card.css`

**Files Modified**:

- `resources/views/components/support-card.blade.php`
- `vite.config.js`

### 3. Vite Configuration Updates

**File**: `vite.config.js`

**Actions**:

- ✅ Added component CSS files to `input` array
- ✅ Included existing component styles (gauges, stat-bar, turn-counter)

**Current Input Array**:

```javascript
input: [
    "resources/css/app.css",
    "resources/js/app.js",
    // Component styles
    "resources/css/components/grade-badge.css",
    "resources/css/components/skill-card.css",
    "resources/css/components/support-card.css",
    "resources/css/components/gauges.css",
    "resources/css/components/stat-bar.css",
    "resources/css/components/turn-counter.css",
],
```text

## Remaining Work

### Statistics

- **Inline `<script>` blocks**: 36 files remaining
- **Inline `<style>` blocks**: 5 files remaining (3 completed)

### Priority Files for Refactoring

#### High Priority (Page-Level Scripts)

1. **Skills Management** (`resources/views/skills/index.blade.php`)
   - Large Alpine component: `skillManagement()`
   - Target: `resources/js/pages/skills/index.js`

2. **Profile Management** (`resources/views/profile/show.blade.php`)
   - Alpine component: `profileManager()`
   - Target: `resources/js/pages/profile/show.js`

3. **MCP Dashboard** (`resources/views/mcp/dashboard.blade.php`)
   - Alpine component: `mcpDashboard()`
   - Target: `resources/js/pages/mcp/dashboard.js`

4. **Performance Dashboard** (`resources/views/performance/dashboard.blade.php`)
   - Alpine component: `apmDashboard()`
   - Target: `resources/js/pages/performance/dashboard.js`

5. **External Data Browser** (`resources/views/external-data/browse.blade.php`)
   - Alpine component: `externalDataBrowser()`
   - Target: `resources/js/pages/external-data/browse.js`

#### Medium Priority (Component Scripts)

1. **AI Components** (`resources/views/components/ai/*.blade.php`)
   - Multiple Alpine components for AI features
   - Target: `resources/js/components/ai/*.js`

2. **Analytics Components** (`resources/views/components/analytics/*.blade.php`)
   - Chart.js integrations
   - Target: `resources/js/components/analytics/*.js`

3. **Race Components** (`resources/views/races/*.blade.php`)
   - Race calendar and targets
   - Target: `resources/js/pages/races/*.js`

#### Low Priority (Utility Scripts)

1. **Import/Export Pages** (`resources/views/{import,export}/index.blade.php`)
   - Form handling and validation
   - Target: `resources/js/pages/{import,export}/index.js`

2. **Migration Page** (`resources/views/migration/index.blade.php`)
    - Data migration logic
    - Target: `resources/js/pages/migration/index.js`

### Remaining Component Styles

1. **Character Card** (`resources/views/components/character-card.blade.php`)
   - Gradient backgrounds
   - Target: `resources/css/components/character-card.css`

2. **Support Card Mini** (`resources/views/components/support-card-mini.blade.php`)
   - Scale transform
   - Target: `resources/css/components/support-card-mini.css`

3. **Keyboard Shortcuts Help** (`resources/views/components/keyboard-shortcuts-help.blade.php`)
   - Keyboard key styling
   - Target: `resources/css/components/keyboard-shortcuts-help.css`

4. **Quick Actions** (`resources/views/components/quick-actions.blade.php`)
   - fadeInUp animation
   - Target: `resources/css/components/quick-actions.css`

5. **Slide Panel** (`resources/views/components/slide-panel.blade.php`)
   - slideInLeft animation
   - Target: `resources/css/components/slide-panel.css`

## Implementation Checklist

For each file to be refactored:

### JavaScript Extraction

- [ ] Identify if script is global, page-specific, or component-specific
- [ ] Create appropriate directory structure
- [ ] Extract JavaScript to external file
- [ ] Export function/component from external file
- [ ] Handle PHP variable injection (data attributes or window object)
- [ ] Import and register in `resources/js/app.js` (if Alpine component)
- [ ] Update Blade file to remove inline script
- [ ] Add `@vite()` directive if needed (or rely on app.js bundle)
- [ ] Test functionality in browser

### CSS Extraction

- [ ] Identify if styles are global, page-specific, or component-specific
- [ ] Create appropriate directory structure
- [ ] Extract CSS to external file
- [ ] Update Blade file to remove inline style
- [ ] Add `@once` + `@push('styles')` + `@vite()` directive
- [ ] Register in `vite.config.js` input array
- [ ] Test styling in browser (light and dark modes)

### Build Configuration

- [ ] Update `vite.config.js` input array for new CSS files
- [ ] Run `npm run build` to verify compilation
- [ ] Check for any build errors or warnings
- [ ] Verify asset file names and paths in compiled output

## Best Practices

### Alpine.js Components

1. **Global Registration**: Register all Alpine components in `resources/js/app.js`
2. **Export Pattern**: Use named exports for better tree-shaking

   ```javascript
   export function componentName() { return { ... } }
   ```

1. **Import Pattern**: Import and register in app.js

   ```javascript
   import { componentName } from './path/to/component.js';
   Alpine.data('componentName', componentName);
   ```text

### CSS Organization

1. **Component Styles**: Use `@once` + `@push('styles')` to avoid duplicate loading
2. **Vite Registration**: Always add new CSS files to `vite.config.js` input array
3. **Naming Convention**: Match CSS filename to component name (kebab-case)

### Data Injection

1. **Prefer Data Attributes**: For simple values (IDs, flags, counts)
2. **Use Window Object**: For complex objects or multiple values
3. **Keep Minimal**: Only inject data that cannot be fetched via API

## Testing Strategy

### Manual Testing

1. **Visual Regression**: Compare before/after screenshots
2. **Functionality**: Test all interactive features
3. **Dark Mode**: Verify styles in both light and dark themes
4. **Responsive**: Test on mobile, tablet, and desktop viewports

### Automated Testing

1. **Build Verification**: Ensure `npm run build` succeeds
2. **Asset Loading**: Verify all assets load without 404 errors
3. **Console Errors**: Check browser console for JavaScript errors
4. **Performance**: Monitor bundle sizes and load times

## Performance Considerations

### Bundle Size Optimization

- **Code Splitting**: Vite automatically splits vendor and app code
- **Tree Shaking**: Use named exports for better tree-shaking
- **Lazy Loading**: Consider dynamic imports for large components
- **CSS Purging**: Tailwind automatically purges unused styles

### Caching Strategy

- **Content Hashing**: Vite adds content hashes to filenames
- **Long-term Caching**: Leverage browser caching for versioned assets
- **Service Worker**: Existing SW handles offline caching

## Migration Notes

### Breaking Changes

None expected. All refactoring maintains existing functionality.

### Rollback Plan

If issues arise:

1. Revert Blade file changes
2. Restore inline scripts/styles
3. Remove external files
4. Revert `vite.config.js` changes
5. Run `npm run build`

### Deployment Considerations

1. **Build Assets**: Run `npm run build` before deployment
2. **Cache Clearing**: Clear CDN/browser caches after deployment
3. **Gradual Rollout**: Consider feature flags for large changes
4. **Monitoring**: Watch for JavaScript errors in production logs

## Success Metrics

- ✅ All inline scripts extracted to external files
- ✅ All inline styles extracted to external files
- ✅ No JavaScript console errors
- ✅ No visual regressions
- ✅ Build process completes successfully
- ✅ Bundle sizes within acceptable limits (<500KB per chunk)
- ✅ Page load times maintained or improved

## Next Steps

1. Continue with high-priority page-level scripts
2. Extract remaining component styles
3. Update documentation as needed
4. Run full test suite
5. Deploy to staging for QA review

## References

- [Laravel Vite Documentation](https://laravel.com/docs/12.x/vite)
- [Alpine.js Component Registration](https://alpinejs.dev/globals/alpine-data)
- [Tailwind CSS v4 Documentation](https://tailwindcss.com/docs)
- Project Guidelines: `AGENTS.md`, `tech.md`, `structure.md`

---

**Last Updated**: January 29, 2026  
**Next Review**: After completing high-priority scripts
