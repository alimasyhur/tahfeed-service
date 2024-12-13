<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class TemplateQuran extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $table = "template_qurans";

    protected $primaryKey = 'uuid';

    const PER_HALAMAN = 'per_halaman';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'slug',
    ];
}
