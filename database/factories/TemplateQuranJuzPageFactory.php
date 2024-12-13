<?php

namespace Database\Factories;

use App\Models\TemplateQuranJuzPage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TemplateQuranJuzPageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TemplateQuranJuzPage::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'template_quran_juz_uuid' => $this->faker->uuid(),
            'template_quran_page_uuid' => $this->faker->uuid(),
            'description' => $this->faker->words(),
            'constant' => $this->faker->numberBetween(1, 20),
            'value' => $this->faker->randomDigit(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
