<?php

namespace App\Repositories;

use App\Helpers\CommonHelper;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SummaryRepository
{
    function __construct(
    ) {}

    public function listSummary($data)
    {
        $orgUUID = Arr::get($data, 'filter.org_uuid');

        // Cache date calculations
        $now = Carbon::now();
        $thisWeek = [
            'start' => $now->copy()->startOfWeek(Carbon::MONDAY),
            'end' => $now->copy()->endOfWeek(Carbon::SUNDAY)
        ];
        $lastWeek = [
            'start' => $now->copy()->subWeek()->startOfWeek(Carbon::MONDAY),
            'end' => $now->copy()->subWeek()->endOfWeek(Carbon::SUNDAY)
        ];

        // Format labels
        $thisWeekLabel = $thisWeek['start']->format('d-M') . ' - ' . $thisWeek['end']->format('d-M');
        $lastWeekLabel = $lastWeek['start']->format('d-M') . ' - ' . $lastWeek['end']->format('d-M');

        // Format date ranges for query
        $thisWeekRange = [
            'start' => $thisWeek['start']->format('Y-m-d 00:00:00'),
            'end' => $thisWeek['end']->format('Y-m-d 23:59:59')
        ];
        $lastWeekRange = [
            'start' => $lastWeek['start']->format('Y-m-d 00:00:00'),
            'end' => $lastWeek['end']->format('Y-m-d 23:59:59')
        ];

        // Create reusable SQL fragments
        $pageCountFormatter = function($countExpression) {
            return "
                CASE
                    WHEN {$countExpression} < 20
                    THEN CONCAT({$countExpression}, ' Halaman')
                    WHEN {$countExpression} % 20 = 0
                    THEN CONCAT({$countExpression} DIV 20, ' Juz')
                    ELSE CONCAT(
                        {$countExpression} DIV 20, ' Juz ',
                        {$countExpression} % 20, ' Halaman'
                    )
                END
            ";
        };

        $conditionalPageCount = function($type, $startDate, $endDate) {
            return "COUNT(DISTINCT CASE WHEN rh.type_report='{$type}' AND rh.date_input BETWEEN '{$startDate}' AND '{$endDate}' THEN rh.juz_page_uuid END)";
        };

        $model = DB::table('report_histories as rh')
            ->join('students as s', function($join) {
                $join->on('rh.student_uuid', '=', 's.uuid')
                    ->whereNull('s.deleted_at');
            })
            ->join('grades as g', function($join) {
                $join->on('s.grade_uuid', '=', 'g.uuid')
                    ->whereNull('g.deleted_at');
            })
            ->join('organizations as o', function($join) {
                $join->on('s.org_uuid', '=', 'o.uuid')
                    ->whereNull('o.deleted_at');
            })
            ->select([
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

                // Total overall pages
                DB::raw($pageCountFormatter('COUNT(DISTINCT rh.juz_page_uuid)') . ' AS total'),

                // Week labels
                DB::raw("'{$thisWeekLabel}' as pekan_ini_label"),
                DB::raw("'{$lastWeekLabel}' as pekan_lalu_label"),

                // This week - Ziyadah
                DB::raw($pageCountFormatter(
                    $conditionalPageCount('ziyadah', $thisWeekRange['start'], $thisWeekRange['end'])
                ) . ' AS z_total_pekan_ini'),

                // This week - Murojaah
                DB::raw($pageCountFormatter(
                    $conditionalPageCount('murojaah', $thisWeekRange['start'], $thisWeekRange['end'])
                ) . ' AS m_total_pekan_ini'),

                // Last week - Ziyadah
                DB::raw($pageCountFormatter(
                    $conditionalPageCount('ziyadah', $lastWeekRange['start'], $lastWeekRange['end'])
                ) . ' AS z_total_pekan_lalu'),

                // Last week - Murojaah
                DB::raw($pageCountFormatter(
                    $conditionalPageCount('murojaah', $lastWeekRange['start'], $lastWeekRange['end'])
                ) . ' AS m_total_pekan_lalu')
            ])
            ->when($orgUUID, function($query, $orgUUID) {
                $query->where('rh.org_uuid', $orgUUID);
            })
            ->groupBy([
                's.uuid', 's.nik', 's.nis', 's.org_uuid', 's.grade_uuid',
                's.firstname', 's.lastname', 'g.name', 'o.name'
            ])
            ->orderBy('s.firstname');

        CommonHelper::sortPageFilterRaw($model, $data);

        return $model->get();
    }

    public function summary($data)
    {
        $studentUUID = Arr::get($data, 'filter.student_uuid');

        $startOfWeekLabel = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('d-M');
        $endOfWeekLabel = Carbon::now()->endOfWeek(Carbon::SUNDAY)->format('d-M');

        $startOfLastWeekLabel = Carbon::now()->subWeek()->startOfWeek(Carbon::MONDAY)->format('d-M');
        $endOfLastWeekLabel = Carbon::now()->subWeek()->endOfWeek(Carbon::SUNDAY)->format('d-M');

        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d 00:00:00');
        $endOfWeek = Carbon::now()->endOfWeek(Carbon::SUNDAY)->format('Y-m-d 23:59:59');

        $startOfLastWeek = Carbon::now()->subWeek()->startOfWeek(Carbon::MONDAY)->format('Y-m-d 00:00:00');
        $endOfLastWeek = Carbon::now()->subWeek()->endOfWeek(Carbon::SUNDAY)->format('Y-m-d 23:59:59');

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
                's.bio',
                's.phone',
                's.birthdate',
                'g.name as grade_name',
                'o.name as organization_name',
                DB::raw("
                CASE
                    WHEN COUNT(DISTINCT rh.juz_page_uuid) < 20 THEN CONCAT(COUNT(DISTINCT rh.juz_page_uuid), ' Halaman')
                    WHEN COUNT(DISTINCT rh.juz_page_uuid) % 20 = 0 THEN CONCAT(COUNT(DISTINCT rh.juz_page_uuid) DIV 20, ' Juz')
                    ELSE CONCAT(COUNT(DISTINCT rh.juz_page_uuid) DIV 20, ' Juz ', COUNT(DISTINCT rh.juz_page_uuid) % 20, ' Halaman')
                END AS total
            "),
                DB::raw("'$startOfWeekLabel - $endOfWeekLabel' as pekan_ini_label"),
                DB::raw("
                    CASE
                        WHEN COUNT(DISTINCT CASE WHEN rh.type_report='ziyadah' AND rh.date_input BETWEEN '$startOfWeek' AND '$endOfWeek' THEN rh.juz_page_uuid END) < 20
                            THEN CONCAT(COUNT(DISTINCT CASE WHEN rh.type_report='ziyadah' AND rh.date_input BETWEEN '$startOfWeek' AND '$endOfWeek' THEN rh.juz_page_uuid END), ' Halaman')
                        WHEN COUNT(DISTINCT CASE WHEN rh.type_report='ziyadah' AND rh.date_input BETWEEN '$startOfWeek' AND '$endOfWeek' THEN rh.juz_page_uuid END) % 20 = 0
                            THEN CONCAT(COUNT(DISTINCT CASE WHEN rh.type_report='ziyadah' AND rh.date_input BETWEEN '$startOfWeek' AND '$endOfWeek' THEN rh.juz_page_uuid END) DIV 20, ' Juz')
                        ELSE CONCAT(
                            COUNT(DISTINCT CASE WHEN rh.type_report='ziyadah' AND rh.date_input BETWEEN '$startOfWeek' AND '$endOfWeek' THEN rh.juz_page_uuid END) DIV 20,
                            ' Juz ',
                            COUNT(DISTINCT CASE WHEN rh.type_report='ziyadah' AND rh.date_input BETWEEN '$startOfWeek' AND '$endOfWeek' THEN rh.juz_page_uuid END) % 20,
                            ' Halaman'
                        )
                    END AS z_total_pekan_ini
            "),
                DB::raw("
                    CASE
                        WHEN COUNT(DISTINCT CASE WHEN rh.type_report='murojaah' AND rh.date_input BETWEEN '$startOfWeek' AND '$endOfWeek' THEN rh.juz_page_uuid END) < 20
                            THEN CONCAT(COUNT(DISTINCT CASE WHEN rh.type_report='murojaah' AND rh.date_input BETWEEN '$startOfWeek' AND '$endOfWeek' THEN rh.juz_page_uuid END), ' Halaman')
                        WHEN COUNT(DISTINCT CASE WHEN rh.type_report='murojaah' AND rh.date_input BETWEEN '$startOfWeek' AND '$endOfWeek' THEN rh.juz_page_uuid END) % 20 = 0
                            THEN CONCAT(COUNT(DISTINCT CASE WHEN rh.type_report='murojaah' AND rh.date_input BETWEEN '$startOfWeek' AND '$endOfWeek' THEN rh.juz_page_uuid END) DIV 20, ' Juz')
                        ELSE CONCAT(
                            COUNT(DISTINCT CASE WHEN rh.type_report='murojaah' AND rh.date_input BETWEEN '$startOfWeek' AND '$endOfWeek' THEN rh.juz_page_uuid END) DIV 20,
                            ' Juz ',
                            COUNT(DISTINCT CASE WHEN rh.type_report='murojaah' AND rh.date_input BETWEEN '$startOfWeek' AND '$endOfWeek' THEN rh.juz_page_uuid END) % 20,
                            ' Halaman'
                        )
                    END AS m_total_pekan_ini
            "),
                DB::raw("'$startOfLastWeekLabel - $endOfLastWeekLabel' as pekan_lalu_label"),
                DB::raw("
                    CASE
                        WHEN COUNT(DISTINCT CASE WHEN rh.type_report='ziyadah' AND rh.date_input BETWEEN '$startOfLastWeek' AND '$endOfLastWeek' THEN rh.juz_page_uuid END) < 20
                            THEN CONCAT(COUNT(DISTINCT CASE WHEN rh.type_report='ziyadah' AND rh.date_input BETWEEN '$startOfLastWeek' AND '$endOfLastWeek' THEN rh.juz_page_uuid END), ' Halaman')
                        WHEN COUNT(DISTINCT CASE WHEN rh.type_report='murojaah' AND rh.date_input BETWEEN '$startOfLastWeek' AND '$endOfLastWeek' THEN rh.juz_page_uuid END) % 20 = 0
                            THEN CONCAT(COUNT(DISTINCT CASE WHEN rh.type_report='ziyadah' AND rh.date_input BETWEEN '$startOfLastWeek' AND '$endOfLastWeek' THEN rh.juz_page_uuid END) DIV 20, ' Juz')
                        ELSE CONCAT(
                            COUNT(DISTINCT CASE WHEN rh.type_report='ziyadah' AND rh.date_input BETWEEN '$startOfLastWeek' AND '$endOfLastWeek' THEN rh.juz_page_uuid END) DIV 20,
                            ' Juz ',
                            COUNT(DISTINCT CASE WHEN rh.type_report='ziyadah' AND rh.date_input BETWEEN '$startOfLastWeek' AND '$endOfLastWeek' THEN rh.juz_page_uuid END) % 20,
                            ' Halaman'
                        )
                    END AS z_total_pekan_lalu
            "),
                DB::raw("
                    CASE
                        WHEN COUNT(DISTINCT CASE WHEN rh.type_report='murojaah' AND rh.date_input BETWEEN '$startOfLastWeek' AND '$endOfLastWeek' THEN rh.juz_page_uuid END) < 20
                            THEN CONCAT(COUNT(DISTINCT CASE WHEN rh.type_report='murojaah' AND rh.date_input BETWEEN '$startOfLastWeek' AND '$endOfLastWeek' THEN rh.juz_page_uuid END), ' Halaman')
                        WHEN COUNT(DISTINCT CASE WHEN rh.type_report='murojaah' AND rh.date_input BETWEEN '$startOfLastWeek' AND '$endOfLastWeek' THEN rh.juz_page_uuid END) % 20 = 0
                            THEN CONCAT(COUNT(DISTINCT CASE WHEN rh.type_report='murojaah' AND rh.date_input BETWEEN '$startOfLastWeek' AND '$endOfLastWeek' THEN rh.juz_page_uuid END) DIV 20, ' Juz')
                        ELSE CONCAT(
                            COUNT(DISTINCT CASE WHEN rh.type_report='murojaah' AND rh.date_input BETWEEN '$startOfLastWeek' AND '$endOfLastWeek' THEN rh.juz_page_uuid END) DIV 20,
                            ' Juz ',
                            COUNT(DISTINCT CASE WHEN rh.type_report='murojaah' AND rh.date_input BETWEEN '$startOfLastWeek' AND '$endOfLastWeek' THEN rh.juz_page_uuid END) % 20,
                            ' Halaman'
                        )
                    END AS m_total_pekan_lalu
            "),
            )
            ->where('rh.student_uuid', $studentUUID)
            ->where('rh.deleted_at', null)
            ->where('o.deleted_at', null)
            ->groupBy('s.uuid', 'g.uuid', 'o.uuid')
            ->orderBy('s.firstname');

        CommonHelper::sortPageFilterRaw($model, $data);

        return $model->first();
    }
}
