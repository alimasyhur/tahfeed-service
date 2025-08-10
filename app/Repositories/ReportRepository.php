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
        $model = Report::query()
            ->join('students', function($join) {
                $join->on('reports.student_uuid', '=', 'students.uuid')
                    ->whereNull('students.deleted_at');
            })
            ->join('template_quran_juz_pages as start_juz_pages', 'reports.start_juz_page_uuid', '=', 'start_juz_pages.uuid')
            ->join('template_quran_juz_pages as end_juz_pages', 'reports.end_juz_page_uuid', '=', 'end_juz_pages.uuid')
            ->select([
                'reports.*',
                'students.nis as student_nis',
                DB::raw("CONCAT(students.firstname, ' ', students.lastname) as student_fullname"),
                'students.firstname as student_firstname',
                'students.lastname as student_lastname',
                'start_juz_pages.description as start_juz_page_name',
                'end_juz_pages.description as end_juz_page_name',
                DB::raw("
                    CASE
                        WHEN (end_juz_pages.value - start_juz_pages.value + 1) < 20
                        THEN CONCAT((end_juz_pages.value - start_juz_pages.value + 1), ' Halaman')
                        WHEN (end_juz_pages.value - start_juz_pages.value + 1) % 20 = 0
                        THEN CONCAT((end_juz_pages.value - start_juz_pages.value + 1) DIV 20, ' Juz')
                        ELSE CONCAT(
                            (end_juz_pages.value - start_juz_pages.value + 1) DIV 20, ' Juz ',
                            (end_juz_pages.value - start_juz_pages.value + 1) % 20, ' Halaman'
                        )
                    END AS total
                ")
            ]);

        // Apply filters using when() for cleaner conditional queries
        $model->when(Arr::get($data, 'q'), function ($query, $qWord) {
            $query->where(function ($subQuery) use ($qWord) {
                $subQuery->where('reports.name', 'like', "%{$qWord}%")
                        ->orWhere('reports.description', 'like', "%{$qWord}%")
                        ->orWhere('reports.type_report', 'like', "%{$qWord}%")
                        ->orWhere('reports.note', 'like', "%{$qWord}%");
            });
        })
        ->when(Arr::get($data, 'filter.uuid'), function ($query, $uuid) {
            $query->where('reports.uuid', $uuid);
        })
        ->when(Arr::get($data, 'filter.date_input'), function ($query, $dateInput) {
            $query->where('reports.date_input', $dateInput);
        })
        ->when(Arr::get($data, 'filter.student_uuid'), function ($query, $studentUUID) {
            $query->where('reports.student_uuid', $studentUUID);
        })
        ->when(Arr::get($data, 'filter.org_uuid'), function ($query, $orgUUID) {
            $query->where('reports.org_uuid', $orgUUID);
        })
        ->when(Arr::get($data, 'filter.kelas_uuid'), function ($query, $kelasUUID) {
            $query->where('reports.kelas_uuid', $kelasUUID);
        })
        ->when(Arr::get($data, 'filter.teacher_uuid'), function ($query, $teacherUUID) {
            $query->where('reports.teacher_uuid', $teacherUUID);
        })
        ->when(Arr::get($data, 'filter.start_juz_page_uuid'), function ($query, $startJuzPageUUID) {
            $query->where('reports.start_juz_page_uuid', $startJuzPageUUID);
        })
        ->when(Arr::get($data, 'filter.end_juz_page_uuid'), function ($query, $endJuzPageUUID) {
            $query->where('reports.end_juz_page_uuid', $endJuzPageUUID);
        })
        ->when(Arr::get($data, 'filter.is_locked'), function ($query, $isLocked) {
            $query->where('reports.is_locked', $isLocked);
        });

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
