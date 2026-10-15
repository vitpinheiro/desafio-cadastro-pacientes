<?php

namespace Database\Seeders;

use App\Models\HealthUnit;
use Illuminate\Database\Seeder;

class HealthUnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'UBS Centro', 'city' => 'Natal RN'],
            ['name' => 'UBS Pescadores', 'city' => 'Natal RN'],
            ['name' => 'UBS Pajuçara', 'city' => 'Natal RN'],
        ];

        foreach ($units as $unit) {
            HealthUnit::firstOrCreate($unit);
        }
    }
}
