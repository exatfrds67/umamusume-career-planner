# MCP Server Configuration Reference

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0
**Date**: January 12, 2026
**Project**: UmamusumeCareerPlanner
**Author**: Development Team
**Status**: Complete

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [MCP Server Architecture](#2-mcp-server-architecture)
3. [Available MCP Servers](#3-available-mcp-servers)
4. [Configuration Patterns](#4-configuration-patterns)
5. [Subagent Coordination Strategy](#5-subagent-coordination-strategy)
6. [Health Monitoring and Management](#6-health-monitoring-and-management)
7. [Integration Guidelines](#7-integration-guidelines)

---

## 1. Executive Summary

This document provides the authoritative reference for all Model Context Protocol (MCP) server configurations in the Umamusume Career Planner project. The application utilizes 10 MCP servers to enhance AI capabilities, infrastructure management, and external integrations.

### Key MCP Integration Benefits

- **Enhanced AI Capabilities**: Advanced agent creation and orchestration via strands-agents and agentcore-mcp-server
- **Cost Optimization**: Real-time AWS pricing and cost management via awspricing server
- **Infrastructure Management**: AWS service integration and Infrastructure as Code validation
- **Context Management**: Enhanced conversation context and data processing capabilities
- **External Integration**: Improved HTTP client capabilities and API management
- **Persistent Memory**: Knowledge graph memory for AI agents across sessions
- **Design Integration**: Optional UI design consistency and asset management

---

## 2. MCP Server Architecture

### 2.1 MCP Integration Overview

```text
┌─────────────────────────────────────────────────────────────────┐
│                    MCP SERVER ECOSYSTEM                        │
├─────────────────────────────────────────────────────────────────┤
│  AI & Agent Services                                            │
│  ┌─────────────────┐ ┌─────────────────┐                       │
│  │ strands-agents  │ │ agentcore-mcp-  │                       │
│  │ Multi-model AI  │ │ server          │                       │
│  │ Agent Creation  │ │ Agent Platform  │                       │
│  └─────────────────┘ └─────────────────┘                       │
├─────────────────────────────────────────────────────────────────┤
│  AWS Infrastructure Services                                   │
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐   │
│  │ awspricing      │ │ awsknowledge    │ │ awsapi          │   │
│  │ Cost Tracking   │ │ Documentation   │ │ Service Mgmt    │   │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘   │
│  ┌─────────────────┐                                           │
│  │ awslabs.aws-iac-│                                           │
│  │ mcp-server      │                                           │
│  │ IaC Validation  │                                           │
│  └─────────────────┘                                           │
├─────────────────────────────────────────────────────────────────┤
│  Data & Context Services                                       │
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐   │
│  │ context7        │ │ fetch           │ │ memory          │   │
│  │ Context Mgmt    │ │ HTTP Client     │ │ Knowledge Graph │   │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘   │
├─────────────────────────────────────────────────────────────────┤
│  Optional Services                                              │
│  ┌─────────────────┐                                           │
│  │ figma           │                                           │
│  │ Design System   │                                           │
│  └─────────────────┘                                           │
└─────────────────────────────────────────────────────────────────┘
```

### 2.2 Current Laravel Service Implementation

The application implements 19 MCP service classes in `app/Services/MCP/`:

| Service | Purpose |
|---------|--------|
| `AgentOrchestrationService` | Multi-agent workflow coordination and result aggregation |
| `AgentCommunicationService` | Inter-agent messaging and event dispatching |
| `AgentContextService` | Cross-session context management for agents |
| `AgentLifecycleManager` | Agent lifecycle (start, stop, restart, health) |
| `AgentMemoryService` | Persistent knowledge graph memory for agents |
| `AgentRoutingService` | Intelligent routing of requests to appropriate agents |
| `APIPerformanceAnalyticsService` | API response time tracking and analytics |
| `CareerStateSyncService` | Career run state synchronization across agents |
| `CostManagementService` | AWS cost tracking and budget management |
| `CostOptimizationService` | Cost reduction strategies and recommendations |
| `FailureRateTrackingService` | Error rate monitoring and circuit breaker support |
| `MCPClientService` | Core MCP protocol client for server communication |
| `MCPHealthDashboardService` | Aggregated health dashboard data |
| `MCPMonitoringService` | Server health checks and alerting |
| `RealTimeMonitoringService` | Real-time metrics and performance monitoring |
| `SkillOptimizationOrchestrationService` | Multi-agent skill analysis orchestration |
| `SubagentCoordinationService` | Subagent task delegation and result merging |
| `TrainingOptimizationAgent` | Training decision analysis agent |
| `WorkflowTemplateService` | Predefined workflow template management |

Additionally, `app/MCP/SubagentCoordinationService.php` provides top-level subagent coordination.

### 2.3 MCP Server Categories

| Category | Servers | Purpose |
|----------|---------|---------|
| **AI & Agents** | strands-agents, agentcore-mcp-server | AI agent creation, orchestration, and management |
| **AWS Infrastructure** | awspricing, awsknowledge, awsapi, awslabs.aws-iac-mcp-server | Cost optimization, documentation, service management, IaC validation |
| **Data & Context** | context7, fetch, memory | Context management, HTTP operations, persistent memory |
| **Optional** | figma | UI design consistency and asset management |

---

## 3. Available MCP Servers

### 3.1 AI and Agent Services

#### 3.1.1 strands-agents

**Purpose**: Strands Agent SDK integration for multi-model AI agent creation and management

**Configuration**:

```yaml
server_name: strands-agents
command: uvx
args: ['strands-agents@latest']
enabled: true
```

**Capabilities**:

- Multi-model AI agent creation (Bedrock, Anthropic, OpenAI, Gemini, Llama)
- Workflow management and orchestration
- Tool integration and chaining
- Agent lifecycle management

**Use Cases**:

- Training Optimization Agent for complex training sequence planning
- Career Strategy Agent for long-term career planning and goal optimization
- Race Analysis Agent for race preparation and performance analysis
- Skill Management Agent for SP optimization and hint collection strategies

#### 3.1.2 agentcore-mcp-server

**Purpose**: Amazon Bedrock AgentCore platform for advanced agent orchestration

**Configuration**:

```yaml
server_name: agentcore-mcp-server
command: uvx
args: ['agentcore-mcp-server@latest']
enabled: true
```

**Capabilities**:

- Production agent deployment and scaling
- Advanced agent orchestration and coordination
- Performance monitoring and analytics
- Enterprise-grade agent management

**Use Cases**:

- Production deployment of optimization agents
- Multi-agent workflow coordination
- Agent performance monitoring and optimization
- Scalable agent infrastructure management

### 3.2 AWS Infrastructure Services

#### 3.2.1 awspricing

**Purpose**: Real-time AWS pricing data for cost optimization and budget management

**Configuration**:

```yaml
server_name: awspricing
command: uvx
args: ['awspricing@latest']
enabled: true
```

**Capabilities**:

- Real-time AWS service pricing lookup
- Cost calculation and estimation
- Budget tracking and alerts
- Cost optimization recommendations

**Use Cases**:

- AI model cost tracking (Bedrock usage)
- Budget management and spending alerts
- Cost-optimized service selection
- Usage pattern analysis and optimization

#### 3.2.2 awsknowledge

**Purpose**: AWS documentation and best practices for infrastructure optimization

**Configuration**:

```yaml
server_name: awsknowledge
command: uvx
args: ['awsknowledge@latest']
enabled: true
```

**Capabilities**:

- AWS service documentation search
- Best practices and recommendations
- Service configuration guidance
- Troubleshooting assistance

**Use Cases**:

- Infrastructure guidance and optimization
- Best practices implementation
- Service configuration recommendations
- Troubleshooting and problem resolution

#### 3.2.3 awsapi

**Purpose**: Direct AWS service integration for infrastructure management and monitoring

**Configuration**:

```yaml
server_name: awsapi
command: uvx
args: ['awsapi@latest']
enabled: true
```

**Capabilities**:

- Direct AWS API access and management
- Resource provisioning and configuration
- Service monitoring and health checks
- Infrastructure automation

**Use Cases**:

- Infrastructure management and monitoring
- Resource provisioning and scaling
- Service health monitoring
- Automated infrastructure operations

#### 3.2.4 awslabs.aws-iac-mcp-server

**Purpose**: Infrastructure as Code validation and optimization tools

**Configuration**:

```yaml
server_name: awslabs.aws-iac-mcp-server
command: uvx
args: ['awslabs.aws-iac-mcp-server@latest']
enabled: true
```

**Capabilities**:

- CloudFormation template validation
- CDK code analysis and optimization
- Infrastructure compliance checking
- Deployment troubleshooting

**Use Cases**:

- Infrastructure template validation
- Compliance checking and security analysis
- Deployment optimization and troubleshooting
- Best practices enforcement

### 3.3 Data and Context Services

#### 3.3.1 context7

**Purpose**: Advanced context management for enhanced conversation and workflow continuity

**Configuration**:

```yaml
server_name: context7
command: uvx
args: ['context7@latest']
enabled: true
```

**Capabilities**:

- Advanced context management and processing
- Conversation continuity across sessions
- Data correlation and analysis
- Workflow context preservation

**Use Cases**:

- Enhanced conversation context for AI agents
- Cross-session data continuity
- Context-aware decision making
- Workflow state management

#### 3.3.2 fetch

**Purpose**: Enhanced HTTP client capabilities for external API integration and data retrieval

**Configuration**:

```yaml
server_name: fetch
command: uvx
args: ['fetch@latest']
enabled: true
```

**Capabilities**:

- Enhanced HTTP client operations
- Advanced request/response handling
- API integration and management
- Data retrieval and processing

**Use Cases**:

- External API integration (umapyoi.net, UmamusumeDB.com)
- Enhanced HTTP operations and error handling
- API response processing and validation
- External data synchronization

#### 3.3.3 memory

**Purpose**: Persistent knowledge graph memory for AI agents across sessions

**Configuration**:

```yaml
server_name: memory
command: npx
args: ['-y', '@modelcontextprotocol/server-memory']
enabled: true
status: ✅ CONFIGURED
```

**Capabilities**:

- Persistent knowledge graph storage
- Entity and relationship management
- Cross-session memory continuity
- AI agent memory enhancement

**Use Cases**:

- AI agent persistent memory across sessions
- User preference and context storage
- Knowledge graph construction and management
- Long-term conversation continuity

### 3.4 Optional Services

#### 3.4.1 figma (Optional)

**Purpose**: UI design consistency and asset management integration

**Configuration**:

```yaml
server_name: figma
command: uvx
args: ['figma@latest']
enabled: false  # Optional
```

**Capabilities**:

- Design system integration
- Asset management and synchronization
- UI consistency validation
- Design-to-code workflows

**Use Cases**:

- UI design consistency validation
- Asset management and optimization
- Design system maintenance
- Design-to-code automation

---

## 4. Configuration Patterns

### 4.1 Standard Configuration Structure

All MCP servers follow a consistent configuration pattern:

```php
// config/mcp.php
return [
    'enabled' => env('MCP_ENABLED', true),
    'debug' => env('MCP_DEBUG', false),

    'servers' => [
        'server-name' => [
            'enabled' => true,
            'command' => 'uvx',  // or 'npx' for npm packages
            'args' => ['package-name@latest'],
            'capabilities' => ['capability1', 'capability2'],
            'timeout' => 30,
            'retry_attempts' => 3,
        ],
    ],

    'health_check_interval' => 300,
    'connection_timeout' => 10,
    'max_concurrent_calls' => 5,
];
```

### 4.2 Environment Configuration

```bash
# .env
MCP_ENABLED=true
MCP_DEBUG=false

# Individual server controls
MCP_STRANDS_AGENTS_ENABLED=true
MCP_AGENTCORE_ENABLED=true
MCP_AWSPRICING_ENABLED=true
MCP_AWSKNOWLEDGE_ENABLED=true
MCP_AWSAPI_ENABLED=true
MCP_AWS_IAC_ENABLED=true
MCP_CONTEXT7_ENABLED=true
MCP_FETCH_ENABLED=true
MCP_MEMORY_ENABLED=true
MCP_FIGMA_ENABLED=false
```

### 4.3 Service Integration Pattern

```php
<?php

namespace App\Services\MCP;

class MCPServiceManager
{
    public function __construct(
        private MCPClientService $mcpClient,
        private array $serverConfigs
    ) {}

    public function callServer(string $serverName, string $method, array $params = []): mixed
    {
        if (!$this->isServerEnabled($serverName)) {
            throw new MCPServerDisabledException("Server {$serverName} is disabled");
        }

        return $this->mcpClient->call($serverName, $method, $params);
    }

    public function getServerHealth(string $serverName): array
    {
        return $this->mcpClient->healthCheck($serverName);
    }
}
```

---

## 5. Subagent Coordination Strategy

### 5.1 Agent Orchestration Architecture

The MCP server integration enables sophisticated subagent coordination through multiple orchestration patterns:

#### 5.1.1 Primary Agent Types

| Agent Type | MCP Server | Purpose | Coordination Role |
|------------|------------|---------|-------------------|
| **Training Optimization Agent** | strands-agents | Complex training sequence planning | Primary coordinator for training decisions |
| **Career Strategy Agent** | strands-agents | Long-term career planning | Strategic oversight and goal alignment |
| **Race Analysis Agent** | strands-agents | Race preparation and analysis | Specialized race strategy coordination |
| **Skill Management Agent** | strands-agents | SP optimization and hint collection | Skill acquisition coordination |
| **Cost Management Agent** | awspricing | Budget tracking and optimization | Resource allocation coordination |
| **Context Management Agent** | context7 | Cross-session continuity | Information flow coordination |

#### 5.1.2 Agent Coordination Patterns

**Sequential Coordination**:

```text
User Request → Context Agent → Strategy Agent → Training Agent → Race Agent → Response
```

**Parallel Coordination**:

```text
User Request → Context Agent
                    ├── Training Agent (parallel)
                    ├── Skill Agent (parallel)
                    └── Cost Agent (parallel)
                         → Aggregation → Response
```

**Hierarchical Coordination**:

```text
Strategy Agent (Coordinator)
    ├── Training Agent (Specialist)
    ├── Race Agent (Specialist)
    └── Skill Agent (Specialist)
```

### 5.2 Subagent Communication Protocols

#### 5.2.1 Inter-Agent Communication

```php
<?php

namespace App\Services\MCP\Agents;

class AgentCoordinator
{
    public function coordinateTrainingDecision(Character $character, array $context): TrainingRecommendation
    {
        // 1. Context preparation
        $enhancedContext = $this->contextAgent->enhanceContext($character, $context);

        // 2. Parallel agent consultation
        $trainingAnalysis = $this->trainingAgent->analyzeOptions($character, $enhancedContext);
        $skillAnalysis = $this->skillAgent->analyzeSkillOpportunities($character, $enhancedContext);
        $costAnalysis = $this->costAgent->analyzeCosts($enhancedContext);

        // 3. Strategy coordination
        return $this->strategyAgent->coordinateRecommendation([
            'training' => $trainingAnalysis,
            'skills' => $skillAnalysis,
            'costs' => $costAnalysis,
            'context' => $enhancedContext
        ]);
    }
}
```

#### 5.2.2 Agent Memory Coordination

```php
<?php

namespace App\Services\MCP\Memory;

class AgentMemoryCoordinator
{
    public function shareMemoryBetweenAgents(string $sessionId, array $agentIds): void
    {
        $sharedMemory = $this->memoryServer->getSharedContext($sessionId);

        foreach ($agentIds as $agentId) {
            $this->memoryServer->updateAgentContext($agentId, $sharedMemory);
        }
    }

    public function consolidateAgentLearnings(array $agentLearnings): void
    {
        $consolidatedKnowledge = $this->consolidateLearnings($agentLearnings);
        $this->memoryServer->storeKnowledge($consolidatedKnowledge);
    }
}
```

### 5.3 Workflow Templates

#### 5.3.1 Training Decision Workflow

```yaml
workflow_name: training_decision
agents:
  - context7: context_enhancement
  - strands-agents: training_analysis
  - strands-agents: skill_analysis
  - awspricing: cost_analysis
coordination: parallel_then_aggregate
timeout: 30_seconds
fallback: local_processing
```

#### 5.3.2 Career Planning Workflow

```yaml
workflow_name: career_planning
agents:
  - memory: historical_analysis
  - strands-agents: strategy_planning
  - awsknowledge: best_practices
coordination: sequential
timeout: 60_seconds
fallback: simplified_planning
```

---

## 6. Health Monitoring and Management

### 6.1 Health Check Architecture

#### 6.1.1 Server Health Monitoring

```php
<?php

namespace App\Services\MCP\Health;

class MCPHealthMonitor
{
    public function performHealthChecks(): array
    {
        $results = [];

        foreach ($this->getEnabledServers() as $serverName => $config) {
            $results[$serverName] = $this->checkServerHealth($serverName, $config);
        }

        return $results;
    }

    private function checkServerHealth(string $serverName, array $config): array
    {
        $startTime = microtime(true);

        try {
            $response = $this->mcpClient->ping($serverName);
            $responseTime = (microtime(true) - $startTime) * 1000;

            return [
                'status' => 'healthy',
                'response_time' => $responseTime,
                'capabilities' => $config['capabilities'] ?? [],
                'last_check' => now(),
                'error' => null
            ];

        } catch (Exception $e) {
            return [
                'status' => 'unhealthy',
                'response_time' => null,
                'capabilities' => [],
                'last_check' => now(),
                'error' => $e->getMessage()
            ];
        }
    }
}
```

#### 6.1.2 Automated Health Monitoring

```php
<?php

namespace App\Console\Commands;

class MCPHealthCheckCommand extends Command
{
    protected $signature = 'mcp:health-check {--alert}';
    protected $description = 'Check health of all MCP servers';

    public function handle(MCPHealthMonitor $healthMonitor): int
    {
        $results = $healthMonitor->performHealthChecks();

        foreach ($results as $serverName => $health) {
            $status = $health['status'] === 'healthy' ? '✅' : '❌';
            $this->line("{$status} {$serverName}: {$health['status']}");

            if ($health['error']) {
                $this->error("   Error: {$health['error']}");
            }
        }

        if ($this->option('alert')) {
            $this->sendHealthAlerts($results);
        }

        return Command::SUCCESS;
    }
}
```

### 6.2 Performance Monitoring

#### 6.2.1 Response Time Tracking

```php
<?php

namespace App\Services\MCP\Monitoring;

class MCPPerformanceMonitor
{
    public function trackServerPerformance(string $serverName, float $responseTime, bool $success): void
    {
        $metrics = [
            'server' => $serverName,
            'response_time' => $responseTime,
            'success' => $success,
            'timestamp' => now()
        ];

        // Store in Redis for real-time monitoring
        Redis::lpush("mcp_performance:{$serverName}", json_encode($metrics));
        Redis::ltrim("mcp_performance:{$serverName}", 0, 999); // Keep last 1000 entries

        // Update aggregated metrics
        $this->updateAggregatedMetrics($serverName, $responseTime, $success);
    }

    public function getServerMetrics(string $serverName, int $hours = 24): array
    {
        $key = "mcp_metrics:{$serverName}";
        $metrics = Redis::hgetall($key);

        return [
            'avg_response_time' => $metrics['avg_response_time'] ?? 0,
            'success_rate' => $metrics['success_rate'] ?? 0,
            'total_calls' => $metrics['total_calls'] ?? 0,
            'last_updated' => $metrics['last_updated'] ?? null
        ];
    }
}
```

### 6.3 Error Handling and Recovery

#### 6.3.1 Circuit Breaker Pattern

```php
<?php

namespace App\Services\MCP\Resilience;

class MCPCircuitBreaker
{
    private const FAILURE_THRESHOLD = 5;
    private const RECOVERY_TIMEOUT = 300; // 5 minutes

    public function callWithCircuitBreaker(string $serverName, callable $operation): mixed
    {
        $circuitState = $this->getCircuitState($serverName);

        if ($circuitState === 'open') {
            if ($this->shouldAttemptRecovery($serverName)) {
                $this->setCircuitState($serverName, 'half-open');
            } else {
                throw new CircuitBreakerOpenException("Circuit breaker open for {$serverName}");
            }
        }

        try {
            $result = $operation();

            if ($circuitState === 'half-open') {
                $this->setCircuitState($serverName, 'closed');
                $this->resetFailureCount($serverName);
            }

            return $result;

        } catch (Exception $e) {
            $this->recordFailure($serverName);

            if ($this->getFailureCount($serverName) >= self::FAILURE_THRESHOLD) {
                $this->setCircuitState($serverName, 'open');
            }

            throw $e;
        }
    }
}
```

---

## 7. Integration Guidelines

### 7.1 Development Guidelines

#### 7.1.1 MCP Server Integration Checklist

- [ ] **Configuration**: Add server configuration to `config/mcp.php`
- [ ] **Environment**: Add environment variables for server control
- [ ] **Service Integration**: Create service wrapper for server operations
- [ ] **Health Monitoring**: Implement health check for the server
- [ ] **Error Handling**: Add proper error handling and fallback mechanisms
- [ ] **Testing**: Create integration tests for server functionality
- [ ] **Documentation**: Update this reference document with server details

#### 7.1.2 Best Practices

1. **Consistent Naming**: Use kebab-case for server names (e.g., `strands-agents`)
2. **Capability Documentation**: Always document server capabilities and use cases
3. **Error Handling**: Implement comprehensive error handling with meaningful messages
4. **Performance Monitoring**: Track response times and success rates
5. **Fallback Mechanisms**: Always provide fallback options when servers are unavailable
6. **Security**: Implement proper authentication and authorization for server access
7. **Testing**: Create comprehensive integration tests for all server interactions

### 7.2 Troubleshooting Guide

#### 7.2.1 Common Issues

| Issue | Symptoms | Solution |
|-------|----------|----------|
| **Server Unavailable** | Connection timeouts, server not responding | Check server status, restart if needed |
| **Authentication Failure** | 401/403 errors | Verify credentials and permissions |
| **Rate Limiting** | 429 errors | Implement backoff strategy, check rate limits |
| **Invalid Configuration** | Server startup failures | Validate configuration syntax and parameters |
| **Memory Issues** | Out of memory errors | Monitor memory usage, optimize queries |

#### 7.2.2 Diagnostic Commands

```bash
# Check MCP server health
php artisan mcp:health-check

# Monitor MCP performance
php artisan mcp:performance --server=strands-agents

# Test MCP server connectivity
php artisan mcp:test-connection --server=all

# View MCP server logs
php artisan mcp:logs --server=memory --lines=100
```

---

## Document Control

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 2026-01-12 | Development Team | Initial MCP server configuration reference |

---

*This document serves as the authoritative reference for all MCP server configurations and integration patterns in the Umamusume Career Planner project.*
