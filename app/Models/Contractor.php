<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contractor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'nip',
        'address_street',
        'address_building',
        'address_apartment',
        'postal_code',
        'city',
        'ksef_legal_name',
        'ksef_legal_street',
        'ksef_legal_building',
        'ksef_legal_apartment',
        'ksef_legal_postal_code',
        'ksef_legal_city',
        'ksef_correspondence_name',
        'ksef_correspondence_street',
        'ksef_correspondence_building',
        'ksef_correspondence_apartment',
        'ksef_correspondence_postal_code',
        'ksef_correspondence_city',
        'ksef_customer_number',
        'is_jst',
        'is_vat_group_member',
        'gus_data',
        'recipient_name',
        'recipient_nip',
        'recipient_street',
        'recipient_building',
        'recipient_apartment',
        'recipient_postal_code',
        'recipient_city',
    ];

    protected $casts = [
        'is_jst' => 'boolean',
        'is_vat_group_member' => 'boolean',
        'gus_data' => 'array',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
