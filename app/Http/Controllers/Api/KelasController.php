<?php

namespace App\Http\Controllers\Api;

use App\Constants\KelasResponse;
use App\Constants\Pagination;
use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Repositories\KelasRepository;
use App\Repositories\KelasStudentRepository;
use App\Repositories\OrganizationRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Arr;

class KelasController extends Controller
{
    protected $repository;
    protected $kelasStudentRepository;
    protected $orgRepository;
    public function __construct(
        KelasRepository $repository,
        KelasStudentRepository $kelasStudentRepository,
        OrganizationRepository $orgRepository
    )
    {
        $this->repository = $repository;
        $this->kelasStudentRepository = $kelasStudentRepository;
        $this->orgRepository = $orgRepository;
    }

    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'q' => 'nullable|string',
                'filter.name' => 'nullable|string',
                'filter.description' => 'nullable|string',
                'filter.teacher_uuid' => 'nullable|string',
                'filter.org_uuid' => 'nullable|string',
                'filter.grade_uuid' => 'nullable|string',
                'page' => 'nullable|integer',
                'limit' => 'nullable|integer',
                'sortOrder' => sprintf('nullable|string|in:%s,%s', Pagination::ASC_PARAM, Pagination::DESC_PARAM),
                'sortField' => 'nullable|string',
            ])->safe()->all();

            $kelas = $this->repository->browse($validator);
            $totalRoles = $this->repository->count($validator);

            return response()->json([
                'status' => KelasResponse::SUCCESS,
                'message' => KelasResponse::SUCCESS_ALL_RETRIEVED,
                'data' => $kelas,
                'total' => $totalRoles,
            ]);

        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => KelasResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }

    public function show($uuid)
    {
        $kelas = $this->repository->find($uuid);

        if(empty($kelas)) {
            return response()->json([
                'status' => KelasResponse::SUCCESS,
                'message' => KelasResponse::NOT_FOUND,
                'error' => true,
                'data' => [],
            ], 404);
        }

        return response()->json([
            'status' => KelasResponse::SUCCESS,
            'message' => KelasResponse::SUCCESS_RETRIEVED,
            'data' => $kelas,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->all();

            $rules = [
                'name' => [
                    'required',
                    'string',
                    'unique:kelas,org_uuid',
                    Rule::unique('kelas')->where(function ($query) use ($request) {
                        return $query->where('org_uuid', $request->org_uuid)
                                    ->where('grade_uuid', $request->grade_uuid)
                                    ->where('deleted_at', null);
                    }),
                ],
                'description' => [
                    'required',
                    'string',
                ],
                'teacher_uuid' => [
                    'required',
                    'string',
                ],
                'org_uuid' => [
                    'required',
                    'string',
                ],
                'grade_uuid' => [
                    'required',
                    'string',
                ],
                'total_juz_target' => [
                    'required',
                    'integer',
                ],
            ];

            $validator = Validator::make($data, $rules);

            $validator->validate();
            $validator = $validator->safe()->all();

            $hasActive = $this->repository
                ->hasActiveByTeacherOrg(Arr::get($validator, 'teacher_uuid'), Arr::get($validator, 'org_uuid'));

            if($hasActive) {
                return response()->json([
                    'status' => KelasResponse::ERROR,
                    'message' => KelasResponse::HAS_ACTIVE_CLASS,
                ], 422);
            }

            $kelas = $this->repository->add($validator);

            return response()->json([
                'status' => KelasResponse::SUCCESS,
                'message' => KelasResponse::SUCCESS_CREATED,
                'data' => $kelas,
            ], 201);
        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => KelasResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }

    public function update($uuid, Request $request)
    {
        try {
            $kelas = $this->repository->find($uuid);
            if (empty($kelas)) {
                return response()->json([
                    'status'  => KelasResponse::SUCCESS,
                    'message' => KelasResponse::NOT_FOUND,
                    'data'    => $kelas,
                ], 201);
            }

            $rules = [
                'name' => [
                    'sometimes',
                    'string',
                    'unique:kelas,org_uuid',
                    Rule::unique('kelas')->where(function ($query) use ($request) {
                        return $query->where('org_uuid', $request->org_uuid)
                                        ->where('grade_uuid', $request->org_uuid);
                    })->ignore($kelas->uuid, 'uuid'),
                ],
                'description' => [
                    'sometimes',
                    'string',
                ],
                'teacher_uuid' => [
                    'sometimes',
                    'string',
                ],
                'org_uuid' => [
                    'sometimes',
                    'string',
                ],
                'grade_uuid' => [
                    'sometimes',
                    'string',
                ],
                'total_juz_target' => [
                    'sometimes',
                    'integer',
                ],
            ];

            $validator = Validator::make($request->all(), $rules);

            $validator->validate();
            $validator = $validator->safe()->all();

            $kelas = $this->repository->update($uuid, $validator);

            return response()->json([
                'status' => KelasResponse::SUCCESS,
                'message' => KelasResponse::SUCCESS_UPDATED,
                'data' => $kelas,
            ], 201);
        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => KelasResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }

    public function activate($uuid)
    {
        try {
            $kelas = $this->repository->find($uuid);
            if (empty($kelas)) {
                return response()->json([
                    'status'  => KelasResponse::SUCCESS,
                    'message' => KelasResponse::NOT_FOUND,
                    'data'    => $kelas,
                ], 201);
            }

            $kelas = $this->repository->activate($uuid);

            return response()->json([
                'status' => KelasResponse::SUCCESS,
                'message' => KelasResponse::SUCCESS_UPDATED,
                'data' => $kelas,
            ], 201);
        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => KelasResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }

    public function stop($uuid)
    {
        try {
            $kelas = $this->repository->find($uuid);
            if (empty($kelas)) {
                return response()->json([
                    'status'  => KelasResponse::SUCCESS,
                    'message' => KelasResponse::NOT_FOUND,
                    'data'    => $kelas,
                ], 201);
            }

            $kelas = $this->repository->stop($uuid);

            return response()->json([
                'status' => KelasResponse::SUCCESS,
                'message' => KelasResponse::SUCCESS_UPDATED,
                'data' => $kelas,
            ], 201);
        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => KelasResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }

    public function reactivate($uuid)
    {
        try {
            $kelas = $this->repository->find($uuid);
            if (empty($kelas)) {
                return response()->json([
                    'status'  => KelasResponse::SUCCESS,
                    'message' => KelasResponse::NOT_FOUND,
                    'data'    => $kelas,
                ], 201);
            }

            $kelas = $this->repository->reactivate($uuid);

            return response()->json([
                'status' => KelasResponse::SUCCESS,
                'message' => KelasResponse::SUCCESS_UPDATED,
                'data' => $kelas,
            ], 201);
        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => KelasResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }

    public function destroy($uuid)
    {
        try {
            if ($this->kelasStudentRepository->hasActiveStudent($uuid)) {
                return response()->json([
                    'status' => KelasResponse::ERROR,
                    'message' => KelasResponse::HAS_ACTIVE_STUDENT,
                ]);
            }

            $kelas = $this->repository->find($uuid);
            $this->repository->delete($kelas);

            return response()->json([
                'status' => KelasResponse::SUCCESS,
                'message' => KelasResponse::SUCCESS_DELETED,
            ]);
        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => KelasResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }

    public function kelasStudent($uuid, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'q' => 'nullable|string',
                'filter.org_uuid' => 'nullable|string',
                'filter.student_uuid' => 'nullable|string',
                'page' => 'nullable|integer',
                'limit' => 'nullable|integer',
                'sortOrder' => sprintf('nullable|string|in:%s,%s', Pagination::ASC_PARAM, Pagination::DESC_PARAM),
                'sortField' => 'nullable|string',
            ])->safe()->all();

            Arr::set($validator, 'filter.kelas_uuid', $uuid);

            $kelas = $this->kelasStudentRepository->browse($validator);
            $totalKelas = $this->kelasStudentRepository->count($validator);

            return response()->json([
                'status' => KelasResponse::SUCCESS,
                'message' => KelasResponse::SUCCESS_ALL_RETRIEVED,
                'data' => $kelas,
                'total' => $totalKelas,
            ]);

        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => KelasResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }

    public function assignStudent(Request $request)
    {
        try {
            $data = $request->all();

            $rules = [
                'student_uuid' => [
                    'required',
                    'string',
                ],
                'kelas_uuid' => [
                    'required',
                    'string',
                ],
                'org_uuid' => [
                    'required',
                    'string',
                ],
                'notes' => [
                    'sometimes',
                    'string',
                ],
            ];

            $customMessages = [
                'student_uuid.unique' => 'student is already assigned to an active class',
            ];

            $validator = Validator::make($data, $rules, $customMessages);

            $validator->validate();
            $validator = $validator->safe()->all();

            $isAssigned = $this->kelasStudentRepository->isActiveKelasAssigned(
                $request->kelas_uuid,
                $request->student_uuid,
                $request->org_uuid);

            if ($isAssigned) {
                return response()->json([
                    'status' => KelasResponse::ERROR,
                    'message' => KelasResponse::ALREADY_ASSIGNED,
                ], 422);
            }

            $kelas = $this->kelasStudentRepository->add($validator);

            return response()->json([
                'status' => KelasResponse::SUCCESS,
                'message' => KelasResponse::SUCCESS_CREATED,
                'data' => $kelas,
            ], 201);
        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => KelasResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }

    public function destroyKelasStudent($uuid)
    {
        try {
            $kelas = $this->kelasStudentRepository->find($uuid);

            $this->kelasStudentRepository->delete($kelas);

            return response()->json([
                'status' => KelasResponse::SUCCESS,
                'message' => KelasResponse::SUCCESS_DELETED,
            ]);
        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => KelasResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }
}
