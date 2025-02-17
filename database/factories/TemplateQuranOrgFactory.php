<?php

namespace Database\Factories;

use App\Models\TemplateQuranJuzPage;
use App\Models\TemplateQuranOrg;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TemplateQuranOrgFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TemplateQuranOrg::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->words(),
            'template_quran_uuid' => $this->faker->uuid(),
            'org_uuid' => $this->faker->uuid(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
