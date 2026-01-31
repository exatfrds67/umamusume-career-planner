# Breadcrumb Implementation Guide

This document tracks the breadcrumb implementation across all views in the Umamusume Career Planner application.

## Breadcrumb Component

Location: `resources/views/components/breadcrumb.blade.php`

Usage:

```blade
<x-breadcrumb :items="[
    ['label' => 'Home', 'url' => route('dashboard')],
    ['label' => 'Characters', 'url' => route('characters.index')],
    ['label' => 'Edit']
]" />
```

## Implementation Status

### ✅ Completed

1. **Dashboard** - `/dashboard`
   - Breadcrumb: `Dashboard`

2. **Characters Index** - `/characters`
   - Breadcrumb: `Characters`

3. **Characters Create** - `/characters/create`
   - Breadcrumb: `Characters > Create Character`

4. **Characters Factors** - `/characters/{id}/factors`
   - Breadcrumb: `Characters > {Character Name} > Manage Factors`

5. **Skills Index** - `/skills`
   - Breadcrumb: `Skills`

6. **Support Cards Index** - `/support-cards`
   - Breadcrumb: `Support Cards`

7. **Training Predictions** - `/training/predictions`
   - Breadcrumb: `Training > Predictions`

8. **Races Index** - `/races`
   - Breadcrumb: `Races`

### 🔄 Pending Implementation

#### Characters Subpages

- `/characters/{id}` - "Characters > {Character Name}"
- `/characters/{id}/edit` - "Characters > {Character Name} > Edit"
- `/characters/{id}/training` - "Characters > {Character Name} > Training"
- `/characters/{id}/deck-builder` - "Characters > {Character Name} > Deck Builder"

#### Races Subpages

- `/races/{id}` - "Races > Race Details"
- `/races/calendar` - "Races > Race Calendar"
- `/races/targets` - "Races > Race Targets"

#### Support Cards Subpages

- `/support-cards/{id}` - "Support Cards > Card Details"

#### Training Subpages

- `/training/predictions/{character}` - "Training > Predictions > {Character Name}"

#### Data Management Group

- `/data-management` - "Data Management"
- `/import` - "Data Management > Import Data"
- `/export` - "Data Management > Export Data"
- `/migration` - "Data Management > Data Migration"
- `/backup` - "Data Management > Backup & Restore"

#### Analytics & Reports Group

- `/reports` - "Analytics & Reports"
- `/reports/career/{id}` - "Analytics & Reports > Career Report"
- `/reports/character/{id}` - "Analytics & Reports > Character Report"
- `/reports/compare` - "Analytics & Reports > Compare Reports"
- `/historical` - "Analytics & Reports > Historical Tracking"
- `/performance/apm/dashboard` - "Analytics & Reports > Performance Dashboard"

#### AI & Tools Group

- `/ai/dashboard` - "AI & Tools > AI Dashboard"
- `/ai/chat` - "AI & Tools > AI Chat"
- `/mcp/dashboard` - "AI & Tools > MCP Dashboard"
- `/ocr/upload` - "AI & Tools > OCR Upload"
- `/ocr/results/{id}` - "AI & Tools > OCR Results"

#### External Resources Group

- `/external-data/browse` - "External Resources > Browse Data"

#### Admin Panel Group

- `/admin/users` - "Admin Panel > User Management"
- `/admin/users/{id}/edit` - "Admin Panel > User Management > Edit User"
- `/admin/system-settings` - "Admin Panel > System Settings"
- `/admin/logs` - "Admin Panel > System Logs"
- `/admin/database/maintenance` - "Admin Panel > Database Maintenance"
- `/admin/database/seeders` - "Admin Panel > Database Seeders"
- `/admin/queue-monitor` - "Admin Panel > Queue Monitor"

#### Profile & Settings

- `/profile` - "Profile"
- `/settings` - "Settings"

## Breadcrumb Patterns

### Pattern 1: Simple Page

```blade
<x-breadcrumb :items="[
    ['label' => 'Page Name']
]" />
```

### Pattern 2: Two-Level Navigation

```blade
<x-breadcrumb :items="[
    ['label' => 'Parent', 'url' => route('parent.index')],
    ['label' => 'Current Page']
]" />
```

### Pattern 3: Three-Level with Dynamic Name

```blade
<x-breadcrumb :items="[
    ['label' => 'Characters', 'url' => route('characters.index')],
    ['label' => $character->name, 'url' => route('characters.show', $character)],
    ['label' => 'Edit']
]" />
```

### Pattern 4: Grouped Navigation

```blade
<x-breadcrumb :items="[
    ['label' => 'Data Management', 'url' => route('data-management.index')],
    ['label' => 'Import Data']
]" />
```

## Controller Updates Required

For dynamic breadcrumbs, controllers need to pass data to views:

```php
public function show(Character $character)
{
    return view('characters.show', [
        'character' => $character,
        'breadcrumbs' => [
            ['label' => 'Characters', 'url' => route('characters.index')],
            ['label' => $character->name]
        ]
    ]);
}
```

## Testing Checklist

- [ ] All breadcrumbs display correctly
- [ ] Links navigate to correct pages
- [ ] Current page is not clickable
- [ ] Home icon displays on first item
- [ ] Chevron separators display between items
- [ ] Dark mode styling works
- [ ] Mobile responsive layout
- [ ] Keyboard navigation works
- [ ] Screen reader announces breadcrumbs correctly
- [ ] Focus states are visible

## Accessibility Notes

- Breadcrumbs use `<nav aria-label="Breadcrumb">` for screen readers
- Current page uses `aria-current="page"`
- Links have proper focus states
- Icons are marked with `aria-hidden="true"`
- Home link has `aria-label="Home"`
