<?php

namespace App\Repositories;

use App\Constants\Pagination;
use App\Constants\ProfileStatus;
use App\Helpers\CommonHelper;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
    function __construct(
    ) {}

    private function getQuery($data = null)
    {
        return User::query()
            ->select(['uuid', 'name', 'email', 'created_at', 'updated_at'])
            ->when(Arr::get($data, 'q'), function ($query, $qWord) {
                $query->where(function ($subQuery) use ($qWord) {
                    $subQuery->where('name', 'like', "%{$qWord}%")
                            ->orWhere('email', 'like', "%{$qWord}%");
                });
            })
            ->when(Arr::get($data, 'filter.name'), function ($query, $name) {
                $query->where('name', 'like', "%{$name}%");
            })
            ->when(Arr::get($data, 'filter.email'), function ($query, $email) {
                $query->where('email', 'like', "%{$email}%");
            });
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

    public function findByEmail($email)
    {
        $user = User::where('email', $email)
            ->first();

        return $user;
    }

    public function count($data)
    {
        $model = $this->getQuery($data);
        return $model->count();
    }

    public function register($user) {
        try {
            $user = User::create([
                'name' => Arr::get($user, 'user_name'),
                'email' => Arr::get($user, 'email'),
                'password' => Hash::make(Arr::get($user, 'password')),
            ]);

            return $user->find($user->uuid);
        } catch (\Exception $e) {
        throw $e;
    }
    }
}
