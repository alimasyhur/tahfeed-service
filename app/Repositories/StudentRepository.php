<?php

namespace App\Repositories;

use App\Helpers\CommonHelper;
use App\Models\OrgUserRole;
use App\Models\Role;
use App\Models\Student;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class StudentRepository
{
    function __construct(
    ) {}

    private function getQuery($data = null)
    {
        $kelasUUID = Arr::get($data, 'filter.kelas_uuid');

        $baseSelect = [
            'students.*',
            'organizations.name as org_name',
            'grades.period as grade_period',
            DB::raw("CONCAT(students.firstname, ' ', students.lastname) as full_name")
        ];

        $selectWithKelas = array_merge($baseSelect, [
            'kelas_students.kelas_uuid as student_kelas_uuid'
        ]);

        return Student::query()
            ->join('organizations', function($join) {
                $join->on('students.org_uuid', '=', 'organizations.uuid')
                    ->whereNull('organizations.deleted_at');
            })
            ->join('grades', function($join) {
                $join->on('students.grade_uuid', '=', 'grades.uuid')
                    ->whereNull('grades.deleted_at');
            })
            ->when($kelasUUID, function ($query) use ($selectWithKelas, $kelasUUID) {
                $query->join('kelas_students', function($join) use ($kelasUUID) {
                    $join->on('students.uuid', '=', 'kelas_students.student_uuid')
                        ->where('kelas_students.kelas_uuid', $kelasUUID)
                        ->whereNull('kelas_students.deleted_at');
                })
                ->select($selectWithKelas);
            }, function ($query) use ($baseSelect) {
                $query->select($baseSelect);
            })
            ->when(Arr::get($data, 'q'), function ($query, $qWord) {
                $query->where(function ($subQuery) use ($qWord) {
                    // Prioritas pencarian: nama, nis/nik, phone, bio
                    $subQuery->where('students.firstname', 'like', "%{$qWord}%")
                            ->orWhere('students.lastname', 'like', "%{$qWord}%")
                            ->orWhere('students.nis', 'like', "%{$qWord}%")
                            ->orWhere('students.nik', 'like', "%{$qWord}%")
                            ->orWhere('students.phone', 'like', "%{$qWord}%")
                            ->orWhere('students.bio', 'like', "%{$qWord}%")
                            ->orWhere('grades.period', 'like', "%{$qWord}%");

                    // Handle birthdate search dengan exact match untuk performa
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $qWord)) {
                        $subQuery->orWhere('students.birthdate', $qWord);
                    }
                });
            })
            ->when(Arr::get($data, 'filter.nik'), function ($query, $nik) {
                $query->where('students.nik', $nik);
            })
            ->when(Arr::get($data, 'filter.nis'), function ($query, $nis) {
                $query->where('students.nis', $nis);
            })
            ->when(Arr::get($data, 'filter.user_uuid'), function ($query, $userUUID) {
                $query->where('students.user_uuid', $userUUID);
            })
            ->when(Arr::get($data, 'filter.org_uuid'), function ($query, $orgUUID) {
                $query->where('students.org_uuid', $orgUUID);
            })
            ->when(Arr::get($data, 'filter.grade_uuid'), function ($query, $gradeUUID) {
                $query->where('students.grade_uuid', $gradeUUID);
            });
    }

    public function browse($data = null)
    {
        $model = $this->getQuery($data);

        CommonHelper::sortPageFilter($model, $data);

        return $model->get();
    }

    public function find($uuid)
    {
        $profile = Student::join('organizations', 'students.org_uuid', '=', 'organizations.uuid')
            ->join('grades', 'students.grade_uuid', '=', 'grades.uuid')
            ->where('students.uuid', $uuid)
            ->select('students.*', 'organizations.name AS org_name', 'grades.name AS grade_name')
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
            $student->grade_uuid = Arr::get($data, 'grade_uuid');
            $student->nik = Arr::get($data, 'nik');
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
        $student->org_uuid = Arr::get($data, 'org_uuid');
        $student->grade_uuid = Arr::get($data, 'grade_uuid');
        $student->nik = Arr::get($data, 'nik');
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

    public function browseOptions($data = null)
    {
        $model = $this->getQuery($data);

        CommonHelper::sortPageFilter($model, $data);

        return $model->get();
    }
}
