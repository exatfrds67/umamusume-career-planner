<?php

declare(strict_types=1);

use App\Services\ImageProcessingService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->service = new ImageProcessingService;
});

describe('ImageProcessingService', function () {
    it('validates valid JPEG image', function () {
        $file = UploadedFile::fake()->image('test.jpg', 800, 600);

        $result = $this->service->validateImage($file);

        expect($result['valid'])->toBeTrue()
            ->and($result['error'])->toBeNull();
    });

    it('validates valid PNG image', function () {
        $file = UploadedFile::fake()->image('test.png', 800, 600);

        $result = $this->service->validateImage($file);

        expect($result['valid'])->toBeTrue()
            ->and($result['error'])->toBeNull();
    });

    it('rejects file that is too large', function () {
        // Create a file larger than max size (10MB)
        $file = UploadedFile::fake()->create('large.jpg', 11000); // 11MB

        $result = $this->service->validateImage($file);

        expect($result['valid'])->toBeFalse()
            ->and($result['error'])->toContain('exceeds maximum');
    });

    it('rejects invalid file extension', function () {
        $file = UploadedFile::fake()->create('test.txt', 100);

        $result = $this->service->validateImage($file);

        expect($result['valid'])->toBeFalse()
            ->and($result['error'])->toContain('Invalid file');
    });

    it('rejects image with dimensions too small', function () {
        $file = UploadedFile::fake()->image('tiny.jpg', 100, 100);

        $result = $this->service->validateImage($file);

        expect($result['valid'])->toBeFalse()
            ->and($result['error'])->toContain('too small');
    });

    it('rejects image with dimensions too large', function () {
        $file = UploadedFile::fake()->image('huge.jpg', 5000, 5000);

        $result = $this->service->validateImage($file);

        expect($result['valid'])->toBeFalse()
            ->and($result['error'])->toContain('too large');
    });

    it('calculates image hash correctly', function () {
        $file = UploadedFile::fake()->image('test.jpg', 800, 600);
        $path = $file->store('test', 'local');
        $fullPath = Storage::disk('local')->path($path);

        $hash1 = $this->service->calculateImageHash($fullPath);
        $hash2 = $this->service->calculateImageHash($fullPath);

        expect($hash1)->toBe($hash2)
            ->and($hash1)->toHaveLength(64); // SHA256 hash length
    });

    it('reports GD library availability', function () {
        $available = $this->service->isAvailable();

        expect($available)->toBeTrue();
    });

    it('preprocesses image for OCR', function () {
        $file = UploadedFile::fake()->image('test.jpg', 800, 600);
        $path = $file->store('test', 'local');
        $fullPath = Storage::disk('local')->path($path);

        $result = $this->service->preprocessForOCR($fullPath);

        expect($result['success'])->toBeTrue()
            ->and($result['processed_path'])->not->toBeNull()
            ->and($result['error'])->toBeNull();

        // Clean up processed image
        if ($result['processed_path']) {
            @unlink($result['processed_path']);
        }
    });
});

describe('ImageProcessingService Security', function () {
    it('detects suspicious PHP code in file', function () {
        // Create a fake file with PHP code
        $content = '<?php echo "malicious"; ?>';
        $file = UploadedFile::fake()->createWithContent('malicious.jpg', $content);

        $result = $this->service->validateImage($file);

        expect($result['valid'])->toBeFalse()
            ->and($result['error'])->toContain('suspicious');
    });

    it('validates actual image format', function () {
        // Create a text file with .jpg extension
        $file = UploadedFile::fake()->create('fake.jpg', 100);

        $result = $this->service->validateImage($file);

        expect($result['valid'])->toBeFalse();
    });
});
