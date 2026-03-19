<?php

return [
    'mode' => env('VAT_EXEMPT', 'vat'),
    'exemption_reason_type' => env('VAT_EXEMPT_REASON_TYPE', 'P_19A'),
    'exemption_reason_text' => env('VAT_EXEMPT_REASON_TEXT', 'Przepis ustawy albo aktu wydanego na podstawie ustawy, na podstawie ktorego podatnik stosuje zwolnienie od podatku'),
];
