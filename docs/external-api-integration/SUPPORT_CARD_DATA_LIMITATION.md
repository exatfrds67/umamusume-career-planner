# Support Card Data Limitation - umapyoi.net API

**Document Version**: 1.0.0  
**Date**: 2026-01-25  
**Status**: Current Limitation  

## Issue Summary

The umapyoi.net API `/api/v1/support` endpoint returns **minimal support card information** in the list view. Detailed card statistics, effects, skills, and bonuses are **not provided** by the external API.

## What Data IS Available

From the umapyoi.net API, we receive:

```json
{
  "id": 10001,
  "chara_id": 1001,
  "gametora": "10001-special-week",
  "title_en": "[Tracen Academy]",
  "rarity": "R"
}
```

### Available Fields

- **id**: Card ID (e.g., 10001)
- **chara_id**: Associated character ID
- **gametora**: GameTora slug for external linking
- **title_en**: Card title in English
- **rarity**: R, SR, or SSR

### NOT Available from API

- ❌ Card type (Speed, Stamina, Power, Guts, Wisdom, Friend)
- ❌ Stat bonuses (speed_bonus, stamina_bonus, etc.)
- ❌ Card effects and unique effects
- ❌ Skills provided by the card
- ❌ Friendship bonus values
- ❌ Limit break information
- ❌ Event bonuses
- ❌ Training bonuses

## Current Implementation

### ResponseTransformer Behavior

The `ResponseTransformer::transformSupportCard()` method attempts to extract these fields but they return empty/zero values:

```php
'stats' => [
    'speed' => 0,      // Always 0 from API
    'stamina' => 0,    // Always 0 from API
    'power' => 0,      // Always 0 from API
    'guts' => 0,       // Always 0 from API
    'wisdom' => 0,     // Always 0 from API
],
'effects' => [],       // Always empty from API
'skills' => [],        // Always empty from API
'unique_effect' => null, // Always null from API
'friendship_bonus' => 0, // Always 0 from API
```

### External Data Browser Display

The browser currently shows:

- ✅ Card image (from GameTora CDN)
- ✅ Card name/title
- ✅ Associated Uma Musume character
- ✅ Rarity badge (R/SR/SSR)
- ✅ Card ID
- ✅ Link to GameTora for full details

## Why This Limitation Exists

1. **API Design**: umapyoi.net provides a lightweight list endpoint for browsing
2. **Data Volume**: Full card details would significantly increase response size
3. **External Source**: Detailed stats are maintained on GameTora.com

## Workarounds & Solutions

### Current Solution: GameTora Integration

Users can click "View Full Details on GameTora" to see complete card information on the external site.

**Advantages:**

- ✅ Always up-to-date information
- ✅ Community-maintained accuracy
- ✅ No data synchronization needed
- ✅ Includes user ratings and comments

**Disadvantages:**

- ❌ Requires leaving the application
- ❌ No offline access to details
- ❌ Cannot filter/sort by detailed stats

### Future Enhancement Options

#### Option 1: Web Scraping GameTora (NOT RECOMMENDED)

- **Pros**: Could get detailed data
- **Cons**:
  - Legal/ethical concerns
  - Fragile (breaks when site changes)
  - Rate limiting issues
  - Maintenance burden

#### Option 2: Manual Data Entry

- **Pros**: Full control over data
- **Cons**:
  - 487+ cards to enter manually
  - Requires constant updates for new cards
  - High maintenance burden
  - Prone to human error

#### Option 3: Community Contribution System

- **Pros**: Crowdsourced accuracy
- **Cons**:
  - Requires moderation
  - Data quality concerns
  - Complex implementation

#### Option 4: Partner with GameTora/umapyoi

- **Pros**: Official data source
- **Cons**:
  - Requires business relationship
  - May have API costs
  - Dependency on external party

#### Option 5: Import from User Screenshots (OCR)

- **Pros**: User provides their own data
- **Cons**:
  - OCR accuracy issues
  - Requires user effort
  - Language barriers (Japanese text)

## Recommended Approach

### Short Term (Current)

1. ✅ Display available basic information
2. ✅ Provide clear link to GameTora for details
3. ✅ Add informational notice explaining limitation
4. ✅ Focus on import workflow for user's own cards

### Medium Term

1. Allow users to manually add detailed stats after import
2. Implement OCR for screenshot-based data entry
3. Build internal database of card stats from user contributions

### Long Term

1. Explore partnership with GameTora or umapyoi.net
2. Consider building comprehensive card database
3. Implement community verification system

## User Communication

### Modal Notice (Implemented)

```
ℹ️ Detailed Stats Available After Import

Import this card to your collection to track stats, effects, 
and skills. Or view complete details on GameTora.
```

### Documentation

- Clearly state in user guide that external browse is for discovery
- Explain that detailed tracking requires import
- Provide GameTora links for reference

## Technical Details

### API Endpoint

```
GET https://api.umapyoi.net/api/v1/support
```

### Response Structure

```json
[
  {
    "id": 10001,
    "chara_id": 1001,
    "gametora": "10001-special-week",
    "title_en": "[Tracen Academy]",
    "rarity": "R"
  },
  // ... 486 more cards
]
```

### Individual Card Endpoint

```
GET https://api.umapyoi.net/api/v1/support/{id}
```

**Status**: Returns validation error - endpoint may not be fully implemented or requires different schema.

## Impact on Features

### Affected Features

- ❌ Cannot filter by card type in External Data Browser
- ❌ Cannot sort by stat bonuses
- ❌ Cannot show stat comparison in browse view
- ❌ Cannot display skill lists in browse view

### Unaffected Features

- ✅ Basic browsing and discovery
- ✅ Rarity filtering
- ✅ Name search
- ✅ Import to collection
- ✅ Character association
- ✅ External reference linking

## Related Files

- `app/Services/ExternalAPI/UmapyoiApiClient.php` - API client
- `app/Services/ExternalAPI/ResponseTransformer.php` - Data transformation
- `resources/views/external-data/browse.blade.php` - UI display
- `app/Http/Controllers/Api/ExternalDataController.php` - API controller

## Conclusion

The limitation is **inherent to the external API design** and not a bug in our implementation. The current approach of linking to GameTora for detailed information is the most practical solution given the constraints.

Users who need detailed card tracking should:

1. Browse cards in External Data Browser
2. Click "View on GameTora" for full details
3. Import cards they own to their collection
4. Manually add detailed stats to their imported cards (future feature)

## Change Log

### Version 1.0.0 (2026-01-25)

- Initial documentation of limitation
- Identified available vs. unavailable data fields
- Proposed short/medium/long term solutions
- Documented current workaround (GameTora linking)
