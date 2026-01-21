# Product Overview

## Umamusume Pretty Derby Career Planner

A comprehensive local-first web application for optimizing gameplay in the Umamusume Pretty Derby mobile game. The system replaces manual tracking methods with intelligent AI-powered optimization.

### Core Purpose

Help players achieve A+ grade character ratings consistently through data-driven decision making in both URA Finale and Unity Cup scenarios.

### Key Features

- **AI-Powered Training Optimization**: Hybrid local (Ollama) and cloud (AWS Bedrock) AI for turn-by-turn recommendations
- **Comprehensive Character Management**: Stats (0-1200 range), aptitudes (G-SS ratings), factors, and progression tracking
- **Advanced Skill System**: Evolution chains, hint-based SP cost reduction (20% per duplicate, 40% max), strategic acquisition
- **Race Strategy Analysis**: Weather conditions, track characteristics, performance predictions
- **Support Card Management**: 6-card deck configuration with friendship tracking and meta tier rankings
- **Career Analytics**: Historical analysis across 60-70 turn careers with pattern recognition
- **OCR Screenshot Processing**: Tesseract with OpenCV for automated game state extraction (Japanese language support)
- **Progressive Web App**: Offline functionality, installable experience, WCAG 2.2 AA accessibility compliance

### Architecture Principles

- **Local-First**: All personal data stored locally (MySQL + Redis via WSL)
- **Privacy-Focused**: No data transmission without explicit consent
- **Hybrid AI**: Local models primary, cloud fallback for complex tasks
- **Accessibility**: WCAG 2.2 AA compliant with comprehensive keyboard navigation and screen reader support
- **Performance**: Sub-2-second response times, Core Web Vitals compliance

### Target Users

Intermediate to advanced Umamusume players seeking consistent A+ grade achievements through systematic optimization and strategic planning.
