# Development Guidelines

## Code Quality Standards

### PHP Code Formatting

#### Strict Types Declaration
- **ALWAYS** use `declare(strict_types=1);` at the top of every PHP file after the opening tag
- This enforces type safety and prevents implicit type coercion
```php
<?php

declare(strict_types=1);

namespace App\Models;
```

#### Type Hints and Return Types
- Use explicit type hints for all method parameters
- Use explicit return types for all methods
- Use nullable types (`?Type`) when values can be null
- Use union types when appropriate (PHP 8+)
```php
public function getStat(string $stat): int
{
    return $this->current_stats[$stat] ?? 0;
}

public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}
```

#### PHPDoc Annotations
- Use PHPDoc blocks for complex types, especially arrays and generics
- Document property types with `@property` annotations
- Document relationship return types with generic annotations
```php
/**
 * @property int $id
 * @property string $name
 * @property array<string, int> $current_stats
 * @property array<string, mixed> $conditions
 * 
 * @return BelongsTo<User, $this>
 */
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}
```

#### Namespace Organization
- Follow PSR-4 autoloading standards
- Use fully qualified class names in imports
- Group imports logically (Framework, Third-party, Application)
```php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
```

### JavaScript Code Formatting

#### ES6+ Module Syntax
- Use ES6 module imports/exports
- Use `import` for dependencies, `export default` for main exports
```javascript
import axios from 'axios';
import eventBus from './core/EventBus.js';

export default eventBus;
```

#### Class-Based Architecture
- Use ES6 classes for complex components
- Include JSDoc comments for methods
- Use constructor for initialization
```javascript
/**
 * Global Event Bus for cross-component communication
 * Provides a centralized event system for the application
 */
class EventBus {
    constructor() {
        this.events = {};
    }

    /**
     * Subscribe to an event
     * @param {string} event - Event name
     * @param {Function} callback - Callback function
     * @returns {Function} Unsubscribe function
     */
    on(event, callback) {
        // Implementation
    }
}
```

#### Arrow Functions
- Use arrow functions for callbacks and short functions
- Use regular functions for methods that need `this` context
```javascript
this.events[event].forEach((callback) => {
    try {
        callback(data);
    } catch (error) {
        console.error(`Error in event handler for "${event}":`, error);
    }
});
```

#### Error Handling
- Always wrap event callbacks in try-catch blocks
- Log errors with descriptive messages
- Provide context in error messages
```javascript
try {
    callback(data);
} catch (error) {
    console.error(`Error in event handler for "${event}":`, error);
}
```

### Configuration Files

#### Array Return Pattern
- Configuration files return arrays directly
- Use short array syntax `[]` instead of `array()`
- Include descriptive comments for each section
```php
<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration Section
    |--------------------------------------------------------------------------
    |
    | Description of what this configuration controls
    |
    */
    'enabled' => env('BOOST_ENABLED', true),
];
```

#### Environment Variable Usage
- Use `env()` helper with default values
- Document expected environment variables
- Use descriptive variable names with prefixes
```php
'browser_logs_watcher' => env('BOOST_BROWSER_LOGS_WATCHER', false),
```

### Blade Template Standards

#### Semantic HTML Structure
- Use semantic HTML5 elements (`<header>`, `<main>`, `<nav>`, `<section>`)
- Include proper ARIA attributes for accessibility
- Use `role` attributes where appropriate
```blade
<main class="py-10" id="main-content">
    <div class="px-4 sm:px-6 lg:px-8">
        @yield('content')
    </div>
</main>
```

#### Accessibility Features
- Include skip-to-content links for keyboard navigation
- Use `sr-only` class for screen reader text
- Provide `aria-hidden="true"` for decorative elements
```blade
<a href="#main-content"
    class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 z-50">
    Skip to content
</a>
```

#### Tailwind CSS Class Organization
- Order classes logically: layout → spacing → typography → colors → effects
- Use responsive prefixes (`sm:`, `md:`, `lg:`, `xl:`)
- Use dark mode variants (`dark:`)
- Group related utilities together
```blade
<div class="fixed inset-0 z-0 bg-cover bg-center bg-no-repeat 
            transition-opacity duration-500"
     role="presentation" aria-hidden="true">
</div>
```

#### Alpine.js Integration
- Use `x-data` for component state
- Use `x-show` for conditional visibility with transitions
- Use `x-transition` directives for smooth animations
- Use `@click` for event handlers
```blade
<body x-data="{ sidebarOpen: false }">
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         @click="sidebarOpen = false">
    </div>
</body>
```

## Architectural Patterns

### Model Design Patterns

#### Eloquent Model Structure
1. **Property Documentation**: Use `@property` annotations for all database columns
2. **Factory Usage**: Include `@use HasFactory<FactoryClass>` annotation
3. **Table Name**: Explicitly define table name with `protected $table`
4. **Fillable Fields**: Define all mass-assignable fields
5. **Type Casting**: Use `casts()` method for array/JSON columns
6. **Boot Method**: Use for model events (creating, updating, etc.)
7. **Relationships**: Type-hint relationship return types with generics
8. **Scopes**: Define query scopes for common filters
9. **Helper Methods**: Add domain-specific helper methods

```php
class Character extends Model
{
    use HasFactory;

    protected $table = 'ucp_characters';

    protected $fillable = ['name', 'current_stats', /* ... */];

    protected function casts(): array
    {
        return [
            'current_stats' => 'array',
            'conditions' => 'array',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        
        static::creating(function ($character) {
            if (empty($character->uuid)) {
                $character->uuid = (string) Str::uuid();
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getStat(string $stat): int
    {
        return $this->current_stats[$stat] ?? 0;
    }
}
```

### Controller Patterns

#### Constructor Dependency Injection
- Inject services via constructor using promoted properties
- Use type hints for all injected dependencies
```php
public function __construct(
    protected CharacterStateService $characterStateService
) {}
```

#### Request Validation
- Use Form Request classes for validation (StoreCharacterRequest, UpdateCharacterRequest)
- Validate input types before processing
- Use `$request->filled()` to check for non-empty values
```php
public function store(StoreCharacterRequest $request): RedirectResponse
{
    if ($request->filled('search')) {
        $searchTerm = $request->input('search');
        if (is_string($searchTerm)) {
            $query->where('name', 'like', '%'.$searchTerm.'%');
        }
    }
}
```

#### Database Transactions
- Wrap multi-step operations in transactions
- Use try-catch blocks for error handling
- Rollback on exceptions
- Log errors with context
```php
try {
    DB::beginTransaction();
    
    $character = Character::create([/* ... */]);
    $this->createAptitudes($character, $aptitudes);
    
    DB::commit();
    
    return redirect()->route('characters.show', $character)
        ->with('success', 'Character created successfully!');
} catch (\Exception $e) {
    DB::rollBack();
    
    Log::error('Character creation failed: '.$e->getMessage(), [
        'exception' => $e,
        'trace' => $e->getTraceAsString(),
    ]);
    
    return redirect()->back()->withInput()
        ->with('error', 'Failed to create character. Please try again.');
}
```

#### Authorization Checks
- Check user ownership before sensitive operations
- Use `abort(403)` for unauthorized access
```php
if ($character->user_id !== Auth::id()) {
    abort(403, 'Unauthorized action.');
}
```

#### Eager Loading
- Use `with()` to eager load relationships
- Prevent N+1 query problems
```php
$character->load([
    'aptitudes',
    'factors',
    'supportCards.supportCard',
]);
```

### Service Layer Pattern

#### Service Injection
- Services handle business logic
- Controllers delegate to services
- Services are injected via constructor
```php
$result = $this->characterStateService->rest($character);
$result = $this->characterStateService->progressTurn($character);
```

### Frontend Patterns

#### Global Object Pattern
- Expose utilities on `window` object for global access
- Use for cross-component communication
```javascript
window.axios = axios;
window.eventBus = eventBus;
```

#### Axios Configuration
- Set default headers for CSRF protection
- Configure XMLHttpRequest header
```javascript
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
```

#### Event Bus Pattern
- Centralized event system for component communication
- Provides subscribe/unsubscribe functionality
- Returns unsubscribe function from `on()` method
- Includes error handling in event emission
```javascript
const unsubscribe = eventBus.on('event-name', (data) => {
    // Handle event
});

// Later: unsubscribe()
```

### Build Configuration Patterns

#### Vite Configuration
- Use `defineConfig` for type safety
- Configure Laravel plugin with input files
- Enable refresh for hot module replacement
- Set build target to `esnext` for modern JavaScript
- Ignore framework cache directories in watch
```javascript
export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        tailwindcss(),
    ],
    build: {
        target: "esnext",
    },
    server: {
        watch: {
            ignored: ["**/storage/framework/views/**"],
        },
    },
});
```

## Common Code Idioms

### Match Expressions (PHP 8+)
- Use `match` for grade calculations and mappings
- More concise than switch statements
- Returns values directly
```php
public function getStatGrade(int $statValue): string
{
    return match (true) {
        $statValue >= 1200 => 'SS',
        $statValue >= 1100 => 'S',
        $statValue >= 1000 => 'A+',
        default => 'G+',
    };
}
```

### Null Coalescing Operator
- Use `??` for default values
- Cleaner than ternary operators
```php
return $this->current_stats[$stat] ?? 0;
```

### Array Destructuring
- Use for cleaner variable assignment
```javascript
const { speed, stamina, power } = character.stats;
```

### Template Literals
- Use for string interpolation in JavaScript
```javascript
console.error(`Error in event handler for "${event}":`, error);
```

### Spread Operator
- Use for function arguments
```javascript
callback(...args);
```

## Testing Patterns

### PHPStan Stubs
- Create stub files for better static analysis
- Define interfaces for mocking libraries
- Place in `phpstan-stubs/` directory
```php
namespace Mockery;

interface ExpectationInterface
{
    public function andReturn(...$args): self;
    public function andReturnSelf(): self;
}
```

## Security Practices

### CSRF Protection
- Include CSRF token in meta tags
- Configure Axios to send CSRF token
- Use `@csrf` directive in forms

### Input Validation
- Always validate user input
- Use Form Request classes
- Type-check input values before processing
- Sanitize output in views (Blade auto-escapes)

### Authorization
- Check user ownership before operations
- Use policies for complex authorization logic
- Use `abort(403)` for unauthorized access

## Performance Optimization

### Lazy Loading
- Implement lazy loading for images
- Use data attributes for deferred loading
- Optimize asset delivery

### Query Optimization
- Use eager loading to prevent N+1 queries
- Use pagination for large datasets
- Index frequently queried columns

### Caching
- Cache configuration in production
- Cache routes and views
- Use Redis/Memcached for session storage

## Error Handling

### Logging Standards
- Log errors with context
- Include exception traces
- Use appropriate log levels
```php
Log::error('Character creation failed: '.$e->getMessage(), [
    'exception' => $e,
    'trace' => $e->getTraceAsString(),
]);
```

### User-Friendly Messages
- Show generic error messages to users
- Log detailed errors for debugging
- Use flash messages for feedback
```php
return redirect()->back()
    ->with('error', 'Failed to create character. Please try again.');
```

## Documentation Standards

### Code Comments
- Use JSDoc for JavaScript functions
- Use PHPDoc for PHP methods and properties
- Include parameter types and return types
- Describe complex logic with inline comments
- Use block comments for section headers

### Configuration Comments
- Use Laravel-style comment blocks
- Explain purpose and usage
- Document expected values
```php
/*
|--------------------------------------------------------------------------
| Configuration Section
|--------------------------------------------------------------------------
|
| Description of what this configuration controls
|
*/
```
