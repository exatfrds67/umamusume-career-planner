# Phase 3 Implementation Guide

This guide provides step-by-step instructions for updating the Blade templates to use the extracted JavaScript files.

---

## Prerequisites

1. Ensure all Phase 3 JS files are created:
   - `resources/js/pages/characters/create.js` ✓
   - `resources/js/pages/characters/edit.js` ✓
   - `resources/js/pages/profile/show.js` ✓
   - `resources/js/pages/mcp/dashboard.js` ✓

2. Verify `vite.config.js` is updated with new entry points ✓

3. Run `npm install` if needed

---

## Implementation Steps

### Step 1: Update characters/create.blade.php

**Location**: `resources/views/characters/create.blade.php`

**Find** (around line 802):

```blade
    </div>

    <script>
        function characterWizard() {
            return {
                // ... ~1900 lines of JavaScript ...
            };
        }
    </script>
@endsection
```

**Replace with**:

```blade
    </div>

    {{-- Data injection for character creation wizard --}}
    <script>
        window.pageData = {
            trainees: @json($trainees ?? []),
            routes: {
                externalSearch: "{{ route('api.characters.prefill.search') }}",
                externalLoad: "{{ route('api.characters.prefill.load') }}"
            },
            externalPrefill: @json($externalPrefill ?? null)
        };
    </script>
    @vite(['resources/js/pages/characters/create.js'])
@endsection
```

**Note**: The trainee data array is massive. If `$trainees` is not passed from the controller, you may need to add it. Check the controller method that renders this view.

---

### Step 2: Update characters/edit.blade.php

**Location**: `resources/views/characters/edit.blade.php`

**Find** (around line 180-220, after the closing `</form>` tag):

```blade
    </form>

    <script>
        function enforceStatMax(input) {
            const max = 1200;
            const errorId = input.id.includes('goal_') ? null : input.id + '_error';
            const errorEl = errorId ? document.getElementById(errorId) : null;

            if (parseInt(input.value) > max) {
                input.value = max;
                if (errorEl) {
                    errorEl.classList.remove('hidden');
                    setTimeout(() => errorEl.classList.add('hidden'), 2000);
                }
            }

            if (parseInt(input.value) < 0) {
                input.value = 0;
            }
        }

        document.getElementById('character-form').addEventListener('submit', function(e) {
            const statInputs = document.querySelectorAll('.stat-input');
            let hasError = false;

            statInputs.forEach(input => {
                if (parseInt(input.value) > 1200) {
                    input.value = 1200;
                    hasError = true;
                }
            });

            if (hasError) {
                // Just notify, form still submits with corrected values
                // alert('Stats capped to 1200');
            }
        });
    </script>
@endsection
```

**Replace with**:

```blade
    </form>

    @vite(['resources/js/pages/characters/edit.js'])
@endsection
```

**Note**: The `enforceStatMax` function is still called inline from the Blade template (`oninput="enforceStatMax(this)"`). The extracted JS file makes this function globally available via `window.enforceStatMax`.

---

### Step 3: Update profile/show.blade.php

**Location**: `resources/views/profile/show.blade.php`

**Find** (in the `@push('scripts')` section near the end):

```blade
    @push('scripts')
        <script>
            function profileManager() {
                return {
                    activeTab: 'account',
                    loading: false,

                    init() {
                        // Check URL hash for tab
                        const hash = window.location.hash.replace('#', '');
                        if (hash && ['account', 'preferences', 'notifications', 'privacy', 'security'].includes(hash)) {
                            this.activeTab = hash;
                        }

                        // Update URL hash when tab changes
                        this.$watch('activeTab', (value) => {
                            window.location.hash = value;
                        });
                    }
                };
            }

            // Make avatarUploader globally available for Alpine.js
            window.avatarUploader = function avatarUploader() {
                return {
                    // ... avatar upload logic ...
                };
            }
        </script>
    @endpush
@endsection
```

**Replace with**:

```blade
    {{-- Data injection for profile management --}}
    <script>
        window.pageData = {
            routes: {
                avatarUpload: "{{ route('profile.avatar') }}",
                avatarDelete: "{{ route('profile.avatar.delete') }}"
            }
        };
    </script>
    @vite(['resources/js/pages/profile/show.js'])
@endsection
```

**Note**: Remove the entire `@push('scripts')` section and replace with the data injection + @vite directive.

---

### Step 4: Update mcp/dashboard.blade.php

**Location**: `resources/views/mcp/dashboard.blade.php`

**Find** (in the `@push('scripts')` section near the end):

```blade
    @push('scripts')
        <script>
            function mcpDashboard() {
                return {
                    activeTab: 'overview',
                    lastUpdated: '--',
                    performanceTimeRange: '24h',
                    loading: false,

                    overview: {
                        // ... state objects ...
                    },

                    // ... methods ...
                }
            }
        </script>
    @endpush
</x-app-layout>
```

**Replace with**:

```blade
    {{-- Data injection for MCP dashboard --}}
    <script>
        window.pageData = {
            routes: {
                overview: '/api/mcp/dashboard/overview',
                servers: '/api/mcp/servers',
                agents: '/api/mcp/agents',
                costs: '/api/mcp/costs',
                performance: '/api/mcp/performance',
                settings: '/api/mcp/settings'
            }
        };
    </script>
    @vite(['resources/js/pages/mcp/dashboard.js'])
</x-app-layout>
```

**Note**: Remove the entire `@push('scripts')` section and replace with the data injection + @vite directive before the closing `</x-app-layout>` tag.

---

## Step 5: Build Assets

After updating all Blade files, build the assets:

```bash
npm run build
```

Or for development with hot reload:

```bash
npm run dev
```

---

## Step 6: Verify Changes

### 1. Check Build Output

```bash
npm run build
```

Expected output should include:

```
✓ built in XXXms
✓ 15 modules transformed.
dist/assets/create-[hash].js
dist/assets/edit-[hash].js
dist/assets/show-[hash].js
dist/assets/dashboard-[hash].js
```

### 2. Check Browser Console

Navigate to each page and check the browser console:

- No JavaScript errors
- No 404 errors for missing assets
- Alpine.js components initialize correctly

### 3. Test Functionality

#### Character Create

1. Go to `/characters/create`
2. Test wizard navigation
3. Test database search
4. Test external API search
5. Test avatar upload
6. Test form submission

#### Character Edit

1. Go to `/characters/{id}/edit`
2. Test stat input validation
3. Test form submission

#### Profile

1. Go to `/profile`
2. Test tab navigation
3. Test avatar upload
4. Test form submission

#### MCP Dashboard

1. Go to `/mcp/dashboard`
2. Verify data loads
3. Test tab switching
4. Verify polling works

---

## Troubleshooting

### Issue: "window.pageData is undefined"

**Solution**: Ensure the `<script>` block with `window.pageData` is placed BEFORE the `@vite()` directive.

### Issue: "Alpine component not found"

**Solution**: Verify that `Alpine.data()` is being called in the extracted JS file and that Alpine.js is imported.

### Issue: "Function not defined" (e.g., enforceStatMax)

**Solution**: Check that the function is exposed globally via `window.functionName = ...` in the extracted JS file.

### Issue: "CSRF token mismatch"

**Solution**: Ensure all API calls include the CSRF token:

```javascript
'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
```

### Issue: "Route not found"

**Solution**: Verify that all routes in `window.pageData.routes` exist in your Laravel routes file.

---

## Rollback Plan

If issues occur, you can quickly rollback by:

1. Restore the original Blade files from git:

   ```bash
   git checkout resources/views/characters/create.blade.php
   git checkout resources/views/characters/edit.blade.php
   git checkout resources/views/profile/show.blade.php
   git checkout resources/views/mcp/dashboard.blade.php
   ```

2. Remove the new entries from `vite.config.js`

3. Rebuild assets:

   ```bash
   npm run build
   ```

---

## Additional Notes

### Controller Updates

You may need to update controllers to pass required data:

**CharacterController@create**:

```php
public function create()
{
    $trainees = Character::where('is_seeded', true)->get();
    
    return view('characters.create', [
        'trainees' => $trainees,
        'externalPrefill' => session('external_character_prefill')
    ]);
}
```

### Route Verification

Ensure these routes exist:

- `api.characters.prefill.search`
- `api.characters.prefill.load`
- `profile.avatar`
- `profile.avatar.delete`
- `/api/mcp/dashboard/overview`
- `/api/mcp/servers`
- `/api/mcp/agents`
- `/api/mcp/costs`
- `/api/mcp/performance`
- `/api/mcp/settings`

---

## Success Criteria

✅ All 4 Blade files updated  
✅ `npm run build` completes without errors  
✅ All pages load without console errors  
✅ All functionality works as before  
✅ Assets are properly cached  
✅ File sizes are reasonable  

---

## Support

If you encounter issues:

1. Check the browser console for errors
2. Verify network tab for 404s
3. Review the Phase 3 summary document
4. Check the quick reference guide from Phase 1 & 2
