# External API Frontend Integration - Implementation Guide

**Document Version**: 1.0.0
**Date**: 2026-01-25
**Status**: In Progress

## Overview

This document outlines the complete implementation of external API integration with the frontend for
character creation and support card management, with full database persistence.

## Implementation Status

### ✅ Phase 1: Backend API Endpoints (COMPLETED)

#### Character Prefill API

- **Controller**: `app/Http/Controllers/Api/CharacterPrefillController.php`
- **Routes**:
  - `GET /api/characters/prefill/search?q={query}` - Search characters
  - `GET /api/characters/prefill/{externalId}` - Get character prefill data

#### Support Card Rarity Inference

- **Service**: `app/Services/ExternalAPI/ResponseTransformer.php`
- **Feature**: Automatic rarity inference from card ID ranges
  - 10001-19999: R (134 cards)
  - 20001-29999: SR (89 cards)
  - 30001-39999: SSR (264 cards)

### 🔄 Phase 2: Frontend Integration (IN PROGRESS)

#### Character Creation Flow

1. User visits `/characters/create`
2. Can search external database for characters
3. Select character to prefill form data
4. Review and modify prefilled data
5. Submit to create character in database

#### Support Card Management Flow

1. User visits `/support-cards`
2. Browse external support cards
3. Import cards to personal collection
4. Attach cards to characters
5. Use in deck builder for career runs

## API Endpoints

### Character Prefill

#### Search Characters

```http
GET /api/characters/prefill/search?q=special+week
Authorization: Bearer {token}
```text

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": 1001,
      "name": "Special Week",
      "name_en": "Special Week",
      "name_jp": "スペシャルウィーク",
      "image": "https://api.umapyoi.net/images/characters/1001.png",
      "category": "Speed",
      "color": "#FF6B6B"
    }
  ],
  "total": 1
}
```

#### Get Prefill Data

```http
GET /api/characters/prefill/1001
Authorization: Bearer {token}
```text

**Response:**

```json
{
  "success": true,
  "data": {
    "external_id": 1001,
    "name": "Special Week",
    "name_en": "Special Week",
    "name_jp": "スペシャルウィーク",
    "image_url": "https://api.umapyoi.net/images/characters/1001.png",
    "category": "Speed",
    "color": "#FF6B6B",
    "stats": {
      "speed": 120,
      "stamina": 100,
      "power": 110,
      "guts": 90,
      "wit": 95
    },
    "aptitudes": {
      "distance": {
        "sprint": "A",
        "mile": "S",
        "medium": "A",
        "long": "B"
      },
      "surface": {
        "turf": "A",
        "dirt": "C"
      },
      "style": {
        "front_runner": "B",
        "pace_chaser": "A",
        "late_surger": "S",
        "end_closer": "A"
      }
    },
    "metadata": {
      "source": "umapyoi.net",
      "fetched_at": "2026-01-25T10:30:00Z"
    }
  },
  "source": "cache"
}
```

### Support Cards

#### List External Support Cards

```http
GET /api/external/support-cards
Authorization: Bearer {token}
```text

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": 30001,
      "name": "[Japan's Number 1 Stage]",
      "name_en": "[Japan's Number 1 Stage]",
      "title_en": "[Japan's Number 1 Stage]",
      "rarity": "SSR",
      "character_id": 1001,
      "gametora": "30001-special-week",
      "metadata": {
        "source": "umapyoi",
        "transformed_at": "2026-01-25T10:30:00Z"
      }
    }
  ],
  "source": "umapyoi.net",
  "cached": true
}
```

#### Import Support Card

```http
POST /api/support-cards/import-external
Authorization: Bearer {token}
Content-Type: application/json

{
  "external_id": 30001,
  "title_en": "[Japan's Number 1 Stage]",
  "chara_id": 1001,
  "gametora": "30001-special-week",
  "rarity": "SSR",
  "image_url": "https://gametora.com/images/umamusume/supports/tex_support_card_30001.png",
  "source": "umapyoi.net"
}
```text

**Response:**

```json
{
  "success": true,
  "message": "Support card imported successfully",
  "data": {
    "id": 1,
    "name": "[Japan's Number 1 Stage]",
    "internal_id": "ext_30001",
    "card_type": "speed",
    "rarity": "SSR",
    "character_name": "Special Week"
  }
}
```

## Frontend Implementation

### Character Creation with Prefill

#### Alpine.js Component Enhancement

Add to `resources/views/characters/create.blade.php`:

```javascript
function characterWizard() {
    return {
        // ... existing properties ...

        // External API integration
        externalCharacters: [],
        searchingExternal: false,
        externalSearchQuery: '',

        // Search external database
        async searchExternalCharacters() {
            if (this.externalSearchQuery.length < 2) {
                this.externalCharacters = [];
                return;
            }

            this.searchingExternal = true;

            try {
                const response = await fetch(
                    `/api/characters/prefill/search?q=${encodeURIComponent(this.externalSearchQuery)}`,
                    {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    }
                );

                const data = await response.json();

                if (data.success) {
                    this.externalCharacters = data.data;
                }
            } catch (error) {
                console.error('Failed to search external characters:', error);
            } finally {
                this.searchingExternal = false;
            }
        },

        // Load prefill data
        async loadPrefillData(externalId) {
            try {
                const response = await fetch(
                    `/api/characters/prefill/${externalId}`,
                    {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    }
                );

                const data = await response.json();

                if (data.success) {
                    // Prefill form data
                    this.formData.name = data.data.name;
                    this.formData.stats = data.data.stats;
                    this.formData.aptitudes = data.data.aptitudes;
                    this.formData.external_id = data.data.external_id;
                    this.formData.image_url = data.data.image_url;

                    // Show success notice
                    this.showExternalPrefillNotice = true;

                    // Close database search
                    this.showDatabase = false;
                }
            } catch (error) {
                console.error('Failed to load prefill data:', error);
            }
        }
    };
}
```text

### Support Card Import

#### Alpine.js Component for Support Cards

Add to `resources/views/support-cards/index.blade.php`:

```javascript
function supportCardManager() {
    return {
        cards: [],
        loading: false,
        importing: false,

        async loadExternalCards() {
            this.loading = true;

            try {
                const response = await fetch('/api/external/support-cards', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.cards = data.data;
                }
            } catch (error) {
                console.error('Failed to load external cards:', error);
            } finally {
                this.loading = false;
            }
        },

        async importCard(card) {
            this.importing = true;

            try {
                const response = await fetch('/api/support-cards/import-external', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        external_id: card.id,
                        title_en: card.title_en,
                        chara_id: card.character_id,
                        gametora: card.gametora,
                        rarity: card.rarity,
                        image_url: `https://gametora.com/images/umamusume/supports/tex_support_card_${card.id}.png`,
                        source: 'umapyoi.net'
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Show success message
                    this.showToast('Support card imported successfully!', 'success');

                    // Mark as imported
                    card.is_imported = true;
                } else {
                    this.showToast(data.message || 'Failed to import card', 'error');
                }
            } catch (error) {
                console.error('Failed to import card:', error);
                this.showToast('Failed to import card', 'error');
            } finally {
                this.importing = false;
            }
        }
    };
}
```

## Database Integration

### Character Creation with External Data

When a character is created with external data, the following is stored:

```php
// In CharacterController@store
$character = Character::create([
    'user_id' => Auth::id(),
    'name' => $request->input('name'),
    'scenario_type' => $request->input('scenario_type'),
    'current_speed' => $request->input('stats.speed'),
    'current_stamina' => $request->input('stats.stamina'),
    'current_power' => $request->input('stats.power'),
    'current_guts' => $request->input('stats.guts'),
    'current_wit' => $request->input('stats.wit'),
    // ... other fields ...
    'external_source_id' => $request->input('external_id'), // Link to external API
    'external_source' => 'umapyoi.net',
    'avatar_url' => $request->input('image_url'),
]);

// Create aptitudes
foreach ($request->input('aptitudes.distance') as $distance => $grade) {
    Aptitude::create([
        'character_id' => $character->id,
        'type' => 'distance',
        'category' => $distance,
        'grade' => $grade,
    ]);
}

// ... similar for surface and style aptitudes
```text

### Support Card Import Process

When a support card is imported:

```php
// In ExternalImportController@importSupportCard
$supportCard = SupportCardDefinition::updateOrCreate(
    ['internal_id' => 'ext_' . $request->input('external_id')],
    [
        'name' => $request->input('title_en'),
        'card_type' => $this->inferCardType($request->input('gametora')),
        'rarity' => $request->input('rarity'),
        'character_name' => $this->extractCharacterName($request->input('gametora')),
        'artwork_url' => $request->input('image_url'),
        'card_metadata' => [
            'external_id' => $request->input('external_id'),
            'gametora' => $request->input('gametora'),
            'source' => $request->input('source'),
            'imported_at' => now()->toISOString(),
        ],
        'is_active' => true,
    ]
);
```

## Integration with Training, Races, and Skills

### Training Integration

When a character trains with support cards:

```php
// In TrainingSession
$trainingSession = TrainingSession::create([
    'career_id' => $career->id,
    'turn_number' => $career->current_turn,
    'training_type' => $request->input('training_type'),
    'participating_support_cards' => $supportCardIds, // Array of card IDs
    'stat_gains' => [
        'speed' => $speedGain,
        'stamina' => $staminaGain,
        // ... calculated with support card bonuses
    ],
    'skill_hints_obtained' => $skillHints, // From support cards
    'friendship_gains' => $friendshipGains, // Per card
]);

// Update character-support card friendship
foreach ($supportCardIds as $cardId) {
    CharacterSupportCard::where('character_id', $character->id)
        ->where('support_card_id', $cardId)
        ->increment('friendship_level');
}
```text

### Race Integration

When a character races:

```php
// In Race
$race = Race::create([
    'career_id' => $career->id,
    'turn_number' => $career->current_turn,
    'race_name' => $request->input('race_name'),
    'distance' => $request->input('distance'),
    'surface' => $request->input('surface'),
    'support_deck_snapshot' => $character->supportCards->toArray(), // Current deck
    'skills_activated' => $activatedSkills, // Skills from support cards
    'result' => $raceResult,
    'performance_data' => [
        'support_card_bonuses' => $bonuses,
        'skill_effects' => $skillEffects,
    ],
]);
```

### Skill Integration

When a character learns a skill:

```php
// In SkillAcquisition
$skillAcquisition = SkillAcquisition::create([
    'character_id' => $character->id,
    'skill_id' => $skill->id,
    'acquisition_source' => 'support_card',
    'source_id' => $supportCard->id, // Which card provided it
    'sp_cost' => $skill->base_sp_cost * (1 - $hintDiscount), // Hint discount
    'hint_level' => $hintLevel, // 0-5 hint levels (0=no hints, 5=40% max discount)
    'acquired_at_turn' => $career->current_turn,
]);
```text

## Testing

### Manual Testing Checklist

#### Character Creation

- [ ] Search external characters
- [ ] Select character and verify prefill
- [ ] Modify prefilled data
- [ ] Submit and verify database record
- [ ] Check external_source_id is stored
- [ ] Verify aptitudes are created correctly

#### Support Card Import Testing

- [ ] Browse external support cards
- [ ] Filter by rarity (R/SR/SSR)
- [ ] Import a card
- [ ] Verify card appears in collection
- [ ] Check card_metadata contains external data
- [ ] Verify duplicate prevention

#### Integration

- [ ] Attach imported support cards to character
- [ ] Start a career run with support deck
- [ ] Perform training and verify bonuses
- [ ] Complete a race and verify deck snapshot
- [ ] Learn a skill from support card hint

### API Testing

```bash
# Test character search
curl -X GET "http://127.0.0.1:8000/api/characters/prefill/search?q=special" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"

# Test character prefill
curl -X GET "http://127.0.0.1:8000/api/characters/prefill/1001" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"

# Test support card import
curl -X POST "http://127.0.0.1:8000/api/support-cards/import-external" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "external_id": 30001,
    "title_en": "[Japan'\''s Number 1 Stage]",
    "chara_id": 1001,
    "gametora": "30001-special-week",
    "rarity": "SSR",
    "image_url": "https://gametora.com/images/umamusume/supports/tex_support_card_30001.png",
    "source": "umapyoi.net"
  }'
```

## Next Steps

1. **Complete Frontend Integration** (2-3 days)
   - Update character creation form with external search
   - Add support card import UI
   - Implement deck builder with imported cards

2. **Enhance Training/Race Integration** (2-3 days)
   - Calculate support card bonuses in training
   - Track friendship levels
   - Apply skill hints and SP discounts

3. **Add Bulk Operations** (1-2 days)
   - Bulk import support cards
   - Export character with support deck
   - Deck templates

4. **Testing & Documentation** (1-2 days)
   - Write automated tests
   - Create user guide
   - API documentation

## Related Documentation

- [Support Card Rarity Solution](../external-api-integration/SUPPORT_CARD_RARITY_SOLUTION.md)
- [Support Card Implementation Roadmap](../external-api-integration/SUPPORT_CARD_IMPLEMENTATION_ROADMAP.md)
- [External Data Browser Filters](../external-api-integration/EXTERNAL_DATA_BROWSER_FILTERS.md)

## Change Log

### Version 1.0.0 (2026-01-25)

- Initial implementation plan
- Created CharacterPrefillController
- Added API routes for character prefill
- Documented integration points
- Provided frontend implementation examples
