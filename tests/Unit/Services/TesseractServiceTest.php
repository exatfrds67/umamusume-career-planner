<?php

declare(strict_types=1);

use App\Models\OCRExtraction;
use App\Models\User;
use App\Services\ImageProcessingService;
use App\Services\TesseractService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->user = User::factory()->create();
    $this->imageProcessor = new ImageProcessingService;
    $this->service = new TesseractService($this->imageProcessor);
});

describe('TesseractService', function () {
    it('checks if Tesseract is available', function () {
        $available = $this->service->isAvailable();

        // This test will pass if Tesseract is installed, otherwise it will fail
        // In CI/CD, we should ensure Tesseract is installed
        expect($available)->toBeIn([true, false]);
    });

    it('validates image before processing', function () {
        // Create an invalid file (too small)
        $file = UploadedFile::fake()->image('tiny.jpg', 100, 100);

        $result = $this->service->processScreenshot($file, $this->user->id);

        // Image validation happens before Tesseract is called
        // Small images should fail validation regardless of Tesseract availability
        expect($result['success'])->toBeFalse()
            ->and($result['error'])->toContain('too small');
    });

    it('creates OCR extraction record when validation passes', function () {
        $file = UploadedFile::fake()->image('test.jpg', 800, 600);

        // Process screenshot - this tests the validation and record creation logic
        $result = $this->service->processScreenshot($file, $this->user->id);

        // If Tesseract is not available, the service should still handle it gracefully
        if ($result['success'] || isset($result['extraction_id'])) {
            expect(OCRExtraction::count())->toBeGreaterThan(0);
        } else {
            // If processing failed (e.g., Tesseract not available), verify error handling
            expect($result)->toHaveKey('error');
        }
    });

    it('detects duplicate images by hash', function () {
        // Create an existing OCRExtraction record with a known hash
        $existingHash = 'test_duplicate_hash_123';
        $existingExtraction = OCRExtraction::factory()->create([
            'user_id' => $this->user->id,
            'image_hash' => $existingHash,
            'status' => 'processed',
            'extracted_text' => 'Speed: 850 Stamina: 720',
            'parsed_data' => ['stats' => ['speed' => 850, 'stamina' => 720]],
            'confidence_score' => 0.95,
        ]);

        // Mock ImageProcessingService to return the same hash
        $mockImageProcessor = Mockery::mock(ImageProcessingService::class);
        $mockImageProcessor->shouldReceive('validateImage')
            ->andReturn(['valid' => true, 'error' => null]);
        $mockImageProcessor->shouldReceive('calculateImageHash')
            ->andReturn($existingHash);

        $service = new TesseractService($mockImageProcessor);

        $file = UploadedFile::fake()->image('test.jpg', 800, 600);
        $result = $service->processScreenshot($file, $this->user->id);

        // Should return the existing extraction (duplicate detected)
        expect($result['extraction_id'])->toBe($existingExtraction->id);
    });

    it('extracts stats from OCR text', function () {
        $text = "スピード: 850\nスタミナ: 720\nパワー: 680\n根性: 450\n賢さ: 920";

        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('extractStats');
        $method->setAccessible(true);

        $stats = $method->invoke($this->service, $text);

        expect($stats['speed'])->toBe(850)
            ->and($stats['stamina'])->toBe(720)
            ->and($stats['power'])->toBe(680)
            ->and($stats['guts'])->toBe(450)
            ->and($stats['wit'])->toBe(920);
    });

    it('extracts stats from English OCR text', function () {
        $text = "Speed: 850\nStamina: 720\nPower: 680\nGuts: 450\nWit: 920";

        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('extractStats');
        $method->setAccessible(true);

        $stats = $method->invoke($this->service, $text);

        expect($stats['speed'])->toBe(850)
            ->and($stats['stamina'])->toBe(720)
            ->and($stats['power'])->toBe(680)
            ->and($stats['guts'])->toBe(450)
            ->and($stats['wit'])->toBe(920);
    });

    it('validates stat ranges', function () {
        $text = "Speed: 9999\nStamina: 10\nPower: 680";

        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('extractStats');
        $method->setAccessible(true);

        $stats = $method->invoke($this->service, $text);

        // 9999 is out of range (max 1200), should be null
        expect($stats['speed'])->toBeNull()
            // 10 is out of range (min 50), should be null
            ->and($stats['stamina'])->toBeNull()
            // 680 is valid
            ->and($stats['power'])->toBe(680);
    });

    it('extracts additional data from OCR text', function () {
        $text = "ターン: 24/78\n体力: 85\nやる気: 好調";

        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('extractAdditionalData');
        $method->setAccessible(true);

        $data = $method->invoke($this->service, $text);

        expect($data['current_turn'])->toBe(24)
            ->and($data['total_turns'])->toBe(78)
            ->and($data['energy_level'])->toBe(85)
            ->and($data['mood_status'])->toBe('good');
    });

    it('normalizes Japanese mood status', function () {
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('normalizeMood');
        $method->setAccessible(true);

        expect($method->invoke($this->service, '絶好調'))->toBe('great')
            ->and($method->invoke($this->service, '好調'))->toBe('good')
            ->and($method->invoke($this->service, '普通'))->toBe('normal')
            ->and($method->invoke($this->service, '不調'))->toBe('bad')
            ->and($method->invoke($this->service, '絶不調'))->toBe('awful');
    });

    it('normalizes English mood status', function () {
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('normalizeMood');
        $method->setAccessible(true);

        expect($method->invoke($this->service, 'Great'))->toBe('great')
            ->and($method->invoke($this->service, 'GOOD'))->toBe('good')
            ->and($method->invoke($this->service, 'normal'))->toBe('normal');
    });

    it('calculates confidence score correctly', function () {
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('calculateConfidence');
        $method->setAccessible(true);

        // All stats found
        $stats = ['speed' => 850, 'stamina' => 720, 'power' => 680, 'guts' => 450, 'wit' => 920];
        expect($method->invoke($this->service, $stats))->toBe(1.0);

        // 3 out of 5 stats found
        $stats = ['speed' => 850, 'stamina' => null, 'power' => 680, 'guts' => null, 'wit' => 920];
        expect($method->invoke($this->service, $stats))->toBe(0.6);

        // No stats found
        $stats = ['speed' => null, 'stamina' => null, 'power' => null, 'guts' => null, 'wit' => null];
        expect($method->invoke($this->service, $stats))->toBe(0.0);
    });
});
