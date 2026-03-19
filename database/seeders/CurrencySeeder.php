<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            [
                'code' => 'PLN',
                'name' => 'Polski Złoty',
                'exchange_rate' => 1.0000,
                'is_default' => true,
            ],
            [
                'code' => 'EUR',
                'name' => 'Euro',
                'exchange_rate' => 4.2500,
                'is_default' => false,
            ],
            [
                'code' => 'USD',
                'name' => 'Dolar Amerykański',
                'exchange_rate' => 3.5500,
                'is_default' => false,
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::firstOrCreate(['code' => $currency['code']], $currency);
        }
    }
}
