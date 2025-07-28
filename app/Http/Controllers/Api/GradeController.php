<?php

namespace App\Http\Controllers\Api;

use App\Constants\GradeResponse;
use App\Constants\OrganizationResponse;
use App\Constants\Pagination;
use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Repositories\GradeRepository;
use App\Repositories\OrganizationRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GradeController extends Controller
{
    protected $repository;
    protected $orgRepository;
    public function __construct(
        GradeRepository $repository,
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
                'filter.period' => 'nullable|string',
                'filter.org_uuid' => 'nullable|string',
                'page' => 'nullable|integer',
                'limit' => 'nullable|integer',
                'sortOrder' => sprintf('nullable|string|in:%s,%s', Pagination::ASC_PARAM, Pagination::DESC_PARAM),
                'sortField' => 'nullable|string',
            ])->safe()->all();

            $grades = $this->repository->browse($validator);
            $totalRoles = $this->repository->count($validator);

            return response()->json([
                'status' => GradeResponse::SUCCESS,
                'message' => GradeResponse::SUCCESS_ALL_RETRIEVED,
                'data' => $grades,
                'total' => $totalRoles,
            ]);

        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => GradeResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }

    public function show($uuid)
    {
        $grade = $this->repository->find($uuid);

        if(empty($grade)) {
            return response()->json([
                'status' => GradeResponse::SUCCESS,
                'message' => GradeResponse::NOT_FOUND,
                'error' => true,
                'data' => [],
            ], 404);
        }

        return response()->json([
            'status' => GradeResponse::SUCCESS,
            'message' => GradeResponse::SUCCESS_RETRIEVED,
            'data' => $grade,
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
                    'unique:grades,org_uuid',
                    Rule::unique('grades')->where(function ($query) use ($request) {
                        return $query->where('org_uuid', $request->org_uuid);
                    }),
                ],
                'description' => [
                    'required',
                    'string',
                ],
                'org_uuid' => [
                    'required',
                    'string',
                ],
                'period' => [
                    'required',
                    'integer',
                ],
            ];

            $validator = Validator::make($data, $rules);

            $validator->validate();
            $validator = $validator->safe()->all();

            $orgUuid = Arr::get($validator, 'org_uuid');
            $org = $this->orgRepository->findByUUID($orgUuid);
            if(empty($org)) {
                return response()->json([
                    'status' => OrganizationResponse::ERROR,
                    'message' => OrganizationResponse::NOT_FOUND,
                    'data' => $validator,
                ], 422);
            }

            $grade = $this->repository->add($validator);

            return response()->json([
                'status' => GradeResponse::SUCCESS,
                'message' => GradeResponse::SUCCESS_CREATED,
                'data' => $grade,
            ], 201);
        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => GradeResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }

    public function update($uuid, Request $request)
    {
        try {
            $grade = $this->repository->find($uuid);
            if (empty($grade)) {
                return response()->json([
                    'status'  => GradeResponse::SUCCESS,
                    'message' => GradeResponse::NOT_FOUND,
                    'data'    => $grade,
                ], 201);
            }

            $rules = [
                'name' => [
                    'sometimes',
                    'string',
                    'unique:grades,org_uuid',
                    Rule::unique('grades')->where(function ($query) use ($request) {
                        return $query->where('org_uuid', $request->org_uuid);
                    })->ignore($grade->uuid, 'uuid'),
                ],
                'description' => [
                    'required',
                    'string',
                ],
                'org_uuid' => [
                    'sometimes',
                    'string',
                ],
                'period' => [
                    'sometimes',
                    'integer',
                ],
            ];

            $validator = Validator::make($request->all(), $rules);

            $validator->validate();
            $validator = $validator->safe()->all();

            $grade = $this->repository->update($uuid, $validator);

            return response()->json([
                'status' => GradeResponse::SUCCESS,
                'message' => GradeResponse::SUCCESS_UPDATED,
                'data' => $grade,
            ], 201);
        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => GradeResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }

    public function destroy($uuid)
    {
        try {
            if ($this->repository->hasActiveStudent($uuid)) {
                return response()->json([
                    'status' => GradeResponse::ERROR,
                    'message' => GradeResponse::HAS_ACTIVE_STUDENT,
                ]);
            }

            $grade = $this->repository->find($uuid);

            $this->repository->delete($grade);

            return response()->json([
                'status' => GradeResponse::SUCCESS,
                'message' => GradeResponse::SUCCESS_DELETED,
            ]);
        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => GradeResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }
}
