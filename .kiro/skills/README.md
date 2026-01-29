# Kiro Skills Directory

This directory contains skill resources for Kiro AI agents. Skills are progressively loaded documentation files that provide specialized knowledge to agents on demand.

## What are Skills?

Skills are markdown files with YAML frontmatter that contain specialized knowledge. Unlike regular file resources that are loaded entirely at startup, skills are loaded progressively:

1. **At Startup**: Only metadata (name and description) is loaded
2. **On Demand**: Full content is loaded when the agent determines it's needed
3. **Context Efficient**: Keeps agent context lean while providing access to extensive documentation

## Skill File Format

Each skill file must:

- Be named with `-SKILL.md` suffix (e.g., `laravel-testing-SKILL.md`)
- Begin with YAML frontmatter containing `name` and `description`
- Have a specific, descriptive description so agents know when to load it

### Example Structure

```markdown
---
name: skill-identifier
description: Specific description of when to use this skill. Be clear about the use cases.
---

# Skill Title

## Content sections...
```

## Available Skills

### 1. Laravel Testing Best Practices (`laravel-testing-SKILL.md`)

**Name**: `laravel-testing-best-practices`

**Description**: Laravel 12 testing best practices with Pest v4. Use when writing or reviewing tests, understanding test structure, or implementing test coverage.

**Topics Covered**:

- Pest v4 syntax and patterns
- Unit, feature, and browser testing
- Testing dual storage mode
- Testing services and Livewire components
- Database testing and factories
- Mocking and faking
- Code coverage
- Troubleshooting guide

**Documentation References**:

- `docs/testing/`: Testing guides and reports
- `docs/setup-guides/INSTALL_CODE_COVERAGE.md`

### 2. Uma Musume Domain Knowledge (`uma-musume-domain-SKILL.md`)

**Name**: `uma-musume-domain-knowledge`

**Description**: Domain knowledge for Uma Musume Pretty Derby game mechanics, character stats, training system, and career planning. Use when working with game-specific features, calculations, or business logic.

**Topics Covered**:

- Character stats and aptitudes
- Training system mechanics
- Skill system and SP management
- Career run structure
- Race mechanics and strategy
- Calculation formulas
- Database schema reference
- Optimization strategies

**Documentation References**:

- `docs/02-prds/`: PRD-001 through PRD-005
- `docs/02-specs/`: SPEC-002 through SPEC-005
- `docs/00-core-docs/009_DBD_Database_Documentation.md`
- `docs/research/game-mechanics-research-report.md`

### 3. Laravel 12 Architecture Patterns (`laravel-architecture-SKILL.md`)

**Name**: `laravel-12-architecture-patterns`

**Description**: Laravel 12 architecture patterns, service layer design, repository pattern, and project structure. Use when designing features, refactoring code, or implementing new modules.

**Topics Covered**:

- Layered architecture
- Laravel 12 specific changes
- Service layer pattern
- Repository pattern
- Controller design
- Form request validation
- Event-driven architecture
- Model design and Eloquent
- Enum usage
- Caching strategy
- API resources

**Documentation References**:

- `docs/00-core-docs/004_SDS_Software_Design_Specifications.md`
- `docs/00-core-docs/010_SCD_Source_Code_Documentation.md`
- `docs/01-tech-flow/`: Technical flows
- `docs/reference/DEVELOPER_GUIDE.md`

### 4. Project Documentation Structure (`project-documentation-SKILL.md`)

**Name**: `project-documentation-structure`

**Description**: Complete project documentation structure, PRDs, SPECs, flows, sequences, and technical documentation. Use when navigating documentation, understanding requirements, or implementing features based on specifications.

**Topics Covered**:

- Core documentation (SDP, BRS, SRS, SDS, DBD, SCD, SUM)
- Product Requirements Documents (PRD-001 through PRD-007)
- Technical Specifications (SPEC-001 through SPEC-008)
- System flows and sequence diagrams
- Technical flows and user flows
- Wireframes and UI documentation
- Implementation documentation
- Documentation best practices
- Quick reference guides

**Documentation References**:

- `docs/00-core-docs/`: All core documentation
- `docs/02-prds/`: All PRDs
- `docs/02-specs/`: All SPECs
- `docs/01-flows/`, `docs/01-sequences/`, `docs/01-tech-flow/`, `docs/01-user-flows/`
- `docs/01-wireframes/`: UI wireframes

## Using Skills in Agent Configuration

### Basic Configuration

```json
{
  "resources": [
    "skill://.kiro/skills/**/SKILL.md"
  ]
}
```

This glob pattern will load all skill files in this directory and subdirectories.

### Specific Skills

```json
{
  "resources": [
    "skill://.kiro/skills/laravel-testing-SKILL.md",
    "skill://.kiro/skills/uma-musume-domain-SKILL.md",
    "skill://.kiro/skills/project-documentation-SKILL.md"
  ]
}
```

Load only specific skills if you want to limit the agent's knowledge scope.

## Creating New Skills

### Guidelines

1. **Specific Descriptions**: Write clear, specific descriptions that help agents determine when to load the skill
2. **Focused Content**: Each skill should cover a specific domain or topic
3. **Comprehensive**: Include all relevant information for the topic
4. **Examples**: Provide code examples and practical use cases
5. **Cross-References**: Link to related documentation (PRDs, SPECs, etc.)
6. **Version Info**: Include version information for dependencies

### Naming Convention

- Use descriptive names with `-SKILL.md` suffix
- Use kebab-case for filenames
- Examples:
  - `laravel-testing-SKILL.md`
  - `uma-musume-domain-SKILL.md`
  - `api-integration-SKILL.md`

### Template

```markdown
---
name: your-skill-identifier
description: Clear, specific description of when to use this skill. Include key use cases and scenarios.
---

# Skill Title

## Overview

Brief overview of what this skill covers.

## Main Content Sections

### Section 1

Content...

### Section 2

Content...

## Examples

Practical examples...

## Best Practices

Guidelines and recommendations...

## Related Documentation

- Links to related docs
- References to PRDs/SPECs
- External documentation

## Version Information

- Relevant versions
- Last updated date
```

## Recommended Skills to Create

### High Priority

1. **Livewire 3 Patterns** (`livewire-patterns-SKILL.md`)
   - Component design
   - State management
   - Validation patterns
   - Real-time updates
   - References: `docs/frontend-development/`

2. **Database Optimization** (`database-optimization-SKILL.md`)
   - Query optimization
   - Index strategies
   - N+1 prevention
   - Migration best practices
   - References: `docs/00-core-docs/009_DBD_Database_Documentation.md`

3. **API Integration** (`api-integration-SKILL.md`)
   - External API patterns
   - Circuit breaker implementation
   - Caching strategies
   - Error handling
   - References: `docs/external-api-integration/`, `docs/02-specs/SPEC-007_External_Integration_Technical.md`

### Medium Priority

1. **Frontend Development** (`frontend-development-SKILL.md`)
   - Alpine.js patterns
   - Tailwind CSS v4
   - Component architecture
   - Accessibility
   - References: `docs/frontend-development/`, `docs/accessibility/`

2. **Security Best Practices** (`security-practices-SKILL.md`)
   - Authentication
   - Authorization
   - Input validation
   - XSS/CSRF protection
   - References: `docs/00-core-docs/003_SRS_Software_Requirement_Specifications.md`

3. **Performance Optimization** (`performance-optimization-SKILL.md`)
   - Caching strategies
   - Query optimization
   - Asset optimization
   - Monitoring
   - References: `docs/02-specs/SPEC-008_Performance_Monitoring_Technical.md`

4. **MCP Integration** (`mcp-integration-SKILL.md`)
   - MCP server configuration
   - Tool integration
   - Agent orchestration
   - References: `docs/mcp-integration/`, `docs/neuron/`

## Maintenance

### Updating Skills

When updating skills:

1. Update the content
2. Update version information
3. Update "Last Updated" date
4. Update documentation references
5. Test with agents to ensure proper loading

### Deprecating Skills

If a skill becomes outdated:

1. Add deprecation notice at the top
2. Point to replacement skill
3. Keep file for backward compatibility
4. Remove after transition period

## Integration with Project

### Agent Examples

**Laravel Development Agent**:

```json
{
  "name": "laravel-dev",
  "resources": [
    "skill://.kiro/skills/laravel-testing-SKILL.md",
    "skill://.kiro/skills/laravel-architecture-SKILL.md"
  ]
}
```

**Domain Expert Agent**:

```json
{
  "name": "uma-musume-expert",
  "resources": [
    "skill://.kiro/skills/uma-musume-domain-SKILL.md",
    "skill://.kiro/skills/project-documentation-SKILL.md"
  ]
}
```

**Full Stack Agent**:

```json
{
  "name": "fullstack-dev",
  "resources": [
    "skill://.kiro/skills/**/SKILL.md"
  ]
}
```

**Documentation Agent**:

```json
{
  "name": "docs-writer",
  "resources": [
    "skill://.kiro/skills/project-documentation-SKILL.md",
    {
      "type": "knowledgeBase",
      "source": "file://./docs",
      "name": "ProjectDocs",
      "description": "All project documentation"
    }
  ]
}
```

## Benefits of Skills

1. **Context Efficiency**: Only load what's needed
2. **Specialized Knowledge**: Deep expertise on specific topics
3. **Maintainability**: Update knowledge in one place
4. **Reusability**: Share skills across multiple agents
5. **Progressive Enhancement**: Start simple, add complexity as needed
6. **Documentation Integration**: Direct references to project docs

## Related Documentation

- [Kiro Agent Configuration Reference](https://kiro.dev/docs/cli/custom-agents/configuration-reference/#skill-resources)
- AGENTS.md: Kiro Agent Configuration section
- SKILLS.md: AI Agent Configuration Skills section
- `docs/00-core-docs/000_DOCUMENT_INDEX.md`: Master documentation index

## Version Information

- Kiro CLI: Latest
- Project Version: v2.0.0
- Skills Count: 4
- Last Updated: 2026-01-29
