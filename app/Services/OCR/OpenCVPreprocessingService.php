<?php

declare(strict_types=1);

namespace App\Services\OCR;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

/**
 * OpenCV Preprocessing Service
 *
 * Advanced image preprocessing pipeline using OpenCV via Python bridge.
 * Falls back to GD-based preprocessing when Python/OpenCV is unavailable.
 *
 * Pipeline steps:
 * 1. Resize (maintain aspect ratio)
 * 2. Deskew (Hough line transform)
 * 3. Noise reduction (Non-Local Means)
 * 4. CLAHE contrast enhancement
 * 5. Sharpening (unsharp mask)
 * 6. Grayscale conversion
 * 7. Adaptive thresholding (binarization)
 *
 * @see \App\Services\ImageProcessingService  GD-based fallback
 */
class OpenCVPreprocessingService
{
    private string $pythonBinary;

    private string $scriptPath;

    public function __construct()
    {
        /** @var string $pythonBinary */
        $pythonBinary = config('services.opencv.python_binary', 'python');
        $this->pythonBinary = is_string($pythonBinary) ? $pythonBinary : 'python';

        $this->scriptPath = base_path('scripts/ocr_preprocess.py');
    }

    /**
     * Check if OpenCV preprocessing is available.
     *
     * @return array{available: bool, engine: string, details: string}
     */
    public function checkAvailability(): array
    {
        if (! file_exists($this->scriptPath)) {
            return [
                'available' => false,
                'engine' => 'none',
                'details' => 'Preprocessing script not found at: '.$this->scriptPath,
            ];
        }

        try {
            $result = Process::timeout(10)
                ->run([$this->pythonBinary, '-c', 'import cv2; print(cv2.__version__)']);

            if ($result->successful()) {
                return [
                    'available' => true,
                    'engine' => 'opencv',
                    'details' => 'OpenCV '.trim($result->output()).' via Python bridge',
                ];
            }
        } catch (\Throwable $e) {
            Log::debug('[OpenCV] Availability check failed', ['error' => $e->getMessage()]);
        }

        if (extension_loaded('gd')) {
            return [
                'available' => true,
                'engine' => 'gd',
                'details' => 'Falling back to GD library (OpenCV not available)',
            ];
        }

        return [
            'available' => false,
            'engine' => 'none',
            'details' => 'Neither OpenCV nor GD is available',
        ];
    }

    /**
     * Preprocess an image for OCR using the best available engine.
     *
     * @param  string  $inputPath  Absolute path to the input image
     * @param  string|null  $outputPath  Optional output path (auto-generated if null)
     * @return array{success: bool, processed_path: string|null, engine: string, steps: array<string>, processing_time_ms: float, error: string|null}
     */
    public function preprocess(string $inputPath, ?string $outputPath = null): array
    {
        if (! file_exists($inputPath)) {
            return $this->errorResult('Input file not found: '.$inputPath);
        }

        $outputPath ??= $this->generateOutputPath($inputPath);

        $availability = $this->checkAvailability();

        if ($availability['engine'] === 'opencv') {
            return $this->preprocessWithOpenCV($inputPath, $outputPath);
        }

        if ($availability['engine'] === 'gd') {
            return $this->preprocessWithGD($inputPath, $outputPath);
        }

        return $this->errorResult('No preprocessing engine available');
    }

    /**
     * Preprocess using OpenCV via Python bridge.
     *
     * @return array{success: bool, processed_path: string|null, engine: string, steps: array<string>, processing_time_ms: float, error: string|null}
     */
    protected function preprocessWithOpenCV(string $inputPath, string $outputPath): array
    {
        $config = $this->getPreprocessingConfig();

        try {
            $result = Process::timeout(30)
                ->run([
                    $this->pythonBinary,
                    $this->scriptPath,
                    $inputPath,
                    $outputPath,
                    '--config',
                    json_encode($config, JSON_THROW_ON_ERROR),
                ]);

            if (! $result->successful()) {
                Log::warning('[OpenCV] Python preprocessing failed, falling back to GD', [
                    'error' => $result->errorOutput(),
                    'input' => $inputPath,
                ]);

                return $this->preprocessWithGD($inputPath, $outputPath);
            }

            /** @var array{success: bool, steps_applied: array<string>, processing_time_ms: float}|null $metadata */
            $metadata = json_decode($result->output(), true);

            if (! is_array($metadata) || ! ($metadata['success'] ?? false)) {
                return $this->preprocessWithGD($inputPath, $outputPath);
            }

            return [
                'success' => true,
                'processed_path' => $outputPath,
                'engine' => 'opencv',
                'steps' => $metadata['steps_applied'] ?? [],
                'processing_time_ms' => $metadata['processing_time_ms'] ?? 0.0,
                'error' => null,
            ];
        } catch (\Throwable $e) {
            Log::error('[OpenCV] Exception during preprocessing', [
                'error' => $e->getMessage(),
                'input' => $inputPath,
            ]);

            return $this->preprocessWithGD($inputPath, $outputPath);
        }
    }

    /**
     * Preprocess using GD library as fallback.
     *
     * Implements a subset of the OpenCV pipeline using PHP's GD extension:
     * grayscale, contrast, sharpen, noise reduction.
     *
     * @return array{success: bool, processed_path: string|null, engine: string, steps: array<string>, processing_time_ms: float, error: string|null}
     */
    protected function preprocessWithGD(string $inputPath, string $outputPath): array
    {
        $startTime = microtime(true);
        $steps = [];

        try {
            $image = $this->loadImageGD($inputPath);
            if ($image === null) {
                return $this->errorResult('Failed to load image with GD');
            }

            $config = $this->getPreprocessingConfig();

            $width = imagesx($image);
            $height = imagesy($image);
            /** @var int $maxWidth */
            $maxWidth = $config['resize_max_width'] ?? 1920;
            /** @var int $maxHeight */
            $maxHeight = $config['resize_max_height'] ?? 1080;

            if ($width > $maxWidth || $height > $maxHeight) {
                $image = $this->resizeGD($image, $maxWidth, $maxHeight);
                $steps[] = 'resize';
            }

            imagefilter($image, IMG_FILTER_GRAYSCALE);
            $steps[] = 'grayscale';

            if ($config['contrast_enhancement'] ?? true) {
                imagefilter($image, IMG_FILTER_CONTRAST, -20);
                $steps[] = 'contrast';
            }

            if ($config['sharpen_enabled'] ?? true) {
                $this->sharpenGD($image);
                $steps[] = 'sharpen';
            }

            /** @var int $denoiseStrength */
            $denoiseStrength = $config['denoise_strength'] ?? 10;
            if ($denoiseStrength > 0) {
                imagefilter($image, IMG_FILTER_SMOOTH, min($denoiseStrength, 10));
                $steps[] = 'denoise';
            }

            $this->binarizeGD($image);
            $steps[] = 'binarize';

            $this->saveImageGD($image, $outputPath);
            imagedestroy($image);

            $processingTime = (microtime(true) - $startTime) * 1000;

            return [
                'success' => true,
                'processed_path' => $outputPath,
                'engine' => 'gd',
                'steps' => $steps,
                'processing_time_ms' => round($processingTime, 2),
                'error' => null,
            ];
        } catch (\Throwable $e) {
            Log::error('[OpenCV] GD fallback preprocessing failed', [
                'error' => $e->getMessage(),
                'input' => $inputPath,
            ]);

            return $this->errorResult($e->getMessage());
        }
    }

    /**
     * Get preprocessing configuration from config.
     *
     * @return array<string, mixed>
     */
    protected function getPreprocessingConfig(): array
    {
        return [
            'resize_max_width' => intval(config('services.opencv.preprocessing.resize_max_width', 1920)),
            'resize_max_height' => intval(config('services.opencv.preprocessing.resize_max_height', 1080)),
            'contrast_enhancement' => (bool) config('services.opencv.preprocessing.contrast_enhancement', true),
            'sharpen_enabled' => (bool) config('services.opencv.preprocessing.sharpen_enabled', true),
            'denoise_strength' => intval(config('services.opencv.preprocessing.denoise_strength', 10)),
            'deskew_enabled' => (bool) config('services.opencv.preprocessing.deskew_enabled', true),
            'adaptive_threshold' => (bool) config('services.opencv.preprocessing.adaptive_threshold', true),
            'threshold_block_size' => intval(config('services.opencv.preprocessing.threshold_block_size', 11)),
            'threshold_c_value' => intval(config('services.opencv.preprocessing.threshold_c_value', 2)),
        ];
    }

    /**
     * Generate output path from input path.
     */
    protected function generateOutputPath(string $inputPath): string
    {
        $pathInfo = pathinfo($inputPath);
        $dirname = $pathInfo['dirname'] ?? dirname($inputPath);
        $filename = $pathInfo['filename'] ?? 'image';
        $extension = $pathInfo['extension'] ?? 'png';

        return $dirname.DIRECTORY_SEPARATOR.$filename.'_opencv.'.$extension;
    }

    /**
     * Load image using GD library.
     */
    protected function loadImageGD(string $path): ?\GdImage
    {
        $imageInfo = getimagesize($path);
        if ($imageInfo === false) {
            return null;
        }

        $image = match ($imageInfo['mime']) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/webp' => imagecreatefromwebp($path),
            default => null,
        };

        return $image instanceof \GdImage ? $image : null;
    }

    /**
     * Resize image maintaining aspect ratio using GD.
     */
    protected function resizeGD(\GdImage $image, int $maxWidth, int $maxHeight): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = max(1, (int) ($width * $ratio));
        $newHeight = max(1, (int) ($height * $ratio));

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        if (! $resized instanceof \GdImage) {
            return $image;
        }

        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($image);

        return $resized;
    }

    /**
     * Sharpen image using convolution matrix via GD.
     */
    protected function sharpenGD(\GdImage $image): void
    {
        imageconvolution($image, [
            [-1, -1, -1],
            [-1, 16, -1],
            [-1, -1, -1],
        ], 8, 0);
    }

    /**
     * Simulate binarization using GD threshold.
     *
     * GD lacks adaptive thresholding, so we use a global threshold
     * after contrast enhancement as an approximation.
     */
    protected function binarizeGD(\GdImage $image): void
    {
        $width = imagesx($image);
        $height = imagesy($image);

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgb = imagecolorat($image, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $gray = (int) (0.299 * $r + 0.587 * $g + 0.114 * $b);
                $bw = $gray > 128 ? 255 : 0;
                $color = imagecolorallocate($image, $bw, $bw, $bw);
                if ($color !== false) {
                    imagesetpixel($image, $x, $y, $color);
                }
            }
        }
    }

    /**
     * Save processed image using GD.
     */
    protected function saveImageGD(\GdImage $image, string $outputPath): void
    {
        $extension = strtolower(pathinfo($outputPath, PATHINFO_EXTENSION));

        $dir = dirname($outputPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        match ($extension) {
            'jpg', 'jpeg' => imagejpeg($image, $outputPath, 95),
            'png' => imagepng($image, $outputPath, 9),
            'webp' => imagewebp($image, $outputPath, 95),
            default => imagepng($image, $outputPath, 9),
        };
    }

    /**
     * Build an error result array.
     *
     * @return array{success: bool, processed_path: null, engine: string, steps: array<string>, processing_time_ms: float, error: string}
     */
    protected function errorResult(string $error): array
    {
        return [
            'success' => false,
            'processed_path' => null,
            'engine' => 'none',
            'steps' => [],
            'processing_time_ms' => 0.0,
            'error' => $error,
        ];
    }
}
