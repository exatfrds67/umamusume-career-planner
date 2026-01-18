# Support Card Placeholder Image Creation Guide

## Overview

This guide explains how to create placeholder images for support cards while the project awaits proper licensing for official Umamusume artwork.

---

## Required Placeholder Images

Create three placeholder images, one for each rarity tier:

1. **placeholder_ssr.png** - For SSR (Super Super Rare) cards
2. **placeholder_sr.png** - For SR (Super Rare) cards  
3. **placeholder_r.png** - For R (Rare) cards

---

## Image Specifications

### Dimensions

- **Width**: 400px
- **Height**: 600px
- **Aspect Ratio**: 2:3 (standard card ratio)
- **Format**: PNG with transparency support

### File Size

- Target: < 100KB per image
- Use PNG optimization tools if needed

---

## Design Guidelines

### Color Schemes by Rarity

**SSR (Super Super Rare)**:

- Primary: Gold/Yellow (#FFD700, #FFA500)
- Accent: White/Light (#FFFFFF, #FFF8DC)
- Border: Thick gold border (8-10px)
- Glow: Subtle golden glow effect

**SR (Super Rare)**:

- Primary: Silver/Gray (#C0C0C0, #A9A9A9)
- Accent: White/Light Blue (#FFFFFF, #E0F2FF)
- Border: Medium silver border (6-8px)
- Glow: Subtle silver shimmer

**R (Rare)**:

- Primary: Bronze/Brown (#CD7F32, #8B4513)
- Accent: Cream/Beige (#F5F5DC, #FFE4B5)
- Border: Thin bronze border (4-6px)
- Glow: Minimal or no glow

### Layout Elements

Each placeholder should include:

1. **Rarity Indicator** (Top)
   - Text: "SSR", "SR", or "R"
   - Font: Bold, large (48-60px)
   - Position: Centered at top, 40px from edge

2. **Card Type Icon** (Center)
   - Generic card back design
   - Umamusume-themed silhouette (horse, horseshoe, or racing motif)
   - Size: 200x200px
   - Position: Centered

3. **Placeholder Text** (Bottom)
   - Text: "Support Card"
   - Font: Medium (24-32px)
   - Position: Centered at bottom, 40px from edge

4. **Border**
   - Rounded corners (radius: 16px)
   - Thickness varies by rarity (see above)
   - Color matches rarity scheme

---

## Design Tools

### Recommended Software

**Free Options**:

- **GIMP** - <https://www.gimp.org/>
- **Inkscape** - <https://inkscape.org/>
- **Photopea** - <https://www.photopea.com/> (web-based)
- **Canva** - <https://www.canva.com/> (free tier)

**Paid Options**:

- Adobe Photoshop
- Adobe Illustrator
- Affinity Designer

### Online Generators

**Placeholder.com**:

```
https://via.placeholder.com/400x600/FFD700/FFFFFF?text=SSR+Support+Card
```

**DummyImage.com**:

```
https://dummyimage.com/400x600/ffd700/ffffff&text=SSR
```

---

## Quick Creation Methods

### Method 1: Using Canva (Easiest)

1. Go to <https://www.canva.com/>
2. Create custom size: 400x600px
3. Add background color (gold/silver/bronze)
4. Add text elements (rarity, "Support Card")
5. Add border using frame or shape
6. Download as PNG

### Method 2: Using GIMP (Most Control)

1. Create new image: 400x600px
2. Fill with gradient (rarity colors)
3. Add text layers
4. Add border using stroke selection
5. Add effects (glow, shadow)
6. Export as PNG

### Method 3: Using CSS/HTML (Developer-Friendly)

Create placeholders programmatically:

```html
<!DOCTYPE html>
<html>
<head>
<style>
.card {
    width: 400px;
    height: 600px;
    border-radius: 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    padding: 40px;
    font-family: Arial, sans-serif;
}
.ssr {
    background: linear-gradient(135deg, #FFD700, #FFA500);
    border: 8px solid #FFD700;
    box-shadow: 0 0 30px rgba(255, 215, 0, 0.5);
}
.rarity {
    font-size: 60px;
    font-weight: bold;
    color: white;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}
.icon {
    font-size: 120px;
    color: rgba(255,255,255,0.3);
}
.label {
    font-size: 28px;
    color: white;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
}
</style>
</head>
<body>
<div class="card ssr">
    <div class="rarity">SSR</div>
    <div class="icon">🏇</div>
    <div class="label">Support Card</div>
</div>
</body>
</html>
```

Then screenshot and save as PNG.

---

## Alternative: Use Existing Assets

### Generic Card Backs

Search for royalty-free card back designs:

- **Unsplash** - <https://unsplash.com/s/photos/card-back>
- **Pexels** - <https://www.pexels.com/search/playing%20card/>
- **Pixabay** - <https://pixabay.com/images/search/card/>

Filter by:

- License: Free for commercial use
- Orientation: Portrait
- Colors: Match rarity scheme

### Icon Libraries

For card type icons:

- **Font Awesome** - <https://fontawesome.com/>
- **Material Icons** - <https://fonts.google.com/icons>
- **Heroicons** - <https://heroicons.com/>

Relevant icons:

- Horse, horseshoe, trophy, star, lightning, heart

---

## Implementation Steps

1. **Create Images**:
   - Use one of the methods above
   - Create all three rarity placeholders
   - Ensure consistent style across all three

2. **Optimize Images**:

   ```bash
   # Using ImageMagick
   convert placeholder_ssr.png -quality 85 -strip placeholder_ssr.png
   
   # Using pngquant
   pngquant --quality=80-95 placeholder_ssr.png
   ```

3. **Save to Directory**:

   ```
   public/images/support_cards/
   ├── placeholder_ssr.png
   ├── placeholder_sr.png
   └── placeholder_r.png
   ```

4. **Verify in Browser**:
   - Visit: <http://127.0.0.1:8000/support-cards>
   - Check that placeholders display correctly
   - Test on different screen sizes

---

## Testing Checklist

- [ ] All three placeholder images created
- [ ] Images are 400x600px
- [ ] File sizes are < 100KB each
- [ ] Images display correctly in browser
- [ ] Rarity colors are distinct
- [ ] Text is readable
- [ ] Borders are visible
- [ ] Images work in light and dark mode
- [ ] Images are responsive on mobile

---

## Future Replacement

When official images become available:

1. **Update Database**:

   ```sql
   UPDATE ucp_support_cards 
   SET artwork_url = '/images/support_cards/kitasan_black_fire.jpg'
   WHERE internal_id = 'GLOBAL_SC_KITASAN_FIRE';
   ```

2. **Keep Placeholders**:
   - Don't delete placeholder files
   - They serve as fallbacks
   - Useful for new cards without images

3. **Add Attribution**:
   - Update README.md with image sources
   - Add copyright notices
   - Link to official sources

---

## Copyright Reminder

**Important**: These placeholders are temporary solutions. They should:

- NOT use copyrighted Umamusume artwork
- NOT copy official card designs
- Be clearly marked as placeholders
- Be replaced with licensed images when available

---

## Questions?

If you need help creating placeholders:

1. Check design tools documentation
2. Search for "card placeholder design" tutorials
3. Use online generators as starting point
4. Consult with a designer if budget allows

---

**End of Guide**
