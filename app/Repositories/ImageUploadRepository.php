<?php

namespace App\Repositories;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver; // or use Imagick\Driver
use Exception;

class ImageUploadRepository
{
    private const PROFILE_MAX_WIDTH = 800;
    private const PROFILE_MAX_HEIGHT = 800;
    private const THUMBNAIL_WIDTH = 200;
    private const THUMBNAIL_HEIGHT = 200;
    private const QUALITY = 85;
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif'];
    private const MAX_FILE_SIZE = 5242880; // 5MB in bytes

    private ImageManager $imageManager;

    public function __construct()
    {
        // Initialize the ImageManager with the appropriate driver
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Upload user profile image with processing
     *
     * @param string $userUuid
     * @param UploadedFile $image
     * @return array
     * @throws Exception
     */
    public function uploadUserProfileImage(string $userUuid, UploadedFile $image): array
    {
        try {
            // Validate image
            $this->validateImage($image);

            // Generate unique filename
            $filename = $this->generateFilename($image->getClientOriginalExtension());

            // Define paths
            $basePath = "images/users/{$userUuid}/profiles";
            $imagePath = "{$basePath}/{$filename}";
            $thumbnailPath = "{$basePath}/thumbnails/{$filename}";

            // Create directories if they don't exist
            $this->ensureDirectoryExists($basePath);
            $this->ensureDirectoryExists("{$basePath}/thumbnails");

            // Process and save main image
            $this->processAndSaveImage($image, $imagePath, self::PROFILE_MAX_WIDTH, self::PROFILE_MAX_HEIGHT);

            // Create and save thumbnail
            $this->createAndSaveThumbnail($image, $thumbnailPath);

            // Log successful upload
            Log::info("Profile image uploaded successfully", [
                'user_uuid' => $userUuid,
                'filename' => $filename,
                'original_name' => $image->getClientOriginalName(),
                'size' => $image->getSize()
            ]);

            return [
                'filename' => $filename,
                'url' => Storage::disk('public')->url($imagePath),
                'thumbnail_url' => Storage::disk('public')->url($thumbnailPath),
                'path' => $imagePath,
                'thumbnail_path' => $thumbnailPath,
                'original_name' => $image->getClientOriginalName(),
                'size' => $image->getSize(),
                'mime_type' => $image->getMimeType()
            ];

        } catch (Exception $e) {
            Log::error("Profile image upload failed", [
                'user_uuid' => $userUuid,
                'error' => $e->getMessage(),
                'file' => $image->getClientOriginalName() ?? 'unknown'
            ]);
            throw new Exception("Failed to upload profile image: " . $e->getMessage());
        }
    }

    /**
     * Upload organization logo image with processing
     *
     * @param string $orgUuid
     * @param UploadedFile $image
     * @return array
     * @throws Exception
     */
    public function uploadOrganizationLogo(string $orgUuid, UploadedFile $image): array
    {
        try {
            // Validate image
            $this->validateImage($image);

            // Generate unique filename
            $filename = $this->generateFilename($image->getClientOriginalExtension());

            // Define paths
            $basePath = "images/organizations/{$orgUuid}/logos";
            $imagePath = "{$basePath}/{$filename}";
            $thumbnailPath = "{$basePath}/thumbnails/{$filename}";

            // Create directories if they don't exist
            $this->ensureDirectoryExists($basePath);
            $this->ensureDirectoryExists("{$basePath}/thumbnails");

            // Process and save main image
            $this->processAndSaveImage($image, $imagePath, self::PROFILE_MAX_WIDTH, self::PROFILE_MAX_HEIGHT);

            // Create and save thumbnail
            $this->createAndSaveThumbnail($image, $thumbnailPath);

            // Log successful upload
            Log::info("Organization logo uploaded successfully", [
                'org_uuid' => $orgUuid,
                'filename' => $filename,
                'original_name' => $image->getClientOriginalName(),
                'size' => $image->getSize()
            ]);

            return [
                'filename' => $filename,
                'url' => Storage::disk('public')->url($imagePath),
                'thumbnail_url' => Storage::disk('public')->url($thumbnailPath),
                'path' => $imagePath,
                'thumbnail_path' => $thumbnailPath,
                'original_name' => $image->getClientOriginalName(),
                'size' => $image->getSize(),
                'mime_type' => $image->getMimeType()
            ];

        } catch (Exception $e) {
            Log::error("Organization logo upload failed", [
                'org_uuid' => $orgUuid,
                'error' => $e->getMessage(),
                'file' => $image->getClientOriginalName() ?? 'unknown'
            ]);
            throw new Exception("Failed to upload organization logo: " . $e->getMessage());
        }
    }

    /**
     * Delete user image files
     *
     * @param string $userUuid
     * @param string $filename
     * @return bool
     */
    public function deleteUserImage(string $userUuid, string $filename): bool
    {
        try {
            $basePath = "images/users/{$userUuid}/profiles";
            $imagePath = "{$basePath}/{$filename}";
            $thumbnailPath = "{$basePath}/thumbnails/{$filename}";

            $deleted = false;

            // Delete main image
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
                $deleted = true;
            }

            // Delete thumbnail
            if (Storage::disk('public')->exists($thumbnailPath)) {
                Storage::disk('public')->delete($thumbnailPath);
                $deleted = true;
            }

            if ($deleted) {
                Log::info("User image deleted successfully", [
                    'user_uuid' => $userUuid,
                    'filename' => $filename
                ]);
            }

            return $deleted;

        } catch (Exception $e) {
            Log::error("Failed to delete user image", [
                'user_uuid' => $userUuid,
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Delete organization image files
     *
     * @param string $orgUuid
     * @param string $filename
     * @return bool
     */
    public function deleteOrganizationImage(string $orgUuid, string $filename): bool
    {
        try {
            $basePath = "images/organizations/{$orgUuid}/logos";
            $imagePath = "{$basePath}/{$filename}";
            $thumbnailPath = "{$basePath}/thumbnails/{$filename}";

            $deleted = false;

            // Delete main image
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
                $deleted = true;
            }

            // Delete thumbnail
            if (Storage::disk('public')->exists($thumbnailPath)) {
                Storage::disk('public')->delete($thumbnailPath);
                $deleted = true;
            }

            if ($deleted) {
                Log::info("Organization image deleted successfully", [
                    'org_uuid' => $orgUuid,
                    'filename' => $filename
                ]);
            }

            return $deleted;

        } catch (Exception $e) {
            Log::error("Failed to delete organization image", [
                'org_uuid' => $orgUuid,
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Validate uploaded image
     *
     * @param UploadedFile $image
     * @throws Exception
     */
    private function validateImage(UploadedFile $image): void
    {
        // Check if file is valid
        if (!$image->isValid()) {
            throw new Exception('Invalid image file uploaded');
        }

        // Check file size
        if ($image->getSize() > self::MAX_FILE_SIZE) {
            throw new Exception('Image file size exceeds maximum allowed size of 5MB');
        }

        // Check file extension
        $extension = strtolower($image->getClientOriginalExtension());
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            throw new Exception('Invalid image format. Only JPG, PNG, and GIF are allowed');
        }

        // Check mime type
        $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($image->getMimeType(), $allowedMimeTypes)) {
            throw new Exception('Invalid image mime type');
        }

        // Check if it's actually an image using getimagesize
        $imageInfo = getimagesize($image->getPathname());
        if ($imageInfo === false) {
            throw new Exception('File is not a valid image');
        }

        // Check minimum dimensions
        if ($imageInfo[0] < 100 || $imageInfo[1] < 100) {
            throw new Exception('Image dimensions must be at least 100x100 pixels');
        }
    }

    /**
     * Process and save main image
     *
     * @param UploadedFile $image
     * @param string $path
     * @param int $maxWidth
     * @param int $maxHeight
     * @return void
     */
    private function processAndSaveImage(UploadedFile $image, string $path, int $maxWidth, int $maxHeight): void
    {
        $img = $this->imageManager->read($image->getPathname());

        // Resize image if larger than max dimensions while maintaining aspect ratio
        $img->scaleDown($maxWidth, $maxHeight);

        // Convert to JPEG and set quality
        $encoded = $img->toJpeg(self::QUALITY);

        // Save to storage
        Storage::disk('public')->put($path, $encoded);
    }

    /**
     * Create and save thumbnail image
     *
     * @param UploadedFile $image
     * @param string $path
     * @return void
     */
    private function createAndSaveThumbnail(UploadedFile $image, string $path): void
    {
        $img = $this->imageManager->read($image->getPathname());

        // Create square thumbnail with cover (crop to fit)
        $img->cover(self::THUMBNAIL_WIDTH, self::THUMBNAIL_HEIGHT);

        // Convert to JPEG and set quality
        $encoded = $img->toJpeg(self::QUALITY);

        // Save to storage
        Storage::disk('public')->put($path, $encoded);
    }

    /**
     * Generate unique filename
     *
     * @param string $extension
     * @return string
     */
    private function generateFilename(string $extension): string
    {
        $uuid = Str::uuid();
        $timestamp = time();
        $extension = strtolower($extension);

        // Ensure extension is jpg for consistency
        if (in_array($extension, ['jpeg'])) {
            $extension = 'jpg';
        }

        return "{$uuid}_{$timestamp}.{$extension}";
    }

    /**
     * Ensure directory exists
     *
     * @param string $path
     * @return void
     */
    private function ensureDirectoryExists(string $path): void
    {
        if (!Storage::disk('public')->exists($path)) {
            Storage::disk('public')->makeDirectory($path, 0755, true);
        }
    }

    /**
     * Get image URL
     *
     * @param string $type (user|organization)
     * @param string $uuid
     * @param string $filename
     * @param bool $thumbnail
     * @return string|null
     */
    public function getImageUrl(string $type, string $uuid, string $filename, bool $thumbnail = false): ?string
    {
        if (!$filename) {
            return null;
        }

        if ($type === 'user') {
            $path = $thumbnail
                ? "images/users/{$uuid}/profiles/thumbnails/{$filename}"
                : "images/users/{$uuid}/profiles/{$filename}";
        } else {
            $path = $thumbnail
                ? "images/organizations/{$uuid}/logos/thumbnails/{$filename}"
                : "images/organizations/{$uuid}/logos/{$filename}";
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return null;
    }

    /**
     * Get image file info
     *
     * @param string $type
     * @param string $uuid
     * @param string $filename
     * @return array|null
     */
    public function getImageInfo(string $type, string $uuid, string $filename): ?array
    {
        if (!$filename) {
            return null;
        }

        if ($type === 'user') {
            $path = "images/users/{$uuid}/profiles/{$filename}";
            $thumbnailPath = "images/users/{$uuid}/profiles/thumbnails/{$filename}";
        } else {
            $path = "images/organizations/{$uuid}/logos/{$filename}";
            $thumbnailPath = "images/organizations/{$uuid}/logos/thumbnails/{$filename}";
        }

        if (!Storage::disk('public')->exists($path)) {
            return null;
        }

        $size = Storage::disk('public')->size($path);
        $lastModified = Storage::disk('public')->lastModified($path);
        $url = Storage::disk('public')->url($path);
        $thumbnailUrl = Storage::disk('public')->exists($thumbnailPath)
            ? Storage::disk('public')->url($thumbnailPath)
            : null;

        return [
            'filename' => $filename,
            'size' => $size,
            'url' => $url,
            'thumbnail_url' => $thumbnailUrl,
            'last_modified' => $lastModified,
            'formatted_size' => $this->formatBytes($size)
        ];
    }

    /**
     * Format bytes to human readable format
     *
     * @param int $bytes
     * @return string
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Bulk delete images (useful for cleanup)
     *
     * @param string $type
     * @param string $uuid
     * @return int Number of files deleted
     */
    public function bulkDeleteImages(string $type, string $uuid): int
    {
        try {
            if ($type === 'user') {
                $basePath = "images/users/{$uuid}";
            } else {
                $basePath = "images/organizations/{$uuid}";
            }

            $deleted = 0;

            if (Storage::disk('public')->exists($basePath)) {
                $files = Storage::disk('public')->allFiles($basePath);
                foreach ($files as $file) {
                    Storage::disk('public')->delete($file);
                    $deleted++;
                }

                // Delete empty directories
                Storage::disk('public')->deleteDirectory($basePath);
            }

            Log::info("Bulk delete completed", [
                'type' => $type,
                'uuid' => $uuid,
                'files_deleted' => $deleted
            ]);

            return $deleted;

        } catch (Exception $e) {
            Log::error("Bulk delete failed", [
                'type' => $type,
                'uuid' => $uuid,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }
}

