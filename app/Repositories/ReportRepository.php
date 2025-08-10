<?php

namespace App\Repositories;

use App\Helpers\CommonHelper;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ReportRepository
{
    protected $history;
    protected $juzPage;
    function __construct(
        ReportHistoryRepository $history,
        JuzPageRepository $juzPage,
    ) {
        $this->history = $history;
        $this->juzPage = $juzPage;
    }

    private function getQuery($data = null)
    {
        $model = Report::join('students', function($join) {
        $join->on('reports.student_uuid', '=', 'students.uuid')
             ->whereNull('students.deleted_at');
        })->join('template_quran_juz_pages as start_juz_pages', 'reports.start_juz_page_uuid', '=', 'start_juz_pages.uuid')
        ->join('template_quran_juz_pages as end_juz_pages', 'reports.end_juz_page_uuid', '=', 'end_juz_pages.uuid')
        ->select(
            'reports.*',
            'students.nis as student_nis',
            DB::raw("CONCAT(students.firstname, ' ', students.lastname) as student_fullname"),
            'students.firstname as student_firstname',
            'students.lastname as student_lastname',
            'start_juz_pages.description as start_juz_page_name',
            'end_juz_pages.description as end_juz_page_name',
            DB::raw("
                CASE
                    WHEN (end_juz_pages.value - start_juz_pages.value + 1) < 20 THEN CONCAT((end_juz_pages.value - start_juz_pages.value + 1), ' Halaman')
                    WHEN (end_juz_pages.value - start_juz_pages.value + 1) % 20 = 0 THEN CONCAT((end_juz_pages.value - start_juz_pages.value + 1) DIV 20, ' Juz')
                    ELSE CONCAT((end_juz_pages.value - start_juz_pages.value + 1) DIV 20, ' Juz ', (end_juz_pages.value - start_juz_pages.value + 1) % 20, ' Halaman')
                END AS total
            "),
        );

        $qWord = Arr::get($data, 'q');
        if (!empty($qWord)) {
            $model->where(function ($query) use ($qWord) {
                $query->where('name', 'like', "%$qWord%");
                $query->orWhere('description', 'like', "%$qWord%");
                $query->orWhere('type_report', 'like', "%$qWord%");
                $query->orWhere('note', 'like', "%$qWord%");
            });
        }

        $uuid = Arr::get($data, 'filter.uuid');
        if (!empty($uuid)) {
            $model->where('uuid', '=', "$uuid");
        }

        $dateInput = Arr::get($data, 'filter.date_input');
        if (!empty($dateInput)) {
            $model->where('date_input', '=', "$dateInput");
        }

        $studentUUID = Arr::get($data, 'filter.student_uuid');
        if (!empty($studentUUID)) {
            $model->where('student_uuid', '=', "$studentUUID");
        }

        $orgUUID = Arr::get($data, 'filter.org_uuid');
        if (!empty($orgUUID)) {
            $model->where('reports.org_uuid', '=', "$orgUUID");
        }

        $kelasUUID = Arr::get($data, 'filter.kelas_uuid');
        if (!empty($kelasUUID)) {
            $model->where('kelas_uuid', '=', "$kelasUUID");
        }

        $teacherUUID = Arr::get($data, 'filter.teacher_uuid');
        if (!empty($teacherUUID)) {
            $model->where('reports.teacher_uuid', '=', "$teacherUUID");
        }

        $startJuzPageUUID = Arr::get($data, 'filter.start_juz_page_uuid');
        if (!empty($startJuzPageUUID)) {
            $model->where('start_juz_page_uuid', '=', "$startJuzPageUUID");
        }

        $endJuzPageUUID = Arr::get($data, 'filter.end_juz_page_uuid');
        if (!empty($endJuzPageUUID)) {
            $model->where('end_juz_page_uuid', '=', "$endJuzPageUUID");
        }

        $isLocked = Arr::get($data, 'filter.is_locked');
        if (!empty($isLocked)) {
            $model->where('is_locked', '=', "$isLocked");
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
        $report = Report::join('students', 'reports.student_uuid', '=', 'students.uuid')
            ->join('organizations', 'reports.org_uuid', '=', 'organizations.uuid')
            ->select(
                'reports.*',
                'students.nik AS student_nik',
                'students.firstname AS student_firstname',
                'students.lastname AS student_lastname',
                'organizations.name AS organization_name',
            )->where('reports.uuid', $uuid)
            ->first();

        return $report;
    }

    public function findByName($name)
    {
        $report = Report::where('name', $name)
            ->first();

        return $report;
    }

    public function add($data)
    {
        $model = new Report();
        $model->student_uuid = Arr::get($data, 'student_uuid');
        $model->org_uuid = Arr::get($data, 'org_uuid');
        $model->kelas_uuid = Arr::get($data, 'kelas_uuid');
        $model->teacher_uuid = Arr::get($data, 'teacher_uuid');
        $model->start_juz_page_uuid = Arr::get($data, 'start_juz_page_uuid');
        $model->end_juz_page_uuid = Arr::get($data, 'end_juz_page_uuid');
        $model->date_input = Arr::get($data, 'date_input');
        $model->name = Arr::get($data, 'name');
        $model->description = Arr::get($data, 'description');
        $model->type_report = Arr::get($data, 'type_report');
        $model->note = Arr::get($data, 'note');
        $model->is_locked = false;
        $model->locked_at = null;
        $model->save();

        return $model;
    }

    public function lock($uuid, $data)
    {
        $model = Report::findOrFail($uuid);

        if ($model->is_locked) {
            return $model;
        }

        $model->is_locked = true;
        $model->locked_at = Carbon::now();
        $model->update($data);

        $model->fresh();

        $startJuzPage = $this->juzPage->find($model->start_juz_page_uuid);
        $endJuzPage = $this->juzPage->find($model->end_juz_page_uuid);

        $startValue = $startJuzPage->value;
        $endValue = $endJuzPage->value;

        $listJuzPages = $this->juzPage->findByValues($startValue, $endValue);

        foreach ($listJuzPages as $juzPage) {
            $history = $this->history->findByStudentOrgPageUUID($model->student_uuid, $model->org_uuid, $juzPage->uuid);
            $reportType = Report::TYPE_ZIYADAH;
            if ($history != null) {
                $reportType = Report::TYPE_MUROJAAH;
            }

            $payload = [
                'student_uuid' => $model->student_uuid,
                'report_uuid' => $model->uuid,
                'org_uuid' => $model->org_uuid,
                'date_input' => $model->date_input,
                'name' => $juzPage->description,
                'description' => $juzPage->description,
                'type_report' => $reportType,
                'juz_page_uuid' => $juzPage->uuid,
                'teacher_uuid' => $model->uuid,
            ];

            $this->history->add($payload);
        }

        return $model;
    }

    public function unlock($uuid, $data)
    {
        DB::beginTransaction();
        try {
            $model = Report::findOrFail($uuid);

            if (!$model->is_locked) {
                return $model;
            }

            $model->is_locked = false;
            $model->locked_at = null;
            $model->update($data);

            $model->fresh();

            $this->history->deleteByReportUUID($model->uuid);

            DB::commit();

            return $model;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update($uuid, $data)
    {
        $model = Report::findOrFail($uuid);
        $model->update($data);

        $model->fresh();
        return $model;
    }

    public function delete(Report $report)
    {
        return $report->delete();
    }

    public function count($data)
    {
        $model = $this->getQuery($data);
        return $model->count();
    }
}
