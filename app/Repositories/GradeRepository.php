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
        return Grade::query()
            ->join('organizations', function($join) {
                $join->on('grades.org_uuid', '=', 'organizations.uuid')
                    ->whereNull('organizations.deleted_at');
            })
            ->select(['grades.*', 'organizations.name as org_name'])
            ->when(Arr::get($data, 'q'), function ($query, $qWord) {
                $query->where(function ($subQuery) use ($qWord) {
                    $subQuery->where('grades.name', 'like', "%{$qWord}%")
                            ->orWhere('grades.description', 'like', "%{$qWord}%")
                            ->orWhere('grades.period', 'like', "%{$qWord}%");
                });
            })
            ->when(Arr::get($data, 'filter.name'), function ($query, $name) {
                $query->where('grades.name', 'like', "%{$name}%");
            })
            ->when(Arr::get($data, 'filter.uuid'), function ($query, $uuid) {
                $query->where('grades.uuid', $uuid);
            })
            ->when(Arr::get($data, 'filter.org_uuid'), function ($query, $orgUuid) {
                $query->where('grades.org_uuid', $orgUuid);
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
