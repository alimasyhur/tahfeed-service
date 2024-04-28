<?php

namespace App\Http\Controllers\Api;

use App\Constants\OrganizationResponse;
use App\Constants\RoleResponse;
use App\Constants\Pagination;
use App\Constants\UserResponse;
use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Repositories\OrganizationRepository;
use App\Repositories\RoleRepository;
use App\Repositories\RoleUserRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class RoleUserController extends Controller
{
    protected $repository;
    protected $orgRepository;
    protected $roleRepository;
    protected $userRepository;

    public function __construct(
        RoleUserRepository $repository,
        OrganizationRepository $orgRepository,
        RoleRepository $roleRepository,
        UserRepository $userRepository,
    )
    {
        $this->repository = $repository;
        $this->orgRepository = $orgRepository;
        $this->roleRepository = $roleRepository;
        $this->userRepository = $userRepository;
    }

    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'q' => 'nullable|string',
                'filter.name' => 'nullable|string',
                'filter.org_uuid' => 'nullable|string',
                'page' => 'nullable|integer',
                'limit' => 'nullable|integer',
                'sortOrder' => sprintf('nullable|string|in:%s,%s', Pagination::ASC_PARAM, Pagination::DESC_PARAM),
                'sortField' => 'nullable|string',
            ])->safe()->all();

            $roles = $this->repository->browse($validator);
            $totalRoles = $this->repository->count($validator);

            return response()->json([
                'status' => RoleResponse::SUCCESS,
                'message' => RoleResponse::SUCCESS_ALL_RETRIEVED,
                'data' => $roles,
                'total' => $totalRoles,
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

    public function show($uuid)
    {
        $role = $this->repository->find($uuid);

        if(empty($role)) {
            return response()->json([
                'status' => RoleResponse::SUCCESS,
                'message' => RoleResponse::NOT_FOUND,
                'error' => true,
                'data' => [],
            ], 404);
        }

        return response()->json([
            'status' => RoleResponse::SUCCESS,
            'message' => RoleResponse::SUCCESS_RETRIEVED,
            'data' => $role,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $roleUuid = $request->input('role_uuid');
            $orgUuid = $request->input('org_uuid');

            $data = $request->all();

            $rules = [
                'user_uuid' => [
                    'required',
                    'string',
                    Rule::unique('orgs_users_roles')->where(function($query) use ($data) {
                        return $query->where('user_uuid', $data['user_uuid'])
                                ->where('org_uuid', $data['org_uuid'])
                                ->where('role_uuid', $data['role_uuid']);
                    })
                ],
                'org_uuid' => ['required', 'string'],
                'role_uuid'=> ['required', 'string'],
            ];

            $customMessages = [
                'user_uuid.unique' => 'User is already assigned to this role',
            ];

            $validator = Validator::make($data, $rules, $customMessages);

            $validator->validate();
            $validator = $validator->safe()->all();


            $userUuid = Arr::get($validator, 'user_uuid');
            $user = $this->userRepository->find($userUuid);
            if(empty($user)) {
                return response()->json([
                    'status' => RoleResponse::ERROR,
                    'message' => UserResponse::NOT_FOUND,
                    'data' => $validator,
                ], 422);
            }

            $orgUuid = Arr::get($validator, 'org_uuid');
            $org = $this->orgRepository->findByUUID($orgUuid);
            if(empty($org)) {
                return response()->json([
                    'status' => RoleResponse::ERROR,
                    'message' => OrganizationResponse::NOT_FOUND,
                    'data' => $validator,
                ], 422);
            }
            Arr::set($validator, 'org_name', $org->name);

            $roleUuid = Arr::get($validator, 'role_uuid');
            $role = $this->roleRepository->find($roleUuid);
            if(empty($role)) {
                return response()->json([
                    'status' => RoleResponse::ERROR,
                    'message' => OrganizationResponse::NOT_FOUND,
                    'data' => $validator,
                ], 422);
            }
            Arr::set($validator, 'role_name', $role->name);

            $roleUser = $this->repository->add($validator);

            return response()->json([
                'status' => RoleResponse::SUCCESS,
                'message' => RoleResponse::SUCCESS_CREATED,
                'data' => $roleUser,
            ], 201);
        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => RoleResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }

    public function destroy($uuid)
    {
        try {
            $role = $this->repository->find($uuid);

            $this->repository->delete($role);

            return response()->json([
                'status' => RoleResponse::SUCCESS,
                'message' => RoleResponse::SUCCESS_DELETED,
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
