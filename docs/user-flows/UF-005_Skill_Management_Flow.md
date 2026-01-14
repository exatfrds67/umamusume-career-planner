# UF-005: Skill Management Flow

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-004], [SPEC-004], [FLOW-004], [SEQ-006], [SEQ-007]

---

## Flow Diagram
```mermaid
flowchart TD
    Start([Need skill upgrade]) --> OpenShop[Open skill shop]
    OpenShop --> Filter[Filter/search skills]
    Filter --> SelectSkill[Select skill]
    SelectSkill --> CheckSP{Enough SP?}
    CheckSP -->|No| SuggestTraining[Suggest SP farming]
    SuggestTraining --> ExitShop
    CheckSP -->|Yes| CheckPrereq{Prerequisites met?}
    CheckPrereq -->|No| ShowRequirements[Show unmet requirements]
    ShowRequirements --> ExitShop
    CheckPrereq -->|Yes| BuySkill[Purchase skill]
    BuySkill --> UpdateState[Deduct SP, add skill]
    UpdateState --> Loadout[Open loadout manager]
    Loadout --> AddToSlots[Place skill into loadout]
    AddToSlots --> Optimize{Run auto-optimize?}
    Optimize -->|Yes| AutoOpt[Apply best combination]
    AutoOpt --> SaveLoadout
    Optimize -->|No| SaveLoadout[Save loadout]
    SaveLoadout --> Confirm[Confirm saved]
    Confirm --> Exit[Return to dashboard]
```

## Notes
- Evolution-capable skills mark prerequisites; unmet shows checklist.  
- Auto-optimize respects SP budget and distance/style coverage.  
- SP suggestions include training choices or race rewards.
