# Aptitude Data Collection Guide

**Purpose**: Guide for collecting and adding official aptitude data for the remaining 151 Uma Musume characters

## Quick Reference

### Aptitude Grade Scale

```
SS - Exceptional (best possible)
S  - Excellent
A  - Very Good
B  - Good
C  - Average
D  - Below Average
E  - Poor
F  - Very Poor
G  - Unsuitable (worst)
```

### Aptitude Categories

**Distance/Surface Combinations** (8 total):

- `turf_short` - Short distance on turf
- `turf_mile` - Mile distance on turf
- `turf_medium` - Medium distance on turf
- `turf_long` - Long distance on turf
- `dirt_short` - Short distance on dirt
- `dirt_mile` - Mile distance on dirt
- `dirt_medium` - Medium distance on dirt
- `dirt_long` - Long distance on dirt

**Running Styles** (4 total):

- `runner` - Escape/Runner (逃げ)
- `leader` - Leader/Front-runner (先行)
- `betweener` - Betweener/Stalker (差し)
- `chaser` - Chaser/Closer (追込)

## Data Sources

### 1. gametora.com (Recommended)

**URL**: <https://gametora.com/umamusume>

**Pros**:

- Community-maintained calculator
- Accurate and up-to-date
- Easy to navigate
- English interface available

**How to Use**:

1. Navigate to character database
2. Select character
3. View aptitude table
4. Copy grades for all 12 categories

### 2. GameWith (Japanese)

**URL**: <https://gamewith.jp/uma-musume/>

**Pros**:

- Official data source
- Comprehensive coverage
- Regularly updated

**Cons**:

- Japanese language only
- Requires translation

### 3. Uma Musume Wiki (English)

**URL**: <https://umamusume.fandom.com/>

**Pros**:

- English language
- Community-verified
- Good coverage

**Cons**:

- May have gaps
- Update lag

## Adding Aptitude Data

### Step 1: Locate the Seeder File

```bash
database/seeders/EnhancedRealUmaMusumeCharactersSeeder.php
```

### Step 2: Find the loadAptitudeData() Method

```php
private function loadAptitudeData(): void
{
    $this->aptitudeData = [
        // Existing characters...
        'Special Week' => [...],
        
        // Add new characters here
    ];
}
```

### Step 3: Add Character Data

**Template**:

```php
'Character Name' => [
    // Distance/Surface combinations
    'turf_short' => 'A',
    'turf_mile' => 'A',
    'turf_medium' => 'A',
    'turf_long' => 'B',
    'dirt_short' => 'G',
    'dirt_mile' => 'G',
    'dirt_medium' => 'G',
    'dirt_long' => 'G',
    
    // Running styles
    'runner' => 'G',
    'leader' => 'A',
    'betweener' => 'A',
    'chaser' => 'B',
],
```

**Example - Adding Grass Wonder**:

```php
'Grass Wonder' => [
    'turf_short' => 'G',
    'turf_mile' => 'A',
    'turf_medium' => 'S',
    'turf_long' => 'A',
    'dirt_short' => 'G',
    'dirt_mile' => 'G',
    'dirt_medium' => 'G',
    'dirt_long' => 'G',
    'runner' => 'G',
    'leader' => 'B',
    'betweener' => 'S',
    'chaser' => 'A',
],
```

### Step 4: Verify Character Name

**Important**: Character name must match EXACTLY with the API name

**Check API name**:

```bash
php artisan tinker --execute="
    \$chars = Http::get('https://api.umapyoi.net/api/v1/character/info')->json();
    \$char = collect(\$chars)->firstWhere('name_en', 'Grass Wonder');
    echo \$char['name_en'];
"
```

### Step 5: Test the Addition

```bash
# Delete existing characters
php artisan tinker --execute="DB::table('ucp_characters')->delete(); DB::table('ucp_aptitudes')->delete();"

# Reseed with new data
php artisan db:seed --class=EnhancedRealUmaMusumeCharactersSeeder

# Verify aptitude count increased
php artisan tinker --execute="
    echo 'Characters with aptitudes: ' . DB::table('ucp_characters')
        ->whereIn('id', DB::table('ucp_aptitudes')->select('character_id')->distinct())
        ->count();
"
```

## Batch Addition Workflow

### For Adding Multiple Characters

1. **Create a spreadsheet** with columns:
   - Character Name
   - turf_short, turf_mile, turf_medium, turf_long
   - dirt_short, dirt_mile, dirt_medium, dirt_long
   - runner, leader, betweener, chaser

2. **Collect data** from sources (gametora, GameWith, wiki)

3. **Convert to PHP array** format:

   ```php
   // Use this helper script
   $spreadsheetData = [
       ['Grass Wonder', 'G', 'A', 'S', 'A', 'G', 'G', 'G', 'G', 'G', 'B', 'S', 'A'],
       ['Biwa Hayahide', 'B', 'A', 'S', 'A', 'G', 'G', 'G', 'G', 'G', 'A', 'S', 'A'],
       // ... more characters
   ];
   
   foreach ($spreadsheetData as $row) {
       echo "'{$row[0]}' => [\n";
       echo "    'turf_short' => '{$row[1]}', 'turf_mile' => '{$row[2]}', 'turf_medium' => '{$row[3]}', 'turf_long' => '{$row[4]}',\n";
       echo "    'dirt_short' => '{$row[5]}', 'dirt_mile' => '{$row[6]}', 'dirt_medium' => '{$row[7]}', 'dirt_long' => '{$row[8]}',\n";
       echo "    'runner' => '{$row[9]}', 'leader' => '{$row[10]}', 'betweener' => '{$row[11]}', 'chaser' => '{$row[12]}',\n";
       echo "],\n";
   }
   ```

4. **Paste into seeder** and test

## Common Patterns

### Sprint Specialists

```php
'turf_short' => 'S',
'turf_mile' => 'A',
'turf_medium' => 'B',
'turf_long' => 'G',
'runner' => 'S',
'leader' => 'A',
```

### Long Distance Runners

```php
'turf_short' => 'G',
'turf_mile' => 'B',
'turf_medium' => 'A',
'turf_long' => 'S',
'betweener' => 'A',
'chaser' => 'S',
```

### Dirt Specialists (Rare)

```php
'dirt_short' => 'A',
'dirt_mile' => 'S',
'dirt_medium' => 'A',
'dirt_long' => 'B',
```

### All-Rounders

```php
'turf_short' => 'A',
'turf_mile' => 'A',
'turf_medium' => 'A',
'turf_long' => 'A',
'runner' => 'B',
'leader' => 'A',
'betweener' => 'A',
'chaser' => 'B',
```

## Validation Checklist

Before committing aptitude data:

- [ ] Character name matches API exactly
- [ ] All 12 aptitude values provided
- [ ] Grades are valid (G, F, E, D, C, B, A, S, SS)
- [ ] Data verified from at least 2 sources
- [ ] Tested with seeder
- [ ] Aptitude count increased correctly

## Priority Characters

### High Priority (Popular/Meta)

1. Kitasan Black
2. Satono Diamond
3. Narita Brian
4. Rice Shower
5. Mejiro McQueen
6. Air Groove
7. Symboli Rudolf
8. T.M. Opera O
9. Mejiro Palmer
10. Haru Urara

### Medium Priority (Common)

11-50: Other frequently used characters

### Low Priority (Rare)

51-161: Less common characters

## Estimated Time

- **Per character**: 2-3 minutes (data collection + entry)
- **Batch of 10**: 20-30 minutes
- **Batch of 50**: 2-3 hours
- **All 151 remaining**: 6-8 hours total

## Tips

1. **Work in batches**: Add 10-20 characters at a time
2. **Verify as you go**: Test after each batch
3. **Use multiple sources**: Cross-reference data
4. **Document sources**: Add comments for verification
5. **Commit frequently**: Don't lose progress

## Example Commit Message

```
feat: add aptitude data for 20 characters

- Added official aptitude grades for:
  - Grass Wonder, Biwa Hayahide, King Halo
  - Mejiro McQueen, Symboli Rudolf, Air Groove
  - [... list all 20]
- Data verified from gametora.com and GameWith
- Tested with seeder, all aptitudes created correctly
- Total characters with aptitudes: 30/161 (18.6%)
```

## Need Help?

- Check existing character data for patterns
- Verify character names with API
- Test frequently to catch errors early
- Ask for review before large batches

---

**Document Version**: 1.0  
**Last Updated**: January 26, 2026  
**Related**: [Task 4 Implementation Summary](./TASK-4-ENHANCED-CHARACTER-BASELINE-DATA.md)
