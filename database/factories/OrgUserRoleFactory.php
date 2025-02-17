<?php

namespace Database\Factories;

use App\Models\OrgUserRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class OrgUserRoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OrgUserRole::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'org_uuid' => fake()->uuid(),
            'org_name' => fake()->name(),
            'user_uuid' => fake()->uuid(),
            'role_uuid' => fake()->uuid(),
            'role_name' => fake()->name(),
            'is_active' => fake()->boolean(),
            'is_confirmed' => fake()->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
