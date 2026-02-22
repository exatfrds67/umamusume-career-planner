# OCR Infrastructure Installation Guide

This guide covers the installation and configuration of Tesseract OCR with Japanese language support for the Umamusume Career Planner application.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Tesseract Installation](#tesseract-installation)
3. [Japanese Language Data](#japanese-language-data)
4. [Configuration](#configuration)
5. [Verification](#verification)
6. [Troubleshooting](#troubleshooting)

## Prerequisites

- PHP 8.1+ with GD extension enabled
- Windows 10/11 or WSL2 (Ubuntu 20.04+)
- Composer installed
- Laravel 12 application set up

## Tesseract Installation

### Windows Installation

1. **Download Tesseract Installer**
   - Visit: <https://github.com/UB-Mannheim/tesseract/wiki>
   - Download the latest Windows installer (e.g., `tesseract-ocr-w64-setup-5.3.3.exe`)

2. **Run Installer**

   ```
   - Run the downloaded installer
   - Choose installation directory (default: C:\Program Files\Tesseract-OCR)
   - **IMPORTANT**: Select "Additional language data" during installation
   - Check "Japanese" in the language list
   - Complete installation
   ```

3. **Add to System PATH**

   ```
   - Open System Properties > Environment Variables
   - Edit "Path" variable
   - Add: C:\Program Files\Tesseract-OCR
   - Click OK to save
   ```

4. **Verify Installation**

   ```powershell
   tesseract --version
   ```

### WSL/Linux Installation

1. **Update Package List**

   ```bash
   sudo apt update
   ```

2. **Install Tesseract**

   ```bash
   sudo apt install tesseract-ocr
   ```

3. **Install Japanese Language Pack**

   ```bash
   sudo apt install tesseract-ocr-jpn
   ```

4. **Verify Installation**

   ```bash
   tesseract --version
   tesseract --list-langs
   ```

### macOS Installation

1. **Install via Homebrew**

   ```bash
   brew install tesseract
   ```

2. **Install Japanese Language Data**

   ```bash
   brew install tesseract-lang
   ```

3. **Verify Installation**

   ```bash
   tesseract --version
   tesseract --list-langs
   ```

## Japanese Language Data

### Manual Installation (if not included)

1. **Download Japanese Trained Data**
   - Visit: <https://github.com/tesseract-ocr/tessdata>
   - Download `jpn.traineddata`
   - Optionally download `jpn_vert.traineddata` for vertical text

2. **Install Language Files**

   **Windows:**

   ```
   Copy jpn.traineddata to:
   C:\Program Files\Tesseract-OCR\tessdata\
   ```

   **Linux/WSL:**

   ```bash
   sudo cp jpn.traineddata /usr/share/tesseract-ocr/5/tessdata/
   ```

   **macOS:**

   ```bash
   cp jpn.traineddata /usr/local/share/tessdata/
   ```

3. **Verify Language Installation**

   ```bash
   tesseract --list-langs
   ```

   Should show:

   ```
   List of available languages (3):
   eng
   jpn
   osd
   ```

## Configuration

### Environment Variables

Add the following to your `.env` file:

```env
# Tesseract OCR Configuration
TESSERACT_PATH=tesseract
TESSERACT_LANGUAGE=jpn+eng
TESSERACT_PSM=6
TESSERACT_OEM=3

# Image Processing Configuration
OPENCV_ENABLED=true
OPENCV_RESIZE_MAX_WIDTH=1920
OPENCV_RESIZE_MAX_HEIGHT=1080
OPENCV_DENOISE_STRENGTH=10
OPENCV_SHARPEN_ENABLED=true
OPENCV_CONTRAST_ENHANCEMENT=true

# Image Upload Limits
IMAGE_MAX_FILE_SIZE=10485760
IMAGE_MIN_WIDTH=320
IMAGE_MIN_HEIGHT=240
IMAGE_MAX_WIDTH=4096
IMAGE_MAX_HEIGHT=4096
IMAGE_SECURITY_SCAN_ENABLED=true
```

### Windows-Specific Configuration

If Tesseract is not in PATH, specify the full path:

```env
TESSERACT_PATH="C:\Program Files\Tesseract-OCR\tesseract.exe"
```

### WSL-Specific Configuration

For WSL, use the Linux path:

```env
TESSERACT_PATH=/usr/bin/tesseract
```

## Verification

### Test Tesseract Installation

1. **Create Test Image**
   Create a simple image with Japanese text or use a game screenshot.

2. **Run Tesseract Manually**

   ```bash
   tesseract test_image.png stdout -l jpn+eng
   ```

3. **Test via Laravel**

   ```bash
   php artisan tinker
   ```

   ```php
   $service = app(\App\Services\TesseractService::class);
   $available = $service->isAvailable();
   var_dump($available); // Should return true
   ```

### Test Image Processing

```bash
php artisan tinker
```

```php
$imageService = app(\App\Services\ImageProcessingService::class);
$available = $imageService->isAvailable();
var_dump($available); // Should return true
```

### Run Unit Tests

```bash
php artisan test --filter=ImageProcessingServiceTest
php artisan test --filter=TesseractServiceTest
```

## Troubleshooting

### Common Issues

#### 1. "Tesseract not found" Error

**Solution:**

- Verify Tesseract is in system PATH
- Use full path in `.env` file
- Restart terminal/IDE after PATH changes

#### 2. "Language 'jpn' not found" Error

**Solution:**

- Verify Japanese language data is installed
- Check tessdata directory location
- Reinstall language pack

#### 3. GD Library Not Found

**Solution:**

- Enable GD extension in `php.ini`:

  ```ini
  extension=gd
  ```

- Restart web server/PHP-FPM

#### 4. Permission Denied on Linux/WSL

**Solution:**

```bash
sudo chmod +x /usr/bin/tesseract
sudo chmod -R 755 /usr/share/tesseract-ocr/
```

#### 5. Poor OCR Accuracy

**Solutions:**

- Ensure image quality is good (min 800x600 recommended)
- Use screenshots at native resolution
- Enable image preprocessing in config
- Adjust PSM (Page Segmentation Mode) value:
  - PSM 3: Fully automatic page segmentation
  - PSM 6: Assume a single uniform block of text (default)
  - PSM 11: Sparse text. Find as much text as possible

### Testing OCR Accuracy

Create a test script to verify OCR accuracy:

```php
// tests/Manual/TestOCR.php
<?php

require __DIR__.'/../../vendor/autoload.php';

$app = require_once __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$imageService = app(\App\Services\ImageProcessingService::class);
$tesseractService = app(\App\Services\TesseractService::class);

// Test with a sample image
$imagePath = __DIR__.'/sample_screenshot.png';

if (!file_exists($imagePath)) {
    echo "Please place a sample screenshot at: {$imagePath}\n";
    exit(1);
}

echo "Testing OCR on: {$imagePath}\n\n";

// Preprocess
$preprocessResult = $imageService->preprocessForOCR($imagePath);
echo "Preprocessing: " . ($preprocessResult['success'] ? 'SUCCESS' : 'FAILED') . "\n";

if ($preprocessResult['success']) {
    echo "Processed image: {$preprocessResult['processed_path']}\n\n";
}

// Run OCR
$reflection = new ReflectionClass($tesseractService);
$method = $reflection->getMethod('performOCR');
$method->setAccessible(true);

$ocrPath = $preprocessResult['success'] ? $preprocessResult['processed_path'] : $imagePath;
$text = $method->invoke($tesseractService, $ocrPath);

echo "OCR Output:\n";
echo "---\n";
echo $text;
echo "\n---\n\n";

// Extract stats
$extractMethod = $reflection->getMethod('extractStats');
$extractMethod->setAccessible(true);
$stats = $extractMethod->invoke($tesseractService, $text);

echo "Extracted Stats:\n";
print_r($stats);
```

Run with:

```bash
php tests/Manual/TestOCR.php
```

## Performance Optimization

### Recommended Settings for Game Screenshots

```env
# Optimal for Umamusume screenshots
TESSERACT_PSM=6
TESSERACT_OEM=3
OPENCV_RESIZE_MAX_WIDTH=1920
OPENCV_RESIZE_MAX_HEIGHT=1080
OPENCV_DENOISE_STRENGTH=5
OPENCV_SHARPEN_ENABLED=true
OPENCV_CONTRAST_ENHANCEMENT=true
```

### PSM Modes Reference

- **0**: Orientation and script detection (OSD) only
- **1**: Automatic page segmentation with OSD
- **3**: Fully automatic page segmentation, but no OSD
- **4**: Assume a single column of text of variable sizes
- **6**: Assume a single uniform block of text (default)
- **7**: Treat the image as a single text line
- **11**: Sparse text. Find as much text as possible in no particular order
- **13**: Raw line. Treat the image as a single text line

### OEM Modes Reference

- **0**: Legacy engine only
- **1**: Neural nets LSTM engine only
- **2**: Legacy + LSTM engines
- **3**: Default, based on what is available (recommended)

## Additional Resources

- [Tesseract Documentation](https://tesseract-ocr.github.io/)
- [Tesseract GitHub](https://github.com/tesseract-ocr/tesseract)
- [Japanese Language Data](https://github.com/tesseract-ocr/tessdata)
- [Improving OCR Accuracy](https://tesseract-ocr.github.io/tessdoc/ImproveQuality.html)

## Support

For issues specific to this application:

1. Check the troubleshooting section above
2. Review application logs in `storage/logs/laravel.log`
3. Run diagnostic tests with `php artisan test`
4. Check Tesseract logs for detailed error messages

For Tesseract-specific issues:

- Visit the [Tesseract Issues](https://github.com/tesseract-ocr/tesseract/issues) page
- Check the [Tesseract FAQ](https://tesseract-ocr.github.io/tessdoc/FAQ.html)
