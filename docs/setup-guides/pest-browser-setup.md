# Pest Browser Plugin Setup Guide

## Overview

This guide documents the installation and configuration of the Pest Browser plugin for end-to-end browser testing.

## Installation Steps

### 1. Install Pest Browser Plugin

```bash
composer require pestphp/pest-plugin-browser:^4.0 --dev --ignore-platform-reqs
```text

**Note**: The `--ignore-platform-reqs` flag is needed on Windows because Laravel Horizon requires `ext-pcntl` and
`ext-posix` which are Unix-only extensions.

### 2. Install Playwright

```bash
npm install playwright@latest
```

### 3. Install Playwright Browsers

```bash
npx playwright install
```text

This downloads the browser binaries (Chromium, Firefox, WebKit) needed for testing.

## What Was Installed

### Composer Packages (21 new packages)

- `pestphp/pest-plugin-browser` v4.0.0 - Main browser testing plugin
- `amphp/*` packages - Async HTTP server and client libraries
- `revolt/event-loop` - Event loop for async operations
- `league/uri-components` - URI parsing and manipulation
- Other supporting libraries

### NPM Packages

- `playwright@latest` - Browser automation library

## Usage

### Basic Browser Test Example

```php
use function Pest\Laravel\visit;

test('homepage loads correctly', function () {
    visit('/')
        ->assertSee('Welcome')
        ->assertNoJavascriptErrors();
});
```

### Available Browser Methods

- `visit($url)` - Navigate to a URL
- `click($selector)` - Click an element
- `type($selector, $text)` - Type into an input
- `assertSee($text)` - Assert text is visible
- `assertNoJavascriptErrors()` - Assert no JS errors
- `assertNoConsoleLogs()` - Assert no console logs
- `screenshot($path)` - Take a screenshot
- And many more...

## Configuration

### Pest Configuration

The plugin is automatically discovered by Pest. No additional configuration needed in `tests/Pest.php`.

### Browser Selection

By default, tests run in Chromium. To test on multiple browsers:

```php
test('works on all browsers', function () {
    visit('/')
        ->assertSee('Welcome');
})->browsers(['chrome', 'firefox', 'safari']);
```text

### Headless Mode

Tests run in headless mode by default. To see the browser:

```bash
PEST_BROWSER_HEADLESS=false php artisan test
```

## Troubleshooting

### Issue: "visit() function requires the Pest Plugin Browser"

**Solution**: Follow the installation steps above.

### Issue: Platform requirements error

**Solution**: Use `--ignore-platform-reqs` flag when installing on Windows.

### Issue: Browsers not found

**Solution**: Run `npx playwright install` to download browser binaries.

### Issue: Tests timing out

**Solution**: Increase timeout in test:

```php
test('slow page', function () {
    visit('/slow-page')
        ->assertSee('Content');
})->timeout(30); // 30 seconds
```text

## Best Practices

1. **Use Browser Tests Sparingly**: Browser tests are slower than unit/feature tests. Use them for critical user flows
only.

2. **Test User Journeys**: Focus on complete user workflows rather than individual page loads.

3. **Avoid Flaky Tests**: Use proper waits and assertions:

   ```php
   visit('/page')
       ->waitFor('.dynamic-content')
       ->assertSee('Loaded');
   ```

1. **Clean Up After Tests**: Use database transactions or refresh database:

   ```php
   use Illuminate\Foundation\Testing\RefreshDatabase;
   
   uses(RefreshDatabase::class);
   ```text

2. **Take Screenshots on Failure**: Helpful for debugging:

   ```php
   test('important flow', function () {
       try {
           visit('/flow')
               ->click('.start-button')
               ->assertSee('Success');
       } catch (Exception $e) {
           screenshot('failure.png');
           throw $e;
       }
   });
   ```

## Running Browser Tests

### Run All Browser Tests

```bash
php artisan test --group=browser
```text

### Run Specific Browser Test

```bash
php artisan test --filter="test name"
```

### Run in Specific Browser

```bash
PEST_BROWSER=firefox php artisan test
```text

### Run with Visible Browser

```bash
PEST_BROWSER_HEADLESS=false php artisan test
```

## Performance Considerations

- Browser tests are 10-100x slower than unit tests
- Each browser instance uses ~100-200MB RAM
- Parallel execution can speed up test suite:

  ```bash
  php artisan test --parallel
  ```text

## Integration with CI/CD

### GitHub Actions Example

```yaml
- name: Install Playwright
  run: npx playwright install --with-deps

- name: Run Browser Tests
  run: php artisan test --group=browser
```

### GitLab CI Example

```yaml
test:browser:
  script:
    - npx playwright install --with-deps
    - php artisan test --group=browser
```text

## Resources

- [Pest Browser Plugin Documentation](https://pestphp.com/docs/plugins/browser)
- [Playwright Documentation](https://playwright.dev/)
- [Laravel Testing Documentation](https://laravel.com/docs/testing)

## Version Information

- Pest Browser Plugin: v4.0.0
- Playwright: Latest
- PHP: 8.4.11
- Laravel: 12

## Date

Installed: February 5, 2026

