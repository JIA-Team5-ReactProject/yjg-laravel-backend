<?php

namespace Database\Seeders;

use App\Models\AfterService;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AfterServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = '[
    {
        "title": "방음벽이 무너졌어요",
        "content": "방음벽이 이상해요 옆방 소리가 너무 잘 들려요",
        "status": 0,
        "visit_place": "A동 1001",
        "visit_date": "2024-03-21"
    },
    {
        "title": "전등이 나갔어요",
        "content": "전등 고쳐주세요",
        "status": 0,
        "visit_place": "B동 1001호",
        "visit_date": "2024-03-21"
    },
    {
        "title": "커튼 줄이 끊어졌어요",
        "content": "제목 그대로 입니다",
        "status": 1,
        "visit_place": "A동 1001호",
        "visit_date": "2024-03-21"
    },
    {
        "title": "HP노트북이 고장났어요",
        "content": "노트북이 너무 느려졌어요",
        "status": 0,
        "visit_place": "인제니움관 302호",
        "visit_date": "2024-03-21"
    },
    {
        "title": "방 문 손잡이 고장",
        "content": "방 문이 안 열러요",
        "status": 1,
        "visit_place": "A동 506호",
        "visit_date": "2024-03-22"
    },
    {
        "title": "샤워기 헤드가 망가졌어요",
        "content": "샤워기 헤드가 깨져서 물이 샙니다",
        "status": 0,
        "visit_place": "A동 506호",
        "visit_date": "2024-03-21"
    },
    {
        "title": "ㅛㄷ뇨뇬",
        "content": "ㅇㄱㅅ뇨ㅛㄴ",
        "status": 0,
        "visit_place": "ㅅㄷㄴㄷㅅㄴㄷㅅ",
        "visit_date": "2024-03-19"
    },
    {
        "title": "냉장고",
        "content": "ㅇㅇ",
        "status": 0,
        "visit_place": "fqwwfq",
        "visit_date": "2024-03-18"
    },
    {
        "title": "냉장고",
        "content": "ㅇㅇ",
        "status": 0,
        "visit_place": "fqwwfq",
        "visit_date": "2024-03-01"
    }
]';
        $afterServices = json_decode($json, true);

        foreach ($afterServices as $afterService) {
            $user = User::where('name', '엄준식')->first();
            $afterService['user_id'] = $user->id;
            AfterService::create($afterService);
        }
    }
}
