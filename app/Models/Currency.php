<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'abbreviation',
        'rate',
        'is_official',
        'is_principal',
        'status',
    ];
    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class, 'id_currency');
    }
}