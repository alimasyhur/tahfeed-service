<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadImageRequest;
use App\Models\OrgUserRole;
use App\Models\User;
use App\Repositories\ImageUploadRepository;
use App\Repositories\OrganizationRepository;
use App\Repositories\ProfileRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImageUploadController extends Controller
{
    private ImageUploadRepository $repository;
    private ProfileRepository $profileRepository;
    private OrganizationRepository $orgRepository;

    public function __construct(
        ImageUploadRepository $repository,
        ProfileRepository $profileRepository,
        OrganizationRepository $orgRepository,
    )
    {
        $this->repository = $repository;
        $this->profileRepository = $profileRepository;
        $this->orgRepository = $orgRepository;
    }

    /**
     * Upload profile picture
     *
     * @param UploadImageRequest $request
     * @return JsonResponse
     */
    public function uploadProfilePicture(UploadImageRequest $request): JsonResponse
    {
        $authUserUuid = $request->user()->uuid;
        try {
            DB::beginTransaction();

            $profile = $this->profileRepository->find($authUserUuid);
            $image = $request->file('image');

            // Delete old profile image if exists
            if ($profile->profile_image) {
                $this->repository->deleteUserImage($profile->user_uuid, $profile->profile_image);
            }

            // Upload new image
            $uploadResult = $this->repository->uploadUserProfileImage($profile->user_uuid, $image);

            // Update user profile image in database
            $profile->update([
                'profile_image' => $uploadResult['filename']
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Profile picture uploaded successfully',
                'data' => [
                    'image_url' => $uploadResult['url'],
                    'thumbnail_url' => $uploadResult['thumbnail_url'],
                    'filename' => $uploadResult['filename']
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload profile picture',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload organization logo
     *
     * @param UploadImageRequest $request
     * @return JsonResponse
     */
    public function uploadOrganizationLogo(UploadImageRequest $request): JsonResponse
    {
        $orgUUID = $request->org_uuid;
        Log::info('ORG_UUID: ', [
            'org_uuid' => $orgUUID,
        ]);

        try {
            DB::beginTransaction();

            $authUserUuid = $request->user()->uuid;

            $organization = $this->orgRepository->findByOrgUserUUID($authUserUuid, $orgUUID);
            $image = $request->file('image');

            // Delete old organization logo if exists
            if ($organization->logo_image) {
                $this->repository->deleteOrganizationImage($organization->uuid, $organization->logo_image);
            }

            // Upload new image
            $uploadResult = $this->repository->uploadOrganizationLogo($organization->uuid, $image);

            // Update organization logo in database
            $organization->update([
                'logo_image' => $uploadResult['filename']
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Organization logo uploaded successfully',
                'data' => [
                    'image_url' => $uploadResult['url'],
                    'thumbnail_url' => $uploadResult['thumbnail_url'],
                    'filename' => $uploadResult['filename']
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload organization logo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete profile picture
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteProfilePicture(Request $request): JsonResponse
    {
        try {
            $authUserUuid = $request->user()->uuid;
            DB::beginTransaction();

            $profile = $this->profileRepository->find($authUserUuid);

            if (!$profile->profile_image) {
                return response()->json([
                    'success' => false,
                    'message' => 'No profile picture to delete'
                ], 404);
            }

            // Delete image files
            $this->repository->deleteUserImage($profile->user_uuid, $profile->profile_image);

            // Update user profile image in database
            $profile->update([
                'profile_image' => null
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Profile picture deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete profile picture',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete organization logo
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteOrganizationLogo(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();

            if (!$user->organization) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not belong to any organization'
                ], 404);
            }

            if (!$this->canUpdateOrganization($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete organization logo'
                ], 403);
            }

            $organization = $user->organization;

            if (!$organization->logo_image) {
                return response()->json([
                    'success' => false,
                    'message' => 'No organization logo to delete'
                ], 404);
            }

            // Delete image files
            $this->repository->deleteOrganizationImage($organization->uuid, $organization->logo_image);

            // Update organization logo in database
            $organization->update([
                'logo_image' => null
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Organization logo deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete organization logo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if user can update organization
     *
     * @param User $user
     * @return bool
     */
    private function canUpdateOrganization(User $user): bool
    {
        // Check if user has admin or super admin role
        return $user->roles->contains(function ($role) {
            return in_array($role->name, ['super_admin', 'admin']);
        });
    }
}
