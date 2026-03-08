# Production Testing Guide

## Overview

This guide outlines the comprehensive testing procedures for validating the UmamusumeCareerPlanner application before
production deployment.

---

## 1. Staging Environment Testing

### Environment Setup

Ensure staging mirrors production:

```bash
# Verify environment configuration
php artisan env
# Should show: staging

# Verify database connection
php artisan db:show

# Verify cache connection
php artisan cache:clear && php artisan cache:get test
```text

## Smoke Tests

Quick validation of core functionality:

```bash
# Run smoke test suite
php artisan test --testsuite=Smoke --compact

# Or manually verify endpoints
curl -s https://staging.example.com/api/health | jq .
curl -s https://staging.example.com/api/v1/characters | jq .
```text

## Expected Results

| Endpoint             | Expected Status | Response Time |
| -------------------- | --------------- | ------------- |
| `/api/health`        | 200             | < 100ms       |
| `/api/v1/characters` | 200/401         | < 200ms       |
| `/api/v1/careers`    | 200/401         | < 200ms       |
| `/api/v1/skills`     | 200             | < 150ms       |

---

## 2. User Acceptance Testing (UAT)

### Test Scenarios

#### Scenario 1: New User Registration

1. Navigate to registration page
2. Fill in valid user details
3. Submit registration form
4. Verify email confirmation (if enabled)
5. Log in with new credentials
6. Verify dashboard access

**Expected**: User can register, receive confirmation, and access dashboard.

#### Scenario 2: Character Management

1. Log in as authenticated user
2. Navigate to Characters section
3. Create new character with valid data
4. Edit character details
5. View character statistics
6. Delete character (if permitted)

**Expected**: All CRUD operations complete successfully.

#### Scenario 3: Career Planning

1. Select existing character
2. Start new career run
3. Configure career settings
4. View training recommendations
5. Record training session
6. View career progress

**Expected**: Career workflow completes without errors.

#### Scenario 4: Skill Management

1. Navigate to Skills section
2. Browse available skills
3. Filter by category/type
4. View skill details
5. Add skill to character (if applicable)

**Expected**: Skill browsing and management works correctly.

#### Scenario 5: Support Card Deck

1. Navigate to Support Cards
2. View card collection
3. Build deck (6 cards)
4. Save deck configuration
5. View deck synergies

**Expected**: Deck building completes successfully.

### UAT Sign-Off Template

```markdown
## UAT Sign-Off

**Tester**: [Name]
**Date**: [Date]
**Environment**: Staging

### Test Results

| Scenario | Status | Notes |
|----------|--------|-------|
| New User Registration | ✅/❌ | |
| Character Management | ✅/❌ | |
| Career Planning | ✅/❌ | |
| Skill Management | ✅/❌ | |
| Support Card Deck | ✅/❌ | |

### Issues Found

1. [Issue description]
2. [Issue description]

### Recommendation

[ ] Ready for production
[ ] Requires fixes before production
[ ] Major issues - delay deployment

**Signature**: ________________
```text

---

## 3. Performance Testing

### Load Testing

Using Apache Bench or similar:

```bash
# Test homepage
ab -n 1000 -c 50 https://staging.example.com/

# Test API endpoint
ab -n 500 -c 25 -H "Authorization: Bearer $TOKEN" \
   https://staging.example.com/api/v1/characters
```

## Expected Performance Metrics

| Metric               | Target  | Acceptable |
| -------------------- | ------- | ---------- |
| Response Time (avg)  | < 100ms | < 200ms    |
| Response Time (95th) | < 200ms | < 500ms    |
| Requests/sec         | > 100   | > 50       |
| Error Rate           | 0%      | < 1%       |

### Database Performance

```sql
-- Check slow queries
SELECT * FROM mysql.slow_log 
ORDER BY start_time DESC 
LIMIT 20;

-- Check query execution plans
EXPLAIN ANALYZE SELECT * FROM characters WHERE user_id = 1;
```text

### Memory and Resource Usage

```bash
# Monitor during load test
watch -n 1 'free -m && echo "---" && top -bn1 | head -20'

# Check PHP-FPM status
curl http://localhost/status?full
```text

---

## 4. Security Testing

### Authentication Tests

```bash
# Test invalid credentials
curl -X POST https://staging.example.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"invalid@test.com","password":"wrong"}'
# Expected: 401 Unauthorized

# Test rate limiting
for i in {1..20}; do
  curl -s -o /dev/null -w "%{http_code}\n" \
    -X POST https://staging.example.com/api/login \
    -H "Content-Type: application/json" \
    -d '{"email":"test@test.com","password":"wrong"}'
done
# Expected: 429 after threshold
```text

## Authorization Tests

```bash
# Test accessing other user's data
curl -H "Authorization: Bearer $USER1_TOKEN" \
  https://staging.example.com/api/v1/characters/999
# Expected: 403 Forbidden or 404 Not Found

# Test admin-only endpoints
curl -H "Authorization: Bearer $REGULAR_USER_TOKEN" \
  https://staging.example.com/admin/users
# Expected: 403 Forbidden
```

## Input Validation Tests

```bash
# Test XSS prevention
curl -X POST https://staging.example.com/api/v1/characters \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"<script>alert(1)</script>"}'
# Expected: Validation error or sanitized input

# Test SQL injection prevention
curl "https://staging.example.com/api/v1/characters?search='; DROP TABLE users;--"
# Expected: No SQL error, safe handling
```text

---

## 5. Accessibility Testing

### Automated Testing

```bash
# Run accessibility tests
npm run test:accessibility

# Or use axe-core CLI
npx axe https://staging.example.com/
```text

## Manual Testing Checklist

- [ ] All images have alt text
- [ ] Form fields have labels
- [ ] Color contrast meets WCAG AA (4.5:1)
- [ ] Focus indicators visible
- [ ] Keyboard navigation works
- [ ] Screen reader announces content correctly
- [ ] Skip links present
- [ ] Error messages are descriptive

---

## 6. Browser Compatibility

### Supported Browsers

| Browser       | Version     | Status   |
| ------------- | ----------- | -------- |
| Chrome        | Latest 2    | Required |
| Firefox       | Latest 2    | Required |
| Safari        | Latest 2    | Required |
| Edge          | Latest 2    | Required |
| Mobile Safari | iOS 15+     | Required |
| Chrome Mobile | Android 10+ | Required |

### Testing Checklist

For each browser:

- [ ] Homepage loads correctly
- [ ] Navigation works
- [ ] Forms submit properly
- [ ] JavaScript features work
- [ ] CSS renders correctly
- [ ] Responsive design works

---

## 7. Mobile Testing

### Device Testing

| Device     | OS Version | Screen Size |
| ---------- | ---------- | ----------- |
| iPhone 14  | iOS 17     | 390x844     |
| iPhone SE  | iOS 17     | 375x667     |
| Pixel 7    | Android 14 | 412x915     |
| Galaxy S23 | Android 14 | 360x780     |

### Mobile-Specific Tests

- [ ] Touch targets are 44x44px minimum
- [ ] Pinch-to-zoom works
- [ ] Orientation changes handled
- [ ] Virtual keyboard doesn't obscure inputs
- [ ] PWA installs correctly
- [ ] Offline mode works (if applicable)

---

## 8. Integration Testing

### External API Integration

```bash
# Test external API connectivity
php artisan tinker --execute="
  \$response = Http::get('https://api.umapyoi.net/characters');
  echo \$response->status();
"
```text

## Queue Processing

```bash
# Dispatch test job
php artisan tinker --execute="
  dispatch(new App\Jobs\TestJob());
"

# Verify job processed
php artisan queue:work --once
```

## Cache Operations

```bash
# Test cache operations
php artisan tinker --execute="
  Cache::put('test', 'value', 60);
  echo Cache::get('test');
  Cache::forget('test');
"
```text

---

## 9. Regression Testing

### Critical Path Tests

Run full test suite:

```bash
# All tests
php artisan test --compact

# With coverage
php artisan test --coverage --min=80
```text

## Specific Regression Areas

After any changes, verify:

1. **Authentication** - Login/logout/registration
2. **Authorization** - Permission checks
3. **Data integrity** - CRUD operations
4. **API contracts** - Response formats
5. **Performance** - No degradation

---

## 10. Final Verification

### Pre-Production Checklist

```bash
# Verify production readiness
php artisan about

# Check for debug mode
grep "APP_DEBUG" .env
# Should be: APP_DEBUG=false

# Verify caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Check for pending migrations
php artisan migrate:status
```text

## Health Check

```bash
# Comprehensive health check
curl https://staging.example.com/api/health | jq .

# Expected response:
{
  "status": "healthy",
  "checks": {
    "database": "ok",
    "redis": "ok",
    "queue": "ok",
    "storage": "ok"
  }
}
```

---

## Test Report Template

```markdown
# Production Testing Report

**Date**: [Date]
**Environment**: Staging
**Version**: [Version]
**Tester**: [Name]

## Summary

| Category | Passed | Failed | Blocked |
|----------|--------|--------|---------|
| Smoke Tests | | | |
| UAT | | | |
| Performance | | | |
| Security | | | |
| Accessibility | | | |
| Browser Compat | | | |
| Mobile | | | |
| Integration | | | |
| Regression | | | |

## Issues Found

### Critical
- None / [List issues]

### Major
- None / [List issues]

### Minor
- None / [List issues]

## Recommendation

[ ] ✅ Ready for production deployment
[ ] ⚠️ Ready with known issues (documented)
[ ] ❌ Not ready - requires fixes

## Sign-Off

| Role | Name | Date |
|------|------|------|
| QA Lead | | |
| Dev Lead | | |
| Product Owner | | |
```text

