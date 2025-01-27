<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $table = "reports";

    protected $primaryKey = 'uuid';

    const TYPE_ZIYADAH = 'ziyadah';
    const TYPE_MUROJAAH = 'murojaah';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_uuid',
        'org_uuid',
        'kelas_uuid',
        'teacher_uuid',
        'start_juz_page_uuid',
        'end_juz_page_uuid',
        'date_input',
        'name',
        'description',
        'type_report',
        'note',
        'is_locked',
        'locked_at',
    ];
}
