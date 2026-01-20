# Umamusume Career Planner - Deployment Guide

## 1. System Requirements

- **PHP**: 8.4+ (Extensions: bcmath, ctype, curl, dom, fileinfo, json, mbstring, openssl, pdo_mysql, redis, tokenizer, xml)
- **Database**: MySQL 8.0+ or PostgreSQL 14+
- **Cache/Queue**: Redis 6.0+
- **Web Server**: Nginx or Apache
- **Node.js**: 20.x+ (for building frontend assets)
- **OCR Engine**: Tesseract OCR 5.0+ (`tesseract-ocr`, `libtesseract-dev`)

## 2. Environment Configuration

Copy `.env.production` to `.env` and configure:

```ini
APP_ENV=production
APP_URL=https://planner.example.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=umamusume_prod
DB_USERNAME=planner_user
DB_PASSWORD=secret_password

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1

# AI Configuration
OLLAMA_HOST=http://localhost:11434
UMAPYOI_API_URL=https://api.umapyoi.net
```

## 3. Installation Steps

### 3.1 Clone & Dependencies

```bash
git clone https://github.com/your-repo/umamusume-career-planner.git
cd umamusume-career-planner
composer install --optimize-autoloader --no-dev
npm install
```

### 3.2 Build Frontend

```bash
npm run build
```

### 3.3 Setup Database

```bash
php artisan migrate --force
```

### 3.4 Optimization

```bash
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache
```

## 4. OCR Setup (Ubuntu/Debian)

```bash
sudo apt-get update
sudo apt-get install tesseract-ocr tesseract-ocr-jpn tesseract-ocr-eng
```

Verify path matches `TESSERACT_PATH` in `.env`.

## 5. Queue Workers (Supervisor)

Create `/etc/supervisor/conf.d/umamusume-worker.conf`:

```ini
[program:umamusume-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/project/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/project/storage/logs/worker.log
```

## 6. Maintenance

- **Backup**: Schedule daily backups of MySQL and `.env` file.
    - **Updates**:

        ```bash
        git pull
        composer install --no-dev
        php artisan migrate --force
        npm run build
        php artisan queue:restart
        ```
