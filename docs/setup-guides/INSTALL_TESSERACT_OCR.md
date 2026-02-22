# Tesseract OCR Installation Guide for Windows

**Date:** January 20, 2026  
**System:** Windows with PHP 8.4.11

---

## 📥 Installation Steps

### Step 1: Download Tesseract OCR

**Official Installer:**

- Visit: <https://github.com/UB-Mannheim/tesseract/wiki>
- Or direct download: <https://digi.bib.uni-mannheim.de/tesseract/>

**Recommended Version:**

- Latest stable release (5.x or higher)
- Choose: `tesseract-ocr-w64-setup-X.X.X.XXXXX.exe` (64-bit)

### Step 2: Run the Installer

1. **Run the downloaded .exe file**
   - Right-click → Run as Administrator

2. **Installation Options:**
   - ✅ Install Tesseract OCR
   - ✅ Add to PATH (IMPORTANT!)
   - ✅ Install language data files:
     - **English (eng)** - Required
     - **Japanese (jpn)** - Required for your app
     - **Japanese Vertical (jpn_vert)** - Recommended

3. **Installation Path:**
   - Default: `C:\Program Files\Tesseract-OCR\`
   - Or custom path (remember it for later)

4. **Complete Installation**
   - Click through the installer
   - Wait for completion

### Step 3: Verify Installation

Open a **new** PowerShell window (important - to reload PATH):

```powershell
tesseract --version
```

**Expected Output:**

```
tesseract 5.x.x
 leptonica-1.x.x
  libgif 5.x.x : libjpeg 8d (libjpeg-turbo 2.x.x) : libpng 1.x.x : libtiff 4.x.x : zlib 1.x.x : libwebp 1.x.x
 Found AVX2
 Found AVX
 Found FMA
 Found SSE4.1
 Found OpenMP 201511
```

### Step 4: Verify Language Data

```powershell
tesseract --list-langs
```

**Expected Output:**

```
List of available languages (3):
eng
jpn
jpn_vert
osd
```

**Required Languages:**

- ✅ eng (English)
- ✅ jpn (Japanese)

### Step 5: Update Laravel Configuration

Your `.env` file should already have:

```env
TESSERACT_PATH=tesseract
TESSERACT_LANGUAGE=jpn+eng
TESSERACT_PSM=6
TESSERACT_OEM=3
```

If Tesseract is not in PATH, update to full path:

```env
TESSERACT_PATH=C:\Program Files\Tesseract-OCR\tesseract.exe
```

### Step 6: Test in Laravel

```powershell
php artisan tinker
```

Then run:

```php
$service = app(\App\Services\TesseractService::class);
echo $service->isAvailable() ? 'Available' : 'Not Available';
exit
```

**Expected:** `Available`

### Step 7: Run Tests

```powershell
php artisan test --filter=Tesseract --compact
```

**Expected:** All 11 tests should pass (no skips)

---

## 🔧 Troubleshooting

### Issue 1: "tesseract is not recognized"

**Cause:** Tesseract not in PATH

**Solutions:**

#### Option A: Add to PATH manually

1. Open System Properties → Environment Variables
2. Edit "Path" in System Variables
3. Add: `C:\Program Files\Tesseract-OCR`
4. Click OK
5. **Restart PowerShell** (important!)

#### Option B: Use full path in .env

```env
TESSERACT_PATH=C:\Program Files\Tesseract-OCR\tesseract.exe
```

### Issue 2: "Failed to load language data"

**Cause:** Language files not installed

**Solution:**

1. Re-run installer
2. Select "Additional language data"
3. Check: English (eng) and Japanese (jpn)
4. Complete installation

**Or download manually:**

1. Visit: <https://github.com/tesseract-ocr/tessdata>
2. Download: `eng.traineddata` and `jpn.traineddata`
3. Copy to: `C:\Program Files\Tesseract-OCR\tessdata\`

### Issue 3: Tests still skipping

**Check:**

```powershell
# Verify Tesseract is available
tesseract --version

# Check PHP can find it
php -r "echo shell_exec('tesseract --version');"

# Test Laravel service
php artisan tinker --execute="echo app(\App\Services\TesseractService::class)->isAvailable() ? 'Yes' : 'No';"
```

### Issue 4: Permission errors

**Solution:**

- Run installer as Administrator
- Ensure Tesseract folder has read permissions

---

## 📋 Quick Verification Checklist

After installation, verify:

- [ ] `tesseract --version` works in PowerShell
- [ ] `tesseract --list-langs` shows eng and jpn
- [ ] New PowerShell window (PATH reloaded)
- [ ] Laravel can detect it: `php artisan tinker` → `app(\App\Services\TesseractService::class)->isAvailable()`
- [ ] Tests pass: `php artisan test --filter=Tesseract`

---

## 🎯 Alternative Installation Methods

### Method 1: Chocolatey (Package Manager)

If you have Chocolatey installed:

```powershell
choco install tesseract
```

### Method 2: Scoop (Package Manager)

If you have Scoop installed:

```powershell
scoop install tesseract
```

### Method 3: Manual Installation

1. Download from: <https://github.com/UB-Mannheim/tesseract/wiki>
2. Extract to: `C:\Tesseract-OCR\`
3. Add to PATH manually
4. Download language files to `tessdata` folder

---

## 📚 Language Data Files

### Required for Your App

- **eng.traineddata** - English text recognition
- **jpn.traineddata** - Japanese text recognition

### Optional but Recommended

- **jpn_vert.traineddata** - Vertical Japanese text
- **osd.traineddata** - Orientation and script detection

### Download Location

- Included in installer (recommended)
- Or download from: <https://github.com/tesseract-ocr/tessdata>

---

## 🧪 Test OCR Functionality

### Create a test image

```powershell
# Create a simple test
php artisan tinker
```

```php
// Test with a sample image
$service = app(\App\Services\TesseractService::class);

// Check availability
echo "Available: " . ($service->isAvailable() ? 'Yes' : 'No') . "\n";

// Test with actual image (if you have one)
$file = new \Illuminate\Http\UploadedFile(
    'path/to/test/image.jpg',
    'test.jpg',
    'image/jpeg',
    null,
    true
);

$result = $service->processScreenshot($file, 1);
print_r($result);
```

---

## 📊 Expected Test Results

### Before Tesseract Installation

```
Tests:  11 passed (33 assertions)
- 3 tests skip when Tesseract not available
- 8 tests run (don't require Tesseract)
```

### After Tesseract Installation

```
Tests:  11 passed (44 assertions)
- All 11 tests run
- No skips
- Full OCR functionality tested
```

---

## 🔗 Useful Links

**Official Resources:**

- Tesseract GitHub: <https://github.com/tesseract-ocr/tesseract>
- Windows Installer: <https://github.com/UB-Mannheim/tesseract/wiki>
- Language Data: <https://github.com/tesseract-ocr/tessdata>
- Documentation: <https://tesseract-ocr.github.io/>

**Laravel Integration:**

- Your app uses: `App\Services\TesseractService`
- Enhanced version: `App\Services\TesseractServiceEnhanced`
- Tests: `tests/Unit/Services/TesseractServiceTest.php`

---

## ⚙️ Configuration Reference

### .env Settings

```env
# Tesseract OCR Settings
TESSERACT_PATH=tesseract                    # Or full path
TESSERACT_LANGUAGE=jpn+eng                  # Japanese + English
TESSERACT_PSM=6                             # Page segmentation mode
TESSERACT_OEM=3                             # OCR Engine mode

# Image Processing Settings
OPENCV_ENABLED=true
OPENCV_RESIZE_MAX_WIDTH=1920
OPENCV_RESIZE_MAX_HEIGHT=1080
OPENCV_DENOISE_STRENGTH=10
OPENCV_SHARPEN_ENABLED=true
OPENCV_CONTRAST_ENHANCEMENT=true

# Image Upload Limits
IMAGE_MAX_FILE_SIZE=10485760                # 10MB
IMAGE_MIN_WIDTH=320
IMAGE_MIN_HEIGHT=240
IMAGE_MAX_WIDTH=4096
IMAGE_MAX_HEIGHT=4096
IMAGE_SECURITY_SCAN_ENABLED=true
```

### PSM (Page Segmentation Mode) Values

- 0 = Orientation and script detection (OSD) only
- 1 = Automatic page segmentation with OSD
- 3 = Fully automatic page segmentation, but no OSD
- 6 = Assume a single uniform block of text (default)
- 7 = Treat the image as a single text line
- 11 = Sparse text. Find as much text as possible

### OEM (OCR Engine Mode) Values

- 0 = Legacy engine only
- 1 = Neural nets LSTM engine only
- 2 = Legacy + LSTM engines
- 3 = Default, based on what is available (recommended)

---

## 🚀 Next Steps After Installation

1. **Verify Installation**

   ```powershell
   tesseract --version
   tesseract --list-langs
   ```

2. **Test Laravel Integration**

   ```powershell
   php artisan tinker --execute="echo app(\App\Services\TesseractService::class)->isAvailable() ? 'Available' : 'Not Available';"
   ```

3. **Run Tests**

   ```powershell
   php artisan test --filter=Tesseract --compact
   ```

4. **Test OCR Upload**
   - Upload a screenshot through your app
   - Check OCR extraction results
   - Verify stats are extracted correctly

---

## 📝 Installation Summary

**What to Download:**

- Tesseract OCR installer (64-bit)
- English language data (included)
- Japanese language data (included)

**What to Configure:**

- Add to PATH (during installation)
- Or update .env with full path

**What to Verify:**

- `tesseract --version` works
- Languages available (eng, jpn)
- Laravel can detect it
- Tests pass

**Estimated Time:** 5-10 minutes

---

## ✅ Success Criteria

Installation is complete when:

- [ ] `tesseract --version` shows version info
- [ ] `tesseract --list-langs` shows eng and jpn
- [ ] Laravel detects it: `isAvailable()` returns true
- [ ] All 11 Tesseract tests pass
- [ ] OCR upload functionality works in app

---

**Ready to install!** 🚀

Download from: <https://github.com/UB-Mannheim/tesseract/wiki>

After installation, run:

```powershell
tesseract --version
php artisan test --filter=Tesseract --compact
```
