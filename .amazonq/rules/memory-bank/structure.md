# Project Structure

## Directory Organization

### Core Application (`/app`)
```
app/
├── Console/          # Artisan commands and scheduled tasks
├── Http/             # HTTP layer (Controllers, Middleware, Requests)
│   ├── Controllers/  # Request handlers and business logic coordination
│   ├── Middleware/   # Request/response filtering
│   └── Requests/     # Form request validation
├── Models/           # Eloquent ORM models (database entities)
├── Policies/         # Authorization policies for models
├── Providers/        # Service providers for dependency injection
├── Repositories/     # Data access layer abstraction
├── Services/         # Business logic and domain services
└── View/             # View composers and view-related logic
```

**Key Components:**
- **Models**: Character, Career, SupportCard, Race, Skill entities
- **Services**: AI integration, career planning, analytics, report generation
- **Repositories**: Database query abstraction for testability
- **Controllers**: RESTful resource controllers for web routes

### Configuration (`/config`)
```
config/
├── ai_agents.php        # AI agent configurations
├── ai.php               # AI service settings
├── aws.php              # AWS Bedrock credentials
├── boost.php            # Laravel Boost settings
├── external-apis.php    # External API integrations
├── horizon.php          # Queue monitoring
├── mcp_tools.php        # MCP tool definitions
├── mcp-agents.php       # MCP agent configurations
├── mcp.php              # MCP server settings
└── telescope.php        # Application debugging
```

### Database (`/database`)
```
database/
├── factories/        # Model factories for testing
├── migrations/       # Database schema migrations
├── seeders/          # Database seeders for initial data
└── database.sqlite   # SQLite database file
```

**Schema Entities:**
- characters, careers, races, support_cards, skills
- career_races (pivot), character_support_cards (pivot)
- Timestamps and soft deletes on all tables

### Resources (`/resources`)
```
resources/
├── css/
│   └── app.css           # Tailwind CSS entry point
├── js/
│   ├── core/             # Core JavaScript modules
│   │   ├── EventBus.js   # Event-driven communication
│   │   └── ...
│   ├── app.js            # Main JavaScript entry
│   └── bootstrap.js      # Axios and Echo setup
└── views/
    ├── layouts/          # Blade layout templates
    │   └── app.blade.php # Main application layout
    ├── components/       # Reusable Blade components
    ├── characters/       # Character CRUD views
    ├── careers/          # Career management views
    ├── reports/          # Analytics and reporting views
    └── support-cards/    # Support card views
```

### Routes (`/routes`)
```
routes/
├── web.php           # Web application routes
├── api.php           # RESTful API routes
└── console.php       # Artisan command definitions
```

### Public Assets (`/public`)
```
public/
├── js/               # Compiled JavaScript
├── images@           # Symlink to storage/app/public/images
├── storage@          # Symlink to storage/app/public
├── index.php         # Application entry point
├── sw.js             # Service worker for PWA
└── offline.html      # Offline fallback page
```

### Storage (`/storage`)
```
storage/
├── app/
│   ├── public/       # Publicly accessible files
│   │   └── images/   # Uploaded images
│   └── private/      # Private application files
├── framework/        # Framework cache and sessions
└── logs/             # Application logs
```

### Images (`/images`)
```
images/
├── app_bg/           # Application background images
├── app_logo/         # Application logos
├── support_cards/    # Support card images (lazy-loaded)
└── trainee_images/   # Character/trainee images
```

### Documentation (`/docs`)
Comprehensive project documentation including:
- Software Development Plan (SDP)
- Business Requirements (BRS)
- Software Requirements (SRS)
- Design Specifications (SDS)
- Database Documentation (DBD)
- User Manual (SUM)
- Implementation summaries
- API specifications (OpenAPI)

### Testing (`/tests`)
```
tests/
├── Feature/          # Feature/integration tests
├── Unit/             # Unit tests
├── Pest.php          # Pest configuration
└── TestCase.php      # Base test case
```

## Architectural Patterns

### MVC Architecture
- **Models**: Eloquent ORM for database interactions
- **Views**: Blade templates with Alpine.js reactivity
- **Controllers**: Thin controllers delegating to services

### Repository Pattern
- Abstracts data access logic from business logic
- Enables easier testing with mock repositories
- Consistent query interface across the application

### Service Layer Pattern
- Business logic encapsulated in service classes
- Services injected via dependency injection
- Promotes single responsibility and testability

### Event-Driven Architecture
- EventBus.js for frontend event communication
- Laravel events for backend decoupling
- Queue system for asynchronous processing

### AI Agent Architecture
- **Hybrid AI System**: AWS Bedrock + Ollama integration
- **MCP Protocol**: Multi-agent orchestration
- **Agent Types**: Career planner, optimizer, analyzer agents
- **Tool Integration**: MCP tools for agent capabilities

## Core Component Relationships

### Character → Career → Race Flow
```
Character (1) ──→ (N) Career ──→ (N) CareerRace ──→ (1) Race
```

### Character → Support Cards
```
Character (N) ←──→ (N) SupportCard (via character_support_cards pivot)
```

### AI Integration Flow
```
User Request → Controller → Service → AI Agent (Bedrock/Ollama/MCP) → Response
```

### Report Generation Flow
```
Character/Career → Repository → Service (Analytics) → Report Data → View
```

## Technology Stack Integration

### Backend Stack
- **Framework**: Laravel 12 (PHP 8.2+)
- **Database**: SQLite with Eloquent ORM
- **Queue**: Laravel Horizon for job processing
- **Debugging**: Telescope, Debugbar, Pail

### Frontend Stack
- **CSS**: Tailwind CSS 4 with Vite plugin
- **JavaScript**: Alpine.js 3 with persist plugin
- **Build Tool**: Vite 7 with HMR
- **HTTP Client**: Axios for AJAX requests

### AI/ML Stack
- **AWS Bedrock**: Claude AI models
- **Ollama**: Local LLM runtime
- **MCP**: Model Context Protocol for agent orchestration

### Development Tools
- **Testing**: Pest PHP 4, PHPUnit 12
- **Static Analysis**: Larastan (PHPStan for Laravel)
- **Code Style**: Laravel Pint
- **Package Manager**: Composer, NPM
