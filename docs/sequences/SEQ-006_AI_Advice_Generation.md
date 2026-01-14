# SEQ-006: AI Advice Generation

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-001], [SPEC-006], [FLOW-006]

---

## Sequence Overview
- User asks for advice; system aggregates context, calls AI, surfaces recommended action and rationale.

## Sequence Diagram

```mermaid
sequenceDiagram
    participant U as User
    participant UI as Frontend
    participant API as Backend
    participant CTX as ContextBuilder
    participant AI as AdvisoryModel
    participant DB as Database

    U->>UI: Request advice
    UI->>API: POST /api/runs/{id}/advice
    API->>DB: Load run state (stats, skills, mood, deck)
    DB-->>API: State

    API->>CTX: BuildPrompt(state)
    CTX-->>API: Prompt payload
    API->>AI: GenerateAdvice(prompt)
    AI-->>API: {action, confidence, rationale, risks}

    API-->>UI: 200 Advice response
    UI-->>U: Show recommended action + reasoning
```
