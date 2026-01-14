---
applyTo: '**'
---

# Coding Preferences

- Follow Laravel 12 coding standards and best practices
- Use PHP 8.3+ strict types and type declarations
- Implement WCAG 2.2 AA accessibility compliance
- Prefer Pest testing framework over PHPUnit
- Use Laravel Pint for code formatting

## Project Architecture

- **Framework**: Laravel 12 (Released February 24, 2025)
- **Frontend**: Tailwind CSS v4 (Released January 22, 2025)
- **Database**: MySQL 8.0+ with Redis 7.0+ caching via WSL
- **AI Integration**: Hybrid Ollama local + AWS Bedrock cloud
- **External APIs**: umapyoi.net (primary), UmamusumeDB.com (verification pending)
- **Testing**: Pest v3 with PHPUnit v11
- **Code Quality**: Laravel Pint v1
- **Requirements**: 59 total requirements, 15+ database tables, 80%+ test coverage
- **Timeline**: 22-28 weeks development (6 phases)
- **Document Numbering**: 000-010, 017 (011-016 reserved/unused)

## Solutions Repository

## Documentation Consistency

- **Problem**: Mixed date formats and inconsistent cross-references across documentation
- **Solution**: Standardize all dates to "Month DD, YYYY" format, add cross-references between planning documents
- **Result**: Achieved 10/10 (100%) documentation consistency

## Product Requirement Documents

- **Problem**: PRD folder missing from docs
- **Solution**: Recreated docs/prds with PRD-001..007 (module-level product requirements) and added to 000_DOCUMENT_INDEX with dependency links
- **Result**: PRDs visible in docs/prds and referenced in catalog/dependency matrix

## API Verification Status

- **Problem**: Inconsistent terminology for API verification status
- **Solution**: Standardize to "verification pending" for unverified APIs, "verified active" with date for verified
- **Avoid**: Don't use "requires verification" - use "verification pending" instead

## Requirements Count Standardization

- **Problem**: Diagram documentation referenced "60 comprehensive requirements" or "48 requirements" inconsistently
- **Solution**: Standardized all documentation to reference "59 requirements" consistently
- **Files Updated**: data-flow-diagram.md, decision-tree-flow-diagrams.md, entity-relationship-diagram.md, system-process-flow-diagrams.md, user-workflow-diagrams.md
- **Result**: All diagram documentation now aligned with main system documentation
