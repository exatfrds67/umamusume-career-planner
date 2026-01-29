# Claude AI Configuration & Technical Documentation

## Project Context

This project uses Claude AI as the primary development assistant with persistent memory capabilities through the Memory MCP Server. The configuration focuses on AWS Bedrock integration for enhanced performance and cost management.

## AWS Bedrock Configuration

### Overview

This project is configured to use Claude via AWS Bedrock instead of Anthropic's direct API to avoid credit limitations and provide better performance for development workflows.

### Prerequisites

- AWS account with Bedrock access enabled
- Access to Claude models (Claude Sonnet 4.5) in Bedrock
- AWS CLI installed and configured (optional)
- Appropriate IAM permissions

### Initial Setup

#### 1. Submit Use Case Details (First-time users)

1. Navigate to [Amazon Bedrock console](https://console.aws.amazon.com/bedrock/)
2. Select **Chat/Text playground**
3. Choose any Anthropic model and fill out the use case form (required once per account)

#### 2. AWS Credentials Configuration

Choose one of these authentication methods:

##### Option A: AWS CLI Configuration (Recommended)

```bash
aws configure
# Enter your AWS Access Key ID and Secret Access Key
# Region: us-east-1
# Output format: json
```

##### Option B: Environment Variables (Access Key)

```powershell
# PowerShell (Windows)
$env:AWS_ACCESS_KEY_ID = "your-access-key-id"
$env:AWS_SECRET_ACCESS_KEY = "your-secret-access-key"
$env:AWS_SESSION_TOKEN = "your-session-token"  # if using temporary credentials
```

##### Option C: Bedrock API Keys (Simplest)

```powershell
$env:AWS_BEARER_TOKEN_BEDROCK = "your-bedrock-api-key"
```

##### Option D: SSO Profile

```bash
aws sso login --profile=your-profile-name
export AWS_PROFILE=your-profile-name
```

## Environment Variables Configuration

### Required Claude Code Variables

```powershell
# Enable Bedrock integration
$env:CLAUDE_CODE_USE_BEDROCK = "1"
$env:AWS_REGION = "us-east-1"  # Required - Claude Code doesn't read from .aws config

# Optional: Override region for small/fast model (Haiku)
$env:ANTHROPIC_SMALL_FAST_MODEL_AWS_REGION = "us-west-2"
```

### Model Configuration (Optional)

```powershell
# Default models (these are already set by default):
# Primary: global.anthropic.claude-sonnet-4-5-20250929-v1:0
# Small/Fast: us.anthropic.claude-haiku-4-5-20251001-v1:0

# To customize models:
$env:ANTHROPIC_MODEL = "global.anthropic.claude-sonnet-4-5-20250929-v1:0"
$env:ANTHROPIC_SMALL_FAST_MODEL = "us.anthropic.claude-haiku-4-5-20251001-v1:0"

# For Haiku 4.5 (manual upgrade required):
$env:ANTHROPIC_DEFAULT_HAIKU_MODEL = "us.anthropic.claude-haiku-4-5-20251001-v1:0"

# Optional: Disable prompt caching if needed
$env:DISABLE_PROMPT_CACHING = "1"
```

### Performance Optimization Settings

```powershell
# Recommended for Bedrock (prevents burndown throttling issues)
$env:CLAUDE_CODE_MAX_OUTPUT_TOKENS = "4096"
$env:MAX_THINKING_TOKENS = "1024"
```

### Setting Persistent Environment Variables

```powershell
[System.Environment]::SetEnvironmentVariable("CLAUDE_CODE_USE_BEDROCK", "1", "User")
[System.Environment]::SetEnvironmentVariable("AWS_REGION", "us-east-1", "User")
[System.Environment]::SetEnvironmentVariable("CLAUDE_CODE_MAX_OUTPUT_TOKENS", "4096", "User")
[System.Environment]::SetEnvironmentVariable("MAX_THINKING_TOKENS", "1024", "User")
```

## IAM Policy Configuration

### Required IAM Policy JSON

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Sid": "AllowModelAndInferenceProfileAccess",
      "Effect": "Allow",
      "Action": [
        "bedrock:InvokeModel",
        "bedrock:InvokeModelWithResponseStream",
        "bedrock:ListInferenceProfiles"
      ],
      "Resource": [
        "arn:aws:bedrock:*:*:inference-profile/*",
        "arn:aws:bedrock:*:*:application-inference-profile/*",
        "arn:aws:bedrock:*:*:foundation-model/*"
      ]
    },
    {
      "Sid": "AllowMarketplaceSubscription",
      "Effect": "Allow",
      "Action": [
        "aws-marketplace:ViewSubscriptions",
        "aws-marketplace:Subscribe"
      ],
      "Resource": "*",
      "Condition": {
        "StringEquals": {
          "aws:CalledViaLast": "bedrock.amazonaws.com"
        }
      }
    }
  ]
}
```

### Applying IAM Policy

1. Go to [AWS IAM Console](https://console.aws.amazon.com/iam/)
2. Navigate to **Policies** → **Create Policy**
3. Choose **JSON** tab and paste the policy above
4. Name it `Claude_Code_IAM_Policy`
5. Attach this policy to your IAM user or role

## Configuration Verification

### Testing Your Setup

1. Launch Claude Code: `claude`
2. Run `/status` command
3. Should show:
   - API provider: AWS Bedrock
   - AWS region: us-east-1
   - Model: your configured model

### Important Configuration Notes

- `/login` and `/logout` commands are disabled when using Bedrock
- `AWS_REGION` is required - Claude Code doesn't read from `.aws` config
- Claude Code uses Bedrock Invoke API, not Converse API
- Prompt caching may not be available in all regions

## Technical Troubleshooting

### AWS Bedrock Authentication Issues

#### "Credit balance too low" Error

**Issue:** This error indicates Claude Code is still using Anthropic's direct API instead of Bedrock.

**Diagnostic Steps:**

1. **Verify Environment Variables:**

```powershell
# Check if variables are set
echo $env:CLAUDE_CODE_USE_BEDROCK
echo $env:AWS_REGION
```

1. **Set Variables in Same Session:**

```powershell
$env:CLAUDE_CODE_USE_BEDROCK = "1"
$env:AWS_REGION = "us-east-1"
# Launch Claude Code from same session
claude
```

1. **Verify AWS Credentials:**

```bash
aws sts get-caller-identity
```

1. **Check Model Availability:**

```bash
aws bedrock list-inference-profiles --region us-east-1
```

**Common Resolution Patterns:**

- **Region Issues:** Switch to supported region (`us-east-1`, `us-west-2`)
- **On-demand throughput error:** Use inference profile IDs instead of model ARNs
- **Continuous "thinking":** Usually indicates authentication issues

**Working Solution Steps:**

1. Set environment variables in PowerShell
2. Launch Claude Code from the same session: `claude`
3. Verify with `/status` - should show "API provider: AWS Bedrock"
4. If still showing Anthropic API, restart terminal and try again

### Laravel Horizon on Windows/WSL

#### Problem Statement

Laravel Horizon requires PCNTL and POSIX PHP extensions for queue processing and monitoring, which are not available on Windows PHP installations.

#### Solution: WSL-Based Horizon Installation

Use Windows Subsystem for Linux (WSL) to run Laravel Horizon while keeping the main Laravel application on Windows.

#### WSL Setup Prerequisites

- Windows with WSL2 installed
- Redis running in WSL
- Laravel 12 project on Windows

#### Implementation Steps

##### 1. Verify WSL PHP and Extensions

```bash
# Check WSL PHP version
wsl php --version

# Verify required extensions are available
wsl bash -c "php -m | grep -E '(pcntl|posix)'"
```

##### 2. Upgrade WSL PHP to Match Project Requirements

```bash
# Update package lists
wsl sudo apt update

# Install PHP 8.4 and required extensions
wsl sudo apt install -y php8.4-cli php8.4-common php8.4-mysql php8.4-xml php8.4-curl php8.4-mbstring php8.4-zip php8.4-bcmath php8.4-intl php8.4-redis

# Verify installation
wsl php --version
# Should show PHP 8.4.x
```

##### 3. Install Laravel Horizon via WSL

```bash
# Install Horizon using WSL composer
wsl composer require laravel/horizon --dev

# Publish Horizon configuration
wsl php artisan horizon:install
```

##### 4. Configure Environment for Redis Queues

Update your `.env` file to use Redis for queues:

```env
# Cache Configuration
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Horizon Configuration
HORIZON_NAME="YourAppName"
HORIZON_PATH=horizon
```

#### Usage Instructions

**Web Application (Windows):**

```bash
php artisan serve
```

**Queue Processing (WSL):**

```bash
wsl php artisan horizon
```

**Horizon Dashboard:**

- Access at: `http://your-app.local/horizon`
- Monitor queues, failed jobs, and performance metrics

#### Troubleshooting Horizon Issues

**PHP Version Mismatch:**

- Ensure WSL PHP version matches project requirements
- Check composer dependencies for minimum PHP version

**Redis Connection Issues:**

```bash
# Start Redis in WSL if not running
wsl sudo service redis-server start

# Check Redis status
wsl sudo service redis-server status
```

**Permission Issues:**

```bash
# Fix Laravel storage permissions
wsl chmod -R 775 storage bootstrap/cache
```

**Horizon Not Starting:**

```bash
# Clear configuration cache
wsl php artisan config:clear

# Check Horizon status
wsl php artisan horizon:status
```

This solution enables full Laravel Horizon functionality on Windows development environments while maintaining the existing XAMPP setup for web serving.

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
