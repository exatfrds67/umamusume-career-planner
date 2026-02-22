# Logout Route Issue Fix

**Date**: January 26, 2026  
**Status**: ✅ Complete  
**Issue**: Web logout redirecting to API endpoint instead of properly logging out users  

## Problem Analysis

### Root Cause

Route name conflict between web and API logout routes:

1. **Web logout route** (`routes/web.php`):

   ```php
   Route::post('/logout', [LoginController::class, 'destroy'])
       ->middleware('auth')
       ->name('logout');  // ❌ Conflicting name
   ```text

2. **API logout route** (`routes/api.php`):

   ```php
   Route::post('/logout', [AuthController::class, 'logout'])
       ->name('logout');  // ❌ Same name as web route
   ```

### Impact

- Header component `route('logout')` resolved to API route instead of web route
- Users clicking "Sign out" were hitting `/api/logout` endpoint
- API logout only revokes Sanctum tokens, doesn't handle web session logout
- Users remained logged in to web interface despite clicking logout

## Solution Implemented

### 1. Fixed Route Name Conflict

Changed API logout route name to avoid conflict:

```php
// routes/api.php
Route::post('/logout', [\App\Http\Controllers\Api\Auth\AuthController::class, 'logout'])
    ->name('api.logout');  // ✅ Unique name
```text

### 2. Route Resolution Verification

- **Web logout**: `route('logout')` → `http://127.0.0.1:8000/logout`
- **API logout**: `route('api.logout')` → `http://127.0.0.1:8000/api/logout`

### 3. Functionality Verification

- **Web logout** (`LoginController@destroy`):
  - Logs out web guard: `Auth::guard('web')->logout()`
  - Invalidates session: `request()->session()->invalidate()`
  - Regenerates CSRF token: `request()->session()->regenerateToken()`
  - Redirects to welcome page: `redirect()->route('welcome')`

- **API logout** (`AuthController@logout`):
  - Revokes current Sanctum token: `$user->currentAccessToken()->delete()`
  - Returns JSON response: `{'message': 'Logged out successfully.'}`

## Testing

### Test Coverage

Created comprehensive test suite (`tests/Feature/Auth/LogoutRouteTest.php`):

1. ✅ **Route Resolution Tests**
   - Web logout route resolves correctly
   - API logout route resolves correctly

2. ✅ **Web Logout Functionality**
   - Logs out user and redirects to welcome page
   - User becomes guest after logout

3. ✅ **Header Component Integration**
   - Header logout form uses correct web route
   - Form method is POST with CSRF protection

### Test Results

```bash
Tests:    4 passed (7 assertions)
Duration: 19.10s
```

## Files Modified

### Core Fix

- `routes/api.php` - Changed API logout route name from `'logout'` to `'api.logout'`

### Testing

- `tests/Feature/Auth/LogoutRouteTest.php` - Comprehensive test coverage

## Verification Steps

1. **Route Resolution**:

   ```php
   route('logout')     // → /logout (web)
   route('api.logout') // → /api/logout (API)
   ```

2. **Header Component**:
   - Form action correctly points to web logout route
   - CSRF token included for security

3. **Logout Flow**:
   - Click "Sign out" → POST to `/logout`
   - Web session invalidated
   - User redirected to welcome page
   - User is logged out

## Impact Assessment

### ✅ Fixed Issues

- Web logout now works correctly
- Users can properly sign out from web interface
- Route name conflicts resolved
- No breaking changes to existing API functionality

### 🔄 Maintained Functionality

- API logout still works for API clients
- All existing routes and functionality preserved
- CSRF protection maintained
- Session security maintained

## Future Considerations

### Route Naming Convention

Established pattern for avoiding conflicts:

- Web routes: Use simple names (`'logout'`, `'login'`, etc.)
- API routes: Use prefixed names (`'api.logout'`, `'api.login'`, etc.)

### API Documentation

API logout route name changed from `'logout'` to `'api.logout'`:

- Update any API documentation referencing the route name
- Frontend API clients should use `route('api.logout')` if using Laravel route helpers

## Conclusion

The logout route issue has been successfully resolved by fixing the route name conflict. Users can now properly log out
from the web interface, and the API logout functionality remains intact with a properly namespaced route name. The fix
is minimal, focused, and maintains backward compatibility while resolving the core issue.
