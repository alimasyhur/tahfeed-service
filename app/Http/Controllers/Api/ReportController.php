<?php

namespace App\Http\Controllers\Api;

use App\Constants\JuzPageResponse;
use App\Constants\KelasResponse;
use App\Constants\OrganizationResponse;
use App\Constants\Pagination;
use App\Constants\ReportResponse;
use App\Constants\StudentResponse;
use App\Constants\TeacherResponse;
use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Repositories\JuzPageRepository;
use App\Repositories\KelasRepository;
use App\Repositories\OrganizationRepository;
use App\Repositories\ReportRepository;
use App\Repositories\StudentRepository;
use App\Repositories\TeacherRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    protected $repository;
    protected $studentRepository;
    protected $orgRepository;
    protected $kelasRepository;
    protected $teacherRepository;
    protected $juzPageRepository;
    public function __construct(
        ReportRepository $repository,
        StudentRepository $studentRepository,
        OrganizationRepository $orgRepository,
        KelasRepository $kelasRepository,
        TeacherRepository $teacherRepository,
        JuzPageRepository $juzPageRepository,
    )
    {
        $this->repository = $repository;
        $this->studentRepository = $studentRepository;
        $this->orgRepository = $orgRepository;
        $this->kelasRepository = $kelasRepository;
        $this->teacherRepository = $teacherRepository;
        $this->juzPageRepository = $juzPageRepository;
    }

    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'q' => 'nullable|string',
                'filter.teacher_uuid' => 'nullable|string',
                'filter.org_uuid' => 'nullable|string',
                'filter.student_uuid' => 'nullable|string',
                'page' => 'nullable|integer',
                'limit' => 'nullable|integer',
                'sortOrder' => sprintf('nullable|string|in:%s,%s', Pagination::ASC_PARAM, Pagination::DESC_PARAM),
                'sortField' => 'nullable|string',
            ])->safe()->all();

            $reports = $this->repository->browse($validator);
            $totalReports = $this->repository->count($validator);

            return response()->json([
                'status' => ReportResponse::SUCCESS,
                'message' => ReportResponse::SUCCESS_ALL_RETRIEVED,
                'data' => $reports,
                'total' => $totalReports,
            ]);

        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => ReportResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }

    public function show($uuid)
    {
        $report = $this->repository->find($uuid);
        $startJuzPageUUID = $report->start_juz_page_uuid;
        $endJuzPageUUID = $report->end_juz_page_uuid;
        $startJuzPage = $this->juzPageRepository->find($startJuzPageUUID);
        Arr::set($report, 'start_juz_page_name', $startJuzPage->description);
        $endJuzPage = $this->juzPageRepository->find($endJuzPageUUID);
        Arr::set($report, 'end_juz_page_name', $endJuzPage->description);
        $totalHalaman = ($endJuzPage->value - $startJuzPage->value) + 1;
        Arr::set($report, 'capaian_halaman', $totalHalaman);

        if(empty($report)) {
            return response()->json([
                'status' => ReportResponse::SUCCESS,
                'message' => ReportResponse::NOT_FOUND,
                'error' => true,
                'data' => [],
            ], 404);
        }

        return response()->json([
            'status' => ReportResponse::SUCCESS,
            'message' => ReportResponse::SUCCESS_RETRIEVED,
            'data' => $report,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->all();

            $rules = [
                'student_uuid' => [
                    'required',
                    'string',
                ],
                'org_uuid' => [
                    'required',
                    'string',
                ],
                'kelas_uuid' => [
                    'required',
                    'string',
                ],
                'teacher_uuid' => [
                    'required',
                    'string',
                ],
                'start_juz_uuid' => [
                    'required',
                    'string',
                ],
                'start_page_uuid' => [
                    'required',
                    'string',
                ],
                'end_juz_uuid' => [
                    'required',
                    'string',
                ],
                'end_page_uuid' => [
                    'required',
                    'string',
                ],
                'date_input' => [
                    'required',
                    'string',
                ],
                'type_report' => [
                    'required',
                    'string',
                ],
                'note' => [
                    'required',
                    'string',
                ],
            ];

            $validator = Validator::make($data, $rules);

            $validator->validate();
            $validator = $validator->safe()->all();

            $studentUUID = Arr::get($validator, 'student_uuid');
            $student = $this->studentRepository->find($studentUUID);
            if(empty($student)) {
                return response()->json([
                    'status' => StudentResponse::ERROR,
                    'message' => StudentResponse::NOT_FOUND,
                    'data' => $validator,
                ], 422);
            }

            $orgUUID = Arr::get($validator, 'org_uuid');
            $org = $this->orgRepository->findByUUID($orgUUID);
            if(empty($org)) {
                return response()->json([
                    'status' => OrganizationResponse::ERROR,
                    'message' => OrganizationResponse::NOT_FOUND,
                    'data' => $validator,
                ], 422);
            }

            $kelasUUID = Arr::get($validator, 'kelas_uuid');
            $kelas = $this->kelasRepository->find($kelasUUID);
            if(empty($kelas)) {
                return response()->json([
                    'status' => KelasResponse::ERROR,
                    'message' => KelasResponse::NOT_FOUND,
                    'data' => $validator,
                ], 422);
            }

            $teacherUUID = Arr::get($validator, 'teacher_uuid');
            $teacher = $this->teacherRepository->find($teacherUUID);
            if(empty($teacher)) {
                return response()->json([
                    'status' => TeacherResponse::ERROR,
                    'message' => TeacherResponse::NOT_FOUND,
                    'data' => $validator,
                ], 422);
            }

            $startJuzUUID = Arr::get($validator, 'start_juz_uuid');
            $startPageUUID = Arr::get($validator, 'start_page_uuid');
            $startJuzPage = $this->juzPageRepository->findByJuzPageUUID($startJuzUUID, $startPageUUID);
            if(empty($startJuzPage)) {
                return response()->json([
                    'status' => JuzPageResponse::ERROR,
                    'message' => JuzPageResponse::NOT_FOUND,
                    'data' => $validator,
                ], 422);
            }
            Arr::set($validator, 'start_juz_page_uuid', $startJuzPage->uuid);

            $endJuzUUID = Arr::get($validator, 'end_juz_uuid');
            $endPageUUID = Arr::get($validator, 'end_page_uuid');
            $endJuzPage = $this->juzPageRepository->findByJuzPageUUID($endJuzUUID, $endPageUUID);
            if(empty($endJuzPage)) {
                return response()->json([
                    'status' => JuzPageResponse::ERROR,
                    'message' => JuzPageResponse::NOT_FOUND,
                    'data' => $validator,
                ], 422);
            }
            Arr::set($validator, 'end_juz_page_uuid', $endJuzPage->uuid);

            if ($startJuzPage->value > $endJuzPage->value) {
                return response()->json([
                    'status' => JuzPageResponse::ERROR,
                    'message' => JuzPageResponse::START_GREATER_END_PAGE,
                    'data' => $validator,
                ], 422);
            }

            $name = $startJuzPage->description . ' - ' . $endJuzPage->description;
            Arr::set($validator, 'name', $name);
            Arr::set($validator, 'description', $name);

            $report = $this->repository->add($validator);

            return response()->json([
                'status' => ReportResponse::SUCCESS,
                'message' => ReportResponse::SUCCESS_CREATED,
                'data' => $report,
            ], 201);
        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => ReportResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }

    public function lock($uuid, Request $request)
    {
        try {
            $role = $this->repository->find($uuid);
            if (empty($role)) {
                return response()->json([
                    'status' => ReportResponse::SUCCESS,
                    'message' => ReportResponse::NOT_FOUND,
                    'data' => $role,
                ], 201);
            }

            $validator = Validator::make($request->all(), []);

            $validator->validate();
            $validator = $validator->safe()->all();

            $role = $this->repository->lock($uuid, $validator);

            return response()->json([
                'status' => ReportResponse::SUCCESS,
                'message' => ReportResponse::SUCCESS_UPDATED,
                'data' => $role,
            ], 201);
        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => ReportResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }

    public function unlock($uuid, Request $request)
    {
        try {
            $role = $this->repository->find($uuid);
            if (empty($role)) {
                return response()->json([
                    'status' => ReportResponse::SUCCESS,
                    'message' => ReportResponse::NOT_FOUND,
                    'data' => $role,
                ], 201);
            }

            $validator = Validator::make($request->all(), []);

            $validator->validate();
            $validator = $validator->safe()->all();

            $role = $this->repository->unlock($uuid, $validator);

            return response()->json([
                'status' => ReportResponse::SUCCESS,
                'message' => ReportResponse::SUCCESS_UPDATED,
                'data' => $role,
            ], 201);
        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => ReportResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }

    public function update($uuid, Request $request)
    {
        try {
            $data = $request->all();

            $rules = [
                'student_uuid' => [
                    'required',
                    'string',
                ],
                'org_uuid' => [
                    'required',
                    'string',
                ],
                'kelas_uuid' => [
                    'required',
                    'string',
                ],
                'teacher_uuid' => [
                    'required',
                    'string',
                ],
                'start_juz_uuid' => [
                    'required',
                    'string',
                ],
                'start_page_uuid' => [
                    'required',
                    'string',
                ],
                'end_juz_uuid' => [
                    'required',
                    'string',
                ],
                'end_page_uuid' => [
                    'required',
                    'string',
                ],
                'date_input' => [
                    'required',
                    'string',
                ],
                'type_report' => [
                    'required',
                    'string',
                ],
                'note' => [
                    'required',
                    'string',
                ],
            ];

            $validator = Validator::make($data, $rules);

            $validator->validate();
            $validator = $validator->safe()->all();

            $report = $this->repository->find($uuid);
            $isLocked = $report->is_locked;
            if ($isLocked) {
                return response()->json([
                    'status' => ReportResponse::ERROR,
                    'message' => ReportResponse::ALREADY_LOCKED,
                    'data' => $validator,
                ], 422);
            }


            $studentUUID = Arr::get($validator, 'student_uuid');
            $student = $this->studentRepository->find($studentUUID);
            if(empty($student)) {
                return response()->json([
                    'status' => StudentResponse::ERROR,
                    'message' => StudentResponse::NOT_FOUND,
                    'data' => $validator,
                ], 422);
            }

            $orgUUID = Arr::get($validator, 'org_uuid');
            $org = $this->orgRepository->findByUUID($orgUUID);
            if(empty($org)) {
                return response()->json([
                    'status' => OrganizationResponse::ERROR,
                    'message' => OrganizationResponse::NOT_FOUND,
                    'data' => $validator,
                ], 422);
            }

            $kelasUUID = Arr::get($validator, 'kelas_uuid');
            $kelas = $this->kelasRepository->find($kelasUUID);
            if(empty($kelas)) {
                return response()->json([
                    'status' => KelasResponse::ERROR,
                    'message' => KelasResponse::NOT_FOUND,
                    'data' => $validator,
                ], 422);
            }

            $teacherUUID = Arr::get($validator, 'teacher_uuid');
            $teacher = $this->teacherRepository->find($teacherUUID);
            if(empty($teacher)) {
                return response()->json([
                    'status' => TeacherResponse::ERROR,
                    'message' => TeacherResponse::NOT_FOUND,
                    'data' => $validator,
                ], 422);
            }

            $startJuzUUID = Arr::get($validator, 'start_juz_uuid');
            $startPageUUID = Arr::get($validator, 'start_page_uuid');
            $startJuzPage = $this->juzPageRepository->findByJuzPageUUID($startJuzUUID, $startPageUUID);
            if(empty($startJuzPage)) {
                return response()->json([
                    'status' => JuzPageResponse::ERROR,
                    'message' => JuzPageResponse::NOT_FOUND,
                    'data' => $validator,
                ], 422);
            }
            Arr::set($validator, 'start_juz_page_uuid', $startJuzPage->uuid);

            $endJuzUUID = Arr::get($validator, 'end_juz_uuid');
            $endPageUUID = Arr::get($validator, 'end_page_uuid');
            $endJuzPage = $this->juzPageRepository->findByJuzPageUUID($endJuzUUID, $endPageUUID);
            if(empty($endJuzPage)) {
                return response()->json([
                    'status' => JuzPageResponse::ERROR,
                    'message' => JuzPageResponse::NOT_FOUND,
                    'data' => $validator,
                ], 422);
            }
            Arr::set($validator, 'end_juz_page_uuid', $endJuzPage->uuid);

            if ($startJuzPage->value > $endJuzPage->value) {
                return response()->json([
                    'status' => JuzPageResponse::ERROR,
                    'message' => JuzPageResponse::START_GREATER_END_PAGE,
                    'data' => $validator,
                ], 422);
            }

            $report = $this->repository->update($uuid, $validator);

            return response()->json([
                'status' => ReportResponse::SUCCESS,
                'message' => ReportResponse::SUCCESS_CREATED,
                'data' => $report,
            ], 201);
        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => ReportResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }

    public function destroy($uuid)
    {
        try {
            $report = $this->repository->find($uuid);
            $isLocked = $report->is_locked;
            if ($isLocked) {
                return response()->json([
                    'status' => ReportResponse::ERROR,
                    'message' => ReportResponse::ALREADY_LOCKED,
                    'data' => $report,
                ], 422);
            }

            $this->repository->delete($report);

            return response()->json([
                'status' => ReportResponse::SUCCESS,
                'message' => ReportResponse::SUCCESS_DELETED,
            ]);
        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => ReportResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }

    public function getWeeklyReportData(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'q' => 'nullable|string',
                'filter.teacher_uuid' => 'nullable|string',
                'filter.org_uuid' => 'nullable|string',
                'filter.student_uuid' => 'nullable|string',
                'page' => 'nullable|integer',
                'limit' => 'nullable|integer',
                'sortOrder' => sprintf('nullable|string|in:%s,%s', Pagination::ASC_PARAM, Pagination::DESC_PARAM),
                'sortField' => 'nullable|string',
            ])->safe()->all();

            $reportData = $this->repository->getWeeklyReportData($validator);

            return response()->json([
                'status' => ReportResponse::SUCCESS,
                'message' => ReportResponse::SUCCESS_RETRIEVED,
                'data' => $reportData,
            ]);
        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => ReportResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }

    public function getSetoranData(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'q' => 'nullable|string',
                'filter.teacher_uuid' => 'nullable|string',
                'filter.org_uuid' => 'nullable|string',
                'filter.student_uuid' => 'nullable|string',
                'page' => 'nullable|integer',
                'limit' => 'nullable|integer',
                'sortOrder' => sprintf('nullable|string|in:%s,%s', Pagination::ASC_PARAM, Pagination::DESC_PARAM),
                'sortField' => 'nullable|string',
            ])->safe()->all();

            $reportData = $this->repository->getSetoranData($validator);

            return response()->json([
                'status' => ReportResponse::SUCCESS,
                'message' => ReportResponse::SUCCESS_RETRIEVED,
                'data' => $reportData,
            ]);
        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => ReportResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }
}
