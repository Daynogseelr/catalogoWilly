<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;
    protected $fillable = ['id_sucursal','id_product','id_user','id_shopping','id_bill','id_repayment','id_inventory_adjustment','quantity','addition','subtraction','description'];
    public function product(){
        return $this->belongsTo(Product::class, 'id_product');
    }
    public function user(){
        return $this->belongsTo(User::class, 'id_user');
    }
    public function inventory_adjustment(){
        return $this->belongsTo(InventoryAdjustment::class, 'id_inventory_adjustment');
    }
    public function shopping(){
        return $this->belongsTo(Shopping::class, 'id_shopping');
    }
    public function bill(){
        return $this->belongsTo(Bill::class, 'id_bill');
    }
    public function repayment(){
        return $this->belongsTo(Repayment::class, 'id_repayment');
    }
}
