<?php

return [
    'url' => env('KSEF_url', env('KSEF_URL', 'https://api-demo.ksef.mf.gov.pl/v2')),
    'nip' => env('KSEF_NIP', env('COMPANY_NIP')),
    'token' => env('KSEF_token', env('KSEF_TOKEN')),
    'cert_path' => storage_path('app/private/ksef/cert.crt'),
    'private_key_path' => storage_path('app/private/ksef/private.key'),
    'key_passphrase' => env('KSEF_PASS', env('KSEF_CERTPASS')),
];
