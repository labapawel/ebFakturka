<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceCounter extends Model
{
    protected $fillable = [
        'type',
        'period_year',
        'period_month',
        'last_number',
    ];
}
