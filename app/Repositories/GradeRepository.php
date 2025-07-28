<?php

namespace App\Repositories;

use App\Helpers\CommonHelper;
use App\Models\Grade;
use App\Models\Student;
use Illuminate\Support\Arr;

class GradeRepository
{
    function __construct(
    ) {}

    private function getQuery($data = null)
    {
        $model = Grade::join('organizations', 'grades.org_uuid', '=', 'organizations.uuid')
            ->select('grades.*', 'organizations.name as org_name');

        $qWord = Arr::get($data, 'q');
        if (!empty($qWord)) {
            $model->where(function ($query) use ($qWord) {
                $query->where('grades.name', 'like', "%$qWord%");
                $query->orWhere('grades.description', 'like', "%$qWord%");
                $query->orWhere('grades.period', 'like', "%$qWord%");
            });
        }

        $name = Arr::get($data, 'filter.name');
        if (!empty($name)) {
            $model->where('grades.name', 'like', "%$name%");
        }

        $uuid = Arr::get($data, 'filter.uuid');
        if (!empty($uuid)) {
            $model->where('grades.uuid', '=', "$uuid");
        }

        $orgUuid = Arr::get($data, 'filter.org_uuid');
        if (!empty($orgUuid)) {
            $model->where('grades.org_uuid', '=', "$orgUuid");
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
        $grade = Grade::where('uuid', $uuid)
            ->first();

        return $grade;
    }

    public function findByName($name)
    {
        $grade = Grade::where('name', $name)
            ->first();

        return $grade;
    }

    public function add($data)
    {
        $model = new Grade();
        $model->name = Arr::get($data, 'name');
        $model->description = Arr::get($data, 'description');
        $model->org_uuid = Arr::get($data, 'org_uuid');
        $model->period = Arr::get($data, 'period');
        $model->save();

        return $model;
    }

    public function update($uuid, $data)
    {

        $model = Grade::findOrFail($uuid);
        $model->update($data);

        $model->fresh();
        return $model;
    }

    public function delete(Grade $grade)
    {
        return $grade->delete();
    }

    public function count($data)
    {
        $model = $this->getQuery($data);
        return $model->count();
    }

    public function hasActiveStudent($gradeUUID) {
        $kelasStudent = Student::where('grade_uuid', $gradeUUID)
            ->where('deleted_at', null)
            ->count();

        return $kelasStudent !== 0;
    }
}
