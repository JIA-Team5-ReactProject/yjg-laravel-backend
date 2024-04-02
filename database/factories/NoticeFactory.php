<?php

namespace Database\Factories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notice>
 */
class NoticeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tagArr = ['restaurant', 'admin', 'salon'];
        return [
            'admin_id' => Admin::inRandomOrder()->first()->id,
            'title' => fake()->text(15),
            'content' => fake()->text,
            'tag' => $tagArr[random_int(0, 2)],
        ];
    }
}
