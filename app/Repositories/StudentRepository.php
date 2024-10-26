<?php

namespace App\Repositories;

use App\Helpers\CommonHelper;
use App\Models\OrgUserRole;
use App\Models\Role;
use App\Models\Student;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class StudentRepository
{
    function __construct(
    ) {}

    private function getQuery($data = null)
    {
        $model = Student::join('organizations', 'students.org_uuid', '=', 'organizations.uuid')
            ->select('students.*', 'organizations.name as org_name');

        $qWord = Arr::get($data, 'q');
        if (!empty($qWord)) {
            $model->where(function ($query) use ($qWord) {
                $query->where('students.nis', 'like', "%$qWord%")
                    ->orWhere('students.firstname', 'like', "%$qWord%")
                    ->orWhere('students.lastname', 'like', "%$qWord%")
                    ->orWhere('students.birthdate', 'like', "%$qWord%")
                    ->orWhere('students.phone', 'like', "%$qWord%")
                    ->orWhere('students.bio', 'like', "%$qWord%");
            });
        }

        $nis = Arr::get($data, 'filter.nis');
        if (!empty($nis)) {
            $model->where('students.nis', $nis);
        }

        $userUUID = Arr::get($data, 'filter.user_uuid');
        if (!empty($userUUID)) {
            $model->where('students.user_uuid', $userUUID);
        }

        $orgUUID = Arr::get($data, 'filter.org_uuid');
        if (!empty($orgUUID)) {
            $model->where('students.org_uuid', $orgUUID);
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
        $profile = Student::where('uuid', $uuid)
            ->first();

        return $profile;
    }

    public function add($data)
    {
        DB::beginTransaction();
        try {
            $student = new Student();
            $student->user_uuid = Arr::get($data, 'user_uuid');
            $student->org_uuid = Arr::get($data, 'org_uuid');
            $student->nis = Arr::get($data, 'nis');
            $student->firstname = Arr::get($data, 'firstname');
            $student->lastname = Arr::get($data, 'lastname');
            $student->birthdate = Arr::get($data, 'birthdate');
            $student->phone = Arr::get($data, 'phone');
            $student->bio = Arr::get($data, 'bio');

            $student->save();

            $model = new OrgUserRole();
            $model->user_uuid = Arr::get($data, 'user_uuid');
            $model->org_uuid = Arr::get($data, 'org_uuid');
            $model->org_name = Arr::get($data, 'org_name');
            $model->role_uuid = Arr::get($data, 'role_uuid');
            $model->role_name = Arr::get($data, 'role_name');
            $model->constant_value = Arr::get($data, 'constant_value');
            $model->is_active = Role::ACTIVE;
            $model->is_confirmed = Role::CONFIRMED;
            $model->save();

            DB::commit();

            return $student;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function addStudent($data)
    {
        $student = new Student();
        $student->user_uuid = Arr::get($data, 'user_uuid');
        $student->org_uuid = Arr::get($data, 'org_uuid');
        $student->nis = Arr::get($data, 'nis');
        $student->firstname = Arr::get($data, 'firstname');
        $student->lastname = Arr::get($data, 'lastname');
        $student->birthdate = Arr::get($data, 'birthdate');
        $student->phone = Arr::get($data, 'phone');
        $student->bio = Arr::get($data, 'bio');

        $student->save();

        return $student;
    }

    public function update($uuid, $data)
    {
        $student = Student::findOrFail($uuid);
        $student->update($data);

        $student->fresh();
        return $student;
    }

    public function delete(Student $student)
    {
        return $student->delete();
    }

    public function count($data)
    {
        $model = $this->getQuery($data);
        return $model->count();
    }
}
