# Kiro Markdown Linting Hooks

This directory contains automated markdown linting hooks that follow the official Kiro Hooks format. These hooks provide comprehensive markdown quality assurance through intelligent agent instructions.

## 🎯 Overview

The markdown linting system consists of four specialized hooks, each designed for different use cases:

1. **Real-time Markdown Linter** - Fast feedback on file saves
2. **Documentation Quality Checker** - Comprehensive docs analysis
3. **Parallel Lint Workflow** - Advanced comprehensive linting
4. **Manual Markdown Audit** - On-demand comprehensive audits

## 🚀 Quick Start

### Prerequisites

- Kiro IDE with hooks system enabled
- Markdown files in your project

### Basic Usage

```bash
# Real-time linter runs automatically on file save

# Check documentation quality (runs automatically on doc saves)

# Execute comprehensive lint workflow
kiro hook run parallel-lint-workflow

# Perform comprehensive audit
kiro hook run manual-markdown-audit
```

## 📋 Hook Details

### 1. Real-time Markdown Linter

**Purpose**: Provides immediate feedback on markdown files as you save them.

**Triggers**:

- File save events for `*.md` and `*.markdown` files
- Excludes: `node_modules/`, `.git/`, `vendor/`, `storage/`

**Features**:

- ⚡ Fast analysis focused on critical issues
- 🎯 Syntax and structure validation
- 🔗 Internal link checking
- 📝 Actionable fixes with line numbers

**Best For**:

- Active development and writing
- Immediate error detection
- Quick quality checks

### 2. Documentation Quality Checker

**Purpose**: Comprehensive quality analysis for documentation directories.

**Triggers**:

- File saves in `docs/`, `documentation/` directories
- Changes to `README.md`, `CHANGELOG.md`, etc.

**Analysis Areas**:

- **Structure**: Header hierarchy, TOC validation, cross-references
- **Content**: Code examples, technical accuracy, completeness
- **Style**: Style guide compliance, formatting consistency

**Features**:

- 🏗️ Multi-dimensional analysis
- 📊 Comprehensive quality assessment
- 🎨 Style and consistency enforcement
- 📈 Detailed reporting with recommendations

**Best For**:

- Documentation maintenance
- Quality assurance processes
- Team collaboration standards

### 3. Parallel Lint Workflow

**Purpose**: Advanced comprehensive markdown linting across the project.

**Triggers**:

- Manual execution
- Git commits to main branches (`main`, `master`, `develop`, `release/*`)

**Analysis Coverage**:

- **Syntax Validation**: Comprehensive syntax checking
- **Link Analysis**: Internal/external link validation
- **Accessibility**: WCAG compliance checking
- **Style Consistency**: Cross-file style enforcement
- **Content Analysis**: Quality and completeness assessment

**Features**:

- 🔄 Comprehensive project analysis
- 📊 Executive summary with metrics
- 🚨 Prioritized issue reporting
- 🔧 Automated fix suggestions
- 💾 Detailed quality metrics

**Best For**:

- Pre-commit quality gates
- Comprehensive project analysis
- Team quality standards
- CI/CD integration

### 4. Manual Markdown Audit

**Purpose**: On-demand comprehensive markdown audit with detailed analysis.

**Triggers**:

- Manual execution with parameters:
  - `scope`: 'all', 'docs', 'project', or specific path
  - `depth`: 'quick', 'standard', 'comprehensive'
  - `format`: 'console', 'html', 'json', 'all'

**Comprehensive Analysis**:

- **Discovery**: File cataloging and dependency mapping
- **Syntax**: Multi-parser validation and spec compliance
- **Content Intelligence**: Quality metrics and gap analysis
- **Accessibility**: Full WCAG 2.1 AA compliance
- **Performance**: Rendering speed and optimization
- **Security**: XSS vectors and safety evaluation

**Features**:

- 🎯 Health scoring (0-100)
- 📈 Quality trend analysis
- 🔒 Security vulnerability assessment
- ⚡ Performance optimization recommendations
- 📊 Strategic improvement roadmap
- 📋 Detailed action planning

**Best For**:

- Quarterly quality reviews
- Security audits
- Performance optimization
- Strategic content planning

## ⚙️ Configuration

### Hook Structure

Each hook follows the standard Kiro format:

```json
{
  "name": "Hook Name",
  "description": "Hook description",
  "triggers": [
    {
      "type": "file_save",
      "patterns": ["**/*.md"],
      "exclude_patterns": ["node_modules/**"]
    }
  ],
  "instructions": "Detailed agent instructions..."
}
```

### Customizing Instructions

Edit the `instructions` field in each `.kiro.hook` file to customize behavior:

```json
{
  "instructions": "You are a markdown specialist focusing on:\n1. Laravel documentation standards\n2. API documentation completeness\n3. Code example validation"
}
```

## 📊 Expected Output

### Real-time Linter Output

```
✅ PASSED: No issues found in README.md
⚠️ WARNINGS:
  - Line 23: Consider adding alt text to image
❌ ERRORS:
  - Line 45: Broken internal link to ./docs/guide.md
```

### Comprehensive Reports

```markdown
# 📋 Markdown Quality Report

## 📊 Executive Summary
- Files analyzed: 45
- Health Score: 87/100
- Critical Issues: 3
- Estimated fix time: 2.5 hours

## 🚨 Critical Issues
1. Broken internal links in README.md (lines 23, 45)
2. Missing alt text for images in docs/guide.md

## 🔧 Automated Fixes Available
- Fix list marker consistency (8 files)
- Standardize header formatting (12 files)
```

## 🔧 Troubleshooting

### Common Issues

**Hook not triggering**:

- Check that the hook is properly formatted JSON
- Verify file patterns match your files
- Ensure Kiro hooks are enabled

**Performance issues**:

- Reduce scope for large projects
- Focus on specific directories
- Use quick analysis for frequent checks

### Testing Hooks

Test hook configuration:

```bash
# Test file patterns
echo "*.md files in project:"
find . -name "*.md" -not -path "./node_modules/*"

# Manual execution
kiro hook run manual-markdown-audit --scope=docs --depth=quick
```

## 🚀 Integration Examples

### VS Code Tasks

```json
{
  "version": "2.0.0",
  "tasks": [
    {
      "label": "Lint Markdown",
      "type": "shell",
      "command": "kiro hook run parallel-lint-workflow",
      "group": "build"
    }
  ]
}
```

### GitHub Actions

```yaml
name: Markdown Quality Check
on: [push, pull_request]

jobs:
  markdown-lint:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Run Markdown Linting
        run: kiro hook run parallel-lint-workflow
```

## 📈 Best Practices

### Development Workflow

1. **Write**: Real-time linter provides immediate feedback
2. **Review**: Docs quality checker validates documentation changes
3. **Release**: Parallel lint workflow ensures quality before commits
4. **Audit**: Comprehensive audits for periodic quality reviews

### Quality Standards

- Maintain syntax compliance above 95%
- Keep link health above 98%
- Ensure accessibility score above 85%
- Target performance score above 80%

## 📚 Resources

- [Kiro Hooks Documentation](https://aicodingtools.blog/en/kiro/kiro-hooks-guide)
- [CommonMark Specification](https://commonmark.org/)
- [GitHub Flavored Markdown](https://github.github.com/gfm/)
- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)

---

*Kiro Markdown Linting Hooks v1.0.0*
