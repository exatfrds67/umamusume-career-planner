# Umamusume Component Library

A comprehensive, accessible component library for the Umamusume Career Planner application, built with Laravel Blade, Tailwind CSS v4, and Alpine.js.

## Table of Contents

- [Button Components](#button-components)
- [Card Components](#card-components)
- [Form Components](#form-components)
- [Badge Components](#badge-components)
- [Alert Components](#alert-components)
- [Toast Notifications](#toast-notifications)

---

## Button Components

### Basic Button (`x-button`)

Reusable button component with multiple variants, sizes, and states.

**Props:**

- `variant` - Button style: `primary` (default), `secondary`, `outline`
- `size` - Button size: `sm`, `md` (default), `lg`
- `type` - HTML button type: `button` (default), `submit`, `reset`
- `loading` - Boolean: Shows spinner and disables button
- `disabled` - Boolean: Disables the button
- `href` - String: Renders as link instead of button
- `ariaLabel` - String: Accessible label for screen readers

**Examples:**

```blade
{{-- Primary button --}}
<x-button variant="primary">Save Changes</x-button>

{{-- Loading state --}}
<x-button loading>Processing...</x-button>

{{-- Button as link --}}
<x-button href="/dashboard" variant="outline">Go to Dashboard</x-button>

{{-- With accessibility label --}}
<x-button aria-label="Delete character permanently" variant="secondary">
    Delete
</x-button>
```

---

## Card Components

### Card Container (`x-card`)

Container component for card layouts with variants.

**Props:**

- `variant` - Card style: `default`, `elevated`, `outlined`

**Examples:**

```blade
{{-- Basic card --}}
<x-card>
    <x-card.header>
        <h2>Character Stats</h2>
    </x-card.header>
    <x-card.body>
        <p>Speed: 500</p>
        <p>Stamina: 450</p>
    </x-card.body>
    <x-card.footer>
        <x-button>Edit Stats</x-button>
    </x-card.footer>
</x-card>

{{-- Elevated card --}}
<x-card variant="elevated">
    <x-card.body>
        Content with elevated shadow
    </x-card.body>
</x-card>
```

### Card Header (`x-card.header`)

Header section for cards with bottom border.

### Card Body (`x-card.body`)

Main content area for cards.

### Card Footer (`x-card.footer`)

Footer section for cards with top border.

---

## Form Components

All form components include built-in validation states, error messages, help text, and WCAG 2.2 AA accessibility features.

### Text Input (`x-form.input`)

**Props:**

- `type` - Input type: `text` (default), `email`, `password`, `number`, etc.
- `name` - Input name attribute (required)
- `id` - Input ID (defaults to name)
- `value` - Input value
- `placeholder` - Placeholder text
- `required` - Boolean: Marks field as required
- `disabled` - Boolean: Disables the input
- `readonly` - Boolean: Makes input read-only
- `error` - String: Error message to display
- `helpText` - String: Help text below input
- `label` - String: Label text
- `ariaLabel` - String: Accessible label

**Examples:**

```blade
{{-- Basic input --}}
<x-form.input 
    name="character_name" 
    label="Character Name"
    placeholder="Enter character name"
    required
/>

{{-- Input with error --}}
<x-form.input 
    name="email" 
    type="email"
    label="Email Address"
    error="Please enter a valid email address"
/>

{{-- Input with help text --}}
<x-form.input 
    name="username" 
    label="Username"
    helpText="Username must be 3-20 characters"
/>
```

### Select Dropdown (`x-form.select`)

**Props:**

- `name` - Select name attribute (required)
- `id` - Select ID (defaults to name)
- `value` - Selected value
- `options` - Array: Options as key-value pairs
- `placeholder` - String: Placeholder option
- `required` - Boolean: Marks field as required
- `disabled` - Boolean: Disables the select
- `error` - String: Error message
- `helpText` - String: Help text
- `label` - String: Label text
- `ariaLabel` - String: Accessible label

**Examples:**

```blade
{{-- Basic select --}}
<x-form.select 
    name="scenario" 
    label="Scenario"
    :options="[
        'ura_finale' => 'URA Finale',
        'unity_cup' => 'Unity Cup'
    ]"
    placeholder="Select a scenario"
    required
/>

{{-- Select with error --}}
<x-form.select 
    name="rarity" 
    label="Rarity"
    :options="['SSR' => 'SSR', 'SR' => 'SR', 'R' => 'R']"
    error="Please select a rarity"
/>
```

### Checkbox (`x-form.checkbox`)

**Props:**

- `name` - Checkbox name attribute (required)
- `id` - Checkbox ID (defaults to name)
- `value` - Checkbox value (default: '1')
- `checked` - Boolean: Checked state
- `required` - Boolean: Marks field as required
- `disabled` - Boolean: Disables the checkbox
- `error` - String: Error message
- `helpText` - String: Help text
- `label` - String: Label text
- `ariaLabel` - String: Accessible label

**Examples:**

```blade
{{-- Basic checkbox --}}
<x-form.checkbox 
    name="agree_terms" 
    label="I agree to the terms and conditions"
    required
/>

{{-- Checkbox with help text --}}
<x-form.checkbox 
    name="newsletter" 
    label="Subscribe to newsletter"
    helpText="Receive updates about new features"
/>
```

### Textarea (`x-form.textarea`)

**Props:**

- `name` - Textarea name attribute (required)
- `id` - Textarea ID (defaults to name)
- `value` - Textarea value
- `placeholder` - Placeholder text
- `rows` - Number: Rows (default: 4)
- `required` - Boolean: Marks field as required
- `disabled` - Boolean: Disables the textarea
- `readonly` - Boolean: Makes textarea read-only
- `error` - String: Error message
- `helpText` - String: Help text
- `label` - String: Label text
- `ariaLabel` - String: Accessible label

**Examples:**

```blade
{{-- Basic textarea --}}
<x-form.textarea 
    name="notes" 
    label="Training Notes"
    rows="6"
    placeholder="Enter your notes here..."
/>

{{-- Textarea with error --}}
<x-form.textarea 
    name="description" 
    label="Description"
    error="Description is required"
    required
/>
```

---

## Badge Components

### Basic Badge (`x-badge`)

**Props:**

- `variant` - Badge style: `primary` (default), `secondary`, `success`, `warning`, `error`

**Examples:**

```blade
<x-badge variant="success">Active</x-badge>
<x-badge variant="warning">Pending</x-badge>
<x-badge variant="error">Failed</x-badge>
```

### Grade Badge (`x-badge.grade`)

Displays character aptitude grades with appropriate colors.

**Props:**

- `grade` - Grade value: `SS`, `S`, `A`, `B`, `C`, `D`, `E`, `F`, `G`
- `ariaLabel` - String: Accessible label (auto-generated if omitted)

**Examples:**

```blade
{{-- Display aptitude grades --}}
<x-badge.grade grade="SS" />
<x-badge.grade grade="A" />
<x-badge.grade grade="C" />

{{-- With custom aria label --}}
<x-badge.grade grade="S" aria-label="Speed aptitude grade S" />
```

### Status Badge (`x-badge.status`)

Displays status indicators with semantic colors.

**Props:**

- `status` - Status value: `success`, `warning`, `error`, `info`, `on_track`, `at_risk`, `off_track`, etc.
- `ariaLabel` - String: Accessible label (auto-generated if omitted)

**Examples:**

```blade
{{-- Goal progress status --}}
<x-badge.status status="on_track" />
<x-badge.status status="at_risk" />
<x-badge.status status="off_track" />

{{-- Custom status text --}}
<x-badge.status status="success">Completed</x-badge.status>
```

### Rarity Badge (`x-badge.rarity`)

Displays card/skill rarity levels.

**Props:**

- `rarity` - Rarity value: `SSR`, `SR`, `R`, `NORMAL`, `RARE`, `UNIQUE`
- `ariaLabel` - String: Accessible label (auto-generated if omitted)

**Examples:**

```blade
{{-- Support card rarity --}}
<x-badge.rarity rarity="SSR" />
<x-badge.rarity rarity="SR" />
<x-badge.rarity rarity="R" />

{{-- Skill rarity --}}
<x-badge.rarity rarity="UNIQUE" />
```

---

## Alert Components

### Alert (`x-alert`)

Static alert messages with optional dismiss functionality.

**Props:**

- `variant` - Alert style: `info` (default), `success`, `warning`, `error`
- `dismissible` - Boolean: Adds close button
- `ariaLabel` - String: Accessible label (auto-generated if omitted)

**Examples:**

```blade
{{-- Success alert --}}
<x-alert variant="success">
    Character created successfully!
</x-alert>

{{-- Dismissible warning --}}
<x-alert variant="warning" dismissible>
    This action cannot be undone.
</x-alert>

{{-- Error alert --}}
<x-alert variant="error">
    <strong>Error:</strong> Failed to save changes. Please try again.
</x-alert>
```

---

## Toast Notifications

### Toast (`x-toast`)

Temporary notification messages with auto-dismiss and animations.

**Props:**

- `variant` - Toast style: `info` (default), `success`, `warning`, `error`
- `autoDismiss` - Boolean: Auto-dismiss after duration (default: true)
- `duration` - Number: Milliseconds before auto-dismiss (default: 5000)
- `ariaLabel` - String: Accessible label (auto-generated if omitted)

**Examples:**

```blade
{{-- Success toast (auto-dismisses after 5 seconds) --}}
<x-toast variant="success">
    Training completed successfully!
</x-toast>

{{-- Warning toast with custom duration --}}
<x-toast variant="warning" :duration="10000">
    Low energy warning: Consider resting before next training.
</x-toast>

{{-- Error toast (no auto-dismiss) --}}
<x-toast variant="error" :auto-dismiss="false">
    Connection lost. Please check your internet connection.
</x-toast>
```

**Toast Container:**

For proper positioning, wrap toasts in a container:

```blade
<div class="fixed top-4 right-4 z-50 flex flex-col gap-2 max-w-md">
    @if (session('success'))
        <x-toast variant="success">
            {{ session('success') }}
        </x-toast>
    @endif

    @if (session('error'))
        <x-toast variant="error">
            {{ session('error') }}
        </x-toast>
    @endif
</div>
```

---

## Accessibility Features

All components include:

- **WCAG 2.2 AA Compliance**: Proper contrast ratios, focus indicators, and semantic HTML
- **Keyboard Navigation**: Full keyboard support with visible focus states
- **Screen Reader Support**: ARIA labels, roles, and live regions
- **Error Announcements**: Validation errors announced to assistive technologies
- **Focus Management**: Automatic focus on first error field
- **High Contrast Mode**: Enhanced borders and contrast in high contrast mode
- **Text Scaling**: Support for text sizes up to 200%

## Browser Support

All components support:

- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Dependencies

- Laravel 12
- Tailwind CSS v4
- Alpine.js (for interactive components)
- Blade templating engine

## Related Documentation

- [Button Component README](./README.md)
- [Accessibility Settings Panel](./accessibility-settings-panel.blade.php)
- [Design System CSS](../../css/app.css)
