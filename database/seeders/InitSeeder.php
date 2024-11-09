<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dateNow = Carbon::now();
        $roles = [
            [
                'uuid' => Str::uuid(),
                'name' => 'Super Admin',
                'constant_value' => 1,
                'created_at' => $dateNow,
                'updated_at' => $dateNow,
            ],
            [
                'uuid' => Str::uuid(),
                'name' => 'Admin',
                'constant_value' => 2,
                'created_at' => $dateNow,
                'updated_at' => $dateNow,
            ],
            [
                'uuid' => Str::uuid(),
                'name' => 'Teacher',
                'constant_value' => 3,
                'created_at' => $dateNow,
                'updated_at' => $dateNow,
            ],
            [
                'uuid' => Str::uuid(),
                'name' => 'Student',
                'constant_value' => 4,
                'created_at' => $dateNow,
                'updated_at' => $dateNow,
            ],
        ];

        foreach($roles as $role) {
            $roleConstantValue = Arr::get($role, 'constant_value');
            if ($roleConstantValue === 1) {
                $userData = [
                    'uuid' => Str::uuid(),
                    'name' => 'alimasyhur',
                    'email' => 'tahfeed@gmail.com',
                    'password' => Hash::make('tahfeed-123'),
                    'created_at' => $dateNow,
                    'updated_at' => $dateNow,
                ];
                \App\Models\User::factory()->create($userData);

                $orgData = [
                    'uuid' => Str::uuid(),
                    'name' => 'tahfeed',
                    'domain' => 'tahfeed',
                    'bio' => 'tahfeed apps pioneer',
                    'address' => 'Sukoharjo, Jawa Tengah, Indonesia',
                    'phone' => '628156558085',
                    'is_verified' => 1,
                    'is_active' => 1,
                    'created_by' => Arr::get($userData, 'uuid'),
                    'created_at' => $dateNow,
                    'updated_at' => $dateNow,
                ];
                \App\Models\Organization::factory()->create($orgData);

                $orgUserRoleData = [
                    'uuid' => Str::uuid(),
                    'org_uuid' => Arr::get($orgData, 'uuid'),
                    'org_name' => Arr::get($orgData, 'name'),
                    'user_uuid' => Arr::get($userData, 'uuid'),
                    'role_uuid' => Arr::get($role, 'uuid'),
                    'role_name' => Arr::get($role, 'name'),
                    'constant_value' => Arr::get($role, 'constant_value'),
                    'is_active' => 1,
                    'is_confirmed' => 1,
                    'created_at' => $dateNow,
                    'updated_at' => $dateNow,
                ];
                \App\Models\OrgUserRole::factory()->create($orgUserRoleData);
            }


            // DB::table('roles')->insert($role);
            \App\Models\Role::factory()->create($role);
        }
    }
}
