<?php

namespace App\Http\Controllers\Api;

use App\Constants\RoleResponse;
use App\Constants\UserResponse;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Repositories\RoleUserRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $repository;
    protected $userRepository;

    public function __construct(
        RoleUserRepository $repository,
        UserRepository $userRepository,
    )
    {
        $this->repository = $repository;
        $this->userRepository = $userRepository;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'data' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function login(Request $request)
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        if (!$this->repository->isActiveRole($user->uuid)) {
            return response()->json([
                'message' => UserResponse::ACCOUNT_NOT_ACTIVE,
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login success',
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function logout()
    {
        Auth::user()->tokens()->delete();
        return response()->json([
            'message' => 'logout success'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $authUserUuid = $request->user()->uuid;

        $validator = Validator::make($request->all(), [
            'user_uuid' => 'required|string',
            'new_password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $validator = $validator->safe()->all();

        $authUserRole = $this->repository->findByUserUuid($authUserUuid);
        if ($authUserRole->role_name !== Role::ROLE_SUPER_ADMIN && $authUserRole->role_name !== Role::ROLE_ADMIN) {
            return response()->json([
                'status' => RoleResponse::ERROR,
                'message' => RoleResponse::NOT_AUTHORIZED,
                'error' => true,
                'data' => [],
            ], 403);
        }

        $requestUserUuid = Arr::get($validator, 'user_uuid');
        $userToModify = $this->repository->findByUserUuid($requestUserUuid);
        if ($authUserRole->role_name === Role::ROLE_ADMIN && $authUserRole->org_uuid !== $userToModify->org_uuid) {
            return response()->json([
                'status' => RoleResponse::ERROR,
                'message' => RoleResponse::NOT_AUTHORIZED,
                'error' => true,
                'data' => [],
            ], 403);
        }

        $user = $this->userRepository->find($requestUserUuid);
        $newPassword = Arr::get($validator, 'new_password');
        $user->update([
            'password' => Hash::make($newPassword),
        ]);
        $user->fresh();

        return response()->json([
            'status' => UserResponse::SUCCESS,
            'message' => UserResponse::SUCCESS_UPDATED,
            'data' => $user,
        ]);
    }
}
