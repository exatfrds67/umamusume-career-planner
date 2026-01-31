# External API Frontend Fix - Requirements

**Feature Name**: external-api-frontend-fix  
**Version**: 1.0.0  
**Date**: January 29, 2026  
**Status**: Draft

## Overview

Fix the External Data Browser frontend to use the correct API endpoints that actually exist in the backend. The current implementation calls a non-existent `/api/external-data/all` endpoint, which needs to be replaced with individual calls to the existing endpoints.

## Background

The External API Integration was implemented and marked as "FULLY OPERATIONAL" on January 25, 2026. However, there's a mismatch between the frontend and backend:

- **Frontend calls**: `/api/external-data/all` (doesn't exist)
- **Backend provides**: `/api/external/characters`, `/api/external/support-cards`, `/api/external/skills`, `/api/external/news`

This mismatch was discovered during code review when investigating the external API integration status.

## Requirements

### 1. Frontend API Endpoint Correction

**Priority**: High  
**Acceptance Criteria**:

- Frontend must call the correct individual API endpoints
- All four data types must be fetched: characters, support cards, skills, news
- API calls should be made in parallel for performance
- Loading states should be handled properly during data fetching
- Error handling should work for individual endpoint failures

### 2. Data Aggregation Logic

**Priority**: High  
**Acceptance Criteria**:

- Frontend must aggregate data from multiple endpoints
- Each endpoint response should be validated before use
- Partial failures should be handled gracefully (e.g., if news fails, still show characters)
- API availability status should be determined based on endpoint responses

### 3. Error Handling and User Feedback

**Priority**: Medium  
**Acceptance Criteria**:

- Show appropriate error messages for failed endpoints
- Display which data sources are unavailable
- Provide retry functionality for failed requests
- Maintain existing offline/cache fallback behavior

### 4. Performance Optimization

**Priority**: Medium  
**Acceptance Criteria**:

- Use `Promise.all()` for parallel API calls
- Implement proper timeout handling
- Cache responses appropriately
- Minimize unnecessary re-fetches

### 5. Backward Compatibility

**Priority**: Low  
**Acceptance Criteria**:

- Maintain existing UI/UX behavior
- Keep all existing filtering and sorting functionality
- Preserve tab switching behavior
- No breaking changes to component interface

## User Stories

### US-1: As a user, I want to browse external data successfully

**Given** I navigate to `/external-data/browse`  
**When** the page loads  
**Then** I should see characters, support cards, skills, and news data from umapyoi.net

### US-2: As a user, I want to see partial data when some endpoints fail

**Given** I am on the external data browser  
**When** one API endpoint fails but others succeed  
**Then** I should see the successful data and an error message for the failed endpoint

### US-3: As a user, I want to retry failed data fetches

**Given** an API endpoint failed to load  
**When** I click the retry button  
**Then** the system should attempt to fetch the data again

## Technical Constraints

1. Must use existing backend API endpoints (no backend changes)
2. Must maintain Alpine.js component architecture
3. Must preserve existing error handling patterns
4. Must work with existing MCP fetch integration
5. Must maintain CSRF token handling

## Out of Scope

- Backend API changes
- New API endpoints
- Database schema changes
- MCP server configuration changes
- Authentication/authorization changes

## Dependencies

- Existing backend API endpoints must be functional
- MCP fetch server must be available
- umapyoi.net API must be accessible (or cache must be available)

## Success Metrics

- External data browser loads without console errors
- All four data types display correctly
- Page load time remains under 2 seconds
- Error handling works for partial failures
- No regression in existing functionality

## References

- Spec: `.kiro/specs/external-api-integration/`
- Documentation: `docs/external-api-integration/FINAL_STATUS.md`
- Backend Controller: `app/Http/Controllers/Api/ExternalDataController.php`
- Frontend Component: `resources/js/pages/external-data/browse.js`
- API Routes: `routes/api.php` (lines 1230-1255)
