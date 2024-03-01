<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\User as UserAlias;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AfterService>
 */
class AfterServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $randomDate = fake()->dateTimeBetween($startOfMonth, $endOfMonth)->format('Y-m-d');
        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'title' => fake()->text(10),
            'content' => fake()->text,
            'status' => false,
            'visit_place' => fake()->buildingNumber,
            'visit_date' => $randomDate,
        ];
    }
}
