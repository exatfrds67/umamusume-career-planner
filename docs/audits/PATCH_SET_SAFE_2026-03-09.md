# Minimal Documentation Patches (Conservative)

## Umamusume Career Planner - Documentation Corrections
**Date**: March 9, 2026
**Basis**: Documentation consistency review (docs-only, not code-verified)
**Total Patches**: 4 minimal, safe corrections
**Estimated Effort**: ~10 minutes

---

## Summary

These are **documentation-only corrections** identified in a consistency review of `docs/00-core-docs/`. They address:
- Numeric count inconsistencies within the same document
- Heading accuracy mismatches
- Missing cross-references

**Scope**: These patches do **not** claim code-level verification. They fix document-to-document or
head-to-content mismatches.

---

## Patch 1: Service Count in SDS

**File**: `docs/00-core-docs/004_SDS_Software_Design_Specifications.md`
**Section**: 2.2 Key Directories
**Type**: Count consistency fix

### Current
```markdown
├── Services/                   # Business logic (166 service files)
```

### Replace With
```markdown
├── Services/                   # Business logic (191 service files)
```

**Rationale**: This patch aligns the count in Section 2.2 with the count already used elsewhere in
the same document. It improves internal consistency, but the authoritative number should still be
re-verified against the repository when the document is next refreshed.

---

## Patch 2: Enum Count Heading in SCD

**File**: `docs/00-core-docs/010_SCD_Source_Code_Documentation.md`
**Section**: 3.2 (Enums heading)
**Type**: Heading accuracy fix

### Current
```markdown
### 3.2 Enums (8 Total)
```

### Replace With
```markdown
### 3.2 Enums (12 Total)
```

**Rationale**: The heading should match the number of enum types documented in the section and related core documents.

---

## Patch 3: Livewire Usage Clarification in SCD

**File**: `docs/00-core-docs/010_SCD_Source_Code_Documentation.md`
**Placement**: Add immediately after the existing Livewire component discussion in the file, or
after the first paragraph that mentions `AdvisoryPanel` if no dedicated Livewire subsection exists.
**Type**: Clarification patch (adds scope note, doesn't change facts)

### Current (if Livewire section exists)
```markdown
#### Livewire Components

- `AdvisoryPanel`: AI advisory chat panel
```

### Add After Existing Content
```markdown

#### Important: Livewire Scope

Livewire 4 is used selectively for focused interactive surfaces rather than as a dominant UI pattern
across the application. See `001_SDP.md` §3.3 for architectural guidance.

**Note**: A complete current inventory of Livewire components should be maintained against the
repository. This document provides representative examples. Refer to `app/Livewire/` in the
repository for the definitive component list.
```

**Rationale**:
- Avoids claiming a definitive component count (which would need code verification)
- Redirects readers to the repository for truth
- Reinforces the "selective usage" architectural principle from SDP
- Prevents future confusion about completeness of documentation

---

## Patch 4: Table Naming Convention Note in DBD

**File**: `docs/00-core-docs/009_DBD_Database_Documentation.md`
**Section**: Introduction or Schema Overview (before table listings)
**Type**: Clarification patch (adds convention note)

### Add at Beginning of Table Documentation

```markdown
#### Table Naming Convention

Application-specific tables use the `ucp_` prefix. Table names use lowercase with underscores
(snake_case). Plural forms should be used consistently for junction and relationship tables (for
example, `ucp_mcp_tool_usages`).

Cross-document references to these tables should use this naming consistently.
```

**Rationale**:
- Establishes authoritative naming source
- Helps other docs maintain consistency
- Reduces ambiguity when developers reference tables

---

## Application Checklist

### Step 1: Backup
```bash
cd docs/00-core-docs
cp 004_SDS_Software_Design_Specifications.md 004_SDS_Software_Design_Specifications.md.backup
cp 010_SCD_Source_Code_Documentation.md 010_SCD_Source_Code_Documentation.md.backup
cp 009_DBD_Database_Documentation.md 009_DBD_Database_Documentation.md.backup
```

### Step 2: Apply Patches
- [ ] Patch 1: Update service count in SDS (1 min)
- [ ] Patch 2: Update enum count heading in SCD (1 min)
- [ ] Patch 3: Add Livewire scope clarification to SCD (2 min)
- [ ] Patch 4: Add table naming convention note to DBD (2 min)

### Step 3: Validation
```bash
# Check for markdownlint issues
npx markdownlint-cli2 docs/00-core-docs/{004_SDS,010_SCD,009_DBD}*.md

# Manual spot-check
# Open each file and verify the patches applied correctly and readably
```

### Step 4: Commit
```bash
git add docs/00-core-docs/{004_SDS,010_SCD,009_DBD}*.md
git commit -m "Documentation: Consistency fixes - service count, enum heading, Livewire
clarification, naming convention (2026-03-09)"
```

---

## What These Patches DO

✅ Fix internal document inconsistencies (count mismatches, heading discrepancies)
✅ Add scope clarifications without claiming code verification
✅ Redirect to repository as source of truth for component/implementation details
✅ Establish naming conventions as reference
✅ Can be applied confidently without code review

---

## What These Patches DO NOT Do

❌ Claim code-verified architecture audit
❌ Assert implementation correctness
❌ Provide exhaustive component inventories
❌ Make security claims
❌ Refactor large sections

---

## Why Not Include Other Corrections?

Other potential corrections (e.g., "enumerate all 9 Livewire components," "detail all 191 services
by subdirectory," "correct AI agent status") are **not included because**:

1. **They require code verification** that was not performed in this review
2. **Component counts may have changed** since this audit
3. **Service organization is internal detail** — documenting exact subdirectory structure creates maintenance burden
4. **Product roadmap claims** (agent status) should come from project leadership, not doc review

Documenting those items would **exceed safe scope** of documentation-only consistency review.

---

## Effort Summary

| Patch | Type | Effort |
| --- | --- | --- |
| 1. Service count | Find & replace | 1 min |
| 2. Enum heading | Find & replace | 1 min |
| 3. Livewire clarification | Add new text | 2 min |
| 4. Naming convention | Add new text | 2 min |
| Validation & commit | Shell commands | 3 min |
| **TOTAL** | Conservative, safe | **~10 minutes** |

---

**Document Status**: Safe to apply immediately
**Confidence Level**: High (documentation-only fixes)
**Review Basis**: See `AUDIT_FINDINGS_SAFE_2026-03-09.md` for full findings
