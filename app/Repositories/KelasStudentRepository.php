<?php

namespace App\Repositories;

use App\Helpers\CommonHelper;
use App\Models\Kelas;
use App\Models\KelasStudent;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class KelasStudentRepository
{
    function __construct(
    ) {}

    private function getQuery($data = null)
    {
        $model = Kelas::join('kelas_students', 'kelas.uuid', '=', 'kelas_students.kelas_uuid')
            ->join('students', 'kelas_students.student_uuid', '=', 'students.uuid')
            ->select(
                'kelas_students.uuid as uuid',
                'kelas.uuid as kelas_uuid',
                'kelas.name as kelas_name',
                'kelas.description as kelas_description',
                'kelas.teacher_uuid',
                'kelas.org_uuid',
                'kelas.grade_uuid',
                'kelas.status',
                'students.uuid as student_uuid',
                'students.nik as nik',
                'students.nis as nis',
                'students.firstname as firstname',
                'students.lastname as lastname',
                'students.birthdate as birthdate',
                'students.phone as phone',
                'kelas_students.created_at as created_at'
            );

        $qWord = Arr::get($data, 'q');
        if (!empty($qWord)) {
            $model->where(function ($query) use ($qWord) {
                $query->where('kelas.name', 'like', "%$qWord%");
                $query->orWhere('kelas.description', 'like', "%$qWord%");
                $query->orWhere('students.nik', 'like', "%$qWord%");
                $query->orWhere('students.nis', 'like', "%$qWord%");
                $query->orWhere('students.firstname', 'like', "%$qWord%");
                $query->orWhere('students.lastname', 'like', "%$qWord%");
                $query->orWhere('students.birthdate', 'like', "%$qWord%");
                $query->orWhere('students.phone', 'like', "%$qWord%");
            });
        }

        $studentUuid = Arr::get($data, 'filter.student_uuid');
        if (!empty($studentUuid)) {
            $model->where('kelas.student_uuid', '=', "$studentUuid");
        }

        $orgUuid = Arr::get($data, 'filter.org_uuid');
        if (!empty($orgUuid)) {
            $model->where('kelas.org_uuid', '=', "$orgUuid");
        }

        $kelasUuid = Arr::get($data, 'filter.kelas_uuid');
        if (!empty($kelasUuid)) {
            $model->where('kelas.uuid', '=', "$kelasUuid");
        }

        $model->where('kelas_students.deleted_at', '=', null);

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
        $kelasStudent = KelasStudent::where('uuid', $uuid)->first();

        return $kelasStudent;
    }

    public function add($data)
    {
        $model = new KelasStudent();
        $model->kelas_uuid = Arr::get($data, 'kelas_uuid');
        $model->student_uuid = Arr::get($data, 'student_uuid');
        $model->org_uuid = Arr::get($data, 'org_uuid');
        $model->status = Kelas::STATUS_ACTIVE;
        $model->save();

        return $model;
    }

    public function count($data)
    {
        $model = $this->getQuery($data);
        return $model->count();
    }

    public function delete(KelasStudent $kelasStudent)
    {
        $kelasStudent->status = Kelas::STATUS_REMOVED;
        return $kelasStudent->update([
            'status' => Kelas::STATUS_REMOVED,
            'deleted_at' => Carbon::now(),
        ]);
    }

    public function isActiveKelasAssigned($kelasUUID, $studentUUID, $orgUUID)
    {
        $kelasStudent = KelasStudent::where('kelas_uuid', $kelasUUID)
            ->where('student_uuid', $studentUUID)
            ->where('org_uuid', $orgUUID)
            ->where('status', Kelas::STATUS_ACTIVE)
            ->where('deleted_at', null)
            ->count();

        return $kelasStudent !== 0;
    }

    public function isStudentHasActiveKelas($studentUUID, $orgUUID)
    {
        $kelasStudent = KelasStudent::where('student_uuid', $studentUUID)
            ->where('org_uuid', $orgUUID)
            ->where('status', Kelas::STATUS_ACTIVE)
            ->where('deleted_at', null)
            ->count();

        return $kelasStudent !== 0;
    }

    public function hasActiveStudent($kelasUUID) {
        $kelasStudent = KelasStudent::where('kelas_uuid', $kelasUUID)
            ->where('status', Kelas::STATUS_ACTIVE)
            ->where('deleted_at', null)
            ->count();

        return $kelasStudent !== 0;
    }

    public function isStudentHasKelas($studentUUID, $orgUUID)
    {
        $kelasStudent = KelasStudent::where('student_uuid', $studentUUID)
            ->where('org_uuid', $orgUUID)
            ->where('deleted_at', null)
            ->count();

        return $kelasStudent !== 0;
    }
}
