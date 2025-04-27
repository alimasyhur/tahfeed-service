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
                'g.name as grade_name',
                'o.name as organization_name',
                DB::raw("
                CASE
                    WHEN COUNT(DISTINCT rh.juz_page_uuid) < 20 THEN CONCAT(COUNT(DISTINCT rh.juz_page_uuid), ' Halaman')
                    WHEN COUNT(DISTINCT rh.juz_page_uuid) % 20 = 0 THEN CONCAT(COUNT(DISTINCT rh.juz_page_uuid) DIV 20, ' Juz')
                    ELSE CONCAT(COUNT(DISTINCT rh.juz_page_uuid) DIV 20, ' Juz ', COUNT(DISTINCT rh.juz_page_uuid) % 20, ' Lembar')
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
                            ' Lembar'
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
                            ' Lembar'
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
                            ' Lembar'
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
                            ' Lembar'
                        )
                    END AS m_total_pekan_lalu
            "),
            )
            ->where('rh.org_uuid', $orgUUID)
            ->where('rh.deleted_at', null)
            ->where('o.deleted_at', null)
            ->groupBy('s.uuid', 'g.uuid', 'o.uuid')
            ->orderBy('s.firstname');

        CommonHelper::sortPageFilterRaw($model, $data);

        return $model->get();
    }
}
