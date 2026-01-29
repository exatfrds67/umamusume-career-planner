---
name: laravel-testing-best-practices
description: Laravel 12 testing best practices with Pest v4. Use when writing or reviewing tests, understanding test structure, or implementing test coverage.
---

# Laravel Testing Best Practices with Pest v4

## Overview

This skill provides comprehensive guidance for testing Laravel 12 applications using Pest v4, including unit tests, feature tests, browser tests, and integration patterns specific to this project.

## Test Structure

### Directory Organization

```
tests/
├── Unit/              # Unit tests for services, helpers, models
├── Feature/           # Feature tests for HTTP, Livewire, APIs
├── Integration/       # Integration tests for external services
├── Browser/           # Browser tests with Pest v4
├── Architecture/      # Architecture tests
├── Pest.php          # Pest configuration
└── TestCase.php      # Base test case
```

### Naming Conventions

- Test files: `{Feature}Test.php` (e.g., `CharacterManagementTest.php`)
- Test names: Descriptive sentences (e.g., `it('creates a character with valid data')`)
- Use `it()` for behavior tests, `test()` for specific scenarios

## Pest v4 Syntax

### Basic Test Structure

```php
<?php

use App\Models\User;
use App\Models\Character;

it('creates a character successfully', function () {
    $user = User::factory()->create();
    
    $this->actingAs($user)
        ->post('/characters', [
            'name' => 'Special Week',
            'speed' => 100,
            'stamina' => 100,
        ])
        ->assertSuccessful()
        ->assertJson(['success' => true]);
    
    expect(Character::count())->toBe(1);
});
```

### Using Datasets

```php
it('validates character stats', function (string $stat, int $value, bool $valid) {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->post('/characters', [
            'name' => 'Test Character',
            $stat => $value,
        ]);
    
    if ($valid) {
        $response->assertSuccessful();
    } else {
        $response->assertUnprocessable();
    }
})->with([
    'valid speed' => ['speed', 100, true],
    'invalid speed negative' => ['speed', -1, false],
    'invalid speed too high' => ['speed', 1201, false],
    'valid stamina' => ['stamina', 500, true],
]);
```

## Testing Patterns for This Project

### Testing Dual Storage Mode

```php
it('works in local mode', function () {
    // Test with UUID-based routes
    $uuid = Str::uuid();
    
    $response = $this->get("/plans/local/{$uuid}");
    
    $response->assertSuccessful();
});

it('works in account mode', function () {
    $user = User::factory()->create();
    $character = Character::factory()->for($user)->create();
    
    $response = $this->actingAs($user)
        ->get("/characters/{$character->id}");
    
    $response->assertSuccessful();
});
```

### Testing Services

```php
use App\Services\TrainingCalculationService;

it('calculates training gains correctly', function () {
    $service = app(TrainingCalculationService::class);
    
    $result = $service->calculateGains([
        'facility' => 'speed',
        'base_stat' => 100,
        'growth_rate' => 1.2,
    ]);
    
    expect($result)
        ->toHaveKey('speed_gain')
        ->and($result['speed_gain'])->toBeGreaterThan(0);
});
```

### Testing Livewire Components

```php
use App\Livewire\Characters\CharacterForm;
use Livewire\Livewire;

it('updates character stats', function () {
    $user = User::factory()->create();
    $character = Character::factory()->for($user)->create();
    
    Livewire::actingAs($user)
        ->test(CharacterForm::class, ['character' => $character])
        ->set('speed', 150)
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('character-updated');
    
    expect($character->fresh()->speed)->toBe(150);
});
```

### Browser Testing (Pest v4)

```php
it('creates a character through the UI', function () {
    $user = User::factory()->create();
    
    $this->actingAs($user);
    
    $page = visit('/characters/create');
    
    $page->assertSee('Create Character')
        ->fill('name', 'Special Week')
        ->fill('speed', '100')
        ->fill('stamina', '100')
        ->click('Create')
        ->assertSee('Character created successfully')
        ->assertNoJavascriptErrors();
    
    expect(Character::where('name', 'Special Week')->exists())->toBeTrue();
});
```

## Common Assertions

### Response Assertions

```php
$response->assertSuccessful();        // 2xx status
$response->assertOk();                // 200 status
$response->assertCreated();           // 201 status
$response->assertNoContent();         // 204 status
$response->assertNotFound();          // 404 status
$response->assertForbidden();         // 403 status
$response->assertUnprocessable();     // 422 status
```

### Pest Expectations

```php
expect($value)->toBe(100);
expect($value)->toBeGreaterThan(50);
expect($array)->toHaveCount(3);
expect($array)->toContain('item');
expect($model)->toBeInstanceOf(Character::class);
expect($collection)->toHaveKey('speed');
expect($string)->toStartWith('Special');
expect($boolean)->toBeTrue();
```

## Database Testing

### Using Factories

```php
it('creates character with relationships', function () {
    $character = Character::factory()
        ->has(SkillAcquisition::factory()->count(3))
        ->has(TrainingSession::factory()->count(5))
        ->create();
    
    expect($character->skillAcquisitions)->toHaveCount(3);
    expect($character->trainingSessions)->toHaveCount(5);
});
```

### Database Assertions

```php
$this->assertDatabaseHas('characters', [
    'name' => 'Special Week',
    'speed' => 100,
]);

$this->assertDatabaseMissing('characters', [
    'name' => 'Invalid Character',
]);

$this->assertDatabaseCount('characters', 1);
```

## Mocking and Faking

### Event Faking

```php
use Illuminate\Support\Facades\Event;

it('dispatches character created event', function () {
    Event::fake();
    
    $user = User::factory()->create();
    
    $this->actingAs($user)
        ->post('/characters', [
            'name' => 'Special Week',
        ]);
    
    Event::assertDispatched(CharacterCreated::class);
});
```

### Queue Faking

```php
use Illuminate\Support\Facades\Queue;

it('queues training calculation job', function () {
    Queue::fake();
    
    // Trigger action that queues job
    
    Queue::assertPushed(CalculateTrainingJob::class);
});
```

### HTTP Faking

```php
use Illuminate\Support\Facades\Http;

it('fetches external data', function () {
    Http::fake([
        'umapyoi.net/*' => Http::response(['data' => 'test'], 200),
    ]);
    
    $service = app(ExternalDataService::class);
    $result = $service->fetchData();
    
    expect($result)->toHaveKey('data');
});
```

## Test Performance

### Using RefreshDatabase

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('runs with fresh database', function () {
    // Database is migrated fresh for each test
});
```

### Parallel Testing

```bash
# Run tests in parallel
php artisan test --parallel

# Specify number of processes
php artisan test --parallel --processes=4
```

## Code Coverage

```bash
# Generate coverage report
composer test:coverage

# Generate HTML coverage report
composer test:coverage-html
```

## Best Practices

1. **Test Isolation**: Each test should be independent
2. **Descriptive Names**: Use clear, descriptive test names
3. **Arrange-Act-Assert**: Follow AAA pattern
4. **Factory Usage**: Use factories instead of manual model creation
5. **Minimal Setup**: Only set up what's needed for the test
6. **Fast Tests**: Keep tests fast by avoiding unnecessary database operations
7. **Test Edge Cases**: Test both happy paths and error conditions
8. **Use Datasets**: Reduce duplication with Pest datasets
9. **Mock External Services**: Don't make real API calls in tests
10. **Run Tests Frequently**: Run tests before committing

## Running Tests

```bash
# All tests
php artisan test --compact

# Specific file
php artisan test --compact tests/Feature/CharacterTest.php

# Filter by name
php artisan test --compact --filter=creates_character

# Specific suite
composer test:unit
composer test:feature
composer test:integration

# With coverage
composer test:coverage
```

## Troubleshooting

### Common Issues

1. **Database not refreshing**: Ensure `RefreshDatabase` trait is used
2. **Factories not found**: Check factory namespace and imports
3. **Authentication issues**: Use `actingAs()` for authenticated tests
4. **Livewire tests failing**: Ensure Livewire is properly configured
5. **Browser tests timing out**: Increase timeout or check for JavaScript errors

### Debug Helpers

```php
// Dump and die
dd($variable);

// Dump response
$response->dump();

// Ray debugging (if installed)
ray($variable);

// Assert and dump on failure
expect($value)->toBe(100)->dd();
```

## Related Documentation

- [Pest Documentation](https://pestphp.com)
- [Laravel Testing Documentation](https://laravel.com/docs/12.x/testing)
- Project AGENTS.md: Testing Standards section
- Project SKILLS.md: Testing & QA section
- `docs/testing/PRODUCTION_TESTING_GUIDE.md`: Production testing procedures
- `docs/testing/API_TESTING_REPORT.md`: API testing results
- `docs/testing/character-creation-flow-test.md`: Flow testing examples
- `docs/testing/offline-page-accessibility.md`: Accessibility testing
- `docs/testing/TEST_ERRORS_RESOLVED.md`: Common test issues and solutions
- `docs/setup-guides/INSTALL_CODE_COVERAGE.md`: Code coverage setup

## Version Information

- Laravel: v12
- Pest: v4
- PHPUnit: v12
- Last Updated: 2026-01-29
