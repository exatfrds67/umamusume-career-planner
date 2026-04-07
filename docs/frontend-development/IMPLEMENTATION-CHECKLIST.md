# Implementation Checklist: UI/UX Fixes

## Before Starting

- [ ] Read UI-UX-FIXES-SUMMARY.md (overview)
- [ ] Read DETAILED-FIX-PLAN.md (code changes)
- [ ] Review WF-002 wireframe for layout spec
- [ ] Review WCAG 2.2 AA standards
- [ ] Backup current create.blade.php

---

## Phase 1: Semantic HTML & Accessibility (30 min)

### 1.1 Update Step Containers (All Steps 1-4)

- [ ] Change `<div class="glass-card">` → `<section class="card">`
- [ ] Add `role="region"` to section
- [ ] Add `aria-labelledby="step-X-heading"` to section
- [ ] Add `aria-label="Step X of 4: [Step Name]"` to section
- [ ] Change `<div class="card-header">` → `<header class="card-header">`
- [ ] Change heading level from `<h3>` → `<h2>` (maintain hierarchy)
- [ ] Add `id="step-X-heading"` to heading

**Affected Lines**: ~200-500 (all 4 step containers)

### 1.2 Update Form Labels (All Inputs)

- [ ] Add `id` attribute to every input/select
- [ ] Add `for="[id]"` to every label
- [ ] Add `aria-describedby` to inputs with help text
- [ ] Add `aria-required="true"` to required inputs
- [ ] Add `aria-invalid="false"` to inputs (update dynamically on error)
- [ ] Add `aria-label` to inputs without visible labels

**Affected Elements**:

- Trainee search input
- Rarity/Distance/Season filter selects
- Character name input
- Character title input
- All form controls in steps 2-4

### 1.3 Add Required Field Indicators

- [ ] Find all required fields (marked with `*`)
- [ ] Add `required` HTML attribute to inputs
- [ ] Add `aria-label="required"` to asterisk spans
- [ ] Add "(required)" text next to asterisk
- [ ] Add text "(optional)" to optional fields

**Affected Fields**: name, scenario_type, trainee (Step 1)

### 1.4 Add Selection Feedback Components

- [ ] Update trainee card selection buttons
- [ ] Add checkmark + "Selected" badge
- [ ] Add `aria-pressed="true/false"` to selection buttons
- [ ] Add `aria-label` with full context (name + rarity)
- [ ] Add multi-modal feedback (border + ring + badge + text)

**Affected Elements**: Trainee selection cards (Step 1)

---

## Phase 2: CSS Updates (20 min)

### 2.1 Add Focus Indicators to app.css

- [ ] Add focus-visible styles for input elements
- [ ] Add focus-visible styles for button elements
- [ ] Add focus-visible styles for [role="button"] elements
- [ ] Ensure 3:1 contrast ratio
- [ ] Add dark mode variants

**Add to**: `resources/css/app.css` (new section)

### 2.2 Update Form Input Styles

- [ ] Verify form-input class has proper styling
- [ ] Verify form-select class has proper styling
- [ ] Add focus-visible outline (3px, 2px offset)
- [ ] Add transition effects

**Location**: app.css (lines ~315-420)

---

## Phase 3: Layout Implementation (60 min)

### 3.1 Create Desktop Sidebar (Show on lg+)

- [ ] Create `<aside>` element (fixed left, width-56)
- [ ] Add step number circles (filled for current, checkmark for completed)
- [ ] Add step labels with descriptions
- [ ] Add keyboard click handlers
- [ ] Add `aria-current="step"` to current step
- [ ] Hide on mobile (lg:block)
- [ ] Add proper z-index (z-40)

**Location**: Insert before main content in create.blade.php

### 3.2 Adjust Main Content Margins

- [ ] Add `lg:ml-56` class to main wrapper
- [ ] Ensure content doesn't overlap sidebar on desktop
- [ ] Verify responsive behavior on mobile/tablet

**Location**: Main content wrapper div

### 3.3 Create Mobile Progress Bar

- [ ] Create progress bar in header (hidden on lg+)
- [ ] Add animated width based on currentStep
- [ ] Add `role="progressbar"` semantics
- [ ] Add aria-valuenow/min/max attributes
- [ ] Add step counter text below bar
- [ ] Verify responsive sizing

**Location**: Insert at top of header for mobile

---

## Phase 4: Validation & Error Messages (30 min)

### 4.1 Add Validation Logic to Alpine Component

- [ ] Create `validationErrors` object in data
- [ ] Create `isValidating` boolean
- [ ] Create `validateStep(step)` method
  - [ ] Step 1: name, scenario_type, trainee
  - [ ] Step 2: parents (if required)
  - [ ] Step 3: deck slots (if required)
  - [ ] Step 4: all fields
- [ ] Update `nextStep()` to call validateStep()
- [ ] Add focus management on validation error

**Location**: Alpine component data + methods

### 4.2 Add Error Message Display Component

- [ ] Create error alert div with role="alert"
- [ ] Add aria-live="polite" for announcements
- [ ] Loop through validationErrors object
- [ ] Display error message for each field
- [ ] Add error icon + red styling
- [ ] Add close button (optional)

**Location**: Add to top of each step content

### 4.3 Update Form Inputs to Mark Errors

- [ ] Add `aria-invalid="true"` when field has error
- [ ] Add `aria-describedby="[error-id]"` when field has error
- [ ] Create error message elements with matching IDs
- [ ] Add error styling (red border, red text)

**Location**: Each form input in steps

---

## Phase 5: Testing & Verification (60 min)

### 5.1 Accessibility Testing

- [ ] Run axe DevTools in Chrome DevTools
  - [ ] Target: 0 violations
  - [ ] Review warnings (should have none)
- [ ] Test keyboard-only navigation
  - [ ] Tab through all inputs
  - [ ] Shift+Tab in reverse
  - [ ] Enter on buttons
  - [ ] Escape to close modals
  - [ ] Arrow keys in menus (if applicable)
- [ ] Test screen reader (NVDA/JAWS simulator)
  - [ ] Read page structure
  - [ ] Read form labels
  - [ ] Read error messages
  - [ ] Announce required fields
  - [ ] Announce current step

### 5.2 Visual Verification

- [ ] Desktop (lg+ breakpoint): sidebar visible
- [ ] Tablet (md breakpoint): sidebar hidden, progress bar visible
- [ ] Mobile (sm- breakpoint): sidebar hidden, progress bar visible
- [ ] Focus indicators visible on all inputs
- [ ] Error messages display with proper styling
- [ ] Selected trainee card shows checkmark badge
- [ ] Progress bar animates as step changes

### 5.3 Functionality Testing

- [ ] All 4 steps render correctly
- [ ] Navigation buttons work (Next/Previous)
- [ ] Sidebar step buttons work (jump to step)
- [ ] Form validation prevents advancing with errors
- [ ] Error messages appear for validation failures
- [ ] Success message appears on step completion
- [ ] localStorage saves/restores data correctly
- [ ] Form submission works end-to-end

### 5.4 Browser Testing

- [ ] Chrome (latest) - Full functionality
- [ ] Firefox (latest) - Full functionality
- [ ] Safari (latest) - Full functionality
- [ ] Mobile Safari (iOS) - Responsive layout
- [ ] Chrome Mobile (Android) - Responsive layout

### 5.5 Contrast Verification

- [ ] Text contrast: 4.5:1 ratio
- [ ] UI components: 3:1 ratio
- [ ] Focus indicators: 3:1 ratio
- [ ] Use WebAIM contrast checker

---

## Parallel Tasks (Can be done simultaneously)

- [ ] **Code Review**: Have someone review changes against DETAILED-FIX-PLAN.md
- [ ] **Documentation**: Update inline code comments
- [ ] **Screenshots**: Take before/after screenshots for comparison
- [ ] **Create Test Cases**: Write Pest tests for new form validation logic

---

## Commit Strategy

### Commit 1: Semantic HTML & Accessibility

```text
Message: "fix: Add semantic HTML and accessibility to character wizard

- Add role='region' and aria-labelledby to step containers
- Fix form label associations with for/id attributes
- Add focus-visible states with 3:1 contrast
- Add aria-required/aria-invalid to required fields
- Replace .glass-card with .card for form sections
- Add multi-modal selection feedback (text+icon+border)

Fixes WCAG 2.2 AA violations per UI-UX-FIXES-SUMMARY.md"
```text

### Commit 2: Layout Implementation

```text
Message: "feat: Implement desktop sidebar and mobile progress bar

- Add fixed sidebar with step indicators (lg+ screens)
- Add animated progress bar for mobile
- Add responsive layout adjustments
- Matches WF-002 wireframe specification

Addresses UI/UX issues Part 4"
```

### Commit 3: Validation & Error Messages

```text
Message: "feat: Add form validation and error messaging

- Add step-specific validation logic
- Add error message display component
- Update form inputs to show aria-invalid state
- Add auto-focus to first error field

Improves UX and form reliability"
```text

---

## Post-Implementation Checklist

- [ ] All tests passing (`php artisan test --compact`)
- [ ] Pint formatting clean (`vendor/bin/pint --dirty`)
- [ ] No errors in get_errors output
- [ ] Accessibility audit: 0 violations
- [ ] Documentation updated in code comments
- [ ] CHANGELOG updated with fixes
- [ ] Screenshots taken for before/after comparison
- [ ] Team reviewed and approved changes
- [ ] Deployed to staging for QA

---

## Rollback Plan (If Issues Arise)

```bash
# Revert to last working commit
git revert [commit-hash]

# Or reset to specific commit
git reset --hard [commit-hash]
```text

Keep current version backed up in case of issues:

```bash
cp resources/views/characters/create.blade.php resources/views/characters/create.blade.php.backup
```

---

## Time Estimates

| Phase                       | Estimated Time | Actual Time | Notes                       |
| --------------------------- | -------------- | ----------- | --------------------------- |
| Phase 1: HTML/Accessibility | 30 min         |             | Complex but straightforward |
| Phase 2: CSS Updates        | 20 min         |             | Focus indicators + styling  |
| Phase 3: Layout             | 60 min         |             | Sidebar + progress bar      |
| Phase 4: Validation         | 30 min         |             | Error handling logic        |
| Phase 5: Testing            | 60 min         |             | Most time-consuming         |
| **Total**                   | **200 min**    |             | **~3.5 hours**              |

---

## Support Resources

- **WCAG 2.2 Specification**: <https://www.w3.org/WAI/WCAG22/quickref/>
- **axe DevTools**: <https://www.deque.com/axe/devtools/>
- **NVDA Screen Reader**: <https://www.nvaccess.org/>
- **WebAIM Contrast Checker**: <https://webaim.org/resources/contrastchecker/>
- **Tailwind CSS v4**: <https://tailwindcss.com/docs>
- **Alpine.js**: <https://alpinejs.dev/>

---

**Status**: Ready for Implementation
**Priority**: Critical (Accessibility + Spec Compliance)
**Assigned To**: [Developer Name]
**Deadline**: [Date]

---

## Sign-Off

- [ ] Code changes reviewed by: ________________
- [ ] Tests passed by: ________________
- [ ] Accessibility verified by: ________________
- [ ] Design approved by: ________________
- [ ] Merged to main by: ________________
- [ ] Date merged: ________________

---

✅ **All phases planned and documented**
🚀 **Ready to begin implementation**
