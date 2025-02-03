<?php

namespace App\Repositories;

use App\Models\TemplateQuranJuzPage;

class JuzPageRepository
{
    function __construct(
    ) {}

    public function find($uuid)
    {
        $juzPage = TemplateQuranJuzPage::where('uuid', $uuid)
            ->first();

        return $juzPage;
    }

    public function findByValue($value)
    {
        $juzPage = TemplateQuranJuzPage::where('value', $value)
            ->first();

        return $juzPage;
    }

    public function findByValues($firstValue, $secondValue)
    {
        $juzPage = TemplateQuranJuzPage::where('value', '>=', $firstValue)
            ->where('value', '<=', $secondValue)
            ->orderBy('value', 'asc')
            ->get();

        return $juzPage;
    }

    public function findByJuzPageUUID($juzUUID, $pageUUID)
    {
        $role = TemplateQuranJuzPage::where('template_quran_juz_uuid', $juzUUID)
            ->where('template_quran_page_uuid', $pageUUID)
            ->first();

        return $role;
    }

}
