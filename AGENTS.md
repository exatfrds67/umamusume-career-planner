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

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.11
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- livewire/livewire (LIVEWIRE) - v4
- larastan/larastan (LARASTAN) - v3
- laravel/horizon (HORIZON) - v5
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- laravel/telescope (TELESCOPE) - v5
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- alpinejs (ALPINEJS) - v3
- tailwindcss (TAILWINDCSS) - v4

## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double-check the available parameters.

## URLs
- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches when dealing with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The `search-docs` tool is perfect for all Laravel-related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

## PHP

- Always use strict typing at the head of a `.php` file: `declare(strict_types=1);`.
- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless there is something very complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

=== tests rules ===

## Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

## Laravel 12

- Use the `search-docs` tool to get version-specific documentation.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

### Laravel 12 Structure
- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app\Console\Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== livewire/core rules ===

## Livewire

- Use the `search-docs` tool to find exact version-specific documentation for how to write Livewire and Livewire tests.
- Use the `php artisan make:livewire [Posts\CreatePost]` Artisan command to create new components.
- State should live on the server, with the UI reflecting it.
- All Livewire requests hit the Laravel backend; they're like regular HTTP requests. Always validate form data and run authorization checks in Livewire actions.

## Livewire Best Practices
- Livewire components require a single root element.
- Use `wire:loading` and `wire:dirty` for delightful loading states.
- Add `wire:key` in loops:

    ```blade
    @foreach ($items as $item)
        <div wire:key="item-{{ $item->id }}">
            {{ $item->name }}
        </div>
    @endforeach
    ```

- Prefer lifecycle hooks like `mount()`, `updatedFoo()` for initialization and reactive side effects:

<code-snippet name="Lifecycle Hook Examples" lang="php">
    public function mount(User $user) { $this->user = $user; }
    public function updatedSearch() { $this->resetPage(); }
</code-snippet>

## Testing Livewire

<code-snippet name="Example Livewire Component Test" lang="php">
    Livewire::test(Counter::class)
        ->assertSet('count', 0)
        ->call('increment')
        ->assertSet('count', 1)
        ->assertSee(1)
        ->assertStatus(200);
</code-snippet>

<code-snippet name="Testing Livewire Component Exists on Page" lang="php">
    $this->get('/posts/create')
    ->assertSeeLivewire(CreatePost::class);
</code-snippet>

=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.

=== pest/core rules ===

## Pest
### Testing
- If you need to verify a feature is working, write or update a Unit / Feature test.

### Pest Tests
- All tests must be written using Pest. Use `php artisan make:test --pest {name}`.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files - these are core to the application.
- Tests should test all of the happy paths, failure paths, and weird paths.
- Tests live in the `tests/Feature` and `tests/Unit` directories.
- Pest tests look and behave like this:
<code-snippet name="Basic Pest Test Example" lang="php">
it('is true', function () {
    expect(true)->toBeTrue();
});
</code-snippet>

### Running Tests
- Run the minimal number of tests using an appropriate filter before finalizing code edits.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).
- When the tests relating to your changes are passing, ask the user if they would like to run the entire test suite to ensure everything is still passing.

### Pest Assertions
- When asserting status codes on a response, use the specific method like `assertForbidden` and `assertNotFound` instead of using `assertStatus(403)` or similar, e.g.:
<code-snippet name="Pest Example Asserting postJson Response" lang="php">
it('returns all', function () {
    $response = $this->postJson('/api/docs', []);

    $response->assertSuccessful();
});
</code-snippet>

### Mocking
- Mocking can be very helpful when appropriate.
- When mocking, you can use the `Pest\Laravel\mock` Pest function, but always import it via `use function Pest\Laravel\mock;` before using it. Alternatively, you can use `$this->mock()` if existing tests do.
- You can also create partial mocks using the same import or self method.

### Datasets
- Use datasets in Pest to simplify tests that have a lot of duplicated data. This is often the case when testing validation rules, so consider this solution when writing tests for validation rules.

<code-snippet name="Pest Dataset Example" lang="php">
it('has emails', function (string $email) {
    expect($email)->not->toBeEmpty();
})->with([
    'james' => 'james@laravel.com',
    'taylor' => 'taylor@laravel.com',
]);
</code-snippet>

=== pest/v4 rules ===

## Pest 4

- Pest 4 is a huge upgrade to Pest and offers: browser testing, smoke testing, visual regression testing, test sharding, and faster type coverage.
- Browser testing is incredibly powerful and useful for this project.
- Browser tests should live in `tests/Browser/`.
- Use the `search-docs` tool for detailed guidance on utilizing these features.

### Browser Testing
- You can use Laravel features like `Event::fake()`, `assertAuthenticated()`, and model factories within Pest 4 browser tests, as well as `RefreshDatabase` (when needed) to ensure a clean state for each test.
- Interact with the page (click, type, scroll, select, submit, drag-and-drop, touch gestures, etc.) when appropriate to complete the test.
- If requested, test on multiple browsers (Chrome, Firefox, Safari).
- If requested, test on different devices and viewports (like iPhone 14 Pro, tablets, or custom breakpoints).
- Switch color schemes (light/dark mode) when appropriate.
- Take screenshots or pause tests for debugging when appropriate.

### Example Tests

<code-snippet name="Pest Browser Test Example" lang="php">
it('may reset the password', function () {
    Notification::fake();

    $this->actingAs(User::factory()->create());

    $page = visit('/sign-in'); // Visit on a real browser...

    $page->assertSee('Sign In')
        ->assertNoJavascriptErrors() // or ->assertNoConsoleLogs()
        ->click('Forgot Password?')
        ->fill('email', 'nuno@laravel.com')
        ->click('Send Reset Link')
        ->assertSee('We have emailed your password reset link!')

    Notification::assertSent(ResetPassword::class);
});
</code-snippet>

<code-snippet name="Pest Smoke Testing Example" lang="php">
$pages = visit(['/', '/about', '/contact']);

$pages->assertNoJavascriptErrors()->assertNoConsoleLogs();
</code-snippet>

=== tailwindcss/core rules ===

## Tailwind CSS

- Use Tailwind CSS classes to style HTML; check and use existing Tailwind conventions within the project before writing your own.
- Offer to extract repeated patterns into components that match the project's conventions (i.e. Blade, JSX, Vue, etc.).
- Think through class placement, order, priority, and defaults. Remove redundant classes, add classes to parent or child carefully to limit repetition, and group elements logically.
- You can use the `search-docs` tool to get exact examples from the official documentation when needed.

### Spacing
- When listing items, use gap utilities for spacing; don't use margins.

<code-snippet name="Valid Flex Gap Spacing Example" lang="html">
    <div class="flex gap-8">
        <div>Superior</div>
        <div>Michigan</div>
        <div>Erie</div>
    </div>
</code-snippet>

### Dark Mode
- If existing pages and components support dark mode, new pages and components must support dark mode in a similar way, typically using `dark:`.

=== tailwindcss/v4 rules ===

## Tailwind CSS 4

- Always use Tailwind CSS v4; do not use the deprecated utilities.
- `corePlugins` is not supported in Tailwind v4.
- In Tailwind v4, configuration is CSS-first using the `@theme` directive — no separate `tailwind.config.js` file is needed.

<code-snippet name="Extending Theme in CSS" lang="css">
@theme {
  --color-brand: oklch(0.72 0.11 178);
}
</code-snippet>

- In Tailwind v4, you import Tailwind using a regular CSS `@import` statement, not using the `@tailwind` directives used in v3:

<code-snippet name="Tailwind v4 Import Tailwind Diff" lang="diff">
   - @tailwind base;
   - @tailwind components;
   - @tailwind utilities;
   + @import "tailwindcss";
</code-snippet>

### Replaced Utilities
- Tailwind v4 removed deprecated utilities. Do not use the deprecated option; use the replacement.
- Opacity values are still numeric.

| Deprecated |	Replacement |
|------------+--------------|
| bg-opacity-* | bg-black/* |
| text-opacity-* | text-black/* |
| border-opacity-* | border-black/* |
| divide-opacity-* | divide-black/* |
| ring-opacity-* | ring-black/* |
| placeholder-opacity-* | placeholder-black/* |
| flex-shrink-* | shrink-* |
| flex-grow-* | grow-* |
| overflow-ellipsis | text-ellipsis |
| decoration-slice | box-decoration-slice |
| decoration-clone | box-decoration-clone |
</laravel-boost-guidelines>
