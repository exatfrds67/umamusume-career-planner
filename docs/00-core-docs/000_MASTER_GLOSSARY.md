# Master Glossary

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.1
**Date**: January 23, 2026
**Project**: UmamusumeCareerPlanner
**Author**: Development Team
**Status**: Current - Aligned to codebase

---

## Purpose

This glossary defines core terminology used across the project. Terms reflect the current implementation and configuration in the Laravel 12 codebase.

---

## Glossary

| Term | Abbreviation | Definition |
| --- | --- | --- |
| AI Conversation | AI Conv | Persisted AI interaction stored in `ucp_ai_conversations` and `ucp_conversation_messages`. |
| AI Cost | AI Cost | Cost tracking entry for AI usage (`ucp_ai_costs`). |
| AI Metrics | AI Metrics | Aggregate performance metrics for AI services (`ucp_ai_metrics`). |
| AI Provider | - | Configured AI backend (Neuron providers, Ollama, Bedrock). |
| Alpine.js | Alpine | Lightweight JS framework used in the frontend. |
| Aptitude | - | Character compatibility with distance, surface, and running style; stored in `ucp_aptitudes`. |
| Backup | - | Exportable archive of user data managed by `BackupService`. |
| Bedrock | - | AWS Bedrock integration for cloud AI inference, configured in `config/ai.php` and `config/neuron.php`. |
| Cache Management | - | Cache metrics, warming, and invalidation via API and services. |
| Career | - | A run lifecycle for a character; stored in `ucp_careers`. |
| Character | - | User-owned trainee entity; stored in `ucp_characters`. |
| Character Support Card | CSC | Pivot for deck assignment and friendship tracking (`character_support_cards`). |
| Conversation Message | - | Individual message within an AI conversation (`ucp_conversation_messages`). |
| Deck | - | Six-card support deck assigned to a character. |
| External Data | - | Cached records from external APIs (`ucp_external_data`). |
| GD | - | PHP GD library used for image preprocessing. |
| Horizon | - | Laravel queue monitoring (configured in `config/horizon.php`). |
| MCP | Model Context Protocol | Local or remote tool/agent integration framework used for AI workflows. |
| MCP Agent | - | MCP agent record stored in `ucp_mcp_agents`. |
| MCP Server | - | MCP server configuration stored in `ucp_mcp_servers`. |
| MCP Tool Usage | - | Tracking of tool calls (`ucp_mcp_tool_usage`). |
| Neuron AI | - | Neuron AI framework integration for agent-driven advice. |
| OCR | Optical Character Recognition | Tesseract-based OCR flow with GD preprocessing. |
| Ollama | - | Local AI inference provider for LLMs. |
| PWA | Progressive Web App | Offline-capable web app with service worker and manifest. |
| Race | - | Race record with conditions and outcomes (`ucp_races`). |
| Redis | - | Cache and queue backend used by Laravel. |
| Sanctum | - | Laravel API authentication for token-based sessions. |
| Skill | - | Skill catalog entry (`ucp_skills`). |
| Skill Acquisition | - | Skill acquisition record (`ucp_skill_acquisitions`). |
| Skill Hint | - | Hint record for SP reduction (`ucp_skill_hints`). |
| Support Card | - | Support card inventory entry (`ucp_support_cards`). |
| Tesseract | - | OCR engine configured in `config/services.php`. |
| Training Session | - | Training data per turn (`ucp_training_sessions`). |
| Vite | - | Frontend build tool (Vite v7). |

---

## Notes

- OpenCV is not currently integrated. Configuration keys exist in `config/services.php`, but preprocessing is performed with GD.
- MCP servers are configurable in `config/mcp.php`; Neuron MCP connector defaults to disabled in `config/neuron.php`.

---

## Document Control

| Version | Date | Author | Changes |
| --- | --- | --- | --- |
| 2.1 | 2026-01-23 | Development Team | Updated terms to match current codebase and configs |
| 2.0 | 2026-01-12 | Development Team | Consolidated glossary |

---

*This glossary is authoritative for terms used in core documentation.*
