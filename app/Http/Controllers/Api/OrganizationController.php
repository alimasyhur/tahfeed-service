<?php

namespace App\Http\Controllers\Api;

use App\Constants\OrganizationResponse;
use App\Constants\Pagination;
use App\Constants\RoleResponse;
use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Repositories\OrganizationRepository;
use App\Repositories\RoleUserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;

class OrganizationController extends Controller
{
    protected $repository;
    public function __construct(
        OrganizationRepository $repository
    )
    {
        $this->repository = $repository;
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

            $organizations = $this->repository->browse($validator);
            $totalOrganizations = $this->repository->count($validator);

            return response()->json([
                'status' => RoleResponse::SUCCESS,
                'message' => RoleResponse::SUCCESS_ALL_RETRIEVED,
                'data' => $organizations,
                'total' => $totalOrganizations,
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

    public function show($uuid, Request $request)
    {
        $userUuid = $request->user()->uuid;
        $organization = $this->repository->findForUpdate($uuid, $userUuid);

        if(empty($organization)) {
            return response()->json([
                'status' => OrganizationResponse::SUCCESS,
                'message' => OrganizationResponse::NOT_FOUND,
                'error' => true,
                'data' => [],
            ], 404);
        }

        return response()->json([
            'message' => OrganizationResponse::SUCCESS_RETRIEVED,
            'data' => $organization,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $userUuid = $user->uuid;

        try {
            if (!RoleUserRepository::isSuperAdmin($userUuid)) {
                $organization = $this->repository->find($userUuid);

                if(!empty($organization)) {
                    return response()->json([
                        'status' => OrganizationResponse::ERROR,
                        'message' => OrganizationResponse::EXIST,
                        'data' => $organization,
                    ], 422);
                }
            }

            $validator = Validator::make($request->all(), [
                'name'          => 'required|string',
                'domain'        => 'required|string|unique:organizations,domain',
                'bio'           => 'required|string',
                'address'       => 'required|string',
                'email'         => 'required|string|unique:organizations,email',
                'phone'         => 'required|string|unique:organizations,phone',
            ]);

            $validator->validate();
            $validator = $validator->safe()->all();

            Arr::set($validator, 'created_by', $userUuid);

            $organization = $this->repository->add($validator);

            return response()->json([
                'status' => OrganizationResponse::SUCCESS,
                'message' => OrganizationResponse::SUCCESS_CREATED,
                'data' => $organization,
            ], 201);
        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => OrganizationResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }

    public function update($uuid, Request $request)
    {
        $user = $request->user();
        $userUuid = $user->uuid;

        try {
            $organization = $this->repository->findForUpdate($uuid, $userUuid);

            if(empty($organization)) {
                return response()->json([
                    'status' => OrganizationResponse::SUCCESS,
                    'message' => OrganizationResponse::NOT_FOUND,
                    'data' => [],
                ]);
            }

            $validator = Validator::make($request->all(), [
                'name'          => 'sometimes|string',
                'domain'        => "sometimes|string|unique:organizations,domain,$organization->uuid,uuid",
                'bio'           => 'sometimes|string',
                'address'       => 'sometimes|string',
                'email'         => "sometimes|string|unique:organizations,email,$organization->uuid,uuid",
                'phone'         => "sometimes|string|unique:organizations,phone,$organization->uuid,uuid",
                'is_verified'   => 'sometimes|integer',
                'is_active'     => 'sometimes|integer',
            ]);

            $validator->validate();
            $validator = $validator->safe()->all();

            $updatedProfile = $this->repository->update($organization, $validator);

            return response()->json([
                'status' => OrganizationResponse::SUCCESS,
                'message' => OrganizationResponse::SUCCESS_UPDATED,
                'data' => $updatedProfile,
            ], 201);
        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => OrganizationResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }

    public function showByDomain($domain, Request $request)
    {
        $organization = $this->repository->findByDomain($domain);
        if ($organization && $organization->logo_image) {
            Arr::set($organization, 'logo_image_url', $organization->getLogoImageThumbnailUrlAttribute());
        }

        if(empty($organization)) {
            return response()->json([
                'status' => OrganizationResponse::SUCCESS,
                'message' => OrganizationResponse::NOT_FOUND,
                'error' => true,
                'data' => [],
            ], 404);
        }

        return response()->json([
            'message' => OrganizationResponse::SUCCESS_RETRIEVED,
            'data' => $organization,
        ]);
    }
}
