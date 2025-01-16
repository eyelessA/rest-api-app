<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Building;
use App\Models\Organization;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $buildings = [
            'address' => ['г. Москва, ул. Ленина 1, офис 3', 'г. Москва, ул. Зеленоград, к834А'],
            'coordinates' => [
                ['latitude' => 55.978468, 'longitude' => 37.173329],
                ['latitude' => 55.979918, 'longitude' => 37.176203],
            ],
        ];

        foreach ($buildings['address'] as $index => $address) {
            $latitude = $buildings['coordinates'][$index]['latitude'];
            $longitude = $buildings['coordinates'][$index]['longitude'];

            Building::query()->create([
                'address' => $address,
                'coordinates' => DB::raw("ST_SetSRID(ST_MakePoint($latitude, $longitude), 4326)"),
            ]);
        }

        $activities = [
            'Еда' => null,
            'Автомобили' => null,
            'Мясная продукция' => 1,
            'Молочная продукция' => 1,
            'Грузовые' => 2,
            'Легковые' => 2
        ];

        foreach ($activities as $activity => $parentId) {
            Activity::query()->create([
                'name' => $activity,
                'parent_id' => $parentId
            ]);
        }

        $organizations = [
            'name' => ['ООО Рога и Копыта', 'ООО Apple'],
            'phone_number' => ['2-222-222', '3-333-333'],
            'building_id' => [1, 2],
            'activity_id' => [1, 2],
        ];

        foreach ($organizations['name'] as $index => $name) {
            Organization::query()->create([
                'name' => $name,
                'phone_number' => $organizations['phone_number'][$index],
                'building_id' => $organizations['building_id'][$index],
                'activity_id' => $organizations['activity_id'][$index],
            ]);
        }
    }
}
