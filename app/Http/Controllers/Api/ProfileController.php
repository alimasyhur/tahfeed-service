<?php

namespace App\Http\Controllers\Api;

use App\Constants\ProfileResponse;
use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Repositories\KelasRepository;
use App\Repositories\OrganizationRepository;
use App\Repositories\ProfileRepository;
use App\Repositories\RoleUserRepository;
use App\Repositories\TemplateQuranOrgRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;

class ProfileController extends Controller
{
    protected $repository;
    protected $orgRepository;
    protected $roleRepository;
    protected $quranOrgRepository;
    protected $kelasRepository;

    public function __construct(
        ProfileRepository $repository,
        OrganizationRepository $orgRepository,
        RoleUserRepository $roleRepository,
        TemplateQuranOrgRepository $quranOrgRepository,
        KelasRepository $kelasRepository,
    )
    {
        $this->repository = $repository;
        $this->orgRepository = $orgRepository;
        $this->roleRepository = $roleRepository;
        $this->quranOrgRepository = $quranOrgRepository;
        $this->kelasRepository = $kelasRepository;
    }

    public function show(Request $request)
    {
        $userUuid = $request->user()->uuid;
        $profile = $this->repository->find($userUuid);

        if(empty($profile)) {
            return response()->json([
                'status' => ProfileResponse::SUCCESS,
                'message' => ProfileResponse::NOT_FOUND,
                'error' => true,
                'data' => [],
            ], 404);
        }

        return response()->json([
            'message' => ProfileResponse::SUCCESS_RETRIEVED,
            'data' => $profile,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $userUuid = $user->uuid;

        try {
            $profile = $this->repository->find($userUuid);

            if(!empty($profile)) {
                return response()->json([
                    'status' => ProfileResponse::SUCCESS,
                    'message' => ProfileResponse::EXIST,
                    'data' => $profile,
                ]);
            }

            $validator = Validator::make($request->all(), [
                'firstname' => 'required|string',
                'lastname'  => 'required|string',
                'birthdate' => 'required|date',
                'phone'     => 'required|string|unique:profiles',
                'bio'       => 'required|string',
            ]);

            $validator->validate();
            $validator = $validator->safe()->all();

            Arr::set($validator, 'user_uuid', $userUuid);

            $profile = $this->repository->add($validator);

            return response()->json([
                'status' => ProfileResponse::SUCCESS,
                'message' => ProfileResponse::SUCCESS_CREATED,
                'data' => $profile,
            ], 201);
        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => ProfileResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $userUuid = $user->uuid;

        try {
            $profile = $this->repository->find($userUuid);
            if(empty($profile)) {
                return response()->json([
                    'status' => ProfileResponse::SUCCESS,
                    'message' => ProfileResponse::NOT_FOUND,
                    'data' => [],
                ]);
            }

            $validator = Validator::make($request->all(), [
                'firstname' => 'sometimes|string',
                'lastname'  => 'sometimes|string',
                'birthdate' => 'sometimes|date',
                'phone'     => "sometimes|string|unique:profiles,phone,$userUuid,user_uuid",
                'bio'       => 'sometimes|string',
            ]);

            $validator->validate();
            $validator = $validator->safe()->all();

            $updatedProfile = $this->repository->update($profile, $validator);

            return response()->json([
                'status' => ProfileResponse::SUCCESS,
                'message' => ProfileResponse::SUCCESS_UPDATED,
                'data' => $updatedProfile,
            ], 201);
        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => ProfileResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $userUuid = $user->uuid;
        $profile = $this->repository->find($userUuid);
        $rolesFilter = ['filter' => ['user_uuid' => $userUuid]];
        $roles = $this->roleRepository->browse($rolesFilter);
        $orgUuid = Arr::get($roles, '0.org_uuid');
        $organization = $this->orgRepository->findByUUID($orgUuid);
        $orgFilter = ['filter' => ['org_uuid' => $orgUuid]];
        $quranOrg = $this->quranOrgRepository->browse($orgFilter);
        $kelas = $this->kelasRepository->findActiveKelasByUserUUID($userUuid);

        return response()->json([
            'status' => ProfileResponse::SUCCESS,
            'message' => ProfileResponse::SUCCESS_RETRIEVED,
            'data' => [
                'user' => $user,
                'profile' => $profile,
                'organization' => $organization,
                'roles' => $roles,
                'qurans' => $quranOrg,
                'kelas' => $kelas,
            ],
        ]);
    }
}
