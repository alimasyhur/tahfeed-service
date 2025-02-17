<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportHistory extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $table = "report_histories";

    protected $primaryKey = 'uuid';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_uuid',
        'report_uuid',
        'org_uuid',
        'date_input',
        'name',
        'description',
        'type_report',
        'juz_page_uuid',
        'teacher_uuid',
    ];

}
