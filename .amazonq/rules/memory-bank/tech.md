# Technology Stack

## Programming Languages

### PHP 8.2+
- **Version**: ^8.2 (minimum requirement)
- **Features Used**: 
  - Typed properties and return types
  - Constructor property promotion
  - Named arguments
  - Match expressions
  - Enums for type safety

### JavaScript (ES6+)
- **Type**: ES Module syntax
- **Features Used**:
  - Arrow functions
  - Async/await
  - Destructuring
  - Template literals
  - Module imports/exports

### SQL
- **Dialect**: SQLite 3
- **Usage**: Database queries via Eloquent ORM

## Core Framework & Libraries

### Backend Dependencies

#### Laravel Framework 12
```json
"laravel/framework": "^12.0"
```
- Modern PHP web application framework
- Eloquent ORM for database operations
- Blade templating engine
- Artisan CLI for development tasks
- Built-in authentication and authorization

#### Laravel Packages
```json
"laravel/sanctum": "^4.2"      // API authentication
"laravel/tinker": "^2.10.1"    // REPL for debugging
```

#### AWS Integration
```json
"aws/aws-sdk-php": "^3.369"
```
- AWS Bedrock integration for Claude AI
- S3 storage capabilities
- AWS service authentication

#### AI/ML Integration
```json
"cloudstudio/ollama-laravel": "^1.1"
```
- Local LLM integration via Ollama
- Offline AI capabilities
- Model management

#### Utilities
```json
"symfony/dom-crawler": "^8.0"
```
- HTML/XML parsing for OCR processing
- Web scraping capabilities

### Frontend Dependencies

#### Tailwind CSS 4
```json
"tailwindcss": "^4.0.0"
"@tailwindcss/vite": "^4.0.0"
```
- Utility-first CSS framework
- Dark mode support
- Responsive design system
- Custom color schemes

#### Alpine.js 3
```json
"alpinejs": "^3.15.4"
"@alpinejs/persist": "^3.15.4"
```
- Lightweight reactive framework
- Component-based interactivity
- State persistence across sessions

#### Build Tools
```json
"vite": "^7.0.7"
"laravel-vite-plugin": "^2.0.0"
```
- Fast development server with HMR
- Optimized production builds
- Asset bundling and minification

#### HTTP Client
```json
"axios": "^1.11.0"
```
- Promise-based HTTP client
- Request/response interceptors
- CSRF token handling

## Development Dependencies

### Testing Framework
```json
"pestphp/pest": "^4.0"
"pestphp/pest-plugin-laravel": "^3.1"
"phpunit/phpunit": "^12.0"
"mockery/mockery": "^1.6"
```
- Modern PHP testing framework
- Laravel-specific testing helpers
- Mocking and stubbing capabilities

### Code Quality Tools
```json
"larastan/larastan": "^3.8"    // Static analysis
"laravel/pint": "^1.24"        // Code formatting
```
- PHPStan integration for Laravel
- Automatic code style fixing
- Type checking and error detection

### Development Tools
```json
"laravel/horizon": "^5.42"     // Queue monitoring
"laravel/telescope": "*"       // Application debugging
"laravel/pail": "^1.2.2"       // Log viewer
"laravel/boost": "^1.8"        // Performance optimization
"barryvdh/laravel-debugbar": "*"  // Debug toolbar
```

### Local Development
```json
"laravel/sail": "^1.41"        // Docker development environment
"fakerphp/faker": "^1.23"      // Fake data generation
```

### Build Utilities
```json
"concurrently": "^9.0.1"       // Run multiple commands
```

## Database

### SQLite 3
- **File**: `database/database.sqlite`
- **Advantages**: 
  - Zero configuration
  - Portable single-file database
  - Perfect for development and small deployments
  - ACID compliant

### Eloquent ORM
- Active Record pattern
- Relationship management
- Query builder
- Migration system
- Model factories and seeders

## Build System

### Vite 7
- **Config**: `vite.config.js`
- **Features**:
  - Lightning-fast HMR (Hot Module Replacement)
  - Optimized production builds
  - CSS code splitting
  - Asset optimization
  - Laravel integration

### Composer
- **Config**: `composer.json`
- **Features**:
  - PHP dependency management
  - PSR-4 autoloading
  - Custom scripts for automation

### NPM
- **Config**: `package.json`
- **Features**:
  - JavaScript dependency management
  - Build scripts
  - Development server

## Development Commands

### Setup & Installation
```bash
# Complete project setup
composer setup

# Manual setup steps
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
npm install
npm run build
```

### Development Server
```bash
# Run all development services concurrently
composer dev
# Runs: Laravel server, queue worker, log viewer, Vite dev server

# Individual services
php artisan serve              # Laravel development server
php artisan queue:listen       # Queue worker
php artisan pail              # Real-time log viewer
npm run dev                   # Vite development server
```

### Database Operations
```bash
php artisan migrate           # Run migrations
php artisan migrate:fresh     # Drop all tables and re-migrate
php artisan migrate:rollback  # Rollback last migration
php artisan db:seed           # Run database seeders
php artisan migrate:fresh --seed  # Fresh database with seed data
```

### Testing
```bash
composer test                 # Run all tests
php artisan test              # Run Pest/PHPUnit tests
php artisan test --filter=TestName  # Run specific test
```

### Code Quality
```bash
./vendor/bin/pint             # Format code with Laravel Pint
./vendor/bin/phpstan analyse  # Run static analysis
```

### Build & Deployment
```bash
npm run build                 # Production build
php artisan optimize          # Optimize application
php artisan config:cache      # Cache configuration
php artisan route:cache       # Cache routes
php artisan view:cache        # Cache views
```

### Debugging & Monitoring
```bash
php artisan horizon           # Start Horizon queue dashboard
php artisan telescope:install # Install Telescope
php artisan tinker            # Interactive REPL
```

### AI Services
```bash
# AWS Bedrock setup (PowerShell scripts)
.\scripts\setup-aws-credentials.ps1
.\scripts\setup-claude-bedrock-complete.ps1
.\scripts\verify-claude-bedrock-setup.ps1
.\scripts\launch-claude-bedrock.ps1
```

## Environment Configuration

### Required Environment Variables
```env
APP_NAME="Uma Musume Career Planner"
APP_ENV=local
APP_KEY=                      # Generated by artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite

# AWS Bedrock (optional)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BEDROCK_REGION=us-east-1

# Ollama (optional)
OLLAMA_HOST=http://localhost:11434
OLLAMA_MODEL=llama2

# MCP Configuration (optional)
MCP_ENABLED=true
MCP_SERVER_URL=http://localhost:3000
```

## Version Requirements

### Minimum Versions
- **PHP**: 8.2 or higher
- **Composer**: 2.0 or higher
- **Node.js**: 18.0 or higher
- **NPM**: 9.0 or higher
- **SQLite**: 3.35 or higher

### Recommended Versions
- **PHP**: 8.3 (latest stable)
- **Node.js**: 20 LTS
- **Composer**: 2.7+
- **NPM**: 10+

## Browser Support
- Chrome/Edge: Last 2 versions
- Firefox: Last 2 versions
- Safari: Last 2 versions
- Mobile browsers: iOS Safari 14+, Chrome Android 90+
