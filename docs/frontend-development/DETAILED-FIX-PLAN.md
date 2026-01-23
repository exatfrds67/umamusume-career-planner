# Detailed Fix Plan: Character Creation Wizard UI/UX Corrections

**Document Purpose**: Specific code changes needed to resolve character creation wizard UI/UX issues per WF-002 spec and WCAG 2.2 AA compliance.

---

## Fix 1: Update Step Containers with Proper Semantics

### Current Code (INCORRECT)

```blade
<div x-show="currentStep === 1" x-transition class="glass-card rounded-xl">
    <div class="card-header">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Step 1: Trainee & Scenario</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Choose a trainee and scenario...</p>
    </div>
    <div class="card-body space-y-6">
        <!-- content -->
    </div>
</div>
```

### Fixed Code (CORRECT)

```blade
<section x-show="currentStep === 1" x-transition 
    class="card rounded-lg" 
    role="region" 
    aria-labelledby="step-1-heading"
    aria-live="polite"
    aria-label="Step 1 of 4: Trainee and Scenario Selection">
    <header class="card-header border-b border-gray-200 dark:border-gray-700">
        <h2 id="step-1-heading" class="text-lg font-semibold text-gray-900 dark:text-white">
            Step 1: Trainee & Scenario
        </h2>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Choose a trainee and scenario to start your run
        </p>
    </header>
    <div class="card-body space-y-6">
        <!-- content -->
    </div>
</section>
```

### Changes Made

- `<div>` → `<section>` (semantic HTML)
- `<div class="card-header">` → `<header class="card-header">` (semantic header)
- Removed `.glass-card` class (wrong component, should be `.card`)
- Added `role="region"` for step identification
- Added `aria-labelledby="step-1-heading"` linking to heading
- Added `aria-live="polite"` for dynamic content updates
- Added `aria-label` for full context
- Promoted heading to `<h2>` (maintains hierarchy from page `<h1>`)

---

## Fix 2: Add Proper Label Associations

### Current Code (INCORRECT)

```blade
<div class="flex-1">
    <label class="form-label text-xs">Search</label>
    <input type="text" x-model="filters.query" placeholder="Search trainee"
        class="form-input">
</div>
```

### Fixed Code (CORRECT)

```blade
<div class="flex-1">
    <label for="trainee-search" class="form-label text-sm font-medium">
        Search <span class="text-gray-400">(optional)</span>
    </label>
    <input 
        id="trainee-search"
        type="text" 
        x-model="filters.query" 
        placeholder="Search by trainee name"
        aria-describedby="trainee-search-help"
        class="form-input"
        @keydown.down="moveFocus($event)"
        @keydown.up="moveFocus($event)">
    <p id="trainee-search-help" class="form-help text-xs mt-1">
        Filter trainees by name (e.g., "Special Week")
    </p>
</div>
```

### Changes Made

- Added `id="trainee-search"` to input
- Added `for="trainee-search"` to label (explicit association)
- Added `aria-describedby="trainee-search-help"` for help text
- Added `<p id="trainee-search-help">` with helpful description
- Improved placeholder text (more descriptive)
- Added keyboard navigation support with `@keydown` directives
- Added "(optional)" indicator for non-required fields

---

## Fix 3: Add Required Field Indicators (Accessible)

### Current Code (INCORRECT)

```blade
<label for="name" class="form-label">Character Name <span class="text-red-500">*</span></label>
<input id="name" x-model="formData.name" class="form-input" required>
```

### Fixed Code (CORRECT)

```blade
<label for="name" class="form-label font-medium">
    Character Name 
    <span class="text-red-500" aria-label="required">*</span>
    <span class="text-xs text-gray-500">(required)</span>
</label>
<input 
    id="name" 
    x-model="formData.name" 
    class="form-input" 
    required
    aria-required="true"
    aria-invalid="false"
    @invalid="onFieldInvalid($event)">
```

### Changes Made

- Added `aria-label="required"` to asterisk (screen reader reads it)
- Added text "(required)" for visual redundancy
- Added `required` attribute to input (semantic HTML)
- Added `aria-required="true"` for ARIA compliance
- Added `aria-invalid="false"` (updated to true on validation error)
- Added `@invalid` handler for validation feedback

---

## Fix 4: Add Visible Focus Indicators

### Add to resources/css/app.css

```css
/* Enhanced focus indicators for accessibility (WCAG 2.2 AA) */
input:focus-visible,
select:focus-visible,
textarea:focus-visible,
button:focus-visible,
[role="button"]:focus-visible {
    outline: 3px solid var(--color-primary-500);
    outline-offset: 2px;
    /* Ensures 3:1 contrast ratio */
}

/* Dark mode focus indicators */
.dark input:focus-visible,
.dark select:focus-visible,
.dark textarea:focus-visible,
.dark button:focus-visible,
.dark [role="button"]:focus-visible {
    outline: 3px solid var(--color-primary-300);
    outline-offset: 2px;
}

/* Ensure focus visible even without JavaScript */
input:focus,
select:focus,
textarea:focus,
button:focus,
[role="button"]:focus {
    outline: 3px solid var(--color-primary-500);
    outline-offset: 2px;
}
```

### Update Trainee Selection Buttons

```blade
<button type="button" @click="selectTrainee(trainee)"
    class="text-left p-4 rounded-lg border-2 transition-all 
           hover:bg-gray-50 dark:hover:bg-gray-700/50
           focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2
           focus-visible:outline-none"
    :class="formData.trainee && formData.trainee.id === trainee.id ?
        'border-primary-500 bg-primary-50 dark:bg-primary-900/20' :
        'border-gray-200 dark:border-gray-700'"
    :aria-pressed="formData.trainee && formData.trainee.id === trainee.id">
    <!-- button content -->
</button>
```

### Changes Made

- Added explicit focus-visible outline (3px for visibility)
- Added outline-offset (visual separation)
- Added focus indicators for all interactive elements
- Added dark mode variant with lighter color (3:1 contrast)
- Added to selection buttons for clear visual feedback

---

## Fix 5: Replace Color-Only Indicators with Multi-Modal Feedback

### Current Code (INCORRECT)

```blade
:class="formData.trainee && formData.trainee.id === trainee.id ?
    'border-primary-500 bg-primary-50 dark:bg-primary-900/20' :
    'border-gray-200 dark:border-gray-700'"
```

### Fixed Code (CORRECT)

```blade
<div class="relative">
    <!-- Selection indicator badge -->
    <template x-if="formData.trainee && formData.trainee.id === trainee.id">
        <div class="absolute top-2 right-2 inline-flex items-center gap-1 
                    px-2 py-1 bg-primary-500 text-white rounded-full text-xs font-semibold"
             role="status"
             aria-live="polite">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            <span>Selected</span>
        </div>
    </template>

    <!-- Main button with multi-modal feedback -->
    <button type="button" @click="selectTrainee(trainee)"
        class="text-left w-full p-4 rounded-lg border-2 transition-all 
               hover:shadow-md focus-visible:ring-2 focus-visible:ring-primary-500"
        :class="formData.trainee && formData.trainee.id === trainee.id ?
            'border-primary-500 bg-primary-50 dark:bg-primary-900/20 ring-2 ring-primary-500 ring-offset-1' :
            'border-gray-200 dark:border-gray-700 hover:border-primary-300'"
        :aria-pressed="formData.trainee && formData.trainee.id === trainee.id"
        :aria-label="`Select ${trainee.name} - ${trainee.rarity} rarity`">
        <!-- button content -->
    </button>
</div>
```

### Changes Made

- Added checkmark icon + "Selected" text label (not just color)
- Added ring around selected state (multiple visual cues)
- Added hover states (shadow lift)
- Added `aria-pressed="true/false"` (announces state)
- Added `aria-label` with full context (trainee name + rarity)
- Added `role="status"` + `aria-live="polite"` for selection announcements

---

## Fix 6: Add Desktop Sidebar (WF-002 Spec)

### Add New Component: Step Sidebar

```blade
<!-- Desktop Sidebar Stepper (lg+ screens) -->
<aside class="hidden lg:block fixed left-0 top-0 h-full w-56 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 shadow-sm z-40 pt-20">
    <nav class="space-y-2 p-4" role="navigation" aria-label="Wizard steps">
        <template x-for="(step, index) in stepNames" :key="index">
            <button 
                type="button"
                @click="goToStep(index + 1)"
                class="w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all text-left group"
                :class="currentStep === index + 1 ?
                    'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-300 font-semibold' :
                    'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800'"
                :aria-current="currentStep === index + 1 ? 'step' : false"
                :aria-label="`Step ${index + 1}: ${step} ${currentStep === index + 1 ? '(current)' : ''}`">
                
                <!-- Step number circle -->
                <div class="shrink-0 w-8 h-8 rounded-full flex items-center justify-center font-semibold transition-colors"
                     :class="currentStep === index + 1 ?
                        'bg-primary-500 text-white' :
                        currentStep > index + 1 ?
                        'bg-green-500 text-white' :
                        'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300'">
                    
                    <!-- Checkmark for completed steps -->
                    <template x-if="currentStep > index + 1">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </template>
                    
                    <!-- Step number for current/future steps -->
                    <template x-if="currentStep <= index + 1">
                        <span x-text="index + 1"></span>
                    </template>
                </div>
                
                <!-- Step label -->
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate" x-text="step"></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                        <template x-if="index === 0">Step of trainee selection</template>
                        <template x-if="index === 1">Parents & inheritance</template>
                        <template x-if="index === 2">Support deck setup</template>
                        <template x-if="index === 3">Review & confirm</template>
                    </p>
                </div>
            </button>
        </template>
    </nav>
</aside>

<!-- Adjust main content to account for sidebar on lg+ screens -->
<main class="lg:ml-56">
    <!-- Existing wizard content -->
</main>
```

### Changes Made

- Created desktop-only sidebar (hidden on mobile/tablet)
- Shows all 4 steps with completion status
- Current step highlighted with primary color
- Completed steps show checkmark
- Responsive text labels per step
- Proper ARIA labels (`aria-current="step"`)
- Click to jump to any step

---

## Fix 7: Add Mobile Progress Bar (WF-002 Spec)

### Update Header Section

```blade
<!-- Update header progress indicator for mobile -->
<div class="lg:hidden">
    <div class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 py-3 px-4">
        <!-- Progress bar -->
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mb-2">
            <div class="bg-primary-500 h-2 rounded-full transition-all"
                 :style="`width: ${(currentStep / 4) * 100}%`"
                 role="progressbar"
                 :aria-valuenow="currentStep"
                 aria-valuemin="1"
                 aria-valuemax="4"
                 :aria-label="`Progress: ${currentStep} of 4 steps complete`">
            </div>
        </div>
        
        <!-- Step indicator text -->
        <p class="text-center text-sm font-medium text-gray-900 dark:text-white">
            <span class="font-semibold text-primary-600 dark:text-primary-300" x-text="currentStep"></span>
            <span class="text-gray-600 dark:text-gray-400"> / 4: </span>
            <span x-text="stepNames[currentStep - 1]"></span>
        </p>
    </div>
</div>
```

### Changes Made

- Added progress bar visual on mobile
- Used proper `<div role="progressbar">` semantics
- Added `aria-valuenow`, `aria-valuemin`, `aria-valuemax`
- Added text progress indicator (not just visual)
- Mobile-only with lg:hidden class

---

## Fix 8: Add Step Validation Feedback

### Add to Alpine Component

```javascript
// In characterWizard() data:
validationErrors: {},
isValidating: false,

// In methods:
async validateStep(step) {
    this.isValidating = true;
    this.validationErrors = {};
    
    try {
        switch (step) {
            case 1:
                if (!this.formData.name || !this.formData.name.trim()) {
                    this.validationErrors.name = 'Character name is required';
                }
                if (!this.formData.scenario_type) {
                    this.validationErrors.scenario_type = 'Scenario type is required';
                }
                if (!this.formData.trainee) {
                    this.validationErrors.trainee = 'Please select a trainee';
                }
                break;
            case 2:
                // Parent/factor validation
                break;
            case 3:
                // Deck validation
                break;
        }
        
        return Object.keys(this.validationErrors).length === 0;
    } finally {
        this.isValidating = false;
    }
},

nextStep() {
    if (this.validateStep(this.currentStep)) {
        this.currentStep = Math.min(4, this.currentStep + 1);
        this.saveToStorage();
    } else {
        // Focus on first error
        setTimeout(() => {
            const firstError = document.querySelector('[aria-invalid="true"]');
            if (firstError) firstError.focus();
        }, 0);
    }
}
```

### Add Error Display Component

```blade
<!-- Add to each step after the header -->
<template x-if="Object.keys(validationErrors).length > 0">
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6"
         role="alert"
         aria-live="polite"
         aria-atomic="true">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-red-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            <div class="flex-1">
                <h3 class="font-medium text-red-900 dark:text-red-100 mb-2">Please fix the following errors:</h3>
                <ul class="space-y-1 text-sm text-red-800 dark:text-red-200">
                    <template x-for="(error, field) in validationErrors" :key="field">
                        <li>• <span x-text="error"></span></li>
                    </template>
                </ul>
            </div>
        </div>
    </div>
</template>
```

### Changes Made

- Added step-specific validation logic
- Added validation error messages (not just blocking)
- Added error display component with `role="alert"`
- Auto-focus on first error for accessibility
- All errors listed with explanations

---

## Implementation Checklist

### Phase 1: Semantic HTML & Accessibility

- [ ] Update all step containers to use `<section>` + `role="region"`
- [ ] Add label + id associations for all form inputs
- [ ] Add aria-describedby for help text
- [ ] Add aria-required + aria-invalid for required fields
- [ ] Add focus-visible styles to CSS

### Phase 2: Component Updates

- [ ] Replace `.glass-card` with `.card` in all steps
- [ ] Update header from `<div>` to `<header>`
- [ ] Add checkmark + text selection indicators
- [ ] Add role="status" to state announcements

### Phase 3: Layout Implementation

- [ ] Add desktop sidebar with step indicators
- [ ] Add mobile progress bar
- [ ] Adjust main content margins for sidebar

### Phase 4: Validation & Feedback

- [ ] Add validation logic to Alpine component
- [ ] Add error message display component
- [ ] Add success feedback messages
- [ ] Test keyboard navigation

### Phase 5: Testing & Verification

- [ ] WCAG 2.2 AA automated audit
- [ ] Screen reader testing (NVDA/JAWS simulator)
- [ ] Keyboard-only navigation testing
- [ ] Color contrast verification
- [ ] Browser verify in Chrome, Firefox, Safari

---

**Total Changes**: ~8 major fixes across HTML, CSS, and Alpine.js  
**Estimated Time**: 2-3 hours implementation + testing  
**Breaking Changes**: None (all changes are additive/improved)
