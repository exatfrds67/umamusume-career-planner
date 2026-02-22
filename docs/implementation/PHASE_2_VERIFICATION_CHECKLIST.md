# Phase 2: Support Card Management - Verification Checklist

**Date**: January 25, 2026  
**Status**: Ready for Testing  
**URL**: <http://127.0.0.1:8000/support-cards>

---

## Pre-Testing Setup

- [ ] Ensure Laravel development server is running (`php artisan serve`)
- [ ] Ensure database is migrated and seeded
- [ ] Clear cache if needed (`php artisan cache:clear`)
- [ ] Verify umapyoi.net API is accessible

---

## Test 1: Load External Cards

**Steps:**

1. Navigate to <http://127.0.0.1:8000/support-cards>
2. Click "Import from API" toggle button
3. Wait for cards to load

**Expected Results:**

- [ ] Loading spinner appears
- [ ] Cards load successfully (487 total)
- [ ] All card images display (NOT placeholders)
- [ ] Images load from gametora.com
- [ ] Card count shows "487 cards available"
- [ ] No error messages appear

**Actual Results:**

- [ ] Pass / [ ] Fail
- Notes: _______________________________________________

---

## Test 2: Image Display Verification

**Steps:**

1. Scroll through the card grid
2. Observe card images loading
3. Check for placeholder avatars

**Expected Results:**

- [ ] All cards show proper game artwork
- [ ] Images load with lazy loading (as you scroll)
- [ ] No generic placeholder avatars visible
- [ ] Rarity badges display correctly (SSR/SR/R)
- [ ] Image URLs follow pattern: `https://gametora.com/images/umamusume/supports/tex_support_card_{ID}.png`

**Actual Results:**

- [ ] Pass / [ ] Fail
- Notes: _______________________________________________

---

## Test 3: Rarity Filtering

**Steps:**

1. Click "SSR" rarity filter
2. Observe filtered results
3. Click "SR" rarity filter
4. Click "R" rarity filter
5. Click "SSR" again to deselect

**Expected Results:**

- [ ] SSR filter shows only SSR cards (~264 cards)
- [ ] SR filter shows only SR cards (~89 cards)
- [ ] R filter shows only R cards (~134 cards)
- [ ] Multiple rarities can be selected simultaneously
- [ ] Deselecting filter shows all cards again
- [ ] Filter count updates correctly

**Actual Results:**

- [ ] Pass / [ ] Fail
- Notes: _______________________________________________

---

## Test 4: Import Status Filtering

**Steps:**

1. Click "Not Imported" status filter
2. Observe filtered results
3. Import one card
4. Click "Already Imported" status filter
5. Verify imported card appears

**Expected Results:**

- [ ] "Not Imported" shows cards not yet in database
- [ ] "Already Imported" shows cards in database
- [ ] Imported cards show green "Imported" badge
- [ ] Filter updates after import
- [ ] Card count reflects filtered results

**Actual Results:**

- [ ] Pass / [ ] Fail
- Notes: _______________________________________________

---

## Test 5: Card Import

**Steps:**

1. Select a card (e.g., SSR card ID 30001)
2. Click "Import Card" button
3. Wait for import to complete
4. Check for success notification

**Expected Results:**

- [ ] Import button shows loading state
- [ ] Success notification appears
- [ ] Card status changes to "Already Imported"
- [ ] Green checkmark badge appears on card
- [ ] Import button becomes disabled
- [ ] Button text changes to "Already Imported"

**Actual Results:**

- [ ] Pass / [ ] Fail
- Card ID tested: _______________
- Notes: _______________________________________________

---

## Test 6: Database Verification

**Steps:**

1. Import a support card
2. Check database for the record
3. Verify all fields saved correctly

**SQL Query:**

```sql
SELECT * FROM support_card_definitions 
WHERE external_source_id = '{CARD_ID}' 
ORDER BY created_at DESC 
LIMIT 1;
```

**Expected Results:**

- [ ] Record exists in `support_card_definitions` table
- [ ] `external_source_id` matches card ID
- [ ] `external_source` = 'umapyoi.net'
- [ ] `name` contains card title
- [ ] `rarity` is correct (SSR/SR/R)
- [ ] `image_url` contains gametora.com URL
- [ ] `character_name` extracted from gametora
- [ ] `card_type` inferred correctly
- [ ] `created_at` timestamp is recent

**Actual Results:**

- [ ] Pass / [ ] Fail
- Notes: _______________________________________________

---

## Test 7: Multiple Card Import

**Steps:**

1. Import 3 different cards (one of each rarity)
2. Verify all import successfully
3. Check import status updates

**Expected Results:**

- [ ] All 3 cards import without errors
- [ ] Each shows success notification
- [ ] All show "Already Imported" status
- [ ] Can filter to see all imported cards
- [ ] Database contains all 3 records

**Actual Results:**

- [ ] Pass / [ ] Fail
- Cards tested: _______________________________________________
- Notes: _______________________________________________

---

## Test 8: Error Handling

**Steps:**

1. Disconnect from internet (or block gametora.com)
2. Try to load external cards
3. Observe error handling

**Expected Results:**

- [ ] Error message displays clearly
- [ ] No JavaScript console errors
- [ ] UI remains functional
- [ ] Can retry loading
- [ ] Fallback to placeholder images works

**Actual Results:**

- [ ] Pass / [ ] Fail
- Notes: _______________________________________________

---

## Test 9: Refresh Functionality

**Steps:**

1. Load external cards
2. Click "Refresh" button
3. Observe reload behavior

**Expected Results:**

- [ ] Cards reload from API
- [ ] Loading spinner appears
- [ ] Card count remains 487
- [ ] Import status preserved
- [ ] No duplicate cards appear

**Actual Results:**

- [ ] Pass / [ ] Fail
- Notes: _______________________________________________

---

## Test 10: Deck Builder Integration

**Steps:**

1. Import a support card
2. Navigate to deck builder (if available)
3. Verify imported card appears

**Expected Results:**

- [ ] Imported card available in deck builder
- [ ] Card image displays correctly
- [ ] Card details are accurate
- [ ] Can add to deck

**Actual Results:**

- [ ] Pass / [ ] Fail
- Notes: _______________________________________________

---

## Performance Checks

**Metrics to Verify:**

- [ ] Initial load time < 3 seconds
- [ ] Image lazy loading works
- [ ] Filtering is instant (< 100ms)
- [ ] Import completes < 2 seconds
- [ ] No memory leaks after multiple imports
- [ ] Smooth scrolling through 487 cards

**Actual Results:**

- Load time: _______________
- Notes: _______________________________________________

---

## Browser Compatibility

Test in multiple browsers:

- [ ] Chrome/Edge (Chromium)
- [ ] Firefox
- [ ] Safari (if available)

**Issues Found:**

- Browser: _______________
- Issue: _______________________________________________

---

## Mobile Responsiveness

Test on mobile viewport:

- [ ] Cards display in grid
- [ ] Filters are accessible
- [ ] Import buttons are tappable
- [ ] Images load correctly
- [ ] No horizontal scroll

**Actual Results:**

- [ ] Pass / [ ] Fail
- Device/Viewport: _______________
- Notes: _______________________________________________

---

## Known Issues

Document any issues found during testing:

1. **Issue**: _______________________________________________
   - **Severity**: Critical / High / Medium / Low
   - **Steps to Reproduce**: _______________________________________________
   - **Expected**: _______________________________________________
   - **Actual**: _______________________________________________

2. **Issue**: _______________________________________________
   - **Severity**: Critical / High / Medium / Low
   - **Steps to Reproduce**: _______________________________________________
   - **Expected**: _______________________________________________
   - **Actual**: _______________________________________________

---

## Sign-Off

**Tester Name**: _______________________________________________  
**Date**: _______________________________________________  
**Overall Status**: [ ] Pass / [ ] Pass with Issues / [ ] Fail  

**Summary**: _______________________________________________
\_______________________________________________
\_______________________________________________

**Ready for Phase 3**: [ ] Yes / [ ] No  

**Additional Notes**: _______________________________________________
\_______________________________________________
\_______________________________________________
