<?php

namespace App\Repositories;

use App\Constants\Pagination;
use App\Constants\ProfileStatus;
use App\Helpers\CommonHelper;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Arr;

class UserRepository
{
    function __construct(
    ) {}

    private function getQuery($data = null)
    {
        $model = User::query()->select('uuid', 'name', 'email', 'created_at', 'updated_at');

        $qWord = Arr::get($data, 'q');
        if (!empty($qWord)) {
            $model->where(function ($query) use ($qWord) {
                $query->where('name', 'like', "%$qWord%")
                    ->orWhere('email', 'like', "%$qWord%");
            });
        }

        $name = Arr::get($data, 'filter.name');
        if (!empty($name)) {
            $model->where('name', 'like', "%$name%");
        }

        $email = Arr::get($data, 'filter.email');
        if (!empty($email)) {
            $model->where('email', 'like', "%$email%");
        }

        return $model;
    }

    public function browse($data = null)
    {
        $model = $this->getQuery($data);

        CommonHelper::sortPageFilter($model, $data);

        $response = $model->with('orgUsers')->get();

        return $response;
    }


    public function find($uuid)
    {
        $user = User::where('uuid', $uuid)
            ->first();

        return $user;
    }

    public function count($data)
    {
        $model = $this->getQuery($data);
        return $model->count();
    }
}
