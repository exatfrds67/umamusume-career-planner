# Frontend API Testing Guide

**Date:** 2026-01-25
**Purpose:** Test umapyoi.net and UmamusumeDB.com APIs directly in the browser using Chrome DevTools

---

## Overview

This guide explains how to test external APIs directly in the browser without writing backend code. This approach allows
you to:

- See real HTTP requests and responses in Chrome DevTools
- Inspect headers, status codes, and response bodies
- Test CORS behavior
- Debug API integration issues
- Verify API availability and response formats

---

## Access the Test Page

1. Start your Laravel development server:

   ```bash
   php artisan serve
   ```text

2. Navigate to the test page:

   ```
   http://localhost:8000/test-api
   ```text

---

## Using Chrome DevTools

### Step 1: Open DevTools

Press `F12` or right-click anywhere on the page and select "Inspect"

### Step 2: Navigate to Network Tab

1. Click on the "Network" tab in DevTools
2. Make sure "Preserve log" is checked (to keep requests after page navigation)
3. You can filter by "Fetch/XHR" to see only API requests

### Step 3: Navigate to Console Tab

1. Click on the "Console" tab to see detailed logs
2. All API test results will be logged here with full details
3. You can expand objects to see nested data

---

## Testing Umapyoi.net API

### Available Tests

1. **Test Characters List** - `/api/v1/character/list`
   - Fetches all available characters
   - Expected: Array of character objects
   - Status: ✅ Working (200 OK)

2. **Test Single Character** - `/api/v1/character/{id}`
   - Fetches a specific character by ID
   - Expected: Single character object
   - Status: ✅ Working (200 OK)

3. **Test Support Cards** - `/api/v1/support`
   - Fetches all support cards
   - Expected: Array of support card objects
   - Status: ✅ Working (200 OK)

4. **Test Skills** - `/api/v1/skill`
   - Fetches all skills
   - Expected: Array of skill objects
   - Status: ⚠️ Configured (needs verification)

5. **Test News** - `/api/v1/news/latest/{limit}`
   - Fetches latest news articles
   - Expected: Array of news objects
   - Status: ✅ Working (200 OK)

6. **Test Health Check** - `/health`
   - Checks API availability
   - Expected: Health status object
   - Status: ⚠️ Needs verification

### What to Look For

#### In Network Tab

- **Status Code**: Should be `200 OK` for successful requests
- **Response Headers**: Check `Content-Type`, `Access-Control-Allow-Origin` (CORS)
- **Request Headers**: Check `Accept`, `User-Agent`
- **Response Body**: Click on the request to see the JSON response
- **Timing**: Check how long the request took

#### In Console Tab

- **Grouped Logs**: Each test creates a collapsible group
- **URL**: The exact endpoint being tested
- **Status**: HTTP status code and text
- **Response Data**: Parsed JSON data
- **Error Messages**: If the request fails

### Example Success Response

```javascript
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Special Week",
      "japanese_name": "スペシャルウィーク",
      // ... more fields
    }
  ],
  "source": "api"
}
```text

### Example Error Response

```javascript
{
  "success": false,
  "data": [],
  "source": "error",
  "error": "HTTP 404: Not Found"
}
```text

---

## Testing UmamusumeDB.com

### Important Note

⚠️ **UmamusumeDB.com does NOT have a public API**. The site explicitly blocks API access via `robots.txt`:

```

Disallow: /api/

```text

### Available Tests

1. **Test Character Page** - `/characters/special_week_2025/`
   - Tests access to a character web page
   - Note: Uses `no-cors` mode (limited response info)

2. **Test Support Card Page** - `/cards/kitasan_black_ssr/`
   - Tests access to a support card web page
   - Note: Uses `no-cors` mode (limited response info)

3. **Check robots.txt** - `/robots.txt`
   - Fetches the robots.txt file
   - Shows which paths are allowed/disallowed

4. **Check Sitemap** - `/sitemap-index.xml`
   - Fetches the sitemap
   - Shows available pages

### CORS Limitations

When testing UmamusumeDB.com, you'll see:

```text

Note: no-cors mode - limited response info available
CORS prevents reading response. Check Network tab in DevTools.

```text

This is because:

- The site doesn't allow cross-origin requests
- We can send requests but can't read responses in JavaScript
- You can still see the request/response in the Network tab

---

## Common Issues and Solutions

### Issue: CORS Error

**Symptom:**

```

Access to fetch at '<https://api.example.com>' from origin '<http://localhost:8000>'
has been blocked by CORS policy

```text

**Solution:**

- This is expected for sites without CORS headers
- Check the Network tab to see if the request was sent
- The backend can still make these requests (server-side doesn't have CORS restrictions)

### Issue: 404 Not Found

**Symptom:**

```text

HTTP 404: Not Found

```text

**Possible Causes:**

1. Endpoint doesn't exist
2. Wrong URL structure
3. API is not yet live
4. Requires authentication

**Solution:**

- Verify the endpoint URL in documentation
- Try alternative URL structures
- Contact API provider

### Issue: Network Error

**Symptom:**

```

Failed to fetch
TypeError: Failed to fetch

```text

**Possible Causes:**

1. API server is down
2. Network connectivity issue
3. HTTPS/HTTP mismatch
4. Firewall blocking request

**Solution:**

- Check if the API website is accessible
- Try the request in a new browser tab
- Check your internet connection

### Issue: Timeout

**Symptom:**
Request takes too long and fails

**Solution:**

- API might be slow or overloaded
- Try again later
- Check API status page

---

## Interpreting Results

### Success Indicators

✅ **Status 200 OK**

- Request was successful
- Data is in the response body
- API is working correctly

✅ **Valid JSON Response**

- Response can be parsed as JSON
- Data structure matches expectations
- No syntax errors

✅ **Expected Data Structure**

- Response contains expected fields
- Data types are correct
- Arrays have items

### Warning Indicators

⚠️ **Status 3xx (Redirects)**

- Request was redirected
- Check the final URL
- May indicate API changes

⚠️ **Empty Response**

- Status 200 but no data
- May indicate no results
- Check query parameters

⚠️ **Unexpected Data Structure**

- Response format differs from expected
- May need to update code
- Check API version

### Error Indicators

❌ **Status 4xx (Client Errors)**

- 400: Bad Request (invalid parameters)
- 401: Unauthorized (needs authentication)
- 403: Forbidden (access denied)
- 404: Not Found (endpoint doesn't exist)
- 429: Too Many Requests (rate limited)

❌ **Status 5xx (Server Errors)**

- 500: Internal Server Error
- 502: Bad Gateway
- 503: Service Unavailable
- 504: Gateway Timeout

❌ **Network Errors**

- Failed to fetch
- Connection refused
- Timeout

---

## Next Steps After Testing

### If API Works

1. ✅ Document the working endpoints
2. ✅ Note the response structure
3. ✅ Update backend code if needed
4. ✅ Write integration tests
5. ✅ Implement caching strategy

### If API Fails

1. ❌ Document the error
2. 📝 Note the exact error message
3. 🔍 Check API documentation
4. 💬 Contact API provider
5. 🔄 Implement fallback strategy

---

## API Provider Contact Information

### Umapyoi.net

- **Website:** <https://umapyoi.net>
- **API Docs:** <https://api.umapyoi.net/docs>
- **Discord:** <https://discord.gg/wvGHW65C6A>
- **Developer:** KevinVG207 (@kevinvg207)

### UmamusumeDB.com

- **Website:** <https://umamusumedb.com>
- **GitHub Issues:** Available for feedback (mentioned in site footer)
- **Note:** No public API available

---

## Advanced Testing

### Testing with Different Parameters

You can modify the test functions in the browser console:

```javascript
// Test with different character ID
async function testCustomCharacter(id) {
    const url = `https://api.umapyoi.net/api/v1/character/${id}`;
    const response = await fetch(url);
    const data = await response.json();
    console.log(data);
}

testCustomCharacter(5);
```text

### Testing with Custom Headers

```javascript
async function testWithHeaders() {
    const response = await fetch('https://api.umapyoi.net/api/v1/character/list', {
        headers: {
            'Accept': 'application/json',
            'User-Agent': 'CustomAgent/1.0',
            'X-Custom-Header': 'test'
        }
    });
    console.log(await response.json());
}
```text

### Testing Rate Limits

```javascript
async function testRateLimit() {
    for (let i = 0; i < 20; i++) {
        console.log(`Request ${i + 1}`);
        const response = await fetch('https://api.umapyoi.net/api/v1/character/list');
        console.log(`Status: ${response.status}`);
        await new Promise(resolve => setTimeout(resolve, 100)); // 100ms delay
    }
}
```

---

## Troubleshooting Checklist

- [ ] DevTools is open (F12)
- [ ] Network tab is visible
- [ ] Console tab is visible
- [ ] "Preserve log" is checked in Network tab
- [ ] No browser extensions blocking requests (try incognito mode)
- [ ] Internet connection is working
- [ ] API server is accessible (try opening in new tab)
- [ ] Correct URL is being used
- [ ] Request method is correct (GET, POST, etc.)

---

## Related Documentation

- [UMAPYOI_NET_API_STATUS.md](./UMAPYOI_NET_API_STATUS.md) - Complete API documentation
- [umamusumedb-api-verification.md](./umamusumedb-api-verification.md) - UmamusumeDB findings
- [README.md](./README.md) - Integration overview

---

**Last Updated:** 2026-01-25
**Status:** Active
**Maintainer:** Development Team
