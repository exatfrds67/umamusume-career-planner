# Quick Start: Testing External APIs in Browser

**Created:** 2026-01-25  
**Purpose:** Quick guide to test umapyoi.net and UmamusumeDB.com APIs

---

## 🚀 Quick Start (3 Steps)

### Step 1: Start Your Server

```bash
php artisan serve
```

### Step 2: Open the Test Page

Navigate to: **<http://localhost:8000/test-api>**

### Step 3: Open Chrome DevTools

Press **F12** or **Right-click → Inspect**

---

## 📊 What You'll See

### Test Page Layout

```
┌─────────────────────────────────────────┐
│  External API Testing                   │
├─────────────────────────────────────────┤
│  Umapyoi.net API                        │
│  ┌─────────────┐ ┌─────────────┐       │
│  │ Characters  │ │ Character   │       │
│  └─────────────┘ └─────────────┘       │
│  ┌─────────────┐ ┌─────────────┐       │
│  │ Support     │ │ Skills      │       │
│  └─────────────┘ └─────────────┘       │
│  ┌─────────────┐ ┌─────────────┐       │
│  │ News        │ │ Health      │       │
│  └─────────────┘ └─────────────┘       │
│                                         │
│  [Results displayed here]               │
├─────────────────────────────────────────┤
│  UmamusumeDB.com                        │
│  ┌─────────────┐ ┌─────────────┐       │
│  │ Character   │ │ Card        │       │
│  └─────────────┘ └─────────────┘       │
│  ┌─────────────┐ ┌─────────────┐       │
│  │ robots.txt  │ │ Sitemap     │       │
│  └─────────────┘ └─────────────┘       │
│                                         │
│  [Results displayed here]               │
└─────────────────────────────────────────┘
```

### Chrome DevTools Layout

```
┌─────────────────────────────────────────┐
│  Elements  Console  Network  ...        │
├─────────────────────────────────────────┤
│  Network Tab:                           │
│  ┌───────────────────────────────────┐  │
│  │ Name          Status    Type      │  │
│  │ character/... 200       xhr       │  │
│  │ support       200       xhr       │  │
│  └───────────────────────────────────┘  │
│                                         │
│  Console Tab:                           │
│  ┌───────────────────────────────────┐  │
│  │ 🧪 Testing Umapyoi Characters     │  │
│  │ URL: https://api.umapyoi.net/...  │  │
│  │ Status: 200 OK                    │  │
│  │ Response Data: {...}              │  │
│  └───────────────────────────────────┘  │
└─────────────────────────────────────────┘
```

---

## 🎯 Testing Umapyoi.net API

### Test 1: Characters List ✅

**Click:** "Test Characters List" button

**Expected Result:**

- ✅ Status: 200 OK
- ✅ Response: Array of character objects
- ✅ Console: Detailed character data

**What to Check:**

1. Network tab shows request to `/api/v1/character/list`
2. Status code is 200
3. Response contains character data
4. Console shows character count

### Test 2: Single Character ✅

**Click:** "Test Single Character" button

**Expected Result:**

- ✅ Status: 200 OK
- ✅ Response: Single character object
- ✅ Console: Character details

### Test 3: Support Cards ✅

**Click:** "Test Support Cards" button

**Expected Result:**

- ✅ Status: 200 OK
- ✅ Response: Array of support card objects
- ✅ Console: Support card data

### Test 4: Skills ⚠️

**Click:** "Test Skills" button

**Expected Result:**

- ⚠️ Status: May be 200 or 404
- ⚠️ Response: Array of skills or error
- ⚠️ Needs verification

### Test 5: News ✅

**Click:** "Test News" button

**Expected Result:**

- ✅ Status: 200 OK
- ✅ Response: Array of news articles
- ✅ Console: News data

### Test 6: Health Check ⚠️

**Click:** "Test Health Check" button

**Expected Result:**

- ⚠️ Status: May be 200 or 404
- ⚠️ Response: Health status or error
- ⚠️ Needs verification

---

## 🔍 Testing UmamusumeDB.com

### Important Note

⚠️ **UmamusumeDB.com does NOT have a public API**

The site blocks API access via robots.txt:

```
Disallow: /api/
```

### Test 1: Character Page

**Click:** "Test Character Page" button

**Expected Result:**

- ℹ️ Request sent (no-cors mode)
- ℹ️ Limited response info
- ℹ️ Check Network tab for details

**Note:** CORS prevents reading the response in JavaScript, but you can see it in the Network tab.

### Test 2: Support Card Page

**Click:** "Test Support Card Page" button

**Expected Result:**

- ℹ️ Request sent (no-cors mode)
- ℹ️ Limited response info
- ℹ️ Check Network tab for details

### Test 3: robots.txt ✅

**Click:** "Check robots.txt" button

**Expected Result:**

- ✅ Status: 200 OK
- ✅ Response: robots.txt content
- ✅ Shows `/api/` is disallowed

### Test 4: Sitemap ✅

**Click:** "Check Sitemap" button

**Expected Result:**

- ✅ Status: 200 OK
- ✅ Response: XML sitemap
- ✅ Shows available pages

---

## 📝 What to Look For

### In Network Tab

1. **Request URL** - The exact endpoint being called
2. **Status Code** - Should be 200 for success
3. **Response Headers** - Check Content-Type, CORS headers
4. **Request Headers** - Check Accept, User-Agent
5. **Response Body** - Click on request to see JSON data
6. **Timing** - How long the request took

### In Console Tab

1. **Grouped Logs** - Each test creates a collapsible group
2. **URL** - The endpoint being tested
3. **Status** - HTTP status code and text
4. **Response Data** - Parsed JSON (if successful)
5. **Error Messages** - If the request fails

---

## ✅ Success Indicators

### Umapyoi.net API

```
✅ Status: 200 OK
✅ Response: Valid JSON
✅ Data: Array or Object with expected fields
✅ Console: No errors
```

### Example Success Response

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Special Week",
      "japanese_name": "スペシャルウィーク"
    }
  ],
  "source": "api"
}
```

---

## ❌ Error Indicators

### Common Errors

#### 404 Not Found

```
❌ Status: 404
❌ Error: Endpoint doesn't exist
❌ Action: Verify URL structure
```

#### CORS Error

```
❌ Error: Blocked by CORS policy
❌ Note: Expected for UmamusumeDB.com
❌ Action: Check Network tab for actual response
```

#### Network Error

```
❌ Error: Failed to fetch
❌ Cause: API server down or network issue
❌ Action: Check internet connection
```

#### Timeout

```
❌ Error: Request timeout
❌ Cause: API is slow or overloaded
❌ Action: Try again later
```

---

## 🔧 Troubleshooting

### Issue: Can't see requests in Network tab

**Solution:**

1. Make sure Network tab is open BEFORE clicking buttons
2. Check "Preserve log" checkbox
3. Clear the network log and try again

### Issue: Console shows errors

**Solution:**

1. Read the error message carefully
2. Check if it's a CORS error (expected for some sites)
3. Check if the URL is correct
4. Try the URL directly in a new browser tab

### Issue: No response data

**Solution:**

1. Check the Network tab for the actual response
2. Look at the Response tab in the request details
3. Check if the API requires authentication
4. Verify the endpoint exists

---

## 📚 Next Steps

### If Tests Pass ✅

1. Document the working endpoints
2. Note the response structure
3. Update backend integration code
4. Write automated tests
5. Implement caching

### If Tests Fail ❌

1. Document the error
2. Check API documentation
3. Contact API provider
4. Implement fallback strategy
5. Update error handling

---

## 📞 Support

### Umapyoi.net

- Discord: <https://discord.gg/wvGHW65C6A>
- Developer: KevinVG207 (@kevinvg207)

### UmamusumeDB.com

- GitHub Issues: Check site footer
- Note: No public API available

---

## 📖 Related Documentation

- [FRONTEND_API_TESTING_GUIDE.md](./FRONTEND_API_TESTING_GUIDE.md) - Detailed guide
- [UMAPYOI_NET_API_STATUS.md](./UMAPYOI_NET_API_STATUS.md) - API documentation
- [umamusumedb-api-verification.md](./umamusumedb-api-verification.md) - UmamusumeDB findings

---

**Last Updated:** 2026-01-25  
**Status:** Active  
**Test Page:** <http://localhost:8000/test-api>
