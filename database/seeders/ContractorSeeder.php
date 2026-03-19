<?php

namespace Database\Seeders;

use App\Models\Contractor;
use Illuminate\Database\Seeder;

class ContractorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contractors = [
            // [
            //     'name' => 'Firma Przykładowa Sp. z o.o.',
            //     'nip' => '1234567890',
            //     'address_street' => 'ul. Testowa',
            //     'address_building' => '1',
            //     'address_apartment' => '2',
            //     'postal_code' => '00-001',
            //     'city' => 'Warszawa',
            // ],
        ];

        foreach ($contractors as $contractor) {
            Contractor::firstOrCreate(['nip' => $contractor['nip']], $contractor);
        }
    }
}
