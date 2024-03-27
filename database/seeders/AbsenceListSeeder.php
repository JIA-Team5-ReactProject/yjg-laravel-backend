<?php

namespace Database\Seeders;

use App\Models\AbsenceList;
use App\Models\User;
use Illuminate\Database\Seeder;

class AbsenceListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = '[
    {
        "start_date": "2024-03-21",
        "end_date": "2024-03-21",
        "type": "go",
        "status": 1,
        "content": "점심 밥"
    },
    {
        "start_date": "2024-03-22",
        "end_date": "2024-03-22",
        "type": "go",
        "status": 1,
        "content": "점심 밥"
    },
    {
        "start_date": "2024-03-23",
        "end_date": "2024-03-24",
        "type": "sleep",
        "status": 1,
        "content": "집"
    },
    {
        "start_date": "2024-03-21",
        "end_date": "2024-03-21",
        "type": "go",
        "status": 1,
        "content": "돈코츠 라멘 먹으러갑니다."
    },
    {
        "start_date": "2024-03-22",
        "end_date": "2024-03-22",
        "type": "go",
        "status": 1,
        "content": "소유 라멘 먹으러 갑니다."
    },
    {
        "start_date": "2024-03-23",
        "end_date": "2024-03-24",
        "type": "sleep",
        "status": 1,
        "content": "미소 라멘 먹으러 갑니다."
    },
    {
        "start_date": "2024-03-20",
        "end_date": "2024-03-21",
        "type": "sleep",
        "status": 1,
        "content": "ㅅ교ㅛㄱㄴ"
    }
]';
        $lists = json_decode($json, true);

        foreach ($lists as $list) {
            $user = User::where('name', '엄준식')->first();
            $list['user_id'] = $user->id;
            AbsenceList::create($list);
        }
    }
}
