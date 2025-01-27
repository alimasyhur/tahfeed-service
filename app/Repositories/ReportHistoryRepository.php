<?php

namespace App\Repositories;

use App\Models\ReportHistory;
use Illuminate\Support\Arr;

class ReportHistoryRepository
{
    function __construct(
    ) {}

    public function find($uuid)
    {
        $report = ReportHistory::where('uuid', $uuid)
            ->first();

        return $report;
    }

    public function findByStudentOrgPageUUID($studentUUID, $orgUUID, $juzPageUUID)
    {
        $report = ReportHistory::where('student_uuid', $studentUUID)
            ->where('org_uuid', $orgUUID)
            ->where('juz_page_uuid', $juzPageUUID)
            ->first();

        return $report;
    }

    public function add($data)
    {
        $model = new ReportHistory();
        $model->student_uuid = Arr::get($data, 'student_uuid');
        $model->report_uuid = Arr::get($data, 'report_uuid');
        $model->org_uuid = Arr::get($data, 'org_uuid');
        $model->date_input = Arr::get($data, 'date_input');
        $model->name = Arr::get($data, 'name');
        $model->description = Arr::get($data, 'description');
        $model->type_report = Arr::get($data, 'type_report');
        $model->juz_page_uuid = Arr::get($data, 'juz_page_uuid');
        $model->teacher_uuid = Arr::get($data, 'teacher_uuid');
        $model->save();

        return $model;
    }

    public function delete(ReportHistory $reportHistory)
    {
        return $reportHistory->delete();
    }

    public function deleteByReportUUID($reportUUID)
    {
        return ReportHistory::where('report_uuid', $reportUUID)->delete();
    }

}
