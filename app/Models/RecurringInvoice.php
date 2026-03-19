<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecurringInvoice extends Model
{
    protected $fillable = [
        'contractor_id',
        'user_id',
        'frequency',
        'frequency_interval',
        'start_date',
        'next_issue_date',
        'end_date',
        'status',
        'currency_id',
        'payment_method',
        'net_total',
        'vat_total',
        'gross_total',
    ];

    protected $casts = [
        'start_date' => 'date',
        'next_issue_date' => 'date',
        'end_date' => 'date',
        'net_total' => 'decimal:2',
        'vat_total' => 'decimal:2',
        'gross_total' => 'decimal:2',
    ];

    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function items()
    {
        return $this->hasMany(RecurringInvoiceItem::class);
    }
}
