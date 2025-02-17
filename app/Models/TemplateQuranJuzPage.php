<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class TemplateQuranJuzPage extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $table = "template_quran_juz_pages";

    protected $primaryKey = 'uuid';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'template_quran_juz_uuid',
        'template_quran_page_uuid',
        'description',
        'constant',
        'value',
    ];
}
