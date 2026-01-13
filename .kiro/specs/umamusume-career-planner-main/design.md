# Umamusume Career Planner - System Design Document

## Table of Contents

1. [Executive Summary](#executive-summary)
2. [System Architecture](#system-architecture)
3. [Technology Stack](#technology-stack)
4. [Database Design](#database-design)
5. [API Design](#api-design)
6. [Frontend Architecture](#frontend-architecture)
7. [AI Integration Architecture](#ai-integration-architecture)
8. [Security Architecture](#security-architecture)
9. [Performance & Scalability](#performance--scalability)
10. [Correctness Properties](#correctness-properties)
11. [Error Handling](#error-handling)
12. [Testing Strategy](#testing-strategy)

## Executive Summary

The **UmamusumeCareerPlanner** is a sophisticated local-first web application designed to manage your Umamusume trainee career progression in the Umamusume Pretty Derby mobile game. Built with Laravel 12 and modern JavaScript technologies, it combines local XAMPP deployment with cloud API integration to provide intelligent training recommendations, race strategy optimization, and comprehensive career analytics.

### Key Architectural Principles

- **Local-First Architecture**: All personal data stored locally with optional cloud API integration
- **Hybrid AI Processing**: Ollama local models primary, AWS Bedrock fallback for complex tasks
- **Performance-Optimized**: Redis caching, database optimization, and efficient frontend patterns
- **Privacy-Focused**: No personal gameplay data transmitted without explicit consent
- **Accessibility-First**: WCAG 2.2 AA compliance throughout the application
- **Scalable Design**: Modular architecture supporting future cloud migration

### System Overview

The application serves as a single-user turn-by-turn career progression planner with the following core capabilities:

- **Character Management**: Comprehensive stat tracking, aptitude management, and inheritance optimization
- **Training Optimization**: AI-powered training predictions with scenario-specific mechanics
- **Race Strategy**: Intelligent race preparation and performance analysis
- **Skill Management**: Advanced SP optimization with hint collection strategies
- **Data Integration**: External API integration with intelligent fallback mechanisms
- **AI Advisory**: Hybrid local/cloud AI system for strategic guidance

## System Architecture

### High-Level Architecture

```text
┌─────────────────────────────────────────────────────────────────┐
│                 UMAMUSUME CAREER PLANNER                       │
├─────────────────────────────────────────────────────────────────┤
│  Frontend Layer (JavaScript + Tailwind CSS v4)                 │
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐   │
│  │   React/Vue     │ │   PWA Features  │ │  Accessibility  │   │
│  │   Components    │ │   Service       │ │  WCAG 2.2 AA    │   │
│  │                 │ │   Workers       │ │  Compliance     │   │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘   │
├─────────────────────────────────────────────────────────────────┤
│  Application Layer (Laravel 12)                                │
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐   │
│  │   Controllers   │ │   Services      │ │   Middleware    │   │
│  │   (Single       │ │   (Business     │ │   (Auth, CORS,  │   │
│  │   Action)       │ │   Logic)        │ │   Rate Limit)   │   │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘   │
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐   │
│  │   Repositories  │ │   Events &      │ │   Queue Jobs    │   │
│  │   (Data Access) │ │   Listeners     │ │   (Background   │   │
│  │                 │ │                 │ │   Processing)   │   │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘   │
├─────────────────────────────────────────────────────────────────┤
│  Data Layer                                                     │
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐   │
│  │   MySQL         │ │   Redis (WSL)   │ │   File Storage  │   │
│  │   (Primary      │ │   (Cache,       │ │   (Local        │   │
│  │   Database)     │ │   Sessions,     │ │   Backups)      │   │
│  │                 │ │   Queues)       │ │                 │   │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘   │
├─────────────────────────────────────────────────────────────────┤
│  External Integration Layer                                     │
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐   │
│  │   AI Services   │ │   Game APIs     │ │   OCR Services  │   │
│  │   (Ollama via   │ │   (Community    │ │   (Tesseract +  │   │
│  │   Laravel pkg + │ │   Databases)    │ │   OpenCV)       │   │
│  │   AWS Bedrock)  │ │                 │ │                 │   │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
```

### Architectural Patterns

#### 1. Repository Pattern

```php
interface CharacterRepositoryInterface
{
    public function findById(int $id): ?Character;
    public function findByUserId(int $userId): Collection;
    public function store(Character $character): Character;
    public function update(Character $character): Character;
    public function delete(int $id): bool;
}

class EloquentCharacterRepository implements CharacterRepositoryInterface
{
    public function __construct(private Character $model) {}

    public function findById(int $id): ?Character
    {
        return $this->model->with(['aptitudes', 'factors', 'skills'])->find($id);
    }
}
```

#### 2. Service Layer Pattern

```php
class TrainingOptimizationService
{
    public function __construct(
        private CharacterRepositoryInterface $characterRepo,
        private TrainingPredictionEngine $predictionEngine,
        private CacheManager $cache
    ) {}

    public function optimizeTrainingSequence(
        Character $character,
        TrainingGoals $goals
    ): TrainingRecommendation {
        $cacheKey = "training_optimization_{$character->id}_{$goals->hash()}";

        return $this->cache->remember($cacheKey, 300, function () use ($character, $goals) {
            return $this->predictionEngine->calculateOptimalSequence($character, $goals);
        });
    }
}
```

#### 3. CQRS Pattern

```php
// Command (Write Operations)
class UpdateCharacterStatsCommand
{
    public function __construct(
        public readonly int $characterId,
        public readonly array $stats,
        public readonly string $source
    ) {}
}

class UpdateCharacterStatsHandler
{
    public function handle(UpdateCharacterStatsCommand $command): void
    {
        $character = $this->characterRepo->findById($command->characterId);
        $character->updateStats($command->stats);
        $this->characterRepo->update($character);

        event(new CharacterStatsUpdated($character, $command->source));
    }
}

// Query (Read Operations)
class GetCharacterProgressQuery
{
    public function __construct(public readonly int $characterId) {}
}

class GetCharacterProgressHandler
{
    public function handle(GetCharacterProgressQuery $query): CharacterProgress
    {
        return $this->characterRepo->getProgressById($query->characterId);
    }
}
```

#### 4. Event-Driven Architecture

```php
class CharacterStatsUpdated
{
    public function __construct(
        public readonly Character $character,
        public readonly string $source
    ) {}
}

class UpdateTrainingRecommendationsListener
{
    public function handle(CharacterStatsUpdated $event): void
    {
        // Invalidate cached training recommendations
        Cache::tags(['training', "character_{$event->character->id}"])->flush();

        // Queue background recalculation
        RecalculateTrainingRecommendations::dispatch($event->character);
    }
}
```

## Technology Stack

### Backend Stack

#### Laravel 12 Framework

- **Version**: Laravel 12.x (Latest LTS, released February 2025)
- **PHP Version**: PHP 8.1 or higher (Laravel Boost compatible; official installer defaults to PHP 8.4)
- **Key Features**:
  - Streamlined file structure (middleware in bootstrap/app.php, no app/Http/Kernel.php)
  - Laravel Boost integration for AI-assisted development with 15+ specialized tools and 17,000+ pieces of vectorized documentation
  - Native service provider configuration in bootstrap/providers.php
  - Console commands auto-discovered from app/Console/Commands/
  - Laravel Sanctum for API authentication
  - Real-time support with Laravel Reverb for WebSocket communication
  - Asynchronous caching and improved database features

#### Database Layer

```php
// Primary Database Configuration
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'umamusume-career-planner'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => 'ucp_',
    'strict' => true,
    'engine' => 'InnoDB',
    'options' => [
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
],

// Redis Configuration (WSL)
'redis' => [
    'client' => env('REDIS_CLIENT', 'phpredis'),
    'default' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', 6379),
        'database' => env('REDIS_DB', 0),
        'prefix' => env('REDIS_PREFIX', 'umamusume-career-planner:'),
    ],
    'cache' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', 6379),
        'database' => env('REDIS_CACHE_DB', 1),
        'prefix' => env('REDIS_PREFIX', 'umamusume-career-planner:') . 'cache:',
    ],
    'session' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', 6379),
        'database' => env('REDIS_SESSION_DB', 2),
        'prefix' => env('REDIS_PREFIX', 'umamusume-career-planner:') . 'session:',
    ],
],
```

#### Queue & Background Processing

```php
// Queue Configuration
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => 90,
        'block_for' => null,
        'after_commit' => false,
    ],
],

// Horizon Configuration for Queue Monitoring
'environments' => [
    'local' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['default', 'training', 'ai', 'external-api'],
            'balance' => 'auto',
            'processes' => 3,
            'tries' => 3,
            'timeout' => 60,
        ],
    ],
],
```

### Frontend Stack

#### Modern JavaScript Architecture (2025)

```javascript
// JavaScript Configuration (ES2024+ with optional TypeScript migration)
// package.json
{
  "name": "umamusume-career-planner",
  "description": "Manage your umamusume trainee career",
  "type": "module",
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "preview": "vite preview"
  },
  "devDependencies": {
    "vite": "^5.0.0",
    "@vitejs/plugin-react": "^4.0.0",
    "tailwindcss": "^4.0.0",
    "autoprefixer": "^10.4.0"
  }
}
```

#### Tailwind CSS v4 Configuration

```css
/* resources/css/app.css */
@import "tailwindcss";

@theme {
  --color-primary-50: #f0f9ff;
  --color-primary-500: #3b82f6;
  --color-primary-900: #1e3a8a;

  --font-family-sans: "Inter", system-ui, sans-serif;
  --font-family-mono: "JetBrains Mono", monospace;

  --spacing-xs: 0.5rem;
  --spacing-sm: 0.75rem;
  --spacing-md: 1rem;
  --spacing-lg: 1.5rem;
  --spacing-xl: 2rem;
}

/* Component-specific styles */
@layer components {
  .btn-primary {
    @apply bg-primary-500 text-white px-4 py-2 rounded-lg hover:bg-primary-600
           focus:ring-2 focus:ring-primary-500 focus:ring-offset-2
           transition-colors duration-200;
  }

  .card {
    @apply bg-white rounded-lg shadow-sm border border-gray-200 p-6;
  }
}
```

#### Component Architecture

```javascript
// Component Structure (JavaScript with JSDoc for type hints)
/**
 * @typedef {Object} ComponentProps
 * @property {string} [className] - Additional CSS classes
 * @property {React.ReactNode} [children] - Child components
 */

/**
 * @typedef {ComponentProps} ButtonProps
 * @property {'primary' | 'secondary' | 'danger'} [variant] - Button style variant
 * @property {'sm' | 'md' | 'lg'} [size] - Button size
 * @property {boolean} [disabled] - Whether button is disabled
 * @property {() => void} [onClick] - Click handler
 */

/**
 * Button Component
 * @param {ButtonProps} props
 * @returns {JSX.Element}
 */
const Button = ({
  variant = 'primary',
  size = 'md',
  disabled = false,
  className = '',
  children,
  onClick
}) => {
  const baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2';

  const variantClasses = {
    primary: 'bg-primary-500 text-white hover:bg-primary-600 focus:ring-primary-500',
    secondary: 'bg-gray-200 text-gray-900 hover:bg-gray-300 focus:ring-gray-500',
    danger: 'bg-red-500 text-white hover:bg-red-600 focus:ring-red-500'
  };

  const sizeClasses = {
    sm: 'px-3 py-1.5 text-sm',
    md: 'px-4 py-2 text-base',
    lg: 'px-6 py-3 text-lg'
  };

  return (
    <button
      className={`${baseClasses} ${variantClasses[variant]} ${sizeClasses[size]} ${disabled ? 'opacity-50 cursor-not-allowed' : ''} ${className}`}
      disabled={disabled}
      onClick={onClick}
      aria-disabled={disabled}
    >
      {children}
    </button>
  );
};
```

#### PWA Configuration

```javascript
// Service Worker Registration
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js')
      .then((registration) => {
        console.log('SW registered: ', registration);
      })
      .catch((registrationError) => {
        console.log('SW registration failed: ', registrationError);
      });
  });
}

// Manifest Configuration
{
  "name": "UmamusumeCareerPlanner",
  "short_name": "UmamusumeCareerPlanner",
  "description": "Manage your umamusume trainee career",
  "start_url": "/",
  "display": "standalone",
  "background_color": "#ffffff",
  "theme_color": "#3b82f6",
  "icons": [
    {
      "src": "/images/app_logo/uma_musume_race_planner_logo_128.png",
      "sizes": "128x128",
      "type": "image/png"
    },
    {
      "src": "/images/app_logo/uma_musume_race_planner_logo_256.png",
      "sizes": "256x256",
      "type": "image/png"
    },
    {
      "src": "/images/app_logo/uma_musume_race_planner_logo_512.png",
      "sizes": "512x512",
      "type": "image/png"
    },
    {
      "src": "/images/app_logo/uma_musume_race_planner_logo_1024.png",
      "sizes": "1024x1024",
      "type": "image/png"
    }
  ],
  "favicon": "/images/app_logo/uma_musume_race_planner_logo_32.ico"
}
```

#### Visual Design System and Asset Integration

```css
/* Theme Configuration with Existing Assets */
@theme {
  /* Background Images from images/app_bg/ */
  --bg-light-desktop: url('/images/app_bg/uma_musume_race_planner_bg_light_1536x1028.png');
  --bg-light-mobile: url('/images/app_bg/uma_musume_race_planner_bg_light_1028x1536.png');
  --bg-dark-desktop: url('/images/app_bg/uma_musume_race_planner_bg_dark_1536x1028.png');
  --bg-dark-mobile: url('/images/app_bg/uma_musume_race_planner_bg_dark_1028x1536.png');

  /* Character Avatar Placeholders from images/trainee_images/ */
  --avatar-agnes-tachyon: url('/images/trainee_images/__agnes_tachyon_umamusume_drawn_by_welchino__sample-1db2ca428e2545fcae81fe526d7a8e96.jpg');
  --avatar-gold-ship: url('/images/trainee_images/__gold_ship_umamusume_drawn_by_advarcher__sample-2713426899554240b99dc00440e97745.jpg');
  --avatar-narita-brian: url('/images/trainee_images/__narita_brian_umamusume_drawn_by_no_uwazumi__sample-0f3c352063a7077cb5708b8284dd7217.jpg');
  --avatar-tokai-teio: url('/images/trainee_images/__tokai_teio_umamusume_drawn_by_so_on__305c01834a0c0cf3fe3593c281a0b05b.jpg');
  --avatar-vodka: url('/images/trainee_images/__vodka_umamusume_drawn_by_mayata__41166bfaeb2670ae37c8785af4566d58.jpg');
  --avatar-silence-suzuka: url('/images/trainee_images/bb962aabeafaee5cbf7831e4d178ca64.jpg');
}

/* Responsive Background System */
.app-background {
  background-image: var(--bg-light-desktop);
  background-size: cover;
  background-position: center;
  background-attachment: fixed;

  @media (max-width: 768px) {
    background-image: var(--bg-light-mobile);
  }

  @media (prefers-color-scheme: dark) {
    background-image: var(--bg-dark-desktop);

    @media (max-width: 768px) {
      background-image: var(--bg-dark-mobile);
    }
  }
}

/* Character Avatar Components */
.character-avatar {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background-size: cover;
  background-position: center;
  border: 2px solid var(--color-primary-500);

  &.avatar-lg {
    width: 128px;
    height: 128px;
  }

  &.avatar-sm {
    width: 32px;
    height: 32px;
  }
}

/* Character Selection Grid */
.character-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;

  .character-card {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 1rem;
    border: 1px solid rgba(255, 255, 255, 0.2);

    @media (prefers-color-scheme: dark) {
      background: rgba(0, 0, 0, 0.8);
      border-color: rgba(255, 255, 255, 0.1);
    }
  }
}
```

#### Character Image Integration

```javascript
// Character Avatar System
const CharacterAvatars = {
  'Agnes Tachyon': '/images/trainee_images/__agnes_tachyon_umamusume_drawn_by_welchino__sample-1db2ca428e2545fcae81fe526d7a8e96.jpg',
  'Daiwa Scarlet': '/images/trainee_images/__daiwa_scarlet_umamusume_drawn_by_kurokawa_heuy__sample-9576ae268cdddfe167c2300d5453f2cf.jpg',
  'El Condor Pasa': '/images/trainee_images/__el_condor_pasa_umamusume_drawn_by_nekogusa_kinako__85cfefb3697093d2c40c9db031ab46a5.jpg',
  'Gold Ship': '/images/trainee_images/__gold_ship_umamusume_drawn_by_advarcher__sample-2713426899554240b99dc00440e97745.jpg',
  'Haru Urara': '/images/trainee_images/__haru_urara_umamusume_drawn_by_advarcher__sample-7d1c3c431ef193e5e061bdda73f97fd5.jpg',
  'Maruzensky': '/images/trainee_images/__maruzensky_umamusume_drawn_by_kamishima_kanon__sample-297ecca0da3990374954a514f06bea2b.jpg',
  'Narita Brian': '/images/trainee_images/__narita_brian_umamusume_drawn_by_no_uwazumi__sample-0f3c352063a7077cb5708b8284dd7217.jpg',
  'Oguri Cap': '/images/trainee_images/__oguri_cap_and_jacques_villeneuve_umamusume_and_1_more_drawn_by_holeecrab__sample-9628095fc1e0ee5bcc8c96c47d5722a1.jpg',
  'Tokai Teio': '/images/trainee_images/__tokai_teio_umamusume_drawn_by_so_on__305c01834a0c0cf3fe3593c281a0b05b.jpg',
  'Vodka': '/images/trainee_images/__vodka_umamusume_drawn_by_mayata__41166bfaeb2670ae37c8785af4566d58.jpg',
  'Silence Suzuka': '/images/trainee_images/bb962aabeafaee5cbf7831e4d178ca64.jpg'
};

/**
 * Character Avatar Component
 * @param {Object} props
 * @param {string} props.characterName - Name of the character
 * @param {string} [props.size='md'] - Avatar size (sm, md, lg)
 * @param {string} [props.className] - Additional CSS classes
 */
const CharacterAvatar = ({ characterName, size = 'md', className = '' }) => {
  const avatarUrl = CharacterAvatars[characterName] || CharacterAvatars['Silence Suzuka']; // Default to Silence Suzuka

  return (
    <div
      className={`character-avatar avatar-${size} ${className}`}
      style={{ backgroundImage: `url(${avatarUrl})` }}
      aria-label={`${characterName} avatar`}
      role="img"
    />
  );
};

/**
 * Character Selection Component with Background
 * @param {Object} props
 * @param {Array} props.characters - Available characters
 * @param {Function} props.onSelect - Character selection handler
 */
const CharacterSelection = ({ characters, onSelect }) => {
  return (
    <div className="app-background min-h-screen">
      <div className="container mx-auto px-4 py-8">
        <h1 className="text-3xl font-bold text-center mb-8 text-white drop-shadow-lg">
          Select Your Umamusume
        </h1>
        <div className="character-grid">
          {characters.map((character) => (
            <div
              key={character.id}
              className="character-card cursor-pointer hover:scale-105 transition-transform"
              onClick={() => onSelect(character)}
            >
              <CharacterAvatar
                characterName={character.name}
                size="lg"
                className="mx-auto mb-4"
              />
              <h3 className="text-lg font-semibold text-center">{character.name}</h3>
              <p className="text-sm text-gray-600 text-center mt-2">
                {character.scenario_type === 'ura_finale' ? 'URA Finale' : 'Unity Cup'}
              </p>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};
```

```text

### AI Integration Stack

#### Ollama Local Models (via cloudstudio/ollama-laravel)

```php
// Ollama Service Configuration using cloudstudio/ollama-laravel package
use CloudStudio\Ollama\Facades\Ollama;

class OllamaService
{
    public function generateResponse(string $prompt, array $context = []): AIResponse
    {
        $response = Ollama::agent('Umamusume Career Advisor')
            ->model(config('ai.ollama.model', 'llama3.3'))
            ->prompt($prompt)
            ->options([
                'temperature' => 0.3,
                'top_p' => 0.9,
                'max_tokens' => 2048,
            ])
            ->ask();

        return new AIResponse([
            'content' => $response,
            'model' => config('ai.ollama.model', 'llama3.3'),
            'processing_time' => $response->getProcessingTime(),
            'token_count' => $response->getTokenCount(),
        ]);
    }

    public function streamResponse(string $prompt, callable $callback = null): Generator
    {
        return Ollama::agent('Umamusume Career Advisor')
            ->model(config('ai.ollama.model', 'llama3.3'))
            ->prompt($prompt)
            ->stream($callback);
    }
}
```

#### AWS Bedrock Integration

```php
// Bedrock Service Configuration
use Aws\BedrockRuntime\BedrockRuntimeClient;

class BedrockService
{
    private BedrockRuntimeClient $client;

    public function __construct()
    {
        $this->client = new BedrockRuntimeClient([
            'region' => config('aws.region', 'us-east-1'),
            'version' => 'latest',
            'credentials' => [
                'key' => config('aws.access_key_id'),
                'secret' => config('aws.secret_access_key'),
            ]
        ]);
    }

    public function invokeModel(string $modelId, array $payload): AIResponse
    {
        $response = $this->client->invokeModel([
            'modelId' => $modelId,
            'contentType' => 'application/json',
            'accept' => 'application/json',
            'body' => json_encode($payload)
        ]);

        return new AIResponse(json_decode($response['body']->getContents(), true));
    }
}
```

#### Hybrid AI Router

```php
class HybridAIService
{
    public function __construct(
        private OllamaService $ollama,
        private BedrockService $bedrock,
        private CacheManager $cache
    ) {}

    public function processRequest(AIRequest $request): AIResponse
    {
        // Check cache first
        $cacheKey = "ai_response_" . md5($request->toJson());
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        // Determine complexity and route accordingly
        if ($this->isComplexRequest($request)) {
            $response = $this->processWithBedrock($request);
        } else {
            try {
                $response = $this->processWithOllama($request);

                // Fallback to Bedrock if Ollama fails or is too slow
                if (!$response->isSuccessful() || $response->getProcessingTime() > 15) {
                    Log::info('Ollama processing slow/failed, falling back to Bedrock', [
                        'processing_time' => $response->getProcessingTime(),
                        'request_id' => $request->getId()
                    ]);
                    $response = $this->processWithBedrock($request);
                }
            } catch (Exception $e) {
                Log::warning('Ollama processing failed, falling back to Bedrock', [
                    'error' => $e->getMessage(),
                    'request' => $request->toArray()
                ]);
                $response = $this->processWithBedrock($request);
            }
        }

        // Cache successful responses
        if ($response->isSuccessful()) {
            $this->cache->put($cacheKey, $response, 3600);
        }

        return $response;
    }

    private function isComplexRequest(AIRequest $request): bool
    {
        return $request->hasMultiStepReasoning()
            || $request->requiresRAG()
            || $request->getTokenCount() > 4000;
    }
}
```

## Database Design

### Entity Relationship Diagram

```text
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│    Users        │    │   Characters    │    │   Aptitudes     │
├─────────────────┤    ├─────────────────┤    ├─────────────────┤
│ id (PK)         │    │ id (PK)         │    │ id (PK)         │
│ name            │◄──┐│ user_id (FK)    │◄──┐│ character_id(FK)│
│ email           │   ││ name            │   ││ distance_type   │
│ password        │   ││ scenario_type   │   ││ surface_type    │
│ created_at      │   ││ current_stats   │   ││ running_style   │
│ updated_at      │   ││ energy_level    │   ││ grade           │
└─────────────────┘   ││ mood_status     │   │└─────────────────┘
                      ││ career_stage    │   │
                      ││ created_at      │   │┌─────────────────┐
                      ││ updated_at      │   ││   Factors       │
                      │└─────────────────┘   │├─────────────────┤
                      │                      ││ id (PK)         │
┌─────────────────┐   │┌─────────────────┐   ││ character_id(FK)│
│   Skills        │   ││   Careers       │   ││ factor_type     │
├─────────────────┤   │├─────────────────┤   ││ factor_level    │
│ id (PK)         │   ││ id (PK)         │   ││ stat_bonus      │
│ character_id(FK)│◄──┘│ character_id(FK)│◄──┘│ source_parent   │
│ skill_name      │    │ scenario_type   │    │ inheritance_rate│
│ skill_type      │    │ start_date      │    │ created_at      │
│ sp_cost         │    │ end_date        │    │ updated_at      │
│ hint_count      │    │ final_grade     │    └─────────────────┘
│ is_acquired     │    │ final_stats     │
│ acquired_at     │    │ race_results    │    ┌─────────────────┐
│ created_at      │    │ training_log    │    │ Support_Cards   │
│ updated_at      │    │ created_at      │    ├─────────────────┤
└─────────────────┘    │ updated_at      │    │ id (PK)         │
                       └─────────────────┘    │ character_id(FK)│
┌─────────────────┐                          │ card_name       │
│ Training_Sessions│    ┌─────────────────┐   │ card_rarity     │
├─────────────────┤    │   Races         │   │ limit_break_lvl │
│ id (PK)         │    ├─────────────────┤   │ specialization  │
│ career_id (FK)  │◄──┐│ id (PK)         │   │ friendship_level│
│ turn_number     │   ││ career_id (FK)  │◄──┤ position_slot   │
│ training_type   │   ││ race_name       │   │ created_at      │
│ stat_gains      │   ││ race_grade      │   │ updated_at      │
│ energy_cost     │   ││ distance        │   └─────────────────┘
│ participants    │   ││ surface         │
│ spirit_burst    │   ││ weather         │   ┌─────────────────┐
│ skill_hints     │   ││ final_position  │   │ External_Data   │
│ created_at      │   ││ performance     │   ├─────────────────┤
│ updated_at      │   ││ strategy_used   │   │ id (PK)         │
└─────────────────┘   ││ created_at      │   │ data_type       │
                      ││ updated_at      │   │ source_api      │
┌─────────────────┐   │└─────────────────┘   │ data_content    │
│ AI_Conversations│   │                      │ cache_expires   │
├─────────────────┤   │┌─────────────────┐   │ last_updated    │
│ id (PK)         │   ││   Events        │   │ created_at      │
│ user_id (FK)    │◄──┘│ id (PK)         │   │ updated_at      │
│ character_id(FK)│    │ career_id (FK)  │◄──┤ is_active       │
│ conversation_id │    │ turn_number     │   └─────────────────┘
│ message_type    │    │ event_type      │
│ message_content │    │ event_name      │   ┌─────────────────┐
│ ai_model_used   │    │ choices         │   │ OCR_Extractions │
│ processing_time │    │ selected_choice │   ├─────────────────┤
│ created_at      │    │ outcome         │   │ id (PK)         │
│ updated_at      │    │ created_at      │   │ user_id (FK)    │
└─────────────────┘    │ updated_at      │   │ image_path      │
                       └─────────────────┘   │ extracted_data  │
                                            │ confidence_score│
                                            │ processing_time │
                                            │ created_at      │
                                            │ updated_at      │
                                            └─────────────────┘
```

### Database Schema

#### Core Tables

```sql
-- Users table
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    INDEX idx_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Characters table
CREATE TABLE characters (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    scenario_type ENUM('ura_finale', 'unity_cup') NOT NULL,

    -- Current Stats (0-1200 range)
    speed_stat SMALLINT UNSIGNED DEFAULT 0,
    stamina_stat SMALLINT UNSIGNED DEFAULT 0,
    power_stat SMALLINT UNSIGNED DEFAULT 0,
    guts_stat SMALLINT UNSIGNED DEFAULT 0,
    wit_stat SMALLINT UNSIGNED DEFAULT 0,

    -- Character State
    energy_level TINYINT UNSIGNED DEFAULT 100,
    mood_status ENUM('awful', 'bad', 'normal', 'good', 'great') DEFAULT 'normal',
    career_stage ENUM('junior', 'classic', 'senior') DEFAULT 'junior',
    current_turn TINYINT UNSIGNED DEFAULT 1,

    -- Goals and Targets
    target_stats JSON NULL,
    race_objectives JSON NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_characters_user_id (user_id),
    INDEX idx_characters_scenario (scenario_type),
    INDEX idx_characters_stage (career_stage)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Aptitudes table (Fixed talent ratings G-SS)
CREATE TABLE aptitudes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    character_id BIGINT UNSIGNED NOT NULL,

    -- Distance Aptitudes
    sprint_aptitude ENUM('G', 'G+', 'F', 'F+', 'E', 'E+', 'D', 'D+', 'C', 'C+', 'B', 'B+', 'A', 'A+', 'S', 'S+', 'SS') NOT NULL,
    mile_aptitude ENUM('G', 'G+', 'F', 'F+', 'E', 'E+', 'D', 'D+', 'C', 'C+', 'B', 'B+', 'A', 'A+', 'S', 'S+', 'SS') NOT NULL,
    medium_aptitude ENUM('G', 'G+', 'F', 'F+', 'E', 'E+', 'D', 'D+', 'C', 'C+', 'B', 'B+', 'A', 'A+', 'S', 'S+', 'SS') NOT NULL,
    long_aptitude ENUM('G', 'G+', 'F', 'F+', 'E', 'E+', 'D', 'D+', 'C', 'C+', 'B', 'B+', 'A', 'A+', 'S', 'S+', 'SS') NOT NULL,

    -- Surface Aptitudes
    turf_aptitude ENUM('G', 'G+', 'F', 'F+', 'E', 'E+', 'D', 'D+', 'C', 'C+', 'B', 'B+', 'A', 'A+', 'S', 'S+', 'SS') NOT NULL,
    dirt_aptitude ENUM('G', 'G+', 'F', 'F+', 'E', 'E+', 'D', 'D+', 'C', 'C+', 'B', 'B+', 'A', 'A+', 'S', 'S+', 'SS') NOT NULL,

    -- Running Style Aptitudes
    front_runner_aptitude ENUM('G', 'G+', 'F', 'F+', 'E', 'E+', 'D', 'D+', 'C', 'C+', 'B', 'B+', 'A', 'A+', 'S', 'S+', 'SS') NOT NULL,
    pace_chaser_aptitude ENUM('G', 'G+', 'F', 'F+', 'E', 'E+', 'D', 'D+', 'C', 'C+', 'B', 'B+', 'A', 'A+', 'S', 'S+', 'SS') NOT NULL,
    late_surger_aptitude ENUM('G', 'G+', 'F', 'F+', 'E', 'E+', 'D', 'D+', 'C', 'C+', 'B', 'B+', 'A', 'A+', 'S', 'S+', 'SS') NOT NULL,
    end_closer_aptitude ENUM('G', 'G+', 'F', 'F+', 'E', 'E+', 'D', 'D+', 'C', 'C+', 'B', 'B+', 'A', 'A+', 'S', 'S+', 'SS') NOT NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
    UNIQUE KEY unique_character_aptitudes (character_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Factors table (Inheritance bonuses)
CREATE TABLE factors (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    character_id BIGINT UNSIGNED NOT NULL,
    factor_type ENUM('blue_stat', 'red_aptitude', 'green_unique', 'white_normal') NOT NULL,
    factor_category ENUM('speed', 'stamina', 'power', 'guts', 'wit', 'distance', 'surface', 'running_style', 'skill') NOT NULL,
    factor_level ENUM('1_star', '2_star', '3_star') NOT NULL,

    -- Bonus Values
    stat_bonus SMALLINT DEFAULT 0, -- For blue factors: +5, +12, +21
    aptitude_bonus TINYINT DEFAULT 0, -- For red factors: grade improvements
    skill_name VARCHAR(255) NULL, -- For green/white factors

    -- Source Information
    source_parent VARCHAR(255) NOT NULL, -- Which parent provided this factor
    inheritance_rate DECIMAL(3,2) DEFAULT 1.00, -- Success rate multiplier
    affinity_bonus BOOLEAN DEFAULT FALSE, -- ◎ symbol compatibility

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
    INDEX idx_factors_character (character_id),
    INDEX idx_factors_type (factor_type),
    INDEX idx_factors_category (factor_category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Skills table
CREATE TABLE skills (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    character_id BIGINT UNSIGNED NOT NULL,
    skill_name VARCHAR(255) NOT NULL,
    skill_type ENUM('normal', 'rare', 'unique') NOT NULL,
    skill_category ENUM('speed', 'passive', 'recovery', 'debuff') NOT NULL,

    -- SP Cost Management
    base_sp_cost SMALLINT UNSIGNED NOT NULL,
    hint_count TINYINT UNSIGNED DEFAULT 0,
    discount_percentage TINYINT UNSIGNED DEFAULT 0, -- 20% per hint, max 40%
    final_sp_cost SMALLINT UNSIGNED NOT NULL,

    -- Acquisition Status
    is_acquired BOOLEAN DEFAULT FALSE,
    acquired_at TIMESTAMP NULL,
    acquisition_source ENUM('training', 'event', 'inheritance', 'evolution') NULL,

    -- Evolution Information
    evolves_from VARCHAR(255) NULL, -- Normal skill that evolves to this Rare skill
    evolves_to VARCHAR(255) NULL, -- Rare skill this Normal skill evolves to

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
    INDEX idx_skills_character (character_id),
    INDEX idx_skills_type (skill_type),
    INDEX idx_skills_acquired (is_acquired),
    INDEX idx_skills_evolution (evolves_from, evolves_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Career Management Tables

```sql
-- Careers table (Complete career runs)
CREATE TABLE careers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    character_id BIGINT UNSIGNED NOT NULL,
    scenario_type ENUM('ura_finale', 'unity_cup') NOT NULL,

    -- Career Timeline
    start_date TIMESTAMP NOT NULL,
    end_date TIMESTAMP NULL,
    total_turns TINYINT UNSIGNED DEFAULT 0,
    current_turn TINYINT UNSIGNED DEFAULT 1,

    -- Final Results
    final_grade ENUM('G', 'G+', 'F', 'F+', 'E', 'E+', 'D', 'D+', 'C', 'C+', 'B', 'B+', 'A', 'A+', 'S', 'S+', 'SS') NULL,
    final_stats JSON NULL, -- Final stat values
    total_sp_earned SMALLINT UNSIGNED DEFAULT 0,
    total_sp_spent SMALLINT UNSIGNED DEFAULT 0,

    -- Performance Metrics
    race_results JSON NULL, -- Array of race outcomes
    training_efficiency DECIMAL(5,2) NULL, -- Average stat gains per turn
    goal_completion_rate DECIMAL(5,2) NULL, -- Percentage of goals achieved

    -- Strategy Information
    support_card_deck JSON NOT NULL, -- 6-card deck configuration
    training_strategy TEXT NULL,
    race_strategy TEXT NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
    INDEX idx_careers_character (character_id),
    INDEX idx_careers_scenario (scenario_type),
    INDEX idx_careers_grade (final_grade),
    INDEX idx_careers_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Training Sessions table
CREATE TABLE training_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    career_id BIGINT UNSIGNED NOT NULL,
    turn_number TINYINT UNSIGNED NOT NULL,

    -- Training Details
    training_type ENUM('speed', 'stamina', 'power', 'guts', 'wit', 'rest', 'infirmary', 'recreation') NOT NULL,
    training_location VARCHAR(255) NULL, -- Facility name
    facility_level TINYINT UNSIGNED DEFAULT 1, -- 1-5 for Unity Cup

    -- Participants
    support_card_participants JSON NULL, -- Which support cards participated
    teammate_participants JSON NULL, -- Unity Cup teammates
    friendship_training BOOLEAN DEFAULT FALSE,
    participant_count TINYINT UNSIGNED DEFAULT 0,

    -- Results
    stat_gains JSON NOT NULL, -- Actual stat increases
    energy_cost TINYINT UNSIGNED DEFAULT 0,
    skill_hints_gained JSON NULL, -- Skills that gained hints
    spirit_burst_used BOOLEAN DEFAULT FALSE,

    -- Predictions vs Reality
    predicted_gains JSON NULL, -- What was predicted
    prediction_accuracy DECIMAL(5,2) NULL, -- How accurate the prediction was

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (career_id) REFERENCES careers(id) ON DELETE CASCADE,
    INDEX idx_training_career (career_id),
    INDEX idx_training_turn (turn_number),
    INDEX idx_training_type (training_type),
    INDEX idx_training_spirit_burst (spirit_burst_used)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Races table
CREATE TABLE races (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    career_id BIGINT UNSIGNED NOT NULL,
    turn_number TINYINT UNSIGNED NOT NULL,

    -- Race Information
    race_name VARCHAR(255) NOT NULL,
    race_grade ENUM('pre_op', 'op', 'g3', 'g2', 'g1') NOT NULL,
    distance ENUM('sprint', 'mile', 'medium', 'long') NOT NULL,
    surface ENUM('turf', 'dirt') NOT NULL,
    track_name VARCHAR(255) NOT NULL,

    -- Conditions
    weather ENUM('sunny', 'cloudy', 'rainy', 'snowy') NOT NULL,
    track_condition ENUM('firm', 'good', 'soft', 'heavy') NOT NULL,

    -- Strategy and Results
    running_style ENUM('front_runner', 'pace_chaser', 'late_surger', 'end_closer') NOT NULL,
    final_position TINYINT UNSIGNED NOT NULL,
    total_participants TINYINT UNSIGNED NOT NULL,

    -- Performance Analysis
    stat_adequacy JSON NOT NULL, -- ○ ⦾ △ × indicators for each stat
    performance_rating ENUM('excellent', 'good', 'average', 'poor') NOT NULL,
    fan_gain INTEGER DEFAULT 0,
    prize_money INTEGER DEFAULT 0,

    -- Goal Tracking
    was_goal_race BOOLEAN DEFAULT FALSE,
    goal_requirement ENUM('debut', 'placement', 'fan_count', 'specific_race') NULL,
    goal_achieved BOOLEAN DEFAULT FALSE,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (career_id) REFERENCES careers(id) ON DELETE CASCADE,
    INDEX idx_races_career (career_id),
    INDEX idx_races_turn (turn_number),
    INDEX idx_races_grade (race_grade),
    INDEX idx_races_distance (distance),
    INDEX idx_races_goal (was_goal_race, goal_achieved)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Support and External Data Tables

```sql
-- Support Cards table
CREATE TABLE support_cards (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    character_id BIGINT UNSIGNED NOT NULL,
    position_slot TINYINT UNSIGNED NOT NULL, -- 1-6 (5 owned + 1 friend)

    -- Card Information
    card_name VARCHAR(255) NOT NULL,
    card_rarity ENUM('SSR', 'SR', 'R') NOT NULL,
    specialization ENUM('speed', 'stamina', 'power', 'guts', 'wit', 'pal') NOT NULL,
    limit_break_level TINYINT UNSIGNED DEFAULT 0, -- 0-4 stars

    -- Relationship Status
    friendship_level TINYINT UNSIGNED DEFAULT 0, -- 0-100%
    rainbow_training_available BOOLEAN DEFAULT FALSE, -- 80%+ friendship

    -- Card Effects
    training_bonuses JSON NOT NULL, -- Stat bonuses provided
    skill_provisions JSON NOT NULL, -- Skills this card can provide hints for
    event_skills JSON NULL, -- Skills from card events
    unique_effects JSON NULL, -- Special card effects

    -- Meta Information
    tier_ranking ENUM('SS', 'S', 'A', 'B') NULL, -- Community tier ranking
    is_friend_card BOOLEAN DEFAULT FALSE, -- Position 6 friend card

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
    INDEX idx_support_cards_character (character_id),
    INDEX idx_support_cards_specialization (specialization),
    INDEX idx_support_cards_tier (tier_ranking),
    UNIQUE KEY unique_character_position (character_id, position_slot)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Events table (Career events and decisions)
CREATE TABLE events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    career_id BIGINT UNSIGNED NOT NULL,
    turn_number TINYINT UNSIGNED NOT NULL,

    -- Event Information
    event_type ENUM('character', 'support_card', 'scenario', 'random') NOT NULL,
    event_name VARCHAR(255) NOT NULL,
    event_description TEXT NULL,

    -- Choices and Outcomes
    available_choices JSON NOT NULL, -- Array of choice options
    selected_choice INTEGER NOT NULL, -- Index of selected choice
    choice_reasoning TEXT NULL, -- Why this choice was made

    -- Results
    outcome_description TEXT NULL,
    stat_changes JSON NULL, -- Any stat modifications
    skill_hints_gained JSON NULL, -- Skills that gained hints
    other_effects JSON NULL, -- Mood changes, conditions, etc.

    -- Optimization Data
    optimal_choice INTEGER NULL, -- Recommended choice based on goals
    choice_effectiveness DECIMAL(5,2) NULL, -- How good the choice was

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (career_id) REFERENCES careers(id) ON DELETE CASCADE,
    INDEX idx_events_career (career_id),
    INDEX idx_events_turn (turn_number),
    INDEX idx_events_type (event_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- External Data Cache table
CREATE TABLE external_data (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    data_type ENUM('character_base', 'support_card', 'race_info', 'skill_data', 'meta_tier', 'community_build') NOT NULL,
    data_key VARCHAR(255) NOT NULL, -- Unique identifier for the data
    source_api VARCHAR(255) NOT NULL, -- Which API provided this data

    -- Data Content
    data_content JSON NOT NULL, -- The actual cached data
    data_version VARCHAR(50) NULL, -- Version/hash for change detection

    -- Cache Management
    cache_expires TIMESTAMP NOT NULL,
    last_updated TIMESTAMP NOT NULL,
    access_count INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,

    -- Quality Metrics
    data_quality_score DECIMAL(3,2) DEFAULT 1.00, -- 0.00-1.00 quality rating
    validation_errors JSON NULL, -- Any validation issues found

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    INDEX idx_external_data_type (data_type),
    INDEX idx_external_data_key (data_key),
    INDEX idx_external_data_expires (cache_expires),
    INDEX idx_external_data_source (source_api),
    UNIQUE KEY unique_data_key_source (data_key, source_api)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- AI Conversations table
CREATE TABLE ai_conversations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    character_id BIGINT UNSIGNED NULL, -- NULL for general conversations
    conversation_id VARCHAR(255) NOT NULL, -- Session identifier

    -- Message Information
    message_type ENUM('user', 'assistant', 'system') NOT NULL,
    message_content TEXT NOT NULL,
    message_context JSON NULL, -- Additional context data

    -- AI Processing Information
    ai_model_used ENUM('ollama_llama3.3', 'ollama_mistral', 'bedrock_nova_pro', 'bedrock_claude_sonnet', 'bedrock_claude_haiku') NOT NULL,
    processing_time DECIMAL(6,3) NOT NULL, -- Seconds
    token_count INTEGER NULL,
    cost_estimate DECIMAL(8,4) NULL, -- USD cost for cloud models

    -- Quality Metrics
    confidence_score DECIMAL(3,2) NULL, -- AI confidence in response
    user_feedback ENUM('helpful', 'neutral', 'unhelpful') NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE SET NULL,
    INDEX idx_ai_conversations_user (user_id),
    INDEX idx_ai_conversations_character (character_id),
    INDEX idx_ai_conversations_session (conversation_id),
    INDEX idx_ai_conversations_model (ai_model_used),
    INDEX idx_ai_conversations_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- OCR Extractions table
CREATE TABLE ocr_extractions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,

    -- Image Information
    image_path VARCHAR(500) NOT NULL, -- Path to uploaded image
    image_hash VARCHAR(64) NOT NULL, -- SHA-256 hash for deduplication
    image_size INTEGER NOT NULL, -- File size in bytes
    image_dimensions VARCHAR(20) NULL, -- "1920x1080" format

    -- OCR Processing
    extracted_data JSON NOT NULL, -- Raw OCR results
    processed_data JSON NULL, -- Cleaned and structured data
    confidence_score DECIMAL(5,2) NOT NULL, -- Average confidence
    processing_time DECIMAL(6,3) NOT NULL, -- Seconds

    -- Data Classification
    detected_screen_type ENUM('training', 'race', 'character_stats', 'support_cards', 'skills', 'unknown') NOT NULL,
    extraction_success BOOLEAN DEFAULT FALSE,
    validation_errors JSON NULL,

    -- Usage Tracking
    used_for_import BOOLEAN DEFAULT FALSE,
    import_character_id BIGINT UNSIGNED NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (import_character_id) REFERENCES characters(id) ON DELETE SET NULL,
    INDEX idx_ocr_extractions_user (user_id),
    INDEX idx_ocr_extractions_hash (image_hash),
    INDEX idx_ocr_extractions_type (detected_screen_type),
    INDEX idx_ocr_extractions_success (extraction_success)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Database Optimization Strategy

#### Indexing Strategy

```sql
-- Performance-Critical Indexes
-- Character lookups by user (most common query)
CREATE INDEX idx_characters_user_scenario ON characters(user_id, scenario_type);

-- Career progression queries
CREATE INDEX idx_careers_character_active ON careers(character_id, end_date);
CREATE INDEX idx_training_career_turn ON training_sessions(career_id, turn_number);

-- Skill management queries
CREATE INDEX idx_skills_character_acquired ON skills(character_id, is_acquired);
CREATE INDEX idx_skills_evolution_chain ON skills(evolves_from, evolves_to);

-- AI conversation history
CREATE INDEX idx_ai_conversations_session_time ON ai_conversations(conversation_id, created_at);

-- External data cache efficiency
CREATE INDEX idx_external_data_active_expires ON external_data(is_active, cache_expires);

-- OCR processing queries
CREATE INDEX idx_ocr_extractions_user_type ON ocr_extractions(user_id, detected_screen_type);
```

#### Database Configuration Optimization

```php
// config/database.php - MySQL Optimization
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'umamusume-career-planner'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => 'ucp_',
    'strict' => true,
    'engine' => 'InnoDB',
    'options' => [
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    ],
    // Performance optimizations
    'dump' => [
        'dump_binary_path' => env('DB_DUMP_PATH', '/usr/bin'),
        'use_single_transaction' => true,
        'timeout' => 60 * 5, // 5 minutes
    ],
],
```

#### Query Optimization Patterns

```php
// Eloquent Optimization Examples

// Efficient character loading with relationships
$character = Character::with([
    'aptitudes',
    'factors' => function ($query) {
        $query->where('factor_type', 'blue_stat');
    },
    'skills' => function ($query) {
        $query->where('is_acquired', true);
    },
    'supportCards' => function ($query) {
        $query->orderBy('position_slot');
    }
])->find($characterId);

// Optimized career history queries
$recentCareers = Career::select(['id', 'character_id', 'final_grade', 'created_at'])
    ->where('character_id', $characterId)
    ->whereNotNull('end_date')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

// Efficient training session analysis
$trainingEfficiency = TrainingSession::selectRaw('
        training_type,
        AVG(JSON_EXTRACT(stat_gains, "$.speed")) as avg_speed_gain,
        AVG(JSON_EXTRACT(stat_gains, "$.stamina")) as avg_stamina_gain,
        COUNT(*) as session_count
    ')
    ->where('career_id', $careerId)
    ->groupBy('training_type')
    ->get();

// Optimized skill hint tracking
$skillHints = Skill::select(['skill_name', 'hint_count', 'discount_percentage'])
    ->where('character_id', $characterId)
    ->where('is_acquired', false)
    ->where('hint_count', '>', 0)
    ->orderBy('discount_percentage', 'desc')
    ->get();
```

## API Design

### RESTful API Architecture

#### API Structure Overview

```text
/api/v1/
├── auth/
│   ├── POST /login
│   ├── POST /logout
│   ├── POST /register
│   └── GET /user
├── characters/
│   ├── GET /characters
│   ├── POST /characters
│   ├── GET /characters/{id}
│   ├── PUT /characters/{id}
│   ├── DELETE /characters/{id}
│   ├── GET /characters/{id}/aptitudes
│   ├── PUT /characters/{id}/aptitudes
│   ├── GET /characters/{id}/factors
│   ├── POST /characters/{id}/factors
│   └── GET /characters/{id}/skills
├── careers/
│   ├── GET /characters/{id}/careers
│   ├── POST /characters/{id}/careers
│   ├── GET /careers/{id}
│   ├── PUT /careers/{id}
│   ├── DELETE /careers/{id}
│   ├── GET /careers/{id}/training-sessions
│   ├── POST /careers/{id}/training-sessions
│   └── GET /careers/{id}/races
├── optimization/
│   ├── POST /characters/{id}/training-predictions
│   ├── POST /characters/{id}/race-strategy
│   ├── POST /characters/{id}/skill-optimization
│   └── GET /characters/{id}/recommendations
├── ai/
│   ├── POST /ai/chat
│   ├── GET /ai/conversations
│   └── DELETE /ai/conversations/{id}
├── external/
│   ├── GET /external/character-data
│   ├── GET /external/support-cards
│   ├── GET /external/race-calendar
│   └── GET /external/meta-data
└── ocr/
    ├── POST /ocr/extract
    ├── GET /ocr/extractions
    └── POST /ocr/import
```

#### API Response Format

```php
// Standardized API Response Structure
class ApiResponse
{
    public function success($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ], $code);
    }

    public function error(string $message, int $code = 400, $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => now()->toISOString(),
        ], $code);
    }

    public function paginated($data, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data->items(),
            'pagination' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ],
            'timestamp' => now()->toISOString(),
        ]);
    }
}
```

### Core API Endpoints

#### Character Management API

```php
// Character Controller
class CharacterController extends Controller
{
    public function __construct(
        private CharacterService $characterService,
        private ApiResponse $response
    ) {}

    /**
     * GET /api/v1/characters
     * List all characters for authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $characters = $this->characterService->getUserCharacters(
            $request->user()->id,
            $request->get('scenario_type')
        );

        return $this->response->success($characters);
    }

    /**
     * POST /api/v1/characters
     * Create new character
     */
    public function store(CreateCharacterRequest $request): JsonResponse
    {
        $character = $this->characterService->createCharacter($request->validated());

        return $this->response->success($character, 'Character created successfully', 201);
    }

    /**
     * GET /api/v1/characters/{id}
     * Get character details with relationships
     */
    public function show(int $id): JsonResponse
    {
        $character = $this->characterService->getCharacterWithRelations($id);

        return $this->response->success($character);
    }

    /**
     * PUT /api/v1/characters/{id}
     * Update character information
     */
    public function update(UpdateCharacterRequest $request, int $id): JsonResponse
    {
        $character = $this->characterService->updateCharacter($id, $request->validated());

        return $this->response->success($character, 'Character updated successfully');
    }
}
```

#### Training Optimization API

```php
// Training Optimization Controller
class TrainingOptimizationController extends Controller
{
    public function __construct(
        private TrainingOptimizationService $optimizationService,
        private ApiResponse $response
    ) {}

    /**
     * POST /api/v1/characters/{id}/training-predictions
     * Get training predictions for current turn
     */
    public function getTrainingPredictions(TrainingPredictionRequest $request, int $characterId): JsonResponse
    {
        $predictions = $this->optimizationService->calculateTrainingPredictions(
            $characterId,
            $request->validated()
        );

        return $this->response->success($predictions);
    }

    /**
     * POST /api/v1/characters/{id}/race-strategy
     * Get race strategy recommendations
     */
    public function getRaceStrategy(RaceStrategyRequest $request, int $characterId): JsonResponse
    {
        $strategy = $this->optimizationService->calculateRaceStrategy(
            $characterId,
            $request->validated()
        );

        return $this->response->success($strategy);
    }

    /**
     * POST /api/v1/characters/{id}/skill-optimization
     * Get skill acquisition recommendations
     */
    public function getSkillOptimization(SkillOptimizationRequest $request, int $characterId): JsonResponse
    {
        $optimization = $this->optimizationService->optimizeSkillAcquisition(
            $characterId,
            $request->validated()
        );

        return $this->response->success($optimization);
    }
}
```

#### AI Integration API

```php
// AI Controller
class AIController extends Controller
{
    public function __construct(
        private HybridAIService $aiService,
        private ApiResponse $response
    ) {}

    /**
     * POST /api/v1/ai/chat
     * Process AI chat request
     */
    public function chat(AIChatRequest $request): JsonResponse
    {
        $aiRequest = new AIRequest([
            'message' => $request->input('message'),
            'context' => $request->input('context', []),
            'character_id' => $request->input('character_id'),
            'conversation_id' => $request->input('conversation_id'),
        ]);

        $response = $this->aiService->processRequest($aiRequest);

        return $this->response->success($response);
    }

    /**
     * GET /api/v1/ai/conversations
     * Get conversation history
     */
    public function getConversations(Request $request): JsonResponse
    {
        $conversations = $this->aiService->getConversationHistory(
            $request->user()->id,
            $request->get('character_id'),
            $request->get('limit', 50)
        );

        return $this->response->success($conversations);
    }
}
```

### API Middleware Configuration

```php
// API Rate Limiting Middleware
class APIRateLimitMiddleware
{
    public function handle(Request $request, Closure $next, string $maxAttempts = '60', string $decayMinutes = '1')
    {
        $key = $this->resolveRequestSignature($request);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => RateLimiter::availableIn($key),
            ], 429);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        return $next($request);
    }
}

// API Authentication Middleware
class APIAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        return $next($request);
    }
}
```

## Frontend Architecture

### Component-Based Architecture

#### Core Component Structure

```javascript
// Component Architecture Overview
const ComponentHierarchy = {
  App: {
    Layout: {
      Header: ['Navigation', 'UserMenu', 'ThemeToggle'],
      Sidebar: ['CharacterList', 'QuickActions', 'AIChat'],
      Main: ['Router', 'ContentArea'],
      Footer: ['StatusBar', 'VersionInfo']
    },
    Pages: {
      Dashboard: ['CharacterOverview', 'RecentActivity', 'QuickStats'],
      Character: {
        Overview: ['StatDisplay', 'AptitudeGrid', 'ProgressChart'],
        Training: ['TrainingOptions', 'PredictionPanel', 'EnergyMeter'],
        Skills: ['SkillTree', 'HintTracker', 'SPCalculator'],
        Races: ['RaceCalendar', 'StrategySelector', 'PerformanceAnalysis']
      },
      Career: {
        Planning: ['GoalSetter', 'SupportCardDeck', 'LegacyTeam'],
        Progress: ['TurnTracker', 'DecisionHistory', 'Analytics'],
        Results: ['FinalStats', 'Achievements', 'Comparison']
      }
    },
    Shared: {
      UI: ['Button', 'Card', 'Modal', 'Tooltip', 'LoadingSpinner'],
      Forms: ['Input', 'Select', 'Checkbox', 'FormGroup'],
      Data: ['DataTable', 'Chart', 'StatBar', 'ProgressRing']
    }
  }
};
```

#### State Management Architecture

```javascript
// Global State Management using Context API
/**
 * @typedef {Object} AppState
 * @property {Object} user - Current user information
 * @property {Array} characters - User's characters
 * @property {Object} currentCharacter - Currently selected character
 * @property {Object} ui - UI state (theme, sidebar, modals)
 * @property {Object} cache - Cached API responses
 */

/**
 * App State Context
 */
const AppStateContext = createContext();

/**
 * App State Provider Component
 * @param {Object} props
 * @param {React.ReactNode} props.children
 */
const AppStateProvider = ({ children }) => {
  const [state, dispatch] = useReducer(appStateReducer, initialState);

  // Character management actions
  const actions = {
    setCurrentCharacter: (character) => {
      dispatch({ type: 'SET_CURRENT_CHARACTER', payload: character });
    },

    updateCharacterStats: (stats) => {
      dispatch({ type: 'UPDATE_CHARACTER_STATS', payload: stats });
    },

    addTrainingSession: (session) => {
      dispatch({ type: 'ADD_TRAINING_SESSION', payload: session });
    },

    updateUI: (uiChanges) => {
      dispatch({ type: 'UPDATE_UI', payload: uiChanges });
    },

    cacheAPIResponse: (key, data) => {
      dispatch({ type: 'CACHE_API_RESPONSE', payload: { key, data } });
    }
  };

  return (
    <AppStateContext.Provider value={{ state, actions }}>
      {children}
    </AppStateContext.Provider>
  );
};

/**
 * Custom hook for accessing app state
 * @returns {Object} State and actions
 */
const useAppState = () => {
  const context = useContext(AppStateContext);
  if (!context) {
    throw new Error('useAppState must be used within AppStateProvider');
  }
  return context;
};
```

#### Responsive Design System

```css
/* Responsive Design Tokens */
@theme {
  /* Breakpoints */
  --breakpoint-sm: 640px;
  --breakpoint-md: 768px;
  --breakpoint-lg: 1024px;
  --breakpoint-xl: 1280px;
  --breakpoint-2xl: 1536px;

  /* Container Queries */
  --container-xs: 320px;
  --container-sm: 384px;
  --container-md: 448px;
  --container-lg: 512px;
  --container-xl: 576px;

  /* Fluid Typography */
  --text-xs: clamp(0.75rem, 0.7rem + 0.25vw, 0.875rem);
  --text-sm: clamp(0.875rem, 0.8rem + 0.375vw, 1rem);
  --text-base: clamp(1rem, 0.9rem + 0.5vw, 1.125rem);
  --text-lg: clamp(1.125rem, 1rem + 0.625vw, 1.25rem);
  --text-xl: clamp(1.25rem, 1.1rem + 0.75vw, 1.5rem);

  /* Spacing Scale */
  --space-1: clamp(0.25rem, 0.2rem + 0.25vw, 0.375rem);
  --space-2: clamp(0.5rem, 0.4rem + 0.5vw, 0.75rem);
  --space-4: clamp(1rem, 0.8rem + 1vw, 1.5rem);
  --space-8: clamp(2rem, 1.6rem + 2vw, 3rem);
}

/* Responsive Grid System */
.grid-responsive {
  display: grid;
  gap: var(--space-4);
  grid-template-columns: repeat(auto-fit, minmax(min(300px, 100%), 1fr));
}

.grid-character-overview {
  display: grid;
  gap: var(--space-4);
  grid-template-areas:
    "stats"
    "aptitudes"
    "progress";

  @container (min-width: 768px) {
    grid-template-areas:
      "stats aptitudes"
      "progress progress";
    grid-template-columns: 1fr 1fr;
  }

  @container (min-width: 1024px) {
    grid-template-areas: "stats aptitudes progress";
    grid-template-columns: 1fr 1fr 1fr;
  }
}

/* Component Responsive Patterns */
.training-panel {
  @apply flex flex-col gap-4;

  @screen md {
    @apply flex-row;
  }

  @container (min-width: 640px) {
    .training-option {
      @apply flex-1 min-w-0;
    }
  }
}
```

#### Accessibility Implementation

```javascript
// Accessibility Utilities
/**
 * Focus Management Hook
 * @param {string} initialFocus - Initial focus target selector
 */
const useFocusManagement = (initialFocus = null) => {
  const focusRef = useRef(null);
  const previousFocusRef = useRef(null);

  const setFocus = useCallback((element) => {
    if (element) {
      previousFocusRef.current = document.activeElement;
      element.focus();
    }
  }, []);

  const restoreFocus = useCallback(() => {
    if (previousFocusRef.current) {
      previousFocusRef.current.focus();
    }
  }, []);

  useEffect(() => {
    if (initialFocus && focusRef.current) {
      const element = focusRef.current.querySelector(initialFocus);
      if (element) setFocus(element);
    }
  }, [initialFocus, setFocus]);

  return { focusRef, setFocus, restoreFocus };
};

/**
 * Keyboard Navigation Hook
 * @param {Array} items - Navigable items
 * @param {Function} onSelect - Selection handler
 */
const useKeyboardNavigation = (items, onSelect) => {
  const [activeIndex, setActiveIndex] = useState(0);

  const handleKeyDown = useCallback((event) => {
    switch (event.key) {
      case 'ArrowDown':
        event.preventDefault();
        setActiveIndex((prev) => (prev + 1) % items.length);
        break;
      case 'ArrowUp':
        event.preventDefault();
        setActiveIndex((prev) => (prev - 1 + items.length) % items.length);
        break;
      case 'Enter':
      case ' ':
        event.preventDefault();
        onSelect(items[activeIndex]);
        break;
      case 'Escape':
        event.preventDefault();
        setActiveIndex(0);
        break;
    }
  }, [items, activeIndex, onSelect]);

  return { activeIndex, handleKeyDown };
};

// Accessible Component Example
/**
 * @typedef {Object} AccessibleButtonProps
 * @property {string} [ariaLabel] - Accessible label
 * @property {string} [ariaDescribedBy] - Description reference
 * @property {boolean} [disabled] - Disabled state
 * @property {Function} onClick - Click handler
 * @property {React.ReactNode} children - Button content
 */

/**
 * Accessible Button Component
 * @param {AccessibleButtonProps} props
 */
const AccessibleButton = ({
  ariaLabel,
  ariaDescribedBy,
  disabled = false,
  onClick,
  children,
  className = '',
  ...props
}) => {
  return (
    <button
      className={`btn-primary focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 ${className}`}
      aria-label={ariaLabel}
      aria-describedby={ariaDescribedBy}
      disabled={disabled}
      onClick={onClick}
      {...props}
    >
      {children}
    </button>
  );
};
```

## AI Integration Architecture

### Hybrid AI Processing System

#### Model Selection Logic

```php
// AI Model Router
class AIModelRouter
{
    private const COMPLEXITY_THRESHOLDS = [
        'simple' => 1000,    // Token count threshold
        'medium' => 4000,    // Token count threshold
        'complex' => 8000,   // Token count threshold
    ];

    private const PROCESSING_TIME_LIMITS = [
        'ollama_timeout' => 15,      // Seconds
        'bedrock_fallback' => 30,    // Seconds
    ];

    public function selectOptimalModel(AIRequest $request): string
    {
        // Analyze request complexity
        $complexity = $this->analyzeComplexity($request);
        $tokenCount = $request->getTokenCount();

        // Route based on complexity and requirements
        if ($this->requiresAdvancedReasoning($request)) {
            return $this->selectBedrockModel($complexity);
        }

        if ($tokenCount > self::COMPLEXITY_THRESHOLDS['complex']) {
            return 'bedrock_nova_pro';
        }

        if ($this->isOllamaAvailable() && $tokenCount <= self::COMPLEXITY_THRESHOLDS['medium']) {
            return $this->selectOllamaModel($complexity);
        }

        return $this->selectBedrockModel($complexity);
    }

    private function selectOllamaModel(string $complexity): string
    {
        return match($complexity) {
            'simple' => 'ollama_llama3.3:8b',
            'medium' => 'ollama_llama3.3:70b',
            'complex' => 'ollama_qwen2.5:32b',
            default => 'ollama_llama3.3:8b'
        };
    }

    private function selectBedrockModel(string $complexity): string
    {
        return match($complexity) {
            'simple' => 'bedrock_nova_lite',
            'medium' => 'bedrock_claude_haiku',
            'complex' => 'bedrock_claude_sonnet',
            default => 'bedrock_nova_pro'
        };
    }

    private function requiresAdvancedReasoning(AIRequest $request): bool
    {
        return $request->hasMultiStepReasoning()
            || $request->requiresRAG()
            || $request->hasComplexGameMechanics()
            || $request->requiresStrategicPlanning();
    }
}
```

#### Prompt Engineering System

```php
// Advanced Prompt Engineering
class UmamusumePromptEngine
{
    private const SYSTEM_PROMPTS = [
        'career_advisor' => 'You are an expert Umamusume Pretty Derby career advisor with comprehensive knowledge of game mechanics, optimal training strategies, and meta analysis. You provide precise, actionable advice based on character stats, aptitudes, and career goals.',

        'training_optimizer' => 'You are a training optimization specialist for Umamusume Pretty Derby. You analyze character state, support card effects, and scenario mechanics to recommend optimal training sequences that maximize stat gains and skill acquisition.',

        'race_strategist' => 'You are a race strategy expert for Umamusume Pretty Derby. You evaluate character readiness, race requirements, and optimal running styles to maximize race performance and goal completion.',

        'skill_specialist' => 'You are a skill acquisition specialist for Umamusume Pretty Derby. You optimize SP allocation, hint collection strategies, and skill evolution paths to maximize character potential within SP constraints.'
    ];

    public function buildPrompt(AIRequest $request): string
    {
        $systemPrompt = $this->getSystemPrompt($request->getType());
        $contextData = $this->buildContextData($request);
        $userQuery = $request->getMessage();

        return $this->assemblePrompt($systemPrompt, $contextData, $userQuery);
    }

    private function buildContextData(AIRequest $request): array
    {
        $context = [];

        if ($request->hasCharacterContext()) {
            $character = $request->getCharacter();
            $context['character'] = [
                'name' => $character->name,
                'scenario' => $character->scenario_type,
                'stats' => $character->getCurrentStats(),
                'aptitudes' => $character->aptitudes->toArray(),
                'energy' => $character->energy_level,
                'mood' => $character->mood_status,
                'turn' => $character->current_turn,
                'career_stage' => $character->career_stage,
            ];

            if ($character->supportCards->isNotEmpty()) {
                $context['support_cards'] = $character->supportCards->map(function ($card) {
                    return [
                        'name' => $card->card_name,
                        'specialization' => $card->specialization,
                        'friendship' => $card->friendship_level,
                        'position' => $card->position_slot,
                    ];
                })->toArray();
            }

            if ($character->skills->isNotEmpty()) {
                $context['skills'] = [
                    'acquired' => $character->skills->where('is_acquired', true)->pluck('skill_name'),
                    'available' => $character->skills->where('is_acquired', false)->map(function ($skill) {
                        return [
                            'name' => $skill->skill_name,
                            'cost' => $skill->final_sp_cost,
                            'hints' => $skill->hint_count,
                        ];
                    }),
                ];
            }
        }

        if ($request->hasCareerContext()) {
            $career = $request->getCareer();
            $context['career'] = [
                'scenario' => $career->scenario_type,
                'turn' => $career->current_turn,
                'total_turns' => $career->total_turns,
                'recent_training' => $career->trainingSessions()
                    ->latest()
                    ->limit(5)
                    ->get()
                    ->toArray(),
            ];
        }

        return $context;
    }

    private function assemblePrompt(string $systemPrompt, array $context, string $userQuery): string
    {
        $prompt = $systemPrompt . "\n\n";

        if (!empty($context)) {
            $prompt .= "Current Context:\n";
            $prompt .= json_encode($context, JSON_PRETTY_PRINT) . "\n\n";
        }

        $prompt .= "User Query: " . $userQuery . "\n\n";
        $prompt .= "Please provide a detailed, actionable response based on the current context and game mechanics.";

        return $prompt;
    }
}
```

#### Conversation Management

```php
// AI Conversation Manager
class AIConversationManager
{
    private const MAX_CONTEXT_TOKENS = 8000;
    private const CONTEXT_COMPRESSION_RATIO = 0.7;

    public function __construct(
        private ConversationRepository $conversationRepo,
        private CacheManager $cache
    ) {}

    public function getConversationContext(string $conversationId, int $maxTokens = null): array
    {
        $maxTokens = $maxTokens ?? self::MAX_CONTEXT_TOKENS;

        $cacheKey = "conversation_context_{$conversationId}_{$maxTokens}";

        return $this->cache->remember($cacheKey, 300, function () use ($conversationId, $maxTokens) {
            $messages = $this->conversationRepo->getRecentMessages($conversationId, 50);

            return $this->compressContext($messages, $maxTokens);
        });
    }

    public function addMessage(string $conversationId, AIMessage $message): void
    {
        $this->conversationRepo->storeMessage($conversationId, $message);

        // Invalidate context cache
        $this->cache->tags(['conversation', $conversationId])->flush();

        // Trigger background context optimization
        OptimizeConversationContext::dispatch($conversationId);
    }

    private function compressContext(Collection $messages, int $maxTokens): array
    {
        $totalTokens = $messages->sum('token_count');

        if ($totalTokens <= $maxTokens) {
            return $messages->toArray();
        }

        // Implement intelligent context compression
        $compressed = [];
        $currentTokens = 0;
        $targetTokens = (int)($maxTokens * self::CONTEXT_COMPRESSION_RATIO);

        // Always include the most recent messages
        foreach ($messages->reverse() as $message) {
            if ($currentTokens + $message->token_count <= $targetTokens) {
                $compressed[] = $message;
                $currentTokens += $message->token_count;
            } else {
                break;
            }
        }

        return array_reverse($compressed);
    }
}
```

## Security Architecture

### Authentication & Authorization

#### Laravel Sanctum Configuration

```php
// Sanctum Configuration
// config/sanctum.php
return [
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        Sanctum::currentApplicationUrlWithPort()
    ))),

    'guard' => ['web'],

    'expiration' => null, // Never expire for local app

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
        'validate_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
    ],
];

// Authentication Service
class AuthenticationService
{
    public function __construct(
        private UserRepository $userRepo,
        private RateLimiter $rateLimiter
    ) {}

    public function authenticate(LoginRequest $request): AuthResult
    {
        $this->checkRateLimit($request->ip());

        $credentials = $request->only(['email', 'password']);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            $this->incrementRateLimit($request->ip());

            throw new AuthenticationException('Invalid credentials');
        }

        $user = Auth::user();
        $token = $user->createToken('umamusume-career-planner')->plainTextToken;

        return new AuthResult([
            'user' => $user,
            'token' => $token,
            'expires_at' => null, // Local app - no expiration
        ]);
    }

    private function checkRateLimit(string $ip): void
    {
        $key = "login_attempts:{$ip}";

        if ($this->rateLimiter->tooManyAttempts($key, 5)) {
            $seconds = $this->rateLimiter->availableIn($key);

            throw new TooManyRequestsException(
                "Too many login attempts. Try again in {$seconds} seconds."
            );
        }
    }
}
```

#### Input Validation & Sanitization

```php
// Advanced Form Requests
class CreateCharacterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Character::class);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\p{L}\p{N}\s\-_]+$/u', // Unicode letters, numbers, spaces, hyphens, underscores
            ],
            'scenario_type' => [
                'required',
                'in:ura_finale,unity_cup'
            ],
            'stats' => [
                'required',
                'array'
            ],
            'stats.speed' => [
                'required',
                'integer',
                'min:0',
                'max:1200'
            ],
            'stats.stamina' => [
                'required',
                'integer',
                'min:0',
                'max:1200'
            ],
            'stats.power' => [
                'required',
                'integer',
                'min:0',
                'max:1200'
            ],
            'stats.guts' => [
                'required',
                'integer',
                'min:0',
                'max:1200'
            ],
            'stats.wit' => [
                'required',
                'integer',
                'min:0',
                'max:1200'
            ],
            'aptitudes' => [
                'required',
                'array'
            ],
            'aptitudes.*' => [
                'required',
                'in:G,G+,F,F+,E,E+,D,D+,C,C+,B,B+,A,A+,S,S+,SS'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'Character name can only contain letters, numbers, spaces, hyphens, and underscores.',
            'stats.*.min' => 'Stat values must be between 0 and 1200.',
            'stats.*.max' => 'Stat values must be between 0 and 1200.',
            'aptitudes.*.in' => 'Aptitude values must be valid grades from G to SS.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Sanitize input data
        $this->merge([
            'name' => strip_tags($this->name),
            'stats' => array_map('intval', $this->stats ?? []),
        ]);
    }
}

// Custom Validation Rules
class ValidAptitudeGrade implements Rule
{
    private const VALID_GRADES = [
        'G', 'G+', 'F', 'F+', 'E', 'E+', 'D', 'D+',
        'C', 'C+', 'B', 'B+', 'A', 'A+', 'S', 'S+', 'SS'
    ];

    public function passes($attribute, $value): bool
    {
        return in_array($value, self::VALID_GRADES, true);
    }

    public function message(): string
    {
        return 'The :attribute must be a valid aptitude grade (G through SS).';
    }
}
```

#### Data Protection & Privacy

```php
// Data Encryption Service
class DataEncryptionService
{
    public function __construct(
        private Encrypter $encrypter
    ) {}

    public function encryptSensitiveData(array $data): array
    {
        $sensitiveFields = ['email', 'personal_notes', 'external_api_keys'];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = $this->encrypter->encrypt($data[$field]);
            }
        }

        return $data;
    }

    public function decryptSensitiveData(array $data): array
    {
        $sensitiveFields = ['email', 'personal_notes', 'external_api_keys'];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                try {
                    $data[$field] = $this->encrypter->decrypt($data[$field]);
                } catch (DecryptException $e) {
                    Log::warning("Failed to decrypt {$field}", ['error' => $e->getMessage()]);
                    $data[$field] = null;
                }
            }
        }

        return $data;
    }
}

// Privacy Compliance Middleware
class PrivacyComplianceMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Ensure no personal data is logged
        if ($this->containsPersonalData($request)) {
            $request->merge([
                '_privacy_filtered' => true,
                '_original_data' => $this->filterPersonalData($request->all())
            ]);
        }

        $response = $next($request);

        // Add privacy headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }

    private function containsPersonalData(Request $request): bool
    {
        $personalFields = ['email', 'name', 'password', 'phone', 'address'];

        return collect($personalFields)->some(function ($field) use ($request) {
            return $request->has($field);
        });
    }
}
```

## Performance & Scalability

### Caching Strategy

#### Multi-Tier Caching Implementation

```php
// Advanced Caching Manager
class AdvancedCacheManager
{
    private const CACHE_TIERS = [
        'memory' => 'array',      // In-memory for request lifecycle
        'redis' => 'redis',       // Redis for session/application cache
        'database' => 'database', // Database for persistent cache
    ];

    private const TTL_STRATEGIES = [
        'training_predictions' => 300,    // 5 minutes
        'character_data' => 3600,         // 1 hour
        'external_api' => 7200,           // 2 hours
        'static_game_data' => 86400,      // 24 hours
        'user_preferences' => 604800,     // 1 week
    ];

    public function __construct(
        private CacheManager $cache,
        private RedisManager $redis
    ) {}

    public function remember(string $key, string $strategy, callable $callback)
    {
        $ttl = self::TTL_STRATEGIES[$strategy] ?? 3600;

        // Try memory cache first
        if ($cached = $this->getFromMemory($key)) {
            return $cached;
        }

        // Try Redis cache
        if ($cached = $this->getFromRedis($key)) {
            $this->storeInMemory($key, $cached, 60); // 1 minute memory cache
            return $cached;
        }

        // Generate fresh data
        $data = $callback();

        // Store in all tiers
        $this->storeInRedis($key, $data, $ttl);
        $this->storeInMemory($key, $data, 60);

        return $data;
    }

    public function invalidatePattern(string $pattern): void
    {
        // Invalidate Redis keys matching pattern
        $keys = $this->redis->keys($pattern);
        if (!empty($keys)) {
            $this->redis->del($keys);
        }

        // Clear memory cache
        $this->clearMemoryPattern($pattern);
    }

    public function warmCache(): void
    {
        // Warm frequently accessed data
        $this->warmStaticGameData();
        $this->warmUserPreferences();
        $this->warmExternalAPIData();
    }

    private function warmStaticGameData(): void
    {
        // Pre-load character base stats, aptitudes, skills
        $gameDataService = app(GameDataService::class);

        $this->remember('game_data:characters', 'static_game_data', function () use ($gameDataService) {
            return $gameDataService->getAllCharacterData();
        });

        $this->remember('game_data:skills', 'static_game_data', function () use ($gameDataService) {
            return $gameDataService->getAllSkillData();
        });

        $this->remember('game_data:support_cards', 'static_game_data', function () use ($gameDataService) {
            return $gameDataService->getAllSupportCardData();
        });
    }
}
```

#### Database Optimization

```php
// Database Performance Optimizer
class DatabaseOptimizer
{
    public function optimizeCharacterQueries(): void
    {
        // Add composite indexes for common query patterns
        Schema::table('characters', function (Blueprint $table) {
            $table->index(['user_id', 'scenario_type'], 'idx_user_scenario');
            $table->index(['user_id', 'created_at'], 'idx_user_created');
            $table->index(['scenario_type', 'career_stage'], 'idx_scenario_stage');
        });

        Schema::table('training_sessions', function (Blueprint $table) {
            $table->index(['career_id', 'turn_number'], 'idx_career_turn');
            $table->index(['training_type', 'created_at'], 'idx_type_created');
        });

        Schema::table('skills', function (Blueprint $table) {
            $table->index(['character_id', 'is_acquired'], 'idx_character_acquired');
            $table->index(['skill_type', 'sp_cost'], 'idx_type_cost');
        });
    }

    public function configureConnectionPooling(): void
    {
        // Configure MySQL connection pooling
        config([
            'database.connections.mysql.options' => [
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET SESSION sql_mode="STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION"',
            ]
        ]);
    }

    public function enableQueryOptimization(): void
    {
        // Enable Eloquent strict mode
        Model::shouldBeStrict();

        // Prevent lazy loading in production
        Model::preventLazyLoading(!app()->isProduction());

        // Prevent silently discarding attributes
        Model::preventSilentlyDiscardingAttributes(!app()->isProduction());

        // Prevent accessing missing attributes
        Model::preventAccessingMissingAttributes(!app()->isProduction());
    }
}

// Optimized Repository Patterns
class OptimizedCharacterRepository implements CharacterRepositoryInterface
{
    public function __construct(
        private Character $model,
        private CacheManager $cache
    ) {}

    public function findWithOptimizedRelations(int $id): ?Character
    {
        return $this->cache->remember("character_full_{$id}", 3600, function () use ($id) {
            return $this->model
                ->select([
                    'id', 'user_id', 'name', 'scenario_type',
                    'speed_stat', 'stamina_stat', 'power_stat', 'guts_stat', 'wit_stat',
                    'energy_level', 'mood_status', 'career_stage', 'current_turn'
                ])
                ->with([
                    'aptitudes:id,character_id,sprint_aptitude,mile_aptitude,medium_aptitude,long_aptitude,turf_aptitude,dirt_aptitude',
                    'factors' => function ($query) {
                        $query->select(['id', 'character_id', 'factor_type', 'factor_category', 'stat_bonus'])
                              ->where('factor_type', '!=', 'white_normal'); // Exclude less important factors
                    },
                    'skills' => function ($query) {
                        $query->select(['id', 'character_id', 'skill_name', 'skill_type', 'final_sp_cost', 'is_acquired'])
                              ->orderBy('is_acquired', 'desc')
                              ->orderBy('final_sp_cost', 'asc');
                    },
                    'supportCards' => function ($query) {
                        $query->select(['id', 'character_id', 'card_name', 'specialization', 'friendship_level', 'position_slot'])
                              ->orderBy('position_slot');
                    }
                ])
                ->find($id);
        });
    }

    public function getUserCharactersOptimized(int $userId, ?string $scenarioType = null): Collection
    {
        $cacheKey = "user_characters_{$userId}" . ($scenarioType ? "_{$scenarioType}" : '');

        return $this->cache->remember($cacheKey, 1800, function () use ($userId, $scenarioType) {
            $query = $this->model
                ->select(['id', 'name', 'scenario_type', 'career_stage', 'current_turn', 'created_at'])
                ->where('user_id', $userId);

            if ($scenarioType) {
                $query->where('scenario_type', $scenarioType);
            }

            return $query->orderBy('created_at', 'desc')->get();
        });
    }
}
```

### Queue Management

#### Background Job Processing

```php
// Training Prediction Job
class CalculateTrainingPredictionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;
    public int $tries = 3;
    public int $maxExceptions = 2;

    public function __construct(
        private int $characterId,
        private array $trainingOptions,
        private string $cacheKey
    ) {}

    public function handle(TrainingPredictionEngine $engine, CacheManager $cache): void
    {
        try {
            $character = Character::with(['aptitudes', 'supportCards', 'skills'])->find($this->characterId);

            if (!$character) {
                Log::warning("Character not found for training prediction", ['id' => $this->characterId]);
                return;
            }

            $predictions = $engine->calculatePredictions($character, $this->trainingOptions);

            // Cache results for 5 minutes
            $cache->put($this->cacheKey, $predictions, 300);

            // Broadcast to user via WebSocket
            broadcast(new TrainingPredictionsCalculated($character->user_id, $predictions));

        } catch (Exception $e) {
            Log::error("Training prediction calculation failed", [
                'character_id' => $this->characterId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error("Training prediction job failed permanently", [
            'character_id' => $this->characterId,
            'error' => $exception->getMessage()
        ]);

        // Notify user of failure
        broadcast(new TrainingPredictionFailed($this->characterId));
    }
}

// Queue Configuration
// config/queue.php
return [
    'default' => env('QUEUE_CONNECTION', 'redis'),

    'connections' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => 90,
            'block_for' => null,
            'after_commit' => false,
        ],
    ],

    'batching' => [
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'job_batches',
    ],

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'failed_jobs',
    ],
];

// Horizon Configuration for Queue Monitoring
// config/horizon.php
return [
    'environments' => [
        'local' => [
            'supervisor-1' => [
                'connection' => 'redis',
                'queue' => ['default', 'training', 'ai', 'external-api'],
                'balance' => 'auto',
                'processes' => 3,
                'tries' => 3,
                'timeout' => 60,
                'nice' => 0,
            ],
        ],
    ],

    'trim' => [
        'recent' => 60,
        'pending' => 60,
        'completed' => 60,
        'recent_failed' => 10080,
        'failed' => 10080,
        'monitored' => 10080,
    ],
];
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property-Based Testing Overview

Property-based testing (PBT) validates software correctness by testing universal properties across many generated inputs. Each property is a formal specification that should hold for all valid inputs.

### Core Correctness Properties

#### Property 1: Character State Consistency

*For any* character state update operation, the character's stats should remain within valid ranges (0-1200) and aptitude grades should remain unchanged from their initial values.
**Validates: Requirements 1.1, 1.2**

#### Property 2: Training Prediction Accuracy

*For any* training session with known support card effects, the predicted stat gains should match the actual gains within a reasonable margin of error (±10%).
**Validates: Requirements 2.1, 2.2**

#### Property 3: SP Cost Calculation Consistency

*For any* skill with hint discounts, the final SP cost should equal the base cost minus (20% × hint_count) with a maximum 40% discount.
**Validates: Requirements 4.1, 4.2**

#### Property 4: Race Strategy Optimization

*For any* character with specific aptitudes and stats, the recommended running style should align with the character's strengths and race requirements.
**Validates: Requirements 3.3, 21.1**

#### Property 5: Energy Management Bounds

*For any* training sequence, the character's energy level should never exceed 100% or fall below 0%, and training failure rates should increase as energy decreases.
**Validates: Requirements 27.1, 27.4**

#### Property 6: Skill Evolution Integrity

*For any* skill evolution from Normal to Rare, the original Normal skill should be completely replaced by its Rare counterpart without duplication.
**Validates: Requirements 31.2, 31.3**

#### Property 7: Support Card Deck Validation

*For any* support card deck configuration, exactly 6 cards should be present (5 owned + 1 friend) with no duplicate positions.
**Validates: Requirements 6.1, 6.3**

#### Property 8: Friendship Training Bonuses

*For any* training session with multiple support card participants, the stat bonus should increase proportionally to participant count (2 participants = +2 bonus, 3 participants = +3 bonus).
**Validates: Requirements 20.2, 20.4**

#### Property 9: Cache Consistency

*For any* cached data with TTL expiration, accessing the data before expiration should return the cached value, and accessing after expiration should trigger fresh data generation.
**Validates: Requirements 14.4, 14.5**

#### Property 10: API Response Format Consistency

*For any* API endpoint response, the response should follow the standardized format with success/error status, message, data, and timestamp fields.
**Validates: Requirements 17.4, 12.2**

### Round-Trip Properties

#### Property 11: Character Data Serialization

*For any* valid character object, serializing to JSON then deserializing should produce an equivalent character with identical stats, aptitudes, and relationships.
**Validates: Requirements 8.1, 8.3**

#### Property 12: Training Session Round-Trip

*For any* training session data, storing to database then retrieving should preserve all training details, stat gains, and participant information.
**Validates: Requirements 5.1, 5.2**

### Invariant Properties

#### Property 13: Stat Breakpoint Consistency

*For any* character, stats above 1200 should provide diminished returns, and the +400 hidden race boost should be consistently applied during race calculations.
**Validates: Requirements 1.1, 3.2**

#### Property 14: Aptitude Immutability

*For any* character throughout their career, aptitude grades should remain constant and never change through training or events.
**Validates: Requirements 10.1, 10.4**

#### Property 15: Career Turn Progression

*For any* career, turn numbers should progress sequentially from 1 to maximum (60-72), and no turn should be skipped or duplicated.
**Validates: Requirements 22.1, 22.2**

### Error Handling Properties

#### Property 16: Invalid Input Rejection

*For any* invalid character stat input (negative values, values > 1200, invalid aptitude grades), the system should reject the input with appropriate error messages.
**Validates: Requirements 17.2, 12.1**

#### Property 17: External API Fallback

*For any* external API failure or timeout, the system should gracefully fallback to cached data or alternative endpoints without losing functionality.
**Validates: Requirements 14.2, 14.3**

#### Property 18: AI Model Fallback

*For any* AI processing request, if the primary model (Ollama) fails or times out, the system should automatically fallback to cloud models (Bedrock) while maintaining conversation context.
**Validates: Requirements 13.1, 13.5**

## Error Handling

### Comprehensive Error Management Strategy

#### Exception Hierarchy

```php
// Custom Exception Classes
abstract class UmamusumeException extends Exception
{
    protected array $context = [];

    public function __construct(string $message = '', array $context = [], int $code = 0, ?Throwable $previous = null)
    {
        $this->context = $context;
        parent::__construct($message, $code, $previous);
    }

    public function getContext(): array
    {
        return $this->context;
    }
}

class CharacterValidationException extends UmamusumeException {}
class TrainingPredictionException extends UmamusumeException {}
class SkillAcquisitionException extends UmamusumeException {}
class ExternalAPIException extends UmamusumeException {}
class AIProcessingException extends UmamusumeException {}

// Global Exception Handler
class Handler extends ExceptionHandler
{
    protected $dontReport = [
        AuthenticationException::class,
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    public function register(): void
    {
        $this->reportable(function (UmamusumeException $e) {
            Log::error('Umamusume Application Error', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'context' => $e->getContext(),
                'trace' => $e->getTraceAsString(),
            ]);
        });

        $this->renderable(function (UmamusumeException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'error_type' => class_basename($e),
                    'context' => $e->getContext(),
                ], 400);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        });
    }
}
```

#### Circuit Breaker Pattern

```php
// Circuit Breaker for External APIs
class CircuitBreaker
{
    private const FAILURE_THRESHOLD = 5;
    private const RECOVERY_TIMEOUT = 60; // seconds
    private const HALF_OPEN_MAX_CALLS = 3;

    public function __construct(
        private CacheManager $cache,
        private string $serviceName
    ) {}

    public function call(callable $operation)
    {
        $state = $this->getState();

        switch ($state['status']) {
            case 'closed':
                return $this->callClosed($operation);
            case 'open':
                return $this->callOpen($operation);
            case 'half_open':
                return $this->callHalfOpen($operation);
        }
    }

    private function callClosed(callable $operation)
    {
        try {
            $result = $operation();
            $this->onSuccess();
            return $result;
        } catch (Exception $e) {
            $this->onFailure();
            throw $e;
        }
    }

    private function callOpen(callable $operation)
    {
        $state = $this->getState();

        if (time() - $state['last_failure'] > self::RECOVERY_TIMEOUT) {
            $this->setState('half_open');
            return $this->callHalfOpen($operation);
        }

        throw new CircuitBreakerOpenException("Circuit breaker is open for {$this->serviceName}");
    }

    private function callHalfOpen(callable $operation)
    {
        try {
            $result = $operation();
            $this->setState('closed');
            return $result;
        } catch (Exception $e) {
            $this->setState('open');
            throw $e;
        }
    }

    private function onSuccess(): void
    {
        $this->cache->forget("circuit_breaker_{$this->serviceName}");
    }

    private function onFailure(): void
    {
        $state = $this->getState();
        $state['failure_count']++;
        $state['last_failure'] = time();

        if ($state['failure_count'] >= self::FAILURE_THRESHOLD) {
            $state['status'] = 'open';
        }

        $this->cache->put("circuit_breaker_{$this->serviceName}", $state, 3600);
    }
}
```

## Testing Strategy

### Dual Testing Approach

The system employs both unit testing and property-based testing as complementary approaches:

- **Unit tests**: Verify specific examples, edge cases, and error conditions
- **Property tests**: Verify universal properties across all inputs
- Both are necessary for comprehensive coverage

### Property-Based Testing Configuration

- **Framework**: Use Pest PHP with Rapid for property-based testing
- **Minimum iterations**: 100 per property test
- **Test tagging**: Each property test references its design document property
- **Tag format**: `Feature: umamusume-career-planner-main, Property {number}: {property_text}`

### Unit Testing Balance

- Focus unit tests on specific examples and integration points
- Avoid excessive unit testing - property tests handle input coverage
- Unit tests should cover:
  - Specific examples demonstrating correct behavior
  - Integration between components
  - Edge cases and error conditions

### Testing Implementation Examples

```php
// Property-Based Test Example
test('character stats remain within valid bounds after any update', function () {
    // Feature: umamusume-career-planner-main, Property 1: Character State Consistency

    $this->forAll(
        Generator\choose(0, 1200), // speed
        Generator\choose(0, 1200), // stamina
        Generator\choose(0, 1200), // power
        Generator\choose(0, 1200), // guts
        Generator\choose(0, 1200)  // wit
    )->then(function ($speed, $stamina, $power, $guts, $wit) {
        $character = Character::factory()->create();

        $character->updateStats([
            'speed' => $speed,
            'stamina' => $stamina,
            'power' => $power,
            'guts' => $guts,
            'wit' => $wit,
        ]);

        expect($character->speed_stat)->toBeBetween(0, 1200);
        expect($character->stamina_stat)->toBeBetween(0, 1200);
        expect($character->power_stat)->toBeBetween(0, 1200);
        expect($character->guts_stat)->toBeBetween(0, 1200);
        expect($character->wit_stat)->toBeBetween(0, 1200);
    });
})->repeat(100);

// Unit Test Example
test('character creation with valid data succeeds', function () {
    $userData = [
        'name' => 'Test Character',
        'scenario_type' => 'ura_finale',
        'stats' => [
            'speed' => 300,
            'stamina' => 250,
            'power' => 200,
            'guts' => 150,
            'wit' => 180,
        ],
        'aptitudes' => [
            'sprint_aptitude' => 'A',
            'mile_aptitude' => 'B+',
            'medium_aptitude' => 'C',
            'long_aptitude' => 'D',
            'turf_aptitude' => 'A+',
            'dirt_aptitude' => 'C+',
        ],
    ];

    $character = Character::create($userData);

    expect($character->name)->toBe('Test Character');
    expect($character->scenario_type)->toBe('ura_finale');
    expect($character->speed_stat)->toBe(300);
});
```
