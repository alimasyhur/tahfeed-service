<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Repositories\ImageUploadRepository;

class Profile extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = "profiles";

    protected $primaryKey = 'uuid';

    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_uuid',
        'firstname',
        'lastname',
        'birthdate',
        'phone',
        'bio',
        'profile_image',
    ];

     /**
     * Get the profile image URL
     *
     * @return string|null
     */
    public function getProfileImageUrlAttribute(): ?string
    {
        if (!$this->profile_image) {
            return null;
        }

        $imageService = app(ImageUploadRepository::class);
        return $imageService->getImageUrl('user', $this->user_uuid, $this->profile_image);
    }

    /**
     * Get the profile image thumbnail URL
     *
     * @return string|null
     */
    public function getProfileImageThumbnailUrlAttribute(): ?string
    {
        if (!$this->profile_image) {
            return null;
        }

        $imageService = app(ImageUploadRepository::class);
        return $imageService->getImageUrl('user', $this->user_uuid, $this->profile_image, true);
    }
}
