<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Http\Controllers\Admin\SalonBusinessHourController;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // for testing
        if(app()->environment() === 'testing'){
            $this->call([
                AdminTestSeeder::class,
            ]);
        }
        // for teammate
        $this->call([
            AdminSeeder::class,
            SalonSeeder::class,
            SalonBusinessHourSeeder::class,
        ]);
    }
}
