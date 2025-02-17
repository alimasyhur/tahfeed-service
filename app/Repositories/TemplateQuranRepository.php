<?php

namespace App\Repositories;

use App\Helpers\CommonHelper;
use App\Models\Role;
use App\Models\TemplateQuran;
use App\Models\TemplateQuranJuz;
use App\Models\TemplateQuranPage;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class TemplateQuranRepository
{
    function __construct(
    ) {}

    private function getQuery($data = null)
    {
        $model = TemplateQuran::query();

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

        $slug = Arr::get($data, 'filter.slug');
        if (!empty($slug)) {
            $model->where('slug', 'like', "%$slug%");
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
        $role = TemplateQuran::where('uuid', $uuid)
            ->first();

        return $role;
    }

    public function findByName($name)
    {
        $role = TemplateQuran::where('name', $name)
            ->first();

        return $role;
    }

    public function findBySlug($slug)
    {
        $role = TemplateQuran::where('slug', $slug)
            ->first();

        return $role;
    }

    public function findWIthJuz($uuid)
    {
        $templateQuran = TemplateQuran::where('uuid', $uuid)
            ->first();

        $juzes = TemplateQuranJuz::orderBy('constant', 'asc')->get();

        $pages = TemplateQuranPage::where('template_quran_uuid', $uuid)
            ->orderBy('constant', 'asc')
            ->get();

        return [
            'template_quran' => $templateQuran,
            'juzes' => $juzes,
            'pages' => $pages,
        ];
    }

    public function count($data)
    {
        $model = $this->getQuery($data);
        return $model->count();
    }
}
