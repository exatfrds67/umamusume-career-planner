# Testing Documentation

This directory contains all testing guides, reports, and best practices for the Uma Musume Career Planner application.

## Testing Guides

- **[TESTING_GUIDE.md](TESTING_GUIDE.md)** — Primary testing guide (start here)
- **[QUICK_START_TESTING.md](QUICK_START_TESTING.md)** — Quick reference for running tests
- **[BROWSER_TESTING_CHROME_EDGE.md](BROWSER_TESTING_CHROME_EDGE.md)** — Browser testing setup and execution
- **[API_TESTING_QUICK_REFERENCE.md](API_TESTING_QUICK_REFERENCE.md)** — API endpoint testing reference

## Test Reports

- **[ERROR_SCENARIO_TEST_REPORT.md](ERROR_SCENARIO_TEST_REPORT.md)** — Error handling test results
- **[FILTER_TESTING_REPORT.md](FILTER_TESTING_REPORT.md)** — Filter functionality test results
- **[SORT_FUNCTIONALITY_TEST_REPORT.md](SORT_FUNCTIONALITY_TEST_REPORT.md)** — Sort functionality test results

## Quick Links

- Unit testing with Pest
- Feature testing with Pest Browser tests
- API testing and validation
- Error scenario handling
- Filter and sort functionality testing
- Chrome/Edge debugging and testing

## Test Execution

```bash
# Run all tests
php artisan test --compact

# Run specific test file
php artisan test tests/Feature/YourTest.php --compact

# Run browser tests
npm run playwright:test
```

For detailed information, start with **TESTING_GUIDE.md**.
