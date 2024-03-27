<?php

namespace Database\Seeders;

use App\Models\BusSchedule;
use Illuminate\Database\Seeder;

class BusScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = '[
    {
        "bus_round_id": 1,
        "station": "영진전문(복현서문)",
        "bus_time": "07:29"
    },
    {
        "bus_round_id": 1,
        "station": "우방(원어민)",
        "bus_time": "07:32"
    },
    {
        "bus_round_id": 1,
        "station": "영어마을",
        "bus_time": "08:00"
    },
    {
        "bus_round_id": 1,
        "station": "문양역",
        "bus_time": "08:30"
    },
    {
        "bus_round_id": 1,
        "station": "글로벌캠퍼스",
        "bus_time": "08:45"
    },
    {
        "bus_round_id": 1,
        "station": "영어마을",
        "bus_time": "08:50"
    },
    {
        "bus_round_id": 2,
        "station": "영진전문(복현서문)",
        "bus_time": "07:45"
    },
    {
        "bus_round_id": 2,
        "station": "칠곡운암역",
        "bus_time": "08:17"
    },
    {
        "bus_round_id": 2,
        "station": "어울아트센터",
        "bus_time": "08:21"
    },
    {
        "bus_round_id": 2,
        "station": "글로벌캠퍼스",
        "bus_time": "08:45"
    },
    {
        "bus_round_id": 2,
        "station": "영어마을",
        "bus_time": "08:50"
    },
    {
        "bus_round_id": 3,
        "station": "복현캠퍼스 도서관앞",
        "bus_time": "09:00"
    },
    {
        "bus_round_id": 4,
        "station": "영진전문(복현서문)",
        "bus_time": "13:00"
    },
    {
        "bus_round_id": 4,
        "station": "태전역",
        "bus_time": "13:15"
    },
    {
        "bus_round_id": 4,
        "station": "글로벌캠퍼스",
        "bus_time": "13:35"
    },
    {
        "bus_round_id": 4,
        "station": "영어마을",
        "bus_time": "13:40"
    },
    {
        "bus_round_id": 5,
        "station": "복현캠퍼스 도서관앞",
        "bus_time": "13:00"
    },
    {
        "bus_round_id": 5,
        "station": "태전역 365제일큰약국",
        "bus_time": "13:25"
    },
    {
        "bus_round_id": 3,
        "station": "태전역 365제일큰약국",
        "bus_time": "09:25"
    },
    {
        "bus_round_id": 3,
        "station": "글로벌캠퍼스",
        "bus_time": "09:45"
    },
    {
        "bus_round_id": 3,
        "station": "글로벌생활관",
        "bus_time": "09:47"
    },
    {
        "bus_round_id": 5,
        "station": "글로벌캠퍼스",
        "bus_time": "13:45"
    },
    {
        "bus_round_id": 5,
        "station": "글로벌생활관",
        "bus_time": "13:47"
    },
    {
        "bus_round_id": 6,
        "station": "영진전문(복현서문)",
        "bus_time": "16:10"
    },
    {
        "bus_round_id": 6,
        "station": "태전역",
        "bus_time": "16:25"
    },
    {
        "bus_round_id": 6,
        "station": "글로벌캠퍼스",
        "bus_time": "16:50"
    },
    {
        "bus_round_id": 6,
        "station": "영어마을",
        "bus_time": "16:55"
    },
    {
        "bus_round_id": 7,
        "station": "복현캠퍼스 도서관앞",
        "bus_time": "17:00"
    },
    {
        "bus_round_id": 7,
        "station": "태전역 365제일큰약국",
        "bus_time": "17:25"
    },
    {
        "bus_round_id": 7,
        "station": "글로벌캠퍼스",
        "bus_time": "17:45"
    },
    {
        "bus_round_id": 7,
        "station": "글로벌생활관",
        "bus_time": "17:47"
    },
    {
        "bus_round_id": 8,
        "station": "복현캠퍼스 도서관앞",
        "bus_time": "18:00"
    },
    {
        "bus_round_id": 8,
        "station": "태전역 365제일큰약국",
        "bus_time": "18:25"
    },
    {
        "bus_round_id": 8,
        "station": "글로벌캠퍼스",
        "bus_time": "18:45"
    },
    {
        "bus_round_id": 8,
        "station": "글로벌생활관",
        "bus_time": "18:47"
    },
    {
        "bus_round_id": 9,
        "station": "영진전문(복현서문)",
        "bus_time": "19:10"
    },
    {
        "bus_round_id": 9,
        "station": "태전역",
        "bus_time": "19:30"
    },
    {
        "bus_round_id": 9,
        "station": "글로벌캠퍼스",
        "bus_time": "19:50"
    },
    {
        "bus_round_id": 9,
        "station": "영어마을",
        "bus_time": "19:55"
    },
    {
        "bus_round_id": 10,
        "station": "영진전문(복현서문)",
        "bus_time": "20:45"
    },
    {
        "bus_round_id": 10,
        "station": "태전역",
        "bus_time": "21:00"
    },
    {
        "bus_round_id": 10,
        "station": "글로벌캠퍼스",
        "bus_time": "21:20"
    },
    {
        "bus_round_id": 10,
        "station": "영어마을",
        "bus_time": "21:25"
    },
    {
        "bus_round_id": 11,
        "station": "복현캠퍼스 도서관앞",
        "bus_time": "22:00"
    },
    {
        "bus_round_id": 11,
        "station": "태전역 365제일큰약국",
        "bus_time": "22:25"
    },
    {
        "bus_round_id": 11,
        "station": "글로벌캠퍼스",
        "bus_time": "22:45"
    },
    {
        "bus_round_id": 11,
        "station": "글로벌생활관",
        "bus_time": "22:47"
    },
    {
        "bus_round_id": 12,
        "station": "글로벌생활관",
        "bus_time": "08:00"
    },
    {
        "bus_round_id": 12,
        "station": "글로벌캠퍼스",
        "bus_time": "08:02"
    },
    {
        "bus_round_id": 12,
        "station": "태전역 365제일큰약국 건너",
        "bus_time": "08:27"
    },
    {
        "bus_round_id": 12,
        "station": "복현서문",
        "bus_time": "08:47"
    },
    {
        "bus_round_id": 13,
        "station": "글로벌생활관",
        "bus_time": "09:00"
    },
    {
        "bus_round_id": 13,
        "station": "글로벌캠퍼스",
        "bus_time": "09:02"
    },
    {
        "bus_round_id": 13,
        "station": "태전역 365제일큰약국 건너",
        "bus_time": "09:27"
    },
    {
        "bus_round_id": 13,
        "station": "복현서문",
        "bus_time": "09:47"
    },
    {
        "bus_round_id": 14,
        "station": "영어마을",
        "bus_time": "10:00"
    },
    {
        "bus_round_id": 14,
        "station": "글로벌생활관",
        "bus_time": "10:03"
    },
    {
        "bus_round_id": 14,
        "station": "글로벌캠퍼스",
        "bus_time": "10:05"
    },
    {
        "bus_round_id": 14,
        "station": "태전역",
        "bus_time": "10:25"
    },
    {
        "bus_round_id": 14,
        "station": "영진전문",
        "bus_time": "10:45"
    },
    {
        "bus_round_id": 15,
        "station": "글로벌생활관",
        "bus_time": "12:00"
    },
    {
        "bus_round_id": 15,
        "station": "글로벌캠퍼스",
        "bus_time": "12:02"
    },
    {
        "bus_round_id": 15,
        "station": "태전역 365제일큰약국 건너",
        "bus_time": "12:27"
    },
    {
        "bus_round_id": 15,
        "station": "복현서문",
        "bus_time": "12:47"
    },
    {
        "bus_round_id": 16,
        "station": "영어마을",
        "bus_time": "14:30"
    },
    {
        "bus_round_id": 16,
        "station": "글로벌캠퍼스",
        "bus_time": "14:33"
    },
    {
        "bus_round_id": 16,
        "station": "글로벌생활관",
        "bus_time": "14:35"
    },
    {
        "bus_round_id": 16,
        "station": "태전역",
        "bus_time": "14:55"
    },
    {
        "bus_round_id": 16,
        "station": "영진전문(복현서문)",
        "bus_time": "15:15"
    },
    {
        "bus_round_id": 17,
        "station": "글로벌생활관",
        "bus_time": "17:00"
    },
    {
        "bus_round_id": 17,
        "station": "글로벌캠퍼스",
        "bus_time": "17:02"
    },
    {
        "bus_round_id": 17,
        "station": "태전역 365제일큰약국 건너",
        "bus_time": "17:27"
    },
    {
        "bus_round_id": 17,
        "station": "복현서문",
        "bus_time": "17:47"
    },
    {
        "bus_round_id": 18,
        "station": "영어마을",
        "bus_time": "18:05"
    },
    {
        "bus_round_id": 18,
        "station": "글로벌생활관",
        "bus_time": "18:08"
    },
    {
        "bus_round_id": 18,
        "station": "글로벌캠퍼스",
        "bus_time": "18:10"
    },
    {
        "bus_round_id": 18,
        "station": "칠곡현대1차아파트",
        "bus_time": "18:25"
    },
    {
        "bus_round_id": 18,
        "station": "칠곡운암역",
        "bus_time": "18:30"
    },
    {
        "bus_round_id": 18,
        "station": "영진전문(복현서문)",
        "bus_time": "19:05"
    },
    {
        "bus_round_id": 19,
        "station": "영어마을",
        "bus_time": "18:05"
    },
    {
        "bus_round_id": 19,
        "station": "글로벌생활관",
        "bus_time": "18:08"
    },
    {
        "bus_round_id": 19,
        "station": "글로벌캠퍼스",
        "bus_time": "18:10"
    },
    {
        "bus_round_id": 19,
        "station": "태전역",
        "bus_time": "18:25"
    },
    {
        "bus_round_id": 19,
        "station": "우방(원어민)",
        "bus_time": "19:05"
    },
    {
        "bus_round_id": 19,
        "station": "영진전문(복현서문)",
        "bus_time": "19:15"
    },
    {
        "bus_round_id": 20,
        "station": "글로벌생활관",
        "bus_time": "19:00"
    },
    {
        "bus_round_id": 20,
        "station": "글로벌캠퍼스",
        "bus_time": "19:02"
    },
    {
        "bus_round_id": 20,
        "station": "태전역 365제일큰약국 건너",
        "bus_time": "19:27"
    },
    {
        "bus_round_id": 20,
        "station": "복현서문",
        "bus_time": "19:47"
    },
    {
        "bus_round_id": 21,
        "station": "영어마을",
        "bus_time": "20:00"
    },
    {
        "bus_round_id": 21,
        "station": "글로벌캠퍼스",
        "bus_time": "20:03"
    },
    {
        "bus_round_id": 21,
        "station": "태전역",
        "bus_time": "20:20"
    },
    {
        "bus_round_id": 21,
        "station": "영진서문",
        "bus_time": "20:40"
    },
    {
        "bus_round_id": 22,
        "station": "영어마을",
        "bus_time": "21:25"
    },
    {
        "bus_round_id": 22,
        "station": "글로벌생활관",
        "bus_time": "21:28"
    },
    {
        "bus_round_id": 22,
        "station": "태전역",
        "bus_time": "21:45"
    },
    {
        "bus_round_id": 22,
        "station": "영진전문(복현서문)",
        "bus_time": "22:00"
    },
    {
        "bus_round_id": 23,
        "station": "영진전문(복현서문)",
        "bus_time": "08:20"
    },
    {
        "bus_round_id": 23,
        "station": "태전역",
        "bus_time": "08:35"
    },
    {
        "bus_round_id": 23,
        "station": "글로벌생활관",
        "bus_time": "08:50"
    },
    {
        "bus_round_id": 23,
        "station": "영어마을",
        "bus_time": "08:55"
    },
    {
        "bus_round_id": 24,
        "station": "시티병원",
        "bus_time": "10:40"
    },
    {
        "bus_round_id": 24,
        "station": "SK빌딩",
        "bus_time": "11:00"
    },
    {
        "bus_round_id": 24,
        "station": "태전역",
        "bus_time": "11:20"
    },
    {
        "bus_round_id": 24,
        "station": "글로벌생활관",
        "bus_time": "11:45"
    },
    {
        "bus_round_id": 24,
        "station": "영어마을",
        "bus_time": "11:50"
    },
    {
        "bus_round_id": 25,
        "station": "영진전문(복현서문)",
        "bus_time": "16:00"
    },
    {
        "bus_round_id": 25,
        "station": "SK빌딩",
        "bus_time": "16:20"
    },
    {
        "bus_round_id": 31,
        "station": "태전역",
        "bus_time": "16:40"
    },
    {
        "bus_round_id": 25,
        "station": "글로벌생활관",
        "bus_time": "17:00"
    },
    {
        "bus_round_id": 25,
        "station": "영어마을",
        "bus_time": "17:05"
    },
    {
        "bus_round_id": 26,
        "station": "영진전문(복현서문)",
        "bus_time": "20:10"
    },
    {
        "bus_round_id": 26,
        "station": "SK빌딩",
        "bus_time": "20:30"
    },
    {
        "bus_round_id": 26,
        "station": "태전역",
        "bus_time": "20:50"
    },
    {
        "bus_round_id": 26,
        "station": "글로벌생활관",
        "bus_time": "21:10"
    },
    {
        "bus_round_id": 26,
        "station": "영어마을",
        "bus_time": "21:15"
    },
    {
        "bus_round_id": 27,
        "station": "영어마을",
        "bus_time": "10:00"
    },
    {
        "bus_round_id": 27,
        "station": "글로벌생활관",
        "bus_time": "10:03"
    },
    {
        "bus_round_id": 27,
        "station": "태전역",
        "bus_time": "10:25"
    },
    {
        "bus_round_id": 27,
        "station": "시티병원",
        "bus_time": "10:40"
    },
    {
        "bus_round_id": 28,
        "station": "영어마을",
        "bus_time": "14:00"
    },
    {
        "bus_round_id": 28,
        "station": "글로벌생활관",
        "bus_time": "14:03"
    },
    {
        "bus_round_id": 28,
        "station": "태전역",
        "bus_time": "14:25"
    },
    {
        "bus_round_id": 28,
        "station": "SK빌딩",
        "bus_time": "14:45"
    },
    {
        "bus_round_id": 28,
        "station": "영진전문(복현서문)",
        "bus_time": "15:10"
    },
    {
        "bus_round_id": 29,
        "station": "영어마을",
        "bus_time": "18:10"
    },
    {
        "bus_round_id": 29,
        "station": "글로벌생활관",
        "bus_time": "18:13"
    },
    {
        "bus_round_id": 29,
        "station": "태전역",
        "bus_time": "18:35"
    },
    {
        "bus_round_id": 29,
        "station": "영진전문(복현서문)",
        "bus_time": "18:55"
    },
    {
        "bus_round_id": 30,
        "station": "영어마을",
        "bus_time": "21:15"
    },
    {
        "bus_round_id": 30,
        "station": "글로벌생활관",
        "bus_time": "21:18"
    },
    {
        "bus_round_id": 30,
        "station": "태전역",
        "bus_time": "21:40"
    },
    {
        "bus_round_id": 30,
        "station": "영진전문(복현서문)",
        "bus_time": "22:00"
    },
    {
        "bus_round_id": 31,
        "station": "영진전문(복현서문)",
        "bus_time": "07:29"
    },
    {
        "bus_round_id": 31,
        "station": "우방(원어민)",
        "bus_time": "07:32"
    },
    {
        "bus_round_id": 31,
        "station": "영어마을",
        "bus_time": "08:00"
    },
    {
        "bus_round_id": 31,
        "station": "문앙역",
        "bus_time": "08:30"
    },
    {
        "bus_round_id": 31,
        "station": "글로벌캠퍼스",
        "bus_time": "08:45"
    },
    {
        "bus_round_id": 31,
        "station": "영어마을",
        "bus_time": "08:50"
    },
    {
        "bus_round_id": 32,
        "station": "영진전문(복현서문)",
        "bus_time": "07:45"
    },
    {
        "bus_round_id": 32,
        "station": "칠곡운암역",
        "bus_time": "08:17"
    },
    {
        "bus_round_id": 32,
        "station": "기업은행칠곡점",
        "bus_time": "08:21"
    },
    {
        "bus_round_id": 32,
        "station": "글로벌캠퍼스",
        "bus_time": "08:45"
    },
    {
        "bus_round_id": 32,
        "station": "영어마을",
        "bus_time": "08:50"
    },
    {
        "bus_round_id": 33,
        "station": "영진전문(복현서문)",
        "bus_time": "13:00"
    },
    {
        "bus_round_id": 33,
        "station": "태전역",
        "bus_time": "13:15"
    },
    {
        "bus_round_id": 33,
        "station": "글로벌캠퍼스",
        "bus_time": "13:35"
    },
    {
        "bus_round_id": 33,
        "station": "영어마을",
        "bus_time": "13:40"
    },
    {
        "bus_round_id": 34,
        "station": "영진전문(복현서문)",
        "bus_time": "15:50"
    },
    {
        "bus_round_id": 34,
        "station": "태전역",
        "bus_time": "16:05"
    },
    {
        "bus_round_id": 34,
        "station": "글로벌캠퍼스",
        "bus_time": "16:25"
    },
    {
        "bus_round_id": 34,
        "station": "영어마을",
        "bus_time": "16:30"
    },
    {
        "bus_round_id": 35,
        "station": "영진전문(복현서문)",
        "bus_time": "19:10"
    },
    {
        "bus_round_id": 35,
        "station": "태전역",
        "bus_time": "19:30"
    },
    {
        "bus_round_id": 35,
        "station": "글로벌캠퍼스",
        "bus_time": "19:50"
    },
    {
        "bus_round_id": 35,
        "station": "영어마을",
        "bus_time": "19:55"
    },
    {
        "bus_round_id": 36,
        "station": "영진전문(복현서문)",
        "bus_time": "20:45"
    },
    {
        "bus_round_id": 36,
        "station": "태전역",
        "bus_time": "21:00"
    },
    {
        "bus_round_id": 36,
        "station": "글로벌캠퍼스",
        "bus_time": "21:20"
    },
    {
        "bus_round_id": 36,
        "station": "영어마을",
        "bus_time": "21:25"
    },
    {
        "bus_round_id": 37,
        "station": "영어마을",
        "bus_time": "10:00"
    },
    {
        "bus_round_id": 37,
        "station": "글로벌생활관",
        "bus_time": "10:03"
    },
    {
        "bus_round_id": 37,
        "station": "글로벌캠퍼스",
        "bus_time": "10:05"
    },
    {
        "bus_round_id": 37,
        "station": "태전역",
        "bus_time": "10:25"
    },
    {
        "bus_round_id": 37,
        "station": "영진전문대",
        "bus_time": "10:45"
    },
    {
        "bus_round_id": 38,
        "station": "영어마을",
        "bus_time": "14:30"
    },
    {
        "bus_round_id": 38,
        "station": "글로벌생활관",
        "bus_time": "14:33"
    },
    {
        "bus_round_id": 38,
        "station": "글로벌캠퍼스",
        "bus_time": "14:35"
    },
    {
        "bus_round_id": 38,
        "station": "태전역",
        "bus_time": "14:55"
    },
    {
        "bus_round_id": 38,
        "station": "영진전문(복현서문)",
        "bus_time": "15:15"
    },
    {
        "bus_round_id": 39,
        "station": "영어마을",
        "bus_time": "17:05"
    },
    {
        "bus_round_id": 39,
        "station": "글로벌생활관",
        "bus_time": "17:08"
    },
    {
        "bus_round_id": 39,
        "station": "글로벌캠퍼스",
        "bus_time": "17:10"
    },
    {
        "bus_round_id": 39,
        "station": "기업은행칠곡점",
        "bus_time": "17:25"
    },
    {
        "bus_round_id": 39,
        "station": "칠곡운암역",
        "bus_time": "17:30"
    },
    {
        "bus_round_id": 39,
        "station": "영진전문(복현서문)",
        "bus_time": "18:15"
    },
    {
        "bus_round_id": 40,
        "station": "영어마을",
        "bus_time": "18:05"
    },
    {
        "bus_round_id": 40,
        "station": "글로벌생활관",
        "bus_time": "18:08"
    },
    {
        "bus_round_id": 40,
        "station": "글로벌캠퍼스",
        "bus_time": "18:10"
    },
    {
        "bus_round_id": 40,
        "station": "태전역",
        "bus_time": "18:25"
    },
    {
        "bus_round_id": 40,
        "station": "우방(원어민)",
        "bus_time": "18:57"
    },
    {
        "bus_round_id": 40,
        "station": "영진전문(복현서문)",
        "bus_time": "19:00"
    },
    {
        "bus_round_id": 41,
        "station": "영어마을",
        "bus_time": "20:00"
    },
    {
        "bus_round_id": 41,
        "station": "글로벌생활관",
        "bus_time": "20:03"
    },
    {
        "bus_round_id": 41,
        "station": "태전역",
        "bus_time": "20:20"
    },
    {
        "bus_round_id": 41,
        "station": "영진전문(복현서문)",
        "bus_time": "20:40"
    },
    {
        "bus_round_id": 42,
        "station": "영어마을",
        "bus_time": "21:25"
    },
    {
        "bus_round_id": 42,
        "station": "글로벌생활관",
        "bus_time": "21:28"
    },
    {
        "bus_round_id": 42,
        "station": "태전역",
        "bus_time": "21:45"
    },
    {
        "bus_round_id": 42,
        "station": "영진전문(복현서문)",
        "bus_time": "22:00"
    },
    {
        "bus_round_id": 43,
        "station": "영진전문(복현서문)",
        "bus_time": "08:20"
    },
    {
        "bus_round_id": 43,
        "station": "태전역",
        "bus_time": "08:35"
    },
    {
        "bus_round_id": 43,
        "station": "글로벌생활관",
        "bus_time": "08:50"
    },
    {
        "bus_round_id": 43,
        "station": "영어마을",
        "bus_time": "08:55"
    },
    {
        "bus_round_id": 43,
        "station": "시티병원",
        "bus_time": "10:40"
    },
    {
        "bus_round_id": 43,
        "station": "SK빌딩",
        "bus_time": "11:00"
    },
    {
        "bus_round_id": 43,
        "station": "태전역",
        "bus_time": "11:20"
    },
    {
        "bus_round_id": 43,
        "station": "글로벌생활관",
        "bus_time": "11:45"
    },
    {
        "bus_round_id": 43,
        "station": "영어마을",
        "bus_time": "11:50"
    },
    {
        "bus_round_id": 44,
        "station": "영진전문(복현서문)",
        "bus_time": "16:00"
    },
    {
        "bus_round_id": 44,
        "station": "SK빌딩",
        "bus_time": "16:20"
    },
    {
        "bus_round_id": 44,
        "station": "태전역",
        "bus_time": "16:40"
    },
    {
        "bus_round_id": 44,
        "station": "글로벌생활관",
        "bus_time": "17:00"
    },
    {
        "bus_round_id": 44,
        "station": "영어마을",
        "bus_time": "17:05"
    },
    {
        "bus_round_id": 45,
        "station": "영진전문(복현서문)",
        "bus_time": "20:10"
    },
    {
        "bus_round_id": 45,
        "station": "SK빌딩",
        "bus_time": "20:30"
    },
    {
        "bus_round_id": 45,
        "station": "태전역",
        "bus_time": "20:50"
    },
    {
        "bus_round_id": 45,
        "station": "글로벌생활관",
        "bus_time": "21:10"
    },
    {
        "bus_round_id": 45,
        "station": "영어마을",
        "bus_time": "21:15"
    },
    {
        "bus_round_id": 46,
        "station": "영어마을",
        "bus_time": "10:00"
    },
    {
        "bus_round_id": 46,
        "station": "글로벌생활관",
        "bus_time": "10:03"
    },
    {
        "bus_round_id": 46,
        "station": "태전역",
        "bus_time": "10:25"
    },
    {
        "bus_round_id": 46,
        "station": "시티병원",
        "bus_time": "10:40"
    },
    {
        "bus_round_id": 47,
        "station": "영어마을",
        "bus_time": "14:00"
    },
    {
        "bus_round_id": 47,
        "station": "글로벌생활관",
        "bus_time": "14:03"
    },
    {
        "bus_round_id": 47,
        "station": "태전역",
        "bus_time": "14:25"
    },
    {
        "bus_round_id": 47,
        "station": "SK빌딩",
        "bus_time": "14:45"
    },
    {
        "bus_round_id": 47,
        "station": "영진전문(복현서문)",
        "bus_time": "15:10"
    },
    {
        "bus_round_id": 48,
        "station": "영어마을",
        "bus_time": "18:10"
    },
    {
        "bus_round_id": 48,
        "station": "글로벌생활관",
        "bus_time": "18:13"
    },
    {
        "bus_round_id": 48,
        "station": "태전역",
        "bus_time": "18:35"
    },
    {
        "bus_round_id": 48,
        "station": "영진전문(복현서문)",
        "bus_time": "18:55"
    },
    {
        "bus_round_id": 49,
        "station": "영어마을",
        "bus_time": "21:15"
    },
    {
        "bus_round_id": 49,
        "station": "글로벌생활관",
        "bus_time": "21:18"
    },
    {
        "bus_round_id": 49,
        "station": "태전역",
        "bus_time": "21:40"
    },
    {
        "bus_round_id": 49,
        "station": "영진전문(복현서문)",
        "bus_time": "22:00"
    }
]';
        $schedules = json_decode($json, true);

        foreach ($schedules as $schedule) {
            BusSchedule::create($schedule);
        }
    }
}
