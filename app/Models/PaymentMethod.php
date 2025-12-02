<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'id_currency', 'type', 'bank', 'reference', 'data', 'status'
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'id_currency');
    }
}