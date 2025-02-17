<?php

namespace App\Repositories;

use App\Helpers\CommonHelper;
use App\Models\TemplateQuranJuz;
use Illuminate\Support\Arr;

class TemplateQuranJuzRepository
{
    function __construct(
    ) {}

    private function getQuery($data = null)
    {
        $model = TemplateQuranJuz::query();

        $qWord = Arr::get($data, 'q');
        if (!empty($qWord)) {
            $model->where(function ($query) use ($qWord) {
                $query->where('name', 'like', "%$qWord%")
                ->orWhere('description', 'like', "%$qWord%")
                ->orWhere('slug', 'like', "%$qWord%");
            });
        }

        $name = Arr::get($data, 'filter.name');
        if (!empty($name)) {
            $model->where('name', 'like', "%$name%");
        }

        $uuid = Arr::get($data, 'filter.uuid');
        if (!empty($uuid)) {
            $model->where('uuid', '=', "$uuid");
        }

        $description = Arr::get($data, 'filter.description');
        if (!empty($description)) {
            $model->where('description', 'like', "%$description%");
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
        $role = TemplateQuranJuz::where('uuid', $uuid)
            ->first();

        return $role;
    }

    public function findByName($name)
    {
        $role = TemplateQuranJuz::where('name', $name)
            ->first();

        return $role;
    }

    public function count($data)
    {
        $model = $this->getQuery($data);
        return $model->count();
    }
}
