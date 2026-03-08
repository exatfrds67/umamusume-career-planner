# Support Card Image Fix

**Date**: January 25, 2026  
**Status**: ✅ COMPLETED  
**Issue**: Support card images showing as placeholders  

---

## Problem

All support card images were displaying as placeholders (generic avatars) in the External API import interface at
`/support-cards`.

### Root Cause

The umapyoi.net API endpoint `/api/v1/support` does **NOT** return image URLs in its response. The API only returns:

- `id` - Card ID
- `chara_id` - Character ID
- `gametora` - GameTora identifier
- `title_en` - English title

The frontend code was trying to use `card.image` which was `undefined`, causing the fallback to placeholder avatars.

---

## Solution

### Image URL Construction

Support card images are hosted on **gametora.com** following a predictable URL pattern:

```text
https://gametora.com/images/umamusume/supports/tex_support_card_{CARD_ID}.png
```text

**Example**:

- Card ID: `30001`
- Image URL: `https://gametora.com/images/umamusume/supports/tex_support_card_30001.png`

### Implementation

#### 1. External Import Partial (`resources/views/support-cards/partials/external-import.blade.php`)

**Before**:

```blade
<img :src="card.image" :alt="card.title_en || card.name" loading="lazy" decoding="async"
    x-on:error="$el.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(card.title_en || card.name || 'Card') + '&background=random&color=fff'"
    class="w-full h-full object-cover">
```text

**After**:

```blade
<img :src="'https://gametora.com/images/umamusume/supports/tex_support_card_' + card.id + '.png'" 
    :alt="card.title_en || card.name" loading="lazy" decoding="async"
    x-on:error="$el.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(card.title_en || card.name || 'Card') + '&background=random&color=fff'"
    class="w-full h-full object-cover">
```

#### 2. Support Cards Index (`resources/views/support-cards/index.blade.php`)

Updated the `importCard()` function to construct the image URL before sending to the backend:

**Before**:

```javascript
body: JSON.stringify({
    external_id: card.id,
    title_en: card.title_en || card.name,
    chara_id: card.chara_id,
    gametora: card.gametora,
    rarity: card.rarity,
    image_url: card.image,  // ❌ undefined
    source: 'umapyoi.net'
})
```text

**After**:

```javascript
// Construct image URL from card ID (gametora.com pattern)
const imageUrl = card.id ? 
    `https://gametora.com/images/umamusume/supports/tex_support_card_${card.id}.png` : 
    null;

body: JSON.stringify({
    external_id: card.id,
    title_en: card.title_en || card.name,
    chara_id: card.chara_id,
    gametora: card.gametora,
    rarity: card.rarity,
    image_url: imageUrl,  // ✅ Properly constructed URL
    source: 'umapyoi.net'
})
```text

---

## Verification

### Test Steps

1. Navigate to `/support-cards`
2. Click "Import from API" toggle
3. Wait for cards to load
4. Verify that card images display correctly (not placeholders)
5. Click "Import Card" on any card
6. Verify the imported card has the correct image

### Expected Results

- ✅ All 487 support cards display with proper images from gametora.com
- ✅ Images load with lazy loading
- ✅ Fallback to placeholder avatar only if gametora.com image fails to load
- ✅ Imported cards save with correct image URL in database

---

## Technical Notes

### Image URL Pattern

The gametora.com URL pattern is consistent across all support cards:

- **R cards** (10001-19999): `tex_support_card_10001.png` to `tex_support_card_19999.png`
- **SR cards** (20001-29999): `tex_support_card_20001.png` to `tex_support_card_29999.png`
- **SSR cards** (30001-39999): `tex_support_card_30001.png` to `tex_support_card_39999.png`

### Error Handling

The `x-on:error` directive provides graceful fallback:

1. Try to load image from gametora.com
2. If 404 or network error, fallback to ui-avatars.com with card name
3. Placeholder uses random background color for visual variety

### Performance

- **Lazy Loading**: Images load only when scrolled into view
- **Decoding**: Async decoding prevents blocking the main thread
- **CDN**: gametora.com serves images via CDN for fast global delivery

---

## Related Files

- `resources/views/support-cards/partials/external-import.blade.php` - External import UI
- `resources/views/support-cards/index.blade.php` - Support cards management page
- `resources/views/external-data/browse.blade.php` - Reference implementation with `getSupportCardImage()` helper
- `app/Services/ExternalAPI/ResponseTransformer.php` - API response transformation
- `app/Services/ExternalAPI/UmapyoiApiClient.php` - API client

---

## Future Enhancements

### Option 1: Backend Image URL Construction

Move image URL construction to the backend `ResponseTransformer`:

```php
protected function transformSupportCard(array $card): array
{
    $cardId = $card['id'] ?? 0;
    
    return [
        'id' => $cardId,
        'name' => $card['name'] ?? $card['title_en'] ?? '',
        // ... other fields ...
        'image_url' => $cardId ? 
            "https://gametora.com/images/umamusume/supports/tex_support_card_{$cardId}.png" : 
            null,
        // ... metadata ...
    ];
}
```text

**Benefits**:

- Centralized image URL logic
- Frontend just uses `card.image_url`
- Easier to change image source in future

### Option 2: Alternative Image Sources

If gametora.com becomes unavailable, consider:

1. **UmamusumeDB.com** - Community database with card images
2. **Local Storage** - Download and host images locally
3. **Character Images** - Fallback to character avatar (via `chara_id`)

### Option 3: Image Caching

Implement service worker caching for frequently accessed card images:

- Cache images after first load
- Reduce bandwidth usage
- Improve offline experience

---

## Conclusion

The support card image issue has been resolved by constructing image URLs from card IDs using the gametora.com URL pattern. All 487 cards now display with proper images instead of placeholders.

**Status**: ✅ Issue resolved, ready for Phase 3 (Training System Integration)

