<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill_detail extends Model
{
    use HasFactory;
    public function bill(){
        return $this->belongsTo(Bill::class, 'id_bill');
    }
    public function product(){
        return $this->belongsTo(Product::class, 'id_product');
    }

    protected $fillable = ['id_bill','id_product','code','name','quantity','price','priceU','total_amount','discount_percent','discount','net_amount','iva','price_type'];
}
