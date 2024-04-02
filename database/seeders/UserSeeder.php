<?php

namespace Database\Seeders;

use App\Imports\UsersImport;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class UserSeeder extends Seeder
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
                'approved' => true,
            ],
            [
                'name'=> '권지훈',
                'phone_number' => '01012345678',
                'email' => env('EMAIL_2'),
                'password' => env('SEEDER_PASSWORD'),
                'approved' => true,
            ],
            [
                'name'=> '김정원',
                'phone_number' => '01012345678',
                'email' => env('EMAIL_3'),
                'password' => env('SEEDER_PASSWORD'),
                'approved' => true,
            ],
            [
                'name'=> '이민혁',
                'phone_number' => '01012345678',
                'email' => env('EMAIL_4'),
                'password' => env('SEEDER_PASSWORD'),
                'approved' => true,
            ],
            [
                'name'=> '김현',
                'phone_number' => '01012345678',
                'email' =>  env('EMAIL_5'),
                'password' => env('SEEDER_PASSWORD'),
                'approved' => true,
            ],
        ];

        foreach ($teammateData as $user) {
            $created = User::create($user);
            $created->privileges()->attach([1, 2, 3, 4]);
        }

        Excel::import(new UsersImport(), env('EXCEL_USER_FILE_PATH'), 's3');
        User::create([
            'student_id'   => '1901234',
            'name'         => '엄준식',
            'phone_number' => '01012345678',
            'email'        => 'um@um.com',
            'password'     => 'test1234',
        ]);
    }
}
