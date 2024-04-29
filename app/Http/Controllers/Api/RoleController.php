<?php

namespace App\Http\Controllers\Api;

use App\Constants\RoleResponse;
use App\Constants\Pagination;
use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Repositories\RoleRepository;
use App\Repositories\RoleUserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    protected $repository;
    protected $roleUserRepository;
    public function __construct(
        RoleRepository $repository,
        RoleUserRepository $roleUserRepository
    )
    {
        $this->repository = $repository;
        $this->roleUserRepository = $roleUserRepository;
    }

    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'q' => 'nullable|string',
                'filter.name' => 'nullable|string',
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
            $data = $request->all();

            $rules = [
                'name' => [
                    'required',
                    'string',
                ],
            ];

            $customMessages = [
                'name.unique' => 'name must be unique.',
            ];

            $validator = Validator::make($data, $rules, $customMessages);

            $validator->validate();
            $validator = $validator->safe()->all();

            $role = $this->repository->add($validator);

            return response()->json([
                'status' => RoleResponse::SUCCESS,
                'message' => RoleResponse::SUCCESS_CREATED,
                'data' => $role,
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

    public function update($uuid, Request $request)
    {
        try {
            $role = $this->repository->find($uuid);
            if (empty($role)) {
                return response()->json([
                    'status' => RoleResponse::SUCCESS,
                    'message' => RoleResponse::NOT_FOUND,
                    'data' => $role,
                ], 201);
            }

            $validator = Validator::make($request->all(), [
                'name'      => "required|string|unique:roles",
            ]);

            $validator->validate();
            $validator = $validator->safe()->all();

            $role = $this->repository->update($uuid, $validator);

            return response()->json([
                'status' => RoleResponse::SUCCESS,
                'message' => RoleResponse::SUCCESS_UPDATED,
                'data' => $role,
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
            $roleUserFilter = ['filter' => ['role_uuid' => $uuid]];
            $totalRoleUser = $this->roleUserRepository->count($roleUserFilter);

            if (!empty($totalRoleUser)) {
                return response()->json([
                    'status' => RoleResponse::ERROR,
                    'message' => RoleResponse::IN_USED,
                ], 422);
            }

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
