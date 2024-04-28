<?php

namespace Database\Seeders;

use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
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
                'name' => 'Admin',
                'created_at' => $dateNow,
                'updated_at' => $dateNow,
            ],
            [
                'uuid' => Str::uuid(),
                'name' => 'Teacher',
                'created_at' => $dateNow,
                'updated_at' => $dateNow,
            ],
            [
                'uuid' => Str::uuid(),
                'name' => 'Student',
                'created_at' => $dateNow,
                'updated_at' => $dateNow,
            ],
        ];

        foreach($roles as $role) {
            // DB::table('roles')->insert($role);
            \App\Models\Role::factory()->create($role);
        }
    }
}
