# AI Agent Development Guidelines

This file contains development guidelines and coding standards for AI coding assistants working on this Laravel application. These guidelines ensure consistent, high-quality code that follows Laravel best practices and project conventions.

**Based on official recommendations from:** Anthropic Claude Code, Amazon Q Developer, GitHub Copilot, OpenAI Codex, Google Gemini Code Assist, Cursor IDE, and JetBrains AI Assistant.

---

## Table of Contents

1. [Project Overview](#project-overview)
2. [Development Principles](#development-principles)
3. [Context and Prompt Engineering](#context-and-prompt-engineering)
4. [PHP Coding Standards](#php-coding-standards)
5. [Laravel Framework Guidelines](#laravel-framework-guidelines)
6. [Laravel 12 Specific Guidelines](#laravel-12-specific-guidelines)
7. [Testing Standards with Pest](#testing-standards-with-pest)
8. [Security Best Practices](#security-best-practices)
9. [Code Quality and Formatting](#code-quality-and-formatting)
10. [Development Workflow](#development-workflow)
11. [Quality Assurance](#quality-assurance)

---

## Project Overview

This is a **Laravel 12** application for the **Umamusume Pretty Derby Career Planner** - a comprehensive local-first web application for optimizing gameplay through AI-powered recommendations.

### Core Technology Stack

- **PHP**: 8.4.11
- **Laravel Framework**: v12 (released February 24, 2025)
- **Testing**: Pest v4, PHPUnit v12
- **Code Quality**: Laravel Pint v1, Larastan v3
- **Development Tools**: Laravel Sail v1, Telescope v5, Horizon v5
- **Authentication**: Laravel Sanctum v4
- **Frontend**: Tailwind CSS v4, Alpine.js v3
- **Build Tool**: Vite v7
- **Database**: MySQL 8.0+ with Redis (WSL) for caching
- **AI Integration**: Ollama (local) + AWS Bedrock (cloud fallback)
- **MCP Integration**: Laravel MCP v0

### Project Architecture

- **Local-First**: All personal data stored locally (MySQL + Redis via WSL)
- **Privacy-Focused**: No data transmission without explicit consent
- **Hybrid AI**: Local models primary, cloud fallback for complex tasks
- **Accessibility**: WCAG 2.2 AA compliant
- **Performance**: Sub-2-second response times, Core Web Vitals compliance

### Project Structure

This application follows Laravel 12's streamlined directory structure:

- `app/` - Application logic (Models, Controllers, Services, etc.)
- `bootstrap/` - Application bootstrapping and configuration
- `config/` - Configuration files
- `database/` - Migrations, factories, seeders
- `resources/` - Views, assets, language files
- `routes/` - Route definitions
- `tests/` - Feature and unit tests (Pest framework)
- `storage/` - Application storage
- `public/` - Public web assets

---

## Development Principles

### Code Quality Standards

> **From GitHub Copilot:** "While Copilot is very powerful, it is still a tool capable of making mistakes, and you should always validate the code it suggests."

- **Follow existing code conventions** by examining sibling files
- **Use descriptive names** for variables and methods (e.g., `isRegisteredForDiscounts`, not `discount()`)
- **Check for existing components** before creating new ones
- **Prioritize tests over verification scripts** - programmatic testing is essential
- **Maintain existing directory structure** without approval for changes
- **Be concise in explanations** - focus on important details
- **Only create documentation files when explicitly requested**

### Architecture Guidelines

> **From OpenAI Codex:** "Act as a discerning engineer: optimize for correctness, clarity, and reliability over speed; avoid risky shortcuts, speculative changes, and messy hacks."

- **Stick to existing directory structure** - don't create new base folders without approval
- **Do not change application dependencies** without approval
- **For frontend changes not reflecting in UI**, suggest running `npm run build`, `npm run dev`, or `composer run dev`
- **Follow DRY principle** - search for existing implementations before creating new ones
- **Implement comprehensive solutions** - cover all relevant surfaces, don't just fix symptoms

### Iteration and Refinement

> **From Claude Code:** "Like humans, Claude's outputs tend to improve significantly with iteration. While the first version might be good, after 2-3 iterations it will typically look much better."

- **Expect to iterate** - first attempts are rarely perfect
- **Provide feedback early and often** to guide toward better solutions
- **Use undo/redo** to explore alternatives
- **Clear context** when switching tasks
- **Request planning** before implementation for complex problems

---

## Context and Prompt Engineering

### Providing Effective Context

> **From Amazon Q Developer:** "Start with existing code, import libraries, create classes and functions, or establish code skeletons. This context significantly improves code generation quality."

**Best Practices:**

1. **Open Relevant Files**
   - Keep relevant files open in your IDE
   - Close irrelevant files to reduce noise
   - AI assistants use open files as context

2. **Include Import Statements**
   - Import relevant libraries before requesting code
   - AI uses imports to understand your tech stack
   - Helps generate framework-specific code

3. **Establish Code Skeletons**
   - Create class structures and function signatures first
   - Define interfaces and types
   - Provides architectural context

4. **Reference Project Documentation**
   - Check `.kiro/steering/` files for project conventions
   - Review `docs/` for feature specifications
   - Reference existing similar implementations

### Crafting Effective Prompts

> **From GitHub Copilot:** "Prompt engineering plays a critical role in Copilot's ability to generate valuable responses."

**Key Principles:**

1. **Be Specific and Detailed**
   - ❌ Poor: "add tests for CharacterService"
   - ✅ Good: "write Pest feature tests for CharacterService, covering character creation with valid data, validation failures, and edge cases where stats exceed 1200. Use factories, avoid mocks."

2. **Provide Examples**
   - Show input/output pairs
   - Reference similar existing code
   - Demonstrate desired patterns

3. **Break Down Complex Tasks**
   - Split large requests into smaller steps
   - Request planning before implementation
   - Use iterative refinement

4. **Specify Constraints**
   - Mention Laravel 12 and PHP 8.4
   - State security requirements
   - Define performance expectations
   - Specify testing requirements (Pest v4)

5. **Use Natural Language**
   - Write prompts as you would explain to a colleague
   - Be conversational but precise
   - Use standard comment blocks for inline generation

---

## PHP Coding Standards

### General PHP Rules

- **Always use curly braces** for control structures, even single-line statements
- **Use explicit return type declarations** for all methods and functions
- **Use appropriate PHP type hints** for method parameters
- **Prefer PHPDoc blocks** over inline comments
- **Never use inline comments** within code unless handling complex logic
- **Add useful array shape type definitions** in PHPDoc when appropriate

### Constructor Standards

Use PHP 8 constructor property promotion:

```php
// ✅ Good
public function __construct(
    public TrainingCalculationService $trainingService,
    public CharacterRepositoryInterface $characterRepository
) {}

// ❌ Bad - verbose old style
private TrainingCalculationService $trainingService;
private CharacterRepositoryInterface $characterRepository;

public function __construct(
    TrainingCalculationService $trainingService,
    CharacterRepositoryInterface $characterRepository
) {
    $this->trainingService = $trainingService;
    $this->characterRepository = $characterRepository;
}
```

- **Do not allow empty `__construct()` methods** with zero parameters unless the constructor is private

### Type Declarations

Always use explicit return types and parameter types:

```php
// ✅ Good
protected function isAccessible(User $user, ?string $path = null): bool
{
    return $user->hasPermission($path);
}

// ❌ Bad - missing return type
protected function isAccessible(User $user, ?string $path = null)
{
    return $user->hasPermission($path);
}
```

### Enums

- Use **TitleCase** for enum keys: `FavoritePerson`, `BestLake`, `Monthly`

---

## Laravel Framework Guidelines

### Do Things the Laravel Way

> **From Laravel AI Documentation:** "Laravel is uniquely positioned to be the best framework for AI assisted and agentic development due to its opinionated conventions and well-defined structure."

- **Use `php artisan make:` commands** to create new files (migrations, controllers, models, etc.)
- For generic PHP classes, use `php artisan make:class`
- **Pass `--no-interaction`** to all Artisan commands
- **Include appropriate `--options`** for correct behavior

### Database and Eloquent

> **From Laravel Boost:** "Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins."

**Best Practices:**

1. **Use Eloquent Relationships**

   ```php
   // ✅ Good - proper relationship with return type
   public function supportCards(): BelongsToMany
   {
       return $this->belongsToMany(SupportCard::class, 'character_support_cards')
           ->withPivot('friendship_level', 'position')
           ->withTimestamps();
   }
   
   // ❌ Bad - manual join
   $cards = DB::table('characters')
       ->join('character_support_cards', ...)
       ->get();
   ```

2. **Prevent N+1 Queries**

   ```php
   // ✅ Good - eager loading
   $characters = Character::with(['supportCards', 'aptitudes', 'skills'])->get();
   
   // ❌ Bad - N+1 problem
   $characters = Character::all();
   foreach ($characters as $character) {
       $cards = $character->supportCards; // N+1 query
   }
   ```

3. **Use Query Builder Properly**
   - Avoid `DB::`; prefer `Model::query()`
   - Use Eloquent models and relationships before suggesting raw queries
   - Use Laravel's query builder for complex database operations only

### Model Management

> **From Security Research:** "45% of AI-generated code contains vulnerabilities like SQL injection and cross-site scripting."

**When creating models:**

1. Create useful **factories** and **seeders**
2. Ask users about additional requirements using `list-artisan-commands`
3. Use the `casts()` method on models rather than the `$casts` property (follow existing conventions)
4. Always define **fillable** or **guarded** properties
5. Add proper **relationship return types**

```php
// ✅ Good - Laravel 12 style
class Character extends Model
{
    protected $fillable = [
        'name',
        'speed',
        'stamina',
        'power',
        'guts',
        'wit',
    ];

    protected function casts(): array
    {
        return [
            'speed' => 'integer',
            'stamina' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function careers(): HasMany
    {
        return $this->hasMany(Career::class);
    }
}
```

### API Development

- **Default to using Eloquent API Resources** and API versioning
- Follow existing application conventions if they differ
- Use proper HTTP status codes
- Implement rate limiting

### Controllers and Validation

> **From Laravel Boost:** "Always create Form Request classes for validation rather than inline validation in controllers."

**Best Practices:**

1. **Always create Form Request classes** for validation
2. Include both **validation rules** and **custom error messages**
3. Check sibling Form Requests for array vs string validation rule conventions

```php
// ✅ Good - Form Request class
class StoreCharacterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'speed' => ['required', 'integer', 'min:0', 'max:1200'],
            'stamina' => ['required', 'integer', 'min:0', 'max:1200'],
        ];
    }

    public function messages(): array
    {
        return [
            'speed.max' => 'Speed cannot exceed 1200.',
            'stamina.max' => 'Stamina cannot exceed 1200.',
        ];
    }
}

// ❌ Bad - inline validation
public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'speed' => 'required|integer|min:0|max:1200',
    ]);
}
```

### Background Processing

- Use **queued jobs** with `ShouldQueue` interface for time-consuming operations
- Implement proper error handling and retry logic
- Use Horizon for queue monitoring

### Authentication and Authorization

- Use Laravel's built-in features: **gates**, **policies**, **Sanctum**
- Implement proper role-based access control
- Never bypass authorization checks

### URL Generation

- Prefer **named routes** and the `route()` function for generating links
- Use `get-absolute-url` tool (Laravel Boost) when sharing project URLs

### Configuration Management

> **Critical Rule:** "Use environment variables only in configuration files - never use the `env()` function directly outside of config files."

```php
// ✅ Good - use config helper
$appName = config('app.name');
$aiEnabled = config('ai.enabled');

// ❌ Bad - direct env() usage
$appName = env('APP_NAME');
$aiEnabled = env('AI_ENABLED');
```

---

## Laravel 12 Specific Guidelines

### Modern Laravel Structure

> **From Laravel 12 Documentation:** "Laravel 12 uses a streamlined file structure with declarative configuration."

#### Middleware Configuration

- Middleware are **no longer registered** in `app/Http/Kernel.php`
- Configure middleware **declaratively** in `bootstrap/app.php` using `Application::configure()->withMiddleware()`

```php
// bootstrap/app.php
return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);
    })
    ->create();
```

#### Application Bootstrap

- `bootstrap/app.php` - Register middleware, exceptions, and routing files
- `bootstrap/providers.php` - Application-specific service providers
- `app/Console/Kernel.php` **no longer exists**
- Use `bootstrap/app.php` or `routes/console.php` for console configuration
- Console commands in `app/Console/Commands/` are **automatically available**

#### Database Features

- When **modifying columns**, include all previously defined attributes to prevent data loss
- Laravel 12 supports **limiting eagerly loaded records** natively: `$query->latest()->limit(10)`

```php
// ✅ Good - Laravel 12 native limit
$characters = Character::with([
    'careers' => fn($query) => $query->latest()->limit(5)
])->get();

// ❌ Bad - missing attributes in migration
Schema::table('characters', function (Blueprint $table) {
    $table->integer('speed')->change(); // Lost nullable, default, etc.
});

// ✅ Good - preserve all attributes
Schema::table('characters', function (Blueprint $table) {
    $table->integer('speed')->nullable()->default(0)->change();
});
```

### Error Handling

For Vite manifest errors, suggest running:

- `npm run build`
- `npm run dev`
- `composer run dev`

---

## Testing Standards with Pest

### Testing Philosophy

> **From Multiple Sources:** "AI-generated code must be programmatically tested. Write tests or update existing tests, then run affected tests to ensure they pass."

- **Write tests to verify features** rather than creating verification scripts
- **Unit and feature tests are more important** than manual verification
- Tests should cover **happy paths**, **failure paths**, and **edge cases**

### Pest Framework Guidelines

- **All tests must be written using Pest framework**
- Use `php artisan make:test --pest {name}` to create tests
- **Never remove tests or test files** without approval - they are core to the application
- Tests live in `tests/Feature` and `tests/Unit` directories
- **Most tests should be feature tests**; use `--unit` flag only when appropriate

### Test Structure

Basic Pest test structure:

```php
<?php

use App\Models\Character;
use App\Models\User;

it('creates a character with valid data', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->postJson('/api/characters', [
        'name' => 'Special Week',
        'speed' => 800,
        'stamina' => 700,
        'power' => 600,
        'guts' => 500,
        'wit' => 650,
    ]);
    
    $response->assertSuccessful();
    expect(Character::count())->toBe(1);
    expect(Character::first()->name)->toBe('Special Week');
});

it('validates character stats do not exceed 1200', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->postJson('/api/characters', [
        'name' => 'Invalid Character',
        'speed' => 1500, // Exceeds maximum
    ]);
    
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['speed']);
});
```

### Test Execution

> **From Laravel Boost:** "Run the minimum number of tests needed to ensure code quality and speed."

- Run minimal tests using appropriate filters before finalizing code
- Run all tests: `php artisan test --compact`
- Run specific file: `php artisan test --compact tests/Feature/CharacterTest.php`
- Filter by test name: `php artisan test --compact --filter=testName`
- Ask users about running full test suite after changes pass related tests

### Assertions

Use **specific assertion methods** instead of generic ones:

```php
// ✅ Good - specific assertions
$response->assertSuccessful();
$response->assertForbidden();
$response->assertNotFound();
$response->assertUnprocessable();

// ❌ Bad - generic status assertions
$response->assertStatus(200);
$response->assertStatus(403);
$response->assertStatus(404);
$response->assertStatus(422);
```

### Test Data and Factories

- Use **model factories** for test data creation
- Check for **custom factory states** before manual model setup
- Follow existing conventions for `$this->faker` vs `fake()`

```php
// ✅ Good - use factories
$character = Character::factory()->create([
    'speed' => 1000,
]);

// ✅ Good - use factory states if available
$character = Character::factory()->withHighStats()->create();

// ❌ Bad - manual model creation
$character = new Character();
$character->name = 'Test';
$character->speed = 1000;
$character->save();
```

### Mocking

- Use mocking when appropriate for external dependencies
- Import Pest mock function: `use function Pest\Laravel\mock;`
- Alternative: use `$this->mock()` if existing tests follow this pattern
- Create partial mocks using the same import pattern

```php
use function Pest\Laravel\mock;

it('uses external API service', function () {
    $mock = mock(ExternalAPIService::class);
    $mock->shouldReceive('fetchData')
        ->once()
        ->andReturn(['data' => 'test']);
    
    // Test code using the mock
});
```

### Datasets

Use **datasets** to reduce test duplication, especially for validation rules:

```php
it('validates email formats', function (string $email, bool $valid) {
    $response = $this->postJson('/api/register', [
        'email' => $email,
    ]);
    
    if ($valid) {
        $response->assertSuccessful();
    } else {
        $response->assertJsonValidationErrors(['email']);
    }
})->with([
    'valid email' => ['test@example.com', true],
    'invalid email' => ['not-an-email', false],
    'missing @ symbol' => ['testexample.com', false],
]);
```

### Test-Driven Development

> **From Claude Code:** "Write tests first based on expected behavior. Confirm tests fail. Generate code to pass tests. Iterate until all tests pass."

**TDD Workflow:**

1. Write tests based on expected input/output pairs
2. Run tests to confirm they fail
3. Commit tests
4. Generate code to pass tests
5. Iterate until all tests pass
6. Commit implementation

---

## Security Best Practices

### Critical Security Principles

> **From Security Research:** "AI-generated code is a security minefield. Speed over scrutiny leads to vulnerabilities. 45% of AI-generated code contains vulnerabilities like SQL injection and cross-site scripting."

**Security Best Practices:**

1. **Input Validation**
   - Never trust AI-generated input handling without explicit validation
   - Implement sanitization for all user inputs
   - Use parameterized queries (Eloquent handles this)
   - Validate data types and formats

2. **Dependency Verification**
   - Verify all AI-suggested packages exist and are legitimate
   - Check package versions and security advisories
   - Review package permissions and dependencies
   - Use trusted package sources only (Packagist for PHP)

3. **Secrets Management**
   - Never hardcode API keys or credentials
   - Use environment variables properly (only in config files)
   - Implement proper secrets rotation
   - Use secure secret management services

4. **Authentication and Authorization**
   - Use Laravel Sanctum for API authentication
   - Implement proper role-based access control
   - Verify permissions at every level
   - Use secure session management

### Common Vulnerabilities to Check

**SQL Injection:**

```php
// ✅ Good - Eloquent prevents SQL injection
$characters = Character::where('name', $request->input('name'))->get();

// ❌ Bad - vulnerable to SQL injection
$characters = DB::select("SELECT * FROM characters WHERE name = '{$request->input('name')}'");
```

**Cross-Site Scripting (XSS):**

```blade
{{-- ✅ Good - Blade escapes output --}}
<h1>{{ $character->name }}</h1>

{{-- ❌ Bad - unescaped output --}}
<h1>{!! $character->name !!}</h1>
```

**Mass Assignment:**

```php
// ✅ Good - protected with fillable
class Character extends Model
{
    protected $fillable = ['name', 'speed', 'stamina'];
}

// ❌ Bad - no protection
class Character extends Model
{
    // No $fillable or $guarded
}
```

### Security Review Process

1. **Automated Security Scanning**
   - Run Larastan for static analysis
   - Use Laravel Pint for code style
   - Check for known vulnerabilities

2. **Manual Security Review**
   - Review authentication flows
   - Check authorization logic
   - Verify data encryption
   - Audit API endpoints

3. **Security Documentation**
   - Document security decisions
   - Track security issues
   - Document remediation steps

---

## Code Quality and Formatting

### Laravel Pint

> **From Laravel Boost:** "You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style."

- Run `vendor/bin/pint --dirty` before finalizing changes
- **Do not run** `vendor/bin/pint --test`
- Simply run `vendor/bin/pint` to fix any formatting issues

### Static Analysis

- Run `vendor/bin/phpstan analyse` for static analysis
- Address all errors and warnings
- Use proper type hints to help static analysis

---

## Development Workflow

### Documentation Research

> **From Laravel Boost:** "Use the `search-docs` tool before any other approaches when dealing with Laravel or Laravel ecosystem packages."

**Best Practices:**

1. **Search documentation before making code changes**
2. Use **multiple, broad, topic-based queries**
3. Examples: `['rate limiting', 'routing rate limiting', 'routing']`
4. **Do not include package names** in queries (version info is automatically included)
5. The `search-docs` tool is perfect for Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, etc.

### Search Query Syntax

1. **Simple Word Searches**: `authentication` (finds 'authenticate', 'auth')
2. **Multiple Words (AND)**: `rate limit` (finds both "rate" AND "limit")
3. **Quoted Phrases**: `"infinite scroll"` (exact phrase match)
4. **Mixed Queries**: `middleware "rate limit"` (combines approaches)
5. **Multiple Queries**: `["authentication", "middleware"]` (ANY of these terms)

### Debugging and Development

- Use **`tinker` tool** for PHP execution and Eloquent queries
- Use **`database-query` tool** for read-only database operations
- Check **`browser-logs`** for frontend issues (focus on recent logs only)
- Use **`get-absolute-url`** tool for sharing project URLs

### File Organization

- Follow existing file structure and naming conventions
- Check sibling files for structure, approach, and naming patterns
- Reuse existing components before creating new ones
- Maintain consistency with established patterns

### Recommended Workflows

#### 1. Explore, Plan, Code, Commit

> **From Claude Code:** "Ask AI to read relevant files first (don't code yet). Request a plan with 'think hard' for complex problems. Implement the solution. Commit and create PR."

```
1. Ask AI to read relevant files (don't code yet)
2. Request a plan for complex problems
3. Implement the solution
4. Run tests to verify
5. Run Pint to format
6. Commit changes
```

#### 2. Test-Driven Development

```
1. Write tests based on expected behavior
2. Run tests to confirm they fail
3. Commit tests
4. Generate code to pass tests
5. Iterate until all tests pass
6. Commit implementation
```

#### 3. Feature Implementation

```
1. Review requirements and design documents
2. Check existing similar implementations
3. Create necessary models, migrations, factories
4. Implement service layer logic
5. Create controllers and routes
6. Write comprehensive tests
7. Verify all tests pass
8. Format code with Pint
9. Commit changes
```

---

## Quality Assurance

### Code Review Checklist

Before finalizing any code changes, verify:

- [ ] Follows existing code conventions
- [ ] Uses descriptive variable and method names
- [ ] Includes proper type declarations
- [ ] Has appropriate test coverage
- [ ] Follows Laravel best practices
- [ ] Uses proper Eloquent relationships
- [ ] Implements proper validation via Form Requests
- [ ] Follows Laravel 12 structure guidelines
- [ ] Passes code formatting standards (Pint)
- [ ] Passes static analysis (Larastan)
- [ ] Includes necessary documentation (when requested)
- [ ] No security vulnerabilities
- [ ] No N+1 query problems
- [ ] Proper error handling

### Performance Considerations

- **Prevent N+1 queries** with eager loading
- Use **queued jobs** for time-consuming operations
- Leverage **Laravel's built-in caching** mechanisms (Redis)
- Optimize database queries using Eloquent best practices
- Use **database indexes** appropriately
- Implement **query result caching** where beneficial

### Testing Requirements

- **80%+ test coverage** for critical components
- All new features must have tests
- All bug fixes must have regression tests
- Tests must pass before committing
- Use factories for test data
- Avoid mocks when possible

---

## Project-Specific Guidelines

### Umamusume Career Planner Specifics

**Domain Concepts:**

- **Characters**: Stats (0-1200 range), aptitudes (G-SS ratings)
- **Training**: URA Finale vs Unity Cup scenarios
- **Skills**: Evolution chains, hint-based SP cost reduction
- **Support Cards**: 6-card deck configuration
- **Careers**: 60-70 turn progression tracking

**Key Services:**

- `TrainingCalculationService` - Training outcome predictions
- `SkillEvolutionService` - Skill evolution and hint management
- `CharacterStateService` - Character state tracking
- `DeckOptimizationService` - Support card deck optimization

**External Integrations:**

- **Ollama**: Local AI models (primary)
- **AWS Bedrock**: Cloud AI fallback
- **umapyoi.net**: Game data API
- **Tesseract OCR**: Screenshot processing

**Performance Targets:**

- Core features: <2 seconds
- AI recommendations: <3 seconds (local), <5 seconds (cloud)
- Database queries: <500ms
- User interactions: <100ms feedback

---

## Additional Resources

### Internal Documentation

- `.kiro/steering/product.md` - Product overview
- `.kiro/steering/tech.md` - Technology stack and commands
- `.kiro/steering/structure.md` - Project structure
- `docs/` - Comprehensive feature documentation
- `docs/ai-coding-assistant-best-practices.md` - Detailed research compilation

### External Resources

- [Laravel 12 Documentation](https://laravel.com/docs/12.x)
- [Pest Documentation](https://pestphp.com)
- [Tailwind CSS v4 Documentation](https://tailwindcss.com)
- [Laravel AI Development Guide](https://laravel.com/docs/12.x/ai)

---

## Conclusion

This document serves as the authoritative guide for AI coding assistants working on this Laravel application. Following these guidelines ensures consistent, maintainable, and high-quality code that aligns with Laravel best practices and project-specific requirements.

**Key Takeaways:**

1. **Context is Critical** - Provide relevant files, imports, and project structure
2. **Be Specific** - Clear, detailed instructions yield better results
3. **Validate Everything** - Test, review, and verify all AI-generated code
4. **Iterate Continuously** - First attempts are rarely perfect
5. **Prioritize Security** - Never trust AI-generated code without security review
6. **Follow Laravel Conventions** - Leverage Laravel's opinionated structure
7. **Test Thoroughly** - Use Pest framework for comprehensive testing
8. **Maintain Quality** - Run Pint and Larastan before committing

---

**Document Version:** 1.0  
**Last Updated:** January 2026  
**Based On:** Official recommendations from Anthropic, Amazon, GitHub, OpenAI, Google, Cursor, and JetBrains  
**Review Status:** Ready for team adoption

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
