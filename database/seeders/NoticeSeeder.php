<?php

namespace Database\Seeders;

use App\Models\Notice;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NoticeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = '[
    {
        "user_id": 1,
        "title": "[글로벌생활관] 글로벌캠퍼스 생활관 미용실 운영 안내",
        "content": "<p>글로벌캠퍼스 운영본부 학생지원팀 입니다. </p><p>글로벌생활관 B동 210호에 위치한 미용실이 다음주 화요일(12/12) 및 목요일(12/14)에 원장님 개인사정으로 휴업 합니다. </p><p>아울러 동계방학기간(2023.12.19.~2024.02.29.)에는 매주 목요일만 운영 하오니 참고하시기 바랍니다.</p><p><span class=\"ql-size-large\"><span class=\"ql-cursor\">﻿</span></span>.관련문의:054-970-9677(학생지원팀)</p>",
        "tag": "salon",
        "urgent": 0,
        "created_at": "2024-03-19T10:33:48.000000Z",
        "updated_at": "2024-03-19T10:33:48.000000Z"
    },
    {
        "user_id": 1,
        "title": "[긴급] 3월 26일 소방훈련을 실시합니다.",
        "content": "<p>소방훈련을 19시에 진행하오니</p><p>학생 여러분들은 택배보관함 앞으로 모여주시기 바랍니다.</p>",
        "tag": "admin",
        "urgent": 1,
        "created_at": "2024-03-19T11:18:14.000000Z",
        "updated_at": "2024-03-19T11:21:34.000000Z"
    }
]';
        $notices = json_decode($json, true);

        foreach ($notices as $notice) {
            Notice::create($notice);
        }
    }
}
