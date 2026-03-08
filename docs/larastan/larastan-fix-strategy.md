# Larastan Level 9 Fix Strategy

## Current Status

- Total errors: 4422
- Focus: app/Services directory (primary source of errors)

## Common Error Patterns

### 1. Mixed Type Casting (Most Common)

**Problem:** `Cannot cast mixed to int/float`
**Solution:** Add validation before casting

```php
// ❌ Bad
$value = (int) $mixedValue;

// ✅ Good
$value = is_numeric($mixedValue) ? (int) $mixedValue : 0;
```text

### 2. Array Shape Return Types

**Problem:** `Method should return array{...} but returns mixed`
**Solution:** Validate and cast array results from Cache/Config

```php
// ❌ Bad
return Cache::remember('key', 60, fn() => [...]);

// ✅ Good
$result = Cache::remember('key', 60, fn() => [...]);
return is_array($result) ? $result : ['default' => 'value'];
```text

### 3. Array Offset Access on Mixed

**Problem:** `Cannot access offset 'key' on mixed`
**Solution:** Validate array before access

```php
// ❌ Bad
$value = $data['key'];

// ✅ Good
$value = is_array($data) && isset($data['key']) ? $data['key'] : null;
```text

### 4. Binary Operations on Mixed

**Problem:** `Binary operation "+=" between mixed and int results in an error`
**Solution:** Initialize with proper types

```php
// ❌ Bad
$stats['count']++;

// ✅ Good
$stats['count'] = ($stats['count'] ?? 0) + 1;
```

### 5. Undefined Model Properties

**Problem:** `Access to an undefined property Model::$property`
**Solution:** Add @property PHPDoc to model or use getAttribute()

```php
// Option 1: Add to model
/**
 * @property string $ai_model_used
 */
class AIConversation extends Model { }

// Option 2: Use getAttribute
$model->getAttribute('ai_model_used')
```text

## Priority Files (Most Errors)

1. AI/AIDashboardService.php - 40+ errors
2. AI/AIPerformanceMonitor.php - 15+ errors
3. MCP/AgentOrchestrationService.php
4. MCP/AgentRoutingService.php
5. ExternalAPI services
6. Training calculation services

## Implementation Plan

### Phase 1: Fix Top 10 Service Files

- Focus on files with 10+ errors each
- Apply systematic fixes for all error patterns
- Run PHPStan after each file to verify

### Phase 2: Fix Remaining Services

- Batch fix similar errors across remaining files
- Use search/replace for common patterns

### Phase 3: Fix Models

- Add missing @property annotations
- Fix relationship return types
- Add proper casts

### Phase 4: Verify and Clean Up

- Run full PHPStan analysis
- Verify error count reduction
- Update ignore rules if needed

## Target

- Reduce from 4422 to under 100 errors
- Focus on application code quality
- Maintain test coverage

