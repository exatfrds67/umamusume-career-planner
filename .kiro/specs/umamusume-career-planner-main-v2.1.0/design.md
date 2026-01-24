# Design Document: Umamusume Career Planner v2.1.0

**Document Version**: 2.1.0  
**Date**: January 23, 2026  
**Project**: UmamusumeCareerPlanner  
**Status**: Active - Design Phase  
**Related Documents**: requirements.md, SDP v2.1, SDS v2.1, SPEC-001 to SPEC-007

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [System Architecture](#2-system-architecture)
3. [Performance Optimization Design](#3-performance-optimization-design)
4. [Accessibility Design](#4-accessibility-design)
5. [Progressive Web App Design](#5-progressive-web-app-design)
6. [Advanced Features Design](#6-advanced-features-design)
7. [Testing Strategy Design](#7-testing-strategy-design)
8. [Security Design](#8-security-design)
9. [Data Models](#9-data-models)
10. [API Design](#10-api-design)
11. [Component Architecture](#11-component-architecture)
12. [Deployment Architecture](#12-deployment-architecture)

---

## 1. Introduction

### 1.1 Purpose

This Design Document specifies the technical design for implementing the Umamusume Career Planner v2.1.0 requirements. It provides detailed architectural decisions, component designs, data models, and implementation strategies for performance optimization, accessibility compliance, PWA enhancements, and advanced analytics features.

### 1.2 Design Principles

**Core Design Principles for v2.1.0:**

1. **Performance First**: Every design decision considers performance impact
2. **Accessibility by Default**: WCAG 2.2 AA compliance built into all components
3. **Progressive Enhancement**: Core functionality works everywhere, enhanced features where supported
4. **Offline Resilience**: Graceful degradation when connectivity is lost
5. **Maintainability**: Clear separation of concerns, testable components
6. **Scalability**: Designed to handle growth in users and data

### 1.3 Technology Stack

| Layer | Technology | Version | Purpose |
|-------|-----------|---------|---------|
| **Backend Framework** | Laravel | 12+ | Application framework |
| **Frontend Reactivity** | Livewire | 3 | Server-driven UI updates |
| **Client Interactivity** | Alpine.js | 3.x | Client-side interactions |
| **Styling** | Tailwind CSS | v4 | Utility-first styling |
| **Build Tool** | Vite | 7+ | Asset bundling and optimization |
| **Database** | MySQL | 8.0+ | Primary data store |
| **Cache** | Redis | 6.0+ | Caching and sessions |
| **Testing** | Pest | 4.0+ | PHP testing framework |
| **Browser Testing** | Playwright | Latest | E2E testing |
| **Service Worker** | Workbox | 7+ | PWA functionality |

### 1.4 Requirements Traceability Matrix

This matrix maps requirements from requirements.md to design sections, components, and test coverage:

| Requirement ID | Requirement Name | Design Section | Key Components | Test Coverage |
|----------------|------------------|----------------|----------------|---------------|
| REQ-1 | Advanced Cache Optimization | 3.1 | CacheOptimizationService, TieredCacheStrategy | TC-161-01 to TC-161-05 |
| REQ-2 | Database Query Optimization | 3.2 | EloquentCharacterRepository, QueryOptimizationService | TC-162-01 to TC-162-05 |
| REQ-3 | API Response Caching | 3.3 | ExternalAPIService, CircuitBreaker | TC-163-01 to TC-163-05 |
| REQ-4 | Frontend Performance | 3.4 | Vite config, Lazy loading components | TC-164-01 to TC-164-05 |
| REQ-5 | APM Integration | 3.5 | APMService, Custom Watchers | TC-165-01 to TC-165-05 |
| REQ-6 | WCAG 2.2 AA Compliance | 4.1 | Accessible component library | TC-166-01 to TC-166-05 |
| REQ-7 | Keyboard Navigation | 4.2 | Keyboard shortcuts system | TC-167-01 to TC-167-05 |
| REQ-8 | Screen Reader Support | 4.3 | Live regions, ARIA components | TC-168-01 to TC-168-05 |
| REQ-9 | Enhanced Offline | 5.1 | Service Worker, IndexedDB | TC-169-01 to TC-169-05 |
| REQ-10 | Background Sync | 5.2 | PushNotificationService | TC-170-01 to TC-170-05 |
| REQ-11 | PWA Install | 5.3 | Install prompt handler | TC-171-01 to TC-171-05 |
| REQ-12 | Batch Simulation | 6.1 | BatchSimulationService | TC-172-01 to TC-172-05 |
| REQ-13 | AI Retraining | 6.2 | ModelRetrainingService | TC-173-01 to TC-173-05 |
| REQ-14 | Advanced Analytics | 6.3 | PatternRecognitionService | TC-174-01 to TC-174-05 |
| REQ-15 | Enhanced Export | 6.4 | PDFExportService, ExcelExportService | TC-175-01 to TC-175-05 |
| REQ-16 | Property-Based Testing | 7.1 | Property test suite | TC-176-01 to TC-176-05 |
| REQ-17 | Browser Testing | 7.2 | Playwright test suite | TC-177-01 to TC-177-05 |
| REQ-18 | Performance Benchmarking | 7.3 | Benchmark suite | TC-178-01 to TC-178-05 |
| REQ-19 | Security Audit | 8.1 | Security scanning pipeline | TC-179-01 to TC-179-05 |
| REQ-20 | Privacy Controls | 8.2 | DataExportService, DataDeletionService | TC-180-01 to TC-180-05 |

---

## 2. System Architecture

### 2.1 High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                         Browser Layer                                │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐              │
│  │  Alpine.js   │  │  Livewire    │  │   Service    │              │
│  │  Components  │  │   Client     │  │   Worker     │              │
│  └──────────────┘  └──────────────┘  └──────────────┘              │
│  ┌──────────────────────────────────────────────────┐              │
│  │           IndexedDB (Offline Storage)             │              │
│  └──────────────────────────────────────────────────┘              │
└────────────────────────────┬────────────────────────────────────────┘
                             │ HTTPS + WebSocket
┌────────────────────────────┴────────────────────────────────────────┐
│                      Application Layer                               │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐              │
│  │  Livewire    │  │  Controllers │  │   Service    │              │
│  │  Components  │  │              │  │    Layer     │              │
│  └──────────────┘  └──────────────┘  └──────────────┘              │
│  ┌──────────────────────────────────────────────────┐              │
│  │         Laravel 12 Core Framework                 │              │
│  └──────────────────────────────────────────────────┘              │
└────────────────────────────┬────────────────────────────────────────┘
                             │
┌────────────────────────────┴────────────────────────────────────────┐
│                         Data Layer                                   │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐              │
│  │    MySQL     │  │    Redis     │  │     File     │              │
│  │   Database   │  │    Cache     │  │   Storage    │              │
│  └──────────────┘  └──────────────┘  └──────────────┘              │
└─────────────────────────────────────────────────────────────────────┘
```

### 2.2 Layered Architecture

**Presentation Layer**

- Blade templates with Livewire components
- Alpine.js for client-side interactivity
- Tailwind CSS v4 for styling
- Service Worker for PWA functionality

**Application Layer**

- Livewire components for UI logic
- Controllers for API endpoints
- Form Requests for validation
- Middleware for cross-cutting concerns

**Domain Layer**

- Service classes for business logic
- Eloquent models for data entities
- Repositories for data access abstraction
- Events and Listeners for decoupled operations

**Infrastructure Layer**

- Database (MySQL) for persistent storage
- Redis for caching and sessions
- Queue system for background jobs
- External API integrations

### 2.3 Design Patterns

**Service Layer Pattern**

- Business logic encapsulated in service classes
- Controllers and Livewire components delegate to services
- Services are testable and reusable

**Repository Pattern**

- Data access abstraction through repositories
- Eloquent models wrapped with repository interfaces
- Enables easier testing and data source switching

**Observer Pattern**

- Laravel events for decoupled communication
- Listeners handle side effects (cache invalidation, notifications)
- Async processing via queued listeners

**Strategy Pattern**

- Cache strategies (memory, Redis, file)
- Export strategies (PDF, Excel, JSON)
- Offline sync strategies

---

## 3. Performance Optimization Design

### 3.1 Advanced Cache Optimization System (REQ-1)

**Design Decision**: Implement tiered caching with Redis as L2 cache and memory as L1 cache.

**Rationale**:

- Memory cache provides fastest access for frequently used data
- Redis cache enables sharing across requests and servers
- Tiered approach balances speed and memory usage

**Architecture**:

```php
// Cache hierarchy
interface CacheStrategy {
    public function get(string $key): mixed;
    public function put(string $key, mixed $value, int $ttl): void;
    public function forget(string $key): void;
}

class TieredCacheStrategy implements CacheStrategy {
    public function __construct(
        private MemoryCache $l1Cache,
        private RedisCache $l2Cache
    ) {}
    
    public function get(string $key): mixed {
        // Try L1 first
        if ($value = $this->l1Cache->get($key)) {
            return $value;
        }
        
        // Fallback to L2
        if ($value = $this->l2Cache->get($key)) {
            $this->l1Cache->put($key, $value, 300); // 5 min L1 TTL
            return $value;
        }
        
        return null;
    }
}
```

**Cache Key Naming Convention**:

```
umamusume-career-planner:cache:{entity}:{id}:{version}
umamusume-career-planner:session:{session_id}
umamusume-career-planner:queue:{job_id}
```

**TTL Strategy**:

| Data Type | TTL | Rationale |
|-----------|-----|-----------|
| Skills catalog | 24 hours | Rarely changes |
| Character data | 12 hours | Moderate change frequency |
| Meta rankings | 6 hours | Updated regularly |
| Training predictions | 5 minutes | User-specific, short-lived |
| User sessions | 2 hours | Security consideration |

**Cache Invalidation Strategy**:

```php
// Tag-based invalidation
Cache::tags(['character_' . $id])->flush();
Cache::tags(['training_' . $id])->flush();

// Event-driven invalidation
class CharacterUpdated {
    public function __construct(public Character $character) {}
}

class InvalidateCharacterCache {
    public function handle(CharacterUpdated $event): void {
        Cache::tags(['character_' . $event->character->id])->flush();
    }
}
```

**Implementation Components**:

- `app/Services/Cache/CacheOptimizationService.php` - Main cache service
- `app/Services/Cache/TieredCacheStrategy.php` - Tiered cache implementation
- `config/cache.php` - Cache configuration with Redis setup
- `app/Listeners/InvalidateCacheOnUpdate.php` - Event-driven invalidation

**Performance Metrics**:

- Cache hit rate monitoring via `CacheHitRateMiddleware`
- Redis memory usage tracking
- Cache operation latency measurement
- Dashboard at `/admin/cache-metrics`

---

### 3.2 Database Query Optimization (REQ-2)

**Design Decision**: Implement strategic indexing, eager loading, and cursor pagination.

**Rationale**:

- Indexes dramatically improve query performance for filtered/sorted queries
- Eager loading prevents N+1 query problems
- Cursor pagination scales better than offset pagination

**Index Strategy**:

```sql
-- Foreign key indexes
CREATE INDEX idx_characters_user_id ON characters(user_id);
CREATE INDEX idx_careers_character_id ON careers(character_id);
CREATE INDEX idx_training_sessions_career_id ON training_sessions(career_id);

-- Status field indexes
CREATE INDEX idx_careers_status ON careers(run_status);
CREATE INDEX idx_skills_status ON skills(skill_status);

-- Timestamp indexes
CREATE INDEX idx_careers_created_at ON careers(created_at);
CREATE INDEX idx_training_sessions_turn_number ON training_sessions(turn_number);

-- Composite indexes for common queries
CREATE INDEX idx_careers_user_status ON careers(user_id, run_status);
CREATE INDEX idx_careers_character_created ON careers(character_id, created_at);
CREATE INDEX idx_training_career_turn ON training_sessions(career_id, turn_number);
```

**Eager Loading Pattern**:

```php
// Character repository with eager loading
class EloquentCharacterRepository implements CharacterRepositoryInterface {
    public function findWithRelations(int $id): ?Character {
        return Character::with([
            'aptitudes',
            'factors',
            'skills' => fn($q) => $q->where('status', 'active'),
            'supportCards.definition'
        ])->find($id);
    }
    
    public function listForUser(User $user): Collection {
        return Character::with(['aptitudes', 'factors'])
            ->where('user_id', $user->id)
            ->cursorPaginate(20);
    }
}
```

**Query Optimization Patterns**:

```php
// Use database aggregation instead of PHP
$totalSP = Skill::where('career_id', $careerId)
    ->sum('sp_cost'); // Database SUM, not PHP array_sum

// Use select to limit columns
$characters = Character::select(['id', 'name', 'avatar_url'])
    ->where('user_id', $userId)
    ->get();

// Use chunk for large datasets
Character::where('user_id', $userId)
    ->chunk(100, function ($characters) {
        // Process in batches
    });
```

**Query Monitoring**:

```php
// Telescope watcher for slow queries
class SlowQueryWatcher extends Watcher {
    public function recordQuery(QueryExecuted $event): void {
        if ($event->time > 100) { // 100ms threshold
            $this->recordEntry([
                'sql' => $event->sql,
                'bindings' => $event->bindings,
                'time' => $event->time,
                'explain' => DB::select('EXPLAIN ' . $event->sql)
            ]);
        }
    }
}
```

**Implementation Components**:

- `database/migrations/*_add_performance_indexes.php` - Index migrations
- `app/Repositories/EloquentCharacterRepository.php` - Optimized queries
- `app/Telescope/Watchers/SlowQueryWatcher.php` - Query monitoring
- `tests/Feature/Query/QueryOptimizationTest.php` - Query performance tests

---

### 3.3 API Response Caching (REQ-3)

**Design Decision**: Implement intelligent caching with circuit breaker pattern for external APIs.

**Rationale**:

- External APIs are slow and unreliable
- Caching reduces latency and API costs
- Circuit breaker prevents cascading failures

**Circuit Breaker Pattern**:

```php
class ExternalAPIService {
    private CircuitBreaker $circuitBreaker;
    
    public function fetchSkillData(string $skillId): array {
        return $this->circuitBreaker->call(
            fn() => $this->makeAPIRequest($skillId),
            fn() => $this->getFallbackData($skillId)
        );
    }
    
    private function getFallbackData(string $skillId): array {
        // Serve stale cache
        return Cache::get("skill_data_{$skillId}_stale") ?? [];
    }
}

class CircuitBreaker {
    private const FAILURE_THRESHOLD = 5;
    private const TIMEOUT = 30; // seconds
    
    public function call(callable $action, callable $fallback): mixed {
        if ($this->isOpen()) {
            return $fallback();
        }
        
        try {
            $result = $action();
            $this->recordSuccess();
            return $result;
        } catch (Exception $e) {
            $this->recordFailure();
            return $fallback();
        }
    }
}
```

**Request Coalescing**:

```php
class ExternalAPIService {
    public function fetchWithCoalescing(string $key, callable $fetcher): mixed {
        $lock = Cache::lock("api_fetch_{$key}", 30);
        
        if ($lock->get()) {
            try {
                $data = $fetcher();
                Cache::put($key, $data, 3600);
                return $data;
            } finally {
                $lock->release();
            }
        }
        
        // Wait for other request to complete
        return $this->waitForCache($key);
    }
}
```

**Background Refresh**:

```php
class RefreshExternalDataJob implements ShouldQueue {
    public function handle(ExternalAPIService $api): void {
        $staleKeys = Cache::get('stale_api_keys', []);
        
        foreach ($staleKeys as $key) {
            $data = $api->fetchFresh($key);
            Cache::put($key, $data, 3600);
        }
    }
}
```

**Implementation Components**:

- `app/Services/ExternalAPI/ExternalAPIService.php` - API service with caching
- `app/Services/ExternalAPI/CircuitBreaker.php` - Circuit breaker implementation
- `app/Jobs/RefreshExternalDataJob.php` - Background refresh job
- `config/external-apis.php` - API configuration and timeouts

---

### 3.4 Frontend Performance Optimization (REQ-4)

**Design Decision**: Implement code splitting, lazy loading, and optimistic UI updates.

**Rationale**:

- Smaller initial bundle improves load time
- Lazy loading defers non-critical resources
- Optimistic updates improve perceived performance

**Code Splitting Strategy**:

```javascript
// vite.config.js
export default defineConfig({
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    'dashboard': ['./resources/js/pages/dashboard.js'],
                    'character': ['./resources/js/pages/character.js'],
                    'training': ['./resources/js/pages/training.js'],
                    'vendor': ['alpinejs', 'axios']
                }
            }
        }
    }
});
```

**Lazy Loading Images**:

```blade
<img 
    src="{{ $character->avatar_url }}" 
    loading="lazy"
    srcset="{{ $character->avatar_url }}?w=400 400w,
            {{ $character->avatar_url }}?w=800 800w"
    sizes="(max-width: 768px) 400px, 800px"
    alt="{{ $character->name }}"
/>
```

**Optimistic UI Updates**:

```php
// Livewire component with optimistic updates
class SkillSelector extends Component {
    #[Locked]
    public Career $career;
    
    public array $selectedSkills = [];
    
    public function addSkill(int $skillId): void {
        // Optimistic update - UI updates immediately
        $this->selectedSkills[] = $skillId;
        
        // Actual save happens in background
        $this->dispatch('skill-added', skillId: $skillId);
        
        // Server validation
        $this->career->skills()->attach($skillId);
    }
}
```

```blade
<div x-data="{ adding: false }">
    <button 
        wire:click="addSkill({{ $skill->id }})"
        x-on:click="adding = true"
        x-on:skill-added.window="adding = false"
        :disabled="adding"
    >
        <span x-show="!adding">Add Skill</span>
        <span x-show="adding">Adding...</span>
    </button>
</div>
```

**Performance Budget**:

```yaml
# .github/workflows/performance-budget.yml
budgets:
  - path: /dashboard
    maxSize: 200kb
    maxRequests: 20
  - path: /character/*
    maxSize: 250kb
    maxRequests: 25
```

**Implementation Components**:

- `vite.config.js` - Build configuration with code splitting
- `resources/js/components/*` - Lazy-loaded components
- `resources/views/components/optimized-image.blade.php` - Image component
- `tests/Browser/Performance/PerformanceTest.php` - Performance tests

---

### 3.5 Application Performance Monitoring (REQ-5)

**Design Decision**: Extend Laravel Telescope with custom watchers and metrics.

**Rationale**:

- Telescope provides excellent foundation for monitoring
- Custom watchers enable domain-specific insights
- Metrics dashboard provides actionable intelligence

**Custom Watchers**:

```php
class CachePerformanceWatcher extends Watcher {
    public function register($app): void {
        $app['events']->listen(CacheHit::class, [$this, 'recordCacheHit']);
        $app['events']->listen(CacheMissed::class, [$this, 'recordCacheMiss']);
    }
    
    public function recordCacheHit(CacheHit $event): void {
        $this->recordEntry([
            'type' => 'hit',
            'key' => $event->key,
            'tags' => $event->tags
        ]);
    }
}

class ExternalAPIWatcher extends Watcher {
    public function recordAPICall(string $service, float $duration, bool $cached): void {
        $this->recordEntry([
            'service' => $service,
            'duration' => $duration,
            'cached' => $cached,
            'timestamp' => now()
        ]);
    }
}
```

**Metrics Collection**:

```php
class APMService {
    public function recordMetric(string $name, float $value, array $tags = []): void {
        DB::table('apm_metrics')->insert([
            'name' => $name,
            'value' => $value,
            'tags' => json_encode($tags),
            'recorded_at' => now()
        ]);
    }
    
    public function getMetrics(string $name, Carbon $from, Carbon $to): Collection {
        return DB::table('apm_metrics')
            ->where('name', $name)
            ->whereBetween('recorded_at', [$from, $to])
            ->get();
    }
}
```

**Alert System**:

```php
class PerformanceAlert {
    public function checkThresholds(): void {
        $slowRequests = $this->getSlowRequests();
        if ($slowRequests->count() > 10) {
            Notification::route('slack', config('services.slack.webhook'))
                ->notify(new SlowRequestsAlert($slowRequests));
        }
        
        $cacheHitRate = $this->getCacheHitRate();
        if ($cacheHitRate < 0.7) {
            Notification::route('mail', config('admin.email'))
                ->notify(new LowCacheHitRateAlert($cacheHitRate));
        }
    }
}
```

**Implementation Components**:

- `app/Services/Monitoring/APMService.php` - Metrics collection service
- `app/Telescope/Watchers/CachePerformanceWatcher.php` - Cache watcher
- `app/Telescope/Watchers/ExternalAPIWatcher.php` - API watcher
- `resources/views/admin/apm.blade.php` - APM dashboard
- `app/Console/Commands/CheckPerformanceThresholds.php` - Alert command

---

## 4. Accessibility Design

### 4.1 WCAG 2.2 AA Compliance (REQ-6)

**Design Decision**: Build accessibility into component library from the ground up.

**Rationale**:

- Retrofitting accessibility is expensive and error-prone
- Component-based approach ensures consistency
- Automated testing catches regressions early

**Accessible Component Architecture**:

```blade
{{-- resources/views/components/accessible/button.blade.php --}}
@props([
    'type' => 'button',
    'variant' => 'primary',
    'disabled' => false,
    'ariaLabel' => null,
    'ariaDescribedby' => null
])

<button
    type="{{ $type }}"
    {{ $attributes->merge([
        'class' => "btn btn-{$variant} focus:outline-none focus:ring-2 focus:ring-offset-2",
        'aria-label' => $ariaLabel,
        'aria-describedby' => $ariaDescribedby,
        'disabled' => $disabled
    ]) }}
>
    {{ $slot }}
</button>
```

**Focus Management**:

```css
/* resources/css/accessibility.css */
:focus-visible {
    outline: 2px solid var(--color-primary);
    outline-offset: 2px;
}

.focus-trap {
    /* Trap focus within modals */
}

.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border-width: 0;
}
```

**Skip Links**:

```blade
{{-- resources/views/layouts/app.blade.php --}}
<a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-0 focus:left-0 focus:z-50 focus:p-4 focus:bg-white">
    Skip to main content
</a>

<main id="main-content" tabindex="-1">
    {{ $slot }}
</main>
```

**Color Contrast System**:

```javascript
// Tailwind config with WCAG AA compliant colors
const colors = {
    primary: {
        DEFAULT: '#0066CC', // 4.5:1 on white
        dark: '#004C99',    // 7:1 on white
    },
    text: {
        DEFAULT: '#1A1A1A', // 16:1 on white
        muted: '#666666',   // 4.5:1 on white
    }
};
```

**Implementation Components**:

- `resources/views/components/accessible/*` - Accessible component library
- `resources/css/accessibility.css` - Accessibility styles
- `tests/Browser/Accessibility/WCAG22Test.php` - Automated accessibility tests
- `docs/accessibility/component-guide.md` - Component usage guide

---

### 4.2 Enhanced Keyboard Navigation (REQ-7)

**Design Decision**: Implement comprehensive keyboard shortcuts with Alpine.js directives.

**Rationale**:

- Alpine.js provides reactive keyboard handling
- Directive-based approach is reusable and testable
- Consistent shortcuts improve user experience

**Keyboard Shortcut System**:

```javascript
// resources/js/keyboard-shortcuts.js
Alpine.directive('shortcut', (el, { expression }, { evaluate }) => {
    const shortcut = evaluate(expression);
    
    const handler = (e) => {
        const keys = shortcut.keys.split('+');
        const matches = keys.every(key => {
            if (key === 'ctrl') return e.ctrlKey || e.metaKey;
            if (key === 'shift') return e.shiftKey;
            if (key === 'alt') return e.altKey;
            return e.key.toLowerCase() === key.toLowerCase();
        });
        
        if (matches) {
            e.preventDefault();
            shortcut.action();
        }
    };
    
    document.addEventListener('keydown', handler);
    
    // Cleanup
    el._x_shortcut_cleanup = () => {
        document.removeEventListener('keydown', handler);
    };
});
```

**Usage Example**:

```blade
<div x-data="{ 
    save() { $wire.save() },
    search() { $refs.searchInput.focus() }
}">
    <button x-shortcut="{ keys: 'ctrl+s', action: save }">
        Save (Ctrl+S)
    </button>
    
    <input 
        x-ref="searchInput"
        x-shortcut="{ keys: 'ctrl+k', action: search }"
        placeholder="Search (Ctrl+K)"
    />
</div>
```

**List Navigation**:

```javascript
// resources/js/list-navigation.js
Alpine.data('listNavigation', (items) => ({
    items,
    selectedIndex: 0,
    
    init() {
        this.$el.addEventListener('keydown', (e) => {
            switch(e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    this.selectNext();
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    this.selectPrevious();
                    break;
                case 'Enter':
                    e.preventDefault();
                    this.activateSelected();
                    break;
                case 'Home':
                    e.preventDefault();
                    this.selectFirst();
                    break;
                case 'End':
                    e.preventDefault();
                    this.selectLast();
                    break;
            }
        });
    },
    
    selectNext() {
        this.selectedIndex = Math.min(this.selectedIndex + 1, this.items.length - 1);
        this.scrollToSelected();
    },
    
    selectPrevious() {
        this.selectedIndex = Math.max(this.selectedIndex - 1, 0);
        this.scrollToSelected();
    }
}));
```

**Keyboard Shortcuts Page**:

```blade
{{-- resources/views/accessibility/keyboard-shortcuts.blade.php --}}
<div x-data="{ search: '', category: 'all' }">
    <input 
        x-model="search" 
        placeholder="Search shortcuts..."
        class="mb-4"
    />
    
    <div class="shortcuts-list">
        @foreach($shortcuts as $category => $items)
            <section>
                <h2>{{ $category }}</h2>
                <dl>
                    @foreach($items as $shortcut)
                        <div x-show="search === '' || '{{ $shortcut->name }}'.toLowerCase().includes(search.toLowerCase())">
                            <dt>
                                <kbd>{{ $shortcut->keys }}</kbd>
                            </dt>
                            <dd>{{ $shortcut->description }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        @endforeach
    </div>
</div>
```

**Implementation Components**:

- `resources/js/keyboard-shortcuts.js` - Shortcut system
- `resources/js/list-navigation.js` - List navigation component
- `resources/views/accessibility/keyboard-shortcuts.blade.php` - Shortcuts page
- `app/Http/Controllers/KeyboardShortcutsController.php` - Shortcuts data
- `tests/Browser/Accessibility/KeyboardNavigationTest.php` - Keyboard tests

---

### 4.3 Screen Reader Support (REQ-8)

**Design Decision**: Use ARIA live regions and semantic HTML for dynamic content.

**Rationale**:

- Screen readers need announcements for dynamic updates
- Semantic HTML provides better context
- ARIA enhances but doesn't replace good HTML

**Live Region Component**:

```blade
{{-- resources/views/components/accessible/live-region.blade.php --}}
@props([
    'politeness' => 'polite', // polite | assertive
    'atomic' => false,
    'relevant' => 'additions text'
])

<div
    role="status"
    aria-live="{{ $politeness }}"
    aria-atomic="{{ $atomic ? 'true' : 'false' }}"
    aria-relevant="{{ $relevant }}"
    {{ $attributes->merge(['class' => 'sr-only']) }}
>
    {{ $slot }}
</div>
```

**Usage in Livewire**:

```php
class TrainingEditor extends Component {
    public string $statusMessage = '';
    
    public function save(): void {
        $this->career->save();
        
        // Announce to screen readers
        $this->statusMessage = 'Training session saved successfully';
        
        // Clear after 3 seconds
        $this->dispatch('clear-status-message')->delay(3000);
    }
    
    public function render() {
        return view('livewire.training-editor');
    }
}
```

```blade
<div>
    <x-accessible.live-region>
        {{ $statusMessage }}
    </x-accessible.live-region>
    
    <form wire:submit="save">
        <!-- Form fields -->
        <button type="submit">Save</button>
    </form>
</div>
```

**Semantic Landmarks**:

```blade
{{-- resources/views/layouts/app.blade.php --}}
<body>
    <header role="banner">
        <nav aria-label="Main navigation">
            <!-- Primary navigation -->
        </nav>
        <nav aria-label="User menu">
            <!-- User menu -->
        </nav>
    </header>
    
    <main role="main" id="main-content">
        {{ $slot }}
    </main>
    
    <aside role="complementary" aria-label="Related information">
        <!-- Sidebar content -->
    </aside>
    
    <footer role="contentinfo">
        <!-- Footer content -->
    </footer>
</body>
```

**Form Accessibility**:

```blade
<div class="form-group">
    <label for="character-name" class="required">
        Character Name
        <span class="sr-only">(required)</span>
    </label>
    
    <input 
        id="character-name"
        type="text"
        wire:model="name"
        aria-required="true"
        aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
        aria-describedby="name-error name-help"
    />
    
    <small id="name-help" class="form-text">
        Enter a unique name for your character
    </small>
    
    @error('name')
        <div id="name-error" class="error-message" role="alert">
            <span class="sr-only">Error:</span>
            {{ $message }}
        </div>
    @enderror
</div>
```

**Implementation Components**:

- `resources/views/components/accessible/live-region.blade.php` - Live region component
- `resources/views/components/accessible/form-field.blade.php` - Accessible form field
- `resources/views/layouts/app.blade.php` - Semantic layout
- `tests/Browser/Accessibility/ScreenReaderTest.php` - Screen reader tests

---

## 5. Progressive Web App Design

### 5.1 Enhanced Offline Functionality (REQ-9)

**Design Decision**: Use Workbox for service worker with network-first strategy and IndexedDB for offline queue.

**Rationale**:

- Workbox provides battle-tested caching strategies
- Network-first ensures fresh data when online
- IndexedDB enables complex offline operations

**Service Worker Architecture**:

```javascript
// public/sw.js
import { NetworkFirst, CacheFirst, StaleWhileRevalidate } from 'workbox-strategies';
import { registerRoute } from 'workbox-routing';
import { BackgroundSyncPlugin } from 'workbox-background-sync';

// Network-first for HTML pages
registerRoute(
    ({ request }) => request.mode === 'navigate',
    new NetworkFirst({
        cacheName: 'pages-cache',
        plugins: [
            {
                cacheWillUpdate: async ({ response }) => {
                    return response.status === 200 ? response : null;
                }
            }
        ]
    })
);

// Cache-first for static assets
registerRoute(
    ({ request }) => request.destination === 'style' || 
                     request.destination === 'script' ||
                     request.destination === 'image',
    new CacheFirst({
        cacheName: 'assets-cache',
        plugins: [
            {
                cacheableResponse: {
                    statuses: [0, 200]
                }
            }
        ]
    })
);

// Stale-while-revalidate for API calls
registerRoute(
    ({ url }) => url.pathname.startsWith('/api/'),
    new StaleWhileRevalidate({
        cacheName: 'api-cache',
        plugins: [
            new BackgroundSyncPlugin('api-queue', {
                maxRetentionTime: 24 * 60 // 24 hours
            })
        ]
    })
);
```

**Offline Storage with IndexedDB**:

```javascript
// resources/js/offline-storage.js
class OfflineStorage {
    constructor() {
        this.dbName = 'umamusume-offline';
        this.version = 1;
        this.db = null;
    }
    
    async init() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.dbName, this.version);
            
            request.onerror = () => reject(request.error);
            request.onsuccess = () => {
                this.db = request.result;
                resolve(this.db);
            };
            
            request.onupgradeneeded = (event) => {
                const db = event.target.result;
                
                // Pending operations store
                if (!db.objectStoreNames.contains('pending_operations')) {
                    const store = db.createObjectStore('pending_operations', { 
                        keyPath: 'id', 
                        autoIncrement: true 
                    });
                    store.createIndex('timestamp', 'timestamp');
                    store.createIndex('type', 'type');
                }
                
                // Cached data store
                if (!db.objectStoreNames.contains('cached_data')) {
                    const store = db.createObjectStore('cached_data', { 
                        keyPath: 'key' 
                    });
                    store.createIndex('expiry', 'expiry');
                }
            };
        });
    }
    
    async queueOperation(operation) {
        const tx = this.db.transaction(['pending_operations'], 'readwrite');
        const store = tx.objectStore('pending_operations');
        
        await store.add({
            type: operation.type,
            payload: operation.payload,
            timestamp: Date.now(),
            retryCount: 0
        });
    }
    
    async getPendingOperations() {
        const tx = this.db.transaction(['pending_operations'], 'readonly');
        const store = tx.objectStore('pending_operations');
        
        return new Promise((resolve, reject) => {
            const request = store.getAll();
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }
    
    async removeOperation(id) {
        const tx = this.db.transaction(['pending_operations'], 'readwrite');
        const store = tx.objectStore('pending_operations');
        await store.delete(id);
    }
}

export default new OfflineStorage();
```

**Background Sync**:

```javascript
// resources/js/background-sync.js
class BackgroundSync {
    constructor(storage) {
        this.storage = storage;
        this.syncing = false;
    }
    
    async sync() {
        if (this.syncing) return;
        
        this.syncing = true;
        const operations = await this.storage.getPendingOperations();
        
        for (const operation of operations) {
            try {
                await this.executeOperation(operation);
                await this.storage.removeOperation(operation.id);
                
                // Notify user of success
                this.showNotification('Sync successful', {
                    body: `${operation.type} synced successfully`
                });
            } catch (error) {
                // Retry with exponential backoff
                if (operation.retryCount < 3) {
                    operation.retryCount++;
                    await this.storage.updateOperation(operation);
                } else {
                    // Max retries reached, notify user
                    this.showNotification('Sync failed', {
                        body: `Failed to sync ${operation.type}`,
                        requireInteraction: true
                    });
                }
            }
        }
        
        this.syncing = false;
    }
    
    async executeOperation(operation) {
        const response = await fetch(operation.payload.url, {
            method: operation.payload.method,
            headers: operation.payload.headers,
            body: JSON.stringify(operation.payload.data)
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        return response.json();
    }
}
```

**Conflict Resolution**:

```php
class OfflineSyncService {
    public function syncOperation(array $operation, User $user): array {
        $localVersion = $operation['data'];
        $serverVersion = $this->getServerVersion($operation['resource_id']);
        
        if ($this->hasConflict($localVersion, $serverVersion)) {
            return [
                'status' => 'conflict',
                'local' => $localVersion,
                'server' => $serverVersion,
                'resolution_options' => [
                    'keep_local',
                    'use_server',
                    'merge'
                ]
            ];
        }
        
        // No conflict, apply changes
        $this->applyChanges($localVersion);
        
        return [
            'status' => 'success',
            'data' => $localVersion
        ];
    }
    
    private function hasConflict($local, $server): bool {
        return $local['updated_at'] < $server['updated_at'];
    }
}
```

**Implementation Components**:

- `public/sw.js` - Service worker with Workbox
- `resources/js/offline-storage.js` - IndexedDB wrapper
- `resources/js/background-sync.js` - Background sync logic
- `app/Services/Offline/OfflineSyncService.php` - Server-side sync
- `tests/Browser/PWA/OfflineTest.php` - Offline functionality tests

---

### 5.2 Background Sync and Push Notifications (REQ-10)

**Design Decision**: Use Background Sync API for reliable sync and Web Push for notifications.

**Rationale**:

- Background Sync API ensures operations complete even after tab closes
- Web Push provides native-like notifications
- VAPID keys enable secure push without third-party services

**Push Notification Service**:

```php
// app/Services/Notifications/PushNotificationService.php
class PushNotificationService {
    public function __construct(
        private WebPush $webPush
    ) {}
    
    public function subscribe(User $user, array $subscription): void {
        PushSubscription::create([
            'user_id' => $user->id,
            'endpoint' => $subscription['endpoint'],
            'public_key' => $subscription['keys']['p256dh'],
            'auth_token' => $subscription['keys']['auth'],
            'content_encoding' => $subscription['contentEncoding'] ?? 'aes128gcm'
        ]);
    }
    
    public function sendNotification(User $user, array $notification): void {
        $subscriptions = $user->pushSubscriptions;
        
        foreach ($subscriptions as $subscription) {
            try {
                $this->webPush->sendNotification(
                    $subscription->toWebPushSubscription(),
                    json_encode($notification)
                );
            } catch (WebPushException $e) {
                if ($e->isSubscriptionExpired()) {
                    $subscription->delete();
                }
            }
        }
    }
    
    public function sendRaceReminder(Career $career, Race $race): void {
        $this->sendNotification($career->user, [
            'title' => 'Race Reminder',
            'body' => "Race {$race->name} starts in 1 hour!",
            'icon' => '/images/icons/race-icon.png',
            'badge' => '/images/icons/badge.png',
            'data' => [
                'url' => route('careers.show', $career),
                'race_id' => $race->id
            ],
            'actions' => [
                ['action' => 'view', 'title' => 'View Race'],
                ['action' => 'dismiss', 'title' => 'Dismiss']
            ]
        ]);
    }
}
```

**Client-Side Push Subscription**:

```javascript
// resources/js/push-notifications.js
class PushNotifications {
    async subscribe() {
        const registration = await navigator.serviceWorker.ready;
        
        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: this.urlBase64ToUint8Array(
                window.vapidPublicKey
            )
        });
        
        // Send subscription to server
        await fetch('/api/push/subscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(subscription)
        });
    }
    
    async unsubscribe() {
        const registration = await navigator.serviceWorker.ready;
        const subscription = await registration.pushManager.getSubscription();
        
        if (subscription) {
            await subscription.unsubscribe();
            
            // Notify server
            await fetch('/api/push/unsubscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
        }
    }
}
```

**Service Worker Push Handler**:

```javascript
// public/sw.js
self.addEventListener('push', (event) => {
    const data = event.data.json();
    
    event.waitUntil(
        self.registration.showNotification(data.title, {
            body: data.body,
            icon: data.icon,
            badge: data.badge,
            data: data.data,
            actions: data.actions,
            requireInteraction: data.requireInteraction || false
        })
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    
    if (event.action === 'view') {
        event.waitUntil(
            clients.openWindow(event.notification.data.url)
        );
    }
});
```

**Notification Preferences**:

```php
// app/Models/UserPreference.php
class UserPreference extends Model {
    protected $casts = [
        'notification_settings' => 'array'
    ];
    
    public function getNotificationSettings(): array {
        return $this->notification_settings ?? [
            'race_reminders' => true,
            'training_alerts' => true,
            'system_updates' => false,
            'quiet_hours' => [
                'enabled' => true,
                'start' => '22:00',
                'end' => '08:00'
            ]
        ];
    }
    
    public function shouldSendNotification(string $type): bool {
        $settings = $this->getNotificationSettings();
        
        if (!($settings[$type] ?? false)) {
            return false;
        }
        
        if ($settings['quiet_hours']['enabled']) {
            return !$this->isQuietHours();
        }
        
        return true;
    }
}
```

**Implementation Components**:

- `app/Services/Notifications/PushNotificationService.php` - Push service
- `resources/js/push-notifications.js` - Client-side push handling
- `public/sw.js` - Service worker push handlers
- `database/migrations/*_create_push_subscriptions_table.php` - Subscriptions table
- `tests/Feature/Notifications/PushNotificationTest.php` - Push tests

---

### 5.3 PWA Install Prompt (REQ-11)

**Design Decision**: Custom install prompt with engagement tracking.

**Rationale**:

- Native prompt timing is unpredictable
- Custom prompt allows better UX and messaging
- Engagement tracking informs prompt optimization

**Install Prompt Handler**:

```javascript
// resources/js/install-prompt.js
class InstallPrompt {
    constructor() {
        this.deferredPrompt = null;
        this.installButton = null;
        this.metrics = {
            visits: 0,
            timeSpent: 0,
            pagesViewed: 0
        };
        
        this.init();
    }
    
    init() {
        // Capture install prompt event
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            this.deferredPrompt = e;
            
            // Check if user meets engagement criteria
            if (this.shouldShowPrompt()) {
                this.showCustomPrompt();
            }
        });
        
        // Track engagement
        this.trackEngagement();
    }
    
    shouldShowPrompt() {
        const metrics = this.getMetrics();
        
        return metrics.visits >= 2 &&
               metrics.timeSpent >= 300 && // 5 minutes
               metrics.pagesViewed >= 3 &&
               !this.hasDeclinedRecently();
    }
    
    async showCustomPrompt() {
        // Show custom UI
        const modal = document.getElementById('install-prompt-modal');
        modal.classList.remove('hidden');
        
        // Track impression
        this.trackEvent('prompt_shown');
    }
    
    async install() {
        if (!this.deferredPrompt) return;
        
        this.deferredPrompt.prompt();
        
        const { outcome } = await this.deferredPrompt.userChoice;
        
        this.trackEvent('install_' + outcome);
        
        if (outcome === 'accepted') {
            this.hidePrompt();
        } else {
            this.recordDecline();
        }
        
        this.deferredPrompt = null;
    }
    
    trackEngagement() {
        // Track visits
        const visits = parseInt(localStorage.getItem('pwa_visits') || '0');
        localStorage.setItem('pwa_visits', (visits + 1).toString());
        
        // Track time spent
        let startTime = Date.now();
        window.addEventListener('beforeunload', () => {
            const timeSpent = parseInt(localStorage.getItem('pwa_time_spent') || '0');
            localStorage.setItem('pwa_time_spent', 
                (timeSpent + (Date.now() - startTime) / 1000).toString()
            );
        });
        
        // Track pages viewed
        const pagesViewed = parseInt(localStorage.getItem('pwa_pages_viewed') || '0');
        localStorage.setItem('pwa_pages_viewed', (pagesViewed + 1).toString());
    }
}

new InstallPrompt();
```

**Manifest Configuration**:

```json
{
  "name": "Umamusume Career Planner",
  "short_name": "UCP",
  "description": "Plan and optimize your Uma Musume careers",
  "start_url": "/",
  "display": "standalone",
  "background_color": "#ffffff",
  "theme_color": "#0066CC",
  "orientation": "portrait-primary",
  "icons": [
    {
      "src": "/images/icons/icon-72x72.png",
      "sizes": "72x72",
      "type": "image/png"
    },
    {
      "src": "/images/icons/icon-96x96.png",
      "sizes": "96x96",
      "type": "image/png"
    },
    {
      "src": "/images/icons/icon-128x128.png",
      "sizes": "128x128",
      "type": "image/png"
    },
    {
      "src": "/images/icons/icon-144x144.png",
      "sizes": "144x144",
      "type": "image/png"
    },
    {
      "src": "/images/icons/icon-152x152.png",
      "sizes": "152x152",
      "type": "image/png"
    },
    {
      "src": "/images/icons/icon-192x192.png",
      "sizes": "192x192",
      "type": "image/png"
    },
    {
      "src": "/images/icons/icon-384x384.png",
      "sizes": "384x384",
      "type": "image/png"
    },
    {
      "src": "/images/icons/icon-512x512.png",
      "sizes": "512x512",
      "type": "image/png",
      "purpose": "any maskable"
    }
  ],
  "screenshots": [
    {
      "src": "/images/screenshots/desktop-1.png",
      "sizes": "1920x1080",
      "type": "image/png",
      "form_factor": "wide"
    },
    {
      "src": "/images/screenshots/mobile-1.png",
      "sizes": "750x1334",
      "type": "image/png",
      "form_factor": "narrow"
    }
  ],
  "categories": ["games", "utilities"],
  "shortcuts": [
    {
      "name": "New Career",
      "short_name": "New",
      "description": "Start a new career run",
      "url": "/careers/create",
      "icons": [{ "src": "/images/icons/new-career.png", "sizes": "96x96" }]
    },
    {
      "name": "Dashboard",
      "short_name": "Home",
      "description": "View your dashboard",
      "url": "/dashboard",
      "icons": [{ "src": "/images/icons/dashboard.png", "sizes": "96x96" }]
    }
  ]
}
```

**PWA Analytics**:

```php
// app/Services/Analytics/PWAAnalyticsService.php
class PWAAnalyticsService {
    public function trackInstall(Request $request): void {
        PWAMetric::create([
            'event' => 'install',
            'user_agent' => $request->userAgent(),
            'platform' => $this->detectPlatform($request),
            'timestamp' => now()
        ]);
    }
    
    public function trackLaunch(Request $request, string $displayMode): void {
        PWAMetric::create([
            'event' => 'launch',
            'display_mode' => $displayMode, // standalone, browser, etc.
            'user_agent' => $request->userAgent(),
            'timestamp' => now()
        ]);
    }
    
    public function getInstallRate(Carbon $from, Carbon $to): float {
        $impressions = PWAMetric::where('event', 'prompt_shown')
            ->whereBetween('timestamp', [$from, $to])
            ->count();
            
        $installs = PWAMetric::where('event', 'install')
            ->whereBetween('timestamp', [$from, $to])
            ->count();
            
        return $impressions > 0 ? ($installs / $impressions) * 100 : 0;
    }
}
```

**Implementation Components**:

- `public/manifest.json` - PWA manifest
- `resources/js/install-prompt.js` - Install prompt handler
- `app/Services/Analytics/PWAAnalyticsService.php` - PWA analytics
- `public/images/icons/*` - App icons in various sizes
- `tests/Browser/PWA/InstallTest.php` - Install flow tests

---

## 6. Advanced Features Design

### 6.1 Batch Simulation System (REQ-12)

**Design Decision**: Queue-based parallel simulation with comparison analytics.

**Rationale**:

- Laravel queues enable parallel processing
- Comparison analytics provide actionable insights
- Modular design allows easy extension

**Simulation Architecture**:

```php
// app/Services/Simulation/BatchSimulationService.php
class BatchSimulationService {
    public function createBatch(User $user, array $config): BatchSimulation {
        $batch = BatchSimulation::create([
            'user_id' => $user->id,
            'base_character' => $config['base_character'],
            'scenarios_count' => count($config['scenarios']),
            'status' => 'pending'
        ]);
        
        foreach ($config['scenarios'] as $index => $scenario) {
            $this->queueScenario($batch, $index, $scenario);
        }
        
        return $batch;
    }
    
    private function queueScenario(BatchSimulation $batch, int $index, array $scenario): void {
        RunSimulationScenarioJob::dispatch($batch, $index, $scenario)
            ->onQueue('simulations');
    }
}

// app/Jobs/RunSimulationScenarioJob.php
class RunSimulationScenarioJob implements ShouldQueue {
    public function handle(SimulationEngine $engine): void {
        $result = $engine->simulate(
            $this->batch->base_character,
            $this->scenario
        );
        
        SimulationResult::create([
            'batch_id' => $this->batch->id,
            'scenario_index' => $this->index,
            'final_stats' => $result->finalStats,
            'win_rate' => $result->winRate,
            'sp_efficiency' => $result->spEfficiency,
            'key_decisions' => $result->keyDecisions,
            'bottlenecks' => $result->bottlenecks
        ]);
        
        $this->batch->increment('completed_scenarios');
        
        if ($this->batch->completed_scenarios === $this->batch->scenarios_count) {
            $this->batch->update(['status' => 'completed']);
            GenerateComparisonReportJob::dispatch($this->batch);
        }
    }
}
```

**Simulation Engine**:

```php
// app/Services/Simulation/SimulationEngine.php
class SimulationEngine {
    public function simulate(Character $baseCharacter, array $scenario): SimulationResult {
        $character = $this->cloneCharacter($baseCharacter);
        $career = $this->initializeCareer($character, $scenario);
        
        for ($turn = 1; $turn <= 78; $turn++) {
            $training = $this->selectTraining($career, $scenario['strategy'], $turn);
            $result = $this->executeTraining($career, $training);
            
            $this->recordTurn($career, $turn, $result);
            
            if ($this->shouldRace($career, $turn, $scenario['race_schedule'])) {
                $this->executeRace($career, $turn);
            }
        }
        
        return new SimulationResult([
            'finalStats' => $career->getFinalStats(),
            'winRate' => $this->calculateWinRate($career),
            'spEfficiency' => $this->calculateSPEfficiency($career),
            'keyDecisions' => $this->identifyKeyDecisions($career),
            'bottlenecks' => $this->identifyBottlenecks($career)
        ]);
    }
    
    private function selectTraining(Career $career, array $strategy, int $turn): Training {
        // AI-driven training selection based on strategy
        return $this->trainingSelector->select($career, $strategy, $turn);
    }
}
```

**Comparison Report Generator**:

```php
// app/Services/Simulation/ComparisonReportService.php
class ComparisonReportService {
    public function generate(BatchSimulation $batch): ComparisonReport {
        $results = $batch->results;
        
        return new ComparisonReport([
            'summary' => $this->generateSummary($results),
            'statComparison' => $this->compareStats($results),
            'winRateAnalysis' => $this->analyzeWinRates($results),
            'spEfficiency' => $this->compareSPEfficiency($results),
            'recommendation' => $this->generateRecommendation($results)
        ]);
    }
    
    private function generateRecommendation(Collection $results): array {
        $best = $results->sortByDesc('win_rate')->first();
        
        return [
            'scenario_index' => $best->scenario_index,
            'reasons' => [
                "Highest win rate: {$best->win_rate}%",
                "Best SP efficiency: {$best->sp_efficiency}",
                "Optimal stat distribution"
            ],
            'key_factors' => $best->key_decisions
        ];
    }
}
```

**Implementation Components**:

- `app/Services/Simulation/BatchSimulationService.php` - Batch orchestration
- `app/Services/Simulation/SimulationEngine.php` - Core simulation logic
- `app/Services/Simulation/ComparisonReportService.php` - Report generation
- `app/Jobs/RunSimulationScenarioJob.php` - Scenario execution job
- `database/migrations/*_create_batch_simulations_table.php` - Database schema
- `tests/Feature/Simulation/BatchSimulationTest.php` - Simulation tests

---

### 6.2 AI Model Retraining (REQ-13)

**Design Decision**: Continuous learning with A/B testing for model deployment.

**Rationale**:

- Historical data improves prediction accuracy
- A/B testing ensures new models are better
- Automated retraining reduces manual intervention

**Model Retraining Pipeline**:

```php
// app/Services/AI/ModelRetrainingService.php
class ModelRetrainingService {
    public function retrain(): void {
        // Collect training data
        $trainingData = $this->collectTrainingData();
        
        if ($trainingData->count() < 1000) {
            Log::info('Insufficient data for retraining', [
                'count' => $trainingData->count()
            ]);
            return;
        }
        
        // Train new model
        $newModel = $this->trainModel($trainingData);
        
        // Validate against holdout set
        $accuracy = $this->validateModel($newModel);
        
        // Compare with current model
        $currentAccuracy = $this->getCurrentModelAccuracy();
        
        if ($accuracy > $currentAccuracy * 1.05) { // 5% improvement
            $this->deployModel($newModel, 'ab_test');
        } else {
            Log::info('New model did not meet improvement threshold', [
                'current' => $currentAccuracy,
                'new' => $accuracy
            ]);
        }
    }
    
    private function collectTrainingData(): Collection {
        return DB::table('training_predictions')
            ->join('training_sessions', 'training_predictions.session_id', '=', 'training_sessions.id')
            ->select([
                'training_predictions.predicted_stats',
                'training_sessions.actual_stats',
                'training_sessions.character_stats',
                'training_sessions.support_cards',
                'training_sessions.mood',
                'training_sessions.energy'
            ])
            ->where('training_sessions.completed', true)
            ->get();
    }
    
    private function trainModel(Collection $data): Model {
        // Feature engineering
        $features = $data->map(fn($row) => $this->extractFeatures($row));
        $labels = $data->map(fn($row) => $row->actual_stats);
        
        // Train model (using Python ML service or PHP-ML)
        $model = $this->mlService->train($features, $labels, [
            'algorithm' => 'gradient_boosting',
            'max_depth' => 10,
            'learning_rate' => 0.1,
            'n_estimators' => 100
        ]);
        
        return $model;
    }
    
    private function deployModel(Model $model, string $strategy): void {
        $version = $this->getNextModelVersion();
        
        // Save model
        Storage::put("models/training-prediction-v{$version}.model", 
            serialize($model));
        
        // Update deployment config
        if ($strategy === 'ab_test') {
            config(['ai.models.training_prediction' => [
                'current' => $this->getCurrentModelVersion(),
                'candidate' => $version,
                'split' => 0.5 // 50/50 split
            ]]);
        } else {
            config(['ai.models.training_prediction.current' => $version]);
        }
    }
}
```

**A/B Testing Framework**:

```php
// app/Services/AI/ABTestingService.php
class ABTestingService {
    public function selectModel(User $user, string $modelType): string {
        $config = config("ai.models.{$modelType}");
        
        if (!isset($config['candidate'])) {
            return $config['current'];
        }
        
        // Consistent assignment based on user ID
        $hash = crc32($user->id . $modelType);
        $bucket = $hash % 100;
        
        return $bucket < ($config['split'] * 100) 
            ? $config['candidate'] 
            : $config['current'];
    }
    
    public function recordPrediction(string $modelVersion, array $prediction, array $actual): void {
        ModelPerformance::create([
            'model_version' => $modelVersion,
            'predicted' => $prediction,
            'actual' => $actual,
            'error' => $this->calculateError($prediction, $actual),
            'recorded_at' => now()
        ]);
    }
    
    public function evaluateABTest(string $modelType): array {
        $config = config("ai.models.{$modelType}");
        
        $currentPerformance = $this->getModelPerformance($config['current']);
        $candidatePerformance = $this->getModelPerformance($config['candidate']);
        
        return [
            'current' => $currentPerformance,
            'candidate' => $candidatePerformance,
            'winner' => $candidatePerformance['accuracy'] > $currentPerformance['accuracy']
                ? 'candidate'
                : 'current',
            'improvement' => $candidatePerformance['accuracy'] - $currentPerformance['accuracy']
        ];
    }
}
```

**Implementation Components**:

- `app/Services/AI/ModelRetrainingService.php` - Retraining orchestration
- `app/Services/AI/ABTestingService.php` - A/B testing framework
- `app/Jobs/RetrainPredictionModelJob.php` - Scheduled retraining job
- `database/migrations/*_create_model_performance_table.php` - Performance tracking
- `tests/Feature/AI/ModelRetrainingTest.php` - Retraining tests

---

### 6.3 Advanced Analytics (REQ-14)

**Design Decision**: Pattern recognition with machine learning and interactive visualizations.

**Rationale**:

- ML identifies non-obvious patterns
- Interactive charts enable exploration
- Actionable insights drive user value

**Pattern Recognition Service**:

```php
// app/Services/Analytics/PatternRecognitionService.php
class PatternRecognitionService {
    public function identifySuccessPatterns(Collection $careers): array {
        $successfulCareers = $careers->filter(fn($c) => $c->final_grade === 'A+');
        
        // Cluster analysis
        $clusters = $this->clusterCareers($successfulCareers);
        
        // Association rule mining
        $rules = $this->mineAssociationRules($successfulCareers);
        
        // Temporal pattern detection
        $temporalPatterns = $this->detectTemporalPatterns($successfulCareers);
        
        return [
            'clusters' => $clusters,
            'rules' => $rules,
            'temporal_patterns' => $temporalPatterns,
            'recommendations' => $this->generateRecommendations($clusters, $rules)
        ];
    }
    
    private function clusterCareers(Collection $careers): array {
        $features = $careers->map(fn($c) => [
            'speed_ratio' => $c->final_speed / $c->total_stats,
            'stamina_ratio' => $c->final_stamina / $c->total_stats,
            'power_ratio' => $c->final_power / $c->total_stats,
            'guts_ratio' => $c->final_guts / $c->total_stats,
            'wit_ratio' => $c->final_wit / $c->total_stats,
            'skill_count' => $c->skills->count(),
            'sp_efficiency' => $c->total_stats / $c->sp_spent
        ]);
        
        // K-means clustering
        $kmeans = new KMeans(5); // 5 clusters
        $clusters = $kmeans->cluster($features->toArray());
        
        return $this->interpretClusters($clusters, $careers);
    }
    
    private function mineAssociationRules(Collection $careers): array {
        // Apriori algorithm for association rules
        $transactions = $careers->map(fn($c) => [
            'support_cards' => $c->support_cards->pluck('id')->toArray(),
            'skills' => $c->skills->pluck('id')->toArray(),
            'training_focus' => $this->identifyTrainingFocus($c)
        ]);
        
        $apriori = new Apriori(0.3, 0.7); // min_support, min_confidence
        $rules = $apriori->mine($transactions->toArray());
        
        return $this->interpretRules($rules);
    }
}
```

**Career Comparison Service**:

```php
// app/Services/Analytics/CareerComparisonService.php
class CareerComparisonService {
    public function compare(array $careerIds): ComparisonResult {
        $careers = Career::with(['training_sessions', 'skills', 'races'])
            ->findMany($careerIds);
        
        return new ComparisonResult([
            'stat_comparison' => $this->compareStats($careers),
            'timeline_comparison' => $this->compareTimelines($careers),
            'skill_overlap' => $this->analyzeSkillOverlap($careers),
            'deviation_analysis' => $this->analyzeDeviations($careers)
        ]);
    }
    
    private function compareStats(Collection $careers): array {
        return [
            'parallel_coordinates' => $this->generateParallelCoordinates($careers),
            'radar_chart' => $this->generateRadarChart($careers),
            'stat_distribution' => $this->analyzeStatDistribution($careers)
        ];
    }
    
    private function compareTimelines(Collection $careers): array {
        $timelines = $careers->map(function($career) {
            return $career->training_sessions->map(function($session) {
                return [
                    'turn' => $session->turn_number,
                    'speed' => $session->speed,
                    'stamina' => $session->stamina,
                    'power' => $session->power,
                    'guts' => $session->guts,
                    'wit' => $session->wit
                ];
            });
        });
        
        return [
            'timelines' => $timelines,
            'milestones' => $this->identifyMilestones($timelines),
            'divergence_points' => $this->findDivergencePoints($timelines)
        ];
    }
}
```

**Visualization Components**:

```javascript
// resources/js/charts/comparison-charts.js
import * as d3 from 'd3';

class ComparisonCharts {
    renderParallelCoordinates(data, container) {
        const dimensions = ['speed', 'stamina', 'power', 'guts', 'wit'];
        const margin = { top: 30, right: 10, bottom: 10, left: 10 };
        const width = 960 - margin.left - margin.right;
        const height = 500 - margin.top - margin.bottom;
        
        const svg = d3.select(container)
            .append('svg')
            .attr('width', width + margin.left + margin.right)
            .attr('height', height + margin.top + margin.bottom)
            .append('g')
            .attr('transform', `translate(${margin.left},${margin.top})`);
        
        // Create scales for each dimension
        const y = {};
        dimensions.forEach(dim => {
            y[dim] = d3.scaleLinear()
                .domain([0, 1200])
                .range([height, 0]);
        });
        
        const x = d3.scalePoint()
            .domain(dimensions)
            .range([0, width]);
        
        // Draw lines
        const line = d3.line();
        const path = function(d) {
            return line(dimensions.map(p => [x(p), y[p](d[p])]));
        };
        
        svg.selectAll('path')
            .data(data)
            .enter().append('path')
            .attr('d', path)
            .style('fill', 'none')
            .style('stroke', (d, i) => d3.schemeCategory10[i])
            .style('opacity', 0.7);
        
        // Draw axes
        dimensions.forEach(dim => {
            svg.append('g')
                .attr('transform', `translate(${x(dim)},0)`)
                .call(d3.axisLeft(y[dim]));
            
            svg.append('text')
                .attr('x', x(dim))
                .attr('y', -10)
                .text(dim)
                .style('text-anchor', 'middle');
        });
    }
}
```

**Implementation Components**:

- `app/Services/Analytics/PatternRecognitionService.php` - Pattern detection
- `app/Services/Analytics/CareerComparisonService.php` - Comparison logic
- `resources/js/charts/comparison-charts.js` - D3.js visualizations
- `resources/views/analytics/patterns.blade.php` - Analytics dashboard
- `tests/Feature/Analytics/PatternRecognitionTest.php` - Analytics tests

---

### 6.4 Enhanced Export Formats (REQ-15)

**Design Decision**: Template-based export with multiple format support.

**Rationale**:

- Templates enable consistent formatting
- Multiple formats serve different use cases
- Shareable links enable community engagement

**PDF Export Service**:

```php
// app/Services/Export/PDFExportService.php
class PDFExportService {
    public function export(Career $career): string {
        $pdf = PDF::loadView('export.pdf-template', [
            'career' => $career,
            'stats_chart' => $this->generateStatsChart($career),
            'timeline_chart' => $this->generateTimelineChart($career),
            'skills' => $career->skills,
            'races' => $career->races
        ]);
        
        $pdf->setPaper('a4', 'portrait');
        
        $filename = "career-{$career->id}-" . now()->format('Y-m-d') . '.pdf';
        $path = storage_path("app/exports/{$filename}");
        
        $pdf->save($path);
        
        return $path;
    }
    
    private function generateStatsChart(Career $career): string {
        // Generate chart image using Chart.js or similar
        $chart = new ChartGenerator();
        return $chart->generateStatProgressionChart($career);
    }
}
```

**Excel Export Service**:

```php
// app/Services/Export/ExcelExportService.php
class ExcelExportService {
    public function export(Career $career): string {
        $spreadsheet = new Spreadsheet();
        
        // Overview sheet
        $this->createOverviewSheet($spreadsheet, $career);
        
        // Stats sheet
        $this->createStatsSheet($spreadsheet, $career);
        
        // Skills sheet
        $this->createSkillsSheet($spreadsheet, $career);
        
        // Races sheet
        $this->createRacesSheet($spreadsheet, $career);
        
        $filename = "career-{$career->id}-" . now()->format('Y-m-d') . '.xlsx';
        $path = storage_path("app/exports/{$filename}");
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);
        
        return $path;
    }
    
    private function createStatsSheet(Spreadsheet $spreadsheet, Career $career): void {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Stats');
        
        // Headers
        $sheet->setCellValue('A1', 'Turn');
        $sheet->setCellValue('B1', 'Speed');
        $sheet->setCellValue('C1', 'Stamina');
        $sheet->setCellValue('D1', 'Power');
        $sheet->setCellValue('E1', 'Guts');
        $sheet->setCellValue('F1', 'Wit');
        $sheet->setCellValue('G1', 'Total');
        
        // Data
        $row = 2;
        foreach ($career->training_sessions as $session) {
            $sheet->setCellValue("A{$row}", $session->turn_number);
            $sheet->setCellValue("B{$row}", $session->speed);
            $sheet->setCellValue("C{$row}", $session->stamina);
            $sheet->setCellValue("D{$row}", $session->power);
            $sheet->setCellValue("E{$row}", $session->guts);
            $sheet->setCellValue("F{$row}", $session->wit);
            $sheet->setCellValue("G{$row}", "=SUM(B{$row}:F{$row})");
            $row++;
        }
        
        // Chart
        $chart = new Chart(
            'statsChart',
            new Title('Stat Progression'),
            new Legend(Legend::POSITION_RIGHT)
        );
        
        $dataSeriesLabels = [
            new DataSeriesValues('String', 'Stats!$B$1', null, 1),
            // ... other stats
        ];
        
        $sheet->addChart($chart);
    }
}
```

**Share Link Service**:

```php
// app/Services/Share/ShareLinkService.php
class ShareLinkService {
    public function createShareLink(Career $career, array $options): ShareLink {
        $token = Str::random(32);
        
        return ShareLink::create([
            'career_id' => $career->id,
            'token' => $token,
            'privacy' => $options['privacy'], // public, unlisted, password
            'password' => $options['password'] ?? null,
            'expires_at' => $options['expires_at'] ?? null,
            'view_count' => 0
        ]);
    }
    
    public function viewSharedCareer(string $token, ?string $password = null): Career {
        $shareLink = ShareLink::where('token', $token)->firstOrFail();
        
        // Check expiration
        if ($shareLink->expires_at && $shareLink->expires_at->isPast()) {
            throw new ShareLinkExpiredException();
        }
        
        // Check password
        if ($shareLink->password && !Hash::check($password, $shareLink->password)) {
            throw new InvalidPasswordException();
        }
        
        // Increment view count
        $shareLink->increment('view_count');
        
        return $shareLink->career;
    }
}
```

**Implementation Components**:

- `app/Services/Export/PDFExportService.php` - PDF generation
- `app/Services/Export/ExcelExportService.php` - Excel generation
- `app/Services/Share/ShareLinkService.php` - Share link management
- `resources/views/export/pdf-template.blade.php` - PDF template
- `resources/views/share/career.blade.php` - Shared career view
- `tests/Feature/Export/EnhancedExportTest.php` - Export tests

---

## 7. Testing Strategy Design

### 7.1 Property-Based Testing (REQ-16)

**Design Decision**: Use Pest v4 property testing with custom generators.

**Rationale**:

- Property testing catches edge cases missed by example-based tests
- Pest v4 provides excellent property testing support
- Custom generators ensure domain-specific constraints

**Property Test Examples**:

```php
// tests/Property/StatCalculationPropertyTest.php
use function Pest\property;

it('ensures stats never exceed 1200', function () {
    property()
        ->forAll(
            Generator::int(0, 1200), // base stat
            Generator::int(0, 500)   // gain
        )
        ->then(function ($baseStat, $gain) {
            $calculator = new StatCalculator();
            $result = $calculator->addStat($baseStat, $gain);
            
            expect($result)->toBeLessThanOrEqual(1200);
        });
});

it('ensures stat calculations are deterministic', function () {
    property()
        ->forAll(
            Generator::int(0, 1200),
            Generator::int(0, 500)
        )
        ->then(function ($baseStat, $gain) {
            $calculator = new StatCalculator();
            
            $result1 = $calculator->addStat($baseStat, $gain);
            $result2 = $calculator->addStat($baseStat, $gain);
            
            expect($result1)->toBe($result2);
        });
});

it('ensures import-export round-trip preserves data', function () {
    property()
        ->forAll(CareerGenerator::generate())
        ->then(function ($career) {
            $exporter = new CareerExporter();
            $importer = new CareerImporter();
            
            $exported = $exporter->export($career);
            $imported = $importer->import($exported);
            
            expect($imported)->toEqual($career);
        });
});
```

**Custom Generators**:

```php
// tests/Property/Generators/CareerGenerator.php
class CareerGenerator {
    public static function generate(): Generator {
        return Generator::map(
            function ($data) {
                return new Career([
                    'name' => $data['name'],
                    'speed' => $data['speed'],
                    'stamina' => $data['stamina'],
                    'power' => $data['power'],
                    'guts' => $data['guts'],
                    'wit' => $data['wit']
                ]);
            },
            Generator::associative([
                'name' => Generator::string(1, 50),
                'speed' => Generator::int(0, 1200),
                'stamina' => Generator::int(0, 1200),
                'power' => Generator::int(0, 1200),
                'guts' => Generator::int(0, 1200),
                'wit' => Generator::int(0, 1200)
            ])
        );
    }
}
```

**Implementation Components**:

- `tests/Property/*PropertyTest.php` - Property-based tests
- `tests/Property/Generators/*` - Custom generators
- `tests/pest-properties.php` - Property test configuration
- `.github/workflows/property-tests.yml` - CI integration

---

### 7.2 Comprehensive Browser Testing (REQ-17)

**Design Decision**: Playwright for cross-browser E2E testing with visual regression.

**Rationale**:

- Playwright supports all major browsers
- Built-in visual regression testing
- Excellent developer experience

**Playwright Configuration**:

```typescript
// playwright.config.ts
import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
    testDir: './tests/Browser',
    fullyParallel: true,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 2 : 0,
    workers: process.env.CI ? 4 : undefined,
    reporter: [
        ['html'],
        ['junit', { outputFile: 'test-results/junit.xml' }]
    ],
    use: {
        baseURL: 'http://localhost:8000',
        trace: 'on-first-retry',
        screenshot: 'only-on-failure',
        video: 'retain-on-failure'
    },
    projects: [
        {
            name: 'chromium',
            use: { ...devices['Desktop Chrome'] }
        },
        {
            name: 'firefox',
            use: { ...devices['Desktop Firefox'] }
        },
        {
            name: 'webkit',
            use: { ...devices['Desktop Safari'] }
        },
        {
            name: 'mobile-chrome',
            use: { ...devices['Pixel 5'] }
        },
        {
            name: 'mobile-safari',
            use: { ...devices['iPhone 12'] }
        }
    ],
    webServer: {
        command: 'php artisan serve',
        port: 8000,
        reuseExistingServer: !process.env.CI
    }
});
```

**Cross-Browser Test Example**:

```typescript
// tests/Browser/CrossBrowser/CharacterCreation.spec.ts
import { test, expect } from '@playwright/test';

test.describe('Character Creation', () => {
    test('creates character successfully', async ({ page }) => {
        await page.goto('/characters/create');
        
        // Fill form
        await page.fill('[name="name"]', 'Test Character');
        await page.selectOption('[name="uma_musume_id"]', '1');
        
        // Submit
        await page.click('button[type="submit"]');
        
        // Verify success
        await expect(page).toHaveURL(/\/characters\/\d+/);
        await expect(page.locator('.success-message')).toBeVisible();
    });
    
    test('validates required fields', async ({ page }) => {
        await page.goto('/characters/create');
        
        // Submit without filling
        await page.click('button[type="submit"]');
        
        // Verify validation errors
        await expect(page.locator('.error-message')).toHaveCount(2);
    });
});
```

**Visual Regression Test**:

```typescript
// tests/Browser/VisualRegression/Dashboard.spec.ts
import { test, expect } from '@playwright/test';

test.describe('Dashboard Visual Regression', () => {
    test('matches dashboard screenshot', async ({ page }) => {
        await page.goto('/dashboard');
        
        // Wait for content to load
        await page.waitForSelector('.dashboard-content');
        
        // Take screenshot and compare
        await expect(page).toHaveScreenshot('dashboard.png', {
            maxDiffPixels: 100
        });
    });
    
    test('matches dashboard in dark mode', async ({ page }) => {
        await page.goto('/dashboard');
        
        // Toggle dark mode
        await page.click('[data-theme-toggle]');
        await page.waitForTimeout(500); // Wait for transition
        
        await expect(page).toHaveScreenshot('dashboard-dark.png', {
            maxDiffPixels: 100
        });
    });
});
```

**Implementation Components**:

- `playwright.config.ts` - Playwright configuration
- `tests/Browser/CrossBrowser/*.spec.ts` - Cross-browser tests
- `tests/Browser/VisualRegression/*.spec.ts` - Visual regression tests
- `.github/workflows/browser-tests.yml` - CI integration

---

### 7.3 Performance Benchmarking (REQ-18)

**Design Decision**: Custom benchmarking suite with historical tracking.

**Rationale**:

- Custom suite tailored to application needs
- Historical tracking enables trend analysis
- CI integration prevents regressions

**Benchmark Suite**:

```php
// tests/Performance/BenchmarkSuite.php
class BenchmarkSuite extends TestCase {
    private array $results = [];
    
    public function testDashboardLoadTime(): void {
        $benchmark = $this->benchmark(function () {
            $this->get('/dashboard');
        }, iterations: 10);
        
        $this->assertLessThan(2000, $benchmark['avg'], 
            'Dashboard load time exceeds 2s');
        
        $this->recordBenchmark('dashboard_load_time', $benchmark);
    }
    
    public function testTrainingPredictionTime(): void {
        $career = Career::factory()->create();
        
        $benchmark = $this->benchmark(function () use ($career) {
            app(TrainingPredictionService::class)->predict($career);
        }, iterations: 100);
        
        $this->assertLessThan(500, $benchmark['avg'],
            'Training prediction exceeds 500ms');
        
        $this->recordBenchmark('training_prediction_time', $benchmark);
    }
    
    public function testDatabaseQueryPerformance(): void {
        Character::factory()->count(100)->create();
        
        $benchmark = $this->benchmark(function () {
            Character::with(['aptitudes', 'factors'])->get();
        }, iterations: 10);
        
        $this->assertLessThan(100, $benchmark['avg'],
            'Character query exceeds 100ms');
        
        $this->recordBenchmark('character_query_time', $benchmark);
    }
    
    private function benchmark(callable $callback, int $iterations): array {
        $times = [];
        
        for ($i = 0; $i < $iterations; $i++) {
            $start = microtime(true);
            $callback();
            $times[] = (microtime(true) - $start) * 1000; // Convert to ms
        }
        
        return [
            'avg' => array_sum($times) / count($times),
            'min' => min($times),
            'max' => max($times),
            'p50' => $this->percentile($times, 50),
            'p95' => $this->percentile($times, 95),
            'p99' => $this->percentile($times, 99)
        ];
    }
    
    private function recordBenchmark(string $name, array $results): void {
        DB::table('performance_benchmarks')->insert([
            'name' => $name,
            'avg' => $results['avg'],
            'min' => $results['min'],
            'max' => $results['max'],
            'p50' => $results['p50'],
            'p95' => $results['p95'],
            'p99' => $results['p99'],
            'git_commit' => exec('git rev-parse HEAD'),
            'php_version' => PHP_VERSION,
            'recorded_at' => now()
        ]);
    }
}
```

**Load Testing Script**:

```javascript
// tests/Performance/load-test.js (k6)
import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
    stages: [
        { duration: '2m', target: 10 },  // Ramp up to 10 users
        { duration: '5m', target: 50 },  // Ramp up to 50 users
        { duration: '5m', target: 100 }, // Ramp up to 100 users
        { duration: '2m', target: 0 },   // Ramp down to 0 users
    ],
    thresholds: {
        http_req_duration: ['p(95)<2000'], // 95% of requests under 2s
        http_req_failed: ['rate<0.01'],    // Error rate under 1%
    }
};

export default function () {
    // Dashboard
    let res = http.get('http://localhost:8000/dashboard');
    check(res, {
        'dashboard status is 200': (r) => r.status === 200,
        'dashboard loads in <2s': (r) => r.timings.duration < 2000
    });
    
    sleep(1);
    
    // Character list
    res = http.get('http://localhost:8000/characters');
    check(res, {
        'character list status is 200': (r) => r.status === 200,
        'character list loads in <1.5s': (r) => r.timings.duration < 1500
    });
    
    sleep(1);
}
```

**Implementation Components**:

- `tests/Performance/BenchmarkSuite.php` - Benchmark tests
- `tests/Performance/load-test.js` - k6 load testing script
- `scripts/run-benchmarks.sh` - Benchmark execution script
- `.github/workflows/performance-benchmarks.yml` - CI integration
- `database/migrations/*_create_performance_benchmarks_table.php` - Tracking table

---

## 8. Security Design

### 8.1 Comprehensive Security Audit (REQ-19)

**Design Decision**: Automated security scanning with manual penetration testing.

**Rationale**:

- Automated scanning catches common vulnerabilities
- Manual testing finds complex security issues
- Regular audits maintain security posture

**Security Scanning Pipeline**:

```yaml
# .github/workflows/security-scan.yml
name: Security Scan

on:
  schedule:
    - cron: '0 0 * * *' # Daily at midnight
  push:
    branches: [main, develop]

jobs:
  dependency-scan:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: PHP Dependency Scan
        run: composer audit
      
      - name: JavaScript Dependency Scan
        run: npm audit --audit-level=moderate
      
      - name: Create Security Issue
        if: failure()
        uses: actions/github-script@v6
        with:
          script: |
            github.rest.issues.create({
              owner: context.repo.owner,
              repo: context.repo.repo,
              title: 'Security vulnerabilities detected',
              body: 'Automated security scan found vulnerabilities. Please review.',
              labels: ['security', 'high-priority']
            })
  
  static-analysis:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Run Larastan
        run: vendor/bin/phpstan analyse --error-format=github
      
      - name: Run ESLint Security
        run: npm run lint:security
  
  owasp-scan:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: OWASP ZAP Scan
        uses: zaproxy/action-baseline@v0.7.0
        with:
          target: 'http://localhost:8000'
```

**Security Test Suite**:

```php
// tests/Security/VulnerabilityScanTest.php
class VulnerabilityScanTest extends TestCase {
    public function testSQLInjectionProtection(): void {
        $maliciousInput = "1' OR '1'='1";
        
        $response = $this->get("/characters?search={$maliciousInput}");
        
        // Should not return all characters
        $this->assertNotEquals(
            Character::count(),
            $response->json('data.total')
        );
    }
    
    public function testXSSProtection(): void {
        $maliciousScript = '<script>alert("XSS")</script>';
        
        $character = Character::factory()->create([
            'name' => $maliciousScript
        ]);
        
        $response = $this->get("/characters/{$character->id}");
        
        // Script should be escaped
        $response->assertDontSee($maliciousScript, false);
        $response->assertSee(e($maliciousScript), false);
    }
    
    public function testCSRFProtection(): void {
        $response = $this->post('/characters', [
            'name' => 'Test Character'
        ]);
        
        // Should fail without CSRF token
        $response->assertStatus(419);
    }
    
    public function testAuthorizationBypass(): void {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $character = Character::factory()->create([
            'user_id' => $user1->id
        ]);
        
        // User 2 should not access User 1's character
        $response = $this->actingAs($user2)
            ->get("/characters/{$character->id}");
        
        $response->assertForbidden();
    }
    
    public function testFileUploadSecurity(): void {
        $user = User::factory()->create();
        
        // Attempt to upload PHP file
        $file = UploadedFile::fake()->create('malicious.php', 100);
        
        $response = $this->actingAs($user)
            ->post('/characters/import', [
                'file' => $file
            ]);
        
        // Should reject non-allowed file types
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('file');
    }
}
```

**Security Policy**:

```
# public/.well-known/security.txt
Contact: security@umamusume-planner.com
Expires: 2027-01-01T00:00:00.000Z
Preferred-Languages: en, ja
Canonical: https://umamusume-planner.com/.well-known/security.txt

# Vulnerability Disclosure Policy
We take security seriously. If you discover a security vulnerability,
please report it to security@umamusume-planner.com.

# Scope
- All features of umamusume-planner.com
- API endpoints
- Authentication and authorization
- Data handling and storage

# Out of Scope
- Social engineering attacks
- Physical attacks
- Denial of service attacks

# Safe Harbor
We will not pursue legal action against security researchers who:
- Make a good faith effort to avoid privacy violations
- Do not exploit vulnerabilities beyond proof of concept
- Report vulnerabilities promptly
- Do not publicly disclose vulnerabilities before we have addressed them
```

**Implementation Components**:

- `.github/workflows/security-scan.yml` - Automated security scanning
- `tests/Security/VulnerabilityScanTest.php` - Security tests
- `public/.well-known/security.txt` - Security policy
- `docs/security/security-policy.md` - Detailed security documentation

---

### 8.2 Enhanced Data Privacy Controls (REQ-20)

**Design Decision**: GDPR-compliant privacy dashboard with granular controls.

**Rationale**:

- GDPR compliance is legally required
- User trust depends on privacy controls
- Granular controls respect user preferences

**Privacy Dashboard**:

```php
// app/Http/Controllers/PrivacyController.php
class PrivacyController extends Controller {
    public function dashboard() {
        $user = auth()->user();
        
        return view('privacy.dashboard', [
            'dataSize' => $this->calculateDataSize($user),
            'accessLog' => $this->getAccessLog($user),
            'sharingSettings' => $user->preferences->sharing_settings,
            'exportRequests' => $user->exportRequests,
            'deletionRequest' => $user->deletionRequest
        ]);
    }
    
    public function requestExport(Request $request) {
        $user = auth()->user();
        
        $exportRequest = DataExportRequest::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'requested_at' => now()
        ]);
        
        GenerateDataExportJob::dispatch($exportRequest)
            ->delay(now()->addMinutes(5));
        
        return redirect()->back()->with('success', 
            'Data export requested. You will receive an email when ready.');
    }
    
    public function requestDeletion(Request $request) {
        $request->validate([
            'confirmation' => 'required|in:DELETE MY ACCOUNT'
        ]);
        
        $user = auth()->user();
        
        AccountDeletionRequest::create([
            'user_id' => $user->id,
            'scheduled_for' => now()->addDays(30),
            'status' => 'pending'
        ]);
        
        return redirect()->route('privacy.deletion-scheduled');
    }
}
```

**Data Export Service**:

```php
// app/Services/Privacy/DataExportService.php
class DataExportService {
    public function generateExport(User $user): string {
        $data = [
            'user' => $this->exportUserData($user),
            'characters' => $this->exportCharacters($user),
            'careers' => $this->exportCareers($user),
            'training_sessions' => $this->exportTrainingSessions($user),
            'skills' => $this->exportSkills($user),
            'preferences' => $this->exportPreferences($user),
            'activity_log' => $this->exportActivityLog($user),
            'export_metadata' => [
                'exported_at' => now()->toIso8601String(),
                'format_version' => '2.1.0',
                'data_retention_policy' => 'https://umamusume-planner.com/privacy'
            ]
        ];
        
        $filename = "user-data-{$user->id}-" . now()->format('Y-m-d') . '.json';
        $path = storage_path("app/exports/{$filename}");
        
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
        
        return $path;
    }
    
    private function exportUserData(User $user): array {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at->toIso8601String(),
            'updated_at' => $user->updated_at->toIso8601String()
        ];
    }
    
    private function exportCareers(User $user): array {
        return $user->careers()->with([
            'character',
            'training_sessions',
            'skills',
            'races'
        ])->get()->map(function ($career) {
            return [
                'id' => $career->id,
                'name' => $career->name,
                'character' => $career->character->name,
                'status' => $career->run_status,
                'final_stats' => [
                    'speed' => $career->final_speed,
                    'stamina' => $career->final_stamina,
                    'power' => $career->final_power,
                    'guts' => $career->final_guts,
                    'wit' => $career->final_wit
                ],
                'training_sessions' => $career->training_sessions->toArray(),
                'skills' => $career->skills->toArray(),
                'races' => $career->races->toArray(),
                'created_at' => $career->created_at->toIso8601String()
            ];
        })->toArray();
    }
}
```

**Data Deletion Service**:

```php
// app/Services/Privacy/DataDeletionService.php
class DataDeletionService {
    public function deleteUserData(User $user): void {
        DB::transaction(function () use ($user) {
            // Delete user-generated content
            $user->careers()->delete();
            $user->characters()->delete();
            $user->preferences()->delete();
            
            // Anonymize activity logs (legal retention)
            ActivityLog::where('user_id', $user->id)
                ->update([
                    'user_id' => null,
                    'anonymized_at' => now()
                ]);
            
            // Delete user account
            $user->delete();
        });
        
        // Log deletion for compliance
        Log::info('User data deleted', [
            'user_id' => $user->id,
            'deleted_at' => now(),
            'reason' => 'user_request'
        ]);
    }
    
    public function cancelDeletion(AccountDeletionRequest $request): void {
        $request->update(['status' => 'cancelled']);
        
        Log::info('Account deletion cancelled', [
            'user_id' => $request->user_id,
            'cancelled_at' => now()
        ]);
    }
}
```

**Consent Management**:

```php
// app/Models/UserPreference.php
class UserPreference extends Model {
    protected $casts = [
        'sharing_settings' => 'array',
        'consent_given_at' => 'datetime'
    ];
    
    public function updateSharingSettings(array $settings): void {
        $this->sharing_settings = array_merge(
            $this->sharing_settings ?? [],
            $settings
        );
        
        $this->consent_given_at = now();
        $this->save();
        
        // Log consent change
        ActivityLog::create([
            'user_id' => $this->user_id,
            'action' => 'consent_updated',
            'details' => $settings
        ]);
    }
    
    public function canShare(string $dataType): bool {
        return $this->sharing_settings[$dataType] ?? false;
    }
}
```

**Implementation Components**:

- `app/Services/Privacy/DataExportService.php` - Data export
- `app/Services/Privacy/DataDeletionService.php` - Data deletion
- `app/Http/Controllers/PrivacyController.php` - Privacy dashboard
- `resources/views/privacy/dashboard.blade.php` - Privacy UI
- `database/migrations/*_create_data_export_requests_table.php` - Export tracking
- `tests/Feature/Privacy/PrivacyControlsTest.php` - Privacy tests

---

## 9. Data Models

### 9.1 Performance Metrics Tables

```sql
-- Cache performance metrics
CREATE TABLE cache_metrics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    metric_type ENUM('hit', 'miss', 'eviction') NOT NULL,
    cache_key VARCHAR(255) NOT NULL,
    tags JSON,
    recorded_at TIMESTAMP NOT NULL,
    INDEX idx_recorded_at (recorded_at),
    INDEX idx_metric_type (metric_type)
);

-- Performance benchmarks
CREATE TABLE performance_benchmarks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    avg DECIMAL(10, 2) NOT NULL,
    min DECIMAL(10, 2) NOT NULL,
    max DECIMAL(10, 2) NOT NULL,
    p50 DECIMAL(10, 2) NOT NULL,
    p95 DECIMAL(10, 2) NOT NULL,
    p99 DECIMAL(10, 2) NOT NULL,
    git_commit VARCHAR(40),
    php_version VARCHAR(20),
    recorded_at TIMESTAMP NOT NULL,
    INDEX idx_name_recorded (name, recorded_at)
);

-- APM metrics
CREATE TABLE apm_metrics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    value DECIMAL(10, 2) NOT NULL,
    tags JSON,
    recorded_at TIMESTAMP NOT NULL,
    INDEX idx_name_recorded (name, recorded_at)
);
```

### 9.2 PWA Tables

```sql
-- Push subscriptions
CREATE TABLE push_subscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    endpoint TEXT NOT NULL,
    public_key VARCHAR(255) NOT NULL,
    auth_token VARCHAR(255) NOT NULL,
    content_encoding VARCHAR(20) DEFAULT 'aes128gcm',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
);

-- PWA metrics
CREATE TABLE pwa_metrics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event VARCHAR(50) NOT NULL,
    user_agent TEXT,
    platform VARCHAR(50),
    display_mode VARCHAR(20),
    timestamp TIMESTAMP NOT NULL,
    INDEX idx_event_timestamp (event, timestamp)
);
```

### 9.3 Simulation Tables

```sql
-- Batch simulations
CREATE TABLE batch_simulations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    base_character JSON NOT NULL,
    scenarios_count INT NOT NULL,
    completed_scenarios INT DEFAULT 0,
    status ENUM('pending', 'running', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_status (user_id, status)
);

-- Simulation results
CREATE TABLE simulation_results (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    batch_id BIGINT UNSIGNED NOT NULL,
    scenario_index INT NOT NULL,
    final_stats JSON NOT NULL,
    win_rate DECIMAL(5, 2) NOT NULL,
    sp_efficiency DECIMAL(10, 2) NOT NULL,
    key_decisions JSON,
    bottlenecks JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (batch_id) REFERENCES batch_simulations(id) ON DELETE CASCADE,
    INDEX idx_batch_scenario (batch_id, scenario_index)
);
```

### 9.4 Privacy Tables

```sql
-- Data export requests
CREATE TABLE data_export_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    file_path VARCHAR(255),
    requested_at TIMESTAMP NOT NULL,
    completed_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_status (user_id, status)
);

-- Account deletion requests
CREATE TABLE account_deletion_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    scheduled_for TIMESTAMP NOT NULL,
    status ENUM('pending', 'cancelled', 'completed') DEFAULT 'pending',
    requested_at TIMESTAMP NOT NULL,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_scheduled_status (scheduled_for, status)
);

-- Share links
CREATE TABLE share_links (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    career_id BIGINT UNSIGNED NOT NULL,
    token VARCHAR(32) UNIQUE NOT NULL,
    privacy ENUM('public', 'unlisted', 'password') DEFAULT 'unlisted',
    password VARCHAR(255),
    view_count INT DEFAULT 0,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (career_id) REFERENCES careers(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_expires (expires_at)
);
```

---

## 10. API Design

### 10.1 Performance Monitoring API

```
GET /api/admin/apm/metrics
- Query parameters: name, from, to
- Response: Array of metric data points

GET /api/admin/cache/stats
- Response: Cache hit rate, memory usage, key count

POST /api/admin/cache/clear
- Body: { tags?: string[] }
- Response: Success message

GET /api/admin/performance/benchmarks
- Query parameters: name, from, to
- Response: Benchmark results with trends
```

### 10.2 PWA API

```
POST /api/push/subscribe
- Body: PushSubscription object
- Response: Subscription ID

POST /api/push/unsubscribe
- Response: Success message

GET /api/pwa/metrics
- Query parameters: event, from, to
- Response: PWA usage metrics
```

### 10.3 Simulation API

```
POST /api/simulations/batch
- Body: { base_character, scenarios[] }
- Response: Batch simulation ID

GET /api/simulations/batch/{id}
- Response: Batch status and results

GET /api/simulations/batch/{id}/report
- Response: Comparison report with recommendations
```

### 10.4 Privacy API

```
POST /api/privacy/export
- Response: Export request ID

GET /api/privacy/export/{id}
- Response: Export status and download link

POST /api/privacy/delete
- Body: { confirmation }
- Response: Deletion scheduled confirmation

POST /api/privacy/delete/cancel
- Response: Cancellation confirmation
```

---

## 11. Component Architecture

### 11.1 Accessible Component Library

```
resources/views/components/accessible/
├── button.blade.php
├── form-field.blade.php
├── live-region.blade.php
├── modal.blade.php
├── tabs.blade.php
├── dropdown.blade.php
└── skip-link.blade.php
```

### 11.2 Performance Components

```
app/Services/
├── Cache/
│   ├── CacheOptimizationService.php
│   ├── TieredCacheStrategy.php
│   └── CacheInvalidationService.php
├── Monitoring/
│   ├── APMService.php
│   └── PerformanceAlertService.php
└── Query/
    ├── QueryOptimizationService.php
    └── EagerLoadingService.php
```

### 11.3 PWA Components

```
public/
├── sw.js
├── manifest.json
└── offline.html

resources/js/
├── install-prompt.js
├── offline-storage.js
├── background-sync.js
└── push-notifications.js
```

---

## 12. Deployment Architecture

### 12.1 Production Environment

```
┌─────────────────────────────────────────┐
│          Load Balancer (Nginx)          │
└────────────┬────────────────────────────┘
             │
    ┌────────┴────────┐
    │                 │
┌───▼────┐      ┌────▼───┐
│  App   │      │  App   │
│Server 1│      │Server 2│
└───┬────┘      └────┬───┘
    │                │
    └────────┬───────┘
             │
    ┌────────▼────────┐
    │                 │
┌───▼────┐      ┌────▼───┐
│ MySQL  │      │ Redis  │
│Primary │      │ Cache  │
└───┬────┘      └────────┘
    │
┌───▼────┐
│ MySQL  │
│Replica │
└────────┘
```

### 12.2 CI/CD Pipeline

```yaml
# .github/workflows/deploy.yml
name: Deploy

on:
  push:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Run Tests
        run: php artisan test
      - name: Run Browser Tests
        run: npm run playwright:test
      - name: Run Benchmarks
        run: php artisan benchmark:run
  
  deploy:
    needs: test
    runs-on: ubuntu-latest
    steps:
      - name: Deploy to Production
        run: |
          php artisan down
          git pull
          composer install --no-dev
          npm run build
          php artisan migrate --force
          php artisan cache:clear
          php artisan config:cache
          php artisan route:cache
          php artisan view:cache
          php artisan up
```

---

## Document Control

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 2.1.0 | 2026-01-23 | Development Team | Complete design for v2.1.0 with performance, accessibility, PWA, analytics, and testing |

---

**End of Design Document**

*This Design Document provides the complete technical design for implementing the Umamusume Career Planner v2.1.0 requirements, with detailed architectural decisions, component designs, and implementation strategies.*
