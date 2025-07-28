<?php

namespace App\Repositories;

use App\Helpers\CommonHelper;
use App\Models\OrgUserRole;
use App\Models\Role;
use App\Models\Teacher;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class TeacherRepository
{
    function __construct(
    ) {}

    private function getQuery($data = null)
    {
        $model = Teacher::join('organizations', 'teachers.org_uuid', '=', 'organizations.uuid')
            ->select('teachers.*', 'organizations.name as org_name');

        $qWord = Arr::get($data, 'q');
        if (!empty($qWord)) {
            $model->where(function ($query) use ($qWord) {
                $query->where('teachers.nik', 'like', "%$qWord%")
                    ->orWhere('teachers.firstname', 'like', "%$qWord%")
                    ->orWhere('teachers.lastname', 'like', "%$qWord%")
                    ->orWhere('teachers.birthdate', 'like', "%$qWord%")
                    ->orWhere('teachers.phone', 'like', "%$qWord%")
                    ->orWhere('teachers.bio', 'like', "%$qWord%");
            });
        }

        $userUUID = Arr::get($data, 'filter.user_uuid');
        if (!empty($userUUID)) {
            $model->where('teachers.nik', $userUUID);
        }

        $orgUUID = Arr::get($data, 'filter.org_uuid');
        if (!empty($orgUUID)) {
            $model->where('teachers.org_uuid', $orgUUID);
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
        $profile = Teacher::where('uuid', $uuid)
            ->first();

        return $profile;
    }

    public function add($data)
    {
        DB::beginTransaction();
        try {
            $orgUUID = Arr::get($data, 'org_uuid');
            $userUUID = Arr::get($data, 'user_uuid');
            $roleUUID = Arr::get($data, 'role_uuid');

            $teacher = new Teacher();
            $teacher->user_uuid = $userUUID;
            $teacher->org_uuid = $orgUUID;
            $teacher->nik = Arr::get($data, 'nik');
            $teacher->firstname = Arr::get($data, 'firstname');
            $teacher->lastname = Arr::get($data, 'lastname');
            $teacher->birthdate = Arr::get($data, 'birthdate');
            $teacher->phone = Arr::get($data, 'phone');
            $teacher->bio = Arr::get($data, 'bio');

            $teacher->save();

            $existingUserRole = OrgUserRole::where('org_uuid', $orgUUID)
                ->where('user_uuid', $userUUID)
                ->where('role_uuid', $roleUUID)
                ->first();

            if (!$existingUserRole) {
                $model = new OrgUserRole();
                $model->user_uuid = $userUUID;
                $model->org_uuid = $orgUUID;
                $model->org_name = Arr::get($data, 'org_name');
                $model->role_uuid = $roleUUID;
                $model->role_name = Arr::get($data, 'role_name');
                $model->constant_value = Arr::get($data, 'constant_value');
                $model->is_active = Role::ACTIVE;
                $model->is_confirmed = Role::CONFIRMED;
                $model->save();
            }

            DB::commit();

            return $teacher;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update($uuid, $data)
    {
        $teacher = Teacher::findOrFail($uuid);
        $teacher->update($data);

        $teacher->fresh();
        return $teacher;
    }

    public function delete(Teacher $teacher)
    {
        DB::beginTransaction();
        try {
            OrgUserRole::where('user_uuid', $teacher->user_uuid)
                ->where('org_uuid', $teacher->org_uuid)
                ->where('role_name', 'Teacher')
                ->delete();

            $teacher->delete();

            DB::commit();

            return $teacher;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $teacher;
    }

    public function count($data)
    {
        $model = $this->getQuery($data);
        return $model->count();
    }
}
