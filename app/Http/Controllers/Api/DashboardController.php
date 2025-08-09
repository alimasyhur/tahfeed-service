<?php

namespace App\Http\Controllers\Api;

use App\Constants\RoleResponse;
use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Repositories\KelasRepository;
use App\Repositories\ReportRepository;
use App\Repositories\StudentRepository;
use App\Repositories\TeacherRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    protected $studentRepository;
    protected $teacherRepository;
    protected $reportRepository;
    protected $kelasRepository;
    public function __construct(
        StudentRepository $studentRepository,
        TeacherRepository $teacherRepository,
        ReportRepository $reportRepository,
        KelasRepository $kelasRepository,
    )
    {
        $this->studentRepository = $studentRepository;
        $this->teacherRepository = $teacherRepository;
        $this->reportRepository = $reportRepository;
        $this->kelasRepository = $kelasRepository;
    }

    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'filter.org_uuid' => 'nullable|string',
            ])->safe()->all();

            $totalStudents = $this->studentRepository->count($validator);
            $totalTeachers = $this->teacherRepository->count($validator);
            $totalReports = $this->reportRepository->count($validator);
            $totalReports = $this->reportRepository->count($validator);
            $totalKelases = $this->kelasRepository->count($validator);

            return response()->json([
                'status' => RoleResponse::SUCCESS,
                'message' => RoleResponse::SUCCESS_ALL_RETRIEVED,
                'data' => [
                    'totalStudents'     => $totalStudents,
                    'totalTeachers'     => $totalTeachers,
                    'totalReports'      => $totalReports,
                    'totalKelases'      => $totalKelases,
                ],
            ]);

        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => RoleResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }
}
