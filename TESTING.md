# Testing Guide

Quick reference for running tests in the Umamusume Career Planner project.

## Prerequisites

- PHP 8.4.11+
- Composer
- Node.js & NPM
- Playwright (for browser tests)

## Installation

### First Time Setup

```bash
# Install PHP dependencies
composer install

# Install Pest Browser plugin (if not already installed)
composer require pestphp/pest-plugin-browser:^4.0 --dev --ignore-platform-reqs

# Install Node dependencies
npm install

# Install Playwright browsers
npx playwright install
```

## Running Tests

### All Tests

```bash
php artisan test --compact
```

### Specific Test Suite

```bash
# Unit tests only
php artisan test tests/Unit --compact

# Feature tests only
php artisan test tests/Feature --compact

# Integration tests only
php artisan test tests/Integration --compact

# Browser tests only
php artisan test --group=browser --compact
```

### Specific Test File

```bash
php artisan test tests/Unit/Models/UserTest.php --compact
```

### Specific Test

```bash
php artisan test --filter="admin user is identified correctly" --compact
```

### Parallel Execution (Faster)

```bash
php artisan test --parallel --compact
```

## Test Types

### Unit Tests (`tests/Unit/`)

Fast, isolated tests for individual classes and methods.

```php
test('calculates total correctly', function () {
    $calculator = new Calculator();
    expect($calculator->add(2, 3))->toBe(5);
});
```

### Feature Tests (`tests/Feature/`)

Test complete features and user workflows.

```php
test('user can create character', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->post('/characters', [
            'name' => 'Test Character',
            'scenario_type' => 'ura_finale',
        ]);
    
    $response->assertRedirect();
    assertDatabaseHas('ucp_characters', ['name' => 'Test Character']);
});
```

### Browser Tests (`tests/Browser/`)

End-to-end tests using real browsers.

```php
test('user can complete training workflow', function () {
    visit('/training')
        ->click('.start-training')
        ->assertSee('Training Complete')
        ->assertNoJavascriptErrors();
});
```

### Integration Tests (`tests/Integration/`)

Test interactions between multiple components.

```php
test('advisory service integrates with cache', function () {
    $service = app(TrainingAdvisoryService::class);
    $result = $service->getRecommendations($character);
    
    expect($result)->toBeInstanceOf(RecommendationCollection::class);
});
```

## Common Test Patterns

### Database Testing

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('creates record', function () {
    $user = User::factory()->create();
    assertDatabaseHas('users', ['id' => $user->id]);
});
```

### API Testing

```php
test('API returns correct data', function () {
    $response = $this->getJson('/api/characters');
    
    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'scenario_type']
            ]
        ]);
});
```

### Mocking

```php
test('uses external service', function () {
    $mock = Mockery::mock(ExternalService::class);
    $mock->shouldReceive('getData')->andReturn(['result' => 'success']);
    
    $this->app->instance(ExternalService::class, $mock);
    
    // Test code that uses ExternalService
});
```

## Debugging Tests

### Run Single Test with Verbose Output

```bash
php artisan test --filter="test name" -vvv
```

### Stop on First Failure

```bash
php artisan test --stop-on-failure
```

### Show Test Coverage

```bash
php artisan test --coverage
```

### Browser Tests with Visible Browser

```bash
PEST_BROWSER_HEADLESS=false php artisan test --group=browser
```

## Common Issues

### Issue: "Undefined array key" errors

**Cause**: Incomplete data arrays (e.g., missing stat fields)

**Solution**: Ensure all required fields are provided:

```php
'current_stats' => [
    'speed' => 500,
    'stamina' => 500,
    'power' => 500,
    'guts' => 500,
    'wit' => 500,  // Don't use 'wisdom'!
]
```

### Issue: "visit() function requires Pest Plugin Browser"

**Solution**: Install browser plugin:

```bash
composer require pestphp/pest-plugin-browser:^4.0 --dev --ignore-platform-reqs
npx playwright install
```

### Issue: Tests timing out

**Solution**: Increase timeout:

```php
test('slow operation', function () {
    // test code
})->timeout(30); // 30 seconds
```

### Issue: Database errors in tests

**Solution**: Use RefreshDatabase trait:

```php
uses(RefreshDatabase::class);
```

## Best Practices

1. **Keep tests fast**: Unit tests should run in milliseconds
2. **Use factories**: Don't manually create test data
3. **Test behavior, not implementation**: Focus on what, not how
4. **One assertion per test**: Makes failures easier to debug
5. **Use descriptive test names**: `test('user can create character with valid data')`
6. **Clean up after tests**: Use transactions or RefreshDatabase
7. **Avoid test interdependence**: Each test should be independent
8. **Mock external services**: Don't make real API calls in tests

## Continuous Integration

Tests run automatically on:

- Every commit (via pre-commit hook)
- Every pull request (via CI/CD)
- Before deployment

## Resources

- [Pest Documentation](https://pestphp.com/)
- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [Pest Browser Plugin](https://pestphp.com/docs/plugins/browser)
- Project-specific: `docs/testing/`

## Quick Commands Reference

```bash
# Run all tests
php artisan test --compact

# Run specific suite
php artisan test tests/Unit --compact

# Run specific test
php artisan test --filter="test name"

# Run with coverage
php artisan test --coverage

# Run in parallel
php artisan test --parallel

# Stop on failure
php artisan test --stop-on-failure

# Browser tests (visible)
PEST_BROWSER_HEADLESS=false php artisan test --group=browser
```

## Getting Help

- Check test output for error messages
- Review `docs/testing/` for detailed guides
- Check `docs/fixes/` for known issues and solutions
- Ask in team chat for assistance

---

**Last Updated**: February 5, 2026
