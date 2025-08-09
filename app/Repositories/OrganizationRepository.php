<?php

namespace App\Repositories;

use App\Constants\OrganizationStatus;
use App\Constants\RoleUserStatus;
use App\Helpers\CommonHelper;
use App\Models\Organization;
use App\Models\OrgUserRole;
use App\Models\Role;
use App\Models\TemplateQuran;
use App\Models\TemplateQuranOrg;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrganizationRepository
{
    protected $roleRepository;
    protected $templateQuranRepository;
    function __construct(
        RoleRepository $roleRepository,
        TemplateQuranRepository $templateQuranRepository,
    ) {
        $this->roleRepository = $roleRepository;
        $this->templateQuranRepository = $templateQuranRepository;
    }

    private function getQuery($data = null)
    {
        $model = Organization::query();

        $qWord = Arr::get($data, 'q');
        if (!empty($qWord)) {
            $model->where(function ($query) use ($qWord) {
                $query->where('name', 'like', "%$qWord%")
                    ->orWhere('domain', 'like', "%$qWord%")
                    ->orWhere('email', 'like', "%$qWord%")
                    ->orWhere('phone', 'like', "%$qWord%");
            });
        }

        $name = Arr::get($data, 'filter.name');
        if (!empty($name)) {
            $model->where('name', 'like', "%$name%");
        }

        $domain = Arr::get($data, 'filter.domain');
        if (!empty($domain)) {
            $model->where('domain', 'like', "%$domain%");
        }

        $bio = Arr::get($data, 'filter.bio');
        if (!empty($bio)) {
            $model->where('bio', 'like', "%$bio%");
        }

        $address = Arr::get($data, 'filter.address');
        if (!empty($address)) {
            $model->where('address', 'like', "%$address%");
        }

        $phone = Arr::get($data, 'filter.phone');
        if (!empty($phone)) {
            $model->where('phone', 'like', "%$phone%");
        }

        $createdBy = Arr::get($data, 'filter.created_by');
        if(!empty($createdBy)) {
            $explodedCreatedBy = explode(',', $createdBy);
            $model->whereIn('created_by', $explodedCreatedBy);
        }

        return $model;
    }

    public function browse($data = null)
    {
        $model = $this->getQuery($data);

        CommonHelper::sortPageFilter($model, $data);

        $response = $model->get();

        return $response->map(function ($organization) {
            if ($organization->is_active == 1) {
                $organization->is_active_label = 'active';
                $organization->is_active_label_color = 'info';
            } else {
                $organization->is_active_label = 'not active';
                $organization->is_active_label_color = 'warning';
            }

            if ($organization->is_verified == 1) {
                $organization->is_verified_label = 'verified';
                $organization->is_verified_label_color = 'info';
            } else if ($organization->is_verified == 2) {
                $organization->is_verified_label = 'rejected';
                $organization->is_verified_label_color = 'error';
            } else {
                $organization->is_verified_label = 'not verified';
                $organization->is_verified_label_color = 'warning';
            }

            return $organization;
        });
    }

    public function findForUpdate($uuid, $userUuid)
    {
        if (RoleUserRepository::isSuperAdmin($userUuid)) {
            $organization = Organization::where('uuid', $uuid)
                ->first();

            return $organization;
        }

        $organization = Organization::where('uuid', $uuid)
            ->first();

        return $organization;
    }

    public function findByDomain($domain, $userUuid)
    {
        if (RoleUserRepository::isSuperAdmin($userUuid)) {
            $organization = Organization::where('domain', $domain)
                ->first();
        } else {
            $organization = Organization::where('created_by', $userUuid)
                ->where('domain', $domain)
                ->first();
        }

        $organization = $this->includeLabel($organization);

        return $organization;
    }

    public function find($userUuid)
    {
        $organization = Organization::where('created_by', $userUuid)
            ->first();

        return $organization;
    }

    public function findByUUID($uuid)
    {
        $organization = Organization::where('uuid', $uuid)
            ->first();

        $organization = $this->includeLabel($organization);

        return $organization;
    }

    public function findByOrgUserUUID($userUUID, $orgUUID)
    {
        $orgUser = OrgUserRole::where('user_uuid', $userUUID)
            ->where('org_uuid', $orgUUID)
            ->first();

        if (!$orgUser) {
            throw new Exception('User does not belogs to this org');
        }

        $organization = Organization::where('uuid', $orgUUID)
            ->first();

        return $organization;
    }

    public function add($data)
    {
        DB::beginTransaction();

        try {
            $createdBy = Arr::get($data, 'created_by');

            $model = new Organization();
            $model->name = Arr::get($data, 'name');
            $model->domain = Arr::get($data, 'domain');
            $model->bio = Arr::get($data, 'bio');
            $model->address = Arr::get($data, 'address');
            $model->email = Arr::get($data, 'email');
            $model->phone = Arr::get($data, 'phone');
            $model->is_verified = OrganizationStatus::VERIFICATION_PENDING;
            $model->is_active = OrganizationStatus::ACTIVE;
            $model->created_by = $createdBy;
            $model->save();

            $role = $this->roleRepository->findByName(Role::ROLE_SUPER_ADMIN);

            $roleUser = new OrgUserRole();
            $roleUser->org_uuid = $model->uuid;
            $roleUser->org_name = $model->name;
            $roleUser->user_uuid = $createdBy;
            $roleUser->role_uuid = $role->uuid;
            $roleUser->role_name = $role->name;
            $roleUser->constant_value = $role->constant_value;
            $roleUser->is_active = RoleUserStatus::ACTIVE;
            $roleUser->is_confirmed = RoleUserStatus::VERIFIED;
            $roleUser->save();

            $templateQuran = $this->templateQuranRepository->findBySlug(TemplateQuran::PER_HALAMAN);

            $quranTemplate = new TemplateQuranOrg();
            $quranTemplate->name = $templateQuran->description;
            $quranTemplate->template_quran_uuid = $templateQuran->uuid;
            $quranTemplate->org_uuid = $model->uuid;
            $quranTemplate->save();

            DB::commit();

            return $model;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update($organization, $data)
    {
        $organization->update($data);

        OrgUserRole::where('org_uuid', $organization->uuid)
            ->update(['org_name' => $organization->name]);

        $organization->fresh();
        return $organization;
    }

    public function updateStatus($id, $data)
    {
        $model = Organization::findOrFail($id);
        $model->status = $data['status'];
        if (Arr::has($data, 'rejected_reason')) {
            $model->rejected_reason = $data['rejected_reason'];
        }
        $model->save();
        $model->fresh();

        return $model;
    }

    public function delete(Organization $organization)
    {
        return $organization->delete();
    }

    public function count($data)
    {
        $model = $this->getQuery($data);
        return $model->count();
    }

    protected function includeLabel($organization) {
        if (!$organization) {
            return null;
        }

        if ($organization->is_active == 1) {
            $organization->is_active_label = 'active';
            $organization->is_active_label_color = 'info';
        } else {
            $organization->is_active_label = 'not active';
            $organization->is_active_label_color = 'warning';
        }

        if ($organization->is_verified == 1) {
            $organization->is_verified_label = 'verified';
            $organization->is_verified_label_color = 'info';
        } else if ($organization->is_verified == 2) {
            $organization->is_verified_label = 'rejected';
            $organization->is_verified_label_color = 'error';
        } else {
            $organization->is_verified_label = 'not verified';
            $organization->is_verified_label_color = 'warning';
        }

        return $organization;
    }
}
