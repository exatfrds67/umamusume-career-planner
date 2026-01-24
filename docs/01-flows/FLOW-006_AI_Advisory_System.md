# FLOW-006: AI Advisory System Flow

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.1.0
**Date**: January 24, 2026
**Project**: UmamusumeCareerPlanner
**Author**: Development Team
**Status**: Current - Aligned with codebase v2.0.0

---

## 1. AI Query Routing & Hybrid Processing Flow

This flow illustrates the decision-making process within `AIAdvisoryService` and `AIRouterService` to select the appropriate AI provider (Local Ollama vs. Cloud AWS Bedrock) based on query complexity, availability, and cost constraints.

```mermaid
flowchart TD
    Start([User Submits Query]) --> BuildContext[Build Context]
    BuildContext --> AnalyzeComplexity[Analyze Complexity]
    
    AnalyzeComplexity --> CheckLocal{Ollama Available?}
    
    CheckLocal -->|Yes| EvaluateLocal[Evaluate Local Suitability]
    CheckLocal -->|No| ForceCloud[Force Cloud Provider]
    
    EvaluateLocal -->|Simple Query| RouteLocal[Route to Ollama (llama3.2)]
    EvaluateLocal -->|Complex Strategy| RouteCloud[Route to AWS Bedrock]
    
    ForceCloud --> RouteCloud
    
    RouteCloud --> CheckBudget{Check Budget}
    CheckBudget -->|Within Limit| SelectModel[Select Claude 3.5 Sonnet]
    CheckBudget -->|Exceeded| ErrorQuota[Return Quota Error]
    
    RouteLocal --> ExecuteRequest[Execute Inference]
    SelectModel --> ExecuteRequest
    
    ExecuteRequest --> Success{Success?}
    
    Success -->|Yes| ProcessResponse[Process Response]
    Success -->|No| CheckFallback{Can Fallback?}
    
    CheckFallback -->|Yes| ForceCloud
    CheckFallback -->|No| ReturnError[Return Service Unavailable]
    
    ProcessResponse --> ReturnUser[Return Advice]
```

---

## 2. Neuron Agent Execution Flow

This flow details how specific **Neuron AI Agents** (Training, Race, Skill, Career) orchestrate tool usage to generate grounded recommendations.

```mermaid
flowchart TD
    Start([Agent Triggered]) --> LoadAgent[Load Specific Agent Class]
    
    LoadAgent -->|Training| TrainingAgent
    LoadAgent -->|Race| RaceAgent
    LoadAgent -->|Skill| SkillAgent
    LoadAgent -->|Career| CareerAgent
    
    TrainingAgent --> IdentifyTools[Identify Required Tools]
    RaceAgent --> IdentifyTools
    SkillAgent --> IdentifyTools
    CareerAgent --> IdentifyTools
    
    IdentifyTools --> ToolLoop{Tool Execution Loop}
    
    ToolLoop -->|Need Data| CallTool[Call Internal Tool]
    ToolLoop -->|Need Memory| CallMCP[Call MCP Client]
    
    CallTool -->|Get Stats| StatsTool[CharacterStatsTool]
    CallTool -->|Get Preds| PredTool[TrainingPredictionTool]
    CallTool -->|Get Calendar| RaceTool[RaceAnalysisTool]
    
    CallMCP -->|Store/Read| MemoryMCP[Memory Server]
    CallMCP -->|Retrieve| FetchMCP[Fetch Server]
    
    StatsTool --> Aggregator[Context Aggregator]
    PredTool --> Aggregator
    RaceTool --> Aggregator
    MemoryMCP --> Aggregator
    
    Aggregator --> ToolLoop
    
    ToolLoop -->|Context Complete| GeneratePrompt[Generate System Prompt]
    GeneratePrompt --> CallLLM[Call LLM Provider]
    CallLLM --> Output[Return Structured Advice]
```

---

## 3. Context Management Flow

Manages conversation history and game state context using the database and Memory MCP server.

```mermaid
flowchart TD
    Start([Init Request]) --> LoadSession[Load Session Context]
    
    LoadSession --> FetchDB[Fetch ucp_ai_conversations]
    LoadSession --> FetchMemory[Fetch Memory MCP Context]
    
    FetchDB --> MergeContext[Merge Context Sources]
    FetchMemory --> MergeContext
    
    MergeContext --> LoadGameState[Load Current Game State]
    LoadGameState --> Prune[Prune to Token Limit]
    
    Prune --> FinalContext[Final Context Window]
    FinalContext --> AgentProcessing[Pass to Agent]
    
    AgentProcessing --> NewResponse[Generate Response]
    
    NewResponse --> UpdateDB[Append to Database]
    NewResponse --> UpdateMemory[Update Memory MCP]
    
    UpdateDB --> End([End Flow])
```

---

## 4. Recommendation Generation Flow

How the system generates domain-specific advice based on the user's current situation.

```mermaid
flowchart TD
    Start([Generate Recommendation]) --> DetectDomain{Detect Domain}
    
    DetectDomain -->|Training| TrainFlow
    DetectDomain -->|Race| RaceFlow
    DetectDomain -->|Skill| SkillFlow
    
    subgraph TrainFlow [Training Analysis]
        GetPreds[Get Predictions] --> CalcEff[Calc Efficiency]
        CalcEff --> CheckRisk[Check Failure Risk]
        CheckRisk --> RankTrain[Rank Facilities]
    end
    
    subgraph RaceFlow [Race Analysis]
        CheckCal[Check Calendar] --> CalcWin[Calc Win Prob]
        CalcWin --> CheckReqs[Check Stat Reqs]
        CheckReqs --> RecStrat[Recommend Strategy]
    end
    
    subgraph SkillFlow [Skill Planning]
        GetBudget[Check SP Budget] --> FilterSkills[Filter Available]
        FilterSkills --> OptBuild[Knapsack Optimization]
        OptBuild --> RecSkills[Suggest Acquisitions]
    end
    
    RankTrain --> FormatOutput[Format Output]
    RecStrat --> FormatOutput
    RecSkills --> FormatOutput
    
    FormatOutput --> AddReasoning[Add AI Reasoning]
    AddReasoning --> ReturnResult[Return Recommendation]
```

---

## 5. Cost Tracking & Budget Management Flow

Tracks token usage for cloud providers (AWS Bedrock) to prevent overage.

```mermaid
flowchart TD
    Start([AI Response Received]) --> ExtractUsage[Extract Token Usage]
    
    ExtractUsage --> IdentifyModel{Identify Model}
    
    IdentifyModel -->|Claude 3.5 Sonnet| CalcSonnet[Apply Sonnet Rates]
    IdentifyModel -->|Claude 3 Haiku| CalcHaiku[Apply Haiku Rates]
    IdentifyModel -->|Ollama| CalcLocal[Cost = 0]
    
    CalcSonnet --> TotalCost[Calculate Total Request Cost]
    CalcHaiku --> TotalCost
    CalcLocal --> TotalCost
    
    TotalCost --> LogTransaction[Log to ucp_ai_conversations]
    LogTransaction --> Aggregator[Update User Daily Total]
    
    Aggregator --> CheckLimit{Check Daily Limit}
    
    CheckLimit -->|Approaching| SendWarning[Send Budget Warning]
    CheckLimit -->|Exceeded| BlockCloud[Disable Cloud Provider]
    CheckLimit -->|OK| Continue
```

---

## 6. MCP Server Integration Flow

Details the interaction between the Laravel backend and external Model Context Protocol servers.

```mermaid
flowchart TD
    Start([Tool Request]) --> MCPClient[MCP Client Service]
    
    MCPClient --> ResolveServer{Resolve Server}
    
    ResolveServer -->|Memory| MemoryServer[Memory MCP]
    ResolveServer -->|Filesystem| FileServer[Filesystem MCP]
    ResolveServer -->|Fetch| FetchServer[Fetch MCP]
    
    MemoryServer -->|Read/Write| GraphStore[Knowledge Graph]
    FileServer -->|Read| LocalFiles[Local Configs/Logs]
    FetchServer -->|GET/POST| ExternalWeb[External Web Resources]
    
    GraphStore --> ReturnResult[Return Tool Result]
    LocalFiles --> ReturnResult
    ExternalWeb --> ReturnResult
    
    ReturnResult --> ValidateResult[Validate Output]
    ValidateResult --> ReturnAgent[Return to Agent]
```

---

## 7. Conversational Interface Flow

The user interaction loop within the Livewire `AdvisorChat` component.

```mermaid
flowchart TD
    Start([User Types Message]) --> Submit[Submit Query]
    
    Submit --> OptimisticUI[Show User Message (Optimistic)]
    OptimisticUI --> ShowTyping[Show 'Thinking...' Indicator]
    
    ShowTyping --> SendRequest[Send to AI Controller]
    
    SendRequest --> ProcessBackend[Process Backend (See Flow 1)]
    
    ProcessBackend --> ReceiveResp[Receive AI Response]
    
    ReceiveResp --> Stream{Stream Enabled?}
    
    Stream -->|Yes| StreamChunks[Stream Markdown Chunks]
    Stream -->|No| RenderFull[Render Full Response]
    
    StreamChunks --> UpdateUI[Update Chat Window]
    RenderFull --> UpdateUI
    
    UpdateUI --> RenderActions[Render Follow-up Actions]
    RenderActions --> SaveHistory[Persist State]
```

---

## Document Control

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 2.1.0 | 2026-01-24 | Development Team | Updated to align with v2.0.0 architecture: Hybrid AI, Neuron Agents, and MCP integration |
| 1.0.0 | 2026-01-14 | Development Team | Initial flow definitions |

---

## Related Documents

- [PRD-006: AI Advisory](../prds/PRD-006_AI_Advisory.md)
- [SPEC-006: AI Advisory Technical](../specs/SPEC-006_AI_Advisory_Technical.md)
- [008_SIS: Software Integration Specs](../008_SIS_Software_Integration_Specifications.md)
