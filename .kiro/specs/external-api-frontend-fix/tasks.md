# External API Frontend Fix - Implementation Tasks

**Feature Name**: external-api-frontend-fix  
**Version**: 1.0.0  
**Date**: January 29, 2026  
**Status**: Draft

## Task Overview

This task list implements the fix for the External Data Browser frontend to use correct API endpoints.

## Task Status Legend

- `[ ]` - Not started
- `[~]` - Queued
- `[-]` - In progress
- `[x]` - Completed
- `[ ]*` - Optional task

## Phase 1: Core Functionality

### 1.1 Update API Endpoint Calls

- [x] 1.1.1 Replace `/api/external-data/all` with individual endpoint calls
  - [x] 1.1.1.1 Update `loadData()` method to use `Promise.all()`
  - [x] 1.1.1.2 Call `/api/external/characters` endpoint
  - [x] 1.1.1.3 Call `/api/external/support-cards` endpoint
  - [x] 1.1.1.4 Call `/api/external/skills` endpoint
  - [x] 1.1.1.5 Call `/api/external/news` endpoint

### 1.2 Implement Helper Methods

- [x] 1.2.1 Create `fetchEndpoint()` helper method
  - [x] 1.2.1.1 Add proper error handling
  - [x] 1.2.1.2 Add CSRF token handling
  - [x] 1.2.1.3 Add response validation
  - [x] 1.2.1.4 Return standardized response format

### 1.3 Update Data Aggregation

- [x] 1.3.1 Process individual endpoint responses
  - [x] 1.3.1.1 Handle characters response
  - [x] 1.3.1.2 Handle support cards response
  - [x] 1.3.1.3 Handle skills response
  - [x] 1.3.1.4 Handle news response

- [x] 1.3.2 Update component state with aggregated data
  - [x] 1.3.2.1 Set `this.characters` array
  - [x] 1.3.2.2 Set `this.supportCards` array
  - [x] 1.3.2.3 Set `this.skills` array
  - [x] 1.3.2.4 Set `this.news` array

## Phase 2: Error Handling

### 2.1 Add Error State Tracking

- [x] 2.1.1 Add `errors` object to component state
  - [x] 2.1.1.1 Add `errors.characters` property
  - [x] 2.1.1.2 Add `errors.supportCards` property
  - [x] 2.1.1.3 Add `errors.skills` property
  - [x] 2.1.1.4 Add `errors.news` property

### 2.2 Implement Error Handling Logic

- [x] 2.2.1 Capture individual endpoint errors
  - [x] 2.2.1.1 Store error messages in state
  - [x] 2.2.1.2 Log errors to console
  - [x] 2.2.1.3 Maintain partial data on failure

- [x] 2.2.2 Determine API availability
  - [x] 2.2.2.1 Set `apiAvailable = true` if any endpoint succeeds
  - [x] 2.2.2.2 Set `apiAvailable = false` if all endpoints fail

### 2.3 Add Retry Functionality

- [x] 2.3.1 Create `retryEndpoint()` method
  - [x] 2.3.1.1 Accept endpoint name as parameter
  - [x] 2.3.1.2 Retry specific failed endpoint
  - [x] 2.3.1.3 Update state on success/failure

- [x] 2.3.2 Create `retryAll()` method
  - [x] 2.3.2.1 Call `loadData()` again
  - [x] 2.3.2.2 Clear previous errors

## Phase 3: UI/UX Enhancements

### 3.1 Update Blade Template

- [x] 3.1.1 Add error message display sections
  - [x] 3.1.1.1 Add error banner for characters
  - [x] 3.1.1.2 Add error banner for support cards
  - [x] 3.1.1.3 Add error banner for skills
  - [x] 3.1.1.4 Add error banner for news

- [x] 3.1.2 Add retry buttons
  - [x] 3.1.2.1 Add retry button for each section
  - [x] 3.1.2.2 Add "Retry All" button
  - [x] 3.1.2.3 Wire up click handlers

### 3.2 Add Loading Indicators

- [x] 3.2.1 Add section-specific loading states
  - [x] 3.2.1.1 Add `loadingCharacters` property
  - [x] 3.2.1.2 Add `loadingSupportCards` property
  - [x] 3.2.1.3 Add `loadingSkills` property
  - [x] 3.2.1.4 Add `loadingNews` property

- [x] 3.2.2 Update UI to show loading spinners
  - [x] 3.2.2.1 Show spinner during initial load
  - [x] 3.2.2.2 Show spinner during retry

### 3.3 Add Data Source Indicators

- [x] 3.3.1 Add cached data badges

  - [x] 3.3.1.1 Show "Cached" badge when data is from cache

  - [x] 3.3.1.2 Show "Offline" badge when using database cache

  - [x] 3.3.1.3 Show "Live" badge when data is fresh

## Phase 4: Testing

### 4.1 Manual Testing

- [x] 4.1.1 Test successful data loading
  - [x] 4.1.1.1 Verify all four data types display
  - [x] 4.1.1.2 Verify no console errors
  - [x] 4.1.1.3 Verify tab switching works

- [x] 4.1.2 Test error scenarios
  - [x] 4.1.2.1 Test with API unavailable
  - [x] 4.1.2.2 Test with partial endpoint failures
  - [x] 4.1.2.3 Test with network timeout

- [x] 4.1.3 Test retry functionality
  - [x] 4.1.3.1 Test individual endpoint retry
  - [x] 4.1.3.2 Test "Retry All" button
  - [x] 4.1.3.3 Verify state updates correctly

- [x] 4.1.4 Test existing features
  - [x] 4.1.4.1 Test search functionality
  - [x] 4.1.4.2 Test filter functionality
  - [x] 4.1.4.3 Test sort functionality
  - [x] 4.1.4.4 Test pagination (if applicable)

### 4.2 Browser Testing

- [x] 4.2.1 Test in Chrome/Edge
- [ ] 4.2.2 Test in Firefox
- [ ] 4.2.3 Test in Safari

### 4.3 Performance Testing

- [x] 4.3.1 Measure page load time
  - [x] 4.3.1.1 Verify < 2 seconds target
  - [x] 4.3.1.2 Check Core Web Vitals

- [x] 4.3.2 Measure API response times
  - [x] 4.3.2.1 Verify cached responses < 1 second
  - [x] 4.3.2.2 Verify fresh responses < 3 seconds

### 4.4 Unit Tests (Property-Based)

- [-] 4.4.1 Write property test for data completeness
  - **Validates: Requirements 1.1**
  - Test that all data types are populated when all endpoints succeed

- [-] 4.4.2 Write property test for partial failure resilience
  - **Validates: Requirements 2.1, 2.2**
  - Test that successful data displays even when some endpoints fail

- [-] 4.4.3 Write property test for error state consistency
  - **Validates: Requirements 3.1**
  - Test that error states accurately reflect endpoint failures

- [-] 4.4.4 Write property test for loading state correctness
  - **Validates: Requirements 1.1**
  - Test that loading state is managed correctly during async operations

- [ ] 4.4.5 Write property test for API availability determination
  - **Validates: Requirements 2.2**
  - Test that API availability is determined correctly based on endpoint responses

## Phase 5: Documentation & Cleanup

### 5.1 Update Documentation

- [x] 5.1.1 Update `FRONTEND_INTEGRATION_SUMMARY.md`
  - [x] 5.1.1.1 Document new API call pattern
  - [x] 5.1.1.2 Document error handling approach

- [x] 5.1.2 Update `FINAL_STATUS.md`
  - [x] 5.1.2.1 Update status to reflect fix
  - [x] 5.1.2.2 Add testing results

- [x] 5.1.3 Update inline code comments
  - [x] 5.1.3.1 Add JSDoc comments to methods
  - [x] 5.1.3.2 Document error handling logic

### 5.2 Code Cleanup

- [x] 5.2.1 Remove unused code
  - [x] 5.2.1.1 Remove old `/api/external-data/all` references
  - [x] 5.2.1.2 Remove unused helper methods

- [x] 5.2.2 Format code
  - [x] 5.2.2.1 Run Prettier on JavaScript files
  - [x] 5.2.2.2 Run Pint on PHP files (if any changes)

### 5.3 Build & Deploy

- [x] 5.3.1 Build assets
  - [x] 5.3.1.1 Run `npm run build`
  - [x] 5.3.1.2 Verify no build errors

- [x] 5.3.2 Clear caches
  - [x] 5.3.2.1 Clear Laravel cache
  - [x] 5.3.2.2 Clear browser cache
  - [x] 5.3.2.3 Clear Redis cache (if applicable)

- [x] 5.3.3 Verify deployment
  - [x] 5.3.3.1 Test in production-like environment
  - [x] 5.3.3.2 Verify all functionality works

## Task Dependencies

```
1.1 → 1.2 → 1.3 → 2.1 → 2.2 → 2.3 → 3.1 → 3.2 → 4.1 → 4.2 → 4.3 → 4.4 → 5.1 → 5.2 → 5.3
```

## Estimated Effort

- Phase 1: 2-3 hours
- Phase 2: 1-2 hours
- Phase 3: 2-3 hours
- Phase 4: 2-3 hours
- Phase 5: 1 hour

**Total**: 8-12 hours

## Risk Assessment

| Task | Risk Level | Mitigation |
|------|-----------|------------|
| 1.1 | Low | Well-defined API endpoints |
| 2.2 | Medium | Thorough testing of error scenarios |
| 3.1 | Low | Minimal UI changes |
| 4.4 | Medium | Use existing test patterns |

## Success Criteria

- [ ] All tasks marked as complete
- [ ] All tests passing
- [ ] No console errors
- [ ] Performance targets met
- [ ] Documentation updated
- [ ] Code reviewed and approved

## Notes

- Maintain backward compatibility with existing UI/UX
- Follow Alpine.js best practices
- Use existing error handling patterns
- Preserve all existing functionality
- Test thoroughly before marking complete

## References

- Requirements: `.kiro/specs/external-api-frontend-fix/requirements.md`
- Design: `.kiro/specs/external-api-frontend-fix/design.md`
- Backend API: `app/Http/Controllers/Api/ExternalDataController.php`
- Frontend Component: `resources/js/pages/external-data/browse.js`
- Blade Template: `resources/views/external-data/browse.blade.php`
