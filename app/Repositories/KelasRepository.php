<?php

namespace App\Repositories;

use App\Helpers\CommonHelper;
use App\Models\Kelas;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class KelasRepository
{
    function __construct(
    ) {}

    private function getQuery($data = null)
    {
        $model = Kelas::join('organizations', 'kelas.org_uuid', '=', 'organizations.uuid')
            ->join('grades', 'kelas.grade_uuid', '=', 'grades.uuid')
            ->select(
                'kelas.*',
                'organizations.name as org_name',
                'grades.period as period',
            );

        $qWord = Arr::get($data, 'q');
        if (!empty($qWord)) {
            $model->where(function ($query) use ($qWord) {
                $query->where('kelas.name', 'like', "%$qWord%");
                $query->orWhere('kelas.description', 'like', "%$qWord%");
            });
        }

        $name = Arr::get($data, 'filter.name');
        if (!empty($name)) {
            $model->where('kelas.name', 'like', "%$name%");
        }

        $uuid = Arr::get($data, 'filter.uuid');
        if (!empty($uuid)) {
            $model->where('kelas.uuid', '=', "$uuid");
        }

        $teacherUuid = Arr::get($data, 'filter.teacher_uuid');
        if (!empty($teacherUuid)) {
            $model->where('kelas.teacher_uuid', $teacherUuid);
        }

        $orgUuid = Arr::get($data, 'filter.org_uuid');
        if (!empty($orgUuid)) {
            $model->where('kelas.org_uuid', $orgUuid);
        }

        $gradeUuid = Arr::get($data, 'filter.grade_uuid');
        if (!empty($gradeUuid)) {
            $model->where('kelas.grade_uuid', '=', "$gradeUuid");
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
        $kelas = Kelas::join('organizations', 'kelas.org_uuid', '=', 'organizations.uuid')
            ->join('grades', 'kelas.grade_uuid', '=', 'grades.uuid')
            ->join('teachers', 'kelas.teacher_uuid', '=', 'teachers.uuid')
            ->select(
                'kelas.*',
                'organizations.name as org_name',
                'grades.name as grade_name',
                'grades.period as grade_period',
                'teachers.nik as teacher_nik',
                'teachers.firstname as teacher_firstname',
                'teachers.lastname as teacher_lastname',
            )->where('kelas.uuid', $uuid)
            ->first();

        return $kelas;
    }

    public function findActiveKelasByUserUUID($userUUID)
    {
        $kelas = Kelas::join('teachers', 'kelas.teacher_uuid', '=', 'teachers.uuid')
            ->select(
                'kelas.*',
                'teachers.user_uuid as teacher_user_uuid',
                'teachers.nik as teacher_nik',
                'teachers.firstname as teacher_firstname',
                'teachers.lastname as teacher_lastname',
            )->where('teachers.user_uuid', $userUUID)
            ->where('kelas.status', Kelas::STATUS_ACTIVE)
            ->first();

        return $kelas;
    }

    public function findByName($name)
    {
        $grade = Kelas::where('name', $name)
            ->first();

        return $grade;
    }

    public function add($data)
    {
        $model = new Kelas();
        $model->name = Arr::get($data, 'name');
        $model->description = Arr::get($data, 'description');
        $model->teacher_uuid = Arr::get($data, 'teacher_uuid');
        $model->org_uuid = Arr::get($data, 'org_uuid');
        $model->grade_uuid = Arr::get($data, 'grade_uuid');
        $model->status = Kelas::STATUS_NOT_STARTED;
        $model->total_juz_target = Arr::get($data, 'total_juz_target');
        $model->save();

        return $model;
    }

    public function update($uuid, $data)
    {
        $model = Kelas::findOrFail($uuid);
        $model->update($data);

        return $model->fresh();
    }

    public function activate($uuid)
    {
        $model = Kelas::findOrFail($uuid);
        $model->start_date = Carbon::now();
        $model->status = Kelas::STATUS_ACTIVE;
        $model->update();

        return $model->fresh();
    }

    public function stop($uuid)
    {
        $model = Kelas::findOrFail($uuid);
        $model->end_date = Carbon::now();
        $model->status = Kelas::STATUS_FINISHED;
        $model->update();

        return $model->fresh();
    }

    public function reactivate($uuid)
    {
        $model = Kelas::findOrFail($uuid);
        $model->end_date = null;
        $model->status = Kelas::STATUS_ACTIVE;
        $model->update();

        return $model->fresh();
    }

    public function delete(Kelas $grade)
    {
        return $grade->delete();
    }

    public function count($data)
    {
        $model = $this->getQuery($data);
        return $model->count();
    }

    public function hasActiveByTeacherOrg($teacherUUID, $orgUUID)
    {
        $kelas = Kelas::where('teacher_uuid', $teacherUUID)
            ->where('org_uuid', $orgUUID)
            ->whereIn('status', [Kelas::STATUS_NOT_STARTED, Kelas::STATUS_ACTIVE])
            ->count();

        return $kelas > 0;
    }
}
