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


    public function find($uuid)
    {
        $user = User::where('uuid', $uuid)
            ->first();

        return $user;
    }
}
