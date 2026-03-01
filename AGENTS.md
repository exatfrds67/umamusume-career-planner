# AI Agent Development Guidelines (v2.0.0)

This document defines development guidelines, coding standards, and documentation practices for AI coding assistants working on the **Uma Musume Career Planner / Umamusume Pretty Derby Career Planner** Laravel application.

It is intended to ensure consistent, secure, testable, and maintainable output aligned with:

- Laravel 12 conventions and common industry practices
- The project’s **dual storage architecture** (Local vs Account)
- The project documentation set (PRDs, SPECs, FLOWS, SEQs, TECH-FLOWs, USER FLOWs, WIREFRAMES)
- The current codebase structure (Service Layer + Livewire 3 + Alpine.js + TailwindCSS v4)

**Document Version**: 2.1.0  
**Date**: 2026-01-29  
**Project**: UmamusumeCareerPlanner  
**Status**: Current (v2 aligned with Kiro agent support)  

---

## Table of Contents

1. [Project Overview](#project-overview)
2. [Golden Rules](#golden-rules)
3. [Architecture & Domain Model](#architecture--domain-model)
4. [Dual Storage Mode Rules](#dual-storage-mode-rules)
5. [Laravel 12 + PHP Standards](#laravel-12--php-standards)
6. [Frontend Standards (Livewire + Alpine + Tailwind)](#frontend-standards-livewire--alpine--tailwind)
7. [API & Integration Guidance](#api--integration-guidance)
8. [Data Import/Export & Migration Guidance](#data-importexport--migration-guidance)
9. [Testing Standards](#testing-standards)
10. [Security Standards](#security-standards)
11. [Performance, Caching, and Reliability](#performance-caching-and-reliability)
12. [Documentation Standards](#documentation-standards)
13. [Kiro Agent Configuration](#kiro-agent-configuration)
14. [Pull Request & Change Management](#pull-request--change-management)
15. [Definition of Done](#definition-of-done)

---

## Project Overview

### Product Summary

A **local-first** career planning / career tracking application for *Uma Musume: Pretty Derby* that supports:

- Character/career run tracking
- Turn-by-turn stat progression (Speed/Stamina/Power/Guts/Wit)
- Aptitude grades and growth rates
- Skill acquisition planning and SP budgeting
- Race planning and outcomes tracking (as documented)
- Import/export, backup/restore, and migration workflows
- AI advisory and external integrations (as documented)

### Technical Stack (v2)

- **Backend**: Laravel 12+, PHP 8.2+ (project guidelines mention 8.4.11 also; follow repository `composer.json` and CI)
- **Frontend**: Livewire 3, Alpine.js, TailwindCSS v4, Vite
- **DB**: MySQL 8+ (prod), SQLite supported for dev/testing
- **Cache/Queues**: Redis (where enabled)
- **Testing**: Pest (PHP), Playwright (E2E), optional JS unit tests as configured
- **Docs**: PRDs, SPECs, FLOWs, SEQs, TECH-FLOWs, USER FLOWs, WIREFRAMES, plus core docs (SDP/BRS/SRS/SDS/DBD/SCD/SUM)

---

## Golden Rules

1. **Do not break dual storage mode behavior.** Any feature touching plans/runs must work in both:
   - **Local Mode** (browser storage, UUID routes)
   - **Account Mode** (database, numeric ID routes)
2. **Prefer the Service Layer for business logic.** Livewire components/controllers coordinate, validate, and delegate.
3. **Maintain canonical naming.** Prefer canonical fields (e.g., `total_sp_available`, `stamina_percentage`, `turn_number`) over legacy variants.
4. **Be explicit with enums and validation.** Use PHP enums for domain states and validate inputs with Form Requests / Livewire validation.
5. **Test critical flows.** Any change impacting plan creation/edit/save/import/export must include tests (Pest + Playwright where applicable).
6. **Keep docs consistent and cross-linked.** When updating behavior, ensure docs align with the related PRD/SPEC/FLOW/SEQ.

---

## Architecture & Domain Model

### High-Level Architecture (v2)

Layered application with strong separation:

- **Presentation**: Blade views, Livewire components, Alpine-driven UI components
- **Application**: Controllers (API), Livewire actions, Form Requests, orchestration
- **Domain**: Eloquent models + Enums + domain rules
- **Infrastructure**: MySQL/SQLite, Redis, file storage, integrations

### Canonical Domain Entities (v2)

- `UmaMusume` → `CareerRun` → `StatProgress`, `SkillCareerRun`, `Goal`, `RacePrediction` (+ additional features documented elsewhere)
- Enums used across domain: `StorageMode`, `RunStatus`, `SkillStatus`, `CareerStage`, `AptitudeGrade`, `Mood`

Keep domain invariants consistent with SRS/SDS/DBD and the older detailed docs (PRDs/SPECs/FLOWs/SEQs).

---

## Dual Storage Mode Rules

### Storage Modes

- **Local Mode**
  - Stored in browser (`localStorage` today; may migrate to IndexedDB later per roadmap)
  - UUID-based routes: `/plans/local/{uuid}` (and `/edit`)
  - Must be fully usable offline
- **Account Mode**
  - Stored in database
  - Numeric ID routes: `/plans/{id}` (and `/edit`)
  - Requires connectivity for persistence; drafts must still be preserved locally

### Engineering Guidelines

- Never assume a plan ID is numeric.
- Always propagate storage mode explicitly:
  - UI badges (Local/Account)
  - Route generation helpers
  - Serialization/deserialization functions

### Conversion: Local → Account

- Provide a safe conversion that:
  - Validates data
  - Detects duplicates
  - Persists relations correctly
  - Optionally keeps local copy
  - Produces a results report

This aligns with:

- User flows (Local-to-Account conversion flows)
- Sequences describing import/migration and state preservation
- Import/export schema definitions

---

## Laravel 12 + PHP Standards

### PHP Style and Type Safety

- Follow **PSR-12** and project formatting tooling (`pint`).
- Use strict types where the project uses them; prefer type hints everywhere.
- Prefer PHP 8.2+ features:
  - Enums
  - Readonly properties where appropriate
  - Typed properties
- Avoid “magic arrays” for domain objects; prefer DTOs or validated arrays with clear keys.

### Laravel Conventions

- Validation:
  - Use **Form Requests** for controllers.
  - Use Livewire validation rules for Livewire components.
- Database:
  - Prefer migrations with proper foreign keys and indexes.
  - Use soft deletes where required and documented.
- Error handling:
  - Standardize error responses for APIs.
  - Use consistent toast/alert events for UI.

---

## Frontend Standards (Livewire + Alpine + Tailwind)

### Livewire

- Keep Livewire component state minimal; avoid hydrating heavy relationships unnecessarily.
- Use `wire:key` for dynamic lists (skills, turns).
- Prefer “save/apply” actions rather than continuous server roundtrips for high-frequency UI updates.

### Alpine.js

- Use Alpine for:
  - Modals, dropdowns, tabs, toasts
  - Client-side localStorage helpers (drafts, local runs)
  - Non-critical UI state that does not require server persistence
- Keep Alpine state names predictable and scoped.

### TailwindCSS v4

- Prefer utility classes and shared Blade components.
- Maintain WCAG AA contrast in both light and dark modes.
- Ensure touch targets and responsive behavior per the user flows and wireframes.

---

## API & Integration Guidance

### Internal APIs (Read-heavy)

- Use internal JSON endpoints where appropriate (e.g., skill search/autocomplete) to reduce Livewire payload size.
- Cache read-heavy endpoints (skills list/search) with sensible TTL.

### External Integrations (if enabled in module set)

- Follow resilience patterns documented:
  - Circuit breaker / fallback for external APIs
  - Timeouts, retries, caching
- Log and track failures without exposing sensitive data.

---

## Data Import/Export & Migration Guidance

### Export Requirements

- Include `schema_version`
- Include `exported_at` (ISO 8601)
- Preserve canonical fields and enum normalization
- Support export for:
  - Individual plan/run
  - Bulk local runs (“Export All”)

### Import Requirements

- Detect format (JSON versioned, legacy JSON, CSV, etc.)
- Validate schema + business rules
- Provide a preview stage
- Handle duplicates: Skip / Overwrite / Import as copy (Merge optional future feature)
- Provide clear error reporting and partial success reporting

### Migration Considerations

- Always be able to rollback or explain how to reverse imported batches.
- Never silently drop data: record warnings and errors in an import report object.

---

## Testing Standards

### Minimum Test Expectations (v2)

- **Pest Unit/Feature**
  - Services: stat validation/calculation, SP calculations, import detection/validation
  - Storage mode decision logic
- **Playwright E2E**
  - Create plan (Local)
  - Create plan (Account)
  - Edit plan fields across tabs
  - Skill add/remove + status changes
  - Export and import basic cases
  - Local → Account conversion

### General Testing Rules

- Prefer deterministic tests; avoid time-based flakes.
- Add regression tests when fixing bugs.
- Ensure tests run in CI with minimal env assumptions.

---

## Security Standards

- Treat localStorage data as **untrusted input**.
- Validate all user-provided data:
  - File uploads: strict MIME/type/size
  - Imported data: schema + business rules
  - Text fields: avoid XSS; rely on Blade escaping and careful rendering
- Enforce per-user authorization on Account runs:
  - Do not allow IDOR via `/plans/{id}`.
- Apply rate limiting to public endpoints where configured.
- Keep secrets out of logs and documentation.

---

## Performance, Caching, and Reliability

### Performance Targets (v2 alignment)

- Prefer p95 targets consistent with SRS:
  - Fast page loads
  - Skill autocomplete and predictions responsive
- Cache where data is stable:
  - Skill catalog, character lists, read-only reference data
- Avoid N+1 queries; eager load relationships intentionally.

### Reliability

- Offline handling:
  - Account mode must degrade gracefully when offline (disable save; preserve draft)
  - Local mode must remain functional offline
- Draft autosave:
  - Ensure draft clearing after successful save
  - Provide restore/discard flows

---

## Documentation Standards

### Source of Truth and Consistency

When updating implementation or docs, ensure alignment with:

- **PRDs**: product requirements per module
- **SPECs**: technical requirements and contracts
- **FLOWs**: system flows (Mermaid)
- **SEQs**: interaction sequences (Mermaid)
- **TECH-FLOWs**: architecture + task breakdowns
- **USER FLOWs**: journey maps and decision points
- **WIREFRAMES**: UX expectations

### Documentation Creation by AI Agents

**IMPORTANT**: When creating documentation as an AI agent or LLM:

- **All documentation files MUST be created within the `docs/` directory**
- Place documentation in appropriate existing subdirectories (e.g., `docs/neuron/`, `docs/02-prds/`, `docs/02-specs/`)
- If no suitable subdirectory exists, create a new subdirectory within `docs/` with a descriptive name
- Never create documentation files in the project root or other directories
- Follow the existing documentation structure and naming conventions

#### Markdownlint Compliance (MANDATORY)

Especially when updating existing documents, you must adhere to markdownlint rules. All markdown (`.md`) files must pass `markdownlint-cli2` validation with zero errors:

- **Before committing**: Run `npx markdownlint-cli2 "docs/**/*.md"` to verify compliance
- **When updating existing documents**: MUST also fix any pre-existing markdownlint violations in the modified file
- **Table formatting** (MD060 - most common):
  - Use consistent pipe spacing: `| value |` not `|value|`
  - Separator rows: `| --- | --- |` not `|---|---|`
  - Ensure all rows have same column count
- **No duplicate headings** (MD024): Each heading must be unique within the document
- **No emphasis-as-heading** (MD036): Use proper heading syntax `### Heading` not `**Heading**` alone on line
- **Valid link fragments** (MD051): Links to fragments must reference existing headings
- **Consistent heading style** (MD003): Use ATX style (`## Heading`) consistently
- **Blank lines around code blocks** (MD031): Add blank line before and after ` ```code` blocks
- **Single trailing newline** (MD047): Files must end with exactly one newline character

See `.agents/memory.instruction.md` section "Markdownlint Standards" for detailed rules, examples, and common fixes.

### Script Creation by AI Agents

**IMPORTANT**: When creating scripts as an AI agent or LLM:

- **All scripts MUST be created within the `scripts/` directory**
- Use appropriate subdirectories if they exist (e.g., `scripts/setup/`, `scripts/deployment/`)
- If creating a new category of scripts, create a subdirectory within `scripts/`
- Never create scripts in the project root or other directories
- Ensure scripts have appropriate file permissions and shebang lines

### Formatting Rules

- Use clear headings, ToC when appropriate, and consistent terminology.
- Prefer Mermaid diagrams when describing flows and sequences.
- Provide document control:
  - Version, date, status
  - Change log section

### Terminology Rules

- Use “Plan” and “Career Run” consistently (define once per doc).
- Use canonical field names in any schema examples.
- Define enums and valid values when relevant.

---

## Kiro Agent Configuration

### Overview

This project supports Kiro AI agent configuration for enhanced development workflows. Kiro agents are JSON-based configurations that define specialized AI assistants with specific tools, resources, and behaviors.

### Agent File Locations

#### Local Agents (Project-Specific)

- Location: `.kiro/agents/`
- Scope: Available only within this workspace
- Use for: Project-specific development tasks, domain logic assistance

#### Global Agents (User-Wide)

- Location: `~/.kiro/agents/`
- Scope: Available from any directory
- Use for: General-purpose development assistance

**Precedence**: Local agents override global agents with the same name.

### Core Configuration Fields

#### Essential Fields

- **name**: Agent identifier (derived from filename if omitted)
- **description**: Human-readable purpose description
- **prompt**: High-level context (inline text or `file://` URI)
- **tools**: Available tools (built-in, MCP server tools, wildcards)
- **allowedTools**: Auto-approved tools without user prompts
- **resources**: Local resources (files, skills, knowledge bases)

#### Advanced Fields

- **mcpServers**: Model Context Protocol server definitions
- **toolAliases**: Tool name remapping for collision resolution
- **toolsSettings**: Tool-specific configuration
- **hooks**: Lifecycle commands (agentSpawn, userPromptSubmit, preToolUse, postToolUse, stop)
- **model**: Specific model ID (e.g., "claude-sonnet-4")
- **keyboardShortcut**: Quick agent switching (e.g., "ctrl+a")
- **welcomeMessage**: Agent activation message

### Tool Management

#### Tool Reference Patterns

```json
{
  "tools": [
    "read",                              // Built-in tool
    "write",                             // Built-in tool
    "@git",                              // All tools from MCP server
    "@rust-analyzer/check_code",         // Specific MCP tool
    "*"                                  // All available tools
  ]
}
```

#### Permission Patterns

```json
{
  "allowedTools": [
    "read",                              // Exact match
    "@git/git_status",                   // Specific MCP tool
    "@server/read_*",                    // Glob pattern
    "@fetch"                             // All tools from server
  ]
}
```

### Resource Management

**File Resources** (loaded at startup)

```json
{
  "resources": [
    "file://README.md",
    "file://docs/**/*.md"
  ]
}
```

**Skill Resources** (progressive loading)

```json
{
  "resources": [
    "skill://.kiro/skills/**/SKILL.md"
  ]
}
```

Skill files must include YAML frontmatter:

```markdown
---
name: laravel-testing
description: Laravel testing best practices with Pest
---
# Content here...
```

**Knowledge Base Resources** (indexed documentation)

```json
{
  "resources": [
    {
      "type": "knowledgeBase",
      "source": "file://./docs",
      "name": "ProjectDocs",
      "description": "Project documentation and guides",
      "indexType": "best",
      "autoUpdate": true
    }
  ]
}
```

### Hook System

#### Hook Types

- `agentSpawn`: Initialization tasks (e.g., git status)
- `userPromptSubmit`: Pre-processing user input
- `preToolUse`: Audit logging, validation (can block execution)
- `postToolUse`: Post-processing (e.g., code formatting)
- `stop`: Cleanup or final validation

#### Example Hook Configuration

```json
{
  "hooks": {
    "agentSpawn": [
      { "command": "git status" }
    ],
    "postToolUse": [
      {
        "matcher": "fs_write",
        "command": "vendor/bin/pint --dirty"
      }
    ]
  }
}
```

### Project-Specific Agent Examples

#### Laravel Development Agent

```json
{
  "name": "laravel-dev",
  "description": "Laravel 12 development with Pest testing",
  "tools": ["read", "write", "shell", "@git"],
  "allowedTools": ["read", "@git/git_status"],
  "toolsSettings": {
    "write": {
      "allowedPaths": ["app/**", "tests/**", "database/**"]
    }
  },
  "resources": [
    "file://AGENTS.md",
    "file://.kiro/steering/**/*.md"
  ],
  "hooks": {
    "postToolUse": [
      {
        "matcher": "fs_write",
        "command": "vendor/bin/pint --dirty"
      }
    ]
  }
}
```

#### Documentation Agent

```json
{
  "name": "docs-writer",
  "description": "Documentation specialist with access to PRDs/SPECs",
  "tools": ["read", "write"],
  "resources": [
    {
      "type": "knowledgeBase",
      "source": "file://./docs",
      "name": "ProjectDocs",
      "description": "All project documentation",
      "indexType": "best",
      "autoUpdate": false
    }
  ],
  "toolsSettings": {
    "write": {
      "allowedPaths": ["docs/**/*.md"]
    }
  }
}
```

### Best Practices

#### Security

- Start with minimal tool access, expand as needed
- Use specific patterns over wildcards in allowedTools
- Configure toolsSettings for sensitive operations
- Test agents in safe environments first

#### Organization

- Use descriptive agent names and descriptions
- Keep prompt files organized and version controlled
- Document agent purposes clearly
- Store local agents in `.kiro/agents/` for team sharing

#### Performance

- Use skill resources for large documentation (progressive loading)
- Configure knowledge bases with appropriate indexType
- Enable autoUpdate only when necessary
- Use file resources for always-needed content only

### Integration with This Project

#### Recommended Agents

1. **Training Optimization Agent**: Domain-specific for training calculations
2. **Skill Analysis Agent**: Specialized in skill SP calculations and evolution
3. **Database Migration Agent**: Schema validation and migration assistance
4. **Code Review Agent**: Pre-commit formatting and quality checks

#### Resource Configuration

```json
{
  "resources": [
    "file://AGENTS.md",
    "file://SKILLS.md",
    "file://.kiro/steering/**/*.md",
    "skill://.kiro/skills/**/SKILL.md",
    {
      "type": "knowledgeBase",
      "source": "file://./docs",
      "name": "UmaMusumeDocs",
      "description": "PRDs, SPECs, flows, and sequences",
      "indexType": "best"
    }
  ]
}
```

### Reference

- **Official Documentation**: [Kiro Agent Configuration Reference](https://kiro.dev/docs/cli/custom-agents/configuration-reference/)
- **Skills Documentation**: See SKILLS.md Section 7 for detailed skill requirements

---

## Pull Request & Change Management

When generating changes (code or docs):

- Keep changes minimal and scoped.
- Write meaningful commit messages.
- Document behavior changes in:
  - README (if user-facing)
  - Core docs (SDP/SRS/SDS/etc. if architectural/requirement-affecting)
- Update tests alongside changes.

---

## Definition of Done

A change is considered complete when:

- [ ] Works in **Local** and **Account** modes (if applicable)
- [ ] Validation rules are present and tested
- [ ] Unit/feature tests pass (`php artisan test`)
- [ ] E2E tests pass where relevant (`npm run playwright:test`)
- [ ] Formatting passes (`pint`, prettier)
- [ ] Accessibility considerations are met (keyboard nav, focus management, contrast)
- [ ] Documentation is updated when behavior or interfaces changed
- [ ] **All markdown files pass markdownlint** without errors (`npx markdownlint-cli2 "docs/**/*.md"`)
  - When updating existing documents, pre-existing markdownlint violations MUST also be fixed
  - New documentation must follow all markdownlint rules (especially MD060, MD024, MD036, MD051, MD003, MD031, MD047)
  - Zero markdownlint errors is non-negotiable for all documentation commits

---
