# Phase 2: Backend Integration Planning - Summary

**Project**: Umamusume Career Planner  
**Phase**: Backend Integration (Character Creation Persistence)  
**Date**: January 22, 2026  
**Status**: ✅ Planning Complete

---

## Current State

### Wizard UI (Phase 1) - ✅ Complete

- 4-step character creation wizard (Trainee & Scenario / Parents & Factors / Support Deck / Review & Confirm)
- All steps render correctly in browser
- Navigation (Next/Previous buttons) working
- Form validation requires trainee selection
- localStorage persistence working
- Feature tests: 7 passing, 21 assertions
- Blade rendering: 0 errors
- Pint formatting: Clean

### Next Phase (Phase 2) - Backend Integration - 🚀 Ready

The wizard UI successfully collects character creation data. Now we need to:

1. Validate the form POST payload
2. Persist data to database (Run, FactorInheritance, SupportDeckSlot tables)
3. Create character detail view to display saved runs
4. Test end-to-end flow

---

## Planning Structure

### 📋 Task Organization

- **Total Tasks**: 22 items
- **Phases**: 5 (Preparation → Persistence → Integration → UI → Testing)
- **Each Phase**: 4-5 focused tasks with specific deliverables

### 📁 Documentation Files Created

**1. `.agents/phase-backend-integration-plan.md`**

- Complete 22-item task breakdown across 5 phases
- Success criteria and testing checklist
- Key files to modify/create
- Dependencies and technical constraints
- Implementation order recommendation
- 50+ lines of detailed planning

**2. `.agents/memory.instruction.md` (Updated)**

- Added Character Creation Wizard completion status
- Added Backend Integration Planning section with goals and file references
- Preserved all existing solutions repository items

### TODO List (Managed via manage_todo_list)

- 22 actionable items with descriptions
- All set to "not-started" status
- Ready for tracking progress

---

## Phase Overview: What We're Building

### Phase 1: Validation & Database Prep (4 tasks)

✅ Understand existing controller + validate database schema  
✅ Create FormRequest for input validation  
✅ Prepare factor inheritance data

### Phase 2: Core Persistence (5 tasks)

✅ Implement Run record creation  
✅ Add stat validation  
✅ Seed inheritance factors  
✅ Create support deck slots  
✅ Add error handling with DB transactions

### Phase 3: Controller Integration (4 tasks)

✅ Map Alpine.js form data to models  
✅ Handle trainee lookup  
✅ Parse and store support deck  
✅ Return success response with run ID

### Phase 4: Detail View (5 tasks)

✅ Create character detail template  
✅ Display character info + stats + grades  
✅ Show parents/factors summary  
✅ Display deck configuration  
✅ Add edit button placeholder

### Phase 5: Testing & Validation (4 tasks)

✅ Test FormRequest validation rules  
✅ Test controller persistence  
✅ Test detail view rendering  
✅ Browser verify end-to-end flow

---

## Key Deliverables

### Files to Create

1. `app/Http/Requests/StoreCharacterRequest.php` - Form validation
2. `resources/views/characters/show.blade.php` - Character detail view
3. `database/factories/FactorInheritanceFactory.php` - If missing

### Files to Modify

1. `app/Http/Controllers/CharacterController.php` - store() method
2. `tests/Feature/CharacterCreationTest.php` - Add 5+ new tests
3. `routes/web.php` - Verify characters.show route

### Files to Verify

1. `database/seeders/FactorInheritanceSeeder.php` - 8 factors seeded
2. Database migrations - Schema correctness

---

## Success Metrics

✅ **Backend Acceptance**: CharacterController::store() returns 201 Created with run ID  
✅ **Data Persistence**: Run + FactorInheritance + SupportDeckSlot records created correctly  
✅ **Detail View**: All saved data displays with proper formatting  
✅ **Test Coverage**: 12+ tests passing (7 existing + 5+ new)  
✅ **End-to-End**: Create character → POST succeeds → detail view loads → all data correct  
✅ **Code Quality**: Pint clean, 0 errors from get_errors

---

## Next Actions

### When Ready to Begin Implementation

1. **Review Phase 1 Tasks (15 min)**
   - Read CharacterController::store() existing code
   - Check database schema for all 3 tables
   - Verify FactorInheritance seeder

2. **Implement Phase 2 Tasks (60-90 min)**
   - Create FormRequest with validation rules
   - Update controller with persistence logic
   - Add transaction handling and error responses

3. **Write Phase 5 Tests (60 min)**
   - FormRequest validation tests
   - Controller integration tests
   - Detail view tests

4. **Build Phase 4 UI (45 min)**
   - Create detail view template
   - Display all saved data sections

5. **Final Verification (30 min)**
   - Browser: Create character → verify POST → check detail view
   - Run full test suite
   - Pint formatting check

**Total Estimated Time**: 4-5 hours for complete implementation + testing

---

## Technical Highlights

### Form Data Structure (from Alpine.js)

```javascript
formData: {
  name: "Special Week",
  scenario_type: "URA Finale",
  trainee: { id: 1, name: "Special Week", ... },
  parents: [1, 2],
  factors: [3, 5, 7],
  stats: { speed: 800, stamina: 700, power: 600, guts: 500, wit: 650 },
  aptitudes: { distance: "A", surface: "B", style: "C" },
  supportDeck: [
    { slot_type: "Speed", card_id: 10 },
    { slot_type: "Stamina", card_id: 15 },
    // ... 4 more slots
  ]
}
```text

### Database Operations Required

1. Create Run: INSERT with trainee_id, scenario_type, starting_stats (JSON)
2. Create FactorInheritance: INSERT 3+ rows linking run_id to factor_ids
3. Create SupportDeckSlot: INSERT 6 rows with slot_type and card_id
4. All in DB::transaction() for rollback on error

### API Response

```json
{
  "success": true,
  "run_id": 42,
  "character": {
    "name": "Special Week",
    "avatar_url": "https://api.umapyoi.net/images/trainees/1.png"
  },
  "redirect_url": "/characters/42"
}
```

---

## Risk Mitigation

**Potential Issues & Mitigations**:

- ⚠️ **Trainee lookup fails**: Verify trainee data exists in database + handle not-found gracefully
- ⚠️ **FactorInheritance seeder missing**: Check database for 8 factors, create if needed
- ⚠️ **Asset paths broken**: Use Laravel asset() helper for all image URLs
- ⚠️ **Support deck slot ordering**: Maintain array index → slot type mapping strictly
- ⚠️ **JSON encoding issues**: Cast stats/aptitudes to JSON type in model

---

## Recommended Tools & Commands

**During Implementation**:

```bash
# Run tests during development
php artisan test tests/Feature/CharacterCreationTest.php --compact

# Format code as you go
vendor/bin/pint --dirty

# Check for errors
php artisan tinker  # Quick debugging

# Verify database
php artisan db:seed --class=FactorInheritanceSeeder
```text

**Final Verification**:

```bash
# Full test suite
php artisan test --compact

# Code formatting
vendor/bin/pint

# No errors check
php artisan tinker "collect(get_declared_classes())"
```

---

## Document References

**Planning Documents**:

- `.agents/phase-backend-integration-plan.md` - Detailed 22-item plan
- `.agents/memory.instruction.md` - Project memory + wizard completion status
- `docs/prds/PRD-001.md` - Character Management requirements
- `docs/specs/SPEC-001.md` - Technical specification

**Code References**:

- `resources/views/characters/create.blade.php` - Wizard UI (1251 lines, Alpine data)
- `app/Http/Controllers/CharacterController.php` - Controller to modify
- `tests/Feature/CharacterCreationTest.php` - Existing tests (7 passing)
- `database/migrations/` - Schema definitions

---

## Continuation Plan

### If Pausing Work

1. Check memory file: `.agents/memory.instruction.md`
2. Read plan: `.agents/phase-backend-integration-plan.md`
3. Check TODO list in conversation for progress tracking
4. Last completed task: [Check TODO status]
5. Resume from next incomplete task

### After Phase 2 Complete

- **Phase 3**: Training System Integration (PRD-002)
- **Phase 4**: Race Calendar & Prep (PRD-003)
- **Phase 5**: Skills Shop & Loadout (PRD-004)
- **Phase 6**: Snapshot/Versioning (PRD-001 Phase C)

---

✅ **Planning Complete**  
🚀 **Ready for Implementation**  
📝 **All documentation prepared**  
📋 **22 tasks organized and tracked**

Start with **Phase 1, Task 1.1**: Review CharacterController::store() existing implementation
