---
name: laravel-12-architecture-patterns
description: Laravel 12 architecture patterns, service layer design, repository pattern, and project structure. Use when designing features, refactoring code, or implementing new modules.
---

# Laravel 12 Architecture Patterns

## Overview

This skill provides architectural guidance for Laravel 12 applications, focusing on the patterns and structures used in this Uma Musume Career Planner project.

## Project Architecture

### Layered Architecture

```
┌─────────────────────────────────────┐
│     Presentation Layer              │
│  (Blade, Livewire, Alpine.js)      │
├─────────────────────────────────────┤
│     Application Layer               │
│  (Controllers, Livewire Actions,    │
│   Form Requests, Resources)         │
├─────────────────────────────────────┤
│     Domain Layer                    │
│  (Models, Enums, Business Rules)    │
├─────────────────────────────────────┤
│     Service Layer                   │
│  (Business Logic, Calculations)     │
├─────────────────────────────────────┤
│     Infrastructure Layer            │
│  (Database, Cache, External APIs)   │
└─────────────────────────────────────┘
```

### Directory Structure

```
app/
├── Console/
│   └── Commands/              # Artisan commands (auto-discovered)
├── Events/                    # Domain events
├── Exceptions/                # Custom exceptions
├── Helpers/                   # Helper functions
├── Http/
│   ├── Controllers/          # HTTP controllers
│   ├── Middleware/           # Request/response filters
│   ├── Requests/             # Form request validation
│   └── Resources/            # API resources
├── Jobs/                     # Queueable jobs
├── Listeners/                # Event listeners
├── Models/                   # Eloquent models
├── Policies/                 # Authorization policies
├── Providers/                # Service providers
├── Repositories/             # Repository pattern (optional)
└── Services/                 # Business logic services
```

## Laravel 12 Specific Changes

### Middleware Configuration

**Old (Laravel 10 and earlier)**:

```php
// app/Http/Kernel.php
protected $middleware = [
    // Global middleware
];
```

**New (Laravel 12)**:

```php
// bootstrap/app.php
return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(CustomMiddleware::class);
    })
    ->create();
```

### Console Commands

**Old**: Manual registration in `app/Console/Kernel.php`

**New**: Auto-discovered from `app/Console/Commands/`

- No registration needed
- Commands automatically available
- Configuration in `bootstrap/app.php` or `routes/console.php`

### Service Providers

**Registration**:

```php
// bootstrap/providers.php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\CacheServiceProvider::class,
    // ... other providers
];
```

## Service Layer Pattern

### Service Organization

```
app/Services/
├── Training/
│   ├── TrainingCalculationService.php
│   ├── TrainingPredictionService.php
│   └── TrainingOptimizationService.php
├── Race/
│   ├── RaceStrategyService.php
│   ├── RaceSimulationService.php
│   └── RaceAnalysisService.php
├── Skill/
│   ├── SkillService.php
│   ├── SkillAnalysisService.php
│   └── SkillEvolutionService.php
└── Character/
    ├── CharacterStateService.php
    └── CharacterMappingService.php
```

### Service Design Principles

1. **Single Responsibility**: One service = one domain concern
2. **Dependency Injection**: Constructor injection for dependencies
3. **Type Hints**: Explicit parameter and return types
4. **Testability**: Easy to mock and test
5. **Stateless**: No instance state between method calls

### Service Example

```php
<?php

namespace App\Services\Training;

use App\Models\Character;
use App\Models\TrainingSession;
use App\Repositories\CharacterRepositoryInterface;

class TrainingCalculationService
{
    public function __construct(
        private CharacterRepositoryInterface $characterRepository,
        private StatCalculatorService $statCalculator
    ) {}

    public function calculateTrainingGains(
        Character $character,
        string $facility,
        array $supportCards = []
    ): array {
        // Validate inputs
        $this->validateFacility($facility);
        
        // Calculate base gains
        $baseGains = $this->calculateBaseGains($facility);
        
        // Apply growth rates
        $modifiedGains = $this->applyGrowthRates(
            $baseGains,
            $character->growth_rates
        );
        
        // Apply support card bonuses
        $finalGains = $this->applySupportBonuses(
            $modifiedGains,
            $supportCards
        );
        
        return $finalGains;
    }

    private function validateFacility(string $facility): void
    {
        $validFacilities = ['speed', 'stamina', 'power', 'guts', 'wit'];
        
        if (!in_array($facility, $validFacilities)) {
            throw new \InvalidArgumentException(
                "Invalid facility: {$facility}"
            );
        }
    }

    // ... other private methods
}
```

## Repository Pattern

### Repository Interface

```php
<?php

namespace App\Repositories\Contracts;

use App\Models\Character;
use Illuminate\Database\Eloquent\Collection;

interface CharacterRepositoryInterface
{
    public function find(int $id): ?Character;
    
    public function findByUser(int $userId): Collection;
    
    public function create(array $data): Character;
    
    public function update(Character $character, array $data): bool;
    
    public function delete(Character $character): bool;
    
    public function withRelations(array $relations): self;
}
```

### Repository Implementation

```php
<?php

namespace App\Repositories;

use App\Models\Character;
use App\Repositories\Contracts\CharacterRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentCharacterRepository implements CharacterRepositoryInterface
{
    private array $relations = [];

    public function find(int $id): ?Character
    {
        return Character::with($this->relations)->find($id);
    }

    public function findByUser(int $userId): Collection
    {
        return Character::with($this->relations)
            ->where('user_id', $userId)
            ->get();
    }

    public function create(array $data): Character
    {
        return Character::create($data);
    }

    public function update(Character $character, array $data): bool
    {
        return $character->update($data);
    }

    public function delete(Character $character): bool
    {
        return $character->delete();
    }

    public function withRelations(array $relations): self
    {
        $this->relations = $relations;
        return $this;
    }
}
```

### Repository Binding

```php
// app/Providers/AppServiceProvider.php
public function register(): void
{
    $this->app->bind(
        CharacterRepositoryInterface::class,
        EloquentCharacterRepository::class
    );
}
```

## Controller Design

### Thin Controllers

Controllers should:

- Handle HTTP concerns
- Validate input (via Form Requests)
- Delegate to services
- Return responses

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCharacterRequest;
use App\Http\Resources\CharacterResource;
use App\Services\Character\CharacterService;

class CharacterController extends Controller
{
    public function __construct(
        private CharacterService $characterService
    ) {}

    public function store(CreateCharacterRequest $request): CharacterResource
    {
        $character = $this->characterService->createCharacter(
            $request->user(),
            $request->validated()
        );

        return new CharacterResource($character);
    }

    public function show(int $id): CharacterResource
    {
        $character = $this->characterService->getCharacter($id);

        $this->authorize('view', $character);

        return new CharacterResource($character);
    }
}
```

## Form Request Validation

### Form Request Structure

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCharacterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'speed' => ['required', 'integer', 'min:0', 'max:1200'],
            'stamina' => ['required', 'integer', 'min:0', 'max:1200'],
            'power' => ['required', 'integer', 'min:0', 'max:1200'],
            'guts' => ['required', 'integer', 'min:0', 'max:1200'],
            'wit' => ['required', 'integer', 'min:0', 'max:1200'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Character name is required.',
            'speed.min' => 'Speed cannot be negative.',
            'speed.max' => 'Speed cannot exceed 1200.',
            // ... other custom messages
        ];
    }

    public function attributes(): array
    {
        return [
            'speed' => 'speed stat',
            'stamina' => 'stamina stat',
            // ... other attribute names
        ];
    }
}
```

## Event-Driven Architecture

### Domain Events

```php
<?php

namespace App\Events;

use App\Models\Character;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CharacterCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Character $character
    ) {}
}
```

### Event Listeners

```php
<?php

namespace App\Listeners;

use App\Events\CharacterCreated;
use App\Services\Cache\CacheInvalidationService;

class InvalidateCharacterCache
{
    public function __construct(
        private CacheInvalidationService $cacheService
    ) {}

    public function handle(CharacterCreated $event): void
    {
        $this->cacheService->invalidateCharacterCache(
            $event->character->user_id
        );
    }
}
```

### Event Registration

```php
// app/Providers/EventServiceProvider.php
protected $listen = [
    CharacterCreated::class => [
        InvalidateCharacterCache::class,
        SendCharacterCreatedNotification::class,
    ],
];
```

## Model Design

### Eloquent Model Best Practices

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Character extends Model
{
    use HasFactory;

    // Explicit fillable fields
    protected $fillable = [
        'user_id',
        'name',
        'speed',
        'stamina',
        'power',
        'guts',
        'wit',
    ];

    // Type casting (Laravel 12 style)
    protected function casts(): array
    {
        return [
            'speed' => 'integer',
            'stamina' => 'integer',
            'power' => 'integer',
            'guts' => 'integer',
            'wit' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // Relationships with return types
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trainingSessions(): HasMany
    {
        return $this->hasMany(TrainingSession::class);
    }

    public function skillAcquisitions(): HasMany
    {
        return $this->hasMany(SkillAcquisition::class);
    }

    // Accessors
    public function getTotalStatsAttribute(): int
    {
        return $this->speed + $this->stamina + $this->power 
             + $this->guts + $this->wit;
    }

    // Scopes
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeWithHighStats($query, int $threshold = 1000)
    {
        return $query->where('speed', '>=', $threshold)
            ->orWhere('stamina', '>=', $threshold)
            ->orWhere('power', '>=', $threshold);
    }
}
```

## Enum Usage (PHP 8.1+)

### Enum Definition

```php
<?php

namespace App\Enums;

enum AptitudeGrade: string
{
    case G = 'G';
    case F = 'F';
    case E = 'E';
    case D = 'D';
    case C = 'C';
    case B = 'B';
    case A = 'A';
    case S = 'S';
    case SS = 'SS';

    public function getMultiplier(): float
    {
        return match($this) {
            self::SS => 1.20,
            self::S => 1.10,
            self::A => 1.05,
            self::B => 1.00,
            self::C => 0.95,
            self::D => 0.90,
            self::E => 0.85,
            self::F => 0.80,
            self::G => 0.75,
        };
    }

    public static function fromString(string $grade): self
    {
        return self::from(strtoupper($grade));
    }
}
```

### Enum in Models

```php
protected function casts(): array
{
    return [
        'distance_aptitude' => AptitudeGrade::class,
        'surface_aptitude' => AptitudeGrade::class,
        'running_style_aptitude' => AptitudeGrade::class,
    ];
}
```

## Caching Strategy

### Cache Layers

1. **Short-term (1-5 minutes)**: Frequently changing data
2. **Medium-term (1-24 hours)**: Semi-static data
3. **Long-term (1-7 days)**: Static reference data

### Cache Implementation

```php
<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;

class CacheManagementService
{
    private const SHORT_TTL = 300;      // 5 minutes
    private const MEDIUM_TTL = 3600;    // 1 hour
    private const LONG_TTL = 86400;     // 1 day

    public function rememberCharacter(int $id, callable $callback): mixed
    {
        return Cache::remember(
            "character:{$id}",
            self::MEDIUM_TTL,
            $callback
        );
    }

    public function rememberSkillCatalog(callable $callback): mixed
    {
        return Cache::remember(
            'skills:catalog',
            self::LONG_TTL,
            $callback
        );
    }

    public function invalidateCharacter(int $id): void
    {
        Cache::forget("character:{$id}");
    }

    public function invalidateUserCharacters(int $userId): void
    {
        Cache::forget("user:{$userId}:characters");
    }
}
```

## API Resource Transformers

### Resource Definition

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CharacterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'stats' => [
                'speed' => $this->speed,
                'stamina' => $this->stamina,
                'power' => $this->power,
                'guts' => $this->guts,
                'wit' => $this->wit,
                'total' => $this->total_stats,
            ],
            'aptitudes' => [
                'distance' => $this->distance_aptitude->value,
                'surface' => $this->surface_aptitude->value,
                'running_style' => $this->running_style_aptitude->value,
            ],
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            
            // Conditional relationships
            'training_sessions' => TrainingSessionResource::collection(
                $this->whenLoaded('trainingSessions')
            ),
            'skills' => SkillResource::collection(
                $this->whenLoaded('skillAcquisitions')
            ),
        ];
    }
}
```

## Testing Architecture

### Test Organization

```
tests/
├── Unit/
│   ├── Services/
│   │   ├── TrainingCalculationServiceTest.php
│   │   └── SkillServiceTest.php
│   └── Models/
│       └── CharacterTest.php
├── Feature/
│   ├── Http/
│   │   └── CharacterControllerTest.php
│   └── Livewire/
│       └── CharacterFormTest.php
└── Integration/
    └── ExternalAPITest.php
```

## Best Practices

### Code Organization

1. **Separation of Concerns**: Each class has one responsibility
2. **Dependency Injection**: Use constructor injection
3. **Type Safety**: Use type hints everywhere
4. **Immutability**: Prefer immutable objects where possible
5. **Explicit over Implicit**: Clear, readable code

### Performance

1. **Eager Loading**: Prevent N+1 queries
2. **Query Optimization**: Use indexes, limit results
3. **Caching**: Cache expensive operations
4. **Queue Jobs**: Offload time-consuming tasks
5. **Database Transactions**: Use for data consistency

### Security

1. **Authorization**: Use policies for access control
2. **Validation**: Validate all inputs
3. **SQL Injection**: Use Eloquent/Query Builder
4. **XSS Protection**: Use Blade templating
5. **CSRF Protection**: Enabled by default

## Related Documentation

- AGENTS.md: Architecture & Domain Model section
- SKILLS.md: Backend Engineering section
- Laravel 12 Documentation: <https://laravel.com/docs/12.x>

## Version Information

- Laravel: v12
- PHP: 8.4.11
- Last Updated: 2026-01-29

- `docs/00-core-docs/004_SDS_Software_Design_Specifications.md`: System architecture
- `docs/00-core-docs/009_DBD_Database_Documentation.md`: Database design
- `docs/00-core-docs/010_SCD_Source_Code_Documentation.md`: Code structure
- `docs/01-tech-flow/`: Technical implementation flows
- `docs/reference/DEVELOPER_GUIDE.md`: Developer guidelines
- `docs/services/CacheManagerService.md`: Caching implementation
