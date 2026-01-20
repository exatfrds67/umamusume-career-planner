# Monitoring and Logging Guide

## Overview

This document describes the comprehensive monitoring, logging, error tracking, and disaster recovery setup for the UmamusumeCareerPlanner application.

## Table of Contents

1. [Error Tracking](#error-tracking)
2. [Performance Monitoring](#performance-monitoring)
3. [User Analytics](#user-analytics)
4. [Logging Configuration](#logging-configuration)
5. [Backup and Recovery](#backup-and-recovery)
6. [Alerting](#alerting)

---

## Error Tracking

### Laravel Telescope

Telescope is configured for development and staging environments to provide detailed debugging information.

#### Configuration

```php
// config/telescope.php
return [
    'enabled' => env('TELESCOPE_ENABLED', false),
    'domain' => env('TELESCOPE_DOMAIN'),
    'path' => 'telescope',
    'driver' => 'database',
    'storage' => [
        'database' => [
            'connection' => env('DB_CONNECTION', 'sqlite'),
            'chunk' => 1000,
        ],
    ],
    'watchers' => [
        Watchers\CacheWatcher::class => env('TELESCOPE_CACHE_WATCHER', true),
        Watchers\CommandWatcher::class => env('TELESCOPE_COMMAND_WATCHER', true),
        Watchers\DumpWatcher::class => env('TELESCOPE_DUMP_WATCHER', true),
        Watchers\EventWatcher::class => env('TELESCOPE_EVENT_WATCHER', true),
        Watchers\ExceptionWatcher::class => env('TELESCOPE_EXCEPTION_WATCHER', true),
        Watchers\JobWatcher::class => env('TELESCOPE_JOB_WATCHER', true),
        Watchers\LogWatcher::class => env('TELESCOPE_LOG_WATCHER', true),
        Watchers\MailWatcher::class => env('TELESCOPE_MAIL_WATCHER', true),
        Watchers\ModelWatcher::class => env('TELESCOPE_MODEL_WATCHER', true),
        Watchers\NotificationWatcher::class => env('TELESCOPE_NOTIFICATION_WATCHER', true),
        Watchers\QueryWatcher::class => [
            'enabled' => env('TELESCOPE_QUERY_WATCHER', true),
            'slow' => 100, // Log queries slower than 100ms
        ],
        Watchers\RedisWatcher::class => env('TELESCOPE_REDIS_WATCHER', true),
        Watchers\RequestWatcher::class => env('TELESCOPE_REQUEST_WATCHER', true),
        Watchers\ScheduleWatcher::class => env('TELESCOPE_SCHEDULE_WATCHER', true),
    ],
];
```

#### Accessing Telescope

- Development: `http://localhost/telescope`
- Staging: `https://staging.example.com/telescope` (requires authentication)

### Exception Handling

Custom exception handling is configured in `bootstrap/app.php`:

```php
->withExceptions(function (Exceptions $exceptions) {
    // Report to external service (Sentry, Bugsnag, etc.)
    $exceptions->report(function (Throwable $e) {
        if (app()->bound('sentry')) {
            app('sentry')->captureException($e);
        }
    });

    // Custom rendering for API exceptions
    $exceptions->render(function (Throwable $e, Request $request) {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ], $this->getStatusCode($e));
        }
    });
})
```

### Error Notification Channels

| Severity | Channel | Response Time |
|----------|---------|---------------|
| Critical | Slack + Email + PagerDuty | Immediate |
| Error | Slack + Email | 15 minutes |
| Warning | Slack | 1 hour |
| Info | Log only | N/A |

---

## Performance Monitoring

### Laravel Horizon (Queue Monitoring)

Horizon provides real-time monitoring for Redis queues.

#### Dashboard Access

- URL: `/horizon`
- Authentication: Admin users only

#### Key Metrics

- **Throughput**: Jobs processed per minute
- **Runtime**: Average job execution time
- **Failed Jobs**: Jobs that failed and require attention
- **Wait Time**: Time jobs spend in queue before processing

#### Configuration

```php
// config/horizon.php
return [
    'environments' => [
        'production' => [
            'supervisor-1' => [
                'connection' => 'redis',
                'queue' => ['default', 'high', 'low'],
                'balance' => 'auto',
                'processes' => 10,
                'tries' => 3,
                'timeout' => 300,
            ],
        ],
        'local' => [
            'supervisor-1' => [
                'connection' => 'redis',
                'queue' => ['default'],
                'balance' => 'simple',
                'processes' => 3,
                'tries' => 3,
            ],
        ],
    ],
];
```

### Database Query Monitoring

#### Slow Query Detection

```php
// app/Providers/AppServiceProvider.php
public function boot(): void
{
    if (config('app.debug')) {
        DB::listen(function ($query) {
            if ($query->time > 100) { // 100ms threshold
                Log::warning('Slow query detected', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time,
                ]);
            }
        });
    }
}
```

### API Performance Metrics

The `ApiPerformanceMonitoringService` tracks:

- Response times per endpoint
- Request counts and error rates
- Cache hit/miss ratios
- Memory usage patterns

#### Accessing Metrics

```php
use App\Services\ApiPerformanceMonitoringService;

$metrics = app(ApiPerformanceMonitoringService::class);
$report = $metrics->getPerformanceReport('last_24_hours');
```

### Health Check Endpoint

```
GET /api/health
```

Response:

```json
{
    "status": "healthy",
    "timestamp": "2026-01-20T10:30:00Z",
    "checks": {
        "database": "ok",
        "redis": "ok",
        "queue": "ok",
        "storage": "ok"
    },
    "metrics": {
        "response_time_avg": 45,
        "memory_usage": "128MB",
        "cpu_usage": "15%"
    }
}
```

---

## User Analytics

### Privacy-Compliant Tracking

All analytics are collected in compliance with GDPR and privacy regulations.

#### Tracked Events (Anonymized)

- Page views (no PII)
- Feature usage patterns
- Error occurrences
- Performance metrics

#### Data Retention

| Data Type | Retention Period |
|-----------|------------------|
| Session data | 24 hours |
| Aggregated analytics | 90 days |
| Error logs | 30 days |
| Audit logs | 1 year |

### User Consent Management

```php
// Check user consent before tracking
if ($user->hasConsented('analytics')) {
    Analytics::track('feature_used', [
        'feature' => 'career_planner',
        'user_id_hash' => hash('sha256', $user->id),
    ]);
}
```

---

## Logging Configuration

### Log Channels

```php
// config/logging.php
return [
    'default' => env('LOG_CHANNEL', 'stack'),
    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['daily', 'slack'],
            'ignore_exceptions' => false,
        ],
        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],
        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'UCP Logger',
            'emoji' => ':boom:',
            'level' => env('LOG_SLACK_LEVEL', 'error'),
        ],
        'performance' => [
            'driver' => 'daily',
            'path' => storage_path('logs/performance.log'),
            'level' => 'info',
            'days' => 7,
        ],
        'security' => [
            'driver' => 'daily',
            'path' => storage_path('logs/security.log'),
            'level' => 'warning',
            'days' => 30,
        ],
    ],
];
```

### Structured Logging

```php
Log::channel('performance')->info('API request completed', [
    'endpoint' => '/api/characters',
    'method' => 'GET',
    'duration_ms' => 45,
    'status' => 200,
    'user_id' => auth()->id(),
]);
```

### Log Rotation

Logs are automatically rotated:

- Daily logs: 14 days retention
- Performance logs: 7 days retention
- Security logs: 30 days retention

---

## Backup and Recovery

### Automated Backups

#### Database Backups

```bash
# Daily backup script (runs via cron)
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/database"

# SQLite backup
cp /var/www/database/database.sqlite "$BACKUP_DIR/sqlite_$DATE.db"

# MySQL backup (if using MySQL)
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > "$BACKUP_DIR/mysql_$DATE.sql"

# Compress and encrypt
gzip "$BACKUP_DIR/*_$DATE.*"
gpg --encrypt --recipient backup@example.com "$BACKUP_DIR/*_$DATE.*.gz"

# Upload to S3
aws s3 cp "$BACKUP_DIR/" s3://ucp-backups/database/ --recursive --exclude "*" --include "*_$DATE.*"

# Cleanup old local backups (keep 7 days)
find $BACKUP_DIR -type f -mtime +7 -delete
```

#### File Backups

```bash
# Storage backup
tar -czf /backups/storage/storage_$(date +%Y%m%d).tar.gz /var/www/storage/app

# Upload to S3
aws s3 sync /backups/storage/ s3://ucp-backups/storage/
```

### Backup Schedule

| Backup Type | Frequency | Retention |
|-------------|-----------|-----------|
| Database (full) | Daily | 30 days |
| Database (incremental) | Hourly | 24 hours |
| File storage | Daily | 14 days |
| Configuration | On change | 90 days |

### Disaster Recovery Procedures

#### Recovery Time Objectives (RTO)

| Scenario | RTO | RPO |
|----------|-----|-----|
| Database corruption | 1 hour | 1 hour |
| Server failure | 2 hours | 1 hour |
| Data center outage | 4 hours | 1 hour |
| Complete disaster | 24 hours | 24 hours |

#### Recovery Steps

1. **Assess the situation**
   - Identify the scope of the failure
   - Determine the recovery point needed

2. **Restore from backup**

   ```bash
   # Download latest backup
   aws s3 cp s3://ucp-backups/database/latest.sql.gz.gpg /tmp/
   
   # Decrypt and decompress
   gpg --decrypt /tmp/latest.sql.gz.gpg | gunzip > /tmp/restore.sql
   
   # Restore database
   mysql -u $DB_USER -p$DB_PASS $DB_NAME < /tmp/restore.sql
   ```

3. **Verify data integrity**

   ```bash
   php artisan db:verify-integrity
   ```

4. **Run migrations if needed**

   ```bash
   php artisan migrate --force
   ```

5. **Clear caches**

   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   ```

6. **Verify application health**

   ```bash
   curl https://example.com/api/health
   ```

---

## Alerting

### Alert Configuration

```yaml
# alerting-rules.yml
groups:
  - name: application
    rules:
      - alert: HighErrorRate
        expr: rate(http_requests_total{status=~"5.."}[5m]) > 0.1
        for: 5m
        labels:
          severity: critical
        annotations:
          summary: High error rate detected
          
      - alert: SlowResponseTime
        expr: histogram_quantile(0.95, rate(http_request_duration_seconds_bucket[5m])) > 2
        for: 10m
        labels:
          severity: warning
        annotations:
          summary: Response time exceeding 2 seconds
          
      - alert: QueueBacklog
        expr: horizon_pending_jobs > 1000
        for: 15m
        labels:
          severity: warning
        annotations:
          summary: Queue backlog growing
```

### Notification Channels

#### Slack Integration

```php
// app/Notifications/SystemAlert.php
class SystemAlert extends Notification
{
    public function toSlack($notifiable): SlackMessage
    {
        return (new SlackMessage)
            ->error()
            ->content('System Alert: ' . $this->message)
            ->attachment(function ($attachment) {
                $attachment->title('Details')
                    ->fields([
                        'Environment' => config('app.env'),
                        'Time' => now()->toDateTimeString(),
                        'Severity' => $this->severity,
                    ]);
            });
    }
}
```

### On-Call Rotation

| Day | Primary | Secondary |
|-----|---------|-----------|
| Mon-Fri | DevOps Team | Backend Team |
| Sat-Sun | On-call Engineer | DevOps Lead |

---

## Dashboard Access

### Available Dashboards

| Dashboard | URL | Access Level |
|-----------|-----|--------------|
| Telescope | `/telescope` | Admin |
| Horizon | `/horizon` | Admin |
| Health Check | `/api/health` | Public |
| Metrics | `/admin/metrics` | Admin |

### Custom Metrics Dashboard

Access the custom metrics dashboard at `/admin/dashboard` to view:

- Real-time request rates
- Error trends
- Queue performance
- Cache efficiency
- Database query performance

---

## Environment Variables

```env
# Monitoring
TELESCOPE_ENABLED=true
HORIZON_ENABLED=true

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=debug
LOG_SLACK_WEBHOOK_URL=https://hooks.slack.com/services/xxx

# Alerting
ALERT_EMAIL=alerts@example.com
PAGERDUTY_KEY=xxx

# Backups
BACKUP_S3_BUCKET=ucp-backups
BACKUP_ENCRYPTION_KEY=xxx
```

---

## Maintenance

### Regular Tasks

- [ ] Review error logs weekly
- [ ] Check backup integrity monthly
- [ ] Update monitoring thresholds quarterly
- [ ] Test disaster recovery annually

### Log Analysis Commands

```bash
# View recent errors
tail -f storage/logs/laravel.log | grep -i error

# Count errors by type
grep -c "ERROR" storage/logs/laravel-$(date +%Y-%m-%d).log

# Find slow queries
grep "Slow query" storage/logs/performance.log
```
