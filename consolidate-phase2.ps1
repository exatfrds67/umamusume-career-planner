#!/usr/bin/env pwsh
# Phase 2 Consolidation Script
# Moves Redis, Larastan, and Audit files to archive directories

$projectRoot = "c:\XAMPP\htdocs\umamusume-career-planner"
$redisDir = "$projectRoot\docs\redis"
$redisArchive = "$projectRoot\docs\archive\legacy\redis"
$larastanDir = "$projectRoot\docs\larastan"
$larastanArchive = "$projectRoot\docs\archive\legacy\larastan"
$auditReportsDir = "$projectRoot\docs\audit-reports"
$adminAuditDir = "$projectRoot\docs\admin-audit"
$auditsDir = "$projectRoot\docs\audits"

Write-Host "=== Phase 2: Consolidation ===" -ForegroundColor Cyan
Write-Host ""

# Redis consolidation
Write-Host "Step 1: Consolidating Redis files..." -ForegroundColor Green
$redisFilesToMove = @(
    "REDIS_SETUP_SUMMARY.md",
    "REDIS_SETUP_CHECKLIST.md",
    "REDIS_SETUP_COMPLETED.md",
    "REDIS_SETUP_FINAL_REPORT.md",
    "REDIS_STEP_BY_STEP_IMPLEMENTATION.md",
    "REDIS_CURRENT_STATUS.md",
    "REDIS_ISSUE_RESOLVED.md",
    "REDIS_ISSUE_SUMMARY.md",
    "REDIS_DIAGNOSIS.md",
    "REDIS_APACHE_FIX.md",
    "FIX_REDIS_WEB_ERROR.md",
    "WSL_REDIS_FIX.md",
    "WSL_REDIS_INVESTIGATION_COMPLETE.md",
    "REDIS_XAMPP_SOLUTION.md",
    "REDIS_IMPLEMENTATION_SUMMARY.md",
    "REDIS_TESTING_GUIDE.md",
    "REDIS_WSL_SETUP_GUIDE.md",
    "UPDATE_REDIS_TESTS.md",
    "REDIS_QUICK_REFERENCE.md",
    "REDIS_COMMANDS_REFERENCE.md",
    "REDIS_DOCUMENTATION_INDEX.md",
    "ADD_REDIS_TO_PHP_INI.txt"
)

foreach ($file in $redisFilesToMove) {
    $source = "$redisDir\$file"
    if (Test-Path $source) {
        Move-Item -Path $source -Destination "$redisArchive\" -Force
        Write-Host "  ✓ $file"
    }
}
Write-Host "Redis consolidation complete!" -ForegroundColor Green
Write-Host ""

# Larastan consolidation
Write-Host "Step 2: Consolidating Larastan files..." -ForegroundColor Green
$larastanFilesToMove = @(
    "larastan-batch3-summary.md",
    "larastan-batch6-summary.md",
    "larastan-final-summary.md",
    "larastan-progress-report.md",
    "larastan-progress-summary.md",
    "larastan-fix-strategy.md",
    "larastan-models-fix-summary.md",
    "larastan-fix-plan.md",
    "larastan-level9-fix-strategy.md",
    "larastan-level9-fix-summary-jan28.md"
)

foreach ($file in $larastanFilesToMove) {
    $source = "$larastanDir\$file"
    if (Test-Path $source) {
        Move-Item -Path $source -Destination "$larastanArchive\" -Force
        Write-Host "  ✓ $file"
    }
}
Write-Host "Larastan consolidation complete!" -ForegroundColor Green
Write-Host ""

# Audit consolidation
Write-Host "Step 3: Consolidating Audit files..." -ForegroundColor Green

# Move from audit-reports to audits
if (Test-Path "$auditReportsDir\AUDIT_REPORT_FINAL.md") {
    Move-Item -Path "$auditReportsDir\AUDIT_REPORT_FINAL.md" -Destination "$auditsDir\" -Force
    Write-Host "  ✓ AUDIT_REPORT_FINAL.md"
}
if (Test-Path "$auditReportsDir\AUDIT_REPORT_PHASE_1.md") {
    Move-Item -Path "$auditReportsDir\AUDIT_REPORT_PHASE_1.md" -Destination "$auditsDir\" -Force
    Write-Host "  ✓ AUDIT_REPORT_PHASE_1.md"
}

# Move from admin-audit to audits
if (Test-Path "$adminAuditDir\2026-02-28-admin-ui-ux-audit.md") {
    Move-Item -Path "$adminAuditDir\2026-02-28-admin-ui-ux-audit.md" -Destination "$auditsDir\" -Force
    Write-Host "  ✓ 2026-02-28-admin-ui-ux-audit.md"
}
if (Test-Path "$adminAuditDir\2026-02-28-re-audit-post-queue-fix.md") {
    Move-Item -Path "$adminAuditDir\2026-02-28-re-audit-post-queue-fix.md" -Destination "$auditsDir\" -Force
    Write-Host "  ✓ 2026-02-28-re-audit-post-queue-fix.md"
}

Write-Host "Audit consolidation complete!" -ForegroundColor Green
Write-Host ""

# Remove empty directories
Write-Host "Step 4: Removing empty directories..." -ForegroundColor Green
if ((Test-Path $auditReportsDir) -and @(Get-ChildItem -Path $auditReportsDir).Count -eq 0) {
    Remove-Item -Path $auditReportsDir -Force
    Write-Host "  ✓ Removed docs/audit-reports/"
}
if ((Test-Path $adminAuditDir) -and @(Get-ChildItem -Path $adminAuditDir).Count -eq 0) {
    Remove-Item -Path $adminAuditDir -Force
    Write-Host "  ✓ Removed docs/admin-audit/"
}

Write-Host ""
Write-Host "=== Phase 2 Complete ===" -ForegroundColor Cyan
