<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\VatRate;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Upewnij się, że stawki VAT istnieją
        $vat23 = VatRate::where('name', '23%')->first();
        $vat8 = VatRate::where('name', '8%')->first();

        if (!$vat23 || !$vat8) {
            // Jeśli nie ma stawek, nie możemy dodać produktów (lub musimy je stworzyć, ale zakładamy że VatRateSeeder był uruchomiony)
            return;
        }

        $products = [
            // [
            //     'name' => 'Usługa programistyczna',
            //     'unit' => 'godz.',
            //     'net_price' => 150.00,
            //     'vat_rate_id' => $vat23->id,
            //     'pkwiu' => '62.01.11.0',
            // ],
            // [
            //     'name' => 'Konsultacje IT',
            //     'unit' => 'godz.',
            //     'net_price' => 200.00,
            //     'vat_rate_id' => $vat23->id,
            //     'pkwiu' => '62.02.20.0',
            // ],
            // [
            //     'name' => 'Abonament serwisowy',
            //     'unit' => 'msc',
            //     'net_price' => 500.00,
            //     'vat_rate_id' => $vat23->id,
            //     'pkwiu' => null,
            // ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(['name' => $product['name']], $product);
        }
    }
}
