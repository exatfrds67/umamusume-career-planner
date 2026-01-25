# Quick Start: Character Creation with External API

## How to Use the New Feature

### Step 1: Open Character Creation

Navigate to: `http://127.0.0.1:8000/characters/create`

### Step 2: Open External API

Click the green **"Open External API"** button

### Step 3: Search for a Character

Type a character name in the search box (e.g., "Special Week")

The search will automatically trigger after you stop typing (500ms delay)

### Step 4: Select a Character

Click on any character card from the search results

You'll see a preview in the right panel

### Step 5: Load Full Data

Click the **"Load Full Data"** button in the preview panel

This will:

- Fetch complete character data from umapyoi.net
- Auto-fill the character name
- Auto-fill the avatar image
- Auto-fill all base stats (Speed, Stamina, Power, Guts, Wit)
- Auto-fill all aptitudes (Distance, Surface, Running Style)

### Step 6: Review and Complete

1. The form will auto-fill with external data
2. A green success notification will appear
3. The External API panel will close automatically
4. Review the prefilled data (you can still edit it)
5. Select a scenario type (URA Finale or Unity Cup)
6. Click "Next" to proceed through the wizard
7. Review your character on the final step
8. Click "Create Character"

### Step 7: Character Saved

Your character is now saved to the database with:

- All the data from the external API
- A reference to the external source (umapyoi.net)
- The external character ID for future updates

## Features

### Search Filters

- **Query**: Search by character name (English or Japanese)
- **Category**: Filter by Main or Support characters

### What Gets Auto-Filled

- ✅ Character Name (English)
- ✅ Avatar Image URL
- ✅ Speed stat
- ✅ Stamina stat
- ✅ Power stat
- ✅ Guts stat
- ✅ Wit stat (mapped from Wisdom)
- ✅ Sprint aptitude
- ✅ Mile aptitude
- ✅ Medium aptitude
- ✅ Long aptitude
- ✅ Turf aptitude
- ✅ Dirt aptitude
- ✅ Front Runner aptitude
- ✅ Pace Chaser aptitude
- ✅ Late Surger aptitude
- ✅ End Closer aptitude

### What You Still Need to Set

- ⚠️ Scenario Type (URA Finale or Unity Cup)
- ⚠️ Any manual adjustments to stats or aptitudes

## Tips

1. **Search is Smart**: You can search in English or Japanese
2. **Debounced Search**: Wait 500ms after typing for search to trigger
3. **Preview First**: Always preview before loading full data
4. **Edit After Load**: You can still edit any auto-filled data
5. **Two Sources**: You can use either the local database OR external API (not both at once)

## Troubleshooting

### No Results Found

- Check your spelling
- Try searching in English instead of Japanese (or vice versa)
- Try a partial name (e.g., "Special" instead of "Special Week")
- Check the category filter

### Loading Takes Too Long

- Check your internet connection
- The external API might be slow
- Try refreshing the page and searching again

### Error Message Appears

- Check the error message for details
- Try searching again
- If the problem persists, use the local database instead

### Form Doesn't Auto-Fill

- Make sure you clicked "Load Full Data"
- Check the browser console for errors (F12)
- Try selecting a different character

## Example Search Queries

Good searches:

- "Special Week"
- "Silence Suzuka"
- "Tokai Teio"
- "Gold Ship"
- "Special" (partial match)

## What's Next?

After creating a character with external API data:

1. View your character at `/characters/{id}`
2. The character will show the external source badge
3. You can use this character for training, races, and skills
4. The external reference is preserved for future updates

## Support Card Import (Coming Soon)

The same external API integration will be available for support cards at:
`http://127.0.0.1:8000/support-cards`

This will allow you to:

- Browse all support cards from umapyoi.net
- Filter by rarity (SSR/SR/R)
- Import cards to your collection
- Build decks with imported cards
- Use cards in training and races
