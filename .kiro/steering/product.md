# Product Overview

## Umamusume Pretty Derby Career Planner

### Executive Summary

The **Umamusume Pretty Derby Career Planner** is a comprehensive local-first web application designed to optimize gameplay in *Umamusume: Pretty Derby*. The system consolidates five legacy tracking applications into a unified platform, providing players with data-driven decision-making tools for character progression across 60-70 turn career runs.

### Mission Statement

**Empower players to achieve consistent A+ grade character ratings through intelligent planning, real-time recommendations, and comprehensive analytics—all while maintaining complete data privacy through local-first storage.**

### Core Purpose

Replace manual tracking methods with intelligent AI-powered optimization by providing:

- Character state management with inherited factor bonuses
- Turn-by-turn training predictions and recommendations
- Race strategy analysis with weather and performance modeling
- Skill acquisition planning with SP cost optimization
- Support card deck configuration with synergy analysis
- Career analytics and pattern recognition

### Key Features (v2.0.0)

#### 1. Dual Storage Architecture

- **Local Mode**: Browser-based (localStorage) for anonymous users
- **Account Mode**: Database-backed (MySQL/MariaDB/SQLite) for authenticated users
- **Seamless Conversion**: Local → Account migration with duplicate detection
- **Full Offline Support**: Complete functionality without internet connection

#### 2. AI-Powered Advisory

- **Hybrid Infrastructure**: Local Ollama + AWS Bedrock fallback
- **Training Optimization**: Turn-by-turn stat gain predictions
- **Race Strategy**: Weather impact analysis and running style optimization
- **Skill Recommendations**: SP cost minimization with evolution paths
- **Cost-Efficient**: ~$0.003 per inference with Bedrock routing

#### 3. Character Management

- **Stat Tracking**: Speed/Stamina/Power/Guts/Wit (0-1200 range)
- **Aptitude Grades**: Distance, surface, and running style ratings (G-SS)
- **Factor Inheritance**: Multi-generational stat and aptitude bonuses
- **Growth Rates**: Configurable per-stat training effectiveness multipliers
- **Condition System**: Positive/negative status effects with duration tracking

#### 4. Training Optimization

- **Facility Analysis**: 5 facilities with synergy-based bonuses
- **Prediction Engine**: Stat gains, bond increases, skill hints
- **Support Card Integration**: 6-card deck with specialization bonuses
- **Ranking Algorithm**: Goal-aligned training recommendations
- **Accuracy Tracking**: Prediction vs. actual comparison for model improvement

#### 5. Race Strategy System

- **Requirements Analysis**: Distance, competition level, terrain evaluation
- **Weather Modeling**: Track condition modifiers (rainy -5%, snowy -15%)
- **Running Style Optimizer**: 4 styles with stat/aptitude scoring
- **Performance Prediction**: Placement probability distribution
- **Pre-Race Checklist**: Stat gaps, skill requirements, readiness assessment

#### 6. Skill Management

- **Catalog**: 150+ skills with evolution chains
- **Hint System**: 20-40% SP cost reduction per hint (max 2 hints)
- **Evolution Paths**: Normal → Rare transitions (e.g., Go with the Flow → Lane Legerdemain)
- **SP Planning**: Real-time balance calculation
- **Auto-Recommendations**: AI-suggested acquisition sequences

#### 7. Support Card System

- **Database**: 200+ card meta rankings (SS, S, A, B tiers)
- **Deck Builder**: 6-slot validation with synergy scoring
- **Bond Tracking**: Levels 1-5 with skill unlock thresholds
- **Limit Breaks**: Star multipliers (★-★★★★★)
- **Meta Analysis**: Automatic tier updates from community sources

#### 8. Data Management

- **Import/Export**: JSON/CSV/Excel/Markdown formats
- **Format Detection**: Automatic schema versioning and migration
- **Conflict Resolution**: Skip/Overwrite/Merge/Rename strategies
- **Backup System**: Automatic snapshots with 30-day recovery window
- **OCR Support**: Screenshot-to-data extraction (Japanese language)

#### 9. Analytics & Reporting

- **Career Summary**: Grade progression, fan count, trait acquisition
- **Comparative Analysis**: Prediction accuracy vs. actual outcomes
- **Trend Detection**: Stat growth patterns and training effectiveness
- **Export Reports**: PDF, CSV, or web-shareable formats

#### 10. Accessibility & UX

- **WCAG 2.2 AA Compliance**: Color contrast, keyboard navigation, screen readers
- **Dark Mode**: System preference + manual toggle with persistence
- **Responsive Design**: 320px → 2560px viewport support
- **Progressive Web App**: Offline-first with Service Worker
- **Multi-language**: English UI + Japanese skill names

### Technology Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| **Backend** | Laravel | 12+ (PHP 8.4+) |
| **Frontend Reactivity** | Livewire | 3 |
| **Client Interactivity** | Alpine.js | 3.x |
| **Styling** | Tailwind CSS | v4 |
| **Build Tool** | Vite | 7+ |
| **Database** | MySQL/MariaDB/SQLite | 8.0+ |
| **Cache** | Redis | (optional) |
| **Testing** | Pest | 4.0+ |
| **AI Services** | Ollama + AWS Bedrock | - |
| **OCR** | Tesseract | 5+ |

### Architecture Principles

1. **Local-First**: Data lives locally by default; cloud is optional
2. **Privacy-Preserving**: Zero transmission without explicit consent
3. **Hybrid AI**: Local models primary; cloud fallback for complex reasoning
4. **Accessibility-First**: WCAG 2.2 AA built-in, not bolted-on
5. **Performance-Optimized**: Core Web Vitals <2s, FCP <1.5s
6. **Maintainable**: PSR-12 coding, >80% test coverage, clear separation of concerns

### Target Users

- **Intermediate to Advanced Players**: Seeking consistent A+ grade achievements
- **Data-Driven Strategists**: Want computational optimization + human control
- **Accessibility-Conscious**: Require keyboard navigation and screen reader support
- **Privacy-Focused**: Prefer local data storage with optional cloud sync
- **Global Community**: Support for Japanese terminology and character names

### Success Metrics (Post-Launch)

| Metric | Target | Measurement |
|--------|--------|-------------|
| User Adoption | 1000+ MAU | Analytics |
| Migration Success | >95% import success | Error tracking |
| Accessibility | 100% WCAG AA | Automated testing |
| Performance | <2s load time | Lighthouse monitoring |
| Uptime (Account Mode) | 99% | APM dashboard |
| Prediction Accuracy | ≥95% | Training vs. actual data |

### Competitive Advantages

1. **Unique Dual-Mode Storage**: Only planner supporting both offline + cloud
2. **Advanced Inheritance Calculations**: Multi-generational factor modeling
3. **Hybrid AI Pipeline**: Cost-optimized with local fallback
4. **Comprehensive Analytics**: Prediction accuracy tracking and model improvement
5. **Community-Driven**: Support card meta synced from umapyoi.net

### Roadmap

#### v2.0.0 (Current)

- ✅ Dual storage modes with seamless conversion
- ✅ Hybrid AI advisory system
- ✅ OCR screenshot processing
- ✅ WCAG 2.2 AA accessibility
- ✅ Import/export with conflict resolution

#### v2.1.0 (Q2 2026)

- Support community features (optional sharing)
- Advanced prediction model retraining
- Mobile app (PWA installable)
- Batch simulation for scenario testing

#### v3.0.0 (Q4 2026)

- Real-time multiplayer planning (beta)
- Championship League rankings
- Custom AI model fine-tuning
- Game event integration

### Business Model

**Freemium Web Application**

- **Free Tier**: Unlimited local storage, core features, basic AI recommendations
- **Premium Tier** (Optional Q3 2026): Advanced AI insights, priority support, analytics exports
- **Enterprise**: Custom deployment, organization management

---

## Document Information

**Document Type**: Product Overview  
**Version**: 2.0.0  
**Date**: January 23, 2026  
**Status**: Current  
**Owner**: Development Team  
**Related**: [SDP](./tech.md), [Architecture](./structure.md)
