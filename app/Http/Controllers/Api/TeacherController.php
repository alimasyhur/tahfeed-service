<?php

namespace App\Http\Controllers\Api;

use App\Constants\OrganizationResponse;
use App\Constants\TeacherResponse;
use App\Constants\Pagination;
use App\Constants\UserResponse;
use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Repositories\OrganizationRepository;
use App\Repositories\RoleRepository;
use App\Repositories\TeacherRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    protected $repository;
    protected $userRepository;
    protected $orgRepository;
    protected $roleRepository;
    public function __construct(
        TeacherRepository $repository,
        UserRepository $userRepository,
        OrganizationRepository $orgRepository,
        RoleRepository $roleRepository,
    )
    {
        $this->repository = $repository;
        $this->userRepository = $userRepository;
        $this->orgRepository = $orgRepository;
        $this->roleRepository = $roleRepository;
    }

    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'q' => 'nullable|string',
                'filter.nik' => 'nullable|string',
                'filter.firstname' => 'nullable|string',
                'filter.lastname' => 'nullable|string',
                'filter.birthdate' => 'nullable|string',
                'filter.phone' => 'nullable|string',
                'filter.bio' => 'nullable|string',
                'filter.user_uuid' => 'nullable|string',
                'filter.org_uuid' => 'nullable|string',
                'page' => 'nullable|integer',
                'limit' => 'nullable|integer',
                'sortOrder' => sprintf('nullable|string|in:%s,%s', Pagination::ASC_PARAM, Pagination::DESC_PARAM),
                'sortField' => 'nullable|string',
            ])->safe()->all();

            $teacher = $this->repository->browse($validator);
            $totalRoles = $this->repository->count($validator);

            return response()->json([
                'status' => TeacherResponse::SUCCESS,
                'message' => TeacherResponse::SUCCESS_ALL_RETRIEVED,
                'data' => $teacher,
                'total' => $totalRoles,
            ]);

        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => TeacherResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }

    public function show($uuid)
    {
        $teacher = $this->repository->find($uuid);

        if(empty($teacher)) {
            return response()->json([
                'status' => TeacherResponse::SUCCESS,
                'message' => TeacherResponse::NOT_FOUND,
                'error' => true,
                'data' => [],
            ], 404);
        }

        return response()->json([
            'status' => TeacherResponse::SUCCESS,
            'message' => TeacherResponse::SUCCESS_RETRIEVED,
            'data' => $teacher,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->all();

            $rules = [
                'nik' => [
                    'required',
                    'string',
                    'unique:teachers,org_uuid',
                    Rule::unique('teachers')->where(function ($query) use ($request) {
                        return $query->where('org_uuid', $request->org_uuid);
                    }),
                ],
                'firstname' => [
                    'required',
                    'string',
                ],
                'lastname' => [
                    'required',
                    'string',
                ],
                'birthdate' => [
                    'required',
                    'string',
                ],
                'phone' => [
                    'required',
                    'string',
                ],
                'bio' => [
                    'required',
                    'string',
                ],
                'org_uuid' => [
                    'required',
                    'string',
                ],
                'user_uuid' => [
                    'required',
                    'string',
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
            Arr::set($validator, 'org_name', $org->name);

            $userUuid = Arr::get($validator, 'user_uuid');
            $user = $this->userRepository->find($userUuid);
            if(empty($user)) {
                return response()->json([
                    'status' => UserResponse::ERROR,
                    'message' => UserResponse::NOT_FOUND,
                    'data' => $validator,
                ], 422);
            }

            $role = $this->roleRepository->findByName(Role::ROLE_TEACHER);
            Arr::set($validator, 'role_uuid', $role->uuid);
            Arr::set($validator, 'role_name', $role->name);
            Arr::set($validator, 'constant_value', $role->constant_value);
            $teacher = $this->repository->add($validator);

            return response()->json([
                'status' => TeacherResponse::SUCCESS,
                'message' => TeacherResponse::SUCCESS_CREATED,
                'data' => $teacher,
            ], 201);
        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => TeacherResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }

    public function update($uuid, Request $request)
    {
        try {
            $teacher = $this->repository->find($uuid);
            if (empty($teacher)) {
                return response()->json([
                    'status'  => TeacherResponse::SUCCESS,
                    'message' => TeacherResponse::NOT_FOUND,
                    'data'    => $teacher,
                ], 201);
            }

            $rules = [
                'nik' => [
                    'sometimes',
                    'string',
                    'unique:teachers,org_uuid',
                    Rule::unique('teachers')->where(function ($query) use ($request) {
                        return $query->where('org_uuid', $request->org_uuid);
                    })->ignore($teacher->uuid, 'uuid'),
                ],
                'firstname' => [
                    'sometimes',
                    'string',
                ],
                'lastname' => [
                    'sometimes',
                    'string',
                ],
                'birthdate' => [
                    'sometimes',
                    'string',
                ],
                'phone' => [
                    'sometimes',
                    'string',
                ],
                'bio' => [
                    'sometimes',
                    'string',
                ],
                'org_uuid' => [
                    'sometimes',
                    'string',
                ],
                'user_uuid' => [
                    'sometimes',
                    'string',
                ],
            ];

            $validator = Validator::make($request->all(), $rules);

            $validator->validate();
            $validator = $validator->safe()->all();

            $userUuid = Arr::get($validator, 'user_uuid');
            $user = $this->userRepository->find($userUuid);
            if(empty($user)) {
                return response()->json([
                    'status' => UserResponse::ERROR,
                    'message' => UserResponse::NOT_FOUND,
                    'data' => $validator,
                ], 422);
            }

            $teacher = $this->repository->update($uuid, $validator);

            return response()->json([
                'status' => TeacherResponse::SUCCESS,
                'message' => TeacherResponse::SUCCESS_UPDATED,
                'data' => $teacher,
            ], 201);
        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => TeacherResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }

    public function destroy($uuid)
    {
        try {
            $teacher = $this->repository->find($uuid);

            $this->repository->delete($teacher);

            return response()->json([
                'status' => TeacherResponse::SUCCESS,
                'message' => TeacherResponse::SUCCESS_DELETED,
            ]);
        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => TeacherResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }
}
