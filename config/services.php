<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'aws' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        'token' => env('AWS_SESSION_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tesseract OCR Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Tesseract OCR engine used for screenshot processing.
    |
    */

    'tesseract' => [
        'path' => env('TESSERACT_PATH', 'tesseract'),
        'language' => env('TESSERACT_LANGUAGE', 'jpn+eng'),
        'psm' => env('TESSERACT_PSM', '6'), // Page segmentation mode
        'oem' => env('TESSERACT_OEM', '3'), // OCR Engine mode
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Processing Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for image preprocessing and validation.
    |
    */

    'image_processing' => [
        // File upload limits
        'max_file_size' => env('IMAGE_MAX_FILE_SIZE', 10485760), // 10MB default
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
        'temp_file_ttl' => env('IMAGE_TEMP_TTL', 3600), // 1 hour default
        'cleanup_enabled' => env('IMAGE_CLEANUP_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenCV Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for OpenCV image preprocessing.
    |
    */

    'opencv' => [
        'preprocessing' => [
            'resize_max_width' => env('OPENCV_RESIZE_MAX_WIDTH', 1920),
            'resize_max_height' => env('OPENCV_RESIZE_MAX_HEIGHT', 1080),
            'contrast_enhancement' => env('OPENCV_CONTRAST_ENHANCEMENT', true),
            'sharpen_enabled' => env('OPENCV_SHARPEN_ENABLED', true),
            'denoise_strength' => env('OPENCV_DENOISE_STRENGTH', 10),
        ],
    ],

];
