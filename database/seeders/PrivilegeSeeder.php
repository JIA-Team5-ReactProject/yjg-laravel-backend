<?php

namespace Database\Seeders;

use App\Models\Privilege;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PrivilegeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $privileges = ['master', 'salon', 'admin', 'restaurant'];

        foreach ($privileges as $privilege) {
            Privilege::create([
                'privilege' => $privilege,
            ]);
        }
    }
}
