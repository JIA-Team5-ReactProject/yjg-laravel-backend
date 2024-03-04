<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teammateData = [
            [
                'name' => '김지훈',
                'phone_number' => '01012345678',
                'email' => env('EMAIL_1'),
                'password' => env('SEEDER_PASSWORD'),
                'admin_privilege' => true,
                'salon_privilege' => true,
                'restaurant_privilege' => true,
                'approved' => true,
                'master' => true,
            ],
            [
                'name'=> '권지훈',
                'phone_number' => '01012345678',
                'email' => env('EMAIL_2'),
                'password' => env('SEEDER_PASSWORD'),
                'admin_privilege' => true,
                'salon_privilege' => true,
                'restaurant_privilege' => true,
                'approved' => true,
                'master' => true,
            ],
            [
                'name'=> '김정원',
                'phone_number' => '01012345678',
                'email' => env('EMAIL_3'),
                'password' => env('SEEDER_PASSWORD'),
                'admin_privilege' => true,
                'salon_privilege' => true,
                'restaurant_privilege' => true,
                'approved' => true,
                'master' => true,
            ],
            [
                'name'=> '이민혁',
                'phone_number' => '01012345678',
                'email' => env('EMAIL_4'),
                'password' => env('SEEDER_PASSWORD'),
                'admin_privilege' => true,
                'salon_privilege' => true,
                'restaurant_privilege' => true,
                'approved' => true,
                'master' => true,
            ],
            [
                'name'=> '김현',
                'phone_number' => '01012345678',
                'email' =>  env('EMAIL_5'),
                'password' => env('SEEDER_PASSWORD'),
                'admin_privilege' => true,
                'salon_privilege' => true,
                'restaurant_privilege' => true,
                'approved' => true,
                'master' => true,
            ],
        ];

        foreach ($teammateData as $user) {
            $createAdmin = Admin::create($user);
        }

        Admin::factory()->count(5)->create();
    }
}
