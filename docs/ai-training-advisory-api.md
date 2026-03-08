# AI Training Advisory System - API Documentation

## Overview

The AI Training Advisory System provides RESTful API endpoints for generating intelligent recommendations during Umamusume Pretty Derby career runs. All endpoints support both Local Mode (browser storage) and Account Mode (database) operations.

**Base URL**: `/api/advisory`

**Authentication**: Required for Account Mode operations

**Content-Type**: `application/json`

---

## Endpoints

### 1. Training Recommendations

Generate turn-by-turn training facility recommendations based on character state.

**Endpoint**: `POST /api/advisory/training/recommendations`

**Request Body**:

```json
{
  "career_run_id": "uuid-or-id",
  "storage_mode": "local",
  "turn_number": 15,
  "phase": "classic_year",
  "stats": {
    "speed": 450,
    "stamina": 380,
    "power": 420,
    "guts": 350,
    "wisdom": 400
  },
  "sp_available": 180,
  "energy": 75,
  "mood": "good",
  "acquired_skills": [1, 5, 12],
  "skill_hints": [
    {"skill_id": 23, "level": 3},
    {"skill_id": 45, "level": 2}
  ],
  "support_deck": {
    "cards": [
      {"id": 1, "bond": 85, "facility": "speed"},
      {"id": 2, "bond": 72, "facility": "stamina"}
    ]
  },
  "facility_levels": {
    "speed": 3,
    "stamina": 2,
    "power": 3,
    "guts": 2,
    "wisdom": 4
  },
  "upcoming_races": [
    {"id": 15, "distance": "medium", "turn": 18}
  ]
}
```text

**Response** (200 OK):

```json
{
  "recommendations": [
    {
      "type": "training_facility",
      "priority": "high",
      "action": "Speed Training",
      "reasoning": "3 support cards present (Friendship Training available), facility at Level 3, aligns with upcoming Medium race requirements",
      "expected_outcomes": {
        "speed_gain": "+45-55",
        "bond_increases": ["+7", "+7", "+7"],
        "skill_hints": ["Possible Level 2 hint for Swinging Maestro"]
      },
      "risks": ["5% failure rate due to energy level"],
      "confidence_score": 0.92
    }
  ],
  "critical_alerts": [],
  "response_time_ms": 1850,
  "ai_provider": "ollama"
}
```

**Error Responses**:

- `400 Bad Request`: Invalid request data
- `401 Unauthorized`: Authentication required (Account Mode)
- `422 Unprocessable Entity`: Validation errors
- `500 Internal Server Error`: AI service unavailable (falls back to rule-based)

---

### 2. Skill Purchase Advice

Get intelligent skill purchase recommendations based on SP budget and character build.

**Endpoint**: `POST /api/advisory/skills/advice`

**Request Body**:

```json
{
  "character_id": "uuid-or-id",
  "storage_mode": "local",
  "sp_available": 220,
  "acquired_skills": [1, 5, 12],
  "available_skills": [
    {
      "id": 23,
      "name": "Swinging Maestro",
      "tier": "gold",
      "base_cost": 180,
      "hint_level": 3,
      "category": "stamina_recovery"
    }
  ]
}
```text

**Response** (200 OK):

```json
{
  "recommendations": [
    {
      "skill_id": 23,
      "priority": "high",
      "action": "Purchase Swinging Maestro",
      "reasoning": "Gold stamina recovery skill with Level 3 hint (30% discount). Cost: 126 SP.",
      "sp_cost": 126,
      "sp_remaining": 94,
      "expected_impact": "Enables Medium/Long distance races with lower stamina investment"
    }
  ],
  "sp_budget_analysis": {
    "current": 220,
    "recommended_spend": 126,
    "remaining": 94,
    "projected_total": "300-350 by career end"
  }
}
```

---

### 3. Race Strategy

Generate pre-race strategy recommendations based on character stats and race requirements.

**Endpoint**: `POST /api/advisory/race/strategy`

**Request Body**:

```json
{
  "character_id": "uuid-or-id",
  "race_id": 15,
  "stats": {
    "speed": 850,
    "stamina": 650,
    "power": 720,
    "guts": 580,
    "wisdom": 690
  },
  "skills": [1, 5, 12, 23],
  "aptitudes": {
    "distance_medium": "A",
    "surface_turf": "B",
    "style_escape": "A"
  }
}
```text

**Response** (200 OK):

```json
{
  "strategy": {
    "recommended_style": "escape",
    "reasoning": "A-grade Escape aptitude, sufficient stamina (650 vs 600 requirement)",
    "win_probability": 0.78,
    "readiness_assessment": {
      "stamina": "sufficient",
      "speed": "excellent",
      "power": "good",
      "overall": "ready"
    },
    "risks": ["B-grade turf aptitude may reduce effectiveness by 5-10%"],
    "preparation_checklist": [
      "✓ Stamina requirement met",
      "✓ Speed above 800",
      "✓ Recovery skills equipped"
    ]
  }
}
```

---

### 4. Critical Situation Detection

Detect critical situations requiring immediate attention.

**Endpoint**: `POST /api/advisory/critical/detect`

**Request Body**:

```json
{
  "career_run_id": "uuid-or-id",
  "turn_number": 35,
  "context": {
    "stats": {"speed": 450, "stamina": 320, "power": 400, "guts": 350, "wisdom": 380},
    "energy": 35,
    "upcoming_races": [{"distance": "medium", "turn": 38}],
    "support_bonds": [65, 70, 58, 75, 68, 72]
  }
}
```text

**Response** (200 OK):

```json
{
  "alerts": [
    {
      "type": "stamina_crisis",
      "priority": "critical",
      "message": "Stamina critically low for upcoming Medium race (320 vs 600 required)",
      "action_items": [
        "Focus next 3 turns on Stamina training",
        "Prioritize Friendship Training at Stamina facility"
      ],
      "turns_until_critical": 3
    }
  ]
}
```

---

### 5. Record Training Outcome

Record actual training outcome for prediction accuracy tracking.

**Endpoint**: `POST /api/advisory/training/outcome`

**Request Body**:

```json
{
  "career_run_id": "uuid-or-id",
  "turn_number": 15,
  "recommendation_id": "rec-123",
  "actual_outcome": {
    "speed_gain": 48,
    "stamina_gain": 0,
    "power_gain": 5,
    "guts_gain": 0,
    "wisdom_gain": 3,
    "bond_increases": [7, 7, 7],
    "skill_hints_gained": [{"skill_id": 23, "level": 2}],
    "failure_occurred": false
  }
}
```text

**Response** (200 OK):

```json
{
  "accuracy_recorded": true,
  "accuracy_score": 0.96,
  "prediction_vs_actual": {
    "speed": {"predicted": "45-55", "actual": 48, "accurate": true}
  }
}
```

---

### 6. Record Race Outcome

Record actual race outcome for prediction accuracy tracking.

**Endpoint**: `POST /api/advisory/race/outcome`

**Request Body**:

```json
{
  "career_run_id": "uuid-or-id",
  "race_id": 15,
  "strategy_id": "strat-456",
  "actual_outcome": {
    "finish_position": 2,
    "finish_time": 125.5,
    "won_race": false,
    "margin": -0.3
  }
}
```text

**Response** (200 OK):

```json
{
  "accuracy_recorded": true,
  "win_probability_accuracy": 0.78,
  "placement_accuracy": "within_1_position"
}
```

---

## Rate Limiting

- **Authenticated Users**: 60 requests per minute
- **Anonymous Users** (Local Mode): 30 requests per minute

---

## Error Codes

| Code | Description                                    |
| ---- | ---------------------------------------------- |
| 400  | Bad Request - Invalid request format           |
| 401  | Unauthorized - Authentication required         |
| 403  | Forbidden - Insufficient permissions           |
| 422  | Unprocessable Entity - Validation failed       |
| 429  | Too Many Requests - Rate limit exceeded        |
| 500  | Internal Server Error - AI service unavailable |
| 503  | Service Unavailable - System maintenance       |

---

## Response Time Targets

- **Local AI (Ollama)**: ≤2 seconds (p95)
- **Cloud AI (AWS Bedrock)**: ≤5 seconds (p95)
- **Rule-Based Fallback**: ≤500ms (p95)

---

## Caching

The API implements intelligent caching:

- **Skill Catalog**: 24-hour TTL
- **Race Requirements**: 1-hour TTL
- **Support Card Meta**: 24-hour TTL
- **Recommendations**: Turn-specific, invalidated on state change

---

## Versioning

Current API Version: **v1**

Version is specified in the URL: `/api/v1/advisory/...`

---

## Support

For API support, please refer to:

- [Developer Guide](./ai-training-advisory-developer-guide.md)
- [Service Documentation](./ai-training-advisory-services.md)
