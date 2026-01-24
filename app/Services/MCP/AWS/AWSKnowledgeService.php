<?php

declare(strict_types=1);

namespace App\Services\MCP\AWS;

use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * AWS Knowledge Service
 *
 * Integrates with awsknowledge MCP server for AWS best practices,
 * documentation access, architecture recommendations, and troubleshooting guidance.
 *
 * Requirements: 56.4, 59.3, 14.1
 */
class AWSKnowledgeService
{
    public const CACHE_TTL = 7200; // 2 hours

    protected const MCP_SERVER_NAME = 'awsknowledge';

    public function __construct(
        private readonly MCPClientService $mcpClient
    ) {}

    /**
     * Check if AWS Knowledge MCP server is available
     */
    public function isAvailable(): bool
    {
        return $this->mcpClient->isServerEnabled(self::MCP_SERVER_NAME) &&
            $this->mcpClient->isServerHealthy(self::MCP_SERVER_NAME);
    }

    /**
     * Get best practices for a specific AWS service
     *
     * @return array{
     *     service: string,
     *     best_practices: array<int, array{
     *         category: string,
     *         title: string,
     *         description: string,
     *         priority: string,
     *         implementation: string
     *     }>,
     *     documentation_links: array<int, string>,
     *     last_updated: string
     * }
     */
    public function getBestPractices(string $service = 'bedrock'): array
    {
        $cacheKey = $this->getCacheKey('best_practices', $service);

        /** @var array{service: string, best_practices: array<int, array{category: string, title: string, description: string, priority: string, implementation: string}>, documentation_links: array<int, string>, last_updated: string} */
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($service): array {
            if (! $this->isAvailable()) {
                return $this->getFallbackBestPractices($service);
            }

            try {
                return $this->fetchBestPracticesFromMCP($service);
            } catch (\Exception $e) {
                Log::error('[AWSKnowledge] Failed to fetch best practices', [
                    'service' => $service,
                    'error' => $e->getMessage(),
                ]);

                return $this->getFallbackBestPractices($service);
            }
        });
    }

    /**
     * Get architecture recommendations for AI workloads
     *
     * @param  array<string, mixed>  $requirements
     * @return array{
     *     architecture_type: string,
     *     recommendations: array<int, array{
     *         component: string,
     *         service: string,
     *         rationale: string,
     *         configuration: array<string, mixed>
     *     }>,
     *     cost_estimate: array<string, mixed>,
     *     scalability: string,
     *     reliability: string
     * }
     */
    public function getArchitectureRecommendations(array $requirements = []): array
    {
        $jsonEncoded = json_encode($requirements);
        $cacheKey = $this->getCacheKey('architecture', md5($jsonEncoded !== false ? $jsonEncoded : ''));

        /** @var array{architecture_type: string, recommendations: array<int, array{component: string, service: string, rationale: string, configuration: array<string, mixed>}>, cost_estimate: array<string, mixed>, scalability: string, reliability: string} */
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($requirements): array {
            if (! $this->isAvailable()) {
                return $this->getFallbackArchitecture();
            }

            try {
                return $this->fetchArchitectureFromMCP($requirements);
            } catch (\Exception $e) {
                Log::error('[AWSKnowledge] Failed to fetch architecture recommendations', [
                    'error' => $e->getMessage(),
                ]);

                return $this->getFallbackArchitecture();
            }
        });
    }

    /**
     * Get troubleshooting guidance for common issues
     *
     * @return array{
     *     issue: string,
     *     category: string,
     *     solutions: array<int, array{
     *         step: int,
     *         action: string,
     *         expected_result: string,
     *         troubleshooting_tips: array<int, string>
     *     }>,
     *     prevention: array<int, string>,
     *     related_documentation: array<int, string>
     * }
     */
    public function getTroubleshootingGuidance(string $issue = 'general', string $service = 'bedrock'): array
    {
        $cacheKey = $this->getCacheKey('troubleshooting', $service, $issue);

        /** @var array{issue: string, category: string, solutions: array<int, array{step: int, action: string, expected_result: string, troubleshooting_tips: array<int, string>}>, prevention: array<int, string>, related_documentation: array<int, string>} */
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($issue, $service): array {
            if (! $this->isAvailable()) {
                return $this->getFallbackTroubleshooting($issue);
            }

            try {
                return $this->fetchTroubleshootingFromMCP($issue, $service);
            } catch (\Exception $e) {
                Log::error('[AWSKnowledge] Failed to fetch troubleshooting guidance', [
                    'issue' => $issue,
                    'service' => $service,
                    'error' => $e->getMessage(),
                ]);

                return $this->getFallbackTroubleshooting($issue);
            }
        });
    }

    /**
     * Get Bedrock best practices
     *
     * @return array{
     *     service: string,
     *     best_practices: array<int, array{
     *         category: string,
     *         title: string,
     *         description: string,
     *         priority: string,
     *         implementation: string
     *     }>,
     *     model_selection: array<string, mixed>,
     *     cost_optimization: array<int, string>,
     *     performance_tips: array<int, string>
     * }
     */
    public function getBedrockBestPractices(): array
    {
        $cacheKey = $this->getCacheKey('bedrock_best_practices');

        /** @var array{service: string, best_practices: array<int, array{category: string, title: string, description: string, priority: string, implementation: string}>, model_selection: array<string, mixed>, cost_optimization: array<int, string>, performance_tips: array<int, string>} */
        return Cache::remember($cacheKey, self::CACHE_TTL, function (): array {
            if (! $this->isAvailable()) {
                return $this->getFallbackBedrockBestPractices();
            }

            try {
                return $this->fetchBedrockBestPracticesFromMCP();
            } catch (\Exception $e) {
                Log::error('[AWSKnowledge] Failed to fetch Bedrock best practices', [
                    'error' => $e->getMessage(),
                ]);

                return $this->getFallbackBedrockBestPractices();
            }
        });
    }

    /**
     * Get security best practices
     *
     * @return array{
     *     category: string,
     *     practices: array<int, array{
     *         title: string,
     *         description: string,
     *         severity: string,
     *         implementation: string,
     *         compliance: array<int, string>
     *     }>,
     *     security_checklist: array<int, string>,
     *     compliance_frameworks: array<int, string>
     * }
     */
    public function getSecurityBestPractices(string $category = 'ai_services'): array
    {
        $cacheKey = $this->getCacheKey('security', $category);

        /** @var array{category: string, practices: array<int, array{title: string, description: string, severity: string, implementation: string, compliance: array<int, string>}>, security_checklist: array<int, string>, compliance_frameworks: array<int, string>} */
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($category): array {
            if (! $this->isAvailable()) {
                return $this->getFallbackSecurityPractices($category);
            }

            try {
                return $this->fetchSecurityPracticesFromMCP($category);
            } catch (\Exception $e) {
                Log::error('[AWSKnowledge] Failed to fetch security best practices', [
                    'category' => $category,
                    'error' => $e->getMessage(),
                ]);

                return $this->getFallbackSecurityPractices($category);
            }
        });
    }

    /**
     * Search AWS documentation
     *
     * @return array{
     *     query: string,
     *     results: array<int, array{
     *         title: string,
     *         url: string,
     *         excerpt: string,
     *         service: string,
     *         relevance_score: float
     *     }>,
     *     total_results: int,
     *     search_time: float
     * }
     */
    public function searchDocumentation(string $query, int $limit = 10): array
    {
        $cacheKey = $this->getCacheKey('search', $query, (string) $limit);

        /** @var array{query: string, results: array<int, array{title: string, url: string, excerpt: string, service: string, relevance_score: float}>, total_results: int, search_time: float} */
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query, $limit): array {
            if (! $this->isAvailable()) {
                return $this->getFallbackSearchResults($query);
            }

            try {
                return $this->fetchSearchResultsFromMCP($query, $limit);
            } catch (\Exception $e) {
                Log::error('[AWSKnowledge] Failed to search documentation', [
                    'query' => $query,
                    'error' => $e->getMessage(),
                ]);

                return $this->getFallbackSearchResults($query);
            }
        });
    }

    /**
     * Get Well-Architected Framework recommendations
     *
     * @param  array<string, mixed>  $workload
     * @return array{
     *     pillars: array<string, array{
     *         name: string,
     *         score: float,
     *         recommendations: array<int, string>,
     *         risks: array<int, string>
     *     }>,
     *     overall_score: float,
     *     priority_improvements: array<int, string>,
     *     documentation: array<int, string>
     * }
     */
    public function getWellArchitectedRecommendations(array $workload = []): array
    {
        $jsonEncoded = json_encode($workload);
        $cacheKey = $this->getCacheKey('well_architected', md5($jsonEncoded !== false ? $jsonEncoded : ''));

        /** @var array{pillars: array<string, array{name: string, score: float, recommendations: array<int, string>, risks: array<int, string>}>, overall_score: float, priority_improvements: array<int, string>, documentation: array<int, string>} */
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($workload): array {
            if (! $this->isAvailable()) {
                return $this->getFallbackWellArchitected();
            }

            try {
                return $this->fetchWellArchitectedFromMCP($workload);
            } catch (\Exception $e) {
                Log::error('[AWSKnowledge] Failed to fetch Well-Architected recommendations', [
                    'error' => $e->getMessage(),
                ]);

                return $this->getFallbackWellArchitected();
            }
        });
    }

    /**
     * Fetch best practices from MCP server
     *
     * @return array{service: string, best_practices: array<int, array{category: string, title: string, description: string, priority: string, implementation: string}>, documentation_links: array<int, string>, last_updated: string}
     */
    protected function fetchBestPracticesFromMCP(string $service): array
    {
        // In production, this would make actual MCP calls
        return $this->getFallbackBestPractices($service);
    }

    /**
     * Fetch architecture recommendations from MCP server
     *
     * @param  array<string, mixed>  $requirements
     * @return array{architecture_type: string, recommendations: array<int, array{component: string, service: string, rationale: string, configuration: array<string, mixed>}>, cost_estimate: array<string, mixed>, scalability: string, reliability: string}
     */
    protected function fetchArchitectureFromMCP(array $requirements): array
    {
        // In production, this would make actual MCP calls
        unset($requirements); // Parameter reserved for future implementation

        return $this->getFallbackArchitecture();
    }

    /**
     * Fetch troubleshooting guidance from MCP server
     *
     * @return array{issue: string, category: string, solutions: array<int, array{step: int, action: string, expected_result: string, troubleshooting_tips: array<int, string>}>, prevention: array<int, string>, related_documentation: array<int, string>}
     */
    protected function fetchTroubleshootingFromMCP(string $issue, string $service): array
    {
        // In production, this would make actual MCP calls
        unset($service); // Parameter reserved for future implementation

        return $this->getFallbackTroubleshooting($issue);
    }

    /**
     * Fetch Bedrock best practices from MCP server
     *
     * @return array{service: string, best_practices: array<int, array{category: string, title: string, description: string, priority: string, implementation: string}>, model_selection: array<string, mixed>, cost_optimization: array<int, string>, performance_tips: array<int, string>}
     */
    protected function fetchBedrockBestPracticesFromMCP(): array
    {
        // In production, this would make actual MCP calls
        return $this->getFallbackBedrockBestPractices();
    }

    /**
     * Fetch security practices from MCP server
     *
     * @return array{category: string, practices: array<int, array{title: string, description: string, severity: string, implementation: string, compliance: array<int, string>}>, security_checklist: array<int, string>, compliance_frameworks: array<int, string>}
     */
    protected function fetchSecurityPracticesFromMCP(string $category): array
    {
        // In production, this would make actual MCP calls
        return $this->getFallbackSecurityPractices($category);
    }

    /**
     * Fetch search results from MCP server
     *
     * @return array{query: string, results: array<int, array{title: string, url: string, excerpt: string, service: string, relevance_score: float}>, total_results: int, search_time: float}
     */
    protected function fetchSearchResultsFromMCP(string $query, int $limit): array
    {
        // In production, this would make actual MCP calls
        unset($limit); // Parameter reserved for future implementation

        return $this->getFallbackSearchResults($query);
    }

    /**
     * Fetch Well-Architected recommendations from MCP server
     *
     * @param  array<string, mixed>  $workload
     * @return array{pillars: array<string, array{name: string, score: float, recommendations: array<int, string>, risks: array<int, string>}>, overall_score: float, priority_improvements: array<int, string>, documentation: array<int, string>}
     */
    protected function fetchWellArchitectedFromMCP(array $workload): array
    {
        // In production, this would make actual MCP calls
        unset($workload); // Parameter reserved for future implementation

        return $this->getFallbackWellArchitected();
    }

    /**
     * Get fallback best practices
     *
     * @return array{service: string, best_practices: array<int, array{category: string, title: string, description: string, priority: string, implementation: string}>, documentation_links: array<int, string>, last_updated: string}
     */
    protected function getFallbackBestPractices(string $service): array
    {
        return [
            'service' => $service,
            'best_practices' => [
                [
                    'category' => 'Performance',
                    'title' => 'Enable caching',
                    'description' => 'Implement caching strategies to reduce latency and costs',
                    'priority' => 'high',
                    'implementation' => 'Use Redis or ElastiCache for frequently accessed data',
                ],
                [
                    'category' => 'Security',
                    'title' => 'Use IAM roles',
                    'description' => 'Implement least privilege access using IAM roles',
                    'priority' => 'critical',
                    'implementation' => 'Create specific IAM roles for each service component',
                ],
                [
                    'category' => 'Cost',
                    'title' => 'Monitor usage',
                    'description' => 'Set up CloudWatch alarms for cost monitoring',
                    'priority' => 'medium',
                    'implementation' => 'Configure billing alerts and budget thresholds',
                ],
            ],
            'documentation_links' => [
                'https://docs.aws.amazon.com/bedrock/latest/userguide/what-is-bedrock.html',
            ],
            'last_updated' => now()->toDateString(),
        ];
    }

    /**
     * Get fallback architecture recommendations
     *
     * @return array{architecture_type: string, recommendations: array<int, array{component: string, service: string, rationale: string, configuration: array<string, mixed>}>, cost_estimate: array<string, mixed>, scalability: string, reliability: string}
     */
    protected function getFallbackArchitecture(): array
    {
        return [
            'architecture_type' => 'hybrid_ai_processing',
            'recommendations' => [
                [
                    'component' => 'AI Processing',
                    'service' => 'Amazon Bedrock + Local Ollama',
                    'rationale' => 'Hybrid approach balances cost and performance',
                    'configuration' => [
                        'primary' => 'Local Ollama for simple queries',
                        'fallback' => 'Bedrock for complex reasoning',
                    ],
                ],
                [
                    'component' => 'Caching',
                    'service' => 'Amazon ElastiCache (Redis)',
                    'rationale' => 'Reduces AI API calls and improves response time',
                    'configuration' => [
                        'instance_type' => 'cache.t3.micro',
                        'ttl' => '1 hour',
                    ],
                ],
            ],
            'cost_estimate' => [
                'monthly' => 50.0,
                'breakdown' => [
                    'bedrock' => 30.0,
                    'elasticache' => 15.0,
                    'data_transfer' => 5.0,
                ],
            ],
            'scalability' => 'high',
            'reliability' => 'high',
        ];
    }

    /**
     * Get fallback troubleshooting guidance
     *
     * @return array{issue: string, category: string, solutions: array<int, array{step: int, action: string, expected_result: string, troubleshooting_tips: array<int, string>}>, prevention: array<int, string>, related_documentation: array<int, string>}
     */
    protected function getFallbackTroubleshooting(string $issue): array
    {
        return [
            'issue' => $issue,
            'category' => 'general',
            'solutions' => [
                [
                    'step' => 1,
                    'action' => 'Check service health status',
                    'expected_result' => 'Service should be operational',
                    'troubleshooting_tips' => [
                        'Visit AWS Service Health Dashboard',
                        'Check for regional outages',
                    ],
                ],
                [
                    'step' => 2,
                    'action' => 'Verify IAM permissions',
                    'expected_result' => 'Proper permissions configured',
                    'troubleshooting_tips' => [
                        'Review IAM policy documents',
                        'Check for explicit deny statements',
                    ],
                ],
            ],
            'prevention' => [
                'Implement proper error handling',
                'Set up monitoring and alerts',
                'Follow AWS best practices',
            ],
            'related_documentation' => [
                'https://docs.aws.amazon.com/bedrock/latest/userguide/troubleshooting.html',
            ],
        ];
    }

    /**
     * Get fallback Bedrock best practices
     *
     * @return array{service: string, best_practices: array<int, array{category: string, title: string, description: string, priority: string, implementation: string}>, model_selection: array<string, mixed>, cost_optimization: array<int, string>, performance_tips: array<int, string>}
     */
    protected function getFallbackBedrockBestPractices(): array
    {
        return [
            'service' => 'Amazon Bedrock',
            'best_practices' => [
                [
                    'category' => 'Model Selection',
                    'title' => 'Choose appropriate model for task complexity',
                    'description' => 'Use Haiku for simple tasks, Sonnet for balanced needs, Opus for complex reasoning',
                    'priority' => 'high',
                    'implementation' => 'Implement complexity detection algorithm',
                ],
                [
                    'category' => 'Cost Optimization',
                    'title' => 'Implement caching strategy',
                    'description' => 'Cache responses for repeated queries to reduce API calls',
                    'priority' => 'high',
                    'implementation' => 'Use Redis with 1-hour TTL for common queries',
                ],
                [
                    'category' => 'Performance',
                    'title' => 'Use streaming for long responses',
                    'description' => 'Enable streaming to improve perceived performance',
                    'priority' => 'medium',
                    'implementation' => 'Implement SSE for real-time response streaming',
                ],
            ],
            'model_selection' => [
                'simple_queries' => 'claude-haiku-4.5',
                'balanced' => 'claude-sonnet-4.5',
                'complex_reasoning' => 'claude-opus-4.5',
                'budget' => 'nova-2-lite',
            ],
            'cost_optimization' => [
                'Use local Ollama for simple queries',
                'Implement aggressive caching',
                'Batch non-urgent requests',
                'Monitor and optimize token usage',
            ],
            'performance_tips' => [
                'Enable response streaming',
                'Optimize prompt engineering',
                'Use appropriate context window sizes',
                'Implement request queuing for rate limiting',
            ],
        ];
    }

    /**
     * Get fallback security practices
     *
     * @return array{category: string, practices: array<int, array{title: string, description: string, severity: string, implementation: string, compliance: array<int, string>}>, security_checklist: array<int, string>, compliance_frameworks: array<int, string>}
     */
    protected function getFallbackSecurityPractices(string $category): array
    {
        return [
            'category' => $category,
            'practices' => [
                [
                    'title' => 'Implement least privilege access',
                    'description' => 'Grant only necessary permissions to IAM roles',
                    'severity' => 'critical',
                    'implementation' => 'Use specific IAM policies for each service',
                    'compliance' => ['SOC 2', 'ISO 27001'],
                ],
                [
                    'title' => 'Enable encryption at rest',
                    'description' => 'Encrypt all sensitive data stored in AWS services',
                    'severity' => 'high',
                    'implementation' => 'Use AWS KMS for encryption key management',
                    'compliance' => ['GDPR', 'HIPAA'],
                ],
            ],
            'security_checklist' => [
                'Enable MFA for all IAM users',
                'Use VPC for network isolation',
                'Implement CloudTrail logging',
                'Regular security audits',
            ],
            'compliance_frameworks' => [
                'SOC 2',
                'ISO 27001',
                'GDPR',
                'HIPAA',
            ],
        ];
    }

    /**
     * Get fallback search results
     *
     * @return array{query: string, results: array<int, array{title: string, url: string, excerpt: string, service: string, relevance_score: float}>, total_results: int, search_time: float}
     */
    protected function getFallbackSearchResults(string $query): array
    {
        return [
            'query' => $query,
            'results' => [],
            'total_results' => 0,
            'search_time' => 0.0,
        ];
    }

    /**
     * Get fallback Well-Architected recommendations
     *
     * @return array{pillars: array<string, array{name: string, score: float, recommendations: array<int, string>, risks: array<int, string>}>, overall_score: float, priority_improvements: array<int, string>, documentation: array<int, string>}
     */
    protected function getFallbackWellArchitected(): array
    {
        return [
            'pillars' => [
                'operational_excellence' => [
                    'name' => 'Operational Excellence',
                    'score' => 0.75,
                    'recommendations' => [
                        'Implement automated monitoring',
                        'Use Infrastructure as Code',
                    ],
                    'risks' => [
                        'Manual deployment processes',
                    ],
                ],
                'security' => [
                    'name' => 'Security',
                    'score' => 0.80,
                    'recommendations' => [
                        'Enable MFA',
                        'Implement least privilege',
                    ],
                    'risks' => [
                        'Overly permissive IAM policies',
                    ],
                ],
                'reliability' => [
                    'name' => 'Reliability',
                    'score' => 0.70,
                    'recommendations' => [
                        'Implement multi-AZ deployment',
                        'Set up automated backups',
                    ],
                    'risks' => [
                        'Single point of failure',
                    ],
                ],
                'performance' => [
                    'name' => 'Performance Efficiency',
                    'score' => 0.85,
                    'recommendations' => [
                        'Use caching strategies',
                        'Optimize database queries',
                    ],
                    'risks' => [
                        'Inefficient data access patterns',
                    ],
                ],
                'cost' => [
                    'name' => 'Cost Optimization',
                    'score' => 0.65,
                    'recommendations' => [
                        'Right-size instances',
                        'Use reserved capacity',
                    ],
                    'risks' => [
                        'Over-provisioned resources',
                    ],
                ],
            ],
            'overall_score' => 0.75,
            'priority_improvements' => [
                'Implement cost optimization strategies',
                'Enhance reliability with multi-AZ',
                'Automate operational processes',
            ],
            'documentation' => [
                'https://aws.amazon.com/architecture/well-architected/',
            ],
        ];
    }

    /**
     * Get cache key
     */
    protected function getCacheKey(string $type, string ...$params): string
    {
        return sprintf('aws_knowledge:%s:%s', $type, implode(':', $params));
    }
}
