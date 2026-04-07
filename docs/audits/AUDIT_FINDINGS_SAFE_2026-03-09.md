# Documentation Consistency Review (Conservative)

## Umamusume Pretty Derby Career Planner
**Date**: March 9, 2026
**Review Scope**: `docs/00-core-docs/` documentation consistency check
**Scope Limitations**: This review examines **documents only**. It does not include code-level
verification. Claims about implementation, architecture correctness, or codebase structure are based
on document examination and should not be treated as code-verified audits.
**Status**: Complete

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Documentation Inconsistencies Found](#2-documentation-inconsistencies-found)
3. [Safe Minimal Patches](#3-safe-minimal-patches)
4. [Items Noted But Outside Scope](#4-items-noted-but-outside-scope)

---

## 1. Executive Summary

### 1.1 What This Review Covers

This review examined **five core documentation files** for internal consistency. This was a targeted
consistency review, not an exhaustive audit of every documentation file in the repository.
- `001_SDP.md` (Software Development Plan) — checked tech stack references
- `004_SDS.md` (Software Design Specifications) — checked architectural descriptions and component counts
- `010_SCD.md` (Source Code Documentation) — checked component/service enumeration and enum counts
- `000_MASTER_GLOSSARY.md` (Terminology) — checked definition consistency
- `000_IMPLEMENTATION_VERIFICATION_MATRIX.md` (Status tracking) — checked feature claims

### 1.2 What This Review Does NOT Do

- ❌ Code-level verification of services, models, or routes
- ❌ Security analysis or vulnerability assessment
- ❌ Architectural correctness judgment (only consistency check)
- ❌ Full codebase inventory (no exhaustive directory scans)
- ❌ Performance, efficiency, or quality analysis

### 1.3 Issues Found

**Total Inconsistencies**: 4 safe to claim
- **Numeric count divergences**: Service count mismatch (191 vs 166), Enum heading mismatch (8 vs 12)
- **Component enumeration gaps**: Livewire components listed incompletely in some sections
- **Cross-document naming inconsistency**: Table/service names vary across docs
- **Documentation missing**: Some sections lack expected descriptions

---

## 2. Documentation Inconsistencies Found

### Issue 1: Service Count Divergence in SDS 🟡
**Severity**: MEDIUM (documentation credibility)
**File**: `docs/00-core-docs/004_SDS_Software_Design_Specifications.md`

**Finding**:
This document contains conflicting claims about service file count:
- **In Section 2.2 (Directory Listing)**: Claims "Business logic (166 service files)"
- **In Section 4.1 (Service Overview)**: References "191 service files"

**Example**:
```markdown
# Current (inconsistent)
├── Services/                   # Business logic (166 service files)
```

**Safe Minimal Fix**:
Update the comment in Section 2.2 directory listing to use the same count as 4.1, or explicitly note
which count is authoritative.

**Why This Matters**:
Readers get conflicting information about codebase size in the same document. The discrepancy (25 files) is not trivial.

---

### Issue 2: Enum Count Heading Mismatch 🟡
**Severity**: LOW (heading accuracy)
**File**: `docs/00-core-docs/010_SCD_Source_Code_Documentation.md`

**Finding**:
Section 3.2 has a heading that claims:
```markdown
### 3.2 Enums (8 Total)
```

while the same document later lists or references more than 8 enum values (StorageMode, Mood,
RunningStyle, CareerPhase, RaceDistance, AlertType, Priority, RecommendationType, ConsentType,
DeletionStatus, AffinityGrade, SparkType = 12 distinct types found in other documentation sections).

**Safe Minimal Fix**:
Update heading to reflect the count actually enumerated in the document or the count consistent with
`000_MASTER_GLOSSARY.md`.

---

### Issue 3: Livewire Component Enumeration Gaps 🟡
**Severity**: MEDIUM (documentation completeness)
**Files**:
- `docs/00-core-docs/004_SDS_Software_Design_Specifications.md` (Section 2.2)
- `docs/00-core-docs/010_SCD_Source_Code_Documentation.md` (Section 2.1, 3.2)

**Finding**:
Documentation mentions Livewire but only specifically names some components:
- `AdvisoryPanel` (mentioned in multiple places)
- Sidebar comments in SDS suggest Livewire is used only for admin/analytics sidebars

Other sections of docs (PRDs, workflows) reference interactive UI features (modals, dropdowns, tabs)
without clearly attributing them to Livewire components.

**Safe Minimal Fix**:
Clarify in relevant sections:
- Which interactive surfaces use Livewire
- Which use Alpine.js or vanilla JavaScript
- Whether "selective Livewire usage" (claimed in SDP) is accurately reflected in component documentation

**Why This Matters**:
Readers benefit from clearer guidance on which interactive surfaces are documented as Livewire-based
versus Alpine.js or other UI patterns.

---

### Issue 4: Inconsistent Table Naming Across Documents 🟡
**Severity**: LOW-MEDIUM (cross-document consistency)
**Files**: Multiple (`008_SIS.md`, `009_DBD.md`, diagram files)

**Finding**:
Table/model names are referenced inconsistently:
- Some sections use `chat_messages`, others use `ucp_chat_messages`
- Some sections reference `ucp_mcp_tool_usage`, others use `ucp_mcp_tool_usages`
- This creates ambiguity for developers implementing against the schema

**Safe Minimal Fix**:
Choose one naming convention (preferably with `ucp_` prefix and consistent pluralization) and standardize across docs.

---

## 3. Safe Minimal Patches

### Patch 1: Service Count in SDS
**File**: `docs/00-core-docs/004_SDS_Software_Design_Specifications.md`
**Section**: 2.2 Key Directories (or wherever the directory tree appears)

**Current (line ~40)**:
```markdown
├── Services/                   # Business logic (166 service files)
```

**Replace With**:
```markdown
├── Services/                   # Business logic (191 service files)
```

---

### Patch 2: Enum Count in SCD
**File**: `docs/00-core-docs/010_SCD_Source_Code_Documentation.md`
**Section**: 3.2 Enums heading

**Current**:
```markdown
### 3.2 Enums (8 Total)
```

**Replace With**:
```markdown
### 3.2 Enums (12 Total)
```

---

### Patch 3: Livewire Usage Pattern Self-Reference
**File**: `docs/00-core-docs/010_SCD_Source_Code_Documentation.md`
**Section**: 3.2 or 2.1 (Livewire documentation)

**Add Note**:
```markdown
#### Livewire Usage Scope

Livewire 4 is used selectively for focused interactive surfaces rather than as the dominant UI
pattern across all screens. See `001_SDP.md` §3.3 for architectural guidance.

Component examples: `AdvisoryPanel` (AI advisory chat), plus focused components in Admin, Analytics,
Settings, Privacy, and Simulation areas.

**Note**: Full component inventory should be maintained against current repository; this document
lists representative examples.
```

---

### Patch 4: Table Naming Convention Note
**File**: `docs/00-core-docs/009_DBD_Database_Documentation.md`
**At the beginning or in schema overview section**

**Add**:
```markdown
#### Naming Convention

This schema uses the `ucp_` prefix for application tables. Table names use lowercase with
underscores (snake_case). Plural forms are standard for junction/relationship tables (e.g.,
`ucp_mcp_tool_usages`).

Cross-document references to these tables should use this consistent naming.
```

---

## 4. Items Noted But Outside Scope

### A. Service Layer Architecture Correctness
**Status**: Not verified in this review
**Why**: Would require code-level inspection of class hierarchies, method signatures, and business
logic implementation. Documentation describes patterns, but implementation correctness is outside
documentation-only scope.

### B. Dual Storage Mode Implementation
**Status**: Documentation describes the pattern; implementation details not verified
**Why**: Document claims `StorageMode` enum and `LocalStorageService` are central to dual storage.
This is plausible based on naming, but code-level verification is needed to confirm actual
implementation.

### C. Security Assessment
**Status**: Not performed
**Why**: Documentation cannot be audited for security. Authorization patterns are described, but
actual vulnerability assessment requires code and runtime analysis.

### D. AI/Neuron Agent Status
**Status**: Documents disagree on agent counts (some claim 9, some claim 6); root cause unknown
**Why**: This appears to reflect product roadmap changes. Without clear timeline documentation,
claiming "correct" status is impossible. Recommend project owner clarify current agent status.

### E. Exact Component Inventory
**Status**: Directory structure noted but not comprehensively verified
**Why**: Counting files in directories doesn't guarantee they are discrete components or that counts
haven't changed since this review. Re-verify against current repository before treating counts as
canonical.

---

## Summary & Recommendations

### What to Do With These Findings

1. **Apply patches 1–4** (estimated 10 minutes) — these are documentation-only corrections with no code implications
2. **Clarify Livewire component scope** with the development team — ensure documentation matches
actual component inventory
3. **Audit table naming** in code against `009_DBD.md` — choose a standard and enforce it
4. **Document AI agent status** formally — clarify which agents are production-ready, in-progress, or planned

### What NOT to Do

- ❌ Treat this review as code-verified architecture audit
- ❌ Make product roadmap decisions based on agent count discrepancies found here
- ❌ Use component counts from this review without re-verification
- ❌ Assume security or correctness based on documentation review alone

---

**Document Status**: Safe for use as documentation consistency findings
**Confidence Level**: High for inconsistency identification; low for implementation claims
**Next Step**: Apply minimal patches and assign follow-up code verification to repository maintainers
