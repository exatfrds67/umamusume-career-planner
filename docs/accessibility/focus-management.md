# Focus Management System

## Overview

The focus management system ensures that keyboard users can navigate the application efficiently and that focus
indicators are always visible with sufficient contrast (3:1 minimum as per WCAG 2.2 AA).

## Features

### 1. Visible Focus Indicators

All interactive elements have visible focus indicators with 3:1 contrast ratio:

```css
:focus-visible {
    outline: 2px solid var(--color-primary-500);
    outline-offset: 2px;
}
```text

Enhanced focus indicators for keyboard navigation mode:

```css
.keyboard-navigation *:focus-visible {
    outline: 2px solid var(--color-primary-500);
    outline-offset: 2px;
    box-shadow: 0 0 0 4px rgb(59 130 246 / 0.2);
}

.keyboard-navigation button:focus-visible,
.keyboard-navigation .btn:focus-visible {
    outline-width: 3px;
}
```

### 2. Skip Links

Skip links allow keyboard users to jump directly to main content areas:

```html
<a href="#main-content" class="skip-link">Skip to main content</a>
<a href="#navigation" class="skip-link">Skip to navigation</a>
```text

Skip links are visually hidden until focused:

```css
.skip-link {
    position: absolute;
    top: -40px;
    left: 0;
    background: var(--color-primary-600);
    color: white;
    padding: 8px 16px;
    z-index: 100;
    text-decoration: none;
    font-weight: 600;
    transition: top 0.2s ease;
}

.skip-link:focus {
    top: 0;
    z-index: 9999;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
}
```

### 3. Keyboard Shortcuts

The application supports the following keyboard shortcuts:

#### Navigation

- **Alt + 1**: Skip to main content
- **Alt + S**: Toggle sidebar
- **Alt + /**: Focus search
- **Tab**: Navigate forward
- **Shift + Tab**: Navigate backward
- **Home**: Scroll to top
- **End**: Scroll to bottom

#### Accessibility

- **Alt + A**: Open accessibility settings
- **Shift + ?**: Show keyboard shortcuts help

#### General

- **Escape**: Close modal/dialog
- **Enter** or **Space**: Activate button/link

### 4. Focus Trap for Modals

When a modal is opened, focus is trapped within the modal to prevent keyboard users from accidentally navigating
outside:

```javascript
// Trap focus in a modal
accessibilitySystem.trapFocus('#modal-container');

// Release focus trap when modal closes
accessibilitySystem.releaseFocus();
```text

Or use the event bus:

```javascript
// Trap focus
eventBus.emit('focus:trap', '#modal-container');

// Release focus
eventBus.emit('focus:release');
```

### 5. Focus Management for Forms

When form validation errors occur, focus is automatically moved to the first invalid field:

```javascript
// Focus first error in form
accessibilitySystem.focusFirstError('#my-form');
```text

This also announces the error to screen readers:

```javascript
// Announces: "Error: [error message]"
```

### 6. Keyboard Navigation Detection

The system automatically detects when users are navigating with the keyboard and applies enhanced focus indicators:

```javascript
// Keyboard navigation detected
document.body.classList.add('using-keyboard');

// Mouse navigation detected
document.body.classList.remove('using-keyboard');
```text

## Usage Examples

### Basic Focus Management

```javascript
// Get all focusable elements in a container
const focusableElements = accessibilitySystem.getFocusableElements('#container');

// Check if an element is focusable
const isFocusable = accessibilitySystem.isFocusable(element);

// Ensure focus is visible
accessibilitySystem.ensureFocusVisible(element);
```

### Registering Custom Keyboard Shortcuts

```javascript
// Register a custom shortcut
accessibilitySystem.registerShortcut('Ctrl+K', (e) => {
    // Handle shortcut
    console.log('Ctrl+K pressed');
});

// Unregister a shortcut
accessibilitySystem.unregisterShortcut('Ctrl+K');
```text

### Focus Trap Example

```html
<!-- Modal with focus trap -->
<div id="my-modal" class="modal" x-data="{ open: false }">
    <div x-show="open" 
         @open-modal.window="open = true; $nextTick(() => window.accessibilitySystem.trapFocus('#my-modal'))"
         @close-modal.window="open = false; window.accessibilitySystem.releaseFocus()">
        <!-- Modal content -->
        <button @click="$dispatch('close-modal')">Close</button>
    </div>
</div>
```

### Form Validation Focus

```html
<!-- Form with validation -->
<form id="my-form" @submit.prevent="handleSubmit">
    <div>
        <label for="name">Name</label>
        <input id="name" type="text" aria-invalid="false" aria-describedby="name-error">
        <span id="name-error" class="form-error" hidden>Name is required</span>
    </div>
    
    <button type="submit">Submit</button>
</form>

<script>
function handleSubmit() {
    // Validate form
    const nameInput = document.getElementById('name');
    const nameError = document.getElementById('name-error');
    
    if (!nameInput.value) {
        // Mark as invalid
        nameInput.setAttribute('aria-invalid', 'true');
        nameError.hidden = false;
        
        // Focus first error
        window.accessibilitySystem.focusFirstError('#my-form');
        
        return;
    }
    
    // Submit form
}
</script>
```text

## Accessibility Compliance

The focus management system ensures compliance with:

- **WCAG 2.2 Level AA Success Criterion 2.4.7**: Focus Visible
- **WCAG 2.2 Level AA Success Criterion 2.4.3**: Focus Order
- **WCAG 2.2 Level AA Success Criterion 2.4.1**: Bypass Blocks (skip links)
- **WCAG 2.2 Level AA Success Criterion 1.4.11**: Non-text Contrast (3:1 for focus indicators)

## Testing

### Manual Testing

1. **Tab Navigation**: Press Tab to navigate through all interactive elements. Verify that:
   - Focus indicators are visible
   - Focus order is logical
   - All interactive elements are reachable

2. **Skip Links**: Press Tab on page load. Verify that:
   - Skip links appear at the top
   - Skip links work when activated
   - Focus moves to the target section

3. **Keyboard Shortcuts**: Test all keyboard shortcuts. Verify that:
   - Shortcuts work as expected
   - Shortcuts don't conflict with browser shortcuts
   - Shortcuts are documented in the help modal

4. **Focus Trap**: Open a modal. Verify that:
   - Focus is trapped within the modal
   - Tab cycles through modal elements
   - Escape closes the modal and restores focus

5. **Form Validation**: Submit an invalid form. Verify that:
   - Focus moves to the first error
   - Error is announced to screen readers
   - Error message is visible

### Automated Testing

```javascript
// Test focus indicators
test('all interactive elements have focus indicators', () => {
    const { container } = render(App);
    const interactiveElements = container.querySelectorAll(
        'button, a, input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    
    interactiveElements.forEach(el => {
        el.focus();
        expect(document.activeElement).toBe(el);
        
        const styles = window.getComputedStyle(el, ':focus-visible');
        expect(styles.outlineWidth).not.toBe('0px');
    });
});

// Test skip links
test('skip links work correctly', () => {
    const { getByText } = render(App);
    const skipLink = getByText('Skip to main content');
    
    fireEvent.click(skipLink);
    
    const mainContent = document.getElementById('main-content');
    expect(document.activeElement).toBe(mainContent);
});

// Test keyboard shortcuts
test('keyboard shortcuts work', () => {
    const { getByRole } = render(App);
    
    // Test Alt+A to open accessibility settings
    fireEvent.keyDown(document, { key: 'a', altKey: true });
    
    expect(getByRole('dialog', { name: /accessibility settings/i })).toBeInTheDocument();
});

// Test focus trap
test('focus trap works in modals', () => {
    const { getByRole, getAllByRole } = render(App);
    
    // Open modal
    const openButton = getByRole('button', { name: /open modal/i });
    fireEvent.click(openButton);
    
    const modal = getByRole('dialog');
    const focusableElements = modal.querySelectorAll(
        'button, a, input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    
    const firstElement = focusableElements[0];
    const lastElement = focusableElements[focusableElements.length - 1];
    
    // Focus should be on first element
    expect(document.activeElement).toBe(firstElement);
    
    // Tab from last element should cycle to first
    lastElement.focus();
    fireEvent.keyDown(document, { key: 'Tab' });
    expect(document.activeElement).toBe(firstElement);
    
    // Shift+Tab from first element should cycle to last
    firstElement.focus();
    fireEvent.keyDown(document, { key: 'Tab', shiftKey: true });
    expect(document.activeElement).toBe(lastElement);
});
```

## Best Practices

1. **Always provide visible focus indicators**: Never use `outline: none` without providing an alternative focus
indicator.

2. **Maintain logical focus order**: Ensure that the tab order follows the visual layout and reading order.

3. **Trap focus in modals**: When a modal is open, trap focus within the modal to prevent users from accidentally
navigating outside.

4. **Restore focus after modal closes**: When a modal closes, restore focus to the element that opened it.

5. **Focus first error in forms**: When form validation fails, move focus to the first invalid field and announce the
error.

6. **Provide skip links**: Allow users to skip repetitive navigation and jump directly to main content.

7. **Document keyboard shortcuts**: Provide a help modal that lists all available keyboard shortcuts.

8. **Test with keyboard only**: Regularly test the application using only the keyboard to ensure all functionality is
accessible.

9. **Use semantic HTML**: Use proper HTML elements (button, a, input, etc.) to ensure they are focusable by default.

10. **Avoid keyboard traps**: Ensure that users can always navigate away from any element using the keyboard.

## Browser Support

The focus management system is compatible with:

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Related Documentation

- [Accessibility System](./accessibility-system.md)
- [Keyboard Shortcuts](./keyboard-shortcuts.md)
- [ARIA Live Regions](./aria-live-regions.md)
- [Screen Reader Support](./screen-reader-support.md)
