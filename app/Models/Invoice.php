<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', // sales, purchase
        'number',
        'issue_date',
        'sale_date',
        'due_date',
        'payment_method',
        'description',
        'contractor_id',
        'user_id',
        'currency_id',
        'net_total',
        'vat_total',
        'gross_total',
        'status',
        'ksef_number',
        'ksef_status',
    ];

    public function scopeSales($query)
    {
        return $query->where('type', 'sales');
    }

    public function scopePurchase($query)
    {
        return $query->where('type', 'purchase');
    }

    protected $casts = [
        'issue_date' => 'date',
        'sale_date' => 'date',
        'due_date' => 'date',
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
        return $this->hasMany(InvoiceItem::class);
    }
    public function getStatusPlAttribute()
    {
        return match($this->status) {
            'issued' => 'Wystawiona',
            'saved' => 'Zapisana',
            'draft' => 'Szkic',
            'sent' => 'Wysłana',
            'paid' => 'Opłacona',
            'cancelled' => 'Anulowana',
            default => $this->status,
        };
    }

    public function getKsefStatusPlAttribute()
    {
        return match($this->ksef_status) {
            'sent' => 'Wysłano',
            'fetched' => 'Pobrana',
            'pending' => 'Oczekuje',
            'error' => 'Błąd',
            default => $this->ksef_status,
        };
    }

    public function getAmountInWordsAttribute()
    {
        $service = new \App\Services\AmountInWordsService();
        return $service->kwotaSlownie($this->gross_total, $this->currency->code ?? 'PLN');
    }
}
