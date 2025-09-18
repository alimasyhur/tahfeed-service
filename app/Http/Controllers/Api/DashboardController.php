<?php

namespace App\Http\Controllers\Api;

use App\Constants\RoleResponse;
use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Repositories\KelasRepository;
use App\Repositories\OrganizationRepository;
use App\Repositories\ReportRepository;
use App\Repositories\StudentRepository;
use App\Repositories\TeacherRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    protected $studentRepository;
    protected $teacherRepository;
    protected $reportRepository;
    protected $kelasRepository;
    protected $userRepository;
    protected $orgRepository;
    public function __construct(
        StudentRepository $studentRepository,
        TeacherRepository $teacherRepository,
        ReportRepository $reportRepository,
        KelasRepository $kelasRepository,
        UserRepository $userRepository,
        OrganizationRepository $orgRepository,
    )
    {
        $this->studentRepository = $studentRepository;
        $this->teacherRepository = $teacherRepository;
        $this->reportRepository = $reportRepository;
        $this->kelasRepository = $kelasRepository;
        $this->userRepository = $userRepository;
        $this->orgRepository = $orgRepository;
    }

    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'filter.org_uuid' => 'nullable|string',
                'filter.is_superadmin' => 'nullable|integer',
                'filter.is_admin' => 'nullable|integer',
            ])->safe()->all();

            $totalStudents = $this->studentRepository->count($validator);
            $totalTeachers = $this->teacherRepository->count($validator);
            $totalReports = $this->reportRepository->count($validator);
            $totalReports = $this->reportRepository->count($validator);
            $totalKelases = $this->kelasRepository->dashboardCount($validator);

            $response = [
                'totalStudents'     => $totalStudents,
                'totalTeachers'     => $totalTeachers,
                'totalReports'      => $totalReports,
                'totalKelases'      => $totalKelases,
            ];

            $isSuperadmin = Arr::get($validator, 'filter.is_superadmin');
            $isAdmin = Arr::get($validator, 'filter.is_admin');

            if (!empty($isSuperadmin) || !empty($isAdmin)) {
                $totalUser = $this->userRepository->count($validator);
                $totalOrgs = $this->orgRepository->count($validator);
                Arr::set($response, 'totalUsers', $totalUser);
                Arr::set($response, 'totalOrgs', $totalOrgs);
            }

            return response()->json([
                'status' => RoleResponse::SUCCESS,
                'message' => RoleResponse::SUCCESS_ALL_RETRIEVED,
                'data' => $response
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
