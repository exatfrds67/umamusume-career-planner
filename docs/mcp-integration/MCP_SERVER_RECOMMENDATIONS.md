# MCP Server Tools Configuration Guide

## Table of Contents

- [Important: 50 Tool Limit](#important-50-tool-limit)
- [Configuration Strategy](#configuration-strategy)
- [User Config](#user-config-kirosettingsmcpjson)
- [Workspace Config](#workspace-config-kirosettingsmcpjson)
- [Complete Configuration Files](#complete-configuration-files)
- [Additional Recommended MCP Servers](#additional-recommended-mcp-servers-optional)
- [Installation Commands](#installation-commands)
- [Configuration Tips](#configuration-tips)
- [Summary](#summary)

---

## Important: 50 Tool Limit

Kiro has a limit of 50 MCP tools total. To avoid warnings, we split tools strategically:

- **User Config**: 25 tools (global development tools)
- **Workspace Config**: 25 tools (project-specific tools)

**Note**: All Power servers in the user config are disabled to stay under the 50-tool limit. Enable them individually only when needed for specific AWS/cloud tasks

---

## Prerequisites

Before configuring MCP servers, ensure you have:

- **Node.js** (v18+) - Required for npx-based servers
- **Python** (v3.8+) - Required for uvx-based servers (install via `pip install uv`)
- **PHP** (v8.1+) - Required for Laravel Boost
- **GitKraken Desktop** - Required for GitKraken MCP server

**Note**: All file paths in configuration examples (e.g., `C:\XAMPP\htdocs\umamusume-career-planner\`) are examples. Replace them with your actual project paths.

---

## Configuration Strategy

- **Workspace Config** (`.kiro/settings/mcp.json`): Project-specific tools (Laravel, database, memory)
- **User Config** (`~/.kiro/settings/mcp.json`): Global development tools (browser automation, testing, utilities)

---

## USER CONFIG (`~/.kiro/settings/mcp.json`)

### 25 Global Development Tools

#### Tools 1: Fetch (1 tool)

- `fetch` - Fetch web content

#### Tools 2-14: Chrome DevTools (13 tools)

- `navigate_page` - Navigate browser
- `take_snapshot` - Take DOM snapshot
- `click` - Click elements
- `fill` - Fill forms
- `evaluate_script` - Run JavaScript
- `list_console_messages` - Get console logs
- `list_network_requests` - Get network logs
- `take_screenshot` - Capture screenshots
- `list_pages` - List browser tabs
- `wait_for` - Wait for elements
- `new_page` - Open new tab
- `close_page` - Close tab
- `select_page` - Switch tabs

#### Tools 15-20: Playwright (6 tools)

- `browser_navigate` - Navigate pages
- `browser_click` - Click interactions
- `browser_snapshot` - Accessibility snapshot
- `browser_fill` - Fill inputs
- `browser_evaluate` - Execute JS
- `browser_take_screenshot` - Screenshots

#### Tools 21-24: GitKraken (4 tools)

- `get_pull_requests` - List PRs
- `create_pull_request` - Create PR
- `get_issues` - List issues
- `get_commits` - View commits

#### Tool 25: Sequential Thinking (1 tool)

- `sequential_thinking` - Step-by-step reasoning

### User Config Total: 25 tools

---

## WORKSPACE CONFIG (`.kiro/settings/mcp.json`)

### 25 Project-Specific Tools

#### Tools 1-13: Laravel Boost (13 tools)

- `application_info` - Get app info
- `database_schema` - Read DB schema
- `database_query` - Query database
- `list_routes` - List Laravel routes
- `search_docs` - Search Laravel docs
- `tinker` - Execute PHP code
- `list_artisan_commands` - List Artisan commands
- `get_config` - Get config values
- `browser_logs` - Read browser logs
- `last_error` - Get last error
- `read_log_entries` - Read log files
- `get_absolute_url` - Generate URLs
- `list_available_env_vars` - List env variables

#### Tools 14-22: Memory (9 tools)

- `create_entities` - Create knowledge entities
- `create_relations` - Create entity relations
- `add_observations` - Add observations
- `delete_entities` - Delete entities
- `delete_relations` - Delete relations
- `delete_observations` - Delete observations
- `read_graph` - Read knowledge graph
- `search_nodes` - Search knowledge nodes
- `open_nodes` - Open specific nodes

#### Tool 23: Filesystem (1 tool)

- `read_file` - Read project files

#### Tools 24-25: BrowserStack (2 tools)

- `accessibilityExpert` - A11y expert advice
- `startAccessibilityScan` - Run a11y scans

### Workspace Config Total: 25 tools

---

## Complete Configuration Files

### User Config (`C:\Users\exatf\.kiro\settings\mcp.json`)

```json
{
  "mcpServers": {
    "fetch": {
      "command": "uvx",
      "args": ["--native-tls", "mcp-server-fetch"],
      "disabled": false,
      "autoApprove": ["fetch"]
    },
    "chrome-devtools": {
      "command": "npx",
      "args": ["-y", "chrome-devtools-mcp@latest"],
      "disabled": false,
      "autoApprove": [
        "navigate_page",
        "take_snapshot",
        "click",
        "fill",
        "evaluate_script",
        "list_console_messages",
        "list_network_requests",
        "take_screenshot",
        "list_pages",
        "wait_for",
        "new_page",
        "close_page",
        "select_page"
      ]
    },
    "playwright": {
      "command": "npx",
      "args": ["-y", "@playwright/mcp@latest"],
      "disabled": false,
      "autoApprove": [
        "browser_navigate",
        "browser_click",
        "browser_snapshot",
        "browser_fill",
        "browser_evaluate",
        "browser_take_screenshot"
      ]
    },
    "gitkraken": {
      "command": "gk",
      "args": ["mcp"],
      "disabled": false,
      "autoApprove": [
        "get_pull_requests",
        "create_pull_request",
        "get_issues",
        "get_commits"
      ]
    },
    "sequential-thinking": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-sequential-thinking"],
      "disabled": false,
      "autoApprove": ["sequential_thinking"]
    }
  },
  "powers": {
    "mcpServers": {
      "power-strands-strands-agents": {
        "disabled": true
      },
      "power-aws-agentcore-agentcore-mcp-server": {
        "disabled": true
      },
      "power-aws-infrastructure-as-code-awslabs.aws-iac-mcp-server": {
        "disabled": true
      },
      "power-cloud-architect-awspricing": {
        "disabled": true
      },
      "power-cloud-architect-awsknowledge": {
        "disabled": true
      },
      "power-cloud-architect-awsapi": {
        "disabled": true
      },
      "power-cloud-architect-context7": {
        "disabled": true
      },
      "power-cloud-architect-fetch": {
        "disabled": true
      }
    }
  }
}
```text

**Note**: All Power servers are disabled. Enable them individually from the MCP Servers view when needed for AWS/cloud development tasks.

### Workspace Config (`.kiro/settings/mcp.json`)

```json
{
  "mcpServers": {
    "laravel-boost": {
      "command": "php",
      "args": [
        "C:\\XAMPP\\htdocs\\umamusume-career-planner\\artisan",
        "boost:mcp"
      ],
      "disabled": false,
      "autoApprove": [
        "application_info",
        "database_schema",
        "database_query",
        "list_routes",
        "search_docs",
        "tinker",
        "list_artisan_commands",
        "get_config",
        "browser_logs",
        "last_error",
        "read_log_entries",
        "get_absolute_url",
        "list_available_env_vars"
      ]
    },
    "memory": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-memory"],
      "env": {
        "MEMORY_FILE_PATH": "C:\\XAMPP\\htdocs\\umamusume-career-planner\\memory.jsonl"
      },
      "disabled": false,
      "autoApprove": [
        "create_entities",
        "create_relations",
        "add_observations",
        "delete_entities",
        "delete_relations",
        "delete_observations",
        "read_graph",
        "search_nodes",
        "open_nodes"
      ]
    },
    "filesystem": {
      "command": "npx",
      "args": [
        "-y",
        "@modelcontextprotocol/server-filesystem",
        "C:\\XAMPP\\htdocs\\umamusume-career-planner"
      ],
      "disabled": false,
      "autoApprove": ["read_file"]
    },
    "browserstack": {
      "command": "npx",
      "args": ["-y", "@browserstack/mcp-server"],
      "env": {
        "BROWSERSTACK_ACCESS_KEY": "$env:BROWSERSTACK_ACCESS_KEY",
        "BROWSERSTACK_USERNAME": "$env:BROWSERSTACK_USERNAME"
      },
      "disabled": false,
      "autoApprove": [
        "accessibilityExpert",
        "startAccessibilityScan"
      ]
    }
  }
}
```

---

## Additional Recommended MCP Servers (Optional)

### Development & Testing

- **Puppeteer** - Headless browser automation
- **Selenium** - Cross-browser testing
- **Cypress** - E2E testing framework

### Database & Data

- **PostgreSQL** - Postgres database operations
- **MySQL** - MySQL database operations
- **SQLite** - SQLite database operations
- **MongoDB** - MongoDB operations
- **Redis** - Redis cache operations

### API & Integration

- **GitHub** - GitHub API operations
- **GitLab** - GitLab API operations
- **Slack** - Slack messaging
- **Discord** - Discord bot operations
- **Notion** - Notion workspace management

### AI & ML

- **OpenAI** - GPT model integration
- **Anthropic** - Claude model integration
- **Hugging Face** - ML model access
- **Replicate** - AI model hosting
- **EverArt** - AI image generation

### Web Scraping & Content

- **Firecrawl** - Advanced web scraping
- **Brave Search** - Web search API
- **Google Search** - Google search integration
- **Exa** - Semantic search

### Cloud & Infrastructure

- **AWS** - AWS service management
- **Azure** - Azure operations
- **Google Cloud** - GCP operations
- **Docker** - Container management
- **Kubernetes** - K8s cluster management

### Documentation & Knowledge

- **Context7** - Documentation search
- **Obsidian** - Note management
- **Confluence** - Wiki integration

### Translation & Localization

- **DeepL** - Translation service
- **Google Translate** - Translation API

### Design & Assets

- **Figma** - Design file access
- **Cloudinary** - Image management
- **Unsplash** - Stock photos

### Communication

- **Email** - Email sending
- **SMS** - SMS messaging
- **Twilio** - Communication APIs

### Analytics & Monitoring

- **Google Analytics** - Analytics data
- **Sentry** - Error tracking
- **DataDog** - Monitoring

### File & Storage

- **S3** - AWS S3 operations
- **Dropbox** - File storage
- **Google Drive** - Drive operations

---

## Installation Commands

### For npx-based servers

```bash
# No installation needed - runs on demand
```text

### For uvx-based servers

```bash
# Install uv first
pip install uv
# or
brew install uv
```

### For GitKraken

```bash
# Install GitKraken Desktop
# https://www.gitkraken.com/download
```text

---

## Troubleshooting

### MCP Server Not Connecting

1. **Check Prerequisites** - Ensure Node.js, Python, or PHP is installed
2. **Verify Paths** - Confirm all file paths in config are correct
3. **Test Command** - Run the command manually in terminal to see errors
4. **Check Logs** - Look for error messages in Kiro's MCP server panel

### Tool Not Appearing

1. **Restart Kiro** - MCP servers need restart after config changes
2. **Check autoApprove** - Tool name must match exactly (case-sensitive)
3. **Verify Tool Limit** - Ensure you haven't exceeded 50 total tools

### Performance Issues

1. **Disable Unused Servers** - Set `"disabled": true` for servers you don't need
2. **Reduce autoApprove List** - Only auto-approve frequently used tools
3. **Check Server Health** - Some servers may be slow or unresponsive

### Common Errors

- **"Command not found"** - Install the required runtime (Node.js, Python, PHP)
- **"Path not found"** - Update file paths to match your system
- **"Too many tools"** - Remove or disable servers to stay under 50-tool limit

---

## Configuration Tips

1. **Auto-approve carefully** - Only auto-approve tools you trust completely. Auto-approved tools can execute without confirmation, which may pose security risks if misconfigured.
2. **Use environment variables** - Store API keys in `.env` files, never hardcode them in config
3. **Disable unused servers** - Set `"disabled": true` to keep config but disable
4. **Test incrementally** - Enable one server at a time to verify functionality
5. **Monitor performance** - Too many active servers can slow down the IDE
6. **Review permissions** - Understand what each tool can access before enabling it

---

## Summary

### Total: 50 tools exactly (25 + 25)

This configuration keeps you under the 50-tool limit while providing:

- Essential Laravel development tools (workspace)
- Browser automation and testing (user)
- Git operations (user)
- Knowledge management (workspace)
- Accessibility testing (workspace)

To add more tools later, you'll need to remove some existing ones or disable servers you don't use frequently.

### Current Configuration Breakdown

#### Workspace (25 tools)

- Laravel Boost: 13 Laravel-specific tools
- Memory: 9 knowledge graph tools
- Filesystem: 1 file reading tool
- BrowserStack: 2 accessibility testing tools

#### User (25 tools)

- Fetch: 1 web content tool
- Chrome DevTools: 13 browser automation tools
- Playwright: 6 advanced browser tools
- GitKraken: 4 git operation tools
- Sequential Thinking: 1 reasoning tool
