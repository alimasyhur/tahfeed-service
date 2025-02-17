<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrgUserRole extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $table = "orgs_users_roles";

    protected $primaryKey = 'uuid';

    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'org_uuid',
        'org_name',
        'user_uuid',
        'role_uuid',
        'role_name',
        'constant_value',
        'is_active',
        'is_confirmed',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
