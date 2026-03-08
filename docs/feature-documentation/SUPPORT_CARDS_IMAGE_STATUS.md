# Support Card Image Status

**Last Updated**: February 27, 2026  
**Status**: ✅ **522/522 CARDS HAVE ARTWORK URLs (100%)**  
**Total Cards**: 522  
**Cards with Images**: 522 (100%)  
**Cards with Placeholders**: 0 (0%)

---

## Implementation Method (February 27, 2026)

All 522 support cards were populated with artwork URLs using the GameTora CDN pattern:

```text
https://gametora.com/images/umamusume/supports/tex_support_card_{external_source_id}.png
```text

### Artisan Command

A new artisan command was created to populate artwork:

```bash
php artisan support-cards:populate-artwork        # Populate missing artwork
php artisan support-cards:populate-artwork --force  # Re-populate all cards
php artisan support-cards:populate-artwork --dry-run # Preview without saving
```

**Source**: `app/Console/Commands/PopulateSupportCardArtworkCommand.php`

### How It Works

1. Each card has an `external_source_id` synced from Umapyoi
2. The GameTora CDN stores card art at a predictable URL using this ID
3. IDs verified working: `tex_support_card_30022.png` (Kitasan Black), `tex_support_card_10001.png`
4. Future syncs via `ExternalDataService::transformUmapyoiCard()` now auto-populate `artwork_url`

### Tests

6 Pest tests covering the command live at:
`tests/Feature/Feature/Commands/PopulateSupportCardArtworkCommandTest.php` ✅ All passing

---

## Cards with Actual Images ✅

All 522 cards now use CDN-hosted artwork from GameTora. Example URLs:

- `https://gametora.com/images/umamusume/supports/tex_support_card_30022.png`
- `https://gametora.com/images/umamusume/supports/tex_support_card_10001.png`

---

## ~~Cards Still Using Placeholders~~

> **N/A** — All 522 cards have artwork URLs as of February 27, 2026. The old manual file-based method
> (placing PNGs in `public/images/support_cards/`) is superseded by the GameTora CDN approach.

---

## Copyright Notice

All Umamusume: Pretty Derby character designs and card artwork are © Cygames, Inc.
Images are sourced via GameTora CDN for reference/planning purposes only and are not for commercial use.

### S+ Tier (1/5)

1. ⚠️ **Biko Pegasus [Double Carrot Punch!]**
   - Suggested filename: `Biko_Pegasus_Double_Carrot_Punch.png`

### S Tier (4/5)

1. ⚠️ **Rice Shower [Happiness Just around the Bend]**
   - Suggested filename: `Rice_Shower_Happiness_Just_around_the_Bend.png`

2. ⚠️ **Riko Kashimoto [Planned Perfection]**
   - Suggested filename: `Riko_Kashimoto_Planned_Perfection.png`

3. ⚠️ **Sweep Tosho [Lamplit Training of a Witch-to-Be]**
   - Suggested filename: `Sweep_Tosho_Lamplit_Training_of_a_Witch-to-Be.png`

4. ⚠️ **Narita Brian [Two Pieces]**
   - Suggested filename: `Narita_Brian_Two_Pieces.png`

### A Tier (3/5)

1. ⚠️ **Special Week [The Setting Sun and Rising Stars]**
   - Suggested filename: `Special_Week_The_Setting_Sun_and_Rising_Stars.png`

2. ⚠️ **El Condor Pasa [Champion's Passion]**
   - Suggested filename: `El_Condor_Pasa_Champions_Passion.png`

3. ⚠️ **Twin Turbo [Turbo Booooost!]**
   - Suggested filename: `Twin_Turbo_Turbo_Booooost.png`

---

## How to Add More Images

### Step 1: Obtain Images

- Download from official sources (with proper licensing)
- Use Game8.co or other fan wikis (with attribution)
- Create placeholders following the guide

### Step 2: Name Files

Use this naming convention:

```text
{Character_Name}_{Card_Title}.{ext}
```text

Examples:

- `Biko_Pegasus_Double_Carrot_Punch.png`
- `Rice_Shower_Happiness_Just_around_the_Bend.jpg`

### Step 3: Save to Directory

```text
public/images/support_cards/
```

### Step 4: Update Database

#### Option A: Using Tinker

```bash
php artisan tinker --execute="DB::table('ucp_support_cards')->where('internal_id', 'GLOBAL_SC_BIKOPEGASUS_CARROT')->update(['artwork_url' => '/images/support_cards/Biko_Pegasus_Double_Carrot_Punch.png']);"
```text

#### Option B: Using SQL

```sql
UPDATE ucp_support_cards 
SET artwork_url = '/images/support_cards/Biko_Pegasus_Double_Carrot_Punch.png'
WHERE internal_id = 'GLOBAL_SC_BIKOPEGASUS_CARROT';
```

#### Option C: Re-run Seeder

Update `database/seeders/SupportCardSeeder.php` with the new artwork_url, then:

```bash
php artisan db:seed --class=SupportCardSeeder
```text

### Step 5: Verify

```bash
php check_card_images.php
```

Or visit: <http://127.0.0.1:8000/support-cards>

---

## Internal ID Reference

For database updates, use these internal IDs:

| Card Name | Internal ID |
| --------- | ----------- |
| Biko Pegasus [Double Carrot Punch!] | `GLOBAL_SC_BIKOPEGASUS_CARROT` |
| Rice Shower [Happiness Just around the Bend] | `GLOBAL_SC_RICESHOWER_HAPPINESS` |
| Riko Kashimoto [Planned Perfection] | `GLOBAL_SC_RIKOKASHIMOTO_PLANNED` |
| Sweep Tosho [Lamplit Training of a Witch-to-Be] | `GLOBAL_SC_SWEEPTOSHO_LAMPLIT` |
| Narita Brian [Two Pieces] | `GLOBAL_SC_NARITABRIAN_TWOPIECES` |
| Special Week [The Setting Sun and Rising Stars] | `GLOBAL_SC_SPECIALWEEK_SETTING` |
| El Condor Pasa [Champion's Passion] | `GLOBAL_SC_ELCONDORPASA_CHAMPION` |
| Twin Turbo [Turbo Booooost!] | `GLOBAL_SC_TWINTURBO_TURBO` |

---

## Copyright and Licensing Notice

All Umamusume: Pretty Derby character designs and card artwork are © Cygames, Inc.

Images should be:

- Obtained legally (licensed, fair use, or public domain)
- Properly attributed to Cygames
- Used for educational/reference purposes only
- Not for commercial use

See `public/images/support_cards/README.md` for full copyright information.

---

## Progress Tracking

- [x] Kitasan Black [Fire at My Heels]
- [x] Super Creek [Piece of Mind]
- [x] Fine Motion [Wave of Gratitude]
- [x] Tazuna Hayakawa [Tracen Reception]
- [ ] Biko Pegasus [Double Carrot Punch!]
- [ ] Rice Shower [Happiness Just around the Bend]
- [ ] Riko Kashimoto [Planned Perfection]
- [ ] Sweep Tosho [Lamplit Training of a Witch-to-Be]
- [ ] Narita Brian [Two Pieces]
- [x] Silence Suzuka [Beyond This Shining Moment]
- [ ] Special Week [The Setting Sun and Rising Stars]
- [x] Tokai Teio [Dream Big!]
- [ ] El Condor Pasa [Champion's Passion]
- [x] Mejiro McQueen [Your Team Ace]
- [ ] Twin Turbo [Turbo Booooost!]

Progress: 7/15 (47%)

---

## Next Steps

1. **Add remaining 8 images** following the naming convention
2. **Update database** for each new image added
3. **Verify display** at <http://127.0.0.1:8000/support-cards>
4. **Test deck builder** with actual card images
5. **Consider creating placeholders** for missing cards (see `PLACEHOLDER_CREATION_GUIDE.md`)

---

End of Status Report
