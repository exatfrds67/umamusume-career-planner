# Backend Integration Planning: Character Creation Persistence

**Date**: January 22, 2026
**Phase**: Backend Integration (Phase 2 of Character Management PRD-001)
**Status**: ⏳ Planning Complete - Ready for Implementation

---

## Phase Overview

Complete the character creation flow by implementing backend validation, database persistence, and character detail
view. This phase bridges the UI wizard (currently complete with 4 steps, all tests passing) with database storage and
read views.

### Phase Goals

1. ✅ Validate form POST data in FormRequest classes
2. ✅ Persist Run records with trainee, scenario, stats, and aptitudes
3. ✅ Seed inheritance factors and support deck slot records
4. ✅ Implement character detail view at `/characters/{id}`
5. ✅ Achieve 12+ passing tests with comprehensive coverage

---

## Task Breakdown (22 items across 5 phases)

### Phase 1: Validation & Database Preparation (4 items)

- [ ] **1.1**: Review CharacterController::store() existing implementation
  - Check for existing validation, database persistence logic
  - Identify what needs to be added/updated

- [ ] **1.2**: Create StoreCharacterRequest FormRequest
  - Validate: trainee_id (exists), scenario_type (valid enum), name (required, string, 1-255 chars)
  - Validate: parents (array, 2 elements), factors (array of selected factor IDs)
  - Validate: stats (array, 0-1200 per stat), aptitudes (distance/surface/style grades)
  - Validate: supportDeck (array of 6 slot assignments with card IDs)

- [ ] **1.3**: Create FactorInheritance factory + verify 8 factor options seeded
  - Create factory: database/factories/FactorInheritanceFactory.php
  - Verify seeder: database/seeders/FactorInheritanceSeeder.php creates 8 options
  - Run seeder if needed

- [ ] **1.4**: Verify database schema correctness
  - runs table: trainee_id (FK), scenario_type (string/enum), starting_stats (JSON), aptitudes (JSON), created_at,
  updated_at
  - factor_inheritances table: id, run_id (FK), factor_id (FK), created_at
  - support_deck_slots table: id, run_id (FK), support_card_id (FK), slot_type (string:
  Speed/Stamina/Power/Guts/Wit/Friend), created_at

---

### Phase 2: Core Persistence Logic (5 items)

- [ ] **2.1**: Implement Run creation in CharacterController::store()
  - Extract trainee_id from formData.trainee (Alpine sends JSON)
  - Extract scenario_type from formData.scenario_type
  - Extract starting_stats array (speed, stamina, power, guts, wit) as JSON
  - Create Run record with all required fields

- [ ] **2.2**: Add stat validation logic
  - Each stat: 0-1200 range (validation already in FormRequest, but double-check)
  - Optional: sum constraint if game rules require total ≤ 6000
  - Store as JSON in runs.starting_stats column

- [ ] **2.3**: Implement inheritance factor seed logic
  - Loop through formData.factors (array of selected factor IDs)
  - Create FactorInheritance records linking run_id to each factor_id
  - Use pivot table or FactorInheritance model with relationships

- [ ] **2.4**: Implement support deck slot creation
  - Parse formData.supportDeck array (6 objects with slot_type and card_id)
  - Create SupportDeckSlot record for each slot
  - Maintain slot order: index 0-5 → Speed, Stamina, Power, Guts, Wit, Friend

- [ ] **2.5**: Add error handling and transaction rollback
  - Wrap all database operations in DB::transaction()
  - On validation error: return error response with 422 Unprocessable Entity
  - On database error: catch exception, rollback, return 500 Server Error

---

### Phase 3: Controller Integration (4 items)

- [ ] **3.1**: Map formData.trainee to Trainee model lookup
  - formData.trainee contains trainee name/ID (check Alpine data structure)
  - Use Trainee::findOrFail() or Trainee::where('name', ...)->first()
  - Extract trainee_id for database insertion

- [ ] **3.2**: Extract trainee name and avatar URL
  - Query Trainee model after successful Run creation
  - Get name and image_path attributes
  - Return in response for frontend redirect/confirmation

- [ ] **3.3**: Parse formData.supportDeck and create SupportDeckSlot records
  - formData.supportDeck is array of 6 slot objects: { slot_type, card_id, support_card }
  - Create SupportDeckSlot record for each, maintaining order
  - Verify all 6 slots are created (error if < 6)

- [ ] **3.4**: Return success response with 201 Created
  - Response structure: `{ success: true, run_id: 123, character: { name, avatar_url }, redirect_url: '/characters/123'
  }`
  - Status code: 201 Created
  - Include run ID for frontend to redirect to character detail view

---

### Phase 4: Character Detail View (5 items)

- [ ] **4.1**: Create Character/Run detail view template
  - File: resources/views/characters/show.blade.php
  - Extends: layouts.app
  - Sections: header (character name + avatar), stats, aptitudes, parents/factors, support deck

- [ ] **4.2**: Display character name, trainee avatar, scenario type, stats with grade badges
  - Show trainee avatar (from run.trainee.image_path)
  - Show character name (from run.name or trainee.name)
  - Show scenario_type (URA Finale or Unity Cup)
  - Show starting stats with grade badges (A, B, C, D, E based on value ranges)

- [ ] **4.3**: Add collapsible parents/factors summary section
  - Display: Parent A name, Parent B name (or "Not selected" if NULL)
  - Display: Selected factor inheritance names (loop through run.factors)
  - Read-only display (no editing in this view)

- [ ] **4.4**: Add support deck configuration card display
  - Show 6 deck slots: Speed, Stamina, Power, Guts, Wit, Friend
  - For each slot, display assigned support card name + image (or "Empty" if NULL)
  - Show card rarity/type metadata if available

- [ ] **4.5**: Add edit button linking to wizard in edit mode (future enhancement)
  - For now: add comment "Edit mode coming in Phase C"
  - Button would link to /characters/{id}/edit with prefilled form data
  - Defer implementation to Phase C (snapshot/versioning)

---

### Phase 5: Testing & Validation (4 items)

- [ ] **5.1**: Write feature tests for StoreCharacterRequest validation
  - Test valid submission: all required fields present → should pass
  - Test invalid trainee_id: → validation error on 'trainee'
  - Test missing stats: → validation error on 'stats.speed', etc.
  - Test stat out of range (> 1200): → validation error
  - Test missing supportDeck: → validation error

- [ ] **5.2**: Write feature tests for CharacterController::store() integration
  - Happy path: POST valid form data → 201 Created, run created in DB
  - Verify database: Run record exists with correct trainee_id, scenario_type, starting_stats
  - Verify database: FactorInheritance records created for selected factors
  - Verify database: SupportDeckSlot records created for all 6 slots
  - Error case: invalid trainee_id → 422 Unprocessable Entity

- [ ] **5.3**: Write feature tests for character detail view
  - Test route: GET /characters/{id} → 200 OK
  - Test rendering: verify trainee avatar, character name, scenario type displayed
  - Test stats display: verify all 5 stats shown with correct values
  - Test factors display: verify selected factors listed
  - Test deck display: verify 6 slots displayed (with cards or "Empty")

- [ ] **5.4**: Browser verify end-to-end flow
  - Open wizard at <http://127.0.0.1:8000/characters/create>
  - Fill all 4 steps (trainee, parents, factors, deck, stats, aptitudes)
  - Submit form → verify POST succeeds (console shows 201 Created)
  - Verify redirect to /characters/{id} works
  - On detail view: verify all data matches what was entered in wizard

---

## Success Criteria

✅ **Backend Validation**

- CharacterController::store() accepts form POST and returns 201 Created with run ID

✅ **Database Persistence**

- Run table stores: trainee_id, scenario_type, starting_stats (JSON), aptitudes (JSON), created_at
- FactorInheritance pivot records created for each selected factor
- SupportDeckSlot records created for all 6 slots with correct card_id + slot_type

✅ **Character Detail View**

- Displays all saved data with proper formatting (stats, deck cards, factors)
- Trainee avatar and name visible
- Scenario type shown
- All 6 support deck slots displayed (with card names or "Empty")

✅ **Test Coverage**

- Feature tests pass: 12+ total (7 existing + 5+ new character creation tests)
- All validation scenarios covered (valid + invalid submissions)
- Database integrity verified (records created with correct relationships)

✅ **End-to-End Verification**

- Browser verify: create character via wizard → POST succeeds → detail view loads → all data correct
- No console errors during submission or redirect
- Form data persistence confirmed (can re-edit run data in Phase C)

✅ **Code Quality**

- Pint formatting clean, no violations
- get_errors output: 0 errors
- All tests passing without warnings

---

## Key Files to Modify/Create

| File                                            | Status        | Purpose                             |
| ----------------------------------------------- | ------------- | ----------------------------------- |
| app/Http/Requests/StoreCharacterRequest.php     | Create        | Form validation rules               |
| app/Http/Controllers/CharacterController.php    | Modify        | store() method implementation       |
| resources/views/characters/show.blade.php       | Create        | Character detail view template      |
| routes/web.php                                  | Verify        | Ensure characters.show route exists |
| tests/Feature/CharacterCreationTest.php         | Modify        | Add 5+ new test cases               |
| database/factories/FactorInheritanceFactory.php | Verify/Create | Factory for factor inheritance      |
| database/seeders/FactorInheritanceSeeder.php    | Verify        | Seed 8 inheritance factors          |
| database/migrations/create_*_table.php          | Verify        | Schema validation                   |

---

## Dependencies & Blockers

✅ **Resolved (Already exist)**

- Character/Run model relationships (verified in app/Models/Run.php)
- Trainee model exists (verified in app/Models/Trainee.php)
- SupportCard model exists (verified in app/Models/SupportCard.php)

⚠️ **To Verify**

- FactorInheritance seeder may need to create 8 inheritance factor records
- Database schema must have: runs.trainee_id FK, runs.starting_stats JSON, runs.aptitudes JSON
- factor_inheritances pivot table must exist
- support_deck_slots table with support_card_id + slot_type columns

---

## Technical Constraints & Notes

1. **Alpine.js Form Data Structure**
   - formData object passed to POST contains: name, scenario_type, trainee, parents, factors, stats, aptitudes,
   supportDeck
   - trainee field contains either trainee ID or full trainee object (verify in create.blade.php)
   - Need to handle both cases in controller

2. **Support Deck Slot Ordering**
   - formData.supportDeck array of 6 objects
   - Index 0 → Speed, 1 → Stamina, 2 → Power, 3 → Guts, 4 → Wit, 5 → Friend
   - Must maintain this order in database

3. **Stats/Aptitudes JSON Storage**
   - Store starting_stats as JSON: { "speed": 800, "stamina": 700, "power": 600, "guts": 500, "wit": 650 }
   - Store aptitudes as JSON: { "distance": "A", "surface": "B", "style": "C" }
   - Use JSON type casting in Run model: `protected $casts = ['starting_stats' => 'json', ...]`

4. **Inheritance Factor Relationships**
   - Verify pivot table structure: factor_inheritances(id, run_id, factor_id, created_at)
   - Or model-based: FactorInheritance with belongsTo Run + Factor relationships

5. **Browser Testing**
   - After form submission, console should show POST request with 201 response
   - Redirect URL from response should match /characters/{id}
   - Detail view should load within 2 seconds (performance target)

---

## Testing Checklist

### Manual Browser Testing

- [ ] Create character with all 4 steps filled → submit
- [ ] Console: verify POST returns 201 Created
- [ ] Console: verify response includes run_id and redirect_url
- [ ] Page redirects to /characters/{id}
- [ ] Detail view loads all sections (avatar, name, stats, deck, factors)
- [ ] Stats display correct values entered in wizard
- [ ] Support deck shows 6 slots with correct card assignments
- [ ] Factors section lists all selected inheritance factors

### Feature Test Coverage

- [ ] StoreCharacterRequest: valid submission passes all validations
- [ ] StoreCharacterRequest: invalid trainee_id fails validation
- [ ] StoreCharacterRequest: stats > 1200 fails validation
- [ ] CharacterController::store(): creates Run record with correct data
- [ ] CharacterController::store(): creates FactorInheritance records
- [ ] CharacterController::store(): creates SupportDeckSlot records
- [ ] Character detail view: renders without errors
- [ ] Character detail view: displays all saved data correctly

---

## Implementation Order

**Recommended sequence** (execute in this order to avoid blocking dependencies):

1. **1.1 → 1.2 → 1.3 → 1.4** (Prepare validation + database)
2. **2.1 → 2.2 → 2.3 → 2.4 → 2.5** (Implement controller logic)
3. **3.1 → 3.2 → 3.3 → 3.4** (Integrate with form data)
4. **5.1 → 5.2 → 5.3** (Write tests in parallel)
5. **4.1 → 4.2 → 4.3 → 4.4 → 4.5** (Create detail view + UI)
6. **5.4** (Final end-to-end browser verify)

This order ensures backend is tested before frontend detail view is built.

---

## Next Steps

**When ready to begin implementation**:

1. ✅ Read current CharacterController::store() implementation
2. ✅ Check database schema for runs/factor_inheritances/support_deck_slots tables
3. ✅ Create FormRequest with validation rules
4. ✅ Implement controller method with database persistence
5. ✅ Write and run tests
6. ✅ Create detail view template
7. ✅ Browser verify end-to-end flow
8. ✅ Run full test suite + Pint formatting

---

**Planning Complete** ✅
**Ready for Implementation** 🚀
