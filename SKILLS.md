# Skills Required for Uma Musume Career Planner

This document outlines the technical skills and knowledge areas required to effectively contribute to and maintain the Uma Musume Career Planner application.

## Core Technical Skills

### Backend Development

#### PHP & Laravel Framework

- **PHP 8.2+**: Modern PHP features including attributes, enums, typed properties, and union types
- **Laravel 12**: Latest Laravel framework features and conventions
  - Eloquent ORM and advanced query building
  - Service container and dependency injection
  - Middleware and request lifecycle
  - Route model binding and resource controllers
  - Database migrations and schema design
  - Queue management with Laravel Horizon
  - Real-time debugging with Laravel Telescope
  - API development with Laravel Sanctum

#### Database & Data Management

- **MySQL/PostgreSQL**: Relational database design and optimization
- **Database Migrations**: Schema versioning and rollback strategies
- **Eloquent Relationships**: Complex model relationships (hasMany, belongsTo, morphMany, etc.)
- **Query Optimization**: Indexes, foreign keys, and performance tuning
- **Data Modeling**: Understanding of Uma Musume game mechanics and data structures

### Frontend Development

#### JavaScript & Modern Web

- **Vanilla JavaScript ES6+**: Modern JavaScript features and syntax
- **Vite**: Build tool configuration and optimization
- **Axios**: HTTP client for API requests
- **Tailwind CSS v4**: Utility-first CSS framework

#### UI/UX Development

- **Responsive Design**: Mobile-first approach using Tailwind CSS
- **Component Architecture**: Modular and reusable UI components
- **Form Handling**: Validation and user input management
- **Performance Optimization**: Asset optimization and lazy loading

### Testing & Quality Assurance

#### Testing Frameworks

- **Pest v3**: Modern PHP testing framework
- **PHPUnit v11**: Traditional PHP unit testing
- **Feature Testing**: End-to-end application testing
- **Unit Testing**: Isolated component testing

#### Code Quality Tools

- **Laravel Pint**: PHP code style fixer and formatter
- **Static Analysis**: Code quality and bug detection
- **Test-Driven Development**: Writing tests before implementation

### DevOps & Infrastructure

#### Development Environment

- **Composer**: PHP dependency management
- **NPM**: JavaScript package management
- **Laravel Sail**: Docker-based development environment
- **Git**: Version control and workflow

#### Deployment & Monitoring

- **Queue Workers**: Background job processing with Laravel Horizon
- **Application Monitoring**: Laravel Telescope for debugging
- **Log Management**: Laravel Pail for real-time log monitoring
- **Environment Configuration**: Managing .env files and configurations

## Domain-Specific Knowledge

### Uma Musume Game Mechanics

#### Character Management

- **Character Templates**: Base character data and templates
- **Character Stats**: Speed, Stamina, Power, Guts, Wit (0-1200 range)
- **Stat Priorities**: Priority ratings (★ to ★★★★★)
- **Stat Breakpoints**: Understanding breakpoints at 901 and 1600
- **Character State**: Energy levels, mood status, and conditions

#### Skills System

- **Skill Types**: Speed skills, passive skills, recovery skills, debuff skills, unique skills
- **Skill Rarity**: Normal (120-180 SP), Rare (180-240 SP), Unique (variable SP)
- **Skill Evolution**: Normal to Rare skill evolution mechanics
- **Skill Acquisition**: Support cards, events, and inheritance sources
- **Skill Synergies**: Understanding skill combinations and meta strategies
- **Meta Tier Rankings**: S+, S, A, B, C tier skill classifications

#### Career Management

- **Scenario Types**: URA Finale and Unity Cup scenarios
- **Career Phases**: Junior, Classic, and Senior stages
- **Turn Management**: 72-78 turn system (varies by scenario)
- **Training Sessions**: Training type selection and optimization
- **Race Schedule**: Race planning and calendar management
- **Performance Metrics**: Win rates, fan counts, and race results

#### Support Cards & Events

- **Support Card Types**: Speed, Stamina, Power, Guts, Wit, Friend cards
- **Event System**: Triggered events and outcomes
- **Skill Hints**: Acquiring skill hints from support cards and events

### Strategic Planning

#### Career Optimization

- **Goal Setting**: Target stats and race objectives
- **Resource Management**: SP (Skill Points) budgeting and allocation
- **Turn Planning**: Optimal training and race schedules
- **Risk Assessment**: Managing conditions and energy levels
- **Meta Strategy**: Understanding current meta and tier lists

#### Data Analysis

- **Performance Tracking**: Analyzing career outcomes and patterns
- **Stat Optimization**: Reaching optimal stat distributions
- **Skill Portfolio**: Building effective skill sets
- **Comparative Analysis**: Evaluating different strategies and builds

## Advanced Technical Skills

### API Integration

#### MCP (Model Context Protocol)

- **MCP Client Service**: Understanding MCP server configuration
- **Server Capabilities**: Working with MCP server features
- **Health Checks**: Monitoring MCP server status
- **Debug Logging**: Troubleshooting MCP operations

#### External Data Sources

- **AWS SDK**: Integration with AWS services
- **Third-party APIs**: Consuming external game data APIs
- **Data Synchronization**: Keeping game data up-to-date

### AI & Machine Learning (Future Considerations)

#### AI Integration

- **Ollama Laravel**: Local LLM integration for AI assistance
- **AI Conversations**: Managing AI-powered career planning suggestions
- **Pattern Recognition**: Identifying optimal strategies from data

### Performance & Scalability

#### Optimization Techniques

- **Database Indexing**: Strategic index creation for query performance
- **Caching Strategies**: Redis/Memcached for performance
- **Query Optimization**: N+1 query prevention and eager loading
- **Asset Optimization**: Vite build optimization and code splitting

#### Monitoring & Debugging

- **Laravel Telescope**: Request/response inspection and debugging
- **Laravel Horizon**: Queue monitoring and job management
- **Laravel Pail**: Real-time log streaming and analysis
- **Performance Profiling**: Identifying and resolving bottlenecks

## Soft Skills & Best Practices

### Development Practices

#### Code Standards

- **PSR Standards**: PSR-12 coding style guidelines
- **Laravel Conventions**: Following Laravel best practices
- **Documentation**: Clear and concise code documentation
- **Type Safety**: Using PHP type hints and return types

#### Collaboration

- **Git Workflow**: Branch management and pull requests
- **Code Review**: Reviewing and providing constructive feedback
- **Issue Tracking**: Managing tasks and bug reports
- **Communication**: Clear technical communication

### Problem Solving

#### Analytical Thinking

- **Debugging**: Systematic problem identification and resolution
- **Performance Analysis**: Identifying and fixing performance issues
- **Architecture Design**: Planning scalable and maintainable solutions
- **Trade-off Analysis**: Evaluating technical decisions

#### Learning & Adaptation

- **Framework Updates**: Staying current with Laravel updates
- **Game Mechanics**: Keeping up with Uma Musume game changes
- **Technology Trends**: Adopting new tools and practices appropriately
- **Documentation**: Reading and understanding technical documentation

## Getting Started

### Minimum Requirements

To begin contributing to this project, you should have:

1. **Core PHP & Laravel Knowledge**: Understanding of PHP 8.2+ and Laravel 12 fundamentals
2. **Database Skills**: Basic SQL and migration experience
3. **Frontend Basics**: HTML, CSS, JavaScript fundamentals
4. **Version Control**: Git workflow and GitHub usage
5. **Testing Mindset**: Understanding of test-driven development

### Recommended Learning Path

1. **Set up Development Environment**: Install PHP, Composer, Node.js, and configure Laravel Sail
2. **Explore the Codebase**: Review migrations, models, and existing features
3. **Run Tests**: Execute `composer run test` to understand test structure
4. **Study Game Mechanics**: Learn Uma Musume character progression and skill systems
5. **Start Small**: Begin with bug fixes or documentation improvements
6. **Build Features**: Progress to implementing new features with guidance
7. **Optimize**: Work on performance improvements and advanced features

## Resources

### Official Documentation

- [Laravel Documentation](https://laravel.com/docs)
- [Pest PHP Testing](https://pestphp.com)
- [Tailwind CSS](https://tailwindcss.com)
- [Vite Documentation](https://vitejs.dev)

### Project-Specific Guides

- `AGENTS.md` - AI agent development guidelines
- `README.md` - Project setup and overview
- Database migrations - Understanding data structures

### Community Resources

- Uma Musume game wikis and guides
- Laravel community forums and Discord
- PHP and web development resources

## Contribution Guidelines

Before contributing, ensure you:

1. Follow the coding standards outlined in `AGENTS.md`
2. Write tests for new features and bug fixes
3. Update documentation when adding new functionality
4. Use Laravel Pint for code formatting: `./vendor/bin/pint`
5. Verify your changes don't break existing functionality
6. Keep commits focused and write clear commit messages

## Conclusion

This project combines web application development with deep domain knowledge of Uma Musume game mechanics. Success requires both technical proficiency in the Laravel ecosystem and understanding of the strategic elements of career planning and skill optimization in the game. Start with the fundamentals, gradually build domain knowledge, and don't hesitate to ask questions or refer to existing code patterns.
