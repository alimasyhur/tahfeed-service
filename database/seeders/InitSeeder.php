<?php

namespace Database\Seeders;

use App\Models\TemplateQuranJuz;
use App\Models\TemplateQuranPage;
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

        $templateQuran = [
            'uuid' => Str::uuid(),
            'name' => 'Per Halaman',
            'description' => 'Al-Quran Per Juz Per Halaman',
            'slug' => 'per_halaman',
            'created_at' => $dateNow,
            'updated_at' => $dateNow,
        ];

        \App\Models\TemplateQuran::factory()->create($templateQuran);

        for ($juz = 1; $juz <= 30; $juz++) {
            $templateQuranJuz = [
                'uuid' => Str::uuid(),
                'name' => 'Juz ' . $juz,
                'description' => 'Al-Quran Juz ' . $juz,
                'constant' => $juz,
                'created_at' => $dateNow,
                'updated_at' => $dateNow,
            ];

            \App\Models\TemplateQuranJuz::factory()->create($templateQuranJuz);
        }

        $constantPage = 1;
        for ($page=1; $page <= 10; $page++) {
            $nameHalamanGanjil = $page . 'A';
            $templateHalamanGanjil = [
                'uuid' => Str::uuid(),
                'name' => $nameHalamanGanjil,
                'description' => 'Al-Qur Per Juz Halaman ' . $nameHalamanGanjil,
                'constant' => $constantPage,
                'template_quran_uuid' => $templateQuran['uuid'],
            ];
            \App\Models\TemplateQuranPage::factory()->create($templateHalamanGanjil);
            $constantPage++;

            $nameHalamanGenap = $page . 'B';
            $templateHalamanGenap = [
                'uuid' => Str::uuid(),
                'name' => $nameHalamanGenap,
                'description' => 'Al-Qur Per Juz Halaman ' . $nameHalamanGenap,
                'constant' => $constantPage,
                'template_quran_uuid' => $templateQuran['uuid'],
            ];
            \App\Models\TemplateQuranPage::factory()->create($templateHalamanGenap);
            $constantPage++;
        }

        $juzes = TemplateQuranJuz::orderBy('constant', 'ASC')->get();
        $pages = TemplateQuranPage::orderBy('constant', 'ASC')->get();
        $value = 0;
        foreach ($juzes as $juz) {
            $descriptionJuz = $juz->description;
            foreach ($pages as $page) {
                $value++;
                $descriptionPage = ' Halaman ' . $page->name;
                $descriptionJuzPage = $descriptionJuz . $descriptionPage;
                $juzPage = [
                    'uuid' => Str::uuid(),
                    'template_quran_juz_uuid' => $juz->uuid,
                    'template_quran_page_uuid' => $page->uuid,
                    'description' => $descriptionJuzPage,
                    'constant' => $juz->constant . str_pad($page->constant, 2, "0", STR_PAD_LEFT),
                    'value' => $value,
                ];

                \App\Models\TemplateQuranJuzPage::factory()->create($juzPage);
            }
        }

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

                $templateOrg = [
                    'uuid' => Str::uuid(),
                    'name' => Arr::get($templateQuran, 'description'),
                    'template_quran_uuid' => Arr::get($templateQuran, 'uuid'),
                    'org_uuid' => Arr::get($orgData, 'uuid'),
                ];

                \App\Models\TemplateQuranOrg::factory()->create($templateOrg);
            }

            \App\Models\Role::factory()->create($role);
        }
    }
}
