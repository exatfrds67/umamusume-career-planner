# WF-012: AI Advisor Interface

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-006], [SPEC-006], [FLOW-006], [SEQ-009], [SEQ-015]

**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (AI Advisory Requirements)
- `.kiro/specs/umamusume-career-planner-main/design.md` (AI Advisor UI)

**Related Artifacts**:

- PRD: [PRD-006](../prds/PRD-006_AI_Advisory.md)
- SPEC: [SPEC-006](../specs/SPEC-006_AI_Advisory_Technical.md)
- Flow: [FLOW-006](../flows/FLOW-006_AI_Advisory_System.md)
- Tech Flow: [TECH-FLOW-006](../tech-flow/TECH-FLOW-006_AI_Advisory_Flow.md)
- Sequences: [SEQ-006](../sequences/SEQ-006_AI_Advice_Generation.md)
- User Flows: [UF-007](../user-flows/UF-007_AI_Advisor_Journey.md)
- MCP Config: [MCP_SERVER_CONFIGURATION_REFERENCE](../MCP_SERVER_CONFIGURATION_REFERENCE.md)

---

## Layout (Desktop)

```
+----------------------------------------------------------------------------------+
| Header: AI Advisor | Model badge (Ollama/Bedrock) | Budget meter                 |
+----------------------------------------------------------------------------------+
| Chat Pane (2 cols)                                                                 |
| Left: Conversation                                                                |
| - Message list (user/assistant bubbles)                                           |
| - Each assistant message shows confidence, model, cost                            |
| - Quick actions: Regenerate, Mark helpful                                         |
|                                                                                    |
| Right: Context & Actions                                                           |
| - Run context summary (stats, mood, energy)                                       |
| - Upcoming race card                                                              |
| - Deck summary, key skills                                                        |
| - Buttons: "Suggest training", "Prep next race", "Optimize skills"              |
+----------------------------------------------------------------------------------+
| Input Bar: multiline textbox, attachments (optional), Send button                 |
+----------------------------------------------------------------------------------+
```

## Layout (Mobile)

- Single column; context collapses to drawer above keyboard.  
- Input bar sticky; cost/confidence shown inline per message.

## Notes

- Streaming indicator while generating; disable send during request.  
- Budget meter shows monthly usage; warn on thresholds.  
- Model badge reflects routed provider (Ollama vs Bedrock) per query complexity.
