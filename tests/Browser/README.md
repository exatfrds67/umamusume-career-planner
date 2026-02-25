# Browser E2E Tests

This directory contains end-to-end (E2E) browser tests using Pest 4's
browser testing capabilities powered by Playwright.

## Prerequisites

Before running browser tests, you need to install the required dependencies:

### 1. Install Pest Browser Plugin

```bash
composer require pestphp/pest-plugin-browser:^4.0 --dev
```

### 2. Install Playwright

```bash
npm install playwright@latest
npx playwright install
```

This will install Playwright and download the necessary browser
binaries (Chromium, Firefox, WebKit).

## Running Browser Tests

Once the prerequisites are installed, you can run browser tests using:

```bash
# Run all browser tests
php artisan test tests/Browser/

# Run a specific browser test file
php artisan test tests/Browser/CriticalAlertHandlingTest.php

# Run with specific filter
php artisan test --filter=CriticalAlertHandlingTest

# Run browser tests with specific group
php artisan test --group=browser
php artisan test --group=critical-alerts
```

## Available Browser Tests

### CriticalAlertHandlingTest.php

Tests the critical alert system including:

- Triggering various critical situations (stamina crisis, energy critical, SP shortage)
- Verifying alerts appear with proper styling and content
- Dismissing alerts
- Verifying dismissal persists across page reloads
- Testing keyboard navigation
- Testing alert badges and animations

**Task**: 7.2.2 Test critical alert handling  
**Requirements**: 3.4, 3.7

### AdvisoryWorkflowTest.php

Tests the complete advisory workflow:

- Navigate to training screen
- View recommendations
- Expand recommendation details
- Apply recommendations
- Verify outcomes

**Task**: 7.2.1 Test complete advisory workflow  
**Requirements**: 3.1, 3.7

### AdvisoryPanelInteractivityTest.php

Tests Alpine.js interactivity for the advisory panel:

- Expand/collapse functionality
- Dismissal functionality
- Keyboard navigation
- Accessibility compliance

**Requirements**: 3.7, WCAG 2.2 AA

### ComprehensiveTraversalTest.php (NEW)

**Automated comprehensive application traversal** testing that
systematically visits ALL routes in the application like a thorough
manual tester.

**What it does:**

- 🔍 **Auto-discovers** all routes from `routes/web.php` (100+ routes)
- 🚦 **Categorizes** routes: Public, Authenticated, Admin
- ✅ **Visits** every page and checks for errors
- 📊 **Generates** detailed HTML and JSON reports with:
  - Coverage statistics (how many routes visited)
  - Error details (JavaScript errors, HTTP errors)
  - Performance metrics (load times, slowest pages)
  - Pass/fail status for each route

**Running the test:**

```bash
# Using Artisan command (recommended)
php artisan test:traversal

# Open report automatically after completion
php artisan test:traversal --open-report

# Test specific scope
php artisan test:traversal --scope=public
php artisan test:traversal --scope=auth
php artisan test:traversal --scope=admin

# Run with visible browser (for debugging)
php artisan test:traversal --headless=false

# Using PHPArtisan test directly
php artisan test tests/Browser/ComprehensiveTraversalTest.php --group=traversal
```

**Generated Reports:**

Reports are saved to `storage/app/test-reports/`:

- `traversal-{timestamp}.html` - Visual HTML report with statistics and tables
- `traversal-{timestamp}.json` - Machine-readable JSON for CI/CD
- `traversal-latest.html` - Always points to the most recent report

**Performance:**

- Typical runtime: 5-10 minutes (depending on number of routes)
- Tests ~100+ routes across all authentication levels
- Tracks load time for each page
- Fails if average load time exceeds 5 seconds

**Use cases:**

- ✅ Smoke testing before deployment
- ✅ Regression detection after major changes
- ✅ Performance benchmarking across all pages
- ✅ Verifying all routes are accessible
- ✅ Finding broken routes or JavaScript errors

**Groups:** `@browser`, `@traversal`, `@slow`

### TabNavigationTest.php (NEW)

**Comprehensive tab interface testing** across all application
components with keyboard navigation and accessibility verification.

**What it tests:**

- 🔄 **Tab Switching**: Clicks through all tab interfaces in the application
- ⌨️ **Keyboard Navigation**: Arrow keys, Tab, Enter key support
- ♿ **ARIA Attributes**: Verifies `aria-selected`, `aria-controls`,
  `role="tab"`, `role="tabpanel"`
- 🎯 **Content Visibility**: Ensures tab panels show/hide correctly

**Test Coverage:**

- Character detail tabs (Overview, Stats, Skills, History)
- Training facility tabs (Speed, Stamina, Power, Guts, Wit, Rest)
- Settings sections (Profile, Accessibility, Notifications, Privacy)
- Dashboard card sections
- Report view tabs (Statistics, Progress, Skills, Races)
- External data categories (Support Cards, Skills, Characters)
- Tab state management across page refreshes

**Running the tests:**

```bash
# All tab navigation tests
php artisan test tests/Browser/TabNavigationTest.php

# Specific groups
php artisan test --group=tabs
php artisan test --group=keyboard
php artisan test --group=accessibility
```

**Groups:** `@browser`, `@tabs`, `@accessibility`, `@keyboard`

### UserJourneyTest.php (NEW)

**End-to-end user workflow simulation** covering complete user
lifecycles from guest to advanced features.

**What it tests:**

- 👤 **Guest to Registered User**: Complete registration flow, guest browsing
- 🎭 **Character Creation**: Full workflow from dashboard to character details
- 🏋️ **Training Sessions**: Multi-turn training, skill acquisition, stat progression
- 🏁 **Race Participation**: Race entry, confirmation, results
- 📤 **Data Export/Import**: Character data export (JSON), import validation
- ⚙️ **Settings Configuration**: Profile updates, accessibility preferences
- 🔄 **Complete E2E Journey**: Registration → Character → Training → Export
- ⚠️ **Error Recovery**: Validation errors, form correction, graceful recovery

**Running the tests:**

```bash
# All user journey tests
php artisan test tests/Browser/UserJourneyTest.php

# Specific workflows
php artisan test --filter="registration flow"
php artisan test --filter="character creation"
php artisan test --filter="training session"

# Integration tests only
php artisan test --group=integration
php artisan test --group=e2e
```

**Groups:** `@browser`, `@user-journey`, `@integration`, `@e2e`

**Typical Runtime:** 3-5 minutes

### SmokeTestSuite.php (NEW)

**Quick critical path verification** designed to run in 2-3 minutes before deployments.

**What it tests:**

- 🚀 **Critical Page Loads**: Homepage, login, dashboard
- 🔐 **Authentication**: Login, logout, redirect protection
- 📦 **Character CRUD**: Create, read, update, delete operations
- 🎓 **Training Basics**: Access training screen, execute training actions
- 🗄️ **Database Connectivity**: Read/write verification
- 🌐 **API Endpoints**: External data API responses
- ⚡ **JavaScript**: Livewire and Alpine.js initialization
- ✅ **Form Validation**: Empty form submissions, invalid data
- 🧭 **Navigation**: Main navigation links
- 💾 **Session Management**: Session persistence across pages
- 🚨 **Error Handling**: 404 pages, unauthorized access
- 📱 **Asset Loading**: CSS and JavaScript loading
- 📲 **Mobile Viewport**: Basic mobile responsiveness
- ⏱️ **Performance**: Page load time checks (< 3 seconds)

**Running the tests:**

```bash
# All smoke tests (fastest way to verify system health)
php artisan test tests/Browser/SmokeTestSuite.php --group=smoke

# Critical tests only
php artisan test --group=smoke --group=critical

# Before deployment quick check
php artisan test tests/Browser/SmokeTestSuite.php
```

**Groups:** `@browser`, `@smoke`, `@critical`, `@auth`, `@character`,
`@training`, `@database`, `@api`, `@validation`, `@navigation`,
`@session`, `@errors`, `@assets`, `@mobile`, `@performance`

**Target Runtime:** 2-3 minutes  
**Use Case:** Pre-deployment smoke testing, quick health check

### VisualRegressionTest.php (ENHANCED)

**Visual consistency testing** with screenshot capture and cross-browser verification.

**What it tests:**

- 📱 **Responsive Viewports**: Mobile (320px), Tablet (640px), Desktop (1024px)
- 🌓 **Dark Mode**: All pages in dark mode rendering
- ☀️ **Light Mode**: All pages in light mode rendering
- 📸 **Screenshot Capture**: All major pages for visual comparison
- 🌐 **Cross-Browser**: Chromium, Firefox, WebKit rendering consistency
- 🧩 **Component Screenshots**: Navigation, footer, individual components
- 📐 **Breakpoint Testing**: 7 breakpoints from 320px to 1920px
- 🎭 **State-Based Visuals**: Empty states, loading states, error states

**Enhanced Features (v2):**

- Full page screenshots for 8+ pages
- Component-level screenshot isolation
- Cross-browser consistency checks
- All major responsive breakpoints
- State-based visual capture (empty, loading, error)

**Running the tests:**

```bash
# All visual regression tests
php artisan test tests/Browser/VisualRegressionTest.php

# Specific viewport tests
php artisan test --group=mobile
php artisan test --group=tablet
php artisan test --group=desktop

# Dark/light mode only
php artisan test --group=dark-mode
php artisan test --group=light-mode

# Screenshot capture only
php artisan test --group=screenshots

# Cross-browser tests
php artisan test --group=cross-browser

# Responsive breakpoints
php artisan test --group=breakpoints
```

**Screenshots Location:** `storage/app/screenshots/`

**Groups:** `@browser`, `@visual-regression`, `@mobile`, `@tablet`,
`@desktop`, `@dark-mode`, `@light-mode`, `@screenshots`,
`@cross-browser`, `@breakpoints`, `@components`, `@states`

### AccessibilityAuditTest.php (NEW)

**WCAG 2.1 AA compliance testing** ensuring the application is
accessible to all users.

**What it tests:**

- ♿ **ARIA Attributes**: Proper `aria-label`, `aria-labelledby`, `role` attributes
- ⌨️ **Keyboard Navigation**: Tab order, Enter activation, Escape key handling
- 🎹 **Focus Indicators**: Visible focus outlines, proper focus management
- 📝 **Form Accessibility**: Labels, error messages, required field indicators
- 🖼️ **Image Alt Text**: All images have descriptive alt attributes
- 🎨 **Color Contrast**: Text contrast ratios (WCAG AA)
- 🏗️ **Semantic HTML**: Proper heading hierarchy, nav/main/footer elements
- 🔗 **Link Accessibility**: Descriptive link text, external link indicators
- ⏭️ **Skip Links**: Skip to main content functionality
- 🔒 **Focus Trapping**: Modal focus management
- 📢 **Screen Reader**: Live regions, dynamic content announcements
- 👆 **Touch Targets**: Minimum 32x32px button sizes
- 🌍 **Language Attributes**: HTML lang attribute
- 📏 **Responsive Text**: Text zoom to 200% without horizontal scroll

**Running the tests:**

```bash
# All accessibility tests
php artisan test tests/Browser/AccessibilityAuditTest.php

# Specific WCAG areas
php artisan test --group=aria
php artisan test --group=keyboard
php artisan test --group=forms
php artisan test --group=semantic
php artisan test --group=contrast

# WCAG compliance check
php artisan test --group=wcag

# Accessibility group only
php artisan test --group=accessibility
```

**Groups:** `@browser`, `@accessibility`, `@wcag`, `@aria`, `@keyboard`,
`@forms`, `@images`, `@contrast`, `@semantic`, `@links`, `@skip-links`,
`@focus`, `@screen-reader`, `@touch-targets`, `@language`, `@responsive`

**Compliance Target:** WCAG 2.1 Level AA

## Test Structure

Browser tests follow this structure:

```php
<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('performs some browser action', function () {
    $page = visit('/some-url')
        ->actingAs($this->user);
    
    $page->click('button')
        ->assertSee('Expected Text')
        ->assertNoJavascriptErrors();
});
```

## Browser Testing Features

Pest 4 browser testing provides:

- **Real Browser Testing**: Tests run in actual browsers (Chromium, Firefox, WebKit)
- **Interactive Actions**: Click, type, scroll, drag-and-drop, etc.
- **Assertions**: Verify page content, attributes, visibility, etc.
- **JavaScript Error Detection**: Automatically detect console errors
- **Screenshots**: Take screenshots for debugging
- **Multi-Browser Support**: Test across different browsers
- **Device Emulation**: Test on different viewports and devices

## Common Patterns

### Waiting for Elements

```php
$page->waitFor('[data-alert-type]', 10) // Wait up to 10 seconds
    ->assertVisible('[data-alert-type]');
```

### Working with Dynamic Content

```php
$page->whenAvailable('[data-alert-type="stamina_crisis"]', function ($alert) {
    $alert->assertVisible()
        ->assertSee('Stamina')
        ->click('button[aria-label*="Dismiss"]');
});
```

### Keyboard Navigation

```php
$page->keys('body', '{tab}')
    ->pause(200)
    ->keys('body', '{enter}')
    ->pause(300);
```

### Checking Attributes

```php
$page->assertAttribute('button', 'aria-expanded', 'true')
    ->assertAttribute('[data-priority]', 'data-priority', 'critical');
```

## Debugging

### Take Screenshots

```php
$page->screenshot('debug-screenshot.png');
```

### Pause Execution

```php
$page->pause(5000); // Pause for 5 seconds to inspect
```

### Check Console Logs

```php
$page->assertNoJavascriptErrors(); // Fails if any JS errors occurred
```

## Accessibility Testing

Browser tests should verify WCAG 2.2 AA compliance:

```php
// Check ARIA attributes
$page->assertAttribute('[role="dialog"]', 'aria-modal', 'true')
    ->assertAttribute('[role="dialog"]', 'aria-labelledby', 'panel-title');

// Check keyboard navigation
$page->keys('body', '{tab}')
    ->keys('body', '{enter}')
    ->keys('body', '{escape}');

// Check focus management
$page->assertFocused('[aria-label*="Open Panel"]');
```

## Performance Considerations

- Use `pause()` sparingly - only when necessary for animations or async operations
- Use `waitFor()` instead of fixed pauses when possible
- Group related assertions to minimize page interactions
- Use `RefreshDatabase` to ensure clean state between tests

## Troubleshooting

### Tests Not Running

1. Verify Pest Browser plugin is installed:

   ```bash
   composer show pestphp/pest-plugin-browser
   ```

2. Verify Playwright is installed:

   ```bash
   npx playwright --version
   ```

3. Reinstall browser binaries if needed:

   ```bash
   npx playwright install --force
   ```

### Browser Not Opening

- Check that the application is accessible at the configured URL
- Verify no firewall or antivirus is blocking browser automation
- Try running with `--headed` flag to see the browser window

### Flaky Tests

- Increase wait timeouts for slow operations
- Use `waitFor()` instead of fixed `pause()`
- Ensure database is properly reset between tests
- Check for race conditions in async operations

## CI/CD Integration

For continuous integration, browser tests can be run headlessly:

```bash
# Run in headless mode (default)
php artisan test tests/Browser/

# Run with specific browser
BROWSER=firefox php artisan test tests/Browser/

# Run with video recording
RECORD_VIDEO=true php artisan test tests/Browser/
```

## Additional Resources

- [Pest Browser Testing Documentation](https://pestphp.com/docs/browser-testing)
- [Playwright Documentation](https://playwright.dev/)
- [WCAG 2.2 Guidelines](https://www.w3.org/WAI/WCAG22/quickref/)
- [Laravel Testing Documentation](https://laravel.com/docs/testing)

## Notes

- Browser tests are slower than unit/feature tests - use them for
  critical user flows
- Consider running browser tests separately from unit tests in CI/CD
- Keep browser tests focused on user interactions, not implementation
  details
- Use data attributes (e.g., `data-testid`) for reliable element selection
