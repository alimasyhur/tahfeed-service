<?php

namespace App\Repositories;

use App\Helpers\CommonHelper;
use App\Models\Role;
use Illuminate\Support\Arr;

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
        $model = Role::findOrFail($uuid);
        $model->update($data);

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
