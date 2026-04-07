# External Data Browser - Testing Guide

**Date**: January 29, 2026
**Feature**: external-api-frontend-fix
**Version**: 1.0.0

## Quick Start

### Manual Testing

1. **Start the development server**:

   ```bash
   php artisan serve
   ```

2. **Open the test page**:

   ```text
   http://127.0.0.1:8000/test-external-data-errors.html
   ```

3. **Run test scenarios**:
   - Click "Test: All Endpoints Fail" to test complete API failure
   - Click "Test: Partial Failure" to test partial endpoint failures
   - Click "Test: Network Error" to test network errors
   - Click "Test: Timeout" to test timeout scenarios
   - Click "Test: All Success" to test normal operation
   - Click "Reset" to clear test state

### Production Testing

1. **Navigate to the actual page**:

   ```text
   http://127.0.0.1:8000/external-data/browse
   ```

2. **Verify normal operation**:
   - All four tabs should load data
   - No console errors
   - Data source badges visible
   - Search and filter work

3. **Test error handling** (requires backend manipulation):
   - Temporarily disable external API
   - Verify error messages display
   - Test retry buttons
   - Verify partial data still shows

## Test Scenarios

### Scenario 1: Complete API Failure

**What to test**:

- All endpoints return errors
- Error messages displayed
- Retry All button visible
- No data shown

**Expected behavior**:

- ✅ Clear error messages for each endpoint
- ✅ "API Unavailable" status
- ✅ Retry All button functional
- ✅ No crashes or console errors

### Scenario 2: Partial Endpoint Failures

**What to test**:

- Some endpoints succeed, others fail
- Successful data displays
- Failed endpoints show errors
- Individual retry buttons work

**Expected behavior**:

- ✅ Successful data visible
- ✅ Error messages for failed endpoints
- ✅ "API Available" status (at least one success)
- ✅ Individual retry buttons functional

### Scenario 3: Network Errors

**What to test**:

- Network connectivity issues
- Timeout scenarios
- Connection refused errors

**Expected behavior**:

- ✅ Network errors caught gracefully
- ✅ User-friendly error messages
- ✅ Retry functionality available
- ✅ Component remains stable

### Scenario 4: Retry Functionality

**What to test**:

- Individual endpoint retry
- Retry All functionality
- Loading states during retry
- State updates after retry

**Expected behavior**:

- ✅ Section-specific loading indicators
- ✅ Errors cleared on success
- ✅ Data updated correctly
- ✅ API availability recalculated

## Verification Checklist

### Error Handling ✅

- [ ] All endpoints fail gracefully
- [ ] Partial failures handled correctly
- [ ] Network errors caught
- [ ] Error messages are clear
- [ ] No unhandled exceptions

### Loading States ✅

- [ ] Global loading indicator works
- [ ] Section-specific loading works
- [ ] Loading states cleared properly
- [ ] No stuck loading states

### Retry Functionality ✅

- [ ] Individual retry works
- [ ] Retry All works
- [ ] Loading indicators during retry
- [ ] State updates correctly

### Data Preservation ✅

- [ ] Existing data preserved on failure
- [ ] Partial data maintained
- [ ] No data loss during errors
- [ ] Data only updates on success

### UI/UX ✅

- [ ] Error banners visible
- [ ] Retry buttons accessible
- [ ] Loading spinners visible
- [ ] Data source badges show
- [ ] Tab switching works
- [ ] Search/filter work

## Browser Testing

### Required Browsers

- ✅ Chrome/Edge (latest)
- ⏳ Firefox (latest)
- ⏳ Safari (latest)

### Test on Each Browser

1. Navigate to test page
2. Run all test scenarios
3. Verify no browser-specific issues
4. Check console for errors
5. Test retry functionality

## Performance Testing

### Metrics to Measure

- Initial page load: < 2 seconds
- API response time: < 1 second (cached)
- API response time: < 3 seconds (fresh)
- Retry response time: < 1 second
- UI responsiveness: < 100ms

### How to Measure

1. Open browser DevTools
2. Go to Network tab
3. Reload page
4. Check timing for each request
5. Verify meets targets

## Automated Testing

### Browser Tests (Pest 4)

**Location**: `tests/Browser/ExternalDataBrowserErrorTest.php`

**Run tests**:

```bash
php artisan test --filter="External Data Browser - Error Scenarios"
```text

**Note**: Browser tests require Pest 4 browser testing setup.

### Property-Based Tests

**Location**: `tests/Property/ExternalDataBrowserTest.php` (to be created)

**Run tests**:

```bash
php artisan test --filter="Property"
```text

## Troubleshooting

### Issue: Test page not loading

**Solution**:

1. Verify server is running: `php artisan serve`
2. Check URL: `http://127.0.0.1:8000/test-external-data-errors.html`
3. Check browser console for errors

### Issue: Tests not working

**Solution**:

1. Clear browser cache
2. Hard reload (Ctrl+Shift+R)
3. Check browser console for JavaScript errors
4. Verify Alpine.js is loaded

### Issue: API endpoints not responding

**Solution**:

1. Check backend server is running
2. Verify routes are registered: `php artisan route:list | grep external`
3. Check API controller exists
4. Test endpoints directly: `curl http://127.0.0.1:8000/api/external/characters`

### Issue: Retry not working

**Solution**:

1. Check browser console for errors
2. Verify CSRF token is present
3. Check network tab for failed requests
4. Verify component state in Alpine DevTools

## Test Reports

### Generated Reports

- **Error Scenario Report**: `docs/external-api-integration/ERROR_SCENARIO_TEST_REPORT.md`
- **Test Results**: Check test page "Test Results" section

### Manual Test Log Template

```markdown
## Test Session: [Date]

**Tester**: [Name]
**Browser**: [Browser Name/Version]
**Environment**: [Development/Staging/Production]

### Test Results

| Scenario | Status | Notes |
|----------|--------|-------|
| Complete API Failure | ✅/❌ | |
| Partial Failures | ✅/❌ | |
| Network Errors | ✅/❌ | |
| Retry Individual | ✅/❌ | |
| Retry All | ✅/❌ | |

### Issues Found
- [Issue 1]
- [Issue 2]

### Recommendations
- [Recommendation 1]
- [Recommendation 2]
```

## Next Steps

1. ✅ Complete error scenario testing (Task 4.1.2)
2. ⏳ Test retry functionality (Task 4.1.3)
3. ⏳ Test existing features (Task 4.1.4)
4. ⏳ Browser compatibility testing (Task 4.2)
5. ⏳ Performance testing (Task 4.3)
6. ⏳ Write property-based tests (Task 4.4)

## Resources

- **Test Page**: `/test-external-data-errors.html`
- **Production Page**: `/external-data/browse`
- **Component**: `resources/js/pages/external-data/browse.js`
- **Template**: `resources/views/external-data/browse.blade.php`
- **API Controller**: `app/Http/Controllers/Api/ExternalDataController.php`
- **Requirements**: `.kiro/specs/external-api-frontend-fix/requirements.md`
- **Design**: `.kiro/specs/external-api-frontend-fix/design.md`

## Contact

For questions or issues with testing:

- Check documentation in `docs/external-api-integration/`
- Review test reports
- Check browser console for errors
- Verify backend API is operational
