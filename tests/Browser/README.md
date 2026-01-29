# Comprehensive Workflow Tests

This directory contains end-to-end workflow tests that validate complete user journeys through the application.

## Overview

Workflow tests validate that all features work together as documented in the PRDs, user flows, and technical specifications. These tests use Laravel's HTTP testing capabilities to simulate real user interactions.

## Test Coverage

### CharacterManagementWorkflowTest.php (Feature Test)

A comprehensive test that covers the entire character management workflow:

1. **Character List Page**
   - View all characters
   - Search and filter functionality

2. **Character Creation**
   - Form validation
   - Initial stat configuration
   - Scenario selection
   - Database persistence

3. **Character Details View**
   - Comprehensive stat display
   - Support deck summary
   - Navigation to management pages

4. **Support Card Deck Building**
   - Adding cards to deck slots (5 owned + 1 friend)
   - Friend card slot management
   - Limit break level configuration (0-4 stars)
   - Friendship level tracking (0-100%)
   - Drag-and-drop card reordering via API
   - Deck synergy calculation
   - Deck validation (6 cards required)

5. **Card Management Operations**
   - Update card details (limit break, friendship)
   - Swap card positions
   - Remove cards from deck
   - Verify deck state after operations

6. **Character Search and Filtering**
   - Search by name
   - Filter by scenario type
   - Filter by status

7. **Character Deletion**
   - Soft delete character
   - Cascade delete associated data
   - Verify cleanup

## Running Workflow Tests

### Run All Workflow Tests

```bash
php artisan test --group=workflow
```

### Run Specific Test

```bash
php artisan test tests/Feature/CharacterManagementWorkflowTest.php
```

### Run with Verbose Output

```bash
php artisan test --group=workflow --compact
```

### Run in Parallel

```bash
php artisan test --group=workflow --parallel
```

## Test Data

Workflow tests use factories to create test data:

- `User::factory()` - Test users
- `Character::factory()` - Test characters
- `SupportCardDefinition::factory()` - Support cards (10 SSR S-tier cards)

## Assertions

Workflow tests include comprehensive assertions:

- **HTTP**: `assertOk()`, `assertRedirect()`, `assertJson()`
- **Content**: `assertSee()`, `assertDontSee()`
- **Database**: `assertDatabaseHas()`, `assertDatabaseMissing()`
- **Eloquent**: Standard model assertions with Pest expectations

## Related Documentation

- [PRD-001: Character Management](../../docs/02-prds/PRD-001_Character_Management.md)
- [PRD-004: Skill Management](../../docs/02-prds/PRD-004_Skill_Management.md)
- [PRD-005: Support Card Management](../../docs/02-prds/PRD-005_Support_Card_Management.md)
- [UF-002: Career Setup Flow](../../docs/01-user-flows/UF-002_Career_Setup_Flow.md)
- [UF-006: Support Deck Building Flow](../../docs/01-user-flows/UF-006_Support_Deck_Building_Flow.md)

## What This Test Validates

### Application Flow

✅ Complete character creation workflow  
✅ Support card deck building (6-card deck)  
✅ Card management (add, update, swap, remove)  
✅ Deck synergy calculation  
✅ Character search and filtering  
✅ Data persistence and relationships  
✅ Cascade deletion  

### API Endpoints

✅ `POST /api/v1/characters/{id}/deck/cards` - Add card to deck  
✅ `PUT /api/v1/characters/{id}/deck/cards/{position}/details` - Update card details  
✅ `POST /api/v1/characters/{id}/deck/cards/swap` - Swap card positions  
✅ `DELETE /api/v1/characters/{id}/deck/cards/{position}` - Remove card  

### Database Operations

✅ Character creation with stats  
✅ Support card relationships  
✅ Deck slot management  
✅ Limit break and friendship tracking  
✅ Cascade deletion  

### Business Logic

✅ 6-card deck requirement (5 owned + 1 friend)  
✅ Friend card must be in slot 6  
✅ Limit break levels (0-4)  
✅ Friendship levels (0-100%)  
✅ Card position swapping  
✅ Deck synergy calculation  

## Troubleshooting

### Test Fails with "Character not found"

- Ensure database is migrated: `php artisan migrate:fresh`
- Check `RefreshDatabase` trait is used
- Verify factory definitions are correct

### Test Fails with "Support card not found"

- Ensure support card factory creates valid data
- Check foreign key constraints
- Verify seeder data if using seeders

### Test Fails with API Errors

- Check route definitions in `routes/api.php`
- Verify controller methods exist
- Check middleware configuration
- Ensure CSRF token handling for API routes

### Test Fails with Database Errors

- Run `php artisan migrate:fresh` before tests
- Ensure `.env.testing` has correct database config
- Check database connection in `config/database.php`

## Best Practices

1. **Test Happy Path First**: Validate the main workflow before edge cases
2. **Use Factories**: Create test data with factories for consistency
3. **Verify State Changes**: Assert database state after operations
4. **Test API Responses**: Verify JSON structure and success flags
5. **Clean Up**: Use `RefreshDatabase` to ensure test isolation
6. **Document Assumptions**: Comment complex business logic
7. **Group Related Tests**: Use `->group()` to organize tests by feature

## Performance

Workflow tests are faster than browser tests but slower than unit tests:

- **Unit Test**: ~10ms
- **Feature/Workflow Test**: ~100-500ms
- **Browser Test**: ~5-30 seconds

## CI/CD Integration

For GitHub Actions or similar CI:

```yaml
- name: Run Workflow Tests
  run: php artisan test --group=workflow
```

## Future Enhancements

- [ ] Add skill management workflow tests
- [ ] Add race schedule workflow tests
- [ ] Add training simulation workflow tests
- [ ] Add inheritance factor workflow tests
- [ ] Add character export/import workflow tests
- [ ] Add aptitude management workflow tests
- [ ] Add growth rate configuration tests

1. **Character Creation**
   - Form validation
   - Initial stat configuration
   - Scenario selection

2. **Aptitude Management**
   - Distance aptitudes (Sprint, Mile, Medium, Long)
   - Surface aptitudes (Turf, Dirt)
   - Running style aptitudes (Front Runner, Pace Chaser, Late Surger, End Closer)

3. **Growth Rates Configuration**
   - Per-stat growth rate multipliers
   - Validation of growth rate ranges

4. **Inheritance Factors**
   - Blue stat factors
   - Red aptitude factors
   - Green unique factors
   - White normal factors
   - Star level configuration

5. **Support Card Deck Building**
   - Adding cards to deck slots
   - Friend card slot management
   - Limit break level configuration
   - Friendship level tracking
   - Drag-and-drop card reordering
   - Deck synergy calculation
   - Deck validation (6 cards required)

6. **Skill Management**
   - Skill catalog browsing
   - Skill search functionality
   - Skill acquisition with SP cost
   - Skill hint system (5 levels: 10%/20%/30%/35%/40% max)
   - Skill evolution chains
   - Skill equipping/unequipping
   - SP balance tracking

7. **Race Schedule Planning**
   - Race selection by grade, distance, surface
   - Turn-based scheduling
   - Race readiness analysis
   - Stat gap identification
   - Recommended skill checks

8. **Training Simulation**
   - Facility selection
   - Stat gain predictions
   - Training execution
   - Turn progression
   - Energy and mood tracking

9. **Character Overview**
   - Comprehensive stat display
   - Aptitude visualization
   - Support deck summary
   - Skill loadout display
   - Inherited factors list
   - Race schedule timeline

10. **Data Management**
    - Character export (JSON/CSV)
    - Character search and filtering
    - Status filtering
    - Scenario filtering

## Running Browser Tests

### Prerequisites

1. **Install Pest 4** (already included in composer.json):

   ```bash
   composer require pestphp/pest --dev --with-all-dependencies
   ```

2. **Start the development server**:

   ```bash
   php artisan serve
   ```

3. **Ensure database is migrated and seeded**:

   ```bash
   php artisan migrate:fresh --seed
   ```

### Run All Browser Tests

```bash
php artisan test --group=browser
```

### Run Specific Test

```bash
php artisan test tests/Browser/CharacterManagementWorkflowTest.php
```

### Run with Verbose Output

```bash
php artisan test --group=browser --compact
```

### Run in Parallel

```bash
php artisan test --group=browser --parallel
```

## Browser Configuration

Pest 4 browser tests use Playwright under the hood. The default configuration:

- **Browser**: Chromium (can be changed to Firefox or WebKit)
- **Headless**: Yes (set to `false` to see the browser)
- **Viewport**: 1280x720
- **Timeout**: 30 seconds per action

### Debugging Browser Tests

1. **Take Screenshots**:

   ```php
   $page->screenshot('debug-screenshot.png');
   ```

2. **Pause Execution**:

   ```php
   $page->pause(); // Opens browser DevTools
   ```

3. **View Console Logs**:

   ```php
   $page->assertNoConsoleLogs(); // Fails if there are console errors
   ```

4. **Run in Non-Headless Mode**:
   Set `PEST_BROWSER_HEADLESS=false` in your `.env.testing` file

## Test Data

Browser tests use factories to create test data:

- `User::factory()` - Test users
- `Character::factory()` - Test characters
- `SupportCardDefinition::factory()` - Support cards
- `Skill::factory()` - Skills
- `Aptitude::factory()` - Aptitudes
- `Factor::factory()` - Inheritance factors

## Assertions

Browser tests include comprehensive assertions:

- **Visual**: `assertSee()`, `assertDontSee()`
- **Form**: `fill()`, `select()`, `click()`
- **Navigation**: `visit()`, `waitFor()`
- **JavaScript**: `assertNoJavascriptErrors()`, `assertNoConsoleLogs()`
- **Database**: Standard Eloquent assertions

## Screenshots

Test screenshots are saved to `tests/` directory:

- `character-management-workflow-complete.png` - Final state after complete workflow

## Related Documentation

- [PRD-001: Character Management](../../docs/02-prds/PRD-001_Character_Management.md)
- [PRD-004: Skill Management](../../docs/02-prds/PRD-004_Skill_Management.md)
- [PRD-005: Support Card Management](../../docs/02-prds/PRD-005_Support_Card_Management.md)
- [UF-002: Career Setup Flow](../../docs/01-user-flows/UF-002_Career_Setup_Flow.md)
- [UF-006: Support Deck Building Flow](../../docs/01-user-flows/UF-006_Support_Deck_Building_Flow.md)

## Troubleshooting

### Test Fails with "Element not found"

- Increase timeout: `$page->waitFor('.element', timeout: 5000)`
- Check if element selector is correct
- Verify element is visible (not hidden by CSS)

### Test Fails with "Navigation timeout"

- Ensure development server is running
- Check network connectivity
- Increase navigation timeout in Pest config

### Test Fails with Database Errors

- Run `php artisan migrate:fresh --seed` before tests
- Ensure `RefreshDatabase` trait is used
- Check database connection in `.env.testing`

### JavaScript Errors in Browser

- Check browser console: `$page->assertNoConsoleLogs()`
- Verify Vite assets are built: `npm run build`
- Check for missing Alpine.js or Livewire components

## Best Practices

1. **Use Descriptive Selectors**: Prefer data attributes or semantic HTML over CSS classes
2. **Wait for Elements**: Always use `waitFor()` before interacting with dynamic content
3. **Verify State Changes**: Assert database state after UI interactions
4. **Take Screenshots**: Capture important states for documentation
5. **Group Related Tests**: Use `->group()` to organize tests by feature
6. **Clean Up**: Use `RefreshDatabase` to ensure test isolation
7. **Test Happy Path First**: Validate the main workflow before edge cases
8. **Document Assumptions**: Comment complex interactions or business logic

## Performance

Browser tests are slower than unit/feature tests:

- **Unit Test**: ~10ms
- **Feature Test**: ~100ms
- **Browser Test**: ~5-30 seconds

Run browser tests separately from unit/feature tests:

```bash
# Fast tests only
php artisan test --exclude-group=browser

# Browser tests only
php artisan test --group=browser
```

## CI/CD Integration

For GitHub Actions or similar CI:

```yaml
- name: Run Browser Tests
  run: |
    php artisan serve &
    sleep 5
    php artisan test --group=browser
```

## Future Enhancements

- [ ] Add visual regression testing
- [ ] Test multiple browsers (Firefox, Safari)
- [ ] Test responsive layouts (mobile, tablet)
- [ ] Add accessibility testing (WCAG compliance)
- [ ] Test dark mode functionality
- [ ] Add performance monitoring
- [ ] Test offline functionality (PWA)
