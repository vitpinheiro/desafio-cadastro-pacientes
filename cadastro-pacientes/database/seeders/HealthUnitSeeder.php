<?php

namespace Database\Seeders;

use App\Models\HealthUnit;
use Illuminate\Database\Seeder;

class HealthUnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'UBS Centro', 'city' => 'São Paulo'],
            ['name' => 'UBS Vila Nova', 'city' => 'São Paulo'],
            ['name' => 'UBS Jardim das Flores', 'city' => 'São Paulo'],
        ];

        foreach ($units as $unit) {
            HealthUnit::firstOrCreate($unit);
        }
    }
}
