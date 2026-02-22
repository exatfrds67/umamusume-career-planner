# PRD-006: AI Advisory System

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.2.0  
**Date**: January 28, 2026  
**Project**: UmamusumeCareerPlanner  
**Author**: Development Team  
**Status**: Current - Aligned with codebase v2.2.0  
**Related Documents**: [SRS-FR-07], [SDS-4.6], [DBD-4.5], [SPEC-006]

**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirements)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Design)
- `.kiro/specs/umamusume-career-planner-main/tasks.md` (Implementation Tasks)

**Related Artifacts**:

- SPEC: [SPEC-006](../specs/SPEC-006_AI_Advisory_Technical.md)
- Flow: [FLOW-006](../flows/FLOW-006_AI_Advisory_System.md)
- Wireframes: [WF-012](../wireframes/WF-012_AI_Advisor_Interface.md)
- Sequences: [SEQ-006](../sequences/SEQ-006_AI_Advice_Generation.md)
- User Flows: [UF-007](../user-flows/UF-007_AI_Advisor_Journey.md)

---

## Table of Contents

- [PRD-006: AI Advisory System](#prd-006-ai-advisory-system)
  - [1. Executive Summary](#1-executive-summary)
  - [2. Product Overview](#2-product-overview)
  - [3. User Stories](#3-user-stories)
  - [4. Functional Requirements](#4-functional-requirements)
  - [5. User Interface Requirements](#5-user-interface-requirements)
  - [6. Data and Integration](#6-data-and-integration)
  - [7. Non-Functional Requirements](#7-non-functional-requirements)
  - [8. Success Metrics](#8-success-metrics)
  - [9. Release Plan](#9-release-plan)
  - [10. Open Questions and Assumptions](#10-open-questions-and-assumptions)
  - [Changelog](#changelog)

---

## 1. Executive Summary

### 1.1 Purpose

Empower players with intelligent, context-aware decision support through a **Hybrid AI Advisory System**. This system utilizes local LLMs for speed/privacy and cloud LLMs for complex reasoning, orchestrated by **Neuron AI Agents**.

### 1.2 Problem Statement

Game mechanics in *Uma Musume* are opaque and highly mathematical. Players struggle to balance short-term turn efficiency with long-term build goals, often needing "coach-like" advice that generic wikis cannot provide.

### 1.3 Solution Overview

- **Hybrid Architecture**: **Ollama** (Llama 3.2) handles routine queries locally; **AWS Bedrock** (Claude 3.5/4.5) handles complex strategy.
- **Neuron Agents**: Specialized agents (`TrainingAdvisor`, `RaceStrategy`, `SkillAdvisor`) use tools to analyze game state.
- **MCP Integration**: Uses Model Context Protocol to persist conversation memory and access external tools securely.

---

## 2. Product Overview

### 2.1 Objectives

- Deliver advice that considers the *specific* context of the current run (Turn, Stats, Deck, Goals).
- Minimize operational costs by routing simple queries to local models.
- Provide transparent reasoning ("Chain of Thought") for every recommendation.

### 2.2 Scope (In)

- **Advice Domains**: Training optimization, Race preparation, Skill acquisition, Deck building.
- **Interaction Modes**:
  - *Reactive*: Contextual tips on dashboard.
  - *Interactive*: Chat interface for specific questions.
- **Agent Framework**: Implementation of Neuron AI agents with specific toolsets.
- **Context Management**: Persistence of conversation history via database and MCP Memory server.
- **Cost Control**: Token tracking and budget limits for cloud provider usage.

### 2.3 Scope (Out)

- **Autonomous Gameplay**: The AI suggests actions but cannot click buttons in the game itself.
- **Image Generation**: No generative art capabilities.

---

## 3. User Stories

| ID | Actor | Story | Acceptance Criteria |
| --- | --- | --- | --- |
| US-6.1 | Player | I want to ask "What should I train next?" and get an answer based on my current stats. | AI analyzes stats/goals and suggests specific facility. |
| US-6.2 | Player | I want to know why the AI recommends resting when I have 60% energy. | Response includes reasoning. |
| US-6.3 | Player | I want to use a local model to avoid data leaving my network. | System allows selecting "Local Only" or prioritizing Ollama. |
| US-6.4 | Player | I want the AI to remember that I'm building a "Betweener" strategy. | Context persists across the chat session via Memory MCP. |
| US-6.5 | Admin | I want to set a daily spending limit for AWS Bedrock. | System stops Cloud routing when budget is exceeded. |

---

## 4. Functional Requirements

### 4.1 Hybrid Routing Engine [FR-07.6]

- **Complexity Analyzer**: Heuristic analysis of user prompt to determine routing.
  - Low Complexity -> Ollama (Llama 3.2).
  - High Complexity -> AWS Bedrock (Claude 3.5 Sonnet).
- **Fallback Logic**: Automatically retry with Cloud if Local fails or times out.

### 4.2 Neuron Agent Orchestration [FR-07.1]

- **Training Agent**: Uses `GetTrainingPredictionsTool` to evaluate options.
- **Race Agent**: Uses `GetRaceCalendarTool` and `WinProbCalculator`.
- **Skill Agent**: Uses `SkillCatalogTool` and `SPBudgetTool`.
- **Orchestrator**: Single entry point (`AIAdvisoryService`) that dispatches to specific agents based on topic.

### 4.3 Game Mechanics Knowledge Base [FR-07.2]

The AI system must be trained on accurate game mechanics including:

**Skill Hint System**:

- 5 hint levels with 10%/10%/10%/5%/5% discounts (40% max)
- Additional sources: Fast Learner (+10%), Skill Sparks, Hint Books

**Aptitude System**:

- Grade scale: G → F → E → D → C → B → A → S (no SS)
- A-rank is baseline (0%); only S-rank provides positive bonuses

**Stat System**:

- Soft cap at 1200 with diminishing returns above
- Important breakpoints: 901, 1200, 1600
- Stamina 1200+ activates "Stamina Contest" buff

**Track Conditions**:

- Firm: No penalties
- Good: -50 Power
- Soft: -50/-100 Power, +2%/sec stamina drain
- Heavy: -50/-100 Power, -50 Speed, +2%/sec stamina drain

**Career Structure**:

- ~70-78 turns across 3 years
- Summer Training Camp: 4 turns, all facilities Level 5

### 4.4 Model Context Protocol (MCP) [FR-07.4]

- **Memory Server**: Store long-term user preferences and run strategy.
- **Filesystem Server**: (Internal) Access to static game data files for RAG.
- **Fetch Server**: Retrieve latest meta updates from web if configured.

### 4.5 Cost & Usage Tracking [FR-07.5]

- **Metrics**: Track input/output tokens per request.
- **Logging**: Persist usage to `ucp_ai_conversations` and `ucp_mcp_tool_usage`.
- **Quotas**: Soft and hard limits on daily Cloud spend per user.

---

## 5. User Interface Requirements

### 5.1 Advisor Chat Widget

- **Position**: Collapsible sidebar or floating action button.
- **State**: "Thinking..." indicator during inference.
- **Format**: Markdown support for bolding key terms and listing steps.
- **Context Badge**: Shows which model answered (e.g., "Local" or "Cloud").

### 5.2 Contextual Tips

- **Location**: Embedded in Training/Race screens.
- **Behavior**: Auto-generated short tips appearing near decision buttons.
- **Action**: "Explain" button to expand the tip into a full chat session.

### 5.3 Settings Panel

- **Provider Selection**: Dropdown (Auto / Local / Cloud).
- **Model Config**: Temperature, Max Tokens (Advanced).
- **Budget View**: Progress bar of daily API quota usage.

---

## 6. Data and Integration

### 6.1 Data Models

- **Conversation**: `user_id`, `context_type` (training/race), `messages` (JSON), `model_used`.
- **Recommendation**: `character_id`, `type`, `content`, `confidence`.
- **Tool Usage**: `tool_name`, `latency`, `success`.

### 6.2 Service Integration

- **Neuron Framework**: Core dependency for agent logic.
- **AWS SDK**: Integration for Bedrock Runtime.
- **Ollama API**: Local HTTP integration (`http://localhost:11434`).

### 6.3 Context Injection

- **Strategy**: RAG (Retrieval-Augmented Generation) using current Character JSON state + recent Training History + Goal definitions.

---

## 7. Non-Functional Requirements

- **Latency**:
  - Local: < 800ms for simple queries.
  - Cloud: < 3s for complex strategy.
- **Privacy**: Local requests must never leave the user's infrastructure. Cloud requests must strip PII.
- **Reliability**: Circuit breaker prevents cascading failures if AI providers are down.

---

## 8. Success Metrics

- **Quality**: > 80% "Helpful" ratings on AI responses.
- **Cost Efficiency**: > 60% of total queries handled by Local (Ollama) model.
- **Latency**: Average response time < 2s.

---

## 9. Release Plan

- **v2.0.0**:
  - Hybrid Router (Ollama/Bedrock).
  - Core Agents (Training, Race).
  - Basic Context Injection.
- **v2.1.0**:
  - Advanced Memory MCP (cross-run memory).
  - Fine-tuned Llama model for game-specific jargon.
  - Voice input/output support.
- **v2.2.0 (Current)**:
  - Updated game mechanics knowledge base with verified data.
  - Corrected skill hint system (5 levels, 40% max).
  - Corrected aptitude system (G-S scale, no SS).
  - Track condition impact modeling.

---

## 10. Open Questions and Assumptions

- **Assumption**: User hosting the app locally has hardware capable of running Ollama (8GB+ RAM recommended).
- **Assumption**: Cloud API keys are managed securely via `.env`.
- **Open Question**: How much history should be injected into the prompt context window? *Current: Last 5 turns + Current State.*

---

## Changelog

| Version | Date | Changes |
| --- | --- | --- |
| 2.2.0 | January 28, 2026 | Updated with verified game mechanics from Global English Server: added game mechanics knowledge base section with accurate skill hint system (5 levels, 40% max), aptitude system (G-S, no SS), stat system (1200+ diminishing returns), track conditions, and career structure (~70-78 turns). |
| 2.1.0 | January 24, 2026 | Aligned with codebase v2.0.0, added source specs references. |
| 2.0.0 | January 2026 | Initial v2 release with hybrid AI architecture. |
