<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasUuids;

    protected $table = "organizations";

    protected $primaryKey = 'uuid';

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
    ];
}
