<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Closure extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_sucursal',
        'id_seller',
        'bill_amount',
        'payment_amount',
        'repayment_amount',
        'small_box_amount',
        'type'
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'id_seller');
    }
}
