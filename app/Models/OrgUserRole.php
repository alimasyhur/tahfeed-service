<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class OrgUserRole extends Model
{
    use HasUuids;

    protected $table = "orgs_users_roles";

    protected $primaryKey = 'uuid';

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
        'is_active',
        'is_confirmed',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
