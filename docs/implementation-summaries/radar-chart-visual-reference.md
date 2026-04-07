# Radar Chart Visual Reference

## Expected Appearance

### Pentagon Structure

```text
                    1000 (top label)
                       *
                      / \
                     /   \
                    /     \
                   /       \
                  /         \
                 /           \
                /             \
               /               \
              /                 \
             /                   \
            /                     \
           /                       \
          /                         \
         /                           \
        /                             \
       /                               \
      /                                 \
     /                                   \
    /                                     \
   /                                       \
  *-----------------------------------------*
 /                                           \
/                                             \
*-----------------------------------------------*
```text

### Grid Levels (from center outward)

1. **Level 1 (20%)**: Label "200" at top
2. **Level 2 (40%)**: Label "400" at top
3. **Level 3 (60%)**: Label "600" at top
4. **Level 4 (80%)**: Label "800" at top
5. **Level 5 (100%)**: Label "1000" at top

### Test Character Stats (ID: 162)

With max=1000, the stats should appear at:

- **Speed (500)**: 50% from center (halfway between center and edge)
- **Stamina (450)**: 45% from center (between 400 and 600 grid lines)
- **Power (400)**: 40% from center (at the 400 grid line)
- **Guts (350)**: 35% from center (between 200 and 400 grid lines)
- **Wit (300)**: 30% from center (between 200 and 400 grid lines)

### Pentagon Axes (5 points)

Starting from top and going clockwise:

1. **Top (12 o'clock)**: Speed (blue)
2. **Top-Right (2:24)**: Stamina (green)
3. **Bottom-Right (4:48)**: Power (orange)
4. **Bottom-Left (7:12)**: Guts (amber/yellow)
5. **Top-Left (9:36)**: Wit (blue/sky)

### ASCII Visualization

```text
                    Speed (500)
                        •
                       /|\
                      / | \
                     /  |  \
                    /   |   \
                   /    |    \
                  /     |     \
                 /      |      \
                /       |       \
               /        |        \
              /         |         \
             /          |          \
            /           |           \
           /            |            \
          /             |             \
         /              |              \
        /               |               \
       /                |                \
      /                 |                 \
     /                  |                  \
    /                   |                   \
   /                    |                    \
  Wit (300)             |              Stamina (450)
      •                 |                    •
       \                |                   /
        \               |                  /
         \              |                 /
          \             |                /
           \            |               /
            \           |              /
             \          |             /
              \         |            /
               \        |           /
                \       |          /
                 \      |         /
                  \     |        /
                   \    |       /
                    \   |      /
                     \  |     /
                      \ |    /
                       \|   /
                        \  /
                         \/
                          •
                    Guts (350)

                          •
                    Power (400)
```

### Color Scheme

- **Speed**: Blue (#3b82f6)
- **Stamina**: Green (#22c55e)
- **Power**: Orange (#f97316)
- **Guts**: Amber/Yellow (#fbbf24)
- **Wit**: Sky Blue (#0ea5e9)

### Legend Layout

```text
[•] Speed  [•] Stamina  [•] Power  [•] Guts  [•] Wit
```text

Compact, single line (wraps if needed), with:

- 8px color dots
- 12px text
- Minimal spacing

### Grid Labels

Labels appear at the top (12 o'clock position) of each grid level:

```text
                    1000  ← Level 5 label
                     800  ← Level 4 label
                     600  ← Level 3 label
                     400  ← Level 2 label
                     200  ← Level 1 label
                      •   ← Center (0)
```

## What to Look For

### ✅ Correct Appearance

1. **Pentagon Shape**: Clear 5-sided polygon
2. **Stat Distribution**: Stats spread across pentagon, not clustered
3. **Grid Labels**: Five labels visible at top (200, 400, 600, 800, 1000)
4. **Legend**: All 5 stats visible, compact layout
5. **Colors**: Distinct colors for each stat
6. **Proportions**: Speed (500) should be halfway from center to edge

### ❌ Incorrect Appearance (Before Fix)

1. **Clustered Stats**: All stats near center (25% radius instead of 50%)
2. **No Grid Labels**: Empty grid with no reference values
3. **Cut-off Legend**: Some stats not visible
4. **Large Legend**: Takes up too much space

## Measurement Guide

To verify correct positioning:

1. **Measure from center to edge**: This is 100% (max value)
2. **Measure from center to Speed point**: Should be ~50% of edge distance
3. **Measure from center to Stamina point**: Should be ~45% of edge distance
4. **Measure from center to Power point**: Should be ~40% of edge distance
5. **Measure from center to Guts point**: Should be ~35% of edge distance
6. **Measure from center to Wit point**: Should be ~30% of edge distance

## Browser DevTools Inspection

### Check SVG viewBox

Open DevTools (F12) and inspect the SVG element:

```html
<svg viewBox="0 0 128 128" class="w-full h-full">
  <!-- For medium size, viewBox should be 128x128 -->
  <!-- For small size, viewBox should be 64x64 -->
  <!-- For large size, viewBox should be 192x192 -->
</svg>
```text

### Check Polygon Points

Inspect the data polygon element:

```html
<polygon points="64,35.2 89.6,58.4 78.4,89.6 49.6,89.6 38.4,58.4">
  <!-- Points should be distributed across the viewBox -->
  <!-- Not all clustered near center (64,64) -->
</polygon>
```text

### Check Grid Labels

Inspect the text elements:

```html
<text x="64" y="35.2" class="grid-label">200</text>
<text x="64" y="25.6" class="grid-label">400</text>
<text x="64" y="16.0" class="grid-label">600</text>
<text x="64" y="6.4" class="grid-label">800</text>
<text x="64" y="-3.2" class="grid-label">1000</text>
```text

## Responsive Behavior

### Mobile (320px-768px)

- Chart scales down proportionally
- Legend may wrap to 2-3 lines
- Grid labels remain readable (3px font for sm size)

### Tablet (768px-1024px)

- Chart at medium size
- Legend typically on one line
- Grid labels at 6px font

### Desktop (1024px+)

- Chart at medium or large size
- Legend on one line
- Grid labels at 6px or 9px font

## Dark Mode

In dark mode:

- Grid lines: Gray (#6b7280)
- Grid labels: Gray (#9ca3af)
- Stat colors: Slightly lighter variants
- Background: Dark gray (#1f2937)

## Accessibility

- SVG has `role="img"` and `aria-label="Stat radar chart"`
- Each data point has a `<title>` element for tooltips
- Colors have sufficient contrast (WCAG AA)
- Keyboard navigation supported (if interactive)

---

**Reference Date**: January 31, 2026
**Test URL**: <http://127.0.0.1:8000/characters/162>
**Test Character**: ID 162 (Speed=500, Stamina=450, Power=400, Guts=350, Wit=300)
