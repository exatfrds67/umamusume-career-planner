<?php

declare(strict_types=1);

use App\Models\OCRExtraction;
use App\Models\User;
use App\Services\ImageProcessingService;
use App\Services\TesseractServiceEnhanced;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

describe('Intelligent OCR Processing', function () {
    beforeEach(function () {
        Storage::fake('local');
        $this->user = User::factory()->create();
        $this->imageProcessor = app(ImageProcessingService::class);
        $this->tesseractService = new TesseractServiceEnhanced($this->imageProcessor);
    });

    it('processes character stats screenshot with intelligent parsing', function () {
        // Create a mock image file
        $file = UploadedFile::fake()->image('character_stats.png', 800, 600);

        // Mock the OCR text output
        $mockText = <<<'TEXT'
        サイレンススズカ
        スピード: 850
        スタミナ: 720
        パワー: 680
        根性: 450
        賢さ: 600
        ターン: 45/72
        体力: 75
        やる気: 好調
        TEXT;

        // Process with intelligent parsing
        $result = $this->tesseractService->processWithIntelligentParsing($mockText);

        expect($result['success'])->toBeTrue()
            ->and($result['screen_type'])->toBe('character_stats')
            ->and($result['parser_used'])->toBe('character_stats')
            ->and($result['confidence'])->toBeGreaterThan(0.8)
            ->and($result['data'])->toHaveKey('stats')
            ->and($result['data']['stats']['speed'])->toBe(850);
    });

    it('processes training session screenshot correctly', function () {
        $mockText = <<<'TEXT'
        トレーニング: スピード
        スピード +45
        パワー +15
        体力 -20
        スキルヒント
        友情トレーニング
        TEXT;

        $result = $this->tesseractService->processWithIntelligentParsing($mockText);

        expect($result['success'])->toBeTrue()
            ->and($result['screen_type'])->toBe('training_session')
            ->and($result['data']['training_type'])->toBe('speed')
            ->and($result['data']['has_skill_hint'])->toBeTrue();
    });

    it('processes race result screenshot correctly', function () {
        $mockText = <<<'TEXT'
        レース結果
        日本ダービー G1
        2400m 芝
        着順: 1位
        ファン +8000
        SP +60
        TEXT;

        $result = $this->tesseractService->processWithIntelligentParsing($mockText);

        expect($result['success'])->toBeTrue()
            ->and($result['screen_type'])->toBe('race_result')
            ->and($result['data']['position'])->toBe(1)
            ->and($result['data']['outcome'])->toBe('victory');
    });

    it('processes skill list screenshot correctly', function () {
        $mockText = <<<'TEXT'
        所持SP: 450
        スキル一覧
        加速スキル SP: 120 ヒント Lv2
        回復スキル SP: 150 習得済
        TEXT;

        $result = $this->tesseractService->processWithIntelligentParsing($mockText);

        expect($result['success'])->toBeTrue()
            ->and($result['screen_type'])->toBe('skill_list')
            ->and($result['data']['total_sp'])->toBe(450)
            ->and($result['data']['skills'])->toHaveCount(2);
    });

    it('falls back to basic parsing when screen type cannot be detected', function () {
        $mockText = 'Some random text Speed: 850 Stamina: 720';

        $result = $this->tesseractService->processWithIntelligentParsing($mockText);

        expect($result['parser_used'])->toBe('fallback')
            ->and($result['data'])->toHaveKey('stats')
            ->and($result['errors'])->not->toBeEmpty();
    });

    it('stores extraction results in database', function () {
        // Create a mock extraction result
        $mockText = 'Speed: 850 Stamina: 720';
        $result = $this->tesseractService->processWithIntelligentParsing($mockText);

        // Store in database using OCRExtraction model
        $extraction = OCRExtraction::create([
            'user_id' => $this->user->id,
            'image_path' => 'ocr/test_image_'.uniqid().'.png',
            'image_hash' => 'test_hash_'.uniqid(),
            'extracted_text' => $mockText,
            'parsed_data' => $result['data'],
            'confidence_score' => $result['confidence'],
            'data_type' => $result['screen_type'] ?? 'character_stats',
            'status' => 'processed',
        ]);

        expect($extraction)->toBeInstanceOf(OCRExtraction::class)
            ->and($extraction->id)->toBeInt()
            ->and($extraction->extracted_text)->toBe($mockText)
            ->and($extraction->user_id)->toBe($this->user->id);
    });

    it('handles duplicate image uploads efficiently', function () {
        $imageHash = 'duplicate_test_hash_'.uniqid();

        // Create first extraction
        $first = OCRExtraction::create([
            'user_id' => $this->user->id,
            'image_path' => 'ocr/duplicate_test_image.png',
            'image_hash' => $imageHash,
            'extracted_text' => 'Test text',
            'parsed_data' => ['stats' => ['speed' => 850]],
            'confidence_score' => 0.9,
            'data_type' => 'character_stats',
            'status' => 'processed',
        ]);

        // Check if duplicate exists by image hash
        $duplicate = OCRExtraction::where('image_hash', $imageHash)->first();

        expect($duplicate)->not->toBeNull()
            ->and($duplicate->id)->toBe($first->id)
            ->and($duplicate->image_hash)->toBe($imageHash);
    });

    it('validates confidence scores correctly', function () {
        // High confidence - all stats found
        $highConfidenceText = 'Speed: 850 Stamina: 720 Power: 680 Guts: 450 Wit: 600';
        $result1 = $this->tesseractService->processWithIntelligentParsing($highConfidenceText);
        expect($result1['confidence'])->toBeGreaterThan(0.8);

        // Low confidence - few stats found
        $lowConfidenceText = 'Speed: 850';
        $result2 = $this->tesseractService->processWithIntelligentParsing($lowConfidenceText);
        expect($result2['confidence'])->toBeLessThan(0.5);
    });
});
