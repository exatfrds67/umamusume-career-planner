# External Data Browser - Manual Testing Guide

**Feature**: External API Frontend Fix  
**Version**: 1.0.0  
**Date**: January 29, 2026  
**Test Phase**: 4.1 Manual Testing

## Prerequisites

Before beginning testing, ensure:

1. **Development server is running**:

   ```bash
   composer run dev
   # OR individually:
   # php artisan serve
   # npm run dev
   # php artisan queue:listen --tries=1
   # php artisan pail --timeout=0
   ```text

2. **Assets are built**: If not using dev mode:

   ```bash
   npm run build
   ```

3. **You're logged in**: The route requires authentication

4. **Browser console open**: Press F12 to open Developer Tools

## Test URL

Navigate to: **<http://localhost:8000/external-data/browse>**  
(Or your configured application URL + `/external-data/browse`)

---

## Task 4.1.1: Test Successful Data Loading

### Test Steps

1. **Navigate to the External Data Browser page**
   - URL: `/external-data/browse`
   - Ensure you're logged in

2. **Verify all four data types display**
   - [ ] Characters tab is visible
   - [ ] Support Cards tab is visible
   - [ ] Skills tab is visible
   - [ ] News & Updates tab is visible

3. **Check data counts in tab badges**
   - [ ] Characters tab shows count (e.g., "Characters 50")
   - [ ] Support Cards tab shows count
   - [ ] Skills tab shows count
   - [ ] News tab shows count

4. **Verify no console errors**
   - [ ] Open browser console (F12 → Console tab)
   - [ ] Check for any red error messages
   - [ ] Check for any failed network requests (Network tab)

5. **Test tab switching**
   - [ ] Click on "Support Cards" tab - switches successfully
   - [ ] Click on "Skills" tab - switches successfully
   - [ ] Click on "News & Updates" tab - switches successfully
   - [ ] Click back to "Characters" tab - switches successfully
   - [ ] No errors during tab switching

6. **Verify data displays correctly**
   - [ ] Characters: Grid of character cards with images and names
   - [ ] Support Cards: Grid of support card items
   - [ ] Skills: Grid of skill items with descriptions
   - [ ] News: List of news items

### Expected Results

✅ **PASS Criteria:**

- All 4 tabs are visible and clickable
- Data counts appear in tab badges
- No console errors (red text in console)
- Tab switching works smoothly without errors
- Data displays in appropriate format for each tab

❌ **FAIL Indicators:**

- Missing tabs
- No data counts
- Console errors (especially network errors)
- Tab switching doesn't work
- Empty tabs with no data or error messages

---

## Task 4.1.2: Test Error Scenarios

### Test A: Check for Error Banners

1. **Look for error banners on page load**
   - [ ] Check for red error banners in any tab
   - [ ] If present, note which endpoints failed
   - [ ] Verify error messages are clear and descriptive

2. **Check API availability banner**
   - [ ] If all endpoints fail, yellow "External API Unavailable" banner should appear
   - [ ] If at least one endpoint succeeds, no yellow banner

### Test B: Simulate API Unavailable (Optional)

**Note**: This test is difficult without stopping the external API. Skip if all endpoints are working.

If you want to test error handling:

1. Temporarily modify the API endpoints in the code to point to invalid URLs
2. Reload the page
3. Verify error banners appear with appropriate messages

### Test C: Partial Endpoint Failures

**Check console for any failed requests:**

- [ ] Open Network tab in browser console
- [ ] Filter by "Fetch/XHR"
- [ ] Look for any red (failed) requests to `/api/external/*`
- [ ] If found, verify corresponding error banner appears

### Expected Results - Error Handling

✅ **PASS Criteria:**

- Error banners appear for failed endpoints
- Error messages are clear and helpful
- Successful endpoints still display data
- API availability determined correctly

---

## Task 4.1.3: Test Retry Functionality

### Test Individual Endpoint Retry

**If error banners are present:**

1. **Test individual retry button**
   - [ ] Click "Retry" button on an error banner
   - [ ] Verify loading spinner appears on that section
   - [ ] Check if error clears after retry
   - [ ] Verify data loads if retry succeeds
   - [ ] Verify error persists if retry fails

2. **Verify state updates correctly**
   - [ ] Loading state shows during retry
   - [ ] Error banner disappears on success
   - [ ] Data appears on success
   - [ ] Error message updates on continued failure

### Test "Retry All" Button

**If any errors are present:**

1. **Test Retry All functionality**
   - [ ] Click "Retry All Failed" button (top right)
   - [ ] Verify button shows "Retrying..." text
   - [ ] Verify loading spinner appears
   - [ ] Check all error banners
   - [ ] Verify successful endpoints reload
   - [ ] Verify failed endpoints show updated errors or clear

2. **Verify global loading state**
   - [ ] Main loading indicator appears
   - [ ] All tabs show loading state
   - [ ] Data refreshes after retry completes

### Expected Results - Retry Functionality

✅ **PASS Criteria:**

- Individual retry buttons work for each endpoint
- Loading spinners appear during retry
- State updates correctly (error clears or persists)
- "Retry All" button retries all failed endpoints
- Successful data displays after retry

**If NO errors appear:**

- This is actually good! All endpoints are working
- You can skip this test or manually simulate errors

---

## Task 4.1.4: Test Existing Features

### Test Search Functionality

1. **Characters Tab Search**
   - [ ] Navigate to Characters tab
   - [ ] Type in search box (e.g., "Special Week")
   - [ ] Verify results filter in real-time
   - [ ] Verify filtered count updates
   - [ ] Clear search box
   - [ ] Verify all results return

2. **Support Cards Tab Search**
   - [ ] Navigate to Support Cards tab
   - [ ] Type in search box
   - [ ] Verify filtering works
   - [ ] Clear and verify reset

3. **Skills Tab Search**
   - [ ] Navigate to Skills tab
   - [ ] Type in search box
   - [ ] Verify filtering works
   - [ ] Clear and verify reset

### Test Filter Functionality

1. **Characters Tab Filters**
   - [ ] Try "Category" dropdown
   - [ ] Select a category
   - [ ] Verify results filter
   - [ ] Check filtered count
   - [ ] Select "All Categories"
   - [ ] Verify all results return

2. **Support Cards Tab Filters**
   - [ ] Click "SSR" rarity filter
   - [ ] Verify only SSR cards show
   - [ ] Click "SR" rarity filter (should add to filter)
   - [ ] Verify SSR and SR cards show
   - [ ] Click "Imported" status filter
   - [ ] Verify filtering works
   - [ ] Click "Clear Filters" button
   - [ ] Verify all filters reset

3. **Skills Tab Filters**
   - [ ] Try rarity filters (Unique, Rare, Normal)
   - [ ] Try type dropdown
   - [ ] Verify filtering works
   - [ ] Clear filters and verify reset

### Test Sort Functionality

1. **Test Sort Options**
   - [ ] Try "ID (Low to High)" - verify ascending order
   - [ ] Try "ID (High to Low)" - verify descending order
   - [ ] Try "Name (A-Z)" - verify alphabetical order
   - [ ] Try "Name (Z-A)" - verify reverse alphabetical
   - [ ] For Support Cards: Try "Rarity" sorts

2. **Verify Sort Persistence**
   - [ ] Change sort option
   - [ ] Switch tabs
   - [ ] Return to original tab
   - [ ] Verify sort option is maintained

### Test Pagination (If Applicable)

- [ ] Check if pagination controls exist
- [ ] If present, test page navigation
- [ ] Verify data loads for each page

**Note**: Pagination may not be implemented yet. If not present, note this in results.

### Test Active Filters Summary

1. **Verify Filter Summary**
   - [ ] Apply multiple filters
   - [ ] Verify "Active filters: X filter(s) applied" appears
   - [ ] Verify "X of Y items shown" count is accurate
   - [ ] Click "Clear Filters"
   - [ ] Verify summary disappears

### Expected Results - Existing Features

✅ **PASS Criteria:**

- Search filters results in real-time
- All filter options work correctly
- Sort options reorder data correctly
- Clear filters button resets all filters
- Active filters summary is accurate
- No console errors during interactions

---

## Test Results Template

### Test Execution Summary

**Date**: _____________  
**Tester**: _____________  
**Browser**: _____________  
**Environment**: _____________

### Task 4.1.1: Successful Data Loading

- [ ] PASS
- [ ] FAIL

**Notes**:

```text
[Record any issues, console errors, or observations]
```text

### Task 4.1.2: Error Scenarios

- [ ] PASS
- [ ] FAIL
- [ ] N/A (No errors to test)

**Notes**:

```text
[Record error messages, behavior]
```

### Task 4.1.3: Retry Functionality

- [ ] PASS
- [ ] FAIL
- [ ] N/A (No errors to test)

**Notes**:

```text
[Record retry behavior]
```text

### Task 4.1.4: Existing Features

- [ ] PASS
- [ ] FAIL

**Features Tested**:

- [ ] Search: ___________
- [ ] Filters: ___________
- [ ] Sort: ___________
- [ ] Pagination: ___________

**Notes**:

```text
[Record any issues with existing features]
```

### Console Errors

```text
[Copy/paste any console errors here]
```text

### Network Errors

```text
[Copy/paste any failed network requests here]
```

### Screenshots

[Attach screenshots of any issues]

---

## Common Issues and Solutions

### Issue: "External API Unavailable" banner appears

**Possible Causes:**

- External API (umapyoi.net) is down
- Network connectivity issues
- CORS issues
- API endpoints changed

**Solution:**

- Check network connectivity
- Check browser console for specific errors
- Verify API endpoints in `app/Http/Controllers/Api/ExternalDataController.php`

### Issue: No data appears in tabs

**Possible Causes:**

- API returned empty data
- JavaScript errors preventing data display
- Data structure mismatch

**Solution:**

- Check browser console for errors
- Check Network tab for API responses
- Verify response data structure

### Issue: Tab switching doesn't work

**Possible Causes:**

- JavaScript errors
- Alpine.js not loaded
- Event handlers not attached

**Solution:**

- Check console for JavaScript errors
- Verify Alpine.js is loaded
- Check if `x-data="externalDataBrowser()"` is present

### Issue: Search/filters don't work

**Possible Causes:**

- JavaScript errors
- Data not loaded
- Filter logic errors

**Solution:**

- Check console for errors
- Verify data is loaded
- Test with simple search terms first

---

## Next Steps After Testing

1. **Mark tasks as complete** in `.kiro/specs/external-api-frontend-fix/tasks.md`
2. **Document any bugs** found during testing
3. **Create bug reports** for any failures
4. **Proceed to Phase 4.2**: Browser Testing (Chrome, Firefox, Safari)
5. **Proceed to Phase 4.3**: Performance Testing

---

## Contact

If you encounter issues during testing, please provide:

1. Browser and version
2. Console errors (full text)
3. Network tab errors
4. Screenshots of the issue
5. Steps to reproduce
