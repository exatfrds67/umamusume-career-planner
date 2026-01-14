# WF-012: AI Advisor Interface

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-006], [SPEC-006], [FLOW-006], [SEQ-009], [SEQ-015]

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
