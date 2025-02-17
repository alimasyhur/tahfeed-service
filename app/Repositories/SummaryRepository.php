<?php

namespace App\Repositories;

use App\Helpers\CommonHelper;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SummaryRepository
{
    function __construct(
    ) {}

    public function listSummary($data)
    {
        $orgUUID = Arr::get($data, 'filter.org_uuid');

        $model = DB::table('report_histories as rh')
            ->join('students as s', 'rh.student_uuid', '=', 's.uuid')
            ->join('grades as g', 's.grade_uuid', '=', 'g.uuid')
            ->join('organizations as o', 's.org_uuid', '=', 'o.uuid')
            ->select(
                's.uuid',
                's.nik',
                's.nis',
                's.org_uuid',
                's.grade_uuid',
                DB::raw("CONCAT(s.firstname, ' ', s.lastname) as fullname"),
                's.firstname',
                's.lastname',
                'g.name as grade_name',
                'o.name as organization_name',
                DB::raw("
                CASE
                    WHEN COUNT(DISTINCT rh.juz_page_uuid) < 20 THEN CONCAT(COUNT(DISTINCT rh.juz_page_uuid), ' Lembar')
                    WHEN COUNT(DISTINCT rh.juz_page_uuid) % 20 = 0 THEN CONCAT(COUNT(DISTINCT rh.juz_page_uuid) / 20, ' Juz')
                    ELSE CONCAT(COUNT(DISTINCT rh.juz_page_uuid) / 20, ' Juz ', COUNT(DISTINCT rh.juz_page_uuid) % 20, ' Lembar')
                END as total
            "))
            ->where('rh.org_uuid', $orgUUID)
            ->where('rh.deleted_at', null)
            ->groupBy('s.uuid', 'g.uuid', 'o.uuid')
            ->orderBy('s.firstname');

        CommonHelper::sortPageFilterRaw($model, $data);

        return $model->get();
    }
}
