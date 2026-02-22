# Testing Lazy Loading - Quick Guide

**Date**: January 18, 2026

---

## Quick Visual Test

### 1. Open DevTools Network Tab

1. Press `F12` to open DevTools
2. Click the **Network** tab
3. Click **Img** filter to show only images
4. Check **Disable cache** checkbox

### 2. Visit Support Cards Page

Navigate to: **<http://127.0.0.1:8000/support-cards>**

### 3. Observe the Magic ✨

**What You Should See**:

- Only ~6 images load initially (the visible ones)
- As you scroll down, more images appear in the Network tab
- Images load just before they enter the viewport
- Total initial payload is much smaller

**Before Lazy Loading**:

```text
15 images × ~200KB = ~3MB loaded immediately
```

**After Lazy Loading**:

```text
~6 images × ~200KB = ~1.2MB loaded initially
Remaining 9 images load as you scroll
```

---

## Performance Comparison

### Test on Slow Connection

1. **Enable Network Throttling**
   - DevTools → Network → Throttling dropdown
   - Select **Fast 3G** or **Slow 3G**

2. **Reload Page** (Ctrl+Shift+R)

3. **Notice**:
   - Page becomes interactive much faster
   - You can start browsing while images load
   - Smooth scrolling experience

### Test on Mobile

1. **Enable Device Emulation**
   - DevTools → Toggle Device Toolbar (Ctrl+Shift+M)
   - Select a mobile device (e.g., iPhone 12)

2. **Throttle Network**
   - Set to **Fast 3G**

3. **Scroll Through Cards**
   - Images load smoothly as you scroll
   - No janky loading or layout shifts

---

## What to Look For

### ✅ Good Signs

- Images load progressively as you scroll
- Page is interactive immediately
- No layout jumping when images load
- Smooth scrolling experience
- Lower initial page load time

### ❌ Bad Signs (If These Happen)

- All images load at once (lazy loading not working)
- Page jumps when images load (need width/height attributes)
- Images don't load when scrolling (check console for errors)
- Slow scrolling (too many images loading at once)

---

## Browser Console Check

### Verify Lazy Loading Attribute

1. Open DevTools Console
2. Run this command:

```javascript
document.querySelectorAll('img[loading="lazy"]').length
```text

**Expected Result**: Should show number of lazy-loaded images (e.g., 15+ on support cards page)

### Check for Errors

Look for any red errors in console. Common issues:

- 404 errors (image not found)
- CORS errors (image blocked)
- Loading errors

---

## Lighthouse Performance Audit

### Run Audit

1. Open DevTools
2. Click **Lighthouse** tab
3. Select:
   - ✅ Performance
   - ✅ Best Practices
   - Device: Mobile
4. Click **Analyze page load**

### Check Metrics

**Key Metrics to Improve**:

- **LCP (Largest Contentful Paint)**: Should be < 2.5s
- **TBT (Total Blocking Time)**: Should be < 200ms
- **Performance Score**: Should be 90+

**Lazy Loading Impact**:

- Reduces initial payload
- Improves LCP for above-the-fold content
- Better overall performance score

---

## Real-World Testing

### Test Scenario 1: Support Cards Index

1. Visit: <http://127.0.0.1:8000/support-cards>
2. Open Network tab
3. Reload page
4. **Count initial image loads**: Should be ~6 images
5. **Scroll down slowly**: Watch new images load
6. **Total images loaded**: Should match number of cards visible

### Test Scenario 2: Character Creation Gallery

1. Visit: <http://127.0.0.1:8000/characters/create>
2. Scroll to avatar gallery section
3. Open Network tab
4. **Observe**: Gallery images load as section comes into view
5. **Not loaded until**: You scroll to that section

---

## Advanced Testing

### Measure Load Time Difference

**Before Lazy Loading** (simulate):

```javascript
// Disable lazy loading temporarily
document.querySelectorAll('img[loading="lazy"]').forEach(img => {
    img.loading = 'eager';
});
// Reload and measure
```

**After Lazy Loading**:

```javascript
// Normal page load with lazy loading
// Compare Network tab waterfall
```text

### Check Intersection Observer

```javascript
// Check if browser supports native lazy loading
if ('loading' in HTMLImageElement.prototype) {
    console.log('✅ Native lazy loading supported');
} else {
    console.log('❌ Native lazy loading NOT supported');
}
```

---

## Expected Results

### Support Cards Page (15 cards)

| Metric              | Before | After  | Improvement   |
| ------------------- | ------ | ------ | ------------- |
| Initial Images      | 15     | ~6     | 60% reduction |
| Initial Payload     | ~3MB   | ~1.2MB | 60% reduction |
| Time to Interactive | ~2.5s  | ~1s    | 60% faster    |
| LCP                 | ~2.8s  | ~1.8s  | 36% faster    |

### Character Creation Gallery (~20 images)

| Metric          | Before | After        | Improvement    |
| --------------- | ------ | ------------ | -------------- |
| Initial Images  | 20     | 0            | 100% reduction |
| Initial Payload | ~4MB   | ~0MB         | 100% reduction |
| Load on Scroll  | N/A    | ~6 at a time | Progressive    |

---

## Troubleshooting

### Images Not Loading

**Check**:

1. Browser console for errors
2. Image paths are correct
3. Images exist in `public/images/` directory

**Fix**:

```bash
# Verify images exist
ls -la public/images/support_cards/
```text

## All Images Load at Once

**Check**:

1. `loading="lazy"` attribute is present
2. Browser supports lazy loading (Chrome 77+, Firefox 75+, Safari 15.4+)

**Verify**:

```javascript
// Check in console
document.querySelector('img').loading
// Should return "lazy" or "eager"
```

### Layout Shifts When Loading

**Fix**: Add explicit dimensions

```blade
<img src="{{ $url }}" 
    width="400" height="600"
    loading="lazy"
    alt="Card">
```text

---

## Quick Checklist

- [ ] Open DevTools Network tab
- [ ] Filter to show only images
- [ ] Visit support cards page
- [ ] Verify only ~6 images load initially
- [ ] Scroll down and watch images load progressively
- [ ] Check console for errors
- [ ] Test on throttled connection (Fast 3G)
- [ ] Test on mobile device emulation
- [ ] Run Lighthouse audit
- [ ] Verify performance score improved

---

## Success Criteria

✅ **Initial page load is faster**  
✅ **Only visible images load first**  
✅ **Images load smoothly when scrolling**  
✅ **No layout shifts or jumps**  
✅ **Works on mobile and slow connections**  
✅ **Performance score improved in Lighthouse**  

---

**Happy Testing!** 🚀

Your images are now loading efficiently with native browser lazy loading.

