<?php

namespace App\Repositories;

use App\Constants\RoleUserStatus;
use App\Helpers\CommonHelper;
use App\Models\OrgUserRole;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RoleUserRepository
{

    function __construct() {
    }

    private function getQuery($data = null)
    {
        $model = OrgUserRole::join('users', 'orgs_users_roles.user_uuid', '=', 'users.uuid')
            ->select(
                'orgs_users_roles.*',
                'users.name as user_name',
                'users.email as email'
            );

        $qWord = Arr::get($data, 'q');
        if (!empty($qWord)) {
            $model->where(function ($query) use ($qWord) {
                $query->where('user_uuid', 'like', "%$qWord%");
                $query->orWhere('user_uuid', 'like', "%$qWord%");
            });
        }

        $uuid = Arr::get($data, 'filter.uuid');
        if (!empty($name)) {
            $model->where('uuid', '=', $uuid);
        }

        $roleUuid = Arr::get($data, 'filter.role_uuid');
        if (!empty($roleUuid)) {
            $model->where('role_uuid', '=', $roleUuid);
        }

        $userUuid = Arr::get($data, 'filter.user_uuid');
        if (!empty($userUuid)) {
            $model->where('user_uuid', '=', $userUuid);
        }

        $orgUuid = Arr::get($data, 'filter.org_uuid');
        if (!empty($orgUuid)) {
            $model->where('org_uuid', '=', $orgUuid);
        }

        return $model;
    }

    public function browse($data = null)
    {
        $model = $this->getQuery($data);

        CommonHelper::sortPageFilter($model, $data);

        $response = $model->get();
        $response->map(function($model){
            if ($model->is_active == 1) {
                $model->is_active_label = 'active';
                $model->is_active_label_color = 'info';
            } else {
                $model->is_active_label = 'not active';
                $model->is_active_label_color = 'warning';
            }

            if ($model->is_confirmed == 1) {
                $model->is_confirmed_label = 'confirmed';
                $model->is_confirmed_label_color = 'info';
            } else {
                $model->is_confirmed_label = 'not confirmed';
                $model->is_confirmed_label_color = 'warning';
            }


        });

        return $response;
    }

    public function find($uuid)
    {
        $role = OrgUserRole::where('uuid', $uuid)
            ->first();

        return $role;
    }

    public function add($data)
    {
        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => Arr::get($data, 'user_name'),
                'email' => Arr::get($data, 'email'),
                'password' => Hash::make(Arr::get($data, 'password')),
            ]);

            $model = new OrgUserRole();
            $model->user_uuid = $user->uuid;
            $model->org_uuid = Arr::get($data, 'org_uuid');
            $model->org_name = Arr::get($data, 'org_name');
            $model->role_uuid = Arr::get($data, 'role_uuid');
            $model->role_name = Arr::get($data, 'role_name');
            $model->is_active = Arr::get($data, 'is_active');
            $model->is_confirmed = Arr::get($data, 'is_confirmed');
            $model->save();

            DB::commit();

            return $model->find($model->uuid);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update($uuid, $data)
    {
        DB::transaction(function () use($uuid, $data) {
            DB::table('orgs_users_roles')->where('uuid', $uuid)->update([
                'uuid' => $uuid,
                'org_uuid' => Arr::get($data, 'org_uuid'),
                'org_name' => Arr::get($data, 'org_name'),
                'role_uuid' => Arr::get($data, 'role_uuid'),
                'role_name' => Arr::get($data, 'role_name'),
                'is_active' => Arr::get($data, 'is_active'),
                'is_confirmed' => Arr::get($data, 'is_confirmed'),
                'updated_at' => Carbon::now(),
            ]);
            DB::table('users')->where('uuid', Arr::get($data, 'user_uuid'))
                ->update([
                    'name' => Arr::get($data, 'user_name'),
                    'email' => Arr::get($data, 'email'),
                    'updated_at' => Carbon::now(),
                ]);
        });

        $modelUser = User::findOrFail(Arr::get($data, 'user_uuid'));

        $model = OrgUserRole::findOrFail($uuid);
        $model->user_name = $modelUser->name;
        $model->email = $modelUser->email;

        return $model;
    }

    public function delete(OrgUserRole $roleUser)
    {
        return $roleUser->delete();
    }

    public function count($data)
    {
        $model = $this->getQuery($data);
        return $model->count();
    }

    public static function isSuperAdmin($userUuid)
    {
        $role = OrgUserRole::where('user_uuid', $userUuid)
            ->where('role_name', 'Super Admin')
            ->count();

        return $role > 0;
    }

    public static function isAllowedChangeRoleAdmin($userOrgRole)
    {
        $role = OrgUserRole::where('org_uuid', $userOrgRole->org_uuid)
            ->where('uuid', '!=', $userOrgRole->uuid)
            ->where('role_name', 'Admin')
            ->count();

        return $role > 0;
    }

    public function findByUserUuid($userUuid)
    {
        $role = OrgUserRole::where('user_uuid', $userUuid)
            ->first();

        return $role;
    }

    public function isActiveRole($userUuid)
    {
        $hasActiveRole = OrgUserRole::where('user_uuid', $userUuid)
            ->where('is_active', 1)
            ->count();

        return $hasActiveRole > 0;
    }

    public function assign($data)
    {
        try {
            $model = new OrgUserRole();
            $model->user_uuid = Arr::get($data, 'user_uuid');
            $model->org_uuid = Arr::get($data, 'org_uuid');
            $model->org_name = Arr::get($data, 'org_name');
            $model->role_uuid = Arr::get($data, 'role_uuid');
            $model->role_name = Arr::get($data, 'role_name');
            $model->is_active = 1;
            $model->is_confirmed = 1;
            $model->save();

            return $model->find($model->uuid);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function isAlreadyAssigned($data)
    {
        $role = OrgUserRole::where('org_uuid', Arr::get($data, 'org_uuid'))
            ->where('user_uuid', Arr::get($data, 'user_uuid'))
            ->where('role_uuid', Arr::get($data, 'role_uuid'))
            ->count();

        return $role > 0;
    }

    public function browseOptions($data = null)
    {
        $model = $this->getQuery($data);

        CommonHelper::sortPageFilter($model, $data);

        $userUUIDList = $model->pluck('user_uuid')->toArray();

        $response = User::select('uuid', 'email')->whereIn('uuid', $userUUIDList)->get();

        return $response;
    }
}
