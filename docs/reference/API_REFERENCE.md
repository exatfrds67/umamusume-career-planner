# API Reference

Complete API documentation for the Umamusume Career Planner.

## Base URL

```
https://api.example.com/api/v1
```

## Authentication

All API endpoints require authentication using Laravel Sanctum tokens.

### Headers

```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Obtaining a Token

```http
POST /api/login
```

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
```

---

## Characters

### List Characters

```http
GET /api/v1/characters
```

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
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
```

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
```

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
```

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
```

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
```

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `character_id` | integer | Filter by character |
| `status` | string | Filter by status (active, completed, abandoned) |

### Get Career

```http
GET /api/v1/careers/{id}
```

### Create Career

```http
POST /api/v1/careers
```

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
```

**Request Body:**

```json
{
    "status": "completed"
}
```

### Delete Career

```http
DELETE /api/v1/careers/{id}
```

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
```

### Career Report

```http
GET /api/v1/careers/{id}/report
```

### Compare Careers

```http
POST /api/v1/careers/compare
```

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
```

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
```

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
```

---

## Races

### List Races

```http
GET /api/v1/careers/{careerId}/races
```

### Available Races

```http
GET /api/v1/careers/{careerId}/available-races
```

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
```

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
```

---

## Skills

### List Skills

```http
GET /api/v1/skills
```

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `skill_type` | string | Filter by type (normal, rare, unique, inherited) |
| `search` | string | Search by name |
| `min_sp_cost` | integer | Minimum SP cost |
| `max_sp_cost` | integer | Maximum SP cost |

### Get Skill

```http
GET /api/v1/skills/{id}
```

### Skill Hints

```http
GET /api/v1/skills/{id}/hints
```

### Character Skills

```http
GET /api/v1/characters/{characterId}/skills
```

### Acquire Skill

```http
POST /api/v1/characters/{characterId}/skills
```

**Request Body:**

```json
{
    "skill_id": 1
}
```

### Remove Skill

```http
DELETE /api/v1/characters/{characterId}/skills/{skillId}
```

### Skill Recommendations

```http
GET /api/v1/skills/analysis/recommendations
```

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `character_id` | integer | Character to analyze |

---

## Support Cards

### List Support Cards

```http
GET /api/v1/support-cards
```

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `card_type` | string | Filter by type (speed, stamina, power, guts, wit, friend) |
| `rarity` | string | Filter by rarity (R, SR, SSR) |
| `meta_tier` | string | Filter by tier (S+, S, A, B, C) |
| `search` | string | Search by name |

### Get Support Card

```http
GET /api/v1/support-cards/{id}
```

### Meta Ranking

```http
GET /api/v1/support-cards/meta-ranking
```

### Card Synergies

```http
GET /api/v1/support-cards/{id}/synergies
```

---

## Character Deck

### Get Deck

```http
GET /api/v1/characters/{characterId}/deck
```

### Add Card to Deck

```http
POST /api/v1/characters/{characterId}/deck
```

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
```

### Deck Analysis

```http
GET /api/v1/characters/{characterId}/deck/analysis
```

### Deck Optimization

```http
GET /api/v1/characters/{characterId}/deck/optimization
```

---

## AI Advisor

### Create Conversation

```http
POST /api/v1/ai/conversations
```

### Send Message

```http
POST /api/v1/ai/conversations/{conversationId}/messages
```

**Request Body:**

```json
{
    "message": "What training should I do next?"
}
```

### Get Conversation History

```http
GET /api/v1/ai/conversations/{conversationId}
```

---

## OCR

### Upload Image

```http
POST /api/v1/ocr/upload
```

**Request Body:** `multipart/form-data`

| Field | Type | Description |
|-------|------|-------------|
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
```

### Get Extraction Result

```http
GET /api/v1/ocr/extractions/{extractionId}
```

---

## Data Export/Import

### Export Data

```http
GET /api/v1/export
```

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `format` | string | Export format (json, csv) |
| `type` | string | Data type (characters, careers, all) |

### Import Data

```http
POST /api/v1/import
```

**Request Body:** `multipart/form-data`

| Field | Type | Description |
|-------|------|-------------|
| `file` | file | Import file |
| `type` | string | Data type |

---

## Error Codes

| Code | Description |
|------|-------------|
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
```

---

## Rate Limiting

API requests are rate limited based on user tier:

| Tier | Requests/Minute |
|------|-----------------|
| Public | 60 |
| Authenticated | 120 |
| Premium | 300 |
| Admin | Unlimited |

Rate limit headers:

```
X-RateLimit-Limit: 120
X-RateLimit-Remaining: 115
X-RateLimit-Reset: 1706000000
```

---

*Last updated: January 2026*
