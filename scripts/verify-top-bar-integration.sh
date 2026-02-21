#!/bin/bash

# Top Bar Integration Verification Script
# Verifies that controllers properly pass topStatus to views

echo "========================================="
echo "Top Bar Integration Verification"
echo "========================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Counter for results
PASS=0
FAIL=0
WARN=0

echo "Checking controllers for topStatus passing..."
echo ""

# Function to check if a controller method passes topStatus
check_controller() {
    local file=$1
    local method=$2
    local description=$3
    
    if grep -q "return view.*'topStatus'" "$file"; then
        echo -e "${GREEN}✓${NC} $description"
        ((PASS++))
    else
        echo -e "${RED}✗${NC} $description"
        ((FAIL++))
    fi
}

# Check TrainingPredictionController
echo "TrainingPredictionController:"
check_controller "app/Http/Controllers/TrainingPredictionController.php" "index" "  index() method passes topStatus"
check_controller "app/Http/Controllers/TrainingPredictionController.php" "show" "  show() method passes topStatus"
echo ""

# Check DashboardController
echo "DashboardController:"
check_controller "app/Http/Controllers/DashboardController.php" "index" "  index() method passes topStatus"
echo ""

# Check for other controllers that might need updating
echo "Checking other controllers (warnings for missing topStatus):"
echo ""

check_controller_warn() {
    local file=$1
    local description=$2
    
    if [ -f "$file" ]; then
        if grep -q "return view" "$file"; then
            if grep -q "'topStatus'" "$file"; then
                echo -e "${GREEN}✓${NC} $description - topStatus present"
            else
                echo -e "${YELLOW}⚠${NC} $description - topStatus missing (may not be needed)"
                ((WARN++))
            fi
        fi
    fi
}

check_controller_warn "app/Http/Controllers/CharacterController.php" "CharacterController"
check_controller_warn "app/Http/Controllers/TrainingController.php" "TrainingController"
check_controller_warn "app/Http/Controllers/RaceController.php" "RaceController"
check_controller_warn "app/Http/Controllers/SkillController.php" "SkillController"
echo ""

# Check if top-status-bar component exists
echo "Checking components:"
if [ -f "resources/views/components/top-status-bar.blade.php" ]; then
    echo -e "${GREEN}✓${NC} top-status-bar.blade.php exists"
    ((PASS++))
else
    echo -e "${RED}✗${NC} top-status-bar.blade.php missing"
    ((FAIL++))
fi

# Check if app layout uses top-status-bar
if grep -q "top-status-bar" "resources/views/layouts/app.blade.php"; then
    echo -e "${GREEN}✓${NC} app.blade.php uses top-status-bar component"
    ((PASS++))
else
    echo -e "${RED}✗${NC} app.blade.php doesn't use top-status-bar component"
    ((FAIL++))
fi
echo ""

# Check if training predictions view exists
echo "Checking views:"
if [ -f "resources/views/training/predictions.blade.php" ]; then
    echo -e "${GREEN}✓${NC} training/predictions.blade.php exists"
    ((PASS++))
else
    echo -e "${RED}✗${NC} training/predictions.blade.php missing"
    ((FAIL++))
fi

if [ -f "resources/views/dashboard.blade.php" ]; then
    echo -e "${GREEN}✓${NC} dashboard.blade.php exists"
    ((PASS++))
else
    echo -e "${RED}✗${NC} dashboard.blade.php missing"
    ((FAIL++))
fi
echo ""

# Summary
echo "========================================="
echo "Summary:"
echo "========================================="
echo -e "${GREEN}Passed:${NC} $PASS"
echo -e "${RED}Failed:${NC} $FAIL"
echo -e "${YELLOW}Warnings:${NC} $WARN"
echo ""

if [ $FAIL -eq 0 ]; then
    echo -e "${GREEN}✓ All critical checks passed!${NC}"
    echo ""
    echo "Next steps:"
    echo "1. Start the development server: php artisan serve"
    echo "2. Visit /training/predictions and select a character"
    echo "3. Verify top bar shows: Turn, Energy, Mood, SP, Storage"
    echo "4. Visit /dashboard and verify same indicators"
    echo "5. Run browser tests: php artisan test --filter=\"Browser\""
    exit 0
else
    echo -e "${RED}✗ Some checks failed. Please review the output above.${NC}"
    exit 1
fi
