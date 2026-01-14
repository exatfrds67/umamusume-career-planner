# FLOW-006: AI Advisory System Flow

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0
**Date**: January 14, 2026
**Related Documents**: [PRD-006], [SPEC-006]

---

## 1. AI Query Routing & Processing Flow

```mermaid
flowchart TD
    Start([User Submits Query]) --> Analyze[Analyze intent/complexity]
    Analyze --> CheckOllama{Ollama available?}
    CheckOllama -->|Yes & Simple| RouteOllama
    CheckOllama -->|No or Complex| RouteCloud
    RouteOllama --> BuildPrompt
    RouteCloud --> BuildPrompt[Build prompt + context]
    BuildPrompt --> CallModel[Call model]
    CallModel --> Receive[Receive response]
    Receive --> ScoreConfidence[Score confidence]
    ScoreConfidence --> ReturnUser[Return to user]
```

---

## 2. Context Management Flow

```mermaid
flowchart TD
    Start([Manage Context]) --> CheckConversation{Existing conversation?}
    CheckConversation -->|Yes| LoadHistory
    CheckConversation -->|No| InitSession
    LoadHistory --> LoadRunData[Load run stats/goals/history/deck/skills]
    InitSession --> LoadRunData
    LoadRunData --> TrimTokens[Trim to token budget]
    TrimTokens --> StoreContext[Store in context service]
```

---

## 3. Recommendation Generation Flow

```mermaid
flowchart TD
    Start([Generate Advice]) --> IdentifyDomain{Domain}
    IdentifyDomain -->|Training| TrainingAdvice
    IdentifyDomain -->|Race| RaceAdvice
    IdentifyDomain -->|Skill| SkillAdvice
    IdentifyDomain -->|General| GeneralAdvice
    TrainingAdvice --> PredictOutcome[Predict outcomes]
    RaceAdvice --> ComputeWinProb
    SkillAdvice --> OptimizeSP
    GeneralAdvice --> Summarize
    PredictOutcome --> RankOptions
    ComputeWinProb --> RankOptions
    OptimizeSP --> RankOptions
    RankOptions --> BuildAnswer[Build actionable steps]
```

---

## 4. Cost Tracking & Budget Management Flow

```mermaid
flowchart TD
    Start([Track Cost]) --> CaptureTokens[Capture input/output tokens]
    CaptureTokens --> ApplyPricing[Apply provider pricing]
    ApplyPricing --> DeductBudget[Deduct from monthly budget]
    DeductBudget --> Status{Budget state}
    Status -->|Healthy| Continue
    Status -->|Warning| WarnUser
    Status -->|Critical| BlockHighCost
```

---

## 5. Confidence Scoring Flow

```mermaid
flowchart TD
    Start([Score]) --> Base50[Base 50%]
    Base50 --> DataQuality[+ data quality 0-10]
    DataQuality --> Recency[+ recency 0-10]
    Recency --> ModelQuality[+ model quality 0-15]
    ModelQuality --> ResponseQuality[+ response quality 0-10]
    ResponseQuality --> Specificity[+ specificity 0-15]
    Specificity --> Consistency[+ consistency 0-10]
    Consistency --> Clamp[Clamp 0-100]
    Clamp --> Classify[Classify Very Low/Low/Med/High/Very High]
```

---

## 6. Conversational Interface Flow

```mermaid
flowchart TD
    Start([Open Chat]) --> LoadHistory
    LoadHistory --> UserInput[Capture input]
    UserInput --> ValidateInput{Valid?}
    ValidateInput -->|No| ShowError
    ValidateInput -->|Yes| SendRequest
    SendRequest --> ShowTyping[Show typing indicator]
    SendRequest --> ReceiveResponse
    ReceiveResponse --> Render[Render markdown + metadata]
    Render --> FollowUps[Offer follow-up actions]
```

---

## 7. MCP Server Integration Flow

```mermaid
flowchart TD
    Start([Use MCP]) --> ConnectServers[Connect: strands, agentcore, context7, memory, fetch]
    ConnectServers --> HealthCheck[Health checks]
    HealthCheck --> ToolCalls[Make tool calls as needed]
    ToolCalls --> MergeResults[Merge results]
    MergeResults --> Cleanup[Cleanup temp context]
```
