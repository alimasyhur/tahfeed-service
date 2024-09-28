<?php

namespace App\Http\Controllers\Api;

use App\Constants\KelasResponse;
use App\Constants\Pagination;
use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Repositories\KelasRepository;
use App\Repositories\OrganizationRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class KelasController extends Controller
{
    protected $repository;
    protected $orgRepository;
    public function __construct(
        KelasRepository $repository,
        OrganizationRepository $orgRepository
    )
    {
        $this->repository = $repository;
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
                                    ->where('grade_uuid', $request->grade_uuid);
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
            ];

            $validator = Validator::make($data, $rules);

            $validator->validate();
            $validator = $validator->safe()->all();

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
                    'required',
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

    public function destroy($uuid)
    {
        try {
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
}
