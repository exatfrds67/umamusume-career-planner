# API Reference

Complete API documentation for the Umamusume Career Planner.

## Base URL

```text
https://api.example.com/api/v1
```

## Authentication

All API endpoints require authentication using Laravel Sanctum tokens.

### Headers

```text
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Obtaining a Token

```http
POST /api/login
```text

**Request Body:**

```json
{
    "email": "user@example.com",
    "password": "password"
}
```

**Response:**

```json
{
    "token": "1|abc123...",
    "user": {
        "id": 1,
        "name": "User Name",
        "email": "user@example.com"
    }
}
```text

---

## Characters

### List Characters

```http
GET /api/v1/characters
```

**Query Parameters:**

| Parameter | Type | Description |
| --- | --- | --- |
| `per_page` | integer | Items per page (default: 15) |
| `page` | integer | Page number |
| `search` | string | Search by name |
| `scenario_type` | string | Filter by scenario |

**Response:**

```json
{
    "data": [
        {
            "id": 1,
            "name": "Character Name",
            "scenario_type": "ura_finale",
            "speed_stat": 500,
            "stamina_stat": 400,
            "power_stat": 300,
            "guts_stat": 200,
            "wit_stat": 100,
            "energy_level": 100,
            "mood_status": "good",
            "created_at": "2026-01-20T12:00:00Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 15,
        "total": 75
    }
}
```text

### Get Character

```http
GET /api/v1/characters/{id}
```

**Response:**

```json
{
    "data": {
        "id": 1,
        "name": "Character Name",
        "scenario_type": "ura_finale",
        "speed_stat": 500,
        "stamina_stat": 400,
        "power_stat": 300,
        "guts_stat": 200,
        "wit_stat": 100,
        "energy_level": 100,
        "mood_status": "good",
        "available_sp": 1500,
        "aptitudes": [...],
        "factors": [...],
        "created_at": "2026-01-20T12:00:00Z"
    }
}
```text

### Create Character

```http
POST /api/v1/characters
```

**Request Body:**

```json
{
    "name": "New Character",
    "scenario_type": "ura_finale",
    "speed_stat": 100,
    "stamina_stat": 100,
    "power_stat": 100,
    "guts_stat": 100,
    "wit_stat": 100
}
```text

**Response:** `201 Created`

### Update Character

```http
PUT /api/v1/characters/{id}
```

**Request Body:**

```json
{
    "name": "Updated Name",
    "speed_stat": 600
}
```text

**Response:** `200 OK`

### Delete Character

```http
DELETE /api/v1/characters/{id}
```

**Response:** `204 No Content`

---

## Careers

### List Careers

```http
GET /api/v1/careers
```text

**Query Parameters:**

| Parameter | Type | Description |
| --- | --- | --- |
| `character_id` | integer | Filter by character |
| `status` | string | Filter by status (active, completed, abandoned) |

### Get Career

```http
GET /api/v1/careers/{id}
```

### Create Career

```http
POST /api/v1/careers
```text

**Request Body:**

```json
{
    "character_id": 1,
    "scenario_type": "ura_finale"
}
```

### Update Career

```http
PUT /api/v1/careers/{id}
```text

**Request Body:**

```json
{
    "status": "completed"
}
```

### Delete Career

```http
DELETE /api/v1/careers/{id}
```text

### Career Statistics

```http
GET /api/v1/careers/{id}/statistics
```

**Response:**

```json
{
    "data": {
        "total_training_sessions": 72,
        "total_stat_gains": {
            "speed": 800,
            "stamina": 600,
            "power": 500,
            "guts": 400,
            "wit": 300
        },
        "efficiency_rating": 85.5,
        "race_win_rate": 75.0
    }
}
```text

### Career Report

```http
GET /api/v1/careers/{id}/report
```

### Compare Careers

```http
POST /api/v1/careers/compare
```text

**Request Body:**

```json
{
    "career_ids": [1, 2, 3]
}
```

---

## Training Sessions

### List Training Sessions

```http
GET /api/v1/careers/{careerId}/training-sessions
```text

### Create Training Session

```http
POST /api/v1/careers/{careerId}/training-sessions
```

**Request Body:**

```json
{
    "training_type": "speed",
    "turn_number": 1
}
```text

### Training Predictions

```http
GET /api/v1/careers/{careerId}/training-predictions
```

**Response:**

```json
{
    "data": {
        "predictions": [
            {
                "training_type": "speed",
                "expected_gains": {
                    "speed": 15,
                    "power": 5
                },
                "success_rate": 95.0,
                "recommended": true
            }
        ]
    }
}
```text

---

## Races

### List Races

```http
GET /api/v1/careers/{careerId}/races
```

### Available Races

```http
GET /api/v1/careers/{careerId}/available-races
```text

### Create Race Entry

```http
POST /api/v1/careers/{careerId}/races
```

**Request Body:**

```json
{
    "race_name": "Japan Cup",
    "race_grade": "G1",
    "turn_number": 50
}
```text

### Update Race Result

```http
PUT /api/v1/careers/{careerId}/races/{raceId}
```

**Request Body:**

```json
{
    "finish_position": 1,
    "won_race": true,
    "sp_reward": 50
}
```text

---

## Skills

### List Skills

```http
GET /api/v1/skills
```

**Query Parameters:**

| Parameter | Type | Description |
| --- | --- | --- |
| `skill_type` | string | Filter by type (normal, rare, unique, inherited) |
| `search` | string | Search by name |
| `min_sp_cost` | integer | Minimum SP cost |
| `max_sp_cost` | integer | Maximum SP cost |

### Get Skill

```http
GET /api/v1/skills/{id}
```text

### Skill Hints

```http
GET /api/v1/skills/{id}/hints
```

### Character Skills

```http
GET /api/v1/characters/{characterId}/skills
```text

### Acquire Skill

```http
POST /api/v1/characters/{characterId}/skills
```

**Request Body:**

```json
{
    "skill_id": 1
}
```text

### Remove Skill

```http
DELETE /api/v1/characters/{characterId}/skills/{skillId}
```

### Skill Recommendations

```http
GET /api/v1/skills/analysis/recommendations
```text

**Query Parameters:**

| Parameter | Type | Description |
| --- | --- | --- |
| `character_id` | integer | Character to analyze |

### Character-Specific Skill Recommendations (AI-Powered)

```http
POST /api/characters/{characterId}/skill-recommendations
```

**Authentication:** Required (`auth:sanctum`)

**Description:** Get AI-powered skill recommendations for a specific character based on their current stats, aptitudes, acquired skills, available hints, and build strategy. This endpoint uses the Neuron AI service to provide intelligent skill acquisition suggestions.

**Path Parameters:**

| Parameter | Type | Required | Description |
| --- | --- | --- | --- |
| `characterId` | integer | Yes | The ID of the character to get recommendations for |

**Request Body:**

```json
{
    "skill_context": {
        "available_sp": 1500,
        "race_preferences": {
            "preferred_distance": "medium",
            "preferred_surface": "turf",
            "preferred_running_style": "leader"
        },
        "build_strategy": "Focus on speed and acceleration skills for early positioning",
        "upcoming_races": [
            {
                "name": "Japan Cup",
                "distance_category": "long",
                "surface": "turf",
                "turns_until": 5
            }
        ],
        "additional_context": "Need skills for rainy weather conditions"
    }
}
```text

**Request Body Parameters:**

| Parameter | Type | Required | Description | Validation |
| --- | --- | --- | --- | --- |
| `skill_context` | object | No | Context for skill recommendations | - |
| `skill_context.available_sp` | integer | No | Available skill points | min:0 |
| `skill_context.race_preferences` | object | No | Character's race preferences | - |
| `skill_context.race_preferences.preferred_distance` | string | No | Preferred race distance | One of: short, mile, medium, long |
| `skill_context.race_preferences.preferred_surface` | string | No | Preferred race surface | One of: turf, dirt |
| `skill_context.race_preferences.preferred_running_style` | string | No | Preferred running style | One of: runner, leader, betweener, chaser |
| `skill_context.build_strategy` | string | No | Overall build strategy description | max:1000 characters |
| `skill_context.upcoming_races` | array | No | List of upcoming races to prepare for | - |
| `skill_context.upcoming_races.*.name` | string | Required if races provided | Race name | max:255 characters |
| `skill_context.upcoming_races.*.distance_category` | string | No | Race distance category | One of: short, mile, medium, long |
| `skill_context.upcoming_races.*.surface` | string | No | Race surface type | One of: turf, dirt |
| `skill_context.upcoming_races.*.turns_until` | integer | No | Number of turns until race | min:0 |
| `skill_context.additional_context` | string | No | Additional context or notes | max:1000 characters |

**Success Response (200 OK):**

```json
{
    "success": true,
    "data": {
        "recommendations": [
            {
                "skill_id": 42,
                "skill_name": "Accelerate",
                "skill_type": "normal",
                "base_sp_cost": 120,
                "effective_sp_cost": 72,
                "hint_level": 3,
                "priority": "high",
                "reasoning": "Excellent for leader running style and medium distance races",
                "synergies": ["Speed Star", "Quick Start"],
                "acquisition_timing": "immediate"
            },
            {
                "skill_id": 58,
                "skill_name": "Corner Master",
                "skill_type": "rare",
                "base_sp_cost": 180,
                "effective_sp_cost": 180,
                "hint_level": 0,
                "priority": "medium",
                "reasoning": "Useful for turf races with multiple corners",
                "synergies": ["Curve Specialist"],
                "acquisition_timing": "after_next_race"
            }
        ],
        "total_recommended_sp": 252,
        "available_sp": 1500,
        "remaining_sp": 1248,
        "strategy_summary": "Focus on acceleration and positioning skills for leader strategy",
        "processing_time_ms": 1234.56,
        "timestamp": "2026-01-31T12:00:00+00:00"
    },
    "message": "Skill recommendations generated successfully."
}
```

**Response Fields:**

| Field | Type | Description |
| --- | --- | --- |
| `success` | boolean | Whether the request was successful |
| `data` | object | Recommendation data |
| `data.recommendations` | array | List of recommended skills |
| `data.recommendations[].skill_id` | integer | Skill database ID |
| `data.recommendations[].skill_name` | string | Skill name |
| `data.recommendations[].skill_type` | string | Skill type (normal, rare, unique, inherited) |
| `data.recommendations[].base_sp_cost` | integer | Base SP cost without hints |
| `data.recommendations[].effective_sp_cost` | integer | Actual SP cost with hint discounts applied |
| `data.recommendations[].hint_level` | integer | Current hint level (0-5) |
| `data.recommendations[].priority` | string | Acquisition priority (high, medium, low) |
| `data.recommendations[].reasoning` | string | AI explanation for recommendation |
| `data.recommendations[].synergies` | array | Skills that synergize with this recommendation |
| `data.recommendations[].acquisition_timing` | string | Suggested timing (immediate, after_next_race, late_game) |
| `data.total_recommended_sp` | integer | Total SP needed for all recommendations |
| `data.available_sp` | integer | Character's available SP |
| `data.remaining_sp` | integer | SP remaining after acquiring recommendations |
| `data.strategy_summary` | string | Overall strategy summary |
| `data.processing_time_ms` | float | Processing time in milliseconds |
| `data.timestamp` | string | ISO 8601 timestamp |
| `message` | string | Success message |

**Error Responses:**

**401 Unauthorized:**

```json
{
    "success": false,
    "message": "Authentication required."
}
```text

**403 Forbidden:**

```json
{
    "success": false,
    "message": "You do not have permission to access this character."
}
```

**404 Not Found:**

```json
{
    "success": false,
    "message": "Character not found."
}
```text

**422 Unprocessable Entity:**

```json
{
    "success": false,
    "message": "Invalid skill context structure.",
    "errors": {
        "skill_context.available_sp": ["Available SP cannot be negative."],
        "skill_context.race_preferences.preferred_distance": ["Preferred distance must be one of: short, mile, medium, long."]
    }
}
```

**503 Service Unavailable:**

```json
{
    "success": false,
    "message": "Unable to generate skill recommendations. Please try again.",
    "error": "AI service temporarily unavailable"
}
```text

**Rate Limiting:**

This endpoint is rate-limited to prevent abuse of AI resources:

- **Authenticated users:** 10 requests per minute
- **Premium users:** 30 requests per minute

**Example Usage:**

```javascript
// JavaScript/Fetch example
const response = await fetch('/api/characters/123/skill-recommendations', {
    method: 'POST',
    headers: {
        'Authorization': 'Bearer YOUR_TOKEN',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
    body: JSON.stringify({
        skill_context: {
            available_sp: 1500,
            race_preferences: {
                preferred_distance: 'medium',
                preferred_surface: 'turf',
                preferred_running_style: 'leader'
            },
            build_strategy: 'Focus on speed and acceleration'
        }
    })
});

const data = await response.json();
console.log(data.data.recommendations);
```

```bash
# cURL example
curl -X POST "https://api.example.com/api/characters/123/skill-recommendations" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "skill_context": {
      "available_sp": 1500,
      "race_preferences": {
        "preferred_distance": "medium",
        "preferred_surface": "turf",
        "preferred_running_style": "leader"
      }
    }
  }'
```text

**Notes:**

- The `skill_context` parameter is optional. If not provided, recommendations will be based solely on character stats and aptitudes.
- AI recommendations are generated in real-time and may take 1-3 seconds to process.
- The endpoint requires the character to belong to the authenticated user (authorization check).
- Recommendations consider the character's current skills, available hints, and SP budget.
- The AI analyzes skill synergies and provides strategic timing suggestions.

---

## Support Cards

### List Support Cards

```http
GET /api/v1/support-cards
```

**Query Parameters:**

| Parameter | Type | Description |
| --- | --- | --- |
| `card_type` | string | Filter by type (speed, stamina, power, guts, wit, friend) |
| `rarity` | string | Filter by rarity (R, SR, SSR) |
| `meta_tier` | string | Filter by tier (S+, S, A, B, C) |
| `search` | string | Search by name |

### Get Support Card

```http
GET /api/v1/support-cards/{id}
```text

### Meta Ranking

```http
GET /api/v1/support-cards/meta-ranking
```

### Card Synergies

```http
GET /api/v1/support-cards/{id}/synergies
```text

---

## Character Deck

### Get Deck

```http
GET /api/v1/characters/{characterId}/deck
```

### Add Card to Deck

```http
POST /api/v1/characters/{characterId}/deck
```text

**Request Body:**

```json
{
    "support_card_id": 1,
    "position_slot": 1
}
```

### Remove Card from Deck

```http
DELETE /api/v1/characters/{characterId}/deck/{slot}
```text

### Deck Analysis

```http
GET /api/v1/characters/{characterId}/deck/analysis
```

### Deck Optimization

```http
GET /api/v1/characters/{characterId}/deck/optimization
```text

---

## AI Advisor

### Create Conversation

```http
POST /api/v1/ai/conversations
```

### Send Message

```http
POST /api/v1/ai/conversations/{conversationId}/messages
```text

**Request Body:**

```json
{
    "message": "What training should I do next?"
}
```

### Get Conversation History

```http
GET /api/v1/ai/conversations/{conversationId}
```text

---

## OCR

### Upload Image

```http
POST /api/v1/ocr/upload
```

**Request Body:** `multipart/form-data`

| Field | Type | Description |
| --- | --- | --- |
| `image` | file | Image file (PNG, JPG) |
| `type` | string | Extraction type (character, stats, skills) |

**Response:**

```json
{
    "data": {
        "extraction_id": "abc123",
        "status": "processing"
    }
}
```text

### Get Extraction Result

```http
GET /api/v1/ocr/extractions/{extractionId}
```

---

## Data Export/Import

### Export Data

```http
GET /api/v1/export
```text

**Query Parameters:**

| Parameter | Type | Description |
| --- | --- | --- |
| `format` | string | Export format (json, csv) |
| `type` | string | Data type (characters, careers, all) |

### Import Data

```http
POST /api/v1/import
```

**Request Body:** `multipart/form-data`

| Field | Type | Description |
| --- | --- | --- |
| `file` | file | Import file |
| `type` | string | Data type |

---

## Error Codes

| Code | Description |
| --- | --- |
| 400 | Bad Request - Invalid parameters |
| 401 | Unauthorized - Invalid or missing token |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Resource doesn't exist |
| 422 | Unprocessable Entity - Validation failed |
| 429 | Too Many Requests - Rate limit exceeded |
| 500 | Internal Server Error |

### Error Response Format

```json
{
    "message": "Error description",
    "errors": {
        "field_name": ["Validation error message"]
    }
}
```text

---

## Rate Limiting

API requests are rate limited based on user tier:

| Tier | Requests/Minute |
| --- | --- |
| Public | 60 |
| Authenticated | 120 |
| Premium | 300 |
| Admin | Unlimited |

Rate limit headers:

```
X-RateLimit-Limit: 120
X-RateLimit-Remaining: 115
X-RateLimit-Reset: 1706000000
```text

---

Last updated: February 2026
