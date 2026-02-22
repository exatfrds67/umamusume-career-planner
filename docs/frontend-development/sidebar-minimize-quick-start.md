# Sidebar Minimize Feature - Quick Start Guide

**Document Version**: 1.0.0  
**Date**: February 8, 2026  
**Related**: [Implementation Plan](./sidebar-minimize-implementation-plan.md) | [Visual
Reference](./sidebar-minimize-visual-reference.md)

---

## Quick Implementation Steps

### Step 1: Create Alpine.js Store (5 minutes)

**File**: `resources/js/stores/sidebar.js`

```javascript
export default {
    minimized: localStorage.getItem('sidebar-minimized') === 'true',
    
    toggle() {
        this.minimized = !this.minimized;
        localStorage.setItem('sidebar-minimized', this.minimized);
        window.dispatchEvent(new CustomEvent('sidebar-toggled', {
            detail: { minimized: this.minimized }
        }));
    }
};
```text

**Register in**: `resources/js/app.js`

```javascript
import sidebarStore from './stores/sidebar';
Alpine.store('sidebar', sidebarStore);
```

---

### Step 2: Update Layout (10 minutes)

**File**: `resources/views/layouts/app.blade.php`

**Find**:

```blade
<div class="hidden lg:fixed lg:inset-y-0 lg:left-0 lg:z-50 lg:flex lg:w-72 lg:flex-col ...">
```text

**Replace with**:

```blade
<div class="hidden lg:fixed lg:inset-y-0 lg:left-0 lg:z-50 lg:flex lg:flex-col transition-all duration-300 ..."
     :class="$store.sidebar.minimized ? 'lg:w-20' : 'lg:w-72'">
```

**Find**:

```blade
<div class="lg:pl-72 flex flex-col min-h-screen ...">
```text

**Replace with**:

```blade
<div class="flex flex-col min-h-screen transition-all duration-300 ..."
     :class="$store.sidebar.minimized ? 'lg:pl-20' : 'lg:pl-72'">
```

---

### Step 3: Add Toggle Button (15 minutes)

**File**: `resources/views/components/app/sidebar.blade.php`

**Find the logo section** (around line 10):

```blade
<div class="flex h-16 shrink-0 items-center gap-3 border-b ...">
```text

**Replace with**:

```blade
<div class="flex h-16 shrink-0 items-center border-b border-gray-200 dark:border-gray-700"
     :class="$store.sidebar.minimized ? 'justify-center' : 'justify-between px-6'">
    <!-- Logo -->
    <div class="flex items-center gap-3" :class="$store.sidebar.minimized ? 'flex-col' : ''">
        <img src="/images/app_logo/uma_musume_race_planner_logo_128.png"
            alt="{{ config('app.name') }} logo" 
            class="h-10 w-10 shrink-0">
        <span x-show="!$store.sidebar.minimized" 
              x-transition
              class="text-base font-bold text-primary-600 dark:text-primary-400 leading-tight">
            Umamusume<br>Career Planner
        </span>
    </div>
    
    <!-- Toggle Button (Desktop Only) -->
    <button @click="$store.sidebar.toggle()"
            type="button"
            class="hidden lg:flex p-2 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            :aria-label="$store.sidebar.minimized ? 'Expand sidebar' : 'Minimize sidebar'"
            :aria-expanded="!$store.sidebar.minimized">
        <!-- Minimize Icon -->
        <svg x-show="!$store.sidebar.minimized" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m18.75 4.5-7.5 7.5 7.5 7.5m-6-15L5.25 12l7.5 7.5" />
        </svg>
        <!-- Expand Icon -->
        <svg x-show="$store.sidebar.minimized" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m5.25 4.5 7.5 7.5-7.5 7.5m6-15 7.5 7.5-7.5 7.5" />
        </svg>
    </button>
</div>
```

---

### Step 4: Update Navigation Items (20 minutes)

**File**: `resources/views/components/app/sidebar.blade.php`

**For each navigation link**, update the structure:

**Before**:

```blade
<li>
    <a href="{{ route('dashboard') }}"
       class="group flex gap-x-3 rounded-md p-2 ...">
        <svg class="h-6 w-6 shrink-0" ...>...</svg>
        Dashboard
    </a>
</li>
```text

**After**:

```blade
<li>
    <a href="{{ route('dashboard') }}"
       class="group flex gap-x-3 rounded-md p-2 ..."
       :class="$store.sidebar.minimized ? 'justify-center' : ''"
       :title="$store.sidebar.minimized ? 'Dashboard' : ''">
        <svg class="h-6 w-6 shrink-0" ...>...</svg>
        <span x-show="!$store.sidebar.minimized" x-transition>Dashboard</span>
    </a>
</li>
```

**Repeat for all primary navigation items**:

- Dashboard
- Characters
- Training
- Races
- Skills
- Support Cards

---

### Step 5: Handle Collapsible Groups (25 minutes)

**For collapsible groups** (Data Management, Analytics, AI Tools, External Resources):

**Wrap in conditional display**:

```blade
<!-- Expanded State: Show full group -->
<li x-show="!$store.sidebar.minimized" x-transition>
    <button @click="dataOpen = !dataOpen" ...>
        <svg class="h-6 w-6 shrink-0" ...>...</svg>
        <span class="flex-1 text-left">Data Management</span>
        <svg class="h-5 w-5 shrink-0 transition-transform" :class="dataOpen ? 'rotate-90' : ''" ...>...</svg>
    </button>
    <ul x-show="dataOpen" x-collapse>
        <!-- Submenu items -->
    </ul>
</li>

<!-- Minimized State: Show icon with tooltip -->
<li x-show="$store.sidebar.minimized" x-transition>
    <div x-data="{ tooltip: false }" class="relative">
        <button @mouseenter="tooltip = true" 
                @mouseleave="tooltip = false"
                class="group flex justify-center rounded-md p-2 w-full ...">
            <svg class="h-6 w-6 shrink-0" ...>...</svg>
        </button>
        <!-- Tooltip -->
        <div x-show="tooltip" 
             x-transition
             class="absolute left-full ml-2 top-0 z-50 bg-gray-900 dark:bg-gray-700 text-white text-sm px-3 py-2 rounded-md whitespace-nowrap pointer-events-none">
            Data Management
            <!-- Arrow -->
            <div class="absolute w-2 h-2 bg-gray-900 dark:bg-gray-700 rotate-45 -left-1 top-1/2 -translate-y-1/2"></div>
        </div>
    </div>
</li>
```text

---

### Step 6: Add Keyboard Shortcut (5 minutes)

**File**: `resources/js/app.js`

```javascript
// Sidebar toggle keyboard shortcut (Alt + B)
document.addEventListener('keydown', (e) => {
    if (e.altKey && e.key === 'b') {
        e.preventDefault();
        Alpine.store('sidebar').toggle();
    }
});
```

---

### Step 7: Add Screen Reader Announcement (5 minutes)

**File**: `resources/views/layouts/app.blade.php`

**Add after the sidebar**:

```blade
<!-- Screen Reader Announcement -->
<div role="status" 
     aria-live="polite" 
     aria-atomic="true" 
     class="sr-only">
    <span x-text="$store.sidebar.minimized ? 'Sidebar minimized' : 'Sidebar expanded'"></span>
</div>
```text

---

## Testing Checklist

### Manual Testing

- [ ] Click toggle button - sidebar minimizes
- [ ] Click toggle button again - sidebar expands
- [ ] Refresh page - state persists
- [ ] Press `Alt + B` - sidebar toggles
- [ ] Hover over icons in minimized state - tooltips appear
- [ ] Test on mobile (<640px) - bottom nav still works
- [ ] Test on tablet (640-1024px) - overlay sidebar still works
- [ ] Test on desktop (≥1024px) - minimize feature works
- [ ] Test in light mode - colors correct
- [ ] Test in dark mode - colors correct
- [ ] Tab through navigation - focus indicators visible
- [ ] Test with screen reader - announcements work

### Browser Testing

- [ ] Chrome
- [ ] Firefox
- [ ] Safari
- [ ] Edge

---

## Common Issues & Solutions

### Issue: Sidebar doesn't minimize

**Solution**: Check that Alpine.js store is registered in `app.js`

### Issue: State doesn't persist

**Solution**: Check localStorage in browser DevTools (Application > Local Storage)

### Issue: Tooltips don't appear

**Solution**: Verify `z-50` class and `pointer-events-none` on tooltip

### Issue: Transition is jerky

**Solution**: Ensure `transition-all duration-300` is on both sidebar and content

### Issue: Icons not centered in minimized state

**Solution**: Add `:class="$store.sidebar.minimized ? 'justify-center' : ''"` to links

---

## Performance Tips

1. **Use CSS transitions** instead of JavaScript animations
2. **Debounce rapid clicks** on toggle button
3. **Lazy load tooltips** - only render when needed
4. **Minimize localStorage writes** - only on state change

---

## Next Steps

After basic implementation:

1. **Write tests** (see Implementation Plan)
2. **Update documentation** (user manual, help)
3. **Gather feedback** from team
4. **Monitor performance** metrics
5. **Plan Phase 2 features** (hover expand, custom width, etc.)

---

## Support

For questions or issues:

- Review [Implementation Plan](./sidebar-minimize-implementation-plan.md)
- Check [Visual Reference](./sidebar-minimize-visual-reference.md)
- Consult [AGENTS.md](../../AGENTS.md) for coding standards

---

*This quick start guide provides the essential steps to implement the sidebar minimize feature. For detailed
specifications, refer to the full implementation plan.*

