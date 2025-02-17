<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class KelasStudent extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $table = "kelas_students";

    protected $primaryKey = 'uuid';

    protected $dates = ['deleted_at'];

    const STATUS_ACTIVE = 'active';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kelas_uuid',
        'student_uuid',
        'org_uuid',
        'notes',
        'status',
        'deleted_at',
    ];
}
