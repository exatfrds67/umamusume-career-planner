#!/usr/bin/env pwsh
# Phase 3 Reorganization Script
# Moves scattered documentation to appropriate subdirectories

$projectRoot = "c:\XAMPP\htdocs\umamusume-career-planner"
$externalApiDir = "$projectRoot\docs\external-api-integration"
$testingDir = "$projectRoot\docs\testing"
$featureDocDir = "$projectRoot\docs\feature-documentation"
$implementationDir = "$projectRoot\docs\implementation"
$frontendDevDir = "$projectRoot\docs\frontend-development"
$designDir = "$projectRoot\docs\design"
$researchDir = "$projectRoot\docs\research"

Write-Host "=== Phase 3: Reorganization ===" -ForegroundColor Cyan
Write-Host ""

# Create new directories if they don't exist
Write-Host "Step 1: Creating new directories..." -ForegroundColor Green
$newDirs = @(
    "$featureDocDir\support-cards",
    "$featureDocDir\skills",
    "$researchDir\game-mechanics"
)
foreach ($dir in $newDirs) {
    if (-not (Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir -Force | Out-Null
        Write-Host "  ✓ Created $(($dir -split '\\')[-1])"
    }
}
Write-Host ""

# Move testing documentation
Write-Host "Step 2: Moving testing documentation..." -ForegroundColor Green
$testingFiles = @(
    "BROWSER_TESTING_CHROME_EDGE.md",
    "API_TESTING_QUICK_REFERENCE.md",
    "TESTING_GUIDE.md",
    "QUICK_START_TESTING.md",
    "ERROR_SCENARIO_TEST_REPORT.md",
    "FILTER_TESTING_REPORT.md",
    "SORT_FUNCTIONALITY_TEST_REPORT.md"
)
foreach ($file in $testingFiles) {
    $source = "$externalApiDir\$file"
    if (Test-Path $source) {
        Move-Item -Path $source -Destination "$testingDir\" -Force
        Write-Host "  ✓ $file"
    }
}
Write-Host ""

# Move support cards documentation
Write-Host "Step 3: Moving support cards documentation..." -ForegroundColor Green
$supportCardFiles = @(
    "SUPPORT_CARD_DATA_LIMITATION.md",
    "SUPPORT_CARD_IMPLEMENTATION_ROADMAP.md",
    "SUPPORT_CARD_RARITY_SOLUTION.md"
)
foreach ($file in $supportCardFiles) {
    $source = "$externalApiDir\$file"
    if (Test-Path $source) {
        Move-Item -Path $source -Destination "$featureDocDir\support-cards\" -Force
        Write-Host "  ✓ $file"
    }
}
Write-Host ""

# Move skills documentation
Write-Host "Step 4: Moving skills documentation..." -ForegroundColor Green
$skillsFiles = @(
    "SKILL_SYSTEM_DOCUMENTATION.md",
    "SKILLS_DATA_IMPLEMENTATION.md"
)
foreach ($file in $skillsFiles) {
    $source = "$featureDocDir\$file"
    if (Test-Path $source) {
        Move-Item -Path $source -Destination "$featureDocDir\skills\" -Force
        Write-Host "  ✓ $file"
    }
}
Write-Host ""

# Move frontend development guides
Write-Host "Step 5: Moving frontend development guides..." -ForegroundColor Green
$frontendFiles = @(
    "blade-asset-refactoring-plan.md",
    "EXTERNAL_API_FRONTEND_INTEGRATION.md",
    "COMPONENTS_INTEGRATION_SUMMARY.md",
    "game-alignment-analysis.md",
    "GAME_MECHANICS_ALIGNMENT_ANALYSIS.md",
    "GAME_MECHANICS_AUDIT_REPORT.md",
    "GAME_MECHANICS_FIX_SUMMARY.md",
    "game-mechanics-corrections-summary.md"
)
foreach ($file in $frontendFiles) {
    $source = "$implementationDir\$file"
    if (Test-Path $source) {
        Move-Item -Path $source -Destination "$frontendDevDir\" -Force
        Write-Host "  ✓ $file"
    }
}
Write-Host ""

# Move game mechanics documentation
Write-Host "Step 6: Moving game mechanics documentation..." -ForegroundColor Green
if (-not (Test-Path "$researchDir\game-mechanics")) {
    New-Item -ItemType Directory -Path "$researchDir\game-mechanics" -Force | Out-Null
}

$gameMechanicsFiles = @(
    "component-inventory.md",
    "data-flow-mapping.md",
    "game-alignment-analysis.md",
    "game-alignment-plan.md",
    "game-ui-alignment-strategy.md",
    "GAME_ALIGNMENT_DOCUMENTATION_INDEX.md",
    "GAME_ALIGNMENT_PLANNING_SUMMARY.md",
    "GAME_ALIGNMENT_STRATEGIC_PLAN.md",
    "COMPLETION_REPORT.md",
    "IMPLEMENTATION_PLAN.md",
    "IMPLEMENTATION_PLAN_UPDATES.md",
    "prototype-plan.md"
)
foreach ($file in $gameMechanicsFiles) {
    $source = "$designDir\$file"
    if (Test-Path $source) {
        Move-Item -Path $source -Destination "$researchDir\game-mechanics\" -Force
        Write-Host "  ✓ $file"
    }
}
Write-Host ""

Write-Host "=== Phase 3 Complete ===" -ForegroundColor Cyan
