---
inclusion:
  - when: executing commands in WSL or cross-platform Windows/Linux environments
  - when: troubleshooting WSL, Redis, or service connectivity issues
  - when: setting up Laravel Horizon or queue workers on Windows
  - when: configuring PHP extensions (PCNTL, POSIX) or development environments
---

# WSL Development Guidelines

## Overview

Windows Subsystem for Linux (WSL) enables developers to run a GNU/Linux environment directly on Windows without the overhead of a traditional virtual machine. This guide provides best practices for using WSL in development workflows, particularly for cross-platform Laravel applications.

## WSL Installation and Setup

### Prerequisites

- Windows 10 version 2004+ (Build 19041+) or Windows 11
- Administrator privileges for initial installation

### Installation Commands

```powershell
# Install WSL with default Ubuntu distribution
wsl --install

# List available distributions
wsl --list --online

# Install specific distribution
wsl --install -d <DistroName>

# For offline installation or download issues
wsl --install --web-download -d <DistroName>
```

### Version Management

```powershell
# Check WSL version for installed distributions
wsl --list --verbose

# Set default WSL version for new installations
wsl --set-default-version 2

# Upgrade existing distribution to WSL 2
wsl --set-version <DistroName> 2

# Set default distribution
wsl --set-default <DistroName>
```

## Development Environment Configuration

### PHP Environment Setup

```bash
# Update package lists
sudo apt update

# Install PHP 8.4+ with required extensions
sudo apt install -y php8.4-cli php8.4-common php8.4-mysql php8.4-xml php8.4-curl php8.4-mbstring php8.4-zip php8.4-bcmath php8.4-intl php8.4-redis

# Install additional utilities
sudo apt install -y unzip p7zip-full curl git

# Verify PHP installation
php --version
php -m | grep -E "(pcntl|posix|redis)"
```

### Composer Installation

```bash
# Download and install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Verify installation
composer --version
```

### Redis Setup

```bash
# Install Redis server
sudo apt install -y redis-server

# Start Redis service
sudo service redis-server start

# Enable Redis to start on boot
sudo systemctl enable redis-server

# Test Redis connection
redis-cli ping
```

## Cross-Platform Development Patterns

### File System Access

```bash
# Access Windows files from WSL
cd /mnt/c/Users/YourUsername/Projects

# Access WSL files from Windows
# Use \\wsl$\Ubuntu\home\username\ in Windows Explorer
```

### Command Execution Best Practices

#### PowerShell to WSL Command Patterns

```powershell
# CORRECT: Wrap Unix command chains in WSL context
wsl bash -c "ps aux | grep horizon"
wsl bash -c "php artisan horizon:status && echo 'Status checked'"

# INCORRECT: Direct piping from WSL to PowerShell
wsl ps aux | grep horizon  # This fails - grep runs in PowerShell context
```

#### Environment Variable Handling

```powershell
# Pass environment variables to WSL commands
wsl bash -c "QUEUE_CONNECTION=redis php artisan horizon"

# Set persistent environment variables in WSL
wsl bash -c "echo 'export QUEUE_CONNECTION=redis' >> ~/.bashrc"
```

### Service Management

```bash
# Start services in WSL
sudo service redis-server start
sudo service mysql start

# Check service status
sudo service redis-server status
sudo service mysql status

# Enable services to start automatically
sudo systemctl enable redis-server
sudo systemctl enable mysql
```

## Laravel-Specific WSL Configuration

### Horizon Queue Processing

```bash
# Install Laravel Horizon via WSL (required for PCNTL/POSIX extensions)
composer require laravel/horizon --dev

# Publish Horizon configuration
php artisan horizon:install

# Start Horizon with Redis queues
QUEUE_CONNECTION=redis php artisan horizon

# Check Horizon status
php artisan horizon:status
```

### Environment Configuration

```bash
# WSL-specific environment variables for Laravel
export QUEUE_CONNECTION=redis
export CACHE_STORE=redis
export SESSION_DRIVER=redis
export REDIS_HOST=127.0.0.1
export REDIS_PORT=6379
```

### Cross-Platform Architecture

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

## Performance Optimization

### Memory Management

```bash
# Configure WSL memory limits in .wslconfig (Windows user directory)
# C:\Users\<UserName>\.wslconfig
[wsl2]
memory=4GB
processors=2
swap=2GB
```

### File System Performance

```bash
# Use WSL file system for better performance
# Store project files in WSL home directory instead of /mnt/c/
cd ~
mkdir projects
cd projects
```

### Network Configuration

```bash
# Configure WSL networking for better performance
# Add to /etc/wsl.conf
[network]
generateHosts = false
generateResolvConf = false
```

## Troubleshooting Common Issues

### Service Startup Issues

```bash
# If services don't start automatically
sudo service redis-server start
sudo service mysql start

# Check service logs
sudo journalctl -u redis-server
sudo journalctl -u mysql
```

### Permission Issues

```bash
# Fix Laravel storage permissions
chmod -R 775 storage bootstrap/cache
chown -R $USER:www-data storage bootstrap/cache
```

### Network Connectivity

```bash
# Test Redis connectivity
redis-cli ping

# Test MySQL connectivity
mysql -u root -p -e "SELECT 1;"

# Check listening ports
netstat -tlnp | grep -E "(6379|3306)"
```

### WSL Reset and Recovery

```powershell
# Restart WSL service
wsl --shutdown
wsl

# Reset specific distribution (WARNING: This deletes all data)
wsl --unregister <DistroName>
wsl --install -d <DistroName>
```

## Security Best Practices

### User Account Management

```bash
# Create non-root user for development
sudo adduser developer
sudo usermod -aG sudo developer

# Switch to development user
su - developer
```

### Service Security

```bash
# Configure Redis security
sudo nano /etc/redis/redis.conf
# Uncomment and set: requirepass your_secure_password

# Configure MySQL security
sudo mysql_secure_installation
```

### File Permissions

```bash
# Set secure permissions for Laravel
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache
```

## Development Workflow Integration

### IDE Configuration

- **VS Code**: Install "Remote - WSL" extension for seamless development
- **PhpStorm**: Configure WSL as remote interpreter
- **Terminal**: Use Windows Terminal with WSL profiles

### Git Configuration

```bash
# Configure Git in WSL
git config --global user.name "Your Name"
git config --global user.email "your.email@example.com"

# Use Windows credential manager
git config --global credential.helper "/mnt/c/Program\ Files/Git/mingw64/libexec/git-core/git-credential-manager-core.exe"
```

### Backup and Sync

```bash
# Export WSL distribution for backup
wsl --export Ubuntu C:\WSL-Backups\ubuntu-backup.tar

# Import WSL distribution from backup
wsl --import Ubuntu C:\WSL\Ubuntu C:\WSL-Backups\ubuntu-backup.tar
```

## AI Agent Instructions

When working with WSL in development environments:

1. **Always use WSL context for Unix commands**: Wrap command chains in `wsl bash -c "command1 | command2"`
2. **Never pipe WSL output to PowerShell commands**: This causes context switching errors
3. **Verify service availability**: Check Redis, MySQL, and other services are running before executing dependent commands
4. **Use appropriate file paths**: Understand the difference between Windows paths (/mnt/c/) and WSL paths (~/)
5. **Handle environment variables correctly**: Pass environment variables explicitly to WSL commands when needed
6. **Consider cross-platform implications**: Some operations work better in WSL (queue processing) while others work better in Windows (web serving)

## Common Error Prevention

### Command Execution Errors

```powershell
# WRONG: This will fail with "grep: The term 'grep' is not recognized"
wsl ps aux | grep horizon

# RIGHT: This works correctly
wsl bash -c "ps aux | grep horizon"
```

### Service Dependency Errors

```bash
# Always check service status before dependent operations
redis-cli ping || sudo service redis-server start
php artisan horizon:status
```

### Permission Errors

```bash
# Ensure proper permissions for Laravel operations
chmod -R 775 storage bootstrap/cache
sudo chown -R $USER:www-data storage bootstrap/cache
```

This guide ensures consistent, reliable WSL usage across development teams and prevents common cross-platform development issues.
