<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill_payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_sucursal','id_bill', 'id_seller', 'id_payment_method','code_repayment', 'id_closure', 'id_closureI',
        'reference', 'amount', 'rate', 'collection'
    ];

    public function bill()
    {
        return $this->belongsTo(Bill::class, 'id_bill');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'id_payment_method');
    }


    public function seller()
    {
        return $this->belongsTo(User::class, 'id_seller');
    }
 
}
