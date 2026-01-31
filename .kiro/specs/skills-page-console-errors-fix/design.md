# Skills Page Console Errors Fix - Design

## 1. Overview

**Feature Name**: Skills Page Console Errors Fix  
**Version**: 1.0  
**Status**: Draft  
**Created**: 2026-01-31

### 1.1 Design Summary

This design addresses the critical missing API endpoint issue on the Skills Management page that prevents the AI Recommendations feature from functioning correctly.

### 1.2 Problem Statement

The Skills Management page JavaScript (`resources/js/pages/skills/index.js`) attempts to call `/api/characters/{characterId}/skill-recommendations` (POST), but this endpoint doesn't exist in the routing configuration. The actual endpoint is at `/api/skill-recommendations/recommendations` with a different path structure.

## 2. Architecture

### 2.1 Current State

**Existing Route** (`routes/api.php`, line 1196):

```php
Route::middleware('auth:sanctum')
    ->prefix('skill-recommendations')
    ->name('api.skill-recommendations.')
    ->group(function () {
        Route::post('/recommendations', [SkillRecommendationController::class, 'getRecommendations'])
            ->name('recommendations');
    });
```

**JavaScript Call** (`resources/js/pages/skills/index.js`, line 271):

```javascript
const response = await fetch(
    `/api/characters/${this.selectedCharacterId}/skill-recommendations`,
    { method: "POST", ... }
);
```

### 2.2 Proposed Solution

**Option 1: Add Character-Specific Route (RECOMMENDED)**

Add a new route that matches the JavaScript expectation while maintaining backward compatibility:

```php
// Character-specific skill recommendations route
Route::post('/characters/{characterId}/skill-recommendations', 
    [SkillRecommendationController::class, 'getRecommendations'])
    ->middleware('auth:sanctum')
    ->name('api.characters.skill-recommendations');
```

**Rationale**:

- Maintains consistency with other character-specific routes
- Doesn't break existing code
- Follows RESTful conventions
- Character ID is part of the URL path (more semantic)

**Option 2: Update JavaScript (ALTERNATIVE)**

Update the JavaScript to use the existing endpoint:

```javascript
const response = await fetch('/api/skill-recommendations/recommendations', {
    method: "POST",
    body: JSON.stringify({ character_id: this.selectedCharacterId }),
    ...
});
```

**Rationale**:

- No backend changes required
- Uses existing endpoint
- Character ID passed in request body

**Decision**: Implement **Option 1** because:

1. It's more RESTful and semantic
2. Consistent with other character-specific endpoints
3. Easier to understand and maintain
4. Matches developer expectations

## 3. Component Design

### 3.1 API Route Structure

**Location**: `routes/api.php`

**Placement**: Add after the existing character-specific routes (around line 220)

```php
// Character-specific skill routes
Route::prefix('characters/{characterId}')->name('api.characters.')->group(function () {
    // Existing routes...
    Route::get('/skill-evolution/opportunities', [SkillManagementController::class, 'evolutionOpportunities'])
        ->name('evolution-opportunities');
    
    Route::get('/agent-performance', [SkillManagementController::class, 'agentPerformance'])
        ->name('agent-performance');
    
    // NEW: Skill recommendations endpoint
    Route::post('/skill-recommendations', [SkillRecommendationController::class, 'getRecommendations'])
        ->middleware('auth:sanctum')
        ->name('skill-recommendations');
});
```

### 3.2 Controller Method

**Assumption**: The `SkillRecommendationController::getRecommendations()` method already exists and accepts character ID.

**Verification Needed**:

- Check if method signature accepts `$characterId` parameter
- Verify it returns the expected data structure:

  ```json
  {
    "data": {
      "recommendations": [...],
      "optimization": {...}
    }
  }
  ```

### 3.3 Data Flow

```
User clicks "Get AI Recommendations"
    ↓
JavaScript: skillManagement.getAIRecommendations()
    ↓
POST /api/characters/{characterId}/skill-recommendations
    ↓
SkillRecommendationController::getRecommendations($characterId)
    ↓
Returns JSON with recommendations array
    ↓
JavaScript updates: this.recommendations, this.aiOptimization
    ↓
UI displays recommendations
```

## 4. Error Handling

### 4.1 HTTP Status Codes

| Status | Scenario | Response |
|--------|----------|----------|
| 200 | Success | `{ "data": { "recommendations": [...] } }` |
| 401 | Unauthorized | `{ "message": "Unauthenticated" }` |
| 404 | Character not found | `{ "message": "Character not found" }` |
| 422 | Validation error | `{ "message": "...", "errors": {...} }` |
| 500 | Server error | `{ "message": "Failed to generate recommendations" }` |

### 4.2 JavaScript Error Handling

Current implementation (already exists):

```javascript
try {
    const response = await fetch(...);
    if (!response.ok) throw new Error("Failed to get AI recommendations");
    const data = await response.json();
    this.recommendations = data.data.recommendations || [];
    this.aiOptimization = data.data;
    this.showSuccess("AI recommendations loaded successfully");
} catch (error) {
    console.error("Error getting AI recommendations:", error);
    this.showError("Failed to get AI recommendations");
} finally {
    this.loading = false;
}
```

**Enhancement**: Add more specific error messages based on status code (optional):

```javascript
if (!response.ok) {
    if (response.status === 404) {
        throw new Error("Character not found");
    } else if (response.status === 401) {
        throw new Error("Please log in to get recommendations");
    }
    throw new Error("Failed to get AI recommendations");
}
```

## 5. Testing Strategy

### 5.1 Unit Tests

**Test File**: `tests/Feature/Api/SkillRecommendationTest.php`

```php
public function test_can_get_skill_recommendations_for_character()
{
    $user = User::factory()->create();
    $character = Character::factory()->create(['user_id' => $user->id]);
    
    $response = $this->actingAs($user)
        ->postJson("/api/characters/{$character->id}/skill-recommendations");
    
    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'recommendations',
                'optimization'
            ]
        ]);
}

public function test_cannot_get_recommendations_for_other_users_character()
{
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $character = Character::factory()->create(['user_id' => $user2->id]);
    
    $response = $this->actingAs($user1)
        ->postJson("/api/characters/{$character->id}/skill-recommendations");
    
    $response->assertForbidden();
}

public function test_requires_authentication()
{
    $character = Character::factory()->create();
    
    $response = $this->postJson("/api/characters/{$character->id}/skill-recommendations");
    
    $response->assertUnauthorized();
}
```

### 5.2 Browser Testing

**Manual Test Steps**:

1. Navigate to <http://127.0.0.1:8000/skills>
2. Select a character from dropdown
3. Open browser DevTools Console
4. Click "Get AI Recommendations" button (if visible)
5. Verify:
   - No 404 error in Network tab
   - No console errors
   - Recommendations display (if feature is implemented)
   - Success toast appears

### 5.3 Integration Testing

**Test Scenarios**:

- [ ] Endpoint returns 200 with valid character ID
- [ ] Endpoint returns 404 with invalid character ID
- [ ] Endpoint returns 401 without authentication
- [ ] Endpoint returns 403 for unauthorized character access
- [ ] Response matches expected JSON structure
- [ ] JavaScript correctly parses and displays recommendations

## 6. Security Considerations

### 6.1 Authorization

**Requirement**: Users should only access recommendations for their own characters.

**Implementation**: Add policy check in controller:

```php
public function getRecommendations($characterId)
{
    $character = Character::findOrFail($characterId);
    $this->authorize('view', $character);
    
    // ... rest of implementation
}
```

### 6.2 Rate Limiting

**Consideration**: AI recommendations may be expensive operations.

**Recommendation**: Apply rate limiting:

```php
Route::post('/skill-recommendations', [SkillRecommendationController::class, 'getRecommendations'])
    ->middleware(['auth:sanctum', 'throttle:10,1']) // 10 requests per minute
    ->name('skill-recommendations');
```

## 7. Performance Considerations

### 7.1 Caching

**Consideration**: Recommendations may be cacheable for short periods.

**Implementation** (optional):

```php
public function getRecommendations($characterId)
{
    $cacheKey = "skill_recommendations_{$characterId}";
    
    return Cache::remember($cacheKey, 300, function () use ($characterId) {
        // Generate recommendations
        return $this->generateRecommendations($characterId);
    });
}
```

### 7.2 Async Processing

**Consideration**: If recommendations take >2 seconds, consider async processing.

**Future Enhancement**: Use queued jobs for complex recommendations.

## 8. Rollout Plan

### 8.1 Deployment Steps

1. **Pre-deployment**:
   - Verify `SkillRecommendationController` exists
   - Verify `getRecommendations()` method exists
   - Run tests locally

2. **Deployment**:
   - Add route to `routes/api.php`
   - Clear route cache: `php artisan route:clear`
   - Verify route exists: `php artisan route:list | grep skill-recommendations`

3. **Post-deployment**:
   - Test endpoint with Postman/curl
   - Test in browser
   - Monitor logs for errors

### 8.2 Rollback Plan

If issues occur:

1. Remove the new route from `routes/api.php`
2. Clear route cache: `php artisan route:clear`
3. Investigate and fix issues
4. Redeploy

## 9. Documentation Updates

### 9.1 API Documentation

Add to API documentation:

```markdown
### POST /api/characters/{characterId}/skill-recommendations

Get AI-powered skill recommendations for a character.

**Authentication**: Required (Sanctum)

**Parameters**:
- `characterId` (path, required): The character ID

**Response** (200 OK):
```json
{
  "data": {
    "recommendations": [
      {
        "skill_id": 1,
        "skill_name": "Speed Star",
        "priority": "high",
        "reason": "Matches character's running style",
        "final_cost": 120
      }
    ],
    "optimization": {
      "total_sp_required": 500,
      "sp_savings": 80
    }
  }
}
```

**Errors**:

- 401: Unauthorized
- 403: Forbidden (not your character)
- 404: Character not found

```

### 9.2 Code Comments

Add comment in `routes/api.php`:
```php
// Character-specific skill recommendations
// Used by Skills Management page (/skills) for AI-powered recommendations
Route::post('/skill-recommendations', ...)
```

## 10. Correctness Properties

### Property 1: Route Accessibility

**Property**: The endpoint `/api/characters/{characterId}/skill-recommendations` must return a 200 status code for authenticated users with valid character IDs.

**Test**:

```php
it('returns 200 for valid authenticated request', function () {
    $user = User::factory()->create();
    $character = Character::factory()->create(['user_id' => $user->id]);
    
    $response = $this->actingAs($user)
        ->postJson("/api/characters/{$character->id}/skill-recommendations");
    
    expect($response->status())->toBe(200);
});
```

### Property 2: Authorization Enforcement

**Property**: Users must not be able to access recommendations for characters they don't own.

**Test**:

```php
it('prevents access to other users characters', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $character = Character::factory()->create(['user_id' => $user2->id]);
    
    $response = $this->actingAs($user1)
        ->postJson("/api/characters/{$character->id}/skill-recommendations");
    
    expect($response->status())->toBe(403);
});
```

### Property 3: Response Structure Consistency

**Property**: All successful responses must contain a `data` object with `recommendations` array.

**Test**:

```php
it('returns consistent response structure', function () {
    $user = User::factory()->create();
    $character = Character::factory()->create(['user_id' => $user->id]);
    
    $response = $this->actingAs($user)
        ->postJson("/api/characters/{$character->id}/skill-recommendations");
    
    expect($response->json())->toHaveKeys(['data']);
    expect($response->json('data'))->toHaveKey('recommendations');
    expect($response->json('data.recommendations'))->toBeArray();
});
```

## 11. Acceptance Criteria Validation

| Requirement | Design Addresses | Validation Method |
|-------------|------------------|-------------------|
| REQ-3.1.3: Identify API endpoint errors | ✅ Identified missing endpoint | Context-gatherer report |
| REQ-3.2.3: Fix API endpoint errors | ✅ Add missing route | Route implementation |
| REQ-3.3.1: Verify page loads without errors | ✅ Endpoint will resolve 404 | Browser testing |
| REQ-3.3.4: Verify API calls succeed | ✅ Endpoint returns 200 | Integration tests |

## 12. Open Questions

1. **Q**: Does `SkillRecommendationController::getRecommendations()` accept `$characterId` parameter?
   **A**: Need to verify controller method signature

2. **Q**: Should we add rate limiting to prevent abuse?
   **A**: Recommended - add `throttle:10,1` middleware

3. **Q**: Should recommendations be cached?
   **A**: Optional enhancement - cache for 5 minutes

4. **Q**: Are there any other missing endpoints?
   **A**: No - context-gatherer verified all other endpoints exist

## 13. Future Enhancements

1. **Streaming Recommendations**: Use Server-Sent Events for real-time updates
2. **Recommendation History**: Track and display past recommendations
3. **Recommendation Feedback**: Allow users to rate recommendation quality
4. **Batch Recommendations**: Get recommendations for multiple characters

---

**Document Version**: 1.0  
**Last Updated**: 2026-01-31  
**Status**: Draft - Ready for Implementation
