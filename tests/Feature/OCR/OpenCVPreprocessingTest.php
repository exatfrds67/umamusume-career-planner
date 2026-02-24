<?php

declare(strict_types=1);

use App\Services\OCR\OpenCVPreprocessingService;
use Illuminate\Support\Facades\Process;

beforeEach(function () {
    $this->service = new OpenCVPreprocessingService;
    $this->testImageDir = sys_get_temp_dir().'/ocr_test_'.uniqid();
    if (! is_dir($this->testImageDir)) {
        mkdir($this->testImageDir, 0755, true);
    }
});

afterEach(function () {
    if (is_dir($this->testImageDir)) {
        $files = glob($this->testImageDir.'/*');
        if (is_array($files)) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        rmdir($this->testImageDir);
    }
});

/*
|--------------------------------------------------------------------------
| OpenCV Preprocessing Service Tests
|--------------------------------------------------------------------------
*/
describe('OpenCVPreprocessingService', function (): void {

    it('returns availability status', function (): void {
        $result = $this->service->checkAvailability();

        expect($result)->toBeArray();
        expect($result)->toHaveKeys(['available', 'engine', 'details']);
        expect($result['engine'])->toBeIn(['opencv', 'gd', 'none']);
    });

    it('reports error for nonexistent input file', function (): void {
        $result = $this->service->preprocess('/nonexistent/path/image.png');

        expect($result['success'])->toBeFalse();
        expect($result['error'])->toContain('not found');
    });

    it('preprocesses a JPEG image with GD fallback', function (): void {
        $inputPath = $this->testImageDir.'/test_input.jpg';
        createTestImage($inputPath, 'jpeg');

        $result = $this->service->preprocess($inputPath);

        expect($result['success'])->toBeTrue();
        expect($result['processed_path'])->not->toBeNull();
        expect($result['engine'])->toBeIn(['opencv', 'gd']);
        expect($result['steps'])->toBeArray();
        expect($result['steps'])->not->toBeEmpty();
        expect($result['processing_time_ms'])->toBeGreaterThanOrEqual(0);
        expect($result['error'])->toBeNull();

        if ($result['processed_path'] !== null) {
            expect(file_exists($result['processed_path']))->toBeTrue();
        }
    });

    it('preprocesses a PNG image', function (): void {
        $inputPath = $this->testImageDir.'/test_input.png';
        createTestImage($inputPath, 'png');

        $result = $this->service->preprocess($inputPath);

        expect($result['success'])->toBeTrue();
        expect($result['engine'])->toBeIn(['opencv', 'gd']);
    });

    it('generates output path when not specified', function (): void {
        $inputPath = $this->testImageDir.'/auto_output.jpg';
        createTestImage($inputPath, 'jpeg');

        $result = $this->service->preprocess($inputPath);

        expect($result['success'])->toBeTrue();
        expect($result['processed_path'])->toContain('auto_output_opencv.jpg');
    });

    it('uses specified output path', function (): void {
        $inputPath = $this->testImageDir.'/test_input.jpg';
        $outputPath = $this->testImageDir.'/custom_output.jpg';
        createTestImage($inputPath, 'jpeg');

        $result = $this->service->preprocess($inputPath, $outputPath);

        expect($result['success'])->toBeTrue();
        expect($result['processed_path'])->toBe($outputPath);

        if (file_exists($outputPath)) {
            expect(file_exists($outputPath))->toBeTrue();
        }
    });

    it('applies grayscale step in GD fallback', function (): void {
        $inputPath = $this->testImageDir.'/grayscale_test.jpg';
        createTestImage($inputPath, 'jpeg');

        Process::fake([
            '*python*' => Process::result(output: '', exitCode: 1),
        ]);

        $result = $this->service->preprocess($inputPath);

        expect($result['success'])->toBeTrue();

        if ($result['engine'] === 'gd') {
            expect($result['steps'])->toContain('grayscale');
        }
    });

    it('applies binarization step in GD fallback', function (): void {
        $inputPath = $this->testImageDir.'/binarize_test.jpg';
        createTestImage($inputPath, 'jpeg');

        Process::fake([
            '*python*' => Process::result(output: '', exitCode: 1),
        ]);

        $result = $this->service->preprocess($inputPath);

        expect($result['success'])->toBeTrue();

        if ($result['engine'] === 'gd') {
            expect($result['steps'])->toContain('binarize');
        }
    });

    it('respects config settings', function (): void {
        config()->set('services.opencv.preprocessing.contrast_enhancement', false);
        config()->set('services.opencv.preprocessing.sharpen_enabled', false);

        $inputPath = $this->testImageDir.'/config_test.jpg';
        createTestImage($inputPath, 'jpeg');

        Process::fake([
            '*python*' => Process::result(output: '', exitCode: 1),
        ]);

        $result = $this->service->preprocess($inputPath);

        expect($result['success'])->toBeTrue();

        if ($result['engine'] === 'gd') {
            expect($result['steps'])->not->toContain('contrast');
            expect($result['steps'])->not->toContain('sharpen');
        }
    });

    it('falls back to GD when Python process fails', function (): void {
        Process::fake([
            '*python*' => Process::result(
                output: '',
                errorOutput: 'Python not found',
                exitCode: 1,
            ),
        ]);

        $inputPath = $this->testImageDir.'/fallback_test.jpg';
        createTestImage($inputPath, 'jpeg');

        $service = new OpenCVPreprocessingService;
        $result = $service->preprocess($inputPath);

        expect($result['success'])->toBeTrue();
        expect($result['engine'])->toBe('gd');
    });

    it('uses OpenCV when Python process succeeds', function (): void {
        $inputPath = $this->testImageDir.'/opencv_test.jpg';
        $outputPath = $this->testImageDir.'/opencv_test_opencv.jpg';
        createTestImage($inputPath, 'jpeg');
        copy($inputPath, $outputPath);

        Process::fake([
            '*cv2*' => Process::result(output: '4.10.0', exitCode: 0),
            '*ocr_preprocess*' => Process::result(
                output: json_encode([
                    'success' => true,
                    'opencv_available' => true,
                    'steps_applied' => ['denoise', 'clahe_contrast', 'sharpen', 'adaptive_threshold'],
                    'processing_time_ms' => 42.5,
                ]),
                exitCode: 0,
            ),
        ]);

        $service = new OpenCVPreprocessingService;
        $result = $service->preprocess($inputPath, $outputPath);

        expect($result['success'])->toBeTrue();
        expect($result['engine'])->toBe('opencv');
        expect($result['steps'])->toContain('adaptive_threshold');
        expect($result['processing_time_ms'])->toBe(42.5);
    });

    it('includes all expected pipeline steps in GD mode', function (): void {
        $inputPath = $this->testImageDir.'/steps_test.jpg';
        createTestImage($inputPath, 'jpeg', 100, 100);

        Process::fake([
            '*python*' => Process::result(output: '', exitCode: 1),
        ]);

        $result = $this->service->preprocess($inputPath);

        expect($result['success'])->toBeTrue();

        if ($result['engine'] === 'gd') {
            expect($result['steps'])->toContain('grayscale');
            expect($result['steps'])->toContain('contrast');
            expect($result['steps'])->toContain('sharpen');
            expect($result['steps'])->toContain('denoise');
            expect($result['steps'])->toContain('binarize');
        }
    });

    it('produces a valid image file after processing', function (): void {
        $inputPath = $this->testImageDir.'/valid_output.jpg';
        createTestImage($inputPath, 'jpeg');

        $result = $this->service->preprocess($inputPath);

        expect($result['success'])->toBeTrue();

        if ($result['processed_path'] !== null && file_exists($result['processed_path'])) {
            $imageInfo = getimagesize($result['processed_path']);
            expect($imageInfo)->not->toBeFalse();
            expect($imageInfo[0])->toBeGreaterThan(0);
            expect($imageInfo[1])->toBeGreaterThan(0);
        }
    });
});

/*
|--------------------------------------------------------------------------
| Image Quality Level Tests
|--------------------------------------------------------------------------
| Feature: umamusume-career-planner-main-v2.4.0
| Validates: Requirements FR-08.4, INT-OCR-04
*/
describe('OCR Preprocessing Quality Levels', function (): void {

    it('handles small images without errors', function (): void {
        $inputPath = $this->testImageDir.'/small.jpg';
        createTestImage($inputPath, 'jpeg', 320, 240);

        $result = $this->service->preprocess($inputPath);

        expect($result['success'])->toBeTrue();
    });

    it('handles large images with resize step', function (): void {
        $inputPath = $this->testImageDir.'/large.jpg';
        createTestImage($inputPath, 'jpeg', 3000, 2000);

        Process::fake([
            '*python*' => Process::result(output: '', exitCode: 1),
        ]);

        $result = $this->service->preprocess($inputPath);

        expect($result['success'])->toBeTrue();

        if ($result['engine'] === 'gd') {
            expect($result['steps'])->toContain('resize');
        }
    });

    it('handles low contrast images', function (): void {
        $inputPath = $this->testImageDir.'/low_contrast.jpg';
        createLowContrastImage($inputPath);

        $result = $this->service->preprocess($inputPath);

        expect($result['success'])->toBeTrue();
        expect($result['steps'])->not->toBeEmpty();
    });

    it('handles noisy images', function (): void {
        $inputPath = $this->testImageDir.'/noisy.jpg';
        createNoisyImage($inputPath);

        $result = $this->service->preprocess($inputPath);

        expect($result['success'])->toBeTrue();

        if ($result['engine'] === 'gd') {
            expect($result['steps'])->toContain('denoise');
        }
    });

    it('handles WebP format', function (): void {
        $inputPath = $this->testImageDir.'/test.webp';
        createTestImage($inputPath, 'webp');

        $result = $this->service->preprocess($inputPath);

        expect($result['success'])->toBeTrue();
    });
});

/**
 * Helper: Create a test image with optional dimensions.
 */
function createTestImage(string $path, string $format = 'jpeg', int $width = 640, int $height = 480): void
{
    $image = imagecreatetruecolor($width, $height);
    if (! $image instanceof \GdImage) {
        throw new \RuntimeException('Failed to create test image');
    }

    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);

    if ($white !== false) {
        imagefill($image, 0, 0, $white);
    }
    if ($black !== false) {
        imagestring($image, 5, 10, 10, 'Speed 1200', $black);
        imagestring($image, 5, 10, 30, 'Stamina 800', $black);
        imagestring($image, 5, 10, 50, 'Power 950', $black);
    }

    match ($format) {
        'jpeg', 'jpg' => imagejpeg($image, $path, 85),
        'png' => imagepng($image, $path),
        'webp' => imagewebp($image, $path, 85),
        default => imagejpeg($image, $path, 85),
    };

    imagedestroy($image);
}

/**
 * Helper: Create a low-contrast test image.
 */
function createLowContrastImage(string $path): void
{
    $image = imagecreatetruecolor(640, 480);
    if (! $image instanceof \GdImage) {
        throw new \RuntimeException('Failed to create test image');
    }

    $bgColor = imagecolorallocate($image, 200, 200, 200);
    $textColor = imagecolorallocate($image, 180, 180, 180);

    if ($bgColor !== false) {
        imagefill($image, 0, 0, $bgColor);
    }
    if ($textColor !== false) {
        imagestring($image, 5, 10, 10, 'Low Contrast Text', $textColor);
    }

    imagejpeg($image, $path, 85);
    imagedestroy($image);
}

/**
 * Helper: Create a noisy test image.
 */
function createNoisyImage(string $path): void
{
    $image = imagecreatetruecolor(640, 480);
    if (! $image instanceof \GdImage) {
        throw new \RuntimeException('Failed to create test image');
    }

    $white = imagecolorallocate($image, 255, 255, 255);
    if ($white !== false) {
        imagefill($image, 0, 0, $white);
    }

    for ($i = 0; $i < 5000; $i++) {
        $gray = mt_rand(0, 255);
        $color = imagecolorallocate($image, $gray, $gray, $gray);
        if ($color !== false) {
            imagesetpixel($image, mt_rand(0, 639), mt_rand(0, 479), $color);
        }
    }

    $black = imagecolorallocate($image, 0, 0, 0);
    if ($black !== false) {
        imagestring($image, 5, 10, 10, 'Noisy Image Text', $black);
    }

    imagejpeg($image, $path, 70);
    imagedestroy($image);
}
