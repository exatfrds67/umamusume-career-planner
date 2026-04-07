# Developer Guide

This guide provides comprehensive documentation for developers working on the Umamusume Career Planner application.

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Getting Started](#getting-started)
3. [Project Structure](#project-structure)
4. [Development Workflow](#development-workflow)
5. [Testing](#testing)
6. [API Development](#api-development)
7. [Database](#database)
8. [Frontend Development](#frontend-development)
9. [AI Integration](#ai-integration)
10. [Deployment](#deployment)

---

## Architecture Overview

### Technology Stack

| Layer | Technology |
| --- | --- |
| Backend | Laravel 12 (PHP 8.4) |
| Frontend | Livewire 4, Blade, Alpine.js 3, Tailwind CSS v4 |
| Database | SQLite (dev), MySQL/PostgreSQL (prod) |
| Cache | Redis |
| Queue | Laravel Horizon |
| Testing | Pest v4 / PHPUnit v12 |
| AI | Ollama, AWS Bedrock, Neuron AI v2.11 |

### System Architecture

```text
┌─────────────────────────────────────────────────────────────┐
│                        Client Layer                          │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐  │
│  │   Browser   │  │  PWA/Mobile │  │   API Consumers     │  │
│  └─────────────┘  └─────────────┘  └─────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                     Application Layer                        │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐  │
│  │ Controllers │  │  Services   │  │   API Resources     │  │
│  └─────────────┘  └─────────────┘  └─────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                       Data Layer                             │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐  │
│  │   Models    │  │    Cache    │  │      Queue          │  │
│  └─────────────┘  └─────────────┘  └─────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    External Services                         │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐  │
│  │ AWS Bedrock │  │  External   │  │   MCP Servers       │  │
│  │     AI      │  │    APIs     │  │                     │  │
│  └─────────────┘  └─────────────┘  └─────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

---

## Getting Started

### Prerequisites

- PHP 8.4+
- Composer 2.x
- Node.js 20+
- npm or yarn
- Redis (optional for development)

### Installation

```bash
# Clone the repository
git clone <repository-url>
cd umamusume-career-planner

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed the database (optional)
php artisan db:seed

# Build frontend assets
npm run build

# Start development server
composer run dev
```text

### Environment Configuration

Key environment variables:

```env
# Application
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database.sqlite

# Cache & Queue
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1

# AI Services
AI_ENABLED=true
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=us-east-1
```

---

## Project Structure

```text
├── app/
│   ├── Console/Commands/     # Artisan commands
│   ├── Http/
│   │   ├── Controllers/      # HTTP controllers
│   │   ├── Middleware/       # Request middleware
│   │   ├── Requests/         # Form request validation
│   │   └── Resources/        # API resources
│   ├── Enums/                # PHP enums (8 enums)
│   ├── Livewire/             # Livewire 4 components (AdvisoryPanel, etc.)
│   ├── Models/               # Eloquent models (~40 models)
│   ├── Neuron/               # Neuron AI agents & tools
│   ├── Policies/             # Authorization policies
│   ├── Providers/            # Service providers
│   ├── Repositories/         # Data repositories
│   ├── Services/             # Business logic services (70+)
│   │   ├── Neuron/           # Neuron AI services
│   │   ├── MCP/              # MCP integration (42 tools)
│   │   └── ...               # Domain services
│   └── ValueObjects/         # Domain value objects
├── bootstrap/                # Application bootstrap
├── config/                   # Configuration files
├── database/
│   ├── factories/            # Model factories
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
├── docs/                     # Documentation
├── public/                   # Public assets
├── resources/
│   ├── css/                  # Stylesheets
│   ├── js/                   # JavaScript
│   └── views/                # Blade templates
├── routes/                   # Route definitions
├── storage/                  # Application storage
└── tests/
    ├── Feature/              # Feature tests
    ├── Unit/                 # Unit tests
    ├── Integration/          # Integration tests
    └── Architecture/         # Architecture tests
```

---

## Development Workflow

### Coding Standards

We follow Laravel best practices and PSR-12 coding standards.

**PHP:**

- Use type declarations for all parameters and return types
- Use constructor property promotion
- Prefer Eloquent over raw queries
- Use Form Request classes for validation

**JavaScript:**

- Use ES6+ syntax
- Follow Alpine.js conventions
- Use Tailwind CSS for styling

### Code Formatting

```bash
# Format PHP code
vendor/bin/pint

# Format only changed files
vendor/bin/pint --dirty
```text

### Static Analysis

```bash
# Run PHPStan
vendor/bin/phpstan analyse
```

### Git Workflow

1. Create a feature branch from `develop`
2. Make your changes
3. Write/update tests
4. Run tests and linting
5. Create a pull request
6. Code review
7. Merge to `develop`

---

## Testing

### Running Tests

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/CharacterApiTest.php

# Run tests matching a filter
php artisan test --filter=CharacterApi

# Run in parallel
php artisan test --parallel

# Run specific test suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
php artisan test --testsuite=Architecture
```text

### Writing Tests

Use Pest v4 for all tests:

```php
<?php

use App\Models\Character;
use App\Models\User;

it('creates a character', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/api/v1/characters', [
            'name' => 'Test Character',
            'scenario_type' => 'ura_finale',
        ]);

    $response->assertCreated()
        ->assertJsonStructure(['data' => ['id', 'name']]);
});
```

### Test Organization

- **Unit Tests**: Test individual classes/methods in isolation
- **Feature Tests**: Test HTTP endpoints and user workflows
- **Integration Tests**: Test component interactions
- **Architecture Tests**: Verify code structure

---

## API Development

### API Versioning

All API routes are versioned under `/api/v1/`.

### Creating Endpoints

1. Create a controller:

```bash
php artisan make:controller Api/V1/NewController --api
```text

1. Create a Form Request:

```bash
php artisan make:request Api/V1/NewRequest
```

1. Create an API Resource:

```bash
php artisan make:resource Api/V1/NewResource
```text

1. Add routes to `routes/api.php`

### API Response Format

```json
{
    "data": {
        "id": 1,
        "name": "Example",
        "created_at": "2026-01-20T12:00:00Z"
    },
    "meta": {
        "current_page": 1,
        "total": 100
    }
}
```

### Error Response Format

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "name": ["The name field is required."]
    }
}
```text

### Authentication

API authentication uses Laravel Sanctum:

```php
// Protected route
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('characters', CharacterController::class);
});
```

---

## Database

### Migrations

```bash
# Create migration
php artisan make:migration create_table_name_table

# Run migrations
php artisan migrate

# Rollback
php artisan migrate:rollback

# Fresh migration with seeding
php artisan migrate:fresh --seed
```text

### Models

```bash
# Create model with factory, migration, and seeder
php artisan make:model ModelName -fms
```

### Relationships

Define relationships with return types:

```php
public function careers(): HasMany
{
    return $this->hasMany(Career::class);
}
```text

### Query Optimization

- Use eager loading to prevent N+1 queries
- Add indexes for frequently queried columns
- Use chunking for large datasets

```php
// Good - eager loading
Character::with(['careers', 'aptitudes'])->get();

// Bad - N+1 queries
$characters = Character::all();
foreach ($characters as $character) {
    $character->careers; // N+1!
}
```

---

## Frontend Development

### Blade Components

Create reusable components:

```bash
php artisan make:component Button
```text

### Alpine.js

Use Alpine.js for interactivity:

```html
<div x-data="{ open: false }">
    <button @click="open = !open">Toggle</button>
    <div x-show="open">Content</div>
</div>
```

### Tailwind CSS v4

Configure in `resources/css/app.css`:

```css
@import "tailwindcss";

@theme {
    --color-primary: oklch(0.72 0.11 178);
}
```text

### Asset Building

```bash
# Development with hot reload
npm run dev

# Production build
npm run build
```

---

## AI Integration

### Ollama (Local AI)

Configure in `config/neuron.php` for local AI inference:

```php
'ollama' => [
    'host' => env('OLLAMA_HOST', 'http://localhost:11434'),
    'model' => env('OLLAMA_MODEL', 'llama3'),
],
```text

### AWS Bedrock (Cloud AI)

Configure in `config/ai.php`:

```php
'bedrock' => [
    'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    'model' => env('BEDROCK_MODEL', 'anthropic.claude-3-sonnet'),
],
```

### Using AI Services

```php
use App\Services\Neuron\NeuronAIService;

$aiService = app(NeuronAIService::class);
$response = $aiService->generateResponse($prompt, $context);
```text

### MCP Integration

MCP servers provide tool capabilities:

```php
use App\Services\MCP\MCPClientService;

$mcpClient = app(MCPClientService::class);
$result = $mcpClient->executeTool('tool_name', $parameters);
```

---

## Deployment

### Production Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure production database
- [ ] Set up Redis for cache/queue
- [ ] Configure SSL/HTTPS
- [ ] Set up monitoring
- [ ] Configure backups

### Deployment Commands

```bash
# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Run migrations
php artisan migrate --force

# Build assets
npm run build
```text

### Environment Variables

Production-specific variables:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=your-db-name

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

---

## Contributing

### Pull Request Process

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Write tests
5. Run `vendor/bin/pint`
6. Run `php artisan test`
7. Submit PR with description

### Code Review Guidelines

- All PRs require at least one approval
- Tests must pass
- Code must be formatted
- Documentation must be updated

---

Last updated: February 2026
