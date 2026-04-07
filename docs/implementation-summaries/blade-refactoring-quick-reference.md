# Blade Asset Refactoring - Quick Reference Guide

## Pattern: Extracting Inline JavaScript

### Before (Inline in Blade)

```blade
@push('scripts')
<script>
    function myFunction() {
        // logic here
    }

    document.addEventListener('DOMContentLoaded', () => {
        myFunction();
    });
</script>
@endpush
```text

### After (Extracted to JS file)

**Blade file** (`resources/views/my-page.blade.php`):

```blade
{{-- Inject data if needed --}}
<script>
window.myPageData = {
    userId: @json($user->id),
    settings: @json($settings)
};
</script>

@vite(['resources/js/pages/my-page.js'])
```text

**JS file** (`resources/js/pages/my-page.js`):

```javascript
/**
 * My Page Script
 * Description of what this script does
 */

// Access injected data
const { userId, settings } = window.myPageData || {};

function myFunction() {
    // logic here
}

// Auto-initialize
document.addEventListener('DOMContentLoaded', () => {
    myFunction();
});
```text

**Vite config** (`vite.config.js`):

```javascript
input: [
    // ... existing entries
    "resources/js/pages/my-page.js",
],
```

---

## Pattern: Alpine.js Components

### Before

```blade
<div x-data="{ count: 0 }">
    <button @click="count++">Increment</button>
    <span x-text="count"></span>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('myComponent', () => ({
            // component logic
        }));
    });
</script>
@endpush
```text

### After

**Blade file**:

```blade
<script>
window.myComponentData = {
    initialValue: @json($value)
};
</script>

<div x-data="myComponent()">
    <!-- component template -->
</div>

@vite(['resources/js/pages/my-component.js'])
```text

**JS file**:

```javascript
const { initialValue } = window.myComponentData || {};

document.addEventListener('alpine:init', () => {
    Alpine.data('myComponent', () => ({
        count: initialValue || 0,

        increment() {
            this.count++;
        }
    }));
});
```text

---

## Pattern: API Calls with Error Handling

```javascript
/**
 * Fetch data with retry logic
 */
async function fetchDataWithRetry(url, maxRetries = 3) {
    let lastError = null;

    for (let attempt = 1; attempt <= maxRetries; attempt++) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ /* data */ })
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            lastError = error;

            if (attempt < maxRetries) {
                // Exponential backoff
                await new Promise(resolve =>
                    setTimeout(resolve, Math.pow(2, attempt) * 1000)
                );
            }
        }
    }

    throw lastError;
}
```

---

## Pattern: Global Functions for Blade onclick

When Blade templates need onclick handlers:

**JS file**:

```javascript
function myGlobalFunction() {
    // logic
}

// Expose globally
window.myGlobalFunction = myGlobalFunction;
```text

**Blade file**:

```blade
<button onclick="myGlobalFunction()">Click Me</button>

@vite(['resources/js/pages/my-page.js'])
```text

---

## Pattern: Dynamic Form Fields

```javascript
document.addEventListener('DOMContentLoaded', function() {
    const selectElement = document.getElementById('my-select');
    const conditionalField = document.getElementById('conditional-field');

    function toggleFields() {
        const value = selectElement.value;

        if (value === 'option1') {
            conditionalField.classList.remove('hidden');
            conditionalField.querySelector('input').required = true;
        } else {
            conditionalField.classList.add('hidden');
            conditionalField.querySelector('input').required = false;
        }
    }

    selectElement.addEventListener('change', toggleFields);
    toggleFields(); // Initialize
});
```text

---

## Pattern: Event Communication

**Dispatching events**:

```javascript
window.dispatchEvent(new CustomEvent('my-event', {
    detail: { message: 'Hello' }
}));
```

**Listening for events**:

```javascript
window.addEventListener('my-event', (event) => {
    console.log(event.detail.message);
});
```text

---

## Pattern: Toast Notifications

```javascript
// Success toast
window.dispatchEvent(new CustomEvent('toast', {
    detail: {
        type: 'success',
        message: 'Operation completed!'
    }
}));

// Error toast
window.dispatchEvent(new CustomEvent('toast', {
    detail: {
        type: 'error',
        message: 'Something went wrong'
    }
}));
```text

---

## Directory Structure

```text
resources/
├── js/
│   ├── app.js                          # Global JS entry point
│   ├── core/                           # Core utilities
│   │   ├── EventBus.js
│   │   ├── ThemeSystem.js
│   │   └── AccessibilitySystem.js
│   └── pages/                          # Page-specific scripts
│       ├── support-cards/
│       │   └── deck-builder.js
│       ├── training/
│       │   └── predictions.js
│       ├── characters/
│       │   └── factors-manage.js
│       └── ai/
│           └── chat.js
├── css/
│   ├── app.css                         # Global CSS entry point
│   ├── components/                     # Component styles
│   │   ├── animations.css
│   │   ├── character-card.css
│   │   └── ...
│   └── pages/                          # Page-specific styles (if needed)
│       └── my-page.css
└── views/
    └── ...                             # Blade templates
```

---

## Checklist for New Refactoring

- [ ] Identify inline `<script>` or `<style>` blocks
- [ ] Determine scope (global vs page-specific)
- [ ] Create new file in appropriate directory
- [ ] Extract logic to new file
- [ ] Identify Blade variables needed
- [ ] Add data injection script in Blade
- [ ] Update Blade to use `@vite()` directive
- [ ] Add entry point to `vite.config.js`
- [ ] Test functionality
- [ ] Run `vendor/bin/pint` for formatting
- [ ] Commit changes

---

## Common Pitfalls

### ❌ Don't: Use Blade syntax in JS files

```javascript
// WRONG - This won't work in .js files
const userId = {{ $user->id }};
```text

### ✅ Do: Inject data via window object

```blade
{{-- In Blade --}}
<script>
window.userData = { id: @json($user->id) };
</script>
```text

```javascript
// In JS file
const { id } = window.userData || {};
```text

---

### ❌ Don't: Forget CSRF token

```javascript
// WRONG - Missing CSRF token
fetch('/api/endpoint', { method: 'POST' });
```

### ✅ Do: Include CSRF token

```javascript
// CORRECT
fetch('/api/endpoint', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
    }
});
```text

---

### ❌ Don't: Forget to add to vite.config.js

```javascript
// File created but not added to Vite config = won't be compiled
```text

### ✅ Do: Always update vite.config.js

```javascript
input: [
    // ... existing
    "resources/js/pages/my-new-page.js",
],
```text

---

## Testing Commands

```bash
# Development with hot reload
npm run dev

# Production build
npm run build

# Check for errors
npm run build 2>&1 | grep -i error

# Format PHP code
vendor/bin/pint

# Run tests
php artisan test --compact
```

---

## Resources

- [Vite Documentation](https://vitejs.dev/)
- [Laravel Vite Plugin](https://laravel.com/docs/12.x/vite)
- [Alpine.js Documentation](https://alpinejs.dev/)
- [Tailwind CSS v4](https://tailwindcss.com/docs)
- Project: `docs/implementation-summaries/blade-asset-refactoring-summary.md`
