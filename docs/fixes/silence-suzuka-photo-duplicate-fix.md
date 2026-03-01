# Silence Suzuka Photo & Duplicate Character Fix

**Date**: February 27, 2026
**Issue**: Silence Suzuka missing photo + duplicate DB record on `/characters` page
**Status**: ✅ RESOLVED

---

## Problem 1 — Missing Photo

Silence Suzuka was the only character out of 169 whose avatar was broken on the `/characters` page.
All other characters loaded their local images correctly.

### Root Cause

The `buildLocalImageMap()` method in `EnhancedRealUmaMusumeCharactersSeeder` uses a regex to identify
local image files:

```php
->filter(fn ($file) => preg_match('/^__([a-z_]+)_umamusume/', $file->getFilename()))
```

Standard filenames follow the pattern `__character_name_umamusume_..._hash.jpg`.
Silence Suzuka's local image file is `bb962aabeafaee5cbf7831e4d178ca64.jpg` — a hash-only filename
with no `__name_umamusume` prefix. The regex never matched it.

Because no local file was found, the seeder fell back to a Microcms external URL that was already
broken:

```text
https://images.microcms-assets.io/assets/.../silencesuzuka_list.png  ← broken 404
```

### Fix Applied

Three coordinated changes to `database/seeders/EnhancedRealUmaMusumeCharactersSeeder.php`:

#### 1. Added slug to display name mapping

In `slugToName()`, after the `'grass_wonder'` entry:

```php
'silence_suzuka' => 'Silence Suzuka',
```

#### 2. Added manual override block in `buildLocalImageMap()`

After the regex-based `$localImageMap` collection is built:

```php
$manualOverrides = [
    'bb962aabeafaee5cbf7831e4d178ca64.jpg' => 'Silence Suzuka',
];
foreach ($manualOverrides as $filename => $characterName) {
    $fullPath = $imagePath.DIRECTORY_SEPARATOR.$filename;
    if (File::exists($fullPath)) {
        $this->localImageMap->put($characterName, '/images/trainee_images/'.$filename);
    }
}
```

#### 3. Self-healing update for existing records

In the existing-character update path, added a check to fix broken external `avatar_url` values
when a local image is available:

```php
if (
    $this->localImageMap->has($characterName)
    && ! str_starts_with((string) $character->avatar_url, '/images/')
) {
    $character->update(['avatar_url' => $this->localImageMap->get($characterName)]);
}
```

#### 4. Direct DB fix

Updated `ucp_characters` ID 135 (Silence Suzuka):

| Field | Before | After |
| --- | --- | --- |
| `avatar_url` | `https://images.microcms-assets.io/.../silencesuzuka_list.png` | `/images/trainee_images/bb962aabeafaee5cbf7831e4d178ca64.jpg` |

---

## Problem 2 — Duplicate DB Record

After fixing the photo, investigation revealed Silence Suzuka appeared **twice** on the characters page.

### Duplicate Root Cause

A prior seeder re-run created a second `ucp_characters` record (ID 195). The seeder's existence
check (`WHERE user_id = ? AND name = ?`) had not matched the original row during that re-run,
resulting in a duplicate insert.

### Duplicate Records

| ID | Created | Aptitudes | Career Data |
| --- | --- | --- | --- |
| 135 | 2026-02-24 | 12 | ✅ Full (kept) |
| 195 | 2026-02-25 | 10 | ❌ None (deleted) |

### Verification Before Deletion

Checked all 14 tables with foreign keys referencing `ucp_characters.id` for ID 195:

- `ucp_careers`: 0 rows
- `ucp_races`: 0 rows
- `ucp_skill_acquisitions`: 0 rows
- `ucp_training_sessions`: 0 rows
- `ucp_factors`: 0 rows
- `ucp_events`: 0 rows
- `ucp_aptitudes`: 10 rows (deleted alongside the character)

### Deletion Fix Applied

Ran a one-off cleanup script that:

1. Deleted 10 `ucp_aptitudes` rows for ID 195
2. Deleted `ucp_characters` row ID 195

### Verification After Fix

```sql
SELECT COUNT(*) as total, COUNT(DISTINCT name) as unique_names
FROM ucp_characters
WHERE user_id = 28;
```

Result: `total = 169, unique_names = 169` ✅

---

## Files Changed

| File | Change |
| --- | --- |
| `database/seeders/EnhancedRealUmaMusumeCharactersSeeder.php` | 3 targeted fixes |
| `ucp_characters` ID 135 | `avatar_url` updated to local path |
| `ucp_characters` ID 195 | Deleted (duplicate) |
| `ucp_aptitudes` (10 rows for ID 195) | Deleted |

---

## Prevention Notes

- Future non-standard image filenames must be added to the `$manualOverrides` array in
  `buildLocalImageMap()`.
- The self-healing update path in the seeder will now automatically fix any broken external
  `avatar_url` values for characters that have a local image available, preventing silent
  regressions on re-seed.
- Seeder idempotency (`UpdateOrCreate` vs `firstOrCreate`) should be reviewed if bulk re-seeds
  are planned to fully prevent duplicate record creation.
