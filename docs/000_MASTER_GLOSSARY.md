# Master Glossary

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 12, 2026  
**Project**: UmamusumeCareerPlanner  
**Author**: Development Team  
**Status**: Final  

---

## Table of Contents

1. [Purpose](#1-purpose)
2. [Technology Terms](#2-technology-terms)
3. [Game Mechanics Terms](#3-game-mechanics-terms)
4. [Architecture Terms](#4-architecture-terms)
5. [AI and Integration Terms](#5-ai-and-integration-terms)
6. [Document Conventions](#6-document-conventions)

---

## 1. Purpose

This Master Glossary provides standardized terminology for all documentation in the Umamusume Career Planner project. All documents MUST use these exact terms to ensure consistency across the documentation suite.

---

## 2. Technology Terms

### 2.1 Core Framework Technologies

| Standard Term | Variations to Avoid | Definition |
| ------------- | ------------------- | ---------- |
| **Laravel 12** | Laravel 12 Framework, Laravel Framework | PHP web application framework (Released February 24, 2025) |
| **Tailwind CSS v4** | Tailwind CSS, Tailwind v4, TailwindCSS | CSS framework with zero configuration (Released January 22, 2025) |
| **PHP 8.3+** | PHP 8.3, PHP8.3 | Server-side scripting language with required extensions |
| **MySQL 8.0+** | MySQL, MySQL 8, MySQL Database | Primary relational database with InnoDB engine |
| **Redis 7.0+** | Redis, Redis Cache, Redis Server | In-memory cache, session store, and queue driver |

### 2.2 AI Technologies

| Standard Term | Variations to Avoid | Definition |
| ------------- | ------------------- | ---------- |
| **Ollama** | Local AI, Ollama Local | Local AI inference engine for privacy-preserving processing |
| **AWS Bedrock** | Bedrock, Amazon Bedrock | Cloud AI service with Claude and Nova models |
| **Hybrid AI Processing** | AI Services, Hybrid AI System | Combined local Ollama + AWS Bedrock cloud architecture |
| **Claude 4.5 Opus** | Claude Opus, Opus | Premium AWS Bedrock model ($5/$25 per 1M tokens) |
| **Claude 4.5 Sonnet** | Claude Sonnet, Sonnet | Standard AWS Bedrock model ($3/$15 per 1M tokens) |
| **Claude 4.5 Haiku** | Claude Haiku, Haiku | Economy AWS Bedrock model ($1/$5 per 1M tokens) |
| **Nova 2 Lite** | Nova Lite, Amazon Nova | Budget AWS Bedrock model ($0.00125 per 1K tokens) |
| **Llama 3.3** | Llama, LLaMA | Ollama local model for general purpose processing |
| **Mistral** | Mistral AI | Ollama local model for fast responses |
| **Qwen 2.5** | Qwen, Qwen2.5 | Ollama local model for multilingual support |

### 2.3 Integration Technologies

| Standard Term | Variations to Avoid | Definition |
| ------------- | ------------------- | ---------- |
| **MCP (Model Context Protocol)** | MCP Servers, Model Context Protocol Servers | Protocol for enhanced AI and service integration |
| **OCR (Tesseract)** | Screenshot Processing, OCR Processing | Optical Character Recognition with Japanese support |
| **OpenCV** | CV, Computer Vision | Image preprocessing library for OCR enhancement |
| **Laravel Sanctum** | Sanctum, API Authentication | Laravel authentication package for APIs |
| **WebSocket** | WebSockets, Real-time | Bidirectional real-time communication protocol |

### 2.4 External APIs

| Standard Term | Variations to Avoid | Definition |
| ------------- | ------------------- | ---------- |
| **umapyoi.net** | Umapyoi, umapyoi API | Primary external game data API (verified active) |
| **UmamusumeDB.com** | UmamusumeDB, UmaDB | Secondary external API (requires verification) |
| **External Game Data APIs** | Game Data APIs, External APIs | Collective term for game data sources |

---

## 3. Game Mechanics Terms

### 3.1 Scenarios

| Standard Term | Variations to Avoid | Definition |
| ------------- | ------------------- | ---------- |
| **URA Finale** | URA, URA Finals | Individual character optimization scenario |
| **Unity Cup** | Unity, Team Scenario | Team-based mechanics with Spirit Burst |
| **Spirit Burst** | Burst, Spirit | Unity Cup 4-session gauge mechanic |

### 3.2 Stats and Attributes

| Standard Term | Range | Definition |
| ------------- | ----- | ---------- |
| **Speed** | 0-1200 | Primary racing stat (Priority ★★★★★) |
| **Stamina** | 0-1200 | Endurance stat (Priority ★★★★) |
| **Power** | 0-1200 | Acceleration stat (Priority ★★★) |
| **Guts** | 0-1200 | Determination stat (Priority ★) |
| **Wit** | 0-1200 | Intelligence stat (Priority ★★) |
| **Aptitude** | G-SS | Fixed talent rating for distance/surface/style |

### 3.3 Skill System

| Standard Term | Definition |
| ------------- | ---------- |
| **Skill Hint** | Training indicator (red "!") for skill acquisition |
| **SP Cost Reduction** | 20% per duplicate hint, 40% maximum |
| **Skill Evolution** | Normal → Rare automatic upgrade system |
| **Normal Skills** | 120-180 SP cost skills |
| **Rare Skills** | 180-240 SP cost skills |
| **Unique Skills** | Variable SP cost character-specific skills |

### 3.4 Support Cards

| Standard Term | Definition |
| ------------- | ---------- |
| **Support Card Deck** | 6-card configuration for training |
| **Meta Tier Rankings** | SS/S/A/B community rankings |
| **Friendship Training** | Bonus training with 80%+ friendship level |
| **Rainbow Training** | Maximum friendship bonus training |

---

## 4. Architecture Terms

### 4.1 Design Patterns

| Standard Term | Definition |
| ------------- | ---------- |
| **Repository Pattern** | Data access abstraction with interface-based design |
| **Service Layer** | Business logic separation with dependency injection |
| **Event-Driven Architecture** | Laravel Events and Listeners for decoupled components |
| **Circuit Breaker Pattern** | Design pattern for handling service failures |
| **Adapter Pattern** | Interface adaptation for external APIs |

### 4.2 Data Architecture

| Standard Term | Definition |
| ------------- | ---------- |
| **Local-First Architecture** | All personal data stored locally with optional cloud |
| **Hybrid Processing** | Combined local and cloud processing approach |
| **ETL (Extract, Transform, Load)** | Data migration processing pattern |
| **CDC (Change Data Capture)** | Incremental data synchronization pattern |

---

## 5. AI and Integration Terms

### 5.1 AI Processing

| Standard Term | Definition |
| ------------- | ---------- |
| **Complexity Detection** | Automatic request analysis for model selection |
| **Intelligent Routing** | Automatic selection between local and cloud AI |
| **Cost Optimization** | Minimizing cloud usage while maintaining quality |
| **Context Preservation** | Maintaining conversation context across models |

### 5.2 MCP Servers

| Standard Term | Definition |
| ------------- | ---------- |
| **strands-agents** | Strands Agent SDK MCP server |
| **agentcore-mcp-server** | Amazon Bedrock AgentCore MCP server |
| **awspricing** | AWS pricing information MCP server |
| **awsknowledge** | AWS documentation MCP server |
| **awsapi** | AWS API access MCP server |

---

## 6. Document Conventions

### 6.1 Requirement Notation

| Term | Meaning |
| ---- | ------- |
| **SHALL** | Mandatory requirement |
| **SHOULD** | Recommended requirement |
| **MAY** | Optional requirement |
| **MUST** | Absolute requirement (equivalent to SHALL) |

### 6.2 Priority Indicators

| Symbol | Level |
| ------ | ----- |
| ★★★★★ | Critical |
| ★★★★ | High |
| ★★★ | Medium |
| ★★ | Low |
| ★ | Optional |

### 6.3 Status Indicators

| Symbol | Meaning |
| ------ | ------- |
| ✅ | Complete/Verified |
| ⚠️ | Warning/Requires Attention |
| ❌ | Failed/Not Available |
| ○ | Adequate |
| ⦾ | Borderline |
| △ | Insufficient |
| × | Inadequate |

### 6.4 Date Format

All dates MUST use **YYYY-MM-DD** format (e.g., 2026-01-12).

### 6.5 Version Format

All versions MUST use **Semantic Versioning** (e.g., 1.0.0, 2.1.3).

---

## Document Cross-References

| Document Code | Document Name |
| ------------- | ------------- |
| 000_GLOSSARY | Master Glossary (this document) |
| 001_SDP | Software Development Plan |
| 002_BRS | Business Requirements Specification |
| 003_SRS | Software Requirements Specification |
| 004_SDS | Software Design Specification |
| 005_DMP | Data Migration Plan |
| 006_DMS | Data Migration Specifications |
| 007_SIP | Software Integration Plan |
| 008_SIS | Software Integration Specifications |
| 009_DBD | Database Documentation |
| 010_SCD | Source Code Documentation |
| 017_SUM | Software User Manual |

---

## Document Control

| Version | Date | Author | Changes |
| ------- | ---- | ------ | ------- |
| 1.0 | 2026-01-12 | Development Team | Initial glossary creation |

---

*This glossary is the authoritative source for all terminology used in the Umamusume Career Planner documentation suite. All documents must reference and comply with these standardized terms.*
