<?php

namespace Database\Seeders;

use App\Imports\UsersImport;
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
        Excel::import(new UsersImport(), env('EXCEL_USER_FILE_PATH'), 's3');
    }
}
