# Umamusume Pretty Derby Career Planner

A comprehensive career planning application for Umamusume Pretty Derby mobile game that helps players make strategic decisions during career mode training to achieve A-grade rankings. The system provides data-driven recommendations for optimal gameplay outcomes in both URA Finale and Unity Cup scenarios.

## 🎯 Overview

The Umamusume Career Planner is a Laravel 12-based web application designed to help players optimize their training strategies, race preparations, and resource management in Umamusume Pretty Derby. It features an AI-powered advisory system, comprehensive data analysis, and intelligent recommendations based on game mechanics and community meta.

### Key Features

- **🤖 AI-Powered Advisory Chatbot** - Intelligent recommendations using Ollama (local) with AWS Bedrock fallback
- **📊 Training Optimization Engine** - Advanced algorithms for optimal training decisions
- **🏆 Race Strategy Analysis** - Comprehensive race preparation and strategy recommendations  
- **💎 Skill Management System** - SP optimization with hint-based cost reduction mechanics
- **📈 Career Progress Tracking** - Historical analysis and performance metrics
- **🎮 Multi-Scenario Support** - Optimized for both URA Finale and Unity Cup scenarios
- **📱 Screenshot Analysis** - OCR-powered game state extraction from screenshots
- **🌐 Community Integration** - Real-time meta data and tier list synchronization
- **📋 Champions Meeting PvP** - 3v3v3 tournament team optimization
- **🎁 Resource Management** - Gacha planning, item optimization, and budget tracking

## 🏗️ System Architecture

### Core Components

1. **Data Collection & Integration** - External API synchronization and community data
2. **Screenshot Analysis & OCR** - Automated game state extraction
3. **AI Advisory System** - Ollama-first with AWS Bedrock fallback
4. **Training Optimization Engine** - Multi-algorithm decision optimization
5. **Career Management System** - Progress tracking and historical analysis
6. **PvP & Competition Analysis** - Champions Meeting and meta strategies
7. **Resource Management** - Items, gacha, and economic optimization
8. **Analytics & Reporting** - Performance metrics and statistical analysis

### Technology Stack

- **Backend**: Laravel 12 with MySQL database
- **Frontend**: Blade templates with responsive design
- **AI Models**: Ollama (local) + AWS Bedrock (Claude 4.5 Opus/Sonnet/Haiku, Nova 2 Lite/Pro)
- **External APIs**: UmaMusumeAPI, umapyoi.net, UmamusumeDB.com
- **Image Processing**: OCR for screenshot analysis
- **Caching**: Laravel cache system for performance optimization

## 🚀 Getting Started

### Prerequisites

- PHP 8.2+
- MySQL 8.0+
- Composer
- Node.js & NPM
- Ollama (optional, for local AI)
- AWS Account (optional, for cloud AI fallback)

### Installation

1. **Clone the repository**

   ```bash
   git clone https://github.com/your-username/umamusume-career-planner.git
   cd umamusume-career-planner
   ```

2. **Install dependencies**

   ```bash
   composer install
   npm install
   ```

3. **Environment setup**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**

   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Build assets**

   ```bash
   npm run build
   ```

6. **Start the application**

   ```bash
   php artisan serve
   ```

### Configuration

#### AI Models Setup

**Ollama (Local AI)**

```bash
# Install Ollama
curl -fsSL https://ollama.ai/install.sh | sh

# Pull recommended models
ollama pull llama2
ollama pull codellama
```

**AWS Bedrock (Cloud Fallback)**

```env
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
BEDROCK_REGION=us-east-1
```

#### External APIs

```env
# Updated API Configuration (January 2026)
# Note: SimpleSandman/UmaMusumeAPI deprecated October 2024
UMAPYOI_API_URL=https://umapyoi.net/api
UMAMUSUMEDB_API_URL=https://umamusumedb.com/api
```

## 📖 Usage Guide

### 1. Character Setup

1. **Create New Career**
   - Select character and scenario (URA Finale/Unity Cup)
   - Input base stats, aptitudes, and growth rates
   - Configure support card deck (5 owned + 1 friend)
   - Set career goals and target stats

2. **Legacy & Inheritance**
   - Select parent characters (2 main + 4 grandparents)
   - Configure factor inheritance (Blue/Red/Green/White factors)
   - Optimize affinity compatibility for maximum bonuses

### 2. Training Optimization

1. **Turn-by-Turn Guidance**
   - Upload screenshots or manually input game state
   - Receive AI-powered training recommendations
   - View predicted stat gains and skill hint opportunities
   - Track energy, mood, and condition management

2. **Skill Management**
   - Monitor skill hints and SP cost reductions (20% per duplicate, 40% max)
   - Plan skill evolution paths (Normal → Rare upgrades)
   - Optimize SP allocation strategies

### 3. Race Preparation

1. **Race Analysis**
   - View upcoming races with stat requirements
   - Get strategy recommendations based on aptitudes
   - Analyze weather conditions and track characteristics
   - Receive performance predictions

2. **Strategy Selection**
   - Choose optimal running styles (Front Runner/Pace Chaser/Late Surger/End Closer)
   - Adapt to weather conditions and competition strength
   - Plan race schedules for goal completion

### 4. AI Chatbot Interaction

1. **Ask Strategic Questions**
   - Get personalized advice based on current career state
   - Receive explanations for complex game mechanics
   - Upload screenshots for instant analysis

2. **Model Selection**
   - Local Ollama processing for privacy and speed
   - Automatic fallback to AWS Bedrock for complex queries
   - Transparent model usage disclosure

### 5. Champions Meeting PvP

1. **Team Building**
   - Create 3-character teams with role assignments
   - Optimize for monthly cup requirements
   - Track meta strategies and counter-compositions

2. **Tournament Management**
   - Monitor RP costs and reward tiers
   - Plan entry timing based on team readiness
   - Analyze performance against different opponents

## 📊 Features Deep Dive

### Training Optimization Engine

The core optimization system uses multiple algorithms to analyze:

- **Stat Prediction**: Expected gains based on support cards and friendship levels
- **Skill Hint Optimization**: Red "!" detection and SP cost reduction strategies
- **Energy Management**: Failure prevention and sustainable training progression
- **Turn Economy**: Optimal resource allocation across 60-70 turn careers
- **Weather Adaptation**: Strategy adjustments for different track conditions

### AI Advisory System

**Local Processing (Ollama)**

- Privacy-focused local AI processing
- Instant responses for common queries
- Conversation history persistence

**Cloud Fallback (AWS Bedrock)**

- Claude 4.5 Opus ($5/$25 per 1M tokens) for complex strategic analysis
- Claude 4.5 Sonnet ($3/$15 per 1M tokens) for balanced reasoning
- Claude 4.5 Haiku ($1/$5 per 1M tokens) for fast responses
- Nova 2 Lite ($0.00125 per 1K tokens) for cost-effective processing
- Nova 2 Pro (Preview) for advanced multimodal capabilities
- Automatic quality assessment and fallback triggers

### Data Integration

**External APIs**

- UmaMusumeAPI: Character and race data
- umapyoi.net: Japanese game data
- UmamusumeDB.com: Calculator tools and meta information

**Community Sources**

- Real-time tier lists and meta strategies
- Competitive performance data
- Strategy sharing and collaboration

## 🎮 Game Mechanics Coverage

### Character Development

- **Stats**: Speed (★★★★★), Stamina (★★★★), Power (★★★), Guts (★), Wit (★★)
- **Aptitudes**: Distance (Sprint/Mile/Medium/Long), Surface (Turf/Dirt), Running Styles
- **Growth Rates**: Inherited bonuses (+10%, +20%, +30%) affecting training efficiency

### Training Systems

- **Friendship Training**: Rainbow training at 80% bond levels with multi-participant bonuses
- **Summer Camps**: High-efficiency 4-turn periods (Early/Late July/August)
- **Spirit Burst**: Unity Cup team mechanics with gauge filling and stat bonuses
- **Skill Hints**: Cost reduction system with support card interactions

### Race Management

- **Race Calendar**: Complete Pre-OP through G1 race scheduling
- **Strategy Selection**: Running style optimization based on stats and conditions
- **Weather System**: Track condition impacts and weather-specific skills
- **Performance Analysis**: Detailed race outcome tracking and improvement identification

### Resource Optimization

- **Skill Points**: Strategic SP allocation with hint-based cost reduction
- **Items**: Training boosters, energy drinks, and facility upgrades
- **Gacha Planning**: Pity system management and banner analysis
- **Energy/Mood**: Sustainable training progression with failure prevention

## 📈 Analytics & Reporting

### Performance Metrics

- Training efficiency (stat gains per turn)
- Prediction accuracy (expected vs actual results)
- Goal completion rates and trajectory analysis
- Resource utilization optimization

### Historical Analysis

- Multi-career comparison and pattern recognition
- Success factor identification and strategy evolution
- Community benchmarking and meta adaptation
- Long-term improvement tracking

### Statistical Tools

- Monte Carlo simulations for outcome prediction
- Regression analysis for training effectiveness
- Confidence intervals and significance testing
- Machine learning model updates based on performance data

## 🔧 Development

### Project Structure

```
├── app/
│   ├── Http/Controllers/     # Web controllers
│   ├── Models/              # Eloquent models
│   ├── Services/            # Business logic services
│   └── Jobs/                # Background jobs
├── database/
│   ├── migrations/          # Database schema
│   └── seeders/            # Sample data
├── resources/
│   ├── views/              # Blade templates
│   └── js/                 # Frontend assets
├── docs/                   # Documentation
└── tests/                  # Test suites
```

### Key Services

- `TrainingOptimizationService`: Core training recommendation engine
- `AIAdvisoryService`: Ollama and AWS Bedrock integration
- `ScreenshotAnalysisService`: OCR and image processing
- `ExternalDataService`: API synchronization and caching
- `PerformanceAnalyticsService`: Metrics calculation and reporting

### Database Schema

- **15 major entity groups** covering all game aspects
- **Comprehensive relationships** between characters, careers, and performance data
- **Optimized indexing** for query performance
- **Data integrity constraints** ensuring consistency

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

### Development Setup

1. Fork the repository
2. Create a feature branch
3. Make your changes with tests
4. Submit a pull request

### Code Standards

- Follow PSR-12 coding standards
- Write comprehensive tests (PHPUnit)
- Document new features and APIs
- Maintain backward compatibility

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- **Cygames** for creating Umamusume Pretty Derby
- **Community Contributors** for game data and meta analysis
- **API Providers**: UmaMusumeAPI, umapyoi.net, UmamusumeDB.com
- **Open Source Libraries** used throughout the project

## 📞 Support

- **Documentation**: [Full documentation](docs/)
- **Issues**: [GitHub Issues](https://github.com/your-username/umamusume-career-planner/issues)
- **Discussions**: [GitHub Discussions](https://github.com/your-username/umamusume-career-planner/discussions)
- **Discord**: [Community Discord Server](https://discord.gg/your-server)

## 🗺️ Roadmap

### Phase 1: Core Features ✅

- Basic training optimization
- Character state management
- Simple AI recommendations

### Phase 2: Advanced Features 🚧

- Screenshot analysis and OCR
- Champions Meeting PvP optimization
- Advanced statistical analysis

### Phase 3: Community Features 📋

- Strategy sharing platform
- Real-time meta tracking
- Collaborative optimization tools

### Phase 4: Mobile & Extensions 🔮

- Mobile-responsive improvements
- Browser extension for game integration
- Advanced machine learning models

---

**Made with ❤️ for the Umamusume community**

*This project is not affiliated with Cygames or Umamusume Pretty Derby. All game data and mechanics are used for educational and optimization purposes only.*
