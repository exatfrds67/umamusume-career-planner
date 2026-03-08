# API Testing Quick Reference Card

**URL:** <http://localhost:8000/test-api>  
**DevTools:** Press F12

---

## 🚀 Quick Start

```bash
# 1. Start server
php artisan serve

# 2. Open browser
http://localhost:8000/test-api

# 3. Open DevTools
Press F12 → Network + Console tabs
```text

---

## 📊 Umapyoi.net API Endpoints

| Button | Endpoint | Expected |
| ------ | -------- | -------- |
| Characters List | `/api/v1/character/list` | ✅ 200 OK |
| Single Character | `/api/v1/character/1` | ✅ 200 OK |
| Support Cards | `/api/v1/support` | ✅ 200 OK |
| Skills | `/api/v1/skill` | ⚠️ Verify |
| News | `/api/v1/news/latest/10` | ✅ 200 OK |
| Health | `/health` | ⚠️ Verify |

**Base URL:** `https://api.umapyoi.net`

---

## 🔍 UmamusumeDB.com Tests

| Button | Endpoint | Expected |
| ------ | -------- | -------- |
| Character Page | `/characters/special_week_2025/` | ℹ️ CORS |
| Card Page | `/cards/kitasan_black_ssr/` | ℹ️ CORS |
| robots.txt | `/robots.txt` | ✅ 200 OK |
| Sitemap | `/sitemap-index.xml` | ✅ 200 OK |

**Base URL:** `https://umamusumedb.com`  
**Note:** ⚠️ No public API available

---

## ✅ Success Indicators

```
✅ Status: 200 OK
✅ Response: Valid JSON
✅ Console: No errors
✅ Data: Expected structure
```text

---

## ❌ Error Indicators

```
❌ 404: Endpoint not found
❌ CORS: Cross-origin blocked
❌ Network: Connection failed
❌ Timeout: Request too slow
```text

---

## 🔧 DevTools Tabs

### Network Tab

- See all HTTP requests
- Check status codes
- View headers
- Inspect response body
- Check timing

### Console Tab

- Detailed logs
- Parsed JSON data
- Error messages
- Request/response info

---

## 📝 What to Check

1. **Status Code** → Should be 200
2. **Response Type** → Should be JSON
3. **Data Structure** → Should match expected
4. **Console Logs** → Should show details
5. **Errors** → Should be none

---

## 🐛 Common Issues

| Issue | Solution |
| ----- | -------- |
| CORS Error | Expected for UmamusumeDB |
| 404 Error | Check endpoint URL |
| Network Error | Check internet connection |
| No response | Check API availability |

---

## 📞 Support

### Umapyoi.net

- Discord: <https://discord.gg/wvGHW65C6A>
- Developer: @kevinvg207

### UmamusumeDB.com

- GitHub Issues (see site footer)
- No public API

---

## 📚 Full Documentation

- [QUICK_START_TESTING.md](./QUICK_START_TESTING.md)
- [FRONTEND_API_TESTING_GUIDE.md](./FRONTEND_API_TESTING_GUIDE.md)
- [FRONTEND_TESTING_SUMMARY.md](./FRONTEND_TESTING_SUMMARY.md)

---

**Last Updated:** 2026-01-25
