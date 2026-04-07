# AI Training Advisory System - Routing Setup

**Date**: February 2, 2026
**Status**: ✅ Complete
**Spec**: `.kiro/specs/ai-training-advisory/`

## Overview

Configured API routing for the AI-Powered Training Advisory System according to the spec documentation. All advisory
endpoints are now properly registered in `routes/api.php` with appropriate middleware and rate limiting.

## Routes Configured

### Base Path

- **Prefix**: `/api/advisory`
- **Middleware**: `auth:sanctum`, `throttle:10,1` (10 requests per minute)
- **Controller**: `App\Http\Controllers\Api\AdvisoryController`

### Endpoints

| Method | Endpoint | Controller Method | Spec Reference | Status |
| --- | --- | --- | --- | --- |
| POST | `/api/advisory/training/recommendations` | `getTrainingRecommendations()` | Requirements 3.1 | ⏳ Stub |
| POST | `/api/advisory/skills/advice` | `getSkillPurchaseAdvice()` | Requirements 3.2 | ⏳ Stub |
| POST | `/api/advisory/race/strategy` | `getRaceStrategy()` | Requirements 3.3 | ⏳ Stub |
| POST | `/api/advisory/critical/detect` | `detectCriticalSituations()` | Requirements 3.4 | ⏳ Stub |
| POST | `/api/advisory/training/outcome` | `recordTrainingOutcome()` | Requirements 3.8 | ✅ Implemented |
| POST | `/api/advisory/race/outcome` | `recordRaceOutcome()` | Requirements 3.8 | ✅ Implemented |

## Controller Structure

### Location

`app/Http/Controllers/Api/AdvisoryController.php`

### Implemented Methods

#### ✅ `recordTrainingOutcome(RecordTrainingOutcomeRequest $request)`

- **Purpose**: Record training outcomes for prediction accuracy tracking
- **Status**: Fully implemented with validation
- **Validates**: Property 15 (Prediction Accuracy Recording)
- **Request**: Accepts recommendation and actual outcome data
- **Response**: Returns success status with accuracy score

#### ✅ `recordRaceOutcome(RecordRaceOutcomeRequest $request)`

- **Purpose**: Record race outcomes for prediction accuracy tracking
- **Status**: Fully implemented with validation
- **Validates**: Property 15 (Prediction Accuracy Recording)
- **Request**: Accepts strategy and actual result data
- **Response**: Returns success status with accuracy score

### Stub Methods (To Be Implemented)

#### ⏳ `getTrainingRecommendations(Request $request)`

- **Purpose**: Generate training facility recommendations
- **Implementation Phase**: Phase 3 (AI Integration)
- **Returns**: 501 Not Implemented with spec reference

#### ⏳ `getSkillPurchaseAdvice(Request $request)`

- **Purpose**: Generate skill purchase recommendations
- **Implementation Phase**: Phase 3 (AI Integration)
- **Returns**: 501 Not Implemented with spec reference

#### ⏳ `getRaceStrategy(Request $request)`

- **Purpose**: Generate race strategy recommendations
- **Implementation Phase**: Phase 4 (Critical Detection)
- **Returns**: 501 Not Implemented with spec reference

#### ⏳ `detectCriticalSituations(Request $request)`

- **Purpose**: Detect critical situations requiring immediate attention
- **Implementation Phase**: Phase 4 (Critical Detection)
- **Returns**: 501 Not Implemented with spec reference

## Rate Limiting

Per the design document (Section: Security Considerations):

- **Limit**: 10 requests per minute per user
- **Middleware**: `throttle:10,1`
- **Scope**: All advisory endpoints

## Authentication

- **Middleware**: `auth:sanctum`
- **Requirement**: All endpoints require authenticated users
- **Token**: Sanctum API token required in Authorization header

## Testing Routes

### Verify Route Registration

```bash
php artisan route:list --path=api/advisory
```text

### Test Endpoint (Example)

```bash
# Training outcome recording (implemented)
curl -X POST http://127.0.0.1:8000/api/advisory/training/outcome \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "career_run_id": 123,
    "turn_number": 15,
    "recommendation": {...},
    "actual_outcome": {...}
  }'

# Training recommendations (stub)
curl -X POST http://127.0.0.1:8000/api/advisory/training/recommendations \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```text

## Integration Points

### Existing Pages

The advisory system integrates with existing pages:

- `/training/predictions` - Main training predictions page
- `/training/predictions/{character}` - Character-specific predictions
- `/characters/{character}/training` - Training interface

### Advisory Panel Component

The advisory panel will be embedded in these pages (not a separate route):

- Livewire component: `AdvisoryPanel`
- Location: To be created in Phase 5 (UI Components)

## Files Modified

1. **routes/api.php**
   - Expanded existing advisory route group
   - Added 4 new endpoints (training recommendations, skill advice, race strategy, critical detection)
   - Maintained existing outcome recording endpoints

2. **app/Http/Controllers/Api/AdvisoryController.php**
   - Added 4 stub methods with proper PHPDoc
   - Each stub returns 501 with spec reference
   - Maintained existing implemented methods

3. **routes/web.php**
   - No changes (advisory routes belong in api.php, not web.php)

## Next Steps

### Phase 3: AI Integration (Week 4)

- [ ] Implement `getTrainingRecommendations()` with AI service integration
- [ ] Implement `getSkillPurchaseAdvice()` with AI service integration
- [ ] Implement `getRaceStrategy()` with AI service integration
- [ ] Create Form Request classes for validation
- [ ] Write integration tests

### Phase 4: Critical Detection (Week 5)

- [ ] Implement `detectCriticalSituations()` with detection logic
- [ ] Create Form Request class for validation
- [ ] Write unit tests for detection logic

### Phase 5: UI Components (Week 6)

- [ ] Create Livewire `AdvisoryPanel` component
- [ ] Integrate panel into training prediction pages
- [ ] Add Alpine.js interactivity
- [ ] Test accessibility (WCAG 2.2 AA)

## Spec Alignment

✅ **Design Document**: API Endpoints section fully implemented
✅ **Requirements**: Endpoints align with functional requirements 3.1-3.4, 3.8
✅ **Tasks**: Aligns with Additional Tasks section (API Endpoints A.1-A.4)

## Notes

- All endpoints use POST method (per spec)
- Rate limiting set to 10 requests/minute (per security considerations)
- Controller uses dependency injection for `TrainingAdvisoryService`
- Stub methods include spec references for implementation guidance
- Existing outcome recording methods are fully functional

---

**Implementation Status**: Routing infrastructure complete, ready for Phase 3 implementation
**Test Coverage**: Route registration verified, endpoint stubs return 501 as expected
**Documentation**: Complete with examples and next steps
