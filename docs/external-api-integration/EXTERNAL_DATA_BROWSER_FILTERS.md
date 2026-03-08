# External Data Browser - Filtering & Sorting Features

**Document Version**: 1.1.0  
**Date**: 2026-01-25  
**Status**: Implemented  

## Overview

The External Data Browser now includes comprehensive filtering and sorting capabilities to help users efficiently browse
and find characters, support cards, and skills. Characters and support cards are sourced from the umapyoi.net API, while
skills are sourced from the local database (umapyoi.net does not provide a skills endpoint).

## Data Sources

- **Characters**: umapyoi.net API (`/api/v1/character/list`)
- **Support Cards**: umapyoi.net API (`/api/v1/support`)
- **Skills**: Local database (umapyoi.net API does not provide skills endpoint)
- **News**: umapyoi.net API (`/api/v1/news/latest/{limit}`)

## Features Implemented

### Characters Tab

#### Search

- **Text Search**: Search by character name (English or Japanese)
- Real-time filtering as you type

#### Filters

- **Category Filter**: Dropdown to filter characters by category (e.g., "Speed", "Stamina", etc.)
  - Shows all unique categories from loaded data
  - "All Categories" option to clear filter

#### Sorting

- **ID (Low to High)**: Sort by character ID ascending
- **ID (High to Low)**: Sort by character ID descending
- **Name (A-Z)**: Sort alphabetically by English name
- **Name (Z-A)**: Sort reverse alphabetically by English name

#### Active Filters Summary

- Shows count of active filters
- Displays "X of Y characters shown" when filters are applied

### Support Cards Tab

#### Search (Support Cards)

- **Text Search**: Search by card title, character name, or GameTora ID
- Real-time filtering as you type

#### Filters (Support Cards)

- **Rarity Filter**: Toggle buttons for SSR, SR, and R rarities
  - Multiple rarities can be selected simultaneously
  - Visual indicators (colored badges with ring highlight when active)
  - Color-coded: Yellow (SSR), Purple (SR), Blue (R)

- **Import Status Filter**: Toggle buttons for import status
  - **Imported**: Show only cards already imported to collection
  - **Not Imported**: Show only cards not yet imported
  - Multiple statuses can be selected simultaneously
  - Visual indicators (colored badges with ring highlight when active)

#### Sorting (Support Cards)

- **ID (Low to High)**: Sort by card ID ascending
- **ID (High to Low)**: Sort by card ID descending
- **Name (A-Z)**: Sort alphabetically by card title
- **Name (Z-A)**: Sort reverse alphabetically by card title
- **Rarity (High to Low)**: Sort by rarity (SSR → SR → R)
- **Rarity (Low to High)**: Sort by rarity (R → SR → SSR)

#### Active Filters Summary (Support Cards)

- Shows count of active filters
- Displays "X of Y cards shown" when filters are applied

### Skills Tab

#### Search (Skills)

- **Text Search**: Search by skill name, description, or effect text
- Real-time filtering as you type

#### Filters (Skills)

- **Rarity Filter**: Toggle buttons for Unique, Rare, and Normal
  - Multiple rarities can be selected simultaneously
  - Visual indicators (colored badges with ring highlight when active)
  - Color-coded: Yellow (Unique), Purple (Rare), Blue (Normal)

- **Type Filter**: Dropdown to filter skills by type (from loaded data)
  - "All Types" option to clear filter

#### Sorting (Skills)

- **ID (Low to High)**: Sort by skill ID ascending
- **ID (High to Low)**: Sort by skill ID descending
- **Name (A-Z)**: Sort alphabetically by skill name
- **Name (Z-A)**: Sort reverse alphabetically by skill name
- **Rarity (High to Low)**: Sort by rarity (Unique → Rare → Normal)
- **Rarity (Low to High)**: Sort by rarity (Normal → Rare → Unique)

#### Active Filters Summary (Skills)

- Shows count of active filters
- Displays "X of Y skills shown" when filters are applied

## User Interface

### Filter Controls Layout

```text
┌─────────────────────────────────────────────────────────────┐
│ Search Bar                                                   │
├─────────────────────────────────────────────────────────────┤
│ Rarity: [SSR] [SR] [R]  Status: [Imported] [Not Imported]  │
│ Sort: [Dropdown]                          [Clear Filters]   │
├─────────────────────────────────────────────────────────────┤
│ Active filters: 3 filter(s) applied • 45 of 487 cards shown│
└─────────────────────────────────────────────────────────────┘
```text

### Visual Feedback

- **Active Filter Buttons**: Colored background with ring highlight
- **Inactive Filter Buttons**: Gray background, hover effect
- **Clear Filters Button**: Only visible when filters are active
- **Active Filters Summary**: Only visible when filters are active

## Technical Implementation

### Alpine.js Data Structure

```javascript
{
    filters: {
        rarity: [],           // ['SSR', 'SR', 'R']
        importStatus: [],     // ['imported', 'not-imported']
      category: '',         // For characters
      skillRarity: [],      // ['unique', 'rare', 'normal']
      skillType: ''         // For skills
    },
    sortBy: 'id-asc',        // Default sort
    skillSortBy: 'id-asc',
    searchTerm: ''
}
```text

### Key Functions

#### `filterData()`

- Applies all active filters to characters and support cards
- Calls `applySorting()` after filtering

#### `applySorting()`

- Sorts filtered results based on `sortBy` value
- Handles different field types (numeric, string, custom order)

#### `toggleFilter(filterType, value)`

- Toggles filter values on/off
- Automatically triggers `filterData()`

#### `clearFilters()`

- Resets all filters to default state
- Clears search term
- Resets sort to default (ID ascending)

#### `hasActiveFilters()`

- Returns true if any filter is active
- Used to show/hide "Clear Filters" button and summary

#### `getActiveFiltersCount()`

- Counts number of active filter types
- Used in active filters summary

#### `getUniqueCategories()`

- Extracts unique categories from character data
- Populates category dropdown dynamically

## Filter Persistence

Currently, filters are **not persisted** across page reloads. This is intentional to ensure users always start with a
clean view of all data.

### Future Enhancement Options

- Add localStorage persistence for user preferences
- Add "Save Filter Preset" functionality
- Add URL query parameters for shareable filtered views

## Performance Considerations

- Filtering is performed client-side for instant feedback
- All data is loaded once on page load
- Sorting uses native JavaScript array methods
- No additional API calls required for filtering/sorting

## Accessibility

- All filter controls are keyboard accessible
- Clear visual indicators for active filters
- Screen reader friendly labels
- Color is not the only indicator (text + icons used)

## Browser Compatibility

- Works in all modern browsers (Chrome, Firefox, Safari, Edge)
- Requires JavaScript enabled
- Uses Alpine.js v3 for reactivity

## Testing Recommendations

### Manual Testing Checklist

- [ ] Search filters results correctly
- [ ] Rarity filters work individually and in combination
- [ ] Import status filters work correctly
- [ ] Category filter shows all unique categories
- [ ] Sorting works for all options
- [ ] Clear Filters resets all controls
- [ ] Active filters summary shows correct counts
- [ ] Filters work correctly when switching between tabs
- [ ] Skills filters work for rarity and type
- [ ] Visual feedback is clear and consistent
- [ ] Keyboard navigation works properly

### Edge Cases to Test

- Empty search results
- All filters active simultaneously
- Switching tabs with active filters
- Rapid filter toggling
- Special characters in search
- Very long character/card names

## Known Limitations

1. **Import Status Filter**: Currently a placeholder - requires backend support to track which cards have been imported
2. **No Advanced Search**: No support for complex queries (AND/OR logic)
3. **No Filter Presets**: Users cannot save commonly used filter combinations
4. **No Bulk Actions**: Cannot perform actions on filtered results

## Future Enhancements

### Short Term

- [ ] Implement backend tracking for import status
- [ ] Add filter persistence to localStorage
- [ ] Add "Export Filtered Results" functionality

### Long Term

- [ ] Advanced search with query builder
- [ ] Saved filter presets
- [ ] Bulk import for filtered results
- [ ] Filter by additional attributes (card type, character stats, etc.)
- [ ] Date range filters for news items

## Related Documentation

- [External API Integration Summary](./IMPLEMENTATION_SUMMARY.md)
- [Frontend Integration Guide](./FRONTEND_INTEGRATION_SUMMARY.md)
- [API Testing Guide](./API_TESTING_QUICK_REFERENCE.md)

## Change Log

### Version 1.0.0 (2026-01-25)

- Initial implementation of filtering and sorting
- Added rarity filter for support cards
- Added import status filter (placeholder)
- Added category filter for characters
- Added sorting options for both tabs
- Added active filters summary
- Added clear filters functionality

