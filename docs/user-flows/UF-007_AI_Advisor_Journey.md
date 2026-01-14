# UF-007: AI Advisor Journey

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-006], [SPEC-006], [FLOW-006], [SEQ-009], [SEQ-015], [WF-012]

---

## Flow Diagram
```mermaid
flowchart TD
    Start([Ask question]) --> Compose[Compose message]
    Compose --> Send[Send to AI]
    Send --> Route{Route model}
    Route -->|Ollama| Local
    Route -->|Bedrock| Cloud
    Local --> Generate[Generate answer]
    Cloud --> Generate
    Generate --> Score[Compute confidence + cost]
    Score --> Deliver[Return answer]
    Deliver --> UserView[User views with markdown]
    UserView --> Actions{Follow-up?}
    Actions -->|Helpful| FeedbackPos[Mark helpful]
    Actions -->|Regenerate| ReAsk[Regenerate]
    Actions -->|Follow-up| NewQuestion[New prompt]
    FeedbackPos --> End
    ReAsk --> Send
    NewQuestion --> Compose
```

## Notes
- Context assembled from run data before routing.  
- Budget meter and model badge shown with response.  
- Conversation stored; reconnect replays recent messages.
