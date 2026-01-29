# Avatar Upload Troubleshooting Guide

## Quick Checklist

If the avatar upload isn't working, check these items:

### 1. Browser Console Errors

Open browser DevTools (F12) and check the Console tab for JavaScript errors.

**Common Issues:**

- `avatarUploader is not defined` → The function isn't loading properly
- `CSRF token mismatch` → Session expired, refresh the page
- `404 Not Found` → Route not registered properly

### 2. Network Tab

Check the Network tab in DevTools when uploading:

**Expected Behavior:**

- POST request to `/profile/avatar`
- Status: 200 OK
- Response: JSON with `message` and `avatar_url`

**Common Issues:**

- 419 (CSRF Token Mismatch) → Refresh page
- 422 (Validation Error) → File too large or wrong type
- 500 (Server Error) → Check Laravel logs

### 3. File Permissions

Ensure the storage directory is writable:

```bash
# Windows (PowerShell as Administrator)
icacls storage /grant Users:F /T

# Or check if storage/app/public exists
php artisan storage:link
```

### 4. Assets Not Updated

If you made changes but don't see them:

```bash
# Rebuild frontend assets
npm run build

# Or run dev server
npm run dev
```

### 5. Cache Issues

Clear browser and Laravel cache:

```bash
# Laravel cache
php artisan cache:clear
php artisan view:clear
php artisan config:clear

# Browser: Hard refresh (Ctrl+Shift+R or Cmd+Shift+R)
```

## Testing the Feature

### Manual Test Steps

1. **Navigate to Profile**
   - Go to <http://127.0.0.1:8000/profile#account>
   - You should see your avatar (or default UI Avatars image)

2. **Upload Avatar**
   - Click "Change Avatar" button
   - Select a valid image (JPG, PNG, GIF, max 2MB)
   - Image should preview immediately
   - Upload should complete with success message
   - Avatar should update

3. **Remove Avatar**
   - Click the trash icon button
   - Confirm deletion
   - Avatar should revert to default

### Automated Test

```bash
php artisan test --filter=Avatar
```

Expected: 4 tests pass

## Common Error Messages

### "Please select a valid image file"

- **Cause**: File type not supported
- **Solution**: Use JPG, PNG, or GIF only

### "File size must be less than 2MB"

- **Cause**: File too large
- **Solution**: Resize or compress image before uploading

### "Upload failed"

- **Cause**: Server error or validation failure
- **Solution**: Check Laravel logs at `storage/logs/laravel.log`

### "Failed to upload avatar. Please try again."

- **Cause**: Network error or server timeout
- **Solution**: Check internet connection and try again

## Debugging Steps

### 1. Check if Alpine.js is loaded

Open browser console and type:

```javascript
window.Alpine
```

Should return an object. If undefined, Alpine.js isn't loading.

### 2. Check if avatarUploader function exists

```javascript
window.avatarUploader
```

Should return a function. If undefined, the script isn't loading.

### 3. Test the upload endpoint directly

```bash
# Using curl (replace with actual file path)
curl -X POST http://127.0.0.1:8000/profile/avatar \
  -H "Accept: application/json" \
  -H "X-CSRF-TOKEN: YOUR_TOKEN_HERE" \
  -F "avatar=@/path/to/image.jpg"
```

### 4. Check Laravel logs

```bash
# View last 50 lines of log
Get-Content storage/logs/laravel.log -Tail 50
```

### 5. Verify routes are registered

```bash
php artisan route:list --name=profile.avatar
```

Should show:

- POST profile/avatar
- DELETE profile/avatar

## Browser Compatibility

**Supported:**

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

**Required Features:**

- File API
- Fetch API
- FormData
- FileReader
- Promises/Async-Await

## Server Requirements

- PHP 8.2+
- GD or Imagick extension (for image validation)
- Write permissions on `storage/app/public`
- Symlink from `public/storage` to `storage/app/public`

## Still Not Working?

1. **Check server logs**: `storage/logs/laravel.log`
2. **Check browser console**: F12 → Console tab
3. **Check network requests**: F12 → Network tab
4. **Verify file permissions**: `storage/app/public` must be writable
5. **Clear all caches**: Browser + Laravel
6. **Rebuild assets**: `npm run build`
7. **Restart server**: Stop and start `php artisan serve`

## Getting Help

If none of the above works, provide:

1. Browser console errors (screenshot)
2. Network tab showing failed request (screenshot)
3. Laravel log errors (last 20 lines)
4. PHP version: `php -v`
5. Laravel version: `php artisan --version`
6. Browser and version

---

**Last Updated**: January 29, 2026
