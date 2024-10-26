<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $table = "roles";

    protected $primaryKey = 'uuid';

    const ROLE_SUPER_ADMIN = "Super Admin";
    const ROLE_ADMIN = "Admin";
    const ROLE_TEACHER = "Teacher";
    const ROLE_STUDENT = "Student";

    const ACTIVE = 1;
    const CONFIRMED = 1;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'constant_value',
    ];
}
