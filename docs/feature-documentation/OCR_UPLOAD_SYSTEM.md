# OCR Upload System Documentation

## Overview

The OCR Upload System provides secure screenshot upload and management capabilities for the Umamusume Career Planner
application. It implements comprehensive validation, image preprocessing, duplicate detection, and automatic cleanup of
temporary files.

**Task**: 5.1.2 - Create screenshot upload and management system  
**Requirements**: Requirement 23.2

## Features

### 1. Secure File Upload Handling

- **File Type Validation**: Only accepts JPEG, PNG, and WebP images
- **File Size Limits**: Configurable maximum file size (default: 10MB)
- **Dimension Validation**: Enforces minimum (320x240) and maximum (4096x4096) dimensions
- **Security Scanning**: Detects suspicious file signatures and malicious content
- **MIME Type Verification**: Validates actual file content matches declared type

### 2. Image Preprocessing

The system uses the `ImageProcessingService` to optimize images for OCR:

- **Automatic Resizing**: Scales down large images while maintaining aspect ratio
- **Grayscale Conversion**: Improves OCR accuracy by removing color information
- **Contrast Enhancement**: Increases text visibility
- **Sharpening**: Enhances edge definition for better character recognition
- **Noise Reduction**: Applies Gaussian blur to reduce image noise

### 3. Duplicate Detection

- **Image Hashing**: Uses SHA-256 hashing to identify duplicate uploads
- **Cached Results**: Returns previously processed results for duplicate images
- **Database Lookup**: Checks for existing extractions before processing
- **Storage Optimization**: Prevents redundant storage of identical images

### 4. Temporary File Management

- **Automatic Cleanup**: Scheduled daily cleanup of old extraction files
- **Configurable Retention**: Default 7-day retention period (configurable)
- **Orphaned File Detection**: Identifies and removes files without database records
- **Space Monitoring**: Tracks and reports freed storage space

## API Endpoints

### POST /api/ocr/upload

Upload and process a screenshot for OCR extraction.

**Authentication**: Required (Sanctum)

**Request Parameters**:

```json
{
  "screenshot": "file (required, image/jpeg|png|webp, max 10MB)",
  "character_id": "integer (optional, must exist in database)",
  "data_type": "string (optional, enum: character_stats|training_session|race_result|skill_list|support_cards)"
}
```text

**Success Response** (200):

```json
{
  "success": true,
  "message": "Screenshot processed successfully",
  "data": {
    "extraction_id": 123,
    "stats": {
      "speed": 850,
      "stamina": 720,
      "power": 680,
      "guts": 450,
      "wit": 590
    },
    "confidence": 0.85,
    "raw_text": "スピード: 850\nスタミナ: 720..."
  }
}
```text

**Error Response** (422):

```json
{
  "success": false,
  "message": "Image validation failed",
  "error": "File size exceeds maximum allowed size of 10 MB"
}
```text

**Error Response** (500):

```json
{
  "success": false,
  "message": "OCR processing failed",
  "error": "Tesseract execution failed"
}
```

### GET /api/ocr/status

Get OCR system status and configuration.

**Authentication**: Required (Sanctum)

**Success Response** (200):

```json
{
  "success": true,
  "data": {
    "ocr_available": true,
    "image_processing_available": true,
    "max_file_size": 10485760,
    "max_file_size_mb": 10,
    "allowed_formats": ["jpg", "jpeg", "png", "webp"],
    "min_dimensions": {
      "width": 320,
      "height": 240
    },
    "max_dimensions": {
      "width": 4096,
      "height": 4096
    }
  }
}
```text

## Configuration

All configuration is stored in `config/services.php`:

### Image Processing Settings

```php
'image_processing' => [
    // File upload limits
    'max_file_size' => env('IMAGE_MAX_FILE_SIZE', 10485760), // 10MB
    'allowed_formats' => ['jpg', 'jpeg', 'png', 'webp'],
    'allowed_mimes' => ['image/jpeg', 'image/png', 'image/webp'],

    // Image dimension constraints
    'min_width' => env('IMAGE_MIN_WIDTH', 320),
    'min_height' => env('IMAGE_MIN_HEIGHT', 240),
    'max_width' => env('IMAGE_MAX_WIDTH', 4096),
    'max_height' => env('IMAGE_MAX_HEIGHT', 4096),

    // Security settings
    'security_scan_enabled' => env('IMAGE_SECURITY_SCAN', true),

    // Temporary file cleanup
    'temp_file_ttl' => env('IMAGE_TEMP_TTL', 3600), // 1 hour
    'cleanup_enabled' => env('IMAGE_CLEANUP_ENABLED', true),
],
```text

### OpenCV Preprocessing Settings

```php
'opencv' => [
    'preprocessing' => [
        'resize_max_width' => env('OPENCV_RESIZE_MAX_WIDTH', 1920),
        'resize_max_height' => env('OPENCV_RESIZE_MAX_HEIGHT', 1080),
        'contrast_enhancement' => env('OPENCV_CONTRAST_ENHANCEMENT', true),
        'sharpen_enabled' => env('OPENCV_SHARPEN_ENABLED', true),
        'denoise_strength' => env('OPENCV_DENOISE_STRENGTH', 10),
    ],
],
```text

## Environment Variables

Add these to your `.env` file to customize behavior:

```env
# Image Upload Configuration
IMAGE_MAX_FILE_SIZE=10485760
IMAGE_MIN_WIDTH=320
IMAGE_MIN_HEIGHT=240
IMAGE_MAX_WIDTH=4096
IMAGE_MAX_HEIGHT=4096
IMAGE_SECURITY_SCAN=true
IMAGE_TEMP_TTL=3600
IMAGE_CLEANUP_ENABLED=true

# OpenCV Preprocessing
OPENCV_RESIZE_MAX_WIDTH=1920
OPENCV_RESIZE_MAX_HEIGHT=1080
OPENCV_CONTRAST_ENHANCEMENT=true
OPENCV_SHARPEN_ENABLED=true
OPENCV_DENOISE_STRENGTH=10
```

## Artisan Commands

### ocr:cleanup

Clean up old OCR extraction files and temporary images.

**Usage**:

```bash
# Clean up files older than 7 days (default)
php artisan ocr:cleanup

# Clean up files older than 30 days
php artisan ocr:cleanup --days=30

# Dry run (show what would be deleted without deleting)
php artisan ocr:cleanup --dry-run
```text

**Options**:

- `--days=N`: Number of days to keep files (default: 7)
- `--dry-run`: Preview deletions without actually deleting files

**Scheduled Execution**:
The cleanup command runs automatically daily at 2:00 AM via Laravel's task scheduler.

## Database Schema

### ucp_ocr_extractions Table

```sql
CREATE TABLE ucp_ocr_extractions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    image_hash VARCHAR(255) NOT NULL,
    extracted_text TEXT NULL,
    parsed_data JSON NULL,
    confidence_score DECIMAL(5,2) NULL,
    data_type VARCHAR(255) NOT NULL,
    status VARCHAR(255) DEFAULT 'pending',
    processed_at TIMESTAMP NULL,
    error_message TEXT NULL,
    processing_metadata JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_image_hash (image_hash),
    INDEX idx_user_status (user_id, status),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```text

## Security Features

### 1. File Validation

- **MIME Type Checking**: Validates file content matches declared type
- **Extension Validation**: Ensures file extension matches allowed formats
- **Magic Byte Detection**: Checks file signatures for suspicious content

### 2. Security Scanning

The system scans uploaded files for:

- PHP code injection attempts
- JavaScript/script tags
- Executable file signatures (Windows/Linux)
- ZIP archives (potential PHP in ZIP attacks)

### 3. Authentication & Authorization

- All endpoints require Sanctum authentication
- Users can only access their own extraction records
- Rate limiting prevents abuse (60 requests per minute)

## Error Handling

The system provides comprehensive error handling:

1. **Validation Errors** (422): Clear messages for invalid inputs
2. **Processing Errors** (500): Graceful handling of OCR failures
3. **Security Errors** (422): Rejection of suspicious files
4. **Authentication Errors** (401): Proper authentication requirements

## Testing

Comprehensive test suite covers:

- ✅ Authentication requirements
- ✅ File validation (type, size, dimensions)
- ✅ Character ID validation
- ✅ Data type validation
- ✅ Successful upload processing
- ✅ Duplicate detection
- ✅ Status endpoint
- ✅ Security scanning
- ✅ Error handling
- ✅ Integration testing

Run tests:

```bash
php artisan test --filter=OCRUploadTest
```text

## Performance Considerations

### 1. Image Preprocessing

- Preprocessing is performed asynchronously
- Temporary processed files are cleaned up after OCR
- Large images are automatically resized to reduce processing time

### 2. Duplicate Detection

- SHA-256 hashing provides fast duplicate detection
- Database indexes optimize hash lookups
- Cached results eliminate redundant OCR processing

### 3. Storage Management

- Automatic cleanup prevents storage bloat
- Configurable retention periods
- Orphaned file detection and removal

## Integration with OCR System

The upload system integrates seamlessly with:

1. **TesseractService**: Handles OCR text extraction
2. **ImageProcessingService**: Provides preprocessing capabilities
3. **OCRExtraction Model**: Manages database records
4. **Task Scheduler**: Automates cleanup operations

## Future Enhancements

Potential improvements for future iterations:

1. **Batch Upload**: Support multiple file uploads
2. **Progress Tracking**: Real-time upload progress indicators
3. **Image Cropping**: Allow users to crop regions of interest
4. **Format Conversion**: Automatic conversion of unsupported formats
5. **Cloud Storage**: Integration with S3 or similar services
6. **Advanced Preprocessing**: OpenCV integration for enhanced preprocessing

## Troubleshooting

### Common Issues

**Issue**: "File size exceeds maximum allowed size"

- **Solution**: Increase `IMAGE_MAX_FILE_SIZE` in `.env` or compress the image

**Issue**: "Image dimensions are invalid"

- **Solution**: Ensure image meets minimum dimensions (320x240)

**Issue**: "File failed security scan"

- **Solution**: Verify the file is a genuine image, not a disguised executable

**Issue**: "OCR processing failed"

- **Solution**: Check Tesseract installation and language packs

### Debug Mode

Enable debug mode in `.env` to see detailed error messages:

```env
APP_DEBUG=true
```

## Support

For issues or questions:

1. Check the test suite for usage examples
2. Review error logs in `storage/logs/laravel.log`
3. Verify configuration in `config/services.php`
4. Ensure Tesseract and GD library are properly installed
