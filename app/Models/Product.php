<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'unit',
        'net_price',
        'vat_rate_id',
        'pkwiu',
    ];

    protected $casts = [
        'net_price' => 'decimal:2',
    ];

    public function vatRate()
    {
        return $this->belongsTo(VatRate::class);
    }
}
