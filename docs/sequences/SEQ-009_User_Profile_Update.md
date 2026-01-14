# SEQ-009: User Profile Update

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-001], [SPEC-009]

---

## Sequence Overview
- User updates profile settings; system validates, persists, and refreshes sessions where needed.

## Sequence Diagram

```mermaid
sequenceDiagram
    participant U as User
    participant UI as Frontend
    participant API as Backend
    participant DB as Database
    participant AUTH as AuthService

    U->>UI: Edit profile
    UI->>API: PUT /api/profile {fields}
    API->>API: Validate payload (email/timezone/prefs)
    API->>DB: Update user + preferences
    DB-->>API: Saved
    API->>AUTH: RefreshTokensIfNeeded
    AUTH-->>API: Tokens (optional)
    API-->>UI: 200 Updated profile
    UI-->>U: Show success + refreshed data
```
