<?php

namespace App\Repositories;

use App\Constants\Pagination;
use App\Constants\ProfileStatus;
use App\Helpers\CommonHelper;
use App\Models\Profile;
use Illuminate\Support\Arr;

class ProfileRepository
{
    function __construct(
    ) {}

    private function getQuery($data = null)
    {
        $model = Profile::query();

        $qWord = Arr::get($data, 'q');
        if (!empty($qWord)) {
            $model->where(function ($query) use ($qWord) {
                $query->where('needs', 'like', "%$qWord%")
                    ->orWhere('requirement', 'like', "%$qWord%")
                    ->orWhere('amount', 'like', "%$qWord%")
                    ->orWhere('price', 'like', "%$qWord%")
                    ->orWhere('total', 'like', "%$qWord%")
                    ->orWhere('note', 'like', "%$qWord%")
                    ->orWhere('status', 'like', "%$qWord%");
            });
        }

        $needs = Arr::get($data, 'filter.needs');
        if (!empty($needs)) {
            $model->where('needs', 'like', "%$needs%");
        }

        $requirement = Arr::get($data, 'filter.requirement');
        if (!empty($requirement)) {
            $model->where('requirement', 'like', "%$requirement%");
        }

        $amount = Arr::get($data, 'filter.amount');
        if (!empty($amount)) {
            $model->where('amount', $amount);
        }

        $price = Arr::get($data, 'filter.price');
        if (!empty($price)) {
            $model->where('price', '=', $price);
        }

        $total = Arr::get($data, 'filter.total');
        if (!empty($total)) {
            $model->where('total', '=', $total);
        }

        $note = Arr::get($data, 'filter.note');
        if (!empty($note)) {
            $model->where('note', 'like', "%$note%");
        }

        $status = Arr::get($data, 'filter.status');
        if (!empty($status)) {
            $model->where('status', '=', $status);
        }

        $startDate = Arr::get($data, 'filter.start_date');
        if (!empty($startDate)) {
            $model->whereDate('created_at', '>=', $startDate);
        }

        $endDate = Arr::get($data, 'filter.end_date');
        if (!empty($endDate)) {
            $model->whereDate('created_at', '<=', $endDate);
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

        return $model->get();
    }

    public function find($userUuid)
    {
        $profile = Profile::where('user_uuid', $userUuid)
            ->first();

        return $profile;
    }

    public function getByListingId($listingId)
    {
        $Profile = Profile::where('listing_id', $listingId)->firstOrFail();

        return $Profile;
    }

    public function add($data)
    {
        $model = new Profile();
        $model->user_uuid = Arr::get($data, 'user_uuid');
        $model->firstname = Arr::get($data, 'firstname');
        $model->lastname = Arr::get($data, 'lastname');
        $model->birthdate = Arr::get($data, 'birthdate');
        $model->phone = Arr::get($data, 'phone');
        $model->bio = Arr::get($data, 'bio');

        $model->save();

        return $model;
    }

    public function update($profile, $data)
    {
        $profile->update($data);

        $profile->fresh();
        return $profile;
    }

    public function updateStatus($id, $data)
    {
        $model = Profile::findOrFail($id);
        $model->status = $data['status'];
        if (Arr::has($data, 'rejected_reason')) {
            $model->rejected_reason = $data['rejected_reason'];
        }
        $model->save();
        $model->fresh();

        return $model;
    }

    public function delete(Profile $Profile)
    {
        return $Profile->delete();
    }

    public function count($data)
    {
        $model = $this->getQuery($data);
        return $model->count();
    }
}
