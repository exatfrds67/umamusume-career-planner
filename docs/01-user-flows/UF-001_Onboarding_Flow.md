# UF-001: Onboarding Flow

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-001], [SPEC-001]
**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Onboarding Requirements)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Onboarding Flow)

**Related Artifacts**:

- PRD: [PRD-001](../prds/PRD-001_Character_Management.md)
- SPEC: [SPEC-001](../specs/SPEC-001_Character_Management_Technical.md)
- Wireframes: [WF-001](../wireframes/WF-001_Dashboard_Overview.md)

---

## Flow Diagram

```mermaid
flowchart TD
    Start([New User Lands]) --> Signup{Has account?}
    Signup -->|No| Create[Create account (email/password or SSO)]
    Signup -->|Yes| Login[Login]
    Create --> VerifyEmail[Verify email]
    VerifyEmail --> Login
    Login --> IntroTour[Optional intro tour]
    IntroTour --> GoalPrompt[Prompt to set goals]
    GoalPrompt -->|Skip| SkipGoals[Use defaults]
    GoalPrompt -->|Set goals| SaveGoals[Save goals]
    SkipGoals --> CreateRunPrompt
    SaveGoals --> CreateRunPrompt[Prompt to create first run]
    CreateRunPrompt --> LaunchWizard[Open character creation wizard]
    LaunchWizard --> CompleteWizard[Finish run setup]
    CompleteWizard --> Dashboard[Arrive at dashboard]
    Dashboard --> CTAHints[Contextual hints on next actions]
```

## Notes

- SSO optional but recommended; email verification required.  
- Tour can be skipped; hints appear contextually later.  
- Goal prompt can be deferred; defaults align to general progression.
