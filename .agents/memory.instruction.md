---
applyTo: '**'
---

# Coding Preferences

- Follow Laravel 12 coding standards and best practices
- Use PHP 8.3+ strict types and type declarations
- Implement WCAG 2.2 AA accessibility compliance
- Prefer Pest testing framework over PHPUnit
- Use Laravel Pint for code formatting

## Project Architecture

- **Framework**: Laravel 12 (Released February 24, 2025)
- **Frontend**: Tailwind CSS v4 (Released January 22, 2025)
- **Database**: MySQL 8.0+ with Redis 7.0+ caching via WSL
- **AI Integration**: Hybrid Ollama local + AWS Bedrock cloud
- **External APIs**: umapyoi.net (primary), UmamusumeDB.com (verification pending)
- **Testing**: Pest v3 with PHPUnit v11
- **Code Quality**: Laravel Pint v1
- **Requirements**: 59 total requirements, 15+ database tables, 80%+ test coverage
- **Timeline**: 22-28 weeks development (6 phases)
- **Document Numbering**: 000-010, 017 (011-016 reserved/unused)

## Solutions Repository

- **Structured response mocking (NeuronAI)**: When mocking `AIProviderInterface::structured`, return an `AssistantMessage` containing JSON that matches the target schema/class instead of returning the DTO directly. The agent pipeline expects a `Message` instance; it will deserialize JSON into the response class via `processResponse()`.

## Service Worker Caching Issue

- **Problem**: Navigation to dashboard was redirected/cached, requiring hard refresh (Ctrl+Shift+R) due to missing or misconfigured service worker, especially aggressive in Chrome
- **Root Cause**: Service worker was being registered in the app but `public/sw.js` didn't exist, causing 404 errors and browser caching issues. Chrome was caching old broken service workers.
- **Solution**:
  1. Created `public/sw.js` with network-first strategy for navigation requests
  2. Added automatic old service worker cleanup in `resources/js/app.js`
  3. Created `SetCacheHeaders` middleware to prevent HTML page caching
  4. Added `updateViaCache: "none"` to service worker registration
- **Files Modified**:
  - Created: `public/sw.js`, `app/Http/Middleware/SetCacheHeaders.php`
  - Modified: `resources/js/app.js`, `bootstrap/app.php`
- **Strategy**: Network-first for HTML (always fresh when online), cache-first for static assets (images, fonts), automatic cache cleanup, no-cache headers for HTML
- **Result**: On next page load, all old service workers are unregistered, caches cleared, and fresh service worker installed

## Alpine.js Script Timing Issue

- **Problem**: Profile page failed to load because `profileManager()` Alpine.js function was defined inside `@section('content')`, making it unavailable when Alpine.js tried to initialize `x-data="profileManager()"` on line 6
- **Solution**: Move the `<script>` tag from inside `@section('content')` to use `@push('scripts')` so it executes after Alpine.js loads
- **File Fixed**: [resources/views/profile/show.blade.php](resources/views/profile/show.blade.php)
- **Result**: Profile page now loads correctly, all 18 profile tests pass

## Dark Mode Flash/Mismatch on Page Load

- **Problem**: Page loads with light mode briefly, then switches to dark mode (or vice versa), requiring page refresh or Ctrl+Shift+R to resolve
- **Root Causes**:
  1. Theme detection and application was happening in JavaScript (app.js) which runs AFTER the page renders
  2. TWO competing theme systems (ThemeSystem.js and settings.js) causing race conditions and conflicts
  3. Theme toggle button wasn't properly synchronized across both systems
- **Solution**:
  1. Added synchronous inline `<script>` in `<head>` that runs BEFORE CSS loads:
     - Checks localStorage for user's theme preference
     - Falls back to system preference via `prefers-color-scheme` media query
     - Immediately applies `dark` class to `<html>` element
     - All CSS scoped to `html.dark` selector, so theme applies instantly
  2. Refactored ThemeSystem.js to be the single source of truth:
     - Removed `getStoredTheme()` redundancy
     - Simplified `applyTheme()` to check current state before DOM manipulation
     - Added `updateToggleButtonState()` to sync button state
     - Removed duplicate settings page update logic from keyboard shortcut
  3. Modified settings.js to delegate to ThemeSystem:
     - Theme button handlers now call `window.themeSystem.applyTheme()`
     - Kept fallback `applyTheme()` for when ThemeSystem unavailable
     - Added `updateThemeButtonUI()` helper for consistent button styling
     - Added initial sync to reflect current theme on page load
- **Files Modified**:
  - Modified: `resources/views/layouts/app.blade.php` (added inline theme script)
  - Modified: `resources/views/layouts/guest.blade.php` (added inline theme script)
  - Modified: `resources/js/core/ThemeSystem.js` (consolidated to single source of truth)
  - Modified: `resources/js/settings.js` (delegate to ThemeSystem, remove duplicates)
- **Why This Works**:
  - Inline script applies theme BEFORE CSS loads → no flash
  - Single ThemeSystem is source of truth → no race conditions
  - All UI components sync to same system → no mismatch
  - Proper initialization order ensures button state reflects actual theme
- **Result**: No more light/dark mode mismatch on page load - theme applies immediately and consistently across all components!

## Documentation Consistency

- **Problem**: Mixed date formats and inconsistent cross-references across documentation
- **Solution**: Standardize all dates to "Month DD, YYYY" format, add cross-references between planning documents
- **Result**: Achieved 10/10 (100%) documentation consistency

## Product Requirement Documents

- **Problem**: PRD folder missing from docs
- **Solution**: Recreated docs/prds with PRD-001..007 (module-level product requirements) and added to 000_DOCUMENT_INDEX with dependency links
- **Result**: PRDs visible in docs/prds and referenced in catalog/dependency matrix

## API Verification Status

- **Problem**: Inconsistent terminology for API verification status
- **Solution**: Standardize to "verification pending" for unverified APIs, "verified active" with date for verified
- **Avoid**: Don't use "requires verification" - use "verification pending" instead

## Requirements Count Standardization

- **Problem**: Diagram documentation referenced "60 comprehensive requirements" or "48 requirements" inconsistently
- **Solution**: Standardized all documentation to reference "59 requirements" consistently
- **Files Updated**: data-flow-diagram.md, decision-tree-flow-diagrams.md, entity-relationship-diagram.md, system-process-flow-diagrams.md, user-workflow-diagrams.md
- **Result**: All diagram documentation now aligned with main system documentation
