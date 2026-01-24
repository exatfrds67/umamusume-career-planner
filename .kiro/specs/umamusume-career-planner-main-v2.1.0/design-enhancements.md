# Design Document Enhancements for v2.1.0

**Document Version**: 2.1.0-enhancements  
**Date**: January 23, 2026  
**Purpose**: Additional diagrams, rationales, and sequence flows for design.md

---

## Table of Contents

1. [Enhanced Mermaid Diagrams](#1-enhanced-mermaid-diagrams)
2. [Design Rationales and Alternatives](#2-design-rationales-and-alternatives)
3. [Critical Sequence Diagrams](#3-critical-sequence-diagrams)
4. [Implementation Depth Examples](#4-implementation-depth-examples)
5. [Component Interaction Flows](#5-component-interaction-flows)

---

## 1. Enhanced Mermaid Diagrams

### 1.1 Cache Hierarchy with Data Flow

**Purpose**: Visualize the complete L1/L2 caching flow with timing and hit rates

```mermaid
flowchart TB
    subgraph Browser["Browser Layer"]
        Request["HTTP Request<br/>~100 req/min"]
    end
    
    subgraph AppServer["Application Server"]
        subgraph L1["L1 Cache (Memory)"]
            MemCache["In-Memory Array<br/>TTL: 5 minutes<br/>Size: 100MB<br/>Hit Rate: 60%"]
        end
        
        subgraph L2["L2 Cache (Redis)"]
            RedisCache["Redis Server<br/>TTL: Variable<br/>Size: 2GB<br/>Hit Rate: 25%"]
        end
        
        subgraph Logic["Business Logic"]
            Service["Service Layer<br/>DB Query: 15%"]
        end
    end
    
    subgraph DataLayer["Data Layer"]
        DB[(MySQL Database<br/>Query Time: 50-200ms)]
    end
    
    Request -->|"1. Check L1<br/><1ms"| MemCache
    MemCache -->|"60% Hit: Return<br/>Total: <1ms"| Request
    MemCache -->|"40% Miss"| RedisCache
    RedisCache -->|"25% Hit<br/>10ms"| MemCache
    RedisCache -->|"15% Miss"| Service
    Service -->|"Query<br/>50-200ms"| DB
    DB -->|"Result"| Service
    Service -->|"Store"| RedisCache
    RedisCache -->|"Promote"| MemCache
    MemCache -->|"Return"| Request
    
    style MemCache fill:#90EE90
    style RedisCache fill:#87CEEB
    style DB fill:#FFB6C1
```
