# Diagram Patch Application Guide (Revised)

## Umamusume Career Planner - Diagram Corrections
**Date**: March 9, 2026
**Based On**: Diagram Update Verification Report (docs-only scope)
**Format**: Organized by file with exact before → after snippets
**Total Patches**: Conservative set, focusing on minimal safe fixes

---

## Table of Contents

1. [Priority Fixes (Apply First)](#1-priority-fixes-apply-first)
2. [Medium-Priority Fixes](#2-medium-priority-fixes)
3. [Low-Priority Fixes](#3-low-priority-fixes)
4. [Application Checklist](#4-application-checklist)

---

## 1. Priority Fixes (Apply First)

### HIGH PRIORITY: Truncated ERD in entity-relationship-diagram.md

**File**: `docs/01-diagrams/entity-relationship-diagram.md`
**Severity**: CRITICAL (Mermaid rendering failure risk)
**Section**: `## 3. Core ERD`

#### Current (Incomplete/Broken)
The ERD block ends abruptly without proper closure, leaving the diagram incomplete and potentially
causing rendering issues.

#### Replace With
Complete the ERD by closing the mermaid block properly and correcting the table name consistency:
- Change `ucp_mcp_tool_usage` to `ucp_mcp_tool_usages` (matches DBD naming convention)
- Ensure the mermaid block has proper closing syntax
- Do not add speculative relationships or unverified tables

**Why**: Completes the truncated diagram conservatively by:
- Fixing the `ucp_mcp_tool_usage` → `ucp_mcp_tool_usages` pluralization inconsistency (matches DBD naming)
- Does not add speculative relationships or unverified tables
- Normalizes naming where already present without expanding scope

---

### HIGH PRIORITY: Fix Services → Browser localStorage implication

**File**: `docs/01-diagrams/data-flow-diagram.md`
**Severity**: HIGH (architectural boundary error)
**Section**: `## 2. Context DFD`

#### Current (Incorrect Architecture)
The diagram shows `Services --> Local (Browser localStorage)`, implying the PHP service layer writes to browser storage.

#### Replace With
Remove the `Services --> Local` edge and add `UI --> Local` edge instead.

**Why**:
- Removes the incorrect implication that PHP services write to browser localStorage
- Adds correct flow: UI/browser manages localStorage state
- Clarifies architectural boundary: services handle server-side persistence; UI handles browser-side state

---

### HIGH PRIORITY: Normalize Table Names

**File**: `docs/01-diagrams/entity-relationship-diagram.md`
**Severity**: MEDIUM (documentation consistency)
**Section**: `### 1.2 Current Inventory Baseline`

#### Current (Inconsistent)
References both `chat_messages` and `ucp_chat_messages`; uses both `ucp_mcp_tool_usage` (singular)
and `ucp_mcp_tool_usages` (plural).

#### Replace With
Standardize to:
- `ucp_chat_messages` (with `ucp_` prefix)
- `ucp_mcp_tool_usages` (plural form to match DBD)

**Why**: Aligns this diagram file to the naming convention used in
`009_DBD_Database_Documentation.md`, reducing inconsistency across documentation.

---

## 2. Medium-Priority Fixes

### MEDIUM: Remove unstable service names

**File**: `docs/01-diagrams/data-flow-diagram.md`
**Severity**: MEDIUM (likely service drift)
**Section**: `### 1.1 Implementation Anchors`

#### Current (Unstable naming)
Lists `LocalStorageService` as a core service.

#### Replace With
Remove `LocalStorageService` and clarify:
- Browser-side local storage helpers manage Local mode state
- Import and migration workflows bridge local data into account-backed persistence when needed
- These are UI/frontend concerns, not PHP service-layer concerns

**Why**:
- Removes the misleading reference to a PHP `LocalStorageService`
- Clarifies that browser localStorage is a UI/browser concern, not a service-layer concern
- Establishes clear boundary between server-side services and client-side state management

---

### MEDIUM: Remove overstated relationships from ERD

**File**: `docs/01-diagrams/entity-relationship-diagram.md`
**Severity**: MEDIUM (relationship clarity)
**Section**: `## 3. Core ERD`

#### Action: Remove these edges if present
- `ucp_users ||--o{ ucp_careers : owns`
- `ucp_characters ||--o{ ucp_support_decks : uses`

**Why**:
- `ucp_users ||--o{ ucp_careers : owns` - careers are primarily owned by characters (which are owned
by users); direct user ownership may denormalize the model
- `ucp_characters ||--o{ ucp_support_decks : uses` - deck usage is a runtime association, not strict
relational ownership; decks are user-owned configurations

**Note**: These were added during speculative expansion but don't appear in the conservative ERD
baseline, so no change needed if already absent.

---

### MEDIUM: Fix service naming drift

**File**: `docs/01-diagrams/decision-tree-flow-diagrams.md`
**Severity**: MEDIUM (service naming consistency)
**Section**: `### 4.1 Implementation Reference`

#### Current (Unstable service names)
References `SkillHintService` and `SkillAnalysisService` as primary anchors.

#### Replace With
Focus on stable anchors:
- Skill operations are backed primarily by `SkillService`
- Acquisitions are recorded through `SkillAcquisition` model
- Hint data is represented by `SkillHint` model

**Why**: Avoids naming services that may not be canonical or may have drifted; focuses on stable
model-level anchors instead.

---

### MEDIUM: Fix external API service naming

**File**: `docs/01-diagrams/system-process-flow-diagrams.md`
**Severity**: MEDIUM (service naming consistency)
**Section**: `### 2.1 Implementation Notes`

#### Current (Inconsistent)
References `ExternalDataService`.

#### Replace With
Use `ExternalAPIService` as the main application-facing integration anchor.

**Why**: Aligns with naming in core docs (002_BRS.md) and clarifies service identity.

---

### MEDIUM: Add dual storage DFD refinement

**File**: `docs/01-diagrams/data-flow-diagram.md`
**Severity**: MEDIUM (architectural clarity)
**Section**: `## 3. Dual Storage DFD`

#### Current
The diagram shows basic branching but doesn't clearly distinguish UUID vs numeric ID usage.

#### Replace With
Enhance to show:
- Local mode uses UUID-oriented identifiers
- Account mode uses numeric database IDs
- Browser manages localStorage state (not PHP services)
- Conversion/import path from Local to Account is explicit

**Why**: Makes the dual storage pattern clearer and reinforces the architectural boundary between
client and server state management.

---

## 3. Low-Priority Fixes

### Add diagram descriptions for accessibility

**Files**: All diagram files
**Severity**: LOW (accessibility/readability)
**Action**: Add one-line diagram description before each Mermaid block

#### Examples for `docs/01-diagrams/data-flow-diagram.md`

Before the Context DFD, add:
```markdown
**Diagram description:** This diagram shows high-level interactions between players, the Laravel 12
application, external services (Ollama, AWS Bedrock, umapyoi.net), and storage systems (browser,
MySQL, Redis, files).
```

Before the Dual Storage DFD, add:
```markdown
**Diagram description:** This diagram shows how user actions branch between Local mode browser
persistence (with UUID identifiers) and Account mode authenticated database writes (with numeric
IDs), including the optional conversion path from Local to Account mode.
```

---

### Fix markdown fence formatting

**File**: `docs/01-diagrams/entity-relationship-diagram.md`
**Problem**: Text blocks may have inconsistent indentation in code fences
**Fix**: Ensure all Mermaid fence opening/closing is at column 0 (standard indentation)

---

## 4. Application Checklist

### Step 1: Backup
```bash
cd docs/01-diagrams
cp entity-relationship-diagram.md entity-relationship-diagram.md.backup
cp data-flow-diagram.md data-flow-diagram.md.backup
cp decision-tree-flow-diagrams.md decision-tree-flow-diagrams.md.backup
cp system-process-flow-diagrams.md system-process-flow-diagrams.md.backup
cp user-workflow-diagrams.md user-workflow-diagrams.md.backup
```

### Step 2: Apply Patches (High Priority First)
- [ ] ERD truncation: Complete ERD conservatively
- [ ] Data flow: Remove Services→localStorage, add UI→localStorage
- [ ] Table naming: Normalize chat_messages and tool_usages
- [ ] Service names: Remove LocalStorageService reference
- [ ] Overstated relationships: Remove or verify user→careers and character→decks edges
- [ ] Service naming: Update to stable anchors (SkillService, etc.)
- [ ] External API naming: ExternalData → ExternalAPI
- [ ] Dual storage clarity: Enhance DFD with UUID/ID distinction

### Step 3: Validation
```bash
# Check for markdownlint issues
npx markdownlint-cli2 docs/01-diagrams/*.md

# Manual spot-check: Render in VS Code or GitHub preview
```

### Step 4: Commit
```bash
git add docs/01-diagrams/
git commit -m "Diagram fixes: Conservative ERD completion, storage boundary clarity, service naming
consistency (2026-03-09)"
```

---

## Effort Summary

| Phase | Content | Effort |
| --- | --- | --- |
| HIGH | ERD, storage boundary, table naming, service removal | 13 min |
| MEDIUM | Overstated relationships, service naming, dual storage | 15 min |
| LOW | Descriptions, fence formatting | 10 min |
| Validation & Commit | Markdownlint, preview, git | 5 min |
| **TOTAL** | Conservative, evidence-based | **~43 minutes** |

---

**Document Status**: Conservative, safe to apply
**Confidence Level**: High (documentation-only fixes with minimal scope change)
**Basis**: See `DIAGRAM_VERIFICATION_REPORT_2026-03-09.md` for underlying findings
