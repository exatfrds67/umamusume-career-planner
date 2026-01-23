# Implementation Plan: Neuron AI Integration

## Overview

This implementation plan breaks down the Neuron AI integration into discrete, manageable tasks. Each task builds on previous work to incrementally add AI agent capabilities to the Uma Musume Career Planner application.

## Tasks

- [x] 1. Install Neuron AI packages and create base configuration
  - Run `composer require neuron-core/neuron-ai neuron-core/neuron-laravel`
  - Publish configuration: `php artisan vendor:publish --tag=neuron-config`
  - Publish migrations: `php artisan vendor:publish --tag=neuron-migrations`
  - Run migrations: `php artisan migrate --path=/database/migrations/neuron`
  - Add environment variables to `.env.example` (NEURON_AI_PROVIDER, ANTHROPIC_KEY, OPENAI_KEY, etc.)
  - Configure default provider in `config/neuron.php`
  - _Requirements: 1.1, 1.2, 1.3, 1.5, 1.6, 2.1, 2.2, 2.4, 2.5, 2.7, 15.1, 15.2, 15.3_

- [x]* 1.1 Write unit tests for configuration loading
  - Test config file structure
  - Test environment variable overrides
  - Test provider configuration retrieval
  - _Requirements: 15.1, 15.2, 15.3, 15.4_

- [x]* 1.2 Write property test for configuration environment overrides
  - **Property 13: Configuration Environment Overrides**
  - **Validates: Requirements 15.4**

- [x] 2. Create base agent architecture
  - [x] 2.1 Create `app/Neuron/Agents/BaseAgent.php` abstract class
    - Extend Neuron `Agent` class
    - Implement `provider()` method using `AIProvider::driver()` facade
    - Add `buildSystemPrompt()` helper method
    - Add `chatHistory()` method using `EloquentChatHistory`
    - Add abstract `getSessionId()` method
    - _Requirements: 3.1, 3.3, 3.4, 3.5, 11.1, 11.3_

  - [x]* 2.2 Write unit tests for BaseAgent
    - Test provider selection logic
    - Test system prompt building
    - Test chat history methods
    - _Requirements: 3.1, 3.3, 3.4, 3.5_

  - [x]* 2.3 Write property test for provider configuration retrieval
    - **Property 1: Provider Configuration Retrieval**
    - **Validates: Requirements 2.5**

  - [x]* 2.4 Write property test for invalid provider configuration exceptions
    - **Property 2: Invalid Provider Configuration Exceptions**
    - **Validates: Requirements 2.6**

- [x] 3. Verify EloquentChatHistory integration
  - [x] 3.1 Verify migration was published correctly
    - Check `database/migrations/neuron/` for chat history migration
    - Verify table structure matches EloquentChatHistory requirements
    - _Requirements: 11.1, 11.2, 11.3, 11.4_

  - [x] 3.2 Test EloquentChatHistory functionality
    - Create test agent with chat history
    - Verify messages are persisted correctly
    - Verify messages are retrieved on subsequent calls
    - _Requirements: 11.1, 11.2, 11.3, 11.4_

  - [x]* 3.3 Write property test for chat history persistence
    - **Property 3: Chat History Persistence**
    - **Validates: Requirements 11.1**

  - [x]* 3.4 Write property test for chat history retrieval
    - **Property 4: Chat History Retrieval**
    - **Validates: Requirements 11.2**

  - [~]* 3.5 Write property test for chat history user association
    - **Property 5: Chat History User Association**
    - **Validates: Requirements 11.3**

  - [x]* 3.6 Write property test for chat history scoping
    - **Property 6: Chat History Scoping**
    - **Validates: Requirements 11.4**

- [x] 4. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 5. Create tool implementations
  - [x] 5.1 Create `app/Neuron/Tools/CharacterStatsTool.php`
    - Use `php artisan neuron:tool CharacterStatsTool` command
    - Implement tool for retrieving character statistics
    - Format output for LLM consumption
    - _Requirements: 10.1, 10.4, 10.5_

  - [x] 5.2 Create `app/Neuron/Tools/SkillDataTool.php`
    - Use `php artisan neuron:tool SkillDataTool` command
    - Implement tool for retrieving skill information
    - Format output for LLM consumption
    - _Requirements: 10.2, 10.4, 10.5_

  - [x] 5.3 Create `app/Neuron/Tools/RaceDataTool.php`
    - Use `php artisan neuron:tool RaceDataTool` command
    - Implement tool for accessing race data
    - Format output for LLM consumption
    - _Requirements: 10.3, 10.4, 10.5_

  - [x]* 5.4 Write unit tests for tools
    - Test tool registration
    - Test tool callable execution
    - Test tool parameter validation
    - _Requirements: 10.1, 10.2, 10.3, 10.4_

  - [x]* 5.5 Write property test for tool output format
    - **Property 11: Tool Output Format**
    - **Validates: Requirements 10.5**

- [x] 6. Create structured output response classes
  - [x] 6.1 Create `app/Neuron/Responses/TrainingAdviceResponse.php`
    - Define schema with SchemaProperty attributes
    - Add validation rules
    - _Requirements: 9.1, 9.2, 9.3_

  - [x] 6.2 Create `app/Neuron/Responses/RaceStrategyResponse.php`
    - Define schema with SchemaProperty attributes
    - Add validation rules
    - _Requirements: 9.1, 9.2, 9.3_

  - [x] 6.3 Create `app/Neuron/Responses/SkillRecommendationResponse.php`
    - Define schema with SchemaProperty attributes
    - Add validation rules
    - _Requirements: 9.1, 9.2, 9.3_

  - [x]* 6.4 Write property test for structured output type safety
    - **Property 9: Structured Output Type Safety**
    - **Validates: Requirements 9.2**

  - [x]* 6.5 Write property test for structured output validation
    - **Property 10: Structured Output Validation**
    - **Validates: Requirements 9.3**

- [x] 7. Create Training Advisor Agent
  - [x] 7.1 Create `app/Neuron/Agents/TrainingAdvisorAgent.php`
    - Use `php artisan neuron:agent TrainingAdvisorAgent` command
    - Extend BaseAgent
    - Implement `provider()` using `AIProvider::driver('anthropic')`
    - Implement `instructions()` with SystemPrompt for training advice
    - Implement `chatHistory()` using EloquentChatHistory
    - Register character stats and training tools in `tools()` method
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6_

  - [x]* 7.2 Write feature tests for Training Advisor Agent
    - Test agent receives proper context
    - Test agent returns structured response
    - Test conversation context maintenance
    - Mock LLM responses
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6_

- [x] 8. Create Race Strategy Agent
  - [x] 8.1 Create `app/Neuron/Agents/RaceStrategyAgent.php`
    - Use `php artisan neuron:agent RaceStrategyAgent` command
    - Extend BaseAgent
    - Implement `provider()` using `AIProvider::driver('anthropic')`
    - Implement `instructions()` with SystemPrompt for race strategy
    - Implement `chatHistory()` using EloquentChatHistory
    - Register race data and character capability tools in `tools()` method
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5_

  - [x]* 8.2 Write feature tests for Race Strategy Agent
    - Test agent receives proper context
    - Test agent returns structured response
    - Mock LLM responses
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5_

- [x] 9. Create Skill Recommendation Agent
  - [x] 9.1 Create `app/Neuron/Agents/SkillRecommendationAgent.php`
    - Use `php artisan neuron:agent SkillRecommendationAgent` command
    - Extend BaseAgent
    - Implement `provider()` using `AIProvider::driver('anthropic')`
    - Implement `instructions()` with SystemPrompt for skill recommendations
    - Implement `chatHistory()` using EloquentChatHistory
    - Register skill data tools in `tools()` method
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5_

  - [x]* 9.2 Write feature tests for Skill Recommendation Agent
    - Test agent receives proper context
    - Test agent returns structured response
    - Mock LLM responses
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5_

- [x] 10. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 11. Create agent service classes
  - [x] 11.1 Create `app/Services/Neuron/TrainingAdvisorService.php`
    - Inject Eloquent models via constructor
    - Implement data formatting methods
    - Implement response parsing methods
    - _Requirements: 7.1, 7.2, 7.3, 7.4_

  - [x] 11.2 Create `app/Services/Neuron/RaceStrategyService.php`
    - Inject Eloquent models via constructor
    - Implement data formatting methods
    - Implement response parsing methods
    - _Requirements: 7.1, 7.2, 7.3, 7.4_

  - [x] 11.3 Create `app/Services/Neuron/SkillRecommendationService.php`
    - Inject Eloquent models via constructor
    - Implement data formatting methods
    - Implement response parsing methods
    - _Requirements: 7.1, 7.2, 7.3, 7.4_

  - [x]* 11.4 Write unit tests for service classes
    - Test data formatting methods
    - Test response parsing methods
    - Test dependency injection
    - _Requirements: 7.1, 7.2, 7.3, 7.4_

  - [x]* 11.5 Write property test for data formatting
    - **Property 7: Data Formatting for Agents**
    - **Validates: Requirements 7.3**

  - [x]* 11.6 Write property test for response parsing
    - **Property 8: Response Parsing**
    - **Validates: Requirements 7.4**

- [x] 12. Create API controllers for agent interactions
  - [x] 12.1 Create `app/Http/Controllers/Api/TrainingAdvisorController.php`
    - Inject TrainingAdvisorService
    - Implement endpoint for getting training advice
    - Handle errors gracefully
    - _Requirements: 7.5, 12.1, 12.2, 12.3, 12.4, 12.5_

  - [x] 12.2 Create `app/Http/Controllers/Api/RaceStrategyController.php`
    - Inject RaceStrategyService
    - Implement endpoint for getting race strategy
    - Handle errors gracefully
    - _Requirements: 7.5, 12.1, 12.2, 12.3, 12.4, 12.5_

  - [x] 12.3 Create `app/Http/Controllers/Api/SkillRecommendationController.php`
    - Inject SkillRecommendationService
    - Implement endpoint for getting skill recommendations
    - Handle errors gracefully
    - _Requirements: 7.5, 12.1, 12.2, 12.3, 12.4, 12.5_

  - [x]* 12.4 Write property test for error logging
    - **Property 12: Error Logging**
    - **Validates: Requirements 12.3**

  - [x]* 12.5 Write feature tests for controllers
    - Test controller → service → agent flow
    - Test error handling
    - Mock LLM responses
    - _Requirements: 7.5, 12.1, 12.2, 12.4, 12.5_

- [x] 13. Implement streaming response support
  - [x] 13.1 Create streaming endpoint in controllers
    - Implement SSE response format
    - Handle streaming errors with fallback
    - _Requirements: 8.1, 8.2, 8.4, 8.5_

  - [x]* 13.2 Write property test for streaming response chunks
    - **Property 14: Streaming Response Chunks**
    - **Validates: Requirements 8.2**

  - [x]* 13.3 Write feature tests for streaming
    - Test SSE endpoint
    - Test chunk delivery
    - Test error handling during streaming
    - _Requirements: 8.1, 8.2, 8.4, 8.5_

- [x] 14. Add API routes
  - Add routes for all agent controllers
  - Apply authentication middleware
  - Apply rate limiting
  - _Requirements: 7.5, 12.2_

- [x] 15. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 16. Configure Inspector monitoring (optional)
  - Add Inspector configuration to `.env.example`
  - Document Inspector setup in README
  - _Requirements: 1.3, 13.1, 13.5_

- [x]* 17. Implement MCP Connector integration (optional)
  - [x]* 17.1 Research available MCP servers for Uma Musume data
    - Explore MCP server directories
    - Identify relevant servers for game data
    - _Requirements: 17.1, 17.2_

  - [x]* 17.2 Create MCP connector configuration
    - Configure local or remote MCP servers
    - Set up authentication tokens if needed
    - Use `McpConnector` class to connect servers
    - _Requirements: 17.2, 17.3, 17.4_

  - [x]* 17.3 Integrate MCP tools with agents
    - Automatically discover tools from MCP servers
    - Filter tools using `exclude()` or `only()` methods
    - Test tool execution through agents
    - _Requirements: 17.5, 17.6_

- [x] 18. Create documentation
  - [x] 18.1 Create `docs/neuron/integration-guide.md`
    - Document installation steps
    - Document agent usage examples
    - Document tool creation with `php artisan neuron:tool`
    - Document troubleshooting
    - _Requirements: 16.1, 16.2, 16.3, 16.4, 16.5_

  - [x] 18.2 Update main README with Neuron AI section
    - Add overview of AI features
    - Link to integration guide
    - Document available Artisan commands
    - _Requirements: 16.1_

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties
- Unit tests validate specific examples and edge cases
- Mock LLM responses in tests to avoid external dependencies and costs
- Focus on core functionality first, then add optional features
