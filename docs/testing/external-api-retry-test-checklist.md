# External API Retry Functionality - Manual Test Checklist

**Task**: 4.1.3 Test retry functionality  
**Date**: January 29, 2026  
**Tester**: _________________  
**Status**: ☐ Not Started | ☐ In Progress | ☐ Completed

## Test Environment Setup

- [ ] Development server running (`composer run dev` or `php artisan serve`)
- [ ] Frontend assets built (`npm run build` or `npm run dev`)
- [ ] User account created and logged in
- [ ] Browser DevTools console open for error monitoring
- [ ] Network tab open in DevTools for API monitoring

## Test 4.1.3.1: Individual Endpoint Retry

### Characters Endpoint Retry

**Steps**:

1. Navigate to `/external-data/browse`
2. Wait for initial page load
3. If characters endpoint fails (error banner visible):
   - [ ] Error banner displays with message
   - [ ] "Retry" button is visible in error banner
   - [ ] Click the "Retry" button
   - [ ] Loading spinner appears during retry
   - [ ] Button text changes to "Retrying..."
   - [ ] Button is disabled during retry
   - [ ] After retry completes:
     - [ ] Error clears if successful
     - [ ] Data displays if successful
     - [ ] Error updates if still failing

**Expected Results**:

- ✅ Retry button triggers `retryEndpoint('characters')` method
- ✅ Section-specific loading state (`loadingCharacters`) activates
- ✅ Other sections remain unaffected
- ✅ Data updates on success
- ✅ Error state updates appropriately

**Actual Results:**

---

**Status**: ☐ Pass | ☐ Fail | ☐ N/A (no error to test)

---

### Support Cards Endpoint Retry

**Steps**:

1. Click on "Support Cards" tab
2. If support cards endpoint fails:
   - [ ] Error banner displays
   - [ ] Click "Retry" button
   - [ ] Observe loading state
   - [ ] Verify result

**Expected Results**:

- ✅ Same behavior as characters retry
- ✅ `loadingSupportCards` state activates
- ✅ Other tabs unaffected

**Actual Results:**

---

**Status**: ☐ Pass | ☐ Fail | ☐ N/A

---

### Skills Endpoint Retry

**Steps**:

1. Click on "Skills" tab
2. If skills endpoint fails:
   - [ ] Error banner displays
   - [ ] Click "Retry" button
   - [ ] Observe loading state
   - [ ] Verify result

**Expected Results**:

- ✅ Same behavior as previous retries
- ✅ `loadingSkills` state activates

**Actual Results:**

---

**Status**: ☐ Pass | ☐ Fail | ☐ N/A

---

### News Endpoint Retry

**Steps**:

1. Click on "News & Updates" tab
2. If news endpoint fails:
   - [ ] Error banner displays
   - [ ] Click "Retry" button
   - [ ] Observe loading state
   - [ ] Verify result

**Expected Results**:

- ✅ Same behavior as previous retries
- ✅ `loadingNews` state activates

**Actual Results:**

---

**Status**: ☐ Pass | ☐ Fail | ☐ N/A

---

## Test 4.1.3.2: "Retry All" Button

### Retry All Failed Endpoints

**Steps**:

1. Navigate to `/external-data/browse`
2. Wait for initial load
3. If any endpoints fail:
   - [ ] "Retry All Failed" button is visible in header
   - [ ] Button shows warning styling (yellow/orange)
   - [ ] Click "Retry All Failed" button
   - [ ] Global loading state activates
   - [ ] All failed endpoints retry simultaneously
   - [ ] Button text changes to "Retrying..."
   - [ ] Button is disabled during retry
   - [ ] After completion:
     - [ ] Successful endpoints show data
     - [ ] Failed endpoints show updated errors
     - [ ] "Retry All Failed" button hides if all succeed

**Expected Results**:

- ✅ Triggers `retryAll()` method
- ✅ Calls `loadData()` internally
- ✅ All endpoints retry in parallel
- ✅ Global `loading` state activates
- ✅ Individual errors clear before retry
- ✅ API availability updates based on results

**Actual Results:**

---

**Status**: ☐ Pass | ☐ Fail | ☐ N/A

---

### Retry All When All Endpoints Succeed

**Steps**:

1. Navigate to `/external-data/browse`
2. If all endpoints succeed initially:
   - [ ] "Retry All Failed" button is NOT visible
   - [ ] Only "Refresh Data" button is visible

**Expected Results**:

- ✅ "Retry All Failed" button only shows when errors exist
- ✅ Conditional rendering: `x-show="errors.characters || errors.supportCards || errors.skills || errors.news"`

**Actual Results:**

---

**Status**: ☐ Pass | ☐ Fail

---

## Test 4.1.3.3: State Updates Correctly

### Error State Management

**Steps**:

1. Trigger an endpoint failure (or wait for natural failure)
2. Observe error state:
   - [ ] Error message displays in banner
   - [ ] Error stored in `errors.{endpoint}` property
   - [ ] Console logs error message
   - [ ] Existing data preserved (partial data maintenance)

3. Retry the endpoint:
   - [ ] Error clears before retry attempt
   - [ ] If retry succeeds:
     - [ ] Error property set to `null`
     - [ ] Error banner hides
     - [ ] Data displays
   - [ ] If retry fails:
     - [ ] Error property updates with new message
     - [ ] Error banner remains visible

**Expected Results**:

- ✅ Error state accurately reflects endpoint status
- ✅ Errors clear appropriately on retry
- ✅ Partial data maintained during failures

**Actual Results:**

---

**Status**: ☐ Pass | ☐ Fail

---

### Loading State Management

**Steps**:

1. Trigger a retry operation
2. Observe loading states:
   - [ ] Section-specific loading state activates (`loadingCharacters`, etc.)
   - [ ] Loading spinner displays
   - [ ] Button shows "Retrying..." text
   - [ ] Button is disabled
   - [ ] After completion:
     - [ ] Loading state deactivates
     - [ ] Spinner hides
     - [ ] Button re-enables
     - [ ] Button text returns to "Retry"

**Expected Results**:

- ✅ Loading states prevent duplicate requests
- ✅ UI feedback is clear and immediate
- ✅ States clean up properly after completion

**Actual Results:**

---

**Status**: ☐ Pass | ☐ Fail

---

### API Availability Updates

**Steps**:

1. Start with all endpoints failing (if possible)
2. Observe API availability:
   - [ ] `apiAvailable` is `false`
   - [ ] Yellow warning banner displays: "External API Unavailable"

3. Retry one endpoint successfully:
   - [ ] `apiAvailable` updates to `true`
   - [ ] Warning banner hides

4. Verify logic:
   - [ ] API available if ANY endpoint succeeds
   - [ ] API unavailable only if ALL endpoints fail

**Expected Results**:

- ✅ API availability determined correctly
- ✅ Logic: `apiAvailable = charactersRes.success || supportCardsRes.success || skillsRes.success || newsRes.success`

**Actual Results:**

---

**Status**: ☐ Pass | ☐ Fail

---

### Data Source Badges

**Steps**:

1. After successful retry, observe data source badges:
   - [ ] Badge displays next to tab name
   - [ ] Badge shows correct source:
     - "Live" for fresh API data
     - "Cached" for cached data
     - "Offline" for database fallback
   - [ ] Badge styling matches source type

**Expected Results**:

- ✅ Data source accurately reflected
- ✅ `determineDataSource()` method works correctly
- ✅ Badges update after retry

**Actual Results:**

---

**Status**: ☐ Pass | ☐ Fail

---

## Browser Compatibility Testing

### Chrome/Edge

- [ ] All retry functionality works
- [ ] No console errors
- [ ] Loading animations smooth
- [ ] Button states correct

**Status**: ☐ Pass | ☐ Fail

---

### Firefox

- [ ] All retry functionality works
- [ ] No console errors
- [ ] Loading animations smooth
- [ ] Button states correct

**Status**: ☐ Pass | ☐ Fail

---

### Safari (if available)

- [ ] All retry functionality works
- [ ] No console errors
- [ ] Loading animations smooth
- [ ] Button states correct

**Status**: ☐ Pass | ☐ Fail

---

## Accessibility Testing

### Keyboard Navigation

**Steps**:

1. Navigate to page using Tab key
2. Focus on retry button
3. Press Enter or Space to activate
4. Verify:
   - [ ] Retry triggers correctly
   - [ ] Focus management appropriate
   - [ ] Loading state announced (if using screen reader)

**Status**: ☐ Pass | ☐ Fail

---

### Screen Reader Testing (Optional)

**Steps**:

1. Enable screen reader (NVDA, JAWS, VoiceOver)
2. Navigate to error banner
3. Verify:
   - [ ] Error message announced
   - [ ] Retry button labeled correctly
   - [ ] Loading state changes announced

**Status**: ☐ Pass | ☐ Fail | ☐ N/A

---

## Performance Testing

### Response Time

**Steps**:

1. Open Network tab in DevTools
2. Trigger retry operation
3. Measure:
   - [ ] Time to initiate request: _______ ms
   - [ ] Time to receive response: _______ ms
   - [ ] Time to update UI: _______ ms
   - [ ] Total time: _______ ms

**Expected**: < 3 seconds for retry operation

**Status**: ☐ Pass | ☐ Fail

---

### Multiple Rapid Retries

**Steps**:

1. Click retry button multiple times rapidly
2. Verify:
   - [ ] Button disables after first click
   - [ ] Only one request sent
   - [ ] No duplicate requests in Network tab
   - [ ] No race conditions or errors

**Status**: ☐ Pass | ☐ Fail

---

## Edge Cases

### Retry During Initial Load

**Steps**:

1. Refresh page
2. Immediately click retry button (if visible)
3. Verify:
   - [ ] No conflicts with initial load
   - [ ] State management correct
   - [ ] No duplicate requests

**Status**: ☐ Pass | ☐ Fail

---

### Network Timeout During Retry

**Steps**:

1. Throttle network to "Slow 3G" in DevTools
2. Trigger retry
3. Verify:
   - [ ] Timeout handled gracefully
   - [ ] Error message appropriate
   - [ ] Can retry again after timeout

**Status**: ☐ Pass | ☐ Fail

---

### Switch Tabs During Retry

**Steps**:

1. Start retry on one tab
2. Immediately switch to another tab
3. Verify:
   - [ ] Retry completes in background
   - [ ] State updates correctly
   - [ ] No UI glitches

**Status**: ☐ Pass | ☐ Fail

---

## Console Error Check

### JavaScript Errors

**Steps**:

1. Open browser console
2. Perform all retry operations
3. Check for:
   - [ ] No JavaScript errors
   - [ ] No unhandled promise rejections
   - [ ] No warning messages (except expected ones)

**Errors Found:**

---

**Status**: ☐ Pass | ☐ Fail

---

## Summary

### Overall Test Results

- **Total Tests**: 20
- **Passed**: _______
- **Failed**: _______
- **N/A**: _______
- **Pass Rate**: _______%

### Critical Issues Found

1. ---
2. ---
3. ---

### Recommendations

---
---
---

### Sign-off

**Tester Name**: _________________  
**Date**: _________________  
**Signature**: _________________

---

## Notes

- This checklist covers Requirements 2.3 from the design document
- All retry functionality should work consistently across all four endpoints
- Partial failure resilience is a key feature - successful endpoints should work even when others fail
- Loading states should prevent duplicate requests and provide clear user feedback
