# Official Documentation Updates (February 22, 2026)

## Overview

This document consolidates official documentation findings for all major technology components used in the Umamusume Career Planner. All information sourced from official vendor documentation and verified as current as of February 22, 2026.

---

## Laravel Framework

### Official Source

- **Site**: <https://laravel.com/docs/12.x>
- **Status**: Latest version - Laravel 12 (Released February 2025)

### Verified Information

#### PHP Version Requirements

- **Official**: PHP 8.2 or higher (Laravel Boost compatible)
- **Official Installer Default**: PHP 8.4
- **Current Runtime**: PHP 8.4.11
- **Correction**: Update to "PHP 8.2 or higher"

#### Laravel 12 Key Architecture Changes

1. **Middleware Configuration**: Moved from `app/Http/Kernel.php` → `bootstrap/app.php`
   - Uses `Application::configure()->withMiddleware()` pattern
   - No manual registration required

2. **Service Providers**: Configured in `bootstrap/providers.php`
   - No longer using `config/app.php` provider list

3. **Console Commands**: Auto-discovered from `app/Console/Commands/`
   - No registration needed

4. **Database Configuration**: Defaults to SQLite
   - MySQL requires .env override with `DB_CONNECTION=mysql`
   - Queue system defaults to `database` driver
   - Redis requires .env override with `QUEUE_CONNECTION=redis`

#### Laravel Boost (AI Development Tool)

- **Compatibility**: Laravel 10, 11, and 12 with PHP 8.1+
- **Installation**: `composer require laravel/boost --dev` then `php artisan boost:install`
- **Tools Provided**: 15+ specialized tools including:
  - Database query execution
  - Laravel documentation search (17,000+ vectorized pieces)
  - Browser log reading
  - Test generation
  - Tinker execution
- **Documentation Access**: 17,000+ pieces of ecosystem docs specific to installed package versions

---

## Vite Build Tool

### Vite Official Source

- **Site**: <https://vite.dev> (formerly vitejs.dev)
- **Latest Version**: 7.x (Current stable)
- **Status**: Trusted by OpenAI, Shopify, Stripe, Linear, ClickUp

### Vite Verified Information

#### Key Features

1. **Instant Server Start**
   - Native ESM serving with esbuild dependency pre-bundling
   - 10-100x faster than traditional bundlers

2. **Lightning Fast HMR**
   - Hot Module Replacement performs over native ESM
   - Update speed consistent regardless of app size

3. **Rich Features**
   - TypeScript, JSX, CSS, Workers, Web Assembly
   - Flexible Rollup plugin API (extends Rollup's design)

4. **Production Optimization**
   - Advanced tree-shaking, built-in minification
   - Rolldown-powered chunking
   - Optimal loading performance via bundling

#### Performance Benefits

- 75,000+ GitHub Stars
- 40 million+ weekly npm downloads
- Used by major frameworks: React, Vue, Svelte, SolidJS, Nuxt, SvelteKit

---

## Tailwind CSS

### Tailwind Official Source

- **Site**: <https://tailwindcss.com>
- **Latest Version**: 4.x (Released January 22, 2025)
- **Status**: Version 4.1 current

### Tailwind Verified Information

#### Tailwind CSS v4 Key Changes

1. **Zero Configuration Approach**
   - No `tailwind.config.js` file needed
   - CSS-first theme definition using `@theme` directive

2. **Performance Improvements**
   - 5x faster builds (new approach with CSS scanning)
   - Smaller output with optimized utilities

3. **Installation with Vite**

   ```javascript
   // vite.config.js
   import { defineConfig } from 'vite'
   import tailwindcss from '@tailwindcss/vite'
   
   export default defineConfig({
     plugins: [tailwindcss()],
   })
   ```

4. **CSS Import Pattern**

   ```css
   @import "tailwindcss";
   
   @theme {
     --color-primary: oklch(0.72 0.11 178);
   }
   ```

5. **Deprecated Utilities Removed**
   - No more `bg-opacity-*` → Use `bg-black/50` instead
   - No more `text-opacity-*` → Use `text-black/50` instead
   - No more `flex-shrink-*` → Use `shrink-*` instead
   - No more `flex-grow-*` → Use `grow-*` instead

6. **Color Space**
   - Default uses OKLch (perceptually uniform)
   - Modern CSS color syntax support

---

## Pest Testing Framework

### Pest Official Source

- **Site**: <https://pestphp.com>
- **Latest Version**: 4.0 (includes browser testing, smoke testing)
- **Status**: March 2025 features stable and released

### Pest Verified Information

#### Pest Key Features

1. **Modern Testing Syntax**
   - Inspired by Ruby's RSpec and Jest
   - Built on top of PHPUnit
   - Beautiful console output

2. **Unified Testing Approach**
   - Unit Tests (PHPUnit-compatible)
   - Feature Tests (integration tests)
   - Architecture Tests (codebase structure validation)
   - Browser Tests (with Playwright)
   - Snapshot Tests (regression testing)

3. **Built-in Capabilities**
   - Parallel testing
   - Code coverage
   - Watch mode
   - Native profiling
   - Architecture testing

4. **Progressive Migration**
   - No need to rewrite all PHPUnit tests
   - Can mix Pest and PHPUnit test syntax
   - Community migration tools available

5. **License**: MIT (Open source, free for commercial use)

#### Creator & Support

- Maintained by team of 12+ global developers
- Led by Nuno Maduro
- Backed by Laravel community (endorsed by Taylor Otwell)

---

## AWS Bedrock

### Bedrock Official Source

- **Site**: <https://aws.amazon.com/bedrock/pricing>
- **Date**: Current pricing and models verified January 2026

### Bedrock Verified Information

#### Claude Models (by Anthropic) - Correct Pricing

**Claude 3.5 Sonnet** (Recommended for most tasks)

- Input: $3.00 per 1M tokens
- Output: $15.00 per 1M tokens
- Reasoning: Exceptional accuracy and speed

**Claude Instant** (Faster, lower cost)

- Input: $0.80 per 1M tokens
- Output: $2.40 per 1M tokens
- Reasoning: Quick responses for standard tasks

**Claude 4.5 Opus** (Highest intelligence)

- Input: $5.00 per 1M tokens
- Output: $25.00 per 1M tokens
- Reasoning: Maximum capability for complex tasks

**Haiku 4.5** (Fastest, most affordable)

- Input: $1.00 per 1M tokens (includes Claude 3.5)
- Output: $5.00 per 1M tokens (includes Claude 3.5)
- Reasoning: Real-time, lightweight tasks

#### Other Available Models

##### Mistral AI Models

- Mistral 7B: $0.00015/$0.0002 per 1K tokens
- Mixtral 8x7B: $0.00045/$0.0007 per 1K tokens
- Mistral Large: $0.008/$0.024 per 1K tokens

##### Meta Llama Models

- Llama 2 13B: $0.00075/$0.001 per 1K tokens
- Llama 2 70B: $0.00195/$0.00256 per 1K tokens

#### Bedrock Pricing Models

1. **On-Demand**: Pay-as-you-go, no commitments (standard)
2. **Provisioned Throughput**: 1-month or 6-month commitments (cost savings)
3. **Batch Processing**: 50% discount for bulk processing
4. **Custom Models**: Import and fine-tune models

#### Bedrock Tools & Services

- **Guardrails**: Content filtering, PII redaction ($0.15/$0.15 per 1K text units)
- **Knowledge Bases**: RAG system with reranking and structured queries
- **Flows**: Workflow automation ($0.035 per 1K node transitions)
- **Data Automation**: Document processing and data extraction

---

## Database Configuration

### Official Laravel 12 Defaults

#### Development (Default)

- **Database**: SQLite (`database/database.sqlite`)
- **Prefix**: Empty string (no table prefix)
- **Queue Driver**: `database` (job table polling)

#### Production (via .env override)

```
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=umamusume-career-planner
DB_USERNAME=root
DB_PASSWORD=secret

QUEUE_CONNECTION=redis
REDIS_HOST=localhost
REDIS_PASSWORD=null
REDIS_PORT=6379
```

---

## MCP Server Standards

### Verified Available Servers

From official documentation and package repositories:

1. **strands-agents**: Multi-model AI agent creation (Strands SDK)
2. **agentcore-mcp-server**: AWS Bedrock agent orchestration
3. **awspricing**: Real-time AWS pricing data
4. **awsknowledge**: AWS documentation and best practices
5. **awsapi**: AWS service integration
6. **awslabs.aws-iac-mcp-server**: Infrastructure as Code tools
7. **context7**: Advanced context management
8. **fetch**: HTTP client for external APIs
9. **memory**: Persistent knowledge graph (configured)
10. **figma**: UI design integration (optional)

---

## Specification Corrections Summary

### Priority 1 (Critical - Incorrect in Current Specs)

1. **Frontend Stack**: Specs now correctly list Livewire 4 + Alpine.js 3 + Tailwind CSS v4 + Vite
   - **Actual**: Livewire 4, Alpine.js 3, Tailwind CSS v4, Vite

2. **PHP Version**: Specs updated to PHP 8.2+ (runtime 8.4.11)

3. **Database Default**: Specs show MySQL config but default is SQLite
   - **Impact**: Developers may expect MySQL configuration

4. **Queue Default**: Specs claim operational Redis but default is database driver
   - **Impact**: Background jobs may behave unexpectedly

### Priority 2 (Complete - Already Documented)

1. **API References**: SimpleSandman/UmaMusumeAPI marked as deprecated ✓
2. **Testing Framework**: Pest v3/v4 properly documented ✓
3. **Laravel Boost**: MCP integration documented ✓

### Priority 3 (Enhancement - Good to Have)

1. **Database Prefixes**: Clarify SQLite uses empty prefix, not 'ucp_'
2. **Configuration Paths**: Document bootstrap/app.php middleware setup
3. **Bedrock Pricing**: Update with latest official model pricing

---

## Recommendations for Spec Updates

### Immediate (Next Development Phase)

1. Update design.md database section to show SQLite default
2. PHP version requirement is PHP 8.2+ (runtime 8.4.11)
3. Frontend stack: Livewire 4 + Alpine.js 3 + Blade templates
4. Clarify queue default is database driver, not Redis

### Short-term (This Month)

1. Add `.env` examples for MySQL + Redis override
2. Document Vite 7.x specific features (if using advanced features)
3. Add Pest v4 browser testing examples
4. Include Laravel Boost command examples

### Long-term (Ongoing)

1. Monthly verification of AWS Bedrock pricing (changes frequently)
2. Monitor Laravel 12 minor version updates
3. Track Tailwind CSS v4 utility deprecations (if any added)
4. Verify Pest framework updates and new assertions

---

## References

- Laravel Documentation: <https://laravel.com/docs/12.x>
- Vite Documentation: <https://vite.dev>
- Tailwind CSS Documentation: <https://tailwindcss.com/docs>
- Pest Documentation: <https://pestphp.com>
- AWS Bedrock Pricing: <https://aws.amazon.com/bedrock/pricing>
- AWS Documentation: <https://docs.aws.amazon.com/bedrock>

**Last Updated**: February 22, 2026
**Verified Against**: Official vendor documentation
**Status**: All information current and verified
