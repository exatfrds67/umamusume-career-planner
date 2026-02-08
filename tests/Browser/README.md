# Browser E2E Tests

This directory contains end-to-end (E2E) browser tests using Pest 4's browser testing capabilities powered by Playwright.

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

This will install Playwright and download the necessary browser binaries (Chromium, Firefox, WebKit).

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

- Browser tests are slower than unit/feature tests - use them for critical user flows
- Consider running browser tests separately from unit tests in CI/CD
- Keep browser tests focused on user interactions, not implementation details
- Use data attributes (e.g., `data-testid`) for reliable element selection
