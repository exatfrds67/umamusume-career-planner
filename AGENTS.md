# AI Agent Development Guidelines

This file contains development guidelines and coding standards for AI coding assistants working on this Laravel application. These guidelines ensure consistent, high-quality code that follows Laravel best practices and project conventions.

## Project Overview

This is a Laravel 12 application with the following technology stack:

### Core Dependencies

- **PHP**: 8.4.11
- **Laravel Framework**: v12
- **Testing**: Pest v3, PHPUnit v11
- **Code Quality**: Laravel Pint v1
- **Development Tools**: Laravel Sail v1, Telescope v5, Horizon v5
- **Authentication**: Laravel Sanctum v4
- **CLI Tools**: Laravel Prompts v0
- **MCP Integration**: Laravel MCP v0

### Project Structure

This application follows Laravel 12's streamlined directory structure:

- `app/` - Application logic (Models, Controllers, etc.)
- `bootstrap/` - Application bootstrapping and configuration
- `config/` - Configuration files
- `database/` - Migrations, factories, seeders
- `resources/` - Views, assets, language files
- `routes/` - Route definitions
- `tests/` - Feature and unit tests
- `storage/` - Application storage
- `public/` - Public web assets

## Development Principles

### Code Quality Standards

- Follow existing code conventions by examining sibling files
- Use descriptive names for variables and methods (e.g., `isRegisteredForDiscounts`, not `discount()`)
- Check for existing components before creating new ones
- Prioritize tests over verification scripts
- Maintain existing directory structure without approval for changes
- Be concise in explanations - focus on important details
- Only create documentation files when explicitly requested

### Architecture Guidelines

- Stick to existing directory structure
- Do not change application dependencies without approval
- For frontend changes not reflecting in UI, suggest running `npm run build`, `npm run dev`, or `composer run dev`

## PHP Coding Standards

### General PHP Rules

- Always use curly braces for control structures, even single-line statements
- Use explicit return type declarations for all methods and functions
- Use appropriate PHP type hints for method parameters
- Prefer PHPDoc blocks over inline comments
- Never use inline comments within code unless handling complex logic
- Add useful array shape type definitions in PHPDoc when appropriate

### Constructor Standards

- Use PHP 8 constructor property promotion:

  ```php
  public function __construct(public GitHub $github) { }
  ```

- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private

### Type Declarations

Always use explicit return types and parameter types:

```php
protected function isAccessible(User $user, ?string $path = null): bool
{
    // Implementation
}
```

### Enums

- Use TitleCase for enum keys: `FavoritePerson`, `BestLake`, `Monthly`

## Laravel Framework Guidelines

### Laravel Way Principles

- Use `php artisan make:` commands to create new files (migrations, controllers, models, etc.)
- For generic PHP classes, use `php artisan make:class`
- Pass `--no-interaction` to all Artisan commands
- Include appropriate `--options` for correct behavior

### Database and Eloquent

- Always use proper Eloquent relationship methods with return type hints
- Prefer relationship methods over raw queries or manual joins
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`
- Generate code that leverages Laravel's ORM capabilities
- Prevent N+1 query problems by using eager loading
- Use Laravel's query builder for complex database operations only

### Model Management

- When creating models, also create factories and seeders
- Ask users about additional model requirements using `list-artisan-commands`
- Use the `casts()` method on models rather than the `$casts` property (follow existing conventions)

### API Development

- Default to using Eloquent API Resources and API versioning
- Follow existing application conventions if they differ

### Controllers and Validation

- Always create Form Request classes for validation instead of inline validation
- Include both validation rules and custom error messages
- Check sibling Form Requests for array vs string validation rule conventions

### Background Processing

- Use queued jobs with `ShouldQueue` interface for time-consuming operations

### Authentication and Authorization

- Use Laravel's built-in features: gates, policies, Sanctum, etc.

### URL Generation

- Prefer named routes and the `route()` function for generating links

### Configuration Management

- Use environment variables only in configuration files
- Never use `env()` function directly outside config files
- Always use `config('app.name')`, not `env('APP_NAME')`

## Laravel 12 Specific Guidelines

### Modern Laravel Structure

Laravel 12 uses a streamlined file structure with these key changes:

#### Middleware Configuration

- Middleware are no longer registered in `app/Http/Kernel.php`
- Configure middleware declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`

#### Application Bootstrap

- `bootstrap/app.php` - Register middleware, exceptions, and routing files
- `bootstrap/providers.php` - Application-specific service providers
- `app/Console/Kernel.php` no longer exists
- Use `bootstrap/app.php` or `routes/console.php` for console configuration
- Console commands in `app/Console/Commands/` are automatically available

#### Database Features

- When modifying columns, include all previously defined attributes to prevent data loss
- Laravel 12 supports limiting eagerly loaded records natively: `$query->latest()->limit(10)`

### Error Handling

For Vite manifest errors, suggest running:

- `npm run build`
- `npm run dev`
- `composer run dev`

## Testing Standards with Pest

### Testing Philosophy

- Write tests to verify features rather than creating verification scripts
- Unit and feature tests are more important than manual verification
- Tests should cover happy paths, failure paths, and edge cases

### Pest Framework Guidelines

- All tests must be written using Pest framework
- Use `php artisan make:test --pest {name}` to create tests
- Never remove tests or test files without approval - they are core to the application
- Tests live in `tests/Feature` and `tests/Unit` directories
- Most tests should be feature tests; use `--unit` flag only when appropriate

### Test Structure

Basic Pest test structure:

```php
it('validates user authentication', function () {
    expect(true)->toBeTrue();
});
```

### Test Execution

- Run minimal tests using appropriate filters before finalizing code
- Run all tests: `php artisan test --compact`
- Run specific file: `php artisan test --compact tests/Feature/ExampleTest.php`
- Filter by test name: `php artisan test --compact --filter=testName`
- Ask users about running full test suite after changes pass related tests

### Assertions

Use specific assertion methods instead of generic ones:

```php
// Good
$response->assertSuccessful();
$response->assertForbidden();
$response->assertNotFound();

// Avoid
$response->assertStatus(200);
$response->assertStatus(403);
```

### Test Data and Factories

- Use model factories for test data creation
- Check for custom factory states before manual model setup
- Follow existing conventions for `$this->faker` vs `fake()`

### Mocking

- Use mocking when appropriate for external dependencies
- Import Pest mock function: `use function Pest\Laravel\mock;`
- Alternative: use `$this->mock()` if existing tests follow this pattern
- Create partial mocks using the same import pattern

### Datasets

Use datasets to reduce test duplication, especially for validation rules:

```php
it('validates email formats', function (string $email) {
    expect($email)->not->toBeEmpty();
})->with([
    'james' => 'james@laravel.com',
    'taylor' => 'taylor@laravel.com',
]);
```

## Code Quality and Formatting

### Laravel Pint

- Run `vendor/bin/pint --dirty` before finalizing changes
- Do not run `vendor/bin/pint --test`; use `vendor/bin/pint` to fix formatting issues
- Ensure code matches project's expected style standards

## Development Workflow

### Documentation Research

- Search documentation before making code changes
- Use multiple, broad, topic-based queries
- Examples: `['rate limiting', 'routing rate limiting', 'routing']`
- Do not include package names in queries (version info is automatically included)

### Search Query Syntax

1. **Simple Word Searches**: `authentication` (finds 'authenticate', 'auth')
2. **Multiple Words (AND)**: `rate limit` (finds both "rate" AND "limit")
3. **Quoted Phrases**: `"infinite scroll"` (exact phrase match)
4. **Mixed Queries**: `middleware "rate limit"` (combines approaches)
5. **Multiple Queries**: `["authentication", "middleware"]` (ANY of these terms)

### Debugging and Development

- Use appropriate debugging tools for PHP execution and Eloquent queries
- Use database query tools for read-only database operations
- Check browser logs for frontend issues (focus on recent logs only)
- Use proper URL generation tools for sharing project URLs

### File Organization

- Follow existing file structure and naming conventions
- Check sibling files for structure, approach, and naming patterns
- Reuse existing components before creating new ones
- Maintain consistency with established patterns

## Quality Assurance

### Code Review Checklist

- [ ] Follows existing code conventions
- [ ] Uses descriptive variable and method names
- [ ] Includes proper type declarations
- [ ] Has appropriate test coverage
- [ ] Follows Laravel best practices
- [ ] Uses proper Eloquent relationships
- [ ] Implements proper validation via Form Requests
- [ ] Follows Laravel 12 structure guidelines
- [ ] Passes code formatting standards
- [ ] Includes necessary documentation (when requested)

### Performance Considerations

- Prevent N+1 queries with eager loading
- Use queued jobs for time-consuming operations
- Leverage Laravel's built-in caching mechanisms
- Optimize database queries using Eloquent best practices

This document serves as the authoritative guide for AI coding assistants working on this Laravel application. Following these guidelines ensures consistent, maintainable, and high-quality code that aligns with Laravel best practices and project-specific requirements.

===

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
- larastan/larastan (LARASTAN) - v3
- laravel/horizon (HORIZON) - v5
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- laravel/telescope (TELESCOPE) - v5
- pestphp/pest (PEST) - v3
- phpunit/phpunit (PHPUNIT) - v11
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

| Deprecated | Replacement |
|------------+--------------|
| bg-opacity-*| bg-black/* |
| text-opacity-*| text-black/* |
| border-opacity-*| border-black/* |
| divide-opacity-*| divide-black/* |
| ring-opacity-*| ring-black/* |
| placeholder-opacity-*| placeholder-black/* |
| flex-shrink-*| shrink-* |
| flex-grow-*| grow-* |
| overflow-ellipsis | text-ellipsis |
| decoration-slice | box-decoration-slice |
| decoration-clone | box-decoration-clone |
</laravel-boost-guidelines>

## Development Environment Issues

### Cross-Platform Command Execution (Windows/WSL)

#### PowerShell/WSL Command Chain Errors

##### Error: "grep: The term 'grep' is not recognized" / "wc: The term 'wc' is not recognized"

**When it happens:**
This error occurs when trying to use Unix commands (`grep`, `wc`, `awk`, etc.) directly in PowerShell instead of within WSL context.

**Incorrect usage:**

```powershell
wsl ps aux | grep horizon  # This fails because grep runs in PowerShell context
wsl cat file.txt | wc -l   # This fails because wc runs in PowerShell context
```

**Correct usage:**

```powershell
wsl bash -c "ps aux | grep horizon"  # This works because grep runs in WSL context
wsl bash -c "cat file.txt | wc -l"   # This works because wc runs in WSL context
```

**Command Execution Rules:**

- Always wrap Unix command chains in `wsl bash -c "command1 | command2"`
- Never pipe WSL output directly to Unix commands in PowerShell
- Use PowerShell equivalents when working in Windows context:
  - `grep` → `Select-String`
  - `wc -l` → `Measure-Object -Line`
  - `ps aux` → `Get-Process`
  - `kill` → `Stop-Process`

**Error Prevention:**

- Test command syntax before execution
- Use proper WSL context wrapping for Unix command chains
- Document working command patterns for future reference

### Tool Execution Errors

#### String Replacement Tool Errors

##### Error: "No path provided"

**When it happens:**
This error occurs when using the `strReplace` tool without providing the required `path` parameter. Common causes:

1. Copy-pasting incomplete function calls
2. Accidentally submitting empty or incomplete strReplace calls
3. System glitches that clear parameters before submission

**Resolution:**
Always ensure the `strReplace` call includes all required parameters:

- `path`: The file path to modify
- `oldStr`: The exact text to replace
- `newStr`: The replacement text

**Example of correct usage:**

```text
strReplace(
  path="docs/example.md",
  oldStr="**Bold Text**",
  newStr="### Bold Text"
)
```

**Best Practices:**

- Double-check all parameters before submitting
- Use specific context when replacing text that appears multiple times
- Test with small, unique text patterns first
