<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kelas extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $table = "kelas";

    protected $primaryKey = 'uuid';

    protected $dates = ['deleted_at'];

    const STATUS_NOT_STARTED = 'not_started';
    const STATUS_ACTIVE = 'active';
    const STATUS_REMOVED = 'removed';
    const STATUS_FINISHED = 'finished';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'teacher_uuid',
        'org_uuid',
        'grade_uuid',
        'status',
        'total_juz_target',
        'start_date',
        'end_date',
    ];
}
