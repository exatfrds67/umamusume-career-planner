# External API Frontend Fix - Design Document

**Feature Name**: external-api-frontend-fix  
**Version**: 1.0.0  
**Date**: January 29, 2026  
**Status**: Draft

## Architecture Overview

### Current Architecture (Broken)

```
Frontend (browse.js)
    ↓
    fetch('/api/external-data/all')  ❌ Route doesn't exist
    ↓
    404 Error
```

### Proposed Architecture (Fixed)

```
Frontend (browse.js)
    ↓
    Promise.all([
        fetch('/api/external/characters'),
        fetch('/api/external/support-cards'),
        fetch('/api/external/skills'),
        fetch('/api/external/news')
    ])
    ↓
    Aggregate responses
    ↓
    Update component state
```

## Component Design

### Alpine.js Component Structure

The `externalDataBrowser()` component will be refactored to:

1. **Initialization**:
   - Call `loadData()` on mount
   - Set up error handling
   - Initialize loading states

2. **Data Loading**:
   - Make parallel API calls using `Promise.all()`
   - Handle individual endpoint failures gracefully
   - Aggregate successful responses
   - Update component state

3. **Error Handling**:
   - Track which endpoints failed
   - Display appropriate error messages
   - Provide retry functionality
   - Maintain partial data display

### API Call Strategy

```javascript
async loadData() {
    this.loading = true;
    this.errors = {
        characters: null,
        supportCards: null,
        skills: null,
        news: null
    };
    
    try {
        const [charactersRes, supportCardsRes, skillsRes, newsRes] = await Promise.all([
            this.fetchEndpoint('/api/external/characters'),
            this.fetchEndpoint('/api/external/support-cards'),
            this.fetchEndpoint('/api/external/skills'),
            this.fetchEndpoint('/api/external/news')
        ]);
        
        // Process each response
        if (charactersRes.success) {
            this.characters = charactersRes.data || [];
        } else {
            this.errors.characters = charactersRes.error;
        }
        
        // ... similar for other endpoints
        
        // Determine overall API availability
        this.apiAvailable = charactersRes.success || supportCardsRes.success;
        
        // Filter data after loading
        this.filterData();
    } catch (error) {
        console.error('Error loading data:', error);
        this.apiAvailable = false;
    } finally {
        this.loading = false;
    }
}
```

### Helper Method: fetchEndpoint

```javascript
async fetchEndpoint(url) {
    try {
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        });
        
        if (!response.ok) {
            return {
                success: false,
                error: `HTTP ${response.status}: ${response.statusText}`,
                data: null
            };
        }
        
        const data = await response.json();
        return data;
    } catch (error) {
        return {
            success: false,
            error: error.message,
            data: null
        };
    }
}
```

## Data Flow

### 1. Initial Load

```
User navigates to /external-data/browse
    ↓
Alpine.js component initializes
    ↓
init() calls loadData()
    ↓
Parallel API calls to 4 endpoints
    ↓
Responses aggregated
    ↓
Component state updated
    ↓
UI renders with data
```

### 2. Error Handling Flow

```
API call fails
    ↓
Error captured in try-catch
    ↓
Error stored in component state
    ↓
Partial data still displayed
    ↓
Error message shown to user
    ↓
Retry button available
```

### 3. Retry Flow

```
User clicks retry button
    ↓
loadData() called again
    ↓
Failed endpoints retried
    ↓
Success: data displayed
    ↓
Failure: error persists
```

## State Management

### Component State Properties

```javascript
{
    // Loading states
    loading: false,
    apiAvailable: true,
    
    // Error tracking
    errors: {
        characters: null,
        supportCards: null,
        skills: null,
        news: null
    },
    
    // Data arrays
    characters: [],
    supportCards: [],
    skills: [],
    news: [],
    
    // Filtered data
    filteredCharacters: [],
    filteredSupportCards: [],
    filteredSkills: [],
    
    // UI state
    activeTab: 'characters',
    searchTerm: '',
    sortBy: 'id-asc',
    filters: {
        category: '',
        rarity: [],
        importStatus: [],
        skillRarity: [],
        skillType: ''
    }
}
```

## API Response Handling

### Expected Response Format

Each endpoint returns:

```json
{
    "success": true,
    "data": [...],
    "source": "umapyoi.net",
    "cached": false,
    "message": "Fresh data from external API"
}
```

### Error Response Format

```json
{
    "success": false,
    "message": "Error message",
    "error": "Detailed error"
}
```

### Response Validation

```javascript
function isValidResponse(response) {
    return response 
        && typeof response === 'object'
        && 'success' in response
        && 'data' in response;
}
```

## UI/UX Considerations

### Loading States

1. **Initial Load**: Show loading spinner for entire page
2. **Retry**: Show loading spinner for specific section
3. **Background Refresh**: Show subtle indicator

### Error Display

1. **Partial Failure**: Show error banner for failed endpoint, display successful data
2. **Complete Failure**: Show error message with retry button
3. **Network Error**: Show offline indicator

### User Feedback

1. **Success**: Data displays normally
2. **Partial Success**: Warning banner + partial data
3. **Failure**: Error message + retry button
4. **Cached Data**: Info badge showing "Cached" or "Offline"

## Performance Considerations

### Optimization Strategies

1. **Parallel Requests**: Use `Promise.all()` for concurrent API calls
2. **Request Timeout**: Set reasonable timeout (5 seconds)
3. **Caching**: Leverage browser cache and backend cache
4. **Debouncing**: Debounce search/filter operations
5. **Lazy Loading**: Consider pagination for large datasets

### Performance Targets

- Initial page load: < 2 seconds
- API response time: < 1 second (cached)
- API response time: < 3 seconds (fresh)
- Filter/search response: < 100ms

## Error Handling Strategy

### Error Categories

1. **Network Errors**: Connection timeout, DNS failure
2. **HTTP Errors**: 404, 500, 503
3. **API Errors**: Invalid response format, missing data
4. **Validation Errors**: Unexpected data structure

### Error Recovery

1. **Automatic Retry**: For transient network errors (with exponential backoff)
2. **Manual Retry**: User-initiated retry button
3. **Fallback Data**: Use cached data if available
4. **Graceful Degradation**: Show partial data when possible

## Testing Strategy

### Unit Tests

1. Test `fetchEndpoint()` with mock responses
2. Test `loadData()` with various response scenarios
3. Test error handling logic
4. Test data aggregation logic

### Integration Tests

1. Test with real API endpoints (development)
2. Test with mocked API responses
3. Test error scenarios (network failure, 404, 500)
4. Test partial failure scenarios

### Manual Testing Checklist

- [ ] Page loads successfully
- [ ] All four data types display
- [ ] Search functionality works
- [ ] Filter functionality works
- [ ] Sort functionality works
- [ ] Tab switching works
- [ ] Error messages display correctly
- [ ] Retry button works
- [ ] Cached data indicator shows
- [ ] Offline mode works

## Implementation Plan

### Phase 1: Core Functionality (Priority: High)

1. Update `loadData()` method to call individual endpoints
2. Implement `fetchEndpoint()` helper method
3. Update error handling logic
4. Test basic functionality

### Phase 2: Error Handling (Priority: High)

1. Add error state tracking
2. Implement retry functionality
3. Add error message display
4. Test error scenarios

### Phase 3: UI/UX Polish (Priority: Medium)

1. Add loading indicators
2. Add cached data badges
3. Improve error messages
4. Add retry buttons

### Phase 4: Testing & Documentation (Priority: Medium)

1. Write unit tests
2. Write integration tests
3. Update documentation
4. Perform manual testing

## Rollback Plan

If issues arise:

1. Revert `resources/js/pages/external-data/browse.js` to previous version
2. Clear browser cache
3. Rebuild assets with `npm run build`
4. Verify page loads with old code

## Documentation Updates

Files to update:

1. `docs/external-api-integration/FRONTEND_INTEGRATION_SUMMARY.md`
2. `docs/external-api-integration/FINAL_STATUS.md`
3. `README.md` (if applicable)

## Security Considerations

1. **CSRF Protection**: Maintain CSRF token in all requests
2. **Input Validation**: Validate all API responses
3. **XSS Prevention**: Sanitize data before display (Blade handles this)
4. **Rate Limiting**: Respect backend rate limits

## Accessibility Considerations

1. **Loading States**: Announce loading states to screen readers
2. **Error Messages**: Ensure error messages are accessible
3. **Retry Buttons**: Keyboard accessible
4. **Focus Management**: Maintain focus during state changes

## Browser Compatibility

Target browsers:

- Chrome/Edge: Latest 2 versions
- Firefox: Latest 2 versions
- Safari: Latest 2 versions

## Dependencies

- Alpine.js v3
- Fetch API (native browser support)
- Existing backend API endpoints
- CSRF token meta tag

## Risks and Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| API endpoint changes | High | Use constants for endpoint URLs |
| Network failures | Medium | Implement retry logic and caching |
| Browser compatibility | Low | Use standard Fetch API |
| Performance degradation | Medium | Use parallel requests and caching |

## Success Criteria

- [ ] External data browser loads without errors
- [ ] All four data types display correctly
- [ ] Error handling works for partial failures
- [ ] Retry functionality works
- [ ] No regression in existing features
- [ ] Performance targets met
- [ ] All tests pass

## Correctness Properties

### Property 1: Data Completeness

**Description**: When all API endpoints succeed, all four data types must be populated.

**Formal Specification**:

```
∀ successful_responses: 
  (characters_success ∧ support_cards_success ∧ skills_success ∧ news_success) 
  ⟹ 
  (characters.length > 0 ∧ supportCards.length > 0 ∧ skills.length > 0 ∧ news.length > 0)
```

**Test Strategy**: Property-based test with mocked successful responses

### Property 2: Partial Failure Resilience

**Description**: When some endpoints fail, successful data must still be displayed.

**Formal Specification**:

```
∀ mixed_responses:
  (∃ endpoint: endpoint_success) 
  ⟹ 
  (corresponding_data.length > 0 ∧ apiAvailable = true)
```

**Test Strategy**: Property-based test with various failure combinations

### Property 3: Error State Consistency

**Description**: Error states must accurately reflect endpoint failures.

**Formal Specification**:

```
∀ endpoint_response:
  (endpoint_response.success = false) 
  ⟹ 
  (errors[endpoint] ≠ null ∧ errors[endpoint].length > 0)
```

**Test Strategy**: Property-based test with error responses

### Property 4: Loading State Correctness

**Description**: Loading state must be true during API calls and false after completion.

**Formal Specification**:

```
∀ loadData_call:
  (loadData_start ⟹ loading = true) ∧
  (loadData_complete ⟹ loading = false)
```

**Test Strategy**: Unit test with async state tracking

### Property 5: API Availability Determination

**Description**: API is considered available if at least one endpoint succeeds.

**Formal Specification**:

```
∀ responses:
  apiAvailable = (∃ endpoint: endpoint.success = true)
```

**Test Strategy**: Property-based test with various success/failure combinations

## References

- Requirements: `.kiro/specs/external-api-frontend-fix/requirements.md`
- Backend API: `app/Http/Controllers/Api/ExternalDataController.php`
- API Routes: `routes/api.php`
- Current Implementation: `resources/js/pages/external-data/browse.js`
- Documentation: `docs/external-api-integration/`
