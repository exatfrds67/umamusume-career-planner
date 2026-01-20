<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OCRExtraction>
 */
class OCRExtractionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'image_path' => 'ocr-uploads/test_'.$this->faker->uuid().'.png',
            'image_hash' => $this->faker->sha256(),
            'extracted_text' => 'Speed: 850 Stamina: 720',
            'parsed_data' => [
                'stats' => [
                    'speed' => $this->faker->numberBetween(500, 1200),
                    'stamina' => $this->faker->numberBetween(500, 1200),
                ],
            ],
            'confidence_score' => $this->faker->randomFloat(2, 0.5, 1.0),
            'data_type' => $this->faker->randomElement(['character_stats', 'training_session', 'race_result', 'skill_list']),
            'status' => 'processed',
            'processed_at' => now(),
            'processing_metadata' => [
                'language' => 'jpn+eng',
                'psm' => '6',
                'oem' => '3',
            ],
        ];
    }
}
