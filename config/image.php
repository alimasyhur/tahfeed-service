<?php

// config/image.php
return [
    /*
    |--------------------------------------------------------------------------
    | Image Driver
    |--------------------------------------------------------------------------
    | Intervention Image supports "GD Library" and "Imagick" to process images
    | internally. You may choose one of them according to your PHP
    | configuration. By default PHP's "GD Library" implementation is used.
    |
    | Supported: "gd", "imagick"
    |
    */
    'driver' => env('IMAGE_DRIVER', 'gd'),

    /*
    |--------------------------------------------------------------------------
    | Image Processing Options
    |--------------------------------------------------------------------------
    | Here you may specify the default options for image processing
    |
    */
    'options' => [
        'jpeg_quality' => env('IMAGE_JPEG_QUALITY', 85),
        'png_compression_level' => env('IMAGE_PNG_COMPRESSION', 6),
        'webp_quality' => env('IMAGE_WEBP_QUALITY', 85),
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Size Limits
    |--------------------------------------------------------------------------
    | Configure the maximum dimensions and file size for uploaded images
    |
    */
    'limits' => [
        'max_width' => env('IMAGE_MAX_WIDTH', 2048),
        'max_height' => env('IMAGE_MAX_HEIGHT', 2048),
        'max_file_size' => env('IMAGE_MAX_FILE_SIZE', 5242880), // 5MB
        'min_width' => env('IMAGE_MIN_WIDTH', 100),
        'min_height' => env('IMAGE_MIN_HEIGHT', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Image Types
    |--------------------------------------------------------------------------
    | Define which image types are allowed for upload
    |
    */
    'allowed_types' => [
        'extensions' => ['jpg', 'jpeg', 'png', 'gif'],
        'mime_types' => ['image/jpeg', 'image/png', 'image/gif'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Variants
    |--------------------------------------------------------------------------
    | Define different image variants (sizes) that should be generated
    |
    */
    'variants' => [
        'profile' => [
            'original' => [
                'width' => 800,
                'height' => 800,
                'quality' => 85,
            ],
            'thumbnail' => [
                'width' => 200,
                'height' => 200,
                'quality' => 85,
                'crop' => true,
            ],
            'medium' => [
                'width' => 400,
                'height' => 400,
                'quality' => 85,
            ],
        ],
        'organization' => [
            'original' => [
                'width' => 800,
                'height' => 600,
                'quality' => 85,
            ],
            'thumbnail' => [
                'width' => 200,
                'height' => 200,
                'quality' => 85,
                'crop' => true,
            ],
            'logo' => [
                'width' => 300,
                'height' => 200,
                'quality' => 90,
            ],
        ],
    ],
];
