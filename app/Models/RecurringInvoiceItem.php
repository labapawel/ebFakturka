<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecurringInvoiceItem extends Model
{
    protected $fillable = [
        'recurring_invoice_id',
        'name',
        'quantity',
        'unit',
        'net_price',
        'vat_rate',
        'vat_amount',
        'gross_amount',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'net_price' => 'decimal:2',
        'vat_rate' => 'decimal:4',
        'vat_amount' => 'decimal:2',
        'gross_amount' => 'decimal:2',
    ];

    public function recurringInvoice()
    {
        return $this->belongsTo(RecurringInvoice::class);
    }
}
