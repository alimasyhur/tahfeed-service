<?php

namespace App\Repositories;

use App\Helpers\CommonHelper;
use App\Models\Kelas;
use App\Models\KelasStudent;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class KelasRepository
{
    function __construct(
    ) {}

    private function getQuery($data = null)
    {
        return Kelas::query()
            ->join('organizations', function($join) {
                $join->on('kelas.org_uuid', '=', 'organizations.uuid')
                    ->whereNull('organizations.deleted_at');
            })
            ->join('grades', function($join) {
                $join->on('kelas.grade_uuid', '=', 'grades.uuid')
                    ->whereNull('grades.deleted_at');
            })
            ->join('teachers', function($join) {
                $join->on('kelas.teacher_uuid', '=', 'teachers.uuid')
                    ->whereNull('teachers.deleted_at');
            })
            ->join('users', function($join) {
                $join->on('teachers.user_uuid', '=', 'users.uuid')
                    ->whereNull('users.deleted_at');
            })
            ->leftJoin('kelas_students', function($join) {
                $join->on('kelas.uuid', '=', 'kelas_students.kelas_uuid')
                    ->whereNull('kelas_students.deleted_at');
            })
            ->select([
                'kelas.*',
                'organizations.name as org_name',
                'grades.period as period',
                'teachers.firstname as teacher_firstname',
                'teachers.lastname as teacher_lastname',
                'users.email as teacher_email',
                DB::raw("CONCAT(teachers.firstname, ' ', teachers.lastname) as teacher_full_name")
            ])
            ->groupBy('kelas.uuid')
            ->when(Arr::get($data, 'q'), function ($query, $qWord) {
                $query->where(function ($subQuery) use ($qWord) {
                    $subQuery->where('kelas.name', 'like', "%{$qWord}%")
                            ->orWhere('kelas.description', 'like', "%{$qWord}%");
                });
            })
            ->when(Arr::get($data, 'filter.name'), function ($query, $name) {
                $query->where('kelas.name', 'like', "%{$name}%");
            })
            ->when(Arr::get($data, 'filter.uuid'), function ($query, $uuid) {
                $query->where('kelas.uuid', $uuid);
            })
            ->when(Arr::get($data, 'filter.teacher_uuid'), function ($query, $teacherUuid) {
                $query->where('kelas.teacher_uuid', $teacherUuid);
            })
            ->when(Arr::get($data, 'filter.org_uuid'), function ($query, $orgUuid) {
                $query->where('kelas.org_uuid', $orgUuid);
            })
            ->when(Arr::get($data, 'filter.grade_uuid'), function ($query, $gradeUuid) {
                $query->where('kelas.grade_uuid', $gradeUuid);
            })
            ->when(Arr::get($data, 'filter.student_uuid'), function ($query, $studentUuid) {
                $query->where('kelas_students.student_uuid', $studentUuid);
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
        $kelas = Kelas::join('organizations', 'kelas.org_uuid', '=', 'organizations.uuid')
            ->join('grades', 'kelas.grade_uuid', '=', 'grades.uuid')
            ->join('teachers', 'kelas.teacher_uuid', '=', 'teachers.uuid')
            ->join('users', 'teachers.user_uuid', '=', 'users.uuid')
            ->select(
                'kelas.*',
                'organizations.name as org_name',
                'grades.name as grade_name',
                'grades.period as grade_period',
                'teachers.nik as teacher_nik',
                'teachers.firstname as teacher_firstname',
                'teachers.lastname as teacher_lastname',
                'users.email as teacher_email',
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

        KelasStudent::where('org_uuid', $model->org_uuid)->update(['status' => 'finished']);

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
