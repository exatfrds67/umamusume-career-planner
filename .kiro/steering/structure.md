# Project Structure

## Directory Organization

### Application Core (`app/`)

#### Console

- `Console/Commands/` - Artisan commands (auto-discovered, no registration needed)

#### Http Layer

- `Http/Controllers/` - Request handlers for API and web routes
- `Http/Middleware/` - Request/response filters (configured in `bootstrap/app.php`)
- `Http/Requests/` - Form request validation classes
- `Http/Resources/` - API resource transformers

#### Domain Logic

- `Models/` - Eloquent models with relationships
- `Policies/` - Authorization policies
- `Repositories/` - Repository pattern implementations (if used)

#### Services

- `Services/` - Business logic and domain services
  - `Services/CareerRun/` - Career run operations
  - `Services/Training/` - Training prediction logic
  - `Services/Race/` - Race strategy and outcomes
  - `Services/Skill/` - Skill management and SP calculations
  - `Services/Import/` - Data import adapters
  - `Services/Export/` - Data export formatters
  - `Services/Data/` - Data migration and conversion
  - `Services/Storage/` - Local and Account storage coordination

#### Livewire Components

- `Livewire/` - Full-page and reusable components
  - `Livewire/CareerRun/` - Plan management components
  - `Livewire/Skills/` - Skill management components
  - `Livewire/Stats/` - Statistics and tracking components
  - `Livewire/Import/` - Import wizard components
  - `Livewire/Dashboard/` - Dashboard and overview components

#### Support

- `Helpers/` - Helper functions and utilities
- `Jobs/` - Queueable background jobs
- `Providers/` - Service providers
- `View/Components/` - Blade components
- `Enums/` - PHP 8.1 Enums (StorageMode, RunStatus, etc.)
- `Exceptions/` - Custom exception classes

### Configuration (`config/`)

Key configuration files:

- `app.php` - Application settings
- `database.php` - Database connections
- `cache.php` - Cache driver configuration
- `queue.php` - Queue configuration
- `filesystems.php` - Storage disk configuration

### Database (`database/`)

- `migrations/` - Database schema migrations (timestamped)
  - `*_create_users_table.php`
  - `*_create_career_runs_table.php`
  - `*_create_stat_progress_table.php`
  - `*_create_skills_table.php`
  - `*_create_skill_career_runs_table.php`
  - `*_create_race_predictions_table.php`
  - `*_create_goals_table.php`
  - `*_create_activity_logs_table.php`
- `factories/` - Model factories for testing
  - `UserFactory.php`
  - `CareerRunFactory.php`
  - `StatProgressFactory.php`
  - `SkillFactory.php`
- `seeders/` - Database seeders
  - `DatabaseSeeder.php`
  - `CharacterSeeder.php`
  - `SkillSeeder.php`

### Resources (`resources/`)

- `css/` - Tailwind CSS source files
  - `app.css` - Main stylesheet with custom stat colors
- `js/` - JavaScript and Alpine.js
  - `app.js` - Entry point
  - `stores/` - Alpine.js stores
    - `localRuns.js` - Local storage coordination
    - `preferences.js` - User preferences
    - `drafts.js` - Draft auto-save
- `views/` - Blade templates
  - `layouts/` - Layout templates
  - `components/` - Reusable Blade components
    - `forms/` - Form components
    - `buttons/` - Button variants
    - `cards/` - Card containers
    - `stats/` - Stat display components
    - `badges/` - Badge components
  - `livewire/` - Livewire component views

### Routes (`routes/`)

- `web.php` - Web routes (public and auth)
- `api.php` - API routes (optional)
- `console.php` - Console commands

### Testing (`tests/`)

- `Unit/` - Unit tests for services and helpers
  - `Services/` - Service layer tests
  - `Enums/` - Enum tests
  - `Helpers/` - Helper function tests
- `Feature/` - Feature tests for Livewire components and HTTP endpoints
  - `CareerRun/` - Career run feature tests
  - `Skills/` - Skill management tests
  - `Import/` - Import workflow tests
  - `Export/` - Export workflow tests
- `Browser/` - Browser/E2E tests (if using Laravel Dusk)
- `Pest.php` - Pest configuration and global helpers
- `TestCase.php` - Base test case class

### Documentation (`docs/`)

Comprehensive documentation:

- `prds/` - Product requirement documents (7 total)
- `specs/` - Technical specifications (7 total)
- `flows/` - User workflow diagrams
- `sequences/` - Sequence diagrams
- `tech-flow/` - Technical flow documentation
- `wireframes/` - UI/UX wireframes
- `implementation-summaries/` - Task completion summaries

### Public Assets (`public/`)

- `build/` - Compiled frontend assets (Vite output)
- `images/` - Public images and icons
- `storage/` - Symlinked storage directory

### Storage (`storage/`)

- `app/` - Application files
  - `app/public/` - Public-facing files
  - `app/uploads/` - User uploads
- `framework/` - Framework cache and sessions
  - `framework/cache/` - Application cache
  - `framework/sessions/` - Session files
  - `framework/views/` - Compiled views
- `logs/` - Application logs
  - `laravel.log` - Main application log

### Bootstrap (`bootstrap/`)

- `app.php` - Application bootstrap (middleware, exceptions, routing)
- `providers.php` - Service provider registration
- `cache/` - Bootstrap cache directory

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

- Interfaces in `app/Repositories/Contracts/`
- Implementations: `Eloquent{Model}Repository`
- Bound in service providers

### Service Layer

- Business logic extracted from controllers
- Services organized by domain (CareerRun, Training, Race, etc.)
- Dependency injection for testability
- Single Responsibility Principle (one service = one domain concern)

### Form Requests

- All validation in dedicated Form Request classes
- Located in `app/Http/Requests/`
- Include both rules and custom error messages
- Support nested validation for complex forms

### API Resources

- Eloquent API Resources for response transformation
- Located in `app/Http/Resources/`
- Support API versioning through resource classes

### Livewire Components

- Full-page components in subdirectories
- Reusable components at component level
- Property casting for type safety
- Validation in `#[Validate]` attributes

## File Naming Conventions

### Models

- Singular, PascalCase: `Character.php`, `SupportCard.php`
- Relationships use proper return types
- Include relationship methods with return type hints

### Controllers

- Singular resource name + Controller: `CharacterController.php`
- RESTful method names preferred (index, show, create, store, edit, update, destroy)
- Thin controllers delegating to services

### Migrations

- Timestamp prefix: `2026_01_12_030016_create_characters_table.php`
- Descriptive action + table name
- Up/down methods for reversibility

### Tests

- Descriptive test names: `CharacterManagementTest.php`
- Use Pest framework syntax with descriptive test names
- Organized in Feature or Unit subdirectories
- Naming: `testUserCanCreateCharacter()`, `testValidatesCharacterName()`

### Services

- Descriptive name + Service: `TrainingCalculationService.php`
- Organized in domain-specific subdirectories
- One primary responsibility per service
- Dependency injection for collaborators

### Livewire Components

- PascalCase directory names: `CareerRun/`, `Skills/`
- PascalCase component files: `PlanList.php`, `SkillEditor.php`
- Corresponding views: `resources/views/livewire/career-run/plan-list.blade.php`

### Blade Components

- Kebab-case subdirectories: `forms/`, `buttons/`, `cards/`
- Kebab-case component names: `stat-bar.blade.php`, `skill-card.blade.php`
- Usage: `<x-stat-bar :value="$stat" :label="$label" />`

## Database Schema Organization

Key tables organized by concern:

### Authentication & Users

- `users` - User accounts

### Reference Data (Static)

- `uma_musumes` - Character reference
- `skills` - Skill reference

### Transactional Data (Core)

- `career_runs` - Career run tracking
- `stat_progress` - Turn-by-turn stats
- `skill_career_runs` - Skill acquisitions

### Supporting Data

- `race_predictions` - Race planning
- `goals` - Training objectives
- `activity_logs` - User actions

All tables follow canonical naming conventions (documented in D09).

## Caching Strategy

- Route model binding uses eager loading
- Query caching in service layer for expensive operations
- Cache invalidation on data mutations
- Redis for session storage in production

## Asset Compilation

- Vite v7 for asset bundling
- Tailwind CSS v4 for styling
- Alpine.js v3 for interactivity
- npm scripts for dev/build: `npm run dev`, `npm run build`

## Testing Directory Organization

- `Unit/Services/` - Service logic tests
- `Unit/Models/` - Model relationship tests
- `Feature/Livewire/` - Component interaction tests
- `Feature/Http/` - API endpoint tests
- `Browser/` - End-to-end user flow tests

## Configuration Management

- Environment variables in `.env` file
- Sensitive values never committed to source control
- `.env.example` maintained with all required keys
- Configuration organized by concern in `config/` directory
