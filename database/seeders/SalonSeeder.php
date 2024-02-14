<?php

namespace Database\Seeders;

use App\Models\SalonCategory;
use App\Models\SalonPrice;
use App\Models\SalonService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SalonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        $salonCategory = [
            'category' => '커트',
        ];

        $createCategory = SalonCategory::create($salonCategory);

        $salonService = [
            'salon_category_id' => $createCategory->id,
            'service' => '남자 커트',
        ];

        $createService = SalonService::create($salonService);

        $salonPrice = [
            'salon_service_id' => $createService->id,
            'gender' => 'M',
            'price' => '10000',
        ];

        $createPrice = SalonPrice::create($salonPrice);
    }
}
