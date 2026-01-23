# Technology Stack

## Core Framework

- **PHP**: 8.4.11
- **Laravel**: v12 (released February 24, 2025)
- **Database**: MySQL 8.0+ with Redis (WSL) for caching
- **Frontend**: Tailwind CSS v4, Alpine.js v3
- **Build Tool**: Vite v7

## Key Dependencies

### Backend

- `laravel/sanctum` v4 - API authentication
- `laravel/horizon` v5 - Queue management
- `laravel/telescope` - Debugging and monitoring
- `aws/aws-sdk-php` - AWS Bedrock integration
- `cloudstudio/ollama-laravel` - Local AI integration
- `symfony/dom-crawler` - HTML parsing
- `laravel/livewire` v3 - Full-stack reactivity
- `pestphp/pest` v4 - Testing framework

### Frontend

- `alpinejs` v3 - Reactive components
- `@alpinejs/persist` - State persistence
- `tailwindcss` v4 - Utility-first CSS
- `@tailwindcss/vite` - Vite integration
- `vite` v7 - Build tool

### Development

- `pestphp/pest` v4 - Testing framework
- `phpunit/phpunit` v12 - Unit testing
- `laravel/pint` v1 - Code formatting
- `larastan/larastan` v3 - Static analysis
- `laravel/sail` v1 - Docker development

## Common Commands

### Development

```bash
# Start development server with all services
composer run dev

# Individual services
php artisan serve
php artisan queue:listen --tries=1
php artisan pail --timeout=0
npm run dev
```

### Testing

```bash
# Run all tests
composer test
# or
php artisan test --compact

# Run specific test suites
composer test:unit
composer test:feature
composer test:integration
composer test:architecture

# Run tests in parallel
composer test:parallel

# Code coverage
composer test:coverage
composer test:coverage-html
```

### Code Quality

```bash
# Format code
vendor/bin/pint

# Static analysis
vendor/bin/phpstan analyse

# Check specific files
vendor/bin/pint --dirty
```

### Database

```bash
# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Fresh migration with seeding
php artisan migrate:fresh --seed
```

### Cache Management

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Warm cache
php artisan cache:warm
```

### Redis (WSL)

```powershell
# Check Redis status
wsl bash -c "redis-cli ping"

# Restart Redis
wsl bash -c "sudo service redis-server restart"

# Check Redis health
php artisan redis:health --detailed
```

## Build System

### Frontend Build

```bash
# Development build with watch
npm run dev

# Production build
npm run build
```

### Asset Management

- Vite handles all frontend asset compilation
- Tailwind CSS v4 with zero configuration
- Hot module replacement in development
- Optimized production builds with code splitting

## Environment Setup

### Required Services

- **XAMPP**: Apache + MySQL
- **WSL**: Redis server
- **Node.js**: v18+ for frontend builds
- **Composer**: PHP dependency management

### Configuration Files

- `.env` - Environment variables
- `phpunit.xml` - Test configuration
- `phpstan.neon` - Static analysis rules
- `vite.config.js` - Frontend build configuration
- `tailwind.config.js` - Tailwind customization (if needed)

## Testing Framework

### Pest Configuration

- All tests use Pest v4 framework
- Test suites: Unit, Feature, Integration, Architecture
- SQLite in-memory database for testing
- Array cache driver for test isolation

### Test Execution

- Use `--compact` flag for cleaner output
- Use `--filter` to run specific tests
- Parallel execution available with `--parallel`
- Coverage requires PCOV or Xdebug extension

## Performance Optimization

### Caching Strategy

- Redis for session, cache, and queue
- Database query result caching
- Eloquent strict mode to prevent N+1 queries
- Connection pooling for database operations

### Response Time Targets

- Core features: <2 seconds
- AI recommendations: <3 seconds (local), <5 seconds (cloud)
- Database queries: <500ms
- User interactions: <100ms feedback

## External Integrations

### AI Services

- **Ollama**: Local AI models (primary)
- **AWS Bedrock**: Cloud AI fallback (Claude, Nova)

### External APIs

- **umapyoi.net**: Game data (character info, skills, rates)
- **UmamusumeDB.com**: Community calculator tools

### OCR Processing

- **Tesseract**: Optical character recognition
- **OpenCV**: Image preprocessing
- **GD**: Image manipulation

## Architecture Patterns

### Service Layer

- Business logic decoupled from controllers
- Dependency injection for testability
- Clear separation of concerns

### Repository Pattern

- Data access abstraction
- Eloquent models wrapped with repositories
- Consistent query interface

### Events & Listeners

- Decoupled event handling
- Database event triggers
- Queue integration

## Security

### Authentication

- Laravel Sanctum for API token auth
- Session-based web auth
- CSRF protection on all state-changing requests

### Data Protection

- Input validation and sanitization
- SQL injection prevention via Eloquent
- XSS protection via Blade templating
- Rate limiting on API endpoints

## Monitoring & Logging

### Development Tools

- Laravel Telescope for debugging
- Laravel Horizon for queue monitoring
- Laravel Pail for real-time logs

### Production Ready

- Structured logging
- Error tracking integration ready
- Performance monitoring hooks

## Deployment

### Build Artifacts

- Compiled frontend assets in `public/build/`
- Optimized CSS and JavaScript
- Source maps for debugging

### Database Migrations

- Version controlled schema changes
- Reversible migrations
- Seed data support

### Environment Variables

- Development, staging, production configs
- Sensitive data in `.env` (not version controlled)
- Docker support via Laravel Sail
