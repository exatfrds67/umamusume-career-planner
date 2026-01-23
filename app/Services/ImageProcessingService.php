<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

/**
 * Image Processing Service
 *
 * Handles image preprocessing and enhancement for OCR optimization.
 * Uses GD library (built-in PHP) for image manipulation.
 * OpenCV integration can be added later if needed for advanced processing.
 *
 * Requirements: Task 5.1.1
 */
class ImageProcessingService
{
    /**
     * Allowed image MIME types
     */
    protected const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    /**
     * Suspicious file signatures (magic bytes) to detect
     */
    protected const SUSPICIOUS_SIGNATURES = [
        '<?php',           // PHP code
        '<script',         // JavaScript
        'MZ',              // Windows executable
        '\x7fELF',         // Linux executable
        'PK\x03\x04',      // ZIP archive (potential PHP in ZIP)
    ];

    public function __construct()
    {
        // Ensure GD library is available
        if (! extension_loaded('gd')) {
            throw new \RuntimeException('GD library is not installed. Required for image processing.');
        }
    }

    /**
     * Validate uploaded image file
     *
     * @return array{valid: bool, error: string|null}
     */
    public function validateImage(): array
        // Check file size
        /** @var int $maxSize */
        $maxSize = config('services.image_processing.max_file_size', 10485760);
        $maxSize = \is_int($maxSize) ? $maxSize : 10485760;
        if ($file->getSize() > $maxSize) {
            return [
                'valid' => false,
                'error' => \sprintf('File size exceeds maximum allowed size of %s MB', $maxSize / 1048576),
            ];
        }

        // Check MIME type
        $mimeType = $file->getMimeType();
        if (! \in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            return [
                'valid' => false,
                'error' => \sprintf('Invalid file type: %s. Allowed types: %s', $mimeType, implode(', ', self::ALLOWED_MIME_TYPES)),
            ];
        }

        // Check file extension
        /** @var array<int, string> $allowedExtensions */
        $allowedExtensions = config('services.image_processing.allowed_formats', ['jpg', 'jpeg', 'png', 'webp']);
        $allowedExtensions = \is_array($allowedExtensions) ? $allowedExtensions : ['jpg', 'jpeg', 'png', 'webp'];
        $extension = strtolower($file->getClientOriginalExtension());
        if (! \in_array($extension, $allowedExtensions, true)) {
            return [
                'valid' => false,
                'error' => \sprintf('Invalid file extension: %s. Allowed extensions: %s', $extension, implode(', ', $allowedExtensions)),
            ];
        }

        // Security scan if enabled
        /** @var bool $securityScanEnabled */
        $securityScanEnabled = config('services.image_processing.security_scan_enabled', true);
        $securityScanEnabled = \is_bool($securityScanEnabled) ? $securityScanEnabled : true;
        if ($securityScanEnabled) {
            $securityCheck = $this->performSecurityScan($file);
            if (! $securityCheck['safe']) {
                return [
                    'valid' => false,
                    'error' => $securityCheck['reason'] ?? 'File failed security scan',
                ];
            }
        }

        // Validate image dimensions
        try {
            $imageInfo = getimagesize($file->getRealPath());
            if ($imageInfo === false) {
                return [
                    'valid' => false,
                    'error' => 'Unable to read image dimensions. File may be corrupted.',
                ];
            }

            [$width, $height] = $imageInfo;

            /** @var int $minWidth */
            $minWidth = config('services.image_processing.min_width', 320);
            $minWidth = \is_int($minWidth) ? $minWidth : 320;
            /** @var int $minHeight */
            $minHeight = config('services.image_processing.min_height', 240);
            $minHeight = \is_int($minHeight) ? $minHeight : 240;
            /** @var int $maxWidth */
            $maxWidth = config('services.image_processing.max_width', 4096);
            $maxWidth = \is_int($maxWidth) ? $maxWidth : 4096;
            /** @var int $maxHeight */
            $maxHeight = config('services.image_processing.max_height', 4096);
            $maxHeight = \is_int($maxHeight) ? $maxHeight : 4096;

            if ($width < $minWidth || $height < $minHeight) {
                return [
                    'valid' => false,
                    'error' => \sprintf('Image dimensions too small. Minimum: %dx%d, Got: %dx%d', $minWidth, $minHeight, $width, $height),
                ];
            }

            if ($width > $maxWidth || $height > $maxHeight) {
                return [
                    'valid' => false,
                    'error' => \sprintf('Image dimensions too large. Maximum: %dx%d, Got: %dx%d', $maxWidth, $maxHeight, $width, $height),
                ];
            }
        } catch (\Exception $e) {
            Log::error('[ImageProcessingService] Failed to validate image dimensions', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
            ]);

            return [
                'valid' => false,
                'error' => 'Failed to validate image: '.$e->getMessage(),
            ];
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Perform security scan on uploaded file
     *
     * @return array{safe: bool, reason: string|null}
     */
    protected function performSecurityScan(): array
        try {
            // Read first 1KB of file for signature detection
            $handle = fopen($file->getRealPath(), 'rb');
            if ($handle === false) {
                return ['safe' => false, 'reason' => 'Unable to read file for security scan'];
            }

            $header = fread($handle, 1024);
            fclose($handle);

            if ($header === false) {
                return ['safe' => false, 'reason' => 'Unable to read file header'];
            }

            // Check for suspicious signatures
            foreach (self::SUSPICIOUS_SIGNATURES as $signature) {
                if (str_contains($header, $signature)) {
                    Log::warning('[ImageProcessingService] Suspicious file signature detected', [
                        'signature' => $signature,
                        'file' => $file->getClientOriginalName(),
                    ]);

                    return ['safe' => false, 'reason' => 'File contains suspicious content'];
                }
            }

            // Verify it's actually an image by trying to load it
            $imageInfo = getimagesize($file->getRealPath());
            if ($imageInfo === false) {
                return ['safe' => false, 'reason' => 'File is not a valid image'];
            }

            return ['safe' => true, 'reason' => null];
        } catch (\Exception $e) {
            Log::error('[ImageProcessingService] Security scan failed', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
            ]);

            return ['safe' => false, 'reason' => 'Security scan failed: '.$e->getMessage()];
        }
    }

    /**
     * Preprocess image for OCR optimization
     *
     * @param  string  $imagePath  Path to image file
     * @return array{success: bool, processed_path: string|null, error: string|null}
     */
    public function preprocessForOCR(): array
        try {
            // Load image
            $image = $this->loadImage($imagePath);
            if ($image === null) {
                return [
                    'success' => false,
                    'processed_path' => null,
                    'error' => 'Failed to load image',
                ];
            }

            /** @var \GdImage $image */

            // Get current dimensions
            $width = imagesx($image);
            $height = imagesy($image);

            // Resize if needed
            /** @var int $maxWidth */
            $maxWidth = config('services.opencv.preprocessing.resize_max_width', 1920);
            $maxWidth = \is_int($maxWidth) ? $maxWidth : 1920;
            /** @var int $maxHeight */
            $maxHeight = config('services.opencv.preprocessing.resize_max_height', 1080);
            $maxHeight = \is_int($maxHeight) ? $maxHeight : 1080;

            if ($width > $maxWidth || $height > $maxHeight) {
                $image = $this->resizeImage($image, $maxWidth, $maxHeight);
            }

            // Convert to grayscale for better OCR
            imagefilter($image, IMG_FILTER_GRAYSCALE);

            // Enhance contrast if enabled
            /** @var bool $contrastEnhancement */
            $contrastEnhancement = config('services.opencv.preprocessing.contrast_enhancement', true);
            $contrastEnhancement = \is_bool($contrastEnhancement) ? $contrastEnhancement : true;
            if ($contrastEnhancement) {
                imagefilter($image, IMG_FILTER_CONTRAST, -20);
            }

            // Sharpen if enabled
            /** @var bool $sharpenEnabled */
            $sharpenEnabled = config('services.opencv.preprocessing.sharpen_enabled', true);
            $sharpenEnabled = \is_bool($sharpenEnabled) ? $sharpenEnabled : true;
            if ($sharpenEnabled) {
                $this->sharpenImage($image);
            }

            // Reduce noise
            $this->reduceNoise($image);

            // Save processed image
            $processedPath = $this->saveProcessedImage($image, $imagePath);
            imagedestroy($image);

            if ($processedPath === null) {
                return [
                    'success' => false,
                    'processed_path' => null,
                    'error' => 'Failed to save processed image',
                ];
            }

            return [
                'success' => true,
                'processed_path' => $processedPath,
                'error' => null,
            ];
        } catch (\Exception $e) {
            Log::error('[ImageProcessingService] Image preprocessing failed', [
                'error' => $e->getMessage(),
                'image_path' => $imagePath,
            ]);

            return [
                'success' => false,
                'processed_path' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Load image from file
     */
    protected function loadImage(string $path): ?\GdImage
    {
        $imageInfo = getimagesize($path);
        if ($imageInfo === false) {
            return null;
        }

        $mimeType = $imageInfo['mime'];

        $image = match ($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/webp' => imagecreatefromwebp($path),
            default => null,
        };

        return $image instanceof \GdImage ? $image : null;
    }

    /**
     * Resize image maintaining aspect ratio
     */
    protected function resizeImage(\GdImage $image, int $maxWidth, int $maxHeight): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);

        // Calculate new dimensions maintaining aspect ratio
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = (int) ($width * $ratio);
        $newHeight = (int) ($height * $ratio);

        // Ensure dimensions are at least 1
        $newWidth = max(1, $newWidth);
        $newHeight = max(1, $newHeight);

        // Create new image
        $resized = imagecreatetruecolor($newWidth, $newHeight);
        if (! $resized instanceof \GdImage) {
            return $image;
        }

        // Preserve transparency for PNG
        imagealphablending($resized, false);
        imagesavealpha($resized, true);

        // Resize
        imagecopyresampled(
            $resized,
            $image,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $width,
            $height
        );

        imagedestroy($image);

        return $resized;
    }

    /**
     * Sharpen image using convolution matrix
     */
    protected function sharpenImage(\GdImage $image): void
    {
        $sharpenMatrix = [
            [-1, -1, -1],
            [-1, 16, -1],
            [-1, -1, -1],
        ];

        $divisor = 8;
        $offset = 0;

        imageconvolution($image, $sharpenMatrix, $divisor, $offset);
    }

    /**
     * Reduce noise using smoothing
     */
    protected function reduceNoise(\GdImage $image): void
    {
        // Apply Gaussian blur for noise reduction
        /** @var int $strength */
        $strength = config('services.opencv.preprocessing.denoise_strength', 10);
        $strength = \is_int($strength) ? $strength : 10;

        // Light smoothing
        if ($strength > 0) {
            imagefilter($image, IMG_FILTER_SMOOTH, min($strength, 10));
        }
    }

    /**
     * Save processed image
     *
     * @param  \GdImage  $image  GD image resource
     * @return string|null Path to saved image
     */
    protected function saveProcessedImage(\GdImage $image, string $originalPath): ?string
    {
        try {
            // Generate processed filename
            $pathInfo = pathinfo($originalPath);
            $extension = $pathInfo['extension'] ?? 'jpg';
            $dirname = $pathInfo['dirname'] ?? dirname($originalPath);
            $processedFilename = $pathInfo['filename'].'_processed.'.$extension;
            $processedPath = $dirname.'/'.$processedFilename;

            // Save based on extension
            $success = match (strtolower($extension)) {
                'jpg', 'jpeg' => imagejpeg($image, $processedPath, 95),
                'png' => imagepng($image, $processedPath, 9),
                'webp' => imagewebp($image, $processedPath, 95),
                default => false,
            };

            return $success ? $processedPath : null;
        } catch (\Exception $e) {
            Log::error('[ImageProcessingService] Failed to save processed image', [
                'error' => $e->getMessage(),
                'original_path' => $originalPath,
            ]);

            return null;
        }
    }

    /**
     * Calculate image hash for duplicate detection
     */
    public function calculateImageHash(string $imagePath): string
    {
        $hash = hash_file('sha256', $imagePath);
        if ($hash === false) {
            throw new \RuntimeException('Failed to calculate image hash');
        }

        return $hash;
    }

    /**
     * Check if image processing is available
     */
    public function isAvailable(): bool
    {
        return extension_loaded('gd');
    }
}
