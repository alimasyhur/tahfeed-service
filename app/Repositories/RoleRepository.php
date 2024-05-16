<?php

namespace App\Repositories;

use App\Helpers\CommonHelper;
use App\Models\Role;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class RoleRepository
{
    function __construct(
    ) {}

    private function getQuery($data = null)
    {
        $model = Role::query();

        $qWord = Arr::get($data, 'q');
        if (!empty($qWord)) {
            $model->where(function ($query) use ($qWord) {
                $query->where('name', 'like', "%$qWord%");
            });
        }

        $name = Arr::get($data, 'filter.name');
        if (!empty($name)) {
            $model->where('name', 'like', "%$name%");
        }

        $uuid = Arr::get($data, 'filter.uuid');
        if (!empty($uuid)) {
            $model->where('uuid', '=', "$uuid");
        }

        $roleName = Arr::get($data, 'filter.role_name');
        if (!empty($roleName) && ($roleName !== Role::ROLE_SUPER_ADMIN)) {
            $model->where('name', '!=', Role::ROLE_SUPER_ADMIN);
        }

        return $model;
    }

    public function browse($data = null)
    {
        $model = $this->getQuery($data);

        CommonHelper::sortPageFilter($model, $data);

        return $model->get();
    }

    public function find($uuid)
    {
        $role = Role::where('uuid', $uuid)
            ->first();

        return $role;
    }

    public function findByName($name)
    {
        $role = Role::where('name', $name)
            ->first();

        return $role;
    }

    public function add($data)
    {
        $model = new Role();
        $model->name = Arr::get($data, 'name');
        $model->save();

        return $model;
    }

    public function update($uuid, $data)
    {
        DB::transaction(function () use($uuid, $data) {
            DB::table('roles')->where('uuid', $uuid)->update($data);
            DB::table('orgs_users_roles')->where('role_uuid', $uuid)->update(['role_name' => Arr::get($data, 'name')]);
        });

        $model = Role::findOrFail($uuid);

        $model->fresh();
        return $model;
    }

    public function delete(Role $role)
    {
        return $role->delete();
    }

    public function count($data)
    {
        $model = $this->getQuery($data);
        return $model->count();
    }
}
