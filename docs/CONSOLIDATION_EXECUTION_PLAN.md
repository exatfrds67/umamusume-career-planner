# Documentation Consolidation Execution Plan

**Status**: Phase 2 Infrastructure Complete
**Date**: March 21, 2026

---

## Phase 2: Consolidation - Action Items

### 2.1 Redis Files to Archive

**Master files (KEEP in docs/redis/):**
- START_HERE.md
- REDIS_COMPLETE_GUIDE.md

**Files to MOVE to docs/archive/legacy/redis/:**

```powershell
Move files from docs/redis/ to docs/archive/legacy/redis/:
- REDIS_SETUP_INSTRUCTIONS.md
- REDIS_SETUP_SUMMARY.md
- REDIS_SETUP_CHECKLIST.md
- REDIS_SETUP_COMPLETED.md
- REDIS_SETUP_FINAL_REPORT.md
- REDIS_STEP_BY_STEP_IMPLEMENTATION.md
- REDIS_CURRENT_STATUS.md
- REDIS_ISSUE_RESOLVED.md
- REDIS_ISSUE_SUMMARY.md
- REDIS_DIAGNOSIS.md
- REDIS_APACHE_FIX.md
- FIX_REDIS_WEB_ERROR.md
- WSL_REDIS_FIX.md
- WSL_REDIS_INVESTIGATION_COMPLETE.md
- REDIS_XAMPP_SOLUTION.md
- REDIS_IMPLEMENTATION_SUMMARY.md
- REDIS_TESTING_GUIDE.md
- REDIS_WSL_SETUP_GUIDE.md
- UPDATE_REDIS_TESTS.md
- REDIS_QUICK_REFERENCE.md
- REDIS_COMMANDS_REFERENCE.md
- REDIS_DOCUMENTATION_INDEX.md
- ADD_REDIS_TO_PHP_INI.txt
- WSL_REDIS_FIX.md
```

### 2.2 Larastan Files to Archive

**Master files (KEEP in docs/larastan/):**
- larastan-level9-fixes-summary.md
- larastan-level9-fix-plan.md
- phpstan-pest-properties-solution.md (utility file)

**Files to MOVE to docs/archive/legacy/larastan/:**

```powershell
Move files from docs/larastan/ to docs/archive/legacy/larastan/:
- larastan-batch3-summary.md
- larastan-batch6-summary.md
- larastan-final-summary.md
- larastan-progress-report.md
- larastan-progress-summary.md
- larastan-fix-strategy.md
- larastan-models-fix-summary.md
- larastan-fix-plan.md
- larastan-level9-fix-strategy.md
- larastan-level9-fix-summary-jan28.md
```

### 2.3 Audit Files to Consolidate

**Consolidate locations:** Merge `audit-reports/` and `admin-audit/` into `audits/`

**Files already in docs/audits/ (KEEP):**
- ai-subsystem-audit-2026-02-28.md
- ai-subsystem-audit-2026-07-09.md
- AUDIT_FINDINGS_2026-03-09.md
- AUDIT_FINDINGS_SAFE_2026-03-09.md
- authorization-audit-2026-01-29.md
- authorization-fix-summary.md
- DIAGRAM_PATCH_APPLICATION_GUIDE.md
- DIAGRAM_PATCH_APPLICATION_GUIDE_REVISED.md (latest)
- DIAGRAM_VERIFICATION_REPORT_2026-03-09.md
- PATCH_APPLICATION_GUIDE.md
- PATCH_APPLICATION_GUIDE_REVISED.md (latest)
- PATCH_SET_SAFE_2026-03-09.md
- telescope-horizon-audit-2026-01-29.md
- lint-results.json

**Files to MOVE from docs/audit-reports/ to docs/audits/:**
- AUDIT_REPORT_FINAL.md
- AUDIT_REPORT_PHASE_1.md

**Files to MOVE from docs/admin-audit/ to docs/audits/:**
- 2026-02-28-admin-ui-ux-audit.md
- 2026-02-28-re-audit-post-queue-fix.md

**Then REMOVE (empty) directories:**
- docs/audit-reports/
- docs/admin-audit/

### 2.4 Duplicated Audit Variants (DELETE or ARCHIVE)

**Files with multiple versions (KEEP LATEST, ARCHIVE OLD):**

```powershell
Archive to docs/archive/deprecated/:
For each file with multiple versions, keep REVISED/SAFE variant:
- DIAGRAM_PATCH_APPLICATION_GUIDE.md (keep REVISED)
- PATCH_APPLICATION_GUIDE.md (keep REVISED)

Archive to docs/archive/deprecated/:
- Multiple "AUDIT_FINDINGS" variants (keep SAFE version)
```

---

## Phase 3: Reorganization - Action Items

### 3.1 Testing Documentation

**Move from docs/external-api-integration/ to docs/testing/:**

```powershell
- BROWSER_TESTING_CHROME_EDGE.md
- API_TESTING_QUICK_REFERENCE.md
- TESTING_GUIDE.md
- QUICK_START_TESTING.md
- ERROR_SCENARIO_TEST_REPORT.md
- FILTER_TESTING_REPORT.md
- SORT_FUNCTIONALITY_TEST_REPORT.md
```

### 3.2 Feature Documentation (Support Cards & Skills)

**Create new directories:**

```powershell
mkdir docs/feature-documentation/support-cards
mkdir docs/feature-documentation/skills
```

**Move from docs/external-api-integration/ to feature-documentation/support-cards/:**

```powershell
- SUPPORT_CARD_DATA_LIMITATION.md
- SUPPORT_CARD_IMPLEMENTATION_ROADMAP.md
- SUPPORT_CARD_RARITY_SOLUTION.md
- SUPPORT_CARDS_IMAGE_STATUS.md
- SUPPORT_CARDS_IMPLEMENTATION_SUMMARY.md
```

**Move from docs/feature-documentation/ to feature-documentation/support-cards/:**

```powershell
- SUPPORT_CARDS_IMAGE_STATUS.md
- SUPPORT_CARDS_IMPLEMENTATION_SUMMARY.md
```

**Move from docs/feature-documentation/ to feature-documentation/skills/:**

```powershell
- SKILL_SYSTEM_DOCUMENTATION.md
- SKILLS_DATA_IMPLEMENTATION.md
```

### 3.3 Frontend Development Guides

**Move from docs/implementation/ to docs/frontend-development/:**

```powershell
- blade-asset-refactoring-plan.md
- EXTERNAL_API_FRONTEND_INTEGRATION.md
- COMPONENTS_INTEGRATION_SUMMARY.md
- game-alignment-analysis.md
- GAME_MECHANICS_ALIGNMENT_ANALYSIS.md
- GAME_MECHANICS_AUDIT_REPORT.md
- GAME_MECHANICS_FIX_SUMMARY.md
- game-mechanics-corrections-summary.md
```

### 3.4 Game Mechanics Documentation

**Move from docs/design/ to docs/research/game-mechanics/:**

```powershell
Create: docs/research/game-mechanics/

Move from docs/design/:
- component-inventory.md (keep copy in design/, move copy to research/)
- data-flow-mapping.md
- game-alignment-analysis.md
- game-alignment-plan.md
- game-ui-alignment-strategy.md
- GAME_ALIGNMENT_DOCUMENTATION_INDEX.md
- GAME_ALIGNMENT_PLANNING_SUMMARY.md
- GAME_ALIGNMENT_STRATEGIC_PLAN.md
- COMPLETION_REPORT.md
- IMPLEMENTATION_PLAN.md
- IMPLEMENTATION_PLAN_UPDATES.md
- prototype-plan.md
```

---

## Phase 4: Deletion - Action Items

### 4.1 Backup Files (DELETE)

```powershell
Delete from docs/:
- archive/008_SIS_Software_Integration_Specifications.bak
- archive/PHASE_5_AUDIT_REPORT.md.bak
- neuron/unity-cup-mechanics-research.md.bak
- research/umamusume-ura-finale-comprehensive-guide.md.bak
```

### 4.2 Quick Check Artifacts (DELETE)

```powershell
Delete from docs/:
- bedrock/BEDROCK_QUICK_CHECK.txt
```

---

## Phase 5: Indexing - Action Items

### 5.1 Create/Update README.md Files

**Create README.md in each subdirectory:**

- `docs/redis/README.md` - Lists master files and consolidation plan
- `docs/larastan/README.md` - Lists master files and consolidation plan
- `docs/audits/README.md` - Lists available audits by category
- `docs/testing/README.md` - Lists all testing guides
- `docs/external-api-integration/README.md` - Entry point for API docs
- `docs/implementation-summaries/README.md` - Guide to finding latest summaries
- `docs/guides/README.md` - Game mechanics and system guides
- `docs/feature-documentation/README.md` - Feature-specific docs
- `docs/feature-documentation/support-cards/README.md` - Support card docs
- `docs/feature-documentation/skills/README.md` - Skill system docs

### 5.2 Update Project-Level Documentation

**Update docs/README.md to link to:**
- LLM_REFERENCE_INDEX.md (as primary LLM reference)
- Explain new structure: core (00, 01, 02) vs. features (rest)
- Link to consolidation indices in each subdirectory

**Update c:\XAMPP\htdocs\umamusume-career-planner\README.md to include:**
- New section: "Documentation Structure"
- Link to docs/LLM_REFERENCE_INDEX.md
- Note about consolidation and archiving

---

## Execution Notes

### File Movement Commands (PowerShell)

For Redis consolidation:
```powershell
cd docs\redis
$oldFiles = @('REDIS_SETUP_INSTRUCTIONS.md', ... # full list above
foreach ($file in $oldFiles) {
    if (Test-Path "./$file") {
        Move-Item -Path "./$file" -Destination "..\archive\legacy\redis\" -Force
    }
}
```

For Larastan consolidation:
```powershell
cd docs\larastan
$oldFiles = @('larastan-batch3-summary.md', ... # full list above
foreach ($file in $oldFiles) {
    if (Test-Path "./$file") {
        Move-Item -Path "./$file" -Destination "..\archive\legacy\larastan\" -Force
    }
}
```

### Consolidation Index Files Created

✅ `/docs/redis/CONSOLIDATION_INDEX.md` - Explains consolidated structure
✅ `/docs/larastan/CONSOLIDATION_INDEX.md` - Explains consolidated structure

### Archive Infrastructure Created

✅ `/docs/archive/legacy/redis/` - For old Redis files
✅ `/docs/archive/legacy/larastan/` - For old Larastan files
✅ `/docs/archive/session-records/` - For old task summaries
✅ `/docs/archive/test-results/` - For old test reports
✅ `/docs/archive/deprecated/` - For obsolete files

---

## Summary of Consolidation Impact

| Category | Before | After | Reduction |
|----------|--------|-------|-----------|
| Redis docs (active) | 26 | 2 | 92% |
| Larastan docs (active) | 14 | 2 | 86% |
| Audit docs (consolidated) | 13 across 3 dirs | 13 in 1 dir | Unified |
| Subdirectories | 26 | 20 | Cleaner |
| LLM Discoverability | Scattered | Centralized | ✓ |

---

**Next Step:**
Ready to execute Phases 2-5. Recommend using the provided PowerShell commands or GUI file operations
to move/delete files according to this plan.
