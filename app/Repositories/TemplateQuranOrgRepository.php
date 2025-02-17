<?php

namespace App\Repositories;

use App\Helpers\CommonHelper;
use App\Models\TemplateQuran;
use App\Models\TemplateQuranJuz;
use App\Models\TemplateQuranOrg;
use App\Models\TemplateQuranPage;
use Illuminate\Support\Arr;

class TemplateQuranOrgRepository
{
    function __construct(
    ) {}

    private function getQuery($data = null)
    {
        $model = TemplateQuranOrg::join('template_qurans', 'template_quran_organizations.template_quran_uuid', '=', 'template_qurans.uuid')
            ->join('organizations', 'template_quran_organizations.org_uuid', '=', 'organizations.uuid')
            ->select(
                'template_qurans.*',
                'template_quran_organizations.org_uuid as org_uuid',
                'organizations.name as organization_name',
            );

        $qWord = Arr::get($data, 'q');
        if (!empty($qWord)) {
            $model->where(function ($query) use ($qWord) {
                $query->where('template_qurans.name', 'like', "%$qWord%")
                ->orWhere('template_qurans.description', 'like', "%$qWord%")
                ->orWhere('template_qurans.slug', 'like', "%$qWord%");
            });
        }

        $name = Arr::get($data, 'filter.name');
        if (!empty($name)) {
            $model->where('template_qurans.name', 'like', "%$name%");
        }

        $uuid = Arr::get($data, 'filter.uuid');
        if (!empty($uuid)) {
            $model->where('template_qurans.uuid', '=', "$uuid");
        }

        $description = Arr::get($data, 'filter.description');
        if (!empty($description)) {
            $model->where('template_qurans.description', 'like', "%$description%");
        }

        $slug = Arr::get($data, 'filter.slug');
        if (!empty($slug)) {
            $model->where('template_qurans.slug', 'like', "%$slug%");
        }

        $orgUUID = Arr::get($data, 'filter.org_uuid');
        if (!empty($slug)) {
            $model->where('template_quran_organizations.org_uuid', $orgUUID);
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
        $role = TemplateQuranOrg::where('uuid', $uuid)
            ->first();

        return $role;
    }

    public function findByName($name)
    {
        $role = TemplateQuranOrg::where('name', $name)
            ->first();

        return $role;
    }

    public function findWIthJuz($uuid)
    {
        $templateQuran = TemplateQuranOrg::where('uuid', $uuid)
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
