# Setup Browser Tests for Pest 4
# This script installs and configures browser testing dependencies

Write-Host "Setting up Pest 4 Browser Testing..." -ForegroundColor Green

# Check if Composer is available
if (-not (Get-Command composer -ErrorAction SilentlyContinue)) {
    Write-Host "Error: Composer is not installed or not in PATH" -ForegroundColor Red
    exit 1
}

# Check if PHP is available
if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
    Write-Host "Error: PHP is not installed or not in PATH" -ForegroundColor Red
    exit 1
}

Write-Host "`nStep 1: Installing Pest dependencies..." -ForegroundColor Cyan
composer require pestphp/pest --dev --with-all-dependencies

Write-Host "`nStep 2: Installing Pest browser plugin..." -ForegroundColor Cyan
composer require pestphp/pest-plugin-browser --dev

Write-Host "`nStep 3: Installing Playwright browsers..." -ForegroundColor Cyan
# Pest 4 uses Playwright for browser automation
npx playwright install chromium

Write-Host "`nStep 4: Creating .env.testing file..." -ForegroundColor Cyan
if (-not (Test-Path ".env.testing")) {
    Copy-Item ".env.example" ".env.testing"
    
    # Update .env.testing for browser tests
    $envContent = Get-Content ".env.testing"
    $envContent = $envContent -replace "APP_ENV=.*", "APP_ENV=testing"
    $envContent = $envContent -replace "APP_DEBUG=.*", "APP_DEBUG=true"
    $envContent = $envContent -replace "DB_CONNECTION=.*", "DB_CONNECTION=sqlite"
    $envContent = $envContent -replace "DB_DATABASE=.*", "DB_DATABASE=:memory:"
    $envContent = $envContent -replace "CACHE_DRIVER=.*", "CACHE_DRIVER=array"
    $envContent = $envContent -replace "SESSION_DRIVER=.*", "SESSION_DRIVER=array"
    $envContent = $envContent -replace "QUEUE_CONNECTION=.*", "QUEUE_CONNECTION=sync"
    
    # Add browser test specific settings
    $envContent += "`n# Browser Testing"
    $envContent += "`nPEST_BROWSER_HEADLESS=true"
    $envContent += "`nPEST_BROWSER_TIMEOUT=30000"
    $envContent += "`nAPP_URL=http://127.0.0.1:8000"
    
    Set-Content ".env.testing" $envContent
    Write-Host "Created .env.testing with browser test configuration" -ForegroundColor Green
} else {
    Write-Host ".env.testing already exists, skipping..." -ForegroundColor Yellow
}

Write-Host "`nStep 5: Running database migrations..." -ForegroundColor Cyan
php artisan migrate:fresh --seed --env=testing

Write-Host "`nStep 6: Building frontend assets..." -ForegroundColor Cyan
npm run build

Write-Host "`n✓ Browser testing setup complete!" -ForegroundColor Green
Write-Host "`nTo run browser tests:" -ForegroundColor Cyan
Write-Host "  1. Start the development server: php artisan serve" -ForegroundColor White
Write-Host "  2. Run browser tests: php artisan test --group=browser" -ForegroundColor White
Write-Host "`nFor more information, see tests/Browser/README.md" -ForegroundColor Cyan
