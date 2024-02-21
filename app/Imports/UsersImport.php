<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithMappedCells;

class UsersImport implements ToModel, SkipsEmptyRows
{

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new User([
            'student_id' => $row[0],
            'name' => $row[1],
            'email' => fake()->email,
            'password' => Hash::make('password'),
        ]);
    }
}
