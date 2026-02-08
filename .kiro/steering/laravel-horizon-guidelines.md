# Laravel Horizon Guidelines

## Overview

Laravel Horizon provides a beautiful dashboard and code-driven configuration for Laravel-powered Redis queues. This guide covers installation, configuration, deployment, and best practices for using Horizon in development and production environments.

## Prerequisites and Requirements

### System Requirements

- **Redis**: Horizon requires Redis to power your queue system
- **PHP Extensions**: PCNTL and POSIX extensions (available in Linux/WSL, not Windows)
- **Laravel Version**: Compatible with Laravel 8.x through 12.x
- **Queue Connection**: Must be set to `redis` in `config/queue.php`

### Cross-Platform Considerations

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Windows       │    │      WSL        │    │     Redis       │
│                 │    │                 │    │   (WSL/Local)   │
│ Laravel App     │◄──►│ Laravel Horizon │◄──►│                 │
│ Web Server      │    │ Queue Worker    │    │ Queue Storage   │
│ (php artisan    │    │ (php artisan    │    │ Cache Storage   │
│  serve)         │    │  horizon)       │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

**Windows Development**: Use WSL for Horizon due to PCNTL/POSIX extension requirements
**Linux/Production**: Native installation with full feature support

## Installation and Setup

### Installation via Composer

```bash
# Install Horizon (typically as dev dependency for development)
composer require laravel/horizon --dev

# For production environments
composer require laravel/horizon

# Publish Horizon assets and configuration
php artisan horizon:install
```

### Configuration Files

After installation, Horizon creates:

- `config/horizon.php` - Main configuration file
- `public/vendor/horizon/` - Dashboard assets
- Database migrations for job tracking

### Environment Configuration

```env
# Required environment variables
QUEUE_CONNECTION=redis
CACHE_STORE=redis  # Recommended for performance
SESSION_DRIVER=redis  # Optional but recommended

# Redis configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CACHE_DB=1
REDIS_SESSION_DB=2

# Horizon-specific configuration
HORIZON_NAME="YourAppName"
HORIZON_PATH=horizon
```

## Configuration Best Practices

### Environment-Based Configuration

```php
// config/horizon.php
'environments' => [
    'production' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['default', 'high', 'low'],
            'balance' => 'auto',
            'autoScalingStrategy' => 'time',
            'minProcesses' => 1,
            'maxProcesses' => 10,
            'balanceMaxShift' => 1,
            'balanceCooldown' => 3,
            'tries' => 3,
            'nice' => 0,
            'timeout' => 60,
            'memory' => 128,
        ],
    ],

    'local' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['default'],
            'balance' => 'simple',
            'minProcesses' => 1,
            'maxProcesses' => 3,
            'tries' => 3,
            'timeout' => 60,
        ],
    ],
],
```

### Balancing Strategies

#### Simple Balancing

```php
'balance' => 'simple',  // Splits jobs evenly between workers
```

#### Auto Balancing (Recommended)

```php
'balance' => 'auto',
'autoScalingStrategy' => 'time',  // or 'size'
'minProcesses' => 1,
'maxProcesses' => 10,
'balanceMaxShift' => 1,      // Max processes to add/remove per cycle
'balanceCooldown' => 3,      // Seconds between scaling decisions
```

#### No Balancing

```php
'balance' => false,  // Uses Laravel's default queue processing order
```

### Queue Priority Configuration

```php
// High-priority queues first
'queue' => ['critical', 'high', 'default', 'low'],

// Separate supervisors for different priorities
'environments' => [
    'production' => [
        'critical-supervisor' => [
            'queue' => ['critical'],
            'minProcesses' => 2,
            'maxProcesses' => 5,
        ],
        'general-supervisor' => [
            'queue' => ['default', 'low'],
            'minProcesses' => 1,
            'maxProcesses' => 8,
        ],
    ],
],
```

## Dashboard Configuration

### Authorization Setup

```php
// app/Providers/HorizonServiceProvider.php
protected function gate(): void
{
    Gate::define('viewHorizon', function (User $user) {
        return in_array($user->email, [
            'admin@example.com',
            'developer@example.com',
        ]);
    });
}

// For IP-based restrictions
protected function gate(): void
{
    Gate::define('viewHorizon', function (User $user = null) {
        return in_array(request()->ip(), [
            '127.0.0.1',
            '192.168.1.100',
        ]);
    });
}
```

### Custom Dashboard Path

```php
// config/horizon.php
'path' => 'admin/queues',  // Access via /admin/queues instead of /horizon
```

## Job Management and Tagging

### Automatic Tagging

Horizon automatically tags jobs with Eloquent model information:

```php
// This job will be tagged with "App\Models\User:123"
class ProcessUser implements ShouldQueue
{
    public function __construct(public User $user) {}
}

// Dispatch the job
ProcessUser::dispatch(User::find(123));
```

### Manual Tagging

```php
class ProcessVideo implements ShouldQueue
{
    use Queueable;

    public function __construct(public Video $video) {}

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'video',
            'render',
            'user:' . $this->video->user_id,
            'priority:high'
        ];
    }
}
```

### Event Listener Tagging

```php
class SendNotification implements ShouldQueue
{
    /**
     * Get the tags for the queued event listener.
     */
    public function tags(UserRegistered $event): array
    {
        return [
            'notification',
            'user:' . $event->user->id,
            'type:welcome'
        ];
    }
}
```

## Running Horizon

### Development Commands

```bash
# Start Horizon
php artisan horizon

# Start Horizon with specific environment
APP_ENV=production php artisan horizon

# WSL-specific command with Redis queues
wsl bash -c "QUEUE_CONNECTION=redis php artisan horizon"
```

### Management Commands

```bash
# Check Horizon status
php artisan horizon:status

# Pause Horizon (stops accepting new jobs)
php artisan horizon:pause

# Continue Horizon (resumes accepting jobs)
php artisan horizon:continue

# Pause specific supervisor
php artisan horizon:pause-supervisor supervisor-1

# Continue specific supervisor
php artisan horizon:continue-supervisor supervisor-1

# Gracefully terminate Horizon
php artisan horizon:terminate
```

### Supervisor Status Commands

```bash
# Check specific supervisor status
php artisan horizon:supervisor-status supervisor-1

# List all supervisors and their status
php artisan horizon:status
```

## Production Deployment

### Process Monitoring with Supervisor

Create `/etc/supervisor/conf.d/horizon.conf`:

```ini
[program:horizon]
process_name=%(program_name)s
command=php /var/www/html/artisan horizon
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/horizon.log
stopwaitsecs=3600
```

### Supervisor Management

```bash
# Update Supervisor configuration
sudo supervisorctl reread
sudo supervisorctl update

# Start Horizon via Supervisor
sudo supervisorctl start horizon

# Check Horizon status
sudo supervisorctl status horizon

# Restart Horizon
sudo supervisorctl restart horizon
```

### Deployment Process

```bash
# 1. Terminate Horizon gracefully
php artisan horizon:terminate

# 2. Deploy your application code
git pull origin main
composer install --no-dev --optimize-autoloader

# 3. Run migrations if needed
php artisan migrate --force

# 4. Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Restart Horizon via Supervisor
sudo supervisorctl restart horizon
```

### Zero-Downtime Deployment

```bash
# Use horizon:terminate instead of stopping supervisor
php artisan horizon:terminate

# Supervisor will automatically restart Horizon with new code
# No manual restart needed
```

## Monitoring and Notifications

### Notification Configuration

```php
// app/Providers/HorizonServiceProvider.php
public function boot(): void
{
    parent::boot();

    // Email notifications
    Horizon::routeMailNotificationsTo('admin@example.com');

    // Slack notifications
    Horizon::routeSlackNotificationsTo(
        'https://hooks.slack.com/services/...',
        '#alerts'
    );

    // SMS notifications
    Horizon::routeSmsNotificationsTo('+1234567890');
}
```

### Wait Time Thresholds

```php
// config/horizon.php
'waits' => [
    'redis:critical' => 30,   // 30 seconds for critical queue
    'redis:default' => 60,    // 60 seconds for default queue
    'redis:low' => 120,       // 2 minutes for low priority queue
],
```

### Metrics Collection

```php
// routes/console.php or app/Console/Kernel.php
use Illuminate\Support\Facades\Schedule;

Schedule::command('horizon:snapshot')->everyFiveMinutes();
```

## Maintenance and Troubleshooting

### Maintenance Mode Handling

```php
// config/horizon.php - Force processing during maintenance
'environments' => [
    'production' => [
        'supervisor-1' => [
            'force' => true,  // Process jobs even in maintenance mode
            // ... other options
        ],
    ],
],
```

### Failed Job Management

```bash
# Delete specific failed job
php artisan horizon:forget 5

# Delete all failed jobs
php artisan horizon:forget --all

# Clear all jobs from default queue
php artisan horizon:clear

# Clear jobs from specific queue
php artisan horizon:clear --queue=emails
```

### Job Silencing

```php
// config/horizon.php
'silenced' => [
    App\Jobs\ProcessPodcast::class,
    App\Jobs\SendNewsletter::class,
],

// Or implement Silenced interface
use Laravel\Horizon\Contracts\Silenced;

class ProcessPodcast implements ShouldQueue, Silenced
{
    use Queueable;
    // Job implementation
}
```

### Common Issues and Solutions

#### Horizon Not Starting

```bash
# Check Redis connection
redis-cli ping

# Verify queue configuration
php artisan config:show queue.connections.redis

# Check for PCNTL/POSIX extensions (Linux/WSL only)
php -m | grep -E "(pcntl|posix)"
```

#### Jobs Not Processing

```bash
# Verify Horizon is running
php artisan horizon:status

# Check queue connection
php artisan queue:work --once

# Verify Redis queues
redis-cli LLEN "queues:default"
```

#### Memory Issues

```php
// config/horizon.php - Adjust memory limits
'memory' => 512,  // MB per worker process

// Or in supervisor configuration
'environments' => [
    'production' => [
        'supervisor-1' => [
            'memory' => 256,  // Restart workers after 256MB
        ],
    ],
],
```

## Performance Optimization

### Redis Configuration

```bash
# /etc/redis/redis.conf optimizations
maxmemory 2gb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
```

### Queue Optimization

```php
// Optimize queue configuration
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => 90,
        'block_for' => null,  // Use blocking pop for better performance
    ],
],
```

### Scaling Strategies

```php
// Auto-scaling based on queue size
'autoScalingStrategy' => 'size',
'minProcesses' => 1,
'maxProcesses' => 20,
'balanceMaxShift' => 2,
'balanceCooldown' => 5,
```

## AI Agent Instructions

When working with Laravel Horizon:

1. **Always verify Redis connection** before starting Horizon operations
2. **Use WSL for Windows development** due to PCNTL/POSIX extension requirements
3. **Check Horizon status** before performing queue operations
4. **Use proper environment variables** when running Horizon commands in WSL
5. **Monitor supervisor processes** in production environments
6. **Implement proper authorization** for dashboard access
7. **Configure appropriate balancing strategies** based on workload patterns
8. **Set up monitoring and notifications** for production deployments
9. **Use graceful termination** during deployments to prevent job loss
10. **Implement proper tagging** for job organization and monitoring

## Security Considerations

### Dashboard Security

```php
// Restrict dashboard access by role
Gate::define('viewHorizon', function (User $user) {
    return $user->hasRole('admin') || $user->hasRole('developer');
});

// IP whitelist for additional security
Gate::define('viewHorizon', function (User $user = null) {
    $allowedIps = ['127.0.0.1', '10.0.0.0/8', '192.168.0.0/16'];
    return in_array(request()->ip(), $allowedIps);
});
```

### Redis Security

```bash
# Secure Redis configuration
requirepass your_secure_password
bind 127.0.0.1
protected-mode yes
```

### Process Security

```bash
# Run Horizon as non-root user
user=www-data  # In supervisor configuration

# Set appropriate file permissions
chmod 755 /var/www/html/artisan
chown -R www-data:www-data /var/www/html/storage
```

This guide ensures reliable Horizon deployment and operation across different environments while maintaining security and performance best practices.
