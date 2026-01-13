# Software Requirements Specification (SRS)

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0
**Date**: January 14, 2026
**Project**: UmamusumeCareerPlanner
**Author**: Development Team

---

## Table of Contents

1. [Introduction](#introduction)
2. [Overall Description](#2-overall-description)
3. [System Features](#3-system-features)
4. [External Interface Requirements](#4-external-interface-requirements)
5. [Non-Functional Requirements](#5-non-functional-requirements)
6. [Other Requirements](#6-other-requirements)
7. [Appendices](#7-appendices)

---

## Introduction

### Purpose

This Software Requirements Specification (SRS) document describes the
comprehensive functional and non-functional requirements for the
**Umamusume Pretty Derby Career Planner**, a sophisticated local-first
web application designed to optimize gameplay in the Umamusume Pretty
Derby mobile game. The system replaces manual Google Docs tracking with
an intelligent platform combining proven optimization workflows, hybrid
AI guidance (Ollama local + AWS Bedrock cloud), comprehensive external
data integration, and advanced game mechanics support.

The application implements all 59 detailed requirements from the
comprehensive specification, including advanced features such as skill
evolution chains, hint-based SP cost reduction, weather condition
optimization, Unity Cup Spirit Burst mechanics, OCR screenshot
processing, and Progressive Web App capabilities with WCAG 2.2 AA
accessibility compliance.

### Document Conventions

- **SHALL**: Indicates mandatory requirements
- **SHOULD**: Indicates recommended requirements
- **MAY**: Indicates optional requirements
- **Priority Levels**: Critical (★★★★★), High (★★★★), Medium (★★★),
  Low (★★), Optional (★)

### Intended Audience and Reading Suggestions

This document is intended for:

- **Development Team**: Complete implementation guidance with MCP server
  integration and subagent utilization strategies
- **Project Stakeholders**: Understanding of system capabilities and
  comprehensive game mechanics support
- **Quality Assurance**: Testing requirements and acceptance criteria for
  all 59 requirements
- **System Administrators**: Deployment and maintenance requirements for
  local-first architecture with hybrid AI processing
- **MCP Integration Specialists**: Model Context Protocol server
  configuration and external service integration
- **AI Development Team**: Subagent coordination and hybrid AI processing
  implementation guidance

### Product Scope

The Umamusume Career Planner is a comprehensive optimization application
that helps players achieve A+ grade character ratings consistently
through data-driven decision making in both URA Finale and Unity Cup
scenarios. The system provides:

- **AI-powered training optimization** with scenario-specific mechanics
  including Spirit Burst coordination, facility level management, and
  friendship training optimization
- **Comprehensive race strategy analysis** with weather condition support,
  track characteristic evaluation, and performance prediction
- **Advanced skill management** with evolution chains (Normal → Rare),
  hint-based SP cost reduction (20% per duplicate, 40% max), and
  strategic acquisition timing
- **Support card deck management** with 6-card configuration, meta tier
  rankings, and skill provision tracking
- **Career progress tracking** with historical analysis, multi-run
  comparison, and pattern recognition across 60-70 turn careers
- **Legacy and inheritance system** with 6-character factor management
  (2 parents + 4 grandparents) and affinity compatibility
- **Hybrid AI advisory system** using local Ollama models (Llama 3.3,
  Mistral, Qwen) with AWS Bedrock fallback (Claude 4.5 series, Nova 2)
- **OCR screenshot processing** with Tesseract and OpenCV for automated
  game state extraction with Japanese language support
- **Progressive Web App capabilities** with offline functionality, service
  workers, and WCAG 2.2 AA accessibility compliance
- **MCP server integration** for enhanced development workflow, AWS
  services management, and external API coordination
- **Subagent utilization** with context-gatherer for codebase analysis
  and general-task-execution for parallel development tasks

### References

- **Laravel 12 Documentation** (Released February 24, 2025) - Advanced
  starter kits, TypeScript support, Tailwind integration
- **Tailwind CSS v4 Documentation** (Released January 22, 2025) - 5x
  faster builds, zero configuration, modern CSS features
- **AWS Bedrock API Documentation** - Claude 4.5 Opus ($5/$25), Sonnet
  ($3/$15), Haiku ($1/$5), Nova 2 Lite ($0.00125), Nova 2 Pro (Preview)
- **cloudstudio/ollama-laravel Package** - Local AI integration with
  verified Laravel 12 compatibility
- **umapyoi.net API Documentation** - Active community API replacing
  deprecated SimpleSandman/UmaMusumeAPI (EOL October 2024)
- **UmamusumeDB.com API Documentation** - Community calculator tools and
  meta analysis (requires verification)
- **WCAG 2.2 AA Accessibility Guidelines** - Web Content Accessibility
  Guidelines Level AA compliance standards
- **Progressive Web App Standards** - Service workers, offline
  functionality, installable experience specifications
- **Model Context Protocol (MCP) Documentation** - MCP server integration
  and configuration guidelines
- **Tesseract OCR Documentation** - Optical Character Recognition with
  Japanese language support
- **OpenCV Documentation** - Image preprocessing and computer vision
  capabilities

---

---

## 2. Overall Description

### 2.1 Product Perspective

The Umamusume Career Planner is a standalone local-first web application built with Laravel 12 that integrates with external APIs and cloud AI services through intelligent routing and MCP server coordination. The system architecture follows a comprehensive hybrid approach:

- **Local-First Architecture**: All personal data stored locally using MySQL database with Redis caching via WSL for optimal performance
- **Hybrid AI Processing**: Local Ollama models (Llama 3.3, Mistral, Qwen) as primary with AWS Bedrock cloud fallback (Claude 4.5 series, Nova 2) for complex tasks
- **External API Integration**: Intelligent integration with umapyoi.net (replacing deprecated SimpleSandman/UmaMusumeAPI) and UmamusumeDB.com with fallback mechanisms
- **Privacy-Focused Design**: No personal gameplay data transmitted without explicit consent, with transparent privacy policies and user-controlled data sharing
- **Progressive Web App**: Modern web standards with offline functionality, service workers, background sync, and installable experience
- **MCP Server Integration**: Model Context Protocol servers for enhanced AWS integration, external API management, and development workflow optimization
- **Subagent Coordination**: Context-gatherer subagent for codebase analysis and general-task-execution subagent for parallel development tasks
- **Accessibility Excellence**: WCAG 2.2 AA compliance with comprehensive keyboard navigation, screen reader support, and inclusive design principles

### 2.2 Product Functions

#### Core Functions

1. **Character State Management**: Comprehensive stat tracking (0-1200 range) with priorities, aptitudes (G-SS fixed ratings), and factor inheritance from 6-character legacy teams
2. **Training Optimization**: AI-powered predictions for URA Finale individual optimization and Unity Cup team mechanics with Spirit Burst coordination
3. **Race Strategy**: Intelligent preparation with weather conditions (Sunny/Cloudy/Rainy/Snowy), track characteristics, and performance analysis with distance-specific requirements
4. **Skill Management**: Advanced SP optimization with evolution chains (Normal → Rare), hint-based cost reduction (20% per duplicate, 40% max), and strategic acquisition timing
5. **Support Card Management**: 6-card deck configuration with friendship tracking, meta tier rankings (SS/S/A/B), and skill provision analysis
6. **Career Analytics**: Historical analysis across multiple 60-70 turn careers with pattern recognition and performance optimization
7. **AI Advisory**: Hybrid local/cloud chatbot with game-specific knowledge, context awareness, and strategic guidance
8. **Screenshot Analysis**: OCR-powered game state extraction using Tesseract with OpenCV preprocessing and Japanese language support

#### Advanced Functions

1. **Legacy Management**: Factor inheritance system with 2 parents + 4 grandparents, affinity compatibility (◎ symbol), and strategic factor farming
2. **Unity Cup Mechanics**: Spirit Burst coordination, facility level management (1-5 providing 1.0x-2.0x multipliers), and distance team optimization
3. **Weather System**: Comprehensive weather condition tracking with performance impact analysis and weather-specific skill recommendations
4. **Turn Economy**: Optimal turn allocation across Junior/Classic/Senior phases with Summer Camp optimization (4-turn high-efficiency periods)
5. **Community Integration**: Meta data synchronization, tier list updates, and privacy-preserving strategy sharing
6. **Performance Analytics**: Statistical analysis, machine learning improvements, and predictive modeling for optimization recommendations
7. **Data Migration**: Import/export functionality with OCR support for existing tracking systems and comprehensive backup capabilities
8. **Progressive Web App**: Offline functionality, background sync, push notifications, and installable experience with service workers

### 2.3 User Classes and Characteristics

#### Primary User: Individual Player

- **Experience Level**: Intermediate to advanced Umamusume players
- **Technical Skills**: Basic web application usage
- **Usage Frequency**: Daily during active gameplay sessions
- **Primary Goals**: Achieve A+ grade characters consistently
- **Key Needs**: Turn-by-turn optimization guidance, strategic planning

#### Secondary User: Community Member

- **Experience Level**: Varies from beginner to expert
- **Technical Skills**: Basic to intermediate
- **Usage Frequency**: Occasional for specific optimization needs
- **Primary Goals**: Access community strategies and meta information
- **Key Needs**: Shared builds, tier lists, strategy guides

### 2.4 Operating Environment

#### Development Environment

- **Operating System**: Windows 10/11 with XAMPP
- **Web Server**: Apache 2.4+
- **Database**: MySQL 8.0+ with Redis (WSL) for caching
- **PHP Version**: PHP 8.3+
- **Framework**: Laravel 12 (released February 24, 2025)
- **Development Timeline**: 22-28 weeks across 6 phases (see 001_SDP)

#### Production Environment

- **Deployment**: Local XAMPP installation
- **Browser Support**: Modern browsers (Chrome 90+, Firefox 88+, Safari 14+, Edge 90+)
- **Mobile Support**: Responsive design for tablets and smartphones
- **Offline Capability**: Progressive Web App (PWA) with service workers

#### External Dependencies

- **AI Services**: Ollama (local) with cloudstudio/ollama-laravel package + AWS Bedrock (cloud fallback) with intelligent routing and cost optimization
- **External APIs**: umapyoi.net (verified active, replacing deprecated SimpleSandman/UmaMusumeAPI), UmamusumeDB.com (verification pending), community sources with intelligent fallback
- **Image Processing**: Tesseract OCR with OpenCV preprocessing for Japanese language support and automated screenshot analysis
- **MCP Servers**: Model Context Protocol integration for AWS services, external API management, and development workflow enhancement
- **Progressive Web Technologies**: Service workers, background sync, push notifications, and offline functionality standards

### 2.5 Design and Implementation Constraints

#### Technical Constraints

- **Local-First Architecture**: All personal data must remain on user's device
- **Performance**: Response times under 2 seconds for core features
- **Accessibility**: WCAG 2.2 AA compliance mandatory
- **Browser Compatibility**: Support for ES2024+ JavaScript features
- **Database**: MySQL with optimized indexing for large datasets

#### Business Constraints

- **Privacy**: No personal gameplay data transmission without consent
- **Cost**: Minimize cloud AI usage through intelligent local/cloud routing
- **Maintenance**: Single-user application with minimal ongoing maintenance
- **Scalability**: Architecture must support future multi-user migration

#### Regulatory Constraints

- **Accessibility**: WCAG 2.2 AA compliance for inclusive design
- **Data Protection**: Local data storage with user-controlled backups
- **API Usage**: Respect rate limits and terms of service for external APIs

### 2.6 User Documentation

#### Required Documentation

1. **Installation Guide**: XAMPP setup and application deployment
2. **User Manual**: Complete feature documentation with screenshots
3. **Quick Start Guide**: Essential workflows for new users
4. **API Documentation**: External integration and customization
5. **Troubleshooting Guide**: Common issues and solutions

#### Documentation Standards

- **Format**: Markdown with embedded diagrams
- **Accessibility**: Screen reader compatible with alt text
- **Maintenance**: Version-controlled with application releases
- **Languages**: English primary, with internationalization support

### 2.7 Assumptions and Dependencies

#### Assumptions

- User has basic understanding of Umamusume Pretty Derby gameplay
- XAMPP environment is properly configured and maintained
- Internet connection available for external API integration
- Modern browser with JavaScript enabled

#### Dependencies

- **Laravel 12**: Core framework dependency with TypeScript support and advanced starter kits (verified release February 24, 2025)
- **Tailwind CSS v4**: Frontend styling with 5x faster builds and zero configuration (verified release January 22, 2025)
- **cloudstudio/ollama-laravel**: Local AI integration with verified Laravel 12 compatibility for hybrid AI processing
- **AWS Bedrock**: Cloud AI fallback service with verified model pricing (Claude 4.5 Opus $5/$25, Sonnet $3/$15, Haiku $1/$5, Nova 2 Lite $0.00125)
- **umapyoi.net**: Primary external API for game data (verified active, replacing deprecated SimpleSandman/UmaMusumeAPI EOL October 2024)
- **Redis via WSL**: Caching, session management, and queue processing for optimal performance
- **Tesseract OCR**: Text extraction with Japanese language support for screenshot processing
- **OpenCV**: Image preprocessing and computer vision capabilities for OCR enhancement
- **MCP Servers**: Model Context Protocol integration for enhanced development workflow and service coordination

### 2.8 MCP Integration and Subagent Architecture

#### 2.8.1 Model Context Protocol (MCP) Server Integration

The system leverages MCP servers to enhance development workflow and service integration:

- **AWS MCP Servers**: Streamlined integration with AWS Bedrock for cloud AI services with automated credential management and cost optimization
- **External API MCP Servers**: Enhanced integration with umapyoi.net and UmamusumeDB.com with intelligent fallback mechanisms and health monitoring
- **Development Tool MCP Servers**: Automated code quality checks, testing assistance, and deployment automation
- **Database MCP Servers**: Advanced database management, optimization recommendations, and performance monitoring

#### 2.8.2 Subagent Coordination Strategy

The system utilizes specialized AI subagents for efficient development and complex task execution:

- **Context-Gatherer Subagent**: Deployed once per major feature area to analyze unfamiliar codebase sections, identify relevant files and patterns, and understand component interactions before implementation
- **General-Task-Execution Subagent**: Handles parallel development tasks including testing automation, documentation generation, and independent work streams to accelerate development velocity
- **Task Delegation**: Efficient coordination between main development process and subagents with proper result integration and quality assurance
- **Development Acceleration**: Strategic subagent deployment to reduce development time while maintaining code quality and architectural consistency

#### 2.8.3 Integration Benefits

- **Enhanced Development Workflow**: MCP servers provide automated tooling and service integration reducing manual configuration overhead
- **Improved Code Quality**: Subagent analysis ensures comprehensive understanding of codebase before implementing complex features
- **Accelerated Development**: Parallel task execution through subagents while maintaining focus on critical path development
- **Service Reliability**: MCP server integration provides robust external service management with automated failover and monitoring

---

## 3. System Features

### 3.1 Character State Management (Priority: ★★★★★)

#### 3.1.1 Description and Priority

Comprehensive character state tracking system that manages all aspects of character development including stats, aptitudes, goals, and progression. This is the foundation feature that enables all other optimization capabilities.

#### 3.1.2 Stimulus/Response Sequences

- **Stimulus**: User creates new character or updates existing character data
- **Response**: System validates input, updates database, and refreshes optimization recommendations

#### 3.1.3 Functional Requirements

**REQ-3.1.1**: Character Creation and Configuration

- The system SHALL allow users to create character profiles with trainee name, career stage, class, and scenario type (URA Finale individual optimization/Unity Cup team mechanics)
- The system SHALL record current stat values for all five stats with priorities: Speed (0-1200 Priority ★★★★★), Stamina (0-1200 Priority ★★★★), Power (0-1200 Priority ★★★), Guts (0-1200 Priority ★), Wit (0-1200 Priority ★★)
- The system SHALL display corresponding letter grades (G+ through SS) for all stat values with visual progress indicators and breakpoint notifications (901 and 1600 thresholds)
- The system SHALL validate stat ranges and prevent invalid entries with comprehensive error handling and user feedback
- The system SHALL integrate character avatars from existing trainee_images directory with automatic character recognition and fallback handling

**REQ-3.1.2**: Aptitude Management

- The system SHALL record aptitude ratings (G through SS) for all distance categories: Sprint (1000-1400m), Mile (1401-1800m), Medium (1801-2400m), Long (2401m+)
- The system SHALL record aptitude ratings for surface types (Turf/Dirt) and running styles (Front Runner/Pace Chaser/Late Surger/End Closer) with performance impact calculations
- The system SHALL enforce that aptitudes are fixed talent ratings that cannot be changed through training with clear user education and visual indicators
- The system SHALL provide visual indicators for aptitude strengths and weaknesses with color-coded grade visualization and strategic recommendations

**REQ-3.1.3**: Goal Setting and Progress Tracking

- The system SHALL allow users to set target stat values with distance-specific minimums: Sprint (350 career/500-600 PvP stamina), Mile (400 career/600-700 PvP), Medium (500 career/800-900 PvP), Long (600 career/900-1100 PvP)
- The system SHALL track progress toward goals with visual indicators (○ adequate, ⦾ borderline, △ insufficient, × inadequate) and percentage completion tracking
- The system SHALL provide recommended development paths based on character aptitudes with strategic guidance and optimization suggestions
- The system SHALL update goal progress automatically as character stats change with real-time recalculation and notification system

**REQ-3.1.4**: Character State Monitoring

- The system SHALL track energy levels (0-100%) with training failure risk calculations and optimal rest timing recommendations
- The system SHALL monitor mood status with training effectiveness modifiers: Great (+20%), Good (+10%), Normal (0%), Bad (-10%), Awful (-20%)
- The system SHALL track current conditions (Practice Perfect -2% failure rate, Practice Poor +2% failure rate, Charming +2 bond, Sharp -10% skill costs, Migraine mood resistance, Dry Skin motivation decrease)
- The system SHALL monitor days until next race, career phase information (Junior turns 1-24, Classic turns 25-48, Senior turns 49-72), and Summer Camp periods (Early/Late July/August)
- The system SHALL maintain inherited growth rate bonuses (+10%, +20%, +30%) for each stat with factor inheritance tracking from 6-character legacy teams

### 3.2 Training Prediction Engine (Priority: ★★★★★)

#### 3.2.1 Description and Priority

Advanced prediction system that calculates expected outcomes for all training options, incorporating support card bonuses, scenario-specific mechanics, and optimization algorithms to provide intelligent recommendations.

#### 3.2.2 Stimulus/Response Sequences

- **Stimulus**: User requests training predictions for current turn
- **Response**: System analyzes character state, calculates predictions for all options, and provides ranked recommendations

#### 3.2.3 Functional Requirements

**REQ-3.2.1**: URA Finale Training Predictions

- The system SHALL calculate predicted stat gains for Speed, Stamina, Power, Guts, and Wit based on support card bonuses, facility levels, and character growth rates
- The system SHALL incorporate friendship training multipliers (2 participants +2 bonus, 3 participants +3 bonus) with rainbow training availability at 80%+ friendship levels
- The system SHALL consider energy levels (0-100%) and training failure risks with mood effects and condition impacts on success rates
- The system SHALL provide individual character optimization recommendations with turn economy management and goal-based prioritization

**REQ-3.2.2**: Unity Cup Training Predictions

- The system SHALL calculate Spirit Burst potential with 4-session gauge filling mechanics and flame icon indicators for teammate availability
- The system SHALL consider team stat distribution effects and facility level bonuses (1-5 providing 1.0x to 2.0x multipliers) based on team rank performance
- The system SHALL track teammate positioning, ready Spirit Burst indicators, and distance team requirements (Sprint/Mile/Medium/Long/Dirt specializations)
- The system SHALL optimize for team synergy and facility level improvements rather than pure individual stat maximization

**REQ-3.2.3**: Training Option Analysis

- The system SHALL evaluate all training options (Speed, Stamina, Power, Guts, Wit, Rest, Recreation, Infirmary) with comprehensive outcome predictions
- The system SHALL calculate expected stat gains, energy costs, skill hint opportunities, and condition management effects
- The system SHALL identify red "!" indicators for guaranteed skill hint acquisition from support card specialization matching
- The system SHALL rank options by effectiveness toward current goals with clear reasoning explanations and confidence scoring

**REQ-3.2.4**: Real-Time Recommendation Updates

- The system SHALL update predictions when character state changes (stats, energy, mood, conditions) with automatic recalculation
- The system SHALL recalculate recommendations when events, Spirit Burst triggers, or support card friendship changes occur
- The system SHALL maintain prediction accuracy tracking for machine learning improvement with historical outcome comparison
- The system SHALL provide confidence indicators for all predictions with uncertainty quantification and alternative scenario analysis

### 3.3 Race Preparation and Strategy (Priority: ★★★★)

#### 3.3.1 Description and Priority

Comprehensive race analysis system that evaluates upcoming races, assesses character readiness, and provides strategic recommendations for optimal performance.

#### 3.3.2 Stimulus/Response Sequences

- **Stimulus**: Race deadline approaches or user requests race analysis
- **Response**: System analyzes race requirements, character readiness, and provides preparation recommendations

#### 3.3.3 Functional Requirements

**REQ-3.3.1**: Race Information Display

- The system SHALL display detailed race information including grade (Pre-OP/OP/G3/G2/G1), track (Kyoto, Tokyo, etc.), surface (Turf/Dirt), distance category with specific meter ranges
- The system SHALL show track characteristics (Right/Left, Inner/Outer) and weather conditions (Sunny/Cloudy/Rainy/Snowy) with performance impact analysis
- The system SHALL provide weather effects on track conditions (Firm for dry, Good/Soft/Heavy for wet conditions) with stamina requirement adjustments
- The system SHALL maintain complete race calendar with scheduling information, fan requirements, and Triple Crown opportunities (Satsuki Sho, Tokyo Yushun/Japanese Derby, Kikuka Sho)

**REQ-3.3.2**: Character Readiness Assessment

- The system SHALL provide stat requirement indicators using symbols (○ adequate, ⦾ borderline, △ insufficient, × inadequate) with detailed threshold analysis
- The system SHALL evaluate readiness for Speed, Stamina, Power, Guts, and Wit based on race requirements, competition level, and weather effects
- The system SHALL consider distance-specific stamina requirements with weather adjustments: Sprint (350 career/500-600 PvP), Mile (400 career/600-700 PvP), Medium (500 career/800-900 PvP), Long (600 career/900-1100 PvP)
- The system SHALL assess weather condition impacts on performance with aptitude-based modifications and skill requirement recommendations

**REQ-3.3.3**: Strategy Optimization

- The system SHALL recommend optimal running styles (Front Runner needs Speed/Stamina, Pace Chaser balanced approach, Late Surger needs Speed/Power, End Closer needs Power/Guts)
- The system SHALL base recommendations on character stats, aptitudes, race conditions, weather effects, and track characteristics with performance prediction modeling
- The system SHALL provide performance predictions for different strategy combinations with confidence intervals and risk assessment
- The system SHALL suggest weather-specific skill selection ("Wet Conditions ○/◎" for Good/Soft/Heavy ground, "Firm Conditions ○" for dry tracks)

**REQ-3.3.4**: Race Goal Management

- The system SHALL track race goals including Junior/Senior debut requirements, fan acquisition targets, and specific placement objectives (1st, Top 2, Top 3)
- The system SHALL monitor weather-specific performance targets with condition-based strategy adjustments
- The system SHALL provide completion status monitoring with progress tracking and deadline alerts
- The system SHALL recommend pre-race preparation strategies including skill selection, energy management, and condition optimization for weather scenarios

### 3.4 Advanced Skill Management System (Priority: ★★★★)

#### 3.4.1 Description and Priority

Comprehensive skill management system with hint-based cost reduction, evolution tracking, and strategic SP allocation optimization.

#### 3.4.2 Stimulus/Response Sequences

- **Stimulus**: User acquires skill hints or requests skill recommendations
- **Response**: System updates hint tracking, recalculates costs, and provides acquisition recommendations

#### 3.4.3 Functional Requirements

**REQ-3.4.1**: Skill Database and Categorization

- The system SHALL maintain complete skill database with SP costs by category (Normal 120-180 SP, Rare 180-240 SP, Unique variable)
- The system SHALL implement skill categorization (Speed, Passive, Recovery, Debuff) with proper relationships
- The system SHALL track skill evolution mapping (Normal → Rare upgrade paths) with prerequisite tracking
- The system SHALL provide skill effect descriptions and strategic usage recommendations

**REQ-3.4.2**: Hint System and Cost Reduction

- The system SHALL track hint sources (support cards, events, inheritance) with source identification
- The system SHALL calculate 20% SP cost reduction per duplicate hint with 40% maximum discount
- The system SHALL identify red "!" indicators for guaranteed hint opportunities during training
- The system SHALL calculate hint probability for non-guaranteed opportunities

**REQ-3.4.3**: Skill Evolution Management

- The system SHALL implement automatic skill evolution system (Normal → Rare replacement)
- The system SHALL validate prerequisites for skill evolution chains
- The system SHALL provide skill evolution planning with optimal acquisition timing
- The system SHALL calculate SP efficiency for evolution vs direct acquisition

**REQ-3.4.4**: SP Optimization Engine

- The system SHALL manage SP budget with hint collection optimization
- The system SHALL recommend hint farming strategies for maximum cost reduction
- The system SHALL provide skill build planning with character synergy analysis
- The system SHALL create long-term skill development roadmaps with milestone tracking

### 3.5 Support Card Management (Priority: ★★★★)

#### 3.5.1 Description and Priority

6-card deck management system with friendship tracking, skill provision analysis, and deck optimization recommendations.

#### 3.5.2 Stimulus/Response Sequences

- **Stimulus**: User configures support deck or friendship levels change
- **Response**: System validates deck composition, updates training predictions, and provides optimization recommendations

#### 3.5.3 Functional Requirements

**REQ-3.5.1**: Support Card Database

- The system SHALL maintain comprehensive support card database with stats, bonuses, and skill provisions
- The system SHALL implement meta tier rankings (SS/S/A/B) with regular update mechanisms
- The system SHALL track skill provision mappings showing which cards provide which skill hints
- The system SHALL provide card effect profiles and specialized use case recommendations

**REQ-3.5.2**: 6-Card Deck Management

- The system SHALL enforce 6-card deck configuration (5 owned + 1 friend card) with position tracking
- The system SHALL implement deck validation rules and constraint checking
- The system SHALL provide card swapping and position management functionality
- The system SHALL manage friend card borrowing system with availability tracking

**REQ-3.5.3**: Friendship and Bond System

- The system SHALL track friendship levels (0-100%) with progression mechanics
- The system SHALL calculate rainbow training availability (80%+ friendship threshold)
- The system SHALL compute friendship bonus calculations for training effectiveness
- The system SHALL track bond level impact on skill hint provision rates

**REQ-3.5.4**: Deck Optimization Analysis

- The system SHALL analyze deck composition for stat coverage and skill provision gaps
- The system SHALL implement synergy analysis for card combinations and strategic alignment
- The system SHALL provide meta tier optimization with character build compatibility
- The system SHALL recommend deck configurations based on character goals and scenario type

### 3.6 Legacy and Inheritance System (Priority: ★★★)

#### 3.6.1 Description and Priority

Comprehensive inheritance management system handling factor inheritance from legacy characters with affinity compatibility and success rate calculations.

#### 3.6.2 Stimulus/Response Sequences

- **Stimulus**: User selects legacy characters or plans inheritance strategy
- **Response**: System calculates inheritance bonuses, validates compatibility, and provides optimization recommendations

#### 3.6.3 Functional Requirements

**REQ-3.6.1**: Legacy Character Management

- The system SHALL record veteran Umamusume stats and inherited factors with specific bonuses
- The system SHALL track Blue stat factors (★☆☆ = +5, ★★☆ = +12, ★★★ = +21)
- The system SHALL manage Red aptitude factors (1★ = 1 grade up, then 3★ per additional grade)
- The system SHALL handle Green unique skill factors and White normal skill/race bonus factors

**REQ-3.6.2**: Inheritance Calculation

- The system SHALL apply legacy stat bonuses and factor effects to base character attributes
- The system SHALL implement proper factor stacking calculations with multiple source handling
- The system SHALL calculate aptitude improvements through red factors
- The system SHALL manage skill inheritance from green/white factors with growth rate bonuses

**REQ-3.6.3**: Affinity and Compatibility

- The system SHALL track factor inheritance stacking rules with affinity compatibility indicators (◎ symbol)
- The system SHALL calculate inheritance success rates based on parent-child affinity compatibility
- The system SHALL recommend optimal legacy team compositions (2 main parents + 4 grandparents maximum)
- The system SHALL optimize factor combinations for different character builds

**REQ-3.6.4**: Strategic Planning

- The system SHALL suggest which characters to develop to improve legacy options for target builds
- The system SHALL recommend factor farming strategies for specific stat/aptitude/skill combinations
- The system SHALL identify optimal inheritance paths for long-term account progression
- The system SHALL track factor collection progress across multiple career completions

### 3.7 AI-Powered Advisory System (Priority: ★★★★)

#### 3.7.1 Description and Priority

Hybrid AI system combining local Ollama models with cloud AWS Bedrock services to provide intelligent strategic guidance and contextual assistance.

#### 3.7.2 Stimulus/Response Sequences

- **Stimulus**: User asks question or uploads screenshot for analysis
- **Response**: System processes request through appropriate AI model and provides contextual advice

#### 3.7.3 Functional Requirements

**REQ-3.7.1**: Hybrid AI Architecture

- The system SHALL implement local Ollama models (Llama 3.3, Mistral, Qwen) as primary inference engine for privacy and speed with response times under 3 seconds
- The system SHALL provide automatic fallback to AWS Bedrock models (Nova 2 Lite $0.00125/1K tokens, Nova 2 Pro Preview, Claude 4.5 Opus $5/$25/1M tokens, Sonnet $3/$15/1M tokens, Haiku $1/$5/1M tokens) when local processing exceeds 10 seconds or advanced reasoning is required
- The system SHALL maintain conversation context and user preferences across model switches with seamless transition and context preservation
- The system SHALL implement intelligent routing based on complexity detection, cost optimization, and performance monitoring with user transparency about which model is being used

**REQ-3.7.2**: Conversation Management

- The system SHALL maintain persistent conversation context throughout career runs with automatic session management and reference to previous decisions
- The system SHALL track conversation history with search and reference capabilities, impact analysis of previous recommendations, and outcome tracking
- The system SHALL provide conversation export and import functionality for strategy sharing with privacy controls and data anonymization options
- The system SHALL implement conversation threading for complex topics with branching for exploring alternative strategies and scenario analysis

**REQ-3.7.3**: Game Knowledge Integration

- The system SHALL provide specialized prompts for Umamusume game mechanics using few-shot learning with game-specific examples and chain-of-thought reasoning for strategic decisions
- The system SHALL integrate current character state and career context into AI responses with real-time data access and dynamic context management
- The system SHALL access real-time game data and meta information through external API integration with retrieval-augmented generation (RAG) capabilities
- The system SHALL learn from user feedback to improve recommendation accuracy with feedback loops and continuous model improvement based on user interactions and outcomes

**REQ-3.7.4**: Screenshot Analysis and OCR Integration

- The system SHALL process uploaded screenshots using Tesseract OCR with OpenCV preprocessing for image enhancement and noise reduction
- The system SHALL extract game state information automatically from images with Japanese language support and confidence scoring for extraction accuracy
- The system SHALL validate extracted data against known game parameters with error detection and manual correction interface for OCR errors
- The system SHALL integrate OCR results with AI analysis for contextual advice and strategic recommendations based on extracted game state

### 3.8 Career Progress Tracking (Priority: ★★★)

#### 3.8.1 Description and Priority

Comprehensive career tracking system that maintains historical records, analyzes patterns, and provides insights for improvement.

#### 3.8.2 Stimulus/Response Sequences

- **Stimulus**: Career events occur or user requests analytics
- **Response**: System logs events, updates analytics, and provides performance insights

#### 3.8.3 Functional Requirements

**REQ-3.8.1**: Career Data Logging

- The system SHALL log actual training outcomes against predictions and calculate accuracy metrics
- The system SHALL record race results, final stats, and strategy effectiveness
- The system SHALL store complete career data including final grade, key decisions, and performance analysis
- The system SHALL maintain database of multiple career runs for comparative analysis

**REQ-3.8.2**: Historical Analysis

- The system SHALL provide insights on successful patterns and areas for improvement
- The system SHALL identify optimal decision points and timing strategies
- The system SHALL track prediction accuracy and model performance over time
- The system SHALL generate statistical reports on career progression trends

**REQ-3.8.3**: Performance Metrics

- The system SHALL calculate career efficiency metrics and goal achievement rates
- The system SHALL track resource utilization and optimization effectiveness
- The system SHALL provide comparative analysis between different strategies and approaches
- The system SHALL identify correlation patterns between decisions and outcomes

**REQ-3.8.4**: Improvement Recommendations

- The system SHALL suggest areas for improvement based on historical data
- The system SHALL recommend strategy adjustments for better outcomes
- The system SHALL provide personalized optimization tips based on user patterns
- The system SHALL track implementation of recommendations and measure effectiveness

---

## 4. External Interface Requirements

### 4.1 User Interfaces

#### 4.1.1 General UI Requirements

- The system SHALL provide a responsive web interface compatible with desktop, tablet, and mobile devices
- The system SHALL implement WCAG 2.2 AA accessibility compliance including keyboard navigation and screen reader support
- The system SHALL use Tailwind CSS v4 for consistent styling and modern design patterns
- The system SHALL provide Progressive Web App (PWA) capabilities with offline functionality

#### 4.1.2 Main Dashboard Interface

- The system SHALL display character overview with current stats, goals, and progress indicators
- The system SHALL provide quick access to training predictions and recommendations
- The system SHALL show upcoming races and preparation status
- The system SHALL display recent AI conversations and system notifications

#### 4.1.3 Character Management Interface

- The system SHALL provide character creation and editing forms with comprehensive validation
- The system SHALL display character stats with visual progress bars and grade indicators
- The system SHALL show aptitude matrix with color-coded grade visualization
- The system SHALL integrate character avatars from existing image assets

#### 4.1.4 Training Interface

- The system SHALL display all training options with predicted outcomes and recommendations
- The system SHALL show support card participation and friendship training opportunities
- The system SHALL highlight red "!" indicators for guaranteed skill hints
- The system SHALL provide Spirit Burst status and team synergy information for Unity Cup

#### 4.1.5 Race Interface

- The system SHALL display race calendar with upcoming events and deadlines
- The system SHALL show race details including requirements and character readiness assessment
- The system SHALL provide strategy selection with performance predictions
- The system SHALL track race results and performance analytics

### 4.2 Hardware Interfaces

#### 4.2.1 Local Hardware Requirements

- The system SHALL run on Windows 10/11 systems with minimum 4GB RAM
- The system SHALL utilize local storage for database and file management
- The system SHALL support screenshot capture and image processing capabilities
- The system SHALL work with standard input devices (keyboard, mouse, touchscreen)

#### 4.2.2 Mobile Device Support

- The system SHALL provide responsive design for tablets (768px+ width)
- The system SHALL support touch interfaces with appropriate gesture recognition
- The system SHALL optimize performance for mobile processors and memory constraints
- The system SHALL provide offline functionality through service workers

### 4.3 Software Interfaces

#### 4.3.1 Database Interface

- The system SHALL interface with MySQL 8.0+ database for persistent data storage
- The system SHALL use Redis for caching, session management, and queue processing
- The system SHALL implement connection pooling for optimal database performance
- The system SHALL provide database backup and restore functionality

#### 4.3.2 AI Service Interfaces

- The system SHALL interface with Ollama via cloudstudio/ollama-laravel package
- The system SHALL connect to AWS Bedrock API for cloud AI processing
- The system SHALL implement intelligent routing between local and cloud AI services
- The system SHALL handle API rate limiting and error recovery

#### 4.3.3 External API Interfaces

- The system SHALL integrate with umapyoi.net API for Japanese game data
- The system SHALL connect to UmamusumeDB.com for calculator tools and community data
- The system SHALL implement fallback mechanisms for API unavailability
- The system SHALL cache external data with appropriate TTL management

#### 4.3.4 OCR Interface

- The system SHALL interface with Tesseract OCR engine for text extraction with Japanese language support and confidence scoring for extraction accuracy
- The system SHALL use OpenCV for image preprocessing and enhancement including noise reduction, contrast adjustment, and text region detection
- The system SHALL implement confidence scoring for extraction accuracy with validation against known game parameters and error detection
- The system SHALL provide manual correction interface for OCR errors with user-friendly editing capabilities and data validation

#### 4.3.5 MCP Server Interfaces

- The system SHALL interface with AWS MCP servers for streamlined Bedrock integration with automated credential management and cost optimization
- The system SHALL connect to external API MCP servers for enhanced umapyoi.net and UmamusumeDB.com integration with intelligent fallback mechanisms
- The system SHALL utilize development tool MCP servers for automated code quality checks, testing assistance, and deployment automation
- The system SHALL implement secure credential management and access control for MCP servers with proper configuration at workspace and user levels

#### 4.3.6 Subagent Coordination Interfaces

- The system SHALL interface with context-gatherer subagent for codebase analysis and feature investigation with efficient task delegation and result integration
- The system SHALL connect to general-task-execution subagent for parallel development tasks including testing automation and documentation generation
- The system SHALL implement quality assurance coordination between main development process and subagents with comprehensive result validation
- The system SHALL provide performance optimization through subagent utilization while maintaining architectural consistency and code quality standardsnhancement
- The system SHALL implement confidence scoring for extraction accuracy
- The system SHALL provide manual correction interface for OCR errors

### 4.4 Communications Interfaces

#### 4.4.1 HTTP/HTTPS Communication

- The system SHALL use HTTPS for all external API communications
- The system SHALL implement proper SSL certificate validation
- The system SHALL handle network timeouts and retry mechanisms
- The system SHALL provide offline mode when network is unavailable

#### 4.4.2 WebSocket Communication

- The system SHALL implement real-time updates using Laravel Reverb
- The system SHALL provide bidirectional communication for instant data synchronization
- The system SHALL handle connection drops and automatic reconnection
- The system SHALL optimize message frequency to prevent performance issues

#### 4.4.3 API Communication Standards

- The system SHALL implement RESTful API design principles
- The system SHALL use JSON for data exchange formats
- The system SHALL provide comprehensive error handling and status codes
- The system SHALL implement API versioning for future compatibility

---

## 5. Non-Functional Requirements

### 5.1 Performance Requirements

#### 5.1.1 Response Time Requirements

- The system SHALL provide response times under 2 seconds for core features (character data, training predictions, skill management) with database optimization and Redis caching
- The system SHALL load initial Progressive Web App interface within 3 seconds on standard hardware with service worker caching and asset optimization
- The system SHALL process AI queries within 3 seconds for local Ollama processing and 5 seconds for AWS Bedrock fallback with intelligent routing based on complexity
- The system SHALL complete database queries within 500ms for standard operations using proper indexing, connection pooling, and Eloquent strict mode optimization
- The system SHALL achieve Core Web Vitals compliance with LCP <2.5s, INP <200ms, CLS <0.1 through performance optimization and modern web standards

#### 5.1.2 Throughput Requirements

- The system SHALL handle concurrent training predictions without performance degradation using efficient caching and background processing
- The system SHALL process multiple AI conversations simultaneously with proper resource management and queue processing
- The system SHALL support batch operations for data import/export with progress tracking and error handling
- The system SHALL maintain performance with databases containing 1000+ career records using optimized indexing and query strategies
- The system SHALL process OCR screenshot analysis within 10 seconds including Tesseract processing and OpenCV preprocessing

#### 5.1.3 Resource Utilization

- The system SHALL use maximum 2GB RAM during normal operation with efficient memory management and garbage collection
- The system SHALL optimize database queries to minimize CPU usage through proper indexing, query optimization, and connection pooling
- The system SHALL implement efficient Redis caching to reduce external API calls with intelligent cache warming and invalidation strategies
- The system SHALL provide lazy loading for large datasets and images with Progressive Web App optimization and service worker caching
- The system SHALL optimize AI model usage to balance local processing capabilities with cloud fallback costs

### 5.2 Safety Requirements

#### 5.2.1 Data Protection

- The system SHALL prevent data loss through automatic backup mechanisms
- The system SHALL validate all user inputs to prevent data corruption
- The system SHALL implement transaction rollback for failed operations
- The system SHALL provide data recovery options for system failures

#### 5.2.2 Error Handling

- The system SHALL gracefully handle all error conditions without data loss
- The system SHALL provide meaningful error messages to users
- The system SHALL log errors for debugging and system monitoring
- The system SHALL implement circuit breakers for external service failures

### 5.3 Security Requirements

#### 5.3.1 Authentication and Authorization

- The system SHALL implement Laravel Sanctum for secure API authentication
- The system SHALL provide session management with appropriate timeouts
- The system SHALL implement rate limiting to prevent abuse
- The system SHALL validate all user permissions before data access

#### 5.3.2 Data Security

- The system SHALL encrypt sensitive data at rest using industry standards
- The system SHALL implement secure communication protocols (HTTPS/TLS)
- The system SHALL sanitize all user inputs to prevent injection attacks
- The system SHALL provide secure backup and restore mechanisms

#### 5.3.3 Privacy Protection

- The system SHALL store all personal data locally by default
- The system SHALL require explicit consent for any external data transmission
- The system SHALL provide data deletion capabilities for privacy compliance
- The system SHALL implement data anonymization for analytics

### 5.4 Software Quality Attributes

#### 5.4.1 Reliability

- The system SHALL maintain 99.9% uptime during normal operation
- The system SHALL recover automatically from transient failures
- The system SHALL provide data consistency across all operations
- The system SHALL implement comprehensive error logging and monitoring

#### 5.4.2 Availability

- The system SHALL provide offline functionality for core features
- The system SHALL implement graceful degradation when external services are unavailable
- The system SHALL maintain service during database maintenance operations
- The system SHALL provide status indicators for system health

#### 5.4.3 Maintainability

- The system SHALL follow Laravel 12 coding standards and best practices
- The system SHALL implement comprehensive unit and integration tests
- The system SHALL provide clear documentation for all components
- The system SHALL use modular architecture for easy updates and modifications

#### 5.4.4 Portability

- The system SHALL run on Windows 10/11 with XAMPP environment
- The system SHALL support modern web browsers (Chrome 90+, Firefox 88+, Safari 14+, Edge 90+)
- The system SHALL provide responsive design for various screen sizes
- The system SHALL implement Progressive Web App standards for cross-platform compatibility

### 5.5 Accessibility Requirements

#### 5.5.1 WCAG 2.2 AA Compliance

- The system SHALL provide keyboard navigation for all interactive elements
- The system SHALL implement proper contrast ratios (4.5:1 for normal text, 3:1 for large text)
- The system SHALL include alternative text for all meaningful images
- The system SHALL provide screen reader compatibility with proper ARIA attributes

#### 5.5.2 Inclusive Design

- The system SHALL support text resizing up to 200% without content loss
- The system SHALL provide focus indicators with 3:1 contrast ratio
- The system SHALL implement logical tab order for keyboard navigation
- The system SHALL offer multiple ways to access the same information

#### 5.5.3 Internationalization

- The system SHALL support UTF-8 character encoding for international text
- The system SHALL provide framework for future language localization
- The system SHALL handle right-to-left text display if needed
- The system SHALL format dates, numbers, and currencies according to locale

---

## 6. Other Requirements

### 6.1 Legal Requirements

#### 6.1.1 Intellectual Property

- The system SHALL respect all intellectual property rights of Umamusume Pretty Derby
- The system SHALL use only publicly available game data and community resources
- The system SHALL provide proper attribution for external data sources
- The system SHALL comply with fair use guidelines for game-related content

#### 6.1.2 Terms of Service Compliance

- The system SHALL comply with terms of service for all external APIs
- The system SHALL respect rate limits and usage guidelines
- The system SHALL implement proper error handling for API restrictions
- The system SHALL provide mechanisms to disable external integrations if required

### 6.2 Standards Compliance

#### 6.2.1 Web Standards

- The system SHALL comply with HTML5, CSS3, and ES2024+ JavaScript standards
- The system SHALL implement Progressive Web App (PWA) standards
- The system SHALL follow RESTful API design principles
- The system SHALL use semantic HTML for accessibility and SEO

#### 6.2.2 Security Standards

- The system SHALL implement OWASP security guidelines
- The system SHALL use industry-standard encryption methods
- The system SHALL follow secure coding practices
- The system SHALL implement proper input validation and sanitization

### 6.3 Environmental Requirements

#### 6.3.1 Operating Environment

- The system SHALL operate in typical home/office environments
- The system SHALL function with standard internet connectivity
- The system SHALL work with varying network speeds and reliability
- The system SHALL adapt to different time zones and regional settings

#### 6.3.2 Deployment Environment

- The system SHALL deploy on local XAMPP installations
- The system SHALL support Windows 10/11 operating systems
- The system SHALL work with standard hardware configurations
- The system SHALL provide installation and setup documentation

---

## 7. Appendices

### 7.1 Glossary

**Aptitudes**: Fixed talent ratings (G through SS) for distances, surfaces, and running styles that cannot be changed through training

**Factor Inheritance**: System where completed characters provide bonuses to new characters through Blue stat factors, Red aptitude factors, Green unique skill factors, and White normal skill factors

**Friendship Training**: Rainbow training unlocked at 80% bond levels providing enhanced stat bonuses through multiple participant effects

**Red "!" Indicators**: Visual indicators on training options guaranteeing skill hint acquisition

**Skill Evolution**: System where Normal skills evolve to Rare counterparts that completely replace the original skill

**Spirit Burst**: Unity Cup mechanic where 4 training sessions with teammates fill meter for large stat bonuses

**Support Card Deck**: 6-card configuration (5 owned + 1 friend) that provides training bonuses and skill hints

**Unity Cup**: Team-based career scenario involving multiple characters and team tournaments with facility levels

**URA Finale**: Primary career scenario focusing on individual character development

### 7.2 Analysis Models

#### 7.2.1 Data Flow Diagrams

- Context Diagram (Level 0): System boundary and external entities
- Level 1 DFD: Major system processes (8 core processes)
- Level 2 DFD: Detailed process breakdowns for critical functions

#### 7.2.2 Entity Relationship Diagrams

- Conceptual ERD: High-level entity relationships
- Logical ERD: Detailed database schema with 15+ major tables
- Physical ERD: Implementation-specific database design

#### 7.2.3 Use Case Diagrams

- Primary Actor: Individual Player with 8 major use cases
- Secondary Actor: Community Member with 3 use cases
- System Actor: External APIs and AI services

### 7.3 To Be Determined List

#### 7.3.1 External API Verification

- **UmamusumeDB.com**: Confirm current availability and API access methods
- **Community APIs**: Identify additional reliable sources for meta data
- **Rate Limits**: Determine exact rate limits for all external APIs

#### 7.3.2 Performance Optimization

- **Database Indexing**: Finalize indexing strategy based on query patterns
- **Caching Strategy**: Optimize cache TTL values based on data update frequencies
- **AI Model Selection**: Fine-tune model selection criteria for optimal performance

#### 7.3.3 Future Enhancements

- **Multi-User Support**: Architecture considerations for future cloud migration
- **Mobile App**: Native mobile application development requirements
- **Advanced Analytics**: Machine learning model integration for predictive analytics

---

### Document Control

| Version | Date             | Author           | Changes                           |
|---------|------------------|------------------|-----------------------------------|
| 1.0     | January 10, 2026 | Development Team | Initial SRS document creation     |

### Approval

| Role                | Name   | Signature   | Date   |
|---------------------|--------|-------------|--------|
| Project Manager     | [Name] | [Signature] | [Date] |
| Lead Developer      | [Name] | [Signature] | [Date] |
| Quality Assurance   | [Name] | [Signature] | [Date] |

---

*This document contains 59 comprehensive requirements organized into 8 major functional areas, supporting the complete implementation of the Umamusume Pretty Derby Career Planner system with advanced game mechanics, hybrid AI processing, MCP server integration, and subagent coordination for enhanced development efficiency.*

**Key Technical Achievements:**

- **Comprehensive Game Mechanics**: Complete implementation of skill evolution chains, hint-based SP optimization, weather systems, Unity Cup Spirit Burst mechanics, and turn economy management
- **Hybrid AI Architecture**: Local Ollama models with AWS Bedrock fallback providing intelligent recommendations while maintaining privacy and cost optimization
- **Progressive Web App**: Modern web standards with offline functionality, service workers, background sync, and WCAG 2.2 AA accessibility compliance
- **MCP Integration**: Model Context Protocol servers for enhanced development workflow, AWS services management, and external API coordination
- **Subagent Utilization**: Context-gatherer and general-task-execution subagents for efficient development and parallel task processing
- **External API Integration**: Intelligent integration with umapyoi.net and UmamusumeDB.com with fallback mechanisms and Redis-based caching
- **OCR Processing**: Tesseract with OpenCV preprocessing for automated screenshot analysis with Japanese language support

**Development Approach:**

- **28-week development timeline** with 6 comprehensive phases ensuring thorough implementation of all requirements
- **Context-gatherer subagent deployment** for codebase analysis and complex feature investigation
- **MCP server integration** from project inception for enhanced development workflow and service coordination
- **Comprehensive testing strategy** with 80%+ coverage target and accessibility compliance validation
- **Performance optimization** with Core Web Vitals compliance and database optimization throughout development

The system represents a significant advancement in Umamusume Pretty Derby optimization tools, combining cutting-edge technology with comprehensive game mechanics support to provide players with the most advanced career planning capabilities available.
