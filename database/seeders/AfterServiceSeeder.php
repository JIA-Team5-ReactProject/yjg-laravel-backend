<?php

namespace Database\Seeders;

use App\Models\AfterService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AfterServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AfterService::factory()->count(10)->create();
    }
}
