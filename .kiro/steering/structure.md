# Project Structure

## Directory Organization

### Application Core (`app/`)

#### Console

- `Console/Commands/` - Artisan commands (auto-discovered, no registration needed)

#### HTTP Layer

- `Http/Controllers/` - Request handlers
- `Http/Middleware/` - Request/response filters (configured in `bootstrap/app.php`)
- `Http/Requests/` - Form request validation classes
- `Http/Resources/` - API resource transformers

#### Domain Logic

- `Models/` - Eloquent models with relationships
- `Policies/` - Authorization policies
- `Repositories/` - Repository pattern implementations

#### Services

- `Services/` - Business logic and domain services
- `Services/Agents/` - AI agent coordination
- `Services/AI/` - AI processing services
- `Services/ExternalAPI/` - External API integrations
- `Services/MCP/` - Model Context Protocol services
- `Services/OCR/` - OCR processing services

#### Support

- `Helpers/` - Helper functions and utilities
- `Jobs/` - Queueable background jobs
- `Providers/` - Service providers
- `View/Components/` - Blade components

### Configuration (`config/`)

Key configuration files:

- `ai.php` - AI service configuration
- `ai_agents.php` - Agent coordination settings
- `aws.php` - AWS Bedrock configuration
- `cache.php` - Cache driver configuration
- `database.php` - Database connections
- `external-apis.php` - External API settings
- `mcp.php` - MCP server configuration
- `mcp_tools.php` - MCP tool settings
- `queue.php` - Queue configuration

### Database (`database/`)

- `migrations/` - Database schema migrations (timestamped)
- `factories/` - Model factories for testing
- `seeders/` - Database seeders

### Resources (`resources/`)

- `css/` - Tailwind CSS source files
- `js/` - JavaScript/Alpine.js components
- `views/` - Blade templates

### Routes (`routes/`)

- `web.php` - Web routes
- `api.php` - API routes
- `console.php` - Console commands

### Testing (`tests/`)

- `Architecture/` - Architecture tests
- `Feature/` - Feature tests (primary)
- `Integration/` - Integration tests
- `Unit/` - Unit tests
- `Pest.php` - Pest configuration
- `TestCase.php` - Base test case

### Documentation (`docs/`)

Comprehensive documentation organized by type:

- `prds/` - Product requirement documents
- `specs/` - Technical specifications
- `flows/` - User workflow diagrams
- `sequences/` - Sequence diagrams
- `tech-flow/` - Technical flow documentation
- `feature-documentation/` - Feature implementation docs
- `implementation-summaries/` - Task completion summaries
- `testing/` - Testing guides
- `mcp-integration/` - MCP server documentation

### Public Assets (`public/`)

- `build/` - Compiled frontend assets (Vite output)
- `images/` - Public images
- `storage/` - Symlinked storage directory

### Storage (`storage/`)

- `app/` - Application files
- `framework/` - Framework cache and sessions
- `logs/` - Application logs

### Bootstrap (`bootstrap/`)

- `app.php` - Application bootstrap (middleware, exceptions, routing)
- `providers.php` - Service provider registration

## Laravel 12 Structure Notes

### Middleware Registration

- No `app/Http/Kernel.php` - middleware configured in `bootstrap/app.php`
- Use `Application::configure()->withMiddleware()` for middleware setup

### Console Commands

- No `app/Console/Kernel.php` - commands auto-discovered from `app/Console/Commands/`
- Console configuration in `bootstrap/app.php` or `routes/console.php`

### Service Providers

- Application-specific providers in `bootstrap/providers.php`
- Framework providers auto-discovered

## Key Architectural Patterns

### Repository Pattern

- Interfaces in `app/Repositories/`
- Implementations follow naming: `Eloquent{Model}Repository`
- Bound in service providers

### Service Layer

- Business logic extracted from controllers
- Services organized by domain (AI, External API, MCP, OCR)
- Dependency injection for testability

### Form Requests

- All validation in dedicated Form Request classes
- Located in `app/Http/Requests/`
- Include both rules and custom error messages

### API Resources

- Eloquent API Resources for response transformation
- Located in `app/Http/Resources/`
- Support API versioning

## File Naming Conventions

### Models

- Singular, PascalCase: `Character.php`, `SupportCard.php`
- Relationships use proper return types

### Controllers

- Singular resource name + Controller: `CharacterController.php`
- RESTful method names preferred

### Migrations

- Timestamp prefix: `2026_01_12_030016_create_characters_table.php`
- Descriptive action + table name

### Tests

- Descriptive test names: `CharacterManagementTest.php`
- Use Pest framework syntax
- Co-locate with source when using `.test.ts` suffix

### Services

- Descriptive name + Service: `TrainingCalculationService.php`
- Organized in domain-specific subdirectories

## Database Schema

### Core Tables

- `users` - User accounts
- `characters` - Character profiles
- `careers` - Career progression
- `training_sessions` - Training history
- `races` - Race information
- `skills` - Skill database
- `skill_acquisitions` - Acquired skills
- `skill_hints` - Skill hints
- `support_cards` - Support card definitions
- `character_support_cards` - Deck configuration
- `aptitudes` - Character aptitudes
- `factors` - Inheritance factors

### AI & Integration

- `ai_conversations` - AI chat history
- `conversation_messages` - Chat messages
- `mcp_servers` - MCP server configuration
- `mcp_agents` - Agent definitions
- `mcp_tool_usage` - Tool usage tracking
- `external_data` - External API cache
- `ocr_extractions` - OCR processing results

### System

- `user_preferences` - User settings
- `cache` - Cache storage
- `jobs` - Queue jobs
- `failed_jobs` - Failed queue jobs

## Asset Organization

### Images

- `images/trainee_images/` - Character avatars
- `images/support_cards/` - Support card images
- `images/app_bg/` - Background images
- `images/app_logo/` - Application logos

### Frontend Assets

- Source: `resources/css/`, `resources/js/`
- Compiled: `public/build/`
- Vite handles compilation and optimization

## Configuration Management

### Environment Variables

- Only use `env()` in config files
- Access via `config()` helper in application code
- Example: `config('app.name')` not `env('APP_NAME')`

### Cache Configuration

- Redis primary (via WSL)
- Array driver for testing
- File driver fallback

### Queue Configuration

- Redis for production
- Sync for testing
- Horizon for monitoring

## Testing Structure

### Test Organization

- Feature tests: Primary test type
- Unit tests: Isolated component testing
- Integration tests: Multi-component interactions
- Architecture tests: Structural validation

### Test Data

- Use model factories for test data
- Check for custom factory states
- SQLite in-memory for test database

### Test Execution

- Run minimal tests with filters
- Use `--compact` for cleaner output
- Parallel execution available
