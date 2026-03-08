# Quick Reference: Backend Integration Phase

**Start Here**: Read this file first for quick orientation

---

## 📍 Current State

✅ **Wizard UI Complete** (4 steps, all tests passing, 0 errors)  
🚀 **Backend Integration Starting** (Data validation + persistence)  

---

## 🎯 The Goal

**Input**: Form data from Alpine.js wizard  
**Process**: Validate + store to database  
**Output**: Character appears in detail view at `/characters/{id}`

---

## 📋 Quick Task List (22 items, 5 phases)

### Phase 1️⃣ (Prep - 4 tasks)

- [ ] 1.1: Review CharacterController::store()
- [ ] 1.2: Create StoreCharacterRequest
- [ ] 1.3: Create FactorInheritance factory
- [ ] 1.4: Verify database schema

### Phase 2️⃣ (Persistence - 5 tasks)

- [ ] 2.1: Implement Run creation
- [ ] 2.2: Add stat validation
- [ ] 2.3: Seed inheritance factors
- [ ] 2.4: Create deck slots
- [ ] 2.5: Add error handling

### Phase 3️⃣ (Integration - 4 tasks)

- [ ] 3.1: Map trainee form data
- [ ] 3.2: Extract trainee info
- [ ] 3.3: Parse support deck
- [ ] 3.4: Return 201 response

### Phase 4️⃣ (Detail View - 5 tasks)

- [ ] 4.1: Create detail template
- [ ] 4.2: Show character info + stats
- [ ] 4.3: Show parents/factors
- [ ] 4.4: Show deck config
- [ ] 4.5: Add edit button placeholder

### Phase 5️⃣ (Testing - 4 tasks)

- [ ] 5.1: Test FormRequest validation
- [ ] 5.2: Test controller persistence
- [ ] 5.3: Test detail view
- [ ] 5.4: Browser end-to-end test

---

## 📁 Documentation Files

| File                                        | Purpose                                              |
| ------------------------------------------- | ---------------------------------------------------- |
| `.agents/PHASE2-PLANNING-SUMMARY.md`        | 📄 **You are here** - Quick overview                 |
| `.agents/phase-backend-integration-plan.md` | 📖 Detailed 22-item plan (read this for full context)|
| `.agents/memory.instruction.md`             | 💾 Project memory + wizard completion status         |
| `docs/prds/PRD-001.md`                      | 📋 Character Management requirements                 |

---

## 🔧 Key Files to Modify

1. **Create**: `app/Http/Requests/StoreCharacterRequest.php`
2. **Modify**: `app/Http/Controllers/CharacterController.php`
3. **Create**: `resources/views/characters/show.blade.php`
4. **Modify**: `tests/Feature/CharacterCreationTest.php`

---

## ✅ Success Criteria

- [ ] CharacterController::store() returns 201 Created
- [ ] Run + FactorInheritance + SupportDeckSlot records created
- [ ] Detail view displays all saved data
- [ ] 12+ tests passing (7 existing + 5+ new)
- [ ] Browser verify: wizard → POST → detail view works
- [ ] Pint formatting: 0 violations

---

## 🚀 How to Start

**Step 1**: Read detailed plan

```text
Open: .agents/phase-backend-integration-plan.md
Time: 10 minutes
Goal: Understand full scope + dependencies
```text

**Step 2**: Start Phase 1

```text
Task 1.1: Review app/Http/Controllers/CharacterController.php
Time: 15 minutes
Action: Check existing store() implementation
```

**Step 3**: Continue sequentially

```text
Complete phases in order: 1→2→3→5→4
(Test during 5, build UI in 4)
```text

---

## 🛠️ Command Cheat Sheet

```bash
# Run tests during development
php artisan test tests/Feature/CharacterCreationTest.php --compact

# Check for errors
php artisan tinker

# Format code
vendor/bin/pint --dirty

# Full test suite (final)
php artisan test --compact

# Seed if needed
php artisan db:seed --class=FactorInheritanceSeeder
```text

---

## 📊 Expected Timeline

| Phase           | Time          | Status                     |
| --------------- | ------------- | -------------------------- |
| 1 (Prep)        | 15 min        | ⏳ Ready                    |
| 2 (Persistence) | 60 min        | ⏳ Blocked by Phase 1       |
| 3 (Integration) | 45 min        | ⏳ Blocked by Phase 2       |
| 5 (Testing)     | 60 min        | ⏳ Parallel with Phases 2-3 |
| 4 (Detail View) | 45 min        | ⏳ Blocked by Phase 3       |
| **Total**       | **4-5 hours** | ⏳ Starting now             |

---

## 🎓 Data Flow Reminder

```

Alpine.js Form Data (4 steps)
        ↓
    POST /characters
        ↓
StoreCharacterRequest (Validation)
        ↓
CharacterController::store() (Persistence)
        ↓
Database (Run + Factors + Deck)
        ↓
Redirect to /characters/{id}
        ↓
Detail View (Read & Display)

```text

---

## 💡 Key Implementation Notes

1. **Trainee Lookup**: formData.trainee contains trainee object/ID → use Trainee::findOrFail()
2. **Stats JSON**: Store { "speed": 800, "stamina": 700, ... } as JSON
3. **Deck Slots**: Maintain order index 0-5 → Speed, Stamina, Power, Guts, Wit, Friend
4. **Error Handling**: Wrap in DB::transaction() for rollback on failure
5. **Tests**: Write validation tests + controller tests + detail view tests

---

## 📞 Need Help?

**Error Debugging**:

1. Check `.agents/memory.instruction.md` for similar issues
2. Search detailed plan for constraints/dependencies
3. Run `php artisan tinker` to debug locally

**Lost Orientation**:

1. Check TODO list: `manage_todo_list operation read`
2. Re-read this quick reference
3. Open detailed plan for context

**Need to Pause**:

1. Note current task number (e.g., "Completed 2.3, stopping here")
2. Next session: Read this file + detailed plan + resume from next task

---

**Next Action**: Open `.agents/phase-backend-integration-plan.md` for detailed planning

✅ Planning Complete | 🚀 Ready to Implement

