<?php

return [
    'name' => env('COMPANY_NAME', 'Moja Firma Sp. z o.o.'),
    'nip' => env('COMPANY_NIP', '1234567890'),
    'street' => env('COMPANY_STREET', 'Biznesowa'),
    'building_number' => env('COMPANY_BUILDING_NUMBER', '1'),
    'postal_code' => env('COMPANY_POSTAL_CODE', '00-000'),
    'city' => env('COMPANY_CITY', 'Warszawa'),
    'bank_name' => env('COMPANY_BANK_NAME', 'Bank'),
    'bank_account' => env('COMPANY_BANK_ACCOUNT', '00 0000 0000 0000 0000 0000 0000'),
    'factor_bank_name' => env('COMPANY_FACTOR_BANK_NAME', ''),
    'factor_bank_account' => env('COMPANY_FACTOR_BANK_ACCOUNT', ''),
    
    // Helpers for display
    'address_line_1' => 'ul. ' . env('COMPANY_STREET', 'Biznesowa') . ' ' . env('COMPANY_BUILDING_NUMBER', '1'),
    'address_line_2' => env('COMPANY_POSTAL_CODE', '00-000') . ' ' . env('COMPANY_CITY', 'Warszawa'),
];
