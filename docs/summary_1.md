# Umamusume Pretty Derby Career Planner - Comprehensive Project Summary

## Project Overview

Creating a sophisticated local-first web application to optimize Umamusume Pretty Derby gameplay, replacing manual Google Docs tracking with an intelligent system combining proven workflow, hybrid AI guidance, and comprehensive external data integration.

## Background Context

- **Current System**: Manual Google Docs forms with multiple iterations showing proven optimization workflow
- **Goal**: Achieve A+ grade character ratings consistently through data-driven decision making
- **User**: Single user (developer), local-only application with optional cloud AI integration
- **Experience**: Laravel framework expertise, existing Ollama setup, AWS Bedrock access
- **Existing Projects**: Multiple discontinued implementations in C:\XAMPP\htdocs\ (uma-musume-planner-laravel, uma_musume_race_planner, umamusume-tracker, uma-run-tracker, uma-tracker-form, uma-tracker)

## Verified Technical Stack (Updated January 10, 2026)

- **Framework**: Laravel 12 (Released February 24, 2025) with TypeScript support and Tailwind CSS integration
- **Frontend**: Modern JavaScript (ES2024+) with Tailwind CSS v4 (Released January 22, 2025) - 5x faster builds, zero configuration
- **AI System**: Hybrid architecture - Ollama local models (via cloudstudio/ollama-laravel) primary, AWS Bedrock fallback
  - **Local Models**: Llama 3.3, Mistral, Qwen via cloudstudio/ollama-laravel package (verified Laravel 11+ compatible)
  - **Cloud Models**: AWS Bedrock Claude 4.5 (Opus $5/$25, Sonnet $3/$15, Haiku $1/$5 per 1M tokens), Nova 2 (Lite $0.00125 per 1K tokens, Pro Preview)
- **Data Sources**: umapyoi.net (verified active), UmamusumeDB.com (requires verification), OCR screenshot analysis
- **Database**: MySQL with Redis (WSL) for caching, sessions, and queues
- **Deployment**: Local XAMPP environment with cloud API integration

# Umamusume Pretty Derby Career Planner - Comprehensive Project Summary

## Project Overview

Creating a sophisticated local-first web application to optimize Umamusume Pretty Derby gameplay, replacing manual Google Docs tracking with an intelligent system combining proven workflow, hybrid AI guidance, and comprehensive external data integration.

## Background Context

- **Current System**: Manual Google Docs forms with multiple iterations showing proven optimization workflow
- **Goal**: Achieve A+ grade character ratings consistently through data-driven decision making
- **User**: Single user (developer), local-only application with optional cloud AI integration
- **Experience**: Laravel framework expertise, existing Ollama setup, AWS Bedrock access
- **Existing Projects**: Multiple discontinued implementations in C:\XAMPP\htdocs\ (uma-musume-planner-laravel, uma_musume_race_planner, umamusume-tracker, uma-run-tracker, uma-tracker-form, uma-tracker)

## Verified Technical Stack (Updated January 10, 2026)

- **Framework**: Laravel 12 (Released February 24, 2025) with TypeScript support and Tailwind CSS integration
- **Frontend**: Modern JavaScript (ES2024+) with Tailwind CSS v4 (Released January 22, 2025) - 5x faster builds, zero configuration
- **AI System**: Hybrid architecture - Ollama local models (via cloudstudio/ollama-laravel) primary, AWS Bedrock fallback
  - **Local Models**: Llama 3.3, Mistral, Qwen via cloudstudio/ollama-laravel package (verified Laravel 11+ compatible)
  - **Cloud Models**: AWS Bedrock Claude 4.5 (Opus $5/$25, Sonnet $3/$15, Haiku $1/$5 per 1M tokens), Nova 2 (Lite $0.00125 per 1K tokens, Pro Preview)
- **Data Sources**: umapyoi.net (verified active), UmamusumeDB.com (requires verification), OCR screenshot analysis
- **Database**: MySQL with Redis (WSL) for caching, sessions, and queues
- **Deployment**: Local XAMPP environment with cloud API integration

## Comprehensive Game Mechanics Research

### Detailed Stat System

- **Speed** (0-1200, Priority ★★★★★): Top speed determination, critical for all race types
- **Stamina** (0-1200, Priority ★★★★): Duration at top speed, distance-specific requirements
- **Power** (0-1200, Priority ★★★): Acceleration rate, crucial for positioning
- **Guts** (0-1200, Priority ★): Final phase performance, endurance in difficult races
- **Wit** (0-1200, Priority ★★): Skill activation rate, positioning wit

### Distance-Specific Requirements

- **Sprint** (1000-1400m): 350 career / 500-600 PvP stamina minimum
- **Mile** (1401-1800m): 400 career / 600-700 PvP stamina minimum  
- **Medium** (1801-2400m): 500 career / 800-900 PvP stamina minimum
- **Long** (2401m+): 600 career / 900-1100 PvP stamina minimum

### Factor Inheritance System

- **Blue Stat Factors**: ★☆☆ = +5, ★★☆ = +12, ★★★ = +21 bonuses
- **Red Aptitude Factors**: 1★ = 1 grade up, then 3★ per additional grade
- **Green Unique Skill Factors**: Guaranteed from 3★ characters
- **White Normal Skill Factors**: From completed races and learned skills
- **Legacy Team**: 2 main parents + 4 grandparents maximum (6 total)

### Unity Cup Mechanics

- **Spirit Burst**: 4 training sessions with flame icons fill meter for large stat bonuses
- **Distance Teams**: Sprint/Mile/Medium/Long/Dirt with 1-3 racers each
- **Team Facilities**: Rank D-S determines facility levels 1-5 (1.0x to 2.0x multipliers)
- **Unity Training**: Participant bonuses (2 participants +2, 3 participants +3)

### Weather and Track Conditions

- **Weather Types**: Sunny, Cloudy, Rainy, Snowy
- **Track Conditions**: Firm (dry), Good, Soft, Heavy (wet)
- **Weather Skills**: "Wet Conditions ○/◎", "Firm Conditions ○"
- **Performance Impact**: Weather affects aptitude effectiveness and race strategy

### Skill System Details

- **Normal Skills**: 120-180 SP cost (e.g., "Go with the Flow" = 120 SP)
- **Rare Skills**: 180-240 SP cost (evolved versions of Normal skills)
- **Skill Evolution**: Normal → Rare replacement (e.g., "Go with the Flow" → "Lane Legerdemain")
- **Hint System**: 20% SP reduction per duplicate (40% maximum with 2+ duplicates)
- **Red "!" Indicators**: Guarantee skill hints during matching stat training

### Summer Camp System

- **Timing**: Early July, Late July, Early August, Late August (Classic and Senior years)
- **High Efficiency**: 4-turn periods with maximum training effectiveness
- **Energy Management**: Critical to maintain 70%+ energy for optimal gains
- **Strategic Planning**: Pre-camp preparation and post-camp recovery

### Friendship Training Mechanics

- **Rainbow Training**: Unlocked at 80%+ friendship with support cards
- **Participant Bonuses**: 2 participants = +2 bonus, 3 participants = +3 bonus
- **Timing Strategy**: Essential to achieve by first Summer Camp for maximum benefit
- **Support Card Synergy**: Multiple cards participating in same stat training

## Complete Requirements Specification (60 Requirements)

### Enhanced Core Functionality (1-17)

1. **Character State Management** - Detailed stat tracking with priorities and aptitudes
2. **Training Prediction Engine** - Scenario-specific URA/Unity Cup predictions  
3. **Race Preparation** - Weather conditions and strategy optimization
4. **Comprehensive Skill Management** - SP optimization with hint collection
5. **Career Progress Tracking** - Historical analysis and pattern recognition
6. **Support Card Configuration** - 6-card deck management with friendship tracking
7. **Legacy and Inheritance** - Factor inheritance with affinity compatibility
8. **Local Data Management** - Privacy-focused storage with backup/restore
9. **Turn-by-Turn Optimization** - Real-time decision guidance
10. **Character Aptitude Management** - Fixed talent ratings and growth bonuses
11. **Multi-Scenario Career Management** - URA Finale vs Unity Cup strategies
12. **Web Application Interface** - Responsive Laravel with accessibility compliance
13. **AI-Powered Advisory Chatbot** - Ollama → AWS fallback with conversation context
14. **External Data Integration** - Multiple API sources with fallback mechanisms
15. **Career Comparison Analysis** - Multi-run pattern identification
16. **Game-Integrated Goal Management** - Official mission tracking
17. **Technical Implementation Standards** - Laravel 12 best practices with testing

### Advanced Game Mechanics (18-38)

1. **Event Decision Database** - Comprehensive event choices with outcomes
2. **Training Facility Management** - Facility levels, mood, energy optimization
3. **Friendship Training Optimization** - Rainbow training timing strategies
4. **Race Strategy Optimization** - Running style and distance specialization
5. **Turn Economy Management** - 60-70 turn career phase optimization
6. **Data Import and Migration** - Google Docs format conversion
7. **Race Calendar System** - Complete Pre-OP through G1 scheduling
8. **Race Performance Analytics** - Performance tracking and improvement
9. **Advanced Skill Hint System** - SP cost reduction optimization
10. **Energy and Condition Management** - Training failure prevention
11. **Support Card Meta Optimization** - Meta-aware deck analysis
12. **Support Card Skill Provision** - Training interaction mechanics
13. **Red Exclamation Training** - Guaranteed hint acquisition system
14. **Skill Evolution Management** - Normal → Rare upgrade tracking
15. **SP Cost Reduction Engine** - Hint farming optimization
16. **Weather System Management** - Track condition impact analysis
17. **Training Failure Recovery** - Risk management and prevention
18. **Advanced Statistics** - Performance metrics and trend analysis
19. **Unity Cup Team Management** - Spirit Burst and team coordination
20. **Screenshot-Based Interaction** - OCR data capture with AI analysis
21. **Intelligent Game State Recognition** - Multi-screen analysis and recommendations

### Technical Excellence (39-60)

1. **Advanced Security Architecture** - Enterprise-grade security measures
2. **Performance Optimization** - Database, caching, and frontend optimization
3. **API Design Excellence** - RESTful APIs with comprehensive documentation
4. **Background Processing** - Queue management and job processing
5. **Monitoring and Observability** - Comprehensive logging and analytics
6. **Local Development Infrastructure** - XAMPP optimization with cloud integration
7. **Hybrid AI Integration** - Ollama primary with AWS Bedrock fallback
8. **Local Agent Architecture** - AI agents with cloud processing capabilities
9. **Modern Frontend Architecture** - Component-based design with accessibility
10. **Advanced State Management** - Scalable data flow patterns
11. **Frontend Security** - Client-side security and privacy protection
12. **Database Architecture** - Advanced optimization and performance
13. **Enterprise Security** - Comprehensive authentication and authorization
14. **API Integration** - External service integration with fallback mechanisms
15. **Queue Management** - Advanced background processing capabilities
16. **System Monitoring** - Comprehensive observability and alerting
17. **Cloud Integration** - Hybrid local-cloud architecture optimization
18. **AI Model Management** - Intelligent model selection and cost optimization
19. **Agent Development** - Local agent architecture with cloud capabilities
20. **Development Infrastructure** - Modern development practices and deployment
21. **Resource Optimization** - Local resource management with cloud cost control
22. **Security Best Practices** - Local development security with enterprise patterns

## Critical API Status Updates (January 10, 2026)

### Verified Active APIs

- ✅ **umapyoi.net**: Primary API for character, support card, and news data (verified active and reliable)
- ✅ **AWS Bedrock**: Claude 4.5 models and Nova 2 available with verified pricing
- ✅ **cloudstudio/ollama-laravel**: Active package supporting Laravel 11+ (compatible with Laravel 12)

### Deprecated/Replaced APIs

- ❌ **SimpleSandman/UmaMusumeAPI**: Repository archived, EOL October 29, 2024 → **REPLACED** with umapyoi.net
- ⚠️ **UmamusumeDB.com**: Requires verification - need to confirm current availability and API access

### Technology Verification Status

- ✅ **Laravel 12**: Released February 24, 2025 with new starter kits, TypeScript support, Tailwind CSS integration
- ✅ **Tailwind CSS v4**: Released January 22, 2025 with 5x faster builds, zero configuration, modern CSS features
- ✅ **AWS Bedrock Models**: Claude 4.5 Opus ($5/$25), Sonnet ($3/$15), Haiku ($1/$5), Nova 2 Lite ($0.00125), Nova 2 Pro (Preview)

### Meta Knowledge Integration

- **SS Tier Cards**: Kitasan Black, Narita Brian, Symboli Rudolf for optimal deck building
- **Friendship Training**: Essential by first Summer Camp (80% bond threshold)
- **Summer Camps**: Early/Late July/August (4 turns each year) for maximum efficiency
- **Turn Phases**: Junior (1-24), Classic (25-48), Senior (49-72) with phase-specific priorities
- **Hidden Race Boost**: +400 stat boost during career mode races affects training calculations

## Innovation Highlights

### AI Architecture

- **Tiered Intelligence**: Local Ollama → Cloud models for complex analysis
- **Screenshot Analysis**: OCR + AI for automatic game state capture
- **Contextual Advice**: Career-long conversation memory and adaptation

### Comprehensive Coverage

- **All Game Modes**: URA Finale and Unity Cup optimization
- **Complete Mechanics**: Stats, skills, weather, inheritance, events
- **Historical Learning**: Pattern recognition across multiple careers
- **Real-time Adaptation**: Dynamic strategy updates based on game state

### User Experience

- **Accessibility**: WCAG 2.1 AA compliance with screen reader support
- **Performance**: <2 second loading, offline capability, progressive loading
- **Privacy**: Local-only operation with optional cloud AI fallback

## Current Status: Requirements Complete

✅ **60 Comprehensive Requirements** covering all game mechanics and technical implementation
✅ **Detailed Research** with specific numerical values and formulas
✅ **External API Integration** strategy with multiple data sources and verified technology stack
✅ **AI System Architecture** with local-first privacy approach using Ollama + AWS Bedrock
✅ **Technical Implementation** standards and best practices for Laravel 12
✅ **Accessibility and Performance** requirements specified with WCAG 2.2 AA compliance

## Next Phase: Implementation

Ready to proceed to **Implementation Phase** based on comprehensive 6-phase development plan:

### Phase 1: Foundation & Core Setup (22-28 hours)

- Laravel 12 project initialization with XAMPP environment
- Database schema implementation with 15+ tables
- Core models and Eloquent relationships

### Phase 2: Authentication & API Foundation (24-30 hours)

- Laravel Sanctum authentication system
- Frontend foundation with Tailwind CSS v4
- Character management interface

### Phase 3: Core Game Mechanics Implementation (37-45 hours)

- Training prediction engine with scenario-specific mechanics
- Advanced skill management system with evolution tracking
- Support card management and deck optimization

### Phase 4: AI Integration and External APIs (36-44 hours)

- Ollama local AI integration via cloudstudio/ollama-laravel
- AWS Bedrock fallback integration with cost management
- External API integration with umapyoi.net and fallback mechanisms

### Phase 5: Advanced Features and Optimization (30-37 hours)

- OCR screenshot processing system
- Career analytics and performance tracking
- Data import/export and migration system

### Phase 6: Performance, Testing, and Deployment (28-35 hours)

- Performance optimization and monitoring
- Comprehensive testing suite
- Documentation and deployment preparation

**Total Estimated Time**: 180-220 hours (22-28 weeks at 8 hours/week)

## Success Metrics

- Consistent A+ grade character achievements
- Eliminated manual tracking overhead
- AI-powered strategic guidance accuracy
- Comprehensive optimization across all game scenarios
- Seamless integration with existing workflow patterns
