<?php

namespace App\Repositories;

use App\Helpers\CommonHelper;
use App\Models\Kelas;
use Illuminate\Support\Arr;

class KelasRepository
{
    function __construct(
    ) {}

    private function getQuery($data = null)
    {
        $model = Kelas::join('organizations', 'kelas.org_uuid', '=', 'organizations.uuid')
            ->select('kelas.*', 'organizations.name as org_name');

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
        if (!empty($orgUuid)) {
            $model->where('kelas.teacher_uuid', '=', "$teacherUuid");
        }

        $orgUuid = Arr::get($data, 'filter.org_uuid');
        if (!empty($orgUuid)) {
            $model->where('kelas.org_uuid', '=', "$orgUuid");
        }

        $gradeUuid = Arr::get($data, 'filter.grade_uuid');
        if (!empty($orgUuid)) {
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
        $grade = Kelas::where('uuid', $uuid)
            ->first();

        return $grade;
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
        $model->save();

        return $model;
    }

    public function update($uuid, $data)
    {

        $model = Kelas::findOrFail($uuid);
        $model->update($data);

        $model->fresh();
        return $model;
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
}
