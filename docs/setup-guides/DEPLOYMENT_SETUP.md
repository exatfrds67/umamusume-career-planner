# Deployment Setup Guide

## Overview

This guide explains how to set up the deployment pipeline for the UmamusumeCareerPlanner application using GitHub
Actions.

---

## Prerequisites

- GitHub repository with admin access
- Staging and production servers with SSH access
- PHP 8.4+ installed on servers
- Composer and npm installed on servers
- Database configured on servers

---

## 1. GitHub Environments Setup

### Create Environments

1. Navigate to your GitHub repository
2. Go to **Settings** → **Environments**
3. Click **New environment**

#### Staging Environment

1. Name: `staging`
2. Add deployment protection rules (optional):
   - Required reviewers: None (for automatic deployment)
   - Wait timer: 0 minutes
3. Click **Configure environment**

#### Production Environment

1. Name: `production`
2. Add deployment protection rules (recommended):
   - Required reviewers: Select team members who must approve
   - Wait timer: 5 minutes (optional safety delay)
   - Deployment branches: Only `main` branch
3. Click **Configure environment**

---

## 2. Configure Secrets

### Staging Secrets

Navigate to **Settings** → **Environments** → **staging** → **Add secret**

Add the following secrets:

| Secret Name           | Description                        | Example                                  |
| --------------------- | ---------------------------------- | ---------------------------------------- |
| `STAGING_SSH_KEY`     | Private SSH key for staging server | `-----BEGIN OPENSSH PRIVATE KEY-----...` |
| `STAGING_SSH_HOST`    | Staging server hostname or IP      | `staging.example.com` or `192.168.1.100` |
| `STAGING_SSH_USER`    | SSH username                       | `deploy` or `www-data`                   |
| `STAGING_DEPLOY_PATH` | Deployment directory path          | `/var/www/staging`                       |

### Production Secrets

Navigate to **Settings** → **Environments** → **production** → **Add secret**

Add the following secrets:

| Secret Name              | Description                           | Example                                  |
| ------------------------ | ------------------------------------- | ---------------------------------------- |
| `PRODUCTION_SSH_KEY`     | Private SSH key for production server | `-----BEGIN OPENSSH PRIVATE KEY-----...` |
| `PRODUCTION_SSH_HOST`    | Production server hostname or IP      | `example.com` or `192.168.1.200`         |
| `PRODUCTION_SSH_USER`    | SSH username                          | `deploy` or `www-data`                   |
| `PRODUCTION_DEPLOY_PATH` | Deployment directory path             | `/var/www/production`                    |

---

## 3. SSH Key Setup

### Generate SSH Key Pair

On your local machine:

```bash
# Generate a new SSH key pair for deployment
ssh-keygen -t ed25519 -C "deployment@example.com" -f ~/.ssh/deploy_key

# This creates:
# - ~/.ssh/deploy_key (private key - add to GitHub secrets)
# - ~/.ssh/deploy_key.pub (public key - add to server)
```text

## Add Public Key to Servers

### Staging Server

```bash
# Copy public key to staging server
ssh-copy-id -i ~/.ssh/deploy_key.pub user@staging.example.com

# Or manually:
cat ~/.ssh/deploy_key.pub | ssh user@staging.example.com "mkdir -p ~/.ssh && cat >> ~/.ssh/authorized_keys"
```

## Production Server

```bash
# Copy public key to production server
ssh-copy-id -i ~/.ssh/deploy_key.pub user@example.com

# Or manually:
cat ~/.ssh/deploy_key.pub | ssh user@example.com "mkdir -p ~/.ssh && cat >> ~/.ssh/authorized_keys"
```text

## Add Private Key to GitHub Secrets

```bash
# Display private key (copy this to GitHub secret)
cat ~/.ssh/deploy_key

# Copy the entire output including:
# -----BEGIN OPENSSH PRIVATE KEY-----
# ...
# -----END OPENSSH PRIVATE KEY-----
```

---

## 4. Server Configuration

### Directory Structure

```text
/var/www/
├── staging/          # Staging deployment
│   ├── .env
│   ├── storage/
│   └── ...
├── production/       # Production deployment
│   ├── .env
│   ├── storage/
│   └── ...
└── backups/          # Backup storage
    ├── staging/
    └── production/
```

### Create Deployment Directories

#### Staging

```bash
ssh user@staging.example.com

# Create directories
sudo mkdir -p /var/www/staging
sudo mkdir -p /var/www/backups/staging
sudo chown -R www-data:www-data /var/www/staging
sudo chown -R www-data:www-data /var/www/backups/staging

# Set permissions
sudo chmod -R 755 /var/www/staging
```text

## Production

```bash
ssh user@example.com

# Create directories
sudo mkdir -p /var/www/production
sudo mkdir -p /var/www/backups/production
sudo chown -R www-data:www-data /var/www/production
sudo chown -R www-data:www-data /var/www/backups/production

# Set permissions
sudo chmod -R 755 /var/www/production
```

## Configure Environment Files

### Staging `.env`

```bash
ssh user@staging.example.com
cd /var/www/staging

# Create .env file
cat > .env << 'EOF'
APP_NAME="UmamusumeCareerPlanner"
APP_ENV=staging
APP_KEY=base64:GENERATE_THIS_WITH_php_artisan_key:generate
APP_DEBUG=true
APP_URL=https://staging.example.com

DB_CONNECTION=sqlite
DB_DATABASE=/var/www/staging/database/database.sqlite

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

TELESCOPE_ENABLED=true
HORIZON_ENABLED=true
EOF

# Generate application key
php artisan key:generate
```text

## Production `.env`

```bash
ssh user@example.com
cd /var/www/production

# Create .env file
cat > .env << 'EOF'
APP_NAME="UmamusumeCareerPlanner"
APP_ENV=production
APP_KEY=base64:GENERATE_THIS_WITH_php_artisan_key:generate
APP_DEBUG=false
APP_URL=https://example.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=umamusume_career_planner
DB_USERNAME=dbuser
DB_PASSWORD=secure_password_here

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

TELESCOPE_ENABLED=false
HORIZON_ENABLED=true
EOF

# Generate application key
php artisan key:generate
```

---

## 5. Web Server Configuration

### Apache Configuration

#### Staging Virtual Host

```apache
<VirtualHost *:80>
    ServerName staging.example.com
    DocumentRoot /var/www/staging/public

    <Directory /var/www/staging/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/staging-error.log
    CustomLog ${APACHE_LOG_DIR}/staging-access.log combined
</VirtualHost>

<VirtualHost *:443>
    ServerName staging.example.com
    DocumentRoot /var/www/staging/public

    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/staging.crt
    SSLCertificateKeyFile /etc/ssl/private/staging.key

    <Directory /var/www/staging/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/staging-ssl-error.log
    CustomLog ${APACHE_LOG_DIR}/staging-ssl-access.log combined
</VirtualHost>
```text

#### Production Virtual Host

```apache
<VirtualHost *:80>
    ServerName example.com
    Redirect permanent / https://example.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName example.com
    DocumentRoot /var/www/production/public

    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/example.com.crt
    SSLCertificateKeyFile /etc/ssl/private/example.com.key

    <Directory /var/www/production/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/production-ssl-error.log
    CustomLog ${APACHE_LOG_DIR}/production-ssl-access.log combined
</VirtualHost>
```

### Enable Sites

```bash
# Enable staging
sudo a2ensite staging.conf
sudo systemctl reload apache2

# Enable production
sudo a2ensite production.conf
sudo systemctl reload apache2
```text

---

## 6. Initial Deployment

### Manual Initial Setup

Before using the automated deployment, perform initial setup:

#### Staging

```bash
ssh user@staging.example.com
cd /var/www/staging

# Clone repository (first time only)
git clone https://github.com/yourusername/umamusume-career-planner.git .

# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# Set up database
php artisan migrate --force
php artisan db:seed --force

# Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Create storage link
php artisan storage:link

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Production

```bash
ssh user@example.com
cd /var/www/production

# Clone repository (first time only)
git clone https://github.com/yourusername/umamusume-career-planner.git .

# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# Set up database
php artisan migrate --force
php artisan db:seed --force

# Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Create storage link
php artisan storage:link

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```text

---

## 7. Testing the Deployment Pipeline

### Test Staging Deployment

1. Push changes to `main` branch
2. GitHub Actions will automatically deploy to staging
3. Monitor the workflow in **Actions** tab
4. Verify deployment at `https://staging.example.com`

### Test Production Deployment

1. Go to **Actions** tab in GitHub
2. Select **Deploy** workflow
3. Click **Run workflow**
4. Select `production` environment
5. Click **Run workflow**
6. Approve deployment (if protection rules are enabled)
7. Monitor deployment progress
8. Verify deployment at `https://example.com`

---

## 8. Troubleshooting

### SSH Connection Issues

```bash
# Test SSH connection
ssh -i ~/.ssh/deploy_key user@staging.example.com

# Check SSH key permissions
chmod 600 ~/.ssh/deploy_key
chmod 644 ~/.ssh/deploy_key.pub

# Verify authorized_keys on server
ssh user@staging.example.com "cat ~/.ssh/authorized_keys"
```

## Permission Issues

```bash
# Fix storage permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Fix .env permissions
sudo chmod 644 .env
```text

## Deployment Failures

1. Check GitHub Actions logs
2. Verify secrets are correctly set
3. Test SSH connection manually
4. Check server logs: `tail -f /var/log/apache2/error.log`
5. Check Laravel logs: `tail -f storage/logs/laravel.log`

---

## 9. Rollback Procedures

### Automatic Rollback

If production deployment fails, the workflow automatically triggers rollback.

### Manual Rollback

```bash
ssh user@example.com
cd /var/www

# List available backups
ls -lh backups/production/

# Restore from backup
cd production
php artisan down
tar -xzf ../backups/production/backup-YYYYMMDD-HHMMSS.tar.gz
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

---

## 10. Maintenance

### Regular Tasks

- Monitor deployment logs weekly
- Review backup integrity monthly
- Update SSH keys annually
- Test rollback procedures quarterly

### Security Best Practices

- Rotate SSH keys regularly
- Use strong passwords for database
- Keep secrets secure and never commit them
- Enable 2FA for GitHub account
- Restrict SSH access by IP if possible
- Monitor failed login attempts

---

## Support

For deployment issues:

1. Check GitHub Actions logs
2. Review server logs
3. Consult the troubleshooting section
4. Contact DevOps team

---

## Checklist

- [ ] GitHub environments created (staging, production)
- [ ] All secrets configured
- [ ] SSH keys generated and deployed
- [ ] Server directories created
- [ ] Environment files configured
- [ ] Web server configured
- [ ] Initial deployment completed
- [ ] Deployment pipeline tested
- [ ] Rollback procedures tested
- [ ] Monitoring configured
