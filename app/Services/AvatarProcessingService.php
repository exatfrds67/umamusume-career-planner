<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;

class AvatarProcessingService
{
    private const CANVAS_SIZE = 256;

    /**
     * Process an avatar image with transforms and produce square + circular outputs.
     *
     * @param  array{image_x: float, image_y: float, image_zoom: float, image_rotation: int, image_flip_h: bool}  $transforms
     * @return array{square: string|null, circular: string|null}
     */
    public function processAvatar(string $avatarUrl, array $transforms, int $characterId): array
    {
        try {
            $manager = new ImageManager(new GdDriver);
            $image = $this->loadImage($manager, $avatarUrl);

            if ($image === null) {
                return ['square' => null, 'circular' => null];
            }

            $image = $this->applyTransforms($image, $transforms);

            $squarePath = $this->saveSquare($image, $characterId);
            $circularPath = $this->saveCircular($image, $characterId);

            return [
                'square' => $squarePath,
                'circular' => $circularPath,
            ];
        } catch (\Throwable $e) {
            Log::warning('Avatar processing failed', [
                'character_id' => $characterId,
                'error' => $e->getMessage(),
            ]);

            return ['square' => null, 'circular' => null];
        }
    }

    private function loadImage(ImageManager $manager, string $avatarUrl): ?ImageInterface
    {
        if (str_starts_with($avatarUrl, '/images/')) {
            $path = public_path($avatarUrl);
            if (! file_exists($path)) {
                return null;
            }

            return $manager->read($path);
        }

        if (str_starts_with($avatarUrl, '/storage/')) {
            $relativeStoragePath = ltrim(substr($avatarUrl, strlen('/storage/')), '/');
            if ($relativeStoragePath === '') {
                return null;
            }

            $path = Storage::disk('public')->path($relativeStoragePath);
            if (! file_exists($path)) {
                return null;
            }

            return $manager->read($path);
        }

        if (str_starts_with($avatarUrl, 'http://') || str_starts_with($avatarUrl, 'https://')) {
            $parsedUrlPath = parse_url($avatarUrl, PHP_URL_PATH);

            if (is_string($parsedUrlPath) && str_starts_with($parsedUrlPath, '/storage/')) {
                $relativeStoragePath = ltrim(substr($parsedUrlPath, strlen('/storage/')), '/');
                if ($relativeStoragePath === '') {
                    return null;
                }

                $path = Storage::disk('public')->path($relativeStoragePath);
                if (! file_exists($path)) {
                    return null;
                }

                return $manager->read($path);
            }
        }

        if (str_starts_with($avatarUrl, 'data:image/')) {
            $parts = explode(',', $avatarUrl, 2);
            if (count($parts) !== 2) {
                return null;
            }
            $decoded = base64_decode($parts[1], true);
            if ($decoded === false) {
                return null;
            }

            return $manager->read($decoded);
        }

        return null;
    }

    /**
     * @param  array{image_x: float, image_y: float, image_zoom: float, image_rotation: int, image_flip_h: bool}  $transforms
     */
    private function applyTransforms(ImageInterface $image, array $transforms): ImageInterface
    {
        if ($transforms['image_flip_h']) {
            $image = $image->flip();
        }

        $rotation = (int) $transforms['image_rotation'];
        if ($rotation !== 0) {
            $image = $image->rotate(-$rotation);
        }

        $zoom = max(0.5, min(2.0, (float) $transforms['image_zoom']));
        $image = $image->cover(
            (int) round(self::CANVAS_SIZE * $zoom),
            (int) round(self::CANVAS_SIZE * $zoom)
        );

        $image = $image->crop(self::CANVAS_SIZE, self::CANVAS_SIZE);

        return $image;
    }

    private function saveSquare(ImageInterface $image, int $characterId): string
    {
        $directory = "avatars/{$characterId}";
        Storage::disk('public')->makeDirectory($directory);

        $relativePath = "{$directory}/square.jpg";
        $fullPath = Storage::disk('public')->path($relativePath);

        $image->toJpeg(85)->save($fullPath);

        return '/storage/'.ltrim($relativePath, '/');
    }

    private function saveCircular(ImageInterface $image, int $characterId): ?string
    {
        $directory = "avatars/{$characterId}";
        Storage::disk('public')->makeDirectory($directory);

        $relativePath = "{$directory}/circular.png";
        $fullPath = Storage::disk('public')->path($relativePath);

        $size = self::CANVAS_SIZE;
        $gdSource = imagecreatefromstring((string) $image->toJpeg(100));
        if ($gdSource === false) {
            return null;
        }

        $gdCircular = imagecreatetruecolor($size, $size);
        if ($gdCircular === false) {
            imagedestroy($gdSource);

            return null;
        }

        imagesavealpha($gdCircular, true);
        $transparent = imagecolorallocatealpha($gdCircular, 0, 0, 0, 127);
        if ($transparent !== false) {
            imagefill($gdCircular, 0, 0, $transparent);
        }

        $center = $size / 2;
        $radius = $center;
        for ($x = 0; $x < $size; $x++) {
            for ($y = 0; $y < $size; $y++) {
                $dist = sqrt(($x - $center) ** 2 + ($y - $center) ** 2);
                if ($dist <= $radius) {
                    $color = imagecolorat($gdSource, $x, $y);
                    if ($color !== false) {
                        imagesetpixel($gdCircular, $x, $y, $color);
                    }
                }
            }
        }

        imagepng($gdCircular, $fullPath);
        imagedestroy($gdSource);
        imagedestroy($gdCircular);

        return '/storage/'.ltrim($relativePath, '/');
    }
}
