<?php

namespace App\Repositories;

use App\Constants\RoleUserStatus;
use App\Helpers\CommonHelper;
use App\Models\OrgUserRole;
use App\Models\Role;
use Illuminate\Support\Arr;

class RoleUserRepository
{

    function __construct() {
    }

    private function getQuery($data = null)
    {
        $model = OrgUserRole::query();

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
        $model = new OrgUserRole();
        $model->user_uuid = Arr::get($data, 'user_uuid');
        $model->org_uuid = Arr::get($data, 'org_uuid');
        $model->org_name = Arr::get($data, 'org_name');
        $model->role_uuid = Arr::get($data, 'role_uuid');
        $model->role_name = Arr::get($data, 'role_name');
        $model->is_active = RoleUserStatus::ACTIVE;
        $model->is_confirmed = RoleUserStatus::VERIFICATION_PENDING;
        $model->save();

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
}
