<?php

namespace App\Models;

use App\Repositories\ImageUploadRepository;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $table = "organizations";

    protected $primaryKey = 'uuid';

    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'domain',
        'bio',
        'address',
        'email',
        'phone',
        'is_verified',
        'is_active',
        'created_by',
        'logo_image',
    ];

    /**
     * Get the logo image URL
     *
     * @return string|null
     */
    public function getLogoImageUrlAttribute(): ?string
    {
        if (!$this->logo_image) {
            return null;
        }

        $imageService = app(ImageUploadRepository::class);
        return $imageService->getImageUrl('organization', $this->uuid, $this->logo_image);
    }

    /**
     * Get the logo image thumbnail URL
     *
     * @return string|null
     */
    public function getLogoImageThumbnailUrlAttribute(): ?string
    {
        if (!$this->logo_image) {
            return null;
        }

        $imageService = app(ImageUploadRepository::class);
        return $imageService->getImageUrl('organization', $this->uuid, $this->logo_image, true);
    }

}
