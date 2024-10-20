<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    ];
}
