<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = ['code','code2','name','units','name_detal','url',
    'cost','utility_detal','price_detal','utility','price','utility2','price2','utility3','price3',
    'stock_min','iva','status'];


    public function scopeSearch($query, $scope=''){
        return $query->where('name','like',"%$scope%");
    }
    public function stocks(){
        return $this->hasMany(Stock::class, 'id_product');
    }

    public function latestStock() {
        return $this->hasOne(Stock::class, 'id_product')->latest();
    }
}


