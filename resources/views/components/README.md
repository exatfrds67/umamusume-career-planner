# Button Component

A reusable button component with multiple variants, sizes, and states.

## Usage

### Basic Usage

```blade
<x-button>Click Me</x-button>
```

### Variants

The button component supports three variants:

- `primary` (default) - Primary action button with blue background
- `secondary` - Secondary action button with gray background
- `outline` - Outlined button with transparent background

```blade
<x-button variant="primary">Primary Button</x-button>
<x-button variant="secondary">Secondary Button</x-button>
<x-button variant="outline">Outline Button</x-button>
```

### Sizes

The button component supports three sizes:

- `sm` - Small button (0.375rem × 0.75rem padding, 0.875rem font)
- `md` (default) - Medium button (0.5rem × 1rem padding, 1rem font)
- `lg` - Large button (0.75rem × 1.5rem padding, 1.125rem font)

```blade
<x-button size="sm">Small</x-button>
<x-button size="md">Medium</x-button>
<x-button size="lg">Large</x-button>
```

### States

#### Disabled State

```blade
<x-button disabled>Disabled Button</x-button>
```

#### Loading State

The loading state automatically disables the button and shows a spinner:

```blade
<x-button loading>Loading...</x-button>
```

### Button Types

By default, buttons render as `type="button"`. You can change this:

```blade
<x-button type="submit">Submit Form</x-button>
<x-button type="reset">Reset Form</x-button>
```

### Button Links

You can render a button as a link by providing an `href` attribute:

```blade
<x-button href="/dashboard">Go to Dashboard</x-button>
```

**Note:** Disabled and loading buttons will not render as links, even if `href` is provided.

### Accessibility

#### ARIA Labels

Provide descriptive labels for screen readers:

```blade
<x-button aria-label="Save character changes">Save</x-button>
<x-button aria-label="Delete character permanently">Delete</x-button>
```

#### Automatic ARIA Attributes

The component automatically adds appropriate ARIA attributes:

- `aria-disabled="true"` when disabled or loading
- `aria-busy="true"` when loading
- `role="button"` for link-style buttons

### Custom Classes

You can add custom classes to the button:

```blade
<x-button class="w-full">Full Width Button</x-button>
<x-button class="shadow-lg">Button with Shadow</x-button>
```

### Additional Attributes

Any additional attributes are passed through to the button element:

```blade
<x-button id="my-button" data-test="submit" @click="handleClick">
    Custom Button
</x-button>
```

## Examples

### Form Submit Button

```blade
<form method="POST" action="/characters">
    @csrf
    <!-- form fields -->
    <x-button type="submit" variant="primary" size="lg">
        Create Character
    </x-button>
</form>
```

### Loading Button with Alpine.js

```blade
<div x-data="{ loading: false }">
    <x-button 
        :loading="loading"
        @click="loading = true; setTimeout(() => loading = false, 2000)"
    >
        Save Changes
    </x-button>
</div>
```

### Button Group

```blade
<div class="flex gap-2">
    <x-button variant="primary">Save</x-button>
    <x-button variant="secondary">Cancel</x-button>
    <x-button variant="outline">Preview</x-button>
</div>
```

### Responsive Button

```blade
<x-button class="w-full md:w-auto">
    Responsive Button
</x-button>
```

## Accessibility Features

- **Keyboard Navigation**: All buttons are keyboard accessible with visible focus indicators
- **Screen Reader Support**: Proper ARIA labels and attributes for assistive technologies
- **Loading States**: Screen readers announce loading state with "Loading..." text
- **Disabled States**: Properly communicated to assistive technologies
- **Focus Management**: 3:1 contrast ratio for focus indicators (WCAG 2.2 AA compliant)

## CSS Classes

The component uses the following CSS classes defined in `resources/css/app.css`:

- `.btn` - Base button styles
- `.btn-primary` - Primary variant styles
- `.btn-secondary` - Secondary variant styles
- `.btn-outline` - Outline variant styles
- `.btn-sm` - Small size styles
- `.btn-md` - Medium size styles
- `.btn-lg` - Large size styles

## Browser Support

The button component supports all modern browsers and includes:

- CSS transitions for smooth hover effects
- Flexbox for proper alignment
- SVG spinner for loading states
- Reduced motion support for accessibility

## Related Components

- Form Components (`resources/views/components/form/`)
- Card Components (`resources/views/components/card/`)
- Badge Components (`resources/views/components/badge/`)
