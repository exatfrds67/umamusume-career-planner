# Uma Musume Career Planner - Copilot Instructions

## Overview

Comprehensive local-first Laravel 12+ application for Umamusume Pretty Derby career optimization. Features AI-powered recommendations (Neuron AI), turn-by-turn character progression tracking, skill management, support card deck building, race strategy planning, and dual storage modes (localStorage + database).

**Stack**: Laravel 12+, PHP 8.2+, Livewire 4, Alpine.js, TailwindCSS v4, Vite 7, Pest 4

## Quick Reference

### Commands

```bash
# Setup
composer install && npm install && php artisan migrate --seed

# Development
composer run dev  # Runs all services (Vite + Horizon)
php artisan serve # Application server
npm run dev       # Vite only

# Testing
php artisan test --compact              # Pest tests
npm run playwright:test                 # E2E tests
php artisan test --filter=testName      # Specific test

# Code Quality
vendor/bin/pint --dirty                 # Format PHP
npm run prettier:fix                    # Format JS/CSS
vendor/bin/phpstan analyse              # Static analysis
```

### Key Models & Relationships

```
User → Character (careers) → Career
                           → SkillAcquisition (pivot)
                           → TrainingSession
                           → Race
                           → Event
                           → ParentCharacter (inheritance)

Career → skillAcquisitions
      → trainingSessions
      → races
      → events
      → runSnapshots

Character → careers
         → factors
         → skills (via SkillAcquisition)
         → supportCards (deck)
```

### Core Enums

```php
StorageMode: LOCAL, ACCOUNT              // Dual storage architecture
CareerPhase: JUNIOR, CLASSIC, SENIOR, URA_FINALE
AffinityGrade: High, Standard, Low       // Parent affinity
Mood: VeryGood, Good, Normal, Bad, VeryBad
RunningStyle: Nige, Senkou, Sashi, Oikomi
RaceDistance: Sprint, Mile, Medium, Long, Dirt
```

### Route Patterns

```php
// Character management (authenticated)
/characters                              // Index
/characters/create                       // Create
/characters/{character}                  // Show (career view)
/characters/{character}/edit             // Edit
/characters/{character}/training         // Training interface
/characters/{character}/deck-builder     // Support card deck
/characters/{character}/factors          // Inheritance factors

// Careers (via character context)

/reports/career/{career}                // Career report
/reports/character/{character}           // Character summary

// Skills, races, AI
/skills                                  // Skill catalog
/races                                   // Race database
/races/calendar                          // 72-turn calendar
/ai/chat                                 // AI advisory chat
/ai/dashboard                            // AI monitoring

// Data management
/import, /export, /backup                // Data operations
/data-management                         // Unified hub
/ocr/upload                              // Screenshot OCR

// Admin (requires admin role)
/admin/dashboard                         // System overview
/admin/users                             // User management
```

## Architecture Patterns

### Service Layer (Business Logic)

All business logic belongs in services. Controllers and Livewire components orchestrate only.

**Key Services**:

- `TrainingAdvisoryService`: AI-powered training recommendations
- `TrainingCalculationService`: Stat growth calculations
- `TrainingPredictionService`: Outcome predictions
- `SkillService`, `SkillAnalysisService`: Skill management
- `CareerAnalyticsService`, `CareerReportingService`: Analytics
- `DeckManagementService`: Support card deck operations
- `RaceExecutionService`, `RaceConditionService`: Race logic
- `LocalStorageService`: Browser storage abstraction
- `DataImportService`, `DataExportService`: Import/export
- `BackupService`: Backup/restore operations

### Dual Storage Mode

**Local Mode** (localStorage):

- Guest users, offline-capable
- UUID-based identification
- Serialized to JSON in browser
- No authentication required

**Account Mode** (database):

- Authenticated users
- Full relational storage
- Sync across devices
- Supports all features

**Critical**: All features touching character/career data must handle both modes transparently. Use `StorageMode` enum and `LocalStorageService` abstraction.

### Livewire Components

**Major Components**:

- `App\Livewire\Admin\*`: Admin panel components
- `App\Livewire\Analytics\*`: Analytics dashboards
- `App\Livewire\Settings\*`: User preferences
- `App\Livewire\Simulation\*`: Scenario simulations
- `AdvisoryPanel`: AI recommendation display
- `NotificationDropdown`: Real-time notifications
- `SynergyBuildPlanner`: Skill synergy analysis

**Alpine Integration**: Many UI interactions (modals, dropdowns, tabs, toasts) use Alpine.js components in `resources/js/components/` and `resources/views/components/`.

## Development Guidelines

### Database

- Use Eloquent relationships over raw queries
- Eager load to prevent N+1 (see `QueryOptimizationService`)
- Soft deletes for user data (careers, characters)
- JSON columns for flexible metadata (`career_metadata`, `acquisition_context`, etc.)
- Proper indexes on frequently queried columns

### Testing Strategy

**Run minimal tests** - never full suite unless explicitly requested (see user memory).

```bash
# Run specific test file
php artisan test tests/Feature/CareerTest.php --compact

# Run specific test
php artisan test --filter=it_creates_career_with_valid_data

# Browser tests (Pest 4)
php artisan test tests/Browser/ --compact
```

**Test Coverage**:

- Feature tests for Livewire components and controllers
- Unit tests for services (calculations, validations)
- Browser tests (Playwright) for critical user flows
- Mock external APIs (Ollama, Bedrock) in tests

### Frontend

**TailwindCSS v4**:

- Use utility classes, not `@apply`
- Dark mode: `dark:` prefix (follows system preference)
- Responsive: mobile-first breakpoints
- Custom theme in `resources/css/app.css` using `@theme` directive

**Alpine.js**:

- Persistent state: `x-persist` for localStorage
- Dropdowns: `x-data="dropdown()"` pattern
- Modals: `x-data="modal()"` pattern
- Dark mode toggle: `x-data="darkMode()"` in `app.js`

**Component Library**: See `resources/views/components/COMPONENT_LIBRARY.md` for reusable Blade components.

### Security

- **Authorization**: Laravel policies for all resource access
- **Validation**: Form Requests for controllers, Livewire validation rules for components
- **CSRF**: Enabled by default (Livewire handles automatically)
- **Rate limiting**: Applied to AI endpoints, OCR uploads
- **Privacy**: Consent management (`ConsentRecord` model), deletion requests (`DeletionRequest`)
- **Sanitization**: DOMPurify for user HTML content

### Performance

- **Caching**: Redis for:
  - Skill catalog (`SkillCatalogCacheService`)
  - Race requirements (`RaceRequirementsCacheService`)
  - Recommendations (`RecommendationCacheService`)
  - Query results (`QueryOptimizationService`)
- **Queues**: Laravel Horizon for:
  - AI inference jobs
  - OCR processing
  - Bulk imports/exports
  - Notification dispatch
- **APM**: `ApmService` tracks performance metrics
- **Monitoring**: Telescope for debugging, Horizon for queues

## AI Integration (Neuron AI)

**Providers**:

- **Local**: Ollama (privacy-first, offline)
- **Cloud**: AWS Bedrock Claude (high accuracy)

**Features**:

- Training recommendations with turn-by-turn context
- Race readiness analysis
- Skill acquisition advisory
- Deck optimization suggestions
- Real-time streaming chat interface
- Wit adequacy checks
- Inheritance timing recommendations

**Configuration**: `config/ai.php`, `config/neuron.php`

## Common Patterns

### Creating New Features

1. **Model**: Define Eloquent model with relationships and casts
2. **Migration**: Create table with proper indexes and foreign keys
3. **Factory**: Generate realistic test data
4. **Service**: Implement business logic in dedicated service class
5. **Controller/Livewire**: Create thin orchestration layer
6. **Routes**: Register routes in `routes/web.php` (authenticated group)
7. **Views**: Create Blade templates using component library
8. **Tests**: Write feature tests for endpoints, unit tests for service logic
9. **Format**: Run `vendor/bin/pint --dirty`

### Enum Usage

Prefer enums over magic strings:

```php
// Good
$career->current_phase = CareerPhase::CLASSIC;
if ($mood === Mood::VeryGood) { ... }

// Bad
$career->current_phase = 'classic_year';
if ($mood === 'very_good') { ... }
```

### JSON Columns

Cast to arrays and validate structure:

```php
protected function casts(): array
{
    return [
        'support_deck' => 'array',
        'career_metadata' => 'array',
    ];
}

// Validate structure in Form Request
'career_metadata' => 'nullable|array',
'career_metadata.notes' => 'nullable|string',
```

## Documentation

**Read first**: `AGENTS.md` for full AI agent guidelines (v2.1.0)

**Key docs**:

- `docs/00-core-docs/`: Architecture (SDP, SRS, SDS, DBD)
- `docs/02-prds/`: Product requirements per module
- `docs/02-specs/`: Technical specifications
- `docs/01-flows/`: System flows (Mermaid diagrams)
- `docs/01-sequences/`: Interaction sequences
- `resources/views/components/COMPONENT_LIBRARY.md`: UI components

**Creating docs**: Place in `docs/` subdirectories only. Follow markdownlint rules (see AGENTS.md Documentation Standards).

## Troubleshooting

### Vite Manifest Error

```bash
# Build assets
npm run build

# Or start dev server
npm run dev
```

### Horizon Not Starting (Windows)

Use WSL: `wsl php artisan horizon` (see CLAUDE.md for setup)

### Queue Jobs Not Processing

```bash
# Check Horizon
php artisan horizon:status

# Restart Horizon
php artisan horizon:terminate
php artisan horizon
```

### Test Failures

```bash
# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan test:clear

# Run with verbose output
php artisan test --filter=failing_test
```

---

**For comprehensive guidelines**: See `AGENTS.md` (v2.1.0) for AI agent development standards, architecture details, and complete development workflow.

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.11
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- livewire/livewire (LIVEWIRE) - v4
- larastan/larastan (LARASTAN) - v3
- laravel/horizon (HORIZON) - v5
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- laravel/telescope (TELESCOPE) - v5
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- alpinejs (ALPINEJS) - v3
- tailwindcss (TAILWINDCSS) - v4

## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double-check the available parameters.

## URLs
- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches when dealing with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The `search-docs` tool is perfect for all Laravel-related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

## PHP

- Always use strict typing at the head of a `.php` file: `declare(strict_types=1);`.
- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless there is something very complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

=== tests rules ===

## Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

## Laravel 12

- Use the `search-docs` tool to get version-specific documentation.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

### Laravel 12 Structure
- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app\Console\Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== livewire/core rules ===

## Livewire

- Use the `search-docs` tool to find exact version-specific documentation for how to write Livewire and Livewire tests.
- Use the `php artisan make:livewire [Posts\CreatePost]` Artisan command to create new components.
- State should live on the server, with the UI reflecting it.
- All Livewire requests hit the Laravel backend; they're like regular HTTP requests. Always validate form data and run authorization checks in Livewire actions.

## Livewire Best Practices
- Livewire components require a single root element.
- Use `wire:loading` and `wire:dirty` for delightful loading states.
- Add `wire:key` in loops:

    ```blade
    @foreach ($items as $item)
        <div wire:key="item-{{ $item->id }}">
            {{ $item->name }}
        </div>
    @endforeach
    ```

- Prefer lifecycle hooks like `mount()`, `updatedFoo()` for initialization and reactive side effects:

<code-snippet name="Lifecycle Hook Examples" lang="php">
    public function mount(User $user) { $this->user = $user; }
    public function updatedSearch() { $this->resetPage(); }
</code-snippet>

## Testing Livewire

<code-snippet name="Example Livewire Component Test" lang="php">
    Livewire::test(Counter::class)
        ->assertSet('count', 0)
        ->call('increment')
        ->assertSet('count', 1)
        ->assertSee(1)
        ->assertStatus(200);
</code-snippet>

<code-snippet name="Testing Livewire Component Exists on Page" lang="php">
    $this->get('/posts/create')
    ->assertSeeLivewire(CreatePost::class);
</code-snippet>

=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.

=== pest/core rules ===

## Pest
### Testing
- If you need to verify a feature is working, write or update a Unit / Feature test.

### Pest Tests
- All tests must be written using Pest. Use `php artisan make:test --pest {name}`.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files - these are core to the application.
- Tests should test all of the happy paths, failure paths, and weird paths.
- Tests live in the `tests/Feature` and `tests/Unit` directories.
- Pest tests look and behave like this:
<code-snippet name="Basic Pest Test Example" lang="php">
it('is true', function () {
    expect(true)->toBeTrue();
});
</code-snippet>

### Running Tests
- Run the minimal number of tests using an appropriate filter before finalizing code edits.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).
- When the tests relating to your changes are passing, ask the user if they would like to run the entire test suite to ensure everything is still passing.

### Pest Assertions
- When asserting status codes on a response, use the specific method like `assertForbidden` and `assertNotFound` instead of using `assertStatus(403)` or similar, e.g.:
<code-snippet name="Pest Example Asserting postJson Response" lang="php">
it('returns all', function () {
    $response = $this->postJson('/api/docs', []);

    $response->assertSuccessful();
});
</code-snippet>

### Mocking
- Mocking can be very helpful when appropriate.
- When mocking, you can use the `Pest\Laravel\mock` Pest function, but always import it via `use function Pest\Laravel\mock;` before using it. Alternatively, you can use `$this->mock()` if existing tests do.
- You can also create partial mocks using the same import or self method.

### Datasets
- Use datasets in Pest to simplify tests that have a lot of duplicated data. This is often the case when testing validation rules, so consider this solution when writing tests for validation rules.

<code-snippet name="Pest Dataset Example" lang="php">
it('has emails', function (string $email) {
    expect($email)->not->toBeEmpty();
})->with([
    'james' => 'james@laravel.com',
    'taylor' => 'taylor@laravel.com',
]);
</code-snippet>

=== pest/v4 rules ===

## Pest 4

- Pest 4 is a huge upgrade to Pest and offers: browser testing, smoke testing, visual regression testing, test sharding, and faster type coverage.
- Browser testing is incredibly powerful and useful for this project.
- Browser tests should live in `tests/Browser/`.
- Use the `search-docs` tool for detailed guidance on utilizing these features.

### Browser Testing
- You can use Laravel features like `Event::fake()`, `assertAuthenticated()`, and model factories within Pest 4 browser tests, as well as `RefreshDatabase` (when needed) to ensure a clean state for each test.
- Interact with the page (click, type, scroll, select, submit, drag-and-drop, touch gestures, etc.) when appropriate to complete the test.
- If requested, test on multiple browsers (Chrome, Firefox, Safari).
- If requested, test on different devices and viewports (like iPhone 14 Pro, tablets, or custom breakpoints).
- Switch color schemes (light/dark mode) when appropriate.
- Take screenshots or pause tests for debugging when appropriate.

### Example Tests

<code-snippet name="Pest Browser Test Example" lang="php">
it('may reset the password', function () {
    Notification::fake();

    $this->actingAs(User::factory()->create());

    $page = visit('/sign-in'); // Visit on a real browser...

    $page->assertSee('Sign In')
        ->assertNoJavascriptErrors() // or ->assertNoConsoleLogs()
        ->click('Forgot Password?')
        ->fill('email', 'nuno@laravel.com')
        ->click('Send Reset Link')
        ->assertSee('We have emailed your password reset link!')

    Notification::assertSent(ResetPassword::class);
});
</code-snippet>

<code-snippet name="Pest Smoke Testing Example" lang="php">
$pages = visit(['/', '/about', '/contact']);

$pages->assertNoJavascriptErrors()->assertNoConsoleLogs();
</code-snippet>

=== tailwindcss/core rules ===

## Tailwind CSS

- Use Tailwind CSS classes to style HTML; check and use existing Tailwind conventions within the project before writing your own.
- Offer to extract repeated patterns into components that match the project's conventions (i.e. Blade, JSX, Vue, etc.).
- Think through class placement, order, priority, and defaults. Remove redundant classes, add classes to parent or child carefully to limit repetition, and group elements logically.
- You can use the `search-docs` tool to get exact examples from the official documentation when needed.

### Spacing
- When listing items, use gap utilities for spacing; don't use margins.

<code-snippet name="Valid Flex Gap Spacing Example" lang="html">
    <div class="flex gap-8">
        <div>Superior</div>
        <div>Michigan</div>
        <div>Erie</div>
    </div>
</code-snippet>

### Dark Mode
- If existing pages and components support dark mode, new pages and components must support dark mode in a similar way, typically using `dark:`.

=== tailwindcss/v4 rules ===

## Tailwind CSS 4

- Always use Tailwind CSS v4; do not use the deprecated utilities.
- `corePlugins` is not supported in Tailwind v4.
- In Tailwind v4, configuration is CSS-first using the `@theme` directive — no separate `tailwind.config.js` file is needed.

<code-snippet name="Extending Theme in CSS" lang="css">
@theme {
  --color-brand: oklch(0.72 0.11 178);
}
</code-snippet>

- In Tailwind v4, you import Tailwind using a regular CSS `@import` statement, not using the `@tailwind` directives used in v3:

<code-snippet name="Tailwind v4 Import Tailwind Diff" lang="diff">
   - @tailwind base;
   - @tailwind components;
   - @tailwind utilities;
   + @import "tailwindcss";
</code-snippet>

### Replaced Utilities
- Tailwind v4 removed deprecated utilities. Do not use the deprecated option; use the replacement.
- Opacity values are still numeric.

| Deprecated |	Replacement |
|------------+--------------|
| bg-opacity-* | bg-black/* |
| text-opacity-* | text-black/* |
| border-opacity-* | border-black/* |
| divide-opacity-* | divide-black/* |
| ring-opacity-* | ring-black/* |
| placeholder-opacity-* | placeholder-black/* |
| flex-shrink-* | shrink-* |
| flex-grow-* | grow-* |
| overflow-ellipsis | text-ellipsis |
| decoration-slice | box-decoration-slice |
| decoration-clone | box-decoration-clone |
</laravel-boost-guidelines>
