<?php

namespace Database\Seeders;

use App\Models\SalonCategory;
use Illuminate\Database\Seeder;

class SalonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $salonCategory = [
            [
                'category' => 'CUT',
                'salonServices' => [
                    [
                        'service' => '커트',
                        'gender' => 'male',
                        'price' => '10000',
                    ],
                    [
                        'service' => '커트+옆다운',
                        'gender' => 'male',
                        'price' => '15000',
                        ],
                    ],
                    [
                        'service' => '커트+옆다운+뒷다운',
                        'gender' => 'male',
                        'price' => '20000',
                    ],
                    [
                        'gender' => 'male',
                        'price' => '25000',
                    ],
                    [
                        'service' => '면접헤어',
                        'gender' => 'male',
                        'price' => '10000',
                    ],
                    [
                        'service' => '앞머리',
                        'gender' => 'female',
                        'price' => '2000',
                    ],
                    [
                        'service' => '단발컷',
                        'gender' => 'female',
                        'price' => '8000',
                    ],
                    [
                        'service' => '숏컷',
                        'gender' => 'female',
                        'price' => '10000',
                    ],
                    [
                        'service' => '디자인컷',
                        'gender' => 'female',
                        'price' => '10000',
                    ],
                    [
                        'service' => '레이어드컷',
                        'gender' => 'female',
                        'price' => '10000',
                    ],
                    [
                        'service' => '허쉬컷',
                        'gender' => 'female',
                        'price' => '10000',
                    ],
                    [
                        'service' => '보브단발',
                        'gender' => 'female',
                        'price' => '10000',
                    ],
                ],
            [
                'category' => 'PERM',
                'salonServices' => [
                    [
                        'service' => '커트+댄디펌',
                        'gender' => 'male',
                        'price' => '30000',
                    ],
                    [
                        'service' => '커트+애즈펌',
                        'gender' => 'male',
                        'price' => '30000',
                    ],
                    [
                        'service' => '커트+쉐도우펌',
                        'gender' => 'male',
                        'price' => '30000',
                    ],
                    [
                        'service' => '커트+포마드펌',
                        'gender' => 'male',
                        'price' => '30000',
                    ],
                    [
                        'service' => '커트+리프펌',
                        'gender' => 'male',
                        'price' => '35000',
                    ],
                    [
                        'service' => '커트+스왈로펌',
                        'gender' => 'male',
                        'price' => '40000',
                    ],
                    [
                        'service' => '커트+펌+옆다운',
                        'gender' => 'male',
                        'price' => '35000',
                    ],
                    [
                        'service' => '커트+펌+옆+뒷다운',
                        'gender' => 'male',
                        'price' => '40000',
                    ],
                    [
                        'service' => '커트+볼륨매직',
                        'gender' => 'male',
                        'price' => '45000',
                    ],
                    [
                        'service' => '재학생 외 펌',
                        'gender' => 'male',
                        'price' => '35000',
                    ],
                    [
                        'service' => '단발펌',
                        'gender' => 'female',
                        'price' => '40000',
                    ],
                    [
                        'service' => '매직(단발기준)',
                        'gender' => 'female',
                        'price' => '50000',
                    ],
                    [
                        'service' => '디지털(단발기준)',
                        'gender' => 'female',
                        'price' => '55000',
                    ],
                    [
                        'service' => '볼륨매직(단발기준)',
                        'gender' => 'female',
                        'price' => '60000',
                    ],
                    [
                        'service' => '물결펌',
                        'gender' => 'female',
                        'price' => '60000',
                    ],
                    [
                        'service' => '매직셋팅',
                        'gender' => 'female',
                        'price' => '70000',
                    ],
                ]
            ],
            [
                'category' => 'COLOR',
                'salonServices' => [
                    [
                        'service' => '커트+새치염색',
                        'gender' => 'male',
                        'price' => '30000',
                    ],
                    [
                        'service' => '커트+염색펌',
                        'gender' => 'male',
                        'price' => '35000',
                    ],
                    [
                        'service' => '커트+탈색(회당)',
                        'gender' => 'male',
                        'price' => '40000',
                    ],
                    [
                        'service' => '커트+뿌리염색',
                        'gender' => 'female',
                        'price' => '35000',
                    ],
                    [
                        'service' => '전체염색(귀밑기준)',
                        'gender' => 'female',
                        'price' => '45000',
                    ],
                    [
                        'service' => '탈색(회당)',
                        'gender' => 'female',
                        'price' => '50000',
                    ],
                ]
            ],
        ];

        foreach ($salonCategory as $categoryData) {
            $category = SalonCategory::create(['category' => $categoryData['category']]);

            if (isset($categoryData['salonServices'])) {
                foreach ($categoryData['salonServices'] as $serviceData) {
                    $service = $category->salonServices()->create([
                        'service' => $serviceData['service'],
                        'gender' => $serviceData['gender'],
                        'price' => $serviceData['price'],
                    ]);
                }
            }
        }
    }

}
