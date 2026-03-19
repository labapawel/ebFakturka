<?php

namespace Database\Seeders;

use App\Models\VatRate;
use Illuminate\Database\Seeder;

class VatRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rates = [
            ['name' => '23%', 'rate' => 0.23, 'is_active' => true],
            ['name' => '8%', 'rate' => 0.08, 'is_active' => true],
            ['name' => '5%', 'rate' => 0.05, 'is_active' => true],
            ['name' => '0%', 'rate' => 0.00, 'is_active' => true],
            ['name' => 'zw', 'rate' => 0.00, 'is_active' => true], // Zwolniony
        ];

        foreach ($rates as $rate) {
            VatRate::firstOrCreate(['name' => $rate['name']], $rate);
        }
    }
}
