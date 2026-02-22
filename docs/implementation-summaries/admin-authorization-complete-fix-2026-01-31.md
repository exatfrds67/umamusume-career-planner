# Admin Authorization Complete Fix

**Date**: 2026-01-31  
**Status**: ✅ Complete  
**Related Issues**: Admin user unable to toggle pin on characters (403 Unauthorized)

## Problem Summary

The admin user created by `AdminUserSeeder` was not able to perform actions on characters, including toggling the pin
status. The error was:

```text
POST http://127.0.0.1:8000/characters/4/toggle-pin
403 This action is unauthorized
```

## Root Cause

The `AdminUserSeeder` was creating the admin user WITHOUT setting the `is_admin` flag to `true`. This meant that the
`CharacterPolicy::before()` method's admin bypass was never triggered:

```php
public function before(User $user, string $ability): ?bool
{
    if ($user->isAdmin()) {
        return true; // Admin bypass - allows all actions
    }
    return null;
}
```text

The `User::isAdmin()` method checks:

```php
public function isAdmin(): bool
{
    return (bool) ($this->is_admin ?? false);
}
```

Since `is_admin` was `false` (default), the admin user was treated as a regular user.

## Solution

### 1. Fixed AdminUserSeeder

Updated `database/seeders/AdminUserSeeder.php` to include the `is_admin` flag:

```php
User::create([
    'uuid' => Str::uuid()->toString(),
    'email' => 'admin@umamusume.local',
    'name' => 'Admin',
    'password' => Hash::make('admin123'),
    'is_admin' => true, // CRITICAL: Set admin flag
    'preferences' => [
        'theme' => 'dark',
        'language' => 'en',
    ],
    // ... other fields
]);
```text

### 2. Updated Existing Admin User

Used Laravel Boost tinker to update the existing admin user in the database:

```php
$user = \App\Models\User::where('email', 'admin@umamusume.local')->first();
$user->is_admin = true;
$user->save();
```

## Verification

### Policy Tests

All 15 CharacterPolicy tests pass, including:

- ✅ Admin can view any character
- ✅ Admin can update any character
- ✅ Admin can delete any character
- ✅ Admin can force delete any character
- ✅ Admin can restore any character

### Admin Bypass Logic

The `CharacterPolicy::before()` method now correctly returns `true` for admin users, bypassing all other authorization
checks in the policy.

## Files Modified

1. `database/seeders/AdminUserSeeder.php` - Added `is_admin => true`

## Database Changes

- Updated existing admin user record: `is_admin = true`

## Testing

```bash
php artisan test --filter=CharacterPolicyTest --compact
```text

**Result**: 15 passed (15 assertions)

## Admin Credentials

- **Email**: `admin@umamusume.local`
- **Password**: `admin123`
- **Admin Flag**: `true` ✅

## Impact

- Admin users can now perform ALL actions on ALL resources
- No more 403 Unauthorized errors for admin users
- Policy `before()` method correctly bypasses all authorization checks
- Seeded characters can be managed by admin
- User-created characters can be managed by admin

## Related Documentation

- `docs/authorization-audit-2026-01-29.md` - Previous authorization audit
- `docs/authorization-fix-summary.md` - Previous authorization fixes
- `app/Policies/CharacterPolicy.php` - Policy with admin bypass
- `app/Models/User.php` - User model with `isAdmin()` method

## Notes

- The `is_admin` column already existed in the database schema
- The policy logic was already correct with the `before()` method
- The only issue was the missing flag in the seeder
- Future admin users created via the seeder will have the flag set correctly

